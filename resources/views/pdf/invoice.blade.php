<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ $invoiceNumber }} – Callytics Invoice</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 13px;
      color: #10233f;
      background: #ffffff;
    }

    /* ── Header ───────────────────────────────────── */
    .header {
      background: linear-gradient(135deg, #10233f 0%, #1e3a5f 100%);
      padding: 28px 36px;
      border-radius: 0 0 8px 8px;
    }
    .header-inner {
      display: table;
      width: 100%;
    }
    .header-left { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; }
    .brand-name {
      font-size: 22px;
      font-weight: 700;
      color: #ffffff;
      letter-spacing: -0.02em;
    }
    .brand-sub { font-size: 11px; color: #94a3b8; margin-top: 2px; }
    .invoice-label { font-size: 28px; font-weight: 700; color: #f97316; }
    .invoice-meta { font-size: 11px; color: #94a3b8; margin-top: 4px; }
    .invoice-meta span { color: #e2e8f0; font-weight: 600; }

    /* ── Body ─────────────────────────────────────── */
    .body { padding: 30px 36px; }

    /* ── Status badge ─────────────────────────────── */
    .badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      background: #dcfce7;
      color: #16a34a;
      border: 1px solid #bbf7d0;
    }

    /* ── Info grid ────────────────────────────────── */
    .info-table { width: 100%; border-collapse: collapse; margin: 24px 0; }
    .info-table td { vertical-align: top; width: 50%; padding: 0; }
    .info-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 16px 18px;
      margin-right: 10px;
    }
    .info-box.right { margin-right: 0; margin-left: 10px; }
    .info-box-title {
      font-size: 9px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #94a3b8;
      margin-bottom: 10px;
    }
    .info-box p { font-size: 12px; color: #374151; margin-bottom: 4px; line-height: 1.5; }
    .info-box strong { color: #10233f; }

    /* ── Section title ────────────────────────────── */
    .section-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #94a3b8;
      margin-bottom: 10px;
      padding-bottom: 6px;
      border-bottom: 1px solid #e2e8f0;
    }

    /* ── Items table ──────────────────────────────── */
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table thead th {
      background: #10233f;
      color: #ffffff;
      font-size: 11px;
      font-weight: 600;
      padding: 11px 14px;
      text-align: left;
    }
    .items-table thead th.right { text-align: right; }
    .items-table tbody td {
      padding: 13px 14px;
      font-size: 12px;
      color: #374151;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: top;
    }
    .items-table tbody td.right { text-align: right; }
    .items-table tbody tr:last-child td { border-bottom: none; }
    .item-name { font-weight: 700; color: #10233f; font-size: 13px; }
    .item-desc { font-size: 11px; color: #6b7280; margin-top: 3px; }

    /* ── Totals ───────────────────────────────────── */
    .totals-wrap { margin-top: 16px; }
    .totals-table { width: 260px; margin-left: auto; border-collapse: collapse; }
    .totals-table td { padding: 8px 0; font-size: 12px; color: #374151; }
    .totals-table td.right { text-align: right; }
    .totals-table tr.divider td { border-top: 1px solid #e2e8f0; }
    .totals-table tr.grand td {
      font-size: 15px;
      font-weight: 700;
      color: #10233f;
      padding-top: 10px;
    }
    .amount-highlight {
      color: #f97316;
      font-size: 17px;
      font-weight: 700;
    }

    /* ── Payment IDs ──────────────────────────────── */
    .payment-ids {
      margin-top: 24px;
      padding: 14px 18px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 11px;
      color: #6b7280;
      word-break: break-all;
    }
    .payment-ids p { margin-bottom: 5px; }
    .payment-ids p:last-child { margin-bottom: 0; }
    .payment-ids strong { color: #374151; }

    /* ── Footer ───────────────────────────────────── */
    .footer {
      margin-top: 30px;
      padding-top: 16px;
      border-top: 1px solid #e2e8f0;
      font-size: 10px;
      color: #94a3b8;
      text-align: center;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="header">
    <div class="header-inner">
      <div class="header-left">
        <div class="brand-name">Callytics</div>
        <div class="brand-sub">
          @if($type === 'addon')
            Add-on SIM Purchase Invoice
          @else
            Subscription Invoice
          @endif
        </div>
      </div>
      <div class="header-right">
        <div class="invoice-label">INVOICE</div>
        <div class="invoice-meta">
          Number: <span>{{ $invoiceNumber }}</span><br>
          Date: <span>{{ $paidAt }}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="body">

    <!-- Status badge -->
    <div style="margin-bottom: 20px;">
      <span class="badge">&#10003; Payment Confirmed</span>
    </div>

    <!-- Billed To / Payment Info -->
    <table class="info-table">
      <tr>
        <td>
          <div class="info-box">
            <div class="info-box-title">Billed To</div>
            <p><strong>{{ $organization?->name ?? 'Organization' }}</strong></p>
            <p>{{ $organization?->email ?? '-' }}</p>
            @if($organization?->mobile)
              <p>{{ $organization->mobile }}</p>
            @endif
          </div>
        </td>
        <td>
          <div class="info-box right">
            <div class="info-box-title">Payment Details</div>
            <p>Status: <strong style="color:#16a34a;">Paid</strong></p>
            <p>Currency: <strong>{{ $currency }}</strong></p>
            <p>Method: <strong>Razorpay</strong></p>
            <p>Date: <strong>{{ $paidAt }}</strong></p>
          </div>
        </td>
      </tr>
    </table>

    <!-- Line items -->
    <div class="section-title">Invoice Items</div>

    <table class="items-table">
      <thead>
        <tr>
          <th>Description</th>
          <th>Billing Cycle</th>
          <th class="right">SIMs</th>
          <th class="right">Rate / SIM</th>
          <th class="right">Amount</th>
        </tr>
      </thead>
      <tbody>
        @if($type === 'addon')
          <tr>
            <td>
              <div class="item-name">Add-on SIM Pack – {{ $planName }}</div>
              <div class="item-desc">
                Service period: {{ $period }}<br>
                Prorated for {{ $remainingDays }} remaining day(s) in current billing cycle
              </div>
            </td>
            <td>{{ $billingCycle }}</td>
            <td class="right">{{ $simQuantity }}</td>
            <td class="right">{{ $currency }} {{ number_format($pricePerSim, 2) }}</td>
            <td class="right">{{ $currency }} {{ number_format($amount, 2) }}</td>
          </tr>
        @else
          <tr>
            <td>
              <div class="item-name">{{ $planName }}</div>
              <div class="item-desc">Service period: {{ $period }}</div>
            </td>
            <td>{{ $billingCycle }}</td>
            <td class="right">{{ $simQuantity }}</td>
            <td class="right">{{ $currency }} {{ number_format($pricePerSim, 2) }}</td>
            <td class="right">{{ $currency }} {{ number_format($amount, 2) }}</td>
          </tr>
        @endif
      </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-wrap">
      <table class="totals-table">
        <tr>
          <td>Subtotal</td>
          <td class="right">{{ $currency }} {{ number_format($amount, 2) }}</td>
        </tr>
        <tr>
          <td>Tax (0%)</td>
          <td class="right">{{ $currency }} 0.00</td>
        </tr>
        <tr class="divider grand">
          <td>Total Paid</td>
          <td class="right amount-highlight">{{ $currency }} {{ number_format($amount, 2) }}</td>
        </tr>
      </table>
    </div>

    <!-- Payment reference IDs -->
    <div class="payment-ids">
      <p><strong>Razorpay Order ID:</strong> {{ $orderId }}</p>
      <p><strong>Razorpay Payment ID:</strong> {{ $paymentId }}</p>
    </div>

    <!-- Footer -->
    <div class="footer">
      <p>Thank you for your business! This is a computer-generated invoice and does not require a signature.</p>
      <p style="margin-top:4px;">Callytics &mdash; Call Analytics &amp; SIM Management Platform</p>
    </div>

  </div>
</body>
</html>
