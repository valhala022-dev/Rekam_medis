<?php
require __DIR__ . '/includes/functions.php';
$db = db();

/** Pastikan sebuah ID benar-benar ada di tabel master (menjaga validitas Foreign Key). */
function id_exists(mysqli $db, string $table, string $pk, int $id): bool
{
    $map = ['pasien' => 'id_pasien', 'dokter' => 'id_dokter', 'obat' => 'id_obat'];
    if (($map[$table] ?? null) !== $pk) {
        return false;
    }
    return fetch_one($db, "SELECT 1 FROM `$table` WHERE `$pk` = ?", 'i', [$id]) !== null;
}

/* ================= PROSES (Create / Update / Delete) ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('rekam_medis.php');
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id        = (int)($_POST['id_rekam'] ?? 0);
        $id_pasien = (int)($_POST['id_pasien'] ?? 0);
        $id_dokter = (int)($_POST['id_dokter'] ?? 0);
        $id_obat   = (int)($_POST['id_obat'] ?? 0);
        $tanggal   = trim($_POST['tanggal_periksa'] ?? '');
        $keluhan   = trim($_POST['keluhan'] ?? '');
        $diagnosa  = trim($_POST['diagnosa'] ?? '');
        $tindakan  = trim($_POST['tindakan'] ?? '');
        $resep     = trim($_POST['resep'] ?? '');
        $back      = $id ? "rekam_medis.php?edit=$id" : 'rekam_medis.php';

        $err = [];
        if (!id_exists($db, 'pasien', 'id_pasien', $id_pasien)) $err[] = 'Pasien wajib dipilih dari daftar';
        if (!id_exists($db, 'dokter', 'id_dokter', $id_dokter)) $err[] = 'Dokter wajib dipilih dari daftar';
        if (!id_exists($db, 'obat', 'id_obat', $id_obat))       $err[] = 'Obat wajib dipilih dari daftar';
        if (!valid_date($tanggal))                              $err[] = 'Tanggal periksa tidak valid';
        if ($keluhan === '')                                    $err[] = 'Keluhan wajib diisi';
        if ($diagnosa === '')                                   $err[] = 'Diagnosa wajib diisi';
        if ($tindakan === '')                                   $err[] = 'Tindakan wajib diisi';
        if ($resep === '' || mb_strlen($resep) > 100)           $err[] = 'Resep wajib diisi (maks. 100 karakter)';

        if ($err) {
            keep_old($_POST);
            flash('error', 'Gagal: ' . implode('; ', $err) . '.');
            redirect($back);
        }

        try {
            if ($id > 0) {
                $st = $db->prepare('UPDATE rekam_medis SET id_pasien=?, id_dokter=?, id_obat=?, tanggal_periksa=?, keluhan=?, diagnosa=?, tindakan=?, resep=? WHERE id_rekam=?');
                $st->bind_param('iiisssssi', $id_pasien, $id_dokter, $id_obat, $tanggal, $keluhan, $diagnosa, $tindakan, $resep, $id);
                $st->execute();
                flash('success', 'Sukses: Data rekam medis berhasil diperbarui.');
            } else {
                $st = $db->prepare('INSERT INTO rekam_medis (id_pasien, id_dokter, id_obat, tanggal_periksa, keluhan, diagnosa, tindakan, resep) VALUES (?,?,?,?,?,?,?,?)');
                $st->bind_param('iiisssss', $id_pasien, $id_dokter, $id_obat, $tanggal, $keluhan, $diagnosa, $tindakan, $resep);
                $st->execute();
                flash('success', 'Sukses: Data rekam medis berhasil disimpan.');
            }
            $st->close();
        } catch (mysqli_sql_exception $ex) {
            keep_old($_POST);
            // 1452 = foreign key tidak valid
            flash('error', ((int)$ex->getCode() === 1452)
                ? 'Gagal: Pasien/dokter/obat yang dipilih tidak valid.'
                : 'Gagal: Data tidak dapat disimpan.');
            redirect($back);
        }
        redirect('rekam_medis.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id_rekam'] ?? 0);
        try {
            $st = $db->prepare('DELETE FROM rekam_medis WHERE id_rekam = ?');
            $st->bind_param('i', $id);
            $st->execute();
            $n = $st->affected_rows;
            $st->close();
            flash($n > 0 ? 'success' : 'error', $n > 0 ? 'Sukses: Data rekam medis berhasil dihapus.' : 'Gagal: Data tidak ditemukan.');
        } catch (mysqli_sql_exception $ex) {
            flash('error', 'Gagal: Data tidak dapat dihapus.');
        }
        redirect('rekam_medis.php');
    }
    redirect('rekam_medis.php');
}

/* ================= TAMPIL (Read) ================= */
$edit = null;
if (isset($_GET['edit'])) {
    $edit = fetch_one($db, 'SELECT * FROM rekam_medis WHERE id_rekam = ?', 'i', [(int)$_GET['edit']]);
    if (!$edit) {
        flash('error', 'Gagal: Data rekam medis tidak ditemukan.');
        redirect('rekam_medis.php');
    }
}
$old  = pull_old();
$form = $old ?: ($edit ?? []);
$v = fn(string $k, $d = '') => e($form[$k] ?? $d);

// Data dropdown diambil langsung dari database (SELECT), bukan diketik manual
$listPasien = fetch_all($db, 'SELECT id_pasien, no_rm, nama_pasien FROM pasien ORDER BY nama_pasien ASC');
$listDokter = fetch_all($db, 'SELECT id_dokter, nama_dokter, spesialisasi FROM dokter ORDER BY nama_dokter ASC');
$listObat   = fetch_all($db, 'SELECT id_obat, nama_obat, satuan, stok FROM obat ORDER BY nama_obat ASC');
$siapIsi    = $listPasien && $listDokter && $listObat;

$rows = fetch_all($db,
    "SELECT rm.id_rekam, rm.tanggal_periksa, p.nama_pasien, d.nama_dokter, o.nama_obat,
            rm.keluhan, rm.diagnosa, rm.tindakan, rm.resep
       FROM rekam_medis rm
       INNER JOIN pasien p ON rm.id_pasien = p.id_pasien
       INNER JOIN dokter d ON rm.id_dokter = d.id_dokter
       INNER JOIN obat   o ON rm.id_obat   = o.id_obat
      ORDER BY rm.tanggal_periksa DESC, rm.id_rekam DESC");

$sel = fn($key, $val) => ((string)($form[$key] ?? '') === (string)$val) ? 'selected' : '';

$pageTitle = 'Rekam Medis';
$active = 'rekam_medis';
require __DIR__ . '/includes/header.php';
?>

<section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
    <h2 class="font-semibold text-slate-900 mb-4"><?= $edit ? 'Edit Rekam Medis' : 'Tambah Rekam Medis' ?></h2>

    <?php if (!$siapIsi): ?>
        <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm p-3">
            Lengkapi dulu data master: minimal 1 pasien, 1 dokter, dan 1 obat sebelum membuat rekam medis.
        </div>
    <?php endif; ?>

    <form method="post" action="rekam_medis.php" data-validate novalidate class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id_rekam" value="<?= $edit ? (int)$edit['id_rekam'] : '' ?>">

        <div>
            <label for="id_pasien" class="block text-sm font-medium mb-1">Pasien</label>
            <select class="input" id="id_pasien" name="id_pasien" required>
                <option value="">-- Pilih Pasien --</option>
                <?php foreach ($listPasien as $p): ?>
                    <option value="<?= (int)$p['id_pasien'] ?>" <?= $sel('id_pasien', $p['id_pasien']) ?>><?= e($p['no_rm'] . ' - ' . $p['nama_pasien']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="id_dokter" class="block text-sm font-medium mb-1">Dokter</label>
            <select class="input" id="id_dokter" name="id_dokter" required>
                <option value="">-- Pilih Dokter --</option>
                <?php foreach ($listDokter as $d): ?>
                    <option value="<?= (int)$d['id_dokter'] ?>" <?= $sel('id_dokter', $d['id_dokter']) ?>><?= e($d['nama_dokter'] . ' (' . $d['spesialisasi'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="id_obat" class="block text-sm font-medium mb-1">Obat</label>
            <select class="input" id="id_obat" name="id_obat" required>
                <option value="">-- Pilih Obat --</option>
                <?php foreach ($listObat as $o): ?>
                    <option value="<?= (int)$o['id_obat'] ?>" <?= $sel('id_obat', $o['id_obat']) ?>><?= e($o['nama_obat'] . ' (' . $o['satuan'] . ', stok ' . $o['stok'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="tanggal_periksa" class="block text-sm font-medium mb-1">Tanggal Periksa</label>
            <input class="input" type="date" id="tanggal_periksa" name="tanggal_periksa" required value="<?= $v('tanggal_periksa', date('Y-m-d')) ?>">
        </div>
        <div class="md:col-span-2">
            <label for="keluhan" class="block text-sm font-medium mb-1">Keluhan</label>
            <textarea class="input" id="keluhan" name="keluhan" rows="2" required placeholder="Keluhan pasien"><?= $v('keluhan') ?></textarea>
        </div>
        <div>
            <label for="diagnosa" class="block text-sm font-medium mb-1">Diagnosa</label>
            <textarea class="input" id="diagnosa" name="diagnosa" rows="2" required placeholder="Hasil diagnosa"><?= $v('diagnosa') ?></textarea>
        </div>
        <div>
            <label for="tindakan" class="block text-sm font-medium mb-1">Tindakan</label>
            <textarea class="input" id="tindakan" name="tindakan" rows="2" required placeholder="Tindakan yang dilakukan"><?= $v('tindakan') ?></textarea>
        </div>
        <div class="md:col-span-2">
            <label for="resep" class="block text-sm font-medium mb-1">Resep</label>
            <input class="input" type="text" id="resep" name="resep" maxlength="100" required placeholder="Contoh: 3x1 sesudah makan" value="<?= $v('resep') ?>">
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary" <?= $siapIsi ? '' : 'disabled' ?>><?= icon($edit ? 'check' : 'plus', 'w-4 h-4') ?> <?= $edit ? 'Simpan Perubahan' : 'Simpan' ?></button>
            <?php if ($edit): ?><a href="rekam_medis.php" class="btn btn-ghost">Batal</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Daftar Rekam Medis</h2>
        <span class="text-sm text-slate-500"><?= count($rows) ?> data</span>
    </div>

    <!-- Tabel (tablet/desktop) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm min-w-[900px]">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Pasien</th><th class="px-5 py-3">Dokter</th>
                    <th class="px-5 py-3">Diagnosa</th><th class="px-5 py-3">Obat</th><th class="px-5 py-3">Resep</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada rekam medis.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 whitespace-nowrap"><?= e(tgl($r['tanggal_periksa'])) ?></td>
                    <td class="px-5 py-3 font-medium"><?= e($r['nama_pasien']) ?></td>
                    <td class="px-5 py-3"><?= e($r['nama_dokter']) ?></td>
                    <td class="px-5 py-3"><?= e($r['diagnosa']) ?></td>
                    <td class="px-5 py-3"><?= e($r['nama_obat']) ?></td>
                    <td class="px-5 py-3"><?= e($r['resep']) ?></td>
                    <td class="px-5 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="rekam_medis.php?edit=<?= (int)$r['id_rekam'] ?>" class="btn btn-edit btn-sm"><?= icon('edit', 'w-4 h-4') ?> Edit</a>
                            <form method="post" action="rekam_medis.php" data-confirm="Hapus rekam medis pasien &quot;<?= e($r['nama_pasien']) ?>&quot; tanggal <?= e(tgl($r['tanggal_periksa'])) ?>?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_rekam" value="<?= (int)$r['id_rekam'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm"><?= icon('trash', 'w-4 h-4') ?> Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Card list (di bawah 768px) -->
    <div class="md:hidden divide-y divide-slate-100">
        <?php if (!$rows): ?><p class="p-6 text-center text-sm text-slate-500">Belum ada rekam medis.</p><?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <article class="p-4 space-y-2">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold"><?= e($r['nama_pasien']) ?></p>
                        <p class="text-xs text-slate-500"><?= e(tgl($r['tanggal_periksa'])) ?> &middot; <?= e($r['nama_dokter']) ?></p>
                    </div>
                    <span class="text-xs bg-teal-50 text-teal-700 rounded-full px-2 py-1 shrink-0"><?= e($r['nama_obat']) ?></span>
                </div>
                <p class="text-sm"><span class="text-slate-500">Diagnosa:</span> <?= e($r['diagnosa']) ?></p>
                <p class="text-sm"><span class="text-slate-500">Resep:</span> <?= e($r['resep']) ?></p>
                <div class="flex gap-2 pt-1">
                    <a href="rekam_medis.php?edit=<?= (int)$r['id_rekam'] ?>" class="btn btn-edit btn-sm"><?= icon('edit', 'w-4 h-4') ?> Edit</a>
                    <form method="post" action="rekam_medis.php" data-confirm="Hapus rekam medis pasien &quot;<?= e($r['nama_pasien']) ?>&quot; tanggal <?= e(tgl($r['tanggal_periksa'])) ?>?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_rekam" value="<?= (int)$r['id_rekam'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm"><?= icon('trash', 'w-4 h-4') ?> Hapus</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
