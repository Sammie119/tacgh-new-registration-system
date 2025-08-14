{{--success--}}
@if (Session::has('success'))
    <script>
        $.notify({
            icon: 'icon-bell',
            title: 'Successful',
            message: "{!! Session::get('success') !!}",
        },{
            type: 'success',
            placement: {
                from: "top",
                align: "right"
            },
            time: 1000,
        });
    </script>
@endif

{{--error--}}
@if (Session::has('error'))
    <script>
        $.notify({
            icon: 'icon-bell',
            title: 'Error',
            message: "{!! Session::get('error') !!}",
        },{
            type: 'danger',
            placement: {
                from: "top",
                align: "right"
            },
            time: 1000,
        });
    </script>
@endif
{{--$.notify({--}}
{{--    icon: 'icon-bell',--}}
{{--    title: 'Error',--}}
{{--    message: 'Error Message Here',--}}
{{--},{--}}
{{--    type: 'danger',--}}
{{--    placement: {--}}
{{--        from: "top",--}}
{{--        align: "right"--}}
{{--    },--}}
{{--    time: 1000,--}}
{{--});--}}

{{--warning--}}
@if (Session::has('warning'))
    <script>
        $.notify({
            icon: 'icon-bell',
            title: 'Warning',
            message: "{!! Session::get('warning') !!}",
        },{
            type: 'warning',
            placement: {
                from: "top",
                align: "right"
            },
            time: 1000,
        });
    </script>
@endif
{{--$.notify({--}}
{{--    icon: 'icon-bell',--}}
{{--    title: 'Warning',--}}
{{--    message: 'Warning Message Here',--}}
{{--},{--}}
{{--    type: 'warning',--}}
{{--    placement: {--}}
{{--        from: "top",--}}
{{--        align: "right"--}}
{{--    },--}}
{{--    time: 1000,--}}
{{--});--}}

{{--information--}}
@if (Session::has('info'))
    <script>
        $.notify({
            icon: 'icon-bell',
            title: 'Information',
            message: "{!! Session::get('info') !!}",
        },{
            type: 'info',
            placement: {
                from: "top",
                align: "right"
            },
            time: 1000,
        });
    </script>
@endif
{{--$.notify({--}}
{{--    icon: 'icon-bell',--}}
{{--    title: 'Information',--}}
{{--    message: 'Information Message Here',--}}
{{--},{--}}
{{--    type: 'info',--}}
{{--    placement: {--}}
{{--        from: "top",--}}
{{--        align: "right"--}}
{{--    },--}}
{{--    time: 1000,--}}
{{--});--}}

{{--Sweetalert--}}

@if (Session::has('warning_sweet'))
    <script>
        swal("Good job!", "{!! Session::get('warning_sweet') !!}", {
            icon: "warning",
            buttons: {
                confirm: {
                    className: "btn btn-warning",
                },
            },
        });
    </script>
@endif

@if (!empty($errors->all()))
    <script>
        swal("Input Error!", "{!! Session::get('error_sweet') !!}", {
            icon: "error",
            buttons: {
                confirm: {
                    className: "btn btn-danger",
                },
            },
        });
    </script>
@endif

@if (Session::has('success_sweet'))
    <script>
        swal("Good job!", "{!! Session::get('success_sweet') !!}", {
            icon: "success",
            buttons: {
                confirm: {
                    className: "btn btn-success",
                },
            },
        });
    </script>
@endif

@if (Session::has('info_sweet'))
    <script>
        swal("Good job!", "{!! Session::get('info_sweet') !!}", {
            icon: "info",
            buttons: {
                confirm: {
                    className: "btn btn-info",
                },
            },
        });
    </script>
@endif

@if (Session::has('error_sweet'))
    <script>
        swal("Error!", "{!! Session::get('error_sweet') !!}", {
            icon: "error",
            buttons: {
                confirm: {
                    className: "btn btn-info",
                },
            },
        });
    </script>
@endif

{{--
// Delete Sweet Alert
$("#alert_demo_7").click(function (e) {
    swal({
        title: "Delete Confirmation?",
        text: "You won't be able to revert this Action!",
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
            swal({
                title: "Deleted!",
                text: "Record deleted Successfully.",
                type: "success",
                buttons: {
                    confirm: {
                        className: "btn btn-success",
                    },
                },
            });
        } else {
            swal.close();
        }
    });
});

--}}


