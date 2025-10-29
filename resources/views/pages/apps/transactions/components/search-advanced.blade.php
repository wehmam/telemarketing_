<div class="collapse mb-5" id="kt_advanced_search_form">
    <!--begin::Separator-->
    <div class="separator separator-dashed mt-9 mb-6"></div>
    <!--end::Separator-->
    <!--begin::Row-->
    <div class="row g-8 mb-8">
        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Account Name</label>
            <input type="text" class="form-control form-control form-control-solid" id="sNamaRekening" name="search_nama_rekening" tabindex="-1">
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Username</label>
            <input type="text" class="form-control form-control form-control-solid" id="sUsername" name="search_username" tabindex="-1">
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Phone No</label>
            <input type="text" class="form-control form-control form-control-solid" id="sPhone" name="search_phone" tabindex="-1">
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Amount Transaction</label>
            <input type="text" class="form-control form-control form-control-solid" id="amountDeposit" name="amount_deposit" placeholder="Nominal Amount Deposit" tabindex="-1">
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Transactions</label>
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
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Transaction Date</label>
            <input type="text" class="form-control form-control form-control-solid sLastDeposit" id="periodeLastDeposit" name="last_deposit" placeholder="Select date range Transaction" tabindex="-1">
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Marketing</label>
            <select name="search_marketing" id="sMarketing" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="Select Marketing">
                <option></option>
                <option value="WA">WA</option>
                @foreach ($marketings as $marketing)
                    <option value="{{ $marketing->id }}">{{ $marketing->name }}</option>
                @endforeach
            </select>
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-3 d-none">
            <label class="fs-6 form-label fw-bold text-dark">Team</label>
            <select name="search_team" id="sTeam" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="Select Team">
                <option></option>
                <option value="WA">WA</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
</div>
