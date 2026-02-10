<div class="card system-specs-card">
    <div class="card-body p-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="card-title mb-0 fw-bold">Kondisi Lingkungan Greenhouse</h6>
            <span class="badge bg-success-light text-success" style="font-size: 0.75rem;">
                <span class="dot d-inline-block rounded-circle me-1"
                    style="width: 6px; height: 6px; background-color: #28a745;"></span>
                Aman
            </span>
        </div>

        <!-- Content Row: Image + Threshold -->
        <div class="row align-items-center">
            <!-- Panel Image -->
            <div class="col-md-8 text-center mb-4 mb-md-0">
                <div style="height: 220px; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ asset('assets/img/illustrations/greenhouse.png') }}" alt="Greenhouse" class="img-fluid"
                        style="max-height: 100%; object-fit: contain;">
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

    .btn-warning:hover {
        background-color: #ffc107;
    }
</style>
