import { createTheme } from '@mui/material/styles';

const theme = createTheme({
    typography: {
        fontFamily: 'Roboto, Arial, sans-serif',
        h1: {
            fontSize: '2.5rem',
            fontWeight: 700,
        },
        h2: {
            fontSize: '2rem',
            fontWeight: 700,
        },
        body1: {
            fontSize: '1rem',
        },
        button: {
            textTransform: 'none',
        },
    },
    palette: {
        mode: 'dark',
        // primary: {
        //    // main: '#000000',
        // },
    },
    components: {
        MuiInputBase: {
          styleOverrides: {
            input: {
                "&.Mui-readOnly": {
                color: 'rgba(255, 255, 255, 0.5)',
                pointerEvents: 'none',
                backgroundColor: 'rgba(255,255,255,0.04)',
                },
            },
          },
        },
    },
});

export default theme;