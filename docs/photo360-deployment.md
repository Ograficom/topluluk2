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
