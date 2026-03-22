<template>
  <div class="page-container developer-api-page">

    <!-- Page Header -->
    <div class="page-header mb-4">
      <div class="d-flex align-items-center gap-3">
        <div>
          <h3 class="page-title mb-0">Developer API</h3>
          <p class="text-muted mb-0 mt-1" style="font-size:13px;">
            Integration documentation for third-party developers. Use these endpoints to authenticate and retrieve call log data for your organization.
          </p>
        </div>
        <div class="ms-auto d-flex gap-2">
          <button class="btn btn-white border shadow-sm btn-sm" @click="downloadPostmanCollection">
            <i class="fas fa-download me-1 text-orange"></i> Download Postman Collection
          </button>
          <button class="btn btn-white border shadow-sm btn-sm" @click="downloadOpenAPI">
            <i class="fas fa-file-code me-1 text-orange"></i> Download OpenAPI (JSON)
          </button>
        </div>
      </div>
    </div>

    <!-- Base URL Banner -->
    <div class="api-base-url-banner mb-4">
      <span class="label">Base URL</span>
      <code class="url">{{ baseUrl }}/api/v1/org</code>
      <button class="copy-btn" @click="copyText(baseUrl + '/api/v1/org', 'baseUrl')" :title="copied.baseUrl ? 'Copied!' : 'Copy'">
        <i :class="copied.baseUrl ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
      </button>
    </div>

    <div class="row g-4">

      <!-- Left: TOC + Overview -->
      <div class="col-12 col-lg-3">
        <div class="api-toc sticky-top" style="top:80px;">
          <div class="toc-title">Contents</div>
          <ul class="toc-list">
            <li><a href="#overview" @click.prevent="scrollTo('overview')">Overview</a></li>
            <li><a href="#authentication" @click.prevent="scrollTo('authentication')">Authentication</a></li>
            <li class="toc-sub"><a href="#endpoint-login" @click.prevent="scrollTo('endpoint-login')">POST /auth/login</a></li>
            <li><a href="#call-logs" @click.prevent="scrollTo('call-logs')">Call Logs</a></li>
            <li class="toc-sub"><a href="#endpoint-calllogs" @click.prevent="scrollTo('endpoint-calllogs')">GET /call-logs</a></li>
            <li><a href="#errors" @click.prevent="scrollTo('errors')">Error Codes</a></li>
          </ul>
        </div>
      </div>

      <!-- Right: Content -->
      <div class="col-12 col-lg-9 api-content">

        <!-- Overview -->
        <section id="overview" class="api-section">
          <h5 class="section-title"><i class="fas fa-info-circle me-2 text-orange"></i>Overview</h5>
          <p>
            The <strong>Callytics Organization API</strong> allows authorized third-party systems to programmatically retrieve call log data scoped to your organization.
            All data returned is automatically filtered to your organization — no cross-organization data is ever exposed.
          </p>
          <div class="info-cards row g-3 mt-2">
            <div class="col-6 col-md-3">
              <div class="info-card">
                <i class="fas fa-lock"></i>
                <span>Bearer Token Auth</span>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="info-card">
                <i class="fas fa-code"></i>
                <span>JSON Responses</span>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="info-card">
                <i class="fas fa-layer-group"></i>
                <span>Paginated Results</span>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="info-card">
                <i class="fas fa-shield-alt"></i>
                <span>Org-Scoped Data</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Authentication -->
        <section id="authentication" class="api-section">
          <h5 class="section-title"><i class="fas fa-key me-2 text-orange"></i>Authentication</h5>
          <p>
            This API uses <strong>Bearer Token authentication</strong> via Laravel Sanctum.
            To obtain a token, call the <code>POST /auth/login</code> endpoint with your organization credentials.
            The token returned must be included in the <code>Authorization</code> header for all subsequent requests.
          </p>
          <div class="auth-flow mt-3">
            <div class="flow-step"><span class="step-num">1</span><span>Call <code>POST /auth/login</code> with email &amp; password</span></div>
            <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
            <div class="flow-step"><span class="step-num">2</span><span>Receive <code>token</code> in response</span></div>
            <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
            <div class="flow-step"><span class="step-num">3</span><span>Send <code>Authorization: Bearer &lt;token&gt;</code> header</span></div>
          </div>
        </section>

        <!-- POST /auth/login -->
        <section id="endpoint-login" class="api-section">
          <div class="endpoint-header">
            <span class="method post">POST</span>
            <span class="endpoint-path">/auth/login</span>
            <span class="badge-auth public">Public</span>
          </div>
          <p class="endpoint-desc">Authenticate with your organization credentials and receive a Bearer token.</p>

          <h6 class="param-group-title">Request Headers</h6>
          <div class="param-table-wrap">
            <table class="param-table">
              <thead><tr><th>Header</th><th>Value</th><th>Required</th></tr></thead>
              <tbody>
                <tr><td><code>Content-Type</code></td><td>application/json</td><td><span class="badge-req">Yes</span></td></tr>
                <tr><td><code>Accept</code></td><td>application/json</td><td><span class="badge-req">Yes</span></td></tr>
              </tbody>
            </table>
          </div>

          <h6 class="param-group-title">Request Body Parameters</h6>
          <div class="param-table-wrap">
            <table class="param-table">
              <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
              <tbody>
                <tr><td><code>email</code></td><td>string</td><td><span class="badge-req">Yes</span></td><td>Organization account email address</td></tr>
                <tr><td><code>password</code></td><td>string</td><td><span class="badge-req">Yes</span></td><td>Account password (min 6 characters)</td></tr>
              </tbody>
            </table>
          </div>

          <h6 class="param-group-title">Example Request</h6>
          <div class="code-block">
            <div class="code-block-header">
              <span class="lang-label">cURL</span>
              <button class="copy-btn" @click="copyText(curlLogin, 'login')" :title="copied.login ? 'Copied!' : 'Copy'">
                <i :class="copied.login ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                {{ copied.login ? 'Copied!' : 'Copy' }}
              </button>
            </div>
            <pre class="code-pre">{{ curlLogin }}</pre>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-12 col-md-6">
              <h6 class="param-group-title">Success Response <span class="text-muted fw-normal">(200)</span></h6>
              <div class="code-block">
                <div class="code-block-header">
                  <span class="lang-label">JSON</span>
                  <button class="copy-btn" @click="copyText(JSON.stringify(loginSuccessResponse, null, 2), 'loginSuccess')" :title="copied.loginSuccess ? 'Copied!' : 'Copy'">
                    <i :class="copied.loginSuccess ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                  </button>
                </div>
                <pre class="code-pre">{{ JSON.stringify(loginSuccessResponse, null, 2) }}</pre>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <h6 class="param-group-title">Error Responses</h6>
              <div class="code-block">
                <div class="code-block-header"><span class="lang-label">401 — Invalid credentials</span></div>
                <pre class="code-pre">{{ JSON.stringify({ success: false, message: 'Invalid credentials.' }, null, 2) }}</pre>
              </div>
              <div class="code-block mt-2">
                <div class="code-block-header"><span class="lang-label">403 — Wrong role / inactive</span></div>
                <pre class="code-pre">{{ JSON.stringify({ success: false, message: 'Access denied. Only organization accounts can use this API.' }, null, 2) }}</pre>
              </div>
            </div>
          </div>
        </section>

        <!-- Call Logs -->
        <section id="call-logs" class="api-section">
          <h5 class="section-title"><i class="fas fa-phone-alt me-2 text-orange"></i>Call Logs</h5>
          <p>Retrieve paginated call log records for your organization filtered by date range and optional criteria.</p>
        </section>

        <!-- GET /call-logs -->
        <section id="endpoint-calllogs" class="api-section">
          <div class="endpoint-header">
            <span class="method get">GET</span>
            <span class="endpoint-path">/call-logs</span>
            <span class="badge-auth protected">Bearer Token</span>
          </div>
          <p class="endpoint-desc">
            Returns a paginated list of call logs scoped to your organization. Results are always sorted by date/time descending.
            Excluded numbers configured for your organization are automatically filtered out.
          </p>

          <h6 class="param-group-title">Request Headers</h6>
          <div class="param-table-wrap">
            <table class="param-table">
              <thead><tr><th>Header</th><th>Value</th><th>Required</th></tr></thead>
              <tbody>
                <tr><td><code>Authorization</code></td><td>Bearer &lt;your_token&gt;</td><td><span class="badge-req">Yes</span></td></tr>
                <tr><td><code>Accept</code></td><td>application/json</td><td><span class="badge-req">Yes</span></td></tr>
              </tbody>
            </table>
          </div>

          <h6 class="param-group-title">Query Parameters</h6>
          <div class="param-table-wrap">
            <table class="param-table">
              <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Default</th><th>Description</th></tr></thead>
              <tbody>
                <tr>
                  <td><code>start_date</code></td><td>string</td><td><span class="badge-req">Yes</span></td><td>—</td>
                  <td>Start of date range. Format: <code>YYYY-MM-DD</code></td>
                </tr>
                <tr>
                  <td><code>end_date</code></td><td>string</td><td><span class="badge-req">Yes</span></td><td>—</td>
                  <td>End of date range. Format: <code>YYYY-MM-DD</code>. Must be ≥ <code>start_date</code></td>
                </tr>
                <tr>
                  <td><code>call_type</code></td><td>string</td><td><span class="badge-opt">No</span></td><td>all</td>
                  <td>Filter by call direction. Values: <code>inbound</code>, <code>outbound</code></td>
                </tr>
                <tr>
                  <td><code>call_status</code></td><td>string</td><td><span class="badge-opt">No</span></td><td>all</td>
                  <td>Filter by call status. Values: <code>Answered</code>, <code>Missed</code>, <code>No Answer</code></td>
                </tr>
                <tr>
                  <td><code>caller_id</code></td><td>string</td><td><span class="badge-opt">No</span></td><td>all</td>
                  <td>Filter by specific SIM / mobile number (e.g. <code>+919876543210</code>)</td>
                </tr>
                <tr>
                  <td><code>team_name</code></td><td>string</td><td><span class="badge-opt">No</span></td><td>all</td>
                  <td>Filter by team name (exact match)</td>
                </tr>
                <tr>
                  <td><code>per_page</code></td><td>integer</td><td><span class="badge-opt">No</span></td><td>30</td>
                  <td>Number of records per page. Min: <code>1</code>, Max: <code>100</code></td>
                </tr>
                <tr>
                  <td><code>page</code></td><td>integer</td><td><span class="badge-opt">No</span></td><td>1</td>
                  <td>Page number to retrieve</td>
                </tr>
              </tbody>
            </table>
          </div>

          <h6 class="param-group-title">Response Fields</h6>
          <div class="param-table-wrap">
            <table class="param-table">
              <thead><tr><th>Field</th><th>Type</th><th>Description</th></tr></thead>
              <tbody>
                <tr><td><code>unique_id</code></td><td>string</td><td>Unique identifier for the call record</td></tr>
                <tr><td><code>date</code></td><td>string</td><td>Call date — <code>YYYY-MM-DD</code></td></tr>
                <tr><td><code>time</code></td><td>string</td><td>Call time — <code>HH:MM:SS</code></td></tr>
                <tr><td><code>date_time</code></td><td>string</td><td>Combined date &amp; time — <code>YYYY-MM-DD HH:MM:SS</code></td></tr>
                <tr><td><code>caller_duration</code></td><td>string</td><td>Total call duration — <code>HH:MM:SS</code></td></tr>
                <tr><td><code>call_type</code></td><td>string</td><td><code>inbound</code> or <code>outbound</code></td></tr>
                <tr><td><code>call_status</code></td><td>string</td><td><code>Answered</code>, <code>Missed</code>, or <code>No Answer</code></td></tr>
                <tr><td><code>team_name</code></td><td>string|null</td><td>Name of the team the SIM belongs to</td></tr>
                <tr><td><code>name</code></td><td>string|null</td><td>Name of the SIM / agent</td></tr>
                <tr><td><code>caller_id</code></td><td>string</td><td>SIM mobile number that made/received the call</td></tr>
                <tr><td><code>caller_number</code></td><td>string</td><td>Customer phone number</td></tr>
                <tr><td><code>call_back</code></td><td>string|null</td><td><code>Y</code> if this missed call was later returned, otherwise <code>null</code></td></tr>
              </tbody>
            </table>
          </div>

          <h6 class="param-group-title">Example Request</h6>
          <div class="code-block">
            <div class="code-block-header">
              <span class="lang-label">cURL</span>
              <button class="copy-btn" @click="copyText(curlCallLogs, 'callLogs')" :title="copied.callLogs ? 'Copied!' : 'Copy'">
                <i :class="copied.callLogs ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                {{ copied.callLogs ? 'Copied!' : 'Copy' }}
              </button>
            </div>
            <pre class="code-pre">{{ curlCallLogs }}</pre>
          </div>

          <h6 class="param-group-title">Success Response <span class="text-muted fw-normal">(200)</span></h6>
          <div class="code-block">
            <div class="code-block-header">
              <span class="lang-label">JSON</span>
              <button class="copy-btn" @click="copyText(JSON.stringify(callLogsSuccessResponse, null, 2), 'callLogsSuccess')" :title="copied.callLogsSuccess ? 'Copied!' : 'Copy'">
                <i :class="copied.callLogsSuccess ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
              </button>
            </div>
            <pre class="code-pre">{{ JSON.stringify(callLogsSuccessResponse, null, 2) }}</pre>
          </div>
        </section>

        <!-- Error Codes -->
        <section id="errors" class="api-section">
          <h5 class="section-title"><i class="fas fa-exclamation-triangle me-2 text-orange"></i>Error Codes</h5>
          <div class="param-table-wrap">
            <table class="param-table">
              <thead><tr><th>HTTP Code</th><th>Meaning</th><th>Common Cause</th></tr></thead>
              <tbody>
                <tr><td><span class="badge-status s200">200</span></td><td>OK</td><td>Request succeeded</td></tr>
                <tr><td><span class="badge-status s401">401</span></td><td>Unauthenticated</td><td>Missing or invalid Bearer token</td></tr>
                <tr><td><span class="badge-status s403">403</span></td><td>Forbidden</td><td>Wrong role, or account / organization is inactive</td></tr>
                <tr><td><span class="badge-status s422">422</span></td><td>Validation Error</td><td>Missing required parameters or invalid format</td></tr>
                <tr><td><span class="badge-status s500">500</span></td><td>Server Error</td><td>Unexpected server-side error</td></tr>
              </tbody>
            </table>
          </div>

          <h6 class="param-group-title mt-3">Validation Error Response Example</h6>
          <div class="code-block">
            <pre class="code-pre">{{ JSON.stringify(validationErrorExample, null, 2) }}</pre>
          </div>
        </section>

      </div><!-- /col -->
    </div><!-- /row -->
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const baseUrl = computed(() => window.location.origin)

const copied = ref({})

function copyText(text, key) {
  navigator.clipboard.writeText(text).then(() => {
    copied.value[key] = true
    setTimeout(() => { copied.value[key] = false }, 2000)
  })
}

function scrollTo(id) {
  const el = document.getElementById(id)
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

// ─── cURL examples ────────────────────────────────────────────────────────────

const curlLogin = computed(() => `curl -X POST "${baseUrl.value}/api/v1/org/auth/login" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{
    "email": "org@example.com",
    "password": "yourpassword"
  }'`)

const curlCallLogs = computed(() => `curl -X GET "${baseUrl.value}/api/v1/org/call-logs?start_date=2026-03-01&end_date=2026-03-15&per_page=30&page=1" \\
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \\
  -H "Accept: application/json"`)

// ─── Sample responses ─────────────────────────────────────────────────────────

const loginSuccessResponse = {
  success: true,
  message: 'Login successful.',
  data: {
    token: '1|AbCdEfGhIjKlMnOpQrStUvWxYz1234567890',
    token_type: 'Bearer',
    organization: {
      id: 1,
      name: 'Acme Corp'
    }
  }
}

const callLogsSuccessResponse = {
  success: true,
  message: 'Call logs fetched successfully.',
  data: {
    call_logs: [
      {
        unique_id: 'CALL-20260315-001',
        date: '2026-03-15',
        time: '10:23:45',
        date_time: '2026-03-15 10:23:45',
        caller_duration: '00:05:30',
        call_type: 'inbound',
        call_status: 'Answered',
        team_name: 'Sales Team',
        name: 'John Doe',
        caller_id: '+919876543210',
        caller_number: '+911234567890',
        call_back: null
      }
    ],
    pagination: {
      total: 150,
      per_page: 30,
      current_page: 1,
      last_page: 5,
      from: 1,
      to: 30
    }
  }
}

const validationErrorExample = {
  success: false,
  message: 'The given data was invalid.',
  errors: {
    start_date: ['The start date field is required.'],
    end_date: ['The end date must be a date after or equal to start date.']
  }
}

// ─── Downloads ────────────────────────────────────────────────────────────────

function downloadPostmanCollection() {
  const collection = {
    info: {
      name: 'Callytics Organization API',
      description: 'Third-party API for organization call log integration',
      schema: 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
    },
    variable: [
      { key: 'base_url', value: baseUrl.value, type: 'string' },
      { key: 'token', value: '', type: 'string' }
    ],
    item: [
      {
        name: 'Auth',
        item: [
          {
            name: 'Login',
            request: {
              method: 'POST',
              header: [
                { key: 'Content-Type', value: 'application/json' },
                { key: 'Accept', value: 'application/json' }
              ],
              body: {
                mode: 'raw',
                raw: JSON.stringify({ email: 'org@example.com', password: 'yourpassword' }, null, 2),
                options: { raw: { language: 'json' } }
              },
              url: { raw: '{{base_url}}/api/v1/org/auth/login', host: ['{{base_url}}'], path: ['api', 'v1', 'org', 'auth', 'login'] },
              description: 'Login with organization credentials to get a Bearer token. Only organization-role accounts are allowed.'
            }
          }
        ]
      },
      {
        name: 'Call Logs',
        item: [
          {
            name: 'Get Call Logs',
            request: {
              method: 'GET',
              header: [
                { key: 'Authorization', value: 'Bearer {{token}}' },
                { key: 'Accept', value: 'application/json' }
              ],
              url: {
                raw: '{{base_url}}/api/v1/org/call-logs?start_date=2026-03-01&end_date=2026-03-15&per_page=30&page=1',
                host: ['{{base_url}}'],
                path: ['api', 'v1', 'org', 'call-logs'],
                query: [
                  { key: 'start_date', value: '2026-03-01', description: 'Required. Format: YYYY-MM-DD' },
                  { key: 'end_date', value: '2026-03-15', description: 'Required. Must be >= start_date' },
                  { key: 'call_type', value: '', description: 'Optional: inbound | outbound', disabled: true },
                  { key: 'call_status', value: '', description: 'Optional: Answered | Missed | No Answer', disabled: true },
                  { key: 'caller_id', value: '', description: 'Optional: filter by SIM number', disabled: true },
                  { key: 'team_name', value: '', description: 'Optional: filter by team name', disabled: true },
                  { key: 'per_page', value: '30', description: 'Optional: 1-100, default 30' },
                  { key: 'page', value: '1', description: 'Optional: page number' }
                ]
              },
              description: 'Get paginated call logs for the authenticated organization.'
            }
          }
        ]
      }
    ]
  }

  downloadJSON(collection, 'callytics-org-api-postman-collection.json')
}

function downloadOpenAPI() {
  const spec = {
    openapi: '3.0.3',
    info: {
      title: 'Callytics Organization API',
      description: 'Third-party API for organization call log integration',
      version: '1.0.0'
    },
    servers: [{ url: `${baseUrl.value}/api/v1/org`, description: 'Production Server' }],
    components: {
      securitySchemes: {
        BearerAuth: { type: 'http', scheme: 'bearer', bearerFormat: 'Sanctum' }
      }
    },
    paths: {
      '/auth/login': {
        post: {
          summary: 'Organization Login',
          description: 'Authenticate and receive a Bearer token. Only organization-role accounts are permitted.',
          tags: ['Authentication'],
          requestBody: {
            required: true,
            content: {
              'application/json': {
                schema: {
                  type: 'object',
                  required: ['email', 'password'],
                  properties: {
                    email: { type: 'string', format: 'email', example: 'org@example.com' },
                    password: { type: 'string', minLength: 6, example: 'yourpassword' }
                  }
                }
              }
            }
          },
          responses: {
            200: { description: 'Login successful. Returns Bearer token.' },
            401: { description: 'Invalid credentials.' },
            403: { description: 'Access denied — wrong role or inactive account/organization.' },
            422: { description: 'Validation error.' }
          }
        }
      },
      '/call-logs': {
        get: {
          summary: 'Get Call Logs',
          description: 'Retrieve paginated call logs scoped to the authenticated organization.',
          tags: ['Call Logs'],
          security: [{ BearerAuth: [] }],
          parameters: [
            { name: 'start_date', in: 'query', required: true, schema: { type: 'string', format: 'date' }, description: 'Start date (YYYY-MM-DD)' },
            { name: 'end_date', in: 'query', required: true, schema: { type: 'string', format: 'date' }, description: 'End date (YYYY-MM-DD)' },
            { name: 'call_type', in: 'query', required: false, schema: { type: 'string', enum: ['inbound', 'outbound'] } },
            { name: 'call_status', in: 'query', required: false, schema: { type: 'string', enum: ['Answered', 'Missed', 'No Answer'] } },
            { name: 'caller_id', in: 'query', required: false, schema: { type: 'string' }, description: 'Filter by SIM number' },
            { name: 'team_name', in: 'query', required: false, schema: { type: 'string' }, description: 'Filter by team name' },
            { name: 'per_page', in: 'query', required: false, schema: { type: 'integer', minimum: 1, maximum: 100, default: 30 } },
            { name: 'page', in: 'query', required: false, schema: { type: 'integer', minimum: 1, default: 1 } }
          ],
          responses: {
            200: { description: 'Paginated list of call logs.' },
            401: { description: 'Unauthenticated.' },
            422: { description: 'Validation error.' }
          }
        }
      }
    }
  }

  downloadJSON(spec, 'callytics-org-api-openapi.json')
}

function downloadJSON(data, filename) {
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}
</script>

<style scoped>
/* ── Layout ─────────────────────────────────────────────────────────────────── */
.developer-api-page { max-width: 1400px; }

.api-content .api-section {
  background: #fff;
  border: 1px solid #e8edf2;
  border-radius: 10px;
  padding: 28px 28px 24px;
  margin-bottom: 20px;
}

/* ── Base URL Banner ─────────────────────────────────────────────────────────── */
.api-base-url-banner {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f8f9fb;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px 18px;
}
.api-base-url-banner .label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .6px;
  color: #8898aa;
  white-space: nowrap;
}
.api-base-url-banner code.url {
  font-size: 13.5px;
  color: #e25c16;
  background: transparent;
  padding: 0;
  flex: 1;
}

/* ── TOC ─────────────────────────────────────────────────────────────────────── */
.api-toc {
  background: #fff;
  border: 1px solid #e8edf2;
  border-radius: 10px;
  padding: 18px 20px;
}
.toc-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .8px;
  color: #8898aa;
  margin-bottom: 12px;
}
.toc-list { list-style: none; padding: 0; margin: 0; }
.toc-list li { margin-bottom: 4px; }
.toc-list li a {
  font-size: 13px;
  color: #4a5568;
  text-decoration: none;
  display: block;
  padding: 3px 0;
  border-left: 2px solid transparent;
  padding-left: 8px;
  transition: color .15s, border-color .15s;
}
.toc-list li a:hover { color: #e25c16; border-left-color: #e25c16; }
.toc-list li.toc-sub a { font-size: 12px; color: #718096; padding-left: 20px; }
.toc-list li.toc-sub a:hover { color: #e25c16; }

/* ── Section title ───────────────────────────────────────────────────────────── */
.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 12px;
}
.text-orange { color: #e25c16 !important; }

/* ── Info cards ──────────────────────────────────────────────────────────────── */
.info-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  background: #f8f9fb;
  border: 1px solid #e8edf2;
  border-radius: 8px;
  padding: 14px 10px;
  text-align: center;
  font-size: 12px;
  color: #4a5568;
}
.info-card i { font-size: 20px; color: #e25c16; }

/* ── Auth flow ───────────────────────────────────────────────────────────────── */
.auth-flow {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.flow-step {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f8f9fb;
  border: 1px solid #e8edf2;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13px;
  color: #4a5568;
}
.step-num {
  width: 22px; height: 22px;
  background: #e25c16;
  color: #fff;
  border-radius: 50%;
  font-size: 11px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.flow-arrow { color: #a0aec0; font-size: 13px; }

/* ── Alert info ──────────────────────────────────────────────────────────────── */
.alert-info-block {
  background: #fff8f5;
  border: 1px solid #fbd5c0;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13px;
  color: #7b3b10;
}

/* ── Endpoint header ─────────────────────────────────────────────────────────── */
.endpoint-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}
.method {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .5px;
  border-radius: 5px;
  padding: 3px 10px;
}
.method.post { background: #fef3c7; color: #92400e; }
.method.get  { background: #d1fae5; color: #065f46; }
.endpoint-path { font-family: monospace; font-size: 14px; font-weight: 600; color: #1a202c; }
.badge-auth {
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 20px;
  font-weight: 600;
}
.badge-auth.public    { background: #f0fff4; color: #276749; border: 1px solid #9ae6b4; }
.badge-auth.protected { background: #ebf4ff; color: #2b6bc8; border: 1px solid #90cdf4; }
.endpoint-desc { font-size: 13px; color: #4a5568; margin-bottom: 16px; }

/* ── Param group title ───────────────────────────────────────────────────────── */
.param-group-title { font-size: 13px; font-weight: 700; color: #2d3748; margin: 18px 0 8px; }

/* ── Tables ──────────────────────────────────────────────────────────────────── */
.param-table-wrap { overflow-x: auto; border-radius: 8px; border: 1px solid #e8edf2; }
.param-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.param-table thead th { background: #f8f9fb; padding: 10px 14px; text-align: left; font-weight: 600; color: #4a5568; border-bottom: 1px solid #e8edf2; white-space: nowrap; }
.param-table tbody td { padding: 9px 14px; border-bottom: 1px solid #f2f4f8; color: #2d3748; vertical-align: top; }
.param-table tbody tr:last-child td { border-bottom: none; }

.badge-req { background: #fed7d7; color: #9b2c2c; font-size: 11px; font-weight: 600; padding: 1px 7px; border-radius: 4px; }
.badge-opt { background: #e2e8f0; color: #4a5568; font-size: 11px; font-weight: 600; padding: 1px 7px; border-radius: 4px; }

.badge-status { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
.badge-status.s200 { background: #d1fae5; color: #065f46; }
.badge-status.s401 { background: #fed7d7; color: #9b2c2c; }
.badge-status.s403 { background: #feebc8; color: #7b341e; }
.badge-status.s422 { background: #fef3c7; color: #92400e; }
.badge-status.s500 { background: #e7e7e7; color: #333; }

/* ── Code blocks ─────────────────────────────────────────────────────────────── */
.code-block {
  background: #1a202c;
  border-radius: 8px;
  overflow: hidden;
  font-size: 13px;
}
.code-block-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #2d3748;
  padding: 7px 14px;
}
.lang-label { font-size: 11px; font-weight: 600; color: #a0aec0; text-transform: uppercase; letter-spacing: .5px; }
.code-pre {
  margin: 0;
  padding: 16px 18px;
  color: #e2e8f0;
  font-size: 12.5px;
  line-height: 1.65;
  white-space: pre-wrap;
  word-break: break-all;
  font-family: 'Courier New', Courier, monospace;
  background: transparent;
}

/* ── Copy button ─────────────────────────────────────────────────────────────── */
.copy-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #a0aec0;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 2px 4px;
  border-radius: 4px;
  transition: color .15s;
}
.copy-btn:hover { color: #e2e8f0; }
</style>
