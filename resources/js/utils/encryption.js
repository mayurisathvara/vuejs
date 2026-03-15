import CryptoJS from 'crypto-js'

const SECRET_KEY = 'VueAdmin2026SecureEncryptionKey'

/**
 * Encrypt ID for URL usage
 * @param {number|string} id - The ID to encrypt
 * @returns {string} Encrypted and URL-safe string
 */
export const encryptId = (id) => {
  if (!id) return ''
  
  try {
    const encrypted = CryptoJS.AES.encrypt(String(id), SECRET_KEY).toString()
    // Make URL safe by replacing special characters
    return encrypted
      .replace(/\+/g, '-')
      .replace(/\//g, '_')
      .replace(/=/g, '')
  } catch (error) {
    console.error('Encryption error:', error)
    return String(id)
  }
}

/**
 * Decrypt ID from URL
 * @param {string} encryptedId - The encrypted ID from URL
 * @returns {number|null} Decrypted ID as number
 */
export const decryptId = (encryptedId) => {
  if (!encryptedId) return null
  
  try {
    // Restore base64 characters
    let base64 = encryptedId
      .replace(/-/g, '+')
      .replace(/_/g, '/')
    
    // Add padding if needed
    while (base64.length % 4) {
      base64 += '='
    }
    
    const decrypted = CryptoJS.AES.decrypt(base64, SECRET_KEY).toString(CryptoJS.enc.Utf8)
    const id = parseInt(decrypted, 10)
    
    return isNaN(id) ? null : id
  } catch (error) {
    console.error('Decryption error:', error)
    return null
  }
}
