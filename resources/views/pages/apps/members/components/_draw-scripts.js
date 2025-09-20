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
    searchInput.addEventListener('keyup', function () {
        window.LaravelDataTables['members-table'].search(this.value).draw();
    });
}

function debounce(func, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

const dt = window.LaravelDataTables['members-table'];

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
$('.sStatus').on('change', function() {
    window.LaravelDataTables['members-table'].ajax.reload();
});

// Kirim data filter ke server sebelum AJAX
dt.on('preXhr.dt', function(e, settings, data) {
    data.s_nama_rekening = $('#sNamaRekening').val();
    data.s_username = $('#sUsername').val();
    data.s_phone = $('#sPhone').val();
    data.s_status = $('.sStatus:checked').val();
});

$('#statusFilter').on('change', function() {
    window.LaravelDataTables['members-table'].ajax.reload();
});

// ===== Modal Show Event =====
const modal = document.querySelector('#kt_modal_add_members');
if (modal) {
    modal.addEventListener('show.bs.modal', (e) => {
        // Livewire.emit('modal.show.role_name', e.relatedTarget.getAttribute('data-role-id'));
    });
}

// ===== Form Submit =====
$(document).ready(function () {
    $('#kt_modal_add_members_form').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        let memberId = $('#member_id').val();

        let url = memberId ? `/members/${memberId}` : `/members`;
        if(memberId){
            formData.append('_method', 'PUT');
        }

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
                $('#kt_modal_add_members').modal('hide');
                $('#kt_modal_add_members_form')[0].reset();
                window.LaravelDataTables['members-table'].ajax.reload();
            },
            error: function (xhr) {
                hideLoadPage();
                if (xhr.status === 422) {
                    let message = xhr.responseJSON.message;
                    Swal.fire("Validation Error", message, "error");
                } else {
                    Swal.fire("Error", "Something went wrong", "error");
                }
            }
        });
    });

    $(document).on("click", ".modal_add_member", function(e) {
        e.preventDefault();

        showLoadPage();

        $("#titleModal").text("Create Member");

        $("#member_id").val("");
        $("#iName").val("");
        $("#iUsername").val("").prop("disabled", false); // ✅ enable username
        $("#iPhone").val("");
        $("#iNamaRekening").val("");

        $("#kt_modal_add_members").modal("show");
        hideLoadPage();
    });

    // ===== Edit Member =====
    $(document).on("click", ".kt_modal_edit_member", function(e) {
        e.preventDefault();

        showLoadPage();
        var member = $(this).data("data");

        $("#titleModal").text("Update Member");

        $("#member_id").val(member.id);
        $("#iName").val(member.name);
        $("#iUsername").val(member.username).prop("disabled", true);
        $("#iPhone").val(member.phone);
        $("#iNamaRekening").val(member.nama_rekening);
        $("#kt_modal_add_members").modal("show");
        hideLoadPage();
    });
});

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
                    window.LaravelDataTables['members-table'].ajax.reload();
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

// ===== Restore Member =====
document.querySelectorAll('[data-kt-action="restore_row"]').forEach(function (element) {
    element.addEventListener('click', function () {
        const memberId = this.getAttribute('data-kt-member-id');

        Swal.fire({
            text: 'Are you sure you want to restore this member?',
            icon: 'warning',
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: 'Yes, restore it!',
            cancelButtonText: 'No, cancel',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                showLoadPage();
                fetch(`/members/${memberId}/restore`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    hideLoadPage();
                    Swal.fire({
                        text: data.message || (data.status ? 'Member restored successfully!' : 'Failed to restore member.'),
                        icon: data.status ? 'success' : 'error',
                        confirmButtonText: 'OK',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                    window.LaravelDataTables['members-table'].ajax.reload();
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
