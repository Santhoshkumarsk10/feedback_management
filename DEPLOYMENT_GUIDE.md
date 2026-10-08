# Shibaura Plant Feedback Management System — Production Deployment Guide

A step-by-step production deployment guide specifically customized for:
- **Domain:** `http://shibaura.amoebatronix.com/`
- **Server Path:** `/home/john/shibaura/feedback_management`
- **Server User:** `john`

---

## 1. System Requirements & Architecture Overview

- **Target Domain:** `http://shibaura.amoebatronix.com/` (Supports HTTPS via Let's Encrypt)
- **Application Root:** `/home/john/shibaura/feedback_management`
- **Web Root:** `/home/john/shibaura/feedback_management/public`
- **Operating System:** Ubuntu 22.04 LTS or 24.04 LTS
- **Web Server:** Nginx (1.18+)
- **PHP Version:** PHP 8.2 or PHP 8.3 with FPM
- **Database:** MySQL 8.0+ (InnoDB utf8mb4)
- **Node.js:** Node.js 20.x LTS & npm (for Vite assets)
- **API Authentication:** JWT Auth (HS256)

---

## 2. Server Preparation (SSH as john)

Connect to your server:
```bash
ssh john@your_server_ip
```

### 2.1 Update System Packages
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y git curl wget zip unzip ufw software-properties-common ca-certificates lsb-release
```

### 2.2 Configure Firewall (UFW)
```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw --force enable
```

---

## 3. Installing PHP 8.2 & Required Extensions

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
                    php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip \
                    php8.2-gd php8.2-intl php8.2-bcmath

# Verify PHP installation
php -v
sudo systemctl status php8.2-fpm --no-pager
```

### 3.1 Install Composer 2 Globally
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

---

## 4. Installing & Securing MySQL 8.0

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### 4.1 Create Database and User
Log into MySQL:
```bash
sudo mysql -u root -p
```

Run in MySQL prompt:
```sql
CREATE DATABASE IF NOT EXISTS plant_feedback CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- If devteam user already exists, grant access:
GRANT ALL PRIVILEGES ON plant_feedback.* TO 'devteam'@'localhost';

-- If devteam user needs to be created anew:
-- CREATE USER IF NOT EXISTS 'devteam'@'localhost' IDENTIFIED BY 'StrongSecretPass@2026';
-- GRANT ALL PRIVILEGES ON plant_feedback.* TO 'devteam'@'localhost';

FLUSH PRIVILEGES;
EXIT;
```

---

## 5. Installing Node.js 20 LTS (Vite Asset Compilation)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

---

## 6. Cloning Code to `/home/john/shibaura/feedback_management`

### 6.1 Create Directory & Clone Repository
```bash
mkdir -p /home/john/shibaura
cd /home/john/shibaura

git clone https://github.com/Santhoshkumarsk10/feedback_management.git feedback_management
cd /home/john/shibaura/feedback_management
git checkout develop_santhosh # or main
```

### 6.2 Install Composer PHP Dependencies
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

---

## 7. Configuring Production Environment (.env)

```bash
cp .env.example .env
nano .env
```

Set the following production configuration:
```ini
APP_NAME="Shibaura Plant Feedback"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://shibaura.amoebatronix.com
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=plant_feedback
DB_USERNAME=devteam
DB_PASSWORD=your_devteam_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database
CACHE_STORE=database

JWT_SECRET=
JWT_ALGO=HS256
```

### 7.1 Generate App Key & JWT Secret
```bash
php artisan key:generate
php artisan jwt:secret
```
> **CRITICAL:** `php artisan jwt:secret` is mandatory. Without it, mobile app logins and API requests will return 500 error.

### 7.2 Create Public Storage Symlink
```bash
php artisan storage:link
```

---

## 8. Database Migrations & Initial Seed Data

Run database migrations:
```bash
php artisan migrate --force
```

Seed initial master data (Plants, Roles, Questions, Company profile, and Organizers):
```bash
php artisan db:seed --class=PlantSeeder --force
php artisan db:seed --class=MasterSeeder --force
php artisan db:seed --class=CompanyAndBannerSeeder --force
```

### Seeded Initial Accounts:
- **Super Admin:** `superadmin@plant.test` / `password`
- **Plant Admin:** `admin@plant.test` / `password`
- **Organizers:** `ravi@plant.test`, `priya@plant.test`, `karthik@plant.test`, `suresh@plant.test` / `password`

*(Please change default passwords immediately after initial login).*

---

## 9. Compile Frontend Assets (Vite)

```bash
npm install
npm run build
```

---

## 10. Web Server (Nginx) Configuration

### 10.1 Install Nginx
```bash
sudo apt install -y nginx
sudo systemctl enable nginx
```

### 10.2 Copy Nginx Configuration
```bash
sudo cp /home/john/shibaura/feedback_management/deployment/nginx-feedback.conf /etc/nginx/sites-available/shibaura.amoebatronix.com
```

### 10.3 Enable Site & Restart Nginx
```bash
sudo ln -s /etc/nginx/sites-available/shibaura.amoebatronix.com /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

---

## 11. Home Directory Permissions (Fixing 403 Forbidden)

Because the project is located inside `/home/john/`, Nginx's worker (`www-data`) needs permission to traverse into the directory.

Execute these commands:
```bash
# Allow Nginx (www-data) to access the /home/john and /home/john/shibaura directories
chmod +x /home/john
chmod +x /home/john/shibaura

# Add www-data to the john group
sudo usermod -a -G john www-data

# Set ownership and permissions on storage and cache
sudo chown -R john:www-data /home/john/shibaura/feedback_management/storage
sudo chown -R john:www-data /home/john/shibaura/feedback_management/bootstrap/cache
sudo chmod -R 775 /home/john/shibaura/feedback_management/storage
sudo chmod -R 775 /home/john/shibaura/feedback_management/bootstrap/cache

# Protect .env
chmod 600 /home/john/shibaura/feedback_management/.env
```

---

## 12. Production Performance Optimization (Caching)

```bash
cd /home/john/shibaura/feedback_management
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 13. SSL Certificate Setup (Let's Encrypt HTTPS)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d shibaura.amoebatronix.com
```

Certbot will automatically update the Nginx configuration to enable HTTPS and redirect HTTP traffic to HTTPS.

---

## 14. Background Queues & Scheduled Tasks

### 14.1 Laravel Scheduled Tasks (Cron)
```bash
crontab -e
```
Add:
```cron
* * * * * cd /home/john/shibaura/feedback_management && php artisan schedule:run >> /dev/null 2>&1
```

### 14.2 Supervisor Queue Worker (Database Queue)
```bash
sudo apt install -y supervisor
sudo cp /home/john/shibaura/feedback_management/deployment/supervisor-queue.conf /etc/supervisor/conf.d/shibaura-worker.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start shibaura-feedback-worker:*
sudo supervisorctl status
```

---

## 15. Automated Future Deployments (1-Command Update)

Whenever changes are pushed to GitHub, update the production server with:
```bash
bash /home/john/shibaura/feedback_management/deployment/deploy.sh
```

---

## 16. Post-Deployment Verification Checklist

- [ ] Web Admin dashboard loads at `http://shibaura.amoebatronix.com/login`
- [ ] Super Admin and Plant Admin accounts log in successfully
- [ ] Mobile App APK file downloads directly from `http://shibaura.amoebatronix.com/apk/shibaura-plant-feedback.apk`
- [ ] Visitor Feedback Form renders all sections, MCQs, and rating matrices
- [ ] API verification: Mobile scan endpoint returns HTTP 200
- [ ] Storage symlink verified (company logo and banner images render properly)
- [ ] Log check: `tail -f /home/john/shibaura/feedback_management/storage/logs/laravel.log` shows no errors
