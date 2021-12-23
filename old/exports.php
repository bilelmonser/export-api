<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

define("ACTIVATE_VERBOSE",1);
define("LOGDIR", dirname(__FILE__)."/dossier-pas-sauvegardé/log");
if(!is_dir(LOGDIR)) die("Merci de creer le repertoire de log".LOGDIR."\n");
define("LOGPATH", LOGDIR."/".$_GET['base']."_exports_".date("Ymd").".log");
$logpath=LOGPATH;

define("CURLTRACE", dirname(__FILE__)."/dossier-pas-sauvegardé/curl_traces");
define("PROD_URL", "https://www.cabinet-expertcomptable.com/Facture/reception-banque");
define("DB_LOGIN", 'facnote_read');
define("DB_PASS", 'EbK6PdNtn@BHsRGJn');
define("DB_HOST", '149.202.77.227');
define("DB_NAME", 'facnote_v3');

// php -l core/exports.php; scp -P 26022 core/exports.php bankutils@ns3061051.ip-193-70-44.eu:/home/bankutils/.;
// sud cp exports.php /var/www/prospect.cabinet-expertcomptable.com/www/
// curl -F 'content=</Applications/MAMP/htdocs/V2/core/content.txt' 'https://prospect.cabinet-expertcomptable.com/exports.php?action=constructexport&base=FA0907&comptable=FA0766&action=constructexport&dos=002&debut_exercice=01-01-2021&fin_exercice=31-12-2021&id_exp=123456&long_cpt=6&long_aux=6&cpt_gen=401000&trace=1&exportmode=32' ;
// curl -F "irfToken=G07Sgi7L9oc134MBy9+bgwTVRvCC87bJcJoZxcISYOXYZydlv4syR9QVoVV/rWpQnxVsLVqV8Si0a1GIct+o45jaj3VgZ68DtROI2ApoWASjtEJ1EAgRkwULMmEY8FxPSgMvG0er0yRXtyCa7wllVT+Dz/MPvpiqT70j7yLLunpaxRkFr2KtGhLtg781zJzoEV3i64Y2eoZ5F9ulVGEWdxo4qX3cd19D/gBr6roLLn6q5UUuqtDPQhurmNNyZXjX6/YHgY72lkqqg4RXYi9L2G7sHgcQI5T2SsIO4Js9xoVsbmQhTjpBw61nQYLax5ZMCiXzJL/jtaPJQrKfEtmDaA==" -F 'content=</Applications/MAMP/htdocs/V2/core/content.txt' 'https://prospect.cabinet-expertcomptable.com/exports.php?action=constructexport&base=FB0078&comptable=FA0766&dos=B0BE3918-E69D-4931-8110-8F364FFA5A51&debut_exercice=01-01-2021&fin_exercice=31-12-2021&id_exp=123456&long_cpt=6&long_aux=6&cpt_gen=401000&trace=1&export_manuel=1&exportmode=34&verbose=0';
/*
A tester avec
20,ACD ok: un zip contenant un seul fichier <NUMDOSSIER>IN
44,ACD GED ok: le fichier ZIP exporté doit avoir le nom <NUMDOSSIER>IN.auto (sans extension .ZIP) et contenir le fichier d'ecritures <NUMDOSSIER>IN avec les pdf
32,AGIRIS ok
33,CEGID  ok
24,COGILOG ok
40,EBP ok
7,EIC ok
34,IBIZA ok
42,Mittler ok
2,QUADRATUS ok
41,SAGE ok
45,SAGE 100 ok
37,Soddec ok
38,Wcompta ok
*/

// librairie commune

function my_filesize($file_path, $kilo=0) {
  $filesize=0;

  if(file_exists($file_path)){
    if($kilo==1) $filesize = filesize($file_path)/1024;
    else $filesize = filesize($file_path);
  }
  return $filesize;

}

function get_ibiza_server($irftoken, $base, $comptable)  {

  $curl_cmd = "curl -s --location --request GET 'https://production-api.fulll.io/irfservice/services/irfservice.svc/endpoint' --header 'IRF-PARTNERID: 96AB1027-FF1A-4189-A851-F78A61C6BA37' --header 'IRF-TOKEN: $irftoken'";
  list($output, $status) = launch_system_command($curl_cmd,0,1);



  $message='';
  $server='https://saas.irf-cloud.com';
  $p = xml_parser_create();
  xml_parse_into_struct($p, implode("\n", $output), $vals, $index);
  xml_parser_free($p);
  for($idx=0; $idx<count($vals); $idx ++){
    //print_r($vals[$idx]);
    if($vals[$idx]['tag'] == 'DATA'){
      if(isset($vals[$idx]['value'])) $server=trim($vals[$idx]['value']);
    }
    if($vals[$idx]['tag'] == 'MESSAGE'){
      if(isset($vals[$idx]['value'])) $message=trim($vals[$idx]['value']);
    }
  }

  // token de test sur ibiza test: https://beta.irf-cloud.com/ (wsfacnote/twhsmf1a3c)
  // G07Sgi7L9oc134MBy9+bgwTVRvCC87bJcJoZxcISYOXYZydlv4syR9QVoVV/rWpQnxVsLVqV8Si0a1GIct+o45jaj3VgZ68DtROI2ApoWASjtEJ1EAgRkwULMmEY8FxPSgMvG0er0yRXtyCa7wllVT+Dz/MPvpiqT70j7yLLunpaxRkFr2KtGhLtg781zJzoEV3i64Y2eoZ5F9ulVGEWdxo4qX3cd19D/gBr6roLLn6q5UUuqtDPQhurmNNyZXjX6/YHgY72lkqqg4RXYi9L2G7sHgcQI5T2SsIO4Js9xoVsbmQhTjpBw61nQYLax5ZMCiXzJL/jtaPJQrKfEtmDaA==G07Sgi7L9oc134MBy9+bgwTVRvCC87bJcJoZxcISYOXYZydlv4syR9QVoVV/rWpQnxVsLVqV8Si0a1GIct+o45jaj3VgZ68DtROI2ApoWASjtEJ1EAgRkwULMmEY8FxPSgMvG0er0yRXtyCa7wllVT+Dz/MPvpiqT70j7yLLunpaxRkFr2KtGhLtg781zJzoEV3i64Y2eoZ5F9ulVGEWdxo4qX3cd19D/gBr6roLLn6q5UUuqtDPQhurmNNyZXjX6/YHgY72lkqqg4RXYi9L2G7sHgcQI5T2SsIO4Js9xoVsbmQhTjpBw61nQYLax5ZMCiXzJL/jtaPJQrKfEtmDaA==
  // base de test


  $result = implode(" ", $output);
  if(preg_match('/.result.Error..result./', $result)) $server = "Erreur Ibiza: \n$message\nurl:\n$curl_cmd\n";
  else {

    if(($base =='FB0076')||($comptable =='FB0075')) $server='https://beta.irf-cloud.com';

    $server = "$server/IRFService/services/IRFService.svc";
  }
  //file_put_contents(LOGDIR."/ibiza_serveur.txt", "$base $comptable $server\n", FILE_APPEND);
  verbose_str_to_file(__FILE__, __FUNCTION__, "url:\n$curl_cmd\nbase $base server ibiza:\n$server");

  return($server);

}

function date_mysql_to_html($sql_date, $set_today=1, $explode=0, $mobile=0, $add_hour=0,$str_pad=0){

  global $MOIS;
  $html_date = "";

  if(preg_match('/\-/', $sql_date)){
    $tmp_array = explode(' ', $sql_date);
    $date = $tmp_array[0];
    //$hour = $tmp_array[1];
    $array_date = explode('-', $date);
    //$array_hour = explode(':', $hour);
    if(count($array_date)>1){
      if($str_pad==1){
        $array_date[2] = str_pad($array_date[2], 2, "0", STR_PAD_LEFT);
        $array_date[1] = str_pad($array_date[1], 2, "0", STR_PAD_LEFT);
      }
      $html_date = $array_date[2]."/".$array_date[1]."/".$array_date[0];
      if($add_hour==1) $html_date .= " ".$hour;
      if($mobile==1) $html_date = $array_date[0].'-'.$array_date[1].'-'.$array_date[2];
    }
  } else $html_date=$sql_date;

  if(Is_empty_str($sql_date)) {
    if($set_today) {
      $html_date = date("d/m/Y");
      $array_date[2] = date("d");
      $array_date[1] = date("m");
      $array_date[0] = date("Y");
      if($mobile==1) $html_date =  date("Y-m-d");;
    } else {
      $html_date = "";
      $array_date[2] = null;
      $array_date[1] = null;
      $array_date[0] = null;
    }
  }

  if($explode) {
    //verbose_str_to_file(__FILE__, __FUNCTION__, "Get sql_date $sql_date Return array: ".print_r($array_date,1));
    if(isset($array_date) && (count($array_date)>1))
      return array((int)$array_date[2], (int)$array_date[1], (int)$array_date[0]);
    else return array();
  }
  else {
    //verbose_str_to_file(__FILE__, __FUNCTION__, "Get sql_date $sql_date Return html_date $html_date");
    return $html_date;
  }
}
function formater_montant($html_montant, $to_display=0, $neg_red=0, $pos_blue=0, $virgule=0, $nb_Dec=2){


	if(($html_montant=="")||($html_montant==null)) $html_montant=0;
	$montant = str_replace(" ","", $html_montant);
	$montant = str_replace('/\s/',"", $montant);
	$montant = preg_replace('/\s/',"", $montant);
	$montant = str_replace(",",".", $montant);
	$montant = str_replace("%","", $montant);
	$montant = round($montant, 6);
	$tmp_arr = str_split($montant,1);
	$res_num="";
	foreach($tmp_arr as $car) {
		if(preg_match('/[0-9]/', $car)) $res_num .= $car;
		if(preg_match('/\./', $car)) $res_num .= $car;
		if(preg_match('/\-/', $car)) $res_num .= $car;
		//verbose_str_to_file(__FILE__, __FUNCTION__, "Montant recu:$html_montant Montant retour:$montant");
	}

	$montant = number_format(floatval($res_num), $nb_Dec);

	if($to_display)$montant = str_replace(","," ", $montant);
	else $montant = str_replace(",","", $montant);

	if($neg_red && ($html_montant<0)) $montant = '<font color="red">'.$montant.'</font>';
	if($pos_blue && ($html_montant>0)) $montant = '<B>'.$montant.'</B>';
	if($virgule) $montant = str_replace(".",",", $montant);

	//verbose_str_to_file(__FILE__, __FUNCTION__, "Montant recu:$html_montant Montant retour:$montant");
	return $montant;
}

function write_roll_logfile($file, $str, $size) {
  $entete = "\n\n************************************* roll_logfile ".date('d/m/Y H:i:s')." *************************************\n\n";
  if(my_filesize($file,1) > $size) {
    $premieres_lignes=file_get_contents($file);
    $premieres_lignes=substr($premieres_lignes, 0, ($size*1024)-strlen($str) )."\n";
  }
  else if(my_filesize($file,1) > 0) $premieres_lignes=file_get_contents($file);
  else $premieres_lignes="";

  file_put_contents($file, $entete.$str.$premieres_lignes);
}
function verbose_str_to_file($file_occured, $function_occured, $verbose_str) {

  global $logpath;
  file_put_contents($logpath, date('H:i:s').":$function_occured => $verbose_str", FILE_APPEND);

}

function rm_file($path_to_rm) {
  if(preg_match('?^\s*/\s*$?', $path_to_rm) ||
     preg_match('?/\s*$?', $path_to_rm) ||
     preg_match('?^\s*\*\s*$?', $path_to_rm) ||
     preg_match('?^\s*/\*\s*$?', $path_to_rm) ||
     preg_match('?^\s*$?', $path_to_rm)||
     ($path_to_rm=="")||
     ($path_to_rm==".")||
     ($path_to_rm=="..")||
     ($path_to_rm=="/")||
     ($path_to_rm=="/*")||
     ($path_to_rm=="*")||
     ($path_to_rm==null)){
    die("rm_file: warning on /. reload page.$path_to_rm");
  } else {
    exec("rm -rf $path_to_rm");
  }
}
function launch_system_command($cmd, $background=0, $delete_tmp_files=1, $timeout_sec=0, $result_of_bck_job_path=null) {

  $timeout="";
  if($timeout_sec>0) $timeout = "timeout -k ".($timeout_sec + 1)."s ".$timeout_sec."s ";

  $temp_dir = dirname(__FILE__);
  $cmd_file_path = "$temp_dir/cmd_for_bck_".date('Ymd_His').rand (1,1000).".sh";
  file_put_contents($cmd_file_path, $cmd);

  $cmd = 'sh '.$cmd_file_path;

  if($background==1) {
    if(preg_match('/^\s*$/', $result_of_bck_job_path)) $result_of_bck_job_path="/dev/null";
    $cmd .= '>'.$result_of_bck_job_path.' 2>&1 &';
    $cmd_file_pathBck = "$temp_dir/bck_cmd_general_".date('Ymd_His').rand (1,1000).".sh";
    file_put_contents($cmd_file_pathBck, $cmd);
    $cmdBck = 'sh '.$cmd_file_pathBck;
    shell_exec($timeout.$cmdBck);
    rm_file($cmd_file_pathBck);
  } else {
    exec($timeout.$cmd.' 2>&1', $output, $return_var);
    rm_file($cmd_file_path);
    return array($output, $return_var);
  }
}
function Is_empty_str($champ, $isdate=0){

	//verbose_str_to_file(__FILE__, __FUNCTION__, "get $isdate and '$champ'");
  if(!isset($champ))return true;
	else if(preg_match('/^\s*$/',$champ)) return true;
	else if(($isdate==1) && preg_match('/^\s*0000.00.00\s*$/',$champ)) return true;
	else if(($isdate==1) && preg_match('/^\s*0000.00.00\s00.00.00\s*$/',$champ)) {
		//verbose_str_to_file(__FILE__, __FUNCTION__, "get $isdate and '$champ' for 2");
		return true;
	}
	else return false;
}
function exit_if_running($cur_prog_name=1, $prog_path, $add_grep, $NO_echo) {

  $nb_process=0;
  $cur_prog="";
  $grep_filter=" | grep -v grep | grep -v sudo";
  if($cur_prog_name==1){
    $file_name_ext = pathinfo($prog_path);
    $grep_filter .= " | grep ".$file_name_ext['filename'];
  }

  if( ! Is_empty_str($add_grep)) $grep_filter .= " | grep ".$add_grep;


  $ps_cmd = "ps -efd";
  $ps_cmd .= $grep_filter;

  list($output, $status) = launch_system_command($ps_cmd,0,1);
  //file_put_contents(dirname(__FILE__)."/log_lancement", date('Y-d-m H:i:s').$ps_cmd.print_r($output,1), FILE_APPEND);
  //echo $ps_cmd.print_r($output,1);
  $ps_res="";
  foreach($output as $comment) {
    if( ! preg_match('?/bin/sh -c?', $comment)) $nb_process++;
    $ps_res .= "\nline=>". $comment;
  }

  if($nb_process > 1) {
    if($NO_echo != 1) echo "Les process suivants sont en cours, donc ne pas lancer celui la.\n$ps_cmd\n".$ps_res."\n";
    die();
  }
}


function get_dir_content($src, $only_files=null, $match_str=null, $uniq=null) {

  $cpt=0;
  $message="";
	//verbose_str_to_file(__FILE__, __FUNCTION__, "Lecture du répertoire $src avec only_files=$only_files et match_str=$match_str");
	$file_list=array();
	if(is_dir($src) && file_exists($src)){
		$contenu="";
		if($dir = opendir($src)){
			while(false !== ( $file_name = readdir($dir)) ) {
				$contenu.= " '$file_name' ";
				$cpt++;
        if( ( $file_name != '.' ) && ( $file_name != '..' ) ) {
					if($only_files==1) {
            //echo "$match_str\n";
						if( ! is_dir("$src/$file_name")){
							//verbose_str_to_file(__FILE__, __FUNCTION__, "$file_name: cas $only_files==1) && ( $is_dir != 1");
							if( Is_empty_str($match_str)) $file_list[]=$file_name;
							else if( preg_match($match_str, $file_name) ) $file_list[]=$file_name;

						} //else echo "dir $file_name\n";
					} else {
						//verbose_str_to_file(__FILE__, __FUNCTION__, "$file_name: cas $only_files==1) && ( $is_dir != 1");
						if( Is_empty_str($match_str)) $file_list[]=$file_name;
						else if( preg_match($match_str, $file_name) ) $file_list[]=$file_name;
					}

					if($cpt>1000000) {
            $status=1;
            $message="Repertoire $src exede 100000 elements";
            break;
          }
				}
			}
			closedir($dir);
			$status=0;
		} else {
			$status=1;
			$message="Repertoire $src illisible";
		}
	} else {
		$status=1;
		$message="Repertoire $src n'existe pas";
	}


	if($uniq==1){
		if(count($file_list) == 0){
			$status=1;
			$message="Aucun fichier trouvé avec les critaires";
		}
		if(count($file_list) > 1){
			$file_list=array();
			$status=1;
			$message="Plus d'un fichier trouvé avec les critaires demandés";
			verbose_str_to_file(__FILE__, __FUNCTION__, "status=$status $message. Plus d'un fichier trouvé avec les critaires demandés "."only_files=$only_files et match_str=$match_str\n $contenu \nrésultat:$resultat");
		}
	}
	$resultat="";
	foreach($file_list as $file){
		$resultat .= " '$file' ";
	}
	verbose_str_to_file(__FILE__, __FUNCTION__, "status=$status $message. Contenu du répertoire $src avec ".
                      "only_files=$only_files et match_str=$match_str\nrésultat:".substr($resultat,0,50)."...");

	return array($status, $message, $file_list);
}
function select ($db_connexion, $requete) {

  verbose_str_to_file(__FILE__, __FUNCTION__, "requete select:\n$requete\n");
  //if($GLOBALS['verbose']==1) echo "lance $requete"."\n";
  $resultat = mysqli_query($db_connexion,$requete);
  //if($GLOBALS['verbose']==1) echo "erreur".mysqli_error($db_connexion)."\n";
  if( ! $resultat) {
    $erreur = mysqli_error($db_connexion);
    verbose_str_to_file(__FILE__, __FUNCTION__, "Erreur: $erreur\n");
  }

  //if($GLOBALS['verbose']==1) echo "resultat"."\n";

  $donnees = array() ;
  //while($ligne = mysqli_fetch_assoc($resultat)) {
  while ($ligne = $resultat->fetch_assoc()) {

    foreach ($ligne as $clef => $valeur) {
      if (!is_numeric($valeur)) {
        $ligne[$clef] = stripslashes($valeur);
      }
    }
    $donnees[] = $ligne ;
  }
  //if($GLOBALS['verbose']==1) echo "NB elems".count($donnees)."\n";
  verbose_str_to_file(__FILE__, __FUNCTION__, "SQL result ".count($donnees)." elements\n");
  return $donnees ;
}

function insert($db_connexion, $table, $valeurs) {

  //verbose_str_to_file(__FILE__, __FUNCTION__, "insertion table $table \n".print_r($valeurs,1));
  $valeurs_=array();
  foreach($valeurs as $clef => $valeur) {
    if( ! Is_empty_str($valeur)){
      $valeur = str_replace("'", "\'", $valeur);
      $valeur = "'" . $valeur . "'" ;
      $valeurs_[$clef] = $valeur ;
    }
  }

  $colonnes_ = array_keys($valeurs_) ;
  $valeurs_ = array_values($valeurs_) ;
  $sql = "INSERT INTO $table (" ;
  $sql .= join(', ', $colonnes_) ;
  $sql .= ') VALUES (' ;
  $sql .= join(', ', $valeurs_) ;
  $sql .= ')' ;

  verbose_str_to_file(__FILE__, __FUNCTION__, "commande insert SQL:\n$sql\n");
  $resultat = mysqli_query($db_connexion,$sql);

  if (!$resultat) {
    $erreur = mysqli_error($db_connexion) . "n[$sql]" ;
    throw new Exception($erreur) ;
  }

  return true ;
}

function clean_file_name($file_name, $keep_plancpt_car=0, $keep_spaces=1, $forsearch=null){

  if($keep_plancpt_car != 1) $file_name = preg_replace('#\.#', 'XXPOINTXX', $file_name);
  $bon_file_name = "";
  $splitted = str_split($file_name);
  foreach($splitted as $splitted_car) {
    $code_ascii = ord($splitted_car);
    //verbose_str_to_file(__FILE__, __FUNCTION__, "$splitted_car $code_ascii");
    if(($keep_spaces == 1)&&($code_ascii == 32)) $bon_file_name .= $splitted_car;
    else if(($forsearch == 1)&&($code_ascii == 32)) $bon_file_name .= '%';

    if( (($code_ascii >47)&&($code_ascii < 58)) || (($code_ascii >64)&&($code_ascii < 91)) || (($code_ascii >96)&&($code_ascii < 123)) || ($code_ascii == 95) ) $bon_file_name .= $splitted_car;
    else if($forsearch == 1) $bon_file_name .= '%';
    else {
      if($code_ascii == 169) $bon_file_name .= 'e';
      if($code_ascii == 142) $bon_file_name .= 'e';
      if($keep_plancpt_car == 1)
        if( ($code_ascii == 37) || ($code_ascii == 40)|| ($code_ascii == 41)|| ($code_ascii == 46)|| ($code_ascii == 60)|| ($code_ascii == 61)|| ($code_ascii == 62)) $bon_file_name .= $splitted_car;
    }
  }
  if($keep_plancpt_car != 1) $bon_file_name = preg_replace('#XXPOINTXX#', '.', $bon_file_name);
  //verbose_str_to_file(__FILE__, __FUNCTION__, "$bon_file_name");

  $bon_file_name = preg_replace('/^\s+/', '', $bon_file_name);
  $bon_file_name = preg_replace('/\s+$/', '', $bon_file_name);

  return $bon_file_name;
}


function circular_file($file_path, $nb_keep=3) {

  for ($idx=$nb_keep; $idx > 0; $idx--) {
    $cmd = "mv ".$file_path.'.'.$idx." ".$file_path.'.'.($idx+1);
    //echo $cmd."\n";
    launch_system_command($cmd,0,1);
  }
  launch_system_command("mv ".$file_path." ".$file_path.'.1',0,1);
}
function build_uniq_file_name($file_name, $dir_target) {

  $sep  = '';
  $idx=0;
  $new_file_name = clean_file_name($file_name,0,0);
  if( ! preg_match('?/\s*$?', $dir_target)) $sep = '/';

  if(file_exists($dir_target . $sep . $new_file_name)) {
    $new_file_name = $idx."s_".$file_name;
    if(file_exists($dir_target . $sep . $new_file_name)) {
      $new_file_name = mktime().rand(0, 15000).$file_name;
    }
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "$dir_target" . "$sep" . "$new_file_name");
  return $new_file_name;
}

function archive_mailfile($file_path, $base) {


  $archive_base=ARCHIVEMAIL."/".strtoupper($base);
  $cmd = "mkdir -p $archive_base";
  verbose_str_to_file(__FILE__, __FUNCTION__,  "$cmd\n");
  list($output, $status) = launch_system_command("$cmd",0,1);

  if(is_dir($archive_base)) {
    $uniq_file_name =build_uniq_file_name(basename($file_path), $archive_base);
    $cmd = "mv '$file_path' $archive_base/$uniq_file_name";
    verbose_str_to_file(__FILE__, __FUNCTION__,  "$cmd\n");
    list($output, $status) = launch_system_command("$cmd",0,1);
    if($status==0) {
      $cmd = "mv '$file_path.log' $archive_base/$uniq_file_name.log";
      verbose_str_to_file(__FILE__, __FUNCTION__,  "$cmd\n");
      list($output, $status) = launch_system_command("$cmd",0,1);
      return("$archive_base/$uniq_file_name");

    } else {
      die("erreur $cmd ".print_r($output,1));
    }
  } else {
    die("No dir $archive_base");
  }

}

function objToArray($obj=false)  {
  if (is_object($obj))
    $obj= get_object_vars($obj);
  if (is_array($obj)) {
    return array_map(__FUNCTION__, $obj);
  } else {
    return $obj;
  }
}

function date_html_to_mysql($html_date, $split_res=0,$quadra=0, $set_today=0){

  $html_date=trim($html_date);
  if(preg_match('?/?', $html_date)){
    $tmp_array = explode("/", $html_date);
    $tmp_array[1] = $tmp_array[1]+0;
    if($tmp_array[1] != 0) {
      if($quadra>0) {
        $tmp_array[1]=str_pad($tmp_array[1], 2, "0",STR_PAD_LEFT);
        $tmp_array[0]=str_pad($tmp_array[0], 2, "0",STR_PAD_LEFT);
        $tmp_array[2] = substr($tmp_array[2],2,4);
        if($quadra==1) $sql_date = $tmp_array[0].$tmp_array[1].$tmp_array[2];
        if($quadra==2) $sql_date = $tmp_array[2].$tmp_array[1].$tmp_array[0];
        if($quadra==3) $sql_date = $tmp_array[1].'/'.$tmp_array[0].'/'.$tmp_array[2];
        if($quadra==4) $sql_date = $tmp_array[0].'/'.$tmp_array[1].'/'.$tmp_array[2];
      }
      else $sql_date = str_pad($tmp_array[2], 2, "0",STR_PAD_LEFT).'-'.str_pad($tmp_array[1], 2, "0",STR_PAD_LEFT)."-".$tmp_array[0];
    } else if($set_today==1) $sql_date = date("Y-m-d");
  } else $sql_date=$html_date;

  if($split_res) return $tmp_array;
  else return $sql_date;
}
function clean_libelle_banque($libelle){

	//verbose_str_to_file(__FILE__, __FUNCTION__, "libelle banque recu: $libelle");

	$libelle = preg_replace('/"/', '', $libelle);
	$libelle = preg_replace('/\'/', '', $libelle);
	$libelle = clean_file_name($libelle, 0, 1);

	//verbose_str_to_file(__FILE__, __FUNCTION__, "libelle banque retourne: $libelle");

	return($libelle);
}

function montant_cfonb($Montant, $Montant_sign, $Nombre_decimales){

  $dec_val = hexdec( dechex(ord($Montant_sign)) );
  $credit = 0;
  $debit = 0;

  if($dec_val == 125) $debit = $Montant* 10 / pow(10,$Nombre_decimales);
  else if($dec_val == 123) $credit = $Montant * 10 / pow(10,$Nombre_decimales);
  else if(($dec_val>64)&&($dec_val<74)) {
    $dern_chiffre = $dec_val - 64;
    $concat_str = $Montant.$dern_chiffre;
    $credit = $concat_str * 1 / pow(10,$Nombre_decimales);
  }
  else if(($dec_val>192)&&($dec_val<202)) {
    $dern_chiffre = $dec_val - 192;
    $concat_str = $Montant.$dern_chiffre;
    $credit = $concat_str * 1 / pow(10,$Nombre_decimales);
  }
  else if(($dec_val>73)&&($dec_val<83)) {
    $dern_chiffre = $dec_val - 73;
    $concat_str = $Montant.$dern_chiffre;
    $debit = $concat_str * 1 / pow(10,$Nombre_decimales);
  }
  else if(($dec_val>207)&&($dec_val<218)) {
    $dern_chiffre = $dec_val - 207;
    $concat_str = $Montant.$dern_chiffre;
    $debit = $concat_str * 1 / pow(10,$Nombre_decimales);
  }
  else {
    verbose_str_to_file(__FILE__, __FUNCTION__, "ERREUR : erreur sur la line ");
    $credit = $debit = 0;
  }

  return array($debit, $credit);
}

// csv_pdf_lib

function set_exported($fraisAndCharges,$base,$div_id, $banque, $unexport, $upd_rev, $type, $id) {

	if(is_FacNote_base($base)) $verboseController = new VerboseController('societe', $base);
	else $verboseController = new VerboseController();

	verbose_str_to_file(__FILE__, __FUNCTION__,"base $base, div_id=$div_id, unexport=$unexport, upd_rev=$upd_rev type=$type et id=$id banque id: ".print_r($banque['id'], 1)." fraisAndCharges ".print_r($fraisAndCharges[0],1));

  if(!Is_empty_str($id)){
    $chg_infos = $verboseController->get($id, $type);
    $chg_infos['type']=$type;
    $fraisAndCharges = array($chg_infos);
    verbose_str_to_file(__FILE__, __FUNCTION__,"fraisAndCharges: ".print_r($fraisAndCharges, 1));
  }
  mysqli_close($verboseController);

	$undo_list = $_SESSION[$base][MODULE_COMPTA]["unExp_".$div_id];
  verbose_str_to_file(__FILE__, __FUNCTION__, "recu undo_list: $undo_list");
  if($banque['id']>0){

    $donnees = array();
    $donnees['id']=$banque['id'];
    if($unexport==1) $donnees['exported']=0;
    else $donnees['exported']=1;
    $verboseController->update($donnees,'banque');
    if( ! ($banque['exported'] > 0)) $undo_list .= "banque".$banque['id']."x";
  }
  mysqli_close($verboseController);

	foreach($fraisAndCharges as $chg_infos){
    $all_csv_elem = search_csv_elem_in_base($base, $chg_infos['type'], $chg_infos['id']);
    if(is_FacNote_base($base)) $verboseController = new VerboseController('societe', $base);
    else $verboseController = new VerboseController();

    $val_actuel = $verboseController->get($chg_infos['id'],$chg_infos['type']);

    if($unexport==1) {
      if($val_actuel['exported']==2) $val_to_set = 1;
      else $val_to_set = 0;
    } else {
      if($val_actuel['exported']==1) $val_to_set = 2;
      else $val_to_set = 1;
    }

		$donnees = array();
		$donnees['id']=$chg_infos['id'];
		$donnees['exported']=$val_to_set;
		$verboseController->update($donnees,$chg_infos['type']);
    foreach($all_csv_elem as $csv_infos){
      $donnees=array();
      $donnees['id']=$csv_infos["id"];
      $donnees['exported']=$val_to_set;
      $donnees['date_export']=date('Y-m-d');
      $verboseController->update($donnees, 'csv_elems');
    }

		if( ! ($banque['id']>0)) {
      if( ! ($chg_infos['exported'] > 0)) $undo_list .= $chg_infos['type']."".$chg_infos['id']."x";
    }

    if(($upd_rev==1)&&($unexport==1)) {
      foreach($all_csv_elem as $csv_infos){
        $donnees=array();
        $donnees['id']=$csv_infos["id"];
        $donnees['revision_ok']=0;
        $donnees['exported']=0;
        $verboseController->update($donnees, 'csv_elems');
      }

      create_in_ver_entry($base, $chg_infos['type'], $chg_infos['id'], "", 'del', 1);
    }
    mysqli_close($verboseController);
	}

  mysqli_close($verboseController);
	if(! Is_empty_str($div_id)) $_SESSION[$base][MODULE_COMPTA]["unExp_".$div_id] = $undo_list;
  verbose_str_to_file(__FILE__, __FUNCTION__, "return undo_list: $undo_list");
  return $undo_list;
}


$BK_AUTO = array(

  'FA0183'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'65q4sdgf5sdfg46sdf5g654SCHE6'),
  'FA0766'=> array( 'file_api'=>"api_CegidV2_FacNote.php", 'key'=>'mkljm4256523453457HZRTYDWF'),
  'FA0917'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'65q4sdgf5sdfg46sdf5g6546'),
  'FA0497'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'6s5dfgh4w3x2cvd56fg4'),
  'FA0050'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'658Q7SDGF32QS1DG654S7DF6G84QS3SD54F'), // a voir
  'FA0051'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'ISA32QS1DG654S7DF6G84QS3SD54FFF'),
  'FA0064CCC'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'ACDSDGF32QS1DG654S7F6G84QS32345267'),
  'FA0067XX'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'Q6S5GDF4SQ65G498SQ7G65QSD1G65SQDG7'),
  'FA0128'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'ACCSDC45698347810137457347563902'),// a voir
  'FA0129'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'ACDSDGF32QS1DG654S7DF6G84QS3SD54'),
  'FA0359'=> array('file_api'=>"api_CegidV2_FacNote.php", 'key'=>'GE567856DFGUUCDRTYU745623DRGXXX'),
  'FA0420XX'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'758Q7SDGF32QS1DG654S7DF6G84QS3SD54F'),
  'FA0503'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'LLI9WSDG8F8SFDG65DS8FG9'),
  'FA0511'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'FACCC45698347810137457347563902'), // a voir
  'FA0594XX'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'ACCSDC45698347810137457347563902'),
  'FA0671'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GD4SQ65G498SQ7G65QS5FB7DG7'),
  'FA0727'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'SAGSDC45698347810137457347563902'),
  'FA0761'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'9Q7QSD7F8QSD8FQS7DG7667QSDG7F'), // A VOIR
  'FA0779'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'ACD6fasD4SQ65G498SQ7G65QS5FB7DG7'), // A VOIR
  'FA0923'=> array('file_api'=>"api_CegidV2_FacNote.php", 'key'=>'GE567856DFGUUCDRTYU745623DRGXXX'), // A VOIR
  'FA1068'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'MMLLI9WSDG8F8SFDG65DS8FG9'),
  'FA1074'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGICPT32564J234GH23V45J23I4HB'), // A VOIR
  'FA1029'=> array('file_api'=>"api_Quadra_FacNote.php", 'key'=>'QUADSDC45698347810137457347563'),
  'FA1077'=> array('file_api'=>"api_Quadra_FacNote.php", 'key'=>'QUADSDCFQSDQ8347810137419285763756'),// A VOIR
  'FA1080'=> array('file_api'=>"api_Quadra_FacNote.php", 'key'=>'QUADSDCFQSDQ834781013745745763756'),// A VOIR
  'FA1083'=> array('file_api'=>"api_Quadra_FacNote.php", 'key'=>'QUADSDCFQSDQ834781013745734756'),// A VOIR
  'FA1091'=> array('file_api'=>"api_Quadra_FacNote.php", 'key'=>'QUADSDCFQSDQ834789561013745734756'),// A VOIR
  'FA1098'=> array('file_api'=>"api_Quadra_FacNote.php", 'key'=>'QUADSDC4569834781013745734756'),// A VOIR
  'FA1107'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GD4SQ65G498SQ7G6QS5FB7DG7'),// A VOIR
  'FA1116'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'Q6S5GDF4SQ65G498SQ7G65QSD1G65SQDG7'),
  'FA1119XX'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'6S5GD4SQ65G498SQ7G65QSD1G65SQDG7'),
  'FA3775XXX'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'Q6S5GD4SQ65G498SQ7G65QSDG65SQDG7 H'),
  'FA1127'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'Q6S5GD4SQ65G498SQ7G65QSD1G65SQDG7'),
  'FA3332'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'Q6S5GD4SQ65G498SQ7G65QSD1G65SQDG7'),
  'FA3331'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'Q6S5GD4SQ65G498SQ7G65QSD1G65SQDG7'),
  'FA3334'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'Q6S5GD4SQ65G498SQ7G65QSD1G65SQDG7'),
  'FA3333'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'Q6S5GD4SQ65G498SQ7G65QSD1G65SQDG7'),

  'FA1193'=> array('file_api'=>"api_EIC_FacNote.php", 'key'=>'EICS5GDLUSN4SQ658SQSDF6345632DHH5'),
  'FA1201'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'MMLLI9WSDG8F8SFDG65DS8FG9SDQF98345'),
  'FA1206'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'6S5GD4SQ65G498SQ7G65QSD1G65SQDG7'),
  'FA1226'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'ACCSDC45698347810137457347563902CCCC'),
  'FA1261'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'Q6S5GD4SQ65G498SQ7G65QS5FB7DG7'),// A VOIR
  'FA1291'=> array('file_api'=>"api_CegidV2_FacNote.php", 'key'=>'Q6S5GD4SQ65G498Q7G65QS5FB7DG7'),// A VOIR
  'FA1313'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'6S5GD4STESTSERVEURDEV'),
  'FA1324'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'6S5GD4SQ65G498SQ7G65QS5FB7DG7'),
  'FA1442'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'MQSmsdf9658769879mdfsjonh976'),// A VOIR
  'FA1542'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'MQSmsdf9658769879mdfsjonh97'),// A VOIR
  'FA1573'=> array('file_api'=>"api_CegidV2_FacNote.php", 'key'=>'LMKLKmqjksdhmq65418hsg'),
  'FA1537'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGICPT32564J234GH23V45J23I4HB'),
  'FA1540'=> array('file_api'=>"api_Quadra_FacNote.php", 'key'=>'ACCSDC45698347810137457347563902'),
  'FA1559X'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'SAGECSDC4569834781013745734756390'),
  'FA1599XX'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'SAGECSDC456983478101374573475639'),
  'FA1565'=> array('file_api'=>"api_SageXLS_FacNote.php", 'key'=>'SAGECSDC45698347810137457347563902'),
  'FA1566'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'6fasD4SQ65G498SQ7G65QS5FB7DG7'),
  'FA1593'=> array('file_api'=>"api_CegidV2_FacNote.php", 'key'=>'CEG345ZS653LMKLKmqjksdhmq18hsg'),
  'FA1768'=> array('file_api'=>"api_Quadra_FacNote.php", 'key'=>'QUADDC456983478101374573475444'),// A VOIR
  'FA1786'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AkGICPT32564J234GH23V45J23I4HB'),

  'FA1829'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'ACCSDC45698347810137457347563902'),// A VOIR
  'FA1859'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'ACDsD4SQ65G498SQ7G65S5FBCHAB'),// A VOIR
  'FA1870'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'ACDsD4SQ65G498SQ7G65QS5FB7DG7'),
  'FA1873'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'SAGECSDC4569FGEZ83471374575639'),
  'FA1932'=> array('file_api'=>"api_Soddec_FacNote.php", 'key'=>'SODDECDC4569FGEZ83471374'),
  'FA1941XX'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'SAGECSDC4569FGEZ83471374BO39'),
  'FA1975'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GD4SQ658SQ7G65QS5FB7DG7'),
  'FA2008'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GD4345658SQ7G65QS5FB7DG7'),
  'FA2011'=> array('file_api'=>"api_Wcompta_FacNote.php", 'key'=>'KHAsD4SQFDSDSQ7G65QS5FB7DG'),

  'FA2100'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'ACDsD4SQFDSDSQ7G65QS5FB7DG7'),
  'FA2127'=> array('file_api'=>"api_Quadra_FacNote.php", 'key'=>'QUADDC456983478101374573475639'),
  'FA2157XX'=> array('file_api'=>"api_Quadra_FacNote.php", 'key'=>'QUADDC456983478101374573475315'),// ne veut pas en auto

  'FA2218'=> array('file_api'=>"api_CegidV2_FacNote.php", 'key'=>'CEGLM654KLKksdhmq65418hsg'),
  'FA2317'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5DG4SQ658SQ7G65QS5FB7DG7'),
  'FA2233XX'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'SAGECSDC4569FGEZ83471374BO39'),
  'FA2251'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GD4SQ658SQ7G65QS5EX345'),
  'FA2429'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GDLUSN4SQ658SQ7G65QS5EX345'),
  'FA2456'=> array('file_api'=>"api_EIC_FacNote.php", 'key'=>'EICS5GDLUSN4SQ658SQ7G65QS5EX345'),
  'FA2581X'=> array('file_api'=>"api_Sage_FacNote.php", 'key'=>'SAGECSDC4569FGEZ83471374BO94'),
  'FA2586'=> array('file_api'=>"api_EIC_FacNote.php", 'key'=>'EICS5GDLUSN4SQ65q3sdf4q5sdf421345'),
  'FA2731'=> array('file_api'=>"api_CegidV2_FacNote.php", 'key'=>'CEGLM654KLKksdhmq65418mlkjhsg'),
  'FA2733'=> array('file_api'=>"api_EIC_FacNote.php", 'key'=>'EICS5GDLUSN4SQ658SQSDF6345632DHH5'),
  'FA3524'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GD4SQ658SQ7G65QS5EX345C'),
  'FA3867'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GD4SQ658SQ7G65QS5VFF345C'),

  'FA4044'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GD4SQ658SQ7G65QS5EX345QSDG'),
  'FA4947'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GD4SQ658SQ7G65QS5EX345QSDDD'),

  'FA5109'=> array('file_api'=>"api_Soddec_FacNote.php", 'key'=>'SODDECDC4569FGEZ83471374G5'),
  'FA5197'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5D4SQ658SQ7G65QS5EX345C'),
  'FA5273'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'ACDsDQ65G498SQ7G65S5FBCHAB'),
  'FA5311'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GD4SQ658SQ7G65QS5EX45C'),
  'FA5698'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'ACDsDQ65G498SQ7G65S5FBCHABKKIS'),
  'FA5798'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'ACDsDQ65G498SQ7G65S5FBCHABKKIS'),

  'FA6672'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGISSS5D4SQ658SQ7G65QS5EX345C'),
  'FA7381'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'ACDLAGER498SQ7G65S5FBCHABKKIS'),
  'FA8326'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGISSS5DFA8326Q7G65QS5EX345C'),

  'FB0166'=> array('file_api'=>"api_ACDV2_FacNote.php", 'key'=>'FTPQSDFG8QSDF66V6G67FGD7DFGH9'),
  'FB0268'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS5GD4SQ658SQ7G65QS5VFF345C'),
  'FB0606'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS0606SQ658SQ7G65QS5VFF345C'),
  'FB1123'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS0FB1123SQ7G65QS5VFF345C'),
  'FB1979'=> array('file_api'=>"api_Quadra_FacNote.php", 'key'=>'QUADSDC456983810137457347563'),
  'FB1913'=> array('file_api'=>"api_Agiris_FacNote.php", 'key'=>'AGIS0FB1913SQ7G65QS5VFF345C'),

);

function get_zipdir_path($date_operation, $base, $cab_dossier, $export_mode) {

  if(preg_match('?/?', $date_operation)) $date_operation = date_html_to_mysql($date_operation);

  if(!Is_empty_str($date_operation))
    list($d_cur,$m_cur,$y_cur) = date_mysql_to_html($date_operation, 1, 1);
  if( ! ($y_cur>0)) $y_cur=date('Y');
  if( ! ($m_cur>0)) $m_cur=date('m');

  $generetedFileName ="Exp_$y_cur"."_$m_cur"."_$base"."_".rand(10,99);

  if(($export_mode==36)||($export_mode==41)){
    if(Is_empty_str($cab_dossier)) $cab_dossier = "NONUM";
    $generetedFileName = "FacNote_".$cab_dossier."_".date('Ymd-His')."_manual";
  }
  if(($export_mode==32)||($export_mode==32)){
    if(Is_empty_str($cab_dossier)) $cab_dossier = "NONUM";
    $generetedFileName = $cab_dossier."_".date('Ymd_His')."_manual";
  }
  if(($export_mode==44)) {
    if(Is_empty_str($cab_dossier)) $cab_dossier = "NONUM";
    $generetedFileName = $cab_dossier."IN.auto";
  }

	$zip_dir_name = $generetedFileName;
	$zip_dir = LOGDIR."/$zip_dir_name";
  list($output, $status) = launch_system_command("rm -rf $zip_dir");
  list($output, $status) = launch_system_command("mkdir $zip_dir");
	verbose_str_to_file(__FILE__, __FUNCTION__, "mkdir zip_dir $zip_dir et zip_dir_name $zip_dir_name\n".print_r($output,1));

	return array($zip_dir, $zip_dir_name);
}

function date_url_to_mysql($date){

  $tmp_date=trim($date);
  $tmp_date=str_replace('--', '-', $tmp_date);
  $tmp_date=str_replace('-', '/', $tmp_date);
  if( ! preg_match('?/?', $tmp_date)) {
    $tmp_arr = str_split($tmp_date);
    if(count($tmp_arr)>6)
      $tmp_date = $tmp_arr[0].$tmp_arr[1]."/".$tmp_arr[2].$tmp_arr[3]."/".$tmp_arr[4].$tmp_arr[5].$tmp_arr[6].$tmp_arr[7];
  }
  if(preg_match('?/?', $tmp_date)) $tmp_date=date_html_to_mysql($tmp_date);

  return $tmp_date;
}

function date_url_to_mysqlB($date){

  $res = $date."- ";
  $tmp_date=trim($date);
  $res .= $tmp_date."- ";
  $tmp_date=str_replace('--', '-', $tmp_date);
  $res .= $tmp_date."- ";
  $tmp_date=str_replace('-', '/', $tmp_date);
  $res .= $tmp_date."- ";
  if( ! preg_match('?/?', $tmp_date)) {
    $tmp_arr = str_split($tmp_date);
    if(count($tmp_arr)>6)
      $tmp_date = $tmp_arr[0].$tmp_arr[1]."/".$tmp_arr[2].$tmp_arr[3]."/".$tmp_arr[4].$tmp_arr[5].$tmp_arr[6].$tmp_arr[7];
    $res .= $tmp_date."++ ";
  }
  if(preg_match('?/?', $tmp_date)) $tmp_date=date_html_to_mysql($tmp_date);
  $res .= $tmp_date."- ";

  return $res;
}

function init_CSV_file($html_mode=0){

	$csv_content ="";

  define('LANG436', "DATE");
  define('LANG437', "CODE JOURNAL");
  define('LANG438', "COMPTE");
  define('LANG439', "NUMERO DE PIECE");
  define('LANG440', "LIBELLE");
  define('LANG441', "DEBIT");
  define('LANG442', "CREDIT");
  define('LANG443', "DEVISE");
  define('LANG675', "Numéro de facture");
  define('LANG880', "Analytique");

	if($html_mode==3){
    $csv_content .= '<!doctype html>
<html lang="en">
<head>
    <title>'.$zip_dir_name.'</title>
</head>
<body>
<table border=1>
';
	}
	if($html_mode<0){
		$csv_content .= '<?xml version="1.0" encoding="utf-8"?>
<!--
- FacNote XML Dump
- http://www.facnote.com
-
- Dump du :  '.date('d/m/Y hh:mm').'
-->

<FacNote_xml_export version="1.0" xmlns:pma="http://www.facnote.com">
    <!--
    - Informations OCR
    -->
';
	}
	if($html_mode==3)
		$csv_content .= "<TR><TD>".LANG436."</TD><TD>".LANG437."</TD><TD>".LANG438."</TD><TD>".LANG439."</TD><TD>".LANG440."</TD><TD>".LANG441."</TD><TD>".LANG442."</TD><TD>".LANG443."</TD><TD>".LANG675."</TD></TR>\n";
	if($html_mode==1){
		$csv_content .= LANG436.";".LANG437.";".LANG438.";".LANG439.";".LANG440.";".LANG441.";".LANG442.";".LANG443.";".LANG880.";".'Lettrage;GED'.";Chemin;\n";
	}
	else if(($html_mode==5)||($html_mode==19)) {
		$csv_content = '';
	}
	else if($html_mode==6) {
		//$csv_content .= LANG662.";".LANG436.";".LANG438.";".LANG440.";".LANG663.";".LANG664.";Document\n";
    $csv_content .= "Code journal;Date;N de compte;Intitule du compte;Compte auxiliaire;Intitule du compte auxiliaire;Document;Debit;Credit;Date echeance;N doc. associe;Documents associes;Poste analytique;Ventilation analytique\n";
	}
	else if($html_mode==7) {
		$csv_content .= "Compte;Intitule Compte;Date;Code Journal;Libellé de l'écriture;Montant;Sens;Numéro de pièce interne;Numéro de pièce externe;Mode Règlement;Code analytique;Fichier Image;Lettrage".";".LANG675."\n";
	}
	else if($html_mode==46) {
		$csv_content .= "Compte;Intitule Compte;Date;Code Journal;Libellé de l'écriture;Montant;Sens;Numéro de pièce interne;Numéro de pièce externe;Mode Règlement;Code analytique;Lien web;Lettrage".";".LANG675.";periode 1;periode 2\n";
	}
	else if($html_mode==39) {
		$csv_content .= "Code journal;Date;Numéro de compte;Numéro de pièce;Fournisseur;Débit;Crédit;Pièce;Libellé explicite;Date de début;Date de fin\n";
	}
	else if($html_mode==9) {
		$csv_content .= ";Compte;Libelle Mouvement;Debit;Credit;Quantite 1;Quantite 2;Code TVA".";".LANG675."\n";
	}
	else if($html_mode==10) {
		$csv_content .= "Date;Journal;Compte;Piece;Libelle;Debit;Credit".";".LANG675."\n";
	}
	else if($html_mode==16) {
		$csv_content .= "Journal;Date;Compte;Libelle piece;Num piece;Libelle mouvement;Debit;Credit"."\n";
	}
	else if($html_mode==24) {
		$csv_content .= "**Compta"."\t"."Ecritures"."\n";
	}
	else if($html_mode==28){
		$csv_content .= LANG436.";".LANG437.";Type;Groupe;TauxTVA;".LANG438.";".LANG439.";".LANG440.";".LANG441.";".LANG442.";".LANG443.";".LANG675.";\n";
	}
	else if($html_mode==29){
		$csv_content .= "Journal;Date;General;Auxiliaire;Reference;Libelle;Debit;Credit"."\n";
	}

	return $csv_content;
}

function print_CSV_entry($all_csv_elem, $mode) {
	$csv_line = $plancpt_line = $justif_to_add = $echmvt = "";
	$total_credit = $total_debit = $idx = $numero_ligne = $old_id_type = $old_famille = $old_type = 0;

	$cae_fait=array();
                          $csv_line_arr=array();

  $base = $all_csv_elem[0]['base'];
  if(!Is_empty_str($base)) $dbName=$base;
  $comptable = $all_csv_elem[0]['comptable'];
  $soc_infos=array();
  $soc_infos['comptable']=$comptable;

  verbose_str_to_file(__FILE__, __FUNCTION__, "print in mode $mode get all_csv_elem:".print_verb_csvelem($all_csv_elem,1));
  //$mode=34;
  $lettrage_total=array();
	foreach($all_csv_elem as $csv_elem) {
    if(!Is_empty_str($csv_elem['lettrage'])) {
      if(!isset($lettrage_total[$csv_elem['lettrage']])) $lettrage_total[$csv_elem['lettrage']]=0;
      $lettrage_total[$csv_elem['lettrage']] += formater_montant($csv_elem['credit']) - formater_montant($csv_elem['debit']);
    }
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "verifier lettrage egale:".print_r($lettrage_total,1));
  foreach($lettrage_total as $code_let=>$val) {
    if($val != 0) {
      verbose_str_to_file(__FILE__, __FUNCTION__, "annulation lettrage $code_let car non egaux:");
      for($idx=0;$idx<count($all_csv_elem);$idx++) {
        //if($all_csv_elem[$idx]['lettrage'] == $code_let) $all_csv_elem[$idx]['lettrage']='';
      }
    }
  }
  $ancienne_famille=$all_csv_elem[0]['famille'];
  $idx=0;
	foreach($all_csv_elem as $csv_elem) {

    $csv_elem['path_pj'][0]=preg_replace('/^\s+/', '', $csv_elem['path_pj'][0]);
    $csv_elem['path_pj'][0]=preg_replace('/\s+$/', '', $csv_elem['path_pj'][0]);
    if(!isset($csv_elem['date_echeance']))$csv_elem['date_echeance']=null;
    if(!isset($csv_elem['nolibel_mvt']))$csv_elem['nolibel_mvt']=null;
    if(!isset($csv_elem['auxiliaire_desc']))$csv_elem['auxiliaire_desc']=null;
    if(!isset($csv_elem['auxiliaire_cpt']))$csv_elem['auxiliaire_cpt']=null;
    $chn='modepaie'; if(!isset($csv_elem[$chn]))$csv_elem[$chn]=null;
    $chn='lettrage'; if(!isset($csv_elem[$chn]))$csv_elem[$chn]=null;




                              if(!isset($csv_elem['peRiod1']))$csv_elem['period1']=null;
                          if(!isset($csv_elem['period2']))$csv_elem['period2']=null;
                              if(!isset($csv_elem['debut_exercice']))$csv_elem['debut_exercice']=null;
                              if(!isset($csv_elem['fin_exercice']))$csv_elem['fin_exercice']=null;
                              if(!isset($csv_elem['reglement']))$csv_elem['reglement']=null;

                              if(!isset($csv_elem['list_liens'])) {
                                $csv_elem['list_liens']=array();
                                $csv_elem['list_liens'][0]=null;
                              }
                              if(!isset($csv_elem['path_pj'])) {
                                $csv_elem['path_pj']=array();
                                 $csv_elem['path_pj'][0]=null;
                              }

    verbose_str_to_file(__FILE__, __FUNCTION__, "traitement:".print_r($csv_elem,1));
    //file_put_contents(dirname(__FILE__)."/../upload/csv_line.txt", $csv_elem['base']."*** avant traitement  $csv_line\n".print_r($csv_elem,1), FILE_APPEND);

		$idx++;
		if(($mode==-1)||($mode==-2)||($mode==-3)){
			//XML
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);
      if($csv_elem['type_element']=='banque') {
        $csv_line .= "<Client ClientID=\"".$csv_elem['base']."\">\n" . "\t<BANQUE>\n";
        $csv_line .= 	"\t\t<id>".$csv_elem['id']."</id>\n";
        $csv_line .= 	"\t\t<compte>".$csv_elem['compte']."</compte>\n";
        $csv_line .= 	"\t\t<date>".$csv_elem['date_operation']."</date>\n";
        $csv_line .= 	"\t\t<date_valeur>".$csv_elem['date_valeur']."</date_valeur>\n";
        $csv_line .= 	"\t\t<libelle>".$csv_elem['libelle']."</libelle>\n";
        $csv_line .= 	"\t\t<debit>".$csv_elem['debit']."</debit>\n";
        $csv_line .= 	"\t\t<credit>".$csv_elem['credit']."</credit>\n";
        $csv_line .= 	"\t\t<solde>".$csv_elem['solde']."</solde>\n";
        $csv_line .= 	"\t\t<id_ecriture>".$csv_elem['id_ecriture']."</id_ecriture>\n";
        $csv_line .= "\t</BANQUE>\n";
      } else {

        if($csv_elem['position']==4) $csv_line .= "<Client ClientID=\"".$csv_elem['base']."\">\n" . "\t<DELETE>\n";
        else if($idx == 1) $csv_line .= "<Client ClientID=\"".$csv_elem['base']."\">\n" . "\t<TTC>\n";
        else if($idx == 2) $csv_line .= "\t<HT>\n";
        else if($csv_elem['position']==2) $csv_line .= "\t<PORT>\n";
        else if($csv_elem['position']==3) $csv_line .= "\t<ECOTAX>\n";
        else if($idx >2) $csv_line .= "\t<TVA>\n";

        $csv_line .= 	"\t\t<date>".$csv_elem['date']."</date>\n";
        if($csv_elem['position'] != 4)	$csv_line .= "\t\t<echeance>".$csv_elem['date_echeance']."</echeance>\n";
        if($csv_elem['position'] != 4)	$csv_line .= "\t\t<code_j>".$csv_elem['code_j']."</code_j>\n";
        if($csv_elem['position'] != 4)	$csv_line .= "\t\t<num_cpt>".$csv_elem['num_cpt']."</num_cpt>\n";
        $csv_line .= "\t\t<id_fact>".$csv_elem['id_fact']."</id_fact>\n";
        if($csv_elem['position'] != 4)	$csv_line .= "\t\t<path_pj>".$csv_elem['path_pj'][0]."</path_pj>\n";
        $csv_line .= "\t\t<description>".$csv_elem['description']."</description>\n";
        $csv_line .= "\t\t<numeroFacture>".$csv_elem['num_fact']."</numeroFacture>\n";
        if($csv_elem['position'] != 4)	$csv_line .= "\t\t<debit>".$csv_elem['debit']."</debit>\n";
        if($csv_elem['position'] != 4)	$csv_line .= "\t\t<credit>".$csv_elem['credit']."</credit>\n";
        if($mode==-2) {
          $csv_line .= 	"\t\t<type_element>".$csv_elem['type_element']."</type_element>\n";
          if($csv_elem['position'] != 4)	$csv_line .= "\t\t<nom_docEmis>".$csv_elem['fichier_source']."</nom_docEmis>\n";
          if(($csv_elem['position'] != 4)	&& (!Is_empty_str($csv_elem['cb'])))$csv_line .= "\t\t<CarteBusiness>".$csv_elem['cb']."</CarteBusiness>\n";
          if(! preg_match('/^\s*$/',$csv_elem['user_login'])) $csv_line .=
                                                                        "\t\t<UserId>".$csv_elem['user_login']."</UserId>\n";
        }
        if($csv_elem['position']==4) $csv_line .= "\t</DELETE>\n";
        else if($idx == 1) $csv_line .= "\t</TTC>\n";
        else if($idx == 2) $csv_line .= "\t</HT>\n";
        else if($csv_elem['position']==2) $csv_line .= "\t</PORT>\n";
        else if($csv_elem['position']==3) $csv_line .= "\t</ECOTAX>\n";
        else if($idx>2) $csv_line .= "\t</TVA>\n";
      }

      if($idx == count($all_csv_elem)) $csv_line .= "</Client>\n\n</FacNote_xml_export>\n";
		} else if($mode==34) {
			//XML
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);

			if($idx == 1){
				$csv_line .= "<importEntryRequest>\n";
				$csv_line .= "<importDate>".date_html_to_mysql($csv_elem['date'])."</importDate>\n";
				$csv_line .= "<wsImportEntry>\n";
			}

      $csv_elem['num_cpt']=preg_replace('/^\s*/', '', $csv_elem['num_cpt']);
      $csv_elem['num_cpt']=preg_replace('/\s*$/', '', $csv_elem['num_cpt']);
      $csv_elem['num_fact']=preg_replace('/^\s*/', '', $csv_elem['num_fact']);
      $csv_elem['num_fact']=preg_replace('/\s*$/', '', $csv_elem['num_fact']);
      if( (Is_empty_str($csv_elem['debit'])) && (Is_empty_str($csv_elem['credit'])) ) $csv_elem['code_j']='';

      if( (! Is_empty_str($csv_elem['code_j'])) && (! Is_empty_str($csv_elem['date'])) && (! Is_empty_str($csv_elem['description'])) ){
        $csv_line .= "<importEntry>\n";
        $csv_line .= "\t<journalRef>".$csv_elem['code_j']."</journalRef>\n";
        $csv_line .= "\t<date>".date_html_to_mysql($csv_elem['date'])."</date>\n";
        $csv_line .= "\t<piece>".trim($csv_elem['num_fact'])."</piece>\n";
        $csv_line .= "\t<voucherID>".trim($csv_elem['list_liens'][0])."</voucherID>\n";
        $csv_line .= "\t<accountNumber>".$csv_elem['num_cpt']."</accountNumber>\n";
        //$csv_line .= "\t<accountName>".$csv_elem['description']."</accountName>\n";
        $csv_line .= "\t<description>".$csv_elem['description']."</description>\n";
        if(formater_montant($csv_elem['debit'])>0) $csv_line .= "\t<debit>".formater_montant($csv_elem['debit'])."</debit>\n";
        if(formater_montant($csv_elem['credit'])>0) $csv_line .= "\t<credit>".formater_montant($csv_elem['credit'])."</credit>\n";
        $csv_line .= "\t<letter>".$csv_elem['lettrage']."</letter>\n";

        if(isset($csv_elem['quantite1']) && (! Is_empty_str($csv_elem['quantite1']))) $csv_line .= "\t<quantity>".$csv_elem['quantite1']."</quantity>\n";


        if(isset($csv_elem['period1']) && (! Is_empty_str($csv_elem['period1']))) $csv_line .= "\t<periodStart>".date_html_to_mysql($csv_elem['period1'])."</periodStart>\n";
        if(isset($csv_elem['period2']) && (! Is_empty_str($csv_elem['period2']))) $csv_line .= "\t<periodEnd>".date_html_to_mysql($csv_elem['period2'])."</periodEnd>\n";

        //file_put_contents(dirname(__FILE__)."/../upload/csv_line.txt", $csv_elem['base']."*** apres lettrage $csv_line\n".print_r($csv_elem,1), FILE_APPEND);

        if($soc_infos['comptable'] == 'FA1826') {
          if(! Is_empty_str($csv_elem['date_echeance'],1))$csv_line .= "\t<term>".date_html_to_mysql($csv_elem['date_echeance'])."</term>\n";
        }
        $csv_line .= "</importEntry>\n";
      }
			if($idx == count($all_csv_elem)) {
				$csv_line .= "</wsImportEntry>\n";
				$csv_line .= "</importEntryRequest>\n";
			}

		} else if(($mode==2)||($mode==13)||($mode==14)||($mode==17)||($mode==31)){

			//Quadra
			if(strlen($csv_elem['description'])<20)
				for($i=strlen($csv_elem['description']); $i<22;$i++){
					$csv_elem['description'] .= " ";
				}
			//$csv_elem['description']=str_pad($csv_elem['description'], 12, "0", STR_PAD_LEFT);
			//$csv_elem['description']= substr($csv_elem['description'],0,30);
			$montant="";
			if(formater_montant($csv_elem['debit'])>0) {
				$total_debit += formater_montant($csv_elem['debit']);
				$montant="D+".str_pad(preg_replace('/,|\./','',$csv_elem['debit']), 12, "0", STR_PAD_LEFT);
			}
			if(formater_montant($csv_elem['credit'])>0) {
				$total_credit += formater_montant($csv_elem['credit'])+1;
				$montant="C+".str_pad(preg_replace('/,|\./','',$csv_elem['credit']), 12, "0", STR_PAD_LEFT);
			}
			if($csv_elem['position']==1) $csv_elem['num_cpt'] = str_pad($csv_elem['num_cpt'], 8, " ");
      else if(preg_match('/[A-Z]/', $csv_elem['num_cpt'])) $csv_elem['num_cpt'] = str_pad($csv_elem['num_cpt'], 8, " ");
			else $csv_elem['num_cpt'] = str_pad($csv_elem['num_cpt'], 8, "0");

			if(preg_match('/^\s*$/', $csv_elem['num_fact'])) $csv_elem['num_fact']=$csv_elem['path_pj'][0];
      if(Is_empty_str($csv_elem['code_lib'])) $csv_elem['code_lib']=" ";
      $date_echeance="";
      $date_echeance = date_html_to_mysql($csv_elem['date_echeance'],0,1);

      if(preg_match('/^\s*E\s*$/i', $csv_elem['devise'])) $csv_elem['devise']=" ";
      if(preg_match('/^\s*EUR\s*$/i', $csv_elem['devise'])) $csv_elem['devise']=" ";

      $pos_vals = array(
				array('posS'=>1,'posE'=>1, 'val'=>$csv_elem['type'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>2,'posE'=>9, 'val'=>$csv_elem['num_cpt'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>10,'posE'=>11, 'val'=>$csv_elem['code_j'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>12,'posE'=>14, 'val'=>'000', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>15,'posE'=>20, 'val'=>date_html_to_mysql($csv_elem['date'],0,1), 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>21,'posE'=>21, 'val'=>$csv_elem['code_lib'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>22,'posE'=>41, 'val'=>"", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>42,'posE'=>55, 'val'=>$montant, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>56,'posE'=>63, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>64,'posE'=>69, 'val'=>$date_echeance, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>70,'posE'=>71, 'val'=>$csv_elem['lettrage'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>72,'posE'=>99, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>100,'posE'=>107, 'val'=>"", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>108,'posE'=>110, 'val'=>$csv_elem['devise'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>111,'posE'=>113, 'val'=>$csv_elem['code_j'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>114,'posE'=>116, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>117,'posE'=>146, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>147,'posE'=>148, 'val'=>$csv_elem['code_tva'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>149,'posE'=>158, 'val'=>build_agiris_num_fact($csv_elem['num_fact']), 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>159,'posE'=>181, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>182,'posE'=>193, 'val'=>$csv_elem['path_pj'][0], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
			);

      if( (!Is_empty_str(date_html_to_mysql($csv_elem['date'],0,2))) && (! Is_empty_str($csv_elem['description']))  && (! Is_empty_str($csv_elem['num_cpt'])) ) {
        $csv_line .= "\r\n".txt_by_length($pos_vals);
        if(! Is_empty_str($csv_elem['code_ana'])) {
          $pos_vals = array(
            array('posS'=>1,'posE'=>1, 'val'=>'I', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
            array('posS'=>2,'posE'=>6, 'val'=>10000, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
            array('posS'=>7,'posE'=>19, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
            array('posS'=>20,'posE'=>29, 'val'=>$csv_elem['code_ana'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
          );
          $csv_line .= "\r\n".txt_by_length($pos_vals);
        }
      }

			verbose_str_to_file(__FILE__, __FUNCTION__, "generation de $csv_line \n avec :".print_r($csv_elem,1));
			if($csv_elem['add_plancpt']==1) {
        if( ($csv_elem['type_chg'] == 'encaissement')||($csv_elem['type_chg'] == 'facture') ) {
          $typec = 'C';
          $csv_elem['cpt_general']=411000;
        } else {
          $typec = 'F';
          $csv_elem['cpt_general']=401000;
        }

        $pos_vals = array(
          array('posS'=>1,'posE'=>1, 'val'=>'C', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>2,'posE'=>9, 'val'=>$csv_elem['num_cpt'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>10,'posE'=>39, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>40,'posE'=>46, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>47,'posE'=>98, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>99,'posE'=>106, 'val'=>$csv_elem['cpt_general'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>107,'posE'=>217, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>218,'posE'=>218, 'val'=>$typec, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        );
        $plancpt_line .= "\r\n".txt_by_length($pos_vals);

				//$plancpt_line.="\n".'C'.$csv_elem['num_cpt'].str_pad($csv_elem['description'], 30, " ", STR_PAD_RIGHT).
        //             $sepQuad.str_pad(substr($csv_elem['description'],0,7), 7, " ", STR_PAD_RIGHT);
      }
		} else if($mode==38) {
      //KhalifaSfez
      $date=str_replace('/', '', $csv_elem['date']);
      $ref_piece = $csv_elem['num_fact'];
      if(preg_match('/^\s*\d\d\d\d[A-Z]\d+\s*$/', $ref_piece))$ref_piece="";

			$date_echeance="";
      $date_echeance=str_replace('/', '', $csv_elem['date_echeance']);

      if(formater_montant($csv_elem['debit'])>0) $sens = "D";
			else $sens = "C";
			if(formater_montant($csv_elem['debit'])>0) {
				$total_debit += formater_montant($csv_elem['debit']);
				$montant = formater_montant($csv_elem['debit']);
			} else if(formater_montant($csv_elem['credit'])>0) {
				$total_credit += formater_montant($csv_elem['credit']);
				$montant = formater_montant($csv_elem['credit']);
			}

			$montant = preg_replace('/,/','.',$montant);
      //if(($csv_elem['id_type']==$old_id_type)||( ($csv_elem['type_chg']=='banque')&&($csv_elem['type_chg']== $old_type) ) ) {

      if($csv_elem['nouvelleversion'] ==1) {
        if($csv_elem['famille']==$old_famille) {
          $numero_ligne ++;
        } else {
          $numero_ligne=1;
          $old_famille=$csv_elem['famille'];
        }
      } else {
        if($csv_elem['id_type']==$old_id_type) {
          $numero_ligne ++;
        } else {
          $numero_ligne=1;
          $old_id_type=$csv_elem['id_type'];
          $old_type=$csv_elem['type_chg'];
        }
      }



      if(!Is_empty_str($csv_elem['modepaie'])) $nature=$csv_elem['modepaie'];
      else if(($csv_elem['type_element']=="encaissement") || ($csv_elem['type_element']=="facture")) $nature='FCC';
      else $nature='FAF';
      $nature=" ";

      $lettrage="";
      //$lettrage='L';
      //if(! Is_empty_str($csv_elem['lettrage'])) $lettrage='L';

      $pos_vals = array(
        array('posS'=>1,'posE'=>3, 'val'=>$numero_ligne, 'carPad'=>"0", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>4,'posE'=>11, 'val'=>$date, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>12,'posE'=>14, 'val'=>$csv_elem['code_j'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>15,'posE'=>27, 'val'=>$csv_elem['num_cpt'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>28,'posE'=>30, 'val'=>$nature, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>31,'posE'=>40, 'val'=>$ref_piece, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>41,'posE'=>72, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>73,'posE'=>80, 'val'=>$date_echeance, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>81,'posE'=>81, 'val'=>$lettrage, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>82,'posE'=>84, 'val'=>"EUR", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>85,'posE'=>85, 'val'=>$sens, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>86,'posE'=>100, 'val'=>$montant, 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>101,'posE'=>132, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
      );
      if( (!Is_empty_str($date)) && (! Is_empty_str($csv_elem['description']))  && (! Is_empty_str($csv_elem['num_cpt'])) )
        $csv_line .= txt_by_length($pos_vals)."\r\n";

		} else if($mode==42) {
      //Mittler
      if(preg_match('?/?', $csv_elem['date'])){
        $tmp_array = explode('/', $csv_elem['date']);
        $date=$tmp_array[2].$tmp_array[1].$tmp_array[0];
      } else $date=$csv_elem['date'];


      $ref_piece = $csv_elem['num_fact'];

      if(preg_match('?/?', $csv_elem['date_echeance'])){
        $tmp_array = explode('/', $csv_elem['date_echeance']);
        $date_echeance=$tmp_array[2].$tmp_array[1].$tmp_array[0];
      } else $date_echeance=$csv_elem['date_echeance'];

      if(formater_montant($csv_elem['debit'])>0) $sens = "D";
			else $sens = "C";
			if(formater_montant($csv_elem['debit'])>0) {
				$total_debit += formater_montant($csv_elem['debit']);
				$montant = formater_montant($csv_elem['debit']);
			} else if(formater_montant($csv_elem['credit'])>0) {
				$total_credit += formater_montant($csv_elem['credit']);
				$montant = formater_montant($csv_elem['credit']);
			}

			$montant = preg_replace('/,/','.',$montant);
      if($csv_elem['position']==1) $numero_ligne ++;

      if(!Is_empty_str($csv_elem['modepaie'])) $nature=$csv_elem['modepaie'];
      else if(($csv_elem['type_element']=="encaissement") || ($csv_elem['type_element']=="facture")) $nature='FCC';
      else $nature='FAF';

      if(strlen($csv_elem['description'])>25) $csv_elem['description']=substr($csv_elem['description'],0,25);
      $pos_vals = array(
        array('posS'=>1,'posE'=>5, 'val'=>$numero_ligne, 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>6,'posE'=>7, 'val'=>$csv_elem['code_j'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>8,'posE'=>15, 'val'=>$date, 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>16,'posE'=>23, 'val'=>$date_echeance, 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>24,'posE'=>35, 'val'=>$ref_piece, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>36,'posE'=>46, 'val'=>$csv_elem['num_cpt'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>47,'posE'=>76, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>77,'posE'=>84, 'val'=>$montant, 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>85,'posE'=>85, 'val'=>$sens, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>86,'posE'=>97, 'val'=>$ref_piece, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>98,'posE'=>101, 'val'=>$csv_elem['code_ana'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
      );
      $csv_line .= txt_by_length($pos_vals)."\r\n";

		} else if(($mode==20)||($mode==25)||($mode==26)||($mode==44)){
			//ACD Auto
			$montant="";
			if(formater_montant($csv_elem['debit'])>0) $sens = "0";
			else $sens = "1";
			if(formater_montant($csv_elem['debit'])>0) {
				$total_debit += formater_montant($csv_elem['debit']);
				$montant = formater_montant($csv_elem['debit']);
			} else if(formater_montant($csv_elem['credit'])>0) {
				$total_credit += formater_montant($csv_elem['credit']);
				$montant = formater_montant($csv_elem['credit']);
			}
			$montant = preg_replace('/,/','',$montant);
			$montant = preg_replace('/\./','',$montant);
			$montant=str_pad($montant, 12, "0", STR_PAD_LEFT);

			//$csv_elem['num_cpt'] = str_pad($csv_elem['num_cpt'], 8, "0");

			$date_echeance="";
      if(!isset($csv_elem['date_echeance']))$csv_elem['date_echeance']=null;
			if(Is_empty_str($csv_elem['date_echeance']))$date_echeance=date_html_to_mysql($csv_elem['date'],0,2);
			else $date_echeance=date_html_to_mysql($csv_elem['date_echeance'],0,2);

			if(preg_match('/^\s*$/', $csv_elem['num_fact'])) $csv_elem['num_fact']=substr($csv_elem['path_pj'][0],0,8);

			if(preg_match('/^\s*F/i', $csv_elem['num_cpt']) || preg_match('/^\s*C/i', $csv_elem['num_cpt']))$addG='';
			else $addG='G';

      $ajouter_pj=0;
      if($csv_elem['famille'] != $ancienne_famille) {
        $csv_line .= "3\r\n1\r\n";
        $ancienne_famille=$csv_elem['famille'];
        $ajouter_pj=1;
			}

			$ref_piece = $csv_elem['num_fact'];
			if(Is_empty_str($csv_elem['num_fact'])) $ref_piece = $csv_elem['num_piece'];
      $ref_piece=build_agiris_num_fact($ref_piece);
      //if( ($all_csv_elem[0]['base'] == 'FA0933')||($all_csv_elem[0]['base'] == 'FA1447')||($all_csv_elem[0]['base'] == 'V2'))
      $pos_vals = array(
				array('posS'=>1,'posE'=>1, 'val'=>2, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>2,'posE'=>3, 'val'=>'00', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>4,'posE'=>9, 'val'=>date_html_to_mysql($csv_elem['date'],0,2), 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>10,'posE'=>14, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>15,'posE'=>46, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>47,'posE'=>59, 'val'=>$addG.$csv_elem['num_cpt'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>60,'posE'=>60, 'val'=>$sens, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>61,'posE'=>72, 'val'=>$montant, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>73,'posE'=>78, 'val'=>$date_echeance, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>79,'posE'=>81, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>82,'posE'=>82, 'val'=>'0', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>83,'posE'=>124, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>125,'posE'=>128, 'val'=>$csv_elem['code_j'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),

        array('posS'=>129,'posE'=>139, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>140,'posE'=>141, 'val'=>$csv_elem['lettrage'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>142,'posE'=>154, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),

				array('posS'=>155,'posE'=>155, 'val'=>"E", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>156,'posE'=>165, 'val'=>'0', 'carPad'=>"0", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>166,'posE'=>172, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>173,'posE'=>180, 'val'=>$ref_piece, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>181,'posE'=>185, 'val'=>$csv_elem['code_ana'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>186,'posE'=>275, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>276,'posE'=>291, 'val'=>$csv_elem['code_tva'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),

			);
      if( (!Is_empty_str(date_html_to_mysql($csv_elem['date'],0,2))) && (! Is_empty_str($csv_elem['description']))  && (! Is_empty_str($csv_elem['num_cpt'])) )
        $csv_line .= txt_by_length($pos_vals)."\r\n";
      if(!isset($csv_elem['list_liens']))$csv_elem['list_liens']=null;
			if(($mode==25)||($mode==26)) $val_lien=$csv_elem['list_liens'][0];
			else $val_lien=$csv_elem['path_pj'][0];

      $val_lien="";
      if(isset($csv_elem['list_liens'][0])) $val_lien=$csv_elem['list_liens'][0];
      if($mode==44) {
        if(isset($csv_elem['path_pj'][0])) $val_lien=$csv_elem['path_pj'][0];
      }
      if($mode==44){
				$pos_vals = array(
					array('posS'=>1,'posE'=>1, 'val'=>'G', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>2,'posE'=>2, 'val'=>'E', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>3,'posE'=>14, 'val'=>'FACNOTE', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>15,'posE'=>257, 'val'=>$val_lien, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				);
				if( (! preg_match('?/\s*$?', $val_lien)) && ( ($ajouter_pj==1)||($idx==1) ) ) $csv_line .= txt_by_length($pos_vals)."\r\n";
			} else if( ! Is_empty_str($val_lien)) {
				$pos_vals = array(
					array('posS'=>1,'posE'=>1, 'val'=>'U', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>2,'posE'=>2, 'val'=>'L', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>3,'posE'=>257, 'val'=>$val_lien, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				);
				if(! preg_match('?/\s*$?', $val_lien)) $csv_line .= txt_by_length($pos_vals)."\r\n";
			}

			verbose_str_to_file(__FILE__, __FUNCTION__, "gen csv line =>$csv_line");
		} else if(($mode==35)){
			//BOBLink Auto
			$montant="";
			if(formater_montant($csv_elem['debit'])>0) $sens = "0";
			else $sens = "1";
			if(formater_montant($csv_elem['debit'])>0) {
				$total_debit += formater_montant($csv_elem['debit']);
				$montant = formater_montant($csv_elem['debit']);
			} else if(formater_montant($csv_elem['credit'])>0) {
				$total_credit += formater_montant($csv_elem['credit']);
				$montant = formater_montant($csv_elem['credit']);
			}

			$montant = preg_replace('/,/','',$montant);
			$montant = preg_replace('/\./','',$montant);
			$montant=str_pad($montant, 12, "0", STR_PAD_LEFT);

			//$csv_elem['num_cpt'] = str_pad($csv_elem['num_cpt'], 8, "0");
			list($d_entree,$m_entree,$y_entree ) = date_mysql_to_html($csv_elem['date'], 0, 1);

			$date_echeance="";
			if( ! Is_empty_str($csv_elem['date_echeance']))list($d_ech,$m_ech,$y_ech ) = date_mysql_to_html($csv_elem['date_echeance'], 0, 1);
			else {$d_ech=$d_entree;$m_ech=$m_entree;$y_ech=$y_entree;}
			if(preg_match('/^\s*$/', $csv_elem['num_fact'])) $csv_elem['num_fact']=substr($csv_elem['path_pj'][0],0,8);

			if(preg_match('/^\s*F/i', $csv_elem['num_cpt']) || preg_match('/^\s*C/i', $csv_elem['num_cpt']))$addG='';
			else $addG='G';
			if($csv_elem['position'] == 1)$csv_line .= "3\r\n1\r\n";

			$ref_piece = $csv_elem['num_fact'];
			if(Is_empty_str($csv_elem['num_fact'])) $ref_piece = $csv_elem['num_piece'];
      $ref_piece=build_agiris_num_fact($ref_piece);
			$pos_vals = array(
        array('posS'=>1,'posE'=>4, 'val'=>$csv_elem['code_j'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>5,'posE'=>9, 'val'=>$annee_fiscale, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>10,'posE'=>20, 'val'=>$y_entree, 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>21,'posE'=>31, 'val'=>$m_entree, 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>32,'posE'=>42, 'val'=>$csv_elem['id'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>43,'posE'=>53, 'val'=>"$d_entree/$m_entree/".substr($y_entree, 2, 2), 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>54,'posE'=>54, 'val'=>$csv_elem['ttypcie'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>55,'posE'=>64, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>65,'posE'=>75, 'val'=>"$d_ech/$m_ech/".substr($y_ech, 2, 2), 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>76,'posE'=>78, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>79,'posE'=>98, 'val'=>"0,00", 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>99,'posE'=>118, 'val'=>"0,00", 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>119,'posE'=>129, 'val'=>"0", 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>130,'posE'=>140, 'val'=>"$d_entree/$m_entree/".substr($y_entree, 2, 2), 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>141,'posE'=>143, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>144,'posE'=>163, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>164,'posE'=>184, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>185,'posE'=>205, 'val'=>$montant, 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>206,'posE'=>245, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>246,'posE'=>285, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>286,'posE'=>296, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>297,'posE'=>298, 'val'=>"SF", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
			);
      if( (!Is_empty_str(date_html_to_mysql($csv_elem['date'],0,2))) && (! Is_empty_str($csv_elem['description']))  && (! Is_empty_str($csv_elem['num_cpt'])) )
        $csv_line .= txt_by_length($pos_vals)."\r\n";
			if(($mode==25)||($mode==26)) $val_lien=$csv_elem['list_liens'][0];
			else $val_lien=$csv_elem['path_pj'][0];
      $val_lien=$csv_elem['list_liens'][0];

			if( ! Is_empty_str($val_lien)) {
				$pos_vals = array(
					array('posS'=>1,'posE'=>1, 'val'=>'U', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>2,'posE'=>2, 'val'=>'L', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>3,'posE'=>257, 'val'=>$val_lien, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				);
				if(! preg_match('?/\s*$?', $val_lien)) $csv_line .= txt_by_length($pos_vals)."\r\n";
			}

			verbose_str_to_file(__FILE__, __FUNCTION__, "gen csv line =>$csv_line");
		} else if(($mode==32)){
			//Agiris ECR
			$debit=formater_montant($csv_elem['debit']);
			$credit=formater_montant($csv_elem['credit']);
			if($debit>0) $total_debit += $debit;
			if($credit>0) $total_credit += $credit;


      $date_ecr = str_replace('/','',date_mysql_to_html(date('Y-m-d')));

			$date = str_replace('/','',$csv_elem['date']);
      if(!Is_empty_str($csv_elem['period1'])) $csv_elem['period1'] = str_replace('/','',$csv_elem['period1']);
      if(!Is_empty_str($csv_elem['period2'])) $csv_elem['period2'] = str_replace('/','',$csv_elem['period2']);


			//list($d_cur,$m_cur,$y_cur) = date_mysql_to_html(date_html_to_mysql($csv_elem['date']), 0, 1);
			$debut_exercice = str_replace('/','',$csv_elem['debut_exercice']);
      $fin_exercice = str_replace('/','',$csv_elem['fin_exercice']);

			$val_lien=$csv_elem['path_pj'][0];

			$csv_elem['num_fact']= build_agiris_num_fact($csv_elem['num_fact']);


			if($csv_elem['position'] == 1) {
        if( ! Is_empty_str($csv_elem['date_echeance'])) {
          $date_echeance= str_replace('/','',$csv_elem['date_echeance']);
          $pos_vals = array(
            array('posS'=>1,'posE'=>6, 'val'=>'ECHMVT', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
            array('posS'=>7,'posE'=>19, 'val'=>formater_montant($debit+$credit), 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
            array('posS'=>20,'posE'=>29, 'val'=>"100", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
            array('posS'=>30,'posE'=>37, 'val'=>$date_echeance, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          );
          $echmvt = txt_by_length($pos_vals)."\r\n";
        } else $echmvt = "";

                          if($debit==0) $debit="";
                          if($credit==0) $credit="";

        $pos_vals = array(
					array('posS'=>1,'posE'=>6, 'val'=>'CPT', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>7,'posE'=>16, 'val'=>$csv_elem['num_cpt'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
          array('posS'=>17,'posE'=>46, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
				);

        $num_cpt_val = $csv_elem['num_cpt'].$csv_elem['description'];
        if(!isset($cae_fait[$num_cpt_val]))$cae_fait[$num_cpt_val]=null;

        if( ($cae_fait[$num_cpt_val] != 1) && ($csv_elem['type_chg'] != 'banque')){
          $plancpt_line .= txt_by_length($pos_vals)."\r\n";
          $cae_fait[$num_cpt_val]=1;
          $pos_vals = array(
            array('posS'=>1,'posE'=>6, 'val'=>'TIERS', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
            array('posS'=>7,'posE'=>36, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
          );
          if( ( $csv_elem['type_element']=='facture' )||( $csv_elem['type_element']=='encaissement' ) )
            $plancpt_line .= txt_by_length($pos_vals)."\r\n";
        }

        //0110201530092016
        $csv_ged_dir=$csv_elem['ged_dir'];
        $csv_ged_dir=substr($csv_ged_dir, 14,2).substr($csv_ged_dir, 10,2);

        if(strlen($csv_elem['num_piece']) > 8) $csv_elem['num_piece']=substr($csv_elem['num_piece'], -8);
        if(strlen($csv_elem['num_fact']) > 8) $csv_elem['num_fact']=substr($csv_elem['num_fact'], -8);

				$pos_vals = array(
					array('posS'=>1,'posE'=>6, 'val'=>'ECR', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>7,'posE'=>8, 'val'=>$csv_elem['code_j'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>9,'posE'=>16, 'val'=>$date, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>17,'posE'=>24, 'val'=>$csv_elem['num_piece'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>25,'posE'=>54, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>55,'posE'=>76, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>77,'posE'=>79, 'val'=>1, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>80,'posE'=>91, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>92,'posE'=>92, 'val'=>'0', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>93,'posE'=>100, 'val'=>$date_ecr, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>101,'posE'=>108, 'val'=>$date_ecr, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>109,'posE'=>119, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
					array('posS'=>120,'posE'=>120, 'val'=>'0', 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
					array('posS'=>121,'posE'=>123, 'val'=>$csv_elem['devise'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
					array('posS'=>149,'posE'=>178, 'val'=>$val_lien, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>182,'posE'=>186, 'val'=>$csv_ged_dir, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>187,'posE'=>234, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
          array('posS'=>235,'posE'=>236, 'val'=>$csv_elem['reglement'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
				);
				$csv_line .= txt_by_length($pos_vals)."\r\n";
			} else $echmvt = "";

      if($csv_elem['nolibel_mvt'] == 1) $lib_mvt="";
      else $lib_mvt = $csv_elem['description'];

			$pos_vals = array(
				array('posS'=>1,'posE'=>6, 'val'=>'MVT', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>7,'posE'=>16, 'val'=>$csv_elem['num_cpt'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>17,'posE'=>46, 'val'=>$lib_mvt, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>47,'posE'=>59, 'val'=>$debit, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				array('posS'=>60,'posE'=>72, 'val'=>$credit, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>73,'posE'=>83, 'val'=>$csv_elem['quantite1'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>84,'posE'=>94, 'val'=>$csv_elem['quantite2'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>95,'posE'=>102, 'val'=>$csv_elem['num_fact'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>103,'posE'=>104, 'val'=>$csv_elem['code_tva'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>105,'posE'=>159, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>160,'posE'=>167, 'val'=>$csv_elem['period1'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>168,'posE'=>175, 'val'=>$csv_elem['period2'], 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
			);

      if( (! Is_empty_str($csv_elem['description'])) && (! Is_empty_str($csv_elem['num_cpt']) ) )
        $csv_line .= txt_by_length($pos_vals)."\r\n".$echmvt;


      if( ! Is_empty_str($csv_elem['code_ana'])) {
        if(preg_match('/^\s*(\w+)\.(\w+)\s*$/', $csv_elem['code_ana'], $matches)) {
          $csv_elem['code_ana']=$matches[1];
          $csv_elem['decoupe']=$matches[2];
        }
        $pos_vals = array(
          array('posS'=>1,'posE'=>6, 'val'=>'ANAMVT', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>7,'posE'=>12, 'val'=>$csv_elem['code_ana'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>13,'posE'=>14, 'val'=>$csv_elem['decoupe'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
          array('posS'=>15,'posE'=>65, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        );
        $csv_line .= txt_by_length($pos_vals)."\r\n";
      }


			verbose_str_to_file(__FILE__, __FUNCTION__, "gen csv line =>$csv_line");
		} else if(($mode==45)){
			//Sage 100
			$debit=formater_montant($csv_elem['debit']);
			$credit=formater_montant($csv_elem['credit']);

      $sens="D";
      $montant=$debit;
			if($debit>0) $total_debit += $debit;
			if($credit>0) {
        $total_credit += $credit;
        $sens="C";
        $montant=$credit;
      }

			if($debit==0) $debit="";
			if($credit==0) $credit="";

      $date = date_html_to_mysql($csv_elem['date'], 0,1);
			//list($d_cur,$m_cur,$y_cur) = date_mysql_to_html(date_html_to_mysql($csv_elem['date']), 0, 1);
			$debut_exercice = str_replace('/','',$csv_elem['debut_exercice']);
      $fin_exercice = str_replace('/','',$csv_elem['fin_exercice']);

			$val_lien=$csv_elem['path_pj'][0];

			$csv_elem['num_fact']= build_agiris_num_fact($csv_elem['num_fact']);
      //12345678911234567892123456789312345678941234567895123456789012345678901234567890
      //ACH010118FF401000	XF00285	FACT MAT DU 17/10/17	171117C	66.00N	582280	EUR
      //ACH030118FF624200	ABAR	FACT DACHSER 12/2017 BAR	D	526.32N	581953	EUR	1
      //VEN151117FF	411000DUPONT JEAN	X	D	1000.0N	582311	EUR

      $date_echeance = date_html_to_mysql($csv_elem['date_echeance'], 0,1);

      $tmp_line="";
      $tmp_line_noAna="";

      $cell = substr($csv_elem['code_j'],0,3).substr($date,0,6)."FF";
      $tmp_line .= $cell;
      $tmp_line_noAna .= $cell;

      if( ($csv_elem['position'] == 1) && ($csv_elem['type_chg'] != 'banque') ) {
        if(Is_empty_str($csv_elem['cpt_general'])) {
          if( ($csv_elem['type_chg'] == 'encaissement')||($csv_elem['type_chg'] == 'facture') ) $csv_elem['cpt_general']=411000;
          else $csv_elem['cpt_general']=401000;
        }
        $cell = substr($csv_elem['cpt_general'],0,6).";";
        $tmp_line .= $cell;
        $tmp_line_noAna .= $cell;
      } else {
        $cell = substr($csv_elem['num_cpt'],0,6).";";
        $tmp_line .= $cell;
        $tmp_line_noAna .= $cell;
      }


      $pos_vals = array();
      $ligne_ana=0;
      if( ($csv_elem['position'] == 1) && ($csv_elem['type_chg'] != 'banque') ) {
        $cell = "X".substr($csv_elem['num_cpt'],0,6).";";
        $tmp_line .= $cell;
        $tmp_line_noAna .= $cell;
      } else if( ! preg_match('/^\s*44/', $csv_elem['num_cpt'])) {
        $cell = "A".substr($csv_elem['code_ana'],0,6).";";
        $tmp_line .= $cell;
        $ligne_ana=1;
        $tmp_line_noAna .= ";";
      } else $tmp_line .= ";";

      $cell = substr($csv_elem['description'],0,25).";";
      $tmp_line .= $cell;
      $tmp_line_noAna .= $cell;

      $cell = substr($date_echeance,0,6)."$sens;";
      $tmp_line .= $cell;
      $tmp_line_noAna .= $cell;

      $cell = $montant."N;";
      $tmp_line .= $cell;
      $tmp_line_noAna .= $cell;

      $cell = "      ;EUR;";
      $tmp_line .= $cell;
      $tmp_line_noAna .= $cell;

      if($ligne_ana==1) $tmp_line .= "1";
      $tmp_line .= "\r\n";
      $tmp_line_noAna .= "\r\n";

      if($ligne_ana==1) $csv_line .= $tmp_line_noAna;
      $csv_line .= $tmp_line;
			verbose_str_to_file(__FILE__, __FUNCTION__, "gen csv line =>$csv_line");
		} else if(($mode==33)){


			//cegid TRA
			$montant=0;
			$debit=formater_montant($csv_elem['debit']);
			$credit=formater_montant($csv_elem['credit']);
			if($debit>0) {
				$total_debit += $debit;
				$sens='D';
				$montant=$debit;
			}
			if($credit>0) {
				$total_credit += $credit;
				$sens='C';
				$montant=$credit;
			}
			$montant = str_replace('.',',',$montant);

			if($debit==0) $debit="";
			if($credit==0) $credit="";

			$date = str_replace('/','',$csv_elem['date']);

			$date_echeance="";
      if(!isset($csv_elem['date_echeance']))$csv_elem['date_echeance']=null;
			if( ! Is_empty_str($csv_elem['date_echeance']))$date_echeance= str_replace('/','',$csv_elem['date_echeance']);

			$val_lien=$csv_elem['path_pj'][0];
			$cpt_aux="";

			if(Is_empty_str($csv_elem['nature'])) $csv_elem['nature']='DIV';
      if(($csv_elem['type_element']=="encaissement")||($csv_elem['type_element']=="facture")) $csv_elem['nature']='CLI';
      else if($csv_elem['type_element']=="encaissement") $csv_elem['nature']='OD';
      else $csv_elem['nature']='FOU';

			$num_cpt_val = $csv_elem['num_cpt'];
			if( ($csv_elem['position'] == 1) && ($csv_elem['type_chg'] != 'banque') ) {

				$cpt_aux="X".$num_cpt_val;
				$cpt_aux_piece="G".$num_cpt_val;
				$pos_vals = array(
					array('posS'=>1,'posE'=>6, 'val'=>"***CAE", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>7,'posE'=>23, 'val'=>$num_cpt_val, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>24,'posE'=>58, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>59,'posE'=>61, 'val'=>$csv_elem['nature'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>62,'posE'=>62, 'val'=>'X', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					array('posS'=>63,'posE'=>79, 'val'=>$csv_elem['cpt_general'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
				);

        if(! isset($cae_fait[$num_cpt_val])) $cae_fait[$num_cpt_val]=null;
				if( ($cae_fait[$num_cpt_val] != 1) && ($csv_elem['type_chg'] != 'banque')) $plancpt_line .= txt_by_length($pos_vals)."\r\n";
				$cae_fait[$num_cpt_val]=1;

				if($csv_elem['type_chg'] != 'banque') $num_cpt_val=$csv_elem['cpt_general'];

			}
      if($csv_elem['type_chg'] == 'banque') {
        $cpt_aux="";
        if(! Is_empty_str($csv_elem['cpt_aux'])) $cpt_aux="X".$csv_elem['cpt_aux'];
      }

      if( preg_match('/^\s*5\d+\s*$/', $csv_elem['num_cpt']) && ($csv_elem['position'] == 1) ) {
        $cpt_aux="";
        $num_cpt_val=$csv_elem['num_cpt'];
      }


      if($csv_elem['type_chg'] == 'banque') {
        //if( (! preg_match('/^\s*(6|7|44|5|1)/', $csv_elem['num_cpt'])) && (! preg_match('/^\s*\d+/', $csv_elem['num_cpt'])) ) {
        if( (! preg_match('/^\s*\d+\s*$/', $csv_elem['num_cpt'])) ) {
          $cpt_aux="X".$csv_elem['num_cpt'];
          $num_cpt_val=$csv_elem['cpt_general'];
        }
      }

      $folio="1";
      if(!Is_empty_str($all_csv_elem[0]['etablissement'])) $etablissement=substr(str_pad($all_csv_elem[0]['etablissement'], 3, "0", STR_PAD_LEFT),0,3);
      else $etablissement="001";

      $ref_piece = $csv_elem['num_fact'];
      //if( preg_match('/^\s*\d\d\d\d[A-Z]\d+\s*$/', $ref_piece) || ($csv_elem['type_chg'] != 'banque') ) $ref_piece="";
      $devise="";
      if( ! Is_empty_str($csv_elem['devise'])) $devise=$csv_elem['devise'];
      if(preg_match('/EUR/i', $devise)) $devise="";


			$pos_vals = array(
        array('posS'=>1,'posE'=>3, 'val'=>$csv_elem['code_j'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>4,'posE'=>11, 'val'=>$date, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>12,'posE'=>13, 'val'=>'OD', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>14,'posE'=>30, 'val'=>$num_cpt_val, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>31,'posE'=>48, 'val'=>$cpt_aux, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>49,'posE'=>83, 'val'=>$ref_piece, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>84,'posE'=>118, 'val'=>$csv_elem['description'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>119,'posE'=>121, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>122,'posE'=>129, 'val'=>$date_echeance, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>130,'posE'=>130, 'val'=>$sens, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>131,'posE'=>150, 'val'=>$montant, 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>151,'posE'=>151, 'val'=>'N', 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
        array('posS'=>152,'posE'=>159, 'val'=>$folio, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>160,'posE'=>162, 'val'=>$devise, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>163,'posE'=>172, 'val'=>" ", 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
        array('posS'=>173,'posE'=>222, 'val'=>'E--                                        '.$etablissement.'   1', 'carPad'=>" ", 'sensPad'=>STR_PAD_LEFT),
      );
      if( (!Is_empty_str($date)) && (! Is_empty_str($csv_elem['description']))  && (! Is_empty_str($csv_elem['num_cpt'])) )
        $csv_line .= txt_by_length($pos_vals)."\r\n";

      if(!isset($csv_elem['cptreso_add_img']))$csv_elem['cptreso_add_img']=null;
			if( ( ($csv_elem['position'] == 1) && ($csv_elem['type_chg'] != 'banque') ) || ($csv_elem['cptreso_add_img'] == 1) ){
				if(! Is_empty_str($csv_elem['path_pj'][0])) {
					$pos_vals = array(
						array('posS'=>1,'posE'=>3, 'val'=>$csv_elem['code_j'], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
						array('posS'=>4,'posE'=>11, 'val'=>$date, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
						array('posS'=>12,'posE'=>13, 'val'=>'OD', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
						array('posS'=>14,'posE'=>30, 'val'=>$num_cpt_val, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
						array('posS'=>31,'posE'=>48, 'val'=>$cpt_aux_piece, 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
						array('posS'=>49,'posE'=>83, 'val'=>' ', 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
						array('posS'=>84,'posE'=>333, 'val'=>$csv_elem['path_pj'][0], 'carPad'=>" ", 'sensPad'=>STR_PAD_RIGHT),
					);
          if( (!Is_empty_str($date)) && (! Is_empty_str($csv_elem['code_j']))  && (! Is_empty_str($num_cpt_val)) )
            $csv_line .= txt_by_length($pos_vals)."\r\n";
				}
			}

			//if($idx == count($all_csv_elem)) $csv_line .= $justif_to_add;

			verbose_str_to_file(__FILE__, __FUNCTION__, "gen csv line =>$csv_line");
		} else if($mode==3){
			//HTML
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);

			$csv_line .= "\n<tr>".
                "<td>".$csv_elem['date']."</td><td>".$csv_elem['code_j']."</td>".
                "<td>".$csv_elem['num_cpt']."</td>".
                '<td>';
			foreach($csv_elem['path_pj'] as $file_path){
				$csv_line .= '<a target="_BLANK" href="'.$file_path.'">'.$file_path.'</a>';
			}
			$csv_line .= '</td>'.
                "<td>".$csv_elem['description']."</td><td>".$csv_elem['debit']."</td>".
                "<td>".$csv_elem['credit']."</td><td>".$csv_elem['devise']."</td><td>".$csv_elem['num_fact']."</td></tr>";
		} else if($mode==4){
			//?
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);

			$csv_line .= $csv_elem['code_j'].";".$csv_elem['description'].";".$idx.";".$csv_elem['date'].";".
                $csv_elem['num_cpt'].";".$csv_elem['description'].";;;".$csv_elem['path_pj'][0].";".$csv_elem['date'].";".$csv_elem['description'].";".
                $csv_elem['debit'].";".$csv_elem['credit'].";;;".$csv_elem['date'].";".$csv_elem['num_fact'].";;\n";
		} else if($mode==5){
			//Cador
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);
			list($d_entree,$m_entree,$y_entree ) = date_mysql_to_html(date_html_to_mysql($csv_elem['date']), 1, 1);
			verbose_str_to_file(__FILE__, __FUNCTION__, "d_entree $d_entree,$m_entree,$y_entree:");

			$csv_line .= "$d_entree;$m_entree;$y_entree;".$csv_elem['code_j'].";".
                $csv_elem['num_cpt'].";".$csv_elem['description'].";".
                $csv_elem['debit'].";".$csv_elem['credit'].";".$csv_elem['devise'].";".$csv_elem['num_fact']."\n";
		} else if($mode==19){
			//Cador date complet
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);

			$csv_line .= $csv_elem['date'].";".$csv_elem['code_j'].";".
                $csv_elem['num_cpt'].";".$csv_elem['description'].";".
                $csv_elem['debit'].";".$csv_elem['credit'].";".$csv_elem['devise'].";".$csv_elem['num_fact']."\n";
		} else if($mode==6){
			// EBP
			if(formater_montant($csv_elem['debit'])>0) {
				$total_debit += formater_montant($csv_elem['debit']);
				$montant=$csv_elem['debit'];
				$signe='D';
			}
			if(formater_montant($csv_elem['credit'])>0) {
				$total_credit += formater_montant($csv_elem['credit']);
				$montant=$csv_elem['credit'];
				$signe='C';
			}
      //$date=date_html_to_mysql($csv_elem['date'],0,3);
      $date=$csv_elem['date'];
      //if( ! Is_empty_str($csv_elem['date_echeance']))$date_echeance= date_html_to_mysql($csv_elem['date_echeance'],0,3);
      if( ! Is_empty_str($csv_elem['date_echeance']))$date_echeance= $csv_elem['date_echeance'];
      else $date_echeance = $date;
			verbose_str_to_file(__FILE__, __FUNCTION__, "total_debit $total_debit total_credit $total_credit:");
			verbose_str_to_file(__FILE__, __FUNCTION__, "montant_a_mettre $montant signe $signe");

      $csv_elem['debit']=preg_replace('/,/','.',$csv_elem['debit']);
      $csv_elem['credit']=preg_replace('/,/','.',$csv_elem['credit']);
      if(! Is_empty_str($csv_elem['list_liens'][0])) $lien_facture = "URL:".$csv_elem['list_liens'][0];

      //compte 6 ou 7: mettre montant
      if(! preg_match('/^\s*(6|7)/', $csv_elem['num_cpt'])) {
        $csv_elem['code_ana']="";
        $montant="";
      }
      if( (!Is_empty_str($csv_elem['cpt_general'])) && ($csv_elem['position']==1)) {
        $csv_elem['auxiliaire_desc']=$csv_elem['description'];
        $csv_elem['auxiliaire_cpt']=$csv_elem['num_cpt'];
        $csv_elem['num_cpt'] = $csv_elem['cpt_general'];
      }


			$csv_line .= $csv_elem['code_j'].";".$date.";".$csv_elem['num_cpt'].";".$csv_elem['description'].";".$csv_elem['auxiliaire_cpt'].";".
                $csv_elem['auxiliaire_desc'].";".$csv_elem['num_fact'].";".$csv_elem['debit'].";".$csv_elem['credit'].";".$date_echeance.";$idx;".$lien_facture.";".
                $csv_elem['code_ana'].";".$montant."\n";

		} else if($mode==21){
			// EIC Pasquier

			$montantD = formater_montant($csv_elem['debit']);
			$montantD = str_replace(",",".", $montantD);
			$montantC = formater_montant($csv_elem['credit']);
			$montantC = str_replace(",",".", $montantC);

			$file_name_ext = pathinfo($csv_elem['path_pj'][0]);
			$numero_piece=$file_name_ext['filename'];

			verbose_str_to_file(__FILE__, __FUNCTION__, "total_debit $total_debit total_credit $total_credit:");
			verbose_str_to_file(__FILE__, __FUNCTION__, "montant_a_mettre $montant signe $signe");
			$csv_line .= date_html_to_mysql($csv_elem['date'],0,3).";"
                .$csv_elem['num_cpt']
                .";".$csv_elem['code_j']
                .";".$csv_elem['num_fact']
                .";".$csv_elem['description']
                .";".$montantD.";".$montantC
                .";".$numero_piece."\n";

		} else if(($mode==7)||($mode==46)){
			// EIC
			if(formater_montant($csv_elem['debit'])>0) {
				$total_debit += formater_montant($csv_elem['debit']);
				$montant=$csv_elem['debit'];
				$signe='D';
			}
			if(formater_montant($csv_elem['credit'])>0) {
				$total_credit += formater_montant($csv_elem['credit']);
				$montant=$csv_elem['credit'];
				$signe='C';
			}

			$file_name_ext = pathinfo($csv_elem['path_pj'][0]);
			$numero_piece=$file_name_ext['filename'];

			verbose_str_to_file(__FILE__, __FUNCTION__, "total_debit $total_debit total_credit $total_credit:");
			verbose_str_to_file(__FILE__, __FUNCTION__, "montant_a_mettre $montant signe $signe");
      $path_pj = $csv_elem['path_pj'][0];
      if($dbName=='FA8241') $path_pj = "D:\\SAUVEGARDE FICHIERS TRAVAIL\\001-CONCORDE\\001-COMPTA\\FAC-NOTE\\".$csv_elem['path_pj'][0];

      $period="";
      if($mode==46) {
        $path_pj = trim($csv_elem['list_liens'][0]);
        if(!Is_empty_str($csv_elem['period1'])) {
          $period = ";".$csv_elem['period1'].";".$csv_elem['period2'];
        }
      }

      $csv_line .= $csv_elem['num_cpt'].";".$csv_elem['description'].";".$csv_elem['date'].";".$csv_elem['code_j'].";".$csv_elem['description'].";".$montant.";".$signe.";".$csv_elem['num_piece'].";".$csv_elem['num_fact'].";;".$csv_elem['code_ana'].";".$path_pj.";;".$csv_elem['num_fact']."$period\n";


		} else if($mode==39){
			// EIC c3s
			if(formater_montant($csv_elem['debit'])>0) {
				$total_debit += formater_montant($csv_elem['debit']);
				$montant=$csv_elem['debit'];
				$signe='D';
			}
			if(formater_montant($csv_elem['credit'])>0) {
				$total_credit += formater_montant($csv_elem['credit']);
				$montant=$csv_elem['credit'];
				$signe='C';
			}

			$file_name_ext = pathinfo($csv_elem['path_pj'][0]);
			$numero_piece=$file_name_ext['filename'];

			verbose_str_to_file(__FILE__, __FUNCTION__, "total_debit $total_debit total_credit $total_credit:");
			verbose_str_to_file(__FILE__, __FUNCTION__, "montant_a_mettre $montant signe $signe");
			$csv_line .= $csv_elem['code_j'].";".$csv_elem['date'].";".$csv_elem['num_cpt'].";".$csv_elem['num_fact'].";".$csv_elem['description'].";".$csv_elem['debit'].";".$csv_elem['credit'].";".$csv_elem['path_pj_ext'][0]."\n";
		} else if(($mode==8)||($mode==30)){
			// cegid
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);

			$csv_line .= $csv_elem['code_j'].";".$csv_elem['description'].";".$idx.";".$csv_elem['date'].";".
                $csv_elem['num_cpt'].";".$csv_elem['description'].";;;".$csv_elem['path_pj'][0].";".$csv_elem['date'].";".$csv_elem['description'].";".
                $csv_elem['debit'].";".$csv_elem['credit'].";;;".$csv_elem['date'].";".$csv_elem['num_fact'].";;\n";
		} else if($mode==9){
			// isacompta
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);
			$csv_line .= "MVT;".substr($csv_elem['num_cpt'],0,10).";".$csv_elem['description'].";".$csv_elem['debit'].";".$csv_elem['credit'].";".$csv_elem['num_fact'].";;\n";
		} else if($mode==10){
			// isacompta2
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);

			$csv_line .= $csv_elem['date'].";".$csv_elem['code_j'].";".
                $csv_elem['num_cpt'].";".$csv_elem['path_pj'][0].";".$csv_elem['description'].";".
                $csv_elem['debit'].";".$csv_elem['credit'].";".$csv_elem['num_fact']."\n";
		} else if(($mode==11)){
			//Cador avec PJ et sur 6 caracteres
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);
			list($d_entree,$m_entree,$y_entree ) = date_mysql_to_html(date_html_to_mysql($csv_elem['date']), 1, 1);
			verbose_str_to_file(__FILE__, __FUNCTION__, "d_entree $d_entree,$m_entree,$y_entree:");

			$csv_line .= "$d_entree;$m_entree;$y_entree;".$csv_elem['code_j'].";".
                $csv_elem['num_cpt'].";".$csv_elem['description'].";".
                $csv_elem['debit'].";".$csv_elem['credit'].";".$csv_elem['devise'].";".$csv_elem['path_pj'][0].";".$csv_elem['num_fact']."\n";

		} else if($mode==12){
			// lellouche
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);

			$csv_line .= $csv_elem['date'].";".strtolower($csv_elem['code_j']).";".
                $csv_elem['num_cpt'].";".$csv_elem['path_pj'][0].";".$csv_elem['description'].";".
                $csv_elem['debit'].";".$csv_elem['credit'].";".$csv_elem['devise'].";".$csv_elem['num_fact']."\n";

		} else if($mode==15){
      //Bonjour, Je m'excuse pour ce retard de réponse. Comme vu avec Ibiza mettre 0 au lieu de mettre un chiffre dans les reglages paramètres (longeur des comptes auxiliaires)
      // regupare les taux de la banque centrale europeenne
			// ibiza
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);

			$csv_line .= $csv_elem['date'].";".strtolower($csv_elem['code_j']).";".
                $csv_elem['num_cpt'].";".$csv_elem['debit'].";".$csv_elem['credit'].";".
                $csv_elem['description'].";".
                $csv_elem['path_pj'][0].";;;".
                $csv_elem['num_fact']."\n";

		} else if($mode==16){
			// isacompta agri plus
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);
			$csv_line .=
                $csv_elem['code_j'].";"
                .$csv_elem['date'].";"
                .$csv_elem['num_cpt'].";"
                .$csv_elem['description'].";"
                .$csv_elem['num_fact'].";"
                .";"
                .$csv_elem['debit'].";"
                .$csv_elem['credit'].";"
                ."\n";

		} else if($mode==23){
			// sage
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);
			$montant=formater_montant($csv_elem['debit'])+formater_montant($csv_elem['credit']);
			if(formater_montant($csv_elem['debit'])>0) $sens = "D";
			else $sens = "C";

			$csv_line .= "\t".$csv_elem['date']." ".$csv_elem['code_j']." ".
                $csv_elem['num_cpt']." \"".$csv_elem['num_fact']."\" \"".$csv_elem['description']."\" ".$sens." ".$montant." E"."\n";

		} else if($mode==24){
			// cogilog
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);
			if(Is_empty_str($csv_elem['date_echeance']))$date_echeance=date_html_to_mysql($csv_elem['date'],0,4);
			else $date_echeance=date_html_to_mysql($csv_elem['date_echeance'],0,4);

			$csv_line .=
                $csv_elem['code_j']
                ."\t".date_html_to_mysql($csv_elem['date'],0,4)
                ."\t".$csv_elem['num_fact']
                ."\t".$csv_elem['num_cpt']
                ."\t"
                ."\t".$csv_elem['description']
                ."\t".$date_echeance
                ."\t".$csv_elem['debit']
                ."\t".$csv_elem['credit']
                ."\t".$csv_elem['lettrage']
                ."\t"."\t"."\t"."\t"."\t"."\t"."\t"."\t"."\t"."\t"."\t"
                ."\t".$csv_elem['list_liens'][0]
                ."\n";
		} else if($mode==43){
			// CIEL FR
			$montant="";
			if(formater_montant($csv_elem['debit'])>0) $sens = "D";
			else $sens = "C";
			if(formater_montant($csv_elem['debit'])>0) {
				$total_debit += formater_montant($csv_elem['debit']);
				$montant=preg_replace('/\./',',',$csv_elem['debit']);
			}
			if(formater_montant($csv_elem['credit'])>0) {
				$total_credit += formater_montant($csv_elem['credit'])+1;
				$montant=preg_replace('/\./',',',$csv_elem['credit']);
			}
			if(Is_empty_str($csv_elem['date_echeance']))$date_echeance=$csv_elem['date'];
			else $date_echeance=$csv_elem['date_echeance'];

			if($csv_elem['position']==1)$csv_line .="##Transfert\r\n##Section	Dos\r\nEUR\r\n##Section	Mvt\r\n";
			$csv_line .='"580'
                .'"'."\t".'"'.$csv_elem['code_j']
                .'"'."\t".'"'.$csv_elem['date']
                .'"'."\t".'"'.$csv_elem['num_cpt']
                .'"'."\t".'"'.$csv_elem['description']
                .'"'."\t".'"'.$montant.'"'."\t$sens"
                ."\tB"
                ."\t".'"'.$csv_elem['description']
                .'"'."\t".'"'.$csv_elem['num_fact']
                .'"'."\t".'"'.'10';

			if($csv_elem['position']==1){
				$csv_line .=
                  '"'."\t".'"'.$date_echeance
                  .'"'."\t".'"'.$csv_elem['num_fact']
                  .'"'."\t".'"'."LET"
                  .'"'."\t".'"'.$csv_elem['date'].'"'
                  ."\r\n";
			} else if($csv_elem['position']==4){
				$csv_line .=
                  '"'."\t".'"'."LET"
                  .'"'."\t".'"'.$date_echeance.'"'
                  ."\r\n";
			} else {
				$csv_line .=
                  '"'."\t".'"'."CTI"
                  .'"'."\t".'"'.$csv_elem['description'].'"'
                  ."\r\n";
			}
		} else if($mode==27){
			// CIEL
			$montant="";
			if(formater_montant($csv_elem['debit'])>0) $sens = "D";
			else $sens = "C";
			if(formater_montant($csv_elem['debit'])>0) {
				$total_debit += formater_montant($csv_elem['debit']);
				$montant=preg_replace('/,/','.',$csv_elem['debit']);
			}
			if(formater_montant($csv_elem['credit'])>0) {
				$total_credit += formater_montant($csv_elem['credit'])+1;
				$montant=preg_replace('/,/','.',$csv_elem['credit']);
			}
			if(Is_empty_str($csv_elem['date_echeance']))$date_echeance=date_html_to_mysql($csv_elem['date'],0,3);
			else $date_echeance=date_html_to_mysql($csv_elem['date_echeance'],0,3);

			if($csv_elem['position']==1)$csv_line .="##Transfert\r\n##Section	Dos\r\nEUR\r\n##Section	Mvt\r\n";
			$csv_line .='"580'
                .'"'."\t".'"'.$csv_elem['code_j']
                .'"'."\t".'"'.date_html_to_mysql($csv_elem['date'],0,3)
                .'"'."\t".'"'.$csv_elem['num_cpt']
                .'"'."\t".'"'.$csv_elem['description']
                .'"'."\t".'"'.$montant.'"'.$sens
                ."\tB"
                ."\t".'"'.$csv_elem['description']
                .'"'."\t".'"'.$csv_elem['num_fact']
                .'"'."\t".'"'.'10';

			if($csv_elem['position']==1){
				$csv_line .=
                  '"'."\t".'"'.$date_echeance
                  .'"'."\t".'"'.$csv_elem['num_fact']
                  .'"'."\t".'"'."LET"
                  .'"'."\t".'"'.date_html_to_mysql($csv_elem['date'],0,3).'"'
                  ."\r\n";
			} else if($csv_elem['position']==4){
				$csv_line .=
                  '"'."\t".'"'."LET"
                  .'"'."\t".'"'.$date_echeance.'"'
                  ."\r\n";
			} else {
				$csv_line .=
                  '"'."\t".'"'."CTI"
                  .'"'."\t".'"'.$csv_elem['description'].'"'
                  ."\r\n";
			}
		} else if($mode==28){
			// sage
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);

			$csv_line .= $csv_elem['date'].";".$csv_elem['code_j'].";".$csv_elem['type_chg'].";".$csv_elem['pos_txt'].";".$csv_elem['taux'].";".
                $csv_elem['num_cpt'].";".$csv_elem['path_pj'][0].";".$csv_elem['description'].";".
                $csv_elem['debit'].";".$csv_elem['credit'].";".$csv_elem['devise'].";".$csv_elem['num_fact']."\n";
		} else if($mode==29){
			// cegid pasquier
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);

			$csv_line .= $csv_elem['code_j'].";".$csv_elem['date'].";".$csv_elem['num_cpt'].";".$csv_elem['auxiliaire'].";".$csv_elem['reference'].";".
                $csv_elem['description'].";".
                $csv_elem['debit'].";".$csv_elem['credit']."\n";
		} else if( ($mode==36)||($mode==1)||($mode==41) ) {


			// sage XLS
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);


      $key_to_chg = 'date';
      if(!isset($csv_elem[$key_to_chg])) $csv_elem[$key_to_chg]=null;
      $csv_elem[$key_to_chg] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg]);
      $csv_elem[$key_to_chg] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg]);
      $key_to_chg = 'date_echeance';
      if(!isset($csv_elem[$key_to_chg])) $csv_elem[$key_to_chg]=null;
      $csv_elem[$key_to_chg] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg]);
      $csv_elem[$key_to_chg] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg]);
      $key_to_chg = 'code_j';
      if(!isset($csv_elem[$key_to_chg])) $csv_elem[$key_to_chg]=null;
      $csv_elem[$key_to_chg] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg]);
      $csv_elem[$key_to_chg] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg]);
      $key_to_chg = 'num_cpt';
      if(!isset($csv_elem[$key_to_chg])) $csv_elem[$key_to_chg]=null;
      $csv_elem[$key_to_chg] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg]);
      $csv_elem[$key_to_chg] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg]);
      $key_to_chg = 'path_pj';
      if(!isset($csv_elem[$key_to_chg])) $csv_elem[$key_to_chg]=null;
      $csv_elem[$key_to_chg] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg]);
      $csv_elem[$key_to_chg] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg]);
      $key_to_chg = 'description';
      if(!isset($csv_elem[$key_to_chg])) $csv_elem[$key_to_chg]=null;
      $csv_elem[$key_to_chg] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg]);
      $csv_elem[$key_to_chg] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg]);
      $key_to_chg = 'devise';
      if(!isset($csv_elem[$key_to_chg])) $csv_elem[$key_to_chg]=null;
      $csv_elem[$key_to_chg] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg]);
      $csv_elem[$key_to_chg] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg]);
      $key_to_chg = 'code_ana';
      if(!isset($csv_elem[$key_to_chg])) $csv_elem[$key_to_chg]=null;
      $csv_elem[$key_to_chg] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg]);
      $csv_elem[$key_to_chg] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg]);
      $key_to_chg = 'ged_dir';
      if(!isset($csv_elem[$key_to_chg])) $csv_elem[$key_to_chg]=null;
      $csv_elem[$key_to_chg] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg]);
      $csv_elem[$key_to_chg] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg]);
      $key_to_chg = 'path_pj_ext';
      if(!isset($csv_elem[$key_to_chg])) $csv_elem[$key_to_chg]=null;
      $csv_elem[$key_to_chg][0] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg][0]);
      $csv_elem[$key_to_chg][0] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg][0]);
      $csv_elem[$key_to_chg][0] = preg_replace('/\.\w+\s*$/', '', $csv_elem[$key_to_chg][0]);
      $key_to_chg = 'path_pj';
      $csv_elem[$key_to_chg][0] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg][0]);
      $csv_elem[$key_to_chg][0] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg][0]);
      $csv_elem[$key_to_chg][0] = preg_replace('/\.\w+\s*$/', '', $csv_elem[$key_to_chg][0]);
      if(Is_empty_str($csv_elem['devise'])) $csv_elem['devise']='E';

      if( ($mode==1)||($mode==36) ) {
        if(($soc_infos['comptable'] == 'FA5287')||($dbName == 'V2')) {
          if($csv_elem['num_fact'] != $csv_elem['num_piece']) $csv_elem['description'] .= " ".$csv_elem['num_fact'];
        }

        $donnees=array($csv_elem['date'],$csv_elem['code_j'],$csv_elem['num_cpt'], $csv_elem['path_pj'][0]." ", $csv_elem['description'],
                       $csv_elem['debit'], $csv_elem['credit'],$csv_elem['devise'],$csv_elem['lettrage']);

      } else {

        if( ($csv_elem['type_element']=='encaissement')||($csv_elem['type_element']=='banque') ) 1;
        else {
          if($csv_elem['num_fact'] != $csv_elem['path_pj'][0]) $csv_elem['description'] .= " ".$csv_elem['num_fact'];
        }
        $donnees=array($csv_elem['date'],$csv_elem['code_j'],$csv_elem['num_cpt'], $csv_elem['path_pj'][0]." ",
                       $csv_elem['description'], $csv_elem['debit'], $csv_elem['credit'],$csv_elem['devise']);

        if($soc_infos['comptable'] == 'FA2258') $donnees[] = $csv_elem['code_ana'];
        else $donnees[]=$csv_elem['lettrage'];

        if(($soc_infos['comptable'] == 'FA2604')||($csv_elem['base'] == 'FB0697')||($csv_elem['base'] == 'FA7038')||($dbName == 'V2')) {
          $donnees[]=$csv_elem['date_echeance'];
        }
      }
      if($mode==1) {
        if(preg_match('/^\s*6|7/', $csv_elem['num_cpt'])) $donnees[]=$csv_elem['code_ana'];
      }
      $csv_line_arr[] = $donnees;
      //verbose_str_to_file(__FILE__, __FUNCTION__, "return CSV enntry $plancpt_line.".print_r($csv_line,1));
    } else if( ($mode==37) ) {

      // soddec
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);
			if(formater_montant($csv_elem['debit'])>0) $sens = "D";
			else $sens = "C";
      $montant=formater_montant($csv_elem['debit']) + formater_montant($csv_elem['credit']);
      $montant=formater_montant($montant, 0,0,0,0,2);
			$csv_line .= $idx.",".date_html_to_mysql($csv_elem['date'],0,1).",".$csv_elem['code_j'].",".$csv_elem['num_cpt'].",".",".$csv_elem['description'].",".$csv_elem['num_fact'].",".$montant.",".$sens."\n";

		} else {
			// tableau csv
			$total_debit += formater_montant($csv_elem['debit']);
			$total_credit += formater_montant($csv_elem['credit']);

      foreach(array('date','code_j','num_cpt', 'path_pj', 'description', 'debit', 'credit','devise', 'code_ana', 'lettrage', 'ged_dir', 'path_pj_ext') as $key_to_chg) {
        if( ($key_to_chg=='path_pj_ext')||($key_to_chg=='path_pj')){

          $csv_elem[$key_to_chg][0] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg][0]);
          $csv_elem[$key_to_chg][0] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg][0]);
          $csv_line .= $csv_elem[$key_to_chg][0].";";
          verbose_str_to_file(__FILE__, __FUNCTION__, "pour key_to_chg=$key_to_chg CSV enntry $csv_line\n.".print_r($csv_elem,1));
        } else {
          if(($key_to_chg=='num_cpt')&& (preg_match('/^\s*40/', $csv_elem[$key_to_chg])||preg_match('/^\s*41/', $csv_elem[$key_to_chg])) )
            $csv_elem[$key_to_chg] = str_pad($csv_elem[$key_to_chg], 8, "0",STR_PAD_RIGHT);
          $csv_elem[$key_to_chg] = preg_replace('/^\s*/', '', $csv_elem[$key_to_chg]);
          $csv_elem[$key_to_chg] = preg_replace('/\s*$/', '', $csv_elem[$key_to_chg]);
          $csv_line .= $csv_elem[$key_to_chg].";";
        }
      }
      $csv_line .= "\r\n";
		}
	}
	if(($mode==20)||($mode==44)||($mode==25)||($mode==26)) {
		if(count($all_csv_elem) >0) {
			$csv_line .= "3\n";
			$csv_line = preg_replace('/^3\s/', '', $csv_line);
			$csv_line = str_replace("\n", "\r\n", $csv_line);
			$csv_line = preg_replace('/^\s*/', '', $csv_line);
			$csv_line = preg_replace('/\s*$/', '', $csv_line);
      if(! preg_match('/^1\s/', $csv_line)) $csv_line = "1\r\n".$csv_line;
		}
	}

	verbose_str_to_file(__FILE__, __FUNCTION__, "return CSV enntry $plancpt_line.".print_r($csv_line,1));

  if(($mode==36)||($mode == 1)||($mode==41)) return $csv_line_arr;
  else return $plancpt_line.$csv_line;

}


function all_csv_elem_to_zip($all_csv_elem, $export_mode, $zip_dir, $zip_dir_name, $base, $cab_dossier, $fin_exercice, $debut_exercice, $long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $sa1,$sa2,$sa3,$sa4,$sa5,$etablissement,$agirismanuel ) {

  verbose_str_to_file(__FILE__, __FUNCTION__, "$export_mode, $zip_dir, $zip_dir_name\n");

	$csv_content=$html_csv_content=$quadra_csv_content="";
	$export_found=0;

  $params_export=array();
  $params_export['fin_exercice']=$fin_exercice;
  $params_export['debut_exercice']=$debut_exercice;
  $params_export['long_cpt']=$long_cpt;
  $params_export['long_cpt_gen_ac']=$long_cpt_gen_ac;
  $params_export['long_cpt_gen_ve']=$long_cpt_gen_ve;
  $params_export['long_aux']=$long_aux;
  $params_export['long_aux_ve']=$long_aux_ve;
  $params_export['sa1']=$sa1;
  $params_export['sa2']=$sa2;
  $params_export['sa3']=$sa3;
  $params_export['sa4']=$sa4;
  $params_export['sa5']=$sa5;
  $params_export['etablissement']=$etablissement;
  $exercice=$all_csv_elem[0]['date'];
  if(Is_empty_str($exercice,1))$exercice=$all_csv_elem[1]['date'];
  if(Is_empty_str($exercice,1))$exercice=$all_csv_elem[2]['date'];
  if(Is_empty_str($exercice,1))$exercice=$all_csv_elem[3]['date'];
  $exercice = build_ged_dir_date($exercice, $params_export['fin_exercice'], $params_export['debut_exercice'], $export_mode);

  $etablissement='';
	if($export_mode==32) {
		$csv_content = "VER   020000000000"."\r\n";
		$csv_content .= "DOS   ".str_pad($cab_dossier, 8, " ",STR_PAD_RIGHT)."$zip_dir_name"."\r\n";
    if( ($exercice=='0101201731122017') && ($base=="FA1073") ) $exercice = '0106201731122017';
    $csv_content .= "EXO   $exercice"."\r\n";
	}
	if($export_mode==33) {
		$csv_content = "***S5EXPJRLETE                   007                                                                                                            001"."\r\n";
		$csv_content .= "***PS1"."\r\n";
    $long_cpt_generaux=8;
    $tmp_val=formater_montant($params_export['long_cpt'], 0, 0, 0, 0, 0);
    if($tmp_val > 0) $long_cpt_generaux=$tmp_val;
    else if(strlen($params_export['long_cpt_gen_ac'])>0) $long_cpt_generaux=strlen($params_export['long_cpt_gen_ac']);
    else if( (strlen($params_export['long_cpt_gen_ve'])>0) && (strlen($params_export['long_cpt_gen_ve'])>strlen($params_export['long_cpt_gen_ac'])) )
      $long_cpt_generaux=strlen($params_export['long_cpt_gen_ve']);
    if($long_cpt_generaux>99) $long_cpt_generaux=8;
    $long_cpt_generaux = str_pad($long_cpt_generaux, 2, " ", STR_PAD_RIGHT);

    $long_cpt_aux=10;
    $tmp_val=formater_montant($params_export['long_aux'], 0, 0, 0, 0, 0);
    $tmp_val_ve=formater_montant($params_export['long_aux_ve'], 0, 0, 0, 0, 0);
    if($tmp_val > 0) $long_cpt_aux=$tmp_val;
    if(($tmp_val_ve > 0) && ($tmp_val_ve > $tmp_val) ) $long_cpt_aux=$tmp_val_ve;
    if($long_cpt_aux>99) $long_cpt_aux=8;
    $long_cpt_aux = str_pad($long_cpt_aux, 2, " ", STR_PAD_RIGHT);
    $sa_arr=array();
    for($isa=1;$isa<6;$isa++) {
      if($params_export['sa'.$isa]>0) $sa_arr[$isa]=substr(str_pad($params_export['sa'.$isa], 2, " ", STR_PAD_RIGHT),0,2)."0";
      else $sa_arr[$isa]=$long_cpt_generaux."0";
    }

    $csv_content .= "***PS2".$long_cpt_generaux."0".$long_cpt_aux."0".$sa_arr[1].$sa_arr[2].$sa_arr[3].$sa_arr[4].$sa_arr[5]."\r\n";

    $csv_content .= "***PS3"."\r\n";

    if(!Is_empty_str($params_export['etablissement'])) $etablissement=substr(str_pad($params_export['etablissement'], 3, "0", STR_PAD_LEFT),0,3);
    else $etablissement="";
		$csv_content .= "***PS5EUR2X"."   "."                 "."                 "."          "."   "."   "."   "."   ".$etablissement."\r\n";
		$csv_content .= "***EXO004$exercice"."OUVOUVExercice                           OAN01011900"."\r\n";

    verbose_str_to_file(__FILE__, __FUNCTION__, "etablissement:$etablissement et sa_arr:".print_r($sa_arr,1).print_r($params_export,1).$csv_content);
	}


	if(count($all_csv_elem)>0){
		$export_found=1;
    $all_csv_elem[0]['etablissement']=$etablissement;

    if( ($export_mode==36)||($export_mode==1)||($export_mode==41)) {
      if( ($base=='FA1574')||($base=='FA0002')||($base=='FA0992') ) $export_mode=1;
      $csv_content_arr = print_CSV_entry($all_csv_elem, $export_mode);
      verbose_str_to_file(__FILE__, __FUNCTION__, "Mode xls: generation csv $csv_content \n et xls_Arr".print_r($csv_content_arr,1));
    } else {
      $csv_content .= init_CSV_file($export_mode);
      $csv_content .= print_CSV_entry($all_csv_elem, $export_mode);
    }

    $file_name = build_export_file_name($export_mode, $cab_dossier, $zip_dir_name);
    $file_path = "$zip_dir/$file_name";

    verbose_str_to_file(__FILE__, __FUNCTION__, "csv_content in $file_path: ".print_r($csv_content,1));

    if( ($export_mode==36)||($export_mode==1)||($export_mode==41)) {
      $file_path_importachat = "$zip_dir/"."ImportAchat_".$cab_dossier."_".date('Ymd-His');
      $file_path_facnote = "$zip_dir/".$cab_dossier."_".date('Ymd-His').".csv";
      $csv_content .= init_CSV_file(1);
      $csv_content .= print_CSV_entry($all_csv_elem, 0);
      //file_put_contents($file_path_facnote, $csv_content);

      $file_path_facnote = "$zip_dir/".$cab_dossier."_".date('Ymd-His').".facnote";
      //file_put_contents($file_path_facnote, $csv_content);

      if( ($base=='FA1574')||($base=='FA0002')||($base=='FA0992') ) $export_mode=1;

      array_to_excel($csv_content_arr, $file_path, $export_mode);

    } else file_put_contents($file_path, $csv_content);


    if(($export_mode==32)&&($agirismanuel==1)) launch_system_command("cp $file_path ".dirname($file_path)."/$zip_dir_name.ECR",0,1);//ECR
    verbose_str_to_file(__FILE__, __FUNCTION__, "yooz conversions en pdf $export_mode\n");


		if(($export_mode==24)||($export_mode==25)||($export_mode==26)||($export_mode==27)||($export_mode==43)||($export_mode==34)||($export_mode==6))
      $url_to_ret = "../../upload/$zip_dir_name/$file_name";
		//else if(($export_mode==32)) $url_to_ret = "../../upload/$zip_dir_name/$file_name";
		else {
      if($export_mode==41){
        verbose_str_to_file(__FILE__, __FUNCTION__, "yooz conversions en pdf\n");
        $dir_path=$zip_dir;
        list($status, $message, $file_list) = get_dir_content($dir_path, 1);

        foreach($file_list as $file){
          $mime_infos = mime_content_type("$dir_path/$file");
          if(preg_match('?image?i', $mime_infos)) {
            $fichier_split = pathinfo("$dir_path/$file");


            $convert_cmd = "timeout -k 31s 30s convert";

            $convert_cmd = "$convert_cmd $dir_path/$file $dir_path/".$fichier_split['filename'].".PDF";
            verbose_str_to_file(__FILE__, __FUNCTION__, "yooz conversions en pdf $convert_cmd\n");

            launch_system_command($convert_cmd,0,1);
          }
        }
        $cmd="cd $zip_dir; zip -r $zip_dir.ZIP *";
        verbose_str_to_file(__FILE__, __FUNCTION__, "export_mode=$export_mode cmd=\n$cmd\n");
        launch_system_command($cmd,0,1);

      } else if($export_mode==44){
        $cmd="mv $zip_dir $zip_dir.tmp; cd $zip_dir.tmp; zip -r $zip_dir.ZIP *";
        verbose_str_to_file(__FILE__, __FUNCTION__, "export_mode=$export_mode cmd=\n$cmd\n");
        launch_system_command($cmd,0,1);
      } else {
        $cmd="cd $zip_dir/../; zip -r $zip_dir.ZIP $zip_dir_name";
        verbose_str_to_file(__FILE__, __FUNCTION__, "export_mode=$export_mode cmd=\n$cmd\n");
        launch_system_command($cmd,0,1);
      }

      sleep(1);

      if($export_mode==44) list($output, $status) = launch_system_command("unzip -l $zip_dir.ZIP",0,1);
      else list($output, $status) = launch_system_command("unzip -l $zip_dir.ZIP",0,1);
      if($status != 0) {
        $export_found=-1;
        $url_to_ret="";
      } else {
        $url_to_ret = "../../upload/$zip_dir_name.ZIP";
        if($export_mode==44) {
          launch_system_command("mv $zip_dir.ZIP $zip_dir",0,1);
          $url_to_ret = "../../upload/$zip_dir_name";
        }
      }
		}
	}

  verbose_str_to_file(__FILE__, __FUNCTION__, "csv_content in $file_path: $csv_content\n$html_csv_content\n export_found=$export_found, url_to_ret=$url_to_ret\n");

	return array($export_found, $url_to_ret);
}

function csvlines_to_array($csv_content) {

  $tableau=array();
  $tmp_arr = explode("\n", $csv_content);
  $lg=$col=0;
  for($lg=0;$lg<count($tmp_arr);$lg++) {
    $explode_arr = explode(';', $tmp_arr[$lg]);
    for($col=0;$col<count($explode_arr);$col++) {
      $tableau[$lg][$col]=$explode_arr[$col];
    }
  }
  verbose_str_to_file(__FILE__, __FUNCTION__, "$csv_content ".print_r($tableau,1));
  return($tableau);
}

function csv_elem_to_importachat($all_csv_elem, $file_path_importachat, $base) {
  $csv_res = "type;n_piece;date_depense;mode_reglement;fournisseur;date_echeance;compte;libelle;montant_ht;taux_tva;montant_tva;montant_ttc\n";
  $id_a_traiter=array();
  foreach($all_csv_elem as $csv_elem) {
    if($csv_elem['type_element']=='charge'){
      $id_a_traiter[$csv_elem['id_type']]=1;
    }
  }

  //$verbosecontroller = new VerboseController('societe', $base);
  foreach($id_a_traiter as $id=>$val){
    //$chg_infos = $verbosecontroller->get($id, 'charge');
    //$tmp_csv_elem = csv_elem_from_base($base, 'charge', $id);
    if($chg_infos['prix_ttc']<0) $type='AVOIR';
    else $type='FACTURE';

    $prix_ht=formater_montant($chg_infos['prix_ht'], 0, 0, 0, 0, 2);
    $prix_ttc = formater_montant($chg_infos['prix_ttc'], 0, 0, 0, 0, 2);
    $prix_tva=formater_montant(($prix_ttc-$prix_ht), 0, 0, 0, 1, 2);

    // remplacer . par ,
    $prix_ht=formater_montant($prix_ht, 0, 0, 0, 1, 2);
    $prix_ttc = formater_montant($prix_ttc, 0, 0, 0, 1, 2);

    $taux_tva= formater_montant($chg_infos['taux_tva1'], 0, 0, 0, 0, 2);

    $csv_res .= $type.";".$tmp_csv_elem[0]['num_fact'].";".$tmp_csv_elem[0]['date'].";virement;".$tmp_csv_elem[0]['description'].";".$tmp_csv_elem[0]['date_echeance'].
             ";".$tmp_csv_elem[1]['num_cpt'].";".$tmp_csv_elem[0]['description'].
             ";".$prix_ht.";".$taux_tva.";".$prix_tva.";".$prix_ttc."\n";
  }
  //mysqli_close($verbosecontroller);
  file_put_contents($file_path_importachat, $csv_res);
}

function get_elems_from_get($month,$year, $export_mode, $export_list, $onglet,$date_deb,$date_fin, $keyword, $nocharges, $base) {

  if(Is_empty_str($base))$soccontroller = new VerboseController();
	else $soccontroller = new VerboseController('societe', $base);

  //$soccontroller = new SocieteController();
  verbose_str_to_file(__FILE__, __FUNCTION__, "get args  in $month,$year, $export_mode, $export_list, $onglet $nocharges");

	if(($month == -1)&&($year == -1)&&(count($export_list)>0)) {
		if($export_list =="all"){
			if($nocharges==1) $tmpArr = $soccontroller->selectLines(-1 ,$date_deb,$date_fin, $keyword,1);
			else $tmpArr = $soccontroller->selectLines(1 ,$date_deb,$date_fin, $keyword,1);
			foreach($tmpArr as $banq_info) {
				if( ! ($banq_info['exported']>0)) $banqueArr[] = $banq_info;
			}
			$tmpArr=array();
		} else {
			$list_id_banq = url_to_list($export_list);
			foreach($list_id_banq as $id_banq) {
				$banq_info = $soccontroller->get($id_banq,'banque');
				if( ! ($banq_info['exported']>0)) $banqueArr[] = $banq_info;
			}
		}
	} else {
		if($month == -1) {
			$begin = 1;
			if($year == date("Y")) $last = date("n")+1;
			else $last = 13;
			$banq_begin="1/$begin/$year";
			$banq_last="31/12/$year";
		} else {
			$begin = $month;
			$last = $month+1;
			$nb_jours_in_month = cal_days_in_month(CAL_GREGORIAN, $month,$year);
			$banq_begin="1/$begin/$year";
			$banq_last=$nb_jours_in_month.'/'.$month.'/'.$year;
		}
		if($export_mode == 4) $banqueArr = $soccontroller->selectLines(1 ,$banq_begin,$banq_last);
		else $banqueArr = $soccontroller->selectLines(1 ,$banq_begin,$banq_last,null,1);
	}
  mysqli_close($soccontroller);
	return $banqueArr;
}

function txt_by_length($pos_vals) {

	$str_res="";
	for($idx=0;$idx<count($pos_vals);$idx++){

		$longeur=($pos_vals[$idx]['posE']-$pos_vals[$idx]['posS'])+1;
		$before=$idx-1;
		if($before > -1) {
			$diff_next = $pos_vals[$idx]['posS']-$pos_vals[$before]['posE'];
			if($diff_next > 1) $str_res .= str_pad("", $diff_next-1, $pos_vals[$idx]['carPad']);
		}
		$str_res .= str_pad(substr($pos_vals[$idx]['val'],0,$longeur), $longeur, $pos_vals[$idx]['carPad'], $pos_vals[$idx]['sensPad']);
	}
	$splitted = str_split($str_res);
	$longeur=3;
	$idx_line=$val_line="<table><tr>";
	for($idx=0;$idx<count($splitted);$idx++){
		$idx_line .= "<td width=\"20px\">".($idx+1)."</td>";
		$val_line .= "<td width=\"20px\">".$splitted[$idx]."</td>";
	}
	$idx_line .="</tr></table>";
	$val_line .="</tr></table>";
	//verbose_str_to_file(__FILE__, __FUNCTION__, "$idx_line\n$val_line", "let_html");
	return $str_res;
}




function calcul_chg_bank($month,$year){

	list($CHARGES_SOCIETE_TO_CPT, $CHARGES_SOCIETE,$cpt_to_tvacpt) = get_plancomptable();



	//verbose_str_to_file(__FILE__, __FUNCTION__, "Get banque for $month/$year: ".print_r($banqueArr,1));
	$total_debit = 0;
	$total_credit = 0;
	$total_tva_ded = 0;
	$total_tva_enc = 0;
	$total_tva_enc_caisse = 0;
	$tvalist_ded = array();
	$tvalist_enc = array();

	//foreach($banqueArr as $banque){
  $cpt_lib = "";

  //$associated_charges = get_liste_rapprochement($module, $banque['id'], STATUS_CLOS);
  $associated_charges = get_all_compta($month, $year);

  verbose_str_to_file(__FILE__, __FUNCTION__, "Get get_liste_rapprochement for banqueid: ".$banque['id'].print_r($associated_charges,1));
  foreach ($associated_charges as $frais) {
    if( ! ($frais['cpt_assoc'] > 0)) $frais['cpt_assoc'] = "47100000";
    if( ! ($cpt_to_tvacpt[$frais['cpt_assoc']] > 0)) {
      if($banque['debit']>0.01) $cpt_tva = "44566000";
      else $cpt_tva = "44571300";
    } else $cpt_tva = $cpt_to_tvacpt[$frais['cpt_assoc']];

    if($frais['type'] == 'charge') {
      $tvalist_ded[$cpt_tva] += $frais['prix_ttc'] - $frais['prix_ht'];
      $total_tva_ded += $frais['prix_ttc'] - $frais['prix_ht'];
    }
    else if(($frais['type'] == 'frais')||($frais['type'] == 'frais_dep')) {
      $tvalist_ded[$cpt_tva] += $frais['prix_ttc'] - $frais['prix_ht'];
      $total_tva_ded += $frais['prix_ttc'] - $frais['prix_ht'];
    }
    else if($frais['type'] == 'facture') {
      list($total_ht_f, $total_ttc_f, $total_tva_f,$total_ht_frais, $bases_tva,
           $total_vers_ht, $total_vers_f, $total_vers_tva, $bases_tva_V,
           $total_ht_f_solde, $total_ttc_f_solde, $total_tva_f_solde, $bases_tva_solde,
           $total_paiements) = get_totaux_factures($frais['id'], $frais['type']);

      foreach($bases_tva as $taux_tva => $baseHT) {
        $tvalist_enc[$taux_tva][0] +=  $baseHT[0];
        $tvalist_enc[$taux_tva][1] +=  $baseHT[1];
        $total_tva_enc += $baseHT[1];
      }
      verbose_str_to_file(__FILE__, __FUNCTION__, "pour la facture".$frais['id'].print_r($tvalist_enc,1));
      foreach($bases_tva_V as $taux_tva => $baseHT) {
        $tvalist_enc[$taux_tva][0] -=  $baseHT[0];
        $tvalist_enc[$taux_tva][1] -=  $baseHT[1];
        $total_tva_enc -= $baseHT[1];
        verbose_str_to_file(__FILE__, __FUNCTION__, "apres encaiss pour la facture".$frais['id'].print_r($tvalist_enc,1));
      }
    }
    else if($frais['type'] == 'versement') {
      list($total_ht_f, $total_ttc_f, $total_tva_f,$total_ht_frais, $bases_tva,
           $total_vers_ht, $total_vers_f, $total_vers_tva, $bases_tva_V,
           $total_ht_f_solde, $total_ttc_f_solde, $total_tva_f_solde, $bases_tva_solde,
           $total_paiements) = get_totaux_factures($frais['id'], $frais['type']);
      foreach($bases_tva as $taux_tva => $baseHT) {
        $tvalist_enc[$taux_tva][0] +=  $baseHT[0];
        $tvalist_enc[$taux_tva][1] +=  $baseHT[1];
        $total_tva_enc += $baseHT[1];
      }
    }
    else if($frais['type'] == 'encaissement') {
      verbose_str_to_file(__FILE__, __FUNCTION__, "enc tva".print_r($frais,1));
      $tvalist_enc[formater_montant($frais['taux_tva'])][0] += $frais['prix_ht'];
      $tvalist_enc[formater_montant($frais['taux_tva'])][1] += $frais['prix_ttc'] - $frais['prix_ht'];
      $total_tva_enc += $frais['prix_ttc'] - $frais['prix_ht'];
    }
    else $cpt_lib = $frais['cpt_assoc'];
  }
	//}

	$total_tva_enc=0;
	foreach($tvalist_enc as $taux_tva=>$baseHT) {
		$tvalist_enc[$taux_tva][1] =  intval($baseHT[1]);
		$total_tva_enc += intval($baseHT[1]);
	}
	$total_tva_enc += $total_tva_enc_caisse;

	$total_tva_ded=0;
	foreach($tvalist_ded as $cpt=>$val) {
		$tvalist_ded[$cpt] =  intval($val);
		$total_tva_ded += intval($val);
	}

	verbose_str_to_file(__FILE__, __FUNCTION__, "total_tva_enc $total_tva_enc tvalist_enc".print_r($tvalist_enc,1));
	verbose_str_to_file(__FILE__, __FUNCTION__, "total_tva_ded $total_tva_ded tvalist_ded".print_r($tvalist_ded,1));

	return array($total_tva_enc,$total_tva_ded, $tvalist_enc, $tvalist_ded);
}


function build_numcpt_ht_encaiss($chg_infos){
	list($taux_tva1, $base_ht1, $prix_tva1, $taux_tva2, $base_ht2, $prix_tva2, $taux_tva3, $base_ht3, $prix_tva3, $taux_tva4, $base_ht4, $prix_tva4) = tva_from_post($chg_infos);
	$cpt_assoc = $chg_infos['cpt_assoc'];
	$description = $chg_infos['description'];
	$list_taux=array(20=>706500,10=>706700,5.5=>706800,2.1=>706900,0=>706300);
	if(preg_match('/VENTE\s+PRESTATION\s+DE\s+SERVICE/', $description)) {
		foreach($list_taux as $taux=>$cpt){
			if((formater_montant($taux_tva1) == $taux)||(formater_montant($taux_tva2) == $taux)||(formater_montant($taux_tva3) == $taux)||(formater_montant($taux_tva4) == $taux)){
				$cpt_assoc=$cpt;
				break;
			}
		}
		//if((formater_montant($taux_tva1) == 20)||(formater_montant($taux_tva2) == 20)||(formater_montant($taux_tva3) == 20)||(formater_montant($taux_tva4) == 20)){
		//	$cpt_assoc=706500;
		//} else if((formater_montant($taux_tva1) == 10)||(formater_montant($taux_tva2) == 10)||(formater_montant($taux_tva3) == 10)||(formater_montant($taux_tva4) == 10)){
		//	$cpt_assoc=706700;
		//} else if((formater_montant($taux_tva1) == 0)&&(formater_montant($taux_tva2) == 0)&&(formater_montant($taux_tva3) == 0)){
		//	$cpt_assoc=706300;
		//} else if((formater_montant($taux_tva1) == 5.5)&&(formater_montant($taux_tva2) == 5.5)&&(formater_montant($taux_tva3) == 5.5)){
		//	$cpt_assoc=706800;
		//} else if((formater_montant($taux_tva1) == 2.1)&&(formater_montant($taux_tva2) == 2.1)&&(formater_montant($taux_tva3) == 2.1)){
		//	$cpt_assoc=706900;
		//}
	}

	$list_taux=array(20=>707500,10=>707700,5.5=>707800,2.1=>707900,0=>707300);
	if(preg_match('/VENTE\s+MARCHANDISES/', $description)) {
		foreach($list_taux as $taux=>$cpt){
			if((formater_montant($taux_tva1) == $taux)||(formater_montant($taux_tva2) == $taux)||(formater_montant($taux_tva3) == $taux)||(formater_montant($taux_tva4) == $taux)){
				$cpt_assoc=$cpt;
				break;
			}
		}
	}
	//if(preg_match('/VENTE\s+MARCHANDISES/', $description)) {
	//	if((formater_montant($taux_tva1) == 20)||(formater_montant($taux_tva2) == 20)||(formater_montant($taux_tva3) == 20)){
	//		$cpt_assoc=707500;
	//	} else if((formater_montant($taux_tva1) == 10)||(formater_montant($taux_tva2) == 10)||(formater_montant($taux_tva3) == 10)){
	//		$cpt_assoc=707700;
	//	} else if((formater_montant($taux_tva1) == 0)&&(formater_montant($taux_tva2) == 0)&&(formater_montant($taux_tva3) == 0)){
	//		$cpt_assoc=707300;
	//	} else if((formater_montant($taux_tva1) == 5.5)&&(formater_montant($taux_tva2) == 5.5)&&(formater_montant($taux_tva3) == 5.5)){
	//		$cpt_assoc=707800;
	//	} else if((formater_montant($taux_tva1) == 2.1)&&(formater_montant($taux_tva2) == 2.1)&&(formater_montant($taux_tva3) == 2.1)){
	//		$cpt_assoc=707900;
	//	}
	//}

	return $cpt_assoc;

}

function get_numcpt_ht($activites, $cpt_du_planf, $CHARGES_SOCIETE_TO_CPT, $cltcontroller, $base){

	verbose_str_to_file(__FILE__, __FUNCTION__, "cpt_du_planf $cpt_du_planf et CHARGES_SOCIETE_TO_CPT recu".print_r($CHARGES_SOCIETE_TO_CPT[$activites['description']], 1));
	verbose_str_to_file(__FILE__, __FUNCTION__, "activites recu ".$activites['type']." ".$activites['id']);

	global $dbConfig;
	$dbName = $dbConfig["db"];
  $export_mode_xml = get_export_mode('', $dbName,1); //XML
  if(is_FacNote_base($base)) $cltcontroller = new VerboseController('societe', $base);
	else $cltcontroller = new VerboseController();

	$param_clt = $cltcontroller->get(1, 'parametresClt');
	$soc_cpt = $cltcontroller->get(1, 'societe');
  mysqli_close($cltcontroller);
  if($activites['type']=='encaissement') $table = 'planclients';
  else $table = 'planfournisseur';
  $all_plan_frs = get_planfrs_clt($table,$base);
  $planfrs_description = $all_plan_frs[$activites['description']];
  $planfrs = $all_plan_frs[$activites['detail']];
  $planfrs_divers = $all_plan_frs[substr($activites['detail'],0,1).'. DIVERS'];
  $planfrs_diversDesc = $all_plan_frs[substr($activites['description'],0,1).'. DIVERS'];

  $chg_desc = $activites['description'];
  $chg_desc_avec_bk = $activites['description']." B".$activites['rapprochement'];

  verbose_str_to_file(__FILE__, __FUNCTION__, "CHARGES_SOCIETE_TO_CPT pour $chg_desc recu".print_r($CHARGES_SOCIETE_TO_CPT[$chg_desc],1)." ==>".$CHARGES_SOCIETE_TO_CPT[$chg_desc][0]);
  verbose_str_to_file(__FILE__, __FUNCTION__, "CHARGES_SOCIETE_TO_CPT pour $chg_desc_avec_bk recu".print_r($CHARGES_SOCIETE_TO_CPT[$chg_desc_avec_bk],1)." ==>".$CHARGES_SOCIETE_TO_CPT[$chg_desc_avec_bk][0]."montant".formater_montant($CHARGES_SOCIETE_TO_CPT[$chg_desc_avec_bk][0])."export ".$export_mode_xml );


	$lib_creditht ="";
  if(! Is_empty_str($planfrs['cpt_assoc'])) {
    $lib_creditht = $planfrs['cpt_assoc'];
    verbose_str_to_file(__FILE__, __FUNCTION__, "numcpt_ht $lib_creditht pris du plan frs");
  } else if( ( ! Is_empty_str($CHARGES_SOCIETE_TO_CPT[$chg_desc_avec_bk][0]) ) && ($CHARGES_SOCIETE_TO_CPT[$chg_desc_avec_bk][0] != "0")) {
    $lib_creditht = $CHARGES_SOCIETE_TO_CPT[$chg_desc_avec_bk][0];
    verbose_str_to_file(__FILE__, __FUNCTION__, "numcpt_ht $lib_creditht pris du plan comptable car la desc ".$chg_desc_avec_bk." existe ");
	} else if(( ! Is_empty_str($CHARGES_SOCIETE_TO_CPT[$chg_desc][0]))&& ($CHARGES_SOCIETE_TO_CPT[$chg_desc][0] != "0")) {
    $lib_creditht = $CHARGES_SOCIETE_TO_CPT[$chg_desc][0];
    verbose_str_to_file(__FILE__, __FUNCTION__, "numcpt_ht $lib_creditht pris du plan comptable car la desc ".$chg_desc." existe ");
  } else if(($activites['type']=="facture")||($activites['type']=="versement")) {
    list($total_ht_f, $total_ttc_f, $total_tva_f,$total_ht_frais, $bases_tva,
         $total_vers_ht, $total_vers_f, $total_vers_tva, $bases_tva_V,
         $total_ht_f_solde, $total_ttc_f_solde, $total_tva_f_solde, $bases_tva_solde,
         $total_paiements) = get_totaux_factures($activites['id'], $activites['type'], 1);

    $total_ht_f=$total_ht_f_solde;
    $total_ttc_f=$total_ttc_f_solde;
    $total_tva_f=$total_tva_f_solde;
    $bases_tva=$bases_tva_solde;

		foreach($bases_tva as $cpt_ht => $baseHT) {
			$lib_creditht = $cpt_ht;
		}
    verbose_str_to_file(__FILE__, __FUNCTION__, "numcpt_ht $lib_creditht pris de totaux facture et base tva");
	} else {
		$lib_creditht = $activites['cpt_assoc'];
		if($activites['type']=="paie") $lib_creditht = 42100000;
		if(Is_empty_str($lib_creditht)) $lib_creditht=$CHARGES_SOCIETE_TO_CPT[$chg_desc][0];
    if($activites['type']=="encaissement"){
      if($export_mode_xml<0) $lib_creditht = build_numcpt_ht_encaiss($activites);
    }

    verbose_str_to_file(__FILE__, __FUNCTION__, "numcpt_ht $lib_creditht pris de activite");
	}

	if(Is_empty_str($lib_creditht) || ($lib_creditht=="0")) {
		$lib_creditht=47100000;
    verbose_str_to_file(__FILE__, __FUNCTION__, "lib_creditht $lib_creditht vide donc 47100000");
	}
	if(preg_match('/^\s*SSDIVISION\s/',$chg_desc)) {
    $lib_creditht=$activites['cpt_assoc'];
    verbose_str_to_file(__FILE__, __FUNCTION__, "lib_creditht $lib_creditht pris de l'activite car SSDIVISION");
  }

	verbose_str_to_file(__FILE__, __FUNCTION__, "return lib_creditht $lib_creditht");

	return $lib_creditht;
}



function get_libcredit($activites, $CHARGES_SOCIETE_TO_LIB, $cltcontroller, $export_mode, $params_export, $type, $ndf_as_chg, $base, $comptable){

	verbose_str_to_file(__FILE__, __FUNCTION__, "cpt_du_planf $cpt_du_planf et CHARGES_SOCIETE_TO_LIB pour la description recu".print_r($CHARGES_SOCIETE_TO_LIB[$activites['description']], 1)." CHARGES_SOCIETE_TO_LIB pour le detail ".print_r($CHARGES_SOCIETE_TO_LIB[$activites['detail']], 1)."activite ".print_r($activites, 1));
	verbose_str_to_file(__FILE__, __FUNCTION__, "activites recu ".$activites['type']." ".$activites['id']);


	global $dbConfig;
	$dbName = $dbConfig["db"];
	$get_from_caisse=$get_from_plan=0;
	if($_SESSION[$dbName]['connectedUser']['soctype'] == 'location')$get_from_caisse=1;
	if(is_FacNote_base($base)) $cltcontroller = new VerboseController('societe', $base);
	else $cltcontroller = new VerboseController();
  $longeur=$params_export['long_cpt'];
  if( ! ($longeur>0)) $longeur=6;

	if(($activites['type']=='facture')||($activites['type']=='avoir')||($activites['type']=='versement')) {
    $client = $cltcontroller->get($activites['id_client'], 'client');
		verbose_str_to_file(__FILE__, __FUNCTION__, "get client".print_r($client, 1));
		$conditions=array();
		$conditions['description']= strtoupper($client['nom']);
		$planclients=$cltcontroller->searchConditionFromTable('planclients', $conditions);


    if( ($base =='FA2487')||($base =='FA2691') ) {
      $lib_cpt = '9'.substr(strtoupper(clean_file_name($client['nom'],0,0)),0,1).'0000';
      if($base =='FA2487') $lib_cpt = "900000";
    } else if( ! Is_empty_str($planclients[0]['libcompte'])) {
			$lib_cpt = $planclients[0]['libcompte'];
			$get_from_plan=1;
		}
    else if(strlen($params_export['prefix_ve']) > 4) {
      $lib_cpt = $params_export['prefix_ve'].substr(strtoupper(clean_file_name($client['nom'],0,0)),0,1);
      verbose_str_to_file(__FILE__, __FUNCTION__, "soga 411cl ".$lib_cpt);
    }

		else if(strlen($client['libcpt']) >2) $lib_cpt = $client['libcpt'];
		else if( ! Is_empty_str($params_export['prefix_ve'])) $lib_cpt = $params_export['prefix_ve'].substr( strtoupper(clean_file_name($client['nom'],0,0)), 0,$longeur );
		else if(($export_mode==1)||($export_mode==4)||($export_mode<0)) $lib_cpt = strtoupper("C".clean_file_name($client['nom'],0,0));
		else if(($export_mode==2)||($export_mode==13)||($export_mode==17)) $lib_cpt = strtoupper("01".clean_file_name($client['nom'],0,0));
		else if($export_mode==14) $lib_cpt = strtoupper("9".clean_file_name($client['nom'],0,0));
		else if($export_mode==22) $lib_cpt = strtoupper("0".clean_file_name($client['nom'],0,0));
		else if($export_mode==31) $lib_cpt = strtoupper("F".clean_file_name($client['nom'],0,0));
		else if($export_mode==8) $lib_cpt = strtoupper("401".clean_file_name($client['nom'],0,0));
		else $lib_cpt = strtoupper("C".clean_file_name($client['nom'],0,0));


		$lib_credit = $lib_cpt;
		if( ! Is_empty_str($planclients[0]['cpt_assoc'])) $cpt_du_planf=$planclients[0]['cpt_assoc'];

	} else {

		$lib_credit=$lib_credit_php="";
		$cpt_du_planf=0;

    if( ($activites['type']=='encaissement') && (($base=='FA0481')||($base=='FA0003')) ){
      $pieces = get_attached_files($activites, null, 1, $base);
      $php_file = dirname(__FILE__)."/../../$base/upload/".$activites['type']."/".$pieces[0]['name_disque'].".php";

      verbose_str_to_file(__FILE__, __FUNCTION__, "verif lib_credit_php dans php file $php_file");

      if(is_file($php_file)&& (filesize($php_file)>0) ){
        include($php_file);
        $ocr_log_inf_dest = $CHG_DEC_INF;

        $lib_credit_php=str_replace('.00', '', $ocr_log_inf_dest['code_client']);
        verbose_str_to_file(__FILE__, __FUNCTION__, "lib_credit_php=$lib_credit_php pris du php dec ".print_r($ocr_log_inf_dest,1));
      }
    }
    if( ($activites['type']=='encaissement') && (($base=='FA5889')||($base=='FA0003')) ){
      if($activites['prix_ttc']<0) $lib_credit_php='419800';
    }
    if($activites['type']=='encaissement') $table = 'planclients';
    else $table = 'planfournisseur';
    $all_plan_frs = get_planfrs_clt($table,$base);
    $planfrs_description = $all_plan_frs[$activites['description']];
    $planfrs = $all_plan_frs[$activites['detail']];
    $planfrs_divers = $all_plan_frs[substr($activites['detail'],0,1).'. DIVERS'];
    $planfrs_diversDesc = $all_plan_frs[substr($activites['description'],0,1).'. DIVERS'];

    if($activites['type']=='encaissement') $field='divers_ve';
    else $field='divers_ac';
    $planfrs_autres=array();
    if(! Is_empty_str($params_export[$field])) $planfrs_autres['libcompte'] = $params_export[$field];
    if(! Is_empty_str($params_export[$field.'_cpt'])) $planfrs_autres['cpt_assoc'] = $params_export[$field.'_cpt'];

    verbose_str_to_file(__FILE__, __FUNCTION__, "get plan planfrs_description ".print_r($planfrs_description, 1)."get plan planfrs ".print_r($planfrs, 1).
                        "get plan planfrs_divers ".print_r($planfrs_divers, 1)."get plan planfrs_diversDesc ".print_r($planfrs_diversDesc, 1));

    if( ! Is_empty_str($activites['detail']) ) $tmp_str = strtoupper(clean_file_name($activites['detail'],0,0));
    else $tmp_str = strtoupper(clean_file_name($activites['description'],0,0));
    verbose_str_to_file(__FILE__, __FUNCTION__, "get tmp_str frs $tmp_str");
    $lib_credit = "F".$tmp_str;
    if($activites['type']=='encaissement')$key_p='prefix_ve';
    else $key_p='prefix_ac';
    $key_rap=$tmp_str." B".$activites['rapprochement'];

    if( ! Is_empty_str($params_export[$key_p])) $lib_credit = $params_export[$key_p].$tmp_str;
    else {

      if(($export_mode==2)||($export_mode==17)) $lib_credit = "08".$tmp_str;
      else if($export_mode==14) {
        if($activites['type']=='encaissement') $lib_credit = "9".$tmp_str;
        else $lib_credit = "0".$tmp_str;
      }
      else if($export_mode==31) {
        if($activites['type']=='encaissement') $lib_credit = "F".$tmp_str;
        else $lib_credit = "A".$tmp_str;
      }
      else if($export_mode==8) {
        if($activites['type']=='encaissement') $lib_credit = "411".$tmp_str;
        else $lib_credit = "401".$tmp_str;
      }
      else if($export_mode==13) $lib_credit = "9".$tmp_str;
      else if($export_mode==16) $lib_credit = "580".$tmp_str;
      else if($export_mode==22) $lib_credit = "9".$tmp_str;
      else if($dbName=='FA0211') $lib_credit = "401".$tmp_str;
      else $lib_credit = "F".$tmp_str;
    }
    verbose_str_to_file(__FILE__, __FUNCTION__, "lib_credit calcule pour export_mode=$export_mode: $lib_credit ");


    if($export_mode<0) {
      1;
      verbose_str_to_file(__FILE__, __FUNCTION__, "Mode xml lib_credit retourne pour export_mode=$export_mode: $lib_credit ");
    } else if( ( ($base =='FA2487')&&($activites['type']=='encaissement') )||(($base =='FA2691')&&($activites['type']=='encaissement')) )  {
      $lib_credit = '9'.substr(strtoupper(clean_file_name($activites['detail'],0,0)),0,1).'0000';
      if($base =='FA2487') $lib_credit = "900000";
    } else if( ! Is_empty_str($CHARGES_SOCIETE_TO_LIB[$key_rap]) )  {
      $lib_credit=$CHARGES_SOCIETE_TO_LIB[$key_rap];
      verbose_str_to_file(__FILE__, __FUNCTION__, "lib_credit pris de conf rapp: $lib_credit ");
    } else if(! Is_empty_str($planfrs['libcompte'])){
      $lib_credit = $planfrs['libcompte'];

      $taux_tva = formater_montant($activites['taux_tva1'],0,0,0,0,0);
      $tva_list = array('20','10','085','055', '021', '00');
      if($taux_tva==8.5) $key='085';
      else if($taux_tva==5.5) $key='055';
      else if($taux_tva==2.1) $key='021';
      else $key=$taux_tva;
      if(!Is_empty_str($planfrs['ac_aux_t'.$key])) $lib_credit = $planfrs['ac_aux_t'.$key];
      verbose_str_to_file(__FILE__, __FUNCTION__, "lib_credit pris de planfrs: $lib_credit ".print_r($activites,1)." et 'ac_aux_t'.$key = ". $planfrs['ac_aux_t'.$key]);
    } else if(! Is_empty_str($CHARGES_SOCIETE_TO_LIB[$activites['description']])) {
      $lib_credit = $CHARGES_SOCIETE_TO_LIB[$activites['description']];
      verbose_str_to_file(__FILE__, __FUNCTION__, "lib_credit pris de plan general: $lib_credit ");
    } else if( ! Is_empty_str($planfrs_description['libcompte']) ) {
      $lib_credit = $planfrs_description['libcompte'];
      $get_from_plan=1;
      verbose_str_to_file(__FILE__, __FUNCTION__, "lib_credit pris de planfrs_description: $lib_credit ");
    } else if(! Is_empty_str($planfrs_divers['libcompte'])) {
      $lib_credit = $planfrs_divers['libcompte'];
      verbose_str_to_file(__FILE__, __FUNCTION__, "lib_credit pris de planfrs_divers: $lib_credit ");
    } else if(! Is_empty_str($planfrs_diversDesc['libcompte'])) {
      $lib_credit = $planfrs_diversDesc['libcompte'];
      verbose_str_to_file(__FILE__, __FUNCTION__, "lib_credit pris de planfrs_diversDesc: $lib_credit ");
    } else if(! preg_match('/^\s*$/', $planfrs_autres['libcompte'])) {
      $lib_credit = $planfrs_autres['libcompte'];
      verbose_str_to_file(__FILE__, __FUNCTION__, "lib_credit pris des paramexport planfrs_autres: $lib_credit ");
    }

    if(! Is_empty_str($planfrs['cpt_assoc'])){
			$cpt_du_planf=$planfrs['cpt_assoc'];
			verbose_str_to_file(__FILE__, __FUNCTION__, " cpt_du_planf $cpt_du_planf a partir de planfrs");
		} else if(! Is_empty_str($planfrs_description['cpt_assoc'])) {
			$cpt_du_planf=$planfrs_description['cpt_assoc'];
			verbose_str_to_file(__FILE__, __FUNCTION__, " cpt_du_planf $cpt_du_planf a partir de planfrs_description");
		} else if(! Is_empty_str($planfrs_divers['cpt_assoc'])) {
			$cpt_du_planf=$planfrs_divers['cpt_assoc'];
			verbose_str_to_file(__FILE__, __FUNCTION__, " cpt_du_planf $cpt_du_planf a partir de planfrs_divers");
		} else if(! Is_empty_str($planfrs_diversDesc['cpt_assoc'])) {
			$cpt_du_planf=$planfrs_diversDesc['cpt_assoc'];
			verbose_str_to_file(__FILE__, __FUNCTION__, " cpt_du_planf $cpt_du_planf a partir de planfrs_diversDesc");
		} else if(! Is_empty_str($planfrs_autres['cpt_assoc'])) {
			//$cpt_du_planf=$planfrs_autres['cpt_assoc'];
			verbose_str_to_file(__FILE__, __FUNCTION__, " cpt_du_planf $cpt_du_planf a partir de planfrs_autres");
		}

		if( ($activites['type']=='encaissement') && ($activites['id_caisse']>0) && ( ! ($get_from_caisse>0) ) ) {
      $lib_credit = $params_export['cpt_caisse'];
      verbose_str_to_file(__FILE__, __FUNCTION__, " caisse de params_export ".$params_export['cpt_caisse']);
      if(Is_empty_str($lib_credit)) $lib_credit = '53000000';
		}
	}
  //if( ($activites['type']=='charge') && preg_match('/ENEDIS/i', $activites['detail']) ) $lib_credit='401ERDF';
  if($type == 'frais') {
		if($dbName=='FA0163') {
			if($activites['id_manager']== 4) $lib_credit = '46710010';
			else if($activites['id_manager']== 3) $lib_credit = '46710011';
			verbose_str_to_file(__FILE__, __FUNCTION__, "cas FA0163 $lib_credit lib_credit pour activites recu".print_r($activites, 1));
		}
    else if(! Is_empty_str($params_export['cpt_ndf'.$activites['id_manager']])) {
      verbose_str_to_file(__FILE__, __FUNCTION__, "trouve cpt_ndf pour id mana".$activites['id_manager']);
      $lib_credit = $params_export['cpt_ndf'.$activites['id_manager']];
    } else if( ! ($ndf_as_chg>0)) {
      if($export_mode==2) $lib_credit = "401NDF";
      else if($export_mode==14) $lib_credit = "45510000";
      else if($export_mode==13) $lib_credit = "45000000";
      else $lib_credit = "NDF";
    }
    else if($export_mode==13) $lib_credit = "46700000";//gatti
    else $lib_credit = "467001";//
  }
  mysqli_close($cltcontroller);
	$longeur=8;
	$car_pad=" ";
	//if($get_from_plan != 1) $lib_credit = strtoupper(substr(str_pad(clean_file_name($lib_credit,0,0), $longeur, $car_pad, STR_PAD_RIGHT),0,$longeur));
	verbose_str_to_file(__FILE__, __FUNCTION__, "return lib_credit $lib_credit cpt_du_planf $cpt_du_planf");
  if( ($activites['type']=='encaissement') && (($base=='FA0481')||($base=='FA0003')||($base=='FA5889')) && (! Is_empty_str($lib_credit_php)) ) $lib_credit=$lib_credit_php;

	return array($lib_credit, $cpt_du_planf);
}

function inverser_tab($tab){
	$new_tab=array();
	foreach($tab as $tag=>$vals) {
		$new_tab[$tag]['credit']=$tab[$tag]['debit'];
		$new_tab[$tag]['debit']=$tab[$tag]['credit'];
	}
	return $new_tab;
}

function moins_tab($tab){
	$new_tab=array();
	foreach($tab as $tag=>$vals) {
		foreach($vals as $key=>$value) {
			if($value<0) $new_tab[$tag][$key]=-$tab[$tag][$key];
		}
	}
	return $new_tab;
}

function afficher_montants($tab){
  $txt="";
  foreach($tab as $k=>$arr){
    $txt .= "$k\t\t=> ";
    foreach($arr as $kB=>$val){
      $txt .= "$val \t";
    }
    $txt .= "\n";
  }
  return $txt;
}
function egaliser_tab($tab, $type, $export_mode, $params_export, $ventiller_ht){

	verbose_str_to_file(__FILE__, __FUNCTION__, "get tab et ventiller_ht=$ventiller_ht ".afficher_montants($tab,1));
	$tags_to_count=array('prix_ttc','prix_ht','frais_de_port','ecotax','prix_ht1','prix_tva1','prix_ht2','prix_tva2','prix_ht3','prix_tva3','prix_ht4','prix_tva4',
                       "livrets_net","remb_tickets","petits_lots","commission","remise","consigne","total_brut","mise_encaissee","biens_serv");



	$all_cred=$all_deb=0;
	$txt="tab: \n";

	foreach($tags_to_count as $tag) {
		$txt .= "$tag=>\t".$tab[$tag]['debit']."\t".$tab[$tag]['credit']."\n";
		$all_cred += $tab[$tag]['credit'];
		$all_deb  += $tab[$tag]['debit'];
	}


	$diff = $all_cred-$all_deb;
	$txt .= "** Total all_deb=$all_deb all_cred=$all_cred diff=$diff\n";
	if($diff>0) $tab['prix_ht']['debit'] = $diff;
	else if($diff<0) $tab['prix_ht']['credit'] = -$diff;
	$txt .= "prix_ht=>\t".$tab['prix_ht']['debit']."\t".$tab['prix_ht']['credit']."\n";

	$diff = $tab['prix_ht']['credit']-$tab['prix_ht']['debit'];
	$txt .= "new Total all_deb=$all_deb all_cred=$all_cred diff=$diff\n";
	if($diff>0) {
    $tab['prix_ht']['credit'] = $diff;
    $tab['prix_ht']['debit'] = 0;
	} else if($diff<0) {
    $tab['prix_ht']['credit'] = 0;
    $tab['prix_ht']['debit'] = -$diff;
	}else if($diff==0){
    $tab['prix_ht']['credit'] = 0;
    $tab['prix_ht']['debit'] = 0;
	}

  $txt .= "prix_ht=>\t".$tab['prix_ht']['debit']."\t".$tab['prix_ht']['credit']."\n";
	verbose_str_to_file(__FILE__, __FUNCTION__, " tab treated".afficher_montants($tab,1));

	if( ($tab['prix_ht']['credit']>0) ){
		$tab['prix_ht1']['credit']+= $tab['prix_ht']['credit'];
		$tab['prix_ht']['credit']=0;
	}
	if( ($tab['prix_ht']['debit']>0) ){
		$tab['prix_ht1']['debit']+= $tab['prix_ht']['debit'];
		$tab['prix_ht']['debit']=0;
	}

	verbose_str_to_file(__FILE__, __FUNCTION__, " tab merged".afficher_montants($tab,1));

	if(($ventiller_ht != 1)&&(($type == 'frais')||($type == 'charge')||($type == 'encaissement'))){
		for($idx=1;$idx<5; $idx++){
			$tab['prix_ht']['credit'] += $tab['prix_ht'.$idx]['credit'];
			$tab['prix_ht']['debit'] += $tab['prix_ht'.$idx]['debit'];
			$tab['prix_ht'.$idx]['credit']=0;
			$tab['prix_ht'.$idx]['debit']=0;
		}
	}

  verbose_str_to_file(__FILE__, __FUNCTION__, " Sans ventiler HT ".afficher_montants($tab,1));

	if(($ventiller_ht == 1) && ($tab['prix_ht']['credit']>0) && ($tab['prix_ht1']['credit']>0) ){
		$tab['prix_ht1']['credit'] += $tab['prix_ht']['credit'];
		$tab['prix_ht1']['debit']+= $tab['prix_ht']['debit'] ;
		$tab['prix_ht']['credit']=0;
		$tab['prix_ht']['debit']=0;
	}

  verbose_str_to_file(__FILE__, __FUNCTION__, " Ventil HT et credit ".afficher_montants($tab,1));

	if($ventiller_ht == 1){
		$tag='prix_ht1';
		if($tab[$tag]['credit']>$tab[$tag]['debit']) {
			$tab[$tag]['credit']=$tab[$tag]['credit']-$tab[$tag]['debit'];
			$tab[$tag]['debit']=0;
		}
		if($tab[$tag]['debit']>$tab[$tag]['credit']) {
			$tab[$tag]['debit']=$tab[$tag]['debit']-$tab[$tag]['credit'];
			$tab[$tag]['credit']=0;
		}
	}

	verbose_str_to_file(__FILE__, __FUNCTION__, "Ventil HT=$ventiller_ht et export_mode != 30".afficher_montants($tab,1));

	if( ($tab['prix_tva1']['credit']==0) && ($tab['prix_ht']['credit']==0) && ($tab['prix_ht1']['credit']>0) ){
		$tab['prix_ht']['credit'] = $tab['prix_ht1']['credit'];
		$tab['prix_ht1']['credit']=0;
	}
	if( ($tab['prix_tva1']['debit']==0) && ($tab['prix_ht']['debit']==0) && ($tab['prix_ht1']['debit']>0) ){
		$tab['prix_ht']['debit'] = $tab['prix_ht1']['debit'];
		$tab['prix_ht1']['debit']=0;
	}


	foreach($tags_to_count as $tag) {
		if($tab[$tag]['credit']>$tab[$tag]['debit']) {
			$tab[$tag]['credit']=$tab[$tag]['credit']-$tab[$tag]['debit'];
			$tab[$tag]['debit']=0;
		}
		if($tab[$tag]['debit']>$tab[$tag]['credit']) {
			$tab[$tag]['debit']=$tab[$tag]['debit']-$tab[$tag]['credit'];
			$tab[$tag]['credit']=0;
		}
	}

	if($tab['prix_ttc']['debit']>0){
		foreach($tags_to_count as $tag) {
			if(($tag != 'prix_ttc')&&($tab[$tag]['debit']>0)) {
				if($tab['prix_ht1']['credit'] > $tab[$tag]['debit']) {
					$tab['prix_ht1']['credit'] -= $tab[$tag]['debit'];
					$tab[$tag]['debit']=0;
				}
			}
		}
	}
	if($tab['prix_ttc']['credit']>0){
		foreach($tags_to_count as $tag) {
			if(($tag != 'prix_ttc')&&($tab[$tag]['credit']>0)) {
				if($tab['prix_ht1']['debit'] > $tab[$tag]['credit']) {
					$tab['prix_ht1']['debit'] -= $tab[$tag]['credit'];
					$tab[$tag]['credit']=0;
				}
			}
		}
	}

	verbose_str_to_file(__FILE__, __FUNCTION__, "return tab".afficher_montants($tab,1));
	verbose_str_to_file(__FILE__, __FUNCTION__, $txt);

	return $tab;
}

function franchise_base($tab, $type) {

	verbose_str_to_file(__FILE__, __FUNCTION__, "get tab".print_r($tab,1));
	$tags_to_count=array('prix_ht','frais_de_port','ecotax','prix_ht1','prix_tva1','prix_ht2','prix_tva2','prix_ht3','prix_tva3');
	//,

	foreach($tags_to_count as $tag) {
		$tab[$tag]['credit']=0;
		$tab[$tag]['debit']=0;
	}
	$tag='prix_ht';
	$tab[$tag]['credit']=$tab['prix_ttc']['debit'];
	$tab[$tag]['debit']=$tab['prix_ttc']['credit'];

	verbose_str_to_file(__FILE__, __FUNCTION__, "return tab".print_r($tab,1));
	verbose_str_to_file(__FILE__, __FUNCTION__, $txt);

	return $tab;
}

function get_ventiller_TTC($activites, $base, $params_export) {

  $ventiler_ttc=0;
  if( ($activites['type']=='encaissement')||($activites['type']=='facture') ) {
    if(preg_match('/march/i',$activites['description'])) $params_export_f = 'aux_march';
    else $params_export_f = 'aux_serv';

    foreach($params_export as $key=>$val) {
      if(preg_match('/^\s*'.$params_export_f.'/', $key) && ( ! Is_empty_str($params_export[$key])) ) {
        $ventiler_ttc=1;
        verbose_str_to_file(__FILE__, __FUNCTION__, "trouve ventiler_ttc pour $key");
      }
    }

    $tva_trouvee=0;
    for($idt=1; $idt<6; $idt++){
      if(formater_montant($activites['taux_tva'.$idt])>0) $tva_trouvee=1;
    }
    if($tva_trouvee==0) {
      $ventiler_ttc=0;
      verbose_str_to_file(__FILE__, __FUNCTION__, "mise a zero de ventiler_ttc car tva_trouvee=$tva_trouvee");
    }
  }
  verbose_str_to_file(__FILE__, __FUNCTION__, "trouve ventiler_ttc=$ventiler_ttc ".print_r($activites,1));
  return $ventiler_ttc;
}

function get_ventiller_ht($activites, $base, $params_export) {

  if(is_FacNote_base($base)) $cltcontroller = new VerboseController('societe', $base);
	else $cltcontroller = new VerboseController();

  if($activites['type'] =='encaissement') {
    $table = 'planclients';
    $tva_intra_tag = 'tva_ve_intra';
    $tva_al_tag = 'tva_ve_al';
  } else {
    $table = 'planfournisseur';
    $tva_intra_tag = 'tva_ac_intra';
    $tva_al_tag = 'tva_ac_al';
  }
  $verb_str="";
  $conditions=array();
  $conditions['description']= $activites['detail'];
  $planfrs=$cltcontroller->searchConditionFromTable($table, $conditions);
  $planfrs=$planfrs[0];
  $ventiller_ht=0;
	foreach(array('ac_ht_t021', 'ac_ht_t055', 'ac_ht_t085', 'ac_ht_t00', 'ac_ht_t10', 'ac_ht_t20','tva_ac_ht_t021', 'tva_ac_ht_t055', 'tva_ac_ht_t085', 'tva_ac_ht_t00', 'tva_ac_ht_t10', 'tva_ac_ht_t20') as $tag) {
    if( ! Is_empty_str($planfrs[$tag]) ) $ventiller_ht = 1;
	}

  $type = $activites['type'];
  if( ($type=='encaissement')||($type=='facture') ) {
    if(preg_match('/PRESTA/i',$activites['description'])) $params_export_f = 've_serv_ht_t';
    else $params_export_f = 've_march_ht_t';
    $verb_str .="verifications des parametres dexport avec params_export_f=$params_export_f\n";

    $taux_2_field = array('00' =>'00', '2.1' =>'021', '2.10' =>'021', '5.5' =>'055', '5.50' =>'055', '8.5' =>'085', '8.50' =>'085', '10' =>'10', '10.00' =>'10', '20' =>'20', '20.00' =>'20');
    foreach($taux_2_field as $k=>$taux) {
      $verb_str .="test params_export_f=$params_export_f$taux =".$params_export[$params_export_f.$taux]."\n";
      if( ! Is_empty_str($params_export[$params_export_f.$taux])) {
        $ventiller_ht = 1;
        $verb_str .="trouve params_export_f=$params_export_f$taux\n";
      }
    }
  }

  if( ! Is_empty_str($planfrs[$tva_intra_tag]) ) $ventiller_ht = 2;
  if( ( ! Is_empty_str($planfrs['tva_ve_al']) ) && ( ! Is_empty_str($planfrs['tva_ac_al']) ) ) $ventiller_ht = 3;
  mysqli_close($cltcontroller);
  verbose_str_to_file(__FILE__, __FUNCTION__, "ventiller_ht = $ventiller_ht $verb_str \n".print_r($planfrs,1).print_r($params_export,1));
  return array($ventiller_ht, $planfrs);
}
function chg_to_ventil_HT($base, $type_element, $idchg) {
	$verboseController = new VerboseController($type_element, $base);
  $chg_elem = $verboseController->get($idchg, $type_element);
  verbose_str_to_file(__FILE__, __FUNCTION__, "chg_elem ".print_r($chg_elem,1));
  $csv_elems = search_csv_elem_in_base($base, $type_element, $idchg);
  $plan_frs = array();
  for($it=1;$it<5;$it++){
    $tag='prix_tva'.$it;
    $tag_taux='taux_tva'.$it;
    foreach($csv_elems as $elem){
      if(formater_montant($chg_elem[$tag]) == formater_montant($elem['debit']+$elem['credit'])){
        $taux_tva = formater_montant($chg_elem[$tag_taux]);

        if($taux_tva==2.1) $field_tva='021';
        else if($taux_tva==5.5) $field_tva='055';
        else if($taux_tva==10) $field_tva='10';
        else if($taux_tva==20) $field_tva='20';
        else $field_tva="";
        verbose_str_to_file(__FILE__, __FUNCTION__, "tag egal $tag pour taux=$taux_tva et field_tva=$field_tva");

        if(! Is_empty_str($field_tva)) $plan_frs['tva_ac_ht_t'.$field_tva] = $elem['num_cpt'];

        $montant_HT = formater_montant($chg_elem[$tag]*100/$taux_tva);
        foreach($csv_elems as $elem2){
          if($montant_HT == formater_montant($elem2['debit']+$elem2['credit'])){
            verbose_str_to_file(__FILE__, __FUNCTION__, "montant_HT=$montant_HT egale à ".print_r($elem2,1));
            $plan_frs['ac_ht_t'.$field_tva] = $elem2['num_cpt'];
          }
        }
      }
    }
  }

  mysqli_close($verboseController);
  verbose_str_to_file(__FILE__, __FUNCTION__, "plan_frs ".print_r($plan_frs,1));
  return $plan_frs;
}

function get_montants($activites, $regime_tva, $export_mode, $params_export, $base) {


  list($ventiller_ht, $planfrs) = get_ventiller_ht($activites, $base, $params_export);

	verbose_str_to_file(__FILE__, __FUNCTION__, "get act ".print_r($activites,1)."get params_export ".print_r($params_export,1));
	$tab = array();
	$tag='prix_ttc'; $tab[$tag]['credit'] = formater_montant($activites[$tag]); $tab[$tag]['debit'] = 0;

  foreach(array('frais_de_port', 'ecotax', 'livrets_net', 'remb_tickets', 'petits_lots', 'commission', 'remise', 'consigne', 'total_brut', 'mise_encaissee', 'biens_serv') as $tag){
    $tag_val = formater_montant($activites[$tag]);
    if($tag_val<0){ $tab[$tag]['credit'] = $tag_val * (-1); $tab[$tag]['debit'] = 0;}
    else { $tab[$tag]['credit'] = 0; $tab[$tag]['debit'] = $tag_val;}
  }

	for($it=1;$it<5;$it++){
		$tag='prix_tva'.$it; $tab[$tag]['credit'] = 0; $tab[$tag]['debit'] = formater_montant($activites[$tag]);

    $tag='prix_ht'.$it;
    $tab[$tag]['credit'] = 0; $tab[$tag]['debit'] = formater_montant($activites['base_ht'.$it]);
    if( ($tab[$tag]['credit'] == 0)&&($tab[$tag]['debit'] == 0) ){
      if(formater_montant($activites['taux_tva'.$it]) > 0)
        $tab[$tag]['credit'] = 0; $tab[$tag]['debit'] = formater_montant($tab['prix_tva'.$it]['debit'] * 100 / formater_montant($activites['taux_tva'.$it]));
    }
	}

	$total_tva = $tab['prix_tva1']['debit']+$tab['prix_tva2']['debit']+$tab['prix_tva3']['debit']+$tab['prix_tva4']['debit'];
	if(Is_empty_str($total_tva,0,1)) $total_tva = formater_montant($activites['prix_ttc']) - formater_montant($activites['prix_ht']);

	$tag='prix_tva'; $tab[$tag]['credit'] = 0; $tab[$tag]['debit'] = $total_tva;

	$tag='prix_ht'; $tab[$tag]['credit'] = 0; $tab[$tag]['debit'] = $tab['prix_ttc']['credit']-$tab['frais_de_port']['debit']-$tab['ecotax']['debit']-$tab['prix_tva']['debit'];

	if($activites['type']=='encaissement') $tab = inverser_tab($tab);
	if(($tab['prix_ttc']['debit'] < 0) || ($tab['prix_ttc']['credit'] < 0)) {
		$tab = inverser_tab($tab);
		$tab = moins_tab($tab);
	}


	$tab = egaliser_tab($tab, $activites['type'], $export_mode, $params_export, $ventiller_ht);
	verbose_str_to_file(__FILE__, __FUNCTION__, "Sortie egaliser ".print_r($tab,1));
  if( ($params_export['compta_treso']==3) || ($regime_tva =='FB') ) $tab = franchise_base($tab);
  if( ($base=='FA2836') && ($activites['type'] =='frais') ) $tab = franchise_base($tab);

	verbose_str_to_file(__FILE__, __FUNCTION__, print_r($tab,1));
	return $tab;

}

function get_tvacpt($taux_tva, $type, $CHARGES_SOCIETE_TO_CPT, $cpt_ht, $activites, $base, $params_export) {
	$cpt_tva=0;
	//verbose_str_to_file(__FILE__, __FUNCTION__, "on a CHARGES_SOCIETE_TO_CPT: ".print_r($CHARGES_SOCIETE_TO_CPT, 1));
	//verbose_str_to_file(__FILE__, __FUNCTION__, "taux_tva et type $taux_tva, $type: ");
	if(preg_match('/%/', $taux_tva)) $taux_tva = preg_replace('/%/', '', $taux_tva);
	$taux_tva = formater_montant($taux_tva);
	//verbose_str_to_file(__FILE__, __FUNCTION__, 'recherche TVA '.$taux_tva."%".print_r($CHARGES_SOCIETE_TO_CPT['TVA '.$taux_tva."%"], 1));
	if(preg_match('/^21/', $cpt_ht) || preg_match('/^20/', $cpt_ht)){
		$cpt_tva=$CHARGES_SOCIETE_TO_CPT["TVA SUR IMMO"][0];
		if(preg_match('/^\s*$/', $cpt_tva) || ($cpt_tva==0)) $cpt_tva=44566000;
	}
	else {
    list($ventiller_ht, $planfrs) = get_ventiller_ht($activites, $base, $params_export);
    verbose_str_to_file(__FILE__, __FUNCTION__, "get planfrs ".print_r($planfrs, 1)." et params_export ".print_r($params_export, 1));
    $taux_2_field = array('2.1' =>'021', '2.10' =>'021', '5.5' =>'055', '5.50' =>'055', '8.5' =>'085', '8.50' =>'085', '10' =>'10', '10.00' =>'10', '20' =>'20', '20.00' =>'20');
    $planfrs_f = 'tva_ac_ht_t'.$taux_2_field["$taux_tva"];
    $params_export_f = 'compte_tva';
    if( ($type=='encaissement')||($type=='facture') ) {
      if(preg_match('/PRESTA/i',$activites['description'])) $params_export_f = 'tva_serv_ht_t'.$taux_2_field[$taux_tva];
      else $params_export_f = 'tva_march_ht_t'.$taux_2_field[$taux_tva];
    }

    if( ! Is_empty_str($planfrs[$planfrs_f])) {
      $cpt=$planfrs[$planfrs_f];
      verbose_str_to_file(__FILE__, __FUNCTION__, "return $cpt de planfrs_f $planfrs_f");
    } else if( ! Is_empty_str($planfrs['cpt_tva'])){
      $cpt=$planfrs['cpt_tva'];
      verbose_str_to_file(__FILE__, __FUNCTION__, "return $cpt de planfrs cpt_tva");
    } else if( ! Is_empty_str($params_export[$params_export_f])){
      verbose_str_to_file(__FILE__, __FUNCTION__, "return $cpt de params_export_f $params_export_f");
      $cpt=$params_export[$params_export_f];
    }

    verbose_str_to_file(__FILE__, __FUNCTION__, "return $cpt pour taux_tva $taux_tva et type=$type planfrs_f=$planfrs_f params_export_f=$params_export_f");

    $cpt_tva=$cpt;
    verbose_str_to_file(__FILE__, __FUNCTION__, "return $cpt".print_r($params_export, 1));
		if(Is_empty_str($cpt_tva) || ($cpt_tva==0)) {
      if($type=='encaissement') $text_rech = "COLL";
      else $text_rech = "DED";
      $cpt_tva=$CHARGES_SOCIETE_TO_CPT["TVA $text_rech $taux_tva%"][0];

      if(preg_match('/^\s*$/', $cpt_tva) || ($cpt_tva==0)) {
        $cpt_tva=$CHARGES_SOCIETE_TO_CPT["TVA $text_rech $taux_tva"][0];
        if(preg_match('/^\s*$/', $cpt_tva) || ($cpt_tva==0)) {
          if($type=='encaissement') $cpt_tva = 44571500;
          else $cpt_tva = 44566000;
        }
      }
    }
	}
	return $cpt_tva;
}

function get_tvacode($taux_tva, $type, $CHARGES_SOCIETE_TO_CPT, $cpt_ht, $activites, $base, $params_export) {

	$code_tva="";
	//verbose_str_to_file(__FILE__, __FUNCTION__, "on a CHARGES_SOCIETE_TO_CPT: ".print_r($CHARGES_SOCIETE_TO_CPT, 1));
  //verbose_str_to_file(__FILE__, __FUNCTION__, "on a params_export: ".print_r($params_export, 1));

  $tva_list_val = array(20,10,8.5,5.5, 2.1, 0);
  $tva_list = array('20','10','085','055', '021', '00');
	if(preg_match('/%/', $taux_tva)) $taux_tva = preg_replace('/%/', '', $taux_tva);
	$taux_tva = formater_montant($taux_tva);

	verbose_str_to_file(__FILE__, __FUNCTION__, 'recherche par taux TVA '.$taux_tva."et type=$type");
  $idxbq=-1;
  foreach($tva_list_val as $tva) {
    $idxbq++;
    //verbose_str_to_file(__FILE__, __FUNCTION__, "$taux_tva==$tva");
    if($taux_tva==$tva) {
      if($type=='encaissement') $text_rech = "code_tva_ve";
      else $text_rech = "code_tva";
      $text_rech = $text_rech.$tva_list[$idxbq];
      $code_tva = $params_export[$text_rech];
      verbose_str_to_file(__FILE__, __FUNCTION__, "text_rech $text_rech => code_tva $code_tva");
    }
  }

  if(Is_empty_str($code_tva)) {
    if(preg_match('/^21/', $cpt_ht) || preg_match('/^20/', $cpt_ht)){
      $code_tva=$CHARGES_SOCIETE_TO_CPT["TVA SUR IMMO"][5];
    } else {
      if($type=='encaissement') $text_rech = "COLL";
      else $text_rech = "DED";
      verbose_str_to_file(__FILE__, __FUNCTION__, 'recherche TVA '.$taux_tva."%".print_r($CHARGES_SOCIETE_TO_CPT["TVA $text_rech $taux_tva%"], 1).print_r($CHARGES_SOCIETE_TO_CPT["TVA $text_rech $taux_tva"], 1));
      $code_tva=$CHARGES_SOCIETE_TO_CPT["TVA $text_rech $taux_tva%"][5];
      if(preg_match('/^\s*$/', $code_tva) || ($code_tva==0)) {
        $code_tva=$CHARGES_SOCIETE_TO_CPT["TVA $text_rech $taux_tva"][5];
      }
    }
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "trouve $code_tva");
	return $code_tva;
}


function get_ged_path($export_mode, $soc_infos, $soc_cpt, $path_pj) {
	$sepGED=$gedpath=$num_dossier="";
	$num_dossier=$soc_infos['num_dossier'];
	$gedpath=str_replace('F-WINSEP-F', '\\', $soc_infos['gedpath']);

	verbose_str_to_file(__FILE__, __FUNCTION__, "get $gedpath and $num_dossier".print_r($soc_infos, 1));

	if($export_mode==20){
		if(preg_match('/\\/', $gedpath)||preg_match('/\:/', $gedpath)) $sepGED=WIN_SEP;
		else $sepGED='/';
	}
	verbose_str_to_file(__FILE__, __FUNCTION__, "get $gedpath and $num_dossier and $sepGED".print_r($path_pj, 1));
	$list_pj=array();
	foreach($path_pj as $pt){
		$list_pj[]=$gedpath.$sepGED.$num_dossier.$sepGED.$pt;
	}
	verbose_str_to_file(__FILE__, __FUNCTION__, "get $gedpath and $num_dossier and $sepGED".print_r($list_pj, 1));
	return $list_pj;
}


function bissextile($annee) {


  $val=FALSE;
	if( (is_int($annee/4) && !is_int($annee/100)) || is_int($annee/400)) {
    //echo "*** bissextile $annee\n";
		$val=TRUE;
	} //else  echo "normal $annee\n";
  return $val;
}

function build_list_exercices($deb_exercice, $fin_exercice, $y_chg) {
  $list_exercices=array();
  if(Is_empty_str($y_chg)) $y_chg=date('Y');

	if(preg_match('?/?', $fin_exercice)) $fin_exercice=date_html_to_mysql($fin_exercice);
  if(Is_empty_str($fin_exercice)) $fin_exercice = $y_chg."-12-31";
	list($d_fin,$m_fin,$y_fin) = date_mysql_to_html($fin_exercice, 0, 1);
  $time_fin = mktime ( 23, 59, 59,$m_fin , $d_fin, $y_fin );


	if(preg_match('?/?', $deb_exercice)) $deb_exercice=date_html_to_mysql($deb_exercice);
  if(Is_empty_str($deb_exercice)) $deb_exercice = $y_chg."-01-01";
	list($d_deb,$m_deb,$y_deb) = date_mysql_to_html($deb_exercice, 0, 1);
  $time_deb = mktime ( 00, 00, 00, $m_deb , $d_deb, $y_deb );


  $list_exercices=array();
  $mode_sup_1_an=0;
  if( ($time_fin-$time_deb) > (366*24*60*60) ) {
    //if($avec_verbose==1) echo "cas premiere annee plus grand que 1 an => y_deb=$y_deb y_fin=$y_fin ------";
    $list_exercices[]=array($time_deb, $time_fin);
    $mode_sup_1_an=1;
  }
  if( ($time_fin-$time_deb) < (364*24*60*60) ) {
    //if($avec_verbose==1) echo "cas premiere annee plus petit que 1 an => y_deb=$y_deb y_fin=$y_fin ------";
    $list_exercices[]=array($time_deb, $time_fin);
    $mode_sup_1_an=1;
  }
  //echo "deb exercice 00, 00, 00, $d_deb/$m_deb/$y_deb\nfin exercice 23, 59, 59,$d_fin/$m_fin/$y_fin\n mode_sup_1_an= $mode_sup_1_an\n";

  if(  $mode_sup_1_an != 1 ){
    for($i_y=10; $i_y>-1; $i_y--) {
      $fev28 = mktime( 0, 0, 0, 2 , 28, $y_fin-$i_y );
      $time_fin = mktime ( 0, 0, 0,$m_fin , $d_fin, $y_fin-$i_y );

      $nb_jours=365;
      $time_deb = $time_fin - ($nb_jours * 24 * 60 * 60);
       if (date('H', $time_deb) == '23') {
            $time_deb += 60*60;
       }

      //echo "time_deb=".date('d/m/Y H:i:s', $time_deb)." time_fin=".date('d/m/Y H:i:s', $time_fin)." - nb_jours=$nb_jours\n";
      $y_calc = date('Y', $time_deb);
      if($y_calc != ($y_fin-$i_y)) {
        if( bissextile($y_fin-$i_y) ) {
          if($m_fin > 2) {
            $nb_jours=366;
          } else if($m_fin == 2) {
            if($d_fin>27) $nb_jours=366;
          }
        } else if( bissextile($y_calc) ) {
          //echo "cas $y_calc bissextile\n";
          list($d_calc,$m_calc,$y_calc) = date_mysql_to_html( date('Y-m-d', $time_deb), 0, 1);
          if($m_calc < 2) {
            //echo "cas $m_calc avant fev\n";
            $nb_jours=366;
          } else if($m_calc == 2) {
            //echo "cas $m_calc  fev\n";
            if($d_calc<28) $nb_jours=366;
          }
        }
      } else if( bissextile($y_fin-$i_y) ) $nb_jours=366;

      $time_deb = $time_fin - ($nb_jours * 24 * 60 * 60) + (24 * 60 * 60);
      //echo "time_deb=".date('d/m/Y H:i:s', $time_deb)." time_fin=".date('d/m/Y H:i:s', $time_fin)." - nb_jours=$nb_jours\n";
      if (date('H', $time_deb) == '23') {
              $time_deb += 60*60;
       }

      $list_exercices[]=array($time_deb, $time_fin);
    }
  }

  for($i_y=1; $i_y<10; $i_y++){
    $time_fin = mktime ( 0, 0, 0,$m_fin , $d_fin, $y_fin+$i_y );

    $nb_jours=365;
    $time_deb = $time_fin - ($nb_jours * 24 * 60 * 60);
    $y_calc = date('Y', $time_deb);
    if($y_calc != ($y_fin+$i_y)) {
      if( bissextile($y_fin+$i_y) ) {
        if($m_fin > 2) {
          $nb_jours=366;
        } else if($m_fin == 2) {
          if($d_fin>27) $nb_jours=366;
        }
      } else if( bissextile($y_calc) ) {
        //echo "cas $y_calc bissextile\n";
        list($d_calc,$m_calc,$y_calc) = date_mysql_to_html( date('Y-m-d', $time_deb), 0, 1);
        if($m_calc < 2) {
          //echo "cas $m_calc avant fev\n";
          $nb_jours=366;
        } else if($m_calc == 2) {
          //echo "cas $m_calc  fev\n";
          if($d_calc<28) $nb_jours=366;
        }
      }
    } else if( bissextile($y_fin+$i_y) ) $nb_jours=366;

    $time_deb = $time_fin - ($nb_jours * 24 * 60 * 60) + (24 * 60 * 60);

    $list_exercices[]=array($time_deb, $time_fin);
  }

  return $list_exercices;
}

function build_ged_dir_date($date, $fin_exercice, $deb_exercice, $export_mod, $avec_verbose=null) {

	if(preg_match('?/?', $date)) $date=date_html_to_mysql($date);
	list($d_chg,$m_chg,$y_chg) = date_mysql_to_html($date, 0, 1);
  $time_chg = mktime ( 0, 0, 0,$m_chg , $d_chg, $y_chg );

  $list_exercices = build_list_exercices($deb_exercice, $fin_exercice, $y_chg);

  foreach($list_exercices as $debfin){
    $time_deb=$debfin[0];
    $time_fin=$debfin[1];

    if( ($time_chg>$time_deb-1) && ($time_chg < $time_fin+1) ) {
      list($d_fin,$m_fin,$y_fin) = date_mysql_to_html(date('Y-m-d', $time_fin), 0, 1);
      list($d_deb,$m_deb,$y_deb) = date_mysql_to_html(date('Y-m-d', $time_deb), 0, 1);

    }
  }



  if($m_deb<10) $m_deb="0$m_deb";
  if($m_fin<10) $m_fin="0$m_fin";
  $y_deb=preg_replace('/^\s*20/', '', $y_deb);
  $y_fin=preg_replace('/^\s*20/', '', $y_fin);
  $ged_dir="$y_fin$m_fin";
  if(($export_mod==1)||($export_mod==36)||($export_mod==41)) $ged_dir="$m_deb$y_deb$m_fin$y_fin";
  if( ($export_mod==33)||($export_mod==32) ) {
    $d_deb = str_pad($d_deb, 2, "0", STR_PAD_LEFT);
    $d_fin = str_pad($d_fin, 2, "0", STR_PAD_LEFT);
    $y_deb="20$y_deb";
    $y_fin="20$y_fin";
    $ged_dir="$d_deb$m_deb$y_deb$d_fin$m_fin$y_fin";
  }

	verbose_str_to_file(__FILE__, __FUNCTION__, "date=$date, fin_exercice=$fin_exercice deb_exercice=$deb_exercice export_mod=$export_mod ==> ged_dir $ged_dir\n");

	return $ged_dir;
}

function chg_to_csv_entry($activites, $export_mode, $zip_dir, $month_idx,$year, $type, $CHARGES_SOCIETE_TO_CPT,$banq_code_lib,$cur_idx,$tar_bq_list,
													$chg_to_tvacpt, $non_associated, $base,$CHARGES_SOCIETE_TO_LIB) {


	//verbose_str_to_file(__FILE__, __FUNCTION__, "pour la base $base get CHARGES_SOCIETE_TO_LIB".print_r($CHARGES_SOCIETE_TO_LIB, 1));
  $export_mode_xml = get_export_mode('', $base,1); //XML
	$params_export=get_params_export($base);
	$application_type = get_application_type();

	if(is_FacNote_base($base)) $cltcontroller = new VerboseController('societe', $base);
	else $cltcontroller = new VerboseController();
	$nb_banq = $cltcontroller->get_bigger_id('banque');
  $nb_banq=$nb_banq['id'];
  //$all_banques = $cltcontroller->selectAllFromTable('banque');
  verbose_str_to_file(__FILE__, __FUNCTION__, "pour la base $base get all_banques".print_r($all_banques, 1)." et nb_banq ".print_r($nb_banq,1));
	$regime_tva = $cltcontroller->get(1, 'tvaparams');

	$ndf_as_chg = 0;

	$all_csv_elem=array();
	$nb_jours_in_month = cal_days_in_month(CAL_GREGORIAN, $month_idx,$year);
	$derniere_date = $nb_jours_in_month.'/'.$month_idx.'/'.$year;
	$soc_infos = $cltcontroller->get(1, 'societe');
  mysqli_close($cltcontroller);
	$num_dossier=$soc_cpt['num_dossier'];
	$base_comptable = $soc_infos['comptable'];
	if($export_mode<0) $ndf_as_chg=1;
	else if(is_FacNote_base($base_comptable)) {
		verbose_str_to_file(__FILE__, __FUNCTION__, "_GET type '$type': sur base comptable $base_comptable");
		$verboseController = new VerboseController('societe', $base_comptable);
		$soc_cpt = $verboseController->get(1, 'societe');
		mysqli_close($verboseController);
		$ndf_as_chg=$soc_cpt['ndf_as_chg'];
	} else {
		$ndf_as_chg=$soc_infos['ndf_as_chg'];
	}
	verbose_str_to_file(__FILE__, __FUNCTION__, "get ndf_as_chg $ndf_as_chg activites".print_r($activites, 1));

	for($i=0; $i<sizeof($activites); $i++) {
    $famille=substr($activites[$i]['type'],0,1).$activites[$i]['id'];
    if($activites[$i]['id']>0){
      $en_banque_uniquement = get_en_banque_uniquement($CHARGES_SOCIETE_TO_CPT, $activites[$i]['description'], $activites[$i]['rapprochement'], $params_export['compta_treso']);

      list($ventiller_ht, $planfrs) = get_ventiller_ht($activites[$i], $base, $params_export);

      verbose_str_to_file(__FILE__, __FUNCTION__, "en_banque_uniquement=$en_banque_uniquement treat activites".print_r($activites[$i], 1));

      list($lib_credit, $cpt_du_planf) = get_libcredit($activites[$i], $CHARGES_SOCIETE_TO_LIB, $cltcontroller, $export_mode, $params_export, $type, $ndf_as_chg, $base, $base_comptable);
      if($en_banque_uniquement == 1) $cpt_du_planf=0;
      $lib_creditht = get_numcpt_ht($activites[$i], $cpt_du_planf, $CHARGES_SOCIETE_TO_CPT, $cltcontroller, $base);
      if( ($activites[$i]['type']=='frais') && (($base=='FA2836')) ) $lib_creditht='467900';

      if( ! ($nb_banq > 0)) $en_banque_uniquement = 0;
      else if($non_associated==1) $en_banque_uniquement = 0;
      else if( ($activites[$i]['type']=="paie") ) $en_banque_uniquement = 1;
      verbose_str_to_file(__FILE__, __FUNCTION__, "en_banque_uniquement=$en_banque_uniquement et number ligne banque = $nb_banq et non_associated = $non_associated");


      if(($type == 'frais')&&( ! ($ndf_as_chg>0))) 1;
      else if(($type == 'frais')&&($export_mode==13)) 1;
      else if($en_banque_uniquement == 1) $lib_credit = $lib_creditht;

      verbose_str_to_file(__FILE__, __FUNCTION__, "en_banque_uniquement=$en_banque_uniquement donc lib_credit = lib_creditht = $lib_creditht");


      if($activites[$i]['type']=="") $activites[$i]['type']=$type;
      $derniere_date = date_mysql_to_html($activites[$i]['created_at']);

      if( Is_empty_str($activites[$i]['date_echeance'],1) ) $date_echeance = null;
      else $date_echeance=date_mysql_to_html($activites[$i]['date_echeance']);

      $montants = get_montants($activites[$i], $regime_tva['regime'], $export_mode, $params_export, $base);

      if( Is_empty_str($lib_creditht) &&( ! ($non_associated==1)) ) return array(-1,-1);


      if($activites[$i]['type']=="avoir")
        list($path_pj, $cur_idx,$tar_bq_list, $nom_file_src, $list_liens, $path_pj_ext) = pj_to_csv_entry($activites[$i]['id'], $activites[$i]['type'], $zip_dir, $cur_idx,$tar_bq_list, $base, $params_export, $export_mode);
      else list($path_pj, $cur_idx,$tar_bq_list, $nom_file_src, $list_liens, $path_pj_ext) = pj_to_csv_entry($activites[$i], $activites[$i]['type'], $zip_dir, $cur_idx,$tar_bq_list, $base, $params_export, $export_mode);
      verbose_str_to_file(__FILE__, __FUNCTION__, "lib_creditht $lib_creditht, lib_credit $lib_credit , $path_pj, $cur_idx,$tar_bq_list");

      if($activites[$i]['type']=='avoir'){
        $ref = $activites[$i]['reference'];
        if(Is_empty_str($banq_code_lib))$banq_code_lib='A';
      }
      else $ref = $activites[$i]['num_fact'];

      if(preg_match('/^\s*$/', $banq_code_lib)) $banq_code_lib=' ';
      if($activites[$i]['type'] == 'encaissement')  $banq_code_lib='F';
      if($activites[$i]['type']=='avoir') $banq_code_lib='A';
      if( ($base_comptable=='FA2674')||($base_comptable=='FA0362') ) {
        $banq_code_lib='F';
        if($activites[$i]['prix_ttc'] < 0) $banq_code_lib='A';
      }

      $cpt_groupe=$nature="";
      if( ($export_mode==33)||($export_mode==6)){
        if(($activites[$i]['type']=="encaissement") || ($activites[$i]['type']=="facture")){
          $cpt_groupe=$params_export['long_cpt_gen_ve'];
          if($export_mode!=6){
            if(Is_empty_str($cpt_groupe))$cpt_groupe="41100000";
          }
          $nature='CLI';
        } else {
          $cpt_groupe=$params_export['long_cpt_gen_ac'];
          if($export_mode!=6){
            if(Is_empty_str($cpt_groupe)) $cpt_groupe="40100000";
          }
          $nature='FOU';
        }
      } else {
        if(($activites[$i]['type']=="encaissement") || ($activites[$i]['type']=="facture")){
          $nature='FCC';
        } else {
          if($activites[$i]['prix_ttc']<0) $nature='AVF';
          else $nature='FAF';
        }
      }

      $tmp_csv_elem = csv_elem_from_base($base, $activites[$i]['type'], $activites[$i]['id'], $path_pj, $list_liens, $export_mode, $banq_code_lib, $ref, $cpt_groupe, $params_export);

      if(count($tmp_csv_elem)>1) {
        verbose_str_to_file(__FILE__, __FUNCTION__, "pris de la base".print_r($tmp_csv_elem, 1));
        for($ix=0;$ix<count($tmp_csv_elem);$ix++){
          $tmp_csv_elem[$ix]['famille'] = $famille;
        }

        if(! Is_empty_str($tmp_csv_elem[0]['num_cpt'])) $lib_credit=$tmp_csv_elem[0]['num_cpt'];
        if($en_banque_uniquement == 1) $lib_credit=$tmp_csv_elem[1]['num_cpt'];
        $ref = $tmp_csv_elem[0]['num_fact'];
        if($en_banque_uniquement == 1) $tmp_csv_elem=array();
        verbose_str_to_file(__FILE__, __FUNCTION__, "pris de la base apres en_banque_uniquement=$en_banque_uniquement".print_r($tmp_csv_elem, 1));

      } else {
        verbose_str_to_file(__FILE__, __FUNCTION__, "construction csv elem");
        $tmp_csv_elem=array();

        $numero_piece = build_num_piece($activites[$i]);
        $code_j = get_code_j($params_export, $activites[$i], $export_mode);

        if(preg_match('/^\s*$/', $activites[$i]['detail'])) $desc_gene = $activites[$i]['description'];
        else $desc_gene = $activites[$i]['detail'];
        //if($base_comptable == 'FA5287') $desc_gene .= " ".$ref;

        $csv_elem=array();
        $csv_elem['date']=$derniere_date;
        $csv_elem['famille']=$famille;
        $csv_elem['cpt_general']=$cpt_groupe;
        $csv_elem['nature']=$nature;
        $csv_elem['date_echeance']=$date_echeance;
        if( ! Is_empty_str($activites[$i]['cb']) ) $csv_elem['cb']=$activites[$i]['cb'];
        $csv_elem['code_j']=$code_j;
        $csv_elem['type_chg']=$activites[$i]['description'];
        $csv_elem['id_type']=$activites[$i]['id'];
        $csv_elem['pos_txt']='TTC';
        $csv_elem['ged_dir']=build_ged_dir_date($derniere_date, $params_export['fin_exercice'], $params_export['debut_exercice'], $export_mode);
        $csv_elem['path_pj']=$path_pj;
        $csv_elem['path_pj_ext']=$path_pj_ext;
        $csv_elem['list_liens']=$list_liens;
        $csv_elem['description']=$desc_gene;
        $csv_elem['devise']='E';
        $csv_elem['num_fact']=$ref;
        $csv_elem['id_fact']=$activites[$i]['type']."_".$activites[$i]['id'];
        $csv_elem['cpt_lib']=$lib_credit;
        $csv_elem['position']=1;
        $csv_elem['code_lib']=$banq_code_lib;
        $csv_elem['type']='M';
        if($export_mode==20) $csv_elem['type']='1';
        $csv_elem['num_piece']=$numero_piece;

        $csv_elem['fichier_source']=fichier_src($nom_file_src);
        $csv_elem['type_element']=$activites[$i]['type'];
        if($activites[$i]['type'] == 'frais') {
          if(is_FacNote_base($base)) $cltcontroller = new VerboseController('societe', $base);
          else $cltcontroller = new VerboseController();
          $user_infos = $cltcontroller->get($activites[$i]['id_manager'], 'manager');
          mysqli_close($cltcontroller);
          $csv_elem['user_login']=$user_infos['login'];
        }

        verbose_str_to_file(__FILE__, __FUNCTION__, "csv_elem genere pour cette activite".print_r($csv_elem, 1));
        if(($type == 'frais')&&( ! ($ndf_as_chg>0))) {
          $csv_elem['num_cpt']=$lib_creditht;
          $csv_elem['debit']=formater_montant($montants['prix_ht']['debit'],0,0,0,1);
          $csv_elem['credit']=formater_montant($montants['prix_ht']['credit'],0,0,0,1);
          if( ($csv_elem['debit']>0)|| ($csv_elem['credit']>0)) $tmp_csv_elem[]=$csv_elem;
          if($export_mode==20) $csv_elem['type']='2';
          $total_ht += $activites[$i]['prix_ht'];
          $total_ttc += $activites[$i]['prix_ttc'];
          verbose_str_to_file(__FILE__, __FUNCTION__, "frais totaux $total_ttc $total_ht csv_elem".print_r($csv_elem, 1).' avec frais '.print_r($activites[$i], 1));
        } else {
          $add_elem=1;
          //if($en_banque_uniquement == 1) $add_elem=0;

          verbose_str_to_file(__FILE__, __FUNCTION__, "add_elem=$add_elem mettre en_banque_uniquement=".$en_banque_uniquement ." et lib_credit=$lib_credit et non_associated=$non_associated");
          //if($activites[$i]['description']=="DIVERS ET AUTRES CHARGES")$add_elem=1;
          if($add_elem==1) {
            if($export_mode==6666666) {
              if(! Is_empty_str($cpt_groupe)) {
                $csv_elem['auxiliaire_cpt'] = $lib_credit;
                $csv_elem['auxiliaire_desc'] = $desc_gene;
                $lib_credit=$cpt_groupe;
              }
            }
            $csv_elem['num_cpt']=$lib_credit;
            $csv_elem['add_plancpt']=1;
            $csv_elem['debit']=formater_montant($montants['prix_ttc']['debit'],0,0,0,1);;
            $csv_elem['credit']=formater_montant($montants['prix_ttc']['credit'],0,0,0,1);
            if($export_mode!=26) $tmp_csv_elem[]=$csv_elem;
            if($export_mode==20) $csv_elem['type']='2';

            $ventiler_ttc=0;
            if( ($activites[$i]['type']=='encaissement')||($activites[$i]['type']=='facture') ) {
              $ventiler_ttc = get_ventiller_TTC($activites[$i], $base, $params_export);
              if($ventiler_ttc==1) $tmp_csv_elem=array();
            }
            verbose_str_to_file(__FILE__, __FUNCTION__, "trouve ventiler_ttc=$ventiler_ttc");

            $ss_mode=0;
            foreach($activites[$i] as $key=>$val) {
              if( preg_match('/^\s*ss_/', $key) && (formater_montant($val)>0) ) $ss_mode=1;
              if( preg_match('/^\s*m_/', $key) && (formater_montant($val)>0) ) $ss_mode=2;
              if( preg_match('/^\s*m_/', $key) && (formater_montant($val)<0) ) $ss_mode=2;
              if(($key=="eco10")&& (formater_montant($val)>0)) $ss_mode=3;
              if(($key=="eco20")&& (formater_montant($val)>0)) $ss_mode=3;
              if(($key=="livraison")&& (formater_montant($val)>0)) $ss_mode=3;
              if(($key=="pose")&& (formater_montant($val)>0)) $ss_mode=3;
              if(($key=="meubles")&& (formater_montant($val)>0)) $ss_mode=3;
              if(($key=="accessoires")&& (formater_montant($val)>0)) $ss_mode=3;
            }
            verbose_str_to_file(__FILE__, __FUNCTION__, "pour cet element ss_mode==$ss_mode".print_r($activites[$i],1));
            if($ss_mode>0) {

              $tva_by_plancpt=$tvatotal_deb=$tvatotal_cred = $alltotal_deb=$alltotal_cred=0;
              for($it=1;$it<5;$it++){
                $taux_tvaVal = formater_montant($activites[$i]['taux_tva'.$it]);
                $tvatotal_deb  += $montants['prix_tva'.$it]['debit'];
                $tvatotal_cred += $montants['prix_tva'.$it]['credit'];
              }
              verbose_str_to_file(__FILE__, __FUNCTION__, "taux_tvaVal trouve $taux_tvaVal pour et prix_tva $tvatotal_deb - $tvatotal_cred");
              if(($tvatotal_deb == 0)&&($tvatotal_cred == 0)){
                $tvatotal_deb  += $montants['prix_tva']['debit'];
                $tvatotal_cred += $montants['prix_tva']['credit'];
              }

              $key_sup_arr=array(
                'ss_achatmarch'=>array('ss_achatmarch', '70850000', '60700', $desc_gene, 'PORT', 2),
                'ss_consomables'=>array('ss_consomables', '70850000', '60260', $desc_gene, 'ECOTAX', 2),
                'ss_flyersjouets'=>array('ss_flyersjouets', '70850000', '60710', $desc_gene,'SUP', 5),
                'ss_produitsentretien'=>array('ss_produitsentretien', '60630', '60630', $desc_gene,'SUP', 5),
                'ss_petitesfournitures'=>array('ss_petitesfournitures', '60640', '60640', $desc_gene,'SUP', 5),
                'ss_bieres'=>array('ss_bieres', '70850000', '60700', $desc_gene,'SUP', 5),

                'm_a'=>array('m_a', '60640', '60640', $desc_gene,'SUP', 5),
                'm_b'=>array('m_b', '60640', '60640', $desc_gene,'SUP', 5),
                'm_c'=>array('m_c', '60640', '60640', $desc_gene,'SUP', 5),
                'm_d'=>array('m_d', '60640', '60640', $desc_gene,'SUP', 5),
                'm_e'=>array('m_e', '60640', '60640', $desc_gene,'SUP', 5),
                'm_f'=>array('m_f', '60640', '60640', $desc_gene,'SUP', 5),
                'm_g'=>array('m_g', '60640', '60640', $desc_gene,'SUP', 5),
                'm_h'=>array('m_h', '60640', '60640', $desc_gene,'SUP', 5),
                'm_i'=>array('m_i', '60640', '60640', $desc_gene,'SUP', 5),
                'm_j'=>array('m_j', '60640', '60640', $desc_gene,'SUP', 5),

                'eco10'=>array('eco10', '701100', '701100', $desc_gene,'SUP', 5),
                'eco20'=>array('eco20', '701200', '701200', $desc_gene,'SUP', 5),
                'livraison'=>array('livraison', '708000', '708000', $desc_gene,'SUP', 5),
                'pose'=>array('pose', '706100', '706100', $desc_gene,'SUP', 5),
                'meubles'=>array('meubles', '707100', '707100', $desc_gene,'SUP', 5),
                'accessoires'=>array('accessoires', '707200', '707200', $desc_gene,'SUP', 5),

              );

              if($base=='FA5928'){
                $key_sup_arr=array(
                  'ss_achatmarch'=>array('ss_achatmarch', '70850000', '60700', $desc_gene, 'PORT', 2),
                  'ss_consomables'=>array('ss_consomables', '70850000', '60260', $desc_gene, 'ECOTAX', 2),
                  'ss_flyersjouets'=>array('ss_flyersjouets', '70850000', '60710', $desc_gene,'SUP', 5),
                  'ss_produitsentretien'=>array('ss_produitsentretien', '60630', '60630', $desc_gene,'SUP', 5),
                  'ss_petitesfournitures'=>array('ss_petitesfournitures', '60640', '60640', $desc_gene,'SUP', 5),
                  'ss_bieres'=>array('ss_bieres', '70850000', '60700', $desc_gene,'SUP', 5),

                  'm_a'=>array('m_a', '6064010', '606401', $desc_gene,'SUP', 5),
                  'm_b'=>array('m_b', '6064020', '606402', $desc_gene,'SUP', 5),
                  'm_c'=>array('m_c', '6064030', '606403', $desc_gene,'SUP', 5),
                  'm_d'=>array('m_d', '6064040', '606404', $desc_gene,'SUP', 5),
                  'm_e'=>array('m_e', '6064050', '606405', $desc_gene,'SUP', 5),
                  'm_f'=>array('m_f', '6064060', '606406', $desc_gene,'SUP', 5),
                  'm_g'=>array('m_g', '6064070', '606407', $desc_gene,'SUP', 5),
                  'm_h'=>array('m_h', '6064080', '606408', $desc_gene,'SUP', 5),
                  'm_i'=>array('m_i', '6064090', '606409', $desc_gene,'SUP', 5),
                  'm_j'=>array('m_j', '6064099', '6064099', $desc_gene,'SUP', 5),
                );

              }

              if($base_comptable=='FA0511'){
                $key_sup_arr=array(
                  'ss_achatmarch'=>array('ss_achatmarch', '70850000', '60700', $desc_gene, 'PORT', 2),
                  'ss_consomables'=>array('ss_consomables', '70850000', '60260', $desc_gene, 'ECOTAX', 2),
                  'ss_flyersjouets'=>array('ss_flyersjouets', '70850000', '60710', $desc_gene,'SUP', 5),
                  'ss_produitsentretien'=>array('ss_produitsentretien', '60630', '60630', $desc_gene,'SUP', 5),
                  'ss_petitesfournitures'=>array('ss_petitesfournitures', '60640', '60640', $desc_gene,'SUP', 5),
                  'ss_bieres'=>array('ss_bieres', '70850000', '60700', $desc_gene,'SUP', 5),

                  'm_a'=>array('m_a', '6071000', '6071000', $desc_gene,'SUP', 5),
                  'm_b'=>array('m_b', '6071000', '6071000', $desc_gene,'SUP', 5),
                  'm_c'=>array('m_c', '6063000', '6063000', $desc_gene,'SUP', 5),
                  'm_d'=>array('m_d', '6073000', '6073000', $desc_gene,'SUP', 5),
                  'm_e'=>array('m_e', '6073000', '6073000', $desc_gene,'SUP', 5),
                  'm_f'=>array('m_f', '6063000', '6063000', $desc_gene,'SUP', 5),
                  'm_g'=>array('m_g', '6063000', '6063000', $desc_gene,'SUP', 5),
                  'm_h'=>array('m_h', '6064000', '6064000', $desc_gene,'SUP', 5),
                  'm_i'=>array('m_i', '6064000', '6064000', $desc_gene,'SUP', 5),
                  'm_j'=>array('m_j', '6064000', '6064000', $desc_gene,'SUP', 5),
                );

              }

              if( ($base_comptable=='FA5798')||($base_comptable=='FA0362')){
                $key_sup_arr=array(
                  'ss_achatmarch'=>array('ss_achatmarch', '70850000', '60700', $desc_gene, 'PORT', 2),
                  'ss_consomables'=>array('ss_consomables', '70850000', '60260', $desc_gene, 'ECOTAX', 2),
                  'ss_flyersjouets'=>array('ss_flyersjouets', '70850000', '60710', $desc_gene,'SUP', 5),
                  'ss_produitsentretien'=>array('ss_produitsentretien', '60630', '60630', $desc_gene,'SUP', 5),
                  'ss_petitesfournitures'=>array('ss_petitesfournitures', '60640', '60640', $desc_gene,'SUP', 5),
                  'ss_bieres'=>array('ss_bieres', '70850000', '60700', $desc_gene,'SUP', 5),

                  'm_a'=>array('m_a', '60720100', '60720100', $desc_gene,'SUP', 5),
                  'm_b'=>array('m_b', '60700100', '60700100', $desc_gene,'SUP', 5),
                  'm_c'=>array('m_c', '60700200', '60700200', $desc_gene,'SUP', 5),
                  'm_d'=>array('m_d', '60700200', '60700200', $desc_gene,'SUP', 5),
                  'm_e'=>array('m_e', '60700200', '60700200', $desc_gene,'SUP', 5),
                  'm_f'=>array('m_f', '60630000', '60630000', $desc_gene,'SUP', 5),
                  'm_g'=>array('m_g', '60630000', '60630000', $desc_gene,'SUP', 5),
                  'm_h'=>array('m_h', '60640000', '60640000', $desc_gene,'SUP', 5),
                  'm_i'=>array('m_i', '47100000', '47100000', $desc_gene,'SUP', 5),
                  'm_j'=>array('m_j', '47100000', '47100000', $desc_gene,'SUP', 5),
                );
                verbose_str_to_file(__FILE__, __FUNCTION__, "FA0362 key_sup_arr pour key_sup trouve montant ".print_r($ocr_log_inf[$key_sup],1));
              }

              foreach($key_sup_arr as $key_sup => $key_def){
                verbose_str_to_file(__FILE__, __FUNCTION__, "key_sup_arr pour key_sup trouve montant ".print_r($ocr_log_inf[$key_sup],1));
                //if($ocr_log_inf[$key_sup] != 0){
                if($activites[$i][$key_sup] != 0){
                  if(! Is_empty_str($planfrs[$key_def[0]])) $csv_elem['num_cpt']=$planfrs[$key_def[0]];
                  else if(! Is_empty_str($params_export[$key_def[0]])) $csv_elem['num_cpt']=$params_export[$key_def[0]];
                  else if($activites[$i]['type']=='encaissement') $csv_elem['num_cpt']=$key_def[1];
                  else $csv_elem['num_cpt']=$key_def[2];
                  verbose_str_to_file(__FILE__, __FUNCTION__, "num_cpt= ".$csv_elem['num_cpt']);

                  $csv_elem['add_plancpt']=0;
                  if($activites[$i]['type']=='encaissement'){
                    $csv_elem['debit']=0;
                    $csv_elem['credit']=formater_montant($activites[$i][$key_sup]);
                  }else {
                    $csv_elem['debit']=formater_montant($activites[$i][$key_sup]);;
                    $csv_elem['credit']=0;
                  }

                  if($csv_elem['debit']<0){
                    $csv_elem['credit']=$csv_elem['debit']*(-1);
                    $csv_elem['debit']=0;
                  } else if($csv_elem['credit']<0){
                    $csv_elem['debit']=$csv_elem['credit']*(-1);
                    $csv_elem['credit']=0;
                  }


                  $csv_elem['position']=$key_def[5];
                  $csv_elem['description']=$key_def[3];
                  $csv_elem['pos_txt']=$key_def[4];
                  $tmp_csv_elem[]=$csv_elem;

                  verbose_str_to_file(__FILE__, __FUNCTION__, "pour la key_sup=$key_sup CSV element ajoute\n.".print_r($csv_elem,1));

                  $csv_elem['position']=0;
                  $csv_elem['description']=$desc_gene;
                  $alltotal_deb  += formater_montant($csv_elem['debit']);
                  $alltotal_cred += formater_montant($csv_elem['credit']);

                }
              }

              if($ss_mode==1){
                //if($activites[$i]['type'] == 'encaissement') {
                $csv_elem['num_cpt']='44566';
                $csv_elem['taux']=$taux_tvaVal;
                $csv_elem['pos_txt']='TVA';
                $csv_elem['add_plancpt']=0;
                $csv_elem['debit']=formater_montant($tvatotal_deb,0,0,0,1);
                $csv_elem['credit']=formater_montant($tvatotal_cred,0,0,0,1);
                $tmp_csv_elem[]=$csv_elem;
                $tva_by_plancpt=1;
              } else if(($ss_mode==2)||($ss_mode==3)){
                for($it=1;$it<5;$it++) {
                  $csv_elem['num_cpt']='44566000';
                  $csv_elem['taux']=$taux_tvaVal;
                  $csv_elem['pos_txt']='TVA';
                  $csv_elem['add_plancpt']=0;
                  $csv_elem['debit']=formater_montant($montants['prix_tva'.$it]['debit'],0,0,0,1);
                  $csv_elem['credit']=formater_montant($montants['prix_tva'.$it]['credit'],0,0,0,1);
                  $tmp_csv_elem[]=$csv_elem;
                  $tva_by_plancpt=1;

                  $alltotal_deb  += formater_montant($csv_elem['debit']);
                  $alltotal_cred += formater_montant($csv_elem['credit']);

                }
                if($alltotal_deb != formater_montant($montants['prix_ttc']['credit'])){
                  $debit_to_set = formater_montant(formater_montant($montants['prix_ttc']['credit']) - $alltotal_deb);
                  if($debit_to_set>0){
                    $csv_elem['debit']=formater_montant($debit_to_set ,0,0,0,1);;
                    $csv_elem['credit']=0;
                    $csv_elem['position']=5;
                    $csv_elem['description']=$desc_gene;
                    $csv_elem['pos_txt']=1;
                    $csv_elem['num_cpt'] = $key_sup_arr['m_j'][2];
                    $tmp_csv_elem[]=$csv_elem;

                    $alltotal_deb  += formater_montant($csv_elem['debit']);
                    $alltotal_cred += formater_montant($csv_elem['credit']);
                  }
                }

                if($ss_mode==3){
                  verbose_str_to_file(__FILE__, __FUNCTION__, "ss_mode==3 et montants et alltotal_cred=$alltotal_cred".print_r($montants,1));

                  if($alltotal_cred != formater_montant($montants['prix_ttc']['debit'])){
                    $debit_to_set = formater_montant(formater_montant($montants['prix_ttc']['debit']) - $alltotal_cred);
                    $csv_elem['credit']=formater_montant($debit_to_set ,0,0,0,1);;
                    $csv_elem['debit']=0;
                    $csv_elem['position']=5;
                    $csv_elem['description']=$desc_gene;
                    $csv_elem['pos_txt']=1;
                    $csv_elem['num_cpt'] = '701000';
                    $tmp_csv_elem[]=$csv_elem;
                    verbose_str_to_file(__FILE__, __FUNCTION__, "ss_mode==3 et montants et alltotal_cred=$alltotal_cred".print_r($csv_elem,1));
                  }
                }

                if($ss_mode==2) {
                  verbose_str_to_file(__FILE__, __FUNCTION__, "ss_mode==2 et montants et alltotal_cred=$alltotal_cred".print_r($montants,1));
                  verbose_str_to_file(__FILE__, __FUNCTION__, "ss_mode==2 et montants et alltotal_cred=$alltotal_cred".print_r($tmp_csv_elem,1));


                  $debit_to_set = formater_montant($alltotal_cred - $alltotal_deb)+formater_montant($montants['prix_ttc']['credit'])-
                                formater_montant($montants['prix_ttc']['debit']);
                  verbose_str_to_file(__FILE__, __FUNCTION__, "ss_mode==2 et debit_to_set=$debit_to_set alltotal_cred=$alltotal_cred et alltotal_deb=$alltotal_deb ");

                  if(($debit_to_set>0)||($debit_to_set<0)) {
                    if($debit_to_set<0) {
                      $csv_elem['credit']=$debit_to_set * (-1);
                      $csv_elem['debit']=0;
                    } else {
                      $csv_elem['credit']=0;
                      $csv_elem['debit']=$debit_to_set;
                    }
                    $csv_elem['position']=5;
                    $csv_elem['description']=$desc_gene;
                    $csv_elem['pos_txt']=1;
                    $csv_elem['num_cpt'] = '60970000';
                    $tmp_csv_elem[]=$csv_elem;
                    verbose_str_to_file(__FILE__, __FUNCTION__, "ss_mode==2 et montants et alltotal_cred=$alltotal_cred".print_r($csv_elem,1));
                  }
                }
              }

            } else {

              verbose_str_to_file(__FILE__, __FUNCTION__, "add_elem==1 et montants".print_r($montants,1));
              if(($montants['prix_ht']['debit'] != 0)||($montants['prix_ht']['credit'] !=0)) {
                verbose_str_to_file(__FILE__, __FUNCTION__, "montants['prix_ht']['debit'] != 0)||montants['prix_ht']['credit'] !=0");
                $csv_elem['num_cpt']=get_htcpt_from_tauxtva($activites[$i]['taux_tva'], $activites[$i]['type'], $params_export, $activites[$i], $base, $lib_creditht);
                $csv_elem['add_plancpt']=0;
                $csv_elem['position']=0;
                $csv_elem['pos_txt']='HT';
                $csv_elem['en_banque_uniquement']=$en_banque_uniquement;
                if($activites[$i]['div_ht']>0){
                  if($montants['prix_ht']['debit']>0) {
                    $debit_div_ht= $activites[$i]['div_ht'];
                    $credit_div_ht= 0;
                  } else {
                    $debit_div_ht= 0;
                    $credit_div_ht=$activites[$i]['div_ht'];
                  }
                  $csv_elem['debit']=formater_montant($montants['prix_ht']['debit'] - $debit_div_ht,0,0,0,1);
                  $csv_elem['credit']=formater_montant($montants['prix_ht']['credit'] - $credit_div_ht,0,0,0,1);
                  $tmp_csv_elem[]=$csv_elem;

                  $csv_elem['num_cpt']=$activites[$i]['div_cpt_assoc'];
                  $csv_elem['debit']=$debit_div_ht;
                  $csv_elem['credit']=$credit_div_ht;
                  $tmp_csv_elem[]=$csv_elem;
                } else {
                  if( ($ventiller_ht == 2) || ($ventiller_ht == 3)) {

                    $tmp_csv_elem_frais=array();$tmp_csv_elem_debit=$tmp_csv_elem_credit=0;
                    $key_sup_arr=array(
                      'frais_de_port'=>array('cpt_fp', '70850000', '62410000', $fpor_desc, 'PORT', 2),
                      'ecotax'=>array('cpt_eco', '70850000', '60850000', $eco_desc, 'ECOTAX', 2),
                      'livrets_net'=>array('livrets_net', '70850000', '60850000', $desc_gene,'SUP', 5),
                      'remb_tickets'=>array('remb_tickets', '70850000', '60850000', $desc_gene,'SUP', 5),
                      'petits_lots'=>array('petits_lots', '70850000', '60850000', $desc_gene,'SUP', 5),
                      'commission'=>array('commission', '70850000', '60850000', $desc_gene,'SUP', 5),
                      'remise'=>array('remise', '70850000', '60850000', $desc_gene,'SUP', 5),
                      'consigne'=>array('consigne', '70850000', '60850000', $desc_gene,'SUP', 5),
                      'total_brut'=>array('total_brut', '70850000', '60850000', $desc_gene,'SUP', 5),
                      'mise_encaissee'=>array('mise_encaissee', '70850000', '60850000', $desc_gene,'SUP', 5),
                      'biens_serv'=>array('biens_serv', '70850000', '60850000', $desc_gene,'SUP', 5),
                    );
                    foreach($key_sup_arr as $key_sup => $key_def){
                      verbose_str_to_file(__FILE__, __FUNCTION__, "key_sup_arr pour $key_sup trouve montant ".print_r($montants[$key_sup],1));
                      if(($montants[$key_sup]['debit'] != 0)||($montants[$key_sup]['credit'] !=0)){
                        $frais_csvelem = $csv_elem;

                        if(! Is_empty_str($planfrs[$key_def[0]])) $frais_csvelem['num_cpt']=$planfrs[$key_def[0]];
                        else if(! Is_empty_str($params_export[$key_def[0]])) $frais_csvelem['num_cpt']=$params_export[$key_def[0]];
                        else if($activites[$i]['type']=='encaissement') $frais_csvelem['num_cpt']=$key_def[1];
                        else $frais_csvelem['num_cpt']=$key_def[2];
                        $frais_csvelem['add_plancpt']=0;
                        $frais_csvelem['debit']=formater_montant($montants[$key_sup]['debit'],0,0,0,1);;
                        $tmp_csv_elem_debit += $frais_csvelem['debit'];
                        $frais_csvelem['credit']=formater_montant($montants[$key_sup]['credit'],0,0,0,1);
                        $tmp_csv_elem_credit += $frais_csvelem['credit'];
                        $frais_csvelem['position']=$key_def[5];
                        //$frais_csvelem['description']=$key_def[3];
                        $frais_csvelem['pos_txt']=$key_def[4];
                        $tmp_csv_elem_frais[]=$frais_csvelem;
                      }
                    }

                    verbose_str_to_file(__FILE__, __FUNCTION__, "deduction des frais de ttc de ".formater_montant($montants['prix_ttc']['credit'],0,0,0,1)." deb ". formater_montant($montants['prix_ttc']['debit'],0,0,0,1).print_r($tmp_csv_elem_frais,1));
                    $csv_elem['debit']=formater_montant($montants['prix_ttc']['credit'])-formater_montant($tmp_csv_elem_debit);
                    $csv_elem['credit']=formater_montant($montants['prix_ttc']['debit'])-formater_montant($tmp_csv_elem_credit);

                    $csv_elem['debit']=formater_montant($csv_elem['debit'],0,0,0,1);
                    $csv_elem['credit']=formater_montant($csv_elem['credit'],0,0,0,1);

                    verbose_str_to_file(__FILE__, __FUNCTION__, "apres deduction des frais de ttc de ".$csv_elem['credit']." deb ". $csv_elem['debit'].print_r($tmp_csv_elem_frais,1));


                  } else {
                    $csv_elem['debit']=formater_montant($montants['prix_ht']['debit'],0,0,0,1);
                    $csv_elem['credit']=formater_montant($montants['prix_ht']['credit'],0,0,0,1);
                  }
                  $tmp_csv_elem[]=$csv_elem;

                  verbose_str_to_file(__FILE__, __FUNCTION__, "div_ht'] == '0".print_r($tmp_csv_elem,1));
                }
              }

              verbose_str_to_file(__FILE__, __FUNCTION__, "ventiller_ht=$ventiller_ht pour ".print_r($montants,1));
              if( ($ventiller_ht == 2) || ($ventiller_ht == 3)) {

                if($ventiller_ht == 2){
                  $csv_elem['num_cpt']=$planfrs['tva_ve_intra'];
                } else if($ventiller_ht == 3){
                  $csv_elem['num_cpt']=$planfrs['tva_ve_al'];
                  //if($base_comptable != 'FA2011') $csv_elem['description']='TVA DUE AUTOLIQUIDATION';
                }

                $taux_tva_intra=20;
                if($base == 'FA1592') $taux_tva_intra=10;
                if($base == 'FA1912') $taux_tva_intra=5.5;
                if($base == 'FA5378') $taux_tva_intra=5.5;
                if($base == 'FA5740') $taux_tva_intra=10;
                if(($base == 'FA1915')&&(preg_match('/euro/i',$activites[$i]['detail']))) $taux_tva_intra=5.5;


                $csv_elem['add_plancpt']=0;
                $csv_elem['debit']=formater_montant(($montants['prix_ttc']['debit']*$taux_tva_intra/100),0,0,0,1);;
                $csv_elem['credit']=formater_montant(($montants['prix_ttc']['credit']*$taux_tva_intra/100),0,0,0,1);
                $csv_elem['position']=2;
                $csv_elem['pos_txt']='0';
                $tmp_csv_elem[]=$csv_elem;

                if($ventiller_ht == 2){
                  $csv_elem['num_cpt']=$planfrs['tva_ac_intra'];
                } else if($ventiller_ht == 3){
                  $csv_elem['num_cpt']=$planfrs['tva_ac_al'];
                }

                $csv_elem['add_plancpt']=0;
                $csv_elem['debit']=formater_montant(($montants['prix_ttc']['credit']*$taux_tva_intra/100),0,0,0,1);;
                $csv_elem['credit']=formater_montant(($montants['prix_ttc']['debit']*$taux_tva_intra/100),0,0,0,1);
                $csv_elem['position']=2;
                $csv_elem['pos_txt']='0';

                if( ($csv_elem['debit']>0)||($csv_elem['credit']>0))
                  $tmp_csv_elem[]=$csv_elem;


                foreach($tmp_csv_elem_frais as $frais_csv){
                  if( ($frais_csv['debit']>0)||($frais_csv['credit']>0)) $tmp_csv_elem[]=$frais_csv;
                }

              } else {
                if($soc_infos['soctype']=='medecin') $lib_credit=$lib_creditht;
                verbose_str_to_file(__FILE__, __FUNCTION__, "activite:".print_r($activites[$i],1)." montants:".print_r($montants,1));
                $tva_by_plancpt=0;
                for($it=1;$it<5;$it++){
                  $taux_tvaVal = formater_montant($activites[$i]['taux_tva'.$it]);
                  if(($montants['prix_tva'.$it]['debit'] != 0)||($montants['prix_tva'.$it]['credit'] !=0)||($montants['prix_ht'.$it]['debit'] !=0)||($montants['prix_ht'.$it]['credit'] !=0)){
                    verbose_str_to_file(__FILE__, __FUNCTION__, "taux_tvaVal trouve $taux_tvaVal pour taux_tva$it et prix_tva$it ".$montants['prix_tva'.$it]);
                    if($activites[$i]['type'] == 'encaissement') {
                      $csv_elem['num_cpt']=get_htcpt_from_tauxtva($taux_tvaVal, $activites[$i]['type'], $params_export, $activites[$i], $base, $lib_creditht);
                    } else $csv_elem['num_cpt']=$lib_creditht;

                    $csv_elem['taux']=$taux_tvaVal;
                    $csv_elem['pos_txt']='TVA';
                    if($ventiler_ttc==1){

                      $ttc_vent_cred = $montants['prix_tva'.$it]['debit']+$montants['prix_ht'.$it]['debit'];
                      $ttc_vent_deb = $montants['prix_tva'.$it]['credit']+$montants['prix_ht'.$it]['credit'];

                      $csv_elem['debit']=formater_montant($ttc_vent_deb,0,0,0,1);
                      $csv_elem['credit']=formater_montant($ttc_vent_cred,0,0,0,1);
                      if(preg_match('/march/i',$activites[$i]['description'])) $params_export_f = 'aux_march';
                      else $params_export_f = 'aux_serv';

                      $taux_2_field = array('2.1' =>'021', '2.10' =>'021', '5.5' =>'055', '5.50' =>'055', '8.5' =>'085', '8.50' =>'085', '10' =>'10', '10.00' =>'10', '20' =>'20', '20.00' =>'20');
                      $params_export_f = $params_export_f."_ht_t".$taux_2_field[$taux_tvaVal];
                      verbose_str_to_file(__FILE__, __FUNCTION__, "mode ventiler_ttc ajout ttc params_export_f=$params_export_f val=".$params_export[$params_export_f]);
                      if( ! Is_empty_str($params_export[$params_export_f])) {
                        $csv_elem['num_cpt'] = $params_export[$params_export_f];
                      }
                      $tmp_csv_elem[]=$csv_elem;
                      verbose_str_to_file(__FILE__, __FUNCTION__, "mode ventiler_ttc ajout ttc ".print_r($csv_elem,1));
                    }

                    if(($montants['prix_ht'.$it]['debit'] !=0)||($montants['prix_ht'.$it]['credit'] !=0)){
                      $csv_elem['num_cpt']=get_htcpt_from_tauxtva($taux_tvaVal, $activites[$i]['type'], $params_export, $activites[$i], $base, $lib_creditht);

                      $csv_elem['add_plancpt']=0;
                      if($export_mode==26)$csv_elem['position']=4;
                      else $csv_elem['position']=0;
                      $csv_elem['debit']=formater_montant($montants['prix_ht'.$it]['debit'],0,0,0,1);
                      $csv_elem['credit']=formater_montant($montants['prix_ht'.$it]['credit'],0,0,0,1);
                      $tmp_csv_elem[]=$csv_elem;
                    }
                    if(($montants['prix_tva'.$it]['debit'] !=0)||($montants['prix_tva'.$it]['credit'] !=0)){

                      if(preg_match('/^\s*6|7/', $tmp_csv_elem[count($tmp_csv_elem)-1]['num_cpt'])){

                        $tmp_csv_elem[count($tmp_csv_elem)-1]['code_tva'] = get_tvacode($activites[$i]['taux_tva'.$it], $activites[$i]['type'], $CHARGES_SOCIETE_TO_CPT, $lib_creditht, $activites[$i], $base, $params_export);
                        if( ($base_comptable != 'FA0671')&&($base_comptable != 'FA1074') ) $csv_elem['code_tva']=$tmp_csv_elem[count($tmp_csv_elem)-1]['code_tva'];
                        //verbose_str_to_file(__FILE__, __FUNCTION__, "mode ajout code_tva ".print_r($tmp_csv_elem,1));
                      }
                      if($export_mode==26)$csv_elem['position']=4;

                      $csv_elem['num_cpt']=get_tvacpt($activites[$i]['taux_tva'.$it], $activites[$i]['type'], $CHARGES_SOCIETE_TO_CPT, $lib_creditht, $activites[$i], $base, $params_export);

                      $csv_elem['add_plancpt']=0;
                      $csv_elem['debit']=formater_montant($montants['prix_tva'.$it]['debit'],0,0,0,1);
                      $csv_elem['credit']=formater_montant($montants['prix_tva'.$it]['credit'],0,0,0,1);
                      $tmp_csv_elem[]=$csv_elem;
                      $tva_by_plancpt=1;
                    }

                  }
                }

                if($tva_by_plancpt==0){
                  if((formater_montant($debit_valtva)>0)||(formater_montant($credit_valtva)>0)){

                    if(preg_match('/^\s*6|7/', $tmp_csv_elem[count($tmp_csv_elem)-1]['num_cpt'])){
                      $tmp_csv_elem[count($tmp_csv_elem)-1]['code_tva'] = get_tvacode($activites[$i]['taux_tva'.$it], $activites[$i]['type'], $CHARGES_SOCIETE_TO_CPT, $lib_creditht, $activites[$i], $base, $params_export);
                      if( ($base_comptable != 'FA0671')&&($base_comptable != 'FA1074') )  $csv_elem['code_tva'] =$tmp_csv_elem[count($tmp_csv_elem)-1]['code_tva'];
                    }
                    $csv_elem['num_cpt']=get_tvacpt($activites[$i]['taux_tva'], $activites[$i]['type'], $CHARGES_SOCIETE_TO_CPT, $lib_creditht, $activites[$i], $base, $params_export);
                    $csv_elem['add_plancpt']=0;
                    $csv_elem['debit']=formater_montant($montants['prix_tva']['debit'],0,0,0,1);
                    $csv_elem['credit']=formater_montant($montants['prix_tva']['credit'],0,0,0,1);
                    if( ($csv_elem['debit']>0)|| ($csv_elem['credit']>0)) $tmp_csv_elem[]=$csv_elem;
                  }
                }
                //if($base_comptable == 'FA2011')
                $eco_desc=$desc_gene;
                //else $eco_desc='ECO PARTICIPATION';
                //if($base_comptable == 'FA2011')
                $fpor_desc=$desc_gene;
                //else $fpor_desc='FRAIS DE PORT';

                $key_sup_arr=array(
                  'frais_de_port'=>array('cpt_fp', '70850000', '62410000', $fpor_desc, 'PORT', 2),
                  'ecotax'=>array('cpt_eco', '70850000', '60850000', $eco_desc, 'ECOTAX', 2),
                  'livrets_net'=>array('livrets_net', '70850000', '60850000', $desc_gene,'SUP', 5),
                  'remb_tickets'=>array('remb_tickets', '70850000', '60850000', $desc_gene,'SUP', 5),
                  'petits_lots'=>array('petits_lots', '70850000', '60850000', $desc_gene,'SUP', 5),
                  'commission'=>array('commission', '70850000', '60850000', $desc_gene,'SUP', 5),
                  'remise'=>array('remise', '70850000', '60850000', $desc_gene,'SUP', 5),
                  'consigne'=>array('consigne', '70850000', '60850000', $desc_gene,'SUP', 5),
                  'total_brut'=>array('total_brut', '70850000', '60850000', $desc_gene,'SUP', 5),
                  'mise_encaissee'=>array('mise_encaissee', '70850000', '60850000', $desc_gene,'SUP', 5),
                  'biens_serv'=>array('biens_serv', '70850000', '60850000', $desc_gene,'SUP', 5),
                );
                foreach($key_sup_arr as $key_sup => $key_def){
                  verbose_str_to_file(__FILE__, __FUNCTION__, "key_sup_arr pour $key_sup trouve montant ".print_r($montants[$key_sup],1));
                  if(($montants[$key_sup]['debit'] != 0)||($montants[$key_sup]['credit'] !=0)){
                    if(! Is_empty_str($planfrs[$key_def[0]])) $csv_elem['num_cpt']=$planfrs[$key_def[0]];
                    else if(! Is_empty_str($params_export[$key_def[0]])) $csv_elem['num_cpt']=$params_export[$key_def[0]];
                    else if($activites[$i]['type']=='encaissement') $csv_elem['num_cpt']=$key_def[1];
                    else $csv_elem['num_cpt']=$key_def[2];
                    $csv_elem['add_plancpt']=0;
                    $csv_elem['debit']=formater_montant($montants[$key_sup]['debit'],0,0,0,1);;
                    $csv_elem['credit']=formater_montant($montants[$key_sup]['credit'],0,0,0,1);
                    $csv_elem['position']=$key_def[5];
                    $csv_elem['description']=$key_def[3];
                    $csv_elem['pos_txt']=$key_def[4];
                    $tmp_csv_elem[]=$csv_elem;
                    $csv_elem['position']=0;
                    $csv_elem['description']=$desc_gene;
                  }
                }
              }
            }
          }
          verbose_str_to_file(__FILE__, __FUNCTION__, "all after csv_elem ".print_r($tmp_csv_elem, 1));
        }
        if($export_mode==26) $tmp_csv_elem[0]['position']=1;
      }

      verbose_str_to_file(__FILE__, __FUNCTION__, "tmp_csv avant ajout a all_csv_elem avec lib_credit $lib_credit".print_r($tmp_csv_elem, 1));
      if(count($tmp_csv_elem)>0) {
        for($it=0; $it<count($tmp_csv_elem);$it++){
          $tmp_csv_elem[$it]['ged_dir']=build_ged_dir_date($tmp_csv_elem[$it]['date'], $params_export['fin_exercice'], $params_export['debut_exercice'], $export_mode);
          $tmp_csv_elem[$it]['path_pj_ext']=$path_pj_ext;
        }
        $all_csv_elem = array_merge($all_csv_elem, $tmp_csv_elem);
      }
      verbose_str_to_file(__FILE__, __FUNCTION__, "all_csv_elem apres array_merge".print_r($all_csv_elem, 1));

      if(($type=='frais')&&(sizeof($activites)>0)) {

        if(($type == 'frais')&&( ! ($ndf_as_chg>0))) {
          $csv_elem['description']='NOTES DE FRAIS';
          $csv_elem['path_pj']=null;

          if(($export_mode == 1)||($export_mode==4)) $csv_elem['num_cpt']=445660;
          else $csv_elem['num_cpt']=44566000;

          $csv_elem['debit']=formater_montant(($total_ttc-$total_ht),0,0,0,1);
          $csv_elem['credit']=0;
          $csv_elem['cpt_lib']=$csv_elem['num_cpt'];
          if( ($csv_elem['debit']>0)|| ($csv_elem['credit']>0)) $all_csv_elem[]=$csv_elem;
          verbose_str_to_file(__FILE__, __FUNCTION__, "frais totaux pour NDF total_ttc=$total_ttc total_ht=$total_ht".print_r($csv_elem, 1));

          if(($export_mode == 1)||($export_mode==4)) $csv_elem['num_cpt']=423000;
          else $csv_elem['num_cpt']=$lib_credit;

          if($soc_infos['soctype']=='medecin') $lib_credit=$csv_elem['num_cpt'];

          $csv_elem['debit']=0;
          $csv_elem['credit']=formater_montant($total_ttc,0,0,0,1);
          $csv_elem['cpt_lib']=$csv_elem['num_cpt'];

          if( ($csv_elem['debit']>0)|| ($csv_elem['credit']>0)) $all_csv_elem[]=$csv_elem;
          verbose_str_to_file(__FILE__, __FUNCTION__, "all_csv_elem apres frais et ndf_as_chg=$ndf_as_chg".print_r($all_csv_elem, 1));
        }
      }
    }
  }

	$all_csv_elem = tronquer_numero_comptes($all_csv_elem, $export_mode, $params_export, $soc_infos['comptable']);
	verbose_str_to_file(__FILE__, __FUNCTION__, "return all_csv_elem avec lib_credit $lib_credit".print_r($all_csv_elem, 1));
	if(count($all_csv_elem)>0){
		$all_csv_elem[0]['debut_exercice']=date_mysql_to_html($params_export['debut_exercice'],0);
		$all_csv_elem[0]['fin_exercice']=date_mysql_to_html($params_export['fin_exercice'],0);
    $all_csv_elem[0]['base']= $base;
    if($soc_infos['comptable'] == 'FA1975') $all_csv_elem[0]['nolibel_mvt']= 1;
	}

	return array($all_csv_elem,$lib_credit, $cur_idx, $tar_bq_list);
}


function check_position1_csvelem($params_export, $activite, $export_mode) {

  $idx_ttc=1;$max_val=0;
  for($idx=1; $idx<20; $idx++) {
    $val=formater_montant(formater_montant($post_protect["debit".$idx])+formater_montant($post_protect["credit".$idx]));
    if( ($val>$max_val) && ( ! preg_match('/^\s*(6|7|44)/', $post_protect["num_cpt".$idx] ))) {
      $idx_ttc=$idx;
      $max_val=$val;
    }
  }
  for($idx=1; $idx<20; $idx++) {
    if($idx==$idx_ttc) $post_protect["position".$idx]=1;
    else $post_protect["position".$idx]=2;
  }

}

function get_code_j($params_export, $activite, $export_mode) {

  $type=$activite['type'];

  if( ($type=='encaissement') && ($activite['id_caisse']>0) ){
    $key_p='jr_caisse';
    $code_j = 'CA';
  } else if($type=='encaissement') {
    $key_p='journal_ve';
    $code_j = 'VE';
  } else {
    $key_p='journal_ac';
    $code_j = 'AC';
  }

	if( ! Is_empty_str($params_export[$key_p])) $code_j = $params_export[$key_p];

	return $code_j;
}

function search_csv_elem_in_base($appliname, $type, $id_type, $autre_chp){

	$verboseController = new VerboseController('societe', $appliname);
	$condition = array();
	if( Is_empty_str($autre_chp))$condition['type_element']=$type;
	else $condition[$autre_chp]=$type;

	$condition['id_type']=$id_type;
	$db_csv_elem = $verboseController->searchConditionFromTable('csv_elems', $condition);
  mysqli_close($verboseController);

  verbose_str_to_file(__FILE__, __FUNCTION__, "db_csv_elem en base".print_r($db_csv_elem,1));

  $idx_ttc=1;$max_val=0;
  for($idx=0; $idx<20; $idx++) {
    if(!Is_empty_str($db_csv_elem[$idx]['num_fact'])) $db_csv_elem[$idx]['num_fact'] = clean_file_name($db_csv_elem[$idx]['num_fact'],0,0);
    $csv_elem = $db_csv_elem[$idx];
    $val=formater_montant(formater_montant($csv_elem["debit"])+formater_montant($csv_elem["credit"]));
    if( ($val>$max_val) && ( ! preg_match('/^\s*(6|7|44)/', $csv_elem["num_cpt"] ))) {
      $idx_ttc=$idx;
      $max_val=$val;
    }
  }

	return 	$db_csv_elem;
}

function csv_elem_from_base($appliname, $type, $id_type, $path_pj, $list_liens, $export_mode, $banq_code_lib, $ref, $cpt_general, $params_export) {

	verbose_str_to_file(__FILE__, __FUNCTION__, "$appliname, $type, $id_type en base".print_r($list_liens,1).print_r($path_pj,1));
  $all_csv_elem=array();
	$idx=0;
	$csv_elem_type='M';
	if($export_mode==20) $csv_elem_type='1';
	$db_csv_elem = search_csv_elem_in_base($appliname, $type, $id_type);

	if( count($db_csv_elem) > 1) {
    verbose_str_to_file(__FILE__, __FUNCTION__, "$appliname, $type, $id_type nb= ".count($db_csv_elem));
		foreach($db_csv_elem as $db_elem) {
			$idx++;
			$csv_elem=$db_elem;
			$csv_elem['date'] = date_mysql_to_html($csv_elem['date']);
			if(! Is_empty_str($csv_elem['date_echeance'],1))$csv_elem['date_echeance']=date_mysql_to_html($csv_elem['date_echeance']);
			$csv_elem['path_pj']=$path_pj;
      if(Is_empty_str($csv_elem['cpt_general'])) $csv_elem['cpt_general']=$cpt_general;
			$csv_elem['type']=$csv_elem_type;
			$csv_elem['code_lib']=$banq_code_lib;
			if( Is_empty_str($csv_elem['num_fact']) ){
				if( ! Is_empty_str($ref) ) $csv_elem['num_fact']=$ref;
				else $csv_elem['num_fact']=$csv_elem['num_piece'];
			}

      //$csv_elem['debut_exercice']
      //$csv_elem['fin_exercice']

			$csv_elem['list_liens']=$list_liens;
			$csv_elem['debit']=formater_montant($csv_elem['debit']);
			$csv_elem['credit']=formater_montant($csv_elem['credit']);
			if(($csv_elem['credit']>0)||($csv_elem['debit']>0)) $all_csv_elem[]=$csv_elem;

			if( ($export_mode==20) && ($idx>1)) $csv_elem_type='2';
		}
	}

  verbose_str_to_file(__FILE__, __FUNCTION__, "return for $appliname, $type, $id_type en base ".print_r($all_csv_elem,1));
	return $all_csv_elem;
}

function get_htcpt_from_tauxtva($taux_tva, $type, $params_export, $activites, $base, $lib_creditht) {

  list($ventiller_ht, $planfrs) = get_ventiller_ht($activites, $base, $params_export);
  verbose_str_to_file(__FILE__, __FUNCTION__, "ventiller_ht=$ventiller_ht taux=$taux_tva  planfrs ".print_r($planfrs, 1)." et params_export ".print_r($params_export, 1));

	$cpt=$lib_creditht;
  if(Is_empty_str($taux_tva)||($taux_tva==0)) $taux_tva='00';
  $taux_2_field = array('00' =>'00', '2.1' =>'021', '2.10' =>'021', '5.5' =>'055', '5.50' =>'055', '8.5' =>'085', '8.50' =>'085', '10' =>'10', '10.00' =>'10', '20' =>'20', '20.00' =>'20');
  $planfrs_f = 'ac_ht_t'.$taux_2_field["$taux_tva"];
  $params_export_f = $planfrs_f;
  if( ($type=='encaissement')||($type=='facture') ) {
    if(preg_match('/PRESTA/i',$activites['description'])) $params_export_f = 've_serv_ht_t'.$taux_2_field[$taux_tva];
    else $params_export_f = 've_march_ht_t'.$taux_2_field[$taux_tva];
  }

  if( ! Is_empty_str($planfrs[$planfrs_f])) {
    $cpt=$planfrs[$planfrs_f];
    verbose_str_to_file(__FILE__, __FUNCTION__, "return $cpt de planfrs_f $planfrs_f");
  } else if( ! Is_empty_str($planfrs['cpt_assoc'])){
    $cpt=$planfrs['cpt_assoc'];
    verbose_str_to_file(__FILE__, __FUNCTION__, "return $cpt de planfrs cpt_assoc");
  } else if( ! Is_empty_str($params_export[$params_export_f])){
    $cpt=$params_export[$params_export_f];
    verbose_str_to_file(__FILE__, __FUNCTION__, "return $cpt de params_export_f $params_export_f");
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "return $cpt pour taux_tva $taux_tva et type=$type planfrs_f=$planfrs_f params_export_f=$params_export_f");
	return $cpt;
}

//ALTER TABLE `plancomptable` CHANGE `compte` `compte` VARCHAR(30) NULL DEFAULT NULL;

function tronquer_numero_comptes($all_csv_elem, $export_mode, $params_export, $comptable) {

	verbose_str_to_file(__FILE__, __FUNCTION__, "recu".print_r($all_csv_elem, 1));
	$res_all_csv = array();

	global $dbConfig;
  $longeur=formater_montant($params_export['long_cpt']);
  $longeur_aux=formater_montant($params_export['long_aux']);
  $longeur_aux_ve=formater_montant($params_export['long_aux_ve']);

	foreach($all_csv_elem as $csv_elem){

    verbose_str_to_file(__FILE__, __FUNCTION__, "csv_elem ".print_r($csv_elem, 1));

		if( ! ($longeur >0)) {
			$longeur=8;
      if($csv_elem['position']==1){
        if(($csv_elem['position']==1)&&($export_mode==5)) $longeur=7;
        else if(($csv_elem['position']==1)&&($export_mode==32)) $longeur=10;
        else if(($csv_elem['position']==1)&&((($export_mode==20)||($export_mode==25)||($export_mode==26)))) $longeur=14;

        else if(($csv_elem['position']==1)&&($export_mode==13)) {
          if(($csv_elem['cpt_lib'] == '46710011')||($csv_elem['cpt_lib'] == '46710010'))$longeur=8;
          else $longeur=7;
        }
      }

      if($csv_elem['position'] != 1){
			  if(($export_mode==2)||($export_mode==13)||($export_mode==14)||($export_mode==17)||($export_mode==-1)) $longeur=8;
			  else if(($export_mode==25)||($export_mode==26)) $longeur=6;
			  else if(($export_mode==8)) $longeur=8;
      }
		}

		$car_pad=" ";
    if($dbConfig['db']=='FA0604') $car_pad="0";
    if($dbConfig['db']=='FA1302') $car_pad="0";

		verbose_str_to_file(__FILE__, __FUNCTION__, "longeur = $longeur ");
    if($dbConfig['db']=='FA0472') $longeur=3;

		if($export_mode != 18){
			if( ($csv_elem['position'] != 1)&&( ! preg_match('/[A-Z]/', $csv_elem['num_cpt'])) && ( ! preg_match('/[a-z]/', $csv_elem['num_cpt'])) )
        $csv_elem['num_cpt'] = substr(str_pad(clean_file_name($csv_elem['num_cpt'],0,0), $longeur, $car_pad, STR_PAD_RIGHT),0,$longeur);
		}
    if( ($csv_elem['position'] == 1)&&( $longeur_aux_ve >0) && (($csv_elem['type_element'] == 'facture')||($csv_elem['type_element'] == 'encaissement')) ){
      $csv_elem['num_cpt'] = substr(str_pad(clean_file_name($csv_elem['num_cpt'],0,0), $longeur_aux_ve, $car_pad, STR_PAD_RIGHT),0,$longeur_aux_ve);
    }
		else if( ($csv_elem['position'] == 1)&&( $longeur_aux >0) ){
      $csv_elem['num_cpt'] = substr(str_pad(clean_file_name($csv_elem['num_cpt'],0,0), $longeur_aux, $car_pad, STR_PAD_RIGHT),0,$longeur_aux);
    }
    if( ($comptable != 'FA0362')&&($comptable != 'FA1609') ) $csv_elem['num_cpt'] = strtoupper($csv_elem['num_cpt']);

		$longeur_desc=formater_montant($params_export['long_libelle']);
    verbose_str_to_file(__FILE__, __FUNCTION__, "longeur_desc = $longeur_desc ");
		if( ! ($longeur_desc >0)) $longeur_desc=30;
		$comp=" ";
		if(($export_mode==2)||($export_mode==13)||($export_mode==14)||($export_mode==17))
			$csv_elem['description'] = strtoupper(substr(str_pad(clean_file_name($csv_elem['description'],1,1), $longeur_desc, $comp, STR_PAD_RIGHT),0,$longeur_desc));
		else
			$csv_elem['description'] = strtoupper(substr(clean_file_name($csv_elem['description'],1,1),0,$longeur_desc));

    verbose_str_to_file(__FILE__, __FUNCTION__, "longeur_desc = $longeur_desc ");
		$res_all_csv[] = $csv_elem;

    verbose_str_to_file(__FILE__, __FUNCTION__, "1 ".print_r($csv_elem,1));

	}
  verbose_str_to_file(__FILE__, __FUNCTION__, "fin boucle ");

	return $res_all_csv;
}

function associated_charges_tocsv_list($associated_charges, $force) {

	$tmpArr=$chg_elems=$avoir_elems=$frais_elems=$fact_elems=array();
	foreach ($associated_charges as $frais) {
		$todo=0;
		if($force==1) $todo=1;
		else if( ! ($frais['exported'] > 0)) $todo=1;
		if( $todo==1) {
			if(($frais['type']=='facture')){
				$fact_elems[] = $frais;
      } else if($frais['type']=='avoir'){
        $avoir_elems[] = $frais;
      } else if($frais['type']=='versement'){
        1;// ne pas les prendre dans l export
			} else if($frais['type']=='frais') {
				$frais_elems[] = $frais;
			} else {
				$chg_elems[] = $frais;
			}
		}
	}
	foreach ($frais_elems as $frais) {
		list($d_cur,$m_cur,$y_cur) = date_mysql_to_html($frais['created_at'], 0, 1);
		//$view_controller = new VerboseController();
		//$mana = $view_controller->get($frais['id_manager'], 'manager');
		//$tmpArr[$y_cur][$m_cur][$mana['last_name']." ".$mana['first_name']][]=$frais;
		$tmpArr[$y_cur][$m_cur][]=$frais;
	}

	//verbose_str_to_file(__FILE__, __FUNCTION__, "get associated_charges".print_r($associated_charges, 1));
	//verbose_str_to_file(__FILE__, __FUNCTION__, "get fact_elems".print_r($fact_elems, 1));
	//verbose_str_to_file(__FILE__, __FUNCTION__, "get chg".print_r($chg_elems, 1));
	//verbose_str_to_file(__FILE__, __FUNCTION__, "get frais".print_r($tmpArr, 1));

	return array($fact_elems, $tmpArr, $chg_elems, $avoir_elems);
}

function fichier_src($file_name, $noextension, $dir_src) {

	if(preg_match('/(_[0-9]+)(\.\w+)\s*$/', $file_name, $matches)) {
		verbose_str_to_file(__FILE__, __FUNCTION__, "get matches".print_r($matches, 1));
		if($noextension==1) $matches[2]='';
		$ret_name = preg_replace('/(_[0-9]+)(\.\w+)\s*$/', $matches[2], $file_name);
	} else if(preg_match('/(\.[0-9]+)(\.\w+)\s*$/', $file_name, $matches)) {
		if($noextension==1) $matches[2]='';
		$ret_name = preg_replace('/(\.[0-9]+)(\.\w+)\s*$/', $matches[2], $file_name);
	}
	else $ret_name = $file_name;
  verbose_str_to_file(__FILE__, __FUNCTION__, "pour $dir_src/$file_name => fichier src = $ret_name size src= ".filesize($dir_src.$ret_name));

  if( ! (filesize($dir_src.$ret_name)>0) ) {
    $tmp_arr=pathinfo($ret_name);
    $tmp_name =  $tmp_arr['filename'].".".strtolower($tmp_arr['extension']);
    verbose_str_to_file(__FILE__, __FUNCTION__, "recherche avec $tmp_name");
    if(filesize($dir_src.$tmp_name)>0) $ret_name=$tmp_name;
    else {
      $tmp_name =  $tmp_arr['filename'].".".strtoupper($tmp_arr['extension']);
      verbose_str_to_file(__FILE__, __FUNCTION__, "recherche avec $tmp_name");
      if(filesize($dir_src.$tmp_name)>0) $ret_name=$tmp_name;
      else {
        verbose_str_to_file(__FILE__, __FUNCTION__, "recherche avec origin");
        list($output, $status) = launch_system_command("find $dir_src".' -type f -name "'.$tmp_arr['filename'].'origin_*"');
        if(filesize($output[0])>0){
          $tmp_arr = pathinfo($output[0]);
          $ret_name=$tmp_arr['basename'];
        } else  $ret_name = $file_name;
      }
    }
  }

	verbose_str_to_file(__FILE__, __FUNCTION__, "fichier source de  $file_name = $ret_name");

	return $ret_name;
}

function build_extension($file_path){

	$file_name_ext = pathinfo($file_path);
	$extension="";
	if($file_name_ext['extension'] == "") $extension="png";
	else $extension=$file_name_ext['extension'];
	$extension=strtoupper(substr($extension,0,3));
	if($extension=='JPE') $extension='JPG';

	return $extension;
}

//sur8
//  } else if(($mode==2)||($mode==13)||($mode==14)||($mode==17)||($mode==31)){
//		} else if(($mode==32)){
function build_csv_entry_pdf_name($export_mode, $base, $type, $elem_id) {
  $ref="";

  $tmp_csv_elem = csv_elem_from_base($base, $type, $elem_id);
  verbose_str_to_file(__FILE__, __FUNCTION__, 'get export_mode=$export_mode base=$base type=$type elem_or_id' . print_r($tmp_csv_elem, 1));

  if(count($tmp_csv_elem)>1) {
    if(($type=='facture')||($type=='encaissement')) {
      $ref=$tmp_csv_elem[0]['num_fact'];
    }
    $date=date_html_to_mysql($tmp_csv_elem[0]['date']);
  }else {
    $verboseController = new VerboseController('societe', $base);
    $fact_infos = $verboseController->get($elem_id, $type);
    mysqli_close($verboseController);
    if($type=='facture') {
      $ref=$fact_infos['reference'];
      $date=$fact_infos['date_f'];
    } else {
      if($type=='encaissement') $ref=$fact_infos['num_fact'];
      $date=$fact_infos['created_at'];
    }
  }
  list($d_entree,$m_entree,$y_entree) = date_mysql_to_html($date, 0, 1);
  $y_entree=substr($y_entree,2,2);
  if(strlen($ref)>8) $ref=substr($ref,strlen($ref)-8,8);

  if(($export_mode==36)||($export_mode==1)||($export_mode==41)) {
    if(Is_empty_str($ref)) {
      if(($base=='FA3133')||($base=='FA3132')||($base=='FA0002X')){
        $str_elem_id=str_pad($elem_id, 3, "0",STR_PAD_LEFT);
        if(strlen($str_elem_id)>3) $str_elem_id=substr($str_elem_id,strlen($str_elem_id)-3,3);
        if($base=='FA3132') $str_elem_id="S$str_elem_id";
        else $str_elem_id="N$str_elem_id";
      } else {
        $str_elem_id=str_pad($elem_id, 4, "0",STR_PAD_LEFT);
        if(strlen($str_elem_id)>4) $str_elem_id=substr($str_elem_id,strlen($str_elem_id)-4,4);
      }
      $ref = str_pad($y_entree, 2, "0", STR_PAD_LEFT).str_pad($m_entree, 2, "0", STR_PAD_LEFT).$str_elem_id;
    }
  } else {
    if( ( ($type=='facture')||($type=='encaissement') ) && (! Is_empty_str($ref)) ) 1;
    else {
      $str_elem_id=str_pad($elem_id, 3, "0",STR_PAD_LEFT);
      if(strlen($str_elem_id)>3) $str_elem_id=substr($str_elem_id,strlen($str_elem_id)-3,3);
      $ref = str_pad($y_entree, 2, "0", STR_PAD_LEFT).str_pad($m_entree, 2, "0", STR_PAD_LEFT).strtoupper(substr($type,0,1)).$str_elem_id;
    }
  }

  $ref = preg_replace('/^\s*/', '', $ref);
  $ref = preg_replace('/\s*$/', '', $ref);
  $pdf_path = "$ref.PDF";
  if($base=='FA6696') $pdf_path="J$pdf_path";

  return $pdf_path;

}

function pj_to_csv_entry($elem_or_id, $type, $zip_dir, $cur_idx, $tar_bq_list, $base, $params_export, $export_mode) {
	$list_pj = $list_liens=array();
	$tar_cmd = "";
	if( ! ($cur_idx>0)) $cur_idx=0;
	$cur_idx = $cur_idx+1;
	verbose_str_to_file(__FILE__, __FUNCTION__, "export_mode=$export_mode for get elem_or_id".print_r($elem_or_id, 1));
	global $dbConfig;
	if(Is_empty_str($base)) $base=$dbConfig["db"];
	if(($type == 'facture')||($type == 'avoir')){

		if(is_array($elem_or_id) && ($elem_or_id['id']>0)) $elem_or_id=$elem_or_id['id'];
		$pdf_path=build_csv_entry_pdf_name($export_mode, $base, $type, $elem_or_id);
		verbose_str_to_file(__FILE__, __FUNCTION__, "ok pdf_path $zip_dir/$pdf_path");

		$link_path = 'upload/clients/';
		$dir_path = dirname(__FILE__)."/../../$base/".$link_path;
    $clientController = new VerboseController('societe', $base);
		$facture_infos = $clientController->get($elem_or_id, $type);
    mysqli_close($clientController);

		$list_pj[] = $pdf_path;
    if( ! Is_empty_str($facture_infos['file_'.$type])) {
      if( ($export_mode != 20)&&($export_mode != 37)) launch_system_command("cp $dir_path".$facture_infos['file_'.$type]." $zip_dir/".$pdf_path, 0, 1);
      $list_liens[] = "http://ns314190.ip-37-187-25.eu/$base/$link_path".$facture_infos['file_'.$type];
    }
	} else if($type == 'versement'){
		if(is_array($elem_or_id) && ($elem_or_id['id']>0)) $elem_or_id=$elem_or_id['id'];
		list($pdf, $pdf_path) = generatePDF($elem_or_id, 'versement', null, $elem_or_id, null, null, null, $base);
		$pdf_path=build_csv_entry_pdf_name($export_mode, $base, 'versement', $elem_or_id);
		if( ($export_mode != 20)&&($export_mode != 37)) $pdf->Output("$zip_dir/$pdf_path");
		$list_pj[] = $pdf_path;
		$pdf->Output(dirname(__FILE__)."/../../$base/upload/encaissement/$pdf_path");
		if(is_file(dirname(__FILE__)."/../../$base/upload/encaissement/$pdf_path")) {
      if( ! Is_empty_str($pdf_path)) 	$list_liens[] = "http://ns314190.ip-37-187-25.eu/$base/upload/encaissement/$pdf_path";
      pdf_to_image(dirname(__FILE__)."/../../$base/upload/encaissement/$pdf_path");
    }

	} else {
		$pieces = get_attached_files($elem_or_id, null, 1, $base);
		if(count($pieces)==1){
			$file = $pieces[0];

      $mime1=mime_content_type($file['path']);
			$convert_err=0;
			if( preg_match('/pdf/', $mime1)) {
        $extension = build_extension($file['path']);
        $pdf_path=build_csv_entry_pdf_name($export_mode, $base, $elem_or_id['type'], $elem_or_id['id']);
        $pdf_path = preg_replace('/PDF\s*$/', $extension, $pdf_path);
        $list_pj[] = $pdf_path;
        if( ($export_mode != 20)&&($export_mode != 37)) launch_system_command("cp ".$file['path']." $zip_dir/".$pdf_path, 0, 1);
        if(is_file(dirname(__FILE__)."/../../$base/upload/".$elem_or_id['type']."/".$file['name_disque']))
          if( ! Is_empty_str($file['name_disque'])) 	$list_liens[] = "http://ns314190.ip-37-187-25.eu/$base/upload/".$elem_or_id['type']."/".$file['name_disque'];
      } else {
        $pdf_path=build_csv_entry_pdf_name($export_mode, $base, $elem_or_id['type'], $elem_or_id['id']);

        $status=1;
				if( ($export_mode != 20)&&($export_mode != 37)) {
          if(filesize($file['path'])<2000000) list($output, $status) = launch_system_command("/usr/bin/convert -density 300 ".$file['path']." $zip_dir/$pdf_path", 0, 1);
          else $status=1;
        }
        verbose_str_to_file(__FILE__, __FUNCTION__, "test same size ".filesize($file['path'])." et ".filesize("$zip_dir/$pdf_path"));
        if(filesize($file['path']) == filesize("$zip_dir/$pdf_path")) {
          $status=1;
          verbose_str_to_file(__FILE__, __FUNCTION__, "same size, conversion failed");
        }

				if($status==0){
					$list_pj[] = $pdf_path;
          //if( ($export_mode != 20)&&($export_mode != 37)) launch_system_command("cp ".$file['path']." $zip_dir/".$pdf_path, 0, 1);
					if(is_file(dirname(__FILE__)."/../../$base/upload/".$elem_or_id['type']."/".$file['name_disque']))
            if( ! Is_empty_str($file['name_disque'])) 	$list_liens[] = "http://ns314190.ip-37-187-25.eu/$base/upload/".$elem_or_id['type']."/".$file['name_disque'];
				} else {
          $extension = build_extension($file['path']);
          $pdf_path=build_csv_entry_pdf_name($export_mode, $base, $elem_or_id['type'], $elem_or_id['id']);
          $pdf_path = preg_replace('/PDF\s*$/', $extension, $pdf_path);
          $list_pj[] = $pdf_path;
          if( ($export_mode != 20)&&($export_mode != 37)) launch_system_command("cp ".$file['path']." $zip_dir/".$pdf_path, 0, 1);
          if(is_file(dirname(__FILE__)."/../../$base/upload/".$elem_or_id['type']."/".$file['name_disque']))
            if( ! Is_empty_str($file['name_disque'])) 	$list_liens[] = "http://ns314190.ip-37-187-25.eu/$base/upload/".$elem_or_id['type']."/".$file['name_disque'];

				}
			}
		} else {
			$file = $pieces[0];
			$mime1=mime_content_type($file['path']);
			//$cur_idx = $cur_idx+1;
			$pdf_path = "A".str_pad($cur_idx, 7, "0",STR_PAD_LEFT).".PDF";
			$convert_err=0;

			if( preg_match('/pdf/', $mime1)) {
				if( ($export_mode != 20)&&($export_mode != 37)) launch_system_command("cp ".$file['path']." $zip_dir/".$pdf_path, 0, 1);
				$list_pj[] = $pdf_path;
				if(is_file(dirname(__FILE__)."/../../$base/upload/".$elem_or_id['type']."/".$file['name_disque']))
          if( ! Is_empty_str($file['name_disque'])) 	$list_liens[] = "http://ns314190.ip-37-187-25.eu/$base/upload/".$elem_or_id['type']."/".$file['name_disque'];
			} else {
				if( ($export_mode != 20)&&($export_mode != 37)) {
          if(filesize($file['path'])<2000000) list($output, $status) = launch_system_command("convert -density 300 ".$file['path']." $zip_dir/$pdf_path", 0, 1);
          else $status=1;
        }

				if($status==0){
					$list_pj[] = $pdf_path;
					if(is_file(dirname(__FILE__)."/../../$base/upload/".$elem_or_id['type']."/".$file['name_disque']))
            if( ! Is_empty_str($file['name_disque'])) 	$list_liens[] = "http://ns314190.ip-37-187-25.eu/$base/upload/".$elem_or_id['type']."/".$file['name_disque'];
				} else {
					$convert_err=1;
				}
			}

			if($convert_err==0){
				$merge_cmd="php ".dirname(__FILE__)."/launch_general_action.php merge_pdf $zip_dir/$pdf_path";
				if(is_file(dirname(__FILE__)."/../../$base/upload/".$elem_or_id['type']."/".$pieces[0]['name_disque']))
					if( ! Is_empty_str($pieces[0]['name_disque'])) $list_liens[] = "http://ns314190.ip-37-187-25.eu/$base/upload/".$elem_or_id['type']."/".$pieces[0]['name_disque'];
				for($idx=1;$idx<count($pieces);$idx++){
					$merge_cmd .= " ".$pieces[$idx]['path'];
				}
				if( ($export_mode != 20)&&($export_mode != 37)) list($output, $status) = launch_system_command($merge_cmd, 0, 1);
				if($status==0){
					rm_file($zip_dir."/merged*");
					rm_file($zip_dir."/origin*");
					$tar_bq_list .= " ".$list_pj[0];

          pdf_to_image(dirname(__FILE__)."/../../$base/upload/".$elem_or_id['type']."/".$file['name_disque']);
				} else {
					$cur_idx = $cur_idx-1;
					if( ($export_mode != 20)&&($export_mode != 37)) list($list_pj, $cur_idx,$tar_bq_list) = pj_to_csv_entry_no_merge($elem_or_id, $type, $zip_dir, $cur_idx, $tar_bq_list, $base);
				}
			} else {
				$cur_idx = $cur_idx-1;
				if( ($export_mode != 20)&&($export_mode != 37)) list($list_pj, $cur_idx,$tar_bq_list) = pj_to_csv_entry_no_merge($elem_or_id, $type, $zip_dir, $cur_idx, $tar_bq_list, $base);
			}
		}
	}

	verbose_str_to_file(__FILE__, __FUNCTION__, "list_pj params_export recu".print_r($params_export, 1));
  $path_pj_ext=$list_pj;
	if( ($params_export['no_extension']==2) || ($export_mode==1)|| ($export_mode==36)||($export_mode==41)) {
		verbose_str_to_file(__FILE__, __FUNCTION__, "list_pj Avant".print_r($list_pj, 1));
		$tmp_arr=array();
		foreach($list_pj as $elem){
			$pj=pathinfo($elem);
			$tmp_arr[]=$pj['filename'];
		}
		$list_pj=$tmp_arr;
		verbose_str_to_file(__FILE__, __FUNCTION__, "list_pj Apres".print_r($list_pj, 1));
	}

  // if($export_mode == 39) {
  //   $path_piece="\\\\serveur\\partage\\0605-FAC-NOTE\\".$params_export['num_dossier']."\\pieces\\";
	// 	$path_pj_ext=array();
	// 	foreach($list_pj as $elem){
	// 		$path_pj_ext[]=$path_piece.$elem;
	// 	}
  // }

	verbose_str_to_file(__FILE__, __FUNCTION__, "list_pj".print_r($list_pj, 1)."list_liens".print_r($list_liens, 1)."path_pj_ext=".print_r($path_pj_ext, 1));
	return array($list_pj, $cur_idx,$tar_bq_list,$pieces[0]['name_disque'], $list_liens, $path_pj_ext);
}

function pj_to_csv_entry_no_merge($elem_or_id, $type, $zip_dir, $cur_idx, $tar_bq_list, $base){
	$list_pj = array();
	$tar_cmd = "";
	if( ! ($cur_idx>0)) $cur_idx=0;
	//$cur_idx = $cur_idx+1;
	verbose_str_to_file(__FILE__, __FUNCTION__, "get elem_or_id".print_r($elem_or_id, 1));


	$pieces = get_attached_files($elem_or_id, null, 1, $base);
	$idx_pj=0;
	foreach($pieces as $file){
		$idx_pj++;
		$file_name_ext = pathinfo($file['path']);
		$extension="";
		if($file_name_ext['extension'] == "") $extension="png";
		else $extension=$file_name_ext['extension'];
		$extension=strtoupper(substr($extension,0,3));
		if($extension=='JPE') $extension='JPG';
		$cur_idx = $cur_idx+1;
		$pdf_path = "A".str_pad($cur_idx, 7, "0",STR_PAD_LEFT).".".$extension;
		//$list_pj[] = $elem_or_id['type'].$file['name_disque'].$extension;
		$list_pj[] = $pdf_path;
		if(count($pieces)==1) launch_system_command("cp ".$file['path']." $zip_dir/".$pdf_path, 0, 1);
		if(preg_match('/^RE/', $base)|| ($base=='FA0062')) {
			launch_system_command("cp ".$file['path']." $zip_dir/".$pdf_path, 0, 1);
			if($idx_pj==1){
				$list_pj = array();
				$list_pj[0] = $pdf_path;
			}
		}
		if(count($pieces)>1) $tar_cmd .= " ".$file['path'];
	}

	verbose_str_to_file(__FILE__, __FUNCTION__, "get list_pj".print_r($list_pj, 1));
	if(count($list_pj)>1){
		if(preg_match('/^RE/', $base)|| ($base=='FA0062')) {

		} else {
			$zip_name = "Z".str_pad($cur_idx, 7, "0",STR_PAD_LEFT).".ZIP";
			$tar_path = $zip_dir."/".$zip_name;
			launch_system_command("mkdir $tar_path.TMP", 0, 1);
			launch_system_command("cp $tar_cmd $tar_path.TMP", 0, 1);
			launch_system_command("cd $tar_path.TMP; zip -r $tar_path *", 0, 1);
			rm_file("$tar_path.TMP");
			$list_pj = array();
			$list_pj[0] = $zip_name;
		}
	}

	$tar_bq_list .= " ".$list_pj[0];
	return array($list_pj, $cur_idx,$tar_bq_list,$pieces[0]['name_disque']);
}

function build_agiris_num_fact($num_fact) {
	$num_fact= str_pad($num_fact, 8, " ", STR_PAD_RIGHT);
	if(strlen($num_fact)>8) $num_fact = substr($num_fact, strlen($num_fact)-8, 8);
	return ($num_fact);
}

function build_num_piece($elem_infos) {

	list($d_entree,$m_entree,$y_entree) = date_mysql_to_html($elem_infos['created_at'], 0, 1);
	$y_entree=substr($y_entree,2,2);

	if( strlen($elem_infos['id']) >3) $id=substr($elem_infos['id'],strlen($elem_infos['id'])-4,3);
	else $id=str_pad($elem_infos['id'], 3, " ",STR_PAD_RIGHT);

	verbose_str_to_file(__FILE__, __FUNCTION__, "$d_entree,$m_entree,$y_entree".$elem_infos['created_at']);

	$numero_piece = str_pad($y_entree, 2, "0", STR_PAD_LEFT).str_pad($m_entree, 2, "0", STR_PAD_LEFT).
                strtoupper(substr($elem_infos['type'],0,1)).$id;

	return $numero_piece;

}
function xml_to_database($xml_content) {

  //echo "get source:\n$xml_content\n";
	$p = xml_parser_create();
	xml_parse_into_struct($p, $xml_content, $vals, $index);
	xml_parser_free($p);
  $arr_res = $data_parse = array();
  //verbose_str_to_file(__FILE__, __FUNCTION__, "Index array\n".print_r($index,1)."\nVals array\n".print_r($vals,1)."\n");
  $idx_cpt=0;
  for($idx=0; $idx<count($vals); $idx++) {
    //echo print_r($vals[$idx], 1);
    if( $vals[$idx]['level'] == 3) $idx_cpt++;
    if( $vals[$idx]['level'] == 4){
      $tmp_str = "";
			if(isset($vals[$idx]['value'])) $tmp_str = $vals[$idx]['value'];
			$tmp_str = str_replace("{", '', $tmp_str);
			$tmp_str = str_replace("}", '', $tmp_str);
			$arr_res[$idx_cpt][$vals[$idx]['tag']] = $tmp_str;
		}
  }

	foreach($arr_res as $key=>$val){
		$data_parse[]=$val;
	}
  verbose_str_to_file(__FILE__, __FUNCTION__, "arr_res array\n".print_r($arr_res,1));

  return($data_parse);
}

function ibiza_get_list_clients($base, $irfToken, $ibizawsdl) {

  $curltrace_path = LOGDIR."/curl_get_list_clients_$base.trace";

	$url = "$ibizawsdl/companies";
	list($response,$httpCode) = launch_ibiza_curl($base, $url, 0, null, $irfToken, $curltrace_path);
	$arr_res = xml_to_database($response);
	verbose_str_to_file(__FILE__, __FUNCTION__, "Get list clt from ibiza".print_r($arr_res,1));

  return $arr_res;
}

function ibiza_get_infos_client($database) {

  $ibizawsdl=get_ibiza_server($irfToken, $base);
	$url = $ibizawsdl."/companies";
	list($response,$httpCode) = launch_ibiza_curl($base, $url);
	$arr_res = xml_to_database($response);
  foreach($arr_res as $xml_infos){
    if($xml_infos['DATABASE']==$database) {
      verbose_str_to_file(__FILE__, __FUNCTION__, "num_dossier $database trouvé sur ibiza");
      $tmp_arr=$xml_infos;
      break;
    }
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "Get infos clt: ".print_r($clt_infos,1));

  $url = $ibizawsdl."/company/".$database."/Informations";
  list($response,$httpCode) = launch_ibiza_curl($base, $url);
  $clt_infos = xml_to_hash($response);
  $clt_infos['NAME'] = $tmp_arr['NAME'];
  $clt_infos['DATABASE'] = $database;
  verbose_str_to_file(__FILE__, __FUNCTION__, "Get infos clt: ".print_r($clt_infos,1));

  return $clt_infos;
}

function avoir_to_csv_entry($all_factures, $export_mode, $zip_dir,$cur_idx,$tar_bq_list, $base, $CHARGES_SOCIETE_TO_CPT){


	$params_export=get_params_export($base);
	if(Is_empty_str($params_export['fin_exercice'])) $params_export['fin_exercice'] = date('Y')."-12-31";
	list($d_fin,$m_fin,$y_fin) = date_mysql_to_html($params_export['fin_exercice'], 0, 1);

	if(preg_match('/^\s*$/', $base)) $cltcontroller = new VerboseController();
	else $cltcontroller = new VerboseController('societe', $base);
	$soc_infos = $cltcontroller->get(1, 'societe');
  mysqli_close($cltcontroller);
	$base_comptable = $soc_infos['comptable'];

	if(is_FacNote_base($base_comptable)) {
		$verboseController = new VerboseController('societe', $base_comptable);
		$soc_cpt = $verboseController->get(1, 'societe');
		mysqli_close($verboseController);
	}

	if(preg_match('/^\s*$/', $base)) $cltcontroller = new VerboseController();
	else $cltcontroller = new VerboseController('societe', $base);

	$all_csv_elem=array();
	$application_type = get_application_type();

	verbose_str_to_file(__FILE__, __FUNCTION__, "get all_avoir".print_r($all_factures, 1));

	foreach($all_factures as $fact_info) {
    $famille="A".$fact_info['id'];
    $total_ttc_f = formater_montant($fact_info['montant']);

		list($lib_cpt, $cpt_du_planf) = get_libcredit($fact_info, $CHARGES_SOCIETE_TO_LIB, $cltcontroller, $export_mode, $params_export, null, null, $base);
		if(($application_type =='admin')||($application_type =='comptable')||($application_type =='secretaire')) {
			$client = $cltcontroller->get($fact_info['id_client'], 'clients_admin');
			$client['nom'] = $client['name'];
		}
		else $client = $cltcontroller->get($fact_info['id_client'], 'client');
		verbose_str_to_file(__FILE__, __FUNCTION__, "get client".print_r($client, 1));

		if(($application_type =='admin')||($application_type =='comptable')||($application_type =='secretaire')) {
			$activites = $cltcontroller->selectDevisItems($fact_info['id'], null, 1);
			$description = strtoupper($activites[0]['designation']);
		}else{
			$description = strtoupper(substr(clean_file_name($fact_info['type'].' '.$client['nom'],1,1), 0, 30));
			$description=$client['nom'].' '.$client['prenom'];
			$description = strtoupper(substr(clean_file_name($description,1,1), 0, 30));
		}
		preg_replace(';', '', $description);

		list($d_entree,$m_entree,$y_entree) = date_mysql_to_html($fact_info['date_f'], 0, 1);
		$y_entree=substr($y_entree,2,2);

    if(strlen($fact_info['id'])) $id_sur4= substr($fact_info['id'],strlen($fact_info['id'])-4,4);
    else $id_sur4=$fact_info['id'];
		$numero_piece = "C".$y_entree.str_pad($m_entree, 2, "0", STR_PAD_LEFT).str_pad(substr($fact_info['id'],0,3), 3, "0",STR_PAD_LEFT);
		$numero_piece = str_pad($y_entree, 2, "0", STR_PAD_LEFT).str_pad($m_entree, 2, "0", STR_PAD_LEFT).str_pad($id_sur4, 4, " ",STR_PAD_RIGHT);

		verbose_str_to_file(__FILE__, __FUNCTION__, "get lib_cpt $lib_cpt description $description");

		list($path_pj, $cur_idx,$tar_bq_list, $toto,$list_liens, $path_pj_ext) = pj_to_csv_entry($fact_info['id'], $fact_info['type'], $zip_dir, $cur_idx,$tar_bq_list, $base, $params_export, $export_mode);
		verbose_str_to_file(__FILE__, __FUNCTION__, "ok pj_to_csv_entry with  $path_pj".print_r($path_pj_ext, 1));


    list($desc, $attached_files, $date, $detail) = get_info_rapprochement_charge($fact_info,1, $base);

    $cpt_groupe=$params_export['long_cpt_gen_ve'];
    if(Is_empty_str($cpt_groupe))$cpt_groupe="41100000";

    $tmp_csv_elem = csv_elem_from_base($base, 'avoir', $fact_info['id'], $path_pj, $list_liens, $export_mode, $banq_code_lib, $ref, $cpt_groupe, $params_export);

		if(count($tmp_csv_elem)>1) {

      for($it=0; $it<count($tmp_csv_elem); $it++){
        $tmp_csv_elem[$it]['path_pj_ext']=$path_pj_ext;
      }
      $all_csv_elem = array_merge($all_csv_elem, $tmp_csv_elem);
			verbose_str_to_file(__FILE__, __FUNCTION__, "csv_elem pris de la base et ajout path_pj_ext".print_r($path_pj_ext,1).print_r($tmp_csv_elem,1));

		} else {
      verbose_str_to_file(__FILE__, __FUNCTION__, "csv_elem non trouve en base");
      if($total_ttc_f>0) {
        verbose_str_to_file(__FILE__, __FUNCTION__, "total_ttc_f=$total_ttc_f");
        $code_j = 'VE';
        $key_p='journal_ve';
        if( ! Is_empty_str($params_export[$key_p])) $code_j = $params_export[$key_p];

        //if($export_mode==20) $path_pj = get_ged_path($export_mode, $soc_infos, $soc_cpt, $path_pj);
        $csv_elem=array();
        $csv_elem['date']=date_mysql_to_html($fact_info['date_f']);
        $csv_elem['code_j']=$code_j;
        $csv_elem['famille']=$famille;
        $csv_elem['type']='M';
        $csv_elem['cpt_general']=$cpt_groupe;
        if($export_mode==20) $csv_elem['type']='1';
        $csv_elem['code_lib']='F';
        $csv_elem['ged_dir']=build_ged_dir_date($fact_info['date_f'], $params_export['fin_exercice'], $params_export['debut_exercice'], $export_mode);
        $csv_elem['num_cpt']=$lib_cpt;
        $csv_elem['type_chg']='AVOIR';
        $csv_elem['cpt_lib']=$lib_cpt;
        $csv_elem['path_pj']=$path_pj;
        $csv_elem['path_pj_ext']=$path_pj_ext;
        $csv_elem['list_liens']=$list_liens;
        $csv_elem['num_piece']=$numero_piece;
        $csv_elem['position']=1;
        $csv_elem['description']=$description;
        $csv_elem['id_fact']=$fact_info['type']."_".$fact_info['id'];
        $csv_elem['num_fact']=$fact_info['reference'];
        $csv_elem['type_element']=$fact_info['type'];
        $csv_elem['id_type']=$fact_info['id'];
        $csv_elem['debit']=0;
        $csv_elem['credit']=formater_montant($total_ttc_f,0,0,0,1);
        $csv_elem['devise']='E';
        $csv_elem['add_plancpt']=1;
        $csv_elem['exported']=$fact_info['exported'];
        $csv_elem['piece_jointe']=$attached_files;
        $csv_elem['rapprochement']=$fact_info['rapprochement'];

        if($export_mode !=26) $all_csv_elem[]=$csv_elem;

        verbose_str_to_file(__FILE__, __FUNCTION__, "get bases_tva".print_r($bases_tva, 1)."bases_tva_solde".print_r($bases_tva_solde, 1));
        $idx_pos=-1;


        if( (!Is_empty_str($fact_info['taux_tva'])) && ($fact_info['taux_tva']>0) ) {
          $montantHT = $total_ttc_f / (1 + (formater_montant($fact_info['taux_tva'])/100));
          $montantTVA = $total_ttc_f - $montantHT;
        } else {
          $montantHT = $total_ttc_f;
          $montantTVA = 0;
        }
        list($num_cpt_ht, $num_cpt_tva,$chg_cpt,$tva_chg_cpt,$description)=get_devisitem_cpt_from_tva('facture',  $fact_info['taux_tva'], 1, $base);
        $bases_tva=array();
        $bases_tva[$num_cpt_ht][0] = $montantHT;
        $bases_tva[$num_cpt_ht][1] = $montantTVA;
        $bases_tva[$num_cpt_ht][2] = $fact_info['taux_tva'];
        $bases_tva[$num_cpt_ht][3] = $num_cpt_tva;

        foreach($bases_tva as $cpt_ht => $baseHT) {
          $idx_pos++;

          if($export_mode==20) $csv_elem['type']='2';
          $csv_elem['num_cpt']=$cpt_ht;
          if(($export_mode==26) && ($idx_pos==0))$csv_elem['position']=1;
          else $csv_elem['position']=0;
          $csv_elem['add_plancpt']=0;
          $csv_elem['debit']=formater_montant($baseHT[0],0,0,0,1);
          $csv_elem['credit']=0;
          $all_csv_elem[]=$csv_elem;

          if($baseHT[1]>0){
            if( ! ($cpt_ht>0)) $cpt_ht = 706300;
            $cpt_tva=$baseHT[3];
            if( ! ($cpt_tva>0)) $cpt_tva = 445710;

            if(preg_match('/^\s*6|7/', $all_csv_elem[count($all_csv_elem)-1]['num_cpt'])){
              $all_csv_elem[count($all_csv_elem)-1]['code_tva'] = get_tvacode($baseHT[2], 'encaissement', $CHARGES_SOCIETE_TO_CPT, null, null, $base, $params_export);
              if( ($base_comptable != 'FA0671')&&($base_comptable != 'FA1074') )  $csv_elem['code_tva'] = $all_csv_elem[count($all_csv_elem)-1]['code_tva'];
            }
            //$csv_elem['code_tva'] = get_tvacode($baseHT[2], 'encaissement', $CHARGES_SOCIETE_TO_CPT, null, null, $base, $params_export);

            $csv_elem['num_cpt']=$cpt_tva;
            $csv_elem['taux']=$baseHT[2];
            $csv_elem['add_plancpt']=0;
            $csv_elem['debit']=formater_montant($baseHT[1],0,0,0,1);
            $csv_elem['credit']=0;
            $all_csv_elem[]=$csv_elem;
          }
        }
      }
		}
	}

	if(count($all_csv_elem)>0) {
		for($it=0; $it<count($all_csv_elem);$it++){
			$all_csv_elem[$it]['ged_dir']=build_ged_dir_date($all_csv_elem[$it]['date'], $params_export['fin_exercice'], $params_export['debut_exercice'], $export_mode);
		}
	}

	$all_csv_elem = tronquer_numero_comptes($all_csv_elem, $export_mode, $params_export, $soc_infos['comptable']);
	if(count($all_csv_elem)>0){
		$all_csv_elem[0]['debut_exercice']=date_mysql_to_html($params_export['debut_exercice'],0);
		$all_csv_elem[0]['fin_exercice']=date_mysql_to_html($params_export['fin_exercice'],0);
    $all_csv_elem[0]['base']= $base;
    if($soc_infos['comptable'] == 'FA1975') $all_csv_elem[0]['nolibel_mvt']= 1;
	}

	verbose_str_to_file(__FILE__, __FUNCTION__, "return all_csv_elem et lib_cpt $lib_cpt".print_r($all_csv_elem, 1));
  mysqli_close($cltcontroller);

	return array($all_csv_elem,$lib_cpt,$cur_idx,$tar_bq_list);
}
function factures_to_csv_entry($all_factures, $export_mode, $zip_dir,$cur_idx,$tar_bq_list, $base, $CHARGES_SOCIETE_TO_CPT){


	$params_export=get_params_export($base);
	if(Is_empty_str($params_export['fin_exercice'])) $params_export['fin_exercice'] = date('Y')."-12-31";
	list($d_fin,$m_fin,$y_fin) = date_mysql_to_html($params_export['fin_exercice'], 0, 1);

	if(preg_match('/^\s*$/', $base)) $cltcontroller = new VerboseController();
	else $cltcontroller = new VerboseController('societe', $base);
	$soc_infos = $cltcontroller->get(1, 'societe');
  mysqli_close($cltcontroller);
	$base_comptable = $soc_infos['comptable'];
	if(is_FacNote_base($base_comptable)) {
		$verboseController = new VerboseController('societe', $base_comptable);
		$soc_cpt = $verboseController->get(1, 'societe');
		mysqli_close($verboseController);
	}

	if(preg_match('/^\s*$/', $base)) $cltcontroller = new VerboseController();
	else $cltcontroller = new VerboseController('societe', $base);

	$all_csv_elem=array();
	$application_type = get_application_type();

	verbose_str_to_file(__FILE__, __FUNCTION__, "get all_factures".print_r($all_factures, 1));

	foreach($all_factures as $fact_info) {
    $famille="F".$fact_info['id'];
		list($total_ht_f, $total_ttc_f, $total_tva_f,$total_ht_frais, $bases_tva,
         $total_vers_ht, $total_vers_f, $total_vers_tva, $bases_tva_V,
         $total_ht_f_solde, $total_ttc_f_solde, $total_tva_f_solde, $bases_tva_solde,
         $total_paiements) = get_totaux_factures($fact_info['id'], $fact_info['type'], 1,0,$base);
    //$total_ttc_f =formater_montant($fact_info['prix_ttc']);
    //$total_tva_f = formater_montant($fact_info['prix_tva']);
    //$total_ht_f  = $total_ttc_f-$total_tva_f;

		verbose_str_to_file(__FILE__, __FUNCTION__, $fact_info['id'].$fact_info['reference']."*** get bases_tva".print_r($bases_tva, 1)."bases_tva_solde".print_r($bases_tva_solde, 1));

		list($lib_cpt, $cpt_du_planf) = get_libcredit($fact_info, $CHARGES_SOCIETE_TO_LIB, $cltcontroller, $export_mode, $params_export, null, null, $base);
		if(($application_type =='admin')||($application_type =='comptable')||($application_type =='secretaire')) {
			$client = $cltcontroller->get($fact_info['id_client'], 'clients_admin');
			$client['nom'] = $client['name'];
		}
		else $client = $cltcontroller->get($fact_info['id_client'], 'client');
		verbose_str_to_file(__FILE__, __FUNCTION__, "get client".print_r($client, 1));

		if(($application_type =='admin')||($application_type =='comptable')||($application_type =='secretaire')) {
			$activites = $cltcontroller->selectDevisItems($fact_info['id'], null, 1);
			$description = strtoupper($activites[0]['designation']);
		}else{
			$description = strtoupper(substr(clean_file_name($fact_info['type'].' '.$client['nom'],1,1), 0, 30));
			$description=$client['nom'].' '.$client['prenom'];
			$description = strtoupper(substr(clean_file_name($description,1,1), 0, 30));
		}
		preg_replace(';', '', $description);

		list($d_entree,$m_entree,$y_entree) = date_mysql_to_html($fact_info['date_f'], 0, 1);
		$y_entree=substr($y_entree,2,2);

    if(strlen($fact_info['id'])) $id_sur4= substr($fact_info['id'],strlen($fact_info['id'])-4,4);
    else $id_sur4=$fact_info['id'];
		$numero_piece = "C".$y_entree.str_pad($m_entree, 2, "0", STR_PAD_LEFT).str_pad(substr($fact_info['id'],0,3), 3, "0",STR_PAD_LEFT);
		$numero_piece = str_pad($y_entree, 2, "0", STR_PAD_LEFT).str_pad($m_entree, 2, "0", STR_PAD_LEFT).str_pad($id_sur4, 4, " ",STR_PAD_RIGHT);

		verbose_str_to_file(__FILE__, __FUNCTION__, "get lib_cpt $lib_cpt description $description");

		list($path_pj, $cur_idx,$tar_bq_list, $toto,$list_liens, $path_pj_ext) = pj_to_csv_entry($fact_info['id'], $fact_info['type'], $zip_dir, $cur_idx,$tar_bq_list, $base, $params_export, $export_mode);
		verbose_str_to_file(__FILE__, __FUNCTION__, "ok pj_to_csv_entry with  $path_pj".print_r($path_pj_ext, 1));


    list($desc, $attached_files, $date, $detail) = get_info_rapprochement_charge($fact_info,1, $base);

    $cpt_groupe=$params_export['long_cpt_gen_ve'];
    if(Is_empty_str($cpt_groupe))$cpt_groupe="41100000";

    $tmp_csv_elem = csv_elem_from_base($base, 'facture', $fact_info['id'], $path_pj, $list_liens, $export_mode, $banq_code_lib, $ref, $cpt_groupe, $params_export);
		if(count($tmp_csv_elem)>1) {
      for($it=0; $it<count($tmp_csv_elem); $it++){
        $tmp_csv_elem[$it]['path_pj_ext']=$path_pj_ext;
        $tmp_csv_elem[$it]['famille']=$famille;
      }
      $all_csv_elem = array_merge($all_csv_elem, $tmp_csv_elem);
			verbose_str_to_file(__FILE__, __FUNCTION__, "csv_elem pris de la base et ajout path_pj_ext".print_r($path_pj_ext,1).print_r($tmp_csv_elem,1));
		} else {

      if($total_ttc_f>0) {
        $code_j = 'VE';
        $key_p='journal_ve';
        if( ! Is_empty_str($params_export[$key_p])) $code_j = $params_export[$key_p];

        //if($export_mode==20) $path_pj = get_ged_path($export_mode, $soc_infos, $soc_cpt, $path_pj);
        //if($base_comptable == 'FA5287') $description .= " ".$fact_info['reference'];

        $csv_elem=array();
        $csv_elem['date']=date_mysql_to_html($fact_info['date_f']);
        $csv_elem['famille']=$famille;
        $csv_elem['code_j']=$code_j;
        $csv_elem['type']='M';
        $csv_elem['cpt_general']=$cpt_groupe;
        if($export_mode==20) $csv_elem['type']='1';
        $csv_elem['code_lib']='F';
        $csv_elem['ged_dir']=build_ged_dir_date($fact_info['date_f'], $params_export['fin_exercice'], $params_export['debut_exercice'], $export_mode);
        $csv_elem['num_cpt']=$lib_cpt;
        $csv_elem['type_chg']='FACTURE VENTE';
        $csv_elem['cpt_lib']=$lib_cpt;
        $csv_elem['path_pj']=$path_pj;
        $csv_elem['path_pj_ext']=$path_pj_ext;
        $csv_elem['list_liens']=$list_liens;

        $csv_elem['num_piece']=$numero_piece;
        $csv_elem['position']=1;
        $csv_elem['description']=$description;
        $csv_elem['id_fact']=$fact_info['type']."_".$fact_info['id'];
        $csv_elem['num_fact']=$fact_info['reference'];
        $csv_elem['type_element']=$fact_info['type'];
        $csv_elem['id_type']=$fact_info['id'];
        $csv_elem['debit']=formater_montant($total_ttc_f,0,0,0,1);
        $csv_elem['credit']=0;
        $csv_elem['devise']='E';
        $csv_elem['add_plancpt']=1;

        $csv_elem['exported']=$fact_info['exported'];
        $csv_elem['piece_jointe']=$attached_files;
        $csv_elem['rapprochement']=$fact_info['rapprochement'];

        if($export_mode !=26) $all_csv_elem[]=$csv_elem;

        verbose_str_to_file(__FILE__, __FUNCTION__, "get bases_tva".print_r($bases_tva, 1)."bases_tva_solde".print_r($bases_tva_solde, 1));
        $idx_pos=-1;
        foreach($bases_tva as $cpt_ht => $baseHT) {
          $idx_pos++;

          if($export_mode==20) $csv_elem['type']='2';
          $csv_elem['num_cpt']=$cpt_ht;
          if(($export_mode==26) && ($idx_pos==0))$csv_elem['position']=1;
          else $csv_elem['position']=0;
          $csv_elem['debit']=0;

          $csv_elem['add_plancpt']=0;
          $csv_elem['credit']=formater_montant($baseHT[0],0,0,0,1);
          $all_csv_elem[]=$csv_elem;

          if($baseHT[1]>0){
            if( ! ($cpt_ht>0)) $cpt_ht = 706300;
            $cpt_tva=$baseHT[3];
            if( ! ($cpt_tva>0)) $cpt_tva = 445710;

            if(preg_match('/^\s*6|7/', $all_csv_elem[count($all_csv_elem)-1]['num_cpt'])){
              $all_csv_elem[count($all_csv_elem)-1]['code_tva'] = get_tvacode($baseHT[2], 'encaissement', $CHARGES_SOCIETE_TO_CPT, null, null, $base, $params_export);
              if( ($base_comptable != 'FA0671')&&($base_comptable != 'FA1074') )  $csv_elem['code_tva'] = $all_csv_elem[count($all_csv_elem)-1]['code_tva'];
            }
            //$csv_elem['code_tva'] = get_tvacode($baseHT[2], 'encaissement', $CHARGES_SOCIETE_TO_CPT, null, null, $base, $params_export);

            $csv_elem['num_cpt']=$cpt_tva;
            $csv_elem['debit']=0;
            $csv_elem['taux']=$baseHT[2];
            $csv_elem['add_plancpt']=0;
            $csv_elem['credit']=formater_montant($baseHT[1],0,0,0,1);
            $all_csv_elem[]=$csv_elem;
          }
        }
      }
		}
	}

	if(count($all_csv_elem)>0) {
		for($it=0; $it<count($all_csv_elem);$it++){
			$all_csv_elem[$it]['ged_dir']=build_ged_dir_date($all_csv_elem[$it]['date'], $params_export['fin_exercice'], $params_export['debut_exercice'], $export_mode);
		}
	}

  $all_csv_elem = facture_egaliser_csv($all_csv_elem);
  verbose_str_to_file(__FILE__, __FUNCTION__, "Sortie egaliser ".print_r($all_csv_elem,1));

	$all_csv_elem = tronquer_numero_comptes($all_csv_elem, $export_mode, $params_export, $soc_infos['comptable']);
	if(count($all_csv_elem)>0){
		$all_csv_elem[0]['debut_exercice']=date_mysql_to_html($params_export['debut_exercice'],0);
		$all_csv_elem[0]['fin_exercice']=date_mysql_to_html($params_export['fin_exercice'],0);
    $all_csv_elem[0]['base']= $base;
    if($soc_infos['comptable'] == 'FA1975') $all_csv_elem[0]['nolibel_mvt']= 1;
	}

	verbose_str_to_file(__FILE__, __FUNCTION__, "return all_csv_elem et lib_cpt $lib_cpt".print_r($all_csv_elem, 1));
  mysqli_close($cltcontroller);
	return array($all_csv_elem,$lib_cpt,$cur_idx,$tar_bq_list);
}

function facture_egaliser_csv($all_csv_elem){

  $pos_ht="";
  $total_deb=$total_cred=0;
  for($i=0;$i<count($all_csv_elem);$i++) {
    $total_deb += formater_montant($all_csv_elem[$i]['debit']);
    $total_cred += formater_montant($all_csv_elem[$i]['credit']);
    if(preg_match('/^\s*7/', $all_csv_elem[$i]['num_cpt'])) {
      if(Is_empty_str($pos_ht)) $pos_ht=$i;
    }
  }

  if($total_cred != $total_deb) $all_csv_elem[$pos_ht]['credit'] = formater_montant($all_csv_elem[$pos_ht]['credit']) + $total_deb - $total_cred;


  return $all_csv_elem;
}

function fraisandcharges_to_csv_entry($associated_charges, $export_mode, $zip_dir,$cur_idx,$tar_bq_list, $banq_code_lib, $CHARGES_SOCIETE_TO_CPT, $chg_to_tvacpt, $non_associated,$CHARGES_SOCIETE_TO_LIB, $base, $force) {


  verbose_str_to_file(__FILE__, __FUNCTION__, "get non_associated =  $non_associated ****");

	if(preg_match('/^\s*$/', $base)) $cltcontroller = new VerboseController();
	else $cltcontroller = new VerboseController('societe', $base);
	$soc_infos = $cltcontroller->get(1, 'societe');
  mysqli_close($cltcontroller);
	$total_ht = 0;
	$tva = 0;
	$total_ttc = 0;
	$chg_csv_elem=array();
	$nb_jours_in_month = cal_days_in_month(CAL_GREGORIAN, $month_idx,$year);
	$derniere_date = $nb_jours_in_month.'/'.$month_idx.'/'.$year;
	$total_debit = 0;
	$total_credit = 0;
	$csv_content_bank="";
	$csv_content_assoc="";
	global $dbConfig;
	$dbName = $dbConfig["db"];
	$chg_non_valid=0;

	verbose_str_to_file(__FILE__, __FUNCTION__, "get associated_charges".print_r($associated_charges, 1));
	list($fact_elems, $frais_elems, $chg_elems, $avoir_elems) = associated_charges_tocsv_list($associated_charges, $force);
	verbose_str_to_file(__FILE__, __FUNCTION__, "get fact_elems".print_r($fact_elems, 1));
	verbose_str_to_file(__FILE__, __FUNCTION__, "get chg".print_r($chg_elems, 1));
	verbose_str_to_file(__FILE__, __FUNCTION__, "get frais".print_r($frais_elems, 1));
  verbose_str_to_file(__FILE__, __FUNCTION__, "get avoirs".print_r($avoir_elems, 1));


	if(count($fact_elems)>0) {
		list($cur_csv_elem,$cpt_lib,$cur_idx,$tar_bq_list)  = factures_to_csv_entry($fact_elems, $export_mode, $zip_dir,$cur_idx,$tar_bq_list, $base, $CHARGES_SOCIETE_TO_CPT);
    if(count($cur_csv_elem)>0) $chg_csv_elem = array_merge($chg_csv_elem, $cur_csv_elem);
    verbose_str_to_file(__FILE__, __FUNCTION__, "sortie fact tar_bq_list $tar_bq_list cur_csv_elem".print_r($cur_csv_elem,1)."chg_csv_elem".print_r($chg_csv_elem,1));
  }

  if(count($avoir_elems)>0){
		list($cur_csv_elem,$cpt_lib,$cur_idx,$tar_bq_list)  = avoir_to_csv_entry($avoir_elems, $export_mode, $zip_dir,$cur_idx,$tar_bq_list, $base, $CHARGES_SOCIETE_TO_CPT);
    if(count($cur_csv_elem)>0) $chg_csv_elem = array_merge($chg_csv_elem, $cur_csv_elem);
    verbose_str_to_file(__FILE__, __FUNCTION__, "sortie avoir tar_bq_list $tar_bq_list cur_csv_elem".print_r($cur_csv_elem,1)."chg_csv_elem".print_r($chg_csv_elem,1));
  }

	foreach($frais_elems as $key => $fraisByYear){
		foreach($fraisByYear as $keyY => $fraisByMonth) {
			//foreach($fraisByMonth as $keyM => $fraisByManager) {
      list($cur_csv_elem,$cpt_libf,$cur_idx,$tar_bq_list) = chg_to_csv_entry
                                                          ($fraisByMonth, $export_mode, $zip_dir, $month_idx,$year, 'frais', $CHARGES_SOCIETE_TO_CPT,$banq_code_lib,$cur_idx,$tar_bq_list,
                                                           $chg_to_tvacpt, $non_associated,$base,$CHARGES_SOCIETE_TO_LIB);
      if(($cur_csv_elem==-1)||($cpt_libf==-1)) $chg_non_valid=1;
      else {
        if(count($cur_csv_elem)>0) $chg_csv_elem = array_merge($chg_csv_elem, $cur_csv_elem);
        if(count($frais_elems) >0) $cpt_lib = $cpt_libf;
        verbose_str_to_file(__FILE__, __FUNCTION__, "sortie frais tar_bq_list $tar_bq_list cur_csv_elem".print_r($cur_csv_elem,1)."\n $$$$ chg_csv_elem".print_r($chg_csv_elem,1));
      }
			//}
		}
	}

	verbose_str_to_file(__FILE__, __FUNCTION__, "pour la base $base get ndf_as_chg $ndf_as_chg activites chg_elems=".print_r($chg_elems, 1));
	if(count($chg_elems)>0){
		list($cur_csv_elem,$cpt_libc,$cur_idx,$tar_bq_list) = chg_to_csv_entry($chg_elems, $export_mode, $zip_dir, $month_idx,$year, '', $CHARGES_SOCIETE_TO_CPT,$banq_code_lib,
                                                                           $cur_idx,$tar_bq_list,$chg_to_tvacpt, $non_associated,$base,$CHARGES_SOCIETE_TO_LIB);

		if(($cur_csv_elem==-1)||($cpt_libf==-1)) $chg_non_valid=1;
		else {
			verbose_str_to_file(__FILE__, __FUNCTION__, "avant merge chg_elems".print_r($chg_elems,1));
			verbose_str_to_file(__FILE__, __FUNCTION__, "avant merge a ajouter".print_r($cur_csv_elem,1));
			if(count($cur_csv_elem)>0) $chg_csv_elem = array_merge($chg_csv_elem, $cur_csv_elem);
			verbose_str_to_file(__FILE__, __FUNCTION__, "apres merge chg_csv_elem".print_r($chg_csv_elem,1));
			if(count($chg_elems) >0) $cpt_lib = $cpt_libc;
			verbose_str_to_file(__FILE__, __FUNCTION__, "sortie chg tar_bq_list $tar_bq_list cur_csv_elem".print_r($cur_csv_elem,1)."\n $$$$ chg_csv_elem".print_r($chg_csv_elem,1));
		}
	}

	if(($soc_infos['soctype']=='medecin')&&($non_associated !=1)) $chg_csv_elem=array();
	$bq_pj_list = 	explode(" ",$tar_bq_list);
	$bq_pj_list_res=array();
	foreach($bq_pj_list as $fileP){
		if(preg_match('/\w+/',$fileP)) $bq_pj_list_res[]=$fileP;
	}
	$bq_pj_list=$bq_pj_list_res;
	if(count($bq_pj_list)>1) {
		$tar_name = "ZIP".str_pad($cur_idx, 5, "0",STR_PAD_LEFT).".ZIP";
		$tar_path = $zip_dir."/".$tar_name;
		launch_system_command("cd $zip_dir; zip -r $tar_path $tar_bq_list", 1);
		$list_pj = array();
		$list_pj[0] = $tar_name;
	} else $list_pj[0] = $bq_pj_list[0];
	verbose_str_to_file(__FILE__, __FUNCTION__, "get bq_pj_list tar_bq_list $tar_bq_list".print_r($bq_pj_list, 1));
	verbose_str_to_file(__FILE__, __FUNCTION__, "return $cur_idx, $cpt_lib, $chg_non_valid, chg_csv_elem".print_r($chg_csv_elem, 1));

	return array($chg_csv_elem, $cur_idx, $cpt_lib, $chg_non_valid);
}
function get_num_dossier_clt($base=null) {

	if($base==null)$verboseController = new VerboseController();
	else $verboseController = new VerboseController('societe', $base);
	$soc_infos = $verboseController->get(1, 'societe');
	$param_exp = $verboseController->get(1, 'params_export');
	mysqli_close($verboseController);
	$doss= $param_exp['num_dossier'];
	if(Is_empty_str($doss)) $doss=$soc_infos['num_dossier'];
	return $doss;
}

function increment_ascii($compteur) {

	if(Is_empty_str($compteur)) $compteur='AAA';

	$splitted = str_split($compteur);

	$code_ascii = ord($splitted[2])+1;
	if($code_ascii > 90) {
		$splitted[2]=chr(65);
		$code_ascii = ord($splitted[1])+1;
		if($code_ascii > 90) {
			$splitted[1]=chr(65);

			$code_ascii = ord($splitted[0])+1;
			if($code_ascii > 90) $splitted[0]=chr(65);
			else $splitted[0]=chr($code_ascii);

		} else $splitted[1]=chr($code_ascii);
	} else $splitted[2]=chr($code_ascii);

	return $splitted[0].$splitted[1].$splitted[2];

}



// csv_pdf_lib

// librairie spécifique

function is_ttc($cpt) {

  $cpt_ttc=0;

  if(preg_match('/[A-Z]/i', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*0/i', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*9/i', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*5/', $cpt) && preg_match('/^\s*\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*10/', $cpt) && preg_match('/^\s*\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*421/', $cpt) && preg_match('/^\s*\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*43/', $cpt) && preg_match('/^\s*\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*45/', $cpt) && preg_match('/^\s*\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*46/', $cpt) && preg_match('/^\s*\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*472/', $cpt) && preg_match('/^\s*\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*473/', $cpt) && preg_match('/^\s*\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*474/', $cpt) && preg_match('/^\s*\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*475/', $cpt) && preg_match('/^\s*\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*(40|41)/', $cpt) && ( ! preg_match('/^\s*(403|405|406|407|408|409)/', $cpt)) ) $cpt_ttc=1;
  else if(preg_match('/^\s*(421|422|423|424|425|426|427|428|512)/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*4451\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*447\d+\s*$/', $cpt)) $cpt_ttc=1;
  else if(preg_match('/^\s*401\d+\s*$/', $cpt)) $cpt_ttc=1;


  return $cpt_ttc;
}

function get_code_lib_fam($all_csv_elem){


  $code_lib_fam=$res=array();
  foreach($all_csv_elem as $csv_elem) {
    if(preg_match('/^\s*51/', $csv_elem['num_cpt']) && ($csv_elem['type_element']=='banque')){
      $code_lib_fam[$csv_elem['famille']]=' ';
    } else if($csv_elem['pos_txt']=='TTC') {
      $code_lib_fam[$csv_elem['famille']]='F';
      if($csv_elem['type_element']=='encaissement') {
        if(formater_montant($csv_elem['credit'])>0)$code_lib_fam[$csv_elem['famille']]='A';
      } else {
        if(formater_montant($csv_elem['debit'])>0)$code_lib_fam[$csv_elem['famille']]='A';
      }
    }
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "code_lib_fam ".print_r($code_lib_fam,1)."\n");

  foreach($all_csv_elem as $csv_elem) {
    $csv_elem['code_lib'] = $code_lib_fam[$csv_elem['famille']];
    $res[]=$csv_elem;
  }
  verbose_str_to_file(__FILE__, __FUNCTION__, "\n all_csv_elem\n".print_verb_csvelem($res,1)."\n");
  return $res;
}


function filtre_achats_ventes($all_csv_elem){

  $res=$res_ach=$res_ve=$res_autre=array();

  foreach($all_csv_elem as $csv_elem) {
    if($csv_elem['nature'] == "achat") $res_ach[]=$csv_elem;
    else if($csv_elem['nature'] == "vente") $res_ve[]=$csv_elem;
    else $res_autre[]=$csv_elem;
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "\nachats\n".print_verb_csvelem($res_ach,1)."\nventes\n ".print_verb_csvelem($res_ve,1)."\nautres\n".print_verb_csvelem($res_autre,1));

  if(count($res_ach)>0) {
    foreach($res_ach as $csv_elem) {
      $res[]=$csv_elem;
    }
    foreach($res_autre as $csv_elem) {
      $res[]=$csv_elem;
    }

    unlock_pour_test($res_ve);

  } else {
    foreach($res_ve as $csv_elem) {
      $res[]=$csv_elem;
    }
    foreach($res_autre as $csv_elem) {
      $res[]=$csv_elem;
    }
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "elements gardes ".print_verb_csvelem($res));
  return $res;
}
function post_content2csv_elem($fin_exercice, $debut_exercice, $exportmode) {

  $total_let_verb="";
  $ttc_found=$total_let=$let2fam=$all_csv_elem=$fam_done=array();
  $content = explode("\n", $_POST['content']);

  $fam2pos=$tmp_arr=array();
  foreach($content as $line){
    $splitted = explode(";", $line);
    $fam2pos=array();
    for($idx=0; $idx<count($splitted); $idx++){
      $tmp_str = $splitted[$idx];

      if(($idx!=4)&&($idx!=10)&&($idx!=7)&&($idx!=8)&&($idx!=40)) $tmp_str = clean_file_name($tmp_str);

      if(preg_match('/Euro\s+Euro\s+Euro/', $tmp_str))$tmp_str='E';

      $fam2pos[$idx] = $tmp_str;
    }
    $tmp_arr[]=$fam2pos;
  }
  $content = $tmp_arr;


  foreach($content as $tmp_arrB){
    //8246583;;;VATAT PIERRE;01-01-2020;01222769778;61320000;640.42;0;FB0122;31-01-2020;AC;TF012020;;;;;;102694;1170231;2769778;;1;;;;1338;;;EUR;;202002_2769778.pdf; ;achat;202002_2769778.pdf;;

    $splitted = array();
    foreach($tmp_arrB as $elem) {
      $elem = str_replace("NULL", "", $elem);
      $elem = str_replace("null", "", $elem);
      $splitted[]=trim($elem);
    }

    for($j=0;$j<50;$j++){
      if( ! isset($splitted[$j])) $splitted[$j]=null;
    }



    //$splitted = explode(";", $line);
    $csv_elem=array();
    $csv_elem['base']=$_GET['base'];
    $csv_elem['comptable']=$_GET['comptable'];
    $csv_elem['date']=date_mysql_to_html(date_url_to_mysql($splitted[4]));
    $csv_elem['famille']=$splitted[5];

    $csv_elem['nature']=$splitted[33];
    // ACE AND GO

    $csv_elem['cpt_general'] = nature2cptgen($csv_elem);
    if(!Is_empty_str($splitted[32])) $csv_elem['cpt_general']=$splitted[32];

    if( ! Is_empty_str($splitted[10])) $csv_elem['date_echeance']=date_mysql_to_html(date_url_to_mysql($splitted[10]));
    if($_GET['comptable']=="FB1021") $csv_elem['date_echeance']="";
    $csv_elem['cb']=$splitted[34];
    $csv_elem['code_j']=$splitted[11];

    if( $_GET['base']=='FB0114') $csv_elem['lettrage']="";
    //else if( $_GET['base']=='FB0122') $csv_elem['lettrage']="";
    else $csv_elem['lettrage']=$splitted[14];

    $csv_elem['code_tva']=$splitted[15];
    $csv_elem['code_ana']=$splitted[13];
    $csv_elem['id_type']=$splitted[0];
    $csv_elem['ged_dir']=build_ged_dir_date($csv_elem['date'], $fin_exercice, $debut_exercice, $exportmode);
    $csv_elem['path_pj']=array($splitted[31]);
    $csv_elem['path_pj_ext']=array($splitted[31]);

    if(!Is_empty_str($splitted[40])) {
      //if($_GET['base']=='FA0143') {
      $csv_elem['list_liens']=array(trim($splitted[40]));
    } else {
      if(!Is_empty_str($splitted[34])) $lien=$splitted[34];
      else $lien=$splitted[31];
      if(!Is_empty_str($lien)) $csv_elem['list_liens']=array("https://www.cabinet-expertcomptable.com/upload/".$_GET['base']."/ecritures/$lien");
    }

    $csv_elem['description']=$splitted[3];
    $csv_elem['devise']=$splitted[29];
    if(strlen($csv_elem['devise']) != 3) $csv_elem['devise']="";
    $csv_elem['num_fact']=$splitted[12];
    //if($exportmode==41) $csv_elem['num_fact']=preg_replace('\.\w+\s*$', '', $splitted[31]);
    $csv_elem['id_fact']=$splitted[0];
    $csv_elem['cpt_lib']=$splitted[6];
    $csv_elem['num_cpt']=$splitted[6];
    $csv_elem['quantite1']=formater_montant($splitted[36]);
    if($csv_elem['quantite1']==0)$csv_elem['quantite1']="";
    $csv_elem['quantite2']=formater_montant($splitted[37]);
    if($csv_elem['quantite2']==0)$csv_elem['quantite2']="";




    $csv_elem['A1period1']=$splitted[38]." ".date_url_to_mysqlB($splitted[38]);
    $tmp_date = trim($splitted[38]);
    if( ! Is_empty_str($tmp_date) ) {
      $tmp_date = date_url_to_mysql($tmp_date);
      $csv_elem['A1period1'] .=" decoup ".$tmp_date;
      if(!Is_empty_str($tmp_date)){
        $tmp_date = date_mysql_to_html($tmp_date);
        $csv_elem['A1period1'] .=" decoup ".$tmp_date;
        $csv_elem['period1']=$tmp_date;
      }
    }

    $csv_elem['A1period1']=$splitted[39]." ".date_url_to_mysql($splitted[39]);
    $tmp_date = trim($splitted[39]);
    if( ! Is_empty_str($tmp_date) ) {
      $tmp_date = date_url_to_mysql($tmp_date);
      $csv_elem['A1period1'] .=" decoup ".$tmp_date;
      if(!Is_empty_str($tmp_date)){
        $tmp_date = date_mysql_to_html($tmp_date);
        $csv_elem['A1period1'] .=" decoup ".$tmp_date;
        $csv_elem['period2']=$tmp_date;
      }
    }



    $csv_elem['add_plancpt']='0';
    $csv_elem['position']=2;
    $csv_elem['pos_txt']='HT';

    $cpt_tva=$cpt_ttc=0;
    if(preg_match('/^\s*44\d+\s*$/', $csv_elem['num_cpt'])) $cpt_tva=1;
    $cpt_ttc = is_ttc($csv_elem['num_cpt']);

    if($cpt_tva==1) $csv_elem['pos_txt']='TVA';
    if($cpt_ttc==1) {
      if(! isset($ttc_found[$csv_elem['famille']])) $ttc_found[$csv_elem['famille']]=0;

      if($ttc_found[$csv_elem['famille']] != 1) {
        $csv_elem['position']=1;
        $csv_elem['add_plancpt']='1';
        $csv_elem['pos_txt']='TTC';
        $ttc_found[$csv_elem['famille']] = 1;
      }
    }

    $csv_elem['type']='M';
    if($exportmode==20) $csv_elem['type']='1';

    $csv_elem['num_piece']="";
    $csv_elem['num_piece']=trim($splitted[35]);
    if(Is_empty_str($csv_elem['num_piece'])) $csv_elem['num_piece']=trim($splitted[12]);

    if($exportmode==41) {
      if(isset($splitted[31]) && (strlen($splitted[31])>2)){
        $csv_elem['num_piece']=preg_replace('/\.\w+\s*$/', '', $splitted[31]);
        $csv_elem['num_piece'] = $splitted[31];
        $tmp_arr = explode('.', $csv_elem['num_piece']);
        $csv_elem['num_piece'] = $tmp_arr[0];
      }
    }

    if(Is_empty_str($csv_elem['num_fact'])) $csv_elem['num_fact']=$csv_elem['num_piece'];

    $csv_elem['fichier_source']=$splitted[31];

    $csv_elem['type_element']='charge';
    if(isset($splitted[33])) {
      if(($splitted[33]=='vente')||($splitted[33]=='avoir')||($splitted[33]=='facture')||($splitted[33]=='encaissement')) $csv_elem['type_element']='encaissement';
      else if($splitted[33]=='banque') $csv_elem['type_element']='banque';
    }

    $csv_elem['type_chg']=$csv_elem['type_element'];

    $csv_elem['user_login']=$splitted[1];
    $csv_elem['nouvelleversion']=1;
    if(isset($_GET['etablissement'])) $csv_elem['etablissement']=$_GET['etablissement'];
    else $csv_elem['etablissement']= null;

    $csv_elem['debit']=formater_montant($splitted[7],0,0,0,1,2);
    $csv_elem['credit']=formater_montant($splitted[8],0,0,0,1,2);
    $montant = formater_montant($csv_elem['debit'])+formater_montant($csv_elem['credit']);

    // lettrage sur compte auxiliaire uniquement
    if(preg_match('/^\s*5/', $csv_elem['num_cpt'])) $csv_elem['lettrage']="";
    else if($cpt_ttc != 1) $csv_elem['lettrage']="";

    if( ! Is_empty_str($csv_elem['lettrage'])) {
      $fam_id = $csv_elem['famille'];
      if(!Is_empty_str($fam_id)) {
        if($cpt_ttc == 1) {
          if(!isset($total_let[$csv_elem['lettrage']]))$total_let[$csv_elem['lettrage']]=0;

          $total_let_verb .= $fam_id." ".$csv_elem['lettrage']." = ".formater_montant($total_let[$csv_elem['lettrage']])." + ".formater_montant($csv_elem['credit'])." - ".formater_montant($csv_elem['debit'])."\n";
          $total_let[$csv_elem['lettrage']] = formater_montant($total_let[$csv_elem['lettrage']]) + formater_montant($csv_elem['credit']) - formater_montant($csv_elem['debit']);
        }

        if(!isset($fam_done[$fam_id]))$fam_done[$fam_id]=0;
        if($fam_done[$fam_id] != 1) {
          $fam_done[$fam_id] = 1;
          $let2fam[$csv_elem['lettrage']][]=$csv_elem['famille'];
        }

      }
    }


    if( $montant > 0 ) {
      $all_csv_elem[] = $csv_elem;
    }
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "all_csv_elem\n".print_verb_csvelem($all_csv_elem,1)."\n"."total_let  \ntotal_let_verb:\n$total_let_verb\n\n".print_r($total_let,1)."\n"."\n"."let2fam  \n".print_r($let2fam,1)."\n");

  return array($all_csv_elem, $total_let, $let2fam);
}

function nature2cptgen($csv_elem) {
  $cpt_gen = "";
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA2248')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA2248')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA2562')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA2562')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA0076')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA0076')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA2866')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA2866')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA2870')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA2870')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA2872')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA2872')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA2991')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA2991')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3060')) $cpt_gen='4111';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3060')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3120')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3120')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3131')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3131')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3192')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3192')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3209')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3209')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA2007')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA2007')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA2614')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA2614')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3161')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3161')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3181')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3181')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3269')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3269')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3458')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3458')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3468')) $cpt_gen='4111';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3468')) $cpt_gen='4011';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3469')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3469')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3479')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3479')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3491')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3491')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA3504')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA3504')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA5281')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA5281')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA5323')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA5323')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA5657')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA5657')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA5981')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA5981')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA6787')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA6787')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA7909')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA7909')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA8308')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA8308')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA8634')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA8634')) $cpt_gen='401';
  if(($csv_elem['nature']=='vente')&&($csv_elem['base']=='FA8636')) $cpt_gen='411';
  if(($csv_elem['nature'] !='vente')&&($csv_elem['base']=='FA8636')) $cpt_gen='401';

  return $cpt_gen;
}

function launch_curl($url, $res_file, $body_str) {

  verbose_str_to_file(__FILE__, __FUNCTION__, date("d/m/Y H:i:s")."launch curl avec resultat dans $res_file sur\n$url\n");

	$header_array = array();

  $curlHandle = curl_init();
  curl_setopt($curlHandle, CURLOPT_URL, $url);
  curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
	if(! Is_empty_str($body_str)) {
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'POST' );
		curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array('content'=>$body_str));
	}

  $response = curl_exec($curlHandle);
  $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
  curl_close($curlHandle);

  verbose_str_to_file(__FILE__, __FUNCTION__, date("d/m/Y H:i:s")." Ret code $httpCode response =\n".substr($response,0,200)."\n");
  $response = preg_replace('/^\s+status=\w+\s+/', '', $response);

  if( ! Is_empty_str($res_file) ) {
    verbose_str_to_file($file_occur, __FUNCTION__, "response in file $res_file\n");
    file_put_contents($res_file, $response);
  }

  return array($response,$httpCode);
}

function build_export_file_name($export_mode, $cab_dossier, $zip_dir_name) {

  $file_name = "export";

  if(($export_mode==20)) $file_name = $cab_dossier."IN.auto";
  else if(($export_mode==25)||($export_mode==44)) $file_name = $cab_dossier."IN";
  else if(($export_mode==32)) $file_name = $cab_dossier."-$zip_dir_name.TXT";//ECR
  else if(($export_mode==38)) $file_name = $cab_dossier."-$zip_dir_name.TXT";//ECR
  else if(($export_mode==33)) $file_name = $cab_dossier."-$zip_dir_name.tra";
  else if(($export_mode==34)) $file_name = "ibiza.xml";
  else $file_name = $zip_dir_name;

  if(($export_mode==20)||($export_mode==44)) $file_name = $file_name;
  else if(($export_mode==2)||($export_mode==13)||($export_mode==14)||($export_mode==17)) $file_name = $file_name."_".date('Ymd').time().".txt";
  else if($export_mode==27) $file_name = $file_name.'.tsv';
  else if(($export_mode<0)) $file_name = $file_name.'.xml';
  else if(($export_mode==20)||($export_mode==44)||($export_mode==25)||($export_mode==26)||($export_mode==34))$file_name = $file_name;
  else if(($export_mode==32))$file_name = $file_name;
  else if(($export_mode==33))$file_name = $file_name;
  else if(($export_mode==38))$file_name = $file_name;
  else if( ($export_mode==36)||($export_mode==1)||($export_mode==41)) $file_name = $cab_dossier."_".date('Ymd-His').".xls";
  else if($export_mode==37) $file_name = $cab_dossier."_".date('Ymd-His').".csv";
  else if($export_mode==42) $file_name = clean_file_name($cab_dossier,0,0)."_".date('Ymd-His').".txt";
  else if($export_mode==43) $file_name = clean_file_name($cab_dossier,0,0)."_".date('Ymd-His').".txt";
  else if($export_mode==45) $file_name = clean_file_name($cab_dossier,0,0)."_".date('Ymd-His').".pnm";
  else $file_name = $file_name.'.csv';


  verbose_str_to_file(__FILE__, __FUNCTION__, "export file_name $file_name\n");

  return($file_name);
}
function ibiza_import_elem($db_clt, $xml_content, $base, $irfToken, $ibizawsdl, $curltrace_path) {
  $verbose=0;
  $message="";

  if(Is_empty_str($ibizawsdl)||(strlen($ibizawsdl)<5)) $ibizawsdl=get_ibiza_server($irfToken, $base);
  $url = "$ibizawsdl/company/$db_clt/entries";

  list($response,$httpCode) = launch_ibiza_curl($base, $url, $verbose, $xml_content, $irfToken, $curltrace_path);
	$arr_res=xml_to_hash($response);

	if( isset($arr_res['MESSAGE']) && (! Is_empty_str($arr_res['MESSAGE']))) $message=$arr_res['MESSAGE'];
	else if( isset($arr_res['DESCRIPTION']) ) $message=$arr_res['DESCRIPTION'];

	if(Is_empty_str($message))  {
    if( isset($arr_res['DETAIL'])) $message=$arr_res['DETAILS'];
  }

	if($httpCode != 200) return array(1, "connexion au serveur Ibiza en panne ");
	else if(preg_match('/Error/i', $arr_res['RESULT']) ) return array(1, $message);
	else return array(0, "");

}

function xml_to_hash($xml_content) {


	$p = xml_parser_create();
	xml_parse_into_struct($p, $xml_content, $vals, $index);
	xml_parser_free($p);
  $arr_res = array();

  foreach($vals as $val_arr){
    $k='tag';
    $key=null;
    if(isset($val_arr['tag']))$key=$val_arr['tag'];
    $val=null;
    if(isset($val_arr['value'])) $val=$val_arr['value'];
    if((! preg_match('/^\s*$/', $key)) &&(! preg_match('/^\s*$/', $val)) )$arr_res[$key] = $val;
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "arr_res array\n".print_r($arr_res,1));

  return($arr_res);
}

function launch_ibiza_curl($base, $url, $verbose, $body_str, $irfToken, $curltrace_path) {



  //$irfToken = 'irfToken: G07Sgi7L9oc134MBy9+bgwTVRvCC87bJcJoZxcISYOXYZydlv4syR9QVoVV/rWpQnxVsLVqV8Si0a1GIct+o45jaj3VgZ68DtROI2ApoWASjtEJ1EAgRkwULMmEY8FxPSgMvG0er0yRXtyCa7wllVT+Dz/MPvpiqT70j7yLLunpaxRkFr2KtGhLtg781zJzoEV3i64Y2eoZ5F9ulVGEWdxo4qX3cd19D/gBr6roLLn6q5UUuqtDPQhurmNNyZXjX6/YHgY72lkqqg4RXYi9L2G7sHgcQI5T2SsIO4Js9xoVsbmQhTjpBw61nQYLax5ZMCiXzJL/jtaPJQrKfEtmDaA==';

  $trace="";
  $trace_path = LOGDIR.'/curl_trace_'.time().'.txt';
  verbose_str_to_file(__FILE__, __FUNCTION__, "trace_path $trace_path");

  $curl_log = fopen($trace_path ,'w' );
  fwrite($curl_log, "launch_ibiza_curl sur base $base\n");

  verbose_str_to_file(__FILE__, __FUNCTION__, "trace_path $trace_path ouvert");
  $curlHandle = curl_init();
  verbose_str_to_file(__FILE__, __FUNCTION__, "curl_init");
  $partnerID='partnerID: {96AB1027-FF1A-4189-A851-F78A61C6BA37}';

  if(! preg_match('/^\s*irfToken/', $irfToken)) $irfToken = "irfToken: $irfToken";

  $trace .= "curl_init on base = $base url=\n$url\n irfToken\n$irfToken\n";

  curl_setopt($curlHandle, CURLOPT_URL, $url);

  $header_array = array($partnerID, $irfToken);
  if(! Is_empty_str($body_str)) $header_array[] = "Content-Type: application/xml";
  if(! Is_empty_str($body_str)) $header_array[] = "Accept: application/xml";

  curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $header_array);
  curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
  curl_setopt($curlHandle, CURLOPT_VERBOSE, true);
  curl_setopt($curlHandle, CURLOPT_STDERR, $curl_log);

  if(! Is_empty_str($body_str)) {
    curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'POST' );
    curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body_str);
  }

  $trace .= "exec with curl_setopt\n".print_r($curlHandle,1);

  $response = curl_exec($curlHandle);
  //verbose_str_to_file(__FILE__, __FUNCTION__, "reponse $response");
  $trace .= "fin exec response\n"."url: $url\n post avec body_str=$body_str\ncurl_errno:".print_r(curl_errno($curlHandle),1)."\n*** curl_getinfo***\n".print_r(curl_getinfo($curlHandle),1);

  $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
  curl_close($curlHandle);
  fclose($curl_log);

  file_put_contents($trace_path, "\n\nTrace: \n$trace", FILE_APPEND);


  verbose_str_to_file(__FILE__, __FUNCTION__, "ret code $httpCode curl_log \n".file_get_contents($trace_path)."\n response: \n".substr($response,0,2000));


  if( ! Is_empty_str($curltrace_path)) {
    $tmp_str=$response;
    if(strlen($response)>1000) $tmp_str = substr($response,0,1000)." ... ".strlen($response)."cars\n";
    file_put_contents($curltrace_path, "\n******************************************\n**** Traces $base: $url\n******************************************\n".file_get_contents($trace_path)."\nReponse:\n$tmp_str\n", FILE_APPEND);
    rm_file($trace_path);
  }

  return array($response,$httpCode);
}

function exportTOibiza($all_csv_elem, $fin_exercice, $debut_exercice, $base, $cab_dossier, $export_mode, $long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $irfToken) {

  verbose_str_to_file(__FILE__, __FUNCTION__, "ecritures a exporter vers ibiza\n: ".print_verb_csvelem($all_csv_elem,1));
  $trace_fam_path = LOGDIR."/exp_".$all_csv_elem[0]['famille'].".txt";
  $trace_clt_path = LOGDIR."/exp_mess_".$all_csv_elem[0]['famille'].".txt";
  $curltrace_path = LOGDIR."/curl_".$all_csv_elem[0]['famille'].".trace";

  $comptable = $all_csv_elem[0]['comptable'];


  verbose_str_to_file(__FILE__, __FUNCTION__, "trace_fam_path=\n$trace_fam_path trace_clt_path=\n$trace_clt_path curltrace_path=\n$curltrace_path\n");

  //EXO004 00 02 0000 20 OUVOUVExercice
  $message_fam = "construct zipdir avec  ".$all_csv_elem[0]['date'];
  list($zip_dir, $zip_dir_name) = get_zipdir_path($all_csv_elem[0]['date'], $base, $cab_dossier, $export_mode);

  verbose_str_to_file(__FILE__, __FUNCTION__, "debut_exercice $debut_exercice fin_exercice $fin_exercice\n");

  all_csv_elem_to_zip($all_csv_elem, $export_mode, $zip_dir, $zip_dir_name, $base, $cab_dossier, $fin_exercice, $debut_exercice, $long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, null,null,null,null,null,null,null);



  list($statusT, $messageT, $file_list) = get_dir_content($zip_dir);
  $message_fam .= "\nContenu du zip_dir: ".print_r($file_list,1);
  $ibiza_xml = file_get_contents($zip_dir."/ibiza.xml");
  $message_fam .= $ibiza_xml;
  verbose_str_to_file(__FILE__, __FUNCTION__, "ibiza_xml:\n$ibiza_xml");



  if(! Is_empty_str($ibiza_xml)) {
    $ibizawsdl = get_ibiza_server($irfToken, $base, $comptable);

    list($status, $cpt_rendu_elem) = ibiza_import_elem($cab_dossier, $ibiza_xml, $base, $irfToken, $ibizawsdl, $curltrace_path);

    $message_fam .= "<BR>\nEnvoie vers ibiza\n<BR>Resultat:\n";
    $message =  "<BR>\nEnvoie vers ibiza\n$ibiza_xml\n Resultat status=$status: $cpt_rendu_elem\n";
    $fam_done=array();

    foreach($all_csv_elem as $csv_elem) {
      $fam_id = $csv_elem['famille'];
      if( ! isset($fam_done[$fam_id]) ) $fam_done[$fam_id]=0;
      if($fam_done[$fam_id] != 1) {
        $fam_done[$fam_id]=1;
	if($status == 0) {
          $message_clt = "Envoie vers Ibiza OK\n";
          launch_exportbon($message_clt, $cpt_rendu_elem."\n".$ibiza_xml, $_GET['base'], $fam_id);
        } else {
          $message_clt = "Erreur envoie vers Ibiza\n";
          if(preg_match('/Value\scannot\sbe\snull/', $cpt_rendu_elem)) {
            $cpt_rendu_elem="\nMerci d'indiquer le irfToken du cabinet Comptable";
            $status_general=2;
          } else if(preg_match('/connexion\sau\sserveur\sIbiza/', $cpt_rendu_elem)) {
            $cpt_rendu_elem="\nErreur de connexion au serveur Ibiza ou erreur écriture. Merci de re-essayer";
            $status_general=2;
          } else {
            $cpt_rendu_elem="\nAutre Erreur Ibiza: ".$cpt_rendu_elem;
            $status_general=1;
          }
          $message_clt .= "$cpt_rendu_elem\n";
          launch_exportko($message_clt, $cpt_rendu_elem."\n".$ibiza_xml, $_GET['base'], $fam_id);
        }
      }
    }


  } else {
    $fam_done=array();
    foreach($all_csv_elem as $csv_elem) {

       $fam_id = $csv_elem['famille'];
      if(!Is_empty_str($fam_id)) {
        if($fam_done[$fam_id] != 1) {
          $fam_done[$fam_id] = 1;
          $message_fam = "Erreur generation export: Aucune ligne XML pour cet élément $fam_id";
          launch_exportko($message_fam, $message_fam, $_GET['base'], $fam_id);
        }
      }
    }
  }
  rm_file($curltrace_path);
  return $message;
}

function export_ecritures_lettrees($all_csv_elem, $total_let, $let2fam, $fin_exercice, $debut_exercice, $base, $dos, $exportmode, $long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $irfToken) {


  $message="";

  $fam_done=$parfam=array();
  foreach($all_csv_elem as $csv_elem) {
    $parfam[$csv_elem['famille']][]=$csv_elem;
  }

  $tmp_arr=array();
  foreach($total_let as $let=>$val) {
    if($val ==0) {

      verbose_str_to_file(__FILE__, __FUNCTION__, "lettrage ok $let\n".print_r($let2fam[$let],1));
      $csv_to_export=array();
      foreach($let2fam[$let] as $fam) {
        if( ! isset($fam_done[$fam])) $fam_done[$fam]=0;
        if($fam_done[$fam]!=1){
          verbose_str_to_file(__FILE__, __FUNCTION__, "lettrage ok pour fam $fam\n ==> ".print_verb_csvelem($parfam[$fam],1));
          foreach($parfam[$fam] as $csv_elem) {
            $csv_to_export[]=$csv_elem;
            $fam_done[$fam]=1;
          }
        }
      }

      if($exportmode == 34) {
        verbose_str_to_file(__FILE__, __FUNCTION__, "csv_to_export vers ibiza avec $exportmode \n".print_verb_csvelem($csv_to_export,1));
        $message = exportTOibiza($csv_to_export, $fin_exercice, $debut_exercice, $base, $dos, $exportmode, $long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $irfToken);
        verbose_str_to_file(__FILE__, __FUNCTION__, "$message\n");
      } else {
        $fam_done[$fam]=0;
      }

    }
  }

  $tmp_arr=array();
  foreach($all_csv_elem as $csv_elem) {
    $fam=$csv_elem['famille'];

    if((! isset($fam_done[$fam])) || ($fam_done[$fam]!=1) ) {
      foreach($parfam[$fam] as $csv_elem) {
        //$message .=  "ajout: ".print_verb_csvelem(array($csv_elem),1);
        $tmp_arr[]=$csv_elem;
        $fam_done[$fam]=1;
      }
    }
  }
  verbose_str_to_file(__FILE__, __FUNCTION__, "ecritures sans lettrage restant a exporter\n: ".print_verb_csvelem($tmp_arr,1));

  return array($tmp_arr, $message);
}

function filtre_par_ttc($all_csv_elem) {

  $parfam=array();
  foreach($all_csv_elem as $csv_elem) {
    $parfam[$csv_elem['famille']][]=$csv_elem;
  }

  $tmp_fam=$fam_done=array();
  foreach($parfam as $fam=>$listfam) {
    $found_one=0;
    $listfam_tmp=array();
    foreach($listfam as $csv_elem) {
      if($csv_elem['position']==1) $found_one=1;
      if(!isset($csv_elem['quadra_contre']))$csv_elem['quadra_contre']=null;
      if( preg_match('/^\s*6|7/', $csv_elem['num_cpt']) && Is_empty_str($csv_elem['quadra_contre']) ) {
        $csv_elem['quadra_contre']=$csv_elem['num_cpt'];
        $csv_elem['quadra_contretva']=$csv_elem['code_tva'];
      }
      $listfam_tmp[]=$csv_elem;
    }



    if($found_one==0) {
      $fam_id=$csv_elem['famille'];
      if($fam_done[$fam_id] != 1) {
        $fam_done[$fam_id] = 1;
        $message_fam = "Erreur: Ligne d'ecriture auxiliaire non trouvee";
        $cmd = launch_exportko($message_fam, $message_fam, $_GET['base'], $fam_id);
        $message .=  "export ko sur $fam_id \n$cmd\n";
      }
    } else $tmp_fam[$fam]=$listfam;
  }

  $message="Classement par famille \n";
  foreach($tmp_fam as $fam=>$listfam){
    $message .= "famille $fam \n".print_verb_csvelem($listfam,1)."\n";
  }
  verbose_str_to_file(__FILE__, __FUNCTION__, "$message\n");

  $res=array();
  $parfam = $tmp_fam;
  foreach($parfam as $fam=>$listfam){

    if($listfam[0]['type_element']=='banque') {
      if( ! preg_match('/^\s*51/', $listfam[0]['num_cpt']) ){
        $message .=  "cas non preg 512".print_r($listfam,1);
        for($if=count($listfam)-1;$if>-1;$if--){
          if($if==(count($listfam)-1)) {
            $listfam[$if]['position']=1;
            $listfam[$if]['pos_txt']='TTC';
          } else {
            $listfam[$if]['position']=2;
            $listfam[$if]['pos_txt']='HT';
          }
          $res[]=$listfam[$if];
        }
      } else {
        for($if=0;$if<count($listfam);$if++) {
          $res[]=$listfam[$if];
        }
      }

    } else {
      $pos_un=-1;
      for($if=0;$if<count($listfam);$if++) {
        if($listfam[$if]['position'] == 1) {
          $res[]=$listfam[$if];
          $pos_un=$if;
        }
      }
      for($if=0;$if<count($listfam);$if++) {
        if($if != $pos_un) $res[]=$listfam[$if];
      }
    }
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "all_csv_elem apres rangement TTC en premiere ligne\n".print_verb_csvelem($res,1));

  return $res;
}

function filtre_par_lettrage($all_csv_elem, $total_let, $let2fam) {


  verbose_str_to_file(__FILE__, __FUNCTION__, "Ecritures a filtrer\n".print_verb_csvelem($all_csv_elem)."\n");
  verbose_str_to_file(__FILE__, __FUNCTION__, "Sommes des debit credit par lettrage\n".print_r($total_let,1)."\n");

  verbose_str_to_file(__FILE__, __FUNCTION__, "lettrage par famille\n".print_r($let2fam,1)."\n");

  $csv_ko=array();
  foreach($total_let as $let=>$val) {
    if($val !=0) {
      if(isset($let2fam[$let])) {
        foreach($let2fam[$let] as $fam) {
          $csv_ko[$fam]=1;
        }
      }
    }
  }
  verbose_str_to_file(__FILE__, __FUNCTION__, "Filtre sur lettrage non equilibre\n".print_r($csv_ko,1)."\n");

  $tmp_fam=$fam_done=array();
  foreach($all_csv_elem as $csv_elem) {

    if((isset($csv_ko[$csv_elem['famille']]))&&($csv_ko[$csv_elem['famille']]==1)) {
      $fam_id = $csv_elem['famille'];
      if(!Is_empty_str($fam_id)) {
        if( ! isset($fam_done[$fam_id])) $fam_done[$fam_id]=0;
        if($fam_done[$fam_id] != 1) {
          $fam_done[$fam_id] = 1;
          $message_fam = "ERREUR: Lettrage des comptes auxiliaires non equilibre";
          $cmd = launch_exportko($message_fam, $message_fam, $_GET['base'], $fam_id);

        }
      }
    } else $tmp_fam[]=$csv_elem;
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "apres filtre sur lettrage non equilibre  \n".print_verb_csvelem($tmp_fam,1)."\n");


  return $tmp_fam;

}

function unlock_pour_test($all_csv_elem) {

  $message = "unlock all pour test\n";
  $tmp_fam=$fam_done=array();
  foreach($all_csv_elem as $csv_elem) {
    $fam_id = $csv_elem['famille'];
    if(!Is_empty_str($fam_id)) {
      if(! isset($fam_done[$fam_id])) $fam_done[$fam_id]=0;
      if($fam_done[$fam_id] != 1) {
        $fam_done[$fam_id] = 1;
        $message_fam = "Unlock automatique, les ventes et achats doivent etre exportes separemment";
        $cmd = launch_exportko($message_fam, $message_fam, $csv_elem['base'], $fam_id);
        $message .= "export ko sur $fam_id \n$cmd\n";
      }
    }
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, $message);

}

function curl_prod_nv($target_url, $post_content=null, $regexp_attendu=null) {

  // Attention: envoyer les fichier par l'argument -F
  //curl -X POST --data-binary "@upload/post_0472270001617873832_9062219.1" --data-binary "@upload/post_0472270001617873832_9062219.2" --data-binary "@upload/post_0472270001617873832_9062219.3" --data-binary "@upload/post_0472270001617873832_9062219.4" --data-binary "@upload/post_0472270001617873832_9062219.5" --data-binary "@upload/post_0472270001617873832_9062219.6" --data-binary "@upload/post_0472270001617873832_9062219.7"  'http://localhost:8888/apiFacNote?action=printpost'

  //curl -X POST --data-binary "@/Applications/MAMP/htdocs/V2/upload/curl_data.txt" --data-binary "@/Applications/MAMP/htdocs/V2/upload/curl_data2.txt" -d "parametername=@filename" -d "additional_parm=param2" 'http://localhost:8888/apiFacNote?action=get_curl&action=get_curl&base=FA0907'

  $randval = microtime();
  $randval = str_replace(" ", '', $randval);
  $randval = str_replace(".", '', $randval);
  $randval = $randval."_".rand(10000,9999999);

  $dir_curl = LOGDIR;


  $idx_file=$idx_key=0;
  $post_args="";
  $verbose="post envoye:\n";
  foreach($post_content as $key=>$val) {
    if(!Is_empty_str($val)) {
      if( preg_match('/_path/',$key) && (file_exists($val))) {
        $post_args .= "-F \"file$idx_key=@$val\" ";
        $idx_key++;
      } else {
        $idx_file++;
        $content_path="$dir_curl/post_$randval.$idx_file";
        file_put_contents($content_path, $val);
        //$post_args .= "--data-binary \"@$content_path\" ";
        if(file_exists($content_path) && (filesize($content_path)>0))
          $post_args .= "-F \"$key=<$content_path\" ";
      }
    }
    $verbose .= "$key =>";
    $verbose .= substr($val,0,1000);
    $verbose .="\n";
  }
  $sh_cmd = "/usr/bin/curl -s -X POST $post_args '".trim($target_url)."'";

  verbose_str_to_file(__FILE__, __FUNCTION__, "Lancement sh:  $sh_cmd \n");
  verbose_str_to_file(__FILE__, __FUNCTION__, $verbose);
  list($output, $status) = launch_system_command($sh_cmd,0,1);
  verbose_str_to_file(__FILE__, __FUNCTION__, "status:$status et message:".print_r($output,1));
  $message = implode("\n", $output);

  if(preg_match($regexp_attendu, $message)) {
    verbose_str_to_file(__FILE__, __FUNCTION__, "return TRUE car regexp_attendu $regexp_attendu trouvee\n");
    rm_file($content_path);
    return TRUE;
  } else {
    verbose_str_to_file(__FILE__, __FUNCTION__, "return FALSE car regexp_attendu $regexp_attendu NON trouvee\n");
    return FALSE;
  }
}

function launch_exportko($message_fam, $postcontent, $base, $fam_id) {

  if($message_fam != $postcontent) $postcontent .= "\n$message_fam\n";

  $status = curl_prod_nv("https://www.cabinet-expertcomptable.com/ecritures/exportEcriture?base=".$base."&famille=".$fam_id."&action=export_ko", array('content'=>$postcontent), '/EXPORT\s+KO/');

  // if( ! $status) // traiter ici en cas d'erreur sur unlock


}
function launch_exportbon($message_fam, $postcontent, $base, $fam_id) {

  if($message_fam != $postcontent) $postcontent .= "\n$message_fam\n";

  $status = curl_prod_nv("https://www.cabinet-expertcomptable.com/ecritures/exportEcriture?base=".$base."&famille=".$fam_id."&action=export_bon", array('content'=>"Import terminé avec succès"), '/EXPORT\s+OK/');

  // if( ! $status) // traiter ici en cas d'erreur sur unlock


}


require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function array_to_excel_NEW($data, $path, $export_mode) {

  verbose_str_to_file(__FILE__, __FUNCTION__, "create excel file $path whith ".print_r($data,1));

  $csv_txt="";
  foreach($data as $csv_arr) {
    foreach($csv_arr as $csv_cel) {
      if(preg_match('?/?', $csv_cel)) $csv_cel = "'$csv_cel";
      $csv_txt .= '"'.$csv_cel.'"'."\t";
    }
    $csv_txt .= "\n";
  }

  file_put_contents($path.".tsv", $csv_txt);

  list($output, $status) = launch_system_command("ssconvert $path.tsv $path",0,1);

  verbose_str_to_file(__FILE__, __FUNCTION__, "ssconvert $status ".print_r($output,1));
  // $spreadsheet = new Spreadsheet();
  // $sheet = $spreadsheet->getActiveSheet();

  // $sheet->fromArray($data, NULL, 'A1');
  // $writer = new Xlsx($spreadsheet);
  // $writer->save($path);
}

function array_to_excel($data, $path, $export_mode) {


  include dirname(__FILE__).'/Classes/PHPExcel.php';
  include dirname(__FILE__).'/Classes/PHPExcel/Writer/Excel5.php';
  include dirname(__FILE__).'/Classes/PHPExcel/Writer/Excel2007.php';

  if(preg_match('/FB4667XX/', $path)) {
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
  }
  $trace_path = dirname(__FILE__)."/../../FA0001/upload/securite/constructexport_ech.txt";
  //write_roll_logfile($trace_path, "recu pour $path avec export_mode=$export_mode ".print_r($data,1), 2000);

  PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

  verbose_str_to_file(__FILE__, __FUNCTION__, "create excel file $path whith ".print_r($data,1));

  if(preg_match('/FB4667/', $path)) file_put_contents("/home/www/www/FB4667/traces/export", "create excel file $path whith ".print_r($data,1), FILE_APPEND);

  //verbose_str_to_file(__FILE__, __FUNCTION__, "avec ".print_r($data,1));
  if(is_array($data)) {
    $tmp_arr=array();
    $highestRow=count($data);
    for ($row = 0; $row < $highestRow; ++$row) {
      $highestColumnIndex=count($data[$row]);
      for ($col = 0; $col < $highestColumnIndex; ++$col) {
        $tmp_arr[$row][$col]=preg_replace('/^\s+/', '', $data[$row][$col]);
        $tmp_arr[$row][$col]=preg_replace('/\s+$/', '', $tmp_arr[$row][$col]);
      }
    }
    $data=$tmp_arr;
    verbose_str_to_file(__FILE__, __FUNCTION__, "create excel file $path whith ".print_r($data,1));

    date_default_timezone_set('Europe/London');
    $letters = array('A', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

    $workbook = new PHPExcel;
    $sheet=$workbook->getActiveSheet();
    $sheet->setTitle('Feuille1');

    if($export_mode==1){
      $range = 'A1:E'.count($data);
      $sheet->getStyle($range)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
      $range = 'H1:K'.count($data);
      $sheet->getStyle($range)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
      //$range = 'H1:J'.count($data);
      //$sheet->getStyle($range)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    }
    $sheet->fromArray($data, NULL, 'A1');
    if(preg_match('/FB4667/', $path)) file_put_contents("/home/www/www/FB4667/traces/export", "sheet ok", FILE_APPEND);
    //$writer = new PHPExcel_Writer_Excel5($workbook);
    $writer = new PHPExcel_Writer_Excel2007($workbook);
    if(preg_match('/FB4667/', $path)) file_put_contents("/home/www/www/FB4667/traces/export", "writer ok", FILE_APPEND);
    $path = str_replace("FA0025/core/../../", "", $path);
    $writer->save($path);


    if(preg_match('/FB4667/', $path)) file_put_contents("/home/www/www/FB4667/traces/export", "save ok to $path\n", FILE_APPEND);
  } else {
    verbose_str_to_file(__FILE__, __FUNCTION__, "data doit etre un tableau");
  }

}
function xml_to_plancpt($xml_content, $params_export, $curltrace_path) {

  $plan_gene=$plan_frs=$plan_clts=array();
  $frs_cpt=$clt_cpt=$gen_cpt=array();
  $collectif_frs=$collectif_clt="";
  $tmp_str=$tmp_str_frs=$tmp_str_clt=$tmp_str_gene="";
  $arr_res = xml_level3_to_array($xml_content);
  foreach($arr_res as $res){
    $tmp_arr = array(
      "libcompte" => strtoupper(clean_file_name($res['NUMBER'],0,1)),
      "description" => strtoupper(clean_file_name($res['NAME'],0,1)),
      "cpt_assoc"=> strtoupper(clean_file_name($res['ASSOCIATE'],0,1)),
    );


    $type=0;
    if($res['CLOSED']==0) {
      if($res['CATEGORY']==1) $type=2;
      else if($res['CATEGORY']==2) $type=1;
      else if($res['CATEGORY']>0) $type=3;
    }
    // if(preg_match('/^\s*$/', $res['COLLECTIF'])) $type=3;
    // else if( ( ! preg_match('/^\s*$/', $res['COLLECTIF'])) &&  ($res['CATEGORY']==1) ) $type=2;
    // else if( ( ! preg_match('/^\s*$/', $res['COLLECTIF'])) &&  ($res['CATEGORY']==2) ) $type=1;
		// else if(( ! Is_empty_str($params_export['prefix_ac'])) && (preg_match('/^\s*'.$params_export['prefix_ac'].'/', $tmp_arr['libcompte'])) && (! preg_match('/^\s*(6|7|44)/', $tmp_arr['libcompte'])) ) $type=1;
		// else if(( ! Is_empty_str($params_export['prefix_ve'])) && (preg_match('/^\s*'.$params_export['prefix_ve'].'/', $tmp_arr['libcompte'])) && (! preg_match('/^\s*(6|7|44)/', $tmp_arr['libcompte']))) $type=2;
    // else if(preg_match('/^\s*401[A-Z]/', $tmp_arr['libcompte'])) $type=1;
		// else if(preg_match('/^\s*F/', $tmp_arr['libcompte'])) $type=1;
		// else if(preg_match('/^\s*411[A-Z]/', $tmp_arr['libcompte'])) $type=2;
		// else if(preg_match('/^\s*C/', $tmp_arr['libcompte'])) $type=2;
		// else if(preg_match('/^\s*9/', $tmp_arr['libcompte'])) $type=1;
		// else if(preg_match('/^\s*0/', $tmp_arr['libcompte'])) $type=2;
    // else if(preg_match('/^\s*\d+\s*$/', $tmp_arr['libcompte'])) $type=3;

    if($type==1) {$plan_frs[] = $tmp_arr;$tmp_str_frs.=" Res pour frs :\n".print_r($res,1); $frs_cpt[]=$tmp_arr['libcompte'];if( ! preg_match('/^\s*$/', $res['COLLECTIF'])) $collectif_frs=$res['COLLECTIF'];}
    else if($type==2) {$plan_clts[] = $tmp_arr;$tmp_str_clt.=" Res pour clt :\n".print_r($res,1);$clt_cpt[]=$tmp_arr['libcompte'];if( ! preg_match('/^\s*$/', $res['COLLECTIF'])) $collectif_clt=$res['COLLECTIF'];}
    else if($type==3) {$plan_gene[] = $tmp_arr;$tmp_str_gene .= " Res pour gene :\n".print_r($res,1);$gen_cpt[]=$tmp_arr['libcompte'];}
  }

  $long_cpt_gen_ac = strlen($collectif_frs);
  $long_cpt_gen_ve = strlen($collectif_clt);
  $collectif_frs = preg_replace('/0+\s*$/', '', $collectif_frs);
  $collectif_clt = preg_replace('/0+\s*$/', '', $collectif_clt);
  $params_export_found=array();
  if($long_cpt_gen_ve>0)$params_export_found['long_aux_ve']=$long_cpt_gen_ve;
  if($long_cpt_gen_ac>0)$params_export_found['long_aux']=$long_cpt_gen_ac;
  if(strlen($plan_gene[0]['libcompte'])>0) $params_export_found['long_cpt_gen_ac']=$params_export_found['long_cpt_gen_ve']=$params_export_found['long_cpt']=strlen($plan_gene[0]['libcompte']);

  if(! preg_match('/^\s*$/', $collectif_frs))$params_export['prefix_ve']=$collectif_frs;
  if(! preg_match('/^\s*$/', $collectif_clt))$params_export['prefix_ac']=$collectif_clt;

  verbose_str_to_file(__FILE__, __FUNCTION__, "plan_frs ".print_r($plan_frs[0],1).print_r($plan_frs[1],1).print_r($plan_frs[2],1).print_r($plan_frs[3],1).print_r($plan_frs[4],1));

  if( ! Is_empty_str($curltrace_path)) {
    $tmp_str.="params_export ".print_r($params_export,1);
    $tmp_str.="plan_frs recu ".substr($tmp_str_frs,0,500)."\nRes: ".print_r($plan_frs[0],1).print_r($plan_frs[1],1).print_r($plan_frs[2],1).print_r($plan_frs[3],1).print_r($plan_frs[4],1);
    $tmp_str.="plan_clt  recu ".substr($tmp_str_clt,0,500)."\nRes: ".print_r($plan_clts[0],1).print_r($plan_clts[1],1).print_r($plan_clts[2],1).print_r($plan_clts[3],1).print_r($plan_clts[4],1);
    $tmp_str.="plan_gene  recu ".substr($tmp_str_gene,0,500)."\nRes: ".print_r($plan_gene[0],1).print_r($plan_gene[1],1).print_r($plan_gene[2],1).print_r($plan_gene[3],1).print_r($plan_gene[4],1);
    file_put_contents($curltrace_path, "\n\n******************************************\n\n**** Traces xml_to_plancpt\n******************************************\n\n$tmp_str\n", FILE_APPEND);
  }


  return array($plan_frs,$plan_clts, $plan_gene, $params_export_found);
}

function xml_to_balance($xml_content, $mode_journal) {
  $arr_res = xml_level3_to_array($xml_content);
  $frs_to_cpt=array();
  foreach($arr_res as $res) {
    if(preg_match('/^\s*6|7/', $res['NUMBER'])) $frs_to_cpt[strtoupper(clean_file_name($res['NAME'],0,1))]['general'] = strtoupper(clean_file_name($res['NUMBER'],0,1));
    else if(preg_match('/^\s*4/', $res['NUMBER'])) $frs_to_cpt[strtoupper(clean_file_name($res['NAME'],0,1))]['tva'] = strtoupper(clean_file_name($res['NUMBER'],0,1));
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "frs_to_cpt ".print_r($frs_to_cpt,1));

  return array($frs_to_cpt);
}
function xml_to_gl_name_number($xml_content, $mode_journal) {
  $arr_res = xml_level3_to_array($xml_content);
  $frs_to_cpt=array();
  foreach($arr_res as $res) {
    if(preg_match('/^\s*6/', $res['NUMBER']) && preg_match('/^\s*\d+\s*$/', $res['NUMBER']) ) $frs_to_cpt[strtoupper(clean_file_name($res['NAME'],0,1))]['general'] = strtoupper(clean_file_name($res['NUMBER'],0,1));
    else if(preg_match('/^\s*4/', $res['NUMBER']) && preg_match('/^\s*\d+\s*$/', $res['NUMBER'])) $frs_to_cpt[strtoupper(clean_file_name($res['NAME'],0,1))]['tva'] = strtoupper(clean_file_name($res['NUMBER'],0,1));
    else $frs_to_cpt[strtoupper(clean_file_name($res['NAME'],0,1))]['autre'] = strtoupper(clean_file_name($res['NUMBER'],0,1));
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "frs_to_cpt ".print_r($frs_to_cpt,1));

  return array($frs_to_cpt);
}

function xml_to_journal($xml_content, $curltrace_path) {

  $tmp_str="recu \n".substr($xml_content,0,500);

  $arr_res = xml_level3_to_array($xml_content);

	$field_to_keep=array('COLLECTIVENAME', 'COLLECTIVENUMBER', 'NAME', 'NATUREAUXI', 'NUMBER', 'REF');
  $journaux=array();

  $i=0;
  foreach($arr_res as $res){
    $tmp_res=array();
    foreach($field_to_keep as $field){
      $tmp_res[$field]=null;
      if(isset($res[$field]))
        $tmp_res[$field]=$res[$field];
    }
    $journaux[]=$tmp_res;
    if($i<7) $tmp_str .= " Res pour journal :\n".print_r($res,1);
    $i++;
  }

  if( ! Is_empty_str($curltrace_path)) {
    file_put_contents($curltrace_path, "\n\n******************************************\n\n**** Traces xml_to_journal\n******************************************\n\n$tmp_str\n", FILE_APPEND);
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "journaux array\n".print_r($journaux,1));
  return $journaux;
}
function xml_to_grandLivre($xml_content, $get_all, $curltrace_path) {

  $i=0;
  $tmp_str="";
  $already_done=$chg_info=array();
	$arr_res = explode('<wsGeneralLedger>', $xml_content);
  foreach($arr_res as $wsGeneralLedger){
    $hash_res = xml_to_hash('<wsGeneralLedger>'.$wsGeneralLedger);
    //if(preg_match('/EDF/', $wsGeneralLedger)) echo "$wsGeneralLedger\n".print_r($hash_res,1);
    $date = date_mysql_to_html($hash_res['DATE']);
    $libelle=$hash_res['DESCRIPTION'];
    $num_fact=$hash_res['VOUCHERREF'];
    $cpt_aux=$hash_res['NUMBER'];
    $debit=$hash_res['DEBIT'];
    $credit=$hash_res['CREDIT'];
    $code_j=$hash_res['REF'];
    $montant=formater_montant($debit+$credit);
    $tmp_arr = array('date'=>$date, 'description'=>$libelle,'num_piece'=>$num_fact, 'num_cpt'=>$cpt_aux, 'debit'=>$debit, 'credit'=>$credit, 'code_j'=>$code_j,
                     'created_at'=>$date, 'detail'=>$libelle,'num_fact'=>$num_fact, 'prix_ttc'=>$montant, 'code_j'=>$code_j,
    );


    if($i<7) $tmp_str .= " recu :\n".print_r($hash_res,1)."ecriture ".print_r($tmp_arr,1);
    $i++;
    //if($get_all==1)$chg_info[] = $tmp_arr;
    //else
    if( ($already_done[$date.$libelle.$num_fact.$cpt_aux.$debit.$credit.$code_j] != 1) &&($montant>0) )$chg_info[] = $tmp_arr;
    $already_done[$date.$libelle.$num_fact.$cpt_aux.$debit.$credit.$code_j]=1;

    $wsGeneralLedger_arr[]=$hash_res;
  }

	verbose_str_to_file(__FILE__, __FUNCTION__, "wsGeneralLedger_arr array\n".print_r($wsGeneralLedger_arr[0],1).print_r($wsGeneralLedger_arr[1],1).print_r($wsGeneralLedger_arr[2],1).print_r($wsGeneralLedger_arr[2],1).print_r($wsGeneralLedger_arr[50],1).print_r($wsGeneralLedger_arr[100],1));
	//echo "wsGeneralLedger_arr array\n".print_r($wsGeneralLedger_arr[0],1).print_r($wsGeneralLedger_arr[1],1).print_r($wsGeneralLedger_arr[2],1).print_r($wsGeneralLedger_arr[2],1).print_r($wsGeneralLedger_arr[50],1).print_r($wsGeneralLedger_arr[100],1);

  if( ! Is_empty_str($curltrace_path)) {
    file_put_contents($curltrace_path, "\n\n******************************************\n\n**** Traces xml_to_grandLivre\n******************************************\n\n$tmp_str\n", FILE_APPEND);
  }

	return $chg_info;
}
function xml_level3_to_array($xml_content, $field_cut='xxx', $change_arr_level=3, $items_level=4) {
  //echo "get source:\n$xml_content\n";
	$arr_res=array();
	$p = xml_parser_create();
	verbose_str_to_file(__FILE__, __FUNCTION__, "xml_parser_create strlen xml_content\n".strlen($xml_content));

	if(strlen($xml_content)>3000000){
		verbose_str_to_file(__FILE__, __FUNCTION__, "ATTENTION xml_parse_into_struct n'accepte pas plus de 3000000octes \n".strlen($xml_content));
		$tmp_str="";

		if(Is_empty_str($field_cut)) $field_cut='xxx';
		$tmp_arr=explode($field_cut, $xml_content);
		for($i=1; $i<count($tmp_arr);$i++) {
			if($i<3) verbose_str_to_file(__FILE__, __FUNCTION__, "traite line:\n".$tmp_arr[$i]);

				$tmp_arr_elem = str_split($tmp_arr[$i],1);
				$tmp_arr_res=array();
				$key="";
				for($j=0; $j<count($tmp_arr_elem);$j++) {

					if($tmp_arr_elem[$j]=='<') {
						$j++;
						//if($i<3) verbose_str_to_file(__FILE__, __FUNCTION__, "ok <:".$tmp_arr_elem[$j].$tmp_arr_elem[$j+1].$tmp_arr_elem[$j+2]);

						$key="";
						while( ($tmp_arr_elem[$j] !=">") && ($j<count($tmp_arr_elem))){
							$key .= $tmp_arr_elem[$j];
							$j++;
						}
						$j++;
						$val="";
						while( ($tmp_arr_elem[$j] !="<") && ($j<count($tmp_arr_elem))){
							$val .= $tmp_arr_elem[$j];
							$j++;
						}
						if($i<3) verbose_str_to_file(__FILE__, __FUNCTION__, "get tmp_arr_res[$key] = $val");

						if( (! Is_empty_str($key)) && ($key != "debit") && ($key != "credit") && ($key != "entryQty") && ($key != "entryUnit/") && ($key != "credit") && (! Is_empty_str($val)) &&
							 ($val != "Tous les axes") && ($val != "Compte sans saisie analytique") && ($val != "Non affectable") ){
							$tmp_arr_res[$key] = $val;
						}
					}
				}

				//if( (! Is_empty_str($tmp_arr_res['number'])) )
				$arr_res[]=$tmp_arr_res;
		}
	}
	else xml_parse_into_struct($p, $xml_content, $vals, $index);
	//verbose_str_to_file(__FILE__, __FUNCTION__, "xml val index".print_r($vals,1).print_r($index,1));

	$trace_path = dirname(__FILE__)."/../upload/arr_res.txt";

	verbose_str_to_file(__FILE__, __FUNCTION__, "trace $trace_path xml_parse_into_struct strlen xml_content\n".strlen($xml_content));
	xml_parser_free($p);

	verbose_str_to_file(__FILE__, __FUNCTION__, "vals array\n".print_r($vals[0],1).print_r($vals[1],1).print_r($vals[2],1));
  $idx_cpt=0;
  if(Is_empty_str($change_arr_level)) $change_arr_level=3;
  if(Is_empty_str($items_level)) $items_level=4;

  for($idx=0; $idx<count($vals); $idx++) {

    $value=""; if(isset($vals[$idx]['value'])) $value=$vals[$idx]['value'];

    if( $vals[$idx]['level'] == $change_arr_level) $idx_cpt++;
    if( $vals[$idx]['level'] == $items_level)$arr_res[$idx_cpt][$vals[$idx]['tag']] = $value;
  }
	$tmp_str=array();
	foreach($arr_res as $key=>$val){
		if(count($val)>0) $tmp_str[]=$val;
	}
	$arr_res=$tmp_str;
	verbose_str_to_file(__FILE__, __FUNCTION__, "arr_res array\n".print_r($arr_res,1));

	return $arr_res;
}

function ibiza_get_plancpt($db_clt, $base, $prefix_ac, $prefix_ve, $irfToken, $ibizawsdl, $curltrace_path) {

  $params_export=array();
  $params_export['prefix_ac']=$prefix_ac;
  $params_export['prefix_ve']=$prefix_ve;

  $url = "$ibizawsdl/company/$db_clt/accounts";
  list($response,$httpCode) = launch_ibiza_curl($base, $url, 0, null, $irfToken,$curltrace_path);
  list($plan_frs,$plan_clts,$plan_gene, $params_export_found) = xml_to_plancpt($response, $params_export,$curltrace_path);
  verbose_str_to_file(__FILE__, __FUNCTION__, "plan_frs ".print_r($plan_frs[0],1).print_r($plan_frs[1],1).print_r($plan_frs[2],1).print_r($plan_frs[3],1).print_r($plan_frs[4],1));

  $url = "$ibizawsdl/company/$db_clt/journals";
  list($response,$httpCode) = launch_ibiza_curl($base, $url, 0, null, $irfToken, $curltrace_path);
	$tmp_elem = xml_to_journal($response, $curltrace_path);
	$journaux=array();
  $csv_res="JOURNAUX\nCode;Type;Description;Longeur;prefix\n";
	foreach($tmp_elem as $elem) {
		$journaux[$elem['REF']] = $elem['REF'];
    $closed=""; if(isset($elem['CLOSED'])) $closed=$elem['CLOSED'];
    $ref=""; if(isset($elem['REF'])) $ref=$elem['REF'];
    $type=""; if(isset($elem['TYPE'])) $type=$elem['TYPE'];
    $description=""; if(isset($elem['DESCRIPTION'])) $description=$elem['DESCRIPTION'];
    if($closed != 1) $csv_res.=$ref.";".$type.";".$description.";\n";
	}

  $url = "$ibizawsdl/company/$db_clt/thirdparty";
  list($response,$httpCode) = launch_ibiza_curl($base, $url, 0, null, $irfToken, $curltrace_path);
  $thirdparty = xml_to_journal($response,$curltrace_path);
  $num2collectiv=array();
  foreach($thirdparty as $plan){
    $num2collectiv[$plan['NUMBER']]=$plan['COLLECTIVENUMBER'];
  }
  for($i=0; $i<count($plan_frs);$i++) {
    $plan_frs[$i]['compteCollectif']=$num2collectiv[$plan_frs[$i]['libcompte']];
  }
  for($i=0; $i<count($plan_clts);$i++) {
    $plan_clts[$i]['compteCollectif']=$num2collectiv[$plan_clts[$i]['libcompte']];
  }
  verbose_str_to_file(__FILE__, __FUNCTION__, "plan_frs ".print_r($plan_frs[0],1).print_r($plan_frs[1],1).print_r($plan_frs[2],1).print_r($plan_frs[3],1).print_r($plan_frs[4],1));
  verbose_str_to_file(__FILE__, __FUNCTION__, "plan_clts ".print_r($plan_clts[0],1).print_r($plan_clts[1],1).print_r($plan_clts[2],1).print_r($plan_clts[3],1).print_r($plan_clts[4],1));


  $url = "$ibizawsdl/company/$db_clt/Exercices";
  list($response,$httpCode) = launch_ibiza_curl($base, $url, 0, null, $irfToken, $curltrace_path);
	$tmp_elem = xml_level3_to_array($response);
	$exercices=array();
	foreach($tmp_elem as $elem) {
		if($elem['STATE'] != 2) {
      $exercices[$elem['END']] = $elem['START'];
      $csv_res="EXERCICE\ndebut;fin\n".$elem['START'].";".$elem['END'];
    }
	}

	verbose_str_to_file(__FILE__, __FUNCTION__, "plan_frs ".print_r($plan_frs[0],1)."plan_clts ".print_r($plan_clts,1)."journaux ".print_r($journaux,1)."exercices ".print_r($exercices,1));

  return array($plan_frs,$plan_clts,$journaux, $exercices, $plan_gene);
}

function print_verb_csvelem($array_to_print, $mode_light=1) {


  $long=6;
  if($mode_light==1) $long=20;

  $res_str="";

  if(isset($array_to_print[0])) {
    foreach($array_to_print[0] as $key=>$val) {
      $afficher=1;
      if( ($mode_light==1) && ( ! (($key=='base')||($key=='date')||($key=='famille')||($key=='code_j')||($key=='lettrage')||($key=='num_cpt')||($key=='debit')||($key=='code_lib')||($key=='credit')||($key=='description')) ) ) $afficher=0;
      if($afficher==1) $res_str .= "|".substr(str_pad($key, $long, " ", STR_PAD_RIGHT),0, $long);
    }
  }
  $res_str .= "\n";

  foreach($array_to_print as $csv_arr) {
    foreach($csv_arr as $key=>$val) {
      $afficher=1;
      if( ($mode_light==1) && ( ! (($key=='base')||($key=='date')||($key=='famille')||($key=='code_j')||($key=='lettrage')||($key=='num_cpt')||($key=='debit')||($key=='code_lib')||($key=='credit')||($key=='description')) ) ) $afficher=0;
      if($afficher==1){
        if(is_array($val)) $res_str .= "| ".substr(str_pad(implode("-",$val), $long, " ", STR_PAD_RIGHT),0, $long);
        else $res_str .= "|".substr(str_pad($val, $long, " ", STR_PAD_RIGHT),0, $long);
      }
    }
    $res_str .= "\n";
  }


  return $res_str;
}

// librairie spécifique


// recuperation des arguments
$usage = "Usage :
curl -F 'content=</Applications/MAMP/htdocs/V2/core/content.txt' 'http://localhost:8888/V2/core/exports.php?action=constructexport&exportmode=33&base=FA0907&comptable=FA0766&action=constructexport&dos=002&debut_exercice=01-01-2021&fin_exercice=31-12-2021&id_exp=123456&long_cpt=6&long_aux=6&cpt_gen=401000'
";

//scp -P 26022 core/banque.php hchehibi@ns3158938.ip-51-91-104.eu:/home/hchehibi/.; ssh -p 26022 -l hchehibi ns3158938.ip-51-91-104.eu "php /home/hchehibi/banque.php -h"
//file_put_contents(dirname(__FILE__)."/log_lancement", date('Y-d-m H:i:s')."\n".print_r($_POST,1).print_r($_GET,1));

circular_file(LOGPATH, $nb_keep=5);
echo "Traces dans \ncat ".LOGPATH."\n";

$irfToken="";
if(isset($_POST['irfToken'])) $irfToken = $_POST['irfToken'];

$base="";
if(isset($_GET['base'])) $base=$_GET['base'];
$comptable="";
if(isset($_GET['comptable'])) $comptable = $_GET['comptable'];

$comptable="";
if(isset($_GET['comptable'])) $comptable = $_GET['comptable'];

if($_GET['action']=='constructexport') {

  $message="";
  $status=200;

  $export_manuel=0;
  if(isset($_GET['export_manuel'])) $export_manuel = $_GET['export_manuel'];

  $exportmode = 1;
  if(isset($_GET['exportmode'])) $exportmode = $_GET['exportmode'];

  if(($exportmode==40)&&($_GET['comptable']=='FB4798')) $exportmode=6;
  if(($exportmode==40)&&($_GET['comptable']=='FA0766')) $exportmode=6;
  if(($exportmode==7)&&($_GET['comptable']=='FA2733')) $exportmode=46;
  if($_GET['comptable']=='FA2247') {$exportmode=6;}

  verbose_str_to_file(__FILE__, __FUNCTION__, "Recu _GET".print_r($_GET,1)."_POST:".substr($_POST['content'],0,200)."\n");

  $debut_exercice=date_mysql_to_html(date_url_to_mysql($_GET['debut_exercice']));
  $fin_exercice=date_mysql_to_html( date_url_to_mysql($_GET['fin_exercice']));

  $cab_dossier=$_GET['dos'];

  $long_cpt=null;
  if(isset($_GET['long_cpt'])) $long_cpt=$_GET['long_cpt'];
  $long_cpt_gen_ac=null;
  if(isset($_GET['long_cpt_gen_ac'])) $long_cpt_gen_ac=$_GET['long_cpt_gen_ac'];
  $long_cpt_gen_ve=null;
  if(isset($_GET['long_cpt_gen_ve'])) $long_cpt_gen_ve=$_GET['long_cpt_gen_ve'];
  $long_aux=null;
  if(isset($_GET['long_aux'])) $long_aux=$_GET['long_aux'];
  $long_aux_ve=null;
  if(isset($_GET['long_aux_ve'])) $long_aux_ve=$_GET['long_aux_ve'];

  $sa1=null;
  if(isset($_GET['sa1'])) $sa1=$_GET['sa1'];
  $sa2=null;
  if(isset($_GET['sa2'])) $sa2=$_GET['sa2'];
  $sa3=null;
  if(isset($_GET['sa3'])) $sa3=$_GET['sa3'];
  $sa4=null;
  if(isset($_GET['sa4'])) $sa4=$_GET['sa4'];
  $sa5=null;
  if(isset($_GET['sa5'])) $sa5=$_GET['sa5'];
  $etablissement=null;
  if(isset($_GET['etablissement'])) $etablissement=$_GET['etablissement'];

  $auto=null;
  if(isset($_GET['auto'])) $auto=$_GET['auto'];


  $ttc_found=$total_let=$let2fam=array();

  list($all_csv_elem, $total_let, $let2fam) = post_content2csv_elem($fin_exercice, $debut_exercice, $exportmode);

  if($exportmode == 34) {
    $all_csv_elem = filtre_achats_ventes($all_csv_elem);
    $all_csv_elem = filtre_par_lettrage($all_csv_elem, $total_let, $let2fam);
  }

  $all_csv_elem = get_code_lib_fam($all_csv_elem);
  $all_csv_elem=filtre_par_ttc($all_csv_elem);


  if($exportmode == 34) {
    list($all_csv_elem_restant, $message) = export_ecritures_lettrees($all_csv_elem, $total_let, $let2fam, $fin_exercice, $debut_exercice, $base, $cab_dossier, $exportmode,$long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $irfToken);
    $message .= exportTOibiza($all_csv_elem_restant, $fin_exercice, $debut_exercice, $base, $cab_dossier, $exportmode,$long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $irfToken);
  } else {

    list($zip_dir, $zip_dir_name) = get_zipdir_path($all_csv_elem[0]['date'], $base, $cab_dossier, $exportmode);

    all_csv_elem_to_zip($all_csv_elem, $exportmode, $zip_dir, $zip_dir_name, $base,$cab_dossier, $fin_exercice, $debut_exercice,$long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $sa1,$sa2,$sa3,$sa4,$sa5,$etablissement, 1 );




    $file_list = array();
    list($statusT, $messageT, $file_list) = get_dir_content($zip_dir);

    if($exportmode==44) {
      //$zip_dir_name = str_replace(".auto", "", $zip_dir_name);

    } else if($exportmode==41) {
      $zip_dir_name = "$zip_dir_name.zip";
    } else {
      $zip_dir_name = "$zip_dir_name.ZIP";
    }

    verbose_str_to_file(__FILE__, __FUNCTION__, "sortie all_csv_elem avec fichiers \n".print_r($file_list,1));

    if(($export_manuel==1)&&($exportmode==200))  $message .=  "\nPas de zip pour ce mode\n";
    else $message .=  "\nNom du zip: '$zip_dir_name'\n";

    foreach($file_list as $file_name) {
      $file_name_url=$file_name;
      $file_name_url = preg_replace('?/home/www/www?', "http://facnote.com", "$zip_dir/$file_name");
      $file_name_url = preg_replace('?/var/www/prospect.cabinet-expertcomptable.com/www?', "https://prospect.cabinet-expertcomptable.com", "$zip_dir/$file_name");

      if( ($export_manuel==1) && ($exportmode==20) ) $file_name=str_replace(".auto", "", $file_name);
      $afficher=1;
      if(($exportmode==44)&&preg_match('/.auto/', $file_name)) $afficher=0;
      if(($exportmode==20)&&preg_match('/IN.auto/', $file_name)) {
        $file_name_targ = str_replace(".auto", "", $file_name);
        $sh_cmd="mv $zip_dir/$file_name $zip_dir/$file_name_targ";
        list($output, $status) = launch_system_command($sh_cmd,0,1);
        if($status==0) {
          $file_name=$file_name_targ;
          $file_name_url=$file_name;
          $file_name_url = preg_replace('?/home/www/www?', "http://facnote.com", "$zip_dir/$file_name");
          $file_name_url = preg_replace('?/var/www/prospect.cabinet-expertcomptable.com/www?', "https://prospect.cabinet-expertcomptable.com", "$zip_dir/$file_name");
        }
      }

      if($afficher==1){
        $message .=  "\nNom de fichier: '$file_name'\n";
        $message .=  "Lien vers fichier : '$file_name_url'\n";
      }
    }
  }

  verbose_str_to_file(__FILE__, __FUNCTION__, "message retourne:\n$message\n");

  echo $message;

} else if($_GET['action']=='export_rev') {

  $base=$_GET['b'];
  $id_export=$_GET['id_export'];
  if(Is_empty_str($id_export)) $id_export="$base"."_".mktime();

  $trace_path = LOGDIR."/export_auto_$base.txt";

  $res_dir = LOGDIR;
  $zip_name = "tmp_$base.".mktime().".ZIP";
  $zip_path = "$res_dir/$zip_name";

  $url_base="https://www.cabinet-expertcomptable.com/ecritures/exportEcriture?base=$base&id_export=$id_export&action=get";
  list($message,$status) = launch_curl($url_base, $zip_path, '');

  if(filesize($zip_path) < 5){
    $status=400;
    $message = "ZIP vide\n";
    verbose_str_to_file(__FILE__, __FUNCTION__, date("d/m/Y H:i:s"). "ZIP vide\n");
  } else {

    $new_zip_name = "Exp_$base.".mktime();
    $zip_dir = "$res_dir/$new_zip_name";

    list($outputS, $statusS) = launch_system_command("mkdir $zip_dir", 0, 1);
    verbose_str_to_file(__FILE__, __FUNCTION__, date("d/m/Y H:i:s"). "mkdir $zip_dir status=$statusS retour commande: ".print_r($outputS,1)."\n");

    list($output, $status) = launch_system_command("cd $zip_dir; unzip $zip_path");
    verbose_str_to_file(__FILE__, __FUNCTION__, date("d/m/Y H:i:s"). "unzip_arc $zip_path vers $zip_dir"." status=$statusS\n");

    if($statusS == 1) {
      $status=400;
      $message = "Erreur reception, fichier ZIP non conforme\n";
      verbose_str_to_file(__FILE__, __FUNCTION__, "unzip erreur sur $zip_path\n");

      $url_base="https://www.cabinet-expertcomptable.com/ecritures/exportEcriture?base=$base&id_export=$id_export&action=zip_error";
      list($message,$status) = launch_curl($url_base, null, $message);

      if(! preg_match('/200/', $status)) {
        sleep(3);
        list($message,$status) = launch_curl($url_base, null, $message);
      }
    } else {
      $url_base="https://www.cabinet-expertcomptable.com/ecritures/exportEcriture?base=$base&id_export=$id_export&action=zip_ok";
      list($messageZ,$status) = launch_curl($url_base, null, "");
      if(! preg_match('/ZIP\s+OK/', $messageZ)) {
        sleep(3);
        list($messageZ,$status) = launch_curl($url_base, null, $messageZ);
      }

      list($outputS, $statusS) = launch_system_command("rm -rf $zip_dir/__MACOSX", 0, 1);
      list($outputS, $statusS) = launch_system_command("mv $zip_dir/*/* $zip_dir/.", 0, 1);
      list($statusNO, $message, $file_list) = get_dir_content("$zip_dir");
      verbose_str_to_file(__FILE__, __FUNCTION__, date("d/m/Y H:i:s"). "status = $statusNO avec fichiers dans $zip_dir :".print_r($file_list,1)."\n");

      $dir_export_rev = "$base"."_export_rev";
      list($outputS, $statusS) = launch_system_command("mkdir $zip_dir/$dir_export_rev", 0, 1);
      list($outputS, $statusS) = launch_system_command("mv $zip_dir/* $zip_dir/$dir_export_rev/.", 0, 1);
      list($status, $message, $file_list) = get_dir_content("$zip_dir/$dir_export_rev");
      verbose_str_to_file(__FILE__, __FUNCTION__, date("d/m/Y H:i:s"). "contenu $zip_dir/$dir_export_rev ".print_r($file_list,1)."\n");

      foreach($file_list as $file_name) {
        if(is_dir("$zip_dir/$dir_export_rev/$file_name") && (!Is_empty_str($file_name))) {
          list($outputS, $statusS) = launch_system_command("rm -rf $zip_dir/$dir_export_rev/$file_name", 0, 1);
        }
      }

      list($status, $message, $file_list) = get_dir_content("$zip_dir/$dir_export_rev");
      verbose_str_to_file(__FILE__, __FUNCTION__, date("d/m/Y H:i:s"). "contenu $zip_dir/$dir_export_rev ".print_r($file_list,1)."\n");

      $envoie_pj_path = "$zip_dir/$dir_export_rev/envoie_pj.txt";
      $base_a_envoyer = "FA7390 FB0268 FB0269 FB0432 FB0519 FB0522 FB0788 FB0813 FB0837 FB0860 FB0889 FB0921 FB0943 FB0949 FB1000 FB1001 FB1007 FB1012 FB1027 FB1029 FB1030 FB1031 FB1034 FB1035 FB1045 FB1052 FB1053 FB1076 FB1080 FB1088 FB1090 FB1107 FB1168 FB1169 FB1186 FB1203 FB1262";
      //$base_a_envoyer = "FA6056 FA1234 FA0123 FA7390 FB0522";
      $trace .= "Test envoie pj\n";
      if(!Is_empty_str($base)){
        if(preg_match('/'.$base.'/', $base_a_envoyer)) {
          $trace .= "Envoie pj ok\n";
          $cmd = "find /home/www/www//$base/ecritures -type f | grep '.pdf'";
          list($output, $status) = launch_system_command("$cmd",0,1);
          $liens="";
          foreach($output as $file_path) {
            $lien_name = basename($file_path);
            if(preg_match('/^\s*\d+_\d+.pdf\s*$/i', $lien_name)) $liens .= "https://www.cabinet-expertcomptable.com/upload/$base/ecritures/$lien_name\n";
          }
          $trace .= "fichier $envoie_pj_path cree avec $liens\n";
          if(filesize("$zip_dir/$dir_export_rev/familles.nv")>5) file_put_contents($envoie_pj_path, $liens);
        }
      }

      $cmd = "cd $zip_dir; zip ../".basename($zip_dir).".ZIP */*";
      list($outputS, $statusS) = launch_system_command($cmd, 0, 1);
      $trace .= date("d/m/Y H:i:s"). "$cmd\n statusS=$statusS filesize ".filesize($zip_dir.".ZIP").print_r($outputS,1)."\n";

      $list_fam = explode("\n", file_get_contents("$zip_dir/$dir_export_rev/familles.nv"));
      $edate = date('Y-m-d H:i:s');
      $exp_message = "";
      for($if=0; $if<count($list_fam);$if++) {
        $fam = clean_file_name($list_fam[$if]);
        if(! Is_empty_str($fam)) $exp_message .= "Famille $fam OK\n";
      }

      $zipsize = filesize($zip_dir.".ZIP");
      $trace .= date("d/m/Y H:i:s"). "message avec zip size = $zipsize"."\n";
      write_roll_logfile($trace_path, $trace, 2000);
      $status=200;
      $lien_zip = "http://facnote.com/$base/upload/".basename($zip_dir.".ZIP");
      //if(($base=='FA6056')||($base=='FA7449'))
      $message = "$zipsize Liste des familles:\n$exp_message"."xX-Xx F_crul_infos".$lien_zip;
      //else $message = "$zipsize Liste des familles:\n$exp_message"."xX-Xx F_crul_infos".file_get_contents($zip_dir.".ZIP");
    }
  }


} else if($_GET['action']=='ibiza_get_list_clients') {

  // curl -F "content=G07Sgi7L9oc134MBy9+bgwTVRvCC87bJcJoZxcISYOXYZydlv4syR9QVoVV/rWpQnxVsLVqV8Si0a1GIct+o45jaj3VgZ68DtROI2ApoWASjtEJ1EAgRkwULMmEY8FxPSgMvG0er0yRXtyCa7wllVT+Dz/MPvpiqT70j7yLLunpaxRkFr2KtGhLtg781zJzoEV3i64Y2eoZ5F9ulVGEWdxo4qX3cd19D/gBr6roLLn6q5UUuqtDPQhurmNNyZXjX6/YHgY72lkqqg4RXYi9L2G7sHgcQI5T2SsIO4Js9xoVsbmQhTjpBw61nQYLax5ZMCiXzJL/jtaPJQrKfEtmDaA==" 'https://prospect.cabinet-expertcomptable.com/exports.php?action=ibiza_get_list_clients&base=FB0078&comptable=FA0766&trace=1&verbose=0';

  if(!Is_empty_str($_POST['content'])) {
    $irfToken=trim($_POST['content']);
  }

  $ibizawsdl = get_ibiza_server($irfToken, $base, $comptable);
  if(preg_match('/Erreur\s+Ibiza:\s+/', $ibizawsdl)) $message = $ibizawsdl;
  else {
    $list_clt = ibiza_get_list_clients($base, $irfToken, $ibizawsdl);
    //echo "liste ".print_r($list_clt,1);
    $message = "Serveur Ibiza: $ibizawsdl\nResultat Ibiza:\n Nom;Dossier;siren;date creation\n";
    foreach($list_clt as $clt_infos) {
      $message .= $clt_infos['NAME'].";".$clt_infos['DATABASE'].";".$clt_infos['SIREN'].";".$clt_infos['CREATEDATE']."\n";
    }
  }
  verbose_str_to_file(__FILE__, __FUNCTION__, $message);
  echo "$message\n";

} else if($_GET['action']=='array_to_excel') {
  $data=array(array("'31/8/2021",2,3),array("'31/8/2021'",2,3));
  $path="/tmp/toto.xlsx"; // pas bon Microsoft Excel 2007+
  $path=dirname(__FILE__)."/array_to_excel.xls";
  array_to_excelX($data, $path, 1);
  list($output, $status) = launch_system_command("file $path",0,1);

  $path = preg_replace('?/var/www/prospect.cabinet-expertcomptable.com/www?', "https://prospect.cabinet-expertcomptable.com", $path);
  echo "file $path ".print_r($output,1);

} else if($_GET['action']=='ibiza_get_plan') {

  // curl -F "content=G07Sgi7L9oc134MBy9+bgwTVRvCC87bJcJoZxcISYOXYZydlv4syR9QVoVV/rWpQnxVsLVqV8Si0a1GIct+o45jaj3VgZ68DtROI2ApoWASjtEJ1EAgRkwULMmEY8FxPSgMvG0er0yRXtyCa7wllVT+Dz/MPvpiqT70j7yLLunpaxRkFr2KtGhLtg781zJzoEV3i64Y2eoZ5F9ulVGEWdxo4qX3cd19D/gBr6roLLn6q5UUuqtDPQhurmNNyZXjX6/YHgY72lkqqg4RXYi9L2G7sHgcQI5T2SsIO4Js9xoVsbmQhTjpBw61nQYLax5ZMCiXzJL/jtaPJQrKfEtmDaA==" 'https://prospect.cabinet-expertcomptable.com/exports.php?action=ibiza_get_plan&base=FB0078&comptable=FA0766&prefix_ve=C&prefix_ac=F&dossier=B0BE3918-E69D-4931-8110-8F364FFA5A51&trace=1&verbose=0';

  if(!Is_empty_str($_POST['content'])) {
    $irfToken=trim($_POST['content']);
  }

  $dossier=null;
  if(isset($_GET['dossier'])) $dossier=$_GET['dossier'];
  $prefix_ve=null;
  if(isset($_GET['prefix_ve'])) $prefix_ve=$_GET['prefix_ve'];
  $prefix_ac=null;
  if(isset($_GET['prefix_ac'])) $prefix_ac=$_GET['prefix_ac'];
  $compte=null;
  if(isset($_GET['compte'])) $compte=$_GET['compte'];

  $ibizawsdl = get_ibiza_server($irfToken, $base, $comptable);

  $trace_path = LOGDIR."/curl_get_plan_$base.trace";

  $ibizawsdl = get_ibiza_server($irfToken, $base, $comptable);
  if(preg_match('/Erreur\s+Ibiza:\s+/', $ibizawsdl)) $message = $ibizawsdl;
  else {

    list($plan_frs,$plan_clts,$journaux, $exercices, $plan_gene) = ibiza_get_plancpt($dossier, $base, $prefix_ac, $prefix_ve, $irfToken,$ibizawsdl, $trace_path);

    $csv_res="\nPLAN GENERAL\nCompte;Libelle\n";
    foreach($plan_gene as $bank_inf) {
      $csv_res .= $bank_inf['libcompte'].";".$bank_inf['description']."\n";
    }

    $csv_res .= "PLAN FOURNISSEUR\nCompte general;Compte auxiliaire;compte tva;Libelle\n";
    foreach($plan_frs as $bank_inf) {
      $cpt_tva=""; if(isset($bank_inf['cpt_tva'])) $cpt_tva=$bank_inf['cpt_tva'];
      $csv_res .= $bank_inf['cpt_assoc'].";".$bank_inf['libcompte'].";".$cpt_tva.";".$bank_inf['description']."\n";
    }

    $csv_res .= "PLAN CLIENT\nCompte general;Compte auxiliaire;compte tva;Libelle\n";
    foreach($plan_clts as $bank_inf) {
      $cpt_tva=""; if(isset($bank_inf['cpt_tva'])) $cpt_tva=$bank_inf['cpt_tva'];
      $csv_res .= $bank_inf['cpt_assoc'].";".$bank_inf['libcompte'].";".$cpt_tva.";".$bank_inf['description']."\n";
    }
    $message = "Serveur Ibiza: $ibizawsdl\nResultat Ibiza:\n$csv_res\n";
  }
  echo $message;
} else die($usage);

if(isset($_GET['verbose']) &&($_GET['verbose']==1)) echo file_get_contents($logpath);

// commande pour le tester:
// php -l core/exports.php; curl -F 'content=</Applications/MAMP/htdocs/V2/core/content.txt' 'http://localhost:8888/V2/core/exports.php?action=constructexport&exportmode=33&base=FxA0907&comptable=FA0766&action=constructexport&dos=002&debut_exercice=01-01-2021&fin_exercice=31-12-2021&id_exp=123456&long_cpt=6&long_aux=6&cpt_gen=401000'

/*
// fichier content pour le tester:
  41227560;;;MOULINSZ DUMEE;28-07-2021;090713017894;401DIV;0;177.5;FA0907;27-08-2021;ACH;323068;;;;;;;0;13017894;;1;;;;7412;;;EUR;;202108_13017894.pdf;401000;achat;202108_13017894.pdf;;0.000;;;;https://www.cabinet-expertcomptable.com/content/download/RkEwOTA3L2Vjcml0dXJlcy8yMDIxMDhfMTMwMTc4OTQucGRm
  41227561;;;MOULINSZ DUMEE;28-07-2021;090713017894;603000;168.25;0;FA0907;27-08-2021;ACH;323068;;;;;;;0;13017894;;1;;;;7412;;;EUR;;202108_13017894.pdf;401000;achat;202108_13017894.pdf;;0.000;;;;https://www.cabinet-expertcomptable.com/content/download/RkEwOTA3L2Vjcml0dXJlcy8yMDIxMDhfMTMwMTc4OTQucGRm
  41227562;;;MOULINSZ DUMEE;28-07-2021;090713017894;445660;9.25;0;FA0907;27-08-2021;ACH;323068;;;;;;;0;13017894;;1;;;;7412;;;EUR;;202108_13017894.pdf;401000;achat;202108_13017894.pdf;;0.000;;;;https://www.cabinet-expertcomptable.com/content/download/RkEwOTA3L2Vjcml0dXJlcy8yMDIxMDhfMTMwMTc4OTQucGRm
  41227548;;;MOULINS DUMEE;28-07-2021;090713017895;401DIV;0;177.5;FA0907;27-08-2021;ACH;323068;;;;;;;0;13017895;;1;;;;7412;;;EUR;;202108_13017895.pdf;401000;achat;202108_13017895.pdf;;0.000;;;;https://www.cabinet-expertcomptable.com/content/download/RkEwOTA3L2Vjcml0dXJlcy8yMDIxMDhfMTMwMTc4OTUucGRm
  41227549;;;MOULINS DUMEE;28-07-2021;090713017895;603000;168.25;0;FA0907;27-08-2021;ACH;323068;;;;;;;0;13017895;;1;;;;7412;;;EUR;;202108_13017895.pdf;401000;achat;202108_13017895.pdf;;0.000;;;;https://www.cabinet-expertcomptable.com/content/download/RkEwOTA3L2Vjcml0dXJlcy8yMDIxMDhfMTMwMTc4OTUucGRm
  41227550;;;MOULINS DUMEE;28-07-2021;090713017895;445660;9.25;0;FA0907;27-08-2021;ACH;323068;;;;;;;0;13017895;;1;;;;7412;;;EUR;;202108_13017895.pdf;401000;achat;202108_13017895.pdf;;0.000;;;;https://www.cabinet-expertcomptable.com/content/download/RkEwOTA3L2Vjcml0dXJlcy8yMDIxMDhfMTMwMTc4OTUucGRm
  2375325;;;ORKYN NANTES;09-10-2019;6056950749;CORKYN;0;14.48;FA6056;;VE;;;;;;;;0;950749;;1;;;;965;;;EUR;;201910_950749.pdf; ;vente;201910_950749.pdf;;;;;;https://www.cabinet-expertcomptable.com/content/download/RkEwNzc4L2Vjcml0dXJlcy8yMDE5MTBfOTUwNzQ5LnBkZg==
  2375326;;;ORKYN NANTES;09-10-2019;6056950749;60300000;12.07;0;FA6056;;VE;;;;A2;;;;0;950749;;1;;;;965;;;EUR;;201910_950749.pdf; ;vente;201910_950749.pdf;;;;;;https://www.cabinet-expertcomptable.com/content/download/RkEwNzc4L2Vjcml0dXJlcy8yMDE5MTBfOTUwNzQ5LnBkZg==
  2375327;;;ORKYN NANTES;09-10-2019;6056950749;445712;2.41;0;FA6056;;VE;;;;A2;;;;0;950749;;1;;;;965;;;EUR;;201910_950749.pdf; ;vente;201910_950749.pdf;;;;;;https://www.cabinet-expertcomptable.com/content/download/RkEwNzc4L2Vjcml0dXJlcy8yMDE5MTBfOTUwNzQ5LnBkZg==
  4866387;;;CSP;19-12-2019;60561811492;0CSP;0;3987.07;FA6056;18-01-2020;AC;Q191254352;;UGL;;;;61173;414093;1811492;;1;;;;965;;;EUR;;201912_1811492.pdf; ;achat;201912_1811492.pdf;;;;;;https://www.cabinet-expertcomptable.com/content/download/RkE2MDU2L2Vjcml0dXJlcy8yMDE5MTJfMTgxMTQ5Mi5wZGY=
  4866388;;;CSP;19-12-2019;60561811492;471000;3987.07;0;FA6056;18-01-2020;AC;Q191254352;;UGL;B;;;61173;414093;1811492;;1;;;;965;;;EUR;;201912_1811492.pdf; ;achat;201912_1811492.pdf;;;;;;https://www.cabinet-expertcomptable.com/content/download/RkE2MDU2L2Vjcml0dXJlcy8yMDE5MTJfMTgxMTQ5Mi5wZGY=
  4920435;5e04ce27deb3a;;TEST RAPPROCHER;26-12-2019;60561833768;0TESTRAPP;2880.21;0;FA6056;;AC;;;UGL;;;;61173;414093;1833768;;1;;;;;;;EUR;;; ;achat;;;;;;;
  4920436;5e04ce27e01e4;;TEST RAPPROCHER;26-12-2019;60561833768;471000;0;2880.21;FA6056;;AC;;;UGL;;;;61173;414093;1833768;;1;;;;;;;EUR;;; ;achat;;;;;;;


  Points à traiter:

  si cette url ne repond pas, tenir compte de ce cas dans la function launch_exportko
  pour le tester: https://www.cabinet-expertcomptable.com/ecritures/exportEcriture?base=".$base."&famille=".$fam_id."&action=export_ko

  Où stocker les traces de lancements d'import anciennement dans les fichiers /data/disk1/upload/FA6056/traces/exp_mess_090713017894.txt ?



*/


?>
