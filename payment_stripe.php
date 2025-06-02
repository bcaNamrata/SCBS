<?php
require_once('./config.php');

// Get booking_id from query param
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
if (!$booking_id) {
    echo "<div class='alert alert-danger'>Invalid booking ID.</div>";
    exit;
}

// Fetch booking details
$qry = $conn->query("
    SELECT 
        b.ref_code, 
        c.firstname, 
        f.name as facility_name,
        a.price as facility_price,
        f.id as facility_id
    FROM booking_list b
    INNER JOIN client_list c ON b.client_id = c.id
    INNER JOIN facility_list f ON b.facility_id = f.id
    LEFT JOIN amount a ON f.id = a.facility_id
    WHERE b.id = {$booking_id}
");

if (!$qry || $qry->num_rows == 0) {
    echo "<div class='alert alert-danger'>Booking not found.</div>";
    exit;
}

$row = $qry->fetch_assoc();

$stripe_secret_key = '';
$stripe_publishable_key = '';
// Stripe Test Keys (replace with your own keys!)

// Calculate amount in cents
$amount = isset($row['facility_price']) ? intval($row['facility_price'] * 100) : 5000;

// Create PaymentIntent via cURL
$data = http_build_query([
    'amount' => $amount,
    'currency' => 'usd',
    'metadata[booking_id]' => $booking_id,
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_USERPWD, $stripe_secret_key . ":");

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode != 200) {
    echo "<div class='alert alert-danger'>Failed to create payment intent.</div>";
    exit;
}

$paymentIntent = json_decode($response, true);
$client_secret = $paymentIntent['client_secret'];
?>

<style>
.StripeElement {
  display: block;
  width: 100%;
  height: 44px;
  padding: 12px 14px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
  margin-bottom: 1rem;
  background-color: white;
  font-size: 16px;
  line-height: 1.4;
}
  .form-group {
    margin-bottom: 1rem;
  }
  label {
    font-weight: 600;
    display: block;
    margin-bottom: 5px;
  }
  #payment-message {
    margin-top: 10px;
    color: red;
  }
</style>

<div>
    <h4>Pay for Booking #<?= htmlspecialchars($booking_id) ?></h4>
    <p><strong>Reference Code:</strong> <?= htmlspecialchars($row['ref_code']) ?></p>
    <p><strong>Client Name:</strong> <?= htmlspecialchars($row['firstname']) ?></p>
    <p><strong>Facility:</strong> <?= htmlspecialchars($row['facility_name']) ?></p>
    <p><strong>Amount:</strong> 
        <?= isset($row['facility_price']) ? 'Nrp ' . number_format($row['facility_price'], 2) : '<em>Not Set</em>' ?>
    </p>
</div>

<form id="payment-form" style="max-width: 400px;">
  <div class="form-group">
    <label for="first-name">First Name</label>
    <input id="first-name" name="first_name" type="text" class="form-control" placeholder="First Name" required>
  </div>
  <div class="form-group">
    <label for="last-name">Last Name</label>
    <input id="last-name" name="last_name" type="text" class="form-control" placeholder="Last Name" required>
  </div>
  <div class="form-group">
    <label for="email">Email Address</label>
    <input id="email" name="email" type="email" class="form-control" placeholder="Email Address" required>
  </div>

  <div class="form-group">
    <label for="card-element">Credit Card Number</label>
    <div id="card-element" class="StripeElement"></div>
  </div>

  <div class="form-group">
    <label for="card-expiry-element">Expiration Date</label>
    <div id="card-expiry-element" class="StripeElement"></div>
  </div>

  <div class="form-group">
    <label for="card-cvc-element">CVC</label>
    <div id="card-cvc-element" class="StripeElement"></div>
  </div>

  <button type="submit" class="btn btn-primary">Pay</button>
  <div id="payment-message"></div>
</form>

<script src="https://js.stripe.com/v3/"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  const stripe = Stripe('<?= $stripe_publishable_key ?>');
  const elements = stripe.elements();

  const style = {
    base: {
      fontSize: '16px',
      color: '#32325d',
      '::placeholder': {
        color: '#a0aec0',
      },
      padding: '12px 14px',
    },
    invalid: {
      color: '#fa755a',
      iconColor: '#fa755a',
    }
  };

  const cardNumber = elements.create('cardNumber', {style, placeholder: '4242 4242 4242 4242'});
  cardNumber.mount('#card-element');

  const cardExpiry = elements.create('cardExpiry', {style, placeholder: 'MM / YY'});
  cardExpiry.mount('#card-expiry-element');

  const cardCvc = elements.create('cardCvc', {style, placeholder: 'CVC'});
  cardCvc.mount('#card-cvc-element');

  const form = document.getElementById('payment-form');
  const paymentMessage = document.getElementById('payment-message');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    paymentMessage.textContent = '';
    paymentMessage.style.color = 'red';

    // Optionally validate your inputs here

    const {error, paymentIntent} = await stripe.confirmCardPayment('<?= $client_secret ?>', {
      payment_method: {
        card: cardNumber,
        billing_details: {
          name: document.getElementById('first-name').value + ' ' + document.getElementById('last-name').value,
          email: document.getElementById('email').value,
        },
      }
    });

    if (error) {
      paymentMessage.textContent = error.message;
    } else if (paymentIntent && paymentIntent.status === 'succeeded') {
        console.log("Posting payment update...");
$.post('http://localhost:8080/scbs/classes/Master.php?f=update_payment_status', {
        booking_id: <?= $booking_id ?>,
        payment_status: 'done',
        first_name: document.getElementById('first-name').value,
        last_name: document.getElementById('last-name').value,
        email: document.getElementById('email').value,
        transaction_id: paymentIntent.id
      }, function(resp) {
        if (resp.status === 'success') {
          alert('Payment recorded successfully');
          
            paymentMessage.style.color = 'green';
      paymentMessage.textContent = 'Payment succeeded!';
          // Redirect after 2 seconds
          setTimeout(() => {
            window.location.href = 'http://localhost:8080/scbs/?p=booking_list';
          }, 2000);
        } else {
          alert('Failed to update payment status.');
        }
      }, 'json');
    

      // Send AJAX request to update payment status, pass transaction id and user details
      
    }
  });
</script>
