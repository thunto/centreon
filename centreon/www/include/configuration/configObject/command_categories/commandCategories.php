<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
 
	if (!isset ($oreon))
		exit ();

	isset($_GET["cc_id"]) ? $cG = $_GET["cc_id"] : $cG = NULL;
	isset($_POST["cc_id"]) ? $cP = $_POST["cc_id"] : $cP = NULL;
	$cG ? $cc_id = $cG : $cc_id = $cP;
		
	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	/*
	 * Pear library
	 */
	require_once 'HTML/QuickForm.php';
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	/*
	 * 	Path to the configuration dir
	 */
	$path = "./include/configuration/configObject/command_categories/";

	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once './include/common/common-Func.php';

	switch ($o)	{
		case "mc" : 
			require_once($path."formCommandCategories.php"); 
			break;
		case "a" : 
			require_once($path."formCommandCategories.php"); 
			break;
		case "w" : 
			require_once($path."formCommandCategories.php"); 
			break;
		case "c" : 
			require_once($path."formCommandCategories.php"); 
			break;
		case "m" : 
			multipleCommandCategorieInDB(isset($select) ? $select : array(), $dupNbr); 
			require_once($path."listCommandCategories.php"); 
			break;
		case "d" : 
			deleteCommandCategorieInDB(isset($select) ? $select : array()); 
			require_once($path."listCommandCategories.php"); 
			break;
		default : 
			require_once($path."listCommandCategories.php"); 
			break;
	}
?>