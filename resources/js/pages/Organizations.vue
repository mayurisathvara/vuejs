<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <h3 class="page-title">Organizations Management</h3>
        </div>
        <div class="header-actions">
          <button type="button" class="hdr-btn hdr-btn--ghost" @click="refreshOrganizations">
            <i class="fas fa-sync-alt"></i><span>Refresh</span>
          </button>
          <button type="button" class="hdr-btn hdr-btn--add" @click="openCreateModal">
            <i class="fas fa-plus"></i><span>Add</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="filters-card">
      <div class="filters-container filters-container-simple">
        <div class="filter-item filter-search">
          <div class="search-box-modern">
            <i class="fas fa-search search-icon-modern"></i>
            <input
              v-model="searchQuery"
              type="text"
              class="form-control search-input-modern"
              placeholder="Search organizations by name, email, or mobile..."
              @input="handleSearch"
            />
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

    <!-- Organizations Table -->
    <div class="card card-round users-table-card">
      <div class="card-body p-0">
        <div class="table-responsive-wrapper">
          <div class="simple-table">
            <Table
              :data="organizationsStore.organizations"
              :headers="organizationHeaders"
              :loading="organizationsStore.loading"
              :actions="{ edit: true, delete: true }"
              @edit="openEditModal"
              @delete="openDeleteModal"
            >
          <template #actions="{ row }">
            <div class="action-buttons">
              <button
                class="assign-sim-btn"
                type="button"
                @click="goToSettings(row.id)"
                title="Settings"
              >
                <i class="fas fa-cog"></i>
                <span>Settings</span>
              </button>
              <button
                class="action-menu-trigger"
                type="button"
                @click.stop="toggleActionMenu(row.id, $event)"
                aria-label="Open actions"
                title="More actions"
              >
                <i class="fas fa-ellipsis-h"></i>
              </button>
              <div v-if="activeActionMenu === row.id" class="action-menu" :style="actionMenuPosition" @click.stop>
                <button type="button" class="action-menu-item" @click="openEditFromMenu(row)">
                  <i class="fas fa-pen"></i>
                  <span>Edit</span>
                </button>
                <button type="button" class="action-menu-item action-menu-item-login" @click="openImpersonateModal(row)">
                  <i class="fas fa-sign-in-alt"></i>
                  <span>Login as Org</span>
                </button>
                <button type="button" class="action-menu-item action-menu-item-danger" @click="openDeleteFromMenu(row)">
                  <i class="fas fa-trash"></i>
                  <span>Delete</span>
                </button>
              </div>
            </div>
          </template>
          <template #cell-name="{ row }">
            <div>
              <div class="fw-bold">{{ row.name }}</div>
            </div>
          </template>
          <template #cell-contact_person="{ row }">
            <div class="user-cell">
              <div class="avatar-sm me-2">
                <div class="avatar-img rounded-circle bg-success d-flex align-items-center justify-content-center text-white fw-bold user-avatar">
                  {{ row.name?.charAt(0) || 'O' }}
                </div>
              </div>
              <div class="user-info">
                <div class="user-name">{{ row.name }}</div>
                <small class="user-email">{{ row.email }}</small>
              </div>
            </div>
          </template>
          <template #cell-mobile="{ value }">
            {{ value || 'N/A' }}
          </template>
          <template #cell-email="{ value }">
            {{ value || 'N/A' }}
          </template>
          <template #cell-app_login_code="{ value }">
            {{ value || '—' }}
          </template>
          <template #cell-sims_count="{ value }">
            {{ Number(value || 0).toLocaleString() }}
          </template>
          <template #cell-status="{ value, row }">
            <div class="status-dropdown">
              <button 
                class="status-badge" 
                :class="`status-${value}`"
                @click="toggleStatusDropdown(row.id)"
                type="button"
              >
                <span class="status-dot"></span>
                <span class="status-text">{{ value.charAt(0).toUpperCase() + value.slice(1) }}</span>
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
                  :class="{ 'active': value === 'active' }"
                  @click="updateOrganizationStatus(row.id, 'active')"
                  type="button"
                >
                  <span class="status-dot status-dot-active"></span>
                  <span>Active</span>
                  <i v-if="value === 'active'" class="fas fa-check ms-auto"></i>
                </button>
                <button 
                  class="status-dropdown-item"
                  :class="{ 'active': value === 'inactive' }"
                  @click="updateOrganizationStatus(row.id, 'inactive')"
                  type="button"
                >
                  <span class="status-dot status-dot-inactive"></span>
                  <span>Inactive</span>
                  <i v-if="value === 'inactive'" class="fas fa-check ms-auto"></i>
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
        Showing {{ (organizationsStore.pagination.current_page - 1) * organizationsStore.pagination.per_page + 1 }} to 
        {{ Math.min(organizationsStore.pagination.current_page * organizationsStore.pagination.per_page, organizationsStore.pagination.total) }} 
        of {{ organizationsStore.pagination.total }} entries
      </div>
      <Pagination
        :current-page="organizationsStore.pagination.current_page"
        :total-pages="organizationsStore.pagination.last_page"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Create/Edit Organization Modal -->
    <Modal
      :show="showOrganizationModal"
      :title="isEditing ? 'Edit Organization' : 'Create Organization'"
      size="lg"
      @close="closeOrganizationModal"
    >
      <form @submit.prevent="handleSubmit">
        <div class="row">
          <div class="col-md-6 mb-3">
            <InputField
              v-model="organizationForm.name"
              type="text"
              label="Organization Name"
              placeholder="Enter organization name"
              :error="errors.name"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="organizationForm.email"
              type="email"
              label="Email Address"
              placeholder="Enter email address"
              :error="errors.email"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">App Login Code <span class="text-danger">*</span></label>
              <div class="input-group app-login-code-group">
                <input
                  v-model="organizationForm.app_login_code"
                  type="text"
                  class="form-control"
                  readonly
                  :class="{ 'is-invalid': errors.app_login_code }"
                />
                <button
                  v-if="isEditing"
                  type="button"
                  class="btn btn-light border"
                  @click="regenerateAppLoginCode"
                >
                  Regenerate
                </button>
              </div>
              <div v-if="errors.app_login_code" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.app_login_code) }}
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="organizationForm.mobile"
              type="tel"
              label="Mobile Number"
              placeholder="Enter 10-digit mobile number"
              :error="errors.mobile"
              maxlength="10"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <div class="form-group">
            <label class="form-label">Status</label>
            <select v-model="organizationForm.status" class="form-select" :class="{ 'is-invalid': errors.status }">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <div v-if="errors.status" class="invalid-feedback d-block">
              {{ getErrorMessage(errors.status) }}
            </div>
          </div>
        </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="organizationForm.password"
              type="password"
              label="Password"
              placeholder="Enter password (minimum 6 characters)"
              :error="errors.password"
              :required="!isEditing"
              autocomplete="new-password"
            />
          </div>
          <div class="col-md-6 mb-3">
            <div class="form-group">
          <label class="form-label">Description</label>
          <textarea
            v-model="organizationForm.description"
            class="form-control"
            rows="3"
            placeholder="Enter organization description"
            :class="{ 'is-invalid': errors.description }"
          ></textarea>
          <div v-if="errors.description" class="invalid-feedback d-block">
            {{ getErrorMessage(errors.description) }}
              </div>
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>
      </form>

      <template #footer>
        <Button variant="secondary" @click="closeOrganizationModal">Cancel</Button>
        <Button
          variant="primary"
          :loading="organizationsStore.loading"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Update Organization' : 'Create Organization' }}
        </Button>
      </template>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete Organization"
      @close="closeDeleteModal"
      @confirm="handleDelete"
    >
      <div class="delete-modal-content">
        <p class="delete-question">Are you sure you want to delete this organization?</p>
        <div v-if="organizationToDelete" class="info-card">
          <div class="info-name">{{ organizationToDelete.name }}</div>
          <div class="info-email">{{ organizationToDelete.email }}</div>
        </div>
        <p class="warning-text">
          <i class="fas fa-exclamation-triangle me-2"></i>
          This action cannot be undone.
        </p>
      </div>
    </Modal>

    <!-- Login as Org Confirmation Modal -->
    <Modal
      :show="showImpersonateConfirm"
      title="Login as Organization"
      @close="closeImpersonateModal"
    >
      <div class="delete-modal-content">
        <p class="delete-question">You are about to access this organization's account as an admin.</p>
        <div v-if="orgToImpersonate" class="info-card impersonate-info-card">
          <div class="impersonate-icon-row">
            <span class="impersonate-avatar">{{ orgToImpersonate.name?.charAt(0) || 'O' }}</span>
          </div>
          <div class="info-name">{{ orgToImpersonate.name }}</div>
          <div class="info-email">{{ orgToImpersonate.email }}</div>
        </div>
        <p class="impersonate-notice">
          <i class="fas fa-shield-alt me-2"></i>
          A warning banner will be shown while you are inside the organization. All organization
          permissions and subscription limits apply normally during this session.
        </p>
      </div>
      <template #footer>
        <Button variant="secondary" @click="closeImpersonateModal">Cancel</Button>
        <Button
          variant="primary"
          :loading="authStore.loading"
          @click="handleLoginAsOrg"
        >
          <i class="fas fa-sign-in-alt me-1"></i>
          Confirm Login
        </Button>
      </template>
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
/* Delete modal specific styling */
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

.info-card {
  background-color: #f8f9fc;
  border: 1px solid #e3e6f0;
  border-radius: 8px;
  padding: 16px 20px;
  margin-bottom: 20px;
}

.info-name {
  font-size: 16px;
  font-weight: 600;
  color: #2c2d3a;
  margin-bottom: 6px;
}

.info-email {
  font-size: 14px;
  color: #6c757d;
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

.search-box-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 12px;
  z-index: 10;
  color: #6c757d;
  font-size: 14px;
  pointer-events: none;
}

.search-input {
  padding-left: 40px !important;
  border: 1px solid #e3e6f0;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
  background-color: #fff;
}

.search-input:focus {
  border-color: #4e73df;
  box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
  outline: none;
}

.search-input::placeholder {
  color: #a0a6b1;
  font-size: 14px;
}

/* Hover effect */
.search-box-wrapper:hover .search-input {
  border-color: #d1d3e2;
}

/* Focus effect for search icon */
.search-box-wrapper:focus-within .search-icon,
.search-box-wrapper:hover .search-icon {
  color: #4e73df;
}

/* Modal button styling */
.modal-footer .btn-secondary {
  background-color: #6f42c1 !important;
  border-color: #6f42c1 !important;
  color: white !important;
}

.modal-footer .btn-secondary:hover {
  background-color: #5a32a3 !important;
  border-color: #5a32a3 !important;
  color: white !important;
}

.modal-footer .btn-primary {
  background-color: #4e73df !important;
  border-color: #4e73df !important;
  color: white !important;
}

.modal-footer .btn-primary:hover {
  background-color: #3d5fc7 !important;
  border-color: #3d5fc7 !important;
  color: white !important;
}

.app-login-code-group .form-control {
  border-right: 0;
}

.app-login-code-group .btn {
  white-space: nowrap;
}

/* Status badge styling */
.badge {
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
}

.badge.bg-success {
  background-color: #28a745 !important;
}

.badge.bg-secondary {
  background-color: #6c757d !important;
}

/* Status Dropdown Styles */
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

/* Actions (duplicated here because Table.vue styles are scoped) */
.action-buttons {
  display: flex;
  gap: 6px;
  justify-content: flex-end;
  align-items: center;
}

.action-btn {
  width: 30px;
  height: 30px;
  border: 1px solid #e3e6f0;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
  background: #fff;
  position: relative;
  padding: 0;
  margin: 0;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.action-btn:active {
  transform: translateY(0);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.settings-btn {
  color: #6c757d;
}

.settings-btn:hover {
  background: #6c757d;
  border-color: #6c757d;
  color: #fff;
}

.edit-btn {
  color: #4e73df;
}

.edit-btn:hover {
  background: #4e73df;
  border-color: #4e73df;
  color: #fff;
}

.delete-btn {
  color: #e74a3b;
}

.delete-btn:hover {
  background: #e74a3b;
  border-color: #e74a3b;
  color: #fff;
}

.hdr-btn { display: inline-flex; align-items: center; gap: 7px; padding: 0 14px; height: 36px; border-radius: 10px; font-size: 0.82rem; font-weight: 700; border: none; cursor: pointer; transition: background 0.15s, box-shadow 0.15s, border-color 0.15s; white-space: nowrap; line-height: 1; }
.hdr-btn i { font-size: 0.8rem; }
.hdr-btn--ghost { background: #fff; color: #374151; border: 1.5px solid #e5e7eb; }
.hdr-btn--ghost:hover { background: #f9fafb; border-color: #d1d5db; }
.hdr-btn--add { background: linear-gradient(135deg, #f97316, #ffb454); color: #fff; box-shadow: 0 3px 12px rgba(249,115,22,0.28); }
.hdr-btn--add:hover { box-shadow: 0 5px 16px rgba(249,115,22,0.38); }

.users-table-card {
  background: #ffffff;
  border: 0;
  border-radius: 14px;
  box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
}

.simple-table :deep(.table-responsive) {
  border: 1px solid #eef2f7;
  border-radius: 14px;
}

.simple-table :deep(table.table) {
  border-collapse: separate;
  border-spacing: 0;
  font-size: 13.5px;
}

.simple-table :deep(table.table thead th) {
  background: #f9fafb;
  color: #374151;
  letter-spacing: 0.03em !important;
  border-bottom: 1px solid #edf1f7;
  border-right: 1px solid #edf1f7;
  padding: 10px 14px !important;
  font-size: 12px;
}

.simple-table :deep(table.table tbody td) {
  border-top: 1px solid #f1f5f9;
  border-right: 1px solid #f1f5f9;
  padding: 9px 14px !important;
  transition: background-color 0.2s ease;
}

.simple-table :deep(table.table tbody tr:hover),
.simple-table :deep(table.table tbody tr:hover td) {
  background: #f9fafb;
}

.filters-card {
  background: #ffffff;
  border: 1px solid #eef2f7;
  border-radius: 14px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
  padding: 14px;
}

.filters-container {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
}

.filter-item {
  min-width: 180px;
  flex: 1 1 180px;
}

.filter-search {
  flex: 2.4 1 360px;
}

.filter-per-page {
  flex: 0 1 170px;
}

.search-box-modern,
.select-wrapper {
  position: relative;
}

.filter-icon,
.search-icon-modern {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  pointer-events: none;
  z-index: 1;
}

.search-input-modern,
.select-modern {
  height: 42px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  font-size: 13px;
  box-shadow: none;
}

.search-input-modern {
  padding-left: 38px;
}

.select-modern {
  padding-left: 36px;
}

.search-input-modern:focus,
.select-modern:focus {
  border-color: #cbd5e1;
  box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.16);
}

.status-badge {
  padding: 5px 10px;
  border-radius: 999px;
  font-weight: 700;
  min-width: 84px;
}

.status-badge.status-active {
  background-color: #dcfce7;
  color: #16a34a;
}

.status-badge.status-active .status-dot {
  background-color: #16a34a;
}

.status-badge.status-inactive {
  background-color: #f3f4f6;
  color: #6b7280;
}

.status-badge.status-inactive .status-dot {
  background-color: #9ca3af;
}

.status-badge.status-active:hover,
.status-badge.status-inactive:hover {
  box-shadow: none;
}

.action-buttons {
  gap: 8px;
  position: relative;
}

.assign-sim-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 7px 12px;
  border: 1px solid #dbe4f0;
  border-radius: 9px;
  background: #f8fafc;
  color: #3559b8;
  font-size: 12.5px;
  font-weight: 700;
  line-height: 1;
  white-space: nowrap;
  transition: all 0.2s ease;
}

.assign-sim-btn:hover {
  background: #eef2ff;
  border-color: #c9d5f3;
  color: #2f4ea2;
}

.action-menu-trigger {
  width: 32px;
  height: 32px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #ffffff;
  color: #6b7280;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-menu-trigger:hover {
  background: #f8fafc;
  border-color: #d1d5db;
  color: #111827;
}

.action-menu {
  position: fixed;
  min-width: 142px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
  padding: 6px;
  z-index: 9999;
}

.action-menu-item {
  width: 100%;
  border: 0;
  background: transparent;
  color: #374151;
  border-radius: 8px;
  padding: 8px 10px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  text-align: left;
  transition: all 0.2s ease;
}

.action-menu-item:hover {
  background: #f9fafb;
  color: #111827;
}

.action-menu-item-danger {
  color: #dc2626;
}

.action-menu-item-danger:hover {
  background: #fef2f2;
  color: #b91c1c;
}

.action-menu-item-login {
  color: #0369a1;
}

.action-menu-item-login:hover {
  background: #f0f9ff;
  color: #0284c7;
}

/* Impersonation confirm modal */
.impersonate-info-card {
  text-align: center;
  padding: 20px;
}

.impersonate-icon-row {
  display: flex;
  justify-content: center;
  margin-bottom: 12px;
}

.impersonate-avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0ea5e9, #3b82f6);
  color: #fff;
  font-size: 22px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}

.impersonate-notice {
  font-size: 13px;
  color: #6b7280;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px 14px;
  margin: 0;
  line-height: 1.6;
}

.impersonate-notice i {
  color: #3b82f6;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 2px;
}

.user-avatar {
  width: 34px;
  height: 34px;
  font-size: 0.85rem;
}

.user-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.user-name {
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
}

.user-email {
  color: #6b7280;
  font-size: 12px;
  line-height: 1.3;
}
</style>

<script setup>
import { ref, reactive, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useOrganizationsStore } from '@/stores/organizations'
import { useAuthStore } from '@/stores/auth'
import { encryptId } from '@/utils/encryption'
import Button from '@/components/Button.vue'
import Table from '@/components/Table.vue'
import Modal from '@/components/Modal.vue'
import InputField from '@/components/InputField.vue'
import Pagination from '@/components/Pagination.vue'
import api from '@/services/api'
import { formatDateDisplay } from '@/utils/dateFormatter'

const organizationsStore = useOrganizationsStore()
const authStore = useAuthStore()
const router = useRouter()

// Search and filters
const searchQuery = ref('')
const perPage = ref(10)
const searchTimeout = ref(null)

// Status dropdown state
const activeDropdown = ref(null)
const activeActionMenu = ref(null)
const actionMenuPosition = ref({})

// Modal states
const showOrganizationModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const organizationToDelete = ref(null)

// Impersonation state
const showImpersonateConfirm = ref(false)
const orgToImpersonate = ref(null)

// Form data
const organizationForm = reactive({
  name: '',
  email: '',
  mobile: '',
  app_login_code: '',
  password: '',
  description: '',
  status: 'active'
})

const errors = ref({})
const errorMessage = ref('')

// Helper function to get error message
const getErrorMessage = (error) => {
  if (Array.isArray(error)) {
    return error[0]
  }
  return error
}

const organizationHeaders = [
  { key: 'name', label: 'Organization', class: 'text-start' },
  { key: 'mobile', label: 'Mobile', class: 'text-center' },
  { key: 'email', label: 'Email', class: 'text-center' },
  { key: 'app_login_code', label: 'App Login Code', class: 'text-center' },
  { key: 'sims_count', label: 'SIMs', class: 'text-center' },
  { key: 'status', label: 'Status', class: 'text-center' },
  { key: 'created_at', label: 'Created', class: 'text-center' }
]

// Methods
const fetchOrganizations = async () => {
  try {
    await organizationsStore.fetchOrganizations(1, searchQuery.value)
  } catch (error) {
    console.error('Error fetching organizations:', error)
  }
}

const refreshOrganizations = () => {
  fetchOrganizations()
}

const handleSearch = () => {
  // Clear existing timeout
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  // Set new timeout for debounced search
  searchTimeout.value = setTimeout(() => {
    fetchOrganizations()
  }, 500) // 500ms delay
}

const handlePerPageChange = () => {
  organizationsStore.pagination.per_page = perPage.value
  fetchOrganizations()
}

const handlePageChange = (page) => {
  organizationsStore.fetchOrganizations(page, searchQuery.value)
}

const goToSettings = (organizationId) => {
  router.push({ name: 'OrganizationSettings', params: { organizationId: encryptId(organizationId) } })
}

const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  organizationForm.app_login_code = generateAppLoginCode('')
  showOrganizationModal.value = true
}

const openEditModal = (organization) => {
  isEditing.value = true
  organizationForm.name = organization.name
  organizationForm.email = organization.email
  organizationForm.mobile = organization.mobile || ''
  organizationForm.description = organization.description || ''
  organizationForm.status = organization.status
  organizationForm.app_login_code = organization.app_login_code || ''
  organizationForm.password = ''
  organizationToDelete.value = organization
  showOrganizationModal.value = true
}

const openDeleteModal = (organization) => {
  organizationToDelete.value = organization
  showDeleteModal.value = true
}

const openEditFromMenu = (organization) => {
  activeActionMenu.value = null
  openEditModal(organization)
}

const openDeleteFromMenu = (organization) => {
  activeActionMenu.value = null
  openDeleteModal(organization)
}

const closeOrganizationModal = () => {
  showOrganizationModal.value = false
  resetForm()
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  organizationToDelete.value = null
}

const resetForm = () => {
  organizationForm.name = ''
  organizationForm.email = ''
  organizationForm.mobile = ''
  organizationForm.app_login_code = ''
  organizationForm.password = ''
  organizationForm.description = ''
  organizationForm.status = 'active'
  errors.value = {}
  errorMessage.value = ''
}

const generateAppLoginCode = (orgName = '') => {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'
  
  // Extract first 3 letters from organization name
  let prefix = 'ORG'
  if (orgName) {
    // Remove special characters and get only letters
    const cleanName = orgName.replace(/[^a-zA-Z]/g, '').toUpperCase()
    if (cleanName.length >= 3) {
      prefix = cleanName.slice(0, 3)
    } else if (cleanName.length > 0) {
      // If less than 3 letters, pad with random characters
      prefix = cleanName
      while (prefix.length < 3) {
        prefix += chars.charAt(Math.floor(Math.random() * chars.length))
      }
    }
  }
  
  // Generate random 8 characters for uniqueness
  let raw = ''
  for (let i = 0; i < 8; i++) {
    raw += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  
  return `${prefix}-${raw.slice(0, 4)}-${raw.slice(4, 8)}`
}

const regenerateAppLoginCode = () => {
  organizationForm.app_login_code = generateAppLoginCode(organizationForm.name)
  // Clear any previous validation error once regenerated
  if (errors.value?.app_login_code) {
    const next = { ...(errors.value || {}) }
    delete next.app_login_code
    errors.value = next
  }
}

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''

  // Collect all validation errors
  const validationErrors = {}
  
  if (!organizationForm.name) {
    validationErrors.name = 'Organization name is required'
  } else if (organizationForm.name.length < 2) {
    validationErrors.name = 'Organization name must be at least 2 characters'
  }
  
  if (!organizationForm.email) {
    validationErrors.email = 'Email is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(organizationForm.email)) {
    validationErrors.email = 'Please enter a valid email address'
  }
  
  if (!organizationForm.mobile) {
    validationErrors.mobile = 'Mobile number is required'
  } else if (!/^\d{10}$/.test(organizationForm.mobile)) {
    validationErrors.mobile = 'Mobile number must be exactly 10 digits'
  }
  
  if (!isEditing.value && !organizationForm.password) {
    validationErrors.password = 'Password is required'
  } else if (organizationForm.password && organizationForm.password.length < 6) {
    validationErrors.password = 'Password must be at least 6 characters'
  }

  if (!organizationForm.app_login_code) {
    validationErrors.app_login_code = 'App login code is required'
  }

  // If there are validation errors, show them all and return
  if (Object.keys(validationErrors).length > 0) {
    errors.value = validationErrors
    return
  }

  try {
    if (isEditing.value) {
      await organizationsStore.updateOrganization(organizationToDelete.value.id, organizationForm)
    } else {
      await organizationsStore.createOrganization(organizationForm)
    }
    closeOrganizationModal()
    fetchOrganizations()
  } catch (error) {
    console.error('Error saving organization:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = 'Error saving organization. Please try again.'
    }
  }
}

const toggleStatusDropdown = (organizationId) => {
  activeActionMenu.value = null
  if (activeDropdown.value === organizationId) {
    activeDropdown.value = null
  } else {
    activeDropdown.value = organizationId
    // Adjust dropdown position after it's rendered
    nextTick(() => {
      adjustDropdownPosition(organizationId)
    })
  }
}

const toggleActionMenu = (organizationId, event) => {
  activeDropdown.value = null
  if (activeActionMenu.value === organizationId) {
    activeActionMenu.value = null
    return
  }
  const rect = event.currentTarget.getBoundingClientRect()
  actionMenuPosition.value = {
    top: (rect.bottom + 4) + 'px',
    right: (window.innerWidth - rect.right) + 'px',
  }
  activeActionMenu.value = organizationId
}

const adjustDropdownPosition = (organizationId) => {
  const dropdown = document.querySelector(`[data-dropdown-id="${organizationId}"]`)
  const indicator = dropdown?.closest('.status-indicator')
  if (!dropdown || !indicator) return

  const indicatorRect = indicator.getBoundingClientRect()
  const viewportHeight = window.innerHeight
  const dropdownHeight = 80 // Approximate height of dropdown with 2 options
  
  // Calculate position
  const left = indicatorRect.left
  const top = indicatorRect.bottom + 4
  const bottom = viewportHeight - indicatorRect.top + 4
  
  // Set CSS custom properties
  dropdown.style.setProperty('--dropdown-left', `${left}px`)
  dropdown.style.setProperty('--dropdown-top', `${top}px`)
  dropdown.style.setProperty('--dropdown-bottom', `${bottom}px`)
  
  // Check if dropdown would go below viewport
  if (top + dropdownHeight > viewportHeight) {
    dropdown.setAttribute('data-position', 'up')
  } else {
    dropdown.setAttribute('data-position', 'down')
  }
}

const updateOrganizationStatus = async (organizationId, newStatus) => {
  try {
    await api.put(`/organizations/${organizationId}/status`, { status: newStatus })
    // Close dropdown and refresh the organizations list
    activeDropdown.value = null
    fetchOrganizations()
  } catch (error) {
    console.error('Error updating organization status:', error)
    // Optionally show error message to user
  }
}

const getStatusIndicatorClass = (status) => {
  return status === 'active' ? 'status-active' : 'status-inactive'
}

const getStatusDotClass = (status) => {
  return status === 'active' ? 'active' : 'inactive'
}

const openImpersonateModal = (organization) => {
  activeActionMenu.value = null
  orgToImpersonate.value = organization
  showImpersonateConfirm.value = true
}

const closeImpersonateModal = () => {
  showImpersonateConfirm.value = false
  orgToImpersonate.value = null
}

const handleLoginAsOrg = async () => {
  if (!orgToImpersonate.value) return
  try {
    await authStore.loginAsOrg(orgToImpersonate.value.id)
    closeImpersonateModal()
    router.push('/dashboard')
  } catch (error) {
    console.error('Impersonation failed:', error)
    alert(error?.response?.data?.message || error?.message || 'Failed to login as organization.')
  }
}

const handleDelete = async () => {
  try {
    await organizationsStore.deleteOrganization(organizationToDelete.value.id)
    closeDeleteModal()
    fetchOrganizations()
  } catch (error) {
    console.error('Error deleting organization:', error)
  }
}

const getStatusBadgeClass = (status) => {
  return status === 'active' 
    ? 'bg-success text-white' 
    : 'bg-secondary text-white'
}

const formatDate = (date) => {
  return formatDateDisplay(date)
}

// Watch for organization name changes to auto-update app_login_code during creation
watch(() => organizationForm.name, (newName) => {
  // Only auto-generate when creating new organization (not editing)
  if (!isEditing.value && newName) {
    organizationForm.app_login_code = generateAppLoginCode(newName)
  }
})

// Watch for changes in perPage
watch(perPage, () => {
  fetchOrganizations()
})

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.status-dropdown')) {
    activeDropdown.value = null
  }
  if (!event.target.closest('.action-buttons')) {
    activeActionMenu.value = null
  }
}

onMounted(() => {
  fetchOrganizations()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
