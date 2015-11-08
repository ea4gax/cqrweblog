<?php
include("config_defaults.php");
include("config.php");
include("inc/header.php");
include("inc/parse_stats2.php");
?>

<!DOCTYPE html> 
<html>
<head>
<?php 
echo '<title>' . strtoupper(logid_to_call($log_id)) . ' \'s DXCC statistics</title>';
include("inc/metaheader.php");
?>
</head>
<body>

<?php
$statsactive=true;
include("inc/navbar.php");
echo '<h1 align="center">DXCC statistics of ' . strtoupper(logid_to_call($log_id)) . '</h1><br /><br />';
include("inc/stats_input2.php");
?>
<br /><br />

<?php
include("inc/stats_proc2.php");
include("inc/dxccstats2.php");
include("inc/metafooter.php");?>

</body>
</html>
