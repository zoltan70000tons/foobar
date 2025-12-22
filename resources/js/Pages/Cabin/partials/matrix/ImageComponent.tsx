import { Box } from "@mui/material";

const CDN_IMAGE_PATH = `fckMeSideways`;
const CABIN_CONFIG_PATH =
  "70k-booking-engine/booking-client/cabin-configurations";

type Props = {
  images: string[] | null | undefined;
};

// Image
export default function ImageComponent({ images }: Props) {
  if (!images || images.length === 0) return null;

  return (
    <Box
      sx={{
        width: "100%",
        height: "auto",
        aspectRatio: "4 / 3",
        margin: "auto",
        display: "flex",
        justifyContent: "center",
        position: "relative",
      }}
    >
      {images.map((image, index) => (
        <img
          key={index}
          src={`https://${CDN_IMAGE_PATH}/${CABIN_CONFIG_PATH}/${image}`}
          alt={`${image}`}
          sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
          //fill={true}
          style={{
            objectFit: "cover",
          }}
        />
      ))}
    </Box>
  );
}
