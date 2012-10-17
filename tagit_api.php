<?php

/*
 * TagIt!
 * Calvin Ongkingco, Karyn Liong, Miguel Sision, Warren Miñano, Yuyu Lai
 */

require ('/includes/tagit_function.php');

$request = $_SERVER['QUERY_STRING'];
$result = null;

if (isset($request) && !empty($request)) {

    $parameters = parseRequest($request);

    switch ($parameters['action']) {

        case "addUser":
            $result = addUser($parameters['email'], null, $parameters['name'], $parameters['password'], $parameters['mobile_number']);
            break;

        case "getUser":
            $result = login($parameters['email'], $parameters['password'], $parameters['mobilenumberused']);
            break;

        case "updateUserInfo":
            $email = null;
            $avatar = null;
            $name = null;
            $password = null;
            $mobile_number = null;
            $status = null;
            if (isset($parameters['email']) && !empty($parameters['email'])) {
                $email = $parameters['email'];
            }
            if (isset($parameters['avatar']) && !empty($parameters['avatar'])) {
                $avatar = $parameters['avatar'];
            }
            if (isset($parameters['name']) && !empty($parameters['name'])) {
                $name = $parameters['name'];
            }
            if (isset($parameters['password']) && !empty($parameters['password'])) {
                $password = $parameters['password'];
            }
            if (isset($parameters['mobile_number']) && !empty($parameters['mobile_number'])) {
                $mobile_number = $parameters['mobile_number'];
            }
            if (isset($parameters['status']) && !empty($parameters['status'])) {
                $status = $parameters['status'];
            }

            $result = updateUser($parameters['id'], $email, $avatar, $name, $password, $mobile_number, $status, null, null, null, $parameters['mobilenumberused']);
            break;
        
        case "syncUser":

            //data pulled for user profile
            addLog($parameters['id'], $parameters['mobilenumberused'], 3);
            
            $result['user'] = getUser(getUserAndFriends($parameters['id']), array("id", "avatar", "email", "name", "mobile_number", "rank", "current_points", "total_number_achievement", "last_update"));
            $result['achievement'] = getAchievementList(null, array("id","name","type","description","type","required_qty","point"));
            $result['friend'] = getFriends($parameters['id']);
            $result['user_achievement'] = getUserAchievements(getUserAndFriends($parameters['id']));
            $result['log'] = getLogs(getUserAndFriends($parameters['id']));
            $result['receipt'] = getReceipt($parameters['id']);
            $result['event'] = getEvent();
            $result['menu'] = getMenu(null, array("id","name","price","type"));
            $result['redeem'] = getRedeem(null, array("id","point","equivalent"));
            
//            $result['user_achievement'] = getUserAchievements($parameters['id']);
            //data pulled for profiles of user's friends
//            $user_friendids = getUserFriendIds($parameters['id'], 4);
//            if (isset($user_friendids) && !empty($user_friendids)) {
//                $result['friends_profile'] = getUser($user_friendids, array("id", "avatar", "email", "name", "password", "mobile_number", "rank", "current_points", "total_number_achievement", "last_update"));
//                $result['friends_achievement'] = getFriendsAchievement
//            } else {
//                $result['friends_profile'] = null;
//            }

            //data pulled for user's friends and requests
//            $result['friends_current'] = getFriend($parameters['id'], 4);
//            
//            $result['friends_request'] = getFriend($parameters['id'], 3);
//            $result['friends_pending'] = getFriend2($parameters['id'], 3);
//          
            //data pulled for user's achievements achieved and not yet achieved
//            $result['achievement_userachieved'] = getUserAchievements($parameters['id']);
//            $result['achievement_usernotachieved'] = getUserNotAchievements($parameters['id']);
            break;

        case "addFriend":
            $result = updateFriend($parameters['id'], $parameters['id2'], "add", $parameters['mobilenumberused']);
            break;

        case "cancelFriend":
            $result = updateFriend($parameters['id'], $parameters['id2'], "remove", $parameters['mobilenumberused']);
            break;

        case "acceptFriend":
            $result = updateFriend($parameters['id'], $parameters['id2'], "accept", $parameters['mobilenumberused']);
            break;

        case "ignoreFriend":
            $result = updateFriend($parameters['id'], $parameters['id2'], "ignore", $parameters['mobilenumberused']);
            break;

        case "removeFriend":
            $result = updateFriend($parameters['id'], $parameters['id2'], "remove", $parameters['mobilenumberused']);
            break;

        case "transferPoint":
            $result = transferPoint($parameters['id'], $parameters['id2'], $parameters['point'], $parameters['mobilenumberused']);
            break;
        
        case "scanPoints":
            $result = updateUserReceipt($parameters['id'], $parameters['receiptnumber'], $parameters['mobilenumberused']);
            break;
    }
}

$api_list = array(
    "addUser"=>array("Param"=>'email, name, password, mobile_number'),
    "getUser"=>array("Param"=>'email, password, mobilenumberused'),
    "updateUserInfo"=>array("Param"=>'email, avatar, name, password, mobile_number, status'),
    "syncUser"=>array("Param"=>'id, mobilenumberused'),
    "addFriend"=>array("Param"=>'id, id2, mobilenumberused'),
    "cancelFriend"=>array("Param"=>'id, id2, mobilenumberused'),
    "acceptFriend"=>array("Param"=>'id, id2, mobilenumberused'),
    "ignoreFriend"=>array("Param"=>'id, id2, mobilenumberused'),
    "removeFriend"=>array("Param"=>'id, id2, mobilenumberused'),
    "transferPoint"=>array("Param"=>'id, id2, point, mobilenumberused'),
    "scanPoints"=>array("Param"=>'id, receiptnumber, mobilenumberused')
    
);

if (is_null($result)) {
    ?>
<table>
    <?php
    foreach($api_list as $api =>$att){
        echo "<tr><td>".$api."<td>Parameter : ".$att['Param']."</tr>";
    }
        ?>
    </table>
    <?php  
} else {
    $arr = array("result" => $result);
//    echo "<script type='text/javascript'>console.log(".json_encode($arr).');</script>';
    print_r(json_encode($arr));
}

/*
 * LOGIN: tagit_api.php?action=login&email=email&password=password
 * RETURNS: 1 - successful login, "Failed to login. Invalid email/password" - failed login
 * 
 * SIGNUP: tagit_api.php?action=signup&email=email&name=name&password=password&mobile_number=mobilenumber
 * RETURNS: 1 - successful login, "Failed to add account. Duplicate email found" - duplicate email being registered 
 */
?>
