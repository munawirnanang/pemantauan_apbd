<?php defined('BASEPATH') OR exit('No direct script access allowed');

class E_jumlah_pengangguran extends CI_Controller {
    var $view_dir   = "peppd1/evaluasi/jumlah_pengangguran/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/tingkat_kemiskinan/tingkat_kemiskinan.js";
    
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
                $this->js_path    = "assets/js/peppd1/evaluasi/jumlah_pengangguran/jumlah_pengangguran_".$this->session->userdata(SESSION_LOGIN)->groupid.".js?v=".now("Asia/Jakarta");
                
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
  
        //Jumlah Pengangguran
    function jumlah_pengangguran(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $this->form_validation->set_rules('s_rpjmn','RPJMN','required');
                
                $pro   = $this->input->post("provinsi");
                $rpjmn = $this->input->post("s_rpjmn");
                
                $bulan = array( '00' => '','01' => 'Januari','02' => 'Februari','03' => 'Maret','04' => 'April','05' => 'Mei','06' => 'Juni','07' => 'Juli','08' => 'Agustus','09' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
                
                $xname              ="";
                $query              ="";
                $tahun_tk_p_max     ="";
                $tahun_tk_p_2       = "";
                $catdata_pro        ='';
                $pe_perbandingan_pro='';
                $label_pe_k1='';
                $label_ppe1='';
                $catdata_kab='';
                $tahun_pe_kab='';
                $catdata_radar='';
                $pe_perbandingan_kabb='';
                $jp_perbandingan_2th='';
                $title_y    = "Jumlah Pengangguran (Orang)";
                $title      ="Perkembangan Jumlah Pengangguran";
                
                $d_peek="SELECT I.`deskripsi` FROM indikator I where id='4'";
                $list_peek = $this->db->query($d_peek);
                foreach ($list_peek->result() as $peek){$peek_d   = $peek->deskripsi;}
                $max_pe_p='';
                $pe_rkpd_rkp='';
                if($pro == '' && $rpjmn=='' ){
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
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
                else if($pro != '' && $rpjmn==''){
                    $catdata = array();
                    $sql_pro = "SELECT P.* FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        $label_pe = $Lis_pro->label;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bulan[$row->periode]." ".$row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        $categories1[]          = $bulan[$row->periode]." ".$row->tahun; //$thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='".$query."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='".$query."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";   
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
                        
                    if($nilai_pro[3] > $nilai_pro[5]){ $nu_p="menurun"; if($nilai[5]>$nilai_pro[5]){ $ba_p="di bawah"; }else{ $ba_p="di atas"; } } 
                    else { $nu_p="meningkat"; if($nilai[5]>$nilai_pro[5]){ $ba_p="di bawah"; }else{ $ba_p="di atas"; } }
                        
                    if($nilai_pro[5] > $nilai_rkpd1[5]){ $rkpdpe="di atas"; }
                    else{ $rkpdpe="di bawah"; }
                    if($nilai_pro[5] > $nilai_rkp1[5]){ $rkppe='di atas'; }
                    else{ $rkppe='di bawah'; }
                    $max_pe_p    ="Jumlah pengangguran ". $xname ." pada ".$categories[5]." ".$nu_p." dibandingkan ".$categories[3].". Pada ".$categories[5]." jumlah pengangguran ". $xname ." adalah sebesar ". number_format(end($nilai_pro),0,",",".") ." orang, sedangkan pada ".$categories[3]." jumlah pengangguran tercatat sebesar ".number_format($nilai_pro[3],0,",",".")." orang.";
                    
                    
                    $periode_pe_max=max($periode_pe);
                    $catdata_pro = array();
                    
                    $perbandingan_pe ="SELECT REF.id,REF.label AS label,REF.nama_provinsi, IND.id_periode,IND.periode,IFNULL(IND.nilai,0) nilai,IND.target,IND.t_m_rpjmn,IND.t_rkpd,IND.t_k_rkp, 
                    IFNULL(IND.sumber,'') sumber_k,IND.tahun 
                                FROM(
                                    SELECT `id`,`label`,`nama_provinsi` FROM `provinsi` 
                                    WHERE 1=1
                                    ORDER BY id  ASC
                                    ) REF
                                LEFT JOIN(
					SELECT p.label AS label,p.nama_provinsi, e.* 
					FROM provinsi p 
					JOIN nilai_indikator e ON p.id = e.wilayah 
					WHERE (e.id_indikator='4' AND e.id_periode='$periode_pe_max') 
						AND (wilayah, versi) IN (
							SELECT wilayah, MAX(versi) AS versi 
							FROM nilai_indikator  
							WHERE id_indikator='4' AND id_periode='$periode_pe_max' 
							GROUP BY wilayah ) 
					GROUP BY wilayah
                                ) IND	ON REF.id=IND.wilayah ORDER BY id  ASC";
                    $list_ppe_per = $this->db->query($perbandingan_pe);
                    foreach ($list_ppe_per->result() as $row_ppe_per) {
                        $label_ppe[]                               = $row_ppe_per->label;
                        $nilai_ppe_per[]                           = (float)$row_ppe_per->nilai;
                        $nilai_p_e_r1[$row_ppe_per->label]         = $row_ppe_per->nilai;
                        $nilai_p_e_r2[$row_ppe_per->nama_provinsi] = $row_ppe_per->nilai;
                    }                    
                    $label_ppe1          = $label_ppe;
                    $nilaiData_p['name'] = $categories1[5];
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
                    $tahun_tk_p_max = $categories1[5];
                    $tahun_tk_p_2 = $categories1[3];
                    $nilai_ppe_p_max = max($nilai_data_pe_r2);  //nila paling besar
                    $nilai_ppe_p_min = min($nilai_data_pe_r2);  //nila paling rendah
                    $nama_pe1=$nilai_data_pe_r2;
                    arsort($nama_pe1);
                    $nama_pe2=array_keys($nama_pe1);
                    $pe_perbandingan_pro = "Perbandingan jumlah pengangguran antar 34 provinsi menunjukkan bahwa jumlah pengangguran ". $xname ." 
                        pada ".$tahun_tk_p_max." berada pada urutan ke-".$rengking_pro.".
                           Provinsi dengan jumlah pengangguran tertinggi adalah ".array_shift($nama_pe2)." (".number_format($nilai_ppe_p_max,0,",",".")." orang),
                            sedangkan provinsi dengan jumlah pengangguran terendah adalah ".end($nama_pe2)." (".number_format($nilai_ppe_p_min,0,",",".")." orang). ";
                    
                    //radar
                    $catdata_radar = array();
                    $perbandingan_tk2 ="SELECT REF.id,REF.label AS label,REF.nama_provinsi, IND.id_periode,IND.periode,IFNULL(IND.nilai,0) nilai,IND.target,IND.t_m_rpjmn,IND.t_rkpd,IND.t_k_rkp, 
                                        IFNULL(IND.sumber,'') sumber_k,IND.tahun 
                                            FROM(
                                                SELECT `id`,`label`,`nama_provinsi` FROM `provinsi` 
                                                WHERE 1=1
                                                ORDER BY id  ASC
                                                ) REF
                                            LEFT JOIN(
                                SELECT p.label AS label,p.nama_provinsi, e.* 
                                FROM provinsi p 
                                JOIN nilai_indikator e ON p.id = e.wilayah 
                                WHERE (e.id_indikator='4' AND e.id_periode='$periode_pe[3]') 
                                    AND (wilayah, versi) IN (
                                        SELECT x.wilayah, MAX(x.versi) AS versi 
                                        FROM nilai_indikator x 
                                        WHERE id_indikator='4' AND id_periode='$periode_pe[3]' 
                                        GROUP BY wilayah ) 
                                GROUP BY wilayah
                                            ) IND	ON REF.id=IND.wilayah ORDER BY id  ASC";
                                            
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
                        $np_tk2[]        = $row_radar->nama_provinsi;
                    }                    

                    $label_tk2 = $label_ppe2;
                    $nilaiData_r['type'] = 'column';
                    $nilaiData_r['name'] = $tahun_tk_p_max;
                    $nilaiData_r['data'] = $nilai_ppe_per;
                    array_push($catdata_radar, $nilaiData_r);
                    
                    $nilaiData_r2['type'] = 'line';
                    $nilaiData_r['name'] = $tahun_tk_p_2;
                    $nilaiData_r['data'] = $nilai_ppe_per2;
                    array_push($catdata_radar, $nilaiData_r);
                      
                    $nilai_data_tk_r2 = $nilai_ppe_per2;
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
                    
                    $jp_perbandingan_2th = "Perbandingan jumlah pengangguran ".$categories_pro[3]." dengan ".$categories_pro[5]." menunjukkan bahwa 
                            provinsi yang mengalami penurunan jumlah jumlah pengangguran adalah ".$label_tk_per_p_min." (".number_format($nilai_tk_per_p_min,0,",",".")." orang). 
                            Dari sisi perubahan jumlah pengangguran periode ".$categories_pro[3]." hingga ".$categories_pro[5].", ". $xname ." berada pada urutan ke-".$rengkingtk_p.", dengan jumlah pengangguran ".$nu_p." sebesar ".number_format($nper_tk,0,",",".")." orang";
        
                    
                    //perbandingan kab
                    $catdata_kab = array();
                    $th_p_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$query."' AND e.id_indikator='4' ";
                    $t_list_kab_pe = $this->db->query($th_p_kab);
                    foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
                        $perio = $row_t_pe_kab->perio;
                    }
                    $ppe_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$query."' and (e.id_indikator='4' AND e.id_periode='$perio') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='4' AND id_periode='$perio' group by wilayah ) 
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
                        $tahun_p_k        = $bulan[$row_ppe_kab_per->periode]." ".$row_ppe_kab_per->tahun;
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
                   $tahun_pe_kab=$tahun_p_k." Antar Kabupaten/kota di ".$xname ;
                   $pe_perbandingan_kabb="Perbandingan jumlah pengangguran antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada tahun ".$tahun_p_k." daerah dengan jumlah pengangguran tertinggi adalah ".array_shift($nama_k2)." (".number_format($nilai_ppe_per_kab_max,0,",",".")." orang), sedangkan daerah dengan jumlah pengangguran terendah adalah ".end($nama_k2)." (".number_format($nilai_ppe_per_kab_min,0,",",".")." orang).
                                            Selisih jumlah pengangguran tertinggi dan terendah di ". $xname ." pada tahun ".$tahun_p_k." adalah sebesar ".number_format($selisih_pe,0,",",".")." orang.";
                   
                }
                else if($pro != '' && $rpjmn!=''){
                    $dari = $rpjmn;
                    $sampai = $rpjmn + 4;
                    $catdata = array();
                    $nsl = "select  max(id_periode) as periode_id from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' AND tahun BETWEEN '".$dari."' AND '".$sampai."' ";
                    $list_nsl  = $this->db->query($nsl);
                    foreach ($list_nsl->result() as $row_n) {
                        $periodeid = $row_n->periode_id;
                    }
                    /*
                    * =========================================================================
                    *                                                     - START
                    * =========================================================================
                    */
                    $sql_pro = "SELECT P.* FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        $label_pe = $Lis_pro->label;
                    }
                    /*
                    * =========================================================================
                    *    Nasional
                    * =========================================================================
                    */
                    $sql = "SELECT REF.id_periode,REF.periode,IFNULL(IND.nilai,0) nilai,IND.target,IND.t_m_rpjmn,IND.t_rkpd,IND.t_k_rkp, IFNULL(IND.sumber,'') sumber_k,REF.tahun 
                                FROM(
                                    SELECT id_periode,periode,tahun FROM `m_periode` 
                                    WHERE id_indikator='4' AND rpjmn='".$dari."'  
                                    ) REF
                                LEFT JOIN(
                                            SELECT id_periode,periode,nilai,target,t_m_rpjmn,t_rkpd,t_k_rkp,sumber,tahun 
                                            FROM nilai_indikator 
                                            WHERE (id_indikator='4' AND wilayah='1000')
                                                AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi 
                                                                FROM nilai_indikator 
                                                                WHERE id_indikator='4' AND wilayah='1000' GROUP BY id_periode
                                                ) 
                                            GROUP BY id_periode 
                                            ORDER BY id_periode DESC LIMIT 12
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $categories[]           = $bulan[$row->periode]." ".$row->tahun;
                        if($row->nilai==0 ){
                             $nilai1='';
                         }else{
                             $nilai1                = (float)$row->nilai;   
                         }
                         $nilai[]=$nilai1;
                        $periode                = $row->periode;
                        $categories1[]          = $bulan[$row->periode]." ".$row->tahun; //$thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    /*
                    * =========================================================================
                    *    Provinsi
                    * =========================================================================
                    */
                        $sql_dpro = "SELECT REF.id_periode,REF.periode,IFNULL(IND.nilai,0) nilai,IND.target,IND.t_m_rpjmn,IND.t_rkpd,IND.t_k_rkp, IFNULL(IND.sumber,'') sumber_k,REF.tahun 
                                FROM(
                                    SELECT id_periode,periode,tahun FROM `m_periode` 
                                    WHERE id_indikator='4' AND rpjmn='".$dari."'  
                                    ) REF
                                LEFT JOIN(
                                            SELECT id_periode,periode,nilai,target,t_m_rpjmn,t_rkpd,t_k_rkp,sumber,tahun 
                                            FROM nilai_indikator 
                                            WHERE (id_indikator='4' AND wilayah='".$query."')
                                                AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi 
                                                                FROM nilai_indikator 
                                                                WHERE id_indikator='4' AND wilayah='".$query."' GROUP BY id_periode
                                                ) 
                                            GROUP BY id_periode 
                                            ORDER BY id_periode DESC LIMIT 12
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dpro  = $this->db->query($sql_dpro);
                        foreach ($list_dpro->result() as $row_dpro) {
                         $categories_pro[]           = $row_dpro->tahun; 
                         if($row_dpro->nilai==0 ){
                             $nilai_pro1='';
                         }else{
                             $nilai_pro1                = (float)$row_dpro->nilai;   
                         }
                         $nilai_pro[]=$nilai_pro1;
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
                        

                        $max_pe_p    ="";
                        $pe_rkpd_rkp ="";
                    
                    
                }
                else if ($pro == '' && $rpjmn!='') {
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
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

                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '4' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
               
                $json_data = array(
                    "text"       => $title,
                    "ket"        => $peek_d,
                    "max_pe_p"   => $max_pe_p,
                    "tk_rkpd_rkp" => $pe_rkpd_rkp,
                    
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata,
                    
                    "text_pro"              => "Perbandingan Jumlah Pengangguran ".$tahun_tk_p_max." Antar Provinsi (orang)",
                    "categories_pro"        => $label_ppe1,
                    "series_pro"            => $catdata_pro,
                    "pe_perbandingan_pro"   => $pe_perbandingan_pro,
                    
                    "text_radar"        => "Perbandingan Jumlah Pengangguran ".$tahun_tk_p_2." Dengan ".$tahun_tk_p_max." (orang)",
                    "tahun_1"           => $tahun_tk_p_max,
                    "catdata_radar"     => $catdata_radar,
                    "perbandingan_2th"  => $jp_perbandingan_2th,
                    
                    "text_kab"          => "Perbandingan Jumlah Pengangguran ".$tahun_pe_kab." (orang)",
                    "categories_kab"    => $label_pe_k1,
                    "series_kab"        => $catdata_kab,
                    "tahun_pe_kab"      => $tahun_pe_kab,
                    "perbandingan_kab"  => $pe_perbandingan_kabb,
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

    

    
}
