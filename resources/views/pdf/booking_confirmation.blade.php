<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$data['booking']->booking_code}}</title>
    <style>
        @page {
            margin: 5;
        }

        body {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 0.6rem;
            margin: 20px;
            padding: 20px;
            /*max-width: 800px */
            ;
        }

        .header {
            text-align: center;
            font-weight: bold;
        }

        .table-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table-container th {
            text-align: right;
        }

        /* .table-container th,
        .table-container td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        } */

        /* .table-container th {
            background-color: #f2f2f2;
        } */



        .table-container th,
        .table-container td {
            font-weight: normal;

            /* border: 1px solid #ddd; */
        }

        .bold {
            font-weight: bold !important;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: gray;
        }

        .page-break {
            page-break-after: always;
        }

        .add_pax {
            font-size: 1.3rem;
            color: red;
            text-align: center;
        }

        .passenger_title {
            text-decoration: underline;
            font-size: 1rem;
        }

        .subrow {
            padding-left: 10px;
        }
    </style>
</head>

<body>
    <header class="header">
        <img src="{{ $data['logo'] }}" width="450px" alt="UMCruises Logo">
        <div>January 30 – February 3, 2025</div>
        <div>{{ $data['event']->address }}</div>
        <div>Independence of the Seas</div>
    </header>
    <section class="body">
        <h2 style="text-align: center;">BOOKING CONFIRMATION</h2>

        <table class="table-container" width="100%">
            <tr>
                <th width="50%">Date Issued:</th>
                <td width="50%"> Jan 29, 2025</td>
            </tr>
            <tr>
                <th>Last Updated:</th>
                <td> Jan 29, 2025</td>
            </tr>
            <tr>
                <th class="bold">Booking Code:</th>
                <td class="bold">{{ $data['booking']->booking_code }}</td>
            </tr>
            <tr>
                <th>Cabin Number:</th>
                <td> {{ $data['cabin_specs']->cabin_number }}</td>
            </tr>
            <tr>
                <th>Category:</th>
                <td> {{ $data['category_specs']->category_code }} – {{ $data['category_specs']->category_name }}</td>
            </tr>
            <tr>
                <th>Number of Passengers:</th>
                <td> {{ $data['category_specs']->capacity }}</td>
            </tr>
            <tr>
                <th>Notes:</th>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <th class="bold">Grand Total Booking Price:</th>
                <td class="bold"> {{$paymentInfo['grand_total']}}</td>
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
                            <td width="12.5%">Discounts</td>
                            <td width="12.5%"></td>
                            <td width="12.5%"></td>
                            <td width="12.5%">{{ $data['booking']->payment_plan }}</td>
                            <td width="12.5%"></td>
                            <td width="12.5%">{{ $passenger['payment_method'] }}</td>
                            <td width="12.5%"></td>
                        </tr>
                        @foreach ($passenger['passenger_discounts'] as $adjustment )
                        <tr>
                            <td width="12.5%"></td>
                            <td width="12.5%" class="subrow">{{$adjustment['code']}}</td>
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
                            <td width="12.5%">Net Ticket Price</td>
                            <td width="12.5%">{{formatCurrency($passenger['net_ticket_price'])}}</td>
                            <td width="12.5%"></td>
                            <td width="12.5%"></td>
                            <td width="12.5%"></td>
                            <td width="12.5%"></td>
                            <td width="12.5%"></td>
                        </tr>
                        <tr>
                            <td width="12.5%"></td>
                            <td width="12.5%">Taxes & Fees</td>
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
                            <td width="12.5%" class="subrow">{{$adjustment['code']}}</td>
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
                            <td width="12.5%" class="subrow">Total Taxes & fees:</td>
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
                                    @foreach ($pass->payments as $payment)
                                    @if ($payment->type == 'PAYMENT')
                                    <tr>
                                        <td width="25%">Paid at {{$payment->transaction_date}}</td>
                                        <td width="25%"> {{ formatCurrency($payment->amount) }}</td>
                                        <td width="25%">{{$pass->payment_method}}</td>
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

    <section style="text-align: center;">
        *All charges by credit card will be made on behalf of UMCruises UK LLP <br />
        Lower Ground Floor • 19-20 Berners Street • London • W1T 3NW • UNITED KINGDOM<br />
        UMCruises International Ltd.
    </section>

    <!-- <div>
        Lorem ipsum dolor sit, amet consectetur adipisicing elit. Iste deleniti sequi expedita repudiandae, nesciunt non est deserunt ea sed quae culpa molestiae consectetur maiores veniam porro autem quam suscipit quo!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempore eaque sunt tempora cum, iste repellendus similique quas iure ad, aliquid a dolorum. Dicta animi enim blanditiis recusandae, quis alias saepe!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Nihil aut ipsam suscipit, distinctio, officiis eveniet qui omnis ullam neque sapiente repellat. Rerum maiores aut mollitia perspiciatis velit voluptatum delectus debitis!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Non maiores quibusdam id alias voluptatem rem porro officia ullam. Temporibus sint consequuntur, reiciendis atque nostrum esse accusantium quam officia at harum.
        Lorem ipsum dolor sit, amet consectetur adipisicing elit. Iste deleniti sequi expedita repudiandae, nesciunt non est deserunt ea sed quae culpa molestiae consectetur maiores veniam porro autem quam suscipit quo!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempore eaque sunt tempora cum, iste repellendus similique quas iure ad, aliquid a dolorum. Dicta animi enim blanditiis recusandae, quis alias saepe!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Nihil aut ipsam suscipit, distinctio, officiis eveniet qui omnis ullam neque sapiente repellat. Rerum maiores aut mollitia perspiciatis velit voluptatum delectus debitis!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Non maiores quibusdam id alias voluptatem rem porro officia ullam. Temporibus sint consequuntur, reiciendis atque nostrum esse accusantium quam officia at harum.
        Lorem ipsum dolor sit, amet consectetur adipisicing elit. Iste deleniti sequi expedita repudiandae, nesciunt non est deserunt ea sed quae culpa molestiae consectetur maiores veniam porro autem quam suscipit quo!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempore eaque sunt tempora cum, iste repellendus similique quas iure ad, aliquid a dolorum. Dicta animi enim blanditiis recusandae, quis alias saepe!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Nihil aut ipsam suscipit, distinctio, officiis eveniet qui omnis ullam neque sapiente repellat. Rerum maiores aut mollitia perspiciatis velit voluptatum delectus debitis!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Non maiores quibusdam id alias voluptatem rem porro officia ullam. Temporibus sint consequuntur, reiciendis atque nostrum esse accusantium quam officia at harum.
        Lorem ipsum dolor sit, amet consectetur adipisicing elit. Iste deleniti sequi expedita repudiandae, nesciunt non est deserunt ea sed quae culpa molestiae consectetur maiores veniam porro autem quam suscipit quo!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempore eaque sunt tempora cum, iste repellendus similique quas iure ad, aliquid a dolorum. Dicta animi enim blanditiis recusandae, quis alias saepe!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Nihil aut ipsam suscipit, distinctio, officiis eveniet qui omnis ullam neque sapiente repellat. Rerum maiores aut mollitia perspiciatis velit voluptatum delectus debitis!
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Non maiores quibusdam id alias voluptatem rem porro officia ullam. Temporibus sint consequuntur, reiciendis atque nostrum esse accusantium quam officia at harum.
    </div> -->

    <div class="page-break"></div>
    <header class="header">
        <img src="{{ $data['logo'] }}" width="450px" alt="UMCruises Logo">
        <div>January 30 – February 3, 2025</div>
        <div>{{ $data['event']->address }}</div>
        <div>Independence of the Seas</div>
    </header>
    <section class="body">
        <h2 style="text-align: center;">BOOKING CONFIRMATION</h2>

        <table class="table-container" width="100%">
            <tr>
                <th class="bold">Booking Code:</th>
                <td class="bold">{{ $data['booking']->booking_code }}</td>
            </tr>
            <tr>
                <th>Cabin Number:</th>
                <td> {{ $data['cabin_specs']->cabin_number }}</td>
            </tr>
            <tr>
                <th>Category:</th>
                <td> {{ $data['category_specs']->category_code }} – {{ $data['category_specs']->category_name }}</td>
            </tr>
            <tr>
                <th>Number of Passengers:</th>
                <td> {{ $data['category_specs']->capacity }}</td>
            </tr>
        </table>
        <div class="add_pax"> To add or correct Passenger Information please visit: addpax.com</div>

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


        <footer style="text-align:center;">


        </footer>
</body>

</html>