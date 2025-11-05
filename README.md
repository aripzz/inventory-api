````markdown
# 📦 Laravel Inventory API (Minimal Setup)

Proyek ini adalah REST API sistem inventaris sederhana yang dibuat menggunakan Laravel 12.

## 🚀 Persyaratan Sistem

Pastikan Anda memiliki hal-hal berikut terinstal di lingkungan lokal Anda:

* PHP (>= 8.2)
* Composer
* Database (PostgreSQL)

## 💻 Setup Awal (Lokal)

### 1. Kloning Repositori

```bash
git clone [URL_REPOSITORY_ANDA] inventory-api
cd inventory-api
````

### 2\. Instalasi Dependensi

Instal dependensi PHP menggunakan Composer:

```bash
composer install
```

### 3\. Konfigurasi Lingkungan

Salin file lingkungan dan buat App Key:

```bash
cp .env.example .env
php artisan key:generate
```

### 4\. Konfigurasi Database

Buka file `.env` dan atur detail koneksi database Anda:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_inventory # Ganti nama DB Anda
DB_USERNAME=root
DB_PASSWORD=
```

> **Catatan:** Pastikan Anda sudah membuat database kosong dengan nama yang sesuai di server database Anda (misalnya: `laravel_inventory`).

### 5\. Jalankan Migrasi Database

Jalankan semua file migrasi untuk membuat tabel `users`, `produks`, `lokasis`, `produk_lokasi`, dan `mutasis`.

```bash
php artisan migrate
```

### 6\. Instalasi Breeze API (Jika Belum Ada)

Pastikan *scaffolding* otentikasi API sudah terpasang:

```bash
composer require laravel/breeze --dev
php artisan breeze:install api
```

> Jika Anda mendapatkan error setelah menjalankan perintah di atas, jalankan `php artisan migrate:refresh` untuk memastikan semua tabel terstruktur dengan benar.

### 7\. Jalankan Server Lokal

Aplikasi API siap dijalankan menggunakan server bawaan PHP Artisan:

```bash
php artisan serve
```

Aplikasi Anda akan berjalan di: `http://127.0.0.1:8000`

-----

## 🐳 Deployment Menggunakan Docker (Minimal)

Bagian ini menjelaskan cara *build* dan menjalankan aplikasi hanya dalam satu *container* PHP-FPM/CLI berdasarkan `Dockerfile` yang telah Anda buat, tanpa menggunakan Nginx dan Docker Compose.

> **Catatan:** Metode ini lebih cocok untuk menjalankan tugas CLI/Artisan atau sebagai *backend* PHP-FPM di lingkungan yang sudah memiliki Nginx/API Gateway terpisah. Anda harus memastikan layanan database Anda sudah berjalan di luar *container* ini, atau di *container* terpisah yang terhubung.

### 1\. Build Image Docker

Gunakan `Dockerfile` yang sudah ada di *root* proyek Anda untuk membangun *image*:

```bash
docker build -t inventory-api-fpm:latest .
```

### 2\. Jalankan Container dan Lakukan Migrasi

Kita akan menjalankan *container* dalam mode CLI (`--rm` untuk menghapus *container* setelah selesai, `-it` untuk interaktif) untuk menjalankan perintah Artisan.

#### a. Generate App Key (Satu Kali)

```bash
docker run --rm -it -v $(pwd):/var/www/html inventory-api-fpm:latest php artisan key:generate
```

#### b. Jalankan Migrasi

Gunakan *image* untuk menjalankan migrasi dan *seeder* (jika ada). Pastikan *container* dapat mengakses *host* database Anda.

```bash
# Ganti --network dengan nama network Docker Anda jika diperlukan
docker run --rm -it \
    --env-file .env \
    -v $(pwd):/var/www/html \
    inventory-api-fpm:latest php artisan migrate --force
```

### 3\. Menjalankan PHP-FPM

Jika Anda perlu menjalankan *container* dalam mode PHP-FPM (Port 9000), gunakan perintah ini.

```bash
docker run -d \
    --name inventory-fpm-standalone \
    -p 8000:8000 \
    --env-file .env \
    -v $(pwd):/var/www/html \
    inventory-api-fpm:latest php-fpm
```

> **PENTING:** Anda memerlukan *container* Nginx/Web Server lain yang berjalan di *host* Anda untuk mengarahkan lalu lintas HTTP/80 ke *container* ini di Port 9000 untuk mengakses API.

-----

## 🔑 Penggunaan API

Setelah server berjalan di `http://127.0.0.1:8000`, semua *endpoint* API tersedia di bawah *prefix* `/api/`.

### Otentikasi

1.  **Register:** `POST /api/register`
2.  **Login:** `POST /api/login` (Untuk mendapatkan **Bearer Token**)

Semua *endpoint* CRUD dan History memerlukan *header*: `Authorization: Bearer <TOKEN_YANG_DIDAPAT>`

### Endpoint Utama

  * **Produk:** `GET/POST/PUT/DELETE /api/produks`
  * **Lokasi:** `GET/POST/PUT/DELETE /api/lokasis`
  * **Mutasi:** `GET/POST/PUT/DELETE /api/mutasis`
  * **History:** `GET /api/produks/{id}/history-mutasi`

<!-- end list -->

```
```
