<div style="font-family: 'Inter', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #2563eb;">
        <h1 style="margin: 0; color: #1e3a5f; font-size: 20px;">ITSM Daily Digest</h1>
        <p style="margin: 5px 0 0; color: #64748b; font-size: 13px;">{{ now()->format('l, d M Y') }}</p>
    </div>

    <p style="margin-top: 20px; font-size: 14px; color: #374151;">Hi {{ $user->name }},</p>
    <p style="font-size: 14px; color: #6b7280;">Here's your daily summary of the IT Service Desk.</p>

    <h3 style="color: #1e3a5f; font-size: 14px; margin-top: 25px; border-left: 3px solid #2563eb; padding-left: 10px;">System Overview</h3>
    <table cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px;">
        <tr style="background: #f8fafc;"><td style="border:1px solid #e2e8f0;">New Tickets Today</td><td style="border:1px solid #e2e8f0; font-weight: bold;">{{ $stats['new_today'] }}</td></tr>
        <tr><td style="border:1px solid #e2e8f0;">Total Open</td><td style="border:1px solid #e2e8f0; font-weight: bold;">{{ $stats['open'] }}</td></tr>
        <tr style="background: #f8fafc;"><td style="border:1px solid #e2e8f0;">Overdue</td><td style="border:1px solid #e2e8f0; font-weight: bold; color: {{ $stats['overdue'] > 0 ? '#dc2626' : '#16a34a' }};">{{ $stats['overdue'] }}</td></tr>
        <tr><td style="border:1px solid #e2e8f0;">Pending Rating</td><td style="border:1px solid #e2e8f0; font-weight: bold;">{{ $stats['pending_rating'] }}</td></tr>
        <tr style="background: #f8fafc;"><td style="border:1px solid #e2e8f0;">Resolved Yesterday</td><td style="border:1px solid #e2e8f0; font-weight: bold; color: #16a34a;">{{ $stats['resolved_yesterday'] }}</td></tr>
    </table>

    <h3 style="color: #1e3a5f; font-size: 14px; margin-top: 25px; border-left: 3px solid #f59e0b; padding-left: 10px;">Your Tasks</h3>
    <table cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px;">
        <tr style="background: #f8fafc;"><td style="border:1px solid #e2e8f0;">My Open Tickets</td><td style="border:1px solid #e2e8f0; font-weight: bold;">{{ $personalStats['my_open'] }}</td></tr>
        <tr><td style="border:1px solid #e2e8f0;">My Overdue</td><td style="border:1px solid #e2e8f0; font-weight: bold; color: {{ $personalStats['my_overdue'] > 0 ? '#dc2626' : '#16a34a' }};">{{ $personalStats['my_overdue'] }}</td></tr>
    </table>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ url('/dashboard') }}" style="display: inline-block; background: #2563eb; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;">Open Dashboard →</a>
    </div>

    <p style="margin-top: 30px; font-size: 11px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 15px;">
        This email was sent automatically by Amarin ITSM. Do not reply to this email.
    </p>
</div>
