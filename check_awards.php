<html>
<head>
<style>
body {
   background-color: white;
   font-family: Georgia, serif;
}

table {
   border-collapse: collapse;
}

table, th, td {
   border: 1px solid black;
}

</style>
<title>OMISS Awards Log Checker</title></head>
<body>

<?php

include 'adif_parser.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $callsign = test_input($_POST["callsign"]);
  $omnum = test_input($_POST["omnum"]);
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["adiflogfile"]["tmp_name"]);
$logfile_file = basename($_FILES["adiflogfile"]["name"]);
$upload_ok = 1;
$filegood = 0;

//echo $logfile_file . " \n";
//echo $target_file . " \n";

$logfile_ext = strtolower(pathinfo($logfile_file,PATHINFO_EXTENSION));

//echo $logfile_ext . " \n";

//check that file has a .adi or .adif extension
if (isset($_POST["submit"])) {
   if ($logfile_ext == "adi" || $logfile_ext == "adif") {
	$upload_ok = 1;
   } else {
	$upload_ok = 0;
	echo "Please upload only .adi or .adif ADIF logfile. \n";
   }
}

// Check if $upload_ok is set to 0 by an error
if ($upload_ok == 0) {
  echo "Sorry, your file was not uploaded. \n";
  $filegood = 0;
// if everything is ok, try to upload file
} else {
  if (move_uploaded_file($_FILES["adiflogfile"]["tmp_name"], $target_file)) {
    //echo "The file " . $logfile_file . " has been uploaded. \n";
    $filegood = 1;
  } else {
    echo "Sorry, there was an error uploading your file. \n";
    $filegood = 0;
  }
}


$p = new ADIF_Parser;
$p->load_from_file($target_file);
$p->initialize();

$log = array();

while ($record = $p->get_record()) {
   if(count($record) == 0) {
	break;
   }
   //echo $record["call"] . "  " . $record["qso_date"] . " \n"; 
   $log[] = $record;
}

usort($log, "om100");

?>
<center>OM International Sideband Society, Inc.<br />LOG REPORTING SHEET</center>
Contacts: CALL <?php echo $callsign; ?> OM# <?php echo $omnum; ?><br />
Award being submitted for: 100 OM Number - Red Award
<table>
<tr><th>Date</th><th>Time</th><th>Freq.</th><th>Station Worked</th><th>OM #</th><th>Additional Info</th></tr>
<?php
$n = "";
foreach ($log as $qso) {
   if ($qso["app_netlogger_clubmemberid"] != "" && substr($qso["app_netlogger_clubmemberid"], -2) != $n) {
	echo "<tr><td>" . $qso["qso_date"] . "</td><td>" . $qso["time_on"] . "</td><td>" . $qso["freq"] . "</td><td>" . $qso["call"] . "</td><td>" . $qso["app_netlogger_clubmemberid"] . "</td><td></td></tr> \n";
   }
   $n = substr($qso["app_netlogger_clubmemberid"], -2);
}
?>
</table>
</body>
</html>


<?php
function om100($a,$b){
      $lastTwoDigitsCmp=strcmp(substr($a["app_netlogger_clubmemberid"], -2), substr($b["app_netlogger_clubmemberid"], -2));
      if($lastTwoDigitsCmp==0)
          return strcmp($a["app_netlogger_clubmemberid"],$b["app_netlogger_clubmemberid"]);
      else
          return $lastTwoDigitsCmp;
}

//delete the logfile so the hard drive doesn't get full
unlink("$target_file");

?>
