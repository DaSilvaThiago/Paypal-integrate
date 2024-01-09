<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laravel - PayPal Integration</title>
</head>
<body>
    
    <h2>Product: LapTop</h2>
    <h3>Price: $5</h3>

    <form action="{{route('paypal')}}" method="post">
        @csrf
        <input type="hidden" name="price" value="5">
        <input type="hidden" name="product_name" value="LapTop">
        <input type="hidden" name="quantity" value="1">
        <button type="submit"> Pay With PayPal</button>
    </form>
</body>
</html>