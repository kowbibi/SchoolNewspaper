<?php  
require_once 'writer/classes/Article.php';
require_once 'writer/classes/Database.php';
require_once 'writer/classes/User.php';

$databaseObj= new Database();
$userObj = new User();
$articleObj = new Article();

$userObj->startSession();
?>
