import { useState } from "react";
import { Box, CircularProgress } from "@mui/material";
import ImageComponent from "./ImageComponent";

// Helper function to validate a URL
const isValidUrl = (url: string): boolean => {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
};

type Props = {
  iframe: string | null | undefined;
};

export default function IframeComponent({ iframe }: Props) {
  const [isIframeLoading, setIsIframeLoading] = useState(true);

  if (iframe && isValidUrl(iframe)) {
    return (
      <Box
        sx={{
          position: "relative",
          width: "100%",
          height: "auto",
          aspectRatio: "4 / 3",
        }}
      >
        <iframe
          id="mp-iframe"
          style={{
            top: 0,
            left: 0,
            width: "100%",
            height: "100%",
            border: "none",
            display: "block",
          }}
          src={iframe}
          allow="vr"
          allowFullScreen
          onLoad={() => setIsIframeLoading(false)}
        />
        {isIframeLoading && (
          <Box
            sx={{
              position: "absolute",
              top: 0,
              left: 0,
              width: "100%",
              height: "100%",
              display: "flex",
              justifyContent: "center",
              alignItems: "center",
            }}
          >
            <CircularProgress />
          </Box>
        )}
      </Box>
    );
  }
  // If iframe is not a valid URL, treat it as an image path
  if (iframe && !isValidUrl(iframe)) {
    return <ImageComponent images={[iframe]} />;
  }

  return null;
}
