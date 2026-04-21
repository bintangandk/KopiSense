<div class="card system-specs-card">
    <div class="card-body p-4">

        <!-- Header -->
        <div class="mb-4 position-relative">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="card-title mb-3 fw-bold">Kondisi Lingkungan Greenhouse</h6>

                    <!-- ANFIS Prediction Status -->
                    <div id="anfisStatus">
                        <span class="badge bg-success-light text-success" style="font-size: 0.75rem;">
                            <span class="dot d-inline-block rounded-circle me-1"
                                style="width: 6px; height: 6px; background-color: #28a745;"></span>
                            <span id="statusText">Aman</span>
                        </span>
                        <small class="text-muted ms-2" id="predictionScore" style="display: none;"></small>
                    </div>
                </div>

                <!-- Arrow Button (Top Right) -->
                <a href="#" class="action-btn" style="flex-shrink: 0;">
                    <img src="{{ asset('assets/img/icons/arrow-line.svg') }}" alt="">
                </a>
            </div>
        </div>

        <!-- Content Row: Image + Threshold -->
        <div class="row align-items-center">
            <!-- Panel Image -->
            <div class="col-md-8 text-center mb-4 mb-md-0">
                <div style="height: 220px; display: flex; align-items: center; justify-content: center;">
                    <img id="greenhouseImage" src="{{ asset('assets/img/illustrations/greenhouse.png') }}"
                        alt="Greenhouse" class="img-fluid" style="max-height: 100%; object-fit: contain;">
                </div>
            </div>

            <!-- Batas Aman (Threshold) Vertical -->
            <div class="col-md-3 ms-auto">
                <h6 class="mb-3 fw-bold">Batas Aman</h6>

                <!-- Suhu -->
                <div class="threshold-item mb-2 pb-2" style="border-bottom: 1px solid #e9ecef;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-brightness-high text-warning me-2" style="font-size: 1.25rem;"></i>
                        <small class="text-muted">Suhu</small>
                    </div>
                    <h6 class="mb-0 ms-4">{{ $temperature ?? '28-30' }}°C</h6>
                </div>

                <!-- Kelembapan -->
                <div class="threshold-item mb-2 pb-2" style="border-bottom: 1px solid #e9ecef;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-cloud-drizzle text-info me-2" style="font-size: 1.25rem;"></i>
                        <small class="text-muted">Kelembapan</small>
                    </div>
                    <h6 class="mb-0 ms-4">{{ $humidity ?? '60-80' }}%</h6>
                </div>

                <!-- pH Tanah -->
                <div class="threshold-item">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-droplet text-success me-2" style="font-size: 1.25rem;"></i>
                        <small class="text-muted">pH Tanah</small>
                    </div>
                    <h6 class="mb-0 ms-4">{{ $soilPH ?? '6-8' }}</h6>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .system-specs-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border-radius: 10px;
    }

    .specs-metric {
        padding: 0;
    }

    .metric-icon {
        width: 50px;
        height: 50px;
        background-color: #fff3cd !important;
    }

    .metric-icon.bg-info-light {
        background-color: #e3f2fd !important;
    }

    .power-metric {
        padding: 0;
    }

    .metric-icon-sm {
        width: 100%;
    }

    .badge.bg-success-light {
        background-color: #d4edda !important;
    }

    .badge.bg-warning-light {
        background-color: #fff3cd !important;
    }

    .badge.bg-danger-light {
        background-color: #f8d7da !important;
    }

    .btn-warning:hover {
        background-color: #ffc107;
    }
</style>

<script>
    // Load latest ANFIS prediction on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadLatestPrediction();
        // Refresh prediction every 30 seconds
        setInterval(loadLatestPrediction, 30000);
    });

    /**
     * Load latest ANFIS prediction from API
     */
    function loadLatestPrediction() {
        const deviceSelect = document.getElementById('deviceSelect');
        const deviceId = deviceSelect ? deviceSelect.value : null;

        let url = '/api/latest-prediction';
        if (deviceId) {
            url += `?device_id=${deviceId}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.data) {
                    updateStatusDisplay(result.data);
                } else {
                    console.log('[loadLatestPrediction] No prediction data available');
                }
            })
            .catch(error => {
                console.error('[loadLatestPrediction] Error:', error);
            });
    }

    /**
     * Update status display based on ANFIS prediction
     */
    function updateStatusDisplay(data) {
        const statusText = document.getElementById('statusText');
        const predictionScore = document.getElementById('predictionScore');
        const anfisStatus = document.getElementById('anfisStatus');
        const statusBadge = anfisStatus.querySelector('.badge');
        const greenhouseImage = document.getElementById('greenhouseImage');

        // Update status text
        if (statusText) {
            statusText.textContent = data.status;
        }

        // Update greenhouse image based on status
        if (greenhouseImage) {
            if (data.status === 'Aman') {
                greenhouseImage.src = "{{ asset('assets/img/illustrations/greenhouse.png') }}";
            } else {
                greenhouseImage.src = "{{ asset('assets/img/illustrations/greenhouse-warning.png') }}";
            }
        }

        // Update badge color based on status
        if (statusBadge) {
            // Remove all status classes
            statusBadge.className = 'badge';

            const statusColor = data.status_color || 'success';
            const colorMap = {
                'success': 'bg-success-light text-success',
                'warning': 'bg-warning-light text-warning',
                'danger': 'bg-danger-light text-danger',
            };

            statusBadge.classList.add(...(colorMap[statusColor] || colorMap['success']).split(' '));

            // Update dot color
            const dot = statusBadge.querySelector('.dot');
            if (dot) {
                const dotColorMap = {
                    'success': '#28a745',
                    'warning': '#ffc107',
                    'danger': '#dc3545',
                };
                dot.style.backgroundColor = dotColorMap[statusColor] || dotColorMap['success'];
            }
        }

        // Show prediction info if available
        if (predictionScore && data.mist_duration !== undefined) {
            const infoText = `Durasi Semprotan: ${data.mist_duration.toFixed(2)}s`;
            predictionScore.textContent = infoText;
            predictionScore.style.display = 'inline-block';
        }

        console.log('[updateStatusDisplay] Updated status:', data.status, 'Color:', data.status_color);
    }
</script>
