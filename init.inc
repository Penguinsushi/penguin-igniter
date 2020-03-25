<?php 
// =============================================================================================================
// PenguinIgniter (simplistic, CodeIgniter-inspired site framework)
// primary execution script
// =============================================================================================================

require_once($GLOBALS['config']['core_dir']."version.inc");

// =============================================================================================================
// INITIAL SETUP

// include autoloader
require_once($GLOBALS['config']['core_dir']."autoload.php");
spl_autoload_register('__autoload');

// =============================================================================================================
// URI PARSING

// call uri steps explicitly with config options
$uri = new URI();
$uri->baseDir($GLOBALS['config']['base_url']);
$uri->directEmpty($GLOBALS['config']['home_page'],$GLOBALS['config']['redirect_empty']);
$uri->redirectURIs($GLOBALS['config']['redirects']);
$uri->rewriteURIs($GLOBALS['config']['rewrites']);
$uri->fakeExt($GLOBALS['config']['fake_ext'],$GLOBALS['config']['force_fake']);
$uri->setElements();

// assign to global
$GLOBALS['uri'] = $uri;

// assign global 'page' tag
$GLOBALS['page'] = $uri->elements[0];

// start session
session_start();
if (empty($_SESSION['session'])) {$_SESSION['session'] = new Session();} else {$_SESSION['session']->new = FALSE;}

// dev
if (isset($uri->query_array['dev'])) {if ($uri->query_array['dev'] == $GLOBALS['config']['dev_key']) {$_SESSION['dev']=TRUE;} else {$_SESSION['dev']=FALSE;}}
if (!empty($_SESSION['dev'])) {error_reporting(E_ALL);} else {error_reporting(0);}

// =============================================================================================================
// GLOBAL OBJECTS

// if config log, create global log object
if (!empty($GLOBALS['config']['log']))
{
    $GLOBALS['log'] = new Log($GLOBALS['config']['log']);
    if (    $GLOBALS['config']['log_pageloads'] === TRUE 
            OR (strstr($GLOBALS['config']['log_pageloads'],'PAGE('.$GLOBALS['page'].')'))
            OR (strstr($GLOBALS['config']['log_pageloads'],'POST') AND !empty($_POST))
            OR (strstr($GLOBALS['config']['log_pageloads'],'QUERY') AND !empty($GLOBALS['uri']->query_string))
            OR (strstr($GLOBALS['config']['log_pageloads'],'SESSION') AND !empty($_SESSION['session']->new) AND ($_SESSION['session']->bot == FALSE OR !strstr($GLOBALS['config']['log_pageloads'],'NOBOTS')))
            ) 
    {$GLOBALS['log']->writeEntry();}
}

// if config setting not empty, create cache instance
if (!empty($GLOBALS['config']['cachetype']))
{
    $GLOBALS['cache'] = new Cache($GLOBALS['config']['cachetype']);
}

// if config autoconnect, create global db instance.  otherwise, db instances must be initialized in individual controllers or models
if ($GLOBALS['config']['db_autoconnect'] == TRUE AND !empty($GLOBALS['config']['db_type']) AND !empty($GLOBALS['config']['db_host']) AND !empty($GLOBALS['config']['db_user']) AND !empty($GLOBALS['config']['db_pass']) AND !empty($GLOBALS['config']['db_name']))
{
    $GLOBALS['db'] = new DatabaseConnection();
}

// =============================================================================================================
// LOAD AND EXECUTE CONTROLLER

$controllername = "Controller_".$GLOBALS['page'];

// make sure valid controller class exists
if (!class_exists($controllername))
{
    // otherwise, redirect to error page
    header("Location: ".$GLOBALS['config']['404']);
    die;
}

// instantiate controller Controller_[element]
$controller = new $controllername();

// determine if second element is valid method
if (!empty($uri->elements[1]) AND method_exists($controllername,$uri->elements[1]))
{
    $method = $uri->elements[1];
    // get additional elements (index 2+)as vars array
    $vars = array_slice($uri->elements,2);
    // execute controller method
    $controller->$method($vars);
}
// otherwise, execute index() method
else
{
    // get additional elements (index 1+) as vars array
    $vars = array_slice($uri->elements,1);
    // execute controller index method
    $controller->index($vars);   
}

// =============================================================================================================
?>