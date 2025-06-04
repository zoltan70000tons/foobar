import React, { useEffect, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { TagEnum } from "@/enums/TagEnum";
import {
    CabinStatus,
    CabinStatusReduced,
    CabinStatusColor,
} from "@/enums/CabinStatus";
import { CabinType } from "@/enums/CabinType";
import { PageProps } from "@/types";
import { Head, router, usePage, useForm } from "@inertiajs/react";
import {
    Box,
    Container,
    FormControl,
    Grid,
    IconButton,
    InputLabel,
    TextField,
    Toolbar,
    Typography,
    Button, MenuItem, Select,
    Autocomplete

} from "@mui/material";
import {
    CheckCircle,
    Block,
    HourglassEmpty,
    Close,
    ArrowBack,
    Rule,
} from "@mui/icons-material";


type Props = PageProps & {
    auth: any;
    cabin: any;
    event: any;
    categories: any[];
    tab: string;
    data: any;
};

const statusOptions = ['AVAILABLE', 'RESERVED', 'BOOKED', 'PARTIALLY_BOOKED', 'CLOSED'];

const Create = ({
    auth,
    event,
    categories
}: Props) => {

    console.log('categories', categories);
    const { data, setData, post, processing, errors } = useForm({
        cabin_type_id: '',
        cabin_category_id: '',
        cabin_spec_id: '',
        inventory: 1,
        notes: '',
        tags: '["NOT ASSIGNED"]',
        status: 'RESERVED',
        internal_notes: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/cabins');
    };

    return (
        <AuthenticatedLayout user={auth.user} header={"Create Cabin"}>
            <Head title="Create Cabin" />
            <Toolbar sx={{ mt: 8 }}>
                <IconButton edge="start" color="inherit" aria-label="menu">
                    <img src={event.image} alt="Logo" style={{ height: 40 }} />
                </IconButton>
                <Typography variant="h6" style={{ flexGrow: 1 }}>
                    {event.name}
                </Typography>
            </Toolbar>
            <Container maxWidth="lg" sx={{ mb: 4 }}>
                <Grid container spacing={3}>
                    <Grid item xs={12}>
                        <form onSubmit={handleSubmit}>
                            <Grid container spacing={3}>
                                <Grid item xs={12} sm={4}>
                                    <FormControl fullWidth>
                                        <InputLabel>Cabin Type</InputLabel>
                                        <Select
                                            value={data.cabin_type_id}
                                            label="Cabin Type"
                                            onChange={(e) => setData('cabin_type_id', e.target.value)}
                                            error={!!errors.cabin_type_id}
                                        >
                                            {Object.entries(CabinType).map(([key, label]) => (
                                                <MenuItem key={key} value={key}>
                                                    {label}
                                                </MenuItem>
                                            ))}
                                        </Select>
                                    </FormControl>
                                </Grid>

                                <Grid item xs={12} sm={4}>
                                    <Autocomplete
                                        options={categories}
                                        getOptionLabel={(option) => option.title}
                                        isOptionEqualToValue={(option, value) => option.id === value.id}
                                        value={categories.find((cat) => cat.id === data.cabin_category_id) || null}
                                        onChange={(event, newValue) =>
                                            setData('cabin_category_id', newValue ? newValue.id : '')
                                        }
                                        renderInput={(params) => (
                                            <TextField
                                                {...params}
                                                label="Cabin Category"
                                                error={!!errors.cabin_category_id}
                                                helperText={errors.cabin_category_id}
                                            />
                                        )}
                                    />
                                </Grid>


                                <Grid item xs={12} sm={6}>
                                    <TextField
                                        fullWidth
                                        type="number"
                                        label="Inventory"
                                        value={data.inventory}
                                        onChange={(e) => setData('inventory', parseInt(e.target.value))}
                                        error={!!errors.inventory}
                                        helperText={errors.inventory}
                                    />
                                </Grid>

                                <Grid item xs={12} sm={6}>
                                    <FormControl fullWidth>
                                        <InputLabel>Status</InputLabel>
                                        <Select
                                            value={data.status}
                                            label="Status"
                                            onChange={(e) => setData('status', e.target.value)}
                                            error={!!errors.status}
                                        >
                                            {statusOptions.map((status) => (
                                                <MenuItem key={status} value={status}>
                                                    {status}
                                                </MenuItem>
                                            ))}
                                        </Select>
                                    </FormControl>
                                </Grid>

                                <Grid item xs={12}>
                                    <TextField
                                        fullWidth
                                        label="Notes"
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                        error={!!errors.notes}
                                        helperText={errors.notes}
                                        multiline
                                        rows={2}
                                    />
                                </Grid>

                                <Grid item xs={12}>
                                    <TextField
                                        fullWidth
                                        label="Tags (JSON)"
                                        value={data.tags}
                                        onChange={(e) => setData('tags', e.target.value)}
                                        error={!!errors.tags}
                                        helperText={errors.tags}
                                    />
                                </Grid>

                                <Grid item xs={12}>
                                    <TextField
                                        fullWidth
                                        label="Internal Notes"
                                        value={data.internal_notes}
                                        onChange={(e) => setData('internal_notes', e.target.value)}
                                        error={!!errors.internal_notes}
                                        helperText={errors.internal_notes}
                                    />
                                </Grid>

                                <Grid item xs={12}>
                                    <Box display="flex" justifyContent="flex-end">
                                        <Button variant="outlined" disabled={processing}>
                                            Save Cabin
                                        </Button>
                                    </Box>
                                </Grid>
                            </Grid>
                        </form>
                    </Grid>
                </Grid>
            </Container>
        </AuthenticatedLayout>
    );
};

export default Create;
