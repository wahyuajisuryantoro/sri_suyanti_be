<aside class="left-sidebar with-vertical">
    <div>
        <div class="brand-logo d-flex align-items-center">
            <a href="{{ route('dashboard') }}" class="text-nowrap logo-img">
                <img src="{{asset('assets/images/logos/logo.png')}}" alt="Logo" width="100" height="auto" />
            </a>
        </div>
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
            <ul class="sidebar-menu" id="sidebarnav">
              
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
                    <span class="hide-menu">Main Menu</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route("dashboard") }}" id="get-url" aria-expanded="false">
                        <iconify-icon icon="solar:widget-add-line-duotone" class=""></iconify-icon>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route("dashboard") }}" id="get-url" aria-expanded="false">
                        <iconify-icon icon="solar:widget-add-line-duotone" class=""></iconify-icon>
                        <span class="hide-menu">Manajemen Karyawan</span>
                    </a>
                </li>
               
                {{-- <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="#" aria-expanded="false">
                        <iconify-icon icon="solar:calendar-mark-line-duotone"></iconify-icon>
                        <span class="hide-menu">Attendance</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('attendance.report.index') }}">
                                <span class="icon-small"></span>
                                <span class="hide-menu">Report Attendance</span>
                            </a>
                        </li>
                    </ul>
                </li> --}}
               
                <li>
                    <span class="sidebar-divider lg"></span>
                </li>
                <li class="sidebar-item">
                    <button class="sidebar-link w-100 text-start" 
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            aria-label="Logout"
                            style="background: none; border: none; cursor: pointer;">
                        <span class="d-flex align-items-center">
                            <iconify-icon icon="solar:logout-3-line-duotone" class="me-2"></iconify-icon>
                            <span class="hide-menu">Logout</span>
                        </span>
                    </button>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>   
        </nav>
    </div>
</aside>
