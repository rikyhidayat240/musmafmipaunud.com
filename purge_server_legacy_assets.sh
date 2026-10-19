#!/usr/bin/env bash
# ==============================================================================
# MUSMA FMIPA — HOSTINGER SERVER ASSET PURGE SCRIPT
# Safely purges verified obsolete legacy files (>300 KB) from public/
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PUBLIC_DIR="${SCRIPT_DIR}/public"

echo "=== PURGING MUSMA OBSOLETE ASSETS FROM SERVER DISK ==="
echo "Target: ${PUBLIC_DIR}"

FILES_TO_REMOVE=(
    "image 67.png"
    "image 65.png"
    "assets/background-hero.png"
    "assets/IMG_5597.webp"
    "assets/background-logo-dpm.png"
    "image 62.png"
    "image 64.png"
    "image 61.png"
    "image 63.png"
    "image 66.png"
    "assets/foto-slide-hero/Banner_1.webp"
    "assets/foto-slide-hero/Banner_2.webp"
    "assets/foto-slide-hero/Banner_3.webp"
    "assets/foto-slide-hero/Banner_4.webp"
    "assets/foto-slide-hero/Banner_5.webp"
    "assets/foto-slide-hero/Banner_6.webp"
    "assets/foto-slide-hero/Banner_7.webp"
    "assets/foto-slide-hero/Banner_8.webp"
    "assets/foto-slide-hero/Banner_9.webp"
    "assets/calon-1.png"
    "assets/calon-2.png"
    "assets/calon-3.png"
    "assets/corner-image-left.png"
    "assets/corner-image-right.png"
    "Text.png"
    "gembok.png"
    "petir.png"
    "logo-musma.svg"
    "images/blank-profile-picture.webp"
)

TOTAL_PURGED=0

for file in "${FILES_TO_REMOVE[@]}"; do
    TARGET="${PUBLIC_DIR}/${file}"
    if [ -f "${TARGET}" ]; then
        rm -f "${TARGET}"
        echo " [PURGED] ${file}"
        TOTAL_PURGED=$((TOTAL_PURGED + 1))
    fi
done

# Remove empty legacy directory
if [ -d "${PUBLIC_DIR}/assets/foto-slide-hero" ]; then
    rmdir "${PUBLIC_DIR}/assets/foto-slide-hero" 2>/dev/null || true
    echo " [CLEANED] assets/foto-slide-hero directory"
fi

echo "=== PURGE COMPLETE: ${TOTAL_PURGED} files removed ==="
