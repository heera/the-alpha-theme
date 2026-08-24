#!/usr/bin/env bash
#
# Build a clean, distributable production zip of the theme.
#
# Runs the minifier first, then stages the theme into dist/<slug>/ excluding
# dev-only artifacts, and zips it as dist/<slug>.zip. The zip's top-level
# folder is the theme slug so it installs cleanly via Appearance → Themes.
#
# Usage: `npm run zip` (assets must be minified) or `npm run build` (does both).
set -euo pipefail

SLUG="the-alpha"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST="$ROOT/dist"
STAGE="$DIST/$SLUG"

cd "$ROOT"

rm -rf "$STAGE" "$DIST/$SLUG.zip"
mkdir -p "$STAGE"

# Images that stay in the repo but must never ship. Each was checked against
# every PHP/CSS/JS file in the theme before being listed here.
#
# ⚠️ A theme zip REPLACES the live directory on upgrade — WordPress deletes the
# old folder and unpacks fresh — so anything listed here also disappears from
# the server. Only list files nothing links to.
IMG_EXCLUDES=(
  # WebP sources. hero.png is the 2 MB original that hero.webp is encoded from;
  # the theme serves the .webp only. Anchored to assets/img/, so the root
  # screenshot.png (the Appearance → Themes preview) still ships.
  --exclude='/assets/img/*.png'
  # Abandoned mobile hero crop, superseded by the `contain` layout in main.css's
  # max-width:760px block. No reference anywhere in the theme.
  --exclude='/assets/img/hero-portrait.webp'
  # The share-card default is a Media Library attachment picked in the
  # Customizer (the_alpha_og_default_image), not this file. heera.it's live
  # og:image resolves to /wp-content/uploads/, so nothing points here.
  --exclude='/assets/img/og-default.jpg'
)

# Stage the theme, excluding everything that should not ship to production.
rsync -a \
  "${IMG_EXCLUDES[@]}" \
  --exclude='.git/' \
  --exclude='.github/' \
  --exclude='.claude/' \
  --exclude='node_modules/' \
  --exclude='dist/' \
  --exclude='bin/' \
  --exclude='.gitignore' \
  --exclude='package.json' \
  --exclude='package-lock.json' \
  --exclude='*.bak' \
  --exclude='*.zip' \
  --exclude='*.map' \
  --exclude='.DS_Store' \
  --exclude='._*' \
  ./ "$STAGE/"

# Zip from inside dist/ so paths are relative to the slug folder.
cd "$DIST"
zip -rqX "$SLUG.zip" "$SLUG"
rm -rf "$STAGE"

SIZE="$(du -h "$DIST/$SLUG.zip" | cut -f1 | tr -d ' ')"
echo "Built dist/$SLUG.zip ($SIZE)"
