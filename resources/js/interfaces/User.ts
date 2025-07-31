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