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
                        <form>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Nama</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name"
                                        placeholder="Masukkan Nama Anggota" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-company">Level</label>
                                <div class="col-sm-10">
                                    <select name="" id="defaultSelect" class="form-select">
                                        <option>Pilih Level</option>
                                        <option value="pegawai">Pegawai</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-email">Email</label>
                                <div class="col-sm-10">
                                    <div class="input-group input-group-merge">
                                        <input type="text" id="basic-default-email" class="form-control"
                                            placeholder="Masukkan Email" aria-label="Masukkan Email"
                                            aria-describedby="basic-default-email2" />
                                        <span class="input-group-text" id="basic-default-email2">@example.com</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-phone">No. Hp</label>
                                <div class="col-sm-10">
                                    <input type="text" id="basic-default-phone" class="form-control phone-mask"
                                        placeholder="Masukkan No. Hp dengan format +62 799 8941" aria-label="+62 799 8941"
                                        aria-describedby="basic-default-phone" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-message">Alamat</label>
                                <div class="col-sm-10">
                                    <textarea id="basic-default-message" class="form-control" placeholder="Masukkan Alamat Anggota"
                                        aria-label="Masukkan Alamat Anggota" aria-describedby="basic-icon-default-message2"></textarea>
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
