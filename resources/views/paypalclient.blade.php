<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <!-- <title>会员付款</title> -->
</head>
<body>
<script
    src="https://www.paypal.com/sdk/js?client-id=AZeJRPH86pfGCwR3axOhotnk0f9CcMcobD2TtadouQ9uIusr2AO_VfFex_UpEq_9vz_F-4-fIs9mBJF5&currency=EUR"> // Required. Replace SB_CLIENT_ID with your sandbox client ID.
  </script>
<div id="paypal-button-container"></div>
</body>
<script>
  paypal.Buttons({
    createOrder: function(data, actions) {
      return actions.order.create({
        purchase_units: [{
          amount: {
            value: '0.01'
          }
        }]
      });
    },
    onApprove: function(data, actions) {
      return actions.order.capture().then(function(details) {
        alert('Transaction completed by ' + details.payer.name.given_name);
        // Call your server to save the transaction
        return fetch('/paypal-transaction-complete', {
          method: 'post',
          headers: {
            'content-type': 'application/json'
          },
          body: JSON.stringify({
            orderID: data.orderID
          })
        });
      });
    }
  }).render('#paypal-button-container');
</html>
