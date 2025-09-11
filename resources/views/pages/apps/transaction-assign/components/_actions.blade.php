<a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
    Actions
    <i class="ki-duotone ki-down fs-5 ms-1"></i>
</a>
<!--begin::Menu-->
<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
    <!--begin::Menu item-->

    <div class="menu-item px-3">
        <a href="{{ route('members.show', $member) }}" class="menu-link px-3">
            View
        </a>
    </div>
    <!--end::Menu item-->

    {{-- @can('transaction-management.assign-transaction')
        <!--begin::Menu item-->
        <div class="menu-item px-3 {{ $member->deleted_at ? '' : 'd-none' }}">
            <a href="#" class="menu-link px-3" data-kt-member-id="{{ $member->id }}" data-kt-action="restore_row">
                Assign Transaction
            </a>
        </div>
        <!--end::Menu item-->
    @endcan --}}
</div>
<!--end::Menu-->
