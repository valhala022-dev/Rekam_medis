<?php
/**
 * Layout atas: sidebar (desktop tetap terbuka, mobile offcanvas) + toast notifikasi.
 * Variabel yang diharapkan: $pageTitle (string), $active (string)
 */
$pageTitle = $pageTitle ?? 'Rekam Medis';
$active    = $active ?? '';
$menu = [
    'dashboard'   => ['index.php',       'Dashboard',          'home'],
    'pasien'      => ['pasien.php',      'Data Pasien',        'user'],
    'dokter'      => ['dokter.php',      'Data Dokter',        'doctor'],
    'obat'        => ['obat.php',        'Data Obat',          'pill'],
    'rekam_medis' => ['rekam_medis.php', 'Rekam Medis',        'clipboard'],
    'laporan'     => ['laporan.php',     'Laporan Riwayat',    'report'],
];
$flash = pull_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · Sistem Rekam Medis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .input{width:100%;border:1px solid #cbd5e1;border-radius:.6rem;padding:.55rem .75rem;background:#fff;font-size:.95rem;transition:box-shadow .15s,border-color .15s}
        .input:focus{outline:none;border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.2)}
        .input:invalid:not(:placeholder-shown):not(select){border-color:#f87171}
        .btn{display:inline-flex;align-items:center;gap:.4rem;border-radius:.6rem;padding:.55rem 1rem;font-weight:600;font-size:.9rem;transition:background .15s}
        .btn-primary{background:#0d9488;color:#fff}.btn-primary:hover{background:#0f766e}
        .btn-ghost{background:#f1f5f9;color:#334155}.btn-ghost:hover{background:#e2e8f0}
        .btn-danger{background:#fee2e2;color:#b91c1c}.btn-danger:hover{background:#fecaca}
        .btn-edit{background:#e0f2fe;color:#0369a1}.btn-edit:hover{background:#bae6fd}
        .btn-sm{padding:.35rem .65rem;font-size:.8rem}
        @keyframes toast-in{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none}}
        .toast{animation:toast-in .25s ease-out}
        @media print{#sidebar,#topbar,.no-print{display:none!important}main{margin-left:0!important}}
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

<!-- Overlay mobile -->
<div id="overlay" class="fixed inset-0 bg-slate-900/50 z-30 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar"
       class="fixed inset-y-0 left-0 w-64 bg-slate-900 text-slate-200 z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 flex flex-col">
    <div class="flex items-center justify-between px-5 h-16 border-b border-slate-800">
        <div class="flex items-center gap-2 font-bold text-white">
            <span class="grid place-items-center w-8 h-8 rounded-lg bg-teal-500"><?= icon('clipboard', 'w-5 h-5 text-white') ?></span>
            Rekam Medis
        </div>
        <button type="button" id="closeSidebar" class="lg:hidden p-1 rounded hover:bg-slate-800" aria-label="Tutup menu"><?= icon('close') ?></button>
    </div>
    <nav class="flex-1 overflow-y-auto p-3 space-y-1">
        <?php foreach ($menu as $key => [$href, $label, $ic]): ?>
            <a href="<?= e($href) ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?= $active === $key ? 'bg-teal-600 text-white' : 'hover:bg-slate-800 text-slate-300' ?>"
               <?= $active === $key ? 'aria-current="page"' : '' ?>>
                <?= icon($ic) ?> <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="p-4 text-xs text-slate-500 border-t border-slate-800">Uji Kompetensi Pemrograman Web<br>SMK Negeri 3 Tuban</div>
</aside>

<!-- Konten -->
<div class="lg:ml-64 min-h-screen flex flex-col">
    <header id="topbar" class="lg:hidden sticky top-0 z-20 bg-white border-b border-slate-200 h-14 flex items-center gap-3 px-4">
        <button type="button" id="openSidebar" class="p-2 -ml-2 rounded-lg hover:bg-slate-100" aria-label="Buka menu"><?= icon('menu', 'w-6 h-6') ?></button>
        <span class="font-semibold"><?= e($pageTitle) ?></span>
    </header>

    <?php if ($flash): $ok = $flash['type'] === 'success'; ?>
        <div id="toast-wrap" class="fixed top-4 right-4 left-4 sm:left-auto sm:w-96 z-50">
            <div class="toast flex items-start gap-3 rounded-xl border p-4 shadow-lg <?= $ok ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800' ?>" role="alert">
                <?= icon($ok ? 'check' : 'warn', 'w-5 h-5 mt-0.5 shrink-0') ?>
                <p class="text-sm font-medium flex-1"><?= e($flash['msg']) ?></p>
                <button type="button" data-dismiss-toast class="opacity-60 hover:opacity-100" aria-label="Tutup"><?= icon('close', 'w-4 h-4') ?></button>
            </div>
        </div>
    <?php endif; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
        <div class="mb-6 hidden lg:block">
            <h1 class="text-2xl font-bold text-slate-900"><?= e($pageTitle) ?></h1>
        </div>
