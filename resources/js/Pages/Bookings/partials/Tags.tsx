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


const availableTags = Object.values(BookingTagEnum).map((tag) => ({
    label: tag,
    value: tag,
}));

const Tags: React.FC<{ editable: boolean; event: any; booking: any }> = ({ editable, event, booking }) => {
    const [tags, setTags] = useState<string[]>([]);
    const [dialogOpen, setDialogOpen] = useState(false);


    useEffect(() => {
        if (Array.isArray(booking?.tags)) {
            setTags(booking.tags);
        } else {
            setTags([]); 
        }
    }, [booking]);

    const handleOpenDialog = () => {
        setDialogOpen(true);
    };

    const handleCloseDialog = () => {
        setDialogOpen(false);
    };

    const handleSaveTags = (newTags: string[]) => {
        setTags(newTags);
        router.post(route("bookings.updateTags", { id: event.id }), {
            tags: newTags,
            booking_id: booking.id,
        });
        setDialogOpen(false);
    };


    console.log(tags);
    return (
        <Box>
            <Box display="flex" alignItems="center" gap={1}>
                <span>Tags:</span>
                {tags && tags.length > 0 && tags.map((tag, index) => (
                    <Chip key={index} label={tag} />
                ))}
                <IconButton onClick={handleOpenDialog} disabled={!editable}>
                    <AddIcon />
                </IconButton>
            </Box>
            <Dialog open={dialogOpen} onClose={handleCloseDialog} fullWidth maxWidth="md">
                <DialogTitle>Select or Remove Tags</DialogTitle>
                <DialogContent>
                    <Autocomplete
                        multiple
                        options={availableTags}
                        getOptionLabel={(option) => option.label}
                        value={tags.map((tag) => ({ label: tag, value: tag }))}
                        onChange={(_, value) => {
                            const newTags = value.map((item) => item.value);
                            handleSaveTags(newTags);
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
