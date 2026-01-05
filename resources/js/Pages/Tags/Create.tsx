import React from "react";
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
  Button,
  Chip,
  Autocomplete,
  Rating,
  Typography,
} from "@mui/material";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import { ColorPicker, useColor } from "react-color-palette";
import "react-color-palette/dist/css/rcp.css";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";

const Create = ({ auth, errors, events, tagTypes }: PageProps) => {
  console.log("Events:", events);
  const { hasPermission } = usePermissions();
  const { data, setData, post, processing } = useForm({
    name: "",
    description: "",
    priority: 0,
    //color: "",
  });
  const [color, setColor] = useColor("#121212");

  const { showSnackbar } = useSnackbar();

  const handleChange = <TForm extends Record<string, any>>(
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>,
  ) => {
    const { name, value } = e.target;

    setData(name as keyof TForm, value as TForm[keyof TForm]);
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    const formData = new FormData();
    for (const key in data) {
      formData.append(key, data[key]);
    }
    formData.append("color", color.hex);

    router.post(route("tags.store"), formData, {
      forceFormData: true,
      onSuccess: (response) => {
        showSnackbar("Tag created successfully", "success");
      },
      onError: (errors) => {
        const errorMessages = Object.values(errors).join("\n");
        showSnackbar(`Error creating tag\n${errorMessages}`, "error");
      },
    });
  };

  const handleBack = () => {
    router.visit(route("tags.index"), {
      only: ["userTags"],
    });
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Tags"}>
      <Head title="Create Tag" />
      <Toolbar sx={{ mt: 8 }}>
        <Button variant="outlined" color="secondary" onClick={handleBack}>
          Back
        </Button>
      </Toolbar>
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          {hasPermission(Permissions.CreateTags) && (
            <Paper
              sx={{
                p: 2,
                display: "flex",
                flexDirection: "column",
                minHeight: 240,
                width: "100%",
              }}
            >
              <h1>Create Tag</h1>
              <form onSubmit={handleSubmit} encType="multipart/form-data">
                <Box sx={{ width: "100%" }}>
                  <Grid container spacing={2}>
                    <Grid item xs={6}>
                      <Autocomplete
                        disablePortal
                        options={events}
                        getOptionLabel={(option) => option.name}
                        onChange={(event, value) => {
                          console.log("Selected event:", value);
                          setData("event_id", value ? value.id : "");
                        }}
                        renderInput={(params) => (
                          <TextField
                            {...params}
                            label="Event"
                            variant="outlined"
                            name={"event_id"}
                            //value={ data.event_id }
                            //onChange={ handleChange }
                          />
                        )}
                      />
                    </Grid>
                    <Grid item xs={6}>
                      <Autocomplete
                        disablePortal
                        options={tagTypes}
                        getOptionLabel={(option) => option.name}
                        onChange={(event, value) => {
                          console.log("Selected tag type:", value);
                          setData("entity", value ? value.value : "");
                        }}
                        renderInput={(params) => (
                          <TextField
                            {...params}
                            label="Tag for"
                            variant="outlined"
                            name={"entity"}
                            //value={ data.event_id }
                            //onChange={ handleChange }
                          />
                        )}
                      />
                    </Grid>
                    <Grid item xs={6}>
                      <TextField
                        fullWidth
                        label="Name"
                        variant="outlined"
                        value={data.name}
                        name={"name"}
                        onChange={handleChange}
                      />
                    </Grid>
                    <Grid item xs={6}>
                      <TextField
                        fullWidth
                        label="Description"
                        variant="outlined"
                        value={data.description}
                        name={"description"}
                        onChange={handleChange}
                      />
                    </Grid>
                    <Grid item xs={6}>
                      <TextField
                        fullWidth
                        label="Color"
                        variant="outlined"
                        value={color.hex}
                        name={"color"}
                        disabled
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
                              name: "priority",
                              value: clampedValue,
                            },
                          });
                        }}
                        max={10}
                      />
                    </Grid>
                    <Grid item xs={6}>
                      <div style={{ display: "flex", alignItems: "center", gap: "16px" }}>
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
                    <Grid item xs={6}>
                      <ColorPicker width={456} height={228} color={color} onChange={setColor} hideHSV dark />
                    </Grid>
                  </Grid>
                </Box>
                <Box sx={{ mt: 4 }}>
                  <Button variant="contained" color="primary" fullWidth type="submit" disabled={processing}>
                    {processing ? "Submitting..." : "Submit"}
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

export default Create;
