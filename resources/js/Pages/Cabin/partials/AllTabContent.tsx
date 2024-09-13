import React, { FC, ReactNode, useMemo } from "react";
import { Card, CardContent, Typography, Grid, Box, Chip } from "@mui/material";
import { MaterialReactTable, MRT_ColumnDef } from "material-react-table";
import MuiTable from "@/Components/MuiTable";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import { CabinCategory } from "@/interfaces/CabinCategory";
import { Cabin } from "@/interfaces/Cabin";
import { Visibility, Edit, Delete } from '@mui/icons-material';
import { CabinStatus } from "@/enums/CabinStatus";

interface CabinTabContentProps {
  data: Cabin[] | undefined;
}

const AllTabContent: React.FC<Cabin> = ({ data }) => {
  const {hasPermission} = usePermissions()
  if (!data || data.length === 0) {
    return (
      <Box p={3}>
        <Typography variant="h6" color="textSecondary">
          No cabins available.
        </Typography>
      </Box>
    );
  }

  const columns = useMemo(
    () => [
      {
        header: "Code",
        accessor: "category_code",
      },
      {
        header: "Type",
        accessor: "category_type",
      },
      {
        header: "Name",
        accessor: "category_name",
      },
      {
        header: "Capacity",
        accessor: "capacity",
      },
      {
        header: "Price",
        accessor: "price",
      },
      {
        header: "Status",
        accessor: "status",
      },
      {
        header: "Actions",
        accessor: "",
        disableFilter: true,
        draw: (row) => (
          <div style={{ display: 'flex', gap: '10px' }}>
            <Visibility
              onClick={() => console.log(`View ${row.category_code}`)}
              style={{ cursor: 'pointer' }}
            />
          </div>
        ),
      },
    ],
    []
  );


  const subColumns = useMemo(
    () => [
      {
        accessor: "cabin_code",
        header: "Code",

      },
      {
        accessor: "cabin_type", 
        header: "Type",
      },
      {
        accessor: "cabin_number", 
        header: "Number",
      },
      {
        accessor: "cabin_deck",
        header: "Deck",
      },
      {
        accessor: "",
        header: "Status",
        draw: (row) => (
          <Chip size="small" label={row.cabin_status} color={
            row.cabin_status === CabinStatus.AVAILABLE
              ? 'success'
              : row.cabin_status === CabinStatus.RESERVED
              ? 'default'
              : row.cabin_status === CabinStatus.BOOKED
              ? 'error'
              : 'default' 
          }
          
          
          />
        )
      
      },
      
      {
        header: "Actions",
        accessor: "category_code",
        disableFilter: true,
        draw: (row) => (
          <div style={{ display: 'flex', gap: '10px' }}>
            <Visibility
              onClick={() => console.log(`View ${row.category_code}`)}
              style={{ cursor: 'pointer' }}
            />
          </div>
        ),
      },
    ],
    []
  );
  
  

  return (
      <MuiTable
        columns={columns}
        data={data}
        subColumns={subColumns}
        showCheckBox={false}
      />
  );
};

export default AllTabContent;
