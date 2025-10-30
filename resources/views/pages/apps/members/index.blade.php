<x-default-layout>

    @section('title')
        Members
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('members') }}
    @endsection

    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                @include('pages.apps.members.components.search')
                <!--end::Search-->
            </div>
            <!--begin::Card title-->

            <div class="card-toolbar">

            @can('member-management.export')
                <!--begin::Add Member Button-->
                <div class="d-flex align-items-center mt-2 mt-md-0 me-3">
                    <a href="javascript:void(0);" class="btn btn-sm btn-success p-3" id="btnExportExcel">
                        {!! getIcon('cloud-download', 'fs-2', '', 'i') !!}
                        Export
                    </a>
                </div>
                <!--end::Add Member Button-->
            @endcan

            @can('member-management.import')
                <a href="{{ asset('assets/templates/TEMPLATE_MEMBERS.xlsx') }}" class="btn btn-sm btn-warning me-3" download>
                    {!! getIcon('cloud-download', 'fs-2', '', 'i') !!}
                    Template
                </a>
                <!--begin::Add Member Button-->
                <div class="d-flex align-items-center mt-2 mt-md-0 me-3">
                    <button type="button" class="btn btn-sm btn-info p-3" data-bs-toggle="modal" data-bs-target="#kt_modal_import_members">
                        {!! getIcon('file', 'fs-2', 'files-folders', 'i') !!}
                        Import
                    </button>
                </div>
                <!--end::Add Member Button-->
            @endcan


            @can('member-management.create')
                <!--begin::Add Member Button-->
                <div class="d-flex align-items-center mt-2 mt-md-0 modal_add_member">
                    <button type="button" class="btn btn-primary btn-sm p-3"
                            data-bs-toggle="modal"
                            data-bs-target="#kt_modal_add_members">
                        {!! getIcon('plus', 'fs-2', '', 'i') !!}
                        Add Members
                    </button>
                </div>
                <!--end::Add Member Button-->
            @endcan
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Table Members-->
        <div class="card-body py-4">
            @include('pages.apps.members.components.search-advanced')
            <!--begin::Table-->
            <div class="table-responsive mt-5">
                {{ $dataTable->table() }}
            </div>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>

    <!--begin::Modal-->
        @include("pages.apps.members.components.add-member-modal")
        @include("pages.apps.members.components.import-member-modal")
    <!--end::Modal-->

    @push('scripts')
        {{ $dataTable->scripts() }}
        <script>
            flatpickr("#periodeLastDeposit", {
                mode: "range",
                dateFormat: "d-m-Y",
                // defaultDate: ["{{ date('d-m-Y', strtotime('-7 days')) }}", "{{ date('d-m-Y') }}"],
                allowInput: true
            });

            flatpickr("#registerDate", {
                mode: "range",
                dateFormat: "d-m-Y",
                allowInput: true
            });

            flatpickr("#import_date", {
                dateFormat: "d-m-Y",
                allowInput: true,
                defaultDate: "{{ date('d-m-Y') }}"
            });

            const phoneInput = document.getElementById('iPhone');

            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            phoneInput.addEventListener('blur', function() {
                let phone = this.value.trim();

                if (phone.startsWith('0')) {
                    phone = '62' + phone.substring(1);
                }

                this.value = phone;
            });
        </script>
    @endpush

</x-default-layout>
