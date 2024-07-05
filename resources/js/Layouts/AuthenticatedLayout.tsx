import { useState, PropsWithChildren, ReactNode } from 'react';
import { Link } from '@inertiajs/react';
import { User } from '@/types';
import { AppBar, Box, Container, Toolbar, Menu, MenuItem, Button } from '@mui/material';
import IconButton from '@mui/material/IconButton';
import MenuIcon from '@mui/icons-material/Menu';

export default function Authenticated({ user, header, children }: PropsWithChildren<{ user: User, header?: ReactNode }>) {

  const [anchorElNav, setAnchorElNav] = useState<null | HTMLElement>(null);

  // Handle open nav menu
  const handleOpenNavMenu = (event: React.MouseEvent<HTMLElement>) => {
    setAnchorElNav(event.currentTarget);
  };


  // Handle close nav menu
  const handleCloseNavMenu = () => {
    setAnchorElNav(null);
  };


  return (
    <Box
      sx={{
        minHeight: "screen"
      }}
    >
      <nav>
        <AppBar
          position="static"
        >
          <Container>
            <Toolbar
              disableGutters
              sx={{
                width: "100%",
                display: "flex",
                justifyContent: "space-between"
              }}
            >
              <Link href={route('dashboard')}>
                Dashboard
              </Link>
              <Box>
                <IconButton
                  size="large"
                  aria-label="account of current user"
                  aria-controls="menu-appbar"
                  aria-haspopup="true"
                  onClick={handleOpenNavMenu}
                  color="inherit"
                >
                  <MenuIcon />
                </IconButton>
                <Menu
                  id="menu-appbar"
                  anchorEl={anchorElNav}
                  anchorOrigin={{
                    vertical: 'bottom',
                    horizontal: 'left',
                  }}
                  keepMounted
                  transformOrigin={{
                    vertical: 'top',
                    horizontal: 'left',
                  }}
                  open={Boolean(anchorElNav)}
                  onClose={handleCloseNavMenu}
                  sx={{
                    display: { xs: 'block' },
                  }}
                >
                  <MenuItem>
                    <Link href={route('profile.edit')}>Profile</Link>
                  </MenuItem>
                  <MenuItem>
                    <Button
                      variant="outlined"
                    >
                      <Link href={route('logout')} method="post" >Log Out</Link>
                    </Button>
                  </MenuItem>
                </Menu>
              </Box>

            </Toolbar>
          </Container>
        </AppBar>
      </nav>
      <Container>
        {header && (
          <header>
            <div>{header}</div>
          </header>
        )}
      </Container>
      <main>{children}</main>
    </Box>
  );
}
