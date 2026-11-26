#!/usr/bin/env python3
"""End-to-end test: render a textured sphere into perspective camera inputs,
then invoke the same Hugin/Nona/Enblend pipeline as production. No network images.
"""
import argparse
import json
import os
import shutil
import time
import math
import subprocess
import sys
import tempfile
from pathlib import Path

import numpy as np
from PIL import Image, ImageDraw, ImageFilter

def make_frames(directory, long_edge=960):
    rng=np.random.default_rng(320)
    w,h=3072,1536
    low=rng.integers(20,235,(h//16,w//16,3),dtype=np.uint8)
    texture=Image.fromarray(low).resize((w,h),Image.Resampling.BICUBIC)
    draw=ImageDraw.Draw(texture)
    for _ in range(3800):
        x,y=map(int,(rng.integers(0,w),rng.integers(0,h)))
        a,b=map(int,(rng.integers(4,42),rng.integers(4,42)))
        color=tuple(map(int,rng.integers(20,245,3)))
        draw.rectangle((x,y,x+a,y+b),fill=color)
    arr=np.asarray(texture)
    sw,sh=round(long_edge*2/3),long_edge
    focal=sw/(2*math.tan(math.radians(28)))
    x,y=np.meshgrid((np.arange(sw)+.5-sw/2)/focal,(sh/2-np.arange(sh)-.5)/focal)
    index=0
    for elev,num in [(0,16),(45,10),(-45,10),(90,1),(-90,1)]:
        pitch=math.radians(elev)
        for i in range(num):
            yaw=2*math.pi*i/num
            forward=np.array([math.sin(yaw)*math.cos(pitch),math.cos(yaw)*math.cos(pitch),math.sin(pitch)])
            right=np.array([math.cos(yaw),-math.sin(yaw),0])
            up=np.array([-math.sin(yaw)*math.sin(pitch),-math.cos(yaw)*math.sin(pitch),math.cos(pitch)])
            rays=forward+x[:,:,None]*right+y[:,:,None]*up
            lon=np.arctan2(rays[:,:,0],rays[:,:,1])
            lat=np.arcsin(np.clip(rays[:,:,2]/np.linalg.norm(rays,axis=2),-1,1))
            u=((lon/(2*math.pi)+.5)*w).astype(int)%w
            v=np.clip(((.5-lat/math.pi)*h).astype(int),0,h-1)
            pic=Image.fromarray(arr[v,u]).filter(ImageFilter.GaussianBlur(.3))
            stem=directory/f"frame-{index:03d}"
            pic.save(stem.with_suffix(".jpg"),quality=95)
            # A small deterministic sensor error forces actual feature registration.
            seed=((math.degrees(yaw)+180)%360)-180
            stem.with_suffix(".json").write_text(json.dumps({
                "yaw":max(-180,min(180,seed+(1 if i%2 else 0))),
                "pitch":max(-90,min(90,elev-(.8 if i%2 else 0))),
                "roll":.4 if i%2 else 0}))
            index+=1

def run_case(source, directory, fast):
    directory.mkdir()
    for file in source.glob("frame-*"):
        shutil.copy2(file,directory/file.name)
    script=Path(__file__).with_name("photo360_hugin.py")
    started=time.monotonic()
    result=subprocess.run([sys.executable,str(script),str(directory)],timeout=700,
                          env={**os.environ,"PHOTO360_FAST_MATCH":"1" if fast else "0"})
    if result.returncode:
        for file in ["failure.txt","engine.log"]:
            path=directory/file
            if path.exists(): print(path.read_text()[-18000:])
        raise SystemExit(result.returncode)
    with Image.open(directory/"result.jpg") as image:
        assert image.size==(4096,2048)
        assert image.format=="JPEG"
    report=json.loads((directory/"quality.json").read_text())
    assert report["mean_error_px"]<=3
    assert report["maximum_error_px"]<=15
    report["wall_seconds"]=round(time.monotonic()-started,2)
    print(json.dumps(report,indent=2))
    return report

def main():
    parser=argparse.ArgumentParser()
    parser.add_argument("--long-edge",type=int,choices=[960,1920],default=960)
    parser.add_argument("--compare",action="store_true",help="Run both matching configurations on identical frames.")
    args=parser.parse_args()
    with tempfile.TemporaryDirectory(prefix="photo360-smoke-") as temp:
        root=Path(temp);source=root/"source";source.mkdir()
        make_frames(source,args.long_edge)
        fast=run_case(source,root/"fast",True)
        if args.compare:
            baseline=run_case(source,root/"baseline",False)
            print(json.dumps({"fast_seconds":fast["wall_seconds"],
                              "baseline_seconds":baseline["wall_seconds"],
                              "speedup":round(baseline["wall_seconds"]/max(.001,fast["wall_seconds"]),2),
                              "fast_error_px":fast["mean_error_px"],
                              "baseline_error_px":baseline["mean_error_px"]},indent=2))
        print("Hugin end-to-end synthetic sphere: PASS")
if __name__=="__main__":
    main()
