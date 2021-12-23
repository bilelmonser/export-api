<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\AccountancyPractice;
use App\Entity\Company;
use App\Entity\FinancialPeriod;
use App\Service\ClientHttpService;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Dotenv\Dotenv;
use App\Service\App\SerializeService;

class ExportService {
    /**
     * Date Mysql To Html Function
     *
     * @param [type] $sql_date
     * @param integer $set_today
     * @param integer $explode
     * @param integer $mobile
     * @param integer $add_hour
     * @param integer $str_pad
     * @return void
     */
    public function date_mysql_to_html($sql_date, $set_today=1, $explode=0, $mobile=0, $add_hour=0,$str_pad=0){

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
      
        if($this->Is_empty_str($sql_date)) {
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
    /**
     * Date HTML to Mysql function
     *
     * @param string $html_date
     * @param integer $split_res
     * @param integer $quadra
     * @param integer $set_today
     * @return void
     */
    public function date_html_to_mysql($html_date, $split_res=0,$quadra=0, $set_today=0){

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
    /**
     * Verbose String to File function
     *
     * @param string $file_occured
     * @param string $function_occured
     * @param string $verbose_str
     * @return void
     */
    public function verbose_str_to_file($file_occured, $function_occured, $verbose_str) {
        global $logpath;
        file_put_contents($logpath, date('H:i:s').":$function_occured => $verbose_str", FILE_APPEND);
    }
    /**
     * Print Verbose CSV Element function
     *
     * @param array $array_to_print
     * @param integer $mode_light
     * @return void
     */
    public function print_verb_csvelem($array_to_print, $mode_light=1) {


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
    
    /**
     * Export Ecritures Lettres function
     *
     * @param [type] $all_csv_elem
     * @param [type] $total_let
     * @param [type] $let2fam
     * @param [type] $fin_exercice
     * @param [type] $debut_exercice
     * @param [type] $base
     * @param [type] $dos
     * @param [type] $exportmode
     * @param [type] $long_cpt
     * @param [type] $long_cpt_gen_ac
     * @param [type] $long_cpt_gen_ve
     * @param [type] $long_aux
     * @param [type] $long_aux_ve
     * @param [type] $irfToken
     * @return void
     */
    public function export_ecritures_lettrees($all_csv_elem, $total_let, $let2fam, $fin_exercice, $debut_exercice, $base, $dos, $exportmode, $long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $irfToken) {


        $message="";
      
        $fam_done=$parfam=array();
        foreach($all_csv_elem as $csv_elem) {
          $parfam[$csv_elem['famille']][]=$csv_elem;
        }
      
        $tmp_arr=array();
        foreach($total_let as $let=>$val) {
          if($val ==0) {
      
            $this->verbose_str_to_file(__FILE__, __FUNCTION__, "lettrage ok $let\n".print_r($let2fam[$let],1));
            $csv_to_export=array();
            foreach($let2fam[$let] as $fam) {
              if( ! isset($fam_done[$fam])) $fam_done[$fam]=0;
              if($fam_done[$fam]!=1){
                $this->verbose_str_to_file(__FILE__, __FUNCTION__, "lettrage ok pour fam $fam\n ==> ".$this->print_verb_csvelem($parfam[$fam],1));
                foreach($parfam[$fam] as $csv_elem) {
                  $csv_to_export[]=$csv_elem;
                  $fam_done[$fam]=1;
                }
              }
            }
      
            if($exportmode == 34) {
              $this->verbose_str_to_file(__FILE__, __FUNCTION__, "csv_to_export vers ibiza avec $exportmode \n".$this->print_verb_csvelem($csv_to_export,1));
              $message = $this>exportTOibiza($csv_to_export, $fin_exercice, $debut_exercice, $base, $dos, $exportmode, $long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $irfToken);
              $this->verbose_str_to_file(__FILE__, __FUNCTION__, "$message\n");
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
              //$message .=  "ajout: ".$this->print_verb_csvelem(array($csv_elem),1);
              $tmp_arr[]=$csv_elem;
              $fam_done[$fam]=1;
            }
          }
        }
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "ecritures sans lettrage restant a exporter\n: ".$this->print_verb_csvelem($tmp_arr,1));
      
        return array($tmp_arr, $message);
      }
      /**
       * Undocumented function
       *
       * @param [type] $fin_exercice
       * @param [type] $debut_exercice
       * @param [type] $exportmode
       * @return void
       */
      public function post_content2csv_elem($fin_exercice, $debut_exercice, $exportmode) {

        $total_let_verb="";
        $ttc_found=$total_let=$let2fam=$all_csv_elem=$fam_done=array();
        $content = explode("\n", $_POST['content']);
      
        $fam2pos=$tmp_arr=array();
        foreach($content as $line){
          $splitted = explode(";", $line);
          $fam2pos=array();
          for($idx=0; $idx<count($splitted); $idx++){
            $tmp_str = $splitted[$idx];
      
            if(($idx!=4)&&($idx!=10)&&($idx!=7)&&($idx!=8)&&($idx!=40)) $tmp_str = $this->clean_file_name($tmp_str);
      
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
          $csv_elem['date']=$this->date_mysql_to_html(date_url_to_mysql($splitted[4]));
          $csv_elem['famille']=$splitted[5];
      
          $csv_elem['nature']=$splitted[33];
          // ACE AND GO
      
          $csv_elem['cpt_general'] = $this->nature2cptgen($csv_elem);
          if(!$this->Is_empty_str($splitted[32])) $csv_elem['cpt_general']=$splitted[32];
      
          if( ! $this->Is_empty_str($splitted[10])) $csv_elem['date_echeance']=$this->date_mysql_to_html(date_url_to_mysql($splitted[10]));
          if($_GET['comptable']=="FB1021") $csv_elem['date_echeance']="";
          $csv_elem['cb']=$splitted[34];
          $csv_elem['code_j']=$splitted[11];
      
          if( $_GET['base']=='FB0114') $csv_elem['lettrage']="";
          //else if( $_GET['base']=='FB0122') $csv_elem['lettrage']="";
          else $csv_elem['lettrage']=$splitted[14];
      
          $csv_elem['code_tva']=$splitted[15];
          $csv_elem['code_ana']=$splitted[13];
          $csv_elem['id_type']=$splitted[0];
          $csv_elem['ged_dir']=$this->build_ged_dir_date($csv_elem['date'], $fin_exercice, $debut_exercice, $exportmode);
          $csv_elem['path_pj']=array($splitted[31]);
          $csv_elem['path_pj_ext']=array($splitted[31]);
      
          if(!$this->Is_empty_str($splitted[40])) {
            //if($_GET['base']=='FA0143') {
            $csv_elem['list_liens']=array(trim($splitted[40]));
          } else {
            if(!$this->Is_empty_str($splitted[34])) $lien=$splitted[34];
            else $lien=$splitted[31];
            if(!$this->Is_empty_str($lien)) $csv_elem['list_liens']=array("https://www.cabinet-expertcomptable.com/upload/".$_GET['base']."/ecritures/$lien");
          }
      
          $csv_elem['description']=$splitted[3];
          $csv_elem['devise']=$splitted[29];
          if(strlen($csv_elem['devise']) != 3) $csv_elem['devise']="";
          $csv_elem['num_fact']=$splitted[12];
          //if($exportmode==41) $csv_elem['num_fact']=preg_replace('\.\w+\s*$', '', $splitted[31]);
          $csv_elem['id_fact']=$splitted[0];
          $csv_elem['cpt_lib']=$splitted[6];
          $csv_elem['num_cpt']=$splitted[6];
          $csv_elem['quantite1']=$this->formater_montant($splitted[36]);
          if($csv_elem['quantite1']==0)$csv_elem['quantite1']="";
          $csv_elem['quantite2']=$this->formater_montant($splitted[37]);
          if($csv_elem['quantite2']==0)$csv_elem['quantite2']="";
      
      
      
      
          $csv_elem['A1period1']=$splitted[38]." ".$this->date_url_to_mysqlB($splitted[38]);
          $tmp_date = trim($splitted[38]);
          if( ! $this->Is_empty_str($tmp_date) ) {
            $tmp_date = $this->date_url_to_mysql($tmp_date);
            $csv_elem['A1period1'] .=" decoup ".$tmp_date;
            if(!$this->Is_empty_str($tmp_date)){
              $tmp_date = $this->date_mysql_to_html($tmp_date);
              $csv_elem['A1period1'] .=" decoup ".$tmp_date;
              $csv_elem['period1']=$tmp_date;
            }
          }
      
          $csv_elem['A1period1']=$splitted[39]." ".$this->date_url_to_mysql($splitted[39]);
          $tmp_date = trim($splitted[39]);
          if( ! $this->Is_empty_str($tmp_date) ) {
            $tmp_date = $this->date_url_to_mysql($tmp_date);
            $csv_elem['A1period1'] .=" decoup ".$tmp_date;
            if(!$this->Is_empty_str($tmp_date)){
              $tmp_date = $this->date_mysql_to_html($tmp_date);
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
          if($this->Is_empty_str($csv_elem['num_piece'])) $csv_elem['num_piece']=trim($splitted[12]);
      
          if($exportmode==41) {
            if(isset($splitted[31]) && (strlen($splitted[31])>2)){
              $csv_elem['num_piece']=preg_replace('/\.\w+\s*$/', '', $splitted[31]);
              $csv_elem['num_piece'] = $splitted[31];
              $tmp_arr = explode('.', $csv_elem['num_piece']);
              $csv_elem['num_piece'] = $tmp_arr[0];
            }
          }
      
          if($this->Is_empty_str($csv_elem['num_fact'])) $csv_elem['num_fact']=$csv_elem['num_piece'];
      
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
      
          $csv_elem['debit']=$this->formater_montant($splitted[7],0,0,0,1,2);
          $csv_elem['credit']=$this->formater_montant($splitted[8],0,0,0,1,2);
          $montant = $this->formater_montant($csv_elem['debit'])+$this->formater_montant($csv_elem['credit']);
      
          // lettrage sur compte auxiliaire uniquement
          if(preg_match('/^\s*5/', $csv_elem['num_cpt'])) $csv_elem['lettrage']="";
          else if($cpt_ttc != 1) $csv_elem['lettrage']="";
      
          if( ! $this->Is_empty_str($csv_elem['lettrage'])) {
            $fam_id = $csv_elem['famille'];
            if(!$this->Is_empty_str($fam_id)) {
              if($cpt_ttc == 1) {
                if(!isset($total_let[$csv_elem['lettrage']]))$total_let[$csv_elem['lettrage']]=0;
      
                $total_let_verb .= $fam_id." ".$csv_elem['lettrage']." = ".$this->formater_montant($total_let[$csv_elem['lettrage']])." + ".$this->formater_montant($csv_elem['credit'])." - ".$this->formater_montant($csv_elem['debit'])."\n";
                $total_let[$csv_elem['lettrage']] = $this->formater_montant($total_let[$csv_elem['lettrage']]) + $this->formater_montant($csv_elem['credit']) - $this->formater_montant($csv_elem['debit']);
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
      
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "all_csv_elem\n".$this->print_verb_csvelem($all_csv_elem,1)."\n"."total_let  \ntotal_let_verb:\n$total_let_verb\n\n".print_r($total_let,1)."\n"."\n"."let2fam  \n".print_r($let2fam,1)."\n");
      
        return array($all_csv_elem, $total_let, $let2fam);
      }
      /**
       * Filtre Achats Ventes function
       *
       * @param [type] $all_csv_elem
       * @return void
       */
      public function filtre_achats_ventes($all_csv_elem){
      
        $res=$res_ach=$res_ve=$res_autre=array();
      
        foreach($all_csv_elem as $csv_elem) {
          if($csv_elem['nature'] == "achat") $res_ach[]=$csv_elem;
          else if($csv_elem['nature'] == "vente") $res_ve[]=$csv_elem;
          else $res_autre[]=$csv_elem;
        }
      
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "\nachats\n".$this->print_verb_csvelem($res_ach,1)."\nventes\n ".$this->print_verb_csvelem($res_ve,1)."\nautres\n".$this->print_verb_csvelem($res_autre,1));
      
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
      
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "elements gardes ".$this->print_verb_csvelem($res));
        return $res;
      }

      /**
       * Filtre par Lettrage function
       *
       * @param [type] $all_csv_elem
       * @param [type] $total_let
       * @param [type] $let2fam
       * @return void
       */
      public function filtre_par_lettrage($all_csv_elem, $total_let, $let2fam) {


        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "Ecritures a filtrer\n".$this->print_verb_csvelem($all_csv_elem)."\n");
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "Sommes des debit credit par lettrage\n".print_r($total_let,1)."\n");
      
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "lettrage par famille\n".print_r($let2fam,1)."\n");
      
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
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "Filtre sur lettrage non equilibre\n".print_r($csv_ko,1)."\n");
      
        $tmp_fam=$fam_done=array();
        foreach($all_csv_elem as $csv_elem) {
      
          if((isset($csv_ko[$csv_elem['famille']]))&&($csv_ko[$csv_elem['famille']]==1)) {
            $fam_id = $csv_elem['famille'];
            if(!$this->Is_empty_str($fam_id)) {
              if( ! isset($fam_done[$fam_id])) $fam_done[$fam_id]=0;
              if($fam_done[$fam_id] != 1) {
                $fam_done[$fam_id] = 1;
                $message_fam = "ERREUR: Lettrage des comptes auxiliaires non equilibre";
                $cmd = $this->launch_exportko($message_fam, $message_fam, $_GET['base'], $fam_id);
      
              }
            }
          } else $tmp_fam[]=$csv_elem;
        }
      
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "apres filtre sur lettrage non equilibre  \n".$this->print_verb_csvelem($tmp_fam,1)."\n");
      
      
        return $tmp_fam;
      
      }
      /**
       * Get Code Libelle Famille function
       *
       * @param [type] $all_csv_elem
       * @return void
       */
      public function get_code_lib_fam($all_csv_elem){


        $code_lib_fam=$res=array();
        foreach($all_csv_elem as $csv_elem) {
          if(preg_match('/^\s*51/', $csv_elem['num_cpt']) && ($csv_elem['type_element']=='banque')){
            $code_lib_fam[$csv_elem['famille']]=' ';
          } else if($csv_elem['pos_txt']=='TTC') {
            $code_lib_fam[$csv_elem['famille']]='F';
            if($csv_elem['type_element']=='encaissement') {
              if($this->formater_montant($csv_elem['credit'])>0)$code_lib_fam[$csv_elem['famille']]='A';
            } else {
              if($this->formater_montant($csv_elem['debit'])>0)$code_lib_fam[$csv_elem['famille']]='A';
            }
          }
        }
      
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "code_lib_fam ".print_r($code_lib_fam,1)."\n");
      
        foreach($all_csv_elem as $csv_elem) {
          $csv_elem['code_lib'] = $code_lib_fam[$csv_elem['famille']];
          $res[]=$csv_elem;
        }
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "\n all_csv_elem\n".$this->print_verb_csvelem($res,1)."\n");
        return $res;
      }
     /**
      * Filtre Par TTC function
      *
      * @param [type] $all_csv_elem
      * @return void
      */
      public function filtre_par_ttc($all_csv_elem) {

        $parfam=array();
        foreach($all_csv_elem as $csv_elem) {
          $parfam[$csv_elem['famille']][]=$csv_elem;
        }
      
        $tmp_fam=$fam_done=array();
        $message="";
        foreach($parfam as $fam=>$listfam) {
          $found_one=0;
          $listfam_tmp=array();
          foreach($listfam as $csv_elem) {
            if($csv_elem['position']==1) $found_one=1;
            if(!isset($csv_elem['quadra_contre']))$csv_elem['quadra_contre']=null;
            if( preg_match('/^\s*6|7/', $csv_elem['num_cpt']) && $this->Is_empty_str($csv_elem['quadra_contre']) ) {
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
              $cmd = $this->launch_exportko($message_fam, $message_fam, $_GET['base'], $fam_id);
              $message .=  "export ko sur $fam_id \n$cmd\n";
            }
          } else $tmp_fam[$fam]=$listfam;
        }
      
        $message="Classement par famille \n";
        foreach($tmp_fam as $fam=>$listfam){
          $message .= "famille $fam \n".$this->print_verb_csvelem($listfam,1)."\n";
        }
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "$message\n");
      
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
      
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "all_csv_elem apres rangement TTC en premiere ligne\n".$this->print_verb_csvelem($res,1));
      
        return $res;
      }
      /**
       * Export To Ibiza function
       *
       * @param [type] $all_csv_elem
       * @param [type] $fin_exercice
       * @param [type] $debut_exercice
       * @param [type] $base
       * @param [type] $cab_dossier
       * @param [type] $export_mode
       * @param [type] $long_cpt
       * @param [type] $long_cpt_gen_ac
       * @param [type] $long_cpt_gen_ve
       * @param [type] $long_aux
       * @param [type] $long_aux_ve
       * @param [type] $irfToken
       * @return void
       */
      public function exportTOibiza($all_csv_elem, $fin_exercice, $debut_exercice, $base, $cab_dossier, $export_mode, $long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $irfToken) {

        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "ecritures a exporter vers ibiza\n: ".$this->print_verb_csvelem($all_csv_elem,1));
        $trace_fam_path = LOGDIR."/exp_".$all_csv_elem[0]['famille'].".txt";
        $trace_clt_path = LOGDIR."/exp_mess_".$all_csv_elem[0]['famille'].".txt";
        $curltrace_path = LOGDIR."/curl_".$all_csv_elem[0]['famille'].".trace";
      
        $comptable = $all_csv_elem[0]['comptable'];
      
      
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "trace_fam_path=\n$trace_fam_path trace_clt_path=\n$trace_clt_path curltrace_path=\n$curltrace_path\n");
      
        //EXO004 00 02 0000 20 OUVOUVExercice
        $message_fam = "construct zipdir avec  ".$all_csv_elem[0]['date'];
        list($zip_dir, $zip_dir_name) = $this->get_zipdir_path($all_csv_elem[0]['date'], $base, $cab_dossier, $export_mode);
      
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "debut_exercice $debut_exercice fin_exercice $fin_exercice\n");
      
        $this->all_csv_elem_to_zip($all_csv_elem, $export_mode, $zip_dir, $zip_dir_name, $base, $cab_dossier, $fin_exercice, $debut_exercice, $long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, null,null,null,null,null,null,null);
      
      
      
        list($statusT, $messageT, $file_list) = $this->get_dir_content($zip_dir);
        $message_fam .= "\nContenu du zip_dir: ".print_r($file_list,1);
        $ibiza_xml = file_get_contents($zip_dir."/ibiza.xml");
        $message_fam .= $ibiza_xml;
        $this->verbose_str_to_file(__FILE__, __FUNCTION__, "ibiza_xml:\n$ibiza_xml");
      
      
      
        if(! $this->Is_empty_str($ibiza_xml)) {
          $ibizawsdl = $this->get_ibiza_server($irfToken, $base, $comptable);
      
          list($status, $cpt_rendu_elem) = $this->ibiza_import_elem($cab_dossier, $ibiza_xml, $base, $irfToken, $ibizawsdl, $curltrace_path);
      
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
                $this->launch_exportbon($message_clt, $cpt_rendu_elem."\n".$ibiza_xml, $_GET['base'], $fam_id);
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
                $this->launch_exportko($message_clt, $cpt_rendu_elem."\n".$ibiza_xml, $_GET['base'], $fam_id);
              }
            }
          }
      
      
        } else {
          $fam_done=array();
          foreach($all_csv_elem as $csv_elem) {
      
             $fam_id = $csv_elem['famille'];
            if(!$this->Is_empty_str($fam_id)) {
              if($fam_done[$fam_id] != 1) {
                $fam_done[$fam_id] = 1;
                $message_fam = "Erreur generation export: Aucune ligne XML pour cet élément $fam_id";
                $this->launch_exportko($message_fam, $message_fam, $_GET['base'], $fam_id);
              }
            }
          }
        }
        rm_file($curltrace_path);
        return $message;
      }
      /**
       * Get Zid Directory Path function
       *
       * @param string $date_operation
       * @param string $base
       * @param string $cab_dossier
       * @param  $export_mode
       * @return void
       */
      public function get_zipdir_path($date_operation, $base, $cab_dossier, $export_mode) {

        if(preg_match('?/?', $date_operation)) $date_operation = $this->date_html_to_mysql($date_operation);
      
        if(!$this->Is_empty_str($date_operation))
          list($d_cur,$m_cur,$y_cur) = $this->date_mysql_to_html($date_operation, 1, 1);
        if( ! ($y_cur>0)) $y_cur=date('Y');
        if( ! ($m_cur>0)) $m_cur=date('m');
      
        $generetedFileName ="Exp_$y_cur"."_$m_cur"."_$base"."_".rand(10,99);
      
        if(($export_mode==36)||($export_mode==41)){
          if($this->Is_empty_str($cab_dossier)) $cab_dossier = "NONUM";
          $generetedFileName = "FacNote_".$cab_dossier."_".date('Ymd-His')."_manual";
        }
        if(($export_mode==32)||($export_mode==32)){
          if($this->Is_empty_str($cab_dossier)) $cab_dossier = "NONUM";
          $generetedFileName = $cab_dossier."_".date('Ymd_His')."_manual";
        }
        if(($export_mode==44)) {
          if($this->Is_empty_str($cab_dossier)) $cab_dossier = "NONUM";
          $generetedFileName = $cab_dossier."IN.auto";
        }
      
          $zip_dir_name = $generetedFileName;
          $zip_dir = LOGDIR."/$zip_dir_name";
        list($output, $status) = $this->launch_system_command("rm -rf $zip_dir");
        list($output, $status) = $this->launch_system_command("mkdir $zip_dir");
          verbose_str_to_file(__FILE__, __FUNCTION__, "mkdir zip_dir $zip_dir et zip_dir_name $zip_dir_name\n".print_r($output,1));
      
          return array($zip_dir, $zip_dir_name);
      }
    /**
     * All CSV Elements To Zip function
     *
     * @param [type] $all_csv_elem
     * @param [type] $export_mode
     * @param [type] $zip_dir
     * @param [type] $zip_dir_name
     * @param [type] $base
     * @param [type] $cab_dossier
     * @param [type] $fin_exercice
     * @param [type] $debut_exercice
     * @param [type] $long_cpt
     * @param [type] $long_cpt_gen_ac
     * @param [type] $long_cpt_gen_ve
     * @param [type] $long_aux
     * @param [type] $long_aux_ve
     * @param [type] $sa1
     * @param [type] $sa2
     * @param [type] $sa3
     * @param [type] $sa4
     * @param [type] $sa5
     * @param [type] $etablissement
     * @param [type] $agirismanuel
     * @return void
     */
    public function all_csv_elem_to_zip($all_csv_elem, $export_mode, $zip_dir, $zip_dir_name, $base, $cab_dossier, $fin_exercice, $debut_exercice, $long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $sa1,$sa2,$sa3,$sa4,$sa5,$etablissement,$agirismanuel ) {

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
        if($this->Is_empty_str($exercice,1))$exercice=$all_csv_elem[1]['date'];
        if($this->Is_empty_str($exercice,1))$exercice=$all_csv_elem[2]['date'];
        if($this->Is_empty_str($exercice,1))$exercice=$all_csv_elem[3]['date'];
        $exercice = $this->build_ged_dir_date($exercice, $params_export['fin_exercice'], $params_export['debut_exercice'], $export_mode);
      
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
      
          if(!$this->Is_empty_str($params_export['etablissement'])) $etablissement=substr(str_pad($params_export['etablissement'], 3, "0", STR_PAD_LEFT),0,3);
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
      
      
          if(($export_mode==32)&&($agirismanuel==1)) $this->launch_system_command("cp $file_path ".dirname($file_path)."/$zip_dir_name.ECR",0,1);//ECR
          verbose_str_to_file(__FILE__, __FUNCTION__, "yooz conversions en pdf $export_mode\n");
      
      
              if(($export_mode==24)||($export_mode==25)||($export_mode==26)||($export_mode==27)||($export_mode==43)||($export_mode==34)||($export_mode==6))
            $url_to_ret = "../../upload/$zip_dir_name/$file_name";
              //else if(($export_mode==32)) $url_to_ret = "../../upload/$zip_dir_name/$file_name";
              else {
            if($export_mode==41){
              verbose_str_to_file(__FILE__, __FUNCTION__, "yooz conversions en pdf\n");
              $dir_path=$zip_dir;
              list($status, $message, $file_list) = $this->get_dir_content($dir_path, 1);
      
              foreach($file_list as $file){
                $mime_infos = mime_content_type("$dir_path/$file");
                if(preg_match('?image?i', $mime_infos)) {
                  $fichier_split = pathinfo("$dir_path/$file");
      
      
                  $convert_cmd = "timeout -k 31s 30s convert";
      
                  $convert_cmd = "$convert_cmd $dir_path/$file $dir_path/".$fichier_split['filename'].".PDF";
                  verbose_str_to_file(__FILE__, __FUNCTION__, "yooz conversions en pdf $convert_cmd\n");
      
                  $this->launch_system_command($convert_cmd,0,1);
                }
              }
              $cmd="cd $zip_dir; zip -r $zip_dir.ZIP *";
              verbose_str_to_file(__FILE__, __FUNCTION__, "export_mode=$export_mode cmd=\n$cmd\n");
              $this->launch_system_command($cmd,0,1);
      
            } else if($export_mode==44){
              $cmd="mv $zip_dir $zip_dir.tmp; cd $zip_dir.tmp; zip -r $zip_dir.ZIP *";
              verbose_str_to_file(__FILE__, __FUNCTION__, "export_mode=$export_mode cmd=\n$cmd\n");
              $this->launch_system_command($cmd,0,1);
            } else {
              $cmd="cd $zip_dir/../; zip -r $zip_dir.ZIP $zip_dir_name";
              verbose_str_to_file(__FILE__, __FUNCTION__, "export_mode=$export_mode cmd=\n$cmd\n");
              $this->launch_system_command($cmd,0,1);
            }
      
            sleep(1);
      
            if($export_mode==44) list($output, $status) = $this->launch_system_command("unzip -l $zip_dir.ZIP",0,1);
            else list($output, $status) = $this->launch_system_command("unzip -l $zip_dir.ZIP",0,1);
            if($status != 0) {
              $export_found=-1;
              $url_to_ret="";
            } else {
              $url_to_ret = "../../upload/$zip_dir_name.ZIP";
              if($export_mode==44) {
                $this->launch_system_command("mv $zip_dir.ZIP $zip_dir",0,1);
                $url_to_ret = "../../upload/$zip_dir_name";
              }
            }
              }
          }
      
        verbose_str_to_file(__FILE__, __FUNCTION__, "csv_content in $file_path: $csv_content\n$html_csv_content\n export_found=$export_found, url_to_ret=$url_to_ret\n");
      
          return array($export_found, $url_to_ret);
      }
    /**
     * Get Directory Content function
     *
     * @param [type] $src
     * @param [type] $only_files
     * @param [type] $match_str
     * @param [type] $uniq
     * @return void
     */
    public function get_dir_content($src, $only_files=null, $match_str=null, $uniq=null) {

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
                                  if( $this->Is_empty_str($match_str)) $file_list[]=$file_name;
                                  else if( preg_match($match_str, $file_name) ) $file_list[]=$file_name;
      
                              } //else echo "dir $file_name\n";
                          } else {
                              //verbose_str_to_file(__FILE__, __FUNCTION__, "$file_name: cas $only_files==1) && ( $is_dir != 1");
                              if( $this->Is_empty_str($match_str)) $file_list[]=$file_name;
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
    /**
     * Launch System Command function
     *
     * @param string $cmd
     * @param integer $background
     * @param integer $delete_tmp_files
     * @param integer $timeout_sec
     * @param string $result_of_bck_job_path
     * @return void
     */
    public function launch_system_command($cmd, $background=0, $delete_tmp_files=1, $timeout_sec=0, $result_of_bck_job_path=null) {

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
      /**
       * Clean File Name function
       *
       * @param string $file_name
       * @param integer $keep_plancpt_car
       * @param integer $keep_spaces
       * @param  $forsearch
       * @return void
       */
      public function clean_file_name($file_name, $keep_plancpt_car=0, $keep_spaces=1, $forsearch=null){

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
    /**
     * Launch EXport Ko function
     *
     * @param [type] $message_fam
     * @param [type] $postcontent
     * @param [type] $base
     * @param [type] $fam_id
     * @return void
     */
    public function launch_exportko($message_fam, $postcontent, $base, $fam_id) {

      if($message_fam != $postcontent) $postcontent .= "\n$message_fam\n";
    
      $status = curl_prod_nv("https://www.cabinet-expertcomptable.com/ecritures/exportEcriture?base=".$base."&famille=".$fam_id."&action=export_ko", array('content'=>$postcontent), '/EXPORT\s+KO/');
    
      // if( ! $status) // traiter ici en cas d'erreur sur unlock
    
    
    }
    /**
     * Launch Export Bon function
     *
     * @param [type] $message_fam
     * @param [type] $postcontent
     * @param [type] $base
     * @param [type] $fam_id
     * @return void
     */
    public function launch_exportbon($message_fam, $postcontent, $base, $fam_id) {

      if($message_fam != $postcontent) $postcontent .= "\n$message_fam\n";
    
      $status = curl_prod_nv("https://www.cabinet-expertcomptable.com/ecritures/exportEcriture?base=".$base."&famille=".$fam_id."&action=export_bon", array('content'=>"Import terminé avec succès"), '/EXPORT\s+OK/');
    
      // if( ! $status) // traiter ici en cas d'erreur sur unlock
    
    
    }
    /**
     * Nature To Cpt Gen function
     *
     * @param array $csv_elem
     * @return void
     */  
    public function nature2cptgen($csv_elem) {
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
  /**
   * Is Empty String function
   *
   * @param string $champ
   * @param integer $isdate
   * @return void
   */
  public function Is_empty_str($champ, $isdate=0){

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
  /**
   * Build Ged Directory Date function
   *
   * @param string $date
   * @param [type] $fin_exercice
   * @param [type] $deb_exercice
   * @param [type] $export_mod
   * @param [type] $avec_verbose
   * @return void
   */
  public function build_ged_dir_date($date, $fin_exercice, $deb_exercice, $export_mod, $avec_verbose=null) {

    if(preg_match('?/?', $date)) $date=$this->date_html_to_mysql($date);
    list($d_chg,$m_chg,$y_chg) = $this->date_mysql_to_html($date, 0, 1);
    $time_chg = mktime ( 0, 0, 0,$m_chg , $d_chg, $y_chg );
  
    $list_exercices = $this->build_list_exercices($deb_exercice, $fin_exercice, $y_chg);
  
    foreach($list_exercices as $debfin){
      $time_deb=$debfin[0];
      $time_fin=$debfin[1];
  
      if( ($time_chg>$time_deb-1) && ($time_chg < $time_fin+1) ) {
        list($d_fin,$m_fin,$y_fin) = $this->date_mysql_to_html(date('Y-m-d', $time_fin), 0, 1);
        list($d_deb,$m_deb,$y_deb) = $this->date_mysql_to_html(date('Y-m-d', $time_deb), 0, 1);
  
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
  
    $this->verbose_str_to_file(__FILE__, __FUNCTION__, "date=$date, fin_exercice=$fin_exercice deb_exercice=$deb_exercice export_mod=$export_mod ==> ged_dir $ged_dir\n");
  
    return $ged_dir;
  }

  public function formater_montant($html_montant, $to_display=0, $neg_red=0, $pos_blue=0, $virgule=0, $nb_Dec=2){


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
  /**
   * Date Url To MysqlB function
   *
   * @param string $date
   * @return void
   */
  public function date_url_to_mysqlB($date){

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
  /**
   * Date Url To Mysql function
   *
   * @param [type] $date
   * @return void
   */
  public function date_url_to_mysql($date){

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
  /**
   * Build List Exercices function
   *
   * @param string $deb_exercice
   * @param string $fin_exercice
   * @param string $y_chg
   * @return array
   */
  public function build_list_exercices($deb_exercice, $fin_exercice, $y_chg) {
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
  /**
   * Get Ibiza Server function
   *
   * @param [type] $irftoken
   * @param [type] $base
   * @param [type] $comptable
   * @return void
   */
  public function get_ibiza_server($irftoken, $base, $comptable)  {

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
  /**
   * Ibiza Import Element function
   *
   * @param string $db_clt
   * @param string $xml_content
   * @param string $base
   * @param string $irfToken
   * @param string $ibizawsdl
   * @param string $curltrace_path
   * @return void
   */
  public function ibiza_import_elem($db_clt, $xml_content, $base, $irfToken, $ibizawsdl, $curltrace_path) {
    $verbose=0;
    $message="";
  
    if(Is_empty_str($ibizawsdl)||(strlen($ibizawsdl)<5)) $ibizawsdl=$this->get_ibiza_server($irfToken, $base);
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

}