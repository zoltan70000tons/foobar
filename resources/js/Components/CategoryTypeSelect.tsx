import React from "react";
import { CategoryTypes, CategoryTypeLabels } from "@/enums/CategoryTypeEnum";
import {
  MenuItem,
  Select,
  SelectChangeEvent,
  FormControl,
  InputLabel,
  FormHelperText,
} from "@mui/material";

const CategoryTypeSelect = ({
  value,
  onChange,
  error,
  disabled = false
}: {
  value: CategoryTypes;
  onChange: (event: SelectChangeEvent<CategoryTypes>) => void;
  error: { category_type?: string };
  disabled?: Boolean
}) => {
  return (
    <FormControl fullWidth error={Boolean(error.category_type)}>
      <InputLabel id="category-type-label">Category Type</InputLabel>
      <Select
        labelId="category-type-label"
        id="category-type"
        value={value}
        label="Category Type"
        onChange={onChange}
        name="category_type"
        disabled={disabled}
      >
        {Object.entries(CategoryTypeLabels).map(([type, label]) => (
          <MenuItem key={type} value={type}>
            {label}
          </MenuItem>
        ))}
      </Select>
      {error.category_type && (
        <FormHelperText>{error.category_type}</FormHelperText>
      )}
    </FormControl>
  );
};

export default CategoryTypeSelect;
