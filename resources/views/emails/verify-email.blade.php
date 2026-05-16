<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Verify Your Email — Callytics</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f6fb;font-family:Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6fb;min-height:100vh;">
    <tr>
      <td align="center" style="padding:40px 16px;">

        <!-- Card -->
        <table role="presentation" width="100%" style="max-width:560px;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(16,35,63,0.10);">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#10233f 0%,#1e3a5f 60%,#10233f 100%);padding:36px 40px 32px;text-align:center;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center">
                    <div style="display:inline-block;background:rgba(255,255,255,0.12);border-radius:14px;padding:10px 18px;">
                      <span style="font-size:1.5rem;font-weight:900;color:#fff;letter-spacing:-0.02em;font-family:Arial,Helvetica,sans-serif;">Callytics</span>
                    </div>
                    <!-- Icon -->
                    <div style="margin-top:28px;">
                      <div style="display:inline-block;background:rgba(249,115,22,0.18);border-radius:50%;padding:18px;">
                        <div style="background:linear-gradient(135deg,#f97316,#fb923c);border-radius:50%;width:52px;height:52px;display:inline-block;line-height:52px;text-align:center;">
                          <span style="font-size:1.6rem;color:#fff;">✉</span>
                        </div>
                      </div>
                    </div>
                    <h1 style="margin:18px 0 0;font-size:1.6rem;font-weight:800;color:#fff;letter-spacing:-0.02em;">Verify Your Email</h1>
                    <p style="margin:8px 0 0;font-size:0.95rem;color:rgba(255,255,255,0.65);">One quick step to activate your account</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:36px 40px 30px;">
              <p style="margin:0 0 16px;font-size:1rem;color:#374151;line-height:1.65;">
                Hi <strong style="color:#10233f;">{{ $user->name }}</strong>,
              </p>
              <p style="margin:0 0 24px;font-size:0.97rem;color:#4b5563;line-height:1.7;">
                Thanks for registering with <strong>Callytics</strong>! To start using your account, please verify your email address by clicking the button below.
              </p>

              <!-- CTA Button -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding:0 0 28px;">
                    <a href="{{ $verificationUrl }}"
                       style="display:inline-block;background:linear-gradient(90deg,#ea580c 0%,#f97316 55%,#fb923c 100%);color:#ffffff;font-size:1rem;font-weight:800;text-decoration:none;border-radius:12px;padding:15px 40px;letter-spacing:0.02em;box-shadow:0 6px 20px rgba(249,115,22,0.35);">
                      ✓ &nbsp;Verify Email Address
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Fallback URL -->
              <p style="margin:0 0 12px;font-size:0.82rem;color:#6b7280;line-height:1.5;">
                If the button doesn't work, copy and paste this link into your browser:
              </p>
              <p style="margin:0 0 28px;word-break:break-all;">
                <a href="{{ $verificationUrl }}" style="font-size:0.78rem;color:#f97316;text-decoration:none;">{{ $verificationUrl }}</a>
              </p>

              <!-- Expiry notice -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:#fff7ed;border:1.5px solid #fed7aa;border-radius:10px;padding:14px 18px;">
                    <p style="margin:0;font-size:0.84rem;color:#9a3412;line-height:1.55;">
                      ⏰ &nbsp;<strong>This link expires in 24 hours.</strong> If it expires, you can request a new one from the login page.
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Divider -->
          <tr>
            <td style="padding:0 40px;">
              <hr style="border:none;border-top:1px solid #e5e7eb;margin:0;" />
            </td>
          </tr>

          <!-- Security note -->
          <tr>
            <td style="padding:22px 40px 30px;">
              <p style="margin:0;font-size:0.82rem;color:#9ca3af;line-height:1.6;">
                If you didn't create a Callytics account, you can safely ignore this email. Someone may have entered your address by mistake.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f8fafc;border-top:1px solid #e5e7eb;padding:20px 40px;text-align:center;">
              <p style="margin:0;font-size:0.78rem;color:#9ca3af;">
                © {{ date('Y') }} Callytics · Call Analytics Platform<br/>
                Need help? Contact our support team.
              </p>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
