<a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
    Actions
    <i class="ki-duotone ki-down fs-5 ms-1"></i>
</a>
<!--begin::Menu-->
<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
    <!--begin::Menu item-->
    <div class="menu-item px-3">
        <a href="{{ route('members.show', $transaction->member_id) }}" class="menu-link px-3">
            View
        </a>
    </div>
    <!--end::Menu item-->

    @can('member-management.delete')
        <!--begin::Menu item-->
        <div class="menu-item px-3 {{  $transaction->deleted_at ? 'd-none' : '' }}">
            <a href="#" class="menu-link px-3" data-kt-member-id="{{  $transaction->id }}" data-kt-action="delete_row">
                Delete
            </a>
        </div>
        <!--end::Menu item-->
    @endcan

    <!--begin::Menu item-->
    <div class="menu-item px-3 {{ $transaction->deleted_at ? 'd-none' : '' }}">
        <a href="#" class="menu-link px-3" data-kt-transaction-id="{{ $transaction->id }}" data-kt-action="follow_up_row">
            Follow Up
        </a>
    </div>
    <!--end::Menu item-->

</div>
<!--end::Menu-->
