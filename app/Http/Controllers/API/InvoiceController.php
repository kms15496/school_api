<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Invoice;

use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Repositories\StudentRepository;
use App\Models\School;

class InvoiceController extends Controller
{
    protected $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }
    public function makePayment(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::find($id);

        $invoice_number = explode('-', $invoice->invoice_id);
        $invoice_number = $invoice_number[0] . '-' . $invoice_number[1];
        $student = $this->studentRepository->getStudent($invoice->student_id);

        $school = School::where('id', $student->school_id)->first();

        if (!$invoice) {
            return $this->apiResponse(false, 'Invoice not found', null, 200);
        }

        if ($invoice->status === 'paid') {
            return $this->apiResponse(false, 'Invoice already paid', null, 200);
        }

        $feeDetail = $invoice->feeDetail;

        $payload = [
            'amount' => $invoice->feeDetail->amount - ($invoice->discount->amount ?? 0),
            'invoice_no' => $invoice_number,
            'first_name' => $student->name,
            'last_name' => '-',
            'phone' => $student->phone,
            'email' => 'dev@icec.com',
            'school_code' => 'icec',
            'invoice_id' => $invoice->invoice_id,
            'UserDefined1' => $school->name,
            'UserDefined2' => $student->name,
            'UserDefined3' => $invoice->invoice_id,
            'UserDefined4' => $student->class_name,
            'UserDefined5' => $student->phone,
        ];

        $response = Http::timeout(30)
            ->acceptJson()
            ->post(
                'https://pgw.asiadigitalplus.com/api/a+/request-qr',
                $payload
            );

        if (!$response->successful()) {
            return $this->apiResponse(false, 'Failed to request QR payment', null, $response->status());
        }

        return $this->apiResponse(true, 'QR payment requested successfully', $response->json());

    }

    public function checkInvoiceStatus(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return $this->apiResponse(false, 'Invoice not found', null, 404);
        }

        $status = Invoice::select('status')->where('id', $id)->first();



        return $this->apiResponse(true, 'Invoice status retrieved successfully', $status);
    }

}
