<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to {{ $appName }}</title>
</head>
<body style="margin:0;padding:0;background:#0b1220;font-family:Arial, Helvetica, sans-serif;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    Welcome to {{ $appName }} — your tasks are about to get bullied into completion.
</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0b1220;padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 12px 30px rgba(0,0,0,.35);">
                <tr>
                    <td style="padding:22px 24px;background:linear-gradient(135deg,#7c3aed,#2563eb);color:#ffffff;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                                <td valign="middle">
                                    <div style="font-size:18px;font-weight:800;letter-spacing:.2px;">
                                        {{ $appName }} <span style="font-weight:700;opacity:.95;">HQ</span>
                                    </div>
                                    <div style="margin-top:6px;font-size:13px;opacity:.95;line-height:1.5;">
                                        Because even monsters need to organize their chaos.
                                    </div>
                                </td>
                                <td align="right" valign="middle" style="white-space:nowrap;">
                                    <div style="display:inline-block;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.28);padding:8px 10px;border-radius:12px;font-size:12px;font-weight:800;letter-spacing:.4px;">
                                        TASKZILLA 🦖
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px 24px 10px 24px;color:#0f172a;">
                        <div style="font-size:22px;font-weight:900;margin:0 0 10px 0;line-height:1.25;">
                            Welcome aboard, {{ $name }}!
                        </div>

                        <div style="font-size:14px;line-height:1.75;color:#334155;margin:0;">
                            Your account is ready. From now on, Taskzilla will politely help… and occasionally
                            <span style="font-weight:800;color:#0f172a;">stare intensely</span> at unfinished tasks until they get done.
                        </div>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:18px;">
                            <tr>
                                <td style="padding:14px 14px;background:#f1f5f9;border-radius:14px;border:1px solid #e2e8f0;">
                                    <div style="font-size:12px;color:#475569;line-height:1.7;">
                                        <div style="font-weight:900;color:#0f172a;margin-bottom:6px;">Your profile</div>
                                        <div><strong>Email:</strong> {{ $email }}</div>
                                        <div style="margin-top:4px;"><strong>Status:</strong> Ready to go ✅</div>
                                        <div style="margin-top:4px;"><strong>Role:</strong>Admin</div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:18px;">
                            <tr>
                                <td style="padding:0;">
                                    <div style="font-size:14px;font-weight:900;color:#0f172a;margin:0 0 10px 0;">
                                        Next best moves (in under 60 seconds):
                                    </div>
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size:14px;line-height:1.7;color:#334155;">
                                        <tr>
                                            <td valign="top" style="width:24px;color:#0f172a;font-weight:900;">1.</td>
                                            <td>Create your first team (and claim the “Lead” crown responsibly).</td>
                                        </tr>
                                        <tr>
                                            <td valign="top" style="width:24px;color:#0f172a;font-weight:900;">2.</td>
                                            <td>Invite your squad — Taskzilla loves company.</td>
                                        </tr>
                                        <tr>
                                            <td valign="top" style="width:24px;color:#0f172a;font-weight:900;">3.</td>
                                            <td>Make a task, assign it, and watch accountability become real.</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top:20px;">
                            <a href="{{ $frontendUrl }}"
                               style="display:inline-block;background:#0f172a;color:#ffffff;text-decoration:none;padding:12px 16px;border-radius:12px;font-size:14px;font-weight:900;">
                                Open {{ $appName }}
                            </a>
                            <span style="display:inline-block;margin-left:10px;font-size:12px;color:#64748b;line-height:1.6;vertical-align:middle;">
                                (No roaring required.)
                            </span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 24px;background:#0b1220;color:#cbd5e1;font-size:12px;line-height:1.7;">
                        <div style="opacity:.95;">
                            If you didn’t sign up for {{ $appName }}, you can safely ignore this email.
                        </div>
                        <div style="margin-top:8px;opacity:.85;">
                            © {{ date('Y') }} {{ $appName }} • Built with Laravel • Sent by Taskzilla (who definitely did not eat your homework)
                        </div>
                    </td>
                </tr>
            </table>

            <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;margin-top:14px;">
                <tr>
                    <td style="text-align:center;color:#94a3b8;font-size:11px;line-height:1.6;">
                        Tip: For best deliverability, ensure your mail domain has SPF/DKIM configured.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

