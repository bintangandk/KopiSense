@extends('layouts.app')

@section('content')
    <div class="container-xxl grow container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Data /</span> Data Anggota</h4>
        <div class="card">
            <div class="card-body pb-0">
                <div class="row mb-3">
                    <div class="col-md-3 d-flex gap-2">
                        <!-- Search Input -->
                        <x-ui.searchInput.index placeholder="Cari data anggota..." class="mb-4" />

                        {{-- Refresh Button --}}
                        <x-ui.buttonRefresh.index id="refreshCDataUser" class="mb-3" />
                    </div>
                    <div class="col-md-9 d-flex justify-content-end">
                        {{-- Add Button --}}
                        <x-ui.button.index variant="primary" icon="bx bx-plus">Tambah
                            Anggota</x-ui.button.index>
                    </div>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr class="text-nowrap">
                            <th>No</th>
                            <th>Nama</th>
                            <th>No. Hp</th>
                            <th>Level</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBodyUser">
                        <tr>
                            <th scope="row">1</th>
                            <td>F Jonh Kenedy</td>
                            <td>+62 8123456789</td>
                            <td><span class="badge bg-label-primary me-1">Pegawai</span></td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0);"><i
                                                class="bx bx-info-circle me-1"></i>
                                            Detail</a>
                                        <a class="dropdown-item" href="javascript:void(0);"><i
                                                class="bx bx-edit-alt me-1"></i> Edit</a>
                                        <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-trash me-1"></i>
                                            Delete</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">2</th>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                        </tr>
                        <tr>
                            <th scope="row">3</th>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                        </tr>
                        <tr>
                            <th scope="row">4</th>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                        </tr>
                        <tr>
                            <th scope="row">5</th>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                        </tr>
                        <tr>
                            <th scope="row">6</th>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                        </tr>
                        <tr>
                            <th scope="row">7</th>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                        </tr>
                        <tr>
                            <th scope="row">8</th>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                        </tr>
                        <tr>
                            <th scope="row">9</th>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                        </tr>
                        <tr>
                            <th scope="row">10</th>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                            <td>Table cell</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <x-ui.pagination.index id="paginationUser" />
            {{-- /Pagination --}}
        </div>
    </div>

    <script>
        // Pagination Class
        document.addEventListener('DOMContentLoaded', function() {
            const userPaginator = new TablePaginator('tableBodyUser', 'paginationUser', 5);
        });

        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            filteredRows.length = 0;

            allRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchValue)) {
                    filteredRows.push(row.cloneNode(true));
                }
            });

            currentPage = 1;
            renderTable();
            renderPagination();
        });

        // Refresh button functionality
        document.getElementById('refreshBtn').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            currentPage = 1;
            filteredRows.length = 0;
            filteredRows.push(...allRows);
            renderTable();
            renderPagination();
        });
    </script>
@endsection
