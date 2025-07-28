import {
  Grid,
  Stack,
  Typography,
  Box,
  Container,
  Divider,
  Accordion,
  AccordionSummary,
  AccordionDetails,
  Button
} from "@mui/material";
import {
  ExpandMore,
  Facebook,
  Instagram,
  Twitter,
  YouTube,
  Telegram,
} from "@mui/icons-material";
import Copyright from "./Copyright";

export default function Footer() {

  return (
    <Box
      component={"footer"}
      sx={{
        zIndex: 1002,
        backgroundColor: "#000",
        color: "#fff",
        px: 4,
        py: 4,
        position: "relative",
      }}
    >
      <Container>
      <Divider
        sx={{
          backgroundColor: "#333",
          opacity: 0.5,
          my: 4,
        }}
      />
      <Copyright />
    </Container>
  </Box>
  );
}