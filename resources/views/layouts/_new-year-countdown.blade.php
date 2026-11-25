<section class="ografi-ny-card" aria-label="Yeni yıl geri sayımı" data-ny-card data-target="{{ \App\Support\NewYearMode::target($newYearSettings)->getTimestamp() * 1000 }}">
    <svg class="ografi-ny-scene" viewBox="0 0 700 260" preserveAspectRatio="xMidYMax slice" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="ny-snowbank" x2="0" y2="1"><stop stop-color="#b9e7ff"/><stop offset="1" stop-color="#f2faff"/></linearGradient>
            <g id="ny-tree"><path d="M0 0  -22 40 -11 38 -33 66 -19 62 -45 95 45 95 19 62 33 66 11 38 22 40Z" fill="#124765"/><path d="M0 0 -22 40 0 33 22 40ZM-11 38 -33 66 0 57 33 66ZM-19 62 -45 95 0 84 45 95Z" fill="#d5f1ff"/><path d="M-5 94h10v18H-5z" fill="#79513d"/></g>
        </defs>
        <g fill="#dcefff" opacity=".85">
            @foreach ([12,43,72,108,155,190,229,270,306,350,391,432,470,505,542,579,618,655,689] as $i => $x)
                <circle cx="{{ $x }}" cy="{{ 9 + ($i * 37 % 138) }}" r="{{ 1 + $i % 2 }}"/>
            @endforeach
        </g>
        <circle cx="610" cy="48" r="21" fill="#e4f5ff" opacity=".85"/>
        <path d="M0 198 Q100 153 195 207 T398 208 T700 182 V260H0Z" fill="#82bedf"/>
        <g><use href="#ny-tree" transform="translate(28 105) scale(.95)"/><use href="#ny-tree" transform="translate(102 130) scale(.66)"/><use href="#ny-tree" transform="translate(665 91) scale(1.1)"/><use href="#ny-tree" transform="translate(590 130) scale(.7)"/></g>
        <path d="M0 235 Q100 208 220 239 T440 234 T700 220 V260H0Z" fill="url(#ny-snowbank)"/>
        <g transform="translate(63 195)">
            <rect x="-25" y="-25" width="50" height="37" rx="2" fill="#835136"/><path d="M-32 -23 0 -53 32 -23Z" fill="#f4faff"/><path d="M-28 -21 0 -45 28 -21Z" fill="#614131"/><rect x="-17" y="-14" width="12" height="12" rx="1" fill="#ffce72"/><rect x="7" y="-14" width="12" height="12" rx="1" fill="#ffce72"/><rect x="-4" y="-4" width="10" height="16" fill="#493027"/>
        </g>
        <g transform="translate(630 217)">
            <circle r="25" fill="#eefaff"/><circle cy="-35" r="19" fill="#fff"/><path d="M-19 -45Q-20 -67 0 -66Q19 -65 20 -45Z" fill="#d64050"/><rect x="-22" y="-47" width="44" height="7" rx="3" fill="#eaf5ff"/><circle cy="-67" r="6" fill="#fff"/><circle cx="-6" cy="-37" r="2" fill="#26374c"/><circle cx="6" cy="-37" r="2" fill="#26374c"/><path d="M0 -32 15 -29 0 -26Z" fill="#f58c39"/><path d="M-17 -19Q0 -12 17 -19L15 -12Q0 -5 -16 -12Z" fill="#d64050"/><path d="M9 -15h8v24H9Z" fill="#d64050"/><circle cy="0" r="2" fill="#26374c"/><circle cy="10" r="2" fill="#26374c"/>
        </g>
    </svg>
    <h2 class="ografi-ny-heading" data-ny-heading>Yeni yıla geri sayım</h2>
    <div class="ografi-ny-year">{{ (int) $newYearSettings['target_year'] }}</div>
    <div class="ografi-ny-clock" data-ny-clock role="timer">
        @foreach (['Gün', 'Saat', 'Dakika', 'Saniye'] as $label)
            <span><b data-ny-number>00</b><small>{{ $label }}</small></span>
        @endforeach
    </div>
</section>
