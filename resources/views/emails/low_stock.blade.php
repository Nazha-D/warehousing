<!DOCTYPE html>
<html>
<head>
    <title>تنبيه المخزون</title>
</head>
<body style="direction: rtl; font-family: Tahoma, sans-serif;">
    <h2>Low Stock!</h2>
    <p>please note that item  <strong>{{ $item->item_name }}</strong>has reached alert limit in warehouse : <strong>{{ $warehouse->name }}</strong>.</p>
<ul>
    <li>current quantity is <span style="color: red;">{{ $quantity }}</span></li>
    <li>alert limit is  {{ $item->alert_level }}</li>
</ul>

</body>
</html>
