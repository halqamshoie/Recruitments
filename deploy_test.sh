#!/bin/bash
# CD to script directory to ensure relative paths work
cd "$(dirname "$0")"

echo "Deploying to TEST (https://cccrc.gov.om/recruitments-test/)..."

# Check if test directory exists (optional, but good practice)
# We assume it exists from previous setup manual steps.

# Sync files
rsync -avz --no-perms --no-owner --no-group --exclude='vendor/' --exclude='.git/' --exclude='upload_max_filesize' --exclude='.DS_Store' --exclude='public/uploads/' ./ cccrc@172.29.2.230:/var/www/html/recruitments-test/

# Ensure .htaccess is there
scp public/.htaccess cccrc@172.29.2.230:/var/www/html/recruitments-test/public/

echo "✅ Deployed to Test Successfully!"
