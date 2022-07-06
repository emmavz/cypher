<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="{{ route('admin.dashboard') }}" class="brand-link">
    <img src="{{ asset('admin_assets/dist/img/AdminLTELogo.png') }}"
         alt="AdminLTE Logo"
         class="brand-image img-circle elevation-3"
         style="opacity: .8">
    <span class="brand-text font-weight-light">{{ env('APP_NAME') }}</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="{{ asset('admin_assets/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="{{ route('admin.profile.edit') }}" class="d-block">{{ Auth::user()->name }}</a>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <!-- Dashboard -->
        <li class="nav-item {{ navActive('admin.dashboard') }}">
          <a href="{{ route('admin.dashboard') }}" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              {{ __('Dashboard') }}
            </p>
          </a>
        </li>

        {{-- <li class="nav-item has-treeview {{ treeActive('admin.categories') }}">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-edit"></i>
            <p>
              {{ __('Categories') }}
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item {{ navActive('admin.categories.index') }}">
              <a href="{{ route('admin.categories.index') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>{{ __('View') }}</p>
              </a>
            </li>
            <li class="nav-item {{ navActive('admin.categories.create') }}">
              <a href="{{ route('admin.categories.create') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>{{ __('Create') }}</p>
              </a>
            </li>
          </ul>
        </li>
        --}}
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>