<div class="modal fade" id="kt_modal_update_import" tabindex="-1" aria-hidden="true" data-bs-focus="false" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_add_members_header">
                <h2 class="fw-bold" id="titleModal">Update Import</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal" aria-label="Close">
                    {!! getIcon('cross','fs-1') !!}
                </div>
            </div>

            <div class="modal-body px-5 my-7">
                <form id="kt_modal_update_import_form" class="form" action="javascript:void(0);" enctype="multipart/form-data">
                    @csrf

                    <div class="d-flex flex-column scroll-y px-5 px-lg-10"
                         id="kt_modal_add_members_scroll"
                         data-kt-scroll="true"
                         data-kt-scroll-activate="true"
                         data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_add_members_header"
                         data-kt-scroll-wrappers="#kt_modal_add_members_scroll"
                         data-kt-scroll-offset="300px">

                        <!-- Option Selector -->
                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">Action Type</label>
                            <select id="action_type" name="action_type" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Type Update" data-dropdown-parent="#kt_modal_update_import" required>
                                <option value="">Select action</option>
                                <option value="change_date">Change Date</option>
                                <option value="replace_data">Replace Data</option>
                            </select>
                        </div>

                        <!-- Reference Batch Code -->
                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">Reference Batch Code</label>
                            <input type="text"
                                   id="batch_code_ref"
                                   name="batch_code_ref"
                                   class="form-control form-control-solid"
                                   placeholder="Enter batch code"
                                   readonly>
                        </div>

                         <!-- Reference Batch Code -->
                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">Type Import</label>
                            <input type="text" id="type_import" name="type_import" class="form-control form-control-solid" placeholder="Type Import" readonly>
                        </div>

                        <!-- Transaction Date -->
                        <div class="fv-row mb-7" id="date_group" style="display:none;">
                            <label class="fw-semibold fs-6 mb-2">Transaction Date</label>
                            <input type="text" id="transaction_date" name="transaction_date" class="form-control form-control-solid" placeholder="Select date" autocomplete="off">
                        </div>

                        <!-- File Input -->
                        <div class="fv-row mb-7" id="file_group" style="display:none;">
                            <label class="fw-semibold fs-6 mb-2">Choose File Import (Excel)</label>
                            <input type="file" name="file" class="form-control form-control-solid" accept=".xlsx">
                        </div>
                    </div>

                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal" aria-label="Close">Discard</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Submit</span>
                            <span class="indicator-progress" style="display:none;">
                                Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Script --}}
{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const actionSelect = document.getElementById('action_type');
        const dateGroup = document.getElementById('date_group');
        const fileGroup = document.getElementById('file_group');

        actionSelect.addEventListener('change', function() {
            if(this.value === 'change_date') {
                dateGroup.style.display = 'block';
                fileGroup.style.display = 'none';
                document.getElementById('transaction_date').required = true;
                document.querySelector('input[name="file"]').required = false;
            } else if(this.value === 'replace_data') {
                dateGroup.style.display = 'none';
                fileGroup.style.display = 'block';
                document.getElementById('transaction_date').required = false;
                document.querySelector('input[name="file"]').required = true;
            } else {
                dateGroup.style.display = 'none';
                fileGroup.style.display = 'none';
                document.getElementById('transaction_date').required = false;
                document.querySelector('input[name="file"]').required = false;
            }
        });

        // Initialize flatpickr
        flatpickr("#transaction_date", {
            dateFormat: "d-m-Y",
            allowInput: true
        });
    });
</script> --}}
