import React, { useMemo } from "react";
import { Card, CardContent, Typography, Grid, Box } from "@mui/material";
import MuiTable from "@/Components/tables/MuiTable";
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


  return (
    <Grid container spacing={3}>
      <Grid item xs={12} style={{ width: "100%" }}>
       Bookins
      </Grid>
    </Grid>
  );
};

export default CategoriesTabContent;
