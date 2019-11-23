<?php
class DBMS {
    var $last_insert_id;
    var $db;
    var $arrs = array();
    var $dbHostName;
    var $dbUserName;
    var $dbPassword;
    var $dbName;

    function __construct($dbUser, $dbPass, $dbHost, $dbName) {
        $this->dbHostName = $dbHost;
        $this->dbUserName = $dbUser;
        $this->dbPassword = $dbPass;
        $this->dbName = $dbName;
    }

    function sqlExecute($sql) {
        $db = new PDO("mysql:host=$this->dbHostName;charset=UTF8;",$this->dbUserName,$this->dbPassword, array(PDO::ATTR_PERSISTENT=>true));
        // $db = mysql_pconnect($this->dbHostName, $this->dbUserName, $this->dbPassword) or mysql_error(mysql_errno(), "Error in DBMS." . E_USER_ERROR);
        mysql_select_db($this->dbName, $db) or die("Error: Data Base: Can not find database");
        $result = @mysql_query($sql, $db);
        if ($result) {
            $rows = array();
            while ($row = mysql_fetch_assoc($result)) {
                $rows[] = $row;
            }
            mysql_close($db);
            return($rows);
        } else {
            echo "<br>ERROR in SQL: " . @mysql_error() . "<br>SQL: " . $sql;
        }
        mysql_close($db);
    }

    function createTableExecute($sql) {
        $db = @mysql_pconnect($this->dbHostName, $this->dbUserName, $this->dbPassword) or trigger_error(mysql_errno(), "Error in DBMS." . E_USER_ERROR);
        mysql_select_db($this->dbName, $db) or die("Error:Data Base: Can not find database");
        $result = @mysql_query($sql, $db);
        if ($result) {
            $rows = array();
            while ($row = mysql_fetch_assoc($result)) {
                $rows[] = $row;
            }
            mysql_close($db);
            return($rows);
        } else {
            echo "<br>ERROR in SQL: " . @mysql_error() . "<br>SQL: " . $sql;
        }
        mysql_close($db);
    }

    /*
     * @param $tableName Table Name
     * @param $fields Array of fielname and value
     * * */

    function isDuplicate($tableName, $fields, $condition="") {
        // echo "<br>function isDuplicate:-".$this->dbName."<br>";
        $field = '';
        $qryStr = "";
        if ($fields != "*") {
            foreach ($fields as $fld => $val) {
                $fld = '`' . $fld . '`';
                if (empty($field))
                    $field.=$fld;
                else
                    $field.=", " . $fld;

                if (empty($qryStr)) {
                    $qryStr = $fld . "=" . $this->getSQLQuote($val);
                }
                else
                    $qryStr.=" and " . $fld . "=" . $this->getSQLQuote($val);
            }
        }
        else {
            $field = $fields;
        }
        $sql = "SELECT " . $field . " FROM `" . $tableName . "` WHERE " . $qryStr . " " . $condition;
        if ($this->sqlExecute($sql)) {
            return (true);
        }
        else
            return (false);
    }

    function getRowCount($tableName, $condition="") {
        $sql = "SELECT count(*) as count from " . $tableName . " " . $condition;
        $count = $this->sqlExecute($sql);
        return(number_format($count[0]['count']));
    }

    function getTableData($tableName, $fields, $condition="", $order_by="") {
        if (!$tableName)
            throw new Exception(" Error: Invalid table Name.\"$tableName\"");
        if (!$fields)
            throw new Exception("Error : Invalid Field name." . $fields);

        $field = '';
        $vals = '';

        if ($fields != "*") {
            foreach ($fields as $fld) {
                $fld = '`' . $fld . '`';

                if (!$field)
                    $field.=$fld;
                else
                    $field.="," . $fld;
            }
        }
        else {
            $field = $fields;
        }

        $sql = " SELECT " . $field . " FROM " . $tableName . " " . $condition . " " . $order_by;
        return($this->sqlExecute($sql));
    }

    /**
     * @param String table Name
     * @param Array Datas to inserted
     * @param Array Meassage
     */
    function insertData($tableName, $data, $msg) {
        $field = '';
        $vals = '';
        $fields = array();
        $db = @mysql_pconnect($this->dbHostName, $this->dbUserName, $this->dbPassword) or trigger_error(mysql_errno(), "Error in DBMS." . E_USER_ERROR);
        mysql_select_db($this->dbName, $db) or die("Error:Data Base: Can not find database");
        $fields = $this->getTableFields($tableName);

        foreach ($data as $fld => $val) {
            if (in_array($fld, $fields)) {
                $fld = '`' . $fld . '`';
                $val = $this->getSQLQuote($val);

                if (!$field)
                    $field.=$fld;
                else
                    $field.="," . $fld;

                if (!$vals)
                    $vals.=$val;
                else
                    $vals.=',' . $val;
            }
        }
        $sql = "INSERT INTO " . $tableName . " (" . $field . ") values(" . $vals . ")";
        
        $result = @mysql_query($sql, $db);
        if (!$result)
        {
            mysql_close($db);
            throw new Exception('Error: ' . @mysql_error());
        }

        else {
            $this->last_insert_id = mysql_insert_id($db);
            mysql_close($db);
            return($msg);
        }
    }  

    function isKeyPresent($table_Name, $keyName, $value, $condition="") {
       // Echo "is Key Present";
        if ($this->sqlExecute("SELECT " . $keyName . " FROM " . $table_Name . " WHERE " . $keyName . "=" . $value . " " . $condition)) {
            return(true);
        }
        return(false);
    }

    /**
     * @param string table name
     * @param int primary key ID
     */
    function deleteData($tableName, $key, $msg) {
        $sql = "DELETE FROM " . $tableName . " WHERE " . $key . "";
        $db = @mysql_pconnect($this->dbHostName, $this->dbUserName, $this->dbPassword) or trigger_error(mysql_errno(), "Error in DBMS." . E_USER_ERROR);
        mysql_select_db($this->dbName, $db) or die("Error:Data Base: Can not find database");
        $result = @mysql_query($sql, $db);
        if (!$result) {
            throw new Exception('Error: ' . @mysql_error());
            //mysql_close($db);
        } else {
            mysql_close($db);
            return($msg);
        }
    }

    /**
     * @param string table name
     * @param int primary key ID
     */
    function editData($tableName, $data, $key, $msg) {
        $setVal = '';
        $fields = array();
        $db = @mysql_pconnect($this->dbHostName, $this->dbUserName, $this->dbPassword) or trigger_error(mysql_errno(), "Error in DBMS." . E_USER_ERROR);
        mysql_select_db($this->dbName, $db) or die("Error:Data Base: Can not find database");
        $fields = $this->getTableFields($tableName);

        foreach ($data as $fld => $val) {
            if (in_array($fld, $fields)) {
                $fld = '`' . $fld . '`';
                $val = $this->getSQLQuote($val);
                if (!$setVal)
                    $setVal = $fld . '=' . $val;
                else
                    $setVal.="," . $fld . '=' . $val;
            }
        }

        $sql = "UPDATE  " . $tableName . " SET " . $setVal . " WHERE " . $key . "";

        $result = @mysql_query($sql, $db);

        if (!$result) {
            throw new Exception('Error: ' . @mysql_error()." SQL:".$sql);
        } else {
            mysql_close($db);
            return($msg);
        }
    }

    function createTable($table_name, $field_details, $extra_fields) {
        $selected_fields = "";
        //var_dump($field_details);
        foreach ($field_details as $field) {
            $type = $field['Type'];
            if (strtoupper($field['Type']) === strtoupper("timestamp")) {
                $type = "DATETIME";
            }
            if (empty($selected_fields))
                $selected_fields = "`" . $field['Field'] . "` " . $type;
            else
                $selected_fields.=", " . "`" . $field['Field'] . "` " . $type;
        }
        
        foreach ($extra_fields as $ex_field) {
            $not_null = "";
            if ($ex_field['Null'] == "NO") {
                $not_null = "NOT NULL";
            }
            $key = "";

            if ($ex_field['Key'] == "PRI") {
                $key = $ex_field['Field'];
            }

            $selected_fields.=", " . "`" . $ex_field['Field'] . "` " . $ex_field['Type'] . " " . $not_null . " " . $ex_field['Extra'];
            if (!empty($key)) {
                $selected_fields.=",  PRIMARY KEY  (`" . $key . "`)";
            }
        }
         $sql = "CREATE TABLE " . $table_name . " (" . $selected_fields . ") ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
        $db = @mysql_pconnect($this->dbHostName, $this->dbUserName, $this->dbPassword) or trigger_error(mysql_errno(), "Error in DBMS." . E_USER_ERROR);
        mysql_select_db($this->dbName, $db) or die("Error:Data Base: Can not find database");
        $result = @mysql_query($sql, $db);
        if (!$result) {
            echo "<br>ERROR in SQL: " . @mysql_error() . "<br>SQL: " . $sql;
        }
        mysql_close($db);
    }
    
    function createTableProperty($table_name, $field_details, $extra_fields) {
        $selected_fields = "";
        //var_dump($field_details);
        $key = "";
        foreach ($field_details as $field) {
            $type = $field['Type'];
            if (strtoupper($field['Type']) === strtoupper("timestamp")) {
                $type = "DATETIME";
            }
            $not_null = "";
            if ($field['Null'] == "NO") {
                $not_null = "NOT NULL";
            }
            if ($field['Key'] == "PRI") {
                $key = $field['Field'];
            }
            
            if (empty($selected_fields))
                $selected_fields = "`" . $field['Field'] . "` " . $type." ".$not_null." ".$field['Extra'];
            else
                $selected_fields.=", " . "`" . $field['Field'] . "` " . $type." ".$not_null." ".$field['Extra'];
        }

        if (!empty($key)) {
                $selected_fields.=",  PRIMARY KEY  (`" . $key . "`)";
        }
        
        //echo "PROP: ". $sql = "CREATE TABLE " . $table_name . " (" . $selected_fields . ") ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
        $sql = "CREATE TABLE " . $table_name . " (" . $selected_fields . ") ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
        $db = @mysql_pconnect($this->dbHostName, $this->dbUserName, $this->dbPassword) or trigger_error(mysql_errno(), "Error in DBMS." . E_USER_ERROR);
        mysql_select_db($this->dbName, $db) or die("Error:Data Base: Can not find database");
        $result = @mysql_query($sql, $db);
        if (!$result) {
            echo "<br>ERROR in SQL: " . @mysql_error() . "<br>SQL: " . $sql;
        }
        mysql_close($db);
    }

    function updataSettings($tableName, $post, $setId) {
        foreach ($post as $fieldName => $value) {
            $value = $this->getSQLQuote($value);
            $sql = "UPDATE  " . $tableName . " SET field_value=" . $value . " WHERE field_name='" . $fieldName . "' and setid=" . $setId;
            //echo "<br>";
            $result = @mysql_query($sql, $this->db);
            if (!$result)
                echo "Error: 4";
        }
    }

    function getSQLQuoteOLD($theValue) {
        $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

        $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

        if (!is_numeric($theValue)) {
            $theValue = "'" . $theValue . "'";
        }

        return $theValue;
    }
    
    function getSQLQuote($theValue) {
        $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

        $theValue = mysql_escape_string($theValue);

        if (!is_numeric($theValue)) {
            $theValue = "'" . $theValue . "'";
        }

        return $theValue;
    }

    
    function getLimitData($sql, $start, $rowPerPage) {

        $limit = $rowPerPage + $start;
        $sql = $sql . (empty($limit) ? "" : " LIMIT " . $limit);

        $rslt = @mysql_query($sql);

        if (!$rslt)
            return false;
        if (@mysql_data_seek($rslt, $start)) {
            while (($rowPerPage-- > 0) && ($row = @mysql_fetch_array($rslt, MYSQL_ASSOC))) {
                $rows[] = $row;
                $row;
            }
        }
        return $rows;
    }

    function TableFieldName($db, $num) {
        $header = array();
        for ($i = 0; $i < $num; $i++) {
            $header[] = mysql_field_name($db, $i);
        }
        return($header);
    }

    function getTableFields($tableName) {
        $skipField[] = 'sysdate';
        $skipField[] = 'confdate';
        $db = @mysql_pconnect($this->dbHostName, $this->dbUserName, $this->dbPassword) or trigger_error(mysql_errno(), "Error in DBMS." . E_USER_ERROR);
        mysql_select_db($this->dbName, $db) or die("Error:Data Base: Can not find database");

        $sql = " SELECT * FROM " . $tableName;
        if (!$db) {
            throw new Exception("Invalid DB Reference.", 5);
        }

        $result = @mysql_query($sql, $db);

        if (!$result) {
            throw new Exception("Error in query: <br>" . $sql . "<br>" . @mysql_error(), 1);
        }
        $num = mysql_num_fields($result);
        $fields = $this->TableFieldName($result, $num);
        $dat = array();
        foreach ($fields as $field) {
            if (!in_array($field, $skipField))
                $dat[] = $field;
        }
        mysql_close($db);
        return($dat);
    }

    static function ShowError($msg) {
        echo '<style>
                .ErrMsg{
                   border: 2px solid #ff0000;
                    text-align: center;
                    background-color:#FEE2DE;
                    margin: 10px;
                    width:300px;
                    vertical-align:middle;
                    -moz-border-radius: 3px;
                    -khtml-border-radius: 3px;
                    -webkit-border-radius: 3px;
                    border-radius: 4px;
                    font-family: Verdana, Arial, Helvetica, sans-serif;
                    font-size: 10px;
                }
             </style>';

        echo '<table  cellpadding="0" cellspacing="0" >
                <tr>
                    <td ><div class="ErrMsg">' . $msg . '</div></td>
                </tr>
             </table>';
        die();
    }

  /*  function get_all_tables_in_db() {
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
    }*/
}
?>