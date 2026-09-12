<?php

namespace App\Http\Controllers;

use App\Helpers\PayStackPayment;
use App\Helpers\Utils;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Services\Admin\PaymentService;
use App\Services\Registrant\RegistrantService;
use Illuminate\Http\Request;

class RegistrantController extends Controller
{
    private RegistrantService $registrant;

    private PaymentService $paymentService;

    public function __construct(RegistrantService $registrant, PaymentService $paymentService)
    {
        $this->registrant = $registrant;
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        return $this->registrant->index(get_logged_in_user_event_id(), $request->get('search'));
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
        $this->individualFormValidation($request, 'create');

        return $this->registrant->registrantRegistration($request->all());
    }

    public function exportRegistrationStage()
    {
        return $this->registrant->exportRegistrationStage();
    }

    public function individualRegistrationConfirm(Request $request)
    {
        $this->individualFormValidation($request, 'confirm');
        $this->authorizeIndividualRegistrant($request->id);

        return $this->registrant->individualRegistrationConfirm($request->all());
    }

    public function individualRegistrationUpdate(Request $request)
    {
        $this->authorizeIndividualRegistrant($request->reg_id);

        return $this->registrant->individualRegistrationUpdate($request->all());
    }

    public function batchRegistrationStage(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone_number' => ['required', 'regex:'.Utils::GHANA_PHONE_REGEX],
            'whatsapp_number' => ['nullable', 'regex:'.Utils::GHANA_PHONE_REGEX],
            'is_student' => 'required|boolean',
            'institution_name' => 'required_if:is_student,1',
            'file' => 'required|mimes:csv,xlx,xls,xlsx|max:1048',
        ],
            [
                'phone_number.regex' => 'Phone number must be a valid Ghanaian number (e.g., 0248000000).',
                'whatsapp_number.regex' => 'WhatsApp number must be a valid Ghanaian number (e.g., 0248000000).',
                'institution_name.required_if' => 'Please enter the institution name.',
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
        $request->validate([
            'stage_id' => 'required|exists:registrants,stage_id',
            'total_fee' => 'required|numeric|min:0.01',
        ]);
        $this->authorizeIndividualRegistrant($request->stage_id);

        $result = $this->paymentService->makePayment($request->all());

        $response = (new PayStackPayment)->initializeTransaction($result);

        return redirect($response['data']['authorization_url']);
    }

    public function batchLogin(Request $request)
    {
        return $this->registrant->batchLogin($request->all());
    }

    public function batchRegistrationConfirm($id)
    {
        $this->authorizeBatchRegistrant($id);

        return $this->registrant->batchRegistrationConfirm($id);
    }

    public function batchRegistrationConfirmation(Request $request)
    {
        $this->formValidation($request);
        $this->authorizeBatchRegistrant($request->id);

        return $this->registrant->batchRegistrationConfirmation($request->all());
    }

    public function batchPayment(Request $request)
    {
        $request->validate([
            'total_amount_paid' => 'required|numeric',
            'reg.*' => 'required',
        ]);

        $session = session('registrant');
        if (! $session instanceof BatchLog || (string) $session->id !== (string) $request->batch_id) {
            abort(403, 'You are not authorized to perform this action.');
        }

        foreach ($request->reg as $kay => $value) {
            $confirm = Registrant::where('stage_id', $value['registrant_id'])->first();
            if (! $confirm) {
                return back()->with('error', 'Line No. '.$kay.' has not been confirmed yet!!!');
            }

            $stage = RegistrantStage::find($value['registrant_id']);
            if (! $stage || $stage->batch_no != $session->batch_no) {
                abort(403, 'You are not authorized to perform this action.');
            }
        }

        return $this->registrant->batchPayment($request->all());
    }

    public function registrantLogout()
    {
        session()->forget('registrant');

        return redirect(route('registrant_login', absolute: false))->with('success', 'Logout Successful!!!.');
    }

    public function removeFromBatch($id)
    {
        $this->authorizeBatchRegistrant($id);

        return RegistrantService::destroy($id);
    }

    /**
     * Require the logged-in session to be an individual registrant acting
     * on their own stage record — not a batch coordinator, and not someone
     * else's registration.
     */
    protected function authorizeIndividualRegistrant($id): void
    {
        $session = session('registrant');

        if (! $session instanceof RegistrantStage || (string) $session->id !== (string) $id) {
            abort(403, 'You are not authorized to perform this action.');
        }
    }

    /**
     * Require the logged-in session to be a batch coordinator acting on a
     * registrant that belongs to their own batch.
     */
    protected function authorizeBatchRegistrant($id): void
    {
        $session = session('registrant');

        if (! $session instanceof BatchLog) {
            abort(403, 'You are not authorized to perform this action.');
        }

        $registrant = RegistrantStage::find($id);

        if (! $registrant || $registrant->batch_no != $session->batch_no) {
            abort(403, 'You are not authorized to perform this action.');
        }
    }

    /**
     * Position Held, Event Attending, Attendance Type, Other Names,
     * WhatsApp Number, Emergency Contact Relationship, and Address were
     * dropped from the individual registration/confirmation forms (they
     * either default automatically or are set once at initial sign-up -
     * see RegistrantService::registrantRegistration()/ConfirmationPipe).
     * This is deliberately separate from formValidation() below, which
     * stays as the batch confirmation form's validator - that form still
     * collects all of these fields.
     */
    protected function individualFormValidation(Request $request, string $type): void
    {
        $request->validate([
            'title' => 'required',
            'first_name' => 'required',
            'surname' => 'required',
            'gender' => 'required',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required',
            'nationality_id' => 'required',
            'phone_number' => ['required', 'regex:'.Utils::GHANA_PHONE_REGEX],
            'email' => 'required|email',
            'profession' => 'required',
            'residence_country_id' => 'required',
            'languages_spoken' => 'required',
            'need_accommodation' => 'required|boolean',
            'emergency_contacts_name' => 'required',
            'emergency_contacts_phone_number' => ['required', 'regex:'.Utils::GHANA_PHONE_REGEX],
            'disability' => 'required|boolean',
            'special_needs' => 'required',
            'is_student' => 'required|boolean',
            'institution_name' => 'required_if:is_student,1',
            'accommodation_fee' => ($type === 'confirm') ? 'required|exists:event_fees,id' : 'nullable',
            'registration_fee' => ($type === 'confirm') ? 'required|exists:event_fees,id' : 'nullable',
            'amount_to_pay' => ($type === 'confirm') ? 'required|numeric' : 'nullable',
        ], [
            'phone_number.regex' => 'Phone number must be a valid Ghanaian number (e.g., 0248000000).',
            'emergency_contacts_phone_number.regex' => 'Emergency Contact number must be a valid Ghanaian number (e.g., 0248000000).',
            'institution_name.required_if' => 'Please enter your institution name.',
        ]);
    }

    protected function formValidation(Request $request, $type = 'update'): void
    {
        $request->validate([
            'title' => 'required',
            'first_name' => 'required',
            'surname' => 'required',
            'gender' => 'required',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required',
            'nationality_id' => 'required',
            'phone_number' => ['required', 'regex:'.Utils::GHANA_PHONE_REGEX],
            'email' => 'required|email',
            'profession' => 'required',
            'residence_country_id' => 'required',
            'languages_spoken' => 'required',
            'need_accommodation' => 'required|boolean',
            'emergency_contacts_name' => 'required',
            'emergency_contacts_phone_number' => ['required', 'regex:'.Utils::GHANA_PHONE_REGEX],
            'disability' => 'required|boolean',
            'special_needs' => 'required',
            'institution_name' => 'required_if:is_student,1',
            'accommodation_fee' => ($type === 'confirm') ? 'required|exists:event_fees,id' : 'nullable',
            'registration_fee' => ($type === 'confirm') ? 'required|exists:event_fees,id' : 'nullable',
            'amount_to_pay' => ($type === 'confirm') ? 'required|numeric' : 'nullable',
        ],
            [
                'phone_number.regex' => 'Phone number must be a valid Ghanaian number (e.g., 0248000000).',
                'whatsapp_number.regex' => 'WhatsApp number must be a valid Ghanaian number (e.g., 0248000000).',
                'emergency_contacts_phone_number.regex' => 'Emergency Contact number must be a valid Ghanaian number (e.g., 0248000000).',
            ]);
    }
}
