@extends('layouts.app')

@section('content')
    <div class="container-xxl grow container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Data /</span> Data Anggota</h4>
        <div class="card">
            <div class="card-body pb-0">
                <form method="GET" action="{{ route('data-user') }}" id="searchForm">
                    <div class="row mb-3">

                        <div class="col-md-3 d-flex gap-2">
                            <!-- Search Input -->
                            <x-ui.searchInput.index name="search" placeholder="Cari data anggota..."
                                value="{{ request('search') }}" class="mb-4" />

                            {{-- Refresh Button --}}
                            <x-ui.buttonRefresh.index id="refreshDataUser" class="btn-sm" />
                        </div>
                        <div class="col-md-9 d-flex justify-content-end">
                            {{-- Add Button --}}
                            <x-ui.button.index href="{{ route('data-user.create') }}" variant="primary" icon="bx bx-plus">
                                Tambah Anggota
                            </x-ui.button.index>
                        </div>
                    </div>
                </form>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr class="text-nowrap">
                            <th class="text-center">No</th>
                            <th class="text-center">Nama</th>
                            <th class="text-center">No. Hp</th>
                            <th class="text-center">Level</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBodyUser">
                        @forelse ($users as $user)
                            <tr>
                                <td class="text-center">
                                    {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                <td class="text-center">{{ $user->profile->full_name ?? '-' }}</td>
                                <td class="text-center">{{ $user->profile->phone ?? '-' }}</td>
                                <td class="text-center">{{ $user->role }}</td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('data-user.show', $user->id) }}"><i
                                                    class="bx bx-info-circle me-1"></i>
                                                Detail</a>
                                            <a class="dropdown-item" href="javascript:void(0);"><i
                                                    class="bx bx-edit-alt me-1"></i> Edit</a>
                                            <form action="{{ route('data-user.destroy', $user->id) }}" method="POST"
                                                class="form-delete" style="display:inline;">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="dropdown-item">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <x-ui.pagination.index :items="$users" id="paginationUser" />
            {{-- /Pagination --}}
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const refreshBtn = document.getElementById('refreshDataUser');
                const searchForm = document.getElementById('searchForm');

                if (refreshBtn && searchForm) {
                    refreshBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        searchForm.querySelectorAll('input[type="text"]').forEach(input => {
                            input.value = '';
                        });
                        searchForm.submit();
                    });
                }

                // ================= DELETE CONFIRMATION =================
                document.querySelectorAll('.form-delete').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        Swal.fire({
                            title: 'Yakin ingin menghapus?',
                            text: 'Data tidak bisa dikembalikan',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, hapus',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });

                // ================= FLASH SUCCESS =================
                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: @json(session('success')),
                        timer: 2000,
                        showConfirmButton: false
                    });
                @endif

                // ================= FLASH ERROR =================
                @if (session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: @json(session('error'))
                    });
                @endif

            });
        </script>
    @endpush
@endsection
