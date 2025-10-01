<div class="modal fade" id="kt_modal_update_transaction" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_add_members_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold" id="titleModal">Update Transactions</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal" aria-label="Close">
                    {!! getIcon('cross','fs-1') !!}
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body px-5 my-7">
                <!--begin::Form-->
                <form id="kt_modal_update_transaction_form" class="form" action="javascript:void(0);" enctype="multipart/form-data">
                    @csrf
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_members_scroll" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_members_header" data-kt-scroll-wrappers="#kt_modal_add_members_scroll" data-kt-scroll-offset="300px">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Transaction ID</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" id="iTransactionId" name="transaction_id" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Transaction ID" readonly/>
                            <!--end::Input-->
                            @error('transaction_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Member</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" id="iMember" name="member" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Member" disabled/>
                            <!--end::Input-->
                            @error('member')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Marketing</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" id="iMarketing" name="marketing" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Marketing" disabled/>
                            <!--end::Input-->
                            @error('marketing')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Amount</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" id="iAmount" name="amount" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Amount" required/>
                            <!--end::Input-->
                            @error('amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <!--end::Input group-->

                    </div>
                    <!--end::Scroll-->
                    <!--begin::Actions-->
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal" aria-label="Close" wire:loading.attr="disabled">Discard</button>
                        <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                            <span class="indicator-label" wire:loading.remove>Submit</span>
                            <span class="indicator-progress" wire:loading wire:target="submit">
                                Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
