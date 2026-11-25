<section class="ografi-ny-card" aria-label="Yeni yıl geri sayımı" data-ny-card data-target="{{ \App\Support\NewYearMode::target($newYearSettings)->getTimestamp() * 1000 }}">
    <svg class="ografi-ny-scene" viewBox="0 0 760 265" preserveAspectRatio="xMidYMax meet" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="ny-snowbank" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#c2e6fc"/><stop offset="1" stop-color="#f4fbff"/></linearGradient>
            <linearGradient id="ny-fir" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#40a888"/><stop offset="1" stop-color="#126052"/></linearGradient>
            <linearGradient id="ny-red" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#ff7180"/><stop offset="1" stop-color="#b91c3d"/></linearGradient>
            <g id="ny-tree">
                <rect x="-7" y="96" width="14" height="20" rx="3" fill="#80503e"/>
                <path d="M0 4-24 39h13l-25 27h17l-27 31Q0 110 46 97L19 66h17L11 39h13Z" fill="url(#ny-fir)"/>
                <path d="M0 5-23 39Q0 31 23 39ZM-11 39-35 66Q0 55 35 66ZM-19 66-44 97Q0 87 44 97Z" fill="#d9f6ff" opacity=".92"/>
                <path d="M-20 45Q4 61 23 47M-31 74Q2 94 31 74" fill="none" stroke="#f9d48a" stroke-width="2" stroke-linecap="round"/>
                <g fill="#ffc75f"><circle cx="-16" cy="51" r="4"/><circle cx="20" cy="80" r="4"/><circle cx="-23" cy="85" r="4"/></g>
                <g fill="#fb7185"><circle cx="10" cy="56" r="4"/><circle cx="-6" cy="83" r="4"/></g>
                <g fill="#8ed7ff"><circle cx="25" cy="63" r="3.5"/><circle cx="33" cy="93" r="3.5"/></g>
                <path d="m0-11 3 8 9 1-7 6 2 9-7-5-7 5 2-9-7-6 9-1Z" fill="#ffe598"/>
            </g>
            <g id="ny-gift">
                <rect x="-17" y="-1" width="34" height="27" rx="3" fill="currentColor"/>
                <rect x="-20" y="-5" width="40" height="8" rx="2" fill="currentColor"/>
                <path d="M-19 3H19" stroke="#452a43" stroke-opacity=".25" stroke-width="2"/>
                <path d="M0-5v31M-17 13h34" stroke="#ffe3a0" stroke-width="5"/>
                <path d="M0-5C-19-6-13-20-5-14L0-5C19-6 13-20 5-14Z" fill="none" stroke="#ffe3a0" stroke-width="3" stroke-linejoin="round"/>
            </g>
            <g id="ny-deer">
                <path d="M-24 1Q-37-10-39-1Q-36 7-24 8" fill="#e2b082"/>
                <path d="M-18 17-20 44h6l8-23M9 17 15 44h6l-3-29" fill="#a87047" stroke="#a87047" stroke-width="3" stroke-linejoin="round"/>
                <path d="M-20 44h7M15 44h7" stroke="#503425" stroke-width="5" stroke-linecap="round"/>
                <ellipse cx="-6" cy="9" rx="27" ry="18" fill="#c88c58"/>
                <ellipse cx="-6" cy="16" rx="18" ry="9" fill="#f4c99d"/>
                <path d="M11 11 16-12 31-11 23 17Z" fill="#c88c58"/>
                <ellipse cx="24" cy="-14" rx="15" ry="13" fill="#dca570"/>
                <ellipse cx="34" cy="-9" rx="10" ry="7" fill="#f2c69e"/>
                <circle cx="28" cy="-18" r="2.4" fill="#272336"/><circle cx="42" cy="-10" r="4" fill="#e9495c"/>
                <path d="M16-23Q3-35 8-21M31-24Q44-34 40-21" fill="#dca570" stroke="#dca570" stroke-width="4" stroke-linecap="round"/>
                <path d="M18-26v-15l-9-8M18-36l7-7M32-26l4-15 8-7M36-37l-7-7" fill="none" stroke="#f2d7b3" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 1 25 5" stroke="#e74c5f" stroke-width="5" stroke-linecap="round"/><circle cx="22" cy="8" r="4" fill="#facc66"/>
            </g>
        </defs>
        <g fill="#e7f5ff" opacity=".78">
            @foreach ([12,43,72,108,155,190,229,270,306,350,391,432,470,505,542,579,618,655,689,735] as $i => $x)
                <circle cx="{{ $x }}" cy="{{ 12 + ($i * 37 % 150) }}" r="{{ 1 + $i % 2 }}"/>
            @endforeach
        </g>
        <circle cx="675" cy="37" r="23" fill="#e5f6ff" opacity=".9"/>
        <path d="M0 189Q85 148 170 209T360 208T550 205T760 184V265H0Z" fill="#78acd2" opacity=".8"/>
        <path d="M0 229Q125 206 240 235T490 231T760 217V265H0Z" fill="url(#ny-snowbank)"/>
        <ellipse cx="100" cy="248" rx="97" ry="8" fill="#89b5cf" opacity=".45"/>
        <ellipse cx="662" cy="249" rx="94" ry="7" fill="#89b5cf" opacity=".45"/>
        <use href="#ny-tree" transform="translate(52 112) scale(1.1)"/>
        <use href="#ny-tree" transform="translate(700 91) scale(1.3)"/>
        <use href="#ny-tree" transform="translate(730 163) scale(.62)"/>
        <!-- Noel Baba -->
        <g transform="translate(128 190)">
            <path d="M17-13Q51-38 53 5L43 42H7Z" fill="#9f4351"/><path d="M39-20 29-29 51-27Z" fill="#f7d48b"/>
            <path d="M-20 39v18h17l7-18M9 39v18h17l-1-18" fill="#26384c"/>
            <rect x="-25" y="52" width="24" height="8" rx="4" fill="#26384c"/><rect x="7" y="52" width="24" height="8" rx="4" fill="#26384c"/>
            <path d="M-23-11Q0-27 24-11L34 32Q0 48-34 32Z" fill="url(#ny-red)"/>
            <path d="M-21-9-39 10Q-45 18-34 24L-18 10M22-8 38-24Q47-29 50-19L34 7" fill="none" stroke="#e34e61" stroke-width="13" stroke-linecap="round"/>
            <path d="M-39 15-33 20M40-22 45-18" stroke="#f8fcff" stroke-width="8" stroke-linecap="round"/>
            <path d="M-31 28Q0 39 31 28" stroke="#fff5ed" stroke-width="8" fill="none"/><path d="M-30 15Q0 24 30 15" stroke="#29384b" stroke-width="8" fill="none"/>
            <rect x="-6" y="13" width="13" height="12" rx="2" fill="#ffd37b"/><rect x="-2" y="16" width="5" height="6" rx="1" fill="#29384b"/>
            <circle cy="-25" r="20" fill="#f9c5a4"/>
            <path d="M-21-22Q-26-1 0 3Q25-1 21-22Q13-7 0-15Q-13-7-21-22Z" fill="#fff"/>
            <path d="M-17-22Q-10-29-2-22M2-22Q10-29 17-22" stroke="#fff" stroke-width="4" fill="none" stroke-linecap="round"/>
            <circle cx="-7" cy="-29" r="2" fill="#34354a"/><circle cx="7" cy="-29" r="2" fill="#34354a"/><circle cy="-22" r="4" fill="#e99282"/>
            <path d="M-21-38Q-16-68 7-57Q21-54 30-40L20-30 8-43-6-32Z" fill="url(#ny-red)"/>
            <rect x="-23" y="-41" width="44" height="9" rx="4.5" fill="#fff"/><circle cx="29" cy="-37" r="7" fill="#fff"/>
        </g>
        <use href="#ny-gift" transform="translate(39 230) scale(.85)" style="color:#db4966"/>
        <use href="#ny-gift" transform="translate(78 230) scale(.74)" style="color:#427cc4"/>
        <use href="#ny-gift" transform="translate(175 238) scale(.67)" style="color:#248f81"/>
        <!-- Geyikler ve hediyeler -->
        <use href="#ny-deer" transform="translate(600 210) scale(.75)"/>
        <use href="#ny-deer" transform="translate(656 192) scale(.85)"/>
        <use href="#ny-gift" transform="translate(703 234) scale(.84)" style="color:#db4966"/>
        <use href="#ny-gift" transform="translate(742 240) scale(.62)" style="color:#417ec8"/>
    </svg>
    <h2 class="ografi-ny-heading" data-ny-heading>Yeni yıla geri sayım</h2>
    <div class="ografi-ny-year">{{ (int) $newYearSettings['target_year'] }}</div>
    <div class="ografi-ny-clock" data-ny-clock role="timer">
        @foreach (['Gün', 'Saat', 'Dakika', 'Saniye'] as $label)
            <span><b data-ny-number>00</b><small>{{ $label }}</small></span>
        @endforeach
    </div>
</section>
