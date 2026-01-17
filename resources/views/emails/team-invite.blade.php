<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Invitation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1f2937;
            margin: 0;
            font-size: 24px;
        }
        .content {
            background-color: white;
            border-radius: 6px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .invite-details {
            background-color: #f3f4f6;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
        }
        .invite-details p {
            margin: 5px 0;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .accept-button {
            display: inline-block;
            background-color: #3b82f6;
            color: white !important;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
        }
        .accept-button:hover {
            background-color: #2563eb;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .expiry-notice {
            color: #ef4444;
            font-size: 14px;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 You've Been Invited!</h1>
        </div>

        <div class="content">
            <p>Hi there,</p>
            
            <p><strong>{{ $inviterName }}</strong> has invited you to join the team <strong>{{ $teamName }}</strong>.</p>

            <div class="invite-details">
                <p><strong>Team:</strong> {{ $teamName }}</p>
                <p><strong>Your Role:</strong> {{ ucfirst($role) }}</p>
                <p><strong>Invited By:</strong> {{ $inviterName }}</p>
            </div>

            <p>Click the button below to accept the invitation and join the team:</p>

            <div class="button-container">
                <a href="{{ $acceptUrl }}" class="accept-button">Accept Invitation</a>
            </div>

            @if($expiresAt)
            <p class="expiry-notice">
                ⏰ This invitation expires on {{ $expiresAt }}
            </p>
            @endif

            <p style="margin-top: 30px; font-size: 14px; color: #6b7280;">
                If you're having trouble clicking the button, copy and paste this URL into your browser:<br>
                <a href="{{ $acceptUrl }}" style="color: #3b82f6; word-break: break-all;">{{ $acceptUrl }}</a>
            </p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
