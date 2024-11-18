import React, { useState } from "react";
import { 
    Box, 
    Chip, 
    Dialog, 
    DialogTitle, 
    DialogContent, 
    DialogActions, 
    Button, 
    TextField, 
    IconButton 
} from "@mui/material";
import { Autocomplete } from "@mui/lab";
import AddIcon from "@mui/icons-material/Add";

// Enum de tags
export enum BookingTagEnum {
    NOT_ASSIGNED = "NOT ASSIGNED",
    NEW = "NEW",
    OVERDUE = "OVERDUE",
    MISSING_INFO = "MISSING INFO",
    PAID = "PAID",
    IN_MANIFEST = "IN MANIFEST",
}

const availableTags = Object.values(BookingTagEnum).map((tag) => ({
    label: tag,
    value: tag,
}));

const Tags: React.FC = ({editable}) => {
    const [tags, setTags] = useState<string[]>([]); 
    const [selectedTags, setSelectedTags] = useState<string[]>([]); 
    const [dialogOpen, setDialogOpen] = useState(false);

    const handleOpenDialog = () => {
        setDialogOpen(true);
    };

    const handleCloseDialog = () => {
        setDialogOpen(false);
    };

    const handleSaveTags = () => {
        setTags(selectedTags); 
        setDialogOpen(false); 
    };

    return (
        <Box>

            <Box display="flex" alignItems="center" gap={1}>
                <span>Tags:</span>
                {tags.map((tag, index) => (
                    <Chip key={index} label={tag} />
                ))}
                <IconButton onClick={handleOpenDialog} disabled={editable}>
                    <AddIcon />
                </IconButton>
            </Box>

            <Dialog open={dialogOpen} onClose={handleCloseDialog} fullWidth maxWidth="md">
                <DialogTitle>Select Tags</DialogTitle>
                <DialogContent>
                    <Autocomplete
                        multiple
                        options={availableTags}
                        getOptionLabel={(option) => option.label}
                        value={selectedTags.map((tag) => ({ label: tag, value: tag }))}
                        onChange={(_, value) => setSelectedTags(value.map((item) => item.value))}
                        renderInput={(params) => (
                            <TextField {...params} label="Tags" placeholder="Select tags" />
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
                    <Button onClick={handleSaveTags} color="primary">
                        Save
                    </Button>
                </DialogActions>
            </Dialog>
        </Box>
    );
};

export default Tags;
