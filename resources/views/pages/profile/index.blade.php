@extends('layouts.app')

@section('content')
    <div class="container-xxl grow container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Account Settings /</span>
            Account
        </h4>

        <div class="row">
            <div class="col-md-12">
                {{-- Form Profile --}}
                @include('components.module.formProfile.index')
                {{-- /Form Profile --}}
            </div>
        </div>
    </div>
@endsection
