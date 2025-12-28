@props([
    'id', // ID Wajib diisi agar unik
    'placeholder' => 'Pilih Tanggal',
    'width' => '250px',
])

{{-- Input Element --}}
<input type="text" id="{{ $id }}" {{ $attributes->merge(['class' => 'form-control']) }}
    placeholder="{{ $placeholder }}" style="max-width: {{ $width }};">

{{-- Script Khusus Component Ini --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Cek apakah flatpickr sudah di-load
        if (typeof flatpickr === 'undefined') {
            console.error('Flatpickr library belum di-load!');
            return;
        }

        const elementId = "{{ $id }}";
        const element = document.getElementById(elementId);

        if (element) {
            flatpickr(element, {
                mode: 'range',
                dateFormat: 'Y-m-d', // Format backend friendly (Tahun-Bulan-Tanggal)
                altInput: true, // Tampilkan format yang lebih mudah dibaca user
                altFormat: 'd/m/Y', // Format tampilan: 28/12/2025
                locale: 'id', // Bahasa Indonesia

                // Callback saat user memilih tanggal
                onChange: function(selectedDates, dateStr, instance) {
                    // Dispatch Custom Event agar halaman utama bisa menangkap perubahannya
                    // Nama eventnya unik: 'date-range-selected'
                    const event = new CustomEvent('date-range-selected', {
                        detail: {
                            dateStr: dateStr,
                            selectedDates: selectedDates,
                            elementId: elementId
                        }
                    });
                    element.dispatchEvent(event);
                }
            });
        }
    });
</script>
