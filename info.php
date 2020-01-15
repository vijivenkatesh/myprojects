<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>MERP AWS Production</title>

</head>

<br>
<?php

function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

if(get_client_ip() == '61.69.65.146'){

    echo get_client_ip();


    echo "<br>";


    $host= gethostname();
    $localip = gethostbyname($host);
    echo $localip;
    echo "<br><br>";
    echo date("Y-m-d H:i:s");
    ?>
    <br>
<?php
SESSION_START();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>

<?php echo phpinfo(); ?>
    <script type="text/javascript" id="la_x2s6df8d" src="https://support.merp.org/scripts/track.js"></script>
    <img src="http://support.merp.org/scripts/pix.gif" onLoad="LiveAgentTracker.createForm('1bbaea99', this);"/>

    <script type="text/javascript" id="la_x2s6df8d" src="https://support.merp.org.au/scripts/track.js"></script>
    <img src="https://support.merp.org/scripts/pix.gif" onLoad="LiveAgentTracker.createButton('b10c4de8', this);"/>


<?php }else{

$host= gethostname();
$localip = gethostbyname($host);
echo $localip;
echo "<br><br>";
echo date("Y-m-d H:i:s");

} ?>
</body>
</html>