# Commands

Statamic backup comes with a few commands to help you manage backups from a cli.

| Command                                  | Description                                                  |
| ---------------------------------------- | ------------------------------------------------------------ |
| `php artisan statamic:backup`            | Run the backup process                                       |
| `php artisan statamic:backup:temp-clean` | Clean up leftover temporary backup files, like upload chunks |
| `php artisan statamic:backup:restore`    | Restore your site to chosen backup                           |
