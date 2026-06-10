/**
 * Date utility functions
 */

/**
 * Format a Date object to YYYY-MM-DD string
 * @param date - The date to format
 * @returns Formatted date string in YYYY-MM-DD format
 */
export const formatDate = (date: Date): string => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

/**
 * Validate if a string matches ORG-XXXX-XXXX format (app login code)
 * @param code - The code to validate
 * @returns True if valid format
 */
export const isValidAppLoginCode = (code: string): boolean => {
  // Format: XXX-XXXX-XXXX (exactly 3 alpha prefix, 4 alphanumeric, 4 alphanumeric)
  const pattern = /^[A-Z]{3}-[A-Z0-9]{4}-[A-Z0-9]{4}$/i;
  return pattern.test(code.trim());
};
