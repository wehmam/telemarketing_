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
    let debounceTimer;
    searchInput.addEventListener('keyup', function () {
        clearTimeout(debounceTimer); // cancel previous timer
        debounceTimer = setTimeout(() => {
            window.LaravelDataTables['marketing-summary-table'].search(this.value).draw();
        }, 1000); // delay in milliseconds (300ms)
    });
}

function debounce(func, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

const dt = window.LaravelDataTables['marketing-summary-table'];


$('#sMarketing').on('change', debounce(function() {
    dt.ajax.reload();
}, 500));

$('#sTeam').on('change', debounce(function() {
    dt.ajax.reload();
}, 500));

$('.sLastDeposit').on('change', debounce(function() {
    console.log('Date changed:', this.value);
    dt.ajax.reload();
}, 1000));


// Kirim data filter ke server sebelum AJAX
dt.on('preXhr.dt', function(e, settings, data) {
    data.s_date = $('.sLastDeposit').val();
    data.s_marketing = $('#sMarketing').val();
    data.s_team = $('#sTeam').val();
});

// ===== Update Total Transactions =====
function updateHeaderTransaction(json) {
    if (!json) return;

    if (json.totalAmount !== undefined) {
        $('#totalTransactions').text(formatRupiah(json.totalAmount));
    }

    if (json.totalMember !== undefined) {
        $('#totalMember').text(json.totalMember);
    }

    if (json.totalMemberDeposit !== undefined) {
        $('#totalMemberDeposit').text(formatRupiah(json.totalMemberDeposit));
    }

    if (json.totalMemberRedeposit !== undefined) {
        $('#totalMemberRedeposit').text(formatRupiah(json.totalMemberRedeposit));
    }

    if (json.totalDeposit !== undefined) {
        $('#totalDeposit').text(json.totalDeposit);
    }

    if (json.totalRedeposit !== undefined) {
        $('#totalRedeposit').text(json.totalRedeposit);
    }
}

// First load
dt.on('init.dt', function (e, settings, json) {
    updateHeaderTransaction(json);
});

// Every ajax request
dt.on('xhr.dt', function (e, settings, json, xhr) {
    updateHeaderTransaction(json);
});

// ===== Export Excel =====
$('#btnExportExcel').off('click').on('click', function(e) {
    e.preventDefault();
    showLoadPage();

    let url = '/summary/export?s_date=' + encodeURIComponent($('.sLastDeposit').val());

    fetch(url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => {
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


// ===== Modal Show Event =====
const modal = document.querySelector('#kt_modal_add_transactions');
if (modal) {
    modal.addEventListener('show.bs.modal', (e) => {
        // Livewire.emit('modal.show.role_name', e.relatedTarget.getAttribute('data-role-id'));
    });
}


const inputAmountDeposit = document.getElementById('amountDeposit');
if (inputAmountDeposit) {
    inputAmountDeposit.addEventListener('input', function (e) {
        let value = this.value.replace(/\D/g, ''); // hanya angka
        if (value) {
            this.value = new Intl.NumberFormat('id-ID').format(value); // format ribuan Indonesia
        } else {
            this.value = '';
        }
    });
}
