<?php

namespace App\Http\Controllers;

use App\Exports\RegistrationStageExport;
use App\Helpers\PayStackPayment;
use App\Helpers\Utils;
use App\Models\Admin\Country;
use App\Models\Admin\Event;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Services\Admin\PaymentService;
use App\Services\Registrant\RegistrantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RegistrantController extends Controller
{
    private RegistrantService $registrant;
    private PaymentService $paymentService;

    public function __construct(RegistrantService $registrant, PaymentService $paymentService)
    {
        $this->registrant = $registrant;
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        return $this->registrant->index(get_logged_in_user_event_id());
    }

    /**
     * Display a listing of the resource.
     */
    public function register()
    {
        return $this->registrant->register();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->formValidation($request, 'create');;

        return $this->registrant->registrantRegistration($request->all());
    }

    public function exportRegistrationStage()
    {
        return $this->registrant->exportRegistrationStage();
    }

    public function individualRegistrationConfirm(Request $request)
    {
        $this->formValidation($request);

        return $this->registrant->individualRegistrationConfirm($request->all());
//        dd($request->all());
    }

    public function individualRegistrationUpdate(Request $request)
    {
        return $this->registrant->individualRegistrationUpdate($request->all());
//        dd($request->all());
    }

    public function batchRegistrationStage(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'email' => 'required|email',
            'phone_number' => 'required|regex:/^\+[1-9][0-9]{10,}$/',
            'whatsapp_number' => 'nullable|regex:/^\+[1-9][0-9]{10,}$/',
            'file' => 'required|mimes:csv,xlx,xls,xlsx|max:1048'
        ],
        [
            'phone_number.regex' => 'Phone number must start with "+" and contain at least 12 digits (e.g., +233541234567).',
            'whatsapp_number.regex' => 'WhatsApp number must start with "+" and contain at least 12 digits (e.g., +233541234567).',
        ]);

        return $this->registrant->batchImportRegistration($request);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function registrationLogin(Request $request)
    {
        $request->validate([
            'email' => ['required'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        return $this->registrant->registrantLogin($request->all());
    }

    public function individualLogin(Request $request)
    {
        return $this->registrant->individualLogin($request->all());
    }

    public function registrantMakePayment(Request $request)
    {
        $result = $this->paymentService->makePayment($request->all());

        $response = (new PayStackPayment())->initializeTransaction($result);
        return redirect($response['data']['authorization_url']);
    }

    public function batchLogin(Request $request)
    {
        return $this->registrant->batchLogin($request->all());
    }

    public function batchRegistrationConfirm($id)
    {
        return $this->registrant->batchRegistrationConfirm($id);
    }

    public function batchRegistrationConfirmation(Request $request)
    {
        $this->formValidation($request);

        return $this->registrant->batchRegistrationConfirmation($request->all());
    }

    public function batchPayment(Request $request)
    {
        $request->validate([
            'total_amount_paid' => 'required|numeric',
            'reg.*' => 'required',
        ]);
        foreach ($request->reg as $kay => $value) {
            $confirm = Registrant::where('stage_id', $value['registrant_id'])->first();
            if(!$confirm){
                return back()->with('error', "Line No. ".$kay." has not been confirmed yet!!!");
            }
        }

        return $this->registrant->batchPayment($request->all());
    }

    public function registrantLogout()
    {
        session()->forget('registrant');
        return redirect(route('registrant_login', absolute: false))->with('success', "Logout Successful!!!.");
    }

    public function destroy(Registrant $registrant)
    {
        //
    }

    /**
     * @param Request $request
     * @return void
     */
    protected function formValidation(Request $request, $type = 'update'): void
    {
        $request->validate([
            'title' => 'required',
            'first_name' => 'required',
            'surname' => 'required',
            'other_names' => 'nullable',
            'gender' => 'required',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required',
            'nationality_id' => 'required',
            'phone_number' => 'required|regex:/^\+[1-9][0-9]{10,}$/',
            'whatsapp_number' => 'nullable|regex:/^\+[1-9][0-9]{10,}$/',
            'email' => 'required|email',
            'address' => 'required',
            'position_held' => 'required',
            'profession' => 'required',
            'residence_country_id' => 'required',
            'languages_spoken' => 'required',
            'need_accommodation' => 'required|boolean',
            'emergency_contacts_name' => 'required',
            'emergency_contacts_relationship' => 'required',
            'emergency_contacts_phone_number' => 'required|regex:/^\+[1-9][0-9]{10,}$/',
            'attendance_type' => 'required|in:In-Person,Online',
            'event_id' => 'required|exists:events,id',
            'disability' => 'required|boolean',
            'special_needs' => 'required',
            'accommodation_fee' => ($type === 'update') ? 'required|exists:event_fees,id' : 'nullable',
            'registration_fee' => ($type === 'update') ? 'required|exists:event_fees,id' : 'nullable',
            'amount_to_pay' => ($type === 'update') ? 'required|numeric' : 'nullable',
        ],
        [
            'phone_number.regex' => 'Phone number must start with "+" and contain at least 12 digits (e.g., +233541234567).',
            'whatsapp_number.regex' => 'WhatsApp number must start with "+" and contain at least 12 digits (e.g., +233541234567).',
            'emergency_contacts_phone_number.regex' => 'Emergency Contact number must start with "+" and contain at least 12 digits (e.g., +233541234567).'
        ]);
    }
}
