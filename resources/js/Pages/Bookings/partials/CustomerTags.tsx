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

type Tag = {
    id: number;
    color: string;
    description: string;
    name: string;
}

type Customer = {
    id: number;
    tags: Tag[];
}

const Tags: React.FC<{ customer: Customer; availableTags: Tag[] }> = ({ customer, availableTags }) => {
    const [tags, setTags] = useState<Tag[]>([]);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (Array.isArray(customer.tags)) {
            setTags(customer.tags);
        } else {
            setTags([]);
        }
    }, [customer]);

    const handleOpenDialog = () => {
        setDialogOpen(true);
    };

    const handleCloseDialog = () => {
        setDialogOpen(false);
    };

    const handleSaveTags = (newTags: Tag[]) => {
        const uniqueTags = [...new Set(newTags)];
        setTags(uniqueTags);
        setLoading(true);
        router.post(
            route("customers.updateTags", { user: customer.id }),
            {
                tags: uniqueTags,
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
                    return (
                        <Chip
                            key={index}
                            label={tag.name}
                            style={{ backgroundColor: tag?.color ?? "#e0e0e0", color: "#fff" }}
                        />
                    );
                })}
                <IconButton onClick={handleOpenDialog}>
                    <AddIcon />
                </IconButton>
            </Box>
            <Dialog open={dialogOpen} onClose={handleCloseDialog} fullWidth maxWidth="md">
                <DialogTitle>Select or Remove Tags</DialogTitle>
                <DialogContent>
                    <Autocomplete
                        multiple
                        options={availableTags}
                        getOptionLabel={(option) => option.name}
                        value={tags.map((tag) => ({ name: tag.name, description: tag.description, id: tag.id, color: tag.color }))}
                        onChange={(_, value) => {
                            const newTags = value.map((item) => item.id);
                            handleSaveTags(newTags);
                        }}
                        renderInput={(params) => (
                            <TextField {...params} label="Tags" placeholder="Select or remove tags" />
                        )}
                        renderOption={(props, option) => {
                            return (
                                <li {...props} key={option.id}>
                                    <Chip
                                        label={option.name}
                                        style={{
                                            backgroundColor: option?.color ?? "#e0e0e0",
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
                                const idx = option.id;
                                return (
                                    <Chip
                                        label={option.name}
                                        {...getTagProps({ idx })}
                                        key={idx}
                                        style={{ backgroundColor: option?.color ?? "#e0e0e0", color: "#fff" }}
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
            <LoadingOverlay open={loading}/>
        </Box>
    );
};

export default Tags;
