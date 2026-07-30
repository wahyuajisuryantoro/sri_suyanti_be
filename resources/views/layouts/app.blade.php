<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />

    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}" />
    @yield('css')
    <title>{{ $title }}</title>
</head>

<body class="link-sidebar">
    <div class="preloader">
        <img src="{{ asset('assets/images/logos/favicon.png') }}" alt="loader" class="lds-ripple img-fluid" />
    </div>
    <div id="main-wrapper">
        @include('partials.sidebar')
        <div class="page-wrapper">
            @include('partials.header')
            <div class="body-wrapper">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <div class="dark-transparent sidebartoggler"></div>
    @include('partials.searchbar')

    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme/app.init.js') }}"></script>
    <script src="{{ asset('assets/js/theme/theme.js') }}"></script>
    <script src="{{ asset('assets/js/theme/app.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme/sidebarmenu-default.js') }}"></script>
    @yield('js')
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="{{ asset('assets/libs/fullcalendar/index.global.min.js') }}"></script>

    {{-- Search Bar Function --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var searchInput = document.getElementById('search');
            searchInput.addEventListener('input', function() {
                var query = searchInput.value.toLowerCase();
                var items = document.querySelectorAll('.search-item');

                items.forEach(function(item) {
                    var text = item.innerText.toLowerCase();
                    if (text.includes(query)) {
                        item.style.display = ''; 
                    } else {
                        item.style.display = 'none'; 
                    }
                });
            });
        });
    </script>
    
</body>

</html>
