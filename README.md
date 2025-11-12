# CBSIG Project

Dự án được tách thành 2 phần riêng biệt: Backend (Laravel) và Frontend (Vue.js).

## Cấu trúc dự án

```
CBSIG/
├── backend/          # Laravel API Backend
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   ├── vendor/
│   ├── docker/
│   └── Dockerfile
│
├── frontend/         # Vue.js Frontend
│   ├── src/
│   │   ├── js/
│   │   ├── css/
│   │   └── sass/
│   ├── public/
│   ├── node_modules/
│   ├── package.json
│   ├── vite.config.js
│   └── Dockerfile
│
└── docker-compose.yml
```

## Yêu cầu

-   Docker
-   Docker Compose

## Cài đặt và chạy

**📖 Xem hướng dẫn chi tiết các lệnh theo thứ tự trong file [SETUP.md](./SETUP.md)**

### Tóm tắt nhanh

```bash
# 1. Cấu hình backend
cd backend && cp .env.example .env && cd ..

# 2. Build và chạy Docker
docker-compose up -d --build

# 3. Cài đặt backend dependencies
docker exec -it laravel_backend composer install
docker exec -it laravel_backend php artisan key:generate
docker exec -it laravel_backend php artisan migrate

# 4. Cài đặt frontend dependencies
docker exec -it laravel_frontend npm install

# 5. Truy cập: http://localhost:8000
```

Các services sẽ được khởi động:

-   **Backend**: Laravel API (PHP-FPM)
-   **Frontend**: Vue.js với Vite dev server (port 5173)
-   **Nginx**: Reverse proxy (port 8000)
-   **MySQL**: Database (port 3306)
-   **Redis**: Cache (port 6379)

### Truy cập ứng dụng

-   **Frontend**: http://localhost:8000
-   **Backend API**: http://localhost:8000/api
-   **Frontend Dev Server (trực tiếp)**: http://localhost:5173

## Development

### Backend (Laravel)

```bash
cd backend

# Install dependencies
composer install

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed
```

### Frontend (Vue.js)

```bash
cd frontend

# Install dependencies
npm install

# Run dev server
npm run dev

# Build for production
npm run build
```

## Docker Services

### Backend Service

-   **Container**: `laravel_backend`
-   **Image**: Custom PHP 8.2-FPM
-   **Working Directory**: `/var/www/html`
-   **Port**: 9000 (internal)

### Frontend Service

-   **Container**: `laravel_frontend`
-   **Image**: Node.js 20 Alpine
-   **Working Directory**: `/app`
-   **Port**: 5173 (exposed)

### Nginx Service

-   **Container**: `laravel_nginx`
-   **Image**: Nginx Alpine
-   **Port**: 8000 (exposed)
-   **Config**: Routes `/api` to backend, `/` to frontend

### MySQL Service

-   **Container**: `laravel_mysql`
-   **Image**: MySQL 8.0
-   **Port**: 3306 (exposed)
-   **Database**: `laravel`
-   **User**: `laravel`
-   **Password**: `root`

### Redis Service

-   **Container**: `laravel_redis`
-   **Image**: Redis Alpine
-   **Port**: 6379 (exposed)

## Cấu hình Nginx

Nginx được cấu hình để:

-   Route `/api/*` đến Laravel backend
-   Route `/` đến Vue.js frontend (Vite dev server)
-   Hỗ trợ WebSocket cho HMR (Hot Module Replacement)

## Lưu ý

-   Backend và Frontend hoàn toàn tách biệt
-   Frontend giao tiếp với Backend qua API (`/api/*`)
-   Backend chỉ phục vụ API endpoints, không còn phục vụ frontend assets
-   Frontend có thể chạy độc lập hoặc thông qua Nginx reverse proxy
