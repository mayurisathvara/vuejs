<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <h3 class="page-title">SIM Management</h3>
        </div>
        <div class="header-actions">
          <button type="button" class="btn btn-light border btn-sm me-2" @click="refreshSims">
            <i class="fas fa-sync-alt me-2"></i>
            <span class="btn-text">Refresh</span>
          </button>
          <button
            v-if="selectedSims.length > 0" 
            type="button"
            class="btn btn-danger btn-sm me-2"
            @click="openBulkDeleteModal"
          >
            <i class="fas fa-trash-alt me-2"></i>
            <span class="btn-text">Delete Selected ({{ selectedSims.length }})</span>
          </button>
          <button type="button" class="btn btn-success btn-sm me-2" @click="handleExportCsv">
            <i class="fas fa-file-export me-2"></i>
            <span class="btn-text">Export</span>
          </button>
          <button type="button" class="btn btn-info btn-sm me-2" @click="openImportModal">
            <i class="fas fa-file-import me-2"></i>
            <span class="btn-text">Import</span>
          </button>
          <button type="button" class="btn btn-primary btn-sm" @click="openCreateModal">
            <i class="fas fa-plus me-2"></i>
            <span class="btn-text">Add</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="filters-card">
      <div class="filters-container">
        <div class="filter-item filter-search">
          <div class="search-box-modern">
            <i class="fas fa-search search-icon-modern"></i>
            <input
              v-model="searchQuery"
              type="text"
              class="form-control search-input-modern"
              placeholder="Search by mobile number or name..."
              @input="handleSearch"
            />
          </div>
        </div>
        <div v-if="isAdmin" class="filter-item">
          <div class="select-wrapper">
            <i class="fas fa-building filter-icon"></i>
            <select v-model="organizationFilter" class="form-select select-modern" @change="handleOrganizationFilter">
              <option value="">All Organizations</option>
              <option v-for="org in organizations" :key="org.id" :value="org.id">
                {{ org.name }}
              </option>
            </select>
          </div>
        </div>
        <div class="filter-item">
          <div class="select-wrapper">
            <i class="fas fa-sitemap filter-icon"></i>
            <select v-model="teamFilter" class="form-select select-modern" @change="handleTeamFilter">
              <option value="">All Teams</option>
              <option v-for="dept in teams" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>
        </div>
        <div class="filter-item filter-per-page">
          <div class="select-wrapper">
            <i class="fas fa-list filter-icon"></i>
            <select v-model="perPage" class="form-select select-modern" @change="handlePerPageChange">
              <option value="10">10 per page</option>
              <option value="25">25 per page</option>
              <option value="50">50 per page</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- SIMs Table -->
    <div class="card card-round">
      <div class="card-body p-0">
        <div class="table-responsive-wrapper">
          <div class="simple-table">
            <Table
              :data="simsStore.sims"
              :headers="simHeaders"
              :loading="simsStore.loading"
              :actions="{ edit: true, delete: true }"
              :checkboxes="true"
              :selectedRows="selectedSims"
              @edit="openEditModal"
              @delete="openDeleteModal"
              @selection-change="handleSelectionChange"
            >
          <template #cell-mobile="{ value }">
            <div class="d-flex align-items-center">
              <i class="fas fa-mobile-alt me-2 text-primary"></i>
              <span class="fw-bold">{{ value }}</span>
            </div>
          </template>
          <template #cell-name="{ value }">
            {{ value || 'N/A' }}
          </template>
          <template #cell-organization_name="{ row }">
            {{ row.organization ? row.organization.name : 'N/A' }}
          </template>
          <template #cell-team_name="{ row }">
            <span v-if="row.team" class="badge bg-info">{{ row.team.name }}</span>
            <span v-else class="text-muted">N/A</span>
          </template>
          <template #cell-status="{ value, row }">
            <div class="status-dropdown">
              <button 
                class="status-badge" 
                :class="`status-${normalizeStatus(value)}`"
                @click="toggleStatusDropdown(row.id)"
                type="button"
              >
                <span class="status-dot"></span>
                <span class="status-text">{{ formatStatusLabel(value) }}</span>
                <i class="fas fa-chevron-down status-icon"></i>
              </button>
              <div 
                v-if="activeDropdown === row.id" 
                class="status-dropdown-menu"
                :data-dropdown-id="row.id"
                @click.stop
              >
                <button 
                  class="status-dropdown-item"
                  :class="{ 'active': normalizeStatus(value) === 'active' }"
                  @click="updateSimStatus(row.id, 'active')"
                  type="button"
                >
                  <span class="status-dot status-dot-active"></span>
                  <span>Active</span>
                  <i v-if="normalizeStatus(value) === 'active'" class="fas fa-check ms-auto"></i>
                </button>
                <button 
                  class="status-dropdown-item"
                  :class="{ 'active': normalizeStatus(value) === 'inactive' }"
                  @click="updateSimStatus(row.id, 'inactive')"
                  type="button"
                >
                  <span class="status-dot status-dot-inactive"></span>
                  <span>Inactive</span>
                  <i v-if="normalizeStatus(value) === 'inactive'" class="fas fa-check ms-auto"></i>
                </button>
              </div>
            </div>
          </template>
          <template #cell-created_at="{ value }">
            {{ formatDate(value) }}
          </template>
            </Table>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Showing {{ (simsStore.pagination.current_page - 1) * simsStore.pagination.per_page + 1 }} to 
        {{ Math.min(simsStore.pagination.current_page * simsStore.pagination.per_page, simsStore.pagination.total) }} 
        of {{ simsStore.pagination.total }} entries
      </div>
      <Pagination
        :current-page="simsStore.pagination.current_page"
        :total-pages="simsStore.pagination.last_page"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Create/Edit SIM Modal -->
    <Modal
      :show="showSimModal"
      :title="isEditing ? 'Edit SIM' : 'Create SIM'"
      size="lg"
      @close="closeSimModal"
    >
      <form @submit.prevent="handleSubmit">
        <div class="row">
          <div class="col-md-6 mb-3">
            <InputField
              v-model="simForm.mobile"
              type="tel"
              label="Mobile Number"
              placeholder="Enter 10-digit mobile number"
              :error="errors.mobile"
              maxlength="10"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="simForm.name"
              type="text"
              label="Employee Name"
              placeholder="Enter employee name"
              :error="errors.name"
              required
            />
          </div>
          <div v-if="isAdmin" class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">
                Organization
                <span class="text-danger">*</span>
              </label>
              <select v-model="simForm.organization_id" class="form-select" :class="{ 'is-invalid': errors.organization_id }" @change="handleOrganizationChange" required>
                <option value="">Select Organization</option>
                <option v-for="org in organizations" :key="org.id" :value="org.id">
                  {{ org.name }}
                </option>
              </select>
              <div v-if="errors.organization_id" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.organization_id) }}
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">
                Team
                <span class="text-danger">*</span>
              </label>
              <select v-model="simForm.team_id" class="form-select" :class="{ 'is-invalid': errors.team_id }" :disabled="!simForm.organization_id" required>
                <option value="">Select Team</option>
                <option v-for="dept in availableTeams" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
              <div v-if="errors.team_id" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.team_id) }}
              </div>
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>
      </form>

      <template #footer>
        <Button variant="secondary" @click="closeSimModal">Cancel</Button>
        <Button
          variant="primary"
          :loading="simsStore.loading"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Update SIM' : 'Create SIM' }}
        </Button>
      </template>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete SIM"
      @close="closeDeleteModal"
      @confirm="handleDelete"
    >
      <div class="delete-modal-content">
        <p class="delete-question">Are you sure you want to delete this SIM?</p>
        <div v-if="simToDelete" class="sim-info-card">
          <div class="sim-mobile">{{ simToDelete.mobile }}</div>
          <div class="sim-name">{{ simToDelete.name }}</div>
        </div>
        <p class="warning-text">
          <i class="fas fa-exclamation-triangle me-2"></i>
          This action cannot be undone.
        </p>
      </div>
    </Modal>

    <!-- Bulk Delete Confirmation Modal -->
    <Modal
      :show="showBulkDeleteModal"
      title="Confirm Bulk Delete"
      @close="closeBulkDeleteModal"
      size="md"
      :showFooter="false"
    >
      <div class="delete-modal-content">
        <p class="delete-question">Are you sure you want to delete <strong>{{ selectedSims.length }}</strong> selected SIM(s)?</p>
        <div class="bulk-info-card">
          <i class="fas fa-exclamation-triangle icon-warning"></i>
          <span>This will permanently delete all selected SIM records.</span>
        </div>
        <p class="warning-text">
          <i class="fas fa-exclamation-triangle me-2"></i>
          This action cannot be undone.
        </p>
        
        <div class="d-flex justify-content-end gap-2 mt-4">
          <Button variant="secondary" @click="closeBulkDeleteModal">Cancel</Button>
          <Button variant="danger" @click="handleBulkDelete">
            <i class="fas fa-trash-alt me-2"></i>Delete {{ selectedSims.length }} SIM(s)
          </Button>
        </div>
      </div>
    </Modal>

    <!-- SIM Limit Reached Modal -->
    <Modal
      :show="showLimitModal"
      title="SIM Limit Reached"
      size="md"
      :showFooter="false"
      @close="closeLimitModal"
    >
      <div class="limit-modal-body">
        <!-- Info banner -->
        <div class="limit-info-banner">
          <div class="limit-info-icon">
            <i class="fas fa-lock"></i>
          </div>
          <div class="limit-info-text">
            <p class="limit-info-title">You've reached your active SIM limit.</p>
            <p class="limit-info-sub">
              To activate
              <strong v-if="limitModalTargetSim">{{ limitModalTargetSim.name || limitModalTargetSim.mobile }}</strong>
              <strong v-else>this SIM</strong>,
              select one currently active SIM below to deactivate first.
            </p>
          </div>
        </div>

        <!-- How it works steps -->
        <ol class="limit-steps">
          <li>Select a SIM from the list below to deactivate.</li>
          <li>Click <strong>Confirm Swap</strong> — the selected SIM is deactivated and your chosen SIM is activated instantly.</li>
          <li>To increase your limit, contact the administrator to upgrade your plan.</li>
        </ol>

        <!-- Active SIMs list -->
        <p class="limit-list-label">Currently active SIMs (select one to deactivate):</p>
        <div class="limit-sims-list">
          <label
            v-for="sim in limitModalActiveSims"
            :key="sim.id"
            class="limit-sim-row"
            :class="{ selected: selectedSimToDeactivate === sim.id }"
          >
            <input
              type="radio"
              :value="sim.id"
              v-model="selectedSimToDeactivate"
              class="limit-sim-radio"
            />
            <span class="limit-sim-dot"></span>
            <div class="limit-sim-info">
              <span class="limit-sim-mobile">{{ sim.mobile }}</span>
              <span class="limit-sim-name">{{ sim.name }}</span>
            </div>
            <i v-if="selectedSimToDeactivate === sim.id" class="fas fa-check-circle limit-check-icon"></i>
          </label>
          <p v-if="limitModalActiveSims.length === 0" class="text-muted text-center py-2">
            No active SIMs found.
          </p>
        </div>

        <!-- Footer actions -->
        <div class="limit-modal-footer">
          <button class="btn btn-secondary" @click="closeLimitModal" :disabled="swapLoading">
            Cancel
          </button>
          <button
            class="btn btn-primary"
            @click="handleSimSwap"
            :disabled="!selectedSimToDeactivate || swapLoading"
          >
            <span v-if="swapLoading"><i class="fas fa-spinner fa-spin me-2"></i>Swapping…</span>
            <span v-else><i class="fas fa-exchange-alt me-2"></i>Confirm Swap</span>
          </button>
        </div>
      </div>
    </Modal>

    <!-- Import CSV Modal -->
    <Modal
      :show="showImportModal"
      title="Import SIMs from CSV"
      size="lg"
      @close="closeImportModal"
      :showFooter="false"
    >
      <div class="import-modal-wrapper">
        <!-- CSV Requirements Info -->
        <div class="alert alert-info mb-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
              <i class="fas fa-info-circle me-2" style="font-size: 1.25rem;"></i>
              <h6 class="mb-0 fw-bold">CSV Format Requirements</h6>
            </div>
            <button class="btn btn-success btn-sm fw-semibold" @click="downloadSampleCsv">
              <i class="fas fa-download me-2"></i>
              Download Sample CSV
            </button>
          </div>
          <p class="mb-3">Your CSV file must include the following columns:</p>
          <div class="row g-3">
            <div class="col-md-6" v-if="isAdmin">
              <div class="csv-column-info">
                <i class="fas fa-building me-2"></i>
                <div>
                  <strong class="d-block">organization</strong>
                  <small class="text-muted">Organization name</small>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="csv-column-info">
                <i class="fas fa-sitemap me-2"></i>
                <div>
                  <strong class="d-block">team</strong>
                  <small class="text-muted">Team name</small>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="csv-column-info">
                <i class="fas fa-mobile-alt me-2"></i>
                <div>
                  <strong class="d-block">mobile</strong>
                  <small class="text-muted">Unique mobile number</small>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="csv-column-info">
                <i class="fas fa-tag me-2"></i>
                <div>
                  <strong class="d-block">name</strong>
                  <small class="text-muted">SIM card name</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- File Upload Section -->
        <div class="file-upload-section">
          <label class="section-label">Select CSV File <span class="required-mark">*</span></label>
          <div class="file-drop-area" :class="{ 'has-file': selectedFile }">
            <input
              type="file"
              class="file-input-hidden"
              accept=".csv"
              @change="handleFileSelect"
              ref="fileInput"
            />
            <div v-if="!selectedFile" class="drop-placeholder">
              <i class="fas fa-cloud-upload-alt"></i>
              <p class="drop-text">Click to browse or drag & drop CSV file</p>
              <p class="drop-hint">Maximum size: 10MB</p>
            </div>
            <div v-else class="file-selected">
              <div class="file-icon">
                <i class="fas fa-file-alt"></i>
              </div>
              <div class="file-info">
                <div class="file-name">{{ selectedFile.name }}</div>
                <div class="file-size">{{ (selectedFile.size / 1024).toFixed(2) }} KB</div>
              </div>
              <button class="btn-remove-file" @click.prevent="clearFile" type="button">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Progress Section -->
        <div v-if="importProgress.uploading" class="progress-section">
          <div class="progress-info">
            <span><i class="fas fa-spinner fa-spin me-2"></i>Processing CSV...</span>
            <strong>{{ importProgress.percent }}%</strong>
          </div>
          <div class="progress" style="height: 8px; border-radius: 10px;">
            <div
              class="progress-bar bg-success progress-bar-striped progress-bar-animated"
              :style="{ width: importProgress.percent + '%' }"
            ></div>
          </div>
        </div>

        <!-- Results Section -->
        <div v-if="importResults" class="results-section">
          <div v-if="importResults.imported > 0" class="success-card">
            <div class="result-icon success-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <div class="result-content">
              <div class="result-title">Import Successful!</div>
              <div class="result-subtitle">
                Imported {{ importResults.imported }} of {{ importResults.total_rows }} SIM(s)
              </div>
            </div>
          </div>

          <div v-if="importResults.errors && importResults.errors.length > 0" class="error-card">
            <div class="error-header">
              <i class="fas fa-exclamation-circle"></i>
              <strong>{{ importResults.errors.length }} Record(s) Failed</strong>
            </div>
            <div class="error-body">
              <div class="error-download-section">
                <div class="error-summary">
                  <i class="fas fa-info-circle me-2"></i>
                  <span>Some records failed to import. Download the error log to see details.</span>
                </div>
                <button class="btn btn-danger btn-sm mt-3" @click="downloadFailedRecords">
                  <i class="fas fa-download me-2"></i>
                  Download Failed Logs
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="modal-footer-actions">
          <Button variant="secondary" @click="closeImportModal">
            {{ importResults ? 'Close' : 'Cancel' }}
          </Button>
          <Button
            v-if="!importResults"
            variant="warning"
            @click="handleImportCsv"
            :disabled="!selectedFile || importProgress.uploading"
          >
            <i class="fas fa-upload me-2"></i>Start Import
          </Button>
          <Button
            v-else
            variant="success"
            @click="resetImport"
          >
            <i class="fas fa-redo me-2"></i>Import Another
          </Button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<style scoped>
.table-responsive-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Simple table look (match Call Reports) */
.simple-table :deep(.table-responsive) {
  border: 1px solid #e3e6f0;
  border-radius: 10px;
  overflow: hidden;
}

.simple-table :deep(table.table) {
  width: 100%;
  min-width: 1200px;
  border-collapse: collapse;
  margin: 0;
  font-size: 14px;
}

.simple-table :deep(table.table thead th) {
  background: #f8f9fa;
  color: #111827;
  font-weight: 600;
  text-transform: none !important;
  letter-spacing: normal !important;
  border-bottom: 1px solid #e3e6f0;
  border-right: 1px solid #e3e6f0;
  padding: 10px 12px !important;
  vertical-align: middle;
  white-space: nowrap;
}

.simple-table :deep(table.table tbody td) {
  border-top: 1px solid #e3e6f0;
  border-right: 1px solid #e3e6f0;
  padding: 10px 12px !important;
  vertical-align: middle;
  background: #fff;
}

.simple-table :deep(table.table thead th:last-child),
.simple-table :deep(table.table tbody td:last-child) {
  border-right: none;
}

.simple-table :deep(table.table tbody tr:hover) {
  background: transparent;
}
/* Badge styles are defined globally in app.css */

/* Header pill action buttons (Export/Import) */
.sim-pill-btn {
  border-radius: 999px;
  padding: 8px 16px;
  font-weight: 600;
  border: 0;
  box-shadow: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.sim-pill-success {
  background-color: rgba(var(--bs-success-rgb), 0.15);
  color: var(--bs-success);
}

.sim-pill-success:hover {
  background-color: rgba(var(--bs-success-rgb), 0.22);
  color: var(--bs-success);
}

.sim-pill-info {
  background-color: rgba(var(--bs-info-rgb), 0.15);
  color: var(--bs-info);
}

.sim-pill-info:hover {
  background-color: rgba(var(--bs-info-rgb), 0.22);
  color: var(--bs-info);
}

/* Delete Modal Styling */
.delete-modal-content {
  padding: 8px 0;
}

.delete-question {
  font-size: 15px;
  color: #3a3b45;
  margin-bottom: 20px;
  font-weight: 500;
  line-height: 1.5;
}

.sim-info-card {
  background-color: #f8f9fc;
  border: 1px solid #e3e6f0;
  border-radius: 8px;
  padding: 16px 20px;
  margin-bottom: 20px;
}

.sim-mobile {
  font-size: 16px;
  font-weight: 600;
  color: #2c2d3a;
  margin-bottom: 6px;
}

.sim-name {
  font-size: 14px;
  color: #6c757d;
}

.bulk-info-card {
  background-color: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 8px;
  padding: 14px 16px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.bulk-info-card .icon-warning {
  color: #856404;
  font-size: 18px;
  flex-shrink: 0;
}

.bulk-info-card span {
  color: #856404;
  font-size: 14px;
  font-weight: 500;
}

.warning-text {
  color: #dc3545;
  font-size: 14px;
  font-weight: 500;
  margin: 0;
  display: flex;
  align-items: center;
}

.warning-text i {
  color: #dc3545;
}

/* Import Modal Styles */
.import-modal-wrapper {
  padding: 0.5rem 0;
}

.csv-column-info {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  padding: 0.75rem;
  background: rgba(13, 110, 253, 0.05);
  border-radius: 6px;
  border-left: 3px solid #0d6efd;
}

.csv-column-info i {
  color: #0d6efd;
  font-size: 1.1rem;
  margin-top: 0.25rem;
}

.file-upload-section {
  margin-bottom: 1.5rem;
}

.section-label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #495057;
  font-size: 0.95rem;
}

.required-mark {
  color: #dc3545;
}

.file-drop-area {
  position: relative;
  border: 2px dashed #cbd5e0;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  background: #f8f9fa;
  transition: all 0.3s ease;
  cursor: pointer;
}

.file-drop-area:hover {
  border-color: #667eea;
  background: #f0f4ff;
}

.file-drop-area.has-file {
  border-style: solid;
  border-color: #28a745;
  background: #f0fff4;
  padding: 1.25rem;
}

.file-input-hidden {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.drop-placeholder {
  pointer-events: none;
}

.drop-placeholder i {
  font-size: 3rem;
  color: #667eea;
  margin-bottom: 1rem;
}

.drop-text {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.25rem;
  font-size: 1rem;
}

.drop-hint {
  color: #6c757d;
  font-size: 0.85rem;
  margin: 0;
}

.file-selected {
  display: flex;
  align-items: center;
  gap: 1rem;
  pointer-events: none;
}

.file-icon {
  background: #28a745;
  color: white;
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.file-icon i {
  font-size: 2rem;
  line-height: 1;
  color: white;
}

.file-info {
  flex: 1;
  text-align: left;
}

.file-name {
  font-weight: 600;
  color: #212529;
  font-size: 0.95rem;
  margin-bottom: 0.25rem;
}

.file-size {
  color: #6c757d;
  font-size: 0.85rem;
}

.btn-remove-file {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background: #dc3545;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  pointer-events: all;
  flex-shrink: 0;
}

.btn-remove-file:hover {
  background: #c82333;
  transform: scale(1.1);
}

.progress-section {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.progress-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
  color: #495057;
}

.results-section {
  margin-bottom: 1.5rem;
}

.success-card {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  color: white;
  box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
  margin-bottom: 1rem;
}

.result-icon {
  width: 60px;
  height: 60px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  flex-shrink: 0;
}

.result-content {
  flex: 1;
}

.result-title {
  font-size: 1.15rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.result-subtitle {
  font-size: 1rem;
  opacity: 0.95;
}

.error-card {
  background: white;
  border: 1px solid #f5c2c7;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.15);
}

.error-header {
  background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
  color: white;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1rem;
}

.error-header i {
  font-size: 1.3rem;
}

.error-body {
  padding: 1.25rem;
}

.error-list {
  max-height: 250px;
  overflow-y: auto;
}

.error-list::-webkit-scrollbar {
  width: 6px;
}

.error-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.error-list::-webkit-scrollbar-thumb {
  background: #dc3545;
  border-radius: 10px;
}

.error-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.65rem;
  margin-bottom: 0.5rem;
  background: #fff5f5;
  border-left: 3px solid #dc3545;
  border-radius: 6px;
  font-size: 0.9rem;
  color: #721c24;
}

.error-item:last-of-type {
  margin-bottom: 0;
}

.error-item i {
  color: #dc3545;
  margin-top: 2px;
  flex-shrink: 0;
}

.error-download-section {
  text-align: center;
}

.error-summary {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: #fff5f5;
  border: 1px solid #f5c2c7;
  border-radius: 8px;
  color: #721c24;
  font-size: 0.95rem;
}

.modal-footer-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding-top: 1rem;
  border-top: 1px solid #e9ecef;
}

/* Status Dropdown Styles */
.status-dropdown {
  position: relative;
  display: inline-flex;
  justify-content: center;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border: none;
  border-radius: 16px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  min-width: 80px;
  justify-content: center;
}

.status-badge .status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-badge .status-text {
  flex: 1;
  text-align: center;
}

.status-badge .status-icon {
  font-size: 8px;
  opacity: 0.7;
  transition: transform 0.2s ease;
}

.status-badge:hover .status-icon {
  opacity: 1;
}

.status-badge.status-active {
  background-color: #d4edda;
  color: #155724;
}

.status-badge.status-active .status-dot {
  background-color: #28a745;
}

.status-badge.status-active:hover {
  background-color: #c3e6cb;
  box-shadow: 0 2px 6px rgba(40, 167, 69, 0.2);
}

.status-badge.status-inactive {
  background-color: #f8d7da;
  color: #721c24;
}

.status-badge.status-inactive .status-dot {
  background-color: #dc3545;
}

.status-badge.status-inactive:hover {
  background-color: #f5c6cb;
  box-shadow: 0 2px 6px rgba(220, 53, 69, 0.2);
}

.status-dropdown-menu {
  position: fixed;
  background: white;
  border: 1px solid #e3e6f0;
  border-radius: 8px;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
  z-index: 9999;
  overflow: hidden;
  min-width: 140px;
  padding: 4px;
  animation: slideDown 0.2s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.status-dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 13px;
  font-weight: 500;
  border: none;
  background: transparent;
  border-radius: 6px;
  width: 100%;
  text-align: left;
  color: #495057;
}

.status-dropdown-item .status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-dropdown-item .status-dot-active {
  background-color: #28a745;
}

.status-dropdown-item .status-dot-inactive {
  background-color: #dc3545;
}

.status-dropdown-item:hover {
  background-color: #f8f9fa;
  color: #212529;
}

.status-dropdown-item.active {
  background-color: #e7f3ff;
  color: #0066cc;
  font-weight: 600;
}

.status-dropdown-item .fa-check {
  font-size: 12px;
  color: #0066cc;
}

/* ============================================================
   SIM Limit Reached Modal
   ============================================================ */
.limit-modal-body {
  padding: 0.25rem 0;
}

.limit-info-banner {
  display: flex;
  gap: 1rem;
  background: #fff8e6;
  border: 1px solid #ffd166;
  border-radius: 12px;
  padding: 1rem 1.1rem;
  margin-bottom: 1.1rem;
  align-items: flex-start;
}

.limit-info-icon {
  width: 40px;
  height: 40px;
  background: #ffd166;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #7a4f00;
  font-size: 1.1rem;
}

.limit-info-text {
  flex: 1;
}

.limit-info-title {
  font-weight: 700;
  color: #7a4f00;
  margin: 0 0 0.25rem;
  font-size: 0.97rem;
}

.limit-info-sub {
  color: #7a4f00;
  margin: 0;
  font-size: 0.9rem;
  line-height: 1.5;
}

.limit-steps {
  margin: 0 0 1rem 0;
  padding-left: 1.3rem;
  font-size: 0.88rem;
  color: #495057;
  line-height: 1.7;
}

.limit-list-label {
  font-weight: 600;
  font-size: 0.9rem;
  color: #495057;
  margin-bottom: 0.5rem;
}

.limit-sims-list {
  border: 1px solid #e3e6f0;
  border-radius: 10px;
  overflow: hidden;
  max-height: 240px;
  overflow-y: auto;
  margin-bottom: 1.2rem;
}

.limit-sims-list::-webkit-scrollbar {
  width: 5px;
}
.limit-sims-list::-webkit-scrollbar-thumb {
  background: #ced4da;
  border-radius: 10px;
}

.limit-sim-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.7rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid #f0f2f5;
  transition: background 0.15s;
  user-select: none;
}

.limit-sim-row:last-child {
  border-bottom: none;
}

.limit-sim-row:hover {
  background: #f8f9fa;
}

.limit-sim-row.selected {
  background: #e8f4ff;
  border-left: 3px solid #0d6efd;
}

.limit-sim-radio {
  display: none;
}

.limit-sim-dot {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid #ced4da;
  flex-shrink: 0;
  transition: all 0.15s;
  background: #fff;
}

.limit-sim-row.selected .limit-sim-dot {
  border-color: #0d6efd;
  background: #0d6efd;
  box-shadow: inset 0 0 0 3px #fff;
}

.limit-sim-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.limit-sim-mobile {
  font-weight: 600;
  font-size: 0.91rem;
  color: #212529;
}

.limit-sim-name {
  font-size: 0.82rem;
  color: #6c757d;
}

.limit-check-icon {
  color: #0d6efd;
  font-size: 1rem;
  flex-shrink: 0;
}

.limit-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.65rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e9ecef;
}
</style>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useSimsStore } from '@/stores/sims'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/Button.vue'
import Table from '@/components/Table.vue'
import Modal from '@/components/Modal.vue'
import InputField from '@/components/InputField.vue'
import Pagination from '@/components/Pagination.vue'
import api from '@/services/api'
import { showSuccess, showError } from '@/services/toast'

const simsStore = useSimsStore()
const authStore = useAuthStore()

// Role-based access
const userRole = computed(() => authStore.user?.role || 'user')
const isAdmin = computed(() => userRole.value === 'admin')
const isOrganization = computed(() => userRole.value === 'organization')
const isManager = computed(() => userRole.value === 'manager')

// Search and filters
const searchQuery = ref('')
const organizationFilter = ref('')
const teamFilter = ref('')
const perPage = ref(10)
const searchTimeout = ref(null)

// Status dropdown state
const activeDropdown = ref(null)

// Modal states
const showSimModal = ref(false)
const showDeleteModal = ref(false)
const showBulkDeleteModal = ref(false)
const showImportModal = ref(false)
const isEditing = ref(false)
const simToDelete = ref(null)

// Bulk selection
const selectedSims = ref([])

// Import state
const selectedFile = ref(null)
const fileInput = ref(null)
const importProgress = reactive({
  uploading: false,
  percent: 0
})
const importResults = ref(null)

// Form data
const simForm = reactive({
  mobile: '',
  name: '',
  organization_id: '',
  team_id: ''
})

// Organizations and teams data
const organizations = ref([])
const teams = ref([])
const availableTeams = ref([])

const errors = ref({})
const errorMessage = ref('')

// Helper function to get error message
const getErrorMessage = (error) => {
  if (Array.isArray(error)) {
    return error[0]
  }
  return error
}

const simHeaders = computed(() => {
  const headers = [
    { key: 'mobile', label: 'Mobile Number', class: 'text-start' },
    { key: 'name', label: 'Name', class: 'text-start' },
  ]
  
  // Show organization column only for admin
  if (isAdmin.value) {
    headers.push({ key: 'organization_name', label: 'Organization', class: 'text-start' })
  }
  
  headers.push(
    { key: 'team_name', label: 'Team', class: 'text-start' },
    { key: 'status', label: 'Status', class: 'text-start' },
    { key: 'created_at', label: 'Created At', class: 'text-start' }
  )
  
  return headers
})

const normalizeStatus = (status) => {
  if (status === 'inactive' || status === 'inactive_plan_limit') return 'inactive'
  return 'active'
}

const formatStatusLabel = (status) => {
  if (status === 'inactive_plan_limit') return 'Inactive (Limit)'
  const normalized = normalizeStatus(status)
  return normalized.charAt(0).toUpperCase() + normalized.slice(1)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const toggleStatusDropdown = (simId) => {
  if (activeDropdown.value === simId) {
    activeDropdown.value = null
  } else {
    activeDropdown.value = simId
    nextTick(() => {
      adjustDropdownPosition(simId)
    })
  }
}

const adjustDropdownPosition = (simId) => {
  const dropdown = document.querySelector(`[data-dropdown-id="${simId}"]`)
  const indicator = dropdown?.closest('.status-dropdown')
  if (!dropdown || !indicator) return

  const indicatorRect = indicator.getBoundingClientRect()
  const viewportHeight = window.innerHeight
  const dropdownHeight = 80

  const left = indicatorRect.left
  const top = indicatorRect.bottom + 4
  const bottom = viewportHeight - indicatorRect.top + 4

  dropdown.style.setProperty('--dropdown-left', `${left}px`)
  dropdown.style.setProperty('--dropdown-top', `${top}px`)
  dropdown.style.setProperty('--dropdown-bottom', `${bottom}px`)

  if (top + dropdownHeight > viewportHeight) {
    dropdown.setAttribute('data-position', 'up')
  } else {
    dropdown.setAttribute('data-position', 'down')
  }
}

// SIM limit modal state
const showLimitModal = ref(false)
const limitModalActiveSims = ref([])  // active SIMs returned by the server
const limitModalTargetId  = ref(null) // the SIM the user is trying to activate
const limitModalTargetSim = ref(null) // full sim object of target
const selectedSimToDeactivate = ref(null)
const swapLoading = ref(false)

const updateSimStatus = async (simId, newStatus) => {
  activeDropdown.value = null

  // If activating, find the sim object so we can show its name in the modal if needed
  const targetSim = simsStore.sims.find(s => s.id === simId)

  try {
    await api.put(`/sims/${simId}/status`, { status: newStatus })
    fetchSims()
  } catch (error) {
    const data = error.response?.data

    if (error.response?.status === 422 && data?.code === 'SIM_LIMIT_REACHED') {
      // Open the swap popup instead of a toast
      limitModalTargetId.value  = simId
      limitModalTargetSim.value = targetSim || null
      limitModalActiveSims.value = data.active_sims || []
      selectedSimToDeactivate.value = null
      showLimitModal.value = true
    } else if (error.response?.status === 403 && data?.code === 'SUBSCRIPTION_EXPIRED') {
      showError(data.message || 'Your subscription has expired. Please contact administrator.')
    } else {
      showError(data?.message || 'Failed to update SIM status')
    }
  }
}

const closeLimitModal = () => {
  showLimitModal.value = false
  limitModalTargetId.value  = null
  limitModalTargetSim.value = null
  limitModalActiveSims.value = []
  selectedSimToDeactivate.value = null
}

const handleSimSwap = async () => {
  if (!selectedSimToDeactivate.value) {
    showError('Please select a SIM to deactivate.')
    return
  }

  swapLoading.value = true
  try {
    await api.post('/sims/swap', {
      activate_sim_id:   limitModalTargetId.value,
      deactivate_sim_id: selectedSimToDeactivate.value,
    })
    showSuccess('SIM swap completed successfully.')
    closeLimitModal()
    fetchSims()
  } catch (error) {
    showError(error.response?.data?.message || 'Swap failed. Please try again.')
  } finally {
    swapLoading.value = false
  }
}

const handleClickOutside = (event) => {
  if (!event.target.closest('.status-dropdown')) {
    activeDropdown.value = null
  }
}

const fetchSims = async () => {
  await simsStore.fetchSims(
    simsStore.pagination.current_page,
    searchQuery.value,
    organizationFilter.value,
    teamFilter.value,
    perPage.value
  )
}

const refreshSims = () => {
  searchQuery.value = ''
  organizationFilter.value = ''
  teamFilter.value = ''
  fetchSims()
}

const handleSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  searchTimeout.value = setTimeout(() => {
    fetchSims()
  }, 500)
}

const handleOrganizationFilter = async () => {
  teamFilter.value = ''
  
  if (organizationFilter.value) {
    await fetchTeamsByOrganization(organizationFilter.value)
  } else {
    teams.value = []
  }
  
  fetchSims()
}

const handleTeamFilter = () => {
  fetchSims()
}

const handlePerPageChange = () => {
  fetchSims()
}

const handlePageChange = (page) => {
  simsStore.fetchSims(page, searchQuery.value, organizationFilter.value, teamFilter.value, perPage.value)
}

const fetchTeamsByOrganization = async (organizationId) => {
  try {
    const response = await api.get(`/sims/teams/by-organization?organization_id=${organizationId}`)
    teams.value = response.data
  } catch (error) {
    console.error('Error fetching teams:', error)
    teams.value = []
  }
}

const openCreateModal = async () => {
  isEditing.value = false
  resetForm()
  
  // For non-admin users, fetch teams after organization is auto-set
  if ((isOrganization.value || isManager.value) && organizations.value.length > 0) {
    simForm.organization_id = organizations.value[0].id
    await handleOrganizationChange()
  }
  
  showSimModal.value = true
}

const openEditModal = async (sim) => {
  isEditing.value = true
  simForm.mobile = sim.mobile
  simForm.name = sim.name
  simForm.organization_id = sim.organization_id
  simForm.team_id = sim.team_id || ''
  simToDelete.value = sim
  
  // Fetch teams for the sim's organization
  if (simForm.organization_id) {
    try {
      const response = await api.get(`/sims/teams/by-organization?organization_id=${simForm.organization_id}`)
      availableTeams.value = response.data
    } catch (error) {
      console.error('Error fetching teams:', error)
      availableTeams.value = []
    }
  }
  
  showSimModal.value = true
}

const openDeleteModal = (sim) => {
  simToDelete.value = sim
  showDeleteModal.value = true
}

const closeSimModal = () => {
  showSimModal.value = false
  resetForm()
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  simToDelete.value = null
}

// Import CSV functions
const openImportModal = () => {
  showImportModal.value = true
  selectedFile.value = null
  importResults.value = null
  importProgress.uploading = false
  importProgress.percent = 0
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const closeImportModal = () => {
  const shouldRefresh = importResults.value && importResults.value.imported > 0
  
  showImportModal.value = false
  selectedFile.value = null
  importResults.value = null
  importProgress.uploading = false
  importProgress.percent = 0
  
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  
  // Refresh the SIMs list if any were imported
  if (shouldRefresh) {
    fetchSims()
  }
}

const clearFile = () => {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const resetImport = () => {
  selectedFile.value = null
  importResults.value = null
  importProgress.uploading = false
  importProgress.percent = 0
  
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (!file.name.endsWith('.csv')) {
      showError('Please select a CSV file')
      event.target.value = ''
      return
    }
    selectedFile.value = file
    importResults.value = null
  }
}

const handleImportCsv = async () => {
  if (!selectedFile.value) {
    showError('Please select a CSV file')
    return
  }

  const formData = new FormData()
  formData.append('file', selectedFile.value)

  try {
    importProgress.uploading = true
    importProgress.percent = 0

    const progressInterval = setInterval(() => {
      if (importProgress.percent < 90) {
        importProgress.percent += 10
      }
    }, 100)

    const response = await api.post('/sims/import-csv', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    clearInterval(progressInterval)
    importProgress.percent = 100
    importProgress.uploading = false

    importResults.value = response.data

    if (response.data.imported > 0) {
      showSuccess(response.data.message)
    } else if (response.data.errors && response.data.errors.length > 0) {
      showError('Import completed with errors. Please check the error list below.')
    }

  } catch (error) {
    importProgress.uploading = false
    console.error('Error importing CSV:', error)
    
    if (error.response?.data?.message) {
      showError(error.response.data.message)
    } else {
      showError('Error importing CSV file')
    }
    
    if (error.response?.data?.required_columns) {
      importResults.value = {
        imported: 0,
        total_rows: 0,
        errors: [`Required columns: ${error.response.data.required_columns.join(', ')}`]
      }
    }
  }
}

// Export CSV function
const handleExportCsv = async () => {
  try {
    // Build params for the current filters - use very large per_page to get all records
    const params = {
      search: searchQuery.value || '',
      organization_id: organizationFilter.value || '',
      team_id: teamFilter.value || '',
      per_page: 999999 // Get all records matching filters, not just current page
    }

    // Fetch all SIMs based on current filters
    const response = await api.get('/sims', { params })
    const simsData = response.data.data || response.data

    if (!simsData || simsData.length === 0) {
      showError('No data to export')
      return
    }

    // Prepare CSV content
    let csvContent = ''
    
    // CSV headers based on role
    if (isAdmin.value) {
      csvContent = 'Mobile Number,Name,Organization,Team,Created At\n'
      
      simsData.forEach(sim => {
        const mobile = sim.mobile || ''
        const name = (sim.name || '').replace(/"/g, '""') // Escape quotes
        const organization = (sim.organization?.name || '').replace(/"/g, '""')
        const team = (sim.team?.name || '').replace(/"/g, '""')
        const createdAt = sim.created_at ? new Date(sim.created_at).toLocaleDateString('en-US') : ''
        
        csvContent += `"${mobile}","${name}","${organization}","${team}","${createdAt}"\n`
      })
    } else {
      csvContent = 'Mobile Number,Name,Team,Created At\n'
      
      simsData.forEach(sim => {
        const mobile = sim.mobile || ''
        const name = (sim.name || '').replace(/"/g, '""') // Escape quotes
        const team = (sim.team?.name || '').replace(/"/g, '""')
        const createdAt = sim.created_at ? new Date(sim.created_at).toLocaleDateString('en-US') : ''
        
        csvContent += `"${mobile}","${name}","${team}","${createdAt}"\n`
      })
    }

    // Create and download the file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    const timestamp = new Date().toISOString().slice(0, 10)
    link.setAttribute('href', url)
    link.setAttribute('download', `sims_export_${timestamp}.csv`)
    link.style.visibility = 'hidden'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    showSuccess(`Exported ${simsData.length} SIM(s) successfully`)
  } catch (error) {
    console.error('Error exporting SIMs:', error)
    showError('Failed to export SIM cards')
  }
}

const downloadSampleCsv = () => {
  let csvContent = ''
  
  if (isAdmin.value) {
    csvContent = 'organization,team,mobile,name\n'
    csvContent += 'Dosti Enterprise,Sales,9876543210,Sales SIM 1\n'
    csvContent += 'Dosti Enterprise,Marketing,9876543211,Marketing SIM 1\n'
  } else {
    csvContent = 'team,mobile,name\n'
    csvContent += 'Sales,9876543210,Sales SIM 1\n'
    csvContent += 'Marketing,9876543211,Marketing SIM 1\n'
  }

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  
  link.setAttribute('href', url)
  link.setAttribute('download', 'sim_import_sample.csv')
  link.style.visibility = 'hidden'
  
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const downloadFailedRecords = () => {
  if (!importResults.value || !importResults.value.errors || importResults.value.errors.length === 0) {
    return
  }

  // Create CSV content with failed records
  let csvContent = 'Error\n'
  
  importResults.value.errors.forEach(error => {
    // Escape quotes and wrap in quotes
    const escapedError = error.replace(/"/g, '""')
    csvContent += `"${escapedError}"\n`
  })

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  
  const timestamp = new Date().toISOString().slice(0, 10)
  link.setAttribute('href', url)
  link.setAttribute('download', `sim_import_failed_records_${timestamp}.csv`)
  link.style.visibility = 'hidden'
  
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  
  showSuccess('Failed records CSV downloaded successfully')
}

const resetForm = () => {
  simForm.mobile = ''
  simForm.name = ''
  simForm.organization_id = ''
  simForm.team_id = ''
  availableTeams.value = []
  errors.value = {}
  errorMessage.value = ''
}

const handleOrganizationChange = async () => {
  simForm.team_id = ''
  availableTeams.value = []
  
  if (simForm.organization_id) {
    try {
      const response = await api.get(`/sims/teams/by-organization?organization_id=${simForm.organization_id}`)
      availableTeams.value = response.data
    } catch (error) {
      console.error('Error fetching teams:', error)
      availableTeams.value = []
    }
  }
}

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''

  // Client-side validation
  const mobile = (simForm.mobile || '').trim()
  if (!mobile) {
    errors.value = { mobile: ['Mobile number is required.'] }
    return
  }
  if (!/^\d{10}$/.test(mobile)) {
    errors.value = { mobile: ['Mobile number must be exactly 10 digits.'] }
    return
  }

  try {
    if (isEditing.value) {
      await simsStore.updateSim(simToDelete.value.id, simForm)
      showSuccess('SIM updated successfully')
    } else {
      await simsStore.createSim(simForm)
      showSuccess('SIM created successfully')
    }
    
    closeSimModal()
    await fetchSims()
  } catch (error) {
    console.error('Error submitting form:', error)
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'An error occurred'
    }
  }
}

const handleDelete = async () => {
  try {
    await simsStore.deleteSim(simToDelete.value.id)
    showSuccess('SIM deleted successfully')
    closeDeleteModal()
    await fetchSims()
  } catch (error) {
    console.error('Error deleting sim:', error)
    showError('Failed to delete SIM')
  }
}

const handleSelectionChange = (selected) => {
  selectedSims.value = selected
}

const openBulkDeleteModal = () => {
  if (selectedSims.value.length === 0) {
    showError('Please select at least one SIM to delete')
    return
  }
  showBulkDeleteModal.value = true
}

const closeBulkDeleteModal = () => {
  showBulkDeleteModal.value = false
}

const handleBulkDelete = async () => {
  try {
    const ids = selectedSims.value.map(sim => sim.id)
    await api.post('/sims/bulk-delete', { ids })
    showSuccess(`Successfully deleted ${selectedSims.value.length} SIM(s)`)
    selectedSims.value = []
    closeBulkDeleteModal()
    await fetchSims()
  } catch (error) {
    console.error('Error deleting SIMs:', error)
    showError('Failed to delete selected SIMs')
  }
}

const fetchOrganizations = async () => {
  // Only admin users need to fetch organizations list
  if (!isAdmin.value) {
    return
  }
  
  try {
    const response = await api.get('/organizations', { params: { per_page: 1000 } })
    organizations.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching organizations:', error)
    organizations.value = []
  }
}

onMounted(() => {
  // Start fetching sims immediately (non-blocking)
  fetchSims()
  
  // For admin users, fetch organizations first
  if (isAdmin.value) {
    fetchOrganizations()
  } else if (isOrganization.value) {
    // For organization users, fetch teams directly using their organization
    fetchTeamsByOrganization()
  }

  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
