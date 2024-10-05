import React, { FC, ReactNode, useMemo } from "react";
import { Card, CardContent, Typography, Grid, Box, Chip } from "@mui/material";
import MuiTable from "@/Components/tables/MuiTable";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import { CabinCategory } from "@/interfaces/CabinCategory";
import { Cabin } from "@/interfaces/Cabin";
import { Visibility, Edit, Delete } from "@mui/icons-material";
import { CabinStatus } from "@/enums/CabinStatus";
import axios from "axios";
import apiRoutes from "@/Helpers/ApiRoutes";
import { TagEnum } from "@/enums/TagEnum";
import { Head, router, useForm } from "@inertiajs/react";

interface CabinTabContentProps {
  data: Cabin[] | undefined;
}

const AllTabContent: React.FC<Cabin> = ({ data }) => {
  const { hasPermission } = usePermissions();

  return (
    <>Bookins</>
  );
};

export default AllTabContent;
