@php
    $logo = 'https://placehold.co/500x150?text=' . tenant()->name;
@endphp
<img src="{{ tenant()->config?->getFirstMediaUrl('logo') ?: $logo }}" alt="{{ tenant()->name ?? 'FTT' }} Logo"
    {{ $attributes->merge(['class' => "mx-auto max-h-64 max-w-64 mix-blend-multiply"])}}/>
