import { CabinCategory } from "./CabinCategory";
import { CabinStatus } from "@/enums/CabinStatus";

export interface Cabin {
  id: number;
  cabin_type_id: number;
  cabin_category_id: number;
  cabin_type: CabinType;
  cabin_category: CabinCategory;
  inventory: number;
  notes: string;
  tags: string[];
  status: CabinStatus;
  created_at: string;
  updated_at: string;
  is_shared_cabin_number: boolean;
  is_reserved: boolean;
  cabin_spec: CabinSpec;
  internal_notes: string;
  deck: number | null;
}

export interface CabinSpec {
  lower_bed_type_1: string;
  lower_bed_type_2: string;
  upper_berths: string;
  accessible: boolean;
  connects_with: string;
  location: string;
  balcony: boolean;
  obstructed_view: boolean;
  cabin_number: string;
  deck: number;
  total_berths: number;
}

interface CabinType {
  id: number;
  cabin_type: string;
  cabin_type_description: string;
}

export type BookingCabin = {
  accessible: boolean;
  balcony: boolean;
  cabin_category_id: number;
  cabin_category_name: string;
  cabin_category_type: string;
  cabin_inventory: number;
  cabin_number: string;
  cabin_type_id: number;
  capacity: number;
  deck: number;
  id: number;
  location: string;
  lower_bed_type_2: string;
  status: CabinStatus;
}
