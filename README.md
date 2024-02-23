# Backup

Welcome to the Backup Addon! This addon supercharges your Statamic experience by providing powerful backup management capabilities. Say goodbye to data loss worries and hello to peace of mind!

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)

## Features

- 📂 **Customizable Storage**: Choose where your backups are stored, whether it's on your local disk or a different storage provider.
- 🔄 **Backup Rotation**: Automatically manage your backups by setting a maximum number to keep. Old backups are deleted to make room for new ones.
- 🔒 **Password Protection**: Keep your backups secure with an optional password. No unauthorized access allowed!
- ⏰ **Scheduled Backups**: Set it and forget it! Schedule backups to run automatically at your preferred frequency.

## Installation

To install this addon, follow these simple steps:

1. **Composer Installation**:

   Run the following composer command in your Statamic project:

   ```bash
   composer require itiden/statamic-backup
   ```

2. **Configuration**:

   Publish the configuration file:

   ```sh
   php artisan vendor:publish --tag="backup-config"
   ```

   Next, configure the addon by editing the `config/backup.php` file. Customize settings such as content path, backup destination, and more.

   Read the full configuration documentation [here](docs/configuration.md)!

3. **Usage**:

   You're all set! Start using the addon to handle your backups effortlessly.

## Commands

Backup your site:

```sh
php artisan statamic:backup
```

Restore your site from an absolute path

```sh
php artisan statamic:backup:restore {path}
```

Clear the temp file directory

```sh
php artisan statamic:backup:clear
```

## Configuration

Read the configuration [docs](docs/configuration.md)!

## Notifications/Events

You can send notifications when a backup is created. Read the notifications [docs](docs/notifications.md)!

## Usage

Using the Statamic Backup Addon is a breeze:

1. Configure your backup settings in `config/backup.php` to match your needs [[docs]](docs/configuration.md).

2. Run the backup command manually or let scheduled backups take care of it automatically.

3. Enjoy the peace of mind knowing your data is backed up, secure, and accessible when you need it.

## Contributing

We welcome contributions from the Statamic community! Whether it's bug fixes, new features, or improvements, please feel free to contribute.

## License

This addon is open-source and available under the [MIT License](LICENSE).
