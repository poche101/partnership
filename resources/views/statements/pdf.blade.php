<!DOCTYPE html>
<html>
<head>
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        color: #16324F;
        font-size: 12px;
    }

    /* Outer frame to mimic the "document" look from the preview */
    .frame {
        border: 1px solid #D9D2C0;
        padding: 26px 32px;
    }

    .header-table { width: 100%; padding-bottom: 12px; }
    .header-table td { vertical-align: middle; }
    .logo { height: 40px; }
    .org-name { font-size: 15px; font-weight: bold; letter-spacing: 0.3px; }
    .org-sub { font-size: 9px; color: #4A5D70; text-transform: uppercase; letter-spacing: 1px; }
    .doc-eyebrow {
        text-align: right;
        font-size: 9px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #A9812F;
        font-weight: bold;
    }
    .doc-date {
        text-align: right;
        font-size: 10px;
        color: #4A5D70;
    }

    .rule {
        margin: 16px 0 18px;
        height: 2px;
        background: #A9812F;
    }

    .title {
        font-size: 18px;
        margin: 0 0 14px;
        color: #16324F;
    }

    .recipient-table { width: 100%; margin-bottom: 16px; }
    .recipient-table td { padding-right: 30px; vertical-align: top; }
    .recipient-label {
        display: block;
        font-size: 8.5px;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #A9812F;
        font-weight: bold;
    }
    .recipient-value {
        display: block;
        font-size: 11.5px;
        color: #16324F;
        margin-top: 2px;
    }

    .content-box {
        background: #F1ECDD;
        border: 1px solid #D9D2C0;
        padding: 16px 20px;
        white-space: pre-wrap;
        font-size: 11px;
        line-height: 1.6;
    }

    .footer-table {
        width: 100%;
        margin-top: 22px;
        border-top: 1px dashed #D9D2C0;
        padding-top: 14px;
    }
    .footer-table td { vertical-align: middle; }

    .seal-cell { width: 78px; }
    .seal {
        width: 66px;
        height: 66px;
        border: 1.5px solid #A9812F;
        border-radius: 50%;
        text-align: center;
        position: relative;
    }
    .seal-inner {
        width: 54px;
        height: 54px;
        margin: 5px auto 0;
        border: 1px solid #A9812F;
        border-radius: 50%;
        display: table;
    }
    .seal-inner-text {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
        font-size: 6.5px;
        letter-spacing: 0.5px;
        color: #A9812F;
        font-weight: bold;
        line-height: 1.3;
    }

    .footer-note {
        font-size: 8.5px;
        color: #4A5D70;
        line-height: 1.5;
    }
</style>
</head>
<body>
    @php
        $partner = $statement->partner ?? null;
        $hasSpouse = $partner && filled($partner->spouse_first_name);

        $partnerName = $partner
            ? trim(($partner->title ?? '').' '.$partner->first_name.' '.$partner->last_name)
            : null;

        $spouseName = $hasSpouse
            ? trim(
                ($partner->spouse_title ?? '').' '.
                $partner->spouse_first_name.' '.
                ($partner->spouse_last_name ?: $partner->last_name)
            )
            : null;
    @endphp

    <div class="frame">
        <table class="header-table">
            <tr>
                <td style="width: 50px;"><img class="logo" src="{{ public_path('images/zone5-logo.png') }}"></td>
                <td>
                    <div class="org-name">Zone 5</div>
                    <div class="org-sub">Partnership &amp; Giving Records</div>
                </td>
                <td style="width: 140px;">
                    <div class="doc-eyebrow">Statement</div>
                    <div class="doc-date">{{ $statement->created_at->format('M j, Y') }}</div>
                </td>
            </tr>
        </table>

        <div class="rule"></div>

        <div class="title">Partnership Giving Statement</div>

        @if ($partnerName)
            <table class="recipient-table">
                <tr>
                    <td>
                        <span class="recipient-label">Partner</span>
                        <span class="recipient-value">{{ $partnerName }}</span>
                    </td>
                    @if ($spouseName)
                        <td>
                            <span class="recipient-label">Spouse</span>
                            <span class="recipient-value">{{ $spouseName }}</span>
                        </td>
                    @endif
                </tr>
            </table>
        @endif

        <div class="content-box">{{ $statement->content }}</div>

        <table class="footer-table">
            <tr>
                <td class="seal-cell">
                    <div class="seal">
                        <div class="seal-inner">
                            <div class="seal-inner-text">OFFICIAL<br>RECORD</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="footer-note">
                        This statement reflects partnership giving recorded in the Zone 5 system as of the date above.
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>