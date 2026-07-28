@extends('layouts.app')

@section('title', 'TAC-GH | Notification Delivery')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Notification Delivery" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Notification Delivery</h5>
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <form method="GET" action="{{ route('notification_log') }}" class="row g-2 mb-3">
                                <div class="col-auto">
                                    <select name="channel" class="form-select">
                                        <option value="">All Channels</option>
                                        <option value="sms" @selected($channel === 'sms')>SMS</option>
                                        <option value="whatsapp" @selected($channel === 'whatsapp')>WhatsApp</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="success" @selected($status === 'success')>Success</option>
                                        <option value="failed" @selected($status === 'failed')>Failed</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    @if($channel || $status)
                                        <a href="{{ route('notification_log') }}" class="btn btn-outline-secondary">Clear</a>
                                    @endif
                                </div>
                            </form>

                            <table class="table">
                                <thead>
                                <tr>
                                    <th class="no-sort">#</th>
                                    <th>Recipient</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Registrant</th>
                                    <th>Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($logs as $key => $log)
                                    <tr>
                                        <td style="width: 40px">{{ $logs->firstItem() + $key }}</td>
                                        <td>{{ $log->recipient }}</td>
                                        <td>{{ ucfirst($log->channel) }}</td>
                                        <td>{{ $log->success ? 'Success' : 'Failed' }}</td>
                                        <td>{{ $registrant_names[$log->registrant_id] ?? 'NULL' }}</td>
                                        <td>{{ $log->created_at }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="50">No Data Found</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>

                            {{ $logs->links() }}

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->
@endsection
