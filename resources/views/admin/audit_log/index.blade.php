@extends('layouts.app')

@section('title', 'TAC-GH | Audit Log')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Audit Log" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Audit Log</h5>
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <form method="GET" action="{{ route('audit_log') }}" class="row g-2 mb-3">
                                <div class="col-auto">
                                    <input
                                        type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Search description"
                                        value="{{ $search }}"
                                    />
                                </div>
                                <div class="col-auto">
                                    <select name="log_name" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach($logNames as $name)
                                            <option value="{{ $name }}" @selected($logName === $name)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    @if($search || $logName)
                                        <a href="{{ route('audit_log') }}" class="btn btn-outline-secondary">Clear</a>
                                    @endif
                                </div>
                            </form>

                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Subject</th>
                                    <th>Details</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($activities as $activity)
                                    <tr>
                                        <td style="white-space: nowrap">{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $activity->causer?->name ?? 'System' }}</td>
                                        <td><span class="badge bg-secondary">{{ $activity->log_name }}</span></td>
                                        <td>{{ $activity->description }}</td>
                                        <td>{{ $activity->subject_type ? class_basename($activity->subject_type).' #'.$activity->subject_id : '' }}</td>
                                        <td>
                                            @if($activity->properties->isNotEmpty())
                                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($activity->properties->toJson(), 80) }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">No Activity Found</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>

                            {{ $activities->links() }}

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->
@endsection
