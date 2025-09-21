<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link <?php echo e($title === 'Dashboard' ? 'nav-active' : ''); ?>" href="/home">
      Home
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo e(Str::contains($title, 'Sales') ? 'nav-active' : ''); ?>" href="/sales">
      Sales
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo e(Str::contains($title, 'Return') ? 'nav-active' : ''); ?>" href="/return">
      Return
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo e(($title === "Customer") ? 'active text-danger' : ''); ?>" href="/customer">
      Customer
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo e(Str::contains($title, 'Inventory') || Str::contains($title, 'Product') ? 'nav-active' : ''); ?>" href="/inventory">
      Inventory
    </a>
  </li>
</ul><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/layouts/navbar-Sales.blade.php ENDPATH**/ ?>