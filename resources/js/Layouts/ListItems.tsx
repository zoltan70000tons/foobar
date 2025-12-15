import { useMemo } from "react";
import { Box, Drawer, IconButton, Tooltip, ListSubheader, ListItemButton, ListItemText, List } from "@mui/material";
import {
  Dashboard as DashboardIcon,
  Workspaces as WorkspacesIcon,
  LocalActivity as LocalActivityIcon,
  Logout as LogoutIcon,
  RoomPreferences as RoomPreferenceIcon,
  Person as PersonIcon,
  DirectionsBoat as EventIcon,
  History as HistoryIcon,
  JoinInner as JoinInnerIcon
} from "@mui/icons-material";
import SellIcon from "@mui/icons-material/Sell";
import { Link, router, usePage } from "@inertiajs/react";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";

type AppEvent = {
  id: number;
  name: string;
  code: string;
};

const SIDE_ICON_WIDTH = 75;
const SIDE_ICON_WIDTH_MOBILE = 60;
const SIDE_MENU_WIDTH = 240;

const MenuItems: React.FC = () => {
  const { hasPermission } = usePermissions();

  const { url, props } = usePage<{ menu?: { events: AppEvent[] } }>();
  const events: AppEvent[] = props.menu?.events ?? [];

  // Parse path once
  const { currentPath, pathParts, routeEventId } = useMemo(() => {
    const currentPath = url || "/";
    const pathParts = currentPath.split("/").filter(Boolean); // removes leading empty string
    const routeEventId = Number(pathParts[1]); // e.g. /bookings/123 -> ["bookings","123",...]
    return { currentPath, pathParts, routeEventId };
  }, [url]);

  // Route flags (cheap + memoized)
  const flags = useMemo(() => {
    const is = (segment: string) => currentPath.includes(`/${segment}`);
    return {
      isDashboardRoute: is("dashboard"),
      isBookingsRoute: is("bookings"),
      isTeamRoute: is("teams") || is("team") || is("roles") || is("permissions"),
      isCabinsRoute: is("cabins"),
      isCustomersRoute: is("customers") || is("customer"),
      isEventsRoute: is("events") && !is("bookings") && !is("cabins"),
      isTagsRoute: is("tags"),
      isLogsRoute: is("logs"),
      isPSMRoute: is("potential-survivor-matches"),
    };
  }, [currentPath]);

  const truncate = (text: string | null | undefined, max: number) =>
    !text ? "" : text.length > max ? `${text.slice(0, max)}…` : text;

  const isActiveBg = (active: boolean) => ({
    backgroundColor: active ? "#2f4f4f" : "transparent",
  });

  // Primary sidebar config
  const primaryItems = [
    {
      key: "dashboard",
      label: "Dashboard",
      icon: <DashboardIcon />,
      href: route("dashboard"),
      can: Permissions.ViewDashboard,
      active: flags.isDashboardRoute,
    },
    {
      key: "bookings",
      label: "Bookings",
      icon: <LocalActivityIcon />,
      href: route("bookings.index", "all"),
      can: Permissions.ViewEvents,
      active: flags.isBookingsRoute,
    },
    {
      key: "cabins",
      label: "Cabins",
      icon: <RoomPreferenceIcon />,
      href: route("cabins.index", "all"),
      can: Permissions.ViewCabins,
      active: flags.isCabinsRoute,
    },
    {
      key: "team",
      label: "Team",
      icon: <WorkspacesIcon />,
      href: route("teams"),
      can: Permissions.ViewUsers,
      active: flags.isTeamRoute,
    },
    {
      key: "customers",
      label: "Customers",
      icon: <PersonIcon />,
      href: route("customers.index"),
      can: Permissions.ViewCustomers,
      active: flags.isCustomersRoute,
    },
    {
      key: "events",
      label: "Events",
      icon: <EventIcon />,
      href: route("events.index"),
      can: Permissions.ViewEvents,
      active: flags.isEventsRoute,
    },
    {
      key: "tags",
      label: "Tags",
      icon: <SellIcon />,
      href: route("tags.index"),
      can: Permissions.ViewTags,
      active: flags.isTagsRoute, // fixed: was isEventsRoute
    },
    {
      key: "Logs",
      label: "Logs",
      icon: <HistoryIcon />,
      href: route("logs.index"),
      can: Permissions.ViewLogs,
      active: flags.isLogsRoute, // fixed: was isLogsRoute
    },
    {
      key: "Potential Survivor Matches",
      label: "Potential Survivor Matches",
      icon: <JoinInnerIcon />,
      href: route("matches.index"),
      can: Permissions.ViewCustomers,
      active: flags.isPSMRoute,
    },
  ] as const;

  return (
    <Box sx={{ display: "flex", height: "100%" }}>
      <Drawer
        variant="permanent"
        sx={{
          width: { xs: SIDE_ICON_WIDTH_MOBILE, md: SIDE_ICON_WIDTH },
          flexShrink: 0,
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
          [`& .MuiDrawer-paper`]: { width: SIDE_ICON_WIDTH, boxSizing: "border-box" },
        }}
      >
        <Box sx={{ display: "flex", flexDirection: "column", alignItems: "center", pt: 1, gap: 0.5 }}>
          {primaryItems
            //.filter((item) => hasPermission(item.can))
            .map((item) => (
              <Tooltip key={item.key} title={item.label} placement="right">
                <IconButton
                  component={Link as any}
                  href={item.href}
                  aria-current={item.active ? "page" : undefined}
                  style={isActiveBg(item.active)}
                >
                  {item.icon}
                </IconButton>
              </Tooltip>
            ))}

          <Tooltip title="Logout" placement="right">
            <IconButton onClick={() => router.post(route("logout"))}>
              <LogoutIcon />
            </IconButton>
          </Tooltip>
        </Box>
      </Drawer>

      {/* Secondary rail (contextual menu) */}
      <Drawer
        variant="permanent"
        sx={{
          width: SIDE_MENU_WIDTH,
          flexShrink: 0,
          [`& .MuiDrawer-paper`]: { width: SIDE_MENU_WIDTH, boxSizing: "border-box" },
        }}
      >
        <List
          sx={{ width: "100%", bgcolor: "background.paper", mt: 0, pt: 0 }}
          subheader={
            !flags.isDashboardRoute &&
            !flags.isTagsRoute &&
            !flags.isCustomersRoute &&
            !flags.isEventsRoute && <ListSubheader component="div">Select a menu option</ListSubheader>
          }
        >
          {flags.isDashboardRoute && (
            <ListItemButton component={Link as any} href={route("dashboard")} selected={flags.isDashboardRoute}>
              <ListItemText primary="Dashboard" />
            </ListItemButton>
          )}

          {flags.isTeamRoute && (
            <>
              <ListItemButton component={Link as any} href={route("teams")} selected={currentPath === "/teams"}>
                <ListItemText primary="Team Members" />
              </ListItemButton>
              <ListItemButton component={Link as any} href={route("roles")} selected={currentPath.includes("/roles")}>
                <ListItemText primary="Team Roles" />
              </ListItemButton>
              <ListItemButton
                component={Link as any}
                href={route("permissions")}
                selected={currentPath.includes("/permissions")}
              >
                <ListItemText primary="Team Permissions" />
              </ListItemButton>
            </>
          )}

          {(flags.isBookingsRoute || flags.isCabinsRoute) && (
            <List disablePadding>
              {events.length ? (
                events.map((event) => (
                  <ListItemButton
                    key={event.id}
                    component={Link as any}
                    href={route(flags.isBookingsRoute ? "bookings.index" : "cabins.index", event.id)}
                    selected={routeEventId === event.id}
                  >
                    <Tooltip title={event.name}>
                      <ListItemText primary={truncate(event.code, 15)} />
                    </Tooltip>
                  </ListItemButton>
                ))
              ) : (
                <ListItemText sx={{ px: 2, py: 1.5 }} primary="No events available" />
              )}
            </List>
          )}
        </List>
      </Drawer>
    </Box>
  );
};

export default MenuItems;
