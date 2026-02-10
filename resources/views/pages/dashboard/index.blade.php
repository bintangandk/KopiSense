<!-- Content -->
@extends('layouts.app')

@section('content')
    <div class="container-xxl grow container-p-y">
        <div class="row">
            <div class="max-w-7xl mx-auto p-6 mb-5 w-100">
                <div class="row g-4">
                    <div class="col-lg-7">
                        @include('components.ui.welcomeCard.index')
                    </div>
                    <div class="col-lg-5">
                        @include('components.ui.thresholdCard.index', [
                            'temperature' => $temperature ?? '28-30',
                            'humidity' => $humidity ?? '60-80',
                            'soilPH' => $soilPH ?? '6-8',
                        ])
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    @include('components.ui.cardDeviceIoT.index', [
                        'temperature' => $temperature ?? null,
                        'humidity' => $humidity ?? null,
                        'soilPH' => $soilPH ?? null,
                        'date' => $date ?? now()->locale('id')->translatedFormat('l, d F Y'),
                    ])
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load devices on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDevices();

            // Add event listener for device selection
            document.getElementById('deviceSelect').addEventListener('change', function() {
                if (this.value) {
                    fetchDeviceData(this.value);
                }
            });
        });

        // Function to load all devices
        function loadDevices() {
            fetch('/api/devices-list')
                .then(response => response.json())
                .then(devices => {
                    const select = document.getElementById('deviceSelect');

                    devices.forEach(device => {
                        const option = document.createElement('option');
                        option.value = device.id;
                        option.text = device.name;
                        select.appendChild(option);
                    });

                    // Auto-select and load first device if exists
                    if (devices.length > 0) {
                        select.value = devices[0].id;
                        fetchDeviceData(devices[0].id);
                    }
                })
                .catch(error => console.error('Error loading devices:', error));
        }

        // Function to fetch device data
        function fetchDeviceData(deviceId) {
            fetch(`/api/device-data/${deviceId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Device not found');
                    }
                    return response.json();
                })
                .then(data => {
                    // Update temperature
                    document.getElementById('tempValue').textContent = data.temperature !== null ? data.temperature :
                        '-';

                    // Update humidity
                    document.getElementById('humidityValue').textContent = data.humidity !== null ? data.humidity : '-';

                    // Update soil pH
                    document.getElementById('phValue').textContent = data.soilPH !== null ? data.soilPH : '-';

                    // Update date
                    document.getElementById('currentDate').textContent = data.date;
                })
                .catch(error => {
                    console.error('Error fetching device data:', error);
                    alert('Gagal mengambil data device. Silakan coba lagi.');
                });
        }
    </script>
@endsection
