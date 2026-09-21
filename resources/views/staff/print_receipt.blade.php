<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Care Service | Receipt #{{ $data->ref_number }}</title>
    <style>
        :root {
            color: #172033;
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 18px 10px;
            background: #fff;
        }
        .receipt {
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
            background: #fff;
        }
        .receipt-header {
            padding: 4px 0 14px;
            color: #111827;
            text-align: center;
            border-bottom: 1px dashed #64748b;
        }
        .brand {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.2px;
        }
        .brand-subtitle {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .payment-details {
            margin-top: 10px;
            color: #475569;
            font-size: 11px;
            line-height: 1.7;
        }
        .location {
            margin-top: 7px;
            color: #64748b;
            font-size: 10px;
            line-height: 1.4;
        }
        .receipt-ref {
            margin-top: 8px;
            font-size: 12px;
            font-weight: 700;
        }
        .receipt-body { padding: 16px 0 0; }
        .section-title {
            margin: 0 0 10px;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }
        .details {
            display: grid;
            gap: 9px;
            margin-bottom: 16px;
        }
        .detail {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            font-size: 12px;
        }
        .detail-label { color: #64748b; }
        .detail-value { font-weight: 700; text-align: right; }
        .total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-top: 1px dashed #64748b;
            border-bottom: 1px dashed #64748b;
        }
        .total-label {
            color: #475569;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .total-amount { color: #111827; font-size: 19px; font-weight: 800; }
        .receipt-footer {
            padding-top: 16px;
            color: #64748b;
            text-align: center;
            font-size: 10px;
            line-height: 1.6;
        }
        @media print {
            @page { size: auto; margin: 8mm; }
            :root, body { background: #fff; }
            body { padding: 0; }
            .receipt {
                max-width: none;
                width: 72mm;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <main class="receipt">
        <header class="receipt-header">
            <h1 class="brand">Laundry Care Service</h1>
            <p class="brand-subtitle">Quality care for every load</p>
            <div class="payment-details">
                <div>GCash: 9098256981</div>
                <div>PayMaya: 9109107296</div>
            </div>
            <div class="location">{{ $data->branch_location ?? 'Branch location unavailable' }}</div>
            <div class="receipt-ref">RECEIPT #{{ $data->ref_number }}</div>
        </header>

        <section class="receipt-body">
            <h2 class="section-title">Service Details</h2>
            <div class="details">
                <div class="detail">
                    <span class="detail-label">Customer</span>
                    <span class="detail-value">{{ $data->fullname }}</span>
                </div>
                <div class="detail">
                    <span class="detail-label">Service</span>
                    <span class="detail-value">{{ $data->service_type }}</span>
                </div>
                <div class="detail">
                    <span class="detail-label">Weight</span>
                    <span class="detail-value">{{ number_format($data->weight_kg ?? 0, 2) }} kg</span>
                </div>
                <div class="detail">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">{{ date('M d, Y h:i A', strtotime($data->created_at)) }}</span>
                </div>
            </div>

            <div class="total">
                <span class="total-label">TOTAL AMOUNT</span>
                <span class="total-amount">₱{{ number_format($data->total_amount, 2) }}</span>
            </div>

            <footer class="receipt-footer">
                Thank you for choosing Laundry Care Service.<br>
                Please keep this receipt for your reference.
            </footer>
        </section>
    </main>
</body>
</html>