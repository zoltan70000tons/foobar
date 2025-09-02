import { Box, Typography } from "@mui/material";
import { grey } from "@mui/material/colors";

export default function Copyright() {
  // handle always current year
  const year = new Date().getFullYear();

  return (
    <Box
      sx={{
        mt: 4,
        textAlign: "center",
        fontSize: "0.75rem",
        lineHeight: 1.5,
      }}
    >
      <Typography variant="body2" sx={{ lineHeight: 1.8, color: grey[200] }}>
        UMCruises International Ltd. Suite 205A Saffrey Square, Bank Lane and
        Bay Street, Nassau, BAHAMAS
        <br />
        All charges by credit card will be made on behalf of:
        <br />
        UMCruises UK LLP, Lower Ground Floor, 19-20 Berners Street, London, W1T
        3NW, UNITED KINGDOM
      </Typography>
      <Typography
        variant="body2"
        sx={{ lineHeight: 1.8, mt: 2, color: grey[200] }}
      >
        &copy; {year} UMCruises International Ltd. &amp; UMCruises UK LLP. All
        Rights Reserved.
      </Typography>
    </Box>
  );
}
