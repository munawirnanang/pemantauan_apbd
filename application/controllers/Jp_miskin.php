<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Jp_miskin extends CI_Controller {
    var $view_dir   = "peppd1/evaluasi/jp_miskin/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/jp_miskin/jp_miskin.js";
    
    function __construct() {
              parent::__construct();
        $this->load->model("M_Master","m_ref");
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
                $this->js_path    = "assets/js/peppd1/evaluasi/jp_miskin/jp_miskin_".$this->session->userdata(SESSION_LOGIN)->groupid.".js?v=".now("Asia/Jakarta");
                
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
                $bln = array( '00' => '','01' => 'Januari','02' => 'Februari','03' => 'Maret','04' => 'April','05' => 'Mei','06' => 'Juni','07' => 'Juli','08' => 'Agustus','09' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember',); 
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
                $posisi  = array('1100' => [96.699389, 4.713777], '1200' => [99.505662, 2.214178],'1300' => [101.030895, -0.709370],'1400' => [101.694295, 0.337480],'1500' => [102.447403, -1.444049],'1600' => [104.090464, -3.196092],'1700' => [102.234200, -3.574566],'1800' => [105.149064, -4.874095],'1900' => [106.841340, -2.303002],'2100' => [104.629934, 0.883574],'3100' => [106.850787, -6.206519],'3200' => [107.768762, -6.942884],'3300' => [109.998987, -7.313526],'3400' => [110.462457, -7.850908],'3500' => [112.644437, -7.585143],'3600' => [106.120118, -6.373885],'5100' => [115.183905, -8.408885],'5200' => [117.364766, -8.653642],'5300' => [122.367551, -9.007799],'6100' => [111.154836, -0.081797],'6200' => [113.538086, -1.591910],'6300' => [115.511801, -2.994677],'6400' => [116.491959, 0.592887],'6500' => [116.252830, 3.157217],'7100' => [124.464421, 0.943605],
                                 '7200' => [120.985712, -0.824342],'7300' => [119.922324, -3.585060],'7400' => [122.263216, -4.009943],'7500' => [122.509390, 0.701081],'7600' => [119.285441, -2.450009],'8100' => [130.535005, -3.598017],'8200' => [127.826539, 0.742708],'9100' => [132.783737, -1.926649],'9400' => [133.112976, -3.802826]);

                $xname      ="";
                $query      ="";
                $title_y    ="";
                $title      ="";
                $thn_a      ='';
                $max_pe_p   ='';
                $pe_rkpd_rkp='';
                $kord       ='';
                $nilai_peta ='';
                if($pro == '' ){
                    $thnmax ="SELECT * FROM (
                                SELECT * 
                                FROM nilai_indikator 
                                WHERE (id_indikator='40' AND wilayah='1000') 
                                        AND (id_periode) IN (
                                                SELECT  MAX(id_periode) AS id_periode 
                                                FROM nilai_indikator WHERE id_indikator='40' AND wilayah='1000' 
                                                ) 
                                GROUP BY id_periode 
                        ORDER BY id_periode DESC LIMIT 6) Y 
                        ORDER BY id_periode ASC ";
                    $list_thn = $this->db->query($thnmax);
                    foreach ($list_thn->result() as $Lis_thn){
                        //$thn_aa      =$Lis_thn->tahun;
                        $nilai = number_format($Lis_thn->nilai,0,',','.');
                        $nilainsl = "Nasional : ".$nilai;
                        $thn_a           = $bln[$Lis_thn->periode]." ".$Lis_thn->tahun;
                        $datasumber ="Sumber : ".$Lis_thn->sumber;
                    }     
                    
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $periode_pe[]           = $row->id_periode;
                    }
                    $periode_pe_max=max($periode_pe);
                    
                    $perbandingan_pro ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='40'
                                    AND e.id_periode='$periode_pe_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='40' AND id_periode='$periode_pe_max' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    
                    $list_ppe_per = $this->db->query($perbandingan_pro);
                    foreach ($list_ppe_per->result() as $row_ppe_per) {
                       $idwill  = $row_ppe_per->wilayah;                       
                       $lt      = nama_provinsi($idwill);
                       $nilai   = number_format($row_ppe_per->nilai,0,',','.');
                       if($idwill == '3100' || $idwill == '3400'){$jenis = 'Polygon'; }
                       else {$jenis = 'MultiPolygon'; }
                       $type1[] = [
                           "type"        => "MultiPolygon",
                           "type" => "Feature",
                           "geometry"    =>array(
                               "type"        => $jenis,
                               "coordinates" => $lt,
                               ),
                           "id"          =>$row_ppe_per->wilayah,
                           "properties"  =>array(
                               "ID"   =>'Indonesia',
                               "kode"       => $row_ppe_per->wilayah,
                               "NAME_2"     => $row_ppe_per->nama_provinsi,
                               "population" => (float)$row_ppe_per->nilai,
                               "description"=> '<strong>'.$row_ppe_per->nama_provinsi.' </Br>Periode : '.$thn_a.'</strong><p> Jumlah penduduk miskin : '.$nilai.' Orang</p>',
                               ),
                           ];
                       $nilai_peta = $type1;
                    }

                    $this->js_geo    = "assets/js/geojson/indonesia.geojson";
                    $js_zoom   = 4;
                    $js_tengah = [112.328365, 0.017125]; 
                }
                else{
                    
                    $sql_pro = "SELECT P.* FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                     $thnmax ="SELECT * FROM (
                                SELECT * 
                                FROM nilai_indikator 
                                WHERE (id_indikator='40' AND wilayah='".$query."') 
                                        AND (id_periode) IN (
                                                SELECT  MAX(id_periode) AS id_periode 
                                                FROM nilai_indikator WHERE id_indikator='40' AND wilayah='".$query."' 
                                                ) 
                                GROUP BY id_periode 
                        ORDER BY id_periode DESC LIMIT 6) Y 
                        ORDER BY id_periode ASC ";
                    $list_thn = $this->db->query($thnmax);
                    foreach ($list_thn->result() as $Lis_thn){
                        $nilai = number_format($Lis_thn->nilai,0,',','.');
                        $nilainsl   = $xname." ".$nilai;
                        $thn_a      = $bln[$Lis_thn->periode]." ".$Lis_thn->tahun;
                        $datasumber ="Sumber : ".$Lis_thn->sumber;
                    }     
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $periode_pe[]                = $row_dpro->id_periode;
                    }
                    $periode_pe_max=max($periode_pe);
                    
                    //perbandingan kab
                    $catdata_kab = array();
                    $th_p_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$query."' AND e.id_indikator='40' ";
                    $t_list_kab_pe = $this->db->query($th_p_kab);
                    foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
                        $perio = $row_t_pe_kab->perio;
                    }
                    $ppe_kab="select p.nama_kabupaten as label, p.prov_id, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$query."' and (e.id_indikator='40' AND e.id_periode='$perio') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='40' AND id_periode='$perio' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_ppe_per = $this->db->query($ppe_kab);
                    foreach ($list_kab_ppe_per->result() as $row_ppe_kab_per) {
                        $nilai_ppe_per_kab[] = $row_ppe_kab_per->nilai;
                        $nilai   = number_format($row_ppe_kab_per->nilai,0,',','.');
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
                        $idpro=$row_ppe_kab_per->prov_id;
                        if($idpro == '3100' || $idpro == '3200' || $idpro == '3300' || $idpro == '3400' || $idpro == '3500'|| $idpro == '3600')     { $lt = nama_jawa($idwill); }
                        else if($idpro == '5100' || $idpro == '5200' || $idpro == '5300' )                                       { $lt = nama_wilayah3($idwill); }
                        //kalimantan
                        else if($idpro == '6100' || $idpro == '6200' || $idpro == '6300' || $idpro == '6400'|| $idpro == '6500' ){ $lt = nama_wilayah4($idwill); }
                        //sulawes
                        else if($idpro =='7100' || $idpro == '7200' ||$idpro == '7300' ||$idpro == '7400' || $idpro == '7500'|| $idpro == '7600'){$lt = nama_wilayah5($idwill);}
                        else if($idpro =='8100' || $idpro =='8200' || $idpro =='9100' || $idpro =='9400'){$lt = nama_wilayah6($idwill); }
                        else { $lt = nama_wilayah($idwill); }
                        $periode_kab           = $bln[$row_ppe_kab_per->periode]." ".$row_ppe_kab_per->tahun;
                        $no=1;
                        $type[] = ["type"        => "MultiPolygon",
                                        "type" => "Feature",
                                        "geometry"    =>array(
                                             "type"        => 'MultiPolygon',
                                            "coordinates" => $lt,
                                          ),
                                        "id"          =>(float)$row_ppe_kab_per->wilayah,
                                        "properties"  =>array("ID"   =>$no++,
                                            "kode"      => $row_ppe_kab_per->wilayah,
                                            "NAME_2"    => $label_ppe11,
                                            "population"=> (float)$row_ppe_kab_per->nilai,
                                            "description"=>'<strong>'.$label_ppe11.'</strong></Br> Periode : '.$periode_kab.' </Br><p> Jumlah penduduk miskin : '.$nilai.' Orang</p>',
                                         ),
                            ];
                       $nilai_peta = $type;
                    }
                   
                 $this->js_geo    = "assets/js/geojson/indonesia-".$query.".geojson";
                 $js_zoom = 6;
                 //$js_tengah = [119.206479, -0.320152];
                 $js_tengah = $posisi[$query];
                 $properties = 'NAME_1';
                }
                
                $json_data = array(
                   "peta" =>["type"=> "FeatureCollection",
                            //   "features"=> [["type"=>$type]]
                           "features"=> $nilai_peta
                              ], 
                    "js_geo"   => base_url($this->js_geo),
                    "js_zoom"  => $js_zoom,
                    "js_tengah"  => $js_tengah,
                    "tahun_a"    => $thn_a,
                    "nasional" => $nilainsl,
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
    
    //Jumlah penduduk miskin Chart
    function jumlah_p_miskin(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                //$this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $bln = array( '00' => '','01' => 'Januari','02' => 'Februari','03' => 'Maret','04' => 'April','05' => 'Mei','06' => 'Juni','07' => 'Juli','08' => 'Agustus','09' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                $d_peek="SELECT I.`deskripsi` FROM indikator I where id='40'";
                $list_peek = $this->db->query($d_peek);
                foreach ($list_peek->result() as $peek){ $peek_d   = $peek->deskripsi; }
                
                $xname     ="";
                $query     ="";
                $prov_tgl  ="";
                $tahun_p_k ="";        
                $title     ="Perkembangan Jumlah Penduduk Miskin";
                $title_y   = "Jumlah Penduduk Miskin (Orang)";
                $pe_perbandingan_pro ='';
                $catdata_pro_r='';
                $jp_perbandingan_2th='';
                $pe_perbandingan_kab='';

                $max_pe_p='';
                $pe_rkpd_rkp='';
                if($pro == '' ){
                    $judul="Perkembangan penduduk miskin";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";                    
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bln[$row->periode]." ".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if($periode == '00'){ $thn=$row->tahun; }
                        else{ $thn=  $bln[$row->periode]." - ".$row->tahun;}
                        $categories1[]   = $thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                    $label_ppe1='';
                    $catdata_pro='';
                    $pe_perbandingan_pro="";
                    //kab 
                    $label_pe_k1='';
                    $catdata_kab     ="";
                    $tahun_pe_kab = "";
                }
                else if($pro != ''){
                    $nilai_5 ='';
                    $nilai_3 ='';
                    $catdata = array();
                    $sql_pro = "SELECT P.* FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        $judul1 = "Perkembangan penduduk miskin Provinsi ".$Lis_pro->nama_provinsi;
                        $label_pe = $Lis_pro->label;
                    }
                    $judul=$judul1;
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bln[$row->periode]." ".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if($periode == '00'){ $thn=$row->tahun; }
                        else{ $thn=  $bln[$row->periode]." - ".$row->tahun; }
                        $categories1[]          = $thn;
                        $periode_jpm_id[]       =$row->id_periode;
                    }
                    $tahun             = $categories1;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]           = $row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                         if($row_dpro->t_m_rpjmn==0){ $nilai_rpjmn=''; }
                         else{ $nilai_rpjmn                = (float)$row_dpro->t_m_rpjmn; }
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
//                    $nilaiData['name'] = "Target Makro RPJMN";
//                    $nilaiData['data'] = $nilai_rpjmn1;
//                    array_push($catdata, $nilaiData);
//                    $nilaiData['name'] = "Target RKPD";
//                    $nilaiData['data'] = $nilai_rkpd1;
//                    array_push($catdata, $nilaiData);
//                    $nilaiData['name'] = "Target Kewilayahan RKP";
//                    $nilaiData['data'] = $nilai_rkp1;
//                    array_push($catdata, $nilaiData);
                        
                    
                    $nilai_5 = number_format($nilai_pro[5],0,",",".");
                    $nilai_3 = number_format($nilai_pro[3],0,",",".");
                    if($nilai_pro[3] > $nilai_pro[5]){                        
                        $nu_p="bertambah";
                        $mm_j=$nilai_pro[3]-$nilai_pro[5];
                        $rt_jpk2=$mm_j/$nilai_pro[3];
                        $rt_jpk3=abs($rt_jpk2*100);
                        $rt_jpk33=number_format($rt_jpk3,2);
//                        if($nilai[5]>$nilai_pro[5]){ $ba_p="di bawah";
//                        }else{ $ba_p="di atas"; }
                    } else {
                        $nu_p="berkurang";
                        $mm_j=$nilai_pro[5]-$nilai_pro[3];
                        $rt_jpk2 =$mm_j/$nilai_pro[3];
                        $rt_jpk3 =$rt_jpk2*100;
                        $rt_jpk33=number_format($rt_jpk3,2);
//                        if($nilai[5]>$nilai_pro[5]){ $ba_p="di bawah";
//                        }else{ $ba_p="di atas"; }
                    }
                        
//                    if($nilai_pro[5] > $nilai_rkpd1[5]){ $rkpdpe="di atas"; }
//                    else{ $rkpdpe="di bawah"; }
//                    if($nilai_pro[5] > $nilai_rkp1[5]){ $rkppe='di atas'; }
//                    else{ $rkppe='di bawah'; }
                    
                    $max_pe_p    ="Jumlah penduduk miskin ". $xname ." pada ".$categories[5]." sebanyak ".$nilai_5." orang sedangkan jumlah penduduk miskin pada ".$categories[3]." sebanyak ".$nilai_3." orang. Selama periode ".$categories[3]." hingga ".$categories[5]." jumlah penduduk miskin di ". $xname ." ".$nu_p." sebanyak ".number_format($mm_j,0,",",".")." orang atau sebesar ".$rt_jpk33."% ";
                    
                    $periode_pe_max=max($periode_pe);
                    $catdata_pro = array();
                    $perbandingan_pe ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='40' AND e.id_periode='$periode_pe_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='40' AND id_periode='$periode_pe_max' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    $list_ppe_per = $this->db->query($perbandingan_pe);
                    foreach ($list_ppe_per->result() as $row_ppe_per) {
                        $label_ppe[]                               = $row_ppe_per->label;
                        $nilai_ppe_per[]                           = (float)$row_ppe_per->nilai;
                        $nilai_p_e_r1[$row_ppe_per->label]         = $row_ppe_per->nilai;
                        $nilai_p_e_r2[$row_ppe_per->nama_provinsi] = $row_ppe_per->nilai;
                        $prov_tgl           = $bln[$row_ppe_per->periode]." ".$row_ppe_per->tahun;
                    }                    
                    $label_ppe1          = $label_ppe;
                    $nilaiData_p['name'] = ' ';
                    $nilaiData_p['data'] = $nilai_ppe_per;
                    //$nilaiData_pro           = $nilaiData_p;
                    array_push($catdata_pro, $nilaiData_p);
                    
                    $nilai_data_tk_per = $nilai_ppe_per;
                    
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
                    $nama_pe2 = array_keys($nama_pe1);
                    $pe_perbandingan_pro = "Perbandingan jumlah penduduk miskin antar 34 provinsi menunjukkan bahwa jumlah penduduk miskin ". $xname ." pada ".max($categories_pro)." berada pada urutan ke-".$rengking_pro.".
                           Provinsi dengan jumlah penduduk miskin tertinggi adalah ".array_shift($nama_pe2)." (".$nilai_ppe_p_max." orang),
                            sedangkan provinsi dengan jumlah penduduk miskin terendah adalah ".end($nama_pe2)." (".$nilai_ppe_p_min." orang). ";
                    //radar
                    $catdata_pro_r = array();
                    $perbandingan_tk2 ="select p.label as label, p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='40' AND e.id_periode='$periode_jpm_id[3]') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='40' AND id_periode='$periode_jpm_id[3]' 
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
                        $nilai_tk_r22[]=$nilai_tk2;
                        $thn_r=  $bln[$row_tk_per2->periode]." - ".$row_tk_per2->tahun;
                    }
                    $label_tk2          = $label_tk2;
                    $nilaiData_p2['name'] = $thn_r;
                    $nilaiData_p2['data'] = $nilai_tk_r22;
                    array_push($catdata_pro_r, $nilaiData_p2);
                    
                    $nilaiData_p1['name'] = $prov_tgl;
                    $nilaiData_p1['data'] = $nilai_ppe_per;
                    array_push($catdata_pro_r, $nilaiData_p);
                     
                    $nilai_data_tk_r2 = $nilai_tk_r22;
                    $array_tiga = array();
                    for($i=0;$i< count($nilai_data_tk_per);$i++){
                        $array_tiga[$i]=$nilai_data_tk_per[$i]-$nilai_data_tk_r2[$i];
                    }
                    $kombinasi_tk=array_combine($label_tk2,$array_tiga);
                    $kombinasi_tk2=array_combine($label_tk2,$array_tiga);
                    asort($kombinasi_tk2); //tinggi-rendah
                    $kombinasi_tk3=array_combine($label_tk2,$array_tiga);
                    asort($kombinasi_tk3);
                
                    $nrtkp=1;
                    foreach($kombinasi_tk2 as $xtkp=>$xtkp_value){
                        if($xtkp==$label_pe){
                            $rengkingtk_p=$nrtkp++;
                        }
                        $urutan_pro_tkp=$xtkp . $xtkp_value . $xtkp_value .$nrtkp++;                        
                    }
                    $nilai_tk_per_p_max = max($kombinasi_tk2);  //nila paling besar
                $nilai_tk_per_p_min = min($kombinasi_tk2);  //nila paling rendah
                
                $kombinasi_tk4=array_combine($np_tk2,$array_tiga);
                asort($kombinasi_tk4);
                $label_p_tk=array_keys($kombinasi_tk4);
                $label_tk_per_p_max = array_shift($label_p_tk); //label paling besar
                $label_tk_per_p_min = end($label_p_tk);     //label paling kecil
                
                $nper_tk=abs($kombinasi_tk2[$label_pe]);
                
                $jp_perbandingan_2th = "Perbandingan jumlah penduduk miskin ".$categories_pro[3]." dengan ".$categories_pro[5]." menunjukkan bahwa 
                        provinsi yang mengalami penurunan jumlah penduduk miskin terbesar adalah ".$label_tk_per_p_max." (".number_format($nilai_tk_per_p_min,0,",",".")." orang). 
                        Dari sisi perubahan jumlah penduduk miskin periode ".$categories_pro[3]." hingga ".$categories_pro[5].", ". $xname ." berada pada urutan ke-".$rengkingtk_p.", dengan jumlah penduduk miskin ".$nu_p." sebesar ".number_format($nper_tk,0,",",".")." orang";
    
                
                    //perbandingan kab
                    $catdata_kab = array();
                    $th_p_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$query."' AND e.id_indikator='40' ";
                    $t_list_kab_pe = $this->db->query($th_p_kab);
                    foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
                        $perio = $row_t_pe_kab->perio;
                    }
                    $ppe_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$query."' and (e.id_indikator='40' AND e.id_periode='$perio') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='40' AND id_periode='$perio' group by wilayah ) 
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
                        //$thn_p_k[]=$row_ppe_kab_per->tahun;
                        $tahun_p_k        = $bln[$row_ppe_kab_per->periode]." ".$row_ppe_kab_per->tahun;
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
                   $tahun_pe_kab=$tahun_p_k." Antar Kabupaten/kota di ".$judul ;
                   $pe_perbandingan_kab="Perbandingan jumlah penduduk miskin antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada ".$tahun_p_k." daerah dengan jumlah penduduk miskin tertinggi adalah ".array_shift($nama_k2)." (".$nilai_ppe_per_kab_max." orang), sedangkan daerah dengan jumlah penduduk miskin terendah adalah ".end($nama_k2)." (".$nilai_ppe_per_kab_min." orang).
                                            Selisih jumlah penduduk miskin tertinggi dan terendah di ". $xname ." pada ".$tahun_p_k." adalah sebesar ".$selisih_pe." orang.";
                   
                   
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '40' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }                
               
                $json_data = array(
                   // "judul"      => $judul,
                    "text"       => $title,
                    "text2"      => $title_y,
                    "ket"        => $peek_d,
                    "max_pe_p"   => $max_pe_p,
                    "pe_rkpd_rkp" => $pe_rkpd_rkp,
                    
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata,
                    
                    "text_pro"       => "Perbandingan Jumlah Penduduk Miskin ".$prov_tgl." Antar Provinsi (Orang)",
                    "categories_pro"=> $label_ppe1,
                    "series_pro"     => $catdata_pro,
                    "pe_perbandingan_pro" =>$pe_perbandingan_pro,
                    //radar
                    "text_radar"    => "Perbandingan Jumlah Penduduk Miskin ".$prov_tgl." Dengan ".$prov_tgl." (Orang)",
                    "catdata_radar"     => $catdata_pro_r,
                    "perbandingan_2th"=>$jp_perbandingan_2th,
                    //kab/kota
                    "text_kab"       => "Perbandingan Jumlah Penduduk Miskin  ".$tahun_p_k." Antar Kabupaten/kota (Orang)",
                    "categories_kab" => $label_pe_k1,
                    "series_kab"     => $catdata_kab,
                    "tahun_pe_kab"   => $tahun_pe_kab,
                    "pe_perbandingan_kab"=>$pe_perbandingan_kab,
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
    
    
    
    //sruktur
    function struktur_pdrb(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
//                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
  //              $kab = $this->input->post("kabupaten");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $title_x           = "Perkembangan Tingkat Kemiskinan";
                $title_y           = "Tingkat Kemiskinan";
                $d_peek="SELECT I.`deskripsi` FROM indikator I where id='36'";
                $list_peek = $this->db->query($d_peek);
                foreach ($list_peek->result() as $peek){$peek_tk   = $peek->deskripsi;}
                
                if($pro == ''){
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
                else if($pro != ''){
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
                            $categories_pro[]           = $bulan[$row_dpro->periode]."-".$row_dpro->tahun;
                            $nilai_pro[]                = (float)$row_dpro->nilai;   
                            if($row_dpro->t_m_rpjmn==0){
                                $nilai_rpjmn='';
                            }else{
                                $nilai_rpjmn                = (float)$row_dpro->t_m_rpjmn;   
                            }
                            $nilai_rpjmn1[]            = $nilai_rpjmn;
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
                         
                        }
                        $tahun_pro             = $categories_pro;
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
                }

                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '36' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "ket_tk"        => $peek_tk,
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
//                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
  //              $kab = $this->input->post("kabupaten");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $bulan_tahun = array( '00' => '','01' => 'Januari','02' => 'Februari','03' => 'Maret','04' => 'April','05' => 'Mei','06' => 'Juni','07' => 'Juli','08' => 'Agustus','09' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember',);
                $title_x           = "Perkembangan Tingkat Kemiskinan";
                $title_y           = "Tingkat Kemiskinan";
                $d_peek="SELECT I.`deskripsi` FROM indikator I where id='36'";
                $list_peek = $this->db->query($d_peek);
                foreach ($list_peek->result() as $peek){$peek_tk   = $peek->deskripsi;}
                $label_ppe1='';
                $label_tk_r='';
                $catdata_pro='';
                $max_n_tk='';
                $max_p_tk ='';
                $tk_rkpd_rkp = '';
                $label_pe_k1 = '';
                $catdata_kab = '';
                $tahun_pe_kab ='';
                $tk_perbandingan_kabb = '';
                
                $tk_perbandingan_pro='';
                
                $catdata_pro1 = array();
                $catdata_pro_r= array();
                
                if($pro == ''){
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
                else {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi, P.label FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;  
                        $label_ = $Lis_pro->label;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bulan[$row->periode]."-".$row->tahun;
                        $tk_tahun[]             = $bulan_tahun[$row->periode]." ".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode_tk_id[]        = $row->id_periode;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                            $id_pro                     = $row_dpro->id;
                            $categories_pro[]           = $bulan[$row_dpro->periode]."-".$row_dpro->tahun;
                            $nilai_pro[]                = (float)$row_dpro->nilai;   
                            if($row_dpro->t_m_rpjmn==0){
                                $nilai_rpjmn='';
                            }else{
                                $nilai_rpjmn                = (float)$row_dpro->t_m_rpjmn;   
                            }
                            $nilai_rpjmn1[]            = $nilai_rpjmn;
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
                    $tahun_pro             = $categories_pro;
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
                        
                        if($nilai_pro[3] > $nilai_pro[5]){
                            $mm_tk="menurun";
                            if($nilai[5]>$nilai_pro[5]){
                                $ba_tk='di bawah';
                                }else{
                            $ba_tk='di atas';
                        }
                        } else {
                        $mm_tk="meningkat";
                        if($nilai[5]>$nilai_pro[5]){
                            $ba_tk='di bawah';
                        }else{
                            $ba_tk='di atas';
                        }
                    }
                        
                    if($nilai_rkpd1[5]=='-'){ $tk_rkpd_rkp1 =""; }
                    else{
                        if($nilai_pro[5] > $nilai_rkpd1[5]){ $rkpdtk="di atas"; }
                        else{ $rkpdtk="di bawah"; }
                        if($nilai_pro[5] > $nilai_rkp1[5]){ $rkptk='di atas'; }
                        else{ $rkptk='di bawah'; }
                        $tk_rkpd_rkp1 = "Tingkat kemiskinan ". $xname ." periode ".$tk_tahun[5]." berada ".$rkpdtk." target RKPD ". $xname ." (".number_format($nilai_rkpd1[5],2)."%) dan ".$rkptk." target kewilayahan RKP (".number_format($nilai_rkp1[5],2)."%.) ";
                    }    
                        
                        $max_n_tk    ="Tingkat kemiskinan ". $xname ." pada ".$tk_tahun[5]." ".$mm_tk." dibandingkan dengan ".$tk_tahun[3].". Pada ".$tk_tahun[5]." tingkat kemiskinan ". $xname ." adalah sebesar ". number_format(end($nilai_pro),2) ."%, sedangkan pada ".$tk_tahun[3]." tingkat kemiskinan tercatat sebesar ".number_format($nilai_pro[3],2)."%. ";
                        $max_p_tk    ="Tingkat kemiskinan ". $xname ." pada ".$tk_tahun[5]." berada ".$ba_tk." capaian nasional. Tingkat kemiskinan nasional pada ".$tk_tahun[5]." adalah sebesar ".number_format(end($nilai),2) ."%. ";
                        $tk_rkpd_rkp = $tk_rkpd_rkp1;
                    
                        $periode_pe_max=max($periode_pe);
                        $catdata_pro  = array();
                        
                        $perbandingan_tk ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='36' AND e.id_periode='$periode_pe_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='36' AND id_periode='$periode_pe_max' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    $list_tk_per = $this->db->query($perbandingan_tk);
                    foreach ($list_tk_per->result() as $row_tk_per) {
                        $label_tk[]                               = $row_tk_per->label;
                        $nilai_ppe_per[]                           = (float)$row_tk_per->nilai;
                        $nilai_tk_r1[$row_tk_per->label]         = $row_tk_per->nilai;
                        $nilai_tk_r2[$row_tk_per->nama_provinsi] = $row_tk_per->nilai;
                    }                    
                    $label_ppe1          = $label_tk;
                    $nilaiData_p['name'] = "Provinsi";
                    $nilaiData_p['data'] = $nilai_ppe_per;
                    array_push($catdata_pro, $nilaiData_p);
                    $nilaiData_p1['name'] = "Provinsi";
                    $nilaiData_p1['data'] = $nilai_ppe_per;
                    array_push($catdata_pro1, $nilaiData_p1);
                    
                    $nilai_data_tk_r1  = $nilai_tk_r1;
                    $nilai_data_tk_r2  = $nilai_tk_r2;
                    $rankingtk = $nilai_data_tk_r1;
                    arsort($rankingtk);
                    $nrtk=1;
                    foreach($rankingtk as $xtk=>$xtk_value){
                        if($xtk==$label_){
                            $rengkingtk_pro=$nrtk++;
                        }
                        $urutan_pro_tk=$xtk . $xtk_value . $xtk_value .$nrtk++;
                        
                    }
                    $nilai_tk_p_max = max($nilai_data_tk_r1);  //nila paling besar
                    $nilai_tk_p_min = min($nilai_data_tk_r1);  //nila paling rendah
                    $nama_tk1=$nilai_data_tk_r2;
                    arsort($nama_tk1);
                    $nama_tk2=array_keys($nama_tk1);
                    $tk_perbandingan_pro = "Perbandingan tingkat kemiskinan antar 34 provinsi menunjukkan bahwa tingkat kemiskinan ". $xname ." "
                            . "             pada ".max($tk_tahun)." berada pada urutan ke-".$rengkingtk_pro.".
                           Provinsi dengan tingkat kemiskinan tertinggi adalah ".array_shift($nama_tk2)." (".$nilai_tk_p_max."%),
                            sedangkan provinsi dengan tingkat kemiskinan terendah adalah ".end($nama_tk2)." (".$nilai_tk_p_min."%). ";
                    
                    //radar
                        $perbandingan_tk2 ="select p.label as label, p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='36' AND e.id_periode='$periode_tk_id[3]') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='36' AND id_periode='$periode_tk_id[3]' 
                            group by wilayah) group by wilayah order by wilayah asc";
                        $list_tk_per2 = $this->db->query($perbandingan_tk2);
                    foreach ($list_tk_per2->result() as $row_tk_per2) {
                        $label_tk2[]     = $row_tk_per2->label;
                        $np_tk2[]        = $row_tk_per2->nama_provinsi;
                        //$nilai_tk_per2[] = $row_tk_per2->nilai;
                        $nilai_tk_r2     = $row_tk_per2->nilai;
                        if ($nilai_tk_r2 <= 0){
                            $nilai_tk2 = 0;
                        }else{
                            $nilai_tk2= $row_tk_per2->nilai;
                        }
                        $nilai_tk_r22[]=$nilai_tk2;
                    }
                    $label_tk_r          = $label_tk2;
                    $nilaiData_p2['name'] = $periode_tk_id[3];
                    $nilaiData_p2['data'] = $nilai_tk_r22;
                    array_push($catdata_pro_r, $nilaiData_p2);
                    
                    
                    //perbandingan kab
                    $catdata_kab = array();
                    $th_p_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$query."' AND e.id_indikator='36' ";
                    $t_list_kab_pe = $this->db->query($th_p_kab);
                    foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
                        $perio = $row_t_pe_kab->perio;
                    }
                    $ppe_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$query."' and (e.id_indikator='36' AND e.id_periode='$perio') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='36' AND id_periode='$perio' group by wilayah ) 
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
                        $tahun_p_k        = $bulan[$row_ppe_kab_per->periode]."".$row_ppe_kab_per->tahun;
                        $nilai_ppe_kab[]                           = (float)$row_ppe_kab_per->nilai;
                    }
                    //
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
                        if($xk==$label_){
                            $rengking_pro_k=$nrk++;
                        }
                        $urutan_pro_k=$xk . $xk_value . $xk_value .$nrk++;
                        
                    }
                   $tahun_pe_kab        = $tahun_p_k." Antar Kabupaten/kota di " ;
                   $tk_perbandingan_kabb= "Perbandingan tingkat kemiskinan antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada ".$tahun_p_k." daerah dengan tingkat kemiskinan tertinggi adalah ".array_shift($nama_k2)." (".$nilai_ppe_per_kab_max." %), sedangkan daerah dengan tingkat kemiskinan terendah adalah ".end($nama_k2)." (".$nilai_ppe_per_kab_min."%).
                                            Selisih tingkat kemiskinan tertinggi dan terendah di ". $xname ." pada tahun ".$tahun_p_k." adalah sebesar ".$selisih_pe."%.";
                    
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
                    "series"     => $catdata,
                    "ket_tk"        => $peek_tk,
                    "max_n_tk"   => $max_n_tk,
                    "max_p_tk"   => $max_p_tk,
                    "tk_rkpd_rkp"   => $tk_rkpd_rkp,
                        
                    "text_pro"       => "Perbandingan Pertumbuhan Ekonomi Antar Provinsi (%)",
                    "categories_pro"=> $label_ppe1,
                    "series_pro"     => $catdata_pro,
                    "tk_perbandingan_pro"     => $tk_perbandingan_pro,
                    //"pe_perbandingan_pro" =>$pe_perbandingan_pro,
                    "label_r" => $label_tk_r,
                    "series_r"     => $catdata_pro1,
                    //kab
                    "text_kab"       => "Perbandingan Pertumbuhan Ekonomi Antar Kab (%)",
                    "categories_kab"=> $label_pe_k1,
                    "series_kab"     => $catdata_kab,
                    "tahun_pe_kab"     => $tahun_pe_kab,
                    "tk_perbandingan_kabb"     => $tk_perbandingan_kabb
                    
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
                $pro = $this->input->post("provinsi");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $title_x           = "Perkembangan Jumlah Penduduk Miskin";
                $title_y           = "Jumlah Penduduk Miskin (Orang)";
                $max_n_jpk            = '';
                $tahun_jpk_kab        = '';
                $jpk_perbandingan_kab = '';
                $catdata_pro1 = array();
                $d_peek="SELECT I.`deskripsi` FROM indikator I where id='40'";
                $list_peek = $this->db->query($d_peek);
                foreach ($list_peek->result() as $peek){$peek_pm   = $peek->deskripsi;}                
                if($pro == ''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
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
                else if($pro != '' ){
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi, P.label FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname  = $Lis_pro->nama_provinsi;
                        $query  = $Lis_pro->id;  
                        $label_ = $Lis_pro->label;
                    }
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                        $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $id_pro                     = $row_dpro->id;
                         $categories_pro[]           = $bulan[$row_dpro->periode]."-".$row_dpro->tahun;
                         $nilai_pro[]                = (float)$row_dpro->nilai;   
                         $periode_jpm[]              = $row_dpro->id_periode;
                        }
                        $tahun         = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                                                
                        if($nilai_pro[3] > $nilai_pro[5]){
                            $rt_jpk=$nilai_pro[5]-$nilai_pro[3];
                            $rt_jpkk=abs($nilai_pro[5]-$nilai_pro[3]);
                            $rt_jpk2=$rt_jpk/$nilai_pro[3];
                            $rt_jpk3=abs($rt_jpk2*100);
                            $rt_jpk33=number_format($rt_jpk3,2,",",".");
                            $bb_jpk="berkurang";
                        }
                        else{
                            $rt_jpk  =$nilai_pro[5]-$nilai_pro[3];
                            $rt_jpk2=$rt_jpk/$nilai_pro[3];
                            $rt_jpk3=$rt_jpk2*100;
                            $rt_jpk33=number_format($rt_jpk3,2,",",".");
                            $bb_jpk="bertambah";
                            //number_format($nilai_pro[5],0,",",".")
                        }
                        $max_n_jpk    ="Jumlah penduduk miskin  ". $xname ." pada  ".$categories_pro[5]." sebanyak ".$nilai_pro[5]." orang sedangkan jumlah penduduk miskin pada ".$categories_pro[3]." sebanyak ".$nilai_pro[3]." orang. Selama periode ".$categories_pro[3]." hingga ".$categories_pro[5]." jumlah penduduk miskin di provinsi ". $xname ." ".$bb_jpk." sebanyak ".$rt_jpkk." orang atau sebesar ".$rt_jpk33."%.";
                        
                        //perbandingan provinsi
                        $periode_jpm_max=max($periode_jpm);
                        $catdata_pro = array();
                        $perbandingan_jpm ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='40' AND e.id_periode='$periode_jpm_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='40' AND id_periode='$periode_jpm_max' 
                            group by wilayah) group by wilayah order by wilayah asc";
                        $list_jpm_per = $this->db->query($perbandingan_jpm);
                        foreach ($list_jpm_per->result() as $row_jpm_per) {
                            $label_jpm[]                               = $row_jpm_per->label;
                            $nilai_jpm_per[]                           = (float)$row_jpm_per->nilai;
                            $nilai_jpm_r1[$row_jpm_per->label]         = $row_jpm_per->nilai;
                            $nilai_jpm_r2[$row_jpm_per->nama_provinsi] = $row_jpm_per->nilai;
                        }
                    $label_jpm1          = $label_jpm;
                    $nilaiData_p_jpm['name'] = ' ';
                    $nilaiData_p_jpm['data'] = $nilai_jpm_per;
                    array_push($catdata_pro, $nilaiData_p_jpm);
                    
                    $nilai_data_jpm_r1   = $nilai_jpm_r1;
                    $nilai_data_jpm_r2   = $nilai_jpm_r2;
                    $ranking            = $nilai_data_jpm_r1;
                    arsort($ranking);
                    $nr=1;
                    foreach($ranking as $x=>$x_value){
                        if($x==$label_jpm){
                            $rengking_pro=$nr++;
                        }
                        $urutan_pro=$x . $x_value . $x_value .$nr++;
                        
                    }
                    $nilai_jpm_p_max = max($nilai_data_jpm_r2);  //nila paling besar
                    $nilai_jpm_p_min = min($nilai_data_jpm_r2);  //nila paling rendah
                    $nama_jpm1=$nilai_data_jpm_r2;
                    arsort($nama_jpm1);
                    $nama_jpm2=array_keys($nama_jpm1);
                    $jpm_perbandingan_pro = "Perbandingan pertumbuhan ekonomi antar 34 provinsi menunjukkan bahwa pertumbuhan ekonomi ". $xname ." pada tahun ".max($categories_pro)." berada pada urutan ke-.
                           Provinsi dengan tingkat pertumbuhan ekonomi tertinggi adalah ".array_shift($nama_jpm2)." (".$nilai_jpm_p_max."%),
                            sedangkan provinsi dengan pertumbuhan ekonomi terendah adalah ".end($nama_jpm2)." (".$nilai_jpm_p_min."%). ";
                    
                    
                        //radar tahun terakhir
                        $perbandingan_jpm1 ="select p.label as label, p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='40' AND e.id_periode='$periode_jpm[5]') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='40' AND id_periode='$periode_jpm[5]'
                            group by wilayah) group by wilayah order by wilayah asc";
                        $list_jpm_per1 = $this->db->query($perbandingan_jpm1);
                        foreach ($list_jpm_per1->result() as $row_jpm_per1) {
                            $label_jpm1[]     = $row_jpm_per1->label;
                            $np_jpm1[]        = $row_jpm_per1->nama_provinsi;
                            $nilai_jpm_r1     = $row_jpm_per1->nilai;
                            if ($nilai_jpm_r1 <= 0){
                                $nilai_jpm1 = 0;
                            }else{
                                $nilai_jpm1 = $row_jpm_per1->nilai;
                            }
                            $nilai_jpm_r11[] = (float)$nilai_jpm1;
                        }                       
                        $nilaiData_1['name'] = $categories_pro[5];
                        $nilaiData_1['data'] = $nilai_jpm_r11;
                        array_push($catdata_pro1, $nilaiData_1);
                        
                        //radar
                        $perbandingan_jpm2 ="select p.label as label, p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='40' AND e.id_periode='$periode_jpm[3]') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='40' AND id_periode='$periode_jpm[3]' 
                            group by wilayah) group by wilayah order by wilayah asc";
                        
                        $list_jpm_per2 = $this->db->query($perbandingan_jpm2);
                        foreach ($list_jpm_per2->result() as $row_jpm_per2) {
                            $label_jpm2[]     = $row_jpm_per2->label;
                            $np_jpm2[]        = $row_jpm_per2->nama_provinsi;
                            $nilai_jpm_r2     = $row_jpm_per2->nilai;
                            if ($nilai_jpm_r2 <= 0){
                                $nilai_jpm2 = 0;
                            }else{
                                $nilai_jpm2 = $row_jpm_per2->nilai;
                            }
                            $nilai_jpm_r22[] = (float)$nilai_jpm2;
                        }
                        $label_jpm_r          = $label_jpm2;
                        $nilaiData_p2['name'] = $categories_pro[3];
                        $nilaiData_p2['data'] = $nilai_jpm_r22;
                        array_push($catdata_pro1, $nilaiData_p2);
                        
                        //perbandingan kab
                    $catdata_kab = array();
                    $th_p_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$query."' AND e.id_indikator='40' ";
                    $t_list_kab_pe = $this->db->query($th_p_kab);
                    foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
                        $perio = $row_t_pe_kab->perio;
                    }
                    $ppe_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$query."' and (e.id_indikator='40' AND e.id_periode='$perio') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='40' AND id_periode='$perio' group by wilayah ) 
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
                        $tahun_p_k        = $bulan[$row_ppe_kab_per->periode]."".$row_ppe_kab_per->tahun;
                        $nilai_ppe_kab[]                           = (float)$row_ppe_kab_per->nilai;
                    }
                    //
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
                        if($xk==$label_){
                            $rengking_pro_k=$nrk++;
                        }
                        $urutan_pro_k=$xk . $xk_value . $xk_value .$nrk++;
                        
                    }
                   $tahun_jpk_kab        = $tahun_p_k." Antar Kabupaten/kota di " ;
                   $jpk_perbandingan_kab= "Perbandingan tingkat kemiskinan antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada ".$tahun_p_k." daerah dengan tingkat kemiskinan tertinggi adalah ".array_shift($nama_k2)." (".$nilai_ppe_per_kab_max." %), sedangkan daerah dengan tingkat kemiskinan terendah adalah ".end($nama_k2)." (".$nilai_ppe_per_kab_min."%).
                                            Selisih tingkat kemiskinan tertinggi dan terendah di ". $xname ." pada tahun ".$tahun_p_k." adalah sebesar ".$selisih_pe."%.";
                }

                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '40' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "ket_pm"        => $peek_pm,
                    "max_n_pm"   => $max_n_jpk,
                    
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata,
                    //perbandingan provinsi
                    "text_pro"       => "Gambar 9 Perbandingan Jumlah Penduduk Miskin Sep-2019 (Orang)",
                    "categories_pro" => $label_jpm1,
                    "series_pro"     => $catdata_pro,
                    "jpm_perbandingan_pro" => $jpm_perbandingan_pro,                    
                    //radar
                    "title_text" => "Gambar 10 Perbandingan Jumlah Penduduk Miskin Sep-2018 dengan Sep-2019 (Orang)",
                    "label_r"    => $label_jpm_r,
                    "series_r"   => $catdata_pro1,
                    //"text_pro"       => "Perbandingan Pertumbuhan Ekonomi Antar Provinsi (%)",
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
    //jumlah_penganggur
    function jumlah_penganggur(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $pro = $this->input->post("provinsi");
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $xname="";
                $query="";
                $select_title="SELECT * FROM indikator where id='4'";
                $list_title = $this->db->query($select_title);
                foreach($list_title->result() as $lst){
                    $title= $lst->nama_indikator;
                    $peek_jp   = $lst->deskripsi;
                }

                if($pro == '' ){
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
                else if($pro != '' ) {
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
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '4' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn            =$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                //$title       = "Perkembangan Jumlah Pengangguran";
                $title_y     = "Jumlah Pengangguran (orang)";
                
                $json_data = array(
                    "ket_jp"        => $peek_jp,
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
    
//    function pdrb(){
//        if($this->input->is_ajax_request()){
//            try {
//                $requestData= $_REQUEST;
//                $this->form_validation->set_rules('provinsi','Provinsi','required');
//                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
//                $pro = $this->input->post("provinsi");
//                $kab = $this->input->post("kabupaten");
//                
//                $xname="";
//                $query="";
//                $select_title="SELECT * FROM indikator where id='2'";
//                $list_title = $this->db->query($select_title);
//                foreach($list_title->result() as $lst){
//                    $title= $lst->nama_indikator;
//                }
//                if($pro == '' & $kab ==''){
//                    $xname="Indonesia";
//                    $query="1000";
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data = $this->db->query($sql);
//                    $nilai    = array();
//                    $nilaiData['seriesname'] ='Indonesia';
//                    $i=1;
//                   // $seriesname="";
//                    foreach ($list_data->result() as $row) {
//                        $id            = $row->id;
//                        $categories[]  = $row->tahun ;
//                        $nilai[]       = (float)$row->nilai;
//                        //$sumber        = $row->sumber;
//
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = $xname;
//                    $nilaiData['data'] = $nilai;
//                    $catdata[]          = $nilaiData;
//                    
//                    
//                    
//                }
//                else if($pro != '' & $kab =='') {
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;
//                        
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data = $this->db->query($sql);
////                    $nilai    = array();
////                    $nilaiData['seriesname'] ='Indonesia';
//                    $i=1;
//                   // $seriesname="";
//                    foreach ($list_data->result() as $row) {
//                        $id            = $row->id;
//                        $categories[]  = $row->tahun ;
//                        $nilai[]       = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    //$catdata          = $nilaiData;
//                    array_push($catdata, $nilaiData);
//                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_p = $this->db->query($sql_p);
//                    foreach ($list_p->result() as $row_p) {
//                        $id_p            = $row_p->id;
//                        $categories_p[]  = $row_p->tahun ;
//                        $nilai_p[]       = (float)$row_p->nilai;
//                        $sumber_p        = $row_p->sumber;
//
//                    }
//                    $tahun             = $categories_p;
//                    $nilaiData['name'] = $xname;
//                    $nilaiData['data'] = $nilai_p;
//                    //$catdata          = $nilaiData;
//                    array_push($catdata, $nilaiData);
//                
//                } 
//                else if($pro != '' & $kab !='') {
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;
//                    }
//                    
//                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
//                    $list_kp = $this->db->query($sql_kp);
//                    foreach ($list_kp->result() as $Lis_kp){
//                        $kpname = $Lis_kp->nama_kabupaten;
//                        $kpquery = $Lis_kp->id;
//                    }
//                    
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id            = $row->id;
//                        $categories[]  = $row->tahun ;
//                        $nilai[]       = (float)$row->nilai;
//                        //$sumber        = $row->sumber;
//                    }
//                    //$tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    //$catdata          = $nilaiData;
//                    array_push($catdata, $nilaiData);
//                    
//                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_p = $this->db->query($sql_p);
//                    foreach ($list_p->result() as $row_p) {
//                        $id_p            = $row_p->id;
//                        $categories_p[]  = $row_p->tahun ;
//                        $nilai_p[]       = (float)$row_p->nilai;
//                        $sumber_p        = $row_p->sumber;
//                    }
//                    $tahun             = $categories_p;
//                    $nilaiData['name'] = $xname;
//                    $nilaiData['data'] = $nilai_p;
//                    //$catdata          = $nilaiData;
//                    array_push($catdata, $nilaiData);
//                    
//                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                        $list_dkab  = $this->db->query($sql_dkab);
//                        foreach ($list_dkab->result() as $row_dkab) {
//                         $id_kab                     = $row_dkab->id;
//                         $categories_kab[]             = $row_dkab->tahun;
//                         $nilai_kab[]                = (float)$row_dkab->nilai;   
//                        }
//                        $tahun_kab             = $categories_kab;
//                        $nilaiData['name'] = $kab;
//                        $nilaiData['data'] = $nilai_kab;
//                        array_push($catdata, $nilaiData);
//                        
//                }
//                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '2' ";
//                $list_thn = $this->db->query($thnmax);
//                foreach ($list_thn->result() as $Lis_thn){
//                    $thn=$Lis_thn->thn;
//                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
//                }
//                //$title       = "Perkembangan PDRB Per Kapita ADHK";
//                $title_y     = "PDRB Per Kapita ADHK (Rp)";
//                               
//                
//                
//                $json_data = array(
//                    "text"           => $title,
//                    "text1"          => $title_y,
//                    "categories"     => $tahun, 
//                    "series"         => $catdata,
//                     "sumber"        => $datasumber
//                );
//                exit(json_encode($json_data));
//            } catch (Exception $exc) {
//                $json_data = array(
//                    "text"           => "",
//                    "text1"          => "",
//                    "categories"     => "",
//                    "series"         => 0,
//                     "sumber"        => ""
//                );
//                exit(json_encode($json_data));
//            }
//        }
//        else
//            die;
//    }
    
//    function adhk(){
//        if($this->input->is_ajax_request()){
//            try {
//                $requestData= $_REQUEST;
//                $this->form_validation->set_rules('provinsi','Provinsi','required');
//                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
//                $pro = $this->input->post("provinsi");
//                $kab = $this->input->post("kabupaten");
//               
//                $xname      ="";
//                $query      ="";
//                //$title      = "Perkembangan PDRB per Kapita ADHK Tanun Dasar 2010";
//                $title_y    = "PDRB per Kapita ADHK Tanun Dasar 2010";
//                $select_title="SELECT * FROM indikator where id='3'";
//                $list_title = $this->db->query($select_title);
//                foreach($list_title->result() as $lst){
//                    $title= $lst->nama_indikator;
//                }
//
//                if($pro == '' & $kab ==''){
//                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    $catdata[]            = $nilaiData;
//                }
//                else if($pro != '' & $kab ==''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;
//                        $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;
//                        
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                        $list_dpro  = $this->db->query($sql_dpro);
//                        foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                        }
//                        $tahun_pro             = $categories_pro;
//                        $nilaiData['name'] = $xname;
//                        $nilaiData['data'] = $nilai_pro;
//                        array_push($catdata, $nilaiData);
//                }
//                else if($pro != '' & $kab !=''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;                        
//                    }
//                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
//                    $list_kp = $this->db->query($sql_kp);
//                    foreach ($list_kp->result() as $Lis_kp){
//                        $kpname = $Lis_kp->nama_kabupaten;
//                        $kpquery = $Lis_kp->id;
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dpro  = $this->db->query($sql_dpro);
//                    foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                    }
//                    $tahun_pro             = $categories_pro;
//                    $nilaiData['name'] = $xname;
//                    $nilaiData['data'] = $nilai_pro;
//                    array_push($catdata, $nilaiData);
//                        
//                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
//                                FROM(
//                                    select DISTINCT id_periode from nilai_indikator 
//                                    where (id_indikator='3' AND wilayah='1000') 
//                                        AND (id_periode, versi) in (
//                                                                    select id_periode, max(versi) as versi 
//                                                                    from nilai_indikator 
//                                                                    WHERE id_indikator='3' AND wilayah='1000' group by id_periode
//                                                                    )
//                                    order by id_periode 
//                                    Desc limit 6 
//                                    ) REF
//                                LEFT JOIN(
//                                            select id_periode,nilai,sumber,tahun 
//                                            from nilai_indikator 
//                                            where (id_indikator='3' AND wilayah='".$kpquery."')
//                                                AND (id_periode, versi) in (
//                                                                select id_periode, max(versi) as versi 
//                                                                from nilai_indikator 
//                                                                WHERE id_indikator='3' AND wilayah='".$kpquery."' group by id_periode
//                                                ) 
//                                            group by id_periode 
//                                            order by id_periode Desc limit 6
//                                ) IND	ON REF.id_periode=IND.id_periode"; 
//                        $list_dkab  = $this->db->query($sql_dkab);
//                        foreach ($list_dkab->result() as $row_dkab) {
//                         //$id_kab                     = $row_dkab->id;
//                         $categories_kab[]             = $row_dkab->tahun;
//                         if($row_dkab->nilai_kab==0){
//                             $nilai_kab1='';
//                         }else{
//                             $nilai_kab1                = (float)$row_dkab->nilai_kab;   
//                         }
//                         $nilai_kab[]                = $nilai_kab1;   
//                        }
//                        //$datay_ppe3 = array_reverse($nilai_kab);
//                        //$tahun_kab         = $categories_kab;
//                        $nilaiData['name'] = $kab;
//                        $nilaiData['data'] = array_reverse($nilai_kab);
//                        array_push($catdata, $nilaiData);
//                }
//                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '3' ";
//                $list_thn = $this->db->query($thnmax);
//                foreach ($list_thn->result() as $Lis_thn){
//                    $thn=$Lis_thn->thn;
//                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
//                }
//                
//                $json_data = array(
//                    "text"       => $title,
//                    "text2"      => $title_y,
//                    "sumber"     => $datasumber,                    
//                    "categories" => $tahun, 
//                    "series"     => $catdata
//                );
//                exit(json_encode($json_data));
//            } catch (Exception $exc) {
//                $json_data = array(
//                    "text"           => "",
//                    "categories"     => "",
//                    "series"         => 0
//                );
//                exit(json_encode($json_data));
//            }
//        }
//        else
//            die;
//    }
//
    
//        
    function tingkat_pengangguran(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $pro = $this->input->post("provinsi");
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                
                //$title           = "Perkembangan Tingkat Pengangguran Terbuka";
                $title_y           = "Tingkat Pengangguran Terbuka";
                $select_title="SELECT * FROM indikator where id='6'";
                $list_title = $this->db->query($select_title);
                foreach($list_title->result() as $lst){
                    $title= $lst->nama_indikator;
                    $peek_tp   = $lst->deskripsi;
                }

                if($pro == '' ){
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
                else if($pro != ''){
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
                        $categories[]           = $bulan[$row->periode]."-".$row->tahun;
                        $nli                    = number_format($row->nilai,2);
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
                        }
                        $tahun_pro             = $categories_pro;
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
                }
                
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '6' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $json_data = array(
                    "ket_tp"        => $peek_tp,
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
//        
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
                if($pro == ''){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                }
                else if($pro != '' ){
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
                         if($row_dpro->t_rkpd==0){
                             $nilai_rkpd='';
                         }else{
                             $nilai_rkpd                = (float)$row_dpro->t_rkpd;   
                         }
                         $nilai_rkpd1[]                = $nilai_rkpd; 
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                        $nilaiData['name'] = "Target RKPD";
                        $nilaiData['data'] = $nilai_rkpd1;
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
   
//    //Gini Rasio
    function gini_rasio(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $title_x           = "Perkembangan Gini Rasio";
                $title_y           = "Gini Rasio";
                
                if($pro == '' ){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
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
                    $catdata[]            = $nilaiData;
                }
                else if($pro != ''){
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
                         if($row_dpro->t_rkpd==0){
                             $nilai_rkpd='';
                         }else{
                             $nilai_rkpd                = (float)$row_dpro->t_rkpd;   
                         }
                         $nilai_rkpd1[]                = $nilai_rkpd;
                        }
                        $tahun_pro             = $categories_pro;
                        $nilaiData['name'] = $xname;
                        $nilaiData['data'] = $nilai_pro;
                        array_push($catdata, $nilaiData);
                        $nilaiData['name'] = "Target RKPD";
                        $nilaiData['data'] = $nilai_rkpd1;
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
//
//    //Harapan Hidup
//    function harapan_hidup(){
//        if($this->input->is_ajax_request()){
//            try {
//                $requestData= $_REQUEST;
//                $this->form_validation->set_rules('provinsi','Provinsi','required');
//                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
//                $pro = $this->input->post("provinsi");
//                $kab = $this->input->post("kabupaten");
//               
//                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
//                $title_x           = "Perkembangan Angka Harapan Hidup";
//                $title_y           = "Angka Harapan Hidup (Tahun)";
//                
//                if($pro == '' & $kab ==''){
//                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    $catdata[]            = $nilaiData;
//                }
//                else if($pro != '' & $kab ==''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;  
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                        $list_dpro  = $this->db->query($sql_dpro);
//                        foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                        }
//                        $tahun_pro             = $categories_pro;
//                        $nilaiData['name'] = $xname;
//                        $nilaiData['data'] = $nilai_pro;
//                        array_push($catdata, $nilaiData);
//                }
//                else if($pro != '' & $kab !=''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;                        
//                    }
//                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
//                    $list_kp = $this->db->query($sql_kp);
//                    foreach ($list_kp->result() as $Lis_kp){
//                        $kpname = $Lis_kp->nama_kabupaten;
//                        $kpquery = $Lis_kp->id;
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dpro  = $this->db->query($sql_dpro);
//                    foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                    }
//                    $tahun_pro             = $categories_pro;
//                    $nilaiData['name'] = $xname;
//                    $nilaiData['data'] = $nilai_pro;
//                    array_push($catdata, $nilaiData);
//                        
//                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dkab  = $this->db->query($sql_dkab);
//                    foreach ($list_dkab->result() as $row_dkab) {
//                         $id_kab                     = $row_dkab->id;
//                         $categories_kab[]             = $row_dkab->tahun;
//                         $nilai_kab[]                = (float)$row_dkab->nilai;   
//                    }
//                    $tahun_kab             = $categories_kab;
//                    $nilaiData['name'] = $kab;
//                    $nilaiData['data'] = $nilai_kab;
//                    array_push($catdata, $nilaiData);
//                }
//                
//                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '8' ";
//                $list_thn = $this->db->query($thnmax);
//                foreach ($list_thn->result() as $Lis_thn){
//                    $thn=$Lis_thn->thn;
//                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
//                }
//                
//                $json_data = array(
//                    "text"       => $title_x,
//                    "text2"      => $title_y,
//                    "sumber"     => $datasumber,                    
//                    "categories" => $tahun, 
//                    "series"     => $catdata
//                );
//                exit(json_encode($json_data));
//            } catch (Exception $exc) {
//                $json_data = array(
//                    "text"           => "",
//                    "categories"     => "",
//                    "series"         => 0
//                );
//                exit(json_encode($json_data));
//            }
//        }
//        else
//            die;
//    }
//    
//    //Rata rata Lama Sekolah
//    function rata_lama_sekolah(){
//        if($this->input->is_ajax_request()){
//            try {
//                $requestData= $_REQUEST;
//                $this->form_validation->set_rules('provinsi','Provinsi','required');
//                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
//                $pro = $this->input->post("provinsi");
//                $kab = $this->input->post("kabupaten");
//               
//                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
//                $title_x           = "Perkembangan Rata-rata Lama Sekolah";
//                $title_y           = "Rata-rata Lama Sekolah (Tahun)";
//                
//                if($pro == '' & $kab ==''){
//                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    $catdata[]            = $nilaiData;
//                }
//                else if($pro != '' & $kab ==''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;  
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                        $list_dpro  = $this->db->query($sql_dpro);
//                        foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                        }
//                        $tahun_pro             = $categories_pro;
//                        $nilaiData['name'] = $xname;
//                        $nilaiData['data'] = $nilai_pro;
//                        array_push($catdata, $nilaiData);
//                }
//                else if($pro != '' & $kab !=''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;                        
//                    }
//                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
//                    $list_kp = $this->db->query($sql_kp);
//                    foreach ($list_kp->result() as $Lis_kp){
//                        $kpname = $Lis_kp->nama_kabupaten;
//                        $kpquery = $Lis_kp->id;
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dpro  = $this->db->query($sql_dpro);
//                    foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                    }
//                    $tahun_pro             = $categories_pro;
//                    $nilaiData['name'] = $xname;
//                    $nilaiData['data'] = $nilai_pro;
//                    array_push($catdata, $nilaiData);
//                        
//                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dkab  = $this->db->query($sql_dkab);
//                    foreach ($list_dkab->result() as $row_dkab) {
//                         $id_kab                     = $row_dkab->id;
//                         $categories_kab[]             = $row_dkab->tahun;
//                         $nilai_kab[]                = (float)$row_dkab->nilai;   
//                    }
//                    $tahun_kab             = $categories_kab;
//                    $nilaiData['name'] = $kab;
//                    $nilaiData['data'] = $nilai_kab;
//                    array_push($catdata, $nilaiData);
//                }
//                
//                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '9' ";
//                $list_thn = $this->db->query($thnmax);
//                foreach ($list_thn->result() as $Lis_thn){
//                    $thn=$Lis_thn->thn;
//                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
//                }
//                
//                $json_data = array(
//                    "text"       => $title_x,
//                    "text2"      => $title_y,
//                    "sumber"     => $datasumber,                    
//                    "categories" => $tahun, 
//                    "series"     => $catdata
//                );
//                exit(json_encode($json_data));
//            } catch (Exception $exc) {
//                $json_data = array(
//                    "text"           => "",
//                    "categories"     => "",
//                    "series"         => 0
//                );
//                exit(json_encode($json_data));
//            }
//        }
//        else
//            die;
//    }
//    
//    //harapan Lama Sekolah
//    function harapan_lama_sekolah(){
//        if($this->input->is_ajax_request()){
//            try {
//                $requestData= $_REQUEST;
//                $this->form_validation->set_rules('provinsi','Provinsi','required');
//                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
//                $pro = $this->input->post("provinsi");
//                $kab = $this->input->post("kabupaten");
//               
//                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
//                $title_x           = "Perkembangan Harapan Lama Sekolah";
//                $title_y           = "Harapan Lama Sekolah (Tahun)";
//                
//                if($pro == '' & $kab ==''){
//                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    $catdata[]            = $nilaiData;
//                }
//                else if($pro != '' & $kab ==''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;  
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                        $list_dpro  = $this->db->query($sql_dpro);
//                        foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                        }
//                        $tahun_pro             = $categories_pro;
//                        $nilaiData['name'] = $xname;
//                        $nilaiData['data'] = $nilai_pro;
//                        array_push($catdata, $nilaiData);
//                }
//                else if($pro != '' & $kab !=''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;                        
//                    }
//                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
//                    $list_kp = $this->db->query($sql_kp);
//                    foreach ($list_kp->result() as $Lis_kp){
//                        $kpname = $Lis_kp->nama_kabupaten;
//                        $kpquery = $Lis_kp->id;
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dpro  = $this->db->query($sql_dpro);
//                    foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                    }
//                    $tahun_pro             = $categories_pro;
//                    $nilaiData['name'] = $xname;
//                    $nilaiData['data'] = $nilai_pro;
//                    array_push($catdata, $nilaiData);
//                        
//                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dkab  = $this->db->query($sql_dkab);
//                    foreach ($list_dkab->result() as $row_dkab) {
//                         $id_kab                     = $row_dkab->id;
//                         $categories_kab[]             = $row_dkab->tahun;
//                         $nilai_kab[]                = (float)$row_dkab->nilai;   
//                    }
//                    $tahun_kab             = $categories_kab;
//                    $nilaiData['name'] = $kab;
//                    $nilaiData['data'] = $nilai_kab;
//                    array_push($catdata, $nilaiData);
//                }
//                
//                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '10' ";
//                $list_thn = $this->db->query($thnmax);
//                foreach ($list_thn->result() as $Lis_thn){
//                    $thn=$Lis_thn->thn;
//                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
//                }
//                
//                $json_data = array(
//                    "text"       => $title_x,
//                    "text2"      => $title_y,
//                    "sumber"     => $datasumber,                    
//                    "categories" => $tahun, 
//                    "series"     => $catdata
//                );
//                exit(json_encode($json_data));
//            } catch (Exception $exc) {
//                $json_data = array(
//                    "text"           => "",
//                    "categories"     => "",
//                    "series"         => 0
//                );
//                exit(json_encode($json_data));
//            }
//        }
//        else
//            die;
//    }    
//    
//    //pengeluaran_perkapita
//    function pengeluaran_perkapita(){
//        if($this->input->is_ajax_request()){
//            try {
//                $requestData= $_REQUEST;
//                $this->form_validation->set_rules('provinsi','Provinsi','required');
//                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
//                $pro = $this->input->post("provinsi");
//                $kab = $this->input->post("kabupaten");
//               
//                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
//                $title_x           = "Perkembangan Pengeluaran per Kapita";
//                $title_y           = "Pengeluaran per Kapita (Rp)";
//                
//                if($pro == '' & $kab ==''){
//                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    $catdata[]            = $nilaiData;
//                }
//                else if($pro != '' & $kab ==''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;  
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                        $list_dpro  = $this->db->query($sql_dpro);
//                        foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                        }
//                        $tahun_pro             = $categories_pro;
//                        $nilaiData['name'] = $xname;
//                        $nilaiData['data'] = $nilai_pro;
//                        array_push($catdata, $nilaiData);
//                }
//                else if($pro != '' & $kab !=''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;                        
//                    }
//                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
//                    $list_kp = $this->db->query($sql_kp);
//                    foreach ($list_kp->result() as $Lis_kp){
//                        $kpname = $Lis_kp->nama_kabupaten;
//                        $kpquery = $Lis_kp->id;
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dpro  = $this->db->query($sql_dpro);
//                    foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                    }
//                    $tahun_pro             = $categories_pro;
//                    $nilaiData['name'] = $xname;
//                    $nilaiData['data'] = $nilai_pro;
//                    array_push($catdata, $nilaiData);
//                        
//                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dkab  = $this->db->query($sql_dkab);
//                    foreach ($list_dkab->result() as $row_dkab) {
//                         $id_kab                     = $row_dkab->id;
//                         $categories_kab[]             = $row_dkab->tahun;
//                         $nilai_kab[]                = (float)$row_dkab->nilai;   
//                    }
//                    $tahun_kab             = $categories_kab;
//                    $nilaiData['name'] = $kab;
//                    $nilaiData['data'] = $nilai_kab;
//                    array_push($catdata, $nilaiData);
//                }
//                
//                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '11' ";
//                $list_thn = $this->db->query($thnmax);
//                foreach ($list_thn->result() as $Lis_thn){
//                    $thn=$Lis_thn->thn;
//                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
//                }
//                
//                $json_data = array(
//                    "text"       => $title_x,
//                    "text2"      => $title_y,
//                    "sumber"     => $datasumber,                    
//                    "categories" => $tahun, 
//                    "series"     => $catdata
//                );
//                exit(json_encode($json_data));
//            } catch (Exception $exc) {
//                $json_data = array(
//                    "text"           => "",
//                    "categories"     => "",
//                    "series"         => 0
//                );
//                exit(json_encode($json_data));
//            }
//        }
//        else
//            die;
//    }
    

    
//    //Indeks Kedalaman Kemiskinan
//    function kedalaman_kemiskinan(){
//        if($this->input->is_ajax_request()){
//            try {
//                $requestData= $_REQUEST;
//                $this->form_validation->set_rules('provinsi','Provinsi','required');
//                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
//                $pro = $this->input->post("provinsi");
//                $kab = $this->input->post("kabupaten");
//               
//                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
//                //$title_x           = "Perkembangan Indeks Kedalaman Kemiskinan";
//                $title_y           = "Indeks Kedalaman Kemiskinan";
//                $select_title="SELECT * FROM indikator where id='39'";
//                $list_title = $this->db->query($select_title);
//                foreach($list_title->result() as $lst){
//                    $title_x= $lst->nama_indikator;
//                }
//                if($pro == '' & $kab ==''){
//                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    $catdata[]            = $nilaiData;
//                }
//                else if($pro != '' & $kab ==''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;  
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             = $bulan[$row->periode]."-".$row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                        $list_dpro  = $this->db->query($sql_dpro);
//                        foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                        }
//                        $tahun_pro             = $categories_pro;
//                        $nilaiData['name'] = $xname;
//                        $nilaiData['data'] = $nilai_pro;
//                        array_push($catdata, $nilaiData);
//                }
//                else if($pro != '' & $kab !=''){
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;                        
//                    }
//                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='".$kab."' ";
//                    $list_kp = $this->db->query($sql_kp);
//                    foreach ($list_kp->result() as $Lis_kp){
//                        $kpname = $Lis_kp->nama_kabupaten;
//                        $kpquery = $Lis_kp->id;
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]             =$bulan[$row->periode]."-".$row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dpro  = $this->db->query($sql_dpro);
//                    foreach ($list_dpro->result() as $row_dpro) {
//                         $id_pro                     = $row_dpro->id;
//                         $categories_pro[]             = $row_dpro->tahun;
//                         $nilai_pro[]                = (float)$row_dpro->nilai;   
//                    }
//                    $tahun_pro             = $categories_pro;
//                    $nilaiData['name'] = $xname;
//                    $nilaiData['data'] = $nilai_pro;
//                    array_push($catdata, $nilaiData);
//                        
//                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dkab  = $this->db->query($sql_dkab);
//                    foreach ($list_dkab->result() as $row_dkab) {
//                         $id_kab                     = $row_dkab->id;
//                         $categories_kab[]             = $row_dkab->tahun;
//                         $nilai_kab[]                = (float)$row_dkab->nilai;   
//                    }
//                    $tahun_kab             = $categories_kab;
//                    $nilaiData['name'] = $kab;
//                    $nilaiData['data'] = $nilai_kab;
//                    array_push($catdata, $nilaiData);
//                }
//                
//                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '39' ";
//                $list_thn = $this->db->query($thnmax);
//                foreach ($list_thn->result() as $Lis_thn){
//                    $thn=$Lis_thn->thn;
//                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
//                }
//                
//                $json_data = array(
//                    "text"       => $title_x,
//                    "text2"      => $title_y,
//                    "sumber"     => $datasumber,                    
//                    "categories" => $tahun, 
//                    "series"     => $catdata
//                );
//                exit(json_encode($json_data));
//            } catch (Exception $exc) {
//                $json_data = array(
//                    "text"           => "",
//                    "categories"     => "",
//                    "series"         => 0
//                );
//                exit(json_encode($json_data));
//            }
//        }
//        else
//            die;
//    }
    
    

    


    
}
