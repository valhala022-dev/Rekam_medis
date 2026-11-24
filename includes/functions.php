<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

/** Escape output HTML (cegah XSS). */
function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/* ---------- Flash message (Toast) ---------- */
function flash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function pull_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Input lama (agar form tidak kosong saat validasi gagal) ---------- */
function keep_old(array $data): void
{
    $_SESSION['old'] = $data;
}

function pull_old(): array
{
    $o = $_SESSION['old'] ?? [];
    unset($_SESSION['old']);
    return $o;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(string $back): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        flash('error', 'Gagal: Sesi form tidak valid, silakan coba lagi.');
        redirect($back);
    }
}

/* ---------- Helper database ---------- */

/** Hitung baris tabel dengan SELECT COUNT(*) (real-time). */
function count_rows(mysqli $db, string $table): int
{
    $allowed = ['pasien', 'dokter', 'obat', 'rekam_medis'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Tabel tidak dikenal');
    }
    $res = $db->query("SELECT COUNT(*) FROM `$table`");
    return (int)$res->fetch_row()[0];
}

/** Jalankan SELECT (prepared) dan kembalikan semua baris sebagai array asosiatif. */
function fetch_all(mysqli $db, string $sql, string $types = '', array $params = []): array
{
    $st = $db->prepare($sql);
    if ($types !== '') {
        $st->bind_param($types, ...$params);
    }
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    return $rows;
}

/** Ambil satu baris, atau null. */
function fetch_one(mysqli $db, string $sql, string $types = '', array $params = []): ?array
{
    $rows = fetch_all($db, $sql, $types, $params);
    return $rows[0] ?? null;
}

/**
 * Hapus data master dengan penanganan relasi:
 * ditolak bila masih dipakai tabel rekam_medis (pesan jelas ke pengguna).
 */
function delete_master(mysqli $db, string $table, string $pk, int $id, string $label): void
{
    $map = ['pasien' => 'id_pasien', 'dokter' => 'id_dokter', 'obat' => 'id_obat'];
    if (($map[$table] ?? null) !== $pk) {
        throw new InvalidArgumentException('Tabel/kolom tidak valid');
    }

    $st = $db->prepare("SELECT COUNT(*) FROM rekam_medis WHERE `$pk` = ?");
    $st->bind_param('i', $id);
    $st->execute();
    $st->bind_result($used);
    $st->fetch();
    $st->close();

    if ($used > 0) {
        flash('error', "Gagal: Data sedang digunakan ($label dipakai di $used rekam medis), tidak dapat dihapus.");
        return;
    }

    try {
        $st = $db->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
        $st->bind_param('i', $id);
        $st->execute();
        $affected = $st->affected_rows;
        $st->close();
        if ($affected > 0) {
            flash('success', "Sukses: Data $label berhasil dihapus.");
        } else {
            flash('error', 'Gagal: Data tidak ditemukan.');
        }
    } catch (mysqli_sql_exception $ex) {
        // 1451 = foreign key constraint (jaring pengaman terakhir)
        if ((int)$ex->getCode() === 1451) {
            flash('error', 'Gagal: Data sedang digunakan.');
        } else {
            flash('error', 'Gagal: Terjadi kesalahan database.');
        }
    }
}

/** Validasi tanggal format Y-m-d. */
function valid_date(string $d): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt !== false && $dt->format('Y-m-d') === $d;
}

/** Format tanggal ke dd-mm-yyyy untuk tampilan. */
function tgl(string $d): string
{
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt ? $dt->format('d-m-Y') : $d;
}

/* ---------- Ikon SVG (tanpa library eksternal) ---------- */
function icon(string $name, string $cls = 'w-5 h-5'): string
{
    $paths = [
        'home'     => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10',
        'user'     => 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0',
        'doctor'   => 'M12 4v6m-3-3h6M5 21v-3a7 7 0 0114 0v3',
        'pill'     => 'M10.5 20.5a5 5 0 01-7-7l10-10a5 5 0 017 7l-10 10zM8.5 8.5l7 7',
        'clipboard'=> 'M9 5h6M9 3h6a2 2 0 012 2v0h2a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1h2v0a2 2 0 012-2zM8 11h8M8 15h5',
        'report'   => 'M9 17v-6m3 6V7m3 10v-4M4 4h16v16H4z',
        'menu'     => 'M4 6h16M4 12h16M4 18h16',
        'close'    => 'M6 6l12 12M18 6L6 18',
        'edit'     => 'M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4z',
        'trash'    => 'M4 7h16M10 11v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3',
        'plus'     => 'M12 5v14M5 12h14',
        'check'    => 'M5 13l4 4L19 7',
        'warn'     => 'M12 9v4m0 4h.01M10.3 3.9L2.4 18a2 2 0 001.7 3h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z',
        'print'    => 'M7 9V3h10v6M7 17H5a1 1 0 01-1-1v-5a2 2 0 012-2h12a2 2 0 012 2v5a1 1 0 01-1 1h-2M7 14h10v7H7z',
    ];
    $d = $paths[$name] ?? '';
    return '<svg class="' . e($cls) . '" fill="none" stroke="currentColor" stroke-width="1.8" '
         . 'stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">'
         . '<path d="' . $d . '"/></svg>';
}
