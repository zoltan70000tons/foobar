import React from 'react';
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
  Button,
  Chip,
  Rating,
  Typography,
} from '@mui/material';
import { usePermissions } from '@/Providers/PermissionContext';
import { Permissions } from '@/enums/PermissionEnum';
import { ColorPicker, useColor } from "react-color-palette";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";

type Tag = {
  name: string;
  description: string;
  color: string;
  type: string;
  priority: number;
}

type PageProps = {
  tag: Tag;
};

const Edit = ({ auth, errors }: PageProps) => {
  const { tag }: PageProps = usePage().props;
  const { hasPermission } = usePermissions();
  const { showSnackbar } = useSnackbar();

  const { data, setData, head, processing } = useForm({
    name: tag.name || '',
    description: tag.description || '',
    color: tag.color || '',
    priority: tag.priority || 0,
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
        showSnackbar('Tag edited successfully', 'success');
      },
      onError: (errors) => {
        const errorMessages = Object.values(errors).join('\n');
        showSnackbar(`Error editing tag\n${errorMessages}`, 'error');
      },
      onFinish: () => {},
    });
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
          {hasPermission(Permissions.EditTags) && (
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
                      <Typography component="legend">Priority</Typography>
                      <Rating
                        name="priority"
                        value={Number(data.priority)}
                        onChange={(event, newValue) => {
                          const clampedValue = Math.max(0, Math.min(10, newValue ?? 0));
                          handleChange({
                            target: {
                              name: 'priority',
                              value: clampedValue,
                            },
                          });
                        }}
                        max={10}
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
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Edit;
