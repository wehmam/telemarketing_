<x-default-layout>

    @section('title')
        Members Follow Up
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
                @include('pages.apps.followup-member.components.search')
                <!--end::Search-->
            </div>
            <!--begin::Card title-->

            <div class="card-toolbar">

            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Table Members-->
        <div class="card-body py-4">
            @include('pages.apps.followup-member.components.search-advanced')
            <!--begin::Table-->
            <div class="table-responsive mt-5">
                {{ $dataTable->table() }}
            </div>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>

    <!--begin::Modal-->
        {{-- @include("pages.apps.followup-member.components.add-member-modal") --}}
    <!--end::Modal-->

    @push('scripts')
        {{ $dataTable->scripts() }}
        <script>
            flatpickr("#periodeLastDeposit", {
                mode: "range",            // memungkinkan pilih dua tanggal (start & end)
                dateFormat: "d-m-Y",      // format sesuai Laravel
                allowInput: false
            });
        </script>
    @endpush

</x-default-layout>
