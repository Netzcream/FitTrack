<x-layouts.tenant.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
<x-toast />
</x-layouts.tenant.sidebar>
