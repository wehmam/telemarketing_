<x-default-layout>

    @section('title')
        Import Log
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('logs.index') }}
    @endsection

    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                {{-- @include('pages.apps.activity-logs.components.search') --}}
                <!--end::Search-->
            </div>
            <!--begin::Card title-->

            <div class="card-toolbar">

            @can('export-management.export')
            <!--begin::Add Member Button-->
            <div class="d-flex align-items-center mt-2 mt-md-0 me-3 d-none">
                <a href="javascript:void(0);" class="btn btn-sm btn-success p-3" id="btnExportExcel">
                    {!! getIcon('cloud-download', 'fs-2', '', 'i') !!}
                    Export
                </a>
            </div>
            <!--end::Add Member Button-->
            @endcan
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Table Activity Logs-->
        <div class="card-body py-4">
            {{-- @include('pages.apps.activity-logs.components.search-advanced') --}}
            <!--begin::Table-->
            <div class="table-responsive mt-5">
                {{ $dataTable->table() }}
            </div>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>

    @include('pages.apps.import-logs.components.modal-update-import')


    @push('scripts')
        {{ $dataTable->scripts() }}
        <script>
            flatpickr("#activityDate", {
                mode: "range",            // memungkinkan pilih dua tanggal (start & end)
                dateFormat: "d-m-Y",      // format sesuai Laravel
                // defaultDate: ["{{ date('d-m-Y', strtotime('-7 days')) }}", "{{ date('d-m-Y') }}"],
                allowInput: true
            });
        </script>
    @endpush

</x-default-layout>
