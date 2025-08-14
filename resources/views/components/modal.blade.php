<div class="modal fade" id="exampleModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- @include('forms.create') --}}
            </div>

        </div>
    </div>
</div>

<script>
    const exampleModal = document.getElementById('exampleModal')
    if (exampleModal) {
        exampleModal.addEventListener('show.bs.modal', event => {
            // Button that triggered the modal
            const button = event.relatedTarget

            // Ajax Request
            const url = button.getAttribute('data-bs-url')
            $.get(`${url}`, function(result) {
                $(".modal-body").html(result);
            })

            // Set Modal title
            const recipient = button.getAttribute('data-bs-title')
            const modalTitle = exampleModal.querySelector('.modal-title')
            modalTitle.textContent = `${recipient}`

            // Set Modal Size
            const size = button.getAttribute('data-bs-size') //modal-xl, modal-lg
            const setSize = exampleModal.querySelector(".modal-dialog");
            setSize.setAttribute("class", `modal-dialog modal-dialog-centered modal-dialog-scrollable ${size}`);
        })
    }
</script>

<script>
    function deleteFunction(id, name, url, type = 'delete'){
        const cap_name = name.charAt(0).toUpperCase() + name.slice(1)
        swal({
            title: "Delete Confirmation?",
            text: `${cap_name} deletion is irreversible!!`,
            type: "warning",
            buttons: {
                confirm: {
                    text: "Yes, delete it!",
                    className: "btn btn-success",
                },
                cancel: {
                    visible: true,
                    className: "btn btn-danger",
                },
            },
        }).then((Delete) => {
            if (Delete) {
                $.ajax({
                    url: `${url}`,
                    method: 'get',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id
                    },
                    success: function(data) {
                        if(data === 1) {
                            document.querySelector(`.${name}_${id}`).remove();
                            swal({
                                title: "Deleted!",
                                text: `${cap_name} Record deleted Successfully.`,
                                type: "success",
                                buttons: {
                                    confirm: {
                                        className: "btn btn-success",
                                    },
                                },
                            }).then(Delete => {
                                if(Delete){
                                    if(type === 'refresh'){
                                        history.go(0);
                                    }
                                }
                            });
                        } else {
                            swal("Deletion Error!", `${cap_name} Deletion was Unsuccessful. Please try Again`, {
                                icon: "error",
                                buttons: {
                                    confirm: {
                                        className: "btn btn-danger",
                                    },
                                },
                            });
                        }
                    }
                });

                swal({
                    title: "Deleted!",
                    text: `${cap_name} Record deleted Successfully.`,
                    type: "success",
                    buttons: {
                        confirm: {
                            className: "btn btn-success",
                        },
                    },
                }).then(Delete => {
                    if(Delete){
                        if(type === 'refresh'){
                            history.go(0);
                        }
                    }
                });

            } else {
                swal.close();
            }
        });
    }

   function get_ajax_data(url, selector, show_data, type = 'innerHTML', class_id = null, subject_id = null, header_id = null){
        let value = selector; //document.getElementById('person_type').value;
        // alert(value)
        $.ajax({
            url: `/${url}`,
            method: 'get',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                value, class_id, subject_id, header_id
            },
            success: function(data) {
                // console.log(data);
                if(type === 'innerHTML'){
                    document.querySelector(`#${show_data}`).innerHTML = data;
                } else {
                    document.querySelector(`#${show_data}`).value = data;
                }

            }
        });
    }
</script>
