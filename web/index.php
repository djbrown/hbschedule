<?php

require("util.php");

$weeks_options = array(2, 40);
$weeks_active = $weeks_options[0];
if (isset($_GET['weeks']) && in_array($_GET['weeks'], $weeks_options)) {
    $weeks_active = $_GET['weeks'];
}

$teams_filter = $_GET['teams'] ?? array();

?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo getenv('CLUB_NAME'); ?> Spielvorschau</title>
    <meta name="description" content="Interaktive Vorschau kommender Spiele von <?php echo getenv('CLUB_NAME'); ?>"/>

    <?php require "head-base.php"; ?>

    <script src="/js/hbschedule.js"></script>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class=" panel-heading">
                    <h4>Filter</h4>
                    <button class="btn btn-default" data-toggle="collapse" data-target="#team-toggle-buttons">
                        ändern
                    </button>
                    <a class="btn btn-default" href=".">zurücksetzen</a>
                </div>
                <div id="team-toggle-buttons" class="panel-body row collapse">
<?php foreach (getTeams() as $team) { ?>
                <div class="col-xs-6 col-sm-2" style="margin-top: 20px"><?php echo createTeamToggleButton($team, $teams_filter) ?></div>
<?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 btn-group">
            <?php foreach ($weeks_options as $weeks_option) {
                echo createWeeksButton($weeks_option, $weeks_active);
            } ?>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-xs-12">
            <?php
            foreach (getCurrentGamesByDateAndIsHomeGame($weeks_active, $teams) as $time => $gameDay) {
                echo createGameDayHTML($time, $gameDay);
            } ?>
        </div>
    </div>
</div>
</body>
</html>
