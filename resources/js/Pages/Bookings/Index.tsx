import React, { useState, useMemo, useEffect, useCallback } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router, Link } from "@inertiajs/react";
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
import { blue } from "@mui/material/colors";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { Person } from "@mui/icons-material";
import MuiTable from "@/Components/tables/MuiTable";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import { Visibility, Clear } from "@mui/icons-material";
import UserSelectorModal from "@/Components/UserSelectorModal";
import NewBookingModal from "./NewBookingModal";
import { PageProps } from "@/types";
import NewReleasesIcon from "@mui/icons-material/NewReleases";
import HourglassBottomIcon from "@mui/icons-material/HourglassBottom";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";
import CancelIcon from "@mui/icons-material/Cancel";
import PersonAddIcon from "@mui/icons-material/PersonAdd";
import { BookingStatusColor, BookingStatusEnum } from "@/enums/StatusEnum";
import SearchIcon from "@mui/icons-material/Search";
import { Autocomplete } from "@mui/material";
import LockedByAgent from "./partials/LockedByAgent";
import { usePage } from "@inertiajs/react";
import "@inertiajs/core";

//Helpers
import { formatDate, formatCurrency } from "@/Helpers/stringUtils";

// reverb
import "@/echo";
import { User } from "@/interfaces/User";
import { CabinCategory } from "@/interfaces/CabinCategory";
import { Errors } from "@inertiajs/core";
import axios from "axios";
import { Event } from "@/interfaces/Event";
import { Booking } from "@/types/booking";
import { Passenger } from "@/interfaces/Passenger";

type Filters = Record<string, string | number | boolean>;

type DateRangeState = Record<
  string,
  {
    startDate?: string;
    endDate?: string;
  }
>;

type Tag = {
  id: string;
  name: string;
  color: string;
  description: string;
  type: string;
  sort_order: number;
  is_system: boolean;
  created_at: string;
  updated_at: string;
  priority: number;
};

type Props = PageProps & {
  auth: AuthProps;
  event: Event;
  users: User[];
  cabinTypes: { id: string; name: string }[];
  cabinCategories?: CabinCategory[];
  errors: Errors;
  tabIndex: number;
  tab: string;
  tags: Tag[];
};

declare module "@inertiajs/core" {
  interface PageProps {
    flash: {
      message?: string;
    };
  }
}

const Index = ({ auth, event, users, cabinTypes, cabinCategories = [], errors, tabIndex, tags }: Props) => {
  const { hasPermission } = usePermissions();
  const { flash } = usePage().props;
  const { showSnackbar } = useSnackbar();

  const [keyword, setKeyword] = useState("");
  const [openModal, setOpenModal] = useState(false);
  const [selectedUserId, setSelectedUserId] = useState<string | null>(null);
  const [selectedUsers, setSelectedUsers] = useState<{ id: string; username: string }[]>([]);
  const [selectedBookingId, setSelectedBookingId] = useState<string | null>(null);
  const [selectedTab, setSelectedTab] = useState<number>(tabIndex);
  const [inputValue, setInputValue] = useState("");
  const [searchTerm, setSearchTerm] = useState("");
  const [tableKey, setTableKey] = useState(0);
  const [selectedTags, setSelectedTags] = useState<Tag[]>([]);
  const eventId = event.id;
  const [shouldReload, setShouldReload] = useState(false);

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
    router.put(
      route("bookings.assignAgent", { id: event.id }),
      { agent_id: userId, booking_code: selectedBookingId },
      {
        onSuccess: () => {
          const message = flash?.message || "Updated successfully.";
          showSnackbar(message, "success");
          setOpenModal(false);
          setShouldReload(true);
        },
        onError: (errors) => {
          showSnackbar("There was an error assigning the user.", "error");
        },
        preserveScroll: true,
      },
    );
  };

  const handleTabChange = (e: React.SyntheticEvent, newValue: number) => {
    e.preventDefault();
    setSelectedTab(newValue);
  };

  const handleViewClick = (row: Booking) => {
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

  const bookingColumns = useMemo(
    () => [
      {
        header: "Booking Date",
        accessor: "created_at",
        sortable: true,
        draw: (row: Booking) => <>{formatDate(row.created_at)}</>,
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
              label={row?.longestDueDateInstallment ? formatDate(row.longestDueDateInstallment) : "No date"}
              color={status}
            />
          );
        },
      },
      {
        header: "Booking Code",
        accessor: "booking_code",
        draw: (row: Booking) => {
          const code = row.booking_code || "";
          const cabinNumber = row.cabin?.cabin_spec?.cabin_number;

          if (!cabinNumber || !row.cabin?.id) {
            return code;
          }

          const matchIndex = code.indexOf(cabinNumber);

          if (matchIndex < 0) {
            return code;
          }

          const before = code.slice(0, matchIndex);
          const after = code.slice(matchIndex + cabinNumber.length);

          return (
            <Box component={"span"} sx={{ "& a": { color: blue[200] } }}>
              {before}
              <Link href={route("cabins.edit", { id: event.id, cabin_id: row.cabin.id })}>{cabinNumber}</Link>
              {after}
            </Box>
          );
        },
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
        draw: (row: Booking) => <>{row.cabin?.cabin_type?.cabin_type} </>,
      },
      {
        header: "Balance",
        accessor: "balance",
        draw: (row: Booking) => (
          <>
            {row.balance != null && row.cost != null && (
              <>{formatCurrency(row.balance, false, false) + " / " + formatCurrency(row.cost, false, false)}</>
            )}
          </>
        ),
      },
      {
        header: "Tags",
        accessor: "Tags",
        draw: (row: Booking) => (
          <Box sx={{ display: "flex", flexFlow: "column wrap", alignItems: "flex-start", gap: 0.5 }}>
            {Array.isArray(row.tags) && row.tags.length > 0 ? (
              row.tags.map((tag: { name: string; color: string }, index: number) => {
                return (
                  <Chip
                    key={index}
                    label={tag.name}
                    size="small"
                    sx={{
                      fontSize: "0.7rem",
                      fontWeight: 500,
                      backgroundColor: tag.color,
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
        header: "Managed by",
        accessor: "agent_id",
        sortable: true,
        draw: (row: Booking) => {
          const agent = row?.agent;
          const label = agent?.username ? agent.username : "Click to add";
          const avatar = agent?.username ? <Avatar>{agent.username[0]}</Avatar> : <PersonAddIcon />;

          return (
            <Chip
              label={label}
              avatar={avatar}
              size="small"
              onClick={() => handleClick(agent?.id.toString() || "", row.booking_code)}
              sx={{
                fontSize: "0.75rem",
                fontWeight: 500,
                color: agent?.detail?.avatar?.badge?.text,
                backgroundColor: agent?.detail?.avatar?.badge?.background,
                "& .MuiChip-label": { px: 1.5 },
              }}
            />
          );
        },
      },
      {
        header: "Actions",
        accessor: "",
        disableFilter: true,
        draw: (row: Booking) => {
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
                <Button variant="outlined" onClick={() => handleViewClick(row)} color="primary">
                  <Visibility />
                </Button>
              )}

              <LockedByAgent bookingId={row.id} currentEditingUser={row.editingUsername} />
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
        draw: (row: Passenger) => (
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
        draw: (row: Passenger) => (
          <div style={{ display: "flex", gap: "10px" }}>
            {formatCurrency(row.passenger_balance, false, false) +
              " / " +
              formatCurrency(row.passenger_allocated_cost, false, false)}
          </div>
        ),
      },
      {
        header: "Next Payment",
        accesor: "installment_status",
        draw: (row: Passenger) => {
          return (
            <div style={{ display: "flex", gap: "10px" }}>
              <Chip
                label={
                  row.installment_status.fully_paid
                    ? "Paid"
                    : formatDate(row.installment_status.next_installment?.due_date ?? null)
                }
                color={row.installment_status.fully_paid ? "success" : "error"}
                size="small"
                sx={{
                  fontSize: "0.7rem",
                  fontWeight: 500,
                }}
              />
            </div>
          );
        },
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
    setTableKey((prev) => prev + 1);
  };

  const handleFilter = () => {
    setSearchTerm(inputValue);
  };

  const customFilter = (e: React.ChangeEvent<HTMLInputElement>) => {};

  const fetchData = useCallback(
    async (
      page: number,
      rowsPerPage: number,
      filters: Filters,
      sort: { key?: string; direction?: string } | undefined,
      dateRangeState: DateRangeState,
    ) => {
      try {
        let tags = selectedTags ? selectedTags.map((tag) => tag.id).join(",") : null;
        const res = await axios.get(route("bookings.data", { id: event.id }), {
          params: {
            page: page + 1,
            per_page: rowsPerPage,
            sort_key: sort?.key ?? "created_at",
            sort_direction: sort?.direction ?? "asc",
            keyword: searchTerm,
            tab: selectedTab ?? 0,
            tags: tags,
            user_ids: selectedUsers.map((user) => user.id),
            date_range: dateRangeState,
            ...filters,
          },
        });
        return res.data;
      } catch (err) {
        throw err;
      }
    },
    [event.id, searchTerm, selectedTab, selectedTags, selectedUsers, tableKey],
  );

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
                  <NewBookingModal
                    cabinTypes={cabinTypes}
                    cabinCategories={cabinCategories}
                    eventId={event.id}
                    onBookingCreated={() => setShouldReload(true)}
                  />
                </Box>
                <TextField
                  size="small"
                  name="filter"
                  value={inputValue}
                  placeholder="Search"
                  onChange={(e) => setInputValue(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === "Enter") {
                      handleFilter();
                    }
                  }}
                  InputProps={{
                    endAdornment: (
                      <InputAdornment position="end">
                        <IconButton onClick={clearFilter} disabled={!inputValue} size="small">
                          <Clear />
                        </IconButton>
                        <IconButton
                          onClick={() => {
                            handleFilter();
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

                <Autocomplete
                  multiple
                  size="small"
                  options={users}
                  getOptionLabel={(option) => option.user_name}
                  value={selectedUsers}
                  onChange={(event, newValue) => setSelectedUsers(newValue)}
                  renderInput={(params) => <TextField {...params} variant="outlined" placeholder="Filter by Users" />}
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
                onCustomFilter={customFilter}
                serverSidePagination={true}
                fetchData={fetchData}
              />
            </Box>

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
