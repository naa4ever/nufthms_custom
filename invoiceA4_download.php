<?php
/*
 * invoiceA4_download.php  —  PDF-only invoice view (loaded by Finance::download())
 *
 * mPDF compatibility:
 *  - Layout uses HTML tables, NOT flexbox (mPDF has no flex support)
 *  - Fonts loaded via @font-face with absolute server filesystem paths
 *    (mPDF ignores <link> tags and CDN URLs entirely)
 *  - No JavaScript, no Bootstrap classes, no external CSS links
 *  - -webkit-print-color-adjust keeps background colours
 *
 * To enable Tajawal in the PDF, place these files in FCPATH/common/fonts/:
 *   Tajawal-Regular.ttf  and  Tajawal-Bold.ttf
 * Download from fonts.google.com → search "Tajawal" → Download family.
 * If absent, mPDF's built-in DejaVuSans (full Arabic support) is used.
 */

// ── PHP helpers ─────────────────────────────────────────────────────────────
if (!function_exists('toArabicNumbers')) {
    function toArabicNumbers($number) {
        $western = ['0','1','2','3','4','5','6','7','8','9'];
        $arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        return str_replace($western, $arabic, (string) $number);
    }
}

if (!function_exists('AmountInWords')) {
    function AmountInWords(float $amount) {
        $cents = (int) round(($amount - floor($amount)) * 100);
        $whole = (int) floor($amount);
        $ones  = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                  'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
                  'Seventeen','Eighteen','Nineteen'];
        $tens  = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

        function _w3(int $n, array $o, array $t): string {
            $s = '';
            if ($n >= 100) { $s .= $o[(int)($n/100)].' Hundred '; $n %= 100; }
            if ($n >= 20)  { $s .= $t[(int)($n/10)].($n%10 ? ' '.$o[$n%10] : '').' '; }
            elseif ($n)    { $s .= $o[$n].' '; }
            return $s;
        }
        if ($whole === 0) {
            $words = 'Zero';
        } else {
            $words = '';
            foreach ([[1000000000,'Billion'],[1000000,'Million'],[1000,'Thousand'],[1,'']] as [$div,$lbl]) {
                $part   = (int)($whole / $div);
                $whole %= $div;
                if ($part) { $words .= _w3($part, $ones, $tens).$lbl.' '; }
            }
            $words = trim($words);
        }
        $currency = $GLOBALS['settings']->currency ?? 'Saudi Riyals';

        if ($cents > 0) {
            $halalasWords = trim(_w3($cents, $ones, $tens));
            $halalaLabel = ($cents == 1) ? 'Halala' : 'Halalas';

            return $words . ' ' . $currency . ' and ' . $halalasWords . ' ' . $halalaLabel;
        }

        return $words . ' ' . $currency;
    }
}

// ── Data shortcuts ───────────────────────────────────────────────────────────
$patient_info  = $this->db->get_where('patient', ['id' => $payment->patient])->row();
$flat_discount = $payment->flat_discount ?? 0;
$vat_total     = $payment->vat           ?? 0;
$total         = $payment->gross_total;
// $balance_due passed from controller; compute here as safety fallback
if (!isset($balance_due)) {
    $balance_due = max(0, $total - $this->finance_model->getDepositAmountByPaymentId($payment->id));
}

// ── Font paths (absolute — mPDF cannot use CDN URLs) ────────────────────────
$font_dir        = FCPATH . 'common/fonts/';
$tajawal_regular = $font_dir . 'Tajawal-Regular.ttf';
$tajawal_bold    = $font_dir . 'Tajawal-Bold.ttf';
$use_tajawal     = file_exists($tajawal_regular) && file_exists($tajawal_bold);
$font_stack      = ($use_tajawal ? "'Tajawal'," : '') . "'dejavusans', Arial, sans-serif";
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<style>
/* ── Font ──────────────────────────────────────────────────────────────── */
<?php if ($use_tajawal): ?>
@font-face {
    font-family: 'Tajawal';
    src: url('<?php echo $tajawal_regular; ?>');
    font-weight: normal;
    font-style: normal;
}
@font-face {
    font-family: 'Tajawal';
    src: url('<?php echo $tajawal_bold; ?>');
    font-weight: bold;
    font-style: normal;
}
<?php endif; ?>
<?php
$saudirial_ttf = $font_dir . 'SaudiRiyal.ttf';
$use_saudirial = file_exists($saudirial_ttf);
?>
<?php if ($use_saudirial): ?>
@font-face {
    font-family: 'SaudiRiyal';
    src: url('<?php echo $saudirial_ttf; ?>');
    font-weight: normal;
    font-style: normal;
}
.sar { font-family: 'SaudiRiyal'; }
<?php else: ?>
.sar { font-family: <?php echo $font_stack; ?>; }
<?php endif; ?>

/* ── Base ──────────────────────────────────────────────────────────────── */
* { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
body { font-family: <?php echo $font_stack; ?>; font-size: 13px; margin: 0; padding: 0; background: #fff; color: #222; }

/* ── Arabic helpers ────────────────────────────────────────────────────── */
.ar  { direction: rtl; text-align: right; font-family: <?php echo $font_stack; ?>; unicode-bidi: plaintext; }
.arb { font-family: <?php echo $font_stack; ?>; unicode-bidi: plaintext; }
.en  { direction: ltr; text-align: left; }

/* ── Coloured bars ─────────────────────────────────────────────────────── */
.line-top-bar    { background: #a38972; padding: 2px; width: 100%; }
.line-bottom-bar { background: #495448; padding: 2px; width: 100%; }
/* top-bar uses inline styles on each <td> — mPDF does not support linear-gradient */

/* ── Items table ───────────────────────────────────────────────────────── */
.table-custom    { width: 100%; border-collapse: collapse; margin-top: 16px; }
.table-custom th { background: #495448; color: #fff; padding: 5px; border: 1px solid #ccc; text-align: center; }
.table-custom td { border: 1px solid #ccc; padding: 5px; text-align: center; }

/* ── Summary ───────────────────────────────────────────────────────────── */
.total-highlight { background: #726050 !important; color: #fff !important; font-weight: bold; }
.dsummary        { width: 100%; border-collapse: collapse; }
.dsummary td     { padding: 5px 8px; border-bottom: 1px solid #eee; }

/* box-header colours are applied as inline styles on each <div> element */

/* ── Badges ────────────────────────────────────────────────────────────── */
.paid-badge { display: inline-block; font-weight: bold; background-color: #495448; border-radius: 25px; padding: 5px 14px; color: #fff; font-size: 15px; }
.due-badge  { display: inline-block; font-weight: bold; background-color: #a38972; border-radius: 25px; padding: 5px 14px; color: #fff; font-size: 15px; }

/* ── Thank-you section ─────────────────────────────────────────────────── */
.footer-divider  { border-bottom: 2px solid #495448; padding-top: 10px; margin-bottom: 8px; width: 90%; }
.download-thanks { font-size: 14px; line-height: 1.7; }
.signature-line  { margin-top: 26px; font-size: 12px; border-top: 2px solid #495448; color: #495448; width: 50%; line-height: 1.6; }

/* ── Footer ────────────────────────────────────────────────────────────── */
.invoice-footer { text-align: center; padding-top: 12px; font-size: 12px; color: #555; }
.barcode-area   { text-align: center; margin-top: 6px; }
.image_bar      { width: 195px; height: 45px; }
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════
     HEADER BARS
     ═══════════════════════════════════════════════════════════════ -->
<div class="line-top-bar"></div>
<table width="100%" cellpadding="10" cellspacing="0">
    <tr>
        <td style="background-color:#a38972;text-align:left;vertical-align:middle;">
            <div style="margin:0;font-weight:bold;font-size:22px;color:#ffffff;">TAX INVOICE</div>
        </td>
        <td style="background-color:#495448;text-align:right;vertical-align:middle;direction:rtl;">
            <div style="margin:0;font-weight:bold;font-size:22px;color:#ffffff;">فاتورة ضريبية</div>
        </td>
    </tr>
</table>
<div class="line-bottom-bar"></div>

<!-- ═══════════════════════════════════════════════════════════════
     COMPANY INFO — 3 columns: logo | EN | AR
     (replaces flex .company-header)
     ═══════════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="4" cellspacing="0" style="margin-top:7px;">
    <tr>
        <!-- Logo -->
        <td width="20%" style="text-align:center;vertical-align:middle;">
            <img src="<?php echo site_url($settings->logo_title); ?>" alt="Logo" style="max-height:160px;max-width:100%;">
        </td>

        <!-- English info -->
        <td width="40%" style="vertical-align:middle;text-align:left;font-size:18px;line-height:1.7;">
            <b>Name:</b> <?php echo $settings->title; ?><br>
            <b>Address:</b> <?php echo $settings->address; ?><br>
            <b>C.R.:</b> <?php echo $settings->cr ?? '403012345'; ?><br>
            <b>VAT ID:</b> <?php echo $settings->vat_id ?? ''; ?><br>
            <b>Contact:</b> <?php echo $settings->phone; ?><br>
            <b>Email:</b> <?php echo $settings->email; ?>
        </td>

        <!-- Arabic info -->
        <td width="40%" class="ar" style="vertical-align:middle;font-size:18px;line-height:1.7;padding-right:12px;">
            <strong>اسم الشركة:</strong> <?php echo $settings->title; ?><br>
            <b>العنوان:</b> <?php echo $settings->address; ?><br>
            <b>س. ت.:</b> <span style="font-size:22px;"><?php echo toArabicNumbers($settings->cr ?? '403012345'); ?></span><br>
            <b>الرقم الضريبي:</b> <span style="font-size:22px;"><?php echo toArabicNumbers($settings->vat_id ?? ''); ?></span><br>
            <b>رقم التواصل:</b> <span style="font-size:22px;"><?php echo toArabicNumbers($settings->phone); ?></span><br>
            <b>البريد الإلكتروني:</b> <?php echo $settings->email; ?>
        </td>
    </tr>
</table>

<!-- ═══════════════════════════════════════════════════════════════
     CUSTOMER INFO + INVOICE DETAILS — 2-column table
     (replaces flex .section with two .box children)
     ═══════════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:7px;">
    <tr>
        <!-- Customer Info -->
        <td width="49%" style="vertical-align:top;">
            <table width="100%" cellpadding="0" cellspacing="0"><tr><td style="background-color:#495448;padding:5px 5px 5px 20px;"><b style="font-size:20px;color:#ffffff;font-family:'Tajawal','dejavusans',Arial,sans-serif;">CUSTOMER INFO / بيانات العميل</b></td></tr></table>
            <div style="padding:6px 6px 6px 15px;font-size:14px;line-height:1.8;">
                <strong>&ensp;<?php echo lang('patient'); ?> Name: / </strong>
                <?php echo $patient_info->name; ?>&ensp;<b>/</b>
                <b class="ar" style="font-size:15px;">اسم المراجع:</b><br>

                <b>&ensp;<?php echo lang('patient_id'); ?>: /&ensp;</b>
                <?php echo $patient_info->id; ?>&ensp;<b>/</b>
                <b class="ar" style="font-size:15px;">رقم تعريف المراجع:</b><br>

                <b>&ensp;VAT ID: / </b>
                <?php echo $patient_info->vat ?? ''; ?>&ensp;<b>/</b>
                <b class="ar" style="font-size:15px;">الرقم الضريبي:</b><br>

                <b>&ensp;Contact No.: /</b>
                <?php echo $patient_info->phone; ?>&ensp;<b>/</b>
                <b class="ar" style="font-size:15px;">رقم التواصل:</b>
            </div>
        </td>

        <td width="2%"></td>

        <!-- Invoice Details -->
        <td width="49%" style="vertical-align:top;">
            <table width="100%" cellpadding="0" cellspacing="0"><tr><td style="background-color:#a38972;padding:5px 5px 5px 10px;text-align:center;"><b style="font-size:20px;color:#ffffff;font-family:'Tajawal','dejavusans',Arial,sans-serif;">INVOICE DETAILS / تفاصيل الفاتورة</b></td></tr></table>
           <div style="padding:6px 15px 6px 6px;font-size:14px;line-height:1.8;text-align:right;">
                <strong>&ensp;Invoice No: / </strong><?php echo $payment->id; ?>&ensp;<b>/</b>
                <b class="ar" style="font-size:15px;">رقم الفاتورة:</b><br>

                <b>&ensp;Invoice Date: / </b><?php echo date('d M Y', $payment->date); ?>&ensp;<b>/</b>
                <b class="ar" style="font-size:15px;">تاريخ الإصدار:</b><br>

                <b>&ensp;Invoice Time: / </b><?php echo date('h:i A', $payment->date); ?>&ensp;<b>/</b>
                <b class="ar" style="font-size:15px;">وقت الإصدار:</b><br>

                <b>&ensp;Invoice Type: / </b>Final Invoice&ensp;<b>/</b>
                <b class="ar" style="font-size:15px;">نوع الفاتورة:</b>
            </div>
        </td>
    </tr>
</table>

<!-- ═══════════════════════════════════════════════════════════════
     ITEMS TABLE  (8-column ZATCA, same PHP logic as invoice_A4.php)
     ═══════════════════════════════════════════════════════════════ -->
<?php
$i                 = 0;
$subtotal          = 0;
$gross_items_total = 0;

if (!empty($payment->category_name)) {
    $items = explode(',', $payment->category_name);

    // ZATCA: VAT must be applied on the post-discount taxable amount.
    // Distribute the invoice-level flat discount proportionally across lines.
    foreach ($items as $_item) {
        $_d = explode('*', $_item);
        $gross_items_total += $_d[1] * $_d[3];
    }
}
?>
<table class="table-custom">
    <thead>
        <tr>
            <th>#</th>
            <th width="25%">Description<br><span class="ar">الوصف</span></th>
            <th>Qty<br><span class="ar">الكمية</span></th>
            <th>Unit Price (SAR)<br><span class="ar">سعر الوحدة</span></th>
            <th>Total Before VAT<br><span class="ar">الإجمالي قبل الضريبة</span></th>
            <th>VAT %<br><span class="ar">الضريبة</span></th>
            <th>VAT Amount<br><span class="ar">قيمة الضريبة</span></th>
            <th>Total (SAR)<br><span class="ar">الإجمالي</span></th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($payment->category_name)):
        foreach ($items as $item):
            $i++;
            $d          = explode('*', $item);
            $qty        = $d[3];
            $price      = $d[1];
            $line_gross = $qty * $price;

            // Proportional share of the invoice-level flat discount
            $line_flat_discount = ($gross_items_total > 0)
                ? ($payment->flat_discount ?? 0) * ($line_gross / $gross_items_total)
                : 0;

            $vat_rate   = $payment->vat_amount_percent;
            // ZATCA: VAT on post-discount taxable amount
            $taxable    = $line_gross - $line_flat_discount;
            $vat_amount = ($taxable * $vat_rate) / 100;
            $final      = $taxable + $vat_amount;
            $subtotal  += $line_gross;
    ?>
        <tr>
            <td><?php echo $i; ?></td>
            <td><?php echo $this->finance_model->getPaymentcategoryById($d[0])->category; ?></td>
            <td><?php echo $qty; ?></td>
            <td><?php echo number_format($price, 2); ?></td>
            <td><?php echo number_format($taxable, 2); ?></td>
            <td><?php echo $vat_rate; ?>%</td>
            <td><?php echo number_format($vat_amount, 2); ?></td>
            <td><?php echo number_format($final, 2); ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<!-- ═══════════════════════════════════════════════════════════════
     BOTTOM ROW: QR | amount-in-words + badge | summary
     (replaces flex .bottom-row with 3 .qr-box / .qr-box2 / .dsummary-box)
     ═══════════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="4" cellspacing="0" style="margin-top:16px;">
    <tr>
        <!-- QR code -->
        <td width="16%" style="vertical-align:top;text-align:left;">
            <img src="<?php echo site_url('uploads/payment_' . $payment->id . '.png'); ?>"
                 width="130" alt="QR">
        </td>

        <!-- Amount in words + paid/due badge -->
        <td width="32%" style="vertical-align:top;padding-left:8px;font-size:13px;line-height:1.7;">
            <div style="margin-bottom:10px;"><?php echo AmountInWords($total); ?></div>
            <?php if ($balance_due <= 0): ?>
                <span class="paid-badge">&ensp;<?php echo lang('paid'); ?>&ensp;</span>
            <?php else: ?>
                <span class="due-badge">&ensp;<?php echo lang('due_have'); ?>&ensp;</span>
            <?php endif; ?>
        </td>

        <!-- Spacer -->
        <td width="5%"></td>

        <!-- ZATCA summary -->
        <td width="47%" style="vertical-align:top;">
            <table class="dsummary">
                <tr>
                    <td><b>&ensp;Subtotal:</b></td>
                    <td><span class="sar">﷼</span>&nbsp;<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php if ($flat_discount > 0): ?>
                <tr>
                    <td><b>&ensp;Discount:</b></td>
                    <td><span class="sar">﷼</span>&nbsp;<?php echo number_format($flat_discount, 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><b>&ensp;Taxable Amount:</b></td>
                    <td><span class="sar">﷼</span>&nbsp;<?php echo number_format($subtotal - $flat_discount, 2); ?></td>
                </tr>
                <tr>
                    <td><b>&ensp;Total VAT (<?php echo $payment->vat_amount_percent; ?>%):</b></td>
                    <td><span class="sar">﷼</span>&nbsp;<?php echo number_format($vat_total, 2); ?></td>
                </tr>
                <tr>
                    <td style="padding:10px 8px;background-color:#9c8a79;">
                        <b style="font-size:18px;color:#ffffff;">&ensp;Invoice Total (SAR):</b>
                    </td>
                    <td style="padding:10px 8px;background-color:#9c8a79;">
                        <b style="font-size:18px;color:#ffffff;"><span class="sar" style="color:#ffffff;">﷼</span>&nbsp;<?php echo number_format($total, 2); ?></b>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ═══════════════════════════════════════════════════════════════
     ROW 2: Thank-you + signature | Payment details
     (replaces flex .payment-row with .payment-box2 and .payment-box)
     ═══════════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="4" cellspacing="0" style="margin-top:8px;">
    <tr>
        <!-- Thank-you message + signature line + remarks -->
        <td width="58%" style="vertical-align:top;">
            <div class="footer-divider"></div>
            <div class="download-thanks">
                Thank you <strong><?php echo $patient_info->name; ?></strong>, for choosing us.
                It was our pleasure to serve you. We appreciate your trust and hope you are
                satisfied with the care and service provided.
            </div>
            <div class="signature-line"><br></div>
            <?php if (!empty(trim($payment->remarks ?? ''))): ?>
                <div style="margin-top:6px;">
                    &ensp;<strong><?php echo lang('note'); ?> : </strong>
                    <?php echo $payment->remarks; ?>
                </div>
            <?php endif; ?>
        </td>

        <td width="2%"></td>

        <!-- Payment details -->
        <td width="40%" style="vertical-align:top;">
            <br>
            <strong>Payment Details</strong><br>
            Bank: Bank Name<br>
            A/c No.: 1234 XXX 1234<br>
            IBAN: XXXX
        </td>
    </tr>
</table>

<!-- Footer message -->
<div class="invoice-footer">
    <?php echo $settings->footer_invoice_message; ?>
</div>

<!-- Barcode -->
<div class="barcode-area">
    <img class="image_bar" alt="barcode" src="<?php echo site_url('lab/barcode'); ?>">
    <p><b>000<?php echo $payment->id; ?></b></p>
</div>

<!-- Closing bar -->
<div class="line-top-bar" style="margin-top:14px;"></div>

</body>
</html>
