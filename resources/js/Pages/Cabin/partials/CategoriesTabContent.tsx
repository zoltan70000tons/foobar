import React, { useMemo } from "react";
import { Card, CardContent, Typography, Grid, Box } from "@mui/material";
import MuiTable from "@/Components/MuiTable";
import AddIcon from "@mui/icons-material/Add";
import { Head, router } from "@inertiajs/react";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import { Visibility, Edit, Delete } from "@mui/icons-material";

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

  console.log(data);

  const { hasPermission } = usePermissions();

  const handleEditClick = (row: CabinCategory) => {
    router.get(
      route("cabinCategory.edit", { id: row.event_id, catId: row.id })
    );
  };
  const handleViewClick = (row: CabinCategory) => {
    router.get(
      route("cabinCategory.show", { id: row.event_id, catId: row.id })
    );
  };
  const handleDeleteClick = (row: CabinCategory) => {
    router.delete(
      route("cabinCategory.destroy", { id: row.event_id, catId: row.id })
    );
  };

  const onAddClick = () => {
    //router.get(route("cabinCategory.create", { id: 1 }));
  };

  const columns = useMemo(
    () => [
      {
        header: "Id",
        accessor: "id",
      },
      {
        header: "Category Type",
        accessor: "category_type",
      },
      {
        header: "Category Code",
        accessor: "category_code",
      },
      {
        header: "Category Name",
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
        header: "Display Order",
        accessor: "display_order",
      },
      {
        header: "Cruise ID",
        accessor: "cruise_id",
      },
      {
        header: "Event Id",
        accessor: "event_id",
      },

      {
        header: "Actions",
        accessor: "",
        disableFilter: true,
        draw: (row) => (
          <div style={{ display: "flex", gap: "10px" }}>
            {hasPermission(Permissions.ViewCabinCategories) && (
              <Visibility
                onClick={() => handleViewClick(row)}
                style={{ cursor: "pointer" }}
              />
            )}
            {hasPermission(Permissions.EditCabinCategories) && (
              <Edit
                onClick={() => handleEditClick(row)}
                style={{ cursor: "pointer" }}
              />
            )}
            {hasPermission(Permissions.DeleteCabinCategories) && (
              <Delete
                onClick={() => handleDeleteClick(row)}
                style={{ cursor: "pointer" }}
              />
            )}
          </div>
        ),
      },
    ],
    []
  );

  return (
    <Grid container spacing={3}>
      <Grid item xs={12} style={{ width: "100%" }}>
        <MuiTable
          columns={columns}
          data={data}
          // onRowClick={handleRowClick}
          // showAddButton={hasPermission(Permissions.CreateCabinCategories)}
          // showActions={true}
          // showView={hasPermission(Permissions.ViewCabinCategories)}
          // showEdit={hasPermission(Permissions.EditCabinCategories)}
          // showDelete={hasPermission(Permissions.DeleteCabinCategories)}
          // addText="Add New"
          // addIcon={<AddIcon />}
          // onAddClick={onAddClick}
          // onEditClick={handleEditClick}
          // onViewClick={handleViewClick}
          // onDeleteClick={handleDeleteClick}
        />
      </Grid>
    </Grid>
  );
};

export default CategoriesTabContent;
