// Initialize KTMenu
KTMenu.init();

// SETUP CSRF
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
});

// ===== Search Input =====
// const searchInput = document.getElementById('mySearchInput');
// if (searchInput) {
//     searchInput.addEventListener('keyup', function () {
//         window.LaravelDataTables['member-transactions-table'].search(this.value).draw();
//     });
// }

function debounce(func, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

const dt = window.LaravelDataTables['member-transactions-table'];

const searchInput = document.getElementById('mySearchInput');
if (searchInput) {
    searchInput.addEventListener('keyup', debounce(function () {
        dt.search(this.value).draw();
    }, 1000));
}

// nama rekening
$('#sNamaRekening').on('keyup', debounce(function() {
    dt.ajax.reload();
}, 500));

// username
$('#sUsername').on('keyup', debounce(function() {
    dt.ajax.reload();
}, 500));

// phone
$('#sPhone').on('keyup', debounce(function() {
    dt.ajax.reload();
}, 500));

// status
$('#sTeam').on('change', function() {
    window.LaravelDataTables['member-transactions-table'].ajax.reload();
});

$('#sMarketing').on('change', function() {
    dt.ajax.reload();
});

// $('#periodeLastDeposit').on('change', function() {
//     window.LaravelDataTables['member-transactions-table'].ajax.reload();
// })

// Kirim data filter ke server sebelum AJAX
dt.on('preXhr.dt', function(e, settings, data) {
    data.s_nama_rekening = $('#sNamaRekening').val();
    data.s_username = $('#sUsername').val();
    data.s_phone = $('#sPhone').val();
    data.s_team = $('#sTeam').val();
    data.s_marketing = $('#sMarketing').val();
    // data.s_last_deposit = $('#periodeLastDeposit').val();
});

$('#statusFilter').on('change', function() {
    window.LaravelDataTables['member-transactions-table'].ajax.reload();
});

// ===== Modal Show Event =====
const modal = document.querySelector('#kt_modal_assign_transactions');
if (modal) {
    modal.addEventListener('show.bs.modal', (e) => {
        // Livewire.emit('modal.show.role_name', e.relatedTarget.getAttribute('data-role-id'));
    });
}


$(document).ready(function() {
    $('#kt_modal_assign_transaction_form').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        let url = '/transactions-assign';

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
                $('#kt_modal_assign_transactions').modal('hide');
                $('#kt_modal_assign_transaction_form')[0].reset();
                window.LaravelDataTables['member-transactions-table'].ajax.reload();
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
