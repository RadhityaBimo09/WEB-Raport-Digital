<?php
/**
 * Komponen notifikasi (flash message).
 * Halaman pemanggil cukup menyiapkan variabel $flash = get_flash();
 */
if (!empty($flash['pesan'])):
    $tipe = $flash['tipe'] ?? 'info';

    $gaya = [
        'success' => 'clay-block-mint text-emerald-950 dark:text-emerald-200',
        'error'   => 'clay-block-alpha text-rose-950 dark:text-rose-200',
        'info'    => 'clay-card text-slate-700 dark:text-slate-200',
    ][$tipe] ?? 'clay-card text-slate-700 dark:text-slate-200';
?>
<div class="<?= $gaya ?> px-4 py-3 rounded-2xl flex items-start gap-2.5 shadow-xs text-xs font-extrabold">
    <svg class="w-4 h-4 shrink-0 mt-px" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
        <?php if ($tipe === 'error'): ?>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        <?php else: ?>
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <?php endif; ?>
    </svg>
    <span class="leading-snug"><?= e($flash['pesan']) ?></span>
</div>
<?php endif; ?>
