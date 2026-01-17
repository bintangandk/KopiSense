<!-- Content -->
@extends('layouts.app')

@section('content')
    <div class="container-xxl grow container-p-y">
        <div class="row">
            <div class="col-lg-8 mb-4 order-0">
                @include('components.ui.welcomeCard.index')
            </div>

            <div class="max-w-7xl mx-auto p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">


                    @include('components.ui.cardDeviceIoT.index', [
                        'suhu' => 27,
                        'kelembapan' => 80,
                        'ph' => 7.6,
                        'tanggal' => 'Sabtu, 19 Januari 2026',
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
