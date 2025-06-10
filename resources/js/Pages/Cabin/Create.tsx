import React, { useEffect, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { TagEnum } from "@/enums/TagEnum";
import {
    CabinStatus,
    CabinStatusReduced,
    CabinStatusColor,
} from "@/enums/CabinStatus";
import { CabinType, CabinTypeIds } from "@/enums/CabinType";
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
    Autocomplete,
    Chip,
    FormControlLabel,
    Checkbox,
    ListItemIcon,
    Alert,
    FormHelperText

} from "@mui/material";
import {
    CheckCircle,
    Block,
    HourglassEmpty,
    Close,
    ArrowBack,
    Rule,
    Cabin,
} from "@mui/icons-material";



type Props = PageProps & {
    auth: any;
    cabin: any;
    event: any;
    categories: any[];
    tab: string;
    data: any;
};

const statusOptions = CabinStatus;

const Create = ({
    auth,
    event,
    categories
}: Props) => {

    const [selectedTags, setSelectedTags] = useState<string[]>(["NEW"]);
    const { data, setData, post, processing, errors } = useForm({
        cabin_type_id: '',
        cabin_category_id: '',
        //cabin_spec_id: '',
        inventory: 1,
        notes: '',
        tags: ["NEW"],
        status: 'RESERVED',
        internal_notes: '',
        cabin_number: '',
        deck: '',
        location: '',
        connects_with: '',
        accessible: false,
        balcony: false,
        obstructed_view: false,
        total_berths: '',
        lower_bed_type_1: '',
        lower_bed_type_2: '',
        upper_berths: '',
    });
    const cabinTypeEntries = Object.entries(CabinType) as [string, CabinType][];
    const statusIcons = {
        [CabinStatus.AVAILABLE]: (
            <CheckCircle
                fontSize="small"
                color={CabinStatusColor[CabinStatus.AVAILABLE]}
            />
        ),
        [CabinStatus.RESERVED]: (
            <HourglassEmpty
                fontSize="small"
                color={CabinStatusColor[CabinStatus.RESERVED]}
            />
        ),
        [CabinStatus.BOOKED]: (
            <Block fontSize="small" color={CabinStatusColor[CabinStatus.BOOKED]} />
        ),
        [CabinStatusReduced.CLOSED]: (
            <Close fontSize="small" color={CabinStatusColor[CabinStatus.CLOSED]} />
        ),
        [CabinStatus.PARTIALLY_BOOKED]: (
            <Rule
                fontSize="small"
                color={CabinStatusColor[CabinStatus.PARTIALLY_BOOKED]}
            />
        ),
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('cabins.store', { id: event.id }), {
            onSuccess: () => {
                // router.visit(route('cabins.index', { id: event.id }));
            },
        });
    };

    const handleTagsChange = (event: any, newValue: string[]) => {
        setSelectedTags(newValue);
        setData('tags', newValue);
    };

    const handleFeatureChange = (event: any) => {
        setData({ ...data, [event.target.name]: event.target.checked });
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
                        <h3>Create New Cabin</h3>
                        <form onSubmit={handleSubmit}>
                            <Grid container spacing={3}>
                                <Grid item xs={12} md={12}>
                                    <Alert severity="info" sx={{ mb: 2 }}>
                                        <Typography variant="body1">
                                            <strong>Note:</strong> This is a new cabin. Please fill in the details below.
                                        </Typography>
                                    </Alert>
                                </Grid>



                                <Grid item xs={12} sm={6}>
                                    <FormControl fullWidth>
                                        <InputLabel>Status</InputLabel>
                                        <Select
                                            value={data.status}
                                            label="Status"
                                            onChange={(e) => setData('status', e.target.value)}
                                            error={!!errors.status}
                                            renderValue={(selected) => (
                                                <Box
                                                    sx={{
                                                        display: "flex",
                                                        alignItems: "center",
                                                        gap: 1,
                                                    }}
                                                >
                                                    <ListItemIcon
                                                        sx={{
                                                            minWidth: "auto",
                                                            display: "flex",
                                                            alignItems: "center",
                                                        }}
                                                    >
                                                        {statusIcons[selected as keyof typeof CabinStatus]}
                                                    </ListItemIcon>
                                                    {CabinStatus[selected as keyof typeof CabinStatus]}
                                                </Box>
                                            )}
                                        >
                                            {Object.keys(statusOptions).map((status) => (
                                                <MenuItem
                                                    key={status}
                                                    value={status}
                                                    sx={{
                                                        display: "flex",
                                                        alignItems: "center",
                                                        gap: 1,
                                                    }}
                                                >
                                                    <ListItemIcon
                                                        sx={{
                                                            minWidth: "auto",
                                                            marginRight: 1,
                                                            display: "flex",
                                                            alignItems: "center",
                                                        }}
                                                    >
                                                        {statusIcons[status as keyof typeof CabinStatus]}
                                                    </ListItemIcon>
                                                    {CabinStatus[status as keyof typeof CabinStatus]}
                                                </MenuItem>
                                            ))}
                                        </Select>
                                    </FormControl>
                                </Grid>


                                {/* Cabin Category */}
                                <Grid item xs={12} sm={6}>
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



                                <Grid item xs={12} sm={4}>
                                    <FormControl fullWidth error={!!errors.cabin_type_id}>
                                        <InputLabel id="cabin-type-label">Cabin Type</InputLabel>
                                        <Select
                                            labelId="cabin-type-label"
                                            value={data.cabin_type_id}
                                            label="Cabin Type"
                                            onChange={(e) => setData('cabin_type_id', Number(e.target.value))}
                                        >
                                            {cabinTypeEntries.map(([key, label]) => (
                                                <MenuItem key={key} value={CabinTypeIds[label]}>
                                                    {label}
                                                </MenuItem>
                                            ))}
                                        </Select>
                                    </FormControl>
                                </Grid>


                                {/* Inventory */}
                                <Grid item xs={12} sm={4}>
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

                                {/* Cabin Number */}
                                <Grid item xs={12} md={4}>
                                    <Box sx={{ mb: 2 }} >
                                        <TextField
                                            name="cabin_number"
                                            label="Cabin Number"
                                            variant="outlined"
                                            fullWidth
                                            value={data.cabin_number}
                                            onChange={(e) => setData('cabin_number', e.target.value)}
                                            error={Boolean(errors.cabin_number)}
                                            helperText={errors.cabin_number}
                                            InputProps={{
                                                // readOnly: !canEditFull,
                                            }}
                                        />
                                    </Box>
                                </Grid>

                                {/* Deck */}
                                <Grid item xs={12} md={2}>
                                    <Box sx={{ mb: 2 }}>
                                        <TextField
                                            InputProps={{
                                                // readOnly: !canEditFull,
                                            }}
                                            name="deck"
                                            label="Deck"
                                            variant="outlined"
                                            fullWidth
                                            value={data.deck}
                                            onChange={(e) => setData('deck', e.target.value)}
                                            error={Boolean(errors.deck)}
                                            helperText={errors.deck}
                                        />
                                    </Box>
                                </Grid>

                                {/* Location */}
                                <Grid item xs={12} md={4}>
                                    <Box sx={{ mb: 2 }}>
                                        <FormControl
                                            fullWidth
                                            variant="outlined"
                                            error={!!errors.location}
                                        >
                                            <InputLabel>Location</InputLabel>
                                            <Select
                                                // readOnly={!canEditFull}
                                                value={data.location}
                                                onChange={(e) => setData('location', e.target.value)}
                                                label="Location"
                                            >
                                                <MenuItem value="FW">Forward</MenuItem>
                                                <MenuItem value="MS">Midship</MenuItem>
                                                <MenuItem value="AF">Aft</MenuItem>
                                            </Select>
                                            {errors.location && ( 
                                                <FormHelperText>{errors.location}</FormHelperText>
                                            )}
                                        </FormControl>
                                    </Box>
                                </Grid>

                                {/* Connect With */}
                                <Grid item xs={12} md={2}>
                                    <Box sx={{ mb: 2 }}>
                                        <TextField
                                            InputProps={{
                                                // readOnly: !canEditFull,
                                            }}
                                            name="connects_with"
                                            label="Connects With"
                                            variant="outlined"
                                            fullWidth
                                            value={data.connectWith}
                                            onChange={(e) => setData('connects_with', e.target.value)}
                                        // error={Boolean(errors.connects_with)}
                                        //helperText={errors.connects_with}
                                        />
                                    </Box>
                                </Grid>


                                {/* Features */}
                                <Grid item xs={12}>
                                    <Box sx={{ mb: 2 }}>
                                        <Typography variant="h6">Features</Typography>
                                        <FormControlLabel
                                            control={
                                                <Checkbox
                                                    checked={data.accessible}
                                                    onChange={handleFeatureChange}
                                                    name="accessible"
                                                //disabled={!canEditFull}
                                                />
                                            }
                                            label="Accessible"
                                        />
                                        <FormControlLabel
                                            control={
                                                <Checkbox
                                                    checked={data.balcony}
                                                    onChange={handleFeatureChange}
                                                    name="balcony"
                                                // disabled={!canEditFull}
                                                />
                                            }
                                            label="Balcony"
                                        />
                                        <FormControlLabel
                                            control={
                                                <Checkbox
                                                    checked={data.obstructed_view}
                                                    onChange={handleFeatureChange}
                                                    name="obstructed_view"
                                                //  disabled={!canEditFull}
                                                />
                                            }
                                            label="Obstructed View"
                                        />
                                    </Box>
                                </Grid>

                                {/* Total Berths */}
                                <Grid item xs={12} md={2}>
                                    <Box sx={{ mb: 2 }}>
                                        <TextField
                                            InputProps={{
                                                //  readOnly: !canEdit,
                                            }}
                                            name="total_berths"
                                            label="Total Berths"
                                            variant="outlined"
                                            fullWidth
                                            value={data.total_berths}
                                            onChange={(e) => setData('total_berths', e.target.value)}
                                        //  error={Boolean(errors.total_berths)}
                                        //  helperText={errors.total_berths}
                                        />
                                    </Box>
                                </Grid>

                                {/* Lower Bed Type 1 */}
                                <Grid item xs={12} md={2}>
                                    <Box sx={{ mb: 2 }}>
                                        <TextField
                                            InputProps={{
                                                // readOnly: !canEditFull,
                                            }}
                                            name="lower_bed_type_1"
                                            label="Lower Bed Type 1"
                                            variant="outlined"
                                            fullWidth
                                            value={data.lower_bed_type_1}
                                            // onChange={(e) => setLowerBedType1(e.target.value)}
                                            onChange={(e) => setData('lower_bed_type_1', e.target.value)}
                                        // error={Boolean(errors.lower_bed_type_1)}
                                        //  helperText={errors.lower_bed_type_1}
                                        />
                                    </Box>
                                </Grid>

                                {/* Lower Bed Type 2 */}
                                <Grid item xs={12} md={2}>
                                    <Box sx={{ mb: 2 }}>
                                        <TextField
                                            InputProps={{
                                                //  readOnly: !canEditFull,
                                            }}
                                            name="lower_bed_type_2"
                                            label="Lower Bed Type 2"
                                            variant="outlined"
                                            fullWidth
                                            value={data.lower_bed_type_2}
                                            onChange={(e) => setData('lower_bed_type_2', e.target.value)}
                                        //error={Boolean(errors.lower_bed_type_2)}
                                        //helperText={errors.lower_bed_type_2}
                                        />
                                    </Box>
                                </Grid>

                                {/* Upper Berths */}
                                <Grid item xs={12} md={2}>
                                    <Box sx={{ mb: 2 }}>
                                        <TextField
                                            InputProps={{
                                                // readOnly: !canEditFull,
                                            }}
                                            name="upper_berths"
                                            label="Upper Berths"
                                            variant="outlined"
                                            fullWidth
                                            value={data.upper_berths}
                                            //onChange={(e) => setUpperBerths(e.target.value)}
                                            onChange={(e) => setData('upper_berths', e.target.value)}
                                        //error={Boolean(errors.upper_berths)}
                                        //helperText={errors.upper_berths}
                                        />
                                    </Box>
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
                                        rows={4}
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
                                        multiline
                                        rows={4}
                                    />
                                </Grid>

                                <Grid item xs={12}>
                                    <Box display="flex" justifyContent="flex-end">
                                        <Button type="submit" variant="outlined" disabled={processing}>
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
