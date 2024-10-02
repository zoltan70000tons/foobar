import React, { FC, ReactNode, useMemo } from "react";
import { Card, CardContent, Typography, Grid, Box, Chip } from "@mui/material";
import MuiTable from "@/Components/tables/MuiTable";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import { CabinCategory } from "@/interfaces/CabinCategory";
import { Cabin } from "@/interfaces/Cabin";
import { Visibility, Edit, Delete } from "@mui/icons-material";
import { CabinStatus, CabinStatusFilters, CabinStatusOverrides } from "@/enums/CabinStatus";
import axios from "axios";
import apiRoutes from "@/Helpers/ApiRoutes";
import { TagEnum } from "@/enums/TagEnum";
import { Head, router, useForm } from "@inertiajs/react";

interface CabinTabContentProps {
  data: Cabin[] | undefined;
}

const AllTabContent: React.FC<Cabin> = ({ data }) => {
  const { hasPermission } = usePermissions();
  const statusFiltersOptions = Object.values(CabinStatusFilters);
  const statusOverridesOptions = Object.values(CabinStatusOverrides);
  const tagOptions = Object.values(TagEnum);
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
        header: "Order",
        accessor: "display_order",
        filterable: false,
        sortable: true,
      },
      {
        header: "Category Code",
        accessor: "category_code",
        filterable: true,
        sortable: true,
      },
      {
        header: "Name",
        accessor: "title",
        filterable: true,
        sortable: true,
      },
      {
        header: "Price",
        accessor: "price",
        filterable: false,
        sortable: true,
      },
      {
        header: "Availability",
        accessor: "availability",
        sortable: true,
        filterable: false,
      }
    ],
    []
  );

  const subColumns = useMemo(
    () => [
      {
        accessor: "cabin_number",
        header: "Number",
        filterable: true,
        sortable: true,
      },
      {
        accessor: "cabin_type",
        header: "Type",
        sortable: true,
        filterable: true,
      },
      {
        accessor: "ticket_inventory",
        header: "Tickets Left",
        sortable: true,
        filterable: true,
      },
      {
        accessor: "deck",
        header: "Deck",
        sortable: true,
        filterable: true,
      },
      {
        accessor: "cabin_status",
        header: "Status",
        filterable: true,
        sortable: true,
        filterType: "select",
        filterOptions: statusFiltersOptions,
        draw: (row) => (
          <Chip
            size="small"
            label={row.cabin_status}
            color={
              row.cabin_status === CabinStatusFilters.AVAILABLE
                ? "success"
                : row.cabin_status === CabinStatusFilters.RESERVED
                ? "warning"
                : row.cabin_status === CabinStatusFilters.BOOKED
                ? "primary"
                : row.cabin_status === CabinStatusFilters.PARTIALY_BOOKED
                ? "secondary"
                : row.cabin_status === CabinStatusFilters.CLOSED
                ? "error"
                : "default"
            }
          />
        ),
      },
      {
        accessor: "cabin_tags",
        header: "Tags",
        filterable: true,
        filterType: "select",
        filterOptions: tagOptions,
        filterFunction: (cellValue: string[] | undefined, filterValue: string) => {
          if (!Array.isArray(cellValue) || cellValue.length === 0) {
            return filterValue === "";
          }
          return cellValue.some((tag) => {
            console.log(tag);
            console.log(cellValue);
            return tag.toLowerCase().includes(filterValue.toLowerCase());
          });
        },
        draw: (subRow) => (
          <Box sx={{ display: "flex", flexWrap: "wrap", gap: 0.5 }}>
            {Array.isArray(subRow.cabin_tags) && subRow.cabin_tags.length > 0 ? (
              subRow.cabin_tags.map((tag: string) => <Chip key={tag} label={tag} size="small" />)
            ) : (
              <em>No Tags</em>
            )}
          </Box>
        ),
      },

      {
        header: "Actions",
        accessor: "category_code",
        disableFilter: true,
        draw: (row) => (
          <div style={{ display: "flex", gap: "10px" }}>
            {hasPermission(Permissions.ViewCabins) && (
              <Visibility
                onClick={() => {
                  console.log(row);
                  router.get(route("cabins.edit", { id: event_id, cabin_id: row.id }));
                }}
                style={{ cursor: "pointer" }}
              />
            )}
          </div>
        ),
      },
    ],
    []
  );

  const manageTags = (rows, tags) => {
    const url = apiRoutes.addCabinTags(event_id);
    axios
      .post(url, { tags: tags, rows: rows })
      .then((response) => {})
      .catch((error) => {
        console.error("Error adding tags:", error);
      });
  };
  const manageStatus = (rows, status) => {
    console.log({ status: status, rows: rows });
    const url = apiRoutes.updateCabinStatus(event_id);
    axios
      .post(url, { status: status, rows: rows })
      .then((response) => {})
      .catch((error) => {
        console.error("Error adding status", error);
      });
  };

  return (
    <MuiTable
      columns={columns}
      data={cabins}
      subColumns={subColumns}
      showCheckBox={false}
      showTableFilters={true}
      showSubTableFilters={true}
      tagOptions={tagOptions}
      statusOptions={statusOverridesOptions}
      onApplyTags={manageTags}
      onApplyState={manageStatus}
    />
  );
};

export default AllTabContent;
