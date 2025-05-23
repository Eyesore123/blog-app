#!/bin/bash

# Create the assets directory if it doesn't exist
mkdir -p public/assets

# Create symbolic links for all files in build/assets
if [ -d "public/build/assets" ]; then
    for file in public/build/assets/*; do
        filename=$(basename "$file")
        ln -sf "../build/assets/$filename" "public/assets/$filename"
    done
    echo "Created symbolic links from public/assets to public/build/assets"
else
    echo "public/build/assets directory does not exist"
fi
