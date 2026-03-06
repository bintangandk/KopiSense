@php
    $authUser = auth()->user();
    $defaultAvatar = asset('assets/img/avatars/1.png');
    $avatarUrl = $authUser->profile?->profile_picture
        ? asset('storage/' . $authUser->profile->profile_picture)
        : $defaultAvatar;
@endphp

<li class="nav-item navbar-dropdown dropdown-user dropdown">
    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
            <img src="{{ $avatarUrl }}" alt class="w-px-40 h-auto rounded-circle" />
        </div>
    </a>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item" href="#">
                <div class="d-flex">
                    <div class="shrink-0 me-3">
                        <div class="avatar avatar-online">
                            <img src="{{ $avatarUrl }}" alt class="w-px-40 h-auto rounded-circle" />
                        </div>
                    </div>
                    <div class="grow">
                        <span
                            class="fw-semibold d-block">{{ $authUser->profile?->full_name ?? $authUser->username }}</span>
                        <small class="text-muted">{{ ucfirst($authUser->role) }}</small>
                    </div>
                </div>
            </a>
        </li>
        <li>
            <div class="dropdown-divider"></div>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('profile') }}">
                <i class="bx bx-user me-2"></i>
                <span class="align-middle">My Profile</span>
            </a>
        </li>
        <li>
            <div class="dropdown-divider"></div>
        </li>
        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item">
                    <i class="bx bx-power-off me-2"></i>
                    <span class="align-middle">Log Out</span>
                </button>
            </form>
        </li>
    </ul>
</li>
