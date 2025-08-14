{{--@extends("layouts.guest")--}}

    <style>

        .lds-roller {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }
        .lds-roller div {
            animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
            transform-origin: 40px 40px;
        }
        .lds-roller div:after {
            content: " ";
            display: block;
            position: absolute;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #fff;
            margin: -4px 0 0 -4px;
        }
        .lds-roller div:nth-child(1) {
            animation-delay: -0.036s;
        }
        .lds-roller div:nth-child(1):after {
            top: 63px;
            left: 63px;
        }
        .lds-roller div:nth-child(2) {
            animation-delay: -0.072s;
        }
        .lds-roller div:nth-child(2):after {
            top: 68px;
            left: 56px;
        }
        .lds-roller div:nth-child(3) {
            animation-delay: -0.108s;
        }
        .lds-roller div:nth-child(3):after {
            top: 71px;
            left: 48px;
        }
        .lds-roller div:nth-child(4) {
            animation-delay: -0.144s;
        }
        .lds-roller div:nth-child(4):after {
            top: 72px;
            left: 40px;
        }
        .lds-roller div:nth-child(5) {
            animation-delay: -0.18s;
        }
        .lds-roller div:nth-child(5):after {
            top: 71px;
            left: 32px;
        }
        .lds-roller div:nth-child(6) {
            animation-delay: -0.216s;
        }
        .lds-roller div:nth-child(6):after {
            top: 68px;
            left: 24px;
        }
        .lds-roller div:nth-child(7) {
            animation-delay: -0.252s;
        }
        .lds-roller div:nth-child(7):after {
            top: 63px;
            left: 17px;
        }
        .lds-roller div:nth-child(8) {
            animation-delay: -0.288s;
        }
        .lds-roller div:nth-child(8):after {
            top: 56px;
            left: 12px;
        }
        @keyframes lds-roller {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .overlay {
            position:absolute;
            top:0;
            left:0;
            right:0;
            bottom:0;
            background-color:rgba(0, 0, 0, 0.85);
            z-index:9999;
            color:white;
        }

    </style>

{{--@section('content')--}}
    <section class="content-wrapper">
        {{--@if($api_details)--}}
        {{--<form name='form' action='{{$api_details['base_url']}}' method='post' id="myForm">--}}
        {{--<input type='hidden' name='clientid' value='{{$api_details['client_id']}}'>--}}
        {{--<input type='hidden' name='itemname' value='{{$api_details['item_name']}}'>--}}
        {{--<input type='hidden' name='clientref' value='{{$api_details['client_ref']}}'>--}}
        {{--<input type='hidden' name='clientsecret' value='{{$api_details['client_secret']}}'>--}}
        {{--<input type='hidden' name='returnurl' value='{{$api_details['return_url']}}'>--}}
        {{--<input type='hidden' name='amount' value='{{$api_details['payment_amount']}}'>--}}
        {{--<input type='hidden' name='securehash' value='{{$api_details['hash_string']}}'>--}}
        {{--</form>--}}
        {{--@endif--}}
        {{--</div>--}}
        {{--</div>--}}
        @php
            echo '<form action="'. $baseurl .'" method="post" id="hidden_form">' . implode('', $args_array) . '</form>';
            echo '<script type="text/javascript">document.getElementById("hidden_form").submit();</script>';
        @endphp
        {{--@endsection--}}
    </section>
    <div class="overlay">
        <div  style="display:table;text-align: center;margin:0 auto;position: relative;height:100%;" >
            <div style="display:table-cell;vertical-align: middle;">
                <div style="display:block;font-size:20pt;"> Connecting to payment site...</div>
                <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
            </div>
        </div>
    </div>
{{--@endsection--}}

{{--    <script >--}}
{{--        document.getElementById('myForm').submit();--}}
{{--    </script>--}}

