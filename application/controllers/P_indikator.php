<?php defined('BASEPATH') OR exit('No direct script access allowed');

class P_indikator extends CI_Controller {
    var $view_dir   = "peppd1/evaluasi/pertumbuhan_ekonomi/";
    var $view_dir_demo   = "demo/evaluasi/pertumbuhan_ekonomi/";
    var $js_init    = "main";
    var $js_path    = "assets/js/peppd1/pertumbuhan_ekonomi/p_indikator.js";
    
    function __construct() {
        parent::__construct();
        $this->load->model("M_Master","m_ref");    
        $this->load->library("coordinat");
        $this->load->helper("prov");
        $this->load->helper("coordinat");
        $this->load->helper("jawa");
        $this->load->helper("blntbntt");
        $this->load->helper("kalimantan");
        $this->load->helper("sulawesi"); 
        $this->load->helper("malpa");
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
                $this->js_path    = "assets/js/peppd1/evaluasi/pertumbuhan_ekonomi/p_indikator_".$this->session->userdata(SESSION_LOGIN)->groupid.".js?v=".now("Asia/Jakarta");
                
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
    
    //peta
    function peta(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $pro = $this->input->post("provinsi");
                
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
                $posisi  = array('1100' => [96.699389, 4.713777], '1200' => [99.505662, 2.214178],'1300' => [101.030895, -0.709370],'1400' => [101.694295, 0.337480],'1500' => [102.447403, -1.444049],'1600' => [104.090464, -3.196092],'1700' => [102.234200, -3.574566],'1800' => [105.149064, -4.874095],'1900' => [106.841340, -2.303002],'2100' => [104.629934, 0.883574],'3100' => [106.850787, -6.206519],'3200' => [107.768762, -6.942884],'3300' => [109.998987, -7.313526],'3400' => [110.462457, -7.850908],'3500' => [112.644437, -7.585143],'3600' => [106.120118, -6.373885],'5100' => [115.183905, -8.408885],'5200' => [117.364766, -8.653642],'5300' => [122.367551, -9.007799],'6100' => [111.154836, -0.081797],'6200' => [113.538086, -1.591910],'6300' => [115.511801, -2.994677],'6400' => [116.491959, 0.592887],'6500' => [116.252830, 3.157217],'7100' => [124.464421, 0.943605],
                                 '7200' => [120.985712, -0.824342],'7300' => [119.922324, -3.585060],'7400' => [122.263216, -4.009943],'7500' => [122.509390, 0.701081],'7600' => [119.285441, -2.450009],'8100' => [130.535005, -3.598017],'8200' => [127.826539, 0.742708],'9100' => [132.783737, -1.926649],'9400' => [138.775450, -4.486719]);
                
                
                $xname      ="";
                $query      ="";
                //$title_y    ="Pertumbuhan Ekonomi(%)";
                $title      ="Perkembangan Pertumbuhan Ekonomi tahun";
                $thn_a      ='';
                $max_pe_p   ='';
                $pe_rkpd_rkp='';
                $kord       ='';
                $nilai_peta ='';
                if($pro == '' ){
                    //$judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $nsl = "select id_periode,nilai, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' ";
                    $list_nsl  = $this->db->query($nsl);
                    foreach ($list_nsl->result() as $row_n) {
                        $nilainsl = "Nasional : ".$row_n->nilai;
                    }
                    
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $periode_pe[]           = $row->id_periode;
                    }
                    $periode_pe_max=max($periode_pe);
                    
                    $perbandingan_pro ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='1'
                                    AND e.id_periode='$periode_pe_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='1' AND id_periode='$periode_pe_max' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    
                    $list_ppe_per = $this->db->query($perbandingan_pro);
                    foreach ($list_ppe_per->result() as $row_ppe_per) {
                       $idwill = $row_ppe_per->wilayah;                       
                       $lt = nama_provinsi($idwill);
                       if($idwill == '3100' || $idwill == '3400'){$jenis = 'Polygon'; }
                       else {$jenis = 'MultiPolygon'; }
                       $type1[] = ["type"        => "MultiPolygon",
                                        "type" => "Feature",
                                        "geometry"    =>array(
                                             "type"        => $jenis,
                                             //"coordinates" => $koordinat,
                                            "coordinates" => $lt,
                                          ),
                                        "id"          =>$row_ppe_per->wilayah,
                                        "properties"  =>array("ID"   =>'Indonesia',
                                                         "kode"      => $row_ppe_per->wilayah,
                                                         "NAME_2"    => $row_ppe_per->nama_provinsi,
                                                         "population"=> (float)$row_ppe_per->nilai,
                                                         "description"=>
                                                                '<strong>'.$row_ppe_per->nama_provinsi.'</strong><p> Pertumbuhan Ekonomin : '.$row_ppe_per->nilai.' %</p>',
                                         ),
                                        ];
                       $nilai_peta = $type1;
                    }
                    $thnmax ="SELECT MAX(tahun) AS thn,sumber,periode FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '1' "; 
                    $list_thn = $this->db->query($thnmax);
                    foreach ($list_thn->result() as $Lis_thn){
                    $periode                = $Lis_thn->periode;
                    if($periode == '00'){ $thn=$Lis_thn->thn;}
                    else{ $thn=  $prde[$Lis_thn->periode]." - ".$Lis_thn->thn; }
                    $thn_a=$thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                        //$this->js_geo    = "assets/js/geojson/indonesia-prov.geojson";
                    $this->js_geo    = "assets/js/geojson/indonesia.geojson";
                    $js_zoom   = 4.05;
                    $js_tengah = [119.206479, -0.320152];
                }
                else if($pro != ''){
                    $catdata = array();
                    
                    $nsl = "select id_periode,nilai, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' ";
                    $list_nsl  = $this->db->query($nsl);
                    foreach ($list_nsl->result() as $row_n) {
                        //$nilainsl = $row_n->nilai;
                    }
                    
                    $sql_pro = "SELECT P.* FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        //$judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;
                        $label_pe = $Lis_pro->label;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bulan[$row->periode]." ".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if($periode == '00'){ $thn=$row->tahun; }
                        else{ $thn=  $prde[$row->periode]." - ".$row->tahun; }
                        $categories1[]   = $thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='".$query."') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                            $nilainsl           = $xname." ".$row_dpro->nilai;
                            $id_pro             = $row_dpro->id;
                            $categories_pro[]   = $row_dpro->tahun;
                            $nilai_pro[]        = (float)$row_dpro->nilai;
                            $periode_pe[]       = $row_dpro->id_periode;
                        }
                       
                    $periode_pe_max=max($periode_pe);
                    $catdata_pro = array();
                    $perbandingan_pe ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='1' AND e.id_periode='$periode_pe_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='1' AND id_periode='$periode_pe_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_ppe_per = $this->db->query($perbandingan_pe);
                    foreach ($list_ppe_per->result() as $row_ppe_per) {
                        $label_ppe[]                               = $row_ppe_per->label;
                        $nilai_ppe_per[]                           = (float)$row_ppe_per->nilai;
                        $nilai_p_e_r1[$row_ppe_per->label]         = $row_ppe_per->nilai;
                        $nilai_p_e_r2[$row_ppe_per->nama_provinsi] = $row_ppe_per->nilai;
                    }                    
                   
                    //perbandingan kab
                    $catdata_kab = array();
                    $th_p_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$query."' AND e.id_indikator='1' ";
                    $t_list_kab_pe = $this->db->query($th_p_kab);
                    foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
                        $perio = $row_t_pe_kab->perio;
                    }
                    $ppe_kab="select p.nama_kabupaten as label,p.prov_id, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$query."' and (e.id_indikator='1' AND e.id_periode='$perio') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='1' AND id_periode='$perio' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                   
                    $list_kab_ppe_per = $this->db->query($ppe_kab);
                    foreach ($list_kab_ppe_per->result() as $row_ppe_kab_per) {
                        $nilai_ppe_per_kab[] = $row_ppe_kab_per->nilai;
                        $posisi_ppe          = strpos($row_ppe_kab_per->label, "Kabupaten");
                        if ($posisi_ppe !== FALSE){
                            $label_ppe11 = substr( $row_ppe_kab_per->label,0,3).". ".substr( $row_ppe_kab_per->label,10);
                        }else{
                            $label_ppe11 = $row_ppe_kab_per->label;
                        }
                        $label_pek1[]=$label_ppe11;
                        $label_pe1_k[$label_ppe11]=$row_ppe_kab_per->nilai;
                        $nilai_ppe_kab[]                           = (float)$row_ppe_kab_per->nilai;
                        $idwill=$row_ppe_kab_per->wilayah;
                        //$lt = nama_wilayah($idwill);
                        $idpro=$row_ppe_kab_per->prov_id;
                        if($idpro == '3100' || $idpro == '3200' || $idpro == '3300' || $idpro == '3400' || $idpro == '3500'|| $idpro == '3600')     { $lt = nama_jawa($idwill); }
                        else if($idpro == '5100' || $idpro == '5200' || $idpro == '5300' )                                       { $lt = nama_wilayah3($idwill); }
                        //kalimantan
                        else if($idpro == '6100' || $idpro == '6200' || $idpro == '6300' || $idpro == '6400'|| $idpro == '6500' ){ $lt = nama_wilayah4($idwill); }
                        //sulawes
                        else if($idpro =='7100' || $idpro == '7200' ||$idpro == '7300' ||$idpro == '7400' || $idpro == '7500'|| $idpro == '7600'){$lt = nama_wilayah5($idwill);}
                        else if($idpro =='8100' || $idpro =='8200' || $idpro =='9100' || $idpro =='9400'){$lt = nama_wilayah6($idwill); }
                        else { $lt = nama_wilayah($idwill); }
                        $periode_kab           = $row_ppe_kab_per->tahun;
                        //$lt = nama_wilayah($idwill);
                        $type[] = ["type"        => "MultiPolygon",
                                        "type" => "Feature",
                                        "geometry"    =>array(
                                             "type"        => 'MultiPolygon',
                                             //"coordinates" => $kord,
                                            "coordinates" => $lt,
                                          ),
                                        "properties"  =>array("ID"   =>'Indonesia',
                                                         "kode"      => $row_ppe_kab_per->wilayah,
                                                         "NAME_2"    => $label_ppe11,
                                                         "population"=> (float)$row_ppe_kab_per->nilai,
                                                         "description"=>
                                                                '<strong>'.$label_ppe11.'</strong><p></Br> Periode : '.$periode_kab.' </Br> Pertumbuhan Ekonomin : '.$row_ppe_kab_per->nilai.' %</p>',
                                         ),
                                        ];
                       $nilai_peta = $type;
                    }
                $thnmax ="SELECT MAX(tahun) AS thn,sumber, periode FROM nilai_indikator WHERE `wilayah`='".$query."' AND id_indikator = '1' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $periode                = $Lis_thn->periode;
                        if($periode == '00'){ $thn=$Lis_thn->thn; //$thn2=$row->tahun; 
                        }
                        else{ $thn=  $prde[$Lis_thn->periode]." - ".$Lis_thn->thn; }
                    $thn_a      =$thn;
                    $datasumber = "Sumber : ".$Lis_thn->sumber;
                }   
                   
                 $this->js_geo    = "assets/js/geojson/indonesia-".$query.".geojson";
                 $js_zoom = 6;
                 $js_tengah = $posisi[$query];
                 $properties = 'NAME_1';
                }

                
                                
               
                $json_data = array(
                    "tahun_a"    => $thn_a,
                    "nasional" => $nilainsl,
                   "peta" =>["type"=> "FeatureCollection",
                            //   "features"=> [["type"=>$type]]
                           "features"=> $nilai_peta
                              ], 
                    "js_geo"   => base_url($this->js_geo),
                    "js_zoom"  => $js_zoom,
                    "js_tengah"  => $js_tengah,
                    
                    "text"       => $title,
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
    
    //Pertumbuhan Ekonomi
    function pertumbuhan_ekomomi(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $pro = $this->input->post("provinsi");
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
                
                $xname      ="";
                $query      ="";
                $prov_tgl   ="";
                $tahun_p_k  ='';
                $title_y    ="Pertumbuhan Ekonomi (%)";
                $title      ="Perkembangan Pertumbuhan Ekonomi (%)";
                $max_pe_p             ='';
                $pe_rkpd_rkp          ='';
                $catdata_pro_r='';
                $perbandingan_2th    ='';
                $pe_perbandingan_kabb ='';
                $thn_r='';
                
                $d_peek="SELECT I.`deskripsi` FROM indikator I where id='1'";
                $list_peek = $this->db->query($d_peek);
                foreach ($list_peek->result() as $peek){$peek_d   = $peek->deskripsi;}
                
                
                if($pro == '' ){
                    $judul="Pertumbuhan Ekonomi Nasional";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                   
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bulan[$row->periode]." ".$row->tahun;
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
                    $label_ppe1        = '';
                    $catdata_pro       = '';
                    $pe_perbandingan_pro="";
                    //kab 
                    $label_pe_k1='';
                    $catdata_kab     ="";
                    $tahun_pe_kab = "";
                }
                else if($pro != ''){
                    $catdata = array();
                    $sql_pro = "SELECT P.* FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        $judul1 = "Pertumbuhan Ekonomi  Provinsi ".$Lis_pro->nama_provinsi;
                        $label_pe = $Lis_pro->label;
                    }
                    $judul=$judul1;
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bulan[$row->periode]." ".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if($periode == '00'){ $thn=$row->tahun; }
                        else{ $thn=  $prde[$row->periode]." - ".$row->tahun; }
                        $categories1[]   = $thn;
                        $periode_id[]    =$row->id_periode;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='".$query."') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]           = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                         if($row_dpro->t_m_rpjmn==0){
                             $nilai_rpjmn='';
                         }else{
                             $nilai_rpjmn                = (float)$row_dpro->t_m_rpjmn;   
                         }
                         $nilai_rpjmn1[]                = $nilai_rpjmn; 
                         if($row_dpro->t_rkpd==0){
                             $nilai_rkpd='';
                         }else{
                             $nilai_rkpd                = (float)$row_dpro->t_rkpd;   
                         }
                         $nilai_rkpd1[]                = $nilai_rkpd; 
                         if($row_dpro->t_k_rkp==0){
                             $nilai_rkp='';
                         }else{
                             $nilai_rkp                = (float)$row_dpro->t_k_rkp;   
                         }
                         $nilai_rkp1[]                = $nilai_rkp; 
                         $periode_pe[]                = $row_dpro->id_periode;
                        }
                        $tahun_pro         = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                        $nilaiData['name'] = "Target Makro RPJMN";
                        $nilaiData['data'] = $nilai_rpjmn1;
                        array_push($catdata, $nilaiData);
                        $nilaiData['name'] = "Target RKPD";
                        $nilaiData['data'] = $nilai_rkpd1;
                        array_push($catdata, $nilaiData);
                        $nilaiData['name'] = "Target Kewilayahan RKP";
                        $nilaiData['data'] = $nilai_rkp1;
                        array_push($catdata, $nilaiData);
                        
                    if($nilai_pro[4] > $nilai_pro[5]){                        
                        $nu_p="menurun";
                        if($nilai[5]>$nilai_pro[5]){ $ba_p="di bawah";
                        }else{ $ba_p="di atas"; }
                    } else {
                        $nu_p="meningkat";
                        if($nilai[5]>$nilai_pro[5]){ $ba_p="di bawah";
                        }else{ $ba_p="di atas"; }
                    }
                        
                    if($nilai_pro[5] > $nilai_rkpd1[5]){ $rkpdpe="di atas"; }
                    else{ $rkpdpe="di bawah"; }
                    if($nilai_pro[5] > $nilai_rkp1[5]){ $rkppe='di atas'; }
                    else{ $rkppe='di bawah'; }
                    $max_pe_p    ="Pertumbuhan ekonomi ". $xname ." pada ".$categories[5]." ".$nu_p." dibandingkan dengan tahun ".$categories[4].". Pada ".$categories[5]." pertumbuhan ekonomi ". $xname ." adalah ".$nilai_pro[5].", sedangkan pada ".$categories[4]." pertumbuhannya tercatat sebesar ".$nilai_pro[4]."%. </Br></Br> "
                            . "Pertumbuhan ekonomi ". $xname ." pada ".$categories[5]." berada ".$ba_p." nasional. Pertumbuhan ekonomi nasional pada ".$categories[5]." adalah ".$nilai[5] ."%. ";
                    $pe_rkpd_rkp ="Pertumbuhan ekonomi ". $xname ." pada ".$categories[5]." berada ".$rkpdpe." target RKPD ". $xname ." (".$nilai_rkpd1[5]."%) dan ".$rkppe." target kewilayahan RKP (".$nilai_rkp1[5]."%).";
                    
                    $periode_pe_max=max($periode_pe);
                    $catdata_pro = array();
                    $perbandingan_pe ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='1' AND e.id_periode='$periode_pe_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='1' AND id_periode='$periode_pe_max' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    $list_ppe_per = $this->db->query($perbandingan_pe);
                    foreach ($list_ppe_per->result() as $row_ppe_per) {
                        $label_ppe[]                               = $row_ppe_per->label;
                        $nilai_ppe_per[]                           = (float)$row_ppe_per->nilai;
                        $nilai_p_e_r1[$row_ppe_per->label]         = $row_ppe_per->nilai;
                        $nilai_p_e_r2[$row_ppe_per->nama_provinsi] = $row_ppe_per->nilai;
                        $prov_tgl                                  = $row_ppe_per->tahun;
                        $nilai_r= $row_ppe_per->nilai;
                        if ($nilai_r <= 0){
                            $nilai_r1 = 0;
                        }else{
                            $nilai_r1= $row_ppe_per->nilai;
                        }
                        $nilai_radar[]=(float)$nilai_r1;
                    }                    
                    $label_ppe1          = $label_ppe;
                    $nilaiData_p['name'] = 'Provinsi';
                    $nilaiData_p['data'] = $nilai_ppe_per;
                    //$nilaiData_pro           = $nilaiData_p;
                    array_push($catdata_pro, $nilaiData_p);
                    
                    $nilai_data_pe_r1   = $nilai_p_e_r1;
                    $nilai_data_pe_r2   = $nilai_p_e_r2;
                    $ranking            = $nilai_data_pe_r1;
                    arsort($ranking);
                    $nr=1;
                    foreach($ranking as $x=>$x_value){
                        if($x==$label_pe){
                            $rengking_pro=$nr++;
                        }
                        $urutan_pro=$x . $x_value . $x_value .$nr++;
                        
                    }
                    $nilai_ppe_p_max = max($nilai_data_pe_r2);  //nila paling besar
                    $nilai_ppe_p_min = min($nilai_data_pe_r2);  //nila paling rendah
                    $nama_pe1=$nilai_data_pe_r2;
                    arsort($nama_pe1);
                    $nama_pe2=array_keys($nama_pe1);
                    $pe_perbandingan_pro = "Perbandingan pertumbuhan ekonomi antar 34 provinsi menunjukkan bahwa pertumbuhan ekonomi ". $xname ." pada tahun ".max($categories_pro)." berada pada urutan ke-".$rengking_pro.".
                           Provinsi dengan tingkat pertumbuhan ekonomi tertinggi adalah ".array_shift($nama_pe2)." (".$nilai_ppe_p_max."%),
                            sedangkan provinsi dengan pertumbuhan ekonomi terendah adalah ".end($nama_pe2)." (".$nilai_ppe_p_min."%). ";
                    
                    //radar
                    $catdata_pro_r = array();
                    $perbandingan_tk2 ="select p.label as label, p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='1' AND e.id_periode='$periode_id[4]') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='1' AND id_periode='$periode_id[4]' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    $list_tk_per2 = $this->db->query($perbandingan_tk2);
                    foreach ($list_tk_per2->result() as $row_tk_per2) {
                        $label_tk2[]     = $row_tk_per2->label;
                        $np_tk2[]        = $row_tk_per2->nama_provinsi;
                        $nilai_tk_r2     = $row_tk_per2->nilai;
                        if ($nilai_tk_r2 <= 0){
                            $nilai_tk2 = 0;
                        }else{
                            $nilai_tk2= $row_tk_per2->nilai;
                        }
                        $nilai_tk_r22[]=(float)$nilai_tk2;
                        $thn_r=  $row_tk_per2->tahun;
                    }
                    $label_tk2          = $label_tk2;
                    $nilaiData_p2['name'] = $thn_r;
                    $nilaiData_p2['data'] = $nilai_tk_r22;
                    array_push($catdata_pro_r, $nilaiData_p2);
                    
                    $nilaiData_p1['name'] = $prov_tgl;
                    $nilaiData_p1['data'] = $nilai_radar;
                    array_push($catdata_pro_r, $nilaiData_p1);
                     
//                    $nilai_data_tk_r2 = $nilai_tk_r22;
//                    $array_tiga = array();
//                    for($i=0;$i< count($nilai_data_tk_per);$i++){
//                        $array_tiga[$i]=$nilai_data_tk_per[$i]-$nilai_data_tk_r2[$i];
//                    }
//                    $kombinasi_tk=array_combine($label_tk2,$array_tiga);
//                    $kombinasi_tk2=array_combine($label_tk2,$array_tiga);
//                    asort($kombinasi_tk2); //tinggi-rendah
//                    $kombinasi_tk3=array_combine($label_tk2,$array_tiga);
//                    asort($kombinasi_tk3);
//                
//                    $nrtkp=1;
//                    foreach($kombinasi_tk2 as $xtkp=>$xtkp_value){
//                        if($xtkp==$label_pe){
//                            $rengkingtk_p=$nrtkp++;
//                        }
//                        $urutan_pro_tkp=$xtkp . $xtkp_value . $xtkp_value .$nrtkp++;                        
//                    }
//                    $nilai_tk_per_p_max = max($kombinasi_tk2);  //nila paling besar
//                $nilai_tk_per_p_min = min($kombinasi_tk2);  //nila paling rendah
//                
//                $kombinasi_tk4=array_combine($np_tk2,$array_tiga);
//                asort($kombinasi_tk4);
//                $label_p_tk=array_keys($kombinasi_tk4);
//                $label_tk_per_p_max = array_shift($label_p_tk); //label paling besar
//                $label_tk_per_p_min = end($label_p_tk);     //label paling kecil
//                
//                $nper_tk=abs($kombinasi_tk2[$label_pe]);
//                
//                $perbandingan_2th = "Perbandingan jumlah penduduk miskin ".$categories_pro[3]." dengan ".$categories_pro[5]." menunjukkan bahwa 
//                        provinsi yang mengalami penurunan jumlah penduduk miskin terbesar adalah ".$label_tk_per_p_max." (".number_format($nilai_tk_per_p_min,0,",",".")." orang). 
//                        Dari sisi perubahan jumlah penduduk miskin periode ".$categories_pro[3]." hingga ".$categories_pro[5].", ". $xname ." berada pada urutan ke-".$rengkingtk_p.", dengan jumlah penduduk miskin ".$nu_p." sebesar ".number_format($nper_tk,0,",",".")." orang";
//                
                
                    //perbandingan kab
                    $catdata_kab = array();
                    $th_p_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$query."' AND e.id_indikator='1' ";
                    $t_list_kab_pe = $this->db->query($th_p_kab);
                    foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
                        $perio = $row_t_pe_kab->perio;
                    }
                    $ppe_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$query."' and (e.id_indikator='1' AND e.id_periode='$perio') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='1' AND id_periode='$perio' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    //print_r($ppe_kab);exit();
                    $list_kab_ppe_per = $this->db->query($ppe_kab);
                    foreach ($list_kab_ppe_per->result() as $row_ppe_kab_per) {
                        $nilai_ppe_per_kab[] = $row_ppe_kab_per->nilai;
                        $posisi_ppe          = strpos($row_ppe_kab_per->label, "Kabupaten");
                        if ($posisi_ppe !== FALSE){
                            $label_ppe11 = substr( $row_ppe_kab_per->label,0,3).". ".substr( $row_ppe_kab_per->label,10);
                        }else{
                            $label_ppe11 = $row_ppe_kab_per->label;
                        }
                        $label_pek1[]=$label_ppe11;
                        $label_pe1_k[$label_ppe11]=$row_ppe_kab_per->nilai;
                        //$thn_p_k[]=$row_ppe_kab_per->tahun;
                        $tahun_p_k        = $bulan[$row_ppe_kab_per->periode]."".$row_ppe_kab_per->tahun;
                        $nilai_ppe_kab[]                           = (float)$row_ppe_kab_per->nilai;
                    }
                    $label_pe_k1          = $label_pek1;
                    $nilaiData_k['name'] = "kabupaten";
                    $nilaiData_k['data'] = $nilai_ppe_kab;
                    //$nilaiData_pro           = $nilaiData_p;
                    array_push($catdata_kab, $nilaiData_k);
                    $nilai_data_ppe_per_kab = $nilai_ppe_per_kab;
                    //$nilai_ppe_per_kab = $nilai_ppe_per_kab;
                    $nilai_ppe_per_kab_max = max($label_pe1_k);
                    $nilai_ppe_per_kab_min = min($label_pe1_k);
                    arsort($nilai_ppe_per_kab);
                    $nama_k1=$label_pe1_k;
                    arsort($nama_k1);
                    $nama_k2=array_keys($nama_k1);
                    $selisih_pe=$nilai_ppe_per_kab_max-$nilai_ppe_per_kab_min;
                    
                    $nrk=1;
                    foreach($nilai_ppe_per_kab as $xk=>$xk_value){
                        if($xk==$label_pe){
                            $rengking_pro_k=$nr++;
                        }
                        $urutan_pro_k=$xk . $xk_value . $xk_value .$nrk++;
                        
                    }
                   $tahun_pe_kab        =$tahun_p_k." Antar Kabupaten/kota di ".$judul ;
                   $pe_perbandingan_kabb="Perbandingan pertumbuhan ekonomi antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada tahun ".$tahun_p_k." daerah dengan tingkat pertumbuhan ekonomi tertinggi adalah ".array_shift($nama_k2)." (".$nilai_ppe_per_kab_max." %), sedangkan daerah dengan pertumbuhan ekonomi terendah adalah ".end($nama_k2)." (".$nilai_ppe_per_kab_min."%).
                                            Selisih pertumbuhan ekonomi tertinggi dan terendah di ". $xname ." pada tahun ".$tahun_p_k." adalah sebesar ".$selisih_pe."%.";
                   
                   
                }
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '1' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }                
               
                $json_data = array(
                    "judul"       => $judul,
                    "text"        => $title,
                    "ket"         => $peek_d,
                    "max_pe_p"    => $max_pe_p,
                    "pe_rkpd_rkp" => $pe_rkpd_rkp,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata,
                    
                    "text_pro"       => "Perbandingan Pertumbuhan Ekonomi Tahun ".$prov_tgl." Antar Provinsi (%)",
                    "categories_pro"=> $label_ppe1,
                    "series_pro"     => $catdata_pro,
                    "pe_perbandingan_pro" =>$pe_perbandingan_pro,
                    
                    "text_radar"    => "Perbandingan Pertumbuhan Ekonomi Tahun ".$thn_r." Dengan Tahun ".$prov_tgl." Antar Provinsi (%)",
                    "data_radar"    =>$catdata_pro_r,
                    "perbandingan_2th"=>$perbandingan_2th,
                    "text_kab"       => "Perbandingan Pertumbuhan Ekonomi Tahun ".$tahun_p_k." Antar Kab (%)",
                    "categories_kab" => $label_pe_k1,
                    "series_kab"     => $catdata_kab,
                    "tahun_pe_kab"   => $tahun_pe_kab,
                    "pe_perbandingan_kab" =>$pe_perbandingan_kabb,
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
    
    
    /*
     * demo
     */
    public function demo(){
        if($this->input->is_ajax_request()){
            try 
            {                
                //if(!$this->session->userdata(SESSION_LOGIN)){throw new Exception("Session expired, please login",2);}
                
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/demo/evaluasi/pertumbuhan_ekonomi/p_indikator_demo.js?v=".now("Asia/Jakarta");
                
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
