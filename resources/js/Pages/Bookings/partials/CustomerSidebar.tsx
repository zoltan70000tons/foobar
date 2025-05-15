import React, { useEffect, useState } from "react";
import {
    Drawer,
    Box,
    Typography,
    Divider,
    IconButton,
    TextField,
    Button,
    Tabs,
    Tab,
    List,
    ListItem,
    ListItemText,
    ListItemIcon,
    Avatar, Dialog, DialogTitle, DialogContent, DialogActions,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import CommentIcon from "@mui/icons-material/Comment";
import HistoryIcon from "@mui/icons-material/History";
import PersonIcon from "@mui/icons-material/Person";

import dayjs from "dayjs";
import { Delete } from "@mui/icons-material";
import { router } from "@inertiajs/react";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import LoadingOverlay from "@/Components/LoadingOverlay";

type Author = {
    username: string;
    id: string;
    email: string;
}

interface CustomerSidebarProps {
    isOpen: boolean;
    toggleSidebar: () => void;
    logs: Array<{ created_at: string; customer_id: string; action: string; description?: string; author?: Author }>;
    comments: Array<{ id: number; customer_id: string; created_at: string; comment: string; author: Author }>;
    onAddComment: (comment: string) => void;
}

const CustomerSidebar: React.FC<CustomerSidebarProps> = ({ auth, isOpen, toggleSidebar, logs, comments, onAddComment, customerId }) => {
    const [newComment, setNewComment] = useState("");
    const [activeTab, setActiveTab] = useState(0);
    const [confirmDeleteOpen, setConfirmDeleteOpen] = useState(false);
    const [commentIdToDelete, setCommentIdToDelete] = useState(null);
    const [loading, setLoading] = useState(false);
    const { showSnackbar } = useSnackbar();
    const [localComments, setLocalComments] = useState(comments);

    useEffect(() => {
        setLocalComments(comments);
    }, [comments])

    const handleAddComment = () => {
        if (newComment.trim()) {
            onAddComment(newComment);
            setNewComment("");
        }
    };

    const handleTabChange = (event: React.SyntheticEvent, newValue: number) => {
        setActiveTab(newValue);
    };

    const handleOpenDelete = (commentId) => {
        setCommentIdToDelete(commentId);
        setConfirmDeleteOpen(true);
    };

    const handleConfirmDelete = () => {
        if (!commentIdToDelete) return;

        setLoading(true);
        router.post(
          route("customers.delete-comment", { user: customerId }),
          {
              comment_id: commentIdToDelete,
          },
          {
              onSuccess: () => {
                  showSnackbar("Comment deleted successfully.", "success");
                const newComments = localComments.filter((comment) => comment.id !== commentIdToDelete);
                setLocalComments(newComments);
              },
              onError: () => {
                  showSnackbar("Could not delete comment.", "error");
              },
              onFinish: () => {
                  setLoading(false);
                  setConfirmDeleteOpen(false);
                  setCommentIdToDelete(null);
              },
          },
        );
    };

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

                {/* Tabs */}
                <Tabs value={activeTab} onChange={handleTabChange} centered>
                    <Tab label="Comments" />
                    <Tab label="Logs" />
                </Tabs>

                <Divider sx={{ my: 2 }} />

                {/* Content for each tab */}
                {activeTab === 0 && (
                    <Box>
                        {/* Comments Section */}
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
                        <Divider sx={{ mb: 2 }} />
                        <List>
                            {localComments.map((comment) => (
                                <ListItem key={comment.id} alignItems="flex-start">
                                    <ListItemIcon>
                                        <Avatar sx={{ bgcolor: "success.main" }}>
                                            <PersonIcon />
                                        </Avatar>
                                    </ListItemIcon>
                                    <ListItemText
                                        primary={
                                            <>
                                                {`@${comment?.author?.username}`} {`${dayjs(comment.created_at).format("DD/MM/YYYY" +
                                              " hh:mm" +
                                              " A")}`} <br />
                                            </>
                                        }
                                        secondary={comment.comment}
                                        primaryTypographyProps={{ style: { fontSize: "12px", color: "gray" } }}
                                        secondaryTypographyProps={{ style: { fontSize: "14px", color: "white" } }}
                                    />
                                    <IconButton
                                      aria-label="delete"
                                      color="error"
                                      size="small"
                                      disabled={!auth.roles.includes('SuperAdmin') && comment.author.id !== auth.user.id}
                                      onClick={() => handleOpenDelete(comment.id)}
                                    >
                                        <Delete fontSize="small" />
                                    </IconButton>
                                </ListItem>
                            ))}
                        </List>
                    </Box>
                )}

                {activeTab === 1 && (
                    <Box>
                        {/* Logs Section */}
                        <List>
                            {logs.map((log, index) => (
                                <ListItem key={index} alignItems="flex-start">
                                    <ListItemIcon>
                                        <HistoryIcon color="info" />
                                    </ListItemIcon>
                                    <ListItemText
                                        primary={`${dayjs(log.created_at).format("DD/MM/YYYY hh:mm A")} - @${log?.author?.username || "System"}`}
                                        secondary={
                                            <>
                                                {log.action}
                                                {log.description && (
                                                    <>
                                                        <br />
                                                        {log.description}
                                                    </>
                                                )}
                                            </>
                                        }
                                    />
                                </ListItem>
                            ))}
                        </List>
                    </Box>
                )}
                <LoadingOverlay open={loading} />
            </Box>
            {/* Delete Confirmation */}
            <Dialog open={confirmDeleteOpen} onClose={() => setConfirmDeleteOpen(false)}>
                <DialogTitle>Delete Discount</DialogTitle>
                <DialogContent>
                    <Typography>Are you sure you want to delete this comment?</Typography>
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setConfirmDeleteOpen(false)} color="secondary">
                        Cancel
                    </Button>
                    <Button onClick={handleConfirmDelete} color="error" variant="contained">
                        Confirm
                    </Button>
                </DialogActions>
            </Dialog>
        </Drawer>
    );
};

export default CustomerSidebar;
