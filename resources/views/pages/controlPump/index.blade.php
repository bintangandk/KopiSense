@extends('layouts.app')

@section('content')
    <div class="container-xxl grow container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Kontrol /</span> Pompa</h4>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <h5 class="card-header">Setting Pompa</h5>
                    <div class="card-body">
                        <div class="mt-3">
                            <label class="form-label">Batas Aman Suhu (°C)</label>
                            <div class="row">
                                <div class="col-6">
                                    <label for="suhuMin" class="form-label">Minimum <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="suhuMin" min="0" max="50"
                                        value="28" />
                                </div>
                                <div class="col-6">
                                    <label for="suhuMax" class="form-label">Maksimum <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="suhuMax" min="0" max="50"
                                        value="29" />
                                </div>
                            </div>
                            <div class="form-text">
                                Tentukan rentang suhu aman untuk pompa (misalnya 28-29°C).
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Batas Aman Kelembaban (%)</label>
                            <div class="row">
                                <div class="col-6">
                                    <label for="kelembabanMin" class="form-label">Minimum <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="kelembabanMin" min="0"
                                        max="100" value="28" />
                                </div>
                                <div class="col-6">
                                    <label for="kelembabanMax" class="form-label">Maksimum <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="kelembabanMax" min="0"
                                        max="100" value="29" />
                                </div>
                            </div>
                            <div class="form-text">
                                Tentukan rentang kelembaban aman untuk pompa (misalnya 80-90%).
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Batas Aman pH Tanah</label>
                            <div class="row">
                                <div class="col-6">
                                    <label for="phTanahMin" class="form-label">Minimum <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="phTanahMin" min="0" max="14"
                                        value="6" />
                                </div>
                                <div class="col-6">
                                    <label for="phTanahMax" class="form-label">Maksimum <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="phTanahMax" min="0" max="14"
                                        value="7" />
                                </div>
                            </div>
                            <div class="form-text">
                                Tentukan rentang pH tanah aman untuk pompa (misalnya 6-7).
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <h5 class="card-header">Kontrol Manual</h5>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Status Pompa</label>
                            <div class="alert alert-success mb-0" role="alert">
                                <i class="bx bx-check-circle"></i> Pompa sedang <strong>HIDUP</strong>
                            </div>
                        </div>
                        <div class="form-check form-switch form-switch-text">
                            <input class="form-check-input" type="checkbox" id="pompaManualSwitch" checked />
                            <label class="form-check-label" for="pompaManualSwitch">Matikan Pompa Secara Manual</label>
                            <div class="form-text mt-2">
                                Gunakan tombol ini jika pompa tidak mati otomatis. Aktifkan untuk mematikan pompa.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
