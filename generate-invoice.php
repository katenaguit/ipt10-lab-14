<?php
require 'init.php'; 

$customers = $stripe->customers->all();

$products = $stripe->products->all();

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerId = $_POST['customer_id'];
    $selectedProducts = $_POST['products'] ?? [];

    if (empty($selectedProducts)) {
        $errorMessage = 'Please select at least one product.';
    } else {
        try {
            $invoice = $stripe->invoices->create([
                'customer' => $customerId,
            ]);

            foreach ($selectedProducts as $productId) {
                $product = $stripe->products->retrieve($productId);
                $price = $stripe->prices->retrieve($product->default_price);
                $stripe->invoiceItems->create([
                    'customer' => $customerId,
                    'price' => $price->id,
                    'invoice' => $invoice->id
                ]);
            }

            $stripe->invoices->finalizeInvoice($invoice->id);

            $invoice = $stripe->invoices->retrieve($invoice->id);

            $successMessage = [
                'hosted_invoice_url' => $invoice->hosted_invoice_url,
                'invoice_pdf' => $invoice->invoice_pdf
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            $errorMessage = 'Error: ' . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 60%;
            margin: 0 auto;
            padding: 30px;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 50px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        select, input[type="checkbox"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background: linear-gradient(135deg, #6c63ff, #2a65e1); 
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            width: 100%;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        button:hover {
            background-color: #0056b3;
            transform: translateY(-2px); 
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        button:active {
            background-color: #004080;
            transform: translateY(0); 
        }
        .message {
            text-align: center;
            font-size: 16px;
        }
        .message.success {
            color: green;
        }
        .message.error {
            color: red;
        }
        .products-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between; 
        }
        .product-item {
            width: calc(33.333% - 20px); 
            display: flex;
            flex-direction: column;
            align-items: stretch; 
            text-align: center;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            min-height: 400px; 
            box-sizing: border-box;
        }
        .product-item input[type="checkbox"] {
            margin-bottom: 10px;
        }
        .product-item img {
            width: 100%;  
            height: auto;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        .product-item .description {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .product-item .price {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        @media (max-width: 768px) {
            .product-item {
                width: calc(50% - 20px); 
            }
        }
        @media (max-width: 480px) {
            .product-item {
                width: 100%; 
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Create Invoice</h2>

        <?php if ($successMessage): ?>
            <div class="message success">
                <p>Invoice created successfully!</p>
                <a href="<?= $successMessage['hosted_invoice_url'] ?>" target="_blank">Go to Hosted Invoice</a><br>
                <a href="<?= $successMessage['invoice_pdf'] ?>" target="_blank">Download Invoice PDF</a>
            </div>
        <?php elseif ($errorMessage): ?>
            <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <form action="" method="POST">

            <label for="customer_id">Select Customer</label>
            <select name="customer_id" id="customer_id" required>
                <option value="">Select a customer</option>
                <?php foreach ($customers->data as $customer): ?>
                    <option value="<?= $customer->id ?>"><?= $customer->name ?> (<?= $customer->email ?>)</option>
                <?php endforeach; ?>
            </select>

            <label for="products">Select Products</label>
            <div class="products-list">
                <?php foreach ($products->data as $product): ?>
                    <?php
                    $price = $stripe->prices->retrieve($product->default_price);
                    $currency = strtoupper($price->currency);
                    $unit_amount = number_format($price->unit_amount / 100, 2); 
                    $imageUrl = $product->images[0] ?? ''; 
                    ?>

                    <div class="product-item">
                        <?php if ($imageUrl): ?>
                            <img src="<?= $imageUrl ?>" alt="<?= $product->name ?> image">
                        <?php endif; ?>
                        <input type="checkbox" name="products[]" value="<?= $product->id ?>" id="product-<?= $product->id ?>">
                        <div class="description"><?= $product->name ?> - <?= $product->description ?></div>
                        <div class="price"><?= $currency ?> <?= $unit_amount ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit">Create Invoice</button>
        </form>
    </div>

</body>
</html>
