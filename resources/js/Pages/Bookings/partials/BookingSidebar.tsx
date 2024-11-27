import React, { useState } from "react";
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
    Avatar,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import CommentIcon from "@mui/icons-material/Comment";
import HistoryIcon from "@mui/icons-material/History";
import PersonIcon from "@mui/icons-material/Person";

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
    const [activeTab, setActiveTab] = useState(0);

    const handleAddComment = () => {
        if (newComment.trim()) {
            onAddComment(newComment);
            setNewComment("");
        }
    };

    const handleTabChange = (event: React.SyntheticEvent, newValue: number) => {
        setActiveTab(newValue);
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
                            {comments.map((comment) => (
                                <ListItem key={comment.id} alignItems="flex-start">
                                    <ListItemIcon>
                                        <Avatar sx={{ bgcolor: "success.main" }}>
                                            <PersonIcon />
                                        </Avatar>
                                    </ListItemIcon>
                                    <ListItemText
                                        primary={
                                            <>
                                                {`@${comment.user.username}`} {`${dayjs(comment.date).format("DD/MM/YYYY hh:mm A")}`} <br />
                                            </>
                                        }
                                        secondary={comment.comment}
                                        primaryTypographyProps={{ style: { fontSize: "12px", color: "gray" } }}
                                        secondaryTypographyProps={{ style: { fontSize: "14px", color: "white" } }}
                                    />
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
                                        primary={`${dayjs(log.date).format("DD/MM/YYYY hh:mm A")} - @${log.user.username}`}
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
            </Box>
        </Drawer>
    );
};

export default BookingSidebar;
