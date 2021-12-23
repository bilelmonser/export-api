<?php

namespace App\Controller\Api\Export;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\ExportService;

class ExportController extends AbstractController
{
    /**
     * @Route("/api/export/export/constructexport/base/{base}/comptable/{comptable}", name="export_export_constructexport")
     */
    public function constructExport(Request $request,ExportService $exportService){
            /************************************************************************************** */
            /************************************************************************************** */
            $message="";
            $status=200;
            $base = ( $request->attributes->get('base')) ? $request->attributes->get('base') : "" ;
            $export_manuel=( $request->request->get('export_manuel')) ? $request->request->get('export_manuel') : 0 ;
            $exportmode=( $request->request->get('export_mode')) ? $request->request->get('export_mode') : 0 ;
            $debut_exercice=( $request->request->get('debut_exercice')) ? $request->request->get('debut_exercice') : "" ;
            $fin_exercice=( $request->request->get('fin_exercice')) ? $request->request->get('fin_exercice') : "" ;
            $cab_dossier=( $request->request->get('dos')) ? $request->request->get('dos') : "" ;
            $long_cpt=( $request->request->get('long_cpt')) ? $request->request->get('long_cpt') : null ;
            $long_cpt_gen_ac=( $request->request->get('long_cpt_gen_ac')) ? $request->request->get('long_cpt_gen_ac') : null ;
            $long_cpt_gen_ve=( $request->request->get('long_cpt_gen_ve')) ? $request->request->get('long_cpt_gen_ve') : null ;
            $long_aux=( $request->request->get('long_aux')) ? $request->request->get('long_aux') : null ;
            $long_aux_ve=( $request->request->get('long_aux_ve')) ? $request->request->get('long_aux_ve') : null ;
            $sa1=( $request->request->get('sa1')) ? $request->request->get('sa1') : null ;
            $sa2=( $request->request->get('sa2')) ? $request->request->get('sa2') : null ;
            $sa3=( $request->request->get('sa3')) ? $request->request->get('sa3') : null ;
            $sa4=( $request->request->get('sa4')) ? $request->request->get('sa4') : null ;
            $sa5=( $request->request->get('sa5')) ? $request->request->get('sa5') : null ;
            $etablissement=( $request->request->get('etablissement')) ? $request->request->get('etablissement') : null ;
            $auto=( $request->request->get('auto')) ? $request->request->get('auto') : null ;

            if(($exportmode==40)&&($request->attributes->get('comptable')=='FB4798')) $exportmode=6;
            if(($exportmode==40)&&($request->attributes->get('comptable')=='FA0766')) $exportmode=6;
            if(($exportmode==7)&&($request->attributes->get('comptable')=='FA2733')) $exportmode=46;            
            if($request->attributes->get('comptable')=='FA2247') {$exportmode=6;}

            
            $exportService->verbose_str_to_file(__FILE__, __FUNCTION__, "Recu _GET".print_r($_GET,1)."_POST:".substr($_POST['content'],0,200)."\n");

            $debut_exercice=$exportService->date_mysql_to_html(date_url_to_mysql($debut_exercice));
            $fin_exercice=$exportService->date_mysql_to_html( date_url_to_mysql($fin_exercice));


            $ttc_found=$total_let=$let2fam=array();

            list($all_csv_elem, $total_let, $let2fam) = $exportService->post_content2csv_elem($fin_exercice, $debut_exercice, $exportmode);

            if($exportmode == 34) {
                $all_csv_elem = $exportService->filtre_achats_ventes($all_csv_elem);
                $all_csv_elem = $exportService->filtre_par_lettrage($all_csv_elem, $total_let, $let2fam);
            }

            $all_csv_elem = $exportService->get_code_lib_fam($all_csv_elem);
            $all_csv_elem= $exportService->filtre_par_ttc($all_csv_elem);


            if($exportmode == 34) {
                list($all_csv_elem_restant, $message) = $exportService->export_ecritures_lettrees($all_csv_elem, $total_let, $let2fam, $fin_exercice, $debut_exercice, $base, $cab_dossier, $exportmode,$long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $irfToken);
                $message .= $exportService->exportTOibiza($all_csv_elem_restant, $fin_exercice, $debut_exercice, $base, $cab_dossier, $exportmode,$long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $irfToken);
            } else {

                list($zip_dir, $zip_dir_name) = $exportService->get_zipdir_path($all_csv_elem[0]['date'], $base, $cab_dossier, $exportmode);

                $exportService->all_csv_elem_to_zip($all_csv_elem, $exportmode, $zip_dir, $zip_dir_name, $base,$cab_dossier, $fin_exercice, $debut_exercice,$long_cpt, $long_cpt_gen_ac, $long_cpt_gen_ve, $long_aux, $long_aux_ve, $sa1,$sa2,$sa3,$sa4,$sa5,$etablissement, 1 );




                $file_list = array();
                list($statusT, $messageT, $file_list) = $exportService->get_dir_content($zip_dir);

                if($exportmode==44) {
                //$zip_dir_name = str_replace(".auto", "", $zip_dir_name);

                } else if($exportmode==41) {
                $zip_dir_name = "$zip_dir_name.zip";
                } else {
                $zip_dir_name = "$zip_dir_name.ZIP";
                }

                $exportService->verbose_str_to_file(__FILE__, __FUNCTION__, "sortie all_csv_elem avec fichiers \n".print_r($file_list,1));

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
                    list($output, $status) = $exportService->launch_system_command($sh_cmd,0,1);
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

            $exportService->verbose_str_to_file(__FILE__, __FUNCTION__, "message retourne:\n$message\n");

            echo $message;
            /***************************************************************************************** */
            /***************************************************************************************** */
    }
}
