---
description: How to deploy the Recruitment app to an Ubuntu server in DMZ
---

# Deploy to Ubuntu Server (DMZ)

## Prerequisites
- Ubuntu server with SSH access
- Domain name or static IP pointing to the server
- Root/sudo access on the server

## Steps

### 1. Install Required Packages
// turbo
```bash
ssh user@YOUR_SERVER_IP "sudo apt update && sudo apt upgrade -y && sudo apt install -y apache2 php php-sqlite3 php-mbstring php-xml php-curl php-zip composer unzip"
```

### 2. Enable Apache Rewrite Module
// turbo
```bash
ssh user@YOUR_SERVER_IP "sudo a2enmod rewrite && sudo systemctl restart apache2"
```

### 3. Upload Project Files
From your Mac, run:
```bash
rsync -avz --exclude='vendor/' /Users/hindalqmahouai/Recruitments/ user@YOUR_SERVER_IP:/var/www/recruitments/
```

### 4. Install PHP Dependencies on Server
```bash
ssh user@YOUR_SERVER_IP "cd /var/www/recruitments && sudo composer install --no-dev"
```

### 5. Set File Permissions
```bash
ssh user@YOUR_SERVER_IP "sudo chown -R www-data:www-data /var/www/recruitments && sudo chmod -R 755 /var/www/recruitments && sudo chmod 664 /var/www/recruitments/database.sqlite && sudo chmod 775 /var/www/recruitments/uploads"
```

### 6. Create Apache Virtual Host
SSH into the server and create the config:
```bash
ssh user@YOUR_SERVER_IP
sudo nano /etc/apache2/sites-available/recruitments.conf
```

Paste:
```apache
<VirtualHost *:80>
    ServerName your-domain-or-ip
    DocumentRoot /var/www/recruitments/public

    <Directory /var/www/recruitments/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        FallbackResource /index.php
    </Directory>

    <Directory /var/www/recruitments/src>
        Require all denied
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/recruit-error.log
    CustomLog ${APACHE_LOG_DIR}/recruit-access.log combined
</VirtualHost>
```

### 7. Enable the Site
```bash
sudo a2ensite recruitments.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

### 8. Create .htaccess
```bash
cat > /var/www/recruitments/public/.htaccess << 'EOF'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
EOF
```

### 9. Update Production URLs
Edit `src/Services/EmailService.php` and change the password reset link:
```
FROM: http://localhost:8000/?page=reset_password&token=
TO:   https://your-domain.com/?page=reset_password&token=
```

### 10. (Optional) Enable HTTPS
```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d your-domain.com
```

### 11. Configure Firewall
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 12. Disable PHP Error Display (Production)
```bash
sudo sed -i 's/display_errors = On/display_errors = Off/' /etc/php/*/apache2/php.ini
sudo systemctl restart apache2
```

### 13. Verify Deployment
Open `http://YOUR_SERVER_IP` in a browser and confirm it loads.

## Re-deployment (after code changes)
To push updated code after making changes locally:
```bash
rsync -avz --exclude='vendor/' --exclude='database.sqlite' /Users/hindalqmahouai/Recruitments/ user@YOUR_SERVER_IP:/var/www/recruitments/
ssh user@YOUR_SERVER_IP "sudo chown -R www-data:www-data /var/www/recruitments && sudo systemctl reload apache2"
```
> Note: `database.sqlite` is excluded to preserve production data.

## Security Checklist
- [ ] `database.sqlite` is NOT accessible from the web
- [ ] `display_errors = Off` in php.ini
- [ ] SMTP credentials moved to environment variables
- [ ] HTTPS enabled with valid certificate
- [ ] Regular backups of `database.sqlite` scheduled
- [ ] Uploads directory writable but not executable
