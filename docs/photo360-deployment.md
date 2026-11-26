# 360° photography: server stitching deployment

Ografi now uses external Hugin/Panorama Tools and Enblend for full spherical still photos. The old browser-only stitcher is not loaded. Normal photo and 280° viewer behavior remains unchanged.

## Ubuntu/Debian deployment
Install packages on the actual application host (root):
```sh
apt-get update
apt-get install -y hugin-tools enblend python3-pil
cd /www/wwwroot/ografi.com
git pull origin main
php artisan migrate --force
php artisan optimize:clear
```
Run ONE dedicated worker under the existing PHP/web user using Supervisor or systemd. Example for a foreground smoke test (use the same PHP binary and user as the site):
```sh
php artisan queue:work photo360 --queue=photo360 --timeout=720 --tries=1 --sleep=2 --memory=256
```
The database queue connection has retry_after=900. Keep this separate from notification/default workers. Restart the worker after future changes. Ensure the existing scheduler runs and storage/app/private is writable. The server needs enough RAM and temporary disk for 38 TIFF layers; start with one worker and monitor resource use. PHP upload limit must be at least 3 MiB per frame, and normal gallery limit remains 5 MiB. Confirm the actual PHP-FPM upload/post limits. Check that /usr/bin/python3 imports PIL and all Hugin tools exist. No browser or client raw-image stitch fallback is used.

## Quality / privacy
- Phone pose is an initial estimate only; Hugin matches image features, prunes bad points, optimizes geometry and exposure. Nona remaps; Enblend selects seams and blends with horizontal wrapping.
- Reject disconnected control-point graphs, high mean error, incomplete alpha coverage, invalid size, or too-large output. Output target 4096x2048 JPEG.
- Camera sends individual JPEGs only after all 38 targets have been captured. Authenticated, verified user ownership, CSRF, rate limits, per-user active-job limit, private temporary storage and bounded single-worker processing. Raw input and intermediates are removed after processing. Scheduler prunes old jobs after 24h. User confirms preview before adding it to gallery.
- Test in well-lit, textured room first; rotate around camera optical centre without translating. Featureless walls, moving objects, parallax and exposure clipping can still prevent stitching. Already published merged images cannot be reprocessed without raw frames.

## References
- Hugin CLI workflow: https://hugin.sourceforge.io/docs/manual/Panorama_scripting_in_a_nutshell.html
- Hugin control points: https://hugin.sourceforge.io/docs/manual/Cpfind.html
- Hugin lens optimisation: https://hugin.sourceforge.io/docs/manual/Hugin_Photos_tab.html
- Enblend: https://manpages.debian.org/unstable/enblend/enblend.1.en.html
- Brown & Lowe (2007): https://doi.org/10.1007/s11263-006-0002-3
External tools remain separately installed under their own licenses. No Hugin or Enblend source copied into Ografi.

## Engine checks
```sh
/usr/bin/python3 -m py_compile scripts/photo360_hugin.py
/usr/bin/python3 scripts/test_photo360_hugin.py
node --check public/assets/js/photo360-capture.js
php artisan test --filter='Photo360AccessTest|PostPanoramaTest'
```
The Python unit tests exercise control-point graph rejection and output coverage. These do not replace an end-to-end Hugin test on real overlapping camera frames. The camera checks the installed binaries before the user starts capturing.

## Verify the running installation before another capture
The camera header must display **360° Fotoğraf · Hugin**. The authenticated GET /composer/photo360/health must return engine=hugin-enblend-v2. If the header is missing or the version differs, the deployed code or cached asset is old. Use the PHP binary actually configured on the host; check the dedicated worker and do not bypass failing deployment tests.

Run the actual engine smoke test separately from the main application deployment:
```sh
apt-get install -y python3-numpy
/usr/bin/python3 scripts/smoke_photo360_hugin.py
```
The smoke test uses a reproducible synthetic textured sphere sampled into overlapping camera frames, not real phone photographs. A passing synthetic test establishes basic CLI interoperability and spherical coverage, not real-world parallax tolerance.

Upstream references: https://hugin.sourceforge.io/docs/manual/Cpfind.html (matching and options); https://hugin.sourceforge.io/docs/manual/Hugin_Photos_tab.html (lens fitting); https://wiki.panotools.org/Checkpto (image connectivity); https://hugin.sourceforge.io/docs/manual/Parallax.html (rotation around the entrance pupil).

## Fast matching (default)
The first CPFind pass uses native half-resolution feature detection, prealigned overlap search, 25 features per spatial bucket (instead of the default 100), 100 KD-tree search steps and 300 RANSAC iterations. The fast search is bounded to 90 seconds. If it fails or does not meet the existing geometric quality gates, the original three matching strategies remain available, within the existing overall processing deadline. Output remains 4096x2048, with original JPEG inputs and the same coverage/error gates and Enblend pipeline. Difficult scenes may be slower than before because of the extra attempt; no fixed duration is promised.
CPU threads are limited to available affinity and at most four, with optional PHOTO360_THREADS=2 override in the service environment.
Compare on identical synthetic frames, preferably when the production queue is idle:
```sh
/usr/bin/python3 scripts/smoke_photo360_hugin.py --compare --long-edge 1920
```
The report includes matching strategy, per-stage durations and geometric errors for both runs. 1–3 minutes is a performance target, not a timeout or verified production SLA. Set PHOTO360_FAST_MATCH=0 in the worker environment to disable the additional fast pass without reverting source code.

## Phone-resolution matching and timing
For source frames larger than 960 pixels on the long edge, the fast CPFind pass works on 960-pixel copies. Only control-point coordinates are transferred back to the original full-size project, with image ordering checked. All optimization, output rendering, coverage gates and fallback strategies operate on original input resolution. The default CI now tests both 960 and 1920 pixel input frames. Successful production jobs write a `photo360 performance` entry to the Laravel log with actual stage durations and matching strategy. No image contents, credentials or frame paths are logged. Proxy matching is disabled together with the fast pass using PHOTO360_FAST_MATCH=0.

## Quick capture (default)
24 images select quick mode automatically; existing 38–46-image captures retain quality mode. The camera has a mode toggle before capture. The 24-image pattern uses 10 horizon, 6 upper, 6 lower and 2 pole images (nominal complete coverage at the assumed 56-degree short-edge field of view; actual lens/pose may leave holes). Quick mode uses 640px feature copies, one prealigned full-copy search (65s max), a 180s external-processing budget, 3072x1536 output and nearest-feature-transform Enblend seams. It skips extra lens fitting only if cleaned geometry is already within tighter thresholds. The original final geometry/connectivity and full coverage gates remain. No fallback to long-running quality mode; failed captures can be retried or recaptured in quality mode. Upload and queue waiting are not included in the engine budget, and the time is not a guaranteed successful result. Quick mode may show more seam artifacts than quality mode. Tests include portrait and landscape phone-sized synthetic frames.
