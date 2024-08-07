import React from 'react';
import { Card, CardContent, CardActions, Typography, IconButton, Box } from '@mui/material';
import TeamIcon from '@mui/icons-material/Group'; 
import Button from '@mui/material/Button';

const TeamCard = () => {
  return (
    <Card sx={{ maxWidth: 345 }}>
      <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', padding: 2 }}>
        <TeamIcon sx={{ fontSize: 40 }} />
      </Box>
      <CardContent>
        <Typography gutterBottom variant="h5" component="div">
          Team
        </Typography>
        <Typography variant="body2" color="text.secondary">
          This card contains information about your team. You can manage team members, roles, and permissions from here.
        </Typography>
      </CardContent>
      <CardActions>
        <Button size="small" color="primary">
          Learn More
        </Button>
        <Button size="small" color="primary">
          Manage Team
        </Button>
      </CardActions>
    </Card>
  );
};

export default TeamCard;