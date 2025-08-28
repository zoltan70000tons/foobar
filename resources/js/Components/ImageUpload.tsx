import React, { useState, useEffect } from 'react';
import { Box, BoxProps, Button, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';



const UploadButton = styled(Button)({
  marginTop: 16,
});

const StyledImageBox = styled(({ imageUrl, ...other }: { imageUrl?: string } & BoxProps) => (
  <Box {...other} />
))(({ imageUrl }) => ({
  width: 100,
  height: 100,
  border: '1px solid #ddd',
  borderRadius: 8,
  overflow: 'hidden',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor: '#f5f5f5',
  backgroundImage: imageUrl ? `url(${imageUrl})` : 'none',
  backgroundSize: 'cover',
  backgroundPosition: 'center',
}));


interface ImageUploadProps {
  onChange: (file: File) => void;
  initialImageUrl?: string | null;
  error?: string;
}

const ImageUpload: React.FC<ImageUploadProps> = ({ onChange, initialImageUrl, error }) => {
  const [imageUrl, setImageUrl] = useState(initialImageUrl || null);

  useEffect(() => {
    setImageUrl(initialImageUrl);
  }, [initialImageUrl]);

  interface FileChangeEvent extends React.ChangeEvent<HTMLInputElement> {}

  const handleFileChange = (event: FileChangeEvent) => {
    const file: File | undefined = event.target.files?.[0];
    if (file) {
      const reader: FileReader = new FileReader();
      reader.onloadend = () => {
        setImageUrl(reader.result as string); // Set image URL
        onChange(file); // Pass file to parent component
      };
      reader.readAsDataURL(file);
    }
  };

  return (
    <Box display="flex" alignItems="center">
      <StyledImageBox imageUrl={imageUrl}>
        {!imageUrl && <Typography variant="body2" color="textSecondary">No Image</Typography>}
      </StyledImageBox>
      <Box ml={2}>
        <Typography variant="body2" color="textSecondary">Please upload a square image, size less than 500KB</Typography>
        <Typography variant="body1">Upload Image:</Typography>
        <input
          accept="image/*"
          type="file"
          id="upload-button"
          style={{ display: 'none' }}
          onChange={handleFileChange}
        />

        <label htmlFor="upload-button">
          <UploadButton variant="contained" component="span">
            Choose File
          </UploadButton>
        </label>
      </Box>
    </Box>
  );
};

export default ImageUpload;
