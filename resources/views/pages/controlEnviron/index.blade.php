@extends('layouts.app')

@section('content')
    <div class="container-xxl grow container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Kontrol /</span> Lingkungan</h4>

        <div class="row">
            {{-- Section Analisis Lingkungan & Kontrol Pompa --}}
            <div class="col-12">
                <div class="card mb-4">
                    <h5 class="card-header">Analisis Lingkungan & Kontrol Sistem</h5>
                    <div class="card-body">
                        <div class="row g-0">
                            {{-- Kolom Kiri: Analisis Lingkungan --}}
                            <div class="col-lg-9 col-md-8">
                                {{-- Hasil Analisis ANFIS --}}
                                <div class="mb-4">
                                    <h6 class="mb-3">
                                        <i class="bx bx-bar-chart-alt-2"></i> Hasil Analisis ANFIS
                                    </h6>
                                    <div class="row g-3">
                                        {{-- Suhu Card --}}
                                        <div class="col-md-6 col-sm-12">
                                            <div class="p-3 border rounded-2" style="background-color: #f8f9fa;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">Suhu Saat Ini</small>
                                                    <span class="badge bg-success">Normal</span>
                                                </div>
                                                <h4 class="mb-0">28.5 <small>°C</small></h4>
                                                <small class="text-muted">Target: 28-29°C</small>
                                            </div>
                                        </div>
                                        {{-- Kelembaban Card --}}
                                        <div class="col-md-6 col-sm-12">
                                            <div class="p-3 border rounded-2" style="background-color: #f8f9fa;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">Kelembaban Saat Ini</small>
                                                    <span class="badge bg-warning">Caution</span>
                                                </div>
                                                <h4 class="mb-0">75 <small>%</small></h4>
                                                <small class="text-muted">Target: 80-90%</small>
                                            </div>
                                        </div>
                                        {{-- pH Tanah Card --}}
                                        <div class="col-md-6 col-sm-12">
                                            <div class="p-3 border rounded-2" style="background-color: #f8f9fa;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">pH Tanah Saat Ini</small>
                                                    <span class="badge bg-success">Normal</span>
                                                </div>
                                                <h4 class="mb-0">6.8</h4>
                                                <small class="text-muted">Target: 6-7</small>
                                            </div>
                                        </div>
                                        {{-- Status Keseluruhan --}}
                                        <div class="col-md-6 col-sm-12">
                                            <div class="p-3 border rounded-2" style="background-color: #f0f9ff;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">Status Keseluruhan</small>
                                                    <i class="bx bx-info-circle text-info"></i>
                                                </div>
                                                <h4 class="mb-0" style="color: #17a2b8;">Sesuai</h4>
                                                <small class="text-muted">Lingkungan dalam kondisi baik</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Rekomendasi Gemini --}}
                                <div class="mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">
                                            <i class="bx bx-sparkles"></i> Rekomendasi AI Gemini
                                        </h6>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="collapse"
                                            data-bs-target="#geminiRecommendation" aria-expanded="false">
                                            <i class="bx bx-analyze"></i> Analisis Soil PH
                                        </button>
                                    </div>

                                    {{-- Collapse untuk Hasil Rekomendasi --}}
                                    <div class="collapse" id="geminiRecommendation">
                                        <div class="card card-body bg-light border-start border-4"
                                            style="border-color: #667eea !important;">
                                            <div class="d-flex gap-2 mb-2">
                                                <i class="bx bx-loader-alt text-primary"></i>
                                                <small class="text-muted">Menganalisa pH tanah...</small>
                                            </div>
                                            <div style="min-height: 200px;">
                                                {{-- Hasil Analisis --}}
                                                <div class="mt-2">
                                                    <h6 class="mb-2">Analisis Kondisi pH Tanah</h6>
                                                    <p class="mb-2" style="font-size: 0.95rem; line-height: 1.6;">
                                                        Berdasarkan analisis ANFIS dan data historis, pH tanah Anda saat ini
                                                        berada
                                                        pada level optimal (6.8) untuk budidaya tanaman. Tingkat keasaman
                                                        ini sangat
                                                        sesuai untuk penyerapan nutrisi yang maksimal.
                                                    </p>

                                                    <h6 class="mb-2 mt-3">Rekomendasi:</h6>
                                                    <ul style="font-size: 0.95rem; line-height: 1.8;">
                                                        <li>Pertahankan pH tanah dalam rentang 6.5-7.0 untuk hasil optimal
                                                        </li>
                                                        <li>Monitor kelembaban tanah secara berkala untuk mencegah perubahan
                                                            pH yang
                                                            drastis</li>
                                                        <li>Jika pH menurun di bawah 6.0, pertimbangkan penambahan pupuk
                                                            kapur</li>
                                                        <li>Lakukan tes pH tanah minimal sebulan sekali</li>
                                                    </ul>

                                                    <h6 class="mb-2 mt-3">Aksi yang Disarankan:</h6>
                                                    <div class="alert alert-info mb-0" role="alert">
                                                        <i class="bx bx-check-circle"></i> <strong>Status:</strong>
                                                        Lanjutkan
                                                        pemeliharaan rutin - tidak ada tindakan khusus yang diperlukan saat
                                                        ini.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Divider --}}
                            <div class="col-lg-1 col-md-4 d-none d-md-block">
                                <div style="height: 100%; border-left: 1px solid #dee2e6; margin: 0 3.35rem;"></div>
                            </div>

                            {{-- Kolom Kanan: Kontrol Darurat Pompa --}}
                            <div class="col-lg-2 col-md-12 col-sm-12">
                                <div>
                                    <h6 class="mb-4">
                                        <i class="bx bx-power-off"></i> Kontrol Pompa
                                    </h6>

                                    {{-- Status Pompa --}}
                                    <div class="mb-4 pb-3" style="border-bottom: 1px solid #dee2e6;">
                                        <small class="text-uppercase text-muted fw-bold d-block mb-2">Status</small>
                                        <div class="alert alert-success mb-0 p-2" role="alert" style="font-size: 0.9rem;">
                                            <i class="bx bx-check-circle"></i> <strong>HIDUP</strong>
                                        </div>
                                    </div>

                                    {{-- Manual Control --}}
                                    <div>
                                        <small class="text-uppercase text-muted fw-bold d-block mb-2">Kontrol Manual</small>
                                        <div class="form-check form-switch form-switch-text">
                                            <input class="form-check-input" type="checkbox" id="pompaManualSwitch"
                                                checked />
                                            <label class="form-check-label" for="pompaManualSwitch"
                                                style="font-size: 0.9rem;">
                                                Matikan Pompa
                                            </label>
                                        </div>
                                        <small class="d-block text-muted mt-2" style="font-size: 0.8rem; line-height: 1.4;">
                                            Gunakan jika pompa tidak mati otomatis
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript for Manual Pump Control --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pompaSwitch = document.getElementById('pompaManualSwitch');

            // Get device ID from the current route or data
            // Adjust this based on how you pass the device ID to the view
            const deviceId = '{{ $deviceId ?? 1 }}'; // Default to 1 if not provided

            pompaSwitch.addEventListener('change', function() {
                // Determine pump status based on checkbox state
                // checked = pompa hidup (1), unchecked = pompa mati (0)
                const pumpStatus = this.checked ? 1 : 0;

                // Send request to backend
                fetch(`/api/device/${deviceId}/manual-pump-control`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            pump_status: pumpStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success notification
                            console.log('Success:', data.message);

                            // Update UI feedback
                            const statusAlert = document.querySelector('.alert-success');
                            if (statusAlert) {
                                const statusText = statusAlert.querySelector('strong');
                                if (statusText) {
                                    statusText.textContent = pumpStatus === 1 ? 'HIDUP' : 'MATI';
                                }

                                // Change alert class based on status
                                statusAlert.classList.remove('alert-success', 'alert-danger');
                                statusAlert.classList.add(pumpStatus === 1 ? 'alert-success' :
                                    'alert-danger');
                            }

                            // Optional: Show toast notification
                            if (typeof showNotification === 'function') {
                                showNotification('success', data.message);
                            }
                        } else {
                            // Show error notification
                            console.error('Error:', data.message);

                            // Revert switch state on error
                            pompaSwitch.checked = !pompaSwitch.checked;

                            // Optional: Show toast notification
                            if (typeof showNotification === 'function') {
                                showNotification('error', data.message || 'Gagal mengontrol pompa');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Request failed:', error);

                        // Revert switch state on error
                        pompaSwitch.checked = !pompaSwitch.checked;

                        // Optional: Show toast notification
                        if (typeof showNotification === 'function') {
                            showNotification('error', 'Gagal menghubungi server');
                        }
                    });
            });
        });
    </script>
@endsection
