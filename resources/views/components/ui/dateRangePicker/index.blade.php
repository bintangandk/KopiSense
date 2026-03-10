@props(['id', 'placeholder' => 'Pilih Tanggal', 'width' => '250px'])

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
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: false,
                locale: 'id',

                // Callback saat kalender ditutup (bukan setiap klik)
                // Ini memastikan event hanya dikirim saat range benar-benar selesai dipilih
                onClose: function(selectedDates, dateStr, instance) {
                    // Abaikan jika tidak ada tanggal yang dipilih
                    if (selectedDates.length < 1) return;

                    // Normalisasi: satu tanggal dipilih = start sama dengan end
                    const fmt = (d) => instance.formatDate(d, instance.config.dateFormat);
                    const startStr = fmt(selectedDates[0]);
                    const endStr = fmt(selectedDates[selectedDates.length - 1]);
                    const normalizedDateStr = startStr + ' to ' + endStr;

                    // Dispatch Custom Event dengan dateStr yang selalu berformat "start to end"
                    const event = new CustomEvent('date-range-selected', {
                        bubbles: true,
                        detail: {
                            dateStr: normalizedDateStr,
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
