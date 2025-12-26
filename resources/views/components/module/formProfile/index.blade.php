<div class="card mb-4">
    <h5 class="card-header">
        Detail Profil Pengguna
    </h5>
    <!-- Account -->
    <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-4">
            <img src="../assets/img/avatars/1.png" alt="user-avatar" class="d-block rounded" height="100" width="100"
                id="uploadedAvatar" />
            <div class="button-wrapper">
                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                    <span class="d-none d-sm-block">Ganti Foto Profil</span>
                    <i class="bx bx-upload d-block d-sm-none"></i>
                    <input type="file" id="upload" class="account-file-input" hidden
                        accept="image/png, image/jpeg" />
                </label>
                <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
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
        <form id="formAccountSettings" method="POST" onsubmit="return false">
            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="firstName" class="form-label">Nama Depan</label>
                    <input class="form-control" type="text" id="firstName" name="firstName" value="John"
                        autofocus />
                </div>
                <div class="mb-3 col-md-6">
                    <label for="lastName" class="form-label">Nama Belakang</label>
                    <input class="form-control" type="text" name="lastName" id="lastName" value="Doe" />
                </div>
                <div class="mb-3 col-md-6">
                    <label for="email" class="form-label">E-mail</label>
                    <input class="form-control" type="text" id="email" name="email"
                        value="john.doe@example.com" placeholder="john.doe@example.com" />
                </div>
                <div class="mb-3 col-md-6">
                    <label for="organization" class="form-label">Posisi</label>
                    <input type="text" class="form-control" id="organization" name="organization"
                        value="ThemeSelection" />
                </div>
                <div class="mb-3 col-md-6">
                    <label class="form-label" for="phoneNumber">Nomor Telepon</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text">ID (+62)</span>
                        <input type="text" id="phoneNumber" name="phoneNumber" class="form-control"
                            placeholder="202 555 0111" />
                    </div>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="address" class="form-label">Alamat</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Address" />
                </div>
                <div class="mb-3 col-md-6">
                    <label for="state" class="form-label">Provinsi</label>
                    <input class="form-control" type="text" id="state" name="state" placeholder="California" />
                </div>
                <div class="mb-3 col-md-6">
                    <label for="zipCode" class="form-label">Kode Pos</label>
                    <input type="text" class="form-control" id="zipCode" name="zipCode" placeholder="231465"
                        maxlength="6" />
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
