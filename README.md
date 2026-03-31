<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Tentang Search Job App

**Search Job App** adalah aplikasi web modern untuk mencari dan melamar pekerjaan yang dibangun dengan teknologi terkini. Aplikasi ini memungkinkan pencari kerja untuk menemukan peluang karir yang sesuai dengan keterampilan mereka, sambil memberikan pengusaha alat untuk mengelola lowongan pekerjaan dan aplikasi secara efisien.

## Fitur Utama

- **Job Search & Filter** - Cari lowongan pekerjaan dengan filter lanjutan berdasarkan kategori, lokasi, dan kriteria lainnya
- **Job Applications** - Ajukan aplikasi dengan mudah dan lacak status aplikasi Anda secara real-time
- **Admin Dashboard** - Dashboard komprehensif untuk mengelola lowongan, aplikasi, dan pengguna
- **Notifikasi Real-time** - Notifikasi instan melalui WebSocket menggunakan Laravel Reverb untuk update status aplikasi
- **Email Notifications** - Sistem email otomatis untuk penerimaan aplikasi dan perubahan status
- **Analytics & Statistics** - Visualisasi data dengan grafik dan counter untuk performa aplikasi dan lowongan
- **CV Preview Modal** - Pratinjau CV pelamar dalam modal dialog
- **Excel Export** - Export aplikasi dan data lowongan ke format Excel
- **Dark/Light Mode** - Toggle tema gelap dan terang untuk kenyamanan pengguna
- **Authentication System** - Sistem autentikasi aman dengan role-based access control
- **Responsive Design** - Desain responsif yang sempurna di semua perangkat

## Tech Stack

- **Backend**: Laravel 11 dengan PHP
- **Frontend**: Inertia.js & Vue.js 3
- **Database**: MySQL/PostgreSQL
- **Styling**: Tailwind CSS
- **Real-time**: Laravel Reverb (WebSocket Broadcasting)
- **Export**: Laravel Excel (Maatwebsite)
- **Build Tool**: Vite
- **Email**: Laravel Mail System
- **Queue**: Laravel Queue untuk background jobs

## Instalasi

### Prerequisites
- PHP 8.2 atau lebih tinggi
- Composer
- Node.js & npm
- Database (MySQL/PostgreSQL)

### Setup

1. **Clone repository**
```bash
git clone <repository-url>
cd search-job-app
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install JavaScript dependencies**
```bash
npm install
```

4. **Setup environment**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure database** di file `.env` kemudian jalankan:
```bash
php artisan migrate
php artisan db:seed
```

6. **Setup Reverb untuk WebSocket** (optional untuk real-time notifications)
```bash
php artisan reverb:install
```

7. **Start development server**
```bash
# Terminal 1: Laravel development server
php artisan serve

# Terminal 2: Vite development server
npm run dev

# Terminal 3: WebSocket server (jika menggunakan Reverb)
php artisan reverb:start
```

## Struktur Project

```
├── app/
│   ├── Actions/          # Business logic actions
│   ├── Http/             # Controllers, Requests, Middleware
│   ├── Models/           # Eloquent Models (User, Job, Application)
│   ├── Events/           # Application events
│   ├── Listeners/        # Event listeners
│   ├── Mail/             # Mailable classes
│   ├── Notifications/    # Notification classes
│   └── Exports/          # Laravel Excel exports
├── resources/
│   ├── js/               # Vue components
│   ├── css/              # Tailwind styles
│   └── views/            # Blade templates
├── routes/               # API dan web routes
├── database/
│   ├── migrations/       # Database migrations
│   ├── factories/        # Model factories
│   └── seeders/          # Database seeders
└── config/               # Configuration files
```

## Pengembangan

### Running Tests
```bash
php artisan test
```

### Code Quality
```bash
./vendor/bin/phpunit
```

### Build for Production
```bash
npm run build
php artisan optimize
```

## Kontribusi

Kami menerima kontribusi! Silakan buat pull request dengan deskripsi perubahan yang jelas. Pastikan code Anda mengikuti style guide project dan semua test lulus.

## Lisensi

Project ini dilisensikan di bawah [MIT license](https://opensource.org/licenses/MIT).
