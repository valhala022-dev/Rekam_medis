<?php
/**
 * Konfigurasi & koneksi database (mysqli).
 * Nilai default cocok untuk MariaDB lokal di Alpine Linux.
 * Bisa diubah lewat environment variable: DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME.
 */

// Lempar exception untuk error mysqli agar mudah ditangani (try/catch)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

date_default_timezone_set('Asia/Jakarta');

/** Halaman error ramah bila database tidak bisa dihubungi. */
function db_error_page(string $detail): never
{
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><title>Database tidak terhubung</title>'
       . '<body style="font-family:sans-serif;max-width:600px;margin:10vh auto;padding:0 1rem">'
       . '<h2>Koneksi database gagal</h2>'
       . '<p>Pastikan MariaDB berjalan dan database <b>db_rekam_medis</b> sudah diimport '
       . '(<code>mariadb -uroot &lt; database.sql</code>), lalu jalankan <code>sh setup.sh</code> '
       . 'untuk membuat user aplikasi.</p>'
       . '<p style="color:#b91c1c"><b>Detail:</b> ' . htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') . '</p></body>';
    exit;
}

/** Koneksi mysqli (dibuat sekali, dipakai ulang). */
function db(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = (int)(getenv('DB_PORT') ?: 3306);
    // User khusus aplikasi (dibuat oleh setup.sh). 'root' MariaDB biasanya memakai
    // autentikasi socket sehingga tidak bisa dipakai lewat TCP.
    $user = getenv('DB_USER') ?: 'rekam';
    $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'rekam123';
    $name = getenv('DB_NAME') ?: 'db_rekam_medis';

    if (!class_exists('mysqli')) {
        db_error_page('Ekstensi PHP mysqli belum terpasang (apk add php82-mysqli).');
    }

    // Alpine: MariaDB bawaan memakai skip-networking (hanya socket), jadi bila ada
    // file socket kita pakai itu lebih dulu, baru TCP sebagai cadangan.
    $sockets = [];
    if (!getenv('DB_HOST')) {
        foreach (['/run/mysqld/mysqld.sock', '/var/run/mysqld/mysqld.sock', '/tmp/mysql.sock'] as $s) {
            if (file_exists($s)) {
                $sockets[] = $s;
            }
        }
    }

    $lastError = 'Tidak diketahui';
    foreach ($sockets as $sock) {
        try {
            $conn = new mysqli('localhost', $user, $pass, $name, $port, $sock);
            $conn->set_charset('utf8mb4');
            return $conn;
        } catch (Throwable $e) {
            $lastError = $e->getMessage();
        }
    }

    try {
        $conn = new mysqli($host, $user, $pass, $name, $port);
        $conn->set_charset('utf8mb4');
    } catch (Throwable $e) {
        // Pesan dari socket (bila ada) biasanya lebih informatif daripada "Connection refused" TCP
        db_error_page($sockets ? $lastError : $e->getMessage());
    }
    return $conn;
}

$db = db();