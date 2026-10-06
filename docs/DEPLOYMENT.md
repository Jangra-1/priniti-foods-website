# Deployment (GitHub -> Hostinger Git)

## What was checked

Read-only, through the WordPress REST API (no changes made):

- shop.prinitifoods.com runs on **Hostinger shared hosting** (hPanel, LiteSpeed, PHP 8.5, WordPress 7.1.2, WooCommerce 11.1.2).
- WordPress is installed at `/home/<account>/domains/shop.prinitifoods.com/public_html/`, i.e. `shop.prinitifoods.com` is its own website in hPanel with its own `public_html`.
- That layout is what hPanel's **Git** tool (Websites → Manage → Advanced → GIT) deploys into: it clones a repository branch into a directory under the website's `public_html`, can use a private repository via an SSH deploy key, and can redeploy automatically through a webhook.

What could **not** be verified from here (no hPanel or SSH access, by design): that the Git tool is enabled on your plan, and the exact deploy-key/webhook values. Please confirm in hPanel → your shop.prinitifoods.com website → Advanced → **GIT**.

## Why deploy branches

Hostinger's Git tool runs `git clone`/`git pull` only. It does not run `npm`, so it cannot build the theme's CSS and JS. The main branch also contains things that must never be on the server (`reference/`, `tools/`, sources). So:

1. `.github/workflows/deploy.yml` runs on every push to `main` (or manually): installs, verifies, builds and packages.
2. It force-publishes two branches that contain **only the ready-to-run files**:
   - `deploy/theme-priniti` → the contents of `wp-content/themes/priniti`
   - `deploy/plugin-priniti-core` → the contents of `wp-content/plugins/priniti-core`
3. hPanel's Git tool pulls each branch into its directory.

Deploying files does not activate anything: WordPress only uses the theme/plugin after someone activates them in wp-admin.

## One-time setup in hPanel (you)

For each of the two branches, add a Git deployment in hPanel → shop.prinitifoods.com → Advanced → GIT:

| Field | Theme | Plugin |
|---|---|---|
| Repository | `git@github.com:Jangra-1/priniti-foods-website.git` | same |
| Branch | `deploy/theme-priniti` | `deploy/plugin-priniti-core` |
| Install path (under `public_html`) | `wp-content/themes/priniti` | `wp-content/plugins/priniti-core` |

1. The repository is private: copy the SSH key hPanel shows and add it in GitHub → repository Settings → **Deploy keys** (read-only; do not allow write access).
2. The install directories must not exist yet (hPanel clones into an empty directory). They do not exist on the live site today.
3. Optional auto-deploy: enable **Auto Deployment** in hPanel and add the webhook URL it shows in GitHub → Settings → Webhooks (content type `application/json`, push events). Without it, press **Deploy** in hPanel after each release.
4. The deploy branches appear after the first run of the "Build deploy branches" workflow on `main` (merge the first pull request, or run the workflow manually from the Actions tab).

Recommended: point the Git deployments at a **staging copy** first (hPanel → WordPress → Staging), activate and review there, then repeat on the live site.

## Release flow

1. Work happens on feature branches; CI (`ci.yml`) typechecks, lints PHP, builds and packages every push.
2. Merging to `main` rebuilds the deploy branches.
3. Hostinger pulls them (webhook or Deploy button).
4. Activation, settings and imports are separate, explicit steps (never automatic).

## Fallback (if Git is not available on the plan)

- `npm ci && npm run build && npm run package`, then zip `build/theme/priniti` and `build/plugin/priniti-core` and upload them in wp-admin (Appearance → Themes → Add New → Upload; Plugins → Add New → Upload), or
- a GitHub Action that uploads `build/` over SFTP/SSH (Hostinger SSH uses port 65002), with the SSH credentials stored only as GitHub encrypted secrets.

## Credentials

- No WordPress, Hostinger or GitHub credentials are stored in this repository or in the deploy branches.
- The deploy workflow uses GitHub's automatic `GITHUB_TOKEN` (scoped to this repository) to push the deploy branches.
- The hPanel deploy key is read-only and is added in GitHub's settings, not in the repository.
- The import tool authenticates through the Claude environment proxy, or through `WP_APP_USER`/`WP_APP_PASSWORD` from a gitignored `.env` when run elsewhere.

## After the files are on the server: activation checklist

Do these in order on staging first, then live. None of them happen automatically.

1. **Activate priniti-core** (Plugins). It requires WooCommerce (already active).
2. **Activate the priniti theme** (Appearance > Themes). On the first page load the theme registers its routes (/about, /contact, /login, /signup, /track-order, /search, the policy pages, /category/<slug>) and flushes rewrite rules by itself.
3. **Import the catalog** (from this repository, reviewed first): `npm run import:plan`, then `PRINITI_IMPORT_ALLOW_WRITE=1 npm run import:apply -- --apply --term-meta`. Products arrive without prices ("Price coming soon").
4. **WooCommerce settings** needed by the design (WooCommerce > Settings):
   - General: Currency INR, **0 decimals**; store address; selling and shipping location India (priniti-core also enforces India).
   - Products: weight unit kg.
   - Accounts: leave guest checkout on; the theme's /signup works independently of WooCommerce's registration setting.
   - Settings > General: timezone Asia/Kolkata.
   - Settings > Priniti: enquiry email (defaults to the admin email).
5. Business setup when ready (not part of this build): prices, GST tax rates (and whether prices include tax), shipping zone for India with rates, a payment gateway (test mode first). The checkout's "Checkout is not live" panel lists exactly which of these are still missing.
6. Launch: remove the noindex (`add_filter( 'priniti_noindex', '__return_false' );`, plus the host's X-Robots-Tag), then switch the payment gateway to live.

The existing Cart, Checkout and My account pages are used as they are (the theme renders them; their content does not need editing). Twenty Twenty-Five stays installed as a fallback theme.
