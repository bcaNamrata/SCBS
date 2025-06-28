<!-- CREATE TABLE predict (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    prediction TEXT NOT NULL,
    FOREIGN KEY (client_id) REFERENCES client_list(id) ON DELETE CASCADE
); -->

<div class="card card-outline card-primary shadow rounded-0">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><b>Prediction List</b></h3>
    <button id="predictAllBtn" class="btn btn-success btn-sm">Predict All</button>
  </div>
  <div class="card-body">
    <table class="table table-striped table-bordered" id="predictionTable">
      <colgroup>
        <col width="5%"><col width="15%"><col width="10%"><col width="15%">
        <col width="20%"><col width="10%"><col width="10%"><col width="10%">
        <col width="10%"><col width="10%"><col width="10%">
      </colgroup>
      <thead>
        <tr class="bg-gradient-dark text-light text-center">
          <th>SN</th><th>Name</th><th>Gender</th><th>Email</th><th>Address</th>
          <th>Occupation</th><th>Day of Visit</th><th>Age Group</th>
          <th>Player Level</th><th>Distance from Futsal</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $predictions = [];
        $rs = $conn->query("SELECT clientid, prediction FROM predict");
        while ($p = $rs->fetch_assoc()) {
          $predictions[$p['clientid']] = intval($p['prediction']);
        }

        $i = 1;
        $clients = $conn->query("SELECT * FROM client_list WHERE delete_flag=0 AND status=1 ORDER BY firstname ASC");
        while ($c = $clients->fetch_assoc()):
          $cid = intval($c['id']);
          $name = htmlspecialchars(trim($c['firstname'].' '.$c['middlename'].' '.$c['lastname']));
          $distance = floatval($c['distance_from_futsal']);
          $plevel = intval($c['player_level']);
          $visit = intval($c['day_of_visit']);
          $occupation = strtolower(htmlspecialchars($c['occupationLabel'] ?? $c['occupation'] ?? ''));
          $gender = strtolower(htmlspecialchars($c['gender'] ?? ''));
          $agegrp = $c['age_group'] !== null ? intval($c['age_group']) : '';
          $pred = $predictions[$cid] ?? null;
        ?>
        <tr>
          <td class="text-center"><?= $i++ ?></td>
          <td><?= $name ?></td>
          <td class="text-center"><?= htmlspecialchars($c['gender']) ?></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td><?= htmlspecialchars($c['address']) ?></td>
          <td><?= htmlspecialchars($c['occupationLabel'] ?? $c['occupation']) ?></td>
          <td class="text-center"><?= $visit ?></td>
          <td class="text-center"><?= $agegrp ?></td>
          <td class="text-center"><?= $plevel ?></td>
          <td class="text-center"><?= $distance ?></td>
          <td class="text-center" data-clientid="<?= $cid ?>">
            <?php if ($pred === null): ?>
              <button class="btn btn-primary btn-sm predict-btn"
                data-distance="<?= $distance ?>"
                data-player_level="<?= $plevel ?>"
                data-day_of_visit="<?= $visit ?>"
                data-occupation="<?= $occupation ?>"
                data-gender="<?= $gender ?>"
                data-age_group="<?= $agegrp ?>"
                data-clientid="<?= $cid ?>">
                Predict
              </button>
            <?php else: ?>
              <span class="badge <?= $pred ? 'bg-success' : 'bg-secondary' ?>">Predicted: <?= $pred ?></span>
              <button class="btn btn-outline-primary btn-sm predict-again-btn"
                data-distance="<?= $distance ?>"
                data-player_level="<?= $plevel ?>"
                data-day_of_visit="<?= $visit ?>"
                data-occupation="<?= $occupation ?>"
                data-gender="<?= $gender ?>"
                data-age_group="<?= $agegrp ?>"
                data-clientid="<?= $cid ?>">
                Predict Again
              </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <div id="predictionResult" class="mt-3"></div>
  </div>
</div>

<script>
$(function(){
  $('#predictionTable').DataTable();

  function updatePrediction(clientid, pred){
    let fd = new FormData();
    fd.append('clientid', clientid);
    fd.append('prediction', pred);
    return $.ajax({
      url: _base_url_ + "classes/Users.php?f=save_prediction",
      data: fd, method: 'POST',
      cache:false, contentType:false, processData:false,
      dataType:'json'
    });
  }

  function predict(btn){
    let d = parseFloat(btn.data('distance')),
        pl = parseInt(btn.data('player_level')),
        dv = parseInt(btn.data('day_of_visit')),
        og = btn.data('occupation'),
        gd = btn.data('gender'),
        ag = btn.data('age_group') === '' ? null : parseInt(btn.data('age_group')),
        cid = parseInt(btn.data('clientid')),
        logd = d > 0 ? Math.log(d) : 0,
        inter = pl * d,
        weekend = (dv===6||dv===7)?1:0,
        sinus = Math.sin((2*Math.PI*dv)/7),
        payload = {
          log_distance: parseFloat(logd.toFixed(3)),
          interaction_feature: parseFloat(inter.toFixed(3)),
          day_of_visit: dv,
          is_weekend: weekend,
          day_sin: parseFloat(sinus.toFixed(3)),
          player_level: pl,
          occupation: og,
          gender: gd,
          age_group: ag
        };

    btn.prop('disabled',true).text('Predicting...');
    return $.ajax({
      url: 'http://localhost:8090/predict_booking',
      method:'POST',
      contentType:'application/json',
      data: JSON.stringify(payload),
      success: function(res){
        let pred = parseInt(res.prediction);
        updatePrediction(cid, pred).then(() => {
          let badge = `<span class="badge ${pred ? 'bg-success' : 'bg-secondary'}">Predicted: ${pred}</span>`;
          btn.closest('td').html(badge + `
            <button class="btn btn-outline-primary btn-sm predict-again-btn"
              data-distance="${d}"
              data-player_level="${pl}"
              data-day_of_visit="${dv}"
              data-occupation="${og}"
              data-gender="${gd}"
              data-age_group="${ag}"
              data-clientid="${cid}">Predict Again</button>
          `);
        });
      },
      error: () => {
        alert("Prediction failed");
        btn.prop('disabled',false).text('Predict');
      }
    });
  }

  $(document).on('click','.predict-btn, .predict-again-btn', function(){
    predict($(this));
  });

  $('#predictAllBtn').click(async function(){
    let b = $(this);
    b.prop('disabled',true).text('Predicting All...');
    const buttons = $('.predict-btn').toArray();
    for (const el of buttons) {
      await predict($(el));
    }
    b.prop('disabled',false).text('Predict All');
  });
});
</script>
