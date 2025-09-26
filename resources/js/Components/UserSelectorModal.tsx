import React, { useState, useEffect } from "react";
import { Dialog, DialogActions, Avatar, DialogContent, Box, Chip, DialogTitle, Button } from "@mui/material";

interface User {
    id: string;
    user_name: string;
}

interface UserSelectorDialogProps {
    open: boolean;
    onClose: () => void;
    onSave: (selectedUserId: string | null) => void;
    initialUserId: string | null;
    users: User[];
    processing?: boolean;
}

const UserSelectorDialog: React.FC<UserSelectorDialogProps> = ({ open, onClose, onSave, initialUserId, users = [], processing = false }) => {
    const [selectedUserId, setSelectedUserId] = useState<string | null>(initialUserId);

    useEffect(() => {
        setSelectedUserId(initialUserId); 
    }, [initialUserId]);

    const handleChipClick = (userId: string) => {
      setSelectedUserId(prev => prev === userId ? null : userId);
    };

    const handleSave = () => {
        onSave(selectedUserId); 
    };

    return (
        <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm">
            <DialogTitle>Choose the user who will be taking care of this booking:</DialogTitle>
            <DialogContent sx={{ padding: "16px 24px" }}>
                <Box sx={{ display: "flex", flexWrap: "wrap", gap: 1 }}>
                    {users.map((user) => (
                        <Chip
                            key={user.id}
                            label={user.user_name}
                            onClick={() => handleChipClick(user.id)}
                            color={selectedUserId === user.id ? "primary" : "default"}
                            variant={selectedUserId === user.id ? "filled" : "outlined"}
                            avatar={
                                selectedUserId === user.id ? (
                                    <Avatar sx={{ backgroundColor: "green", width: 24, height: 24 }}>
                                        ✔
                                    </Avatar>
                                ) : undefined
                            }
                            sx={{ cursor: "pointer" }}
                        />
                    ))}
                </Box>
            </DialogContent>
            <DialogActions>
                <Button onClick={onClose}>Cancel</Button>
                <Button onClick={handleSave} color="primary" disabled={processing}>
                    Save
                </Button>
            </DialogActions>
        </Dialog>
    );
};

export default UserSelectorDialog;
