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

# Stage the theme, excluding everything that should not ship to production.
rsync -a \
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
