<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Jp_miskin extends CI_Controller {
    var $view_dir   = "admin/jp_miskin/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/jp_miskin/jp_miskin.js";
    
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
               // print_r($current_date_time);exit();
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/admin/jp_miskin/jp_miskin_".$this->session->userdata(SESSION_LOGIN)->groupid.".js";
                
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
                    WHERE K.ppd = '1'";
                
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
    //peta
    function peta(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                //$this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                //$kab = $this->input->post("kabupaten");
                //print_r($pro);exit();
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
                $provinsi = explode("|", $this->input->post('provinsi'));
//                $string_provinsi = " P.`nama_provinsi` IN(";
                $count_pay = count($provinsi);
                $i=1;
                foreach ($provinsi as $provinsi_v):
                    $i++;
                endforeach;
                
                $xname      ="";
                $query      ="";
                $title_y    = "Pertumbuhan Ekonomi (%)";
                $title="Perkembangan Pertumbuhan Ekonomi tahun";
                $select_title="SELECT * FROM indikator where id='1'";
                $list_title = $this->db->query($select_title);
                foreach($list_title->result() as $lst){
                    //$title= $lst->nama_indikator;
                    
                }
                $d_peek="SELECT I.`deskripsi` FROM indikator I where id='1'";
                $list_peek = $this->db->query($d_peek);
                foreach ($list_peek->result() as $peek){$peek_d   = $peek->deskripsi;}
                $max_pe_p='';
                $pe_rkpd_rkp='';
                $label_ppe1='';
                    $catdata_pro='';
                    $pe_perbandingan_pro="";
                    //kab 
                    $label_pe_k1='';
                    $catdata_kab     ="";
                    $tahun_pe_kab = "";
                if($pro == '' ){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bulan[$row->periode]." ".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        $periode_pe[] = $row->id_periode;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                    
                    $periode_pe_max=max($periode_pe);
                    $perbandingan_pro ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='40' AND e.id_periode='$periode_pe_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='40' AND id_periode='$periode_pe_max' 
                            group by wilayah) group by wilayah order by wilayah asc";
                   $list_ppe_per = $this->db->query($perbandingan_pro);
                    foreach ($list_ppe_per->result() as $row_ppe_per) {
                        $nilai_pro[]                           = (float)$row_ppe_per->nilai;
                    }
                    $pp[populasion] =[$nilai_pro];
                    //print_r($pp);exit();
              //  $this->js_geo    = "assets/js/geojson/indonesia-prov.geojson";
                   $this->js_geo    = "assets/js/geojson/indonesia.geojson";
                    $js_zoom = 4;
                }
                else if($pro != ''){
                    $catdata = array();
                    $sql_pro = "SELECT P.* FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;
                        $label_pe = $Lis_pro->label;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
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
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
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
                    $max_pe_p    ="Pertumbuhan ekonomi ". $xname ." pada ".$categories[5]." berada ".$ba_p." nasional. Pertumbuhan ekonomi nasional pada ".$categories[5]." adalah sebesar ".$nilai[5] ."%. ";
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
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_ppe_per = $this->db->query($perbandingan_pe);
                    foreach ($list_ppe_per->result() as $row_ppe_per) {
                        $label_ppe[]                               = $row_ppe_per->label;
                        $nilai_ppe_per[]                           = (float)$row_ppe_per->nilai;
                        $nilai_p_e_r1[$row_ppe_per->label]         = $row_ppe_per->nilai;
                        $nilai_p_e_r2[$row_ppe_per->nama_provinsi] = $row_ppe_per->nilai;
                        $nilai_pro = (float)$row_ppe_per->nilai;
                    }                    
                    $label_ppe1          = $label_ppe;
                    $nilaiData_p['name'] = ' ';
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
                   $tahun_pe_kab=$tahun_p_k." Antar Kabupaten/kota di ".$judul ;
                   $pe_perbandingan_kabb="Perbandingan pertumbuhan ekonomi antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada tahun ".$tahun_p_k." daerah dengan tingkat pertumbuhan ekonomi tertinggi adalah ".array_shift($nama_k2)." (".$nilai_ppe_per_kab_max." %), sedangkan daerah dengan pertumbuhan ekonomi terendah adalah ".end($nama_k2)." (".$nilai_ppe_per_kab_min."%).
                                            Selisih pertumbuhan ekonomi tertinggi dan terendah di ". $xname ." pada tahun ".$tahun_p_k." adalah sebesar ".$selisih_pe."%.";
                   
                 $this->js_geo    = "assets/js/geojson/indonesia-".$query.".geojson";  
                 $js_zoom = 6;
                }

                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '1' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }                
               
                $json_data = array(
                    "js_geo"  => base_url($this->js_geo),
                    "js_zoom" => $js_zoom,
                   // "nilai_pro" => $nilai_pro,
                    //"pp" => $pp,
//                    "text"       => $title,
//                    "ket"        => $peek_d,
//                    "max_pe_p"   => $max_pe_p,
//                    "pe_rkpd_rkp" => $pe_rkpd_rkp,
//                    "text2"      => $title_y,
//                    "sumber"     => $datasumber,                    
//                    "categories" => $tahun, 
//                    "series"     => $catdata,
//                    
//                    "text_pro"       => "Perbandingan Pertumbuhan Ekonomi Antar Provinsi (%)",
//                    "categories_pro"=> $label_ppe1,
//                    "series_pro"     => $catdata_pro,
//                    "pe_perbandingan_pro" =>$pe_perbandingan_pro,
//                    
//                    "text_kab"       => "Perbandingan Pertumbuhan Ekonomi Antar Kab (%)",
//                    "categories_kab" => $label_pe_k1,
//                    "series_kab"     => $catdata_kab,
//                    "tahun_pe_kab"   => $tahun_pe_kab,
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
        function jumlah_p_miskin(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                //$this->form_validation->set_rules('kabupaten','Kabupaten','required');
                $pro = $this->input->post("provinsi");
                //$kab = $this->input->post("kabupaten");
                //print_r($pro);exit();
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
                $provinsi = explode("|", $this->input->post('provinsi'));
//                $string_provinsi = " P.`nama_provinsi` IN(";
                $count_pay = count($provinsi);
                $i=1;
                foreach ($provinsi as $provinsi_v):
                    $i++;
                endforeach;
                
                $xname      ="";
                $query      ="";
                $tahun_tk_p_max = "";
                $tahun_tk_p_2   = "";
                $title_y    = "Jumlah Penduduk Miskin (Orang)";
                $title="Perkembangan Jumlah Penduduk Miskin";
                $select_title="SELECT * FROM indikator where id='40'";
                $list_title = $this->db->query($select_title);
                foreach($list_title->result() as $lst){
                    //$title= $lst->nama_indikator;
                    
                }
                $d_peek="SELECT I.`deskripsi` FROM indikator I where id='40'";
                $list_peek = $this->db->query($d_peek);
                foreach ($list_peek->result() as $peek){$peek_d   = $peek->deskripsi;}
                $max_pe_p='';
                $pe_rkpd_rkp='';
                if($pro == '' ){
                    $judul="Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    //print_r($sql);exit();
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bulan[$row->periode]." ".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if($periode == '00'){ $thn=$row->tahun; //$thn2=$row->tahun; 
                        }
                        else{ $thn=  $bulan[$row->periode]." - ".$row->tahun;      
                        }
                        $categories1[]   = $bulan[$row->periode]." ".$row->tahun; //$thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                    $label_ppe1='';
                    $catdata_pro='';
                    $pe_perbandingan_pro="";
                    //radar
                    $catdata_radar='';
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
                        $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;
                        $label_pe = $Lis_pro->label;
                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]           = $bulan[$row->periode]." ".$row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                        $periode                = $row->periode;
//                        $categories1[]   = $bulan[$row->periode]." ".$row->tahun; //$thn;
//                    }
//                    $tahun             = $categories1;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
                    
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
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
                        
                    if($nilai_pro[3] > $nilai_pro[5]){                        
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
                    $max_pe_p    ="Tingkat kemiskinan ". $xname ." pada ".$categories[5]." ".$nu_p." dibandingkan ".$categories[3].". Pada ".$categories[5]." tingkat kemiskinan ". $xname ." adalah sebesar ". number_format(end($nilai_pro),2,",",".") ."%, sedangkan pada ".$categories[3]." tingkat kemiskinan tercatat sebesar ".number_format($nilai_pro[3],2,",",".")."%.";
                    $pe_rkpd_rkp ="Tingkat kemiskinan ". $xname ." pada ".$categories[5]." capaian nasional. Tingkat kemiskinan nasional pada ".$categories[5]." adalah sebesar target RKPD ". $xname ." (".$nilai_rkpd1[5]."%) dan ".$rkppe." target kewilayahan RKP (".$nilai_rkp1[5]."%).";
                    
                    $periode_pe_max=max($periode_pe);
                    $catdata_pro = array();
                    $perbandingan_pe ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='36' AND e.id_periode='$periode_pe_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='36' AND id_periode='$periode_pe_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_ppe_per = $this->db->query($perbandingan_pe);
                    foreach ($list_ppe_per->result() as $row_ppe_per) {
                        $label_ppe[]                               = $row_ppe_per->label;
                        $nilai_ppe_per[]                           = (float)$row_ppe_per->nilai;
                        $nilai_p_e_r1[$row_ppe_per->label]         = $row_ppe_per->nilai;
                        $nilai_p_e_r2[$row_ppe_per->nama_provinsi] = $row_ppe_per->nilai;
                    }                    
                    $label_ppe1          = $label_ppe;
                    $nilaiData_p['name'] = ' ';
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
                    $tahun_tk_p_max = $categories1[5];
                    $tahun_tk_p_2 = $categories1[3];
                    $nilai_ppe_p_max = max($nilai_data_pe_r2);  //nila paling besar
                    $nilai_ppe_p_min = min($nilai_data_pe_r2);  //nila paling rendah
                    $nama_pe1=$nilai_data_pe_r2;
                    arsort($nama_pe1);
                    $nama_pe2=array_keys($nama_pe1);
                    $pe_perbandingan_pro = "Perbandingan tingkat kemiskinan antar 34 provinsi menunjukkan bahwa tingkat kemiskinan ". $xname ." 
                        pada ".$tahun_tk_p_max." berada pada urutan ke-".$rengking_pro.".
                           Provinsi dengan tingkat kemiskinan tertinggi adalah ".array_shift($nama_pe2)." (".$nilai_ppe_p_max."%),
                            sedangkan provinsi dengan tingkat kemiskinan terendah adalah ".end($nama_pe2)." (".$nilai_ppe_p_min."%). ";
                    
                    //radar
                    $catdata_radar = array();
                    $perbandingan_tk2 ="select p.label as label, p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='36' AND e.id_periode='$periode_pe[3]') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='36' AND id_periode='$periode_pe[3]' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    // print_r($perbandingan_tk2);exit();
                    $list_radar = $this->db->query($perbandingan_tk2);
                    foreach ($list_radar->result() as $row_radar) {
                        $label_ppe2[]                               = $row_radar->label;
                        $nilai_tk_r2     = $row_radar->nilai;
                        if ($nilai_tk_r2 <= 0){
                            $nilai_tk2 = 0;
                        }else{
                            $nilai_tk2= $row_radar->nilai;
                        }
                        $nilai_ppe_per2[]                           = (float)$nilai_tk2;
                        //$nilai_p_e_r12[$row_radar->label]         = $row_radar->nilai;
                        //$nilai_p_e_r22[$row_radar->nama_provinsi] = $row_radar->nilai;
                    }                    
//                    $label_ppe1          = $label_ppe;
                    $label_tk2 = $label_ppe2;
                    $nilaiData_r['name'] = $tahun_tk_p_max;
                    $nilaiData_r['data'] = $nilai_ppe_per;
                    //$nilaiData_pro           = $nilaiData_p;
                    array_push($catdata_radar, $nilaiData_r);
                    
                    $nilaiData_r['name'] = $tahun_tk_p_2;
                    $nilaiData_r['data'] = $nilai_ppe_per2;
                    array_push($catdata_radar, $nilaiData_r);
                    //arsort($label_tk2);
                    $nilai_data_tk_r2 = $nilai_ppe_per2;
//                    $array_tiga = array();
//                    for($i=0;$i< count($nilai_data_tk_per);$i++){
//                        $array_tiga[$i]=$nilai_data_tk_per[$i]-$nilai_data_tk_r2[$i];
//                    }
//                    $kombinasi_tk=array_combine($label_tk2,$array_tiga);
//                //arsort($kombinasi_tk);
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
                   $tahun_pe_kab=$tahun_p_k." Antar Kabupaten/kota di ".$judul ;
                   $pe_perbandingan_kabb="Perbandingan pertumbuhan ekonomi antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada tahun ".$tahun_p_k." daerah dengan tingkat pertumbuhan ekonomi tertinggi adalah ".array_shift($nama_k2)." (".$nilai_ppe_per_kab_max." %), sedangkan daerah dengan pertumbuhan ekonomi terendah adalah ".end($nama_k2)." (".$nilai_ppe_per_kab_min."%).
                                            Selisih pertumbuhan ekonomi tertinggi dan terendah di ". $xname ." pada tahun ".$tahun_p_k." adalah sebesar ".$selisih_pe."%.";
                   
                   
                }

                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '36' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }                
               
                $json_data = array(
                    "text"       => $title,
                    "ket"        => $peek_d,
                    "max_pe_p"   => $max_pe_p,
                    "pe_rkpd_rkp" => $pe_rkpd_rkp,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata,
                    
                    "text_pro"       => "Perbandingan Tingkat Kemiskinan ".$tahun_tk_p_max." Antar Provinsi (%)",
                    "categories_pro"=> $label_ppe1,
                    "series_pro"     => $catdata_pro,
                    "pe_perbandingan_pro" =>$pe_perbandingan_pro,
                    
                    "text_radar"       => "Perbandingan Tingkat Kemiskinan ".$tahun_tk_p_max." Dengan ".$tahun_tk_p_2." (%)",
                    "tahun_1" => $tahun_tk_p_max,
                    "catdata_radar" => $catdata_radar,
                    
                    "text_kab"       => "Perbandingan Pertumbuhan Ekonomi Antar Kab (%)",
                    "categories_kab" => $label_pe_k1,
                    "series_kab"     => $catdata_kab,
                    "tahun_pe_kab"   => $tahun_pe_kab,
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
//    function tinkat_kemiskinan1(){
//        if($this->input->is_ajax_request()){
//            try {
//                $requestData= $_REQUEST;
//                $this->form_validation->set_rules('provinsi','Provinsi','required');
////                $this->form_validation->set_rules('kabupaten','Kabupaten','required');
//                $pro = $this->input->post("provinsi");
//  //              $kab = $this->input->post("kabupaten");
//               
//                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
//                $bulan_tahun = array( '00' => '','01' => 'Januari','02' => 'Februari','03' => 'Maret','04' => 'April','05' => 'Mei','06' => 'Juni','07' => 'Juli','08' => 'Agustus','09' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember',);
//                $title_x           = "Perkembangan Tingkat Kemiskinan";
//                $title_y           = "Tingkat Kemiskinan";
//                $d_peek="SELECT I.`deskripsi` FROM indikator I where id='36'";
//                $list_peek = $this->db->query($d_peek);
//                foreach ($list_peek->result() as $peek){$peek_tk   = $peek->deskripsi;}
//                $label_ppe1='';
//                $label_tk_r='';
//                $catdata_pro='';
//                $max_n_tk='';
//                $max_p_tk ='';
//                $tk_rkpd_rkp = '';
//                $label_pe_k1 = '';
//                $catdata_kab = '';
//                $tahun_pe_kab ='';
//                $tk_perbandingan_kabb = '';
//                
//                $tk_perbandingan_pro='';
//                
//                $catdata_pro1 = array();
//                $catdata_pro_r= array();
//                
//                if($pro == ''){
//                    $judul="";
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
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
//                else {
//                    $catdata = array();
//                    $sql_pro = "SELECT P.id, P.nama_provinsi, P.label FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
//                    $list_data = $this->db->query($sql_pro);
//                    foreach ($list_data->result() as $Lis_pro){
//                        $xname = $Lis_pro->nama_provinsi;
//                        $query = $Lis_pro->id;  
//                        $label_ = $Lis_pro->label;
//                    }
//                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_data  = $this->db->query($sql);
//                    foreach ($list_data->result() as $row) {
//                        $id                     = $row->id;
//                        $categories[]           = $bulan[$row->periode]."-".$row->tahun;
//                        $tk_tahun[]             = $bulan_tahun[$row->periode]." ".$row->tahun;
//                        $nilai[]                = (float)$row->nilai;
//                        $periode_tk_id[]        = $row->id_periode;
//                    }
//                    $tahun             = $categories;
//                    $nilaiData['name'] = "Indonesia";
//                    $nilaiData['data'] = $nilai;
//                    array_push($catdata, $nilaiData);
//                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
//                    $list_dpro  = $this->db->query($sql_dpro);
//                    foreach ($list_dpro->result() as $row_dpro) {
//                            $id_pro                     = $row_dpro->id;
//                            $categories_pro[]           = $bulan[$row_dpro->periode]."-".$row_dpro->tahun;
//                            $nilai_pro[]                = (float)$row_dpro->nilai;   
//                            if($row_dpro->t_m_rpjmn==0){
//                                $nilai_rpjmn='';
//                            }else{
//                                $nilai_rpjmn                = (float)$row_dpro->t_m_rpjmn;   
//                            }
//                            $nilai_rpjmn1[]            = $nilai_rpjmn;
//                            if($row_dpro->t_rkpd==0){
//                             $nilai_rkpd='';
//                            }else{
//                                $nilai_rkpd                = (float)$row_dpro->t_rkpd;   
//                            }
//                            $nilai_rkpd1[]                = $nilai_rkpd; 
//                            if($row_dpro->t_k_rkp==0){
//                                $nilai_rkp='';
//                            }else{
//                                $nilai_rkp                = (float)$row_dpro->t_k_rkp;   
//                            }
//                            $nilai_rkp1[]                = $nilai_rkp; 
//                            $periode_pe[]                = $row_dpro->id_periode;
//                         
//                        }
//                    $tahun_pro             = $categories_pro;
//                    $nilaiData['name'] = $xname;
//                    $nilaiData['data'] = $nilai_pro;
//                    array_push($catdata, $nilaiData);
//                        $nilaiData['name'] = "Target Makro RPJMN";
//                        $nilaiData['data'] = $nilai_rpjmn1;
//                        array_push($catdata, $nilaiData);
//                        $nilaiData['name'] = "Target RKPD";
//                        $nilaiData['data'] = $nilai_rkpd1;
//                        array_push($catdata, $nilaiData);
//                        $nilaiData['name'] = "Target Kewilayahan RKP";
//                        $nilaiData['data'] = $nilai_rkp1;
//                        array_push($catdata, $nilaiData);
//                        
//                        if($nilai_pro[3] > $nilai_pro[5]){
//                            $mm_tk="menurun";
//                            if($nilai[5]>$nilai_pro[5]){
//                                $ba_tk='di bawah';
//                                }else{
//                            $ba_tk='di atas';
//                        }
//                        } else {
//                        $mm_tk="meningkat";
//                        if($nilai[5]>$nilai_pro[5]){
//                            $ba_tk='di bawah';
//                        }else{
//                            $ba_tk='di atas';
//                        }
//                    }
//                        
//                    if($nilai_rkpd1[5]=='-'){ $tk_rkpd_rkp1 =""; }
//                    else{
//                        if($nilai_pro[5] > $nilai_rkpd1[5]){ $rkpdtk="di atas"; }
//                        else{ $rkpdtk="di bawah"; }
//                        if($nilai_pro[5] > $nilai_rkp1[5]){ $rkptk='di atas'; }
//                        else{ $rkptk='di bawah'; }
//                        $tk_rkpd_rkp1 = "Tingkat kemiskinan ". $xname ." periode ".$tk_tahun[5]." berada ".$rkpdtk." target RKPD ". $xname ." (".number_format($nilai_rkpd1[5],2)."%) dan ".$rkptk." target kewilayahan RKP (".number_format($nilai_rkp1[5],2)."%.) ";
//                    }    
//                        
//                        $max_n_tk    ="Tingkat kemiskinan ". $xname ." pada ".$tk_tahun[5]." ".$mm_tk." dibandingkan dengan ".$tk_tahun[3].". Pada ".$tk_tahun[5]." tingkat kemiskinan ". $xname ." adalah sebesar ". number_format(end($nilai_pro),2) ."%, sedangkan pada ".$tk_tahun[3]." tingkat kemiskinan tercatat sebesar ".number_format($nilai_pro[3],2)."%. ";
//                        $max_p_tk    ="Tingkat kemiskinan ". $xname ." pada ".$tk_tahun[5]." berada ".$ba_tk." capaian nasional. Tingkat kemiskinan nasional pada ".$tk_tahun[5]." adalah sebesar ".number_format(end($nilai),2) ."%. ";
//                        $tk_rkpd_rkp = $tk_rkpd_rkp1;
//                    
//                        $periode_pe_max=max($periode_pe);
//                        $catdata_pro  = array();
//                        
//                    $perbandingan_tk ="select p.label as label,p.nama_provinsi, e.* 
//                                    from provinsi p 
//                                    join nilai_indikator e on p.id = e.wilayah 
//                                    where (e.id_indikator='36' AND e.id_periode='$periode_pe_max') 
//                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
//							from nilai_indikator x  
//							where id_indikator='36' AND id_periode='$periode_pe_max' 
//                            group by wilayah) group by wilayah order by wilayah asc";
//                    $list_tk_per = $this->db->query($perbandingan_tk);
//                    foreach ($list_tk_per->result() as $row_tk_per) {
//                        $label_tk[]                               = $row_tk_per->label;
//                        $nilai_ppe_per[]                           = (float)$row_tk_per->nilai;
//                        $nilai_tk_r1[$row_tk_per->label]         = $row_tk_per->nilai;
//                        $nilai_tk_r2[$row_tk_per->nama_provinsi] = $row_tk_per->nilai;
//                    }                    
//                    $label_ppe1          = $label_tk;
//                    $nilaiData_p['name'] = "Provinsi";
//                    $nilaiData_p['data'] = $nilai_ppe_per;
//                    array_push($catdata_pro, $nilaiData_p);
//                    $nilaiData_p1['name'] = "Provinsi";
//                    $nilaiData_p1['data'] = $nilai_ppe_per;
//                    array_push($catdata_pro1, $nilaiData_p1);
//                    
//                    $nilai_data_tk_r1  = $nilai_tk_r1;
//                    $nilai_data_tk_r2  = $nilai_tk_r2;
//                    $rankingtk = $nilai_data_tk_r1;
//                    arsort($rankingtk);
//                    $nrtk=1;
//                    foreach($rankingtk as $xtk=>$xtk_value){
//                        if($xtk==$label_){
//                            $rengkingtk_pro=$nrtk++;
//                        }
//                        $urutan_pro_tk=$xtk . $xtk_value . $xtk_value .$nrtk++;
//                        
//                    }
//                    $nilai_tk_p_max = max($nilai_data_tk_r1);  //nila paling besar
//                    $nilai_tk_p_min = min($nilai_data_tk_r1);  //nila paling rendah
//                    $nama_tk1=$nilai_data_tk_r2;
//                    arsort($nama_tk1);
//                    $nama_tk2=array_keys($nama_tk1);
//                    $tk_perbandingan_pro = "Perbandingan tingkat kemiskinan antar 34 provinsi menunjukkan bahwa tingkat kemiskinan ". $xname ." "
//                            . "             pada ".max($tk_tahun)." berada pada urutan ke-".$rengkingtk_pro.".
//                           Provinsi dengan tingkat kemiskinan tertinggi adalah ".array_shift($nama_tk2)." (".$nilai_tk_p_max."%),
//                            sedangkan provinsi dengan tingkat kemiskinan terendah adalah ".end($nama_tk2)." (".$nilai_tk_p_min."%). ";
//                    
//                    //radar
//                        $perbandingan_tk2 ="select p.label as label, p.nama_provinsi, e.* 
//                                    from provinsi p 
//                                    join nilai_indikator e on p.id = e.wilayah 
//                                    where (e.id_indikator='36' AND e.id_periode='$periode_tk_id[3]') 
//                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
//							from nilai_indikator x  
//							where id_indikator='36' AND id_periode='$periode_tk_id[3]' 
//                            group by wilayah) group by wilayah order by wilayah asc";
//                        $list_tk_per2 = $this->db->query($perbandingan_tk2);
//                    foreach ($list_tk_per2->result() as $row_tk_per2) {
//                        $label_tk2[]     = $row_tk_per2->label;
//                        $np_tk2[]        = $row_tk_per2->nama_provinsi;
//                        //$nilai_tk_per2[] = $row_tk_per2->nilai;
//                        $nilai_tk_r2     = $row_tk_per2->nilai;
//                        if ($nilai_tk_r2 <= 0){
//                            $nilai_tk2 = 0;
//                        }else{
//                            $nilai_tk2= $row_tk_per2->nilai;
//                        }
//                        $nilai_tk_r22[]=$nilai_tk2;
//                    }
//                    $label_tk_r          = $label_tk2;
//                    $nilaiData_p2['name'] = $periode_tk_id[3];
//                    $nilaiData_p2['data'] = $nilai_tk_r22;
//                    array_push($catdata_pro_r, $nilaiData_p2);
//                    
//                    
//                    //perbandingan kab
//                    $catdata_kab = array();
//                    $th_p_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$query."' AND e.id_indikator='36' ";
//                    $t_list_kab_pe = $this->db->query($th_p_kab);
//                    foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
//                        $perio = $row_t_pe_kab->perio;
//                    }
//                    $ppe_kab="select p.nama_kabupaten as label, e.* 
//                            from kabupaten p
//                            join nilai_indikator e on p.id = e.wilayah 
//                            where p.prov_id='".$query."' and (e.id_indikator='36' AND e.id_periode='$perio') AND (wilayah, versi) in (
//                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='36' AND id_periode='$perio' group by wilayah ) 
//                           group by wilayah order by wilayah asc";
//                    
//                    $list_kab_ppe_per = $this->db->query($ppe_kab);
//                    foreach ($list_kab_ppe_per->result() as $row_ppe_kab_per) {
//                        $nilai_ppe_per_kab[] = $row_ppe_kab_per->nilai;
//                        $posisi_ppe          = strpos($row_ppe_kab_per->label, "Kabupaten");
//                        if ($posisi_ppe !== FALSE){
//                            $label_ppe11 = substr( $row_ppe_kab_per->label,0,3).". ".substr( $row_ppe_kab_per->label,10);
//                        }else{
//                            $label_ppe11 = $row_ppe_kab_per->label;
//                        }
//                        $label_pek1[]=$label_ppe11;
//                        $label_pe1_k[$label_ppe11]=$row_ppe_kab_per->nilai;
//                        $tahun_p_k        = $bulan[$row_ppe_kab_per->periode]."".$row_ppe_kab_per->tahun;
//                        $nilai_ppe_kab[]                           = (float)$row_ppe_kab_per->nilai;
//                    }
//                    //
//                    $label_pe_k1          = $label_pek1;
//                    $nilaiData_k['name'] = "kabupaten";
//                    $nilaiData_k['data'] = $nilai_ppe_kab;
//                    //$nilaiData_pro           = $nilaiData_p;
//                    array_push($catdata_kab, $nilaiData_k);
//                    $nilai_data_ppe_per_kab = $nilai_ppe_per_kab;
//                    //$nilai_ppe_per_kab = $nilai_ppe_per_kab;
//                    $nilai_ppe_per_kab_max = max($label_pe1_k);
//                    $nilai_ppe_per_kab_min = min($label_pe1_k);
//                    arsort($nilai_ppe_per_kab);
//                    $nama_k1=$label_pe1_k;
//                    arsort($nama_k1);
//                    $nama_k2=array_keys($nama_k1);
//                    $selisih_pe=$nilai_ppe_per_kab_max-$nilai_ppe_per_kab_min;
//                    
//                    $nrk=1;
//                    foreach($nilai_ppe_per_kab as $xk=>$xk_value){
//                        if($xk==$label_){
//                            $rengking_pro_k=$nrk++;
//                        }
//                        $urutan_pro_k=$xk . $xk_value . $xk_value .$nrk++;
//                        
//                    }
//                   $tahun_pe_kab        = $tahun_p_k." Antar Kabupaten/kota di " ;
//                   $tk_perbandingan_kabb= "Perbandingan tingkat kemiskinan antar kabupaten/kota di ". $xname ." memperlihatkan 
//                                            bahwa pada ".$tahun_p_k." daerah dengan tingkat kemiskinan tertinggi adalah ".array_shift($nama_k2)." (".$nilai_ppe_per_kab_max." %), sedangkan daerah dengan tingkat kemiskinan terendah adalah ".end($nama_k2)." (".$nilai_ppe_per_kab_min."%).
//                                            Selisih tingkat kemiskinan tertinggi dan terendah di ". $xname ." pada tahun ".$tahun_p_k." adalah sebesar ".$selisih_pe."%.";
//                    
//                }
//
//                
//                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '36' ";
//                $list_thn = $this->db->query($thnmax);
//                foreach ($list_thn->result() as $Lis_thn){
//                    $thn=$Lis_thn->thn;
//                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
//                }
//                
//                $json_data = array(
//                    
//                    "text"       => $title_x,
//                    "text2"      => $title_y,
//                    "sumber"     => $datasumber,                    
//                    "categories" => $tahun, 
//                    "series"     => $catdata,
//                    "ket_tk"        => $peek_tk,
//                    "max_n_tk"   => $max_n_tk,
//                    "max_p_tk"   => $max_p_tk,
//                    "tk_rkpd_rkp"   => $tk_rkpd_rkp,
//                        
//                    "text_pro"       => "Perbandingan Tingkat Kemiskinan Antar ".$xname." (%)",
//                    "categories_pro"=> $label_ppe1,
//                    "series_pro"     => $catdata_pro,
//                    "tk_perbandingan_pro"     => $tk_perbandingan_pro,
//                    
//                    "label_r" => $label_tk_r,
//                    "series_r"     => $catdata_pro1,
//                    //kab
//                    "text_kab"       => "Perbandingan Tingkat Kemiskinan Antar Kab/Kota ".$xname." (%)",
//                    "categories_kab"=> $label_pe_k1,
//                    "series_kab"     => $catdata_kab,
//                    "tahun_pe_kab"     => $tahun_pe_kab,
//                    "tk_perbandingan_kab"     => $tk_perbandingan_kabb
//                    
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
