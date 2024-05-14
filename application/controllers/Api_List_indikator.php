<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api_List_indikator extends CI_Controller {
    var $view_dir   = "peppd1/list_data/";
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
                $this->js_path    = "assets/js/peppd1/list_data/list_data_".$this->session->userdata(SESSION_LOGIN)->groupid.".js?v=".now("Asia/Jakarta");
                 //$this->js_path    = "assets/js/ppd3/PPD3_bahan_dukung/dokumen_daerah_t2.js?v=".now("Asia/Jakarta");
              
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
    
    function g_list_data(){
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN)){session_write_close();throw new Exception("Session expired, please login",2);}
                $session = $this->session->userdata(SESSION_LOGIN);
                session_write_close();
//                $usergroupid = $session->group;
                
                $this->form_validation->set_rules('id','ID Data Wilayah','required');
                $this->form_validation->set_rules('indk','ID Indikator','required');
//                if($this->form_validation->run() == FALSE){
//                    throw new Exception(validation_errors("", ""),0);
//                }
                $idwlyh = decrypt_text($this->input->post("id"));
                $idind = decrypt_text($this->input->post("indk"));

                if($idwlyh=='0000'){
                    $wilayah='9999';
                }else{
                    $wilayah=$idwlyh;
                }

                $sql = "SELECT A.*,B.tahun, C.label, D.unit 
                        FROM `a_s_ind_nilai` A 
                        JOIN `t_tahun_var` B ON A.tahun_var = B.tahun_var
                        JOIN `a_s_ind_turtahun` C ON A.val = C.id_s_ind AND C.val =A.turtahun
                        JOIN `a_s_ind` D ON A.val =D.val
                        WHERE A.vervar_var =? AND A.val=?
                        ORDER BY A.tahun_var,A.turtahun";
                $bind = array($wilayah,$idind);
                $list_data = $this->db->query($sql,$bind);
                 if(!$list_data){
                        $msg = $session->userid." ".$this->router->fetch_class()." : ".$this->db->error()["message"];
                        log_message("error", $msg);
                        throw new Exception("Invalid SQL!");
                    }
                $str="";
                if($list_data->num_rows()==0)
                $str = "<tr><td colspan='9'>Data tidak ditemukan</td></tr>";
                $no=1;
                foreach ($list_data->result() as $v) {
                    
                    $str.="<tr>";
                    $str.="<td class='text-right'>".$v->tahun."</td>";
                    $str.="<td class='text-right'>".$v->label."</td>";
                    $str.="<td class='text-right'>".$v->data."</td>";
                    $str.="<td class='text-right'></td>";
                    $str.="<td class='text-right'></td>";
                    $str.="<td class='text-right'></td>";
                    $str.="<td class='text-right'></td>";
                    $str.="<td class='text-right'></td>";
                    $str.="<td class='text-right'>".$v->unit."</td>";
//                    $str.="<td class=''><a href='' target='_blank' title='Klik untuk unduh dokumen' ></a></td>";
//                    $str.="<td class='text-center'><a class='btn btn-xs text-primary' target='_blank' title='Klik untuk unduh dokumen' href='' ><i class='fas fa-2x fa-download'></i></a></td>";
                    $str.="</tr>";
                }
//                $idcomb = decrypt_base64($this->input->post("id"));
//                $tmp = explode('-', $idcomb);
//                if(count($tmp)!=2)
//                    throw new Exception("Invalid ID");
//                $kate_wlyh  = $tmp[0];
//                $idwlyh     = $tmp[1];
//                
//                $_arr = array("PROV","KAB","KOTA");
//                if(!in_array($kate_wlyh, $_arr))
//                    throw new Exception("InvaliD Kategori Wilayah");
//                if(!is_numeric($idwlyh))
//                    throw new Exception("Invalid ID Map");
//                
                
                /* 
                 * +++++++++++++++++++++++++++++++++++++++++++++
                 * PEMBAGIAN QUERY PER KATEGORI WILAYAH - START
                 * +++++++++++++++++++++++++++++++++++++++++++++
                 */
//                if($kate_wlyh=="PROV"){
//                    
//                    /*
//                     * check data PROV - start
//                     */
//                    $sql = "SELECT A.`id`
//                            FROM `provinsi` A
//                            WHERE A.`id`=?";
//                    $bind = array($idwlyh);
//                    $list_data = $this->db->query($sql,$bind);
//                    if(!$list_data){
//                        $msg = $session->userid." ".$this->router->fetch_class()." : ".$this->db->error()["message"];
//                        log_message("error", $msg);
//                        throw new Exception("Invalid SQL!");
//                    }
//                    if($list_data->num_rows() ==0){
//                        $msg = $session->userid." ".$this->router->fetch_class()." : Prov ID : ".$idwlyh." not found";
//                        log_message("error", $msg);
//                        throw new Exception("Data Provinsi tidak ditemukan!");
//                    }
//                    /*
//                     * check data PROV - end
//                     */
//                    
//                    //get LIST tautan doc
//                    $sql = "
//                            SELECT A.`id`,A.`judul`,A.`tautan`, 'daerah' kate
//                            FROM `t_doc_prov` A
//                            JOIN `t_doc_prov_groupuser` B ON B.`docid`=A.`id` AND B.`groupid`=?
//                            JOIN `t_doc_tahap_prov` C ON C.`docid`=A.`id` AND C.`tahap`=2
//                            WHERE A.`isactive`='Y' AND A.provid=?";
//                    $bind = array($usergroupid,$idwlyh);
//                    $list_data = $this->db->query($sql,$bind);
//                    if(!$list_data){
//                        $msg = $session->userid." ".$this->router->fetch_class()." : ".$this->db->error()["message"];
//                        log_message("error", $msg);
//                        throw new Exception("Invalid SQL!");
//                    }
//                }
//                
//                elseif($kate_wlyh=="KAB" || $kate_wlyh=="KOTA"){
//                    /*
//                     * check data KAB / KOTA - start
//                     */
//                    $sql = "SELECT A.`id`
//                            FROM `kabupaten` A
//                            WHERE A.`id`=?";
//                    $bind = array($idwlyh);
//                    $list_data = $this->db->query($sql,$bind);
//                    if(!$list_data){
//                        $msg = $session->userid." ".$this->router->fetch_class()." : ".$this->db->error()["message"];
//                        log_message("error", $msg);
//                        throw new Exception("Invalid SQL!");
//                    }
//                    if($list_data->num_rows() ==0){
//                        $msg = $session->userid." ".$this->router->fetch_class()." : Kab/Kota ID : ".$idwlyh." not found";
//                        log_message("error", $msg);
//                        throw new Exception("Data Kabupaten / Kota tidak ditemukan!");
//                    }
//                    /*
//                     * check data KAB / KOTA - end
//                     */
//                    
//                    //get LIST tautan doc
//                    $sql = "
//                            SELECT A.`id`,A.`judul`,A.`tautan`, 'umum' kate
//                            FROM `t_doc_kab` A
//                            JOIN `t_doc_kab_groupuser` B ON B.`docid`=A.`id` AND B.`groupid`=?
//                            JOIN `t_doc_tahap_kab` C ON C.`docid`=A.`id` AND C.`tahap`=2
//                            WHERE A.`isactive`='Y' AND A.kabid=?";
//                    $bind = array($usergroupid,$idwlyh);
//                    $list_data = $this->db->query($sql,$bind);
//                    if(!$list_data){
//                        $msg = $session->userid." ".$this->router->fetch_class()." : ".$this->db->error()["message"];
//                        log_message("error", $msg);
//                        throw new Exception("Invalid SQL!");
//                    }
//                }
//                
//                
//                
//                $str="";
//                if($list_data->num_rows()==0)
//                    $str = "<tr><td colspan='6'>Data tidak ditemukan</td></tr>";
//                
//                $no=1;
//                foreach ($list_data->result() as $v) {
//                    
//                    $str.="<tr>";
//                    $str.="<td class='text-right'>".$no++."</td>";
//                    $str.="<td class=''><a href='".$v->tautan."' target='_blank' title='Klik untuk unduh dokumen' >".wordwrap($v->judul,50,"<br/>")."</a></td>";
//                    $str.="<td class='text-center'><a class='btn btn-xs text-primary' target='_blank' title='Klik untuk unduh dokumen' href='".$v->tautan."' ><i class='fas fa-2x fa-download'></i></a></td>";
//                    $str.="</tr>";
//                }
                
                
                
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
