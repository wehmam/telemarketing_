<x-default-layout>

    @section('title')
        Transactions
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('transactions') }}
    @endsection

     <div class="card card-xxl-stretch mb-5 mb-xxl-10">
        <!--begin::Header-->
        <div class="card-header">
            <div class="card-title">
                <h3>Summary Transaction</h3>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body pb-0">
            {{-- <span class="fs-5 fw-semibold text-gray-600 pb-5 d-block">Last 30 day earnings calculated. Apart from arrangingthe order of topics.</span> --}}
            <!--begin::Left Section-->
            <div class="d-flex flex-wrap justify-content-between pb-6">
                <!--begin::Row-->
                <div class="d-flex flex-wrap">
                    <!--begin::Col-->
                    <div class="border border-dashed border-gray-300 w-500px rounded my-3 p-4 me-6">
                        <span class="fs-2x fw-bold text-gray-800 lh-1">
                            <span id="totalTransactions">Rp. 0</span>
                        </span>
                        <span class="fs-6 fw-semibold text-gray-400 d-block lh-1 pt-2">Total</span>
                    </div>
                    <!--end::Col-->
                    <!--begin::Col-->
                    <div class="border border-dashed border-gray-300 w-250px rounded my-3 p-4 me-6">
                        <span class="fs-2x fw-bold text-gray-800 lh-1">
                            <span id="totalMember">0</span></span>
                        <span class="fs-6 fw-semibold text-gray-400 d-block lh-1 pt-2">Total Member</span>
                    </div>
                    <!--end::Col-->
                    <!--begin::Col-->
                    <div class="border border-dashed border-gray-300 w-250px rounded my-3 p-4 me-6">
                        <span class="fs-2x fw-bold text-gray-800 lh-1">
                            <span id="totalMemberDeposit">Rp. 0</span></span>
                        <span class="fs-6 fw-semibold text-gray-400 d-block lh-1 pt-2">Total Member DEPOSIT</span>
                    </div>
                    <!--end::Col-->
                    <!--begin::Col-->
                    <div class="border border-dashed border-gray-300 w-250px rounded my-3 p-4 me-6">
                        <span class="fs-2x fw-bold text-gray-800 lh-1">
                            <span id="totalMemberRedeposit">Rp. 0</span>
                        </span>
                        <span class="fs-6 fw-semibold text-gray-400 d-block lh-1 pt-2">Total Member REDEPOSIT</span>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->
            </div>
            <!--end::Left Section-->
        </div>
        <!--end::Body-->
    </div>

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
