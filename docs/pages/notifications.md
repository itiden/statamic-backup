# Notifications

This package does not provide any notifications out of the box. However, you can easily create your own notifications by listening to the events that are fired during the backup and restore process.

## Available events

- `Itiden\Backup\Events\BackupCreated` - Fires when a backup is successfully created.

  Contains a `backup` property which is the backupDto from the backup that was just created.

- `Itiden\Backup\Events\BackupRestored` - Fired when a backup is succesfully restored.

  Contains a `backup` property which is the backupDto from the backup that was just restored.

- `Itiden\Backup\Events\BackupFailed` - This event is fired when a backup fails.

  Contains a `exception` property which is an instance of `BackupFailedException`, which contains the exception that was thrown during the backup process.

- `Itiden\Backup\Events\RestoreFailed` - This event is fired when a restore fails.

  Contains a `exception` property which is an instance of `RestoreFailedException`, which contains the exception that was thrown during the restore process and the `BackupDto` it tried to restore from.

## How to create a notification

Here's how to create a notification that sends an email when a backup is created.

### Create the notification

To create a notification you can run the following command (or copy our example):

```bash
php artisan make:notification BackupCreatedNotification
```

In the newly created `BackupCreatedNotification` class you can customize how the user get the notification, see the laravel [documentation about notifications](https://laravel.com/docs/10.x/notifications) for more information.

All data you want to have available in the notification should be passed to the constructor. In this example we pass the `BackupDto` to the notification.

Here is how ours looks, after we've changed the `toMail` method to fit our needs:

```php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Itiden\Backup\DataTransferObjects\BackupDto;

class BackupCreated extends Notification
{
    use Queueable;

    public function __construct(
        protected BackupDto $backup
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Site backed up at ' . $this->backup->created_at)
            ->action('see all backups', url('/cp/backups'));
    }
}
```

### Create the listener

Then we need to inform laravel when to send the notification. We do this by creating a listener that listens to the `BackupCreated` event.

You can create a listener by running the following command:

```bash
php artisan make:listener BackupCreatedListener
```

Then you add the logic to the `handle` method in the listener. In this example we'll send an email to all users with the `admin` role.

```php
namespace App\Listeners;

use App\Notifications\BackupCreatedNotification;
use Itiden\Backup\Events\BackupCreated;
use Statamic\Facades\User;

class SendBackupCreatedNotification
{
    public function handle(BackupCreated $event): void
    {
        \Statamic\Facades\User::query()->whereRole('admin')->get()->each(function ($user) use ($event) {
            $user->notify(new BackupCreatedNotification($event->backup));
        });

    }
}
```

### Register the listener

Now all that is left is to register the listener in the `EventServiceProvider`:

```php
# file: app/Providers/EventServiceProvider.php

use Itiden\Backup\Events\BackupCreated;
use App\Listeners\SendBackupCreatedNotification;

...

protected $listen = [
    BackupCreated::class => [
        SendBackupCreatedNotification::class,
    ],
];
```

Now, when a backup is created, all users with the `admin` role will receive an email telling them the site was backedup with a link to the backups page.

## Links

- [Laravel notifications documentation](https://laravel.com/docs/10.x/notifications)
- [Laravel events documentation](https://laravel.com/docs/10.x/events)
- [Laravel listeners documentation](https://laravel.com/docs/10.x/events#defining-listeners)
- [Laravel event service provider documentation](https://laravel.com/docs/10.x/events#registering-events-and-listeners)
