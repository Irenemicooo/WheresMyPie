# Where's My Pie? Administrator Guide 🔧

## Table of Contents

- [System Configuration](#system-configuration)
  - [Database Management](#database-management)
  - [Directory Structure](#directory-structure)
- [System Maintenance](#system-maintenance)
  - [Regular Tasks](#regular-tasks)
  - [Security Maintenance](#security-maintenance)
  - [Performance Optimization](#performance-optimization)
- [Monitoring & Troubleshooting](#monitoring--troubleshooting)
  - [System Health Checks](#system-health-checks)
  - [Common Issues](#common-issues)
- [Backup & Recovery](#backup--recovery)
  - [Backup Strategy](#backup-strategy)
  - [Recovery Procedures](#recovery-procedures)
- [System Updates](#system-updates)
  - [Update Checklist](#update-checklist)

## System Configuration

### Database Management
1. **Database Connection**
   ```php
   // Configuration in includes/config.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'WheresMyPie');
   define('DB_USER', 'pieuser');
   define('DB_PASS', 'your_password');
   ```

2. **File Upload Settings**
   ```php
   // Upload configuration
   define('UPLOADS_DIR', __DIR__ . '/../uploads/');
   define('MAX_FILE_SIZE', 5242880); // 5MB
   define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
   ```

### Directory Structure
```
WheresMyPie/
├── public_html/          # Web accessible files
│   ├── uploads/          # User uploaded content
│   │   ├── items/       # Item photos
│   │   ├── evidence/    # Claim evidence
│   │   └── profiles/    # Profile photos
│   └── assets/          # Static resources
└── private/             # Server-only files
    └── logs/           # System logs
```

## System Maintenance

### Regular Tasks

1. **Database Backup**
   ```bash
   # Daily backup script
   mysqldump -u pieuser -p WheresMyPie > backup_$(date +%Y%m%d).sql

   # Restore from backup
   mysql -u pieuser -p WheresMyPie < backup_file.sql
   ```

2. **Log Management**
   ```bash
   # Check error logs
   tail -f private/logs/error.log

   # Archive old logs
   mv private/logs/error.log private/logs/error_$(date +%Y%m%d).log
   touch private/logs/error.log
   ```

3. **File System Maintenance**
   ```bash
   # Clean temporary files
   find public_html/uploads/temp -type f -mtime +7 -delete

   # Check storage space
   du -sh public_html/uploads/*
   ```

### Security Maintenance

1. **Permission Verification**
   ```bash
   # Check upload directories
   ls -la public_html/uploads/
   
   # Reset if needed
   chown -R www-data:www-data public_html/uploads/
   chmod -R 755 public_html/uploads/
   ```

2. **SSL Certificate (if using HTTPS)**
   - Monitor certificate expiration
   - Renew certificates when needed
   - Update Apache configuration

### Performance Optimization

1. **Database Optimization**
   ```sql
   -- Analyze tables
   ANALYZE TABLE users, items, claims, chat_messages;
   
   -- Optimize tables
   OPTIMIZE TABLE users, items, claims, chat_messages;
   ```

2. **Cache Management**
   - Monitor cache usage
   - Clear cache when needed
   - Adjust cache settings

## Monitoring & Troubleshooting

### System Health Checks

1. **Service Status**
   ```bash
   # Check web server
   systemctl status apache2
   
   # Check database
   systemctl status mariadb
   ```

2. **Resource Monitoring**
   ```bash
   # Check disk space
   df -h
   
   # Check memory usage
   free -m
   
   # Check CPU usage
   top
   ```

### Common Issues

1. **Upload Issues**
   - Verify directory permissions
   - Check file size limits
   - Validate file types

2. **Database Issues**
   - Check connection settings
   - Verify user permissions
   - Monitor query performance

3. **Performance Issues**
   - Check server load
   - Monitor memory usage
   - Analyze slow queries

## Backup & Recovery

### Backup Strategy

1. **Database Backups**
   - Daily automated backups
   - Weekly full backups
   - Monthly archived backups

2. **File Backups**
   - Regular backup of uploaded files
   - Configuration file backups
   - Log file archives

### Recovery Procedures

1. **Database Recovery**
   ```bash
   # Stop services
   systemctl stop apache2
   
   # Restore database
   mysql -u pieuser -p WheresMyPie < backup.sql
   
   # Start services
   systemctl start apache2
   ```

2. **File Recovery**
   - Restore from backup
   - Verify permissions
   - Check file integrity

## System Updates

### Update Checklist

1. **Before Update**
   - Backup database
   - Backup configuration files
   - Notify users of maintenance

2. **During Update**
   - Apply updates
   - Test functionality
   - Check error logs

3. **After Update**
   - Verify system operation
   - Update documentation
   - Monitor for issues

---

*For technical support, please contact the development team.*
