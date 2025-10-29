// Initialize KTMenu
KTMenu.init();


function debounce(func, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

const dt = window.LaravelDataTables['members-followup-table'];

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
}, 1000));

// phone
$('#sPhone').on('keyup', debounce(function() {
    dt.ajax.reload();
}, 1000));

$('#totalDeposit').on('keyup', debounce(function() {
    dt.ajax.reload();
}, 1000));

$('#sMarketing').on('change', debounce(function() {
    dt.ajax.reload();
}, 1000));

$('#sTeam').on('change', debounce(function() {
    dt.ajax.reload();
}, 1000));

$('#periodeLastDeposit').on('change', debounce(function() {
    dt.ajax.reload();
}, 1000));

$('#periodeFollowUp').on('change', debounce(function() {
    dt.ajax.reload();
}, 1000));

$('#periodeRegister').on('change', debounce(function() {
    dt.ajax.reload();
}, 1000));

$('.sStatus').on('change', debounce(function() {
    dt.ajax.reload();
}, 1000));


// Kirim data filter ke server sebelum AJAX
dt.on('preXhr.dt', function(e, settings, data) {
    // data.s_nama_rekening = $('#sNamaRekening').val();
    data.s_username = $('#sUsername').val();
    data.s_phone = $('#sPhone').val();
    data.s_marketing = $('#sMarketing').val();
    // data.s_team = $('#sTeam').val();
    // data.s_total_deposit = $('#totalDeposit').val().replace(/\./g, '');
    data.s_last_deposit = $('#periodeLastDeposit').val();
    data.s_status = $('.sStatus:checked').val();
    data.s_follow_up = $('#periodeFollowUp').val();
    data.s_register = $('#periodeRegister').val();
});

$('#statusFilter').on('change', function() {
    window.LaravelDataTables['members-followup-table'].ajax.reload();
});

// ===== Modal Show Event =====
// const modal = document.querySelector('#kt_modal_add_members');
// if (modal) {
//     modal.addEventListener('show.bs.modal', (e) => {
//         // Livewire.emit('modal.show.role_name', e.relatedTarget.getAttribute('data-role-id'));
//     });
// }

// document.getElementById('totalDeposit').addEventListener('input', function (e) {
//     let value = this.value.replace(/\D/g, ''); // hanya angka
//     if (value) {
//         this.value = new Intl.NumberFormat('id-ID').format(value); // format ribuan Indonesia
//     } else {
//         this.value = '';
//     }
// });


document.querySelectorAll('[data-kt-action="follow_up_row"]').forEach(function (e) {
    e.addEventListener('click', function() {
        const memberId = this.getAttribute('data-kt-member-id');
        showLoadPage();
        fetch(`/members/followup`, {
            method: 'POST',
            body: JSON.stringify({ member_id: memberId }),
            headers: {
                'Content-Type': 'application/json', // 👈 required for Laravel
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), // optional if not using $.ajax
            },
        })
        .then(response => response.json())
        .then(data => {
            hideLoadPage();
            if(data.status) {
                if (data.data.redirectUrl) {
                    window.open(data.data.redirectUrl, '_blank');
                    window.LaravelDataTables['members-followup-table'].ajax.reload(null, false);
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
            window.LaravelDataTables['members-followup-table'].ajax.reload();
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
