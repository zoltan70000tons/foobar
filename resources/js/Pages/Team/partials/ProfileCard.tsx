import { Avatar, Card, Badge, Chip, Grid, Typography } from "@mui/material";
import PhotoCameraIcon from "@mui/icons-material/PhotoCamera";
import { User } from "@/interfaces/User";

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



interface ProfileCardProps {
  user: User;
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
    <Card variant="outlined" sx={{borderRadius:0,  background:'#383838'}}>
      <Grid
        container
        direction="column"
        justifyContent="center"
        alignItems="center"
        sx={{
          background: "#383838",
          border: "2px solid grey",
          marginBottom: { xs: "1rem", sm: "1rem", md : 0 },
        }}
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
            {user.detail?.first_name} {user.detail?.last_name}
          </Typography>
          <Typography color="text.secondary">{user.email}</Typography>
        </Grid>
        <Grid container>
          <Grid item xs={6}>
            <Typography style={styles.details}>Organization</Typography>
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
