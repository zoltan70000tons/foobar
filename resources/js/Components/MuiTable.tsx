import React, { FC, useState, ChangeEvent, MouseEvent, useEffect } from "react";
import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  IconButton,
  Collapse,
  Box,
  TablePagination,
  TextField,
  CircularProgress,
  TableSortLabel,
  Checkbox,
  Button
} from "@mui/material";
import { KeyboardArrowDown, KeyboardArrowUp } from "@mui/icons-material";

interface DataGridProps<T> {
  columns: {
    header: string;
    accessor: keyof T;
    sortable?: boolean;
    draw?: (row: T) => React.ReactNode;
  }[];
  data: T[];
  subColumns?: {
    header: string;
    accessor: keyof T;
    sortable?: boolean;
    draw?: (row: T) => React.ReactNode;
  }[];
  serverSidePagination?: boolean;
  fetchData?: (
    page: number,
    rowsPerPage: number,
    filters: { [key: string]: string },
    sort: { key: keyof T; direction: "asc" | "desc" }
  ) => Promise<{ data: T[]; total: number }>;
  showCheckBox?: boolean;
}

interface RowProps<T> {
  row: T;
  columns: {
    header: string;
    accessor: keyof T;
    draw?: (row: T) => React.ReactNode;
  }[];
  subRows?: T[];
  subColumns?: {
    header: string;
    accessor: keyof T;
    draw?: (row: T) => React.ReactNode;
  }[];
  isOpen: boolean;
  onToggle: () => void;
  isSelected: boolean;
  onSelectRow: () => void;
  showCheckBox?: boolean;
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
}) => {
  const [subPage, setSubPage] = useState(0);
  const [subRowsPerPage, setSubRowsPerPage] = useState(5);
  const [selectedSubRows, setSelectedSubRows] = useState<number[]>([]);

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

  const handleSelectAllSubRows = (event: ChangeEvent<HTMLInputElement>) => {
    if (event.target.checked) {
      const allRows = subRows?.map((_, index) => index) || [];
      setSelectedSubRows(allRows);
    } else {
      setSelectedSubRows([]);
    }
  };

  const handleSelectRow = (index: number) => {
    setSelectedSubRows((prev) =>
      prev.includes(index) ? prev.filter((i) => i !== index) : [...prev, index]
    );
  };

  const paginatedSubRows =
    subRows?.slice(
      subPage * subRowsPerPage,
      subPage * subRowsPerPage + subRowsPerPage
    ) || [];

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
        <TableRow>
          <TableCell
            style={{ paddingBottom: 0, paddingTop: 0 }}
            colSpan={columns.length + 2}
          >
            <Collapse in={isOpen} timeout="auto" unmountOnExit>
              <Box margin={1}>
                <Box display="flex" justifyContent="space-between" alignItems="center" p={2}>
                  <Box>
                    {selectedSubRows.length > 0 && (
                      <Button
                        variant="contained"
                        color="primary"
                        onClick={() => {
                          // Handle applying tags to selected sub-rows
                          console.log("Applying tags to:", selectedSubRows);
                        }}
                      >
                        Apply Tags
                      </Button>
                    )}
                  </Box>
                  <Box>
                    <TextField
                      label="Tags"
                      variant="outlined"
                      size="small"
                      placeholder="Add tags"
                    />
                  </Box>
                </Box>
                <Table size="small">
                  <TableHead>
                    <TableRow>
                      <TableCell padding="checkbox">
                        <Checkbox
                          checked={selectedSubRows.length === subRows.length}
                          onChange={handleSelectAllSubRows}
                        />
                      </TableCell>
                      {subColumns?.map((column) => (
                        <TableCell key={column.accessor as string}>
                          {column.header}
                        </TableCell>
                      ))}
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {paginatedSubRows.map((subRow, index) => (
                      <TableRow key={index}>
                        <TableCell padding="checkbox">
                          <Checkbox
                            checked={selectedSubRows.includes(index)}
                            onChange={() => handleSelectRow(index)}
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
                  </TableBody>
                </Table>
                <TablePagination
                  rowsPerPageOptions={[5, 10, 25]}
                  component="div"
                  count={subRows.length}
                  rowsPerPage={subRowsPerPage}
                  page={subPage}
                  onPageChange={handleSubPageChange}
                  onRowsPerPageChange={handleSubRowsPerPageChange}
                />
              </Box>
            </Collapse>
          </TableCell>
        </TableRow>
      )}
    </>
  );
};

const MuiTable: FC<DataGridProps<any>> = ({
  columns,
  data,
  subColumns,
  serverSidePagination = false,
  fetchData,
  showCheckBox,
}) => {
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [expandedRowIndex, setExpandedRowIndex] = useState<number | null>(null);
  const [filters, setFilters] = useState<{ [key: string]: string }>({});
  const [sort, setSort] = useState<{
    key: keyof any;
    direction: "asc" | "desc";
  }>({
    key: columns[0]?.accessor,
    direction: "asc",
  });
  const [paginatedData, setPaginatedData] = useState<any[]>([]);
  const [totalCount, setTotalCount] = useState(0);
  const [loading, setLoading] = useState(false);
  const [selectedRows, setSelectedRows] = useState<number[]>([]);

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

  const handleSelectRow = (index: number) => {
    setSelectedRows((prevSelectedRows) =>
      prevSelectedRows.includes(index)
        ? prevSelectedRows.filter((i) => i !== index)
        : [...prevSelectedRows, index]
    );
  };

  const handleSelectAllRows = (event: ChangeEvent<HTMLInputElement>) => {
    if (event.target.checked) {
      const allRows = data.map((_, index) => index);
      setSelectedRows(allRows);
    } else {
      setSelectedRows([]);
    }
  };

  const sortedData = !serverSidePagination
    ? data.sort((a, b) => {
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

  const handleSort = (key: keyof any) => {
    setSort((prevSort) => ({
      key,
      direction: prevSort.direction === "asc" ? "desc" : "asc",
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

  return (
    <Paper>
      <TableContainer>
        <Table>
          <TableHead>
            <TableRow>
              {showCheckBox && (
                <TableCell padding="checkbox">
                  <Checkbox
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
          </TableHead>
          <TableBody>
            {loading ? (
              <TableRow>
                <TableCell colSpan={columns.length + 2}>
                  <Box display="flex" justifyContent="center" p={2}>
                    <CircularProgress />
                  </Box>
                </TableCell>
              </TableRow>
            ) : (
              paginatedRows.map((row, index) => (
                <Row
                  key={index}
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
                  isSelected={selectedRows.includes(index)}
                  onSelectRow={() => handleSelectRow(index)}
                  showCheckBox={showCheckBox}
                />
              ))
            )}
          </TableBody>
        </Table>
      </TableContainer>
      <TablePagination
        rowsPerPageOptions={[5, 10, 25]}
        component="div"
        count={serverSidePagination ? totalCount : data.length}
        rowsPerPage={rowsPerPage}
        page={page}
        onPageChange={handlePageChange}
        onRowsPerPageChange={handleRowsPerPageChange}
      />
    </Paper>
  );
};

export default MuiTable;
