<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>New Contact Form Submission</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%); padding: 32px 32px 24px; text-align: center; }
    .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; }
    .header p { color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px; }
    .body { padding: 32px; }
    .row { display: flex; margin-bottom: 16px; border-bottom: 1px solid #f0f0f0; padding-bottom: 16px; }
    .row:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .label { width: 130px; flex-shrink: 0; font-weight: 700; color: #555; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; padding-top: 2px; }
    .value { color: #222; font-size: 15px; line-height: 1.5; word-break: break-word; }
    .message-box { background: #fff8f3; border-left: 4px solid #ff6b00; padding: 16px; border-radius: 4px; color: #333; font-size: 15px; line-height: 1.7; white-space: pre-wrap; }
    .footer { background: #f8f8f8; border-top: 1px solid #eee; padding: 20px 32px; text-align: center; color: #999; font-size: 12px; }
    .badge { display: inline-block; background: #fff3e8; color: #ff6b00; border-radius: 20px; padding: 3px 12px; font-size: 13px; font-weight: 600; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>New Contact Form Submission</h1>
      <p>Received via Callytics website</p>
    </div>

    <div class="body">
      <div class="row">
        <div class="label">Name</div>
        <div class="value">{{ $contact->name }}</div>
      </div>
      <div class="row">
        <div class="label">Email</div>
        <div class="value"><a href="mailto:{{ $contact->email }}" style="color:#ff6b00;">{{ $contact->email }}</a></div>
      </div>
      @if($contact->phone)
      <div class="row">
        <div class="label">Phone</div>
        <div class="value">{{ $contact->phone }}</div>
      </div>
      @endif
      @if($contact->company)
      <div class="row">
        <div class="label">Company</div>
        <div class="value">{{ $contact->company }}</div>
      </div>
      @endif
      <div class="row">
        <div class="label">Subject</div>
        <div class="value"><span class="badge">{{ ucfirst($contact->subject) }}</span></div>
      </div>
      <div class="row" style="flex-direction:column;">
        <div class="label" style="margin-bottom:10px;">Message</div>
        <div class="message-box">{{ $contact->message }}</div>
      </div>
    </div>

    <div class="footer">
      This message was sent on {{ $contact->created_at->format('d M Y, h:i A') }} UTC<br>
      Callytics — Call Tracking &amp; Analytics
    </div>
  </div>
</body>
</html>
