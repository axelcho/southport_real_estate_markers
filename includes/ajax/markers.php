<?php
//The ajax script
//This script uses Zend library files Zend/Config/Xml and /Zend/Db from Zend Framework 1. 

//set siteroot one directory above the web root, so that configuration information would not be accessible via http. 
$_SITE_ROOT = dirname(dirname(dirname(__FILE__)));

//set time zone
date_default_timezone_set('America/New_York');

//set path
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . dirname(__FILE__));

//read configuration xml file
require_once 'Zend/Config/Xml.php';
$_CONFIG = new Zend_Config_Xml($_SITE_ROOT . '/config/site.xml', 'global');
$_CONFIG = new Zend_Config_Xml($_SITE_ROOT . '/config/site.xml', $_CONFIG->mode);

//database plug by factory
require_once 'Zend/Db.php';
require_once 'Zend/Db/Table.php';
require_once 'Zend/Db/Table/Row/Abstract.php';

$_DB = Zend_Db::factory($_CONFIG->database);

//build query from data
$query = "SELECT * from `property` WHERE `Latitude` BETWEEN ".$_REQUEST['swlat']." AND ".$_REQUEST['nelat']." AND `Longitude` BETWEEN ".$_REQUEST['swlng']." AND ".$_REQUEST['nelng'];

//add conditions if required
if (!empty($_REQUEST['beds']))
	$query .= " AND `Bedrooms` > ".$_REQUEST['beds'];

if (!empty($_REQUEST['baths']))
	$query .= " AND `Bathrooms` > ".$_REQUEST['baths'];

if (!empty($_REQUEST['property']))
	$query .= " AND `PropertyType` LIKE '".$_REQUEST['property']."'";

if (!empty($_REQUEST['price'])){
	$prices = explode("|", $_REQUEST['price']);
	$query .= " AND `ListingPrice` BETWEEN ".$prices[0]." AND ".$prices[1];
}

//some region has squarefoot in range (1000-1200 sqft). Some has definite number (1327 sqft). 

if (!empty($_REQUESST['sqft'])){
	$sqft = explode("-", $_REQUEST['sqft']);
	$query .= " AND (`SquarefootRange` LIKE '".$_REQUEST['sqft']"' OR `SquarefootRange` BETWEEN ".$sqft[0]." AND ".$sqft[1].")" 	
}

//limit
$query .= " Limit ". ($_REQUEST['page']*50) .", 50";

//get the result
$result = $_DB->fetchAll($query);

//convert to json and send to javascript
$json_result = json_encode($result);
print($json_result);
?>
