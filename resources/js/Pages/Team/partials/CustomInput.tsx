import { Box, TextField } from "@mui/material";

const CustomInput: React.FC<CustomInputProps> = ({
  name,
  value,
  title,
  onChange,
  dis,
  error,
}) => {

  const sanitizeInput = (input: string) => {
    const dangerousPattern = /['";<>\\\/`&{}[\]()=|%+*^$#@!]/g;
    return input.replace(dangerousPattern, "");
  };


  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const sanitizedValue = sanitizeInput(event.target.value);
    const sanitizedEvent = {
      target: {
        name: event.target.name,
        value: sanitizedValue,
      },
    };
    onChange(sanitizedEvent as unknown as React.ChangeEvent<HTMLInputElement>);
  };

  return (
    <Box>
      <TextField
        name={name}
        value={value}
        label={title}
        onChange={handleChange}
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
