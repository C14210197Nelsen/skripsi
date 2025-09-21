<ul class="navbar-nav">
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
    <a class="nav-link {{ Str::contains($title, 'Return') ? 'nav-active' : '' }}" href="/return">
      Return
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ ($title === "Customer") ? 'active text-danger' : ''}}" href="/customer">
      Customer
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ Str::contains($title, 'Inventory') || Str::contains($title, 'Product') ? 'nav-active' : '' }}" href="/inventory">
      Inventory
    </a>
  </li>
</ul>