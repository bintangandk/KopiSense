<div class="card card-iot-container">
    <div class="card-body p-0">

        <div class="mb-4 mx-4 mt-4 d-flex justify-content-between align-items-center">
            <div class="date-badge">
                <span class="date-label">Real Time Data:</span>
                <strong style="font-size: 1rem; color: #555;">
                    <span id="currentDate">{{ $date }}</span>
                </strong>
            </div>

            <!-- Device Select Button -->
            <div class="device-selector">
                <select id="deviceSelect" class="form-control" style="width: 200px;">
                    <option value="" disabled selected>Pilih Device IoT</option>
                </select>
            </div>
        </div>

        <div class="row g-4 mb-4 mx-2">

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <img src="{{ asset('assets/img/icons/temp.svg') }}" class="device-icon-iot" alt="">
                        <a href="#" class="action-btn">
                            <img src="{{ asset('assets/img/icons/arrow-line.svg') }}" alt="">
                        </a>
                    </div>

                    <span class="stat-label">Nilai Suhu</span>
                    <h1 class="stat-value">
                        <span id="tempValue">{{ $temperature ?? '-' }}</span><span class="stat-unit">°C</span>
                    </h1>

                    <small class="stat-desc">
                        Batas aman suhu adalah 28-30 °C
                    </small>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <img src="{{ asset('assets/img/icons/humidity.svg') }}" class="device-icon-iot" alt="">
                        <a href="#" class="action-btn">
                            <img src="{{ asset('assets/img/icons/arrow-line.svg') }}" alt="">
                        </a>
                    </div>

                    <span class="stat-label">Kelembapan</span>
                    <h1 class="stat-value">
                        <span id="humidityValue">{{ $humidity ?? '-' }}</span><span class="stat-unit">%</span>
                    </h1>

                    <small class="stat-desc">
                        Batas aman kelembapan 28-30 %
                    </small>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <img src="{{ asset('assets/img/icons/ph-tanah.svg') }}" class="device-icon-iot" alt="">
                        <a href="#" class="action-btn">
                            <img src="{{ asset('assets/img/icons/arrow-line.svg') }}" alt="">
                        </a>
                    </div>

                    <span class="stat-label">pH Tanah</span>
                    <h1 class="stat-value">
                        <span id="phValue">{{ $soilPH ?? '-' }}</span>
                    </h1>

                    <small class="stat-desc">
                        Batas aman level pH tanah adalah 6 - 8
                    </small>
                </div>
            </div>

        </div>
    </div>
</div>
