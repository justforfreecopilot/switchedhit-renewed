# 🌐 Hostinger Deployment Guide - SwitchedHit Cricket Management

## Overview
This guide provides step-by-step instructions to deploy the SwitchedHit cricket management application on Hostinger shared hosting with your existing MySQL database.

## 📋 **Prerequisites**

### **Hosting Requirements**
- ✅ Hostinger shared hosting account
- ✅ PHP 7.4+ support (available on Hostinger)
- ✅ MySQL database access
- ✅ File Manager or FTP access
- ✅ Custom domain (optional)

### **Database Information**
- **Host**: `srv1669.hstgr.io`
- **Database**: `u471111749_sh_local`
- **Username**: `u471111749_sh_local`
- **Password**: `Data!9012`

## 🚀 **Deployment Steps**

### **Step 1: Prepare Project Files**

#### **1.1 Clean Project Directory**
On your local machine, create a clean deployment package:

```bash
# Remove development files
rm -rf cypress/
rm -rf node_modules/
rm -rf tmp/
rm -rf .git/
rm cypress.config.js
rm package.json
rm package-lock.json
rm test-runner.*
rm *.md
```

#### **1.2 Create Production Files**
Create necessary production configuration files.

### **Step 2: Create Hostinger Configuration**

#### **2.1 Create .htaccess File**
Create `.htaccess` in your project root:

```apache
# Enable URL rewriting
RewriteEngine On

# Handle Fat-Free Framework routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Hide sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
</IfModule>
```

#### **2.2 Update .env for Production**
Create production `.env` file:

```env
# Database Configuration (Hostinger)
DB_HOST=srv1669.hstgr.io
DB_NAME=u471111749_sh_local
DB_USER=u471111749_sh_local
DB_PASS=Data!9012

# JWT Secret (Generate strong secret)
JWT_SECRET=your_production_jwt_secret_here_make_it_long_and_secure

# Production URL (Update with your domain)
APP_URL=https://yourdomain.com

# Environment
APP_ENV=production
DEBUG=0
```

#### **2.3 Update index.php for Production**
Modify `index.php` for production environment:

```php
<?php

// Production error handling
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// Check for static files before loading F3
$path = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
$file = __DIR__ . $path;
if ($path && file_exists($file) && !is_dir($file)) {
    return false;
}

// Require Composer autoloader
require 'vendor/autoload.php';

// Load environment variables from .env file
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
} else {
    die('Environment configuration not found.');
}

// Create F3 instance
$f3 = Base::instance();

// Set paths
$f3->set('AUTOLOAD', 'app/');
$f3->set('UI', 'ui/');

// Set configuration
$f3->set('JWT_SECRET', $env['JWT_SECRET']);
$f3->set('DEBUG', isset($env['DEBUG']) ? (int)$env['DEBUG'] : 0);

// Production error handling
$f3->set('ONERROR', function($f3) {
    $error = $f3->get('ERROR');
    
    // Log error (create logs directory if needed)
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    $logFile = 'logs/error_' . date('Y-m-d') . '.log';
    $errorMsg = date('[Y-m-d H:i:s] ') . $error['text'] . ' in ' . $error['trace'][0]['file'] . ':' . $error['trace'][0]['line'] . PHP_EOL;
    file_put_contents($logFile, $errorMsg, FILE_APPEND | LOCK_EX);
    
    // Show user-friendly error page
    if ($f3->get('DEBUG')) {
        echo 'Error: ' . $error['text'];
    } else {
        echo 'Sorry, something went wrong. Please try again later.';
    }
});

// Database connection with error handling
try {
    $db = new DB\\SQL(
        'mysql:host=' . $env['DB_HOST'] . ';dbname=' . $env['DB_NAME'],
        $env['DB_USER'],
        $env['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    $f3->set('DB', $db);
} catch (Exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please contact support.');
}

// Your existing routes here...
// (Keep all your existing route definitions)

// Run the application
try {
    $f3->run();
} catch (Exception $e) {
    error_log('Application error: ' . $e->getMessage());
    echo 'Application error occurred. Please contact support.';
}
?>
```

### **Step 3: Upload to Hostinger**

#### **3.1 Using File Manager**
1. **Login to Hostinger hPanel**
2. **Open File Manager**
3. **Navigate to `public_html`** directory
4. **Upload your project files**:
   - Upload all PHP files (`index.php`, `.htaccess`, `.env`)
   - Upload `app/` directory (controllers and models)
   - Upload `ui/` directory (templates)
   - Upload `assets/` directory (CSS, JS, images)
   - Upload `vendor/` directory (Composer dependencies)
   - Upload `db/` directory (database schema)

#### **3.2 Using FTP (Alternative)**
```bash
# Using FTP client or command line
ftp your-hostname
cd public_html
put -r * .
```

#### **3.3 Set File Permissions**
Set proper permissions for security:
- **Files**: 644 (`-rw-r--r--`)
- **Directories**: 755 (`drwxr-xr-x`)
- **Special**: Make sure `.env` is not publicly accessible

### **Step 4: Database Setup**

#### **4.1 Import Database Schema**
Using Hostinger's phpMyAdmin:

1. **Access phpMyAdmin** from hPanel
2. **Select your database**: `u471111749_sh_local`
3. **Import schema**:
   - Click "Import" tab
   - Choose `db/schema.sql`
   - Execute import

#### **4.2 Verify Database Tables**
Ensure these tables are created:
- `users`
- `teams`  
- `players`

#### **4.3 Test Database Connection**
Create a test file `test-db.php`:

```php
<?php
require 'vendor/autoload.php';

// Load .env
$lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    if (strpos($line, '#') === 0) continue;
    list($key, $value) = explode('=', $line, 2);
    $env[trim($key)] = trim($value);
}

try {
    $db = new PDO(
        'mysql:host=' . $env['DB_HOST'] . ';dbname=' . $env['DB_NAME'],
        $env['DB_USER'],
        $env['DB_PASS']
    );
    echo "✅ Database connection successful!";
    
    // Test table existence
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<br>📋 Tables found: " . implode(', ', $tables);
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
?>
```

Visit `yourdomain.com/test-db.php` to verify connection.

### **Step 5: Domain Configuration**

#### **5.1 Custom Domain Setup**
If using a custom domain:

1. **Point domain to Hostinger**:
   - Update nameservers to Hostinger's
   - Or add A record pointing to your server IP

2. **Update .env**:
   ```env
   APP_URL=https://yourdomain.com
   ```

3. **SSL Certificate**:
   - Enable SSL in Hostinger hPanel
   - Force HTTPS redirect in `.htaccess`

#### **5.2 Subdomain Setup**
If using subdomain (e.g., `cricket.yourdomain.com`):

1. **Create subdomain** in hPanel
2. **Point to public_html** or subfolder
3. **Update APP_URL** accordingly

### **Step 6: Production Optimizations**

#### **6.1 Enable Caching**
Add to `.htaccess`:

```apache
# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

#### **6.2 Security Enhancements**
```apache
# Disable server signature
ServerSignature Off

# Prevent access to sensitive files
<FilesMatch "\\.(htaccess|htpasswd|ini|log|sh|inc|bak)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

#### **6.3 PHP Configuration**
Create `php.ini` if needed:

```ini
; Production settings
display_errors = Off
log_errors = On
error_log = logs/php_error.log
max_execution_time = 300
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
```

## 🧪 **Testing Deployment**

### **Functional Tests**
1. **Visit your domain**: `https://yourdomain.com`
2. **Test registration**: Create new cricket team
3. **Test login**: Login with created account
4. **Test squad management**: Add/view players
5. **Test lineup**: Batting and bowling orders
6. **Test navigation**: All menu items work

### **Performance Tests**
1. **Page load speed**: < 3 seconds
2. **Database queries**: Monitor response times
3. **Asset loading**: CSS/JS files load correctly

### **Security Tests**
1. **Direct file access**: Try accessing `.env` directly (should be blocked)
2. **SQL injection**: Test with malicious inputs
3. **XSS protection**: Test form inputs

## 🔧 **Troubleshooting**

### **Common Issues**

#### **Issue: 500 Internal Server Error**
**Solutions**:
1. Check file permissions (755 for directories, 644 for files)
2. Verify `.htaccess` syntax
3. Check PHP error logs in `logs/` directory
4. Ensure all dependencies in `vendor/` are uploaded

#### **Issue: Database Connection Failed**
**Solutions**:
1. Verify database credentials in `.env`
2. Check if database server allows external connections
3. Test with `test-db.php` script
4. Contact Hostinger support for database issues

#### **Issue: Assets Not Loading**
**Solutions**:
1. Check file paths in HTML templates
2. Verify `assets/` directory uploaded correctly
3. Check `.htaccess` rewrite rules
4. Clear browser cache

#### **Issue: Routes Not Working**
**Solutions**:
1. Verify `.htaccess` file exists and is readable
2. Check if mod_rewrite is enabled (usually enabled on Hostinger)
3. Ensure `index.php` contains all route definitions

### **Monitoring & Logs**
1. **Check PHP errors**: `logs/php_error.log`
2. **Check application logs**: `logs/error_YYYY-MM-DD.log`
3. **Monitor database**: Use phpMyAdmin
4. **Check Hostinger logs**: Available in hPanel

## 📞 **Support Resources**

### **Hostinger Support**
- **Documentation**: [Hostinger Help Center](https://support.hostinger.com)
- **Live Chat**: Available 24/7 in hPanel
- **PHP Support**: Most shared hosting supports PHP 7.4+

### **Application Support**
- **Error Logs**: Check `logs/` directory
- **Database Issues**: Use phpMyAdmin for direct access
- **Performance**: Use Hostinger's built-in analytics

## 🚀 **Go Live Checklist**

### **Pre-Launch**
- [ ] All files uploaded to `public_html`
- [ ] Database schema imported successfully  
- [ ] `.env` configured with production values
- [ ] `.htaccess` configured for security and performance
- [ ] SSL certificate enabled
- [ ] Domain/subdomain configured
- [ ] Error logging enabled

### **Post-Launch**
- [ ] Test all major functions (register, login, squad management)
- [ ] Verify cricket lineup features work
- [ ] Check page load speeds
- [ ] Monitor error logs for issues
- [ ] Set up regular database backups
- [ ] Document any custom configurations

### **Ongoing Maintenance**
- [ ] Regular security updates
- [ ] Database optimization
- [ ] Performance monitoring
- [ ] User feedback collection
- [ ] Feature updates and improvements

---

## 🎯 **Final Notes**

### **Production URL Structure**
```
https://yourdomain.com/             # Dashboard
https://yourdomain.com/register     # Registration
https://yourdomain.com/login        # Login  
https://yourdomain.com/players      # Squad Management
https://yourdomain.com/team-composition # Lineup Management
```

### **Backup Strategy**
1. **Database**: Export via phpMyAdmin weekly
2. **Files**: Download entire `public_html` monthly
3. **Configuration**: Keep copies of `.env` and `.htaccess`

### **Performance Expectations**
- **Page Load**: 2-4 seconds (depending on Hostinger plan)
- **Concurrent Users**: 50-100 (shared hosting limits)
- **Database**: MySQL with standard shared hosting performance

**Your SwitchedHit cricket management application is now ready for production on Hostinger! 🏏🚀**

---

*Last Updated: September 24, 2025*  
*Hosting Provider: Hostinger Shared Hosting*  
*Application: SwitchedHit Cricket Management System*