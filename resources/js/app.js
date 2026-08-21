import Swal from 'sweetalert2';

window.Swal = Swal;

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    },
});

window.Toast = Toast;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-delete').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const form = btn.closest('form');
            Swal.fire({
                title: '¿Está seguro?',
                text: 'Si elimina el registro no podrá volver atrás.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#df6403',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
