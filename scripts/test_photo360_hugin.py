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

if __name__=="__main__":
    unittest.main()
