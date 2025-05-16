<div>


    @section('pagename')
        <i class="fas fa-file-invoice"></i> Payslips
    @endsection

    <div class="bg-white rounded-lg shadow-md">
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

        <div class="row">
            <div class="col-md-12 d-flex align-items-stretch">
                <div class="card w-100">
                    <div class="card-body p-4">
                        <form wire:submit.prevent="upload" class="row row-cols-md-auto g-3 align-items-end mb-6">

                                <div class="col-md-9">
                                    <label class="form-label">
                                        Select Files
                                    </label>
                                    <input type="file"
                                        wire:model="files"
                                        multiple
                                        class="form-control"
                                    >
                                    @error('files.*')
                                        <small class="error text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <button type="submit"
                                        class="btn btn-primary w-100 w-md-auto"
                                        wire:loading.attr="disabled">
                                        <span wire:loading wire:target="upload" class="spinner-grow spinner-grow-sm mr-2" role="status" aria-hidden="true"></span>
                                        Upload Files
                                    </button>
                                </div>
                        </form>


                        <div class="mt-5 mb-3">
                            <input type="text" class="form-control" wire:model="search" placeholder="Search...">
                        </div>
                        <div class="mb-2">
                            <strong>Total files uploaded:</strong> {{ $fileUploads->total() }}
                        </div>
                        <div class="table-responsive">
                            <table id="category_table" class="table table-striped align-items-center mb-0" style="width:100%">
                                <thead class="table-dark">
                                    <tr class="">
                                        <th class="text-secondary text-xs font-weight-semibold opacity-7 col-2">Filename</th>
                                        <th class="text-secondary text-xs font-weight-semibold opacity-7 col-1">Staff ID</th>
                                        <th class="text-secondary text-xs font-weight-semibold opacity-7 col-2">Month</th>
                                        <th class="text-secondary text-xs font-weight-semibold opacity-7 col-2">Type</th>
                                        <th class="text-secondary text-xs font-weight-semibold opacity-7 col-1">Size</th>
                                        <th class="text-secondary text-xs font-weight-semibold opacity-7 col-2">Date Uploaded</th>
                                        <th class="text-secondary text-xs font-weight-semibold opacity-7 col-1">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fileUploads as $file)
                                        <tr>
                                            <td class="">{{ $file->name }}</td>
                                            <td class="">{{ $file->staff_id }}</td>
                                            <td class="">{{ date('M', mktime(0, 0, 0, $file->month, 1)) . ' ' . $file->year }}</td>
                                            <td class="">{{ $file->type }}</td>
                                            <td class="">{{ number_format($file->size / 1024, 2) }} KB</td>
                                            <td class="">{{ $file->created_at->format('d M, y H:i') }}</td>
                                            <td class="">
                                                <button wire:click="delete({{ $file->id }})"
                                                    class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mt-4">
                                {{ $fileUploads->links() }}
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
