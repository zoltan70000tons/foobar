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
} from "@mui/material";
import { KeyboardArrowDown, KeyboardArrowUp } from "@mui/icons-material";

interface RowProps<T> {
  row: T;
  columns: {
    header: string;
    accessor: keyof T | string;
    sortable?: boolean;
    filtrable?: boolean;
    draw?: (row: T) => React.ReactNode;
  }[];
  subRows?: any[];
  subColumns?: {
    header: string;
    accessor: keyof any | string;
    sortable?: boolean;
    filtrable?: boolean;
    draw?: (row: any) => React.ReactNode;
  }[];
  isOpen: boolean;
  onToggle: () => void;
  isSelected: boolean;
  onSelectRow: () => void;
  showCheckBox?: boolean;
  onSelectSubRow?: (id: string | number) => void;
  selectedSubRows?: (string | number)[];
  showSubTableFilters?: boolean;
  onManageSubRows?: () => void; 
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
  onSelectSubRow,
  selectedSubRows = [],
  showSubTableFilters,
  onManageSubRows, 
}) => {
  const [subPage, setSubPage] = useState(0);
  const [subRowsPerPage, setSubRowsPerPage] = useState(5);
  const [subFilters, setSubFilters] = useState<{ [key: string]: string }>({});
  const [subSort, setSubSort] = useState<{
    key: keyof any | string;
    direction: "asc" | "desc";
  }>({
    key: subColumns && subColumns[0]?.accessor,
    direction: "asc",
  });

  const handleSubPageChange = (
    event: React.MouseEvent<HTMLButtonElement> | null,
    newPage: number
  ) => {
    setSubPage(newPage);
  };

  const handleSubRowsPerPageChange = (event: ChangeEvent<HTMLInputElement>) => {
    setSubRowsPerPage(parseInt(event.target.value, 10));
    setSubPage(0);
  };

  const handleSubFilterChange = (event: ChangeEvent<HTMLInputElement>) => {
    setSubFilters({
      ...subFilters,
      [event.target.name]: event.target.value,
    });
    setSubPage(0);
  };

  const handleSubSort = (key: keyof any | string) => {
    setSubSort((prevSort) => ({
      key,
      direction:
        prevSort.key === key && prevSort.direction === "asc" ? "desc" : "asc",
    }));
  };

  const filteredSubRows = subRows?.filter((subRow) => {
    return Object.keys(subFilters).every((key) => {
      const filterValue = subFilters[key]?.toLowerCase() || "";
      const rowValue = subRow[key]?.toString().toLowerCase() || "";
      return rowValue.includes(filterValue);
    });
  }) || [];

  const sortedSubRows = filteredSubRows.sort((a, b) => {
    const aValue = a[subSort.key];
    const bValue = b[subSort.key];

    if (typeof aValue === "string" && typeof bValue === "string") {
      return subSort.direction === "asc"
        ? aValue.localeCompare(bValue)
        : bValue.localeCompare(aValue);
    }

    if (typeof aValue === "number" && typeof bValue === "number") {
      return subSort.direction === "asc" ? aValue - bValue : bValue - aValue;
    }

    return 0;
  });

  const paginatedSubRows = sortedSubRows.slice(
    subPage * subRowsPerPage,
    subPage * subRowsPerPage + subRowsPerPage
  );

  const handleSelectAllSubRows = (event: ChangeEvent<HTMLInputElement>) => {
    if (event.target.checked) {
      const allSubRowIds = sortedSubRows.map((subRow) => subRow.id);
      if (onSelectSubRow) {
        allSubRowIds.forEach((id) => onSelectSubRow(id));
      }
    } else {
      if (onSelectSubRow) {
        sortedSubRows.forEach((subRow) => onSelectSubRow(subRow.id));
      }
    }
  };

  return (
    <>
      <TableRow>
        {showCheckBox && (
          <TableCell padding="checkbox">
            <Checkbox checked={isSelected} onChange={onSelectRow} />
          </TableCell>
        )}
        <TableCell>
          {subRows ? (
            <IconButton onClick={onToggle}>
              {isOpen ? <KeyboardArrowUp /> : <KeyboardArrowDown />}
            </IconButton>
          ) : null}
        </TableCell>
        {columns.map((column) => (
          <TableCell key={column.accessor as string}>
            {column.draw ? column.draw(row) : row[column.accessor]}
          </TableCell>
        ))}
      </TableRow>
      {subRows && (
        <TableRow sx={{ 'background' : '#1C252C'}}>
          <TableCell
            style={{ paddingBottom: 0, paddingTop: 0 }}
            colSpan={columns.length + (showCheckBox ? 2 : 1)}
          >
            <Collapse in={isOpen} timeout="auto" unmountOnExit  >
              <Box margin={1}>
                <Table size="small">
                  <TableHead>
                    <TableRow>
                      <TableCell padding="checkbox">
                        <Checkbox
                          indeterminate={
                            selectedSubRows.length > 0 &&
                            selectedSubRows.length < sortedSubRows.length
                          }
                          checked={
                            sortedSubRows.length > 0 &&
                            selectedSubRows.length === sortedSubRows.length
                          }
                          onChange={handleSelectAllSubRows}
                        />
                      </TableCell>
                      {subColumns?.map((column) => (
                        <TableCell key={column.accessor as string}>
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
                        {subColumns?.map((column) => (
                          <TableCell key={column.accessor as string}>
                            {column.filtrable ? (
                              <TextField
                                name={column.accessor as string}
                                label=""
                                placeholder={`Filter ${column.header}`}
                                variant="outlined"
                                size="small"
                                onChange={handleSubFilterChange}
                              />
                            ) : null}
                          </TableCell>
                        ))}
                      </TableRow>
                    )}

                    {selectedSubRows.length > 0 && (
                      <TableRow>
                        <TableCell
                          colSpan={subColumns?.length! + 1}
                          style={{ textAlign: "right" }}
                        >
                          <Button
                            variant="contained"
                            color="primary"
                            onClick={() => {
                              if (onManageSubRows) onManageSubRows();
                            }}
                          >
                            Manage ({selectedSubRows.length})
                          </Button>
                        </TableCell>
                      </TableRow>
                    )}
                  </TableHead>
                  <TableBody>
                    {paginatedSubRows.map((subRow) => (
                      <TableRow key={subRow.id}>
                        <TableCell padding="checkbox">
                          <Checkbox
                            checked={selectedSubRows.includes(subRow.id)}
                            onChange={() =>
                              onSelectSubRow && onSelectSubRow(subRow.id)
                            }
                          />
                        </TableCell>
                        {subColumns?.map((column) => (
                          <TableCell key={column.accessor as string}>
                            {column.draw
                              ? column.draw(subRow)
                              : subRow[column.accessor]}
                          </TableCell>
                        ))}
                      </TableRow>
                    ))}
                    {paginatedSubRows.length === 0 && (
                      <TableRow>
                        <TableCell
                          colSpan={subColumns?.length! + 1}
                          style={{ textAlign: "center" }}
                        >
                          No se encontraron datos.
                        </TableCell>
                      </TableRow>
                    )}
                  </TableBody>
                </Table>
                <TablePagination
                  rowsPerPageOptions={[5, 10, 25]}
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
