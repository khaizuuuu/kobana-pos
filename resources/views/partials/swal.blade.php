<script>
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert2 Toast configuration
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    @if (session('success'))
        Toast.fire({
            icon: 'success',
            title: "{{ session('success') }}"
        });
    @endif

    @if (session('error'))
        Toast.fire({
            icon: 'error',
            title: "{{ session('error') }}"
        });
    @endif

    @if (session('warning'))
        Toast.fire({
            icon: 'warning',
            title: "{{ session('warning') }}"
        });
    @endif

    @if (session('info'))
        Toast.fire({
            icon: 'info',
            title: "{{ session('info') }}"
        });
    @endif

    @if ($errors->any())
        Toast.fire({
            icon: 'error',
            title: "Periksa kembali isian formulir Anda."
        });
    @endif

    // Global Delete & Action Confirmation
    document.addEventListener('click', function(e) {
        const target = e.target.closest('[data-confirm], [data-confirm-delete], .btn-delete');
        if (!target) return;

        // If it's inside a form or is a button/link
        e.preventDefault();
        const form = target.closest('form') || (target.getAttribute('data-confirm-form') ? document.getElementById(target.getAttribute('data-confirm-form')) : null);
        const customMessage = target.getAttribute('data-confirm-message') || 'Data yang dihapus tidak dapat dikembalikan!';
        const customTitle = target.getAttribute('data-confirm-title') || 'Apakah Anda yakin?';
        const confirmButtonText = target.getAttribute('data-confirm-btn') || 'Ya, Hapus!';
        const isDelete = target.hasAttribute('data-confirm-delete') || target.classList.contains('btn-delete') || (form && form.querySelector('input[name="_method"][value="DELETE"]'));

        Swal.fire({
            title: customTitle,
            text: customMessage,
            icon: isDelete ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: isDelete ? '#dc3545' : '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmButtonText,
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                if (form) {
                    form.submit();
                } else if (target.tagName === 'A' && target.href) {
                    window.location.href = target.href;
                }
            }
        });
    });

    // Global helper for showing loading state
    window.showSwalLoading = function(title = 'Memproses Data...', text = 'Mohon tunggu sebentar.') {
        Swal.fire({
            title: title,
            text: text,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    };
});
</script>
