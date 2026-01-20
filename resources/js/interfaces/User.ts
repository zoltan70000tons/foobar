import { Nullable } from "@/interfaces/utils";
import { HearAboutSourceEnum, PassengerPaymentMethodEnum } from "@/enums/PassengerEnum";

export interface User {
  detail: UserDetail;
  id: number;
  email: string;
  status: string;
  roles: string[];
  organization_id: number;
  organization_name: string;
  survivor_number?: string;
  lastname?: string;
  middlename?: string;
  username?: string;
  firstname?: string;
  phone_number?: string;
  gender?: string;
}

export interface UserDetail {
  phone: string;
  first_name: string;
  last_name: string;
  middle_name: string;
  gender: string;
}

export interface Agent {
  id: number;
  name: string;
  email: string;
  email_verified_at: string;
  username: string;
}

export type BookingUser = {
  address_first: string;
  address_second: string;
  cabin_conf_accp: Nullable<boolean>;
  citizenship: string;
  city: string;
  confirmed_booking_email: Nullable<string>;
  country: string;
  dob: string;
  email: Nullable<string>;
  emergency_c_name: string;
  emergency_c_phone: string;
  first_name: string;
  full_name: string;
  gender: string;
  has_booking: boolean;
  hear_about: Nullable<HearAboutSourceEnum>;
  id: string;
  last_name: string;
  lead_passenger: Nullable<boolean>;
  middle_name: string;
  newsletter: Nullable<boolean>;
  passenger_allocated_cost: Nullable<string>;
  passenger_balance: Nullable<string>;
  payment_method: Nullable<PassengerPaymentMethodEnum>;
  phone: string;
  postal_code: string;
  single_t_agreement: Nullable<boolean>;
  special_request: Nullable<string>;
  state: Nullable<string>;
  survivor_number: Nullable<string>;
  term_n_cons: Nullable<boolean>;
  travel_info: Nullable<boolean>;
  was_on_board: Nullable<boolean>;
}
