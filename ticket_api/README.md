# Sistem Pemesanan Tiket Ferry

Sistem pemesanan tiket ferry yang terdiri dari API backend berbasis Laravel dan aplikasi mobile berbasis Flutter untuk pengguna.

## Fitur

- Manajemen pengguna dan autentikasi (API token via Sanctum)
- Pencarian jadwal ferry
- Pemesanan tiket untuk penumpang dan kendaraan
- Pembuatan dan validasi e-tiket dengan watermark dinamis
- Integrasi pembayaran dengan Midtrans (Virtual Account, E-Wallet)
- Manajemen pemesanan dan tiket
- Rescheduling dan refund tiket
- Panel admin untuk pengelolaan sistem

## Teknologi

- **Backend**: Laravel 11
- **Frontend Mobile**: Flutter
- **Database**: MySQL
- **Payment Gateway**: Midtrans
- **Frontend Admin**: Blade + TailwindCSS

## Persyaratan Sistem

- PHP 8.1 atau lebih tinggi
- Composer
- MySQL 5.7 atau lebih tinggi
- Node.js (untuk development frontend admin)
- Flutter SDK (untuk development aplikasi mobile)

## Instalasi Backend

1. Clone repositori
   ```
   git clone https://github.com/username/ferry-ticket-system.git
   cd ferry-ticket-system
   ```

2. Install dependencies
   ```
   composer install
   npm install
   ```

3. Salin file .env.example menjadi .env dan konfigurasi database dan kredensial lainnya
   ```
   cp .env.example .env
   ```

4. Generate key aplikasi
   ```
   php artisan key:generate
   ```

5. Jalankan migrasi database dan seeder
   ```
   php artisan migrate --seed
   ```

6. Buat link storage
   ```
   php artisan storage:link
   ```

7. Jalankan server development
   ```
   php artisan serve
   ```

## Struktur Aplikasi

### Backend Laravel

```
ferry_ticket_api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── API/           # Controller untuk API mobile
│   │   │   └── Admin/         # Controller untuk panel admin
│   │   ├── Middleware/
│   │   └── Requests/          # Form request validation
│   ├── Models/                # Model database
│   ├── Services/              # Business logic
│   └── Helpers/               # Helper functions
├── config/                    # Konfigurasi aplikasi
├── database/
│   ├── migrations/            # Definisi struktur database
│   └── seeders/               # Data awal
├── resources/
│   └── views/                 # Blade templates untuk admin panel
└── routes/
    ├── api.php                # Definisi route API
    ├── web.php                # Route aplikasi web
    └── admin.php              # Route khusus admin panel
```

### Frontend Mobile Flutter

```
ferry_ticket_app/
├── lib/
│   ├── config/                # Konfigurasi aplikasi
│   ├── models/                # Model data
│   ├── screens/               # Halaman-halaman aplikasi
│   ├── widgets/               # Komponen UI reusable
│   ├── services/              # Layanan API & logika bisnis
│   ├── providers/             # State management
│   └── utils/                 # Utility functions
└── assets/                    # Resource statis
```

## Dokumentasi API

Sistem menyediakan API untuk aplikasi mobile dengan endpoint sebagai berikut:

### Autentikasi

- `POST /api/v1/register` - Registrasi pengguna baru
- `POST /api/v1/login` - Login dan mendapatkan token
- `POST /api/v1/logout` - Logout dan mencabut token

### Ferry & Rute

- `GET /api/v1/ferries` - Daftar kapal ferry
- `GET /api/v1/routes` - Daftar rute
- `GET /api/v1/routes/search` - Pencarian rute

### Jadwal

- `GET /api/v1/schedules/search` - Pencarian jadwal
- `POST /api/v1/schedules/check-availability` - Cek ketersediaan jadwal

### Pemesanan

- `POST /api/v1/bookings` - Buat pemesanan baru
- `GET /api/v1/bookings` - Daftar pemesanan pengguna
- `GET /api/v1/bookings/{bookingCode}` - Detail pemesanan
- `POST /api/v1/bookings/{bookingCode}/pay` - Proses pembayaran
- `POST /api/v1/bookings/{bookingCode}/cancel` - Batalkan pemesanan
- `POST /api/v1/bookings/{bookingCode}/reschedule` - Jadwal ulang pemesanan

### Tiket

- `GET /api/v1/tickets` - Daftar tiket pengguna
- `GET /api/v1/tickets/{ticketCode}` - Detail tiket
- `GET /api/v1/tickets/{ticketCode}/download` - Download PDF tiket
- `POST /api/v1/tickets/{ticketCode}/check-in` - Check-in tiket

## Kredensial Default

### Admin Panel

- Super Admin: admin@ferryticket.com / adminpassword
- Admin: system@ferryticket.com / systempassword
- Operator: operator@ferryticket.com / operatorpassword

## Lisensi

[MIT License](LICENSE)
