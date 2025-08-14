@php use Illuminate\Support\Facades\DB; @endphp
@extends('layouts.app')

@section('title', 'TAC-GH | Dropdowns')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Dropdowns" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Dropdowns</h5>
                                <x-button
                                    type="button"
                                    icon="bi bi-plus-lg"
                                    class="btn-primary rounded-pill"
                                    title="Add New Record"
                                    name="New Category"
                                    data-bs-size="" {{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
                                    data-bs-toggle="modal"
                                    data-bs-target="#exampleModal"
                                    data-bs-title="Add New Category"
                                    data-bs-url="/execute_form/create/dropdown_category"
                                />
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th class="no-sort">#</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Status</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($categories as $key => $category)
                                    <tr class="category_{{ $category->id }}">
                                        <td style="width: 40px">{{ ++$key }}</td>
                                        <td>{{ $category->look_up_name }}</td>
                                        <td>{{ $category->lookup_short_code }}</td>
                                        <td>{!! get_active_flag($category->active_flag) !!}</td>
                                        <td style="width: 200px">
                                            <x-button
                                                type='button'
                                                class="btn-info btn-sm"
                                                icon="bi b-chevron-double-down"
                                                name="Dropdowns"
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Add Dropdowns to {{ $category->category_name }}"
                                                data-bs-url="/execute_form/view/dropdown/{{ $category->id }}"
                                                data-bs-size="modal-lg"
                                                title="Dropdowns"
                                                style="padding: 6px 10px 6px 10px"
                                            />
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-primary btn-sm"
                                                icon="bi bi-pencil-square"
                                                name=""
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Edit Permission"
                                                data-bs-url="/execute_form/edit/dropdown_category/{{ $category->id }}"
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
                                                    {{ $category->id }},
                                                    'category',
                                                    '/execute_form/delete/dropdown_category/{{ $category->id }}'
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
