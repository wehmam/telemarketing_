// Initialize KTMenu
KTMenu.init();

// Add click event listener to delete buttons
document.querySelectorAll('[data-kt-action="delete_row"]').forEach(function (element) {
    element.addEventListener('click', function () {
        Swal.fire({
            text: 'Are you sure you want to remove?',
            icon: 'warning',
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.emit('delete_user', this.getAttribute('data-kt-user-id'));
            }
        });
    });
});

// Add click event listener to restore buttons
document.querySelectorAll('[data-kt-action="restore_row"]').forEach(function (element) {
    element.addEventListener('click', function () {
        const userId = this.getAttribute('data-kt-user-id');

        Swal.fire({
            text: 'Are you sure you want to restore?',
            icon: 'warning',
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                showLoadPage();
                fetch(`/user-management/users/${userId}/restore`, {
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
                        text: data.message || (data.status ? 'User restored successfully!' : 'Failed to restore user.'),
                        icon: data.status ? 'success' : 'error',
                        confirmButtonText: 'OK',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                    window.LaravelDataTables['users-table'].ajax.reload();
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

// Add click event listener to update buttons
document.querySelectorAll('[data-kt-action="update_row"]').forEach(function (element) {
    element.addEventListener('click', function () {
        Livewire.emit('update_user', this.getAttribute('data-kt-user-id'));
    });
});

// Listen for 'success' event emitted by Livewire
Livewire.on('success', (message) => {
    // Reload the users-table datatable
    LaravelDataTables['users-table'].ajax.reload();
});
