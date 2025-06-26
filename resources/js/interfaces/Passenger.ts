import { InstallmentStatus } from "@/types/payments";

export interface Passenger {
  id: number;
  lead_passenger: boolean;
  passenger_order: number;
  booking_id: number;
  full_name: string;
  email: string;
  passenger_balance: number;
  passenger_allocated_cost: number;
  installment_status: InstallmentStatus;
  fees:
}

// interface InstallmentStatus{
//     fully_paid: boolean;
//     next_installment: {
//         amount: number;
//         due_date: string;
//         type: string;
//     };
//     paid_installments : {
//       amount: number;
//       type: string;
//     }
// }