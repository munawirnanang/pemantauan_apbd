<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api_domain extends CI_Controller {
    var $view_dir   = "peppd1/api/";
    var $js_init    = "main";
    var $js_path    = "assets/js/peppd1/api/api.js";
    
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
                
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/peppd1/api/api_".$this->session->userdata(SESSION_LOGIN)->groupid.".js?v=".now("Asia/Jakarta");
                
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
    
    
    
    function g_indikator(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
                
                
//                $satker= $this->session->userdata(SESSION_LOGIN)->satker;
                
                   $sql = "SELECT D.*
                            FROM `a_s_ind` D
                            WHERE 1=1 ORDER BY D.val";
  //                  $bind = array($satker);
                    $list_data = $this->db->query($sql);
                    
                if(!$list_data){
                    $msg = $session->userid." ".$this->router->fetch_class()." : ".$this->db->error()["message"];
                    log_message("error", $msg);
                    throw new Exception("Invalid SQL!");
                }
                
                $str="";
                if($list_data->num_rows()==0)
                    $str = "<tr><td colspan='2'>Data tidak ditemukan</td></tr>";
                
                $no=1;
                foreach ($list_data->result() as $v) {
                    $idcomb = $v->id;
//                    $encrypted_id= base64_encode(openssl_encrypt($idcomb,"AES-128-ECB",ENCRYPT_PASS));
//                    $tmp = "class='btnDel' data-id='".$encrypted_id."'";
//                    $tmp .= " data-title='".$v->domain_name."'";
 
                    $str.="<tr class='' title=''>";                    
                    $str.="<td  class='text'>".$v->val."</td>";
                    $str.="<td  class='text'>".$v->label."</td>";
                    $str.="<td  class='text'>".$v->unit."</td>";
                    $str.="<td  class='text'>".$v->subj."</td>";
                    $str.="<td  class='text'>".$v->def."</td>";
                    $str.="<td  class='text'>".$v->decimal."</td>";
                    $str.="<td  class='text'>".$v->note."</td>";
                    $str.="<td  class='text'>".$v->labelvervar."</td>";
                    
                    $str.="</tr>";
                   
                   
                }
                $response = array(
                    "status"    => 1,   
                    "csrf_hash" => $this->security->get_csrf_hash(),
                    "str"       => $str,
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
    
    function a_subind(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
                $this->form_validation->set_rules('tvar','ID Data','required');
                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                $tvar = $this->input->post("tvar");
                $stream_opts=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                
                $base_url = "https://";
                $linkapi = file_get_contents($base_url ."webapi.bps.go.id/v1/api/list/model/data/lang/ind/domain/0000/var/".$tvar."/key/3f7220b76475c7476f90fb8eb25a2f36/",false, stream_context_create($stream_opts));
                $json_object_i =  json_decode($linkapi,false);
                $jsonvar = $json_object_i->{'data-availability'};
                if($jsonvar =="list-not-available"){
                    $msg = "Invalid";
                    log_message("error", $msg);
                    throw new Exception("Invalid SQL!");
                }
                 $i=1;
                $count_insert1 = 0;
                $count_insert2 = 0;
                $count_insert3 = 0;
                $count_insert4 = 0;
                $count_insert5 = 0;
                $count_insert6 = 0;
                $count_insert7 = 0;
                foreach($json_object_i as $a):
                    
                    foreach ($json_object_i->{'var'} as $a):
                    $vall       = $a->val;
                    $label      = $a->label;
                    $unit       = $a->unit;
                    $subj       = $a->subj;
                    $defl       = $a->def;
                    $decimal    = $a->decimal;
                    $note        = $a->note;
                    $labelvervar = $json_object_i->labelvervar;
                    $val_label   = $a->val;
                    $label_l     = $a->label;
                    
                    $qry_d1 = $this->db->query("SELECT `val` FROM `a_s_ind` WHERE `val` = '".$vall."'");
                    //print_r($qry_kr);exit();
                    if($qry_d1->num_rows() == 0){
                        $qry_d_new1= array(
                            "val"   => $vall,
                            "label" => $label,
                            "unit"  => $unit,
                            "subj"  => $subj,
                            "def"   => $defl,
                            "decimal"   => $decimal,
                            "note"  => $note,
                            "labelvervar"   => $labelvervar,
                            "ppd"   => '1',
                        );
                        $qry_save1 = $this->db->insert("a_s_ind",$qry_d_new1);
                        if(!$qry_save1){
                            echo "Gagal Save data, DB Rollback";
                            $this->log->write_log("DB Rollback, id : ".$vall);
                            $this->db->trans_rollback();
                        }
                        $count_insert1++;
                    }
                    endforeach;
                    
                    foreach ($json_object_i->{'turvar'} as $b):

                        $vall        = $b->val;
                        $label       = $b->label;
                        $var_turvar  = $b->val;
                        $qry_d2 = $this->db->query("SELECT `id_s_ind`, val FROM `a_s_ind_turvar` WHERE `id_s_ind` = '".$tvar."' AND val= '".$vall."' ");
                        if($qry_d2->num_rows() == 0){
                            $qry_d_new2= array(
                                "val"           => $vall,
                                "label"         => $label,
                                "id_s_ind"         => $tvar,

                            );
                            $qry_save2 = $this->db->insert("a_s_ind_turvar",$qry_d_new2);
                            if(!$qry_save2){
                                echo "Gagal Save data, DB Rollback";
                                $this->log->write_log("DB Rollback, id : ");
                                $this->db->trans_rollback();
                            }
                            $count_insert2++;
                        }
                    endforeach;
                    
                    foreach ($json_object_i->{'turtahun'} as $c):
                        $vall        = $c->val;
                        $label       = $c->label;
                        $var_turtahun= $c->val;
                        $qry_d = $this->db->query("SELECT `id_s_ind`, val FROM `a_s_ind_turtahun` WHERE `id_s_ind` = '".$tvar."' AND val= '".$vall."' ");
                        if($qry_d->num_rows() == 0){
                            $qry_d_new= array(
                                "val"           => $vall,
                                "label"         => $label,
                                "id_s_ind"         => $tvar,

                            );
                            $qry_save = $this->db->insert("a_s_ind_turtahun",$qry_d_new);
                            if(!$qry_save){
                                echo "Gagal Save data, DB Rollback";
                                $this->log->write_log("DB Rollback, id : ");
                                $this->db->trans_rollback();
                            }
                            $count_insert3++;
                        }
                    endforeach;
                    
                    foreach ($json_object_i->{'vervar'} as $d):
                        $var_vervar        = $d->val;
                        $var_vervar1        = $d->val;
                        $lab_vervar        = $d->label;
                        $qry_d = $this->db->query("SELECT * FROM `a_s_ind_varvar` "
                                . "WHERE `id_s_ind` = '".$tvar."' AND val= '".$var_vervar."' ");
                        if($qry_d->num_rows() == 0){
                            $qry_d_new= array(
                                "val"           => $var_vervar,
                                "label"         => $lab_vervar,
                                "id_s_ind"      => $tvar,

                            );
                            $qry_save = $this->db->insert("a_s_ind_varvar",$qry_d_new);
                            if(!$qry_save){
                                echo "Gagal Save data, DB Rollback";
                                $this->log->write_log("DB Rollback, id : ");
                                $this->db->trans_rollback();
                            }
                            $count_insert4++;
                        }
                        
                    endforeach;
                    
                    foreach ($json_object_i->{'turtahun'} as $e):
                        $vall        = $e->val;
                        $label       = $e->label;
                        $var_turtahun= $e->val;
                        $qry_d = $this->db->query("SELECT `id_s_ind`, val FROM `a_s_ind_turtahun` WHERE `id_s_ind` = '".$tvar."' AND val= '".$vall."' ");
                        if($qry_d->num_rows() == 0){
                            $qry_d_new= array(
                                "val"           => $vall,
                                "label"         => $label,
                                "id_s_ind"         => $tvar,

                            );
                        $qry_save = $this->db->insert("a_s_ind_turtahun",$qry_d_new);
                        if(!$qry_save){
                            echo "Gagal Save data, DB Rollback";
                            $this->log->write_log("DB Rollback, id : ");
                            $this->db->trans_rollback();
                        }
                        $count_insert5++;
                        }
                    endforeach;
                   
                  endforeach;  
                  
                    $count_insert61=0;
                    //foreach ($json_object_i->{'datacontent'} as $g1):
                    foreach ($json_object_i as $g1):
                        foreach ($json_object_i->{'vervar'} as $d22){
                            $var_vervar2        = $d22->val;
            }
                        foreach ($json_object_i->{'turvar'} as $d1){
                            $var_turvar_1  = $d1->val;
            }    
                        
                        foreach ($json_object_i->{'tahun'} as $f1){
                        $var_tahun1        = $f1->val;}
                        
                        foreach ($json_object_i->{'turtahun'} as $e1){
                            $var_turtahun_1= $e1->val;
                        }
                        
                        $var_vervar   =  $var_vervar2;
                        $val_label1   =  $tvar;
                        $var_turvar1  = $var_turvar_1;
                        $var_tahun    = $var_tahun1;
                        $var_turtahun1= $var_turtahun_1;
                        $versi       =  $var_vervar.$val_label1.$var_turvar1.$var_tahun.$var_turtahun1;
                        print_r($versi);                        echo "</Br>";//exit();
                        $nilai_data = $json_object_i->datacontent->$versi;
                        //$nilai_data = $g1->$versi;
                        $sql = "SELECT * FROM a_s_ind_nilai WHERE val='".$tvar."' AND  versi= '".$versi."' ";
                     //   
                       $qry_d4 =$this->db->query($sql); 
                       if($qry_d4->num_rows() == 0){
                            $qry_d_new4= array(
                                "val"           => $tvar,
                                "label"         => $label_l,
                                "turvar_var"    => $var_turvar,
                                "vervar_var"    => $var_vervar,
                                "tahun_var"     => $var_tahun,
                                "turtahun"      => $var_turtahun,
                                "data"          => $nilai_data,
                                "versi"         => $versi,
                            );
                        $qry_save4 = $this->db->insert("a_s_ind_nilai",$qry_d_new4);
                        if(!$qry_save4){
                            echo "Gagal Save data, DB Rollback";
                            $this->log->write_log("DB Rollback, id : ".$val_label);
                            $this->db->trans_rollback();
                        }
                          $count_insert61++;
                        }
                        
                    endforeach; 
                
              
                
                $response = array(
                    "status"    => 1,   
                    "csrf_hash" => $this->security->get_csrf_hash(),
                  //  "str"       => $json_object,
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
    
//    foreach ($json_object_i as $a) {
//                    $vall        = $json_object_i->{'turvar'}->val;
//                    $label       = $json_object_i->{'turvar'}->label;
//                    $var_turvar  = $json_object_i->{'turvar'}->val;
//                    $qry_d2 = $this->db->query("SELECT `id_s_ind`, val FROM `a_s_ind_turvar` WHERE `id_s_ind` = '".$tvar."' AND val= '".$vall."' ");
//                    if($qry_d2->num_rows() == 0){
//                        $qry_d_new2= array(
//                            "val"           => $vall,
//                            "label"         => $label,
//                            "id_s_ind"         => $tvar,
//                            
//                        );
//                        $qry_save2 = $this->db->insert("a_s_ind_turvar",$qry_d_new2);
//                        if(!$qry_save2){
//                            echo "Gagal Save data, DB Rollback";
//                            $this->log->write_log("DB Rollback, id : ");
//                            $this->db->trans_rollback();
//                        }
//                    }
//                    
//                    
//                    $vall        = $json_object_i->{'turtahun'}->val;
//                    $label       = $json_object_i->{'turtahun'}->label;
//                    $var_turtahun= $json_object_i->{'turtahun'}->val;
//                    $qry_d3 = $this->db->query("SELECT `id_s_ind`, val FROM `a_s_ind_turtahun` WHERE `id_s_ind` = '".$tvar."' AND val= '".$vall."' ");
//                    if($qry_d3->num_rows() == 0){
//                        $qry_d_new3= array(
//                            "val"           => $vall,
//                            "label"         => $label,
//                            "id_s_ind"         => $tvar,
//                            
//                        );
//                        $qry_save3 = $this->db->insert("a_s_ind_turtahun",$qry_d_new3);
//                        if(!$qry_save3){
//                            echo "Gagal Save data, DB Rollback";
//                            $this->log->write_log("DB Rollback, id : ");
//                            $this->db->trans_rollback();
//                        }
//                    }
//                    $var_vervar        = $json_object_i->{'vervar'}->val;
//                    $var_tahun        = $json_object_i->{'tahun'}->val;
//                    $versi       = $var_vervar.$val_label.$var_turvar.$var_tahun.$var_turtahun;
//                    
//                    $nilai_data  =$json_object_i->datacontent->$versi;
//                    
//                    $qry_d4 = $this->db->query("SELECT * FROM a_s_ind_nilai "
//                            . "AND  versi= '".$versi."' ");
//                    if($qry_d4->num_rows() == 0){
//                        $qry_d_new4= array(
//                            "val"           => $val_label,
//                            "label"         => $label_l,
//                            "turvar_var"    => $var_turvar,
//                            "vervar_var"    => $var_vervar,
//                            "tahun_var"     => $var_tahun,
//                            "turtahun"      => $var_turtahun,
//                            "data"          => $nilai_data,
//                            "versi"         => $versi,
//                        );
//                    $qry_save4 = $this->db->insert("a_s_ind_nilai",$qry_d_new4);
//                    if(!$qry_save4){
//                        echo "Gagal Save data, DB Rollback";
//                        $this->log->write_log("DB Rollback, id : ".$val_label);
//                        $this->db->trans_rollback();
//                    }
//                   
//                    }
//                     $count_insert++;
//                }
    
    
    function a_subindOK(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
                $this->form_validation->set_rules('tvar','ID Data','required');
                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                $tvar = $this->input->post("tvar");
                $stream_opts=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                
                $base_url = "https://";
                $linkapi = file_get_contents($base_url ."webapi.bps.go.id/v1/api/list/model/data/lang/ind/domain/0000/var/".$tvar."/key/3f7220b76475c7476f90fb8eb25a2f36/",false, stream_context_create($stream_opts));
                $json_object_i =  json_decode($linkapi,false);
                $jsonvar = $json_object_i->{'data-availability'};
                if($jsonvar =="list-not-available"){
                    $msg = "Invalid";
                    log_message("error", $msg);
                    throw new Exception("Invalid SQL!");
                }
                 $i=1;
//                $count_insert = 0;
//                foreach ($json_object_i->var['0'] as $a) {
//                    $vall   = $json_object_i->var['0']->val;
//                    $label = $json_object_i->var['0']->label;
//                    $unit  = $json_object_i->var['0']->unit;
//                    $subj  = $json_object_i->var['0']->subj;
//                    $defl  = $json_object_i->var['0']->def;
//                    $decimal  = $json_object_i->var['0']->decimal;
//                    $note  = $json_object_i->var['0']->note;
//                    $labelvervar  = $json_object_i->labelvervar;
//                    
//                    $qry_d = $this->db->query("SELECT `val` FROM `a_s_ind` WHERE `val` = '".$vall."'");
//                    //print_r($qry_kr);exit();
//                    if($qry_d->num_rows() == 0){
//                        $qry_d_new= array(
//                            "val"   => $vall,
//                            "label" => $label,
//                            "unit"  => $unit,
//                            "subj"  => $subj,
//                            "def"   => $defl,
//                            "decimal"   => $decimal,
//                            "note"  => $note,
//                            "labelvervar"   => $labelvervar,
//                            "ppd"   => '1',
//                        );
//                    $qry_save = $this->db->insert("a_s_ind",$qry_d_new);
//                    if(!$qry_save){
//                        echo "Gagal Save data, DB Rollback";
//                        $this->log->write_log("DB Rollback, id : ".$subcat_id);
//                        $this->db->trans_rollback();
//                    }
//                    $count_insert++;
//                    }
//                }
                
                $count_insert = 0;
                 $count_insertttt=0;
                foreach ($json_object_i->{'var'} as $a) {
                    $vall   = $a->val;
                    $label = $a->label;
                    $unit  = $a->unit;
                    $subj  = $a->subj;
                    $defl  = $a->def;
                    $decimal    = $a->decimal;
                    $note  = $a->note;
                    $labelvervar    = $json_object_i->labelvervar;
                    
                    $val_label   = $a->val;
                    $label_l       = $a->label;
                    
                    $qry_d = $this->db->query("SELECT `val` FROM `a_s_ind` WHERE `val` = '".$vall."'");
                    //print_r($qry_kr);exit();
                    if($qry_d->num_rows() == 0){
                        $qry_d_new= array(
                            "val"   => $vall,
                            "label" => $label,
                            "unit"  => $unit,
                            "subj"  => $subj,
                            "def"   => $defl,
                            "decimal"   => $decimal,
                            "note"  => $note,
                            "labelvervar"   => $labelvervar,
                            "ppd"   => '1',
                        );
                    $qry_save = $this->db->insert("a_s_ind",$qry_d_new);
                    if(!$qry_save){
                        echo "Gagal Save data, DB Rollback";
                        $this->log->write_log("DB Rollback, id : ".$vall);
                        $this->db->trans_rollback();
                    }
                    $count_insert++;
                    }
                }
                
                $count_insertt=0;
                foreach ($json_object_i->{'turvar'} as $t){
                    $vall   = $t->val;
                    $label   = $t->label;
                    $var_turvar  = $t->val;
                    $qry_d = $this->db->query("SELECT `id_s_ind`, val FROM `a_s_ind_turvar` WHERE `id_s_ind` = '".$tvar."' AND val= '".$vall."' ");
                    if($qry_d->num_rows() == 0){
                        $qry_d_new= array(
                            "val"           => $vall,
                            "label"         => $label,
                            "id_s_ind"         => $tvar,
                            
                        );
                    $qry_save = $this->db->insert("a_s_ind_turvar",$qry_d_new);
                    if(!$qry_save){
                        echo "Gagal Save data, DB Rollback";
                        $this->log->write_log("DB Rollback, id : ");
                        $this->db->trans_rollback();
                    }
                    $count_insertt++;
                    }
                }
                
                $count_inserttt=0;
                foreach ($json_object_i->{'turtahun'} as $tt ){
                    $vall        = $tt->val;
                    $label       = $tt->label;
                    $var_turtahun= $tt->val;
                    $qry_d = $this->db->query("SELECT `id_s_ind`, val FROM `a_s_ind_turtahun` WHERE `id_s_ind` = '".$tvar."' AND val= '".$vall."' ");
                    if($qry_d->num_rows() == 0){
                        $qry_d_new= array(
                            "val"           => $vall,
                            "label"         => $label,
                            "id_s_ind"         => $tvar,
                            
                        );
                    $qry_save = $this->db->insert("a_s_ind_turtahun",$qry_d_new);
                    if(!$qry_save){
                        echo "Gagal Save data, DB Rollback";
                        $this->log->write_log("DB Rollback, id : ");
                        $this->db->trans_rollback();
                    }
                    $count_inserttt++;
                    }
                }
                
                $count_inser_vv=0;
                foreach ($json_object_i->{'vervar'} as $vv){
                    $var_vervar        = $vv->val;
                    $count_inser_vv++;
                }
                 $count_inser_th=0;
                foreach ($json_object_i->{'tahun'} as $th){
                    $var_tahun        = $th->val;
                    $versi       = $var_vervar.$val_label.$var_turvar.$var_tahun.$var_turtahun;
                    $count_inser_th++;
                }
                
               
               // $nilai_data='';
                foreach ($json_object_i->{'datacontent'} as $d){
                    
                    $nilai_data  =$json_object_i->datacontent->$versi;
                    
                    $qry_d = $this->db->query("SELECT * FROM a_s_ind_nilai "
                            . "WHERE val= '".$val_label."' "
//                            . "AND turvar_var= '".$var_turvar."' "
//                            . "AND  vervar_var= '".$var_vervar."' "
//                            . "AND  tahun_var= '".$var_tahun."' "
                            . "AND  versi= '".$versi."' ");
                  //  print_r($qry_d);exit();
                    if($qry_d->num_rows() == 0){
                        $qry_d_new= array(
                            "val"           => $val_label,
                            "label"         => $label_l,
                            "turvar_var"    => $var_turvar,
                            "vervar_var"    => $var_vervar,
                            "tahun_var"     => $var_tahun,
                            "turtahun"      => $var_turtahun,
                            "data"          => $nilai_data,
                            "versi"         => $versi,
                        );
                    $qry_save = $this->db->insert("a_s_ind_nilai",$qry_d_new);
                    if(!$qry_save){
                        echo "Gagal Save data, DB Rollback";
                        $this->log->write_log("DB Rollback, id : ".$val_label);
                        $this->db->trans_rollback();
                    }
                    $count_insertttt++;
                    }
                    
                }
                
                $response = array(
                    "status"    => 1,   
                    "csrf_hash" => $this->security->get_csrf_hash(),
                  //  "str"       => $json_object,
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
     * list data Bahan Dokumen
     * author :  FSM
     * date : 17 des 2020
     */
    function g_bahan(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
                
                
//                $satker= $this->session->userdata(SESSION_LOGIN)->satker;
                
                   $sql = "SELECT D.*
                            FROM `t_domain` D
                            WHERE 1=1 ";
  //                  $bind = array($satker);
                    $list_data = $this->db->query($sql);
                    
                if(!$list_data){
                    $msg = $session->userid." ".$this->router->fetch_class()." : ".$this->db->error()["message"];
                    log_message("error", $msg);
                    throw new Exception("Invalid SQL!");
                }
                
                $str="";
                if($list_data->num_rows()==0)
                    $str = "<tr><td colspan='3'>Data tidak ditemukan</td></tr>";
                
                $no=1;
                foreach ($list_data->result() as $v) {
                    $idcomb = $v->id;
//                    $encrypted_id= base64_encode(openssl_encrypt($idcomb,"AES-128-ECB",ENCRYPT_PASS));
//                    $tmp = "class='btnDel' data-id='".$encrypted_id."'";
//                    $tmp .= " data-title='".$v->domain_name."'";
 
                    $str.="<tr class='' title=''>";
                    $str.="<td class='text-right'>".$no++."</td>";                    
                    $str.="<td  class='text'>".$v->domain_id."</td>";
                    $str.="<td  class='text'>".$v->domain_name."</td>";
                    $str.="<td  class='text'>".$v->domain_url."</td>";
                    
                    $str.="</tr>";
                   
                   
                }
                $response = array(
                    "status"    => 1,   
                    "csrf_hash" => $this->security->get_csrf_hash(),
                    "str"       => $str,
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
    
    
    function g_restapi(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
                
                $domain_name    = "";
                $domain_url     = "";
                $stream_opts=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);  
                $base_url = "https://";
                $linkapi = file_get_contents($base_url ."webapi.bps.go.id/v1/api/domain/type/all/prov/4/key/3f7220b76475c7476f90fb8eb25a2f36/");
                $json_object =  json_decode($linkapi);
                
                $i=1;
                $count_insert = 0;
                $domain_id    = 0;
                foreach ($json_object->data['1'] as $a) {
//                    $page   = $a->page;
//                    $pages   = $a->page;
//                    $total   = $a->total;
                    
                    $domain_id   = $a->domain_id;
                    $domain_name =$a->domain_name;
                    $domain_url  = $a->domain_url;
                    
                    $qry_d = $this->db->query("SELECT `domain_id` FROM `t_domain` WHERE `domain_id` = '".$domain_id."'");
                    //print_r($qry_kr);exit();
                    if($qry_d->num_rows() == 0){
                        $qry_d_new= array(
                            "domain_id"           => $domain_id,
                            "domain_name"         => $domain_name,
                            "domain_url"          => $domain_url,
                        );
                    $qry_save = $this->db->insert("t_domain",$qry_d_new);
                    if(!$qry_save){
                        echo "Gagal Save data, DB Rollback";
                        $this->log->write_log("DB Rollback, id : ".$domain_id);
                        $this->db->trans_rollback();
                    }
                    $count_insert++;
                    }
                    
                }
//                $satker= $this->session->userdata(SESSION_LOGIN)->satker;
                
    
                $response = array(
                    "status"    => 1,   
                    "csrf_hash" => $this->security->get_csrf_hash(),
                  //  "str"       => $json_object,
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
    
    function g_subject(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
                
                
//                $satker= $this->session->userdata(SESSION_LOGIN)->satker;
                
                   $sql = "SELECT D.*
                            FROM `a_subject` D
                            WHERE 1=1 ";
  //                  $bind = array($satker);
                    $list_data = $this->db->query($sql);
                    
                if(!$list_data){
                    $msg = $session->userid." ".$this->router->fetch_class()." : ".$this->db->error()["message"];
                    log_message("error", $msg);
                    throw new Exception("Invalid SQL!");
                }
                
                $str="";
                if($list_data->num_rows()==0)
                    $str = "<tr><td colspan='2'>Data tidak ditemukan</td></tr>";
                
                $no=1;
                foreach ($list_data->result() as $v) {
                    $idcomb = $v->id;
//                    $encrypted_id= base64_encode(openssl_encrypt($idcomb,"AES-128-ECB",ENCRYPT_PASS));
//                    $tmp = "class='btnDel' data-id='".$encrypted_id."'";
//                    $tmp .= " data-title='".$v->domain_name."'";
 
                    $str.="<tr class='' title=''>";                    
                    $str.="<td  class='text'>".$v->subcat_id."</td>";
                    $str.="<td  class='text'>".$v->subcat."</td>";
                    
                    $str.="</tr>";
                   
                   
                }
                $response = array(
                    "status"    => 1,   
                    "csrf_hash" => $this->security->get_csrf_hash(),
                    "str"       => $str,
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
    
    function g_subjectsub(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
                
                
//                $satker= $this->session->userdata(SESSION_LOGIN)->satker;
                
                   $sql = "SELECT D.*
                            FROM `a_subject_sub` D
                            WHERE 1=1 Order By subcat_id, sub_id";
  //                  $bind = array($satker);
                    $list_data = $this->db->query($sql);
                    
                if(!$list_data){
                    $msg = $session->userid." ".$this->router->fetch_class()." : ".$this->db->error()["message"];
                    log_message("error", $msg);
                    throw new Exception("Invalid SQL!");
                }
                
                $str="";
                if($list_data->num_rows()==0)
                    $str = "<tr><td colspan='2'>Data tidak ditemukan</td></tr>";
                
                $no=1;
                foreach ($list_data->result() as $v) {
                    $idcomb = $v->id;
//                    $encrypted_id= base64_encode(openssl_encrypt($idcomb,"AES-128-ECB",ENCRYPT_PASS));
//                    $tmp = "class='btnDel' data-id='".$encrypted_id."'";
//                    $tmp .= " data-title='".$v->domain_name."'";
 
                    $str.="<tr class='' title=''>";                    
                    $str.="<td  class='text'>".$v->sub_id."</td>";
                    $str.="<td  class='text'>".$v->title."</td>";
                    $str.="<td  class='text'>".$v->subcat_id."</td>";
                    
                    $str.="</tr>";
                   
                   
                }
                $response = array(
                    "status"    => 1,   
                    "csrf_hash" => $this->security->get_csrf_hash(),
                    "str"       => $str,
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
    
    
    
    function a_subject(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
                
                $domain_name    = "";
                $domain_url     = "";
                
                $base_url = "https://";
//                $linkapi = file_get_contents($base_url ."webapi.bps.go.id/v1/api/list/model/subject/lang/ind/domain/0000/subcat/3/page/1,2,3,4/key/3f7220b76475c7476f90fb8eb25a2f36/");
                $linkapi = file_get_contents($base_url ."webapi.bps.go.id/v1/api/list/model/subject/lang/ind/domain/1100/subcat/1/key/3f7220b76475c7476f90fb8eb25a2f36/");
                $json_object =  json_decode($linkapi);

                $i=1;
                $count_insert = 0;
                $count_insertd = 0;
                foreach ($json_object->data['1'] as $a) {
                    
                    $sub_id   = $a->sub_id;
                    $title =$a->title;
                    $subcat_id   = $a->subcat_id;
                    $subcat   = $a->subcat;
                    $ntabel =$a->ntabel;
                    
                    $qry_d = $this->db->query("SELECT `subcat_id` FROM `a_subject` WHERE `subcat_id` = '".$subcat_id."'");
                    //print_r($qry_kr);exit();
                    if($qry_d->num_rows() == 0){
                        $qry_d_new= array(
                            "subcat_id"           => $subcat_id,
                            "subcat"         => $subcat,
                        );
                    $qry_save = $this->db->insert("a_subject",$qry_d_new);
                    if(!$qry_save){
                        echo "Gagal Save data, DB Rollback";
                        $this->log->write_log("DB Rollback, id : ".$subcat_id);
                        $this->db->trans_rollback();
                    }
                    $count_insert++;
                    }
                    $qry_ds = $this->db->query("SELECT `sub_id` FROM `a_subject_sub` WHERE `sub_id` = '".$sub_id."'");
                    if($qry_ds->num_rows() == 0){
                        $qry_ds_new= array(
                            "sub_id"         => $sub_id,
                            "title"         => $title,
                            "ntabel"         => $ntabel,
                            "subcat_id"      => $subcat_id,
                        );
                    $qry_dsave = $this->db->insert("a_subject_sub",$qry_ds_new);
                    if(!$qry_dsave){
                        echo "Gagal Save data, DB Rollback";
                        $this->log->write_log("DB Rollback, id : ".$sub_id);
                        $this->db->trans_rollback();
                    }
                    $count_insertd++;
                    }
                    
                    
                }
//                $satker= $this->session->userdata(SESSION_LOGIN)->satker;
                
    
                $response = array(
                    "status"    => 1,   
                    "csrf_hash" => $this->security->get_csrf_hash(),
                  //  "str"       => $json_object,
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
    
    
    
    
    
    
    
    
    function pro_datatable(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $idx = 0;
                $columns = array( 
                // datatable column index  => database column name
                        $idx++   =>"A.id", 
                        $idx++   =>"A.nama_wilayah",
//                        $idx++   =>"A.label", 
//                        $idx++   =>"A.`ppd`", 
                );
                $sql = "SELECT A.id,A.`nama_wilayah`
                        FROM `wilayah` A
                        WHERE 1=1 ";
                
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {  
                        $sql.=" AND ( "
                                . " A.`id` LIKE '%".$requestData['search']['value']."%' "
                                . " OR A.`nama_wilayah` LIKE '%".$requestData['search']['value']."%' "
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
                    $id     = $row->id;
                    $nestedData[] = $row->id;
                    $nestedData[] = $row->nama_wilayah;
                    $nestedData[] = "";
                    $tmp = " data-id='".encrypt_text($id)."' ";
                    $wil = " data-wil='".$row->nama_wilayah."' ";
                    $nama = " data-nama='".$row->nama_wilayah."' ";
                    $nestedData[] = ""
//                            . "<a class='btn btn-xs btn-info btnSelect' ".$tmp." title='Pilih Data'><i class='fa fa-hand-o-up'></i> Pilih</a>";
//                            . "<input type='checkbox' class='checkbox' $tmp  value='".$row->nama_provinsi."'  /> ";
                    . "<input type='radio' class='checkbox' name='group' $tmp $wil  value='".$row->nama_wilayah."'  /> ";
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
                        $idx++   =>"K.id", 
                        $idx++   =>"K.nama_indikator",

                );
                $sql = "SELECT K.`id`, K.`nama_indikator` FROM `indikator` K WHERE 1=1";
                
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {  
                        $sql.=" AND ( "
                                . " K.`id` LIKE '%".$requestData['search']['value']."%' "
                                . " OR K.`nama_indikator` LIKE '%".$requestData['search']['value']."%' "
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
                    $id     = $row->id;
                    
                    $nestedData[] = $row->id;
                    $nestedData[] = $row->nama_indikator ;
                    $tmp = " data-id='".encrypt_text($id)."' ";
                    $tmp_nm = " data-in='".$row->nama_indikator."' ";
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
    
    
    function add_act()
    {
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){
                    throw new Exception("Your session is ended, please relogin",2);
                }
                $this->form_validation->set_rules('inp_pro','Id Prov','required|xss_clean');
                $this->form_validation->set_rules('inp_proid','Nama Provinsi','required|xss_clean');
                $this->form_validation->set_rules('inp_idind','Id Indikator','required|xss_clean');
                $this->form_validation->set_rules('inp_kab','Nama Indikator','required|xss_clean');

                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                $idpro = decrypt_text($this->input->post("inp_proid"));
                $idind = decrypt_text($this->input->post("inp_idind"));
              
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                if($idind==1){
                $bulan  = array('00' => '', '01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');    
                } else {
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);    
                }
                
                
                //cari data 
                $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='".$idind."' AND wilayah='".$idpro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='".$idind."' AND wilayah='".$idpro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                
                $list_data  = $this->db->query($sql);
                $content="";
                foreach ($list_data->result() as $row) {
                    $periode                = $row->periode;
                    if($periode == '00'){ $thn=$row->tahun; //$thn2=$row->tahun; 
                    }
                    else{ $thn=  $bulan[$row->periode]." - ".$row->tahun; }
                    $nilai                = $row->nilai;
                    if($nilai > '0'){ $warna= "<td style='font-size: 11px'>".$row->nilai."</td>"; 
                    }
                    else{ $warna= "<td style='font-size: 11px; color:#ef3312;'>".$row->nilai."</td>"; }
                    
                    $content.="<tr class='odd gradeX'>";
                    $content.="<td style='font-size: 11px;'>".$row->tahun."</td>";
                    $content.="<td style='font-size: 11px'>".$thn."</td>";
                    $content.=$warna;
                    $content.="<td style='font-size: 11px'>".$row->nasional."</td>";
                    $content.="<td style='font-size: 11px'>".$row->target."</td>";
                    $content.="<td style='font-size: 11px'>".$row->t_m_rpjmn."</td>";
                    $content.="<td style='font-size: 11px'>".$row->t_rkpd."</td>";
                    $content.="<td style='font-size: 11px'>".$row->t_k_rkp."</td>";
                    $content.="<td style='font-size: 11px'>".$row->satuan."</td>";
                    $content.="<td style='font-size: 11px'>".$row->versi."</td>";
                    $content.="</tr>";
                }
                //style='font-size: 11px'> style="font-variant:small-caps;font-style:normal;color:black;font-size:18px;"
                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "content"       =>  $content,
                    "msg"       =>  ""
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"    =>  $exc->getMessage(),
                );
                exit(json_encode($output));
            }
        }
        else{exit("Access Denied");}
    }
    
    function Download_excel(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){
                    throw new Exception("Your session is ended, please relogin",2);
                }
                $this->form_validation->set_rules('inp_wl','Id Prov','required|xss_clean');
                $this->form_validation->set_rules('inp_in','Nama Provinsi','required|xss_clean');

                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                $idpro = decrypt_text($this->input->post("inp_proid"));
                $idind = decrypt_text($this->input->post("inp_idind"));
                
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                if($idind==1){
                $bulan  = array('00' => '', '01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');    
                } else {
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);    
                }
                
                
                               
                //cari data 
                $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='".$idind."' AND wilayah='".$idpro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='".$idind."' AND wilayah='".$idpro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                
                $list_data  = $this->db->query($sql);
                $content="";
                foreach ($list_data->result() as $row) {
                    $periode                = $row->periode;
                    if($periode == '00'){ $thn=$row->tahun; //$thn2=$row->tahun; 
                    }
                    else{ $thn=  $bulan[$row->periode]." - ".$row->tahun; }
                    $content.="<tr class='odd gradeX'>";
                    $content.="<td>".$row->tahun."</td>";
                    $content.="<td>".$thn."</td>";
                    $content.="<td>".$row->nilai."</td>";
                    $content.="<td>".$row->nasional."</td>";
                    $content.="<td>".$row->target."</td>";
                    $content.="<td>".$row->satuan."</td>";
                    $content.="<td>".$row->versi."</td>";
                    $content.="</tr>";
                }
                $this->load->library("Excel");
                $sharedStyleTitles = new PHPExcel_Style();
                
                //garis
                $sharedStyleTitles->applyFromArray(
                        array('borders' => 
                            array(
                                'bottom'=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'top'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'left'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'right'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                            )
                        ));
                
//                        $this->excel->getActiveSheet()->getRowDimension('3')->setRowHeight(50);
        
                $this->excel->getActiveSheet()->setSharedStyle($sharedStyleTitles, 'B4:K10');
                
                $this->excel->getActiveSheet()->setCellValue('B2', "");
                $this->excel->getActiveSheet()->setCellValue('B3', "Indikator :");
                $this->excel->getActiveSheet()->setCellValue("C3", "");
//                $this->excel->getActiveSheet()->setCellValue('C3', '$nm_indikator');
                
                $this->excel->getActiveSheet()->setCellValue("B4", "Tahun ");
                $this->excel->getActiveSheet()->setCellValue("C4", "Periode");
                $this->excel->getActiveSheet()->setCellValue("D4", "Nilai");
                $this->excel->getActiveSheet()->setCellValue("E4", "Nasional");
                $this->excel->getActiveSheet()->setCellValue("F4", "Target");
                $this->excel->getActiveSheet()->setCellValue("G4", "Target Makro RPJMN");
                $this->excel->getActiveSheet()->setCellValue("H4", "Target RKPD");
                $this->excel->getActiveSheet()->setCellValue("I4", "Target Kewilayahan RKP");
                $this->excel->getActiveSheet()->setCellValue("J4", "Satuan");
                $this->excel->getActiveSheet()->setCellValue("K4", "Versi");
//                $this->excel->getActiveSheet()->mergeCells('B4:C4');
                $this->excel->getActiveSheet()->getStyle('B4')->getFont()->setSize(12);
                
                
                //$this->excel->getActiveSheet()->mergeCells('D4:Q4');
                
                header("Content-Type:application/vnd.ms-excel");
                header("Content-Disposition:attachment;filename = Data_indikator__.xls");
                header("Cache-Control:max-age=0");
                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save("php://output");   
               // $xlsData = ob_get_contents();
               // ob_end_clean();
echo'';
                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                   // "content"       =>  $content,
                    "msg"       =>  "",
                    //'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
                );
 
                exit(json_encode($output));
                              
            } catch (Exception $exc) {
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"    =>  $exc->getMessage(),
                );
                exit(json_encode($output));
            }
            
        }
        else{exit("Access Denied");}
    }
  
    function Download_excel1(){
        if(!$this->session->userdata(SESSION_LOGIN)){throw new Exception("Session expired, please login",2);}
        $id = $_GET['wl'];
        $idpro = decrypt_text($_GET['wl']);
        $idind = decrypt_text($_GET['in']);
        
        
        if($idind==1){
                $bulan  = array('00' => '', '01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');    
        } else {
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);    
        }
        //cari nama indikator
        $d_peek="SELECT I.* FROM indikator I where id='$idind'";
                $list_peek = $this->db->query($d_peek);
                foreach ($list_peek->result() as $peek){
                    $nm_indikator   = $peek->nama_indikator;
                    
                }
            //cari Provinsi
        $d_pro="SELECT I.* FROM `wilayah` I WHERE id='$idpro'";
                $list_pro = $this->db->query($d_pro);
                foreach ($list_pro->result() as $pro){
                    $nm_wilayah   = $pro->nama_wilayah;
                    
                }
                
                //cari data 
                $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='".$idind."' AND wilayah='".$idpro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='".$idind."' AND wilayah='".$idpro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                $list_data  = $this->db->query($sql);
                //$content="";
                $excelColumn = range('A', 'ZZ');
            $index_excelColumn = 1;
                $row = $rowstart = 5;
                
                foreach ($list_data->result() as $value) {
                    $periode                = $value->periode;
                    if($periode == '00'){ $thn=$value->tahun; //$thn2=$row->tahun; 
                    }
                    else{ $thn=  $bulan[$value->periode]." - ".$value->tahun; }
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->tahun);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $thn);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->nilai);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->nasional);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->target);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->t_m_rpjmn);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->t_rkpd);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->t_k_rkp);
                    
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->satuan);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->versi);
                    
                    $index_excelColumn=1;$row++;
                }
        
        
                  $this->load->library("Excel");
                $sharedStyleTitles = new PHPExcel_Style();
                
                //garis
                $sharedStyleTitles->applyFromArray(
                        array('borders' => 
                            array(
                                'bottom'=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'top'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'left'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'right'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                            )
                        ));
                
//                        $this->excel->getActiveSheet()->getRowDimension('3')->setRowHeight(50);
        
                $this->excel->getActiveSheet()->setSharedStyle($sharedStyleTitles, 'B4:K10');
                
                $this->excel->getActiveSheet()->setCellValue('B2', "$nm_wilayah");
                $this->excel->getActiveSheet()->setCellValue('B3', "Indikator :");
                $this->excel->getActiveSheet()->setCellValue("C3", "$nm_indikator");
//                $this->excel->getActiveSheet()->setCellValue('C3', '$nm_indikator');
                
                $this->excel->getActiveSheet()->setCellValue("B4", "Tahun ");
                $this->excel->getActiveSheet()->setCellValue("C4", "Periode");
                $this->excel->getActiveSheet()->setCellValue("D4", "Nilai");
                $this->excel->getActiveSheet()->setCellValue("E4", "Nasional");
                $this->excel->getActiveSheet()->setCellValue("F4", "Target");
                $this->excel->getActiveSheet()->setCellValue("G4", "Target Makro RPJMN");
                $this->excel->getActiveSheet()->setCellValue("H4", "Target RKPD");
                $this->excel->getActiveSheet()->setCellValue("I4", "Target Kewilayahan RKP");
                $this->excel->getActiveSheet()->setCellValue("J4", "Satuan");
                $this->excel->getActiveSheet()->setCellValue("K4", "Versi");
//                $this->excel->getActiveSheet()->mergeCells('B4:C4');
                $this->excel->getActiveSheet()->getStyle('B4')->getFont()->setSize(12);
                
                
                //$this->excel->getActiveSheet()->mergeCells('D4:Q4');
                
                header("Content-Type:application/vnd.ms-excel");
                header("Content-Disposition:attachment;filename = Data_indikator_".$nm_indikator."_".$nm_wilayah.".xls");
                header("Cache-Control:max-age=0");
                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save("php://output");   
    }
    
}
