<?php
require __DIR__ . '/includes/functions.php';
$db = db();

/* ================= PROSES (Create / Update / Delete) ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('dokter.php');
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id    = (int)($_POST['id_dokter'] ?? 0);
        $nama  = trim($_POST['nama_dokter'] ?? '');
        $spes  = trim($_POST['spesialisasi'] ?? '');
        $back  = $id ? "dokter.php?edit=$id" : 'dokter.php';

        $err = [];
        if ($nama === '' || mb_strlen($nama) > 100) $err[] = 'Nama dokter wajib diisi (maks. 100 karakter)';
        if ($spes === '' || mb_strlen($spes) > 50)  $err[] = 'Spesialisasi wajib diisi (maks. 50 karakter)';

        if ($err) {
            keep_old($_POST);
            flash('error', 'Gagal: ' . implode('; ', $err) . '.');
            redirect($back);
        }

        try {
            if ($id > 0) {
                $st = $db->prepare('UPDATE dokter SET nama_dokter=?, spesialisasi=? WHERE id_dokter=?');
                $st->bind_param('ssi', $nama, $spes, $id);
                $st->execute();
                flash('success', 'Sukses: Data dokter berhasil diperbarui.');
            } else {
                $st = $db->prepare('INSERT INTO dokter (nama_dokter, spesialisasi) VALUES (?,?)');
                $st->bind_param('ss', $nama, $spes);
                $st->execute();
                flash('success', 'Sukses: Data dokter berhasil disimpan.');
            }
            $st->close();
        } catch (mysqli_sql_exception $ex) {
            keep_old($_POST);
            flash('error', 'Gagal: Data tidak dapat disimpan.');
            redirect($back);
        }
        redirect('dokter.php');
    }

    if ($action === 'delete') {
        delete_master($db, 'dokter', 'id_dokter', (int)($_POST['id_dokter'] ?? 0), 'dokter');
        redirect('dokter.php');
    }
    redirect('dokter.php');
}

/* ================= TAMPIL (Read) ================= */
$edit = null;
if (isset($_GET['edit'])) {
    $edit = fetch_one($db, 'SELECT * FROM dokter WHERE id_dokter = ?', 'i', [(int)$_GET['edit']]);
    if (!$edit) {
        flash('error', 'Gagal: Data dokter tidak ditemukan.');
        redirect('dokter.php');
    }
}
$old  = pull_old();
$form = $old ?: ($edit ?? []);
$v = fn(string $k, $d = '') => e($form[$k] ?? $d);

$rows = fetch_all($db, 'SELECT * FROM dokter ORDER BY id_dokter ASC');

$pageTitle = 'Data Dokter';
$active = 'dokter';
require __DIR__ . '/includes/header.php';
?>

<section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
    <h2 class="font-semibold text-slate-900 mb-4"><?= $edit ? 'Edit Dokter' : 'Tambah Dokter' ?></h2>
    <form method="post" action="dokter.php" data-validate novalidate class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id_dokter" value="<?= $edit ? (int)$edit['id_dokter'] : '' ?>">

        <div>
            <label for="nama_dokter" class="block text-sm font-medium mb-1">Nama Dokter</label>
            <input class="input" type="text" id="nama_dokter" name="nama_dokter" maxlength="100" required placeholder="Dr. Nama" value="<?= $v('nama_dokter') ?>">
        </div>
        <div>
            <label for="spesialisasi" class="block text-sm font-medium mb-1">Spesialisasi</label>
            <input class="input" type="text" id="spesialisasi" name="spesialisasi" maxlength="50" required placeholder="Umum / Penyakit Dalam / ..." value="<?= $v('spesialisasi') ?>">
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><?= icon($edit ? 'check' : 'plus', 'w-4 h-4') ?> <?= $edit ? 'Simpan Perubahan' : 'Simpan' ?></button>
            <?php if ($edit): ?><a href="dokter.php" class="btn btn-ghost">Batal</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Daftar Dokter</h2>
        <span class="text-sm text-slate-500"><?= count($rows) ?> data</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[520px]">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr><th class="px-5 py-3">No</th><th class="px-5 py-3">Nama Dokter</th><th class="px-5 py-3">Spesialisasi</th><th class="px-5 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (!$rows): ?>
                <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada data dokter.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $i => $r): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3"><?= $i + 1 ?></td>
                    <td class="px-5 py-3 font-medium"><?= e($r['nama_dokter']) ?></td>
                    <td class="px-5 py-3"><?= e($r['spesialisasi']) ?></td>
                    <td class="px-5 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="dokter.php?edit=<?= (int)$r['id_dokter'] ?>" class="btn btn-edit btn-sm"><?= icon('edit', 'w-4 h-4') ?> Edit</a>
                            <form method="post" action="dokter.php" data-confirm="Hapus dokter &quot;<?= e($r['nama_dokter']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_dokter" value="<?= (int)$r['id_dokter'] ?>">
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
