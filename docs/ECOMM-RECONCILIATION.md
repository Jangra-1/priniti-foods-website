# E-commerce catalog reconciliation

Source: tools/import/data/ecomm-item-list.csv (Ecomm Item List.xlsx, sheet 30-09-2025)

> **Applied to shop.prinitifoods.com on 2026-10-07** (`npm run ecomm:apply -- --apply`). This is the reconciliation as it
> stood *before* the apply. Post-apply verification (`npm run ecomm:apply -- --verify`): 161 packs checked, 0 errors;
> 77 live products, no duplicate names, Potato Chips Sizzling Hot absent. Two existing names were tidied (slugs kept):
> "Noodles (Yellow)" → "Noodles", "A TO Z" → "A To Z". The 20 new products have no image yet (internal note on each).
> Charchare Mast Masala Rs30 uses the sheet's CURRENT WT (80 g), not the 75 g in its item code.

| | Count |
|---|---|
| Sheet item rows | 162 |
| Excluded rows (Potato Chips Sizzling Hot) | 1 |
| Rows in the e-commerce catalog | 161 |
| Products (rows grouped by product) | 71 |
| Matched to existing WooCommerce products | 51 |
| New products to create | 20 |
| Sellable packs / variations | 161 (68 single packs with 1/2/3 selector, 93 Pack-of-X) |
| Products with more than one pack size | 39 |
| Existing simple products becoming variable | 28 |
| Live products not in the sheet | 6 |
| Problems | 0 |

## Excluded

- line 5: PotatoChips SizzlingHot Rs30(6L*10P*50G)

## Products

| # | Product | Category | Match | Live ID | Packs (label: price; MRP) |
|---|---|---|---|---|---|
| 1 | Potato Chips Classic Salted | potato-chips | normalised, simple→variable | 69 | 50 g: ₹30 (2: ₹52.80, 3: ₹79.20)<br>55 g: ₹20 (2: ₹35.20, 3: ₹52.80)<br>Pack of 14 × 13 g: ₹70 (MRP ₹5 × 14)<br>Pack of 10 × 28 g: ₹100 (MRP ₹10 × 10) |
| 2 | Potato Chips Cream 'n' Onion | potato-chips | normalised, simple→variable | 71 | 50 g: ₹30 (2: ₹52.80, 3: ₹79.20)<br>55 g: ₹20 (2: ₹35.20, 3: ₹52.80)<br>Pack of 14 × 13 g: ₹70 (MRP ₹5 × 14)<br>Pack of 10 × 28 g: ₹100 (MRP ₹10 × 10) |
| 3 | Potato Chips Masala Punch | potato-chips | exact, simple→variable | 73 | 50 g: ₹30 (2: ₹52.80, 3: ₹79.20)<br>55 g: ₹20 (2: ₹35.20, 3: ₹52.80)<br>Pack of 14 × 13 g: ₹70 (MRP ₹5 × 14)<br>Pack of 10 × 28 g: ₹100 (MRP ₹10 × 10) |
| 4 | Potato Chips Spicy Masti | potato-chips | new | new | 50 g: ₹30 (2: ₹52.80, 3: ₹79.20)<br>Pack of 14 × 13 g: ₹70 (MRP ₹5 × 14)<br>Pack of 10 × 28 g: ₹100 (MRP ₹10 × 10) |
| 5 | Potato Chips Tomato Punch | potato-chips | exact, simple→variable | 75 | 50 g: ₹30 (2: ₹52.80, 3: ₹79.20)<br>55 g: ₹20 (2: ₹35.20, 3: ₹52.80)<br>Pack of 14 × 13 g: ₹70 (MRP ₹5 × 14)<br>Pack of 10 × 28 g: ₹100 (MRP ₹10 × 10) |
| 6 | CharChare Mast Masala | charchare-sticks | exact, simple→variable | 76 | 80 g: ₹30 (2: ₹52.80, 3: ₹79.20)<br>85 g: ₹20 (2: ₹35.20, 3: ₹52.80)<br>Pack of 14 × 20 g: ₹70 (MRP ₹5 × 14)<br>Pack of 10 × 42 g: ₹100 (MRP ₹10 × 10) |
| 7 | CharChare Tangy Tomato | charchare-sticks | exact, simple→variable | 78 | 85 g: ₹20 (2: ₹35.20, 3: ₹52.80)<br>Pack of 14 × 20 g: ₹70 (MRP ₹5 × 14)<br>Pack of 10 × 42 g: ₹100 (MRP ₹10 × 10) |
| 8 | Pasta | puffs-fryums | exact, simple→variable | 92 | Pack of 14 × 17 g: ₹70 (MRP ₹5 × 14)<br>Pack of 10 × 34 g: ₹100 (MRP ₹10 × 10) |
| 9 | Noodles (Yellow) | puffs-fryums | reviewed, simple→variable | 90 | Pack of 14 × 18 g: ₹70 (MRP ₹5 × 14)<br>Pack of 10 × 34 g: ₹100 (MRP ₹10 × 10) |
| 10 | A TO Z | puffs-fryums | exact | 81 | Pack of 14 × 18 g: ₹70 (MRP ₹5 × 14) |
| 11 | Chiji Noodles | puffs-fryums | new | new | Pack of 14 × 18 g: ₹70 (MRP ₹5 × 14) |
| 12 | Jungle Masti | puffs-fryums | exact | 84 | Pack of 14 × 18 g: ₹70 (MRP ₹5 × 14) |
| 13 | Moon Chips | puffs-fryums | exact | 88 | Pack of 14 × 18 g: ₹70 (MRP ₹5 × 14) |
| 14 | Roll N Roll | puffs-fryums | new | new | Pack of 14 × 18 g: ₹70 (MRP ₹5 × 14) |
| 15 | Manchurian Fried Rice | puffs-fryums | new | new | Pack of 12 × 18 g: ₹60 (MRP ₹5 × 12) |
| 16 | Mintoze Baby Ring | puffs-fryums | new | new | Pack of 12 × 18 g: ₹60 (MRP ₹5 × 12) |
| 17 | Salted Pipe | puffs-fryums | exact | 98 | Pack of 12 × 18 g: ₹60 (MRP ₹5 × 12) |
| 18 | Tomato Katori | puffs-fryums | new | new | Pack of 12 × 18 g: ₹60 (MRP ₹5 × 12) |
| 19 | Loopyz | puffs-fryums | new | new | Pack of 14 × 18 g: ₹70 (MRP ₹5 × 14) |
| 20 | Pizza | puffs-fryums | exact | 94 | Pack of 14 × 18 g: ₹70 (MRP ₹5 × 14) |
| 21 | Puffcorn | puffs-fryums | new | new | Pack of 14 × 16 g: ₹70 (MRP ₹5 × 14) |
| 22 | Veg Biryani | puffs-fryums | new | new | Pack of 14 × 18 g: ₹70 (MRP ₹5 × 14) |
| 23 | Chilli Storm | puffs-fryums | new | new | Pack of 12 × 18 g: ₹60 (MRP ₹5 × 12) |
| 24 | Puff Hot Spicy | puffs-fryums | new | new | Pack of 12 × 18 g: ₹60 (MRP ₹5 × 12) |
| 25 | Ringo Star Tangy Tomato | ringo-star-rings | exact | 101 | Pack of 14 × 11 g: ₹70 (MRP ₹5 × 14) |
| 26 | Popcorn Butter Salted | popcorn | exact, simple→variable | 79 | 48 g: ₹20 (2: ₹35.20, 3: ₹52.80)<br>Pack of 14 × 12 g: ₹70 (MRP ₹5 × 14)<br>Pack of 10 × 24 g: ₹100 (MRP ₹10 × 10) |
| 27 | All In One | indian-traditional-namkeen | exact, simple→variable | 22 | 200 g: ₹60 (2: ₹105.60, 3: ₹158.40)<br>Pack of 12 × 17 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 34 g: ₹100 (MRP ₹10 × 10)<br>Pack of 10 × 65 g: ₹200 (MRP ₹20 × 10) |
| 28 | Aloo Bhujia | indian-traditional-namkeen | exact, simple→variable | 24 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20)<br>400 g: ₹110 (2: ₹193.60, 3: ₹290.40)<br>1 kg: ₹300 (2: ₹528, 3: ₹792)<br>Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 40 g: ₹100 (MRP ₹10 × 10)<br>Pack of 10 × 75 g: ₹200 (MRP ₹20 × 10) |
| 29 | Bhujia | indian-traditional-namkeen | new | new | 200 g: ₹60 (2: ₹105.60, 3: ₹158.40)<br>400 g: ₹120 (2: ₹211.20, 3: ₹316.80)<br>1 kg: ₹325 (2: ₹572, 3: ₹858)<br>Pack of 12 × 17 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 34 g: ₹100 (MRP ₹10 × 10)<br>Pack of 10 × 65 g: ₹200 (MRP ₹20 × 10) |
| 30 | Bombay Mix | indian-traditional-namkeen | exact, simple→variable | 26 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20)<br>400 g: ₹110 (2: ₹193.60, 3: ₹290.40)<br>1 kg: ₹300 (2: ₹528, 3: ₹792)<br>Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 40 g: ₹100 (MRP ₹10 × 10) |
| 31 | Chana Jor Garam | indian-traditional-namkeen | exact | 30 | Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12) |
| 32 | Chatpati Dal | indian-traditional-namkeen | exact, simple→variable | 32 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20)<br>Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 40 g: ₹100 (MRP ₹10 × 10) |
| 33 | Cocktail Mix | indian-traditional-namkeen | new | new | Pack of 12 × 16 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 32 g: ₹100 (MRP ₹10 × 10) |
| 34 | Cornflakes | indian-traditional-namkeen | new | new | Pack of 12 × 22 g: ₹60 (MRP ₹5 × 12) |
| 35 | Diet Mixture | indian-traditional-namkeen | exact, simple→variable | 36 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20)<br>Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12) |
| 36 | Gathiya Papdi | indian-traditional-namkeen | exact | 40 | Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12) |
| 37 | Gathiya | indian-traditional-namkeen | exact | 38 | Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12) |
| 38 | Hing Jeera Chana | indian-traditional-namkeen | exact, simple→variable | 42 | Pack of 12 × 17 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 34 g: ₹100 (MRP ₹10 × 10) |
| 39 | Jhalmuri | indian-traditional-namkeen | exact, simple→variable | 44 | Pack of 12 × 22 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 44 g: ₹100 (MRP ₹10 × 10) |
| 40 | Jhatpat Bhel | indian-traditional-namkeen | new | new | Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 40 g: ₹100 (MRP ₹10 × 10)<br>Pack of 10 × 75 g: ₹200 (MRP ₹20 × 10) |
| 41 | Khatta Meetha | indian-traditional-namkeen | exact, simple→variable | 48 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20)<br>400 g: ₹110 (2: ₹193.60, 3: ₹290.40)<br>1 kg: ₹300 (2: ₹528, 3: ₹792)<br>Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 40 g: ₹100 (MRP ₹10 × 10)<br>Pack of 10 × 75 g: ₹200 (MRP ₹20 × 10) |
| 42 | Masala Murmura | indian-traditional-namkeen | exact | 52 | Pack of 12 × 22 g: ₹60 (MRP ₹5 × 12) |
| 43 | Mast Matar | indian-traditional-namkeen | exact, simple→variable | 54 | Pack of 12 × 17 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 34 g: ₹100 (MRP ₹10 × 10) |
| 44 | Moong Dal | indian-traditional-namkeen | exact, simple→variable | 56 | 200 g: ₹60 (2: ₹105.60, 3: ₹158.40)<br>400 g: ₹120 (2: ₹211.20, 3: ₹316.80)<br>Pack of 12 × 15 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 30 g: ₹100 (MRP ₹10 × 10)<br>Pack of 10 × 60 g: ₹200 (MRP ₹20 × 10) |
| 45 | Navratan Mixture | indian-traditional-namkeen | exact, simple→variable | 58 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20)<br>400 g: ₹110 (2: ₹193.60, 3: ₹290.40)<br>1 kg: ₹300 (2: ₹528, 3: ₹792)<br>Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 40 g: ₹100 (MRP ₹10 × 10)<br>Pack of 10 × 75 g: ₹200 (MRP ₹20 × 10) |
| 46 | Punjabi Tadka | indian-traditional-namkeen | exact, simple→variable | 62 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20)<br>Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 40 g: ₹100 (MRP ₹10 × 10)<br>Pack of 10 × 75 g: ₹200 (MRP ₹20 × 10) |
| 47 | Ratlami Sev | indian-traditional-namkeen | exact | 64 | Pack of 12 × 17 g: ₹60 (MRP ₹5 × 12) |
| 48 | Salted Peanuts | indian-traditional-namkeen | new | new | Pack of 12 × 15 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 30 g: ₹100 (MRP ₹10 × 10) |
| 49 | Tasty Nuts | indian-traditional-namkeen | exact, simple→variable | 66 | 200 g: ₹60 (2: ₹105.60, 3: ₹158.40)<br>400 g: ₹120 (2: ₹211.20, 3: ₹316.80)<br>1 kg: ₹325 (2: ₹572, 3: ₹858)<br>Pack of 12 × 17 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 34 g: ₹100 (MRP ₹10 × 10)<br>Pack of 10 × 65 g: ₹200 (MRP ₹20 × 10) |
| 50 | Tikha Mitha Mix | indian-traditional-namkeen | exact, simple→variable | 68 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20)<br>400 g: ₹110 (2: ₹193.60, 3: ₹290.40)<br>Pack of 12 × 20 g: ₹60 (MRP ₹5 × 12)<br>Pack of 10 × 40 g: ₹100 (MRP ₹10 × 10) |
| 51 | Boondi Masala | indian-traditional-namkeen | new | new | Pack of 10 × 34 g: ₹100 (MRP ₹10 × 10) |
| 52 | Kaju Mixture | indian-traditional-namkeen | exact, simple→variable | 46 | 200 g: ₹100 (2: ₹176, 3: ₹264)<br>400 g: ₹200 (2: ₹352, 3: ₹528)<br>Pack of 10 × 22 g: ₹100 (MRP ₹10 × 10) |
| 53 | Panchrattan | indian-traditional-namkeen | normalised, simple→variable | 60 | 200 g: ₹100 (2: ₹176, 3: ₹264)<br>Pack of 10 × 22 g: ₹100 (MRP ₹10 × 10) |
| 54 | Boondi | indian-traditional-namkeen | reviewed | 28 | 200 g: ₹60 (2: ₹105.60, 3: ₹158.40) |
| 55 | Cornflakes Mixture | indian-traditional-namkeen | new | new | 200 g: ₹80 (2: ₹140.80, 3: ₹211.20) |
| 56 | Diet Chiwda | indian-traditional-namkeen | exact | 34 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20) |
| 57 | Malai Sev | indian-traditional-namkeen | exact, simple→variable | 50 | 200 g: ₹60 (2: ₹105.60, 3: ₹158.40)<br>400 g: ₹120 (2: ₹211.20, 3: ₹316.80)<br>1 kg: ₹325 (2: ₹572, 3: ₹858) |
| 58 | Rusk | rusk | exact, simple→variable | 102 | 268 g: ₹50 (2: ₹88, 3: ₹132)<br>Pack of 24 × 60 g: ₹240 (MRP ₹10 × 24)<br>Pack of 24 × 120 g: ₹480 (MRP ₹20 × 24) |
| 59 | Ajwain Cookies | cookies | exact | 114 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20) [var 115]<br>300 g: ₹85 (2: ₹149.60, 3: ₹224.40) [var 116] |
| 60 | Jeera Cookies | cookies | exact | 132 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20) [var 133]<br>300 g: ₹85 (2: ₹149.60, 3: ₹224.40) [var 134] |
| 61 | Atta Cookies | cookies | exact | 118 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20) [var 119]<br>300 g: ₹85 (2: ₹149.60, 3: ₹224.40) [var 120] |
| 62 | Coconut Cookies | cookies | exact | 124 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20) [var 125]<br>300 g: ₹85 (2: ₹149.60, 3: ₹224.40) [var 126] |
| 63 | Jam Cookies | cookies | exact | 128 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20) [var 129]<br>300 g: ₹85 (2: ₹149.60, 3: ₹224.40) [var 130] |
| 64 | Tutti Frutti Cookies | cookies | exact | 138 | 200 g: ₹55 (2: ₹96.80, 3: ₹145.20) [var 139]<br>300 g: ₹85 (2: ₹149.60, 3: ₹224.40) [var 140] |
| 65 | Kaju Cookies | cookies | exact | 136 | 300 g: ₹100 (2: ₹176, 3: ₹264) |
| 66 | Badam Cookies | cookies | exact | 122 | 300 g: ₹100 (2: ₹176, 3: ₹264) |
| 67 | Choco Vanilla Donut Cake | donut-cakes | new | new | Pack of 10 × 45 g: ₹200 (MRP ₹20 × 10) |
| 68 | Strawberry Vanilla Donut Cake | donut-cakes | new | new | Pack of 10 × 45 g: ₹200 (MRP ₹20 × 10) |
| 69 | Rasgulla | sweets | exact | 111 | 1 kg: ₹230 (2: ₹404.80, 3: ₹607.20) |
| 70 | Gulab Jamun | sweets | exact | 106 | 1 kg: ₹250 (2: ₹440, 3: ₹660) |
| 71 | Soan Papdi | sweets | exact, simple→variable | 113 | 200 g: ₹75 (2: ₹132, 3: ₹198)<br>450 g: ₹170 (2: ₹299.20, 3: ₹448.80)<br>900 g: ₹340 (2: ₹598.40, 3: ₹897.60) |

## Naming and matching decisions

- **Potato Chips Classic Salt** → #69 Potato Chips Classic Salted: "Potato Chips Classic Salt" = "Potato Chips Classic Salted"
- **Potato Chips Cream Onion** → #71 Potato Chips Cream 'n' Onion: "Potato Chips Cream Onion" = "Potato Chips Cream 'n' Onion"
- **Noodle** → #90 Noodles (Yellow): "Noodles (Yellow)" pack is the fried snack noodle sold at Rs5/Rs10; "Masala Noodles" is instant noodles with a seasoning sachet, not in the sheet.
- **Chiji Noodles** → new product: Different product from Noodles (Yellow) and Masala Noodles.
- **Puff Hot Spicy** → new product: Different flavour from the catalog's Puff Tangy Tomato.
- **Bhujia** → new product: Bhujia (plain) is listed separately from Aloo Bhujia in the sheet, with different MRPs.
- **Cornflakes** → new product: Sheet lists "Cornflakes" (Rs5) and "Cornflakes Mixture" (200 g) under different names; kept separate (see report).
- **Boondi Masala** → new product: Masala boondi is a different flavour from the plain boondi in the catalog.
- **Panchratan** → #60 Panchrattan: "Panchratan" = "Panchrattan"
- **Boondi Plain** → #28 Boondi: Catalog "Boondi" artwork is plain boondi ("fried balls of gram pulse flour").
- **Cornflakes Mixture** → new product: Not in the catalog; kept separate from "Cornflakes" because the sheet names them differently.

## Live products not in the sheet (left unchanged)

- #108 Panjeeri Ladoo (sweets)
- #104 Besan Ladoo (sweets)
- #100 Samosa Sev (puffs-fryums)
- #96 Puff Tangy Tomato (puffs-fryums)
- #86 Masala Noodles (puffs-fryums)
- #83 Dal Chawal (puffs-fryums)

## Sheet rows → catalog

| Line | Sheet item | MRP | Wt | Pcs | → Product | Pack | Price |
|---|---|---|---|---|---|---|---|
| 2 | PotatoChips ClassicSalt Rs30(6L*10P*50G) | 30 | 50 g | 1 | Potato Chips Classic Salted (#69) | 50 g | ₹30 |
| 8 | PotatoChips ClassicSalt Rs20(60P*55G) | 20 | 55 g | 1 | Potato Chips Classic Salted (#69) | 55 g | ₹20 |
| 17 | PotatoChips ClassicSalt Rs5(13L*14P*13G) | 5 | 13 g | 14 | Potato Chips Classic Salted (#69) | Pack of 14 × 13 g | ₹70 |
| 12 | PotatoChips ClassicSalt Rs10(9L*10P*28G) | 10 | 28 g | 10 | Potato Chips Classic Salted (#69) | Pack of 10 × 28 g | ₹100 |
| 3 | PotatoChips CreamOnion Rs30(6L*10P*50G) | 30 | 50 g | 1 | Potato Chips Cream 'n' Onion (#71) | 50 g | ₹30 |
| 9 | PotatoChips CreamOnion Rs20(60P*55G) | 20 | 55 g | 1 | Potato Chips Cream 'n' Onion (#71) | 55 g | ₹20 |
| 18 | PotatoChips CreamOnion Rs5(13L*14P*13G) | 5 | 13 g | 14 | Potato Chips Cream 'n' Onion (#71) | Pack of 14 × 13 g | ₹70 |
| 13 | PotatoChips CreamOnion Rs10(9L*10P*28G) | 10 | 28 g | 10 | Potato Chips Cream 'n' Onion (#71) | Pack of 10 × 28 g | ₹100 |
| 4 | PotatoChips MasalaPunch Rs30(6L*10P*50G) | 30 | 50 g | 1 | Potato Chips Masala Punch (#73) | 50 g | ₹30 |
| 10 | PotatoChips MasalaPunch Rs20(60P*55G) | 20 | 55 g | 1 | Potato Chips Masala Punch (#73) | 55 g | ₹20 |
| 19 | PotatoChips MasalaPunch Rs5(13L*14P*13G) | 5 | 13 g | 14 | Potato Chips Masala Punch (#73) | Pack of 14 × 13 g | ₹70 |
| 14 | PotatoChips MasalaPunch Rs10(9L*10P*28G) | 10 | 28 g | 10 | Potato Chips Masala Punch (#73) | Pack of 10 × 28 g | ₹100 |
| 6 | PotatoChips SpicyMasti Rs30(6L*10P*50G) | 30 | 50 g | 1 | Potato Chips Spicy Masti (new) | 50 g | ₹30 |
| 21 | PotatoChips SpicyMasti Rs5(13L*14P*13G) | 5 | 13 g | 14 | Potato Chips Spicy Masti (new) | Pack of 14 × 13 g | ₹70 |
| 15 | PotatoChips SpicyMasti Rs10(9L*10P*28G) | 10 | 28 g | 10 | Potato Chips Spicy Masti (new) | Pack of 10 × 28 g | ₹100 |
| 7 | PotatoChips TomatoPunch Rs30(6L*10P*50G) | 30 | 50 g | 1 | Potato Chips Tomato Punch (#75) | 50 g | ₹30 |
| 11 | PotatoChips TomatoPunch Rs20(60P*55G) | 20 | 55 g | 1 | Potato Chips Tomato Punch (#75) | 55 g | ₹20 |
| 20 | PotatoChips TomatoPunch Rs5(13L*14P*13G) | 5 | 13 g | 14 | Potato Chips Tomato Punch (#75) | Pack of 14 × 13 g | ₹70 |
| 16 | PotatoChips TomatoPunch Rs10(9L*10P*28G) | 10 | 28 g | 10 | Potato Chips Tomato Punch (#75) | Pack of 10 × 28 g | ₹100 |
| 22 | Charchare Mast Masala Rs30(60P*75G) | 30 | 80 g | 1 | CharChare Mast Masala (#76) | 80 g | ₹30 |
| 23 | Charchare Mast Masala Rs20(60P*85G) | 20 | 85 g | 1 | CharChare Mast Masala (#76) | 85 g | ₹20 |
| 27 | Charchare Mast Masala Rs5(12L*14P*20G) | 5 | 20 g | 14 | CharChare Mast Masala (#76) | Pack of 14 × 20 g | ₹70 |
| 25 | Charchare Mast Masala Rs10(9L*10P*42G) | 10 | 42 g | 10 | CharChare Mast Masala (#76) | Pack of 10 × 42 g | ₹100 |
| 24 | Charchare Tangy Tomato Rs20(60P*85G) | 20 | 85 g | 1 | CharChare Tangy Tomato (#78) | 85 g | ₹20 |
| 28 | Charchare Tangy Tomato Rs5(12L*14P*20G) | 5 | 20 g | 14 | CharChare Tangy Tomato (#78) | Pack of 14 × 20 g | ₹70 |
| 26 | Charchare Tangy Tomato Rs10(9L*10P*42G) | 10 | 42 g | 10 | CharChare Tangy Tomato (#78) | Pack of 10 × 42 g | ₹100 |
| 36 | Pasta Rs5(12L*14P*17G) | 5 | 17 g | 14 | Pasta (#92) | Pack of 14 × 17 g | ₹70 |
| 29 | Pasta Rs10(9L*10P*34G) | 10 | 34 g | 10 | Pasta (#92) | Pack of 10 × 34 g | ₹100 |
| 35 | Noodle Rs5(12L*14P*18G) | 5 | 18 g | 14 | Noodles (Yellow) (#90) | Pack of 14 × 18 g | ₹70 |
| 30 | Noodle Rs10(9L*10P*34G) | 10 | 34 g | 10 | Noodles (Yellow) (#90) | Pack of 10 × 34 g | ₹100 |
| 31 | A To Z Rs5(12L*14P*18G) | 5 | 18 g | 14 | A TO Z (#81) | Pack of 14 × 18 g | ₹70 |
| 32 | Chiji Noodles Rs5(12L*14P*18G) | 5 | 18 g | 14 | Chiji Noodles (new) | Pack of 14 × 18 g | ₹70 |
| 33 | Jungle Masti Rs5(12L*14P*18G) | 5 | 18 g | 14 | Jungle Masti (#84) | Pack of 14 × 18 g | ₹70 |
| 34 | Moon Chips Rs5(12L*14P*18G) | 5 | 18 g | 14 | Moon Chips (#88) | Pack of 14 × 18 g | ₹70 |
| 37 | Roll N Roll Rs5(12L*14P*18G) | 5 | 18 g | 14 | Roll N Roll (new) | Pack of 14 × 18 g | ₹70 |
| 38 | Manchurian Fried Rice Rs5(12L*12P*18G) | 5 | 18 g | 12 | Manchurian Fried Rice (new) | Pack of 12 × 18 g | ₹60 |
| 39 | Mintoze Baby Ring Rs5(12L*12P*18G) | 5 | 18 g | 12 | Mintoze Baby Ring (new) | Pack of 12 × 18 g | ₹60 |
| 40 | Salted Pipe Rs5(12L*12P*18G) | 5 | 18 g | 12 | Salted Pipe (#98) | Pack of 12 × 18 g | ₹60 |
| 41 | Tomato Katori Rs5(12L*12P*18G) | 5 | 18 g | 12 | Tomato Katori (new) | Pack of 12 × 18 g | ₹60 |
| 42 | Loopyz Rs5(12L*14P*18G) | 5 | 18 g | 14 | Loopyz (new) | Pack of 14 × 18 g | ₹70 |
| 43 | Pizza Rs5(12L*14P*18G) | 5 | 18 g | 14 | Pizza (#94) | Pack of 14 × 18 g | ₹70 |
| 44 | Puffcorn Rs5(12L*14P*16G) | 5 | 16 g | 14 | Puffcorn (new) | Pack of 14 × 16 g | ₹70 |
| 45 | Veg Biryani Rs5(13L*14P*18G) | 5 | 18 g | 14 | Veg Biryani (new) | Pack of 14 × 18 g | ₹70 |
| 46 | Chilli Storm Rs5(12L*12P*18G) | 5 | 18 g | 12 | Chilli Storm (new) | Pack of 12 × 18 g | ₹60 |
| 47 | Puff Hot Spicy Rs5(12L*12P*18G) | 5 | 18 g | 12 | Puff Hot Spicy (new) | Pack of 12 × 18 g | ₹60 |
| 48 | Ringo Star Tangy Tomato Rs5(12L*14P*11G) | 5 | 11 g | 14 | Ringo Star Tangy Tomato (#101) | Pack of 14 × 11 g | ₹70 |
| 49 | Popcorn Butter Salted Rs20(6L*10P*48G) | 20 | 48 g | 1 | Popcorn Butter Salted (#79) | 48 g | ₹20 |
| 51 | Popcorn Butter Salted Rs5(13L*14P*12G) | 5 | 12 g | 14 | Popcorn Butter Salted (#79) | Pack of 14 × 12 g | ₹70 |
| 50 | Popcorn Butter Salted Rs10(9L*10P*24G) | 10 | 24 g | 10 | Popcorn Butter Salted (#79) | Pack of 10 × 24 g | ₹100 |
| 105 | All in One (60P*200G) | 60 | 200 g | 1 | All In One (#22) | 200 g | ₹60 |
| 52 | All in One Rs5(30L*12P*17G) | 5 | 17 g | 12 | All In One (#22) | Pack of 12 × 17 g | ₹60 |
| 76 | All in One Rs10(20L*10P*34G) | 10 | 34 g | 10 | All In One (#22) | Pack of 10 × 34 g | ₹100 |
| 96 | All in One Rs20(12L*10P*65G) | 20 | 65 g | 10 | All In One (#22) | Pack of 10 × 65 g | ₹200 |
| 106 | Aloo Bhujia (60P*200G) | 55 | 200 g | 1 | Aloo Bhujia (#24) | 200 g | ₹55 |
| 123 | Aloo Bhujia (30P*400G) | 110 | 400 g | 1 | Aloo Bhujia (#24) | 400 g | ₹110 |
| 133 | Aloo Bhujia (12P*1KG) | 300 | 1 kg | 1 | Aloo Bhujia (#24) | 1 kg | ₹300 |
| 53 | Aloo Bhujia Rs5(30L*12P*20G) | 5 | 20 g | 12 | Aloo Bhujia (#24) | Pack of 12 × 20 g | ₹60 |
| 77 | Aloo Bhujia Rs10(20L*10P*40G) | 10 | 40 g | 10 | Aloo Bhujia (#24) | Pack of 10 × 40 g | ₹100 |
| 97 | Aloo Bhujia Rs20(12L*10P*75G) | 20 | 75 g | 10 | Aloo Bhujia (#24) | Pack of 10 × 75 g | ₹200 |
| 107 | Bhujia (60P*200G) | 60 | 200 g | 1 | Bhujia (new) | 200 g | ₹60 |
| 124 | Bhujia (30P*400G) | 120 | 400 g | 1 | Bhujia (new) | 400 g | ₹120 |
| 134 | Bhujia (12P*1KG) | 325 | 1 kg | 1 | Bhujia (new) | 1 kg | ₹325 |
| 54 | Bhujia Rs5(30L*12P*17G) | 5 | 17 g | 12 | Bhujia (new) | Pack of 12 × 17 g | ₹60 |
| 78 | Bhujia Rs10(20L*10P*34G) | 10 | 34 g | 10 | Bhujia (new) | Pack of 10 × 34 g | ₹100 |
| 98 | Bhujia Rs20(12L*10P*65G) | 20 | 65 g | 10 | Bhujia (new) | Pack of 10 × 65 g | ₹200 |
| 108 | Bombay Mix (60P*200G) | 55 | 200 g | 1 | Bombay Mix (#26) | 200 g | ₹55 |
| 125 | Bombay Mix (30P*400G) | 110 | 400 g | 1 | Bombay Mix (#26) | 400 g | ₹110 |
| 135 | Bombay Mix (12P*1KG) | 300 | 1 kg | 1 | Bombay Mix (#26) | 1 kg | ₹300 |
| 55 | Bombay Mix Rs5(30L*12P*20G) | 5 | 20 g | 12 | Bombay Mix (#26) | Pack of 12 × 20 g | ₹60 |
| 79 | Bombay Mix Rs10(20L*10P*40G) | 10 | 40 g | 10 | Bombay Mix (#26) | Pack of 10 × 40 g | ₹100 |
| 56 | Chana Jor Garam Rs5(30L*12P*20G) | 5 | 20 g | 12 | Chana Jor Garam (#30) | Pack of 12 × 20 g | ₹60 |
| 111 | Chatpati Dal (60P*200G) | 55 | 200 g | 1 | Chatpati Dal (#32) | 200 g | ₹55 |
| 57 | Chatpati Dal Rs5(30L*12P*20G) | 5 | 20 g | 12 | Chatpati Dal (#32) | Pack of 12 × 20 g | ₹60 |
| 81 | Chatpati Dal Rs10(20L*10P*40G) | 10 | 40 g | 10 | Chatpati Dal (#32) | Pack of 10 × 40 g | ₹100 |
| 58 | Cocktail Mix Rs5(30L*12P*16G) | 5 | 16 g | 12 | Cocktail Mix (new) | Pack of 12 × 16 g | ₹60 |
| 82 | Cocktail Mix Rs10(20L*10P*32G) | 10 | 32 g | 10 | Cocktail Mix (new) | Pack of 10 × 32 g | ₹100 |
| 59 | Cornflakes Rs5(30L*12P*22G) | 5 | 22 g | 12 | Cornflakes (new) | Pack of 12 × 22 g | ₹60 |
| 113 | Diet Mixture (60P*200G) | 55 | 200 g | 1 | Diet Mixture (#36) | 200 g | ₹55 |
| 60 | Diet Mixture Rs5(30L*12P*20G) | 5 | 20 g | 12 | Diet Mixture (#36) | Pack of 12 × 20 g | ₹60 |
| 61 | Gathiya Papdi Rs5(30L*12P*20G) | 5 | 20 g | 12 | Gathiya Papdi (#40) | Pack of 12 × 20 g | ₹60 |
| 62 | Gathiya Rs5(30L*12P*20G) | 5 | 20 g | 12 | Gathiya (#38) | Pack of 12 × 20 g | ₹60 |
| 63 | Hing Jeera Chana Rs5(30L*12P*17G) | 5 | 17 g | 12 | Hing Jeera Chana (#42) | Pack of 12 × 17 g | ₹60 |
| 83 | Hing Jeera Chana Rs10(20L*10P*34G) | 10 | 34 g | 10 | Hing Jeera Chana (#42) | Pack of 10 × 34 g | ₹100 |
| 64 | Jhalmuri Rs5(30L*12P*22G) | 5 | 22 g | 12 | Jhalmuri (#44) | Pack of 12 × 22 g | ₹60 |
| 84 | Jhalmuri Rs10(20L*10P*44G) | 10 | 44 g | 10 | Jhalmuri (#44) | Pack of 10 × 44 g | ₹100 |
| 65 | Jhatpat Bhel Rs5(30L*12P*20G) | 5 | 20 g | 12 | Jhatpat Bhel (new) | Pack of 12 × 20 g | ₹60 |
| 85 | Jhatpat Bhel Rs10(20L*10P*40G) | 10 | 40 g | 10 | Jhatpat Bhel (new) | Pack of 10 × 40 g | ₹100 |
| 99 | Jhatpat Bhel Rs20(12L*10P*75G) | 20 | 75 g | 10 | Jhatpat Bhel (new) | Pack of 10 × 75 g | ₹200 |
| 115 | Khatta Meetha (60P*200G) | 55 | 200 g | 1 | Khatta Meetha (#48) | 200 g | ₹55 |
| 127 | Khatta Meetha (30P*400G) | 110 | 400 g | 1 | Khatta Meetha (#48) | 400 g | ₹110 |
| 136 | Khatta Meetha (12P*1KG) | 300 | 1 kg | 1 | Khatta Meetha (#48) | 1 kg | ₹300 |
| 66 | Khatta Meetha Rs5(30L*12P*20G) | 5 | 20 g | 12 | Khatta Meetha (#48) | Pack of 12 × 20 g | ₹60 |
| 87 | Khatta Meetha Rs10(20L*10P*40G) | 10 | 40 g | 10 | Khatta Meetha (#48) | Pack of 10 × 40 g | ₹100 |
| 100 | Khatta Meetha Rs20(12L*10P*75G) | 20 | 75 g | 10 | Khatta Meetha (#48) | Pack of 10 × 75 g | ₹200 |
| 67 | Masala Murmura Rs5(30L*12P*22G) | 5 | 22 g | 12 | Masala Murmura (#52) | Pack of 12 × 22 g | ₹60 |
| 68 | Mast Matar Rs5(30L*12P*17G) | 5 | 17 g | 12 | Mast Matar (#54) | Pack of 12 × 17 g | ₹60 |
| 88 | Mast Matar Rs10(20L*10P*34G) | 10 | 34 g | 10 | Mast Matar (#54) | Pack of 10 × 34 g | ₹100 |
| 117 | Moong Dal (60P*200G) | 60 | 200 g | 1 | Moong Dal (#56) | 200 g | ₹60 |
| 129 | Moong Dal (30P*400G) | 120 | 400 g | 1 | Moong Dal (#56) | 400 g | ₹120 |
| 69 | Moong Dal Rs5(30L*12P*15G) | 5 | 15 g | 12 | Moong Dal (#56) | Pack of 12 × 15 g | ₹60 |
| 89 | Moong Dal Rs10(20L*10P*30G) | 10 | 30 g | 10 | Moong Dal (#56) | Pack of 10 × 30 g | ₹100 |
| 101 | Moong Dal Rs20(12L*10P*60G) | 20 | 60 g | 10 | Moong Dal (#56) | Pack of 10 × 60 g | ₹200 |
| 118 | Navratan Mixture (60P*200G) | 55 | 200 g | 1 | Navratan Mixture (#58) | 200 g | ₹55 |
| 130 | Navratan Mixture (30P*400G) | 110 | 400 g | 1 | Navratan Mixture (#58) | 400 g | ₹110 |
| 138 | Navratan Mixture (12P*1KG) | 300 | 1 kg | 1 | Navratan Mixture (#58) | 1 kg | ₹300 |
| 70 | Navratan Mixture Rs5(30L*12P*20G) | 5 | 20 g | 12 | Navratan Mixture (#58) | Pack of 12 × 20 g | ₹60 |
| 90 | Navratan Mixture Rs10(20L*10P*40G) | 10 | 40 g | 10 | Navratan Mixture (#58) | Pack of 10 × 40 g | ₹100 |
| 102 | Navratan Mixture Rs20(12L*10P*75G) | 20 | 75 g | 10 | Navratan Mixture (#58) | Pack of 10 × 75 g | ₹200 |
| 120 | Punjabi Tadka (60P*200G) | 55 | 200 g | 1 | Punjabi Tadka (#62) | 200 g | ₹55 |
| 71 | Punjabi Tadka Rs5(30L*12P*20G) | 5 | 20 g | 12 | Punjabi Tadka (#62) | Pack of 12 × 20 g | ₹60 |
| 92 | Punjabi Tadka Rs10(20L*10P*40G) | 10 | 40 g | 10 | Punjabi Tadka (#62) | Pack of 10 × 40 g | ₹100 |
| 103 | Punjabi Tadka Rs20(12L*10P*75G) | 20 | 75 g | 10 | Punjabi Tadka (#62) | Pack of 10 × 75 g | ₹200 |
| 72 | Ratlami Sev Rs5(30L*12P*17G) | 5 | 17 g | 12 | Ratlami Sev (#64) | Pack of 12 × 17 g | ₹60 |
| 73 | Salted Peanuts Rs5(30L*12P*15G) | 5 | 15 g | 12 | Salted Peanuts (new) | Pack of 12 × 15 g | ₹60 |
| 93 | Salted Peanuts Rs10(20L*10P*30G) | 10 | 30 g | 10 | Salted Peanuts (new) | Pack of 10 × 30 g | ₹100 |
| 121 | Tasty Nuts (60P*200G) | 60 | 200 g | 1 | Tasty Nuts (#66) | 200 g | ₹60 |
| 131 | Tasty Nuts (30P*400G) | 120 | 400 g | 1 | Tasty Nuts (#66) | 400 g | ₹120 |
| 139 | Tasty Nuts (12P*1KG) | 325 | 1 kg | 1 | Tasty Nuts (#66) | 1 kg | ₹325 |
| 74 | Tasty Nuts Rs5(30L*12P*17G) | 5 | 17 g | 12 | Tasty Nuts (#66) | Pack of 12 × 17 g | ₹60 |
| 94 | Tasty Nuts Rs10(20L*10P*34G) | 10 | 34 g | 10 | Tasty Nuts (#66) | Pack of 10 × 34 g | ₹100 |
| 104 | Tasty Nuts Rs20(12L*10P*65G) | 20 | 65 g | 10 | Tasty Nuts (#66) | Pack of 10 × 65 g | ₹200 |
| 122 | Tikha Mitha Mix (60P*200G) | 55 | 200 g | 1 | Tikha Mitha Mix (#68) | 200 g | ₹55 |
| 132 | Tikha Mitha Mix (30P*400G) | 110 | 400 g | 1 | Tikha Mitha Mix (#68) | 400 g | ₹110 |
| 75 | Tikha Mitha Mix Rs5(30L*12P*20G) | 5 | 20 g | 12 | Tikha Mitha Mix (#68) | Pack of 12 × 20 g | ₹60 |
| 95 | Tikha Mitha Mix Rs10(20L*10P*40G) | 10 | 40 g | 10 | Tikha Mitha Mix (#68) | Pack of 10 × 40 g | ₹100 |
| 80 | Boondi Masala Rs10(20L*10P*34G) | 10 | 34 g | 10 | Boondi Masala (new) | Pack of 10 × 34 g | ₹100 |
| 114 | Kaju Mixture (60P*200G) | 100 | 200 g | 1 | Kaju Mixture (#46) | 200 g | ₹100 |
| 126 | Kaju Mixture (30P*400G) | 200 | 400 g | 1 | Kaju Mixture (#46) | 400 g | ₹200 |
| 86 | Kaju Mixture Rs10(20L*10P*22G) | 10 | 22 g | 10 | Kaju Mixture (#46) | Pack of 10 × 22 g | ₹100 |
| 119 | Panchratan (60P*200G) | 100 | 200 g | 1 | Panchrattan (#60) | 200 g | ₹100 |
| 91 | Panchratan Rs10(20L*10P*22G) | 10 | 22 g | 10 | Panchrattan (#60) | Pack of 10 × 22 g | ₹100 |
| 109 | Boondi Plain (60P*200G) | 60 | 200 g | 1 | Boondi (#28) | 200 g | ₹60 |
| 110 | Cornflakes Mixture (60P*200G) | 80 | 200 g | 1 | Cornflakes Mixture (new) | 200 g | ₹80 |
| 112 | Diet Chiwda (60P*200G) | 55 | 200 g | 1 | Diet Chiwda (#34) | 200 g | ₹55 |
| 116 | Malai Sev (60P*200G) | 60 | 200 g | 1 | Malai Sev (#50) | 200 g | ₹60 |
| 128 | Malai Sev (30P*400G) | 120 | 400 g | 1 | Malai Sev (#50) | 400 g | ₹120 |
| 137 | Malai Sev (12P*1KG) | 325 | 1 kg | 1 | Malai Sev (#50) | 1 kg | ₹325 |
| 142 | Rusk Rs50(20P*268G) | 50 | 268 g | 1 | Rusk (#102) | 268 g | ₹50 |
| 140 | Rusk Rs10(24P*60G) | 10 | 60 g | 24 | Rusk (#102) | Pack of 24 × 60 g | ₹240 |
| 141 | Rusk Rs20(24P*120G) | 20 | 120 g | 24 | Rusk (#102) | Pack of 24 × 120 g | ₹480 |
| 151 | Ajwain Cookies (30P*200G) | 55 | 200 g | 1 | Ajwain Cookies (#114) | 200 g | ₹55 |
| 143 | Ajwain Cookies (15P*300G) | 85 | 300 g | 1 | Ajwain Cookies (#114) | 300 g | ₹85 |
| 152 | Jeera Cookies (30P*200G) | 55 | 200 g | 1 | Jeera Cookies (#132) | 200 g | ₹55 |
| 144 | Jeera Cookies (15P*300G) | 85 | 300 g | 1 | Jeera Cookies (#132) | 300 g | ₹85 |
| 153 | Atta Cookies (30P*200G) | 55 | 200 g | 1 | Atta Cookies (#118) | 200 g | ₹55 |
| 145 | Atta Cookies (15P*300G) | 85 | 300 g | 1 | Atta Cookies (#118) | 300 g | ₹85 |
| 154 | Coconut Cookies (30P*200G) | 55 | 200 g | 1 | Coconut Cookies (#124) | 200 g | ₹55 |
| 146 | Coconut Cookies (15P*300G) | 85 | 300 g | 1 | Coconut Cookies (#124) | 300 g | ₹85 |
| 155 | Jam Cookies (30P*200G) | 55 | 200 g | 1 | Jam Cookies (#128) | 200 g | ₹55 |
| 147 | Jam Cookies (15P*300G) | 85 | 300 g | 1 | Jam Cookies (#128) | 300 g | ₹85 |
| 156 | Tutti Frutti Cookies (30P*200G) | 55 | 200 g | 1 | Tutti Frutti Cookies (#138) | 200 g | ₹55 |
| 148 | Tutti Frutti Cookies (15P*300G) | 85 | 300 g | 1 | Tutti Frutti Cookies (#138) | 300 g | ₹85 |
| 149 | Kaju Cookies (15P*300G) | 100 | 300 g | 1 | Kaju Cookies (#136) | 300 g | ₹100 |
| 150 | Badam Cookies (15P*300G) | 100 | 300 g | 1 | Badam Cookies (#122) | 300 g | ₹100 |
| 157 | Choco Vanilla DonutCake(6B*10P*45G) | 20 | 45 g | 10 | Choco Vanilla Donut Cake (new) | Pack of 10 × 45 g | ₹200 |
| 158 | Strawberry Vanilla DonutCake(6B*10P*45G) | 20 | 45 g | 10 | Strawberry Vanilla Donut Cake (new) | Pack of 10 × 45 g | ₹200 |
| 159 | Rasgulla (12P*1KG) | 230 | 1 kg | 1 | Rasgulla (#111) | 1 kg | ₹230 |
| 160 | Gulab Jamun (12P*1KG) | 250 | 1 kg | 1 | Gulab Jamun (#106) | 1 kg | ₹250 |
| 161 | Soan Papdi (48P*200G) | 75 | 200 g | 1 | Soan Papdi (#113) | 200 g | ₹75 |
| 162 | Soan Papdi (24P*450G) | 170 | 450 g | 1 | Soan Papdi (#113) | 450 g | ₹170 |
| 163 | Soan Papdi (12P*900G) | 340 | 900 g | 1 | Soan Papdi (#113) | 900 g | ₹340 |
| 5 | PotatoChips SizzlingHot Rs30(6L*10P*50G) | 30 | 50 g | 1 | EXCLUDED | – | – |
