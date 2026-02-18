#!/bin/bash
# CD to script directory to ensure relative paths work
cd "$(dirname "$0")"

echo "Deploying to PRODUCTION (https://cccrc.gov.om/recruitments/)..."

# Sync files
rsync -avz --no-perms --no-owner --no-group --exclude='vendor/' --exclude='.git/' --exclude='upload_max_filesize' --exclude='.DS_Store' --exclude='database.sqlite' --exclude='public/uploads/' ./ cccrc@172.29.2.230:/var/www/html/recruitments/

# Ensure .htaccess is there
scp public/.htaccess cccrc@172.29.2.230:/var/www/html/recruitments/public/

# Run Database Migration (One-time, but safe to run multiple times)
#echo "Running Database Migration..."
#ssh cccrc@172.29.2.230 "php /var/www/html/recruitments/add_gender_column.php"

echo "✅ Deployed to Production Successfully!"
