<?php
include("db.php");
?>

<?php

function logid_to_tableid ( $log_id )
{
	$log_id = sprintf("%03d", $log_id);	
	return 'cqrlog' . $log_id;
}

function parse_remarks( $remarks, $field)
{


}

function get_cluster_spots ( $number, $band )
{		
		global $apitimeout;	
		//global $clusterurl;
		global $hamqthurl;
		$clusterurl= $hamqthurl . 'dxc_csv.php?';
		$ctx = stream_context_create(array('http'=>array('timeout' => $apitimeout,  )));

		if ($number != 0) {
				$clusterurl .= 'limit=' . $number;
		}
		else {
				$clusterurl .= 'limit=10';
		}
		if ($band != 'ALL') {
				$clusterurl .= '&band=' . $band;
		}
		$csvData = @file_get_contents( $clusterurl ,false, $ctx);
		$csvData = htmlentities($csvData);
		$csvData = str_replace('\^', '\ ^', $csvData);
		$csvData = str_replace('^', '"^"', $csvData);
		$csvData = preg_replace('/^/', '"', $csvData);
		$csvData = str_replace("\n", '"' .  "\n" . '"' , $csvData);
		$csvData = str_replace("^", ",", $csvData);
		$arrayData = array_map("str_getcsv", preg_split('/\r*\n+|\r+/', $csvData)) ;
		$devnull = array_pop($arrayData);
		
		if (!empty($arrayData)) {
				return $arrayData;
		}
		else {
				echo "Cluster timed out";
				$hamqthtimeout = true;
				return $arrayData;
		}
}

function call_to_dxcc ( $callsign) {
		global $hamqthurl;	
		global $hamqth_api;
		global $hamqthtimeout;
		if (empty($callsign)) {
				return array ( NULL, NULL, NULL, NULL );
		}
		// fallback to old call_to_dxcc withouth hamqth
		if (!$hamqth_api||$hamqthtimeout) {
		include("oldinclude.php");
				return call_to_dxcc2( $callsign);
		}
		$ctx = stream_context_create(array('http'=>array('timeout' => 12,  )));
		$jsonurl= $hamqthurl . 'dxcc_json.php?callsign=' . $callsign;
		$jsonData = @file_get_contents( $jsonurl, false, $ctx );
		$jsonData = str_replace('".', '",', $jsonData);
		$data = json_decode($jsonData,true); 
		$dxcc_adif = $data['adif'];
		$dxcc_name = $data['details'];
		$dxcc_itu = $data['itu'];
		$dxcc_waz = $data['waz'];
		// if timeout => fallback to old call_to_dxcc
		if (empty($dxcc_waz)) {
				//$hamqthtimeout=true;
				include("oldinclude.php");
				return call_to_dxcc2( $callsign);
		}

		return array ( $dxcc_adif, $dxcc_name, $dxcc_itu, $dxcc_waz );
}

function get_iota ( $call, $pref ) {
		
		global $dbconnect;
		
		$dbconnect -> select_db("cqrlog_common");
		$call = mysqli_real_escape_string($dbconnect ,$call);
		$ergebnis = mysqli_query($dbconnect, 'SELECT * FROM iota_list WHERE dxcc_ref="' . $pref . '" AND "' . $call . '" RLIKE CONCAT("^",pref) AND pref !=""'  );	
		
		while($row = mysqli_fetch_object($ergebnis)) {
			$iota_nr = $row->iota_nr;
			$iota_name = $row->island_name;
			return array ( $iota_nr, $iota_name );
		}
		
		return array ( NULL , NULL);
}

function count_qsos ( $log_id ) {
		
		global $where;
		global $dbconnect;

		$log_id = mysqli_real_escape_string($dbconnect ,$log_id);
		if (logid_to_call( $log_id)) {
				$dbconnect -> select_db( logid_to_tableid( $log_id ) );
				$ergebnis = mysqli_query($dbconnect, "SELECT COUNT(*) FROM view_cqrlog_main_by_qsodate " . $where );
				$result=$ergebnis->fetch_row();
				
				return $result[0];
		}
		
		else return NULL;

}

function logid_to_call ( $log_id ) {
		
		global $dbconnect;		
		$dbconnect -> select_db("cqrlog_common" );
		
		$log_id = mysqli_real_escape_string($dbconnect ,$log_id);
		$ergebnis = mysqli_query($dbconnect, "SELECT log_name FROM log_list WHERE log_nr=" . $log_id);
		while($row = mysqli_fetch_object($ergebnis)) {
				return $row->log_name;
		}
				
				return NULL;
}

function freq_to_band_mode ( $inputfreq ) {
				
		global $dbconnect;
		$dbconnect -> select_db("cqrlog_common");
		
		$inputfreq = mysqli_real_escape_string($dbconnect ,$inputfreq);
		$ergebnis = mysqli_query($dbconnect, "select cw,rtty,ssb,band from bands where b_begin <= " . $inputfreq . " and b_end >=" . $inputfreq  );
		
		while($row = mysqli_fetch_object($ergebnis)) {
				$returnband=$row->band;
				if ($inputfreq < $row->cw) {
						$returnmode = "CW";
				}
				
				else if (($inputfreq >= $row->rtty)&&($inputfreq < $row->ssb)) {
						$returnmode = "RTTY";
				}
				
				else if ($inputfreq > $row->ssb) {
						$returnmode = "SSB";
				}

				return array ($returnband, $returnmode);
		}

		return NULL ;
}


function freq_to_band ( $inputfreq ) {
		$bandmode = freq_to_band_mode($inputfreq);
		return $bandmode[0];
}


function freq_to_mode ( $inputfreq ) {
		
		$bandmode = freq_to_band_mode($inputfreq);
		return $bandmode[1];
}

function get_manager ( $call ) {

		global $dbconnect;		
		$dbconnect -> select_db("cqrlog_common" );
		
		$call = mysqli_real_escape_string($dbconnect ,$call);
		$ergebnis = mysqli_query($dbconnect, 'SELECT qsl_via FROM qslmgr WHERE callsign ="' . $call . '"');
		
		while($row = mysqli_fetch_object($ergebnis)) {
				return $row->qsl_via;
		}
		
		return NULL;
}

function band_to_freq ( $inputband ) {
		
		global $dbconnect;
		$dbconnect -> select_db("cqrlog_common");
		
		$inputband = mysqli_real_escape_string($dbconnect ,$inputband);
		$ergebnis = mysqli_query($dbconnect, "select b_begin from bands where band = '" . $inputband ."'"  );
		
		while($row = mysqli_fetch_object($ergebnis)) {
				return $row->b_begin;
		}

		return NULL;
}

function validate_freq ( $freq ) {
		
		switch ($freq) {
			//check for lazy WARC band frequency input
			case '10' :
				return band_to_freq( '30M' );
				break;
			
			case '18' :
				return band_to_freq( '17M' );
				break;
			
			case '24' :
				return band_to_freq( '12M' );
			
			case '' :
				return NULL;
			
			//Check if frequency input was in kHz, if true, convert to MHz
			default:
			if (!freq_to_band($freq)) {
					
					//kHz/1000 = MHz
					$newfreq=$freq/1000;
					
					if (freq_to_band( $newfreq )) {
							
							return $newfreq;
					}
			}
			
			return $freq;
			break;
		}
}

function dxcc_to_adif ( $dxcc ) {

		global $dbconnect;
		$dbconnect -> select_db("cqrlog_common");
	  
		$dxcc = mysqli_real_escape_string($dbconnect ,$dxcc);
		$ergebnis = mysqli_query($dbconnect, "select adif from dxcc_ref where pref = '" . $pref ."'"  );
		
		while($row = mysqli_fetch_object($ergebnis)) {
				
				return $row->adif;
		}

		return NULL;
}

function adif_to_dxcc ( $adif ) {
		
		global $dbconnect;
		$dbconnect -> select_db("cqrlog_common");
		
		$adif = mysqli_real_escape_string($dbconnect , $adif);
		$ergebnis = mysqli_query($dbconnect, "select pref from dxcc_ref where adif = '" . $adif ."'"  );
		
		while($row = mysqli_fetch_object($ergebnis)) {
				
				return $row->pref;
		}

		return NULL;
}

function qslstring ( $paper, $lotw, $eqsl ) {
		
		
		$qslstring ="";	
		
		if (($paper) && ($lotw) && ($eqsl)) {
				$qslstring=' and ( (qsl_r !="" ) OR (lotw_qslrdate IS NOT NULL) OR (eqsl_qslrdate IS NOT NULL) )';
		}

		elseif (($paper) && ($lotw)) {
				$qslstring=' and ( (qsl_r !="" ) OR (lotw_qslrdate IS NOT NULL) )';
		}

		elseif (($paper) && ($eqsl)) {
				$qslstring=' and ( (qsl_r !="" ) OR (eqsl_qslrdate IS NOT NULL) )';
		}

		elseif (($lotw) && ($eqsl)) {
				$qslstring=' and ( (lotw_qslrdate IS NOT NULL) OR (eqsl_qslrdate IS NOT NULL) )';
		}
		
		elseif ($lotw) {
				$qslstring=' and lotw_qslrdate IS NOT NULL ';
		}
		
		elseif ($eqsl) {
				$qslstring=' and eqsl_qslrdate IS NOT NULL ';
		}
		
		elseif ($paper) {
				$qslstring=' and qsl_r !="" ';
		}
	
		return $qslstring;
}


function check_dupe ( $log_id, $callsign, $band = 'ALL', $mode = 'ALL' ) {
		global $dbconnect;
		$dbconnect -> select_db (logid_to_tableid( $log_id ));

		$log_id = mysqli_real_escape_string($dbconnect ,$log_id);
		$band = mysqli_real_escape_string($dbconnect ,$band);
		$mode = mysqli_real_escape_string($dbconnect ,$mode);
		$callsign = mysqli_real_escape_string($dbconnect ,$callsign);
		

		
		$ergebnis = mysqli_query($dbconnect,	'select callsign from cqrlog_main where callsign="' . $callsign . '" and mode="' . $mode . '" and band="' . $band    . '" limit 1');
		
		while($row = mysqli_fetch_object($ergebnis)) {
		return true;
		}
		return false;
}

function check_adif ( $adif, $log_id, $band = 'ALL', $mode = 'ALL' , $paper = true , $lotw = true, $eqsl = true ) {
		
		global $dbconnect;
		$dbconnect -> select_db (logid_to_tableid( $log_id ));

		$adif = mysqli_real_escape_string($dbconnect ,$adif);
		$log_id = mysqli_real_escape_string($dbconnect ,$log_id);
		$band = mysqli_real_escape_string($dbconnect ,$band);
		$mode = mysqli_real_escape_string($dbconnect ,$mode);
		$paper = mysqli_real_escape_string($dbconnect ,$paper);
		$lotw = mysqli_real_escape_string($dbconnect ,$lotw);
		$eqsl = mysqli_real_escape_string($dbconnect ,$eqsl);

		$qslstring = qslstring( $paper, $lotw, $eqsl);
		if ($mode == "RTTY") {
				$mode = "DATA";
		}
		if ($band == "ALL") {
				$bandstring='';
		}
		
		else {
				$bandstring = ' and band="' . $band . '"';
		}

		$dbconnect -> select_db( logid_to_tableid( $log_id ) );
		switch ($mode) {
				case 'ALL' :
						$ergebnis = mysqli_query($dbconnect,	'select callsign from cqrlog_main where adif=' . $adif . $bandstring . $qslstring  . ' limit 1');
				break;
				case 'DATA' :
						$ergebnis = mysqli_query($dbconnect,	'select callsign from cqrlog_main where adif=' . $adif . $bandstring . ' and mode!="SSB" and mode !="CW" and mode !="FM"'  . $qslstring  . ' limit 1');
				break;
				default :
						$ergebnis = mysqli_query($dbconnect,	'select callsign from cqrlog_main where adif=' . $adif . $bandstring . ' and mode="' . $mode . '"'  . $qslstring  . ' limit 1');
		}
		
		while($row = mysqli_fetch_object($ergebnis)) {
				return array ("C", '<td align="center" class="success">',  '</td>');
	}
	
	switch ($mode) {
			case 'ALL' :
					$ergebnis2 = mysqli_query($dbconnect,	'select callsign from cqrlog_main where adif=' . $adif . $bandstring . ' limit 1');
					break;	
			case 'DATA' :
					$ergebnis2 = mysqli_query($dbconnect,	'select callsign from cqrlog_main where adif=' . $adif . $bandstring . ' and  mode!="SSB" and mode !="CW" and mode !="FM" limit 1');
					break;
	default:
			$ergebnis2 = mysqli_query($dbconnect,	'select callsign from cqrlog_main where adif=' . $adif . $bandstring . ' and mode="' . $mode . '" limit 1');
	}
	while($row = mysqli_fetch_object($ergebnis2)) {
			return array ('W', '<td class="danger" align="center" >','</td>');
	}

	return array ( 'N', '<td align="center">', '</td>' );
}

function count_dxcc ( $log_id, $band, $mode, $paper, $lotw, $eqsl ) {
		
		global $dbconnect;
		$dbconnect -> select_db (logid_to_tableid( $log_id ));

		$log_id = mysqli_real_escape_string($dbconnect ,$log_id);
		$band = mysqli_real_escape_string($dbconnect ,$band);
		$mode = mysqli_real_escape_string($dbconnect ,$mode);
		$paper = mysqli_real_escape_string($dbconnect ,$paper);
		$lotw = mysqli_real_escape_string($dbconnect ,$lotw);
		$eqsl = mysqli_real_escape_string($dbconnect ,$eqsl);

		$qslstring = qslstring($paper, $lotw, $eqsl);
		$querystring = 'select count(distinct adif) from cqrlog_main where adif<>0 ' ;

		if ($band != "ALL") {
				$querystring .= 'and band="' . $band .'" ';
		}
	 	
		if ($mode == "DATA") {
				$querystring .= 'and mode!="SSB" and mode!="CW" and mode!="FM" ';
		}
		else if ($mode != "ALL") {
				$querystring .= 'and mode="' . $mode .'" ';
		}
		
		$querystring .= $qslstring;
		
		$ergebnis = mysqli_query($dbconnect, $querystring );
		$result=$ergebnis->fetch_row();
		return $result[0];
}

function qso_to_adif( $qsodate, $timeon, $timeoff, $call, $mode, $freq, $band, $rst_sent, $rst_rcvd, $name, $comment, $qsl_sent, $qsl_rcvd, $qsl_via, $iota, $adif, $itu, $cqz, $state, $qslrdate, $qslsdate , $lotws, $lotwr, $lotwsdate, $lotwrdate, $eqsls, $eqslr, $eqslsdate, $eqslrdate  ) {

		$qso ='';

		if (!empty($qsodate)) {
				$qsodate = str_replace("-","",$qsodate);
				$qso .= '<QSO_DATE:8>' . $qsodate ;
		}

		if (!empty($timeon)) {
				$timeon = str_replace(":","",$timeon);
				$qso .= '<TIME_ON:4>' . $timeon ;
		}

		if (!empty($timeoff)) {
				$timeoff = str_replace(":","",$timeoff);
				$qso .= '<TIME_OFF:4>' . $timeoff ;
		}

		if (!empty($call)) {
				$qso .= '<CALL:' . strlen($call). '>' . $call ;
		}
		
		if (!empty($mode)) {
				$qso .= '<MODE:' . strlen($mode). '>' . $mode ;
		}
		
		if (!empty($freq)) {
				$qso .= '<FREQ:' . strlen($freq). '>' . $freq ;
		}
		
		if (!empty($band)) {
				$qso .= '<BAND:' . strlen($band). '>' . $band ;
		}
		
		if (!empty($rst_sent)) {
				$qso .= '<RST_SENT:' . strlen($rst_sent). '>' . $rst_sent ;
		}

		if (!empty($rst_rcvd)) {
				$qso .= '<RST_RCVD:' . strlen($rst_rcvd). '>' . $rst_rcvd ;
		}
		
		if (!empty($name)) {
				$qso .= '<NAME:' . strlen($name). '>' . $name ;
		}
		
		if (!empty($comment)) {
				$qso .= '<COMMENT:' . strlen($comment). '>' . $comment ;
		}
		
		if (!empty($qsl_sent)) {
				if ($qsl_sent == "D" OR $qsl_sent == "B")	{
						$qsl_sent ="Y";
				}
				
				else {
						$qsl_sent ="N";
				}
				
				$qso .= '<QSL_SENT:1>' . $qsl_sent ;
		}
		
		else {
				$qso .= '<QSL_SENT:1>N' ;
		}

		if (!empty($qsl_rcvd)) {
				$qsl_rcvd ="Y";
				$qso .= '<QSL_RCVD:1>' . $qsl_rcvd ;
		}
		
		else {

				$qso .= '<QSL_RCVD:1>N' ;
		}

		if (!empty($qsl_via)) {
				$qso .= '<QSL_VIA:' . strlen($qsl_via). '>' . $qsl_via ;
		}

		if (!empty($iota)) {
				$qso .= '<IOTA:' . strlen($iota) . '>' . $iota ;
		}

		if (!empty($adif)) {
				$qso .= '<DXCC:' . strlen($adif) . '>' . $adif ;
		}

		if (!empty($itu)) {
				$qso .= '<ITUZ:' . strlen($itu) . '>' . $itu ;
		}

		if (!empty($cqz))	{
				$qso .= '<CQZ:' . strlen($cqz) . '>' . $cqz ;
		}

		if (!empty($state))	{
				$qso .= '<STATE:' . strlen($state) . '>' . $state ;
		}

		if (!empty($qslsdate)) {
				$qslsdate = str_replace("-","",$qslsdate);
				$qso .= '<QSLSDATE:8>' . $qslsdate  ;
		}

		if (!empty($qslrdate)) {
				$qslrdate = str_replace("-","",$qslrdate);
				$qso .= '<QSLRDATE:8>' . $qslrdate ;
		}

		if (!empty($lotws)) {
				$qso .= '<LOTW_QSL_SENT:1>Y' ;
		}

		if (!empty($lotwsdate)) {
				$lotwsdate = str_replace("-","",$lotwsdate);
				$qso .= '<LOTW_QSLSDATE:8>' . $lotwsdate ;
		}

		if (!empty($lotwr)) {
				$qso .= '<LOTW_QSL_RCVD:1>Y' ;
		}

		if (!empty($lotwrdate))	{
				$lotwrdate = str_replace("-","",$lotwrdate);
				$qso .= '<LOTW_QSLRDATE:8>' . $lotwrdate ;
		}

		if (!empty($eqsls)) {
				$qso .= '<EQSL_QSL_SENT:1>Y' ;
		}

		if (!empty($eqslsdate)) {
				$eqslsdate = str_replace("-","",$eqslsdate);
				$qso .= '<EQSL_QSLSDATE:8>' . $eqslsdate ;
		}

		if (!empty($eqslr)) {
				$qso .= '<EQSL_QSL_RECEIVED:1>Y' ;
		}

		if (!empty($eqslrdate))	{
				$eqslrdate = str_replace("-","",$eqslrdate);
				$qso .= '<EQSL_QSLRDATE:8>' . $eqslrdate ;
		}

		$qso .=  '<EOR>';

		return $qso ;

		}


function get_search_count ($log_id) {
		
		$count_searches = ("searches.txt");
		$searches = file($count_searches);
		return $searches[$log_id-1];
}

function increment_search_count ($log_id) {
		
		$count_searches = ("searches.txt");
		$searches = file($count_searches);
		$newcount=$searches[$log_id-1]+1;
		array_splice($searches, ($log_id - 1), 1, $newcount . "\n");
		$string = implode("",$searches);
		file_put_contents("searches.txt", $string);
		return $newcount;
}

?> 
