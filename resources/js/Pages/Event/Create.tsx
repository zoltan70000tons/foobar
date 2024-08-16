import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';

const Create = ({ auth, events }: PageProps) => {
    const [title, setTitle] = useState('');
    const [body, setBody] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();
        Inertia.post(route('events.store'), { title, body });
    };

    return (
        <div>
            <h1>Create Post</h1>
            <form onSubmit={handleSubmit}>
                <div>
                    <label>Title</label>
                    <input type="text" value={title} onChange={(e) => setTitle(e.target.value)} />
                </div>
                <div>
                    <label>Body</label>
                    <textarea value={body} onChange={(e) => setBody(e.target.value)}></textarea>
                </div>
                <button type="submit">Create</button>
            </form>
        </div>
    );
}

export default Create;