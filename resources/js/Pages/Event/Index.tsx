import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Card, CardActionArea, CardActions, CardContent, CardHeader, CardMedia, Container, Grid, Toolbar, Typography, Chip, Fab } from '@mui/material';
import { usePermissions } from '@/Providers/PermissionContext';
import dayjs from 'dayjs';
import 'dayjs/locale/en';
import localizedFormat from 'dayjs/plugin/localizedFormat';




const Index = ({ auth, events }: PageProps) => {
    const { hasPermission } = usePermissions();
    dayjs.extend(localizedFormat);

    console.log(events);
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={"Events"}
        >
            <Head title="Events" />
            <Toolbar />


            <Link href={route('events.create')} className="btn btn-primary">Create New Event</Link>
            <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>

                <Grid container spacing={3}  >

                    {events.map(event => (
                        <Grid item key={event.id} xs={12} sm={6} md={4} >
                            <Card>
                                <CardActionArea component={Link} href={route('events.edit', event.id)}>
                                    <CardHeader
                                        //title="Shrimp and Chorizo Paella"
                                        subheader={dayjs(event.start_date).format('LL')}
                                    />
                                    <CardContent>
                                        <Typography gutterBottom variant="h6" component="div">
                                            {event.name}
                                        </Typography>
                                        <Typography variant="body2" color="text.secondary">
                                            {event.description}
                                        </Typography>
                                    </CardContent>
                                    <CardActions disableSpacing>
                                        <Chip size="small" label={event.status} />
                                    </CardActions>
                                </CardActionArea>
                            </Card>
                        </Grid>
                    ))}
                </Grid>

            </Container>
            {/* </ul> */}
        </AuthenticatedLayout>
    );
};

export default Index;