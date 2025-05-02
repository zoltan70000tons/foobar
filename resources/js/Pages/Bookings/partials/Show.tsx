import React, { useEffect, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router, usePage } from "@inertiajs/react";
import { PageProps } from "@/types";
import {
  Avatar,
  Box,
  Container,
  Grid,
  Typography,
  Toolbar,
  useTheme,
  Alert,
  Select,
  MenuItem,
  alpha,
  Button,
  Divider,
  FormGroup,
  FormControlLabel,
  Switch,
  AlertTitle,
  Drawer,
} from "@mui/material";
import CommentIcon from "@mui/icons-material/Comment";
import { usePermissions } from "@/Providers/PermissionContext";
import dayjs from "dayjs";
import "dayjs/locale/en";
import localizedFormat from "dayjs/plugin/localizedFormat";
import Status from "./Status";
import Detail from "./Details";
import Passengers from "./Passengers";
import Payment from "./Payment";
import BookingSidebar from "./BookingSidebar";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import AdjustmentForm from "./AdjustmentForm";
import LoadingOverlay from "@/Components/LoadingOverlay";
import { BookingSessionTimer } from "./BookingSessionTimer";
import '@/echo';


const Show = ({ auth, event, booking, users, cabinTypes, cabinCategories, adjustments }: PageProps) => {
  const [editMode, setEditMode] = useState(false);
  const [locked, setLocked] = useState(booking.locked_by ? true : false);
  const [isSidebarOpen, setSidebarOpen] = useState(false);
  const { hasPermission } = usePermissions();
  const [comments, setComments] = useState(booking.comments || []);
  const [loading, setLoading] = useState(false);
  const [logs, setLogs] = useState(booking.logs || []);
  const [isDynamicLocked, setIsDynamicLocked] = useState(false);
  const [isOverlayOpen, setIsOverlayOpen] = useState(false);
  const theme = useTheme();
  dayjs.extend(localizedFormat);
  const { showSnackbar } = useSnackbar();
  const capacity = booking.cabin.category.capacity;

  const { flash } = usePage().props;

  // useEffect(() => {
  //   if (booking.locked_by && booking.locked_by.agent_id === auth.user.id) {
  //     setEditMode(true);
  //   }
  // }, []);


  console.log('!@$$$@$', auth);
  console.log('booking', booking);


  useEffect(() => {
    const channel = window.Echo.channel('booking-status');
  
    channel.listen('.BookingEditStatusUpdated', ({ agentId, bookingId, username }: any) => {
     console.log('BookingEditStatusUpdated event received:',agentId, bookingId, username);
      if (
        bookingId === bookingId && 
        username !== null &&
        auth.user.id !== agentId
      ) {
        setIsDynamicLocked(true);
        setIsOverlayOpen(true);
      }
    });
  
    return () => {
      window.Echo.leave('booking-status');
    };
  }, []);


  console.log('isDynamicLocked', isDynamicLocked);

  const handleEditChange = (e) => {
    setLoading(true);
    router.get(
      route("bookings.editMode"),
      {
        booking_id: booking.id,
        lock: e.target.checked ? "1" : "0",
        event_id: event.id,
      },
      {
        onSuccess: (response) => {
          if (response.success) {
            setEditMode(e.target.checked);
            setLoading(false);
          }
        },
        onError: (error) => {
          setLoading(false);
        },
      },
    );
  };


  // handle reassign booking
  const handleReAsssign = () => {
    setLoading(true);
    router.get(
      route("bookings.reAssign"),
      {
        booking_id: booking.id,
        event_id: event.id,
      },
      {
        onSuccess: (response) => {
          if (response.success) {
            setEditMode(false);
            setLoading(false);
          }
        },
        onError: (error) => {
          setLoading(false);
        },
      },
    );
  };

  const toggleSidebar = () => setSidebarOpen(!isSidebarOpen);

  useEffect(() => {
    if (!flash) return;

    if (flash.error) {
      showSnackbar(flash.error, "error");
    }
    if (flash.success) {
      showSnackbar(flash.success, "success");
    }
  }, [flash]);

  const handleAddComment = (comment: string) => {
    router.post(
      route("bookings.addComment", {
        id: event.id,
      }),
      {
        comment: comment,
        booking_id: booking.id,
      },
      {
        onSuccess: (page) => {
          const newComment = page.props.booking.comments.slice(-1)[0];
          setComments((prevComments) => [...prevComments, newComment]);
          showSnackbar("Comment added successfully!", "success");
        },
        onError: (errors) => {
          showSnackbar("Error adding comment:", "error");
          console.error("Error adding comment:", errors);
        },
        preserveScroll: true,
        preserveState: true,
      },
    );
  };

  const handleAddAdjustment = (data) => {
    router.post(
      route("bookings.addAdjustment", {
        id: event.id,
      }),
      {
        code: data.code,
        type: data.type,
        operation: data.operation,
        value: data.value,
        restrictions: null,
        event_id: event.id,
        booking_id: booking.id,
      },
    );
  };

  const handleBack = () => {
    setLoading(true);

    router.visit(route("bookings.index", { id: event.id }), {
      replace: true,
      preserveScroll: true,
      preserveState: false,
    });
  };


  return (
    <AuthenticatedLayout user={auth.user} header={"Booking Detail"}>
      <Head title="Booking " />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        {isOverlayOpen && (
          <Box
            sx={{
              position: "fixed",
              top: 0,
              left: 0,
              width: "100%",
              height: "100%",
              backgroundColor: alpha("#000", 0.5),
              zIndex: 9999,
              display: "flex",
              justifyContent: "center",
              alignItems: "center",
            }}
            >
            <Box
              sx={{
                backgroundColor: "#000",
                padding: 4,
                borderRadius: 2,
                boxShadow: 3,
                width: { xs: "90%", sm: "60%", md: "40%" },
                maxWidth: 600,
                mx: "auto",
              }}
            >
              <Typography variant="h6" gutterBottom>
                Booking is being edited by another agent
              </Typography>
              <Typography variant="body1" gutterBottom>
                This booking is currently being edited by another agent. Please wait until they finish editing or
                contact them for more information.
              </Typography>
              <Button
                variant="outlined"
                color="secondary"
                onClick={() => setIsOverlayOpen(false)}
                sx={{ mt: 2 }}
              >
                Close
              </Button>
            </Box>
          </Box>
        )}
        {editMode && (<BookingSessionTimer lockedAt={booking?.locked_by?.time} sessionDurationMinutes={10} eventId={event.id} bookingId={booking.id} />)}
        <Box display="flex" justifyContent="space-between" alignItems="center">
          <Grid item xs={6}>
            <FormGroup>
              {booking.status === "CANCELLED" ? (
                <Alert severity="error" sx={{ mb: 2 }}>
                  <AlertTitle>Info</AlertTitle>
                  This booking has been cancelled and cannot be edited.
                </Alert>
              ) : (
                <FormControlLabel
                  control={
                    <Switch
                      checked={
                        editMode || 
                        (!isDynamicLocked && booking.locked_by && booking.locked_by.agent_id === auth.user.id)
                     
                      }
                      onChange={handleEditChange}
                      disabled={!isDynamicLocked && booking.locked_by && booking.locked_by.agent_id !== auth.user.id}
                      sx={{
                        width: 68,
                        height: 38,
                        '& .MuiSwitch-thumb': {
                          width: 24,
                          height: 24,
                          marginTop: '-2px',
                          marginLeft: '2px',
                        },
                        '& .MuiSwitch-track': {
                          borderRadius: 8,
                        },
                      }}
                    />
                  }
                  label={
                    <Typography sx={{ fontSize: '1.1rem' }}>
                      Edit Mode
                    </Typography>
                  }
                />
              )}
            </FormGroup>
          </Grid>

          <Grid item xs={6} sx={{ textAlign: "right" }}>
            <Button variant="outlined" color="secondary" onClick={handleBack} sx={{ mb: 2, mr: 2 }}>
              Back
            </Button>
            <Button
              variant="outlined"
              color="secondary"
              startIcon={<CommentIcon />}
              onClick={toggleSidebar}
              sx={{ mb: 2 }}
            >
              View Comments & Logs
            </Button>
          </Grid>
        </Box>

        {(isDynamicLocked || (
          booking.locked_by &&
          booking.locked_by.agent_id !== auth.user.id &&
          booking.status !== "CANCELLED"
        )) && (
          <Alert severity="warning" sx={{ mb: 2 }}>
            <AlertTitle>Warning</AlertTitle>
            This booking request is currently being edited by another agent, so all editable fields have been disabled.
            If you now what you are doing, you can fetch this booking by clicking bellow button.
            <Button
              variant="outlined"
              color="secondary"
              onClick={() => handleReAsssign()}
              sx={{ 
                mt: 2, 
                display: 'block',
              }}
            >
              Fetch Booking
            </Button>
          </Alert>
        )}
        <Status event={event} editMode={editMode} booking={booking} users={users} />
        <Detail
          event={event}
          booking={booking}
          editMode={editMode}
          cabinTypes={cabinTypes}
          cabinCategories={cabinCategories}
        />
        <Passengers booking={booking} editMode={editMode} setLoading={setLoading} />
        <AdjustmentForm booking={booking} editMode={editMode} onSubmit={handleAddAdjustment} list={adjustments} />
        <Payment booking={booking} editMode={editMode} />
        {/* <Payment booking={booking} passenger={null} number={2} count={capacity} editMode={editMode} /> */}
        {/* <ActionList editMode={editMode} /> */}
        <BookingSidebar
          isOpen={isSidebarOpen}
          toggleSidebar={toggleSidebar}
          logs={booking.logs}
          comments={comments}
          onAddComment={handleAddComment}
        />
        {/* <LoadingOverlay open={loading} /> */}
      </Container>
    </AuthenticatedLayout>
  );
};

export default Show;

