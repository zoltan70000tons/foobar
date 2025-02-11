import React, { useEffect, useState } from "react";
import { Container, Stack, Chip } from "@mui/material";
import {
  DataGrid,
  GridColDef,
  GridFilterModel,
  GridFilterItem,
  GridRowModel,
} from "@mui/x-data-grid";
import { useTeamData } from "@/Hooks/useTeamData";
import ActionMenu from "./ActionMenu";
import LoadingOverlay from "@/Components/LoadingOverlay";

interface User {
  id: number;
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

  useEffect(() => {
    fetchData();
  }, []);

  // Custom filtering logic for the "name" column
  const applyCustomFilter = (row: GridRowModel, filter: GridFilterItem) => {
    console.log(row.name);
    if (filter.columnField === "name") {
      
      const fullName = `${row.detail?.first_name || ""} ${row.detail?.last_name || ""}`.toLowerCase();
      const filterValue = (filter.value || "").toLowerCase();
      return fullName.includes(filterValue);
    }
    return true;
  };

  // Apply custom filtering logic to rows
  const filteredRows = rows.filter((row) =>
    filterModel.items.every((filter) => applyCustomFilter(row, filter))
  );

  const columns: GridColDef[] = [
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
          <Chip
            label={params.row.status}
            color={params.row.status === "Active" ? "success" : "error"}
          />
        </Stack>
      ),
    },
    {
      field: "roles",
      headerName: "Role",
      width: 300,
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
      renderCell: (params) => <ActionMenu params={params} />,
    },
  ];

  const handleFilterChange = (newFilterModel: GridFilterModel) => {
    const updatedItems = newFilterModel.items.map((item) => ({
      ...item,
      value: item.value || "", 
    }));
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
            checkboxSelection
            filterModel={filterModel} // Bind the filter model to the DataGrid
            onFilterModelChange={handleFilterChange} // Update the filter model on changes
          />
        )}
      </div>
      <LoadingOverlay open={loading} />
    </Container>
  );
};

export default List;
