<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title >Data Statistik Kota Lubuk Linggau</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
  <script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/11.4.8/highcharts.js"></script>
</head>
<body class="bg-[#F0F0F0] min-h-screen">
    <nav class="bg-blue-900 text-white  py-4 shadow">
        <h1 class="max-w-6xl mx-auto text-xl font-semibold">Data Statistik Kota Lubuk Linggau</h1>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>