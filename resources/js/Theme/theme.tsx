import { createTheme, alpha } from "@mui/material/styles";
import { inputOverrides } from "./inputs";
import { blue, grey, red } from "@mui/material/colors";

const theme = createTheme({
  typography: {
    fontFamily: "Roboto, Arial, sans-serif",
    h1: {
      fontSize: "2.5rem",
      fontWeight: 700,
    },
    h2: {
      fontSize: "2rem",
      fontWeight: 700,
    },
    body1: {
      fontSize: "1rem",
    },
    button: {
      textTransform: "none",
    },
  },
  palette: {
    mode: "dark",
    background: {
      default: "#0b0b0b",
      paper: "#0e0e0eff",
    },
  },
  components: {
    ...inputOverrides,
  },
});

export default theme;
