<?php
//error_reporting(-1);
//ini_set('display_errors', 'On');

// Load the AWS SDK for PHP
require __DIR__ . '/aws.phar';

use Doctrine\Common\Cache\FilesystemCache;
use Guzzle\Cache\DoctrineCacheAdapter;

// Create a cache adapter that stores data on the filesystem
$cacheAdapter = new DoctrineCacheAdapter(new FilesystemCache('/tmp/cache'));

// Create a new Amazon SNS client

$sns = Aws\Sns\SnsClient::factory(array(
    'credentials.cache' => $cacheAdapter,
    'region' => 'ap-southeast-1'
));

// Create a new Amazon SNS client
/*
$sns = Aws\Sns\SnsClient::factory(array(
    'key'    => 'Aaaa',
    'secret' => 'Bbbb',
    'region' => 'ap-southeast-1'
));
*/

// Get and display the platform applications
$Model1 = $sns->listPlatformApplications();
/*
echo("<p><i>List of applications:</i></p>\n");
foreach ($Model1['PlatformApplications'] as $App)
{
  echo("<p>" . $App['PlatformApplicationArn'] . "</p>\n");
}
echo("\n");
*/
// Get the Arns of the first 2 applications
$AppArn = $Model1['PlatformApplications'][0]['PlatformApplicationArn'];
// $AppArn2 = $Model1['PlatformApplications'][1]['PlatformApplicationArn'];

// Get the application's endpoints
$Model2 = $sns->listEndpointsByPlatformApplication(array('PlatformApplicationArn' => $AppArn));
//$Model3 = $sns->listEndpointsByPlatformApplication(array('PlatformApplicationArn' => $AppArn2));

// Display all of the endpoints for the first application
//echo("<p><i>List of endpoints:</i></p>\n");
foreach ($Model2['Endpoints'] as $Endpoint)
{
  $EndpointArn = $Endpoint['EndpointArn'];
  //echo("<p>" . $EndpointArn . "</p>\n");
}

// Display all of the endpoints for the second application
/*
foreach ($Model3['Endpoints'] as $Endpoint)
{
  $EndpointArn = $Endpoint['EndpointArn'];
  echo("<p>" . $EndpointArn . "</p>\n");
}
*/
if($_POST['formSubmit'] == "Submit")
{
  $errorMessage = "";
  
  if(empty($_POST['formMessage']))
  {
    $errorMessage .= "<li>Please enter a message text!</li>";
  }
   
  $varMessage = $_POST['formMessage'];

if(empty($errorMessage)) 
{
  // Send a message to each endpoint
  echo("<p><b>Sending to all endpoints: " . $varMessage . "</b></p>\n");
  foreach ($Model2['Endpoints'] as $Endpoint)
  {
    $EndpointArn = $Endpoint['EndpointArn'];

    try
    {
      $sns->publish(array('Message' => $varMessage,
			 'TargetArn' => $EndpointArn));

      echo("<p>" . $EndpointArn . "<b> - Succeeded!</b></p>\n");
    }
    catch (Exception $e)
    {
      echo("<p>" . $EndpointArn . " - Failed: " . $e->getMessage() . "!</p>\n");
    }
  }
/*
  foreach ($Model3['Endpoints'] as $Endpoint)
  {
    $EndpointArn = $Endpoint['EndpointArn'];

    try
    {
      $sns->publish(array('Message' => $varMessage,
       'TargetArn' => $EndpointArn));

      echo("<p>" . $EndpointArn . "<b> - Succeeded!</b></p>\n");
    }
    catch (Exception $e)
    {
      echo("<p>" . $EndpointArn . " - Failed: " . $e->getMessage() . "!</p>\n");
    }
  }
*/
}
}



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html>
<head>
  <title>Amazon SNS / Live Streaming Demo Web</title>
 <!-- Flowplayer depends on jquery -->
<link rel="stylesheet" href="//releases.flowplayer.org/5.4.6/skin/minimalist.css">
<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="http://releases.flowplayer.org/5.4.6/flowplayer.min.js"></script>
 
<script>
    // display information about the video type
    flowplayer(function (api) {
               api.bind("load", function (e, api, video) {
                        $("#vtype").text(video.type);
                        });
               });
</script>
</head>

<body>
  <?php
    if(!empty($errorMessage)) 
    {
      echo("<p>There was an error with your form:</p>\n");
      echo("<ul>" . $errorMessage . "</ul>\n");
    } 
  ?>
  <form action="index.php" method="post">
    <p>
      Notification Message:<br>
      <input type="text" name="formMessage" maxlength="50" value="Hello!" />
    </p>
    <input type="submit" name="formSubmit" value="Submit" />
  </form>
     <div class="flowplayer" style="width: 320px; height: 568px;" >
                <video>
                    <source type="application/x-mpegurl" src="http://d1g5u3al4rzyct.cloudfront.net/livecf/myStream/playlist.m3u8">
                </video>
     </div>
  <img src="bottom.png" alt="AWS Logo">
</body>
</html>
