# Scheduling

To use the scheduling you need to run the laravel scheduler, see the laravel documentation about running the scheduler [here](https://laravel.com/docs/10.x/scheduling#running-the-scheduler).

You can configure the frequency in the config file under the `schedule` key. The default is once a day at midnight.

```php
'schedule' => [
    'frequency' => 'daily',
    // 'time' => '03:00',
],
```

Frequency can be any of the methods found [here](https://laravel.com/docs/10.x/scheduling#schedule-frequency-options).

Time then equals whatever you want to pass to that "frequency" method.

## Examples

- Every three hours at the 30th minute
  ```php
  'schedule' => [
      'frequency' => 'everyThreeHours',
      'time' => 30,
  ],
  ```
- Every week on Monday at 8:00
  ```php
  'schedule' => [
      'frequency' => 'weeklyOn',
      'time' => 1, '8:00',
  ],
  ```
- Custom cron schedule
  ```php
  'schedule' => [
      'frequency' => 'cron',
      'time' => '* * * * *',
  ],
  ```
