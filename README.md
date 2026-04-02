02/04 - 22:06
# OVACS - Online Vehicle Availability Control System

A comprehensive web-based system for managing emergency vehicle availability across multiple stations. Built specifically for ambulance services managing 150+ vehicles across 50+ stations with shift-based operations.

## Features

🚑 **Vehicle Management**
- Real-time status tracking (Available, In Service, Out of Service, Maintenance)
- Support for multiple vehicle types (DCA, RRV)
- Maintenance scheduling and history
- Status change logging

🏥 **Station Management**
- 50+ station locations with capacity management
- Geographic distribution and contact information
- Vehicle assignment tracking

📋 **Shift Management**
- Flexible shift patterns (Day, Night, Split, 24-Hour)
- Staff assignments per station
- Shift handover logs

📊 **Dashboard & Reporting**
- Real-time fleet overview
- Activity monitoring
- Performance analytics
- Maintenance reports

## System Requirements

- **PHP 8.0+** with PDO MySQL extension
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Web Server** (Apache, Nginx, IIS)
- Modern web browser

## Quick Setup

### 1. Database Setup

Run the automated setup script:
```batch
setup-database.bat
```

Or manually create the database:
```sql
mysql -u root -p < database/schema.sql
```

### 2. Configuration

Update database credentials in `includes/database.php`:
```php
private const DB_HOST = 'localhost';
private const DB_NAME = 'ovacs_db';
private const DB_USER = 'your_username';
private const DB_PASS = 'your_password';
```

### 3. Access the System

Visit your OVACS installation:
- Main Dashboard: `http://localhost/OVACS/`
- Vehicle Management: `http://localhost/OVACS/vehicles.php`
- Station Management: `http://localhost/OVACS/stations.php`

### 4. Default Login

- **Username:** admin
- **Password:** admin123

*Change these credentials after first login!*

## Database Schema

### Core Tables
- `vehicles` - 150 ambulance records with status tracking
- `stations` - 50 station locations with capacity info  
- `vehicle_types` - DCA (Double Crewed Ambulance) and RRV (Rapid Response Car)
- `shifts` - Flexible shift pattern management
- `vehicle_status_log` - Complete audit trail of status changes
- `maintenance_records` - Service history and scheduling
- `users` - Role-based access control
- `deployments` - Active operation tracking

### Sample Data Included
- 20 example vehicles (A001-A012, B001-B008)
- 10 sample stations (STN001-STN010)
- Standard shift patterns
- Admin user account

## Key Pages

| Page | Purpose |
|------|---------|
| `index.php` | Main dashboard with fleet overview |
| `vehicles.php` | Vehicle management and status updates |
| `stations.php` | Station management and capacity |
| `database-check.php` | System diagnostics and setup verification |

## Deployment

### For IIS (Windows)
```batch
deploy-to-iis.bat
```

### For GitHub
```batch
push-to-github.bat
```

## Development

Built with:
- **Backend:** PHP 8+ with PDO
- **Database:** MySQL with stored procedures and views
- **Frontend:** HTML5, CSS3 (Grid/Flexbox), Vanilla JavaScript
- **Design:** Mobile-first responsive design
- **Fonts:** Inter (Google Fonts)

## Project Structure

```
phpOVACS/
├── css/style.css           # Main stylesheet
├── database/
│   └── schema.sql          # Database creation script
├── includes/
│   ├── database.php        # Database connection & classes
│   ├── header.php          # Navigation header
│   └── footer.php          # Site footer
├── js/main.js              # JavaScript functionality
├── index.php               # Main dashboard
├── vehicles.php            # Vehicle management
├── database-check.php      # Setup verification
├── setup-database.bat      # Automated DB setup
├── deploy-to-iis.bat      # IIS deployment
└── push-to-github.bat     # GitHub sync
```

## Security Features

- SQL injection protection via prepared statements
- XSS prevention with output escaping
- Role-based access control
- Audit logging for all status changes
- Secure password hashing

## Future Enhancements

- [ ] GPS tracking integration
- [ ] Mobile app for field updates  
- [ ] CAD system integration
- [ ] Advanced reporting dashboard
- [ ] Multi-language support
- [ ] API endpoints for third-party integration

## Support

For setup assistance or feature requests, check:
1. `database-check.php` for system diagnostics
2. Database connection settings in `includes/database.php`
3. PHP error logs for detailed error information

## License

Built for emergency services vehicle management. Customize as needed for your organization.

---

**OVACS** - Keeping emergency vehicles available when they're needed most. 🚑