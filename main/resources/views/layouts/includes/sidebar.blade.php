    @if(Auth::user()->user_type != 2)
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon">
          <img src="{{ asset('theme/img/logoDIS.jpg') }}">
        </div>
        <div class="sidebar-brand-text mx-3">{{ env("APP_NAME") }}</div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Nav Item - Dashboard -->
      <li class="nav-item active">
        <a class="nav-link" href="{{ route('dashboard') }}">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span></a>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider">
        
        @if(Auth::user()->user_type == 1)  <!-- Admin -->
          @include('layouts.includes.sidebar_admin')  
        @elseif(Auth::user()->user_type == 3)  <!-- Distributor -->
          @include('layouts.includes.sidebar_distributor')  
        @elseif(Auth::user()->user_type == 4)  <!-- Super Distributor -->
          @include('layouts.includes.sidebar_super_distributor')  
        @endif
        
      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>

    </ul>
    @endif