<?php
require_once('../config.php');
class Users extends DBConnection
{
	private $settings;
	public function __construct()
	{
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct()
	{
		parent::__destruct();
	}
	public function save_users()
	{
		if (!isset($_POST['status']) && $this->settings->userdata('login_type') == 1) {
			$_POST['status'] = 1;
		}
		extract($_POST);
		$oid = $id;
		$data = '';
		if (isset($oldpassword)) {
			if (md5($oldpassword) != $this->settings->userdata('password')) {
				return 4;
			}
		}
		$chk = $this->conn->query("SELECT * FROM `users` where username ='{$username}' " . ($id > 0 ? " and id!= '{$id}' " : ""))->num_rows;
		if ($chk > 0) {
			return 3;
			exit;
		}
		foreach ($_POST as $k => $v) {
			if (in_array($k, array('firstname', 'middlename', 'lastname', 'username', 'type'))) {
				if (!empty($data))
					$data .= " , ";
				$data .= " {$k} = '{$v}' ";
			}
		}
		if (!empty($password)) {
			$password = md5($password);
			if (!empty($data))
				$data .= " , ";
			$data .= " `password` = '{$password}' ";
		}

		if (empty($id)) {
			$qry = $this->conn->query("INSERT INTO users set {$data}");
			if ($qry) {
				$id = $this->conn->insert_id;
				$this->settings->set_flashdata('success', 'User Details successfully saved.');
				$resp['status'] = 1;
			} else {
				$resp['status'] = 2;
			}

		} else {
			$qry = $this->conn->query("UPDATE users set $data where id = {$id}");
			if ($qry) {
				$this->settings->set_flashdata('success', 'User Details successfully updated.');
				if ($id == $this->settings->userdata('id')) {
					foreach ($_POST as $k => $v) {
						if ($k != 'id') {
							if (!empty($data))
								$data .= " , ";
							$this->settings->set_userdata($k, $v);
						}
					}

				}
				$resp['status'] = 1;
			} else {
				$resp['status'] = 2;
			}

		}

		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = 'uploads/avatar-' . $id . '.png';
			$dir_path = base_app . $fname;
			$upload = $_FILES['img']['tmp_name'];
			$type = mime_content_type($upload);
			$allowed = array('image/png', 'image/jpeg');
			if (!in_array($type, $allowed)) {
				$resp['msg'] .= " But Image failed to upload due to invalid file type.";
			} else {
				$new_height = 200;
				$new_width = 200;

				list($width, $height) = getimagesize($upload);
				$t_image = imagecreatetruecolor($new_width, $new_height);
				imagealphablending($t_image, false);
				imagesavealpha($t_image, true);
				$gdImg = ($type == 'image/png') ? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
				imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				if ($gdImg) {
					if (is_file($dir_path))
						unlink($dir_path);
					$uploaded_img = imagepng($t_image, $dir_path);
					imagedestroy($gdImg);
					imagedestroy($t_image);
				} else {
					$resp['msg'] .= " But Image failed to upload due to unkown reason.";
				}
			}
			if (isset($uploaded_img)) {
				$this->conn->query("UPDATE users set `avatar` = CONCAT('{$fname}','?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$id}' ");
				if ($id == $this->settings->userdata('id')) {
					$this->settings->set_userdata('avatar', $fname);
				}
			}
		}
		if (isset($resp['msg']))
			$this->settings->set_flashdata('success', $resp['msg']);
		return $resp['status'];
	}
	public function delete_users()
	{
		extract($_POST);
		$avatar = $this->conn->query("SELECT avatar FROM users where id = '{$id}'")->fetch_array()['avatar'];
		$qry = $this->conn->query("DELETE FROM users where id = $id");
		if ($qry) {
			$this->settings->set_flashdata('success', 'User Details successfully deleted.');
			if (is_file(base_app . $avatar))
				unlink(base_app . $avatar);
			$resp['status'] = 'success';
		} else {
			$resp['status'] = 'failed';
		}
		return json_encode($resp);
	}

// 	ALTER TABLE client_list
// ADD COLUMN occupation VARCHAR(100) NULL,
// ADD COLUMN occupationLabel VARCHAR(100) NULL,
// ADD COLUMN day_of_visit DATE NULL,
// ADD COLUMN dateofbirth  INT NULL,
// ADD COLUMN age_group VARCHAR(50) NULL,
// ADD COLUMN player_level INT NULL,
// ADD COLUMN distance_from_futsal FLOAT NULL;

	// public function save_client(){
	// 	if(!empty($_POST['password']))
	// 	$_POST['password'] = md5($_POST['password']);
	// 	else
	// 	unset($_POST['password']);
	// 	if(isset($_POST['oldpassword'])){
	// 		if($this->settings->userdata('id') > 0 && $this->settings->userdata('login_type') == 2){
	// 			$get = $this->conn->query("SELECT * FROM `client_list` where id = '{$this->settings->userdata('id')}'");
	// 			$res = $get->fetch_array();
	// 			if($res['password'] != md5($_POST['oldpassword'])){
	// 				return  json_encode([
	// 					'status' =>'failed',
	// 					'msg'=>' Current Password is incorrect.'
	// 				]);
	// 			}
	// 		}
	// 		unset($_POST['oldpassword']);
	// 	}
	// 	extract($_POST);
	// 	$data = "";
	// 	foreach($_POST as $k => $v){
	// 		if(!in_array($k, array('id'))){
	// 			if(!empty($data)) $data .= ", ";
	// 			$data .= " `{$k}` = '{$v}' ";
	// 		}
	// 	}
	// 	$check = $this->conn->query("SELECT * FROM `client_list` where email = '{$email}' and delete_flag ='0' ".(is_numeric($id) && $id > 0 ? " and id != '{$id}'" : "")." ")->num_rows;
	// 	if($check > 0){
	// 		$resp['status'] = 'failed';
	// 		$resp['msg'] = ' Email already exists in the database.';
	// 	}else{
	// 		if(empty($id)){
	// 			$sql = "INSERT INTO `client_list` set $data";
	// 		}else{
	// 			$sql = "UPDATE `client_list` set $data where id = '{$id}'";
	// 		}
	// 		$save = $this->conn->query($sql);
	// 		if($save){
	// 			$resp['status'] = 'success';
	// 			$uid = empty($id) ? $this->conn->insert_id : $id;
	// 			if(empty($id)){
	// 				$resp['msg'] = " Account is successfully registered.";
	// 			}else if($this->settings->userdata('id') == $id && $this->settings->userdata('login_type') == 2){
	// 				$resp['msg'] = " Account Details has been updated successfully.";
	// 				foreach($_POST as $k => $v){
	// 					if(!in_array($k,['password'])){
	// 						$this->settings->set_userdata($k,$v);
	// 					}
	// 				}
	// 			}else{
	// 				$resp['msg'] = " Client's Account Details has been updated successfully.";
	// 			}
	// 			if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
	// 				if(!is_dir(base_app."uploads/clients/"))
	// 					mkdir(base_app."uploads/clients/");
	// 				$fname = 'uploads/clients/'.$uid.'.png';
	// 				$dir_path =base_app. $fname;
	// 				$upload = $_FILES['img']['tmp_name'];
	// 				$type = mime_content_type($upload);
	// 				$allowed = array('image/png','image/jpeg');
	// 				if(!in_array($type,$allowed)){
	// 					$resp['msg'].=" But Image failed to upload due to invalid file type.";
	// 				}else{
	// 					$new_height = 200; 
	// 					$new_width = 200; 

	// 					list($width, $height) = getimagesize($upload);
	// 					$t_image = imagecreatetruecolor($new_width, $new_height);
	// 					imagealphablending( $t_image, false );
	// 					imagesavealpha( $t_image, true );
	// 					$gdImg = ($type == 'image/png')? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
	// 					imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	// 					if($gdImg){
	// 							if(is_file($dir_path))
	// 							unlink($dir_path);
	// 							$uploaded_img = imagepng($t_image,$dir_path);
	// 							imagedestroy($gdImg);
	// 							imagedestroy($t_image);
	// 					}else{
	// 					$resp['msg'].=" But Image failed to upload due to unkown reason.";
	// 					}
	// 				}
	// 				if(isset($uploaded_img)){
	// 					$this->conn->query("UPDATE client_list set `image_path` = CONCAT('{$fname}','?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$uid}' ");
	// 					if($id == $this->settings->userdata('id') && $this->settings->userdata('login_type') == 2){
	// 							$this->settings->set_userdata('image_path',$fname);
	// 					}
	// 				}
	// 			}
	// 		}else{
	// 			$resp['status'] = 'failed';
	// 			if(empty($id)){
	// 				$resp['msg'] = " Account has failed to register for some reason.";
	// 			}else if($this->settings->userdata('id') == $id && $this->settings->userdata('login_type') == 2){
	// 				$resp['msg'] = " Account Details has failed to update.";
	// 			}else{
	// 				$resp['msg'] = " Client's Account Details has failed to update.";
	// 			}
	// 		}
	// 	}

	// 	if($resp['status'] == 'success')
	// 	$this->settings->set_flashdata('success',$resp['msg']);
	// 	return json_encode($resp);

	// } 

	public function save_client()
	{
		if (!empty($_POST['password'])) {
			$_POST['password'] = md5($_POST['password']);
		} else {
			unset($_POST['password']);
		}

		if (isset($_POST['oldpassword'])) {
			if ($this->settings->userdata('id') > 0 && $this->settings->userdata('login_type') == 2) {
				$get = $this->conn->query("SELECT * FROM `client_list` WHERE id = '{$this->settings->userdata('id')}'");
				$res = $get->fetch_array();
				if ($res['password'] != md5($_POST['oldpassword'])) {
					return json_encode([
						'status' => 'failed',
						'msg' => 'Current Password is incorrect.'
					]);
				}
			}
			unset($_POST['oldpassword']);
		}

		extract($_POST);

		// ✅ Calculate age_group from dateofbirth
		$age_group = '';
		if (!empty($dateofbirth)) {
			try {
				$dob_dt = new DateTime($dateofbirth);
				$now = new DateTime();
				$age = $now->diff($dob_dt)->y;

				if ($age < 18) {
					$age_group = 'Minor';
				} elseif ($age < 30) {
					$age_group = 'Young Adult';
				} elseif ($age < 60) {
					$age_group = 'Adult';
				} else {
					$age_group = 'Senior';
				}
			} catch (Exception $e) {
				$age_group = '';
			}
		}

		// ✅ Calculate day_of_visit using email
$day_of_visit = 0;

if (!empty($email)) {
    $email_escaped = $this->conn->real_escape_string($email);
    $qry = $this->conn->query("
        SELECT COUNT(*) AS total 
        FROM payment 
        WHERE email = '{$email_escaped}' AND status = 'done'
    ");

    if ($qry) {
        $res = $qry->fetch_assoc();
        $day_of_visit = isset($res['total']) ? (int)$res['total'] : 0;
    }
}


		// ✅ Calculate distance_from_futsal
		$distance_from_futsal = 0;
		if (!empty($address)) {
			$origin = urlencode('Lagankhel, Lalitpur, Nepal');
			$destination = urlencode($address);
			$apiKey = '5b3ce3597851110001cf6248457aa7fcfedc45bba0dace331e04e4df';

			$geocode_url = "https://api.openrouteservice.org/geocode/search?api_key={$apiKey}&text={$destination}";
			$geo_response = file_get_contents($geocode_url);
			$geo_data = json_decode($geo_response, true);

			if (!empty($geo_data['features'][0]['geometry']['coordinates'])) {
				$dest_coords = $geo_data['features'][0]['geometry']['coordinates'];
				$start_coords = [85.3085, 27.6654];

				$dir_url = "https://api.openrouteservice.org/v2/directions/driving-car?api_key={$apiKey}&start={$start_coords[0]},{$start_coords[1]}&end={$dest_coords[0]},{$dest_coords[1]}";
				$dir_response = file_get_contents($dir_url);
				$dir_data = json_decode($dir_response, true);

				if (!empty($dir_data['features'][0]['properties']['segments'][0]['distance'])) {
					$distance_km = $dir_data['features'][0]['properties']['segments'][0]['distance'] / 1000;
					$distance_from_futsal = ($distance_km > 20) ? 20 : round($distance_km, 2);
				}
			}
		}

		// ✅ Add calculated fields to $_POST
		$_POST['age_group'] = $age_group;
		$_POST['day_of_visit'] = $day_of_visit;
		$_POST['distance_from_futsal'] = $distance_from_futsal;

		// ✅ Build SQL SET string
		$data = "";
		foreach ($_POST as $k => $v) {
			if ($k == 'id')
				continue;
			$v = $this->conn->real_escape_string($v);
			if (!empty($data))
				$data .= ", ";
			$data .= "`{$k}` = '{$v}'";
		}

		// ✅ Email uniqueness check
		$check = $this->conn->query("SELECT * FROM `client_list` WHERE email = '{$this->conn->real_escape_string($email)}' AND delete_flag = 0 " . ((is_numeric($id) && $id > 0) ? " AND id != '{$id}'" : ""))->num_rows;

		if ($check > 0) {
			return json_encode([
				'status' => 'failed',
				'msg' => 'Email already exists.'
			]);
		}

		// ✅ Insert or Update
		if (empty($id)) {
			$sql = "INSERT INTO `client_list` SET $data";
		} else {
			$sql = "UPDATE `client_list` SET $data WHERE id = '{$id}'";
		}

		$save = $this->conn->query($sql);
		$resp = [];

		if ($save) {
			$uid = empty($id) ? $this->conn->insert_id : $id;
			$resp['status'] = 'success';
			$resp['msg'] = empty($id) ? "Account successfully registered." : "Account details updated.";

			if ($this->settings->userdata('id') == $uid && $this->settings->userdata('login_type') == 2) {
				foreach ($_POST as $k => $v) {
					if ($k != 'password') {
						$this->settings->set_userdata($k, $v);
					}
				}
			}

			// ✅ Avatar upload
			if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
				if (!is_dir(base_app . "uploads/clients/"))
					mkdir(base_app . "uploads/clients/");
				$fname = "uploads/clients/{$uid}.png";
				$path = base_app . $fname;
				$tmp = $_FILES['img']['tmp_name'];
				$type = mime_content_type($tmp);

				if (in_array($type, ['image/png', 'image/jpeg'])) {
					list($width, $height) = getimagesize($tmp);
					$t_image = imagecreatetruecolor(200, 200);
					imagealphablending($t_image, false);
					imagesavealpha($t_image, true);
					$src = ($type == 'image/png') ? imagecreatefrompng($tmp) : imagecreatefromjpeg($tmp);
					imagecopyresampled($t_image, $src, 0, 0, 0, 0, 200, 200, $width, $height);
					if (is_file($path))
						unlink($path);
					imagepng($t_image, $path);
					imagedestroy($src);
					imagedestroy($t_image);

					$this->conn->query("UPDATE `client_list` SET image_path = '{$fname}?v=" . time() . "' WHERE id = '{$uid}'");
					if ($this->settings->userdata('id') == $uid && $this->settings->userdata('login_type') == 2) {
						$this->settings->set_userdata('image_path', $fname);
					}
				}
			}
		} else {
			$resp['status'] = 'failed';
			$resp['msg'] = empty($id) ? "Registration failed." : "Update failed.";
		}

		if ($resp['status'] == 'success') {
			$this->settings->set_flashdata('success', $resp['msg']);
		}

		return json_encode($resp);
	}



	function delete_client()
	{
		extract($_POST);
		$del = $this->conn->query("UPDATE `client_list` set delete_flag = 1 where id='{$id}'");
		if ($del) {
			$resp['status'] = 'success';
			$resp['msg'] = ' Client Account has been deleted successfully.';
		} else {
			$resp['status'] = 'failed';
			$resp['msg'] = " Client Account has failed to delete";
		}
		if ($resp['status'] == 'success')
			$this->settings->set_flashdata('success', $resp['msg']);
		return json_encode($resp);
	}
public function save_prediction() {
    if (!isset($_POST['clientid']) || !isset($_POST['prediction'])) {
        return json_encode([
            'status' => 'failed',
            'msg' => 'Missing clientid or prediction.'
        ]);
    }

    $clientid = intval($_POST['clientid']);
    $prediction = intval($_POST['prediction']);

    // Validate inputs
    if ($clientid <= 0 || ($prediction !== 0 && $prediction !== 1)) {
        return json_encode([
            'status' => 'failed',
            'msg' => 'Invalid client ID or prediction value.'
        ]);
    }

    // Escape just in case
    $clientid = $this->conn->real_escape_string($clientid);
    $prediction = $this->conn->real_escape_string($prediction);

    // Check if prediction already exists
    $check = $this->conn->query("SELECT id FROM predict WHERE clientid = '{$clientid}'");
    if ($check && $check->num_rows > 0) {
        // Update existing
        $sql = "UPDATE predict SET prediction = '{$prediction}' WHERE clientid = '{$clientid}'";
    } else {
        // Insert new
        $sql = "INSERT INTO predict (clientid, prediction) VALUES ('{$clientid}', '{$prediction}')";
    }

    $result = $this->conn->query($sql);

    if ($result) {
        return json_encode([
            'status' => 'success',
            'msg' => 'Prediction saved successfully.'
        ]);
    } else {
        return json_encode([
            'status' => 'failed',
            'msg' => 'Database query failed.'
        ]);
    }
}



}

$users = new users();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
switch ($action) {
	case 'save':
		echo $users->save_users();
		break;
	case 'delete':
		echo $users->delete_users();
		break;
	case 'save_client':
		echo $users->save_client();
		break;
	case 'delete_client':
		echo $users->delete_client();
		break;
	case 'save_prediction':
		echo $users->save_prediction();
		break;
	default:
		// echo $sysset->index();
		break;
}