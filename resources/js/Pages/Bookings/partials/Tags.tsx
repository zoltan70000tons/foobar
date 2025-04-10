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
import { TagEnum, TagEnumStyles } from "@/enums/TagEnum";
import { router } from "@inertiajs/react";
import LoadingOverlay from "@/Components/LoadingOverlay";



const availableTags = Object.values(TagEnum).map((tag) => ({
    label: tag,
    value: tag,
}));

const Tags: React.FC<{ editable: boolean; event: any; booking: any }> = ({ editable, event, booking }) => {
    const [tags, setTags] = useState<string[]>([]);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [loading, setLoading] = useState(false);


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
                {tags && tags.length > 0 && tags.map((tag, index) => {
                    const tagStyle = TagEnumStyles[tag as TagEnum]; 
                    return (
                        <Chip
                            key={index}
                            label={tag}
                            style={{ backgroundColor: tagStyle?.color ?? "#e0e0e0", color: "#fff" }}
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
                        renderOption={(props, option) => {
                            const tagStyle = TagEnumStyles[option.value as TagEnum];
                            return (
                                <li {...props}>
                                    <Chip
                                        label={option.label}
                                        style={{
                                            backgroundColor: tagStyle?.color ?? "#e0e0e0",
                                            color: "#fff",
                                            marginRight: 8,
                                        }}
                                        size="small"
                                    />
                                </li>
                            );
                        }}
                        renderTags={(tagValue, getTagProps) =>
                            tagValue.map((option, index) => {
                                const tagStyle = TagEnumStyles[option.value as TagEnum];
                                return (
                                    <Chip
                                        key={index}
                                        label={option.label}
                                        {...getTagProps({ index })}
                                        style={{ backgroundColor: tagStyle?.color ?? "#e0e0e0", color: "#fff" }}
                                    />
                                );
                            })
                        }
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleCloseDialog} color="secondary">
                        Cancel
                    </Button>
                </DialogActions>
            </Dialog>
            {/* <LoadingOverlay open={loading}/> */}

        </Box>
    );
};

export default Tags;
