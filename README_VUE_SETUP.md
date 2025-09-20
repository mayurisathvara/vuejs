# Vue 3 Admin Dashboard with Laravel Sanctum

A complete Vue 3 admin dashboard application with Laravel Sanctum authentication, featuring a modern UI based on the Kaiadmin theme.

## Features

- **Vue 3** with Composition API and script setup
- **Laravel Sanctum** for API authentication
- **Pinia** for state management
- **Vue Router** for navigation
- **Tailwind CSS** for styling
- **Bootstrap 5** theme integration
- **Responsive design** with mobile support

## Project Structure

```
resources/js/
├── components/          # Reusable Vue components
│   ├── Button.vue
│   ├── InputField.vue
│   ├── Modal.vue
│   ├── Table.vue
│   ├── Pagination.vue
│   ├── Sidebar.vue
│   ├── Navbar.vue
│   └── Footer.vue
├── layouts/             # Layout components
│   ├── AuthLayout.vue   # For login/register pages
│   └── DefaultLayout.vue # For dashboard and protected pages
├── pages/               # Page components
│   ├── Login.vue
│   ├── Register.vue
│   ├── Dashboard.vue
│   └── Users.vue
├── services/            # API services
│   └── api.js          # Axios configuration with Sanctum
├── stores/              # Pinia stores
│   ├── auth.js         # Authentication store
│   └── users.js        # Users management store
├── router/              # Vue Router configuration
│   └── index.js
├── App.vue              # Main app component
└── app.js               # App entry point
```

## Setup Instructions

### 1. Install Dependencies

```bash
npm install
```

### 2. Configure Laravel Sanctum

The Sanctum configuration is already set up. Make sure your `.env` file has:

```env
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1
```

### 3. Run Database Migrations

```bash
php artisan migrate
php artisan db:seed --class=UserSeeder
```

### 4. Start Development Servers

**Terminal 1 - Laravel (Backend):**
```bash
php artisan serve
```

**Terminal 2 - Vite (Frontend):**
```bash
npm run dev
```

### 5. Access the Application

- **Frontend:** http://localhost:5173
- **Backend API:** http://localhost:8000/api

## Default Login Credentials

- **Admin:** admin@example.com / password
- **Manager:** manager@example.com / password
- **User:** john@example.com / password

## API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/register` - User registration
- `POST /api/logout` - User logout (requires auth)
- `GET /api/user` - Get authenticated user (requires auth)

### Users Management
- `GET /api/users` - List users (requires auth)
- `POST /api/users` - Create user (requires auth)
- `GET /api/users/{id}` - Get user (requires auth)
- `PUT /api/users/{id}` - Update user (requires auth)
- `DELETE /api/users/{id}` - Delete user (requires auth)

## Features Overview

### Authentication
- Login/Register forms with validation
- Token-based authentication with Sanctum
- Automatic token refresh and logout on 401 errors
- Protected routes with navigation guards

### Dashboard
- Statistics cards showing user metrics
- Recent users table
- Responsive charts (placeholder for Chart.js integration)
- Quick action buttons

### User Management
- Complete CRUD operations for users
- Search and filter functionality
- Pagination support
- Role-based user management
- Modal forms for create/edit operations
- Confirmation dialogs for delete operations

### UI Components
- Reusable form components (InputField, Button)
- Data table with sorting and actions
- Modal dialogs for forms and confirmations
- Pagination component
- Responsive sidebar navigation
- Top navigation bar with user menu

## Customization

### Adding New Pages
1. Create a new Vue component in `resources/js/pages/`
2. Add the route in `resources/js/router/index.js`
3. Add navigation link in `resources/js/components/Sidebar.vue`

### Adding New API Endpoints
1. Create controller methods in `app/Http/Controllers/`
2. Add routes in `routes/api.php`
3. Create corresponding methods in the appropriate Pinia store

### Styling
- The app uses Bootstrap 5 with custom Kaiadmin theme
- Additional styles can be added in `resources/css/app.css`
- Component-specific styles use scoped CSS

## Production Build

```bash
npm run build
```

The built assets will be available in the `public/build` directory and can be served by Laravel.

## Troubleshooting

### CORS Issues
Make sure your Laravel CORS configuration allows requests from your frontend domain.

### Token Issues
Check that the Sanctum middleware is properly configured and the CSRF token is being sent with requests.

### Theme Assets
Ensure all theme assets are properly copied to the `public/assets` directory.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
