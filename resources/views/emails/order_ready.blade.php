<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Ready</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="color: #0ea5e9; text-align: center;">LaundryCare Notification</h2>
        
        <p>Hello <strong>{{ $transaction->fullname }}</strong>,</p>
        
        <p>Great news! Your laundry with Reference Number: <strong>{{ $transaction->ref_number }}</strong> is now <strong>READY</strong> for pick-up at our branch.</p>
        
        <div style="background: #f8fafc; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Service Type:</strong> {{ $transaction->service_type }}</p>
            <p style="margin: 5px 0;"><strong>Payment Status:</strong> 
                @if($transaction->payment_status == 'Paid')
                    <span style="color: green; font-weight: bold;">Paid / Settled</span>
                @else
                    <span style="color: red; font-weight: bold;">Unpaid (Bring payment to the shop)</span>
                @endif
            </p>
        </div>

        @if($transaction->payment_status != 'Paid')
            <p style="color: #b91c1c; font-size: 14px;">
                ⚠️ Reminder: You still have an outstanding balance or payment that needs to be settled upon arriving at the shop to claim your laundry.
            </p>
        @else
            <p style="color: #047857; font-size: 14px;">
                ✅ Your account/order is fully paid. You can claim your laundry directly!
            </p>
        @endif

        <p style="margin-top: 30px;">Thank you for choosing LaundryCare!</p>
        
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #888; text-align: center;">This is an automated message from the system. Please do not reply to this email.</p>
    </div>
</body>
</html>