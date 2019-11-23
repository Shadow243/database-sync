<?php
class DBSync {

    var $property;
    var $noc;
    var $keys;
    var $noc_mysql_config="noc_mysql_config";
    function connectproperty($userName, $password, $hostName, $dbName) {
        //echo"Connecting to :" . $userName . " With Password: " . $password . " Host Name: " . $hostName . " Database Name: " . $dbName;
        $this->property = new DataDefination($userName, $password, $hostName, $dbName);
       
        //echo "Connected";
    }

    function connectnoc($userName, $password, $hostName, $dbName) {
       // echo"Connecting to :" . $userName . " With Password: " . $password . " Host Name: " . $hostName . " Database Name: " . $dbName;
        $this->noc = new DataDefination($userName, $password, $hostName, $dbName);
        ///echo "Connected";
    }

    function print_all() {
        echo "<hr>property<hr>";

        $this->property->print_table_details();

        echo "<hr>noc<hr>";

        $this->noc->print_table_details();
    }

    function print_c() {
        $this->keys = $this->property->get_table_key();
    }
  
   function sync_property_to_noc($property_id) {
        try {
            
            $dataBase = array();
            /*
             * Create DataDict table to NOC
             *
             */
            if(!$this->noc->isTableExist("data_dict"))
             {
                  $data_dict_columns=array("dd_Field","dd_Type","dd_Null","dd_Key","dd_Default","dd_Extra");
                  $extra_fields[]= array('Type'=>"int(20)",'Field'=> "dict_id",'Null'=> "NO",'Extra'=>"auto_increment",'Key'=>"PRI");
                  $extra_fields[]= array('Type'=>"varchar(255)",'Field'=> "table_name",'Null'=> "NO",'Extra'=>"",'Key'=>"");
                  $extra_fields[]= array('Type'=>"int(20)",'Field'=> "sync_property_id",'Null'=> "NO",'Extra'=>"",'Key'=>"");
                  $extra_fields[]= array('Type'=>"timestamp",'Field'=> "sync_added_date",'Null'=> "NO",'Extra'=>"default CURRENT_TIMESTAMP",'Key'=>"");
                  $data_dict_fields=array();
                  foreach($data_dict_columns as $col_name)
                  {
                      $new_field['Type']="varchar(255)";
                      $new_field['Field']=$col_name;
                      $new_field['Null']="NO";
                      $new_field['Extra']="";
                      $new_field['Key']="";
                      $data_dict_fields[]=$new_field;
                  }
                  $this->noc->createTable("data_dict", $data_dict_fields,$extra_fields);
            }

            /*
             * Fetch Primary Keys..
             */
            $this->keys = $this->property->get_table_key();
            /*
             * Fetch All Tables from Database.
             */
            $tables = $this->property->get_all_tables_in_db();
            
            $data_dictionary=array();
            foreach ($tables as $table)
            {
                /*
                 * Print Table Name:
                 */
                if($table=="data_dict" || $table==$this->noc_mysql_config)
                    continue;
                
                echo "<div class='table_head'> Data for Table: ".$table."</div>";
                /*
                 * Fetch each tables Columns Details. With DESCRIBE..
                 */
                $property_table_columns = $this->property->get_table_details($table);
                /*
                 * Add Table information in datadictionary
                 */
               // $data_dictionary[][$table]=$property_table_columns;
                $extra_fields = array();
                /*
                 * Create extra fields to identifying each property.
                 */
                $extra_fields[]= array('Type'=>"int(20)",'Field'=> "sync_property_id",'Null'=> "NO",'Extra'=>"",'Key'=>"");
                $extra_fields[]= array('Type'=>"int(20)",'Field'=> "sync_table_id",'Null'=> "NO",'Extra'=>"auto_increment",'Key'=>"PRI");
                $extra_fields[]= array('Type'=>"timestamp",'Field'=> "sync_added_date",'Null'=> "NO",'Extra'=>"default CURRENT_TIMESTAMP",'Key'=>"");
                /*
                 * Check if table doesnot exist Create Table;
                 */
                if (!$this->noc->isTableExist($table)) {
                    $this->noc->createTable($table, $property_table_columns, $extra_fields);
                }
                /*
                 * Create Column information from columns name
                 */
                $fields = $this->property->get_column_field($property_table_columns);
                /*
                 * Get Tables Primary keys;
                 */
                $keys = $this->property->get_table_key($property_table_columns);
                /*
                 * Get all data from Property table
                 */
                $property_table_rows = $this->property->getTableData($table, $fields, $condition = "", $order_by = "");
                $updated_rows=0;
                $inserted_rows=0;
                echo "<ul class='ul_msg'>";
                foreach ($property_table_rows as $row) {
                    $row['sync_property_id'] = $property_id;
                    /*
                     * Check For Duplicate data
                     */
                    if (!$this->noc->isDuplicate($table, $row, "")) {
                        /*
                         * If Primary key Value is present then Edit the data else Insert the data...and print the result
                         */
                        if ($this->noc->isKeyPresent($table, $keys[$table], $row[$keys[$table]], " AND sync_property_id=" . $property_id)) {
                            echo $this->noc->editData($table, $row, $keys[$table] . "=" . $row[$keys[$table]] . " AND sync_property_id=" . $property_id, "<ol>Row changed in table: <strong>".$table."</strong> with ".$keys[$table] . "=" . $row[$keys[$table]]."</ol>");
                            $updated_rows++;
                        } else {
                            echo $this->noc->insertData($table, $row, "<ol>New row inserted in table: <strong>".$table."</strong> with ".$keys[$table] . "=" . $row[$keys[$table]]."</ol>" );
                            $inserted_rows++;
                        }
                    }
                }
                /*
                 * Fetch all the data from the NOC...to check if data is deleted from property then delete from Noc also
                 */
                $noc_table_rows=$this->noc->getTableData($table, $this->noc->get_column_field($property_table_columns),"Where sync_property_id=".$property_id);
                $deleted_row=0;
                /*
                 * Check the each row for duplicate.. if duplicate is not found Delete the data.
                 */
                foreach ($noc_table_rows as $row) {
                    if (!$this->property->isDuplicate($table, $row, "")) {
                        if(!$this->property->isKeyPresent($table, $keys[$table],$row[$keys[$table]]))
                        {
                         echo $this->noc->deleteData($table, $keys[$table]."=".$row[$keys[$table]], "<ol>Data Deleted from table: <strong>".$table."</strong> with ".$keys[$table] . "=" . $row[$keys[$table]]."</ol>");
                         $deleted_row++;
                        }
                    }
                }
                echo "</ul>";

                //$this->noc->logger->logSettings($table,$property_id,1);
                
                if($updated_rows>0)
                {
                    echo "<div class='updated_row'> Total updated Rows: <strong>".$updated_rows."</strong></div>";
                   // $this->noc->logger->logUpdate("Rows Updated", $updated_rows);
                }
                if($inserted_rows>0)
                {
                    echo "<div class='inserted_row'> Total new Rows Added: <strong>".$inserted_rows."</strong></div>";
                   // $this->noc->logger->logInsert("Rows inserted", $inserted_rows);
                }
                if($deleted_row>0)
                {
                    echo "<div class='deleted_row'> Total Old rows DELETED: <strong>".$deleted_row."</strong></div>";
                    //$this->noc->logger->logDelete("Rows Deleted", $deleted_row);
                }
                if($deleted_row==0 && $inserted_rows==0 && $updated_rows==0)
                    echo "<div class='inserted_row'>Data in Sync...</div>";
                /*
                * Insert Table Information to Data Dictionary ;
                */
                  $dict_flds=array();
                  $dict_flds=$this->property->get_data_dict_fields($property_table_columns);
                  
                  foreach($dict_flds as $dict_cols)
                  {

                      $dict_cols['table_name']=$table;
                      $dict_cols['sync_property_id']=$property_id;
                      if(!$this->noc->isDuplicate("data_dict", $dict_cols))
                      {
                           // print_r($dict_cols);
                            //echo "<br>".$this->noc->insertData("data_dict", $dict_cols, "Data Inserted data dictionary");
                            $this->noc->insertData("data_dict", $dict_cols, "Data Inserted data dictionary");
                      }
                  }
            }
           // $this->noc->create_data_dictionary($property_id);
            
        } catch (Exception $e) {
            DBMS::ShowError($e->getMessage());
        }
    }
    
   function sync_property_to_noc_Modified($noc_dbname,$noc_username,$noc_password,$noc_ipaddress,$property_id) {
        try {

            $dataBase = array();
           
            /*
             * Create DataDict table to NOC
             *
             */
            if(!$this->noc->isTableExist("data_dict"))
             {
                  $data_dict_columns=array("dd_Field","dd_Type","dd_Null","dd_Key","dd_Default","dd_Extra");
                  $extra_fields[]= array('Type'=>"int(20)",'Field'=> "dict_id",'Null'=> "NO",'Extra'=>"auto_increment",'Key'=>"PRI");
                  $extra_fields[]= array('Type'=>"varchar(255)",'Field'=> "table_name",'Null'=> "NO",'Extra'=>"",'Key'=>"");
                  $extra_fields[]= array('Type'=>"int(20)",'Field'=> "sync_property_id",'Null'=> "NO",'Extra'=>"",'Key'=>"");
                  $extra_fields[]= array('Type'=>"timestamp",'Field'=> "sync_added_date",'Null'=> "NO",'Extra'=>"default CURRENT_TIMESTAMP",'Key'=>"");
                  $data_dict_fields=array();
                  foreach($data_dict_columns as $col_name)
                  {
                      $new_field['Type']="varchar(255)";
                      $new_field['Field']=$col_name;
                      $new_field['Null']="NO";
                      $new_field['Extra']="";
                      $new_field['Key']="";
                      $data_dict_fields[]=$new_field;
                  }
                  $this->noc->createTable("data_dict", $data_dict_fields,$extra_fields);
            }

            
            if(!$this->property->isTableExist($this->noc_mysql_config))
             {
                  $noc_mysql_config_columns=array("db_username","db_password","db_host","db_name");
                  $extra_fields[]= array('Type'=>"int(20)",'Field'=> "sys_config_id",'Null'=> "NO",'Extra'=>"auto_increment",'Key'=>"PRI");
                  $extra_fields[]= array('Type'=>"int(20)",'Field'=> "sync_property_id",'Null'=> "NO",'Extra'=>"",'Key'=>"");
                  $extra_fields[]= array('Type'=>"timestamp",'Field'=> "added_date",'Null'=> "NO",'Extra'=>"default CURRENT_TIMESTAMP",'Key'=>"");
                  $noc_mysql_config_fields=array();
                  foreach($noc_mysql_config_columns as $col_name)
                  {
                      $new_field['Type']="varchar(255)";
                      $new_field['Field']=$col_name;
                      $new_field['Null']="NO";
                      $new_field['Extra']="";
                      $new_field['Key']="";
                      $noc_mysql_config_fields[]=$new_field;
                  }
                  $this->property->createTable($this->noc_mysql_config, $noc_mysql_config_fields,$extra_fields);

            }
            
            if(!$this->property->isDuplicate($this->noc_mysql_config, $fields=array("db_username"=>$noc_username,"db_password"=>$noc_password,"db_host"=>$noc_ipaddress,"db_name"=>$noc_dbname,"sync_property_id"=>$property_id)))
            {
                $this->property->insertData($this->noc_mysql_config, $data=array("db_username"=>$noc_username,"db_password"=>$noc_password,"db_host"=>$noc_ipaddress,"db_name"=>$noc_dbname,"sync_property_id"=>$property_id), "Sys data inserted");
            }

            /*
             * Fetch All Tables from Database.
             */
            //$tables = $this->property->get_all_tables_in_db();
            $tables=array('property_details','pa_contacts','pa_work_schedule','property_description','pa_ipaddresses','franchise','management_group');
            foreach ($tables as $table)
            {
                /*
                 * Print Table Name:
                 */
                if($table=="data_dict")
                    continue;

                echo "<div class='table_head'> Data for Table: ".$table."</div>";
                /*
                 * Fetch each tables Columns Details. With DESCRIBE..
                 */
                $property_table_columns = $this->property->get_table_details($table);
                /*
                 * Add Table information in datadictionary
                 */
               // $data_dictionary[][$table]=$property_table_columns;
                $extra_fields = array();
                /*
                 * Create extra fields to identifying each property.
                 */
                $extra_fields[]= array('Type'=>"int(20)",'Field'=> "sync_property_id",'Null'=> "NO",'Extra'=>"",'Key'=>"");
                $extra_fields[]= array('Type'=>"int(20)",'Field'=> "sync_table_id",'Null'=> "NO",'Extra'=>"auto_increment",'Key'=>"PRI");
                $extra_fields[]= array('Type'=>"timestamp",'Field'=> "sync_added_date",'Null'=> "NO",'Extra'=>"default CURRENT_TIMESTAMP",'Key'=>"");
                /*
                 * Check if table doesnot exist Create Table;
                 */
                if (!$this->noc->isTableExist($table)) {
                    $this->noc->createTable($table, $property_table_columns, $extra_fields);
                }
                /*
                 * Create Column information from columns name
                 */
                $fields = $this->property->get_column_field($property_table_columns);
                /*
                 * Get Tables Primary keys;
                 */
                $keys = $this->property->get_table_key($property_table_columns);
                /*
                 * Get all data from Property table
                 */
                $property_table_rows = $this->property->getTableData($table, $fields, $condition = "", $order_by = "");
                $updated_rows=0;
                $inserted_rows=0;
                echo "<ul class='ul_msg'>";
                foreach ($property_table_rows as $row) {
                    $row['sync_property_id'] = $property_id;
                    /*
                     * Check For Duplicate data
                     */
                    if (!$this->noc->isDuplicate($table, $row, "")) {
                        /*
                         * If Primary key Value is present then Edit the data else Insert the data...and print the result
                         */
                        if ($this->noc->isKeyPresent($table, $keys[$table], $row[$keys[$table]], " AND sync_property_id=" . $property_id)) {
                            echo $this->noc->editData($table, $row, $keys[$table] . "=" . $row[$keys[$table]] . " AND sync_property_id=" . $property_id, "<ol>Row changed in table: <strong>".$table."</strong> with ".$keys[$table] . "=" . $row[$keys[$table]]."</ol>");
                            $updated_rows++;
                        } else {
                            echo $this->noc->insertData($table, $row, "<ol>New row inserted in table: <strong>".$table."</strong> with ".$keys[$table] . "=" . $row[$keys[$table]]."</ol>" );
                            $inserted_rows++;
                        }
                    }
                }
                /*
                 * Fetch all the data from the NOC...to check if data is deleted from property then delete from Noc also
                 */
                $noc_table_rows=$this->noc->getTableData($table, $this->noc->get_column_field($property_table_columns),"Where sync_property_id=".$property_id);
                $deleted_row=0;
                /*
                 * Check the each row for duplicate.. if duplicate is not found Delete the data.
                 */
                foreach ($noc_table_rows as $row) {
                    if (!$this->property->isDuplicate($table, $row, "")) {
                        if(!$this->property->isKeyPresent($table, $keys[$table],$row[$keys[$table]]))
                        {
                         echo $this->noc->deleteData($table, $keys[$table]."=".$row[$keys[$table]], "<ol>Data Deleted from table: <strong>".$table."</strong> with ".$keys[$table] . "=" . $row[$keys[$table]]."</ol>");
                         $deleted_row++;
                        }
                    }
                }
                echo "</ul>";

                //$this->noc->logger->logSettings($table,$property_id,1);

                if($updated_rows>0)
                {
                    echo "<div class='updated_row'> Total updated Rows: <strong>".$updated_rows."</strong></div>";
                   // $this->noc->logger->logUpdate("Rows Updated", $updated_rows);
                }
                if($inserted_rows>0)
                {
                    echo "<div class='inserted_row'> Total new Rows Added: <strong>".$inserted_rows."</strong></div>";
                   // $this->noc->logger->logInsert("Rows inserted", $inserted_rows);
                }
                if($deleted_row>0)
                {
                    echo "<div class='deleted_row'> Total Old rows DELETED: <strong>".$deleted_row."</strong></div>";
                    //$this->noc->logger->logDelete("Rows Deleted", $deleted_row);
                }
                if($deleted_row==0 && $inserted_rows==0 && $updated_rows==0)
                    echo "<div class='inserted_row'>Data in Sync...</div>";
                /*
                * Insert Table Information to Data Dictionary ;
                */
                  $dict_flds=array();
                  $dict_flds=$this->property->get_data_dict_fields($property_table_columns);

                  foreach($dict_flds as $dict_cols)
                  {

                      $dict_cols['table_name']=$table;
                      $dict_cols['sync_property_id']=$property_id;
                      if(!$this->noc->isDuplicate("data_dict", $dict_cols))
                      {
                           // print_r($dict_cols);
                            //echo "<br>".$this->noc->insertData("data_dict", $dict_cols, "Data Inserted data dictionary");
                            $this->noc->insertData("data_dict", $dict_cols, "Data Inserted data dictionary");
                      }
                  }
            }
           // $this->noc->create_data_dictionary($property_id);

        } catch (Exception $e) {
            DBMS::ShowError($e->getMessage());
        }
    }


    function sync_noc_to_property($property_id) {
        try {

            $dataBase = array();
            /*
             *  Get table keys
             */
            $this->keys = $this->noc->get_table_key();
            /*
             *Get All Table names of NOC Server
             */
            $tables = $this->noc->get_All_Tables_from_Data_dict($property_id);
           // print_r($tables);
           // return;
            /*
             * Name Of Tables Which are Skiped
             */
            $skip_tables=array("data_dict");
            /*
             * Individual Tables
             */
            foreach ($tables as $table)
            {
                if(in_array($table, $skip_tables,false))
                    continue;
                echo "<div class='table_head'> Data for Table: ".$table."</div>";
                /*
                 * Check The Table is present in Property or not
                 */
                if (!$this->property->isTableExist($table)) {
                    /*
                     * If table is not present then Ceck the Data Dictionary for table Information
                     */
                    $pro_table=$this->noc->get_table_defination($table," AND sync_property_id=".$property_id);
                   // print_r($pro_table);
                    //echo "<hr>";
                    //continue;
                    /*
                     * If no table information found Show message.
                     */
                    if(!$pro_table)
                    {

                        Echo "<Div>Table infornmation, for table ".$table." not Found in data_dict table.Please Syncronize Property first.</div>";
                        continue;
                    }
                    /*
                     * if Table information present create table from information
                     */
                    $this->property->createTableProperty($table, $pro_table,$pro_table);
                }
                
                /*
                 * Create table information
                 */
                $extra_fields=array();
                $extra_fields[]= array('Field'=> "sync_property_id");
                $extra_fields[]= array('Field'=> "sync_table_id");
                $extra_fields[]= array('Field'=> "sync_added_date");
                /*
                 * Crete table
                 */
                $property_table_columns=$this->noc->get_table_defination($table,"AND sync_property_id=".$property_id);
                $fields = $this->noc->get_column_field($property_table_columns);
                $fields=  $this->noc->remove_extra_fields($fields, $extra_fields);
                $keys = $this->property->get_table_key($property_table_columns);

                $property_table_rows = $this->noc->getTableData($table, $fields, $condition = "WHERE sync_property_id=".$property_id, $order_by = "");
                $updated_rows=0;
                $inserted_rows=0;
                echo "<ul class='ul_msg'>";
                foreach ($property_table_rows as $row) {
                    if (!$this->property->isDuplicate($table, $row, "")) {
                        //echo "Key".$keys[$table];
                        if ($this->property->isKeyPresent($table, $keys[$table], $row[$keys[$table]],$condition="")) {
                            $updated_id=$row[$keys[$table]];
                            echo $this->property->editData($table, $row, $keys[$table] . "=" . $updated_id, "<ol>Row changed in table: <strong>".$table."</strong> with ".$keys[$table] . "=" .$updated_id."</ol>");
                            $updated_rows++;
                        } else {
                            echo $this->property->insertData($table, $row, "<ol>New row inserted in table: <strong>".$table."</strong> with ".$keys[$table] . "=" . $row[$keys[$table]]."</ol>" );
                            $inserted_rows++;
                        }
                    }
                }
                $property_table_rows=$this->property->getTableData($table, $this->property->get_column_field($property_table_columns),"");
               
                $deleted_row=0;
                foreach ($property_table_rows as $row) {
                    $row['sync_property_id']=$property_id;
                    if (!$this->noc->isDuplicate($table, $row, "")) {
                       // echo"<hr>"."Baas";
                        if(!$this->noc->isKeyPresent($table, $keys[$table],$row[$keys[$table]]))
                        {
                         echo $this->property->deleteData($table, $keys[$table]."=".$row[$keys[$table]], "<ol>Data Deleted from table: <strong>".$table."</strong> with ".$keys[$table] . "=" . $row[$keys[$table]]."</ol>");
                         $deleted_row++;
                        }
                    }
                }
                echo "</ul>";
                
                if($updated_rows>0)
                    echo "<div class='updated_row'> Total updated Rows: <strong>".$updated_rows."</strong></div>";
                if($inserted_rows>0)
                    echo "<div class='inserted_row'> Total new Rows Added: <strong>".$inserted_rows."</strong></div>";
                if($deleted_row>0)
                    echo "<div class='deleted_row'> Total Old rows DELETED: <strong>".$deleted_row."</strong></div>";

                if($deleted_row==0 && $inserted_rows==0 && $updated_rows==0)
                    echo "<div class='inserted_row'>Data in Sync...</div>";
            }
        } catch (Exception $e) {
            DBMS::ShowError($e->getMessage().$e->getTraceAsString());
        }
    }
    function sync_noc_to_property_Modified($property_id) {
        try {

            $dataBase = array();
           
            //$tables = $this->noc->get_All_Tables_from_Data_dict($property_id);
            $tables=array('property_details','pa_contacts','pa_work_schedule','property_description','pa_ipaddresses');
           // print_r($tables);
           // return;
            /*
             * Name Of Tables Which are Skiped
             */
            $skip_tables=array("data_dict");
            /*
             * Individual Tables
             */
            foreach ($tables as $table)
            {
                if(in_array($table, $skip_tables,false))
                    continue;
                echo "<div class='table_head'> Data for Table: ".$table."</div>";
                /*
                 * Check The Table is present in Property or not
                 */
                if (!$this->property->isTableExist($table)) {
                    /*
                     * If table is not present then Ceck the Data Dictionary for table Information
                     */
                    $pro_table=$this->noc->get_table_defination($table," AND sync_property_id=".$property_id);
                   // print_r($pro_table);
                    //echo "<hr>";
                    //continue;
                    /*
                     * If no table information found Show message.
                     */
                    if(!$pro_table)
                    {

                        Echo "<Div>Table infornmation, for table ".$table." not Found in data_dict table.Please Syncronize Property first.</div>";
                        continue;
                    }
                    /*
                     * if Table information present create table from information
                     */
                    $this->property->createTableProperty($table, $pro_table,$pro_table);
                }

                /*
                 * Create table information
                 */
                $extra_fields=array();
                $extra_fields[]= array('Field'=> "sync_property_id");
                $extra_fields[]= array('Field'=> "sync_table_id");
                $extra_fields[]= array('Field'=> "sync_added_date");
                /*
                 * Crete table
                 */
                $property_table_columns=$this->noc->get_table_defination($table,"AND sync_property_id=".$property_id);
                $fields = $this->noc->get_column_field($property_table_columns);
                $fields=  $this->noc->remove_extra_fields($fields, $extra_fields);
                $keys = $this->property->get_table_key($property_table_columns);

                $property_table_rows = $this->noc->getTableData($table, $fields, $condition = "WHERE sync_property_id=".$property_id, $order_by = "");
                $updated_rows=0;
                $inserted_rows=0;
                echo "<ul class='ul_msg'>";
                foreach ($property_table_rows as $row) {
                    if (!$this->property->isDuplicate($table, $row, "")) {
                        //echo "Key".$keys[$table];
                        if ($this->property->isKeyPresent($table, $keys[$table], $row[$keys[$table]],$condition="")) {
                            $updated_id=$row[$keys[$table]];
                            echo $this->property->editData($table, $row, $keys[$table] . "=" . $updated_id, "<ol>Row changed in table: <strong>".$table."</strong> with ".$keys[$table] . "=" .$updated_id."</ol>");
                            $updated_rows++;
                        } else {
                            echo $this->property->insertData($table, $row, "<ol>New row inserted in table: <strong>".$table."</strong> with ".$keys[$table] . "=" . $row[$keys[$table]]."</ol>" );
                            $inserted_rows++;
                        }
                    }
                }
                $property_table_rows=$this->property->getTableData($table, $this->property->get_column_field($property_table_columns),"");

                $deleted_row=0;
                foreach ($property_table_rows as $row) {
                    $row['sync_property_id']=$property_id;
                    if (!$this->noc->isDuplicate($table, $row, "")) {
                       // echo"<hr>"."Baas";
                        if(!$this->noc->isKeyPresent($table, $keys[$table],$row[$keys[$table]]))
                        {
                         echo $this->property->deleteData($table, $keys[$table]."=".$row[$keys[$table]], "<ol>Data Deleted from table: <strong>".$table."</strong> with ".$keys[$table] . "=" . $row[$keys[$table]]."</ol>");
                         $deleted_row++;
                        }
                    }
                }
                echo "</ul>";

                if($updated_rows>0)
                    echo "<div class='updated_row'> Total updated Rows: <strong>".$updated_rows."</strong></div>";
                if($inserted_rows>0)
                    echo "<div class='inserted_row'> Total new Rows Added: <strong>".$inserted_rows."</strong></div>";
                if($deleted_row>0)
                    echo "<div class='deleted_row'> Total Old rows DELETED: <strong>".$deleted_row."</strong></div>";

                if($deleted_row==0 && $inserted_rows==0 && $updated_rows==0)
                    echo "<div class='inserted_row'>Data in Sync...</div>";
            }
        } catch (Exception $e) {
            DBMS::ShowError($e->getMessage().$e->getTraceAsString());
        }
    }

    function synchronize($property_id) {
        $this->sync_property_to_noc($property_id);
    }
}
?>