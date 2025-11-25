import React, { useEffect, useState } from "react";
import { Container, Stack, Chip, Box, Avatar } from "@mui/material";
import { DataGrid, GridColDef, GridFilterModel, GridFilterItem, GridRowModel, GridFilterInputMultipleValue, GridFilterInputValue } from "@mui/x-data-grid";
import { useTeamData } from "@/Hooks/useTeamData";
import ActionMenu from "./ActionMenu";
import PersonAddIcon from '@mui/icons-material/PersonAdd';

interface User {
  id: number;
  user_name: string;
  name: string;
  email: string;
  status: string;
  roles: string[];
}

const List: React.FC = () => {
  const { rows, fetchData, loading } = useTeamData();

  const [filterModel, setFilterModel] = useState<GridFilterModel>({
    items: [],
  });


  // Custom filtering logic for the "name" column
  const applyCustomFilter = (row: GridRowModel, filter: GridFilterItem) => {
    if (filter.columnField === "name") {
      const fullName = `${row.detail?.first_name || ""} ${row.detail?.last_name || ""}`.toLowerCase();
      const filterValue = (filter.value || "").toLowerCase();
      return fullName.includes(filterValue);
    }
    return true;
  };

  const customFilters = () => {
    return [
      {
        label: 'contains',
        value: 'contains',
        getApplyFilterFn: (filterItem) => {
          if (!filterItem.value) return null;
          return (value) =>
            Array.isArray(value) &&
            value.some((v) => v.toLowerCase().includes(filterItem.value.toLowerCase()));
        },
        InputComponent: GridFilterInputValue,
      },
      {
        label: 'equals',
        value: 'equals',
        getApplyFilterFn: (filterItem) => {
          if (!filterItem.value) return null;
          return (value) =>
            Array.isArray(value) &&
            value.includes(filterItem.value);
        },
        InputComponent: GridFilterInputValue,
      },
      {
        label: 'starts with',
        value: 'startsWith',
        getApplyFilterFn: (filterItem) => {
          if (!filterItem.value) return null;
          return (value) =>
            Array.isArray(value) &&
            value.some((v) => v.toLowerCase().startsWith(filterItem.value.toLowerCase()));
        },
        InputComponent: GridFilterInputValue,
      },
      {
        label: 'ends with',
        value: 'endsWith',
        getApplyFilterFn: (filterItem) => {
          if (!filterItem.value) return null;
          return (value) =>
            Array.isArray(value) &&
            value.some((v) => v.toLowerCase().endsWith(filterItem.value.toLowerCase()));
        },
        InputComponent: GridFilterInputValue,
      },
      {
        label: 'is empty',
        value: 'isEmpty',
        getApplyFilterFn: () => {
          return (value) => !value || value.length === 0;
        },
        InputComponent: null,
      },
      {
        label: 'is not empty',
        value: 'isNotEmpty',
        getApplyFilterFn: () => {
          return (value) => Array.isArray(value) && value.length > 0;
        },
        InputComponent: null,
      },
      {
        label: 'is any of',
        value: 'isAnyOf',
        getApplyFilterFn: (filterItem) => {
          if (!filterItem.value || !Array.isArray(filterItem.value)) return null;
          return (value) =>
            Array.isArray(value) &&
            value.some((v) => filterItem.value.includes(v));
        },
        InputComponent: GridFilterInputMultipleValue,
      },
    ];
  };



  // Apply custom filtering logic to rows
  const filteredRows = rows.filter((row) => filterModel.items.every((filter) => applyCustomFilter(row, filter)));

  const columns: GridColDef[] = [
    {
      field: "user_name",
      headerName: "Username",
      width: 200,
      renderCell: (params) => (
        <Stack direction="row" alignItems="center" height="100%">
           <Chip
                label={params.row.user_name || "Agent Name"}
                avatar={params.row?.user_name ? <Avatar>{params.row.user_name[0]}</Avatar> : <PersonAddIcon />}
                size="small"
                sx={{
                  fontSize: "0.75rem",
                  fontWeight: 500,
                  color: params.row.detail?.avatar?.badge?.text,
                  backgroundColor: params.row.detail?.avatar?.badge?.background,
                  "& .MuiChip-label": { px: 1.5 },
                }}
              /> 
        </Stack>
      ),
    },
    {
      field: "fullName",
      headerName: "Name",
      width: 200,
    },
    { field: "email", headerName: "Email", type: "string", width: 200 },
    {
      field: "status",
      headerName: "Status",
      sortable: false,
      width: 100,
      renderCell: (params) => (
        <Stack direction="row" alignItems="center" height="100%">
          <Chip label={params.row.status} color={params.row.status === "Active" ? "success" : "error"} />
        </Stack>
      ),
    },
    {
      field: "roles",
      headerName: "Role",
      width: 300,
      flex: 1,
      filterOperators: customFilters(),
      renderCell: (params) => (
        <Stack direction="row" alignItems="center" height="100%">
          {params.row.roles.map((role: string, index: number) => (
            <Chip key={index} label={role} />
          ))}
        </Stack>
      ),
    },
    {
      field: "actions",
      headerName: "Actions",
      width: 150,
      renderCell: (params) => <Box display="flex" justifyContent="flex-end" width="100%"><ActionMenu params={params} onUpdate={fetchData} /></Box>,
    },
  ];

  const handleFilterChange = (newFilterModel: GridFilterModel) => {
    const updatedItems = newFilterModel.items.map((item) => {
      if (item.operator === 'isAnyOf') {
        return item;
      }
    
      return {
        ...item,
        value: item.value ?? '',
      };
    });
    setFilterModel({ ...newFilterModel, items: updatedItems });
  };

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      <div style={{ height: 400, width: "100%" }}>
        {loading ? (
          <></>
        ) : (
          <DataGrid
            disableRowSelectionOnClick
            rows={filteredRows} // Pass the custom-filtered rows here
            columns={columns}
            getRowId={(row) => row.id}
            initialState={{
              pagination: {
                paginationModel: { page: 0, pageSize: 5 },
              },
            }}
            pageSizeOptions={[5, 10]}
            filterModel={filterModel} // Bind the filter model to the DataGrid
            onFilterModelChange={handleFilterChange} // Update the filter model on changes
          />
        )}
      </div>
      {/* <LoadingOverlay open={loading} /> */}
    </Container>
  );
};

export default List;
