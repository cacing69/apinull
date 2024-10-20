# ApiNull

API Framework Modular dan Fleksibel
ApiNull adalah framework API berbasis PHP yang memudahkan pengembangan aplikasi modular dan fleksibel. Dengan struktur yang rapi dan dukungan middleware, Anda dapat membuat API yang kuat dan mudah dikelola.

- Modular: Fitur API dipecah menjadi modul yang terpisah, sehingga mudah dikembangkan dan dipelihara.
- Routing YAML: Gunakan file YAML yang sederhana untuk mengelola rute API dengan cepat.
- Middleware Dinamis: Tambahkan middleware sesuai kebutuhan untuk validasi, otorisasi, dan lainnya.
- Validasi Mudah: Integrasi dengan Symfony Validator memastikan input selalu valid dan sesuai aturan.

ApiNull ideal untuk pengembang yang membutuhkan framework API PHP ringan namun scalable, baik untuk proyek kecil maupun besar.

## Proyek API - Dokumentasi Penggunaan

Dokumentasi ini menjelaskan cara membuat handler, menambahkan route, dan mengintegrasikan middleware dalam proyek API Anda.

### Persiapan

Pastikan Anda telah mengunduh dan menginstal dependensi yang diperlukan dengan menjalankan

```bash
composer install
```

### Struktur Proyek

```bash
.
├── app/
│   ├── Console/
│   ├── Core/
│   ├── Http/
│   │   ├── Middlewares/
│   │   │   └── Router.php
│   │   └── helpers.php
├── config/
│   └── routes.yaml
├── logs/
├── modules/
│   └── User/
│       └── Handler.php
├── public/
├── tests/
│   └── Modules/
│       └── User/
├── vendor/
├── .gitignore
├── bootstrap.php
├── composer.json
├── composer.lock
├── console.php
└── README.md
```

### Struktur Folder `modules`

Di dalam folder `modules`, setiap modul akan berisi handler yang menangani permintaan terkait modul tersebut. Misalnya, modul `User` akan memiliki handler di `Handler.php`.

```bash
modules/
└── User/
    └── Handler.php
```

### Pembuatan Handler

Handler berfungsi untuk memproses request yang masuk dan mengembalikan response.

Contoh Handler di modules/User/Handler.php:

```php
<?php

namespace Modules\User;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Handler
{
    public function check(int $id, string $name): Response
    {
        return new Response("ID: $id, Name: $name", 200);

        // atau
        // return ["id" => $id, "name" => $name];
    }
}
```

Penjelasan:

- Method check akan menerima parameter id (integer) dan name (string).
- Mengembalikan response yang menunjukkan ID dan nama pengguna.

### Contoh Routing di `modules/User/routes.yaml`

Routing menggunakan file YAML untuk mendefinisikan path API dan handler yang akan dipanggil.

Contoh `modules/User/routes.yaml`:

```yaml
routes:
  - path: /check/{id}/{name}
    handler: Modules\User\Handler::check
    methods: [GET]
    middleware: [validateIntegerId, validateNameString]
```

Penjelasan:

Path /check/{id}/{name} akan memanggil method check dari class Modules\User\Handler.
Menggunakan dua middleware: validateIntegerId dan validateNameString.

### Integrasi Route ke Router Utama

Router utama akan membaca file YAML, mencocokkan route dengan request yang masuk, dan mengeksekusi handler yang sesuai.

`config/routes.yaml`

```yaml
imports:
  - { resource: ../modules/User/routes.yaml }
```

### Penambahan Middleware

Middleware digunakan untuk memproses request sebelum mencapai handler. Sebagai contoh, middleware bisa digunakan untuk memvalidasi bahwa id harus berupa integer dan name harus berupa string.

Contoh Middleware app/Http/Middlewares/ValidateIntegerId.php:

```php
<?php

namespace App\Http\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateIntegerId
{
    public function handle(Request $request, $next)
    {
        $id = $request->attributes->get('id');

        if (!is_numeric($id)) {
            return new Response('ID harus berupa angka', 400);
        }

        return $next($request);
    }
}
```

### Integrasi Middleware

Middleware diintegrasikan ke dalam route dengan menambahkannya di file config/routes.yaml pada setiap rute yang membutuhkan validasi.

```yaml
routes:
  - path: /check/{id}/{name}
    handler: Modules\User\Handler::check
    methods: [GET]
    middleware: [validateIntegerId]
```

### Menjalankan Proyek

Untuk menjalankan aplikasi, Anda bisa menggunakan server bawaan PHP atau server lain seperti Apache atau Nginx.

```bash
php -S localhost:8000 -t public
```

Akses endpoint Anda melalui browser atau tool seperti Postman:

```bash
GET http://localhost:8000/ping
```

Anda seharusnya mendapatkan respons JSON seperti ini:

```json
{
  "ping": "pong",
}
```
