<?php
require_once __DIR__ . '/../../Shared/Classes/protectedMusic.class.php';
require_once __DIR__ . '/../../Shared/asset.php';
new ProtectedMusic();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require __DIR__ . '/../../../templates/partials/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>Music - Keystone Concert Band</title>

    <?php require __DIR__ . '/../../../templates/partials/common_css.php'; ?>
    <link rel="stylesheet" href="<?= asset('/assets/css/member.css') ?>">
    <style>
    .ui-autocomplete-loading {
        background: white url("/assets/images/ui-anim_basic_16x16.gif") right center no-repeat;
    }

    .ui-autocomplete {
        z-index: 5000;
    }
    </style>
</head>

<body>

    <?php require __DIR__ . '/../../../templates/partials/nav.php'; ?>
    <div class="container">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-lg-12">
                <div class="mb-4 pb-2 border-bottom">
                    <h2>Music</h2>
                </div>
                <div id="pageAlert" class="alert d-none alert-dismissible fade show" role="alert"></div>
                <?php if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) { ?>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modal_add_edit">Add Music</button>
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <div id="msgMainHeader" class="h4 d-none"></div>
                    </div>
                </div>
                <?php } ?>
                <div class="row">
                    <div class="col-lg-12">
                        <table id="kcbMusicTable" class="table table-striped table-bordered" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th><i class="fa-solid fa-cogs"></i></th>
                                    <th>Title</th>
                                    <th>Notes</th>
                                    <th>Music Link</th>
                                    <th>Genre</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal_add_edit" tabindex="-1" aria-labelledby="modalAddEditLabel" aria-hidden="true">
            <form id="form_music" class="needs-validation" novalidate>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAddEditLabel">Add Music</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="formAlert" class="alert d-none alert-dismissible fade show" role="alert"></div>
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label for="title" class="form-label">Title*</label>
                                    <input type="text" class="form-control" name="title" id="title" placeholder="Title"
                                        value="" required="true" maxlength="255">
                                    <div class="invalid-feedback">Please complete this field.</div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" placeholder="Notes"
                                        maxlength="2000" rows="3"></textarea>
                                    <div class="invalid-feedback">Please complete this field.</div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label for="genre" class="form-label">Genre*</label>
                                    <select class="form-select" name="genre" id="genre" required="true">
                                        <option value="0" selected="Selected">Select type</option>
                                    </select>
                                    <div class="invalid-feedback">Please complete this field.</div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label for="music_link" class="form-label">Youtube link to music</label>
                                    <input type="text" class="form-control" name="music_link" id="music_link"
                                        placeholder="Music Link" value="" maxlength="2000">
                                    <div class="invalid-feedback">Please complete this field.</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="uid" name="uid" value="" />
                            <div id="msgSubmit" class="h4 d-none"></div>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php require __DIR__ . '/../../../templates/partials/footer.php'; ?>
    </div> <!-- /container -->

    <script type="text/javascript">
    var accountType = "<?=$_SESSION['accountType']?>";
    </script>
    <?php require __DIR__ . '/../../../templates/partials/common_js.php'; ?>
    <script type="text/javascript" src="<?=asset('/assets/js/shared.js')?>"></script>
    <?php require __DIR__ . '/../../../templates/partials/common_datatables.php'; ?>
    <script type="text/javascript" src="<?=asset('/assets/js/music.js')?>"></script>
</body>

</html>
