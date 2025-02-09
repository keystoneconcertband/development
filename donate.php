<!DOCTYPE html>
<html lang="en">

<head>
    <?php require 'includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band continues to operate by donations made by you!">

    <title>Donate - Keystone Concert Band</title>

    <?php require 'includes/common_css.php'; ?>

</head>

<body>

    <?php require 'includes/nav.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="bs-component">
                    <div class="jumbotron">
                        <h1>Donate</h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h2>Paypal donation</h2>
                <p>We accept donations online via PayPal. You do not need an account to donate and your donation is
                    always tax-deductible.</p>
                <p>
                <form action="https://www.paypal.com/donate" method="post" target="_top">
                    <input type="hidden" name="hosted_button_id" value="ZEXNBASLQRSNS" />
                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0"
                        name="submit" title="PayPal - The safer, easier way to pay online!"
                        alt="Donate with PayPal button" />
                    <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
                </form>
                </p>
            </div>
            <div class="col-md-6">
                <h2>Mail a check</h2>
                <p>If you would rather mail us a check, please click the button below to view the mailing address. If
                    you would like a receipt for your check, please include your address with your donation.</p>
                <p><button class="btn btn-info" type="button" data-toggle="modal" data-target="#addressModal">View
                        address &raquo;</button></p>

                <div id="addressModal" class="modal" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"
                                    aria-hidden="true">&times;</button>
                                <h4 class="modal-title">Mailing Address</h4>
                            </div>
                            <div class="modal-body">
                                <p>Keystone Concert Band<br>
                                    145 E Main Street<br>
                                    1st Floor<br>
                                    Mechanicsburg, PA 17055
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require 'includes/footer.php'; ?>
    </div> <!-- /container -->

    <?php require 'includes/common_js.php'; ?>
</body>

</html>