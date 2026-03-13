<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset your password – SoloHours</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body, table, td, p, a {
            margin: 0;
            padding: 0;
            text-decoration: none;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Inter", "Segoe UI", sans-serif;
        }

        body {
            background-color: #f3f4f6;
            color: #0f172a;
        }

        .wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 24px 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .header {
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-name {
            font-size: 18px;
            font-weight: 700;
        }

        .brand-primary {
            color: #2563EB;
        }

        .brand-secondary {
            color: #1F2937;
        }

        .brand-logo {
            display: block;
        }

        .content {
            padding: 24px;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 8px;
            color: #0f172a;
        }

        .subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .details-table th,
        .details-table td {
            padding: 6px 0;
            font-size: 14px;
            vertical-align: top;
        }

        .details-table th {
            width: 32%;
            color: #6b7280;
            font-weight: 500;
            text-align: left;
            padding-right: 12px;
        }

        .details-table td {
            color: #111827;
        }

        .details-table tr + tr th,
        .details-table tr + tr td {
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        .btn-primary {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 999px;
            background-color: #2563EB;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .footer {
            padding: 16px 24px 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #9ca3af;
        }

        a {
            color: #2563EB;
        }
    </style>
</head>
<body>
<table class="wrapper" role="presentation" width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table class="container" role="presentation" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="header">
                        <img src="{{ url('/assets/logo.png') }}" alt="SoloHours" height="32" class="brand-logo">
                        <span class="brand-name"><span class="brand-primary">Solo</span><span class="brand-secondary">Hours</span></span>
                    </td>
                </tr>
                <tr>
                    <td class="content">
                        <h1>Reset your password</h1>
                        <p class="subtitle">
                            We received a request to reset your SoloHours password. Use the link below to choose a new password.
                        </p>

                        <table class="details-table" role="presentation">
                            <tr>
                                <th>Email</th>
                                <td>{{ $notifiable->email }}</td>
                            </tr>
                        </table>

                        <a href="{{ $resetUrl }}" class="btn-primary">
                            Reset password
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="footer">
                        If you did not request a password reset, no further action is required.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
