<?php

include "config.php";
ignore_user_abort(true);
set_time_limit(0);
setlocale(LC_TIME, 'de_DE@euro', 'de_DE', 'de', 'ge');

$dataFileName = dirname(__FILE__) . "/data.json";

function getCurrentGamesByDateAndIsHomeGame($weeks): array
{
    $allGames = getAllGamesFromFile();
    $minDate = new DateTime("yesterday");
    $maxDate = new DateTime("today + $weeks weeks");

    $currentGamesByDateAndIsHomeGame = array();
    foreach ($allGames as $game) {
        $game->date = getGameDate($game);
        if ($game->date < $minDate || $game->date > $maxDate) {
            continue;
        } else {
            $time = $game->date->getTimestamp();
            $regex = "#" . getenv('CLUB_NAME') . "( [123])?#";
            $game->isHomeGame = preg_match($regex, $game->gHomeTeam);

            if (!isset($currentGamesByDateAndIsHomeGame[$time])) {
                $currentGamesByDateAndIsHomeGame[$time] = array();
            }
            //$game->caterers = getGameCaterers($game);
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

function createTeamToggleButton($team): string
{
    return <<<HTML
<button type="button" class="btn btn-block btn-$team->color team-toggle" data-team-id="$team->teamID">$team->name</button>
HTML;
}

function createWeeksButton($weeks_option, $weeks_active): string
{
    $active = ($weeks_option === $weeks_active) ? ' active' : '';
    #$url = '?' . teamButtonURL($team);
    return <<<HTML
<a class="btn btn-default$active" href="?weeks=$weeks_option">nächste $weeks_option Wochen</a>
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
<ul class="list-group">
    <li class="list-group-item list-group-item-danger">
        <h4 class="list-group-item-heading">$date</h4>
    </li>
    $gamesHTML
</ul>
HTML;
}

function createGameHTML($game): string
{
    $locationLabel = createLocationLabel($game);
    $teamLabel = createTeamLabel($game->teamID);
    return <<<HTML
    <li class="list-group-item game-list-item" data-team-id="{$game->teamID}">
        <strong>$game->gTime Uhr</strong>&nbsp;<span>$game->gClassSname:</span>
        <span>$game->gHomeTeam</span> - <span>$game->gGuestTeam</span>
        <br/>
        $teamLabel$locationLabel
    </li>
HTML;
}

function createTeamLabel($teamID): string
{
    $team = getTeams()[$teamID];
    return <<<HTML
<span class="label label-$team->color">$team->name</span>
HTML;
}

function createLocationLabel($game): string
{
    if ($game->isHomeGame) {
        if (isInReblandhalle($game)) {
            return '&nbsp;<span class="label label-success">Reblandhalle</span>';
        } else if (isInParkringhalle($game)) {
            return '&nbsp;<span class="label label-danger">Parkringhalle</span>';
        } else {
            return '&nbsp;<span class="label label-default">andere Halle</span>';
        }
    }
    return "";
}
