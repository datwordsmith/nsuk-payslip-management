    <!-- ADD USER MODAL -->
    <div wire:ignore.self class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Add User</h1>
                    <button type="button" class="btn-close" wire:click ="closeModal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="storeUser()">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Fullname</label>
                            <input type="text" class="form-control" placeholder = "Fullname" wire:model.defer="name">
                            @error('name')
                                <small class="error text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" placeholder = "Email Address" wire:model.defer="email">
                            @error('email')
                                <small class="error text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <!--
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" placeholder = "Password" wire:model.defer="password">
                            @error('password')
                                <small class="error text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" placeholder = "Confirm Password" wire:model.defer="confirm_password">
                            @error('confirm_password')
                                <small class="error text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click ="closeModal" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading wire:target="storeUser" class="spinner-grow spinner-grow-sm mr-2" role="status" aria-hidden="true"></span>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END ADD MODAL -->


    <!-- EDIT USER MODAL -->
    <div wire:ignore.self class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Edit User</h1>
                    <button type="button" class="btn-close" wire:click ="closeModal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div wire:loading class="py-5">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="spinner-grow text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div wire:loading.remove>
                    <form wire:submit.prevent="updateUser()">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Fullname</label>
                                <input type="text" class="form-control" placeholder = "Fullname" wire:model.defer="name">
                                @error('name')
                                    <small class="error text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" placeholder = "Email Address" wire:model.defer="email">
                                @error('email')
                                    <small class="error text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">User Status</label>
                                @if(($admin->id != $user_id) || (!$admin->isSuperAdmin))
                                    <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" value="1" wire:model.defer="status" >
                                        <label class="form-check-label" for="status">
                                            Active
                                        </label>
                                        <span class="labels" data-on="ON" data-off="OFF"></span>
                                    </div>
                                @else
                                    <div class="alert alert-danger">
                                        You cannot Deactivate this account
                                    </div>
                                @endif

                                @error('status')
                                    <small class="error text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click ="closeModal" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-warning">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END EDIT MODAL -->

    <!-- DELETE USER MODAL -->
    <div wire:ignore.self class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Delete User</h1>
                    <button type="button" class="btn-close" wire:click ="closeModal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div wire:loading class="py-5">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="spinner-grow text-danger" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div wire:loading.remove>
                    <form wire:submit.prevent="destroyUser()">
                        <div class="modal-body">
                            <h4>Are you sure you want to delete this user?</h4>
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click ="closeModal" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END DELETE MODAL -->
