import { InstallmentStatus } from "@/types/payments";
import { Nullable } from "@/interfaces/utils";

export interface Passenger {
  id: number | string;
  lead_passenger: boolean;
  passenger_order: number;
  booking_id: number;
  full_name: string;
  email: string | null;
  passenger_balance: number;
  passenger_allocated_cost: number;
  installment_status?: InstallmentStatus;
  first_name: string,
  middle_name?: Nullable<string>,
  last_name: string,
  dob: Nullable<string>,
  gender: Nullable<string>,
  citizenship: Nullable<string>,
  survivor_number: Nullable<string>,
  phone: Nullable<string>,
  address_first: Nullable<string>,
  address_second: Nullable<string>,
  city: Nullable<string>,
  state?: Nullable<string>,
  postal_code: Nullable<string>,
  country: Nullable<string>,
  emergency_c_name: Nullable<string>,
  emergency_c_phone: Nullable<string>,
  payment_method: Nullable<string>,
  special_request: Nullable<string>,
  travel_info: boolean,
  terms_n_cons: boolean,
  single_t_agreement: boolean,
  newsletter: boolean,
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
