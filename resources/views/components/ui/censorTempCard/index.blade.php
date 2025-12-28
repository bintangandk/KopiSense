<div class="col-12 col-sm-12 col-md-10 col-lg-8 col-xl-8 order-2 order-md-3 order-lg-2 mb-4 w-100 pt-4">
    <div class="d-flex justify-content-end gap-2 mb-3 flex-column flex-sm-row">
        <input type="text" class="form-control form-control-sm" id="dateRangePicker" placeholder="Pilih Tanggal"
            style="max-width: 250px;">
        <button type="button" class="btn btn-outline-secondary" id="refreshBtn" title="Refresh">
            <i class="bx bx-refresh"></i>
        </button>
        <button class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="bx bx-printer me-2"></i>Print Data
        </button>
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
        // Initialize date range picker
        const dateRangeInput = document.getElementById('dateRangePicker');

        // Menggunakan Flatpickr (lightweight date picker)
        if (typeof flatpickr !== 'undefined') {
            flatpickr(dateRangeInput, {
                mode: 'range',
                dateFormat: 'd-m-Y',
                locale: 'id',
                onChange: function(selectedDates, dateStr, instance) {
                    console.log('Date range selected:', dateStr);
                    // Tambahkan logika untuk filter data di sini
                }
            });
        } else {
            // Fallback: gunakan HTML5 date input
            dateRangeInput.type = 'date';
        }
    });
</script>
