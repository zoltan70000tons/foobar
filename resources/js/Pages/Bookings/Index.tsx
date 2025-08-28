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
  Button,
  Avatar,
  TextField,
  InputAdornment,
} from "@mui/material";
import { Person } from "@mui/icons-material";
import MuiTable from "@/Components/tables/MuiTable";
import { CabinStatus, CabinStatusColor, CabinStatusReduced } from "@/enums/CabinStatus";
import { TagEnum, TagEnumStyles } from "@/enums/TagEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import SnackbarAlert from "@/Components/SnackbarAlert";
import { Permissions } from "@/enums/PermissionEnum";
import { Visibility, Clear } from "@mui/icons-material";
import UserSelectorModal from "@/Components/UserSelectorModal";
import NewBookingModal from "./NewBookingModal";
import { PageProps } from "@/types";
// import LoadingOverlay from "@/Components/LoadingOverlay";
import debounce from "lodash/debounce";
import axios from "axios";
import NewReleasesIcon from "@mui/icons-material/NewReleases";
import HourglassBottomIcon from "@mui/icons-material/HourglassBottom";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";
import CancelIcon from "@mui/icons-material/Cancel";
import { BookingStatusColor, BookingStatusEnum } from "@/enums/StatusEnum";
import SearchIcon from "@mui/icons-material/Search";
import { Autocomplete } from "@mui/material";
import LockedByAgent from "./partials/LockedByAgent";

//Helpers
import { formatDate, formatCurrency } from "@/Helpers/stringUtils";

// reverb
import '@/echo';
import { ta } from "date-fns/locale";

const Index = ({
  auth,
  event,
  // bookings,
  // newBookings,
  // inProgressBookings,
  // uploadedBookings,
  // cancelledBookings,
  users,
  cabinTypes,
  cabinCategories,
  //errors,
  tags,
  tabIndex,
}: PageProps & { tab: string; data: any; event: any; tabIndex: number }) => {
  const { hasPermission } = usePermissions();
  // const [selectedTab, setSelectedTab] = useState<number>(1);

  const [openDialog, setOpenDialog] = useState(false);
  const [keyword, setKeyword] = useState("");
  const [openModal, setOpenModal] = useState(false);
  const [selectedUserId, setSelectedUserId] = useState<string | null>(null);
  const [selectedUsers, setSelectedUsers] = useState<{ id: string, username: string }[]>([]);
  const [selectedBookingId, setSelectedBookingId] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [selectedTab, setSelectedTab] = useState<number>(tabIndex);
  const [inputValue, setInputValue] = useState("");
  const [searchTerm, setSearchTerm] = useState("");
  const [tableKey, setTableKey] = useState(0);
  const [selectedTags, setSelectedTags] = useState<string[]>([]);
  const eventId = event.id;
  const [shouldReload, setShouldReload] = useState(false);

  console.log(tags)


  useEffect(() => {

    if (shouldReload) {
      setShouldReload(false);
      setTableKey((prev) => prev + 1);
    }
  }, [shouldReload]);

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
          setShouldReload(true);
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

  // const handleTabChange = (e: React.SyntheticEvent, newValue: number) => {
  //   e.preventDefault();

  //   router.get(
  //     route("bookings.index", { id: eventId }),
  //     { tab: newValue },
  //     {
  //       preserveScroll: true,
  //       preserveState: true,
  //     },
  //   );
  // };

  const handleTabChange = (e: React.SyntheticEvent, newValue: number) => {
    e.preventDefault();
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

  const handleViewClick = (row: any) => {
    if (!eventId || !row?.booking_code) {
      console.error("Missing parameters: eventId or bookingCode is undefined.");
      return;
    }

    const url = `/events/${event.id}/bookings/${row.booking_code}`;

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

  const getTagStyle = (rawTag: string) => {
    const normalized = rawTag.trim().toUpperCase();

    const match = Object.values(TagEnum).find((enumValue) => enumValue.toUpperCase() === normalized);
    if (match) {
      return TagEnumStyles[match as TagEnum];
    }


    // fallback
    return {
      label: normalized,
      color: "#9e9e9e",
    };
  };

  const bookingColumns = useMemo(
    () => [
      {
        header: "Booking Date",
        accessor: "created_at",
        sortable: true,
        draw: (row: any) => (
          <>
            {formatDate(row.created_at)}
          </>
        ),
      },
      {
        header: "Next Payment",
        accessor: "longestDueDateInstallment",
        sortable: true,
        dateRange: true,
        width: "15%",
        draw: (row: { longestDueDateInstallment?: string | null }) => {
          const today = new Date();
          const longestDueDate = row?.longestDueDateInstallment ? new Date(row.longestDueDateInstallment) : null;

          if (!longestDueDate) {
            return <>-</>;
          }

          const twentyFiveDaysFromNow = new Date(today);
          twentyFiveDaysFromNow.setDate(today.getDate() + 25);

          const oneDayFromNow = new Date(today);
          oneDayFromNow.setDate(today.getDate() + 1);

          const fiveDaysFromNow = new Date(today);
          fiveDaysFromNow.setDate(today.getDate() + 5);

          let status: "default" | "success" | "warning" | "error" = "default"; // Default color

          if (longestDueDate > twentyFiveDaysFromNow) {
            status = "success";
          } else if (longestDueDate > oneDayFromNow && longestDueDate <= fiveDaysFromNow) {
            status = "warning";
          } else if (longestDueDate <= today) {
            status = "error";
          }

          return (
            <Chip
              label={
                row?.longestDueDateInstallment
                  ? formatDate(row.longestDueDateInstallment)
                  : "No date"
              }
              color={status}
            />
          );
        },
      },
      {
        header: "Booking Code",
        accessor: "booking_code",
      },
      {
        header: "Lead Passenger",
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
        draw: (row: any) => <>{row.balance != null && row.cost != null && <>{formatCurrency(row.balance, false, false) + " / " + formatCurrency(row.cost, false, false)}</>}</>,
      },
      {
        header: "Tags",
        accessor: "Tags",
        draw: (row: any) => (
          <Box sx={{ display: "flex", flexFlow: "column wrap", alignItems: "flex-start", gap: 0.5 }}>
            {Array.isArray(row.tags) && row.tags.length > 0 ? (
              row.tags.map((tag: string, index: number) => {
                const tagStyle = getTagStyle(tag);

                return (
                  <Chip
                    key={index}
                    label={tagStyle.label}
                    size="small"
                    sx={{
                      fontSize: "0.7rem",
                      fontWeight: 500,
                      backgroundColor: tagStyle.color,
                      color: "#fff",
                    }}
                  />
                );
              })
            ) : (
              <em>No Tags</em>
            )}
          </Box>
        ),
      },

      {
        header: "Assigned To",
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
            <Box
              sx={{
                position: "relative",
                display: "flex",
                flexDirection: "column",
                gap: 1,
              }}
            >
              {hasPermission(Permissions.ViewCabins) && (
                <Button
                  variant="outlined"
                  onClick={() => handleViewClick(row)}
                  color="primary"
                >
                  <Visibility />
                </Button>
              )}

              <LockedByAgent
                bookingId={row?.id}
                currentEditingUser={row?.editingUsername}
              />
              {/* <IconButton onClick={() => handleClick(row.agent_id, row.booking_code)} size="small" color="primary">
                <Person />
              {/* {row?.editingUsername && (
                <Box component="small" sx={{ width: "10px" }} color="warning.main">
                  Being used by {row.editingUsername}
                </Box>
              )} */}
            </Box>
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
            {formatCurrency(row.passenger_balance, false, false) + " / " + formatCurrency(row.passenger_allocated_cost, false, false)}
          </div>
        ),
      },
      {
        header: "Next Payment",
        accesor: "installment_status",
        draw: (row: any) => (
          <div style={{ display: "flex", gap: "10px" }}>
            <Chip
              label={row.installment_status.fully_paid ? "Paid" : formatDate(row.installment_status.next_installment?.due_date)}
              color={row.installment_status.fully_paid ? "success" : "error"}
              size="small"
              sx={{
                fontSize: "0.7rem",
                fontWeight: 500,
              }}
            />
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

  const clearFilter = () => {
    setInputValue("");
    setSearchTerm("");
    setKeyword("");
    //setSelectedTags([]);
    setTableKey((prev) => prev + 1);
  };

  const handleFilter = () => {
    setSearchTerm(inputValue);
  };

  const customFilter = (e: React.ChangeEvent<HTMLInputElement>) => { };

  const fetchData = useCallback(async (page, rowsPerPage, filters, sort, dateRangeState) => {
    try {
      console.log(selectedTags, 'selected tags');
      const res = await axios.get(route("bookings.data", { id: event.id }), {
        params: {
          page: page + 1,
          per_page: rowsPerPage,
          sort_key: sort?.key ?? "created_at",
          sort_direction: sort?.direction ?? "asc",
          keyword: searchTerm,
          tab: selectedTab ?? 0,
          tags: selectedTags.map((tag) => tag.id).join(','),
          user_ids: selectedUsers.map((user) => user.id),
          date_range: dateRangeState,
          ...filters,
        },
      });
      return res.data;
    } catch (err) {
      throw err;
    }
  }, [event.id, searchTerm, selectedTab, selectedTags, selectedUsers, tableKey]);




  const bookingTabs = [
    {
      status: BookingStatusEnum.NEW,
      label: "NEW BOOKINGS",
      icon: <NewReleasesIcon />,
    },
    {
      status: BookingStatusEnum.ON_HOLD,
      label: "IN PROGRESS",
      icon: <HourglassBottomIcon />,
    },
    {
      status: BookingStatusEnum.UPLOADED,
      label: "UPLOADED TO MANIFEST",
      icon: <CloudUploadIcon />,
    },
    {
      status: BookingStatusEnum.CANCELLED,
      label: "CANCELLED",
      icon: <CancelIcon />,
    },
  ];





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
                  <NewBookingModal cabinTypes={cabinTypes} cabinCategories={cabinCategories} onBookingCreated={() => setShouldReload(true)} />
                </Box>
                <TextField
                  size="small"
                  name="filter"
                  value={inputValue}
                  placeholder="Search"
                  onChange={(e) => setInputValue(e.target.value)}
                  InputProps={{
                    endAdornment: (
                      <InputAdornment position="end">
                        <IconButton onClick={clearFilter} disabled={!inputValue} size="small">
                          <Clear />
                        </IconButton>
                        <IconButton
                          onClick={() => {
                            handleFilter(inputValue);
                          }}
                          size="small"
                        >
                          <SearchIcon />
                        </IconButton>
                      </InputAdornment>
                    ),
                  }}
                />

                <Autocomplete
                  multiple
                  size="small"
                  options={tags}
                  getOptionLabel={(option) => option.name}
                  value={selectedTags}
                  onChange={(event, newValue) => setSelectedTags(newValue)}
                  renderTags={(value, getTagProps) =>
                    value.map((option, index) => {
                      return (
                        <Chip
                          key={option.id}
                          variant="outlined"
                          label={option.name}
                          {...getTagProps({ index })}
                          sx={{
                            backgroundColor: option.color,
                            color: "#fff",
                            fontWeight: 500,
                            fontSize: "0.75rem",
                          }}
                        />
                      );
                    })
                  }

                  renderOption={(props, option) => {
                    return (
                      <Box component="li" {...props}>
                        <Chip
                          label={option.name}
                          size="small"
                          sx={{
                            backgroundColor: option.color,
                            color: "#fff",
                            fontWeight: 500,
                            mr: 1,
                          }}
                        />
                      </Box>
                    );
                  }}

                  renderInput={(params) => <TextField {...params} variant="outlined" placeholder="Filter by Tags" />}
                  sx={{ minWidth: 250 }}
                />
                {/* <IconButton
                  onClick={() => {
                    setSearchTerm(inputValue);
                    setTableKey(prev => prev + 1);
                  }}
                  color="primary"
                  size="medium"
                  sx={{ border: '1px solid #ccc', borderRadius: 1 }}
                >
                  <SearchIcon />
                </IconButton> */}

                <Autocomplete
                  multiple
                  size="small"
                  options={users}
                  getOptionLabel={(option) => option.user_name}
                  value={selectedUsers}
                  onChange={(event, newValue) => setSelectedUsers(newValue)}
                  renderInput={(params) => (
                    <TextField
                      {...params}
                      variant="outlined"
                      placeholder="Filter by Users"
                    />
                  )}
                  sx={{ minWidth: 250 }}
                />

              </Box>
            </Grid>
            <Box sx={{ mt: "1rem;" }}>
              {/* Tabs for navigation */}
              <Tabs
                value={selectedTab}
                onChange={handleTabChange}
                aria-label="Manage Bookings"
                sx={{
                  backgroundColor: "#121212",
                  "& .MuiTab-root": {
                    color: "#757575",
                    fontWeight: "bold",
                    fontSize: "0.9rem",
                    textTransform: "none",
                    minHeight: "48px",
                  },
                  "& .Mui-selected": {
                    color: BookingStatusColor[bookingTabs[tabIndex]?.status],
                  },
                  "& .MuiTabs-indicator": {
                    backgroundColor: BookingStatusColor[bookingTabs[tabIndex]?.status],
                  },
                  "& .MuiTab-root:nth-of-type(1):hover": {
                    color: BookingStatusColor[bookingTabs[0].status],
                  },
                  "& .MuiTab-root:nth-of-type(2):hover": {
                    color: BookingStatusColor[bookingTabs[1].status],
                  },
                  "& .MuiTab-root:nth-of-type(3):hover": {
                    color: BookingStatusColor[bookingTabs[2].status],
                  },
                  "& .MuiTab-root:nth-of-type(4):hover": {
                    color: BookingStatusColor[bookingTabs[3].status],
                  },
                }}
              >
                {bookingTabs.map((tab, index) => (
                  <Tab key={tab.status} icon={tab.icon} iconPosition="start" label={tab.label} value={index} />
                ))}
              </Tabs>

              {/* Display the bookings */}
              <MuiTable
                columns={bookingColumns}
                data={[]}
                subColumns={subColumns}
                showCheckBox={false}
                showSubCheckBox={false}
                // showCustomFilter={true}
                onCustomFilter={customFilter}
                serverSidePagination={true}
                fetchData={fetchData}
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
