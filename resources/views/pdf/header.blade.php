<style>
    .details-table {
        width: 30%;
        margin: 0 auto;
        line-height: 1.1px;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5px;
        margin-top: 5px;
        font-weight: normal;
    }

    header p {
        margin: 1px;
        padding: 0;
        font-size: 10px;
        font-weight: 500;
        line-height: 10px;
    }

    .confirmation-title {
        text-align: center;
        font-size: 12px;
        font-weight: bold;
        margin: 0 0;
    }
    .logo{
        margin-bottom: 3px;;
    }
</style>
<div class="header">
    <img src="{!! $logoSrc !!}" width="50%" class="logo"/>
    <p>{{ formatDate($event->start_date, true, true) }} – {{ formatDate($event->end_date, true) }}</p>
    <p>{{ $event->address }}</p>
    <p>Freedom Of The Seas</p>

    <div class="confirmation-title">BOOKING CONFIRMATION</div>

    <table class="details-table">
        <tr>
            <td style="text-align: right;" width="50%">Date Issued:</td>
            <td style="text-align: left;" width="50%">{!! $dateIssued !!}&nbsp;&nbsp;&nbsp;&nbsp;Last Updated: {!! $lastUpdated !!}</td>
        </tr>
        <tr>
            <td style="text-align: right; font-weight: bold;">Booking Code:</td>
            <td style="font-weight: bold;">{!! $bookingCode !!}</td>
        </tr>
        <tr>
            <td style="text-align: right;">Cabin Number:</td>
            <td>{!! $cabinNumber !!}</td>
        </tr>
        <tr>
            <td style="text-align: right;">Category:</td>
            <td>{!! $cabinCategory !!}</td>
        </tr>
        <tr>
            <td style="text-align: right;">Number of Passengers:</td>
            <td>{!! $capacity !!}</td>
        </tr>
        <tr>
            <td style="text-align: right;">Notes:</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td style="text-align: right;"><strong>Grand Total Booking Price:</strong></td>
            <td><strong>{{ formatCurrency($grandTotal) }}</strong></td>
        </tr>
       
    </table>
</div>