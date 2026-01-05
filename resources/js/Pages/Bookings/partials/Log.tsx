import React from "react";
import { Box, Typography, Divider } from "@mui/material";
import { styled } from "@mui/system";

// interface LogEntry {
//   date: string;
//   time: string;
//   user: string;
//   action: string;
//   description?: string;
// }

// interface LogProps {
//   logs: LogEntry[];
// }

const Dot = styled("span")({
  width: 10,
  height: 10,
  borderRadius: "50%",
  backgroundColor: "#ffffff",
  display: "inline-block",
  marginRight: 8,
});

const Log = ({ logs }) => {
  console.log(logs);
  return (
    <Box p={4} bgcolor="#1c1c1c" color="white" minHeight="100vh">
      <Typography variant="h5" mb={2}>
        Logs
      </Typography>
      {logs.map((log, index) => (
        <Box key={index} display="flex" alignItems="flex-start" mb={3}>
          <Box display="flex" flexDirection="column" alignItems="center" mr={2}>
            <Dot />
            {index < logs.length - 1 && (
              <Divider orientation="vertical" flexItem sx={{ borderColor: "gray", height: "100%", margin: "4px 0" }} />
            )}
          </Box>
          <Box>
            <Typography variant="body2" color="gray">
              {log.created_at}
            </Typography>
            <Typography variant="body2" color="gray" mb={1}>
              by @{log.user.username}
            </Typography>
            <Typography variant="body1" fontWeight="bold">
              {log.action}
            </Typography>
            {/*  {log.description && (
              <Typography variant="body2" color="gray" mt={1}>
                <strong>Custom:</strong> {log.description}
              </Typography>
            )}  */}
          </Box>
        </Box>
      ))}
    </Box>
  );
};

export default Log;
