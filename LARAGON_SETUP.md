# Laragon Setup Guide - UKK Project

## ✅ Configuration Status

Aplikasi Laravel UKK sudah dikonfigurasi untuk berjalan dengan MySQL di Laragon.

## Database Configuration

- **Database Type**: MySQL
- **Database Name**: `ukk_db`
- **Host**: 127.0.0.1
- **Port**: 3306
- **Username**: root
- **Password**: (kosong)

## Session Configuration

- **Driver**: database
- **Connection**: mysql
- **Table**: sessions

Sessions disimpan di tabel `sessions` dalam database MySQL.

## Access URLs

- **Aplikasi Laravel**: http://127.0.0.1/UKK/public/
- **Login Page**: http://127.0.0.1/UKK/public/login
- **phpMyAdmin**: http://localhost/phpmyadmin/

## File Konfigurasi Penting

- `.env` - Environment variables (DB_CONNECTION=mysql)
- `config/database.php` - Default connection diubah ke 'mysql'
- `config/session.php` - Session menggunakan database MySQL

## Perubahan yang Telah Dilakukan

1. ✅ Mengubah default database connection dari SQLite ke MySQL di `config/database.php`
2. ✅ Mengatur `.env` untuk menggunakan MySQL:
   - `DB_CONNECTION=mysql`
   - `DB_DATABASE=ukk_db`
   - `SESSION_CONNECTION=mysql`
3. ✅ Menjalankan migrasi database
4. ✅ Membersihkan cache Laravel
5. ✅ Menginstal phpMyAdmin

## Cara Menjalankan Aplikasi

1. Pastikan Laragon sudah berjalan (Apache & MySQL)
2. Buka browser dan akses: http://127.0.0.1/UKK/public/
3. Untuk manajemen database, akses: http://localhost/phpmyadmin/

## Troubleshooting

Jika ada masalah:

1. **Clear Cache**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan config:cache
   ```

2. **Restart Apache**:
   - Restart dari Laragon UI
   - Atau jalankan: `httpd -k restart`

3. **Check Database Connection**:
   ```bash
   php artisan migrate:status
   ```

## Notes

- Session cookies domain: 127.0.0.1
- APP_URL: http://127.0.0.1
- Debug mode: enabled (APP_DEBUG=true)
- Environment: local

---
Last Updated: November 13, 2025
