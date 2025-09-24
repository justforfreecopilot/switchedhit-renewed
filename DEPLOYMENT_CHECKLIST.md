# 🚀 SwitchedHit Hostinger Deployment Checklist

## Pre-Deployment Preparation

### Local Environment
- [ ] Test application locally with `php -S localhost:8080`
- [ ] Verify all cricket features work (registration, squad, lineup)
- [ ] Run database schema and confirm 15-player generation
- [ ] Test with production database credentials
- [ ] Remove development files (cypress/, node_modules/, .git/)

### File Preparation
- [ ] Create `.htaccess` file (provided in project)
- [ ] Update `.env` with production values
- [ ] Set `DEBUG=0` in `.env`
- [ ] Generate secure `JWT_SECRET` (minimum 32 characters)
- [ ] Update `APP_URL` to your domain
- [ ] Verify `composer install` completed successfully

## Hostinger Account Setup

### Hosting Account
- [ ] Hostinger shared hosting account active
- [ ] Access to hPanel dashboard
- [ ] File Manager access confirmed
- [ ] Database access verified (phpMyAdmin)

### Domain Configuration
- [ ] Domain pointed to Hostinger (if custom domain)
- [ ] SSL certificate enabled in hPanel
- [ ] Subdomain created (if using subdomain)

## File Upload Process

### Using Hostinger File Manager
- [ ] Login to hPanel → File Manager
- [ ] Navigate to `public_html` directory
- [ ] Upload core files:
  - [ ] `index.php`
  - [ ] `.htaccess`
  - [ ] `.env` (with production values)
  - [ ] `composer.json`
  - [ ] `composer.lock`

- [ ] Upload directories:
  - [ ] `app/` (controllers and models)
  - [ ] `ui/` (HTML templates)
  - [ ] `assets/` (CSS, JS, images)
  - [ ] `vendor/` (Composer dependencies)
  - [ ] `db/` (database schema)

### File Permissions
- [ ] Set directories to 755 permissions
- [ ] Set files to 644 permissions
- [ ] Verify `.env` is not publicly accessible
- [ ] Create `logs/` directory with 755 permissions

## Database Setup

### Schema Import
- [ ] Access phpMyAdmin from hPanel
- [ ] Select database: `u471111749_sh_local`
- [ ] Import `db/schema.sql`
- [ ] Verify tables created: `users`, `teams`, `players`
- [ ] Check for any import errors

### Database Testing
- [ ] Create test connection script
- [ ] Verify connection to `srv1669.hstgr.io`
- [ ] Test basic queries (SELECT, INSERT)
- [ ] Remove test files after verification

## Application Testing

### Basic Functions
- [ ] Visit homepage: loads without errors
- [ ] Registration page: `/register` works
- [ ] User can register new cricket team
- [ ] Login functionality works
- [ ] Dashboard displays after login

### Cricket Features
- [ ] Squad shows 15 players after registration
- [ ] Player list page displays cricket positions
- [ ] Batting order drag-and-drop works
- [ ] Bowling order respects cricket rules (max 4 overs, no consecutive)
- [ ] Save functionality works for lineups
- [ ] Navigation menu works across all pages

### Performance Testing
- [ ] Page load times under 5 seconds
- [ ] Assets (CSS/JS) load properly
- [ ] Images display correctly
- [ ] Mobile responsiveness works

## Security Verification

### File Access Protection
- [ ] Cannot access `.env` directly in browser
- [ ] Cannot access `composer.json` directly
- [ ] Cannot browse directories (no index listing)
- [ ] Error pages don't reveal sensitive information

### Application Security
- [ ] SQL injection protection works
- [ ] XSS protection enabled
- [ ] CSRF protection for forms
- [ ] Session management secure
- [ ] JWT tokens work properly

## Production Configuration

### Environment Settings
- [ ] `DEBUG=0` in production `.env`
- [ ] Error logging enabled (check `logs/` directory)
- [ ] Production `JWT_SECRET` set (different from development)
- [ ] Correct `APP_URL` in configuration

### Performance Optimization
- [ ] Static asset caching enabled (.htaccess)
- [ ] Compression enabled for text files
- [ ] Browser caching headers set
- [ ] Database queries optimized

## Post-Launch Monitoring

### Initial 24 Hours
- [ ] Monitor error logs in `logs/` directory
- [ ] Check application performance
- [ ] Test user registration and login flows
- [ ] Verify database connections stable
- [ ] Monitor server resource usage

### Ongoing Maintenance
- [ ] Set up regular database backups
- [ ] Monitor disk space usage
- [ ] Check for PHP/security updates
- [ ] Review error logs weekly
- [ ] Performance monitoring setup

## Troubleshooting Quick Reference

### Common Issues and Solutions

**500 Internal Server Error**
- Check `.htaccess` syntax
- Verify file permissions (755/644)
- Review PHP error logs
- Ensure all vendor dependencies uploaded

**Database Connection Issues**
- Verify credentials in `.env`
- Test connection with simple PHP script
- Check Hostinger database status
- Ensure database server allows connections

**Assets Not Loading**
- Check file paths in templates
- Verify `.htaccess` rewrite rules
- Clear browser cache
- Check file permissions

**Routes Not Working**
- Verify mod_rewrite enabled (usually yes on Hostinger)
- Check `.htaccess` file exists and readable
- Ensure all routes defined in `index.php`

## Support Contacts

### Hostinger Support
- **24/7 Live Chat**: Available in hPanel
- **Help Center**: support.hostinger.com
- **PHP Documentation**: Hostinger PHP support guides

### Application Support
- **Error Logs**: Check `logs/error_YYYY-MM-DD.log`
- **Database**: Use phpMyAdmin for direct access
- **Performance**: Hostinger analytics in hPanel

## Final Verification URLs

Replace `yourdomain.com` with your actual domain:

- [ ] Homepage: `https://yourdomain.com/`
- [ ] Registration: `https://yourdomain.com/register`
- [ ] Login: `https://yourdomain.com/login`
- [ ] Dashboard: `https://yourdomain.com/dashboard`
- [ ] Squad: `https://yourdomain.com/players`
- [ ] Lineup: `https://yourdomain.com/team-composition`

## Success Criteria

✅ **Deployment Successful When:**
- All URLs load without errors
- User can register and create cricket team
- 15 players generated automatically
- Squad management functions work
- Batting and bowling lineups can be set and saved
- No error messages in logs
- Performance is acceptable (< 5 second load times)

---

**🏏 Your SwitchedHit Cricket Management System is now live on Hostinger!**

*Deployment Date: _____________*  
*Domain: _____________________*  
*Deployed By: ________________*