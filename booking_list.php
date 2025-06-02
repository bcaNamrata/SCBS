<div class="content py-5 mt-5">
    <div class="container">
        <div class="card card-outline card-primary shadow rounded-0">
            <div class="card-header">
                <h4 class="card-title">My Booking List</h4>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <colgroup>
                        <col width="5%">
                        <col width="12%">
                        <col width="12%">
                        <col width="22%">
                        <col width="12%">
                        <col width="12%">
                        <col width="10%">
                        <col width="15%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Date Booked</th>
                            <th class="text-center">Ref Code</th>
                            <th class="text-center">Facility</th>
                            <th class="text-center">Schedule</th>
                            <th class="text-center">Time Slot</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        $qry = $conn->query("
                            SELECT 
                                b.*, 
                                f.name as facility, 
                                c.name as category,
                                ts.slot as time_slot
                            FROM `booking_list` b 
                            INNER JOIN facility_list f ON b.facility_id = f.id 
                            INNER JOIN category_list c ON f.category_id = c.id 
                            LEFT JOIN time_slot ts ON ts.booking_id = b.id
                            WHERE b.client_id = '{$_settings->userdata('id')}' 
                            ORDER BY unix_timestamp(b.date_created) DESC
                        ");
                        while ($row = $qry->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="text-center"><?= $i++; ?></td>
                                <td><?= date("Y-m-d H:i", strtotime($row['date_created'])) ?></td>
                                <td><?= htmlspecialchars($row['ref_code']) ?></td>
                                <td>
                                    <p class="m-0 truncate-1"><?= htmlspecialchars($row['facility']) ?></p>
                                    <p class="m-0 truncate-1"><?= htmlspecialchars($row['category']) ?></p>
                                </td>
                                <td>
                                    <?php
                                    if ($row['date_from'] == $row['date_to']) {
                                        echo date("M d, Y", strtotime($row['date_from']));
                                    } else {
                                        echo date("M d, Y", strtotime($row['date_from'])) . " - " . date("M d, Y", strtotime($row['date_to']));
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?= !empty($row['time_slot']) ? htmlspecialchars($row['time_slot']) : '<em>Not set</em>' ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    switch ($row['status']) {
                                        case 0:
                                            echo "<span class='badge badge-secondary bg-gradient-secondary px-3 rounded-pill'>Pending</span>";
                                            break;
                                        case 1:
                                            echo "<span class='badge badge-primary bg-gradient-primary px-3 rounded-pill'>Confirmed</span>";
                                            break;
                                        case 2:
                                            echo "<span class='badge badge-warning bg-gradient-success px-3 rounded-pill'>Done</span>";
                                            break;
                                        case 3:
                                            echo "<span class='badge badge-danger bg-gradient-danger px-3 rounded-pill'>Cancelled</span>";
                                            break;
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-flat btn-light border btn-sm view_data"
                                        data-id="<?= $row['id'] ?>">View</button>
                                    <?php if ($row['status'] == 1): ?>
                                        <button type="button" class="btn btn-flat btn-success btn-sm pay_btn"
                                            data-id="<?= $row['id'] ?>">Pay</button>
                                    <?php elseif ($row['status'] == 2): ?>
                                        <span class="badge badge-success bg-gradient-success px-3 rounded-pill">Paid</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('table th, table td').addClass('px-2 py-1 align-middle');
        $('table').dataTable();

        $('.view_data').click(function () {
            uni_modal("Booking Details", "view_booking.php?id=" + $(this).attr('data-id'));
        });

        $('.pay_btn').click(function () {
            var bookingId = $(this).attr('data-id');
            // uni_modal("Make Payment", "payment_stripe.php?booking_id=" + bookingId);
            my_modal("Make Payment", "payment_stripe.php?booking_id=" + bookingId);
        });
    });
</script>