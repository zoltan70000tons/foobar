export interface CabinCategory {
    id: number;
    category_type: string;
    category_code: string;
    category_name: string;
    capacity: number;
    description: string;
    price: number;
    display_order: number;
    cruise_id?: number;
    event_id?: number;
    title?: string;
  }