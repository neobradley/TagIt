<?php

  function db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE) {

      $link = mysql_pconnect($server, $username, $password) or die(mysql_error());


    if ($link) mysql_select_db($database);

    return $link;
  }

  function db_close($link) {

    return mysql_close($link);
  }
  
  function db_column($table, $link){
	$query = 'SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`="'.DB_NAME.'" AND `TABLE_NAME`="'.$table.'";';
	$query = db_query($query, $link);
	
	$columns = array();
	while($result = db_fetch_array($query)){
		array_push($columns, $result['COLUMN_NAME']);
	}
	
	return $columns;
  }

  function db_query($query, $link) {

    $result = mysql_query($query, $link) or die(mysql_error());

    return $result;
  }
  
  function db_query_columns($link, $table, $items, $parameters = null, $orders = null, $groups = null, $limit = null){
	
	$query = 'select';
	
	if(is_array($items)){
		foreach($items as $item){
			$query .= ' '.$item.' as '.$item.', ';
		}
		$query = substr($query, 0, -2);
	}
	else{
		$query .= ' '.$items;
	}
	
	$query .= ' from '.$table;
	
	if(isset($parameters) && !empty($parameters)){
		$query .= ' where';
		
		if(is_array($parameters)){
			foreach($parameters as $parameter){
			$query .= ' '.$parameter.' AND';
			}
		$query = substr($query, 0, -4);
		}
		else{
		
			$query .= ' '.$parameters;
		}
	}
	
	if(isset($orders) && !empty($orders)){
		$query .= ' order by';
		
		if(is_array($orders)){
			foreach($orders as $order){
			$query .= ' '.$order.', ';
			}
		$query = substr($query, 0, -2);
		}
		else{
			$query .= ' '.$orders;
		}
	}
	
	if(isset($groups) && !empty($groups)){
		$query .= ' group by';
		
		if(is_array($groups)){
			foreach($groups as $group){
			$query .= ' '.$group.', ';
			}
		$query = substr($query, 0, -2);
		}
		else{
			$query .= ' '.$groups;
		}
	}
	
	if(isset($limit) && !empty($limit)){
		$query .= ' '.$limit;
	}
	
	$result = mysql_query($query, $link) or die(mysql_error());
	return $result;
  }
  
  function db_query_all($link, $table, $parameters = null, $orders = null, $groups = null, $limit = null){
	
	$items = db_column($table, $link);
	
	$query = 'select';
	
	if(is_array($items)){
		foreach($items as $item){
			$query .= ' '.$item.' as '.$item.', ';
		}
		$query = substr($query, 0, -2);
	}
	else{
		$query .= ' '.$items;
	}
	
	$query .= ' from '.$table;
	
	if(isset($parameters) && !empty($parameters)){
		$query .= ' where';
		
		if(is_array($parameters)){
			foreach($parameters as $parameter){
			$query .= ' '.$parameter.' AND';
			}
		$query = substr($query, 0, -4);
		}
		else{
		
			$query .= ' '.$parameters;
		}
	}
	
	if(isset($orders) && !empty($orders)){
		$query .= ' order by';
		
		if(is_array($orders)){
			foreach($orders as $order){
			$query .= ' '.$order.', ';
			}
		$query = substr($query, 0, -2);
		}
		else{
			$query .= ' '.$orders;
		}
	}
	
	if(isset($groups) && !empty($groups)){
		$query .= ' group by';
		
		if(is_array($groups)){
			foreach($groups as $group){
			$query .= ' '.$group.', ';
			}
		$query = substr($query, 0, -2);
		}
		else{
			$query .= ' '.$groups;
		}
	}
	
	if(isset($limit) && !empty($limit)){
		$query .= ' '.$limit;
	}
	
	$result = mysql_query($query, $link) or die(mysql_error());
	return $result;
  }

  function db_perform($table, $data, $action = 'insert', $parameters = '', $link) {
    reset($data);
    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= 'now(), ';
            break;
          case 'null':
            $query .= 'null, ';
            break;
          default:
            $query .= '\'' . db_input($value, $link) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'update ' . $table . ' set ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'null':
            $query .= $columns .= ' = null, ';
            break;
          default:
            $query .= $columns . ' = \'' . db_input($value, $link) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }
    
    return $query;
  }

  function db_fetch_array($db_query) {
    return mysql_fetch_array($db_query, MYSQL_ASSOC);
  }

  function db_num_rows($db_query) {
    return mysql_num_rows($db_query);
  }

  function db_free_result($db_query) {
    return mysql_free_result($db_query);
  }

  function db_fetch_fields($db_query) {
    return mysql_fetch_field($db_query);
  }

  function db_output($string) {
    return htmlspecialchars($string);
  }

  function db_input($string, $link) {

    if (function_exists('mysql_real_escape_string')) {
      return mysql_real_escape_string($string, $link);
    } elseif (function_exists('mysql_escape_string')) {
      return mysql_escape_string($string);
    }

    return addslashes($string);
  }
?>
