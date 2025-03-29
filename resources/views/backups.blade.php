@extends('statamic::layout')
@section('title', 'Backups')

@section('content')

@csrf

<itiden-backup :chunk-size="{{ config('backup.chunk_size') }}" />

@endsection