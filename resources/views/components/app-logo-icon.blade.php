<svg {{ $attributes }} viewBox="5 50 220 160" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
    aria-labelledby="Logo Fit Track">
    <title id="title">{{env('APP_NAME','LNQ-Core')}} icon</title>
    <desc id="desc">Blue circle with white checkmark and motion lines to the left.</desc>

    <!-- Motion lines -->
    <path d="M44 96 H84" stroke="currentColor" stroke-width="18" stroke-linecap="round" />
    <path d="M28 128 H84" stroke="currentColor" stroke-width="18" stroke-linecap="round" />
    <path d="M16 160 H84" stroke="currentColor" stroke-width="18" stroke-linecap="round" />

    <!-- Circle -->
    <circle cx="144" cy="128" r="76" fill="currentColor" />

    <!-- Check -->
    <path d="M105 128 L135 158 L183 100" stroke="white" stroke-width="18" fill="none" stroke-linecap="round"
        stroke-linejoin="round" />
</svg>
