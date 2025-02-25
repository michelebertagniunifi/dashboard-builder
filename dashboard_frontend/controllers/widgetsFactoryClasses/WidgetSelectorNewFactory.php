<?php

class WidgetSelectorNewFactory extends aGenericWidgetFactory
{
    //Sovrascrive l'originaria
    function completeWidget()
    {
        include '../config.php';
        $link = mysqli_connect($host, $username, $password);
        mysqli_select_db($link, $dbname);

        $myQuery = null;
        $myDesc = null;
        $myQueryType = null;
        $defaultColors1 = ["#ffdb4d", "#ff9900", "#ff6666", "#00e6e6", "#33ccff", "#33cc33", "#009900"];
        $defaultColors2 = ["#fff5cc", "#ffe0b3", "#ffcccc", "#99ffff", "#99e6ff", "#adebad", "#80ff80"];
        $myIconText = null;
        $high_level_type = null;
        $nature = null;
        $sub_nature = null;
       // $mapPinIcon = null;
        $newMapPinColor = "#959595";
     //   $defaultPinColor = "#959595";
        $scaleFactor = 3;
        
        if($this->widgetTypeDbRow['mono_multi'] == 'Mono')
        {
            //Qui non usata 
        }
        else
        {
            $selectorParameters = json_decode($this->startParams->parameters);

            $count = 0;
            foreach($this->selectedRows as $selectedRowKey => $selectedRow) 
            {
                //Se high_level_type è POI si fa la query "classica", se invece è Sensor va presa dai parameters e bisogna aggiungerci il parametro per il time trend
                switch($selectedRow['high_level_type'])
                {
                    case "wfs":
                        $myQuery = $selectedRow['parameters'];
                        $myDesc = $selectedRow['unique_name_id'];
                        $myQueryType = "wfs";
                        break;

                    case "Sensor Device";
                    case "IoT Device":
                    case "Mobile Device":
                    case "Data Table Device":
                    case "Sensor":
                        $myQuery = $selectedRow['parameters'] . "&fromTime=3-day";
                        $myDesc = $selectedRow['unique_name_id'];
                        $myQueryType = "Sensor";
                        break;

                    case "MyKPI":
                    case "MyPOI":
                        $myQuery = $selectedRow['parameters'];
                        $myDesc = $selectedRow['unique_name_id'];
                        $myQueryType = "MyPOI";
                        break;

                    case "Tools":
                    case "Traffic Flow":
                    case "Heatmap":
                        $myQuery = $selectedRow['parameters'];
                        $myDesc = $selectedRow['unique_name_id'];
                        $myQueryType = "Heatmap";
                        break;

                    case "IoT Device Model":
                    case "Data Table Model":
                    case "Mobile Device Model":
                        $baseUrlKb = "https://servicemap.disit.org/WebAppGrafo/api/v1/";
                        if (isset($_SESSION['orgKbUrl'])) {
                            $baseUrlKb = $_SESSION['orgKbUrl'];
                        }
                        if (isset($_SESSION['orgGpsCentreLatLng'])) {
                            $orgGpsCentreLatLng = $_SESSION['orgGpsCentreLatLng'];
                            $orgGpsLat = trim(explode(",", $orgGpsCentreLatLng)[0]);
                            $orgGpsLng = trim(explode(",", $orgGpsCentreLatLng)[1]);
                        } else {
                            // Se è di organizzazione "Other" o nessuna dà le coordinate del centro di Firenze di default
                            $orgGpsLat = "43.769789";
                            $orgGpsLng = "11.255694";
                        }
                        if (isset($_SESSION['orgZoomLevel'])) {
                            $orgZoomLevel = $_SESSION['orgZoomLevel'];
                        }
                        //    $myQuery = $baseUrlKb . "?selection=41.47154438707647;6.459960937499999;45.182036837015886;15.595092773437498&categories=" . $selectedRow['sub_nature'] . "&maxResults=200&format=json";
                        $myQuery = $baseUrlKb . "?selection=" . ($orgGpsLat-0.125) . ";" . ($orgGpsLng-0.25) . ";" . ($orgGpsLat+0.125) .";". ($orgGpsLng+0.25) . "&categories=" . $selectedRow['sub_nature'] . "&maxResults=200&format=json&model=" . $selectedRow['unique_name_id'];
                        $myDesc = $selectedRow['unique_name_id'];
                        $myQueryType = "Default";
                        break;
                    
                    default:
                        $baseUrlKb = "https://servicemap.disit.org/WebAppGrafo/api/v1/";
                        if (isset($_SESSION['orgKbUrl'])) {
                            $baseUrlKb = $_SESSION['orgKbUrl'];
                        }
                        if (isset($_SESSION['orgGpsCentreLatLng'])) {
                            $orgGpsCentreLatLng = $_SESSION['orgGpsCentreLatLng'];
                            $orgGpsLat = trim(explode(",", $orgGpsCentreLatLng)[0]);
                            $orgGpsLng = trim(explode(",", $orgGpsCentreLatLng)[1]);
                        } else {
                            // Se è di organizzazione "Other" o nessuna dà le coordinate del centro di Firenze di default
                            $orgGpsLat = "43.769789";
                            $orgGpsLng = "11.255694";
                        }
                        if (isset($_SESSION['orgZoomLevel'])) {
                            $orgZoomLevel = $_SESSION['orgZoomLevel'];
                        }
                    //    $myQuery = $baseUrlKb . "?selection=41.47154438707647;6.459960937499999;45.182036837015886;15.595092773437498&categories=" . $selectedRow['sub_nature'] . "&maxResults=200&format=json";
                        $myQuery = $baseUrlKb . "?selection=" . ($orgGpsLat-0.125) . ";" . ($orgGpsLng-0.25) . ";" . ($orgGpsLat+0.125) .";". ($orgGpsLng+0.25) . "&categories=" . $selectedRow['sub_nature'] . "&maxResults=200&format=json";
                        $myDesc = $selectedRow['sub_nature'];
                        $myQueryType = "Default";
                        break;
                }

                $high_level_type = $selectedRow['high_level_type'];
                $nature = $selectedRow['nature'];
                $sub_nature = $selectedRow['sub_nature'];

                if ($sub_nature != null) {
                    $query = "SELECT * FROM Dashboard.DefaultNatureColors WHERE nature = '" . escapeForSQL($nature, $link) . "' AND sub_nature = '" . escapeForSQL($sub_nature, $link) . "';";
                } else {
                    $query = "SELECT * FROM Dashboard.DefaultNatureColors WHERE nature = '" . escapeForSQL($nature, $link) . "';";
                }
                $result = mysqli_query($link, $query);

                if ($result) {
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_array($result)) {
                            if ($row['defaultColor'] != null && $row['defaultColor'] !== "") {
                                $newMapPinColor = $row['defaultColor'];
                            }
                        }
                    }
                }

                $newQueryObj = ["color1" => $defaultColors1[$count%7],
                    "color2" => $defaultColors2[$count%7],
                    "defaultOption" => false,
                    "desc" => $myDesc,
                    "display" => "pins",
                    "high_level_type" => $high_level_type,
                    "nature" => $nature,
                    "sub_nature" => $sub_nature,
                    "iconText" => $myIconText,
                    "newMapPinColor" => $newMapPinColor,
                 //   "mapPinIcon" => $mapPinIcon,
                    //La selection è di default, tanto poi il widget la sovrascrive con quella dell'area visibile.
                    "query" => $myQuery,
                    "queryType" => $myQueryType,
                    "symbolMode" => "auto",
                    "targets" => "[]",];

                array_push($selectorParameters->queries, $newQueryObj);
                $count++;
            }

            $this->startParams->parameters = json_encode($selectorParameters);
            $this->startParams->size_rows = ($count + 1) * $scaleFactor;
            $this->startParams->title_w = "Selector";
        }
        
        return $this->startParams;
    }
}
