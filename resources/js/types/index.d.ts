export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string;
  username: string;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
  auth: {
    user: User;
    permissions: string[];
    roles: string[];
  };
  flash: {
    message?: string;
    success?: string;
    error?: string;
  };
  errors: Record<string, string[]>;
  menu?: {
    events: any;
  };
};

type MembershipType = {
  id: number;
  name: string;
  discount_value: string;
  booking_number_requirement: number;
}

type PresalePeriods = {
  id: string;
  event_id: number;
  membership_type_id: number;
  membership_type: MembershipType;
  start_date: string;
  end_date: string;
}

export type EventType = {
  id: number;
  address: string;
  booked_stamp: string;
  code: string;
  description: string;
  end_date: string;
  image: string;
  name: string;
  organization_id: number;
  presale_periods: PresalePeriods[];
  start_date: string;
  status: string;
  url: string;
}
