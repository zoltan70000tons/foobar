import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { router } from '@inertiajs/react';
import {
  Container,
  Paper,
  Grid,
  Toolbar,
  TextField,
  Box,
  Button, Chip,
} from '@mui/material';
import { usePermissions } from '@/Providers/PermissionContext';
import SnackbarAlert from '@/Components/SnackbarAlert';
import { Permissions } from '@/enums/PermissionEnum';
import { ColorPicker, useColor } from "react-color-palette";

type Tag = {
  name: string;
  description: string;
  color: string;
  type: string;
}

type PageProps = {
  tag: Tag;
};

const Edit = ({ auth, errors }: PageProps) => {
  const { tag }: PageProps = usePage().props;
  const [snackbar, setSnackbar] = useState({ open: false, severity: 'success', message: '' });
  const { hasPermission } = usePermissions();

  const { data, setData, head, processing } = useForm({
    name: tag.name || '',
    description: tag.description || '',
    color: tag.color || '',
  });

  const [color, setColor] = useColor(data.color);

  const handleChange = <TForm extends Record<string, any>>(
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>,
  ) => {
    const { name, value } = e.target;

    setData(name as keyof TForm, value as TForm[keyof TForm]);
  };

  const handleBack = () => {
    router.visit(route("customer-tags.show", tag.id), {
      only: ['tag'],
    })
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    const formData = new FormData();
    for (const key in data) {
      formData.append(key, data[key]);
    }
    formData.append('_method', 'PUT');
    formData.append('color', color.hex);

    router.post(`/tags/${tag.id}`, formData, {
      forceFormData: true,
      onSuccess: (response) => {
        setSnackbar({ open: true, severity: 'success', message: 'tag edited successfully' });
      },
      onError: (errors) => {
        const errorMessages = Object.values(errors).join('\n');
        setSnackbar({
          open: true,
          severity: 'error',
          message: `Error editing tag\n${errorMessages}`,
        });
      },
      onFinish: () => {},
    });
  };

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  return (
    <AuthenticatedLayout user={auth.user} header={'Tags'}>
      <Head title="Edit Tag" />
      <Toolbar sx={{ mt: 8, mb: 4 }}>
        <Button variant="outlined" color="secondary" onClick={handleBack}>
          Back
        </Button>
      </Toolbar>
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          {hasPermission(Permissions.EditCustomers) && (
            <Paper
              sx={{
                p: 2,
                display: 'flex',
                flexDirection: 'column',
                minHeight: 240,
                width: '100%',
              }}
            >
              <h1>Edit Customer Tag</h1>
              <form onSubmit={handleSubmit} encType="multipart/form-data">
                <Box sx={{ width: '100%' }}>
                  <Grid container spacing={2}>
                    <Grid item xs={6}>
                      <TextField
                        fullWidth
                        label="Name"
                        variant="outlined"
                        value={data.name}
                        name={'name'}
                        onChange={handleChange}
                      />
                    </Grid>
                    <Grid item xs={6}>
                      <TextField
                        fullWidth
                        label="Description"
                        variant="outlined"
                        value={data.description}
                        name={'description'}
                        onChange={handleChange}
                      />
                    </Grid>
                    <Grid item xs={6}>
                      <TextField
                        fullWidth
                        label="Color"
                        variant="outlined"
                        value={color.hex}
                        name={'color'}
                        onChange={handleChange}
                        disabled
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <ColorPicker width={456} height={228}
                                   color={color} onChange={setColor} hideHSV dark />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <div style={{display: 'flex', alignItems: 'center', gap: '16px'}}>
                        <span>Preview:</span>
                        <Chip
                          label={data.name}
                          size="small"
                          sx={{
                            fontSize: "0.7rem",
                            fontWeight: 500,
                            backgroundColor: color.hex,
                            color: "#fff",
                          }}
                        />
                      </div>
                    </Grid>
                  </Grid>
                </Box>
                <Box sx={{ mt: 4 }}>
                  <Button variant="contained" color="primary" fullWidth type="submit" disabled={processing}>
                    {processing ? 'Submitting...' : 'Submit'}
                  </Button>
                </Box>
              </form>
            </Paper>
          )}

          <SnackbarAlert
            open={snackbar.open}
            severity={snackbar.severity}
            message={snackbar.message}
            onClose={handleCloseSnackbar}
            horizontal={'center'}
            vertical={'top'}
          />
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Edit;
