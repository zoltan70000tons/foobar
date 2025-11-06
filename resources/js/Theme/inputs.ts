import type { Components, Theme } from "@mui/material/styles";
import { grey } from "@mui/material/colors";

export const disabledStateStyles = {
  WebkitTextFillColor: grey[500],
  backgroundColor: "transparent",
  cursor: "not-allowed",
  opacity: 0.9,
};

/**
 *
 * Component-level overrides for inputs
 */
export const inputOverrides: Components<Theme> = {
  // ------------------------------
  // Input Base
  // ------------------------------
  MuiInputBase: {
    styleOverrides: {
      root: {
        backgroundColor: "rgba(255, 255, 255, 0.05)",
        "&.Mui-disabled, &.Mui-readOnly": {
          ...disabledStateStyles,
          "& fieldset.MuiOutlinedInput-notchedOutline": {
            borderTop: "transparent",
            borderLeft: "transparent",
            borderRight: "transparent",
            borderRadius: 0,
          },
        },
      },
      input: {
        // Autofill styling
        "&:-webkit-autofill": {
          WebkitBoxShadow: "0 0 0 100px rgba(30, 136, 229, 0.05) inset",
          WebkitTextFillColor: "#ffffff",
          transition: "background-color 5000s ease-in-out 0s",
        },

        // Read-only: rely on the native [readonly] attribute
        "&[readonly]": {
          ...disabledStateStyles,
        },

        // Redundant safety in case someone sets disabled at the input level
        "&.Mui-disabled": {
          ...disabledStateStyles,
        },
      },
    },
  },

  // ------------------------------
  // Input Label
  // ------------------------------
  MuiInputLabel: {
    styleOverrides: {},
  },

  // ------------------------------
  // Select
  // ------------------------------
  MuiSelect: {
    styleOverrides: {
      select: {
        borderColor: "rgba(255, 255, 255, 0.4)",
        "&:focus": {
          backgroundColor: "rgba(255, 255, 255, 0.01)",
        },
      },
    },
  },
};
