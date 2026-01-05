import React from "react";
import { Box, FormControl, InputLabel, Select, MenuItem, FormHelperText } from "@mui/material";

interface CustomSelectProps {
  name: string;
  value: string;
  title: string;
  onChange: (event: React.ChangeEvent<{ value: unknown }>) => void;
  dis?: boolean;
  error?: string;
  options: { value: string | number; label: string }[];
}

const CustomSelect: React.FC<CustomSelectProps> = ({ name, value, title, onChange, dis, error, options }) => {
  return (
    <Box>
      <FormControl fullWidth variant="outlined" error={!!error} disabled={dis}>
        <InputLabel>{title}</InputLabel>
        <Select name={name} value={value} onChange={onChange} label={title}>
          {options.map((option) => (
            <MenuItem key={option.value} value={option.value}>
              {option.label}
            </MenuItem>
          ))}
        </Select>
        {error && <FormHelperText>{error}</FormHelperText>}
      </FormControl>
    </Box>
  );
};

export default CustomSelect;
