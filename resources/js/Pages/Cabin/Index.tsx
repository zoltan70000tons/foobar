import React, { useState, useMemo, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import {
  Box,
  Container,
  Grid,
  IconButton,
  Toolbar,
  Typography,
  Tabs,
  Tab,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
  Button,
} from '@mui/material';
import MuiTable from '@/Components/tables/MuiTable';
import { CabinStatus, CabinStatusColor, CabinStatusReduced } from '@/enums/CabinStatus';
import { TagEnum } from '@/enums/TagEnum';
import { CabinCategory } from '@/interfaces/CabinCategory';
import { Visibility, Edit, Delete } from '@mui/icons-material';
import { Permissions } from '@/enums/PermissionEnum';
import { usePermissions } from '@/Providers/PermissionContext';
import apiRoutes from '@/Helpers/ApiRoutes';
import type { PageProps } from '@inertiajs/core';
import { useSnackbar } from '@/Providers/SnackBarAlertProvider';
import AddIcon from '@mui/icons-material/Add';

type Props = PageProps & {
  auth: any;
  event: any;
  categories: CabinCategory[];
  cabins: any[];
  errors: any;
  tab: string;
  data: any;
};

const Index = ({ auth, event, categories, cabins, errors, tags }: Props) => {
  const { hasPermission } = usePermissions();

  // TEST
  // const isPermissions = auth.permissions.includes(Permissions.ViewCabinCategories);
  // console.log('isPermissions', isPermissions);

  const [selectedTab, setSelectedTab] = useState(0);

  const [openDialog, setOpenDialog] = useState(false);

  const [loading, setLoading] = useState(true);

  const { showSnackbar } = useSnackbar();

  const { flash } = usePage().props as any;

  useEffect(() => {
    if (flash.message) {
      if (flash.success) {
        showSnackbar(flash.message, 'success');
      } else {
        showSnackbar(flash.message, 'error');
      }
    }
  }, [flash])

  useEffect(() => {
    if (cabins) {
      setLoading(false);
    }
  }, [cabins]);

  const handleTabChange = (event: React.ChangeEvent<{}>, newValue: number) => {
    setSelectedTab(newValue);
  };

  console.log('Cabin Index Page', event, categories, cabins);

  const columns = useMemo(
    () => [
      {
        header: 'Category Code',
        accessor: 'category_code',
        filterable: true,
        sortable: true,
      },
      {
        header: 'Name',
        accessor: 'title',
        filterable: true,
        sortable: true,
      },
      {
        header: 'Price',
        accessor: 'price',
        filterable: false,
        sortable: true,
      },
      {
        header: 'Availability',
        accessor: 'availability',
        sortable: true,
        filterable: false,
      },
    ],
    [],
  );

  const subColumns = useMemo(
    () => [
      {
        accessor: 'cabin_number',
        header: 'Number',
        filterable: true,
        sortable: true,
        draw: (row: any) => (
          <Typography variant="body2" sx={{ fontWeight: '500' }}>
            {row.cabin_number}
            {row && row.is_shared_cabin_number && (
              <Chip
                label="Shared"
                size="small"
                color="warning"
                sx={{
                  ml: 1,
                  bgcolor: '#ff9800',
                  color: '#fff',
                  fontSize: '0.75em',
                  height: '20px',
                }}
              />
            )}
          </Typography>
        ),
      },
      {
        accessor: 'cabin_type',
        header: 'Type',
        sortable: true,
        filterable: true,
      },
      {
        accessor: 'deck',
        header: 'Deck',
        sortable: true,
        filterable: true,
      },
      {
        accessor: 'cabin_status',
        header: 'Status',
        filterable: true,
        sortable: true,
        width: '150px',
        filterType: 'select',
        filterOptions: Object.values(CabinStatus),
        draw: (row: any) => (
          <>
            <Chip
              size="small"
              label={row.cabin_status === "RESERVED" ? "EXCLUDED" : row.cabin_status}
              color={CabinStatusColor[row.cabin_status]}
              sx={{
                margin: 'auto',
                fontSize: '0.7rem',
                fontWeight: '400',
                color: 'white',
              }}
            />
            {row.is_reserved && (
              <Typography
                variant="body2"
                color="warning.main"
                sx={{
                  mt: 0.5,
                  fontSize: '0.7rem',
                  fontWeight: '500',
                }}
              >
                Booking in Progress
              </Typography>
            )}
          </>
        ),
      },
      {
        accessor: 'cabin_tags',
        header: 'Tags',
        filterable: true,
        filterType: 'select',
        filterOptions: tags ? tags.map((tag: any) => tag.name) : [],
        filterFunction: (cellValue: any, filterValue: string) => {
          const norm = (v: unknown) => String(v ?? "").toLowerCase().trim();
          const names = Array.isArray(cellValue) ? cellValue.map(t => norm(t?.name)) : [];
          return names.includes(norm(filterValue));
        },
        draw: (subRow: any) => {
          return (
            <Box sx={{ display: 'inline-flex', gap: 0.5 }}>
              {Array.isArray(subRow.cabin_tags) && subRow.cabin_tags.length > 0 ? (
                subRow.cabin_tags.map((tag: string) => (
                  <Chip
                    key={tag.id}
                    label={tag.name}
                    size="small"
                    sx={{ margin: 'auto', fontSize: '0.7rem', fontWeight: '400' }}
                  />
                ))
              ) : (
                <em>No Tags</em>
              )}
            </Box>
          );
        }
      },
      {
        header: 'Actions',
        accessor: 'category_code',
        disableFilter: true,
        draw: (row: any) => (
          <div style={{ display: 'flex', gap: '10px' }}>
            {auth.permissions.includes(Permissions.ViewCabins) && (
              <Visibility
                onClick={() => {
                  router.get(route('cabins.edit', { id: event.id, cabin_id: row.id }));
                }}
                style={{ cursor: 'pointer' }}
              />
            )}
          </div>
        ),
      },
    ],
    [],
  );

  // cabin categories
  const handleEditClick = (row: CabinCategory) => {
    router.get(route('cabinCategory.edit', { id: row.event_id, catId: row.id }));
  };
  const handleViewClick = (row: CabinCategory) => {
    router.get(route('cabinCategory.show', { id: row.event_id, catId: row.id }));
  };
  const handleDeleteClick = (row: CabinCategory) => {
    router.delete(route('cabinCategory.destroy', { id: row.event_id, catId: row.id }));
  };


  const categoriesColumns = useMemo(
    () => [
      {
        header: 'Id',
        accessor: 'id',
      },
      {
        header: 'Category Type',
        accessor: 'category_type',
      },
      {
        header: 'Category Code',
        accessor: 'category_code',
      },
      {
        header: 'Category Name',
        accessor: 'category_name',
      },
      {
        header: 'Capacity',
        accessor: 'capacity',
      },
      {
        header: 'Price',
        accessor: 'price',
      },
      {
        header: 'Display Order',
        accessor: 'display_order',
      },
      {
        header: 'Cruise ID',
        accessor: 'cruise_id',
      },
      {
        header: 'Event ID',
        accessor: 'event_id',
      },

      {
        header: 'Actions',
        accessor: '',
        disableFilter: true,
        draw: (row: any) => (
          <div style={{ display: 'flex', gap: '10px' }}>
            {auth.permissions.includes(Permissions.ViewCabinCategories) && (
              <Visibility onClick={() => handleViewClick(row)} style={{ cursor: 'pointer' }} />
            )}
            {auth.permissions.includes(Permissions.EditCabinCategories) && (
              <Edit onClick={() => handleEditClick(row)} style={{ cursor: 'pointer' }} />
            )}
            {auth.permissions.includes(Permissions.DeleteCabinCategories) && (
              <Delete onClick={() => handleDeleteClick(row)} style={{ cursor: 'pointer' }} />
            )}
          </div>
        ),
      },
    ],
    [],
  );

  const manageTags = async (rows: any[], tags: string[]) => {
    setLoading(true);
    try {
      router.post(route('cabins.addTag', { id: event.id }), { tags, rows });
      //router.reload({ only: ['cabins'], preserveScroll: true });
      showSnackbar('Tags edited successfully', 'success');
    } catch (error) {
      console.error('Error adding tags:', error);
      showSnackbar('Error updating tags', 'error');
    } finally {
      setLoading(false);
    }
  };


  const manageStatus = (rows: any, status: string) => {
    const hasInvalidStatus = rows.some(
      (row: any) => row.status === CabinStatus.BOOKED || row.status === CabinStatus.PARTIALLY_BOOKED,
    );
    if (hasInvalidStatus) {
      setOpenDialog(true);
      return;
    }
    const url = apiRoutes.updateCabinStatus(event.id);
    const rowIds = rows.map((row: any) => row.id);
    setLoading(true);
    router.post(route('cabins.updateStatus', { id: event.id }), { status, rows: rowIds });
    // axios
    //   .post(url, { status: status, rows: rowIds })
    //   .then((response) => {
    //     setLoading(false);
    //     router.reload({ only: ['cabins'], preserveScroll: true });
    //     showSnackbar('Status edited successfully', 'success');
    //   })
    //   .catch((error) => {
    //     showSnackbar('Error updating status', 'error');
    //   });
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
  };

  return (
    <AuthenticatedLayout user={auth.user} header={'Cabins'}>
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
              <Tabs value={selectedTab} onChange={handleTabChange} aria-label="manage inventory and categories">
                <Tab label="CABIN INVENTORY" />
                <Tab label="MANAGE CATEGORIES" />
              </Tabs>
              <Box sx={{ display: selectedTab === 0 ? 'block' : 'none', mt: 2 }}>
                {cabins ? (
                  <div>
                    {auth.permissions.includes(Permissions.ViewCabinCategories) && (<Button
                      variant="outlined"
                      color="primary"
                      startIcon={<AddIcon />}
                      sx={{ mb: 2, ml: 'auto' }}
                      onClick={() => {
                        router.get(route('cabins.create', { id: event.id }));
                      }}
                    >
                      Create Cabin
                    </Button>)}
                    <MuiTable
                      columns={columns}
                      data={cabins}
                      subColumns={subColumns}
                      showCheckBox={false}
                      showTableFilters={true}
                      showSubTableFilters={true}
                      tagOptions={tags ? tags.map((tag: any) => tag.name) : []}
                      statusOptions={Object.values(CabinStatusReduced)}
                      onApplyTags={manageTags}
                      onApplyState={manageStatus}
                    />
                  </div>
                ) : (
                  <></>
                )}

                <Dialog
                  open={openDialog}
                  onClose={handleCloseDialog}
                  aria-labelledby="alert-dialog-title"
                  aria-describedby="alert-dialog-description"
                >
                  <DialogTitle id="alert-dialog-title">Non Editable Rows</DialogTitle>
                  <DialogContent>
                    <DialogContentText id="alert-dialog-description">
                      Some cabins cannot be processed due to their current status: <strong>{CabinStatus.BOOKED}</strong>{' '}
                      or <strong>{CabinStatus.PARTIALLY_BOOKED}</strong>. Please check and try again.
                    </DialogContentText>
                  </DialogContent>
                  <DialogActions>
                    <Button onClick={handleCloseDialog} color="primary" autoFocus>
                      Ok
                    </Button>
                  </DialogActions>
                </Dialog>
              </Box>

              <Box sx={{ display: selectedTab === 1 ? 'block' : 'none', mt: 2 }}>
                <MuiTable columns={categoriesColumns} data={categories} />
              </Box>
            </Box>
            {/* <LoadingOverlay open={loading} /> */}
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
