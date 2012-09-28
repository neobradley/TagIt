<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        // put your code here
        require ('\includes\database_tables.php');
        require ('\includes\database.php');
        
        echo "Menu:<br/>";
        
        $link = connect('192.168.1.101:3306', 'tagit', 'tagit', 'tagit');
        $result = query('select name, price, type from menu where status = 1 order by type', $link) or die(mysql_error());
        
        while($products = mysql_fetch_array($result)){
            echo $products['name']." - ".$products['price']. "<br/>";
        }
        
        
        ?>
    </body>
</html>
