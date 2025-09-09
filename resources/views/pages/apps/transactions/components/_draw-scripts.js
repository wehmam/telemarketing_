// Initialize KTMenu
KTMenu.init();

// SETUP CSRF
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
});

// ===== Search Input =====
const searchInput = document.getElementById('mySearchInput');
if (searchInput) {
    // searchInput.addEventListener('keyup', function () {
    //     window.LaravelDataTables['transactions-table'].search(this.value).draw();
    // });
    let debounceTimer;
    searchInput.addEventListener('keyup', function () {
        clearTimeout(debounceTimer); // cancel previous timer
        debounceTimer = setTimeout(() => {
            window.LaravelDataTables['transactions-table'].search(this.value).draw();
        }, 300); // delay in milliseconds (300ms)
    });
}

function debounce(func, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

const dt = window.LaravelDataTables['transactions-table'];

// // nama rekening
$('#sNamaRekening').on('keyup', debounce(function() {
    dt.ajax.reload();
}, 500));

// // username
$('#sUsername').on('keyup', debounce(function() {
    dt.ajax.reload();
}, 500));

// // phone
$('#sPhone').on('keyup', debounce(function() {
    dt.ajax.reload();
}, 500));

// // status
$('.sStatus').on('change', function() {
    window.LaravelDataTables['transactions-table'].ajax.reload();
});

// Kirim data filter ke server sebelum AJAX
dt.on('preXhr.dt', function(e, settings, data) {
    data.s_nama_rekening = $('#sNamaRekening').val();
    data.s_username = $('#sUsername').val();
    data.s_phone = $('#sPhone').val();
    data.s_status = $('.sStatus:checked').val();
});

// $('#statusFilter').on('change', function() {
//     window.LaravelDataTables['transactions-table'].ajax.reload();
// });

// ===== Modal Show Event =====
const modal = document.querySelector('#kt_modal_add_transactions');
if (modal) {
    modal.addEventListener('show.bs.modal', (e) => {
        // Livewire.emit('modal.show.role_name', e.relatedTarget.getAttribute('data-role-id'));
    });
}


// ===== Delete Member =====
document.querySelectorAll('[data-kt-action="delete_row"]').forEach(function (element) {
    element.addEventListener('click', function () {
        const memberId = this.getAttribute('data-kt-member-id');

        Swal.fire({
            text: 'Are you sure you want to remove this member?',
            icon: 'warning',
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                showLoadPage();
                fetch(`/members/${memberId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    hideLoadPage();
                    Swal.fire({
                        text: data.message || (data.status ? 'Member deleted successfully!' : 'Failed to delete member.'),
                        icon: data.status ? 'success' : 'error',
                        confirmButtonText: 'OK',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                    window.LaravelDataTables['transactions-table'].ajax.reload();
                })
                .catch(error => {
                    hideLoadPage();
                    Swal.fire({
                        text: 'Something went wrong!',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                });
            }
        });
    });
});

