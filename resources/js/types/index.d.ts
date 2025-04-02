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
