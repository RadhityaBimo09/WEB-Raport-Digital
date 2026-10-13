// ==========================================
// DARK MODE INITIATOR (Mencegah Flash Putih Saat Reload)
// ==========================================
(function () {
  const saved = localStorage.getItem('bi-theme');
  if (saved === 'dark') {
    document.documentElement.classList.add('dark');
  }
})();

document.addEventListener('DOMContentLoaded', () => {

  // ==========================================
  // HELPER: Generik Buka/Tutup Modal dengan Animasi
  // ==========================================
  function openModal(modal, backdrop, content) {
    if (!modal) return;
    modal.classList.remove('hidden');
    setTimeout(() => {
      backdrop?.classList.remove('opacity-0');
      content?.classList.remove('opacity-0', 'scale-95');
      content?.classList.add('opacity-100', 'scale-100');
    }, 10);
  }

  function closeModalGeneric(modal, backdrop, content) {
    if (!modal) return;
    backdrop?.classList.add('opacity-0');
    content?.classList.remove('opacity-100', 'scale-100');
    content?.classList.add('opacity-0', 'scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
  }

  // ==========================================
  // MODUL 0: TOGGLE DARK MODE (SIDEBAR) & LOGOUT RESET
  // ==========================================
  const themeToggle = document.getElementById('theme-toggle');
  const themeToggleCollapsed = document.getElementById('theme-toggle-collapsed');

  function updateThemeUI(isDark) {
    const lightOption = document.querySelector('.theme-option-light');
    const darkOption = document.querySelector('.theme-option-dark');

    if (lightOption && darkOption) {
      if (isDark) {
        lightOption.className = "theme-option-light flex-1 py-1.5 px-3 rounded-full flex items-center justify-center gap-2 text-xs font-bold transition-all text-slate-400 bg-transparent shadow-none";
        darkOption.className = "theme-option-dark flex-1 py-1.5 px-3 rounded-full flex items-center justify-center gap-2 text-xs font-bold transition-all text-white bg-brand-primary shadow-xs";
      } else {
        lightOption.className = "theme-option-light flex-1 py-1.5 px-3 rounded-full flex items-center justify-center gap-2 text-xs font-bold transition-all text-slate-800 bg-white shadow-xs";
        darkOption.className = "theme-option-dark flex-1 py-1.5 px-3 rounded-full flex items-center justify-center gap-2 text-xs font-bold transition-all text-slate-400 bg-transparent shadow-none";
      }
    }
  }

  function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('bi-theme', isDark ? 'dark' : 'light');
    updateThemeUI(isDark);
  }

  const initialDark = document.documentElement.classList.contains('dark');
  updateThemeUI(initialDark);

  window.toggleTheme = toggleTheme;
  window.updateThemeUI = updateThemeUI;

  themeToggle?.addEventListener('click', toggleTheme);
  themeToggleCollapsed?.addEventListener('click', toggleTheme);

  // --- AUTO RESET THEME & SIDEBAR ON LOGOUT ---
  const logoutLinks = document.querySelectorAll('a[href*="login.php"]');
  logoutLinks.forEach(link => {
    link.addEventListener('click', () => {
      localStorage.setItem('bi-theme', 'light');
      localStorage.setItem('bi-sidebar', 'collapsed');
      document.documentElement.classList.remove('dark');
    });
  });

  // ==========================================
  // MODUL 1: INTERAKSI SIDEBAR (MURENI BERSIH TANPA MANIPULASI MARGIN LOGO)
  // ==========================================
  const sidebar = document.getElementById('sidebar');
  const sidebarToggleBtn = document.getElementById('sidebar-toggle');
  const toggleTooltip = document.getElementById('sidebar-toggle-tooltip');
  const iconLeft = document.getElementById('icon-chevron-left');
  const iconRight = document.getElementById('icon-chevron-right');
  const toggleExpandedThemeBtn = document.getElementById('theme-toggle');
  const toggleCollapsedThemeBtn = document.getElementById('theme-toggle-collapsed');
  const navTooltips = document.querySelectorAll('.nav-tooltip');

  function applySidebarState(isExpanded) {
    if (!sidebar) return;

    if (isExpanded) {
      // 1. Container melebar ke w-64
      sidebar.classList.remove('w-20', 'px-3');
      sidebar.classList.add('w-64', 'px-5', 'is-expanded');

      // 2. Tombol toggle floating
      if (sidebarToggleBtn) {
        sidebarToggleBtn.classList.remove('-right-[19px]');
        sidebarToggleBtn.classList.add('-right-[27px]');
      }

      // 3. Switcher Theme
      if (toggleExpandedThemeBtn) {
        toggleExpandedThemeBtn.classList.remove('hidden');
        toggleExpandedThemeBtn.classList.add('flex');
      }
      if (toggleCollapsedThemeBtn) {
        toggleCollapsedThemeBtn.classList.add('hidden');
      }

      // 4. Chevron & Tooltip
      if (iconLeft && iconRight) {
        iconLeft.classList.remove('hidden');
        iconRight.classList.add('hidden');
      }

      navTooltips.forEach(t => t.classList.add('hidden'));
      if (toggleTooltip) toggleTooltip.textContent = 'Tutup Sidebar';

    } else {
      // 1. Container menguncup ke w-20
      sidebar.classList.remove('w-64', 'px-5', 'is-expanded');
      sidebar.classList.add('w-20', 'px-3');

      // 2. Tombol toggle floating
      if (sidebarToggleBtn) {
        sidebarToggleBtn.classList.remove('-right-[27px]');
        sidebarToggleBtn.classList.add('-right-[19px]');
      }

      // 3. Switcher Theme
      if (toggleExpandedThemeBtn) {
        toggleExpandedThemeBtn.classList.add('hidden');
        toggleExpandedThemeBtn.classList.remove('flex');
      }
      if (toggleCollapsedThemeBtn) {
        toggleCollapsedThemeBtn.classList.remove('hidden');
      }

      // 4. Chevron & Tooltip
      if (iconLeft && iconRight) {
        iconLeft.classList.add('hidden');
        iconRight.classList.remove('hidden');
      }

      navTooltips.forEach(t => t.classList.remove('hidden'));
      if (toggleTooltip) toggleTooltip.textContent = 'Buka Sidebar';
    }
  }

  // Set State Awal saat Halaman Dimuat
  const isSavedExpanded = localStorage.getItem('bi-sidebar') === 'expanded';
  applySidebarState(isSavedExpanded);

  // Listener Toggle Click
  if (sidebarToggleBtn && sidebar) {
    sidebarToggleBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const currentlyExpanded = sidebar.classList.contains('is-expanded');
      const nextState = !currentlyExpanded;

      localStorage.setItem('bi-sidebar', nextState ? 'expanded' : 'collapsed');
      applySidebarState(nextState);
    });
  }

  // ==========================================
  // MODUL 2: MODAL "ATUR JADWAL GURU TAMU"
  // ==========================================
  const modalJadwal = document.getElementById('modal-jadwal');
  const modalJadwalBackdrop = document.getElementById('modal-backdrop');
  const modalJadwalContent = document.getElementById('modal-content');

  document.body.addEventListener('click', (e) => {
    const openBtn = e.target.closest('.btn-open-modal');
    if (openBtn) {
      openModal(modalJadwal, modalJadwalBackdrop, modalJadwalContent);
      return;
    }
    if (!modalJadwal) return;
    if (e.target.closest('#close-modal, #cancel-modal') || e.target === modalJadwalBackdrop) {
      closeModalGeneric(modalJadwal, modalJadwalBackdrop, modalJadwalContent);
    }
  });

  // ==========================================
  // MODUL 3: DROPDOWN PROFIL
  // ==========================================
  const profileBtn = document.getElementById('profile-btn');
  const profileDropdown = document.getElementById('profile-dropdown');

  if (profileBtn && profileDropdown) {
    profileBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      profileDropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
      if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.add('hidden');
      }
    });
  }

  // ==========================================
  // MODUL 4: DASHBOARD - FILTER STAT CARD & TABEL PRESENSI
  // ==========================================
  const statCardsDashboard = document.querySelectorAll('[data-filter]');
  const dashboardRows = () => document.querySelectorAll('tbody tr[data-presensi]');

  function applyPresensiFilter(value) {
    dashboardRows().forEach(row => {
      row.style.display = (!value || row.dataset.presensi === value) ? '' : 'none';
    });
  }

  if (statCardsDashboard.length) {
    statCardsDashboard.forEach(card => {
      card.addEventListener('click', () => {
        const alreadyActive = card.classList.contains('clay-stat-active');
        statCardsDashboard.forEach(c => c.classList.remove('clay-stat-active'));

        if (alreadyActive) {
          applyPresensiFilter('');
          return;
        }
        card.classList.add('clay-stat-active');
        applyPresensiFilter(card.dataset.filter);
      });
    });
  }

  const filterStatusGuru = document.getElementById('filter-status-guru');
  if (filterStatusGuru) {
    filterStatusGuru.addEventListener('change', (e) => {
      const value = e.target.value;
      dashboardRows().forEach(row => {
        row.style.display = (!value || row.dataset.status === value) ? '' : 'none';
      });
    });
  }

  const searchGuru = document.getElementById('search-guru');
  if (searchGuru) {
    searchGuru.addEventListener('input', (e) => {
      const keyword = e.target.value.toLowerCase();
      dashboardRows().forEach(row => {
        const nama = row.querySelector('td span.font-bold')?.textContent.toLowerCase() || '';
        row.style.display = nama.includes(keyword) ? '' : 'none';
      });
    });
  }

  // ==========================================
  // MODUL 5: DATA GURU - MODAL TAMBAH, KARTU, & HAPUS
  // ==========================================
  const modalTambah = document.getElementById('modal-tambah-guru');
  const modalTambahBackdrop = document.getElementById('modal-tambah-backdrop');
  const modalTambahContent = document.getElementById('modal-tambah-content');
  const btnOpenTambah = document.querySelector('.btn-open-modal-tambah');

  if (btnOpenTambah) {
    btnOpenTambah.addEventListener('click', () => {
      openModal(modalTambah, modalTambahBackdrop, modalTambahContent);
    });
  }

  document.body.addEventListener('click', (e) => {
    if (modalTambah && (e.target.closest('#close-modal-tambah, #cancel-modal-tambah') || e.target === modalTambahBackdrop)) {
      closeModalGeneric(modalTambah, modalTambahBackdrop, modalTambahContent);
    }
  });

  const inputStatusGuru = document.getElementById('input-status-guru');
  const wrapJamTetap = document.getElementById('wrap-jam-tetap');
  const wrapInfoTamu = document.getElementById('wrap-info-tamu');
  if (inputStatusGuru) {
    inputStatusGuru.addEventListener('change', (e) => {
      const isTetap = e.target.value === 'tetap';
      wrapJamTetap?.classList.toggle('hidden', !isTetap);
      wrapInfoTamu?.classList.toggle('hidden', isTetap);
    });
  }

  // Modal Kartu Profil
  const modalKartu = document.getElementById('modal-kartu');
  const modalKartuBackdrop = document.getElementById('modal-kartu-backdrop');
  const modalKartuContent = document.getElementById('modal-kartu-content');

  document.body.addEventListener('click', (e) => {
    const btnKartu = e.target.closest('.btn-open-kartu');
    if (btnKartu) {
      const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '-'; };
      const avatarEl = document.getElementById('kartu-avatar');
      if (avatarEl) avatarEl.src = btnKartu.dataset.avatar || '';
      setText('kartu-nama', btnKartu.dataset.nama);
      setText('kartu-nip', btnKartu.dataset.nip);
      setText('kartu-status', btnKartu.dataset.statusLabel);
      setText('kartu-mapel', btnKartu.dataset.mapel);
      setText('kartu-jam-masuk', btnKartu.dataset.jamMasuk);
      setText('kartu-jam-pulang', btnKartu.dataset.jamPulang);
      openModal(modalKartu, modalKartuBackdrop, modalKartuContent);
      return;
    }
    if (modalKartu && (e.target.closest('#close-modal-kartu') || e.target === modalKartuBackdrop)) {
      closeModalGeneric(modalKartu, modalKartuBackdrop, modalKartuContent);
    }
  });

  // Modal Hapus Guru
  const modalHapus = document.getElementById('modal-hapus');
  const modalHapusBackdrop = document.getElementById('modal-hapus-backdrop');
  const modalHapusContent = document.getElementById('modal-hapus-content');
  const namaHapusEl = document.getElementById('hapus-nama-guru');
  let rowToDelete = null;

  document.body.addEventListener('click', (e) => {
    const btnHapus = e.target.closest('.btn-open-hapus');
    if (btnHapus) {
      rowToDelete = btnHapus.closest('tr');
      if (namaHapusEl) namaHapusEl.textContent = btnHapus.dataset.nama || '-';
      openModal(modalHapus, modalHapusBackdrop, modalHapusContent);
      return;
    }
    if (modalHapus && (e.target.closest('#close-modal-hapus, #cancel-modal-hapus') || e.target === modalHapusBackdrop)) {
      rowToDelete = null;
      closeModalGeneric(modalHapus, modalHapusBackdrop, modalHapusContent);
    }
  });

  document.getElementById('confirm-modal-hapus')?.addEventListener('click', () => {
    if (rowToDelete) {
      rowToDelete.remove();
      rowToDelete = null;
    }
    closeModalGeneric(modalHapus, modalHapusBackdrop, modalHapusContent);
  });

  // Filter & Search Halaman Guru
  const filterStatusGuruPage = document.getElementById('filter-status-guru-page');
  if (filterStatusGuruPage) {
    filterStatusGuruPage.addEventListener('change', (e) => {
      const value = e.target.value;
      document.querySelectorAll('tbody tr[data-status]').forEach(row => {
        row.style.display = (!value || row.dataset.status === value) ? '' : 'none';
      });
    });
  }

  const searchGuruPage = document.getElementById('search-guru-page');
  if (searchGuruPage) {
    searchGuruPage.addEventListener('input', (e) => {
      const keyword = e.target.value.toLowerCase();
      document.querySelectorAll('tbody tr[data-status]').forEach(row => {
        const nama = (row.dataset.nama || '').toLowerCase();
        const mapel = (row.dataset.mapel || '').toLowerCase();
        row.style.display = (nama.includes(keyword) || mapel.includes(keyword)) ? '' : 'none';
      });
    });
  }

  // ==========================================
  // MODUL 6: KALENDER DINAMIS & INDIKATOR AGENDA
  // ==========================================
  const monthSelect = document.getElementById('cal-month-select');
  const yearSelect = document.getElementById('cal-year-select');
  const calDaysGrid = document.getElementById('cal-days-grid');
  const calPrevBtn = document.getElementById('cal-prev');
  const calNextBtn = document.getElementById('cal-next');

  let currentDate = new Date();

  function getAgendaMap() {
    const agendaItems = document.querySelectorAll('#agenda-list [data-tanggal]');
    const map = {};
    agendaItems.forEach(item => {
      const date = item.dataset.tanggal;
      const kategori = item.dataset.kategori;
      if (date) {
        let dotColor = 'bg-emerald-500';
        if (kategori === 'nasional') dotColor = 'bg-brand-primary';
        if (kategori === 'penting') dotColor = 'bg-rose-500';
        map[date] = dotColor;
      }
    });
    return map;
  }

  function populateYearDropdown() {
    if (!yearSelect) return;
    const currentYear = new Date().getFullYear();
    const startYear = currentYear - 5;
    const endYear = currentYear + 5;

    yearSelect.innerHTML = '';
    for (let y = startYear; y <= endYear; y++) {
      const opt = document.createElement('option');
      opt.value = y;
      opt.textContent = y;
      if (y === currentDate.getFullYear()) opt.selected = true;
      yearSelect.appendChild(opt);
    }
  }

  function renderCalendar(date) {
    if (!calDaysGrid) return;
    const year = date.getFullYear();
    const month = date.getMonth();
    const agendaMap = getAgendaMap();

    if (monthSelect) monthSelect.value = month;
    if (yearSelect) yearSelect.value = year;
    calDaysGrid.innerHTML = '';

    const firstDayIndex = new Date(year, month, 1).getDay();
    const lastDate = new Date(year, month + 1, 0).getDate();
    const prevLastDate = new Date(year, month, 0).getDate();

    // Tanggal Bulan Sebelumnya
    for (let x = firstDayIndex; x > 0; x--) {
      const dayNum = prevLastDate - x + 1;
      calDaysGrid.innerHTML += `<span class="h-9 sm:h-10 flex items-center justify-center text-slate-300 dark:text-slate-600 pointer-events-none select-none text-xs sm:text-sm font-semibold">${dayNum}</span>`;
    }

    // Tanggal Bulan Sekarang
    const today = new Date();
    for (let i = 1; i <= lastDate; i++) {
      const monthFormatted = String(month + 1).padStart(2, '0');
      const dayFormatted = String(i).padStart(2, '0');
      const dateAttr = `${year}-${monthFormatted}-${dayFormatted}`;

      const isToday = (i === today.getDate() && month === today.getMonth() && year === today.getFullYear());
      const dotColor = agendaMap[dateAttr];

      let cellClass = "h-9 sm:h-10 cursor-pointer transition-all select-none flex flex-col items-center justify-center font-bold text-xs sm:text-sm rounded-xl ";

      if (isToday) {
        cellClass += "clay-block-ocean text-white shadow-md font-black z-10";
      } else {
        cellClass += "text-slate-700 dark:text-slate-200 hover:bg-white/60 dark:hover:bg-white/10 hover:text-brand-primary";
      }

      const dotMarkup = dotColor
        ? `<span class="w-1.5 h-1.5 rounded-full ${isToday ? 'bg-white' : dotColor} inline-block -mt-0.5"></span>`
        : '';

      calDaysGrid.innerHTML += `
        <div data-date="${dateAttr}" class="${cellClass}">
          <span>${i}</span>
          ${dotMarkup}
        </div>`;
    }

    // Tanggal Bulan Seterusnya
    const totalCells = firstDayIndex + lastDate;
    const nextDays = (totalCells % 7 === 0) ? 0 : 7 - (totalCells % 7);
    for (let j = 1; j <= nextDays; j++) {
      calDaysGrid.innerHTML += `<span class="h-9 sm:h-10 flex items-center justify-center text-slate-300 dark:text-slate-600 pointer-events-none select-none text-xs sm:text-sm font-semibold">${j}</span>`;
    }
  }

  if (monthSelect && yearSelect && calDaysGrid) {
    populateYearDropdown();
    renderCalendar(currentDate);

    monthSelect.addEventListener('change', (e) => {
      currentDate.setMonth(parseInt(e.target.value));
      renderCalendar(currentDate);
    });

    yearSelect.addEventListener('change', (e) => {
      currentDate.setFullYear(parseInt(e.target.value));
      renderCalendar(currentDate);
    });

    calPrevBtn?.addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() - 1);
      renderCalendar(currentDate);
    });

    calNextBtn?.addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() + 1);
      renderCalendar(currentDate);
    });
  }

  // ==========================================
  // MODUL 7: AGENDA CRUD (SINGLE DATE)
  // ==========================================
  const modalAgenda = document.getElementById('modal-agenda');
  const modalAgendaContent = document.getElementById('modal-agenda-content');
  const modalAgendaBackdrop = document.getElementById('modal-agenda-backdrop');
  const formAgenda = document.getElementById('form-agenda');
  const agendaList = document.getElementById('agenda-list');

  const modalHapusAgenda = document.getElementById('modal-hapus-agenda');
  const modalHapusAgendaContent = document.getElementById('modal-hapus-agenda-content');
  const modalHapusAgendaBackdrop = document.getElementById('modal-hapus-agenda-backdrop');

  let activeAgendaItemToDelete = null;

  document.querySelectorAll('.btn-open-modal-agenda').forEach(btn => {
    btn.addEventListener('click', () => {
      const modalTitle = document.getElementById('agenda-modal-title');
      if (modalTitle) modalTitle.textContent = 'Tambah Agenda Sekolah';
      if (formAgenda) formAgenda.reset();
      const agendaIdInput = document.getElementById('agenda-id');
      if (agendaIdInput) agendaIdInput.value = '';
      openModal(modalAgenda, modalAgendaBackdrop, modalAgendaContent);
    });
  });

  agendaList?.addEventListener('click', (e) => {
    const editBtn = e.target.closest('.btn-edit-agenda');
    if (editBtn) {
      const item = editBtn.closest('[data-agenda-id]');
      const modalTitle = document.getElementById('agenda-modal-title');
      if (modalTitle) modalTitle.textContent = 'Edit Agenda Sekolah';

      document.getElementById('agenda-id').value = item.dataset.agendaId;
      document.getElementById('agenda-judul').value = item.dataset.judul;
      document.getElementById('agenda-tanggal').value = item.dataset.tanggal;
      document.getElementById('agenda-jam').value = item.dataset.jam;
      document.getElementById('agenda-kategori').value = item.dataset.kategori;

      openModal(modalAgenda, modalAgendaBackdrop, modalAgendaContent);
      return;
    }

    const deleteBtn = e.target.closest('.btn-delete-agenda');
    if (deleteBtn) {
      activeAgendaItemToDelete = deleteBtn.closest('[data-agenda-id]');
      const judulEl = document.getElementById('hapus-judul-agenda');
      if (judulEl) judulEl.textContent = activeAgendaItemToDelete.dataset.judul;
      openModal(modalHapusAgenda, modalHapusAgendaBackdrop, modalHapusAgendaContent);
    }
  });

  ['close-modal-agenda', 'cancel-modal-agenda', 'modal-agenda-backdrop'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', () => {
      closeModalGeneric(modalAgenda, modalAgendaBackdrop, modalAgendaContent);
    });
  });

  formAgenda?.addEventListener('submit', (e) => {
    e.preventDefault();
    const id = document.getElementById('agenda-id').value;
    const judul = document.getElementById('agenda-judul').value;
    const tanggal = document.getElementById('agenda-tanggal').value;
    const jam = document.getElementById('agenda-jam').value;
    const kategori = document.getElementById('agenda-kategori').value;

    const dateFormatted = new Date(tanggal).toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
    const dateDisplay = `${dateFormatted} • ${jam}`;

    let dotColor = 'bg-emerald-500';
    if (kategori === 'nasional') dotColor = 'bg-brand-primary';
    if (kategori === 'penting') dotColor = 'bg-rose-500';

    if (id) {
      const item = agendaList.querySelector(`[data-agenda-id="${id}"]`);
      if (item) {
        item.dataset.judul = judul;
        item.dataset.tanggal = tanggal;
        item.dataset.jam = jam;
        item.dataset.kategori = kategori;
        item.querySelector('p').textContent = judul;
        item.querySelector('span').textContent = dateDisplay;
        item.querySelector('.rounded-full').className = `w-3 h-3 rounded-full ${dotColor} mt-1 shrink-0`;
      }
    } else {
      const newId = Date.now();
      const newItemHTML = `
        <div class="p-3.5 rounded-2xl clay-inset flex items-start justify-between gap-3 group transition-all"
             data-agenda-id="${newId}"
             data-judul="${judul}"
             data-tanggal="${tanggal}"
             data-jam="${jam}"
             data-kategori="${kategori}">
          <div class="flex items-start gap-3 flex-1 min-w-0">
            <div class="w-3 h-3 rounded-full ${dotColor} mt-1 shrink-0"></div>
            <div class="min-w-0">
              <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">${judul}</p>
              <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5 block">${dateDisplay}</span>
            </div>
          </div>
          <div class="flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity shrink-0">
            <button title="Edit Agenda" class="btn-edit-agenda w-7 h-7 clay-card rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-brand-primary clay-btn transition-colors cursor-pointer">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
            </button>
            <button title="Hapus Agenda" class="btn-delete-agenda w-7 h-7 clay-card rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-rose-600 clay-btn transition-colors cursor-pointer">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
          </div>
        </div>`;
      agendaList.insertAdjacentHTML('beforeend', newItemHTML);
    }

    renderCalendar(currentDate);
    closeModalGeneric(modalAgenda, modalAgendaBackdrop, modalAgendaContent);
  });

  document.getElementById('confirm-modal-hapus-agenda')?.addEventListener('click', () => {
    if (activeAgendaItemToDelete) {
      activeAgendaItemToDelete.remove();
      activeAgendaItemToDelete = null;
      renderCalendar(currentDate);
      closeModalGeneric(modalHapusAgenda, modalHapusAgendaBackdrop, modalHapusAgendaContent);
    }
  });

  ['cancel-modal-hapus-agenda', 'modal-hapus-agenda-backdrop'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', () => {
      closeModalGeneric(modalHapusAgenda, modalHapusAgendaBackdrop, modalHapusAgendaContent);
    });
  });

  // ==========================================
  // MODUL 8: FILTER INTERAKTIF STAT CARD REKAP (admin-rekap.php)
  // ==========================================
  const rekapFilterCards = document.querySelectorAll('[data-rekap-filter]');
  const rekapRows = () => document.querySelectorAll('tbody tr[data-total-menit]');

  if (rekapFilterCards.length > 0) {
    rekapFilterCards.forEach(card => {
      card.addEventListener('click', () => {
        const filterType = card.dataset.rekapFilter;
        const isActive = card.classList.contains('clay-stat-active');

        rekapFilterCards.forEach(c => c.classList.remove('clay-stat-active'));

        if (isActive) {
          rekapRows().forEach(row => row.style.display = '');
          return;
        }

        card.classList.add('clay-stat-active');

        rekapRows().forEach(row => {
          const menitTelat = parseInt(row.dataset.totalMenit || '0');
          const totalAlpha = parseInt(row.dataset.totalAlpha || '0');

          if (filterType === 'semua') {
            row.style.display = '';
          } else if (filterType === 'telat') {
            row.style.display = (menitTelat > 0) ? '' : 'none';
          } else if (filterType === 'alpha') {
            row.style.display = (totalAlpha > 0) ? '' : 'none';
          }
  // ==========================================
  // MODUL 14: SMOOTH PAGE TRANSITIONS
  // Tujuan: pindah halaman terasa mengalir (fade + progress),
  // bukan lompatan kaku. Dihormati prefers-reduced-motion.
  // ==========================================
  (function initPageTransitions() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const bar = document.createElement('div');
    bar.id = 'page-progress';
    bar.setAttribute('aria-hidden', 'true');
    document.body.appendChild(bar);

    const cards = document.querySelectorAll('main .clay-card, aside .clay-card');
    if (cards.length) {
      document.body.classList.add('has-reveal');
      cards.forEach((el, i) => {
        el.classList.add('reveal');
        el.style.transitionDelay = Math.min(i * 45, 320) + 'ms';
      });
      requestAnimationFrame(() => requestAnimationFrame(() => {
        document.body.classList.add('reveal-on');
      }));
    }

    let navigating = false;
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a[href]');
      if (!a || e.defaultPrevented || e.button !== 0) return;
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      const href = a.getAttribute('href');
      if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
      if (a.target === '_blank' || a.hasAttribute('download')) return;
      if (a.hasAttribute('data-no-transition')) return;
      let url;
      try {
        url = new URL(href, location.href);
      } catch {
        return;
      }
      if (url.origin !== location.origin) return;
      if (url.pathname === location.pathname && url.search === location.search) return;
      e.preventDefault();
      if (navigating) return;
      navigating = true;
      bar.classList.add('is-active');
      document.body.classList.add('page-leave');
      document.body.classList.remove('reveal-on');
      setTimeout(() => {
        location.href = url.href;
      }, 170);
    });

    window.addEventListener('pageshow', (e) => {
      if (e.persisted) {
        navigating = false;
        document.body.classList.remove('page-leave');
        bar.classList.remove('is-active');
      }
    });
  })();

});
      });
    });
  }

  // Search Nama Guru khusus di Halaman Rekap
  const searchRekap = document.getElementById('search-rekap');
  if (searchRekap) {
    searchRekap.addEventListener('input', (e) => {
      const keyword = e.target.value.toLowerCase();
      document.querySelectorAll('tbody tr[data-nama]').forEach(row => {
        const nama = (row.dataset.nama || '').toLowerCase();
        row.style.display = nama.includes(keyword) ? '' : 'none';
      });
    });
  }

  // ==========================================
  // MODUL 9: REALTIME CLOCK (#realtime-clock)
  // ==========================================
  const clockElement = document.getElementById('realtime-clock');
  if (clockElement) {
    function updateRealtimeClock() {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      clockElement.textContent = `${hours}:${minutes}:${seconds} WIB`;
    }
    updateRealtimeClock();
    setInterval(updateRealtimeClock, 1000);
  }

  // ==========================================
  // MODUL 10: TOGGLE PASSWORD VISIBILITY (auth/login.php)
  // ==========================================
  window.togglePasswordVisibility = function () {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    if (!passwordInput || !eyeIcon) return;

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      eyeIcon.innerHTML = `
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.02 10.02 0 013.982-.82c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18" />
      `;
    } else {
      passwordInput.type = 'password';
      eyeIcon.innerHTML = `
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
      `;
    }
  };

  // ==========================================
  // MODUL 11: KONFIRMASI EXPORT PDF (app/guru/rekap-nilai.php)
  // Modal in-app (bukan popup browser) sebelum buka export.
  // ==========================================
  const modalExportPdf = document.getElementById('modal-export-pdf');
  const modalExportPdfContent = document.getElementById('modal-export-pdf-content');

  window.openExportConfirm = function () {
    if (!modalExportPdf) return;
    modalExportPdf.classList.remove('hidden');
    requestAnimationFrame(() => requestAnimationFrame(() => {
      modalExportPdfContent?.classList.remove('opacity-0', 'scale-95');
      modalExportPdfContent?.classList.add('opacity-100', 'scale-100');
    }));
    document.getElementById('cancel-export-pdf')?.focus();
  };

  window.closeExportConfirm = function () {
    if (!modalExportPdf) return;
    modalExportPdfContent?.classList.remove('opacity-100', 'scale-100');
    modalExportPdfContent?.classList.add('opacity-0', 'scale-95');
    setTimeout(() => modalExportPdf.classList.add('hidden'), 200);
  };

  document.getElementById('cancel-export-pdf')?.addEventListener('click', () => {
    window.closeExportConfirm();
  });

  modalExportPdf?.addEventListener('click', (e) => {
    if (e.target === modalExportPdf) window.closeExportConfirm();
  });

  document.getElementById('confirm-export-pdf')?.addEventListener('click', () => {
    window.closeExportConfirm();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modalExportPdf && !modalExportPdf.classList.contains('hidden')) {
      window.closeExportConfirm();
    }
  });

  // ==========================================
  // MODUL 11B: KONFIRMASI HAPUS DATA (app/admin/*)
  // Intersep form.js-confirm-hapus -> modal in-app (bukan
  // confirm() browser). Nama item dari data-nama.
  // ==========================================
  (function initHapusConfirm() {
    const modal = document.getElementById('modal-hapus-data');
    const content = document.getElementById('modal-hapus-data-content');
    const namaEl = document.getElementById('hapus-data-nama');
    const btnCancel = document.getElementById('cancel-hapus-data');
    const btnConfirm = document.getElementById('confirm-hapus-data');
    if (!modal || !btnConfirm) return;

    let formAktif = null;

    function buka(form) {
      formAktif = form;
      const nama = form.dataset.nama || '';
      if (namaEl) {
        namaEl.textContent = nama;
        namaEl.classList.toggle('hidden', !nama);
      }
      modal.classList.remove('hidden');
      requestAnimationFrame(() => requestAnimationFrame(() => {
        content?.classList.remove('opacity-0', 'scale-95');
        content?.classList.add('opacity-100', 'scale-100');
      }));
      btnCancel?.focus();
    }

    function tutup() {
      content?.classList.remove('opacity-100', 'scale-100');
      content?.classList.add('opacity-0', 'scale-95');
      setTimeout(() => modal.classList.add('hidden'), 200);
      formAktif = null;
    }

    document.addEventListener('submit', (e) => {
      const form = e.target.closest?.('form.js-confirm-hapus');
      if (!form) return;
      if (form.dataset.terkonfirmasi === '1') {
        delete form.dataset.terkonfirmasi;
        return;
      }
      e.preventDefault();
      buka(form);
    });

    btnConfirm.addEventListener('click', () => {
      if (!formAktif) return;
      const form = formAktif;
      tutup();
      form.dataset.terkonfirmasi = '1';
      if (typeof form.requestSubmit === 'function') form.requestSubmit();
      else form.submit();
    });

    btnCancel?.addEventListener('click', tutup);
    modal.addEventListener('click', (e) => {
      if (e.target === modal) tutup();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) tutup();
    });
  })();

  // ==========================================
  // MODUL 12: ACCORDION & MULTI-SEARCH RAPOR (app/siswa/rapor.php)
  // ==========================================
  window.toggleMapelDetail = function (index) {
    const detail = document.getElementById(`sk-detail-${index}`);
    const arrow = document.getElementById(`arrow-icon-${index}`);

    if (detail && arrow) {
      detail.classList.toggle('hidden');
      arrow.classList.toggle('rotate-180');
    }
  };

  window.filterRapor = function () {
    const inputEl = document.getElementById('search-mapel-input');
    if (!inputEl) return;

    const query = inputEl.value.toLowerCase().trim();
    const items = document.querySelectorAll('.mapel-card-item');
    let hasMatch = false;

    items.forEach((item, index) => {
      const searchContent = item.getAttribute('data-search') || '';
      const detail = document.getElementById(`sk-detail-${index}`);
      const arrow = document.getElementById(`arrow-icon-${index}`);

      if (searchContent.includes(query)) {
        item.style.display = 'block';
        hasMatch = true;

        if (detail && arrow) {
          if (query.length > 2) {
            detail.classList.remove('hidden');
            arrow.classList.add('rotate-180');
          } else if (query.length === 0) {
            detail.classList.add('hidden');
            arrow.classList.remove('rotate-180');
          }
        }
      } else {
        item.style.display = 'none';
      }
    });

    const noResult = document.getElementById('no-result-msg');
    if (noResult) {
      if (hasMatch) {
        noResult.classList.add('hidden');
      } else {
        noResult.classList.remove('hidden');
      }
    }
  };

  // ==========================================
  // MODUL 13: STEPPER & RECALCULATE GRADE (app/guru/input-nilai.php)
  // ==========================================
  window.updateNilai = function (btn, step) {
    const input = btn.parentElement.querySelector('input[type="number"]');
    if (!input) return;

    let val = parseInt(input.value) || 0;
    val = Math.min(100, Math.max(0, val + step));
    input.value = val;
    window.recalculateGrade(input);
  };

  window.recalculateGrade = function (input) {
    const tr = input.closest('tr');
    if (!tr) return;

    const badge = tr.querySelector('.grade-badge');
    if (!badge) return;

    // Nilai dikosongkan = siswa belum dinilai.
    if (String(input.value).trim() === '') {
      badge.textContent = '-';
      badge.className = 'grade-badge px-3.5 py-1.5 clay-inset text-slate-500 rounded-xl text-xs font-black inline-block shadow-xs transition-all';
      return;
    }

    const val = parseInt(input.value) || 0;

    let huruf = 'D';
    let styleClass = 'clay-block-alpha text-rose-950';

    if (val >= 88) {
      huruf = 'A';
      styleClass = 'clay-block-mint text-emerald-950';
    } else if (val >= 78) {
      huruf = 'B';
      styleClass = 'clay-block-mint text-emerald-950';
    } else if (val >= 70) {
      huruf = 'C';
      styleClass = 'clay-block-amber text-amber-950';
    } else {
      huruf = 'D';
      styleClass = 'clay-block-alpha text-rose-950';
    }

    badge.textContent = huruf;
    badge.className = `grade-badge px-3.5 py-1.5 ${styleClass} rounded-xl text-xs font-black inline-block shadow-xs transition-all`;
  };

});