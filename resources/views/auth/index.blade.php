@extends('layouts.app')

@section('title', 'TAC-GH | User Management')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="User Management" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Users List</h5>
                                <x-button
                                    type="button"
                                    icon="bi bi-plus-lg"
                                    class="btn-primary rounded-pill"
                                    title="Add New Record"
                                    name="New User"
                                    data-bs-size="" {{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
                                    data-bs-toggle="modal"
                                    data-bs-target="#exampleModal"
                                    data-bs-title="Add New User"
                                    data-bs-url="/execute_form/create/user"
                                />
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Created at</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $key => $user)
                                    <tr class="user_{{ $user->id }}">
                                        <th scope="row">{{ ++$key }}</th>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{!! get_active_flag($user->active_flag) !!}</td>
                                        <td>{{ $user->created_at->format('d-M-Y') }}</td>
                                        <td style="width: 200px">
                                            <x-button
                                                type='button'
                                                class="btn-info btn-sm"
                                                icon="fas fa-angle-double-down"
                                                name="Assign Role"
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Roles for {{ $user->name }}"
                                                data-bs-url="/execute_form/view/user_roles/{{ $user->id }}"
                                                data-bs-size="modal-lg"
                                                title="Assign Role"
                                                style="padding: 6px 10px 6px 10px"
                                            />
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-primary btn-sm"
                                                icon="bi bi-pencil-square"
                                                name=""
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Edit User"
                                                data-bs-url="/execute_form/edit/user/{{ $user->id }}"
                                                data-bs-size=""
                                                title="Edit"
                                            />
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-danger btn-sm"
                                                icon="bi bi-trash-fill"
                                                name=""
                                                title="Delete"
                                                onclick="deleteFunction(
                                                    {{ $user->id }},
                                                    'user',
                                                    '/execute_form/delete/user/{{ $user->id }}'
                                                )"
                                            />
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <th scope="row" colspan="10">No Record Found</th>
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
