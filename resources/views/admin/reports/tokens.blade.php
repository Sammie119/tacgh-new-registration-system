@extends('layouts.app')

@section('title', 'TAC-GH | Login Tokens')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Login Tokens" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Login Tokens</h5>
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Bordered Tabs Justified -->
                            <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="tokenTabs" role="tablist">
                                <li class="nav-item flex-fill" role="presentation">
                                    <button class="nav-link w-100 {{ $active_tab === 'individual' ? 'active' : '' }}" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual-tokens" type="button" role="tab" aria-controls="individual-tokens" aria-selected="{{ $active_tab === 'individual' ? 'true' : 'false' }}">Individual</button>
                                </li>
                                <li class="nav-item flex-fill" role="presentation">
                                    <button class="nav-link w-100 {{ $active_tab === 'batch' ? 'active' : '' }}" id="batch-tab" data-bs-toggle="tab" data-bs-target="#batch-tokens" type="button" role="tab" aria-controls="batch-tokens" aria-selected="{{ $active_tab === 'batch' ? 'true' : 'false' }}">Batch Coordinators</button>
                                </li>
                                <li class="nav-item flex-fill" role="presentation">
                                    <button class="nav-link w-100 {{ $active_tab === 'member' ? 'active' : '' }}" id="member-tab" data-bs-toggle="tab" data-bs-target="#member-tokens" type="button" role="tab" aria-controls="member-tokens" aria-selected="{{ $active_tab === 'member' ? 'true' : 'false' }}">Batch Members</button>
                                </li>
                            </ul>
                            <div class="tab-content pt-2" id="tokenTabsContent">

                                <div class="tab-pane fade {{ $active_tab === 'individual' ? 'show active' : '' }}" id="individual-tokens" role="tabpanel" aria-labelledby="individual-tab">
                                    <form method="GET" action="{{ route('tokens_report') }}" class="row g-2 mb-3">
                                        <div class="col-auto">
                                            <input type="text" name="individual_search" class="form-control" placeholder="Search by name, phone, email or token" value="{{ $individual_search }}" />
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-primary">Search</button>
                                            @if($individual_search)
                                                <a href="{{ route('tokens_report') }}" class="btn btn-outline-secondary">Clear</a>
                                            @endif
                                        </div>
                                    </form>

                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th class="no-sort">#</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Token</th>
                                            <th>Registered</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($individuals as $key => $registrant)
                                            @php
                                                $registrant_name = strtoupper(trim(($dropdown_names[$registrant->title] ?? '').' '.$registrant->first_name.' '.$registrant->other_names.' '.$registrant->surname));
                                            @endphp
                                            <tr>
                                                <td style="width: 40px">{{ $individuals->firstItem() + $key }}</td>
                                                <td>{{ $registrant_name }}</td>
                                                <td>{{ $registrant->phone_number }}</td>
                                                <td>{{ $registrant->email }}</td>
                                                <td>{{ $registrant->token }}</td>
                                                <td>{{ $registrant->created_at }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="50">No Data Found</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>

                                    {{ $individuals->links() }}
                                </div>

                                <div class="tab-pane fade {{ $active_tab === 'batch' ? 'show active' : '' }}" id="batch-tokens" role="tabpanel" aria-labelledby="batch-tab">
                                    <form method="GET" action="{{ route('tokens_report') }}" class="row g-2 mb-3">
                                        <div class="col-auto">
                                            <input type="text" name="batch_search" class="form-control" placeholder="Search by email, phone, batch no or token" value="{{ $batch_search }}" />
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-primary">Search</button>
                                            @if($batch_search)
                                                <a href="{{ route('tokens_report') }}" class="btn btn-outline-secondary">Clear</a>
                                            @endif
                                        </div>
                                    </form>

                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th class="no-sort">#</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>WhatsApp</th>
                                            <th>Batch No</th>
                                            <th>Token</th>
                                            <th>Members</th>
                                            <th>Uploaded</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($batches as $key => $batch)
                                            <tr>
                                                <td style="width: 40px">{{ $batches->firstItem() + $key }}</td>
                                                <td>{{ $batch->email }}</td>
                                                <td>{{ $batch->phone_number }}</td>
                                                <td>{{ $batch->whatsapp_number }}</td>
                                                <td>{{ $batch->batch_no }}</td>
                                                <td>{{ $batch->token }}</td>
                                                <td>{{ $member_counts[$batch->batch_no] ?? 0 }}</td>
                                                <td>{{ $batch->created_at }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="50">No Data Found</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>

                                    {{ $batches->links() }}
                                </div>

                                <div class="tab-pane fade {{ $active_tab === 'member' ? 'show active' : '' }}" id="member-tokens" role="tabpanel" aria-labelledby="member-tab">
                                    <form method="GET" action="{{ route('tokens_report') }}" class="row g-2 mb-3">
                                        <div class="col-auto">
                                            <input type="text" name="member_search" class="form-control" placeholder="Search by name, phone, email, batch no or token" value="{{ $member_search }}" />
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-primary">Search</button>
                                            @if($member_search)
                                                <a href="{{ route('tokens_report') }}" class="btn btn-outline-secondary">Clear</a>
                                            @endif
                                        </div>
                                    </form>

                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th class="no-sort">#</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Batch No</th>
                                            <th>Token</th>
                                            <th>Registered</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($members as $key => $registrant)
                                            @php
                                                $registrant_name = strtoupper(trim(($dropdown_names[$registrant->title] ?? '').' '.$registrant->first_name.' '.$registrant->other_names.' '.$registrant->surname));
                                            @endphp
                                            <tr>
                                                <td style="width: 40px">{{ $members->firstItem() + $key }}</td>
                                                <td>{{ $registrant_name }}</td>
                                                <td>{{ $registrant->phone_number }}</td>
                                                <td>{{ $registrant->email }}</td>
                                                <td>{{ $registrant->batch_no }}</td>
                                                <td>{{ $registrant->token }}</td>
                                                <td>{{ $registrant->created_at }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="50">No Data Found</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>

                                    {{ $members->links() }}
                                </div>

                            </div><!-- End Bordered Tabs Justified -->

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->
@endsection
