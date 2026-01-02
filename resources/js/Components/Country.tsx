import React, { ChangeEvent, useState } from "react";
import { Box, FormControl, Select, MenuItem, InputLabel, FormHelperText, Typography } from "@mui/material";
import countries from "i18n-iso-countries";
import "/node_modules/flag-icons/css/flag-icons.min.css";

// Import locales
import enLocale from "i18n-iso-countries/langs/en.json";
import esLocale from "i18n-iso-countries/langs/es.json";
import deLocale from "i18n-iso-countries/langs/de.json";

// Initialize locales
countries.registerLocale(enLocale);
countries.registerLocale(esLocale);
countries.registerLocale(deLocale);

type Props = {
  nameOfField?: string;
  label?: string;
  value?: string | undefined; // Country code
  onChange: (value: string) => void;
  error?: boolean;
  helperText?: string;
  disabled?: boolean;
};

const Country: React.FC<Props> = ({
  value,
  label,
  onChange,
  error,
  helperText,
  nameOfField,
  disabled = false,
  size = "",
}) => {
  const [marginTop, setMarginTop] = useState("-7px");
  // Function to get country name based on language
  const getCountryName = (countryCode: string) => {
    return countries.getName(countryCode, "en");
  };

  // Get all country codes
  const countryCodes = countries.getAlpha3Codes();

  // Countries list
  const CountryList = Object.keys(countryCodes).map((countryCode) => {
    const alpha2Country = countries.alpha3ToAlpha2(countryCode) as string;

    return (
      <MenuItem key={countryCode} value={countryCode}>
        <Typography>
          <Box component={"span"} className={`fi fi-${alpha2Country.toLocaleLowerCase()}`} sx={{ mr: 1 }}></Box>
          {getCountryName(countryCode)}
        </Typography>
      </MenuItem>
    );
  });

  // Handle change country
  const handleChangeCountry = (e: ChangeEvent<HTMLSelectElement>) => {
    const selectedCountry = e.target.value;
    onChange(selectedCountry);
    setMarginTop("0");
  };
  return (
    <Box
      sx={{
        width: "100%",
      }}
    >
      <FormControl fullWidth error={error}>
        <InputLabel id="country-label" sx={size === "small" ? { marginTop } : {}}>
          {label ? label : "Country"}
        </InputLabel>
        {/* <InputLabel>Country</InputLabel> */}
        <Select
          name={nameOfField}
          labelId="country-label"
          label={label ? label : "Country"}
          value={value}
          sx={{
            backgroundColor: "rgba(255, 255, 255, 0.05)",
            ...(size === "small" && { maxHeight: "40px" }),
          }}
          onChange={handleChangeCountry}
          disabled={disabled}
          renderValue={(selected) => {
            const alpha2 = countries.alpha3ToAlpha2(selected as string)?.toLowerCase();
            return (
              <Box display="flex" alignItems="center">
                <Box component="span" className={`fi fi-${alpha2}`} sx={{ mr: 1 }} />
                {getCountryName(selected as string)}
              </Box>
            );
          }}
        >
          {CountryList}
        </Select>
        {error && <FormHelperText>{helperText}</FormHelperText>}
      </FormControl>
    </Box>
  );
};

export default Country;
