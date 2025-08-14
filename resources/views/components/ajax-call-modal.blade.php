@props(['ajax_url', 'form'])

<input type="hidden" value="{{ $ajax_url }}" id="ajax-url">

@push('scripts')
    <script>
        // const inputDisplay = document.getElementById('inputDisplay')
        $("body").on("click",".addRecord", function (){
            var search = $('.record').val();

            const url = $('#ajax-url').val();

            if(search === ''){
                alert('Input Empty!!!');
            }else {
                $.ajax({
                    type:'GET',
                    url:`${url}`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        search
                    },
                    success:function(data) {
                        if(data.record_name === 'No_data'){
                            alert('Student does not Exist!!!');
                        }else{
                            document.querySelector('#contentRecord').insertAdjacentHTML(
                                'beforeend',
                                `@include($form)`
                            );
                        }

                        document.querySelector('.show_data').style.display='none';

                        $('.record').val('');
                        $('.record').focus();

                    }
                });
            }

        });
    </script>
@endpush

<script>
    function removeRow (input) {
        input.parentNode.parentElement.remove()
    }
</script>

