<?php
            require_once  "dbms.php";
            require_once 'datadefination.php';
            require_once 'dbsync.php';
            require_once 'logger.php';
            $dbDef = new DBSync();
            echo "<a href='?action=noc'>Sync to NOC</a> || ";
            echo "<a href='?action=property'>Sync to PROPERTY</a>";

            $action = isset($_GET['action']) ? $_GET['action'] : "";
          /*  $property_info=array();
            $property_info[]=array("user_name"=>"root","password"=>"","host_name"=>"localhost","database_name"=>"db_propertyinnflicks","property_id"=>"2");
            $property_info[]=array("user_name"=>"property","password"=>"Duwe13r","host_name"=>"192.168.1.166","database_name"=>"db_propertyinnflicks","property_id"=>"1");*/

//  $property_ids = array('1' => 'forum','2'=>'forum2');
            //$property_ids

            switch ($action) {
                case "noc":
                    echo "<div class='property_head'>Property  to  NOC</div>";
                   //foreach ($property_info as $property) {
                       // $dbDef->connectproperty($userName = $property['user_name'], $password = $property['password'], $hostName = $property['host_name'], $dbName=$property['database_name']);
                    $dbDef->connectnoc($userName = "property", $password = "Duwe13r", $hostName = "192.168.1.166", $dbName = "db_propertyinnflicks");
                    $dbDef->connectnoc($userName = "noc", $password = "cBbwy24", $hostName = "192.168.1.41", $dbName = "db_nocinnflicks");
                        echo "<div class='sync_head'>Sync Started Property Id: " . $property['property_id']. "</div>";
                       // $dbDef->synchronize($property['property_id']);
                        $dbDef->synchronize(1);
                 //  }
                    break;
                case "property";
                    echo "<div class='property_head'>NOC  to  Property</div>";
                   //  foreach ($property_info as $property) {
                        $dbDef->connectproperty($userName = $property['user_name'], $password = $property['password'], $hostName = $property['host_name'], $dbName=$property['database_name']);
                        $dbDef->connectnoc($userName = "noc", $password = "cBbwy24", $hostName = "192.168.1.41", $dbName = "db_nocinnflicks");
                        /*/
                        /*$dbDef->connectproperty($userName = "root", $password = "", $hostName = "localhost", $dbName='forum3');
                        $dbDef->connectnoc($userName = "root", $password = "", $hostName = "localhost", $dbName = "forum_5");
                        /**/
                        echo "<div class='sync_head'>Sync Started Property Id: " . $property['property_id']. "</div>";
                        $dbDef->sync_noc_to_property($property['property_id']);

                        $db=new DBMS("", $dbPass, $dbHost, $dbName);
                 //   }
                    break;
            }
            /*Back Up*/
//            switch ($action) {
//                case "noc":
//                    echo "<div class='property_head'>Property  to  NOC</div>";
//                   //foreach ($property_ids as $property_id => $property_dbName) {
//                        $dbDef->connectproperty($userName = "property", $password = "Duwe13r", $hostName = "192.168.1.166", $dbName='db_propertyinnflicks');
//                        $dbDef->connectnoc($userName = "noc", $password = "cBbwy24", $hostName = "192.168.1.41", $dbName = "db_nocinnflicks");
//                        /*/
//                        $dbDef->connectproperty($userName = "root", $password = "", $hostName = "localhost", $dbName='forum3');
//                        $dbDef->connectnoc($userName = "root", $password = "", $hostName = "localhost", $dbName = "forum_5");
//                        /*
//                         */
//                        echo "<div class='sync_head'>Sync Started Property Id: 1 " . $property_dbName . "</div>";
//                        $dbDef->synchronize(1);
//                   //}
//                    break;
//                case "property";
//                    echo "<div class='property_head'>NOC  to  Property</div>";
//                     //$dbDef->connectnoc($userName, $password, $hostName, $dbName)();
//                    //$dbDef->connectproperty($userName, $password, $hostName, $dbName);
//                    //foreach ($property_ids as $property_id => $property_dbName) {
//                        echo "Reverse Sync Started Property Id:" . $property_id . "-> " . $property_dbName . "<BR>";
//                        $dbDef->connectproperty($userName = "property", $password = "Duwe13r", $hostName = "192.168.1.166", $dbName='db_propertyinnflicks');
//                        $dbDef->connectnoc($userName = "noc", $password = "cBbwy24", $hostName = "192.168.1.41", $dbName = "db_nocinnflicks");
//                        /*/
//                        /*$dbDef->connectproperty($userName = "root", $password = "", $hostName = "localhost", $dbName='forum3');
//                        $dbDef->connectnoc($userName = "root", $password = "", $hostName = "localhost", $dbName = "forum_5");
//                        /**/
//                        echo "<div class='sync_head'>Sync Started Property Id: " . $property_id . "-> " . $property_dbName . "</div>";
//                        $dbDef->sync_noc_to_property(1);
//                  // }
//                    break;
//            }
            ?>

?>
