<x-default-layout>
    @section('title')
        Allowed IPs
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2 class="fw-bold">Allowed IPs Configuration</h2>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_update_ips">
                    {!! getIcon('pencil', 'fs-2', '', 'i') !!}
                    Update Allowed IPs
                </button>
            </div>
        </div>

        <div class="card-body py-4">
            <ul class="list-group" id="allowed-ips-list">
                @forelse($ips as $ip)
                    <li class="list-group-item">
                        {!! getIcon('shield', 'fs-3 text-success me-2') !!}
                        {{ $ip }}
                    </li>
                @empty
                    <li class="list-group-item text-muted">No IPs configured yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="kt_modal_update_ips" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Update Allowed IPs</h2>
                    <button type="button" class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                        {!! getIcon('cross', 'fs-1') !!}
                    </button>
                </div>

                <div class="modal-body py-10 px-lg-17">
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Allowed IP Addresses</label>
                        <textarea id="allowed_ips_input" class="form-control form-control-solid" rows="6" placeholder="One IP per line">{{ implode("\n", $ips) }}</textarea>
                        <div class="form-text">Example: <code>192.168.1.10</code><br><code>103.120.45.22</code></div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="btn-save-ips" class="btn btn-primary">
                        <span class="indicator-label">Save</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('btn-save-ips').addEventListener('click', function () {
            let ips = document.getElementById('allowed_ips_input').value;

            fetch("{{ route('settings.config.ips.update') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    allowed_ips: ips
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // update list dynamically
                    let list = document.getElementById('allowed-ips-list');
                    list.innerHTML = "";
                    if (data.ips.length === 0) {
                        list.innerHTML = '<li class="list-group-item text-muted">No IPs configured yet.</li>';
                    } else {
                        data.ips.forEach(ip => {
                            let li = document.createElement('li');
                            li.classList.add('list-group-item');
                            li.innerHTML = `{!! getIcon('shield', 'fs-3 text-success me-2') !!} ${ip}`;
                            list.appendChild(li);
                        });
                    }

                    // close modal
                    bootstrap.Modal.getInstance(document.getElementById('kt_modal_update_ips')).hide();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong!'
                });
            });
        });
    </script>
    @endpush
</x-default-layout>
