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

        case "signup":
            $result = addUser($parameters['email'], $parameters['name'], $parameters['password'], $parameters['mobile_number']);
            break;
        case "login":
            $result = login($parameters['email'], $parameters['password']);
            break;
        case "sync":
            $result = getUser($ids, $columns);
            break;
    }
}

    if(is_null($result)){
        echo "Request error.";
    }
    else{
        echo $result;
    }
    
/*
 * LOGIN: tagit_api.php?action=login&email=email&password=password
 * RETURNS: 1 - successful login, "Failed to login. Invalid email/password" - failed login
 * 
 * SIGNUP: tagit_api.php?action=signup&email=email&name=name&password=password&mobile_number=mobilenumber
 * RETURNS: 1 - successful login, "Failed to add account. Duplicate email found" - duplicate email being registered 
 */
?>
