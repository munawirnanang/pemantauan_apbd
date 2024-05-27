<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pencapaian_indikator extends CI_Controller {
    var $view_dir   = "peppd1/pencapaian_indikator/";
    var $view_dir_demo   = "demo/pencapaian_indikator/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/pencapaian_indikator/pencapaian_indikator.js";
    
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
                $this->js_path    = "assets/js/admin/pencapaian_indikator/pencapaian_indikator_".$this->session->userdata(SESSION_LOGIN)->groupid.".js?v=".now("Asia/Jakarta");
                
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
                        $idx++   =>"A.nama_provinsi",
                        $idx++   =>"A.label", 
                        $idx++   =>"A.`ppd`", 
                );
                $sql = "SELECT A.id,A.`nama_provinsi`,A.`label`,A.`ppd`
                        FROM provinsi A
                        WHERE 1=1 ";
                
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
                    $nama = " data-nama='".$row->nama_provinsi."' ";
                    $nestedData[] = ""
//                            . "<a class='btn btn-xs btn-info btnSelect' ".$tmp." title='Pilih Data'><i class='fa fa-hand-o-up'></i> Pilih</a>";
//                            . "<input type='checkbox' class='checkbox' $tmp  value='".$row->nama_provinsi."'  /> ";
                    . "<input type='radio' class='checkbox' name='group' $tmp  value='".$row->nama_provinsi."'  /> ";
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
                $sql = "SELECT K.`id` 'idkab', K.`nama_kabupaten`, K.`prov_id`, K.`ppd`, K.`urutan`, P.id, P.nama_provinsi 
                    FROM `kabupaten` K
                    LEFT JOIN provinsi P ON P.id = K.`prov_id`
                    WHERE 1=1";
                
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
                    
                    $nestedData[] = $row->idkab;
                    $nestedData[] = $row->nama_kabupaten ;
                    $tmp = " data-id='".encrypt_text($id)."' ";
                    $nestedData[] = ""
//                            . "<input type='radio' class='checkboxx' name='noso' value='".$row->nama_kabupaten."' data-id='$row->nama_kabupaten' data-pv='$row->nama_provinsi' /> ";
                                . "<input type='radio' class='radio' name='group' value='".$row->nama_kabupaten."' data-id='$row->nama_kabupaten' data-pv='$row->nama_provinsi' /> ";
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
    //Pertumbuhan Ekonomi
    function pertumbuhan_ekomomi(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
               
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
//                $provinsi = explode("|", $this->input->post('provinsi'));
//                $string_provinsi = " P.`nama_provinsi` IN(";
//                $count_pay = count($provinsi);
//                $i=1;
//                foreach ($provinsi as $provinsi_v):
//                    
//                    $i++;
//                endforeach;
//                $string_provinsi.=") ";  
                
                $xname      ="";
                $query      ="";
                //$title      = "Perkembangan Pertumbuhan Ekonomi";
                $title_y    = "Pertumbuhan Ekonomi (%)";
                $content1='<table class="table mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Tahun</th>
                                                                <th>Indonesia</th>
                                                                <th>'.$pro.'</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>';
                $select_title="SELECT * FROM indikator where id='1'";
                $list_title = $this->db->query($select_title);
                foreach($list_title->result() as $lst){
                    $title= $lst->nama_indikator;
                }
                if($pro == '' & $kab ==''){
                    //$judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]." ".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if($periode == '00'){ $thn=$row->tahun; //$thn2=$row->tahun; 
                        }
                        else{ $thn=  $prde[$row->periode]." - ".$row->tahun;
                              //$thn2=  $prde2[$row->periode]." - ".$row->tahun;      
                        }
                        $categories1[]   = $thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]         = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        //$judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;
                        
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]." ".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if($periode == '00'){ $thn=$row->tahun; //$thn2=$row->tahun; 
                        }
                        else{ $thn=  $prde[$row->periode]." - ".$row->tahun;
                              //$thn2=  $prde2[$row->periode]." - ".$row->tahun;      
                        }
                        $categories1[]   = $thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    //    $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;
                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                        //$judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;                        
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]." ".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if($periode == '00'){ $thn=$row->tahun; //$thn2=$row->tahun; 
                        }
                        else{ $thn=  $prde[$row->periode]." - ".$row->tahun;
                              //$thn2=  $prde2[$row->periode]." - ".$row->tahun;      
                        }
                        $categories1[]   = $thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                        
                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='1' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='1' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='1' AND wilayah='".$kpquery."')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='1' AND wilayah='".$kpquery."' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode"; 
                        $list_dkab  = $this->db->query($sql_dkab);
                        foreach ($list_dkab->result() as $row_dkab) {
                         //$id_kab                     = $row_dkab->id;
                         $categories_kab[]             = $row_dkab->tahun;
                         if($row_dkab->nilai_kab==0){
                             $nilai_kab1='';
                         }else{
                             $nilai_kab1                = (float)$row_dkab->nilai_kab;   
                         }
                         $nilai_kab[]                = $nilai_kab1;   
                        }
                        //$datay_ppe3 = array_reverse($nilai_kab);
                        //$tahun_kab         = $categories_kab;
                        $nilaiData['name'] = $kab;
                        $nilaiData['data'] = array_reverse($nilai_kab);
                        array_push($catdata, $nilaiData);
                        
                        
                        
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '1' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }                
                $json_data = array(
                    "text"       => $title,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata,
                    "content1"   => $content1
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
                $select_title="SELECT * FROM indikator where id='2'";
                $list_title = $this->db->query($select_title);
                foreach($list_title->result() as $lst){
                    $title= $lst->nama_indikator;
                }
                if($pro == '' & $kab ==''){
                    $xname="Indonesia";
                    $query="1000";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data = $this->db->query($sql);
                    $nilai    = array();
                    $nilaiData['seriesname'] ='Indonesia';
                    $i=1;
                   // $seriesname="";
                    foreach ($list_data->result() as $row) {
                        $id            = $row->id;
                        $categories[]  = $row->tahun ;
                        $nilai[]       = (float)$row->nilai;
                        //$sumber        = $row->sumber;

                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai;
                    $catdata[]          = $nilaiData;
                    
                    
                    
                }
                else if($pro != '' & $kab =='') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data = $this->db->query($sql);
//                    $nilai    = array();
//                    $nilaiData['seriesname'] ='Indonesia';
                    $i=1;
                   // $seriesname="";
                    foreach ($list_data->result() as $row) {
                        $id            = $row->id;
                        $categories[]  = $row->tahun ;
                        $nilai[]       = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);
                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $row_p->tahun ;
                        $nilai_p[]       = (float)$row_p->nilai;
                        $sumber_p        = $row_p->sumber;

                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);
                
                } 
                else if($pro != '' & $kab !='') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id            = $row->id;
                        $categories[]  = $row->tahun ;
                        $nilai[]       = (float)$row->nilai;
                        //$sumber        = $row->sumber;
                    }
                    //$tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);
                    
                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $row_p->tahun ;
                        $nilai_p[]       = (float)$row_p->nilai;
                        $sumber_p        = $row_p->sumber;
                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dkab  = $this->db->query($sql_dkab);
                        foreach ($list_dkab->result() as $row_dkab) {
                         $id_kab                     = $row_dkab->id;
                         $categories_kab[]             = $row_dkab->tahun;
                         $nilai_kab[]                = (float)$row_dkab->nilai;   
                        }
                        $tahun_kab             = $categories_kab;
                        $nilaiData['name'] = $kab;
                        $nilaiData['data'] = $nilai_kab;
                        array_push($catdata, $nilaiData);
                        
                }
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '2' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                //$title       = "Perkembangan PDRB Per Kapita ADHK";
                $title_y     = "PDRB Per Kapita ADHK (Rp)";
                               
                
                
                $json_data = array(
                    "text"           => $title,
                    "text1"          => $title_y,
                    "categories"     => $tahun, 
                    "series"         => $catdata,
                     "sumber"        => $datasumber
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text"           => "",
                    "text1"          => "",
                    "categories"     => "",
                    "series"         => 0,
                     "sumber"        => ""
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
               
                $xname      ="";
                $query      ="";
                //$title      = "Perkembangan PDRB per Kapita ADHK Tanun Dasar 2010";
                $title_y    = "PDRB per Kapita ADHK Tanun Dasar 2010";
                $select_title="SELECT * FROM indikator where id='3'";
                $list_title = $this->db->query($select_title);
                foreach($list_title->result() as $lst){
                    $title= $lst->nama_indikator;
                }
                if($pro == '' & $kab ==''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;
                        
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                        
                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='3' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='3' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='3' AND wilayah='".$kpquery."')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='3' AND wilayah='".$kpquery."' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode"; 
                        $list_dkab  = $this->db->query($sql_dkab);
                        foreach ($list_dkab->result() as $row_dkab) {
                         //$id_kab                     = $row_dkab->id;
                         $categories_kab[]             = $row_dkab->tahun;
                         if($row_dkab->nilai_kab==0){
                             $nilai_kab1='';
                         }else{
                             $nilai_kab1                = (float)$row_dkab->nilai_kab;   
                         }
                         $nilai_kab[]                = $nilai_kab1;   
                        }
                        //$datay_ppe3 = array_reverse($nilai_kab);
                        //$tahun_kab         = $categories_kab;
                        $nilaiData['name'] = $kab;
                        $nilaiData['data'] = array_reverse($nilai_kab);
                        array_push($catdata, $nilaiData);
                }
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '3' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "text"       => $title,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata
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
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $xname="";
                $query="";
                $select_title="SELECT * FROM indikator where id='4'";
                $list_title = $this->db->query($select_title);
                foreach($list_title->result() as $lst){
                    $title= $lst->nama_indikator;
                }
                if($pro == '' & $kab ==''){
                    $xname="Indonesia";
                    $query="1000";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data = $this->db->query($sql);
                    $nilai    = array();
                    $nilaiData['seriesname'] ='Indonesia';
                    $i=1;
                   // $seriesname="";
                    foreach ($list_data->result() as $row) {
                        $id            = $row->id;
                        $categories[]= $bulan[$row->periode]."-".$row->tahun;
                        $nilai[]       = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai;
                    $catdata[]          = $nilaiData;
                                                            
                }
                else if($pro != '' & $kab =='') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        
                    }
                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $bulan[$row_p->periode]."-".$row_p->tahun;
                        $nilai_p[]       = (float)$row_p->nilai;
                        $sumber_p        = $row_p->sumber;

                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    array_push($catdata, $nilaiData);
                
                } 
                else if($pro != '' & $kab !='') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    
                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $bulan[$row_p->periode]."-".$row_p->tahun;
                        $nilai_p[]       = (float)$row_p->nilai;
                        //$sumber_p        = $row_p->sumber;
                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    array_push($catdata, $nilaiData);
                    
//                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                        $list_dkab  = $this->db->query($sql_dkab);
//                        foreach ($list_dkab->result() as $row_dkab) {
//                         $id_kab                     = $row_dkab->id;
//                         $categories_kab[]             = $bulan[$row_dkab->periode]."-".$row_dkab->tahun;
//                         $nilai_kab[]                = (float)$row_dkab->nilai;   
//                        }
//                        $tahun_kab         = $categories_kab;
//                        $nilaiData['name'] = $kab;
//                        $nilaiData['data'] = $nilai_kab;
//                        array_push($catdata, $nilaiData);
                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='4' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='4' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='4' AND wilayah='".$kpquery."')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='4' AND wilayah='".$kpquery."' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        $nilai_kab[]                = (float)$row_dkab->nilai_kab;   
                    }
                        $nilaiData['name'] = $kab;
                        $nilaiData['data'] = array_reverse($nilai_kab);
                        array_push($catdata, $nilaiData);
                        
                }
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '4' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn            =$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                //$title       = "Perkembangan Jumlah Pengangguran";
                $title_y     = "Jumlah Pengangguran (orang)";
                
                $json_data = array(
                    "text"           => $title,
                    "text1"          => $title_y,
                    "categories"     => $tahun, 
                    "series"         => $catdata,
                     "sumber"        => $datasumber
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text"           => "",
                    "text1"          => "",
                    "categories"     => "",
                    "series"         => 0,
                     "sumber"        => ""
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
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                
                //$title           = "Perkembangan Tingkat Pengangguran Terbuka";
                $title_y           = "Tingkat Pengangguran Terbuka";
                $select_title="SELECT * FROM indikator where id='6'";
                $list_title = $this->db->query($select_title);
                foreach($list_title->result() as $lst){
                    $title= $lst->nama_indikator;
                }
                if($pro == '' & $kab ==''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
                        $nli                     = number_format($row->nilai,2);
                        $nilai[]                = (float)$nli;
                        //number_format($angka,2);
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;
                        
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
                        $nli                     = number_format($row->nilai,2);
                        $nilai[]                = (float)$nli;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $bulan[$row_dpro->periode]."-".$row_dpro->tahun;
                         $nli_pro                     = number_format($row_dpro->nilai,2);
                         $nilai_pro[]                = (float)$nli_pro;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
                        $nli                     = number_format($row->nilai,2);
                        $nilai[]                = (float)$nli;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $bulan[$row_dpro->periode]."-".$row_dpro->tahun;
                         $nli_pro                     = number_format($row_dpro->nilai,2);
                         $nilai_pro[]                = (float)$nli_pro;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                        
                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='6' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='6' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='6' AND wilayah='".$kpquery."')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='6' AND wilayah='".$kpquery."' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode 
                                 order by id_periode"; 
                    
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n=0;
                    foreach ($list_dkab->result() as $row_dkab) {
                         $m=$n++;
                         if($row_dkab->nilai_kab!=0){
                             $nli_dkab                   = [$m,(float)number_format($row_dkab->nilai_kab,2)];  
                             $nilai_kab[]                = $nli_dkab;
                         }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData[]       = $categories;
                    //$nilaiData['data'] = array_reverse($nilai_kab);
                    $nilaiData['data'] = $nilai_kab;
                    //print_r($nilaiData);exit();
                    array_push($catdata, $nilaiData);
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '6' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "text"       => $title,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata
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
        
    function pembangunan_manusia(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
               
                $xname      ="";
                $query      ="";
               // $title_x             = "Perkembangan Indek Tingkat Pembangunan Manusia";
                $title_y             = "Indek Tingkat Pembangunan Manusia";
                $select_title="SELECT * FROM indikator where id='5'";
                $list_title = $this->db->query($select_title);
                foreach($list_title->result() as $lst){
                    $title_x= $lst->nama_indikator;
                }
                if($pro == '' & $kab ==''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;  
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                        
                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                         $id_kab                     = $row_dkab->id;
                         $categories_kab[]             = $row_dkab->tahun;
                         $nilai_kab[]                = (float)$row_dkab->nilai;   
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '5' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata
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
    
    //Gini Rasio
    function gini_rasio(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $title_x           = "Gini Rasio";
                $title_y           = "Gini Rasio";
                
                if($pro == '' & $kab ==''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bulan[$row->periode]."-".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;  
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $bulan[$row_dpro->periode]."-".$row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id_periode_huruf       = str_split($row->id_periode, 4);
                        $bulann = $id_periode_huruf[1];
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $bulan[$row_dpro->periode]."-".$row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='7' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='7' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='7' AND wilayah='".$kpquery."')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='7' AND wilayah='".$kpquery."' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    //print_r($sql_dkab);exit();
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n=0;
                    foreach ($list_dkab->result() as $row_dkab) {
//                        if($row_dkab->nilai!=NULL){
//                            $ngr_k=$ngr_k=(float)$row_dkab->nilai;
//                        }else{
//                            $ngr_k=$row_dkab->nilai;
//                        }
                        //$nilai_kab[]                = $ngr_k;
                        $m=$n++;
                         if($row_dkab->nilai_kab!=0){
                             $n_dkab                   = [$m,(float)$row_dkab->nilai_kab];  
                             $nilai_kab[]                = $n_dkab;
                         }
                        
                        
                    }
                    $nilaiData['name'] = $kab;
                    //$nilaiData['data'] = array_reverse($nilai_kab);
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '7' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata
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

    //Harapan Hidup
    function harapan_hidup(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $title_x           = "Angka Harapan Hidup";
                $title_y           = "Angka Harapan Hidup (Tahun)";
                
                if($pro == '' & $kab ==''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;  
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                        
                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                         $id_kab                     = $row_dkab->id;
                         $categories_kab[]             = $row_dkab->tahun;
                         $nilai_kab[]                = (float)$row_dkab->nilai;   
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '8' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata
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
    
    //Rata rata Lama Sekolah
    function rata_lama_sekolah(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $title_x           = "Rata-rata Lama Sekolah";
                $title_y           = "Rata-rata Lama Sekolah (Tahun)";
                
                if($pro == '' & $kab ==''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;  
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                        
                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                         $id_kab                     = $row_dkab->id;
                         $categories_kab[]             = $row_dkab->tahun;
                         $nilai_kab[]                = (float)$row_dkab->nilai;   
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '9' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata
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
    
    //harapan Lama Sekolah
    function harapan_lama_sekolah(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $title_x           = "Harapan Lama Sekolah";
                $title_y           = "Harapan Lama Sekolah (Tahun)";
                
                if($pro == '' & $kab ==''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;  
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                        
                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                         $id_kab                     = $row_dkab->id;
                         $categories_kab[]             = $row_dkab->tahun;
                         $nilai_kab[]                = (float)$row_dkab->nilai;   
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '10' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata
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
    
    //pengeluaran_perkapita
    function pengeluaran_perkapita(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $title_x           = "Pengeluaran per Kapita";
                $title_y           = "Pengeluaran per Kapita (Rp)";
                
                if($pro == '' & $kab ==''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;  
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                        
                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                         $id_kab                     = $row_dkab->id;
                         $categories_kab[]             = $row_dkab->tahun;
                         $nilai_kab[]                = (float)$row_dkab->nilai;   
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '11' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata
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
    
    //Tingkat Kemiskinan
    function tinkat_kemiskinan(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $title_x           = " Tingkat Kemiskinan";
                $title_y           = "Tingkat Kemiskinan";
                
                if($pro == '' & $kab ==''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;  
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $bulan[$row_dpro->periode]."-".$row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $bulan[$row_dpro->periode]."-".$row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                        
                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                         $id_kab                     = $row_dkab->id;
                         $categories_kab[]             = $bulan[$row_dkab->periode]."-".$row_dkab->tahun;
                         $nilai_kab[]                = (float)$row_dkab->nilai;   
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '36' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata
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
    
    //Indeks Kedalaman Kemiskinan
    function kedalaman_kemiskinan(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                //$title_x           = "Perkembangan Indeks Kedalaman Kemiskinan";
                $title_y           = "Indeks Kedalaman Kemiskinan";
                $select_title="SELECT * FROM indikator where id='39'";
                $list_title = $this->db->query($select_title);
                foreach($list_title->result() as $lst){
                    $title_x= $lst->nama_indikator;
                }
                if($pro == '' & $kab ==''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;  
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             =$bulan[$row->periode]."-".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                        
                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                         $id_kab                     = $row_dkab->id;
                         $categories_kab[]             = $row_dkab->tahun;
                         $nilai_kab[]                = (float)$row_dkab->nilai;   
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '39' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata
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
    
    //Jumlah Penduduk Miskin
    function penduduk_miskin(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $title_x           = "Jumlah Penduduk Miskin";
                $title_y           = "Jumlah Penduduk Miskin (Orang)";
                
                if($pro == '' & $kab ==''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' & $kab ==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;  
                    }
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $bulan[$row_dpro->periode]."-".$row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                        }
                        $tahun            = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                }
                else if($pro != '' & $kab !=''){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;                        
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp){
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             =$bulan[$row->periode]."-".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]             = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                        
                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                         $id_kab                     = $row_dkab->id;
                         $categories_kab[]             = $row_dkab->tahun;
                         $nilai_kab[]                = (float)$row_dkab->nilai;   
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                }
                

                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '40' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata
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

    public function demo(){
        if($this->input->is_ajax_request()){
            try 
            {  
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
               
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/demo/pencapaian_indikator/pencapaian_indikator_demo.js?v=".now("Asia/Jakarta");
                
                $data_page = array( );
                $str = $this->load->view($this->view_dir_demo."content_demo",$data_page,TRUE);

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


    
}
