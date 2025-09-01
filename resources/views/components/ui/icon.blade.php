@props([
    'name',
    'style' => 'o', // o = outline, s = solid
    'class' => 'w-4 h-4',
])

@php
    $component = 'heroicon-' . $style . '-' . $name;
@endphp

<x-dynamic-component :component="$component" :class="$class" />
