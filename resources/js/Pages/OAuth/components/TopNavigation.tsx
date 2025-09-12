import { red } from "@mui/material/colors";
import { useTheme } from "@mui/material/styles";
import React, { useState } from "react";
import { Close, Menu as Hamburger } from "@mui/icons-material";
import { 
  IconButton, 
  Box, 
  Stack, 
  alpha,
  useMediaQuery,
  Dialog,
  DialogTitle,
  DialogContent, 
  DialogActions
} from "@mui/material";
import LangSwitcher from "./LangSwitcher";
import SignInDialog from "./SignInDialog";

const EXTERNAL_URL = "https://70000tons.com";
const FRONTEND_URL = import.meta.env.VITE_FRONTEND_URL;

const TopNavigation = React.memo(({ language }: { language: string }) => {

  const theme = useTheme();
  const isMobile = useMediaQuery(
    `(max-width:${theme.breakpoints.values.md}px)`
  );

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
            gap: 1
          }}
        >
          <SignInDialog
            isFull={false} 
          />
          <LangSwitcher />
        </Box>
      </Box>
    )}
      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        fullScreen
        scroll={'paper'}
        sx={{
          "& .MuiDialog-paper": {
            backgroundColor: "#000",
          },
        }}
      >
        <DialogTitle 
          sx={{
            display: "flex",
            width: "100%",
            gap: 4,
            justifyContent: "flex-end",
            pt: 2,
            px: 1,
          }}
        >

          <Box sx={{ width: "60px", height: "40px", display: 'flex', justifyContent: 'flex-end'}}>
            <IconButton
              onClick={() => setOpen(false)}
              sx={{
                backgroundColor: alpha("#fff", 0.1),
              }}
            >
              <Close />
            </IconButton>
          </Box>
        </DialogTitle >
        <DialogContent
          sx={{
            padding: "10px",
          }}
        >
          <MenuElements language={language} />
        </DialogContent>
        <DialogActions
          sx={{
            display: "flex",
            justifyContent: "space-between",
            padding: "10px",
          }}
        >
          <SignInDialog isFull={true}/>
          <LangSwitcher/>
        </DialogActions>
      </Dialog>
    </Stack>
  );
});

TopNavigation.displayName = "TopNavigation";

export default TopNavigation;

const MenuElements = ({ language }: { language: string }) => {

  const menu =  [
    {
      id: 2,
      name: 'Booking',
      href: FRONTEND_URL + "/" + language,
      external: false,
    },
    {
      id: 3,
      name: 'Artists',
      href: EXTERNAL_URL + "/artists/?lang=" + language,
      external: true,
    },
    {
      id: 5,
      name: 'Event',
      href: EXTERNAL_URL + "/voyage?lang=" + language,
      external: true,
    },
    {
      id: 6,
      name: 'FAQ',
      href: EXTERNAL_URL + "/faq?lang=" + language,
      external: true,
    },
    {
      id: 8,
      name: 'Check Booking',
      href: FRONTEND_URL + "/check-booking" + language,
      external: false,
    },
    {
      id: 9,
      name: 'Make Payment',
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
          fontSize: { xs: '18px', md: "0.8rem"},
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
  )
}