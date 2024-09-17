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
} from "@mui/material";
import Row from "./Row"; 

interface ColumnProps<T> {
  header: string;
  accessor: keyof T | string;
  sortable?: boolean;
  filterable?: boolean;
  draw?: (row: T) => React.ReactNode;
  filterType?: "text" | "select";
  filterOptions?: string[];
}

interface DataGridProps<T> {
  columns: ColumnProps<T>[];
  data: T[];
  subColumns?: ColumnProps<any>[];
  serverSidePagination?: boolean;
  fetchData?: (
    page: number,
    rowsPerPage: number,
    filters: { [key: string]: string },
    sort: { key: keyof T | string; direction: "asc" | "desc" }
  ) => Promise<{ data: T[]; total: number }>;
  showCheckBox?: boolean;
  onApplyTags?: (subRowIds: (string | number)[], selectedTags: string[]) => void;
  onApplyState?: (
    subRowIds: (string | number)[],
    selectedStatus: string
  ) => void; // Modificado
  showTableFilters?: boolean;
  showSubTableFilters?: boolean;
  tagOptions?: string[];
  statusOptions?: string[]; 
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
  showTableFilters = false,
  showSubTableFilters = false,
  tagOptions = [],
  statusOptions = [],
}) => {
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [expandedRowId, setExpandedRowId] = useState<string | number | null>(null);
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
  const [tableFilters, setTableFilters] = useState<{ [key: string]: string }>({});
  const [selectedSubRows, setSelectedSubRows] = useState<(string | number)[]>([]);

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

  const handleEditTags = (selectedTags: string[]) => {
    if (onApplyTags) {
      onApplyTags(selectedSubRows, selectedTags);
    }
    //setSelectedSubRows([]);
  };

  const handleChangeStatus = (selectedStatus: string) => {
    if (onApplyState) {
      onApplyState(selectedSubRows, selectedStatus);
    }
    //setSelectedSubRows([]);
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
                        selectedRows.length > 0 && selectedRows.length < data.length
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
                      {column.filterable ? (
                        column.filterType === "select" ? (
                          <TextField
                            name={column.accessor as string}
                            label=""
                            placeholder={`Filter ${column.header}`}
                            variant="outlined"
                            size="small"
                            onChange={handleTableFilterChange}
                          />
                        ) : (
                          <TextField
                            name={column.accessor as string}
                            label=""
                            placeholder={`Filter ${column.header}`}
                            variant="outlined"
                            size="small"
                            onChange={handleTableFilterChange}
                          />
                        )
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
                    isOpen={expandedRowId === row.id}
                    onToggle={() =>
                      setExpandedRowId(expandedRowId === row.id ? null : row.id)
                    }
                    isSelected={selectedRows.includes(row.id)}
                    onSelectRow={() => handleSelectRow(row.id)}
                    showCheckBox={showCheckBox}
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
    </>
  );
};

export default MuiTable;
