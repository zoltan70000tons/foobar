import * as React from "react";
import ListItemButton from "@mui/material/ListItemButton";
import ListItemIcon from "@mui/material/ListItemIcon";
import ListItemText from "@mui/material/ListItemText";
import ListSubheader from "@mui/material/ListSubheader";
import DashboardIcon from "@mui/icons-material/Dashboard";
import PeopleIcon from "@mui/icons-material/People";
import BarChartIcon from "@mui/icons-material/BarChart";
import AssignmentIcon from "@mui/icons-material/Assignment";
import EmailIcon from "@mui/icons-material/Email";
import WorkspacesIcon from "@mui/icons-material/Workspaces";
import LocalActivityIcon from "@mui/icons-material/LocalActivity";
import LogoutIcon from "@mui/icons-material/Logout";
import { Link } from "@inertiajs/react";
import BusinessIcon from "@mui/icons-material/Business";
import LoginIcon from "@mui/icons-material/Login";
export const guestListItems = (
  <React.Fragment>
    {/* <ListItemButton component={Link} href={route('organization.register')} method="get">
            <ListItemIcon>
                <BusinessIcon />
            </ListItemIcon>
            <ListItemText primary="Create Organization" />
        </ListItemButton> */}
  </React.Fragment>
);

export const guestAccountListItems = (
  <React.Fragment>
    <ListSubheader component="div" inset>
      Account
    </ListSubheader>
    <ListItemButton component={Link} href={route("login")} method="get">
      <ListItemIcon>
        <LoginIcon />
      </ListItemIcon>
      <ListItemText primary="Login" />
    </ListItemButton>
  </React.Fragment>
);
