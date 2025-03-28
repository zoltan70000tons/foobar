import React, { useState } from "react";
import { PageProps } from "@/types";
import { Avatar, Box, Container, Grid, Typography, Toolbar, useTheme } from "@mui/material";
import dayjs from "dayjs";
import "dayjs/locale/en";
import localizedFormat from "dayjs/plugin/localizedFormat";
import { router } from "@inertiajs/react";
// import LoadingOverlay from "./LoadingOverlay";

interface Event {
  id: string;
  name: string;
  image: string;
  start_date: string;
  description?: string;
}

const EventSelector = ({ events, url }: PageProps & { events: Event[] }) => {
  dayjs.extend(localizedFormat);

  const handleClick = (eventId: string) => {
    router.get(`/events/${eventId}${url}`);
  };

  return (
    <Grid container spacing={3}>
      {events &&
        events.length > 0 &&
        events.map((event: Event) => (
          <Grid item xs={12} sm={6} md={4} key={event.id}>
            <Box
              onClick={() => handleClick(event.id)}
              sx={{
                display: "flex",
                flexDirection: "row",
                alignItems: "center",
                cursor: "pointer",
                p: 2,
                backgroundColor: "#121212",
                borderRadius: 2,
                boxShadow: "0 4px 8px rgba(0, 0, 0, 0.1)",
                transition: "transform 0.2s",
                "&:hover": {
                  transform: "scale(1.05)",
                },
              }}
            >
              <Avatar
                variant="square"
                sx={{
                  width: 80,
                  height: 80,
                  mr: 2,
                  // border: `2px solid ${theme.palette.secondary.main}`,
                  // backgroundColor: theme.palette.secondary.light,
                }}
                src={event.image}
              >
                G
              </Avatar>
              <Box>
                <Typography
                  variant="h6"
                  sx={
                    {
                      //fontWeight: "bold",
                      //color: theme.palette.primary.dark,
                    }
                  }
                >
                  {event.name}
                </Typography>
                <Typography
                  variant="body2"
                  sx={{
                    // color: theme.palette.text.secondary,
                    mt: 1,
                  }}
                >
                  {dayjs(event.start_date).format("LL")}
                </Typography>
                <Typography
                  variant="body2"
                  //   sx={{
                  //     color: theme.palette.text.secondary,
                  //   }}
                >
                  {/* {event.description} */}
                </Typography>
              </Box>
            </Box>
          </Grid>
        ))}

      {/* <LoadingOverlay  open={loading}/> */}
    </Grid>
  );
};

export default EventSelector;
