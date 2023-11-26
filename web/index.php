<?php require("util.php"); ?>
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
                    <button id="btn-reset-teams" class="btn btn-default">zurücksetzen</button>
                </div>
                <div id="team-toggle-buttons" class="panel-body row collapse">
<?php foreach (getTeams() as $team) { ?>
                <?php echo '<div class="col-xs-6 col-sm-2" style="margin-top: 20px">' . "\n"; ?>
                    <?php echo createTeamToggleButton($team) . "\n";?>
                <?php echo '</div>' . "\n";
} ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 btn-group">
<?php
            $weeks_available = array(2, 40);
            $weeks = $weeks_available[0];
            if (isset($_GET['weeks']) && in_array($_GET['weeks'], $weeks_available)) {
                $weeks = $_GET['weeks'];
            }
            foreach($weeks_available as $w) {
                $active = ($w == $weeks) ? ' active' : '';?>
            <?php echo '<a href="?weeks=' . $w .'" class="btn btn-default' . $active . '">nächste ' . $w . ' Wochen</a>' . "\n";?>
<?php } ?>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-xs-12">
            <?php
            foreach (getCurrentGamesByDateAndIsHomeGame($weeks) as $time => $gameDay) {
                echo createGameDayHTML($time, $gameDay);
            } ?>
        </div>
    </div>
</div>
</body>
</html>
