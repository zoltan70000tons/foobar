import * as React from "react";
import ListItemButton from "@mui/material/ListItemButton";
import ListItemText from "@mui/material/ListItemText";
import ListSubheader from "@mui/material/ListSubheader";
import Collapse from "@mui/material/Collapse";
import DashboardIcon from "@mui/icons-material/Dashboard";
import PeopleIcon from "@mui/icons-material/People";
import WorkspacesIcon from "@mui/icons-material/Workspaces";
import LocalActivityIcon from "@mui/icons-material/LocalActivity";
import LogoutIcon from "@mui/icons-material/Logout";
import ExpandLess from "@mui/icons-material/ExpandLess";
import ExpandMore from "@mui/icons-material/ExpandMore";
import { Link } from "@inertiajs/react";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import LocalPoliceIcon from '@mui/icons-material/LocalPolice';
import AdminPanelSettingsIcon from '@mui/icons-material/AdminPanelSettings';
import Drawer from '@mui/material/Drawer';
import Box from '@mui/material/Box';
import IconButton from '@mui/material/IconButton';
import Tooltip from '@mui/material/Tooltip';

const MenuItems = () => {
  const { hasPermission } = usePermissions();

  // Estado compartido para la sección seleccionada
  const [openSection, setOpenSection] = React.useState(
    JSON.parse(localStorage.getItem("openSection")) || null
  );

  // Estados para las secciones del segundo sidebar
  const [openTeam, setOpenTeam] = React.useState(
    JSON.parse(localStorage.getItem("openTeam")) || false
  );
  const [openEvents, setOpenEvents] = React.useState(
    JSON.parse(localStorage.getItem("openEvents")) || false
  );

  const handleSectionClick = (section) => {
    setOpenSection(section);
    localStorage.setItem("openSection", JSON.stringify(section));

    // Sincronizar el estado de expansión para las secciones del segundo sidebar
    if (section === 'team') {
      setOpenTeam(true);
    } else if (section === 'events') {
      setOpenEvents(true);
    } else {
      setOpenTeam(false);
      setOpenEvents(false);
    }
  };

  const handleTeamClick = () => {
    const newState = !openTeam;
    setOpenTeam(newState);
    localStorage.setItem("openTeam", JSON.stringify(newState));
  };

  const handleEventsClick = () => {
    const newState = !openEvents;
    setOpenEvents(newState);
    localStorage.setItem("openEvents", JSON.stringify(newState));
  };

  return (
    <Box sx={{ display: 'flex' }}>
      {/* Primer sidebar con solo iconos */}
      <Drawer
        variant="permanent"
        sx={{
          width: 60,
          flexShrink: 0,
          [`& .MuiDrawer-paper`]: { width: 60, boxSizing: 'border-box' },
        }}
      >
        <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', paddingTop: 1 }}>
          {hasPermission(Permissions.ViewDashboard) && (
            <Tooltip title="Dashboard" placement="right">
              <IconButton onClick={() => handleSectionClick('dashboard')}>
                <DashboardIcon />
              </IconButton>
            </Tooltip>
          )}
          {hasPermission(Permissions.ViewUsers) && (
            <Tooltip title="Team" placement="right">
              <IconButton onClick={() => handleSectionClick('team')}>
                <WorkspacesIcon />
              </IconButton>
            </Tooltip>
          )}
          {hasPermission(Permissions.ViewEvents) && (
            <Tooltip title="Events" placement="right">
              <IconButton onClick={() => handleSectionClick('events')}>
                <LocalActivityIcon />
              </IconButton>
            </Tooltip>
          )}
          <Tooltip title="Logout" placement="right">
            <IconButton component={Link} href={route("logout")} method="post">
              <LogoutIcon />
            </IconButton>
          </Tooltip>
        </Box>
      </Drawer>

      {/* Segundo sidebar que muestra el contenido según la sección seleccionada */}
      <Drawer
        variant="permanent"
        sx={{
          width: 240,
          flexShrink: 0,
          [`& .MuiDrawer-paper`]: { width: 240, boxSizing: 'border-box' },
        }}
      >
        <Box sx={{ overflow: 'auto' }}>
          {openSection === 'dashboard' && (
            <ListItemButton component={Link} href={route("dashboard")} method="get">
              <ListItemText primary="Dashboard" />
            </ListItemButton>
          )}

          {openSection === 'team' && (
            <>
              <ListItemButton onClick={handleTeamClick}>
                <ListItemText primary="Team" />
                {openTeam ? <ExpandLess /> : <ExpandMore />}
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

          {openSection === 'events' && (
            <>
              <ListItemButton onClick={handleEventsClick}>
                <ListItemText primary="Events" />
                {openEvents ? <ExpandLess /> : <ExpandMore />}
              </ListItemButton>
              <Collapse in={openEvents} timeout="auto" unmountOnExit>
                <ListItemButton component={Link} href={route("events.index")}>
                  <ListItemText primary="All Events" />
                </ListItemButton>
                {/* <ListItemButton component={Link} href={route("cabins.index")}>
                  <ListItemText primary="Cabins" />
                </ListItemButton>
                <ListItemButton component={Link} href={route("cabincategories.index")}>
                  <ListItemText primary="Cabin Categories" />
                </ListItemButton>
                <ListItemButton component={Link} href={route("taxes.index")}>
                  <ListItemText primary="Taxes" />
                </ListItemButton> */}
              </Collapse>
            </>
          )}

          {openSection === null && (
            <ListSubheader component="div" inset>
              Please select a section
            </ListSubheader>
          )}
        </Box>
      </Drawer>

      {/* Contenido principal */}
      <Box
        component="main"
        sx={{ flexGrow: 1, bgcolor: 'background.default', padding: 3 }}
      >
        {/* Aquí iría tu contenido principal */}
      </Box>
    </Box>
  );
};

export default MenuItems;
