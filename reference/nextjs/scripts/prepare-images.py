#!/usr/bin/env python3
"""
Builds web-ready copies of the supplied Priniti pack images. Packaging artwork is never edited:
images are only colour-converted (CMYK -> sRGB), downscaled when larger than 1600 px, and encoded as WebP.
Usage: python3 scripts/prepare-images.py "<path to unzipped 'Products Images' folder>"
Originals are NOT stored in this repo; keep the supplied archive as the source of truth.
"""
import io, json, os, subprocess, sys
from PIL import Image, ImageCms

Image.MAX_IMAGE_PIXELS = None
SRC = sys.argv[1]
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MAP = json.load(open(os.path.join(ROOT, "scripts", "image-map.json")))
MAX_EDGE = 1600
SRGB = ImageCms.createProfile("sRGB")

def load(path):
    if path.lower().endswith(".pdf"):
        png = subprocess.run(["pdftoppm", "-png", "-r", "200", "-singlefile", path], capture_output=True, check=True).stdout
        return Image.open(io.BytesIO(png)).convert("RGBA")
    im = Image.open(path)
    if im.format == "JPEG":
        im.draft(im.mode, (MAX_EDGE * 2, MAX_EDGE * 2))
    icc = im.info.get("icc_profile")
    if im.mode == "CMYK":
        if icc:
            im = ImageCms.profileToProfile(im, ImageCms.ImageCmsProfile(io.BytesIO(icc)), SRGB, outputMode="RGB")
        else:
            im = im.convert("RGB")
    return im.convert("RGBA")

def save(im, out):
    if max(im.size) > MAX_EDGE:
        s = MAX_EDGE / max(im.size)
        im = im.resize((round(im.size[0] * s), round(im.size[1] * s)), Image.LANCZOS)
    os.makedirs(os.path.dirname(out), exist_ok=True)
    if max(im.size) <= 800:
        im.save(out, "WEBP", lossless=True, method=6)
    else:
        im.save(out, "WEBP", quality=92, method=6)
    return im.size

for slug, files in MAP["published"].items():
    for i, f in enumerate(files, 1):
        size = save(load(os.path.join(SRC, f)), os.path.join(ROOT, "public", "images", "products", f"{slug}-{i}.webp"))
        print("published", f"{slug}-{i}.webp", size)
for slug, f in MAP["pending"].items():
    size = save(load(os.path.join(SRC, f)), os.path.join(ROOT, "assets", "pending-images", f"{slug}.webp"))
    print("pending  ", f"{slug}.webp", size)
