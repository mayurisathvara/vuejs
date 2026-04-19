const DATE_FORMAT_STORAGE_KEY = 'date_formate'
const ADMIN_DEFAULT_DATE_FORMAT = 'Y-m-d'
const ALLOWED_FORMATS = new Set(['Y-m-d', 'd-m-Y', 'm-d-Y', 'd/m/Y', 'm/d/Y', 'Y/m/d'])

const parseDateValue = (value) => {
  if (value instanceof Date) return Number.isNaN(value.getTime()) ? null : value
  if (value === null || value === undefined || value === '') return null
  const parsed = new Date(value)
  return Number.isNaN(parsed.getTime()) ? null : parsed
}

const pad2 = (value) => String(value).padStart(2, '0')

const normalizeDateFormat = (value) => (ALLOWED_FORMATS.has(value) ? value : ADMIN_DEFAULT_DATE_FORMAT)

export const getActiveDateFormat = () => {
  if (typeof window === 'undefined') return ADMIN_DEFAULT_DATE_FORMAT

  try {
    const userRaw = localStorage.getItem('user')
    const user = userRaw ? JSON.parse(userRaw) : null
    if (user?.role === 'admin') return ADMIN_DEFAULT_DATE_FORMAT
  } catch {
    // ignore parse errors and fallback
  }

  const stored = localStorage.getItem(DATE_FORMAT_STORAGE_KEY)
  return normalizeDateFormat(stored || ADMIN_DEFAULT_DATE_FORMAT)
}

const formatByPattern = (date, pattern) => {
  const resolved = pattern || ADMIN_DEFAULT_DATE_FORMAT
  const parts = {
    Y: String(date.getFullYear()),
    m: pad2(date.getMonth() + 1),
    d: pad2(date.getDate())
  }
  return resolved.replace(/[Ymd]/g, (token) => parts[token] || token)
}

export const formatDateDisplay = (value, fallback = 'N/A') => {
  const date = parseDateValue(value)
  if (!date) return fallback
  return formatByPattern(date, getActiveDateFormat())
}

export const formatShortDateDisplay = (value, fallback = '') => {
  const date = parseDateValue(value)
  if (!date) return fallback

  const pattern = getActiveDateFormat()
  const separator = pattern.includes('/') ? '/' : '-'

  if (pattern.startsWith('Y')) {
    return formatByPattern(date, `m${separator}d`)
  }
  if (pattern.endsWith('Y')) {
    return formatByPattern(date, pattern.replace(`${separator}Y`, ''))
  }
  return formatByPattern(date, `m${separator}d`)
}

export const formatDateTimeDisplay = (value, fallback = 'N/A') => {
  const date = parseDateValue(value)
  if (!date) return fallback
  const dateText = formatByPattern(date, getActiveDateFormat())
  const timeText = new Intl.DateTimeFormat('en-US', {
    hour: 'numeric',
    minute: '2-digit'
  }).format(date)
  return `${dateText}, ${timeText}`
}

export const formatExportDate = (value, fallback = '') => {
  const date = parseDateValue(value)
  if (!date) return fallback
  return `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`
}

export const formatRelativeTime = (value, fallback = '') => {
  const date = parseDateValue(value)
  if (!date) return fallback

  const now = new Date()
  const diffMs = date.getTime() - now.getTime()
  const absMs = Math.abs(diffMs)
  const minute = 60000
  const hour = 3600000
  const day = 86400000

  if (absMs < minute) return 'just now'

  const rtf = new Intl.RelativeTimeFormat('en-US', { numeric: 'auto' })
  if (absMs < hour) return rtf.format(Math.round(diffMs / minute), 'minute')
  if (absMs < day) return rtf.format(Math.round(diffMs / hour), 'hour')
  return rtf.format(Math.round(diffMs / day), 'day')
}
