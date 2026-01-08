@extends('emails.layouts.systemLayout')

@section('title', 'Cabin Inventory Integrity Report')

@section('content')
  <style type="text/css">
    .container {
      max-width: 100%;
      width: 100%;
      background-color: #ffffff;
      box-sizing: border-box;
    }

    .inventory-report-container {
      color: #000000;
    }

    .inventory-report-container p,
    .inventory-report-container h2,
    .inventory-report-container h3 {
      color: #000000;
    }

    .inventory-report-container .table-bordered {
      table-layout: fixed;
      width: 100%;
    }

    .inventory-report-container .table-bordered th,
    .inventory-report-container .table-bordered td {
      font-size: 12px;
      color: #000000;
      word-break: break-word;
      word-wrap: break-word;
    }

    .inventory-report-container .table-bordered th {
      background-color: #ffffff;
    }

    .inventory-report-container .table-bordered tbody tr:nth-child(odd) {
      background-color: #ffffff;
    }

    .inventory-report-container .table-bordered td,
    .inventory-report-container .table-bordered th {
      border-color: #000000;
    }

    .inventory-report-container .table-bordered th:nth-child(1),
    .inventory-report-container .table-bordered td:nth-child(1) {
      width: 16%;
    }

    .inventory-report-container .table-bordered th:nth-child(2),
    .inventory-report-container .table-bordered td:nth-child(2) {
      width: 8%;
    }

    .inventory-report-container .table-bordered th:nth-child(3),
    .inventory-report-container .table-bordered td:nth-child(3) {
      width: 10%;
    }

    .inventory-report-container .table-bordered th:nth-child(4),
    .inventory-report-container .table-bordered td:nth-child(4) {
      width: 10%;
    }

    .inventory-report-container .table-bordered th:nth-child(5),
    .inventory-report-container .table-bordered td:nth-child(5) {
      width: 8%;
      text-align: center;
    }

    .inventory-report-container .table-bordered th:nth-child(6),
    .inventory-report-container .table-bordered td:nth-child(6) {
      width: 8%;
      text-align: center;
    }

    .inventory-report-container .table-bordered th:nth-child(7),
    .inventory-report-container .table-bordered td:nth-child(7) {
      width: 8%;
      text-align: center;
    }

    .inventory-report-container .table-bordered th:nth-child(8),
    .inventory-report-container .table-bordered td:nth-child(8) {
      width: 32%;
    }
  </style>

  <div class="inventory-report-container">
    <h2>Cabin Inventory Integrity Report</h2>
    <p>Generated at: {{ $report['run_at'] ?? 'N/A' }}</p>
    <p>
      Events checked: {{ $report['summary']['events_checked'] ?? 0 }},
      Cabins checked: {{ $report['summary']['cabins_checked'] ?? 0 }},
      Issues found: {{ $report['summary']['issues_count'] ?? 0 }}
    </p>

    @if (!empty($report['message']))
      <p>{{ $report['message'] }}</p>
    @endif

    @if (($report['summary']['issues_count'] ?? 0) === 0)
      <p>No integrity issues were detected.</p>
    @else
      @php
        $issuesByEvent = collect($report['issues'] ?? [])->groupBy('event_id');
      @endphp

      @foreach ($issuesByEvent as $eventId => $issues)
        @php
          $firstIssue = $issues->first();
          $eventName = is_array($firstIssue) ? ($firstIssue['event_name'] ?? 'Unknown Event') : 'Unknown Event';
          $eventStatus = is_array($firstIssue) ? ($firstIssue['event_status'] ?? 'N/A') : 'N/A';
        @endphp
        <h3>{{ $eventName }} ({{ $eventStatus }})</h3>
        <table class="table-bordered">
          <thead>
            <tr>
              <th>Cabin (cat code, capacity, number and ID)</th>
              <th>Status</th>
              <th>Type</th>
              <th>Category</th>
              <th>Inventory</th>
              <th>Bookings</th>
              <th>Passengers</th>
              <th>Message</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($issues as $issue)
              <tr>
                <td>{{ $issue['cabin_number'] && $issue['cabin_id'] ? $issue['category_code'] . ' / ' . $issue['capacity_name'] . ' / ' . $issue['cabin_number'] . ' (ID: ' . $issue['cabin_id'] . ')' : 'N/A' }}</td>
                <td>{{ $issue['cabin_status'] ?? 'N/A' }}</td>
                <td>{{ $issue['cabin_type'] ?? 'N/A' }}</td>
                <td>{{ $issue['category_code'] ?? 'N/A' }}</td>
                <td>{{ $issue['inventory'] ?? 'N/A' }}</td>
                <td>{{ $issue['booking_count'] ?? 'N/A' }}</td>
                <td>{{ $issue['passenger_count'] ?? 'N/A' }}</td>
                <td>{{ $issue['message'] ?? 'N/A' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endforeach
    @endif
  </div>
@endsection

@section('regards')
  <p>This report is generated automatically by the booking admin system.</p>
@endsection
