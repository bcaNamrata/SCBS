<?php
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT f.*, c.name as category FROM `facility_list` f INNER JOIN category_list c ON f.category_id = c.id WHERE f.id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k = stripslashes($v);
        }
    }
}

// Check client data completeness
$incomplete_data = false;
$logged_in = false;
$client_id = $_settings->userdata('id');
$login_type = $_settings->userdata('login_type');

if ($client_id && $login_type == 2) {
    $logged_in = true;
    $client_qry = $conn->query("SELECT occupation, player_level, dateofbirth FROM client_list WHERE id = '{$client_id}' LIMIT 1");
    if ($client_qry && $client_qry->num_rows > 0) {
        $client_data = $client_qry->fetch_assoc();
        if (empty($client_data['occupation']) || empty($client_data['player_level']) || empty($client_data['dateofbirth'])) {
            $incomplete_data = true;
        }
    } else {
        $incomplete_data = true;
    }
} else {
    $incomplete_data = true;
}
?>
<style>
    .facility-img {
        width: 100%;
        object-fit: scale-down;
        object-position: center center;
    }
</style>

<section class="py-5">
    <div class="container pt-4">
        <div class="card rounded-0 card-outline card-primary shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img src="<?= validate_image(isset($image_path) ? $image_path : "") ?>" alt="facility Image <?= isset($name) ? htmlspecialchars($name) : "" ?>" class="img-thumbnail facility-img">
                    </div>
                </div>
                <fieldset>
                    <div class="row">
                        <div class="col-md-12">
                            <small class="mx-2 text-muted">Name</small>
                            <div class="pl-4"><?= isset($name) ? htmlspecialchars($name) : '' ?></div>
                        </div>
                        <div class="col-md-12">
                            <small class="mx-2 text-muted">Description</small>
                            <div class="pl-4"><?= isset($description) ? htmlspecialchars($description) : '' ?></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <small class="mx-2 text-muted">Price</small>
                            <div class="pl-4"><?= isset($price) ? number_format($price, 2) : '' ?></div>
                        </div>
                    </div>
                </fieldset>
                <center>
                    <button class="btn btn-large btn-primary rounded-pill w-25" id="book_now" type="button">Book Now</button>
                </center>
            </div>
        </div>
    </div>
</section>

<script>
    $(function() {
        $('#book_now').click(function() {
            const loggedIn = <?= json_encode($logged_in) ?>;
            const incompleteData = <?= json_encode($incomplete_data) ?>;

            if (!loggedIn) {
                // Not logged in, redirect to login page
                location.href = './login.php';
                return;
            }

            if (incompleteData) {
                alert("Your profile information is incomplete. Please update your occupation, player level, and date of birth.");
                window.location.href = "http://localhost:8080/scbs/?p=manage_account";
                return;
            }

            // All checks passed, open booking modal
            uni_modal("Book Facility", "booking.php?fid=<?= isset($id) ? $id : '' ?>", 'modal-sm');
        });
    });
</script>
