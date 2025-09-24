<x-default-layout>

    @section('title')
        Export Report
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('followup') }}
    @endsection

    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                {{-- @include('pages.apps.followup-member.components.search') --}}
                <!--end::Search-->
            </div>
            <!--begin::Card title-->
        </div>
        <!--end::Card header-->

        <!--begin::Table Members-->
        <div class="card-body py-4">
            <!--begin::Form group-->
            <div class="form-group d-flex flex-stack">
                <!--begin::Heading-->
                <div class="d-flex flex-column">
                    <h4 class="fw-bold text-gray-900">Type Report</h4>
                    <div class="fs-7 fw-semibold text-muted">
                        Type Of Report
                    </div>
                </div>
                <!--end::Heading-->

                <!--begin::Option-->
                <div class="d-flex justify-content-end">
                    <div class="form-group">
                        <select class="form-select form-select-solid w-auto" data-control="select2" id="typeReport" name="type_report" data-placeholder="Select Type Report">
                            <option></option>
                            <option value="summary_employee">Summary Employee</option>
                            <option value="redeposit">Report Redeposit</option>
                            <option value="backup">Backup Transaction</option>
                            <option value="delete_transaction">Delete Transaction</option>
                        </select>
                    </div>
                </div>
                <!--end::Option-->
            </div>
            <!--end::Form group-->

            <!--begin::Separator-->
            <div class="separator separator-dashed my-6"></div>
            <!--end::Separator-->

             <!--begin::Form group-->
            <div class="form-group d-flex flex-stack">
                <!--begin::Heading-->
                <div class="d-flex flex-column">
                    <h4 class="fw-bold text-gray-900">Period Report</h4>
                    <div class="fs-7 fw-semibold text-muted">
                        Period Of Report
                    </div>
                </div>
                <!--end::Heading-->

                <!--begin::Option-->
                <div class="d-flex justify-content-end">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-solid w-auto" name="periode_last_deposit" id="periodeDate" placeholder="Select date range">
                    </div>
                </div>
                <!--end::Option-->
            </div>
            <!--end::Form group-->

        </div>
        <!--end::Card body-->

        {{-- card footer --}}
        <div class="card-footer d-flex py-8">
            <input type="hidden" id="kt_layout_builder_tab" name="layout-builder[tab]" value="kt_layout_builder_header">
            <input type="hidden" id="kt_layout_builder_action" name="layout-builder[action]">

            <button type="button" id="kt_layout_builder_export" class="btn btn-primary me-2" fdprocessedid="o044uq">
                <span class="indicator-label">
                    Export
                </span>
                <span class="indicator-progress">
                    Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>

            <button type="button" id="kt_layout_builder_reset" class="btn btn-active-light btn-color-muted"
                fdprocessedid="qcdxd8">
                <span class="indicator-label">
                    Reset
                </span>
                <span class="indicator-progress">
                    Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
        </div>
        {{-- card footer ends --}}

    </div>

    <!--begin::Modal-->
        {{-- @include("pages.apps.followup-member.components.add-member-modal") --}}
    <!--end::Modal-->

    @push('scripts')
        <script>
            flatpickr("#periodeDate", {
                mode: "range",            // memungkinkan pilih dua tanggal (start & end)
                dateFormat: "d-m-Y",      // format sesuai Laravel
                defaultDate: [
                    "{{ date('d-m-Y', strtotime('first day of this month')) }}",
                    "{{ date('d-m-Y') }}"
                ],
                allowInput: true
            });

            $('#typeReport').on('change', function() {
                var selectedValue = $(this).val();
                // if (selectedValue === "backup") {
                //     $('#periodeDate').val('').prop('disabled', true);
                // } else {
                    $('#periodeDate').prop('disabled', false);
                // }
                console.log("Selected Type Report: " + selectedValue);
            });

            document.getElementById("kt_layout_builder_export").addEventListener("click", async function() {
                let typeReport = document.querySelector('[name="type_report"]').value;
                let periode = document.querySelector('#periodeDate').value;

                if (!typeReport || !periode) {
                    console.log("Type Report or Periode is missing", typeReport, periode);
                    if (typeReport == "summary_employee" || typeReport == "redeposit") {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Missing Data',
                            text: 'Please select both Type Report and Period!'
                        });
                        return;
                    }
                }

                // Kalau backup → langsung redirect ke route download
                if (typeReport === "backup") {
                    return
                    window.location.href = "/export/backup-transactions?periode=" + encodeURIComponent(periode);
                    return;
                } else if (typeReport === "delete_transaction") {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "This action will permanently delete all transactions. You cannot undo this action! Please make sure you have backed up the data first.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "/export/delete-transactions?periode=" + encodeURIComponent(periode);
                        }
                    });
                    return;
                }

                // Kalau report biasa → pakai fetch blob
                try {
                    let url = "{{ route('export.store') }}";
                    let response = await fetch(url, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        },
                        body: new URLSearchParams({
                            type_report: typeReport,
                            periode: periode
                        })
                    });

                    if (!response.ok) {
                        let error;
                        try {
                            error = await response.json();
                        } catch (e) {
                            error = { message: "Unexpected server error" };
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Export Failed',
                            text: error.message ?? 'Something went wrong'
                        });
                        return;
                    }

                    let disposition = response.headers.get('Content-Disposition');
                    let filename = "report.xlsx"; // fallback
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        let matches = /filename="?(.+?)"?($|;)/.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1];
                    }

                    // Success → download Excel file
                    let blob = await response.blob();
                    let downloadUrl = window.URL.createObjectURL(blob);
                    let a = document.createElement("a");
                    a.href = downloadUrl;
                    a.download = filename; // dynamic filename dari server
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(downloadUrl);

                    Swal.fire({
                        icon: 'success',
                        title: 'Export Success',
                        text: 'Your report has been downloaded!'
                    });

                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Error',
                        text: err.message
                    });
                }
            });


        </script>
    @endpush

</x-default-layout>
