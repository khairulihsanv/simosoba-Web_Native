# Dokumentasi Teknis Manajemen Proyek `simosoba2`

Dokumentasi ini menjelaskan standar struktur file, konfigurasi serverless menggunakan Vercel, serta aturan pengembangan (development guidelines) untuk memastikan pemisahan kode yang bersih antara logika backend dan antarmuka frontend.

## 1. Deskripsi Struktur Folder Utama

Proyek `simosoba2` menggunakan arsitektur pemisahan tanggung jawab (Separation of Concerns). Berikut adalah fungsi dari masing-masing folder utama:

*   **`/config`**: Berisi file pengaturan sistem, seperti koneksi database, variabel lingkungan (environment variables), atau konfigurasi global lainnya.
*   **`/classes`**: Merupakan *core* (inti) dari logika backend (Business Logic). Di sinilah tempat mendefinisikan kelas-kelas OOP (Object-Oriented Programming) untuk berinteraksi dengan database (Model), utilitas, atau *helper* khusus.
*   **`/api`**: Berisi endpoint-endpoint khusus untuk menangani permintaan HTTP berbasis data (misalnya: GET/POST) yang mengembalikan respons dalam format JSON. Folder ini ideal digunakan untuk komunikasi dengan aplikasi mobile, frontend berbasis JavaScript (Ajax/Fetch), atau layanan eksternal.
*   **`/actions`**: Bertugas sebagai penerima aksi dari aplikasi internal (contoh: *form submit*). File di dalam folder ini memproses input dari user, memanggil fungsi dari `/classes`, lalu melakukan *redirect* kembali ke halaman yang sesuai.
*   **`/views`**: Folder khusus untuk menyimpan *template* halaman antarmuka pengguna (Frontend). File di sini fokus pada struktur HTML dan penyajian data (PHP sebatas untuk *echo* data).
*   **`/includes`**: Berisi potongan-potongan kode frontend yang dapat digunakan kembali (*reusable components*), seperti `header.php`, `footer.php`, `sidebar.php`, maupun *navbar*.
*   **`/css` & `/assets`**: Direktori untuk menyimpan aset-aset statis seperti *stylesheet* (CSS), gambar, ikon, maupun *script* klien (JavaScript) yang bersifat publik.

---

## 2. Bedah Konfigurasi Routing Vercel (`vercel.json`)

Karena aplikasi di-deploy pada lingkungan Serverless di Vercel menggunakan `vercel-php@0.6.2`, file `vercel.json` berfungsi sebagai pengganti pengaturan server tradisional (seperti `.htaccess` pada Apache).

**Bagian Builds:**
Menentukan bagaimana lingkungan eksekusi disiapkan. Ekstensi `*.php` diproses sebagai eksekusi PHP (`vercel-php`), sementara file statis (`css`, `assets`) secara tegas diarahkan ke `@vercel/static` agar dapat di-_cache_ di CDN secara optimal tanpa membebani _compute resources_.

**Bagian Routes:**
Sistem routing dieksekusi secara berurutan (dari atas ke bawah):

1.  **Statis (`/css/(.*)` dan `/assets/(.*)`)**: 
    Jika URL merujuk ke `/css/...` atau `/assets/...`, Vercel akan langsung mengembalikan file dari CDN tanpa mengeksekusi skrip PHP apa pun.
2.  **Backend Khusus (`/api/(.*)`)**: 
    Permintaan (request) yang mengarah ke `/api/...` akan diteruskan apa adanya ke dalam direktori `/api/`. Hal ini mengizinkan file PHP di dalam `/api/` diakses langsung sebagai endpoint terpisah.
3.  **Catch-all Routing (`/(.*)` -> `/index.php`)**: 
    Ini adalah inti dari *Front Controller Pattern*. Jika URL tidak memenuhi kondisi 1 dan 2, seluruh *request* (misal: `/dashboard`, `/laporan`, dll.) akan diteruskan secara paksa ke `/index.php`. File `index.php` ini nantinya yang bertugas untuk memproses parameter URL dan menentukan file `/views/...` mana yang harus dirender kepada pengguna.

---

## 3. Aturan Pengembangan (Development Guidelines)

Untuk menjaga agar aplikasi tetap terukur (*scalable*), mudah di-_maintenance_, dan meminimalisir *"Spaghetti Code"*, patuhi batasan-batasan (boundaries) berikut:

### Aturan Frontend (Presentasi)
*   **Lokasi**: Letakkan HANYA di `/views`, `/includes`, `/css`, dan `/assets`.
*   **Batasan**:
    *   > [!WARNING]
    *   File dalam `/views` dan `/includes` **TIDAK BOLEH** mengeksekusi *query* ke database secara langsung (jangan menggunakan *raw SQL* di dalam file HTML/View).
    *   Penggunaan PHP pada `/views` murni hanya untuk kontrol alur tampilan sederhana seperti `if/else` (menampilkan/menyembunyikan elemen), perulangan `foreach` (menampilkan tabel), dan *echo* variabel.
    *   Setiap kali membangun UI baru, pastikan untuk memanfaatkan komponen *layout* dari direktori `/includes`.

### Aturan Backend & API (Logika Sistem)
*   **Lokasi**: Letakkan HANYA di `/classes`, `/actions`, `/api`, dan `/config`.
*   **Batasan**:
    *   > [!IMPORTANT]
    *   File dalam direktori ini **TIDAK BOLEH** melakukan proses *render* tampilan HTML (tidak boleh ada struktur tag `<html>`, `<body>`, dll).
    *   Semua koneksi dan kueri database (CRUD) harus dibungkus dalam bentuk fungsi/metode di dalam file-file `/classes`.
    *   **Untuk `/api`**: Harus mengembalikan representasi data mentah dan menetapkan *header* dengan benar (contoh: `header('Content-Type: application/json')`).
    *   **Untuk `/actions`**: Setelah proses logika (contoh: autentikasi login atau _insert_ ke database) berhasil maupun gagal diselesaikan, wajib ditutup dengan instruksi `header('Location: ...')` untuk me-_redirect_ user, guna menghindari pengiriman ulang data (Form Resubmission) saat *refresh* halaman.

### Aturan Routing Pusat
*   File `index.php` di luar folder (root directory) hanya bertindak sebagai pengatur lalu lintas (*Router/Dispatcher*).
*   Jangan menaruh logika bisnis yang rumit (seperti *query builder* panjang) di dalam `index.php`. Tugaskan kembali ke `/classes`.
