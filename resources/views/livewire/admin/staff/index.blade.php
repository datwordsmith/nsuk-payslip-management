<div>
    @section('pagename')
        <i class="fas fa-users"></i> Staff Management
    @endsection

    <div class="bg-white rounded-lg shadow-md">
        @if (session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <div class="col-md-4">
                <!-- Manual Input Form -->
                <div class="card">
                    <div class="card-body p-4">
                        <form wire:submit.prevent="addStaff">
                            <div class="mb-3">
                                <label class="form-label">Staff ID</label>
                                <input type="text" class="form-control" wire:model="staff_id" required>
                                @error('staff_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" wire:model="email" required>
                                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Add Staff</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Excel Import Form -->
                <div class="card mt-4">
                    <div class="card-body p-4">
                        <form wire:submit.prevent="importExcel">
                            <div class="mb-3">
                                <label class="form-label">Import from Excel</label>
                                <input type="file" class="form-control" wire:model="excelFile">
                                @error('excelFile') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-success">
                                    <span wire:loading wire:target="importExcel" class="spinner-grow spinner-grow-sm mr-2"></span>
                                    Import Excel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8 d-flex align-items-stretch">
                <div class="card w-100">
                    <div class="card-body p-4">

                        <!-- Search -->
                        <div class="my-3">
                            <input type="text" class="form-control" wire:model="search" placeholder="Search staff...">
                        </div>

                        <!-- Staff List Table -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Staff ID</th>
                                        <th>Email</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staffList as $staff)
                                        <tr>
                                            <td>{{ $staff->staff_id }}</td>
                                            <td>{{ $staff->email }}</td>
                                            <td>
                                                <button wire:click="delete({{ $staff->id }})"
                                                    class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mt-4">
                                {{ $staffList->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
