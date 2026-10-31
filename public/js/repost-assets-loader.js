(() => {
  const root = '/repost-bundle/css/';
  const count = 6;
  const load = async () => {
    const parts = await Promise.all(Array.from({ length: count }, async (_, i) => {
      const url = root + String(i + 1).padStart(2, '0') + '.b64';
      const response = await fetch(url, { cache: 'force-cache' });
      if (!response.ok) throw new Error('Repost stylesheet part unavailable: ' + url);
      return (await response.text()).trim();
    }));
    const binary = atob(parts.join(''));
    const bytes = Uint8Array.from(binary, (character) => character.charCodeAt(0));
    if (!('DecompressionStream' in window)) throw new Error('This browser does not support gzip decompression.');
    const stream = new Blob([bytes]).stream().pipeThrough(new DecompressionStream('gzip'));
    const css = await new Response(stream).text();
    const style = document.createElement('style');
    style.id = 'repost-original-styles';
    style.textContent = css;
    const compatibility = [...document.querySelectorAll('link[rel="stylesheet"]')]
      .find((link) => link.href.includes('/css/repost-full.css'));
    if (compatibility?.parentNode) compatibility.parentNode.insertBefore(style, compatibility);
    else document.head.append(style);
    document.documentElement.dataset.theme = document.documentElement.dataset.theme || 'light';
  };
  load().catch((error) => console.error('[Repost theme]', error));
})();