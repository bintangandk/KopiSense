@php
    $profile = $user->profile;
    $fullName = trim($profile?->full_name ?? '');
    $nameParts = $fullName !== '' ? explode(' ', $fullName, 2) : [];
    $firstName = old('first_name', $nameParts[0] ?? '');
    $lastName = old('last_name', $nameParts[1] ?? '');
    $email = old('email', $user->email);
    $phone = old('phone', $profile?->phone ?? '');
    $address = old('address', $profile?->address ?? '');
    $postalCode = old('postal_code', $profile?->postal_code ?? '');
    $selectedProvinceId = old('province_id', $profile?->province?->id);
    $selectedProvinceName = old('province_name', $profile?->province?->name ?? '');
    $selectedCityId = old('city_id', $profile?->city?->id);
    $selectedCityName = old('city_name', $profile?->city?->name ?? '');
    $removeProfilePicture = old('remove_profile_picture', '0');
    $defaultAvatar = asset('assets/img/avatars/1.png');
    $profilePictureUrl = $profile?->profile_picture ? asset('storage/' . $profile->profile_picture) : $defaultAvatar;
@endphp

<div class="card mb-4">
    <h5 class="card-header">
        Detail Profil Pengguna
    </h5>
    <!-- Account -->
    <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-4">
            <img src="{{ $profilePictureUrl }}" alt="user-avatar" class="d-block rounded" height="100" width="100"
                id="uploadedAvatar" />
            <div class="button-wrapper">
                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                    <span class="d-none d-sm-block">Ganti Foto Profil</span>
                    <i class="bx bx-upload d-block d-sm-none"></i>
                    <input type="file" id="upload" name="profile_picture" class="account-file-input" hidden
                        accept="image/png, image/jpeg, image/gif" form="formAccountSettings" />
                </label>
                <button type="button" id="resetProfileImage"
                    class="btn btn-outline-secondary account-image-reset mb-4">
                    <i class="bx bx-reset d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Reset</span>
                </button>

                <p class="text-muted mb-0">
                    Format file JPG, GIF or PNG.
                    Ukuran maksimal 2 MB.
                </p>
            </div>
        </div>
    </div>
    <hr class="my-0" />
    <div class="card-body">
        <form id="formAccountSettings" method="POST" action="{{ route('profile.update') }}"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="remove_profile_picture" id="removeProfilePicture"
                value="{{ $removeProfilePicture }}">

            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="firstName" class="form-label">Nama Depan</label>
                    <input class="form-control @error('first_name') is-invalid @enderror" type="text" id="firstName"
                        name="first_name" value="{{ $firstName }}" autofocus />
                </div>
                <div class="mb-3 col-md-6">
                    <label for="lastName" class="form-label">Nama Belakang</label>
                    <input class="form-control @error('last_name') is-invalid @enderror" type="text" name="last_name"
                        id="lastName" value="{{ $lastName }}" />
                </div>
                <div class="mb-3 col-md-6">
                    <label for="email" class="form-label">E-mail</label>
                    <input class="form-control @error('email') is-invalid @enderror" type="text" id="email"
                        name="email" value="{{ $email }}" placeholder="john.doe@example.com" />
                </div>
                <div class="mb-3 col-md-6">
                    <label for="organization" class="form-label">Posisi</label>
                    <input type="text" class="form-control" id="organization" name="organization"
                        value="{{ ucfirst($user->role) }}" readonly />
                </div>
                <div class="mb-3 col-md-6">
                    <label class="form-label" for="phoneNumber">Nomor Telepon</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text">ID (+62)</span>
                        <input type="text" id="phoneNumber" name="phone"
                            class="form-control @error('phone') is-invalid @enderror" value="{{ $phone }}"
                            placeholder="202 555 0111" />
                    </div>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="address" class="form-label">Alamat</label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address"
                        name="address" value="{{ $address }}" placeholder="Address" />
                </div>
                <div class="mb-3 col-md-6">
                    <label for="province" class="form-label">Provinsi</label>
                    <select id="province" name="province_id"
                        class="form-select @error('province_id') is-invalid @enderror">
                        <option value="">Pilih Provinsi</option>
                        @if ($selectedProvinceId)
                            <option value="{{ $selectedProvinceId }}" selected>{{ $selectedProvinceName }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="province_name" id="provinceName"
                        value="{{ $selectedProvinceName }}">
                </div>
                <div class="mb-3 col-md-6">
                    <label for="city" class="form-label">Kota/Kabupaten</label>
                    <select id="city" name="city_id" class="form-select @error('city_id') is-invalid @enderror">
                        <option value="">Pilih Kota/Kabupaten</option>
                        @if ($selectedCityId)
                            <option value="{{ $selectedCityId }}" selected>{{ $selectedCityName }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="city_name" id="cityName" value="{{ $selectedCityName }}">
                </div>
                <div class="mb-3 col-md-6">
                    <label for="zipCode" class="form-label">Kode Pos</label>
                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                        id="zipCode" name="postal_code" placeholder="231465" maxlength="10"
                        value="{{ $postalCode }}" />
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-primary me-2">
                    Save changes
                </button>
                <a type="reset" class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Batal</a>
            </div>
        </form>
    </div>
    <!-- /Account -->
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            const apiUrl = '/api';
            const provinceSelect = $('#province');
            const citySelect = $('#city');

            provinceSelect.select2({
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

            citySelect.select2({
                placeholder: 'Cari dan Pilih Kota/Kabupaten',
                allowClear: true,
                width: '100%'
            });

            provinceSelect.on('select2:select', function(e) {
                $('#provinceName').val(e.params.data.text);
            });

            provinceSelect.on('select2:clear', function() {
                $('#provinceName').val('');
            });

            citySelect.on('select2:select', function(e) {
                $('#cityName').val(e.params.data.text);
            });

            citySelect.on('select2:clear', function() {
                $('#cityName').val('');
            });

            provinceSelect.on('change', function() {
                const provinceId = $(this).val();

                citySelect.empty().append('<option value="">Pilih Kota/Kabupaten</option>');
                citySelect.val('').trigger('change');
                $('#cityName').val('');

                if (!provinceId) {
                    return;
                }

                $.ajax({
                    url: apiUrl + '/provinces/' + provinceId + '/cities',
                    type: 'GET',
                    dataType: 'json',
                    success: function(cities) {
                        cities.forEach(function(city) {
                            citySelect.append('<option value="' + city.id + '">' + city
                                .name + '</option>');
                        });

                        const oldCityId = '{{ $selectedCityId }}';
                        if (oldCityId) {
                            citySelect.val(oldCityId).trigger('change');
                        }
                    },
                    error: function() {
                        alert('Gagal memuat data kota/kabupaten');
                    }
                });
            });

            if (provinceSelect.val()) {
                provinceSelect.trigger('change');
            }

            const profileImageInput = document.getElementById('upload');
            const uploadedAvatar = document.getElementById('uploadedAvatar');
            const resetProfileImageBtn = document.getElementById('resetProfileImage');
            const defaultProfileImage = '{{ $defaultAvatar }}';
            const removeProfilePictureInput = document.getElementById('removeProfilePicture');

            if (profileImageInput && uploadedAvatar) {
                profileImageInput.addEventListener('change', function(event) {
                    const [file] = event.target.files;
                    if (!file) {
                        return;
                    }

                    if (removeProfilePictureInput) {
                        removeProfilePictureInput.value = '0';
                    }

                    uploadedAvatar.src = URL.createObjectURL(file);
                });
            }

            if (resetProfileImageBtn && profileImageInput && uploadedAvatar) {
                resetProfileImageBtn.addEventListener('click', function() {
                    profileImageInput.value = '';
                    uploadedAvatar.src = defaultProfileImage;

                    if (removeProfilePictureInput) {
                        removeProfilePictureInput.value = '1';
                    }
                });
            }
        });
    </script>
@endpush
