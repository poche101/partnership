<!DOCTYPE html>
<html>
<head>
<style>
    body { font-family: DejaVu Sans, sans-serif; color: #16324F; font-size: 12px; }
    .header-table { width: 100%; border-bottom: 2px solid #A9812F; padding-bottom: 12px; margin-bottom: 20px; }
    .header-table td { vertical-align: middle; }
    .logo { height: 40px; }
    .org-name { font-size: 16px; font-weight: bold; }
    .org-sub { font-size: 9px; color: #4A5D70; text-transform: uppercase; letter-spacing: 1px; }
    .doc-date { text-align: right; font-size: 10px; color: #4A5D70; }
    .title { font-size: 18px; margin: 20px 0; color: #16324F; }
    .content-box {
        background: #F1ECDD;
        border: 1px solid #D9D2C0;
        padding: 16px 20px;
        white-space: pre-wrap;
        font-size: 11px;
        line-height: 1.6;
    }
    .footer {
        margin-top: 24px;
        border-top: 1px dashed #D9D2C0;
        padding-top: 12px;
        font-size: 9px;
        color: #4A5D70;
    }
</style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 50px;"><img class="logo" src="<?php echo e(public_path('images/zone5-logo.png')); ?>"></td>
            <td>
                <div class="org-name">Zone 5</div>
                <div class="org-sub">Partnership &amp; Giving Records</div>
            </td>
            <td class="doc-date"><?php echo e($statement->created_at->format('M j, Y')); ?></td>
        </tr>
    </table>

    <div class="title">Partnership Giving Statement</div>

    <div class="content-box"><?php echo e($statement->content); ?></div>

    <div class="footer">
        This statement reflects partnership giving recorded in the Zone 5 system as of the date above.
    </div>
</body>
</html><?php /**PATH C:\Users\kings\partnership\partnership\resources\views/statements/pdf.blade.php ENDPATH**/ ?>