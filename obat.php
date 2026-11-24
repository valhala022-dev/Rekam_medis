<?php
require __DIR__ . '/includes/functions.php';
$db = db();

/* ================= PROSES (Create / Update / Delete) ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('obat.php');
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id     = (int)($_POST['id_obat'] ?? 0);
        $nama   = trim($_POST['nama_obat'] ?? '');
        $satuan = trim($_POST['satuan'] ?? '');
        $stokIn = trim($_POST['stok'] ?? '');
        $back   = $id ? "obat.php?edit=$id" : 'obat.php';

        $err = [];
        if ($nama === '' || mb_strlen($nama) > 100)      $err[] = 'Nama obat wajib diisi (maks. 100 karakter)';
        if ($satuan === '' || mb_strlen($satuan) > 30)   $err[] = 'Satuan wajib diisi (maks. 30 karakter)';
        if (!preg_match('/^\d{1,9}$/', $stokIn))         $err[] = 'Stok harus berupa angka bulat 0 atau lebih';

        if ($err) {
            keep_old($_POST);
            flash('error', 'Gagal: ' . implode('; ', $err) . '.');
            redirect($back);
        }
        $stok = (int)$stokIn;

        try {
            if ($id > 0) {
                $st = $db->prepare('UPDATE obat SET nama_obat=?, satuan=?, stok=? WHERE id_obat=?');
                $st->bind_param('ssii', $nama, $satuan, $stok, $id);
                $st->execute();
                flash('success', 'Sukses: Data obat berhasil diperbarui.');
            } else {
                $st = $db->prepare('INSERT INTO obat (nama_obat, satuan, stok) VALUES (?,?,?)');
                $st->bind_param('ssi', $nama, $satuan, $stok);
                $st->execute();
                flash('success', 'Sukses: Data obat berhasil disimpan.');
            }
            $st->close();
        } catch (mysqli_sql_exception $ex) {
            keep_old($_POST);
            flash('error', 'Gagal: Data tidak dapat disimpan.');
            redirect($back);
        }
        redirect('obat.php');
    }

    if ($action === 'delete') {
        delete_master($db, 'obat', 'id_obat', (int)($_POST['id_obat'] ?? 0), 'obat');
        redirect('obat.php');
    }
    redirect('obat.php');
}

/* ================= TAMPIL (Read) ================= */
$edit = null;
if (isset($_GET['edit'])) {
    $edit = fetch_one($db, 'SELECT * FROM obat WHERE id_obat = ?', 'i', [(int)$_GET['edit']]);
    if (!$edit) {
        flash('error', 'Gagal: Data obat tidak ditemukan.');
        redirect('obat.php');
    }
}
$old  = pull_old();
$form = $old ?: ($edit ?? []);
$v = fn(string $k, $d = '') => e($form[$k] ?? $d);

$rows = fetch_all($db, 'SELECT * FROM obat ORDER BY id_obat ASC');

$pageTitle = 'Data Obat';
$active = 'obat';
require __DIR__ . '/includes/header.php';
?>

<section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
    <h2 class="font-semibold text-slate-900 mb-4"><?= $edit ? 'Edit Obat' : 'Tambah Obat' ?></h2>
    <form method="post" action="obat.php" data-validate novalidate class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id_obat" value="<?= $edit ? (int)$edit['id_obat'] : '' ?>">

        <div>
            <label for="nama_obat" class="block text-sm font-medium mb-1">Nama Obat</label>
            <input class="input" type="text" id="nama_obat" name="nama_obat" maxlength="100" required placeholder="Nama obat" value="<?= $v('nama_obat') ?>">
        </div>
        <div>
            <label for="satuan" class="block text-sm font-medium mb-1">Satuan</label>
            <input class="input" type="text" id="satuan" name="satuan" maxlength="30" required placeholder="Strip / Tablet / Botol" value="<?= $v('satuan') ?>">
        </div>
        <div>
            <label for="stok" class="block text-sm font-medium mb-1">Stok</label>
            <input class="input" type="number" id="stok" name="stok" min="0" step="1" required placeholder="0" value="<?= $v('stok') ?>">
        </div>
        <div class="md:col-span-3 flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><?= icon($edit ? 'check' : 'plus', 'w-4 h-4') ?> <?= $edit ? 'Simpan Perubahan' : 'Simpan' ?></button>
            <?php if ($edit): ?><a href="obat.php" class="btn btn-ghost">Batal</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Daftar Obat</h2>
        <span class="text-sm text-slate-500"><?= count($rows) ?> data</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[560px]">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr><th class="px-5 py-3">No</th><th class="px-5 py-3">Nama Obat</th><th class="px-5 py-3">Satuan</th><th class="px-5 py-3">Stok</th><th class="px-5 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (!$rows): ?>
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada data obat.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $i => $r): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3"><?= $i + 1 ?></td>
                    <td class="px-5 py-3 font-medium"><?= e($r['nama_obat']) ?></td>
                    <td class="px-5 py-3"><?= e($r['satuan']) ?></td>
                    <td class="px-5 py-3"><?= (int)$r['stok'] ?></td>
                    <td class="px-5 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="obat.php?edit=<?= (int)$r['id_obat'] ?>" class="btn btn-edit btn-sm"><?= icon('edit', 'w-4 h-4') ?> Edit</a>
                            <form method="post" action="obat.php" data-confirm="Hapus obat &quot;<?= e($r['nama_obat']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_obat" value="<?= (int)$r['id_obat'] ?>">
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
