import "react-international-phone/style.css";
import React from "react";
import {
  BaseTextFieldProps,
  MenuItem,
  Select,
  TextField,
  Typography,
  FormControl,
} from "@mui/material";
import {
  CountryIso2,
  defaultCountries,
  FlagImage,
  parseCountry,
  usePhoneInput,
} from "react-international-phone";

export interface MUIPhoneProps extends BaseTextFieldProps {
  value: string;
  onChange: (phone: string) => void;
  forceDialCode?: boolean;
  disabled?: boolean;
}

export default function PhoneNumber({
  value,
  onChange,
  forceDialCode = true,
  ...restProps
}: MUIPhoneProps) {
  const { inputValue, handlePhoneValueChange, inputRef, country, setCountry } =
    usePhoneInput({
      defaultCountry: "us",
      value,
      forceDialCode,
      countries: defaultCountries,
      onChange: (data) => onChange(data.phone),
    });

  // Styles for Select Component
  const selectStyles = {
    minWidth: "50px",
    marginRight: "10px",
    "& .MuiSelect-select": {
      padding: "8px",
      paddingRight: "24px !important",
      display: "flex",
      alignItems: "center",
    },
    "& fieldset": { border: "none" },
    "& svg": { right: 0 },
  };

  // Memoized Country List
  const countryOptions = React.useMemo(
    () =>
      defaultCountries.map((c) => {
        const countryData = parseCountry(c);
        return (
          <MenuItem key={countryData.iso2} value={countryData.iso2}>
            <FlagImage iso2={countryData.iso2} style={{ marginRight: "8px" }} />
            <Typography marginRight="8px">{countryData.name}</Typography>
            <Typography color="gray">+{countryData.dialCode}</Typography>
          </MenuItem>
        );
      }),
    []
  );

  return (
    <TextField
      variant="outlined"
      label="Phone number"
      color="primary"
      placeholder="Phone number"
      value={inputValue}
      onChange={handlePhoneValueChange}
      type="tel"
      inputRef={inputRef}
      sx={{ width: "100%" }}
      InputProps={{
        startAdornment: (
          <FormControl sx={{ minWidth: "50px" }}>
            <Select
              disabled={restProps.disabled ?? false}
              value={country.iso2}
              onChange={(e) => setCountry(e.target.value as CountryIso2)}
              MenuProps={{
                PaperProps: {
                  style: {
                    maxHeight: 300,
                    width: 360,
                  },
                },
                anchorOrigin: {
                  vertical: "bottom",
                  horizontal: "left",
                },
                transformOrigin: {
                  vertical: "top",
                  horizontal: "left",
                },
              }}
              sx={selectStyles}
              renderValue={(value) => (
                <FlagImage iso2={value} style={{ display: "flex" }} />
              )}
            >
              {countryOptions}
            </Select>
          </FormControl>
        ),
      }}
      {...restProps}
    />
  );
}
