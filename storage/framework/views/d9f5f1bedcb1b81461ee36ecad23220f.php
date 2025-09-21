<!DOCTYPE html>
<html>
<head>
    <title>Invoice - <?php echo e($salesorder->invoiceNumber ?? 'INV/' . \Carbon\Carbon::parse($salesorder->salesDate)->format('m/Y') . '/' . $salesorder->salesID); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            margin: 40px; /* Lebar margin kiri-kanan lebih besar */
            color: #333;
            position: relative;
            padding-bottom: 100px;
        }

        .header-top {
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .header-top img {
            max-width: 80px;
            height: auto;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-left: 15px;
        }

        .invoice-center {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .invoice-center h1 {
            font-size: 26px;
            margin: 0;
            letter-spacing: 1px;
        }

        .invoice-center p {
            margin: 4px 0;
            font-size: 14px;
        }

        .info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .info-col {
            width: 48%;
        }

        .info-col p {
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            border: 1px solid #aaa;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
        }

        .total-amount {
            text-align: right;
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
            page-break-inside: avoid;
            page-break-before: avoid;
        }

        .thankyou {
            text-align: center;
            margin-top: 50px;
            font-style: italic;
            font-size: 15px;
        }

        .footer {
            position: fixed;
            bottom: 0px;
            left: 40px;
            font-size: 12px;
            color: #ffffff;
            width: calc(100% - 160px); /* menyesuaikan margin kiri-kanan */
        }

        @media print {
            body {
                margin: 0;
                padding: 0 50px 100px 50px;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            tr, td, th {
                page-break-inside: avoid;
                padding: 0px 10px 0px 10px;
            }
        }
    </style>

</head>
<body onload="window.print()">

    
    <div class="header-top">
        <img src="<?php echo e(asset('img/logo.jpg')); ?>" alt="Company Logo">
        <div class="company-name">Gwan Global Digital</div>
    </div>

    
    <div class="invoice-center">
        <h1>INVOICE</h1>
        <p><strong>No:</strong> <?php echo e($salesorder->invoiceNumber ?? 'INV/' . \Carbon\Carbon::parse($salesorder->salesDate)->format('m/Y') . '/' . $salesorder->salesID); ?></p>
    </div>

    <hr>

    
    
    <div class="info">
        <div class="info-col-2">
            <p><strong>Tanggal Order:</strong> <?php echo e(\Carbon\Carbon::parse($salesorder->salesDate)->format('d F Y')); ?></p>

            <?php if(strtolower(trim($salesorder->customer->customerName)) !== 'one time customer'): ?>
                <p><strong>Customer:</strong> <?php echo e($salesorder->customer->customerName); ?></p>

                <?php if(!empty($salesorder->customer->telephone)): ?>
                    <p><strong>Telepon:</strong> <?php echo e($salesorder->customer->telephone); ?></p>
                <?php endif; ?>

                <?php if(!empty($salesorder->customer->address)): ?>
                    <p><strong>Alamat:</strong> <?php echo e($salesorder->customer->address); ?></p>
                <?php endif; ?>

                <br>
                <p><strong>Keterangan:</strong> <?php echo e($salesorder->description ?? '-'); ?></p>
            <?php else: ?>
                <p> <?php echo e($salesorder->description ?? '-'); ?></p>
            <?php endif; ?>

            
        </div>
    </div>


    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Produk</th>
                <th>Qty</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0; ?>
            <?php $__currentLoopData = $salesorder->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $subtotal = $item->quantity * $item->price;
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($item->product->productName); ?></td>
                    <td><?php echo e($item->quantity); ?></td>
                    <td>Rp<?php echo e(number_format($item->price, 0, ',', '.')); ?></td>
                    <td>Rp<?php echo e(number_format($subtotal, 0, ',', '.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>


    <div class="total-amount">
        <strong>Discount:</strong> Rp<?php echo e(number_format($salesorder->discount_order, 0, ',', '.')); ?>

    </div>
    <div class="total-amount">
        <strong>Total:</strong> Rp<?php echo e(number_format($total-$salesorder->discount_order, 0, ',', '.')); ?>

    </div>

    
    <div class="thankyou">
        <p>Terima kasih atas kepercayaan Anda kepada kami.</p>
    </div>

    
    <div class="footer">
        Dicetak pada <?php echo e(\Carbon\Carbon::now()->format('d F Y, H:i')); ?>

    </div>

</body>
</html>
<?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/sales/print-invoice.blade.php ENDPATH**/ ?>