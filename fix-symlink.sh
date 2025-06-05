#!/bin/bash

# Remove existing symlink if it exists
rm -f public/storage

# Create the correct symlink
ln -s ../storage/app/public public/storage

echo "Storage symlink fixed!"