import React from 'react';
import { Alert } from '@mui/material';

const NoAccessAlert = ({ message }) => {
    return (
        <Alert severity="error">
            {message || "You do not have permission to access this section."}
        </Alert>
    );
};

export default NoAccessAlert;