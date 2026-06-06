/**
 * Unique ID Generator for Call Logs
 * Uses Stripe/AWS-style format for professional, compact IDs
 */

/**
 * Base62 encoding for compact representation
 * Uses: 0-9, A-Z, a-z (62 characters)
 */
const BASE62_CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

const encodeBase62 = (num: number): string => {
  if (num === 0) return '0';
  
  let result = '';
  while (num > 0) {
    result = BASE62_CHARS[num % 62] + result;
    num = Math.floor(num / 62);
  }
  return result;
};

/**
 * Generate a short hash from string
 */
const generateShortHash = (str: string): string => {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    const char = str.charCodeAt(i);
    hash = ((hash << 5) - hash) + char;
    hash = hash & hash;
  }
  return encodeBase62(Math.abs(hash)).padStart(4, '0').substring(0, 4);
};

/**
 * Generate random Base62 string
 */
const randomBase62 = (length: number): string => {
  let result = '';
  for (let i = 0; i < length; i++) {
    result += BASE62_CHARS[Math.floor(Math.random() * 62)];
  }
  return result;
};

/**
 * Generate a unique ID for call log
 * 
 * Format: cl_[timestamp]_[hash]_[random]
 * Example: cl_2bk5h7n_a9x4_m2p8
 * 
 * Benefits:
 * - Short & compact (21-25 characters)
 * - URL-safe (no special characters)
 * - Readable with underscores
 * - Industry standard (similar to Stripe, AWS)
 * - Lexicographically sortable by timestamp
 * 
 * Components:
 * - cl: Call log prefix
 * - timestamp: Base62 encoded timestamp (7 chars)
 * - hash: Base62 hash of userId+phone+type (4 chars)
 * - random: Base62 random string (4 chars)
 */
export const generateCallLogUniqueId = (
  userId: string,
  timestamp: number,
  phoneNumber: string,
  callType: 'IN' | 'OUT' | 'MISS'
): string => {
  // Encode timestamp in Base62 (compact & sortable)
  const timestampEncoded = encodeBase62(timestamp).padStart(11, '0').substring(0, 11);
  
  // Generate hash from userId, phone, and type
  const phoneDigits = phoneNumber.replace(/\D/g, '');
  const hashInput = `${userId}${phoneDigits}${callType}`;
  const hashPart = generateShortHash(hashInput);
  
  // Random component for uniqueness
  const randomPart = randomBase62(4);
  
  // Format: cl_[timestamp]_[hash]_[random]
  return `cl_${timestampEncoded}_${hashPart}_${randomPart}`;
};

/**
 * Alternative format: Compact without underscores
 * Format: CL[YYMMDD][HHMMSS][HASH][RND]
 * Example: CL260128143052A7B9
 * 
 * Benefits:
 * - Very compact (20 characters)
 * - No special characters
 * - Human-readable date/time
 */
export const generateCompactCallLogId = (
  userId: string,
  timestamp: number,
  phoneNumber: string,
  callType: 'IN' | 'OUT' | 'MISS'
): string => {
  const date = new Date(timestamp);
  
  // YY (last 2 digits of year)
  const year = String(date.getFullYear()).slice(-2);
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const seconds = String(date.getSeconds()).padStart(2, '0');
  
  // Generate 4-char hash
  const phoneDigits = phoneNumber.replace(/\D/g, '');
  const hashInput = `${userId}${phoneDigits}${callType}`;
  const hash = generateShortHash(hashInput);
  
  // Random 4-char suffix
  const random = randomBase62(4);
  
  // Format: CL[YYMMDD][HHMMSS][HASH][RND]
  return `CL${year}${month}${day}${hours}${minutes}${seconds}${hash}${random}`;
};

/**
 * Validate unique ID format (underscore format)
 */
export const isValidCallLogUniqueId = (uniqueId: string): boolean => {
  // Pattern: cl_[11chars]_[4chars]_[4chars]
  const pattern = /^cl_[0-9A-Za-z]{11}_[0-9A-Za-z]{4}_[0-9A-Za-z]{4}$/;
  return pattern.test(uniqueId);
};

/**
 * Validate compact unique ID format
 */
export const isValidCompactCallLogId = (uniqueId: string): boolean => {
  // Pattern: CL[YYMMDD][HHMMSS][4chars][4chars]
  const pattern = /^CL\d{6}\d{6}[0-9A-Za-z]{4}[0-9A-Za-z]{4}$/;
  return pattern.test(uniqueId);
};

/**
 * Extract timestamp from unique ID (underscore format)
 */
export const extractTimestampFromUniqueId = (uniqueId: string): number | null => {
  try {
    if (!isValidCallLogUniqueId(uniqueId)) {
      return null;
    }
    
    const parts = uniqueId.split('_');
    const timestampEncoded = parts[1];
    
    // Decode Base62 to get original timestamp
    let timestamp = 0;
    for (let i = 0; i < timestampEncoded.length; i++) {
      const char = timestampEncoded[i];
      const value = BASE62_CHARS.indexOf(char);
      timestamp = timestamp * 62 + value;
    }
    
    return timestamp;
  } catch (error) {
    return null;
  }
};
