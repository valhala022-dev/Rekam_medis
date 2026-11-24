<?php
require __DIR__ . '/includes/functions.php';
$db = db();

// Semua angka memakai SELECT COUNT(*) langsung ke database (real-time)
$stats = [
    ['Total Pasien',        count_rows($db, 'pasien'),      'user',      'pasien.php',      'bg-teal-500'],
    ['Total Dokter',        count_rows($db, 'dokter'),      'doctor',    'dokter.php',      'bg-sky-500'],
    ['Total Jenis Obat',    count_rows($db, 'obat'),        'pill',      'obat.php',        'bg-amber-500'],
    ['Total Rekam Medis',   count_rows($db, 'rekam_medis'), 'clipboard', 'rekam_medis.php', 'bg-violet-500'],
];

$terbaru = fetch_all($db,
    "SELECT rm.tanggal_periksa, p.nama_pasien, d.nama_dokter, rm.diagnosa, o.nama_obat
       FROM rekam_medis rm
       INNER JOIN pasien p ON rm.id_pasien = p.id_pasien
       INNER JOIN dokter d ON rm.id_dokter = d.id_dokter
       INNER JOIN obat   o ON rm.id_obat   = o.id_obat
      ORDER BY rm.tanggal_periksa DESC, rm.id_rekam DESC
      LIMIT 5");

$pageTitle = 'Dashboard';
$active = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
    <?php foreach ($stats as [$label, $total, $ic, $href, $color]): ?>
        <a href="<?= e($href) ?>" class="group bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition flex items-center gap-4">
            <span class="grid place-items-center w-14 h-14 rounded-xl text-white <?= $color ?>"><?= icon($ic, 'w-7 h-7') ?></span>
            <div>
                <p class="text-sm text-slate-500"><?= e($label) ?></p>
                <p class="text-3xl font-bold text-slate-900 leading-tight" data-stat="<?= e($label) ?>"><?= (int)$total ?></p>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<section class="mt-8 bg-white rounded-2xl border border-slate-200 shadow-sm">
    <div class="flex items-center justify-between p-5 border-b border-slate-100">
        <h2 class="font-semibold text-slate-900">Pemeriksaan Terbaru</h2>
        <a href="laporan.php" class="text-sm font-medium text-teal-700 hover:underline">Lihat semua</a>
    </div>
    <?php if (!$terbaru): ?>
        <p class="p-6 text-sm text-slate-500">Belum ada riwayat pemeriksaan.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr><th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Pasien</th><th class="px-5 py-3">Dokter</th><th class="px-5 py-3">Diagnosa</th><th class="px-5 py-3">Obat</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php foreach ($terbaru as $r): ?>
                    <tr>
                        <td class="px-5 py-3 whitespace-nowrap"><?= e(tgl($r['tanggal_periksa'])) ?></td>
                        <td class="px-5 py-3 font-medium"><?= e($r['nama_pasien']) ?></td>
                        <td class="px-5 py-3"><?= e($r['nama_dokter']) ?></td>
                        <td class="px-5 py-3"><?= e($r['diagnosa']) ?></td>
                        <td class="px-5 py-3"><?= e($r['nama_obat']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
