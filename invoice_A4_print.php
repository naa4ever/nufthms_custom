<link href="common/css/sasymbol.css" rel="stylesheet">
<div class="content-wrapper bg-light">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row my-2 pl-1">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold"><i class="fas fa-file-invoice mr-2"></i><?php echo lang('invoice') ?> # <?php echo $payment->id; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#"><?php echo lang('home') ?></a></li>
                        <li class="breadcrumb-item"><a href="finance/payment"><?php echo lang('all') ?> <?php echo lang('invoices') ?></a></li>
                        <li class="breadcrumb-item active"><?php echo lang('invoice') ?> # <?php echo $payment->id; ?></li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <!-- Main content -->
                    <div class="invoice p-3 mb-3">
                        <?php

                        // $balance_due passed from controller; compute here as safety fallback
                        if (!isset($balance_due)) {
                            $balance_due = max(0, $total - $this->finance_model->getDepositAmountByPaymentId($payment->id));
                        }

                        ?>
                        <style>
                        .invoice-custom {
                            font-family: 'Tahoma', Arial, sans-serif;
                        }

                        .ar {
                            direction: rtl;
                            text-align: right;
                            font-family: 'Tajawal', 'Tahoma', Arial, sans-serif;
                            unicode-bidi: plaintext;
                            font-variant-numeric: normal;
                            font-variant-ligatures: normal;
                        }

                        .arb {
                            font-family: 'Tajawal';
                            unicode-bidi: plaintext;
                            font-variant-numeric: normal;
                            font-variant-ligatures: normal;
                        }

                        .en {
                            direction: ltr;
                            text-align: left;
                        }

                        .top-bar {
                            background: linear-gradient(to right, #a38972, #495448);
                            color: #fff;
                            padding: 10px;
                            display: flex;
                            justify-content: space-between;
                        }

                        .line-top-bar {
                            background: #a38972;
                            color: #fff;
                            padding: 2px;
                            display: flex;
                            justify-content: space-between;
                        }

                        .line-bottom-bar {
                            background: #495448;
                            color: #fff;
                            padding: 2px;
                            display: flex;
                            justify-content: space-between;
                        }

                        .top-bar h2 {
                            margin: 0;
                            font-weight: bold;
                        }

                        .section {
                            display: flex;
                            justify-content: space-between;
                            margin-top: 20px;
                        }

                        .box {
                            width: 49%;
                        }

                        .table-custom {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 20px;
                        }

                        .table-custom th {
                            background: #495448;
                            color: #fff;
                            padding: 8px;
                            border: 1px solid #ccc;
                        }

                        .table-custom td {
                            border: 1px solid #ccc;
                            padding: 8px;
                            text-align: center;
                        }

                        .summary {
                            width: 40%;
                            margin-left: auto;
                            margin-top: 20px;
                        }

                        .summary td {
                            padding: 6px;
                        }

                        .total-highlight {
                            background: #7260509d;
                            color: #fff;
                            font-weight: bold;
                        }

                        .footer {
                            display: flex;
                            justify-content: space-between;
                            margin-top: 30px;
                        }

                        .company-header {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            margin-top: 20px;
                        }

                        /* FIXED WIDTHS (matches your reference design) */
                        .logo-box {
                            width: 20%;
                            display: flex;
                            justify-content: center;   /* horizontal center */
                            align-items: center;       /* vertical center */
                        }

                        .company-info {
                            width: 40%;
                            font-size: 18px;
                            line-height: 1.7;
                        }

                        /* LOGO */
                        .logo-box img {
                            max-height: 200px;
                        }

                        /* TEXT ALIGNMENT */
                        .company-info.en {
                            text-align: left;
                        }

                        .company-info.ar {
                            text-align: right;
                            direction: rtl;
                            unicode-bidi: plaintext;
                            font-feature-settings: "tnum";
                            padding-right: 15px
                        }

                        .bottom-row {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            margin-top: 30px;
                        }

                        .qr-box {
                            width: 15%;
                            padding-left: 20px;
                            text-align: left;
                        }

                        .qr-box2 {
                            width: 30%;
                            padding-left: 10px;
                            text-align: left;
                        }

                        .dsummary-box {
                            width: 40%;
                        }

                        .dsummary {
                            width: 100%;
                        }

                        /* SECOND ROW */
                        .payment-row {
                            margin-top: 3px;
                            display: flex;
                            justify-content: flex-end; /* pushes it to the right */
                        }

                        .payment-box {
                            width: 40%;
                            text-align: left; /* text stays left-aligned inside */
                        }

                        .payment-box2 {
                            width: 60%;
                            text-align: left; /* text stays left-aligned inside */
                        }

                        .barcode_first_td {
                            padding-left: 10px !important;
                        }

                        .image_bar {
                            margin-top: 15px;
                            width: 195px;
                            height: 45px !important;
                        }

                        /* =====================================================
                           PRINT STYLES
                           Fixes the "table disappears when scrolled then print"
                           bug by forcing the invoice wrapper to be the only
                           visible element and resetting scroll/overflow state.
                           ===================================================== */
                        @media print {
                            /* Hide everything that must not appear on the printout */
                            .no-print,
                            .main-header,
                            .main-sidebar,
                            .content-header,
                            .modal,
                            .breadcrumb,
                            nav,
                            aside,
                            footer,
                            .btn,
                            form:not(#deposit-form) {
                                display: none !important;
                            }

                            /* Reset body/html overflow so the full page renders */
                            html, body {
                                height: auto !important;
                                overflow: visible !important;
                                margin: 0 !important;
                                padding: 0 !important;
                            }

                            /* Make the content wrapper fill the page */
                            .content-wrapper {
                                margin: 0 !important;
                                padding: 0 !important;
                                background: #fff !important;
                                min-height: unset !important;
                                overflow: visible !important;
                            }

                            /* Ensure the invoice card fills the printable area */
                            .invoice-custom {
                                width: 100% !important;
                                overflow: visible !important;
                                page-break-inside: avoid;
                            }

                            /* Prevent table rows from being clipped across pages */
                            .table-custom {
                                page-break-inside: auto;
                            }

                            .table-custom tr {
                                page-break-inside: avoid;
                                page-break-after: auto;
                            }

                            /* Keep summary and QR together */
                            .bottom-row, .dsummary-box, .qr-box {
                                page-break-inside: avoid;
                            }
                        }

                        /* ===============================================================
                            BADGES
                            Styled to match the payment badges matching and spacing
                            accordingly, need to make the PDF to look like a natural 
                            continuation of the same document rather than a different 
                            design.
                        =============================================================== */

                        /* ── received-with-thanks / cashier / badge / amounts ── */
                        .download-footer-row {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            margin-top: 2px;
                            border-bottom: 2px solid #495448;
                            border-radius: 5px;
                            padding-top: 14px;
                            width: 90%;
                        }

                        .download-thanks {
                            padding-left: 10px;
                            font-size: 15px;
                            line-height: 1.7;
                            width: 90%;
                        }

                        .download-badge {
                            width: 50%;
                            padding-left: 10px;
                            text-align: left;
                            padding-top: 6px;
                        }

                        .paid-badge {
                            display: inline-block;
                            font-weight: bold;
                            background-color: #495448;
                            border-radius: 25px;
                            padding: 6px 12px;
                            color: #ffff;
                            font-size: 18px;
                        }

                        .due-badge {
                            display: inline-block;
                            font-weight: bold;
                            background-color: #a38972;
                            border-radius: 25px;
                            padding: 6px 12px;
                            color: #ffffff;
                            font-size: 18px;
                        }

                        .signature-line {
                            margin-top: 26px;
                            font-size: 12px;
                            line-height: 1.6;
                            border-radius: 5px;
                            border-top: 2px solid #495448;
                            color: #495448;
                            width: 50%;
                        }

                        </style>

                        <script>
                        // Fix: calling window.print() directly after scrolling causes
                        // the browser to capture only the visible viewport, making the
                        // table rows disappear. This handler scrolls to top first, waits
                        // for two animation frames to repaint, then triggers print so the
                        // full document is captured correctly.
                        function printInvoice() {
                            window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
                            requestAnimationFrame(function () {
                                requestAnimationFrame(function () {
                                    window.print();
                                });
                            });
                        }
                        </script>


                        <div class="invoice-custom">

                        <!-- HEADER -->
                        <div class="line-top-bar">
                        </div>
                            <div class="top-bar">
                                <div class="en">
                                    <h2>TAX INVOICE</h2>
                                </div>
                                <div class="ar">
                                    <h2>فاتورة ضريبية</h2>
                                </div>
                            </div>
                        <div class="line-bottom-bar">
                        </div>
                        

                            <!-- COMPANY INFO -->
                            <div class="company-header">

                            <?php
                                if (!function_exists('toArabicNumbers')) {
                                    function toArabicNumbers($number) {
                                        $number = (string) $number; // 🔥 force string

                                        $western = ['0','1','2','3','4','5','6','7','8','9'];
                                        $arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];

                                        return str_replace($western, $arabic, $number);
                                    }
                                }

                                                                
                                /*
                                * AmountInWords
                                * Fixed from original: replaced Indian denominations (Lakh / Crore)
                                * with international ones (Thousand / Million / Billion).
                                */
                                if (!function_exists('AmountInWords')) {
                                    function AmountInWords(float $amount) {
                                        $cents = (int) round(($amount - floor($amount)) * 100);
                                        $whole = (int)  floor($amount);

                                        $ones = [
                                            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
                                            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                                            'Seventeen', 'Eighteen', 'Nineteen',
                                        ];
                                        $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

                                        function _w3(int $n, array $o, array $t): string {
                                            $s = '';
                                            if ($n >= 100) { $s .= $o[(int)($n / 100)] . ' Hundred '; $n %= 100; }
                                            if ($n >= 20)  { $s .= $t[(int)($n / 10)] . ($n % 10 ? ' ' . $o[$n % 10] : '') . ' '; }
                                            elseif ($n)    { $s .= $o[$n] . ' '; }
                                            return $s;
                                        }

                                        if ($whole === 0) {
                                            $words = 'Zero';
                                        } else {
                                            $words = '';
                                            foreach ([
                                                [1000000000, 'Billion'],
                                                [1000000,    'Million'],
                                                [1000,       'Thousand'],
                                                [1,          ''],
                                            ] as [$div, $lbl]) {
                                                $part   = (int)($whole / $div);
                                                $whole %= $div;
                                                if ($part) { $words .= _w3($part, $ones, $tens) . $lbl . ' '; }
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
                            ?>

                                <!-- LOGO -->
                                <div class="logo-box">
                                    <img src="<?php echo site_url($settings->logo_title); ?>" alt="Logo">
                                </div>

                                <!-- ENGLISH INFO -->
                                <div class="company-info en" style="font-size: 20px">
                                     <b>Name:</b> <?php echo $settings->title; ?><br>
                                     <b>Address:</b> <?php echo $settings->address; ?><br>
                                     <b>C.R.:</b> <?php echo $settings->cr ?? '403012345'; ?><br>
                                     <b>VAT ID:</b> <?php echo $settings->vat_id ?? ''; ?><br>
                                     <b>Contact:</b> <?php echo $settings->phone; ?><br>
                                     <b>Email:</b><?php echo $settings->email; ?>
                                </div>

                                <!-- ARABIC INFO -->
                                <div class="company-info ar" style="font-size: 20px">
                                    <strong>اسم الشركة:</strong> مركز ود لايف للإرشاد الأسري<br>
                                    <b>العنوان: </b> شارع الصفا، حي الصفا، جدة ٤٢٥٢٥<br>
                                    <b>س. ت.:</b> <span style="font-size: 25px;"><?php echo toArabicNumbers($settings->cr ?? '403012345'); ?></span><br>
                                    <b>الرقم الضريبي: </b><span style="font-size: 25px;"><?php echo toArabicNumbers($settings->vat_id ?? ''); ?></span><br>
                                    <b>رقم التواصل: </b><span style="font-size: 25px;"><?php echo toArabicNumbers($settings->phone); ?></span><br>
                                    <b>البريد الإلكتروني: </b><?php echo $settings->email; ?>
                                </div>

                            </div>

                            <!-- BILL TO + DETAILS -->
                            <?php $patient_info = $this->db->get_where('patient', array('id' => $payment->patient))->row(); ?>

                            <div class="section">
                                <div class="box">
                                    <div style="background: linear-gradient(to right, #495448, #a38972);color:#fff;padding:5px;padding-left:20px;font-size: 24px"><b class="arb">CUSTOMER INFO / بيانات العميل</b></div>
                                        <p></p>
                                    <div style="padding-left: 15px;">
                                        <strong><?php echo lang('patient') ?> Name: / </strong> <?php echo $patient_info->name; ?>&ensp;<b>/</b><b class="ar" style="font-size: 20px;">اسم المراجع: </b><br>
                                <!--    <b>Address: / </b> <?php echo $patient_info->address; ?>&ensp;<b>/</b><b class="ar" style="font-size: 20px;">العنوان: </b><br> -->
                                        <b><?php echo lang('patient_id') ?>: /&ensp;</b> <?php echo $patient_info->id ?>&ensp; <b>/</b><b class="ar" style="font-size: 20px;">رقم تعريف المراجع: </b><br>
                                        <b>VAT ID: / </b> <?php echo $patient_info->vat ?? ''; ?>&ensp;<b>/</b><b class="ar" style="font-size: 20px;">الرقم الضريبي:  </b><br>
                                        <b>Contact No.: /</b> <?php echo $patient_info->phone; ?>&ensp;<b>/</b><b class="ar" style="font-size: 20px;">رقم التواصل: </b>
                                    </div>
                                </div>

                                <div class="box">
                                    <div style="background: linear-gradient(to right, #a38972, #495448);color:#fff;padding:5px;padding-left:10px;font-size: 24px"><b>INVOICE DETAILS / تفاصيل الفاتورة</b></div>
                                        <p></p>
                                        <div style="padding-right: 15px; text-align: right">
                                            <strong>Invoice No: / </strong><?php echo $payment->id; ?> &ensp;<b>/</b><b class="ar" style="font-size: 20px;">رقم الفاتورة: </b><br>
                                            <b>Invoice Date: / </b><?php echo date('d M Y', $payment->date); ?> <b>/</b><b class="ar" style="font-size: 20px;"> تاريخ الإصدار:</b><br>
                                            <b>Invoice Time: / </b> <?php echo date('h:i A', $payment->date); ?> <b>/</b><b class="ar" style="font-size: 20px;"> وقت الإصدار:</b><br>
                                            <b>Invoice Type: / </b> Final Invoice <b>/</b><b class="ar" style="font-size: 20px;">نوع الفاتورة: </b>
                                        </div>
                                </div>
                            </div>

                            <!-- TABLE -->
                            <table class="table-custom">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center" width="20%">Description<br><div class="ar text-center">الوصف</div></th>
                                            <th class="text-center">Qty<br><div class="ar  text-center">الكمية</div></th>
                                            <th class="text-center">Unit Price (SAR)<br><div class="ar text-center">سعر الوحدة</div></th>
                                            <th class="text-center">Total Before VAT<br><div class="ar text-center">الإجمالي قبل الضريبة</div></th>
                                            <th class="text-center">VAT %<br><div class="ar text-center">الضريبة</div></th>
                                            <th class="text-center">VAT Amount<br><div class="ar text-center">قيمة الضريبة</div></th>
                                            <th class="text-center">Total (SAR)<br><div class="ar text-center">الإجمالي</div></th>
                                        </tr>
                                    </thead>
                                <tbody>

                                <?php
                                $i = 0;
                                $subtotal = 0;

                                if (!empty($payment->category_name)) {
                                    $items = explode(',', $payment->category_name);

                                    // ZATCA: VAT must be applied on the post-discount taxable amount.
                                    // We distribute the invoice-level flat discount proportionally
                                    // across lines by their weight in the subtotal.
                                    $gross_items_total = 0;
                                    foreach ($items as $_item) {
                                        $_d = explode('*', $_item);
                                        $gross_items_total += $_d[1] * $_d[3];
                                    }

                                    foreach ($items as $item) {
                                        $i++;
                                        $data = explode('*', $item);

                                        $qty        = $data[3];
                                        $price      = $data[1];
                                        $line_gross = $qty * $price;

                                        // Proportional share of the invoice discount for this line
                                        $line_flat_discount = ($gross_items_total > 0)
                                            ? ($payment->flat_discount ?? 0) * ($line_gross / $gross_items_total)
                                            : 0;

                                        $vat_rate   = $payment->vat_amount_percent;
                                        // ZATCA: VAT on taxable (post-discount) amount
                                        $taxable    = $line_gross - $line_flat_discount;
                                        $vat_amount = ($taxable * $vat_rate) / 100;
                                        $final      = $taxable + $vat_amount;

                                        $subtotal  += $line_gross;
                                ?>

                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $this->finance_model->getPaymentcategoryById($data[0])->category; ?></td>
                                        <td><?php echo $qty; ?></td>
                                        <td><?php echo number_format($price, 2); ?></td>
                                        <td><?php echo number_format($taxable, 2); ?></td>
                                        <td><?php echo $vat_rate; ?>%</td>
                                        <td><?php echo number_format($vat_amount, 2); ?></td>
                                        <td><?php echo number_format($final, 2); ?></td>
                                    </tr>

                                <?php } } ?>

                                </tbody>
                            </table>

                            <!-- SUMMARY -->
                            <?php
                            // ZATCA: use flat_discount (monetary value) not the raw discount field
                            // which may hold a percentage string when discount_type is 'percentage'
                            $flat_discount = $payment->flat_discount ?? 0;
                            $vat_total     = $payment->vat ?? 0;
                            $total         = $payment->gross_total;
                            ?>

                            <!-- ROW 1 -->
                            <div class="bottom-row">

                                <!-- QR -->
                                <div class="qr-box">
                                    <img src="<?php echo site_url('uploads/payment_'.$payment->id.'.png'); ?>" width="180"> 
                                    <div class="qr-text"><!-- &emsp;Scan QR -->
                                    </div>
                                </div>
                                <div class="qr-box2"> 
                                    <p></p>
                                        <?php echo AmountInWords($total); ?>
                                        <p></p>
                                                <!-- Paid / Due badge -->
                                                <div class="download-badge">
                                                    <?php if ($balance_due <= 0): ?>
                                                        <span class="paid-badge">&emsp;<?php echo lang('paid'); ?>&emsp;</span>
                                                    <?php else: ?>
                                                        <span class="due-badge">&emsp;<?php echo lang('due_have'); ?>&emsp;</span>
                                                    <?php endif; ?>
                                                </div>
                                </div>
                                
                                <!-- SUMMARY -->
                                <div class="dsummary-box">
                                    <table class="dsummary">
                                        <tr>
                                            <td><b>&ensp;Subtotal:</b></td>
                                            <td><span class="saprice">R</span>&nbsp;<?php echo number_format($subtotal, 2); ?></td>
                                        </tr>
                                        <?php if ($flat_discount > 0): ?>
                                            <tr>
                                                <td><b>&ensp;Discount:</b></td>
                                                <td><span class="saprice">R</span>&nbsp;<?php echo number_format($flat_discount, 2); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td><b>&ensp;Taxable Amount:</b></td>
                                            <td><span class="saprice">R</span>&nbsp;<?php echo number_format($subtotal - $flat_discount, 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td><b>&ensp;Total VAT (<?php echo $payment->vat_amount_percent; ?>%):</b></td>
                                            <td><span class="saprice">R</span>&nbsp;<?php echo number_format($vat_total, 2); ?></td>
                                        </tr>
                                        <tr class="total-highlight">
                                            <td style="padding-top: 10px; padding-bottom: 10px"><b style="font-size: 22px;">&ensp;Invoice Total (SAR):</b></td>
                                            <td><span class="saprice" style="font-size: 23px;">R</span>&nbsp;<b style="font-size: 22px;"><?php echo number_format($total, 2); ?></b></td>
                                        </tr>
                                    </table>
                                </div>

                            </div>
                                
                            <!-- ROW 2 -->
                            <div class="payment-row">
                                <div class="payment-box2">
                                    <!-- Received with thanks -->
                                            <div class="download-footer-row">
                                            </div>    
                                              <p></p>
                                                <!-- Received with thanks + cashier signature -->
                                                <div class="download-thanks">
                                                    Thank you <strong><?php echo $patient_info->name; ?></strong>, for choosing us. It was our pleasure to serve you. We appreciate your trust and hope you are satisfied with the care and service provided.<br>
                                                    
                                                    
                                                </div>

                                                

                                            
                                            
                                            <div class="signature-line">
                                                        <br>
                                            </div>
                                    <?php if (!empty(trim($payment->remarks))) { ?>
                                                <div class="mt-1">
                                                    &emsp;<strong><?php echo lang('note'); ?> : </strong>
                                                    <?php echo $payment->remarks; ?>
                                                </div>
                                     <?php } ?>
                                </div>

                                <div class="payment-box">
                                    <p></p>
                                    <strong>Payment Details</strong><br>
                                    Bank: Bank Name<br>
                                    A/c No.: 1234 XXX 1234<br>
                                    IBAN: XXXX
                                </div>
                            </div>

                            
                            <div class="text-center" style="padding-top: 15px">
                                <?php echo $settings->footer_invoice_message; ?>
                            </div>
                                        <div style="text-align: center;">
                                                    <p></p>
                                                <img class="image_bar" alt="testing" src="<?php echo site_url('lab/barcode') ?>">
                                                <p></p><div class="qr-text"><b>000<?php echo $payment->id; ?></b><br><label class="control-label image_text"></label></div>
                                            
                                        </div>
                            </div>

                            <div class="line-top-bar">
                            </div>

                        <!-- this row will not appear when printing -->
                        <div class="row no-print">
                            <div class="col-12">

                                <button type="button" class="btn btn-success float-right depositButton" data-toggle="modal" data-id="<?php echo $payment->id ?>" data-from="<?php echo $payment->payment_from ?>"><i class="far fa-credit-card"></i>
                                    <?php echo lang('submit'); ?>
                                    <?php echo lang('payment'); ?>
                                </button>
                                <a type="button" href="finance/download?id=<?php echo $payment->id; ?>" class="btn btn-primary float-right" style="margin-right: 5px;">
                                    <i class="fas fa-download"></i> <?php echo lang('generate'); ?> PDF
                                </a>
                                <a rel="noopener" onclick="printInvoice();" class="btn btn-secondary float-right mr-2"><i class="fas fa-print"></i>
                                    <?php echo lang('print'); ?></a>

                                <?php if ($this->ion_auth->in_group(array('admin', 'Accountant'))) {

                                ?>
                                    <?php if ($payment->payment_from == 'payment' || empty($payment->payment_from)) {
                                        $lab_pending = array();
                                        $lab_reports_previous = $this->lab_model->getLabByInvoice($payment->id);

                                        if (!empty($lab_reports_previous)) {
                                            foreach ($lab_reports_previous as $lab) {
                                                if ($lab->test_status == 'not_done' || empty($lab->test_status)) {
                                                    $lab_pending[] = 'no';
                                                }
                                            }
                                        }
                                        if (count($lab_reports_previous) == count($lab_pending) || empty($lab_reports_previous)) {
                                    ?>
                                <!--             <a href="finance/editPayment?id=--><?php //echo $payment->id; ?><!--" class="btn btn-secondary editbutton float-left mr-2"><i class="fa fa-edit"></i>-->
                                                <?php //echo lang('edit'); ?><!-- --><?php //echo lang('invoice'); ?> <!-- </a> -->
                                    <?php }
                                    } ?>

                                <?php } ?>


                                <?php if ($payment->payment_from == 'payment' || empty($payment->payment_from)) { ?>
                                    <a href="finance/payment" class="btn btn-secondary float-left mb-1"><i class="fa fa-arrow-circle-left"></i>
                                        <?php echo lang('back_to_payment_modules'); ?> </a>
                                <?php } ?>



                            </div>


                        </div>
                    </div>


                    <div class="col-md-7 no-print card card-body">
                        <form role="form" action="finance/sendInvoice" method="post" enctype="multipart/form-data">
                            <div class="radio radio_button">
                                <label>
                                    <input type="radio" name="radio" id="optionsRadios2" value="patient" checked="checked">
                                    <?php echo lang('send_invoice_to_patient'); ?>
                                </label>
                            </div>
                            <div class="radio radio_button">
                                <label>
                                    <input type="radio" name="radio" id="optionsRadios2" value="other">
                                    <?php echo lang('send_invoice_to_others'); ?>
                                </label>
                            </div>
                            <input type="hidden" name="id" value="<?php echo $payment->id; ?>">
                            <div class="radio other" style="display:none;">
                                <label>
                                    <?php echo lang('email'); ?> <?php echo lang('address'); ?>
                                    <input type="email" name="other_email" value="" class="form-control form-control-lg">
                                </label>

                            </div>

                            <button type="submit" name="submit" class="btn btn-success float-left my-3"><i class="fa fa-location-arrow"></i> <?php echo lang('send'); ?></button>

                        </form>
                    </div>



                    <!-- /.invoice -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<div class="modal fade" id="myModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold"> <?php echo lang('add_deposit'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form role="form" action="finance/deposit" id="deposit-form" class="clearfix" method="post" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1"> <?php echo lang('patient'); ?> <?php echo lang('name'); ?>
                                &ast; </label>
                            <input type="text" class="form-control form-control-lg" name="name" id="name" value='' placeholder="" readonly="">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1"> <?php echo lang('invoice_no'); ?> &ast; </label>
                            <input type="text" class="form-control form-control-lg" name="invoice_no" id="invoice_no" value='' placeholder="" readonly="">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1"> <?php echo lang('date'); ?> &ast; </label>
                            <input type="text" class="form-control form-control-lg" name="date" id="date" value='' placeholder="" readonly="">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1"> <?php echo lang('due'); ?>
                                <?php echo lang('amount'); ?></label>
                            <input type="text" class="form-control form-control-lg" id="due_amount" name="due" value='' placeholder="" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1"> <?php echo lang('deposit_amount'); ?></label>
                            <input type="text" class="form-control form-control-lg" name="deposited_amount" id="deposited_amount" value='' placeholder="" required>
                        </div>



                        <div class="form-group col-md-12">
                            <div class="">
                                <label for="exampleInputEmail1"> <?php echo lang('deposit_type'); ?></label>
                            </div>
                            <div class="">
                                <select class="form-control form-control-lg m-bot15 js-example-basic-single selecttype" id="selecttype" name="deposit_type" value=''>
                                    <?php if ($this->ion_auth->in_group(array('admin', 'Accountant', 'Receptionist'))) { ?>
                                        <option value="Cash"> <?php echo lang('cash'); ?> </option>
                                        <!-- <option value="Insurance"> <?php echo lang('insurance'); ?> </option> -->
                                        <option value="Card"> <?php echo lang('card'); ?> </option>
                                    <?php } ?>


                                </select>
                            </div>

                            <div class="hidden insurance_div">

                                <div class="form-group" style="margin-top:10px;">
                                    <label for="exampleInputEmail1"> <?php echo lang('insurance_company'); ?> <?php echo lang('name'); ?></label>

                                    <div class="company_div">
                                        <select class="form-control form-control-lg m-bot15 js-example-basic-single" name="insurance_company" id="insurance_company" value=''>
                                            <option value="">Select Company</option>
                                            <?php foreach ($insurance_companys as $insurance_company) { ?>
                                                <option value="<?php echo $insurance_company->id; ?>" <?php


                                                                                                        ?>>
                                                    <?php echo $insurance_company->name; ?> </option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                </div>
                                <div class="">
                                    <div class="payment_label" style="margin-top:10px;">
                                        <label for="exampleInputEmail1"> <?php echo lang('insurance_details'); ?>
                                        </label>
                                    </div>
                                    <div class="">
                                        <textarea class="form-control form-control-lg" name="insurance_details" rows="2" cols="20"></textarea>
                                    </div>

                                </div>
                            </div>

                            <?php
                            $payment_gateway = $settings->payment_gateway;
                            ?>



                            <div class="cardPayment row mt-3">

                                <div class="col-md-12 payment pad_bot">
                                    <label for="exampleInputEmail1"> <?php echo lang('accepted'); ?>
                                        <?php echo lang('cards'); ?></label>
                                    <div class="payment pad_bot">
                                        <img src="uploads/card.png" width="100%">
                                    </div>
                                </div>
                                <?php
                                if ($payment_gateway == 'PayPal') {
                                ?>

                                    <div class="col-md-12 payment pad_bot">
                                        <label for="exampleInputEmail1"> <?php echo lang('card'); ?>
                                            <?php echo lang('type'); ?></label>
                                        <select class="form-control form-control-lg m-bot15" name="card_type" value=''>

                                            <option value="Mastercard"> <?php echo lang('mastercard'); ?> </option>
                                            <option value="Visa"> <?php echo lang('visa'); ?> </option>
                                            <option value="American Express"> <?php echo lang('american_express'); ?>
                                            </option>
                                        </select>
                                    </div>
                                <?php } ?>
                                <?php if ($payment_gateway == '2Checkout' || $payment_gateway == 'PayPal') {
                                ?>
                                    <div class="col-md-12 payment pad_bot mt-3">
                                        <label for="exampleInputEmail1"> <?php echo lang('cardholder'); ?>
                                            <?php echo lang('name'); ?></label>
                                        <input type="text" id="cardholder" class="form-control pay_in" name="cardholder" value='' placeholder="">
                                    </div>
                                <?php } ?>
                                <?php if ($payment_gateway != 'Pay U Money' && $payment_gateway != 'Paystack' && $payment_gateway != 'SSLCOMMERZ' && $payment_gateway != 'Paytm') { ?>
                                    <div class="col-md-12 payment pad_bot mt-3">
                                        <label for="exampleInputEmail1"> <?php echo lang('card'); ?>
                                            <?php echo lang('number'); ?></label>
                                        <input type="text" class="form-control pay_in" id="card" name="card_number" value='' placeholder="">
                                    </div>



                                    <div class="col-md-8 payment pad_bot mt-3">
                                        <label for="exampleInputEmail1"> <?php echo lang('expire'); ?>
                                            <?php echo lang('date'); ?></label>
                                        <input type="text" class="form-control pay_in" id="expire" data-date="" data-date-format="MM YY" placeholder="Expiry (MM/YY)" name="expire_date" maxlength="7" aria-describedby="basic-addon1" value=''>
                                    </div>
                                    <div class="col-md-4 payment pad_bot mt-3">
                                        <label for="exampleInputEmail1"> <?php echo lang('cvv'); ?> </label>
                                        <input type="text" class="form-control pay_in" id="cvv" maxlength="3" name="cvv" value='' placeholder="">
                                    </div>
                                <?php
                                }
                                ?>
                            </div>



                        </div>


                        <input type="hidden" name="redirect" value="due">
                        <input type="hidden" name="id" id="id" value=''>
                        <input type="hidden" name="patient" id="patient_id" value=''>
                        <input type="hidden" name="payment_id" id="payment_id" value=''>
                        <div class="cashsubmit payment btn-block mt-4">
                            <button type="submit" name="submit2" id="submit1" class="btn btn-primary btn-block float-right"> <?php echo lang('submit'); ?></button>
                        </div>
                        <div class="cardsubmit d-none btn-block mt-4">
                            <?php $twocheckout = $this->db->get_where('paymentGateway', array('name =' => '2Checkout'))->row(); ?>
                            <button type="submit" name="pay_now" id="submit-btn" class="btn btn-primary btn-block float-right" <?php if ($settings->payment_gateway == 'Stripe') {
                                                                                                                                ?>onClick="stripePay(event);" <?php }
                                                                                                                                                                ?><?php if ($settings->payment_gateway == '2Checkout' && $twocheckout->status == 'live') {
                                                                                                                                                                    ?>onClick="twoCheckoutPay(event);" <?php }
                                                                                                                                                                                                        ?>>
                                <?php echo lang('submit'); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<script src="common/js/codearistos.min.js"></script>
<script type="text/javascript">
    var language = "<?php echo $this->language; ?>";
</script>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script src="common/extranal/js/finance/invoice.js"></script>
<?php if (!empty($gateway->publish)) {
    $gateway_stripe = $gateway->publish;
} else {
    $gateway_stripe = '';
} ?>




<script type="text/javascript">
    var publish = "<?php echo $gateway_stripe; ?>";
</script>
<script src="common/js/moment.min.js"></script>

<script type="text/javascript">
    var payment_gateway = "<?php echo $settings->payment_gateway; ?>";
</script>

<script src="common/extranal/js/finance/patient_deposit.js"></script>

<script>
    window.addEventListener("load", window.print());
</script>
