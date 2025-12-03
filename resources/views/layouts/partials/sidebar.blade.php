<div class="sidebar-wrapper sidebar-theme">
    <nav id="sidebar">
        <div class="navbar-nav theme-brand flex-row text-center">
            <div class="nav-logo">
                <div class="nav-item theme-logo">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('assets/src/assets/img/logo.svg') }}" class="navbar-logo" alt="logo">
                    </a>
                </div>
                <div class="nav-item theme-text">
                    <a href="{{ route('dashboard') }}" class="nav-link">Factory</a>
                </div>
            </div>
            <div class="nav-item sidebar-toggle">
                <div class="btn-toggle sidebarCollapse">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevrons-left">
                        <polyline points="11 17 6 12 11 7"></polyline>
                        <polyline points="18 17 13 12 18 7"></polyline>
                    </svg>
                </div>
            </div>
        </div>

        <div class="shadow-bottom"></div>

        <ul class="list-unstyled menu-categories" id="accordionExample">

            {{-- ============================================ --}}
            {{-- DASHBOARD - All Users --}}
            {{-- ============================================ --}}
            <li class="menu {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span>Dashboard</span>
                    </div>
                </a>
            </li>

            {{-- ============================================ --}}
            {{-- SUPERVISOR MENU - Supervisors Only --}}
            {{-- ============================================ --}}
            @if(auth()->user()->role === 'supervisor')
                <li class="menu {{ request()->routeIs('supervisor.*') ? 'active' : '' }}">
                    <a href="#supervisorMenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('supervisor.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                <circle cx="12" cy="13" r="4"></circle>
                            </svg>
                            <span>Supervisor</span>
                        </div>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled {{ request()->routeIs('supervisor.*') ? 'show' : '' }}" id="supervisorMenu" data-bs-parent="#accordionExample">
                        <li class="{{ request()->routeIs('supervisor.scan') ? 'active' : '' }}">
                            <a href="{{ route('supervisor.scan') }}">QR Scanner</a>
                        </li>
                        <li class="{{ request()->routeIs('supervisor.quick-select') ? 'active' : '' }}">
                            <a href="{{ route('supervisor.quick-select') }}">Quick Select Table</a>
                        </li>
                        <li class="{{ request()->routeIs('supervisor.my-activity') ? 'active' : '' }}">
                            <a href="{{ route('supervisor.my-activity') }}">My Activity</a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- ============================================ --}}
            {{-- TABLES - All Users (View), Admin (Full) --}}
            {{-- ============================================ --}}
            <li class="menu {{ request()->routeIs('tables.*') ? 'active' : '' }}">
                <a href="#tablesMenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('tables.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Tables</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('tables.*') ? 'show' : '' }}" id="tablesMenu" data-bs-parent="#accordionExample">
                    <li class="{{ request()->routeIs('tables.index') ? 'active' : '' }}">
                        <a href="{{ route('tables.index') }}">View All Tables</a>
                    </li>
                    @if(auth()->user()->role === 'admin')
                        <li class="{{ request()->routeIs('tables.create') ? 'active' : '' }}">
                            <a href="{{ route('tables.create') }}">Add New Table</a>
                        </li>
                    @endif
                </ul>
            </li>

            {{-- ============================================ --}}
            {{-- WORKERS - All Users (View), Admin (Full) --}}
            {{-- ============================================ --}}
            <li class="menu {{ request()->routeIs('workers.*') ? 'active' : '' }}">
                <a href="#workersMenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('workers.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Workers</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('workers.*') ? 'show' : '' }}" id="workersMenu" data-bs-parent="#accordionExample">
                    <li class="{{ request()->routeIs('workers.index') ? 'active' : '' }}">
                        <a href="{{ route('workers.index') }}">View All Workers</a>
                    </li>
                    @if(auth()->user()->role === 'admin')
                        <li class="{{ request()->routeIs('workers.create') ? 'active' : '' }}">
                            <a href="{{ route('workers.create') }}">Add New Worker</a>
                        </li>
                    @endif
                </ul>
            </li>

            {{-- ============================================ --}}
            {{-- ASSIGNMENTS - Admin Only --}}
            {{-- ============================================ --}}
            @if(auth()->user()->role === 'admin')
                <li class="menu {{ request()->routeIs('assignments.*') ? 'active' : '' }}">
                    <a href="#assignmentsMenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('assignments.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                            <span>Assignments</span>
                        </div>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled {{ request()->routeIs('assignments.*') ? 'show' : '' }}" id="assignmentsMenu" data-bs-parent="#accordionExample">
                        <li class="{{ request()->routeIs('assignments.index') ? 'active' : '' }}">
                            <a href="{{ route('assignments.index') }}">View Assignments</a>
                        </li>
                        <li class="{{ request()->routeIs('assignments.create') ? 'active' : '' }}">
                            <a href="{{ route('assignments.create') }}">Create Assignment</a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- ============================================ --}}
            {{-- REPORTS - Admin & Monitor Only --}}
            {{-- ============================================ --}}
            @if(in_array(auth()->user()->role, ['admin', 'monitor']))
                <li class="menu {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <a href="#reportsMenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                            <span>Reports</span>
                        </div>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="reportsMenu" data-bs-parent="#accordionExample">
                        <li class="{{ request()->routeIs('reports.index') ? 'active' : '' }}">
                            <a href="{{ route('reports.index') }}">Reports Dashboard</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.daily') ? 'active' : '' }}">
                            <a href="{{ route('reports.daily') }}">Daily Report</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.monthly') ? 'active' : '' }}">
                            <a href="{{ route('reports.monthly') }}">Monthly Report</a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- ============================================ --}}
            {{-- LIVE MONITOR - All Users --}}
            {{-- ============================================ --}}
            <li class="menu {{ request()->routeIs('monitor') ? 'active' : '' }}">
                <a href="{{ route('monitor') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-monitor">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        <span>Live Monitor</span>
                    </div>
                </a>
            </li>

            {{-- ============================================ --}}
            {{-- USER MANAGEMENT - Admin Only --}}
            {{-- ============================================ --}}
            @if(auth()->user()->role === 'admin')
                <li class="menu {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="#usersMenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('users.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shield">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span>User Management</span>
                        </div>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled {{ request()->routeIs('users.*') ? 'show' : '' }}" id="usersMenu" data-bs-parent="#accordionExample">
                        <li class="{{ request()->routeIs('users.index') ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}">View All Users</a>
                        </li>
                        <li class="{{ request()->routeIs('users.create') ? 'active' : '' }}">
                            <a href="{{ route('users.create') }}">Add New User</a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- ============================================ --}}
            {{-- SETTINGS - All Users --}}
            {{-- ============================================ --}}
            <li class="menu {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <a href="#settingsMenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('profile.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span>Settings</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('profile.*') ? 'show' : '' }}" id="settingsMenu" data-bs-parent="#accordionExample">
                    <li class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <a href="{{ route('profile.edit') }}">My Profile</a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                            @csrf
                            <a href="#" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                                Logout
                            </a>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</div>
