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
                                                    <span class="badge" id="tempBadge">
                                                        <i class="bx bx-loader-alt bx-spin"></i> Loading
                                                    </span>
                                                </div>
                                                <h4 class="mb-0">
                                                    <span id="tempValue">--</span> <small>°C</small>
                                                </h4>
                                                <small class="text-muted">Target: 28-29°C</small>
                                            </div>
                                        </div>
                                        {{-- Kelembaban Card --}}
                                        <div class="col-md-6 col-sm-12">
                                            <div class="p-3 border rounded-2" style="background-color: #f8f9fa;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">Kelembaban Saat Ini</small>
                                                    <span class="badge" id="humidityBadge">
                                                        <i class="bx bx-loader-alt bx-spin"></i> Loading
                                                    </span>
                                                </div>
                                                <h4 class="mb-0">
                                                    <span id="humidityValue">--</span> <small>%</small>
                                                </h4>
                                                <small class="text-muted">Target: 80-90%</small>
                                            </div>
                                        </div>
                                        {{-- pH Tanah Card --}}
                                        <div class="col-md-6 col-sm-12">
                                            <div class="p-3 border rounded-2" style="background-color: #f8f9fa;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">pH Tanah Saat Ini</small>
                                                    <span class="badge" id="phBadge">
                                                        <i class="bx bx-loader-alt bx-spin"></i> Loading
                                                    </span>
                                                </div>
                                                <h4 class="mb-0">
                                                    <span id="phValue">--</span>
                                                </h4>
                                                <small class="text-muted">Target: 6-7</small>
                                            </div>
                                        </div>
                                        {{-- Status Keseluruhan --}}
                                        <div class="col-md-6 col-sm-12">
                                            <div class="p-3 border rounded-2" id="statusOverallContainer"
                                                style="background-color: #f0f9ff;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">Status Keseluruhan</small>
                                                    <i class="bx bx-info-circle text-info"></i>
                                                </div>
                                                <h4 class="mb-0" id="statusOverall" style="color: #17a2b8;">
                                                    <i class="bx bx-loader-alt bx-spin"></i> Loading
                                                </h4>
                                                <small class="text-muted" id="statusMessage">Mengambil data ANFIS...</small>
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
                                        <div class="alert mb-0 p-2" id="pumpStatusAlert" role="alert"
                                            style="font-size: 0.9rem;">
                                            <i class="bx bx-check-circle"></i> <strong id="pumpStatusText">LOADING</strong>
                                        </div>
                                    </div>

                                    {{-- Manual Control --}}
                                    <div>
                                        <small class="text-uppercase text-muted fw-bold d-block mb-3">Kontrol
                                            Manual</small>

                                        {{-- Pump Control Buttons --}}
                                        <div class="d-flex gap-2 mb-2">
                                            <button class="btn btn-sm btn-success grow" id="pumpOnBtn">
                                                <i class="bx bx-power-off"></i> Hidupkan
                                            </button>
                                            <button class="btn btn-sm btn-danger grow" id="pumpOffBtn">
                                                <i class="bx bx-power-off"></i> Matikan
                                            </button>
                                        </div>

                                        <small class="d-block text-muted" style="font-size: 0.8rem; line-height: 1.4;">
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

    {{-- JavaScript for Manual Pump Control & ANFIS Data Display --}}
    <script>
        /**
         * Fetch latest ANFIS prediction and update UI
         */
        function loadAnfisData() {
            fetch('/api/latest-prediction', {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        updateAnfisDisplay(data.data);
                        updatePumpStatus(data.data); // Update pump status from ANFIS data
                    } else {
                        showAnfisError('Data ANFIS tidak tersedia');
                    }
                })
                .catch(error => {
                    console.error('Error fetching ANFIS data:', error);
                    showAnfisError('Gagal mengambil data ANFIS');
                });
        }

        /**
         * Update UI with ANFIS prediction data
         */
        function updateAnfisDisplay(data) {
            const aggData = data.aggregated_data || {};

            // Update Temperature
            document.getElementById('tempValue').textContent = aggData.temperature ?? '--';
            const tempBadge = document.getElementById('tempBadge');
            tempBadge.textContent = aggData.temperature_status || 'N/A';
            tempBadge.className = `badge ${aggData.temperature_badge || 'bg-secondary'}`;

            // Update Humidity
            document.getElementById('humidityValue').textContent = aggData.humidity ?? '--';
            const humidityBadge = document.getElementById('humidityBadge');
            humidityBadge.textContent = aggData.humidity_status || 'N/A';
            humidityBadge.className = `badge ${aggData.humidity_badge || 'bg-secondary'}`;

            // Update Soil pH
            document.getElementById('phValue').textContent = aggData.soil_ph ?? '--';
            const phBadge = document.getElementById('phBadge');
            phBadge.textContent = aggData.soil_ph_status || 'N/A';
            phBadge.className = `badge ${aggData.soil_ph_badge || 'bg-secondary'}`;

            // Update Overall Status
            const statusContainer = document.getElementById('statusOverallContainer');
            const statusBgMap = {
                'success': '#f0f9ff',
                'warning': '#fffbf0',
                'danger': '#fff5f5'
            };
            const statusColorMap = {
                'success': '#17a2b8',
                'warning': '#ff9800',
                'danger': '#dc3545'
            };

            statusContainer.style.backgroundColor = statusBgMap[data.status_color] || '#f0f9ff';

            const statusOverall = document.getElementById('statusOverall');
            statusOverall.style.color = statusColorMap[data.status_color] || '#17a2b8';
            statusOverall.textContent = data.status || 'N/A';

            document.getElementById('statusMessage').textContent = data.status_message || 'Lingkungan dalam kondisi baik';

            console.log('ANFIS Data loaded successfully:', data);
        }

        /**
         * Update pump status from latest ANFIS data
         */
        function updatePumpStatus(data) {
            const pumpStatus = data.pump_status;
            const statusAlert = document.getElementById('pumpStatusAlert');
            const statusText = document.getElementById('pumpStatusText');

            if (pumpStatus) {
                statusAlert.className = 'alert alert-success p-2';
                statusText.textContent = 'HIDUP';
            } else {
                statusAlert.className = 'alert alert-danger p-2';
                statusText.textContent = 'MATI';
            }

            console.log('Pump status updated:', pumpStatus ? 'HIDUP' : 'MATI');
        }

        /**
         * Show error message in ANFIS display
         */
        function showAnfisError(message) {
            document.getElementById('tempValue').textContent = '--';
            document.getElementById('tempBadge').textContent = 'Error';
            document.getElementById('humidityValue').textContent = '--';
            document.getElementById('humidityBadge').textContent = 'Error';
            document.getElementById('phValue').textContent = '--';
            document.getElementById('phBadge').textContent = 'Error';
            document.getElementById('statusOverall').textContent = 'Error';
            document.getElementById('statusMessage').textContent = message;
        }

        /**
         * Send manual pump control command
         */
        function sendPumpControl(pumpStatus, mistDuration) {
            const deviceId = 1; // Dummy device ID (controller will override)
            const isLoading = true;
            const buttons = [document.getElementById('pumpOnBtn'), document.getElementById('pumpOffBtn')];

            const payload = {
                pump_status: pumpStatus,
                mist_duration: mistDuration
            };

            console.log('🔧 Sending pump control:', {
                payload,
                deviceId,
                url: `/api/device/${deviceId}/manual-pump-control`
            });

            // Disable buttons during request
            buttons.forEach(btn => btn.disabled = isLoading);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            console.log('🔐 CSRF Token:', csrfToken ? 'Present' : 'MISSING!');

            fetch(`/api/device/${deviceId}/manual-pump-control`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => {
                    console.log('📡 Response status:', response.status);
                    console.log('📡 Response headers:', {
                        'content-type': response.headers.get('content-type')
                    });

                    if (!response.ok) {
                        console.error('❌ HTTP Error:', response.statusText);
                    }

                    return response.json().then(data => ({
                        status: response.status,
                        data: data
                    }));
                })
                .then(({
                    status,
                    data
                }) => {
                    console.log('📦 API Response:', {
                        status,
                        data
                    });

                    if (data.success) {
                        console.log('✅ Success:', data.message);

                        // Update status display
                        const statusAlert = document.getElementById('pumpStatusAlert');
                        const statusText = document.getElementById('pumpStatusText');

                        if (pumpStatus === 1) {
                            statusAlert.className = 'alert alert-success p-2';
                            statusText.textContent = 'HIDUP';
                        } else {
                            statusAlert.className = 'alert alert-danger p-2';
                            statusText.textContent = 'MATI';
                        }

                        // Show success notification
                        if (typeof showNotification === 'function') {
                            showNotification('success', data.message);
                        }
                    } else {
                        // Show error notification
                        console.error('❌ API Error:', data);

                        let errorMessage = data.message || 'Gagal mengontrol pompa';

                        // Show validation errors if available
                        if (data.errors) {
                            console.error('❌ Validation Errors:', data.errors);
                            errorMessage += '\n\nValidation Errors:\n' + JSON.stringify(data.errors, null, 2);
                        }

                        if (typeof showNotification === 'function') {
                            showNotification('error', errorMessage);
                        }
                    }
                })
                .catch(error => {
                    console.error('🚨 Request failed:', error);
                    console.error('🚨 Error stack:', error.stack);
                    if (typeof showNotification === 'function') {
                        showNotification('error', 'Gagal menghubungi server: ' + error.message);
                    }
                })
                .finally(() => {
                    // Re-enable buttons
                    buttons.forEach(btn => btn.disabled = false);
                    // Reload ANFIS data to get latest status
                    console.log('🔄 Reloading ANFIS data...');
                    setTimeout(loadAnfisData, 500);
                });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Page loaded - Initializing ANFIS data and event listeners');

            // Load ANFIS data on page load
            loadAnfisData();

            // Refresh ANFIS data every 30 seconds
            setInterval(loadAnfisData, 30000);

            // Pump ON button handler
            document.getElementById('pumpOnBtn').addEventListener('click', function() {
                sendPumpControl(1, 0);
            });

            // Pump OFF button handler
            document.getElementById('pumpOffBtn').addEventListener('click', function() {
                sendPumpControl(0, 0);
            });
        });
    </script>
@endsection
