<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();

	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";

	function write_command($cmd){
		global $oreon;
		$str = NULL;
		$str = "echo '[" . time() . "]" . $cmd . "\n' >> " . $oreon->Nagioscfg["command_file"];
		# print $str;
		return passthru($str);
	}

	function send_cmd($arg, $lang){
		if (isset($arg))
			$flg = write_command($arg);
		$flg ? $ret = $lang["cmd_send"] : $ret = "Problem Execution";
		return $ret;
	}
	
	// Re-Schedule for all service of an host
	
	function schedule_host_svc_checks($arg, $lang, $forced){
		global $pearDB;
		$tab_forced = array("0" => "", "1" => "_FORCED");
		$flg = write_command(" SCHEDULE".$tab_forced[$forced]."_HOST_SVC_CHECKS;" . $arg . ";" . time());
		return $flg;
	}
	
	// SCHEDULE_SVC_CHECK
	
	function schedule_svc_checks($arg, $lang, $forced){
		global $pearDB;
		$tab_forced = array("0" => "", "1" => "_FORCED");
		$tab_data = split(";", $arg);
		$flg = write_command(" SCHEDULE".$tab_forced[$forced]."_SVC_CHECK;". $tab_data[0] . ";" . $tab_data[1] . ";" . time());
		return $lang["cmd_send"];
	}
	
	// host check
	
	function host_check($arg, $lang, $type){
		global $tab, $pearDB;
		$flg = write_command(" ". $tab[$type]."_HOST_CHECK;". $arg);
		return $lang["cmd_send"];
	}
	
	//  host notification
	
	function host_notification($arg, $lang, $type){
		global $tab, $pearDB;
		$flg = write_command(" ".$tab[$type]."_HOST_NOTIFICATIONS;". $arg);
		return $lang["cmd_send"];
	}
	
	// ENABLE_HOST_SVC_NOTIFICATIONS
	
	function host_svc_notifications($arg, $lang, $type){
		global $tab, $pearDB;
		$flg = write_command(" " . $tab[$type] . "_HOST_SVC_NOTIFICATIONS;". $arg);
		return $lang["cmd_send"];
	}
	
	// ENABLE_HOST_SVC_CHECKS
	
	function host_svc_checks($arg, $lang, $type){
		global $tab, $pearDB;
		$flg = write_command(" " . $tab[$type] . "_HOST_SVC_CHECKS;". $arg);
		return $lang["cmd_send"];
	}
	
	// ENABLE_HOST_SVC_CHECKS
	
	function svc_check($arg, $lang, $type){
		global $tab, $pearDB;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_SVC_CHECK;". $tab_data["0"] .";".$tab_data["1"]);
		return $lang["cmd_send"];
	}
	
	// PASSIVE_SVC_CHECKS
	
	function passive_svc_check($arg, $lang, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_PASSIVE_SVC_CHECKS;". $tab_data[0] . ";". $tab_data[1]);
		return $lang["cmd_send"];
	}
	
	// SVC_NOTIFICATIONS
	
	function svc_notifications($arg, $lang, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_SVC_NOTIFICATIONS;". $tab_data[0] . ";". $tab_data[1]);
		return $lang["cmd_send"];
	}
	
	// _SVC_EVENT_HANDLER
	
	function svc_event_handler($arg, $lang, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_SVC_EVENT_HANDLER;". $tab_data[0] .";".$tab_data[1]);
		return $lang["cmd_send"];
	}
	
	// _HOST_EVENT_HANDLER
	
	function host_event_handler($arg, $lang, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_HOST_EVENT_HANDLER;". $arg);
		return $lang["cmd_send"];
	}
	
	//_SVC_FLAP_DETECTION
	
	function svc_flapping_enable($arg, $lang, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_SVC_FLAP_DETECTION;". $tab_data[0] .";".$tab_data[1]);
		return $lang["cmd_send"];
	}
	
	//_HOST_FLAP_DETECTION
	
	function host_flapping_enable($arg, $lang, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_HOST_FLAP_DETECTION;". $arg);
		return $lang["cmd_send"];
	}
	
	function notifi_host_hostgroup($arg, $lang, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_HOST_NOTIFICATIONS;". $tab_data[0]);
		return $lang["cmd_send"];
	}
	
	function acknowledgeHost($lang){
		global $pearDB,$tab, $_GET;
		$flg = write_command(" ACKNOWLEDGE_HOST_PROBLEM;".$_GET["host_name"].";1;".$_GET["notify"].";".$_GET["persistent"].";".$_GET["author"].";".$_GET["comment"]);
		return $lang["cmd_send"];
	}
	
	function acknowledgeHostDisable($lang){
		global $pearDB,$tab, $_GET;
		$flg = write_command(" REMOVE_HOST_ACKNOWLEDGEMENT;".$_GET["host_name"]);
		return $lang["cmd_send"];
	}
	
	function acknowledgeServiceDisable($lang){
		global $pearDB,$tab;
		$flg = write_command(" REMOVE_SVC_ACKNOWLEDGEMENT;".$_GET["host_name"].";".$_GET["service_description"]);
		return $lang["cmd_send"];
	}

	function acknowledgeService($lang){
		global $pearDB,$tab;
		$flg = write_command(" ACKNOWLEDGE_SVC_PROBLEM;".$_GET["host_name"].";".$_GET["service_description"].";1;".$_GET["notify"].";".$_GET["persistent"].";".$_GET["author"].";".$_GET["comment"]);
		return $lang["cmd_send"];
	}

	function submitPassiveCheck($lang){
		global $pearDB;
		$flg = write_command(" PROCESS_SERVICE_CHECK_RESULT;".$_GET["host_name"].";".$_GET["service_description"].";".$_GET["return_code"].";".$_GET["output"]."|".$_GET["dataPerform"]);
		return $lang["cmd_send"];
	}
	
	
	function notifi_svc_host_hostgroup($arg, $lang, $type){
		global $tab, $pearDB;
	/*	$res =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$arg."'");
		while ($r =& $res->fetchRow()){
			$resH =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$r["host_host_id"]."'");
			$rH =& $resH->fetchRow();
			$flg = write_command(" " . $tab[$type] . "_HOST_NOTIFICATIONS;". $rH["host_name"]);
		}
	*/
		return $lang["cmd_send"];
	}
	
	function checks_svc_host_hostgroup($arg, $lang, $type){
		global $tab, $pearDB;
		/*$res =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$arg."'");
		$r =& $res->fetchRow();
		$flg = write_command(" " . $tab[$type] . "_HOST_SVC_CHECKS;". $rH["host_name"]);
		*/
		return $lang["cmd_send"];
	}
	
	
?>