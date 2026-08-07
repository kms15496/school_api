<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeController extends Controller
{
    public function getFees(Request $request)
    {
        $schoolId = (int) $request->input('school_id');
        $academicYearId = (int) $request->input('aay');
        $studentId = (int) $request->input('student_id');



        if (!$schoolId || !$academicYearId || !$studentId) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid request context',
                'data' => null,
            ], 400);
        }

        // $result = DB::table('student_academic_years as say')
        //     ->join('fees as f', function ($join) {
        //         $join->on('f.school_class_id', '=', 'say.school_class_id')
        //             ->on('f.academic_year_id', '=', 'say.academic_year_id')
        //             ->on('f.school_id', '=', 'say.school_id');
        //     })
        //     ->join('fee_details as fed', 'fed.fee_id', '=', 'f.id')
        //     ->leftJoin('invoices as i', function ($join) use ($studentId) {
        //         $join->on('i.fee_id', '=', 'fed.fee_id')
        //             ->on('i.fee_detail_id', '=', 'fed.id')
        //             ->where('i.student_id', '=', $studentId);
        //     })
        //     ->where('say.student_id', $studentId)
        //     ->where('say.academic_year_id', $academicYearId)
        //     ->where('say.school_id', $schoolId)
        //     ->select([
        //         'fed.fee_id as fid',
        //         'fed.id as fedid',
        //         'fed.payment_name',
        //         'fed.amount',
        //         'fed.due_date',
        //         'i.id as invoice_id',
        //         'i.status as invoice_status',
        //     ])
        //     ->orderBy('fed.due_date')
        //     ->get();


        $result = DB::table('invoices as i')
            ->join('fee_details as fed', 'fed.id', '=', 'i.fee_detail_id')
            ->join('fees as f', 'f.id', '=', 'i.fee_id')
            ->join('student_academic_years as say', function ($join) {
                $join->on('say.school_class_id', '=', 'f.school_class_id')
                    ->on('say.academic_year_id', '=', 'f.academic_year_id')
                    ->on('say.school_id', '=', 'f.school_id');
            })
            ->where('i.student_id', $studentId)
            ->where('say.student_id', $studentId)
            ->where('say.academic_year_id', $academicYearId)
            ->where('say.school_id', $schoolId)
            ->select([
                'fed.fee_id as fid',
                'fed.id as fedid',
                'fed.payment_name',
                'fed.amount',
                'fed.due_date',
                'i.id as invoice_id',
                'i.status as invoice_status',
            ])
            ->orderBy('fed.due_date')
            // ->orderBy('fed.id')
            ->get();

        $previousInvoicePaid = true;

        $result = $result->map(function ($fee) use (&$previousInvoicePaid) {
            $fee->is_payable = $previousInvoicePaid;
            $previousInvoicePaid = $fee->invoice_status === 'paid';

            return $fee;
        });

        return $this->apiResponse(true, 'Fees fetched successfully', $result);
    }

    public function makePayment(Request $request, $feeId)
    {
        $schoolId = (int) $request->input('school_id');
        $academicYearId = (int) $request->input('aay');
        $studentId = (int) $request->input('student_id');

        if (!$schoolId || !$academicYearId || !$studentId) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid request context',
                'data' => null,
            ], 400);
        }

        // Here you would typically create a payment intent or redirect to a payment gateway
        // For demonstration, we'll just return a success message with the fee ID

        return $this->apiResponse(true, 'Payment initiated successfully', [
            'fee_id' => $feeId,
            'payment_url' => 'https://payment-gateway.example.com/pay?fee_id=' . $feeId . '&student_id=' . $studentId,
        ]);
    }
}
