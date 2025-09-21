<!doctype html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    
    <style>
    /*Komponen Utama*/
        body {
            padding-top: 60px; /* kompensasi tinggi navbar */
            background-color: #b40000;
        }

        .container.mt-4 {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        h2, h5 {
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 0.4rem;
            margin-bottom: 1.2rem;
            color: #343a40;
        }


    /* Pagination */
        .circle-pagination .pagination {
            gap: 6px;
            justify-content: center;
        }

        .circle-pagination .page-link {
            border-radius: 50% !important;
            width: 36px;
            height: 36px;
            padding: 0;
            line-height: 36px;
            text-align: center;
            border: 1px solid #dee2e6;
            color: #dc3545;
            font-size: 0.85rem;
            transition: 0.2s;
        }

        .circle-pagination .page-link:hover {
            background-color: #dc3545;
            color: #fff;
            border-color: #dc3545;
        }

        .circle-pagination .active > .page-link {
            background-color: #dc3545 !important;
            color: #fff !important;
            border-color: #dc3545 !important;
            font-weight: bold;
        }


    /* Tabel Halaman Index */
        .table-responsive {
        border-radius: 0.5rem;
        overflow: hidden;
        }

        .table th, .table td {
            text-align: center;  
            vertical-align: middle;
            padding: 5px; 
            font-size: 0.9rem; 
        }


    /* Navbar */
        .navbar-nav .nav-link {
            font-weight: 500;
            font-size: 1rem;
            color: #495057;
            padding: 0.6rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .navbar-nav .nav-link:hover {
            background-color: #f2f2f2;
            color: #dc3545;
        }

        .navbar-nav .nav-active {
            color: #dc3545 !important;
            font-weight: 600;
        }

        .navbar-nav .dropdown-menu {
            border-radius: 0.5rem;
            padding: 0.3rem 0;
            font-size: 1rem;
        }

        .navbar-nav .dropdown-item {
            padding: 0.5rem 1.25rem;
            color: #495057;
            transition: background 0.2s;
        }

        .navbar-nav .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #dc3545;
        }


    /* Form Styling */
        /* Label Input Field */
        form .form-label {
            font-weight: 600;
            color: #333;
        }

        form input,
        form select {
            font-size: 0.9rem;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid #ced4da;
            transition: border 0.2s ease;
        }

        form input:focus,
        form select:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.1rem rgba(220, 53, 69, 0.2);
        }

        .alert {
            border-radius: 0.5rem;
        }

        /* Untuk input yang tidak bisa diganti */
        .readonly-input {
            background-color: #eeeeee !important; 
            color: #4b4b4b !important; 
        }


    /* Product */
        .product-item {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #b4b4b4;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* .product-item input {
            background-color: #fff;
        } */

        
    /* Button */
        .btn {
            transition: all 0.2s ease-in-out;
        }

        /* Button di Kolom Action dan "Deleted" */
        .btn-sm {
            padding: 0.5rem 0.5rem;
            font-size: 0.7rem;
            vertical-align: middle;
        }
        
        /* Button Save dan Cancel Transaksi */
        .btn-outline-danger, .btn-primary {
            border-radius: 0.5rem;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-danger:hover,
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        
        .btn-outline-danger:hover {
            background-color: #dc3545;
            color: #fff;
            border-color: #dc3545;
        }

        /* Tombol Discard di Form Master Data (Product, Customer, Supplier) */
        .btn-secondary {
            border-radius: 0.5rem;
            font-weight: 500;
            background-color: #e9ecef;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #dee2e6;
            color: #000;
        }


    
    /* Random */
        /* Style untuk Area Filter */
        .card {
            border-radius: 0.75rem;
            border: none;
        }

        /* Text Header di Detail Transaksi */
        .card h6 {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6c757d;
        }

        .card p {
            font-size: 1rem;
            margin: 0;
        }
    
        /* Pointer Detail Transaksi di Rekapan */
        .cursor-pointer {
            cursor: pointer;
        }
        
        .shadow-sm {
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        

    </style>

  </head>

  <body>
    
    <!-- LAYOUT NAVBAR -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top shadow-sm">
        <div class="container-fluid">
            <!-- Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        
            <form method="POST" action="{{ route('logout') }}" class="d-flex align-items-center ms-auto order-lg-1">
                @csrf
                <button type="submit" class="btn btn-link text-danger fw-bold text-decoration-none p-0 me-3"
                        style="font-size: 1.25rem;">
                    GwanGlobalDigital
                </button>
            </form>

        
            <!-- Isi Navbar -->
            <div class="collapse navbar-collapse order-lg-0" id="navbarNavDropdown">

                {{-- Fitur menampilkan Navbar berdasarkan Role --}}
                @if(Auth::user()->role === 'Owner')
                    @include('layouts.navbar-owner')
                @elseif(Auth::user()->role === 'Purchase')
                    @include('layouts.navbar-purchase')
                @elseif(Auth::user()->role === 'Sales')
                    @include('layouts.navbar-Sales')
                @endif

            </div>
        </div>
    </nav>



    <div class="container bg-light rounded mt-4 mb-4 p-4" style="max-width: 96%; min-height: calc(95vh - 100px);">
      @yield('container')
    </div>


       <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    @yield('scripts')


  </body>

</html>