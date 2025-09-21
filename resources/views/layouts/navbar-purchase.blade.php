<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link {{ $title === 'Dashboard' ? 'nav-active' : '' }}" href="/home">
      Home
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
  <li class="nav-item">
    <a class="nav-link {{ ($title === "Supplier") ? 'active text-danger' : ''}}" href="/supplier">
      Supplier
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ Str::contains($title, 'Inventory') || Str::contains($title, 'Product') ? 'nav-active' : '' }}" href="/inventory">
      Inventory
    </a>
  </li>
</ul>