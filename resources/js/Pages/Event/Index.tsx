import React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";
import { PageProps } from "@/types";
import {
  Card,
  CardActionArea,
  CardActions,
  CardContent,
  CardHeader,
  Chip,
  Container,
  Grid,
  Toolbar,
  Typography,
  Box,
  CardMedia,
} from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import dayjs from "dayjs";
import "dayjs/locale/en";
import localizedFormat from "dayjs/plugin/localizedFormat";
import AddCircleIcon from "@mui/icons-material/AddCircle";
import { Permissions } from "@/enums/PermissionEnum";

const Index = ({ auth, events }: PageProps) => {
  const { hasPermission } = usePermissions();
  dayjs.extend(localizedFormat);

  return (
    <AuthenticatedLayout user={auth.user} header={"Events"}>
      <Head title="Events" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        {hasPermission(Permissions.ViewEvents) && (
          <Grid container spacing={3}>
            {hasPermission(Permissions.CreateEvents) && (
              <Grid item xs={12} sm={6}>
                <Card
                  sx={{
                    display: "flex",
                    flexDirection: "column",
                    height: "100%",
                  }}
                >
                  <CardActionArea component={Link} href={route("events.create")} sx={{ flex: 1 }}>
                    <CardContent
                      sx={{
                        display: "flex",
                        flexDirection: "column",
                        alignItems: "center",
                        justifyContent: "center",
                        height: "100%",
                      }}
                    >
                      <Typography variant="h6" component="div" sx={{ display: "flex", alignItems: "center" }}>
                        <AddCircleIcon sx={{ mr: 1 }} />
                        New Event
                      </Typography>
                    </CardContent>
                  </CardActionArea>
                </Card>
              </Grid>
            )}

            {events.map((event) => (
              <Grid item key={event.id} xs={12} sm={6}>
                <Card
                  sx={{
                    display: "flex",
                    flexDirection: "column", // Default to column for mobile
                    height: "100%",
                  }}
                >
                  <CardActionArea component={Link} href={route("events.show", event.id)} sx={{ flex: 1 }}>
                    <Grid container>
                      <Grid item xs={12} sm={4}>
                        {event.image && (
                          <CardMedia
                            style={{ height: "170px" }}
                            component="img"
                            sx={{ width: "100%" }}
                            image={event.image}
                            alt={event.name}
                          />
                        )}
                      </Grid>
                      <Grid item xs={12} sm={8}>
                        <CardHeader sx={{ fontSize: "0.8rem" }} subheader={dayjs(event.start_date).format("LL")} />
                        <CardContent sx={{ mt: 0, pt: 0 }}>
                          <Typography gutterBottom variant="h6" component="div">
                            {event.name}
                          </Typography>
                          <Typography
                            variant="body2"
                            color="text.secondary"
                            noWrap
                            sx={{
                              overflow: "hidden",
                              textOverflow: "ellipsis",
                              whiteSpace: "nowrap",
                            }}
                          >
                            {event.description}
                          </Typography>
                        </CardContent>
                        <CardActions
                          sx={{
                            display: "flex",
                            justifyContent: "flex-end",
                            padding: 1,
                            marginTop: "auto",
                          }}
                        >
                          <Chip size="small" label={event.status} style={{ textTransform: "capitalize" }} />
                        </CardActions>
                      </Grid>
                    </Grid>
                  </CardActionArea>
                </Card>
              </Grid>
            ))}
          </Grid>
        )}
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
