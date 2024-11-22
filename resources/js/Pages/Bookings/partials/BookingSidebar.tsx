import React, { useState } from "react";
import {
    Drawer,
    Box,
    Typography,
    List,
    ListItem,
    ListItemText,
    Divider,
    IconButton,
    TextField,
    Button,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import CommentIcon from "@mui/icons-material/Comment";
import HistoryIcon from "@mui/icons-material/History";
import { ListItemIcon } from "@mui/material";

import dayjs from "dayjs";

interface BookingSidebarProps {
    isOpen: boolean;
    toggleSidebar: () => void;
    logs: Array<{ date: string; time: string; user: string; action: string; description?: string }>;
    comments: Array<{ id: number; user: string; date: string; comment: string }>;
    onAddComment: (comment: string) => void;
}

const BookingSidebar: React.FC<BookingSidebarProps> = ({ isOpen, toggleSidebar, logs, comments, onAddComment }) => {
    const [newComment, setNewComment] = useState("");

    const handleAddComment = () => {
        if (newComment.trim()) {
            onAddComment(newComment);
            setNewComment("");
        }
    };

    const combinedData = [...comments.map(comment => ({
        ...comment,
        type: "comment",
    })), ...logs.map(log => ({
        ...log,
        date: log.created_at,
        type: "log",
    }))].sort((a, b) => dayjs(a.created_at).diff(dayjs(b.created_at)));

    return (
        <Drawer anchor="right" open={isOpen} onClose={toggleSidebar}>
            <Box sx={{ width: 350, p: 2, mt: "50px" }}>
                {/* Header */}
                <Box sx={{ display: "flex", justifyContent: "space-between", alignItems: "center", mb: 2 }}>
                    <Typography variant="h6">Comments & Logs</Typography>
                    <IconButton onClick={toggleSidebar}>
                        <CloseIcon />
                    </IconButton>
                </Box>

                <Divider sx={{ mb: 2 }} />

                {/* Input para añadir comentario */}
                <Box sx={{ mb: 2 }}>
                    <TextField
                        fullWidth
                        label="Add a comment"
                        variant="outlined"
                        value={newComment}
                        onChange={(e) => setNewComment(e.target.value)}
                        multiline
                        rows={3}
                    />
                    <Button
                        variant="contained"
                        color="primary"
                        sx={{ mt: 1 }}
                        onClick={handleAddComment}
                        disabled={!newComment.trim()}
                    >
                        Add Comment
                    </Button>
                </Box>

                <Divider sx={{ my: 2 }} />

                <List>
                    {combinedData.map((item, index) => (
                        <ListItem key={index} alignItems="flex-start">
                            {/* Ícono condicional */}
                            <ListItemIcon>
                                {item.type === "comment" ? (
                                    <CommentIcon color="primary" />
                                ) : (
                                    <HistoryIcon color="action" />
                                )}
                            </ListItemIcon>
                            {/* Contenido del texto */}
                            {item.type === "comment" ? (
                                <ListItemText
                                primary={
                                    <Box>
                                        <Typography variant="body2" color="textPrimary">
                                            {dayjs(item.date).format("DD/MM/YYYY hh:mm A")}
                                        </Typography>
                                        <Typography variant="body2" color="textSecondary">
                                            @{item.user.username}
                                        </Typography>
                                    </Box>
                                }
                                secondary={item.comment || `${item.action}${item.description ? `: ${item.description}` : ""}`}
                            />
                            ) : (
                                <ListItemText
                                    primary={`${dayjs(item.date).format("DD/MM/YYYY hh:mm A")} - @${item.user.username}`}
                                    secondary={`${item.action}${item.description ? `: ${item.description}` : ""}`}
                                />
                            )}
                        </ListItem>
                    ))}
                </List>
            </Box>
        </Drawer>
    );
};

export default BookingSidebar;
