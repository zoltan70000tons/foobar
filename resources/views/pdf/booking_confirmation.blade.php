<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: Verdana, Geneva, sans-serif;
            margin: 20px;
            padding: 20px;
            max-width: 800px;
        }

        .header {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }

        .section {
            /* margin-top: 20px;
            padding: 10px; */
            /* border-bottom: 1px solid #ccc; */
        }

        .details {
            display: flex;
            justify-content: space-between;
        }

        .bold {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .passengers-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 10px;
        }

        .passenger-detail {
            width: 48%;
            box-sizing: border-box;
            padding: 10px;
            border: 1px solid #ddd;
            flex-grow: 1;
        }


        .passenger-title {
            font-weight: bold;
            margin-bottom: 1rem;
        }

        /* .passenger-detail,
        .payment-detail {
            width: 50%;
            box-sizing: border-box;
        } */
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ $data['logo'] }}" width="650px" alt="UMCruises Logo">
        <br />
        <br />
        <div><span class="bold">Dates:</span> January 30 – February 3, 2025</div>
        <div><span class="bold">Route:</span> {{ $data['event']->address }}</div>
        <div><span class="bold">Ship:</span> Independence of the Seas</div>
    </div>

    <div class="section">
        <h2>Booking Confirmation</h2>
        <div class="details">
            <div><span class="bold">Date Issued:</span> Jan 29, 2025</div>
            <div><span class="bold">Booking Code:</span>{{ $data['booking']->booking_code }}</div>
        </div>
        <div class="details">
            <div><span class="bold">Cabin Number:</span> {{ $data['cabin_specs']->cabin_number }}</div>
            <div><span class="bold">Category:</span> {{ $data['category_specs']->category_code }} – {{ $data['category_specs']->category_name }}</div>
        </div>
        <div class="details">
            <div><span class="bold">Number of Passengers:</span> {{ $data['category_specs']->capacity }}</div>
        </div>
    </div>

    <h3>Passenger Details:</h3>
    <table width="100%" border="0" cellspacing="0" cellpadding="5">
        <tr>
            @foreach ($data['passengers'] as $index => $passenger)
            @if ($index % 2 == 0) <!-- Abre una nueva fila cada 2 pasajeros -->
        <tr style="margin-bottom: 40px;">
            @endif
            <td width="50%" valign="top" style="">
                <div class="passenger-title">
                    <span class="bold">
                        {{ $index == 0 ? 'Lead Passenger:' : 'Passenger ' . ($index + 1) . ':' }}
                    </span>
                </div>
                <div><span class="bold">First Name:</span> {{ $passenger['first_name'] }}</div>
                <div><span class="bold">Middle Name:</span> {{ $passenger['middle_name'] }}</div>
                <div><span class="bold">Last Name:</span> {{ $passenger['last_name'] }}</div>
                <div><span class="bold">Date of Birth:</span> {{ $passenger['dob'] }}</div>
                <div><span class="bold">Citizenship:</span> {{ $passenger['citizenship'] }}</div>
                <div><span class="bold">Address:</span> {{ $passenger['address'] }}</div>
                <div><span class="bold">Phone:</span> {{ $passenger['phone'] }}</div>
                <div><span class="bold">Emergency Contact:</span> {{ $passenger['emergency_contact'] }}</div>
            </td>
            @if ($index % 2 == 1 || $loop->last) 
            <br />
            <br />
        </tr>
        @endif
        @endforeach
        </tr>
    </table>



    <h3>Individual Payment Detail:</h3>
    <table width="100%" border="0" cellspacing="0" cellpadding="5">
        <tr>
            @foreach ($paymentInfo['passengers'] as $index => $passenger)
            @if ($index % 2 == 0)
        <tr style="margin-bottom: 40px;">
            @endif
            <td width="50%" valign="top" >
                <div class="passenger-title">
                    <span class="bold">
                        {{ $index == 0 ? 'Lead Passenger:' : 'Passenger ' . ($index + 1) . ':' }}
                    </span>
                </div>
                <div><span class="bold">Official Ticket Price:</span> USD {{ $paymentInfo['price_per_person'] }}</div>
                <div><span class="bold">Discount:</span> {{ $paymentInfo['total_discounts'] }}</div>
                <div><span class="bold">Net Ticket Price:</span> {{ $passenger['payment_method'] }}</div>
                <div><span class="bold">Taxes & Fees:</span> {{ $passenger['payment_method'] }}</div>
                <div><span class="bold">Net Ticket Price:</span> {{ $passenger['payment_method'] }}</div>
                <div><span class="bold">Single Traveler SurCharge:</span> {{ $passenger['payment_method'] }}</div>
                <div><span class="bold">Payment Method:</span> {{ $passenger['payment_method'] }}</div>

            </td>
            @if ($index % 2 == 1 || $loop->last) 
            <br />
            <br />
        </tr>
        @endif
        @endforeach
        </tr>
    </table>

    <div class="section">
        <h3>Total Payment Summary</h3>
        <div class="details">
            <div class="bold">Grand Total Booking Price:</div>
            <div class="text-right"> {{ $paymentInfo['amount_to_pay'] }}</div>
        </div>
        <div class="details">
            <div class="bold">Total Paid:</div>
            <div class="text-right"> {{ $paymentInfo['paid_amount'] }}</div>
        </div>
    </div>
</body>

</html>