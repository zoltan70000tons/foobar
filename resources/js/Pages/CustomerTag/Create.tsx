import React, { useState } from "react";
import { Head, useForm } from "@inertiajs/react";
import { PageProps } from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { router } from "@inertiajs/react";
import {
  Container,
  Paper,
  Grid,
  Toolbar,
  TextField,
  Box,
  Button, Chip,
} from "@mui/material";
import SnackbarAlert from "@/Components/SnackbarAlert";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import { ColorPicker, useColor } from "react-color-palette";
import "react-color-palette/dist/css/rcp.css";

const Create = ({ auth, errors }: PageProps) => {
  const { hasPermission } = usePermissions();
  const { data, setData, post, processing } = useForm({
    name: "",
    description: "",
    //color: "",
  });
  const [color, setColor] = useColor("#121212");

  const [snackbar, setSnackbar] = useState({
    open: false,
    severity: "success",
    message: "",
  });

  const handleChange = <TForm extends Record<string, any>>(
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;

    setData(name as keyof TForm, value as TForm[keyof TForm]);
  };

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false, message: "" });
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    setSnackbar({ ...snackbar, message: "" });

    const formData = new FormData();
    for (const key in data) {
      formData.append(key, data[key]);
    }
    formData.append('color', color.hex);

    router.post(route("customer-tags.store"), formData, {
      forceFormData: true,
      onSuccess: (response) => {
        setSnackbar({
          open: true,
          severity: "success",
          message: "Customer tag created successfully",
        });
      },
      onError: (errors) => {
        const errorMessages = Object.values(errors).join("\n");
        setSnackbar({
          open: true,
          severity: "error",
          message: `Error creating customer tag\n${ errorMessages }`,
        });
      },
    });
  };

  const handleBack = () => {
    router.visit(route("customer-tags.index"), {
      only: ['userTags'],
    })
  }

  return (
    <AuthenticatedLayout user={ auth.user } header={ "Customer Tags" }>
      <Head title="Create Customer Tag"/>
      <Toolbar sx={ { mt: 8 } }>
        <Button variant="outlined" color="secondary" onClick={ handleBack }>
          Back
        </Button>
      </Toolbar>
      <Container maxWidth="lg" sx={ { mt: 4, mb: 4 } }>
        <Grid container spacing={ 3 }>
          { hasPermission(Permissions.CreateCustomers) && (<Paper
              sx={ {
                p: 2,
                display: "flex",
                flexDirection: "column",
                minHeight: 240,
                width: "100%",
              } }
            >
              <h1>Create Customer Tag</h1>
              <form onSubmit={ handleSubmit } encType="multipart/form-data">
                <Box sx={ { width: "100%" } }>
                  <Grid container spacing={ 2 }>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Name"
                        variant="outlined"
                        value={ data.name }
                        name={ "name" }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Description"
                        variant="outlined"
                        value={ data.description }
                        name={ "description" }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Color"
                        variant="outlined"
                        value={ color.hex }
                        name={ "color" }
                        disabled
                        onChange={ handleChange }
                      />
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
                    <Grid item xs={ 6 }>
                      <ColorPicker width={456} height={228}
                                   color={color} onChange={setColor} hideHSV dark />
                    </Grid>
                  </Grid>
                </Box>
                <Box sx={ { mt: 4 } }>
                  <Button
                    variant="contained"
                    color="primary"
                    fullWidth
                    type="submit"
                    disabled={ processing }
                  >
                    { processing ? "Submitting..." : "Submit" }
                  </Button>
                </Box>
              </form>
            </Paper>
          ) }
          <SnackbarAlert
            open={ snackbar.open }
            severity={ snackbar.severity }
            message={ snackbar.message }
            onClose={ handleCloseSnackbar }
            horizontal={ "center" }
            vertical={ "top" }
          />
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Create;
