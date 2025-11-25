
import { useEffect, useState } from'react';
import { Box, Typography } from '@mui/material';
// reverb
import '@/echo';
import { ReverbLockBookingEvent } from '@/interfaces/ReverbLockBookingEvent';

type Props = {
  bookingId: number;
  currentEditingUser: string | null;
};

export default function LockedByAgent({bookingId, currentEditingUser}: Props) {
 
  const [userName, setUserName] = useState<string | null>(currentEditingUser);
 
  useEffect(() => {
    
    const channel = window.Echo.channel('reverb-lock-booking');
  
    channel.listen('.ReverbLockBooking', ({ bookingId: incominBookingId, username }: ReverbLockBookingEvent) => {
      // console.log('BookingEditStatusUpdated event received:', bookingId, username);
      if (incominBookingId === bookingId) {
        setUserName(username);
      }
    });
  
    return () => {
      window.Echo.leave('reverb-lock-booking');
    };
  }, []);

  if(!userName) {
    return null;
  }

  return (
    <Box 
      component="small" 
      color="warning.main"
   >
      Locked by {userName}
    </Box>
  )
}