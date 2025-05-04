# Skripsi Salwa API Documentation

Backend API untuk aplikasi manajemen produk dan kegiatan warga.

## Requirement

-   PHP 8.1 atau lebih tinggi
-   PostgreSQL 13 atau lebih tinggi
-   Composer
-   Laravel 10.x

## Instalasi

1. Clone repository ini

```bash
git clone <repository-url>
cd skripsi-salwa
```

2. Install dependencies

```bash
composer install
```

3. Copy file .env.example ke .env

```bash
cp .env.example .env
```

4. Generate application key

```bash
php artisan key:generate
```

5. Konfigurasi database di file .env

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=skripsi_salwa
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

6. Jalankan migrasi database

```bash
php artisan migrate
```

7. Buat folder untuk menyimpan foto

```bash
mkdir -p public/photos
chmod 775 public/photos
```

## Menjalankan Aplikasi

```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

## API Endpoints

### Products API

#### Get All Products

```http
GET /api/v1/products
```

#### Create Product

```http
POST /api/v1/products

{
    "nama": "Nama Produk",
    "harga": 100000,
    "nama_toko": "Nama Toko",
    "foto": "data:image/jpeg;base64,/9j/4AAQSkZJRgAB...",
    "kategori": "Kategori",
    "description": "Deskripsi produk"
}
```

#### Get Product Detail

```http
GET /api/v1/products/{id}
```

#### Update Product

```http
POST /api/v1/products/{id}

{
    "nama": "Nama Produk Update",
    "harga": 150000,
    "nama_toko": "Nama Toko Update",
    "foto": "data:image/jpeg;base64,/9j/4AAQSkZJRgAB...",
    "kategori": "Kategori Update",
    "description": "Deskripsi produk update"
}
```

#### Delete Product

```http
DELETE /api/v1/products/{id}
```

### Activities API

#### Get All Activities

```http
GET /api/v1/activities
```

#### Create Activity

```http
POST /api/v1/activities

{
    "nama_kegiatan": "Nama Kegiatan",
    "start_date": "2024-05-04 10:00:00",
    "end_date": "2024-05-04 12:00:00",
    "deskripsi": "Deskripsi kegiatan",
    "tempat": "Lokasi kegiatan",
    "foto": "data:image/jpeg;base64,/9j/4AAQSkZJRgAB..."
}
```

#### Get Activity Detail

```http
GET /api/v1/activities/{id}
```

#### Update Activity

```http
POST /api/v1/activities/{id}

{
    "nama_kegiatan": "Nama Kegiatan Update",
    "start_date": "2024-05-04 11:00:00",
    "end_date": "2024-05-04 13:00:00",
    "deskripsi": "Deskripsi kegiatan update",
    "tempat": "Lokasi kegiatan update",
    "foto": "data:image/jpeg;base64,/9j/4AAQSkZJRgAB..."
}
```

#### Delete Activity

```http
DELETE /api/v1/activities/{id}
```

## Format Response

### Success Response

```json
{
    "status": "berhasil",
    "messages": "Pesan sukses",
    "data": null|object|array
}
```

### Error Response

```json
{
    "status": "gagal",
    "messages": "Pesan error",
    "error": "Detail error"
}
```

## Catatan

-   Foto dikirim dalam format base64 string
-   Foto akan disimpan di folder `public/photos`
-   Nama file foto untuk produk: `nama_toko_tanggal_nama_produk.extension`
-   Nama file foto untuk kegiatan: `nama_kegiatan.extension`

## License

[MIT License](LICENSE)
# skripsi_salwa
