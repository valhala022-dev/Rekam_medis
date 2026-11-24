<?php
require __DIR__ . '/includes/functions.php';
$db = db();

/* ================= PROSES (Create / Update / Delete) ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('pasien.php');
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id     = (int)($_POST['id_pasien'] ?? 0);
        $no_rm  = trim($_POST['no_rm'] ?? '');
        $nama   = trim($_POST['nama_pasien'] ?? '');
        $jk     = $_POST['jenis_kelamin'] ?? '';
        $tgl    = trim($_POST['tanggal_lahir'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $back   = $id ? "pasien.php?edit=$id" : 'pasien.php';

        // Validasi backend
        $err = [];
        if ($no_rm === '' || mb_strlen($no_rm) > 20)   $err[] = 'No. RM wajib diisi (maks. 20 karakter)';
        if ($nama === '' || mb_strlen($nama) > 100)    $err[] = 'Nama pasien wajib diisi (maks. 100 karakter)';
        if (!in_array($jk, ['L', 'P'], true))          $err[] = 'Jenis kelamin tidak valid';
        if (!valid_date($tgl) || $tgl > date('Y-m-d')) $err[] = 'Tanggal lahir tidak valid';
        if ($alamat === '')                            $err[] = 'Alamat wajib diisi';

        if ($err) {
            keep_old($_POST);
            flash('error', 'Gagal: ' . implode('; ', $err) . '.');
            redirect($back);
        }

        try {
            if ($id > 0) {
                $st = $db->prepare('UPDATE pasien SET no_rm=?, nama_pasien=?, jenis_kelamin=?, tanggal_lahir=?, alamat=? WHERE id_pasien=?');
                $st->bind_param('sssssi', $no_rm, $nama, $jk, $tgl, $alamat, $id);
                $st->execute();
                flash('success', 'Sukses: Data pasien berhasil diperbarui.');
            } else {
                $st = $db->prepare('INSERT INTO pasien (no_rm, nama_pasien, jenis_kelamin, tanggal_lahir, alamat) VALUES (?,?,?,?,?)');
                $st->bind_param('sssss', $no_rm, $nama, $jk, $tgl, $alamat);
                $st->execute();
                flash('success', 'Sukses: Data pasien berhasil disimpan.');
            }
            $st->close();
        } catch (mysqli_sql_exception $ex) {
            keep_old($_POST);
            if ((int)$ex->getCode() === 1062) {
                flash('error', "Gagal: No. RM \"$no_rm\" sudah terdaftar.");
            } else {
                flash('error', 'Gagal: Data tidak dapat disimpan.');
            }
            redirect($back);
        }
        redirect('pasien.php');
    }

    if ($action === 'delete') {
        delete_master($db, 'pasien', 'id_pasien', (int)($_POST['id_pasien'] ?? 0), 'pasien');
        redirect('pasien.php');
    }
    redirect('pasien.php');
}

/* ================= TAMPIL (Read) ================= */
$edit = null;
if (isset($_GET['edit'])) {
    $edit = fetch_one($db, 'SELECT * FROM pasien WHERE id_pasien = ?', 'i', [(int)$_GET['edit']]);
    if (!$edit) {
        flash('error', 'Gagal: Data pasien tidak ditemukan.');
        redirect('pasien.php');
    }
}
$old  = pull_old();
$form = $old ?: ($edit ?? []);
$v = fn(string $k, $d = '') => e($form[$k] ?? $d);

$rows = fetch_all($db, 'SELECT * FROM pasien ORDER BY id_pasien ASC');

$pageTitle = 'Data Pasien';
$active = 'pasien';
require __DIR__ . '/includes/header.php';
?>

<section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
    <h2 class="font-semibold text-slate-900 mb-4"><?= $edit ? 'Edit Pasien' : 'Tambah Pasien' ?></h2>
    <form method="post" action="pasien.php" data-validate novalidate class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id_pasien" value="<?= $edit ? (int)$edit['id_pasien'] : '' ?>">

        <div>
            <label for="no_rm" class="block text-sm font-medium mb-1">No. Rekam Medis</label>
            <input class="input" type="text" id="no_rm" name="no_rm" maxlength="20" required placeholder="RM-0004" value="<?= $v('no_rm') ?>">
        </div>
        <div>
            <label for="nama_pasien" class="block text-sm font-medium mb-1">Nama Pasien</label>
            <input class="input" type="text" id="nama_pasien" name="nama_pasien" maxlength="100" required placeholder="Nama lengkap" value="<?= $v('nama_pasien') ?>">
        </div>
        <div>
            <label for="jenis_kelamin" class="block text-sm font-medium mb-1">Jenis Kelamin</label>
            <select class="input" id="jenis_kelamin" name="jenis_kelamin" required>
                <option value="">-- Pilih --</option>
                <option value="L" <?= ($form['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                <option value="P" <?= ($form['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
            </select>
        </div>
        <div>
            <label for="tanggal_lahir" class="block text-sm font-medium mb-1">Tanggal Lahir</label>
            <input class="input" type="date" id="tanggal_lahir" name="tanggal_lahir" max="<?= date('Y-m-d') ?>" required value="<?= $v('tanggal_lahir') ?>">
        </div>
        <div class="md:col-span-2">
            <label for="alamat" class="block text-sm font-medium mb-1">Alamat</label>
            <textarea class="input" id="alamat" name="alamat" rows="2" required placeholder="Alamat lengkap"><?= $v('alamat') ?></textarea>
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><?= icon($edit ? 'check' : 'plus', 'w-4 h-4') ?> <?= $edit ? 'Simpan Perubahan' : 'Simpan' ?></button>
            <?php if ($edit): ?><a href="pasien.php" class="btn btn-ghost">Batal</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Daftar Pasien</h2>
        <span class="text-sm text-slate-500"><?= count($rows) ?> data</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[760px]">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-5 py-3">No</th><th class="px-5 py-3">No. RM</th><th class="px-5 py-3">Nama</th>
                    <th class="px-5 py-3">JK</th><th class="px-5 py-3">Tgl Lahir</th><th class="px-5 py-3">Alamat</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada data pasien.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $i => $r): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3"><?= $i + 1 ?></td>
                    <td class="px-5 py-3 whitespace-nowrap"><?= e($r['no_rm']) ?></td>
                    <td class="px-5 py-3 font-medium"><?= e($r['nama_pasien']) ?></td>
                    <td class="px-5 py-3"><?= $r['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                    <td class="px-5 py-3 whitespace-nowrap"><?= e(tgl($r['tanggal_lahir'])) ?></td>
                    <td class="px-5 py-3"><?= e($r['alamat']) ?></td>
                    <td class="px-5 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="pasien.php?edit=<?= (int)$r['id_pasien'] ?>" class="btn btn-edit btn-sm"><?= icon('edit', 'w-4 h-4') ?> Edit</a>
                            <form method="post" action="pasien.php" data-confirm="Hapus pasien &quot;<?= e($r['nama_pasien']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_pasien" value="<?= (int)$r['id_pasien'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm"><?= icon('trash', 'w-4 h-4') ?> Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
