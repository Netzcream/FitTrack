@if (tenant()->config?->getFirstMediaUrl('favicon'))
    <link rel="icon" type="image/png" href="{{ tenant()->config?->getFirstMediaUrl('favicon') }}">
@else
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E
%3Cdefs%3E
%3ClinearGradient id='g' x1='0%25' y1='0%25' x2='100%25' y2='0%25'%3E
%3Cstop offset='0%25' stop-color='%23fdf6e3' /%3E
%3Cstop offset='50%25' stop-color='%233bc9f5' /%3E
%3Cstop offset='100%25' stop-color='%238141e8' /%3E
%3C/linearGradient%3E
%3C/defs%3E
%3Ccircle cx='50' cy='50' r='45' fill='url(%23g)' /%3E
%3C/svg%3E">
@endif
