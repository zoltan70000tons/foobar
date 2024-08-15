import React from 'react';
import { Box, Button, ButtonGroup, Link } from '@mui/material';
import { usePermissions } from '@/Providers/PermissionContext';

const NavigationTeam = ({ buttonsConfig }) => {
    const { hasPermission } = usePermissions();
    const currentPath = window.location.pathname.split('/').filter(Boolean).pop();

    return (
        <>
            <Box>
                <ButtonGroup style={{ marginBottom: '1rem' }} disableElevation size="small" aria-label="Small button group" variant="outlined">
                    {buttonsConfig.map(({ href, label, variant, requiredPermission }) => {
                        const isActive = currentPath === href.split('/').filter(Boolean).pop();

                        return hasPermission(requiredPermission) && (
                            <Link href={href} key={label} style={{ textDecoration: 'none' }}>
                                <Button variant={isActive ? 'contained' : variant}>
                                    {label}
                                </Button>
                            </Link>
                        );
                    })}
                </ButtonGroup>
            </Box>
        </>
    );
};

export default NavigationTeam;