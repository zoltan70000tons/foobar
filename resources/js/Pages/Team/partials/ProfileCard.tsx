// IMPORTS
import Card from "@mui/material/Card";
import Typography from "@mui/material/Typography";
import { Chip, Grid } from "@mui/material";
import Avatar from "@mui/material/Avatar";
import PhotoCameraIcon from "@mui/icons-material/PhotoCamera";
import Badge from "@mui/material/Badge";
import Button from "@mui/material/Button";

const styles = {
  details: {
    padding: "1rem",
    borderTop: "1px solid #e1e1e1"
  },
  value: {
    padding: "1rem 2rem",
    borderTop: "1px solid #e1e1e1",
    color: "#899499"
  }
};

interface User {
  id: number;
  name: string;
  email: string;
  status: string;
  roles: string[];
  organization_id: number;
  organization_name: string;
  survivor_number: number;
}

interface ProfileCardProps {
  user: User | null;
}

export default function ProfileCard({ user }: ProfileCardProps) {

  const getChipColor = (status: string) => {
    switch (status) {
      case 'Pending':
        return 'error';
      case 'Active':
        return 'success';
      default:
        return 'default';
    }
  };

  return (
    <Card variant="outlined">
      <Grid
        container
        direction="column"
        justifyContent="center"
        alignItems="center"
      >

        <Grid item sx={{ p: "1.5rem 0rem", textAlign: "center" }}>
          <Badge
            overlap="circular"
            anchorOrigin={{ vertical: "bottom", horizontal: "right" }}
            badgeContent={
              <PhotoCameraIcon
                sx={{
                  border: "5px solid white",
                  backgroundColor: "#ff558f",
                  borderRadius: "50%",
                  padding: ".2rem",
                  width: 35,
                  height: 35
                }}
              ></PhotoCameraIcon>
            }
          >
            <Avatar
              sx={{ width: 100, height: 100, mb: 1.5 }}
            >L</Avatar>
          </Badge>

          <Typography variant="h6">
            {user.name}
          </Typography>
          <Typography color="text.secondary">{user.email}</Typography>
        </Grid>
        <Grid container>
          <Grid item xs={6}>
            <Typography style={styles.details}>Organizacion</Typography>
            <Typography style={styles.details}>Status</Typography>
            <Typography style={styles.details}>Role</Typography>
          </Grid>
          {/* VALUES */}
          <Grid item xs={6} sx={{ textAlign: "end" }}>
            <Typography style={styles.value}>{"70K"}</Typography>
            <Typography style={styles.value}><Chip size="small" label={user?.status} color={getChipColor(user?.status)} style={{ marginBottom: "-1px" }}
            /></Typography>
            <Typography style={styles.value}><Chip
              size="small"
              label={user?.roles[0] ? user?.roles[0] : "Pending"}
              color="default"
            />
            </Typography>
          </Grid>
        </Grid>
        <Grid item style={styles.details} sx={{ width: "100%", height: "100px" }}>

        </Grid>
      </Grid>
    </Card>
  );
}
