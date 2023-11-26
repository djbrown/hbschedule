<?php

include "web/util.php";

updateDataFile();

function updateDataFile()
{
    global $dataFileName;
    $allGames = array();
    foreach (getTeams() as $team) {
        $allGames = array_merge($allGames, getAllGamesOfTeam($team));
    }
    $data = json_encode($allGames);
    file_put_contents($dataFileName, $data);
}

function getAllGamesOfTeam($team): array
{
    $url = "http://www.handball4all.de/m/php/spo-proxy_public.php?cmd=data&lvTypeNext=team&lvIDNext=$team->teamID";
    $json = file_get_contents($url);
    $response = json_decode($json)[0];
    $games = $response->dataList;
    foreach ($games as $game) {
        $game->teamID = $response->lvIDPathStr;
    }
    return $games;
}