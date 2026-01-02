import React, { useState, useMemo } from "react";
import { Drawer, Box, Typography, IconButton, TextField, Button, Badge, Divider, Grid, Stack } from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import HistoryList from "./HistoryList";

type Actor = { username: string };
type Logs = {
  action: string;
  actor: Actor;
  actor_id: string;
  actor_type: string;
  message: string;
  status: string;
  payment_plan: string;
  cabin_number: string;
  cabin_status: string;
  bed_config: string;
  is_single_occupancy: boolean;
  booking_request_id: string;
  created_at: string;
  description: string;
  id: string;
  related_id: string;
  related_type: string;
  booking_code: string;
};
type BookingSidebarProps = {
  isOpen: boolean;
  toggleSidebar: () => void;
  history: any[];
  onAddComment: (comment: string) => void;
};

const BookingSidebar: React.FC<BookingSidebarProps> = ({ isOpen, toggleSidebar, history, onAddComment }) => {
  const [newComment, setNewComment] = useState("");

  const { comments, logs } = useMemo(() => {
    const comments = history.filter((it) => it?.type === "comment");
    const logs = history.filter((it) => it?.type === "log" || it?.type !== "comment");
    return { comments, logs };
  }, [history]);

  const handleAddComment = () => {
    if (!newComment.trim()) return;
    onAddComment(newComment.trim());
    setNewComment("");
  };

  return (
    <Drawer
      anchor="right"
      open={isOpen}
      onClose={toggleSidebar}
      PaperProps={{
        sx: {
          width: { xs: "100vw", sm: 700 },
          maxWidth: 700,
        },
      }}
    >
      <Box sx={{ display: "flex", flexDirection: "column", height: "100vh" }}>
        <Box
          sx={{
            position: "sticky",
            top: 0,
            zIndex: 2,
            bgcolor: "background.paper",
            borderBottom: (t) => `1px solid ${t.palette.divider}`,
            px: 2,
            py: 1.25,
          }}
        >
          <Box sx={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
            <Typography variant="h6" sx={{ m: 0 }}>
              Booking History
            </Typography>
            <IconButton onClick={toggleSidebar} size="small">
              <CloseIcon />
            </IconButton>
          </Box>
        </Box>

        <Box sx={{ flex: 1, overflow: "hidden", px: 2, py: 2 }}>
          <Grid container spacing={2} sx={{ height: "100%" }}>
            <Grid item xs={12} md={6} sx={{ height: "100%" }}>
              <Box
                sx={{
                  display: "flex",
                  flexDirection: "column",
                  height: "100%",
                  border: (t) => `1px solid ${t.palette.divider}`,
                  borderRadius: 2,
                  overflow: "hidden",
                }}
              >
                <Box
                  sx={{
                    position: "sticky",
                    top: 0,
                    zIndex: 1,
                    bgcolor: "grey.900",
                    color: "common.white",
                    px: 1.5,
                    py: 1,
                  }}
                >
                  <Stack
                    direction="row"
                    spacing={1}
                    alignItems="center"
                    justifyContent="space-between"
                    sx={{ pr: 1.3 }}
                  >
                    <Typography variant="subtitle1" sx={{ m: 0, fontWeight: 700 }}>
                      Comments
                    </Typography>
                    <Badge color="info" badgeContent={comments.length} />
                  </Stack>
                </Box>

                {/* Input */}
                <Box sx={{ p: 1.5 }}>
                  <TextField
                    fullWidth
                    label="Add a comment"
                    variant="outlined"
                    value={newComment}
                    onChange={(e) => setNewComment(e.target.value)}
                    multiline
                    rows={3}
                  />
                  <Button variant="contained" sx={{ mt: 1 }} onClick={handleAddComment} disabled={!newComment.trim()}>
                    Add Comment
                  </Button>
                </Box>

                <Divider />

                <Box sx={{ flex: 1, overflowY: "auto", p: 1.5 }}>
                  <HistoryList history={comments} />
                </Box>
              </Box>
            </Grid>

            <Grid item xs={12} md={6} sx={{ height: "100%" }}>
              <Box
                sx={{
                  display: "flex",
                  flexDirection: "column",
                  height: "100%",
                  border: (t) => `1px solid ${t.palette.divider}`,
                  borderRadius: 2,
                  overflow: "hidden",
                }}
              >
                <Box
                  sx={{
                    position: "sticky",
                    top: 0,
                    zIndex: 1,
                    bgcolor: "grey.900",
                    color: "common.white",
                    px: 1.5,
                    py: 1,
                    mt: { xs: "-16px" },
                  }}
                >
                  <Stack
                    direction="row"
                    spacing={1}
                    alignItems="center"
                    justifyContent="space-between"
                    sx={{ pr: 1.2 }}
                  >
                    <Typography variant="subtitle1" sx={{ m: 0, fontWeight: 700 }}>
                      Logs
                    </Typography>
                    <Badge color="warning" badgeContent={logs.length} />
                  </Stack>
                </Box>

                <Box sx={{ flex: 1, overflowY: "auto", p: 1.5 }}>
                  <HistoryList history={logs} />
                </Box>
              </Box>
            </Grid>
          </Grid>
        </Box>
      </Box>
    </Drawer>
  );
};

export default BookingSidebar;
