declare module "iso-3166-2" {
  interface Subdivision {
    code: string;
    name: string;
    type: string;
    countryCode: string;
    countryName: string;
  }

  interface Country {
    code: string;
    name: string;
    subdivisions: Record<string, Subdivision>;
  }

  const iso3166: {
    country: (code: string) => Country | undefined;
    subdivision: (countryOrCode: string, subCode?: string) => Subdivision | undefined;
  };

  export = iso3166;
}
