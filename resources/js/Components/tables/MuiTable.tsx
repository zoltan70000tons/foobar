import React, { useState, useEffect, ChangeEvent, MouseEvent } from "react";
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
  Select,
  MenuItem,
  Box,
} from "@mui/material";
import Row from "./Row";
import { DateRange, HeaderDateRange } from "@/Components/HeaderDateRange";
import { Passenger } from "@/interfaces/Passenger";
import { Booking } from "@/types/booking";
import { Cabin } from "@/interfaces/Cabin";
import { useRemember } from '@inertiajs/react';

interface ColumnProps<T> {
  dateRange?: string;
  header: string;
  accessor: keyof T | string;
  sortable?: boolean;
  filterable?: boolean;
  draw?: (row: T) => React.ReactNode;
  filterType?: "text" | "select";
  filterOptions?: string[];
  width?: string;
}

interface BaseRow<T> {
  id: string | number;
  subRows?: T[];
}

interface DataGridProps<T> {
  columns: ColumnProps<T>[];
  data: T[];
  subColumns?: ColumnProps<Passenger | Booking | Cabin>[];
  serverSidePagination?: boolean;
  fetchData?: (
    page: number,
    rowsPerPage: number,
    filters: { [key: string]: string },
    sort: { key: keyof T | string; direction: "asc" | "desc" },
    dateRangeState: Record<string, DateRange>
  ) => Promise<{ data: T[]; total: number | null | undefined }>;
  showCheckBox?: boolean;
  showSubCheckBox?: boolean;
  onApplyTags?: (subRowIds: (string | number)[], selectedTags: string[]) => void;
  onApplyState?: (subRowIds: (string | number | object)[], selectedStatus: string) => void;
  onCustomFilter?: (value: string) => void;
  showSubTableFilters?: boolean;
  tagOptions?: string[];
  statusOptions?: string[];
  showCustomFilter?: boolean;
  rememberKey?: string
}

function MuiTable<T>(props: DataGridProps<T>) {
  const {
    columns,
    data,
    subColumns,
    serverSidePagination = false,
    fetchData,
    showCheckBox,
    showSubCheckBox = true,
    onApplyTags,
    onApplyState,
    onCustomFilter,
    showSubTableFilters = false,
    tagOptions = [],
    statusOptions = [],
    showCustomFilter = false,
    rememberKey = 'MuiTable'
  } = props;

  const [page, setPage] = useRemember<number>(0, `${rememberKey}:page`);
  const [rowsPerPage, setRowsPerPage] = useRemember<number>(10, `${rememberKey}:rpp`);
  const [expandedRowId, setExpandedRowId] = useState<string | number | null>(null);
  const [filters, setFilters] = useRemember<Record<string, string>>({}, `${rememberKey}:filters`);
  const [sort, setSort] = useRemember<{key: keyof T | string; direction:'asc'|'desc'}>(
    { key: props.columns[0]?.accessor ?? '', direction: 'asc' },
    `${rememberKey}:sort`
  );
  const [customFilter, setCustomFilter] = useRemember<string>('', `${rememberKey}:q`);
  const [dateRangeState, setDateRangeState] = useRemember<Record<string, any>>(
    {}, `${rememberKey}:dr`
  );
  
  
  const [subFilters, setSubFilters] = useState<{ [key: string]: string }>({});
  const [selectedRows, setSelectedRows] = useState<(string | number)[]>([]);
  const [selectedSubRows, setSelectedSubRows] = useState<{ id: string | number; status: string }[]>([]);
  // const [sort, setSort] = useState<{
  //   key: keyof T | string;
  //   direction: "asc" | "desc";
  // }>({
  //   key: columns[0]?.accessor ?? "",
  //   direction: "asc",
  // });

  const [loading, setLoading] = useState(false);
  const [paginatedData, setPaginatedData] = useState<T[]>([]);
  const [totalCount, setTotalCount] = useState(0);
  // const [customFilter, setCustomFilter] = useState("");
  // const [dateRangeState, setDateRangeState] = useState<Record<string, DateRange>>({});

  useEffect(() => {
    if (serverSidePagination && fetchData) {
      const fetchTableData = async () => {
        setLoading(true);
        try {
          const response = await fetchData(page, rowsPerPage, filters, sort, dateRangeState);
          setPaginatedData(response.data);
          setTotalCount(response.total ?? 0);
        } catch (error) {
          console.error("Error fetching data:", error);
        } finally {
          setLoading(false);
        }
      };

      fetchTableData();
    }
  }, [page, rowsPerPage, filters, sort, serverSidePagination, fetchData, dateRangeState]);

  const dataArray: T[] = Array.isArray(data) ? data : data ? Object.values(data) as T[] : [];

  const filteredData = dataArray.filter((row: T) => {
    return Object.keys(filters).every((key) => {
      const filterValue = filters[key]?.toLowerCase() || "";

      const rowKey = key as keyof T;
      const rowValue = row[rowKey];

      const rowString = rowValue != null ? rowValue.toString().toLowerCase() : "";

      return rowString.includes(filterValue);
    });
  });


  const sortedData = [...filteredData].sort((a, b) => {
    const key = sort.key as keyof typeof a;

    const aValue = a && a[key] ? a[key] : null; // Handle null/undefined
    const bValue = b && b[key] ? b[key] : null;

    // Handle null/undefined values:
    if (aValue === null && bValue !== null) return sort.direction === "asc" ? 1 : -1;
    if (bValue === null && aValue !== null) return sort.direction === "asc" ? -1 : 1;
    if (aValue === null && bValue === null) return 0;

    // Convert values to lowercase strings for case-insensitive sorting
    const aStr = aValue.toString().toLowerCase();
    const bStr = bValue.toString().toLowerCase();

    if (aStr < bStr) return sort.direction === "asc" ? -1 : 1;
    if (aStr > bStr) return sort.direction === "asc" ? 1 : -1;
    return 0;
  });

  const displayedData = serverSidePagination
    ? paginatedData
    : sortedData.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage);

  const handleSelectRow = (id: string | number) => {
    setSelectedRows((prevSelectedRows) =>
      prevSelectedRows.includes(id) ? prevSelectedRows.filter((i) => i !== id) : [...prevSelectedRows, id],
    );
  };

  const handleSelectAllRows = (event: ChangeEvent<HTMLInputElement>) => {
    setSelectedRows(event.target.checked ? data.map((row) => row.id) : []);
  };

  const handleFilterChange = (name: string, value: string) => {
    setFilters((prevFilters) => ({ ...prevFilters, [name]: value }));
    setPage(0); // restart pagination when change filters
  };

  const handleSubFilterChange = (name: string, value: string) => {
    setSubFilters((prevFilters) => ({ ...prevFilters, [name]: value }));
  };

  const handleSelectSubRow = (id: string | number, status: string) => {
    setSelectedSubRows((prevSelectedSubRows) => {
      const isSelected = prevSelectedSubRows.find((row) => row.id === id);
      if (isSelected) {
        return prevSelectedSubRows.filter((row) => row.id !== id);
      } else {
        return [...prevSelectedSubRows, { id, status }];
      }
    });
  };

  const handleEditTags = (selectedTags: string[]) => {
    if (onApplyTags) {
      const selectedIds = selectedSubRows.map((row) => row.id);
      onApplyTags(selectedIds, selectedTags);
    }
  };

  const handleChangeStatus = (selectedStatus: string) => {
    if (onApplyState) {
      const selectedIds = selectedSubRows.map((row) => row.id);
      onApplyState(selectedSubRows, selectedStatus);
    }
  };

  const handleSort = (key: keyof T | string) => {
    setSort((prevSort) => ({
      key,
      direction: prevSort.key === key && prevSort.direction === "asc" ? "desc" : "asc",
    }));
  };


  const handlePageChange = (event: MouseEvent<HTMLButtonElement> | null, newPage: number) => {
    setPage(newPage);
  };

  const handleRowsPerPageChange = (event: ChangeEvent<HTMLInputElement>) => {
    setRowsPerPage(parseInt(event.target.value, 10));
    setPage(0);
  };

  const handleFilterSearchChange = (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const value = e.target.value;
    setCustomFilter(value);
    if (onCustomFilter) {
      onCustomFilter(value);
    }
  };

  return (
    <Paper>
      {showCustomFilter && (
        <Box sx={{ width: "100%" }}>
          {" "}
          <TextField
            variant="outlined"
            size="small"
            value={customFilter}
            onChange={(event) => handleFilterSearchChange(event)}
            placeholder="Search"
            sx={{ margin: "1rem" }}
          />
        </Box>
      )}
      <TableContainer>
        <Table sx={{ width: "100%" }}>
          <TableHead>
            {/* first row for headers */}
            <TableRow>
              {showCheckBox && (
                <TableCell padding="checkbox">
                  <Checkbox
                    indeterminate={selectedRows.length > 0 && selectedRows.length < data.length}
                    checked={selectedRows.length === data.length}
                    onChange={handleSelectAllRows}
                  />
                </TableCell>
              )}
              <TableCell />
              {columns.map((column) => (
                <TableCell
                  key={column.accessor as string}
                  sx={column.width ? { width: column.width } : { textAlign: 'center', verticalAlign: 'middle' }}
                >
                  {column?.dateRange ? (
                    <div style={{ display: 'flex', flexDirection: 'row', alignItems: 'center', flexWrap: 'wrap' }}>
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
                      <HeaderDateRange
                        accessor={column.accessor as string}
                        dateRangeState={dateRangeState}
                        setDateRangeState={setDateRangeState}
                      />
                    </div>
                  ) : (
                    <>
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
                    </>
                  )}
                </TableCell>
              ))}
            </TableRow>

            {/* second row for filters */}
            {columns.some((column) => column.filterable) && (
              <TableRow>
                {showCheckBox && <TableCell />}
                <TableCell />
                {columns.map((column) => (
                  <TableCell key={column.accessor as string}>
                    {column.filterable &&
                      (column.filterType === "select" ? (
                        <Select
                          value={filters[column.accessor as string] || ""}
                          onChange={(event) => handleFilterChange(column.accessor as string, event.target.value)}
                          displayEmpty
                          fullWidth
                          size="small"
                        >
                          <MenuItem value="">
                            <em>All</em>
                          </MenuItem>
                          {column.filterOptions?.map((option) => (
                            <MenuItem key={option} value={option}>
                              {option === "RESERVED"
                                ? "INTERNALLY AVAILABLE"
                                : option === "AVAILABLE"
                                  ? "PUBLICLY AVAILABLE"
                                  : option}
                            </MenuItem>
                          ))}
                        </Select>
                      ) : (
                        <TextField
                          variant="outlined"
                          size="small"
                          value={filters[column.accessor as string] || ""}
                          onChange={(event) => handleFilterChange(column.accessor as string, event.target.value)}
                          placeholder={`Filter ${column.header}`}
                        />
                      ))}
                  </TableCell>
                ))}
              </TableRow>
            )}
          </TableHead>
          <TableBody>
            {loading ? (
              <TableRow>
                <TableCell colSpan={columns.length + (showCheckBox ? 2 : 1)} style={{ textAlign: "center" }}>
                  <CircularProgress />
                </TableCell>
              </TableRow>
            ) : (displayedData as BaseRow<T>[])?.length > 0 ? (
              (displayedData as BaseRow<T>[]).map((row) => (
                <Row
                  key={row.id}
                  row={row}
                  columns={columns}
                  subRows={row.subRows}
                  subColumns={subColumns}
                  isOpen={expandedRowId === row.id}
                  onToggle={() => setExpandedRowId(expandedRowId === row.id ? null : row.id)}
                  isSelected={selectedRows.includes(row.id)}
                  onSelectRow={() => handleSelectRow(row.id)}
                  showCheckBox={showCheckBox}
                  showSubCheckBox={showSubCheckBox}
                  onSelectSubRow={handleSelectSubRow}
                  selectedSubRows={selectedSubRows}
                  showSubTableFilters={showSubTableFilters}
                  onEditTags={handleEditTags}
                  onChangeStatus={handleChangeStatus}
                  tagOptions={tagOptions}
                  statusOptions={statusOptions}
                />
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={columns.length + (showCheckBox ? 2 : 1)} style={{ textAlign: "center" }}>
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
        count={
          serverSidePagination
            ? (totalCount ?? 0)
            : Array.isArray(data)
              ? data.length
              : data
                ? Object.keys(data).length
                : 0
        }
        rowsPerPage={rowsPerPage}
        page={page}
        onPageChange={handlePageChange}
        onRowsPerPageChange={handleRowsPerPageChange}
        labelRowsPerPage="Rows per page"
      />
    </Paper>
  );
};

export default MuiTable;
