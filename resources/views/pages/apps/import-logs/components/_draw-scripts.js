// Initialize KTMenu
KTMenu.init();

// ===== Search Input =====
const searchInput = document.getElementById('mySearchInput');
if (searchInput) {
    let debounceTimer;
    searchInput.addEventListener('keyup', function () {
        clearTimeout(debounceTimer); // cancel previous timer
        debounceTimer = setTimeout(() => {
            window.LaravelDataTables['import-logs-table'].search(this.value).draw();
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

const dt = window.LaravelDataTables['import-logs-table'];

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

$(document).on("click", ".modal_update_import", function(e) {
    e.preventDefault();

    showLoadPage();
    var batchCode = $(this).data("kt-batch-code");

    $('#batch_code_ref').val(batchCode)

    let type = "UNKNOWN"
    if(batchCode.startsWith("BATCH_TRANSACTIONS")) {
        type = "Import Transactions";
    } else if(batchCode.startsWith("BATCH_MEMBERS")) {
        type = "Import Members";
    }

    $('#type_import').val(type)

    $("#kt_modal_update_import").modal("show");
    hideLoadPage();
});

flatpickr("#transaction_date", {
    dateFormat: "d-m-Y",
    allowInput: true
});

// Action type change
$('#action_type').on('change', function() {
    let val = $(this).val();
    let typeImport = $('#type_import').val();

    if(val === 'change_date') {
        $('#date_group').show();
        $('#file_group').hide();
        $('#transaction_date').prop('required', true);
        $('input[name="file"]').prop('required', false);
    } else if(val === 'replace_data') {
        $('#date_group').show();
        $('#file_group').show();
        $('#transaction_date').prop('required', true);
        $('input[name="file"]').prop('required', true);

        if (typeImport == 'Import Members') {
            $('#date_group').hide();
            $('#transaction_date').prop('required', false);
            $('#transaction_date').val(new Date().toLocaleDateString('en-GB').replace(/\//g, '-'));

            console.log("date", new Date().toLocaleDateString('en-GB').replace(/\//g, '-'));
        }
    } else {
        $('#date_group, #file_group').hide();
        $('#transaction_date').prop('required', false);
        $('input[name="file"]').prop('required', false);
    }
});

$(document).ready(function() {

    $('#kt_modal_update_import_form').off('submit').on('submit', function(e) {
        e.preventDefault();

        showLoadPage();

        let formData = new FormData(this); // otomatis ambil semua field termasuk file
        let url = '/importlog/update'; // ganti sesuai route backend kamu

        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoadPage();
                Swal.fire(
                    response.status ? "Success" : "Error",
                    response.message,
                    response.status ? "success" : "error"
                );
                $('#kt_modal_update_import').modal('hide');
                $('#kt_modal_update_import_form')[0].reset();
                window.LaravelDataTables['import-logs-table'].ajax.reload();
            },
            error: function(xhr) {
                hideLoadPage();
                if (xhr.status === 422) {
                    let message = xhr.responseJSON.message;
                    Swal.fire("Validation Error", message, "error");
                } else {
                    Swal.fire("Error", xhr.responseJSON?.message ?? "Something went wrong", "error");
                }
            }
        });

    });

});
