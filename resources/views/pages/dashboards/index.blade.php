<x-default-layout>

    @section('title')
        Dashboard
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('dashboard') }}
    @endsection

    <!--begin::Row-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!--begin::Col-->
        <div class="col-md-12 col-lg-12 col-xl-12 col-xxl-6 mb-md-10 mb-xl-10">
            @include('pages.dashboards.components.total_deposit')
            @include('pages.dashboards.components.total_transactions')
            {{-- @include('pages.dashboards.components.top_employees') --}}
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-md-12 col-lg-12 col-xl-12 col-xxl-6 mb-md-10 mb-xl-10">
            @include('pages.dashboards.components.total_redeposit')
            @include('pages.dashboards.components.top_member_deposit')
            {{-- @include('partials.widgets.cards._widget-20') --}}
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
</x-default-layout>
