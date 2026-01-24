@extends('layouts.app')

@section('content')
    <div class="container-xxl grow container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Forms/</span> Tambah Anggota</h4>
        <div class="row">
            <div class="col-xxl">
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Buat Anggota</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('data-user.store') }}">
                            @csrf
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Lengkap</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="full_name" name="full_name"
                                        placeholder="Masukkan Nama Lengkap Anggota" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Username</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="username" name="username"
                                        placeholder="Masukkan Username Anggota" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">NIK</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="nik" name="nik"
                                        placeholder="Masukkan NIK Anggota" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Gender</label>
                                <div class="col-sm-10">
                                    <select name="gender" id="gender" class="form-select">
                                        <option value="">Pilih Gender</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-company">Level</label>
                                <div class="col-sm-10">
                                    <select name="role" id="role" class="form-select">
                                        <option value="">Pilih Level</option>
                                        <option value="pegawai">Pegawai</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-email">Email</label>
                                <div class="col-sm-10">
                                    <div class="input-group input-group-merge">
                                        <input type="text" id="email" name="email" class="form-control"
                                            placeholder="Masukkan Email" aria-label="Masukkan Email"
                                            aria-describedby="basic-default-email2" />
                                        <span class="input-group-text" id="basic-default-email2">@example.com</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-phone">No. Hp</label>
                                <div class="col-sm-10">
                                    <input type="text" id="phone" name="phone" class="form-control phone-mask"
                                        placeholder="Masukkan No. Hp dengan format +62 799 8941" aria-label="+62 799 8941"
                                        aria-describedby="basic-default-phone" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-message">Alamat</label>
                                <div class="col-sm-10">
                                    <textarea id="address" name="address" class="form-control" placeholder="Masukkan Alamat Anggota"
                                        aria-label="Masukkan Alamat Anggota" aria-describedby="basic-icon-default-message2"></textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-company">Provinsi</label>
                                <div class="col-sm-10">
                                    <select name="province_id" id="province" class="form-select select2">
                                        <option value="">Pilih Provinsi</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-message">Kota/Kabupaten</label>
                                <div class="col-sm-10">
                                    <select name="city_id" id="city" class="form-select select2">
                                        <option value="">Pilih Kota/Kabupaten</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-message">Kode Pos </label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="postal_code" name="postal_code"
                                        placeholder="Masukkan Kode Pos" />
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <x-ui.button.index variant="primary" type="submit">
                                        Simpan
                                    </x-ui.button.index>
                                    <x-ui.button.index variant="secondary" href="{{ route('data-user') }}">
                                        Batal
                                    </x-ui.button.index>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Use relative URL for API
            const apiUrl = '/api';

            // Initialize Select2 for province
            $('#province').select2({
                placeholder: 'Cari dan Pilih Provinsi',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: apiUrl + '/provinces',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            // Initialize Select2 for city
            $('#city').select2({
                placeholder: 'Cari dan Pilih Kota/Kabupaten',
                allowClear: true,
                width: '100%'
            });

            // Update cities when province changes
            $('#province').on('change', function() {
                const provinceId = $(this).val();
                const citySelect = $('#city');

                // Clear city select
                citySelect.empty().append('<option value="">Pilih Kota/Kabupaten</option>');
                citySelect.val('').trigger('change');

                // If no province selected, return
                if (!provinceId) {
                    return;
                }

                // Fetch cities for selected province
                $.ajax({
                    url: apiUrl + '/provinces/' + provinceId + '/cities',
                    type: 'GET',
                    dataType: 'json',
                    success: function(cities) {
                        cities.forEach(function(city) {
                            citySelect.append(
                                '<option value="' + city.id + '">' + city.name +
                                '</option>'
                            );
                        });
                    },
                    error: function() {
                        alert('Gagal memuat data kota/kabupaten');
                    }
                });
            });
        });
    </script>
@endpush
