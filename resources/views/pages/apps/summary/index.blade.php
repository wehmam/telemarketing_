<x-default-layout>

    @section('title')
        Summary Report
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('summary.index') }}
    @endsection

     <div class="card card-xxl-stretch mb-5 mb-xxl-10">
        <!--begin::Header-->
        <div class="card-header">
            <div class="card-title">
                <h3>Summary Report</h3>
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
                    <div class="border border-dashed border-gray-300 w-400px rounded my-3 p-4 me-6">
                        <span class="fs-2x fw-bold text-gray-800 lh-1">
                            <span id="totalTransactions">Rp. 0</span>
                        </span>
                        <span class="fs-6 fw-semibold text-gray-400 d-block lh-1 pt-2">Total</span>
                    </div>
                    <!--end::Col-->
                    <!--begin::Col-->
                    <div class="border border-dashed border-gray-300 w-150px rounded my-3 p-4 me-6">
                        <span class="fs-2x fw-bold text-gray-800 lh-1">
                            <span id="totalMember">0</span></span>
                        <span class="fs-6 fw-semibold text-gray-400 d-block lh-1 pt-2">New Regis</span>
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="border border-dashed border-gray-300 w-150px rounded my-3 p-4 me-6">
                        <span class="fs-2x fw-bold text-gray-800 lh-1">
                            <span id="totalDeposit">0</span></span>
                        <span class="fs-6 fw-semibold text-gray-400 d-block lh-1 pt-2">Total Deposit</span>
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="border border-dashed border-gray-300 w-150px rounded my-3 p-4 me-6">
                        <span class="fs-2x fw-bold text-gray-800 lh-1">
                            <span id="totalRedeposit">0</span></span>
                        <span class="fs-6 fw-semibold text-gray-400 d-block lh-1 pt-2">Total Redeposit</span>
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

        <!--begin::Table transactions-->
        <div class="card-body py-4">
            @include('pages.apps.summary.components.search-advanced')
            <!--begin::Table-->
            <div class="table-responsive mt-5">
                {{ $dataTable->table() }}
            </div>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>

    @push('scripts')
        {{ $dataTable->scripts() }}
        <script>
            flatpickr("#periodeLastDeposit", {
                mode: "range",
                dateFormat: "d-m-Y",
                // defaultDate: ["{{ date('d-m-Y', strtotime('-7 days')) }}", "{{ date('d-m-Y') }}"],
                defaultDate: ["{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}",  "{{ \Carbon\Carbon::now()->endOfMonth()->format('d-m-Y') }}"],
                allowInput: true
            });
        </script>

    @endpush

</x-default-layout>
