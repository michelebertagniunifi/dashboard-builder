<?php

/* Dashboard Builder.
  Copyright (C) 2018 DISIT Lab https://www.disit.org - University of Florence

  This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as
   published by the Free Software Foundation, either version 3 of the
   License, or (at your option) any later version.
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU Affero General Public License for more details.
   You should have received a copy of the GNU Affero General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>. */

include '../config.php';
require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;

error_reporting(E_ERROR | E_NOTICE);
date_default_timezone_set('Europe/Rome');

session_start();
$link = mysqli_connect($host, $username, $password);
mysqli_select_db($link, $dbname);

$response = [];
$result = [];
$filePathArray = [];

if(isset($_SESSION['loggedUsername']) || $_SESSION['isPublic'] === true)
{

    if(isset($_SESSION['refreshToken']))
    {
        $action = $_GET['action'];

        switch($action)
        {
            case "getAll":
                $queryTemplates = "SELECT * FROM Dashboard.SynopticTemplates WHERE singleReadVariable IS NOT NULL;";
                $r = mysqli_query($link, $queryTemplates);
                if($r)
                {
                    while($row = mysqli_fetch_assoc($r))
                    {
                        array_push($result,  $row['parameters']);
                    //    $result[$row['singleReadVariable']] = $row['parameters'];
                    }
                    $response['singleReadVars'] = $result;
                    $response['detail'] = 'Succesful Response.';
                } else {
                    $response['detail'] = 'Synoptic Templates Query KO.';
                }
                break;
        }
    } else {
        $response['detail'] = 'Synoptic Templates Query Not Authorized.';
    }

    echo json_encode($response);
}
