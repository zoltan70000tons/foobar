import React, { useState } from "react";
import {
  Box,
  List,
  ListItem,
  ListItemText,
  Chip,
  Typography,
  Divider,
  Tooltip,
  Stack,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  AccordionSummary,
  Accordion,
  Grid,
  AccordionDetails,
  Avatar,
} from "@mui/material";
import ChatBubbleOutlineIcon from "@mui/icons-material/ChatBubbleOutline";
import HistoryIcon from "@mui/icons-material/History";
import ContentCopyIcon from "@mui/icons-material/ContentCopy";

const formatDateTime = (iso?: string | Date) => (iso ? new Date(iso).toLocaleString() : "");

type Avatar = { image: string | null; badge: { text: string; background: string } };

type Actor = { username: string; id?: number | string; avatar?: Avatar | null };

type HistoryItem = {
  id: string;
  type: "log" | "comment";
  action: string;
  message?: string;
  detail?: string;
  created_at: string;
  payload?: string | null;
  actor?: Actor | null;
  booking_request_id?: string | null;
  booking_code?: string | null;
  bed_config?: string | null;
  cabin_number?: string | null;
  cabin_status?: string | null;
  is_single_occupancy?: boolean | null;
  payment_plan?: string | null;
  status?: string | null;
};

const safeParseJSON = (payload?: unknown) => {
  if (!payload) return null;
  if (typeof payload === "string") {
    try {
      return JSON.parse(payload);
    } catch {
      return null;
    }
  }
  if (typeof payload === "object") return payload as any;
  return null;
};

const humanDate = (iso?: string | Date) => (iso ? new Date(iso).toLocaleString() : "");

const timeAgo = (iso?: string | Date) => {
  if (!iso) return "";
  const diff = Date.now() - new Date(iso).getTime();
  const mins = Math.round(diff / 60000);
  if (mins < 1) return "a moment ago";
  if (mins < 60) return `${mins} min ago`;
  const hrs = Math.round(mins / 60);
  if (hrs < 24) return `${hrs} h ago`;
  const days = Math.round(hrs / 24);
  return `${days} d ago`;
};

const diffObjects = (before: any, after: any) => {
  const keys = new Set<string>([...Object.keys(before || {}), ...Object.keys(after || {})]);
  const changes: Array<{ field: string; from: any; to: any }> = [];
  keys.forEach((k) => {
    const a = before?.[k];
    const b = after?.[k];
    const same = JSON.stringify(a) === JSON.stringify(b);
    if (!same) changes.push({ field: k, from: a, to: b });
  });
  return changes;
};

const CopyBtn: React.FC<{ value?: string }> = ({ value }) => {
  if (!value) return null;
  return (
    <Button
      size="small"
      onClick={() => navigator.clipboard.writeText(value)}
      startIcon={<ContentCopyIcon fontSize="small" />}
      sx={{ ml: 1, textTransform: "none" }}
    >
      Copy
    </Button>
  );
};

const HistoryList = ({ history = [] as HistoryItem[] }) => {
  const [openItem, setOpenItem] = useState<HistoryItem | null>(null);

  if (!Array.isArray(history) || history.length === 0) {
    return <em>Nothing to see here (yet)</em>;
  }

  const openModal = (item: HistoryItem) => setOpenItem(item);
  const closeModal = () => setOpenItem(null);

  const primaryText = (item: HistoryItem) => {
    return item.type === "comment" ? (item.message ?? "") : item.detail || item.message || "";
  };

  return (
    <>
      <List
        dense
        sx={{
          mt: 2,
          borderRadius: 2,
          border: "1px solid",
          borderColor: "divider",
          overflow: "hidden",
        }}
      >
        {history.map((item, idx) => {
          const isComment = item.type === "comment";
          const username = item?.actor?.username ?? "System";
          const text = primaryText(item);
          const showMoreVisible = !!text && text.trim().length > 0;
          console.log(item.actor, "actor");

          return (
            <Box key={item.id}>
              {idx > 0 && <Divider />}
              <ListItem
                alignItems="flex-start"
                sx={{
                  py: 1.25,
                  pr: 10,
                  position: "relative",
                  "&:hover": { bgcolor: "action.hover" },
                }}
              >
                <ListItemText
                  primary={
                    <Stack direction="row" alignItems="center" spacing={1} sx={{ mb: 0.5 }}>
                      <Box
                        sx={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          width: 24,
                          height: 24,
                        }}
                      >
                        {isComment ? (
                          <ChatBubbleOutlineIcon sx={{ fontSize: 18 }} />
                        ) : (
                          <HistoryIcon sx={{ fontSize: 18 }} />
                        )}
                      </Box>

                      <Typography variant="subtitle2" sx={{ fontWeight: 700 }}>
                        <Chip
                          label={item?.actor?.username ?? "System"}
                          avatar={
                            item?.actor?.username ? <Avatar>{item?.actor?.username[0]}</Avatar> : <Avatar>A</Avatar>
                          }
                          size="small"
                          sx={{
                            fontSize: "0.75rem",
                            fontWeight: 500,
                            color: item?.actor?.avatar?.badge?.text,
                            textTransform: "capitalize",
                            backgroundColor: item?.actor?.avatar?.badge?.background,
                            "& .MuiChip-label": { px: 1.5 },
                          }}
                        />
                      </Typography>

                      <Box sx={{ flexGrow: 1 }} />

                      <Tooltip title={new Date(item.created_at).toISOString()}>
                        <Typography variant="caption" sx={{ opacity: 0.7, whiteSpace: "nowrap" }}>
                          {formatDateTime(item.created_at)}
                        </Typography>
                      </Tooltip>
                    </Stack>
                  }
                  secondary={
                    <Box>
                      <Typography
                        variant="body2"
                        sx={
                          isComment
                            ? {
                                display: "-webkit-box",
                                WebkitBoxOrient: "vertical",
                                WebkitLineClamp: 1,
                                overflow: "hidden",
                                textOverflow: "ellipsis",
                              }
                            : undefined
                        }
                      >
                        {text}
                      </Typography>
                    </Box>
                  }
                />

                {showMoreVisible && (
                  <Box
                    sx={{
                      position: "absolute",
                      right: 12,
                      bottom: 8,
                    }}
                  >
                    <Button size="small" onClick={() => openModal(item)}>
                      Show more
                    </Button>
                  </Box>
                )}
              </ListItem>
            </Box>
          );
        })}
      </List>

      <Dialog open={!!openItem} onClose={closeModal} maxWidth="sm" fullWidth>
        <DialogTitle>{openItem?.type === "comment" ? "Comment detail" : "Log detail"}</DialogTitle>
        <DialogContent dividers>
          {openItem && (
            <Stack spacing={1.5}>
              <Stack direction="row" alignItems="center" spacing={1}>
                <Chip
                  label={openItem?.actor?.username ?? "System"}
                  avatar={
                    openItem?.actor?.username ? <Avatar>{openItem?.actor?.username[0]}</Avatar> : <Avatar>A</Avatar>
                  }
                  size="small"
                  sx={{
                    fontSize: "0.75rem",
                    textTransform: "capitalize",
                    fontWeight: 500,
                    color: openItem?.actor?.avatar?.badge?.text,
                    backgroundColor: openItem?.actor?.avatar?.badge?.background,
                    "& .MuiChip-label": { px: 1.5 },
                  }}
                />
                <Box sx={{ flexGrow: 1 }} />
                <Tooltip title={new Date(openItem.created_at).toISOString()}>
                  <Typography variant="caption" sx={{ opacity: 0.7 }}>
                    {formatDateTime(openItem.created_at)}
                  </Typography>
                </Tooltip>
              </Stack>

              {openItem.message && (
                <Box
                  sx={{
                    p: 1.5,
                    borderRadius: 1,
                    bgcolor: "action.hover",
                    fontFamily: "monospace",
                    whiteSpace: "pre-wrap",
                  }}
                >
                  <Typography variant="subtitle2" sx={{ mb: 0.5 }}>
                    Detail
                  </Typography>
                  <Typography variant="body2">{openItem.message}</Typography>
                </Box>
              )}

              {openItem &&
                (() => {
                  const parsed = safeParseJSON(openItem.payload);
                  const before = parsed?.before ?? null;
                  const after = parsed?.after ?? parsed ?? null;

                  const statusRaw = after?.status ?? before?.status;
                  const paymentRaw = after?.payment_plan ?? before?.payment_plan;

                  const summary = {
                    action: openItem.type === "comment" ? "Comment" : (openItem.action ?? "Event"),
                    whenAbs: humanDate(openItem.created_at),
                    whenRel: timeAgo(openItem.created_at),
                    who: openItem?.actor?.username ?? "System",
                    bookingCode: openItem?.booking_code,
                    status: openItem?.status,
                    paymentPlan: openItem?.payment_plan,
                    singleOccupancy: openItem?.is_single_occupancy,
                    requestId: openItem?.booking_request_id,
                    cabinNumber: openItem?.cabin_number,
                  };

                  const changes = before ? diffObjects(before, after) : [];

                  return (
                    <Stack spacing={2}>
                      <Box
                        sx={{
                          p: 2,
                          borderRadius: 2,
                          border: (t) => `1px solid ${t.palette.divider}`,
                        }}
                      >
                        <Stack direction="row" justifyContent="space-between" alignItems="center" sx={{ mb: 1 }}>
                          <Stack direction="row" spacing={1} alignItems="center">
                            <Typography variant="subtitle2">Booking Data:</Typography>
                          </Stack>
                          <Typography variant="caption" sx={{ opacity: 0.75 }}>
                            {summary.whenAbs} • {summary.whenRel}
                          </Typography>
                        </Stack>

                        <Divider sx={{ mb: 1.5 }} />

                        <Grid container spacing={1.2}>
                          <Grid item xs={12} sm={6}>
                            <Typography variant="caption" sx={{ opacity: 0.7 }}>
                              Booking code
                            </Typography>
                            <Stack direction="row" alignItems="center">
                              <Typography variant="body2" sx={{ fontWeight: 600 }}>
                                {summary.bookingCode || undefined}
                              </Typography>
                              <CopyBtn value={summary.bookingCode || ""} />
                            </Stack>
                          </Grid>

                          <Grid item xs={6} sm={3}>
                            <Typography variant="caption" sx={{ opacity: 0.7 }}>
                              Status
                            </Typography>
                            <Typography variant="body2">{summary.status || undefined}</Typography>
                          </Grid>

                          <Grid item xs={6} sm={3}>
                            <Typography variant="caption" sx={{ opacity: 0.7 }}>
                              Payment plan
                            </Typography>
                            <Typography variant="body2">{summary.paymentPlan || undefined}</Typography>
                          </Grid>

                          <Grid item xs={6} sm={3}>
                            <Typography variant="caption" sx={{ opacity: 0.7 }}>
                              Agent
                            </Typography>
                            <Typography variant="body2">{String(summary.who)}</Typography>
                          </Grid>

                          <Grid item xs={6} sm={3}>
                            <Typography variant="caption" sx={{ opacity: 0.7 }}>
                              Cabin Number
                            </Typography>
                            <Typography variant="body2">{String(summary.cabinNumber)}</Typography>
                          </Grid>
                        </Grid>
                      </Box>

                      {parsed && (
                        <Box
                          sx={{
                            border: (t) => `1px solid ${t.palette.divider}`,
                            borderRadius: 2,
                            overflow: "hidden",
                          }}
                        >
                          <Accordion disableGutters>
                            <AccordionSummary>
                              <Typography variant="subtitle2">Technical details (JSON)</Typography>
                            </AccordionSummary>
                            <AccordionDetails>
                              <Box
                                sx={{
                                  bgcolor: "action.hover",
                                  p: 1.5,
                                  borderRadius: 1,
                                  fontFamily: "monospace",
                                  fontSize: "0.85rem",
                                  overflowX: "auto",
                                }}
                              >
                                <pre style={{ margin: 0 }}>{JSON.stringify(parsed, null, 2)}</pre>
                              </Box>
                            </AccordionDetails>
                          </Accordion>
                        </Box>
                      )}
                    </Stack>
                  );
                })()}
            </Stack>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={closeModal}>Close</Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default HistoryList;
