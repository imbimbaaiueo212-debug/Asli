{{-- resources/views/layouts/plain.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>@yield('title', 'Unit')</title>

  <link rel="icon" href="/template/img/test.png" type="image/png">
  <link href="/template/css/styles.css" rel="stylesheet" />
  <link href="/template/css/custom.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

  @stack('head')
</head>
<body>
  {{-- minimal container tanpa navbar/sidebar --}}
  <main>
    <div class="container-fluid px-4 mt-4">
      @yield('content')
    </div>
  </main>

  <footer class="mt-4 text-center text-muted small">
    &copy; INFINITE MANAGEMENT 2025
  </footer>

  <!-- vendor JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/template/js/scripts.js"></script>

  {{-- jQuery + select2 (if needed by page) --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  @stack('scripts')
</body>
</html>
