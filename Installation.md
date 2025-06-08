# Where's My Pie? Installation Guide 📋

This guide will walk you through the installation process of Where's My Pie? Lost and Found System. Even if you're a beginner, you'll be able to complete the installation by following these steps!

## 📦 Prerequisites

### Hardware Requirements
- 📱 Raspberry Pi Zero 2W
- 💾 16GB+ microSD card (Class 10 recommended)
- 🔌 5V/2A USB power supply
- 💻 Another computer (for preparing SD card)

### Software Requirements
- 📥 Raspberry Pi Imager
- 📝 Text editor (e.g., Notepad++)
- 📟 SSH client (e.g., PuTTY)

## 🚀 Installation Steps

### Step 1️⃣ - System Preparation

1. **Download and Install Raspberry Pi Imager**
   - Go to [Raspberry Pi website](https://www.raspberrypi.com/software/)
   - Download and install Raspberry Pi Imager

2. **Write OS to SD Card**
   ```bash
   # 1. Open Raspberry Pi Imager
   # 2. Choose OS: Raspberry Pi OS Lite (64-bit)
   # 3. Select your SD card
   # 4. Click settings icon ⚙️ and configure:
   #    - Set hostname: wheresmypie
   #    - Enable SSH
   #    - Set username and password
   #    - Configure WiFi (if needed)
   # 5. Click "Write" and wait for completion
   ```

3. **First Boot**
   ```bash
   # 1. Insert the SD card into Raspberry Pi
   # 2. Connect the power supply
   # 3. Wait 1-2 minutes for the system to complete the first boot
   ```

### Step 2️⃣ - Install Basic Software

1. **Connect to Raspberry Pi**
   ```bash
   # Connect using SSH (Windows users use PuTTY)
   ssh pi@wheresmypie.local
   
   # Update the system
   sudo apt update
   sudo apt upgrade -y
   
   # Install essential tools
   sudo apt install -y git curl wget unzip
   ```

2. **Install Web Server**
   ```bash
   # Install Apache
   sudo apt install -y apache2
   sudo systemctl start apache2
   sudo systemctl enable apache2
   
   # Check if Apache is working
   # Open in browser: http://wheresmypie.local
   # You should see the Apache default page
   ```

3. **Install PHP**
   ```bash
   # Install PHP and necessary extensions
   sudo apt install -y php php-mysql php-gd php-curl php-zip php-mbstring
   
   # Restart Apache
   sudo systemctl restart apache2
   
   # Test PHP
   echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php
   # Open in browser: http://wheresmypie.local/info.php
   ```

4. **Install Database**
   ```bash
   # Install MariaDB
   sudo apt install -y mariadb-server
   sudo systemctl start mariadb
   sudo systemctl enable mariadb
   
   # Run the security script
   sudo mysql_secure_installation
   # Answer the prompts:
   # 1. Current root password: Press Enter
   # 2. Set root password: Y
   # 3. New root password: Enter your password
   # 4. Remove anonymous users: Y
   # 5. Disallow root login remotely: Y
   # 6. Remove test database: Y
   # 7. Reload privilege tables: Y
   ```

### Step 3️⃣ - Install Where's My Pie?

1. **Download the Code**
   ```bash
   # Remove Apache default page
   sudo rm /var/www/html/index.html
   
   # Clone the repository
   cd /var/www/html
   sudo git clone https://github.com/Irenemicooo/WheresMyPie.git .
   ```

2. **Set Up Database**
   ```bash
   # Log into MySQL
   sudo mysql -u root -p
   
   # In MySQL prompt, run:
   CREATE DATABASE WheresMyPie;
   CREATE USER 'pieuser'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON WheresMyPie.* TO 'pieuser'@'localhost';
   FLUSH PRIVILEGES;
   exit;
   
   # Import database schema
   mysql -u pieuser -p WheresMyPie < sql/schema.sql
   ```

3. **Configure Application**
   ```bash
   # Copy configuration template
   sudo cp includes/config.php.example includes/config.php
   
   # Edit configuration file
   sudo nano includes/config.php
   ```

   Edit config.php with your settings:
   ```php
   <?php
   // Database configuration
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'WheresMyPie');
   define('DB_USER', 'pieuser');
   define('DB_PASS', 'your_password');

   // Application settings
   define('APP_NAME', 'Where\'s My Pie?');
   define('BASE_URL', '/WheresMyPie');
   define('UPLOAD_DIR', __DIR__ . '/../uploads/');
   define('MAX_FILE_SIZE', 5242880); // 5MB

   // Security settings
   define('DEBUG', false);
   define('HASH_COST', 10);
   ?>
   ```

### Step 4️⃣ - Set File Permissions

1. **Set Ownership and Permissions**
   ```bash
   # Set the correct owner
   sudo chown -R www-data:www-data /var/www/html/WheresMyPie
   
   # Set directory permissions
   sudo find /var/www/html/WheresMyPie -type d -exec chmod 755 {} \;
   
   # Set file permissions
   sudo find /var/www/html/WheresMyPie -type f -exec chmod 644 {} \;
   
   # Create upload directories and set permissions
   sudo mkdir -p /var/www/html/WheresMyPie/public_html/uploads
   sudo mkdir -p /var/www/html/WheresMyPie/public_html/uploads/items
   sudo mkdir -p /var/www/html/WheresMyPie/public_html/uploads/evidence
   sudo chown -R www-data:www-data /var/www/html/WheresMyPie/public_html/uploads
   sudo chmod -R 755 /var/www/html/WheresMyPie/public_html/uploads
   ```

2. **Configure Upload Security**
   ```bash
   # Create .htaccess for uploads folder
   sudo tee /var/www/html/WheresMyPie/public_html/uploads/.htaccess > /dev/null << 'EOF'
   # Deny PHP file execution
   <FilesMatch "\.php$">
       Require all denied
   </FilesMatch>

   # Allow only image files
   <FilesMatch "\.(jpg|jpeg|png|gif)$">
       Require all granted
   </FilesMatch>
   EOF
   ```

### Step 5️⃣ - Configure Apache

1. **Enable PHP and Rewrite Modules**
   ```bash
   # Enable Apache modules
   sudo a2enmod php7.4
   sudo a2enmod rewrite
   
   # Restart Apache
   sudo systemctl restart apache2
   ```

2. **Configure Virtual Host (Optional)**
   ```bash
   # Create virtual host configuration
   sudo tee /etc/apache2/sites-available/WheresMyPie.conf > /dev/null << 'EOF'
   <VirtualHost *:80>
       DocumentRoot /var/www/html/WheresMyPie/public_html
       ServerName WheresMyPie.local
       
       Alias /WheresMyPie /var/www/html/WheresMyPie/public_html
       
       <Directory "/var/www/html/WheresMyPie/public_html">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   EOF

   # Enable the site
   sudo a2ensite WheresMyPie.conf
   sudo systemctl reload apache2
   ```

## ✅ Verification

### Step 6️⃣ - Test the Installation

1. **Check Web Server**
   ```bash
   # Open in browser:
   http://your-raspberry-pi-ip/WheresMyPie/

   # The site should load and display the homepage
   ```

2. **Test Database Connection**
   ```bash
   # Create a simple test script
   sudo tee /var/www/html/WheresMyPie/test_db.php > /dev/null << 'EOF'
   <?php
   require_once 'public_html/includes/config.php';

   try {
       $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
       echo "Database connection successful!";
   } catch(PDOException $e) {
       echo "Connection failed: " . $e->getMessage();
   }
   ?>
   EOF

   # Test via browser: http://your-raspberry-pi-ip/WheresMyPie/test_db.php
   # Delete the test file after verification
   sudo rm /var/www/html/WheresMyPie/test_db.php
   ```

3. **Test File Upload**
   ```bash
   # Check upload folder permissions
   ls -la /var/www/html/WheresMyPie/public_html/uploads/

   # Should show:
   # drwxr-xr-x www-data www-data uploads
   ```

## 🔧 Troubleshooting

### Common Issues and Solutions

#### Issue 1: Apache Won't Start
```bash
# Check Apache status and logs
sudo systemctl status apache2
sudo journalctl -u apache2

# Common fix: Check configuration syntax
sudo apache2ctl configtest
```

#### Issue 2: PHP Not Working
```bash
# Verify PHP installation
php -v

# Check if PHP modules are loaded
sudo apache2ctl -M | grep php

# Restart Apache after PHP installation
sudo systemctl restart apache2
```

#### Issue 3: Database Connection Failed
```bash
# Check MariaDB status
sudo systemctl status mariadb

# Test database credentials
mysql -u pieuser -p WheresMyPie

# Check config.php settings
sudo nano public_html/includes/config.php
```

#### Issue 4: File Upload Not Working
```bash
# Check upload folder permissions
ls -la public_html/uploads/

# If needed, fix permissions
sudo chown -R www-data:www-data public_html/uploads/
sudo chmod -R 755 public_html/uploads/
```

#### Issue 5: Cannot Access from Network
```bash
# Check Raspberry Pi IP address
hostname -I

# Ensure Apache is listening on all interfaces
sudo netstat -tlnp | grep :80

# Check firewall (if enabled)
sudo ufw status
```

## 🔐 Security Hardening

### Additional Security Steps (Recommended)

#### 1. Configure Firewall
```bash
# Enable UFW firewall
sudo ufw enable

# Allow SSH, HTTP
sudo ufw allow ssh
sudo ufw allow 80/tcp

# Check status
sudo ufw status
```

#### 2. Secure MariaDB
```bash
# Edit MariaDB configuration
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf

# Ensure bind-address is set
bind-address = 127.0.0.1
```

#### 3. Hide Server Information
```bash
# Edit Apache configuration
sudo nano /etc/apache2/conf-available/security.conf

# Set these values:
ServerTokens Prod
ServerSignature Off
```

## 📝 Final Steps

1. **Remove Test Files**: Clean up any test PHP files created during installation
2. **Set Up Backups**: Configure regular database backups
3. **Monitor Logs**: Regularly check Apache and MariaDB logs
4. **System Updates**: Keep the system updated with security patches

## 🎉 Installation Complete!

Your **Where's My Pie?** application should now be accessible at:
- **Direct access**: `http://your-pi-ip/WheresMyPie/`
- **Virtual host**: `http://WheresMyPie.local/` (if configured)

---

**Need help?** Check our [troubleshooting section](#-troubleshooting) above.