<?php

require('/tagit_database_table.php');
require('/tagit_database_function.php');
require('/tagit_database_config.php');


$link = db_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

/* BOF PARSER */

function parseRequest($querystring) {

    $query = $querystring;
    $vars = array();
    $second = array();
    foreach (explode('&', $query) as $pair) {
        list($key, $value) = explode('=', $pair);
        if ('' == trim($value)) {
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
function getMenu($ids = null, $columns = array("id", "name", "price", "type")) {
    global $link;

    $request = "";
    //$id_parameter = "";
    $parameters = array();

    if (isset($ids) && !empty($ids)) {

        if (is_array($ids)) {
            $parameter_id = "id IN (";
            foreach ($ids as $id) {
                $parameter_id .= ' ' . $id . ', ';
            }
            $parameter_id = substr($parameter_id, 0, -2);
            $parameter_id .= ")";

            array_push($parameters, $parameter_id);
        } else {
            $parameter_id = " id = " . $ids;
            array_push($parameters, $parameter_id);
        }
    }

    array_push($parameters, "status = 1");

    if (is_null($columns)) {
        $query = db_query_all($link, TABLE_MENU, $parameters);
    } else {
        $query = db_query_columns($link, TABLE_MENU, $columns, $parameters);
    }

    $result_array = array();
    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
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
function addMenu($name, $price, $type, $status = 1) {
    global $link;

    $item = array('id' => null, 'name' => $name, 'price' => $price, 'date_created' => "now()", 'last_update' => "now()", 'status' => $status, 'type' => $type);

    $query = db_perform(TABLE_MENU, $item, "insert", null, $link);
    $result = db_query($query, $link);
    if ($result) {
        return true;
    } else {
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
function getUser($ids = null, $columns = array("id", "email", "name", "mobile_number", "status", "current_point"), $params = null) {
    global $link;

    $request = "";
    //$id_parameter = "";
    $parameters = array();

    if (isset($ids) && !empty($ids)) {

        if (is_array($ids)) {
            $parameter_id = "id IN (";
            foreach ($ids as $id) {
                $parameter_id .= ' ' . $id . ', ';
            }
            $parameter_id = substr($parameter_id, 0, -2);
            $parameter_id .= ")";

            array_push($parameters, $parameter_id);
        } else {
            $parameter_id = " id = " . $ids;
            array_push($parameters, $parameter_id);
        }
    }

    array_push($parameters, "status = 1");

    if (is_null($columns)) {
        $query = db_query_all($link, TABLE_USERS, $parameters);
    } else {
        $query = db_query_columns($link, TABLE_USERS, $columns, $parameters);
    }

    $result_array = array();
    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
}

//inserts a user into the user table
//$email - email of user item
//$name - name of user item
//$password - password of user item
//$current_points - set to null or is null will set user currentpoint to default
//$status - set to null or is null will set user status to default
function addUser($email, $name, $avatar = null, $password, $mobile_number, $status = 1, $rank = "newbie", $current_points = 0, $total_number_achievement = 0) {
    global $link;

    if (checkEmail($email)) {
        return "Failed to add account. Duplicate email found";
    } else {
        $data = array('id' => null, 'email' => $email, 'avatar' => $avatar, 'name' => $name, 'password' => $password, 'mobile_number' => $mobile_number, 'date_created' => "now()", 'status' => $status, 'rank' => $rank, 'current_points' => $current_points, 'total_number_achievement' => $total_number_achievement, 'last_update' => "now()");

        $query = db_perform(TABLE_USERS, $data, "insert", null, $link);
        $result = db_query($query, $link);
        if ($result) {
            return getUserId($email);
        } else {
            return "Failed to add account";
        }
    }
}

//updates user table for given $id
function updateUser($id, $email = null, $avatar = null, $name = null, $password = null, $mobile_number = null, $status = null, $rank = null, $current_points = null, $total_number_achievement = null, $mobilenumberused) {
    global $link;

    $data = array();

    $data['last_update'] = 'now()';

    if (isset($email) && !empty($email)) {
        $data['email'] = $email;
    }
    if (isset($avatar) && !empty($avatar)) {
        $data['avatar'] = $avatar;
    }
    if (isset($name) && !empty($name)) {
        $data['name'] = $name;
    }
    if (isset($password) && !empty($password)) {
        $data['password'] = $password;
    }
    if (isset($mobile_number) && !empty($mobile_number)) {
        $data['mobile_number'] = $mobile_number;
    }
    if (isset($rank) && !empty($rank)) {
        $data['rank'] = $rank;
    }
    if (isset($current_points) && !empty($current_points)) {
        $data['current_points'] = $current_points;
    }
    if (isset($total_number_achievement) && !empty($total_number_achievement)) {
        $data['total_number_achievement'] = $total_number_achievement;
    }
    if (isset($status) && !empty($status)) {
        $data['status'] = $status;
    }

    $query = db_perform(TABLE_USERS, $data, "update", "id = $id", $link);
    $result = db_query($query, $link);
    if (db_affected_rows($query, $link) > 0) {
        
        addLog($id, $mobilenumberused, 10);
        return "200";
    } else {
        return "Failed to update user information";
    }
}

function updateUserLastUpdate($id){
    global $link;

    $data = array();

    $data['last_update'] = 'now()';
	
	$query = db_perform(TABLE_USERS, $data, "update", "id = $id", $link);
    $result = db_query($query, $link);
    if (db_affected_rows($query, $link) > 0) {
        return "200";
    } else {
        return "Failed to update user information";
    }
}

/* EOF USER MODULE */

/* BOF FRIEND MODULE */

function getFriend($id, $status) {
    global $link;

    $result_array = array();

    //gets friends wherein the request came from you
    $dbquery = 'select id as id from ' . TABLE_USERS . ' where id in (select user_res from ' . TABLE_FRIENDS . ' where user_req = ' . $id . ' and status = ' . $status . ')';
    $query = db_query($dbquery, $link);

    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
}

function getFriend2($id, $status) {
    global $link;

    $result_array = array();

    //gets friends wherein the request came from them
    $dbquery = 'select id as id from ' . TABLE_USERS . ' where id in (select user_req from ' . TABLE_FRIENDS . ' where user_res = ' . $id . ' and status = ' . $status . ')';
    $query = db_query($dbquery, $link);

    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
}

function getUserFriendIds($id, $status) {
    global $link;

    $dbquery = 'select id from ' . TABLE_USERS . ' where id in (select user_res from ' . TABLE_FRIENDS . ' where user_req = ' . $id . ' and status = ' . $status . ')';
    $query = db_query($dbquery, $link);

    $result_array = array();
    while ($result = db_fetch_array($query)) {
        array_push($result_array, (int) $result['id']);
    }

    $dbquery = 'select id as id, email as email, name as name, mobile_number as mobile_number, rank as rank, total_number_achievement as total_number_achievement from ' . TABLE_USERS . ' where id in (select user_req from ' . TABLE_FRIENDS . ' where user_res = ' . $id . ' and status = ' . $status . ')';
    $query = db_query($dbquery, $link);

    while ($result = db_fetch_array($query)) {
        array_push($result_array, (int) $result['id']);
    }

    return $result_array;
}

function getFriend3($id, $columns = array("user_res", "status"), $status) {
    global $link;

    if (isset($columns) && !empty($columns)) {
        if (isset($status)) {
            $query = db_query_columns($link, TABLE_FRIENDS, $columns, "user_req = $id AND status = '" . $status . "'");
        } else {
            $query = db_query_columns($link, TABLE_FRIENDS, $columns, "user_req = $id", $status);
        }
    } else if (is_null($columns)) {
        if (isset($status)) {
            $query = db_query_all($link, TABLE_FRIENDS, "user_req = $id AND status = '" . $status . "'");
        } else {
            $query = db_query_all($link, TABLE_FRIENDS, "user_req = $id", $status);
        }
    } else {
        $query = db_query_columns($link, TABLE_FRIENDS, $columns, "user_req = $id", "status");
    }

    $result_array = array();
    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
}

//NOT YET TESTED
//$action
// -if set to add, sets status to 3
// -if set to accept, sets status to 4
// -if set to ignore, sets status to 5
// -if set to cancel/remove, deletes record
//$id - id of requesting for friendship
//$id2 - id of being requested for friendship
//status [3:pending | 4:accepted | 5:ignore | 6:cancelled]
function updateFriend($id, $id2, $action = 'add', $mobilenumberused) {
    global $link;

    if ($action == 'add') {
        if (!checkFriendRequest($id, $id2)) {
            $data = array('user_req' => $id, 'user_res' => $id2, 'date_req' => 'now()', 'date_res' => 'null', 'status' => 3);
            $query = db_perform(TABLE_FRIENDS, $data, "insert", null, $link);
            $result = db_query($query, $link);
            if ($result) {
                
                addLog($id, $mobilenumberused, 7);
                return "200";
            } else {
                return "Friend request failed";
            }
        } else {
            return "Friend request failed";
        }
    } else if ($action == 'accept') {
        $data = array('date_res' => 'now()', 'status' => 4);
        $query = db_perform(TABLE_FRIENDS, $data, "update", "user_res = $id AND user_req = $id2 AND status NOT IN (4, 6, 7)", $link);
        $result = db_query($query, $link);
        if (db_affected_rows($query, $link) > 0) {
            
            addLog($id, $mobilenumberused, 8);
            return "200";
        } else {
            return "Failed to accept request";
        }
    } else if ($action == 'ignore') {
        $data = array('date_res' => 'now()', 'status' => 5);
        $query = db_perform(TABLE_FRIENDS, $data, "update", "user_req = $id AND user_res = $id2 AND status NOT IN (4, 6, 7)", $link);
        $result = db_query($query, $link);
        if (db_affected_rows($query, $link) > 0) {
            return "200 Invinsible Wall Zone Success";
        } else {
            return "Failed to ignore request";
        }
    } else if ($action == 'cancel' || $action == 'remove') {
        $query = "delete from " . TABLE_FRIENDS . " WHERE (user_req = " . $id . " AND user_res = " . $id2 . ") OR (user_res = " . $id . " AND user_req = " . $id2 . ")";
        $result = db_query($query, $link);
        if ($result) {
            addLog($id, $mobilenumberused, 12);
            return "200";
        } else {
            return "Failed to cancel/remove request";
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

function getAchievementList($ids = null, $columns = array("id", "name", "description", "point")) {
    global $link;

    $request = "";
    //$id_parameter = "";
    $parameters = array();

    if (isset($ids) && !empty($ids)) {

        if (is_array($ids)) {
            $parameter_id = "id IN (";
            foreach ($ids as $id) {
                $parameter_id .= ' ' . $id . ', ';
            }
            $parameter_id = substr($parameter_id, 0, -2);
            $parameter_id .= ")";

            array_push($parameters, $parameter_id);
        } else {
            $parameter_id = " id = " . $ids;
            array_push($parameters, $parameter_id);
        }
    }

    array_push($parameters, "status = 1");

    if (is_null($columns)) {
        $query = db_query_all($link, TABLE_ACHIEVEMENTS, $parameters);
    } else {
        $query = db_query_columns($link, TABLE_ACHIEVEMENTS, $columns, $parameters);
    }

    $result_array = array();
    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
}

function addAchievementList($name, $description, $point, $status = 1) {
    global $link;

    $data = array('id' => null, 'name' => $name, 'description' => $description, 'point' => $point, 'status' => $status, 'date_created' => "now()", 'last_update' => "now()");

    $query = db_perform(TABLE_ACHIEVEMENTS, $data, "insert", null, $link);
    $result = db_query($query, $link);
    if ($result) {
        return true;
    } else {
        return "Failed to add achievement item to list";
    }
}

function updateAchievementList($id, $name = null, $description = null, $point = null, $status = null) {
    global $link;

    $data = array();

    $data['last_update'] = 'now()';

    if (isset($name) && !empty($name)) {
        $data['name'] = $name;
    }
    if (isset($description) && !empty($description)) {
        $data['description'] = $description;
    }
    if (isset($point) && !empty($point)) {
        $data['point'] = $point;
    }
    if (isset($status) && !empty($status)) {
        $data['status'] = $status;
    }

    $query = db_perform(TABLE_ACHIEVEMENTS, $data, "update", "id = $id", $link);
    $result = db_query($query, $link);
    if ($result) {
        return true;
    } else {
        return "Failed to update achievement list item";
    }
}

function getUserAchievements($ids) {
    global $link;

    $result_array = array();
//    $dbquery = 'select id as id, name as name, description as description, point as point FROM ' . TABLE_ACHIEVEMENTS . ' where id in (select achievement_id from ' . TABLE_USERS_ACHIEVEMENT . ' where user_id = ' . $id . ') and status = 1';
    if (is_array($ids)) {
        foreach($ids as $id){
            $dbquery = 'select user_id as user_id, achievement_id as achievement_id from ' . TABLE_USERS_ACHIEVEMENT . ' where user_id = ' . $id;
            $query = db_query($dbquery, $link);


            while ($result = db_fetch_array($query)) {
                array_push($result_array, $result);
            }
        }
    } else {
        $dbquery = 'select user_id as user_id, achievement_id as achievement_id from ' . TABLE_USERS_ACHIEVEMENT . ' where user_id = ' . $id;
        $query = db_query($dbquery, $link);


        while ($result = db_fetch_array($query)) {
            array_push($result_array, $result);
        }
    }

    return $result_array;
}

function getUserNotAchievements($id) {
    global $link;

    $dbquery = 'select id as id, name as name, description as description, point as point FROM ' . TABLE_ACHIEVEMENTS . ' where id not in (select achievement_id from ' . TABLE_USERS_ACHIEVEMENT . ' where user_id = ' . $id . ') and status = 1';
    $query = db_query($dbquery, $link);

    $result_array = array();
    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
}

function getFriendAchievements($id) {
    global $link;

    if (is_array($id)) {
        
    } else {
        $dbquery = 'select id as id, name as name, description as description, point as point FROM ' . TABLE_ACHIEVEMENTS . ' where id in (select achievement_id from ' . TABLE_USERS_ACHIEVEMENT . ' where user_id = ' . $id . ') and status = 1';
        $query = db_query($dbquery, $link);

        $result_array = array();
        while ($result = db_fetch_array($query)) {
            array_push($result_array, $result);
        }

        return $result_array;
    }
}

//function addUserAchievement($userid, $achievementid) {
//    global $link;
//
//    $data = array('user_id' => userid, 'achievement_id' => $achievementid, 'date_created' => 'now()', 'last_update' => 'now()');
//
//    $result = db_query($query, $link);
//    if ($result) {
//        return true;
//    } else {
//        return "Failed to add user achievement";
//    }
//}

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

function getEventList($ids = null, $columns = array("id", "name", "date_start", "date_end", "achievement_id", "point", "description", "status")) {
    global $link;

    $request = "";
    //$id_parameter = "";
    $parameters = array();

    if (isset($ids) && !empty($ids)) {

        if (is_array($ids)) {
            $parameter_id = "id IN (";
            foreach ($ids as $id) {
                $parameter_id .= ' ' . $id . ', ';
            }
            $parameter_id = substr($parameter_id, 0, -2);
            $parameter_id .= ")";

            array_push($parameters, $parameter_id);
        } else {
            $parameter_id = " id = " . $ids;
            array_push($parameters, $parameter_id);
        }
    }

    array_push($parameters, "status = 1");

    if (is_null($columns)) {
        $query = db_query_all($link, TABLE_EVENTS, $parameters);
    } else {
        $query = db_query_columns($link, TABLE_EVENTS, $columns, $parameters);
    }

    $result_array = array();
    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
}

function getEventByDate($date) {
    global $link;

    $dbquery = 'select id as id, name as name, date_start as date_start, date_end as date_end, achievement_id as achievement_id, point as point, description as description, status as status from ' . TABLE_EVENTS . ' where status = 1 and date_start <= ' . $date . ' and date_end >= ' . $date . ';';
    $query = db_query($dbquery, $link);

    $result_array = array();
    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
}

function addEvent($name, $date_start, $date_end, $achievement_id, $point, $description, $status = 1) {
    global $link;

    $data = array('id' => null, 'name' => $name, 'date_start' => $date_start, 'date_end' => $date_end, 'achievement_id' => "achievement_id", 'point' => $point, 'description' => "description", 'status' => $status, 'date_created' => "now()");

    $result = db_perform(TABLE_EVENTS, $data, "insert", null, $link);
    $result = db_query($query, $link);
    if ($result) {
        return true;
    } else {
        return "Failed to add event";
    }
}

function updateEvent($id, $name = null, $date_start = null, $date_end = null, $achievement_id = null, $point = null, $description = null, $status = null) {
    global $link;

    $data = array();

    $data['last_update'] = 'now()';

    if (isset($name) && !empty($name)) {
        $data['name'] = $name;
    }
    if (isset($date_start) && !empty($date_start)) {
        $data['date_start'] = $date_start;
    }
    if (isset($date_end) && !empty($date_end)) {
        $data['date_end'] = $date_end;
    }
    if (isset($achievement_id) && !empty($achievement_id)) {
        $data['achievement_id'] = $achievement_id;
    }
    if (isset($point) && !empty($point)) {
        $data['point'] = $point;
    }
    if (isset($description) && !empty($description)) {
        $data['description'] = $description;
    }
    if (isset($status) && !empty($status)) {
        $data['status'] = $status;
    }

    $result = db_perform(TABLE_ACHIEVEMENTS, $data, "update", "id = $id", $link);
    $result = db_query($query, $link);
    if ($result) {
        return true;
    } else {
        return "Failed to update event item";
    }
}

/* EOF EVENT MODULE */

/* BOF LOGIN MODULE */

function login($email, $password, $mobilenumberused) {
    global $link;

    $dbquery = "select * from " . TABLE_USERS . " where email = '$email' and password = '$password' and status = 1";
    $query = db_query($dbquery, $link);

    if (db_num_rows($query) > 0) {
        addLog(getUserId($email), $mobilenumberused, 2);
        return getUserId($email);
    } else {
        return "Failed to login. Email or Password invalid.";
    }
}

/* EOF LOGIN MODULE */

/* BOF FRIEND MODULE */

function getUserFriend($id) {
    global $link;

    $dbquery = "select * from " . TABLE_USERS . " where email = '" . $email . "'";
    $query = db_query($dbquery, $link);

    $result_array = array();
    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
}

/* EOF FRIEND MODULE */

/* BOF POINT MODULE */

function transferPoint($id, $id2, $points, $mobilenumberused) {
    global $link;


    //get senders current points
    $dbquery = "select current_points from " . TABLE_USERS . " where id = $id";
    $query = db_query($dbquery, $link);

    $row = mysql_fetch_row($query);
    $id_points = $row[0];

    //get receivers current points
    $dbquery = "select current_points from " . TABLE_USERS . " where id = $id2";
    $query = db_query($dbquery, $link);

    $row = mysql_fetch_row($query);
    $id2_points = $row[0];

    if (isset($id_points) && isset($id2_points)) {

        //sender's new points
        $id_newpoints = $id_points - $points;

        //receiver's new points
        $id2_newpoints = $id2_points + $points;

        //update sender's points
        $data = array('current_points' => $id_newpoints, 'last_update' => 'now()');
        $query = db_perform(TABLE_USERS, $data, "update", "id = $id", $link);
        $result = db_query($query, $link);

        //update receiver's points
        $data2 = array('current_points' => $id2_newpoints, 'last_update' => 'now()');
        $query2 = db_perform(TABLE_USERS, $data2, "update", "id = $id2", $link);
        $result2 = db_query($query2, $link);

        if (db_affected_rows($query, $link) > 0 && db_affected_rows($query2, $link) > 0) {

            //add record of transfer point to table transferpoint
            $data = array('id' => null, 'giver_id' => $id, 'receiver_id' => $id2, 'point' => $points, 'date_created' => 'now()', 'status' => 1);
            $query = db_perform(TABLE_TRANSFERPOINT, $data, "insert", null, $link);
            $result = db_query($query, $link);
            if ($result) {
                
                addLog($id, $mobilenumberused, 9);
                return "200";
            } else {
                return "Failed to transfer point";
            }
        } else {
            return "Failed to transfer points";
        }
    }
}

/* EOF POINT MODULE */

/* BOF GENERAL */

function getUserId($email) {
    global $link;

    $dbquery = "select id from " . TABLE_USERS . " where email = '$email'";
    $query = db_query($dbquery, $link);

    $row = mysql_fetch_row($query);
    $id = $row[0];
    if (isset($id) && !empty($id)) {
        return $id;
    } else {
        return "Failed to get id by email.";
    }
}

function checkEmail($email) {
    global $link;

    $dbquery = "select * from " . TABLE_USERS . " where email = '" . $email . "'";
    $query = db_query($dbquery, $link);

    if (db_num_rows($query) > 0) {
        return true;
    } else {
        return false;
    }
}

function checkFriendRequest($id, $id2) {
    global $link;

    $dbquery = "select * from " . TABLE_FRIENDS . " where user_req = '" . $id . "' and user_res = '" . $id2 . "' and status != 6";
    $query = db_query($dbquery, $link);

    $dbquery2 = "select * from " . TABLE_FRIENDS . " where user_res = '" . $id . "' and user_req = '" . $id2 . "' and status != 6";
    $query2 = db_query($dbquery2, $link);

    if (db_num_rows($query) > 0 || db_num_rows($query2) > 0) {
        return true;
    } else {
        return false;
    }
}

function usedReceipt($receiptnumber){
    global $link;
    
    $dbquery = "select count(*) from " . TABLE_RECEIPT . " where status = 1 and receipt_number = ".$receiptnumber;
    $query = db_query($dbquery, $link);
    $row = mysql_fetch_row($query);
    
    if ($row[0] > 0) {
        return true;
    } else {
        return false;
    }
}

function countAchievement($userid){
    global $link;
    
    $dbquery = "select count(*) from " . TABLE_USERS_ACHIEVEMENT . " where user_id = ".$userid;
    $query = db_query($dbquery, $link);

    $row = mysql_fetch_row($query);
    return $achievementcount = $row[0];
}

/* EOF GENERAL */

function getUserAndFriends($id) {
    global $link;

    $result_array = array();
    array_push($result_array, $id);
    //gets friends wherein the request came from you
    $dbquery = 'select id as id from ' . TABLE_USERS . ' where id in (select user_res from ' . TABLE_FRIENDS . ' where user_req = ' . $id . ')';
    $query = db_query($dbquery, $link);

    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result['id']);
    }

    //gets friends wherein the request came from them
    $dbquery = 'select id as id from ' . TABLE_USERS . ' where id in (select user_req from ' . TABLE_FRIENDS . ' where user_res = ' . $id . ')';
    $query = db_query($dbquery, $link);

    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result['id']);
    }

    return $result_array;
}

function getFriends($id){
    global $link;

    $result_array = array();

    $dbquery = 'select user_res, date_req, date_res, status from friends where user_req ='.$id;
    $query = db_query($dbquery, $link);

    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }
    
    $dbquery = 'select user_req, date_req, date_res, status from friends where user_res ='.$id;
    $query = db_query($dbquery, $link);

    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
}

function getLogs($ids){
    global $link;
    
    $result_array = array();
    if (is_array($ids)) {
        foreach($ids as $id){
            $dbquery = 'select id as id, user_id as user_id, date_created as date_created, action as action, action_description as action_description from '.TABLE_LOGS.' where user_id = ' . $id;
            $query = db_query($dbquery, $link);


            while ($result = db_fetch_array($query)) {
                array_push($result_array, $result);
            }
        }
    } else {
        $dbquery = 'select id as id, user_id as user_id, date_created as date_created, action as action, action_description as action_description from '.TABLE_LOGS.' where user_id = ' . $id;
        $query = db_query($dbquery, $link);


        while ($result = db_fetch_array($query)) {
            array_push($result_array, $result);
        }
    }

    return $result_array;
    
}

function getReceipt($id){
    global $link;
    
    $result_array = array();
    $dbquery = 'select receipt_number as receipt_number, date_created as date_created from '.TABLE_RECEIPT.' where user_id = ' . $id;
    $query = db_query($dbquery, $link);

    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
    
}

function getEvent(){
    global $link;
    
    $result_array = array();
    $dbquery = 'select name as name, date_start as date_start, date_end as date_end, description as description, achievement_id as achievement_id, point as point, status as status from '.TABLE_EVENTS;
    $query = db_query($dbquery, $link);

    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
    
}

function updateUserReceipt($userid, $receiptnumber, $mobilenumberused){
    global $link;
    
    
    
    //update receipt details
    $receipt_added = array();
    $receipt_rejected = array();
    
    if(is_array($receiptnumber)){
        foreach ($receiptnumber as $value) {
            if(usedReceipt($value)){
                array_push($receipt_rejected, $value);
            }else{
                array_push($receipt_added, $value);
                
                $data = array();
                $data['user_id'] = $userid;
                $data['status'] = 1;
                $query = db_perform(TABLE_RECEIPT, $data, "update", "receipt_number = $value", $link);
                $result = db_query($query, $link);
            }    
                
        }
    }
    else{
        if(usedReceipt($receiptnumber)){
           array_push($receipt_rejected, $value);
        }else{
           array_push($receipt_added, $value);

           $data = array();
           $data['user_id'] = $userid;
           $data['status'] = 1;
           $query = db_perform(TABLE_RECEIPT, $data, "update", "receipt_number = $value", $link);
           $result = db_query($query, $link);
        }    
    }
    
    if(count($receipt_added)>0){
        addLog($userid, $mobilenumberused, 4);
    }else if(count($receipt_added)==0 && count($receipt_rejected)>0){
        return "Invalid/Duplicate receipt/s.";
    }
    
    //check for new achievements
    $newachievement = checkAchievement($userid);
    if($newachievement>0){
        addLog($userid, $mobilenumberused, 11);
        return $receipt_added;
    }
    
}

function checkAchievement($userid){
    global $link;
    $newachievement = 0;
    
    //gets combined information for user's receipt id, combined type
    $dbquery = "select food_type, sum(qty) as qty_usertotal, sum(subtotal) as total from receipt_details where receipt_number in (select receipt_number from receipt where user_id = $userid) group by food_type";
    $query = db_query($dbquery, $link);

    //per food type, queries achievement information of the same type and 
    while ($result = db_fetch_array($query)) {
        $query2 = "select id, name, description, type, required_qty as qty_requirement, point from achievements where type = '".$result['food_type']."' and id not in (select achievement_id from users_achievements where user_id = $userid);";
        $query2 = db_query($query2, $link);
        while ($result2 = db_fetch_array($query2)) {
            if($result2['qty_requirement']<=$result['qty_usertotal']){
                updateUserAchievement($userid, $result2['id'], $result2['point']);
                $newachievement++;
            }
        }
    }
    
    return $newachievement;
}

function updateUserAchievement($userid, $achievementid, $point){
    global $link;
    
    $data = array('user_id' => $userid, 'achievement_id' => $achievementid, 'date_created' => 'now()', 'last_update' => 'now()');

    $query = db_perform(TABLE_USERS_ACHIEVEMENT, $data, "insert", null, $link);
    $result = db_query($query, $link);
    
    $dbquery = "update ".TABLE_USERS." set rank='".getRank($userid)."', current_points = current_points+".$point.", total_number_achievement=".countAchievement($userid).", last_update = now() where id = ".$userid;
    $query = db_query($dbquery, $link);
}

function getRank($userid){
    global $link;
    
    $dbquery = 'select rank from '.TABLE_RANK.' where required_number<='.  countAchievement($userid).' order by required_number desc limit 1';
    $query = db_query($dbquery, $link);

    $row = mysql_fetch_row($query);
    return $rank = $row[0];
    
}

function addLog($userid, $mobilenumberused, $action) {
    global $link;
    
    $action = getLogAction($action);
    $data = array('user_id' => $userid, 'date_created' => 'now()', 'mobile_number' => $mobilenumberused, 'action' => $action['action'], 'action_description' => $action['action_description']);

    $query = db_perform(TABLE_LOGS, $data, "insert", null, $link);
    $result = db_query($query, $link);
    if ($result) {
        return true;
    } else {
        return "Failed to add log item";
    }
}

function getLogAction($type){
    
    $action = array();
    switch($type){
        case 1: 
            $action['action'] = "Sign Up";
            $action['action_description'] = "User signed up";
            return $action;
            break;
        case 2: 
            $action['action'] = "Log In";
            $action['action_description'] = "User logged in";
            return $action;
            break;
        case 3: 
            $action['action'] = "Sync";
            $action['action_description'] = "User synced information";
            return $action;
            break;
        case 4: 
            $action['action'] = "Scan for points";
            $action['action_description'] = "User scanned receipt for points";
            return $action;
            break;
        case 5: 
            $action['action'] = "Order made";
            $action['action_description'] = "User made an order";
            return $action;
            break;
        case 6: 
            $action['action'] = "Shared Tag'it";
            $action['action_description'] = "User shared Tag'it";
            return $action;
            break;
        case 7: 
            $action['action'] = "Friend Request";
            $action['action_description'] = "User made a friend request";
            return $action;
            break;
        case 8: 
            $action['action'] = "Accept Request";
            $action['action_description'] = "User accepted a friend request";
            return $action;
            break;
        case 9: 
            $action['action'] = "Transfer Point";
            $action['action_description'] = "User transferred point";
            return $action;
            break;
        case 10: 
            $action['action'] = "Profile Update";
            $action['action_description'] = "User updated profile information";
            return $action;
            break;
        case 11: 
            $action['action'] = "Achievement Unlocked";
            $action['action_description'] = "User unlocked achievement";
            return $action;
            break;
        case 12: 
            $action['action'] = "Cancelled Friend/Request";
            $action['action_description'] = "User cancelled friend request / removed friend";
            return $action;
            break;
    }
    
}

function getRedeem($ids = null, $columns = array("id", "name", "price", "type")) {
    global $link;

    $request = "";
    //$id_parameter = "";
    $parameters = array();

    if (isset($ids) && !empty($ids)) {

        if (is_array($ids)) {
            $parameter_id = "id IN (";
            foreach ($ids as $id) {
                $parameter_id .= ' ' . $id . ', ';
            }
            $parameter_id = substr($parameter_id, 0, -2);
            $parameter_id .= ")";

            array_push($parameters, $parameter_id);
        } else {
            $parameter_id = " id = " . $ids;
            array_push($parameters, $parameter_id);
        }
    }

    array_push($parameters, "status = 1");

    if (is_null($columns)) {
        $query = db_query_all($link, TABLE_POINTEQUIVALENT, $parameters);
    } else {
        $query = db_query_columns($link, TABLE_POINTEQUIVALENT, $columns, $parameters);
    }

    $result_array = array();
    while ($result = db_fetch_array($query)) {
        array_push($result_array, $result);
    }

    return $result_array;
}

function searchUser($tag){ 
    global $link;
	
	$dbquery = "select count(*) from " . TABLE_USERS . " WHERE email = '".$tag."' OR name = '".$tag."'";
    $query = db_query($dbquery, $link);
    $row = mysql_fetch_row($query);
    
    if ($row[0] > 0) {
        
		
			$dbquery = "SELECT id, avatar, email, name, mobile_number, rank, current_points, total_number_achievement, last_update FROM ".TABLE_USERS." WHERE email = '".$tag."' OR name = '".$tag."'";
			$query = db_query($dbquery, $link);

			$result_array = array();
			while ($result = db_fetch_array($query)) {
				array_push($result_array, $result);
			}

			return $result_array;
		
		
    } else {
        return "No results found.";
    }
}


?>