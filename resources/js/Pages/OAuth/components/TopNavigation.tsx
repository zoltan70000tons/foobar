import { red } from "@mui/material/colors";
import { useTheme } from "@mui/material/styles";
import React, { useState } from "react";
import { Close, Menu as Hamburger } from "@mui/icons-material";
import { IconButton, Box, Stack, alpha, useMediaQuery, Drawer, Button } from "@mui/material";
import { usePage } from "@inertiajs/react";
import LangSwitcher from "./LangSwitcher";

import { OAuthTranslations } from "../../../types/inertia";

const EXTERNAL_URL = "https://70000tons.com";
const FRONTEND_URL = import.meta.env.VITE_FRONTEND_URL;

type SignInDialogProps = {
  tAuth: {
    signIn: string;
  };
  flash: {
    message?: string;
  };
};

const TopNavigation = React.memo(({ language }: { language: string }) => {
  const { tAuth } = usePage<SignInDialogProps>().props;

  const theme = useTheme();
  const isMobile = useMediaQuery(`(max-width:${theme.breakpoints.values.md}px)`);

  const [open, setOpen] = useState<boolean>(false);

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
      alignItems={{ xs: "flex-end", md: "center" }}
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
      {isMobile ? (
        <IconButton
          sx={{
            marginLeft: "10px",
          }}
          onClick={() => setOpen(!open)}
        >
          <Hamburger
            sx={{
              color: "white",
            }}
          />
        </IconButton>
      ) : (
        <Box
          sx={{
            display: "flex",
            alignItems: "center",
            gap: 2,
            justifyContent: "space-between",
            width: "100%",
          }}
        >
          <MenuElements language={language} />
          <Box
            sx={{
              display: "flex",
              minWidth: "120px",
              alignItems: "center",
              gap: 1,
            }}
          >
            {/* <SignInDialog isFull={false} /> */}
            <Button variant="modern" color="primary" fullWidth={false}>
              {tAuth?.signIn ?? "Sign In"}
            </Button>
            <LangSwitcher />
          </Box>
        </Box>
      )}

      <Drawer
        anchor="right"
        open={open}
        onClose={() => setOpen(false)}
        ModalProps={{ keepMounted: true }}
        sx={{
          "& .MuiDrawer-paper": {
            width: "100%",
            maxWidth: 380,
            minHeight: "100%",
            backgroundColor: "#000",
            backgroundImage: "none",
            color: "common.white",
            display: "flex",
            flexDirection: "column",
            pt: 2,
          },
          // styl tła (backdrop)
          "& .MuiBackdrop-root": {
            backgroundColor: alpha("#000", 0.7),
          },
        }}
      >
        <Box
          sx={{
            display: "flex",
            alignItems: "flex-start",
            justifyContent: "space-between",
            gap: 2,
            px: 2,
          }}
        >
          <IconButton
            onClick={() => setOpen(false)}
            sx={{
              backgroundColor: alpha("#fff", 0.1),
              color: "#fff",
              ml: "auto",
            }}
          >
            <Close />
          </IconButton>
        </Box>
        <Box
          sx={{
            flex: 1,
            overflowY: "auto",
            px: 2,
            py: 2,
          }}
        >
          <MenuElements language={language} />
        </Box>
        <Box
          sx={{
            display: "flex",
            alignItems: "center",
            gap: 2,
            px: 2,
            pb: 2,
          }}
        >
          <Box sx={{ flex: 1, display: "flex" }}>
            <Button fullWidth={true} variant="modern" color="primary" onClick={() => setOpen(false)}>
              Sign In
            </Button>
          </Box>
          <LangSwitcher />
        </Box>
      </Drawer>
    </Stack>
  );
});

TopNavigation.displayName = "TopNavigation";

export default TopNavigation;

const MenuElements = ({ language }: { language: string }) => {
  const { oauthTranslations } = usePage().props as { oauthTranslations?: OAuthTranslations };
  const tMenu = oauthTranslations?.Menu;

  const menu = [
    {
      id: 2,
      name: tMenu?.home || "Home",
      href: FRONTEND_URL + "/" + language,
      external: false,
    },
    {
      id: 3,
      name: tMenu?.artist || "Artists",
      href: EXTERNAL_URL + "/artists/?lang=" + language,
      external: true,
    },
    {
      id: 5,
      name: tMenu?.event || "Event",
      href: EXTERNAL_URL + "/voyage?lang=" + language,
      external: true,
    },
    {
      id: 6,
      name: tMenu?.faq || "FAQ",
      href: EXTERNAL_URL + "/faq?lang=" + language,
      external: true,
    },
    {
      id: 8,
      name: tMenu?.check_booking || "Check Booking",
      href: FRONTEND_URL + "/check-booking" + language,
      external: false,
    },
    {
      id: 9,
      name: tMenu?.make_payment || "Make Payment",
      href: FRONTEND_URL + "/make-a-payment" + language,
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
      // alignItems="center"
      sx={{
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
          fontWeight: 500,
          fontSize: { xs: "18px", md: "0.8rem" },
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
};
