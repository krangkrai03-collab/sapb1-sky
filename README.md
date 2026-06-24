# Admin Portal (CodeIgniter 4 + Shield)

ระบบหลังบ้าน (admin portal) บน **CodeIgniter 4** ใช้ **CodeIgniter Shield** สำหรับ
authentication/RBAC และธีม **AdminLTE 4** (Bootstrap 5, ไม่ใช้ jQuery) — port มาจากเวอร์ชัน CodeIgniter 3 เดิม

> **Frontend stack:** AdminLTE 4.0.2 · Bootstrap 5.3.5 · Font Awesome 6.7.2 (โหลดผ่าน CDN, ไม่มี jQuery)
> ธีมสี accent ใช้ชุดสีมาตรฐาน Bootstrap 5 (primary/secondary/success/info/warning/danger/dark); dark mode ใช้ `data-bs-theme`

> โปรเจกต์นี้เริ่มต้นเป็น **PoC** เพื่อประเมินการย้ายจาก CI3 → CI4 แล้ว port ฟีเจอร์ครบทั้ง 6 เฟส

## ฟีเจอร์

- 🔐 **Auth + RBAC** ด้วย Shield — login ด้วย **ชื่อผู้ใช้หรืออีเมล** (ช่องเดียว), throttle, remember-me
- 📊 **Dashboard** — สถิติผู้ใช้/กลุ่ม
- 👥 **จัดการผู้ใช้** — CRUD + กำหนดกลุ่มสิทธิ์ + ระงับ (ban) + avatar
- 🛡️ **บทบาท/สิทธิ์ (ไดนามิก)** — สร้าง/แก้/ลบบทบาท + ติ๊กสิทธิ์ผ่านเว็บ เก็บใน DB (Shield groups + matrix)
- ⚙️ **ตั้งค่าระบบ** — ชื่อ/โลโก้/ธีม (สี/sidebar/dark mode)/พื้นหลัง login/ข้อความ login เก็บผ่าน `codeigniter4/settings`
- 👤 **โปรไฟล์** — แก้ข้อมูลตัวเอง + เปลี่ยนรหัสผ่าน + avatar
- 📜 **Activity log** — บันทึก login/logout/CRUD ผ่าน Shield events (`/logs`)
- 🌐 **หลายภาษา (ไทย/English)** — แปลทั้งแอป (เมนู/หัวข้อ/ปุ่ม/ฟอร์ม/flash) ผ่าน `lang()`; ภาษา **จำติดตัวผู้ใช้ (per-user)** สลับที่ navbar/โปรไฟล์ + ตั้งค่าเริ่มต้นทั้งระบบที่หน้า Settings
- 🔒 **Hardening** — CSRF, security headers (CSP ฯลฯ), หน้า 403, เมนู/route ตามสิทธิ์

## ความต้องการของระบบ

- **PHP 8.1+** (ทดสอบบน 8.5) — ส่วนขยาย `intl`, `mbstring`, `mysqli`, `curl`, `json`
- **MySQL / MariaDB**
- Composer

> เครื่องนี้: PHP จาก Homebrew (`/opt/homebrew/bin/php`) + MariaDB ของ XAMPP

## ติดตั้ง

```bash
cd ~/ci4-admin-poc

# 1) dependencies
php ~/ci3-admin-portal/composer.phar install      # หรือ: composer install

# 2) ตั้งค่า .env (มีให้แล้ว) — ปรับ DB ถ้าจำเป็น
#    database.default.hostname / database / username / password

# 3) สร้างฐานข้อมูล
/Applications/XAMPP/xamppfiles/bin/mysql -uroot -e "CREATE DATABASE IF NOT EXISTS ci4_admin_poc DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4) รัน migration (Shield + Settings + ของแอป)
php spark migrate --all

# 5) สร้างผู้ใช้ admin เริ่มต้น (superadmin)
php spark db:seed AdminSeeder
```

## รัน

```bash
php spark serve --port 8081
```
เปิด <http://localhost:8081>

### บัญชีเริ่มต้น

| ล็อกอินด้วย | รหัสผ่าน | กลุ่ม |
|-------------|----------|-------|
| `admin` หรือ `admin@example.com` | `secret12345` | superadmin |

> ⚠️ เปลี่ยนรหัสผ่านทันทีหลังเข้าใช้งานครั้งแรก (หน้าโปรไฟล์)

## RBAC (กลุ่มสิทธิ์)

บทบาทคือ **Shield groups** กำหนดใน [app/Config/AuthGroups.php](app/Config/AuthGroups.php):

| กลุ่ม | สิทธิ์ |
|------|--------|
| `superadmin` | ทุกสิทธิ์ (`users.*`, `roles.*`, `settings.*`, `logs.*`) |
| `editor` | ดู/เพิ่ม/แก้ผู้ใช้, ดูบทบาท |
| `viewer` | ดูผู้ใช้, ดูบทบาท |

- กั้นการเข้าถึงด้วย filter `perm:<permission>` บน route (เช่น `perm:users.delete`)
- ในโค้ด/วิว ใช้ `auth()->user()->can('users.edit')` หรือ helper `user_can('users.edit')`

**บทบาทแก้ผ่านเว็บได้ (ไดนามิก):** หน้า **บทบาท/สิทธิ์** (`/roles`, สิทธิ์ `roles.manage`) สร้าง/แก้/ลบ
กลุ่มและติ๊กสิทธิ์ได้ — ค่าถูกเก็บใน DB ผ่าน `codeigniter4/settings` ซึ่ง Shield อ่านผ่าน
`setting('AuthGroups.groups'|'matrix')` (override ค่า default ใน `AuthGroups.php`) มีผลทันที
- **แคตตาล็อกสิทธิ์** (permission keys) ยังนิยามใน `AuthGroups.php` เพราะผูกกับ filter `perm:` บน route — UI ใช้กำหนดว่ากลุ่มไหนได้สิทธิ์ใด
- กลุ่ม `superadmin` เป็นของระบบ (แก้/ลบไม่ได้) · ลบกลุ่มที่ยังมีผู้ใช้ไม่ได้
- **กำหนดกลุ่มให้ผู้ใช้** ทำที่หน้า “จัดการผู้ใช้”

## ปรับแบรนด์/ธีม

แก้ผ่านหน้า **ตั้งค่าระบบ** (`/settings`, ต้องมีสิทธิ์ `settings.manage`) — เก็บใน DB ผ่าน
`codeigniter4/settings` (override ค่าใน [app/Config/Branding.php](app/Config/Branding.php))
ครอบคลุม: ชื่อระบบ, ไอคอน, footer, version, สี accent, sidebar dark/light, dark mode,
รูปพื้นหลัง login, ข้อความใต้ฟอร์ม login

## โครงสร้างที่เพิ่มเข้ามา

```
app/
├── Config/
│   ├── AuthGroups.php     # กลุ่ม/สิทธิ์ (RBAC)
│   ├── Auth.php           # validFields=[email,username], login view = auth/login
│   ├── Branding.php       # ค่า default แบรนด์/ธีม
│   ├── Events.php         # listener: login/logout/failedLogin → activity log
│   ├── Filters.php        # perm (403), appheaders, csrf
│   └── Routes.php
├── Controllers/
│   ├── Auth/LoginController.php   # login ด้วย username หรือ email
│   ├── Dashboard.php Users.php Roles.php Profile.php Settings.php Logs.php
├── Filters/
│   ├── PermissionFilter.php       # 'perm' — 403 page เมื่อไม่มีสิทธิ์
│   └── SecurityHeaders.php        # 'appheaders' — CSP/X-Frame-Options ฯลฯ
├── Helpers/ui_helper.php          # branding(), theme_*(), user_can(), log_activity()
├── Models/UserModel.php           # extend Shield (เพิ่ม name, avatar)
├── Database/
│   ├── Migrations/  (เพิ่ม name/avatar, activity_logs)
│   └── Seeds/AdminSeeder.php
└── Views/ layout/ auth/ dashboard users roles profile settings logs errors/forbidden
```

## การทดสอบ

```bash
php vendor/bin/phpunit
```
ใช้ฐานข้อมูลทดสอบแยก `ci4_admin_poc_test` (group `tests` ใน [Database.php](app/Config/Database.php))
รัน migration อัตโนมัติต่อเทสต์ — **18 tests** ครอบคลุม:
- guest redirect, การเข้าถึงตามสิทธิ์ (superadmin/editor/viewer), หน้า 403, permission matrix
- **จัดการบทบาทไดนามิก** ([RolesTest](tests/feature/RolesTest.php)): สร้าง/แก้/ลบบทบาท, เก็บลง DB,
  บังคับใช้สิทธิ์จาก matrix, guard (แก้ระบบ/ลบกลุ่มที่มีผู้ใช้ไม่ได้)

> ต้องสร้าง DB ทดสอบก่อนครั้งแรก:
> `mysql -uroot -e "CREATE DATABASE IF NOT EXISTS ci4_admin_poc_test"`

## ความปลอดภัย

- **CSRF** เปิดทั้งระบบ (POST ต้องมี token — ฟอร์มใช้ `csrf_field()`)
- **Security headers** ทุก response: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Content-Security-Policy`
- **403 page** สำหรับผู้ใช้ที่ล็อกอินแล้วแต่ไม่มีสิทธิ์
- รหัสผ่าน/throttle/session จัดการโดย Shield
- กันลบ/ลดสิทธิ์/ระงับ **superadmin คนสุดท้าย** และลบบัญชีตัวเองไม่ได้

## ก่อนขึ้น production

1. `CI_ENVIRONMENT = production` ใน `.env`
2. docroot ชี้ที่โฟลเดอร์ `public/` เท่านั้น
3. ตั้งค่า DB ผ่าน `.env` (อย่า commit รหัสผ่านจริง) + `app.baseURL`
4. ใช้ HTTPS · ปิดการรันสคริปต์ในโฟลเดอร์อัปโหลด `public/uploads/`
5. ปิด self-registration หากเป็น portal ภายใน (Shield: ปรับ route/Config\Auth)
6. เปลี่ยนรหัสผ่าน admin เริ่มต้น

## หมายเหตุการ port จาก CI3

- roles ของ CI3 (DB ไดนามิก) → **Shield groups** ที่ทำให้ไดนามิกผ่าน DB (settings) เหมือนเดิม; email เคย optional → **บังคับ** (Shield ใช้ email identity) แต่ login ด้วย username ได้
- auth/login/throttle/RBAC/password ที่เคยเขียนเองใน CI3 → ใช้ของ **Shield** แทน
- ผู้ใช้ที่ลบจะเป็น **soft delete** (username/email นำกลับมาใช้ทันทีไม่ได้)
