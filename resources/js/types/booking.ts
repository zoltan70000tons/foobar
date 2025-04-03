export type Booking = {
    id: number;
    booking_code: string;
    event_id: number;
    passengers: { id: number; email: string }[];
  };