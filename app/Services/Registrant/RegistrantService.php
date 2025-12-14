<?php

namespace App\Services\Registrant;


use App\Exports\RegistrationStageExport;
use App\Helpers\PayStackPayment;
use App\Helpers\Utils;
use App\Http\Traits\SMSNotify;
use App\Imports\RegistrationStageImport;
use App\Jobs\SmsNotificationJob;
use App\Jobs\WhatsappNotificationJob;
use App\Models\Admin\Country;
use App\Models\Admin\Event;
use App\Models\Admin\EventFees;
use App\Models\Admin\OnlinePayment;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Pipelines\Registration\ConfirmationPipe;
use App\Pipelines\Registration\PaymentPipe;
use App\Pipelines\Registration\RegistrantPipe;
use App\Pipelines\Registration\RoomAllocationPipe;
use App\Services\Admin\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Maatwebsite\Excel\Facades\Excel;

class RegistrantService
{
    use SMSNotify;

    public function index($event_id)
    {
        $data['registrants'] = RegistrantStage::where(['event_id' => $event_id, 'confirmed' => 'Yes'])->orderBy('id', 'desc')->get();
        return view('admin.registrant.index', $data);
    }
    public function register()
    {
        $data['title'] = Utils::getLookups(22);
        $data['gender'] = Utils::getLookups(2);
        $data['marital_status'] = Utils::getLookups(3);
        $data['profession'] = Utils::getLookups(10);
        $data['position_held'] = Utils::getLookups(5);
        $data['nations'] = Country::orderBy('name', 'asc')->get();
        $data['events'] = Event::where('active_flag', 1)->where('status', '!=', 'Completed')->orderBy('name', 'asc')->get();
        return view('registrant.registration_form', $data);
    }

    public function registrantRegistration(array $data)
    {
        $token = Utils::generateToken(6);

        $results = RegistrantStage::updateOrCreate([
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'phone_number' => $data['phone_number'],
                'event_id' => $data['event_id'],
            ],[
                'title' => $data['title'],
                'first_name' => $data['first_name'],
                'surname' => $data['surname'],
                'other_names' => $data['other_names'],
                'marital_status' => $data['marital_status'],
                'nationality_id' => $data['nationality_id'],
                'whatsapp_number' => $data['whatsapp_number'],
                'email' => $data['email'],
                'address' => $data['address'],
                'position_held' => $data['position_held'],
                'profession' => $data['profession'],
                'residence_country_id' => $data['residence_country_id'],
                'languages_spoken' => $data['languages_spoken'],
                'need_accommodation' => $data['need_accommodation'],
                'emergency_contacts_name' => $data['emergency_contacts_name'],
                'emergency_contacts_relationship' => $data['emergency_contacts_relationship'],
                'emergency_contacts_phone_number' => $data['emergency_contacts_phone_number'],
                'attendance_type' => $data['attendance_type'],
                'disability' => $data['disability'],
                'special_needs' => $data['special_needs'],
                'token' => $token,
            ]);


        if($results){
            $reg_name = event_registrant_name($results->id);
            $event = get_event($results->event_id);

//            dd($event, $event->is_payment_required);

            $event->is_payment_required == "Yes" ?
                $msg = 'Congrats ' . $results->first_name . ' for your interest in '.$event->name.'. Registration is incomplete until full payment of the Event registration fee is made.'."\n". 'Login token : ' . $token :
                $msg = 'Congrats ' . $results->first_name . ' for your interest in '.$event->name.'. Use the details below to complete your Registration process.'."\n". 'Login token : ' . $token;

            WhatsappNotificationJob::dispatch($results->whatsapp_number, $msg);

            if($results->residence_country_id == 64)
                SmsNotificationJob::dispatch($results->phone_number, $msg);
//                $this->sendSms($results->phone_number, $msg);

//            $this->sendWhatsApp($results->whatsapp_number, $msg);

            return redirect(route('registrant_login', absolute: false))->with("success", "Registration Successful!!. Check your SMS/Whatsapp for further instructions.");
        }

        return back()->with('error', 'Role Creation Unsuccessful!!!');
    }

    public function individualRegistrationConfirm(array $data)
    {
        $result = Pipeline::send($data)->through(
            [
                ConfirmationPipe::class,
                RegistrantPipe::class,
                PaymentPipe::class,
            ]
        )->thenReturn();
//dd($result['amount']);
        if($result['amount'] > 0){
            $response = (new PayStackPayment())->initializeTransaction($result);
            return redirect($response['data']['authorization_url']);
        }

        $data_results['registrant'] = $data;
        $data_results['confirmed_registrant'] = Registrant::where('stage_id', $data['id'])->first();

        if( $data_results['confirmed_registrant']->total_fee == 0)
            (new RoomAllocationPipe())->autoRoomAllocation($data_results);

        return back()->with("success", "Registration Confirmation Successful!!!");

    }

    public function exportRegistrationStage()
    {
        return Excel::download(new RegistrationStageExport(), 'registration_template.xlsx');
    }

    public function batchImportRegistration($request){

        $event_id = $request['event_id'];
        $batch_no = date('YmdHis');
        Excel::import(new RegistrationStageImport($event_id, $batch_no), $request->file('file'));

        $token = Utils::generateToken();

        $results = BatchLog::create([
            'batch_no' => $batch_no,
            'event_id' => $request['event_id'],
            'email' => $request['email'],
            'phone_number' => $request['phone_number'],
            'whatsapp_number' => $request['whatsapp_number'],
            'token' => $token,
        ]);

        $event = Event::find($results->event_id);

        $event->is_payment_required == "Yes" ?
            $msg = 'Congrats for your interest in '.$event->name.'. Registration is incomplete until full payment of the Event registration fee is made.'."\n". 'Login token : ' . $token :
            $msg = 'Congrats for your interest in '.$event->name.'. Use the details below to complete your Registration process.'."\n". 'Login token : ' . $token;

        WhatsappNotificationJob::dispatch($results->whatsapp_number, $msg);

        SmsNotificationJob::dispatch($results->phone_number, $msg);

        return redirect(route('registrant_login', absolute: false))->with("success", "Registration Successful!!. Check your SMS/Whatsapp for further instructions.");
    }

    public function registrantLogin(array $data)
    {
//        dd(strlen($data['password']));
        $auth_key = $data['password'];
        if(strlen($data['password']) <= 7){

            $reg = RegistrantStage::where('token', $auth_key)->first();

            if($reg){
                if(Utils::check($reg->phone_number, $data['email']) || Utils::check($reg->email, $data['email'])){

                    session(['registrant' => $reg]);
                    return redirect(route('registrant_page', absolute: false));
                }

                return back()->with('error', "Login Unsuccessful!!!. Try again.");
            }
        }

        $reg = BatchLog::where('token', $auth_key)->first();

        if($reg){
            if(Utils::check($reg->phone_number, $data['email']) || Utils::check($reg->email, $data['email'])){

                session(['registrant' => $reg]);
                return redirect(route('registrant_page_batch', absolute: false));

            }

            return back()->with('error', "Login Unsuccessful!!!. Try again.");
        }

        return back()->with('error', "Login Unsuccessful!!!. Try again.");
    }

    public function individualLogin(array $reference)
    {
        if(!empty(session('registrant'))){
            $data['registrant'] = session('registrant');
            $data['title'] = Utils::getLookups(22);
            $data['gender'] = Utils::getLookups(2);
            $data['marital_status'] = Utils::getLookups(3);
            $data['profession'] = Utils::getLookups(10);
            $data['position_held'] = Utils::getLookups(5);
            $data['nations'] = Country::orderBy('name', 'asc')->get();
            $data['events'] = Event::where('active_flag', 1)->orderBy('name', 'asc')->get();
            $data['accommodation'] = EventFees::selectRaw("id, concat(description, ' - ', 'GHS',fee_amount) as name")->where([
                'fee_type' => 'accommodation',
                'event_id' => $data['registrant']->event_id,
                'active_flag' => 1
            ])->get();
            $data['registration'] = EventFees::selectRaw("id, concat(description, ' - ', 'GHS',fee_amount) as name")->where([
                'fee_type' => 'registration_fee',
                'event_id' =>  $data['registrant']->event_id,
                'active_flag' => 1
            ])->get();
            $data['confirmed_registrant'] = Registrant::where('stage_id', $data['registrant']['id'])->first();
            $data['payments'] = OnlinePayment::where('reg_id', $data['registrant']['id'])->get();

            if(!empty($reference)){

                $response = (new PayStackPayment())->verifyTransaction($reference['reference']);
                if ($response['status'] && $response['data']['status'] === 'success') {
                    $paymentDetails = $response['data'];

                    $count = OnlinePayment::where('payment_token', $paymentDetails['id'])->count();

                    if($count === 0){

                        (new PaymentService())->paymentReceipt($data, $paymentDetails, $response);

                        $total_payment_made = OnlinePayment::where('reg_id', $data['registrant']['id'])->sum('amount_paid');

                        if($total_payment_made >= $data['confirmed_registrant']->total_fee){
                            // Room Allocation Function Here.........
                            (new RoomAllocationPipe())->autoRoomAllocation($data);

                        }
                    }

                    $data['confirmed_registrant'] = Registrant::where('stage_id', $data['registrant']['id'])->first();
                    $data['payments'] = OnlinePayment::where('reg_id', $data['registrant']['id'])->get();
                }
            }

            return view('registrant.individual', $data);
        }

        return redirect(route('registrant_login', absolute: false))->with('error', "Login Unsuccessful!!!. Try again.");
    }

    public function batchLogin(array $reference)
    {
        if(!empty(session('registrant'))){
            $data['get_data'] = session('registrant');
            $data['batch'] = RegistrantStage::where('batch_no', $data['get_data']->batch_no)->get();

            if(!empty($reference)){

//                dd("Welcome to the Batch Registration");

                $response = (new PayStackPayment())->verifyTransaction($reference['reference']);
                if ($response['status'] && $response['data']['status'] === 'success') {
                    $paymentDetails = $response['data'];

                    $count = OnlinePayment::where('payment_token', $paymentDetails['id'])->count();

                    if($count === 0){

                        (new PaymentService())->paymentReceipt($data, $paymentDetails, $response);

//                        dd(session('batch_payment'));
                        $batch_payment = session('batch_payment')['reg'];

                        foreach ($batch_payment as $payment){
                            $data2['confirmed_registrant'] = Registrant::where('stage_id', $payment['registrant_id'])->first();
                            $data2['registrant'] = RegistrantStage::find($payment['registrant_id']);

                            $total_payment_made = OnlinePayment::where('reg_id', $payment['registrant_id'])->sum('amount_paid');

                            if($total_payment_made >= $data2['confirmed_registrant']->total_fee){
                                // Room Allocation Function Here.........
                                (new RoomAllocationPipe())->autoRoomAllocation($data2);

                            }
                        }
//                         $amount_paid = collect($batch_payment)->where('registrant_id', $data['id'])->first();
                    }
                }
            }

            return view('registrant.batch', $data);
        }

        return redirect(route('registrant_login', absolute: false))->with('error', "Login Unsuccessful!!!. Try again.");
    }

    public function batchRegistrationConfirm($id)
    {
        $data['registrant'] = RegistrantStage::find($id);
        $data['title'] = Utils::getLookups(22);
        $data['gender'] = Utils::getLookups(2);
        $data['marital_status'] = Utils::getLookups(3);
        $data['profession'] = Utils::getLookups(10);
        $data['position_held'] = Utils::getLookups(5);
        $data['nations'] = Country::orderBy('name', 'asc')->get();
        $data['events'] = Event::where('active_flag', 1)->orderBy('name', 'asc')->get();
        $data['accommodation'] = EventFees::selectRaw("id, concat(description, ' - ', 'GHS',fee_amount) as name")->where([
            'fee_type' => 'accommodation',
            'event_id' => $data['registrant']->event_id,
            'active_flag' => 1
        ])->get();
        $data['registration'] = EventFees::selectRaw("id, concat(description, ' - ', 'GHS',fee_amount) as name")->where([
            'fee_type' => 'registration_fee',
            'event_id' =>  $data['registrant']->event_id,
            'active_flag' => 1
        ])->get();

        return view('registrant.batch_confirmation', $data);
    }

    public function batchRegistrationConfirmation(array $data)
    {
        $result = Pipeline::send($data)->through(
            [
                ConfirmationPipe::class,
                RegistrantPipe::class,
            ]
        )->thenReturn();

        if($result){
            return back()->with("success", "Registration Confirmation Successful!!!");
        }

        return back()->with("error", "Registration Confirmation Unsuccessful!!!");
    }

    public function batchPayment(array $data)
    {
        session(['batch_payment' => $data]);
        $data['total_fee'] = $data['total_amount_paid'];

//        dd($data['batch_id']);
        BatchLog::find($data['batch_id'])->update([
            'confirmed' => 'Yes',
            'total_registration_fees' => $data['total_fee_to_pay'],
        ]);

        if($data['total_amount_paid'] > 0){
            $result = (new PaymentService)->makePayment($data);

            $response = (new PayStackPayment())->initializeTransaction($result);
            return redirect($response['data']['authorization_url']);
        }

        $amount = BatchLog::find($data['batch_id']);

        if($amount->total_registration_fees == 0) {
            foreach ($data['reg'] as $registrant) {
                $data2['registrant'] = RegistrantStage::where('id', $registrant['registrant_id'])->first();
                $data2['confirmed_registrant'] = Registrant::where('stage_id', $registrant['registrant_id'])->first();
                (new RoomAllocationPipe())->autoRoomAllocation($data2);
            }
        }

        return back()->with("success", "Registration Confirmation Successful!!!");

    }


}
