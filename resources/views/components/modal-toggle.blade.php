<div class="modal fade" id="exampleModalToggle2" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="exampleModalToggleLabel2" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 modal-title2" id="exampleModalToggleLabel2"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- @include('forms.create') --}}
            </div>
        </div>
    </div>
</div>


<script>
    const exampleModalToggle = document.getElementById('exampleModalToggle2')
    if (exampleModalToggle) {
        exampleModalToggle.addEventListener('show.bs.modal', event => {
            // Button that triggered the modal
            const button = event.relatedTarget

            // Ajax Request
            const url = button.getAttribute('data-bs-url2')
            $.get(`${url}`, function(result) {
                $(".modal-body").html(result);
            })

            // Set Modal title
            const recipient = button.getAttribute('data-bs-title2')
            const modalTitle = exampleModalToggle.querySelector('.modal-title')
            modalTitle.textContent = `${recipient}`

            // Set Modal Size
            const size = button.getAttribute('data-bs-size2') //modal-xl, modal-lg
            const setSize = exampleModalToggle.querySelector(".modal-dialog");
            setSize.setAttribute("class", `modal-dialog modal-dialog-centered modal-dialog-scrollable ${size}`);
        })
    }
</script>
