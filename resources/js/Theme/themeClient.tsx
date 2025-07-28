import { createTheme, alpha } from "@mui/material/styles";
import { blue, grey, red } from "@mui/material/colors";

/*
 * Add new variant of button
 * https://mui.com/material-ui/customization/theme-components/
 *
 */
declare module "@mui/material/Button" {
  interface ButtonPropsVariantOverrides {
    modern: true;
  }
}

/*
 * Add new typography variant
 * https://mui.com/material-ui/customization/typography/
 *
 */

// Alert module extends
declare module "@mui/material/Alert" {
  interface AlertPropsColorOverrides {
    brightRed: true;
  }
}

// Theme
const theme = createTheme({
  typography: {
    h1: {
      fontSize: "3rem",
      fontWeight: 600,
    },
  },
  palette: {
    mode: "dark",
    primary: {
      main: blue[600],
    },
    secondary: {
      main: "#ff0000",
      light: "#df3535",
    },
    divider: "rgba(255, 255, 255, 0.4)",
  },
  breakpoints: {
    values: {
      xs: 0,
      sm: 600,
      md: 1000,
      lg: 1200,
      xl: 1536,
    },
  },
  components: {
    MuiButton: {
      variants: [
        {
          props: { variant: "modern", color: "primary" },
          style: {
            border: `1px solid ${blue[800]}`,
            backgroundColor: alpha(blue[800], 0.3),
            "&:hover": {
              backgroundColor: alpha(blue[800], 1),
            },
          },
        },
        {
          props: { variant: "modern", color: "error" },
          style: {
            border: `1px solid ${red[800]}`,
            backgroundColor: alpha(red[800], 0.3),
            "&:hover": {
              backgroundColor: alpha(red[800], 1),
            },
          },
        },
        {
          props: { variant: "modern", color: "secondary" },
          style: {
            border: `1px solid ${grey[800]}`,
            backgroundColor: alpha(grey[800], 0.3),
            "&:hover": {
              backgroundColor: alpha(grey[800], 1),
            },
          },
        },
      ],
    },
    MuiAppBar: {
      styleOverrides: {
        root: {
          backgroundColor: "#000000",
          backgroundImage: "none",
        },
      },
    },
    MuiAlert: {
      styleOverrides: {
        standardError: {
          backgroundColor: "#461010",
        },
        standardSuccess: {
          backgroundColor: "#112714",
          color: "#d1fed2",
        },
        standardInfo: {
          backgroundColor: "#095270",
        },
      },
    },
    MuiTextField: {
      styleOverrides: {
        root: {
          backgroundColor: "rgba(255, 255, 255, 0.01)", // Consistent background color
          "& .MuiOutlinedInput-root": {
            "& fieldset": {
              borderColor: "rgba(255, 255, 255, 0.4)",
              backgroundColor: "rgba(255, 255, 255, 0.05)",
            },
          },
          "& .Mui-focused fieldset": {
            borderColor: "rgba(255, 255, 255, 0.4)",
            backgroundColor: "rgba(255, 255, 255, 0.01)",
          },
          "& input:-webkit-autofill": {
            WebkitBoxShadow: "0 0 0 100px rgba(30, 136, 229, 0.05) inset",
            WebkitTextFillColor: "#ffffff", // Text color for autofilled content
            transition: "background-color 5000s ease-in-out 0s", // Ensures background color stays
          },
        },
      },
    },
    MuiSelect: {
      styleOverrides: {
        select: {
          backgroundColor: "rgba(255, 255, 255, 0.05)",
          borderColor: "rgba(255, 255, 255, 0.4)",
          "&:focus": {
            backgroundColor: "rgba(255, 255, 255, 0.01)",
          },
        },
      },
    },
    MuiGrid: {
      styleOverrides: {
        root: {
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          alignContent: "center",
          margin: 0,
          padding: 0,
        },
      },
    },
  },
});

export default theme;
