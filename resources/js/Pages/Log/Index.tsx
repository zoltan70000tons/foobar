import React, { useMemo, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, usePage, router } from "@inertiajs/react";
import dayjs from "dayjs";
import "dayjs/locale/en";
import localizedFormat from "dayjs/plugin/localizedFormat";
import {
    Button,
    Card,
    CardContent,
    CircularProgress,
    Container,
    Grid,
    MenuItem,
    Select,
    TextField,
    Toolbar,
    Typography,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableRow,
    TableContainer,
    TablePagination,
    Stack,
    Chip,
    InputLabel,
    FormControl,
} from "@mui/material";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from "@/Providers/PermissionContext";

dayjs.extend(localizedFormat);

type LogRow = {
    id: string;
    created_at: string;
    actor_type: "agent" | "system";
    actor_id?: string | null;
    action: string;
    description: string;
    related_type: "booking" | "customer" | "cabin";
    related_id: string;
    event_id?: number | null;
    booking_code?: string | null;
    actor_username?: string | null;
};

type InertiaPagination<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    next_page_url?: string | null;
    prev_page_url?: string | null;
    links: { url: string | null; label: string; active: boolean }[];
};

type PagePropsEx = {
    auth: { user: any };
    logs: InertiaPagination<LogRow>;
    filters: { type?: string | null; action?: string | null; from?: string | null; to?: string | null };
    actionsByType: Record<string, string[]>;
};

const TYPE_OPTIONS = [
    { value: "", label: "All" },
    { value: "booking", label: "Booking" },
    { value: "customer", label: "Customer" },
    { value: "cabin", label: "Cabin" },
    { value: "user", label: "User" },
    { value: "event", label: "Event" },
];

const Index: React.FC = () => {
    const { hasPermission } = usePermissions();
    const { props } = usePage<PagePropsEx>();
    const { auth, logs, filters, actionsByType } = props;

    const [type, setType] = useState<string>(filters.type ?? "");
    const [action, setAction] = useState<string>(filters.action ?? "");
    const [from, setFrom] = useState<string>(filters.from ?? "");
    const [to, setTo] = useState<string>(filters.to ?? "");
    const [loading, setLoading] = useState(false);

    const actionsForSelectedType = useMemo(() => {
        if (!type) return [];
        return actionsByType?.[type] ?? [];
    }, [type, actionsByType]);

    const handleTypeChange = (v: string) => {
        setType(v);
        setAction("");
    };

    const submitFilters = (page?: number) => {
        setLoading(true);
        const payload = {
            ...(type ? { type } : {}),
            ...(action ? { action } : {}),
            ...(from ? { from } : {}),
            ...(to ? { to } : {}),
            ...(page ? { page } : {}),
        };

        router.post(route("logs.index"), payload, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const clearFilters = () => {
        setType("");
        setAction("");
        setFrom("");
        setTo("");
        submitFilters(1);
    };


    const handleChangePage = (_: unknown, newPageIndex: number) => {
        submitFilters(newPageIndex + 1);
    };

    const handleChangeRowsPerPage = (e: React.ChangeEvent<HTMLInputElement>) => {
        const per = Number(e.target.value);
        setLoading(true);
        router.post(route("logs.index"), {
            ...(type ? { type } : {}),
            ...(action ? { action } : {}),
            ...(from ? { from } : {}),
            ...(to ? { to } : {}),
            per_page: per,
            page: 1,
        }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const currentPageZeroBased = (logs.current_page ?? 1) - 1;

    return (
        <AuthenticatedLayout user={auth.user} header={"Logs"}>
            <Head title="Logs" />
            <Toolbar />
            <Container maxWidth="lg" sx={{ mt: 4, mb: 6 }}>
                {!hasPermission(Permissions.ViewLogs) ? (
                    <Card>
                        <CardContent>
                            <Typography variant="h6">You do not have permission to view the Global Logs.</Typography>
                        </CardContent>
                    </Card>
                ) : (
                    <Grid container spacing={3}>
                        {/* Filters */}
                        <Grid item xs={12}>
                            <Card>
                                <CardContent>
                                    <Typography variant="h6" gutterBottom>Filters</Typography>
                                    <Grid container spacing={2} alignItems="center">
                                        <Grid item xs={12} sm={6} md={3}>
                                            <TextField
                                                label="From"
                                                type="date"
                                                value={from}
                                                onChange={(e) => setFrom(e.target.value)}
                                                fullWidth
                                                InputLabelProps={{ shrink: true }}
                                            />
                                        </Grid>
                                        <Grid item xs={12} sm={6} md={3}>
                                            <TextField
                                                label="To"
                                                type="date"
                                                value={to}
                                                onChange={(e) => setTo(e.target.value)}
                                                fullWidth
                                                InputLabelProps={{ shrink: true }}
                                            />
                                        </Grid>
                                        <Grid item xs={12} sm={6} md={3}>
                                            <FormControl fullWidth>
                                                <InputLabel id="type-label">Type</InputLabel>
                                                <Select
                                                    labelId="type-label"
                                                    value={type}
                                                    onChange={(e) => handleTypeChange(e.target.value as string)}
                                                    label="Type"
                                                >
                                                    {TYPE_OPTIONS.map((opt) => (
                                                        <MenuItem key={opt.value} value={opt.value}>
                                                            {opt.label}
                                                        </MenuItem>
                                                    ))}
                                                </Select>
                                            </FormControl>
                                        </Grid>

                                        <Grid item xs={12} sm={6} md={3}>
                                            <FormControl fullWidth disabled={!type}>
                                                <InputLabel id="action-label">Action</InputLabel>
                                                <Select
                                                    labelId="action-label"
                                                    value={action}
                                                    onChange={(e) => setAction(e.target.value as string)}
                                                    label="Action"
                                                >
                                                    <MenuItem value="">
                                                        <em>{type ? "All" : "Select a type"}</em>
                                                    </MenuItem>
                                                    {actionsForSelectedType.map((a) => (
                                                        <MenuItem key={a} value={a}>
                                                            {a}
                                                        </MenuItem>
                                                    ))}
                                                </Select>
                                            </FormControl>
                                        </Grid>


                                        <Grid item xs={12}>
                                            <Stack direction="row" spacing={2}>
                                                <Button
                                                    variant="contained"
                                                    onClick={() => submitFilters()}
                                                    disabled={loading}
                                                    startIcon={loading ? <CircularProgress size={18} /> : null}
                                                >
                                                    Apply
                                                </Button>
                                                <Button variant="outlined" onClick={clearFilters} disabled={loading}>
                                                    Clear
                                                </Button>
                                            </Stack>
                                        </Grid>
                                    </Grid>
                                </CardContent>
                            </Card>
                        </Grid>

                        {/* Table */}
                        <Grid item xs={12}>
                            <Card>
                                <CardContent>
                                    <Stack direction="row" alignItems="center" justifyContent="space-between" mb={2}>
                                        <Typography variant="h6">Results</Typography>
                                        {loading && (
                                            <Stack direction="row" alignItems="center" spacing={1}>
                                                <CircularProgress size={18} />
                                                <Typography variant="body2" color="text.secondary">Loading…</Typography>
                                            </Stack>
                                        )}
                                    </Stack>

                                    <TableContainer>
                                        <Table size="small">
                                            <TableHead>
                                                <TableRow>
                                                    <TableCell>Date</TableCell>
                                                    <TableCell>Actor</TableCell>
                                                    <TableCell>Action</TableCell>
                                                    <TableCell>Description</TableCell>
                                                    <TableCell>Object</TableCell>
                                                </TableRow>
                                            </TableHead>
                                            <TableBody>
                                                {logs.data.length === 0 ? (
                                                    <TableRow>
                                                        <TableCell colSpan={5}>
                                                            <Typography variant="body2" color="text.secondary">No results.</Typography>
                                                        </TableCell>
                                                    </TableRow>
                                                ) : (
                                                    logs.data.map((row) => (
                                                        <TableRow key={row.id} hover>
                                                            <TableCell width={180}>
                                                                {dayjs(row.created_at).format("LLL")}
                                                            </TableCell>
                                                            <TableCell width={160}>
                                                                <Stack direction="row" spacing={1} alignItems="center">
                                                                    <Chip
                                                                        size="small"
                                                                        label={row.actor_type === "agent" ? "Agent" : "System"}
                                                                        color={row.actor_type === "agent" ? "primary" : "default"}
                                                                        variant="outlined"
                                                                    />
                                                                    {row.actor_id && (
                                                                        <Typography variant="caption" color="text.secondary">
                                                                            {row.actor_username}
                                                                        </Typography>
                                                                    )}
                                                                </Stack>
                                                            </TableCell>
                                                            <TableCell width={200}>
                                                                <Typography variant="body2" sx={{ fontWeight: 600 }}>
                                                                    {row.action}
                                                                </Typography>
                                                            </TableCell>
                                                            <TableCell>
                                                                <Typography variant="body2">{row.description}</Typography>
                                                            </TableCell>
                                                            <TableCell width={200}>
                                                                <Stack spacing={0.5}>
                                                                    {row.related_type === "cabin" && (
                                                                        <Chip
                                                                            size="small"
                                                                            label="Go To Cabin"
                                                                            color="primary"
                                                                            component={Link}
                                                                            href={route("cabins.edit", { id: 1, cabin_id: row.related_id })}
                                                                            clickable
                                                                        />
                                                                    )}

                                                                    {row.related_type === "booking" && row.event_id && row.booking_code && (
                                                                        <Chip
                                                                            size="small"
                                                                            label="Go To Booking"
                                                                            color="success"
                                                                            component={Link}
                                                                            href={route("bookings.show", {
                                                                                id: row.event_id,
                                                                                booking_code: row.booking_code,
                                                                            })}
                                                                            clickable
                                                                        />
                                                                    )}

                                                                    {row.related_type === "customer" && (
                                                                        <Chip
                                                                            size="small"
                                                                            label="Go To Customer"
                                                                            color="warning"
                                                                            component={Link}
                                                                            href={route("customers.show", row.related_id)}
                                                                            clickable
                                                                        />
                                                                    )}

                                                                    {row.related_type === "event" && (
                                                                      <Chip
                                                                        size="small"
                                                                        label="Go To Event"
                                                                        color="info"
                                                                        component={Link}
                                                                        href={route("events.show", row.related_id)}
                                                                        clickable
                                                                      />
                                                                    )}
                                                                    <Typography variant="caption" color="text.secondary">
                                                                        ID: {row.related_id}
                                                                    </Typography>
                                                                </Stack>
                                                            </TableCell>
                                                        </TableRow>
                                                    ))
                                                )}
                                            </TableBody>
                                        </Table>
                                    </TableContainer>

                                    {/* Pagination */}
                                    <TablePagination
                                        component="div"
                                        count={logs.total}
                                        page={currentPageZeroBased}
                                        onPageChange={handleChangePage}
                                        rowsPerPage={logs.per_page}
                                        onRowsPerPageChange={handleChangeRowsPerPage}
                                        rowsPerPageOptions={[10, 20, 30, 50]}
                                    />
                                </CardContent>
                            </Card>
                        </Grid>
                    </Grid>
                )}
            </Container>
        </AuthenticatedLayout>
    );
};

export default Index;
