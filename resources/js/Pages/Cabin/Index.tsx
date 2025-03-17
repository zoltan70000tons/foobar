import React, { useState, useMemo, useEffect } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";
import {
  Box,
  Container,
  Grid,
  IconButton,
  Toolbar,
  Typography,
  Tabs,
  Tab,
  CircularProgress,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
  Button,
} from "@mui/material";
import MuiTable from "@/Components/tables/MuiTable";
import { CabinStatus, CabinStatusColor, CabinStatusReduced } from "@/enums/CabinStatus";
import { TagEnum } from "@/enums/TagEnum";
import { CabinCategory } from "@/interfaces/CabinCategory";
import { Visibility, Edit, Delete } from "@mui/icons-material";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import apiRoutes from "@/Helpers/ApiRoutes";
import axios from "axios";
import LoadingOverlay from "@/Components/LoadingOverlay";
import SnackbarAlert from "@/Components/SnackbarAlert";

const Index = ({
  auth,
  event,
  categories,
  cabins,
  errors,
}: PageProps & { tab: string; data: any }) => {
  const { hasPermission } = usePermissions();
  const [selectedTab, setSelectedTab] = useState(0);

  const [openDialog, setOpenDialog] = useState(false);

  const [loading, setLoading] = useState(true);

  const [snackbar, setSnackbar] = useState({
    open: false,
    severity: "success",
    message: "",
  });

  useEffect(() => {
    if (cabins) {
      setLoading(false);
    }
  }, [cabins]);

  const handleTabChange = (event: React.ChangeEvent<{}>, newValue: number) => {
    setSelectedTab(newValue);
  };

  const columns = useMemo(
    () => [
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
      },
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
        width: "150px",
        filterType: "select",
        filterOptions: Object.values(CabinStatus),
        draw: (row) => (
          <Chip
            size="small"
            label={row.cabin_status}
            color={CabinStatusColor[row.cabin_status]}
            sx={{
              margin: "auto",
              fontSize: "0.7rem",
              fontWeight: "400",
              color: "white",
            }}
          />
        ),
      },
      {
        accessor: "cabin_tags",
        header: "Tags",
        filterable: true,
        filterType: "select",
        filterOptions: Object.values(TagEnum),
        filterFunction: (
          cellValue: string[] | undefined,
          filterValue: string
        ) => {
          if (!Array.isArray(cellValue) || cellValue.length === 0) {
            return filterValue === "";
          }
          return cellValue.some((tag) =>
            tag.toLowerCase().includes(filterValue.toLowerCase())
          );
        },
        draw: (subRow) => (
          <Box sx={{ display: "inline-flex", gap: 0.5 }}>
            {Array.isArray(subRow.cabin_tags) &&
            subRow.cabin_tags.length > 0 ? (
              subRow.cabin_tags.map((tag: string) => (
                <Chip
                  key={tag}
                  label={tag}
                  size="small"
                  sx={{ margin: "auto", fontSize: "0.7rem", fontWeight: "400" }}
                />
              ))
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
                  router.get(
                    route("cabins.edit", { id: event.id, cabin_id: row.id })
                  );
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

  // cabin categories
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

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  const onAddClick = () => {
    //router.get(route("cabinCategory.create", { id: 1 }));
  };

  const categoriesColumns = useMemo(
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

  const manageTags = (rows, tags) => {
    const url = apiRoutes.addCabinTags(event.id);
    setLoading(true);
    axios
      .post(url, { tags: tags, rows: rows })
      .then((response) => {
        router.reload({ only: ["cabins"], preserveScroll: true });
        setLoading(false);
        setSnackbar({
            open: true,
            severity: "success",
            message: "Tags edited successfully",
          });
        
      })
      .catch((error) => {
        console.error("Error adding tags:", error);
        setSnackbar({
            open: true,
            severity: "error",
            message: "Error updating tags",
          });
      });
  };

  const manageStatus = (rows, status) => {
    const hasInvalidStatus = rows.some(
      (row) =>
        row.status === CabinStatus.BOOKED ||
        row.status === CabinStatus.PARTIALLY_BOOKED
    );
    if (hasInvalidStatus) {
      setOpenDialog(true);
      return;
    }
    const url = apiRoutes.updateCabinStatus(event.id);
    const rowIds = rows.map((row) => row.id);
    setLoading(true);
    axios
      .post(url, { status: status, rows: rowIds })
      .then((response) => {
        setLoading(false);
        router.reload({ only: ["cabins"], preserveScroll: true });
        setSnackbar({
            open: true,
            severity: "success",
            message: "Status edited successfully",
          });
      })
      .catch((error) => {
        //console.error("Error updating status", error);
        setSnackbar({
            open: true,
            severity: "error",
            message: "Error updating status",
          });
      });
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Cabins"}>
      <Head title="Cabins" />
      <Toolbar sx={{ mt: 8 }}>
        <IconButton edge="start" color="inherit" aria-label="menu">
          <img src={event.image} alt="Logo" style={{ height: 40 }} />
        </IconButton>
        <Typography variant="h6" style={{ flexGrow: 1 }}>
          {event.name}
        </Typography>
      </Toolbar>
      <Container maxWidth="lg" sx={{ mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Box>
              {/* Tabs for navigation */}
              <Tabs
                value={selectedTab}
                onChange={handleTabChange}
                aria-label="manage inventory and categories"
              >
                <Tab label="CABIN INVENTORY" />
                <Tab label="MANAGE CATEGORIES" />
              </Tabs>
              <Box
                sx={{ display: selectedTab === 0 ? "block" : "none", mt: 2 }}
              >
                { cabins ? (<MuiTable
                  columns={columns}
                  data={cabins}
                  subColumns={subColumns}
                  showCheckBox={false}
                  showTableFilters={true}
                  showSubTableFilters={true}
                  tagOptions={Object.values(TagEnum)}
                  statusOptions={Object.values(CabinStatusReduced)}
                  onApplyTags={manageTags}
                  onApplyState={manageStatus}
                />) : <></>}

                <Dialog
                  open={openDialog}
                  onClose={handleCloseDialog}
                  aria-labelledby="alert-dialog-title"
                  aria-describedby="alert-dialog-description"
                >
                  <DialogTitle id="alert-dialog-title">
                    Non Editable Rows
                  </DialogTitle>
                  <DialogContent>
                    <DialogContentText id="alert-dialog-description">
                      Some cabins cannot be processed due to their current
                      status: <strong>{CabinStatus.BOOKED}</strong> or{" "}
                      <strong>{CabinStatus.PARTIALLY_BOOKED}</strong>. Please
                      check and try again.
                    </DialogContentText>
                  </DialogContent>
                  <DialogActions>
                    <Button
                      onClick={handleCloseDialog}
                      color="primary"
                      autoFocus
                    >
                      Ok
                    </Button>
                  </DialogActions>
                </Dialog>
              </Box>

              <Box
                sx={{ display: selectedTab === 1 ? "block" : "none", mt: 2 }}
              >
                <MuiTable columns={categoriesColumns} data={categories} />
              </Box>
            </Box>
            <LoadingOverlay open={loading} />
            <SnackbarAlert
            open={snackbar.open}
            severity={snackbar.severity}
            message={snackbar.message}
            onClose={handleCloseSnackbar}
          />
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
