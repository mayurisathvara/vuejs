<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
      <div>
        <h3 class="fw-bold mb-3">Dashboard</h3>
      </div>
      <div class="ms-md-auto py-2 py-md-0">
        <Button variant="outline-info" class="me-2" @click="refreshData">
          <i class="fas fa-sync-alt me-1"></i>
          Refresh
        </Button>
        <Button variant="primary" @click="$router.push('/users')">
          <i class="fas fa-users me-1"></i>
          Manage Users
        </Button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
      <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-icon">
                <div class="icon-big text-center icon-primary bubble-shadow-small">
                  <i class="fas fa-users"></i>
                </div>
              </div>
              <div class="col col-stats ms-3 ms-sm-0">
                <div class="numbers">
                  <p class="card-category">Total Users</p>
                  <h4 class="card-title">{{ stats.totalUsers }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-icon">
                <div class="icon-big text-center icon-info bubble-shadow-small">
                  <i class="fas fa-user-check"></i>
                </div>
              </div>
              <div class="col col-stats ms-3 ms-sm-0">
                <div class="numbers">
                  <p class="card-category">Active Users</p>
                  <h4 class="card-title">{{ stats.activeUsers }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-icon">
                <div class="icon-big text-center icon-success bubble-shadow-small">
                  <i class="fas fa-chart-line"></i>
                </div>
              </div>
              <div class="col col-stats ms-3 ms-sm-0">
                <div class="numbers">
                  <p class="card-category">Growth Rate</p>
                  <h4 class="card-title">{{ stats.growthRate }}%</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-icon">
                <div class="icon-big text-center icon-secondary bubble-shadow-small">
                  <i class="fas fa-calendar-check"></i>
                </div>
              </div>
              <div class="col col-stats ms-3 ms-sm-0">
                <div class="numbers">
                  <p class="card-category">This Month</p>
                  <h4 class="card-title">{{ stats.thisMonth }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts and Tables Row -->
    <div class="row">
      <div class="col-md-8">
        <div class="card card-round">
          <div class="card-header">
            <div class="card-head-row">
              <div class="card-title">User Statistics</div>
              <div class="card-tools">
                <Button variant="outline-success" size="sm" class="me-2">
                  <i class="fa fa-pencil me-1"></i>
                  Export
                </Button>
                <Button variant="outline-info" size="sm">
                  <i class="fa fa-print me-1"></i>
                  Print
                </Button>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="chart-container" style="min-height: 375px">
              <canvas id="statisticsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card card-primary card-round">
          <div class="card-header">
            <div class="card-head-row">
              <div class="card-title">Recent Activity</div>
            </div>
            <div class="card-category">Last 7 days</div>
          </div>
          <div class="card-body pb-0">
            <div class="mb-4 mt-2">
              <h1>{{ stats.recentActivity }}</h1>
            </div>
            <div class="pull-in">
              <canvas id="recentActivityChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Users Table -->
    <div class="row">
      <div class="col-md-12">
        <div class="card card-round">
          <div class="card-header">
            <div class="card-head-row card-tools-still-right">
              <h4 class="card-title">Recent Users</h4>
              <div class="card-tools">
                <Button variant="outline-primary" size="sm" @click="refreshData">
                  <i class="fa fa-sync-alt me-1"></i>
                  Refresh
                </Button>
              </div>
            </div>
            <p class="card-category">Latest registered users</p>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <Table
                :data="recentUsers"
                :headers="userHeaders"
                :loading="loading"
                :actions="{ edit: true, delete: true }"
                @edit="editUser"
                @delete="deleteUser"
              >
                <template #cell-name="{ row }">
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm me-2">
                      <div class="avatar-img rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px;">
                        {{ row.name?.charAt(0) || 'U' }}
                      </div>
                    </div>
                    <div>
                      <div class="fw-bold">{{ row.name }}</div>
                      <small class="text-muted">{{ row.email }}</small>
                    </div>
                  </div>
                </template>
                <template #cell-role="{ value }">
                  <span class="badge" :class="getRoleBadgeClass(value)">
                    {{ value }}
                  </span>
                </template>
                <template #cell-created_at="{ value }">
                  {{ formatDate(value) }}
                </template>
              </Table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useUsersStore } from '@/stores/users'
import Button from '@/components/Button.vue'
import Table from '@/components/Table.vue'

const usersStore = useUsersStore()

const loading = ref(false)
const recentUsers = ref([])

const stats = reactive({
  totalUsers: 0,
  activeUsers: 0,
  growthRate: 0,
  thisMonth: 0,
  recentActivity: 0
})

const userHeaders = [
  { key: 'name', label: 'User', class: 'text-start' },
  { key: 'role', label: 'Role', class: 'text-center' },
  { key: 'created_at', label: 'Joined', class: 'text-center' }
]

const fetchDashboardData = async () => {
  loading.value = true
  try {
    // Fetch users data
    await usersStore.fetchUsers(1, '')
    recentUsers.value = usersStore.users.slice(0, 5)
    
    // Update stats (mock data for now)
    stats.totalUsers = usersStore.pagination.total
    stats.activeUsers = Math.floor(usersStore.pagination.total * 0.85)
    stats.growthRate = 12.5
    stats.thisMonth = Math.floor(usersStore.pagination.total * 0.15)
    stats.recentActivity = 24
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  } finally {
    loading.value = false
  }
}

const refreshData = () => {
  fetchDashboardData()
}

const editUser = (user) => {
  // Navigate to users page with edit mode
  // This would be implemented in the users page
  console.log('Edit user:', user)
}

const deleteUser = (user) => {
  // Show confirmation modal
  console.log('Delete user:', user)
}

const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'badge-danger',
    manager: 'badge-warning',
    user: 'badge-info'
  }
  return classes[role] || 'badge-secondary'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

onMounted(() => {
  fetchDashboardData()
})
</script>
