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
        .logo {
            font-size: 36px;
            margin-bottom: 10px;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e5e5e5;
        }
        .highlight-box {
            background: #FEF2F2;
            border-left: 4px solid #DC2626;
            padding: 15px;
            margin: 20px 0;
        }
        .request-number {
            font-size: 24px;
            font-weight: bold;
            color: #DC2626;
            text-align: center;
            margin: 20px 0;
        }
        .info-section {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .footer {
            background: #1a1a1a;
            color: #999;
            padding: 30px;
            text-align: center;
            font-size: 13px;
            border-radius: 0 0 8px 8px;
        }
        .footer a {
            color: #DC2626;
            text-decoration: none;
        }
        .checkmark {
            color: #DC2626;
            font-size: 48px;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">⚡</div>
            <h1>Quote Request Received</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; color: #ccc;">MILELE POWER LTD</p>
        </div>
        
        <div class="content">
            <div class="checkmark">✓</div>
            
            <p>Dear {{ $request->full_name }},</p>
            
            <p>Thank you for requesting a quote from Milele Power! We have successfully received your request and our team will review it shortly.</p>
            
            <div class="request-number">
                {{ $request->request_number }}
            </div>
            
            <p style="text-align: center; color: #666; font-size: 14px;">
                Please save this number for your reference
            </p>

            <div class="highlight-box">
                <strong>📋 What happens next?</strong>
                <ol style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Our team reviews your requirements (within 24 hours)</li>
                    <li>We prepare a customized quotation</li>
                    <li>You receive the quote via email</li>
                    <li>You approve and we schedule delivery</li>
                </ol>
            </div>

            <div class="info-section">
                <h3 style="margin-top: 0; color: #DC2626;">Your Request Summary:</h3>
                
                <div class="info-row">
                    <span class="info-label">Generator Type:</span>
                    <span>{{ $request->genset_type_formatted }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Start Date:</span>
                    <span>{{ $request->rental_start_date->format('l, F j, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Duration:</span>
                    <span>{{ $request->rental_duration_days }} days</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Delivery Location:</span>
                    <span>{{ $request->delivery_location }}</span>
                </div>
            </div>

            <p><strong>Need immediate assistance?</strong></p>
            <p>
                📞 Call us: +255 XXX XXX XXX<br>
                📧 Email: info@milelepower.co.tz<br>
                🕒 Operating Hours: Mon-Fri 9 AM - 5 PM, Sat 9 AM - 1 PM
            </p>

            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>Milele Power Team</strong><br>
                <em>"Reliable Power, Anytime, Anywhere!"</em>
            </p>
        </div>
        
        <div class="footer">
            <p><strong style="color: white; font-size: 16px;">MILELE POWER LTD</strong></p>
            <p>Powering your cold chain logistics — from dock to destination</p>
            <p style="margin-top: 15px;">
                Plot No. 80, Mikocheni Industrial Area<br>
                Coca Cola Road, Dar es Salaam, Tanzania
            </p>
            <p style="margin-top: 15px;">
                Website: <a href="https://www.milelepower.co.tz">www.milelepower.co.tz</a><br>
                Email: <a href="mailto:info@milelepower.co.tz">info@milelepower.co.tz</a>
            </p>
            <p style="margin-top: 20px; font-size: 11px; color: #666;">
                © {{ date('Y') }} Milele Power Ltd. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
