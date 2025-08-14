@extends('layouts.app')

@section('title', 'TAC-GH | Report: '. $form->title)

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="{{ $form->title }}" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Report: {{ $form->title }}</h5>
                            </div>

                            <!-- Table with stripped rows -->
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Submitted At</th>
                                    <th>Submitter</th>
                                    @foreach($form->fields as $f) <th>{{ $f->label }}</th> @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($form->responses as $key => $resp)
                                    <tr>
                                        <td>{{ ++$key }}</td>
                                        <td>{{ $resp->created_at }}</td>
                                        <td>{{ $resp->submitter_name }} <br> {{ $resp->submitter_email }}</td>
                                        @foreach($form->fields as $f)
                                            @php $val = $resp->values->firstWhere('field_id', $f->id)->value ?? '' @endphp
                                            <td>{{ str_replace('|', ', ', $val) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <!-- End Table with stripped rows -->

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->

    <x-modal />
@endsection


