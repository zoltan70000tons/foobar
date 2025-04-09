import React, { useState, useMemo, useEffect, useCallback } from "react";
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
  Checkbox,
  Avatar,
  TextField,
  InputAdornment,
} from "@mui/material";
import { Person } from "@mui/icons-material";
import MuiTable from "@/Components/tables/MuiTable";
import { CabinStatus, CabinStatusColor, CabinStatusReduced } from "@/enums/CabinStatus";
import { TagEnum } from "@/enums/TagEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import SnackbarAlert from "@/Components/SnackbarAlert";
import { Permissions } from "@/enums/PermissionEnum";
import { Visibility, Clear } from "@mui/icons-material";
import UserSelectorModal from "@/Components/UserSelectorModal";
import NewBookingModal from "./NewBookingModal";
import { PageProps } from "@/types";
// import LoadingOverlay from "@/Components/LoadingOverlay";
import debounce from "lodash/debounce";

const Index = ({
  auth,
  event,
  bookings,
  newBookings,
  inProgressBookings,
  uploadedBookings,
  cancelledBookings,
  users,
  cabinTypes,
  cabinCategories,
  errors,
  tabIndex,
}: PageProps & { tab: string; data: any; event: any; tabIndex: number }) => {
  const { hasPermission } = usePermissions();
  // const [selectedTab, setSelectedTab] = useState<number>(1);

  const [openDialog, setOpenDialog] = useState(false);
  const [keyword, setKeyword] = useState("");
  const [openModal, setOpenModal] = useState(false);
  const [selectedUserId, setSelectedUserId] = useState<string | null>(null);
  const [selectedBookingId, setSelectedBookingId] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const eventId = event.id;

  // const getStatusFromTab = (tabIndex: number) => {
  //   const statuses = ['NEW', 'ON HOLD', 'UPLOADED', 'CANCELLED'];
  //   return statuses[tabIndex] || 'NEW';
  // };

  const handleOpenModal = (userId: string | null, booking_id: string | null) => {
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
      route("bookings.assignAgent", { id: event.id }),
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
      },
    );
  };

  // const handleTabChange = (event: React.ChangeEvent<{}>, newValue: number) => {
  //   setSelectedTab(newValue);
  // };

  const handleTabChange = (e: React.SyntheticEvent, newValue: number) => {
    e.preventDefault();

    router.get(
      route("bookings.index", { id: eventId }),
      { tab: newValue },
      {
        preserveScroll: true,
        preserveState: true,
      },
    );
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

  const handleViewClick = (row: any) => {
    if (!eventId || !row?.booking_code) {
      console.error("Missing parameters: eventId or bookingCode is undefined.");
      return;
    }

    const url = `/events/${event.id}/bookings/${row.booking_code}`;

    // TODO: JG - Leo can we use visit instead of location.href?
    // https://inertiajs.com/manual-visits

    // window.location.href = url; // Redirige al usuario
    router.visit(url);
  };

  const handleClick = (agent_id: string, booking_id: string) => {
    handleOpenModal(agent_id, booking_id);
  };

  const toCamelCase = (str: string) => {
    return str
      .toLowerCase()
      .split(" ")
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(" ");
  };

  const bookingColumns = useMemo(
    () => [
      {
        header: "Booking Date",
        accessor: "created_at",
        sortable: true,
        draw: (row: any) => (
          <>
            {new Date(row.created_at).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" })}
          </>
        ),
      },
      {
        header: "Booking code",
        accessor: "booking_code",
      },
      {
        header: "Lead passenger",
        accessor: "fullName",
        sortable: true,
      },
      {
        header: "Type",
        accessor: "cabinType",
        sortable: true,
        draw: (row: any) => <>{row.cabin?.cabin_type?.cabin_type}</>,
      },
      {
        header: "Balance",
        accessor: "balance",
        draw: (row: any) => <>{row.balance != null && row.cost != null && <>{row.balance + " / " + row.cost}</>}</>,
      },
      {
        header: "Tags",
        accessor: "Tags",
        draw: (row: any) => (
          <Box sx={{ display: "flex", flexFlow: "column wrap", alignItems: "flex-start", gap: 0.5 }}>
            {Array.isArray(row.tags) && row.tags.length > 0 ? (
              row.tags.map((tag: string, index: number) => (
                <Chip key={index} label={tag} size="small" sx={{ fontSize: "0.7rem", fontWeight: "400" }} />
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
        sortable: true,
        draw: (row: any) => {
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
        },
      },
      {
        header: "Actions",
        accessor: "",
        disableFilter: true,
        draw: (row: any) => {
          return (
            <>
              <div style={{ display: "flex", gap: "10px" }}>
                {hasPermission(Permissions.ViewCabins) && (
                  <Visibility onClick={() => handleViewClick(row)} style={{ cursor: "pointer" }} />
                )}
              </div>
              {row?.editingUsername && (
                <Typography variant="div" color="textSecondary">Being viewed by {row.editingUsername}</Typography>
              )}
            </>
          );
        },
      },
    ],
    [],
  );

  const subColumns = useMemo(
    () => [
      {
        header: "Passenger",
        accessor: "lead_passenger",
        width: "25%",
        draw: (row: any) => (
          <Box display="flex" alignItems="center" gap={1} key={row.passenger_order}>
            <Person
              titleAccess={row.lead_passenger ? "Lead Passenger" : "Passenger"}
              sx={{
                color: row.lead_passenger ? "#f39c12" : "gray",
              }}
            />
            <Typography>{toCamelCase(row.full_name)}</Typography>
          </Box>
        ),
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
        draw: (row: any) => (
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
    [],
  );

  // const handleFilter = useCallback(
  //   debounce((searchTerm) => {
  //     setLoading(true);
  //     router.post(
  //       `/events/${event.id}/bookings`,
  //       { keyword: searchTerm },
  //       {
  //         preserveState: true,
  //         replace: true,
  //         onSuccess: (data) => {
  //           if (data.props.tabIndex !== undefined) {
  //             setSelectedTab(data.props.tabIndex);
  //           }
  //         },
  //         onFinish: () => setLoading(false),
  //       },
  //     );
  //   }, 300),
  //   [],
  // );

  const handleFilter = useCallback(
    debounce((searchTerm: string) => {
      setLoading(true);
      router.get(
        route("bookings.index", { id: event.id }),
        { keyword: searchTerm, tab: tabIndex },
        {
          preserveState: true,
          preserveScroll: true,
          replace: true,
          onFinish: () => setLoading(false),
        },
      );
    }, 300),
    [tabIndex],
  );

  const clearFilter = () => {
    setKeyword("");
    handleFilter("");
  };

  const customFilter = (e: React.ChangeEvent<HTMLInputElement>) => {
    let currentKeyword = e.target?.value as string;
    setKeyword(currentKeyword);
    handleFilter(currentKeyword);
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
          <Grid item xs={12} sx={{ textAlign: "right" }}>
            <Grid item xs={12}>
              <Box
                sx={{
                  display: "flex",
                  justifyContent: "flex-end",
                  alignItems: "stretch",
                  gap: 2,
                }}
              >
                <Box sx={{ minHeight: "40px", display: "flex", alignItems: "center" }}>
                  <NewBookingModal cabinTypes={cabinTypes} cabinCategories={cabinCategories} />
                </Box>
                <TextField
                  size="small"
                  name="filter"
                  value={keyword}
                  placeholder="Search"
                  sx={{ minHeight: "40px" }}
                  onChange={customFilter}
                  InputProps={{
                    endAdornment: keyword && ( // 🔥 Solo muestra la "X" si hay texto
                      <InputAdornment position="end">
                        <IconButton onClick={clearFilter} size="small">
                          <Clear />
                        </IconButton>
                      </InputAdornment>
                    ),
                  }}
                />
              </Box>
            </Grid>
            <Box>
              {/* Tabs for navigation */}
              <Tabs value={tabIndex} onChange={handleTabChange} aria-label="manage inventory and categories">
                <Tab label="NEW BOOKINGS" />
                <Tab label="IN PROGRESS" />
                <Tab label="UPLOADED TO MANIFEST" />
                <Tab label="CANCELLED" />
              </Tabs>

              {/* Display the bookings */}
              <MuiTable
                columns={bookingColumns}
                data={bookings}
                subColumns={subColumns}
                showCheckBox={false}
                showSubCheckBox={false}
                // showCustomFilter={true}
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
        {/* <LoadingOverlay open={loading} /> */}
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
