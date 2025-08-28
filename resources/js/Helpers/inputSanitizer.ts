// src/utils/inputSanitizer.js

/**
 * Sanitizes a string by removing dangerous characters.
 *
 * @param {string} input - The input string to sanitize.
 * @returns {string} - The sanitized string.
 */
export const sanitizeInput = (input) => {
    const dangerousPattern = /['";<>\\\/`&{}[\]()=|%+*^$#@!]/g;
    return input.replace(dangerousPattern, "");
};