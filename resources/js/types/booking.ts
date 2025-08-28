import { Cabin } from "@/interfaces/Cabin";
import { User } from "@/interfaces/User";

export type Booking = {
    id: number;
    booking_code: string;
    event_id: number;
    passengers: { id: number; email: string }[];
    created_at: string;
    updated_at: string;
    cabin: Cabin;
    balance: number;
    cost: number;
    status:string;
    tags: string[];
    agent: User | null;
    editingUsername: string;
  };