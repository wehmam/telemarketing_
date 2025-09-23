<div class="collapse mb-5" id="kt_advanced_search_form">
    <!--begin::Separator-->
    <div class="separator separator-dashed mt-9 mb-6"></div>
    <!--end::Separator-->
    <!--begin::Row-->
    <div class="row g-8 mb-8">
        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Account Name</label>
            <input type="text" class="form-control form-control form-control-solid" id="sNamaRekening" name="search_nama_rekening" placeholder="Bank Account Name Member" tabindex="-1">
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Username</label>
            <input type="text" class="form-control form-control form-control-solid" id="sUsername" name="search_username" placeholder="Username Member" tabindex="-1">
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Phone No</label>
            <input type="text" class="form-control form-control form-control-solid" id="sPhone" name="search_phone" placeholder="Phone No Member" tabindex="-1">
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Total Deposit</label>
            <input type="text" class="form-control form-control form-control-solid" id="totalDeposit" name="total_deposit" placeholder="Nominal Deposit" tabindex="-1">
        </div>
        <!--end::Col-->

         <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Last Deposit</label>
            <input type="text" class="form-control form-control form-control-solid" id="periodeLastDeposit" name="last_deposit" placeholder="Periode Last Deposit" tabindex="-1">
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Marketing</label>
            <select name="search_marketing" id="sMarketing" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Marketing">
                <option></option>
                @foreach ($marketings as $marketing)
                    <option value="{{ $marketing->id }}">{{ $marketing->name }}</option>
                @endforeach
            </select>
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Team</label>
            <select name="search_team" id="sTeam" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Team">
                <option></option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Type Member</label>
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
                    <input type="radio" class="btn-check sStatus" name="search_status" value="wa">
                    <span class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-bold px-4">WA</span>
                </label>
                <!--end::Option-->
                <!--begin::Option-->
                <label>
                    <input type="radio" class="btn-check sStatus" name="search_status" value="has_team">
                    <span class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-bold px-4">Has Team</span>
                </label>
                <!--end::Option-->
            </div>
            <!--end::Radio group-->
        </div>
        <!--end::Col-->


        {{-- <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Status Member</label>
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
                    <input type="radio" class="btn-check sStatus" name="search_status" value="my_team_only">
                    <span class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-bold px-4">My Team Only</span>
                </label>
                <!--end::Option-->
                <!--begin::Option-->
                <label>
                    <input type="radio" class="btn-check sStatus" name="search_status" value="my_members_only">
                    <span class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-bold px-4">My Members Only</span>
                </label>
                <!--end::Option-->
            </div>
            <!--end::Radio group-->
        </div>
        <!--end::Col--> --}}


    </div>
    <!--end::Row-->
</div>
