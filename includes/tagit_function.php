<?php
require('/tagit_database_table.php');
require('/tagit_database_function.php');
require('/tagit_database_config.php');


$link = db_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

/* BOF PARSER */

function parseRequest($querystring){
    
    $query = $querystring;
        $vars = array();
        $second = array();
        foreach (explode('&', $query) as $pair) {
            list($key, $value) = explode('=', $pair);
            if('' == trim($value)){
                continue;
            }

            if (array_key_exists($key, $vars)) {
                if (!array_key_exists($key, $second))
                    $second[$key][] .= $vars[$key];
                $second[$key][] = $value;
            } else {
                $vars[$key] = urldecode($value);
            }
        }
        return array_merge($vars, $second);
    
}

/* EOD PARSER */

/* BOF MENU MODULE */


//returns menu table data
//$ids
//	-if set to null, returns all rows
//	-if set to array, returns rows with ids specified in the array
//	-if set to integer, returns row result with given id
//$columns
//	-if set to null, returns all columns
//	-if not set, returns default columns
//	-if set to array, returns columns specified in the array
//	-if set to string, returns only column specified in the string
function getMenu($ids = null, $columns = array("id","name","price","type")){
	global $link;
	
	$request = "";
	//$id_parameter = "";
	$parameters = array();
	
	if(isset($ids) && !empty($ids)){
	
		if(is_array($ids)){
			$parameter_id = "id IN (";
				foreach($ids as $id){
				$parameter_id .= ' '.$id.', ';
				}
			$parameter_id = substr($parameter_id, 0, -2);
			$parameter_id .= ")";
			
			array_push($parameters, $parameter_id);
		}
		else{
			$parameter_id = " id = ".$ids;
			array_push($parameters, $parameter_id);
		}
	}
	
	array_push($parameters, "status = 1");
	
	if(is_null($columns)){
		$query = db_query_all($link, TABLE_MENU, $parameters);
	}
	else{
		$query = db_query_columns($link, TABLE_MENU, $columns, $parameters);
	}
		
        $result_array = array();
	while($result = db_fetch_array($query)){
            array_push($result_array, $result);
	}
	
	return array('result'=>$result_array);
}


////returns distinct type from menu type table
//function getMenuType(){
//	global $link;
//	$query = db_query_columns($link, TABLE_MENUTYPE, "type", "status = 1");
//	
//	$request = "";
//	while($result = db_fetch_array($query)){
//		$request .= json_encode($result);
//	}
//	
//	return $request;
//}

//inserts an item into the menu table
//$name - name of menu item
//$price - price of menu item
//$type - type of menu item
//$status - set to null or is null will set menu item to default
function addMenu($name, $price, $type, $status = 1){
	global $link;
	
	$item = array('id'=>null, 'name'=>$name, 'price'=>$price, 'date_created'=>"now()", 'last_update'=>"now()", 'status'=>$status, 'type'=>$type);
	
	$query = db_perform(TABLE_MENU, $item, "insert", null, $link);
	$result = db_query($query, $link);
        if($result){
            return true;
        }
        else{
            return "Failed to add menu item";
        }
}

/* EOF MENU MODULE */

/* BOF USER MODULE */

//returns user table data
//$ids
//	-if set to null, returns all rows
//	-if set to array, returns rows with ids specified in the array
//	-if set to integer, returns row result with given id
//$columns
//	-if set to null, returns all columns
//	-if not set, returns default columns
//	-if set to array, returns columns specified in the array
//	-if set to string, returns only column specified in the string
function getUser($ids = null, $columns = array("id","email","name","mobile_number","status","current_point"), $params = null){
	global $link;
	
	$request = "";
	//$id_parameter = "";
	$parameters = array();
	
	if(isset($ids) && !empty($ids)){
	
		if(is_array($ids)){
			$parameter_id = "id IN (";
				foreach($ids as $id){
				$parameter_id .= ' '.$id.', ';
				}
			$parameter_id = substr($parameter_id, 0, -2);
			$parameter_id .= ")";
			
			array_push($parameters, $parameter_id);
		}
		else{
			$parameter_id = " id = ".$ids;
			array_push($parameters, $parameter_id);
		}
	}
	
	array_push($parameters, "status = 1");
	
	if(is_null($columns)){
		$query = db_query_all($link, TABLE_USERS, $parameters);
	}
	else{
		$query = db_query_columns($link, TABLE_USERS, $columns, $parameters);
	}
		
	$result_array = array();
	while($result = db_fetch_array($query)){
            array_push($result_array, $result);
	}
	
	return array('result'=>$result_array);
}

//inserts a user into the user table
//$email - email of user item
//$name - name of user item
//$password - password of user item
//$currentpoint - set to null or is null will set user currentpoint to default
//$status - set to null or is null will set user status to default
function addUser($email, $name, $password, $mobile_number, $currentpoint = 0, $status = 1){
	global $link;
	
        if(checkEmail($email)){
            return "Failed to add account. Duplicate email found";
        }
        else{
            $data = array('id'=>null, 'email'=>$email, 'name'=>$name, 'password'=>$password, 'mobile_number'=>$mobile_number, 'date_created'=>"now()", 'status'=>$status,'current_point'=>$currentpoint, 'last_update'=>"now()");

            $query = db_perform(TABLE_USERS, $data, "insert", null, $link);
            $result = db_query($query, $link);
            if($result){
                return true;
            }
            else{
                return "Failed to add account";
            }
        }
}

//updates user table for given $id
function updateUser($id, $email = null, $name = null, $password = null, $mobile_number = null, $currentpoint = null, $status = null){
	global $link;
	
	$data = array();
	
	$data['last_update'] = 'now()';
	
	if(isset($email) && !empty($email)){
		$data['email'] = $email;
	}
	if(isset($name) && !empty($name)){
		$data['name'] = $name;
	}
	if(isset($password) && !empty($password)){
		$data['password'] = $password;
	}
	if(isset($mobile_number) && !empty($mobile_number)){
		$data['mobile_number'] = $mobile_number;
	}
	if(isset($currentpoint) && !empty($currentpoint)){
		$data['currentpoint'] = $currentpoint;
	}
	if(isset($status) && !empty($status)){
		$data['status'] = $status;
	}
	
	$query = db_perform(TABLE_USERS, $data, "update", "id = $id", $link);
	$result = db_query($query, $link);
        if($result){
            return true;
        }
        else{
            return "Failed to update user information";
        }
}

/* EOF USER MODULE */

/* BOF FRIEND MODULE */

function getFriend($id, $status){
	global $link;
	
	$dbquery = 'select id as id, email as email, name as name, mobile_number as mobile_number from '.TABLE_USERS.' where id in (select user_res from '.TABLE_FRIENDS.' where user_req = '.$id.' and status = '.$status.')';
	$query = db_query($dbquery, $link);
	
	$request = "";
	
	$result_array = array();
	while($result = db_fetch_array($query)){
            array_push($result_array, $result);
	}
	
	return array('result'=>$result_array);
}

function getFriend2($id, $columns = array("user_res", "status"), $status){
	global $link;
	
	if(isset($columns) && !empty($columns)){
		if(isset($status)){
			$query = db_query_columns($link, TABLE_FRIENDS, $columns, "user_req = $id AND status = '".$status."'");
		}else{
			$query = db_query_columns($link, TABLE_FRIENDS, $columns, "user_req = $id", $status);
		}
	}
	else if(is_null($columns)){
		if(isset($status)){
			$query = db_query_all($link, TABLE_FRIENDS, "user_req = $id AND status = '".$status."'");
		}else{
			$query = db_query_all($link, TABLE_FRIENDS, "user_req = $id", $status);
		}
	}
	else{
		$query = db_query_columns($link, TABLE_FRIENDS, $columns, "user_req = $id", "status");
	}
	
	$result_array = array();
	while($result = db_fetch_array($query)){
            array_push($result_array, $result);
	}
	
	return array('result'=>$result_array);
}

//NOT YET TESTED
//$action
// -if set to add, sets status to 3
// -if set to accept, sets status to 4
// -if set to ignore, sets status to 5
// -if set to cancel, sets status to 6
//$id - id of requesting for friendship
//$id2 - id of being requested for friendship

//status [3:pending | 4:accepted | 5:ignore | 6:cancelled]
function updateFriend($id, $id2, $action = 'add'){
	global $link;
	
	if($action == 'add'){
            $data = array('id'=>null, $reqid, 'user_req'=>$id, 'user_res'=>$id2, 'date_req'=>'now()', 'date_res'=>'null', 'status'=>3);
            $result = db_perform(TABLE_FRIENDS, $data, "insert", null, $link);
            $result = db_query($query, $link);
            if($result){
                return true;
            }
            else{
                return "Failed to add request";
            }
	}
	else if($action == 'accept'){
            $data = array('date_res'=>'now()', 'status'=>4);
            $result = db_perform(TABLE_FRIENDS, $data, "update", "user_req = $id AND user_res = $id2 AND status NOT IN (4, 6)", $link);
            $result = db_query($query, $link);
            if($result){
                return true;
            }
            else{
                return "Failed to accept request";
            }
	}
	else if($action == 'ignore'){
            $data = array('date_res'=>'now()', 'status'=>5);
            $result = db_perform(TABLE_FRIENDS, $data, "update", "user_req = $id AND user_res = $id2 AND status NOT IN (4, 6)", $link);
            $result = db_query($query, $link);
            if($result){
                return true;
            }
            else{
                return "Failed to ignore request";
            }
	}
	else if($action == 'cancel'){
            $data = array('date_req'=>'now()', 'status'=>6);
            $result = db_perform(TABLE_FRIENDS, $data, "update", "user_req = $id AND user_res = $id2 AND status NOT IN (4, 6)", $link);
            $result = db_query($query, $link);
            if($result){
                return true;
            }
            else{
                return "Failed to cancel request";
            }
	}
}

/* EOF FRIEND MODULE */


/* BOF ACHIEVEMENT MODULE */

//returns achievement table data
//$ids
//	-if set to null, returns all rows
//	-if set to array, returns rows with ids specified in the array
//	-if set to integer, returns row result with given id
//$columns
//	-if set to null, returns all columns
//	-if not set, returns default columns
//	-if set to array, returns columns specified in the array
//	-if set to string, returns only column specified in the string

function getAchievementList($ids = null, $columns = array("id","name","description","point","quota")){
	global $link;
	
	$request = "";
	//$id_parameter = "";
	$parameters = array();
	
	if(isset($ids) && !empty($ids)){
	
		if(is_array($ids)){
			$parameter_id = "id IN (";
				foreach($ids as $id){
				$parameter_id .= ' '.$id.', ';
				}
			$parameter_id = substr($parameter_id, 0, -2);
			$parameter_id .= ")";
			
			array_push($parameters, $parameter_id);
		}
		else{
			$parameter_id = " id = ".$ids;
			array_push($parameters, $parameter_id);
		}
	}
	
	array_push($parameters, "status = 1");
	
	if(is_null($columns)){
		$query = db_query_all($link, TABLE_ACHIEVEMENTS, $parameters);
	}
	else{
		$query = db_query_columns($link, TABLE_ACHIEVEMENTS, $columns, $parameters);
	}
		
        $result_array = array();
	while($result = db_fetch_array($query)){
            array_push($result_array, $result);
	}
	
	return array('result'=>$result_array);
}

function addAchievementList($name, $description, $point, $quota = null, $status = 1){
	global $link;
	
	$data = array('id'=>null, 'name'=>$name, 'description'=>$description, 'point'=>$point, 'quota'=>"quota", 'status'=>$status,'date_created'=>"now()",'last_update'=>"now()");
	
	$query = db_perform(TABLE_ACHIEVEMENTS, $data, "insert", null, $link);
        $result = db_query($query, $link);
        if($result){
            return true;
        }
        else{
            return "Failed to add achievement item to list";
        }

}

function updateAchievementList($id, $name = null, $description = null, $point = null, $quota = null, $status = null){
    global $link;

    $data = array();

    $data['last_update'] = 'now()';

    if(isset($name) && !empty($name)){
            $data['name'] = $name;
    }
    if(isset($description) && !empty($description)){
            $data['description'] = $description;
    }
    if(isset($point) && !empty($point)){
            $data['point'] = $point;
    }
    if(isset($quota) && !empty($quota)){
            $data['quota'] = $quota;
    }
    if(isset($status) && !empty($status)){
            $data['status'] = $status;
    }

    $query = db_perform(TABLE_ACHIEVEMENTS, $data, "update", "id = $id", $link);
    $result = db_query($query, $link);
    if($result){
        return true;
    }
    else{
        return "Failed to update achievement list item";
    }
}

function getUserAchievements($id){
    global $link;

    $dbquery = 'select id as id, name as name, description as description, point as point, quota as quota FROM '.TABLE_ACHIEVEMENTS.' where id in (select achievement_id from '.TABLE_USERS_ACHIEVEMENT.' where user_id = '.$id.') and status = 1';
    $query = db_query($dbquery, $link);

    $result_array = array();
    while($result = db_fetch_array($query)){
        array_push($result_array, $result);
    }

    return array('result'=>$result_array);

}

function addUserAchievement($userid, $achievementid){
    global $link;

    $data = array('user_id'=>userid, 'achievement_id'=>$achievementid, 'date_created'=>'now()', 'last_update'=>'now()');

    $result = db_query($query, $link);
    if($result){
        return true;
    }
    else{
        return "Failed to add user achievement";
    }
	
}

/* EOF ACHIEVEMENT MODULE */

/* BOF EVENT MODULE */

//returns event table data
//$ids
//	-if set to null, returns all rows
//	-if set to array, returns rows with ids specified in the array
//	-if set to integer, returns row result with given id
//$columns
//	-if set to null, returns all columns
//	-if not set, returns default columns
//	-if set to array, returns columns specified in the array
//	-if set to string, returns only column specified in the string

function getEventList($ids = null, $columns = array("id","name","date_start","date_end","achievement_id", "point", "description", "status")){
	global $link;
	
	$request = "";
	//$id_parameter = "";
	$parameters = array();
	
	if(isset($ids) && !empty($ids)){
	
		if(is_array($ids)){
			$parameter_id = "id IN (";
				foreach($ids as $id){
				$parameter_id .= ' '.$id.', ';
				}
			$parameter_id = substr($parameter_id, 0, -2);
			$parameter_id .= ")";
			
			array_push($parameters, $parameter_id);
		}
		else{
			$parameter_id = " id = ".$ids;
			array_push($parameters, $parameter_id);
		}
	}
	
	array_push($parameters, "status = 1");
	
	if(is_null($columns)){
		$query = db_query_all($link, TABLE_EVENTS, $parameters);
	}
	else{
		$query = db_query_columns($link, TABLE_EVENTS, $columns, $parameters);
	}
	
        $result_array = array();
	while($result = db_fetch_array($query)){
            array_push($result_array, $result);
	}
	
	return array('result'=>$result_array);
}

function getEventByDate($date){
	global $link;
	
	$dbquery = 'select id as id, name as name, date_start as date_start, date_end as date_end, achievement_id as achievement_id, point as point, description as description, status as status from '.TABLE_EVENTS.' where status = 1 and date_start <= '.$date.' and date_end >= '.$date.';';
	$query = db_query($dbquery, $link);
	
	$result_array = array();
        while($result = db_fetch_array($query)){
            array_push($result_array, $result);
        }

        return array('result'=>$result_array);
}

function addEvent($name, $date_start, $date_end, $achievement_id, $point, $description, $status = 1){
	global $link;
	
	$data = array('id'=>null, 'name'=>$name, 'date_start'=>$date_start, 'date_end'=>$date_end, 'achievement_id'=>"achievement_id", 'point'=>$point,'description'=>"description", 'status'=>$status, 'date_created'=>"now()");
	
	$result = db_perform(TABLE_EVENTS, $data, "insert", null, $link);
	$result = db_query($query, $link);
        if($result){
            return true;
        }
        else{
            return "Failed to add event";
        }
}

function updateEvent($id, $name = null, $date_start = null, $date_end = null, $achievement_id = null, $point = null, $description = null, $status = null){
	global $link;
	
	$data = array();
	
	$data['last_update'] = 'now()';
	
	if(isset($name) && !empty($name)){
		$data['name'] = $name;
	}
	if(isset($date_start) && !empty($date_start)){
		$data['date_start'] = $date_start;
	}
	if(isset($date_end) && !empty($date_end)){
		$data['date_end'] = $date_end;
	}
	if(isset($achievement_id) && !empty($achievement_id)){
		$data['achievement_id'] = $achievement_id;
	}
	if(isset($point) && !empty($point)){
		$data['point'] = $point;
	}
	if(isset($description) && !empty($description)){
		$data['description'] = $description;
	}
	if(isset($status) && !empty($status)){
		$data['status'] = $status;
	}
	
	$result = db_perform(TABLE_ACHIEVEMENTS, $data, "update", "id = $id", $link);
	$result = db_query($query, $link);
        if($result){
            return true;
        }
        else{
            return "Failed to update event item";
        }
}

/* EOF EVENT MODULE */

/* BOF LOGIN MODULE */

function login($email, $password){
    global $link;
    
    $dbquery = "select * from ".TABLE_USERS." where email = '$email' and password = '$password' and status = 1";
    $query = db_query($dbquery, $link);
    
    if(db_num_rows($query)>0){
        return true;
    }
    else{
        return "Failed to login. Invalid email/password";
    }
}

function checkEmail($email){
    global $link;
    
    $dbquery = "select * from ".TABLE_USERS." where email = '".$email."'";
    $query = db_query($dbquery, $link);
    
    if(db_num_rows($query)>0){
        return true;
    }
    else{
        return false;
    }
}

/* EOF LOGIN MODULE */

/* BOF SYNC MODULE */

function sync($identifier){
    
}

/*EOF SYNC MODULE */

//$result = login("tracy.mondelipano@gmail.com", "123456");
//if($result){echo true;}else{echo "Failed to login";}

//$result = addUser("warrddwadwen.miniano15dwadwa3@gmail.com", "Warre23n", "1234356", "0927432498924", 0, 1);
//echo $result;




// echo "JSON: MENU";
// echo "<br/>";
//echo getMenu();
// echo "<br/> <br/>";
// echo "JSON: USER";
// echo "<br/>";
// echo getUser(null, null);
// echo "<br/> <br/>";
// echo "JSON: ACHIEVEMENT";
// echo "<br/>";
// echo getAchievementList(null,null);
?>