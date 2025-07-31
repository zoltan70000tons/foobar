type AuthProps = {
  user: {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    username: string;
  } | null;
  permissions: string[];
  roles: string[];
};
