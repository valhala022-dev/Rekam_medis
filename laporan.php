<?php
require __DIR__ . '/includes/functions.php';
$db = db();

// Laporan terpadu: INNER JOIN 4 tabel (rekam_medis, pasien, dokter, obat) - tanpa kode ID
$rows = fetch_all($db,
    "SELECT rm.tanggal_periksa, p.nama_pasien, d.nama_dokter, rm.keluhan, rm.diagnosa,
            rm.tindakan, o.nama_obat, rm.resep
       FROM rekam_medis rm
       INNER JOIN pasien p ON rm.id_pasien = p.id_pasien
       INNER JOIN dokter d ON rm.id_dokter = d.id_dokter
       INNER JOIN obat   o ON rm.id_obat   = o.id_obat
      ORDER BY rm.tanggal_periksa DESC, rm.id_rekam DESC");

$pageTitle = 'Laporan Riwayat Pemeriksaan';
$active = 'laporan';
require __DIR__ . '/includes/header.php';
?>

<section class="bg-white rounded-2xl border border-slate-200 shadow-sm">
    <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-semibold text-slate-900">Riwayat Pemeriksaan</h2>
            <p class="text-sm text-slate-500"><?= count($rows) ?> pemeriksaan tercatat</p>
        </div>
        <button type="button" onclick="window.print()" class="btn btn-ghost no-print"><?= icon('print', 'w-4 h-4') ?> Cetak</button>
    </div>

    <!-- Tabel (tablet/desktop), dibungkus overflow-x-auto agar bisa digeser -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm min-w-[1000px]">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Tanggal Periksa</th>
                    <th class="px-4 py-3">Nama Pasien</th>
                    <th class="px-4 py-3">Nama Dokter</th>
                    <th class="px-4 py-3">Keluhan</th>
                    <th class="px-4 py-3">Diagnosa</th>
                    <th class="px-4 py-3">Tindakan</th>
                    <th class="px-4 py-3">Nama Obat</th>
                    <th class="px-4 py-3">Resep</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 align-top">
            <?php if (!$rows): ?>
                <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat pemeriksaan.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 whitespace-nowrap"><?= e(tgl($r['tanggal_periksa'])) ?></td>
                    <td class="px-4 py-3 font-medium"><?= e($r['nama_pasien']) ?></td>
                    <td class="px-4 py-3"><?= e($r['nama_dokter']) ?></td>
                    <td class="px-4 py-3"><?= e($r['keluhan']) ?></td>
                    <td class="px-4 py-3"><?= e($r['diagnosa']) ?></td>
                    <td class="px-4 py-3"><?= e($r['tindakan']) ?></td>
                    <td class="px-4 py-3"><?= e($r['nama_obat']) ?></td>
                    <td class="px-4 py-3"><?= e($r['resep']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Card list (di bawah 768px) -->
    <div class="md:hidden divide-y divide-slate-100">
        <?php if (!$rows): ?><p class="p-6 text-center text-sm text-slate-500">Belum ada riwayat pemeriksaan.</p><?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <article class="p-4 space-y-1.5 text-sm">
                <div class="flex items-start justify-between gap-3">
                    <p class="font-semibold text-base"><?= e($r['nama_pasien']) ?></p>
                    <span class="text-xs text-slate-500 shrink-0"><?= e(tgl($r['tanggal_periksa'])) ?></span>
                </div>
                <p><span class="text-slate-500">Dokter:</span> <?= e($r['nama_dokter']) ?></p>
                <p><span class="text-slate-500">Keluhan:</span> <?= e($r['keluhan']) ?></p>
                <p><span class="text-slate-500">Diagnosa:</span> <?= e($r['diagnosa']) ?></p>
                <p><span class="text-slate-500">Tindakan:</span> <?= e($r['tindakan']) ?></p>
                <p><span class="text-slate-500">Obat:</span> <?= e($r['nama_obat']) ?></p>
                <p><span class="text-slate-500">Resep:</span> <?= e($r['resep']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
