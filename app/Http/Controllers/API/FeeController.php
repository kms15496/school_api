<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} - Transaction Receipt</title>
    <style>
        @font-face {
            font-family: 'Padauk';
            src: url("{{ storage_path('fonts/Padauk-Regular.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Padauk';
            src: url("{{ storage_path('fonts/Padauk-Bold.ttf') }}") format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        body {
            font-family: 'Padauk', sans-serif;
            font-size: 14px;
            color: #222;
            margin: 0;
            padding: 14px 16px 24px;
            line-height: 1.35;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        .page {
            width: 100%;
        }

        .top-note {
            text-align: center;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
        }

        .header img {
            max-width: 520px;
            width: 100%;
            height: auto;
        }

        .notice-box {
            background: #e31c1c;
            color: #fff;
            padding: 8px 12px;
            font-size: 11px;
            line-height: 1.45;
            margin: 8px 0 18px;
        }

        .notice-line {
            margin: 0 0 4px;
        }

        .notice-line:last-child {
            margin-bottom: 0;
        }

        .meta-row {
            width: 100%;
            margin-bottom: 10px;
        }

        .meta-row td {
            border: none;
            padding: 0;
            vertical-align: top;
            font-size: 14px;
        }

        .meta-row .right {
            text-align: right;
            white-space: nowrap;
        }

        .info {
            margin-bottom: 12px;
        }

        .info p {
            margin: 0 0 5px;
            font-size: 15px;
        }

        .info .label {
            font-weight: bold;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 15px;
        }

        .receipt-table th,
        .receipt-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #cfcfcf;
        }

        .receipt-table thead th {
            background: transparent;
            font-size: 15px;
            font-weight: bold;
            border-bottom: 2px solid #d8d8d8;
        }

        .receipt-table th:first-child,
        .receipt-table td:first-child {
            text-align: left;
        }

        .receipt-table th:last-child,
        .receipt-table td:last-child {
            text-align: right;
            width: 180px;
        }

        .receipt-table tfoot td {
            font-weight: bold;
            border-top: 3px solid #222;
            border-bottom: 1px solid #d8d8d8;
        }

        .footer-note {
            text-align: center;
            color: #0a6b39;
            font-size: 17px;
            font-weight: bold;
            margin-top: 90px;
            line-height: 1.8;
        }

        .footer-note .small {
            font-size: 14px;
            font-weight: normal;
        }
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
                background: #fff !important;
            }

            .notice-box {
                background: #ef1f1f !important;
                color: #fff !important;
                border: 1px solid #d71919 !important;
            }
        }
    </style>
</head>

<body>
    @php
        $receivedAmount = $transaction->amount ?? 0;
        $discount = $transaction->discount_amount ?? 0;
        $total = $receivedAmount;
        $printedAt = \Carbon\Carbon::now('Asia/Yangon')->format('Y-m-d H:i:s');
    @endphp

    <div class="page">
        @if ($image)
            <div class="header">
                <img src="{{ $image }}" alt="Receipt Header">
            </div>
        @elseif ($address)
            <div class="top-note">
                @foreach ($addresses as $add)
                    <div>{{ $add }}</div>
                @endforeach
            </div>
        @else
            <div class="top-note">{{ config('app.name') }}</div>
        @endif

        <div class="notice-box">
            <p class="notice-line">
                ကျောင်းနှစ်(၁) အတွက် (၁)လစဉ်ကြေးအားလုံးကိုသတ်မှတ်ရက်နောက်ဆုံးထားပေးသွင်းရန် မေတ္တာရပ်ခံအပ်ပါသည်။
            </p>
            <p class="notice-line">
                ကျောင်းနှစ်(၁) မှ မဟုတ်သောကျောင်းသားများသည် (အကြွင်းကျန်စာရင်းအတိုင်း) ပေးသွင်းရန်၊ နောက်ဆုံးရက်မကျော်လွန်စေရန် လိုက်နာပေးပါရန်။
            </p>
        </div>

        <table class="meta-row">
            <tr>
                <td></td>
                <td class="right">{{ $printedAt }}</td>
            </tr>
        </table>

        <div class="info">
            <p><span class="label">Invoice ID:</span> {{ $invoiceId }}</p>
            <p><span class="label">Fee Name:</span> {{ $fee ?? '.....................' }}</p>
            <p><span class="label">Name:</span> {{ $student->name ?? '.....................' }}</p>
            <p><span class="label">Class:</span> {{ $student->class_name ?? '.....................' }}</p>
        </div>

        <table class="receipt-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount (Ks)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $feeDetail->payment_name }}</td>
                    <td>{{ number_format($feeDetail->amount) }}</td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td>{{ number_format($discount) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>Received Amount</td>
                    <td>{{ number_format($total) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="footer-note">
            ၁။ ရက်ကျော်ပြီး ကျောင်းလခ တောင်းခံခြင်းမပြုပါ။ မေတ္တာဖြင့်သာ ပြန်လည်သတိပေးမည်ဖြစ်ပါသည်။<br>
            ၂။ လစဉ် လဆန်း (၁)ရက် နောက်ဆုံးထား၍ ပုံမှန်ကျောင်းလခ နောက်ဆုံးပေးသွင်းရမည်။<br>
            ၃။ ကျောင်းပြီး ကျောင်းလခ ပေးချေမှုအား သိမ်းဆည်းပါ။ ကျောင်းလခများ ပေးသွင်းလျှင်
            ကျောင်းမှပေးသောပြေစာများ သိမ်းဆည်းပါ။ ကျောင်းလခများ ပေးသွင်းလျှင်
            လစဉ်ကျောင်းစည်းကမ်းအတိုင်းသာ ပေးသွင်းရမည်။ Kpay ဖြင့်လည်းပေးချေနိုင်ပါသည်။<br>
            <span class="small">Note ကျောင်းတွင် ကျောင်းသား/သူ အမည် အတန်းကို ရေးပေးပါ။</span>
        </div>
    </div>
</body>

</html>
