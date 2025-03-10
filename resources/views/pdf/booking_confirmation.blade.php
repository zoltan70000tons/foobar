<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$data['booking']->booking_code}}</title>
    <style>
        @page {
            margin-top: 10px;
            margin-bottom: 80px;
        }

        body {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 0.5rem;
            margin: 20px;
            padding: 20px;
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
            margin-bottom: 5px;
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

        .passenger_title {
            text-decoration: underline;
            font-size: 1rem;
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
    </style>
</head>

<body>
    <header class="header">
        <img src="{{ $data['logo'] }}" width="450px" alt="UMCruises Logo" style="margin-bottom: 1rem;">
    </header>
    <section class="body">
        <section class="post-header">
            <div>{{ formatDate($data['event']->start_date, true, true) }} – {{ formatDate($data['event']->end_date, true) }}</div>
            <div>{{ $data['event']->address }}</div>
            <div>Independence of the Seas</div>
        </section>
        <section class="body">
            <h2 style="text-align: center;">BOOKING CONFIRMATION</h2>

            <table class="table-container" width="100%">
                <tr>
                    <th width="50%">Date Issued:</th>
                    <td width="50%" class="align-left"> {{formatDate($data['booking']->created_at)}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Last Updated: {{formatDate($data['booking']->updated_at)}}</td>
                </tr>
                <!-- <tr>
                <th>Last Updated:</th>
                <td class="align-left"> {{formatDate($data['booking']->updated_at)}}</td>
            </tr> -->
                <tr>
                    <th class="bold">Booking Code:</th>
                    <td class="bold align-left">{{ $data['booking']->booking_code }}</td>
                </tr>
                <tr>
                    <th>Cabin Number:</th>
                    <td class="align-left"> {{ $data['cabin_specs']->cabin_number }}</td>
                </tr>
                <tr>
                    <th>Category:</th>
                    <td class="align-left"> {{ $data['category_specs']->category_code }} – {{ $data['category_specs']->category_name }}</td>
                </tr>
                <tr>
                    <th>Number of Passengers:</th>
                    <td class="align-left"> {{ $data['category_specs']->capacity }}</td>
                </tr>
                <tr>
                    <th>Notes:</th>
                    <td class="align-left"></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>

                <tr>
                    <th class="bold">Grand Total Booking Price:</th>
                    <td class="bold align-left"> {{$paymentInfo['grand_total']}}</td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>

                @foreach ($paymentInfo['passengers'] as $passenger)
                {{ $pass = $passenger['passenger'] }}
                <tr>
                    <td colspan="2"></td>
                </tr>

                <tr>
                    <td colspan="2">
                        <table width="100%">
                            <tr>
                                <td width="12.5%" class="bold">{{ $pass->lead_passenger ? 'Lead Passenger' : (['2nd', '3rd', '4th', '5th', '6th', '7th', '8th'][$pass->passenger_order - 2] ?? '8th') . ' Passenger' }}
                                </td>
                                <td width="12.5%">Official Ticket Price:</td>
                                <td width="12.5%">{{ formatCurrency($data['booking']->cabin->category->price) }}</td>
                                <td width="12.5%"></td>
                                <td width="12.5%"><span class="bold underline">Installment Plan:</span></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"><span class="bold underline">Form Of Payment:</span></td>
                                <td width="12.5%"></td>
                            </tr>
                            <tr>
                                <td width="12.5%"></td>
                                <td width="12.5%">Discounts:</td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%">{{ $passenger['payment_method'] }}</td>
                                <td width="12.5%"></td>
                            </tr>
                            @foreach ($passenger['passenger_discounts'] as $adjustment )
                            <tr>
                                <td width="12.5%"></td>
                                <td width="12.5%" class="subrow">{{formatFeeName($adjustment['code'])}}:</td>
                                <td width="12.5%">{{$adjustment['formated_amount']}}</td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                            </tr>
                            @endforeach

                            <tr>
                                <td width="12.5%"></td>
                                <td width="12.5%" class="subrow">Total Discount:</td>
                                <td width="12.5%">{{$paymentInfo['total_discounts']}}</td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                            </tr>

                            <tr>
                                <td width="12.5%"></td>
                                <td width="12.5%">Net Ticket Price:</td>
                                <td width="12.5%">{{formatCurrency($passenger['net_ticket_price'])}}</td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                            </tr>
                            <tr>
                                <td width="12.5%"></td>
                                <td width="12.5%">Taxes & Fees:</td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                            </tr>
                            @foreach ($paymentInfo['addons_detail'] as $adjustment)
                            <tr>
                                <td width="12.5%"></td>
                                <td width="12.5%" class="subrow">{{formatFeeName($adjustment['code'])}}:</td>
                                <td width="12.5%">{{$adjustment['formated_amount']}}</td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                            </tr>
                            @endforeach

                            <tr>
                                <td width="12.5%"></td>
                                <td width="12.5%" class="subrow">Total Taxes & Fees:</td>
                                <td width="12.5%">{{$paymentInfo['total_addons']}}</td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                            </tr>
                            <tr>
                                <td width="12.5%"></td>
                                <td width="12.5%" class="bold">Total Ticket Price:</td>
                                <td width="12.5%">{{ $passenger['total_ticket_price'] }}</td>
                                <td width="12.5%"></td>
                                <td width="12.5%" colspan="4">
                                    <table width="100%">
                                        @foreach ($passenger['payments'] as $payment)
                                        @if ($payment['overdue'])
                                        <tr>
                                            <td width="25%" class="bold">Due immediately</td>
                                            <td width="25%"> {{formatDate($payment['due_date'])}}</td>
                                            <td width="25%"></td>
                                            <td width="25%"></td>
                                        </tr>
                                        @elseif ($payment['paid'])
                                        <tr>
                                            <td width="25%">Paid at {{ formatDate($payment['paid_at']) }}</td>
                                            <td width="25%">{{ formatCurrency($payment['amount_paid']) }}</td>
                                            <td width="25%">{{ $pass->payment_method == 'CREDIT_CARD' ? 'Credit Card*' : 'Pay In Full' }}</td>
                                            <td width="25%"></td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </table>

                                </td>
                            </tr>
                            <tr>
                                <td width="12.5%">&nbsp;</td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                            </tr>
                            <tr>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                                <td colspan="2" width="25%" style="text-align:right;"><span class="bold" style="text-align: right;">Total {{ $pass->lead_passenger ? 'Lead Passenger' : (['2nd', '3rd', '4th', '5th', '6th', '7th', '8th'][$pass->passenger_order - 2] ?? '8th') . ' Passenger' }} Paid</span></td>
                                <td width="12.5%">{{ formatCurrency($pass->passenger_balance) }}</td>
                                <td width="12.5%"></td>
                                <td width="12.5%"></td>
                            </tr>

                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                </tr>
                @endforeach
            </table>

            <table class="table-container">
                <tr>
                    <td width="12.5%"></td>
                    <td width="12.5%"></td>
                    <td width="12.5%"></td>
                    <td width="12.5%"></td>
                    <td width="12.5%" class="bold">Grand Total Paid</td>
                    <td width="12.5%" class="bold">{{$paymentInfo['paid_amount']}}</td>
                    <td width="12.5%"></td>
                    <td width="12.5%"></td>
                </tr>
            </table>
        </section>


        @if ($paymentInfo['cabinType']->id == 2 || $paymentInfo['cabinType']->id == 3 )
        <section class="single-text">
            Please note: Bed assignments in Single Ticket cabins are first come first serve on board! <br />
            You will be sharing your cabin with 3 other passenger(s)
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
        <h2 style="text-align: center;">BOOKING CONFIRMATION</h2>

        <table class="table-container" width="100%">
            <tr>
                <th class="bold">Booking Code:</th>
                <td class="bold align-left">{{ $data['booking']->booking_code }}</td>
            </tr>
            <tr>
                <th>Cabin Number:</th>
                <td class="align-left"> {{ $data['cabin_specs']->cabin_number }}</td>
            </tr>
            <tr>
                <th>Category:</th>
                <td class="align-left"> {{ $data['category_specs']->category_code }} – {{ $data['category_specs']->category_name }}</td>
            </tr>
            <tr>
                <th>Number of Passengers:</th>
                <td class="align-left"> {{ $data['category_specs']->capacity }}</td>
            </tr>
        </table>
        <div class="add-pax"> To add or correct Passenger Information please visit: addpax.com</div>

        <table width="100%" border="0" cellspacing="0" cellpadding="5">
            @foreach ($data['passengers'] as $index => $passenger)
            @if ($index % 2 == 0)
            <tr>
                @endif

                <td width="50%" valign="top" style="padding: 10px;">
                    <table width="100%" cellspacing="0" cellpadding="3">
                        <tr>
                            <td colspan="2" class="passenger_title">
                                <strong>{{ $passenger->lead_passenger ? 'Lead Passenger' : (['2nd', '3rd', '4th', '5th', '6th', '7th', '8th'][$passenger->passenger_order - 2] ?? '8th') . ' Passenger' }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>First Name:</strong></td>
                            <td>{{ $passenger['first_name'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Middle Name:</strong></td>
                            <td>{{ $passenger['middle_name'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Name:</strong></td>
                            <td>{{ $passenger['last_name'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Survivor Number:</strong></td>
                            <td>{{ $passenger['survivor_number'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Survivor Status:</strong></td>
                            <td>{{ $passenger['survivor_status'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Date of Birth:</strong></td>
                            <td>{{ $passenger['dob'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Citizenship:</strong></td>
                            <td>{{ $passenger['citizenship'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Address:</strong></td>
                            <td>{{ $passenger['address_first'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>City:</strong></td>
                            <td>{{ $passenger['city'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>State/Province/Region:</strong></td>
                            <td>{{ $passenger['state'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Postal Code:</strong></td>
                            <td>{{ $passenger['postal_code'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Country:</strong></td>
                            <td>{{ $passenger['country'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $passenger['email'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ $passenger['phone'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Emergency Name:</strong></td>
                            <td>{{ $passenger['emergency_c_name'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Emergency Phone:</strong></td>
                            <td>{{ $passenger['emergency_c_phone'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Special Requests:</strong></td>
                            <td>{{ $passenger['special_request'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Onboard Credit:</strong></td>
                            <td>{{ $passenger['onboard_credit'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Reason For Credit:</strong></td>
                            <td>{{ $passenger['credit_reason'] }}</td>
                        </tr>
                    </table>
                </td>

                @if ($index % 2 == 1 || $loop->last)
            </tr>
            @endif
            @endforeach
        </table>

</body>

</html>