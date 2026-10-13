<!-- Konfirmasi Hapus Data (reusable, in-app, bukan popup browser).
     Dipakai bersama JS: form hapus beri class "js-confirm-hapus"
     dan atribut data-nama="Nama item" untuk konteks pesan. -->
<div id="modal-hapus-data" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="hapus-data-title">
    <div id="modal-hapus-data-content" class="clay-card bg-white dark:bg-slate-900 rounded-[28px] p-6 w-full max-w-md shadow-2xl border border-slate-200/80 dark:border-white/10 transition-all duration-300 opacity-0 scale-95">
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div class="min-w-0">
                <h3 id="hapus-data-title" class="text-base font-extrabold text-slate-800 dark:text-slate-100">Hapus Data?</h3>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Apakah Anda yakin ingin menghapus data ini?
                    <span id="hapus-data-nama" class="block mt-1 font-extrabold text-slate-700 dark:text-slate-200 truncate"></span>
                </p>
            </div>
        </div>
        <div class="flex justify-end gap-2.5 pt-4 mt-4 border-t border-slate-200/60 dark:border-slate-700/60">
            <button type="button" id="cancel-hapus-data" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">Batal</button>
            <button type="button" id="confirm-hapus-data" class="px-5 py-2.5 rounded-xl text-xs font-extrabold text-white bg-rose-500 hover:bg-rose-600 clay-btn inline-flex items-center justify-center cursor-pointer">Ya, Hapus</button>
        </div>
    </div>
</div>
