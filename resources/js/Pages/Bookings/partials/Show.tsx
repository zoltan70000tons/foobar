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

const Show = ({ auth, event, booking, users}: PageProps) => {
  const [editMode, setEditMode] = useState(false);
  const [locked, setLocked] = useState(booking.locked_by ? true : false);
  const [isSidebarOpen, setSidebarOpen] = useState(false); 
  const { hasPermission } = usePermissions();
  const [comments, setComments] = useState(booking.comments || []); // Estado inicial
  const [logs, setLogs] = useState(booking.logs || []);
  const theme = useTheme();
  dayjs.extend(localizedFormat);

  console.log(booking);

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
          // Actualizamos los comentarios con los datos del backend
          const newComment = page.props.booking.comments.slice(-1)[0]; // Último comentario agregado
          setComments((prevComments) => [...prevComments, newComment]);
        },
        onError: (errors) => {
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
          <FormControlLabel
            control={<Switch onChange={handleEditChange} />}
            label="Edit Mode"
          />
        </FormGroup>

        {booking.locked_by && (
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
        <Detail event={event} booking={booking} editMode={editMode} />
        <Passengers passengers={booking.passengers} editMode={editMode} />
        <Payment
          booking={booking}
          passenger={null}
          number={1}
          count={4}
          lead={true}
          editMode={editMode}
        />
        <Payment booking={booking} passenger={null} number={2} count={4} editMode={editMode} />
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
