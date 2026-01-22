@props(['placeholder' => 'cari data...', 'name' => 'search', 'value' => ''])
<div class="navbar-nav align-items-center">
    <div class="nav-item d-flex align-items-center">
        <i class="bx bx-search fs-4 lh-0"></i>
        <input type="text" name="{{ $name }}" class="form-control border-0 shadow-none"
            placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}" value="{{ $value }}" />
    </div>
</div>
