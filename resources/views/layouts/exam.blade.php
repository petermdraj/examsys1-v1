<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Exam') · {{ config('app.name') }}</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
</head>
<body style="background:var(--exam-bg);margin:0;overflow-x:hidden;">
{{ $slot }}
@livewireScripts
</body>
</html>
