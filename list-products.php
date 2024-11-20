<?php
require 'init.php';  

$products = $stripe->products->all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Products</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
            line-height: 1.6;
        }

        header {
            background-color: #fff;
            padding: 40px 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        header p {
            font-size: 1.2rem;
            color: #777;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 0 10px;
        }

        .product {
            background-color: #fff;
            border-radius: 8px;
            width: 280px;
            margin: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .product img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .product h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .product-description {
            font-size: 1rem;
            color: #666;
            margin-bottom: 20px;
        }

        .price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            display: flex;
            justify-content: space-between;
        }

        .price span {
            font-size: 1rem;
            color: #888;
        }

        @media (max-width: 768px) {
            .product {
                width: 220px;
            }
        }

        @media (max-width: 480px) {
            .product {
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>

    <header>
        <h1>Our Product Collection</h1>
        <p>Browse through our exclusive products below.</p>
    </header>

    <div class="container">
        <?php foreach ($products->data as $product): ?>
            <div class="product">
                <?php 
                    $image = !empty($product->images) ? $product->images[0] : 'https://via.placeholder.com/300';
                    
                    $price = $stripe->prices->retrieve($product->default_price);
                ?>
                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product->name); ?>">
                <h2><?php echo htmlspecialchars($product->name); ?></h2>
                <div class="product-description">
                    <?php echo htmlspecialchars($product->description ?? 'No description available'); ?>
                </div>
                <div class="price">
                    <span><?php echo strtoupper($price->currency); ?></span>
                    <span><?php echo number_format($price->unit_amount / 100, 2); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
