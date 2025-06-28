<?php
// Assumes $conn exists and $_GET['id'] is passed
if (!isset($_GET['id']) || intval($_GET['id'])<=0) {
  echo "Invalid client ID."; exit;
}
$id = intval($_GET['id']);
$q = $conn->query("SELECT * FROM client_list WHERE id='{$id}' AND delete_flag=0 AND status=1");
if (!$q->num_rows) {
  echo "Client not found."; exit;
}
$c = $q->fetch_assoc();
$title = htmlspecialchars(trim($c['firstname'].' '.$c['middlename'].' '.$c['lastname']));
?>
<style>#uni_modal .modal-footer{display:none;}</style>
<div class="container-fluid">
  <fieldset class="border-bottom">
    <legend class="h5 text-muted">Client Details</legend>
    <dl>
      <dt>Name</dt><dd class="pl-4"><?= $title ?></dd>
      <dt>Gender</dt><dd class="pl-4"><?= htmlspecialchars($c['gender']) ?></dd>
      <dt>Email</dt><dd class="pl-4"><?= htmlspecialchars($c['email']) ?></dd>
      <dt>Address</dt><dd class="pl-4"><?= htmlspecialchars($c['address']) ?></dd>
      <dt>Occupation</dt><dd class="pl-4"><?= htmlspecialchars($c['occupationLabel']) ?></dd>
      <dt>Day of Visit</dt><dd class="pl-4"><?= htmlspecialchars($c['day_of_visit']) ?></dd>
      <dt>Age Group</dt><dd class="pl-4"><?= htmlspecialchars($c['age_group']) ?></dd>
      <dt>Player Level</dt><dd class="pl-4"><?= htmlspecialchars($c['player_level']) ?></dd>
      <dt>Distance from Futsal</dt><dd class="pl-4"><?= htmlspecialchars($c['distance_from_futsal']) ?></dd>
    </dl>
  </fieldset>
  <div class="text-right">
    <button class="btn btn-dark btn-flat bg-gradient-dark" type="button" data-dismiss="modal">
      <i class="fa fa-times"></i> Close
    </button>
  </div>
</div>
