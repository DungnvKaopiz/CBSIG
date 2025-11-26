# Tổng Quan Database - CBSIG Digital Signage System

## Mục Lục

1. [Tổng Quan](#tổng-quan)
2. [Chi Tiết Các Bảng](#chi-tiết-các-bảng)
3. [Mối Quan Hệ](#mối-quan-hệ)
4. [Demo Data](#demo-data)

---

## Tổng Quan

Hệ thống database được thiết kế cho **Digital Signage System** với các chức năng chính:

-   Quản lý nội dung đa phương tiện (video, image, HTML, YouTube)
-   Quản lý playlist và layout
-   Lập lịch phát nội dung trên thiết bị
-   Analytics và logging
-   Phân quyền người dùng

**Tổng số bảng**: 20+ bảng (bao gồm hệ thống Laravel và Spatie Permission)

---

## Chi Tiết Các Bảng

### 1. Bảng Quản Lý Người Dùng

#### `users`

Quản lý thông tin người dùng hệ thống.

| Trường              | Kiểu      | Mô tả                    |
| ------------------- | --------- | ------------------------ |
| `id`                | bigint    | Primary key              |
| `name`              | string    | Tên người dùng           |
| `email`             | string    | Email (unique)           |
| `email_verified_at` | timestamp | Thời gian xác thực email |
| `password`          | string    | Mật khẩu (hashed)        |
| `remember_token`    | string    | Token nhớ đăng nhập      |
| `created_at`        | timestamp | Thời gian tạo            |
| `updated_at`        | timestamp | Thời gian cập nhật       |

**Indexes**: `email` (unique)

---

#### `password_reset_tokens`

Quản lý token reset mật khẩu.

| Trường       | Kiểu      | Mô tả               |
| ------------ | --------- | ------------------- |
| `email`      | string    | Email (primary key) |
| `token`      | string    | Token reset         |
| `created_at` | timestamp | Thời gian tạo       |

---

#### `sessions`

Quản lý session đăng nhập.

| Trường          | Kiểu       | Mô tả                    |
| --------------- | ---------- | ------------------------ |
| `id`            | string     | Session ID (primary key) |
| `user_id`       | bigint     | Foreign key → users      |
| `ip_address`    | string(45) | Địa chỉ IP               |
| `user_agent`    | text       | User agent               |
| `payload`       | longText   | Session data             |
| `last_activity` | integer    | Timestamp hoạt động cuối |

**Indexes**: `user_id`, `last_activity`

---

### 2. Bảng Phân Quyền (Spatie Permission)

#### `permissions`

Quản lý các quyền trong hệ thống.

| Trường       | Kiểu      | Mô tả                             |
| ------------ | --------- | --------------------------------- |
| `id`         | bigint    | Primary key                       |
| `name`       | string    | Tên quyền (ví dụ: 'view-content') |
| `guard_name` | string    | Guard name (thường là 'web')      |
| `created_at` | timestamp | Thời gian tạo                     |
| `updated_at` | timestamp | Thời gian cập nhật                |

**Indexes**: `(name, guard_name)` (unique)

---

#### `roles`

Quản lý các vai trò trong hệ thống.

| Trường       | Kiểu      | Mô tả                                   |
| ------------ | --------- | --------------------------------------- |
| `id`         | bigint    | Primary key                             |
| `name`       | string    | Tên vai trò (ví dụ: 'admin', 'manager') |
| `guard_name` | string    | Guard name                              |
| `created_at` | timestamp | Thời gian tạo                           |
| `updated_at` | timestamp | Thời gian cập nhật                      |

**Indexes**: `(name, guard_name)` (unique)

---

#### `model_has_permissions`

Gán quyền trực tiếp cho model (polymorphic).

| Trường          | Kiểu   | Mô tả                                  |
| --------------- | ------ | -------------------------------------- |
| `permission_id` | bigint | Foreign key → permissions              |
| `model_type`    | string | Model class (ví dụ: 'App\Models\User') |
| `model_id`      | bigint | ID của model                           |

**Primary Key**: `(permission_id, model_id, model_type)`

---

#### `model_has_roles`

Gán vai trò cho model (polymorphic).

| Trường       | Kiểu   | Mô tả               |
| ------------ | ------ | ------------------- |
| `role_id`    | bigint | Foreign key → roles |
| `model_type` | string | Model class         |
| `model_id`   | bigint | ID của model        |

**Primary Key**: `(role_id, model_id, model_type)`

---

#### `role_has_permissions`

Quyền của từng vai trò.

| Trường          | Kiểu   | Mô tả                     |
| --------------- | ------ | ------------------------- |
| `permission_id` | bigint | Foreign key → permissions |
| `role_id`       | bigint | Foreign key → roles       |

**Primary Key**: `(permission_id, role_id)`

---

### 3. Bảng Quản Lý Nội Dung

#### `contents`

Quản lý các nội dung đa phương tiện.

| Trường                | Kiểu        | Mô tả                                                                         |
| --------------------- | ----------- | ----------------------------------------------------------------------------- |
| `id`                  | bigint      | Primary key                                                                   |
| `name`                | string      | Tên nội dung hoặc tiêu đề video YouTube                                       |
| `type`                | tinyint     | Loại: 1=video, 2=image, 3=playlist, 4=youtube                                 |
| `file_url`            | text        | URL tới file trên Cloud Storage hoặc URL video YouTube                        |
| `file_size`           | bigint      | Kích thước file (bytes), NULL cho YouTube                                     |
| `checksum`            | string(64)  | MD5/SHA256 của file, hoặc Video ID của YouTube                                |
| `duration_seconds`    | integer     | Thời lượng phát (giây)                                                        |
| `thumbnail_url`       | string(255) | URL ảnh thumbnail                                                             |
| `uploaded_by_user_id` | bigint      | Foreign key → users (onDelete: restrict)                                      |
| `parent_content_id`   | bigint      | Foreign key → contents (onDelete: set null) - Reference khi clone với effects |
| `effects_metadata`    | json        | Text overlay và effects metadata (xem chi tiết bên dưới)                      |
| `created_at`          | timestamp   | Thời gian tạo                                                                 |
| `updated_at`          | timestamp   | Thời gian cập nhật                                                            |

**Foreign Keys**:

-   `uploaded_by_user_id` → `users.id` (restrict on delete)
-   `parent_content_id` → `contents.id` (set null on delete)

**Effects Metadata Structure** (JSON):

```json
{
    "rotation": 0,
    "text": "Sample Text",
    "fontFamily": "Arial",
    "fontSize": 24,
    "fontColor": "#000000",
    "fontWeight": "normal",
    "textAlign": "center",
    "orientation": "horizontal",
    "letterSpacing": 0,
    "lineHeight": 1.5,
    "horizontalPosition": "center",
    "verticalPosition": "center",
    "startTime": 0,
    "endTime": 10,
    "displayDuration": 5,
    "interval": 2,
    "scrollMode": "none",
    "scrollSpeed": 50,
    "loopCount": 1,
    "outlineEnabled": false,
    "outlineColor": "#000000",
    "outlineWidth": 1,
    "shadowEnabled": false,
    "shadowColor": "#000000",
    "shadowBlur": 0
}
```

---

### 4. Bảng Quản Lý Playlist

#### `playlists`

Quản lý các playlist nội dung.

| Trường               | Kiểu      | Mô tả                                    |
| -------------------- | --------- | ---------------------------------------- |
| `id`                 | bigint    | Primary key                              |
| `name`               | string    | Tên playlist                             |
| `description`        | text      | Mô tả playlist                           |
| `created_by_user_id` | bigint    | Foreign key → users (onDelete: restrict) |
| `is_active`          | boolean   | Trạng thái hoạt động (default: true)     |
| `created_at`         | timestamp | Thời gian tạo                            |
| `updated_at`         | timestamp | Thời gian cập nhật                       |

**Foreign Keys**:

-   `created_by_user_id` → `users.id` (restrict on delete)

---

#### `playlist_items`

Các item trong playlist.

| Trường        | Kiểu      | Mô tả                                       |
| ------------- | --------- | ------------------------------------------- |
| `id`          | bigint    | Primary key                                 |
| `playlist_id` | bigint    | Foreign key → playlists (onDelete: cascade) |
| `content_id`  | bigint    | Foreign key → contents (onDelete: cascade)  |
| `order_index` | integer   | Thứ tự phát (default: 0)                    |
| `created_at`  | timestamp | Thời gian tạo                               |
| `updated_at`  | timestamp | Thời gian cập nhật                          |

**Foreign Keys**:

-   `playlist_id` → `playlists.id` (cascade on delete)
-   `content_id` → `contents.id` (cascade on delete)

**Indexes**: `(playlist_id, order_index)`

---

### 5. Bảng Quản Lý Layout

#### `layouts`

Quản lý các layout hiển thị.

| Trường               | Kiểu      | Mô tả                                     |
| -------------------- | --------- | ----------------------------------------- |
| `id`                 | bigint    | Primary key                               |
| `name`               | string    | Tên layout                                |
| `description`        | text      | Mô tả layout                              |
| `canvas_width`       | integer   | Chiều rộng canvas (pixels, default: 1280) |
| `canvas_height`      | integer   | Chiều cao canvas (pixels, default: 720)   |
| `created_by_user_id` | bigint    | Foreign key → users (onDelete: restrict)  |
| `created_at`         | timestamp | Thời gian tạo                             |
| `updated_at`         | timestamp | Thời gian cập nhật                        |

**Foreign Keys**:

-   `created_by_user_id` → `users.id` (restrict on delete)

---

#### `layout_frames`

Các frame trong layout.

| Trường           | Kiểu      | Mô tả                                                  |
| ---------------- | --------- | ------------------------------------------------------ |
| `id`             | bigint    | Primary key                                            |
| `layout_id`      | bigint    | Foreign key → layouts (onDelete: cascade)              |
| `name`           | string    | Tên/identifier của frame                               |
| `content_id`     | bigint    | Foreign key → contents (onDelete: set null)            |
| `frame_metadata` | json      | Metadata về vị trí và hiển thị (xem chi tiết bên dưới) |
| `created_at`     | timestamp | Thời gian tạo                                          |
| `updated_at`     | timestamp | Thời gian cập nhật                                     |

**Foreign Keys**:

-   `layout_id` → `layouts.id` (cascade on delete)
-   `content_id` → `contents.id` (set null on delete)

**Indexes**: `(layout_id, order_index)`, `(layout_id, z_index)`

**Frame Metadata Structure** (JSON):

```json
{
    "x": 0,
    "y": 0,
    "width": 960,
    "height": 540,
    "z_index": 1,
    "image_fit": 1,
    "order_index": 0
}
```

---

### 6. Bảng Quản Lý Lịch Phát

#### `schedules`

Quản lý lịch phát nội dung.

| Trường               | Kiểu      | Mô tả                                                                                       |
| -------------------- | --------- | ------------------------------------------------------------------------------------------- |
| `id`                 | bigint    | Primary key                                                                                 |
| `name`               | string    | Tên lịch phát                                                                               |
| `description`        | text      | Mô tả lịch phát                                                                             |
| `created_by_user_id` | bigint    | Foreign key → users (onDelete: restrict)                                                    |
| `type`               | tinyint   | Loại: 1=content, 2=playlist, 3=layout                                                       |
| `item_id`            | bigint    | ID của content, playlist, hoặc layout                                                       |
| `start_time`         | time      | Thời gian bắt đầu (HH:mm), nullable                                                         |
| `end_time`           | time      | Thời gian kết thúc (HH:mm), nullable                                                        |
| `repeat`             | tinyint   | Lặp lại: 1=Everyday, 2=Weekdays Only, 3=Weekends Only, 4=Custom, 5=Custom Date (default: 1) |
| `days_of_week`       | json      | Mảng các ngày: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']                            |
| `custom_dates`       | json      | Mảng các ngày tùy chỉnh (YYYY-MM-DD) cho Custom Date                                        |
| `status`             | tinyint   | Trạng thái: 1=active, 2=paused (default: 1)                                                 |
| `schedule_config`    | json      | Cấu hình lịch phát đầy đủ (JSON)                                                            |
| `created_at`         | timestamp | Thời gian tạo                                                                               |
| `updated_at`         | timestamp | Thời gian cập nhật                                                                          |

**Foreign Keys**:

-   `created_by_user_id` → `users.id` (restrict on delete)

**Repeat Types**:

-   `1`: Everyday - Phát mỗi ngày
-   `2`: Weekdays Only - Chỉ các ngày trong tuần (Mon-Fri)
-   `3`: Weekends Only - Chỉ cuối tuần (Sat-Sun)
-   `4`: Custom - Tùy chỉnh theo days_of_week
-   `5`: Custom Date - Phát vào các ngày cụ thể trong custom_dates

---

#### `schedule_devices`

Quan hệ nhiều-nhiều giữa schedules và devices.

| Trường        | Kiểu      | Mô tả                                       |
| ------------- | --------- | ------------------------------------------- |
| `id`          | bigint    | Primary key                                 |
| `schedule_id` | bigint    | Foreign key → schedules (onDelete: cascade) |
| `device_id`   | bigint    | Foreign key → devices (onDelete: cascade)   |
| `created_at`  | timestamp | Thời gian tạo                               |
| `updated_at`  | timestamp | Thời gian cập nhật                          |

**Foreign Keys**:

-   `schedule_id` → `schedules.id` (cascade on delete)
-   `device_id` → `devices.id` (cascade on delete)

**Indexes**: `(schedule_id, device_id)` (unique), `device_id`

---

### 7. Bảng Quản Lý Thiết Bị

#### `devices`

Quản lý các thiết bị STB (Set-Top Box).

| Trường             | Kiểu       | Mô tả                                                                       |
| ------------------ | ---------- | --------------------------------------------------------------------------- |
| `id`               | bigint     | Primary key                                                                 |
| `device_uid`       | string     | Mã định danh duy nhất của thiết bị (unique)                                 |
| `name`             | string     | Tên thân thiện của thiết bị                                                 |
| `location`         | string     | Vị trí đặt thiết bị                                                         |
| `status`           | tinyint    | Trạng thái: 1=online, 2=offline, 3=syncing, 4=error, 5=pending (default: 5) |
| `last_seen_at`     | timestamp  | Thời gian kết nối cuối                                                      |
| `ip_address`       | string(45) | Địa chỉ IP                                                                  |
| `api_key`          | string     | API key để xác thực (unique)                                                |
| `firmware_version` | string(50) | Phiên bản firmware                                                          |
| `canvas_width`     | integer    | Chiều rộng canvas (pixels, default: 1280)                                   |
| `canvas_height`    | integer    | Chiều cao canvas (pixels, default: 720)                                     |
| `created_at`       | timestamp  | Thời gian tạo                                                               |
| `updated_at`       | timestamp  | Thời gian cập nhật                                                          |

**Indexes**: `device_uid` (unique), `api_key` (unique), `status`

**Status Values**:

-   `1`: Online - Thiết bị đang online
-   `2`: Offline - Thiết bị offline
-   `3`: Syncing - Đang đồng bộ dữ liệu
-   `4`: Error - Có lỗi
-   `5`: Pending - Chờ kích hoạt

---

### 8. Bảng Analytics và Logging

#### `analytics_logs`

Log dữ liệu analytics từ thiết bị (face detection, body detection).

| Trường                | Kiểu       | Mô tả                                            |
| --------------------- | ---------- | ------------------------------------------------ |
| `id`                  | bigint     | Primary key                                      |
| `device_id`           | bigint     | Foreign key → devices (onDelete: cascade)        |
| `timestamp`           | datetime   | Thời điểm sự kiện xảy ra trên STB                |
| `event_type`          | tinyint    | Loại sự kiện: 1=face_detection, 2=body_detection |
| `age_group`           | string(20) | Nhóm tuổi (ví dụ: '18-25', '26-35')              |
| `gender`              | tinyint    | Giới tính: 1=male, 2=female, 3=unknown           |
| `posture`             | string(50) | Tư thế (ví dụ: 'standing', 'sitting')            |
| `current_content_id`  | bigint     | Foreign key → contents (onDelete: set null)      |
| `current_schedule_id` | bigint     | Foreign key → schedules (onDelete: set null)     |
| `raw_data_json`       | json       | Dữ liệu thô từ thiết bị (JSON)                   |
| `created_at`          | timestamp  | Thời điểm log được server nhận                   |

**Foreign Keys**:

-   `device_id` → `devices.id` (cascade on delete)
-   `current_content_id` → `contents.id` (set null on delete)
-   `current_schedule_id` → `schedules.id` (set null on delete)

**Indexes**: `timestamp`, `(age_group, gender)`

**Event Types**:

-   `1`: Face Detection - Phát hiện khuôn mặt
-   `2`: Body Detection - Phát hiện cơ thể

---

#### `system_logs`

Log hệ thống từ WebCMS và STB Player.

| Trường         | Kiểu        | Mô tả                                       |
| -------------- | ----------- | ------------------------------------------- |
| `id`           | bigint      | Primary key                                 |
| `timestamp`    | timestamp   | Thời điểm log (default: current timestamp)  |
| `level`        | tinyint     | Mức độ: 1=INFO, 2=WARNING, 3=ERROR, 4=DEBUG |
| `source`       | string(100) | Nguồn gốc: 'WebCMS', 'STB_Player'           |
| `message`      | text        | Nội dung log                                |
| `user_id`      | bigint      | Foreign key → users (onDelete: set null)    |
| `device_id`    | bigint      | Foreign key → devices (onDelete: set null)  |
| `details_json` | json        | Chi tiết bổ sung (JSON)                     |
| `created_at`   | timestamp   | Thời gian tạo                               |
| `updated_at`   | timestamp   | Thời gian cập nhật                          |

**Foreign Keys**:

-   `user_id` → `users.id` (set null on delete)
-   `device_id` → `devices.id` (set null on delete)

**Indexes**: `(level, source)`

**Log Levels**:

-   `1`: INFO - Thông tin
-   `2`: WARNING - Cảnh báo
-   `3`: ERROR - Lỗi
-   `4`: DEBUG - Debug

---

### 9. Bảng Hệ Thống Laravel

#### `cache`, `cache_locks`

Quản lý cache của Laravel.

#### `jobs`, `job_batches`, `failed_jobs`

Quản lý queue jobs của Laravel.

#### `personal_access_tokens`

API tokens cho Sanctum authentication.

---

## Mối Quan Hệ

### Sơ Đồ Quan Hệ Chính

```
users (1) ──┬── (N) contents (uploaded_by_user_id)
            ├── (N) playlists (created_by_user_id)
            ├── (N) layouts (created_by_user_id)
            ├── (N) schedules (created_by_user_id)
            └── (N) system_logs (user_id)

contents (1) ──┬── (N) playlist_items (content_id)
              ├── (N) layout_frames (content_id)
              ├── (N) analytics_logs (current_content_id)
              └── (1) contents (parent_content_id) [self-reference]

playlists (1) ── (N) playlist_items (playlist_id)

layouts (1) ── (N) layout_frames (layout_id)

schedules (1) ──┬── (N) schedule_devices (schedule_id)
                └── (N) analytics_logs (current_schedule_id)

devices (1) ──┬── (N) schedule_devices (device_id)
              ├── (N) analytics_logs (device_id)
              └── (N) system_logs (device_id)
```

### Quan Hệ Nhiều-Nhiều

-   **schedules ↔ devices**: Thông qua bảng `schedule_devices`
    -   Một schedule có thể được gán cho nhiều devices
    -   Một device có thể có nhiều schedules

---

## Demo Data

### 1. Users

```sql
INSERT INTO users (id, name, email, password, email_verified_at, created_at, updated_at) VALUES
(1, 'Admin User', 'admin@cbsig.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW(), NOW()),
(2, 'Manager User', 'manager@cbsig.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW(), NOW()),
(3, 'Content Creator', 'creator@cbsig.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW(), NOW());
```

### 2. Contents

```sql
INSERT INTO contents (id, name, type, file_url, file_size, checksum, duration_seconds, thumbnail_url, uploaded_by_user_id, parent_content_id, effects_metadata, created_at, updated_at) VALUES
(1, 'Welcome Video', 1, 'https://storage.example.com/videos/welcome.mp4', 15728640, 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', 30, 'https://storage.example.com/thumbnails/welcome.jpg', 1, NULL, NULL, NOW(), NOW()),
(2, 'Promotion Image', 2, 'https://storage.example.com/images/promotion.jpg', 2097152, 'b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7', NULL, 'https://storage.example.com/thumbnails/promotion.jpg', 1, NULL, NULL, NOW(), NOW()),
(3, 'YouTube Video', 4, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', NULL, 'dQw4w9WgXcQ', 212, 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg', 2, NULL, NULL, NOW(), NOW()),
(4, 'Text Overlay Content', 1, 'https://storage.example.com/videos/base.mp4', 10485760, 'c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8', 60, 'https://storage.example.com/thumbnails/base.jpg', 1, 1, '{"text":"Welcome to CBSIG","fontSize":48,"fontColor":"#FFFFFF","horizontalPosition":"center","verticalPosition":"top","startTime":0,"endTime":60}', NOW(), NOW()),
(5, 'HTML Content', 3, 'https://storage.example.com/html/weather.html', 51200, 'd4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9', NULL, NULL, 2, NULL, NULL, NOW(), NOW());
```

### 3. Playlists

```sql
INSERT INTO playlists (id, name, description, created_by_user_id, is_active, created_at, updated_at) VALUES
(1, 'Morning Playlist', 'Playlist phát vào buổi sáng', 1, true, NOW(), NOW()),
(2, 'Evening Playlist', 'Playlist phát vào buổi tối', 1, true, NOW(), NOW()),
(3, 'Weekend Special', 'Playlist đặc biệt cuối tuần', 2, true, NOW(), NOW());
```

### 4. Playlist Items

```sql
INSERT INTO playlist_items (id, playlist_id, content_id, order_index, created_at, updated_at) VALUES
(1, 1, 1, 0, NOW(), NOW()),
(2, 1, 2, 1, NOW(), NOW()),
(3, 1, 5, 2, NOW(), NOW()),
(4, 2, 3, 0, NOW(), NOW()),
(5, 2, 1, 1, NOW(), NOW()),
(6, 3, 3, 0, NOW(), NOW()),
(7, 3, 4, 1, NOW(), NOW());
```

### 5. Layouts

```sql
INSERT INTO layouts (id, name, description, canvas_width, canvas_height, created_by_user_id, created_at, updated_at) VALUES
(1, 'Split Screen Layout', 'Layout chia màn hình 2 phần', 1280, 720, 1, NOW(), NOW()),
(2, 'Full Screen Layout', 'Layout toàn màn hình', 1920, 1080, 1, NOW(), NOW()),
(3, 'Picture in Picture', 'Layout PiP', 1280, 720, 2, NOW(), NOW());
```

### 6. Layout Frames

```sql
INSERT INTO layout_frames (id, layout_id, name, content_id, frame_metadata, created_at, updated_at) VALUES
(1, 1, 'Left Frame', 1, '{"x":0,"y":0,"width":640,"height":720,"z_index":1,"image_fit":1,"order_index":0}', NOW(), NOW()),
(2, 1, 'Right Frame', 2, '{"x":640,"y":0,"width":640,"height":720,"z_index":1,"image_fit":1,"order_index":1}', NOW(), NOW()),
(3, 2, 'Main Frame', 3, '{"x":0,"y":0,"width":1920,"height":1080,"z_index":1,"image_fit":1,"order_index":0}', NOW(), NOW()),
(4, 3, 'Background Frame', 1, '{"x":0,"y":0,"width":1280,"height":720,"z_index":1,"image_fit":1,"order_index":0}', NOW(), NOW()),
(5, 3, 'Overlay Frame', 5, '{"x":960,"y":520,"width":320,"height":200,"z_index":2,"image_fit":1,"order_index":1}', NOW(), NOW());
```

### 7. Devices

```sql
INSERT INTO devices (id, device_uid, name, location, status, last_seen_at, ip_address, api_key, firmware_version, canvas_width, canvas_height, created_at, updated_at) VALUES
(1, 'STB-001-ABC123', 'Lobby Display 1', 'Tầng 1 - Sảnh chính', 1, NOW(), '192.168.1.100', 'sk_live_abc123def456ghi789', 'v1.2.3', 1280, 720, NOW(), NOW()),
(2, 'STB-002-XYZ789', 'Meeting Room Display', 'Tầng 2 - Phòng họp A', 1, NOW(), '192.168.1.101', 'sk_live_xyz789uvw456rst123', 'v1.2.3', 1920, 1080, NOW(), NOW()),
(3, 'STB-003-DEF456', 'Reception Display', 'Tầng 1 - Lễ tân', 2, DATE_SUB(NOW(), INTERVAL 1 HOUR), '192.168.1.102', 'sk_live_def456ghi789jkl012', 'v1.2.2', 1280, 720, NOW(), NOW()),
(4, 'STB-004-GHI789', 'Cafeteria Display', 'Tầng 3 - Căng tin', 5, NULL, NULL, 'sk_live_ghi789jkl012mno345', NULL, 1280, 720, NOW(), NOW());
```

### 8. Schedules

```sql
INSERT INTO schedules (id, name, description, created_by_user_id, type, item_id, start_time, end_time, repeat, days_of_week, custom_dates, status, schedule_config, created_at, updated_at) VALUES
(1, 'Morning Content Schedule', 'Lịch phát nội dung buổi sáng', 1, 2, 1, '08:00:00', '12:00:00', 2, NULL, NULL, 1, '{"type":"playlist","playlist_id":1,"start_time":"08:00","end_time":"12:00","repeat":2}', NOW(), NOW()),
(2, 'Evening Content Schedule', 'Lịch phát nội dung buổi tối', 1, 2, 2, '18:00:00', '22:00:00', 1, NULL, NULL, 1, '{"type":"playlist","playlist_id":2,"start_time":"18:00","end_time":"22:00","repeat":1}', NOW(), NOW()),
(3, 'Weekend Layout Schedule', 'Lịch phát layout cuối tuần', 2, 3, 1, '09:00:00', '21:00:00', 3, NULL, NULL, 1, '{"type":"layout","layout_id":1,"start_time":"09:00","end_time":"21:00","repeat":3}', NOW(), NOW()),
(4, 'Single Content Schedule', 'Lịch phát nội dung đơn lẻ', 1, 1, 3, '10:00:00', '11:00:00', 4, '["Mon","Wed","Fri"]', NULL, 1, '{"type":"content","content_id":3,"start_time":"10:00","end_time":"11:00","repeat":4,"days_of_week":["Mon","Wed","Fri"]}', NOW(), NOW()),
(5, 'Custom Date Schedule', 'Lịch phát ngày đặc biệt', 1, 1, 1, '00:00:00', '23:59:59', 5, NULL, '["2025-12-25","2026-01-01"]', 1, '{"type":"content","content_id":1,"start_time":"00:00","end_time":"23:59","repeat":5,"custom_dates":["2025-12-25","2026-01-01"]}', NOW(), NOW());
```

### 9. Schedule Devices

```sql
INSERT INTO schedule_devices (id, schedule_id, device_id, created_at, updated_at) VALUES
(1, 1, 1, NOW(), NOW()),
(2, 1, 2, NOW(), NOW()),
(3, 2, 1, NOW(), NOW()),
(4, 2, 3, NOW(), NOW()),
(5, 3, 2, NOW(), NOW()),
(6, 4, 1, NOW(), NOW()),
(7, 5, 1, NOW(), NOW()),
(8, 5, 2, NOW(), NOW());
```

### 10. Analytics Logs

```sql
INSERT INTO analytics_logs (id, device_id, timestamp, event_type, age_group, gender, posture, current_content_id, current_schedule_id, raw_data_json, created_at) VALUES
(1, 1, '2025-11-11 10:15:30', 1, '26-35', 1, 'standing', 1, 1, '{"confidence":0.95,"face_count":2,"detection_area":{"x":100,"y":150,"width":200,"height":250}}', NOW()),
(2, 1, '2025-11-11 10:16:45', 2, '18-25', 2, 'standing', 1, 1, '{"confidence":0.87,"body_count":1,"detection_area":{"x":300,"y":200,"width":150,"height":300}}', NOW()),
(3, 2, '2025-11-11 14:20:10', 1, '36-45', 1, 'sitting', 3, 2, '{"confidence":0.92,"face_count":1,"detection_area":{"x":500,"y":300,"width":180,"height":220}}', NOW()),
(4, 1, '2025-11-11 18:30:00', 1, '26-35', 2, 'standing', 2, 2, '{"confidence":0.88,"face_count":3,"detection_area":{"x":200,"y":100,"width":400,"height":500}}', NOW());
```

### 11. System Logs

```sql
INSERT INTO system_logs (id, timestamp, level, source, message, user_id, device_id, details_json, created_at, updated_at) VALUES
(1, NOW(), 1, 'WebCMS', 'User logged in successfully', 1, NULL, '{"ip":"192.168.1.50","user_agent":"Mozilla/5.0"}', NOW(), NOW()),
(2, NOW(), 1, 'STB_Player', 'Device connected successfully', NULL, 1, '{"firmware":"v1.2.3","ip":"192.168.1.100"}', NOW(), NOW()),
(3, NOW(), 2, 'STB_Player', 'Content sync failed', NULL, 3, '{"content_id":1,"error":"Network timeout"}', NOW(), NOW()),
(4, NOW(), 3, 'WebCMS', 'Failed to upload content', 2, NULL, '{"file_name":"large_video.mp4","error":"File size exceeds limit"}', NOW(), NOW()),
(5, NOW(), 1, 'STB_Player', 'Schedule updated', NULL, 1, '{"schedule_id":1,"action":"activated"}', NOW(), NOW());
```

### 12. Permissions (Mẫu)

```sql
INSERT INTO permissions (id, name, guard_name, created_at, updated_at) VALUES
(1, 'view-content', 'web', NOW(), NOW()),
(2, 'create-content', 'web', NOW(), NOW()),
(3, 'edit-content', 'web', NOW(), NOW()),
(4, 'delete-content', 'web', NOW(), NOW()),
(5, 'view-playlist', 'web', NOW(), NOW()),
(6, 'create-playlist', 'web', NOW(), NOW()),
(7, 'view-schedule', 'web', NOW(), NOW()),
(8, 'create-schedule', 'web', NOW(), NOW()),
(9, 'view-device', 'web', NOW(), NOW()),
(10, 'manage-device', 'web', NOW(), NOW()),
(11, 'view-analytics', 'web', NOW(), NOW()),
(12, 'view-logs', 'web', NOW(), NOW());
```

### 13. Roles (Mẫu)

```sql
INSERT INTO roles (id, name, guard_name, created_at, updated_at) VALUES
(1, 'admin', 'web', NOW(), NOW()),
(2, 'manager', 'web', NOW(), NOW()),
(3, 'content-creator', 'web', NOW(), NOW());
```

### 14. Role Has Permissions (Mẫu)

```sql
INSERT INTO role_has_permissions (permission_id, role_id) VALUES
-- Admin có tất cả quyền
(1, 1), (2, 1), (3, 1), (4, 1), (5, 1), (6, 1), (7, 1), (8, 1), (9, 1), (10, 1), (11, 1), (12, 1),
-- Manager có quyền xem và quản lý
(1, 2), (2, 2), (3, 2), (5, 2), (6, 2), (7, 2), (8, 2), (9, 2), (11, 2), (12, 2),
-- Content Creator chỉ có quyền nội dung
(1, 3), (2, 3), (3, 3), (5, 3), (6, 3);
```

### 15. Model Has Roles (Mẫu)

```sql
INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES
(1, 'App\\Models\\User', 1),  -- Admin user
(2, 'App\\Models\\User', 2),  -- Manager user
(3, 'App\\Models\\User', 3);  -- Content creator user
```

---

## Ghi Chú Quan Trọng

1. **Password Hash**: Tất cả password trong demo data đều là `password` (đã được hash)
2. **Timestamps**: Sử dụng `NOW()` cho demo, trong thực tế sẽ tự động set bởi Laravel
3. **Foreign Keys**: Đảm bảo insert theo thứ tự để không vi phạm foreign key constraints
4. **JSON Fields**: Các trường JSON cần được format đúng cú pháp
5. **Unique Constraints**: `device_uid`, `api_key`, `email` phải unique
6. **Status Values**: Sử dụng đúng giá trị enum như đã định nghĩa

---

## Thứ Tự Insert Demo Data

1. `users`
2. `permissions`, `roles`, `role_has_permissions`
3. `model_has_roles`
4. `contents`
5. `playlists`, `playlist_items`
6. `layouts`, `layout_frames`
7. `devices`
8. `schedules`, `schedule_devices`
9. `analytics_logs`, `system_logs`

---

**Tài liệu này được tạo tự động từ các migration files.**
**Cập nhật lần cuối**: 2025-11-11
