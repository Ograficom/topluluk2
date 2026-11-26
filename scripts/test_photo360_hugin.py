#!/usr/bin/env python3
"""Unit checks for failure gates, not a substitute for actual Hugin execution."""
import tempfile
import unittest
from pathlib import Path
from PIL import Image
import photo360_hugin as h

class QualityTest(unittest.TestCase):
    def test_graph_connected_and_disconnected(self):
        with tempfile.TemporaryDirectory() as d:
            path=Path(d)/"graph.pto"
            lines=[]
            for a,b in [(0,1),(1,2),(2,0)]:
                lines.extend(["c n%d N%d x10 y10 X12 Y10 t0" % (a,b)]*6)
            path.write_text("\n".join(lines))
            self.assertEqual(h.verify_graph(path,3),18)
            path.write_text("\n".join(lines[:6]))
            with self.assertRaises(RuntimeError): h.verify_graph(path,3)

    def test_reject_holes_and_wrong_dimensions(self):
        with tempfile.TemporaryDirectory() as d:
            path=Path(d)/"out.tif"
            old=(h.WIDTH,h.HEIGHT)
            h.WIDTH,h.HEIGHT=64,32
            try:
                im=Image.new("RGBA",(64,32),(80,100,120,255))
                im.save(path)
                self.assertEqual(h.alpha_coverage(path).size,(64,32))
                im.putpixel((30,15),(0,0,0,0))
                im.save(path)
                with self.assertRaises(RuntimeError): h.alpha_coverage(path)
                Image.new("RGBA",(64,64),(0,0,0,255)).save(path)
                with self.assertRaises(RuntimeError): h.alpha_coverage(path)
            finally: h.WIDTH,h.HEIGHT=old

class ProgressTest(unittest.TestCase):
    def test_heartbeat_and_output_are_preserved(self):
        from unittest.mock import patch
        import time
        with tempfile.TemporaryDirectory() as d:
            with patch.object(h, "HEARTBEAT", .05), patch.object(h, "notify") as notify:
                output = h.run(Path(d), time.monotonic(), "python3", "-c",
                               "import time; time.sleep(.18); print('engine output')")
                self.assertIn("engine output", output)
                self.assertGreaterEqual(notify.call_count, 2)
                self.assertIn("engine output", (Path(d)/"engine.log").read_text())
                self.assertIsNone(h.child)

    def test_duration_format(self):
        self.assertEqual(h.duration_text(65), "01:05")

class PerformanceTest(unittest.TestCase):
    def test_fast_pass_preserves_original_fallbacks(self):
        standard=h.matching_strategies(["--allpairs"],fast=False)
        fast=h.matching_strategies(["--allpairs"])
        self.assertEqual(fast[1:],standard)
        self.assertEqual(fast[0][0],"fast")
        self.assertIn("--sieve1size=25",fast[0][1])
        self.assertEqual(fast[0][2],90)

    def test_quick_mode_has_no_expensive_fallback(self):
        strategies=h.matching_strategies(["--allpairs"],quick=True)
        self.assertEqual(len(strategies),1)
        self.assertIn("--prealigned",strategies[0][1])
        self.assertIn("--fullscale",strategies[0][1])
        self.assertEqual(h.QUICK_COUNT,24)
        self.assertEqual((h.QUICK_WIDTH,h.QUICK_HEIGHT),(3072,1536))
        self.assertEqual(h.QUICK_DEADLINE,180)

    def test_bounded_fast_process_is_terminated(self):
        import time
        with tempfile.TemporaryDirectory() as d:
            with self.assertRaisesRegex(RuntimeError,"zaman sınırı"):
                h.run(Path(d),time.monotonic(),"python3","-c","import time; time.sleep(5)",
                      max_seconds=.1)
            self.assertIsNone(h.child)

class ProxyTest(unittest.TestCase):
    def test_transfer_coordinates_keeps_full_size_project(self):
        with tempfile.TemporaryDirectory() as d:
            root=Path(d)
            proxy=root/"proxy.pto";full=root/"full.pto";out=root/"out.pto"
            proxy.write_text('p f2 w4096 h2048\n'+'i w480 h960 n"match-000.jpg"\n'+'i w480 h960 n"match-001.jpg"\n'+'c n0 N1 x10 y20 X30 Y40 t0\n')
            full.write_text('p f2 w4096 h2048\n'+'i w960 h1920 n"input-000.jpg"\n'+'i w960 h1920 n"input-001.jpg"\n')
            h.transfer_control_points(proxy,full,out,["match-000.jpg","match-001.jpg"],
                ["input-000.jpg","input-001.jpg"],(480,960),(960,1920))
            self.assertTrue(out.read_text().startswith(full.read_text()))
            self.assertIn("x20.500000 y40.500000 X60.500000 Y80.500000",out.read_text())
            with self.assertRaises(RuntimeError):
                h.transfer_control_points(proxy,full,out,["match-001.jpg","match-000.jpg"],
                    ["input-000.jpg","input-001.jpg"],(480,960),(960,1920))

class PillowCompatibilityTest(unittest.TestCase):
    def test_old_and_new_filter_api(self):
        from types import SimpleNamespace
        old=SimpleNamespace(LANCZOS=1,BICUBIC=3)
        new=SimpleNamespace(Resampling=SimpleNamespace(LANCZOS=1,BICUBIC=3))
        for module in (old,new):
            self.assertEqual(h.resampling_filter("LANCZOS",module),1)
            self.assertEqual(h.resampling_filter("BICUBIC",module),3)

    def test_real_resize(self):
        im=Image.new("RGB",(1280,1920),(100,120,140))
        im.thumbnail((960,960),h.resampling_filter("LANCZOS"))
        self.assertEqual(im.size,(640,960))
        self.assertEqual(im.resize((320,480),h.resampling_filter("BICUBIC")).size,(320,480))

if __name__=="__main__":
    unittest.main()
