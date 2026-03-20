<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .badge {
            display: inline-block;
            background: #DC2626;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e5e5e5;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            color: #DC2626;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 2px solid #DC2626;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            min-width: 180px;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 8px 8px;
        }
        .btn {
            display: inline-block;
            background: #DC2626;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 New Quote Request</h1>
            <span class="badge">{{ $request->request_number }}</span>
        </div>
        
        <div class="content">
            <div class="section">
                <div class="section-title">Customer Information</div>
                <div class="info-row">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">{{ $request->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $request->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $request->phone }}</span>
                </div>
                @if($request->company_name)
                <div class="info-row">
                    <span class="info-label">Company:</span>
                    <span class="info-value">{{ $request->company_name }}</span>
                </div>
                @endif
            </div>

            <div class="section">
                <div class="section-title">Rental Requirements</div>
                <div class="info-row">
                    <span class="info-label">Generator Type:</span>
                    <span class="info-value">{{ $request->genset_type_formatted }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Start Date:</span>
                    <span class="info-value">{{ $request->rental_start_date->format('l, F j, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Duration:</span>
                    <span class="info-value">{{ $request->rental_duration_days }} days</span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Location Details</div>
                <div class="info-row">
                    <span class="info-label">Delivery Location:</span>
                    <span class="info-value">{{ $request->delivery_location }}</span>
                </div>
                @if($request->pickup_location)
                <div class="info-row">
                    <span class="info-label">Pickup Location:</span>
                    <span class="info-value">{{ $request->pickup_location }}</span>
                </div>
                @endif
            </div>

            @if($request->additional_requirements)
            <div class="section">
                <div class="section-title">Additional Requirements</div>
                <p>{{ $request->additional_requirements }}</p>
            </div>
            @endif

            <div class="section">
                <div class="section-title">Metadata</div>
                <div class="info-row">
                    <span class="info-label">Submitted:</span>
                    <span class="info-value">{{ $request->created_at->format('F j, Y \a\t g:i A') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Source:</span>
                    <span class="info-value">{{ ucfirst($request->source) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">IP Address:</span>
                    <span class="info-value">{{ $request->ip_address }}</span>
                </div>
            </div>

            <center>
                <a href="{{ url('/admin/quote-requests/' . $request->id) }}" class="btn">
                    View in Admin Panel →
                </a>
            </center>
        </div>
        
        <div class="footer">
            <p><strong>MILELE POWER LTD</strong></p>
            <p>Plot No. 80, Mikocheni, Dar es Salaam, Tanzania</p>
            <p>Phone: +255 XXX XXX XXX | Email: info@milelepower.co.tz</p>
            <p style="margin-top: 15px; color: #999;">
                This is an automated notification. Please respond to the customer within 24 hours.
            </p>
        </div>
    </div>
</body>
</html>
