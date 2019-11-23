<?php
class Logger extends DBMS{

    var $message;
    var $no_of_rec_affected;
    var $table_name;
    var $sync_id;
    var $user_id;
    var $log_table_name="log_data";
    
    function  __construct($dbUser, $dbPass, $dbHost, $dbName) {
        parent::__construct($dbUser, $dbPass, $dbHost, $dbName);
    }

    function logSettings($table_name,$sync_id,$user_id)
    {
        $this->table_name=$table_name;
        $this->sync_id=$sync_id;
        $this->user_id=$user_id;
    }
    function logError($message,$no_of_rec_affected)
    {
        $this->message=$message;
        $this->no_of_rec_affected=$no_of_rec_affected;
        $this->log();
    }

    function logUpdate($message,$no_of_rec_affected)
    {
        $this->message=$message;
        $this->no_of_rec_affected=$no_of_rec_affected;
        $this->log();
    }
    function logInsert($message,$no_of_rec_affected)
    {
        $this->message=$message;
        $this->no_of_rec_affected=$no_of_rec_affected;
        $this->log();
    }
    function logDelete($message,$no_of_rec_affected)
    {
        $this->message=$message;
        $this->no_of_rec_affected=$no_of_rec_affected;
        $this->log();
    }

    function log()
    {
        $data=array();
        $data['message']=$this->message;
        $data['no_of_rec_affected']=$this->no_of_rec_affected;
        $data['table_name']=  $this->table_name;
        $data['sync_property_id']=  $this->sync_id;
        $data['user_id']=  $this->user_id;
         if(!$this->isTableExist($this->log_table_name))
         {
             $sql= "CREATE TABLE `".$this->log_table_name."` (
              `id` int(11) NOT NULL auto_increment,
              `message` varchar(255) default NULL,
              `no_of_rec_affected` int(10) default NULL,
              `table_name` varchar(255) NOT NULL,
              `sync_property_id` int(20) NOT NULL,
              `user_id` int(11) NOT NULL,
              `sync_added_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
              PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";

              
            /* $data_dict_columns=array("message","no_of_rec_affected","table_name","sync_property_id","user_id");
              $extra_fields[]= array('Type'=>"int(11)",'Field'=> "id",'Null'=> "NO",'Extra'=>"auto_increment",'Key'=>"PRI");
              $extra_fields[]= array('Type'=>"timestamp",'Field'=> "sync_added_date",'Null'=> "NO",'Extra'=>"",'Key'=>"");

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
              $this->createTable("data_dict", $data_dict_fields,$extra_fields);*/
           $this->sqlExecute($sql);
        }
        
            $this->insertData($this->log_table_name, $data, "Log generated");
    }

}

?>
