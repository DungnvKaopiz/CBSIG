# Hướng dẫn cài đặt Authentication & Authorization

## Bước 1: Cài đặt Spatie Laravel Permission

```bash
# 1. Cài đặt package
docker-compose exec app composer require spatie/laravel-permission

# 2. Publish config và migrations (QUAN TRỌNG: Phải chạy bước này trước khi migrate)
docker-compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 3. Chạy migrations để tạo các bảng permissions, roles, model_has_permissions, etc.
docker-compose exec app php artisan migrate
```

**Lưu ý**: Nếu gặp lỗi "Table 'permissions' doesn't exist" khi chạy seeder, hãy đảm bảo đã chạy bước 2 (publish) và bước 3 (migrate) trước.

## Bước 2: Chạy Seeder để tạo Roles & Permissions

```bash
# Chạy RolePermissionSeeder
docker-compose exec app php artisan db:seed --class=RolePermissionSeeder

# Hoặc chạy tất cả seeders
docker-compose exec app php artisan db:seed
```

## Bước 3: Đăng ký Middleware (nếu cần)

Thêm vào `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'permission' => \App\Http\Middleware\CheckPermission::class,
    ]);
})
```

## Bước 4: Sử dụng trong Routes

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'permission:content.create'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
});
```

## Bước 5: Sử dụng trong Controllers

```php
// Check permission
if (!auth()->user()->can('content.edit')) {
    return response()->json(['message' => 'Forbidden'], 403);
}

// Check role
if (!auth()->user()->hasRole('admin')) {
    return response()->json(['message' => 'Forbidden'], 403);
}
```

## Bước 6: Sử dụng trong Vue Components

### Sử dụng useAuth Composable

```javascript
import { useAuth } from "@/composables/useAuth";

export default {
    setup() {
        const { user, isAuthenticated, hasRole, hasPermission, login, logout } =
            useAuth();

        // Check role
        if (hasRole("admin")) {
            // Do something
        }

        // Check permission
        if (hasPermission("content.edit")) {
            // Do something
        }

        return { user, isAuthenticated };
    },
};
```

### Sử dụng Directives

```vue
<template>
    <!-- Ẩn/hiện dựa trên permission -->
    <button v-permission="'content.edit'">Edit</button>

    <!-- Ẩn/hiện dựa trên role -->
    <div v-role="'admin'">Admin Panel</div>

    <!-- Check nhiều permissions (any) -->
    <button v-permission:any="['content.edit', 'content.delete']">
        Action
    </button>

    <!-- Check nhiều roles (any) -->
    <div v-role:any="['admin', 'editor']">Content</div>
</template>
```

## Default Users

Sau khi chạy seeder, các user mặc định:

-   **Super Admin**: admin@example.com / password
-   **Admin**: admin2@example.com / password
-   **Content Manager**: content@example.com / password
-   **Viewer**: viewer@example.com / password

## Roles & Permissions

### Roles:

-   `super-admin` - Tất cả quyền
-   `admin` - Quản lý users, devices, content
-   `content-manager` - Quản lý nội dung và schedules
-   `device-manager` - Quản lý STB devices
-   `editor` - Chỉnh sửa nội dung
-   `viewer` - Xem báo cáo, dashboard

### Permissions:

-   `users.*` - Quản lý users
-   `devices.*` - Quản lý STB devices
-   `content.*` - Quản lý nội dung
-   `schedules.*` - Quản lý lịch phát
-   `templates.*` - Quản lý templates
-   `reports.*` - Xem báo cáo
-   `settings.*` - Cài đặt hệ thống

## API Endpoints

### Public:

-   `POST /api/register` - Đăng ký
-   `POST /api/login` - Đăng nhập

### Protected (cần token):

-   `POST /api/logout` - Đăng xuất
-   `GET /api/user` - Lấy thông tin user hiện tại
-   `GET /api/posts` - Lấy danh sách posts
-   `POST /api/posts` - Tạo post mới
-   `PUT /api/posts/{id}` - Cập nhật post
-   `DELETE /api/posts/{id}` - Xóa post

## Lưu ý

1. Đảm bảo Docker containers đang chạy: `docker-compose up -d`
2. Đảm bảo PHP version >= 8.2 (đã có trong Dockerfile)
3. Chạy migrations trước khi seed
4. Token được lưu trong localStorage
5. Token tự động được thêm vào header của mọi request
6. Nếu token hết hạn (401), user sẽ tự động logout

## Các lệnh Docker thường dùng

```bash
# Khởi động containers
docker-compose up -d

# Dừng containers
docker-compose down

# Xem logs
docker-compose logs -f app

# Chạy artisan commands
docker-compose exec app php artisan [command]

# Chạy composer commands
docker-compose exec app composer [command]

# Truy cập vào container
docker-compose exec app bash
```
