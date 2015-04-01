	<table align="center" border="0">
	<td width="40">Band</td>
	<td width="10"></td>
	<td width="40">Time</td>
	<td width="10"></td>
	<td width="40">Frequency</td>
	<td width="10"></td>
	<td width="150">Callsign</td>
	<td width="10"></td>
	<td width="15">Mode</td>
	<td width="10"></td>
	<td width="15">RST_S</td>
	<td width="10"></td>
	<td width="15">RST_R</td>
	<td width="10"></td>
	<td width="150">Name</td>
	<td width="10"></td>
	<td width="150">Remarks</td>
<?php

$urlparameter = '?log_id=' . $log_id;

if (isset($qso_count)) {
		$urlparameter .= '&qso_count=' . $qso_count;
}

echo '<form name="input" action="log.php' . $urlparameter . '" method="post">';
?>
<tr>
	<td><select name="band" tabindex=1>
	<?php
	echo '<option>select</option>';
	$dbconnect -> select_db("cqrlog_main");
	$ergebnis = mysqli_query($dbconnect, "SELECT band FROM bands order by b_begin asc");
	while($row = mysqli_fetch_object($ergebnis))
	{
	$band_in = $row->band;
	echo '<option>' . $band_in . '</option>';
	}
	?>
	</select></td>
	<td></td>
	<td><input type="text" name="time" size="5" value="" maxlength="5" tabindex="1" title="optional, if empty: time in UTC, input format: hh:mm or hhmm"></td>
	<td></td>
	<?php
	if (isset($freq))
	{
	echo '<td><input type="text" maxlength="55" size="15" name="frequency" value="' . $freq . '" tabindex=1" title="only band or frequency is needed" ></td> ';
	}
	else
	{
	echo '<td><input type="text" maxlength="55" size="15" name="frequency" tabindex="1" value="" title="only band or frequency is needed" ></td> ';
	}
	?>
	<td></td>
	<td><input id="call" type="text" maxlength="55" size="15" name="call" tabindex="2" onchange="data_copy()" autofocus ></td>
	<td></td>
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
	echo '<td><input type="text" name="mode" size="5" value="' . $mode . '" maxlength="5"></td>';
	echo '<td></td>';
	echo '<td><input type="text" name="rst_sent" size="8" value="' . $rapport  . '" maxlength="10" tabindex="3"></td>';
	echo '<td></td>';
	echo '<td><input type="text" name="rst_rcvd" size="8" value="' . $rapport . '" maxlength="10" tabindex="4"></td>';
	echo '<td></td>';
	echo '<td><input type="text" name="name" maxlength="55" tabindex="5"></td>';
	echo '<td></td>';
	echo '<td><input type="text" name="remarks" maxlength="55" tabindex="6"></td>';
	echo '<td></td>';
	echo '<td><input type="submit" value="Submit"></td>';
	?>
</form>

<?php
	echo '<td></td>';
	echo '<form name="stats" action="stats.php' . $urlparameter . '" target="_blank"  method="post">';
	echo '<td><input type="submit" value="CheckDXCC"></td>';
?>
<input type="hidden" name="callsign" value="">
</form>
</tr>
</table>