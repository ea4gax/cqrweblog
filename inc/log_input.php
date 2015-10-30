<?php
$urlparameter = '?log_id=' . $log_id;

if (isset($qso_count)) {
		$urlparameter .= '&qso_count=' . $qso_count;
}

echo '<form class="form" role="form" name="input" action="index.php' . $urlparameter . '" method="post">';
?> 
	<div class="row ">
	<div class="form-group col-sm-1 " >
	<label for="band" class="control-label">Band</label>
	<select class="form-control" id="band" name="band" tabindex=1>
	<?php
	echo '<option>select</option>';
	$dbconnect -> select_db("cqrlog_common");
	$ergebnis = mysqli_query($dbconnect, "SELECT band FROM bands order by b_begin asc");
	while($row = mysqli_fetch_object($ergebnis))
	{
	$band_in = $row->band;
	echo '<option>' . $band_in . '</option>';
	}
	?>
	</select>
	</div>
	<div class="form-group col-sm-1 ">
    <label for="time" class="control-label" >Time</label>
	<input id="time" class="form-control" placeholder="HHMM" onClick="this.setSelectionRange(0,this.value.length)" type="text" name="time" size="5" value="" maxlength="5" tabindex="1" data-toggle="tooltip" title="optional, if empty: time in UTC, input format: hh:mm or hhmm">
</div>
  <div class="form-group col-sm-1 <?php if (!isset($freq)) { echo "has-error has-feedback" ; }  ?>">	
    <label for="freq" class="control-label">Frequency</label>
	<?php
	if (isset($freq))
	{
	echo '<input onClick="this.setSelectionRange(0,this.value.length)" class="form-control" id="freq" type="text" maxlength="55" size="15" name="frequency" value="' . $freq . '" tabindex=1" data-toggle="tooltip" title="only band or frequency is needed" > ';
	}
	else
	{
	echo '<input  onClick="this.setSelectionRange(0,this.value.length)" class="form-control" id="freq" type="text" maxlength="55" size="15" name="frequency" tabindex="1" value="" data-toggle="tooltip"  title="only one of band or frequency is needed" >';
	echo '<span class="glyphicon glyphicon-remove form-control-feedback"></span>';
	}
	?>
	</div>
  <div class="form-group col-sm-2 <?php if (empty($call)) { echo "has-error has-feedback" ; }  ?>">
    <label for="call" class="control-label">Callsign</label>
	<input id="call" class="form-control	" type="text" maxlength="55" size="15" name="call" tabindex="2" onchange="data_copy()" autofocus >
	<?php if (empty($call)) { echo' <span class="glyphicon glyphicon-remove form-control-feedback"></span>';} ?>
	</div>
  <div class="form-group col-sm-1 ">
    <label for="mode">Mode</label>
	<?php
	if (empty($mode))
			{
					$mode=$defaultmode;
			}
	if ($mode == "CW")
	{
			$rapport=$default_cw_rapport;
	}
	elseif ($mode=="SSB")
	{
			$rapport=$default_ssb_rapport;
	}
	else {
			$rapport=$default_cw_rapport;
	}
	?>
		<input onClick="enable_space(); this.setSelectionRange(0,this.value.length); enable_space();" id="mode" class="form-control" type="text" name="mode" size="5" value="<?php echo $mode ;?>" tabindex="3" maxlength="7">
	</div>

	
	<div class="form-group col-sm-1">
			<label for="rst_sent" class="control-label">RST_S</label>
			<input onClick="this.setSelectionRange(0,this.value.length)" type="text" class="form-control" id="rst_sent" name="rst_sent" size="8" value="<?php echo $rapport ;?>  " maxlength="10" tabindex="4">
	</div>

	
	<div class="form-group col-sm-1 ">
			<label for="rst_rcvd" class="control-label">RST_R</label>
			<input onClick="this.setSelectionRange(0,this.value.length)" type="text" class="form-control" id="rst_rcvd" name="rst_rcvd" size="8" value="<?php echo $rapport; ?>" maxlength="10" tabindex="5">
	</div>
	
	<div class="form-group col-sm-1 ">
			<label for="name">Name</label>
			<input onClick="this.setSelectionRange(0,this.value.length)" type="text" class="form-control" id="name" name="name" maxlength="55" tabindex="6">
	</div>
	
	<div class="form-group col-sm-2 ">
			<label for="remarks">Remarks</label>
			<input onClick="this.setSelectionRange(0,this.value.length)" type="text" class="form-control" id="remarks" name="remarks" maxlength="55" data-toggle="tooltip" title="I:IOTA L:LOCATOR M:MANAGER S:STATE" tabindex="7"></div>
	</div>
	<div class="row">
			<div class="col-sm-10"></div>
			<input class="btn btn-primary col-sm-1 " type="submit" value="Log">
	</div>
	</form>


</div>
</div>
<div class="row">
	<div class="col-sm-3">

<?php
if ($enable_help[$log_id]) {
	?>
	<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#help">Show help</button>
  <div id="help" class="collapse">
	Wipe: <kbd>Alt+w</kbd> <kbd>F11</kbd></br>
	QRZ: <kbd>Alt+q</kbd> </br>
	Remarks-Strings: I:IOTA L:LOCATOR M:MANAGER S:STATE</br>
	<a href="./README.txt" target="_blank" >Complete Manual</a>
	</div>
	<?php
}
?>
		</div>
		<div class="col-sm-1">
<?php
	if ($altstats[$log_id])
	{
			echo '<form name="stats" action="stats2.php' . $urlparameter . '" target="_blank"  method="post">';
	}
	else
	{
			echo '<form name="stats" action="stats.php' . $urlparameter . '" target="_blank"  method="post">';
	}
	?>
	<input class="btn btn-info" type="submit" value="Check DXCC" accesskey="C" data-toggle="tooltip" title="shortcut browser accesskey + C">
	<input type="hidden" name="callsign" value="">
	<input type="hidden" name="inlog" value="1"> 
		</form></div>
		<div class="col-sm-1"></div>
				<div class="col-sm-1">
					<form name="search" action="logsearch.php<?php echo $urlparameter ;?> " target="_blank"  method="post">
						<input class="btn btn-info " type="submit" value="In log?" accesskey="S" data-toggle="tooltip" title="shortcut browser accesskey + S" >
						<input type="hidden" name="call" value=""> 
						<input type="hidden" name="inlog" value="1">
					</form>
				</div>

				<div class="col-sm-1"></div>
				<input class="btn btn-info col-sm-1" data-toggle="tooltip" type="submit" value="QRZ?" onclick="qrz_call()" title="shortcut: Alt+q">
				<div class="col-sm-2"></div>
				<input class="btn btn-warning col-sm-1" data-toggle="tooltip" type="submit" value="Cancel" onclick="wipe_data()" title="shortcut: Alt+w/F11">
</div>
