<div class="col-12 col-sm-12 col-md-10 col-lg-8 col-xl-8 order-2 order-md-3 order-lg-2 mb-4 w-100 pt-4">
    <div class="d-flex justify-content-end gap-2 mb-3 flex-column flex-sm-row">
        <div style="min-width: 200px; max-width: 250px;" class="grow flex-sm-grow-0">
            <x-ui.dateRangePicker.index id="filterTanggalHum" placeholder="Filter Tanggal" class="form-control-sm" />
        </div>
        <div>
            <x-ui.buttonRefresh.index id="refreshCensorHumData" class="btn-sm" />
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
                    Rata-Rata Data Kelembapan
                </h5>
                <div id="humidityChartContainer" class="px-2"></div>
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
                    <span id="avgHumidityText">0%</span> Rata-Rata Kelembapan Greenhouse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variable untuk chart
    let humidityChartInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Ambil Element berdasarkan ID yang Anda tulis di HTML component di atas
        const datePickerHum = document.getElementById('filterTanggalHum');
        const chartElement = document.querySelector("#humidityChart");

        // Initialize chart dari dashboards-analytics.js
        initializeHumidityChart();

        // 2. Logika Update Grafik saat Tanggal Dipilih
        if (datePickerHum) {
            datePickerHum.addEventListener('date-range-selected', function(e) {
                // Data dikirim dari component lewat e.detail
                const {
                    dateStr,
                    selectedDates
                } = e.detail;

                console.log('User memilih tanggal:', dateStr);

                if (dateStr.includes(' to ')) {
                    const [startDate, endDate] = dateStr.split(' to ').map(d => d.trim());

                    // Panggil fungsi update Chart
                    updateHumidityChartWithDateRange(startDate, endDate);
                    console.log(`Filter Grafik dari ${startDate} sampai ${endDate}`);

                } else {
                    console.log("Menunggu pemilihan tanggal selesai...");
                }
            });
        }

        // 3. Logika Tombol Refresh
        const btnRefresh = document.getElementById('refreshCensorHumData');
        if (btnRefresh && datePickerHum) {
            btnRefresh.addEventListener('click', function() {
                // Clear input tanggal via Flatpickr instance
                if (datePickerHum._flatpickr) {
                    datePickerHum._flatpickr.clear();
                }

                // Reset grafik ke data default
                loadDefaultHumidityData();
                console.log("Grafik di-refresh ke data default");
            });
        }

        // Initialize dengan data default saat page load
        loadDefaultHumidityData();
    });

    /**
     * Initialize humidity chart (dipanggil dari dashboards-analytics.js)
     */
    function initializeHumidityChart() {
        const humidityChartEl = document.querySelector('#humidityChartContainer');
        if (!humidityChartEl) {
            console.error('Humidity chart element tidak ditemukan');
            return;
        }

        const humidityChartOptions = {
            series: [{
                name: 'Kelembapan (%)',
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

        humidityChartInstance = new ApexCharts(humidityChartEl, humidityChartOptions);
        humidityChartInstance.render();
    }

    /**
     * Load default temperature data
     */
    async function loadDefaultHumidityData() {
        try {
            const response = await fetch('/api/humidities');
            const result = await response.json();

            if (result.success && humidityChartInstance) {
                // Update chart dengan data dari database
                humidityChartInstance.updateSeries([{
                    name: 'Rata-Rata Kelembapan (%)',
                    data: result.humidityValues || [0, 0, 0, 0, 0, 0, 0]
                }]);

                // Update x-axis labels dengan tanggal
                if (result.dates && result.dates.length > 0) {
                    humidityChartInstance.updateOptions({
                        xaxis: {
                            categories: result.dates.slice(0, 7)
                        }
                    });
                }

                // Update rata-rata humidity display
                const avgHumidityElement = document.getElementById('avgHumidityText');
                if (avgHumidityElement) {
                    avgHumidityElement.textContent = result.average + '%';
                }

                console.log('Data humidity default berhasil dimuat:', result);
            }
        } catch (error) {
            console.error('Error loading default humidity data:', error);
        }
    }

    /**
     * Update humidity chart dengan date range
     */
    async function updateHumidityChartWithDateRange(startDate, endDate) {
        try {
            const url = `/api/humidities?start_date=${startDate}&end_date=${endDate}`;
            const response = await fetch(url);
            const result = await response.json();

            if (result.success && humidityChartInstance) {
                // Update chart dengan data dari database
                humidityChartInstance.updateSeries([{
                    name: 'Rata-Rata Kelembapan (%)',
                    data: result.humidityValues || [0, 0, 0, 0, 0, 0, 0]
                }]);

                // Update x-axis labels dengan tanggal
                if (result.dates && result.dates.length > 0) {
                    humidityChartInstance.updateOptions({
                        xaxis: {
                            categories: result.dates
                        }
                    });
                }

                // Update rata-rata humidity display
                const avgHumidityElement = document.getElementById('avgHumidityText');
                if (avgHumidityElement) {
                    avgHumidityElement.textContent = result.average + '%';
                }

                console.log('Data humidity dengan date range berhasil dimuat:', result);
            }
        } catch (error) {
            console.error('Error updating humidity chart with date range:', error);
        }
    }
</script>
