<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>DB Synchronize</title>
        <link href="style.css" media="screen" rel="stylesheet" type="text/css" />
    </head>
    <body>
<?php
    require_once  "dbms.php";
    require_once 'datadefination.php';
    require_once 'dbsync.php';
    require_once 'logger.php';
    $dbDef = new DBSync();

    $property_dbname=$_POST['prop_db_name'];
    $property_username=$_POST['prop_db_username'];
    $property_password=$_POST['prop_db_password'];
    $property_ipaddress=$_POST['prop_db_ipaddress'];
    $property_id=$_POST['prop_sync_id'];
    
    $noc_dbname=$_POST['noc_db_name'];
    $noc_username=$_POST['noc_db_username'];
    $noc_password=$_POST['noc_db_password'];
    $noc_ipaddress=$_POST['noc_db_ipaddress'];

    echo "<div class='property_head'>NOC  to  Property</div>";
    $dbDef->connectproperty($userName =  $property_username, $password = $property_password, $hostName = $property_ipaddress, $dbName =  $property_dbname);
    $dbDef->connectnoc($userName = $noc_username, $password =$noc_password, $hostName = $noc_ipaddress, $dbName = $noc_dbname);

    /*$dbDef->connectproperty($userName = "property", $password = "Duwe13r", $hostName = "192.168.1.166", $dbName = "db_propertyinnflicks");
    $dbDef->connectnoc($userName = "noc", $password = "cBbwy24", $hostName = "192.168.1.41", $dbName = "db_nocinnflicks");*/
    echo "<div class='sync_head'>Sync Started Property Id: " . $property['property_id']. "</div>";
    $dbDef->sync_noc_to_property($property_id);
?>
</body>
</html>