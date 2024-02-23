@can('manage backups')
    <div class="card h-full p-4 text-center">
        <h2 class="mb-2 font-bold">
            @if (!$lastBackup)
                {{ __('No backups have been created yet.') }}
            @else
                {{ __('Your site was backed up') }} {{ $lastBackup->created_at }}
            @endif
        </h2>

        <div class="text-center text-sm">
            <a href="{{ cp_route('itiden.backup.index') }}" class="hover:text-blue py-1">{{ __('View Backups') }}</a>
        </div>
    </div>
@endcan
