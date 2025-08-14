@extends('layouts.app')

@section('title', 'TAC-GH | Financial Entries')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Financial Entries" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Financial Entries</h5>
                                <x-button
                                    type="button"
                                    icon="bi bi-plus-lg"
                                    class="btn-primary rounded-pill"
                                    title="Add New Record"
                                    name="New Entry"
                                    data-bs-size="modal-lg" {{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
                                    data-bs-toggle="modal"
                                    data-bs-target="#exampleModal"
                                    data-bs-title="Add New Entry"
                                    data-bs-url="/execute_form/create/financial_entry"
                                />
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th class="no-sort">#</th>
                                    <th>Type</th>
                                    <th>Financial Type</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>amount</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($finances as $key => $finance)
                                    <tr class="finance_{{ $finance->id }}">
                                        <td style="width: 40px">{{ ++$key }}</td>
                                        <td>{{ $finance->entry_type }}</td>
                                        <td>{{ get_dropdown_name($finance->transaction_type) }}</td>
                                        <td>{{ $finance->description }}</td>
                                        <td>{{ $finance->transaction_date }}</td>
                                        <td>{{ $finance->amount }}</td>
                                        <td style="width: 100px">
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-primary btn-sm"
                                                icon="bi bi-pencil-square"
                                                name=""
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Edit Event"
                                                data-bs-url="/execute_form/edit/financial_entry/{{ $finance->id }}"
                                                data-bs-size="modal-lg"
                                                title="Edit"
                                            />
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-danger btn-sm"
                                                icon="bi bi-trash-fill"
                                                name=""
                                                title="Delete"
                                                onclick="deleteFunction(
                                                    {{ $finance->id }},
                                                    'finance',
                                                    '/execute_form/delete/financial_entry/{{ $finance->id }}',
                                                    'refresh'
                                                )"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="50">No Data Found</td>
                                    </tr>
                                @endforelse
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

