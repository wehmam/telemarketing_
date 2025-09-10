<x-default-layout>

    @section('title')
        Transactions
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('transactions') }}
    @endsection

    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                @include('pages.apps.transactions.components.search')
                <!--end::Search-->
            </div>
            <!--begin::Card title-->

            <div class="card-toolbar">

            @can('transaction-management.export')
            <!--begin::Add Member Button-->
            <div class="d-flex align-items-center mt-2 mt-md-0 me-3">
                <a href="javascript:void(0);" class="btn btn-sm btn-success p-3" id="btnExportExcel">
                    {!! getIcon('cloud-download', 'fs-2', '', 'i') !!}
                    Export
                </a>
            </div>
            <!--end::Add Member Button-->
            @endcan

            @can('transaction-management.import')
                <!--begin::Add Member Button-->
                <div class="d-flex align-items-center mt-2 mt-md-0 me-3">
                    {{-- <button type="button" class="btn btn-sm btn-info p-3" onclick="window.location='{{ route('transactions.downloadTemplate') }}'"> --}}
                    <button type="button" class="btn btn-sm btn-info p-3" onclick="window.location='{{ route('transactions.index') }}'">
                        {!! getIcon('file', 'fs-2', 'files-folders', 'i') !!}

                        Download Template
                    </button>
                </div>
                <!--end::Add Member Button-->
                
                <!--begin::Add Member Button-->
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    <button type="button" class="btn btn-sm btn-primary p-3" data-bs-toggle="modal" data-bs-target="#kt_modal_add_transactions">
                        {!! getIcon('plus', 'fs-2', '', 'i') !!}
                        Import
                    </button>
                </div>
                <!--end::Add Member Button-->
            @endcan
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Table transactions-->
        <div class="card-body py-4">
            @include('pages.apps.transactions.components.search-advanced')
            <!--begin::Table-->
            <div class="table-responsive mt-5">
                {{ $dataTable->table() }}
            </div>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>

    <!--begin::Modal-->
        @include("pages.apps.transactions.components.add-transaction-modal")
    <!--end::Modal-->

    @push('scripts')
        {{ $dataTable->scripts() }}
        <script>
            flatpickr("#periodeLastDeposit", {
                mode: "range",            // memungkinkan pilih dua tanggal (start & end)
                dateFormat: "d-m-Y",      // format sesuai Laravel
                // defaultDate: ["{{ date('d-m-Y', strtotime('-7 days')) }}", "{{ date('d-m-Y') }}"],
                allowInput: true
            });
        </script>

    @endpush

</x-default-layout>
