import React, { useState, useEffect } from "react";
import {
    Box,
    Chip,
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    Button,
    TextField,
    IconButton,
} from "@mui/material";
import { Autocomplete } from "@mui/lab";
import AddIcon from "@mui/icons-material/Add";
import { BookingTagEnum } from "@/enums/TagEnum";
import { router } from "@inertiajs/react";

// Tags disponibles
const availableTags = Object.values(BookingTagEnum).map((tag) => ({
    label: tag,
    value: tag,
}));

const Tags: React.FC<{ editable: boolean; event: any; booking: any }> = ({ editable, event, booking }) => {
    const [tags, setTags] = useState<string[]>([]); // Tags actuales del booking
    const [dialogOpen, setDialogOpen] = useState(false);

    // Inicializar con las tags del booking
    useEffect(() => {
        if (booking?.tags) {
            setTags(booking.tags); // Sincroniza el estado inicial con booking.tags
        }
    }, [booking]);

    const handleOpenDialog = () => {
        setDialogOpen(true);
    };

    const handleCloseDialog = () => {
        setDialogOpen(false);
    };

    const handleSaveTags = (newTags: string[]) => {
        setTags(newTags); // Actualiza el estado de tags
        router.post(route("bookings.updateTags", { id: event.id }), {
            tags: newTags,
            booking_id: booking.id,
        });
        setDialogOpen(false); // Cierra el diálogo
    };

    return (
        <Box>
            {/* Chips de tags actuales */}
            <Box display="flex" alignItems="center" gap={1}>
                <span>Tags:</span>
                {tags.map((tag, index) => (
                    <Chip key={index} label={tag} />
                ))}
                <IconButton onClick={handleOpenDialog} disabled={!editable}>
                    <AddIcon />
                </IconButton>
            </Box>

            {/* Diálogo para seleccionar y eliminar tags */}
            <Dialog open={dialogOpen} onClose={handleCloseDialog} fullWidth maxWidth="md">
                <DialogTitle>Select or Remove Tags</DialogTitle>
                <DialogContent>
                    <Autocomplete
                        multiple
                        options={availableTags} // Todas las opciones disponibles
                        getOptionLabel={(option) => option.label}
                        value={tags.map((tag) => ({ label: tag, value: tag }))} // Tags actuales
                        onChange={(_, value) => {
                            // Actualizar las tags seleccionadas
                            const newTags = value.map((item) => item.value);
                            handleSaveTags(newTags); // Guardar cambios
                        }}
                        renderInput={(params) => (
                            <TextField {...params} label="Tags" placeholder="Select or remove tags" />
                        )}
                        renderTags={(tagValue, getTagProps) =>
                            tagValue.map((option, index) => (
                                <Chip
                                    key={index}
                                    label={option.label}
                                    {...getTagProps({ index })}
                                />
                            ))
                        }
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleCloseDialog} color="secondary">
                        Cancel
                    </Button>
                </DialogActions>
            </Dialog>
        </Box>
    );
};

export default Tags;
