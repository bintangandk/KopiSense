@extends('layouts.app')

@section('content')
    <div class="container-xxl grow container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Form/</span> Edit Anggota</h4>
        <div class="row">
            <div class="col-xxl">
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Edit Anggota</h5>
                    </div>
                    <form action="{{ route('data-user.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-username">Username</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-username" name="username"
                                        placeholder="Masukkan Username" value="{{ $user->username }}" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Nama</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="full_name"
                                        placeholder="Masukkan Nama Anggota"
                                        value="{{ $user->profile?->full_name ?? '-' }}" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">NIK</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="nik"
                                        placeholder="Masukkan Nama Anggota" value="{{ $user->profile?->nik ?? '-' }}" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Jenis Kelamin</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="gender"
                                        placeholder="Masukkan Nama Anggota" value="{{ $user->profile?->gender ?? '-' }}" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-email">Email</label>
                                <div class="col-sm-10">
                                    <div class="input-group input-group-merge">
                                        <input type="text" id="basic-default-email" class="form-control"
                                            placeholder="Masukkan Email" aria-label="Masukkan Email" name="email"
                                            aria-describedby="basic-default-email2" value="{{ $user->email }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-phone">No. Hp</label>
                                <div class="col-sm-10">
                                    <input type="text" id="basic-default-phone" class="form-control phone-mask"
                                        name="phone" placeholder="Masukkan No. Hp dengan format +62 799 8941"
                                        aria-label="+62 799 8941" aria-describedby="basic-default-phone"
                                        value="{{ $user->profile?->phone ?? '-' }}" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-company">Level</label>
                                <div class="col-sm-10">
                                    <input type="text" id="basic-default-company" class="form-control" name="role"
                                        placeholder="Masukkan Level Anggota" value="{{ $user->role }}" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-message">Alamat</label>
                                <div class="col-sm-10">
                                    <textarea id="basic-default-message" class="form-control" placeholder="Masukkan Alamat Anggota" name="address"
                                        aria-label="Masukkan Alamat Anggota" aria-describedby="basic-icon-default-message2">{{ $user->profile?->address ?? '-' }} </textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-company">Provinsi</label>
                                <div class="col-sm-10">
                                    <select name="province_id" id="province" class="form-select select2">
                                        <option value="{{ $user->profile?->province?->id ?? '' }}">
                                            {{ $user->profile?->province?->name ?? 'Pilih Provinsi' }} </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-company">Kota</label>
                                <div class="col-sm-10">
                                    <select name="city_id" id="city" class="form-select select2">
                                        <option value="{{ $user->profile?->city?->id ?? '' }}">
                                            {{ $user->profile?->city?->name ?? 'Pilih Kota/Kabupaten' }} </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-company">Kode POS</label>
                                <div class="col-sm-10">
                                    <input type="text" id="basic-default-company" class="form-control"
                                        name="postal_code" placeholder="Masukkan Kode POS"
                                        value="{{ $user->profile?->postal_code ?? '-' }}" />
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <x-ui.button.index type="submit" variant="primary">
                                        Simpan Perubahan
                                    </x-ui.button.index>

                                    <x-ui.button.index variant="secondary" href="{{ route('data-user') }}">
                                        Keluar
                                    </x-ui.button.index>
                                </div>
                            </div>
                        </div>
                    </form>
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
