import { useEffect, useState } from 'react';
import { Alert, AlertTitle, Typography } from '@mui/material';
import { router } from '@inertiajs/react'; 
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';

dayjs.extend(utc);

interface BookingSessionTimerProps {
  lockedAt: string;
  sessionDurationMinutes?: number;
  bookingId: number;
  eventId: number;
}

export const BookingSessionTimer = ({
  lockedAt,
  sessionDurationMinutes = 10,
  bookingId,
  eventId,
}: BookingSessionTimerProps) => {

  const getInitialTimeLeft = () => {
    const lockedTime = dayjs.utc(lockedAt);
    const expireTime = lockedTime.add(sessionDurationMinutes, 'minute');
    const now = dayjs.utc();
    const diffInSeconds = expireTime.diff(now, 'second');
    return Math.max(diffInSeconds, 0);
  };

  const [timeLeft, setTimeLeft] = useState(getInitialTimeLeft);

  useEffect(() => {
    const interval = setInterval(() => {
      setTimeLeft((prevTimeLeft) => {
        if (prevTimeLeft <= 1) {
          clearInterval(interval);

          router.get(route('bookings.editMode'), {
            booking_id: bookingId,
            lock: "0",
            event_id: eventId,
          });

          return 0;
        }
        return prevTimeLeft - 1;
      });
    }, 1000);

    return () => clearInterval(interval);
  }, [lockedAt, sessionDurationMinutes, bookingId, eventId]);

  const minutes = Math.floor(timeLeft / 60);
  const seconds = timeLeft % 60;
  const formatTime = (num: number) => String(num).padStart(2, '0');
  return (
    <Alert severity="info" sx={{ mb: 2 }}>
      <AlertTitle>Info</AlertTitle>
      {timeLeft > 0 ? (
          <>
            <Typography variant="body2" sx={{ mb: 1 }}>
              You are currently editing this booking.
            </Typography>
            <Typography variant="body1" sx={{ fontWeight: 'bold' }}>
              Time left: {formatTime(minutes)}:{formatTime(seconds)}
            </Typography>
            <Typography variant="caption" display="block" sx={{ mt: 1 }}>
              After that, it will be automatically unlocked.
            </Typography>
          </>
        ) : (
          <Typography variant="body2">
            Your session has expired and the booking is now unlocked.
          </Typography>
        )}
    </Alert>
  );
};
