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

<backup-listing />

@endsection