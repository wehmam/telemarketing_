<div class="modal fade" id="kt_modal_assign_transactions" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Assign Transactions</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal" aria-label="Close">
                    {!! getIcon('cross','fs-1') !!}
                </div>
            </div>

            <div class="modal-body px-5 my-7">
                <form id="kt_modal_assign_transaction_form" class="form" action="javascript:void(0);">
                    @csrf
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10">

                        <!--begin::Input group-->
                        <div class="fv-row mb-10">
                            <label class="fs-5 fw-bold form-label mb-2">
                                <span class="required">Select Members (From)</span>
                            </label>
                            <select class="form-select form-select-solid"
                                    name="from_member_ids[]"
                                    id="assignFromMemberId"
                                    multiple="multiple"
                                    data-control="select2"
                                    data-placeholder="Select members"
                                    required>
                                @foreach($fromMembers as $member)
                                    @php
                                        $phone = $member->phone;
                                        if ($member->phone && str_starts_with($member->phone, '62')) {
                                            $phone = '0' . substr($member->phone, 2);
                                        }
                                    @endphp
                                    <option value="{{ $member->id }}">
                                        {{ $member->name }} ({{ $member->username . " - " . $phone }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">You can select multiple members to transfer transactions from.</div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-10">
                            <label class="fs-5 fw-bold form-label mb-2">
                                <span class="required">Assign To User</span>
                            </label>
                            <select class="form-select form-select-solid"
                                    name="to_user_id"
                                    id="assignToUserId"
                                    data-control="select2"
                                    data-placeholder="Select user"
                                    data-dropdown-parent="#kt_modal_assign_transactions"
                                    required>
                                <option value="">-- Select To User --</option>
                                @foreach($toUsers as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }} ({{ $user->team->name }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">You can select user has team only</div>
                        </div>
                        <!--end::Input group-->

                    </div>
                    <!--end::Scroll-->

                    <!--begin::Actions-->
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Assign</span>
                            <span class="indicator-progress d-none">
                                Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
            </div>
        </div>
    </div>
</div>
