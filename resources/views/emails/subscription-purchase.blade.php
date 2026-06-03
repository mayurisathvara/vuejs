<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Confirmed – Callytics</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">

@php
  $org          = $subscription->organization;
  $currency     = $subscription->currency ?: 'INR';
  $amount       = number_format((float) $subscription->amount, 2);
  $pricePerSim  = number_format((float) $subscription->price_per_sim, 2);
  $simQty       = (int) $subscription->sim_quantity;
  $planName     = $subscription->plan_name ?? $subscription->plan?->display_name ?? 'Subscription Plan';
  $billingCycle = ucfirst((string) $subscription->billing_cycle);
  $paidAt       = $subscription->created_at?->format('d M Y, h:i A') ?? '-';
  $startDate    = $subscription->start_date?->format('d M Y') ?? '-';
  $endDate      = $subscription->end_date?->format('d M Y') ?? '-';
  $paymentId    = $subscription->razorpay_payment_id ?? '-';
  $customerName = $org?->name ?? 'Valued Customer';
  $appUrl       = rtrim(config('app.url'), '/');
@endphp

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 16px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#10233f 0%,#1e3a5f 100%);border-radius:16px 16px 0 0;padding:32px 40px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <span style="font-size:22px;font-weight:900;color:#ffffff;letter-spacing:-0.02em;">Callytics</span>
                  <p style="margin:4px 0 0;font-size:12px;color:#94a3b8;">
                    Subscription Confirmation
                  </p>
                </td>
                <td align="right">
                  <span style="background:rgba(249,115,22,0.15);border:1px solid rgba(249,115,22,0.4);color:#fb923c;font-size:11px;font-weight:700;padding:5px 12px;border-radius:20px;letter-spacing:0.06em;">
                    PAYMENT CONFIRMED
                  </span>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="background:#ffffff;padding:36px 40px;">

            <!-- Success icon -->
            <div style="text-align:center;margin-bottom:28px;">
              <div style="display:inline-block;width:64px;height:64px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:2px solid #bbf7d0;border-radius:50%;line-height:64px;font-size:28px;">&#10003;</div>
            </div>

            <!-- Greeting -->
            <h1 style="font-size:22px;font-weight:800;color:#10233f;text-align:center;margin:0 0 10px;letter-spacing:-0.02em;">
              Your Subscription is Active!
            </h1>
            <p style="font-size:15px;color:#6b7280;text-align:center;margin:0 0 28px;line-height:1.6;">
              Hi <strong style="color:#10233f;">{{ $customerName }}</strong>, your payment was successful.<br>
              Your Callytics subscription is now active.
            </p>

            <!-- Invoice number banner -->
            <div style="background:#fff7ed;border:1.5px solid #fed7aa;border-radius:10px;padding:14px 20px;text-align:center;margin-bottom:28px;">
              <p style="margin:0;font-size:11px;color:#9a3412;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;">Invoice Number</p>
              <p style="margin:4px 0 0;font-size:20px;font-weight:900;color:#ea580c;letter-spacing:0.04em;">{{ $invoiceNumber }}</p>
            </div>

            <!-- Plan details table -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
              <tr>
                <td style="padding-bottom:10px;">
                  <p style="margin:0 0 10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#94a3b8;">Plan Details</p>
                  <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                    <tr style="background:#f8fafc;">
                      <td style="padding:12px 16px;font-size:12px;color:#6b7280;font-weight:600;width:45%;">Plan</td>
                      <td style="padding:12px 16px;font-size:12px;color:#10233f;font-weight:700;">{{ $planName }}</td>
                    </tr>
                    <tr style="border-top:1px solid #e2e8f0;">
                      <td style="padding:12px 16px;font-size:12px;color:#6b7280;font-weight:600;">Billing Cycle</td>
                      <td style="padding:12px 16px;font-size:12px;color:#10233f;font-weight:700;">{{ $billingCycle }}</td>
                    </tr>
                    <tr style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                      <td style="padding:12px 16px;font-size:12px;color:#6b7280;font-weight:600;">SIM Quantity</td>
                      <td style="padding:12px 16px;font-size:12px;color:#10233f;font-weight:700;">{{ $simQty }} SIMs</td>
                    </tr>
                    <tr style="border-top:1px solid #e2e8f0;">
                      <td style="padding:12px 16px;font-size:12px;color:#6b7280;font-weight:600;">Rate per SIM</td>
                      <td style="padding:12px 16px;font-size:12px;color:#10233f;font-weight:700;">{{ $currency }} {{ $pricePerSim }}</td>
                    </tr>
                    <tr style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                      <td style="padding:12px 16px;font-size:12px;color:#6b7280;font-weight:600;">Valid From</td>
                      <td style="padding:12px 16px;font-size:12px;color:#10233f;font-weight:700;">{{ $startDate }}</td>
                    </tr>
                    <tr style="border-top:1px solid #e2e8f0;">
                      <td style="padding:12px 16px;font-size:12px;color:#6b7280;font-weight:600;">Valid Until</td>
                      <td style="padding:12px 16px;font-size:12px;color:#10233f;font-weight:700;">{{ $endDate }}</td>
                    </tr>
                    <tr style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                      <td style="padding:12px 16px;font-size:12px;color:#6b7280;font-weight:600;">Purchase Date</td>
                      <td style="padding:12px 16px;font-size:12px;color:#10233f;font-weight:700;">{{ $paidAt }}</td>
                    </tr>
                    <tr style="border-top:1px solid #e2e8f0;">
                      <td style="padding:12px 16px;font-size:12px;color:#6b7280;font-weight:600;">Payment Status</td>
                      <td style="padding:12px 16px;">
                        <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:700;padding:3px 10px;border-radius:12px;">Paid</span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <!-- Amount -->
            <div style="background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1.5px solid #fed7aa;border-radius:12px;padding:20px 24px;text-align:center;margin-bottom:28px;">
              <p style="margin:0;font-size:12px;color:#9a3412;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;">Total Amount Paid</p>
              <p style="margin:6px 0 0;font-size:32px;font-weight:900;color:#ea580c;">{{ $currency }} {{ $amount }}</p>
              <p style="margin:4px 0 0;font-size:11px;color:#c2410c;">Tax: {{ $currency }} 0.00 &bull; Inclusive of all charges</p>
            </div>

            <!-- Payment ID -->
            <p style="font-size:11px;color:#94a3b8;text-align:center;margin:0 0 24px;word-break:break-all;">
              Payment Ref: <strong style="color:#6b7280;">{{ $paymentId }}</strong>
            </p>

            <!-- CTA button -->
            <div style="text-align:center;margin-bottom:28px;">
              <a href="{{ $appUrl }}/subscription"
                 style="display:inline-block;padding:14px 32px;background:linear-gradient(90deg,#ea580c 0%,#f97316 55%,#fb923c 100%);color:#ffffff;font-size:14px;font-weight:800;text-decoration:none;border-radius:12px;box-shadow:0 4px 14px rgba(249,115,22,0.35);">
                View My Subscription
              </a>
            </div>

            <!-- PDF note -->
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 18px;margin-bottom:8px;">
              <p style="margin:0;font-size:12px;color:#374151;">
                <strong>&#128196; Invoice attached:</strong> Your PDF invoice <strong>{{ strtolower($invoiceNumber) }}.pdf</strong> is attached to this email. You can also view or download it from your Subscription page.
              </p>
            </div>

          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 16px 16px;padding:24px 40px;text-align:center;">
            <p style="margin:0 0 8px;font-size:12px;color:#10233f;font-weight:700;">Callytics</p>
            <p style="margin:0;font-size:11px;color:#94a3b8;line-height:1.6;">
              Call Analytics &amp; SIM Management Platform<br>
              This is an automated email. Please do not reply to this message.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
