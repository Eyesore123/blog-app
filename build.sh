#!/bin/bash
set -e

# build assets only
npm run build
npm run move-vite-assets
npm run copy-manifest
npm run create-symlinks
