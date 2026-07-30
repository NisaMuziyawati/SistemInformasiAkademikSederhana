// Konfirmasi sebelum menghapus data
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-confirm-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
    });
});
