<?php
/*

/Applications/Splunk/bin/scripts/ga.sh

*/
// /Applications/XAMPP/xamppfiles/bin/php htdocs/api/info.php
//phpinfo();
// ----------------------------------------------------------------------------------------------------
// - Display Errors
// ----------------------------------------------------------------------------------------------------
ini_set('display_errors', 'On');
ini_set('html_errors', 1);

// ----------------------------------------------------------------------------------------------------
// - Error Reporting
// ----------------------------------------------------------------------------------------------------
error_reporting(-1);

// ----------------------------------------------------------------------------------------------------
// - Shutdown Handler
// ----------------------------------------------------------------------------------------------------
function ShutdownHandler()
{
    if(@is_array($error = @error_get_last()))
    {
        return(@call_user_func_array('ErrorHandler', $error));
    };

    return(TRUE);
};

register_shutdown_function('ShutdownHandler');

// ----------------------------------------------------------------------------------------------------
// - Error Handler
// ----------------------------------------------------------------------------------------------------
function ErrorHandler($type, $message, $file, $line)
{
    $_ERRORS = Array(
        0x0001 => 'E_ERROR',
        0x0002 => 'E_WARNING',
        0x0004 => 'E_PARSE',
        0x0008 => 'E_NOTICE',
        0x0010 => 'E_CORE_ERROR',
        0x0020 => 'E_CORE_WARNING',
        0x0040 => 'E_COMPILE_ERROR',
        0x0080 => 'E_COMPILE_WARNING',
        0x0100 => 'E_USER_ERROR',
        0x0200 => 'E_USER_WARNING',
        0x0400 => 'E_USER_NOTICE',
        0x0800 => 'E_STRICT',
        0x1000 => 'E_RECOVERABLE_ERROR',
        0x2000 => 'E_DEPRECATED',
        0x4000 => 'E_USER_DEPRECATED'
    );

    if(!@is_string($name = @array_search($type, @array_flip($_ERRORS))))
    {
        $name = 'E_UNKNOWN';
    };

    return(print(@sprintf("%s Error in file \xBB%s\xAB at line %d: %s\n", $name, @basename($file), $line, $message)));
};

$old_error_handler = set_error_handler("ErrorHandler");


// Los includes de la librería google-api son relativos, por lo que si no programamos
// en la misma carpeta de la libería no funcionarían.
// Solución: Cambiarmos la carpeta a partir de la que se hacen los includes en php con chdir()


define ('PATH_TO_API', '/Applications/XAMPP/xamppfiles/htdocs/ga-api/');
define ('PATH_TO_KEYFILE', 'ga-api-cc58bcca6921.p12');
define ('API_EMAIL_ADDRESS', '343781322834-52f61f9i4aaksgjhbog0cqeus5pkuntm@developer.gserviceaccount.com');
define ('API_CLIENT_ID', '343781322834-52f61f9i4aaksgjhbog0cqeus5pkuntm.apps.googleusercontent.com');
define ('API_APP_NAME', 'ga-api');
define ('GA_VIEW_ID', 'ga:83321718');
//chdir(PATH_TO_API);

// api dependencies
require_once(PATH_TO_API . 'Google/Client.php');
require_once(PATH_TO_API . 'Google/Service/Analytics.php');
require_once(PATH_TO_API . 'Google/Http/Batch.php');
/*
require_once('Google/Client.php');
require_once('Google/Service/Analytics.php');
require_once('Google/Http/Batch.php');
*/
// create client object and set app name
$client = new Google_Client();
$client->setApplicationName(API_APP_NAME); // name of your app



// set assertion credentials
$client->setAssertionCredentials(
  new Google_Auth_AssertionCredentials(

    API_EMAIL_ADDRESS, // email you added to GA

    array('https://www.googleapis.com/auth/analytics.readonly'),

    file_get_contents(PATH_TO_KEYFILE)  // keyfile you downloaded

));

// other settings
$client->setClientId(API_CLIENT_ID);           // from API console
$client->setAccessType('offline_access');  // this may be unnecessary?


##################


$client->setUseBatch(true);
$batch = new Google_Http_Batch($client);


$service = new Google_Service_Analytics($client);


$metrics = 'ga:sessions,ga:pageviews,ga:bounces,ga:entranceBounceRate,ga:visitBounceRate,ga:avgTimeOnSite';
$dimensions = 'ga:date,ga:year,ga:month,ga:day';

$optParams = array('dimensions' => $dimensions, 'filters' => $filters, 'segment' => $segment, 'sort' => $sort, 'max-results' => $maxresults, 'start-index' => $startindex);

$from = date('Y-m-d', time()-2*24*60*60); // 2 days
$to = date('Y-m-d'); // today

$req1  = $service->data_ga->get(GA_VIEW_ID, $from, $to, $metrics, $optParams);


//$req1 = $service->data_ga->get(GA_VIEW_ID, '2014-08-01', '2014-08-01', $params);
$batch->add($req1, "sessions");
$req2 = $service->data_ga->get(GA_VIEW_ID, '2014-08-01', '2014-08-01', 'ga:users');
$batch->add($req2, "users");



$results = $batch->execute();

header('Content-Type: application/json');
echo json_encode($results, true);
exit;


#################




echo "<pre>";
//print_r($results);
$data = $results;
echo "</pre>";

echo "<h3>Results Of Call 1:</h3>";

foreach ($data['response-sessions'] as $item) {
  echo "->".$item['totalsForAllResults']['ga:sessions'], "<br /> \n";

}
echo "<h3>Results Of Call 2:</h3>";
foreach ($results['response-users'] as $item) {
  echo $item['totalsForAllResults']['ga:users'], "<br /> \n";
}








###################














// create service and get data
$service = new Google_Service_Analytics($client);
//$service->data_ga->get($ids, $startDate, $endDate, $metrics, $optParams);
 
 $results = $service->data_ga->get('ga:90140478', '2014-08-01', '2014-09-01', 'ga:sessions');
 
// imprimimos el resultado para verlo

//var_dump($results);
//echo "{".$results['totalsForAllResults']['ga:sessions']."}";

header('Content-Type: application/json');
echo json_encode($results);
?>