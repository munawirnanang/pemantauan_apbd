<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_pemantauan extends CI_Controller {
    var $view_dir   = "laporan_pemantauan/";
    var $js_init    = "main";
    var $js_path    = "assets/js/custom/laporan_pemantauan/laporan_pemantauan.js";
    
    function __construct() {
        parent::__construct();
        $this->load->model("M_Master","m_ref");
        //$this->load->library('Pdf');
        //$this->load->library("Excel");
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
                $this->js_path    = "assets/js/custom/laporan_pemantauan/laporan_pemantauan.js";
                
                $data_page = array( );
                $str = $this->load->view($this->view_dir."module",$data_page,TRUE);

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
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
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
                        $idx++   =>"A.nama_provinsi",
                        $idx++   =>"A.label", 
                        $idx++   =>"A.`ppd`", 
                );
                $sql = "SELECT A.id,A.`nama_provinsi`,A.`label`,A.`ppd`
                        FROM provinsi A
                        WHERE A.`ppd`='1' ";
                
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {  
                        $sql.=" AND ( "
                                . " A.`id` LIKE '%".$requestData['search']['value']."%' "
                                . " OR A.`nama_provinsi` LIKE '%".$requestData['search']['value']."%' "
                                . " OR A.`label` LIKE '%".$requestData['search']['value']."%' "
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
                    $nestedData[] = $row->nama_provinsi;
                    $nestedData[] = $row->label;
                    $tmp = " data-id='".encrypt_text($id)."' ";

                    $nestedData[] = ""
//                            . "<a class='btn btn-xs btn-info btnSelect' ".$tmp." title='Pilih Data'><i class='fa fa-hand-o-up'></i> Pilih</a>";
                            . "<input type='checkbox' class='checkbox' $tmp value='".$row->nama_provinsi."'  /> ";
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
                $this->form_validation->set_rules('id','ID','required');
                $prov = $this->input->post("id");
                //$idprov = decrypt_text($prov);
                //cari
                $idx = 0;
                $columns = array( 
                // datatable column index  => database column name
                        $idx++   =>"K.id", 
                        $idx++   =>"K.nama_kabupaten",

                );
                $sql = "SELECT K.`id`, K.`nama_kabupaten`, K.`prov_id`, K.`ppd`, K.`urutan`, P.id, P.nama_provinsi 
                    FROM `kabupaten` K
                    LEFT JOIN provinsi P ON P.id = K.`prov_id`
                    WHERE K.ppd = '1'
                          AND P.`nama_provinsi`='".$prov."' ";
                
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {  
                        $sql.=" AND ( "
                                . " K.`id` LIKE '%".$requestData['search']['value']."%' "
                                . " OR K.`nama_kabupaten` LIKE '%".$requestData['search']['value']."%' "
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
                    $nestedData[] = $row->nama_kabupaten ;
                    $tmp = " data-id='".encrypt_text($id)."' ";
                    $nestedData[] = ""
//                            . "<a class='btn btn-xs btn-info btnSelect' ".$tmp." title='Pilih Data'><i class='fa fa-hand-o-up'></i> Pilih</a>";
                            . "<input type='checkbox' class='checkboxx' name='noso' value='".$row->nama_kabupaten."'  /> ";
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
    
    function pertumbuhan_ekomomi(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $xname="Indonesia";
                    $query="1000";
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;
                    }
                }
                $thn="";
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='".$query."' AND id_indikator = '1' ";
                //print_r($thnmax);exit();
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $chartdata           = "Perkembangan Pertumbuhan Ekonomii";
                $datatitle           = "Pertumbuhan Ekonomi (%)";
                //$datasumber          = "Sumber : Badan Pusat Statistik";
                
                
               // $chart               = $chartdata;
                $nilaiData['name']   =$xname;
                
                $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                
                $list_data  = $this->db->query($sql);
                $categories = array();
                $nilai      = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    $id                     = $row->id;
                    $nestedData             = $row->tahun;
                    $nilaiData1             = (float)$row->nilai;
                    $categories[]           = $nestedData;
                    $nilai[]                = $nilaiData1;
                }
               
                $tahun             = $categories;
                $dataset['data']   = $nilai;
                $nilaiData['name'] = $xname;
                $nilaiData['data'] = $nilai;
                
                $catdata          = $nilaiData + $dataset;
                
                $json_data = array(
                    "text"     => $chartdata,
                    "text2"     => $datatitle,
                    "sumber"    => $datasumber,
                    
                    "categories"     => $tahun, 
                    "series"         => $catdata
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text"           => "",
                    "categories"     => "",
                    "series"         => 0
                );
                exit(json_encode($json_data));
            }
        }
        else
            die;
    }
    
    function pdrb(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $xname="Indonesia";
                    $query="1000";
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        
                    }
                }
                
                
                $chartdata       = "Perkembangan PDRB Per Kapita ADHK";
                $chartdata1       = "PDRB Per Kapita ADHK (Rp)";
                
//                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
//                        FROM `nilai_indikator` N
//                        WHERE N.`wilayah`='1000' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
//               
                $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                $nilaiData['seriesname'] ='Indonesia';
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $nestedData    = $row->tahun ;
                    $nilaiData1    = (float)$row->nilai;
                    $categories[]           = $nestedData;
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun             = $categories;
                $dataset['data']   = $nilai;
                $nilaiData['name'] = $xname;
                $nilaiData['data'] = $nilai;
                
                $catdata          = $nilaiData + $dataset;
                
                $json_data = array(
                    "text"     => $chartdata,
                    "text1"     => $chartdata1,
                    "categories"     => $tahun, 
                    "series"         => $catdata
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text"           => "",
                    "text1"           => "",
                    "categories"     => "",
                    "series"         => 0
                );
                exit(json_encode($json_data));
            }
        }
        else
            die;
    }
    
    function adhk(){
        
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $xname="Indonesia";
                    $query="1000";
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        
                    }
                }
                $thn="";
                $thnmax ="SELECT MAX(tahun) AS thn FROM nilai_indikator WHERE `wilayah`='".$query."' AND id_indikator = '1' ";
               
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                }
                
                $chartdata           = "Perkembangan PDRB per Kapita ADHK Tanun Dasar 2010";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                //$datatitle           = "Pertumbuhan Ekonomi (%)";
                
                $chart               = $chartdata;
                $nilaiData['name']   =$xname;
                
//                $sql = "SELECT N.`id`, N.`id_wil`, N.`tahun`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`flag`
//                        FROM `n_indik` N
//                        WHERE N.`id_wil`='".$query."' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' AND N.flag='Y'";
                
                $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                
                $list_data  = $this->db->query($sql);
                $categories = array();
                $nilai      = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $nestedData             = $row->tahun;
                    $nilaiData1             = (float)$row->nilai;
                    $categories[]           = $nestedData;
                    $nilai[]                = $nilaiData1;
                    
                }
                
                
                $tahun             = $categories;
                $dataset['data']   = $nilai;
                $nilaiData['name'] = $xname;
                $nilaiData['data'] = $nilai;
                
                $catdata          = $nilaiData + $dataset;
                
                $json_data = array(
                    
                    "tahun" => $thn,
                    "text"     => $chartdata,
                    "categories"     => $tahun, 
                    "series"         => $catdata
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text"           => "",
                    "categories"     => "",
                    "series"         => 0
                );
                exit(json_encode($json_data));
            }
        }
        else
            die;
    }
    
    function jumlah_penganggur(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $xname="Indonesia";
                    $query="1000";
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        
                    }
                }
                
                
                $chartdata       = "Perkembangan Jumlah Pengangguran";
                $chartdata1       = "Jumlah Pengangguran (orang)";
                
//                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
//                        FROM `nilai_indikator` N
//                        WHERE N.`wilayah`='1000' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
//               
                $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                $nilaiData['seriesname'] ='Indonesia';
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $nestedData    = $row->id_periode ;
                    $nilaiData1    = (float)$row->nilai;
                    $categories[]           = $nestedData;
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun             = $categories;
                $dataset['data']   = $nilai;
                $nilaiData['name'] = $xname;
                $nilaiData['data'] = $nilai;
                
                $catdata          = $nilaiData + $dataset;
                
                $json_data = array(
                    "text"     => $chartdata,
                    "text1"     => $chartdata1,
                    "categories"     => $tahun, 
                    "series"         => $catdata
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text"           => "",
                    "text1"           => "",
                    "categories"     => "",
                    "series"         => 0
                );
                exit(json_encode($json_data));
            }
        }
        else
            die;
    }
    
    function tingkat_pengangguran(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array();
                
                //$chartdata['caption']="Perkembangan PDRB Per Kapita ADHK Tahun Dasar 2010";
                //$chartdata['subCaption']="Sumber : Badan Pusat Statistik";
                //$chartdata['xAxisName']="Indonesia";
                //$chartdata['yAxisName']="PDRB Per Kapita ADHK Tahun Dasar 2010 (%)";
                
                $chartdata           = "Perkembangan Tingkat Pengangguran Terbuka";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Tingkat Pengangguran Terbuka";
                $chart               = $chartdata;
                $nilaiData['name']   = $xname;
                
                $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $categories[]           = $row->id_periode;
                    
                    
                    $nestedData['label']    = $row->tahun ;
                    $nilaiData1             = (float)$row->nilai;
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun  = $categories;
                
                
                $dataset['data']    = $nilai;
                $catdata          = $nilaiData+$dataset;
                
                $json_data = array(
                    "text"            => $chartdata,
                    "sumber"          => $datasumber,
                    "categories"      => $tahun,
                    "title"           => $datatitle,
                    "dataset"         => $catdata 
                    
                    
                    
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        else
            die;
    }
    
    function pembangunan_manusia(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array();
                
                //$chartdata['caption']="Perkembangan PDRB Per Kapita ADHK Tahun Dasar 2010";
                //$chartdata['subCaption']="Sumber : Badan Pusat Statistik";
                //$chartdata['xAxisName']="Indonesia";
                //$chartdata['yAxisName']="PDRB Per Kapita ADHK Tahun Dasar 2010 (%)";
                
                $chartdata           = "Perkembangan Indek Tingkat Pembangunan Manusia";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Indek Tingkat Pembangunan Manusia";
                $chart               = $chartdata;
                $nilaiData['name']   = $xname;
                
                $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $categories[]           = $row->id_periode;
                    
                    
                    $nestedData['label']    = $row->tahun ;
                    $nilaiData1             = (float)$row->nilai;
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun  = $categories;
                
                
                $dataset['data']    = $nilai;
                $catdata          = $nilaiData+$dataset;
                
                $json_data = array(
                    "text"            => $chartdata,
                    "sumber"          => $datasumber,
                    "categories"      => $tahun,
                    "title"           => $datatitle,
                    "dataset"         => $catdata 
                    
                    
                    
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        else
            die;
    }
    
    function gini_rasio(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array(); 
                
                $chartdata           = "Perkembangan Gini Rasio";
                $datasumber           = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Gini Rasio";
                
                $chart                          = $chartdata;
                $nilaiData['name'] =$xname;
                
               $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai      = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $nestedData             = $row->id_periode;
                    $nilaiData1             = (float)$row->nilai;
                    $categories[]           = $nestedData;
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun             = $categories;
                $dataset['data']   = $nilai;
                $nilaiData['name'] = $xname;
                $nilaiData['data'] = $nilai;
                
                $catdata          = $nilaiData + $dataset;
                
                $json_data = array(
                    "text"           => $chartdata,
                    "sumber"         => $datasumber,
                    "categories"     => $tahun,                                        
                    "title"          => $datatitle,
                    "series"         => $catdata
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text"           => "",
                    "sumber"         => "",
                    "categories"     => "",                                        
                    "title"          => "", 
                    "series"         => 0
                );
                exit(json_encode($json_data));
            }
        }
        else
            die;
    }

    function perkembangan2(){
        if($this->input->is_ajax_request()){
            try {
                //$requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab==''){
                    $query="1000";
                    $xname="Indonesia";                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }                
                }
                
                
                
                $chartdata=array();
                
                $chartdata['caption']="Perkembangan PDRB per Kapita ADHB";
                $chartdata['subCaption']="Sumber : Badan Pusat Statistik";
                $chartdata['xAxisName']="$xname";
                $chartdata['yAxisName']="per Kapita ADHB (Rp)";
                $chartdata['captionFontSize']= "12";
                $chartdata['paletteColors']= "#0075c2";
                $chartdata['baseFont']= "Helvetica Neue,Arial";
                $chartdata['showShadow']= "0";
                $chartdata['divlineColor']= "#999999";
                $chartdata['divLineIsDashed']= "1";
                $chartdata['divlineThickness']= "1";
                $chartdata['divLineDashLen']= "1";
                $chartdata['divLineGapLen']="1";
                $chartdata['canvasBgColor']="#ffffff";                
                
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='$query' AND N.id_indikator = '2' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
               
                $list_data = $this->db->query($sql);
                $data = array();
                $i=1;
                foreach ($list_data->result() as $row) {
                    $nestedData=array(); 
                    $id     = $row->id;
                  
                    $nestedData['label']         = $row->tahun;
                    $nestedData['labelFontSize'] = '12';
                    $nestedData['value']         = $row->nilai ;
                    
                    $data[] = $nestedData;
                }
                $chart=$chartdata;
                $json_data = array(
                    "chart"      => $chart,
                    "data"       => $data    // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
                }
        else
            die;
    }
    
    function perkembangan4(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                
                $chartdata=array();
                
                $chartdata['caption']="Perkembangan Jumlah Pengangguran";
                $chartdata['subCaption']="Sumber : Badan Pusat Statistik";
                $chartdata['xAxisName']="Indonesia";
                $chartdata['yAxisName']="Jumlah Pengangguran (Orang)";
                $chartdata['captionFontSize']= "12";
                $chartdata['paletteColors']= "#0075c2";
                $chartdata['baseFont']= "Helvetica Neue,Arial";
                $chartdata['showShadow']= "0";
                $chartdata['divlineColor']= "#999999";
                $chartdata['divLineIsDashed']= "1";
                $chartdata['divlineThickness']= "1";
                $chartdata['divLineDashLen']= "1";
                $chartdata['divLineGapLen']="1";
                $chartdata['canvasBgColor']="#ffffff";                
                
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='1000' AND N.id_indikator = '4' and N.sumber = 'Badan Pusat Statistik' and N.`periode`='08' AND N.`tahun` >= '2016' 
                         ORDER BY tahun ASC ";
               
                $list_data = $this->db->query($sql);
                $data = array();
                $i=1;
                foreach ($list_data->result() as $row) {
                    $nestedData=array(); 
                    $id     = $row->id;
                  
                    $nestedData['label']         = $row->tahun;
                    $nestedData['labelFontSize'] = '12';
                    $nestedData['value']         = $row->nilai ;
                    
                    $data[] = $nestedData;
                }
                $chart=$chartdata;
                $json_data = array(
                    "chart"      => $chart,
                    "data"       => $data    // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
                }
        else
            die;
    }
    
    function harapan_hidup(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array();
                
                $chartdata           = "Perkembangan Angka Harapan Hidup";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Angka Harapan Hidup (Tahun)";
                
                $chart               = $chartdata;
                $nilaiData['name'] =$xname;
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='".$query."' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
               
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $categories[]           = $row->tahun;
                    
                    
                    $nestedData['label']    = $row->tahun ;
                    $nilaiData1    = $row->nilai;
                    
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun  = $categories;
                
                
                $dataset['data']    = $nilai;
                $catdata[]          = $nilaiData+$dataset;
                
                $json_data = array(
                    "text"            => $chartdata,
                    "sumber"          => $datasumber,
                    "categories"      => $tahun,
                    "title"           => $datatitle,
                    "dataset"         => $catdata 
                    //"chart"          => $chart,
                    
                    
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        else
            die;
    }
    
    function rata_lama_sekolah(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array();
                
                $chartdata           = "Perkembangan Rata-rata Lama Sekolah";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Rata-rata Lama Sekolah (Tahun)";
                
                $chart               = $chartdata;
                $nilaiData['name'] =$xname;
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='".$query."' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
               
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $categories[]           = $row->tahun;
                    
                    
                    $nestedData['label']    = $row->tahun ;
                    $nilaiData1    = $row->nilai;
                    
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun  = $categories;
                
                
                $dataset['data']    = $nilai;
                $catdata[]          = $nilaiData+$dataset;
                
                $json_data = array(
                    "text"            => $chartdata,
                    "sumber"          => $datasumber,
                    "categories"      => $tahun,
                    "title"           => $datatitle,
                    "dataset"         => $catdata 
                    //"chart"          => $chart,
                    
                    
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        else
            die;
    }
    
    function harapan_lama_sekolah(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array();
                
                $chartdata           = "Perkembangan Harapan Lama Sekolah";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Harapan Lama Sekolah (Tahun)";
                
                $chart               = $chartdata;
                $nilaiData['name'] =$xname;
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='".$query."' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
               
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $categories[]           = $row->tahun;
                    
                    
                    $nestedData['label']    = $row->tahun ;
                    $nilaiData1    = $row->nilai;
                    
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun  = $categories;
                
                
                $dataset['data']    = $nilai;
                $catdata[]          = $nilaiData+$dataset;
                
                $json_data = array(
                    "text"            => $chartdata,
                    "sumber"          => $datasumber,
                    "categories"      => $tahun,
                    "title"           => $datatitle,
                    "dataset"         => $catdata 
                    //"chart"          => $chart,
                    
                    
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        else
            die;
    }
    
    function pengeluaran_perkapita(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array();
                
                $chartdata           = "Perkembangan Pengeluaran per Kapita";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Pengeluaran per Kapita";
                
                $chart               = $chartdata;
                $nilaiData['name'] =$xname;
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='".$query."' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
               
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $categories[]           = $row->tahun;
                    
                    
                    $nestedData['label']    = $row->tahun ;
                    $nilaiData1    = $row->nilai;
                    
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun  = $categories;
                
                
                $dataset['data']    = $nilai;
                $catdata[]          = $nilaiData+$dataset;
                
                $json_data = array(
                    "text"            => $chartdata,
                    "sumber"          => $datasumber,
                    "categories"      => $tahun,
                    "title"           => $datatitle,
                    "dataset"         => $catdata 
                    //"chart"          => $chart,
                    
                    
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        else
            die;
    }
    
    function inflasi(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array();
                
                $chartdata           = "Perkembangan Inflasi";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Inflasi";
                
                $chart               = $chartdata;
                $nilaiData['name'] =$xname;
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='".$query."' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
               
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $categories[]           = $row->tahun;
                    
                    
                    $nestedData['label']    = $row->tahun ;
                    $nilaiData1    = $row->nilai;
                    
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun  = $categories;
                
                
                $dataset['data']    = $nilai;
                $catdata[]          = $nilaiData+$dataset;
                
                $json_data = array(
                    "text"            => $chartdata,
                    "sumber"          => $datasumber,
                    "categories"      => $tahun,
                    "title"           => $datatitle,
                    "dataset"         => $catdata 
                    //"chart"          => $chart,
                    
                    
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        else
            die;
    }
    
    function tinkat_kemiskinan(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array();
                
                $chartdata           = "Perkembangan Tingkat Kemiskinan";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Tingkat Kemiskinan";
                
                $chart               = $chartdata;
                $nilaiData['name'] =$xname;
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='".$query."' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
               
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $categories[]           = $row->tahun;
                    
                    
                    $nestedData['label']    = $row->tahun ;
                    $nilaiData1    = $row->nilai;
                    
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun  = $categories;
                
                
                $dataset['data']    = $nilai;
                $catdata[]          = $nilaiData+$dataset;
                
                $json_data = array(
                    "text"            => $chartdata,
                    "sumber"          => $datasumber,
                    "categories"      => $tahun,
                    "title"           => $datatitle,
                    "dataset"         => $catdata 
                    //"chart"          => $chart,
                    
                    
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        else
            die;
    }
    
    function garis_kemiskinan(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array();
                
                $chartdata           = "Perkembangan Garis Kemiskinan";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Garis Kemiskinan (Rp)";
                
                $chart               = $chartdata;
                $nilaiData['name'] =$xname;
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='".$query."' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
               
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $categories[]           = $row->tahun;
                    
                    
                    $nestedData['label']    = $row->tahun ;
                    $nilaiData1    = $row->nilai;
                    
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun  = $categories;
                
                
                $dataset['data']    = $nilai;
                $catdata[]          = $nilaiData+$dataset;
                
                $json_data = array(
                    "text"            => $chartdata,
                    "sumber"          => $datasumber,
                    "categories"      => $tahun,
                    "title"           => $datatitle,
                    "dataset"         => $catdata 
                    //"chart"          => $chart,
                    
                    
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        else
            die;
    }
    
    function keparahan_kemiskinan(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array();
                
                $chartdata           = "Perkembangan Indeks Keparahan Kemiskinan";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Indeks Keparahan Kemiskinan";
                
                $chart               = $chartdata;
                $nilaiData['name'] =$xname;
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='".$query."' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
               
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $categories[]           = $row->tahun;
                    
                    
                    $nestedData['label']    = $row->tahun ;
                    $nilaiData1    = $row->nilai;
                    
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun  = $categories;
                
                
                $dataset['data']    = $nilai;
                $catdata[]          = $nilaiData+$dataset;
                
                $json_data = array(
                    "text"            => $chartdata,
                    "sumber"          => $datasumber,
                    "categories"      => $tahun,
                    "title"           => $datatitle,
                    "dataset"         => $catdata 
                    //"chart"          => $chart,
                    
                    
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        else
            die;
    }
    
    function kedalaman_kemiskinan(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $query="1000";
                    $xname="Indonesia";
                
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $query = $Lis_pro->id;
                        $xname = $Lis_pro->nama_provinsi;
                    }
                
                }
                
                $chartdata=array();
                
                $chartdata           = "Perkembangan Indeks Kedalaman Kemiskinan";
                $datasumber          = "Sumber : Badan Pusat Statistik";
                $datatitle           = "Indeks Kedalaman Kemiskinan";
                
                $chart               = $chartdata;
                $nilaiData['name'] =$xname;
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='".$query."' AND N.id_indikator = '1' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2013' ";
               
                $list_data = $this->db->query($sql);
                $categories = array();
                $nilai    = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $categories[]           = $row->tahun;
                    
                    
                    $nestedData['label']    = $row->tahun ;
                    $nilaiData1    = $row->nilai;
                    
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun  = $categories;
                
                
                $dataset['data']    = $nilai;
                $catdata[]          = $nilaiData+$dataset;
                
                $json_data = array(
                    "text"            => $chartdata,
                    "sumber"          => $datasumber,
                    "categories"      => $tahun,
                    "title"           => $datatitle,
                    "dataset"         => $catdata 
                    //"chart"          => $chart,
                    
                    
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        else
            die;
    }
    
    function penduduk_miskin(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                
                $xname="";
                $query="";
                if($pro == '' & $kab ==''){
                    $xname="Indonesia";
                    $query="";
                }
                else {
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        
                    }
                }
                
                $sql = "SELECT N.`id`, N.`wilayah`, N.`tahun`, N.`periode`, N.`id_periode`, N.`nilai`, N.`nasional`, N.`target`, N.`versi`, N.`satuan`, N.`sumber`, N.`id_indikator`, N.`nama_indikator` 
                        FROM `nilai_indikator` N
                        WHERE N.`wilayah`='1000' AND N.id_indikator = '40' and N.sumber = 'Badan Pusat Statistik' and N.`tahun` >= '2016' ORDER BY tahun ";
               
                $list_data  = $this->db->query($sql);
                $categories = array();
                $nilai      = array();
                
                $i=1;
               // $seriesname="";
                foreach ($list_data->result() as $row) {
                    $nestedData=array();
                    $nilaiData1=array();
                    
                    $id                     = $row->id;
                    $nestedData             = $row->tahun;
                    $nilaiData1             = (int)$row->nilai;
                    $categories[]           = $nestedData;
                    $nilai[]                = $nilaiData1;
                    
                }
                $tahun             = $categories;
                $dataset['data']   = $nilai;
                $nilaiData['name'] = $xname;
                $nilaiData['data'] = $nilai;
                
                $catdata          = $nilaiData + $dataset;
                
                $json_data = array(
                    "categories"     => $tahun,                                        
                    //"series"         => [$nilaiData] 
                    "series"         => $catdata
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "categories"     => "",                                        
                    //"series"         => [$nilaiData] 
                    "series"         => 0
                );
                exit(json_encode($json_data));
            }
        }
        else
            die;
    }

    
}
