# 🚀 LabNest

A modern, beautiful, and secure file management system for schools, labs, and organizations.

![LabNest Banner](https://img.shields.io/badge/LabNest-File%20Manager-blueviolet?style=for-the-badge&logo=files)

---

## ✨ Features

- 📁 **Explorer**: Browse and manage all uploaded files and folders in a clean tree view.
- ⬆️ **Upload**: Drag & drop multi-file upload with category selection.
- 🕵️ **Activity Log**: Track all user actions (upload, download, delete, login, etc).
- 🔒 **Authentication**: Secure login for registered users only.
- 🌓 **Modern UI**: Glassmorphism, dark mode, and responsive design.
- 🏷️ **Categories**: Organize files by type (software, config, document, others).
- 🗑️ **Delete**: Admins can delete files securely.

---

## 🚦 Quick Start

1. **Clone this repo**
   ```bash
   git clone https://github.com/yourusername/labnest.git
   cd labnest
   ```
2. **Install dependencies** (if any, e.g. composer, npm)
3. **Setup your database**
   - Import `database.sql` to your MySQL/MariaDB server
   - Buat user admin secara manual setelah import database (gunakan `user.php` jika ingin membuat user via script)
4. **Configure your environment**
   - Copy `config.php` and fill in your DB credentials
   - Or use `.env` for better security
5. **Start the PHP server**
   ```bash
   php -S localhost:8000
   ```
6. **Access LabNest**
   - Open [http://localhost:8000](http://localhost:8000) in your browser

---

## ⚙️ Folder Structure

```
labnest/
├── uploads/           # user uploaded files (auto-created, gitignored)
├── config.php         # configuration (edit for production)
├── *.php              # main app files
├── styles.css         # main stylesheet
├── .gitignore         # ignore sensitive & generated files
└── README.md          # this file
```

---

## 🛡️ Security & Best Practices
- **Never commit real DB credentials to public repos!**
- Always use `.gitignore` to exclude `uploads/`, `.env`, and sensitive files.
- For production, set correct file/folder permissions.
- Use HTTPS in production for secure file transfer.

---

## 💡 About
LabNest is built for modern educational and organizational needs. Fast, secure, and easy to use—ready for your team!

---

## 📬 Contributing & License
Pull requests are welcome! For major changes, please open an issue first.

MIT License © 2025 SOFTWARE ENGINEERING 

## 🛠️ Prasyarat & Kebutuhan Sistem

- **PHP 7.4+** (disarankan PHP 8+)
- **MySQL/MariaDB**
- **Ekstensi PHP:** PDO, PDO_MySQL, fileinfo
- **Web server:** Apache, Nginx, atau built-in PHP server
- **Browser modern** (Chrome, Firefox, Edge, dsb)

## ⚙️ Konfigurasi Penting Sebelum Produksi

### 1. Konfigurasi Database
- Edit file `config.php` dan isi dengan kredensial database Anda:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'your_db_user');
  define('DB_PASS', 'your_db_password');
  define('DB_NAME', 'file_manager');
  ```
- **Jangan commit kredensial asli ke repo publik!**

### 2. Import Database
- Import file `database.sql` ke MySQL/MariaDB Anda.
- Setelah import, **buat user admin secara manual** sesuai kebutuhan produksi. Anda bisa menggunakan script `user.php` untuk membuat user admin baru.

### 3. Konfigurasi Upload File Besar
Secara default, PHP membatasi upload file maksimal 2MB. Untuk upload file besar:
- Edit file `php.ini` (lihat lokasi dengan `php --ini`):
  ```ini
  upload_max_filesize = 1G
  post_max_size = 1G
  max_file_uploads = 50
  ```
- Setelah mengubah, **restart web server/PHP** (misal: `sudo systemctl restart httpd` atau `php-fpm`).
- Pastikan permission folder `uploads/` bisa ditulis oleh web server.

### 4. Keamanan Produksi
- Pastikan `.gitignore` sudah mengecualikan file sensitif (`uploads/`, `.env`, `config.local.php`, dsb).
- Gunakan HTTPS di server produksi.
- Jangan gunakan user/password default di database.

## 📦 Dependensi Frontend
- [Font Awesome 6](https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css) (CDN)
- [Google Fonts: Inter](https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap) (CDN)
- [Bootstrap 5 JS Bundle](https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js) (CDN)

## 📝 Catatan Tambahan
- Untuk upload file sangat besar, pastikan koneksi stabil dan server tidak memutus request sebelum selesai.
- Semua file upload akan disimpan di folder `uploads/` dengan struktur tahun/bulan/kategori.
- Log aktivitas user tersimpan di tabel `activity_logs`. 