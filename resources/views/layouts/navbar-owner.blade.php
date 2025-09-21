@php use Illuminate\Support\Str; @endphp
<ul class="navbar-nav d-flex align-items-center gap-2">

  <li class="nav-item">
    <a class="nav-link {{ $title === 'Dashboard' ? 'nav-active' : '' }}" href="/home">
      Home
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link {{ Str::contains($title, 'Sales') ? 'nav-active' : '' }}" href="/sales">
      Sales
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link {{ Str::contains($title, 'Purchase') ? 'nav-active' : '' }}" href="/purchase">
      Purchase
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link {{ Str::contains($title, 'Return') ? 'nav-active' : '' }}" href="/return">
      Return
    </a>
  </li>

  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ in_array($title, ['User', 'Customer', 'Supplier']) ? 'nav-active' : '' }}"
       href="#" data-bs-toggle="dropdown">
      Master Data
    </a>
    <ul class="dropdown-menu shadow-sm border-0">
      <li><a class="dropdown-item" href="/user">User</a></li>
      <li><a class="dropdown-item" href="/customer">Customer</a></li>
      <li><a class="dropdown-item" href="/supplier">Supplier</a></li>
    </ul>
  </li>

  <li class="nav-item">
    <a class="nav-link {{ Str::contains($title, 'Inventory') || Str::contains($title, 'Product') ? 'nav-active' : '' }}" href="/inventory">
      Inventory
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link {{ $title === 'Laporan Laba Rugi' ? 'nav-active' : '' }}" href="/laporan">
      Laporan
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link {{ $title === 'Rekapan' ? 'nav-active' : '' }}" href="/rekapan">
      Rekapan
    </a>
  </li>

</ul>
