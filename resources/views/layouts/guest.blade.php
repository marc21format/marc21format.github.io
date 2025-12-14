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
        
        <style>
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.6);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                backdrop-filter: blur(4px);
            }
            
            .modal-content {
                background: white;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                max-width: 450px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
                animation: modalFadeIn 0.3s ease-out;
            }
            
            @keyframes modalFadeIn {
                from {
                    opacity: 0;
                    transform: scale(0.95) translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="modal-overlay">
            <div class="modal-content">
                <div class="px-8 py-8">
                    <div class="flex justify-center mb-6">
                        <a href="/" wire:navigate>
                            <x-application-logo class="w-16 h-16 fill-current text-gray-500" />
                        </a>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
