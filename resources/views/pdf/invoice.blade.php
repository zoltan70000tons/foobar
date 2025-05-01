<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>{{ __('pdfs.invoice') }} {{ $booking_code }}</title>
  <style>
    @page {
      margin: 40px 30px 120px;
    }

    body {
      font-family: Verdana, Geneva, sans-serif;
      font-size: 0.7rem;
      line-height: 1.3;
      color: #000;
    }

    /* logo */
    header {
      text-align: center;
      margin-bottom: 3rem;
    }

    header img {
      width: 600px;
      height: auto;
    }

    .lead-passenger {
      margin: 20px 0;
    }

    /* Invoice title */
    h1 {
      text-align: center;
      font-size: 1.4rem;
      margin: 0 0 1rem;
    }

    /* Invoice # / Date */
    .table-meta {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1rem;
    }

    .table-meta td {
      border: 1px solid #000;
      padding: 6px 8px;
      font-size: 0.7rem;
    }

    .table-meta .left {
      text-align: left;
    }

    .table-meta .right {
      text-align: right;
    }

    /* Banner below meta */
    .table-banner {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1rem;
    }

    .table-banner td {
      border: 1px solid #000;
      padding: 6px 0;
      text-align: center;
      font-weight: bold;
      font-size: 1.5rem;
    }

    /* Line-items table */
    .table-bordered {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1rem;
    }

    .table-bordered th,
    .table-bordered td {
      border: 1px solid #000;
      padding: 6px 8px;
      font-size: 0.7rem;
    }

    .table-bordered th {
      background: #f5f5f5;
      font-weight: bold;
    }

    .right {
      text-align: right;
    }

    .left {
      text-align: left;
    }

    .center {
      text-align: center;
    }

    .bold {
      font-weight: bold;
    }

    .underline {
      text-decoration: underline;
    }

    .bank-block {
      width: 100%;
      display: inline-block;
      margin-top: 1rem;
    }

    .bank-block__section {
      width: 45%;
      display: inline-table;
      vertical-align: top;
    }

    .bank-block__section td {
      vertical-align: top;
      padding: 5px 0;
    }

    .table-bank {
      width: 50%;
      border-collapse: collapse;
      margin-top: 0.5rem;
    }

    .table-bank td {
      padding: 4px 6px;
      font-size: 0.7rem;
      vertical-align: top;
    }

    .table-bank td:first-child {
      font-weight: bold;
      white-space: nowrap;
    }

    /* Send-only notice */
    .notice {
      border: 2px solid #000;
      padding: 8px;
      text-align: center;
      font-weight: bold;
      font-size: 1.5rem;
      margin: 1rem 0;
    }
  </style>
</head>

<body>

  <header>
    <img src="{{ $logo }}" alt="70000TONS OF METAL Logo">
  </header>

  <div class="lead-passenger left">
    {{ $leadPassenger->first_name }} {{ $leadPassenger->last_name }}<br>
    {{ $leadPassenger->address_first }}
    @if($leadPassenger->address_second) {{ $leadPassenger->address_second }} @endif<br>
    {{ $leadPassenger->city }}, {{ $leadPassenger->state }} {{ $leadPassenger->postal_code }}<br><br>

    <span class="bold">
      {{
    \Symfony\Component\Intl\Countries::alpha3CodeExists($leadPassenger->country)
        ? \Symfony\Component\Intl\Countries::getAlpha3Name($leadPassenger->country, $language)
        : $fallbackList[$leadPassenger->country] ?? $leadPassenger->country
      }}
    </span>
  </div>

  <h1>{{ __('pdfs.invoice') }}</h1>

  {{-- Invoice # / Date --}}
  <table class="table-meta">
    <tr>
      <td class="left" width="50%">
        {{ __('pdfs.invoice_number') }}: <strong>{{ $booking_code }}inv</strong>
      </td>
      <td class="right" width="50%">
        {{ __('pdfs.date') }}: <strong>{{ $today }}</strong>
      </td>
    </tr>
  </table>

  {{-- Banner --}}
  <table class="table-banner">
    <tr>
      <td>{{ __('pdfs.amounts_in_usd') }}</td>
    </tr>
  </table>

  {{-- Line items + summary --}}
  <table class="table-bordered">
    <thead>
      <tr>
        <th class="center">{{ __('pdfs.booking_code') }}</th>
        <th class="center">{{ __('pdfs.lead_passenger') }}</th>
        <th class="right">{{ __('pdfs.number_of_pax') }}</th>
        <th class="right">{{ __('pdfs.price_per_pax') }}</th>
        <th class="right">{{ __('pdfs.total') }}</th>
      </tr>
    </thead>
    <tbody>
      {{-- data row --}}
      <tr>
        <td class="center">{{ $booking_code }}</td>
        <td class="center">{{ $leadPassenger->first_name }} {{ $leadPassenger->last_name }}</td>
        <td class="right">{{ $numPax }}</td>
        <td class="right">{{ $formatted['price_per_pax'] }}</td>
        <td class="right">{{ $formatted['total'] }}</td>
      </tr>

      {{-- summary rows --}}
      <tr>
        <td colspan="4" class="bold underline">{{ __('pdfs.invoice_amount') }}</td>
        <td class="right">{{ $formatted['invoice_amount'] }}</td>
      </tr>
      <tr>
        <td colspan="4" class="bold underline">{{ __('pdfs.amount_due_now') }}</td>
        <td class="right bold">{{ $formatted['amount_due_now'] }}</td>
      </tr>
    </tbody>
  </table>

  {{-- Transfer instructions --}}
  <p style="font-size:0.75rem; line-height:1.3; margin-bottom:0.5rem;">
    {{ __('pdfs.please_transfer') }} <span class="bold">USD {{ number_format($grandTotal,2,'.',',') }}
      ({{ $amountInWords }}</span> <span class="bold underline">{{ __('pdfs.united_states_dollars') }}</span>)
    {{ __('pdfs.into_account') }}:
  </p>

  {{-- Bank details block --}}
  <div class="bank-block">
    <table class="bank-block__section">
      <tr>
        <td>
          <strong>{{ __('pdfs.account_holder') }}:</strong>
        </td>
        <td>
          {{ $bank['holder'] }}<br>
          {{ $bank['holder_address_1'] }}<br>
          {{ $bank['holder_address_2'] }}<br>
          {{ $bank['holder_address_3'] }}<br>
        </td>
      </tr>
    </table>

    <table class="bank-block__section">
      <tr>
        <td>
          <strong>{{ __('pdfs.bank_name') }}:</strong>
        </td>
        <td>
          {{ $bank['bank_name'] }}
        </td>
      </tr>

      <tr>
        <td>
          <strong>{{ __('pdfs.bank_address') }}:</strong>
        </td>

        <td>
          {{ $bank['branch_address_1'] }}<br>
          {{ $bank['branch_address_2'] }}<br>
          {{ $bank['branch_address_3'] }}
        </td>
      </tr>

      <tr>
        <td>
          <strong>{{ __('pdfs.account_no') }}:</strong>
        </td>
        <td>
          {{ $bank['account_no'] }}
        </td>
      </tr>

      <tr>
        <td>
          <strong>{{ __('pdfs.routing_number') }}:</strong>
        </td>

        <td>
          {{ $bank['routing'] }}
        </td>
      </tr>

      <tr>
        <td>
          <strong>{{ __('pdfs.swift') }}:</strong>
        </td>
        <td>
          {{ $bank['swift'] }}
        </td>
      </tr>
    </table>
  </div>

  {{-- Send-only notice --}}
  <div class="notice">
    {{ __('pdfs.send_usd_only') }}
  </div>
</body>

</html>