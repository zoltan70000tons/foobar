import React from "react";
import { Card, CardContent, Typography, Grid, Box } from "@mui/material";
import MuiTable from "@/Components/MuiTable";

interface Cabin {
  id: number;
  name: string;
  description: string;
  location: string;
}

interface AllTabContentProps {
  data: Cabin[] | undefined;
}

const AllTabContent: React.FC<AllTabContentProps> = ({ data }) => {
  if (!data || data.length === 0) {
    return (
      <Box p={3}>
        <Typography variant="h6" color="textSecondary">
          No cabins available.
        </Typography>
      </Box>
    );
  }
  

  return (
    <Grid container spacing={3}>
      {data.map((cabin) => (
        <Grid item xs={12} sm={6} md={4} key={cabin.id}>
        
        </Grid>
      ))}
    </Grid>
  );
};

export default AllTabContent;
