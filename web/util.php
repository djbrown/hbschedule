<?php

include "config.php";
ignore_user_abort(true);
set_time_limit(0);
setlocale(LC_TIME, "de_DE@euro", "de_DE", "de", "ge");

$dataFileName = dirname(__FILE__) . "/data.json";

function getCurrentGamesByDateAndIsHomeGame($weeks, $teams): array
{
    $allGames = getAllGamesFromFile();
    $minDate = new DateTime("yesterday");
    $maxDate = new DateTime("today + $weeks weeks");

    $currentGamesByDateAndIsHomeGame = array();
    foreach ($allGames as $game) {
        $game->date = getGameDate($game);
        if ($game->date < $minDate || $game->date > $maxDate || !in_array($game->teamID, $teams)) {
            continue;
        } else {
            $time = $game->date->getTimestamp();
            $regex = "#" . getenv("CLUB_NAME") . "( [123])?#";
            $game->isHomeGame = preg_match($regex, $game->gHomeTeam);

            if (!isset($currentGamesByDateAndIsHomeGame[$time])) {
                $currentGamesByDateAndIsHomeGame[$time] = array();
            }
            array_push($currentGamesByDateAndIsHomeGame[$time], $game);
        }
    }

    ksort($currentGamesByDateAndIsHomeGame);
    foreach ($currentGamesByDateAndIsHomeGame as $key => $gamesOnDate) {
        usort($currentGamesByDateAndIsHomeGame[$key], "cmpGames");
    }
    return $currentGamesByDateAndIsHomeGame;
}

function getAllGamesFromFile(): array
{
    global $dataFileName;
    if (!file_exists($dataFileName)) {
        return array();
    } else {
        $data = file_get_contents($dataFileName);
        return json_decode($data);
    }
}

function dump($var)
{
    error_log(var_export($var, true));
}

function getGameDate($game): DateTime
{
    if ($game->gDate === "") {
        $date = date_create("last month");
        error_log(var_export($date, true));
        return $date;
    }
    $dateFormat = "d.m.y H:i:s";
    $dateString = $game->gDate . " 00:00:00";
    $date = date_create_from_format($dateFormat, $dateString);
    return $date;
}

$cmpID = 0;
function cmpGames($game1, $game2): int
{
    global $cmpID;
    $cmpID++;
    if ($game1->date == $game2->date) {
        if ($game1->isHomeGame && $game2->isHomeGame) {
            if (isInReblandhalle($game1) === isInReblandhalle($game2)) {
                $cmp = strcmp($game1->gTime, $game2->gTime);
            } elseif (isInReblandhalle($game1)) {
                $cmp = -1;
            } else {
                $cmp = 1;
            }
        } elseif ($game1->isHomeGame) {
            $cmp = -1;
        } elseif ($game2->isHomeGame) {
            $cmp = 1;
        } else {
            $cmp = strcmp($game1->gTime, $game2->gTime);
        }
    } else {
        $cmp = $game1->date - $game2->date;
    }
    return $cmp;
}

function isInReblandhalle($game)
{
    return $game->gGymnasiumID === "487";
}

function isInParkringhalle($game)
{
    return $game->gGymnasiumID === "666";
}

function teamButtonURL($team): string
{
    $params = $_GET;
    if (!isset($params["teams"])) {
        $params["teams"] = array();
    }

    if (($key = array_search($team->teamID, $params["teams"])) !== false) {
        unset($params["teams"][$key]);
    } else {
        $params["teams"][] = $team->teamID;
    }

    $url = http_build_query($params);
    $url = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $url);
    return "?$url";
}

function createTeamToggleButton($team, $teams_filter): string
{
    $url = teamButtonURL($team);
    $outline = (in_array($team->teamID, $teams_filter)) ? "" : "outline-";
    return <<<HTML
<a class="btn d-block btn-$outline$team->color" href="$url">$team->name</a>
HTML;
}

function weekButtonURL($week): string
{
    $params = $_GET;
    $params["week"] = $week;
    $url = http_build_query($params);
    $url = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $url);
    return "?$url";
}

function createWeeksButton($weeks_option, $weeks_active): string
{
    $btn = ($weeks_option == $weeks_active) ? "btn-secondary" : "btn-outline-secondary";
    $url = weekButtonURL($weeks_option);
    return <<<HTML
<a class="btn $btn" href="$url">nächste $weeks_option Wochen</a>
HTML;
}

function createGameDayHTML($time, $gameDay): string
{
    $date = datefmt_format(datefmt_create("de-DE", pattern: "eee dd.LL.yyyy"), $time);
    $gamesHTML = "";
    foreach ($gameDay as $key => $game) {
        $gamesHTML .= createGameHTML($game);
    }
    return <<<HTML
<li class="list-group-item list-group-item-danger">
    <h4>$date</h4>
</li>
$gamesHTML
HTML;
}

function createGameHTML($game): string
{
    $locationBadge = createLocationBadge($game);
    $teamBadge = createTeamBadge($game->teamID);
    return <<<HTML
    <li class="list-group-item game-list-item">
        <strong>$game->gTime Uhr</strong>&nbsp;<span>$game->gClassSname:</span>
        <span>$game->gHomeTeam</span> - <span>$game->gGuestTeam</span>
        <br/>
        $teamBadge$locationBadge
    </li>
HTML;
}

function createTeamBadge($teamID): string
{
    $team = getTeams()[$teamID];
    return <<<HTML
<span class="badge bg-$team->color">$team->name</span>
HTML;
}

function createLocationBadge($game): string
{
    if ($game->isHomeGame) {
        if (isInReblandhalle($game)) {
            return '&nbsp;<span class="badge bg-success">Reblandhalle</span>';
        } else if (isInParkringhalle($game)) {
            return '&nbsp;<span class="badge bg-danger">Parkringhalle</span>';
        }
    }
    return "";
}
