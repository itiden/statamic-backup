@can('manage backups')
    <div class="card h-full p-4 text-center">
        <h2 class="mb-2 font-bold">
            @if (!$lastBackup)
                <span class="text-3xl text-red-500">{{ __('Not backed up.') }}</span>
            @else
                <span class="text-xl">
                    {{ __('Your site was backed up') }} <span @class([
                        'text-green-500' => $lastBackup->timestamp > now()->subDay()->timestamp,
                        'text-orange' =>
                            $lastBackup->timestamp < now()->subDay()->timestamp &&
                            $lastBackup->timestamp > now()->subWeek()->timestamp,
                        'text-red-500' => $lastBackup->timestamp < now()->subWeek()->timestamp,
                    ])>
                        {{ Carbon\Carbon::createFromTimestamp($lastBackup->timestamp)->diffForHumans() }}
                    </span>
                </span>
            @endif
        </h2>

        @if ($lastBackup)
            <a href="{{ cp_route('itiden.backup.index') }}" class="hover:text-blue py-1 text-sm">{{ __('View Backups') }}</a>
        @else
            @can('create backups')
                <a href={{ cp_route('itiden.backup.index') }}>
                    {{ __('You should create a backup now.') }}
                </a>
            @endcan
        @endif
    </div>
@endcan
