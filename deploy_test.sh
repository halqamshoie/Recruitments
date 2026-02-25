#!/bin/bash
# CD to script directory to ensure relative paths work
cd "$(dirname "$0")"

echo "Deploying to TEST (https://cccrc.gov.om/recruitments-test/)..."

# Check if test directory exists (optional, but good practice)
# We assume it exists from previous setup manual steps.

# Sync files
rsync -avz --no-perms --no-owner --no-group --exclude='vendor/' --exclude='.git/' --exclude='upload_max_filesize' --exclude='.DS_Store' --exclude='public/uploads/' --exclude='storage/uploads/resumes/*' --exclude='storage/uploads/qualifications/*' ./ cccrc@172.29.2.230:/var/www/html/recruitments-test/

# Ensure storage directories exist on the server and fix permissions
ssh cccrc@172.29.2.230 "mkdir -p /var/www/html/recruitments-test/storage/uploads/resumes /var/www/html/recruitments-test/storage/uploads/qualifications && chmod -R 775 /var/www/html/recruitments-test/storage/ && chmod 664 /var/www/html/recruitments-test/database.sqlite && chmod 775 /var/www/html/recruitments-test/"

# Ensure .htaccess is there
scp public/.htaccess cccrc@172.29.2.230:/var/www/html/recruitments-test/public/

echo "✅ Deployed to Test Successfully!"
