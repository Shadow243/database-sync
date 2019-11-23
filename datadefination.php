<?php
class DataDefination extends DBMS {
    var $logger;
    function __construct($dbUser, $dbPass, $dbHost, $dbName) {
        $this->logger=new Logger($dbUser, $dbPass, $dbHost, $dbName);
        parent::__construct($dbUser, $dbPass, $dbHost, $dbName);
    }

    function get_all_tables_in_db() {
        $result = $this->sqlExecute('SHOW TABLES');
        $tables=array();
        foreach ($result as $table)
        {
            foreach ($table as $t)
            {
               $tables[]= $t;
            }
        }
       
        return($tables);
    }

    function get_table_details($table_name) {
        $result = $this->sqlExecute('DESCRIBE ' . $table_name);
        return($result);
    }

    function get_table_key() {
        $all_tables = $this->get_all_tables_in_db();
        $keys = array();

        foreach ($all_tables as $table) {
            $columns = $this->get_table_details($table);
            foreach ($columns as $cols) {
                if ($cols['Key'] == "PRI") {
                    $keys[$table] = $cols['Field'];
                    break;
                }
            }
        }
//        /var_dump($keys);
        return($keys);
    }
	
    function get_column_field($columns)
    {
        $fields=array();
        foreach ($columns as $cols) {
            $fields[] = $cols['Field'];
        }
        return($fields);
    }

    function remove_extra_fields($fields,$extra_fields)
    {
        foreach($fields as $field)
        {

            if(!in_array($field,$extra_fields,false))
            {
                unset ($field);
            }
        }
        //var_dump($fields);
        return($fields);
    }

    function isTableExist($table_name) {
        $allTable = array();
        $allTable = $this->get_all_tables_in_db();

        if (!count($allTable)) {
            return(false);
        }
        if (in_array($table_name, $allTable, false)) {
           return(true);
        }
        return(false);
    }

    function print_table_details() {
        $dataBase = array();
        $tables = $this->get_all_tables_in_db();
        foreach ($tables as $table) {
            echo "<hr>Data for Table: ";
            foreach ($table as $t)
                echo $table_name = $t;
            echo "<HR>";
            $columns = $this->get_table_details($table_name);
        }
        echo "</pre>";
    }
	
     function create_data_dictionary_table()
     {
         
         if(!$this->isTableExist("data_dict"))
         {
              $data_dict_columns=array("dd_Field","dd_Type","dd_Null","dd_Key","dd_Default","dd_Extra");
              $extra_fields[]= array('Type'=>"int(20)",'Field'=> "dict_id",'Null'=> "NO",'Extra'=>"auto_increment",'Key'=>"PRI");
              $extra_fields[]= array('Type'=>"varchar(30)",'Field'=> "table_name",'Null'=> "NO",'Extra'=>"",'Key'=>"");
              $extra_fields[]= array('Type'=>"int(20)",'Field'=> "sync_property_id",'Null'=> "NO",'Extra'=>"",'Key'=>"");
              $extra_fields[]= array('Type'=>"timestamp",'Field'=> "sync_added_date",'Null'=> "NO",'Extra'=>"default CURRENT_TIMESTAMP",'Key'=>"");
              $data_dict_fields=array();
              foreach($data_dict_columns as $col_name)
              {
                  $new_field['Type']="varchar(50)";
                  $new_field['Field']=$col_name;
                  $new_field['Null']="NO";
                  $new_field['Extra']="";
                  $new_field['Key']="";
                  $data_dict_fields[]=$new_field;
              }
              $this->createTable("data_dict", $data_dict_fields,$extra_fields);
         }
     }

     function insert_table_info_Data_dictionary($fields)
     {
        $field=array();
        $tables = $this->get_all_tables_in_db();
          foreach ($tables as $table)
          {
              $columns = $this->get_table_details($table);
              $flds=$this->get_data_dict_fields($columns);
              foreach($flds as $cols)
              {
                  $cols['table_name']=$table;
                  $cols['sync_property_id']=$sync_property_id;
                  if(!$this->isDuplicate("data_dict", $cols))
                  {
                    echo "<br>".$this->insertData("data_dict", $cols, "Data Inserted data dictionary");
                  }
              }
          }       
    }

    function get_table_defination($table_name,$condition="")
    {
        $sql="Select dd_Field as `Field`, dd_Type as `Type`, dd_Null as `Null`, dd_Key as `Key`, dd_Default as `Default`, dd_Extra as `Extra` from data_dict where table_name='".$table_name."' ".$condition;
        //var_dump($this->sqlExecute($sql));
        return($this->sqlExecute($sql));
    }

    function get_All_Tables_from_Data_dict($property_id)
    {
        $sql="Select distinct table_name from data_dict where sync_property_id=".$property_id;
        //var_dump($this->sqlExecute($sql));
        $results=$this->sqlExecute($sql);
        $tables=array();
        foreach($results as $result)
        {
            foreach ($result as $table)
            {
                $tables[]=$table;
            }
        }
        return($tables);
        
    }

    function get_data_dict_fields($cols)
    {
        //echo "Field info";
       // var_dump($cols);
        $flds=array();
        foreach($cols as $col)
        {
            $new_col=array();
            foreach($col as $key=>$val)
                $new_col['dd_'.$key]=$val;
            $flds[]=$new_col;
        }
        //var_dump($flds);
        return($flds);
    }
	
}
?>