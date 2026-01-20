import { Nullable } from "@/interfaces/utils";

export type AuthProps = {
  user: Nullable<{
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    username: string;
  }>;
  permissions: string[];
  roles: string[];
};
