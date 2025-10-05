<div class="collapse mb-5 show" id="kt_advanced_search_form">
    <!--begin::Separator-->
    {{-- <div class="separator separator-dashed mt-9 mb-6"></div> --}}
    <!--end::Separator-->
    <!--begin::Row-->
    <div class="row g-8 mb-8">
         <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Period Date</label>
            <input type="text" class="form-control form-control form-control-solid sLastDeposit" id="periodeLastDeposit" name="last_deposit" autocomplete="off" placeholder="Select date range Transaction" tabindex="-1">
        </div>
        <!--end::Col-->

        {{-- <!--begin::Col-->
        <div class="col-xxl-4">
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
        <div class="col-xxl-3">
            <label class="fs-6 form-label fw-bold text-dark">Team</label>
            <select name="search_team" id="sTeam" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="Select Team">
                <option></option>
                <option value="WA">WA</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        <!--end::Col--> --}}

        <div class="col-xxl-1 d-flex align-items-end">
            @can('transaction-management.export')
                <!--begin::Add Member Button-->
                <a href="javascript:void(0);" class="btn btn-sm btn-success p-3 w-100" id="btnExportExcel">
                    {!! getIcon('cloud-download', 'fs-2', '', 'i') !!}
                    Export
                </a>
                <!--end::Add Member Button-->
            @endcan
        </div>
    </div>
    <!--end::Row-->
</div>
