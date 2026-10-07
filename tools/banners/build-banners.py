#!/usr/bin/env python3
"""
Builds the Priniti banner images (homepage hero slides and category heroes) from tools/banners/banners.json.

Usage: python3 -I tools/banners/build-banners.py [banner-id ...]

How a banner is made
- Background, red stage, light, shadows and floating ingredients are drawn here, in code (original artwork in the
  style direction of the owner's reference creatives: warm yellow/orange, red rippled stage, floating food).
- Every product pack is the REAL official product image of that product, downloaded from the store's public
  Store API (/wp-json/wc/store/v1/products?slug=...). Packs are only trimmed to their transparent edges, scaled
  uniformly (aspect ratio kept), very slightly tilted and shadowed. Their artwork is never redrawn or edited.
- Before using a product the script checks, against the same API, that it belongs to the banner's category, and
  stops if it does not.
- No text is baked into the images: headings and buttons are HTML on the website.
- `person` (optional) is an approved lifestyle cut-out: {"file": "tools/banners/people/<name>.png", "source": "...",
  "license": "...", "mobile": false}. It must be a transparent PNG with a recorded source and licence. On desktop it
  stands between the HTML text and the packs (the packs move right); on mobile only when "mobile" is true. The
  build stops if the person would overlap any pack. See docs/BANNER-PEOPLE.md.

Outputs (theme/priniti/assets/images/banners/):
  <id>-desktop.webp  1600 x 700  packs on the right; the left ~45% stays calm for the HTML text
  <id>-mobile.webp   1000 x 700  packs centred (shown under the HTML text below 1024 px)
  banners.json       what each image contains (products, categories, sizes), read by the theme
"""
import html
import json
import math
import os
import random
import ssl
import sys
import urllib.parse
import urllib.request

import numpy as np
from PIL import Image, ImageChops, ImageDraw, ImageFilter

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", ".."))
CONFIG = os.path.join(ROOT, "tools", "banners", "banners.json")
OUT = os.path.join(ROOT, "theme", "priniti", "assets", "images", "banners")
CACHE = os.path.join(ROOT, "build", "banner-packs")
STORE = os.environ.get("PRINITI_STORE_URL", "https://shop.prinitifoods.com")


# --------------------------------------------------------------------------------------------------------------
# Real product packs
# --------------------------------------------------------------------------------------------------------------

def _get(url: str) -> bytes:
    ctx = ssl.create_default_context(cafile=os.environ.get("SSL_CERT_FILE") or None)
    req = urllib.request.Request(url, headers={"User-Agent": "priniti-banner-builder"})
    with urllib.request.urlopen(req, context=ctx, timeout=60) as r:
        return r.read()


def product(slug: str) -> dict:
    """Public Store API product (name, categories, original image URL)."""
    data = json.loads(_get(f"{STORE}/wp-json/wc/store/v1/products?slug={urllib.parse.quote(slug)}"))
    if not data:
        raise SystemExit(f"Product not found in the store: {slug}")
    return data[0]


def pack_image(p: dict) -> Image.Image:
    if not p.get("images"):
        raise SystemExit(f"{p['slug']} has no product image")
    src = p["images"][0]["src"]
    os.makedirs(CACHE, exist_ok=True)
    path = os.path.join(CACHE, os.path.basename(urllib.parse.urlparse(src).path))
    if not os.path.exists(path):
        with open(path, "wb") as f:
            f.write(_get(src))
    im = Image.open(path).convert("RGBA")
    box = im.getchannel("A").point(lambda a: 255 if a > 12 else 0).getbbox()
    if not box:
        raise SystemExit(f"{p['slug']}: image has no visible pixels")
    im = im.crop(box)
    # A pack photo without transparency would show its rectangle: refuse rather than fake a cut-out.
    a = np.asarray(im.getchannel("A"))
    if (a[[0, -1], :] > 250).mean() > 0.9 and (a[:, [0, -1]] > 250).mean() > 0.9:
        print(f"  note: {p['slug']} image is opaque; it is shown as a card")
    return im


# --------------------------------------------------------------------------------------------------------------
# Colour helpers and backgrounds (numpy)
# --------------------------------------------------------------------------------------------------------------

def hexrgb(h: str) -> np.ndarray:
    h = h.lstrip("#")
    return np.array([int(h[i:i + 2], 16) for i in (0, 2, 4)], dtype=np.float32)


def radial(w, h, cx, cy, inner, outer, power=1.2):
    yy, xx = np.mgrid[0:h, 0:w].astype(np.float32)
    d = np.sqrt(((xx - cx) / w) ** 2 + ((yy - cy) / h) ** 2) / 0.75
    t = np.clip(d, 0, 1) ** power
    c = hexrgb(inner)[None, None, :] * (1 - t[..., None]) + hexrgb(outer)[None, None, :] * t[..., None]
    return c


def overlay(base, mask, colour, alpha):
    m = (np.clip(mask, 0, 1) * alpha)[..., None]
    return base * (1 - m) + hexrgb(colour)[None, None, :] * m


def pattern(kind, w, h, cx, cy, rnd):
    """0..1 mask of a light decorative pattern."""
    yy, xx = np.mgrid[0:h, 0:w].astype(np.float32)
    dx, dy = xx - cx, yy - cy
    r = np.sqrt(dx ** 2 + dy ** 2)
    ang = np.arctan2(dy, dx)
    if kind == "waves":  # wobbly concentric bands, like a heat shimmer
        v = r + 22 * np.sin(ang * 7 + r / 55) + 14 * np.sin(ang * 3 - r / 90)
        band = (np.mod(v, 64) / 64)
        return np.clip(1 - np.abs(band - 0.5) * 6, 0, 1) * np.clip(r / 260, 0, 1) * 0.8
    if kind == "rays":  # sunburst
        v = np.mod((ang + math.pi) / (2 * math.pi) * 28, 1)
        return (v < 0.42).astype(np.float32) * np.clip(r / 300, 0, 1) * 0.9
    if kind == "zigzag":  # energetic diagonal streaks
        v = np.mod((xx + yy * 0.6 + 26 * np.abs(np.mod(yy / 22, 2) - 1)) / 64, 1)
        return np.clip(1 - np.abs(v - 0.5) * 9, 0, 1)
    if kind == "rangoli":  # rings of round dots (festive rangoli)
        k = np.round(r / 56)
        rk = k * 56
        n = np.maximum(8, np.round(2 * math.pi * rk / 34))
        step = 2 * math.pi / n
        arc = rk * np.abs(ang - np.round(ang / step) * step)
        dist = np.sqrt(arc ** 2 + (r - rk) ** 2)
        size = 3.2 + 1.6 * (np.mod(k, 2))
        return np.clip(size + 1 - dist, 0, 1) * (k > 0) * np.clip(r / 200, 0, 1)
    if kind == "beams":  # cinema spotlights
        m = np.zeros((h, w), np.float32)
        for bx, spread, tilt in ((0.22, 0.10, -0.35), (0.5, 0.12, 0.0), (0.82, 0.10, 0.4)):
            ox = bx * w
            a = np.arctan2(xx - ox - (yy * tilt), yy + 1)
            m += np.clip(1 - np.abs(a) / spread, 0, 1) * np.clip(1 - yy / (h * 0.95), 0, 1)
        return np.clip(m, 0, 1)
    if kind == "bokeh":
        m = np.zeros((h, w), np.float32)
        for _ in range(26):
            bx, by, br = rnd.uniform(0, w), rnd.uniform(0, h * 0.7), rnd.uniform(18, 70)
            m = np.maximum(m, np.clip(1 - np.sqrt((xx - bx) ** 2 + (yy - by) ** 2) / br, 0, 1) ** 1.6 * rnd.uniform(0.3, 0.8))
        return m
    return np.zeros((h, w), np.float32)


def dust(arr, rnd, n, colour, area):
    """Fine glitter: small soft dots."""
    h, w = arr.shape[:2]
    x0, y0, x1, y1 = area
    yy, xx = np.mgrid[0:h, 0:w].astype(np.float32)
    m = np.zeros((h, w), np.float32)
    for _ in range(n):
        cx, cy, s = rnd.uniform(x0, x1), rnd.uniform(y0, y1), rnd.uniform(0.8, 2.6)
        sl = (slice(max(0, int(cy - 8)), int(cy + 8)), slice(max(0, int(cx - 8)), int(cx + 8)))
        m[sl] = np.maximum(m[sl], np.clip(1 - np.sqrt((xx[sl] - cx) ** 2 + (yy[sl] - cy) ** 2) / s, 0, 1))
    return overlay(arr, m, colour, 0.85)


# --------------------------------------------------------------------------------------------------------------
# Ingredient sprites (drawn at 4x, returned as RGBA)
# --------------------------------------------------------------------------------------------------------------

SS = 4


def canvas(s):
    im = Image.new("RGBA", (s * SS, s * SS), (0, 0, 0, 0))
    return im, ImageDraw.Draw(im), s * SS


def done(im, s):
    return im.resize((s, s), Image.LANCZOS)


def shade(im, light=(255, 255, 255), dark=(0, 0, 0), strength=0.35):
    """Soft top-left light / bottom-right shade on a sprite, so flat shapes read as round."""
    w, h = im.size
    yy, xx = np.mgrid[0:h, 0:w].astype(np.float32)
    t = np.clip(((xx / w) + (yy / h)) / 2, 0, 1)
    a = np.asarray(im).astype(np.float32)
    lit = np.array(light, np.float32)
    drk = np.array(dark, np.float32)
    k = (0.5 - t)[..., None] * 2 * strength
    rgb = a[..., :3]
    rgb = np.where(k > 0, rgb + (lit - rgb) * k, rgb + (drk - rgb) * (-k))
    a[..., :3] = np.clip(rgb, 0, 255)
    return Image.fromarray(a.astype(np.uint8))


def sp_chili(s):
    im, d, S = canvas(s)
    pts = [(S * (0.18 + 0.62 * t), S * (0.2 + 0.55 * math.sin(t * 2.4) * 0.9 + 0.1 * t)) for t in np.linspace(0, 1, 40)]
    for i, wdt in enumerate(np.linspace(0.16, 0.03, len(pts) - 1)):
        d.line([pts[i], pts[i + 1]], fill=(206, 22, 30, 255), width=max(2, int(S * wdt)), joint="curve")
        d.ellipse([pts[i][0] - S * wdt / 2, pts[i][1] - S * wdt / 2, pts[i][0] + S * wdt / 2, pts[i][1] + S * wdt / 2], fill=(206, 22, 30, 255))
    im = shade(im, (255, 140, 120), (90, 0, 10), 0.45)
    d = ImageDraw.Draw(im)
    x, y = pts[0]
    d.line([(x, y), (x - S * 0.1, y - S * 0.12)], fill=(46, 125, 50, 255), width=int(S * 0.05))
    d.ellipse([x - S * 0.07, y - S * 0.06, x + S * 0.05, y + S * 0.05], fill=(56, 142, 60, 255))
    return done(im, s)


def sp_peanut(s):
    """Red-skinned peanut kernel, as in Bombay Mix."""
    im, d, S = canvas(s)
    d.ellipse([S * .22, S * .08, S * .78, S * .92], fill=(170, 64, 44, 255))
    d.ellipse([S * .3, S * .14, S * .56, S * .5], fill=(205, 104, 78, 255))
    d.line([(S * .5, S * .12), (S * .5, S * .3)], fill=(120, 40, 26, 255), width=int(S * .03))
    im = shade(im, (240, 150, 120), (70, 20, 10), .45)
    return done(im.rotate(random.uniform(0, 180), resample=Image.BICUBIC), s)


def sp_almond(s):
    im, d, S = canvas(s)
    pts = [(S * (.5 + .3 * math.cos(t) * (1 - .35 * math.sin(t))), S * (.5 + .42 * math.sin(t))) for t in np.linspace(0, 2 * math.pi, 60)]
    d.polygon(pts, fill=(160, 88, 44, 255))
    for k in range(7):
        x = S * (.32 + k * .055)
        d.line([(x, S * .2), (x + S * .03, S * .8)], fill=(124, 62, 28, 180), width=int(S * .012))
    return done(shade(im, (230, 170, 120), (70, 30, 10), .4), s)


def sp_cashew(s):
    """Kidney-shaped cashew: a thick curved stroke with rounded ends."""
    im, d, S = canvas(s)
    pts = [(S * (.5 + .3 * math.cos(t)), S * (.42 + .3 * math.sin(t))) for t in np.linspace(math.pi * .15, math.pi * 1.05, 40)]
    for i, (x, y) in enumerate(pts):
        r = S * (.13 + .05 * math.sin(i / (len(pts) - 1) * math.pi))
        d.ellipse([x - r, y - r, x + r, y + r], fill=(238, 208, 150, 255))
    im = shade(im, (255, 248, 225), (150, 100, 50), .45)
    return done(im.rotate(random.uniform(0, 360), resample=Image.BICUBIC), s)


def sp_tomato(s):
    im, d, S = canvas(s)
    d.ellipse([S * .06, S * .06, S * .94, S * .94], fill=(214, 34, 34, 255))
    d.ellipse([S * .12, S * .12, S * .88, S * .88], fill=(240, 70, 56, 255))
    for k in range(5):
        a = k * 2 * math.pi / 5 + .3
        cx, cy = S * (.5 + .22 * math.cos(a)), S * (.5 + .22 * math.sin(a))
        d.ellipse([cx - S * .1, cy - S * .07, cx + S * .1, cy + S * .07], fill=(255, 150, 110, 255))
        for j in range(3):
            d.ellipse([cx - S * .03 + j * S * .025, cy - S * .015, cx - S * .005 + j * S * .025, cy + S * .015], fill=(255, 225, 150, 255))
    d.ellipse([S * .44, S * .44, S * .56, S * .56], fill=(255, 120, 100, 255))
    return done(im, s)


def sp_onion(s):
    im, d, S = canvas(s)
    d.ellipse([S * .08, S * .08, S * .92, S * .92], outline=(150, 40, 120, 255), width=int(S * .1))
    d.ellipse([S * .17, S * .17, S * .83, S * .83], outline=(245, 225, 245, 255), width=int(S * .04))
    return done(shade(im, (255, 220, 255), (80, 10, 60), .35), s)


def sp_chip(s):
    im, d, S = canvas(s)
    pts = [(S * (.5 + .42 * math.cos(t) + .03 * math.sin(5 * t)), S * (.5 + .3 * math.sin(t) + .03 * math.cos(4 * t))) for t in np.linspace(0, 2 * math.pi, 50)]
    d.polygon(pts, fill=(246, 196, 72, 255))
    for k in range(6):
        y = S * (.28 + k * .09)
        d.arc([S * .15, y - S * .1, S * .85, y + S * .1], 200, 340, fill=(255, 228, 140, 220), width=int(S * .02))
    for _ in range(14):
        x, y = random.uniform(.25, .75) * S, random.uniform(.3, .7) * S
        d.ellipse([x, y, x + S * .025, y + S * .025], fill=(196, 92, 30, 200))
    return done(shade(im, (255, 245, 200), (150, 90, 10), .35), s)


def sp_popcorn(s):
    im, d, S = canvas(s)
    for cx, cy, r in ((.5, .55, .26), (.32, .45, .2), (.68, .45, .2), (.42, .3, .18), (.6, .28, .17), (.5, .72, .16)):
        d.ellipse([S * (cx - r), S * (cy - r), S * (cx + r), S * (cy + r)], fill=(255, 248, 228, 255))
    d.ellipse([S * .44, S * .62, S * .58, S * .78], fill=(240, 186, 60, 255))
    return done(shade(im, (255, 255, 255), (180, 140, 80), .3), s)


def sp_cookie(s, seeds=(70, 40, 20)):
    im, d, S = canvas(s)
    d.ellipse([S * .06, S * .06, S * .94, S * .94], fill=(214, 150, 70, 255))
    d.ellipse([S * .12, S * .12, S * .88, S * .88], fill=(232, 178, 96, 255))
    for _ in range(22):
        x, y = random.uniform(.2, .78) * S, random.uniform(.2, .78) * S
        d.ellipse([x, y, x + S * .035, y + S * .022], fill=seeds + (230,))
    return done(shade(im, (255, 236, 190), (120, 70, 20), .3), s)


def sp_leaf(s, colour=(46, 125, 50)):
    """Curry-leaf lens: the overlap of two circles, with a midrib."""
    im, d, S = canvas(s)
    m1 = Image.new("L", im.size, 0)
    ImageDraw.Draw(m1).ellipse([-S * .25, S * .02, S * .75, S * .98], fill=255)
    m2 = Image.new("L", im.size, 0)
    ImageDraw.Draw(m2).ellipse([S * .25, S * .02, S * 1.25, S * .98], fill=255)
    im.paste(Image.new("RGBA", im.size, colour + (255,)), (0, 0), ImageChops.multiply(m1, m2))
    d = ImageDraw.Draw(im)
    d.line([(S * .5, S * .1), (S * .5, S * .92)], fill=(150, 200, 120, 220), width=int(S * .02))
    return done(shade(im, (180, 240, 160), (10, 60, 20), .35).rotate(random.uniform(-40, 40), resample=Image.BICUBIC), s)


def sp_seed(s, colour=(92, 64, 40)):
    im, d, S = canvas(s)
    d.ellipse([S * .3, S * .1, S * .7, S * .9], fill=colour + (255,))
    return done(shade(im, (200, 170, 120), (30, 20, 10), .4).rotate(random.uniform(0, 180), resample=Image.BICUBIC), s)


def sp_sparkle(s, colour=(255, 255, 240)):
    im, d, S = canvas(s)
    c = S / 2
    pts = []
    for k in range(8):
        a = k * math.pi / 4
        rr = S * (.48 if k % 2 == 0 else .1)
        pts.append((c + rr * math.cos(a), c + rr * math.sin(a)))
    d.polygon(pts, fill=colour + (255,))
    im = im.filter(ImageFilter.GaussianBlur(S * .01))
    return done(im, s)


def sp_marigold(s):
    im, d, S = canvas(s)
    for ring, (r, col) in enumerate(((.42, (238, 120, 20)), (.32, (250, 150, 30)), (.22, (255, 180, 50)))):
        for k in range(18):
            a = k * 2 * math.pi / 18 + ring * .2
            cx, cy = S * (.5 + r * .75 * math.cos(a)), S * (.5 + r * .75 * math.sin(a))
            d.ellipse([cx - S * .09, cy - S * .09, cx + S * .09, cy + S * .09], fill=col + (255,))
    d.ellipse([S * .42, S * .42, S * .58, S * .58], fill=(200, 90, 10, 255))
    return done(shade(im, (255, 230, 150), (150, 50, 0), .3), s)


def sp_sprinkle(s, colour):
    im, d, S = canvas(s)
    d.rounded_rectangle([S * .38, S * .1, S * .62, S * .9], radius=S * .12, fill=colour + (255,))
    return done(im.rotate(random.uniform(0, 180), resample=Image.BICUBIC), s)


def sp_strawberry(s):
    im, d, S = canvas(s)
    d.polygon([(S * .5, S * .92), (S * .12, S * .38), (S * .2, S * .22), (S * .5, S * .2), (S * .8, S * .22), (S * .88, S * .38)], fill=(222, 30, 50, 255))
    d.ellipse([S * .12, S * .18, S * .88, S * .6], fill=(222, 30, 50, 255))
    for _ in range(18):
        x, y = random.uniform(.25, .75) * S, random.uniform(.3, .75) * S
        d.ellipse([x, y, x + S * .03, y + S * .04], fill=(255, 220, 120, 255))
    for k in range(5):
        a = math.pi + k * math.pi / 4
        d.polygon([(S * .5, S * .2), (S * (.5 + .25 * math.cos(a)), S * (.2 + .12 * math.sin(a))), (S * (.5 + .2 * math.cos(a + .3)), S * (.16))], fill=(56, 142, 60, 255))
    return done(shade(im, (255, 160, 170), (100, 0, 20), .35), s)


def sp_choco(s):
    im, d, S = canvas(s)
    d.rounded_rectangle([S * .18, S * .18, S * .82, S * .82], radius=S * .14, fill=(92, 50, 28, 255))
    d.rounded_rectangle([S * .28, S * .28, S * .72, S * .72], radius=S * .08, outline=(122, 72, 44, 255), width=int(S * .03))
    return done(shade(im, (170, 110, 80), (30, 10, 0), .4).rotate(random.uniform(-30, 30), resample=Image.BICUBIC), s)


def sp_ring(s, colour):
    im, d, S = canvas(s)
    d.ellipse([S * .1, S * .1, S * .9, S * .9], outline=colour + (255,), width=int(S * .2))
    return done(shade(im, (255, 255, 255), (0, 0, 0), .35), s)


def sp_corn(s):
    im, d, S = canvas(s)
    d.rounded_rectangle([S * .25, S * .15, S * .75, S * .85], radius=S * .22, fill=(250, 196, 40, 255))
    return done(shade(im, (255, 240, 160), (170, 100, 0), .4).rotate(random.uniform(0, 180), resample=Image.BICUBIC), s)


def sp_wheat(s):
    im, d, S = canvas(s)
    d.line([(S * .5, S * .95), (S * .5, S * .2)], fill=(196, 150, 60, 255), width=int(S * .025))
    for k in range(6):
        y = S * (.25 + k * .1)
        for side in (-1, 1):
            cx = S * (.5 + side * .07)
            d.ellipse([cx - S * .06, y - S * .05, cx + S * .06, y + S * .07], fill=(230, 184, 90, 255))
    d.ellipse([S * .44, S * .1, S * .56, S * .26], fill=(230, 184, 90, 255))
    return done(shade(im, (255, 236, 180), (120, 80, 20), .3).rotate(random.uniform(-25, 25), resample=Image.BICUBIC), s)


def sp_confetti(s, colour):
    im, d, S = canvas(s)
    d.rectangle([S * .3, S * .42, S * .7, S * .58], fill=colour + (255,))
    return done(im.rotate(random.uniform(0, 180), resample=Image.BICUBIC), s)


def sp_steam(s):
    im, d, S = canvas(s)
    for k in range(3):
        x0 = S * (.3 + k * .2)
        pts = [(x0 + S * .06 * math.sin(t * 3), S * (.9 - .8 * t)) for t in np.linspace(0, 1, 30)]
        d.line(pts, fill=(255, 255, 255, 150), width=int(S * .03), joint="curve")
    return done(im.filter(ImageFilter.GaussianBlur(S * .01)), s)


def sp_glow(s, colour=(255, 214, 120)):
    im = Image.new("RGBA", (s, s), (0, 0, 0, 0))
    a = np.zeros((s, s, 4), np.uint8)
    yy, xx = np.mgrid[0:s, 0:s]
    r = np.clip(1 - np.sqrt((xx - s / 2) ** 2 + (yy - s / 2) ** 2) / (s / 2), 0, 1) ** 1.8
    a[..., 0], a[..., 1], a[..., 2] = colour
    a[..., 3] = (r * 200).astype(np.uint8)
    return Image.fromarray(a)


SPRITES = {
    "chili": sp_chili, "peanut": sp_peanut, "almond": sp_almond, "cashew": sp_cashew, "tomato": sp_tomato,
    "onion": sp_onion, "chip": sp_chip, "popcorn": sp_popcorn, "cookie": sp_cookie, "leaf": sp_leaf, "seed": sp_seed,
    "sparkle": sp_sparkle, "marigold": sp_marigold, "strawberry": sp_strawberry, "choco": sp_choco, "corn": sp_corn,
    "wheat": sp_wheat, "steam": sp_steam, "glow": sp_glow,
    "cookie-dark": lambda s: sp_cookie(s, (60, 40, 30)),
    "pistachio": lambda s: sp_leaf(s, (130, 170, 60)),
    "sprinkle": lambda s: sp_sprinkle(s, random.choice([(255, 90, 140), (255, 210, 60), (90, 180, 255), (120, 220, 120), (255, 255, 255)])),
    "ring": lambda s: sp_ring(s, random.choice([(255, 80, 60), (255, 200, 40), (70, 160, 255), (120, 210, 90), (200, 90, 220)])),
    "confetti": lambda s: sp_confetti(s, random.choice([(255, 80, 60), (255, 210, 40), (70, 160, 255), (120, 210, 90), (255, 255, 255)])),
    "sparkle-gold": lambda s: sp_sparkle(s, (255, 214, 102)),
}


# --------------------------------------------------------------------------------------------------------------
# Scenes: colour, pattern and ingredients per banner (all share the Priniti yellow/orange + red stage identity)
# --------------------------------------------------------------------------------------------------------------

SCENES = {
    "namkeen": dict(inner="#FFD23F", outer="#F59E0B", pattern="waves", pat="#FFE27A", stage=("#D3122B", "#8E0A1E"), items=["chili", "peanut", "leaf", "seed", "almond", "cashew"], dust="#FFF3B0"),
    "chips": dict(inner="#FFE14D", outer="#F7A51C", pattern="waves", pat="#FFF0A0", stage=("#D3122B", "#8E0A1E"), items=["chip", "tomato", "onion", "chili", "chip", "leaf"], dust="#FFFBE0"),
    "cookies-home": dict(inner="#FFD98A", outer="#E88A2E", pattern="waves", pat="#FFE9B8", stage=("#C2142A", "#7E0B1C"), items=["cookie", "almond", "cashew", "seed", "cookie-dark", "sparkle-gold"], dust="#FFF4D6"),
    "namkeen-festive": dict(inner="#FFC93C", outer="#E8780C", pattern="rangoli", pat="#FFE08A", stage=("#C8102E", "#860A1C"), items=["peanut", "leaf", "chili", "cashew", "seed", "marigold"], dust="#FFE9A8"),
    "chips-party": dict(inner="#FFEB5C", outer="#FF9F1C", pattern="rays", pat="#FFF6B8", stage=("#D3122B", "#8E0A1E"), items=["chip", "confetti", "tomato", "chip", "onion", "sparkle"], dust="#FFFFFF"),
    "charchare": dict(inner="#FFB627", outer="#F2620F", pattern="zigzag", pat="#FFD36B", stage=("#D3122B", "#8E0A1E"), items=["tomato", "chili", "seed", "sparkle", "chili", "confetti"], dust="#FFE6A0"),
    "popcorn": dict(inner="#B3122E", outer="#3A0612", pattern="beams", pat="#FFD76A", stage=("#7E0B1C", "#40040E"), items=["popcorn", "popcorn", "sparkle-gold", "corn", "popcorn", "sparkle"], dust="#FFD76A", text="light"),
    "puffs": dict(inner="#FFE04A", outer="#FF8A1F", pattern="bokeh", pat="#FFF4B0", stage=("#D3122B", "#8E0A1E"), items=["corn", "confetti", "chili", "sparkle", "tomato", "confetti"], dust="#FFFFFF"),
    "ringo": dict(inner="#FFDF3D", outer="#FF7A1A", pattern="rays", pat="#FFF0A0", stage=("#D3122B", "#8E0A1E"), items=["ring", "ring", "tomato", "sparkle", "ring", "confetti"], dust="#FFFFFF"),
    "rusk": dict(inner="#FFE7B0", outer="#F2A541", pattern="rays", pat="#FFF4D8", stage=("#B5172D", "#7A0C1D"), items=["wheat", "steam", "almond", "sparkle-gold", "wheat", "seed"], dust="#FFFFFF"),
    "sweets": dict(inner="#FFC23C", outer="#E0560E", pattern="rangoli", pat="#FFE59A", stage=("#B8102C", "#6E0716"), items=["marigold", "almond", "pistachio", "cashew", "glow", "sparkle-gold"], dust="#FFE59A"),
    "cookies": dict(inner="#FFE2A0", outer="#D9822B", pattern="bokeh", pat="#FFF2D0", stage=("#B5172D", "#7A0C1D"), items=["cookie", "almond", "cashew", "cookie", "seed", "sparkle-gold"], dust="#FFF4D6"),
    "donut": dict(inner="#FFD1E0", outer="#F06292", pattern="bokeh", pat="#FFE6EF", stage=("#C2185B", "#7B0D3A"), items=["sprinkle", "strawberry", "choco", "sprinkle", "sparkle", "sprinkle"], dust="#FFFFFF"),
}

# Layout changes when a lifestyle person is present: the person slot, and the narrower pack zone beside it.
PERSON = {
    "desktop": dict(slot=(720, 990), height=0.95, cx=1272, zone=590),
    "mobile": dict(slot=(10, 330), height=0.86, cx=655, zone=660),
}

LAYOUTS = {
    # w, h, horizon y, pack centre x, pack baseline y, hero height, zone width for packs, calm text box (x0,y0,x1,y1)
    "desktop": dict(w=1600, h=700, horizon=505, cx=1130, base=640, hero=455, zone=820, calm=(0, 60, 730, 520), sun=(1130, 300)),
    "mobile": dict(w=1000, h=700, horizon=455, cx=500, base=640, hero=470, zone=900, calm=None, sun=(500, 260)),
}

# Pack arrangement, left to right: (index into the banner's products, scale). Index 0 is the hero (largest, in
# front). Packs sit side by side with a small overlap, so wide boxes and tall pouches both stay readable.
ARRANGE = {
    1: [(0, 1.0)],
    2: [(1, 0.82), (0, 1.0)],
    3: [(1, 0.78), (0, 1.0), (2, 0.78)],
    4: [(2, 0.7), (1, 0.82), (0, 1.0), (3, 0.74)],
    5: [(3, 0.64), (1, 0.8), (0, 1.0), (2, 0.8), (4, 0.64)],
}
OVERLAP = 0.12  # share of the narrower neighbour hidden behind the next pack


def stage(arr, L, sc, rnd):
    h, w = arr.shape[:2]
    yy, xx = np.mgrid[0:h, 0:w].astype(np.float32)
    top = L["horizon"] + 18 * ((xx - w / 2) / (w / 2)) ** 2
    floor = (yy >= top).astype(np.float32)
    t = np.clip((yy - top) / (h - L["horizon"]), 0, 1)
    c = hexrgb(sc[0])[None, None, :] * (1 - t[..., None]) + hexrgb(sc[1])[None, None, :] * t[..., None]
    # concentric ripples under the packs
    cx, cy = L["cx"], L["base"] - 10
    e = np.sqrt(((xx - cx) / 1.0) ** 2 + ((yy - cy) / 0.24) ** 2)
    rip = np.clip(1 - np.abs(np.mod(e, 70) - 35) / 6, 0, 1) * np.clip(1 - e / 1400, 0, 1)
    c = c * (1 - rip[..., None] * 0.22) + 255 * rip[..., None] * 0.0 + hexrgb("#FF5A4E")[None, None, :] * rip[..., None] * 0.22
    # rim light along the horizon
    rim = np.clip(1 - np.abs(yy - top) / 5, 0, 1)
    out = arr * (1 - floor[..., None]) + c * floor[..., None]
    out = overlay(out, rim, "#FF8A65", 0.6)
    # soft spotlight on the floor under the packs
    spot = np.clip(1 - np.sqrt(((xx - cx) / (L["zone"] * 0.55)) ** 2 + ((yy - cy) / 70) ** 2), 0, 1) * floor
    return overlay(out, spot, "#FF6B5B", 0.35)


def place(layer, sprite, x, y, blur=0, alpha=1.0, angle=0):
    if angle:
        sprite = sprite.rotate(angle, resample=Image.BICUBIC, expand=True)
    if blur:
        pad = int(blur * 3)
        big = Image.new("RGBA", (sprite.width + pad * 2, sprite.height + pad * 2), (0, 0, 0, 0))
        big.paste(sprite, (pad, pad))
        sprite = big.filter(ImageFilter.GaussianBlur(blur))
    if alpha < 1:
        a = sprite.getchannel("A").point(lambda v: int(v * alpha))
        sprite.putalpha(a)
    layer.alpha_composite(sprite, (int(x - sprite.width / 2), int(y - sprite.height / 2)))


def load_person(banner):
    """The banner's approved person cut-out, validated, or None."""
    spec = banner.get("person")
    if not spec:
        return None
    if not isinstance(spec, dict) or not spec.get("file") or not spec.get("source") or not spec.get("license"):
        raise SystemExit(f"{banner['id']}: person needs file, source and license (see docs/BANNER-PEOPLE.md)")
    path = os.path.join(ROOT, spec["file"])
    if not os.path.abspath(path).startswith(os.path.join(ROOT, "tools", "banners", "people") + os.sep) or not os.path.exists(path):
        raise SystemExit(f"{banner['id']}: person file must exist under tools/banners/people/: {spec['file']}")
    im = Image.open(path)
    if im.mode not in ("RGBA", "LA", "P") or "A" not in im.convert("RGBA").getbands():
        raise SystemExit(f"{banner['id']}: person must be a transparent PNG")
    im = im.convert("RGBA")
    if np.asarray(im.getchannel("A")).min() > 0:
        raise SystemExit(f"{banner['id']}: person image has no transparent background")
    box = im.getchannel("A").point(lambda a: 255 if a > 12 else 0).getbbox()
    return im.crop(box), bool(spec.get("mobile"))


def compose(banner, variant, packs):
    L = dict(LAYOUTS[variant])
    person = load_person(banner)
    if person and (variant == "desktop" or person[1]):
        P = PERSON[variant]
        L.update(cx=P["cx"], zone=P["zone"], sun=(P["cx"], L["sun"][1]))
    else:
        person, P = None, None
    sc = SCENES[banner["scene"]]
    rnd = random.Random(f"{banner['id']}-{variant}")
    random.seed(f"{banner['id']}-{variant}-sprites")
    w, h = L["w"], L["h"]

    arr = radial(w, h, L["sun"][0], L["sun"][1], sc["inner"], sc["outer"])
    arr = overlay(arr, pattern(sc["pattern"], w, h, L["sun"][0], L["sun"][1], rnd), sc["pat"], 0.35)
    arr = dust(arr, rnd, 120 if variant == "desktop" else 80, sc["dust"], (0, 0, w, L["horizon"]))
    arr = stage(arr, L, sc["stage"], rnd)
    img = Image.fromarray(np.clip(arr, 0, 255).astype(np.uint8), "RGB").convert("RGBA")

    # --- pack geometry (left to right, width-aware). Optional `front` products (for example sweet tins) stand in a
    # lower front row between the packs of the main row, as tins in front of gift boxes.
    front_slugs = banner.get("front") or []
    front_packs = [im for slug, im in zip(banner["products"], packs) if slug in front_slugs]
    packs = [im for slug, im in zip(banner["products"], packs) if slug not in front_slugs]
    n = len(packs)
    if person and n > 3:
        raise SystemExit(f"{banner['id']}: with a person, use at most 3 packs in the main row (products stay dominant)")
    hero = L["hero"] * (0.94 if n >= 4 else 1.0)
    row = []
    for i, (idx, scale) in enumerate(ARRANGE[n]):
        im = packs[idx]
        ph = hero * scale
        pw = ph * im.width / im.height
        side = 0 if scale == 1.0 else (-1 if i < [k for k, (j, _) in enumerate(ARRANGE[n]) if j == 0][0] else 1)
        row.append({"im": im, "ph": ph, "pw": pw, "scale": scale, "lift": (1 - scale) * 110, "tilt": side * (3 + (1 - scale) * 8)})
    x = 0.0
    for i, r in enumerate(row):
        if i:
            prev = row[i - 1]
            x += prev["pw"] / 2 + r["pw"] / 2 - OVERLAP * min(prev["pw"], r["pw"])
        r["x"] = x
    left = min(r["x"] - r["pw"] / 2 for r in row)
    right = max(r["x"] + r["pw"] / 2 for r in row)
    fit = min(1.0, L["zone"] / (right - left))
    shift = -(left + right) / 2 * fit
    boxes = [(r["x"], r["ph"], r["pw"], r["lift"], r["tilt"], r["im"], r["scale"]) for r in row]
    for k, im in enumerate(front_packs):
        # between neighbouring main-row packs, lower and in front
        a, b = row[k]["x"], row[min(k + 1, len(row) - 1)]["x"]
        ph = hero * 0.56
        boxes.append(((a + b) / 2 + (0 if a != b else row[k]["pw"] * 0.4), ph, ph * im.width / im.height, -34, 0, im, 2.0 + k))

    # --- ingredients behind the packs (far, blurred) and in front (near, sharp)
    back = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    front = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    placed = []  # final pack rectangles, ingredients never overlap them
    for dx, ph, pw, lift, tilt, im, _ in boxes:
        x = L["cx"] + shift + dx * fit
        bottom = L["base"] - lift
        placed.append((x - pw * fit / 2 - 26, bottom - ph * fit - 26, x + pw * fit / 2 + 26, bottom + 30))
    pack_rect = (min(r[0] for r in placed), min(r[1] for r in placed), max(r[2] for r in placed), h)
    person_img = None
    if person:
        im = person[0]
        ph = h * P["height"]
        maxw = P["slot"][1] - P["slot"][0]
        scale = min(ph / im.height, maxw / im.width)
        person_img = im.resize((max(1, int(im.width * scale)), max(1, int(im.height * scale))), Image.LANCZOS)
        person_xy = (int((P["slot"][0] + P["slot"][1]) / 2 - person_img.width / 2), h - person_img.height)
        placed.append((person_xy[0] - 10, person_xy[1] - 10, person_xy[0] + person_img.width + 10, h))
    calm = L["calm"]
    spots = []
    tries = 0
    while len(spots) < (16 if variant == "desktop" else 11) and tries < 2000:
        tries += 1
        x, y = rnd.uniform(30, w - 30), rnd.uniform(30, L["horizon"] + 120)
        if any(math.hypot(x - a, y - b) < 120 for a, b, _ in spots):
            continue
        if any(r[0] - 50 < x < r[2] + 50 and r[1] - 50 < y < r[3] + 50 for r in placed):
            continue  # never cover a pack (sprites are up to ~100 px wide)
        inside_pack = pack_rect[0] < x < pack_rect[2] and pack_rect[1] < y < pack_rect[3]
        in_calm = calm and calm[0] < x < calm[2] and calm[1] < y < calm[3]
        spots.append((x, y, "calm" if in_calm else ("pack" if inside_pack else "free")))
    for i, (x, y, where) in enumerate(spots):
        kind = sc["items"][i % len(sc["items"])]
        if where == "calm":  # keep the text area quiet: only small, soft accents
            s = rnd.randint(22, 38)
            place(back, SPRITES[kind](s), x, y, blur=2.2, alpha=0.55, angle=rnd.uniform(-40, 40))
        elif y < L["horizon"] - 40 and rnd.random() < 0.5:
            s = rnd.randint(44, 74)
            place(back, SPRITES[kind](s), x, y, blur=1.6, alpha=0.9, angle=rnd.uniform(-40, 40))
        else:
            s = rnd.randint(52, 92)
            place(front, SPRITES[kind](s), x, y, blur=0, alpha=1.0, angle=rnd.uniform(-40, 40))
    img.alpha_composite(back)

    # --- optional approved lifestyle person, in its own slot beside the packs (never over them)
    pack_mask = Image.new("L", (w, h), 0)
    if person_img is not None:
        sh = Image.new("RGBA", (w, h), (0, 0, 0, 0))
        cx = person_xy[0] + person_img.width / 2
        ImageDraw.Draw(sh).ellipse([cx - person_img.width * 0.45, h - 34, cx + person_img.width * 0.45, h + 20], fill=(60, 0, 8, 120))
        img.alpha_composite(sh.filter(ImageFilter.GaussianBlur(16)))
        img.alpha_composite(person_img, person_xy)

    # --- packs with contact + drop shadows, drawn back (smaller) to front (hero)
    for dx, ph, pw, lift, tilt, im, _ in sorted(boxes, key=lambda b: b[6]):
        ph, pw = ph * fit, pw * fit
        x = L["cx"] + shift + dx * fit
        bottom = L["base"] - lift
        pack = im.resize((max(1, int(pw)), max(1, int(ph))), Image.LANCZOS)
        if tilt:
            pack = pack.rotate(-tilt, resample=Image.BICUBIC, expand=True)
        px, py = int(x - pack.width / 2), int(bottom - pack.height)
        # contact shadow on the floor
        sh = Image.new("RGBA", (w, h), (0, 0, 0, 0))
        ImageDraw.Draw(sh).ellipse([x - pw * 0.5, bottom - pw * 0.06, x + pw * 0.5, bottom + pw * 0.08], fill=(60, 0, 8, 150))
        img.alpha_composite(sh.filter(ImageFilter.GaussianBlur(14)))
        # soft drop shadow from the pack silhouette
        ds = Image.new("RGBA", (w, h), (0, 0, 0, 0))
        sil = Image.new("RGBA", pack.size, (50, 0, 10, 0))
        sil.putalpha(pack.getchannel("A").point(lambda v: int(v * 0.42)))
        ds.alpha_composite(sil, (px + 14, py + 16))
        img.alpha_composite(ds.filter(ImageFilter.GaussianBlur(16)))
        img.alpha_composite(pack, (px, py))
        pack_mask.paste(pack.getchannel("A"), (px, py), pack.getchannel("A"))

    if person_img is not None:  # the person must never cover or touch a pack
        pm = Image.new("L", (w, h), 0)
        pm.paste(person_img.getchannel("A"), person_xy, person_img.getchannel("A"))
        both = np.minimum(np.asarray(pm), np.asarray(pack_mask)) > 40
        if both.any():
            raise SystemExit(f"{banner['id']} ({variant}): the person overlaps a pack ({int(both.sum())} px)")

    img.alpha_composite(front)
    return img.convert("RGB")


def main(only):
    cfg = json.load(open(CONFIG))["banners"]
    os.makedirs(OUT, exist_ok=True)
    manifest = {}
    for b in cfg:
        if only and b["id"] not in only:
            continue
        print(f"{b['id']}:")
        packs, used = [], []
        for slug in b["products"]:
            p = product(slug)
            cats = [c["slug"] for c in p.get("categories", [])]
            if b["category"] not in cats:
                raise SystemExit(f"  {slug} is in {cats}, not {b['category']}: refusing to build {b['id']}")
            packs.append(pack_image(p))
            used.append({"slug": slug, "name": html.unescape(p["name"]), "categories": cats, "image": p["images"][0]["src"]})
            print(f"  {slug} ({', '.join(cats)}) <- {os.path.basename(p['images'][0]['src'])}")
        entry = {"kind": b["kind"], "category": b["category"], "scene": b["scene"], "products": used, "files": {}}
        for variant in ("desktop", "mobile"):
            im = compose(b, variant, packs)
            name = f"{b['id']}-{variant}.webp"
            path = os.path.join(OUT, name)
            im.save(path, "WEBP", quality=80, method=6)
            entry["files"][variant] = {"file": name, "width": im.width, "height": im.height, "bytes": os.path.getsize(path)}
            if variant == "mobile":  # background colour of the image's top edge: the HTML text area above it on phones
                top = np.asarray(im.crop((0, 0, im.width, 24))).reshape(-1, 3).mean(axis=0)
                entry["top"] = "#%02x%02x%02x" % tuple(int(v) for v in top)
            print(f"  -> {name} {im.width}x{im.height} {os.path.getsize(path) // 1024} KB")
        entry["text"] = SCENES[b["scene"]].get("text", "dark")
        if b.get("person"):
            entry["person"] = {k: b["person"].get(k) for k in ("file", "source", "license", "mobile")}
        manifest[b["id"]] = entry
    mpath = os.path.join(OUT, "banners.json")
    if only and os.path.exists(mpath):
        old = json.load(open(mpath))
        old.update(manifest)
        manifest = old
    with open(mpath, "w") as f:
        json.dump(manifest, f, indent=1, ensure_ascii=False)
        f.write("\n")


if __name__ == "__main__":
    main(set(sys.argv[1:]))
