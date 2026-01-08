<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Now - MG Skill</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .page-wrapper { min-height: 80vh; padding-top: 20px; }
        .card { border: none; border-radius: 15px; }
        .card-header { border-radius: 15px 15px 0 0 !important; background: linear-gradient(90deg, #1358db 0%, #5b61ff 100%); }
        .btn-primary { background-color: #1358db; border-color: #1358db; }
        .btn-primary:hover { background-color: #0b45b0; border-color: #0b45b0; }
        .amount-btn.active { background-color: #1358db !important; border-color: #1358db !important; color: white !important; }
        .amount-btn:hover { background-color: #eef6ff; color: #1358db; border-color: #1358db; }
        .amount-btn.active:hover { background-color: #0b45b0; color: white; }
    </style>
</head>
<body>

<?php
include '../../includes/header.php';
?>

<div class="page-wrapper">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-lg mt-5 mb-5">
                    <div class="card-header justify-content-center text-center bg-primary text-white">
                        <h3 class="font-weight-light my-2">Make a Contribution</h3>
                        <p class="mb-0">Your support helps us empower education and social initiatives.</p>
                    </div>
                    <div class="card-body p-5">
                        <form id="donationForm">
                            
                            <div class="form-group mb-4">
                                <label class="h5 mb-3">Select Donation Amount</label>
                                <div class="d-flex flex-wrap gap-2 justify-content-center" id="amount-buttons">
                                    <button type="button" class="btn btn-outline-primary btn-lg amount-btn" data-amount="101" data-purpose="Support a cause">₹101</button>
                                    <button type="button" class="btn btn-outline-primary btn-lg amount-btn" data-amount="501" data-purpose="Education & awareness support">₹501</button>
                                    <button type="button" class="btn btn-outline-primary btn-lg amount-btn" data-amount="1001" data-purpose="Student learning assistance">₹1,001</button>
                                    <button type="button" class="btn btn-outline-primary btn-lg amount-btn" data-amount="2100" data-purpose="Event / campaign support">₹2,100</button>
                                    <button type="button" class="btn btn-outline-primary btn-lg amount-btn" data-amount="5100" data-purpose="Sponsor a social initiative">₹5,100+</button>
                                </div>
                                <div class="mt-3">
                                    <label>Or Enter Custom Amount (₹)</label>
                                    <input type="number" class="form-control form-control-lg" id="customAmount" placeholder="Enter amount">
                                </div>
                                <input type="hidden" name="amount" id="finalAmount" value="">
                                <input type="hidden" name="purpose" id="purpose" value="General Donation">
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Donor Details</h5>
                            
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label>Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="full_name" required>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label>Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label>Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="mobile" required pattern="[0-9]{10}" title="10 digit mobile number">
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label>PAN Card (Optional, for 80G)</label>
                                    <input type="text" class="form-control" name="pan_card" placeholder="ABCDE1234F">
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>Address (Optional)</label>
                                <textarea class="form-control" name="address" rows="2"></textarea>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg btn-block" id="donateBtn">Donate Now</button>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    
    // Amount Selection Logic
    $('.amount-btn').click(function() {
        $('.amount-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
        
        let amount = $(this).data('amount');
        let purpose = $(this).data('purpose');
        
        $('#finalAmount').val(amount);
        $('#customAmount').val(''); // Clear custom input
        $('#purpose').val(purpose);
    });
    
    $('#customAmount').on('input', function() {
        $('.amount-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
        let amount = $(this).val();
        $('#finalAmount').val(amount);
        $('#purpose').val('Custom Donation');
    });

    // Form Submission
    $('#donationForm').submit(function(e) {
        e.preventDefault();
        
        let amount = $('#finalAmount').val();
        if(!amount || amount < 1) {
            alert("Please select a valid donation amount (Minimum ₹1)");
            return;
        }
        
        // Disable button
        $('#donateBtn').prop('disabled', true).text('Processing...');

        // 1. Create Order
        $.ajax({
            url: 'create_order.php',
            type: 'POST',
            data: { amount: amount },
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success') {
                    
                    // 2. Open Razorpay
                    var options = {
                        "key": response.key_id,
                        "amount": response.amount * 100, 
                        "currency": "INR",
                        "name": response.name,
                        "description": response.description,
                        "image": response.image,
                        "order_id": response.order_id, 
                        "handler": function (paymentParams){
                            verifyPayment(paymentParams, $('#donationForm').serializeArray());
                        },
                        "prefill": {
                            "name": $('input[name="full_name"]').val(),
                            "email": $('input[name="email"]').val(),
                            "contact": $('input[name="mobile"]').val()
                        },
                        "theme": {
                            "color": "#3399cc"
                        },
                        "modal": {
                            "ondismiss": function(){
                                $('#donateBtn').prop('disabled', false).text('Donate Now');
                            }
                        }
                    };
                    var rzp1 = new Razorpay(options);
                    rzp1.open();
                    
                } else {
                    alert(response.message);
                    $('#donateBtn').prop('disabled', false).text('Donate Now');
                }
            },
            error: function() {
                alert("Something went wrong with order creation.");
                $('#donateBtn').prop('disabled', false).text('Donate Now');
            }
        });
    });
    
    function verifyPayment(paymentParams, formData) {
        // Merge payment params with form data
        let data = {};
        $(formData).each(function(index, obj){
            data[obj.name] = obj.value;
        });
        
        data.razorpay_payment_id = paymentParams.razorpay_payment_id;
        data.razorpay_order_id = paymentParams.razorpay_order_id;
        data.razorpay_signature = paymentParams.razorpay_signature;
        
        // 3. Verify & Save
        $.ajax({
            url: 'process_donation.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success') {
                    // Redirect to receipt page
                     window.location.href = "receipt.php?id=" + response.donation_id;
                } else {
                    alert("Payment Success but Verification Failed: " + response.message);
                    $('#donateBtn').prop('disabled', false).text('Donate Now');
                }
            },
            error: function() {
                alert("Server error during verification.");
                $('#donateBtn').prop('disabled', false).text('Donate Now');
            }
        });
    }
});
</script>

<style>
.amount-btn { min-width: 100px; }
.gap-2 { gap: 0.5rem; }
</style>

<?php
include '../../includes/footer.php';
?>
</body>
</html>
