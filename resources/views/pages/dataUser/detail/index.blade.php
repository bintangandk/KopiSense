@extends('layouts.app')

@section('content')
    <div class="container-xxl grow container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Form/</span> Detail Anggota</h4>
        <div class="row">
            <div class="col-xxl">
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Detail Anggota</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-name">Nama</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="basic-default-name"
                                    placeholder="Masukkan Nama Anggota" value="{{ $user->profile?->full_name ?? '-' }}"
                                    readonly />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-name">NIK</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="basic-default-name"
                                    placeholder="Masukkan Nama Anggota" value="{{ $user->profile?->nik ?? '-' }}"
                                    readonly />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-name">Jenis Kelamin</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="basic-default-name"
                                    placeholder="Masukkan Nama Anggota" value="{{ $user->profile?->gender ?? '-' }}"
                                    readonly />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-email">Email</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-merge">
                                    <input type="text" id="basic-default-email" class="form-control"
                                        placeholder="Masukkan Email" aria-label="Masukkan Email"
                                        aria-describedby="basic-default-email2" value="{{ $user->email }}" readonly />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-phone">No. Hp</label>
                            <div class="col-sm-10">
                                <input type="text" id="basic-default-phone" class="form-control phone-mask"
                                    placeholder="Masukkan No. Hp dengan format +62 799 8941" aria-label="+62 799 8941"
                                    aria-describedby="basic-default-phone" value="{{ $user->profile?->phone ?? '-' }}"
                                    readonly />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-company">Level</label>
                            <div class="col-sm-10">
                                <input type="text" id="basic-default-company" class="form-control"
                                    placeholder="Masukkan Level Anggota" value="{{ $user->role }}" readonly />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-message">Alamat</label>
                            <div class="col-sm-10">
                                <textarea id="basic-default-message" class="form-control" placeholder="Masukkan Alamat Anggota"
                                    aria-label="Masukkan Alamat Anggota" aria-describedby="basic-icon-default-message2" readonly>{{ $user->profile?->address ?? '-' }} </textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-company">Kota</label>
                            <div class="col-sm-10">
                                <input type="text" id="basic-default-company" class="form-control"
                                    placeholder="Masukkan Level Anggota" value="{{ $user->profile?->city ?? '-' }}"
                                    readonly />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-company">Provinsi</label>
                            <div class="col-sm-10">
                                <input type="text" id="basic-default-company" class="form-control"
                                    placeholder="Masukkan Level Anggota" value="{{ $user->profile?->province ?? '-' }}"
                                    readonly />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-company">Kode POS</label>
                            <div class="col-sm-10">
                                <input type="text" id="basic-default-company" class="form-control"
                                    placeholder="Masukkan Level Anggota" value="{{ $user->profile?->postal_code ?? '-' }}"
                                    readonly />
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <x-ui.button.index variant="secondary" href="{{ route('data-user') }}">
                                    Keluar
                                </x-ui.button.index>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
