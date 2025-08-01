import { Stack } from "@mui/material";
import { red } from "@mui/material/colors";
import { useTheme } from "@mui/material/styles";
import React from "react";

const EXTERNAL_URL = "https://70000tons.com";
const FRONTEND_URL = import.meta.env.VITE_FRONTEND_URL;

const MenuItems = React.memo(() => {

  const theme = useTheme();

  const menu =  [
    {
      id: 2,
      name: 'Booking',
      href: FRONTEND_URL,
      external: false,
    },
    {
      id: 3,
      name: 'Artists',
      href: EXTERNAL_URL + "/artists/?lang=" + 'en',
      external: true,
    },
    {
      id: 5,
      name: 'Event',
      href: EXTERNAL_URL + "/voyage?lang=" + 'en',
      external: true,
    },
    {
      id: 6,
      name: 'FAQ',
      href: EXTERNAL_URL + "/faq?lang=" + 'en',
      external: true,
    },
    {
      id: 8,
      name: 'Check Booking',
      href: FRONTEND_URL + "/check-booking",
      external: false,
    },
    {
      id: 9,
      name: 'Make Payment',
      href: FRONTEND_URL + "/make-a-payment",
      external: false,
    },
  ];

  return (
    <Stack
      direction={{
        xs: "column",
        md: "row",
      }}
      spacing={{
        xs: 4,
        md: 2,
        lg: 3,
      }}
      alignItems="center"
      sx={{
        width: "100%",
        md: "flex",
        textDecoration: "none",
        color: "white",
        textTransform: "uppercase",
        marginBottom: {
          xs: "30px",
          md: "0px",
        },
        "& a": {
          color: "white",
          textDecoration: "none",
          textTransform: "uppercase",
          fontFamily: theme.typography.fontFamily,
          fontWeight: 500,
          fontSize: "0.8rem",
          transition: "color 0.3s",
          "&:hover": {
            color: red[600],
          },
        },
      }}
    >
      {menu.map((item) => {
        return item.external ? (
          <a key={item.id} href={item.href} target="_blank">
            {item.name}
          </a>
        ) : (
          <a key={item.id} href={item.href}>
            {item.name}
          </a>
        );
      })}
    </Stack>
  );
});

MenuItems.displayName = "MenuItems";

export default MenuItems;
