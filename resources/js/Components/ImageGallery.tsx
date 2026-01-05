import React from "react";
import { Grid, Card, CardMedia, CircularProgress, IconButton, Typography, Box } from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";

interface Image {
  name: string;
  url: string;
  date: string;
}

interface ImageGalleryProps {
  images: Image[] | null;
  loading: boolean;
  onDelete: (url: string) => void;
}

const ImageGallery: React.FC<ImageGalleryProps> = ({ images, loading, onDelete }) => {
  if (loading) {
    return (
      <div style={{ display: "flex", justifyContent: "center", alignItems: "center", height: "100vh" }}>
        <CircularProgress />
      </div>
    );
  }

  if (!images || images.length === 0) {
    return (
      <Box display="flex" justifyContent="center" alignItems="center" height="100vh">
        <Typography variant="h6">No images loaded</Typography>
      </Box>
    );
  }

  return (
    <Grid container spacing={2}>
      {images.map((image) => (
        <Grid item xs={12} sm={6} md={4} lg={3} key={image.url} style={{ position: "relative" }}>
          <Card>
            <CardMedia component="img" height="140" image={image.url} alt={image.name} />
            <IconButton
              onClick={() => onDelete(image.url)}
              style={{
                position: "absolute",
                top: 8,
                right: 8,
                color: "white",
                backgroundColor: "rgba(0, 0, 0, 0.5)",
              }}
            >
              <CloseIcon />
            </IconButton>
          </Card>
        </Grid>
      ))}
    </Grid>
  );
};

export default ImageGallery;
