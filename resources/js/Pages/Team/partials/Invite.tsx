import React, { useState, useEffect } from 'react';
import { TextField, Button, Box, Select, MenuItem, InputLabel, FormControl, Grid } from '@mui/material';
import { useForm, Head, Link } from '@inertiajs/react';
import axios from 'axios';

export default function Invite() {
  const baseURL = "http://localhost:8000/api/organization/roles";
  const { data, setData, post, errors } = useForm({
    email: '',
    role: '',
  });

  const [roles, setRoles] = useState([]);

  useEffect(() => {
    axios.get(baseURL,{ params: { id: '1' } })  // Todo change to actual organization
      .then(response => {
        setRoles(response.data.data);  
      })
      .catch(error => {
        console.error('Error fetching roles:', error);
      });
  }, []);

  const handleSubmit = (e) => {
    e.preventDefault();
    post(route('invitations.store')); 
  };

  return (
    <Box component="form" onSubmit={handleSubmit} sx={{ mt: 3 }}>
      <Grid container spacing={2} alignItems="center">
        <Grid item xs={4}>
          <TextField
            fullWidth
            size="small"
            label="Email"
            type="email"
            name="email"
            value={data.email}
            onChange={(e) => setData('email', e.target.value)}
            error={errors.email ? true : false}
            helperText={errors.email}
            margin="normal"
          />
        </Grid>
        <Grid item xs={4}>
          <FormControl fullWidth margin="normal">
            <InputLabel id="role-label">Role</InputLabel>
            <Select
              size="small"
              fullWidth
              labelId="role-label"
              id="role"
              value={data.role}
              label="Role"
              onChange={(e) => setData('role', e.target.value)}
              error={errors.role ? true : false}
            >
              <MenuItem value="">
                <em>None</em>
              </MenuItem>
              {roles.map((role) => (
                <MenuItem key={role.id} value={role.name}>{role.name}</MenuItem>
              ))}
            </Select>
          </FormControl>
        </Grid>
        <Grid item xs={4} margin="normal">
          <Button type="submit" variant="outlined" color="primary" size="small">
            Send
          </Button>
        </Grid>
      </Grid>
    </Box>
  );
}
