<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Data_demografi_G1 extends CI_Controller {
    var $view_dir   = "admin/demografi/";
    var $js_init    = "main";
    var $js_path    = "package/js/admin/demografi/demografi_G1.js";
    
    function __construct() {
        parent::__construct();
        $this->load->model("M_Master","m_ref");
       // $this->load->library("Excel");
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
                $this->js_path    = "package/js/admin/demografi/demografi_G1.js";
                
                $data_page = array(
                );
                $str = $this->load->view($this->view_dir."content_G1",$data_page,TRUE);

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
    
    function get_datatable(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $satkercode = $this->session->userdata(SESSION_LOGIN)->unit_code;
                $idx = 0;
                $columns = array( 
                // datatable column index  => database column name
                    $idx++   =>'AA.`kode`', 
                    $idx++   =>'AA.`nama_pro`',
                    $idx++   =>'AA.`mitra`', 
                    $idx++   =>'AA.`nm_kabko`', 
                    $idx++   =>'AA.`nm_kec`', 
                    $idx++   =>'AA.`keldes`', 
                    $idx++   =>'AA.`rt`', 
                    $idx++   =>'AA.`rw`', 
                    $idx++   =>'TU.`userid`',
                    $idx++   =>'TU.`name`', 
                );
                $sql = "SELECT AA.id kd,AA.kode,AA.nama_pro,AA.mitra,AA.nm_kabko,AA.nm_kec,AA.keldes,AA.rt,AA.rw,TU.userid,TU.name "
                        . "FROM m_provinsi AA "
                        . "Left JOIN tbl_user TU ON TU.unit_code=AA.kode "
                        . "WHERE 1=1 ";
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                        $sql.=" AND ( "
                                . " AA.`nama_pro` LIKE '%".$requestData['search']['value']."%' "
                                . " OR AA.`mitra` LIKE '%".$requestData['search']['value']."%' "
                                . " OR AA.`nm_kabko` LIKE '%".$requestData['search']['value']."%' "
                                . " OR AA.`nm_kec` LIKE '%".$requestData['search']['value']."%' "
                                . " OR AA.`keldes` LIKE '%".$requestData['search']['value']."%' "
                                . " OR TU.`userid` LIKE '%".$requestData['search']['value']."%' "
                                . " OR TU.`name` LIKE '%".$requestData['search']['value']."%' "
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
                    $id      = $row->kd;
                    $title  = $row->kode;

                    $nestedData[] = $row->kode;
                    $nestedData[] = $row->mitra;
                    $nestedData[] = $row->nama_pro;
                    $nestedData[] = $row->nm_kabko;
                    $nestedData[] = $row->nm_kec;
                    $nestedData[] = $row->keldes;
                    $nestedData[] = $row->name;
                    $nestedData[] = "<a data-title='".$title."' data-id='".encrypt_text($id)."' class=\"btn btn-download btn-xs download\" title='Download data'><i class=\"fa fa-download\"></i></a>";
                    $nestedData[] = ""
                            . " "
                            . "<a class='btn btn-xs btn-info edit' data-id='".encrypt_text($id)."' title='Edit Data'><i class='fa fa-pencil'></i></a>"
                            . " <a data-title='".$title."' data-id='".encrypt_text($id)."' class=\"btn btn-danger btn-xs hapus\" title='Hapus data'><i class=\"fa fa-times\"></i></a>";

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
    
    function detail_get(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){
                    throw new Exception("Session berakhir, silahkan login ulang",2);
                }
                $this->form_validation->set_rules('id','ID Data','required');
                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                
                $id = decrypt_text($this->input->post("id"));
                if(!is_numeric($id))
                    throw new Exception("Invalid ID!");
                
                $this->m_ref->setTableName("m_provinsi");
                $select = array();
                $cond = array(
                    "id"  => $id,
                );
                $list_data = $this->m_ref->get_by_condition($select,$cond);
                if($list_data->num_rows() == 0){throw new Exception("Data not found, please reload this page!",0);}

                foreach ($list_data->result() as $v) {
                    if($v->polsek=='Y')
                        $str_pol = "<div class='checker' >
                            <span><input type='checkbox' id='pskk' name='pskk' value='Y' checked disabled></span>
                            </div>  Polsek/Koramil";
                    else 
                        $str_pol = "<div class='checker' >"
                            . "<span><input type='checkbox' id='pskk' name='pskk' value='N' disabled></span>"
                            . "</div>  Polsek/Koramil";                        
                    if($v->kntdesa=='Y')
                        $str_dk = "<div class='checker' ><span>"
                            . "<input type='checkbox' id='kantordesa' name='kantordesa' value='Y' checked disabled></span>"
                            . "</div>  Kantor Desa/Kelurahan ";
                    else 
                        $str_dk = "<div class='checker' ><span>"
                            . "<input type='checkbox' id='kantordesa' name='kantordesa' value='N' disabled></span>"
                            . "</div>  Kantor Desa/Kelurahan ";                        
                    if($v->kntkoperasi=='Y')
                        $str_ksp = "<div class='checker' ><span><input type='checkbox' id='koper' name='koper' value='Y' checked disabled disabled></span></div>  Koperasi/Simpan Pinjam ";
                    else 
                        $str_ksp = "<div class='checker' ><span><input type='checkbox' id='koper' name='koper' value='N' disabled></span></div>  Koperasi/Simpan Pinjam ";                        
                    if($v->p2tp2a=='Y')
                        $str_p2t = "<div class='checker'><span><input type='checkbox' id='ptpa' name='ptpa' value='Y' checked disabled></span></div>  P2TP2A";
                    else 
                        $str_p2t = "<div class='checker' ><span><input type='checkbox' id='ptpa' name='ptpa' value='N' disabled></span></div>  P2TP2A ";                        
                    if($v->psrdesa =='Y')
                        $str_pd = "<div class='checker' ><span><input type='checkbox' id='pasard' name='pasard' value='Y' checked disabled></span></div>  Pasar Desa";
                    else 
                        $str_pd = "<div class='checker' ><span><input type='checkbox' id='pasard' name='pasard' value='N' disabled></span></div>  Pasar Desa ";                        
                    
                    
                    
                    if($v->pkk=='Y')
                        $str_pkk = "<div class='checker' ><span><input type='checkbox' id='pkk' name='pkk' value='Y' checked disabled></span></div>  PKK";
                    else 
                        $str_pkk = "<div class='checker' ><span><input type='checkbox' id='pkk' name='pkk' value='N' disabled></span></div>  pkk";                        
                    if($v->mtlkk=='Y')
                        $str_mtlkk = "<div class='checker' ><span><input type='checkbox' name='mtll' value='Y' checked disabled></span></div>  MAjlis Taklim Laki-laki ";
                    else 
                        $str_mtlkk = "<div class='checker' ><span><input type='checkbox' name='mtll' value='N' disabled></span></div>  Majlis Taklim Laki-laki ";
                    if($v->mtp=='Y')
                        $str_mtp = "<div class='checker'><span><input type='checkbox' name='mtp' value='Y' checked disabled></span></div>  Majlis Taklim Perempuan ";
                    else 
                        $str_mtp = "<div class='checker'><span><input type='checkbox' name='mtp' value='N' disabled></span></div>  Majlis Taklim Perempuan ";
                    if($v->karang=='Y')
                        $str_karang = "<div class='checker' ><span><input type='checkbox' name='kart' value='Y' checked disabled></span></div>  Karang Taruna ";
                    else 
                        $str_karang = "<div class='checker' ><span><input type='checkbox' name='kart' value='N' disabled></span></div>  Karang Taruna ";
                    if($v->tpa=='Y')
                        $str_tpa = "<div class='checker'><span><input type='checkbox' name='tpa' value='Y' checked disabled></span></div>  TPA ";
                    else 
                        $str_tpa = "<div class='checker'><span><input type='checkbox' name='tpa' value='N' disabled></span></div>  TPA ";
                    if($v->rm=='Y')
                        $str_rm = "<div class='checker'><span><input type='checkbox' name='remm' value='Y' checked disabled></span></div>  Remaja Masjid  ";
                    else 
                        $str_rm = "<div class='checker'><span><input type='checkbox' name='remm' value='N' disabled></span></div>  Remaja Masjid  ";
                    if($v->kp=='Y')
                        $str_kp = "<div class='checker'><span><input type='checkbox' name='kelpe' value='Y' checked disabled></span></div>  Kelompok Pemuda  ";
                    else 
                        $str_kp = "<div class='checker'><span><input type='checkbox' name='kelpe' value='N' disabled></span></div>  Kelompok Pemuda  ";
                }
                
                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"       =>  "success get data",
                    "str_pol"    =>  $str_pol,
                    "str_dk"    =>  $str_dk,
                    "str_ksp"    =>  $str_ksp,
                    "str_p2t"    =>  $str_p2t,
                    "str_pd"    =>  $str_pd,
                    
                    
                    "str_pkk"    =>  $str_pkk,
                    "str_mtlkk"    =>  $str_mtlkk,
                    "str_mtp"    =>  $str_mtp,
                    "str_karang"    =>  $str_karang,
                    "str_tpa"   =>$str_tpa,
                    "str_rm"   =>$str_rm,
                    "str_kp"   =>$str_kp,                    
                    "data"      =>  $list_data->result(),
                    "id"      => encrypt_text($id),
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
    
    
    
    function get_datatable_detail(){
        if($this->input->is_ajax_request()){
            try {
                $this->form_validation->set_rules('id','id','required|xss_clean');

                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                
                $id = decrypt_text($this->input->post("id"));
                
                $requestData= $_REQUEST;
        
                $idx = 0;
                $columns = array( 
                // datatable column index  => database column name
                        $idx++   =>"KK.`code`", 
                        $idx++   =>"KK.`name`", 
                        $idx++   =>"MP.`nama_pro`", 
                );
                $sql = "SELECT KK.id IDKK,KK.code,KK.name,MP.id,MP.nama_pro
                        FROM m_kabko KK
                        inner join m_provinsi MP on MP.id=KK.proid
                        WHERE MP.`id`=?";
                $bind = array($id);
                $totalData = $this->db->query($sql,$bind)->num_rows();
                $totalFiltered = $totalData;
                if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                        $sql.=" AND ( "
                                . " KK.`name` LIKE '%".$requestData['search']['value']."%' "
                                . ")";    
                }
                $list_data = $this->db->query($sql,$bind);
                $totalFiltered = $list_data->num_rows();

                $sql.=" ORDER BY "
                        .$columns[$requestData['order'][0]['column']]."   "
                        .$requestData['order'][0]['dir']."  "
                        . "LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
                $list_data = $this->db->query($sql,$bind);
                $data = array();

                $i=1;
                foreach ($list_data->result() as $row) {
                    $nestedData=array(); 
                    $IDKK     = $row->IDKK;
                    $title  = $row->name." - ".$row->name;

                    $nestedData[] = $row->code;
                    $nestedData[] = $row->name;
                    $nestedData[] = "<a class='btn btn-xs btn-info edit' href='#' title='Lihat Data' data-id='".encrypt_text($IDKK)."' ><i class='fa fa-pencil'></i></a>";
//                    $nestedData[] = $row->unitid;
                    $nestedData[] = ""
                            
                            . " <a data-title='".$title."' data-id='".encrypt_text($IDKK)."' class=\"btn btn-danger btn-xs hapus\" title='Hapus data'><i class=\"fa fa-times\"></i></a>";

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
                $json_data = array(
                    "draw"            => intval( 0 ),
                    "recordsTotal"    => intval( 0), 
                    "recordsFiltered" => intval( 0),
                    "data"            => array() ,
                    "msg"            => $exc->getMessage() ,
                    );
                exit(json_encode($json_data));
            }
                }
        else
            die;
    }
    
    function get_datatable_kec(){
        if($this->input->is_ajax_request()){
            try {
                $this->form_validation->set_rules('id','id','required|xss_clean');

                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                
                $id = decrypt_text($this->input->post("id"));
                
                $requestData= $_REQUEST;
        
                $idx = 0;
                $columns = array( 
                // datatable column index  => database column name
                        $idx++   =>"KC.`kode`", 
                        $idx++   =>"KC.`nama`", 
//                        $idx++   =>"MC.`nama_pro`", 
                );
                $sql = "SELECT KC.id,KC.kode,KC.nama,KC.kkid
                        FROM m_kec KC
                        WHERE KC.`kkid`=?";
                $bind = array($id);
                $totalData = $this->db->query($sql,$bind)->num_rows();
                $totalFiltered = $totalData;
                if( !empty($requestData['search']['value']) ) {   
                        $sql.=" AND ( "
                                . " KC.`nama` LIKE '%".$requestData['search']['value']."%' "
                                . ")";    
                }
                $list_data = $this->db->query($sql,$bind);
                $totalFiltered = $list_data->num_rows();
                $sql.=" ORDER BY "
                        .$columns[$requestData['order'][0]['column']]."   "
                        .$requestData['order'][0]['dir']."  "
                        . "LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
                $list_data = $this->db->query($sql,$bind);
                $data = array();
                $i=1;
                foreach ($list_data->result() as $row) {
                    $nestedData=array(); 
                    $id     = $row->kode;
                    $title  = $row->nama." - ".$row->nama;
                    $nestedData[] = $row->kode;
                    $nestedData[] = $row->nama;
                    $nestedData[] = "<a class='btn btn-xs btn-info edit' href='#' title='Lihat Data' data-id='".encrypt_text($id)."' ><i class='fa fa-pencil'></i></a>";
//                    $nestedData[] = $row->unitid;
                    $nestedData[] = ""                            
                            . " <a data-title='".$title."' data-id='".encrypt_text($id)."' class=\"btn btn-danger btn-xs hapus\" title='Hapus data'><i class=\"fa fa-times\"></i></a>";
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
                $json_data = array(
                    "draw"            => intval( 0 ),
                    "recordsTotal"    => intval( 0), 
                    "recordsFiltered" => intval( 0),
                    "data"            => array() ,
                    "msg"            => $exc->getMessage() ,
                    );
                exit(json_encode($json_data));
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
                $this->form_validation->set_rules('code','Code','required|xss_clean');
                $this->form_validation->set_rules('name','Nama Provinsi','required|xss_clean');
                $this->form_validation->set_rules('kabko','Nama kab/Kota','required|xss_clean');
                $this->form_validation->set_rules('kecam','Nama Kecamatan','required|xss_clean');
                $this->form_validation->set_rules('keldes','Nama Kelurahan/Desa','required|xss_clean');
                $this->form_validation->set_rules('mitra','Mitra','required|xss_clean');

                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                
               
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                
                //cek data 
                $this->m_ref->setTableName("m_provinsi");
                $select = array();
                $cond = array(
                    "kode"  => $this->input->post("code"),
                );
                $list_data = $this->m_ref->get_by_condition($select,$cond);
                if($list_data->num_rows() > 0){throw new Exception("Data duplication",0);}
                
                
                $data_baru = array(
                    "kode"          => $this->input->post("code"),
                    "nama_pro"      => $this->input->post("name"),
                    "mitra"         => $this->input->post("mitra"),
                    "nm_kabko"      => $this->input->post("kabko"),
                    "nm_kec"        => $this->input->post("kecam"),
                    "keldes"        => $this->input->post("keldes"),
                    "cr_by"         => $this->session->userdata(SESSION_LOGIN)->userid,
                    "cr_dt"         => $current_date_time,
                );
                $status_save = $this->m_ref->save($data_baru);
                if(!$status_save){
                    throw new Exception($this->db->error()["code"].":Failed save data",0);
                }
                
                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"       =>  "New data has been saved"
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
    
    
     function add_act_kk()
    {
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){
                    throw new Exception("Your session is ended, please relogin",2);
                }
                $this->form_validation->set_rules('id','id','required|xss_clean');
                $this->form_validation->set_rules('code_kk','Code_kk','required|xss_clean');
                $this->form_validation->set_rules('name_kk','Name_kk','required|xss_clean');

                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                
               
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                $id = decrypt_text($this->input->post("id"));
                //cek data 
                $this->m_ref->setTableName("m_kabko");
                $select = array();
                $cond = array(
                    "code"  => $this->input->post("code_kk"),
                );
                $list_data = $this->m_ref->get_by_condition($select,$cond);
                if($list_data->num_rows() > 0){throw new Exception("Data duplication",0);}
                
                
                $data_baru = array(
                    "code"          => $this->input->post("code_kk"),
                    "name"          => $this->input->post("name_kk"),
                    "proid"         => $id,
                    "cr_by"         => $this->session->userdata(SESSION_LOGIN)->userid,
                    "cr_dt"         => $current_date_time,
                );
                $status_save = $this->m_ref->save($data_baru);
                if(!$status_save){
                    throw new Exception($this->db->error()["code"].":Failed save data",0);
                }
                
                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"       =>  "New data has been saved"
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
    /*
     * hapus data
     */
    function delete(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){
                    throw new Exception("Session berakhir, silahkan login ulang",2);
                }
                $this->form_validation->set_rules('id','ID Data','required');
                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                
                $id = decrypt_text($this->input->post("id"));
                if(!is_numeric($id))
                    throw new Exception("Invalid ID!");
                
                $this->m_ref->setTableName("m_provinsi");
                $select = array();
                $cond = array(
                    "id"  => $id,
                );
                $list_data = $this->m_ref->get_by_condition($select,$cond);
                if($list_data->num_rows() == 0){throw new Exception("Data not found, please reload this page!",0);}
                
                $this->db->trans_begin();
                $status = $this->m_ref->delete($cond);
                if(!$status){
                    if($this->db->error()["code"] == 1451)
                        throw new Exception($this->db->error()["code"].":Data sedang digunakan",0);
                    else
                        throw new Exception($this->db->error()["code"].":Failed delete data",0);
                }
                $this->db->trans_commit();
                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"       =>  "Data has been deleted"
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
     * download data
     */
    function Download_excel(){
        if(!$this->session->userdata(SESSION_LOGIN)){throw new Exception("Session expired, please login",2);}
        //$id = $_GET['id'];
        $id = decrypt_text($_GET['id']);
        if(!is_numeric($id))
            throw new Exception("Invalid ID!");
                
            $tgl  = date('Y-m-d');
            $sql = "SELECT AA.*,TU.userid,TU.name "
                        . "FROM m_provinsi AA "
                        . "Left JOIN tbl_user TU ON TU.unit_code=AA.kode "
                        . "WHERE AA.id='$id' ";
            $list_data = $this->db->query($sql);
//            $this->m_ref->setTableName("m_provinsi");
//            $select = array();
//            $cond = array(
//                    "id"  => $id,
//            );
//            $list_data = $this->m_ref->get_by_condition($select,$cond);
            foreach ($list_data->result() as $v):
                    $kode      = $v->kode;
                    $mitra     = $v->mitra;
                    $provinsi  = $v->nama_pro;
                    $kab_kota  = $v->nm_kabko;
                    $kecamatan = $v->nm_kec;
                    $keldes    = $v->keldes;
                    $rt        = $v->rt;
                    $rw        = $v->rw;
                    $jumlah_lk = $v->jml_lk;
                    $jumlah_pr = $v->jml_per;
                    $dws_lk     = $v->dws_lk;
                    $dws_pr     = $v->dws_pr;
                    if($v->polsek=='Y')
                        $polsek="Ada";
                    else 
                        $polsek="Tidak";
                    if($v->kntdesa=='Y')
                        $kntdesa="Ada";
                    else 
                        $kntdesa="Tidak";
                    if($v->kntkoperasi=='Y')
                        $str_ksp = "Ada";
                    else 
                        $str_ksp = "Tidak";
                    if($v->p2tp2a=='Y')
                        $str_p2t = "Ada";
                    else 
                        $str_p2t = "Tidak";
                    if($v->psrdesa =='Y')
                        $str_pd = "Ada";
                    else 
                        $str_pd = "";
                    
                    
                    if($v->pkk=='Y')
                        $str_pkk = "Ada";
                    else 
                        $str_pkk = "Tidak";
                    if($v->mtlkk=='Y')
                        $str_mtlkk = "Ada";
                    else 
                        $str_mtlkk = "Tidak";
                    if($v->mtp=='Y')
                        $str_mtp = "Ada";
                    else 
                        $str_mtp = "Tidak";
                    if($v->karang=='Y')
                        $str_karang = "Ada";
                    else 
                        $str_karang = "Tidak";
                    if($v->tpa=='Y')
                        $str_tpa = "Ada";
                    else 
                        $str_tpa = "Tidak";
                    if($v->rm=='Y')
                        $str_rm = "Ada";
                    else 
                        $str_rm = "Tidak";
                    if($v->kp=='Y')
                        $str_kp = "Ada";
                    else 
                        $str_kp = "Tidak";
                    
                    
                    
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
                $this->excel->getActiveSheet()->setSharedStyle($sharedStyleTitles, 'B4:Q35');
                
                $this->excel->getActiveSheet()->setCellValue('B2', "DATA KEPENDUDUKAN LOKASI DAMPINGAN ");
                $this->excel->getActiveSheet()->setCellValue("B4", "Kode :");
                $this->excel->getActiveSheet()->mergeCells('B4:C4');
                $this->excel->getActiveSheet()->getStyle('B4')->getFont()->setSize(12);
                
                $this->excel->getActiveSheet()->setCellValue("D4", "$kode ");
                $this->excel->getActiveSheet()->setCellValue("B5", "Mitra :");
                $this->excel->getActiveSheet()->mergeCells('B5:C5');
                $this->excel->getActiveSheet()->setCellValue("D5", "$mitra");
                $this->excel->getActiveSheet()->setCellValue("B6", "Provinsi :");
                $this->excel->getActiveSheet()->mergeCells('B6:C6');
                $this->excel->getActiveSheet()->setCellValue("D6", "$provinsi");
                $this->excel->getActiveSheet()->setCellValue("B7", "Kabupaten/Kota :");
                $this->excel->getActiveSheet()->mergeCells('B7:C7');
                $this->excel->getActiveSheet()->setCellValue("D7", "$kab_kota");
                $this->excel->getActiveSheet()->setCellValue("B8", "Kecamatan :");
                $this->excel->getActiveSheet()->mergeCells('B8:C8');
                $this->excel->getActiveSheet()->setCellValue("D8", "$kecamatan");
                $this->excel->getActiveSheet()->setCellValue("B9", "Kelurahan/Desa :");
                $this->excel->getActiveSheet()->mergeCells('B9:C9');
                $this->excel->getActiveSheet()->setCellValue("D9", "$keldes");
                $this->excel->getActiveSheet()->setCellValue("B10", "Petugas :");
                $this->excel->getActiveSheet()->mergeCells('B10:C10');
                $this->excel->getActiveSheet()->setCellValue("D10", "$v->name");
                $this->excel->getActiveSheet()->setCellValue("B11", "RT");
                $this->excel->getActiveSheet()->setCellValue("C11", "$rt");
                $this->excel->getActiveSheet()->setCellValue("D11", "RW");
                $this->excel->getActiveSheet()->setCellValue("E11", "$rw");
                $this->excel->getActiveSheet()->setCellValue("B12", "Jumlah Penduduk *");
                $this->excel->getActiveSheet()->mergeCells('B12:Q12');
                $this->excel->getActiveSheet()->setCellValue("B13", "Anak-anak Laki-Laki (0-19 Tahun) :");
                $this->excel->getActiveSheet()->mergeCells('B13:D13');
                $this->excel->getActiveSheet()->setCellValue("E13", "$jumlah_lk");
                $this->excel->getActiveSheet()->setCellValue("F13", "Anak-anak Perempuan (0-19 Tahun) :");
                $this->excel->getActiveSheet()->mergeCells('F13:H13');
                $this->excel->getActiveSheet()->setCellValue("I13", "$jumlah_pr");
                $this->excel->getActiveSheet()->setCellValue("B14", "Dewasa Laki-Laki (20-64 Tahun) :");
                $this->excel->getActiveSheet()->mergeCells('B14:D14');
                $this->excel->getActiveSheet()->setCellValue("E14", "$dws_lk");
                $this->excel->getActiveSheet()->setCellValue("F14", "Dewasa Perempuan (20-64 Tahun) :");
                $this->excel->getActiveSheet()->mergeCells('F14:H14');
                $this->excel->getActiveSheet()->setCellValue("I14", "$dws_pr");
                $this->excel->getActiveSheet()->setCellValue("B15", "Lansia Laki-Laki (+65 Tahun) :");
                $this->excel->getActiveSheet()->mergeCells('B15:D15');
                $this->excel->getActiveSheet()->setCellValue("E14", "$v->manula_lk");
                $this->excel->getActiveSheet()->setCellValue("F15", "Lansia Perempuan (+65 Tahun) :");
                $this->excel->getActiveSheet()->mergeCells('F15:H15');
                $this->excel->getActiveSheet()->setCellValue("I14", "$v->manula_pr");
                $this->excel->getActiveSheet()->setCellValue("B16", "Pekerjaan Penduduk *");
                $this->excel->getActiveSheet()->mergeCells('B16:Q16');
                $this->excel->getActiveSheet()->setCellValue("B17", "PNS :");
                $this->excel->getActiveSheet()->mergeCells('B17:D17');
                $this->excel->getActiveSheet()->setCellValue("E17", "$v->pns");
                $this->excel->getActiveSheet()->setCellValue("F17", "Petani :");
                $this->excel->getActiveSheet()->setCellValue("G17", "$v->petani");
                $this->excel->getActiveSheet()->setCellValue("H17", "Nelayan :");
                $this->excel->getActiveSheet()->setCellValue("I17", "$v->nelayan");
                $this->excel->getActiveSheet()->setCellValue("B18", "Wiraswasta :");
                $this->excel->getActiveSheet()->mergeCells('B18:D18');
                $this->excel->getActiveSheet()->setCellValue("E18", "$v->wiraswasta");
                $this->excel->getActiveSheet()->setCellValue("F18", "Peternakan :");
                $this->excel->getActiveSheet()->setCellValue("G18", "$v->peternakan");
                $this->excel->getActiveSheet()->setCellValue("H18", "Pedagang :");
                $this->excel->getActiveSheet()->setCellValue("I18", "$v->pedagang");
                $this->excel->getActiveSheet()->setCellValue("B19", "Pekerja Informal :");
                $this->excel->getActiveSheet()->mergeCells('B19:D19');
                $this->excel->getActiveSheet()->setCellValue("E19", "$v->p_informal");
                $this->excel->getActiveSheet()->setCellValue("F19", "Lain-lain :");
                $this->excel->getActiveSheet()->setCellValue("G19", "$v->lain");
                $this->excel->getActiveSheet()->setCellValue("B20", "Jumlah Posyandu/Bidan Desa  :");
                $this->excel->getActiveSheet()->mergeCells('B20:D20');
                $this->excel->getActiveSheet()->setCellValue("E20", "$v->puske");
                $this->excel->getActiveSheet()->setCellValue("B21", "Jumlah Sekolah *");
                $this->excel->getActiveSheet()->mergeCells('B21:Q21');
                $this->excel->getActiveSheet()->setCellValue("B22", "Paud :");
                $this->excel->getActiveSheet()->setCellValue("C22", "$v->paud");
                $this->excel->getActiveSheet()->setCellValue("D22", "TK :");
                $this->excel->getActiveSheet()->setCellValue("E22", "$v->tk");
                $this->excel->getActiveSheet()->setCellValue("F22", "SD/MI :");
                $this->excel->getActiveSheet()->setCellValue("G22", "$v->sdmi");
                $this->excel->getActiveSheet()->setCellValue("H22", "SMP/MTs :");
                $this->excel->getActiveSheet()->setCellValue("I22", "$v->smpmts");
                $this->excel->getActiveSheet()->setCellValue("J22", "SMA/MA :");
                $this->excel->getActiveSheet()->setCellValue("K22", "$v->smama");
                $this->excel->getActiveSheet()->setCellValue("L22", "SMK :");
                $this->excel->getActiveSheet()->setCellValue("M22", "$v->smk");
                $this->excel->getActiveSheet()->setCellValue("N22", "Ponpes :");
                $this->excel->getActiveSheet()->setCellValue("O22", "$v->ponpes");
                $this->excel->getActiveSheet()->setCellValue("P22", "PT/Akademi :");
                $this->excel->getActiveSheet()->setCellValue("Q22", "$v->akademi");
                
                $this->excel->getActiveSheet()->setCellValue("B23", "Jumlah Rumah Ibadah *");
                $this->excel->getActiveSheet()->mergeCells('B23:Q23');
                $this->excel->getActiveSheet()->setCellValue("B24", "Masjid :");
                $this->excel->getActiveSheet()->setCellValue("C24", "$v->masjid");
                $this->excel->getActiveSheet()->setCellValue("D24", "Gereja :");
                $this->excel->getActiveSheet()->setCellValue("E24", "$v->gereja");
                $this->excel->getActiveSheet()->setCellValue("F24", "Pura :");
                $this->excel->getActiveSheet()->setCellValue("G24", "$v->pura");
                $this->excel->getActiveSheet()->setCellValue("H24", "Lainnya :");
                $this->excel->getActiveSheet()->setCellValue("I24", "$v->lainya");
                
                $this->excel->getActiveSheet()->setCellValue("B25", "Ketersedian Layanan *");
                $this->excel->getActiveSheet()->mergeCells('B25:Q25');
                $this->excel->getActiveSheet()->setCellValue("B26", "Polsek/Koramil :");
                $this->excel->getActiveSheet()->mergeCells('B26:D26');
                $this->excel->getActiveSheet()->setCellValue("E26", "$polsek");
                $this->excel->getActiveSheet()->setCellValue("B27", "Kantor Desa/Kelurahan  :");
                $this->excel->getActiveSheet()->mergeCells('B27:D27');
                $this->excel->getActiveSheet()->setCellValue("E27", "$kntdesa");
                $this->excel->getActiveSheet()->setCellValue("B28", "Koperasi/Simpan Pinjam  :");
                $this->excel->getActiveSheet()->mergeCells('B28:D28');
                $this->excel->getActiveSheet()->setCellValue("E28", "$str_ksp");
                $this->excel->getActiveSheet()->setCellValue("B29", "P2TP2A :");
                $this->excel->getActiveSheet()->mergeCells('B29:D29');
                $this->excel->getActiveSheet()->setCellValue("E29", "$str_p2t");
                $this->excel->getActiveSheet()->setCellValue("B30", "Pasar Desa :");
                $this->excel->getActiveSheet()->mergeCells('B30:D30');
                $this->excel->getActiveSheet()->setCellValue("E30", "$str_pd");
                
                $this->excel->getActiveSheet()->setCellValue("B31", "Kelompok Warga *");
                $this->excel->getActiveSheet()->mergeCells('B31:Q31');
                $this->excel->getActiveSheet()->setCellValue("B32", "pkk :");
                $this->excel->getActiveSheet()->setCellValue("C32", "$str_pkk");
                $this->excel->getActiveSheet()->setCellValue("D32", "Majlis Taklim Laki-laki :");
                $this->excel->getActiveSheet()->mergeCells('D32:E32');
                $this->excel->getActiveSheet()->setCellValue("F32", "$str_mtlkk");
                $this->excel->getActiveSheet()->setCellValue("G32", "Majlis Taklim Perempuan :");
                $this->excel->getActiveSheet()->mergeCells('G32:I32');
                $this->excel->getActiveSheet()->setCellValue("J32", "$str_mtp");
                $this->excel->getActiveSheet()->setCellValue("K32", "Karang Taruna :");
                $this->excel->getActiveSheet()->mergeCells('K32:L32');
                $this->excel->getActiveSheet()->setCellValue("M32", "$str_karang");
                $this->excel->getActiveSheet()->setCellValue("B33", "TPA :");
                $this->excel->getActiveSheet()->setCellValue("C33", "$str_tpa");
                $this->excel->getActiveSheet()->setCellValue("D33", "Remaja Masjid  :");
                $this->excel->getActiveSheet()->mergeCells('D33:E33');
                $this->excel->getActiveSheet()->setCellValue("F33", "$str_rm");
                $this->excel->getActiveSheet()->setCellValue("G33", "Kelompok Pemuda  :");
                $this->excel->getActiveSheet()->mergeCells('G33:I33');
                $this->excel->getActiveSheet()->setCellValue("J33", "$str_kp");
                $this->excel->getActiveSheet()->setCellValue("B34", "Ormas :");
                $this->excel->getActiveSheet()->setCellValue("C34", "$v->ormas");
                $this->excel->getActiveSheet()->setCellValue("B35", "Lainnya :");
                $this->excel->getActiveSheet()->setCellValue("C35", "$v->k_lain");
                
                endforeach;
                
                header("Content-Type:application/vnd.ms-excel");
                header("Content-Disposition:attachment;filename = LokasiDampingan_".$id.".xls");
                header("Cache-Control:max-age=0");
                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save("php://output");   
    }
    
    
    
    
    public function detail_act()
    {
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){
                    throw new Exception("Session berakhir, silahkan login ulang",2);
                }
                $this->form_validation->set_rules('id','id','required|xss_clean');
                $this->form_validation->set_rules('code','Code','required|xss_clean');
                $this->form_validation->set_rules('name','Name','required|xss_clean');
                $this->form_validation->set_rules('name_kab','Name Kab/kota','required|xss_clean');
                $this->form_validation->set_rules('name_kec','Name Kec','required|xss_clean');
                $this->form_validation->set_rules('name_kel','Name Kel','required|xss_clean');
                $this->form_validation->set_rules('rt','Rt','required|xss_clean');
                $this->form_validation->set_rules('rw','Rw','required|xss_clean');

                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                $this->form_validation->set_rules('manula_lk','Manula Laki2','required|xss_clean');
                $this->form_validation->set_rules('manula_pr','Manula perempuan','required|xss_clean');
                $this->form_validation->set_rules('manula_lk','Manula Laki2','required|xss_clean');
                $this->form_validation->set_rules('dewasa_lk','Dewasa Laki','required|xss_clean');
                $this->form_validation->set_rules('dewasa_pr','Dewasa Pr','required|xss_clean');
                $this->form_validation->set_rules('anak_lk','Anak Laki','required|xss_clean');
                $this->form_validation->set_rules('anak_pr','Anak Pr','required|xss_clean');
                $this->form_validation->set_rules('pns','PNS','required|xss_clean');
                $this->form_validation->set_rules('petani','Petani','required|xss_clean');
                $this->form_validation->set_rules('nelayan','Nelayan','required|xss_clean');
                $this->form_validation->set_rules('wiraswasta','Wiraswasta','required|xss_clean');
                $this->form_validation->set_rules('lain','Lain','required|xss_clean');
                
                $id = decrypt_text($this->input->post("id"));
                //print_r($this->input->post("code"));exit();
                if(!is_numeric($id))
                    throw new Exception("Invalid ID!",0);
                
                //CHECK DATA
                $this->m_ref->setTableName("m_provinsi");
                $select = array();
                $cond = array(
                    "id"  => $id,
                );
                $list_data = $this->m_ref->get_by_condition($select,$cond);
                if($list_data->num_rows() == 0){throw new Exception("Data not found, reload the page!!",0);}
                              
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                                
                $this->db->trans_begin();
                $this->m_ref->setTableName("m_provinsi");
                $data_terbaru = array(
                    "nama_pro"  => $this->input->post("name"),
                    "nm_kabko"  => $this->input->post("name_kab"),
                    "nm_kec"    => $this->input->post("name_kec"),
                    "keldes"    => $this->input->post("name_kel"),
                    "mitra"     => $this->input->post("mitra"),
                    "ud_by"       => $this->session->userdata(SESSION_LOGIN)->userid,
                    "ud_dt"       => $current_date_time,
                );
                $condisi = array(
                    "id"  => $id,
                );
                
                $status_save_baru = $this->m_ref->update($condisi,$data_terbaru);
                if(!$status_save_baru){throw new Exception($this->db->error("code")." : Failed save data",0);}
                
                $this->db->trans_commit();
                
                //sukses
                $output = array(
                    "status"    =>  1,
                    "msg"       =>  "Data has been updated",
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "msg"    =>  $exc->getMessage(),
                );
                exit(json_encode($output));
            }
        }
        else{exit("Access Denied");}
    }
    
    function detail_kabko(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){
                    throw new Exception("Session berakhir, silahkan login ulang",2);
                }
                $this->form_validation->set_rules('id','ID Data','required');
                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                $id = decrypt_text($this->input->post("id"));
                //print_r($id);exit();
                if(!is_numeric($id))
                    throw new Exception("Invalid ID!");
                
                $this->m_ref->setTableName("m_kabko");
                $select = array();
                $cond = array(
                    "id"  => $id,
                );
                $list_data = $this->m_ref->get_by_condition($select,$cond);
                if($list_data->num_rows() == 0){throw new Exception("Data not found, please reload this page!",0);}                
                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"       =>  "success get data",
                    "data"      =>  $list_data->result(),
                    "id"      => encrypt_text($id),
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
