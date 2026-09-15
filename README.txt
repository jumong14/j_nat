📚 ระบบจัดการคลังหนังสือห้องสมุด + Barcode Scanner
ระบบเว็บสำหรับจัดการหนังสือ สมาชิก การยืม-คืน และประวัติการยืม-คืน
พร้อมระบบสแกน Barcode ผ่านกล้องหรือไฟล์รูปภาพ
✨ ฟังก์ชันหลัก
- 🔐 Login สำหรับผู้ดูแลระบบ
- 📊 Dashboard แสดงจำนวนหนังสือ สมาชิก หนังสือว่าง และหนังสือที่กำลังยืม
- 📚 เพิ่ม / แก้ไข / ลบ / ค้นหาหนังสือ
- 👥 เพิ่ม / แก้ไข / ลบ / ค้นหาสมาชิก
- 📖 ยืมหนังสือ
- 🔄 คืนหนังสือ
- 📷 สแกน Barcode ด้วยกล้อง
- 🖼️ สแกน Barcode จากรูปภาพ
- 🕘 ดูประวัติการยืม-คืน
- 🔎 ค้นหาและกรองประวัติการยืม-คืน
🧰 Tech Stack
เทคโนโลยี	ใช้ทำอะไร
PHP 8+	Backend และประมวลผลหน้าเว็บ
HTML5	โครงสร้างหน้าเว็บ
CSS3	การออกแบบและจัดรูปแบบหน้าเว็บ
JavaScript	การทำงานฝั่ง Browser และระบบสแกน Barcode
html5-qrcode	Library สำหรับอ่าน Barcode / QR Code จากกล้องและรูปภาพ
Supabase	Database และ REST API
Supabase REST API	เชื่อม PHP กับตารางข้อมูล
PHP cURL	ส่ง HTTP Request ไปยัง Supabase REST API
PHP Session	เก็บสถานะ Login
Apache (XAMPP)	Web Server สำหรับรัน PHP บนเครื่อง
Git	Version Control
GitHub	เก็บ Source Code และเผยแพร่โปรเจกต์


โปรเจกต์นี้ ไม่ได้ใช้ Framework เช่น Laravel หรือ Bootstrap
เป็น PHP แบบ Native + HTML/CSS/JavaScript

🗄️ Database
ระบบปัจจุบันใช้ Supabase เป็นฐานข้อมูลผ่าน REST API
ตารางหลัก:
users
เก็บบัญชีผู้ดูแลระบบ
- id
- username
- password
members
เก็บข้อมูลสมาชิกห้องสมุด
- id
- member_code
- name
- phone
- created_at
books
เก็บข้อมูลหนังสือ
- id
- barcode
- title
- author
- category
- quantity
- available
- created_at
borrowings
เก็บข้อมูลการยืม-คืน
- id
- member_id
- book_id
- borrow_date
- due_date
- return_date
- status
ความสัมพันธ์:
members
   │
   └──────< borrowings >──────┐
                              │
                           books
📁 โครงสร้างโปรเจกต์
library_barcode_system/
│
├── assets/
│   ├── app.js
│   └── style.css
│
├── config/
│   ├── auth.php
│   └── database.php
│
├── partials/
│   ├── header.php
│   └── footer.php
│
├── index.php
├── login.php
├── logout.php
├── dashboard.php
├── books.php
├── members.php
├── borrow.php
├── scanner.php
├── barcode.php
├── history.php
│
├── database.sql
└── README.md
💻 วิธีติดตั้งบน Windows
1. ติดตั้ง XAMPP
ติดตั้ง XAMPP แล้วเปิด:
Apache
⚠️ ไม่จำเป็นต้องเปิด MySQL
เนื่องจากโปรเจกต์ปัจจุบันใช้ Supabase เป็น Database
2. วางโปรเจกต์
นำโฟลเดอร์:
library_barcode_system
ไปไว้ที่:
C:\xampp\htdocs\
จะได้:
C:\xampp\htdocs\library_barcode_system
3. ตรวจสอบ PHP
เปิด CMD แล้วใช้:
php -v
ถ้า CMD หา PHP ไม่เจอ ให้ใช้ PHP ที่ติดมากับ XAMPP เช่น:
C:\xampp\php\php.exe -v
☁️ 4. ตั้งค่า Supabase
สร้าง Project บน Supabase แล้วสร้างตาราง:
users
members
books
borrowings
ระบบ PHP จะเรียก Supabase ผ่าน REST API ที่ไฟล์:
config/database.php
ภายในไฟล์จะมี:
$SUPABASE_URL = "...";
$SUPABASE_KEY = "...";
ให้ใส่ URL และ Publishable Key ของ Supabase ของโปรเจกต์ที่ต้องการใช้งาน
ห้ามนำ service_role key ไปใส่ในโปรเจกต์หรือ GitHub
ควรใช้ Publishable/anon key และตั้งค่า RLS ให้เหมาะสม

🔑 บัญชีทดสอบ
Username: admin
Password: admin123
ควรเปลี่ยนรหัสผ่านก่อนใช้งานจริง
🌐 5. เปิดเว็บไซต์
เปิด Browser:
http://localhost/library_barcode_system/
หน้า Login:
http://localhost/library_barcode_system/login.php
📷 ระบบ Barcode Scanner
ระบบใช้:
html5-qrcode
โหลดผ่าน CDN:
https://unpkg.com/html5-qrcode
รองรับ:
- สแกน Barcode ด้วยกล้อง
- เลือกรูป Barcode จากเครื่อง
- ส่ง Barcode ไปยังระบบยืม/คืนหนังสือ
การใช้กล้อง
เมื่อเปิด Scanner ระบบจะขอสิทธิ์ใช้กล้อง
บนมือถือ หากเข้าผ่านเครื่องอื่น ควรใช้:
HTTPS
หรือสภาพแวดล้อมที่ Browser อนุญาตให้ใช้กล้อง
🔄 Flow การทำงาน
เริ่ม
  │
  ▼
Login
  │
  ▼
Dashboard
  │
  ├──► จัดการหนังสือ
  │      ├── เพิ่ม
  │      ├── แก้ไข
  │      ├── ลบ
  │      └── ค้นหา
  │
  ├──► จัดการสมาชิก
  │      ├── เพิ่ม
  │      ├── แก้ไข
  │      ├── ลบ
  │      └── ค้นหา
  │
  ├──► ยืมหนังสือ
  │      │
  │      └── สแกน Barcode
  │
  ├──► คืนหนังสือ
  │      │
  │      └── สแกน Barcode
  │
  └──► ประวัติการยืม-คืน
🔌 การเชื่อมต่อระบบ
โครงสร้างการทำงาน:
Browser
   │
   ▼
Apache / XAMPP
   │
   ▼
PHP
   │
   ├── Session / Login
   │
   └── PHP cURL
          │
          ▼
     Supabase REST API
          │
          ▼
       Database
📌 ไฟล์สำคัญ
config/database.php
ใช้เชื่อมต่อ Supabase REST API
config/auth.php
ตรวจสอบว่าผู้ใช้ Login แล้วหรือไม่
login.php
หน้า Login และตรวจสอบ username/password
dashboard.php
หน้า Dashboard
books.php
จัดการข้อมูลหนังสือ CRUD
members.php
จัดการข้อมูลสมาชิก CRUD
borrow.php
ระบบยืมและคืนหนังสือ
scanner.php
ระบบ Barcode Scanner
history.php
ประวัติการยืม-คืน
🛠️ CRUD ที่รองรับ
หนังสือ
CREATE  → เพิ่มหนังสือ
READ    → แสดง/ค้นหาหนังสือ
UPDATE  → แก้ไขหนังสือ
DELETE  → ลบหนังสือ
สมาชิก
CREATE  → เพิ่มสมาชิก
READ    → แสดง/ค้นหาสมาชิก
UPDATE  → แก้ไขสมาชิก
DELETE  → ลบสมาชิก
การยืม-คืน
CREATE  → สร้างรายการยืม
UPDATE  → เปลี่ยนสถานะเป็นคืนแล้ว
READ    → ดูรายการและประวัติ
📦 GitHub
โปรเจกต์สามารถเก็บบน GitHub ได้โดยใช้:
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/USERNAME/REPOSITORY.git
git push -u origin main
หาก Repository มีข้อมูลอยู่แล้ว:
git pull --rebase origin main
git push -u origin main
⚠️ หมายเหตุเกี่ยวกับ database.sql
ไฟล์ database.sql ที่มากับชุดโปรเจกต์เดิมเป็น SQL สำหรับ MySQL แต่ตัว PHP ปัจจุบันเชื่อมต่อ Supabase REST API
ดังนั้น:
XAMPP → Apache → PHP → Supabase
คือโครงสร้างที่ระบบปัจจุบันใช้งานจริง
ไม่จำเป็นต้อง Import database.sql เข้า phpMyAdmin สำหรับเวอร์ชันที่ใช้ Supabase
หากต้องการย้ายระบบกลับไปใช้ MySQL ต้องแก้ config/database.php และส่วนที่เรียก Supabase ใหม่
👨‍💻 Project
ชื่อโปรเจกต์: ระบบจัดการคลังหนังสือห้องสมุดพร้อมระบบสแกนบาร์โค้ดผ่านเว็บ
ประเภท: Web Application
Backend: Native PHP
Database: Supabase
Web Server: Apache / XAMPP
Frontend: HTML5 + CSS3 + JavaScript
Barcode: html5-qrcode
Version Control: Git + GitHub
