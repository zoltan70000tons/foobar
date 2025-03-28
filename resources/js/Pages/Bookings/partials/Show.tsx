import React, { useEffect, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import {
  Avatar,
  Box,
  Container,
  Grid,
  Typography,
  Toolbar,
  useTheme,
  Alert,
  Select,
  MenuItem,
  Button,
  Divider,
  FormGroup,
  FormControlLabel,
  Switch,
  AlertTitle,
  Drawer,
} from '@mui/material';
import CommentIcon from '@mui/icons-material/Comment';
import { usePermissions } from '@/Providers/PermissionContext';
import dayjs from 'dayjs';
import 'dayjs/locale/en';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import Status from './Status';
import Detail from './Details';
import Passengers from './Passengers';
import Payment from './Payment';
import BookingSidebar from './BookingSidebar';
import { useSnackbar } from '@/Providers/SnackBarAlertProvider';
import AdjustmentForm from './AdjustmentForm';
import LoadingOverlay from '@/Components/LoadingOverlay';

const Show = ({ auth, event, booking, users, cabinTypes, cabinCategories, adjustments }: PageProps) => {
  const [editMode, setEditMode] = useState(false);
  const [locked, setLocked] = useState(booking.locked_by ? true : false);
  const [isSidebarOpen, setSidebarOpen] = useState(false);
  const { hasPermission } = usePermissions();
  const [comments, setComments] = useState(booking.comments || []);
  const [loading, setLoading] = useState(false);
  const [logs, setLogs] = useState(booking.logs || []);
  const theme = useTheme();
  dayjs.extend(localizedFormat);
  const { showSnackbar } = useSnackbar();
  const capacity = booking.cabin.category.capacity;

  const { flash } = usePage().props;

  useEffect(() => {
    if (booking.locked_by && booking.locked_by.agent_id === auth.user.id) {
      setEditMode(true);
    }
  }, []);
  const handleEditChange = (e) => {
    setLoading(true);
    router.get(
      route('bookings.editMode'),
      {
        booking_id: booking.id,
        lock: e.target.checked ? '1' : '0',
        event_id: event.id,
      },
      {
        onSuccess: (response) => {
          if (response.success) {
            setEditMode(e.target.checked);
            setLoading(false);
          }
        },
        onError: (error) => {
          setLoading(false);
        },
      },
    );
  };

  const toggleSidebar = () => setSidebarOpen(!isSidebarOpen);

  useEffect(() => {
    if (flash.error) {
      showSnackbar(flash.error, 'error');
    }
    if (flash.success) {
      showSnackbar(flash.success, 'success');
    }
  }, [flash]);

  const handleAddComment = (comment: string) => {
    router.post(
      route('bookings.addComment', {
        id: event.id,
      }),
      {
        comment: comment,
        booking_id: booking.id,
      },
      {
        onSuccess: (page) => {
          const newComment = page.props.booking.comments.slice(-1)[0];
          setComments((prevComments) => [...prevComments, newComment]);
          showSnackbar('Comment added successfully!', 'success');
        },
        onError: (errors) => {
          showSnackbar('Error adding comment:', 'error');
          console.error('Error adding comment:', errors);
        },
        preserveScroll: true,
        preserveState: true,
      },
    );
  };

  const handleAddAdjustment = (data) => {
    router.post(
      route('bookings.addAdjustment', {
        id: event.id,
      }),
      {
        code: data.code,
        type: data.type,
        operation: data.operation,
        value: data.value,
        restrictions: null,
        event_id: event.id,
        booking_id: booking.id,
      },
    );
  };

  const handleBack = () => {
    setLoading(true);

    router.visit(route('bookings.index', { id: event.id }), {
      replace: true,
      preserveScroll: true,
      preserveState: false,
    });
  };

  return (
    <AuthenticatedLayout user={auth.user} header={'Booking Detail'}>
      <Head title="Booking " />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Box display="flex" justifyContent="space-between" alignItems="center">
          <Grid item xs={6}>
            <FormGroup>
              {booking.status === 'CANCELLED' ? (
                <Alert severity="error" sx={{ mb: 2 }}>
                  <AlertTitle>Info</AlertTitle>
                  This booking has been cancelled and cannot be edited.
                </Alert>
              ) : (
                <FormControlLabel
                  control={
                    <Switch
                      checked={editMode || (booking.locked_by && booking.locked_by.agent_id === auth.user.id)}
                      onChange={handleEditChange}
                      disabled={booking.locked_by && booking.locked_by.agent_id !== auth.user.id}
                    />
                  }
                  label="Edit Mode"
                />
              )}
            </FormGroup>
          </Grid>

          <Grid item xs={6} sx={{ textAlign: 'right' }}>
            <Button variant="outlined" color="secondary" onClick={handleBack} sx={{ mb: 2, mr: 2 }}>
              Back
            </Button>
            <Button
              variant="outlined"
              color="secondary"
              startIcon={<CommentIcon />}
              onClick={toggleSidebar}
              sx={{ mb: 2 }}
            >
              View Comments & Logs
            </Button>
          </Grid>
        </Box>

        {booking.locked_by && booking.status !== 'CANCELLED' && (
          <Alert severity="warning" sx={{ mb: 2 }}>
            <AlertTitle>Warning</AlertTitle>
            {booking.locked_by.agent_id === auth.user.id
              ? 'Once you finish editing, remember to exit edit mode.'
              : 'This booking request is currently being edited by another agent, so all editable fields have been disabled.'}
          </Alert>
        )}

        <Status event={event} editMode={editMode} booking={booking} users={users} />
        <Detail
          event={event}
          booking={booking}
          editMode={editMode}
          cabinTypes={cabinTypes}
          cabinCategories={cabinCategories}
        />
        <Passengers booking={booking} editMode={editMode} setLoading={setLoading} />
        <AdjustmentForm booking={booking} editMode={editMode} onSubmit={handleAddAdjustment} list={adjustments} />
        <Payment booking={booking} editMode={editMode} />
        {/* <Payment booking={booking} passenger={null} number={2} count={capacity} editMode={editMode} /> */}
        {/* <ActionList editMode={editMode} /> */}
        <BookingSidebar
          isOpen={isSidebarOpen}
          toggleSidebar={toggleSidebar}
          logs={booking.logs}
          comments={comments}
          onAddComment={handleAddComment}
        />
        {/* <LoadingOverlay open={loading} /> */}
      </Container>
    </AuthenticatedLayout>
  );
};

export default Show;
