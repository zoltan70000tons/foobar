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


/**
 * Converts a number to its ordinal name (e.g., 1 -> "first", 2 -> "second").
 *
 * @param n - The number to convert.
 * @returns The ordinal name of the number.
 */
export function getOrdinalName(n: number): string {
  const ordinals: { [key: number]: string } = {
    1: 'first',
    2: 'second',
    3: 'third',
    4: 'fourth',
    5: 'fifth',
    6: 'sixth',
    7: 'seventh',
    8: 'eighth',
    9: 'ninth',
    10: 'tenth',
    11: 'eleventh',
    12: 'twelfth',
    13: 'thirteenth',
    14: 'fourteenth',
    15: 'fifteenth',
    16: 'sixteenth',
    17: 'seventeenth',
    18: 'eighteenth',
    19: 'nineteenth',
    20: 'twentieth',
  };

  if (ordinals[n]) {
    return ordinals[n];
  }

  if (n > 20 && n < 100) {
    const tens = Math.floor(n / 10) * 10;
    const units = n % 10;

    const tensWord: { [key: number]: string } = {
      20: 'twentieth',
      30: 'thirtieth',
      40: 'fortieth',
      50: 'fiftieth',
      60: 'sixtieth',
      70: 'seventieth',
      80: 'eightieth',
      90: 'ninetieth',
    };

    const unitWord = ordinals[units] ?? `${units}th`;

    if (units === 0) {
      return tensWord[tens] || `${tens}th`;
    } else {
      return `${tensWord[tens]?.replace('ieth', 'y') || `${tens}`} ${unitWord}`;
    }
  }

  return `${n}th`;
}

