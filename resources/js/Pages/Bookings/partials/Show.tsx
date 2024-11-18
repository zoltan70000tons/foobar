import React, { useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
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


const Show = ({ auth, event, booking }: PageProps) => {
  console.log(booking);
  const { hasPermission } = usePermissions();
  const theme = useTheme();
  dayjs.extend(localizedFormat);

  const [editMode, setEditMode] = useState(false);
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
    console.log(e.target.checked);
  }

  return (
    <AuthenticatedLayout user={auth.user} header={"Events"}>
      <Head title="Booking " />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <FormGroup>
          <FormControlLabel control={<Switch onChange={handleEditChange} />} label="Edition Mode" />
        </FormGroup>
        <Status event={event} editMode={editMode} booking={booking} />
        <Detail booking={booking} editMode={editMode} />
        <Passengers passengers={booking.passengers} editMode={editMode} />
        <Payment booking={booking} passenger={null} number={1} count={4} lead={true} editMode={editMode}  />
        <Payment booking={booking} passenger={null} number={2} count={4} editMode={editMode} />
        <ActionList editMode={editMode} />
        <Log logs={booking.logs} />
      </Container>
    </AuthenticatedLayout>
  );
};

export default Show;
