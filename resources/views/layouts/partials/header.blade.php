<div class="header-container fixed-top">
    <header class="header navbar navbar-expand-sm">
        <ul class="navbar-item theme-brand flex-row  text-center">
            <li class="nav-item theme-logo">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('assets/img/logo.svg') }}" class="navbar-logo" alt="logo">
                </a>
            </li>
            <li class="nav-item theme-text">
                <a href="{{ route('dashboard') }}" class="nav-link">Factory System</a>
            </li>
        </ul>

        <ul class="navbar-item flex-row ml-md-auto">
            {{-- Real-time clock --}}
            <li class="nav-item align-self-center mr-3">
                <span id="current-time" class="text-white"></span>
            </li>

            {{-- User info --}}
            <li class="nav-item dropdown user-profile-dropdown">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <span class="mr-2 d-none d-sm-inline-block text-white">{{ auth()->user()->name }}</span>
                    <span class="badge badge-{{ auth()->user()->role === 'admin' ? 'danger' : (auth()->user()->role === 'supervisor' ? 'warning' : 'info') }}">
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                </a>
                <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">
                    <div class="dropdown-item">
                        <a href="{{ route('profile.edit') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <span>Profile</span>
                        </a>
                    </div>
                    <div class="dropdown-item">
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1-2 2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                <span>Logout</span>
                            </a>
                        </form>
                    </div>
                </div>
            </li>
        </ul>
    </header>
</div>
