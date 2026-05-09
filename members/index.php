<?php
include_once '../includes/class/member.class.php';
new Member(true);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>Member Area - Keystone Concert Band</title>

    <?php require '../includes/common_css.php'; ?>
    <link href="<?= asset('/css/member.css') ?>" rel="stylesheet">

</head>

<body>

    <?php require '../includes/nav.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="bs-component">
                    <div class="jumbotron">
                        <h1>KCB Members</h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-lg-12">
                <div class="mb-4 pb-2 border-bottom">
                    <h2>Welcome
                        <?php echo $_SESSION["office"] . ' ' ?: "" ?><?php echo $_SESSION["firstName"] ?: 'Firstname'?>
                        <?php echo $_SESSION["lastName"] ?: 'Lastname' ?>!</h2>
                </div>
                <div class='alert alert-info'><strong>Discord</strong><br />Please join us on Discord (a group chat
                    platform) at <a href="https://discord.gg/Szux9TQ" target="_blank">https://discord.gg/Szux9TQ</a>
                </div>

                Welcome to the KCB Member section! With this site you can update your information, view the other band
                member's
                information, and find all the music the band has to play.<br>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="bs-component">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title">My Info</h4>
                            <h6 class="card-subtitle mb-2 text-muted">View or update your information.
                            </h6>
                            <a href="myInfo.php" class="card-link">Go to My Info</a>
                        </div>
                    </div>
                </div>
                <div class="bs-component">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title">Current Roster</h4>
                            <h6 class="card-subtitle mb-2 text-muted">View the current list of band members.</h6>
                            <a href="members.php" class="card-link">Go to Roster</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h4 class="card-title">Music</h4>
                        <h6 class="card-subtitle mb-2 text-muted">View the current list of band members.</h6>
                        <a href="music.php" class="card-link">Go to Music</a>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h4 class="card-title">Band Documents</h4>
                        <h6 class="card-subtitle mb-2 text-muted">View the current list of band members.</h6>
                        <a href="documents.php" class="card-link">Go to Documents</a>
                    </div>
                </div>
            </div>
        </div>
        <?php require '../includes/footer.php'; ?>
    </div> <!-- /container -->

    <?php require '../includes/common_js.php'; ?>
</body>

</html>