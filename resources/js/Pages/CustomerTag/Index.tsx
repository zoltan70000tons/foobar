import React, { useEffect, useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container, Grid, Toolbar, Box, Button, Chip } from '@mui/material';
import { usePermissions } from '@/Providers/PermissionContext';
import 'dayjs/locale/en';
import { Permissions } from '@/enums/PermissionEnum';
import MuiTable from '@/Components/tables/MuiTable';
import { Visibility } from '@mui/icons-material';
import axios from 'axios';
import { CustomerTag } from '@/interfaces/CustomerTag';
import { Customer } from '@/interfaces/Customer';

type FetchCustomerTagsResult = {
  data: CustomerTag[];
  total: number;
};

const Index = ({ auth, tags }: PageProps) => {
  const { hasPermission } = usePermissions();
  const { get } = useForm();

  const columns = useMemo(
    () => [
      {
        header: 'Name',
        accessor: 'name',
        filterable: true,
        sortable: true,
        //width: '26%',
      },
      {
        accessor: 'description',
        header: 'Description',
        filterable: true,
        sortable: true,
        //width: '17%',
      },
      {
        accessor: 'color',
        header: 'Preview',
        filterable: false,
        sortable: false,
        //width: '17%',
        draw: (row: CustomerTag) => (
          <Chip
            label={row.name}
            size="small"
            sx={{
              fontSize: "0.7rem",
              fontWeight: 500,
              backgroundColor: row.color,
              color: "#fff",
            }}
          />
        ),
      },
      {
        header: 'Actions',
        accessor: 'id',
        disableFilter: true,
        //width: '13%',
        draw: (row: CustomerTag) => (
          <div style={{ display: 'flex', gap: '10px' }}>
            {hasPermission(Permissions.ViewCustomerTags) && (
              <Visibility
                onClick={() => {
                  router.get(route('customer-tags.show', { userTag: row.id }));
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
    get(route('customer-tags.create', {}));
  };

  const fetchCustomerTags = async (
    page: number,
    rowsPerPage: number,
    filters: { [key: string]: string },
    sort: { key: string; direction: 'asc' | 'desc' },
  ): Promise<FetchCustomerTagsResult> => {
    try {
      const response = await axios.get('/customer-tags/paginated', {
        params: {
          page,
          per_page: rowsPerPage,
          sort_by: sort.key,
          sort_direction: sort.direction,
          filters: JSON.stringify(filters),
        },
        paramsSerializer: (params) =>
          new URLSearchParams(params as Record<string, string>).toString(),
      });

      return {
        data: response.data?.data ?? [],
        total: response.data?.total ?? 0,
      };
    } catch (error) {
      console.error('Error fetching customer tags:', error);
      return { data: [], total: 0 };
    }
  };

  return (
    <AuthenticatedLayout user={auth.user} header={'Customer Tags'}>
      <Head title="Customer Tags" />
      <Toolbar sx={{ mt: 8 }}>
        <Button variant="outlined" color="secondary" onClick={handleCreate}>
          New Customer Tag
        </Button>
      </Toolbar>
      <Container maxWidth="lg" sx={{ mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Box>
              <Box>
                {tags ? (
                  <MuiTable
                    columns={columns}
                    data={tags}
                    showCheckBox={false}
                  //serverSidePagination={true}
                  //fetchData={fetchCustomerTags}
                  />
                ) : (
                  <></>
                )}
              </Box>
            </Box>
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
