import { Typography, Link, Box, Button } from "@mui/material";
import { styled } from "@mui/material/styles";
import { blue, grey } from "@mui/material/colors";
import { Info } from "@mui/icons-material";

export default function SurvivorLogin() {

    const AnimatedBorder = styled("svg")({
    position: "absolute",
    top: 0,
    left: 0,
    width: "100%",
    height: "100%",
    pointerEvents: "none",
    "& .line": {
      strokeDasharray: "1300",
      strokeDashoffset: "1300",
      strokeWidth: "4px",
      fill: "transparent",
      stroke: "url(#gradient)",
      animation: "svgAnimation 2.5s linear infinite",
    },
    "@keyframes svgAnimation": {
      "0%": { strokeDashoffset: "1300", opacity: 0.5 },
      "80%": { opacity: 1 },
      "100%": { strokeDashoffset: "0", opacity: 0 },
    },
  });

  return (
      <Box
        sx={{
          position: "relative",
          mb: 2,
          color: "text.primary",
          backgroundColor: grey[900],
          justifyContent: "center",
          display: "flex",
          flexDirection: { xs: "column", md: "row" },
          borderRadius: "8px",
          padding: "16px",
          gap: 1.5,
          overflow: "hidden",
        }}
      >
        <AnimatedBorder xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="gradient">
              <stop offset="0%" stopColor="transparent" />
              <stop offset="50%" stopColor={blue[600]} />
              <stop offset="100%" stopColor="transparent" />
            </linearGradient>
          </defs>
          <rect
            rx="18"
            ry="18"
            className="line"
            height="100%"
            width="100%"
            strokeLinejoin="round"
          />
        </AnimatedBorder>
        <Info sx={{ color: "text.primary" }} />

        <Typography variant="body1">
          Have you sailed with us before? Link your eMail to your Survivor Number!
        </Typography>

        <Button
          variant="contained"
          color="primary"
          sx={{ minWidth: "100px", py: "5px" }}
          size="small"
          // onClick={() => router.push("/activate-survivor-account")}
        >
          Click here
        </Button>
      </Box>
  );
}
