@props([
    'as' => 'button', // por defecto 'button'
])

@php
    $baseClasses = '
        inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest
        transition ease-in-out duration-150
        border border-indigo-700 text-indigo-700 bg-transparent
        hover:bg-indigo-600 hover:text-white
        focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-50

        dark:border-indigo-400 dark:text-indigo-400
        dark:hover:bg-indigo-500 dark:hover:text-white
        dark:focus:ring-indigo-800 dark:focus:ring-offset-slate-900
    ';
@endphp

@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $baseClasses]) }}>
        {{ $slot }}
    </button>
@endif
