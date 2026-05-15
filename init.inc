<?php 
// =============================================================================================================
// PenguinIgniter (simplistic, CodeIgniter-inspired site framework)
// primary execution script
// =============================================================================================================

// =============================================================================================================
// CONFIG
// 
// backwards compatibility
if (empty($config) && !empty($GLOBALS['config'])) {
    $config = $GLOBALS['config'];
}
require_once($config['core_dir'] . "version.inc");

// include autoloader
require_once($config['core_dir'] . "autoload.php");
spl_autoload_register('autoloader');

Config::load($config);

// =============================================================================================================
// URI

URI::routing();

// dev
if (URI::query('dev')) {
    if (URI::query('dev') == Config::val('dev_key')) {
        Session::val('dev', true);
    } else {
        Session:val('dev', false, true);
    }
}

// =============================================================================================================
// SESSION

SESSION::initialize();

if (Session::val('dev')) {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

// =============================================================================================================
// SITE LOG

Log::logPageLoad();

// =============================================================================================================
// BACKWARDS COMPATIBILITY

$_SESSION['session'] = Session::current();
$GLOBALS['uri'] = URI::current();
$GLOBALS['page'] = URI::page();
$GLOBALS['cache'] = Cache::single();
$GLOBALS['db'] = Config::val('db_autoconnect') ? DatabaseConnection::site() : null;
$GLOBALS['log'] = Log::site();
$GLOBALS['pi_version'] = Config::val('pi_version');

// =============================================================================================================
// LOAD AND EXECUTE CONTROLLER

$controllername = "Controller_".URI::page();

// make sure valid controller class exists
if (!class_exists($controllername))
{
    // otherwise, redirect to error page
    header("Location: ".Config::val('404'));
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