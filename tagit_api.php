<?php

/*
 * TagIt!
 * Calvin Ongkingco, Karyn Liong, Miguel Sision, Warren Miñano, Yuyu Lai
 */

require ('/includes/tagit_function.php');

$request = $_SERVER['QUERY_STRING'];
$result = null;

if(isset($request) && !empty($request)){
    
    $parameters = parseRequest($request);
    
    switch($parameters['action']){

        case "addUser":
            $result = addUser($parameters['email'], $parameters['name'], $parameters['password'], $parameters['mobile_number']);
            break;
			
        case "getUser":
            $result = login($parameters['email'], $parameters['password']);
            break;
			
        case "syncUser":
		
			//data pulled for user profile
            $result['user_profile'] = getUser($parameters['id'], array("id", "email", "name", "password", "mobile_number", "current_rank", "current_achievements"));
			
			//data pulled for profiles of user's friends
			$result['friends_profile'] = getUser(getUserFriendIds($parameters['id'], 4), array("id", "email", "name", "password", "mobile_number", "current_rank", "current_achievements"));
			
			//data pulled for user's friends and requests
			$result['friends_current'] = getFriend($parameters['id'], 4);
			$result['friends_pending'] = getFriend($parameters['id'], 3);
			$result['friends_ignored'] = getFriend($parameters['id'], 5);
			$result['friends_cencelled'] = getFriend($parameters['id'], 6);
			
			//data pulled for user's achievements achieved and not yet achieved
			$result['achievement_userachieved'] = getUserAchievements($parameters['id']);
			$result['achievement_usernotachieved'] = getUserNotAchievements($parameters['id']);
            break;
			
		case "addFriend":
			$result = updateFriend($parameters['id'], $parameters['id2'], "add");
			break;
			
		case "cancelFriend":
			$result = updateFriend($parameters['id'], $parameters['id2'], "remove");
			break;
			
		case "acceptFriend":
			$result = updateFriend($parameters['id'], $parameters['id2'], "accept");
			break;	
			
		case "ignoreFriend":
			$result = updateFriend($parameters['id'], $parameters['id2'], "ignore");
			break;	
			
		case "removeFriend":
			$result = updateFriend($parameters['id'], $parameters['id2'], "remove");
			break;
		
		case "transferPoint":
			$result = transferPoint($parameters['id'], $parameters['id2'], $parameters['point']);
			break;
    }
}

    if(is_null($result)){
        echo "Request error.";
    }
    else{
        $arr = array("result"=>$result);
        echo json_encode($arr);
    }
	
	$query =  mysql_query("SELECT email FROM users WHERE id !=1");
	while ($row = mysql_fetch_array($query)) {
	$email= $row['email'];
	echo getUserId($email);
	}
    
/*
 * LOGIN: tagit_api.php?action=login&email=email&password=password
 * RETURNS: 1 - successful login, "Failed to login. Invalid email/password" - failed login
 * 
 * SIGNUP: tagit_api.php?action=signup&email=email&name=name&password=password&mobile_number=mobilenumber
 * RETURNS: 1 - successful login, "Failed to add account. Duplicate email found" - duplicate email being registered 
 */
?>
