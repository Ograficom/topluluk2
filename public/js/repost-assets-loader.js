(() => {
  const root = '/repost-bundle/css/';
  const count = 6;

  const decodeGzip = async (encoded) => {
    const binary = atob(encoded);
    const bytes = Uint8Array.from(binary, (character) => character.charCodeAt(0));
    const stream = new Blob([bytes]).stream().pipeThrough(new DecompressionStream('gzip'));
    return new Response(stream).text();
  };

  const load = async () => {
    const parts = await Promise.all(Array.from({ length: count }, async (_, i) => {
      const url = root + String(i + 1).padStart(2, '0') + '.b64';
      const response = await fetch(url, { cache: 'force-cache' });
      if (!response.ok) throw new Error('Repost stylesheet part unavailable: ' + url);
      return (await response.text()).trim();
    }));

    const joined = parts.join('');
    let css;

    try {
      css = await decodeGzip(joined);
    } catch (error) {
      // Recover a single transfer-boundary typo in the first text chunk.
      // This path is only used when the complete payload has an invalid
      // Base64 length; normal requests decode once.
      if (joined.length % 4 !== 1) throw error;
      const tail = parts.slice(1).join('');
      for (let offset = 0; offset < parts[0].length; offset += 1) {
        try {
          css = await decodeGzip(parts[0].slice(0, offset) + parts[0].slice(offset + 1) + tail);
          if (css.length > 10000) break;
          css = undefined;
        } catch (_) {
          // Continue until the gzip stream validates.
        }
      }
      if (!css) throw new Error('Original Repost CSS bundle could not be repaired.');
    }

    const style = document.createElement('style');
    style.id = 'repost-original-styles';
    style.textContent = css;
    const compatibility = [...document.querySelectorAll('link[rel="stylesheet"]')]
      .find((link) => link.href.includes('/css/repost-full.css'));
    if (compatibility?.parentNode) compatibility.parentNode.insertBefore(style, compatibility);
    else document.head.append(style);
    document.documentElement.dataset.theme = document.documentElement.dataset.theme || 'light';
  };

  if ('DecompressionStream' in window) {
    load().catch((error) => console.error('[Repost theme]', error));
  } else {
    console.error('[Repost theme] Gzip decompression is not supported in this browser.');
  }
})();