import React from "react";
import { Select, MenuItem, FormControl, InputLabel, FormHelperText, SelectChangeEvent } from "@mui/material";

interface UMSelectProps {
  name: string;
  id: string;
  value: string;
  options: { value: string; label: string }[];
  onChange: (event: SelectChangeEvent<{ value: string }>) => void;
  error?: { [key: string]: string };
  label?: string | undefined;
  disabled?: Boolean;
}

const UMSelect: React.FC<UMSelectProps> = ({
  name,
  id,
  value,
  options,
  onChange,
  error = "",
  label = "",
  disabled = false,
}) => {
  const validOptions = Array.isArray(options) ? options : [];
  return (
    <FormControl fullWidth error={Boolean(Object.keys(error).length)}>
      <InputLabel id={`${id}-label`}>{label}</InputLabel>
      <Select
        labelId={`${id}-label`}
        id={id}
        name={name}
        value={value}
        onChange={onChange}
        disabled={disabled}
        displayEmpty
      >
        {validOptions.map((option) => (
          <MenuItem key={option.value} value={option.value}>
            {option.label}
          </MenuItem>
        ))}
      </Select>
      {Object.keys(error).length > 0 && <FormHelperText>{Object.values(error)}</FormHelperText>}
    </FormControl>
  );
};

export default UMSelect;
