import { Box } from "@mui/material";

export default function EnvironmentBar({ environment }: { environment: string | null }) {
  if (!environment) {
    return null;
  }

  const color = environment === "stage" ? "red" : environment === "prod" ? "green" : "gray";

  return (
    <Box
      sx={{
        position: "fixed",
        top: 0,
        left: 0,
        width: "100%",
        height: "20px",
        backgroundColor: color,
        color: "white",
        textAlign: "center",
        zIndex: 9999,
        fontSize: "12px",
      }}
    >
      {environment.toUpperCase()}
    </Box>
  );
}
