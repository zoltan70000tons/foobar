import { Box, Typography } from "@mui/material";
import { alpha } from "@mui/material/styles";
import { blue } from "@mui/material/colors";
import { PhotoCamera } from "@mui/icons-material";
import { fixedHeight } from "@/Pages/Cabin/partials/matrix/constants";

type CabinCodeProps = {
  cabinCode: string;
  onOpenDetail: () => void;
};

export function CabinCode({ cabinCode, onOpenDetail }: CabinCodeProps) {
  return (
    <Box
      component="button"
      onClick={onOpenDetail}
      sx={{
        cursor: "pointer",
        height: fixedHeight,
        width: "100%",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        border: "1px solid #1e1e1e",
        borderLeft: "none",
        backgroundColor: alpha(blue[600], 0.1),
        color: blue[600],
        "&:hover": { backgroundColor: alpha(blue[600], 0.3) },
      }}
    >
      <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
        <Typography sx={{ fontWeight: "bold", fontSize: "14px" }}>
          {cabinCode}
        </Typography>
        <PhotoCamera sx={{ color: blue[600], fontSize: 16 }} />
      </Box>
    </Box>
  );
}
