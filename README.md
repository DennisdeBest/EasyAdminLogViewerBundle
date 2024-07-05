# EasyAdmin Log Viewer Bundle

A Symfony bundle that provides a log viewer interface for EasyAdmin 4, compatible with Symfony 7 and PHP 8.3.

## Features

- View log files directly from your EasyAdmin dashboard
- Download log files
- Delete log files
- Filter log entries by level and type
- Configurable route prefix

## Requirements

- PHP 8.3+
- Symfony 7+
- EasyAdmin 4

## Installation

Use Composer to install the bundle:

```bash
composer require codebuds/easyadmin-log-viewer-bundle
```

## Configuration

Add the bundle to your `config/bundles.php`:

```php
return [
    // ...
    EasyAdminLogViewerBundle::class => ['all' => true],
];
```

Create a configuration file `config/packages/easy_admin_log_viewer.yaml`:

```yaml
easy_admin_log_viewer:
    route_prefix: '/admin'  # Default value, can be customized
    levels:
        - { level: 'INFO', class: 'info' }
        - { level: 'ERROR', class: 'danger' }
        - { level: 'CRITICAL', class: 'danger' }
        - { level: 'DEBUG', class: 'secondary' }
```

## Usage

After installation and configuration, a new "Log Files" menu item will appear in your EasyAdmin dashboard. Click on it to access the log viewer.

### Customizing the Route Prefix

You can customize the route prefix in your configuration:

```yaml
easy_admin_log_viewer:
    route_prefix: '/custom-admin'
```

This will change all log viewer routes to start with `/custom-admin` instead of the default `/admin`.

### Security
Only users with `ROLE_ADMIN` can access the log viewer interface. Make sure to properly secure your admin routes.

### Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

License
