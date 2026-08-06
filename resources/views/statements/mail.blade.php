<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giving Statement</title>
</head>

<body
    style="margin: 0; padding: 20px 0; background-color: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #16324F;">

    @php
        $logoPath = public_path('images/zone5-logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;
    @endphp

    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <!-- Document Container -->
                <table role="presentation" width="100%"
                    style="max-width: 600px; background-color: #FAF8F2; border: 1px solid #D9D2C0; border-radius: 4px; box-shadow: 0 4px 12px rgba(22, 50, 79, 0.08); padding: 32px; text-align: left;">

                    <!-- Header Section -->
                    <tr>
                        <td>
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    @if ($logoBase64)
                                        <td width="48" style="vertical-align: middle; padding-right: 12px;">
                                            <img src="{{ $logoBase64 }}" alt="Zone 5 Logo" width="40"
                                                height="40"
                                                style="display: block; width: 40px; height: auto; border: 0;">
                                        </td>
                                    @endif
                                    <td style="vertical-align: middle;">
                                        <p
                                            style="margin: 0; font-weight: 600; font-size: 16px; color: #16324F; letter-spacing: 0.02em;">
                                            Zone 5</p>
                                        <p
                                            style="margin: 2px 0 0 0; font-size: 11px; color: #4A5D70; letter-spacing: 0.04em; text-transform: uppercase;">
                                            Partnership &amp; Giving Records</p>
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <p
                                            style="margin: 0; font-size: 10px; font-weight: 600; color: #A9812F; letter-spacing: 0.12em; text-transform: uppercase;">
                                            Statement</p>
                                        <p style="margin: 2px 0 0 0; font-size: 12px; color: #4A5D70;">
                                            {{ $generatedAt ?? now()->format('M j, Y') }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Decorative Gold Rule -->
                    <tr>
                        <td style="padding: 16px 0 20px 0;">
                            <div style="height: 1px; background: #A9812F; width: 100%;"></div>
                        </td>
                    </tr>

                    <!-- Title & Recipient -->
                    <tr>
                        <td>
                            <h1 style="margin: 0 0 16px 0; font-size: 20px; color: #16324F; font-weight: 600;">
                                Partnership Giving Statement</h1>

                            @if (!empty($partnerName) || !empty($name))
                                <table role="presentation" border="0" cellspacing="0" cellpadding="0"
                                    style="margin-bottom: 20px;">
                                    <tr>
                                        <td style="padding-right: 24px;">
                                            <span
                                                style="display: block; font-size: 10px; font-weight: 600; color: #A9812F; letter-spacing: 0.08em; text-transform: uppercase;">Partner</span>
                                            <span
                                                style="display: block; font-size: 14px; color: #16324F; margin-top: 2px;">{{ $partnerName ?? $name }}</span>
                                        </td>
                                        @if (!empty($spouseName))
                                            <td>
                                                <span
                                                    style="display: block; font-size: 10px; font-weight: 600; color: #A9812F; letter-spacing: 0.08em; text-transform: uppercase;">Spouse</span>
                                                <span
                                                    style="display: block; font-size: 14px; color: #16324F; margin-top: 2px;">{{ $spouseName }}</span>
                                            </td>
                                        @endif
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    <!-- Message Body (Custom Template Content) -->
                    <tr>
                        <td>
                            <div
                                style="background-color: #F1ECDD; border: 1px solid #D9D2C0; border-radius: 3px; padding: 20px; font-family: 'Courier New', Courier, monospace; font-size: 13px; line-height: 1.6; color: #16324F; white-space: pre-wrap;">
                                {{ $messageBody ?? ($statement->content ?? '') }}</div>
                        </td>
                    </tr>

                    <!-- Footer Section with Brass Seal -->
                    <tr>
                        <td style="padding-top: 24px; border-top: 1px dashed #D9D2C0; margin-top: 24px;">
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="60" style="vertical-align: middle;">
                                        <!-- Mini SVG Official Seal -->
                                        <svg viewBox="0 0 120 120" width="52" height="52"
                                            style="display: block;">
                                            <circle cx="60" cy="60" r="56" fill="none" stroke="#A9812F"
                                                stroke-width="2" />
                                            <circle cx="60" cy="60" r="48" fill="none" stroke="#A9812F"
                                                stroke-width="1" />
                                            <path d="M45,62 L55,72 L76,48" fill="none" stroke="#A9812F"
                                                stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </td>
                                    <td style="vertical-align: middle; padding-left: 12px;">
                                        <p style="margin: 0; font-size: 11px; color: #4A5D70; line-height: 1.4;">
                                            This official statement reflects partnership giving recorded in the Zone 5
                                            system as of {{ $generatedAt ?? now()->format('M j, Y') }}.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>