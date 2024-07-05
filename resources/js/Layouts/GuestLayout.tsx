import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import { Box } from '@mui/material';

export default function Guest({ children }: PropsWithChildren) {
  return (
    <div
      style={{
        width: "100%",
        minHeight: "100vh",
        display: "flex",
        flexDirection: "row",
        flexWrap: "wrap",
        justifyContent: "center",
      }}
    >
      <Box
        sx={{
          width: "100%",
          display: "flex",
          justifyContent: "center",
          alignItems: "center"
        }}
      >
        <Link href="/">
          <ApplicationLogo
            style={{
              width: "80px",
              height: "80px",
            }}
          />
        </Link>
      </Box>
      <div>
        {children}
      </div>
    </div>
  );
}
