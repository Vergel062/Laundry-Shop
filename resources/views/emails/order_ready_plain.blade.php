LaundryCare Notification

Hello {{ $transaction->fullname }},

Great news! Your laundry with Reference Number: {{ $transaction->ref_number }} is now READY for pick-up at our branch.

Service Type: {{ $transaction->service_type }}
Payment Status: {{ $transaction->payment_status === 'Paid' ? 'Paid / Settled' : 'Unpaid (Bring payment to the shop)' }}

@if($transaction->payment_status !== 'Paid')
Reminder: You still have an outstanding balance or payment that needs to be settled upon arriving at the shop to claim your laundry.
@else
Your account/order is fully paid. You can claim your laundry directly!
@endif

Thank you for choosing LaundryCare!

This is an automated message from the system. Please do not reply to this email.
