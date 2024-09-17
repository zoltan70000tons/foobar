import React, { FC, useState, ChangeEvent, MouseEvent, useEffect } from "react";
import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  TablePagination,
  CircularProgress,
  TableSortLabel,
  Checkbox,
  TextField,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Fab,
  Box,
} from "@mui/material";
import { ManageAccounts } from "@mui/icons-material"; 
import Row from "./Row"; 

interface DataGridProps<T> {
  columns: {
    header: string;
    accessor: keyof T | string;
    sortable?: boolean;
    filtrable?: boolean;
    draw?: (row: T) => React.ReactNode;
  }[];
  data: T[];
  subColumns?: {
    header: string;
    accessor: keyof any | string;
    sortable?: boolean;
    filtrable?: boolean;
    draw?: (row: any) => React.ReactNode;
  }[];
  serverSidePagination?: boolean;
  fetchData?: (
    page: number,
    rowsPerPage: number,
    filters: { [key: string]: string },
    sort: { key: keyof T | string; direction: "asc" | "desc" }
  ) => Promise<{ data: T[]; total: number }>;
  showCheckBox?: boolean;
  onApplyTags?: (tags: string[], subRowIds: (string | number)[]) => void;
  onApplyState?: (state: string, subRowIds: (string | number)[]) => void;
  onChangeCabinType?: (type: string, subRowIds: (string | number)[]) => void;
  showTableFilters?: boolean;
  showSubTableFilters?: boolean;
}

const MuiTable: FC<DataGridProps<any>> = ({
  columns,
  data,
  subColumns,
  serverSidePagination = false,
  fetchData,
  showCheckBox,
  onApplyTags,
  onApplyState,
  onChangeCabinType,
  showTableFilters = false,
  showSubTableFilters = false,
}) => {
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [expandedRowIndex, setExpandedRowIndex] = useState<number | null>(null);
  const [filters, setFilters] = useState<{ [key: string]: string }>({});
  const [sort, setSort] = useState<{
    key: keyof any | string;
    direction: "asc" | "desc";
  }>({
    key: columns[0]?.accessor,
    direction: "asc",
  });
  const [paginatedData, setPaginatedData] = useState<any[]>([]);
  const [totalCount, setTotalCount] = useState(0);
  const [loading, setLoading] = useState(false);
  const [selectedRows, setSelectedRows] = useState<(string | number)[]>([]);
  const [tableFilters, setTableFilters] = useState<{ [key: string]: string }>(
    {}
  );


  const [selectedSubRows, setSelectedSubRows] = useState<(string | number)[]>(
    []
  );
  const [isManageModalOpen, setIsManageModalOpen] = useState(false);
  const [selectedTags, setSelectedTags] = useState<string[]>([]);
  const [selectedState, setSelectedState] = useState<string>("");
  const [selectedCabinType, setSelectedCabinType] = useState<string>("");


  const tagOptions = ["NOT ASSIGNED", "ASSIGNED", "OPTION 1", "OPTION 2"];
  const cabinStatuses = ["AVAILABLE", "RESERVED", "BOOKED", "MAINTENANCE"];
  const cabinTypes = ["TYPE A", "TYPE B", "TYPE C"];

  useEffect(() => {
    if (serverSidePagination && fetchData) {
      const fetchTableData = async () => {
        setLoading(true);
        try {
          const response = await fetchData(page, rowsPerPage, filters, sort);
          setPaginatedData(response.data);
          setTotalCount(response.total);
        } catch (error) {
          console.error("Error fetching data:", error);
        } finally {
          setLoading(false);
        }
      };

      fetchTableData();
    }
  }, [page, rowsPerPage, filters, sort, serverSidePagination, fetchData]);

  const handleSelectRow = (id: string | number) => {
    setSelectedRows((prevSelectedRows) =>
      prevSelectedRows.includes(id)
        ? prevSelectedRows.filter((i) => i !== id)
        : [...prevSelectedRows, id]
    );
  };

  const handleSelectAllRows = (event: ChangeEvent<HTMLInputElement>) => {
    if (event.target.checked) {
      const allRowIds = data.map((row) => row.id);
      setSelectedRows(allRowIds);
    } else {
      setSelectedRows([]);
    }
  };

  const handleTableFilterChange = (event: ChangeEvent<HTMLInputElement>) => {
    setTableFilters({
      ...tableFilters,
      [event.target.name]: event.target.value,
    });
    setPage(0); 
  };

  const filteredData = data.filter((row) => {
    return Object.keys(tableFilters).every((key) => {
      const filterValue = tableFilters[key]?.toLowerCase() || "";
      const rowValue = row[key]?.toString().toLowerCase() || "";
      return rowValue.includes(filterValue);
    });
  });

  const sortedData = !serverSidePagination
    ? filteredData.sort((a, b) => {
        const aValue = a[sort.key];
        const bValue = b[sort.key];

        if (typeof aValue === "string" && typeof bValue === "string") {
          return sort.direction === "asc"
            ? aValue.localeCompare(bValue)
            : bValue.localeCompare(aValue);
        }

        if (typeof aValue === "number" && typeof bValue === "number") {
          return sort.direction === "asc" ? aValue - bValue : bValue - aValue;
        }

        return 0;
      })
    : paginatedData;

  const paginatedRows = !serverSidePagination
    ? sortedData.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
    : paginatedData;

  const handleSort = (key: keyof any | string) => {
    setSort((prevSort) => ({
      key,
      direction:
        prevSort.key === key && prevSort.direction === "asc" ? "desc" : "asc",
    }));
  };

  const handlePageChange = (
    event: MouseEvent<HTMLButtonElement> | null,
    newPage: number
  ) => {
    setPage(newPage);
  };

  const handleRowsPerPageChange = (event: ChangeEvent<HTMLInputElement>) => {
    setRowsPerPage(parseInt(event.target.value, 10));
    setPage(0);
  };


  const handleSelectSubRow = (id: string | number) => {
    setSelectedSubRows((prevSelectedSubRows) =>
      prevSelectedSubRows.includes(id)
        ? prevSelectedSubRows.filter((i) => i !== id)
        : [...prevSelectedSubRows, id]
    );
  };

  const handleManageClick = () => {
    setIsManageModalOpen(true);
  };

  const handleManageClose = () => {
    setIsManageModalOpen(false);
    setSelectedTags([]);
    setSelectedState("");
    setSelectedCabinType("");
  };

  const handleApplyChanges = () => {
    if (onApplyTags && selectedTags.length > 0) {
      onApplyTags(selectedTags, selectedSubRows);
    }
    if (onApplyState && selectedState) {
      onApplyState(selectedState, selectedSubRows);
    }
    if (onChangeCabinType && selectedCabinType) {
      onChangeCabinType(selectedCabinType, selectedSubRows);
    }
    setIsManageModalOpen(false);
    setSelectedSubRows([]);
    setSelectedTags([]);
    setSelectedState("");
    setSelectedCabinType("");
  };

  return (
    <>
      <Paper>
        <TableContainer>
          <Table>
            <TableHead>

              <TableRow>
                {showCheckBox && (
                  <TableCell padding="checkbox">
                    <Checkbox
                      indeterminate={
                        selectedRows.length > 0 &&
                        selectedRows.length < data.length
                      }
                      checked={selectedRows.length === data.length}
                      onChange={handleSelectAllRows}
                    />
                  </TableCell>
                )}
                <TableCell />
                {columns.map((column) => (
                  <TableCell key={column.accessor as string}>
                    {column.sortable ? (
                      <TableSortLabel
                        active={sort.key === column.accessor}
                        direction={sort.direction}
                        onClick={() => handleSort(column.accessor)}
                      >
                        {column.header}
                      </TableSortLabel>
                    ) : (
                      column.header
                    )}
                  </TableCell>
                ))}
              </TableRow>

              {showTableFilters && (
                <TableRow>
                  {showCheckBox && <TableCell />}
                  <TableCell />
                  {columns.map((column) => (
                    <TableCell key={column.accessor as string}>
                      {column.filtrable ? (
                        <TextField
                          name={column.accessor as string}
                          label=""
                          placeholder={`Filter ${column.header}`}
                          variant="outlined"
                          size="small"
                          onChange={handleTableFilterChange}
                        />
                      ) : null}
                    </TableCell>
                  ))}
                </TableRow>
              )}
            </TableHead>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell
                    colSpan={columns.length + (showCheckBox ? 2 : 1)}
                    style={{ textAlign: "center" }}
                  >
                    <CircularProgress />
                  </TableCell>
                </TableRow>
              ) : paginatedRows.length > 0 ? (
                paginatedRows.map((row, index) => (
                  <Row
                    key={row.id}
                    row={row}
                    columns={columns}
                    subRows={row.subRows}
                    subColumns={subColumns}
                    isOpen={expandedRowIndex === index}
                    onToggle={() =>
                      setExpandedRowIndex(
                        expandedRowIndex === index ? null : index
                      )
                    }
                    isSelected={selectedRows.includes(row.id)}
                    onSelectRow={() => handleSelectRow(row.id)}
                    showCheckBox={showCheckBox}
                    onSelectSubRow={handleSelectSubRow}
                    selectedSubRows={selectedSubRows}
                    showSubTableFilters={showSubTableFilters}
                    onManageSubRows={handleManageClick} 
                  />
                ))
              ) : (
                <TableRow>
                  <TableCell
                    colSpan={columns.length + (showCheckBox ? 2 : 1)}
                    style={{ textAlign: "center" }}
                  >
                    No data found.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </TableContainer>
        <TablePagination
          rowsPerPageOptions={[5, 10, 25]}
          component="div"
          count={serverSidePagination ? totalCount : filteredData.length}
          rowsPerPage={rowsPerPage}
          page={page}
          onPageChange={handlePageChange}
          onRowsPerPageChange={handleRowsPerPageChange}
          labelRowsPerPage="Rows per page"
        />
      </Paper>

      <Dialog open={isManageModalOpen} onClose={handleManageClose}>
        <DialogTitle>Manage selected cabins</DialogTitle>
        <DialogContent>

        <Box display="flex" gap={2} flexDirection="row" flexWrap="wrap">
  <FormControl variant="outlined" size="small" style={{ minWidth: 200 }}>
    <InputLabel id="tags-label">Tags</InputLabel>
    <Select
      labelId="tags-label"
      multiple
      value={selectedTags}
      onChange={(event) => setSelectedTags(event.target.value as string[])}
      label="Tags"
      renderValue={(selected) => (selected as string[]).join(", ")}
    >
      {tagOptions.map((tag) => (
        <MenuItem key={tag} value={tag}>
          <Checkbox checked={selectedTags.indexOf(tag) > -1} />
          <span>{tag}</span>
        </MenuItem>
      ))}
    </Select>
  </FormControl>

  <FormControl variant="outlined" size="small" style={{ minWidth: 200 }}>
    <InputLabel id="state-label">Status</InputLabel>
    <Select
      labelId="state-label"
      value={selectedState}
      onChange={(event) => setSelectedState(event.target.value as string)}
      label="Status"
    >
      {cabinStatuses.map((status) => (
        <MenuItem key={status} value={status}>
          {status}
        </MenuItem>
      ))}
    </Select>
  </FormControl>

  <FormControl variant="outlined" size="small" style={{ minWidth: 200 }}>
    <InputLabel id="type-label">Cabin Type</InputLabel>
    <Select
      labelId="type-label"
      value={selectedCabinType}
      onChange={(event) => setSelectedCabinType(event.target.value as string)}
      label="Cabin Type"
    >
      {cabinTypes.map((type) => (
        <MenuItem key={type} value={type}>
          {type}
        </MenuItem>
      ))}
    </Select>
  </FormControl>
</Box>

        </DialogContent>
        <DialogActions>
          <Button onClick={handleManageClose} color="secondary">
            Cancel
          </Button>
          <Button
            onClick={handleApplyChanges}
            color="primary"
            disabled={
              selectedTags.length === 0 && !selectedState && !selectedCabinType
            }
          >
            Apply
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default MuiTable;
