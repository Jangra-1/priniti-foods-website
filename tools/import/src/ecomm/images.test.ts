import assert from "node:assert/strict";
import { test } from "node:test";
import { imageInfo, loadImageMap } from "./images.ts";
import { NEW_PRODUCT_NAMES } from "./model.ts";

test("PNG and JPEG headers are read; anything else is rejected", () => {
  const png = new Uint8Array(32);
  png.set([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]);
  new DataView(png.buffer).setUint32(16, 800);
  new DataView(png.buffer).setUint32(20, 1000);
  assert.deepEqual(imageInfo(png), { type: "png", width: 800, height: 1000 });

  // SOI, APP0 (length 4), SOF0 with height 600 / width 400.
  const jpeg = new Uint8Array([0xff, 0xd8, 0xff, 0xe0, 0x00, 0x04, 0x00, 0x00, 0xff, 0xc0, 0x00, 0x11, 0x08, 0x02, 0x58, 0x01, 0x90, 0x03, 0, 0, 0]);
  assert.deepEqual(imageInfo(jpeg), { type: "jpeg", width: 400, height: 600 });

  assert.equal(imageInfo(new TextEncoder().encode("<html>not an image</html>")), null);
});

test("the image map covers exactly the products created from the item list", () => {
  const mapped = Object.keys(loadImageMap().products).sort();
  const created = Object.values(NEW_PRODUCT_NAMES).sort();
  assert.deepEqual(mapped, created);
  for (const e of Object.values(loadImageMap().products)) {
    if (e.url) assert.match(e.url, /^https:\/\/www\.prinitifoods\.com\//);
  }
});
