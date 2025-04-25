/**
 * Converts a string into a handleized/slug version suitable for URLs or identifiers.
 *
 * @param str - The input string to be handleized.
 * @returns The handleized version of the input string.
 */
export function handleize(str: string): string {
  return str
    .toLowerCase()
    .replace(/\s+/g, "-") // Replace spaces with hyphens
    .replace(/[^a-z0-9-]/g, "") // Remove all non-alphanumeric characters except hyphens
    .replace(/-+/g, "-") // Replace multiple hyphens with a single one
    .replace(/^-+|-+$/g, ""); // Remove hyphens from the start and end
}

/**
 * Formats a number as a USD currency string.
 *
 * @param number - The number to format. Can be a number or a string that represents a number.
 * @param hideDecimals - Whether to hide decimal places. Defaults to false.
 * @param fullCurrency - Whether to prefix with "USD" or use "$". Defaults to true.
 * @returns The formatted currency string.
 */
export function formatCurrency(
  number: number | string,
  hideDecimals: boolean = false,
  fullCurrency: boolean = true
): string {
  const value = typeof number === "string" ? parseFloat(number) : number;

  const options: Intl.NumberFormatOptions = hideDecimals
    ? {}
    : { minimumFractionDigits: 2, maximumFractionDigits: 2 };

  const formatted = new Intl.NumberFormat("en-US", options).format(value);

  return fullCurrency ? `USD ${formatted}` : `$${formatted}`;
}

/**
 * Formats a date as a string in "en-US" locale and UTC timezone.
 *
 * @param date - The date to format. Can be a Date object or a string that represents a date.
 * @returns The formatted date string.
 */
export function formatDate(date: string | Date | null): string {
  if (typeof date !== "string" && !(date instanceof Date)) {
    return ""; // Return empty string if date is null or undefined
  }
  
  let parsedDate: Date;

  if (typeof date === "string") {
    // Check if it's a simple date (YYYY-MM-DD) or a full ISO string
    if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
      const [year, month, day] = date.split("-").map(Number);
      parsedDate = new Date(Date.UTC(year, month - 1, day));
    } else {
      // Fallback to ISO parsing
      parsedDate = new Date(date);
    }
  } else if (date instanceof Date) {
    parsedDate = date;
  } else {
    throw new Error("Invalid date format");
  }

  return new Intl.DateTimeFormat("en-US", {
    year: "numeric",
    month: "short",
    day: "2-digit",
    timeZone: "UTC", // Forces UTC formatting
  }).format(parsedDate);
}