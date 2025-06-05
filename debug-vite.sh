#!/bin/bash

# Rebuild assets
cd /app && npm run build

# Check if build was successful
if [ -f public/build/manifest.json ]; then
  echo "Build complete!"
else
  echo "Build may have failed - no manifest found"
fi