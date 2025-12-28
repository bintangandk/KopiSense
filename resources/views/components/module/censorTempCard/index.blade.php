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
                <div id="totalRevenueChart" class="px-2"></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card-body">
                    <div class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                id="growthReportId" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                2022
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="growthReportId">
                                <a class="dropdown-item" href="javascript:void(0);">2021</a>
                                <a class="dropdown-item" href="javascript:void(0);">2020</a>
                                <a class="dropdown-item" href="javascript:void(0);">2019</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="growthChart"></div>
                <div class="text-center fw-semibold pt-3 mb-2">
                    62% Rata-Rata Kenaikan Suhu
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Ambil Element berdasarkan ID yang Anda tulis di HTML component di atas
        const datePickerTemp = document.getElementById('filterTanggalTemp');
        const chartElement = document.querySelector("#totalRevenueChart");

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
                    const [startDate, endDate] = dateStr.split(' to ');

                    // TODO: Panggil fungsi update Chart disini
                    // Contoh: updateTemperatureChart(startDate, endDate);
                    console.log(`Filter Grafik dari ${startDate} sampai ${endDate}`);

                } else {
                    console.log("Menunggu pemilihan tanggal selesai...");
                }
            });
        }

        // 3. Logika Tombol Refresh (Opsional)
        const btnRefresh = document.getElementById('refreshCensorTempData');
        if (btnRefresh && datePickerTemp) {
            btnRefresh.addEventListener('click', function() {
                // Clear input tanggal via Flatpickr instance
                if (datePickerTemp._flatpickr) {
                    datePickerTemp._flatpickr.clear();
                }

                // TODO: Reset grafik ke data default
                console.log("Grafik di-refresh ke data default");
            });
        }
    });
</script>
