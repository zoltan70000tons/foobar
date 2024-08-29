import React from "react";
import { Card, CardContent, Typography, Grid, Box } from "@mui/material";
import MuiTable from "@/Components/MuiTable";
import { GridColDef } from '@mui/x-data-grid';
import AddIcon from '@mui/icons-material/Add';

interface CabinCategory {
  id: number;
  category_type: string;
  category_code: string;
  category_name: string;
  capacity: number;
  description: string;
  price: number;
  display_order: number;
  cruise_id?: number;
  event_id?: number;
}

interface CategoriesTabContentProps {
  data: CabinCategory[] | undefined;
}


const columns: GridColDef[] = [
  { field: 'id', headerName: 'ID', width: 90 },
  { field: 'category_type', headerName: 'Category Type', width: 150 },
  { field: 'category_code', headerName: 'Category Code', width: 150 },
  { field: 'category_name', headerName: 'Category Name', width: 150 },
  { field: 'capacity', headerName: 'Capacity', width: 110 },
  { field: 'description', headerName: 'Description', width: 200 },
  { field: 'price', headerName: 'Price', width: 110, valueFormatter: (params) => `$${params.value}` },
  { field: 'display_order', headerName: 'Display Order', width: 130 },
  { field: 'cruise_id', headerName: 'Cruise ID', width: 110, hide: true }, 
  { field: 'event_id', headerName: 'Event ID', width: 110, hide: true },  
];

const CategoriesTabContent: React.FC<CategoriesTabContentProps> = ({
  data,
}) => {
  if (!data || data.length === 0) {
    return (
      <Box p={3}>
        <Typography variant="h6" color="textSecondary">
          No cabin categories available.
        </Typography>
      </Box>
    );
  }

  const handleRowClick = (row: CabinCategory) => {
    //alert(`You clicked on row with ID: ${row.id}`);
  };

 

  return (
    <Grid container spacing={3}>
      <Grid item xs={12} style={{ width: '100%' }}>
        <MuiTable
          columns={columns}
          data={data}
          onRowClick={handleRowClick}
          showAddButton={true}
          showActions={true}
          showEdit={true}
          addText="Add New"
          addIcon={<AddIcon />}
        />
      </Grid>
    </Grid>
  );
};

export default CategoriesTabContent;
