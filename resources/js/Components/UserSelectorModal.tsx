import React, { useState, useEffect } from "react";
import {
  Dialog,
  DialogActions,
  Avatar,
  DialogContent,
  Box,
  Chip,
  DialogTitle,
  Button,
  useTheme,
  Alert,
} from "@mui/material";
import PersonAddIcon from "@mui/icons-material/PersonAdd";
import CheckIcon from "@mui/icons-material/Check";

interface User {
  id: string;
  user_name: string;
  detail?: {
    avatar?: {
      badge?: {
        text?: string;
        background?: string;
      };
    };
  };
}

interface UserSelectorDialogProps {
  open: boolean;
  onClose: () => void;
  onSave: (selectedUserId: string | null) => void;
  initialUserId: string | null;
  users: User[];
  processing?: boolean;
}

const UserSelectorDialog: React.FC<UserSelectorDialogProps> = ({
  open,
  onClose,
  onSave,
  initialUserId,
  users = [],
  processing = false,
}) => {
  const [selectedUserId, setSelectedUserId] = useState<string | null>(initialUserId);
  const theme = useTheme();

  useEffect(() => {
    setSelectedUserId(initialUserId);
  }, [initialUserId]);

  const handleChipClick = (userId: string) => {
    setSelectedUserId((prev) => (prev === userId ? null : userId));
  };

  const handleSave = () => {
    onSave(selectedUserId);
  };

  const handleClose = () => {
    setSelectedUserId(initialUserId);
    onClose();
  };

  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm">
      <DialogTitle>Choose the user who will be taking care of this booking:</DialogTitle>
      <DialogContent sx={{ padding: "16px 24px" }}>
        <Alert severity="info">Please remember to click Save to apply your changes</Alert>
        <Box sx={{ display: "flex", flexWrap: "wrap", gap: 1, mt: 2 }}>
          {users.map((user) => {
            const isSelected = selectedUserId === user.id;
            const bg = user?.detail?.avatar?.badge?.background;
            const text = user?.detail?.avatar?.badge?.text;

            return (
              <Chip
                key={user.id}
                label={user.user_name || "Agent Name"}
                avatar={
                  <Avatar
                    sx={{
                      width: 28,
                      height: 28,
                      fontSize: 12,
                      bgcolor: isSelected ? "#2ecc71" : undefined,
                      color: isSelected ? "white" : "inherit",
                      animation: isSelected ? "blink 1s infinite ease-in-out" : "none",
                      "@keyframes blink": {
                        "0%": { opacity: 1 },
                        "50%": { opacity: 0.4 },
                        "100%": { opacity: 1 },
                      },
                    }}
                  >
                    {isSelected ? <CheckIcon sx={{ fontSize: 10 }} /> : (user.user_name || "A")[0]}
                  </Avatar>
                }
                onClick={() => handleChipClick(user.id)}
                size="small"
                aria-pressed={isSelected}
                icon={isSelected ? <CheckIcon /> : undefined}
                sx={(t) => ({
                  fontSize: "0.75rem",
                  fontWeight: 500,
                  border: isSelected ? `2px solid #2ecc71` : `transparent`,
                  color: user.detail?.avatar?.badge?.text,
                  backgroundColor: user.detail?.avatar?.badge?.background,
                  boxShadow: isSelected ? "0 2px 8px rgba(0,0,0,0.12)" : "none",
                  transition: "all 150ms ease",
                  cursor: "pointer",
                  "& .MuiChip-label": { px: 1.5 },
                })}
              />
            );
          })}
        </Box>
      </DialogContent>

      <DialogActions>
        <Button onClick={handleClose}>Cancel</Button>
        <Button onClick={handleSave} color="primary" disabled={processing}>
          Save
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default UserSelectorDialog;
