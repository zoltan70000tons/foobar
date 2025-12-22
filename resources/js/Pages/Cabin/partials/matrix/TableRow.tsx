import { Box, Typography } from "@mui/material";

export default function TableRow({
  firstCol,
  secondCol,
}: {
  firstCol: string;
  secondCol: string;
}) {
  return (
    <Box
      sx={{
        display: "flex",
        py: 1.5,
        justifyContent: "space-between",
      }}
    >
      <Typography sx={{ fontWeight: "bold" }}>{firstCol}</Typography>
      <Typography textAlign="right">{secondCol}</Typography>
    </Box>
  );
}
