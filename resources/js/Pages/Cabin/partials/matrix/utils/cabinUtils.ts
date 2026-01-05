import { CabinPriceType, PriceAndAvailability } from "@/types/cabin";

export const CAPACITIES = [2, 3, 4, 5, 6, 7, 8];

export function extractCabinPrices(priceAndAvailability: PriceAndAvailability, cabinCode: string): CabinPriceType[] {
  return CAPACITIES.map((capacity) => {
    const key = `price_capacity_${capacity}` as keyof PriceAndAvailability;
    const data = priceAndAvailability[key];

    if (!data) return null;

    return {
      full_title: data.full_title,
      price: data.price ?? "-",
      is_available: data.is_available,
      cabin_code: cabinCode,
      cabin_category_id: data.cabin_category_id,
      capacity: data.capacity,
      decks: data.decks,
      iframe: data.iframe,
      images: data.images,
      description: data.description ?? "",
      inventory: data.inventory,
    };
  }).filter(Boolean) as CabinPriceType[];
}
