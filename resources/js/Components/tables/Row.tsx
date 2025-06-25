import React, { FC, useState, ChangeEvent } from "react";
import {
  TableRow,
  TableCell,
  Checkbox,
  IconButton,
  Collapse,
  Box,
  TextField,
  Table,
  TableHead,
  TableBody,
  TablePagination,
  TableSortLabel,
  Button,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Chip,
  OutlinedInput,
} from "@mui/material";
import { KeyboardArrowDown, KeyboardArrowUp } from "@mui/icons-material";

interface ColumnProps<T> {
  header: string;
  accessor: keyof T | string;
  sortable?: boolean;
  filterable?: boolean;
  draw?: (row: T) => React.ReactNode;
  filterType?: "text" | "select";
  filterOptions?: string[];
  filterFunction?: (cellValue: any, filterValue: string) => boolean;
  width?: string;
}

interface RowProps<T> {
  row: T;
  columns: ColumnProps<T>[];
  subRows?: any[];
  subColumns?: ColumnProps<any>[];
  isOpen: boolean;
  onToggle: () => void;
  isSelected: boolean;
  onSelectRow: () => void;
  showCheckBox?: boolean;
  showSubCheckBox?: boolean;
  onSelectSubRow?: (id: string | number, status: string) => void;
  selectedSubRows?: { id: string | number; status: string }[];
  showSubTableFilters?: boolean;
  onEditTags?: (selectedTags: string[]) => void;
  onChangeStatus?: (selectedStatus: string) => void;
  tagOptions?: string[];
  statusOptions?: string[];
  perPageOptions?: number[];
}

const Row: FC<RowProps<any>> = ({
  row,
  columns,
  subRows,
  subColumns,
  isOpen,
  onToggle,
  isSelected,
  onSelectRow,
  showCheckBox,
  showSubCheckBox = true,
  onSelectSubRow,
  selectedSubRows = [],
  showSubTableFilters,
  onEditTags,
  onChangeStatus,
  tagOptions = [],
  statusOptions = [],
  perPageOptions = [8],
}) => {
  const [subPage, setSubPage] = useState(0);
  const [subRowsPerPage, setSubRowsPerPage] = useState(8);
  const [subFilters, setSubFilters] = useState<{ [key: string]: string }>({});
  const [subSort, setSubSort] = useState<{
    key: keyof any | string;
    direction: "asc" | "desc";
  }>({
    key: subColumns && subColumns[0]?.accessor,
    direction: "asc",
  });

  const [selectedTags, setSelectedTags] = useState<string[]>([]);
  const [selectedStatus, setSelectedStatus] = useState<string>("");

  const handleSubPageChange = (event: React.MouseEvent<HTMLButtonElement> | null, newPage: number) => {
    setSubPage(newPage);
  };

  const handleSubRowsPerPageChange = (event: ChangeEvent<HTMLInputElement>) => {
    setSubRowsPerPage(parseInt(event.target.value, 10));
    setSubPage(0);
  };

  const handleSubFilterChange = (event: ChangeEvent<HTMLInputElement | { name?: string; value: unknown }>) => {
    const { name, value } = event.target;
    setSubFilters({
      ...subFilters,
      [name as string]: value as string,
    });
    setSubPage(0);
  };

  const handleSubSort = (key: keyof any | string) => {
    setSubSort((prevSort) => ({
      key,
      direction: prevSort.key === key && prevSort.direction === "asc" ? "desc" : "asc",
    }));
  };

  const filteredSubRows =
    subRows?.filter((subRow) => {
      return subColumns?.every((column) => {
        const key = column.accessor as string;
        const filterValue = subFilters[key];
        if (filterValue) {
          const cellValue = subRow[key];
          if (column.filterFunction) {
            return column.filterFunction(cellValue, filterValue);
          } else if (column.filterType === "select") {
            return cellValue === filterValue;
          } else {
            const cellValueStr = cellValue?.toString().toLowerCase() || "";
            return cellValueStr.includes(filterValue.toLowerCase());
          }
        }
        return true;
      });
    }) || [];

  const sortedSubRows = filteredSubRows.sort((a, b) => {
    const aValue = a[subSort.key];
    const bValue = b[subSort.key];

    if (typeof aValue === "string" && typeof bValue === "string") {
      return subSort.direction === "asc" ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
    }

    if (typeof aValue === "number" && typeof bValue === "number") {
      return subSort.direction === "asc" ? aValue - bValue : bValue - aValue;
    }

    return 0;
  });

  const paginatedSubRows = sortedSubRows.slice(subPage * subRowsPerPage, subPage * subRowsPerPage + subRowsPerPage);

  const handleSelectAllSubRows = (event: ChangeEvent<HTMLInputElement>) => {
    if (event.target.checked) {
      const allSubRowIdsAndStatuses = sortedSubRows.map((subRow) => ({
        id: subRow.id,
        status: subRow.cabin_status,
      }));

      if (onSelectSubRow) {
        allSubRowIdsAndStatuses.forEach(({ id, status }) => {
          const isAlreadySelected = selectedSubRows.some((selectedRow) => selectedRow.id === id);

          if (!isAlreadySelected) {
            onSelectSubRow(id, status);
          }
        });
      }
    } else {
      if (onSelectSubRow) {
        sortedSubRows.forEach((subRow) => {
          onSelectSubRow(subRow.id, subRow.cabin_status);
        });
      }
    }
  };

  const handleSelectSubRow = (id: string | number, status: string) => {
    if (onSelectSubRow) {
      onSelectSubRow(id, status);
    }
  };

  const handleTagChange = (event: any) => {
    const {
      target: { value },
    } = event;
    setSelectedTags(typeof value === "string" ? value.split(",") : value);
  };

  const handleStatusChange = (event: any) => {
    setSelectedStatus(event.target.value);
  };

  const handleEditTags = () => {
    if (onEditTags) {
      onEditTags(selectedTags);
    }
    setSelectedTags([]);
  };

  const handleChangeStatus = () => {
    if (onChangeStatus) {
      onChangeStatus(selectedStatus);
    }
    setSelectedStatus("");
  };

  return (
    <>
      <TableRow>
        {showCheckBox && (
          <TableCell padding="checkbox">
            <Checkbox checked={isSelected} onChange={onSelectRow} />
          </TableCell>
        )}
        <TableCell size="small">
          {subRows ? (
            <IconButton onClick={onToggle}>{isOpen ? <KeyboardArrowUp /> : <KeyboardArrowDown />}</IconButton>
          ) : null}
        </TableCell>
        {columns.map((column) => (
          <TableCell
            key={column.accessor as string}
            sx={column.width ? { width: column.width, wordWrap: "break-word" } : {}}
          >
            {column.draw ? column.draw(row) : row[column.accessor]}
          </TableCell>
        ))}
      </TableRow>
      {subRows && (
        <TableRow sx={{ background: "#272931" }}>
          <TableCell style={{ paddingBottom: 0, paddingTop: 0 }} colSpan={columns.length + (showCheckBox ? 2 : 1)}>
            <Collapse in={isOpen} timeout="auto" unmountOnExit>
              <Box margin={1}>
                {selectedSubRows.length > 0 && (
                  <Box mb={2} mt={2}>
                    <FormControl variant="outlined" size="small" style={{ minWidth: 200, marginRight: 8 }}>
                      <InputLabel>Tags</InputLabel>
                      <Select
                        multiple
                        value={selectedTags}
                        onChange={handleTagChange}
                        input={<OutlinedInput label="Tags" />}
                        renderValue={(selected) => (
                          <Box
                            sx={{
                              display: "flex",
                              flexWrap: "wrap",
                              gap: 0.5,
                            }}
                          >
                            {(selected as string[]).map((value) => (
                              <Chip size="small" key={value} label={value} />
                            ))}
                          </Box>
                        )}
                      >
                        {tagOptions.map((tag) => (
                          <MenuItem key={tag} value={tag}>
                            {tag}
                          </MenuItem>
                        ))}
                      </Select>
                    </FormControl>
                    <Button
                      variant="contained"
                      color="primary"
                      onClick={handleEditTags}
                      style={{ marginRight: 8 }}
                      disabled={selectedTags.length === 0}
                    >
                      Edit Tags ({selectedSubRows.length})
                    </Button>
                    <FormControl variant="outlined" size="small" style={{ minWidth: 200, marginRight: 8 }}>
                      <InputLabel>Status</InputLabel>
                      <Select value={selectedStatus} onChange={handleStatusChange} label="Status">
                        {statusOptions.map((status) => (
                          <MenuItem key={status} value={status}>
                            {status === "RESERVED" ? "EXCLUDED" : status}
                          </MenuItem>
                        ))}
                      </Select>
                    </FormControl>
                    <Button
                      variant="contained"
                      color="secondary"
                      onClick={handleChangeStatus}
                      disabled={!selectedStatus}
                    >
                      Change Status ({selectedSubRows.length})
                    </Button>
                  </Box>
                )}
                <Table size="small">
                  <TableHead>
                    <TableRow>
                      {showSubCheckBox && (
                        <TableCell padding="checkbox">
                          <Checkbox
                            indeterminate={selectedSubRows.length > 0 && selectedSubRows.length < sortedSubRows.length}
                            checked={sortedSubRows.length > 0 && selectedSubRows.length === sortedSubRows.length}
                            onChange={handleSelectAllSubRows}
                          />
                        </TableCell>
                      )}
                      {subColumns?.map((column, index) => (
                        <TableCell key={`filter-${index}`}>
                          {column.sortable ? (
                            <TableSortLabel
                              active={subSort.key === column.accessor}
                              direction={subSort.direction}
                              onClick={() => handleSubSort(column.accessor)}
                            >
                              {column.header}
                            </TableSortLabel>
                          ) : (
                            column.header
                          )}
                        </TableCell>
                      ))}
                    </TableRow>
                    {showSubTableFilters && (
                      <TableRow>
                        <TableCell />
                        {subColumns?.map((column, index) => (
                          <TableCell key={`filter-${index}`}>
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
                                      <MenuItem key={`filter-option-${option}`} value={option}>
                                        {option === "RESERVED" ? "EXCLUDED" : option}
                                      </MenuItem>
                                    ))}
                                  </Select>
                                </FormControl>
                              ) : (
                                <TextField
                                  name={column.accessor as string}
                                  label=""
                                  placeholder={`Filter ${column.header}`}
                                  variant="outlined"
                                  size="small"
                                  onChange={handleSubFilterChange}
                                />
                              )
                            ) : null}
                          </TableCell>
                        ))}
                      </TableRow>
                    )}
                  </TableHead>
                  <TableBody>
                    {paginatedSubRows.map((subRow, index) => {

                      //const uniqueIndex = `${index}-${subRow.id}`; // Create a unique key for each subRow

                      return (
                        <TableRow key={subRow.id}>
                          {showSubCheckBox && (
                            <TableCell padding="checkbox">
                              <Checkbox
                                checked={selectedSubRows.some((selected) => selected.id === subRow.id)}
                                onChange={() => handleSelectSubRow(subRow.id, subRow.cabin_status)}
                              />
                            </TableCell>
                          )}
                          {subColumns?.map((column, index) => (
                            <TableCell key={subRow.id + index} sx={{ width: column?.width || "100px" }}>
                              {column.draw ? column.draw(subRow) : subRow[column.accessor]}
                            </TableCell>
                          ))}
                        </TableRow>
                      );
                    })}
                    {paginatedSubRows.length === 0 && (
                      <TableRow>
                        <TableCell colSpan={subColumns?.length! + 1}>No data found.</TableCell>
                      </TableRow>
                    )}
                  </TableBody>
                </Table>
                <TablePagination
                  rowsPerPageOptions={perPageOptions}
                  component="div"
                  count={sortedSubRows.length}
                  rowsPerPage={subRowsPerPage}
                  page={subPage}
                  onPageChange={handleSubPageChange}
                  onRowsPerPageChange={handleSubRowsPerPageChange}
                  labelRowsPerPage="Rows per page"
                />
              </Box>
            </Collapse>
          </TableCell>
        </TableRow>
      )}
    </>
  );
};

export default Row;
