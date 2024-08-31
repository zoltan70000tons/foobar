import React, { FC, ReactNode } from 'react';
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import { Box, Button, IconButton } from '@mui/material';
import SettingsIcon from '@mui/icons-material/Settings';
import EditIcon from '@mui/icons-material/Edit';
import VisibilityIcon from '@mui/icons-material/Visibility';
import DeleteIcon from '@mui/icons-material/Delete';

interface DataGridProps<T> {
    columns: GridColDef[];
    data: T[];
    isSSR?: boolean;
    onRowClick?: (row: T) => void;
    showAddButton?: boolean;
    onAddClick?: () => void;
    showActions?: boolean;
    showSettings?: boolean;
    showEdit?: boolean;
    showView?: boolean;
    showDelete?: boolean;
    onSettingsClick?: (row: T) => void;
    onEditClick?: (row: T) => void;
    onViewClick?: (row: T) => void;
    onDeleteClick?: (row: T) => void;
    addText?: string;
    addIcon?: ReactNode;
}

const MuiTable: FC<DataGridProps<any>> = ({
    columns,
    data,
    isSSR,
    onRowClick,
    showAddButton,
    onAddClick,
    showActions,
    showSettings,
    showEdit,
    showView,
    showDelete,
    onSettingsClick,
    onEditClick,
    onViewClick,
    onDeleteClick,
    addText = 'Add',
    addIcon
}) => {
    const handleRowClick = (params: any) => {
        if (onRowClick) {
            onRowClick(params.row);
        }
    };

    const actionColumn: GridColDef = {
        field: 'actions',
        headerName: 'Actions',
        width: 150,
        renderCell: (params) => (
            <Box sx={{ display: 'flex', justifyContent: 'space-between', width: '100%' }}>
                {showSettings && (
                    <IconButton onClick={() => onSettingsClick && onSettingsClick(params.row)}>
                        <SettingsIcon />
                    </IconButton>
                )}
                {showEdit && (
                    <IconButton onClick={() => onEditClick && onEditClick(params.row)}>
                        <EditIcon />
                    </IconButton>
                )}
                {showView && (
                    <IconButton onClick={() => onViewClick && onViewClick(params.row)}>
                        <VisibilityIcon />
                    </IconButton>
                )}
                {showDelete && (
                    <IconButton onClick={() => onDeleteClick && onDeleteClick(params.row)}>
                        <DeleteIcon />
                    </IconButton>
                )}
            </Box>
        )
    };

    return (
        <Box sx={{ width: '100%', height: '100%' }}>
            {showAddButton && (
                <Box sx={{ display: 'flex', justifyContent: 'flex-end', mb: 2 }}>
                    <Button variant="contained" color="primary" onClick={onAddClick} startIcon={addIcon}>
                        {addText}
                    </Button>
                </Box>
            )}
            <div style={{ height: '100%', width: '100%' }}>
                <DataGrid
                    autoHeight
                    sx={{ width: '100%', flexGrow: 1 }}
                    rows={data}
                    columns={showActions ? [...columns, actionColumn] : columns}
                    initialState={{
                        ...data.initialState,
                        pagination: { paginationModel: { pageSize: 10 } },
                    }}
                    pageSizeOptions={[5, 10, 25]}
                    onRowClick={handleRowClick}
                    disableSelectionOnClick
                />
            </div>
        </Box>
    );
};

export default MuiTable;
