import React, { FC, ChangeEvent } from "react";
import { TableRow, TableCell, TextField, FormControl, InputLabel, Select, MenuItem } from "@mui/material";

interface ColumnProps<T> {
  header: string;
  accessor: keyof T | string;
  filterable?: boolean;
  filterType?: "text" | "select";
  filterOptions?: string[];
}

interface TableFiltersProps {
  columns: ColumnProps<any>[];
  filters: { [key: string]: string };
  onFilterChange: (name: string, value: string) => void;
  subColumns?: ColumnProps<any>[]; // Añade subColumns como opcional
  subFilters?: { [key: string]: string };
  onSubFilterChange?: (name: string, value: string) => void;
}

const TableFilters: FC<TableFiltersProps> = ({
  columns,
  filters,
  onFilterChange,
  subColumns,
  subFilters = {},
  onSubFilterChange,
}) => {
  const handleFilterChange = (event: ChangeEvent<HTMLInputElement | { name?: string; value: unknown }>) => {
    const { name, value } = event.target;
    onFilterChange(name as string, value as string);
  };

  const handleSubFilterChange = (event: ChangeEvent<HTMLInputElement | { name?: string; value: unknown }>) => {
    if (onSubFilterChange) {
      const { name, value } = event.target;
      onSubFilterChange(name as string, value as string);
    }
  };

  return (
    <>
      <TableRow>
        <TableCell />
        <TableCell />
        {columns.map((column) => (
          <TableCell key={column.accessor as string}>
            {column.filterable ? (
              column.filterType === "select" ? (
                <FormControl variant="outlined" size="small" fullWidth>
                  <InputLabel>{column.header}</InputLabel>
                  <Select
                    name={column.accessor as string}
                    value={filters[column.accessor as string] || ""}
                    onChange={handleFilterChange}
                    label={column.header}
                  >
                    <MenuItem value="">
                      <em>All</em>
                    </MenuItem>
                    {column.filterOptions?.map((option) => (
                      <MenuItem key={option} value={option}>
                        {option}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
              ) : (
                <TextField
                  name={column.accessor as string}
                  placeholder={`Filter ${column.header}`}
                  variant="contained"
                  size="small"
                  //fullWidth
                  value={filters[column.accessor as string] || ""}
                  onChange={handleFilterChange}
                />
              )
            ) : null}
          </TableCell>
        ))}
      </TableRow>
      
      {subColumns && onSubFilterChange && (
        <TableRow>
          <TableCell colSpan={columns.length + 2}>
            <TableRow>
              {subColumns.map((column) => (
                <TableCell key={column.accessor as string}>
                  {column.filterable ? (
                    column.filterType === "select" ? (
                      <FormControl variant="outlined" size="small" fullWidth>
                        <InputLabel>{column.header}</InputLabel>
                        <Select
                          name={column.accessor as string}
                          value={subFilters[column.accessor as string] || ""}
                          onChange={handleSubFilterChange}
                          label={column.header}
                        >
                          <MenuItem value="">
                            <em>All</em>
                          </MenuItem>
                          {column.filterOptions?.map((option) => (
                            <MenuItem key={option} value={option}>
                              {option}
                            </MenuItem>
                          ))}
                        </Select>
                      </FormControl>
                    ) : (
                      <TextField
                        name={column.accessor as string}
                        placeholder={`Filter ${column.header}`}
                        variant="outlined"
                        size="small"
                        fullWidth
                        value={subFilters[column.accessor as string] || ""}
                        onChange={handleSubFilterChange}
                      />
                    )
                  ) : null}
                </TableCell>
              ))}
            </TableRow>
          </TableCell>
        </TableRow>
      )}
    </>
  );
};

export default TableFilters;
