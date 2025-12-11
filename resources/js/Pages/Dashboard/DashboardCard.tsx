import { Paper, Typography, Box, IconButton, Badge, Grid } from "@mui/material";
import { ThemeProvider } from "@mui/material/styles";
import theme from "../../Theme/theme";
import { router } from "@inertiajs/react";
import { SvgIconComponent } from "@mui/icons-material";

type DashboardCardProps = {
  title: string;
  description: string;
  Icon: SvgIconComponent;
  link: string;
  badgeContent?: number;
  onBadgeClick?: () => void;
};

const DashboardCard = ({ title, description, Icon, link, badgeContent, onBadgeClick }: DashboardCardProps) => {
  // const [loading, setLoading] = useState(false);

  const handleClick = () => {
    // setLoading(true);
    router.get(link);
  };

  return (
    <ThemeProvider theme={theme}>
      <Paper
        sx={{
          display: "flex",
          alignItems: "center",
          padding: "10px",
          marginBottom: "10px",
          position: "relative", // Necessary for positioning badge
          cursor: "pointer", // Change cursor to pointer for click indication
        }}
        onClick={() => handleClick()} // Redirect on click
      >
        <Box sx={{ marginRight: "15px" }}>{Icon && <Icon fontSize="large" />}</Box>
        <Box>
          <Typography variant="h5" component="div" sx={{ wordBreak: "break-word" }}>
            {title}
          </Typography>
          <Typography variant="body2" color="text.secondary">
            {description}
          </Typography>
        </Box>
        {badgeContent && (
          <Badge badgeContent={badgeContent} color="primary" sx={{ position: "absolute", top: 10, right: 10 }}>
            <IconButton onClick={onBadgeClick} aria-label="notifications">
              {/* Optionally include a badge icon */}
            </IconButton>
          </Badge>
        )}
      </Paper>

      {/* <LoadingOverlay  open={loading}/> */}
    </ThemeProvider>
  );
};

export default DashboardCard;
