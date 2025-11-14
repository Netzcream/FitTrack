<div class="flex aspect-square size-8 items-center justify-center ">
    <img src="{{ tenant()->config?->getFirstMediaUrl('favicon') }}" class="size-6" />

</div>
<div class="ms-1 grid flex-1 text-start text-sm">
    <span class="mb-0.5 truncate leading-none font-semibold">{{ tenant()->name }}</span>
</div>
