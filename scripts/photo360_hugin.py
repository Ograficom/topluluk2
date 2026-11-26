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
import tempfile
from collections import defaultdict
import sys
import time
from pathlib import Path

from PIL import Image, ImageChops

# Ubuntu 22.04 ships Pillow 9.0, before the Resampling enum.
def resampling_filter(name, image_module=Image):
    return getattr(getattr(image_module, "Resampling", image_module), name)


WIDTH, HEIGHT = 4096, 2048
DEADLINE = 620
HEARTBEAT = 5
FAST_MATCH_LIMIT = 90
MATCH_EDGE = 960
QUICK_COUNT = 24
QUICK_EDGE = 640
QUICK_WIDTH, QUICK_HEIGHT = 3072, 1536
QUICK_DEADLINE = 180
# One worker, bounded CPU use. A two-vCPU host still uses only two threads.
def cpu_budget():
    try:
        available = len(os.sched_getaffinity(0))
    except (AttributeError, OSError):
        available = os.cpu_count() or 2
    try:
        requested = int(os.environ.get("PHOTO360_THREADS", "4"))
    except ValueError:
        requested = 4
    return max(1, min(available, requested, 4))

THREADS = cpu_budget()
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

def duration_text(seconds):
    minutes, seconds = divmod(max(0, int(seconds)), 60)
    return f"{minutes:02d}:{seconds:02d}"

STAGES = {
    "pto_gen": "Çekim projesi hazırlanıyor",
    "pto_var": "Kamera ve lens parametreleri hazırlanıyor",
    "pano_modify": "Küresel görüntü alanı hazırlanıyor",
    "cpfind": "Ortak görüntü noktaları eşleştiriliyor",
    "cpclean": "Hatalı eşleşmeler temizleniyor",
    "autooptimiser": "Kamera açıları, lens veya pozlama optimize ediliyor",
    "checkpto": "Geometrik kalite kontrol ediliyor",
    "nona": "Kareler küresel yüzeye yansıtılıyor",
    "enblend": "Görüntü birleşim sınırları işleniyor",
}

def run(directory, began, binary, *args, stage=None, max_seconds=None):
    global child
    remaining = DEADLINE - (time.monotonic() - began)
    if remaining <= 0:
        raise RuntimeError("İşleme zaman aşımına uğradı.")
    cmd = ["/usr/bin/" + binary, *map(str, args)]
    label = stage or STAGES.get(binary, binary)
    started = time.monotonic()
    stop_at = min(began + DEADLINE, started + max_seconds) if max_seconds else began + DEADLINE
    notify(f"{label} · 00:00")
    # A temporary output file avoids full pipes and permits periodic heartbeats
    # even when a third-party binary emits no progress or buffers its output.
    # It is private and automatically removed, including on failure.
    with tempfile.TemporaryFile(dir=directory) as capture:
        child = subprocess.Popen(cmd, cwd=directory, stdout=capture,
                                 stderr=subprocess.STDOUT, start_new_session=True,
                                 env={**os.environ, "LC_ALL": "C", "OMP_NUM_THREADS": str(THREADS)})
        try:
            while True:
                remaining = stop_at - time.monotonic()
                if remaining <= 0:
                    raise subprocess.TimeoutExpired(cmd, DEADLINE)
                try:
                    child.wait(timeout=min(HEARTBEAT, remaining))
                    break
                except subprocess.TimeoutExpired:
                    if time.monotonic() >= stop_at:
                        raise
                    notify(f"{label} · {duration_text(time.monotonic()-started)}")
        except subprocess.TimeoutExpired:
            if child.poll() is None:
                try: os.killpg(child.pid, signal.SIGKILL)
                except ProcessLookupError: pass
            child.wait()
            raise RuntimeError(f"{label}: zaman sınırı aşıldı; alternatif yöntem denenebilir.")
        finally:
            returncode = child.poll()
            if returncode is None:
                try: os.killpg(child.pid, signal.SIGKILL)
                except ProcessLookupError: pass
                returncode = child.wait()
            child = None
        capture.seek(0)
        output = capture.read().decode("utf-8", errors="replace")
    with (directory / "engine.log").open("a", encoding="utf-8") as log:
        log.write(binary + " · " + duration_text(time.monotonic()-started) + "\n" + output[-12000:] + "\n")
    if returncode != 0:
        if binary == "checkpto":
            raise RuntimeError("Bazı kareler birbirine bağlanamadı. Daha fazla örtüşme ve ışıkla yeniden çekin.")
        raise RuntimeError("360° motorunda " + binary + " işlemi başarısız oldu.")
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
        raise RuntimeError(f"Ortak noktalar yetersiz: {len(seen)}/{count} kare bağlı, {sum(edges.values())} nokta.")
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

def project_image_names(project):
    names=[]
    for line in project.read_text(encoding="utf-8").splitlines():
        if line.startswith("i "):
            match=re.search(r'\bn"([^"]+)"',line)
            if not match:
                raise RuntimeError("Hugin kare sırası doğrulanamadı.")
            names.append(Path(match.group(1)).name)
    return names

def transfer_control_points(proxy_project, full_project, output, proxy_names, full_names, proxy_size, full_size):
    # CPFind returns coordinates in its input image space. Transform just the
    # control points back to original pixel centres, leaving full-size lens,
    # poses, and original image filenames untouched.
    if project_image_names(proxy_project)!=proxy_names or project_image_names(full_project)!=full_names:
        raise RuntimeError("Eşleştirme kare sırası değişti.")
    sx,sy=full_size[0]/proxy_size[0],full_size[1]/proxy_size[1]
    result=[line for line in full_project.read_text(encoding="utf-8").splitlines()
            if not line.startswith("c ")]
    for line in proxy_project.read_text(encoding="utf-8").splitlines():
        if not line.startswith("c "):
            continue
        fields={}
        for key in ("n","N","x","y","X","Y","t"):
            match=re.search(r"(?:^|\s)"+key+r"([+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+-]?\d+)?)(?=\s|$)",line)
            if not match:
                raise RuntimeError("Hugin eşleşme koordinatı okunamadı.")
            fields[key]=float(match.group(1))
        if fields["t"]!=0:
            continue
        n,N=int(fields["n"]),int(fields["N"])
        if not (0<=n<len(full_names) and 0<=N<len(full_names) and n!=N):
            raise RuntimeError("Geçersiz eşleşme karesi.")
        for key in ("x","y","X","Y"):
            scale=sx if key.lower()=="x" else sy
            fields[key]=(fields[key]+.5)*scale-.5
            if not math.isfinite(fields[key]):
                raise RuntimeError("Geçersiz eşleşme koordinatı.")
        result.append(f'c n{n} N{N} x{fields["x"]:.6f} y{fields["y"]:.6f} X{fields["X"]:.6f} Y{fields["Y"]:.6f} t0')
    output.write_text("\n".join(result)+"\n",encoding="utf-8")

def matching_strategies(all_pairs, fast=True, quick=False):
    if quick:
        # At 640px use the whole working copy for control-point precision.
        # A single bounded recovery at 960px may rescue sparse 640px matches.
        # Never launch the expensive original-resolution quality fallbacks.
        return [("fast", ["--prealigned", "--fullscale", "--ransacmode=rpy",
            "--ransacdist=10", "--minmatches=6", "--sieve1size=25",
            "--kdtreesteps=100", "--ransaciter=300"], 45),
            ("quick-recovery", ["--prealigned", "--fullscale", "--ransacmode=rpy",
            "--ransacdist=12", "--minmatches=6"], 35)]
    # The native CPFind half-resolution mode is retained. The fast pass
    # limits the feature budget per spatial bucket, not the output image.
    # Every accepted result goes through the same full quality gates.
    strategies = []
    if fast:
        strategies.append(("fast", ["--prealigned", "--ransacmode=rpy",
            "--ransacdist=10", "--minmatches=6", "--sieve1size=25",
            "--kdtreesteps=100", "--ransaciter=300"], FAST_MATCH_LIMIT))
    strategies.extend([
        ("standard", ["--prealigned", "--ransacmode=rpy", "--ransacdist=10", "--minmatches=6"], None),
        ("multirow", ["--multirow", "--ransacmode=rpy", "--ransacdist=12", "--minmatches=6"], None),
        ("full", [*all_pairs, "--fullscale", "--ransacmode=rpyv", "--ransacdist=12", "--minmatches=6"], None),
    ])
    return strategies

def main(directory):
    global WIDTH, HEIGHT, DEADLINE
    began = time.monotonic()
    sources = sorted(directory.glob("frame-*.jpg"))
    if not 24 <= len(sources) <= 48:
        raise RuntimeError("360° için yeterli çekim yok.")
    quick = len(sources)==QUICK_COUNT
    WIDTH, HEIGHT = (QUICK_WIDTH, QUICK_HEIGHT) if quick else (4096, 2048)
    DEADLINE = QUICK_DEADLINE if quick else 620
    match_edge = QUICK_EDGE if quick else MATCH_EDGE
    canvas_arg=f"--canvas={WIDTH}x{HEIGHT}"
    crop_arg=f"--crop=0,{WIDTH},0,{HEIGHT}"
    notify("Hızlı mod · 24 kare hazırlanıyor." if quick else "Kalite modu · Çekimler doğrulanıyor.")
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
    durations = defaultdict(float)
    def cmd(binary,*args,**kwargs):
        started=time.monotonic()
        try:
            return run(directory,began,binary,*args,**kwargs)
        finally:
            durations[binary] += time.monotonic()-started
    cmd("pto_gen","-p","0","-f",round(hfov,3),"-o","base.pto",*inputs)
    cmd("pto_var","--set",",".join(values),"-o","pose.pto","base.pto")
    # Use the same spherical projection and scale for all optimization and
    # error measurements. A default narrow output is unsuitable for full spheres.
    cmd("pano_modify","-p","2","--fov=360x180",canvas_arg,
        crop_arg,"-o","seed.pto","pose.pto")
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
    fast = quick or os.environ.get("PHOTO360_FAST_MATCH", "1") != "0"
    strategies = matching_strategies(all_pairs, fast=fast, quick=quick)
    proxy_names=[]
    proxy_size=None
    def prepare_proxy(edge, tag):
        names, dimensions=[],None
        notify(f"Eşleştirme kopyaları hazırlanıyor ({edge} px).")
        for index,name in enumerate(inputs):
            with Image.open(directory/name) as im:
                im.thumbnail((edge,edge),resampling_filter("LANCZOS"))
                if dimensions is None:
                    dimensions=im.size
                if im.size!=dimensions:
                    raise RuntimeError("Çalışma kopyası boyutları farklı.")
                proxy_name=f"{tag}-{index:03d}.jpg"
                im.save(directory/proxy_name,"JPEG",quality=95)
                names.append(proxy_name)
        cmd("pto_gen","-p","0","-f",round(hfov,3),"-o",tag+"-base.pto",*names)
        cmd("pto_var","--set",",".join(values),"-o",tag+"-pose.pto",tag+"-base.pto")
        cmd("pano_modify","-p","2","--fov=360x180",canvas_arg,
            crop_arg,"-o",tag+"-seed.pto",tag+"-pose.pto")
        return names,dimensions,tag+"-seed.pto"
    proxy_seed=None
    if fast and max(size)>match_edge:
        proxy_names,proxy_size,proxy_seed=prepare_proxy(match_edge,"match")
    labels = {
        "fast": "Hızlı görüntü eşleştirme",
        "quick-recovery": "Kurtarma eşleştirmesi (960 px)",
        "standard": "Ayrıntılı görüntü eşleştirme",
        "multirow": "Çok sıralı alternatif eşleştirme",
        "full": "Tam çözünürlükte alternatif eşleştirme",
    }
    def stats_for(project):
        stats=cmd("checkpto",project)
        mean=re.search(r"Mean error\s*:\s*([0-9.]+)",stats)
        maximum=re.search(r"Maximum\s*:\s*([0-9.]+)",stats)
        return (float(mean.group(1)) if mean else math.inf,
                float(maximum.group(1)) if maximum else math.inf)
    best=None
    selected_strategy = None
    failures=[]
    for attempt, (strategy, options, limit) in enumerate(strategies):
        if attempt:
            notify("Kalite kontrolü için alternatif eşleştirme yapılıyor.")
        prefix=f"fit-{attempt}"
        try:
            if strategy=="quick-recovery":
                if time.monotonic()-began>85:
                    failures.append("Kurtarma için kalan süre yetersiz.")
                    break
                proxy_names,proxy_size,proxy_seed=prepare_proxy(960,"rescue")
            if strategy in ("fast","quick-recovery") and proxy_size is not None:
                cmd("cpfind",*options,f"--ncores={THREADS}","-o",prefix+"-proxy.pto",proxy_seed,
                    stage=labels[strategy]+" (çalışma kopyaları)",max_seconds=limit)
                transfer_control_points(directory/(prefix+"-proxy.pto"),directory/"seed.pto",
                    directory/(prefix+"-matches.pto"),proxy_names,inputs,proxy_size,size)
            else:
                cmd("cpfind",*options,f"--ncores={THREADS}","-o",prefix+"-matches.pto","seed.pto",
                    stage=labels[strategy],max_seconds=limit)
            cmd("cpclean","--pairwise-checking","-o",prefix+"-pairs.pto",prefix+"-matches.pto")
            verify_graph(directory/(prefix+"-pairs.pto"),len(inputs))
            # Preserve sensor pose as the initialization. Do not let the
            # pairwise auto-align stage replace the initial camera orientations.
            cmd("pto_var","--opt=y,p,r","-o",prefix+"-orient.pto",prefix+"-pairs.pto")
            cmd("autooptimiser","-n","-o",prefix+"-rotation.pto",prefix+"-orient.pto")
            cmd("cpclean","--whole-pano-checking","--dont-optimize","-o",
                prefix+"-clean.pto",prefix+"-rotation.pto")
            verify_graph(directory/(prefix+"-clean.pto"),len(inputs))
            candidates=[prefix+"-rotation.pto",prefix+"-clean.pto"]
            clean_mean,clean_max=stats_for(prefix+"-clean.pto")
            # Skip an expensive extra lens fit only when cleaned geometry is
            # already comfortably inside the original acceptance limits.
            if not quick or clean_mean>1.5 or clean_max>8:
                cmd("pto_var","--opt=y,p,r,v,b","-o",prefix+"-lens.pto",prefix+"-clean.pto")
                cmd("autooptimiser","-n","-o",prefix+"-refined.pto",prefix+"-lens.pto")
                candidates.append(prefix+"-refined.pto")
            observed=[]
            for project in candidates:
                mean,maximum=stats_for(project)
                observed.append((mean,maximum))
                if mean<=3 and maximum<=15 and (best is None or mean<best[0]):
                    best=(mean,maximum,project)
            if best is not None:
                # The candidate must still connect every source frame;
                # the best error alone is not sufficient.
                verify_graph(directory/best[2],len(inputs))
                selected_strategy=strategy
                break
            failures.append(f"{strategy}: geometri hatası (ortalama/en yüksek): "+", ".join(f"{m:.2f}/{x:.2f}" for m,x in observed))
        except RuntimeError as error:
            best=None
            failures.append(f"{strategy}: {error}")
            notify("Eşleştirme aşaması: "+str(error))
        finally:
            if failures:
                (directory/"diagnostics.json").write_text(json.dumps({"mode":"quick" if quick else "quality",
                    "frame_count":len(inputs),"source_size":list(size),"attempt_errors":failures,
                    "stage_seconds":{key:round(value,2) for key,value in durations.items()}},ensure_ascii=False),encoding="utf-8")
    if best is None:
        if quick:
            reason=failures[-1] if failures else "Geometri doğrulanamadı."
            raise RuntimeError("Hızlı mod: "+reason[:190])
        raise RuntimeError("Geometrik eşleşme güvenilir değil. Hatalı panorama oluşturulmadı.")
    notify("Kamera açıları doğrulandı; pozlama dengeleniyor.")
    # Photometric optimization only; do not re-level or re-optimize geometry.
    cmd("autooptimiser","-m","-o","optimized.pto",best[2])
    cmd("pano_modify","-p","2","--fov=360x180",canvas_arg,
        crop_arg,"-o","sphere.pto","optimized.pto")
    error,maximum=stats_for("sphere.pto")
    if error>3 or maximum>15:
        raise RuntimeError("Son geometri kalite kontrolü başarısız.")
    report={"engine":"hugin-enblend","mode":"quick" if quick else "quality","matching_strategy":selected_strategy,
        "source_size":list(size),"proxy_size":list(proxy_size) if proxy_size and selected_strategy in ("fast","quick-recovery") else None,
        "threads":THREADS,"mean_error_px":error,"maximum_error_px":maximum,
        "control_points":verify_graph(directory/"sphere.pto",len(inputs))}
    notify("Nona çekimleri küresel yüzeye yansıtıyor.")
    cmd("nona","-m","TIFF_m","-o","warp","sphere.pto")
    layers=sorted(directory.glob("warp*.tif"))
    if len(layers)!=len(inputs):
        raise RuntimeError("Bazı kareler küreye yansıtılamadı.")
    notify("Enblend birleşim sınırlarını ve geçişleri işliyor.")
    seam="nearest-feature-transform" if quick else "graph-cut"
    cmd("enblend","--wrap=horizontal",f"--primary-seam-generator={seam}",
        "--compression=none" if quick else "--compression=lzw",
        "-f",f"{WIDTH}x{HEIGHT}+0+0","-o","sphere.tif",*[f.name for f in layers])
    notify("Kapsama ve çıktı kalitesi kontrol ediliyor.")
    image=alpha_coverage(directory/"sphere.tif", layers)
    if time.monotonic()-began>=DEADLINE:
        raise RuntimeError("Görüntü işleme süre sınırına ulaştı.")
    write_jpeg(image,directory/"result.jpg")
    report["elapsed_seconds"]=round(time.monotonic()-began,2)
    report["stage_seconds"]={key:round(value,2) for key,value in durations.items()}
    (directory/"quality.json").write_text(json.dumps(report),encoding="utf-8")
    print(json.dumps({"performance":report},ensure_ascii=False),flush=True)

if __name__ == "__main__":
    folder=Path(sys.argv[1]).resolve()
    try:
        main(folder)
    except Exception as exc:
        (folder/"failure.txt").write_text(str(exc)[:250],encoding="utf-8")
        sys.exit(1)
