# CBSIG Project - API & Architecture Guide

## 📋 Mục lục
1. [Tổng quan kiến trúc](#tổng-quan-kiến-trúc)
2. [Cách project chạy](#cách-project-chạy)
3. [Cách Frontend gọi API](#cách-frontend-gọi-api)
4. [Cách Backend xử lý API](#cách-backend-xử-lý-api)
5. [Flow hoàn chỉnh: Frontend → Backend](#flow-hoàn-chỉnh-frontend--backend)
6. [Ví dụ thực tế: Device CRUD](#ví-dụ-thực-tế-device-crud)

---

## 🏗️ Tổng quan kiến trúc

### Kiến trúc tổng thể
```
┌─────────────┐
│   Browser   │
│  (Port 8000)│
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    Nginx    │  Reverse Proxy
│  (Port 80)  │
└──────┬──────┘
       │
   ┌───┴───┐
   │       │
   ▼       ▼
┌──────┐ ┌────────┐
│Backend│ │Frontend│
│PHP-FPM│ │  Vite  │
│:9000  │ │ :5173  │
└───┬───┘ └────────┘
    │
    ▼
┌─────────┐
│  MySQL  │
│  :3306  │
└─────────┘
```

### Cấu trúc thư mục
```
CBSIG/
├── backend/              # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/    # API Controllers
│   │   │   ├── Requests/       # Form Request Validation
│   │   │   └── Middleware/     # Auth, Permissions
│   │   ├── Models/             # Eloquent Models
│   │   └── Services/          # Business Logic Layer
│   ├── routes/
│   │   └── api.php            # API Routes
│   └── database/
│       └── migrations/         # Database Schema
│
└── frontend/            # Vue.js SPA
    └── src/
        └── js/
            ├── api/            # API Services
            │   ├── client.js   # Axios instance
            │   └── deviceService.js
            └── components/      # Vue Components
```

---

## 🚀 Cách project chạy

### 1. Docker Setup

#### Services trong docker-compose.yml:
- **backend** (laravel_backend): PHP 8.2-FPM, port 9000 (internal)
- **frontend** (laravel_frontend): Node.js 20, Vite dev server, port 5173
- **nginx** (laravel_nginx): Reverse proxy, port 8000 (exposed)
- **mysql** (laravel_mysql): MySQL 8.0, port 3306
- **redis** (laravel_redis): Redis cache, port 6379

#### Khởi động project:
```bash
# 1. Build và start containers
docker-compose up -d --build

# 2. Setup backend
docker exec -it laravel_backend composer install
docker exec -it laravel_backend php artisan key:generate
docker exec -it laravel_backend php artisan migrate

# 3. Setup frontend
docker exec -it laravel_frontend npm install

# 4. Truy cập: http://localhost:8000
```

### 2. Nginx Routing

**File:** `backend/docker/nginx/default.conf`

```nginx
# API requests → Backend (Laravel)
location /api {
    root /var/www/html/public;
    try_files $uri $uri/ /index.php?$query_string;
}

# Frontend requests → Vite dev server
location / {
    proxy_pass http://frontend:5173;
    # WebSocket support for HMR
}
```

**Kết quả:**
- `http://localhost:8000/api/*` → Laravel Backend
- `http://localhost:8000/*` → Vue.js Frontend

### 3. Vite Proxy Configuration

**File:** `frontend/vite.config.js`

```javascript
proxy: {
  '/api': {
    target: 'http://nginx:80',  // Trong Docker
    // target: 'http://localhost:8000',  // Local development
    changeOrigin: true,
  },
}
```

**Kết quả:** Frontend gọi `/api/*` sẽ được proxy đến backend.

---

## 📡 Cách Frontend gọi API

### 1. API Client Setup

**File:** `frontend/src/js/api/client.js`

```javascript
import axios from 'axios';

const client = axios.create({
  baseURL: '/api',  // Tự động proxy đến backend
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000,
});
```

#### Request Interceptor:
- Tự động thêm Bearer token từ localStorage
```javascript
client.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

#### Response Interceptor:
- Xử lý lỗi chung (401, 403, 404, 500)
- Tự động logout nếu 401 Unauthorized

### 2. Service Layer Pattern

**File:** `frontend/src/js/api/deviceService.js`

```javascript
import client from './client';

export const deviceService = {
  getAll() {
    return client.get('/devices');
  },
  
  getById(id) {
    return client.get(`/devices/${id}`);
  },
  
  create(data) {
    return client.post('/devices', data);
  },
  
  update(id, data) {
    return client.put(`/devices/${id}`, data);
  },
  
  delete(id) {
    return client.delete(`/devices/${id}`);
  },
};
```

**Lợi ích:**
- Tái sử dụng code
- Dễ maintain
- Centralized API calls

### 3. Sử dụng trong Vue Components

**File:** `frontend/src/js/components/menu/DeviceTab.vue`

```javascript
import { deviceService } from '@/api/deviceService';

// Fetch devices
const fetchDevices = async () => {
  isLoadingDevices.value = true;
  try {
    const response = await deviceService.getAll();
    const devicesData = response.data?.data || response.data || [];
    devices.value = devicesData.map(mapDeviceToUI);
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to fetch devices';
  } finally {
    isLoadingDevices.value = false;
  }
};

// Create device
const handleAddDevice = async (deviceData) => {
  isLoading.value = true;
  try {
    const response = await deviceService.create(apiData);
    const device = response.data.data;
    devices.value.push(device);
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create device';
  } finally {
    isLoading.value = false;
  }
};

// Delete device
const deleteDevice = async (deviceId) => {
  try {
    await deviceService.delete(deviceId);
    devices.value = devices.value.filter(d => d.id !== deviceId);
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete device';
  }
};
```

---

## ⚙️ Cách Backend xử lý API

### 1. Route Definition

**File:** `backend/routes/api.php`

```php
// Devices CRUD với Route Model Binding
Route::middleware('auth:sanctum')->prefix('devices')->group(function () {
    Route::get('/', [DeviceController::class, 'index']);
    Route::get('/{device}', [DeviceController::class, 'show']);      // Model binding
    Route::post('/', [DeviceController::class, 'store']);
    Route::put('/{device}', [DeviceController::class, 'update']);   // Model binding
    Route::delete('/{device}', [DeviceController::class, 'destroy']); // Model binding
});
```

**Route Model Binding:**
- Laravel tự động inject `Device` model từ route parameter
- Tự động trả 404 nếu model không tồn tại

### 2. Form Request Validation

**File:** `backend/app/Http/Requests/StoreDeviceRequest.php`

```php
class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'device_uid' => 'required|string|max:255|unique:devices,device_uid',
            'name' => 'required|string|max:255',
            'status' => 'nullable|integer|in:1,2,3,4,5',
            // ... more rules
        ];
    }

    public function messages(): array
    {
        return [
            'device_uid.required' => 'Device UID is required.',
            // ... custom messages
        ];
    }
}
```

**Lợi ích:**
- Validation tách biệt khỏi controller
- Reusable validation rules
- Custom error messages

### 3. Controller Layer

**File:** `backend/app/Http/Controllers/DeviceController.php`

```php
class DeviceController extends Controller
{
    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService; // Dependency Injection
    }

    public function store(StoreDeviceRequest $request)
    {
        // Validation tự động xử lý bởi Form Request
        $device = $this->deviceService->create($request->validated());
        
        return response()->json([
            'message' => 'Device created successfully',
            'data' => $device,
        ], 201);
    }

    public function update(UpdateDeviceRequest $request, Device $device)
    {
        // $device đã được inject từ route model binding
        $device = $this->deviceService->update($device, $request->validated());
        
        return response()->json([
            'message' => 'Device updated successfully',
            'data' => $device,
        ]);
    }

    public function destroy(Device $device)
    {
        // $device đã được inject từ route model binding
        $this->deviceService->delete($device);
        
        return response()->json([
            'message' => 'Device deleted successfully',
        ]);
    }
}
```

### 4. Service Layer (Business Logic)

**File:** `backend/app/Services/DeviceService.php`

```php
class DeviceService
{
    public function create(array $data): Device
    {
        // Generate unique API key
        $apiKey = $this->generateUniqueApiKey();

        // Prepare data with defaults
        $deviceData = [
            'device_uid' => $data['device_uid'],
            'name' => $data['name'],
            'status' => $data['status'] ?? 5, // Default pending
            'api_key' => $apiKey,
            'canvas_width' => $data['canvas_width'] ?? 1280,
            'canvas_height' => $data['canvas_height'] ?? 720,
        ];

        return Device::create($deviceData);
    }

    public function update(Device $device, array $data): Device
    {
        $device->update($data);
        return $device->fresh();
    }

    public function delete(Device $device): bool
    {
        return $device->delete();
    }
}
```

**Lợi ích:**
- Business logic tách biệt khỏi controller
- Dễ test và maintain
- Reusable service methods

### 5. Model Layer

**File:** `backend/app/Models/Device.php`

```php
class Device extends Model
{
    protected $fillable = [
        'device_uid',
        'name',
        'location',
        'status',
        'ip_address',
        'api_key',
        'firmware_version',
        'canvas_width',
        'canvas_height',
    ];

    protected $casts = [
        'status' => 'integer',
        'canvas_width' => 'integer',
        'canvas_height' => 'integer',
        'last_seen_at' => 'datetime',
    ];
}
```

---

## 🔄 Flow hoàn chỉnh: Frontend → Backend

### Ví dụ: Create Device

```
1. User clicks "Add Device" button
   ↓
2. DeviceCreateModal opens
   ↓
3. User fills form and clicks "Add Device"
   ↓
4. DeviceTab.handleAddDevice() called
   ↓
5. deviceService.create(apiData) called
   ↓
6. client.post('/devices', data) executed
   ↓
7. Request Interceptor adds Bearer token
   ↓
8. Request sent to: /api/devices
   ↓
9. Nginx routes /api/* to Laravel backend
   ↓
10. Laravel Route: POST /api/devices
    ↓
11. Middleware: auth:sanctum (check token)
    ↓
12. DeviceController@store called
    ↓
13. StoreDeviceRequest validates data
    ↓
14. DeviceService.create() processes business logic
    ↓
15. Device::create() saves to database
    ↓
16. Response returned: { message, data }
    ↓
17. Frontend receives response
    ↓
18. Device added to devices array
    ↓
19. UI updates automatically (Vue reactivity)
```

### Request/Response Format

**Request:**
```http
POST /api/devices
Authorization: Bearer {token}
Content-Type: application/json

{
  "device_uid": "STB-001-ABC123",
  "name": "Lobby Display",
  "location": "Main Lobby",
  "status": 5,
  "ip_address": "192.168.1.100",
  "canvas_width": 1280,
  "canvas_height": 720
}
```

**Success Response (201):**
```json
{
  "message": "Device created successfully",
  "data": {
    "id": 1,
    "device_uid": "STB-001-ABC123",
    "name": "Lobby Display",
    "status": 5,
    "api_key": "sk_live_abc123...",
    "created_at": "2025-01-11T10:00:00.000000Z"
  }
}
```

**Error Response (422):**
```json
{
  "message": "Validation failed",
  "errors": {
    "device_uid": ["Device UID has already been taken."],
    "name": ["Device name is required."]
  }
}
```

---

## 📝 Ví dụ thực tế: Device CRUD

### 1. GET /api/devices (List all devices)

**Frontend:**
```javascript
const response = await deviceService.getAll();
const devices = response.data.data;
```

**Backend:**
```php
// Route
Route::get('/', [DeviceController::class, 'index']);

// Controller
public function index(Request $request)
{
    $devices = $this->deviceService->getAll();
    return response()->json([
        'message' => 'Devices fetched successfully',
        'data' => $devices,
    ]);
}

// Service
public function getAll()
{
    return Device::orderBy('created_at', 'desc')->get();
}
```

### 2. POST /api/devices (Create device)

**Frontend:**
```javascript
const apiData = {
  device_uid: 'STB-001-ABC123',
  name: 'Lobby Display',
  status: 5
};
const response = await deviceService.create(apiData);
```

**Backend:**
```php
// Route
Route::post('/', [DeviceController::class, 'store']);

// Controller
public function store(StoreDeviceRequest $request)
{
    $device = $this->deviceService->create($request->validated());
    return response()->json([
        'message' => 'Device created successfully',
        'data' => $device,
    ], 201);
}

// Form Request validates data
// Service generates API key and creates device
```

### 3. PUT /api/devices/{device} (Update device)

**Frontend:**
```javascript
const response = await deviceService.update(deviceId, updateData);
```

**Backend:**
```php
// Route với Model Binding
Route::put('/{device}', [DeviceController::class, 'update']);

// Controller
public function update(UpdateDeviceRequest $request, Device $device)
{
    // $device tự động inject từ route
    $device = $this->deviceService->update($device, $request->validated());
    return response()->json([
        'message' => 'Device updated successfully',
        'data' => $device,
    ]);
}
```

### 4. DELETE /api/devices/{device} (Delete device)

**Frontend:**
```javascript
await deviceService.delete(deviceId);
```

**Backend:**
```php
// Route với Model Binding
Route::delete('/{device}', [DeviceController::class, 'destroy']);

// Controller
public function destroy(Device $device)
{
    $this->deviceService->delete($device);
    return response()->json([
        'message' => 'Device deleted successfully',
    ]);
}
```

---

## 🔐 Authentication Flow

### 1. Login
```javascript
// Frontend: frontend/src/js/stores/authStore.js
const response = await client.post('/login', credentials);
token.value = response.data.token;
localStorage.setItem('token', token.value);
client.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
```

### 2. Protected Routes
```php
// Backend: routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    // All routes here require authentication
});
```

### 3. Token trong Request
- Request Interceptor tự động thêm token vào header
- Backend middleware `auth:sanctum` validate token
- Nếu token invalid → 401 → Frontend tự động logout

---

## 📊 Data Flow Summary

```
┌──────────────┐
│   Vue Component │
│  (DeviceTab.vue)│
└───────┬────────┘
        │
        │ deviceService.create(data)
        ▼
┌──────────────┐
│  API Service │
│(deviceService)│
└───────┬────────┘
        │
        │ client.post('/devices', data)
        ▼
┌──────────────┐
│  API Client  │
│  (client.js) │
└───────┬────────┘
        │
        │ + Bearer Token (interceptor)
        ▼
┌──────────────┐
│    Nginx     │
│  (Port 8000) │
└───────┬────────┘
        │
        │ /api/devices → Backend
        ▼
┌──────────────┐
│   Laravel    │
│   Route      │
│  (api.php)   │
└───────┬────────┘
        │
        │ auth:sanctum middleware
        ▼
┌──────────────┐
│  Controller  │
│(DeviceController)│
└───────┬────────┘
        │
        │ StoreDeviceRequest validates
        ▼
┌──────────────┐
│   Service    │
│(DeviceService)│
└───────┬────────┘
        │
        │ Device::create()
        ▼
┌──────────────┐
│    Model     │
│   (Device)   │
└───────┬────────┘
        │
        │ INSERT INTO devices
        ▼
┌──────────────┐
│    MySQL     │
│  Database    │
└──────────────┘
```

---

## 🎯 Best Practices

### Frontend:
1. ✅ Sử dụng Service Layer pattern
2. ✅ Centralized error handling trong interceptors
3. ✅ Loading states cho UX tốt hơn
4. ✅ Validation ở cả frontend và backend
5. ✅ Type safety với TypeScript (nếu có)

### Backend:
1. ✅ Form Request cho validation
2. ✅ Service Layer cho business logic
3. ✅ Route Model Binding
4. ✅ Dependency Injection
5. ✅ RESTful API conventions

---

## 🔧 Troubleshooting

### API không hoạt động:
1. Kiểm tra Docker containers đang chạy: `docker ps`
2. Kiểm tra Nginx routing: `docker logs laravel_nginx`
3. Kiểm tra Backend logs: `docker logs laravel_backend`
4. Kiểm tra token trong localStorage
5. Kiểm tra CORS nếu có lỗi

### CORS Issues:
- Backend đã cấu hình CORS trong Laravel
- Nginx proxy đã xử lý headers đúng cách

### Authentication Issues:
- Kiểm tra token trong localStorage
- Kiểm tra token format: `Bearer {token}`
- Kiểm tra token expiry
- Kiểm tra Sanctum configuration

---

## 📚 Tài liệu tham khảo

- **Laravel API**: https://laravel.com/docs/api
- **Vue.js**: https://vuejs.org/
- **Axios**: https://axios-http.com/
- **Laravel Sanctum**: https://laravel.com/docs/sanctum

---

**Tài liệu này được tạo tự động dựa trên codebase hiện tại.**

