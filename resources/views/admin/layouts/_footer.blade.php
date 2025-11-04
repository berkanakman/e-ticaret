<footer class="mt-auto py-3 border-top bg-body-tertiary">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="text-muted small">
            © {{ date('Y') }} Admin Panel — Tüm Hakları Saklıdır.
        </div>

        <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
            {{-- Tema değiştirme butonu --}}
            <button id="themeToggle" class="btn btn-sm btn-outline-secondary">
                Tema Değiştir
            </button>

            {{-- Versiyon bilgisi (opsiyonel) --}}
            <span class="text-muted small">v1.0.0</span>
        </div>
    </div>
</footer>

{{-- Tema toggle script --}}
@push('scripts')
    <script>
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const html = document.documentElement;
            const current = html.getAttribute('data-bs-theme') || 'light';
            html.setAttribute('data-bs-theme', current === 'light' ? 'dark' : 'light');
        });
    </script>
@endpush
