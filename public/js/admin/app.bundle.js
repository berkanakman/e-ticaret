// public/js/admin/app.bundle.js

document.addEventListener('DOMContentLoaded', () => {
    console.log('Admin panel JS aktif');

    // Bootstrap global kontrolü
    if (typeof bootstrap === 'undefined') {
        console.warn('Bootstrap JS yüklenemedi. Lütfen CDN bağlantısını kontrol et.');
        return;
    }

    // Tooltip ve Popover başlat
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        new bootstrap.Popover(el);
    });

    // Sidebar toggle örneği (isteğe göre)
    const menuToggle = document.querySelector('[aria-label="Menüyü aç/kapat"]');
    const sidebar = document.querySelector('aside');
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('d-none');
        });
    }

    console.log('Admin panel interaktif bileşenler yüklendi');
});
