# Admin Portal (CodeIgniter 4 + Shield)

ระบบหลังบ้าน (admin portal) บน **CodeIgniter 4** ใช้ **CodeIgniter Shield** สำหรับ
authentication/RBAC และธีม **AdminLTE 4** (Bootstrap 5, ไม่ใช้ jQuery) — port มาจากเวอร์ชัน CodeIgniter 3 เดิม
แล้วต่อยอดเป็นพอร์ทัลเชื่อมต่อ **SAP Business One** (sync ข้อมูลหลัก + สร้างเอกสารโอนย้ายสินค้า)

> **Frontend stack:** AdminLTE 4.0.2 · Bootstrap 5.3.5 · Font Awesome 6.7.2 · flatpickr 4.6 (โหลดผ่าน CDN, ไม่มี jQuery)
> ธีมสี accent ใช้ชุดสีมาตรฐาน Bootstrap 5 (primary/secondary/success/info/warning/danger/dark); dark mode ใช้ `data-bs-theme`

## ฟีเจอร์

### พื้นฐาน (port จาก CI3)
- 🔐 **Auth + RBAC** ด้วย Shield — login ด้วย **ชื่อผู้ใช้หรืออีเมล** (ช่องเดียว), throttle, remember-me
- 📊 **Dashboard** — สถิติผู้ใช้/กลุ่ม (การ์ดสูงเท่ากันด้วย flexbox)
- 👥 **จัดการผู้ใช้** — CRUD + กลุ่มสิทธิ์ + ระงับ (ban) + **ผูกคลังสินค้า** + avatar
- 🛡️ **บทบาท/สิทธิ์ (ไดนามิก)** — สร้าง/แก้/ลบบทบาท + ติ๊กสิทธิ์ผ่านเว็บ เก็บใน DB (Shield groups + matrix)
- ⚙️ **ตั้งค่าระบบ** — แบรนด์/ธีม/พื้นหลัง login + **URL & API Key ของ Web API** + **endpoint ย่อย**
- 👤 **โปรไฟล์** — แก้ข้อมูลตัวเอง + เปลี่ยนรหัสผ่าน + **เลือก avatar เป็นไอคอนสี** (5 แบบ)
- 📜 **Activity log** — บันทึก login/logout/CRUD/sync ผ่าน Shield events (`/logs`) — แบ่งหน้า 20/หน้า, แสดงเวลาแบบ **Asia/Bangkok**
- 🌐 **หลายภาษา (ไทย/English)** — แปลทั้งแอป; ภาษา **จำติดตัวผู้ใช้ (per-user)** สลับที่ navbar/โปรไฟล์
- 🔒 **Hardening** — CSRF, security headers (CSP ฯลฯ), หน้า 403, เมนู/route ตามสิทธิ์

### โมดูล SAP (เพิ่มใหม่)
- 📦 **Master data sync จาก SAP** — ปุ่ม **Sync Data From SAP** ดึงข้อมูลผ่าน Web API แล้ว upsert:
  - **Item Master** (`/items`) — Itemcode / Itemname / Default Warehouse / **หน่วยนับหลายหน่วย (UoM)**
  - **Warehouses** (`/warehouses`) — Warehouse Code / Warehouse Name
  - **Business Partner** (`/business-partners`) — BP Code / BP Name / Ship To
- 🔁 **Inventory Transfer Request** (`/transfer-requests`) — เอกสารคำขอโอนย้ายสินค้าแบบ SAP:
  header (คู่ค้า/วันที่/คลังต้นทาง→ปลายทาง) + รายการสินค้าหลายบรรทัด, เลขที่เอกสารรันอัตโนมัติแยกตามเดือน

## ความต้องการของระบบ

- **PHP 8.1+** (ทดสอบบน 8.5) — ส่วนขยาย `intl`, `mbstring`, `mysqli`, `curl`, `json`
- **MySQL / MariaDB**
- Composer

## ติดตั้ง

```bash
cd ~/ci4-admin-poc

# 1) dependencies
composer install

# 2) ตั้งค่า .env — ปรับ DB ถ้าจำเป็น (ไฟล์นี้ไม่ถูก commit; ดู env เป็นตัวอย่าง)
#    database.default.hostname / database / username / password

# 3) สร้างฐานข้อมูล
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS ci4_admin_poc DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

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
- **บทบาทแก้ผ่านเว็บได้ (ไดนามิก):** หน้า `/roles` (สิทธิ์ `roles.manage`) — เก็บใน DB ผ่าน `codeigniter4/settings`, override default ใน `AuthGroups.php`, มีผลทันที

**สิทธิ์ของแต่ละโมดูล:**

| โมดูล | สิทธิ์ที่ใช้ |
|-------|------------|
| Warehouses / Item Master / Business Partner / API endpoints | `settings.manage` |
| Inventory Transfer Request | `admin.access` (ทุกคนที่เข้าหลังบ้านได้) |

## โมดูล SAP

### ตั้งค่า Web API (หน้า Settings)
เก็บผ่าน `codeigniter4/settings`:
- **Web API URL** — base URL ของ SAP gateway (`Branding.apiUrl`)
- **API Key** — ส่งเป็น header `X-API-Key` ตอน sync (`Branding.apiKey`)
- **Endpoint ย่อย** (ตาราง `api_endpoints`) — กำหนด path ต่อการทำงาน เช่น `ItemMaster → /item`,
  `Warehouses → /warehouses`, `BusinessPartner → /business-partners` (เพิ่ม/ลบได้, กันชื่อซ้ำ)

### Sync จาก SAP
แต่ละหน้า master data มีปุ่ม **Sync Data From SAP** — เรียก `GET {apiUrl} + {endpoint path}`
แนบ `X-API-Key` แล้ว upsert (มีอยู่→update, ไม่มี→insert) คาดหวัง response เป็น JSON array of objects
(รองรับชื่อ key หลายแบบ รวม SAP B1 style เช่น `CardCode`/`CardName`)

| โมดูล | endpoint name | คอลัมน์ | key ธรรมชาติ |
|-------|---------------|---------|--------------|
| Warehouses | `Warehouses` | code, name | code |
| Item Master | `ItemMaster` | item_code, item_name, default_warehouse, **uoms[]** | item_code |
| Business Partner | `BusinessPartner` | bp_code, bp_name, ship_to | bp_code |

> Item Master รองรับ `DefaultWhs` และ `Uoms[]` (หลายหน่วยนับต่อสินค้า) — เก็บหน่วยฐานที่ `items.inventory_uom` และหน่วยอื่นในตาราง `item_uoms`
> ถ้ายังไม่ตั้ง URL หรือ endpoint ปุ่ม Sync จะแจ้ง error ชัดเจน

### Inventory Transfer Request (`/transfer-requests`)
เอกสารคำขอโอนย้ายสินค้าแนว SAP — **header + line items**:
- **Header:** เลขที่เอกสาร (auto), Business Partner/Name/Contact/Ship To, Posting/Due/Document Date,
  From/To Warehouse, Price List, Remarks
- **Line items:** เลือกสินค้า (จาก Item Master) / คลังต้นทาง→ปลายทาง / จำนวน / UoM — เพิ่มได้หลายบรรทัด
  (บรรทัดแรกลบไม่ได้)
- **เลขที่เอกสาร** รันแยกตามเดือน: `ITR` + `yymm` + ลำดับ 4 หลัก
  เช่น `ITR26060001` — preview ตอนเปลี่ยน Posting Date, รันจริงจาก DB ตอนบันทึก
- **คอลัมน์ SAP Document** เตรียมไว้เก็บเลขเอกสารที่ตอบกลับจาก SAP (สำหรับ integration ขาส่ง)
- **สิทธิ์เห็นข้อมูล:** admin (superadmin) เห็นทุกใบ + คอลัมน์ *Created By*; ผู้ใช้อื่นเห็นเฉพาะของตัวเอง
- รายการเกิน 20 ใบ → แบ่งหน้า

## ปรับแบรนด์/ธีม

แก้ผ่านหน้า **ตั้งค่าระบบ** (`/settings`, สิทธิ์ `settings.manage`) — เก็บใน DB ผ่าน `codeigniter4/settings`
(override ค่าใน [app/Config/Branding.php](app/Config/Branding.php)): ชื่อระบบ, ไอคอน, footer, version,
สี accent, **สีพื้นหลัง sidebar**, sidebar dark/light, dark mode, รูปพื้นหลัง login, ข้อความ login, Web API URL/Key

## โครงสร้างที่เพิ่มเข้ามา

```
app/
├── Config/
│   ├── AuthGroups.php     # กลุ่ม/สิทธิ์ (RBAC)
│   ├── Branding.php       # ค่า default แบรนด์/ธีม + Web API URL/Key
│   ├── Pager.php          # + template 'bootstrap5'
│   ├── Events.php Filters.php Routes.php Auth.php
├── Controllers/
│   ├── Auth/LoginController.php
│   ├── Dashboard.php Users.php Roles.php Profile.php Settings.php Logs.php Locale.php
│   ├── Warehouses.php Items.php BusinessPartners.php   # master data + sync
│   ├── ApiEndpoints.php                                # endpoint ย่อย
│   └── TransferRequests.php                            # คำขอโอนย้าย
├── Filters/ PermissionFilter.php SecurityHeaders.php
├── Helpers/ui_helper.php   # branding(), theme_*/sidebar_*(), avatar_icon/color(),
│                           # local_datetime(), user_can(), log_activity()
├── Models/
│   ├── UserModel.php (name/avatar/locale) ActivityLogModel.php UserWarehouseModel.php
│   ├── WarehouseModel.php ItemModel.php ItemUomModel.php BusinessPartnerModel.php
│   ├── ApiEndpointModel.php
│   └── TransferRequestModel.php TransferRequestItemModel.php
├── Database/Migrations/    # name/avatar/locale, activity_logs, warehouses,
│                           # items (+item_uoms), business_partners, api_endpoints,
│                           # user_warehouses, transfer_requests (+items)
└── Views/  layout/ auth/ dashboard users roles profile settings logs
            warehouses/ items/ business_partners/ transfer_requests/ pager/ errors/
```

## การทดสอบ

```bash
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS ci4_admin_poc_test"   # ครั้งแรก
php vendor/bin/phpunit
```
ใช้ฐานข้อมูลทดสอบแยก `ci4_admin_poc_test` (group `tests` ใน [Database.php](app/Config/Database.php)),
รัน migration อัตโนมัติต่อเทสต์ — **61 tests** ครอบคลุม:

| ชุดเทส | ครอบคลุม |
|--------|----------|
| AccessControl / Roles | guest redirect, สิทธิ์ตามกลุ่ม, 403, permission matrix, จัดการบทบาทไดนามิก |
| Warehouses / Items / BusinessPartners | สิทธิ์เข้าถึง, แสดงข้อมูล (รวม UoM badge), guard ของ sync (ไม่มี URL/endpoint), cascade ลบ UoM |
| ApiEndpoints | สร้าง/ลบ, กันชื่อซ้ำ |
| Settings | บันทึกธีม/API URL/Key, ปฏิเสธ URL ผิด, sidebar helpers |
| Users | ผูก warehouse (กรอง server-side, ตัด id ที่ไม่มีจริง) |
| TransferRequests | สร้างเอกสาร+line, กันไม่มี line, เลขรันแยกเดือน, guard คลัง/สินค้าที่ไม่มีจริง, สิทธิ์เห็นเฉพาะของตัวเอง, AJAX preview |
| UiHelper / TransferRequestModel (unit) | `local_datetime()` แปลง timezone, `nextDocNo()` รันแยกเดือน |
| Profile | เลือก avatar ไอคอน, ปฏิเสธไอคอนปลอม, ค่าเริ่มต้น |

## ความปลอดภัย

- **CSRF** เปิดทั้งระบบ (POST ต้องมี token — ฟอร์มใช้ `csrf_field()`)
- **Security headers** ทุก response: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Content-Security-Policy`
- **403 page** สำหรับผู้ใช้ที่ล็อกอินแล้วแต่ไม่มีสิทธิ์
- รหัสผ่าน/throttle/session จัดการโดย Shield
- กันลบ/ลดสิทธิ์/ระงับ **superadmin คนสุดท้าย** และลบบัญชีตัวเองไม่ได้
- Transfer Request: ผู้ที่ไม่ใช่ admin เข้าดู/ลบเอกสารของผู้อื่นไม่ได้ (404), คลัง/สินค้าในเอกสารตรวจ server-side ว่ามีจริง

## ก่อนขึ้น production

1. `CI_ENVIRONMENT = production` ใน `.env`
2. docroot ชี้ที่โฟลเดอร์ `public/` เท่านั้น
3. ตั้งค่า DB ผ่าน `.env` (อย่า commit รหัสผ่านจริง) + `app.baseURL`
4. ใช้ HTTPS
5. ปิด self-registration หากเป็น portal ภายใน (Shield: ปรับ route/`Config\Auth`)
6. เปลี่ยนรหัสผ่าน admin เริ่มต้น + ตั้ง Web API URL/Key ของ SAP จริง

## หมายเหตุการ port จาก CI3

- roles ของ CI3 (DB ไดนามิก) → **Shield groups** ที่ทำให้ไดนามิกผ่าน DB (settings); email เคย optional → **บังคับ** แต่ login ด้วย username ได้
- auth/login/throttle/RBAC/password ที่เคยเขียนเองใน CI3 → ใช้ของ **Shield** แทน
- ผู้ใช้ที่ลบจะเป็น **soft delete** (username/email นำกลับมาใช้ทันทีไม่ได้)
