<?php
require_once '../includes/class/protectedAdmin.class.php';
require_once '../includes/asset.php';
new ProtectedAdmin();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>Schedule - Keystone Concert Band</title>

    <?php require '../includes/common_css.php'; ?>
    <link rel="stylesheet" href="<?= asset('/css/member.css') ?>">
</head>

<body>

    <?php require '../includes/nav.php'; ?>
    <div class="container">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-lg-12">
                <div class="mb-4 pb-2 border-bottom">
                    <h2>Schedule</h2>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-12">
                        <div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modal_add_edit">Add New</button>
                        </div>
                    </div>
                </div>
                <div id="pageAlert" class="alert d-none alert-dismissible fade show" role="alert"></div>
                <div class="row">
                    <div class="col-lg-12">
                        <table id="kcbScheduleTable" class="table table-striped table-bordered" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th><i class="fa-solid fa-cogs"></i></th>
                                    <th>Title</th>
                                    <th>Concert Date/Time</th>
                                    <th>Pants</th>
                                    <th>Chair</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal_add_edit" tabindex="-1" aria-labelledby="modalAddEditLabel"
            aria-hidden="true">
            <form id="form_schedule" class="needs-validation" novalidate>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAddEditLabel">Schedule Entry</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="formAlert" class="alert d-none alert-dismissible fade show" role="alert"></div>
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" id="title"
                                        placeholder="Title" value="" required maxlength="255">
                                    <div class="invalid-feedback">Please complete this field.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label for="concertBegin" class="form-label">Concert Date/Time</label>
                                    <input type="date" class="form-control" id="concertDate" required>
                                    <div class="row g-2 mt-1">
                                        <div class="col-6">
                                            <select class="form-select" id="concertHour" aria-label="Concert hour" required>
                                                <option value="">Hour</option>
                                                <?php for ($hour = 0; $hour < 24; $hour++): ?>
                                                    <option value="<?= sprintf('%02d', $hour) ?>"><?= sprintf('%02d', $hour) ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <select class="form-select" id="concertMinute" aria-label="Concert minute" required>
                                                <option value="">Minute</option>
                                                <option value="00">00</option>
                                                <option value="15">15</option>
                                                <option value="30">30</option>
                                                <option value="45">45</option>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" name="concertBegin" id="concertBegin" required>
                                    <div class="invalid-feedback">Please complete this field.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="pants" id="pants" value="1">
                                        <label class="form-check-label" for="pants">Tan pants (otherwise black)</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="chair" id="chair" value="1">
                                        <label class="form-check-label" for="chair">Assign chair</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" name="address" id="address"
                                        placeholder="Address" maxlength="255" required>
                                    <div class="invalid-feedback">Please complete this field.</div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="uid" name="uid" value="" />
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php require '../includes/footer.php'; ?>
    </div> <!-- /container -->

    <?php require '../includes/common_js.php'; ?>
    <script type="text/javascript" src="<?=asset('/kcb-js/shared.js')?>"></script>
    <?php require '../includes/common_datatables.php'; ?>
    <script type="text/javascript" src="<?=asset('/kcb-js/schedule.js')?>"></script>
</body>

</html>
