@php use Illuminate\Support\Facades\DB; @endphp
@extends('layouts.app')

@section('title', 'TAC-GH | Roles')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Roles" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Roles</h5>
                                <x-button
                                    type="button"
                                    icon="bi bi-plus-lg"
                                    class="btn-primary rounded-pill"
                                    title="Add New Role"
                                    name="New Role"
                                    data-bs-size="" {{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
                                    data-bs-toggle="modal"
                                    data-bs-target="#exampleModal"
                                    data-bs-title="Add New User"
                                    data-bs-url="/execute_form/create/role"
                                />
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th class="no-sort">#</th>
                                        <th>Name</th>
                                        <th>Permissions</th>
                                        <th class="no-sort">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $key => $role)
                                        @php
                                            $permissions = DB::table('role_has_permissions')->where('role_id', $role->id)->pluck('permission_id')->toArray();
                                        @endphp
                                        <tr class="role_{{ $role->id }}">
                                            <td style="width: 50px">{{ ++$key }}</td>
                                            <td>{{ $role->name }}</td>
                                            <td>
                                                @forelse($permissions as $permission)
                                                    <span class="badge rounded-pill bg-dark">{{ get_permission_name($permission) }}</span>
{{--                                                    <button class="btn btn-secondary btn-round btn-sm mx-2"></button>--}}
                                                    {{--                                                        <span class="badge rounded-pill text-bg-dark"></span>--}}
                                                @empty
                                                    {{ null }}
                                                @endforelse
                                            </td>
                                            <td style="width: 220px">
                                                <x-button
                                                    type='button'
                                                    class="btn-info btn-sm"
                                                    icon="fas fa-angle-double-down"
                                                    name="Permissions"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal"
                                                    data-bs-title="Assign Permissions to {{ $role->name }}"
                                                    data-bs-url="/execute_form/view/assign_permissions/{{ $role->id }}"
                                                    data-bs-size=""
                                                    title="Assign Permissions"
                                                    style="padding: 6px 10px 6px 10px"
                                                />
                                                <x-button
                                                    type='button'
                                                    class="btn-icon btn-primary btn-sm"
                                                    icon="bi bi-pencil-square"
                                                    name=""
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal"
                                                    data-bs-title="Edit Role"
                                                    data-bs-url="/execute_form/edit/role/{{ $role->id }}"
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
                                                        {{ $role->id }},
                                                        'role',
                                                        '/execute_form/delete/role/{{ $role->id }}'
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
