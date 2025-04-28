import { useEffect, useState, useRef } from "react";
import { Alert, AlertTitle, Typography, Snackbar } from "@mui/material";
import { router } from "@inertiajs/react";
import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";

dayjs.extend(utc);

interface BookingSessionTimerProps {
  lockedAt: string;
  sessionDurationMinutes?: number;
  bookingId: number;
  eventId: number;
}

// Helper outside to avoid recreating
const formatTime = (num: number) => String(num).padStart(2, "0");

export const BookingSessionTimer = ({
  lockedAt,
  sessionDurationMinutes = 10,
  bookingId,
  eventId,
}: BookingSessionTimerProps) => {
  const expireAtRef = useRef(dayjs.utc(lockedAt).add(sessionDurationMinutes, "minute"));

  const getTimeLeft = () => {
    const now = dayjs.utc();
    const diffInSeconds = expireAtRef.current.diff(now, "second");
    return Math.max(diffInSeconds, 0);
  };

  const [timeLeft, setTimeLeft] = useState(getTimeLeft());

  useEffect(() => {
    const interval = setInterval(() => {
      const updatedTimeLeft = getTimeLeft();
      setTimeLeft(updatedTimeLeft);
    }, 1000);

    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    if (timeLeft === 0) {
      router.get(route("bookings.editMode"), {
        booking_id: bookingId,
        lock: "0",
        event_id: eventId,
      });
    }
  }, [timeLeft, bookingId, eventId]);

  const minutes = Math.floor(timeLeft / 60);
  const seconds = timeLeft % 60;

  return (
    <>
      <Alert severity="info" sx={{ mb: 2 }}>
        <AlertTitle>Info</AlertTitle>
        {timeLeft > 0 ? (
          <>
            <Typography variant="body2" sx={{ mb: 1 }}>
              You are currently editing this booking.
            </Typography>
            <Typography variant="body1" sx={{ fontWeight: "bold" }}>
              Edit Time left: {formatTime(minutes)}:{formatTime(seconds)}
            </Typography>
            <Typography variant="caption" display="block" sx={{ mt: 1 }}>
              After that, it will be automatically unlocked.
            </Typography>
          </>
        ) : (
          <Typography variant="body2">Your session has expired and the booking is now unlocked.</Typography>
        )}
      </Alert>

      {/* Snackbar always visible when timeLeft > 0 */}
      <Snackbar open={timeLeft > 0}>
        <Alert severity="warning" variant="filled" sx={{ width: "100%" }}>
          Edit Time left: {formatTime(minutes)}:{formatTime(seconds)}
        </Alert>
      </Snackbar>
    </>
  );
};
