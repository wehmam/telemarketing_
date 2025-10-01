<a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
    Actions
    <i class="ki-duotone ki-down fs-5 ms-1"></i>
</a>
<!--begin::Menu-->
<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
    <!--begin::Menu item-->
    <div class="menu-item px-3  {{ request()->routeIs('members.transactions.*') ? 'd-none' : '' }}">
        <a href="{{ route('members.show', $transaction->member_id) }}" class="menu-link px-3">
            View
        </a>
    </div>
    <!--end::Menu item-->

    @can('transaction-management.update')
        <!--begin::Menu item-->
        <div class="menu-item px-3 {{  $transaction->deleted_at ? 'd-none' : '' }}">
            <a href="#" class="menu-link px-3 kt_modal_update_transaction" data-transaction="{{  $transaction }}" data-kt-action="edit_row">
                Edit
            </a>
        </div>
        <!--end::Menu item-->
    @endcan

    @can('transaction-management.delete')
        <!--begin::Menu item-->
        <div class="menu-item px-3 {{  $transaction->deleted_at ? 'd-none' : '' }}">
            <a href="#" class="menu-link px-3" data-kt-transaction-id="{{  $transaction->id }}" data-kt-action="delete_row">
                Delete
            </a>
        </div>
        <!--end::Menu item-->
    @endcan

    @can('transaction-management.restore')
        <!--begin::Menu item-->
        <div class="menu-item px-3 {{ $transaction->deleted_at ? '' : 'd-none' }}">
            <a href="#" class="menu-link px-3" data-kt-transaction-id="{{ $transaction->id }}" data-kt-action="restore_row">
                Restore
            </a>
        </div>
        <!--end::Menu item-->
    @endcan

    @can('transaction-management.follow-up')
        <!--begin::Menu item-->
        <div class="menu-item px-3 {{ $transaction->deleted_at ? 'd-none' : '' }}">
            <a href="#" class="menu-link px-3" data-kt-transaction-id="{{ $transaction->id }}" data-kt-action="follow_up_row">
                Follow Up
            </a>
        </div>
        <!--end::Menu item-->
    @endcan

</div>
<!--end::Menu-->
