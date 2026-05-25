

<ul class="menu-inner py-1">
  <!-- Dashboard -->
  <li class="menu-item {{ request()->routeIs('admin/dashboard') || request()->routeIs('superadmin/dashboard') ? 'active' : '' }}">
    <a href="{{ url('/') }}" class="menu-link">
      <i class="menu-icon tf-icons ri ri-home-4-line"></i>
      <div>Dashboard</div>
    </a>
  </li>

  <!-- Chat Interno -->
  <li class="menu-item {{ request()->routeIs('admin.chat-interno.*') ? 'active' : '' }}">
    <a href="{{ route('admin.chat-interno.index') }}" class="menu-link">
      <i class="menu-icon tf-icons ri ri-chat-1-line"></i>
      <div>Chat Interno</div>
    </a>
  </li>

  @include('components.partials.menu-sector-items', ['isHorizontal' => true])
</ul>