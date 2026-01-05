export function roundToTwoDecimals(value: number): number {
  return Math.round(value * 100) / 100;
}

export function handleTotalSingle(price: string | number, singleTicketFeeAddon: number, taxPrice: number) {
  return roundToTwoDecimals(Number(price) + roundToTwoDecimals(singleTicketFeeAddon) + roundToTwoDecimals(taxPrice));
}

export function handleTotal(price: string | number, capacity: number, taxPrice: number) {
  if (!capacity) return 0;

  const tax = roundToTwoDecimals(taxPrice * capacity);
  return Number(price) * capacity + tax;
}
