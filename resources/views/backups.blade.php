@extends('statamic::layout')
@section('title', 'Backups')

@section('content')

<div class="flex justify-between">
    <h1 class="mb-6">Backups</h1>
    <form action="{{ cp_route('itiden.backup.create') }}" method="POST">
        @csrf
        <button type="submit" class="btn-primary">Create Backup</button>
    </form>
</div>

@if ($backups->isEmpty())
<div class="card">
    <div class="flex items-center justify-between">
        <h2 class="flex-1">No backups found</h2>
    </div>
</div>
@else
<ul class="card flex flex-col gap-3">
    @foreach($backups as $backup)
    <li class="flex items-center justify-between gap-3">
        <h2 class="flex-1">{{ $backup->name }}</h2>
        <span>{{ $backup->size }} bytes</span>
        <a href="{{ cp_route('itiden.backup.download', ['timestamp' => $backup->timestamp]) }}" class="btn-primary">Download</a>
    </li>
    @endforeach
</ul>
@endif

@endsection