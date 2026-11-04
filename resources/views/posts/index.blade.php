<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="format-detection" content="telephone=no">
  <meta name="theme-color" content="#f7f9fd">
  <title>@yield('title', 'Repost — something interesting every day')</title>
  <meta name="description" content="@yield('meta_description', 'A feed of fresh stories, news and opinions.')">
  <link rel="icon" href="{{ asset('assets/img/meta/logo.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="{{ asset('assets/libs/fancyapp/fancybox.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/libs/fancyapp/carousel.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/kit.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/engine.css') }}">
  <script>
    (() => { const r=document.documentElement; let t; try{t=localStorage.getItem('theme')}catch{} if(t!=='light'&&t!=='dark')t=matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'; r.dataset.theme=t;r.dataset.bsTheme=t;r.style.colorScheme=t; })();
  </script>
</head>
<body>
  <div id="repost-icon-sprite" class="sprite" aria-hidden="true">
    <svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" fill="none" style="visibility: hidden; position: absolute;" aria-hidden="true">
      <symbol id="search" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="m20 20-3.35-3.35" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="pen" viewBox="0 0 24 24">
          <path d="M3 21l3.75-.75L20 7a2.121 2.121 0 1 0-3-3L3.75 17.25zM14 5l5 5M13 22h8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="message" viewBox="0 0 24 24">
          <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 8.4 8.4 0 0 1-3.6-.8L3 21l1.8-4.9A8.4 8.4 0 0 1 4 12.5 8.5 8.5 0 1 1 21 11.5Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="bookmark" viewBox="0 0 24 24">
          <path d="M8 3h8a3 3 0 0 1 3 3v12.5a2 2 0 0 1-3.32 1.5l-2.35-2.07a2 2 0 0 0-2.66 0L8.32 20A2 2 0 0 1 5 18.5V6a3 3 0 0 1 3-3ZM9 7h6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="setting" viewBox="0 0 24 24">
          <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.09a2 2 0 0 1 1 1.74v.5a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/>
      </symbol>
      <symbol id="adminpanel" viewBox="0 0 24 24">
          <path d="M7 2v4.5m0 6V22m10-20v8.5m0 7V22M10 9.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm10 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="logout" viewBox="0 0 24 24">
          <path d="M8.9 7.56C9.21 3.96 11.06 2.49 15.11 2.49h.13c4.47 0 6.26 1.79 6.26 6.26v6.52c0 4.47-1.79 6.26-6.26 6.26h-.13c-4.02 0-5.87-1.45-6.2-4.99M15 12H3.62m2.23-3.35L2.5 12l3.35 3.35" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="close" viewBox="0 0 24 24">
          <path d="M5 5l14 14M19 5 5 19" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="verify" viewBox="0 0 24 24">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M9.592 3.2c-.243.208-.365.312-.495.399a2.75 2.75 0 0 1-.985.408c-.154.03-.313.043-.632.068-.801.064-1.202.096-1.536.214a2.75 2.75 0 0 0-1.655 1.655c-.118.334-.15.735-.214 1.536-.025.319-.038.478-.068.632a2.75 2.75 0 0 1-.408.985c-.087.13-.191.252-.399.495-.521.612-.782.918-.935 1.238a2.75 2.75 0 0 0 0 2.34c.153.32.414.626.935 1.238.208.243.312.365.399.495.2.298.338.633.408.985.03.154.043.313.068.632.064.801.096 1.202.214 1.536a2.75 2.75 0 0 0 1.655 1.655c.334.118.735.15 1.536.214.319.025.478.038.632.068.352.07.687.208.985.408.13.087.252.191.495.399.612.521.918.782 1.238.935a2.75 2.75 0 0 0 2.34 0c.32-.153.626-.414 1.238-.935.243-.208.365-.312.495-.399.298-.2.633-.338.985-.408.154-.03.313-.043.632-.068.801-.064 1.202-.096 1.536-.214a2.75 2.75 0 0 0 1.655-1.655c.118-.334.15-.735.214-1.536.025-.319.038-.478.068-.632.07-.352.208-.687.408-.985.087-.13.191-.252.399-.495.521-.612.782-.918.935-1.238a2.75 2.75 0 0 0 0-2.34c-.153-.32-.414-.626-.935-1.238-.208-.243-.312-.365-.399-.495a2.75 2.75 0 0 1-.408-.985c-.03-.154-.043-.313-.068-.632-.064-.801-.096-1.202-.214-1.536a2.75 2.75 0 0 0-1.655-1.655c-.334-.118-.735-.15-1.536-.214-.319-.025-.478-.038-.632-.068a2.75 2.75 0 0 1-.985-.408c-.13-.087-.252-.191-.495-.399-.612-.521-.918-.782-1.238-.935a2.75 2.75 0 0 0-2.34 0c-.32.153-.626.414-1.238.935Zm6.782 6.663a.814.814 0 0 0-1.151-1.151l-4.851 4.85-1.595-1.594a.814.814 0 0 0-1.151 1.151l2.171 2.17a.814.814 0 0 0 1.151 0l5.426-5.426Z" fill="currentColor"/>
      </symbol>
      <symbol id="mic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
          <rect x="8" y="2" width="8" height="13" rx="4"/><path d="M20 11a8 8 0 0 1-16 0m8 8v3"/>
      </symbol>
      <symbol id="eye" viewBox="0 0 24 24">
          <g transform="scale(1.5)" fill="currentColor"><path d="M8 5.5A2.59 2.59 0 0 0 5.33 8A2.59 2.59 0 0 0 8 10.5A2.59 2.59 0 0 0 10.67 8A2.59 2.59 0 0 0 8 5.5m0 3.75A1.35 1.35 0 0 1 6.58 8A1.35 1.35 0 0 1 8 6.75A1.35 1.35 0 0 1 9.42 8A1.35 1.35 0 0 1 8 9.25"/><path d="M8 2.5A8.11 8.11 0 0 0 0 8a8.11 8.11 0 0 0 8 5.5A8.11 8.11 0 0 0 16 8a8.11 8.11 0 0 0-8-5.5m5.4 7.5A6.91 6.91 0 0 1 8 12.25A6.91 6.91 0 0 1 2.6 10a7.2 7.2 0 0 1-1.27-2A7.2 7.2 0 0 1 2.6 6A6.91 6.91 0 0 1 8 3.75A6.91 6.91 0 0 1 13.4 6a7.2 7.2 0 0 1 1.27 2a7.2 7.2 0 0 1-1.27 2"/></g>
      </symbol>
      <symbol id="user" viewBox="0 0 24 24">
          <path d="M12 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2c-4.418 0-8 2.239-8 5v1c0 2.761 3.582 4 8 4s8-1.239 8-4v-1c0-2.761-3.582-5-8-5Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="comment" viewBox="0 0 24 24">
          <path d="M7 3h10a4 4 0 0 1 4 4v6a5 5 0 0 1-5 5h-1.5L12 21l-2.5-3H8a5 5 0 0 1-5-5V7a4 4 0 0 1 4-4Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="tags" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
          <path d="M3 5.67h7.45c.35 0 .69.14.94.39l6.81 6.81a2.67 2.67 0 0 1 0 3.77l-4.23 4.23a2.67 2.67 0 0 1-3.77 0l-6.81-6.81a1.33 1.33 0 0 1-.39-.94V5.67ZM5 1.67h7.45c.35 0 .69.14.94.39l7.69 7.69"/><circle cx="7.67" cy="10.33" r="1.67" fill="currentColor" stroke="none"/>
      </symbol>
      <symbol id="check" viewBox="0 0 24 24">
          <path d="m3 12 6 6L21 6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="chevron-left" viewBox="0 0 24 24">
          <path d="m15 20-8-8 8-8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="chevron-right" viewBox="0 0 24 24">
          <path d="m9 4 8 8-8 8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="chevron-down" viewBox="0 0 24 24">
          <path d="m4 9 8 8 8-8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="chevron-up" viewBox="0 0 24 24">
          <path d="m4 15 8-8 8 8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
      </symbol>
      <symbol id="ai" viewBox="0 0 24 24">
          <path d="M7.336 3.383c.595-1.51 2.733-1.51 3.328 0l1.072 2.718c.03.074.088.133.163.162l2.718 1.072c1.51.596 1.51 2.733 0 3.329l-2.718 1.072a.25.25 0 0 0-.163.163l-1.072 2.718c-.595 1.51-2.733 1.51-3.328 0l-1.072-2.718a.25.25 0 0 0-.163-.163l-2.718-1.072c-1.51-.596-1.51-2.733 0-3.329L6.1 6.264a.25.25 0 0 0 .163-.163l1.072-2.718Zm1.933.55a.185.185 0 0 0-.345 0L7.86 6.651a1.75 1.75 0 0 1-1.008 1.008L4.132 8.73a.185.185 0 0 0 0 .346l2.719 1.072a1.75 1.75 0 0 1 1.008 1.008l1.072 2.718a.185.185 0 0 0 .345 0l1.072-2.718a1.75 1.75 0 0 1 1.008-1.008l2.718-1.072a.185.185 0 0 0 0-.346l-2.718-1.072a1.75 1.75 0 0 1-1.008-1.008L9.269 3.933Zm6.522 9.14c.432-1.098 1.985-1.098 2.418 0l.768 1.949 1.949.768c1.097.433 1.097 1.986 0 2.418l-1.949.768-.768 1.949c-.433 1.097-1.986 1.097-2.418 0l-.768-1.949-1.949-.768c-1.097-.432-1.097-1.985 0-2.418l1.949-.768.768-1.949ZM17 14.095l-.614 1.558a1.75 1.75 0 0 1-.733.883l-.825.464.825.464c.322.181.576.458.733.883L17 19.905l.614-1.558c.157-.425.411-.702.733-.883l.825-.464-.825-.464a1.75 1.75 0 0 1-.733-.883L17 14.095Z" fill="currentColor"/>
      </symbol>
      <symbol id="more" viewBox="0 0 24 24">
          <circle cx="5" cy="12" r="2" fill="currentColor"/><circle cx="12" cy="12" r="2" fill="currentColor"/><circle cx="19" cy="12" r="2" fill="currentColor"/>
      </symbol>
      <symbol id="image" viewBox="0 0 24 24">
          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 18V6.2c0-1.12 0-1.68.218-2.108c.192-.377.497-.682.874-.874C4.52 3 5.08 3 6.2 3h11.6c1.12 0 1.68 0 2.108.218a2 2 0 0 1 .874.874c.218.427.218.987.218 2.
