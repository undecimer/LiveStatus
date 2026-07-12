#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

echo "Packaging LiveStatus Joomla extension..."

# Remove existing package if it exists
if [ -f install.zip ]; then
    rm -f install.zip
fi

# Package files and directories
zip -r install.zip livestatus.xml livestatus.php bootstrap.php script.php LICENSE README.md language services src

echo "LiveStatus Joomla extension successfully packaged into install.zip!"
