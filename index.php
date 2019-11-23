<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>DB Synchronize</title>
        <link href="style.css" media="screen" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="jquery/jquery-1.4.2.min.js"></script>
        <script>
            $("document").ready(function(){
                //alert("this is submit");
                $("#prop_to_noc").click(function(){
                    $("#show_msg").html("Loading.... please wait...");
                    $.post("dbsync_tonoc_ajax.php", $("#form1").serialize(), function(data){
                        $("#show_msg").html(data);
                    });
                });
                
                $("#noc_to_prop").click(function(){
                    $("#show_msg").html("Loading.... please wait...");
                    $.post("dbsync_toprop_ajax.php", $("#form1").serialize(), function(data){
                        $("#show_msg").html(data);
                    });
                });
            });
        </script>
    </head>
    <body>
        <!-- /*$dbDef->connectproperty($userName = "property", $password = "Duwe13r", $hostName = "192.168.1.166", $dbName = "db_propertyinnflicks");
    $dbDef->connectnoc($userName = "noc", $password = "cBbwy24", $hostName = "192.168.1.41", $dbName = "db_nocinnflicks");*/ -->
        <form id="form1" method="post" action="dbsync_tonoc_ajax.php">
               <table>
                   <tr>
                       <td align="center">Property Server</td>
                       <td align="Center">NOC Server</td>
                   </tr>
                   <tr>
                       <td>
                         <table width="400" border="1">
                            <tr>
                              <td>Database Name</td>
                              <td><input type="text" name="prop_db_name" id="prop_db_name" /></td>
                            </tr>
                            <tr>
                              <td>User name</td>
                              <td><input type="text" name="prop_db_username" id="prop_db_username" /></td>
                            </tr>
                            <tr>
                              <td>Password</td>
                              <td><input type="text" name="prop_db_password" id="prop_db_pawssword" /></td>
                            </tr>
                            <tr>
                              <td>Connection String</td>
                              <td><input type="text" name="prop_db_ipaddress" id="prop_db_ipaddress" /></td>
                            </tr>
                            <tr>
                              <td>Sync Property id</td>
                              <td><input type="text" name="prop_sync_id" id="prop_sync_id" /></td>
                            </tr>
                         </table>
                       </td>
                       <td>
                         <table width="400" border="1">
                            <tr>
                              <td>Database Name</td>
                              <td><input type="text" name="noc_db_name" id="noc_db_name" /></td>
                            </tr>
                            <tr>
                              <td>User name</td>
                              <td><input type="text" name="noc_db_username" id="noc_db_username" /></td>
                            </tr>
                            <tr>
                              <td>Password</td>
                              <td><input type="text" name="noc_db_password" id="noc_db_pawssword" /></td>
                            </tr>
                            <tr>
                              <td>Connection String</td>
                              <td><input type="text" name="noc_db_ipaddress" id="noc_db_ipaddress" /></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                              <td>&nbsp;</td>
                            </tr>
                         </table>
                       </td>
                   </tr>
                   <tr>
                       <td> <input type="button" id="prop_to_noc" name="prop_to_noc" value="Start Sync to NOC" /></td>
                       <td> <input type="button" id="noc_to_prop" name="noc_to_prop" value="Start Sync to Property" /></td>
                   </tr>
               </table>
           </form>
    <div id="show_msg"></div>
</body>
</html>