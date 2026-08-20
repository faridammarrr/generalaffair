@extends('layouts.app')

@section('title', 'General Affair')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">General Affair</p>
            <h1>Workspace operasional GA.</h1>
            <p class="lead">Kelola request, meterai, tagihan, tracking kegiatan, petty cash, dan budget dari satu dashboard.</p>
        </div>

        <a class="button" href="{{ route('request-items.index') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            Buka Dashboard
        </a>
    </section>
@endsection
