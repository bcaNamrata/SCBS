 <!-- Header-->
 <header class="bg-dark py-5" id="main-header">
    <div class="container h-100 d-flex align-items-center justify-content-center w-100">
        <div class="text-center text-white w-100">
            <h1 class="display-5 fw-bolder mx-5"><?php echo $_settings->info('name') ?></h1>
            <div class="col-auto mt-4">
                <!-- <a class="btn btn-warning btn-lg rounded-0" href="./?p=booking">Book Now</a> -->
            </div>
        </div>
    </div>
</header>
<!-- Section-->
<section class="py-5">
    <div class="container">

    <div class="suhehad pb-3">
        <h3 >About us</h3>
    </div>
    <p>
        The application allows the said business management to provide their clients or possible client an automated and online platform where they can explore their facilities that are for rent. The possible clients can book their desired facility or facilities if it is available on the date they wanted. This application has a simple and pleasant user interface. This project contains user-friendly features and functionalities.
    </p>

    
         <?php include './welcome.html' ?>
    </div>
</section>
<script>
    $(function(){
        $('#search').on('input',function(){
            var _search = $(this).val().toLowerCase().trim()
            $('#service_list .item').each(function(){
                var _text = $(this).text().toLowerCase().trim()
                    _text = _text.replace(/\s+/g,' ')
                    console.log(_text)
                if((_text).includes(_search) == true){
                    $(this).toggle(true)
                }else{
                    $(this).toggle(false)
                }
            })
            if( $('#service_list .item:visible').length > 0){
                $('#noResult').hide('slow')
            }else{
                $('#noResult').show('slow')
            }
        })
        $('#service_list .item').hover(function(){
            $(this).find('.callout').addClass('shadow')
        })
        $('#service_list .view_service').click(function(){
            uni_modal("Service Details","view_service.php?id="+$(this).attr('data-id'),'mid-large')
        })
        $('#send_request').click(function(){
            uni_modal("Fill the Service Request Form","send_request.php",'large')
        })

    })
    $(document).scroll(function() { 
        $('#topNavBar').removeClass(' navbar-light navbar-light bg-gradient-light text-dark')
        if($(window).scrollTop() === 0) {
           $('#topNavBar').addClass('navbar-light text-dark')
        }else{
           $('#topNavBar').addClass('navbar-light bg-gradient-light ')
        }
    });
    $(function(){
        $(document).trigger('scroll')
    })
</script>