<div class="collapse mb-5" id="kt_advanced_search_form">
    <!--begin::Separator-->
    <div class="separator separator-dashed mt-9 mb-6"></div>
    <!--end::Separator-->
    <!--begin::Row-->
    <div class="row g-8 mb-8">
         <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Activity Date</label>
            <input type="text" class="form-control form-control form-control-solid sActivityDate" id="activityDate" name="activity_date" placeholder="Select date range Activity" tabindex="-1">
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Marketing</label>
            <select name="search_marketing" id="sMarketing" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="Select Marketing">
                <option></option>
                @foreach ($marketings as $marketing)
                    <option value="{{ $marketing->id }}">{{ $marketing->name }}</option>
                @endforeach
            </select>
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-4">
            <label class="fs-6 form-label fw-bold text-dark">Team</label>
            <select name="search_team" id="sTeam" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="Select Team">
                <option></option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
</div>
