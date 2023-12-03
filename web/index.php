<?php

require("util.php");

$weeks_options = array(2, 40);
$weeks_active = $weeks_options[0];
if (isset($_GET['weeks']) && in_array($_GET['weeks'], $weeks_options)) {
    $weeks_active = $_GET['weeks'];
}

$teams_filter = $_GET['teams'] ?? array();
$teams = $_GET['teams'] ?? getTeams();

?>

<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Interaktive Vorschau kommender Spiele von <?php echo getenv('CLUB_NAME'); ?>" />
    <title><?php echo getenv('CLUB_NAME'); ?> Spielvorschau</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script>
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }
    </script>
</head>

<body>
    <div class="container">
        <div class="d-grid gap-2 d-sm-block my-2">
            <button class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#team-toggle-buttons">Teams</button>
            <a class="btn btn-secondary" href=".">zurücksetzen</a>
            <div class="btn-group">
                <?php foreach ($weeks_options as $weeks_option) {
                    echo createWeeksButton($weeks_option, $weeks_active);
                } ?>
            </div>
        </div>
        <div id="team-toggle-buttons" class="collapse card card-body my-2">
            <div class="row row-cols-3 row-cols-sm-4 row-cols-md-6 g-2">
                <?php foreach (getTeams() as $team) { ?>
                    <div class="col">
                        <?php echo createTeamToggleButton($team, $teams_filter) ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php foreach (getCurrentGamesByDateAndIsHomeGame($weeks_active, $teams) as $time => $gameDay) { ?>
            <ul class="list-group my-2">
                <?php echo createGameDayHTML($time, $gameDay); ?>
            </ul>
        <?php } ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>