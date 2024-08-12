import React from 'react';
import { Paper, Typography, Box, IconButton, Badge, Grid } from '@mui/material';
import { ThemeProvider } from '@mui/material/styles';
import theme from '../../Theme/theme';

const DashboardCard = ({ title, description, Icon, link, badgeContent, onBadgeClick }) => {
  return (
    <ThemeProvider theme={theme}>
      <Paper 
        sx={{ 
          display: 'flex', 
          alignItems: 'center', 
          padding: '10px', 
          marginBottom: '10px',
          position: 'relative', // Necessary for positioning badge
          cursor: 'pointer' // Change cursor to pointer for click indication
        }}
        onClick={() => window.location.href = link} // Redirect on click
      >
        <Box sx={{ marginRight: '15px' }}>
          {Icon && <Icon fontSize="large" />}
        </Box>
        <Box sx={{ flex: '1 0 auto' }}>
          <Typography variant="h5" component="div">
            {title}
          </Typography>
          <Typography variant="body2" color="text.secondary">
            {description}
          </Typography>
        </Box>
        {badgeContent && (
          <Badge
            badgeContent={badgeContent}
            color="primary"
            sx={{ position: 'absolute', top: 10, right: 10 }}
          >
            <IconButton onClick={onBadgeClick} aria-label="notifications">
              {/* Optionally include a badge icon */}
            </IconButton>
          </Badge>
        )}
      </Paper>
    </ThemeProvider>
  );
};

export default DashboardCard;
