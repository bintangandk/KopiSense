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
                                                <small class="text-muted">Target: 20-28°C</small>
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
                                                <small class="text-muted">Target: 5 - 6.5</small>
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
                                        <button class="btn btn-sm btn-primary" id="analyzePhButton"
                                            onclick="loadGeminiRecommendationOnDemand()">
                                            <i class="bx bx-analyze"></i> Refresh Analisis
                                        </button>
                                    </div>

                                    {{-- Hasil Rekomendasi (Always Visible) --}}
                                    <div id="geminiRecommendation">
                                        <div class="card card-body bg-light border-start border-4"
                                            style="border-color: #667eea !important;">
                                            {{-- Loading State --}}
                                            <div class="d-flex gap-2 mb-2">
                                                <i class="bx bx-loader-alt bx-spin text-primary"></i>
                                                <small class="text-muted">Menganalisa pH tanah dengan AI...</small>
                                            </div>
                                            <div style="min-height: 150px;"
                                                class="d-flex align-items-center justify-content-center">
                                                <small class="text-muted">
                                                    <i class="bx bx-loader-alt bx-spin"></i> Loading
                                                </small>
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
        // ========== CLIENT-SIDE RATE LIMITING FOR GEMINI API ==========
        let geminiRequestInProgress = false;
        let lastGeminiRequestTime = 0;
        const GEMINI_MIN_REQUEST_INTERVAL = 3000; // Minimum 3 seconds between requests

        /**
         * Check if Gemini request is allowed (rate limiting)
         */
        function isGeminiRequestAllowed() {
            if (geminiRequestInProgress) {
                console.warn('⏱️ Gemini request already in progress - blocking duplicate');
                return false;
            }

            const now = Date.now();
            const timeSinceLastRequest = now - lastGeminiRequestTime;

            if (timeSinceLastRequest < GEMINI_MIN_REQUEST_INTERVAL) {
                const remainingMs = GEMINI_MIN_REQUEST_INTERVAL - timeSinceLastRequest;
                console.warn('⏱️ Rate limited - wait another ' + Math.ceil(remainingMs / 1000) + 's');
                return false;
            }

            return true;
        }
        /**
         * Load Gemini AI Recommendation for aggregated soil pH
         */
        function loadGeminiRecommendation() {
            // Prevent auto-load if a manual request is in progress
            if (geminiRequestInProgress) {
                console.log('⏳ Melewati auto-load Gemini karena request manual sedang berjalan.');
                return;
            }

            geminiRequestInProgress = true;
            fetch('/api/ph-recommendation-aggregated', {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('🤖 Gemini Recommendation loaded:', data);

                    if (data.success) {
                        updateGeminiDisplay(data);
                    } else {
                        showGeminiError(data.message || 'Gagal memuat rekomendasi Gemini');
                    }
                })
                .catch(error => {
                    console.error('❌ Error fetching Gemini recommendation:', error);
                    showGeminiError('Gagal menghubungi server rekomendasi');
                })
                .finally(() => {
                    geminiRequestInProgress = false;
                });
        }

        /**
         * Load Gemini AI Recommendation ON-DEMAND (user clicks button)
         * POST to optimized endpoint with rate limiting & caching
         */
        function loadGeminiRecommendationOnDemand() {
            // ========== CLIENT-SIDE RATE LIMITING ==========
            if (!isGeminiRequestAllowed()) {
                const remainingMs = GEMINI_MIN_REQUEST_INTERVAL - (Date.now() - lastGeminiRequestTime);
                const message = '⏳ Terlalu cepat - tunggu ' + Math.ceil(remainingMs / 1000) +
                    ' detik sebelum analisis lagi';

                // 🔥 SOLUSI: Cek apakah fungsi ada, jika tidak gunakan alert standar
                if (typeof showNotification === 'function') {
                    showNotification('warning', message);
                } else {
                    alert(message); // Fallback bawaan browser
                }
                return;
            }

            console.log('📊 Loading on-demand Gemini recommendation...');

            geminiRequestInProgress = true;
            lastGeminiRequestTime = Date.now();

            const button = document.getElementById('analyzePhButton');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Menganalisa...';

            fetch('/api/ph-recommendation-on-demand', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    console.log('🎯 On-demand Gemini Recommendation loaded:', data);

                    if (data.success) {
                        updateGeminiDisplay(data);
                    } else {
                        showGeminiError(data.message || 'Gagal memuat rekomendasi Gemini');
                    }
                })
                .catch(error => {
                    console.error('❌ Error fetching on-demand Gemini recommendation:', error);
                    showGeminiError('Gagal menghubungi server rekomendasi: ' + error.message);
                })
                .finally(() => {
                    button.disabled = false;
                    button.innerHTML = originalText;
                    geminiRequestInProgress = false;
                });
        }

        /**
         * Update Gemini recommendation display
         */
        function updateGeminiDisplay(data) {
            const geminiContainer = document.querySelector('#geminiRecommendation .card-body');

            if (!geminiContainer) {
                console.warn('⚠️ Gemini container not found');
                return;
            }

            // Determine badge and status based on pH status
            const phStatus = data.status;
            const badgeClass = phStatus === 'Aman' ? 'bg-success' :
                phStatus === 'Peringatan' ? 'bg-warning' : 'bg-danger';
            const statusIcon = phStatus === 'Aman' ? 'bx-check-circle' : 'bx-exclamation-circle';

            // Determine cache/API status indicator
            let cacheStatusBadge = '';
            let cacheStatusColor = 'secondary';
            let cacheStatusText = '';

            if (data.recommendation_type === 'static') {
                cacheStatusBadge = '✅ pH Normal';
                cacheStatusColor = 'success';
                cacheStatusText = 'Tanah dalam kondisi optimal';
            } else if (data.recommendation_type === 'ai_fresh') {
                cacheStatusBadge = '🚀 Fresh API Call';
                cacheStatusColor = 'info';
                cacheStatusText = 'Analisis baru dari Gemini AI (disimpan cache 7 hari)';
            } else if (data.recommendation_type === 'ai_cached_7d' || data.recommendation_type === 'ai_cached_24h') {
                cacheStatusBadge = '💾 Cached 7d';
                cacheStatusColor = 'info';
                cacheStatusText = 'Hasil analisis dari cache (max 7 hari)';
            } else if (data.recommendation_type === 'fallback_rate_limited') {
                cacheStatusBadge = '⏱️ Rate Limited';
                cacheStatusColor = 'warning';
                cacheStatusText = 'Max 1 analisis per 2 menit - menggunakan rekomendasi fallback';
            } else if (data.recommendation_type === 'fallback_quota_exhausted') {
                cacheStatusBadge = '❌ Quota Habis';
                cacheStatusColor = 'danger';
                cacheStatusText = 'Free Tier quota telah mencapai limit harian - gunakan rekomendasi fallback';
            } else if (data.recommendation_type === 'fallback_api_error' || data.recommendation_type === 'fallback') {
                cacheStatusBadge = '📋 Fallback';
                cacheStatusColor = 'warning';
                cacheStatusText = 'Rekomendasi default (API tidak tersedia saat ini)';
            }

            // Build HTML content
            const htmlContent = `
                <div class="d-flex gap-2 align-items-center mb-3">
                    <i class="bx bx-loader-alt text-primary"></i>
                    <small class="text-muted">pH Rata-rata: <strong>${data.ph}</strong> (dari ${data.sensor_count} sensor)</small>
                </div>
                <div style="min-height: 150px;">
                    {{-- Rekomendasi Status --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge ${badgeClass}">
                                <i class="bx ${statusIcon}"></i> ${phStatus}
                            </span>
                            <span class="badge bg-${cacheStatusColor}">
                                ${cacheStatusBadge}
                            </span>
                        </div>
                        <small class="text-muted">${cacheStatusText}</small>
                    </div>

                    {{-- Rekomendasi Text --}}
                    <div class="mb-3">
                        <p style="font-size: 0.95rem; line-height: 1.6; white-space: pre-wrap;">
                            ${escapeHtml(data.recommendation)}
                        </p>
                    </div>

                    {{-- Action Badge --}}
                    <div class="alert ${phStatus === 'Aman' ? 'alert-success' : 'alert-warning'} mb-0" role="alert">
                        <i class="bx bx-check-circle"></i> 
                        <strong>${phStatus === 'Aman' ? 'Kondisi Optimal' : 'Perlu Tindakan'}:</strong>
                        ${phStatus === 'Aman' ? 
                            'Lanjutkan pemeliharaan rutin - lingkungan optimal untuk kopi Robusta.' : 
                            'Tindakan penyesuaian pH disarankan untuk hasil optimal.'}
                    </div>

                    ${data.recommendation_type === 'fallback_quota_exhausted' ? 
                        `<div class="alert alert-danger mt-3 mb-0" role="alert">
                                                                                                    <i class="bx bx-error-circle"></i> 
                                                                                                    <strong>❌ Quota Gemini Free Tier Habis</strong><br>
                                                                                                    <small><strong>Penyebab:</strong> API Gemini memiliki limit request harian untuk tier gratis (limit: 0 reached).<br>
                                                                                                    <strong>Solusi:</strong><br>
                                                                                                    1. Coba lagi besok ketika quota reset (24 jam)<br>
                                                                                                    2. Upgrade ke plan berbayar di <a href="https://ai.google.dev" target="_blank">ai.google.dev</a><br>
                                                                                                    3. Gunakan rekomendasi fallback ini sebagai panduan sementara</small>
                                                                                                </div>` : 
                        (data.message && (data.message.includes('429') || data.message.includes('quota') || data.message.includes('Quota')) ? 
                        `<div class="alert alert-warning mt-3 mb-0" role="alert">
                                                                                                    <i class="bx bx-info-circle"></i> 
                                                                                                    <strong>⚠️ Quota Gemini Free Tier Habis</strong><br>
                                                                                                    <small>API Gemini memiliki limit penggunaan harian untuk tier gratis. Coba lagi nanti atau upgrade ke plan berbayar. Rekomendasi ini menggunakan logika fallback otomatis.</small>
                                                                                                </div>` : '')}
                </div>
            `;

            geminiContainer.innerHTML = htmlContent;
            console.log('✅ Gemini display updated with cache status:', data.cache_status);
        }

        /**
         * Show error in Gemini display
         */
        function showGeminiError(message) {
            const geminiContainer = document.querySelector('#geminiRecommendation .card-body');

            if (!geminiContainer) {
                return;
            }

            geminiContainer.innerHTML = `
                <div class="alert alert-danger mb-0" role="alert">
                    <i class="bx bx-error-circle"></i> <strong>Error:</strong> ${message}
                </div>
            `;
        }

        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

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
            console.log('🚀 Page loaded - Initializing ANFIS data and Gemini recommendation');

            // Load ANFIS data on page load
            loadAnfisData();

            // Load Gemini recommendation on page load
            loadGeminiRecommendation();

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
