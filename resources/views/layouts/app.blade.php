<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | FreshDeal - Wholesale Vegetables</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-slate-800 antialiased selection:bg-emerald-500 selection:text-white" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex flex-col md:flex-row">
        <!-- Sidebar Navigation -->
        <x-sidebar />

        <!-- Mobile Backdrop -->
        <div
            x-show="sidebarOpen"
            @click="sidebarOpen = false"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 z-30 md:hidden"
            x-cloak
        ></div>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 md:pl-64">
            <!-- Topbar Header -->
            <x-topbar :title="$headerTitle ?? 'Dashboard'" :subtitle="$headerSubtitle ?? null" />

            <!-- Page Content -->
            <main class="flex-1 bg-slate-50">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Global Simulate WhatsApp Message Modal -->
    <x-simulate-message-modal />

    <!-- Global Toast Notifications -->
    <x-toast />
</body>
</html>
