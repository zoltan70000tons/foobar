import React, { useState } from "react";
import { Box, Typography, Select, MenuItem, Button, Paper, IconButton } from "@mui/material";
import VisibilityIcon from "@mui/icons-material/Visibility";

const ActionItem = ({ content, onDelete, onSend }) => (
  <Box display="flex" alignItems="center" mb={2}>
    <Box mr={2} fontSize="1.5rem">
      •
    </Box>
    <Box flex={1}>
      <Typography variant="subtitle1" mb={1}>
        Email "some-template" was sent
      </Typography>
      <Paper variant="outlined" sx={{ p: 2, bgcolor: "#3a3a3a" }}>
        <Typography variant="body2" color="white">
          {content}
        </Typography>
      </Paper>
    </Box>
    <Box display="flex" alignItems="center" ml={2}>
      <IconButton color="success">
        <VisibilityIcon />
      </IconButton>
      <Button variant="contained" color="error" onClick={onDelete} sx={{ ml: 1 }}>
        DELETE
      </Button>
      <Button variant="contained" color="primary" onClick={onSend} sx={{ ml: 1 }}>
        SEND
      </Button>
    </Box>
  </Box>
);

const ActionList = () => {
  const [type, setType] = useState("Email - Booking request");
  const handleAdd = () => {
    console.log("Add action");
  };

  return (
    <Box>
      <Typography variant="h5" mb={2}>
        Action
      </Typography>

      <Box display="flex" alignItems="center" mb={4}>
        <Select
          value={type}
          onChange={(e) => setType(e.target.value)}
          variant="outlined"
          sx={{
            bgcolor: "white",
            color: "black",
            borderRadius: 1,
            mr: 2,
          }}
        >
          <MenuItem value="Email - Booking request">Email - Booking request</MenuItem>
          <MenuItem value="Email - Confirmation">Email - Confirmation</MenuItem>
          <MenuItem value="SMS - Alert">SMS - Alert</MenuItem>
        </Select>
        <Button variant="contained" color="primary" onClick={handleAdd} sx={{ height: "100%" }}>
          ADD
        </Button>
      </Box>

      <Box>
        {/* Lista de Acciones */}
        <ActionItem
          content="Hello John Doe. Lorem Ipsum Dolor Sit Amet, Consectetur Adipiscing Elit..."
          onDelete={() => console.log("Delete")}
          onSend={() => console.log("Send")}
        />
        <ActionItem
          content="Hello John Doe. Lorem Ipsum Dolor Sit Amet, Consectetur Adipiscing Elit..."
          onDelete={() => console.log("Delete")}
          onSend={() => console.log("Send")}
        />
        <ActionItem
          content="Hello John Doe. Lorem Ipsum Dolor Sit Amet, Consectetur Adipiscing Elit..."
          onDelete={() => console.log("Delete")}
          onSend={() => console.log("Send")}
        />
      </Box>
    </Box>
  );
};

export default ActionList;
