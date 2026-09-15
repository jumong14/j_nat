ระบบจัดการคลังหนังสือห้องสมุด + Barcode Scanner

วิธีติดตั้ง XAMPP
1. ติดตั้ง XAMPP และเปิด Apache + MySQL
2. นำโฟลเดอร์ library_barcode_system ไปไว้ที่:
   C:\xampp\htdocs\library_barcode_system
3. เข้า phpMyAdmin
4. สร้าง/Import ไฟล์ database.sql
5. เปิด:
   http://localhost/library_barcode_system/

บัญชีทดสอบ:
username: admin
password: admin123

หมายเหตุ:
- ระบบสแกนใช้กล้องผ่าน html5-qrcode CDN
- บนมือถือ หากเปิดจากเครื่องอื่น ต้องใช้ HTTPS หรือ localhost ที่รองรับกล้อง
- เปลี่ยนรหัสผ่าน admin ก่อนใช้งานจริง
