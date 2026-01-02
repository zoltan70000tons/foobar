export type InventoryStatus = {
  AVAILABLE: number;
  RESERVED: number;
  BOOKED: number;
  CLOSED: number;
  PARTIALLY_BOOKED: number;
  IP: number;
};

export type PriceAndCapacity = {
  cabin_category_id: number;
  capacity: number;
  decks: string;
  full_title: string;
  price: string | null;
  iframe: string | null;
  images: string[] | null;
  is_available: boolean;
  description: {
    de: string;
    en: string;
    es: string;
  };
  inventory: InventoryStatus;
};

export type PriceAndAvailability = {
  price_capacity_2: PriceAndCapacity;
  price_capacity_3: PriceAndCapacity;
  price_capacity_4: PriceAndCapacity;
  price_capacity_5: PriceAndCapacity;
  price_capacity_6: PriceAndCapacity;
  price_capacity_7: PriceAndCapacity;
  price_capacity_8: PriceAndCapacity;
};

export type CabinDetail = {
  cabin_category_id: number;
  code: string;
  decks_static: string;
  description: {
    de: string;
    en: string;
    es: string;
  };
  display_order: number;
  full_title: string;
  name: string;
  price_and_availability: PriceAndAvailability;
};

export type CabinData = {
  cabins: CabinDetail[];
  cabin_category_id: number;
  display_order: number;
  name: string;
};

export type MainCategory = {
  name: string;
  categories: CabinData[];
  display_order: number;
  max_capacity: number;
};

export type CabinTypeData = {
  cabinTypeId: string;
  cabinTypeTitle: string;
  cabinTypeSlug: string;
};

export type CabinPriceType = {
  full_title: string;
  price: string | null;
  is_available: boolean;
  cabin_code: string;
  cabin_category_id: number;
  capacity: number;
  inventory: InventoryStatus;
  decks: string;
  iframe?: string | null;
  images?: string[] | null;
  description: {
    de: string;
    en: string;
    es: string;
  };
};

export interface MobileCabinDetail {
  cabin_category_id: number;
  code: string;
  decks_static: string;
  description: {
    de: string;
    en: string;
    es: string;
  };
  display_order: number;
  full_title: string;
  name: string;
  price_and_availability: PriceAndAvailability;
}

export interface MobileCabinRow {
  code: string;
  row_data_name: string;
  capacity: number;
  price_and_availability: PriceAndCapacity | null;
  cabin_category_id: number | string;
  full_title: string;
  images?: string[] | null;
  iframe?: string;
  decks_static: string;
}
