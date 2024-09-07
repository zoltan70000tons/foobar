import React, { useState } from 'react';
import TextField from '@mui/material/TextField';
import InputAdornment from '@mui/material/InputAdornment';
import { NumericFormat } from 'react-number-format';

type FormatInputProps = {
  name: string;
  format?: string;
  placeholder?: string;
  label: string;
  prefix?: string;
  onChange: (name: string, value: string) => void;
  decimalScale?: number;
  error: Object | null
  disabled: boolean
};

const FormatInput: React.FC<FormatInputProps> = ({
  name,
  format,
  placeholder,
  label,
  prefix,
  onChange,
  decimalScale = 2,
  error,
  disabled= false
}) => {
  const [value, setValue] = useState<string>('');

  const handleChange = (values: { formattedValue: string; value: string }) => {
    setValue(values.formattedValue);
    onChange(name, values.value);
  };

  return (
    <NumericFormat
      value={value}
      name={name}
      customInput={TextField}
      format={format}
      placeholder={placeholder}
      label={label}
      fullWidth
      onValueChange={handleChange}
      prefix={prefix}
      decimalScale={decimalScale}
      fixedDecimalScale
      disabled={disabled}
      error={Boolean(error.price)}
      helperText={error.price}
    />
  );
};

export default FormatInput;
