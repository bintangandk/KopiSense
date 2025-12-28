@extends('layouts.app')

@section('content')
    <div class="container-xxl grow container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Data /</span> Data pH Tanah</h4>
        <div class="row">
            <div class="d-flex justify-content-start align-items-center">
                <span
                    style="width: 20px; height: 20px; background-color: #000000; border-radius: 50%; display: inline-block; margin-right: 8px;"></span>
                <p class="font-weight-bold mb-0">Arahkan Kursor untuk melihat nilai pH Tanah </p>
            </div>

            <div class="d-flex justify-content-start align-items-center mt-2">
                <span
                    style="width: 20px; height: 20px; background-color: #00704A; border-radius: 50%; display: inline-block; margin-right: 8px;"></span>
                <p class="font-weight-bold mb-0">Nilai pH Tanah</p>
            </div>

            <!-- Card Censor -->
            @include('components.module.censorSoilCard.index')
            <!--/ Card Censor -->
        </div>
    </div>
@endsection
