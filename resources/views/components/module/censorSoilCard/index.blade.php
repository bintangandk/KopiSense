<div class="col-12 col-sm-12 col-md-10 col-lg-8 col-xl-8 order-2 order-md-3 order-lg-2 mb-4 w-100 pt-4">
    <div class="d-flex justify-content-end gap-2 mb-3 flex-column flex-sm-row">
        <div style="min-width: 200px; max-width: 250px;" class="grow flex-sm-grow-0">
            <x-ui.dateRangePicker.index id="filterTanggalSoil" placeholder="Filter Tanggal" class="form-control-sm" />
        </div>
        <div>
            <x-ui.buttonRefresh.index id="refreshCensorSoilData" class="btn-sm" />
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
                    Rata-Rata Data pH Tanah
                </h5>
                <div id="soilPhChartContainer" class="px-2"></div>
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
                <div id="soilPhGrowthChart"></div>
                <div class="text-center fw-semibold pt-3 mb-2">
                    <span id="avgSoilPhText">0</span> Rata-Rata Kenaikan pH Tanah Greenhouse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variable untuk chart
    let soilPhChartInstance = null;
    let soilPhGrowthChartInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Ambil Element berdasarkan ID yang Anda tulis di HTML component di atas
        const datePickerSoil = document.getElementById('filterTanggalSoil');
        const chartElement = document.querySelector("#soilPhChartContainer");

        // Initialize chart dari dashboards-analytics.js
        initializeSoilPhChart();
        initializeSoilPhGrowthChart();

        // 2. Logika Update Grafik saat Tanggal Dipilih
        if (datePickerSoil) {
            datePickerSoil.addEventListener('date-range-selected', function(e) {
                // Data dikirim dari component lewat e.detail
                const {
                    dateStr,
                    selectedDates
                } = e.detail;

                console.log('User memilih tanggal:', dateStr);

                if (dateStr.includes(' to ')) {
                    const [startDate, endDate] = dateStr.split(' to ').map(d => d.trim());

                    // Panggil fungsi update Chart
                    updateSoilPhChartWithDateRange(startDate, endDate);
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
                        updateSoilPhGrowthChartByPeriode('daily');
                    } else if (periode === 'Minggu Ini') {
                        updateSoilPhGrowthChartByPeriode('weekly');
                    } else if (periode === 'Bulan Ini') {
                        updateSoilPhGrowthChartByPeriode('monthly');
                    } else if (periode === 'Tahun Ini') {
                        updateSoilPhGrowthChartByPeriode('yearly');
                    }
                });
            });
        }

        // 4. Logika Tombol Refresh
        const btnRefresh = document.getElementById('refreshCensorSoilData');
        if (btnRefresh && datePickerSoil) {
            btnRefresh.addEventListener('click', function() {
                // Clear input tanggal via Flatpickr instance
                if (datePickerSoil._flatpickr) {
                    datePickerSoil._flatpickr.clear();
                }

                // Reset grafik ke data default
                loadDefaultSoilPhData();
                updateSoilPhGrowthChartByPeriode('daily');
                console.log("Grafik di-refresh ke data default");
            });
        }

        // Initialize dengan data default saat page load
        loadDefaultSoilPhData();
        updateSoilPhGrowthChartByPeriode('daily');
    });

    /**
     * Initialize soil pH chart
     */
    function initializeSoilPhChart() {
        const soilPhChartEl = document.querySelector('#soilPhChartContainer');
        if (!soilPhChartEl) {
            console.error('Soil pH chart element tidak ditemukan');
            return;
        }

        const soilPhChartOptions = {
            series: [{
                name: 'Rata-Rata pH Tanah',
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

        soilPhChartInstance = new ApexCharts(soilPhChartEl, soilPhChartOptions);
        soilPhChartInstance.render();
    }

    /**
     * Load default soil pH data
     */
    async function loadDefaultSoilPhData() {
        try {
            const response = await fetch('/api/soil-ph');
            const result = await response.json();

            if (result.success && soilPhChartInstance) {
                // Update chart dengan data dari database
                soilPhChartInstance.updateSeries([{
                    name: 'Rata-Rata pH Tanah',
                    data: result.soilPHValues || [0, 0, 0, 0, 0, 0, 0]
                }]);

                // Update x-axis labels dengan tanggal
                if (result.dates && result.dates.length > 0) {
                    soilPhChartInstance.updateOptions({
                        xaxis: {
                            categories: result.dates.slice(0, 7)
                        }
                    });
                }

                // Update rata-rata soil pH display
                const avgSoilPhElement = document.getElementById('avgSoilPhText');
                if (avgSoilPhElement && result.soilPHValues && result.soilPHValues.length > 1) {
                    // Hitung rata-rata kenaikan
                    const phValues = result.soilPHValues;
                    const increases = [];
                    for (let i = 1; i < phValues.length; i++) {
                        increases.push(phValues[i] - phValues[i - 1]);
                    }
                    const avgIncrease = increases.reduce((a, b) => a + b, 0) / increases.length;
                    avgSoilPhElement.textContent = (avgIncrease >= 0 ? '+' : '') + avgIncrease.toFixed(2);
                } else if (avgSoilPhElement) {
                    avgSoilPhElement.textContent = '0';
                }

                console.log('Data soil pH default berhasil dimuat:', result);
            }
        } catch (error) {
            console.error('Error loading default soil pH data:', error);
        }
    }

    /**
     * Update soil pH chart dengan date range
     */
    async function updateSoilPhChartWithDateRange(startDate, endDate) {
        try {
            const url = `/api/soil-ph?start_date=${startDate}&end_date=${endDate}`;
            const response = await fetch(url);
            const result = await response.json();

            if (result.success && soilPhChartInstance) {
                // Update chart dengan data dari database
                soilPhChartInstance.updateSeries([{
                    name: 'Rata-Rata pH Tanah',
                    data: result.soilPHValues || [0, 0, 0, 0, 0, 0, 0]
                }]);

                // Update x-axis labels dengan tanggal
                if (result.dates && result.dates.length > 0) {
                    soilPhChartInstance.updateOptions({
                        xaxis: {
                            categories: result.dates
                        }
                    });
                }

                // Update rata-rata soil pH display
                const avgSoilPhElement = document.getElementById('avgSoilPhText');
                if (avgSoilPhElement && result.soilPHValues && result.soilPHValues.length > 1) {
                    // Hitung rata-rata kenaikan
                    const phValues = result.soilPHValues;
                    const increases = [];
                    for (let i = 1; i < phValues.length; i++) {
                        increases.push(phValues[i] - phValues[i - 1]);
                    }
                    const avgIncrease = increases.reduce((a, b) => a + b, 0) / increases.length;
                    avgSoilPhElement.textContent = (avgIncrease >= 0 ? '+' : '') + avgIncrease.toFixed(2);
                } else if (avgSoilPhElement) {
                    avgSoilPhElement.textContent = '0';
                }

                console.log('Data soil pH dengan date range berhasil dimuat:', result);
            }
        } catch (error) {
            console.error('Error updating soil pH chart with date range:', error);
        }
    }

    /**
     * Initialize Soil pH Growth Chart (Radial Bar Chart)
     */
    function initializeSoilPhGrowthChart() {
        console.log('[initializeSoilPhGrowthChart] Starting initialization');

        const growthChartEl = document.querySelector('#soilPhGrowthChart');
        if (!growthChartEl) {
            console.error('[initializeSoilPhGrowthChart] Element #soilPhGrowthChart not found');
            return;
        }
        console.log('[initializeSoilPhGrowthChart] Element found');

        if (typeof ApexCharts === 'undefined') {
            console.error('[initializeSoilPhGrowthChart] ApexCharts library not loaded');
            return;
        }

        const soilPhGrowthChartOptions = {
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
            soilPhGrowthChartInstance = new ApexCharts(growthChartEl, soilPhGrowthChartOptions);
            console.log('[initializeSoilPhGrowthChart] ApexCharts instance created');
            soilPhGrowthChartInstance.render();
            console.log('[initializeSoilPhGrowthChart] Chart rendered successfully');
        } catch (error) {
            console.error('[initializeSoilPhGrowthChart] Error creating chart:', error);
        }
    }

    /**
     * Update Soil pH Growth Chart berdasarkan periode
     * @param {string} periode - 'daily', 'weekly', 'monthly', atau 'yearly'
     */
    async function updateSoilPhGrowthChartByPeriode(periode) {
        try {
            console.log('[updateSoilPhGrowthChartByPeriode] Periode:', periode);

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

            console.log('[updateSoilPhGrowthChartByPeriode] Date range:', startDate, 'to', endDate);

            const url = `/api/soil-ph?start_date=${startDate}&end_date=${endDate}`;
            const response = await fetch(url);
            const result = await response.json();

            console.log('[updateSoilPhGrowthChartByPeriode] API Response:', result);

            if (!soilPhGrowthChartInstance) {
                console.error('[updateSoilPhGrowthChartByPeriode] soilPhGrowthChartInstance not initialized');
                return;
            }

            if (result.success) {
                // Validasi data
                const soilPhValues = Array.isArray(result.soilPHValues) ? result.soilPHValues : [];
                console.log('[updateSoilPhGrowthChartByPeriode] soilPhValues:', soilPhValues);

                // Calculate growth percentage berdasarkan rata-rata KENAIKAN data
                let growthPercentage = 50; // Default value (50% = no growth)

                if (soilPhValues.length > 1) {
                    // Hitung kenaikan antar data point
                    const increases = [];
                    for (let i = 1; i < soilPhValues.length; i++) {
                        const change = soilPhValues[i] - soilPhValues[i - 1];
                        increases.push(change);
                    }

                    // Hitung rata-rata kenaikan
                    const avgIncrease = increases.reduce((a, b) => a + b, 0) / increases.length;
                    console.log('[updateSoilPhGrowthChartByPeriode] Increases:', increases);
                    console.log('[updateSoilPhGrowthChartByPeriode] Average increase:', avgIncrease);

                    // Normalize kenaikan ke percentage
                    // Range: -1 hingga +1 pH per hari = neutral di 50%
                    // Positive growth = naik (lebih asam/basa), Negative = turun
                    const maxChange = 1; // Max change per day untuk kalkulasi pH
                    growthPercentage = Math.min(100, Math.max(0, ((avgIncrease / maxChange) * 50) + 50));
                    growthPercentage = Math.round(growthPercentage);
                    console.log('[updateSoilPhGrowthChartByPeriode] Growth percentage (based on increase):',
                        growthPercentage);

                    // Update display dengan rata-rata kenaikan
                    const avgSoilPhElement = document.getElementById('avgSoilPhText');
                    if (avgSoilPhElement) {
                        avgSoilPhElement.textContent = (avgIncrease >= 0 ? '+' : '') + avgIncrease.toFixed(2);
                    }
                } else if (soilPhValues.length === 1) {
                    // Hanya 1 data point, tidak bisa hitung kenaikan
                    growthPercentage = 50; // Neutral
                    console.log('[updateSoilPhGrowthChartByPeriode] Only 1 data point, using neutral 50%');

                    // Update display ke 0 (netral)
                    const avgSoilPhElement = document.getElementById('avgSoilPhText');
                    if (avgSoilPhElement) {
                        avgSoilPhElement.textContent = '0';
                    }
                }

                // Update growth chart dengan persentase baru
                soilPhGrowthChartInstance.updateSeries([growthPercentage]);
                console.log('[updateSoilPhGrowthChartByPeriode] Series updated with percentage:', growthPercentage);
            } else {
                console.warn('[updateSoilPhGrowthChartByPeriode] API success is false:', result);
            }
        } catch (error) {
            console.error('[updateSoilPhGrowthChartByPeriode] Error:', error);
        }
    }
</script>
