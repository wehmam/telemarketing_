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

$('.sLastDeposit').on('change', function() {
    window.LaravelDataTables['transactions-table'].ajax.reload();
})

// Kirim data filter ke server sebelum AJAX
dt.on('preXhr.dt', function(e, settings, data) {
    data.s_nama_rekening = $('#sNamaRekening').val();
    data.s_username = $('#sUsername').val();
    data.s_phone = $('#sPhone').val();
    data.s_status = $('.sStatus:checked').val();
    data.s_last_deposit = $('.sLastDeposit').val();
});


const btnExportExcel = document.getElementById('btnExportExcel');
if (btnExportExcel) {
    document.getElementById('btnExportExcel').addEventListener('click', function (e) {
        e.preventDefault();
        showLoadPage();

        let params = {
            s_nama_rekening: $('#sNamaRekening').val(),
            s_username: $('#sUsername').val(),
            s_phone: $('#sPhone').val(),
            s_status: $('.sStatus:checked').val(),
            s_last_deposit: $('.sLastDeposit').val(),
        };
        let query = $.param(params);
        let url = '/transactions/export/excel?' + query;

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => {
            // Try to read filename from Content-Disposition header
            const disposition = response.headers.get('Content-Disposition');
            let filename = "transactions.xlsx";
            if (disposition && disposition.indexOf('filename=') !== -1) {
                filename = disposition.split('filename=')[1].replace(/"/g, '');
            }
            return response.blob().then(blob => ({ blob, filename }));
        })
        .then(({ blob, filename }) => {
            hideLoadPage();

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        })
        .catch(() => {
            hideLoadPage();
            Swal.fire("Error", "Failed to export file.", "error");
        });
    });
}

// ===== Modal Show Event =====
const modal = document.querySelector('#kt_modal_add_transactions');
if (modal) {
    modal.addEventListener('show.bs.modal', (e) => {
        // Livewire.emit('modal.show.role_name', e.relatedTarget.getAttribute('data-role-id'));
    });
}

$(document).ready(function() {
    $('#kt_modal_import_transaction_form').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        let url = '/transactions/import';

        showLoadPage();
        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                hideLoadPage();
                Swal.fire(
                    response.status ? "Success" : "Error",
                    response.message,
                    response.status ? "success" : "error"
                );
                $('#kt_modal_add_transactions').modal('hide');
                $('#kt_modal_import_transaction_form')[0].reset();
                window.LaravelDataTables['transactions-table'].ajax.reload();
            },
            error: function (xhr) {
                hideLoadPage();
                if (xhr.status === 422) {
                    let message = xhr.responseJSON.message;
                    Swal.fire("Validation Error", message, "error");
                } else {
                    Swal.fire("Error", message ?? "Something went wrong", "error");
                }
            }
        })

    });
})

document.querySelectorAll('[data-kt-action="follow_up_row"]').forEach(function (e) {
    e.addEventListener('click', function() {
        const transactionId = this.getAttribute('data-kt-transaction-id');
        showLoadPage();
        fetch(`/transactions/${transactionId}/follow-up`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            hideLoadPage();
            if(data.status) {
                if (data.data.redirectUrl) {
                    window.open(data.data.redirectUrl, '_blank');
                    window.LaravelDataTables['transactions-table'].ajax.reload();
                    return
                } else {
                    Swal.fire({
                            text: "Failed to get redirect URL WA.ME",
                            icon: 'error',
                            confirmButtonText: 'OK',
                            customClass: { confirmButton: 'btn btn-primary' }
                    });
                    return
                }
            }

            Swal.fire({
                text: data.message ,
                icon: 'error',
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
    })
})

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

