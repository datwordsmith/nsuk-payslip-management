<div>
    @include('livewire.admin.user.modal-form')

    @section('pagename')
        <i class="fas fa-users" style="color: #229635;"></i> Active Users
    @endsection

    @section('breadcrumbs')
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('t/users')}}">Users</a></li>
            <li class="breadcrumb-item active" aria-current="page">Active</li>
            <li class="breadcrumb-item"><a href="{{ url('t/users/inactive') }}">Inactive</a></li>
            @if ($admin->isAdmin)
                <li class="breadcrumb-item"><a href="{{ url('t/users/admin')}}">Admin</a></li>
            @endif
        </ol>
    @endsection

    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body p-4">
                    <div class="col-md-12 d-flex mb-2">
                        {{-- <div class="ms-auto">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#addUserModal" class="btn btn-sm btn-primary text-white"><i class="fas fa-plus-square pr-4"></i>&nbsp;&nbsp; Add User</a>
                        </div> --}}
                    </div>
                    @if (session('message'))
                        <div class="alert alert-success" role="alert">
                            {{ session('message') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table id="ActiveUsers" class="table table-striped dt-responsive nowrap" style="width:100%">
                            <thead class="table-dark">
                                <tr class="">
                                    <th class="text-secondary text-xs font-weight-semibold opacity-7 col-4">Name</th>
                                    <th class="text-secondary text-xs font-weight-semibold opacity-7 col-4">Email Address</th>
                                    <th class="text-secondary text-xs font-weight-semibold opacity-7 col-1 text-center">Status</th>
                                    @if ($admin->isAdmin)
                                        <th class="text-secondary text-xs font-weight-semibold opacity-7 col-1">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                <tr>
                                    <td>{{$user->name}} @if($user->isAdmin) <span class="badge text-bg-success ml-2">Admin</span> @endif</td>
                                    <td>{{$user->email}}</td>
                                    <td class="text-center">

                                        @if($user->status == '1')
                                            <i class="far fa-check-circle text-success"></i>
                                        @else
                                            <i class="fas fa-ban text-danger"></i>
                                        @endif
                                    </td>
                                    @if ($admin->isAdmin)
                                        <td class="">
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <a href="#" wire:click="editUser({{$user->id}})" data-bs-toggle="modal" data-bs-target="#editUserModal" class="btn btn-sm btn-warning text-white"><i class="fas fa-pen"></i></a>
                                                @if($user->isAdmin)

                                                @else
                                                    <a href="#" wire:click="deleteUser({{$user->id}})" data-bs-toggle="modal" data-bs-target="#deleteUserModal" class="btn btn-sm btn-danger text-white"><i class="fas fa-trash-alt"></i></a>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-danger">No Record Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

@section('scripts')
    {{-- <script>
        $(document).ready(function () {
            $('#ActiveUsers').DataTable();
        });
    </script> --}}

    <script>
        $(document).ready(function() {

            window.addEventListener('close-modal', event => {
                $('.modal').modal('hide');
            });

            $(document).on('hidden.bs.modal', '.modal', function () {
                console.log("Modal hidden");
                livewire.emit('reloadData');
                console.log("DT Reset");
            });

            window.addEventListener('init-datatable', function () {
                $('#ActiveUsers').DataTable();
            });

        });
    </script>
@endsection
