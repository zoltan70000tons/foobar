import React, { useEffect, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PageProps } from "@/types";
import { Head, router, useForm, usePage } from "@inertiajs/react";
import {
  Accordion,
  AccordionDetails,
  AccordionSummary,
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
import DropZoneField from "@/Components/DropZoneField";
import UMSelect from "@/Components/UMSelect";
import ImageGallery from "@/Components/ImageGallery";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { Errors } from "@inertiajs/core";
import { Cruisers } from "@/interfaces/Cruiser";
import { Event } from "@/interfaces/Event";
import { CabinCategory } from "@/interfaces/CabinCategory";


type Props = PageProps & {
  auth: AuthProps;
  event: Event;
  cruisers: Cruisers;
  cabin_category: CabinCategory;
  errors: Errors;
};


const Edit = ({
  auth,
  event,
  cruisers,
  cabin_category,
  errors,
}: Props & { tab: string;}) => {
  
  const { flash } = usePage().props as { message?: string; success?: boolean; error?: boolean };

  const { data, setData, post, processing } = useForm({
    category_name: cabin_category.category_name,
    category_code: cabin_category.category_code,
    price: cabin_category.price,
    capacity: cabin_category.spec.capacity,
    description: cabin_category.spec.description,
    category_type: cabin_category.category_type,
    display_order: cabin_category.spec.display_order,
    event_id: event.id,
    cruise: cabin_category.spec.cruise_id,
  });

  interface Image {
    name: string;
    url: string;
    path: string;
    date: string;
  }
  
  const { showSnackbar } = useSnackbar();
  const [images, setImages] = useState<Image[]>(
    Array.isArray(cabin_category.images) ? cabin_category.images : []
  );
  const [uploadedFiles, setUploadedFiles] = useState<string[]>([]);

  useEffect(() => {
    if (flash.message) {
      if (flash.success) {
        showSnackbar(flash.message, 'success');
      } else if (flash.error) {
        showSnackbar(flash.message, 'error');
      }
    }
  }, [flash])

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

  const handleCruiserChange = (event: SelectChangeEvent<{ value: string }>) => {
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

    const imagePathsSet = new Set((images || []).map((image) => image.path));
    const missingImages = (cabin_category.images || []).filter(
      (image) => !imagePathsSet.has(image.path)
    );

    missingImages.forEach((image) => {
      formData.append("remove[]", image.path);
    });

    router.post(route("cabinCategory.update", { id: event.id, catId: cabin_category.id }), formData, {
      onSuccess: (response) => {},
      onError: (errors) => {},
    });
  };

  const onDelete = (url: string) => {
    setImages((prevImages) => prevImages.filter((image) => image.url !== url));
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
      </Toolbar>
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Typography variant="h5" sx={{ mb: 3 }}>
              Edit Cabin Category
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
                      val={data.price}
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
                  <Box sx={{ mb: 2 }}>
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
                      helperText={errors.description}
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

              <Grid item xs={12} sx={{ mb: 2 }}>
                <Accordion>
                  <AccordionSummary
                    expandIcon={<ExpandMoreIcon />}
                    aria-controls="panel1-content"
                    id="panel1-header"
                  >
                    Image Gallery
                  </AccordionSummary>
                  <AccordionDetails>
                    <ImageGallery images={images || []} onDelete={onDelete} />
                  </AccordionDetails>
                </Accordion>
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

export default Edit;
