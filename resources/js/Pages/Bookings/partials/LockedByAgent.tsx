
import { useEffect, useState } from'react';
import { Box } from '@mui/material';
// reverb
import '@/echo';

type Props = {
  bookingId: any;
  currentEditingUser: any;
};

export default function LockedByAgent({bookingId, currentEditingUser}: Props) {
 
  const [userName, setUserName] = useState<string | null>(currentEditingUser);
 
  useEffect(() => {
    
    const channel = window.Echo.channel('booking-status');
  
    channel.listen('.BookingEditStatusUpdated', ({ bookingId, username }: any) => {
      // console.log('BookingEditStatusUpdated event received:', bookingId, username);
      if (bookingId === bookingId) {
        setUserName(username);
      }
    });
  
    return () => {
      window.Echo.leave('booking-status');
    };
  }, []);

  if(!userName) {
    return null;
  }

  return (
   
    <Box component="small" sx={{ width: "10px" }} color="warning.main">
      Being used by {userName}
    </Box>
  )
}