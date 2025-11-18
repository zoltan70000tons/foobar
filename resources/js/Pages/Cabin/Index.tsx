import React, { useState, useMemo, useEffect, useCallback } from 'react';
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
import type { Errors } from '@inertiajs/core';
import { useSnackbar } from '@/Providers/SnackBarAlertProvider';
import AddIcon from '@mui/icons-material/Add';
import { Event } from '@/interfaces/Event';
import { Cabin } from '@/interfaces/Cabin';
import type { PageProps } from '@/types';
import { CabinSubRow } from '@/interfaces/CabinSubRow';
import { StatusTooltip } from '@/Components/StatusToolTip';
import TagToolTip from '@/Components/TagToolTip';
import axios from 'axios';
import { Link, useRemember } from '@inertiajs/react';

type Tag = { id: string; name: string; color: string; description: string; priority: number; };

type Props = PageProps & {
  auth: AuthProps;
  event: Event;
  categories: CabinCategory[];
  cabins: Cabin[];
  errors: Errors;
  tab: string;
  tags: Tag[];
};

const Index = ({ auth, event, categories, cabins, errors, tags }: Props) => {
  const { hasPermission } = usePermissions();

  // TEST
  // const isPermissions = auth.permissions.includes(Permissions.ViewCabinCategories);
  // console.log('isPermissions', isPermissions);

  const [selectedTab, setSelectedTab] = useRemember(0, 'cabins:selectedTab');

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    setSelectedTab(parseInt(params.get('tab') || String(selectedTab), 10));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const [openDialog, setOpenDialog] = useState(false);

  const [loading, setLoading] = useState(true);

  const { showSnackbar } = useSnackbar();

  const { flash } = usePage<PageProps>().props;

  const [tableTick, setTableTick] = useState(0); // State to force table re-render

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

  const handleTabChange = (event: React.SyntheticEvent, newValue: number) => {
    setSelectedTab(newValue);

    const params = new URLSearchParams(window.location.search);
    params.set('tab', String(newValue));
    const newUrl = `${window.location.pathname}?${params.toString()}`;

    router.get(
      newUrl,
      {},
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      }
    );
  };

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
        draw: (row: Cabin) => (
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
        header: 'Filter Status',
        filterable: true,
        sortable: true,
        width: '150px',
        filterType: 'select',
        filterOptions: Object.values(CabinStatus),
        draw: (row: CabinSubRow) => (
          <>
            <StatusTooltip status={row.cabin_status}>
              <Chip
                size="small"
                label={
                  row.cabin_status === "RESERVED"
                    ? "INTERNALLY AVAILABLE"
                    : row.cabin_status === "AVAILABLE"
                      ? "PUBLICLY AVAILABLE"
                      : row.cabin_status
                }
                color={CabinStatusColor[row.cabin_status]}
                sx={{
                  margin: 'auto',
                  fontSize: '0.7rem',
                  fontWeight: '600',
                }}
              />
            </StatusTooltip>
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
        header: 'Filter Tags',
        filterable: true,
        filterType: 'select',
        filterOptions: tags ? tags.map((tag: any) => tag.name) : [],
        filterFunction: (cellValue: any, filterValue: string) => {
          const norm = (v: unknown) => String(v ?? "").toLowerCase().trim();
          const names = Array.isArray(cellValue) ? cellValue.map(t => norm(t?.name)) : [];
          return names.includes(norm(filterValue));
        },
        draw: (subRow: CabinSubRow) => {
          return (
            <Box sx={{ display: 'inline-flex', gap: 0.5 }}>
              {Array.isArray(subRow.cabin_tags) && subRow.cabin_tags.length > 0 ? (
                subRow.cabin_tags.map((tag: Tag) => (
                  <TagToolTip description={tag.description} title={tag.name} key={tag.id} label={tag.name}>
                    <Chip
                      key={tag.id}
                      label={tag.name}
                      size="small"
                      style={{ backgroundColor: tag.color, color: '#fff' }}
                      sx={{ margin: 'auto', fontSize: '0.7rem', fontWeight: '400' }}
                    />
                  </TagToolTip>
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
        draw: (row: Cabin) => (
          <div style={{ display: 'flex', gap: '10px' }}>
            {auth.permissions.includes(Permissions.ViewCabins) && (
              <Link href={route('cabins.edit', { id: event.id, cabin_id: row.id })}>
                <Visibility style={{ cursor: 'pointer', fill: 'white' }} />
              </Link>

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
        draw: (row: CabinCategory) => (
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

  const manageTags = async (rows: Cabin[], tags: string[]) => {
    const url = apiRoutes.addCabinTags(event.id);
    setLoading(true);
    try {
      router.post(route('cabins.addTag', { id: event.id }), { tags, rows }, {
        onSuccess: () => {
          setTableTick(t => t + 1); 
          showSnackbar('Tags edited successfully', 'success');
        },
        onError: () => showSnackbar('Error updating Tags', 'error'),
        onFinish: () => setLoading(false),
        preserveScroll: true,
      });

    } catch (error) {
      console.error('Error adding tags:', error);
      showSnackbar('Error updating tags', 'error');
    } finally {
      setLoading(false);
    }
  };


  const manageStatus = (rows: Cabin, status: string) => {
    const hasInvalidStatus = rows.some(
      (row: Cabin) => row.status === CabinStatus.BOOKED || row.status === CabinStatus.PARTIALLY_BOOKED,
    );
    if (hasInvalidStatus) {
      setOpenDialog(true);
      return;
    }
    const url = apiRoutes.updateCabinStatus(event.id);
    const rowIds = rows.map((row: Cabin) => row.id);
    setLoading(true);
    router.post(route('cabins.updateStatus', { id: event.id }),
      { status, rows: rowIds },
      {
        onSuccess: () => {
          setTableTick(t => t + 1); 
          showSnackbar('Status edited successfully', 'success');
        },
        onError: () => showSnackbar('Error updating status', 'error'),
        onFinish: () => setLoading(false),
        preserveScroll: true,
      }
    );
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

  type Filters = Record<string, string | number | boolean>;
  const fetchData = useCallback(
    async (
      page: number,
      rowsPerPage: number,
      filters: Filters,
      sort: { key?: string; direction?: string } | undefined,
      dateRangeState: null
    ) => {
      try {

        const res = await axios.get(route("cabins.getData", { id: event.id }), {
          params: {
            page: page + 1,
            per_page: rowsPerPage,
            sort_key: sort?.key ?? "created_at",
            sort_direction: sort?.direction ?? "asc",
            // keyword: searchTerm,
            //tab: selectedTab ?? 0,
            //tags: tags,
            //user_ids: selectedUsers.map((user) => user.id),
            //date_range: dateRangeState,
            ...filters,
          },
        });
        return res.data.data;
      } catch (err) {
        throw err;
      }
    },
    [event.id]
  );


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
                      serverSidePagination={true}
                      fetchData={fetchData}
                      rememberKey={`cabins:${event.id}:inventory`}
                      key={tableTick}
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
