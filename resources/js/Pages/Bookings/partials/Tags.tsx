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
import { router } from "@inertiajs/react";
import LoadingOverlay from "@/Components/LoadingOverlay";
import { Event } from "@/interfaces/Event";
import { Booking } from "@/types/booking";



const availableTags = Object.values(TagEnum).map((tag) => ({
    label: tag,
    value: tag,
}));

const Tags: React.FC<{ editable: boolean; event: Event; booking: Booking }> = ({ editable, event, booking }) => {
    const [tags, setTags] = useState<string[]>([]);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    console.log(booking.tags);
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
        const uniqueTags = [...new Set(newTags)];
        setTags(uniqueTags);
        setLoading(true);
        router.post(
            route("bookings.updateTags", { id: event.id }),
            {
                tags: uniqueTags,
                booking_id: booking.id,
            },
            {
                onSuccess: () => setLoading(false),
                onError: () => setLoading(false),
                onFinish: () => setLoading(false),
            }
        );
        setDialogOpen(false);
    };


    return (
        <Box>
            <Box display="flex" alignItems="center" gap={1}>
                <span>Tags:</span>
                {booking.tags.map((tag, index) => {
                    return (
                        <Chip
                            key={index}
                            label={tag.name}
                            style={{ backgroundColor: tag.color ?? "#e0e0e0", color: "#fff" }}
                        />
                    );
                })}
                <IconButton onClick={handleOpenDialog} disabled={!editable}>
                    <AddIcon />
                </IconButton>
            </Box>
            <Dialog open={dialogOpen} onClose={handleCloseDialog} fullWidth maxWidth="md">
                <DialogTitle>Select or Remove Tags</DialogTitle>
                <DialogContent>
                    <Autocomplete
                        multiple
                        options={availableTags.filter(
                            (tag) => !(tags || []).some((t) => t.id === tag.id)
                        )}
                        getOptionLabel={(option) => option.name}
                        value={availableTags.filter((tag) =>
                            (tags || []).some((t) => t.id === tag.id)
                        )} 
                        onChange={(_, value) => {
                            const newTags = value.map((item) => item.id);
                            handleSaveTags(newTags);
                        }}
                        renderInput={(params) => (
                            <TextField {...params} label="Tags" placeholder="Select or remove tags" />
                        )}
                        renderOption={(props, option) => (
                            <li {...props}>
                                <Chip
                                    label={option.name}
                                    style={{
                                        backgroundColor: option.color,
                                        color: "#fff",
                                        marginRight: 8,
                                    }}
                                    size="small"
                                />
                            </li>
                        )}
                        renderTags={(tagValue, getTagProps) =>
                            tagValue.map((option, index) => (
                                <Chip
                                    key={option.id}
                                    label={option.name}
                                    {...getTagProps({ index })}
                                    style={{ backgroundColor: option.color, color: "#fff" }}
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
