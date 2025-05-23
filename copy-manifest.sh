#!/bin/bash

echo "Copying Vite manifest file..."

# Check if the .vite directory exists in public/build
if [ -d "public/build/.vite" ]; then
    # Check if the manifest exists in the .vite directory
    if [ -f "public/build/.vite/manifest.json" ]; then
        # Copy the manifest to the build directory
        cp public/build/.vite/manifest.json public/build/manifest.json
        echo "✅ Successfully copied manifest.json to public/build/"
        echo "Contents of manifest.json:"
        cat public/build/manifest.json | head -n 10
        echo "..."
    else
        echo "❌ manifest.json not found in public/build/.vite/"
    fi
else
    echo "❌ .vite directory not found in public/build/"
fi

# Verify the manifest exists in the correct location
if [ -f "public/build/manifest.json" ]; then
    echo "✅ manifest.json exists in public/build/"
else
    echo "❌ manifest.json NOT found in public/build/"
    exit 1
fi
