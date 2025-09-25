// Initialize KTMenu
KTMenu.init();

// ===== Search Input =====
const searchInput = document.getElementById('mySearchInput');
if (searchInput) {
    let debounceTimer;
    searchInput.addEventListener('keyup', function () {
        clearTimeout(debounceTimer); // cancel previous timer
        debounceTimer = setTimeout(() => {
            window.LaravelDataTables['activity-logs-table'].search(this.value).draw();
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

const dt = window.LaravelDataTables['activity-logs-table'];

// activity date filter
$('#activityDate').on('change', debounce(function() {
    dt.ajax.reload();
}, 500));

// marketing filter
$('#sMarketing').on('change', debounce(function() {
    dt.ajax.reload();
}, 500));

// team filter
$('#sTeam').on('change', debounce(function() {
    dt.ajax.reload();
}, 500));


// Kirim data filter ke server sebelum AJAX
dt.on('preXhr.dt', function(e, settings, data) {
    data.s_activity_date = $('#activityDate').val();
    data.s_marketing = $('#sMarketing').val();
    data.s_team = $('#sTeam').val();
});

// $('#btnExportExcel').off('click').on('click', function(e) {
//     e.preventDefault();
//     showLoadPage();

//     let params = {
//         s_activity_date: $('#activityDate').val(),
//         s_marketing: $('#sMarketing').val(),
//         s_team: $('#sTeam').val()
//     };

//     let query = $.param(params);
//     let url = '/transactions/export/excel?' + query;

//     fetch(url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
//         .then(response => {
//             const disposition = response.headers.get('Content-Disposition');
//             let filename = "transactions.xlsx";
//             if (disposition && disposition.indexOf('filename=') !== -1) {
//                 filename = disposition.split('filename=')[1].replace(/"/g, '');
//             }
//             return response.blob().then(blob => ({ blob, filename }));
//         })
//         .then(({ blob, filename }) => {
//             hideLoadPage();
//             const url = window.URL.createObjectURL(blob);
//             const a = document.createElement('a');
//             a.href = url;
//             a.download = filename;
//             document.body.appendChild(a);
//             a.click();
//             a.remove();
//             window.URL.revokeObjectURL(url);
//         })
//         .catch(() => {
//             hideLoadPage();
//             Swal.fire("Error", "Failed to export file.", "error");
//         });
// });
