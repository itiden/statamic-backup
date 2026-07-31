# Events

The addon comes with a couple of events you can listen to, an example is sending notifications or uploading backups to an extra location.

## Available events

### `Itiden\Backup\Events\BackupCreated`

Fires when a backup is successfully created.

Contains a `backup` property which is the `BackupDto` from the backup that was just created.

### `Itiden\Backup\Events\BackupFailed`

This event is fired when a backup fails.

Contains a `exception` property which is an instance of `BackupFailed`, which contains the exception that was thrown during the backup process.


### `Itiden\Backup\Events\BackupRestored`

Fires when a backup is succesfully restored.

Contains a `backup` property which is the `BackupDto` from the backup that was just restored.

### `Itiden\Backup\Events\RestoreFailed`

This event is fired when a restore fails.

Contains a `exception` property which is an instance of `RestoreFailedException`, which contains the exception that was thrown during the restore process and the `BackupDto` it tried to restore from.
