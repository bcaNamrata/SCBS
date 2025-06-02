<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	function capture_err(){
		if(!$this->conn->error)
			return false;
		else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function save_category(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id','description'))){
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(isset($_POST['description'])){
			if(!empty($data)) $data .=",";
				$data .= " `description`='".addslashes(htmlentities($description))."' ";
		}
		$check = $this->conn->query("SELECT * FROM `category_list` where `name` = '{$name}' and delete_flag = 0 ".(!empty($id) ? " and id != {$id} " : "")." ")->num_rows;
		if($this->capture_err())
			return $this->capture_err();
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = " Category already exist.";
			return json_encode($resp);
			exit;
		}
		if(empty($id)){
			$sql = "INSERT INTO `category_list` set {$data} ";
			$save = $this->conn->query($sql);
		}else{
			$sql = "UPDATE `category_list` set {$data} where id = '{$id}' ";
			$save = $this->conn->query($sql);
		}
		if($save){
			$resp['status'] = 'success';
			if(empty($id))
				$this->settings->set_flashdata('success'," New Category successfully saved.");
			else
				$this->settings->set_flashdata('success'," Category successfully updated.");
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		return json_encode($resp);
	}
	function delete_category(){
		extract($_POST);
		$del = $this->conn->query("UPDATE `category_list` set delete_flag = 1 where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success'," Category successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_facility(){
		$_POST['description'] = html_entity_decode($_POST['description']);
		if(empty($_POST['id'])){
			$prefix = date('Ym-');
			$code = sprintf("%'.05d",1);
			while(true){
				$check = $this->conn->query("SELECT * FROM `facility_list` where facility_code = '{$prefix}{$code}'")->num_rows;
				if($check > 0){
					$code = sprintf("%'.05d",ceil($code) + 1);
				}else{
					break;
				}
			}
			$_POST['facility_code'] = $prefix.$code;
		}

		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				$v = $this->conn->real_escape_string($v);
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(isset($reg_no)){
			$check = $this->conn->query("SELECT * FROM `facility_list` where `name` = '{$name}' ".(!empty($id) ? " and id != {$id} " : "")." ")->num_rows;
			if($this->capture_err())
				return $this->capture_err();
			if($check > 0){
				$resp['status'] = 'failed';
				$resp['msg'] = " Facility already exist.";
				return json_encode($resp);
				exit;
			}
		}
		
		if(empty($id)){
			$sql = "INSERT INTO `facility_list` set {$data} ";
			$save = $this->conn->query($sql);
		}else{
			$sql = "UPDATE `facility_list` set {$data} where id = '{$id}' ";
			$save = $this->conn->query($sql);
		}
		if($save){
			$resp['status'] = 'success';
			$cid = empty($id) ? $this->conn->insert_id : $id;
			$resp['id'] = $cid ;
			if(empty($id))
				$resp['msg'] = " New facility successfully saved.";
			else
				$resp['msg'] = " Facility successfully updated.";
				if($this->settings->userdata('id')  == $cid && $this->settings->userdata('login_type') == 3){
					foreach($_POST as $k => $v){
						if(!in_array($k,['password']))
						$this->settings->set_userdata($k,$v);
					}
					$resp['msg'] = " Account successfully updated.";
				}
				if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
					if(!is_dir(base_app."uploads/facility/"))
						mkdir(base_app."uploads/facility/");
					$fname = 'uploads/facility/'.$cid.'.png';
					$dir_path =base_app. $fname;
					$upload = $_FILES['img']['tmp_name'];
					$type = mime_content_type($upload);
					$allowed = array('image/png','image/jpeg');
					if(!in_array($type,$allowed)){
						$resp['msg'].=" But Image failed to upload due to invalid file type.";
					}else{
						 
						list($width, $height) = getimagesize($upload);
						$t_image = imagecreatetruecolor($width, $height);
						imagealphablending( $t_image, false );
						imagesavealpha( $t_image, true );
						$gdImg = ($type == 'image/png')? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
						imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $width, $height, $width, $height);
						if($gdImg){
								if(is_file($dir_path))
								unlink($dir_path);
								$uploaded_img = imagepng($t_image,$dir_path);
								imagedestroy($gdImg);
								imagedestroy($t_image);
						}else{
						$resp['msg'].=" But Image failed to upload due to unkown reason.";
						}
					}
					if(isset($uploaded_img)){
						$this->conn->query("UPDATE facility_list set `image_path` = CONCAT('{$fname}','?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$cid}' ");
					}
				}
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		if(isset($resp['msg']) && $resp['status'] == 'success'){
			$this->settings->set_flashdata('success',$resp['msg']);
		}
		return json_encode($resp);
	}
	function delete_facility(){
		extract($_POST);
		$del = $this->conn->query("UPDATE `facility_list` set `delete_flag` = 1  where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success'," Facility successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	// function save_booking(){
	// 	if(empty($_POST['id'])){
	// 		$prefix = date('Ym-');
	// 		$code = sprintf("%'.05d",1);
	// 		while(true){
	// 			$check = $this->conn->query("SELECT * FROM `booking_list` where ref_code = '{$prefix}{$code}'")->num_rows;
	// 			if($check > 0){
	// 				$code = sprintf("%'.05d",ceil($code) + 1);
	// 			}else{
	// 				break;
	// 			}
	// 		}
	// 		$_POST['client_id'] = $this->settings->userdata('id');
	// 		$_POST['ref_code'] = $prefix.$code;
	// 	}
	// 	extract($_POST);
	// 	$data = "";
	// 	foreach($_POST as $k =>$v){
	// 		if(!in_array($k,array('id'))){
	// 			if(!empty($data)) $data .=",";
	// 			$data .= " `{$k}`='{$v}' ";
	// 		}
	// 	}
		
	// 	$check = $this->conn->query("SELECT * FROM `booking_list` where  facility_id = '{$facility_id}' and ('{$date_from}' BETWEEN date(date_from) and date(date_to) or '{$date_to}' BETWEEN date(date_from) and date(date_to)) and status = 1 ")->num_rows;
	// 	if($check > 0){
	// 		$resp['status'] = 'failed';
	// 		$resp['msg'] = 'Facility is not available on the selected dates.';
	// 		return json_encode($resp);
	// 		exit;
	// 	}

	// 	if(empty($id)){
	// 		$sql = "INSERT INTO `booking_list` set {$data} ";
	// 		$save = $this->conn->query($sql);
	// 	}else{
	// 		$sql = "UPDATE `booking_list` set {$data} where id = '{$id}' ";
	// 		$save = $this->conn->query($sql);
	// 	}
	// 	if($save){
	// 		$resp['status'] = 'success';
	// 		if(empty($id))
	// 			$this->settings->set_flashdata('success'," Facility has been booked successfully.");
	// 		else
	// 			$this->settings->set_flashdata('success'," Booking successfully updated.");
	// 	}else{
	// 		$resp['status'] = 'failed';
	// 		$resp['err'] = $this->conn->error."[{$sql}]";
	// 	}
	// 	return json_encode($resp);
	// }

	
// 	CREATE TABLE `time_slot` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `booking_id` INT NOT NULL, `slot` VARCHAR(20) NOT NULL, FOREIGN KEY (`booking_id`) REFERENCES `booking_list`(`id`) ON DELETE CASCADE );
// 	CREATE TABLE `payment` (
//   `id` INT AUTO_INCREMENT PRIMARY KEY,
//   `booking_id` INT NOT NULL,
//   `payment` VARCHAR(255) NOT NULL,
//   `status` ENUM('pending', 'done') NOT NULL DEFAULT 'pending',
//   FOREIGN KEY (`booking_id`) REFERENCES `booking_list`(`id`) ON DELETE CASCADE
// );

// CREATE TABLE `amount` (
//     `id` INT AUTO_INCREMENT PRIMARY KEY,
//     `facility_id` INT NOT NULL,
//     `price` DECIMAL(10, 2) DEFAULT NULL,
//     FOREIGN KEY (`facility_id`) REFERENCES `facility_list`(`id`) ON DELETE CASCADE
// );

// INSERT INTO `amount` (`facility_id`)
// SELECT `id` FROM `facility_list`
// WHERE `id` NOT IN (SELECT `facility_id` FROM `amount`);

// SELECT a.id, f.name as facility_name, a.price
// FROM amount a
// JOIN facility_list f ON a.facility_id = f.id;


	function save_booking(): bool|string{
    if(empty($_POST['id'])){
        $prefix = date('Ym-');
        $code = sprintf("%'.05d",1);
        while(true){
            $check = $this->conn->query("SELECT * FROM `booking_list` where ref_code = '{$prefix}{$code}'")->num_rows;
            if($check > 0){
                $code = sprintf("%'.05d",ceil($code) + 1);
            }else{
                break;
            }
        }
        $_POST['client_id'] = $this->settings->userdata('id');
        $_POST['ref_code'] = $prefix.$code;
    }
    extract($_POST);

    // Prepare data string for booking_list (exclude id and time_slot)
    $data = "";
    foreach($_POST as $k => $v){
        if(!in_array($k, array('id', 'time_slot'))){
            if(!empty($data)) $data .= ",";
            $v = $this->conn->real_escape_string($v);
            $data .= " `{$k}`='{$v}' ";
        }
    }

    // Parse time slot start and end from the string, e.g. "08:00-10:00"
    if(!isset($_POST['time_slot']) || empty($_POST['time_slot'])){
        $resp['status'] = 'failed';
        $resp['msg'] = 'Time slot is required.';
        return json_encode($resp);
        exit;
    }
    $time_slot = $this->conn->real_escape_string($_POST['time_slot']);
    list($slot_start, $slot_end) = explode('-', $time_slot);

    // Convert to time format for comparisons
    $slot_start = date("H:i:s", strtotime($slot_start));
    $slot_end = date("H:i:s", strtotime($slot_end));

    // Check if facility is available during selected dates and time slot (excluding canceled status)
    // We need to check for overlapping date ranges AND overlapping time slots

    // Query to check overlapping bookings with time slot conflicts
    $check_sql = "
        SELECT ts.*
        FROM booking_list b
        JOIN time_slot ts ON b.id = ts.booking_id
        WHERE b.facility_id = '{$facility_id}'
          AND b.status != 3
          AND (
            (DATE(b.date_from) <= '{$date_to}' AND DATE(b.date_to) >= '{$date_from}')
          )
          AND (
            -- parse the slot times in DB and check for overlap with requested time slot
            STR_TO_DATE(SUBSTRING_INDEX(ts.slot, '-', 1), '%H:%i') < '{$slot_end}'
            AND STR_TO_DATE(SUBSTRING_INDEX(ts.slot, '-', -1), '%H:%i') > '{$slot_start}'
          )
    ";

    // Exclude self if updating existing booking
    if(!empty($id)){
        $check_sql .= " AND b.id != '{$id}' ";
    }

    $check = $this->conn->query($check_sql)->num_rows;

    if($check > 0){
        $resp['status'] = 'failed';
        $resp['msg'] = 'The selected time slot is already occupied on the selected dates.';
        return json_encode($resp);
        exit;
    }

    // Insert or update booking_list record
    if(empty($id)){
        $sql = "INSERT INTO `booking_list` SET {$data}";
        $save = $this->conn->query($sql);
        if($save){
            $id = $this->conn->insert_id; // get new booking id
        }
    } else {
        $sql = "UPDATE `booking_list` SET {$data} WHERE id = '{$id}'";
        $save = $this->conn->query($sql);
    }

    if($save){
        // Handle time_slot table
        // Delete old time slots for this booking first
        $this->conn->query("DELETE FROM `time_slot` WHERE booking_id = '{$id}'");

        // Insert new time slot (assumes single time_slot string)
        if(isset($_POST['time_slot']) && !empty($_POST['time_slot'])){
            $this->conn->query("INSERT INTO `time_slot` (booking_id, slot) VALUES ('{$id}', '{$time_slot}')");
        }

        $resp['status'] = 'success';
        if(empty($_POST['id']))
            $this->settings->set_flashdata('success', "Facility has been booked successfully.");
        else
            $this->settings->set_flashdata('success', "Booking successfully updated.");
    } else {
        $resp['status'] = 'failed';
        $resp['err'] = $this->conn->error . "[{$sql}]";
    }
    return json_encode($resp);
}

// ALTER TABLE payment 
// ADD COLUMN transaction_id VARCHAR(255) DEFAULT NULL,
// ADD COLUMN first_name VARCHAR(100) DEFAULT NULL,
// ADD COLUMN last_name VARCHAR(100) DEFAULT NULL,
// ADD COLUMN email VARCHAR(150) DEFAULT NULL;
function update_payment_status(): bool|string {
    global $conn;

    if (empty($_POST['booking_id'])) {
        $resp = ['status' => 'failed', 'error' => 'Booking ID is required'];
        return json_encode($resp);
    }

    $booking_id = intval($_POST['booking_id']);
    $payment_status = $_POST['payment_status'] ?? 'pending';
    $first_name = $conn->real_escape_string($_POST['first_name'] ?? '');
    $last_name = $conn->real_escape_string($_POST['last_name'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');

    // Insert payment record
    $sql = "INSERT INTO payment (booking_id, status, first_name, last_name, email) 
            VALUES ('{$booking_id}', '{$payment_status}', '{$first_name}', '{$last_name}', '{$email}')";

    $insert = $conn->query($sql);

    if ($insert) {
        // If insert successful, update booking status to paid (2)
        $status_paid = 2;
        $stmt2 = $conn->prepare("UPDATE booking_list SET status = ? WHERE id = ?");
        $stmt2->bind_param("ii", $status_paid, $booking_id);
        $booking_updated = $stmt2->execute();
        $stmt2->close();

        if ($booking_updated) {
            $resp = ['status' => 'success'];
        } else {
            $resp = ['status' => 'failed', 'error' => 'Failed to update booking status'];
        }
    } else {
        $resp = ['status' => 'failed', 'error' => $conn->error];
    }

    return json_encode($resp);
}




// function update_payment_status() {
//     if ($_GET['f'] == 'update_payment_status') {
//         // Get raw JSON input
//         $input = json_decode(file_get_contents("php://input"), true);

//         if (!$input) {
//             echo json_encode(['status' => 'failed', 'error' => 'Invalid or missing JSON']);
//             exit;
//         }

//         $booking_id = intval($input['booking_id'] ?? 0);
//         $payment_status = $input['payment_status'] ?? 'pending';
//         $first_name = $input['first_name'] ?? '';
//         $last_name = $input['last_name'] ?? '';
//         $email = $input['email'] ?? '';
//         $transaction_id = $input['transaction_id'] ?? '';

//         if ($booking_id > 0) {
//             $conn = $GLOBALS['conn'];

//             // Check if booking_id exists in the payment table
//             $check = $conn->prepare("SELECT id FROM payment WHERE booking_id = ?");
//             $check->bind_param("i", $booking_id);
//             $check->execute();
//             $result = $check->get_result();
//             $check->close();

//             if ($result->num_rows === 0) {
//                 echo json_encode(['status' => 'failed', 'error' => 'Booking ID not found in payment table']);
//                 exit;
//             }

//             // Update payment table
//             $stmt = $conn->prepare("UPDATE payment 
//                 SET status = ?, first_name = ?, last_name = ?, email = ?, transaction_id = ? 
//                 WHERE booking_id = ?");
//             $stmt->bind_param("sssssi", $payment_status, $first_name, $last_name, $email, $transaction_id, $booking_id);
//             $payment_updated = $stmt->execute();
//             $stmt->close();

//             // Update booking_list status to 4 (paid)
//             $status_paid = 3;
//             $stmt2 = $conn->prepare("UPDATE booking_list SET status = ? WHERE id = ?");
//             $stmt2->bind_param("ii", $status_paid, $booking_id);
//             $booking_updated = $stmt2->execute();
//             $stmt2->close();

//             if ($payment_updated && $booking_updated) {
//                 echo json_encode(['status' => 'success']);
//             } else {
//                 echo json_encode(['status' => 'failed', 'error' => 'Database update failed']);
//             }
//         } else {
//             echo json_encode(['status' => 'failed', 'error' => 'Invalid booking ID']);
//         }

//         exit;
//     }
// }





	function delete_booking(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `booking_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success'," Booking successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function update_booking_status(){
		extract($_POST);
		$update = $this->conn->query("UPDATE `booking_list` set `status` = '{$status}' where id = '{$id}' ");
		if($update){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success'," Booking status successfully updated.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'save_category':
		echo $Master->save_category();
	break;
	case 'delete_category':
		echo $Master->delete_category();
	break;
	case 'save_facility':
		echo $Master->save_facility();
	break;
	case 'delete_facility':
		echo $Master->delete_facility();
	break;
	case 'save_booking':
		echo $Master->save_booking();
	break;
	case 'delete_booking':
		echo $Master->delete_booking();
	break;
	case 'update_booking_status':
		echo $Master->update_booking_status();
	break;
	case 'update_payment_status':
    	echo $Master->update_payment_status();
    break;
	default:
		// echo $sysset->index();
		break;
}