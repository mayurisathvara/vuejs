<template>
  <div class="page-container">
    <div class="page-header">
      <div class="header-content d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="header-title">
          <h3 class="page-title">Generated Reports</h3>
          <p class="page-subtitle">Your queued export files, ready for download.</p>
        </div>
        <button type="button" class="hdr-btn hdr-btn--ghost" @click="fetchReports" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i><span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="!loading && reports.length === 0" class="empty-state">
      <div class="empty-state__icon"><i class="fas fa-file-download"></i></div>
      <h5 class="empty-state__title">No reports yet</h5>
      <p class="empty-state__desc">Queue an export from the Call Report or Summary Report page and your files will appear here.</p>
    </div>

    <!-- Reports table -->
    <div v-else class="reports-card">
      <div v-if="loading && reports.length === 0" class="reports-loading">
        <i class="fas fa-spinner fa-spin me-2"></i> Loading reports…
      </div>

      <div class="table-responsive">
        <table class="reports-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Report</th>
              <th>Format</th>
              <th>Status</th>
              <th>Date Range</th>
              <th>Generated At</th>
              <th>Expires At</th>
              <th>Size</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(report, index) in reports" :key="report.id">
              <td class="text-muted small">{{ index + 1 }}</td>
              <td>
                <span class="report-type-badge" :class="report.report_type === 'call_report' ? 'badge--call' : 'badge--summary'">
                  <i class="fas" :class="report.report_type === 'call_report' ? 'fa-phone' : 'fa-chart-bar'"></i>
                  {{ report.report_label }}
                </span>
              </td>
              <td>
                <span class="format-badge">
                  <i class="fas" :class="report.format === 'excel' ? 'fa-file-excel text-success' : 'fa-file-alt text-primary'"></i>
                  {{ report.format_label }}
                </span>
              </td>
              <td>
                <span class="status-badge" :class="statusClass(report.status)">
                  <i class="fas fa-sm me-1" :class="statusIcon(report.status)"></i>
                  {{ statusLabel(report.status) }}
                </span>
              </td>
              <td class="small text-muted">{{ filterSummary(report.filters) }}</td>
              <td class="small">{{ report.generated_at ? formatDate(report.generated_at) : '—' }}</td>
              <td class="small" :class="isExpiringSoon(report.expires_at) ? 'text-warning fw-semibold' : 'text-muted'">
                {{ report.expires_at ? formatDate(report.expires_at) : '—' }}
              </td>
              <td class="small text-muted">{{ report.file_size ? formatSize(report.file_size) : '—' }}</td>
              <td class="text-end">
                <div class="d-flex align-items-center justify-content-end gap-1">
                  <!-- Download -->
                  <button
                    v-if="report.status === 'ready' && !report.is_expired"
                    class="action-btn action-btn--download"
                    @click="downloadReport(report)"
                    :disabled="downloading === report.id"
                    title="Download"
                  >
                    <i class="fas" :class="downloading === report.id ? 'fa-spinner fa-spin' : 'fa-download'"></i>
                  </button>

                  <!-- Generating indicator -->
                  <span v-if="report.status === 'generating' || report.status === 'pending'" class="generating-indicator">
                    <i class="fas fa-circle-notch fa-spin me-1"></i>
                    {{ report.status === 'pending' ? 'Queued' : 'Generating…' }}
                  </span>

                  <!-- Failed badge -->
                  <span v-if="report.status === 'failed'" class="failed-tip" :title="report.error_message">
                    <i class="fas fa-exclamation-circle me-1"></i> Failed
                  </span>

                  <!-- Expired badge -->
                  <span v-if="report.is_expired" class="expired-badge">
                    <i class="fas fa-clock me-1"></i> Expired
                  </span>

                  <!-- Delete -->
                  <button
                    class="action-btn action-btn--delete"
                    @click="confirmDelete(report)"
                    :disabled="deleting === report.id"
                    title="Delete"
                  >
                    <i class="fas" :class="deleting === report.id ? 'fa-spinner fa-spin' : 'fa-trash'"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="reports-pagination">
        <button class="pagination-btn" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">
          <i class="fas fa-chevron-left"></i>
        </button>
        <span class="pagination-info">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
        <button class="pagination-btn" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Auto-refresh notice for in-progress reports -->
    <p v-if="hasInProgress" class="auto-refresh-notice">
      <i class="fas fa-info-circle me-1"></i>
      Reports are being generated — this page refreshes every 10 seconds.
    </p>

    <!-- Delete confirm modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="confirm-modal">
        <h5 class="confirm-modal__title">Delete Report?</h5>
        <p class="confirm-modal__desc">
          This will permanently delete <strong>{{ deleteTarget?.file_name }}</strong>.
          This action cannot be undone.
        </p>
        <div class="confirm-modal__actions">
          <button class="hdr-btn hdr-btn--ghost" @click="showDeleteModal = false">Cancel</button>
          <button class="hdr-btn hdr-btn--danger" @click="doDelete" :disabled="deleting === deleteTarget?.id">
            <i class="fas fa-trash me-1"></i> Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import api from '@/services/api'
import { showSuccess, showError } from '@/services/toast'

const reports     = ref([])
const loading     = ref(false)
const downloading = ref(null)
const deleting    = ref(null)
const pagination  = ref({ current_page: 1, last_page: 1 })
const currentPage = ref(1)

const showDeleteModal = ref(false)
const deleteTarget    = ref(null)

let autoRefreshTimer = null

const hasInProgress = computed(() =>
  reports.value.some(r => r.status === 'pending' || r.status === 'generating')
)

const fetchReports = async (page = currentPage.value) => {
  loading.value = true
  try {
    const resp = await api.get('/generated-reports', { params: { page } })
    reports.value    = resp.data.data
    pagination.value = resp.data
    currentPage.value = resp.data.current_page
  } catch {
    showError('Failed to load reports.')
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  currentPage.value = page
  fetchReports(page)
}

const downloadReport = async (report) => {
  downloading.value = report.id
  try {
    const resp = await api.get(`/generated-reports/${report.id}/download`, { responseType: 'blob' })
    const url  = window.URL.createObjectURL(new Blob([resp.data]))
    const link = document.createElement('a')
    link.href  = url
    link.setAttribute('download', report.file_name)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch {
    showError('Download failed. The file may have expired.')
  } finally {
    downloading.value = null
  }
}

const confirmDelete = (report) => {
  deleteTarget.value  = report
  showDeleteModal.value = true
}

const doDelete = async () => {
  if (!deleteTarget.value) return
  deleting.value = deleteTarget.value.id
  try {
    await api.delete(`/generated-reports/${deleteTarget.value.id}`)
    showSuccess('Report deleted.')
    showDeleteModal.value = false
    deleteTarget.value    = null
    await fetchReports()
  } catch {
    showError('Failed to delete report.')
  } finally {
    deleting.value = null
  }
}

// ── Helpers ──────────────────────────────────────────────────────────────────

const statusLabel = (status) => ({
  pending:    'Queued',
  generating: 'Generating',
  ready:      'Ready',
  failed:     'Failed',
})[status] ?? status

const statusClass = (status) => ({
  pending:    'status--queued',
  generating: 'status--generating',
  ready:      'status--ready',
  failed:     'status--failed',
})[status] ?? ''

const statusIcon = (status) => ({
  pending:    'fa-clock',
  generating: 'fa-circle-notch fa-spin',
  ready:      'fa-check-circle',
  failed:     'fa-times-circle',
})[status] ?? 'fa-question-circle'

const filterSummary = (filters) => {
  if (!filters) return '—'
  const parts = []
  if (filters.start_date_time) parts.push(formatDateShort(filters.start_date_time))
  if (filters.end_date_time)   parts.push(formatDateShort(filters.end_date_time))
  return parts.length ? parts.join(' → ') : 'All dates'
}

const formatDate = (iso) => {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-IN', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

const formatDateShort = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
}

const formatSize = (bytes) => {
  if (!bytes) return '—'
  if (bytes < 1024)        return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB'
}

const isExpiringSoon = (expiresAt) => {
  if (!expiresAt) return false
  const diff = new Date(expiresAt) - new Date()
  return diff > 0 && diff < 24 * 60 * 60 * 1000
}

// ── Auto-refresh for in-progress reports ─────────────────────────────────────

const startAutoRefresh = () => {
  if (autoRefreshTimer) return
  autoRefreshTimer = setInterval(async () => {
    if (!hasInProgress.value) {
      clearInterval(autoRefreshTimer)
      autoRefreshTimer = null
      return
    }
    await fetchReports()
  }, 10000)
}

onMounted(async () => {
  await fetchReports()
  if (hasInProgress.value) {
    startAutoRefresh()
  }
})

onUnmounted(() => {
  if (autoRefreshTimer) {
    clearInterval(autoRefreshTimer)
  }
})
</script>

<style scoped>
.hdr-btn { display: inline-flex; align-items: center; gap: 7px; padding: 0 14px; height: 36px; border-radius: 10px; font-size: 0.82rem; font-weight: 700; border: none; cursor: pointer; transition: background 0.15s, box-shadow 0.15s, border-color 0.15s; white-space: nowrap; line-height: 1; }
.hdr-btn:disabled { opacity: 0.55; cursor: not-allowed; }
.hdr-btn i { font-size: 0.8rem; }
.hdr-btn--ghost { background: #fff; color: #374151; border: 1.5px solid #e5e7eb; }
.hdr-btn--ghost:hover:not(:disabled) { background: #f9fafb; border-color: #d1d5db; }
.hdr-btn--danger { background: #fee2e2; color: #dc2626; border: 1.5px solid #fecaca; }
.hdr-btn--danger:hover:not(:disabled) { background: #fecaca; }

.page-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin: 0; }
.page-subtitle { font-size: 0.875rem; color: #64748b; margin: 2px 0 0; }

/* ── Reports card ────────────────────────────────────────── */
.reports-card {
  background: #fff;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(15,23,42,0.05);
}

.reports-loading {
  padding: 2rem;
  color: #64748b;
  text-align: center;
}

/* ── Table ───────────────────────────────────────────────── */
.reports-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.reports-table thead th {
  background: #f8fafc;
  color: #64748b;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 10px 14px;
  border-bottom: 1.5px solid #e2e8f0;
  white-space: nowrap;
}
.reports-table tbody td {
  padding: 11px 14px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
  color: #374151;
}
.reports-table tbody tr:last-child td { border-bottom: none; }
.reports-table tbody tr:hover td { background: #f8fafc; }

/* ── Badges ──────────────────────────────────────────────── */
.report-type-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
}
.badge--call    { background: #eff6ff; color: #1d4ed8; }
.badge--summary { background: #f0fdf4; color: #15803d; }

.format-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 9px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.status--queued     { background: #f1f5f9; color: #64748b; }
.status--generating { background: #eff6ff; color: #2563eb; }
.status--ready      { background: #dcfce7; color: #16a34a; }
.status--failed     { background: #fee2e2; color: #dc2626; }

/* ── Action buttons ──────────────────────────────────────── */
.action-btn {
  width: 30px;
  height: 30px;
  border: none;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 13px;
  transition: background 0.15s;
}
.action-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.action-btn--download { background: #eff6ff; color: #2563eb; }
.action-btn--download:hover:not(:disabled) { background: #dbeafe; }
.action-btn--delete   { background: #fee2e2; color: #dc2626; }
.action-btn--delete:hover:not(:disabled)   { background: #fecaca; }

.generating-indicator {
  font-size: 12px;
  color: #2563eb;
  font-weight: 600;
}
.failed-tip {
  font-size: 12px;
  color: #dc2626;
  font-weight: 600;
  cursor: help;
}
.expired-badge {
  font-size: 12px;
  color: #92400e;
  font-weight: 600;
  background: #fef3c7;
  padding: 2px 8px;
  border-radius: 20px;
}

/* ── Pagination ──────────────────────────────────────────── */
.reports-pagination {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 12px 16px;
  border-top: 1px solid #f1f5f9;
  justify-content: flex-end;
}
.pagination-btn {
  width: 30px;
  height: 30px;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
}
.pagination-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.pagination-info { font-size: 13px; color: #64748b; }

/* ── Empty state ─────────────────────────────────────────── */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: #fff;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
}
.empty-state__icon {
  font-size: 2.5rem;
  color: #cbd5e1;
  margin-bottom: 1rem;
}
.empty-state__title { font-size: 1.1rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem; }
.empty-state__desc  { font-size: 0.875rem; color: #64748b; max-width: 420px; margin: 0 auto; }

/* ── Auto-refresh notice ─────────────────────────────────── */
.auto-refresh-notice {
  font-size: 13px;
  color: #2563eb;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  padding: 8px 14px;
  margin-top: 1rem;
}

/* ── Delete modal ────────────────────────────────────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15,23,42,0.4);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
}
.confirm-modal {
  background: #fff;
  border-radius: 14px;
  padding: 1.75rem;
  width: 400px;
  max-width: 92vw;
  box-shadow: 0 20px 60px rgba(15,23,42,0.18);
}
.confirm-modal__title { font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; }
.confirm-modal__desc  { font-size: 14px; color: #475569; margin-bottom: 1.25rem; }
.confirm-modal__actions { display: flex; gap: 0.5rem; justify-content: flex-end; }
</style>
