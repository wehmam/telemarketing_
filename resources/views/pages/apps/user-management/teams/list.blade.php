<x-default-layout>

    @section('title')
        Teams
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('user-management.teams.index') }}
    @endsection

    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-5 g-xl-9">
            @foreach($teams as $team)
                <!--begin::Col-->
                <div class="col-md-4">
                    <!--begin::Card-->
                    <div class="card card-flush h-md-100">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h2>{{ ucwords($team->name) }}</h2>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-1">
                            <!--begin::Users-->
                            <div class="fw-bold text-gray-600 mb-5">Total users with this role: {{ $team->members->count() }}</div>
                            <!--end::Users-->
                            <!--begin::Permissions-->
                            <div class="d-flex flex-column text-gray-600">
                                <div class="d-flex align-items-center py-2">
                                    <span class="bullet bg-danger me-3"></span> {{ $team->leader->name ?? "-" }} ({{ $team->leader->role_name ?? "" }})
                                </div>
                                @foreach($team->members ?? [] as $member)
                                    <div class="d-flex align-items-center py-2">
                                        <span class="bullet bg-primary me-3"></span>{{ ucfirst($member->name) }} ({{ $member->role_name ?? "" }})
                                    </div>
                                @endforeach
                            </div>
                            <!--end::Permissions-->
                        </div>
                        <!--end::Card body-->
                        <!--begin::Card footer-->
                        <div class="card-footer flex-wrap pt-0">
                            <a href="{{ route('user-management.roles.show', $team) }}" class="btn btn-light btn-active-primary my-1 me-2">View Team</a>
                            <button type="button" class="btn btn-light btn-active-light-primary my-1" data-role-id="{{ $team->name }}" data-bs-toggle="modal" data-bs-target="#kt_modal_update_team">Edit Team</button>
                        </div>
                        <!--end::Card footer-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Col-->
            @endforeach

            <!--begin::Add new card-->
            <div class="ol-md-4">
                <!--begin::Card-->
                <div class="card h-md-100">
                    <!--begin::Card body-->
                    <div class="card-body d-flex flex-center">
                        <!--begin::Button-->
                        <button type="button" class="btn btn-clear d-flex flex-column flex-center" data-bs-toggle="modal" data-bs-target="#kt_modal_update_team">
                            <!--begin::Illustration-->
                            <img src="{{ image('illustrations/sketchy-1/4.png') }}" alt="" class="mw-100 mh-150px mb-7"/>
                            <!--end::Illustration-->
                            <!--begin::Label-->
                            <div class="fw-bold fs-3 text-gray-600 text-hover-primary">Add New Team</div>
                            <!--end::Label-->
                        </button>
                        <!--begin::Button-->
                    </div>
                    <!--begin::Card body-->
                </div>
                <!--begin::Card-->
            </div>
            <!--begin::Add new card-->
        </div>

    </div>
    <!--end::Content container-->

    <!--begin::Modal-->
    {{-- <livewire:permission.role-modal></livewire:permission.role-modal> --}}
    <div class="modal fade" id="kt_modal_update_team" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-750px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2 class="fw-bold">Teams</h2>
                    <!--end::Modal title-->
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal" aria-label="Close">
                        {!! getIcon('cross','fs-1') !!}
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body scroll-y mx-5 my-7">
                    <!--begin::Form-->
                    <form id="kt_modal_team_form" class="form" action="#">
                        @csrf

                        <input type="hidden" name="id" id="team_id"> <!-- for update -->
                        <!--begin::Scroll-->
                        <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_update_team_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
                            data-kt-scroll-dependencies="#kt_modal_update_team_header" data-kt-scroll-wrappers="#kt_modal_update_team_scroll" data-kt-scroll-offset="300px">
                            <!--begin::Input group-->
                            <div class="fv-row mb-10">
                                <!--begin::Label-->
                                <label class="fs-5 fw-bold form-label mb-2">
                                    <span class="required">Team name</span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input class="form-control form-control-solid" placeholder="Enter a team name" name="team_name"/>
                                <!--end::Input-->
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="fv-row mb-10">
                                <label class="fs-5 fw-bold form-label mb-2">
                                    <span class="required">Leader</span>
                                </label>
                                <select class="form-select form-select-solid" name="team_leader" id="leader_id" data-control="select2" data-placeholder="Select leader">
                                    @foreach($leaders as $lead)
                                        <option value="{{ $lead->id }}">{{ $lead->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Each team can only have 1 leader.</div>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="fv-row mb-10">
                                <label class="fs-5 fw-bold form-label mb-2">
                                    <span class="required">Select Users</span>
                                </label>
                                <select class="form-select form-select-solid" name="team_members[]" id="users" multiple="multiple" data-control="select2" data-placeholder="Select users">
                                    @foreach($marketings as $marketing)
                                        <option value="{{ $marketing->id }}">{{ $marketing->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">You can select multiple users (one will be the leader, others are members).</div>
                            </div>
                            <!--end::Input group-->
                        </div>
                        <!--end::Scroll-->
                        <!--begin::Actions-->
                        <div class="text-center pt-15">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal" aria-label="Close" wire:loading.attr="disabled">Discard</button>
                            <button type="submit" class="btn btn-primary">
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

    @push('scripts')
        <script>
            const modal = document.querySelector('#kt_modal_update_team');
            modal.addEventListener('show.bs.modal', (e) => {
            //     Livewire.emit('modal.show.role_name', e.relatedTarget.getAttribute('data-role-id'));
            });


            $(document).ready(function () {
                $('#kt_modal_team_form').on('submit', function (e) {
                    e.preventDefault();

                    let id = $('#teamId').val();
                    let url = '';
                    let method = '';

                    if (id) {
                        // Update
                        url = `/user-management/teams/${id}`;
                        method = 'PUT';
                    } else {
                        // Create
                        url = `/user-management/teams`;
                        method = 'POST';
                    }

                    showLoadPage();
                    $.ajax({
                        url: url,
                        method: method,
                        data: $(this).serialize(),
                        success: function (res) {
                            hideLoadPage();
                            if(res.status) {
                                toastr.success(res.message);
                                setTimeout(() => {
                                    window.location.reload();
                                }, 500);
                            } else {
                                toastr.error(res.message);
                            }
                        },
                        error: function (xhr) {
                            hideLoadPage();
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                let messages = Object.values(errors).flat().join('<br>') ?? "Something Wrong!";
                                toastr.error(messages);
                            } else {
                                toastr.error("Something went wrong");
                            }
                        }
                    });
                });
            });
        </script>
    @endpush
    <!--end::Modal-->

</x-default-layout>
