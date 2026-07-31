# Notifications

This code can be used as a starting point for creating/sending notifications when something happens with the backups in your application. See available events [here](../events.md).

## Snippets

```php

// app/Notifications/BackupCreated.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Itiden\Backup\DataTransferObjects\BackupDto;

final class BackupCreated extends Notification
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

```php

// app/Listeners/SendBackupCreatedNotification.php

namespace App\Listeners;

use App\Notifications\BackupCreated as BackupCreatedNotification;
use Itiden\Backup\Events\BackupCreated;
use Statamic\Facades\User;

final class SendBackupCreatedNotification
{
    public function handle(BackupCreated $event): void
    {
        \Statamic\Facades\User::query()->whereRole('admin')->get()->each(function ($user) use ($event) {
            $user->notify(new BackupCreatedNotification($event->backup));
        });

    }
}
```

## Links

- [Laravel notifications documentation](https://laravel.com/docs/10.x/notifications)
- [Laravel events documentation](https://laravel.com/docs/10.x/events)
- [Laravel listeners documentation](https://laravel.com/docs/10.x/events#defining-listeners)
- [Laravel event service provider documentation](https://laravel.com/docs/10.x/events#registering-events-and-listeners)
