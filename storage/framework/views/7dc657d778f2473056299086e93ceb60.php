<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link <?php echo e($title === 'Dashboard' ? 'nav-active' : ''); ?>" href="/home">
      Home
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo e(Str::contains($title, 'Purchase') ? 'nav-active' : ''); ?>" href="/purchase">
      Purchase
    </a>
  </li>
   <li class="nav-item">
    <a class="nav-link <?php echo e(Str::contains($title, 'Return') ? 'nav-active' : ''); ?>" href="/return">
      Return
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo e(($title === "Supplier") ? 'active text-danger' : ''); ?>" href="/supplier">
      Supplier
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo e(Str::contains($title, 'Inventory') || Str::contains($title, 'Product') ? 'nav-active' : ''); ?>" href="/inventory">
      Inventory
    </a>
  </li>
</ul><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/layouts/navbar-purchase.blade.php ENDPATH**/ ?>