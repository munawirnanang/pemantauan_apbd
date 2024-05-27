<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Master_indikator extends CI_Controller {
    var $view_dir   = "admin/masterindikator/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/masterindikator/masterindikator.js";
    
    function __construct() {
        parent::__construct();
        $this->load->model("M_Master","m_ref");
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
                $this->js_path    = "assets/js/admin/masterindikator/masterindikator.js";
                
                $data_page = array( );
                $str = $this->load->view($this->view_dir."module_masterindikator",$data_page,TRUE);

                $output = array(
                    "status"        =>  1,
                    "str"           =>  $str,
                    "js_path"       =>  base_url($this->js_path),
                    "js_initial"    =>  $this->js_init.".init();",
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status"        =>  $exc->getCode(),
                    "msg"           =>  $exc->getMessage(),
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
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
        
                $idx = 0;
                $columns = array( 
                // datatable column index  => database column name
                    $idx++   =>'A.`id`', 
                    $idx++   =>'A.`nama_indikator`',
                    $idx++   =>'G.`group_indikator`',
                    $idx++   =>'A.`chart`',
                    $idx++   => 'A.`satuan`'
                );
                $sql = "SELECT A.*, G.group_indikator 
                    FROM `indikator` A 
                    LEFT JOIN group_indikator G on G.id=A.group_id
                    WHERE 1=1";
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;


                if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                        $sql.=" AND ( "
                                . " A.`id` LIKE '%".$requestData['search']['value']."%' "
                                . " OR A.`nama_indikator` LIKE '%".$requestData['search']['value']."%' "
                                . " OR G.`group_indikator` LIKE '%".$requestData['search']['value']."%' "
                                . " OR A.`chart` LIKE '%".$requestData['search']['value']."%' "
                                . " OR A.`satuan` LIKE '%".$requestData['search']['value']."%' "
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
                    $title  = $row->nama_indikator;
                    $nestedData[] = $i++;
                    $nestedData[] = $row->nama_indikator;
                    $nestedData[] = $row->group_indikator;
                    $nestedData[] = $row->chart;
                   // $nestedData[] = $row->satuan;
                    $nestedData[] = "";

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
    
    function group_indk(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
        
                $idx = 0;
                $columns = array( 
                // datatable column index  => database column name
                        $idx++   =>"A.id", 
                        $idx++   =>"A.group_indikator",
                );
                $sql = "SELECT A.id,A.`group_indikator`
                        FROM group_indikator A
                        WHERE 1=1 ";
                
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {  
                        $sql.=" AND ( "
                                . " A.`id` LIKE '%".$requestData['search']['value']."%' "
                                . " OR A.`group_indikator` LIKE '%".$requestData['search']['value']."%' "
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
                    $nestedData[] = $row->group_indikator;
                    $tmp = " data-id='".encrypt_text($id)."' ";
                    $nestedData[] = ""
                            . "<input type='radio' class='checkbox' name='kode' $tmp value='".$row->group_indikator."'  /> ";
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
                $this->form_validation->set_rules('judul','judul','required|xss_clean');
                $this->form_validation->set_rules('pedate','pedate','required|xss_clean');
                $this->form_validation->set_rules('ket','Keterangan','required|xss_clean');

//                if($this->form_validation->run() == FALSE){
//                    throw new Exception(validation_errors("", ""),0);
//                }
                
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                
                //upload file checking
                $inp_urldoc="";
                if(file_exists($_FILES['attch']['tmp_name']) && is_uploaded_file($_FILES['attch']['tmp_name'])) {
                    //UPLOAD documents
                    $config['upload_path'] = './attachments/';
                    $config['allowed_types'] = "doc|docx|pdf";
                    $config['max_size']	= '30000'; //3 Mb
                    $config['encrypt_name']	= TRUE;
                    $this->load->library('upload');
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload("attch")){
                        throw new Exception($this->upload->display_errors("",""),0);
                    }
                    //uploaded data
                    $upload_file = $this->upload->data();
                    $inp_urldoc = base_url("attachments/").$upload_file['file_name'];
                    
                }
                //print_r($inp_urldoc);exit();
                //date
                $tmp = explode('/', $this->input->post("pedate"));
                if(count($tmp)<3)
                    throw new Exception("Invalid Date");
                $dt = $tmp[0];
                $mt = $tmp[1];
                $yr = $tmp[2];
                
                $podate = $yr."-".$mt."-".$dt;
                $ket = $this->input->post("ket");
//                $supplierid = decrypt_text($this->input->post("supplierid"));
//                if(!is_numeric($supplierid))
//                    throw new Exception("Invalid Value Supplier");
//                
//                //cek suplier
//                $this->m_ref->setTableName("m_supplier");
//                $select = array("id");
//                $cond = array(
//                    "id"  => $supplierid,
//                );
//                $list_data = $this->m_ref->get_by_condition($select,$cond);
//                if($list_data->num_rows() == 0){throw new Exception("Supplier Data not Found",0);}
                
                //cek data 
                $this->m_ref->setTableName("t_pedoman");
                $select = array();
                $cond = array(
                    "judul"  => $this->input->post("judul"),
                );
                $list_data = $this->m_ref->get_by_condition($select,$cond);
                if($list_data->num_rows() > 0){throw new Exception("Data duplication",0);}
                
                $data_baru = array(
                    "judul"         => $this->input->post("judul"),
                    "pe_no "        => $this->input->post("judul"),
                  //  "pe_date"       => $podate,
                    "ket"           => $ket,
                    "attch"         => $inp_urldoc,
//                    "cr_by"         => $this->session->userdata(SESSION_LOGIN)->userid,
//                    "cr_dt"         => $current_date_time,
                );
                $status_save = $this->m_ref->save($data_baru);
                if(!$status_save){
                    throw new Exception($this->db->error()["code"].":Failed save data",0);
                }
                $id = $this->db->insert_id();
                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                    "id"    => encrypt_text($id),
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
                
                $this->m_ref->setTableName("t_pedoman");
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
                
                $this->m_ref->setTableName("m_plant");
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

                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                
                $id = decrypt_text($this->input->post("id"));
                if(!is_numeric($id))
                    throw new Exception("Invalid ID!",0);
                
                //CHECK DATA
                $this->m_ref->setTableName("m_plant");
                $select = array();
                $cond = array(
                    "id"  => $id,
                );
                $list_data = $this->m_ref->get_by_condition($select,$cond);
                if($list_data->num_rows() == 0){throw new Exception("Data not found, reload the page!!",0);}
                
              
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                
                
                $this->db->trans_begin();
                $this->m_ref->setTableName("m_plant");
                $data_baru = array(
                    "code"      => $this->input->post("code"),
                    "name"      => $this->input->post("name"),
                    "up_by"     => $this->session->userdata(SESSION_LOGIN)->userid,
                    "up_dt"     => $current_date_time,
                );
                $cond = array(
                    "id"  => $id,
                );
                $status_save = $this->m_ref->update($cond,$data_baru);
                if(!$status_save){throw new Exception($this->db->error("code")." : Failed save data",0);}
                
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
    
    
}
