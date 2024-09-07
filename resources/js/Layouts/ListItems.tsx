import { useState } from "react";
import {
  Box,
  Collapse,
  Drawer,
  IconButton,
  ListSubheader,
  ListItemButton,
  ListItemText,
  Tooltip,
  List,
} from "@mui/material";
import {
  Dashboard as DashboardIcon,
  Workspaces as WorkspacesIcon,
  LocalActivity as LocalActivityIcon,
  Logout as LogoutIcon,
  ExpandLess as ExpandLessIcon,
  ExpandMore as ExpandMoreIcon,
} from "@mui/icons-material";
import { Link, router } from "@inertiajs/react";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";

// Define types for the section names
type Section = "dashboard" | "team" | "events" | null;

interface MenuItemsProps {
  mainDrawerToggle: React.Dispatch<React.SetStateAction<boolean>>; // Type for setState
}

const MenuItems: React.FC<MenuItemsProps> = ({ mainDrawerToggle }) => {
  const { hasPermission } = usePermissions();

  // Use lazy initialization for localStorage-based on Menu state
  const [state, setState] = useState(() => ({
    openSection: JSON.parse(localStorage.getItem("openSection") || "null") as Section,
    openTeam: JSON.parse(localStorage.getItem("openTeam") || "false"),
    openEvents: JSON.parse(localStorage.getItem("openEvents") || "false"),
  }));

  const { openSection, openTeam, openEvents } = state;

  // Handle section click events
  const handleSectionClick = (section: Section): void => {

    // Close the main drawer if the section is the same as the open section
    if (section === openSection) {
      mainDrawerToggle(false);
      setState((prevState) => ({ ...prevState, openSection: null }));
    } else {
      // Open the main drawer if the section is different from the open section
      mainDrawerToggle(true);
      setState((prevState) => ({
        ...prevState,
        openSection: section,
        openTeam: section === "team",
        openEvents: section === "events",
      }));
      localStorage.setItem("openSection", JSON.stringify(section));
    }
  };

  // Handle team section toggle
  const handleTeamClick = (): void => {
    setState((prevState) => {
      const newOpenTeam = !prevState.openTeam;
      localStorage.setItem("openTeam", JSON.stringify(newOpenTeam));
      return { ...prevState, openTeam: newOpenTeam };
    });
  };

  // Handle events section toggle
  const handleEventsClick = (): void => {
    setState((prevState) => {
      const newOpenEvents = !prevState.openEvents;
      localStorage.setItem("openEvents", JSON.stringify(newOpenEvents));
      return { ...prevState, openEvents: newOpenEvents };
    });
  };

  return (
    <Box sx={{ display: "flex" }}>
      {/* Main Side bar - Icons Only */}
      <Drawer
        variant="permanent"
        sx={{
          width: 60,
          flexShrink: 0,
          [`& .MuiDrawer-paper`]: { width: 60, boxSizing: "border-box" },
        }}
      >
        <Box sx={{ display: "flex", flexDirection: "column", alignItems: "center", paddingTop: 1 }}>
          {hasPermission(Permissions.ViewDashboard) && (
            <Tooltip title="Dashboard" placement="right">
              <IconButton onClick={() => (handleSectionClick("dashboard"))} >
                <DashboardIcon />
              </IconButton>
            </Tooltip>
          )}
          {hasPermission(Permissions.ViewUsers) && (
            <Tooltip title="Team" placement="right">
              <IconButton onClick={() => handleSectionClick("team")}>
                <WorkspacesIcon />
              </IconButton>
            </Tooltip>
          )}
          {hasPermission(Permissions.ViewEvents) && (
            <Tooltip title="Events" placement="right">
              <IconButton onClick={() => handleSectionClick("events")}>
                <LocalActivityIcon />
              </IconButton>
            </Tooltip>
          )}
          <Tooltip title="Logout" placement="right">
            <IconButton onClick={() => router.post("logout")}>
              <LogoutIcon />
            </IconButton>
          </Tooltip>
        </Box>
      </Drawer>

      {/* Second sidebar that displays content based on the selected section */}
      <List
        sx={{ width: "100%", bgcolor: "background.paper" }}
        subheader={openSection === null && (
            <ListSubheader component="div">
              Select a Menu option
            </ListSubheader>
          )
        }
      >
        {openSection === "dashboard" && (
          <ListItemButton component={Link} href={route("dashboard")} method="get">
            <ListItemText primary="Dashboard" />
          </ListItemButton>
        )}

        {openSection === "team" && (
          <>
            <ListItemButton onClick={handleTeamClick}>
              <ListItemText primary="Team" />
              {openTeam ? <ExpandLessIcon /> : <ExpandMoreIcon />}
            </ListItemButton>
            <Collapse in={openTeam} timeout="auto" unmountOnExit>
              <ListItemButton component={Link} href={route("teams")}>
                <ListItemText primary="Team Members" />
              </ListItemButton>
              <ListItemButton component={Link} href={route("roles")}>
                <ListItemText primary="Team Roles" />
              </ListItemButton>
              <ListItemButton component={Link} href={route("permissions")}>
                <ListItemText primary="Team Permissions" />
              </ListItemButton>
            </Collapse>
          </>
        )}

        {openSection === "events" && (
          <>
            <ListItemButton onClick={handleEventsClick}>
              <ListItemText primary="Events" />
              {openEvents ? <ExpandLessIcon /> : <ExpandMoreIcon />}
            </ListItemButton>
            <Collapse in={openEvents} timeout="auto" unmountOnExit>
              <ListItemButton component={Link} href={route("events.index")}>
                <ListItemText primary="All Events" />
              </ListItemButton>
            </Collapse>
          </>
        )}
      </List>
    </Box>
  );
};

export default MenuItems;