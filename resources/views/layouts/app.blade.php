<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Custom styles for components -->
        <style>
            /* Range slider styling */
            input[type="range"] {
                -webkit-appearance: none;
                appearance: none;
                background: transparent;
                cursor: pointer;
            }

            input[type="range"]::-webkit-slider-track {
                background: #e5e7eb;
                height: 8px;
                border-radius: 4px;
            }

            input[type="range"]::-webkit-slider-thumb {
                -webkit-appearance: none;
                appearance: none;
                background: #3b82f6;
                height: 20px;
                width: 20px;
                border-radius: 50%;
                cursor: pointer;
                border: 2px solid #ffffff;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            input[type="range"]::-webkit-slider-thumb:hover {
                background: #2563eb;
            }

            input[type="range"]::-moz-range-track {
                background: #e5e7eb;
                height: 8px;
                border-radius: 4px;
                border: none;
            }

            input[type="range"]::-moz-range-thumb {
                background: #3b82f6;
                height: 20px;
                width: 20px;
                border-radius: 50%;
                cursor: pointer;
                border: 2px solid #ffffff;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            input[type="range"]::-moz-range-thumb:hover {
                background: #2563eb;
            }

            /* Dark mode support */
            .dark input[type="range"]::-webkit-slider-track {
                background: #374151;
            }

            .dark input[type="range"]::-moz-range-track {
                background: #374151;
            }

            /* Expandable section animations */
            .expandable-section .section-content {
                transition: max-height 0.3s ease-in-out;
            }

            .expandable-section .expand-icon {
                transition: transform 0.2s ease-in-out;
            }

            .expandable-section.expanded .expand-icon {
                transform: rotate(180deg);
            }

            /* Section header hover effect */
            .section-header:hover {
                background-color: rgba(0, 0, 0, 0.02);
            }

            .dark .section-header:hover {
                background-color: rgba(255, 255, 255, 0.02);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
