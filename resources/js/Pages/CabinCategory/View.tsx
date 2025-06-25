import React, { useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PageProps } from "@/types";
import { Head, router, useForm } from "@inertiajs/react";
import {
  Accordion,
  AccordionDetails,
  AccordionSummary,
  Box,
  Container,
  Grid,
  IconButton,
  TextField,
  Toolbar,
  Tooltip,
  Typography,
} from "@mui/material";
import FormatInput from "@/Components/FormatInput";
import CategoryTypeSelect from "@/Components/CategoryTypeSelect";
import UMSelect from "@/Components/UMSelect";
import ImageGallery from "@/Components/ImageGallery";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import { usePermissions } from "@/Providers/PermissionContext";
import { ArrowBack, Delete, Edit } from "@mui/icons-material";
import { Permissions } from "@/enums/PermissionEnum";
import { Errors } from "@inertiajs/core";
import { CabinCategory } from "@/interfaces/CabinCategory";
import { Cruiser, Cruisers } from "@/interfaces/Cruiser";
import { Event } from "@/interfaces/Event";
import { CategoryTypes } from "@/enums/CategoryTypeEnum";


type Props = PageProps & {
  auth: AuthProps;
  event: Event;
  cruisers: Cruisers;
  cabin_category: CabinCategory;
  errors: Errors;
};

const View = ({
  auth,
  event,
  cruisers,
  cabin_category,
  errors,
}: Props) => {
  const { data, setData, post, processing } = useForm({
    category_name: cabin_category.category_name,
    category_code: cabin_category.category_code,
    price: cabin_category.price,
    capacity: cabin_category.capacity,
    description: cabin_category.description,
    category_type: cabin_category.category_type,
    display_order: cabin_category.display_order,
    event_id: event.id,
    cruise: cabin_category.spec.cruise_id,
  });

  const category = (Object.entries(CategoryTypes).find(([_, v]) => v === data.category_type)?.[1] ?? CategoryTypes.INTERIOR);


  interface Image {
    name: string;
    url: string;
    path: string;
    date: string;
  }

  const { hasPermission } = usePermissions();
  const [images, setImages] = useState<Image[]>(
    Array.isArray(cabin_category.images) ? cabin_category.images : []
  );

  const handleInputChange = () => { }
  const handleCategoryTypeChange = () => { }
  const handleCruiserChange = () => { }
  const onDelete = () => { }
  const handleBack = () => {
    window.history.back();
  }

  const handleEdit = () => {
    router.get(route("cabinCategory.edit", { id: event.id, catId: cabin_category.id }));
  };

  const handleDelete = () => {
    if (confirm("Are you sure you want to delete this category?")) {
      router.delete(route("cabinCategory.destroy", { id: event.id, catId: cabin_category.id }), {
        onSuccess: () => {
          router.get(route("cabinCategory.index", { id: event.id }));
        },
      });
    }
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
              View Cabin Category
            </Typography>
            <form>
              <Grid container spacing={2}>
                <Grid item xs={12} md={6}>
                  <Box sx={{ mb: 2 }}>
                    <TextField
                      name="category_name"
                      label="Category Name"
                      variant="outlined"
                      fullWidth
                      disabled
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
                      disabled
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
                      disabled
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
                      disabled
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
                      disabled
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
                      value={category}
                      disabled={true}
                    />
                  </Box>
                  <Box sx={{ mb: 2 }}>
                    <TextField
                      name="display_order"
                      label="Display order"
                      type="number"
                      variant="outlined"
                      fullWidth
                      disabled
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
                      value={String(data.cruise)}
                      options={cruisers.data}
                      disabled
                      onChange={handleCruiserChange}
                      error={errors.cruise ? { cruise: errors.cruise as string } : undefined}
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

              {/* <Box sx={{ mt: 3 }}>
                <Grid container spacing={2}>
                  <Grid item>
                    <Button
                      variant="outlined"
                      color="secondary"
                      onClick={() => router.back()}
                    >
                      Back
                    </Button>
                  </Grid>
                  <Grid item>
                    <Button
                      variant="contained"
                      color="primary"
                      onClick={handleEdit}
                    >
                      Edit
                    </Button>
                  </Grid>
                  <Grid item>
                    <Button
                      variant="contained"
                      color="error"
                      onClick={handleDelete}
                    >
                      Delete
                    </Button>
                  </Grid>
                </Grid>
              </Box> */}
              <Box sx={{ mt: 4 }}>
                <div
                  style={{
                    display: "flex",
                    justifyContent: "flex-end",
                    gap: "8px",
                  }}
                >
                  <Tooltip title="Back">
                    <IconButton color="primary" onClick={handleBack}>
                      <ArrowBack />
                    </IconButton>
                  </Tooltip>
                  {hasPermission(Permissions.EditCabinCategories) && (<Tooltip title="Edit">
                    <IconButton color="primary" onClick={handleEdit}>
                      <Edit />
                    </IconButton>
                  </Tooltip>)}
                  {hasPermission(Permissions.DeleteCabinCategories) && (<Tooltip title="Delete">
                    <IconButton color="error" onClick={handleDelete}>
                      <Delete />
                    </IconButton>
                  </Tooltip>)}

                </div>
              </Box>
            </form>
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default View;
