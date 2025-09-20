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

         {{-- <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Marketing Name</label>
            <input type="text" class="form-control form-control form-control-solid" id="sMarketing" name="search_marketing" tabindex="-1">
        </div>
        <!--end::Col--> --}}

         <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Marketing</label>
            <select name="search_marketing" id="sMarketing" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Marketing">
                <option></option>
                @foreach ($marketings as $marketing)
                    <option value="{{ $marketing->id }}">{{ $marketing->name }}</option>
                @endforeach
            </select>
        </div>
        <!--end::Col-->

        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Team</label>
                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" data-allow-clear="true" data-hide-search="true" id="sTeam" name="search_team" tabindex="-1">
                    <option></option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
        </div>

         {{-- <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Last Deposit</label>
            <input type="text" class="form-control form-control form-control-solid" id="periodeLastDeposit" name="last_deposit" tabindex="-1">
        </div>
        <!--end::Col--> --}}
    </div>
    <!--end::Row-->
</div>
