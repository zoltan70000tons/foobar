/**
 * return array of decks
 *
 * @returns {string}
 */
export const formatDeckNumber = (input) => {
  return input
    .replace(/\s+/g, "")
    .split(/[,|-]/)
    .map(Number)
    .filter((n) => !isNaN(n));
};