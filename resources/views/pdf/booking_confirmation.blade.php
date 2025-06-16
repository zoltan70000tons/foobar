<!DOCTYPE html>
<html lang="en">
{{ $booking = $data['booking'] }}
{{ $event =$booking->event }}
{{ $cabin = $booking->cabin }}


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$booking->booking_code}}</title>
    <style>
        @page {
            margin-top: 0px;
            margin-bottom: 80px;
            margin-left: 0px;
            margin-right: 0px;

        }

        body {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 10px;
        }

        header {
            position: fixed;
            top: 10px;
            left: 0;
            right: 0;
            text-align: center;
        }

        td,
        th {
            line-height: 8px;

        }

        .page-container {
            width: 780px;
            margin: 0 auto;
        }

        .break {
            height: 250px;
        }

        .payments-table {
            width: 700px;
            margin: 0 auto;
            border-collapse: collapse;
            font-weight: normal;
            margin-bottom: 10px;
        }

        .table-wrapper {
            width: 100%;
            text-align: center;
            /* opcional: para centrar si hay espacio */
        }

        .table-half {
            display: inline-block;
            vertical-align: top;
            width: 325px;
            border-collapse: collapse;
            font-size: 10px;
            /* margin-right: 2%; */
        }

        .table-half:last-child {
            margin-right: 0;
        }

        .table-half td,
        .table-half th {
            padding: 5px;
            vertical-align: top;
        }

        .passenger-table td {
            line-height: 10px;
            padding: 0;
            margin: 0;
            font-size: 10px;
        }

        .passenger_title {
            font-size: 14px !important;
        }

        td .underline {
            text-decoration: underline;
        }

        .single-text,
        .credit-charges,
        .add-pax {
            font-size: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            color: red;
            text-align: center
        }

        .due-immediately {
            color: red;
            font-weight: bold;
        }

        .header-1 {
            text-align: left;
            width: 100px;
        }

        .header-2 {
            text-align: right;
            width: 100px;
        }
    </style>
</head>

<body>

    <header>
        {!! $header !!}
    </header>
    <div class="page-container">
        <div class="break"></div>
        <div class="table-wrapper">
            @foreach($paymentInfo['passengers'] as $index => $passenger)
            @php
            $passenger = $passenger['passenger'];
            $payment_method = match ($passenger->payment_method) {
            'CREDIT_CARD' => 'Credit Card*',
            'BANK_TRANSFER' => 'Bank Transfer',
            default => '',
            };
            $installments = $passenger->getInstallmentStatus();
            $fullPaymentStatus = $passenger->getFullPaymentStatus();
            $fullyPaid = $fullPaymentStatus['fully_paid'];
            $amount = $fullyPaid ? $fullPaymentStatus['amount'] : $fullPaymentStatus['remaining_amount'];
            @endphp

            <table role="doc-pagebreak" width="100%" style="border-collapse: collapse;text-align:left;" class="payments-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">
                            <strong>{{ $passenger->lead_passenger ? 'Lead Passenger' : (['2nd', '3rd', '4th', '5th', '6th', '7th', '8th'][$passenger->passenger_order - 2] ?? '8th') . ' Passenger' }}</strong>
                        </th>
                        <td style="width: 100px;">Official Ticket Price</td>
                        <td style="width: 100px;">{{ formatCurrency($booking->cabin->category->price) }}</td>
                        <th style="width: 130px; text-align: left;">Payment Plan</th>
                        <td style="width: 70px;"></td>
                        <td style="width: 100px;"></td>
                        <th style="width: 100px; text-align: left;">Form of Payment</th>
                    </tr>
                    <tr>
                        <td colspan="3" rowspan="4" width="100%" style="vertical-align: top;">
                            <table width="100%" style="border-collapse: collapse; margin: 0 auto;">
                                <tr>
                                    <td style="width: 100px;"></td>
                                    <td style="width: 100px;">{{ $passenger['total_discounts_percentage'] }}% Discount</td>
                                    <td style="width: 100px;">{{ $paymentInfo['formatted_total_discounts'] }}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>Net Ticket Price</td>
                                    <td>{{ $paymentInfo['formatted_net_ticket_price'] }}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>Taxes & Fees</td>
                                    <td>{{ formatCurrency($passenger->paymentInfo['total_fees']) }}</td>

                                </tr>
                                <tr>
                                    <td></td>
                                    <td>Total Ticket Price</td>
                                    <td>{{ formatCurrency($passenger->passenger_allocated_cost) }}</td>
                                </tr>
                            </table>
                        </td>
                        <td colspan="4" rowspan="5" style="vertical-align: top;">
                            <table width="100%" style="border-collapse: collapse; margin: 0 auto;" class="nested-payments-table">
                                <tbody>
                                    <tr>
                                        <td style="width:130px">&nbsp;</td>
                                        <td style="width:70px"></td>
                                        <td style="width:100px"></td>
                                        <td style="width:100px"></td>
                                    </tr>
                                    @if($booking->payment_plan == 'INSTALLMENTS')
                                    @foreach ($installments['paid_installments'] as $installment)
                                    <tr>
                                        <td>{{ $installment['status'] }}, {{ formatDate($installment['due_date']) }}</td>
                                        <td>{{ formatCurrency($installment['amount']) }}</td>
                                        <td></td>
                                        <td>{{ $payment_method }}</td>
                                    </tr>
                                    @endforeach

                                    @foreach ($installments['remaining_installments'] as $installment)
                                    @php
                                    $isFee = $installment['type'] == 'FEE';
                                    $name = $isFee ? $installment['name'] :'Installment';
                                    @endphp
                                    <tr>
                                        <td>
                                            @if ($installment['status'] === 'Unpaid' && $installment['due_date'] <= now())
                                                <span class="due-immediately">Due Immediately</span>
                                                @else
                                                {{ $installment['status'] }}, {{ formatDate($installment['due_date']) }}
                                                @endif
                                        </td>
                                        <td>{{ formatCurrency($installment['amount_due']) }}</td>
                                        <td>{{ $name }}</td>
                                        <td>{{ $payment_method }}</td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <!-- If payment plan is not installments, show full payment status -->
                                    @foreach ($installments['remaining_installments'] as $installment)
                                    @php
                                    $isFee = $installment['type'] == 'FEE';
                                    $name = $isFee ? $installment['name'] :'Full Payment';
                                    @endphp
                                    <tr>
                                        <td>
                                            @if ($installment['status'] === 'Unpaid' && $installment['due_date'] <= now())
                                                <span class="due-immediately">Due Immediately</span>
                                                @else
                                                {{ $installment['status'] }}, {{ formatDate($installment['due_date']) }}
                                                @endif
                                        </td>
                                        <td>{{ formatCurrency($installment['amount_due']) }}</td>
                                        <td>{{ $name }}</td>
                                        <td>{{ $payment_method }}</td>
                                    </tr>
                                    @endforeach
                                    @endif
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 130px;"><strong>Total {{ $passenger->lead_passenger ? 'Lead Passenger' : (['2nd', '3rd', '4th', '5th', '6th', '7th', '8th'][$passenger->passenger_order - 2] ?? '8th') . ' Passenger' }} Paid<strong></td>
                                        <td style="width: 70px!important;"><strong>{{formatCurrency($passenger->passenger_balance)}}</strong></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>

                        </td>
                    </tr>
                </thead>
            </table>
            @if($index + 1 == count($paymentInfo['passengers']))
            <table role="doc-pagebreak" width="100%" style="border-collapse: collapse;text-align:left;margin-top:0px;font-size:10px;" class="payments-table">
                <tr>
                        <td style="width: 100px;"></td>
                        <td style="width: 100px;"></td>
                        <td style="width: 100px;"></td>
                        <td style="width: 130px;text-decoration:underline"><strong>Grand Total Paid</strong></td>
                        <td style="width: 70px;text-decoration:underline"><strong>{{ formatCurrency($booking->getTotalPaid())}}</strong></td>
                        <td style="width: 100px;"></td>
                        <td style="width: 100px;"></td>

            </table>
            @endif
            

            @php
            $isLast = ($index + 1) === count($booking->passengers);
            $shouldBreak = (($index + 1) % 4 === 0) || $isLast;
            @endphp

            @if($shouldBreak)
            @if ($paymentInfo['cabinType']->id == 2 || $paymentInfo['cabinType']->id == 3 )
            <section class="single-text" style="text-align: center;">
                Please note: Bed assignments in Single Ticket cabins are first come first serve on board!<br />
                You will be sharing your cabin with {{ $data['category_specs']->capacity - 1 }} other passenger(s)
            </section>
            @endif

            <section class="credit-charges" style="text-align: center;">
                *All charges by credit card will be made on behalf of UMCruises UK LLP<br />
                Lower Ground Floor • 19-20 Berners Street • London • W1T 3NW • UNITED KINGDOM<br />
                UMCruises International Ltd.
            </section>

            <div style="page-break-after: always;"></div>
            <div class="break"></div>
            @endif

            @endforeach

        </div>

        <div style="margin-top: 5px;">
            <div class="add-pax center" style="">
                To add or correct Passenger Information please visit:
                <a href="{{ $data['addpax_url'] }}" target="_blank">AddPax.com</a>
                <br><br>
            </div>
            <table width="700px" cellspacing="0" cellpadding="5" class="passenger-table" style="border-collapse: collapse;margin:0 auto;font-size: 12px!important;">
                @foreach ($booking->passengers as $index => $passenger)
                {{-- Check if the index is even or odd to determine the row structure --}}
                {{-- If even, start a new row --}}
                @if ($index % 2 == 0)
                <tr>
                    @endif

                    <td width="50%" valign="top" style="padding-bottom: 30px;">
                        <table width="100%" cellspacing="0" cellpadding="3">
                            <tr>
                                <td colspan="2" class="passenger_title underline">
                                    <strong>{{ $passenger->lead_passenger ? 'Lead Passenger' : (['2nd', '3rd', '4th', '5th', '6th', '7th', '8th'][$passenger->passenger_order - 2] ?? '8th') . ' Passenger' }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">&nbsp;</td>
                            </tr>

                            <tr>
                                <td width="50%"><strong>Gender:</strong></td>
                                <td width="50%">{{ $passenger->gender }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>First Name:</strong></td>
                                <td width="50%">{{ $passenger->first_name }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Middle Name:</strong></td>
                                <td width="50%">{{ $passenger->middle_name }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Last Name:</strong></td>
                                <td width="50%">{{ $passenger->last_name }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Survivor Number:</strong></td>
                                <td width="50%">{{ $passenger->survivor_number }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Survivor Status:</strong></td>
                                <td width="50%">{{ $passenger->getMemberShip() }}</td>

                            <tr>
                                <td width="50%"><strong>Date of Birth:</strong></td>
                                <td width="50%">{{ formatDate($passenger->dob ) }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Citizenship:</strong></td>
                                <td width="50%">{{ $passenger->citizenship }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Address:</strong></td>
                                <td width="50%">{{ $passenger->address_first }}</td>
                            </tr>
                            <tr>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>City:</strong></td>
                                <td width="50%">{{ $passenger->city }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>State/Province/Region:</strong></td>
                                <td width="50%">{{ $passenger->state }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Postal Code:</strong></td>
                                <td width="50%">{{ $passenger->postal_code }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Country:</strong></td>
                                <td width="50%">{{ $passenger->country }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Email:</strong></td>
                                <td width="50%">{{ $passenger->email }}</td>
                            </tr>
                            <tr>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Phone:</strong></td>
                                <td width="50%">{{ $passenger->phone }}</td>
                            </tr>
                            <tr>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Emergency Name:</strong></td>
                                <td width="50%">{{ $passenger->emergency_c_name }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Emergency Phone:</strong></td>
                                <td width="50%">{{ $passenger->emergency_c_phone }}</td>
                            </tr>
                            <tr>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Special Requests:</strong></td>
                                <td width="50%">{{ $passenger->special_request }}</td>
                            </tr>
                            <tr>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            @php
                            $creditAmount = $passenger->onboardCredits->pluck('amount')->implode(' | ');
                            $creditReason = $passenger->onboardCredits->pluck('reason')->implode(' | ');
                            @endphp
                            <tr>
                                <td width="50%"><strong>Onboard Credit:</strong></td>
                                <td width="50%">{{ formatCurrency($creditAmount) }}</td>
                            </tr>
                            <tr>
                                <td width="50%"><strong>Reason For Credit:</strong></td>
                                <td width="50%">{{ $creditReason }}</td>
                            </tr>
                        </table>
                    </td>

                    @if ($index % 2 == 1 || $loop->last)
                    {{-- If the total number of passengers is odd, add an empty cell to complete the row --}}
                    @if ($index % 2 == 0)
                    <td width="50%"></td>
                    @endif

                </tr>
                @endif

                @if(($index + 1) % 4 === 0 && $index ==3 && $booking->cabin->category->spec->capacity > 4)
                <div style="page-break-after: always;"></div>
                <div class="break"></div>
                <tr>
                    <td width="100%" colspan="2">
                        <div class="add-pax">
                            To add or correct Passenger Information please visit:
                            <a href="{{ $data['addpax_url'] }}" target="_blank">AddPax.com</a>
                            <br><br>
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
            </table>
        </div>


    </div>

</body>

</html>