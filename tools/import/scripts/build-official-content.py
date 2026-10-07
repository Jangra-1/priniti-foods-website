"""
Builds tools/import/data/official-content.json from saved copies of the official product pages
(https://www.prinitifoods.com/<page>.php). Only text published on those pages is used:

  shortDescription  first sentence of the page's product description
  description       the consumer paragraph of the description (trade/market paragraphs are dropped)
  highlights        up to 3 FAQ answers about taste, texture or occasions (never trade/distribution answers)
  ingredients       only ingredients the page explicitly states (curated in INGREDIENTS from the page text)
  allergenNote      an allergen statement, when the page has one (ALLERGEN)
  storage           the page's storage guidance, phrased for consumers
  shelfLife         shelf life, when the page states it
Nutrition is not published on the official site, so it is never filled.

Usage: python3 -I build-official-content.py <pages-dir> <out.json>
"""
import html, json, re, sys, os

# WooCommerce product name -> official page (file name without .html). Products not listed have no official page.
PAGES = {
    "All In One": "all-in-one-namkeen", "Aloo Bhujia": "aloo-bhujia-namkeen-manufacturers", "Bhujia": "bhujia-namkeen",
    "Bombay Mix": "bombay-mix-namkeen", "Boondi": "boondi-masala", "Chana Jor Garam": "chana-jor-garam",
    "Chatpati Dal": "chatpati-dal-namkeen-manufacturers", "Cocktail Mix": "Cocktail-Mix", "Cornflakes": "cornflakes-manufacturers",
    "Cornflakes Mixture": "cornflakes-mixture-namkeen", "Diet Chiwda": "diet-chiwda-namkeen-manufacturers",
    "Diet Mixture": "diet-mixture-namkeen-manufacturers", "Gathiya": "gathiya", "Gathiya Papdi": "gathiya-papdi",
    "Hing Jeera Chana": "hing-jeera-chana-namkeen-manufacturers", "Jhalmuri": "jhalmuri-namkeen", "Jhatpat Bhel": "Jhatpal-bhel",
    "Kaju Mixture": "kaju-mixture-namkeen-manufacturers", "Khatta Meetha": "khatta-meetha", "Malai Sev": "malai-sev-namkeen-manufacturers",
    "Masala Murmura": "masala-murmura", "Mast Matar": "mast-matar", "Moong Dal": "moong-dal-namkeen-manufacturers",
    "Navratan Mixture": "navratan-mixture-namkeen-manufacturers", "Panchrattan": "panchrattan-namkeen-manufacturers",
    "Punjabi Tadka": "punjabi-tadka-namkeen-manufacturers", "Ratlami Sev": "ratlami-sev", "Salted Peanuts": "salted-peanuts-manufacturer",
    "Tasty Nuts": "tasty-peanuts-manufacturer", "Tikha Mitha Mix": "tikha-mitha-mix",
    "Potato Chips Spicy Masti": "chips-spicy-masti",
    "CharChare Mast Masala": "charchare-mast-masala", "CharChare Tangy Tomato": "charchare-tangy-tomato",
    "Popcorn Butter Salted": "butter-salted-popcorn", "Ringo Star Tangy Tomato": "ringo-star-tangy-tomato",
    "A To Z": "a-to-z", "Chiji Noodles": "noodles_snacks", "Jungle Masti": "jungle-masti", "Puffcorn": "puff-corn",
    "Moon Chips": "moon_chips", "Noodles": "noodles", "Pasta": "pasta", "Pizza": "pizza", "Puff Tangy Tomato": "rice-puffs-tangy-tomato",
    "Roll N Roll": "roll-n-roll", "Salted Pipe": "salted_pipe", "Veg Biryani": "veg-biryani-snacks", "Mintoze Baby Ring": "mintoze",
    "Puff Hot Spicy": "puffs", "Loopyz": "loopyz", "Tomato Katori": "tomato-katori", "Manchurian Fried Rice": "manchurian-fried-rice",
    "Chilli Storm": "chilli-storm",
    "Ajwain Cookies": "ajwaincookies", "Atta Cookies": "attacookies", "Badam Cookies": "badamcookies", "Coconut Cookies": "coconut-cookies",
    "Jam Cookies": "jam-cookies", "Jeera Cookies": "Jeera-cookies", "Kaju Cookies": "kajucookies", "Tutti Frutti Cookies": "tuttifrutticookies",
    "Choco Vanilla Donut Cake": "choco-vanilla-donut", "Strawberry Vanilla Donut Cake": "strawberry-vanilla-donut",
    "Besan Ladoo": "Besanladdoo", "Gulab Jamun": "gulab-jamun", "Panjeeri Ladoo": "panjeeriLadoo", "Rasgulla": "rasgulla",
    "Soan Papdi": "milk-soan-papdi",
}
# Ingredients as stated on each official page (reviewed by hand: the pages state them in running text, often only the
# base ingredients). Products not listed here have no ingredient statement on the official site.
INGREDIENTS = {
    "All In One": "Red peanuts, gram pulses, rice flakes, gram flakes, raisins, cornflakes, sago, potato, green peas and cashew nuts",
    "Aloo Bhujia": "Potato, seasoned with garlic, mint, cumin, coriander, onion, mango, cardamom, black pepper, clove and cinnamon",
    "Bhujia": "Tepary bean flour and gram pulse flour",
    "Bombay Mix": "Gram pulse, potato, rice flakes and curry leaves",
    "Boondi": "Gram pulse flour",
    "Chatpati Dal": "Gram pulse, seasoned with mango powder, black salt, red chilli, onion powder, garlic powder, ginger powder, cinnamon powder, mint leaf powder, clove powder and mint oil",
    "Cocktail Mix": "Green peas, red peanuts and chickpeas",
    "Cornflakes": "Cornflakes and red peanuts",
    "Cornflakes Mixture": "Cornflakes, potato, raisins and cashew nuts",
    "Diet Chiwda": "Rice flakes and cumin seeds",
    "Diet Mixture": "Rice flakes, bengal gram, cornflakes and curry leaves",
    "Gathiya": "Gram pulse flour and selected spices",
    "Gathiya Papdi": "Gram pulse flour (besan)",
    "Hing Jeera Chana": "Roasted bengal gram, hing (asafoetida) and cumin",
    "Jhalmuri": "Puffed rice, potato, gram pulse and red peanuts",
    "Jhatpat Bhel": "Puffed rice, red peanuts and gram pulse",
    "Kaju Mixture": "Green gram pulses, cashew nuts, potato, musk melon seeds and rice flakes",
    "Khatta Meetha": "Rice flakes, gram pulse, peanuts, peas and sago",
    "Masala Murmura": "Puffed rice, gram pulse and red peanuts",
    "Mast Matar": "Roasted green peas and a savoury spice blend",
    "Moong Dal": "Green gram pulses",
    "Navratan Mixture": "Gram pulse, red peanuts, rice flakes and green peas",
    "Panchrattan": "Potato, raisins, cashew nuts, almonds, rice flakes and curry leaves",
    "Ratlami Sev": "Gram flour and a traditional spice blend",
    "Salted Peanuts": "Peanuts, black salt and common salt",
    "Tasty Nuts": "Red peanuts coated with gram pulse flour, and a blend of spices",
    "Tikha Mitha Mix": "Gram pulse, rice flakes, red peanuts, cornflakes, green peas, tapioca sago and curry leaves",
    "CharChare Mast Masala": "Rice meal, corn meal, gram meal and spices",
    "CharChare Tangy Tomato": "Rice meal, corn meal, gram meal and spices",
    "Popcorn Butter Salted": "Popping corn",
    "Ringo Star Tangy Tomato": "Corn meal",
    "Chiji Noodles": "Refined wheat flour",
    "Jungle Masti": "Refined wheat flour and a blend of spices",
    "Moon Chips": "Refined wheat flour",
    "Noodles": "Refined wheat flour",
    "Pasta": "Refined wheat flour",
    "Pizza": "Corn meal and rice meal",
    "Puff Tangy Tomato": "Corn meal",
    "Roll N Roll": "Refined wheat flour",
    "Salted Pipe": "Refined wheat flour",
    "Atta Cookies": "Wheat flour",
    "Ajwain Cookies": "Ajwain (carom seeds)",
    "Jeera Cookies": "Jeera (cumin seeds)",
    "Badam Cookies": "Almonds",
    "Kaju Cookies": "Cashews",
    "Jam Cookies": "Jam centre",
    "Tutti Frutti Cookies": "Tutti frutti (fruit bits)",
    "Besan Ladoo": "Gram pulse flour, almonds, cashews and selected seeds",
    "Panjeeri Ladoo": "Whole wheat, almonds, cashews and selected seeds",
    "Gulab Jamun": "Milk solids and sugar syrup",
    "Rasgulla": "Cottage cheese (chhena), sugar syrup and rose water",
    "Soan Papdi": "Refined wheat flour, gram flour, sugar, almonds, pistachios and cardamom",
}
ALLERGEN = {
    "Gathiya": "Made using gram-based ingredients; may be processed in a facility that also handles nuts, soy, milk, wheat and sesame. Complete allergen information is provided on the packaging.",
}
# Official pages whose text is a copy of another product's page (left as "Information coming soon").
SKIP = {"Chana Jor Garam": "official page repeats the All in One text"}
# Official sentences that contradict the product (copy errors on the site).
DROP = ["fruity and classic cake flavours"]
# Products whose official FAQ answers were copied from another product (highlights left empty).
NO_HIGHLIGHTS = {"Moong Dal": "FAQ answers on the official page describe Mast Matar"}
# Spelling fixes only; wording is otherwise the official text.
TYPOS = {"choclate": "chocolate"}

TRADE = re.compile(r"retail|distribut|trade|market|manufactur|supplier|shelf movement|offtake|replenish|margin|stock|dispatch|assortment|counter|wholesale|kirana|bulk|order|buyers|channel|price point|demand", re.I)
CONSUMER_Q = re.compile(r"taste|flavou?r|texture|suitable|pair|occasion|enjoy|kind of snack|what is|what are|different|shape|crunch|bite|who is|why is", re.I)

def lines(h):
    b = h[h.find("<body"):]
    b = re.sub(r"(?s)<!--.*?-->", "", b)
    b = re.sub(r"(?s)<(script|style|noscript|select)[^>]*>.*?</\1>", "", b)
    b = re.sub(r"(?i)<br\s*/?>", " ", b)
    t = re.sub(r"(?s)<[^>]+>", "\n", b)
    return [re.sub(r"\s+", " ", html.unescape(l)).strip() for l in t.split("\n") if l.strip()]

def sentences(p):
    return [s.strip() for s in re.split(r"(?<=[.!?])\s+(?=[A-Z])", p) if s.strip()]

def page(path):
    L = lines(open(path, errors="replace").read())
    s = next(i for i in range(1, len(L)) if L[i] == "/" and L[i - 1] == "Home") + 2
    e = L.index("Want more? Connect with us.", s) if "Want more? Connect with us." in L[s:] else len(L)
    body = L[s:e]
    stop = next((i for i, l in enumerate(body) if l in ("Get Brochure", "Contact Us", "Frequently Asked Questions")), len(body))
    paras = [l for l in body[1:stop] if len(l) > 60 and not l.startswith(("Available in", "Smaller packs"))]
    faqs = []
    for i, l in enumerate(body):
        if re.match(r"^\d+\.\s", l) and l.rstrip().endswith("?"):
            a = body[i + 2] if i + 2 < len(body) and body[i + 1] == "+" else body[i + 1]
            faqs.append((re.sub(r"^\d+\.\s*", "", l).strip(), a.strip()))
    return body[0], paras, faqs

def build(pages_dir):
    out = {}
    names = [n for n in PAGES]
    for product, slug in PAGES.items():
        if product in SKIP:
            continue
        title, paras, faqs = page(os.path.join(pages_dir, slug + ".html"))
        consumer = [p for p in paras if not TRADE.search(p)]
        allfaq = " ".join(q + " " + a for q, a in faqs) + " " + " ".join(paras)
        highlights = []
        for q, a in faqs:
            if CONSUMER_Q.search(q) and not TRADE.search(q) and not TRADE.search(a):
                first = sentences(re.sub(r"^(Yes|No|Absolutely)\s*[,.—–-]\s*", "", a))
                if not first:
                    continue
                first = first[0][0].upper() + first[0][1:]
                other = [n for n in names if n != product and len(n) > 4 and n.lower() in first.lower() and n.lower() not in product.lower()]
                if len(first) < 30 or len(first) > 220 or other or any(d in first for d in DROP):
                    continue
                highlights.append(first)
        # Pages that only describe the product in trade terms: use the consumer FAQ answers instead.
        desc = consumer[0] if consumer else (" ".join(highlights[:2]) if highlights else None)
        if not consumer:
            highlights = highlights[2:]
        short = sentences(desc)[0] if desc else None
        highlights = [h for h in highlights if h[:40] not in (desc or "") and h[:40] != (short or "")[:40]]
        if product in NO_HIGHLIGHTS:
            highlights = []
        fix = lambda t: t if t is None else re.sub("|".join(TYPOS), lambda m: TYPOS[m.group(0)], t)
        desc, short, highlights = fix(desc), fix(short), [fix(h) for h in highlights]
        shelf = re.search(r"(up to )?(\d+|six)[- ]?months?\s+shelf life|shelf life of (up to )?(\d+|six)[- ]?months?", allfaq, re.I)
        if shelf:
            n = shelf.group(2) or shelf.group(4)
            n = "6" if n.lower() == "six" else n
            shelf_txt = f"Up to {n} months" if (shelf.group(1) or shelf.group(3)) else f"{n} months"
        else:
            shelf_txt = None
        store = next((a for q, a in faqs if re.search(r"cool,? (and )?dry", a, re.I)), None)
        storage = None
        if store:
            parts = ["Store in a cool, dry place"]
            away = [w for w in ("moisture", "direct sunlight" if "direct sunlight" in store.lower() else "sunlight") if w in store.lower()]
            if away:
                parts.append(" away from " + " and ".join(away))
            storage = "".join(parts) + "."
            if re.search(r"air-?tight", store, re.I):
                storage += " After opening, keep in an air-tight container."
        out[product] = {
            "source": f"https://www.prinitifoods.com/{slug}.php",
            "officialTitle": title,
            "shortDescription": short,
            "description": desc,
            "highlights": highlights[:3],
            "ingredients": INGREDIENTS.get(product),
            "allergenNote": ALLERGEN.get(product),
            "storage": storage,
            "shelfLife": shelf_txt,
        }
    return out

if __name__ == "__main__":
    data = build(sys.argv[1])
    json.dump({"source": "https://www.prinitifoods.com/ (official product pages)", "skipped": SKIP, "products": data}, open(sys.argv[2], "w"), indent=2, ensure_ascii=False)
    print(len(data), "products")
