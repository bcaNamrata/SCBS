<?php 
if($_settings->userdata('id') > 0 && $_settings->userdata('login_type') == 2){
    $qry = $conn->query("SELECT * FROM `client_list` WHERE id = '{$_settings->userdata('id')}'");
    if($qry->num_rows > 0){
        $res = $qry->fetch_assoc();
        foreach($res as $k => $v){
            $$k = $v;
        }
    } else {
        echo "<script>
                alert('You are not allowed to access this page. Unknown User ID.');
                location.replace('./');
              </script>";
        exit;
    }
} else {
    echo "<script>
            alert('You are not allowed to access this page.');
            location.replace('./');
          </script>";
    exit;
}
?>

<style>
    #cimg {
        width: 15vw;
        height: 20vh;
        object-fit: scale-down;
        object-position: center center;
    }
</style>

<div class="content py-5 mt-3">
    <div class="container">
        <div class="card card-outline card-dark shadow rounded-0">
            <div class="card-header">
                <h4 class="card-title"><b>Manage Account Details / Credentials</b></h4>
            </div>
            <div class="card-body">
                <div class="container-fluid">
                    <form id="register-frm" action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= isset($id) ? htmlspecialchars($id) : '' ?>">

                        <div class="row">
                            <div class="form-group col-md-6">
                                <input type="text" name="firstname" id="firstname" placeholder="Enter First Name" autofocus
                                    class="form-control form-control-sm form-control-border"
                                    value="<?= isset($firstname) ? htmlspecialchars($firstname) : '' ?>" required>
                                <small class="ml-3">First Name</small>
                            </div>
                            <div class="form-group col-md-6">
                                <input type="text" name="middlename" id="middlename" placeholder="Enter Middle Name (optional)"
                                    class="form-control form-control-sm form-control-border"
                                    value="<?= isset($middlename) ? htmlspecialchars($middlename) : '' ?>">
                                <small class="ml-3">Middle Name</small>
                            </div>
                            <div class="form-group col-md-6">
                                <input type="text" name="lastname" id="lastname" placeholder="Enter Last Name"
                                    class="form-control form-control-sm form-control-border" required
                                    value="<?= isset($lastname) ? htmlspecialchars($lastname) : '' ?>">
                                <small class="ml-3">Last Name</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <select name="gender" id="gender" class="custom-select custom-select-sm form-control-border" required>
                                    <option value="Male" <?= (isset($gender) && strtolower($gender) == 'male') ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= (isset($gender) && strtolower($gender) == 'female') ? 'selected' : '' ?>>Female</option>
                                </select>
                                <small class="ml-3">Gender</small>
                            </div>
                            <div class="form-group col-md-6">
                                <input type="text" name="contact" id="contact" placeholder="Enter Contact #"
                                    class="form-control form-control-sm form-control-border" required
                                    value="<?= isset($contact) ? htmlspecialchars($contact) : '' ?>">
                                <small class="ml-3">Contact #</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <small class="ml-3">Address</small>
                            <textarea name="address" id="address" rows="3" class="form-control form-control-sm rounded-0"
                                placeholder="Block 6 Lot 23, Here Subd., There City, Anywhere, 2306"><?= isset($address) ? htmlspecialchars($address) : '' ?></textarea>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <input type="email" name="email" id="email" placeholder="jsmith@sample.com"
                                    class="form-control form-control-sm form-control-border" required
                                    value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                                <small class="ml-3">Email</small>
                            </div>
                        </div>

                        <hr>

                        <!-- Change Password Checkbox -->
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="change_password_check" name="change_password_check">
                                <label class="custom-control-label" for="change_password_check">Change Password</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <div class="input-group">
                                    <input type="password" name="password" id="password"
                                        class="form-control form-control-sm form-control-border" placeholder="" disabled>
                                    <div class="input-group-append border-bottom border-top-0 border-left-0 border-right-0">
                                        <span class="input-append-text text-sm"><i
                                                class="fa fa-eye-slash text-muted pass_type" data-type="password"></i></span>
                                    </div>
                                </div>
                                <small class="ml-3">New Password</small>
                            </div>

                            <div class="form-group col-md-6">
                                <div class="input-group">
                                    <input type="password" id="cpassword"
                                        class="form-control form-control-sm form-control-border" placeholder="" disabled>
                                    <div class="input-group-append border-bottom border-top-0 border-left-0 border-right-0">
                                        <span class="input-append-text text-sm"><i
                                                class="fa fa-eye-slash text-muted pass_type" data-type="password"></i></span>
                                    </div>
                                </div>
                                <small class="ml-3">Confirm New Password</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <div class="input-group">
                                    <input type="password" name="oldpassword" id="oldpassword"
                                        class="form-control form-control-sm form-control-border" placeholder="" disabled>
                                    <div class="input-group-append border-bottom border-top-0 border-left-0 border-right-0">
                                        <span class="input-append-text text-sm"><i
                                                class="fa fa-eye-slash text-muted pass_type" data-type="password"></i></span>
                                    </div>
                                </div>
                                <small class="ml-3">Current Password</small>
                            </div>
                        </div>

                        <hr>

                        <!-- New Fields Added Here -->

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="player_level">Player Level</label>
                                <select name="player_level" id="player_level" class="custom-select custom-select-sm form-control-border" required>
                                    <option value="1" <?= (isset($player_level) && $player_level == 1) ? 'selected' : '' ?>>1 - Beginner</option>
                                    <option value="3" <?= (isset($player_level) && $player_level == 3) ? 'selected' : '' ?>>3 - Average</option>
                                    <option value="5" <?= (isset($player_level) && $player_level == 5) ? 'selected' : '' ?>>5 - Pro</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="occupation">Occupation</label>
                                <select name="occupation" id="occupation" class="custom-select custom-select-sm form-control-border" required>
                                    <option value="student" <?= (isset($occupation) && strtolower($occupation) == 'student') ? 'selected' : '' ?>>Student</option>
                                    <option value="employee" <?= (isset($occupation) && strtolower($occupation) == 'employee') ? 'selected' : '' ?>>Employee</option>
                                    <option value="unemployed" <?= (isset($occupation) && strtolower($occupation) == 'unemployed') ? 'selected' : '' ?>>Unemployed</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dateofbirth">Date of Birth</label>
                            <input type="date" name="dateofbirth" id="dateofbirth" class="form-control form-control-sm form-control-border"
                                value="<?= isset($dateofbirth) ? htmlspecialchars($dateofbirth) : '' ?>" required>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="avatar" class="control-label">Avatar</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input rounded-0 form-control form-control-sm form-control-border" id="customFile" name="img" onchange="displayImg(this, $(this))">
                                    <label class="custom-file-label" for="customFile">Choose file</label>
                                </div>
                            </div>
                            <div class="form-group col-md-6 d-flex justify-content-center">
                                <img src="<?= validate_image(isset($image_path) ? $image_path : '') ?>" alt="" id="cimg" class="img-fluid img-thumbnail bg-gradient-gray">
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-8"></div>
                            <div class="col-4">
                                <button type="submit" class="btn btn-primary btn-sm btn-flat btn-block">Update Details</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.displayImg = function(input, _this) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#cimg').attr('src', e.target.result);
                _this.siblings('.custom-file-label').html(input.files[0].name);
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            $('#cimg').attr('src', "<?= validate_image(isset($image_path) ? $image_path : '') ?>");
            _this.siblings('.custom-file-label').html("Choose file");
        }
    };

    $(function() {
        // Toggle password visibility
        $('.pass_type').click(function() {
            var type = $(this).attr('data-type');
            var input = $(this).closest('.input-group').find('input');
            if(type == 'password'){
                $(this).attr('data-type', 'text');
                input.attr('type', "text");
                $(this).removeClass("fa-eye-slash").addClass("fa-eye");
            } else {
                $(this).attr('data-type', 'password');
                input.attr('type', "password");
                $(this).removeClass("fa-eye").addClass("fa-eye-slash");
            }
        });

        // Enable/Disable password fields based on checkbox
        $('#change_password_check').change(function() {
            if ($(this).is(':checked')) {
                $('#password, #cpassword, #oldpassword').prop('disabled', false);
                $('#oldpassword').attr('required', true);
            } else {
                $('#password, #cpassword, #oldpassword').prop('disabled', true).val('');
                $('#oldpassword').removeAttr('required');
                $('.err-msg').remove(); // remove error messages if any
            }
        });

        $('#register-frm').submit(function(e) {
            e.preventDefault();
            var _this = $(this);
            $('.err-msg').remove();

            if($('#change_password_check').is(':checked')){
                if($('#password').val() != $('#cpassword').val()){
                    var el = $('<div>').addClass('alert alert-danger err-msg').text('Password does not match.');
                    _this.prepend(el);
                    el.show('slow');
                    return false;
                }
            }

            start_loader();

            $.ajax({
                url: _base_url_ + "classes/Users.php?f=save_client",
                data: new FormData(this),
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
                success: function(resp) {
                    if(typeof resp == 'object' && resp.status == 'success'){
                        location.reload();
                    } else if(resp.status == 'failed' && resp.msg) {
                        var el = $('<div>').addClass("alert alert-danger err-msg").text(resp.msg);
                        _this.prepend(el);
                        el.show('slow');
                    } else {
                        alert_toast("An error occurred", 'error');
                        console.log(resp);
                    }
                    end_loader();
                    $('html, body').scrollTop(0);
                }
            });
        });
    });
</script>
