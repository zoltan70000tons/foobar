import * as React from 'react';
import { Link, Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import Navbar from '@/Components/Navbar';
import { Container, Stack, Button } from '@mui/material';

export default function Welcome({ auth, laravelVersion, phpVersion }: PageProps<{ laravelVersion: string, phpVersion: string }>) {
  return (
    <>
      <Head title="Welcome" />
      <header>
        <nav>
          <Container>
            <Stack direction="row" spacing={2}>
              {auth.user ? (
                <Link
                  href={route('dashboard')}
                >
                  <Button variant="contained">Dashboard</Button>
                </Link>
              ) : (
                <>
                  <Link
                    href={route('login')}
                  >
                    <Button variant="contained">Log in</Button>
                  </Link>
                  <Link
                    href={route('register')}
                  >
                    <Button variant="contained">Register</Button>
                  </Link>
                </>
              )}
            </Stack>
          </Container>
        </nav>
      </header>
      <Container
        sx={{
          minHeight: "100vh",
          widht: "100%",
          display: "flex",
          justifyContent: "center",
          alignItems: "center"
        }}
      >
        <h1>70 000 TONS OF METAL ADMIN</h1>
      </Container>
      <Container>
        Laravel {laravelVersion} (PHP v{phpVersion})
      </Container>
    </>
  );
}
