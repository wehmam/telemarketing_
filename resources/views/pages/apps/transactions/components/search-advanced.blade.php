<div class="collapse mb-5" id="kt_advanced_search_form">
    <!--begin::Separator-->
    <div class="separator separator-dashed mt-9 mb-6"></div>
    <!--end::Separator-->
    <!--begin::Row-->
    <div class="row g-8 mb-8">
        <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Account Name</label>
            <input type="text" class="form-control form-control form-control-solid" id="sNamaRekening" name="search_nama_rekening" tabindex="-1">
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Username</label>
            <input type="text" class="form-control form-control form-control-solid" id="sUsername" name="search_username" tabindex="-1">
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Phone No</label>
            <input type="text" class="form-control form-control form-control-solid" id="sPhone" name="search_phone" tabindex="-1">
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Status Transactions</label>
            <!--begin::Radio group-->
            <div class="nav-group nav-group-fluid">
                <!--begin::Option-->
                <label>
                    <input type="radio" class="btn-check sStatus" name="search_status" value="all" checked="checked">
                    <span class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-bold px-4">All</span>
                </label>
                <!--end::Option-->
                <!--begin::Option-->
                <label>
                    <input type="radio" class="btn-check sStatus" name="search_status" value="DEPOSIT">
                    <span class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-bold px-4">DEPOSIT</span>
                </label>
                <!--end::Option-->
                <!--begin::Option-->
                <label>
                    <input type="radio" class="btn-check sStatus" name="search_status" value="REDEPOSIT">
                    <span class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-bold px-4">REDEPOSIT</span>
                </label>
                <!--end::Option-->
                <!--begin::Option-->
                <label>
                    <input type="radio" class="btn-check sStatus" name="search_status" value="DELETED">
                    <span class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-bold px-4">DELETED</span>
                </label>
                <!--end::Option-->
            </div>
            <!--end::Radio group-->
        </div>
        <!--end::Col-->

         <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Last Deposit</label>
            <input type="text" class="form-control form-control form-control-solid sLastDeposit" id="periodeLastDeposit" name="last_deposit" placeholder="Select date range Deposit" tabindex="-1">
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
</div>
