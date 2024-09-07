import React, { useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PageProps } from "@/types";
import { Head, router, useForm } from "@inertiajs/react";
import {
  Box,
  Button,
  Container,
  Grid,
  IconButton,
  SelectChangeEvent,
  TextField,
  Toolbar,
  Typography,
} from "@mui/material";
import FormatInput from "@/Components/FormatInput";
import CategoryTypeSelect from "@/Components/CategoryTypeSelect";
import { CategoryTypes } from "@/enums/CategoryTypeEnum";
import { GridMenuIcon } from "@mui/x-data-grid";
import DropZoneField from "@/Components/DropZoneField";
import CustomNumericFormat from "@/Components/CustomNumericFormat";
import { NumericFormat } from "react-number-format";
import UMSelect from "@/Components/UMSelect";

const Create = ({
  auth,
  event,
  cruisers,
  errors,
}: PageProps & { tab: string; data: any }) => {
  const { data, setData, post, processing } = useForm({
    category_name: "",
    category_code: "",
    price: "",
    capacity: "",
    description: "",
    category_type: "",
    display_order: "",
    event_id: event.id,
    cruise: ""
  });

  const [uploadedFiles, setUploadedFiles] = useState<string[]>([]);

  const handleFilesChange = (files: string[]) => {
    setUploadedFiles(files);
  };

  const handleInputChange = (
    e: React.FormEvent<HTMLFormElement> | string,
    value?: string
  ) => {
    let name: string;

    if (typeof e === "string") {
      name = e;
      setData(name, value || "");
    } else {
      const target = e.target as HTMLInputElement;
      name = target.name;
      setData(name, value || target.value);
    }
  };

  const handleCategoryTypeChange = (
    event: SelectChangeEvent<CategoryTypes>
  ) => {
    setData("category_type", event.target.value);
  };

  const handleCruiserChange = (
    event: SelectChangeEvent<{ value: string }>
  ) => {
    setData("cruise", event.target.value);
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const formData = new FormData();
    for (const key in data) {
      formData.append(key, data[key]);
    }
    uploadedFiles.forEach((file) => {
      formData.append("files[]", file);
    });
    router.post(route("cabinCategory.store", { id: event.id }), formData, {
      onSuccess: (response) => {
        //   setSnackbar({
        //     open: true,
        //     severity: "success",
        //     message: "Event created successfully",
        //   });
      },
      onError: (errors) => {
        //   setSnackbar({
        //     open: true,
        //     severity: "error",
        //     message: "Error creating event",
        //   });
      },
    });
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Cabins"}>
      <Head title="Cabins" />
      <Toolbar sx={{ mt: 8 }}>
        <IconButton edge="start" color="inherit" aria-label="menu">
          <img src={event.image} alt="Logo" style={{ height: 40 }} />
        </IconButton>
        <Typography variant="h6" style={{ flexGrow: 1 }}>
          {event.name}
        </Typography>

        {/* <IconButton color="inherit">
        <GridMenuIcon />
      </IconButton> */}
      </Toolbar>
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Typography variant="h5" sx={{ mb: 3 }}>
              Create Cabin Category
            </Typography>
            <form onSubmit={handleSubmit}>
              <Grid container spacing={2}>
                <Grid item xs={12} md={6}>
                  <Box sx={{ mb: 2 }}>
                    <TextField
                      name="category_name"
                      label="Category Name"
                      variant="outlined"
                      fullWidth
                      value={data.category_name}
                      onChange={handleInputChange}
                      error={Boolean(errors.category_name)}
                      helperText={errors.category_name}
                    />
                  </Box>
                </Grid>
                <Grid item xs={12} md={6}>
                  <Box sx={{ mb: 2 }}>
                    <TextField
                      name="category_code"
                      label="Category code"
                      variant="outlined"
                      fullWidth
                      value={data.category_code}
                      onChange={handleInputChange}
                      error={Boolean(errors.category_code)}
                      helperText={errors.category_code}
                    />
                  </Box>
                </Grid>
              </Grid>

              <Grid container spacing={2}>
                <Grid item xs={12} md={6}>
                  <Box sx={{ mb: 2 }}>
                    <FormatInput
                      name="price"
                      label="Price per person"
                      placeholder="Enter price"
                      format="#,##0.00"
                      prefix="$"
                      decimalScale={2}
                      onChange={handleInputChange}
                      error={errors}
                     
                    />
                  </Box>
                </Grid>
                <Grid item xs={12} md={6}>
                  <Box sx={{ mb: 2 }}>
                    <TextField
                      name="capacity"
                      label="Capacity"
                      variant="outlined"
                      fullWidth
                      type="number"
                      inputProps={{ min: 0, max: 12 }}
                      value={data.capacity}
                      onChange={handleInputChange}
                      error={Boolean(errors.capacity)}
                      helperText={errors.capacity}
                    />
                  </Box>
                </Grid>
              </Grid>

              <Grid container spacing={2}>
                <Grid item xs={12} md={6}>
                  <Box sx={{ mb: 2 }} >
                    <TextField
                      name="description"
                      label="Description"
                      variant="outlined"
                      fullWidth
                      multiline
                      rows={8}
                      value={data.description}
                      onChange={handleInputChange}
                      error={Boolean(errors.description)}
                      helpeText={errors.description}
                    />
                  </Box>
                </Grid>
                <Grid item xs={12} md={6}>
                  <Box sx={{ mb: 2 }}>

                    <CategoryTypeSelect
                      onChange={handleCategoryTypeChange}
                      error={errors}
                      value={data.category_type}

                    />
                  </Box>
                  <Box sx={{ mb: 2 }}>
                    <TextField
                      name="display_order"
                      label="Display order"
                      type="number"
                      variant="outlined"
                      fullWidth
                      inputProps={{ min: 0 }}
                      value={data.display_order}
                      onChange={handleInputChange}
                      error={Boolean(errors.display_order)}
                      helperText={errors.display_order}
                    />
                  </Box>

                  <Box sx={{ mb: 2 }}>
                    <UMSelect
                      name="cruise"
                      id="cruise"
                      value={data.cruise}
                      options={cruisers.data}
                      onChange={handleCruiserChange}
                      error={errors.cruise}
                      label="Cruiser"
                    />
                  </Box>
                </Grid>
              </Grid>

              <Grid container spacing={2}>
                <Grid item xs={12} md={6}>
                  <Typography variant="h6">Media</Typography>
                  <DropZoneField onFilesChange={handleFilesChange} />
                </Grid>
                <Grid item xs={12}>
                  <Box sx={{ mb: 2 }}>
                    <Button
                      variant="contained"
                      color="primary"
                      fullWidth
                      type="submit"
                    >
                      Submit
                    </Button>
                  </Box>
                </Grid>
              </Grid>
            </form>
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Create;
