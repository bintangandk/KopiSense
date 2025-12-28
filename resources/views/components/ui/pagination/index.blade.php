@props(['id'])

<div class="card-body pt-3">
    <div class="d-flex justify-content-end">
        <nav aria-label="Page navigation">
            {{-- Kita tempelkan ID yang diterima ke ul --}}
            <ul {{ $attributes->merge(['class' => 'pagination mb-0', 'id' => $id]) }}>
                <li class="page-item prev">
                    <a class="page-link" href="javascript:void(0);">
                        <i class="tf-icon bx bx-chevron-left"></i> Previous
                    </a>
                </li>
                <li class="page-item next">
                    <a class="page-link" href="javascript:void(0);">
                        Next <i class="tf-icon bx bx-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<script>
    // Cek agar Class tidak dideklarasikan ulang jika component di-include berkali-kali
    if (typeof TablePaginator === 'undefined') {
        class TablePaginator {
            constructor(tableBodyId, paginationId, itemsPerPage = 5) {
                this.tableBody = document.getElementById(tableBodyId);
                this.paginationContainer = document.getElementById(paginationId);
                this.itemsPerPage = itemsPerPage;
                this.currentPage = 1;
                this.allRows = []; // Menyimpan semua data asli
                this.filteredRows = []; // Menyimpan data hasil search

                if (!this.tableBody || !this.paginationContainer) {
                    console.error("Element ID not found:", tableBodyId, paginationId);
                    return;
                }

                // Simpan semua baris asli saat inisialisasi
                this.tableBody.querySelectorAll('tr').forEach(row => {
                    this.allRows.push(row.cloneNode(true));
                });

                // Awalnya, filteredRows sama dengan semua data
                this.filteredRows = [...this.allRows];

                this.init();
            }

            init() {
                this.renderTable();
                this.renderPagination();
            }

            // Method Baru: Untuk menangani Search
            search(keyword) {
                const lowerKeyword = keyword.toLowerCase();

                // Filter dari allRows, simpan ke filteredRows
                this.filteredRows = this.allRows.filter(row => {
                    return row.textContent.toLowerCase().includes(lowerKeyword);
                });

                this.currentPage = 1; // Reset ke halaman 1 setiap kali search
                this.updateView();
            }

            // Method Baru: Untuk menangani Refresh
            refresh() {
                this.filteredRows = [...this.allRows]; // Kembalikan ke data asli
                this.currentPage = 1;
                this.updateView();
            }

            renderTable() {
                this.tableBody.innerHTML = '';
                const startIndex = (this.currentPage - 1) * this.itemsPerPage;
                const endIndex = startIndex + this.itemsPerPage;

                // Render dari filteredRows, bukan allRows
                const visibleRows = this.filteredRows.slice(startIndex, endIndex);

                if (visibleRows.length === 0) {
                    this.tableBody.innerHTML =
                        '<tr><td colspan="100%" class="text-center">Tidak ada data ditemukan</td></tr>';
                } else {
                    visibleRows.forEach(row => this.tableBody.appendChild(row.cloneNode(true)));
                }
            }

            renderPagination() {
                // Hitung total page berdasarkan filteredRows
                const totalPages = Math.ceil(this.filteredRows.length / this.itemsPerPage);

                let paginationHTML = `
                <li class="page-item prev ${this.currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);"><i class="tf-icon bx bx-chevron-left"></i> Previous</a>
                </li>`;

                for (let i = 1; i <= totalPages; i++) {
                    paginationHTML += `
                    <li class="page-item page-num ${i === this.currentPage ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0);">${i}</a>
                    </li>`;
                }

                paginationHTML += `
                <li class="page-item next ${this.currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);">Next <i class="tf-icon bx bx-chevron-right"></i></a>
                </li>`;

                this.paginationContainer.innerHTML = paginationHTML;
                this.addEventListeners(totalPages);
            }

            addEventListeners(totalPages) {
                this.paginationContainer.querySelectorAll('.page-num').forEach((item, index) => {
                    item.addEventListener('click', () => {
                        this.currentPage = index + 1;
                        this.updateView();
                    });
                });

                const prevBtn = this.paginationContainer.querySelector('.prev');
                if (prevBtn && !prevBtn.classList.contains('disabled')) {
                    prevBtn.addEventListener('click', () => {
                        this.currentPage--;
                        this.updateView();
                    });
                }

                const nextBtn2 = this.paginationContainer.querySelector('.next');
                if (nextBtn2 && !nextBtn2.classList.contains('disabled')) {
                    nextBtn2.addEventListener('click', () => {
                        this.currentPage++;
                        this.updateView();
                    });
                }
            }

            updateView() {
                this.renderTable();
                this.renderPagination();
            }
        }
    }
</script>
