<?php
require_once('./config.php');

if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * from `booking_list` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k = $v;
        }
        $qry2 = $conn->query("SELECT f.*, c.name as category from `facility_list` f inner join category_list c on f.category_id = c.id where f.id = '{$facility_id}' ");
        if($qry2->num_rows > 0){
            foreach($qry2->fetch_assoc() as $k => $v){
                if(!isset($$k))
                    $$k = $v;
            }
        }

        // Fetch time slots for this booking
        $time_slots = [];
        $ts_qry = $conn->query("SELECT slot FROM `time_slot` WHERE booking_id = '{$id}' ");
        if($ts_qry->num_rows > 0){
            while($row = $ts_qry->fetch_assoc()){
                $time_slots[] = $row['slot'];
            }
        }

        // Fetch payment details for this booking
        $payment = null;
        $payment_qry = $conn->query("SELECT * FROM `payment` WHERE booking_id = '{$id}' LIMIT 1");
        if($payment_qry->num_rows > 0){
            $payment = $payment_qry->fetch_assoc();
        }
    }
}
?>

<style>
    /* Hide everything except print content when printing */
    @media print {
        body * {
            visibility: hidden !important;
        }
        #printable-content, #printable-content * {
            visibility: visible !important;
        }
        #printable-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0 1rem;
            font-family: Arial, sans-serif;
        }
    }
</style>

<div class="container-fluid" id="printable-content">
    <fieldset class="border-bottom">
        <legend class="h5 text-muted">Facility Details</legend>
        <dl>
            <dt>Facility Code</dt>
            <dd class="pl-4"><?= isset($facility_code) ? htmlspecialchars($facility_code) : "" ?></dd>
            <dt>Name</dt>
            <dd class="pl-4"><?= isset($name) ? htmlspecialchars($name) : "" ?></dd>
            <dt>Category</dt>
            <dd class="pl-4"><?= isset($category) ? htmlspecialchars($category) : "" ?></dd>
        </dl>
    </fieldset>

    <div class="clear-fix my-2"></div>

    <fieldset class="bor">
        <legend class="h5 text-muted">Booking Details</legend>
        <dl>
            <dt>Ref. Code</dt>
            <dd class="pl-4"><?= isset($ref_code) ? htmlspecialchars($ref_code) : "" ?></dd>
            <dt>Schedule</dt>
            <dd class="pl-4">
                <?php 
                    if($date_from == $date_to){
                        echo date("M d, Y", strtotime($date_from));
                    }else{
                        echo date("M d, Y", strtotime($date_from))." - ".date("M d, Y", strtotime($date_to));
                    }
                ?>
            </dd>
            <dt>Time Slot</dt>
            <dd class="pl-4">
                <?php 
                    if(!empty($time_slots)){
                        echo htmlspecialchars(implode(", ", $time_slots));
                    } else {
                        echo "N/A";
                    }
                ?>
            </dd>
            <dt>Status</dt>
            <dd class="pl-4">
                <?php 
                    switch($status){
                        case 0:
                            echo "Pending";
                            break;
                        case 1:
                            echo "Confirmed";
                            break;
                        case 2:
                            echo "Done";
                            break;
                        case 3:
                            echo "Cancelled";
                            break;
                        default:
                            echo "Unknown";
                            break;
                    }
                ?>
            </dd>
        </dl>
    </fieldset>

    <fieldset class="bor mt-3">
        <legend class="h5 text-muted">Payment Details</legend>
        <?php if($payment): ?>
            <dl>
                <dt>First Name</dt>
                <dd class="pl-4"><?= htmlspecialchars($payment['first_name']) ?></dd>
                <dt>Last Name</dt>
                <dd class="pl-4"><?= htmlspecialchars($payment['last_name']) ?></dd>
                <dt>Email</dt>
                <dd class="pl-4"><?= htmlspecialchars($payment['email']) ?></dd>
                <dt>Amount Paid</dt>
                <dd class="pl-4">
                    <?= isset($payment['amount']) ? '$'.number_format($payment['amount'], 2) : 'N/A' ?>
                </dd>
                <dt>Status</dt>
                <dd class="pl-4">
                    <span class="badge badge-success bg-gradient-success px-3 rounded-pill">Paid</span>
                </dd>
            </dl>
        <?php else: ?>
            <div class="text-warning font-italic">Pending Payment</div>
        <?php endif; ?>
    </fieldset>
</div>

<div class="text-right mt-3">
    <?php if(isset($status) && $status == 0): ?>
        <button class="btn btn-danger btn-flat bg-gradient-danger" type="button" id="cancel_booking">Cancel Book</button>
    <?php endif; ?>
    <button class="btn btn-secondary btn-flat bg-gradient-secondary" type="button" id="print_booking">
        <i class="fa fa-print"></i> Print
    </button>
</div>

<script>
$(function(){
    $('#cancel_booking').click(function(){
        _conf("Are you sure to cancel your facility booking [Ref. Code: <b><?= htmlspecialchars($ref_code ?? '') ?></b>]?", "cancel_booking", ["<?= $id ?? '' ?>"]);
    });

    $('#print_booking').click(function(){
        var content = document.getElementById('printable-content').innerHTML;
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Booking Details</title>');
        printWindow.document.write('<style>body{font-family: Arial, sans-serif; padding:20px;} dl dt {font-weight:bold; margin-top:10px;} dl dd {margin-left: 20px; margin-bottom: 10px;}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    });
});

function cancel_booking(id){
    start_loader();
    $.ajax({
        url: _base_url_ + "classes/Master.php?f=update_booking_status",
        method: "POST",
        data: {id: id, status: 3},
        dataType: "json",
        error: err => {
            console.log(err);
            alert_toast("An error occured.", 'error');
            end_loader();
        },
        success: function(resp){
            if(typeof resp == 'object' && resp.status == 'success'){
                location.reload();
            } else {
                alert_toast("An error occured.", 'error');
                end_loader();
            }
        }
    })
}
</script>
