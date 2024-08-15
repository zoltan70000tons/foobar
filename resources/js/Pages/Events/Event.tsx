import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';





const Event = ({ auth, events }: PageProps) => {

    console.log(events);
    return (
        <AuthenticatedLayout
        user={auth.user}
        header={"Events"}
      >
        <div>
            <h1>Lista de Eventos</h1>
            <ul>
                {events.map(event => (
                    <li key={event.id}>{event.name}</li>
                ))}
            </ul>
        </div>
        </AuthenticatedLayout>
    );
};

export default Event;