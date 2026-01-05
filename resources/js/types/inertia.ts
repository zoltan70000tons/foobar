type FooterGroup = Record<string, string>;

export type OAuthTranslations = {
  Navigation: Record<string, string>;
  Menu: Record<string, string>;
  Footer: Record<string, string> & {
    Event: FooterGroup;
    Support: FooterGroup;
    Legal: FooterGroup;
    Contact: FooterGroup;
  };
};

declare module "@inertiajs/react" {
  interface PageProps {
    auth: {
      user: { id: number; name: string; email: string } | null;
      permissions: string[];
      roles: string[];
    };
    flash: {
      message: string | null;
      success: string | null;
      error: string | null;
    };
    menu: {
      events: { id: number; name: string; code: string }[];
    };
    oauthTranslations: OAuthTranslations;
  }
}
