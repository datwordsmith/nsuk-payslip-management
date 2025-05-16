<div>
    @include('livewire.admin.payslip-dispatch.modal')

    @section('pagename')
        <i class="fas fa-mail-bulk"></i> Dispatch Payslips
    @endsection

    <div class="bg-white rounded-lg shadow-md">
        @if (session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <div class="card">
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Month</label>
                        <select wire:model="month" class="form-select" required>
                            <option value="">Select Month</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Year</label>
                        <select wire:model="year" class="form-select" required>
                            <option value="">Select Year</option>
                            @foreach(range(date('Y')-2, date('Y')) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button"
                            class="btn btn-primary w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmDispatchModal"
                            @if(!$month || !$year) disabled @endif>
                            Dispatch Payslips
                        </button>
                    </div>
                </div>

                <div class="my-3">
                    <input type="text" class="form-control" wire:model="search" placeholder="Search dispatches...">
                </div>
                <div>
                    <button wire:click="exportToExcel" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Staff ID</th>
                                <th>Email</th>
                                <th>Month/Year</th>
                                <th>Status</th>
                                <th>Sent At</th>
                                <th>Sent By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dispatches as $dispatch)
                                <tr>
                                    <td>{{ $dispatch->staff_id }}</td>
                                    <td>{{ $dispatch->email }}</td>
                                    <td>{{ date('F Y', mktime(0, 0, 0, $dispatch->month, 1, $dispatch->year)) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $dispatch->status === 'sent' ? 'success' : 'danger' }}">
                                            {{ ucfirst($dispatch->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $dispatch->sent_at ? $dispatch->sent_at->format('d M, Y H:i') : '-' }}</td>
                                    <td>{{ $dispatch->user->name }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning"
                                                wire:click="resend({{ $dispatch->id }})"
                                                wire:loading.attr="disabled">
                                            <span wire:loading wire:target="resend({{ $dispatch->id }})" class="spinner-grow spinner-grow-sm mr-2"></span>
                                            Resend
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $dispatches->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
