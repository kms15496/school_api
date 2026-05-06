<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Invoice;

use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;


class InvoiceController extends Controller
{
    public function makePayment(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return $this->apiResponse(false, 'Invoice not found', null, 404);
        }

        if($invoice->status === 'paid') {
            return $this->apiResponse(false, 'Invoice already paid', null, 400);
        }

        $feeDetail = $invoice->feeDetail;

        $payload = [
            'school_code' => 'pnpt',
            'amount' => (int) $feeDetail->amount,
            'currency' => 'MMK',
            'invoice_no' => $invoice->invoice_no,
            'invoice_id' => (string) $invoice->invoice_id,
            'external_transaction_id' => 'PNPTINV' . $invoice->id,
            'service_code' => 'testayammqr-qr',
        ];

        $response = Http::timeout(30)
            ->acceptJson()
            ->post(
                'https://pgw.asiadigitalplus.com/api/aya/request-qr-payment',
                $payload
            );

        if (!$response->successful()) {
            return $this->apiResponse(false, 'Failed to request QR payment', null, $response->status());
        }

        return $this->apiResponse(true, 'QR payment requested successfully', $response->json());

    }


}
