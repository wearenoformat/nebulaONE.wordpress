#!/bin/bash

# Create a new directory for the plugin if it doesn't already exist
mkdir -p build/nebulaONE/

# Copy the plugin files to the new directory
cp -r src/* build/nebulaONE/

# Compress the plugin files into a .zip file
cd build
zip -r nebulaONE.zip nebulaONE

# Delete the directory containing the uncompressed plugin files
rm -rf nebulaONE
