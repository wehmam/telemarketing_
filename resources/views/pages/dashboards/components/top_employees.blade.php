<!--begin::Card widget 17-->
<div class="card card-flush h-md-50 mb-5 mb-xl-10">
	<!--begin::Header-->
	<div class="card-header pt-1">
		<!--begin::Title-->
		<div class="card-title d-flex flex-column">
			{{-- <!--begin::Info-->
			<div class="d-flex align-items-center">
				<!--begin::Currency-->
				<span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">Rp. </span>
				<!--end::Currency-->
				<!--begin::Amount-->
				<span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ $topMembers->sum('transactions_sum_amount') }}</span>
				<!--end::Amount-->
				<!--begin::Badge-->
				<span class="badge badge-light-success fs-base">{!! getIcon('arrow-up', 'fs-5 text-success ms-n1') !!} 2.2%</span>
				<!--end::Badge-->
			</div>
			<!--end::Info--> --}}
			<!--begin::Subtitle-->
			<span class="fw-bold fs-2">Top Employees</span>
			<!--end::Subtitle-->
		</div>
		<!--end::Title-->
	</div>
	<!--end::Header-->
	<!--begin::Card body-->
	<div class="card-body d-flex flex-wrap align-items-center">
		<!--begin::Labels-->
		<div class="d-flex flex-column content-justify-center flex-row-fluid">
            @foreach ($topEmployees as $employee)
                <!--begin::Label-->
                <div class="d-flex fw-semibold align-items-center my-1">
                    <!--begin::Bullet-->
                    <div class="bullet w-8px h-3px rounded-2 bg-success me-3"></div>
                    <!--end::Bullet-->
                    <!--begin::Label-->
                    <div class="text-gray-500 flex-grow-1 me-4 fs-5">{{ $employee->name }}</div>
                    <!--end::Label-->
                    <!--begin::Stats-->
                    <div class="fw-bolder text-gray-700 text-xxl-end">{{ $employee->members_count }} - Members</div>
                    <!--end::Stats-->
                </div>
                <!--end::Label-->
            @endforeach
		</div>
		<!--end::Labels-->
	</div>
	<!--end::Card body-->
</div>
<!--end::Card widget 17-->
