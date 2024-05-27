<?php
function exportMysqlToCsv($sql_query,$filename = 'export.csv',$mode="",$mode_query="")
{
    $con = mysql_connect(DB_SERVER,DB_USER,DB_PASS);
    mysql_select_db(DB_NAME);
    $csv_terminated = "\n";
    $csv_separator = ",";
    $csv_enclosed = '';
    $csv_escaped = "\\";
//    $sql_query = "select * from $table";
 
    // Gets the data from the database
//    exit($sql_query);
    $result = mysql_query($sql_query);
    $fields_cnt = mysql_num_fields($result);
 
 
    $schema_insert = '';
 
    for ($i = 0; $i < $fields_cnt; $i++)
    {
        $l = $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed,
            stripslashes(mysql_field_name($result, $i))) . $csv_enclosed;
        $schema_insert .= $l;
        $schema_insert .= $csv_separator;
    } // end for
 
    $out = trim(substr($schema_insert, 0, -1));
    $out .= $csv_terminated;
 
    // Format the data
    while ($row = mysql_fetch_array($result))
    {
    	//print_r($row);echo"<br>";
        $schema_insert = '';
        for ($j = 0; $j < $fields_cnt; $j++)
        {
        $schema_insert .= $csv_enclosed . 
            str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, str_replace(',','.',$row[mysql_field_name($result,$j)])) . $csv_enclosed; //menghilangkan karakter , pada field 140919
            /*if ($row[$j] == '0' || $row[$j] != '')
            {
 
                if ($csv_enclosed == '')
                {
                    $schema_insert .= $row[$j];
                } else
                {
                    $schema_insert .= $csv_enclosed . 
					str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $row[mysql_field_name($result,$j)]) . $csv_enclosed;
                }
            } 
            else
            {
                $schema_insert .= '';//echo "sini".$row[$j];
            }*/
 
            if ($j < $fields_cnt - 1)
            {
                $schema_insert .= $csv_separator;
            }
        } // end for
 
        $out .= $schema_insert;
        $out .= $csv_terminated;
    } // end while
    
    if(isset($mode) && !empty($mode)){
        if($mode == "update"){
            $q_update = $mode_query;
            $r_up = mysql_query($q_update);
        }
    }
    
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Length: " . strlen($out));
    // Output to browser with appropriate mime type, you choose ;)
    header("Content-type: text/x-csv");
    //header("Content-type: text/csv");
    //header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=$filename");
    echo $out;
    exit;
 
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

