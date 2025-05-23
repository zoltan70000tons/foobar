import React, { useEffect, useMemo, useState } from 'react';
import { router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container, Grid, Box } from '@mui/material';
import { usePermissions } from '@/Providers/PermissionContext';
import 'dayjs/locale/en';
import { Permissions } from '@/enums/PermissionEnum';
import MuiTable from '@/Components/tables/MuiTable';
import LoadingOverlay from '@/Components/LoadingOverlay';
import { Visibility } from '@mui/icons-material';

const Index = ({ auth, bookings }: PageProps) => {
  const { hasPermission } = usePermissions();

  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (bookings) {
      setLoading(false);
    }
  }, [bookings]);

  const columns = useMemo(
    () => [
      {
        header: 'Event Name',
        accessor: 'event_name',
        filterable: true,
        sortable: true,
      },
      {
        accessor: 'booking_code',
        header: 'Booking Code',
        filterable: true,
        sortable: true,
      },
      {
        accessor: 'cabin_type',
        header: 'Cabin Type',
        filterable: true,
        sortable: true,
      },
      {
        accessor: 'category_full_title',
        header: 'Category Full Title (Custom attribute in Category Model)',
        filterable: true,
        sortable: true,
      },
      {
        header: 'Actions',
        accessor: 'id',
        disableFilter: true,
        draw: (row) => (
          <div style={{ display: 'flex', gap: '10px' }}>
            {hasPermission(Permissions.ViewBookings) && (
              <Visibility
                onClick={() => {
                  router.get(route('bookings.show', { id: row.event_id, booking_code: row.booking_code }));
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

  return (
    <Container maxWidth="lg" sx={{ mb: 4 }}>
      <Grid container spacing={3}>
        <Grid item xs={12}>
          <Box>
            <Box>{bookings ? <MuiTable columns={columns} data={bookings} /> : <></>}</Box>
          </Box>
          {/* <LoadingOverlay open={ loading }/> */}
        </Grid>
      </Grid>
    </Container>
  );
};

export default Index;
