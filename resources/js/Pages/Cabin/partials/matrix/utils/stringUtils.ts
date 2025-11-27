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
 * Formats a number as a localized currency string.
 *
 * @param number - The number to format. Can be a number or a string that represents a number.
 * @param locale - The locale to use for formatting. Defaults to "en".
 * @param hideDecimals - Whether to hide decimal places. Defaults to false.
 * @returns The formatted currency string.
 */
export function localNumberFormat(
  number: number | string,
  locale: string = "en",
  hideDecimals: boolean = false,
  fullCurrency: boolean = true
): string {
  // Use English format locale for "es"
  const overrideLocale = locale === "es" ? "en" : locale;

  const options: Intl.NumberFormatOptions = hideDecimals
    ? {}
    : { minimumFractionDigits: 2, maximumFractionDigits: 2 };

  if (fullCurrency === true) {
    return (
      "USD " +
      new Intl.NumberFormat(overrideLocale, options).format(
        typeof number === "string" ? parseFloat(number) : number
      )
    );
  }

  return "$".concat(
    new Intl.NumberFormat(overrideLocale, options).format(
      typeof number === "string" ? parseFloat(number) : number
    )
  );
}

/**
 * Formats a date as a localized string.
 *
 * @param date - The date to format. Can be a Date object or a string that represents a date.
 * @param locale - The locale to use for formatting. Defaults to "en".
 * @returns The formatted date string.
 */

export function localDateFormat(
  date: string | Date,
  locale: string = "en"
): string {
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
    return ""; // Return empty string if date is not valid
  }

  return new Intl.DateTimeFormat(locale, {
    year: "numeric",
    month: locale === "es" ? "long" : "short",
    day: "2-digit",
    timeZone: "UTC", // Forces UTC formatting
  }).format(parsedDate);
}
