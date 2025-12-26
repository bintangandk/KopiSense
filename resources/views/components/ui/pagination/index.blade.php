<div class="card-body pt-3">
    <div class="d-flex justify-content-end">
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0" id="pagination">
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
    const itemsPerPage = 5;
    let currentPage = 1;
    const allRows = [];
    const filteredRows = [];

    function initPagination() {
        const tableBody = document.getElementById('tableBody');
        allRows.length = 0;
        tableBody.querySelectorAll('tr').forEach(row => {
            allRows.push(row.cloneNode(true));
        });
        filteredRows.length = 0;
        filteredRows.push(...allRows);
        renderPagination();
        renderTable();
    }

    function renderTable() {
        const tableBody = document.getElementById('tableBody');
        tableBody.innerHTML = '';

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const visibleRows = filteredRows.slice(startIndex, endIndex);

        visibleRows.forEach(row => {
            tableBody.appendChild(row.cloneNode(true));
        });
    }

    function renderPagination() {
        const pagination = document.getElementById('pagination');
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);

        // Remove existing page numbers
        const pageItems = pagination.querySelectorAll('.page-item.page-num');
        pageItems.forEach(item => item.remove());

        // Add page numbers before next button
        const nextBtn = pagination.querySelector('.next');
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = 'page-item page-num' + (i === currentPage ? ' active' : '');
            li.innerHTML = `<a class="page-link" href="javascript:void(0);">${i}</a>`;
            li.addEventListener('click', () => {
                currentPage = i;
                renderTable();
                renderPagination();
                window.scrollTo(0, 0);
            });
            pagination.insertBefore(li, nextBtn);
        }

        // Enable/disable prev button
        const prevBtn = pagination.querySelector('.prev');
        if (currentPage === 1) {
            prevBtn.classList.add('disabled');
        } else {
            prevBtn.classList.remove('disabled');
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage--;
                renderTable();
                renderPagination();
                window.scrollTo(0, 0);
            });
        }

        // Enable/disable next button
        const nextBtn2 = pagination.querySelector('.next');
        if (currentPage === totalPages) {
            nextBtn2.classList.add('disabled');
        } else {
            nextBtn2.classList.remove('disabled');
            nextBtn2.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage++;
                renderTable();
                renderPagination();
                window.scrollTo(0, 0);
            });
        }
    }

    // Initialize on page load
    initPagination();
</script>
