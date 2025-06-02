<script>
  $(document).ready(function () {
    $('#p_use').click(function () {
      uni_modal("Privacy Policy", "policy.php", "mid-large")
    })
    window.viewer_modal = function ($src = '') {
      start_loader()
      var t = $src.split('.')
      t = t[1]
      if (t == 'mp4') {
        var view = $("<video src='" + $src + "' controls autoplay></video>")
      } else {
        var view = $("<img src='" + $src + "' />")
      }
      $('#viewer_modal .modal-content video,#viewer_modal .modal-content img').remove()
      $('#viewer_modal .modal-content').append(view)
      $('#viewer_modal').modal({
        show: true,
        backdrop: 'static',
        keyboard: false,
        focus: true
      })
      end_loader()

    }
    window.uni_modal = function ($title = '', $url = '', $size = "") {
      start_loader()
      $.ajax({
        url: $url,
        error: err => {
          console.log()
          alert("An error occured")
        },
        success: function (resp) {
          if (resp) {
            $('#uni_modal .modal-title').html($title)
            $('#uni_modal .modal-body').html(resp)
            if ($size != '') {
              $('#uni_modal .modal-dialog').addClass($size + '  modal-dialog-centered')
            } else {
              $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-md modal-dialog-centered")
            }
            $('#uni_modal').modal({
              show: true,
              backdrop: 'static',
              keyboard: false,
              focus: true
            })
            end_loader()
          }
        }
      })
    }
    window._conf = function ($msg = '', $func = '', $params = []) {
      $('#confirm_modal #confirm').attr('onclick', $func + "(" + $params.join(',') + ")")
      $('#confirm_modal .modal-body').html($msg)
      $('#confirm_modal').modal('show')
    }

    window.my_modal = function (title = '', url = '', size = '') {
      if (typeof start_loader === 'function') start_loader();

      $.ajax({
        url: url,
        error: err => {
          console.error("AJAX Error:", err);
          alert("An error occurred while loading the modal.");
          if (typeof end_loader === 'function') end_loader();
        },
        success: function (resp) {
          if (resp) {
            $('#my_modal .modal-title').html(title);
            $('#my_modal .modal-body').html(resp);

            if (size !== '') {
              $('#my_modal .modal-dialog').attr('class', 'modal-dialog ' + size + ' modal-dialog-centered');
            } else {
              $('#my_modal .modal-dialog').attr('class', 'modal-dialog modal-md modal-dialog-centered');
            }

            var modal = new bootstrap.Modal(document.getElementById('my_modal'), {
              backdrop: 'static',
              keyboard: false,
              focus: true
            });
            modal.show();

            if (typeof end_loader === 'function') end_loader();
          }
        }
      });
    }

  })
</script>
<!-- Footer-->
<footer class="py-5 bg-dark">
  <div class="container">
    <p class="m-0 text-center text-white">Copyright &copy; <?php echo $_settings->info('short_name') ?> 2025</p>
    <p class="m-0 text-center text-white">Developed By: <a href="mailto:namubazz24@gmail.com">nomnom</a></p>
  </div>
</footer>


<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="<?php echo base_url ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="<?php echo base_url ?>plugins/select2/js/select2.full.min.js"></script>
<!-- stripe -->
<script src="https://js.stripe.com/v3/"></script>
<!-- Summernote -->
<script src="<?php echo base_url ?>plugins/summernote/summernote-bs4.min.js"></script>
<script src="<?php echo base_url ?>plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url ?>plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo base_url ?>plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?php echo base_url ?>plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<!-- overlayScrollbars -->
<!-- <script src="<?php echo base_url ?>plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script> -->
<!-- AdminLTE App -->
<script src="<?php echo base_url ?>dist/js/adminlte.js"></script>