import React, { useEffect, useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container, Grid, Toolbar, Box, Button, Chip, Autocomplete, TextField } from '@mui/material';
import { usePermissions } from '@/Providers/PermissionContext';
import 'dayjs/locale/en';
import { Permissions } from '@/enums/PermissionEnum';
import MuiTable from '@/Components/tables/MuiTable';
// import LoadingOverlay from '@/Components/LoadingOverlay';
import { Visibility } from '@mui/icons-material';
import axios from 'axios';
import NewBookingModal from "@/Pages/Bookings/NewBookingModal";
import { TagEnum, TagEnumStyles } from "@/enums/TagEnum";

const Index = ({ auth, customers, userTags }: PageProps) => {
  const { hasPermission } = usePermissions();
  const { get } = useForm();
  const [selectedTags, setSelectedTags] = useState<string[]>([]);

  const [loading, setLoading] = useState(true);
  const userTagAutocomleteOptions = userTags.map(item => ({
    ...item,
    label: item.name,
  }));

  useEffect(() => {
    if (customers) {
      setLoading(false);
    }
  }, [customers]);

  const columns = useMemo(
    () => [
      {
        header: 'eMail',
        accessor: 'email',
        filterable: true,
        sortable: true,
        width: '26%',
      },
      {
        accessor: 'first_name',
        header: 'First Name',
        filterable: true,
        sortable: true,
        width: '17%',
      },
      {
        accessor: 'last_name',
        header: 'Last Name',
        filterable: true,
        sortable: true,
        width: '17%',
      },
      {
        accessor: 'dob',
        header: 'Date of Birth',
        filterable: true,
        sortable: true,
        width: '17%',
      },
      {
        accessor: 'survivor_number',
        header: 'Survivor Number',
        filterable: true,
        sortable: true,
        width: '17%',
      },
      {
        header: "Tags",
        accessor: "Tags",
        draw: (row: any) => (
          <Box sx={{ display: "flex", flexFlow: "column wrap", alignItems: "flex-start", gap: 0.5 }}>
            {Array.isArray(row.tags) && row.tags.length > 0 ? (
              row.tags.map((tag: {label: string; color: string}, index: number) => {
                return (
                  <Chip
                    key={tag.label}
                    label={tag.label}
                    size="small"
                    sx={{
                      fontSize: "0.7rem",
                      fontWeight: 500,
                      backgroundColor: tag.color,
                      color: "#fff",
                    }}
                  />
                );
              })
            ) : (
              <em>No Tags</em>
            )}
          </Box>
        ),
      },
      {
        accessor: 'membership_type',
        header: 'Membership',
        filterable: true,
        sortable: true,
        width: '13%',
      },
      {
        header: 'Actions',
        accessor: 'id',
        disableFilter: true,
        width: '13%',
        draw: (row) => (
          <div style={{ display: 'flex', gap: '10px' }}>
            {hasPermission(Permissions.ViewCustomers) && (
              <Visibility
                onClick={() => {
                  router.get(route('customers.show', { customer: row.id }));
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

  const handleCreate = () => {
    get(route('customers.create', {}));
  };

  const handleViewTags = () => {
    get(route('customer-tags.index', {}));
  }

  const fetchCustomers = async (
    page: number,
    rowsPerPage: number,
    filters: { [key: string]: string },
    sort: { key: string; direction: 'asc' | 'desc' },
  ): Promise<{ data: any[]; total: number }> => {
    console.log({selectedTags})
    try {
      const response = await axios.get('/customers/paginated', {
        params: {
          page,
          per_page: rowsPerPage,
          sort_by: sort.key,
          sort_direction: sort.direction,
          filters: JSON.stringify(filters),
          tags: JSON.stringify(selectedTags),
        },
        paramsSerializer: (params) => {
          return new URLSearchParams(params as any).toString();
        },
      });

      return {
        data: response.data?.data ?? [],
        total: response.data?.total ?? 0,
      };
    } catch (error) {
      console.error('Error fetching customers:', error);
      return { data: [], total: 0 };
    }
  };

  return (
    <AuthenticatedLayout user={auth.user} header={'Customers'}>
      <Head title="Customers" />
      <Toolbar sx={{ mt: 8 }}>
        <Button variant="outlined" color="secondary" onClick={handleCreate} sx={{ mr: 2 }}>
          New Customer
        </Button>
        <Button variant="outlined" color="primary" onClick={handleViewTags}>
          Customer Tags
        </Button>
      </Toolbar>
      <Container maxWidth="lg" sx={{ mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Grid item xs={12}>
              <Box
                sx={{
                  display: "flex",
                  justifyContent: "flex-end",
                  alignItems: "stretch",
                  gap: 2,
                }}
              >
                <Autocomplete
                  multiple
                  size="small"
                  options={userTagAutocomleteOptions}
                  getOptionLabel={(option) => option}
                  value={selectedTags}
                  onChange={(event, newValue) => setSelectedTags(newValue)}
                  renderTags={(value: string[], getTagProps) =>
                    value.map((option: {name: string; color: string}, index) => {
                      return (
                        <Chip
                          variant="outlined"
                          label={option.name}
                          {...getTagProps({ index })}
                          sx={{
                            backgroundColor: option.color,
                            color: "#fff",
                            fontWeight: 500,
                            fontSize: "0.75rem",
                          }}
                        />
                      );
                    })
                  }
                  renderOption={(props, option) => {
                    return (
                      <Box component="li" {...props}>
                        <Chip
                          label={option.label}
                          size="small"
                          sx={{
                            backgroundColor: option.color,
                            color: "#fff",
                            fontWeight: 500,
                            mr: 1,
                          }}
                        />
                      </Box>
                    );
                  }}
                  renderInput={(params) => <TextField {...params} variant="outlined" placeholder="Filter by Tags" />}
                  sx={{ minWidth: 250 }}
                />
              </Box>
            </Grid>
            <Box>
              <Box>
                {customers ? (
                  <MuiTable
                    columns={columns}
                    data={customers}
                    showCheckBox={false}
                    serverSidePagination={true}
                    fetchData={fetchCustomers}
                  />
                ) : (
                  <></>
                )}
              </Box>
            </Box>
            {/* <LoadingOverlay open={ loading }/> */}
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
