import React from "react";
import TextField from "@mui/material/TextField";
import { Box } from "@mui/material";

interface CustomInputProps {
  name: string;
  value: string;
  title: string;
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
  dis?: boolean;
  error?: string; 
}

const CustomInput: React.FC<CustomInputProps> = ({ name, value, title, onChange, dis, error }) => {
  return (
    <Box>
      <TextField
        name={name}
        value={value}
        label={title}
        onChange={onChange}
        fullWidth
        disabled={dis}
        error={!!error}
        helperText={error}
        variant="outlined"
        size="small"
      />
    </Box>

  );
};

export default CustomInput;
