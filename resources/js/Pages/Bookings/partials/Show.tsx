import React, { useEffect, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";
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
  Button,
  Divider,
  FormGroup,
  FormControlLabel,
  Switch,
  AlertTitle,
  Drawer,
  IconButton,
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
import ActionList from "./ActionList";
import Log from "./Log";
import BookingSidebar from "./BookingSidebar";
import SnackbarAlert from "@/Components/SnackbarAlert";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";

const Show = ({ auth, event, booking, users, cabinTypes, cabinCategories }: PageProps) => {
  const [editMode, setEditMode] = useState(false);
  const [locked, setLocked] = useState(booking.locked_by ? true : false);
  const [isSidebarOpen, setSidebarOpen] = useState(false);
  const { hasPermission } = usePermissions();
  const [comments, setComments] = useState(booking.comments || []); // Estado inicial
  const [logs, setLogs] = useState(booking.logs || []);
  const theme = useTheme();
  dayjs.extend(localizedFormat);
  const { showSnackbar } = useSnackbar();

  const capacity = booking.cabin.cabin_category.capacity;
  console.log(capacity); 
  const handleEditChange = (e) => {
    setEditMode(e.target.checked);
    router.get(
      route("bookings.editMode", {
        booking_id: booking.id,
        lock: e.target.checked,
        event_id: event.id,
      }),
      {},
      {
        only: ["booking", "event"],
        preserveScroll: true,
        preserveState: true,
      }
    );
  };

  const toggleSidebar = () => setSidebarOpen(!isSidebarOpen);



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
      }
    );
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Booking Detail"}>
      <Head title="Booking " />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <FormGroup>
          {booking.is_cancelled ? (
            <Alert severity="error" sx={{ mb: 2 }}>
              <AlertTitle>Info</AlertTitle>
              This booking has been cancelled and cannot be edited.
            </Alert>
          ) : (
            <FormControlLabel
              control={
                <Switch
                  checked={editMode || (booking.locked_by && booking.locked_by.agent_id === auth.user.id)}
                  onChange={handleEditChange}
                  disabled={booking.locked_by && booking.locked_by.agent_id !== auth.user.id}
                />
              }
              label="Edit Mode"
            />
          )}
        </FormGroup>

        {booking.locked_by && !booking.is_cancelled && (
          <Alert severity="warning" sx={{ mb: 2 }}>
            <AlertTitle>Warning</AlertTitle>
            {booking.locked_by.agent_id === auth.user.id
              ? "Once you finish editing, remember to exit edit mode."
              : "This booking request is currently being edited by another agent, so all editable fields have been disabled."}
          </Alert>
        )}

        <Button
          variant="contained"
          color="primary"
          startIcon={<CommentIcon />}
          onClick={toggleSidebar}
          sx={{ mb: 2 }}
        >
          View Comments & Logs
        </Button>

        <Status event={event} editMode={editMode} booking={booking} users={users} />
        <Detail event={event} booking={booking} editMode={editMode} cabinTypes={cabinTypes} cabinCategories={cabinCategories} />
        <Passengers booking={booking} editMode={editMode} />
        <Payment
          booking={booking}
          passenger={null}
          number={1}
          count={4}
          lead={true}
          editMode={editMode}
        />
        <Payment booking={booking} passenger={null} number={2} count={capacity} editMode={editMode} />
        <ActionList editMode={editMode} />
        <BookingSidebar
          isOpen={isSidebarOpen}
          toggleSidebar={toggleSidebar}
          logs={booking.logs}
          comments={comments}
          onAddComment={handleAddComment}
        />
      </Container>
    </AuthenticatedLayout>
  );
};

export default Show;
