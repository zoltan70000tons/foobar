export interface Customer {
  id: number;
  email: string;
  status: string;
  survivor_number?: string;
  lastname?: string;
  middlename?: string;
  username?: string;
  firstname?: string;
  phone_number?: string;
  gender?: string;
  tags: string[];
}
