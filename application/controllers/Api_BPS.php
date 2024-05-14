<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api_BPS extends CI_Controller {
    var $view_dir   = "peppd1/integrasi/";
    var $js_init    = "main";
    var $js_path    = "assets/js/peppd1/list_data/list_data.js";
    
    function __construct() {
        parent::__construct();
        $this->load->model("M_Master","m_ref");
        $this->load->library("Excel");
        //require_once (APPPATH.'/libraries/Excel.php');
    }
    
    /*
     * 
     */
    public function index(){
        if($this->input->is_ajax_request()){
            try 
            {                
                if(!$this->session->userdata(SESSION_LOGIN)){throw new Exception("Session expired, please login",2);}
                
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
               // print_r($current_date_time);exit();
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/peppd1/integrasi/list_data_".$this->session->userdata(SESSION_LOGIN)->groupid.".js?v=".now("Asia/Jakarta");
                
                $data_page = array( );
                $str = $this->load->view($this->view_dir."content",$data_page,TRUE);

                $output = array(
                    "status"        =>  1,
                    "str"           =>  $str,
                    "js_path"       =>  base_url($this->js_path),
                    "js_initial"    =>  $this->js_init.".init();",
                    "csrf_hash"     => $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status"        =>  $exc->getCode(),
                    "msg"           =>  $exc->getMessage(),
                    "csrf_hash"     => $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            }

        }
        else{
            exit("access denied!");
        }
    }
    
    function pro_datatable(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $idx = 0;
                $columns = array( 
                // datatable column index  => database column name
                        $idx++   =>"A.id", 
                        $idx++   =>"A.domain_name",
                        $idx++   =>"A.domain_id", 
//                        $idx++   =>"A.`ppd`", 
                );
                $sql = "SELECT A.id,A.domain_id,A.`domain_name`
                        FROM `t_domain` A
                        WHERE 1=1 ";
                
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {  
                        $sql.=" AND ( "
                                . " A.`id` LIKE '%".$requestData['search']['value']."%' "
                                . " OR A.`domain_name` LIKE '%".$requestData['search']['value']."%' "
                           //     . " OR A.`label` LIKE '%".$requestData['search']['value']."%' "
                                . ")";    
                }
                $list_data = $this->db->query($sql);
                $totalFiltered = $list_data->num_rows();

                $sql.=" ORDER BY "
                        .$columns[$requestData['order'][0]['column']]."   "
                        .$requestData['order'][0]['dir']."  "
                        . "LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
                $list_data = $this->db->query($sql);
                $data = array();
                $i=1;
                foreach ($list_data->result() as $row) {
                    $nestedData=array(); 
                    $id     = $row->domain_id;
                    $nestedData[] = $row->id;
                    $nestedData[] = $row->domain_name;
                    $nestedData[] = "";
                    $tmp = " data-id='".encrypt_text($id)."' ";
                    $wil = " data-wil='".$row->domain_name."' ";
                    $nama = " data-nama='".$row->domain_name."' ";
                    $nestedData[] = ""
                    . "<input type='radio' class='checkbox' name='group' $tmp $wil  value='".$row->domain_name."'  /> ";
                    $data[] = $nestedData;
                }
                $json_data = array(
                    "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval( $totalData ),  // total number of records
                    "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $data   // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
                }
        else
            die;
    }
    
    function kab_datatable(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
               // $this->form_validation->set_rules('id','ID','required');
                //$prov = $this->input->post("id");
                
                //$idprov = decrypt_text($prov);
                //cari
                $idx = 0;
                $columns = array( 
                        $idx++   =>"K.val", 
                        $idx++   =>"K.label",

                );
                $sql = "SELECT K.`val`, K.`label` FROM `a_s_ind` K WHERE ppd=1";
                
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {  
                        $sql.=" AND ( "
                                . " K.`val` LIKE '%".$requestData['search']['value']."%' "
                                . " OR K.`label` LIKE '%".$requestData['search']['value']."%' "
                                . ")";    
                }
                $list_data = $this->db->query($sql);
                $totalFiltered = $list_data->num_rows();

                $sql.=" ORDER BY "
                        .$columns[$requestData['order'][0]['column']]."   "
                        .$requestData['order'][0]['dir']."  "
                        . "LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
                $list_data = $this->db->query($sql);
                $data = array();
                $i=1;
                foreach ($list_data->result() as $row) {
                    $nestedData=array(); 
                    $id     = $row->val;
                    
                    $nestedData[] = $row->val;
                    $nestedData[] = $row->label ;
                    $tmp = " data-id='".encrypt_text($id)."' ";
                    $tmp_nm = " data-in='".$row->label."' ";
                    $nestedData[] = ""
                            . "<input type='radio' class='checkbox' name='group' $tmp $tmp_nm /> ";
                    $data[] = $nestedData;
                }
                $json_data = array(
                    "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval( $totalData ),  // total number of records
                    "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $data   // total data array
                    );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
                }
        else
            die;
    }
    
    function list_kategori(){
        if($this->input->is_ajax_request()){
            try {
//                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
//                $session = $this->session->userdata(SESSION_LOGIN);
//                session_write_close();

                
                $this->form_validation->set_rules('id','ID Data Wilayah','required');
               
                $idwlyh = decrypt_text($this->input->post("id"));
                if($idwlyh=='')
                    $domain='0000';
                else
                    $domain=$idwlyh;
                $base_url = "https://";
               $linkapi = file_get_contents($base_url ."webapi.bps.go.id/v1/api/list/model/subcat/lang/ind/"
                       . "domain/$domain/"
                       . "key/3f7220b76475c7476f90fb8eb25a2f36/");
                $json_object =  json_decode($linkapi);
                $str="";
                foreach ($json_object->data['1'] as $v) {
                    $tmp = "class='getDetail' data-id='".$v->subcat_id."'";
                    $tmp1 = "class='getDetail' data-title='".$v->title."'";
                    $str.="<tr>";
                    $str.="<td class='text'>".$v->subcat_id."</td>";
                    $str.="<td  class='text'><a href='javascript:void(0)' ".$tmp." ".$tmp1.">".$v->title."</a></td>";
                    $str.="</tr>";
                }
                
                $response = array(
                    "status"            => 1,   
                    "csrf_hash"         => $this->security->get_csrf_hash(),
                    "str"               => $str,
                    );
                $this->output
                        ->set_status_header(200)
                        ->set_content_type('application/json', 'utf-8')
                        ->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                        ->_display();
                exit;
            } catch (Exception $exc) {
                 $json_data = array(
                    "status"    => 0,
                    "csrf_hash" => $this->security->get_csrf_hash(),
                    "msg"       => $exc->getMessage(),
                    );
                exit(json_encode($json_data));
            }
                }
        else die("Die!");
    }
    /*
     * subjek kategori
     */
    function sub_view(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
                
                $this->form_validation->set_rules('wila','ID Wilayah','required');
                $this->form_validation->set_rules('kate','ID Kategori','required');
                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
               $idkate = $this->input->post("kate");
               $idwlyh = decrypt_text($this->input->post("wila"));
                if($idwlyh=='')
                    $domain='0000';
                else
                    $domain=$idwlyh;
//                https://webapi.bps.go.id/v1/api/list/model/subject/lang/ind/domain/0000/subcat/3/key/3f7220b76475c7476f90fb8eb25a2f36/
               $base_url = "https://";
               $linkapi = file_get_contents($base_url ."webapi.bps.go.id/v1/api/list/model/subject/lang/ind/"
                       . "domain/$domain/"
                       . "subcat/$idkate/"
                       . "key/3f7220b76475c7476f90fb8eb25a2f36/");
                $json_object =  json_decode($linkapi);
                
//               $sql="SELECT A.*
//                    FROM `a_subject_sub` A 
//                    WHERE A.subcat_id=? ORDER BY sub_id";
//                $bind = array($idkate);
//                $list_data = $this->db->query($sql,$bind);
//                if(!$list_data){
//                    $msg = $session->userid." ".$this->router->fetch_class()." : ".$this->db->error()["message"];
//                    log_message("error", $msg);
//                    throw new Exception("Invalid SQL!");
//                }
                
                $str="";
//                if($list_data->num_rows()==0)
//                    $str = "<tr><td colspan='8'>Data tidak ditemukan</td></tr>";
                $no=1;
                foreach ($json_object->data['1'] as $v) {
                    $tmp = "class='getDetail' data-id='".$v->sub_id."'";
                    $tmp1 = "class='getDetail' data-title='".$v->title."'";
                    $str.="<tr class='' >";
                    $str.="<td class='text'>".$v->sub_id."</td>";
                    $str.="<td  class='text'><a href='javascript:void(0)' ".$tmp." ".$tmp1.">".$v->title."</a></td>";
                 
                    $str.="</tr>";
                }
                
                //sukses
                $output = array(
                    "status"        =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "str"       => $str,
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "msg"       =>  $exc->getMessage(),
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            }
        }
        else{exit("Access Denied");}
    }
    
    /*
     * subjek variabel
     */
    function sub_variabel(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
                
                $this->form_validation->set_rules('wila','ID Wilayah','required');
                $this->form_validation->set_rules('subdata','ID Kategori','required');
                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                $idwlyh  = decrypt_text($this->input->post("wila"));
                $subject = $this->input->post("subdata");
                $str='';
               $base_url = "https://";
                $linkapi = file_get_contents($base_url ."webapi.bps.go.id/v1/api/list/model/var/lang/ind"
                        . "/domain/".$idwlyh.""
                        . "/subject/".$subject.""
                        . "/key/3f7220b76475c7476f90fb8eb25a2f36/");
                $json_object =  json_decode($linkapi);
                $no=0;
               foreach ($json_object->data['1'] as $a) {
                   $tmp = "class='getDetail' data-id='".$a->var_id."'";
                   
                   $str.="<tr class='' >";
                   $str.="<td class='text'>".$a->var_id."</td>";
                   $str.="<td  class='text'><a href='javascript:void(0)' ".$tmp.">".wordwrap($a->title,50,"<br/>")."</a></td>";
                   $str.="<td class='text'>".$a->unit."</td>";
                   $str.="<td class='text'>".($a->notes)."</td>";
                   $str.="</tr>";
               }

                
                //sukses
                $output = array(
                    "status"        =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "str"       => $str,
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "msg"       =>  $exc->getMessage(),
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            }
        }
        else{exit("Access Denied");}
    }
    
        /*
     * subjek data
     */
    function nilai_data(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
                
                $this->form_validation->set_rules('wila','ID Wilayah','required');
                $this->form_validation->set_rules('subdata','ID Kategori','required');
                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                $idwlyh  = decrypt_text($this->input->post("wila"));
                $subject = $this->input->post("subdata");
                $str_b='';
                $str_h='';
                $str_h1='';
                $str1='';
                $str2='';
                $str22='';
                $str23='';
                $str3='';
                $str33='';
                $str333='';
                $strXX='';
               $base_url = "https://";
               $linkapi = file_get_contents($base_url ."webapi.bps.go.id/v1/api/list/model/data/lang/ind"
                       . "/domain/".$idwlyh.""
                       . "/var/".$subject.""
                       . "/key/3f7220b76475c7476f90fb8eb25a2f36/");
                $json_object =  json_decode($linkapi);
                $n1=0;
                $n11=0;
                $th1=0;
                
                $th2=0;
                
                $th222=0;
                
                $val_label    = $json_object->var['0']->val;
                $count_tahun    = count($json_object->tahun);
                $count_turtahun = count($json_object->turtahun);
                $count_turvar   = count($json_object->turvar);
                $count_vervar   = count($json_object->vervar);
                if($count_turtahun >= $count_turvar){
                    $colspan1=$count_turtahun;
                }else{
                    $colspan1=$count_turvar;
                }

                for($i=0;$i<$count_turvar;$i++){
                    $str33.="<th>".$json_object->turvar[$th2++]->label."</th >";
                    $str333.=$json_object->turvar[$th222++]->val;
                }
                
                for($i=0;$i<$count_turtahun;$i++){
                            $str22.="<th colspan=".$count_turvar.">".$json_object->turtahun[$th1++]->label."</th >";
                            $str23.=$str33;
                }
                
                foreach ($json_object->tahun as $a) {
                    //$t_str1.=$json_object->tahun[$n11++]->val;
                    $str1.="<th colspan=".$colspan1.">".$json_object->tahun[$n1++]->label."</th >";
                    $str2.=$str22;
                    $str3.="$str23";
                          
               }
              
                $str_h.="<tr ><th rowspan='4'>".$json_object->labelvervar."</th ></tr>";
                $str_h.="<tr >".$str1." </tr>";
                $str_h.="<tr >".$str2."</tr >";
                $str_h.="<tr >".$str3."</tr >";
                
                $thb1=0;
                $thb11=0;
                $thb2=0;
                $thb22=0;
                $thb3=0;
                $thb33=0;
                $str_b1='';
                $str_b11='';
                $str_b111='';
                $strb2='';
                $strb3='';
                $strb4='';
                $t_var='';
                $s1='';
                $s2='';
                $s22='';
//                foreach ($json_object->tahun as $b) {
//                    $s2=$json_object->tahun[$thb22++]->val;
//               }
                //domain-var-turvar-tahun-turtahun
               $count_datacontent    = count($json_object->datacontent);
              for($i=0;$i<$count_turvar;$i++){
                  $s1=$json_object->turvar[$thb11++]->val;
                  //$s22=$idwlyh."_".$subject."_".$s1."_".$s2."";
                  $str_b1.="<td id='".$json_object->turvar[$thb1++]->val."'>$s22</td >";
                  //$str_b1.=$json_object->turvar[$thb1++]->val;
                  
                }
                for($i=0;$i<$count_turtahun;$i++){
                    $str_b11.="<th colspan=".$count_turvar.">".$json_object->turtahun[$thb2++]->val."</th >";
                    $strb2.=$str_b1;
                }
                foreach ($json_object->tahun as $a) {
                    //$s2=$json_object->tahun[$thb22++]->val;
                    $idth=$json_object->tahun[$thb3++]->val;
                    $str_b111.="<th colspan=".$colspan1." id_th=".$idth."></th >";
                    $strb3.=$str_b11;
                    $strb4.=$strb2;
                    $s22=$idwlyh."_".$subject."_".$s1."_".$idth."";
                    //$strb4.="<td id='".$strb2."'>$s22</td >";          
               }
                // if()
               //$str_h1.="<td >".$strb3."</td >";
               //$str_h1.=$str_b1;
               //$str_h1.=$strb3;
               $str_h1.=$strb4;
                
                $nol=0;
                $str_vvv='';
                
                
                
                foreach ($json_object->vervar as $v) {
//                for($i=0;$i<$count_vervar;$i++){
                    $str_vvv.="<tr class='odd'>";
                    $str_vvv.="<td class=''>".$json_object->vervar[$nol++]->label." </td>";
                    $str_vvv.=$str_h1;
                    $str_vvv.="</tr>";
                }
                
                $str_b.=$str_vvv;
                
                //$test= $json_object->datacontent;
//                print_r($test);
                
                //sukses
                $output = array(
                    "status"        =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "str_h"       => $str_h,
                    "str"       => $str_b,
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "msg"       =>  $exc->getMessage(),
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            }
        }
        else{exit("Access Denied");}
    }
    
    
}
//Subject Categories 
//https://webapi.bps.go.id/v1/api/list/model/subcat/lang/ind/domain/0000/page/1,2,3,4/key/3f7220b76475c7476f90fb8eb25a2f36/
//
//https://webapi.bps.go.id/v1/api/list/model/subject/lang/ind/domain/0000/subcat/3/key/3f7220b76475c7476f90fb8eb25a2f36/
//data
//https://webapi.bps.go.id/v1/api/list/model/data/lang/ind/domain/0000/var/98/vervar/9999/th/121/key/3f7220b76475c7476f90fb8eb25a2f36/

//https://webapi.bps.go.id/v1/api/list/model/var/lang/ind/domain/0000/subject/5/key/3f7220b76475c7476f90fb8eb25a2f36/

//https://webapi.bps.go.id/v1/api/list/model/data/lang/ind/domain/1100/var/34/key/3f7220b76475c7476f90fb8eb25a2f36/