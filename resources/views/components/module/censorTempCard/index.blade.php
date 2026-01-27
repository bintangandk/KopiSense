<div class="col-12 col-sm-12 col-md-10 col-lg-8 col-xl-8 order-2 order-md-3 order-lg-2 mb-4 w-100 pt-4">
    <div class="d-flex justify-content-end gap-2 mb-3 flex-column flex-sm-row">
        <div style="min-width: 200px; max-width: 250px;" class="grow flex-sm-grow-0">
            <x-ui.dateRangePicker.index id="filterTanggalTemp" placeholder="Filter Tanggal" class="form-control-sm" />
        </div>
        <div>
            <x-ui.buttonRefresh.index id="refreshCensorTempData" class="btn-sm" />
        </div>
        <div>
            <x-ui.button.index variant="primary" icon="bx bx-download" class="btn-sm">
                Unduh Data
            </x-ui.button.index>
        </div>
    </div>
    <div class="card h-100">
        <div class="row row-bordered g-0 h-100">
            <div class="col-12 col-md-8">
                <h5 class="card-header m-0 me-2 pb-3">
                    Rata-Rata Data Suhu
                </h5>
                <div id="temperatureChart" class="px-2"></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card-body">
                    <div class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                id="growthReportId" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                Periode Waktu
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="growthReportId">
                                <a class="dropdown-item" href="javascript:void(0);">Minggu Ini</a>
                                <a class="dropdown-item" href="javascript:void(0);">Bulan Ini</a>
                                <a class="dropdown-item" href="javascript:void(0);">Tahun Ini</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="growthChart"></div>
                <div class="text-center fw-semibold pt-3 mb-2">
                    <span id="avgTemperatureText">0°C</span> Rata-Rata Suhu Greenhouse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variable untuk chart
    let temperatureChartInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Ambil Element berdasarkan ID yang Anda tulis di HTML component di atas
        const datePickerTemp = document.getElementById('filterTanggalTemp');
        const chartElement = document.querySelector("#temperatureChart");

        // Initialize chart dari dashboards-analytics.js
        initializeTemperatureChart();

        // 2. Logika Update Grafik saat Tanggal Dipilih
        if (datePickerTemp) {
            datePickerTemp.addEventListener('date-range-selected', function(e) {
                // Data dikirim dari component lewat e.detail
                const {
                    dateStr,
                    selectedDates
                } = e.detail;

                console.log('User memilih tanggal:', dateStr);

                if (dateStr.includes(' to ')) {
                    const [startDate, endDate] = dateStr.split(' to ').map(d => d.trim());

                    // Panggil fungsi update Chart
                    updateTemperatureChartWithDateRange(startDate, endDate);
                    console.log(`Filter Grafik dari ${startDate} sampai ${endDate}`);

                } else {
                    console.log("Menunggu pemilihan tanggal selesai...");
                }
            });
        }

        // 3. Logika Tombol Refresh
        const btnRefresh = document.getElementById('refreshCensorTempData');
        if (btnRefresh && datePickerTemp) {
            btnRefresh.addEventListener('click', function() {
                // Clear input tanggal via Flatpickr instance
                if (datePickerTemp._flatpickr) {
                    datePickerTemp._flatpickr.clear();
                }

                // Reset grafik ke data default
                loadDefaultTemperatureData();
                console.log("Grafik di-refresh ke data default");
            });
        }

        // Initialize dengan data default saat page load
        loadDefaultTemperatureData();
    });

    /**
     * Initialize temperature chart (dipanggil dari dashboards-analytics.js)
     */
    function initializeTemperatureChart() {
        const temperatureChartEl = document.querySelector('#temperatureChart');
        if (!temperatureChartEl) {
            console.error('Temperature chart element tidak ditemukan');
            return;
        }

        const temperatureChartOptions = {
            series: [{
                name: 'Suhu (°C)',
                data: [0, 0, 0, 0, 0, 0, 0]
            }],
            chart: {
                height: 300,
                stacked: true,
                type: 'bar',
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '33%',
                    borderRadius: 12,
                    startingShape: 'rounded',
                    endingShape: 'rounded'
                }
            },
            colors: ['#00704A'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 6,
                lineCap: 'round',
                colors: ['#fff']
            },
            legend: {
                show: true,
                horizontalAlign: 'left',
                position: 'top',
                markers: {
                    height: 8,
                    width: 8,
                    radius: 12,
                    offsetX: -3
                },
                labels: {
                    colors: '#00704A'
                },
                itemMargin: {
                    horizontal: 10
                }
            },
            grid: {
                borderColor: '#e0e0e0',
                padding: {
                    top: 0,
                    bottom: -8,
                    left: 20,
                    right: 20
                }
            },
            xaxis: {
                categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                labels: {
                    style: {
                        fontSize: '13px',
                        colors: '#00704A'
                    }
                },
                axisTicks: {
                    show: false
                },
                axisBorder: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '13px',
                        colors: '#00704A'
                    }
                }
            }
        };

        temperatureChartInstance = new ApexCharts(temperatureChartEl, temperatureChartOptions);
        temperatureChartInstance.render();
    }

    /**
     * Load default temperature data
     */
    async function loadDefaultTemperatureData() {
        try {
            const response = await fetch('/api/temperatures');
            const result = await response.json();

            if (result.success && temperatureChartInstance) {
                // Update chart dengan data dari database
                temperatureChartInstance.updateSeries([{
                    name: 'Rata-Rata Suhu (°C)',
                    data: result.temperatureValues || [0, 0, 0, 0, 0, 0, 0]
                }]);

                // Update x-axis labels dengan tanggal
                if (result.dates && result.dates.length > 0) {
                    temperatureChartInstance.updateOptions({
                        xaxis: {
                            categories: result.dates.slice(0, 7)
                        }
                    });
                }

                // Update rata-rata temperature display
                const avgTempElement = document.getElementById('avgTemperatureText');
                if (avgTempElement) {
                    avgTempElement.textContent = result.average + '°C';
                }

                console.log('Data temperature default berhasil dimuat:', result);
            }
        } catch (error) {
            console.error('Error loading default temperature data:', error);
        }
    }

    /**
     * Update temperature chart dengan date range
     */
    async function updateTemperatureChartWithDateRange(startDate, endDate) {
        try {
            const url = `/api/temperatures?start_date=${startDate}&end_date=${endDate}`;
            const response = await fetch(url);
            const result = await response.json();

            if (result.success && temperatureChartInstance) {
                // Update chart dengan data dari database
                temperatureChartInstance.updateSeries([{
                    name: 'Rata-Rata Suhu (°C)',
                    data: result.temperatureValues || [0, 0, 0, 0, 0, 0, 0]
                }]);

                // Update x-axis labels dengan tanggal
                if (result.dates && result.dates.length > 0) {
                    temperatureChartInstance.updateOptions({
                        xaxis: {
                            categories: result.dates
                        }
                    });
                }

                // Update rata-rata temperature display
                const avgTempElement = document.getElementById('avgTemperatureText');
                if (avgTempElement) {
                    avgTempElement.textContent = result.average + '°C';
                }

                console.log('Data temperature dengan date range berhasil dimuat:', result);
            }
        } catch (error) {
            console.error('Error updating temperature chart with date range:', error);
        }
    }
</script>
