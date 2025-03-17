import { useEffect, useState } from "react";
import {
  Box,
  Drawer,
  IconButton,
  Tooltip,
  ListSubheader,
  ListItemButton,
  ListItemText,
  List,
} from "@mui/material";
import {
  Dashboard as DashboardIcon,
  Workspaces as WorkspacesIcon,
  LocalActivity as LocalActivityIcon,
  Logout as LogoutIcon,
  RoomPreferences as RoomPreferenceIcon,
  Person as PersonIcon,
} from "@mui/icons-material";


import { Link, router } from "@inertiajs/react";
import axios from "axios"; 
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";

const MenuItems: React.FC = () => {
  const { hasPermission } = usePermissions();
  const [selectedEventId, setSelectedEventId] = useState<number | null>(null); 
  const [events, setEvents] = useState<any[]>([]);
  const [isBookingsOpen, setIsBookingsOpen] = useState(false);
  const currentPath = window.location.pathname;
  const pathParts = window.location.pathname.split("/");
  const routeEventId = pathParts[2];
  const isBookingsRoute = currentPath.includes("bookings");
  const isDashboardRoute = currentPath.includes("dashboard");
  const isTeamRoute = currentPath.includes("team");
  const isCabinsRoute = currentPath.includes("cabins");
  const isCustomersRoute = currentPath.includes("customer");

  useEffect(() => {
    const fetchEvents = async () => {
      if (isBookingsOpen) {
        try {
          const response = await axios.get("/menu/bookings");
          setEvents(response.data);
        } catch (error) {
          console.error("Error fetching events:", error);
        }
      }
    };

    fetchEvents(); 
  }, [isBookingsOpen]);

  useEffect(() => {
    const fetchEvents = async () => {
      if (isBookingsRoute || isCabinsRoute) {
        try {
          const response = await axios.get("/menu/bookings");
          setEvents(response.data);
          setIsBookingsOpen(true);
        } catch (error) {
          console.error("Error fetching events:", error);
        }
      }
    };

    fetchEvents();
  }, [isBookingsRoute, isCabinsRoute]);
  const handleBookingsClick = async () => {
    setIsBookingsOpen(true); 
  };


  const handleEventClick = (eventId: number): void => {
    setSelectedEventId(eventId);
    router.visit(route("bookings.index", eventId)); 
  };

  const truncateText = (text: string, maxLength: number) => {
    if (text.length > maxLength) {
      return text.substring(0, maxLength) + "...";
    }
    return text;
  };

  return (
    <Box sx={{ display: "flex" }}>
      {/* Main Side bar - Icons Only */}
      <Drawer
        variant="permanent"
        sx={{
          width: 60,
          flexShrink: 0,
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
          [`& .MuiDrawer-paper`]: { width: 60, boxSizing: "border-box" },
        }}
      >
        <Box
          sx={{
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            paddingTop: 1,
          }}
        >
          {hasPermission(Permissions.ViewDashboard) && (
            <Tooltip title="Dashboard" placement="right">
              <IconButton
                component={Link}
                href={route("dashboard")}
                style={{
                  backgroundColor: isDashboardRoute ? "#2f4f4f" : "transparent",
                }}
              >
                <DashboardIcon />
              </IconButton>
            </Tooltip>
          )}
          {hasPermission(Permissions.ViewEvents) && (
            <Tooltip title="Bookings" placement="right">
              <IconButton
                component={Link}
                href={route("bookings.index", "all")}
                style={{
                  backgroundColor: isBookingsRoute ? "#2f4f4f" : "transparent",
                }}
              >
                <LocalActivityIcon />
              </IconButton>
            </Tooltip>
          )}

          {hasPermission(Permissions.ViewCabins) && (
            <Tooltip title="Cabins" placement="right">
              <IconButton
                component={Link}
                href={route("cabins.index", "all")}
                style={{
                  backgroundColor: isCabinsRoute ? "#2f4f4f" : "transparent",
                }}
              >
                <RoomPreferenceIcon />
              </IconButton>
            </Tooltip>
          )}
          {hasPermission(Permissions.ViewUsers) && (
            <Tooltip title="Team" placement="right">
              <IconButton
                component={Link}
                href={route("teams")}
                style={{
                  backgroundColor: isTeamRoute ? "#2f4f4f" : "transparent",
                }}
              >
                <WorkspacesIcon />
              </IconButton>
            </Tooltip>
          )}
          {hasPermission(Permissions.ViewCustomers) && (
              <Tooltip title="Customers" placement="right">
                <IconButton
                    component={Link}
                    href={route("customers.index")}
                    style={{
                      backgroundColor: isCustomersRoute ? "#2f4f4f" : "transparent",
                    }}
                >
                  <PersonIcon />
                </IconButton>
              </Tooltip>
          )}
          <Tooltip title="Logout" placement="right">
            <IconButton onClick={() => router.post(route("logout"))}>
              <LogoutIcon />
            </IconButton>
          </Tooltip>
        </Box>
      </Drawer>
      <Drawer
        variant="permanent"
        sx={{
          width: 240, 
          flexShrink: 0,
          [`& .MuiDrawer-paper`]: { width: 240, boxSizing: "border-box" },
        }}
      >
        <List
          sx={{ width: "100%", bgcolor: "background.paper", mt: 0, pt: 0 }}
          subheader={
            currentPath === null && (
              <ListSubheader component="div">
                Select a Menu option
              </ListSubheader>
            )
          }
        >
          {isDashboardRoute && !isBookingsOpen && (
            <ListItemButton
              component={Link}
              href={route("dashboard")}
              method="get"
              selected={isDashboardRoute}
            >
              <ListItemText primary="Dashboard" />
            </ListItemButton>
          )}

          {isTeamRoute && !isBookingsOpen && (
            <>
              <ListItemButton
                key="team"
                component={Link}
                href={route("teams")}
                selected={currentPath === "/team"}
              >
                <ListItemText primary="Team Members" />
              </ListItemButton>
              <ListItemButton
                key="roles"
                component={Link}
                href={route("roles")}
                selected={currentPath.includes("roles")}
              >
                <ListItemText primary="Team Roles" />
              </ListItemButton>
              <ListItemButton
                key="permissions"
                component={Link}
                href={route("permissions")}
                selected={currentPath.includes("permissions")}
              >
                <ListItemText primary="Team Permissions" />
              </ListItemButton>
            </>
          )}

          {isBookingsOpen  && (
            <List disablePadding>
              {events.length > 0 ? (
                events.map((event) => (
                  <ListItemButton
                    key={event.id}
                    component={Link}
                    href={route(  isBookingsRoute ? "bookings.index" : "cabins.index", event.id)}
                    selected={parseInt(routeEventId) === event.id}
                  >
                    <Tooltip title={event.name}>
                      <ListItemText primary={truncateText(event.code, 15)} />
                    </Tooltip>
                  </ListItemButton>
                ))
              ) : (
                <ListItemText primary="No events available" />
              )}
            </List>
          )}
        </List>
      </Drawer>
    </Box>
  );
};

export default MenuItems;
