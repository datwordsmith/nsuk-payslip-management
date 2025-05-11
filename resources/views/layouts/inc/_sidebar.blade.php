<aside class="left-sidebar bg-danger-subtle">
    <!-- Sidebar scroll-->
    <div>
      <div class="brand-logo d-flex align-items-center justify-content-between">
        <a href="{{ url('t/payslips')}}" class="text-nowrap logo-img">
            <img src="{{ asset('assets/payslip_dispatch_logo.png') }}" width="180" alt="" />
        </a>
        <div class="close-btn d-xl-none d-block sidebartoggler " id="sidebarCollapse">
            <i class="fas fa-bars" style="color: #ffffff;"></i>
        </div>
      </div>
      <!-- Sidebar navigation-->
      <nav class="sidebar-nav scroll-sidebar mt-5 pb-5" data-simplebar="">
        <ul id="sidebarnav">
          {{-- <li class="nav-small-cap">
            <i class="ti ti-layout-dashboard"></i>
            <span class="hide-menu">Home</span>
          </li>
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ url('t/dashboard')}}" aria-expanded="false">
              <span>
                <i class="ti ti-layout-dashboard"></i>
              </span>
              <span class="hide-menu">Dashboard</span>
            </a>
          </li>--}}
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ url('t/payslips')}}" aria-expanded="false">
              <span>
                <i class="fas fa-file-invoice"></i>
              </span>
              <span class="hide-menu">Payslips</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ url('t/staff')}}" aria-expanded="false">
              <span>
                <i class="fas fa-users"></i>
              </span>
              <span class="hide-menu">Staff</span>
            </a>
          </li>
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ url('t/dispatch_payslips')}}" aria-expanded="false">
              <span>
                <i class="fas fa-mail-bulk"></i>
              </span>
              <span class="hide-menu">Dispatch Payslips</span>
            </a>
          </li>


      </nav>
      <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
  </aside>
