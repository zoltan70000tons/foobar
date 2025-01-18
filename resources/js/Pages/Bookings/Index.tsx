import React, { useState, useMemo, useEffect } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";
import {
  Box,
  Container,
  Grid,
  IconButton,
  Toolbar,
  Typography,
  Tabs,
  Tab,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
  Button,
  Avatar,
} from "@mui/material";
import MuiTable from "@/Components/tables/MuiTable";
import {
  CabinStatus,
  CabinStatusColor,
  CabinStatusReduced,
} from "@/enums/CabinStatus";
import { TagEnum } from "@/enums/TagEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import LoadingOverlay from "@/Components/LoadingOverlay";
import SnackbarAlert from "@/Components/SnackbarAlert";
import { Permissions } from "@/enums/PermissionEnum";
import { Visibility } from "@mui/icons-material";
import { current } from "@reduxjs/toolkit";
import PersonIcon from '@mui/icons-material/Person';
import UserSelectorModal from "@/Components/UserSelectorModal";

const Index = ({
  auth,
  event,
  newBookings,
  inProgressBookings,
  uploadedBookings,
  cancelledBookings,
  users,
  errors,
}: PageProps & { tab: string; data: any }) => {

  const { hasPermission } = usePermissions();
  const [selectedTab, setSelectedTab] = useState(0);

  const [openDialog, setOpenDialog] = useState(false);
  const [keyword, setKeyword] = useState("");
  const [openModal, setOpenModal] = useState(false);
  const [selectedUserId, setSelectedUserId] = useState<string | null>(null);
  const [selectedBookingId, setSelectedBookingId] = useState<string | null>(null);
  

  const handleOpenModal = (userId: string | null, booking_id : string | null) => {
    setSelectedBookingId(booking_id);
    setSelectedUserId(userId);
    setOpenModal(true);
  };

  const handleCloseModal = () => {
    setOpenModal(false);
  };

  const handleSave = (userId: string | null) => {
    if (!userId) return;
    router.put(
      route("bookings.assignAgent", { id:event.id }),
      { agent_id: userId, booking_code: selectedBookingId },
      {
        onSuccess: () => {
          setSnackbar({
            open: true,
            severity: "success",
            message: "User assigned successfully.",
          });
          setOpenModal(false);
        },
        onError: (errors) => {
          setSnackbar({
            open: true,
            severity: "error",
            message: "There was an error assigning the user.",
          });
        },
        preserveScroll: true,
      }
    );
  };
  const handleTabChange = (event: React.ChangeEvent<{}>, newValue: number) => {
    setSelectedTab(newValue);
  };

  const [snackbar, setSnackbar] = useState({
    open: false,
    severity: "success",
    message: "",
  });

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
  };

  const handleViewClick = (row) => {
    if (!event?.id || !row?.booking_code) {
      console.error("Missing parameters: eventId or bookingCode is undefined.");
      return;
    }
  
    const url = `/events/${event.id}/bookings/${row.booking_code}`;
    window.location.href = url; // Redirige al usuario
  };

  // const handleViewClick = (row) => {
  //   router.get(
  //     route("bookings.show", { id: event.id, booking_code: row.booking_code })
  //   );
  // };

  const handleClick = (agent_id : String, booking_id: String) => {
    handleOpenModal(agent_id, booking_id);

  }

  const bookingColumns = useMemo(
    () => [
      {
        header: "Id",
        accessor: "id",
      },
      {
        header: "Booking code",
        accessor: "booking_code",
      },
      {
        header: "Lead passenger",
        accessor: "fullName",
      },
      {
        header: "Type",
        accessor: "cabinType",
        draw: (row) => <>{row.cabin?.cabin_type?.cabin_type}</>,
      },
      {
        header: "Balance",
        accessor: "balance",
        draw: (row) => (
          <>
            {row.balance != null && row.cost != null && (
              <>{row.balance + " / " + row.cost}</>
            )}
          </>
        ),
      },
      {
        header: "Tags",
        accessor: "Tags",
        draw: (row) => (
          <Box sx={{ display: "inline-flex", gap: 0.5 }}>
            {Array.isArray(row.tags) && row.tags.length > 0 ? (
              row.tags.map((tag: string) => (
                <Chip
                  key={tag}
                  label={tag}
                  size="small"
                  sx={{ margin: "auto", fontSize: "0.7rem", fontWeight: "400" }}
                />
              ))
            ) : (
              <em>No Tags</em>
            )}
          </Box>
        ),
      },

      {
        header: "Assigned to",
        accessor: "agent_id",
        draw: (row) => {
          const agent = row?.agent;
          const label = agent?.username ? agent.username : <em>Not Assigned</em>;
          const avatar = agent?.username ? <Avatar>{agent.username[0]}</Avatar> : <Avatar>N</Avatar>;

          return (
            <Box sx={{ display: "inline-flex", gap: 0.5 }}>
              <Chip
                key={row.id}
                label={label}
                avatar={avatar}
                onClick={() => handleClick(agent?.id, row.booking_code)}
                size="small"
                color={agent?.username ? "primary" : "default"}
                sx={{ margin: "auto", fontSize: "0.7rem", fontWeight: "400" }}
              />
            </Box>
          );
        }
      }
      ,

      {
        header: "Actions",
        accessor: "",
        disableFilter: true,
        draw: (row) => (
          <div style={{ display: "flex", gap: "10px" }}>
            {hasPermission(Permissions.ViewCabins) && (
              <Visibility
                onClick={() => handleViewClick(row)}
                style={{ cursor: "pointer" }}
              />
            )}
          </div>
        ),
      },
    ],
    []
  );

  const subColumns = useMemo(
    () => [
      // {
      //   header: "Id",
      //   accessor: "id",
      // },
      {
        header: "Passenger",
        accessor: "full_name",
      },
      {
        header: "Email",
        accessor: "email",
      },
      {
        header: "Phone",
        accessor: "phone",
      },
      {
        header: "Country Of Residence",
        accessor: "country",
      },
      {
        header: "Balance",
        accesor: "passenger_allocated_cost",
        draw: (row) => (
          <div style={{ display: "flex", gap: "10px" }}>
            {row.passenger_balance + " / " + row.passenger_allocated_cost}
          </div>
        ),
      },
      {
        header: "Payment Method",
        accessor: "payment_method",
      },
    ],
    []
  );

  const customFilter = (e) => {
    let currentKeyword = e.target.value;
    setKeyword(currentKeyword);
    router.get(
      `/events/${event.id}/bookings`,
      { keyword: currentKeyword },
      { preserveState: true, replace: true }
    );
  };
  return (
    <AuthenticatedLayout user={auth.user} header={"Bookings"}>
      <Head title="Bookings" />
      <Toolbar sx={{ mt: 8 }}>
        <IconButton edge="start" color="inherit" aria-label="menu">
          <img src={event.image} alt="Logo" style={{ height: 40 }} />
        </IconButton>
        <Typography variant="h6" style={{ flexGrow: 1 }}>
          {event.name}
        </Typography>
      </Toolbar>
      <Container maxWidth="lg" sx={{ mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Box>
              {/* Tabs for navigation */}
              <Tabs
                value={selectedTab}
                onChange={handleTabChange}
                aria-label="manage inventory and categories"
              >
                <Tab label="NEW BOOKINGS" />
                <Tab label="IN PROGRESS" />
                <Tab label="UPLOADED TO MANIFEST" />
                <Tab label="CANCELLED" />
              </Tabs>

              <Box
                sx={{ display: selectedTab === 0 ? "block" : "none", mt: 2 }}
              >
                <MuiTable
                  columns={bookingColumns}
                  data={newBookings}
                  subColumns={subColumns}
                  showCheckBox={false}
                  showSubCheckBox={false}
                  showCustomFilter={true}
                  onCustomFilter={customFilter}
                />
              </Box>

              <Box
                sx={{ display: selectedTab === 1 ? "block" : "none", mt: 2 }}
              >
                <MuiTable
                  columns={bookingColumns}
                  data={inProgressBookings}
                  subColumns={subColumns}
                  showCheckBox={false}
                  showSubCheckBox={false}
                  showCustomFilter={true}
                  onCustomFilter={customFilter}
                />
              </Box>

              <Box
                sx={{ display: selectedTab === 2 ? "block" : "none", mt: 2 }}
              >
                <MuiTable
                  columns={bookingColumns}
                  data={uploadedBookings}
                  subColumns={subColumns}
                  showCheckBox={false}
                  showSubCheckBox={false}
                  showCustomFilter={true}
                  onCustomFilter={customFilter}
                />
              </Box>
            </Box>

            <Box sx={{ display: selectedTab === 3 ? "block" : "none", mt: 2 }}>
              <MuiTable
                columns={bookingColumns}
                data={cancelledBookings}
                subColumns={subColumns}
                showCheckBox={false}
                showSubCheckBox={false}
                showCustomFilter={true}
                onCustomFilter={customFilter}
              />
            </Box>

            <SnackbarAlert
              open={snackbar.open}
              severity={snackbar.severity}
              message={snackbar.message}
              onClose={handleCloseSnackbar}
            />

            <UserSelectorModal
              open={openModal}
              onClose={handleCloseModal}
              onSave={handleSave}
              initialUserId={selectedUserId}
              users={users}
            />
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
