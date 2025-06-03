<!DOCTYPE html>
<html lang="en">
{{ $booking = $data['booking'] }}
{{ $lastUpdated = $data['last_updated']}}
{{ $dateIssued = $data['date_issued']}}
{{ $event =$booking->event }}
{{ $cabin = $booking->cabin }}
{{ $category = $cabin->category }}
{{ $categorySpec = $cabin->cabinSpecs }}


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$booking->booking_code}}</title>
    <style>
        @page {
            margin-top: 10px;
            margin-bottom: 80px;
            /* margin-left: 0px;
            margin-right: 10px; */

        }

        body {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 0.5rem;
        }

        header {
            position: fixed;
            top: 10px;
            left: 0;
            right: 0;
            text-align: center;
        }

        .header,
        .post-header {
            text-align: center;
            font-weight: bold;
            font-size: 0.6rem;

        }

        .table-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table-container th {
            text-align: right;
        }

        .table-container th,
        .table-container td {
            font-weight: normal;
            text-align: right;
        }

        .bold {
            font-weight: bold !important;
        }

        .text-right {
            text-align: right;
        }

        .page-break {
            page-break-after: always;
        }

        .add-pax,
        .single-text {
            margin-top: 1rem;
            font-size: 0.8rem;
            color: red;
        }

        .add-pax a {
            color: red;
            text-decoration: none;
        }

        .passenger_title {
            font-size: 0.7rem;
        }

        .subrow {
            padding-left: 10px;
        }

        .align-left {
            text-align: left !important;
        }

        body {
            font-family: sans-serif;
            margin-top: 100px;
            margin-bottom: 60px;
        }

        .credit-charges {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.5rem !important;
            line-height: 0.8rem;
        }

        .underline {
            text-decoration: underline;
        }

        td,
        th {
            line-height: 0.7;
            padding: 2px 5px;
        }
        .booking-code {
            font-size: 0.7rem;
        }
    </style>
</head>

<body>

    <header class="header">
        <img src="{{ $data['logo'] }}" width="400px" alt="UMCruises Logo" style="margin-bottom: 1rem;">
    </header>
    <section class="body">
        <section class="post-header">
            <div>{{ formatDate($event->start_date, true, true) }} – {{ formatDate($event->end_date, true) }}</div>
            <div>{{ $data['event']->address }}</div>
            <div>Freedom Of The Seas</div>
        </section>
        <section class="body">
            <h2 style="text-align: center; margin-top:5px; margin-bottom: 0px;">BOOKING CONFIRMATION</h2>

            <table class="table-container" width="100%" style="margin-top:0px;">
                <tr>
                    <th width="50%">Date Issued:</th>
                    <td width="50%" class="align-left" style="padding-left: 5px;"> {{$dateIssued}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Last Updated: {{$lastUpdated}}</td>
                </tr>
                <tr class="booking-code">
                    <th class="bold">Booking Code:</th>
                    <td class="bold align-left" style="padding-left: 5px;">{{ $booking->booking_code }}</td>
                </tr>
                <tr>
                    <th>Cabin Number:</th>
                    <td class="align-left" style="padding-left: 5px;"> {{ $cabin->cabin_number }}</td>
                </tr>
                <tr>
                    <th>Category:</th>
                    <td class="align-left" style="padding-left: 5px;"> {{ $category->title }}</td>
                </tr>
                <tr>
                    <th>Number of Passengers:</th>
                    <td class="align-left" style="padding-left: 5px;"> {{ $data['category_specs']->capacity }}</td>
                </tr>
                <tr>
                    <th>Notes:</th>
                    <td class="align-left" style="padding-left: 5px;"></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>

                <tr>
                    <th class="underline bold">Grand Total Booking Price:</th>
                    <td class="align-left underline bold" style="padding-left: 5px;"> {{ formatCurrency($booking->getGrandTotal()) }}</td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                </tr>
                <!-- <tr>
                    <td>&nbsp;</td>
                </tr> -->

                {{ $booking = $data['booking'] }}

                @foreach ($paymentInfo['passengers'] as $passenger)
                @php
                $pass = $passenger['passenger'];
                $payment_method = match ($pass->payment_method) {
                'CREDIT_CARD' => 'Credit Card*',
                'BANK_TRANSFER' => 'Bank Transfer',
                default => '',
                };
                $installments = $pass->getInstallmentStatus();
                @endphp
                <tr>
                    <td colspan="2">
                        <table width="100%">
                            <tr>
                                <td width="50%">
                                    <table width="100%">
                                        <tr>
                                            <td width="40%" class="bold passenger_title">{{ $pass->lead_passenger ? 'Lead Passenger' : (['2nd', '3rd', '4th', '5th', '6th', '7th', '8th'][$pass->passenger_order - 2] ?? '8th') . ' Passenger' }}
                                            </td>
                                            <td width="35%">Official Ticket Price</td>
                                            <td width="30%">{{ formatCurrency($data['booking']->cabin->category->price) }}</td>
                                        </tr>
                                        <tr>
                                            <td width="25%"></td>
                                            <td width="25%">{{ $passenger['total_discounts_percentage'] }}% Discount</td>
                                            <td width="25%">{{$paymentInfo['formatted_total_discounts']}}</td>

                                        </tr>
                                        <tr>
                                            <td width="25%"></td>
                                            <td width="25%">Net Ticket Price</td>
                                            <td width="25%">{{formatCurrency($passenger['net_ticket_price'])}}</td>

                                        </tr>
                                        <tr>
                                            <td width="25%"></td>
                                            <td width="25%">Taxes & Fees</td>
                                            <td width="25%">{{formatCurrency($pass->paymentInfo['total_fees'])}}</td>

                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td width="25%"></td>
                                            <td width="25%">Total Ticket Price</td>
                                            <td width="25%">{{ $passenger['total_ticket_price'] }}</td>

                                        </tr>

                                    </table>

                                </td>
                                <td colspan="4" style="padding: 0; margin: 0; vertical-align: top;" width="50%">
                                    <table width="100%">
                                        <tr>
                                            <td width="30%"><span class="bold underline">Payment Plan:</span></td>
                                            <td width="15%"></td>
                                            <td width="20%"><span class="bold underline">Form Of Payment:</span></td>
                                            <td width="15%"></td>

                                        </tr>
                                        <tr>
                                            <td width="30%">&nbsp;</td>
                                            <td width="15%">&nbsp;</td>
                                            <td width="20%">&nbsp;</td>
                                            <td width="15%">&nbsp;</td>
                                        </tr>
                                        @if($booking->payment_plan == 'INSTALLMENTS')
                                        @php
                                        $paidInstallments = $installments['paid_installments'];
                                        @endphp

                                        @foreach ($paidInstallments as $installment)
                                        <tr>
                                            @php
                                            $isFee = $installment['type'] == 'FEE';
                                            @endphp
                                            <td class="">
                                                @if ($installment['status'] === 'Unpaid')
                                                <strong>Due Immediately</strong>
                                                @else
                                                {{ $installment['status'] }}, {{ formatDate($installment['due_date']) }}
                                                @endif
                                            </td>
                                            <td>{{ formatCurrency($installment['amount']) }}</td>
                                            <td>{{$payment_method}}</td>
                                            <td></td>
                                        </tr>
                                        @endforeach

                                        @php
                                        $remainingInstallments = $installments['remaining_installments'];
                                        @endphp
                                        @foreach ($remainingInstallments as $installment)
                                        <tr>
                                            <td>
                                                @php
                                                $isFee = $installment['type'] == 'FEE';
                                                @endphp
                                                @if ($installment['status'] === 'Unpaid')
                                                <strong>Due Immediately</strong>
                                                @else
                                                {{ $installment['status'] }}, {{ formatDate($installment['due_date']) }}
                                                @endif
                                            </td>
                                            <td>{{ formatCurrency($installment['amount_due']) }}</td>
                                            <td>{{ $payment_method }}</td>
                                            <td></td>

                                        </tr>
                                        @endforeach

                                        @else
                                        @php
                                        $fullPaymentStatus = $pass->getFullPaymentStatus();
                                        $fullyPaid =$fullPaymentStatus['fully_paid'];
                                        $amount = $fullyPaid ? $fullPaymentStatus['amount'] : $fullPaymentStatus['remaining_amount'];
                                        @endphp
                                        <tr>
                                            <td>
                                                @if ($fullPaymentStatus['status'] === 'Unpaid')
                                                <strong class="strong">Due Immediately</strong>
                                                @else
                                                {{ $fullPaymentStatus['status'] }}, {{ formatDate($fullPaymentStatus['due_date']) }}
                                                @endif
                                            </td>
                                            <td>{{ formatCurrency($amount) }}</td>
                                            <td>{{$payment_method}}</td>
                                            <td></td>

                                        </tr>

                                        @endif

                                        <tr>
                                            <td>&nbsp;</td>
                                        </tr>

                                        <tr>
                                            <td width="30%" class="bold underline">Total {{ $pass->lead_passenger ? 'Lead Passenger' : (['2nd', '3rd', '4th', '5th', '6th', '7th', '8th'][$pass->passenger_order - 2] ?? '8th') . ' Passenger' }} Paid:</td>
                                            <td width="15%" class="bold underline">{{ $passenger['formatted_balance'] }}</td>
                                            <td width="20%">&nbsp;</td>
                                            <td width="15%">&nbsp;</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                @endforeach

                <tr>
                    <td colspan="2">
                        <table width="100%">
                            <tr>
                                <td width="50%"></td>
                                <td colspan="4" style="padding: 0; margin: 0; vertical-align: top;" width="50%">
                                    <table width="100%">
                                        <tr>
                                            <td width="30%" class="bold underline">Grand Total Paid:</td>
                                            <td width="15%" class="bold underline">{{ formatCurrency($booking->getTotalPaid())}}</td>
                                            <td width="20%">&nbsp;</td>
                                            <td width="15%">&nbsp;</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </section>


        @if ($paymentInfo['cabinType']->id == 2 || $paymentInfo['cabinType']->id == 3 )
        <section class="single-text" style="text-align: center;">
            Please note: Bed assignments in Single Ticket cabins are first come first serve on board! <br />
            You will be sharing your cabin with {{ $data['category_specs']->capacity - 1 }} other passenger(s)
        </section>
        @endif

        <section class="credit-charges">
            *All charges by credit card will be made on behalf of UMCruises UK LLP <br />
            Lower Ground Floor • 19-20 Berners Street • London • W1T 3NW • UNITED KINGDOM<br />
            UMCruises International Ltd.
        </section>
    </section>

    <div class="page-break"></div>
    <section class="post-header">
        <div>{{ formatDate($data['event']->start_date, true, true) }} – {{ formatDate($data['event']->end_date, true) }}</div>
        <div>{{ $data['event']->address }}</div>
        <div>Independence of the Seas</div>
    </section>
    <section class="body">
        <h2 style="text-align: center; margin-top:5px; margin-bottom: 0px;">BOOKING CONFIRMATION</h2>

        <table class="table-container" width="100%">
            <tr>
                <th width="50%">Date Issued:</th>
                <td width="50%" class="align-left" style="padding-left: 5px;"> {{$dateIssued}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Last Updated: {{$lastUpdated}}</td>
            </tr>
            <tr class="booking-code">
                <th class="bold">Booking Code:</th>
                <td class="bold align-left" style="padding-left: 5px;">{{ $data['booking']->booking_code }}</td>
            </tr>
            <tr>
                <th>Cabin Number:</th>
                <td class="align-left" style="padding-left: 5px;"> {{ $data['cabin_specs']->cabin_number }}</td>
            </tr>
            <tr>
                <th>Category:</th>
                <td class="align-left" style="padding-left: 5px;"> {{ $data['cabin_category']->title }}</td>
            </tr>
            <tr>
                <th>Number of Passengers:</th>
                <td class="align-left" style="padding-left: 5px;"> {{ $data['category_specs']->capacity }}</td>
            </tr>
            <tr>
                <th>Notes:</th>
                <td class="align-left" style="padding-left: 5px;"></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <th class="underline bold">Grand Total Booking Price:</th>
                <td class="align-left underline bold" style="padding-left: 5px;"> {{ formatCurrency($booking->getGrandTotal()) }}</td>
            </tr>

            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
        <div class="add-pax center" style="text-align: center;">
            To add or correct Passenger Information please visit:
            <a href="{{ $data['addpax_url'] }}" target="_blank">AddPax.com</a>
            <br><br>
        </div>

        <table width="100%" cellspacing="0" cellpadding="5">
            @foreach ($data['passengers'] as $index => $passenger)
            {{-- Check if the index is even or odd to determine the row structure --}}
            {{-- If even, start a new row --}}
            @if ($index % 2 == 0)
            <tr>
                @endif

                <td width="50%" valign="top" style="padding: 10px;">
                    <table width="100%" cellspacing="0" cellpadding="3" style="table-layout: fixed;">
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
            @endforeach
        </table>


</body>

</html>