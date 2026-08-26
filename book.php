<?php
session_start();
if (empty($_SESSION['book_csrf_token'])) {
    $_SESSION['book_csrf_token'] = bin2hex(random_bytes(32));
}
$bookCsrfToken = $_SESSION['book_csrf_token'];
$formCreatedAt = time();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once 'src/Shared/asset.php'; ?>
    <?php require_once 'templates/partials/common_meta.php'; ?>
    <meta name="description"
        content="The Keystone Concert Band is always looking for new venues to play at. Can we play at your event?">

    <title>Book a concert - Keystone Concert Band</title>

    <?php require_once 'templates/partials/common_css.php'; ?>
</head>

<body>
    <?php require_once 'templates/partials/nav.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="bs-component">
                    <div class="jumbotron">
                        <h1 class="display-5">Book</h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="page-header">
                    <h2 class="border-bottom">Book a concert</h2>
                </div>
                <p>The Keystone Concert Band plays a wide variety of music, and has performed in many different
                    locations.
                    We've played at the State Capitol, nursing homes, church picnics, community events, ice cream
                    festivals,
                    and even weddings, in addition to our regular public concerts. <br><br>
                    For more information on booking the Keystone Concert Band for your event, contact us below using the
                    form.
                    Please keep in mind that it is best to contact us at least 3-6 months before your event,
                    as we are filling dates as much as a year in advance.</p>
                <h3 class="border-bottom">Contact Us</h3>
                <div id="formAlert" class="alert d-none alert-dismissible fade show" role="alert"></div>
                <form class="row g-3" id="frmBook">
                    <div class="d-none" aria-hidden="true">
                        <label for="honeypot">Leave this field blank</label>
                        <input type="text" class="form-control" id="honeypot" name="honeypot" autocomplete="off"
                            tabindex="-1">
                    </div>
                    <input type="hidden" id="csrf_token" name="csrf_token" value="<?= htmlentities($bookCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" id="formCreatedAt" name="formCreatedAt" value="<?= htmlentities($formCreatedAt, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" id="jsCheck" name="jsCheck" value="">
                    <div class="col-lg-12">
                        If you are interested in booking the band, please fill out the form below with your contact
                        information so we can get back to you.<br>
                        <em>Fields marked with an * are required.</em>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="txtName" name="txtName" placeholder="Name"
                                required="true">
                            <label for="floatingInput">* Name</label>
                            <div class="invalid-feedback">Please enter a name</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="txtPhone" name="txtPhone"
                                placeholder="Phone Number" minlength="10" maxlength="10" required="true">
                            <label for="floatingInput">* Phone Number</label>
                            <div class="invalid-feedback">Sorry, that phone number is invalid. Please enter a valid
                                10-digit phone number.</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="txtEmail" name="txtEmail"
                                placeholder="Email Address" required="true">
                            <label for="floatingInput">* Email Address</label>
                            <div class="invalid-feedback">Sorry, that email address is invalid. Please enter a valid
                                email address.</div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <label for="exampleTextarea" class="form-label mt-4">* Booking Information</label>
                        <textarea class="form-control" id="txtComments" name="txtComments" rows="3"
                            required="true"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
        <?php require_once 'templates/partials/footer.php'; ?>
    </div> <!-- /container -->

    <?php require_once 'templates/partials/common_js.php'; ?>
    <script type="text/javascript" src="<?=asset('assets/js/shared.js')?>"></script>
    <script type="text/javascript" src="<?=asset('assets/js/book.js')?>" ></script>
</body>

</html>
