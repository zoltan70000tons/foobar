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
} from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import dayjs from "dayjs";
import "dayjs/locale/en";
import localizedFormat from "dayjs/plugin/localizedFormat";
import { Permissions } from "@/enums/PermissionEnum";
import { BookingTagEnum } from "@/enums/TagEnum";
import Status from "./Status";
import Detail from "./Details";
import Passengers from "./Passengers";
import Payment from "./Payment";
import ActionList from "./ActionList";
import Log from "./Log";
import Echo from 'laravel-echo';



const Show = ({ auth, event, booking }: PageProps) => {


  const [editMode, setEditMode] = useState(false);
  const [locked, setLocked] = useState(booking.locked_by ? true : false);
  const { hasPermission } = usePermissions();
  const theme = useTheme();
  dayjs.extend(localizedFormat);
  useEffect(() => {

  }, []);



  const logData = [
    {
      date: '28 / 04 / 2024',
      time: '2:13AM',
      user: 'LB',
      action: 'Custom message',
      description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
    },
    {
      date: '23 / 04 / 2024',
      time: '2:23AM',
      user: 'JG',
      action: 'Edited email title'
    },
    {
      date: '22 / 04 / 2024',
      time: '2:23AM',
      user: 'user',
      action: 'Created'
    }
  ];

  const handleEditChange = (e) => {
    setEditMode(e.target.checked);

    router.get(
      route('bookings.editMode', {
        booking_id: booking.id,
        lock: e.target.checked,
        event_id: event.id
      }),
      {},
      {
        only: ['booking', 'event'], // Solo estas props se actualizan
        preserveScroll: true, // Mantener el scroll
        preserveState: true, // Mantener el estado del cliente
      }
    );
  };

  console.log(booking.locked_by);



  return (
    <AuthenticatedLayout user={auth.user} header={"Events"}>
      <Head title="Booking " />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <FormGroup>
          <FormControlLabel
            control={<Switch onChange={handleEditChange} />}
            label="Edit Mode"
            disabled={locked && booking.locked_by?.agent_id !== auth.user.id}
            checked={locked && booking.locked_by?.agent_id === auth.user.id}
          />
        </FormGroup>

        {booking.locked_by && (
          <Alert severity="warning" sx={{ mb: 2 }}>
            <AlertTitle>Warning</AlertTitle>
            {booking.locked_by.agent_id === auth.user.id ? (
              "Once you finish editing, remember to exit edit mode."
            ) : (
              "This booking request is currently being edited by another agent, so all editable fields have been disabled."
            )}
          </Alert>)}
        <Status event={event} editMode={editMode} booking={booking} />
        <Detail event={event} booking={booking} editMode={editMode} />
        <Passengers passengers={booking.passengers} editMode={editMode} />
        <Payment booking={booking} passenger={null} number={1} count={4} lead={true} editMode={editMode} />
        <Payment booking={booking} passenger={null} number={2} count={4} editMode={editMode} />
        <ActionList editMode={editMode} />
        <Log logs={booking.logs} />
      </Container>
    </AuthenticatedLayout>
  );
};

export default Show;
