<?php
require_once('./config.php');
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $qry = $conn->query("SELECT * from `booking_list` where id = '{$_GET['id']}' ");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
    }
}
?>
<div class="container-fluid">
    <form action="" id="booking-form">
        <input type="hidden" name="id" value="<?= isset($id) ? $id : '' ?>">
        <input type="hidden" name="facility_id" value="<?= isset($_GET['fid']) ? $_GET['fid'] : (isset($facility_id) ? $facility_id : "") ?>">

        <div class="form-group">
            <label for="date_from" class="control-label">From Date</label>
            <input 
                name="date_from" 
                id="date_from" 
                type="date" 
                class="form-control form-control-sm rounded-0" 
                min="<?= date('Y-m-d') ?>" 
                value="<?= isset($date_from) ? $date_from : '' ?>" 
                required 
            />
        </div>

        <div class="form-group">
            <label for="date_to" class="control-label">To Date</label>
            <input 
                name="date_to" 
                id="date_to" 
                type="date" 
                class="form-control form-control-sm rounded-0" 
                min="<?= date('Y-m-d') ?>" 
                value="<?= isset($date_to) ? $date_to : '' ?>" 
                required 
            />
        </div>

        <div class="form-group">
            <label for="time_slot" class="control-label">Time Slot</label>
            <select name="time_slot" id="time_slot" class="form-control form-control-sm rounded-0" required>
                <option value="" disabled <?= !isset($time_slot) ? 'selected' : '' ?>>Select time slot</option>
                <option value="08:00-10:00" <?= isset($time_slot) && $time_slot == '08:00-10:00' ? 'selected' : '' ?>>08:00 AM – 10:00 AM</option>
                <option value="10:00-12:00" <?= isset($time_slot) && $time_slot == '10:00-12:00' ? 'selected' : '' ?>>10:00 AM – 12:00 PM</option>
                <option value="13:00-15:00" <?= isset($time_slot) && $time_slot == '13:00-15:00' ? 'selected' : '' ?>>01:00 PM – 03:00 PM</option>
                <option value="15:00-17:00" <?= isset($time_slot) && $time_slot == '15:00-17:00' ? 'selected' : '' ?>>03:00 PM – 05:00 PM</option>
                <option value="17:00-19:00" <?= isset($time_slot) && $time_slot == '17:00-19:00' ? 'selected' : '' ?>>05:00 PM – 07:00 PM</option>
            </select>
        </div>
    </form>
</div>

<script>
$(document).ready(function(){
    $('#booking-form').submit(function(e){
        e.preventDefault();
        var _this = $(this);
        $('.err-msg').remove();

        const today = new Date().toISOString().split('T')[0];
        const dateFrom = $('#date_from').val();
        const dateTo = $('#date_to').val();
        const timeSlot = $('#time_slot').val();

        if (!timeSlot) {
            var el = $('<div>').addClass("alert alert-danger err-msg").text("Please select a time slot.");
            _this.prepend(el);
            el.show('slow');
            $("html, body, .modal").scrollTop(0);
            return false;
        }

        if (dateFrom < today || dateTo < today) {
            var el = $('<div>').addClass("alert alert-danger err-msg").text("You cannot select past dates.");
            _this.prepend(el);
            el.show('slow');
            $("html, body, .modal").scrollTop(0);
            return false;
        }

        if (dateTo < dateFrom) {
            var el = $('<div>').addClass("alert alert-danger err-msg").text("End date cannot be earlier than start date.");
            _this.prepend(el);
            el.show('slow');
            $("html, body, .modal").scrollTop(0);
            return false;
        }

        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=save_booking",
            data: new FormData(_this[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            dataType: 'json',
            error: err => {
                console.log(err);
                alert_toast("An error occurred", 'error');
                end_loader();
            },
            success: function(resp){
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.href = './?p=booking_list';
                } else if (resp.status == 'failed' && !!resp.msg) {
                    var el = $('<div>').addClass("alert alert-danger err-msg").text(resp.msg);
                    _this.prepend(el);
                    el.show('slow');
                    end_loader();
                } else {
                    alert_toast("An error occurred", 'error');
                    end_loader();
                    console.log(resp);
                }
                $("html, body, .modal").scrollTop(0);
            }
        });
    });
});
</script>
