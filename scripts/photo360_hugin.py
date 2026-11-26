#!/usr/bin/env python3
"""Ografi adapter around Hugin/Panorama Tools and Enblend (external programs).
No third-party source code is embedded. Input files are private server uploads.
"""
import json
import math
import os
import re
import signal
import subprocess
import sys
import time
from pathlib import Path

from PIL import Image, ImageChops

WIDTH, HEIGHT = 4096, 2048
DEADLINE = 620
Image.MAX_IMAGE_PIXELS = 30_000_000
child = None

def notify(message):
    print(json.dumps({"message": message}, ensure_ascii=False), flush=True)

def terminate(sig, frame):
    if child is not None and child.poll() is None:
        try: os.killpg(child.pid, signal.SIGKILL)
        except ProcessLookupError: pass
    raise SystemExit(130)

signal.signal(signal.SIGTERM, terminate)
signal.signal(signal.SIGINT, terminate)

def run(directory, began, binary, *args):
    global child
    remaining = DEADLINE - (time.monotonic() - began)
    if remaining <= 0:
        raise RuntimeError("İşleme zaman aşımına uğradı.")
    cmd = ["/usr/bin/" + binary, *map(str, args)]
    child = subprocess.Popen(cmd, cwd=directory, stdout=subprocess.PIPE,
                             stderr=subprocess.STDOUT, start_new_session=True,
                             env={**os.environ, "LC_ALL": "C", "OMP_NUM_THREADS": "2"})
    try:
        data, _ = child.communicate(timeout=remaining)
    except subprocess.TimeoutExpired:
        os.killpg(child.pid, signal.SIGKILL)
        child.communicate()
        raise RuntimeError("İşleme zaman aşımına uğradı.")
    output = data.decode("utf-8", errors="replace")
    (directory / "engine.log").open("a", encoding="utf-8").write(binary + "\n" + output[-12000:] + "\n")
    if child.returncode != 0:
        if binary == "checkpto":
            raise RuntimeError("Bazı kareler birbirine bağlanamadı. Daha fazla örtüşme ve ışıkla yeniden çekin.")
        raise RuntimeError("360° motorunda " + binary + " işlemi başarısız oldu.")
    child = None
    return output

def verify_graph(project, count):
    # Require multiple matching control points on every graph edge and a connected graph.
    edges = {}
    for line in project.read_text(encoding="utf-8", errors="replace").splitlines():
        if not line.startswith("c "): continue
        a, b, mode = (re.search(r"\b" + key + r"(\d+)\b", line) for key in ("n", "N", "t"))
        if not a or not b or (mode and mode.group(1) != "0"): continue
        i, j = sorted((int(a.group(1)), int(b.group(1))))
        if i != j and 0 <= i < j < count:
            edges[i, j] = edges.get((i, j), 0) + 1
    graph = [set() for _ in range(count)]
    for (i, j), n in edges.items():
        if n >= 4:
            graph[i].add(j)
            graph[j].add(i)
    seen, todo = {0}, [0]
    while todo:
        for j in graph[todo.pop()]:
            if j not in seen:
                seen.add(j)
                todo.append(j)
    if len(seen) != count or sum(edges.values()) < count * 5:
        raise RuntimeError("Ortak görüntü noktaları yetersiz. Daha aydınlık ortamda, kareleri örtüştürerek yeniden çekin.")
    return sum(edges.values())

def layer_coverage(layers):
    # Nona cropped TIFFs carry X/Y position in resolution units. Check the
    # union independently: Enblend may omit alpha if the output is opaque.
    union = Image.new("L", (WIDTH, HEIGHT), 0)
    for path in layers:
        with Image.open(path) as im:
            if "A" not in im.getbands():
                raise RuntimeError("Küresel katmanın kapsama maskesi eksik.")
            x = round(float(im.tag_v2.get(286, 0)) * float(im.tag_v2.get(282, 1)))
            y = round(float(im.tag_v2.get(287, 0)) * float(im.tag_v2.get(283, 1)))
            layer = Image.new("L", (WIDTH, HEIGHT), 0)
            layer.paste(im.getchannel("A"), (x, y))
            union = ImageChops.lighter(union, layer)
    return union

def alpha_coverage(path, layers=None):
    with Image.open(path) as im:
        if im.size != (WIDTH, HEIGHT):
            raise RuntimeError("Motor tam küresel 2:1 çıktı üretmedi.")
        alpha = im.getchannel("A") if "A" in im.getbands() else layer_coverage(layers or [])
        histogram = alpha.histogram()
        covered = sum(histogram[1:]) / (WIDTH * HEIGHT)
        if covered < 0.998:
            raise RuntimeError(f"Küre eksik: %{covered * 100:.1f} kapsama. Tavan ve zemini de çekin.")
        if histogram[0] > 0:
            raise RuntimeError("Küre üzerinde çekilmemiş alanlar var. Eksik yönleri yeniden çekin.")
        return im.convert("RGB")

def write_jpeg(image, path):
    # Keep full 4096x2048 where possible; match the existing 5 MiB gallery limit.
    for quality in (95, 92, 89, 86, 83, 80, 76):
        image.save(path, "JPEG", quality=quality, subsampling=0, optimize=True)
        if path.stat().st_size <= 5 * 1024 * 1024:
            return
    raise RuntimeError("Sonuç 5 MB galeri sınırını aşıyor.")

def main(directory):
    began = time.monotonic()
    sources = sorted(directory.glob("frame-*.jpg"))
    if not 24 <= len(sources) <= 48:
        raise RuntimeError("360° için yeterli çekim yok.")
    notify("Çekimler doğrulanıyor.")
    inputs, values, size = [], [], None
    for index, file in enumerate(sources):
        if file.name != "frame-%03d.jpg" % index:
            raise RuntimeError("Çekim dizisi eksik.")
        pose = json.loads(file.with_suffix(".json").read_text())
        with Image.open(file) as img:
            if img.format != "JPEG" or max(img.size) > 2560 or min(img.size) < 320:
                raise RuntimeError("Geçersiz kamera karesi.")
            if size is None: size = img.size
            if img.size != size: raise RuntimeError("Çekim sırasında kamera boyutu değişti.")
            # Re-encode to strip untrusted metadata and unify input format.
            clean = directory / ("input-%03d.jpg" % index)
            img.convert("RGB").save(clean, "JPEG", quality=97, subsampling=0)
        inputs.append(clean.name)
        for k, name, limit in (("y","yaw",180),("p","pitch",90),("r","roll",180)):
            value = float(pose[name])
            if not math.isfinite(value) or abs(value) > limit:
                raise RuntimeError("Geçersiz kamera yönü.")
            values.append("%s%d=%.6f" % (k,index,value))
    hfov = math.degrees(2*math.atan(size[0]/min(size)*math.tan(math.radians(28))))
    def cmd(binary,*args): return run(directory,began,binary,*args)
    cmd("pto_gen","-p","0","-f",round(hfov,3),"-o","base.pto",*inputs)
    cmd("pto_var","--set",",".join(values),"-o","pose.pto","base.pto")
    # Use the same spherical projection and scale for all optimization and
    # error measurements. A default narrow output is unsuitable for full spheres.
    cmd("pano_modify","-p","2","--fov=360x180","--canvas=4096x2048",
        "--crop=0,4096,0,2048","-o","seed.pto","pose.pto")
    notify("Hugin ortak görüntü noktalarını arıyor.")
    # Hugin 2024 introduced --allpairs; older releases already use all
    # pairs by default. Inspect the installed CLI rather than assume a version.
    help_text = subprocess.run(["/usr/bin/cpfind", "--help"], capture_output=True,
                               text=True, timeout=10).stdout
    # Some releases print usage on stderr.
    if not help_text or "--multirow" not in help_text:
        help_text = subprocess.run(["/usr/bin/cpfind", "--help"], stdout=subprocess.PIPE,
                                   stderr=subprocess.STDOUT, text=True, timeout=10).stdout
    all_pairs = ["--allpairs"] if "--allpairs" in help_text else []
    strategies = [
        ["--prealigned", "--ransacmode=rpy", "--ransacdist=10", "--minmatches=6"],
        ["--multirow", "--ransacmode=rpy", "--ransacdist=12", "--minmatches=6"],
        [*all_pairs, "--fullscale", "--ransacmode=rpyv", "--ransacdist=12", "--minmatches=6"],
    ]
    def stats_for(project):
        stats=cmd("checkpto",project)
        mean=re.search(r"Mean error\s*:\s*([0-9.]+)",stats)
        maximum=re.search(r"Maximum\s*:\s*([0-9.]+)",stats)
        return (float(mean.group(1)) if mean else math.inf,
                float(maximum.group(1)) if maximum else math.inf)
    best=None
    for attempt, options in enumerate(strategies):
        if attempt:
            notify("Alternatif geometrik eşleştirme yapılıyor.")
        prefix=f"fit-{attempt}"
        try:
            cmd("cpfind",*options,"--ncores=2","-o",prefix+"-matches.pto","seed.pto")
            cmd("cpclean","--pairwise-checking","-o",prefix+"-pairs.pto",prefix+"-matches.pto")
            verify_graph(directory/(prefix+"-pairs.pto"),len(inputs))
            # Preserve sensor pose as the initialization. Do not let the
            # pairwise auto-align stage replace the initial camera orientations.
            cmd("pto_var","--opt=y,p,r","-o",prefix+"-orient.pto",prefix+"-pairs.pto")
            cmd("autooptimiser","-n","-o",prefix+"-rotation.pto",prefix+"-orient.pto")
            cmd("cpclean","--whole-pano-checking","--skip-optimisation","-o",
                prefix+"-clean.pto",prefix+"-rotation.pto")
            verify_graph(directory/(prefix+"-clean.pto"),len(inputs))
            cmd("pto_var","--opt=y,p,r,v,b","-o",prefix+"-lens.pto",prefix+"-clean.pto")
            cmd("autooptimiser","-n","-o",prefix+"-refined.pto",prefix+"-lens.pto")
            for project in [prefix+"-rotation.pto",prefix+"-clean.pto",prefix+"-refined.pto"]:
                mean,maximum=stats_for(project)
                if mean<=3 and maximum<=15 and (best is None or mean<best[0]):
                    best=(mean,maximum,project)
            if best is not None:
                break
        except RuntimeError as error:
            notify("Eşleştirme aşaması: "+str(error))
    if best is None:
        raise RuntimeError("Geometrik eşleşme güvenilir değil. Hatalı panorama oluşturulmadı.")
    notify("Kamera açıları doğrulandı; pozlama dengeleniyor.")
    # Photometric optimization only; do not re-level or re-optimize geometry.
    cmd("autooptimiser","-m","-o","optimized.pto",best[2])
    cmd("pano_modify","-p","2","--fov=360x180","--canvas=4096x2048",
        "--crop=0,4096,0,2048","-o","sphere.pto","optimized.pto")
    error,maximum=stats_for("sphere.pto")
    if error>3 or maximum>15:
        raise RuntimeError("Son geometri kalite kontrolü başarısız.")
    (directory/"quality.json").write_text(json.dumps({"engine":"hugin-enblend",
        "mean_error_px":error,"maximum_error_px":maximum,
        "control_points":verify_graph(directory/"sphere.pto",len(inputs))}),encoding="utf-8")
    notify("Nona çekimleri küresel yüzeye yansıtıyor.")
    cmd("nona","-m","TIFF_m","-o","warp","sphere.pto")
    layers=sorted(directory.glob("warp*.tif"))
    if len(layers)!=len(inputs):
        raise RuntimeError("Bazı kareler küreye yansıtılamadı.")
    notify("Enblend birleşim sınırlarını ve geçişleri işliyor.")
    cmd("enblend","--wrap=horizontal","--primary-seam-generator=graph-cut","--compression=lzw","-f","4096x2048+0+0","-o","sphere.tif",*[f.name for f in layers])
    notify("Kapsama ve çıktı kalitesi kontrol ediliyor.")
    image=alpha_coverage(directory/"sphere.tif", layers)
    write_jpeg(image,directory/"result.jpg")

if __name__ == "__main__":
    folder=Path(sys.argv[1]).resolve()
    try:
        main(folder)
    except Exception as exc:
        (folder/"failure.txt").write_text(str(exc)[:250],encoding="utf-8")
        sys.exit(1)
