import React, { FC, ReactNode, useMemo } from "react";
import { Card, CardContent, Typography, Grid, Box, Chip } from "@mui/material";
import { MaterialReactTable, MRT_ColumnDef } from "material-react-table";
import MuiTable from "@/Components/tables/MuiTable";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import { CabinCategory } from "@/interfaces/CabinCategory";
import { Cabin } from "@/interfaces/Cabin";
import { Visibility, Edit, Delete } from "@mui/icons-material";
import { CabinStatus } from "@/enums/CabinStatus";
import axios from "axios";
import apiRoutes from "@/Helpers/ApiRoutes";

interface CabinTabContentProps {
  data: Cabin[] | undefined;
}

const AllTabContent: React.FC<Cabin> = ({ data}) => {
  const { hasPermission } = usePermissions();
  const event_id = data.event_id;
  const cabins = data.data;
  if (!cabins || cabins.length === 0) {
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
        filtrable:true,
        sortable: true,
      },
      {
        header: "Type",
        accessor: "category_type",
        filtrable:true,
        sortable: true,
      },
      {
        header: "Name",
        accessor: "title",
        filtrable:true,
        sortable: true,
      },
      {
        header: "Capacity",
        accessor: "capacity",
        filtrable:true,
        sortable: true,
      },
      {
        header: "Price",
        accessor: "price",
        filtrable:true,
        sortable: true,
      },
      {
        header: "Status",
        accessor: "status",
        sortable: true,
        filtrable:true
      },
      // {
      //   header: "Actions",
      //   accessor: "",
      //   draw: (row) => (
      //     <div style={{ display: "flex", gap: "10px" }}>
      //       <Visibility
      //         onClick={() => console.log(`View ${row.category_code}`)}
      //         style={{ cursor: "pointer" }}
      //       />
      //     </div>
      //   ),
      // },
    ],
    []
  );

  const subColumns = useMemo(
    () => [
      {
        accessor: "cabin_code",
        header: "Code",
        sortable: true,
        filtrable: true
      },
      {
        accessor: "cabin_type",
        header: "Type",
        sortable: true,
        filtrable: true
      },
      {
        accessor: "cabin_number",
        header: "Number",
        filtrable: true,
        sortable: true,
      },
      {
        accessor: "cabin_deck",
        header: "Deck",
        sortable: true,
        filtrable: true
      },
      {
        accessor: "cabin_status",
        header: "Status",
        filtrable: true,
        sortable: true,
        draw: (row) => (
          <Chip
            size="small"
            label={row.cabin_status}
            color={
              row.cabin_status === CabinStatus.AVAILABLE
                ? "success"
                : row.cabin_status === CabinStatus.RESERVED
                ? "default"
                : row.cabin_status === CabinStatus.BOOKED
                ? "error"
                : "default"
            }
          />
        ),
      },

      // {
      //   header: "Actions",
      //   accessor: "category_code",
      //   disableFilter: true,
      //   draw: (row) => (<></>
      //     // <div style={{ display: "flex", gap: "10px" }}>
      //     //   <Visibility
      //     //     onClick={() => console.log(`View ${row.category_code}`)}
      //     //     style={{ cursor: "pointer" }}
      //     //   />
      //     // </div>
      //   ),
      // },
    ],
    []
  );

  const manageTags = (tags, rows) => {
  
    const url = apiRoutes.addCabinTags(event_id);
    axios
      .post(url, { tags: tags, rows: rows })
      .then((response) => {})
      .catch((error) => {
        console.error("Error adding tags:", error);
      });
  };

  return (
    <MuiTable
      columns={columns}
      data={cabins}
      subColumns={subColumns}
      showCheckBox={false}
      onApplyTags={manageTags}
      showTableFilters={true}
      showSubTableFilters={true}
    />
  );
};

export default AllTabContent;
