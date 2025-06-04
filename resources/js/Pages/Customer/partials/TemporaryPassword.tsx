import { useState } from 'react';
import { Button, Box, Typography } from '@mui/material';
import axios from "axios";

type Props = {
  customer: any;
}


export default function TemporaryPassword({customer}: Props) {

  const customerId = customer.id;

  const [temporaryPassword, setTemporaryPassword] = useState(null);
  const [generatedPassword, setGeneratedPassword] = useState(null);
  const [loading, setLoading] = useState(false);


  const generateTemporaryPassword = async () => {
    setLoading(true);

    axios.post(route('customers.temporaryPassword.create', { user: customerId }), {
        customer_id: customerId
    })
      .then(response => {
        setTemporaryPassword(response.data.raw_password);
        setGeneratedPassword(response.data.raw_password);
        setLoading(false);
      })
      .catch(error => {
        console.error("Error generating temporary password:", error);
        setLoading(false);
      });
  };

  const deleteTemporaryPassword = async () => {
    setLoading(true);

    axios.delete(route('customers.temporaryPassword.delete', { user: customerId }))
      .then(response => {
        setTemporaryPassword(null);
        setGeneratedPassword(null);
        setLoading(false);
      })
      .catch(error => {
        console.error("Error deleting temporary password:", error);
        setLoading(false);
      });
  }

  return (
    <Box sx={{ p: 2 }}>
      <Typography variant="h6" gutterBottom>
        Temporary Password
      </Typography>
      {generatedPassword && (
        <Box sx={{
          mb: 2,
          p: 2,
          border: '1px solid #ccc',
          borderRadius: '4px',
        
        }}>
          <Typography variant="body1">
            Generated Temporary Password: <strong>{generatedPassword}</strong>
          </Typography>
        </Box>
      )}
      {!temporaryPassword ? (
        <Button 
          variant="contained"
          disabled={loading}
          color="primary"
          onClick={generateTemporaryPassword}
          sx={{ mb: 2 }}
        >
          Generate Temporary Password
        </Button>
      ) : (
        <Button 
          disabled={loading}
          variant="outlined"
          color="secondary"
          onClick={deleteTemporaryPassword}
          sx={{ mb: 2, ml: 2 }}
        >
          Delete Temporary Password
        </Button>
      )}
    </Box>
  )
}
