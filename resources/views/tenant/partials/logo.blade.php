<div class="bg-gradient-to-b from-white to-gray-50 text-center py-16 px-4 shadow-inner">

    @php
        $logo = 'https://placehold.co/500x150?text=' . tenant()->name;
    @endphp
    <img src="{{ tenant()->config?->getFirstMediaUrl('logo') ?: $logo }}" alt="{{ tenant()->name ?? 'FTT' }} Logo"
        class="mx-auto max-h-64 max-w-64 mix-blend-multiply" />
    @if (!empty(tenant_config('landing_description')))
        <div class="text-md text-gray-800 mt-4">
            {!! tenant_config('landing_description') !!}
        </div>
    @endif
</div>
