<?php
    require_once '../includes/class/protectedAdmin.class.php';
    header('Content-Type: application/json');

    $admin = new ProtectedAdmin();

    if(isset($_POST['type']) && $_POST['type'] === 'add') {
        if(!isset($_POST['title'])) {
            echo json_encode('Title is required.');
        } elseif(!isset($_POST['concertBegin'])) {
            echo json_encode('Concert date/time is required.');
        } else {
            // normalize checkbox values
            $pants = isset($_POST['pants']) && $_POST['pants'] ? 1 : 0;
            $chair = isset($_POST['chair']) && $_POST['chair'] ? 1 : 0;
            echo json_encode($admin->addSchedule($_POST['title'], $_POST['concertBegin'], $pants, $chair, $_POST['address'] ?? ''));
        }
    }
    elseif(isset($_POST['type']) && $_POST['type'] === 'edit') {
        if(!isset($_POST['uid'])) {
            echo json_encode('Unique Identifier is missing.');
        } elseif(!isset($_POST['title'])) {
            echo json_encode('Title is required.');
        } elseif(!isset($_POST['concertBegin'])) {
            echo json_encode('Concert date/time is required.');
        } else {
            $pants = isset($_POST['pants']) && $_POST['pants'] ? 1 : 0;
            $chair = isset($_POST['chair']) && $_POST['chair'] ? 1 : 0;
            echo json_encode($admin->editSchedule($_POST['uid'], $_POST['title'], $_POST['concertBegin'], $pants, $chair, $_POST['address'] ?? ''));
        }
    }
    elseif(isset($_POST['type']) && $_POST['type'] === 'getScheduleRecord') {
        if(!isset($_POST['uid'])) {
            echo json_encode('Unique Identifier is missing.');
        } else {
            echo json_encode($admin->getScheduleRecord($_POST['uid']));
        }
    }
    else {
        echo json_encode($admin->getSchedules());
    }
?>
