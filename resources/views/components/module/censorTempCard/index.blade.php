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
                                <a class="dropdown-item" href="javascript:void(0);">Hari Ini</a>
                                <a class="dropdown-item" href="javascript:void(0);">Minggu Ini</a>
                                <a class="dropdown-item" href="javascript:void(0);">Bulan Ini</a>
                                <a class="dropdown-item" href="javascript:void(0);">Tahun Ini</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="TempGrowthChart"></div>
                <div class="text-center fw-semibold pt-3 mb-2">
                    <span id="avgTemperatureText">0°C</span> Rata-Rata Kenaikan Suhu Greenhouse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variable untuk chart
    let temperatureChartInstance = null;
    let temperatureGrowthChartInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Ambil Element berdasarkan ID yang Anda tulis di HTML component di atas
        const datePickerTemp = document.getElementById('filterTanggalTemp');
        const chartElement = document.querySelector("#temperatureChart");

        // Initialize chart dari dashboards-analytics.js
        initializeTemperatureChart();
        initializeTemperatureGrowthChart();

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

        // 3. Logika Tombol Dropdown Periode Waktu
        const periodeButtons = document.querySelectorAll('[aria-labelledby="growthReportId"]');
        if (periodeButtons && periodeButtons.length > 0) {
            const dropdown = periodeButtons[0];
            const dropdownItems = dropdown.querySelectorAll('.dropdown-item');

            dropdownItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const periode = this.textContent.trim();
                    console.log('Selected periode:', periode);

                    if (periode === 'Hari Ini') {
                        updateTemperatureGrowthChartByPeriode('daily');
                    } else if (periode === 'Minggu Ini') {
                        updateTemperatureGrowthChartByPeriode('weekly');
                    } else if (periode === 'Bulan Ini') {
                        updateTemperatureGrowthChartByPeriode('monthly');
                    } else if (periode === 'Tahun Ini') {
                        updateTemperatureGrowthChartByPeriode('yearly');
                    }
                });
            });
        }

        // 4. Logika Tombol Refresh
        const btnRefresh = document.getElementById('refreshCensorTempData');
        if (btnRefresh && datePickerTemp) {
            btnRefresh.addEventListener('click', function() {
                // Clear input tanggal via Flatpickr instance
                if (datePickerTemp._flatpickr) {
                    datePickerTemp._flatpickr.clear();
                }

                // Reset grafik ke data default
                loadDefaultTemperatureData();
                updateTemperatureGrowthChartByPeriode('daily');
                console.log("Grafik di-refresh ke data default");
            });
        }

        // Initialize dengan data default saat page load
        loadDefaultTemperatureData();
        updateTemperatureGrowthChartByPeriode('daily');
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
                if (avgTempElement && result.temperatureValues && result.temperatureValues.length > 1) {
                    // Hitung rata-rata kenaikan
                    const tempValues = result.temperatureValues;
                    const increases = [];
                    for (let i = 1; i < tempValues.length; i++) {
                        increases.push(tempValues[i] - tempValues[i - 1]);
                    }
                    const avgIncrease = increases.reduce((a, b) => a + b, 0) / increases.length;
                    avgTempElement.textContent = (avgIncrease >= 0 ? '+' : '') + avgIncrease.toFixed(2) + '°C';
                } else if (avgTempElement) {
                    avgTempElement.textContent = '0°C';
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
                if (avgTempElement && result.temperatureValues && result.temperatureValues.length > 1) {
                    // Hitung rata-rata kenaikan
                    const tempValues = result.temperatureValues;
                    const increases = [];
                    for (let i = 1; i < tempValues.length; i++) {
                        increases.push(tempValues[i] - tempValues[i - 1]);
                    }
                    const avgIncrease = increases.reduce((a, b) => a + b, 0) / increases.length;
                    avgTempElement.textContent = (avgIncrease >= 0 ? '+' : '') + avgIncrease.toFixed(2) + '°C';
                } else if (avgTempElement) {
                    avgTempElement.textContent = '0°C';
                }

                console.log('Data temperature dengan date range berhasil dimuat:', result);
            }
        } catch (error) {
            console.error('Error updating temperature chart with date range:', error);
        }
    }

    /**
     * Initialize Temperature Growth Chart (Radial Bar Chart)
     */
    function initializeTemperatureGrowthChart() {
        console.log('[initializeTemperatureGrowthChart] Starting initialization');

        const growthChartEl = document.querySelector('#TempGrowthChart');
        if (!growthChartEl) {
            console.error('[initializeTemperatureGrowthChart] Element #TempGrowthChart not found');
            return;
        }
        console.log('[initializeTemperatureGrowthChart] Element found');

        if (typeof ApexCharts === 'undefined') {
            console.error('[initializeTemperatureGrowthChart] ApexCharts library not loaded');
            return;
        }

        const temperatureGrowthChartOptions = {
            series: [78],
            labels: ['Growth'],
            chart: {
                height: 240,
                type: 'radialBar'
            },
            plotOptions: {
                radialBar: {
                    size: 150,
                    offsetY: 10,
                    startAngle: -150,
                    endAngle: 150,
                    hollow: {
                        size: '55%'
                    },
                    track: {
                        background: '#fff',
                        strokeWidth: '100%'
                    },
                    dataLabels: {
                        name: {
                            offsetY: 15,
                            color: '#333',
                            fontSize: '15px',
                            fontWeight: '600',
                            fontFamily: 'Public Sans'
                        },
                        value: {
                            offsetY: -25,
                            color: '#333',
                            fontSize: '22px',
                            fontWeight: '500',
                            fontFamily: 'Public Sans'
                        }
                    }
                }
            },
            colors: ['#00704A'],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    shadeIntensity: 0.5,
                    gradientToColors: ['#00704A'],
                    inverseColors: true,
                    opacityFrom: 1,
                    opacityTo: 0.6,
                    stops: [30, 70, 100]
                }
            },
            stroke: {
                dashArray: 5
            },
            grid: {
                padding: {
                    top: -35,
                    bottom: -10
                }
            },
            states: {
                hover: {
                    filter: {
                        type: 'none'
                    }
                },
                active: {
                    filter: {
                        type: 'none'
                    }
                }
            }
        };

        try {
            temperatureGrowthChartInstance = new ApexCharts(growthChartEl, temperatureGrowthChartOptions);
            console.log('[initializeTemperatureGrowthChart] ApexCharts instance created');
            temperatureGrowthChartInstance.render();
            console.log('[initializeTemperatureGrowthChart] Chart rendered successfully');
        } catch (error) {
            console.error('[initializeTemperatureGrowthChart] Error creating chart:', error);
        }
    }

    /**
     * Update Temperature Growth Chart berdasarkan periode
     * @param {string} periode - 'daily', 'weekly', 'monthly', atau 'yearly'
     */
    async function updateTemperatureGrowthChartByPeriode(periode) {
        try {
            console.log('[updateTemperatureGrowthChartByPeriode] Periode:', periode);

            let startDate, endDate;
            const today = new Date();
            const todayFormatted = today.toISOString().split('T')[0]; // YYYY-MM-DD

            if (periode === 'daily') {
                // Hari ini saja
                startDate = todayFormatted;
                endDate = todayFormatted;
            } else if (periode === 'weekly') {
                // Minggu ini (7 hari ke belakang)
                const weekAgo = new Date(today);
                weekAgo.setDate(weekAgo.getDate() - 7);
                startDate = weekAgo.toISOString().split('T')[0];
                endDate = todayFormatted;
            } else if (periode === 'monthly') {
                // Bulan ini
                const monthAgo = new Date(today);
                monthAgo.setMonth(monthAgo.getMonth() - 1);
                startDate = monthAgo.toISOString().split('T')[0];
                endDate = todayFormatted;
            } else if (periode === 'yearly') {
                // Tahun ini
                const yearAgo = new Date(today);
                yearAgo.setFullYear(yearAgo.getFullYear() - 1);
                startDate = yearAgo.toISOString().split('T')[0];
                endDate = todayFormatted;
            }

            console.log('[updateTemperatureGrowthChartByPeriode] Date range:', startDate, 'to', endDate);

            const url = `/api/temperatures?start_date=${startDate}&end_date=${endDate}`;
            const response = await fetch(url);
            const result = await response.json();

            console.log('[updateTemperatureGrowthChartByPeriode] API Response:', result);

            if (!temperatureGrowthChartInstance) {
                console.error(
                    '[updateTemperatureGrowthChartByPeriode] temperatureGrowthChartInstance not initialized');
                return;
            }

            if (result.success) {
                // Validasi data
                const temperatureValues = Array.isArray(result.temperatureValues) ? result.temperatureValues : [];
                console.log('[updateTemperatureGrowthChartByPeriode] temperatureValues:', temperatureValues);

                // Calculate growth percentage berdasarkan rata-rata KENAIKAN data
                let growthPercentage = 50; // Default value (50% = no growth)

                if (temperatureValues.length > 1) {
                    // Hitung kenaikan antar data point
                    const increases = [];
                    for (let i = 1; i < temperatureValues.length; i++) {
                        const change = temperatureValues[i] - temperatureValues[i - 1];
                        increases.push(change);
                    }

                    // Hitung rata-rata kenaikan
                    const avgIncrease = increases.reduce((a, b) => a + b, 0) / increases.length;
                    console.log('[updateTemperatureGrowthChartByPeriode] Increases:', increases);
                    console.log('[updateTemperatureGrowthChartByPeriode] Average increase:', avgIncrease);

                    // Normalize kenaikan ke percentage
                    // Range: -5 hingga +5 °C per hari = neutral di 50%
                    // Positive growth = naik, Negative = turun
                    const maxChange = 5; // Max change per day untuk kalkulasi
                    growthPercentage = Math.min(100, Math.max(0, ((avgIncrease / maxChange) * 50) + 50));
                    growthPercentage = Math.round(growthPercentage);
                    console.log('[updateTemperatureGrowthChartByPeriode] Growth percentage (based on increase):',
                        growthPercentage);

                    // Update display dengan rata-rata kenaikan
                    const avgTempElement = document.getElementById('avgTemperatureText');
                    if (avgTempElement) {
                        avgTempElement.textContent = (avgIncrease >= 0 ? '+' : '') + avgIncrease.toFixed(2) + '°C';
                    }
                } else if (temperatureValues.length === 1) {
                    // Hanya 1 data point, tidak bisa hitung kenaikan
                    growthPercentage = 50; // Neutral
                    console.log('[updateTemperatureGrowthChartByPeriode] Only 1 data point, using neutral 50%');

                    // Update display ke 0°C (netral)
                    const avgTempElement = document.getElementById('avgTemperatureText');
                    if (avgTempElement) {
                        avgTempElement.textContent = '0°C';
                    }
                }

                // Update growth chart dengan persentase baru
                temperatureGrowthChartInstance.updateSeries([growthPercentage]);
                console.log('[updateTemperatureGrowthChartByPeriode] Series updated with percentage:',
                    growthPercentage);
            } else {
                console.warn('[updateTemperatureGrowthChartByPeriode] API success is false:', result);
            }
        } catch (error) {
            console.error('[updateTemperatureGrowthChartByPeriode] Error:', error);
        }
    }
</script>
