@extends('layouts.app')

@section('title', 'TAC-GH | Permissions')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Permissions" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Permissions</h5>
                                <x-button
                                    type="button"
                                    icon="bi bi-plus-lg"
                                    class="btn-primary rounded-pill"
                                    title="Add New Record"
                                    name="New Permission"
                                    data-bs-size="" {{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
                                    data-bs-toggle="modal"
                                    data-bs-target="#exampleModal"
                                    data-bs-title="Add New Permission"
                                    data-bs-url="/execute_form/create/permission"
                                />
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th class="no-sort">#</th>
                                    <th>Name</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($permissions as $key => $permission)
                                    <tr class="permission_{{ $permission->id }}">
                                        <td style="width: 40px">{{ ++$key }}</td>
                                        <td>{{ $permission->name }}</td>
                                        <td style="width: 100px">
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-primary btn-sm"
                                                icon="bi bi-pencil-square"
                                                name=""
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Edit Permission"
                                                data-bs-url="/execute_form/edit/permission/{{ $permission->id }}"
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
                                                    {{ $permission->id }},
                                                    'permission',
                                                    '/execute_form/delete/permission/{{ $permission->id }}'
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
