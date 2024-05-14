<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_2 extends CI_Controller {
    var $view_dir   = "admin/laporan_2/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/laporan_2/laporan_2.js";
    var $picture    = "picture/laporan_ppd";
    
    function __construct() {
        parent::__construct();
        $this->load->model("M_Master","m_ref");
        // load core JpGraph as CI library
       //$this->load->library('jpgraph/jpgraph.php');
        $this->load->library('M_pdf');   
//        require_once (APPPATH.'/third_party/jpgraph/jpgraph.php');
//        require_once (APPPATH.'/third_party/jpgraph/jpgraph_bar.php');
//        require_once (APPPATH.'/third_party/jpgraph/jpgraph_line.php');
//        require_once (APPPATH.'/third_party/jpgraph/jpgraph_radar.php');
//        require_once (APPPATH.'/third_party/jpgraph/jpgraph_scatter.php');
//        require_once (APPPATH.'/third_party/jpgraph/jpgraph_ttf.inc.php');        
//        require_once (APPPATH.'/third_party/jpgraph/jpgraph_iconplot.php');
        //Load library pchart        
        require_once (APPPATH.'/third_party/jpgraph-4.3.1/src/jpgraph.php');
        require_once (APPPATH.'/third_party/jpgraph-4.3.1/src/jpgraph_bar.php');
        require_once (APPPATH.'/third_party/jpgraph-4.3.1/src/jpgraph_line.php');
        require_once (APPPATH.'/third_party/jpgraph-4.3.1/src/jpgraph_radar.php');
        require_once (APPPATH.'/third_party/jpgraph-4.3.1/src/jpgraph_scatter.php');
        require_once (APPPATH.'/third_party/jpgraph-4.3.1/src/jpgraph_ttf.inc.php');        
        require_once (APPPATH.'/third_party/jpgraph-4.3.1/src/jpgraph_iconplot.php');
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
                $this->js_path    = "assets/js/admin/laporan_2/laporan_2.js";
                $now=date('Y');
                $data_page = array(
                    "now" => $now
                );
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
        else{ exit("access denied!"); }
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
                    $tmp = " data-id='".$id."' ";
                    $grp = " data-gi='".$row->nama_provinsi."' ";
                    $nestedData[] = ""
                            . "<input type='radio' class='radio' name='group' $tmp $grp value='".$row->group_indikator."'  /> ";
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
                $idprov = decrypt_text($prov);
                //cari
                $idx = 0;
                $columns = array( 
                // datatable column index  => database column name
                        $idx++   =>"K.id", 
                        $idx++   =>"K.nama_kabupaten",

                );
                $sql = "SELECT K.`id`'kb', K.`nama_kabupaten`, K.`prov_id`, K.`ppd`, K.`urutan`, P.id, P.nama_provinsi 
                    FROM `kabupaten` K
                    LEFT JOIN provinsi P ON P.id = K.`prov_id` 
                        WHERE K.ppd = '1' ";
                
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
                    $id     = $row->kb;                    
                    $nestedData[] = $row->kb;
                    $nestedData[] = $row->nama_kabupaten ;
                    //$tmp = " data-id='".encrypt_text($id)."' ";
                    $tmp = " data-id='".$id."' ";
                    $kp = " data-kp='".$row->nama_kabupaten."' ";
                    $tpr = " data-idp='". encrypt_text($row->id)."'";
                    $tpr = " data-idp='". $row->id ."'";
                    $pr = " data-pr='".$row->nama_provinsi."' ";
                    $nestedData[] = ""
//                            . "<a class='btn btn-xs btn-info btnSelect' ".$tmp." title='Pilih Data'><i class='fa fa-hand-o-up'></i> Pilih</a>";
//                            . "<input type='checkbox' class='checkboxx' name='noso' value='".$row->nama_kabupaten."'  /> ";
                    . "<input type='radio' class='radio' name='group' $tmp $kp $pr $tpr value='".$row->nama_kabupaten."'  /> ";
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
    
    function download_act(){
        
               // if(!$this->session->userdata(SESSION_LOGIN)){ throw new Exception("Your session is ended, please relogin",2); }
                $provinsi  = $_GET['inp_pro']; $kabupaten = $_GET['inp_sp']; 
                //$tahun     = $_GET['tahun'];
                //$pro = decrypt_text($provinsi); $kab = decrypt_text($kabupaten);
                $pro = $provinsi;
                $kab = $kabupaten;
                
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $bulan_tahun = array( '00' => '','01' => 'Januari','02' => 'Februari','03' => 'Maret','04' => 'April','05' => 'Mei','06' => 'Juni','07' => 'Juli','08' => 'Agustus','09' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember',);
                $prde = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
                $prde2 = array('01' => 'triwulan I', '02' => 'triwulan II', '03' => 'triwulan III');
                $xname="";
                $query="";
                
                $d_peek="SELECT I.`deskripsi` FROM indikator I where id='1'";
                $list_peek = $this->db->query($d_peek);
                foreach ($list_peek->result() as $peek){$peek_d   = $peek->deskripsi;}
                $d_adhb="SELECT I.`deskripsi` FROM indikator I where id='2'";
                $list_adhb = $this->db->query($d_adhb);
                foreach ($list_adhb->result() as $adhb){ $adhb_d   = $adhb->deskripsi; }
                $d_adhk="SELECT I.`deskripsi` FROM indikator I where id='3'";
                $list_adhk = $this->db->query($d_adhk);
                foreach ($list_adhk->result() as $adhk){ $adhk_d   = $adhk->deskripsi; }
                $d_jp="SELECT I.`deskripsi` FROM indikator I where id='4'";
                $list_jp = $this->db->query($d_jp);
                foreach ($list_jp->result() as $jp){ $jp_d   = $jp->deskripsi; }
                $d_ipm="SELECT I.`deskripsi` FROM indikator I where id='5'";
                $list_ipm = $this->db->query($d_ipm);
                foreach ($list_ipm->result() as $ipm){ $ipm_d   = $ipm->deskripsi; }
                $d_tpt="SELECT I.`deskripsi` FROM indikator I where id='6'";
                $list_tpt = $this->db->query($d_tpt);
                foreach ($list_tpt->result() as $tpt){ $tpt_d   = $tpt->deskripsi; }
                $d_gr="SELECT I.`deskripsi` FROM indikator I where id='7'";
                $list_gr = $this->db->query($d_gr);
                foreach ($list_gr->result() as $gr){ $gr_d   = $gr->deskripsi; }
                $d_ahh="SELECT I.`deskripsi` FROM indikator I where id='8'";
                $list_ahh = $this->db->query($d_ahh);
                foreach ($list_ahh->result() as $ahh){ $ahh_d   = $ahh->deskripsi; }
                $d_rls="SELECT I.`deskripsi` FROM indikator I where id='9'";
                $list_rls = $this->db->query($d_rls);
                foreach ($list_rls->result() as $rls){ $rls_d   = $rls->deskripsi; }
                $d_hls="SELECT I.`deskripsi` FROM indikator I where id='10'";
                $list_hls = $this->db->query($d_hls);
                foreach ($list_hls->result() as $hls){ $hls_d   = $hls->deskripsi; }
                $d_pk="SELECT I.`deskripsi` FROM indikator I where id='11'";
                $list_pk = $this->db->query($d_pk);
                foreach ($list_pk->result() as $pk){ $pk_d   = $pk->deskripsi; }
                $d_tk="SELECT I.`deskripsi` FROM indikator I where id='36'";
                $list_tk = $this->db->query($d_tk);
                foreach ($list_tk->result() as $tk){ $tk_d   = $tk->deskripsi; }
                $d_ikk="SELECT I.`deskripsi` FROM indikator I where id='39'";
                $list_ikk = $this->db->query($d_ikk);
                foreach ($list_ikk->result() as $ikk){ $ikk_d   = $ikk->deskripsi; }
                $d_jpm="SELECT I.`deskripsi` FROM indikator I where id='40'";
                $list_jpm = $this->db->query($d_jpm);
                foreach ($list_jpm->result() as $jpm){ $jpm_d   = $jpm->deskripsi; }
                
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                $picture_1          = "halaman_1_".date("Y_m_d_H_i_s");
                $picture_2          = "halaman_2_".date("Y_m_d_H_i_s");
                
                $picture_pe         = "pertumbuhan_ekonomi_".date("Y_m_d_H_i_s");
                $picture_pe_bar     = "pertumbuhan_ekonomi_per".date("Y_m_d_H_i_s");
                $picture_rpe        = "radar_pertumbuhan_ekonomi_".date("Y_m_d_H_i_s");
                $picture_r_pe       = "radar_pe_".date("Y_m_d_H_i_s");
                
                $picture_s_pdrb     = "struktur_pdrb_".date("Y_m_d_H_i_s");
                $picture_sektoral   = "struktur_ektoral_".date("Y_m_d_H_i_s");
                $picture_ctc        = "struktur_ektoral_ctc".date("Y_m_d_H_i_s");
                
                $picture_tk         = "tingkat_kemiskinan_".date("Y_m_d_H_i_s");
                $picture_tk_bar     = "tk_per".date("Y_m_d_H_i_s");
                $picture_r_tk       = "tk_radar_".date("Y_m_d_H_i_s");
                $picture_k_tk       = "tk_k_".date("Y_m_d_H_i_s");
                
                $picture_tpt        = "tingkatpengangguranterbuka_".date("Y_m_d_H_i_s");
                $picture_tpt_bar    = "tpt_per".date("Y_m_d_H_i_s");
                $picture_tpt_r      = "tpt_radar".date("Y_m_d_H_i_s");
                $picture_tpt_k      = "tpt_kab".date("Y_m_d_H_i_s");
                
                $picture_jp         = "jp_".date("Y_m_d_H_i_s");
                $picture_jp_bar     = "jp_per".date("Y_m_d_H_i_s");
                $picture_jp_r       = "jp_radar".date("Y_m_d_H_i_s");
                $picture_jp_k       = "jp_kab".date("Y_m_d_H_i_s");
                
                $picture_ipm        = "indekspembangunanmanusia_".date("Y_m_d_H_i_s");
                $picture_ipm_bar    = "ipm_per".date("Y_m_d_H_i_s");
                $picture_ipm_r    = "ipm_radar".date("Y_m_d_H_i_s");
                $picture_ipm_k    = "ipm_kab".date("Y_m_d_H_i_s");
                
                $picture_gr         = "gini_rasio_".date("Y_m_d_H_i_s");
                $picture_gr_bar     = "gr_per".date("Y_m_d_H_i_s");
                $picture_gr_r       = "gr_radar".date("Y_m_d_H_i_s");
                $picture_gr_k       = "gr_kab".date("Y_m_d_H_i_s");
                                                
                $picture_jpk        = "jumlah_penduduk_miskin_".date("Y_m_d_H_i_s");
                $picture_jpk_bar    = "jpk_per".date("Y_m_d_H_i_s");
                $picture_jpk_r      = "jpk_radar".date("Y_m_d_H_i_s");
                $picture_jpk_k      = "jpk_kab".date("Y_m_d_H_i_s");
                
                if($provinsi == '' ){ $xname="Indonesia"; $query="1000";$judul="Indonesia";}
                elseif($provinsi != '' ) {
                    
                    $sql_pro = "SELECT P.id, P.nama_provinsi, P.label FROM provinsi P WHERE P.`id`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = "1000";
                        $id_pro = $Lis_pro->id;
                        $judul = $Lis_pro->nama_provinsi;
                        $label_pe = $Lis_pro->label;
                    }
                    $logopro      = $pro.".jpg";
                    
                    //Perkembangan Pertumbuhan Ekonomi (%)
                    $sql_ppe = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    
                    $list_ppe = $this->db->query($sql_ppe);
                    foreach ($list_ppe->result() as $row_ppe) {
                        $tahun[]   = $row_ppe->tahun;
                        $nilaiData1[] = (float)$row_ppe->nilai;
                        $nilaimax[] = number_format($row_ppe->nilai,2,",","."); 
                        
                    }
                    $max_pe = end($nilaiData1);
                    
                    $sql_ppe_pro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    
                    $list_ppe_pro = $this->db->query($sql_ppe_pro);
                    foreach ($list_ppe_pro->result() as $row_ppe_pro) {
                        $tahun1_pro[]   = $row_ppe_pro->tahun;
                        $nilaiData1_pro[] = (float)$row_ppe_pro->nilai;
                        $nilaimax_pro[]   = number_format($row_ppe_pro->nilai,2,",","."); 
                        $sumber_pe        = $row_ppe_pro->sumber;
                        $periode_pe[]     = $row_ppe_pro->id_periode;
                        $t_ppe            = $row_ppe_pro->t_m_rpjmn;
                        $periode=$row_ppe_pro->periode;
                        if($periode == '00'){ $thn=$row_ppe_pro->tahun; $thn2=$row_ppe_pro->tahun; }
                        else{ $thn=  $prde[$row_ppe_pro->periode]." - ".$row_ppe_pro->tahun;
                              $thn2=  $prde2[$row_ppe_pro->periode]." - ".$row_ppe_pro->tahun;      
                        }
                        $tahun1[]   = $thn;
                        $tahun1_t[]   = $thn2;
                        if($t_ppe==0){ $nilaiT = '-'; }
                        else{ $nilaiT = $row_ppe_pro->t_m_rpjmn; }
                        $nilaiTarget_RPJMN[] = $nilaiT;
                        
                        $t_p_rkpd = $row_ppe_pro->t_rkpd;
                        if($t_p_rkpd==0){ $nilaiRKPD = '-'; }
                        else{ $nilaiRKPD = $row_ppe_pro->t_rkpd; }
                        $nilaiTarget_RKPD[] = $nilaiRKPD;
                        
                        $t_p_rkp = $row_ppe_pro->t_k_rkp;
                        if($t_p_rkp==0){ $nilaiRKP = '-'; }
                        else{ $nilaiRKP = $row_ppe_pro->t_k_rkp; }
                        $nilaiTarget_RKP[] = $nilaiRKP;
                    }
                    $periode_pe_max=max($periode_pe);
                    $periode_pe_2=max($tahun1_pro)-2;
                    
                    $tahun_pe_g1     = $tahun1_t[0]." sampai ".$tahun1_t[5] ;
                    $tahun_pe_max    = $tahun1_t[5]." Antar Provinsi" ;
                    $tahun_pe_anatar = $tahun1_t[5]." Dengan Tahun ".$tahun1[4] ;                    
                    
                    if($nilaimax_pro[4] > $nilaimax_pro[5]){                        
                        $nu_p="menurun";
                        if($nilaimax[5]>$nilaimax_pro[5]){ $ba_p="di bawah";
                        }else{ $ba_p="di atas"; }
                    } else {
                        $nu_p="meningkat";
                        if($nilaimax[5]>$nilaimax_pro[5]){ $ba_p="di bawah";
                        }else{ $ba_p="di atas"; }
                    }
                    
                    if($nilaimax_pro[5] > $nilaiTarget_RKPD[5]){ $rkpdpe="di atas"; }
                    else{ $rkpdpe="di bawah"; }
                    if($nilaimax_pro[5] > $nilaiTarget_RKP[5]){ $rkppe='di atas'; }
                    else{ $rkppe='di bawah'; }
                    $max_pe      ="Pertumbuhan ekonomi ". $xname ." pada ".$tahun1_t[5]." ".$nu_p." dibandingkan dengan tahun ".$tahun1_t[4].". Pada ".$tahun1_t[5]." pertumbuhan ekonomi ". $xname ." adalah sebesar ". end($nilaimax_pro) ."%, sedangkan pada ".$tahun1_t[4]." pertumbuhannya tercatat sebesar ".$nilaimax_pro[4]."%. ";
                    $max_pe_p    ="Pertumbuhan ekonomi ". $xname ." pada ".$tahun1_t[5]." berada ".$ba_p." nasional. Pertumbuhan ekonomi nasional pada ".$tahun1_t[5]." adalah sebesar ". end($nilaimax) ."%. ";
                    $pe_rkpd_rkp ="Pertumbuhan ekonomi ". $xname ." pada ".$tahun1_t[5]." berada ".$rkpdpe." target RKPD ". $xname ." (".$nilaiTarget_RKPD[5]."%) dan ".$rkppe." target kewilayahan RKP (".$nilaiTarget_RKP[5]."%).";
                    $max_pe_k  =" ";
                    
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
                        $nilai_ppe_per[]                           = $row_ppe_per->nilai;
                        $nilai_p_e_r1[$row_ppe_per->label]         = $row_ppe_per->nilai;
                        $nilai_p_e_r2[$row_ppe_per->nama_provinsi] = $row_ppe_per->nilai;
                    }

                    $label_data_ppe     = $label_ppe;
                    $nilai_data_ppe_per = $nilai_ppe_per;
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
                                        
                    $pe_perbandingan_pro = "Perbandingan pertumbuhan ekonomi antar 34 provinsi menunjukkan bahwa pertumbuhan ekonomi ". $xname ." pada tahun ".max($tahun1_pro)." berada pada urutan ke-".$rengking_pro.".
                           Provinsi dengan tingkat pertumbuhan ekonomi tertinggi adalah ".array_shift($nama_pe2)." (".$nilai_ppe_p_max."%),
                            sedangkan provinsi dengan pertumbuhan ekonomi terendah adalah ".end($nama_pe2)." (".$nilai_ppe_p_min."%). ";

                   
                    //perbandingan kab
                    $th_p_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$id_pro."' AND e.id_indikator='1' ";
                    $t_list_kab_pe = $this->db->query($th_p_kab);
                    foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
                        $perio = $row_t_pe_kab->perio;
                    }
                    //$thn_pe_kab_max = max($perio);
                    $ppe_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$id_pro."' and (e.id_indikator='1' AND e.id_periode='$perio') AND (wilayah, versi) in (
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
                        $label_ppe1[]=$label_ppe11;
                        $label_pe1_k[$label_ppe11]=$row_ppe_kab_per->nilai;
                        //$thn_p_k[]=$row_ppe_kab_per->tahun;
                        $tahun_p_k        = $bulan[$row_ppe_kab_per->periode]."".$row_ppe_kab_per->tahun;
                    }
                    $label_data_ppe_kab     = $label_ppe1;
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
//                           echo "Key=" . $xk . ", Value=" . $xk_value . ", no=" . $nrk++;
//                          echo "<br>";
                        if($xk==$label_pe){
                            $rengking_pro_k=$nr++;
                        }
                        $urutan_pro_k=$xk . $xk_value . $xk_value .$nrk++;
                        
                    }

                   $tahun_pe_kab=$tahun_p_k." Antar Kabupaten/kota di ".$judul ;
                   $pe_perbandingan_kabb="Perbandingan pertumbuhan ekonomi antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada tahun ".$tahun_p_k." daerah dengan tingkat pertumbuhan ekonomi tertinggi adalah ".array_shift($nama_k2)." (".$nilai_ppe_per_kab_max." %), sedangkan daerah dengan pertumbuhan ekonomi terendah adalah ".end($nama_k2)." (".$nilai_ppe_per_kab_min."%).
                                            Selisih pertumbuhan ekonomi tertinggi dan terendah di ". $xname ." pada tahun ".$tahun_p_k." adalah sebesar ".$selisih_pe."%.";
                   
                     //perbandingan pertumbuhan ekonomi
                $graph_ppe       = new Graph(600,230);
                $graph_ppe->SetScale("textlin");
                $theme_class_ppe = new UniversalTheme;
                $graph_ppe->SetTheme($theme_class_ppe);
                $graph_ppe->SetMargin(40,10,33,58);
                $graph_ppe->SetBox(false);
                $graph_ppe->yaxis->HideZeroLabel();
                $graph_ppe->yaxis->HideLine(false);
                $graph_ppe->yaxis->HideTicks(false,false);
                $graph_ppe->xaxis->SetTickLabels($tahun1);
                $graph_ppe->ygrid->SetFill(false);
                    
                //perkembangan pertumbuhan ekonomi
                $p1_ppe = new LinePlot($nilaiData1);
                $graph_ppe->Add($p1_ppe);
                $p1_ppe->SetColor("#0000FF");
                $p1_ppe->SetLegend('Indonesia');
                $p1_ppe->SetCenter();
                $p1_ppe->mark->SetType(MARK_FILLEDCIRCLE,'',9.0);
                $p1_ppe->mark->SetSize(3);
                $p1_ppe->mark->SetColor('#0000FF');
                $p1_ppe->mark->SetFillColor('#0000FF');
                $p1_ppe->value->SetMargin(20);
//                $p1_ppe->value->SetColor('#0000FF');
                $p1_ppe->value->SetFormat('%0.2f');
                $p1_ppe->value->SetAlign('left','center');
                $p1_ppe->value->SetColor('#0000FF','darkred');
                $p1_ppe->value->Show();
                //$p1_ppe->value->SetFormat('%.1f mkr');
               // $p1_ppe->grid->SetColor('darkgrey');
                

                $p2_ppe = new LinePlot($nilaiData1_pro);
                $graph_ppe->Add($p2_ppe);
                $p2_ppe->SetColor("#000000");
                $p2_ppe->SetLegend($xname);
                $p2_ppe->SetCenter();
                $p2_ppe->SetStyle("dotted");
                $p2_ppe->mark->SetType(MARK_UTRIANGLE,9.0);
                $p2_ppe->mark->SetSize(14);
                $p2_ppe->mark->SetColor('#000000');
                $p2_ppe->mark->SetFillColor('#000000');
                $p2_ppe->value->SetColor('#000000');
                //$p2_ppe->value->SetMargin('left','center');
                $p2_ppe->value->SetMargin(20);
                $p2_ppe->value->SetFormat('%0.2f');
                $p2_ppe->value->SetAlign('right','center');
                $p2_ppe->value->SetColor('#000000','darkred');
                $p2_ppe->value->Show();
                
                
                $t_ppe = new LinePlot($nilaiTarget_RPJMN);
                $graph_ppe->Add($t_ppe);
                $t_ppe->SetColor("#FF0000");
                $t_ppe->SetCenter();
                $t_ppe->SetLegend('Target Makro RPJMN');
                $t_ppe->mark->SetType(MARK_FILLEDCIRCLE,'',9.0);
                $t_ppe->mark->SetSize(5);
                $t_ppe->mark->SetColor('#FF0000');
                $t_ppe->mark->SetFillColor('#FF0000');
                $t_ppe->value->SetMargin(20);
                $t_ppe->value->SetFormat('%0.2f');
                $t_ppe->value->SetAlign('left','center');
                $t_ppe->value->SetColor('#FF0000','darkred');
                $t_ppe->value->Show();
                //$t_ppe->value->SetMargin('left','center');
                
                $t_rkpd = new LinePlot($nilaiTarget_RKPD);
                $graph_ppe->Add($t_rkpd);
                $t_rkpd->SetColor("#006400");
                $t_rkpd->SetLegend('Target RKPD');
                $t_rkpd->SetCenter();
                $t_rkpd->mark->SetType(MARK_STAR,'',9.0);
                $t_rkpd->mark->SetSize(10);
                $t_rkpd->mark->SetColor('#006400');
                $t_rkpd->mark->SetFillColor('#006400');
                $t_rkpd->value->SetColor('#006400');
                $t_rkpd->value->SetMargin(14);
                $t_rkpd->value->SetFormat('%0.2f');
                $t_rkpd->value->SetAlign('right','center');
                $t_rkpd->value->Show();
                
                $t_rkp = new LinePlot($nilaiTarget_RKP);
                $graph_ppe->Add($t_rkp);
                $t_rkp->SetColor("#A9A9A9");
                $t_rkp->SetLegend('Target Kewilayahan RKP');
                $t_rkp->SetCenter();
                $t_rkp->mark->SetType(MARK_SQUARE,'',9.0);
                $t_rkp->mark->SetSize(10);
                $t_rkp->mark->SetColor('#A9A9A9');
                $t_rkp->mark->SetFillColor('#A9A9A9');
                $t_rkp->value->SetMargin(20);
                $t_rkp->value->SetColor('#A9A9A9');
                $t_rkp->value->SetFormat('%0.2f');
                $t_rkp->value->SetAlign('left','center');
                $t_rkp->value->Show();                
               // $p1_ppe->grid->SetColor('darkgrey');
                
//                $graph_ppe->legend->SetFrameWeight(2);
//                $graph_ppe->legend->SetColor('#4E4E4E','#00A78A');
//                $graph_ppe->legend->SetMarkAbsSize(8);
                $graph_ppe->legend->SetFrameWeight(5);
                $graph_ppe->legend->SetColor('#4E4E4E','#00A78A');
                $graph_ppe->legend->SetMarkAbsSize(8);
                
                //bar antar provinsi
                $graph_bar_ppe = new Graph(600,190);
                $graph_bar_ppe->img->SetMargin(40,20,20,100);
                $graph_bar_ppe->SetScale("textlin");                
                $graph_bar_ppe->SetMarginColor("40,0.1,0.1,150");
                $graph_bar_ppe->SetShadow();
                
                $graph_bar_ppe->title->SetMargin(8);
                $graph_bar_ppe->title->SetColor("darkred");
                // Show 0 label on Y-axis (default is not to show)
                $graph_bar_ppe->yscale->ticks->SupressZeroLabel(false);
                
                $graph_bar_ppe->ygrid->SetFill(false);
                $graph_bar_ppe->xaxis->SetTickLabels($label_data_ppe);
                $graph_bar_ppe->yaxis->HideLine(false);
                $graph_bar_ppe->yaxis->HideTicks(false,false);                
                $graph_bar_ppe->xaxis->SetLabelAngle(90);
                // Set X-axis at the minimum value of Y-axis (default will be at 0)
                $graph_bar_ppe->xaxis->SetPos("min"); 
                
                $b1plot_ppe_per = new BarPlot($nilai_data_ppe_per);
                $b1plot_ppe_per->SetColor("white");
                $b1plot_ppe_per->SetFillColor("#0000FF");
                $gbplot_ppe_per = new GroupBarPlot(array($b1plot_ppe_per));
                $graph_bar_ppe->Add($gbplot_ppe_per);
                $graph_bar_ppe->Stroke($this->picture.'/'.$picture_pe_bar.'.png');
                
                
                //kab
                $graph_bar_kab = new Graph(600,290);
                $graph_bar_kab->SetScale("textlin");
                $graph_bar_kab->SetY2Scale("lin",0,90);
                //$graph_bar_kab->graph_theme = null;
                //$graph_bar_kab->SetFrame(false);
                // Add a drop shadow
                $graph_bar_kab->SetShadow();
                //$graph_bar_kab->SetY2OrderBack(false);
                $theme_class_bar_kab=new UniversalTheme;
                $graph_bar_kab->SetTheme($theme_class_bar_kab);
                $graph_bar_kab->SetMargin(40,20,20,150);
                $graph_bar_kab->ygrid->SetFill(false);
                $graph_bar_kab->xaxis->SetTickLabels($label_data_ppe_kab);
                $graph_bar_kab->xaxis->SetLabelAngle(90);
                $graph_bar_kab->yaxis->HideLine(false);
                $graph_bar_kab->yaxis->HideTicks(false,false);
                
                
                $b1plot_ppe_kab = new BarPlot($nilai_data_ppe_per_kab);
                $b1plot_ppe_kab->SetColor("white");
                $b1plot_ppe_kab->SetFillColor("#0000FF");  
                //$b1plot_ppe_kab->value->SetMargin(20);
                //$b1plot_ppe_kab->value->SetFormat('%0.2f');
                //$b1plot_ppe_kab->value->SetAlign('left','center');
                //$b1plot_ppe_kab->value->SetColor('#FF0000','darkred');
                $b1plot_ppe_kab->value->Show();
                $gbplot_kab_per = new GroupBarPlot(array($b1plot_ppe_kab));
                
                $graph_bar_kab->Add($gbplot_kab_per);
               // 
//$gbplot_kab_per->value->SetColor("black","darkred"); 
//$gbplot_kab_per->value->SetFormat('%01.2f');  
                                


                //PERKEMBANGAN PERTUMBUHAN EKONOMI
                $graph_ppe->Stroke($this->picture.'/'.$picture_pe.'.png');
                
                $graph_bar_kab->Stroke($this->picture.'/'.$picture_rpe.'.png');
                //$graph_r_pe->Stroke($this->picture.'/'.$picture_r_pe.'.png');
                
                
                //Struktur PDRB
               $select_pdrb="SELECT R.id,R.nama_indikator,IFNULL(IND.nilai,0) nilai_struktur,IFNULL(IND.target,0) nilai_pertumbuhan  
                             FROM (SELECT IK.id,IK.nama_indikator 
                                    FROM `indikator` IK 
                                    WHERE IK.group_id='6' )R 
                            LEFT JOIN ( select id_indikator, id_periode,nilai,target  
                                        from nilai_indikator 
                                        where (wilayah='".$id_pro."') 
                                             AND (id_indikator, id_periode, versi) IN( 
                                                    select id_indikator,id_periode, max(versi) as versi 
                                                    from nilai_indikator 
                                                    WHERE wilayah='".$id_pro."' group by id_indikator ) 
                                       )IND ON R.id=IND.id_indikator "; 
             
                $list_s_p = $this->db->query($select_pdrb);
                foreach ($list_s_p->result() as $row_s_p) {
                     $indikator_sp[]= $row_s_p->nama_indikator;
                     $nilai_s[]     = (float)$row_s_p->nilai_struktur;
                     $nilai_p[]     = $row_s_p->nilai_pertumbuhan;
                     
                     $label_sektor[$row_s_p->nama_indikator]=(float)$row_s_p->nilai_struktur;
                     $label_pertumbuhan[$row_s_p->nama_indikator]=(float)$row_s_p->nilai_pertumbuhan;
                     //
                     $nilai_[]=(float)$row_s_p->nilai_struktur;
                }
                $nilai_max_s = max($nilai_s);
                $nilai_max_p = max($nilai_p);                
//                $nilai_ppe_per_kab_max = max($label_sektor);
//                $nilai_ppe_per_kab_min = min($label_sektor);
                   
                arsort($nilai_s);
                $nrk=1;
                    foreach($nilai_s as $xk=>$xk_value){
                        $nok=$nrk++;
                       //    echo "Key=" . $xk . ", Value=" . $xk_value. ", nomor=" . $nok;
                       //   echo "<br>";
                        if($nok=='1'){$rengking_satu=$xk_value;}
                        if($nok=='2'){$rengking_dua =$xk_value;}
                        if($nok=='3'){$rengking_tiga=$xk_value;}                        
                    }
                $nama_s1=$label_sektor;
                arsort($nama_s1);
//                echo '</Br>';
//                print_r($nama_s1);
//                echo '</Br>';
                $nama_s2=array_keys($nama_s1);
                //arsort($nilai_s);
                    foreach($nilai_s as $xk=>$xk_value){
                        $nok=$nrk++;
                       //    echo "Key=" . $xk . ", Value=" . $xk_value. ", nomor=" . $nok;
                       //   echo "<br>";
                        if($nok=='1'){$rengking_satu=$xk_value;}
                        if($nok=='2'){$rengking_dua =$xk_value;}
                        if($nok=='3'){$rengking_tiga=$xk_value;}                        
                    }
                arsort($nilai_p);
                $nrp=1;
                foreach($nilai_p as $xkp=>$xkp_value){
                    $nop=$nrp++;
                    if($nop=='1'){$rengking_satu_p=$xkp_value;}
                    if($nop=='2'){$rengking_dua_p =$xkp_value;}
                    if($nop=='3'){$rengking_tiga_p=$xkp_value;}
                }
                
                $nama_p1=$label_pertumbuhan;
                arsort($nama_p1);
                $nama_p2=array_keys($nama_p1);
//                echo '</Br>';
//                print_r($nama_p1);
//                echo '</Br>';
//                print_r($nama_p2);
//                echo '</Br>';echo '</Br>';
                
                $nama_p3=$label_pertumbuhan;
                asort($nama_p3);
//                print_r($nama_p3);
//                echo '</Br>';echo '</Br>';
                $nrps=1;
                foreach($nama_p3 as $xkps=>$xkps_value){
                    $nops=$nrps++;
                    if($nops=='1'){$rengking_satu_ts=$xkps_value;}
                    if($nops=='2'){$rengking_dua_ts =$xkps_value;}
                    if($nops=='3'){$rengking_tiga_ts=$xkps_value;}
                }
                $nama_p4=array_keys($nama_p3);
                $pdrb_d="Tiga sektor yang paling dominan dalam struktur perekonomian ". $xname ." adalah sektor "
                        . "".$nama_s2[0]." dengan kontribusi sebesar ".$rengking_satu." persen, diikuti oleh sektor ".$nama_s2[1]." dengan kontribusi sebesar ".$rengking_dua." persen, kemudian sektor ".$nama_s2[2]." dengan kontribusi sebesar ".$rengking_tiga." persen. "
                        . "Sedangkan tiga sektor yang memiliki pertumbuhan paling tinggi adalah sektor ".$nama_p2[0]." "
                        . "dengan pertumbuhan sebesar ".$rengking_satu_p." persen, "
                        . "diikuti oleh sektor ".$nama_p2[1]." dengan pertumbuhan sebesar ".$rengking_dua_p." persen, "
                        . "serta sektor ".$nama_p2[2]." dengan pertumbuhan sebesar ".$rengking_tiga_p." persen. "
                        . "Di sisi lain tiga sektor yang mengalami pertumbuhan terendah adalah "
                        . "sektor ".$nama_p4[0]." dengan pertumbuhan sebesar ".$rengking_satu_ts." persen, "
                        . "diikuti oleh sektor ".$nama_p4[1]." dengan pertumbuhan sebesar ".$rengking_dua_ts." persen, "
                        . "serta sektor ".$nama_p4[2]." dengan pertumbuhan sebesar ".$rengking_tiga_ts." persen.	";
//                print_r($pdrb_d);exit();
                
                //$datay_s_pdrb=array(17,22,33,48,24,-5.51,0,0,0,0,0,0,0,0,0,0,0,0);
                // Create the graph. These two calls are always required
//$graph_s_pdrb = new Graph(1200,500,'auto');
//$graph_s_pdrb->SetScale("textlin");
//
//$theme_class=new UniversalTheme;
//$graph_s_pdrb->SetTheme($theme_class);
//
//$graph_s_pdrb->Set90AndMargin(320,40,40,40);
//$graph_s_pdrb->img->SetAngle(90); 
//// set major and minor tick positions manually
//$graph_s_pdrb->SetBox(false);
//                
//                //$graph->ygrid->SetColor('gray');
//                $graph_s_pdrb->ygrid->Show(false);
//                $graph_s_pdrb->ygrid->SetFill(false);
//                $graph_s_pdrb->xaxis->SetTickLabels(array('1.Pertanian, Kehutanan, dan Perikanan','2.Pertambangan dan Penggalian','3.Industri Pengolahan','4.Pengadaan Listrik dan Gas','5.Pengadaan Air, Pengelolaan Sampah, Limbah dan Daur Ulang','6.Konstruksi','7.Perdagangan Besar dan Eceran; Reparasi Mobil 
//                    dan Sepeda Motor','8.Transportasi dan Pergudangan','9.Penyediaan Akomodasi dan Makan Minum','10.Informasi dan Komunikasi','11.Jasa Keuangan dan Asuransi','12.Real Estat','13.Jasa Perusahaan','14.Administrasi Pemerintahan, Pertahanan dan 
//Jaminan Sosial Wajib','15.Jasa Pendidikan','16.Jasa Kesehatan dan Kegiatan Sosial','17.Jasa Lainnya','18.Produk Domestik Regional Bruto dengan migas'));
//                // Label align for X-axis
//                $graph_s_pdrb->xaxis->SetLabelAlign('right','center','right');
//                
//                $graph_s_pdrb->yaxis->HideLine(false);
//                $graph_s_pdrb->yaxis->HideTicks(false,false);
//
//// For background to be gradient, setfill is needed first.
//               // $graph_s_pdrb->SetBackgroundGradient('', '#00FFFF', GRAD_HOR, BGRAD_PLOT);
//
//                // Create the bar plots
//                $b1plot_spdrb = new BarPlot($datay_s_pdrb);
//
//                // ...and add it to the graPH
//                $graph_s_pdrb->Add($b1plot_spdrb);
//
//                $b1plot_spdrb->SetWeight(0);
//                $b1plot_spdrb->SetFillGradient("#808000","#90EE90",GRAD_HOR);
//                $b1plot_spdrb->SetWidth(17);
//
//                // Display the graph
//                $graph_s_pdrb->Stroke($this->picture.'/'.$picture_s_pdrb.'.png');
                //$datay_s_pdrb=array(10,20,13,48,24,-5.51,0,0,0,0,0,0,0,8,0,0,0,0);
//$datax_s_pdrb=array("1.Pertanian, Kehutanan, dan Perikanan","2.Pertambangan dan Penggalian","3.Industri Pengolahan",'4.Pengadaan Listrik dan Gas','5.Pengadaan Air, Pengelolaan Sampah, '
//    . 'Limbah dan Daur Ulang','6.Konstruksi','7.Perdagangan Besar dan Eceran; Reparasi Mobil 
//                    dan Sepeda Motor','8.Transportasi dan Pergudangan','9.Penyediaan Akomodasi dan Makan Minum','10.Informasi dan Komunikasi','11.Jasa Keuangan dan Asuransi','12.Real Estat','13.Jasa Perusahaan','14.Administrasi Pemerintahan, Pertahanan dan 
//Jaminan Sosial Wajib','15.Jasa Pendidikan','16.Jasa Kesehatan dan Kegiatan Sosial','17.Jasa Lainnya','18.Produk Domestik Regional Bruto dengan migas');

// Size of graph
$datay_s_pdrb=$nilai_s;
$datax_s_pdrb=$indikator_sp;
$width_s_pdrb=1200; 
$height_s_pdrb=700;
// Set the basic parameters of the graph 
$graph_s_pdrb = new Graph($width_s_pdrb,$height_s_pdrb,'auto');
$graph_s_pdrb->SetScale("textlin");
$graph_s_pdrb->graph_theme = null;
                $graph_s_pdrb->SetFrame(false);
$top_s_pdrb = 10;
$bottom_s_pdrb = 80;
$left_s_pdrb = 400;
$right_s_pdrb = 10;
$graph_s_pdrb->Set90AndMargin($left_s_pdrb,$right_s_pdrb,$top_s_pdrb,$bottom_s_pdrb);
$graph_s_pdrb->xaxis->SetPos('min');
// Nice shadow
$graph_s_pdrb->SetShadow();
// Setup title
//$graph->title->Set("Horizontal bar graph ex 3");
//$rgaph->title->SetFont(FF_VERDANA,FS_BOLD,14);
//$graph->subtitle->Set("(Axis at bottom)");
// Setup X-axis
$graph_s_pdrb->xaxis->SetTickLabels($datax_s_pdrb);
//$graph->xaxis->SetFont(FF_FONT2,FS_BOLD,12);
// Some extra margin looks nicer
$graph_s_pdrb->xaxis->SetLabelMargin(5);
// Label align for X-axis
$graph_s_pdrb->xaxis->SetLabelAlign('right','center');
// Add some grace to y-axis so the bars doesn't go
// all the way to the end of the plot area
$graph_s_pdrb->yaxis->scale->SetGrace(20);
// Setup the Y-axis to be displayed in the bottom of the 
// graph. We also finetune the exact layout of the title,
// ticks and labels to make them look nice.
$graph_s_pdrb->yaxis->SetPos('max');
// First make the labels look right
$graph_s_pdrb->yaxis->SetLabelAlign('center','top');
$graph_s_pdrb->yaxis->SetLabelFormat('%d');
$graph_s_pdrb->yaxis->SetLabelSide(SIDE_RIGHT);
// The fix the tick marks
$graph_s_pdrb->yaxis->SetTickSide(SIDE_LEFT);
// Finally setup the title
$graph_s_pdrb->yaxis->SetTitleSide(SIDE_RIGHT);
$graph_s_pdrb->yaxis->SetTitleMargin(35);
// To align the title to the right use :
$graph_s_pdrb->yaxis->SetTitle('Pertumbuhan PDRB Sektoral TW I','high');
$graph_s_pdrb->yaxis->title->Align('center');
// To center the title use :
//$graph->yaxis->SetTitle('Turnaround 2002','center');
//$graph->yaxis->title->Align('center');
//$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,12);
$graph_s_pdrb->yaxis->title->SetAngle(0);
//$graph->yaxis->SetFont(FF_FONT2,FS_NORMAL);
// If you want the labels at an angle other than 0 or 90
// you need to use TTF fonts
//$graph->yaxis->SetLabelAngle(0);
// Now create a bar pot
$bplot_s_pdrb = new BarPlot($datay_s_pdrb);
$bplot_s_pdrb->SetFillColor("orange");
$bplot_s_pdrb->SetShadow();
//You can change the width of the bars if you like
//$bplot->SetWidth(0.5);
// We want to display the value of each bar at the top
$bplot_s_pdrb->value->Show();
//$bplot->value->SetFont(FF_ARIAL,FS_BOLD,12);
$bplot_s_pdrb->value->SetAlign('left','center');
$bplot_s_pdrb->value->SetColor("black","darkred");
$bplot_s_pdrb->value->SetFormat('%.2f ');
// Add the bar to the graph
$graph_s_pdrb->Add($bplot_s_pdrb);
$graph_s_pdrb->Stroke($this->picture.'/'.$picture_s_pdrb.'.png');
                
                //Struktur Sektoral
//                $datay_sektoral=array(17,22,33,48,24,-5.51,0,0,0,0,0,0,0,0,0,0,0,0);
//                // Create the graph. These two calls are always required
//                $graph_sektoral = new Graph(1200,500,'auto');
//                $graph_sektoral->SetScale("textlin");
//
//                $theme_class=new UniversalTheme;
//                $graph_sektoral->SetTheme($theme_class);
//
//                $graph_sektoral->Set90AndMargin(320,40,40,40);
//                $graph_sektoral->img->SetAngle(90); 
//                // set major and minor tick positions manually
//                $graph_sektoral->SetBox(false);
//                
//                //$graph->ygrid->SetColor('gray');
//                $graph_sektoral->ygrid->Show(false);
//                $graph_sektoral->ygrid->SetFill(false);
//                $graph_sektoral->xaxis->SetTickLabels(array('1.Pertanian, Kehutanan, dan Perikanan','2.Pertambangan dan Penggalian','3.Industri Pengolahan','4.Pengadaan Listrik dan Gas','5.Pengadaan Air, Pengelolaan Sampah, 
//                    Limbah dan Daur Ulang','6.Konstruksi','7.Perdagangan Besar dan Eceran; Reparasi Mobil 
//                    dan Sepeda Motor','8.Transportasi dan Pergudangan','9.Penyediaan Akomodasi dan Makan Minum','10.Informasi dan Komunikasi','11.Jasa Keuangan dan Asuransi','12.Real Estat','13.Jasa Perusahaan','14.Administrasi Pemerintahan, Pertahanan dan 
//Jaminan Sosial Wajib','15.Jasa Pendidikan','16.Jasa Kesehatan dan Kegiatan Sosial','17.Jasa Lainnya','18.Produk Domestik Regional Bruto dengan migas'));
//                //$graph_s_pdrb->title->SetFont();
//                
//                //$graph_s_pdrb->xaxis->SetTickLabels( SIDE_DOWN );
//                
//                $graph_sektoral->yaxis->HideLine(false);
//                $graph_sektoral->yaxis->HideTicks(false,false);
//
//                // For background to be gradient, setfill is needed first.
//                //$graph_sektoral->SetBackgroundGradient('', '#00FFFF', GRAD_HOR, BGRAD_PLOT);
//
//                // Create the bar plots
//                $b1plot_sektoral = new BarPlot($datay_sektoral);
//
//                // ...and add it to the graPH
//                $graph_sektoral->Add($b1plot_sektoral);
//
//                $b1plot_sektoral->SetWeight(0);
//                $b1plot_sektoral->SetFillGradient("#808000","#90EE90",GRAD_HOR);
//                $b1plot_sektoral->SetWidth(17);
//
//                // Display the graph
//                $graph_sektoral->Stroke($this->picture.'/'.$picture_sektoral.'.png');
                                

                ///////
                $datay=$nilai_p;
                //$datay=array(17,22,33,48,24,-5.51,0,0,0,0,0,25,10,0,0,0,4,0);
$datax=array("1.Pertanian, Kehutanan, dan Perikanan","2.Pertambangan dan Penggalian","3.Industri Pengolahan",'4.Pengadaan Listrik dan Gas','5.Pengadaan Air, Pengelolaan Sampah, 
     Limbah dan Daur Ulang','6.Konstruksi','7.Perdagangan Besar dan Eceran; Reparasi Mobil 
                    dan Sepeda Motor','8.Transportasi dan Pergudangan','9.Penyediaan Akomodasi dan Makan Minum','10.Informasi dan Komunikasi','11.Jasa Keuangan dan Asuransi','12.Real Estat','13.Jasa Perusahaan','14.Administrasi Pemerintahan, Pertahanan dan 
Jaminan Sosial Wajib','15.Jasa Pendidikan','16.Jasa Kesehatan dan Kegiatan Sosial','17.Jasa Lainnya','18.Produk Domestik Regional Bruto dengan migas');
//$datax->array('1.Pertanian, Kehutanan, dan Perikanan','2.Pertambangan dan Penggalian','3.Industri Pengolahan','4.Pengadaan Listrik dan Gas','5.Pengadaan Air, Pengelolaan Sampah, 
//                    Limbah dan Daur Ulang','6.Konstruksi','7.Perdagangan Besar dan Eceran; Reparasi Mobil 
//                    dan Sepeda Motor','8.Transportasi dan Pergudangan','9.Penyediaan Akomodasi dan Makan Minum','10.Informasi dan Komunikasi','11.Jasa Keuangan dan Asuransi','12.Real Estat','13.Jasa Perusahaan','14.Administrasi Pemerintahan, Pertahanan dan 
//Jaminan Sosial Wajib','15.Jasa Pendidikan','16.Jasa Kesehatan dan Kegiatan Sosial','17.Jasa Lainnya','18.Produk Domestik Regional Bruto dengan migas');
// Size of graph
$width=1200; 
$height=700;
// Set the basic parameters of the graph 
$graph = new Graph($width,$height,'auto');
$graph->SetScale("textlin");
$graph->graph_theme = null;
                $graph->SetFrame(false);
$top = 10;
$bottom = 80;
$left = 400;
$right = 10;
$graph->Set90AndMargin($left,$right,$top,$bottom);
$graph->xaxis->SetPos('min');
// Nice shadow
$graph->SetShadow();
// Setup title
//$graph->title->Set("Horizontal bar graph ex 3");
//$rgaph->title->SetFont(FF_VERDANA,FS_BOLD,14);
//$graph->subtitle->Set("(Axis at bottom)");
// Setup X-axis
$graph->xaxis->SetTickLabels($datax_s_pdrb);
//$graph->xaxis->SetFont(FF_FONT2,FS_BOLD,12);
// Some extra margin looks nicer
$graph->xaxis->SetLabelMargin(5);
// Label align for X-axis
$graph->xaxis->SetLabelAlign('right','center');
// Add some grace to y-axis so the bars doesn't go
// all the way to the end of the plot area
$graph->yaxis->scale->SetGrace(20);
// Setup the Y-axis to be displayed in the bottom of the 
// graph. We also finetune the exact layout of the title,
// ticks and labels to make them look nice.
$graph->yaxis->SetPos('max');
// First make the labels look right
$graph->yaxis->SetLabelAlign('center','top');
$graph->yaxis->SetLabelFormat('%d');
$graph->yaxis->SetLabelSide(SIDE_RIGHT);
// The fix the tick marks
$graph->yaxis->SetTickSide(SIDE_LEFT);
// Finally setup the title
$graph->yaxis->SetTitleSide(SIDE_RIGHT);
$graph->yaxis->SetTitleMargin(35);
// To align the title to the right use :
$graph->yaxis->SetTitle('Pertumbuhan PDRB Sektoral TW I','high');
$graph->yaxis->title->Align('center');
// To center the title use :
//$graph->yaxis->SetTitle('Turnaround 2002','center');
//$graph->yaxis->title->Align('center');
//$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->yaxis->title->SetAngle(0);
//$graph->yaxis->SetFont(FF_FONT2,FS_NORMAL);
// If you want the labels at an angle other than 0 or 90
// you need to use TTF fonts
//$graph->yaxis->SetLabelAngle(0);
// Now create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor("steelblue");
$bplot->SetShadow();
//You can change the width of the bars if you like
//$bplot->SetWidth(0.5);
// We want to display the value of each bar at the top
$bplot->value->Show();
//$bplot->value->SetFont(FF_ARIAL,FS_BOLD,12);
$bplot->value->SetAlign('left','center');
$bplot->value->SetColor("black","darkred");
$bplot->value->SetFormat('%.2f ');
// Add the bar to the graph
$graph->Add($bplot);
$graph->Stroke($this->picture.'/'.$picture_sektoral.'.png');
                
                //c to c
                //$data1y_c=array(12,8,19,3,10,5);
$data1y_c=$nilai_s;
                $data2y_c=array(0,0,0,0,0,0);
 
// Create the graph. These two calls are always required
$graph_c = new Graph(1200,800);    
$graph_c->SetScale("textlin");
$graph_c->SetShadow();
$top = 10;
$bottom = 500;
$left = 40;
$right = 10;
//$graph_c->Set90AndMargin($left,$right,$top,$bottom);
$graph_c->img->SetMargin(40,30,20,300);
 
// Create the bar plots
$b1plot_c = new BarPlot($data1y_c);
$b1plot_c->SetFillColor("orange");
$b2plot_c = new BarPlot($data2y_c);
$b2plot_c->SetFillColor("blue");
 
// Create the grouped bar plot
$gbplot_c = new GroupBarPlot(array($b1plot_c,$b2plot_c));
$gbplot_c->SetWidth(0.9);
 
// ...and add it to the graPH
$graph_c->Add($gbplot_c);
 
$graph_c->title->Set("");
$graph_c->xaxis->title->Set("");
$graph_c->yaxis->title->Set("");
 
$graph_c->title->SetFont(FF_FONT1,FS_BOLD);
$graph_c->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph_c->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph_c->xaxis->SetTickLabels($datax_s_pdrb);
$graph_c->xaxis->SetLabelAngle(90);
 
// Display the graph
$graph_c->Stroke($this->picture.'/'.$picture_ctc.'.png');
 
//print_r($label_data_ppe_kab);exit();
                //Tingkat Kemiskinan
                    $sql_tk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_tk = $this->db->query($sql_tk);
                    foreach ($list_tk->result() as $row_tk) {
                        $tahun_tk[]     = $bulan[$row_tk->periode]."-".$row_tk->tahun;
                        $tk_tahun[]     = $bulan_tahun[$row_tk->periode]." ".$row_tk->tahun;
                        $nilaiData_tk[] = (float)$row_tk->nilai;
                    }
                    $datay_tk = $nilaiData_tk;
                    $tahun_tk = $tahun_tk;
                    
                    $sql_tk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                  
                    $list_tk2 = $this->db->query($sql_tk2);
                    foreach ($list_tk2->result() as $row_tk2) {
                        $tahun_tk2[]   = $row_tk2->tahun;
                        $nilaiData_tk2[] = (float)$row_tk2->nilai;
                        $t_tk2 = $row_tk2->t_m_rpjmn;
                        if($t_tk2==0){ $nilaiR = '-'; }
                        else{ $nilaiR= $row_tk2->t_m_rpjmn; }
                        $n_rpjmn_tk2[] = $nilaiR;
                        $t_rkpd_tk2 = $row_tk2->t_rkpd;
                        if($t_rkpd_tk2==0){ $nilaiRKPD = '-'; }
                        else{ $nilaiRKPD= $row_tk2->t_rkpd; }
                        $n_rkpd_tk2[] = $nilaiRKPD;
                        $t_rkp_tk2 = $row_tk2->t_k_rkp;
                        if($t_rkp_tk2==0){ $nilaiRKP = '-'; }
                        else{ $nilaiRKP= $row_tk2->t_k_rkp; }
                        $n_rkp_tk2[] = $nilaiRKP;
                        //$n_rpjmn_tk2[] = (float)$row_tk2->t_m_rpjmn;
                        $sumber_tk       = $row_tk2->sumber;
                        $periode_tk_id[] = $row_tk2->id_periode;
                        $tahun_tk21[]    = $bulan[$row_tk2->periode]."-".$row_tk2->tahun;
                    }
                    $datay_tk2        = $nilaiData_tk2;
                    $data_rpjmn_tk2   = $n_rpjmn_tk2;
                    $data_rkpd_tk2    = $n_rkpd_tk2;
                    $data_rkp_tk2     = $n_rkp_tk2;
                    $tahun_tk2        = $tahun_tk2;
                    $periode_tk_max   = max($periode_tk_id);
                    $periode_tk_tahun = $tahun_tk[5]." Antar Provinsi" ;
                    $periode_tk_a     = $tahun_tk[3]." dengan Periode ".$tahun_tk[5] ;
                    
                    if($nilaiData_tk2[3] > $nilaiData_tk2[5]){
                        $mm_tk="menurun";
                        if($nilaiData_tk[5]>$nilaiData_tk2[5]){
                            $ba_tk='di bawah';
                        }else{
                            $ba_tk='di atas';
                        }
                    } else {
                        $mm_tk="meningkat";
                        if($nilaiData_tk[5]>$nilaiData_tk2[5]){
                            $ba_tk='di bawah';
                        }else{
                            $ba_tk='di atas';
                        }
                    }
                    if($data_rkpd_tk2[5]=='-'){
                        $tk_rkpd_rkp1 ="";
                    }else{
                        if($nilaiData_tk2[5] > $data_rkpd_tk2[5]){ $rkpdtk="di atas"; }
                        else{ $rkpdtk="di bawah"; }
                        if($nilaiData_tk2[5] > $data_rkp_tk2[5]){ $rkptk='di atas'; }
                        else{ $rkptk='di bawah'; }
                        $tk_rkpd_rkp1 = "Tingkat kemiskinan ". $xname ." periode ".$tk_tahun[5]." berada ".$rkpdtk." target RKPD ". $xname ." (".number_format($data_rkpd_tk2[5],2,",",".")."%) dan ".$rkptk." target kewilayahan RKP (".number_format($data_rkp_tk2[5],2,",",".")."%.) ";
                    }
                    $max_n_tk    ="Tingkat kemiskinan ". $xname ." pada ".$tk_tahun[5]." ".$mm_tk." dibandingkan dengan ".$tk_tahun[3].". Pada ".$tk_tahun[5]." tingkat kemiskinan ". $xname ." adalah sebesar ". number_format(end($nilaiData_tk2),2,",",".") ."%, sedangkan pada ".$tk_tahun[3]." tingkat kemiskinan tercatat sebesar ".number_format($nilaiData_tk2[3],2,",",".")."%. ";                    
                    $max_p_tk    ="Tingkat kemiskinan ". $xname ." pada ".$tk_tahun[5]." berada ".$ba_tk." capaian nasional. Tingkat kemiskinan nasional pada ".$tk_tahun[5]." adalah sebesar ".number_format(end($nilaiData_tk),2,",",".") ."%. ";
                    $tk_rkpd_rkp = $tk_rkpd_rkp1;
                    $max_k_tk = "";
                    $perbandingan_tk ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='36' AND e.id_periode='$periode_tk_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='36' AND id_periode='$periode_tk_max' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    
                    $list_tk_per = $this->db->query($perbandingan_tk);
                    foreach ($list_tk_per->result() as $row_tk_per) {
                        $label_tk[]     = $row_tk_per->label;
                        $nilai_tk_per[] = $row_tk_per->nilai;
                        $nilai_tk_r1[$row_tk_per->label]         = $row_tk_per->nilai;
                        $nilai_tk_r2[$row_tk_per->nama_provinsi] = $row_tk_per->nilai;
                    }
                    $label_data_tk     = $label_tk;
                    $nilai_data_tk_per = $nilai_tk_per;
                    $nilai_data_tk_r1  = $nilai_tk_r1;
                    $nilai_data_tk_r2  = $nilai_tk_r2;
                    $rankingtk = $nilai_data_tk_r1;
                    arsort($rankingtk);
                    $nrtk=1;
                    foreach($rankingtk as $xtk=>$xtk_value){
                        if($xtk==$label_pe){
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
                           Provinsi dengan tingkat kemiskinan tertinggi adalah ".array_shift($nama_tk2)." (".number_format($nilai_tk_p_max,2,",",".")."%),
                            sedangkan provinsi dengan tingkat kemiskinan terendah adalah ".end($nama_tk2)." (".number_format($nilai_tk_p_min,2,",",".")."%). ";
                   
                    
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
                    $label_tk2 = $label_tk2;
                    $np_tk2    = $np_tk2;
                    //arsort($label_tk2);
                    $nilai_data_tk_r2 = $nilai_tk_r22;
                    $array_tiga = array();
                for($i=0;$i< count($nilai_data_tk_per);$i++){
                    $array_tiga[$i]=$nilai_data_tk_per[$i]-$nilai_data_tk_r2[$i];
                }
                $kombinasi_tk=array_combine($label_tk2,$array_tiga);
                //arsort($kombinasi_tk);
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
                //arsort($kombinasi_tk);                              //urutan kab/kota nilai terbesar ke terkecil
                //$kombinasi_tk2=array_keys($kombinasi_tk); //urutan kab/kota nama terbesar ke terkecil
//                $nilai_tk_per_p_max = max($kombinasi_tk);  //nila propinsi paling besar
                $nilai_tk_per_p_max = max($kombinasi_tk2);  //nila paling besar
                $nilai_tk_per_p_min = min($kombinasi_tk2);  //nila paling rendah
                
                $kombinasi_tk4=array_combine($np_tk2,$array_tiga);
                asort($kombinasi_tk4);
                $label_p_tk=array_keys($kombinasi_tk4);
                $label_tk_per_p_max = array_shift($label_p_tk); //label paling besar
                $label_tk_per_p_min = end($label_p_tk);     //label paling kecil
                
                $nper_tk=abs($kombinasi_tk2[$label_pe]);
                
                $tk_perbandingan_2th = "Perbandingan tingkat kemiskinan ".$tk_tahun[3]." dengan ".$tk_tahun[5]." menunjukkan bahwa 
                        provinsi yang mengalami penurunan tingkat kemiskinan terbesar adalah ".$label_tk_per_p_max." (".number_format($nilai_tk_per_p_min,2,",",".")."%). 
                            Dari sisi perubahan tingkat kemiskinan periode ".$tk_tahun[3]." hingga ".$tk_tahun[5].", ". $xname ." berada pada urutan ke-".$rengkingtk_p.", dengan tingkat kemiskinan ".$mm_tk." sebesar ".number_format($nper_tk,2,",",".")."%";


                
                     //perbandingan kab
                    $th_tk_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$id_pro."' AND e.id_indikator='36' ";
                    $t_list_kab_tk = $this->db->query($th_tk_kab);
                    foreach ($t_list_kab_tk->result() as $row_t_tk_kab) { $perio_tk = $row_t_tk_kab->perio; }
                    $tk_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$id_pro."' and (e.id_indikator='36' AND e.id_periode='$perio_tk') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='36' AND id_periode='$perio_tk' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_tk_per = $this->db->query($tk_kab);
                    foreach ($list_kab_tk_per->result() as $row_tk_kab_per) {
                        $nilai_tk_per_kab[] = $row_tk_kab_per->nilai;
                        $posisi_tk          = strpos($row_tk_kab_per->label, "Kabupaten");
                        if ($posisi_tk !== FALSE){
                            $label_tk11=substr( $row_tk_kab_per->label,0,3).". ".substr( $row_tk_kab_per->label,10);
                        }else{
                            $label_tk11=$row_tk_kab_per->label;
                        }
                        $label_tk1[]=$label_tk11; 
                        $label_tk1_k[$label_tk11]=$row_tk_kab_per->nilai; 
                        $tahun_tk_k        = $bulan[$row_tk_kab_per->periode]."-".$row_tk_kab_per->tahun;
                        $tk_k_tahun         = $bulan_tahun[$row_tk_kab_per->periode]." ".$row_tk_kab_per->tahun;
                    }
                    $label_data_tk_kab     = $label_tk1;            // array kab/kota
                    $nilai_data_tk_per_kab = $nilai_tk_per_kab;     // array nilai
                    $nilai_kk_tk_per_kab   = $label_tk1_k;          //array kab dan nilai
                    $namatk_k1=$nilai_kk_tk_per_kab;
                    arsort($namatk_k1);                              //urutan kab/kota nilai terbesar ke terkecil
                    $namatk_k2=array_keys($namatk_k1); //urutan kab/kota nama terbesar ke terkecil
                    $nilai_tk_per_kab_max = max($nilai_kk_tk_per_kab);  //nila paling besar
                    $nilai_tk_per_kab_min = min($nilai_kk_tk_per_kab);       // nilai paling kecil
                    $selisih_tk     = $nilai_tk_per_kab_max-$nilai_tk_per_kab_min;
                    arsort($nilai_tk_per_kab);
                    

                   $tk_perbandingan_kab="Perbandingan tingkat kemiskinan antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada ".$tk_k_tahun." daerah dengan tingkat kemiskinan tertinggi adalah ".array_shift($namatk_k2)." (".number_format($nilai_tk_per_kab_max,2,",",".")."%), "
                           . "              sedangkan daerah dengan tingkat kemiskinan terendah adalah ".end($namatk_k2)." (".number_format($nilai_tk_per_kab_min,2,",",".")."%).
                                            Selisih tingkat kemiskinan tertinggi dan terendah di ". $xname ." pada ".$tk_k_tahun." adalah sebesar ".number_format($selisih_tk,2,",",".")."%.";
                                    
                    $tahun_tk_kab = $tahun_tk_k." Antar Kabupaten/kota ".$judul ;
                  
                     //tingkat kemiskinan
                $graph_tk = new Graph(600,230);
                $graph_tk->SetScale("textlin");
                $theme_class_tk= new UniversalTheme;
                $graph_tk->SetTheme($theme_class_tk);
                $graph_tk->SetMargin(40,20,33,58);
                //$graph_tk->title->Set('Perkembangan Tingkat Kemiskinan');
                $graph_tk->SetBox(false);
                $graph_tk->yaxis->HideZeroLabel();
                $graph_tk->yaxis->HideLine(false);
                $graph_tk->yaxis->HideTicks(false,false);
                $graph_tk->xaxis->SetTickLabels($tahun_tk);
                $graph_tk->ygrid->SetFill(false);
                
                $graph_bar_tk = new Graph(600,230);
                $graph_bar_tk->img->SetMargin(40,20,20,100);
                $graph_bar_tk->SetScale("textlin");
                $graph_bar_tk->SetMarginColor("lightblue:1.1");
                $graph_bar_tk->SetShadow();
                $graph_bar_tk->title->SetMargin(8);
                $graph_bar_tk->title->SetColor("darkred");
                $graph_bar_tk->ygrid->SetFill(false);
                $graph_bar_tk->xaxis->SetTickLabels($label_data_tk);
                $graph_bar_tk->yaxis->HideLine(false);
                $graph_bar_tk->yaxis->HideTicks(false,false);                
                $graph_bar_tk->xaxis->SetLabelAngle(90);
                
                //tingkat kemiskinan
                $p1_tk = new LinePlot($datay_tk);
                $graph_tk->Add($p1_tk);
                $p1_tk->SetColor("#0000FF");
                $p1_tk->SetLegend('Indonesia');
                $p1_tk->SetCenter();
                $p1_tk->mark->SetType(MARK_FILLEDCIRCLE,'',9.0);
                $p1_tk->mark->SetSize(3);
                $p1_tk->mark->SetColor('#0000FF');
                $p1_tk->mark->SetFillColor('#0000FF');
                $p1_tk->value->SetMargin(20);
                $p1_tk->value->SetFormat('%0.2f');
                $p1_tk->value->SetAlign('left','center');
                $p1_tk->value->SetColor('#0000FF','darkred');
                $p1_tk->value->Show();
                
                //
                $p2_tk = new LinePlot($datay_tk2);
                $graph_tk->Add($p2_tk);
                $p2_tk->SetColor("#000000");
                $p2_tk->SetLegend($xname);
                $p2_tk->SetCenter();
                $p2_tk->SetStyle("dotted");
                $p2_tk->mark->SetType(MARK_UTRIANGLE,9.0);
                $p2_tk->mark->SetSize(14);
                $p2_tk->mark->SetColor('#000000');
                $p2_tk->mark->SetFillColor('#000000');
                $p2_tk->value->SetColor('#000000');
                $p2_tk->value->SetMargin(20);
                $p2_tk->value->SetFormat('%0.2f');
                $p2_tk->value->SetAlign('right','center');
                $p2_tk->value->SetColor('#000000','darkred');
                $p2_tk->value->Show();

                    //
                $p3_tk = new LinePlot($data_rpjmn_tk2);
                $graph_tk->Add($p3_tk);
                $p3_tk->SetColor("#FF0000");
                $p3_tk->SetCenter();
                $p3_tk->SetLegend('Target Makro RPJMN');
                $p3_tk->mark->SetType(MARK_FILLEDCIRCLE,'',9.0);
                $p3_tk->mark->SetSize(5);
                $p3_tk->mark->SetColor('#FF0000');
                $p3_tk->mark->SetFillColor('#FF0000');
                $p3_tk->value->SetMargin(20);
                $p3_tk->value->SetFormat('%0.2f');
                $p3_tk->value->SetAlign('left','center');
                $p3_tk->value->SetColor('#FF0000','darkred');
                $p3_tk->value->Show();
                
                    //
                $p4_tk = new LinePlot($data_rkpd_tk2);
                $graph_tk->Add($p4_tk);
                $p4_tk->SetColor("#006400");
                $p4_tk->SetLegend('Target RKPD');
                $p4_tk->SetCenter();
                $p4_tk->mark->SetType(MARK_STAR,'',9.0);
                $p4_tk->mark->SetSize(10);
                $p4_tk->mark->SetColor('#006400');
                $p4_tk->mark->SetFillColor('#006400');
                $p4_tk->value->SetColor('#006400');
                $p4_tk->value->SetMargin(14);
                $p4_tk->value->SetFormat('%0.2f');
                $p4_tk->value->SetAlign('right','center');
                $p4_tk->value->Show();
                    
                //
                $p5_tk = new LinePlot($data_rkp_tk2);
                $graph_tk->Add($p5_tk);
                $p5_tk->SetColor("#A9A9A9");
                $p5_tk->SetLegend('Target Kewilayahan RKP');
                $p5_tk->SetCenter();
                $p5_tk->mark->SetType(MARK_SQUARE,'',9.0);
                $p5_tk->mark->SetSize(10);
                $p5_tk->mark->SetColor('#A9A9A9');
                $p5_tk->mark->SetFillColor('#A9A9A9');
                $p5_tk->value->SetMargin(20);
                $p5_tk->value->SetColor('#A9A9A9');
                $p5_tk->value->SetFormat('%0.2f');
                $p5_tk->value->SetAlign('left','center');
                $p5_tk->value->Show();                

                    
                    $graph_tk->legend->SetFrameWeight(1);
                    $graph_tk->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_tk->legend->SetMarkAbsSize(8);
                                        
                    //Radar
                    // Some data to plot
                $titles_tk  = $label_data_tk; //label provinsi
                $data_r_tk  = $nilai_data_tk_per;
                $data_r_tk2 = $nilai_data_tk_r2;
                // Create the graph and the plot
                $graph_r_tk = new RadarGraph(500,380);
                //$graph_r_pe->title->SetFont(FF_VERDANA,FS_NORMAL,12);                
                $graph_r_tk->SetTitles($titles_tk);
                $graph_r_tk->SetCenter(0.5,0.55);
                $graph_r_tk->HideTickMarks();
                //$graph_r_pe->SetColor('lightgreen@0.7');
                $graph_r_tk->axis->SetColor('darkgray');
                $graph_r_tk->grid->SetColor('darkgray');
                $graph_r_tk->grid->Show();
                //$graph_r_pe->axis->title->SetFont(FF_ARIAL,FS_NORMAL,12);
                $graph_r_tk->axis->title->SetMargin(5);
                $graph_r_tk->SetGridDepth(DEPTH_BACK);
                $graph_r_tk->SetSize(0.6);  
                $graph_r_tk->axis->SetLabelAngle(10);                
                $plot_tk = new RadarPlot($data_r_tk);
                $plot_tk->SetColor('red@0.2');
                $plot_tk->SetLineWeight(1);
                //$plot->SetFillColor('red@0.7');
                $plot_tk->mark->SetType(MARK_IMG_SBALL,'red');
                $plot_tk->SetLegend($tahun_tk[5]);
                // Add the plot and display the graph                
                $plot2_tk = new RadarPlot($data_r_tk2);
                $plot2_tk->SetColor('forestgreen');
                $plot2_tk->SetLineWeight(1);
                $plot2_tk->SetFillColor('forestgreen@0.9');
                $plot2_tk->SetLegend($tahun_tk[3]);
                //$plot2->mark->SetType(MARK_IMG_SBALL,'darkred');
                // Add the plot and display the graph
                $graph_r_tk->Add($plot_tk);
                $graph_r_tk->Add($plot2_tk);                    
                    //perbandingan Provinsi
                    $b1plot_tk_per = new BarPlot($nilai_data_tk_per);
                    $gbplot_tk_per = new GroupBarPlot(array($b1plot_tk_per));
                    $graph_bar_tk->Add($gbplot_tk_per);
                    $b1plot_tk_per->SetColor("white");
                    $b1plot_tk_per->SetFillColor("#0000FF");
                    
                    //Perbandingan Kabupaten
                $graph_bar_tk_kab = new Graph(500,250);
                $graph_bar_tk_kab->SetScale("textlin");
                $graph_bar_tk_kab->SetY2Scale("lin",0,90);
                $graph_bar_tk_kab->SetY2OrderBack(false);
                $theme_class_bar_tk_kab=new UniversalTheme;
                $graph_bar_tk_kab->SetTheme($theme_class_bar_tk_kab);
                $graph_bar_tk_kab->SetMargin(40,20,20,150);
                $graph_bar_tk_kab->ygrid->SetFill(false);
                $graph_bar_tk_kab->xaxis->SetTickLabels($label_data_tk_kab);
                $graph_bar_tk_kab->xaxis->SetLabelAngle(90);
                $graph_bar_tk_kab->yaxis->HideLine(false);
                $graph_bar_tk_kab->yaxis->HideTicks(false,false);
                
                $b1plot_tk_kab = new BarPlot($nilai_data_tk_per_kab);
                $b1plot_tk_kab->SetColor("white");
                $b1plot_tk_kab->SetFillColor("#0000FF");  
                $gbplot_kab_tk = new GroupBarPlot(array($b1plot_tk_kab));
                $graph_bar_tk_kab->Add($gbplot_kab_tk);
                    
                    //Tingkat Kemiskinan
               $graph_tk->Stroke($this->picture.'/'.$picture_tk.'.png');
               $graph_r_tk->Stroke($this->picture.'/'.$picture_r_tk.'.png');
               $graph_bar_tk->Stroke($this->picture.'/'.$picture_tk_bar.'.png');
               $graph_bar_tk_kab->Stroke($this->picture.'/'.$picture_k_tk.'.png');
               
               
               //jumlah Penduduk Miskin
                     $sql_jpk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jpk = $this->db->query($sql_jpk);
                    foreach ($list_jpk->result() as $row_jpk) {
                        //$tahun_jpk[]   = $row_jpk->tahun;
                        $tahun_jpk[]    = $bulan[$row_jpk->periode]."-".$row_jpk->tahun;
                        $jpk_tahun[]    = $bulan_tahun[$row_jpk->periode]." ".$row_jpk->tahun;
                        $nilaiData_jpk[] = (float)$row_jpk->nilai;
                        //$nilaiData_jpk1[] = (float)$row_jpk->nilai;
                    }
                    $datay_jpk = $nilaiData_jpk;
                    $tahun_jpk = $tahun_jpk;
                    
                    $sql_jpk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jpk2 = $this->db->query($sql_jpk2);
                    foreach ($list_jpk2->result() as $row_jpk2) {
                        $tahun_jpk2[]   = $row_jpk2->tahun;
                        $nilaiData_jpk2[] = (float)$row_jpk2->nilai;
                        $nilaiData_jpk22[] = (float)$row_jpk2->nilai;
                        $sumber_jpk       = $row_jpk2->sumber;
                        $periode_jpk_id[]   = $row_jpk2->id_periode;
                        $tahun_jpk21[]    = $bulan[$row_jpk2->periode]."-".$row_jpk2->tahun;
                    }
                    $datay_jpk2 = $nilaiData_jpk2;
                    $tahun_jpk2 = $tahun_jpk2;
                    $periode_jpk_max = max($periode_jpk_id);
                    $periode_jpk_max_p = max($tahun_jpk21);
                    $periode_jpk_tahun=$tahun_jpk21[3]." dengan ".max($tahun_jpk21)." " ;
                     if($nilaiData_jpk22[3] > $nilaiData_jpk22[5]){
//                        $rt_jpk=$nilaiData_jpk22[3]-$nilaiData_jpk22[5]; $rt_jpk2=$rt_jpk/$nilaiData_jpk22[5]; $rt_jpk3=$rt_jpk2*100;$rt_jpk33=number_format($rt_jpk3,2);
                        $rt_jpk=$nilaiData_jpk22[5]-$nilaiData_jpk22[3];
                        $rt_jpkk=abs($nilaiData_jpk22[5]-$nilaiData_jpk22[3]);
                        $rt_jpk2=$rt_jpk/$nilaiData_jpk22[3];
                        $rt_jpk3=abs($rt_jpk2*100);
                        $rt_jpk33=number_format($rt_jpk3,2,",",".");
                        $bb_jpk="berkurang";
                    }else{
                        $rt_jpk  =$nilaiData_jpk22[5]-$nilaiData_jpk22[3];
                        $rt_jpk2=$rt_jpk/$nilaiData_jpk22[3];
                        $rt_jpk3=$rt_jpk2*100;
                        $rt_jpk33=number_format($rt_jpk3,2,",",".");
                        $bb_jpk="bertambah";
                    }
                    $max_n_jpk    ="Jumlah penduduk miskin  ". $xname ." pada  ".$jpk_tahun[5]." sebanyak ".number_format($nilaiData_jpk22[5],0,",",".") ." orang sedangkan jumlah penduduk miskin pada ".$jpk_tahun[3]." sebanyak ".number_format($nilaiData_jpk22[3],0,",",".")." orang. Selama periode ".$jpk_tahun[3]." hingga ".$jpk_tahun[5]." jumlah penduduk miskin di provinsi ". $xname ." ".$bb_jpk." sebanyak ".number_format($rt_jpkk,0,",",".")." orang atau sebesar ".number_format($rt_jpk33,2,",",".")."%.";
                    
                    $perbandingan_jpk ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='40' AND e.id_periode='$periode_jpk_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='40' AND id_periode='$periode_jpk_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_jpk_per = $this->db->query($perbandingan_jpk);
                    foreach ($list_jpk_per->result() as $row_jpk_per) {
                        $label_jpk[]     = $row_jpk_per->label;
                        $nilai_jpk_per[] = $row_jpk_per->nilai;
                        $nilai_jpk_per1[] = $row_jpk_per->nilai/100000;
                        $nilai_jpk_r1[$row_jpk_per->label]         = $row_jpk_per->nilai;
                        $nilai_jpk_r2[$row_jpk_per->nama_provinsi] = $row_jpk_per->nilai;
                    }
                    $label_data_jpk     = $label_jpk;
                    $nilai_data_jpk_per = $nilai_jpk_per;
                    $nilai_data_jpk_per1 = $nilai_jpk_per1;
                    $nilai_data_jpk_r1  = $nilai_jpk_r1;
                    $nilai_data_jpk_r2  = $nilai_jpk_r2;
                    $ranking_jpk            = $nilai_data_jpk_r1;
                    arsort($ranking_jpk);
                    $nrjpk=1;
                    foreach($ranking_jpk as $x_jpk=>$x_value){
                        if($x_jpk==$label_pe){
                            $rengkingjpk_pro=$nrjpk++;
                        }
                        $urutanjpk_pro=$x_jpk . $xjpk_value  .$nrjpk++;
                        
                    }
                    $nilai_jpk_p_max = max($nilai_data_jpk_per);  //nila paling besar
                    $nilai_jpk_p_min = min($nilai_data_jpk_per);  //nila paling rendah
                    $nama_jpk1=$nilai_data_jpk_r2;
                    arsort($nama_jpk1);
                    $nama_jpk2=array_keys($nama_jpk1);
                    $jpk_perbandingan_pro = "Perbandingan jumlah penduduk miskin antar 34 provinsi menunjukkan bahwa jumlah penduduk miskin ". $xname ." "
                            . "             pada ".max($jpk_tahun)." berada pada urutan ke-".$rengkingjpk_pro.".
                           Provinsi dengan jumlah penduduk miskin tertinggi adalah ".array_shift($nama_jpk2)." (".number_format($nilai_jpk_p_max,0,",",".")." orang),
                            sedangkan yang terendah adalah ".end($nama_jpk2)." (".number_format($nilai_jpk_p_min,0,",",".")." orang).";
                   
                    
                    $perbandingan_jpk2 ="select p.label as label, p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='40' AND e.id_periode='$periode_jpk_id[3]') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='40' AND id_periode='$periode_jpk_id[3]' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_jpk_per2 = $this->db->query($perbandingan_jpk2);
                    foreach ($list_jpk_per2->result() as $row_jpk_per2) {
                        $label_jpk2[]     = $row_jpk_per2->label;
                        $np_jpk2[]        = $row_jpk_per2->nama_provinsi;
                        //$nilai_jpk_per2[] = $row_jpk_per2->nilai;
                        $nilai_jpk_r2     = $row_jpk_per2->nilai;
                        if ($nilai_jpk_r2 <= 0){
                            $nilai_jpk2 = 0;
                            $nilai_jpk22 = 0;
                        }else{
                            $nilai_jpk2= $row_jpk_per2->nilai;
                            $nilai_jpk22= $row_jpk_per2->nilai/100000;
                        }
                        $nilai_jpk_r22[]=$nilai_jpk2;
                        $nilai_jpk_r222[]=$nilai_jpk22;
                    }
                    //$label_data_jpk2     = $label_jpk2;
                    $nilai_data_jpk_r22  = $nilai_jpk_r222;
                    $np_jpk2             = $np_jpk2;
                    $label_jpk2          = $label_jpk2;
                    //arsort($label_tk2);
                    $nilai_data_jpk_r2 = $nilai_jpk_r22;
                    $array_tiga_jpk = array();
                for($i=0;$i< count($nilai_data_jpk_per);$i++){
                    $array_tiga_jpk[$i]=$nilai_data_jpk_r2[$i]-$nilai_data_jpk_per[$i];
                }
                //$kombinasi_jpk=array_combine($label_jpk2,$array_tiga_jpk);
                $kombinasi_jpk2=array_combine($label_jpk2,$array_tiga_jpk);
                arsort($kombinasi_jpk2); //tinggi-rendah

                $njpk=1;
                    foreach($kombinasi_jpk2 as $xjpk=>$xjpk_value){
                        if($xjpk==$label_pe){
                            $rengkingjpk_p=$njpk++;
                        }
                        $urutan_pro_jpk=$xjpk . $xjpk_value . $xjpk_value .$njpk++;                      
                    }
                $nilai_jpk_per_p_max = max($kombinasi_jpk2);  //nila paling besar
                $nilai_jpk_per_p_min = min($kombinasi_jpk2);  //nila paling rendah
                if($nilai_jpk_per_p_max < 0){
                    $nilai_jpk_per_pmax = 'berkurang '.number_format(abs($nilai_jpk_per_p_max),0,",",".");
                }else{
                    $nilai_jpk_per_pmax = number_format($nilai_jpk_per_p_max,0,",",".");
                }
                $kombinasi_jpk4=array_combine($np_jpk2,$array_tiga_jpk);
                arsort($kombinasi_jpk4);
                $label_p_jpk=array_keys($kombinasi_jpk4);
                $label_jpk_per_p_max = array_shift($label_p_jpk); //label paling besar
                $label_jpk_per_p_min = end($label_p_jpk);     //label paling kecil
                arsort($kombinasi_jpk2);
                $nper_pro=$kombinasi_jpk2[$label_pe];
                if($nper_pro > 0){ $perub = "kenaikan";
                }else{ $perub="penurunan"; }
                
                $jpk_perbandingan_2th = "Perbandingan jumlah penduduk miskin ".$jpk_tahun[3]." dengan ".$jpk_tahun[5]." 
                                            menunjukkan bahwa provinsi yang mengalami penurunan jumlah penduduk miskin terbesar adalah ".$label_jpk_per_p_max." (".number_format($nilai_jpk_per_pmax,0,",",".")." orang). 
                                            Dari sisi perubahan jumlah penduduk miskin selama periode ".$jpk_tahun[3]." hingga ".$jpk_tahun[5].", ". $xname ." berada pada urutan ke-".$rengkingjpk_p.", 
                                            dengan jumlah penduduk miskin ".$bb_jpk." sebesar ".number_format($nper_pro,0,",",".")." orang. ";

                
                    //perbandingan kab
                    $th_jpk_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$id_pro."' AND e.id_indikator='40' ";
                    $t_list_kab_jpk = $this->db->query($th_jpk_kab);
                    foreach ($t_list_kab_jpk->result() as $row_t_jpk_kab) { $perio_jpk = $row_t_jpk_kab->perio; }
                    $jpk_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$id_pro."' and (e.id_indikator='40' AND e.id_periode='$perio_jpk') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='40' AND id_periode='$perio_jpk' group by wilayah ) 
                           group by wilayah order by wilayah asc";

                    $list_kab_jpk_per = $this->db->query($jpk_kab);
                    foreach ($list_kab_jpk_per->result() as $row_jpk_kab_per) {
                        $nilai_jpk_per_kab[] = $row_jpk_kab_per->nilai;
                        $posisi_jpk          = strpos($row_jpk_kab_per->label, "Kabupaten");
                        if ($posisi_jpk !== FALSE){
                            $label_jpk11=substr( $row_jpk_kab_per->label,0,3).". ".substr( $row_jpk_kab_per->label,10);
                        }else{
                            $label_jpk11=$row_jpk_kab_per->label;
                        }
                        $label_jpk1[]=$label_jpk11;
                        $label_jpk1_k[$label_jpk11]=$row_jpk_kab_per->nilai; 
                        $tahun_k_jpk        = $bulan[$row_jpk_kab_per->periode]."-".$row_jpk_kab_per->tahun;
                        $k_jpk_tahun        = $bulan_tahun[$row_jpk_kab_per->periode]." ".$row_jpk_kab_per->tahun;
                    }
                    $nilai_data_jpk_kab     = $nilai_jpk_per_kab;      // array nilai
                    $label_data_jpk_kab1    = $label_jpk1;            // array kab/kota
                    $nilai_kk_jpk_per_kab   = $label_jpk1_k;          //array kab dan nilai
                     $namajpk_k1            = $nilai_kk_jpk_per_kab;
                    arsort($namajpk_k1);                              //urutan kab/kota nilai terbesar ke terkecil
                    $namajpk_k2=array_keys($namajpk_k1);           //urutan kab/kota nama terbesar ke terkecil
                    $nilai_jpk_per_kab_max = max($nilai_kk_jpk_per_kab);  //nilai paling besar
                    $nilai_jpk_per_kab_min = min($nilai_kk_jpk_per_kab);       // nilai paling kecil
                    $selisih_jpk           = $nilai_jpk_per_kab_max-$nilai_jpk_per_kab_min;
                   
                    $tahun_jpk_kab=$tahun_k_jpk." Antar Kabupaten ".$judul ;
                   
                    $jpk_perbandingan_kab="Perbandingan jumlah penduduk miskin antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada ".$k_jpk_tahun." daerah dengan jumlah penduduk miskin tertinggi adalah ".array_shift($namajpk_k2)." (".number_format($nilai_jpk_per_kab_max,0,",",".")." orang), "
                           . "              sedangkan daerah dengan jumlah penduduk miskin terendah adalah ".end($namajpk_k2)." (".number_format($nilai_jpk_per_kab_min,0,",",".")." orang).
                                            Selisih jumlah penduduk miskin tertinggi dan terendah di ". $xname ." pada ".$k_jpk_tahun." sebesar ".number_format($selisih_jpk,0,",",".")." orang.";
                                 
                    //Jumlah Penduduk Miskin                
                $graph_jpk = new Graph(650,250);
                $graph_jpk->SetScale("textlin");
                $graph_jpk->SetY2Scale("lin",0,90);
                $graph_jpk->SetY2OrderBack(false);
                $theme_class_jpk=new UniversalTheme;
                $graph_jpk->SetTheme($theme_class_jpk);
                $graph_jpk->SetMargin(120,60,33,60);
                $graph_jpk->ygrid->SetFill(false);
                $graph_jpk->xaxis->SetTickLabels($tahun_jpk);
                $graph_jpk->yaxis->HideLine(false);
                $graph_jpk->yaxis->HideTicks(false,false);
                $graph_jpk->title->Set("");
                
                $graph_bar_jpk = new Graph(600,230);
                $graph_bar_jpk->img->SetMargin(80,20,20,100);
                $graph_bar_jpk->SetScale("textlin");
                $graph_bar_jpk->SetMarginColor("lightblue:1.1");
                $graph_bar_jpk->SetShadow();
                $graph_bar_jpk->title->SetMargin(8);
                $graph_bar_jpk->title->SetColor("darkred");
                $graph_bar_jpk->ygrid->SetFill(false);
                $graph_bar_jpk->xaxis->SetTickLabels($label_data_jpk);
                $graph_bar_jpk->yaxis->HideLine(false);
                $graph_bar_jpk->yaxis->HideTicks(false,false);                
                $graph_bar_jpk->xaxis->SetLabelAngle(90);
                    
                 //Jumlah Penduduk Miskin                    
                    $b1plot_jpk = new BarPlot($datay_jpk);
                    $b1plot2_jpk = new BarPlot($datay_jpk2);
                    $gbplot_jpk = new GroupBarPlot(array($b1plot2_jpk));
                    $graph_jpk->Add($gbplot_jpk);
                    $b1plot_jpk->SetColor("white");
                    $b1plot_jpk->SetFillColor("#0000FF");
                    $b1plot_jpk->SetLegend("Indonesia");
                    $b1plot_jpk->SetWidth(20);            
                    $b1plot2_jpk->SetColor("white");
                    $b1plot2_jpk->SetFillColor("#000000");
                    $b1plot2_jpk->SetLegend($xname);
                    $b1plot2_jpk->SetWidth(20);
                    
                    $b1plot_jpk_per = new BarPlot($nilai_data_jpk_per);
                    $gbplot_jpk_per = new GroupBarPlot(array($b1plot_jpk_per));
                    $graph_bar_jpk->Add($gbplot_jpk_per);
                    $b1plot_jpk_per->SetColor("white");
                    $b1plot_jpk_per->SetFillColor("#0000FF");
                    
                    //Radar
                $titles_jpk  =$label_jpk2;
                $data_r_jpk  = $nilai_data_jpk_per1;
                $data_r_jpk2 = $nilai_data_jpk_r22;
                // Create the graph and the plot
                $graph_r_jpk = new RadarGraph(500,380);
//                $graph_r_jpk->title->Set('Radar Provinsi (%)');
                //$graph_r_pe->title->SetFont(FF_VERDANA,FS_NORMAL,12);
                $graph_r_jpk->SetTitles($titles_jpk);
                $graph_r_jpk->SetCenter(0.5,0.55);
                $graph_r_jpk->HideTickMarks();
                //$graph_r_pe->SetColor('lightgreen@0.7');
                $graph_r_jpk->axis->SetColor('darkgray');
                $graph_r_jpk->grid->SetColor('darkgray');
                $graph_r_jpk->grid->Show();
                //$graph_r_pe->axis->title->SetFont(FF_ARIAL,FS_NORMAL,12);
                $graph_r_jpk->axis->title->SetMargin(5);
                $graph_r_jpk->SetGridDepth(DEPTH_BACK);
                $graph_r_jpk->SetSize(0.6);
                $plot_jpk = new RadarPlot($data_r_jpk);
                $plot_jpk->SetColor('red@0.2');
                $plot_jpk->SetLineWeight(1);
                //$plot->SetFillColor('red@0.7');
                $plot_jpk->mark->SetType(MARK_IMG_SBALL,'red');
                $plot_jpk->SetLegend($tahun_jpk[5]);
                // Add the plot and display the graph
                $plot2_jpk = new RadarPlot($data_r_jpk2);
                $plot2_jpk->SetColor('forestgreen');
                $plot2_jpk->SetLineWeight(1);
                $plot2_jpk->SetFillColor('forestgreen@0.9');
                $plot2_jpk->SetLegend($tahun_jpk[3]);
                //$plot2->mark->SetType(MARK_IMG_SBALL,'darkred');
                // Add the plot and display the graph
                $graph_r_jpk->Add($plot_jpk);
                $graph_r_jpk->Add($plot2_jpk);
                
                 //Perbandingan Kabupaten
                $graph_bar_jpk_kab = new Graph(600,250);
                $graph_bar_jpk_kab->SetScale("textlin");
                $graph_bar_jpk_kab->SetY2Scale("lin",0,90);
                $graph_bar_jpk_kab->SetY2OrderBack(false);
                $theme_class_bar_jpk_kab=new UniversalTheme;
                $graph_bar_jpk_kab->SetTheme($theme_class_bar_jpk_kab);
                $graph_bar_jpk_kab->SetMargin(60,20,20,150);
                $graph_bar_jpk_kab->ygrid->SetFill(false);
                $graph_bar_jpk_kab->xaxis->SetTickLabels($label_data_jpk_kab1);
                $graph_bar_jpk_kab->xaxis->SetLabelAngle(90);
                $graph_bar_jpk_kab->yaxis->HideLine(false);
                $graph_bar_jpk_kab->yaxis->HideTicks(false,false);
                
                $b1plot_jpk_kab = new BarPlot($nilai_data_jpk_kab);
                $b1plot_jpk_kab->SetColor("white");
                $b1plot_jpk_kab->SetFillColor("#0000FF");  
                $gbplot_kab_jpk = new GroupBarPlot(array($b1plot_jpk_kab));
                $graph_bar_jpk_kab->Add($gbplot_kab_jpk);
                                
                    //Jumlah Penduduk Miskin
               $graph_jpk->Stroke($this->picture.'/'.$picture_jpk.'.png');
               $graph_bar_jpk->Stroke($this->picture.'/'.$picture_jpk_bar.'.png');
               $graph_r_jpk->Stroke($this->picture.'/'.$picture_jpk_r.'.png');
               $graph_bar_jpk_kab->Stroke($this->picture.'/'.$picture_jpk_k.'.png');
               
               //tingkat pengangguran terbuka
                    $sql_tpt = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_tpt = $this->db->query($sql_tpt);
                    foreach ($list_tpt->result() as $row_tpt) {
                        $tahun_tpt1[]    = $bulan[$row_tpt->periode]."-".$row_tpt->tahun;
                        $tahun_tpt[]   = $row_tpt->tahun;
                        $nilaiData_tpt[] = (float)$row_tpt->nilai;
                    }
                    $datay_tpt = $nilaiData_tpt;
                    $tahun_tpt = $tahun_tpt;
                    $sql_tpt2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_tpt2 = $this->db->query($sql_tpt2);
                    foreach ($list_tpt2->result() as $row_tpt2) {
                        $tahun_tpt21[]        = $bulan[$row_tpt2->periode]."-".$row_tpt2->tahun;
                        $tpt_tahun[]        = $bulan_tahun[$row_tpt2->periode]." ".$row_tpt2->tahun;
                        $periode_tpt21[]      = $row_tpt2->periode;
                        $tahun_tpt2[]         = $row_tpt2->tahun;
                        $nilaiData_tpt2[]     = (float)$row_tpt2->nilai;
                        $sumber_tpt           = $row_tpt2->sumber;
                        $periode_tpt_id[]     = $row_tpt2->id_periode;
                        $t_tpt2               = $row_tpt2->t_m_rpjmn;
                        if($t_tpt2==0){ $nilaiRtpt = '-'; }
                        else{ $nilaiRtpt= (float)$row_tpt2->t_m_rpjmn; }
                        $n_rpjmn_tpt2[]       = $nilaiRtpt;
                        
                        $t_rkpd_tpt2 = $row_tpt2->t_rkpd;
                        if($t_rkpd_tpt2==0){ $nilaiRKPDtpt = '-'; }
                        else{ $nilaiRKPDtpt = (float)$row_tpt2->t_rkpd; }
                        $n_rkpd_tpt2[] = $nilaiRKPDtpt;
                        $t_rkp_tpt2 = $row_tpt2->t_k_rkp;
                        if($t_rkp_tpt2==0){ $nilaiRKPtpt = '-'; }
                        else{ $nilaiRKPtpt = (float)$row_tpt2->t_k_rkp; }
                        $n_rkp_tpt2[] = $nilaiRKPtpt;
                        
                    }
                    $datay_tpt2           = $nilaiData_tpt2;
                    $tahun_tpt2           = $tahun_tpt2;
                    $periode_tpt_max      = max($periode_tpt_id);
                    $data_rpjmn_tpt2      = $n_rpjmn_tpt2;
                    $nilaiTarget_RKPD_tpt = $n_rkpd_tpt2;
                    $nilaiTarget_RKP_tpt  = $n_rkp_tpt2;
                                        
                    $periode_tpt_tahun=$bulan[end($periode_tpt21)]." ".max($tahun_tpt2)." Antar Provinsi" ;
                    if($nilaiData_tpt2[3] > $nilaiData_tpt2[5]){                        
                        $mm_tpt='menurun';
                        if($nilaiData_tpt[5]>$nilaiData_tpt2[5]){ $ba_tpt='di bawah';
                        }else{ $ba_tpt='di atas'; }
                    } else {
                        $mm_tpt='meningkat';
                        if($nilaiData_tpt[5]>$nilaiData_tpt2[5]){ $ba_tpt='di bawah';
                        }else{ $ba_tpt='di atas'; }
                    }
                    if($nilaiData_tpt2[5] > $nilaiTarget_RKPD_tpt[5]){ $rkpdtpt="di atas"; }
                    else{ $rkpdtpt="di bawah"; }
                    if($nilaiData_tpt2[5] > $nilaiTarget_RKP_tpt[5]){ $rkptpt='di atas'; }
                    else{ $rkptpt='di bawah'; }
                    $max_tpt      = "Tingkat pengangguran terbuka ". $xname ." pada ".$tpt_tahun[5]." ".$mm_tpt." dibandingkan dengan ".$tpt_tahun[3].". Pada ".$tpt_tahun[5]." tingkat pengangguran terbuka ". $xname ." adalah sebesar ". number_format(end($nilaiData_tpt2),2,",",".") ." sedangkan pada ".$tpt_tahun[3]."  tingkat pengangguran terbuka tercatat sebesar ".number_format($nilaiData_tpt2[3],2,",",".").". ";
                    $max_tpt_p    = "Tingkat pengangguran terbuka ". $xname ." pada ".$tpt_tahun[5]." berada ".$ba_tpt." capaian nasional. Tingkat pengangguran terbuka nasional pada ".$tpt_tahun[5]." adalah sebesar ".number_format(end($nilaiData_tpt),2,",",".") .". ";                    
                    $tpt_rkpd_rkp = "Tingkat pengangguran terbuka ". $xname ." periode ".$tpt_tahun[5]." berada ".$rkpdtpt." target RKPD ". $xname ." (".number_format($nilaiTarget_RKPD_tpt[5],2,",",".").") dan ".$rkptpt." target kewilayahan RKP (".number_format($nilaiTarget_RKP_tpt[5],2,",",".").").";
                    
                    $max_tpt_k =" ";
                    $perbandingan_tpt ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='6' AND e.id_periode='$periode_tpt_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='6' AND id_periode='$periode_tpt_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    
                    $list_tpt_per = $this->db->query($perbandingan_tpt);
                    foreach ($list_tpt_per->result() as $row_tpt_per) {
                        $label_tpt[]     = $row_tpt_per->label;
                        $nilai_tpt_per[] = (float)$row_tpt_per->nilai;
                        $nilai_tpt_r1[$row_tpt_per->label] = $row_tpt_per->nilai;
                        $nilai_tpt_r2[$row_tpt_per->nama_provinsi] = $row_tpt_per->nilai;
                    }
                    $label_data_tpt     = $label_tpt;
                    $nilai_data_tpt_per = $nilai_tpt_per;
                    $nilai_data_tpt_r1  = $nilai_tpt_r1;
                    $nilai_data_tpt_r2  = $nilai_tpt_r2;
                    $rankingtpt         = $nilai_data_tpt_r1;
                    arsort($rankingtpt);
                    $nrtpt=1;
                    foreach($rankingtpt as $xtpt=>$xtpt_value){
                        if($xtpt==$label_pe){
                            $rengkingtpt_pro=$nrtpt++;
                        }
                        $urutan_pro_tpt=$xtpt . $xtpt_value . $xtpt_value .$nrtpt++;
                    }
                    $nilai_tpt_p_max = max($nilai_data_tpt_r1);  //nila paling besar
                    $nilai_tpt_p_min = min($nilai_data_tpt_r1);  //nila paling rendah
                    $nama_tpt1=$nilai_data_tpt_r2;
                    arsort($nama_tpt1);
                    $nama_tpt2=array_keys($nama_tpt1);
                    $tpt_perbandingan_pro = "Perbandingan tingkat pengangguran terbuka antar "
                            . "               34 provinsi menunjukkan bahwa tingkat pengangguran terbuka ". $xname ." 
                                              pada ".$tpt_tahun[5]." berada pada urutan ke-".$rengkingtpt_pro.",
                                              provinsi dengan tingkat pengangguran terbuka tertinggi adalah ".array_shift($nama_tpt2)." (".number_format($nilai_tpt_p_max,2,",",".")."),
                                              sedangkan yang terendah adalah ".end($nama_tpt2)." (".number_format($nilai_tpt_p_min,2,",",".")."). ";
                                  

                    $perbandingan_tpt2 ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='6' AND e.id_periode='$periode_tpt_id[3]') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='6' AND id_periode='$periode_tpt_id[3]' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    $list_tpt_per2 = $this->db->query($perbandingan_tpt2);
                    foreach ($list_tpt_per2->result() as $row_tpt_per2) {
                        $label_tpt2[]     = $row_tpt_per2->label;
                        $np_tpt2[]     = $row_tpt_per2->nama_provinsi;
                        $nilai_tpt_r2     = $row_tpt_per2->nilai;
                        if ($nilai_tpt_r2 <= 0){
                            $nilai_tpt2 = 0;
                        }else{
                            $nilai_tpt2= $row_tpt_per2->nilai;
                        }
                        $nilai_tpt_r22[]=$nilai_tpt2;
                    }
                    $np_tpt2             = $np_tpt2;
                    $label_tpt2 = $label_tpt2;
                    $nilai_data_tpt_r2 = $nilai_tpt_r22;
                    $array_tiga_tpt = array();
                for($i=0;$i< count($nilai_data_tpt_per);$i++){
                    $array_tiga_tpt[$i]=$nilai_data_tpt_per[$i]-$nilai_data_tpt_r2[$i];
                }
               
               // $kombinasi_tpt  = array_combine($label_tpt2,$array_tiga_tpt);
                $kombinasi_tpt2 = array_combine($label_tpt2,$array_tiga_tpt);
                asort($kombinasi_tpt2); //tinggi-rendah
                $kombinasi_tpt3 = array_combine($label_tpt2,$array_tiga_tpt);
                asort($kombinasi_tpt3);
                //$kombinasi_tpt4 = $kombinasi_tpt3;
                
                $ntpt=1;
                    foreach($kombinasi_tpt2 as $xtpt=>$xtpt_value){
                        if($xtpt==$label_pe){
                            $rengkingtpt_p=$ntpt++;
                        }
                        $urutan_pro_tpt=$xtpt . $xtpt_value . $xtpt_value .$ntpt++;                      
                    }
                $nilai_tpt_per_p_max = number_format(max($kombinasi_tpt2),2,",",".");  //nila paling besar
                $nilai_tpt_per_p_min = number_format(min($kombinasi_tpt2),2,",",".");  //nila paling rendah
                $kombinasi_tpt4       = array_combine($np_tpt2,$array_tiga_tpt);
                asort($kombinasi_tpt4);
                $label_p_tpt         = array_keys($kombinasi_tpt4);
                $label_tpt_per_p_max = array_shift($label_p_tpt); //label paling besar
                $label_tpt_per_p_min = end($label_p_tpt);     //label paling kecil
                $label_tpt_3             = $tahun_tpt21[3];
                $label_tpt_5             = $tahun_tpt21[5];
                
                $nper_jpk=$kombinasi_tpt2[$label_pe];
                $nper_jpk1 = abs($nper_jpk);

                $tpt_perbandingan_2th = "Perbandingan tingkat pengangguran terbuka ".$tpt_tahun[3]." dengan ".$tpt_tahun[5]." 
                                         menunjukkan bahwa provinsi yang mengalami penurunan tingkat pengangguran terbuka terbesar adalah ".$label_tpt_per_p_max." (".$nilai_tpt_per_p_min."). 
                                         Dari sisi perubahan tingkat pengangguran terbuka periode ".$tpt_tahun[3]." hingga ".$tpt_tahun[5].", ". $xname ." berada pada urutan ke-".$rengkingtpt_p.", "
                        . "              dengan tingkat pengangguran terbuka ".$mm_tpt." sebesar ".number_format($nper_jpk1,2,",",".").".";

                    //perbandingan kab
                    $th_tpt_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$id_pro."' AND e.id_indikator='6' ";
                    $t_list_kab_tpt = $this->db->query($th_tpt_kab);
                    foreach ($t_list_kab_tpt->result() as $row_t_tpt_kab) { $perio_tpt = $row_t_tpt_kab->perio; }                    
                    $tpt_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$id_pro."' and (e.id_indikator='6' AND e.id_periode='$perio_tpt') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='6' AND id_periode='$perio_tpt' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_tpt_per = $this->db->query($tpt_kab);
                    foreach ($list_kab_tpt_per->result() as $row_tpt_kab_per) {
                        $nilai_tpt_per_kab[] = (float)$row_tpt_kab_per->nilai;
                        $posisi_tpt          = strpos($row_tpt_kab_per->label, "Kabupaten");
                        if ($posisi_tpt !== FALSE){
                            $label_tpt11=substr( $row_tpt_kab_per->label,0,3).". ".substr( $row_tpt_kab_per->label,10);
                        }else{
                            $label_tpt11=$row_tpt_kab_per->label;
                        }
                        $label_tpt1[]=$label_tpt11; 
                        $label_tpt1_k[$label_tpt11]=(float)$row_tpt_kab_per->nilai; 
                        $tahun_k_tpt        = $bulan[$row_tpt_kab_per->periode]."-".$row_tpt_kab_per->tahun;
                        $k_tpt_tahun        = $bulan_tahun[$row_tpt_kab_per->periode]." ".$row_tpt_kab_per->tahun;
                    }
                    
                    $label_data_tpt_kab     = $label_tpt1;            // array kab/kota
                    $nilai_data_tpt_per_kab = $nilai_tpt_per_kab;     // array nilai
                    $nilai_kk_tpt_per_kab   = $label_tpt1_k;          //array kab dan nilai
                    $namatpt_k1=$nilai_kk_tpt_per_kab;
                    arsort($namatpt_k1);                              //urutan kab/kota nilai terbesar ke terkecil
                    $namatpt_k2=array_keys($namatpt_k1); //urutan kab/kota nama terbesar ke terkecil
                    $nilai_tpt_per_kab_max = number_format(max($nilai_kk_tpt_per_kab),2,",",".");  //nila paling besar
                    $nilai_tpt_per_kab_min = number_format(min($nilai_kk_tpt_per_kab),2,",",".");       // nilai paling kecil
                    $selisih_tpt     = $nilai_tpt_per_kab_max-$nilai_tpt_per_kab_min;
                    arsort($nilai_tpt_per_kab);
                    
                   $tpt_perbandingan_kab="Perbandingan tingkat pengangguran terbuka antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada ".$k_tpt_tahun." daerah dengan tingkat pengangguran terbuka tertinggi adalah ".array_shift($namatpt_k2)." (".$nilai_tpt_per_kab_max."), "
                           . "              sedangkan daerah dengan tingkat pengangguran terbuka terendah adalah ".end($namatpt_k2)." (".$nilai_tpt_per_kab_min.").
                                            Selisih tingkat pengangguran terbuka tertinggi dan terendah di ". $xname ." pada ".$k_tpt_tahun." sebesar ".number_format($selisih_tpt,2,",",".").".";
                   
                    $tahun_tpt_kab = $tahun_k_tpt." Antar Kabupaten/kota ".$judul ;
                    
                    
                    //tingkat pengangguran terbuka  
                $graph_tpt = new Graph(500,230);
                $graph_tpt->SetScale("textlin");
                $theme_class_tpt= new UniversalTheme;
                $graph_tpt->SetTheme($theme_class_tpt);
                $graph_tpt->SetMargin(40,20,33,60);
                $graph_tpt->SetBox(false);
                $graph_tpt->yaxis->HideZeroLabel();
                $graph_tpt->yaxis->HideLine(false);
                $graph_tpt->yaxis->HideTicks(false,false);
                $graph_tpt->xaxis->SetTickLabels($tahun_tpt1);
                $graph_tpt->ygrid->SetFill(false);
                
                //tingkat pengangguran terbuka
                $p1_tpt = new LinePlot($datay_tpt);
                $graph_tpt->Add($p1_tpt);
                $p1_tpt->SetColor("#0000FF");
                $p1_tpt->SetLegend('Indonesia');
                $p1_tpt->SetCenter();
                $p1_tpt->mark->SetType(MARK_FILLEDCIRCLE,'',9.0);
                $p1_tpt->mark->SetSize(3);
                $p1_tpt->mark->SetColor('#0000FF');
                $p1_tpt->mark->SetFillColor('#0000FF');
                $p1_tpt->value->SetMargin(20);
                $p1_tpt->value->SetFormat('%0.2f');
                $p1_tpt->value->SetAlign('left','center');
                $p1_tpt->value->SetColor('#0000FF','darkred');
                $p1_tpt->value->Show();
                
                    
                    $p2_tpt = new LinePlot($datay_tpt2);
                $graph_tpt->Add($p2_tpt);
                $p2_tpt->SetColor("#000000");
                $p2_tpt->SetLegend($xname);
                $p2_tpt->SetCenter();
                $p2_tpt->SetStyle("dotted");
                $p2_tpt->mark->SetType(MARK_UTRIANGLE,9.0);
                $p2_tpt->mark->SetSize(14);
                $p2_tpt->mark->SetColor('#000000');
                $p2_tpt->mark->SetFillColor('#000000');
                $p2_tpt->value->SetColor('#000000');
                $p2_tpt->value->SetMargin(20);
                $p2_tpt->value->SetFormat('%0.2f');
                $p2_tpt->value->SetAlign('right','center');
                $p2_tpt->value->SetColor('#000000','darkred');
                $p2_tpt->value->Show();
                                    
                $p3_tpt = new LinePlot($data_rpjmn_tpt2);
                $graph_tpt->Add($p3_tpt);
                $p3_tpt->SetColor("#FF0000");
                $p3_tpt->SetCenter();
                $p3_tpt->SetLegend('Target Makro RPJMN');
                $p3_tpt->mark->SetType(MARK_FILLEDCIRCLE,'',9.0);
                $p3_tpt->mark->SetSize(5);
                $p3_tpt->mark->SetColor('#FF0000');
                $p3_tpt->mark->SetFillColor('#FF0000');
                $p3_tpt->value->SetMargin(20);
                $p3_tpt->value->SetFormat('%0.2f');
                $p3_tpt->value->SetAlign('left','center');
                $p3_tpt->value->SetColor('#FF0000','darkred');
                $p3_tpt->value->Show();
                    
//             
                                    $p4_tpt = new LinePlot($nilaiTarget_RKPD_tpt);
                $graph_tpt->Add($p4_tpt);
                $p4_tpt->SetColor("#006400");
                $p4_tpt->SetLegend('Target RKPD');
                $p4_tpt->SetCenter();
                $p4_tpt->mark->SetType(MARK_STAR,'',9.0);
                $p4_tpt->mark->SetSize(10);
                $p4_tpt->mark->SetColor('#006400');
                $p4_tpt->mark->SetFillColor('#006400');
                $p4_tpt->value->SetColor('#006400');
                $p4_tpt->value->SetMargin(14);
                $p4_tpt->value->SetFormat('%0.2f');
                $p4_tpt->value->SetAlign('right','center');
                $p4_tpt->value->Show();
                
//              
                                $p5_tpt = new LinePlot($nilaiTarget_RKP_tpt);
                $graph_tpt->Add($p5_tpt);
                $p5_tpt->SetColor("#A9A9A9");
                $p5_tpt->SetLegend('Target Kewilayahan RKP');
                $p5_tpt->SetCenter();
                $p5_tpt->mark->SetType(MARK_SQUARE,'',9.0);
                $p5_tpt->mark->SetSize(10);
                $p5_tpt->mark->SetColor('#A9A9A9');
                $p5_tpt->mark->SetFillColor('#A9A9A9');
                $p5_tpt->value->SetMargin(20);
                $p5_tpt->value->SetColor('#A9A9A9');
                $p5_tpt->value->SetFormat('%0.2f');
                $p5_tpt->value->SetAlign('left','center');
                $p5_tpt->value->Show();                
                    
                    
                    $graph_tpt->legend->SetFrameWeight(1);
                    $graph_tpt->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_tpt->legend->SetMarkAbsSize(8);
                    
                    $graph_bar_tpt = new Graph(600,200);
                $graph_bar_tpt->img->SetMargin(40,20,20,100);
                $graph_bar_tpt->SetScale("textlin");
                $graph_bar_tpt->SetMarginColor("lightblue:1.1");
                $graph_bar_tpt->SetShadow();
                $graph_bar_tpt->title->SetMargin(8);
                $graph_bar_tpt->title->SetColor("darkred");

                $graph_bar_tpt->ygrid->SetFill(false);
                $graph_bar_tpt->xaxis->SetTickLabels($label_data_tpt);
                $graph_bar_tpt->yaxis->HideLine(false);
                $graph_bar_tpt->yaxis->HideTicks(false,false);                
                $graph_bar_tpt->xaxis->SetLabelAngle(90);
                  
                    $b1plot_tpt_per = new BarPlot($nilai_data_tpt_per);
                    $gbplot_tpt_per = new GroupBarPlot(array($b1plot_tpt_per));
                    $graph_bar_tpt->Add($gbplot_tpt_per);
                    $b1plot_tpt_per->SetColor("white");
                    $b1plot_tpt_per->SetFillColor("#0000FF");
                    $b1plot_tpt_per->value->SetFormat('%0.2f');  
                    
                    
                    //Radar
                    // Some data to plot
                $titles_tpt  = $label_data_tpt;
                $data_r_tpt  = $nilai_data_tpt_per;
                $data_r_tpt2 = $nilai_data_tpt_r2;
                // Create the graph and the plot
                $graph_r_tpt = new RadarGraph(500,380);
//                $graph_r_tpt->title->Set('Radar Provinsi (%)');
                //$graph_r_pe->title->SetFont(FF_VERDANA,FS_NORMAL,12);
                $graph_r_tpt->SetTitles($titles_tpt);
                $graph_r_tpt->SetCenter(0.5,0.55);
                //$graph_r_tpt->HideTickMarks();
                //$graph_r_pe->SetColor('lightgreen@0.7');
                $graph_r_tpt->axis->SetColor('darkgray');
                $graph_r_tpt->grid->SetColor('darkgray');
                $graph_r_tpt->grid->Show();
                //$graph_r_pe->axis->title->SetFont(FF_ARIAL,FS_NORMAL,12);
                $graph_r_tpt->axis->title->SetMargin(5);
                $graph_r_tpt->SetGridDepth(DEPTH_BACK);
                $graph_r_tpt->SetSize(0.6);
                $plot_tpt = new RadarPlot($data_r_tpt);
                $plot_tpt->SetColor('red@0.2');
                $plot_tpt->SetLineWeight(1);
                //$plot->SetFillColor('red@0.7');
                $plot_tpt->mark->SetType(MARK_IMG_SBALL,'red');
                $plot_tpt->SetLegend($label_tpt_5);
                // Add the plot and display the graph
                $plot2_tpt = new RadarPlot($data_r_tpt2);
                $plot2_tpt->SetColor('forestgreen');
                $plot2_tpt->SetLineWeight(1);
                $plot2_tpt->SetFillColor('forestgreen@0.9');
                $plot2_tpt->SetLegend($label_tpt_3);
                //$plot2->mark->SetType(MARK_IMG_SBALL,'darkred');
                // Add the plot and display the graph
                $graph_r_tpt->Add($plot_tpt);
                $graph_r_tpt->Add($plot2_tpt);
                
                //Perbandingan Kabupaten
                $graph_bar_tpt_kab = new Graph(500,250);
                $graph_bar_tpt_kab->SetScale("textlin");
                $graph_bar_tpt_kab->SetY2Scale("lin",0,90);
                $graph_bar_tpt_kab->SetY2OrderBack(false);
                $theme_class_bar_tpt_kab=new UniversalTheme;
                $graph_bar_tpt_kab->SetTheme($theme_class_bar_tpt_kab);
                $graph_bar_tpt_kab->SetMargin(40,20,20,150);
                $graph_bar_tpt_kab->ygrid->SetFill(false);
                $graph_bar_tpt_kab->xaxis->SetTickLabels($label_data_tpt_kab);
                $graph_bar_tpt_kab->xaxis->SetLabelAngle(90);
                $graph_bar_tpt_kab->yaxis->HideLine(false);
                $graph_bar_tpt_kab->yaxis->HideTicks(false,false);
                
                $b1plot_tpt_kab = new BarPlot($nilai_data_tpt_per_kab);
                $b1plot_tpt_kab->SetColor("white");
                $b1plot_tpt_kab->SetFillColor("#0000FF");  
                $gbplot_kab_tpt = new GroupBarPlot(array($b1plot_tpt_kab));
                $graph_bar_tpt_kab->Add($gbplot_kab_tpt);
                
                    //tingkat pengangguran terbuka
               $graph_tpt->Stroke($this->picture.'/'.$picture_tpt.'.png');
               $graph_bar_tpt->Stroke($this->picture.'/'.$picture_tpt_bar.'.png');
               $graph_r_tpt->Stroke($this->picture.'/'.$picture_tpt_r.'.png');
               $graph_bar_tpt_kab->Stroke($this->picture.'/'.$picture_tpt_k.'.png');
               
                //jumlah pengangguran (Orang)
                    $sql_jp = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jp = $this->db->query($sql_jp);
                    foreach ($list_jp->result() as $row_jp) {
                        $tahun_jp[]      = $bulan[$row_jp->periode]."-".$row_jp->tahun;
                        $tahun_jp1[]     = $row_jp->id_periode;
                        $nilaiData_jp[]  = (float)$row_jp->nilai/1000;
                        $nilai_capaian[] = $row_jp->nilai;
                        $tahun_jp11[]    = $row_jp->tahun;
                        $periode_jp1[]   = $row_jp->periode;
                    }
                    $datay_jp = $nilaiData_jp;
                    $tahun_jp = $tahun_jp;
                    $periode_jp_max  =max($tahun_jp1);
                    $periode_jp_tahun=$bulan[$periode_jp1[5]]." ".max($tahun_jp11)." Antar Provinsi" ;
                    $sql_jp2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jp2 = $this->db->query($sql_jp2);
                    foreach ($list_jp2->result() as $row_jp2) {
                        $tahun_jp2[]      = $row_jp2->tahun;
                        $nilaiData_jp2[]  = (float)$row_jp2->nilai/1000;
                        $nilai_capaian2[] = $row_jp2->nilai;
                        $sumber_jp        = $row_jp->sumber;
                    }
                    $datay_jp2  = $nilaiData_jp2;
                    $tahun_jp2  = $tahun_jp2;
                    $label_jp_5 = $tahun_jp[5];
                    $label_jp_3 = $tahun_jp[3];
                    if($nilai_capaian[3] > $nilai_capaian[5]){
                        $nn_jp   = $nilai_capaian[3]-$nilai_capaian[5];
                        $nn_jp2  = $nn_jp/$nilai_capaian[5];
                        $nn_jp3  = $nn_jp2*100;
                        $nn_jp33 = number_format($nn_jp3,2,",",".");
//                        $max_jp  = "Jumlah penganggur nasional pada ". $tahun_jp[5] ." sebanyak ". number_format($nilai_capaian[5],0) . " orang. Sedangkan jumlah penganggur pada ". $tahun_jp[3] ." sebanyak ". number_format($nilai_capaian[3],0) . " orang. Selama periode  ". $tahun_jp[3] ." - ". $tahun_jp[5] . " jumlah penganggur nasional berkurang ".number_format($nn_jp)." orang atau sebesar ".$nn_jp33 ."%.";
                        $bm_jp="berkurang";
                    } else {
                        $nn_jp   = $nilai_capaian[5]-$nilai_capaian[3];
                        $nn_jp2  = $nn_jp/$nilai_capaian[3];
                        $nn_jp3  = $nn_jp2*100;
                        $nn_jp33 = number_format($nn_jp3,2,",",".");
                        $bm_jp="meningkat";
//                        $max_jp  = "Jumlah penganggur nasional pada ". $tahun_jp[5] ." sebanyak ". number_format($nilai_capaian[5],0) . " orang. Sedangkan jumlah penganggur pada ". $tahun_jp[3] ." sebanyak ". number_format($nilai_capaian2[3],0) . " orang. Selama periode  ". $tahun_jp[3] ." - ". $tahun_jp[5] . " jumlah penganggur di ". $xname ." meningkat ".number_format($nn_jp)." orang atau sebesar ".number_format($nn_jp33) ."%.";
                    }
                   $max_jp  = "Jumlah penganggur nasional pada ". $tahun_jp[5] ." sebanyak ". number_format($nilai_capaian[5],0,",",".") . " orang. Sedangkan jumlah penganggur pada ". $tahun_jp[3] ." sebanyak ". number_format($nilai_capaian[3],0,",",".") . " orang. Selama periode  ". $tahun_jp[3] ." - ". $tahun_jp[5] . " jumlah penganggur nasional ".$bm_jp." ".number_format($nn_jp,0,",",".")." orang atau sebesar ".number_format($nn_jp33,2,",",".")."%.";
                   
                   //$max_jp  = "Jumlah penganggur nasional pada ". $tahun_jp[5] ." sebanyak ". number_format($nilai_capaian[5],0) . " orang. Sedangkan jumlah penganggur pada ". $tahun_jp[3] ." sebanyak ". number_format($nilai_capaian2[3],0) . " orang. Selama periode  ". $tahun_jp[3] ." - ". $tahun_jp[5] . " jumlah penganggur di ". $xname ." meningkat ".number_format($nn_jp)." orang atau sebesar ".number_format($nn_jp33,2) ."%.";
                    if($nilai_capaian2[3] > $nilai_capaian2[5]){
                        //$rt_jp=$nilai_capaian2[3]-$nilai_capaian2[5];$rt_jp2=$rt_jp/$nilai_capaian2[3];$rt_jp3=$rt_jp2*100;$rt_jp33=number_format($rt_jp3,2);
                        $rt_jp    = $nilai_capaian2[5]-$nilai_capaian2[3];
                        $rt_jpp   = abs($nilai_capaian2[5]-$nilai_capaian2[3]);
                        $rt_jp2   = $rt_jp/$nilai_capaian2[3];
                        $rt_jp3   = abs($rt_jp2*100);
                        $rt_jp33  = number_format($rt_jp3,2,",",".");
                        $bm_jp="berkurang";
                    }else{
                        $rt_jp     = $nilai_capaian2[5]-$nilai_capaian2[3];
                        $rt_jpp   = abs($nilai_capaian2[5]-$nilai_capaian2[3]);
                        $rt_jp2    = $rt_jp/$nilai_capaian2[3];
                        $rt_jp3    = $rt_jp2*100;
                        $rt_jp33   = number_format($rt_jp3,2,",",".");
                        $bm_jp="meningkat";
                    }
                    $max_jp_p = "Jumlah penganggur di ". $xname ." pada ". $tahun_jp[5] ." sebanyak ". number_format($nilai_capaian2[5],0,",",".") . " orang. Sedangkan jumlah penganggur pada ". $tahun_jp[3] ." sebanyak ". number_format($nilai_capaian2[3],0,",",".") . " orang. Selama periode  ". $tahun_jp[3] ." hingga ". $tahun_jp[5] . " jumlah penganggur di ". $xname ." ".$bm_jp." ".number_format($rt_jpp,0,",",".")." orang atau sebesar ".number_format($rt_jp33,2,",",".")."%.";
                    
                    $perbandingan_jp ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='4' AND e.id_periode='$periode_jp_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='4' AND id_periode='$periode_jp_max' 
                            group by wilayah) group by wilayah order by wilayah asc";
                     
                    $list_jp_per = $this->db->query($perbandingan_jp);
                    foreach ($list_jp_per->result() as $row_jp_per) {
                        $label_jp[]      = $row_jp_per->label;
                        $nilai_jp_per[]  = $row_jp_per->nilai/1000;
                        $nilai_jp_per1[] = $row_jp_per->nilai;
                        $nilai_jp_r1[$row_jp_per->label] = $row_jp_per->nilai;
                        $nilai_jp_r2[$row_jp_per->nama_provinsi] = $row_jp_per->nilai;
                    }
                    $label_data_jp     = $label_jp; 
                    $nilai_data_jp_per = $nilai_jp_per1;    
                    $nilai_data_jp_r1  = $nilai_jp_r1;
                    $nilai_data_jp_r2  = $nilai_jp_r2;
                    $rankingjp         = $nilai_data_jp_r1;
                    arsort($rankingjp);
                    $nrjp=1;
                    foreach($rankingjp as $xjp=>$xjp_value){
                        if($xjp==$label_pe){
                            $rengkingjp_pro=$nrjp++;
                        }
                        $urutan_pro_jp=$xjp . $xjp_value . $xjp_value .$nrjp++;
                    }
                    $nilai_jp_p_max = max($nilai_data_jp_r1);  //nila paling besar
                    $nilai_jp_p_min = min($nilai_data_jp_r1);  //nila paling rendah
                    $nama_jp1       = $nilai_data_jp_r2;
                    arsort($nama_jp1);
                    $nama_jp2=array_keys($nama_jp1);
                    
                    $jp_perbandingan_pro = "Perbandingan jumlah penganggur antar 34 provinsi menunjukkan bahwa jumlah penganggur ". $xname ." 
                                         pada ".$tahun_jp[5]." berada di urutan ke-".$rengkingjp_pro.",
                           provinsi dengan jumlah penganggur tertinggi adalah ".array_shift($nama_jp2)." (".number_format($nilai_jp_p_max,0,",",".")." orang),
                            sedangkan yang terendah adalah ".end($nama_jp2)." (".number_format($nilai_jp_p_min,0,",",".")." orang). ";

                    //radar
                    $perbandingan_jp2 ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='4' AND e.id_periode='$periode_tpt_id[3]') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='4' AND id_periode='$periode_tpt_id[3]' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    
                    $list_jp_per2 = $this->db->query($perbandingan_jp2);
                    foreach ($list_jp_per2->result() as $row_jp_per2) {
                        $label_jp2[]     = $row_jp_per2->label;
                        $np_jp2[]        = $row_jp_per2->nama_provinsi;
                        $nilai_jp_r2     = $row_jp_per2->nilai;
                        if ($nilai_jp_r2 <= 0){ $nilai_jp2 = 0;
                        }else{ $nilai_jp2= $row_jp_per2->nilai; }
                        $nilai_jp_r22[]=$nilai_jp2;
                    }
                    $nilai_data_jp_r2   = $nilai_jp_r22;
                    $label_jp2        = $label_jp2;
                    $np_jp2           = $np_jp2;
                    $nilai_data_jp_r2 = $nilai_jp_r22;
                    $array_tiga_jp    = array();
                    for($i=0;$i< count($nilai_data_jp_per);$i++){
                        $array_tiga_jp[$i]=$nilai_data_jp_r2[$i]-$nilai_data_jp_per[$i];
                    }
                    //$kombinasi_jp  = array_combine($label_jp2,$array_tiga_jp);
                    $kombinasi_jp2 = array_combine($label_jp2,$array_tiga_jp);
                    arsort($kombinasi_jp2); //tinggi-rendah
                    $kombinasi_jp3 = array_combine($label_jp2,$array_tiga_jp);
                    arsort($kombinasi_jp3);
                    //$kombinasi_jp4 = $kombinasi_jp3;
                
                $njp=1;
                    foreach($kombinasi_jp2 as $xjp=>$xjp_value){
                        if($xjp==$label_pe){
                            $rengkingjp_p=$njp++;
                        }
                        $urutan_pro_jp=$xjp . $xjp_value . $xjp_value .$njp++;                      
                    }
                $nilai_jp_per_p_max = number_format(max($kombinasi_jp2));  //nila paling besar
                $nilai_jp_per_p_min = min($kombinasi_jp2);  //nila paling rendah
                if($nilai_jp_per_p_min <0){
                    $nilai_jp_per_p_min1 = "berkurang ".number_format(abs($nilai_jp_per_p_min));
                }else{
                    $nilai_jp_per_p_min1 = number_format($nilai_jp_per_p_min);
                }
                $kombinasi_jp4       = array_combine($np_jp2,$array_tiga_jp);
                arsort($kombinasi_jp4);
                $label_p_jp         = array_keys($kombinasi_jp4);
                $label_jp_per_p_max = array_shift($label_p_jp); //label paling besar
                $label_jp_per_p_min = end($label_p_jp);     //label paling kecil
               
                $jp_perbandingan_2th = "Perbandingan jumlah penganggur periode ".$tahun_jp[3]." dengan ".$tahun_jp[5]." "
                                       . "menunjukkan bahwa provinsi yang mengalami penurunan jumlah penganggur terbesar adalah ".$label_jp_per_p_max." (".number_format($nilai_jp_per_p_max,0,",",".")." orang). "
                                       . "Dari sisi perubahan jumlah penganggur selama ".$tahun_jp[3]." hingga ".$tahun_jp[5].", "
                                       . "". $xname ." berada pada urutan ke-".$rengkingjp_p.", dengan jumlah penduduk penganggur ".$bm_jp." sebesar ".number_format($rt_jpp,0,",",".")." orang.";
               

                    //perbandingan kab
                    $th_jp_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$id_pro."' AND e.id_indikator='4' ";
                    $t_list_kab_jp = $this->db->query($th_jp_kab);
                    foreach ($t_list_kab_jp->result() as $row_t_jp_kab) { $perio_jp = $row_t_jp_kab->perio; }
                    $jp_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$id_pro."' and (e.id_indikator='4' AND e.id_periode='$perio_jp') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='4' AND id_periode='$perio_jp' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                   
                    $list_kab_jp_per = $this->db->query($jp_kab);
                    foreach ($list_kab_jp_per->result() as $row_jp_kab_per) {
                        $nilai_jp_per_kab[] = (float)$row_jp_kab_per->nilai;
                        $posisi_jp          = strpos($row_jp_kab_per->label, "Kabupaten");
                        if ($posisi_jp !== FALSE){
                            $label_jp11=substr( $row_jp_kab_per->label,0,3).". ".substr( $row_jp_kab_per->label,10);
                        }else{
                            $label_jp11=$row_jp_kab_per->label;
                        }
                        $label_jp1[]=$label_jp11; 
                        $label_jp1_k[$label_jp11]=(float)$row_jp_kab_per->nilai; 
                        $tahun_jp_k        = $bulan[$row_jp_kab_per->periode]."-".$row_jp_kab_per->tahun;
                    }
                    $label_data_jp_kab     = $label_jp1;            // array kab/kota
                    $nilai_data_jp_per_kab = $nilai_jp_per_kab;     // array nilai
                    $nilai_kk_jp_per_kab   = $label_jp1_k;          //array kab dan nilai
                    $namajp_k1=$nilai_kk_jp_per_kab;
                    arsort($namajp_k1);                              //urutan kab/kota nilai terbesar ke terkecil
                    $namajp_k2=array_keys($namajp_k1); //urutan kab/kota nama terbesar ke terkecil
                    $nilai_jp_per_kab_max = max($nilai_kk_jp_per_kab);  //nila paling besar
                    $nilai_jp_per_kab_min = min($nilai_kk_jp_per_kab);       // nilai paling kecil
                    $selisih_jp     = $nilai_jp_per_kab_max-$nilai_jp_per_kab_min;
                    arsort($nilai_jp_per_kab);
                    $tahun_jp_kab = $tahun_jp_k." Antar Kabupaten/kota ".$judul ;
                   $jp_perbandingan_kab="Perbandingan jumlah penganggur antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada ".$tahun_jp_k." daerah dengan jumlah penganggur tertinggi adalah ".array_shift($namajp_k2)." (".number_format($nilai_jp_per_kab_max,0,",",".")." orang), "
                           . "              sedangkan daerah dengan jumlah penganggur terendah adalah ".end($namajp_k2)." (".number_format($nilai_jp_per_kab_min,0,",",".")." orang).
                                            Selisih jumlah penganggur tertinggi dan terendah di ". $xname ." pada ".$tahun_jp_k." sebesar ".number_format($selisih_jp,0,",",".")." orang.";


                    //jumlah pengangguran
                $graph_jp = new Graph(600,230);
                $graph_jp->SetScale("textlin");
                $graph_jp->SetY2Scale("lin",0,90);
                $graph_jp->SetY2OrderBack(false);
                $theme_class_jp=new UniversalTheme;
                $graph_jp->SetTheme($theme_class_jp);
                $graph_jp->SetMargin(80,20,46,80);
                $graph_jp->ygrid->SetFill(false);
                $graph_jp->xaxis->SetTickLabels($tahun_jp);
                $graph_jp->yaxis->HideLine(false);
                $graph_jp->yaxis->HideTicks(false,false);
                $graph_jp->title->Set("");
                                //jumlah pengangguran
                $b1plot_jp = new BarPlot($datay_jp);
                $b1plot2_jp = new BarPlot($datay_jp2);
                $gbplot_jp = new GroupBarPlot(array($b1plot2_jp));
                $graph_jp->Add($gbplot_jp);
                $b1plot_jp->SetColor("white");
                $b1plot_jp->SetFillColor("#0000FF");
                $b1plot_jp->SetLegend("Indonesia");
                $b1plot_jp->SetWidth(20);            
                $b1plot2_jp->SetColor("white");
                $b1plot2_jp->SetFillColor("#000000");
                $b1plot2_jp->SetLegend($xname);
                $b1plot2_jp->SetWidth(20);
                
                
                $graph_bar_jp = new Graph(600,280);
                $graph_bar_jp->SetScale("textlin");
                $graph_bar_jp->SetY2Scale("lin",0,90);
                $graph_bar_jp->SetY2OrderBack(false);
                $theme_class_bar_jp=new UniversalTheme;
                $graph_bar_jp->SetTheme($theme_class_bar_jp);
                $graph_bar_jp->SetMargin(80,20,20,100);
                $graph_bar_jp->ygrid->SetFill(false);
                $graph_bar_jp->xaxis->SetTickLabels($label_data_jp);
                $graph_bar_jp->yaxis->HideLine(false);
                $graph_bar_jp->yaxis->HideTicks(false,false);
                $graph_bar_jp->title->Set(" ");
                $graph_bar_jp->xaxis->SetLabelAngle(90);
                $b1plot_jp_per = new BarPlot($nilai_data_jp_per);
                $gbplot_jp_per = new GroupBarPlot(array($b1plot_jp_per));
                $graph_bar_jp->Add($gbplot_jp_per);
                $b1plot_jp_per->SetColor("white");
                $b1plot_jp_per->SetFillColor("#0000FF");
              
                    
                    //Radar
                    // Some data to plot
                $titles_jp  = $label_data_jp;
                $data_r_jp  = $nilai_data_jp_per;
                $data_r_jp2 = $nilai_data_jp_r2;
                // Create the graph and the plot
                $graph_r_jp = new RadarGraph(500,380);
                //$graph_r_jp->title->Set('Radar Provinsi (%)');
                //$graph_r_pe->title->SetFont(FF_VERDANA,FS_NORMAL,12);
                $graph_r_jp->SetTitles($titles_jp);
                $graph_r_jp->SetCenter(0.5,0.55);
                $graph_r_jp->HideTickMarks();
                //$graph_r_pe->SetColor('lightgreen@0.7');
                $graph_r_jp->axis->SetColor('darkgray');
                $graph_r_jp->grid->SetColor('darkgray');
                $graph_r_jp->grid->Show();
                //$graph_r_pe->axis->title->SetFont(FF_ARIAL,FS_NORMAL,12);
                $graph_r_jp->axis->title->SetMargin(5);
                $graph_r_jp->SetGridDepth(DEPTH_BACK);
                $graph_r_jp->SetSize(0.6);
                $plot_jp = new RadarPlot($data_r_jp);
                $plot_jp->SetColor('red@0.2');
                $plot_jp->SetLineWeight(1);
                //$plot->SetFillColor('red@0.7');
                $plot_jp->mark->SetType(MARK_IMG_SBALL,'red');
                $plot_jp->SetLegend($label_jp_5);
                // Add the plot and display the graph
                $plot2_jp = new RadarPlot($data_r_jp2);
                $plot2_jp->SetColor('forestgreen');
                $plot2_jp->SetLineWeight(1);
                $plot2_jp->SetFillColor('forestgreen@0.9');
                $plot2_jp->SetLegend($label_jp_3);
                //$plot2->mark->SetType(MARK_IMG_SBALL,'darkred');
                // Add the plot and display the graph
                $graph_r_jp->Add($plot_jp);
                $graph_r_jp->Add($plot2_jp);
                
                //Perbandingan Kabupaten
                $graph_bar_jp_kab = new Graph(500,250);
                $graph_bar_jp_kab->SetScale("textlin");
                $graph_bar_jp_kab->SetY2Scale("lin",0,90);
                $graph_bar_jp_kab->SetY2OrderBack(false);
                $theme_class_bar_jp_kab=new UniversalTheme;
                $graph_bar_jp_kab->SetTheme($theme_class_bar_jp_kab);
                $graph_bar_jp_kab->SetMargin(40,20,20,150);
                $graph_bar_jp_kab->ygrid->SetFill(false);
                $graph_bar_jp_kab->xaxis->SetTickLabels($label_data_jp_kab);
                $graph_bar_jp_kab->xaxis->SetLabelAngle(90);
                $graph_bar_jp_kab->yaxis->HideLine(false);
                $graph_bar_jp_kab->yaxis->HideTicks(false,false);
                
                $b1plot_jp_kab = new BarPlot($nilai_data_jp_per_kab);
                $b1plot_jp_kab->SetColor("white");
                $b1plot_jp_kab->SetFillColor("#0000FF");  
                $gbplot_kab_jp = new GroupBarPlot(array($b1plot_jp_kab));
                $graph_bar_jp_kab->Add($gbplot_kab_jp);
                    
                  //jumlah pengangguran
                $graph_jp->Stroke($this->picture.'/'.$picture_jp.'.png');
                $graph_bar_jp->Stroke($this->picture.'/'.$picture_jp_bar.'.png');
                $graph_r_jp->Stroke($this->picture.'/'.$picture_jp_r.'.png');
                $graph_bar_jp_kab->Stroke($this->picture.'/'.$picture_jp_k.'.png');
               
               //indeks pembangunan Manusia
                    $sql_ipm = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ipm = $this->db->query($sql_ipm);
                    foreach ($list_ipm->result() as $row_ipm) {
                        $tahun_ipm[]   = $row_ipm->tahun;
                        $nilaiData_ipm[] = (float)$row_ipm->nilai;
                    }
                    $datay_ipm = $nilaiData_ipm;
                    $tahun_ipm = $tahun_ipm;
                    $sql_ipm2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    //print_r($sql_ipm2);exit();
                    $list_ipm2 = $this->db->query($sql_ipm2);
                    foreach ($list_ipm2->result() as $row_ipm2) {
                        $tahun_ipm2[]   = $row_ipm2->tahun;
                        $nilaiData_ipm2[] = (float)$row_ipm2->nilai;
                        $sumber_ipm       = $row_ipm2->sumber;
                        $periode_ipm_id[] = $row_ipm2->id_periode;
                        $nilaiTarget_RKPD_ipm = (float)$row_ipm2->t_rkpd;
                        $nilaiTarget_RKP_ipm = (float)$row_ipm2->t_k_rkp;
                        $t_ipm2               = $row_ipm2->t_m_rpjmn;
                        if($t_ipm2==0){ $nilaiRipm = '-'; }
                        else{ $nilaiRipm= (float)$row_ipm2->t_m_rpjmn; }
                        $n_rpjmn_ipm2[]       = $nilaiRipm;
                        
                        $t_rkpd_ipm2 = $row_ipm2->t_rkpd;
                        if($t_rkpd_ipm2==0){ $nilaiRKPDipm = '-'; }
                        else{ $nilaiRKPDipm = (float)$row_ipm2->t_rkpd; }
                        $n_rkpd_ipm2[] = $nilaiRKPDipm;
                        $t_rkp_ipm2    = $row_ipm2->t_k_rkp;
                        if($t_rkp_ipm2==0){ $nilaiRKPipm = '-'; }
                        else{ $nilaiRKPipm = (float)$row_ipm2->t_k_rkp; }
                        $n_rkp_ipm2[] = $nilaiRKPipm;
                    }
                    $datay_ipm2 = $nilaiData_ipm2;
                    $tahun_ipm2 = $tahun_ipm2;
                    $max_ipm_k ="";
                    $periode_ipm_max = max($periode_ipm_id);
                    $periode_ipm_max2=$periode_ipm_max-100;
                    
                    $th_ipm1=max($tahun_ipm2);
                    $th_ipm2=max($tahun_ipm2)-1;
                    //$data_rpjmn_ipm2      = $n_rpjmn_ipm2;
                    $nilaiTarget_RKPD_ipm = $n_rkpd_ipm2;
                    $nilaiTarget_RKP_ipm  = $n_rkp_ipm2;
                    $periode_ipm_tahun="tahun ".$th_ipm2." dengan tahun ".$th_ipm1."" ;
                    if($nilaiData_ipm2[4] > $nilaiData_ipm2[5]){ 
                        $mm_ipm="menurun";
                        if($nilaiData_ipm[5]>$nilaiData_ipm2[5]){ $ba_ipm="di bawah";
                        }else{ $ba_ipm="di atas";
                        }
                    } else {
                        $mm_ipm="meningkat";
                        if($nilaiData_ipm[5]>$nilaiData_ipm2[5]){
                            $ba_ipm="di bawah";
                        }else{
                            $ba_ipm="di atas";
                        }
                    }
                    
                    $max_ipm    ="Indeks pembangunan manusia ". $xname ." pada tahun ".$tahun_ipm2[5]." ".$mm_ipm." dibandingkan dengan tahun ".$tahun_ipm2[4].". Pada tahun ".$tahun_ipm2[5]." indeks pembangunan manusia ". $xname ." adalah sebesar ". number_format(end($nilaiData_ipm2),2,",",".") ." sedangkan pada tahun ".$tahun_ipm2[4]."  indeks pembangunan manusia tercatat sebesar ".number_format($nilaiData_ipm2[4],2,",",".").". ";
                    $max_ipm_p  ="Indeks pembangunan manusia ". $xname ." pada tahun ".$tahun_ipm2[5]." berada ".$ba_ipm." capaian nasional. Indeks Pembangunan Manusia nasional pada tahun ".$tahun_ipm2[5]." adalah sebesar ".number_format(end($nilaiData_ipm),2,",",".") ." "."";

                    if($nilaiTarget_RKPD_ipm[5]=='-'){ $ipm_rkpd_rkp = ""; }
                    else{
                    if($nilaiData_ipm2[5] > $nilaiTarget_RKPD_ipm[5]){ $rkpdipm="diatas"; }
                    else{ $rkpdipm="dibawah"; }
                        $ipm_rkpd_rkp = "Indeks Pembangunan Manusia ". $xname ." tahun ".$tahun_ipm2[5]." berada ".$rkpdipm." target RKPD ". $xname ."(".$nilaiTarget_RKPD_ipm[5].").";   
                    }

                    $perbandingan_ipm ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='5' AND e.id_periode='$periode_ipm_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='5' AND id_periode='$periode_ipm_max' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    $list_ipm_per = $this->db->query($perbandingan_ipm);
                    foreach ($list_ipm_per->result() as $row_ipm_per) {
                        $label_ipm[]     = $row_ipm_per->label;
                        $nilai_ipm_per[] = $row_ipm_per->nilai;
                        $nilai_ipm_r1[$row_ipm_per->label] = $row_ipm_per->nilai;
                        $nilai_ipm_r2[$row_ipm_per->nama_provinsi] = $row_ipm_per->nilai;
                    }
                    $label_data_ipm     = $label_ipm;
                    $nilai_data_ipm_per = $nilai_ipm_per;
                    $nilai_data_ipm_r1    = $nilai_ipm_r1;
                    $nilai_data_ipm_r2    = $nilai_ipm_r2;
                    $rankingipm = $nilai_data_ipm_r1;
                    arsort($rankingipm);
                    $nripm=1;
                    foreach($rankingipm as $xipm=>$xipm_value){
                        if($xipm==$label_pe){
                            $rengkingipm_pro=$nripm++;
                        }
                        $urutan_pro_ipm=$xtpt . $xipm_value . $xipm_value .$nripm++;
                    }
                    $nilai_ipm_p_max = max($nilai_data_ipm_r1);  //nila paling besar
                    $nilai_ipm_p_min = min($nilai_data_ipm_r1);  //nila paling rendah
                    $nama_ipm1       = $nilai_data_ipm_r2;
                    arsort($nama_ipm1);
                    $nama_ipm2=array_keys($nama_ipm1);
                    $ipm_tahun="tahun ".max($tahun_ipm2)." " ;
                    $ipm_perbandingan_pro = "Perbandingan capaian indeks pembangunan manusia antar 34 provinsi menunjukkan bahwa indeks pembangunan manusia ". $xname ." 
                                         pada tahun ".$th_ipm1." berada pada urutan ke-".$rengkingipm_pro.".
                           Provinsi dengan indeks pembangunan manusia tertinggi adalah ".array_shift($nama_ipm2)." (".number_format($nilai_ipm_p_max,2)."),
                            sedangkan yang terendah adalah ".end($nama_ipm2)." (".number_format($nilai_ipm_p_min,2,",",".")."). ";
                    
                     //radar
                    $perbandingan_ipm2 ="select p.label as label, p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='5' AND e.id_periode='$periode_ipm_max2') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='5' AND id_periode='$periode_ipm_max2' 
                            group by wilayah) group by wilayah order by wilayah asc";
                    $list_ipm_per2 = $this->db->query($perbandingan_ipm2);
                    foreach ($list_ipm_per2->result() as $row_ipm_per2) {
                        $label_ipm2[]     = $row_ipm_per2->label;
                        $np_ipm2[]        = $row_ipm_per2->nama_provinsi;
                        $nilai_ipm_per2[] = $row_ipm_per2->nilai;
                        $nilai_ipm_r2     = $row_ipm_per2->nilai;
                        if ($nilai_ipm_r2 <= 0){
                            $nilai_ipm2 = 0;
                        }else{
                            $nilai_ipm2= $row_ipm_per2->nilai;
                        }
                        $nilai_ipm_r22[]=$nilai_ipm2;
                    }
                    $label_data_ipm2     = $label_ipm2;
                    $nilai_data_ipm_per2 = $nilai_ipm_per2;
                    $nilai_data_ipm_r2   = $nilai_ipm_r22;
                    
                    $label_ipm2        = $label_ipm2;
                    $nilai_data_ipm_r2 = $nilai_ipm_r22;
                    $array_tiga_ipm    = array();
                    for($i=0;$i< count($nilai_data_ipm_per);$i++){
                        $array_tiga_ipm[$i]=$nilai_data_ipm_per[$i]-$nilai_data_ipm_r2[$i];
                    }
                    $kombinasi_ipm  = array_combine($label_ipm2,$array_tiga_ipm);
                    $kombinasi_ipm2 = array_combine($label_ipm2,$array_tiga_ipm);
                    arsort($kombinasi_ipm2); //tinggi-rendah
                    $kombinasi_ipm3 = array_combine($label_ipm2,$array_tiga_ipm);
                    arsort($kombinasi_ipm3);
                    //$kombinasi_ipm4 = $kombinasi_ipm3;
                
                $nipm=1;
                    foreach($kombinasi_ipm2 as $xipm=>$xipm_value){
                        if($xipm==$label_pe){
                            $rengkingipm_p=$nipm++;
                        }
                        $urutan_pro_ipm=$xipm . $xipm_value . $xipm_value .$nipm++;                      
                    }
                $nilai_ipm_per_p_max1 = max($kombinasi_ipm2);  //nila paling besar
                $nilai_ipm_per_p_max = number_format($nilai_ipm_per_p_max1,2);
                $nilai_ipm_per_p_min1 = min($kombinasi_ipm2);  //nila paling rendah
                $nilai_ipm_per_p_min = number_format($nilai_ipm_per_p_min1,2);
                $kombinasi_ipm4       = array_combine($np_ipm2,$array_tiga_ipm);
                arsort($kombinasi_ipm4);
                $label_p_ipm         = array_keys($kombinasi_ipm4);
                $label_ipm_per_p_max = array_shift($label_p_ipm); //label paling besar
                $label_ipm_per_p_min = end($label_p_ipm);     //label paling kecil
                
                $nper_ipm=$kombinasi_ipm2[$label_pe];
//                if($nper_ipm > 0){ $p_ipm = "kenaikan";
//                }else{ $p_ipm = "penurunan"; }                                
                $ipm_perbandingan_2th = "Perbandingan indeks pembangunan manusia ".$th_ipm2." dengan ".$th_ipm1." "
                        . "menunjukkan bahwa provinsi yang mengalami peningkatan indeks pembangunan manusia tertinggi adalah ".$label_ipm_per_p_max." (".$nilai_ipm_per_p_max."), "
                        . "Dari sisi perubahan indeks pembangunan manusia ".$th_ipm2." dengan ".$th_ipm1.", "
                        . "". $xname ." berada pada urutan ke-".$rengkingipm_p.", dengan indeks pembangunan manusia ".$mm_ipm." sebesar ".number_format($nper_ipm,2,",",".").".";
                                   
                                
                    //perbandingan kab
                    $th_ipm_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$id_pro."' AND e.id_indikator='5' ";
                    $t_list_kab_ipm = $this->db->query($th_ipm_kab);
                    foreach ($t_list_kab_ipm->result() as $row_t_ipm_kab) { $perio_ipm = $row_t_ipm_kab->perio; }
                    $ipm_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$id_pro."' and (e.id_indikator='5' AND e.id_periode='$perio_ipm') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='5' AND id_periode='$perio_ipm' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_ipm_per = $this->db->query($ipm_kab);
                    foreach ($list_kab_ipm_per->result() as $row_ipm_kab_per) {
                        $nilai_ipm_per_kab[] = $row_ipm_kab_per->nilai;
                        $posisi_ipm          = strpos($row_ipm_kab_per->label, "Kabupaten");
                        if ($posisi_ipm !== FALSE){
                            $label_ipm11=substr( $row_ipm_kab_per->label,0,3).". ".substr( $row_ipm_kab_per->label,10);
                        }else{
                            $label_ipm11=$row_ipm_kab_per->label;
                        }
                        $label_ipm1[]=$label_ipm11; 
                        $label_ipm1_k[$label_ipm11]=$row_ipm_kab_per->nilai; 
                        $tahun_ipm_k        = $bulan[$row_ipm_kab_per->periode]."".$row_ipm_kab_per->tahun;
                    }
                    $label_data_ipm_kab     = $label_ipm1;            // array kab/kota
                    $nilai_data_ipm_per_kab = $nilai_ipm_per_kab;     // array nilai
                    $nilai_kk_ipm_per_kab   = $label_ipm1_k;          //array kab dan nilai
                    $namaipm_k1=$nilai_kk_ipm_per_kab;
                    arsort($namaipm_k1);                              //urutan kab/kota nilai terbesar ke terkecil
                    $namaipm_k2=array_keys($namaipm_k1); //urutan kab/kota nama terbesar ke terkecil
                    $nilai_ipm_per_kab_max = max($nilai_kk_ipm_per_kab);  //nila paling besar
                    $nilai_ipm_per_kab_min = min($nilai_kk_ipm_per_kab);       // nilai paling kecil
                    $selisih_ipm     = $nilai_ipm_per_kab_max-$nilai_ipm_per_kab_min;
                    arsort($nilai_ipm_per_kab);
                    $tahun_ipm_kab = $tahun_ipm_k." Antar Kabupaten/kota ".$judul ;
                   $ipm_perbandingan_kab="Perbandingan indeks pembangunan manusia antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada tahun ".$tahun_ipm_k." daerah dengan indeks pembangunan manusia tertinggi adalah ".array_shift($namaipm_k2)." (".number_format($nilai_ipm_per_kab_max,2,",",".")."), "
                           . "              sedangkan daerah dengan capaian indeks pembangunan manusia terendah adalah ".end($namaipm_k2)." (".number_format($nilai_ipm_per_kab_min,2,",",".").").
                                            Selisih indeks pembangunan manusia tertinggi dan terendah di ". $xname ." pada tahun ".$tahun_ipm_k." sebesar ".number_format($selisih_ipm,2,",",".").".";


                   
                                    //Indeks Pembangunan Manusia
                $graph_ipm = new Graph(500,230);
                $graph_ipm->SetScale("textlin");
                $theme_class_ipm= new UniversalTheme;
                $graph_ipm->SetTheme($theme_class_ipm);
                $graph_ipm->SetMargin(40,20,33,60);
                //$graph_ipm->title->Set('Perkembangan Indeks Pembangunan Manusia');
                $graph_ipm->SetBox(false);
                $graph_ipm->yaxis->HideZeroLabel();
                $graph_ipm->yaxis->HideLine(false);
                $graph_ipm->yaxis->HideTicks(false,false);
                $graph_ipm->xaxis->SetTickLabels($tahun_ipm);
                $graph_ipm->ygrid->SetFill(false);
                //$graph_ipm->SetFormat('%.3f');
                                
                    //Indeks Pembangunan Manusia
                $p1_ipm = new LinePlot($datay_ipm);
                $graph_ipm->Add($p1_ipm);
                $p1_ipm->SetColor("#0000FF");
                $p1_ipm->SetLegend('Indonesia');
                $p1_ipm->SetCenter();
                $p1_ipm->mark->SetType(MARK_FILLEDCIRCLE,'',9.0);
                $p1_ipm->mark->SetSize(3);
                $p1_ipm->mark->SetColor('#0000FF');
                $p1_ipm->mark->SetFillColor('#0000FF');
                $p1_ipm->value->SetMargin(20);
                $p1_ipm->value->SetFormat('%0.2f');
                $p1_ipm->value->SetAlign('left','center');
                $p1_ipm->value->SetColor('#0000FF','darkred');
                $p1_ipm->value->Show();

                $p2_ipm = new LinePlot($datay_ipm2);
                $graph_ipm->Add($p2_ipm);
                $p2_ipm->SetColor("#000000");
                $p2_ipm->SetLegend($xname);
                $p2_ipm->SetCenter();
                $p2_ipm->SetStyle("dotted");
                $p2_ipm->mark->SetType(MARK_UTRIANGLE,9.0);
                $p2_ipm->mark->SetSize(14);
                $p2_ipm->mark->SetColor('#000000');
                $p2_ipm->mark->SetFillColor('#000000');
                $p2_ipm->value->SetColor('#000000');
                $p2_ipm->value->SetMargin(20);
                $p2_ipm->value->SetFormat('%0.2f');
                $p2_ipm->value->SetAlign('right','center');
                $p2_ipm->value->SetColor('#000000','darkred');
                $p2_ipm->value->Show();
                
                $p4_ipm = new LinePlot($nilaiTarget_RKPD_ipm);
                        $graph_ipm->Add($p4_ipm);
                        $p4_ipm->SetColor("#006400");
                        $p4_ipm->SetLegend('Target RKPD');
                        $p4_ipm->SetCenter();
                        $p4_ipm->mark->SetType(MARK_STAR,'',9.0);
                        $p4_ipm->mark->SetSize(10);
                        $p4_ipm->mark->SetColor('#006400');
                        $p4_ipm->mark->SetFillColor('#006400');
                        $p4_ipm->value->SetColor('#006400');
                        $p4_ipm->value->SetMargin(14);
                        $p4_ipm->value->SetFormat('%0.2f');
                        $p4_ipm->value->SetAlign('right','center');
                        $p4_ipm->value->Show();    
                
                    
                    $graph_ipm->legend->SetFrameWeight(1);
                    $graph_ipm->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_ipm->legend->SetMarkAbsSize(8);
                    
                    
                     $graph_bar_ipm = new Graph(600,230);
                $graph_bar_ipm->img->SetMargin(80,20,20,100);
                $graph_bar_ipm->SetScale("textlin");
                $graph_bar_ipm->SetMarginColor("lightblue:1.1");
                $graph_bar_ipm->SetShadow();
                $graph_bar_ipm->title->SetMargin(8);
                $graph_bar_ipm->title->SetColor("darkred");
                $graph_bar_ipm->ygrid->SetFill(false);
                $graph_bar_ipm->xaxis->SetTickLabels($label_data_ipm);
                $graph_bar_ipm->yaxis->HideLine(false);
                $graph_bar_ipm->yaxis->HideTicks(false,false);                
                $graph_bar_ipm->xaxis->SetLabelAngle(90);
                    
                    $b1plot_ipm_per = new BarPlot($nilai_data_ipm_per);
                    $gbplot_ipm_per = new GroupBarPlot(array($b1plot_ipm_per));
                    $graph_bar_ipm->Add($gbplot_ipm_per);
                    $b1plot_ipm_per->SetColor("white");
                    $b1plot_ipm_per->SetFillColor("#0000FF");
                    
                    //Radar
                    // Some data to plot
                $titles_ipm  = $label_data_ipm;
                $data_r_ipm  = $nilai_data_ipm_per;
                $data_r_ipm2 = $nilai_data_ipm_r2;
                // Create the graph and the plot
                $graph_r_ipm = new RadarGraph(500,380);
                //$graph_r_ipm->title->Set('Radar Provinsi (%)');
                //$graph_r_pe->title->SetFont(FF_VERDANA,FS_NORMAL,12);
                $graph_r_ipm->SetTitles($titles_ipm);
                $graph_r_ipm->SetCenter(0.5,0.55);
                $graph_r_ipm->HideTickMarks();
                //$graph_r_pe->SetColor('lightgreen@0.7');
                $graph_r_ipm->axis->SetColor('darkgray');
                $graph_r_ipm->grid->SetColor('darkgray');
                $graph_r_ipm->grid->Show();
                //$graph_r_pe->axis->title->SetFont(FF_ARIAL,FS_NORMAL,12);
                $graph_r_ipm->axis->title->SetMargin(5);
                $graph_r_ipm->SetGridDepth(DEPTH_BACK);
                $graph_r_ipm->SetSize(0.6);
                $plot_ipm = new RadarPlot($data_r_ipm);
                $plot_ipm->SetColor('red@0.2');
                $plot_ipm->SetLineWeight(1);
                //$plot->SetFillColor('red@0.7');
                $plot_ipm->mark->SetType(MARK_IMG_SBALL,'red');
                $plot_ipm->SetLegend($th_ipm1);
                // Add the plot and display the graph
                $plot2_ipm = new RadarPlot($data_r_ipm2);
                $plot2_ipm->SetColor('forestgreen');
                $plot2_ipm->SetLineWeight(1);
                $plot2_ipm->SetFillColor('forestgreen@0.9');
                $plot2_ipm->SetLegend($th_ipm2);
                //$plot2->mark->SetType(MARK_IMG_SBALL,'darkred');
                // Add the plot and display the graph
                $graph_r_ipm->Add($plot_ipm);
                $graph_r_ipm->Add($plot2_ipm);
                
                //Perbandingan Kabupaten
                $graph_bar_ipm_kab = new Graph(500,250);
                $graph_bar_ipm_kab->SetScale("textlin");
                $graph_bar_ipm_kab->SetY2Scale("lin",0,90);
                $graph_bar_ipm_kab->SetY2OrderBack(false);
                $theme_class_bar_ipm_kab=new UniversalTheme;
                $graph_bar_ipm_kab->SetTheme($theme_class_bar_ipm_kab);
                $graph_bar_ipm_kab->SetMargin(40,20,20,150);
                $graph_bar_ipm_kab->ygrid->SetFill(false);
                $graph_bar_ipm_kab->xaxis->SetTickLabels($label_data_ipm_kab);
                $graph_bar_ipm_kab->xaxis->SetLabelAngle(90);
                $graph_bar_ipm_kab->yaxis->HideLine(false);
                $graph_bar_ipm_kab->yaxis->HideTicks(false,false);
                
                $b1plot_ipm_kab = new BarPlot($nilai_data_ipm_per_kab);
                $b1plot_ipm_kab->SetColor("white");
                $b1plot_ipm_kab->SetFillColor("#0000FF");  
                $gbplot_kab_ipm = new GroupBarPlot(array($b1plot_ipm_kab));
                $graph_bar_ipm_kab->Add($gbplot_kab_ipm);
                    
                    //Indeks Pembangunan Manusia
               $graph_ipm->Stroke($this->picture.'/'.$picture_ipm.'.png');                
               $graph_bar_ipm->Stroke($this->picture.'/'.$picture_ipm_bar.'.png');
               $graph_r_ipm->Stroke($this->picture.'/'.$picture_ipm_r.'.png');
               $graph_bar_ipm_kab->Stroke($this->picture.'/'.$picture_ipm_k.'.png');
               
               
                    //Gini Rasio
                    $sql_gr = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_gr = $this->db->query($sql_gr);
                    foreach ($list_gr->result() as $row_gr) {
                        $tahun_gr[]    = $bulan[$row_gr->periode]."-".$row_gr->tahun;
                        $gr_tahun[]    = $bulan_tahun[$row_gr->periode]." ".$row_gr->tahun;
                        $nilaiData_gr[] = (float)$row_gr->nilai;
                    }
                    $datay_gr = $nilaiData_gr;
                    $tahun_gr = $tahun_gr;
                    $sql_gr2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    
                    $list_gr2 = $this->db->query($sql_gr2);
                    foreach ($list_gr2->result() as $row_gr2) {
                        $tahun_gr2[]   = $row_gr2->tahun;
                        //$periode     = $row_gr2->periode;                        
                        $nilaiData_gr2[] = (float)$row_gr2->nilai;
                        $nilaiData_gr22[] = number_format((float)$row_gr2->nilai,2,",",".");
                        $sumber_gr       = $row_gr2->sumber;
                        $periode_gr_id[]    = $row_gr2->id_periode;
                        $tahun_gr21[]    = $bulan[$row_gr2->periode]."-".$row_gr2->tahun;
                        $t_gr2               = $row_gr2->t_m_rpjmn;
                        if($t_gr2==0){ $nilaiRgr = '-'; }
                        else{ $nilaiRgr= (float)$row_gr2->t_m_rpjmn; }
                        $n_rpjmn_gr2[]       = $nilaiRgr;
                        
                        $t_rkpd_gr2 = $row_gr2->t_rkpd;
                        if($t_rkpd_gr2==0){ $nilaiRKPDgr = '-'; }
                        else{ $nilaiRKPDgr = (float)$row_gr2->t_rkpd; }
                        $n_rkpd_gr2[] = $nilaiRKPDgr;
                        $t_rkp_gr2    = $row_gr2->t_k_rkp;
                        if($t_rkp_gr2==0){ $nilaiRKPgr = '-'; }
                        else{ $nilaiRKPgr = (float)$row_gr2->t_k_rkp; }
                        $n_rkp_gr2[] = $nilaiRKPgr;
                    }
                    $datay_gr2 = $nilaiData_gr2;
                    $tahun_gr2 = $tahun_gr2;
                    $data_rpjmn_gr2      = $n_rpjmn_gr2;
                    $nilaiTarget_RKPD_gr = $n_rkpd_gr2;
                    $nilaiTarget_RKP_gr  = $n_rkp_gr2;
                    $max_k_gr ="";
                    $periode_gr_max   = max($periode_gr_id);
                    $periode_gr_max_2 = $periode_gr_max-100;
                    $periode_gr_tahun=max($tahun_gr21)." Antar Provinsi" ;
                    //$periode_gr_tahun=$bulan[max($periode)]." ".max($tahun_gr2)." Antar Provinsi" ;
                    if($nilaiData_gr2[3] > $nilaiData_gr2[5]){ 
                        $mm_gr="menurun";
                        if($nilaiData_gr[5]>$nilaiData_gr2[5]){ $ba_gr="di bawah"; }else{ $ba_gr="di atas"; }
                    } else {
                        $mm_gr="meningkat";
                        if($nilaiData_gr[5]>$nilaiData_gr2[5]){ $ba_gr="di bawah"; }else{ $ba_gr="di atas"; }
                    }
                    if($nilaiTarget_RKPD_gr[5]=='-'){
                        $gr_rkpd_rkp1="";
                    }else{
                        if($nilaiData_gr2[5] > $nilaiTarget_RKPD_gr[5]){ $rkpdgr="diatas"; }
                        else{ $rkpdgr="dibawah"; }
                        $gr_rkpd_rkp1 = "Gini rasio ". $xname ." pada ".$gr_tahun[5]." berada ".$rkpdgr." target RKPD ". $xname ."(".number_format($nilaiTarget_RKPD_gr[5],3,",",".").").";
                    }
                    $max_n_gr    ="Gini rasio ". $xname ." pada ".$gr_tahun[5]." ".$mm_gr." dibandingkan dengan ".$gr_tahun[3].". Pada ".$gr_tahun[5]." gini rasio ". $xname ." adalah sebesar ". number_format($nilaiData_gr2[5],3,",",".") ." sedangkan pada ".$gr_tahun[3]."  gini rasio tercatat sebesar ".number_format($nilaiData_gr2[3],3,",",".").". ";
                    $max_p_gr    ="Gini rasio ". $xname ." pada ".$gr_tahun[5]." berada ".$ba_gr." capaian nasional. Gini rasio nasional pada ".$gr_tahun[5]." adalah sebesar ".number_format($nilaiData_gr[5],3,",",".") .". ";
                    $gr_rkpd_rkp =$gr_rkpd_rkp1;
                    
                    $perbandingan_gr ="select p.label as label,p.nama_provinsi, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='7' AND e.id_periode='$periode_gr_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='7' AND id_periode='$periode_gr_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    
                    $list_gr_per = $this->db->query($perbandingan_gr);
                    foreach ($list_gr_per->result() as $row_gr_per) {
                        $label_gr[]     = $row_gr_per->label;
                        $nilai_gr_per[] = $row_gr_per->nilai;
                        $nilai_gr_r1[$row_gr_per->label] = $row_gr_per->nilai;
                        $nilai_gr_r2[$row_gr_per->nama_provinsi] = $row_gr_per->nilai;
                    }
                    $label_data_gr     = $label_gr;
                    $nilai_data_gr_per = $nilai_gr_per;
                    $nilai_data_gr_r1  = $nilai_gr_r1;
                    $nilai_data_gr_r2  = $nilai_gr_r2;
                    $rankinggr         = $nilai_data_gr_r1;
                    arsort($rankinggr);
                    $nrgr=1;
                    foreach($rankinggr as $xgr=>$xgr_value){
                        if($xgr==$label_pe){
                            $rengkinggr_pro=$nrgr++;
                        }
                        $urutan_pro_gr=$xgr . $xgr_value . $xgr_value .$nrgr++;
                    }
                    $nilai_gr_p_max = max($nilai_data_gr_r1);  //nila paling besar
                    $nilai_gr_p_min = min($nilai_data_gr_r1);  //nila paling rendah
                    $nama_gr1       = $nilai_data_gr_r2;
                    arsort($nama_gr1);
                    $nama_gr2=array_keys($nama_gr1);               
                    $gr_perbandingan_pro = "Perbandingan gini rasio antar 34 provinsi menunjukkan bahwa gini rasio ". $xname ." 
                                         pada ".$gr_tahun[5]." berada pada urutan ke-".$rengkinggr_pro.".
                           Provinsi dengan gini rasio tertinggi adalah ".array_shift($nama_gr2)." (".number_format($nilai_gr_p_max,3,",",".")."),
                            sedangkan yang terendah adalah ".end($nama_gr2)." (".number_format($nilai_gr_p_min,3,",",".")."). ";
                    
                    
                    //radar
                    $perbandingan_gr2 ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='7' AND e.id_periode='$periode_gr_max_2') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='7' AND id_periode='$periode_gr_max_2' 
                            group by wilayah) group by wilayah order by wilayah asc";
        
                    $list_gr_per2 = $this->db->query($perbandingan_gr2);
                    foreach ($list_gr_per2->result() as $row_gr_per2) {
                        $label_gr2[]     = $row_gr_per2->label;
                        $nilai_gr_per2[] = $row_gr_per2->nilai;
                        $nilai_gr_r2     = $row_gr_per2->nilai;
                        if ($nilai_gr_r2 <= 0){
                            $nilai_gr2 = 0;
                        }else{
                            $nilai_gr2= $row_gr_per2->nilai;
                        }
                        $nilai_gr_r22[]=$nilai_gr2;
                    }
                    $label_data_gr2     = $label_gr2;
                    $nilai_data_gr_per2 = $nilai_gr_per2;
                    $nilai_data_gr_r2   = $nilai_gr_r22;
                    
                    $label_gr2        = $label_gr2;
                    $nilai_data_gr_r2 = $nilai_gr_r22;
                    $array_tiga_gr    = array();
                    for($i=0;$i< count($nilai_data_gr_per);$i++){
                        $array_tiga_gr[$i]=$nilai_data_gr_per[$i]-$nilai_data_gr_r2[$i];
                    }
                    $kombinasi_gr  = array_combine($label_gr2,$array_tiga_gr);
                    $kombinasi_gr2 = array_combine($label_gr2,$array_tiga_gr);
                    asort($kombinasi_gr2); //tinggi-rendah
                    $kombinasi_gr3 = array_combine($label_gr2,$array_tiga_gr);
                    asort($kombinasi_gr3);
                    $kombinasi_gr4 = $kombinasi_gr3;
                
                $ngr=1;
                    foreach($kombinasi_gr2 as $xgr=>$xgr_value){
                        if($xgr==$label_pe){
                            $rengkinggr_p=$ngr++;
                        }
                        $urutan_pro_gr=$xgr . $xgr_value . $xgr_value .$ngr++;                      
                    }
                $nilai_gr_per_p_max = max($kombinasi_gr2);  //nila paling besar
                $nilai_gr_per_p_min = min($kombinasi_gr2);  //nila paling rendah
                $kombinasi_gr4       = array_combine($label_gr2,$array_tiga_gr);
                asort($kombinasi_gr4);
                $label_p_gr         = array_keys($kombinasi_gr4);
                $label_gr_per_p_max = array_shift($label_p_gr); //label paling besar
                $label_gr_per_p_min = end($label_p_gr);     //label paling kecil
                $nper_gr=$kombinasi_gr2[$label_pe];

                $gr_perbandingan_2th = "Perbandingan gini rasio ".$gr_tahun[3]." dan ".$gr_tahun[5]." menunjukkan bahwa provinsi yang mengalami 
                                        penurunan gini rasio terbesar adalah ".$label_gr_per_p_max." (".$nilai_gr_per_p_min.").
                                        Dari sisi perubahan gini rasio periode ".$gr_tahun[3]." dengan ".$gr_tahun[5].", "
                        . "             ". $xname ." berada pada urutan ke-".$rengkinggr_p." dengan gini rasio ".$mm_gr." sebesar ".$nper_gr.". ";
 

                
                    //perbandingan kab
                    $th_gr_kab ="select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='".$id_pro."' AND e.id_indikator='7' ";
                    $t_list_kab_gr = $this->db->query($th_gr_kab);
                    foreach ($t_list_kab_gr->result() as $row_t_gr_kab) { $perio_gr = $row_t_gr_kab->perio; }
                    $gr_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$id_pro."' and (e.id_indikator='7' AND e.id_periode='$perio_gr') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='7' AND id_periode='$perio_gr' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_gr_per = $this->db->query($gr_kab);
                    foreach ($list_kab_gr_per->result() as $row_gr_kab_per) {
                        $nilai_gr_per_kab[] = $row_gr_kab_per->nilai;
                        $posisi_gr          = strpos($row_gr_kab_per->label, "Kabupaten");
                        if ($posisi_gr !== FALSE){
                            $label_gr11=substr( $row_gr_kab_per->label,0,3).". ".substr( $row_gr_kab_per->label,10);
                        }else{
                            $label_gr11=$row_gr_kab_per->label;
                        }
                        $label_gr1[]=$label_gr11; 
                        $label_gr1_k[$label_gr11]=$row_gr_kab_per->nilai; 
                        $tahun_gr_k        = $bulan[$row_gr_kab_per->periode]."-".$row_gr_kab_per->tahun;
                        $gr_k_tahun        = $bulan_tahun[$row_gr_kab_per->periode]." ".$row_gr_kab_per->tahun;
                    }
                    $label_data_gr_kab     = $label_gr1;            // array kab/kota
                    $nilai_data_gr_per_kab = $nilai_gr_per_kab;     // array nilai
                    $nilai_kk_gr_per_kab   = $label_gr1_k;          //array kab dan nilai
                    $namagr_k1=$nilai_kk_gr_per_kab;
                    arsort($namagr_k1);                              //urutan kab/kota nilai terbesar ke terkecil
                    $namagr_k2=array_keys($namagr_k1); //urutan kab/kota nama terbesar ke terkecil
                    $nilai_gr_per_kab_max = max($nilai_kk_gr_per_kab);  //nila paling besar
                    $nilai_gr_per_kab_min = min($nilai_kk_gr_per_kab);       // nilai paling kecil
                    $selisih_gr     = $nilai_gr_per_kab_max-$nilai_gr_per_kab_min;
                    arsort($nilai_gr_per_kab);
                    $tahun_gr_kab = $tahun_gr_k." Antar Kabupaten/kota ".$judul ;
                   $gr_perbandingan_kab="Perbandingan gini rasio antar kabupaten/kota di ". $xname ." memperlihatkan 
                                            bahwa pada ".$gr_k_tahun." daerah dengan gini rasio tertinggi adalah ".array_shift($namagr_k2)." (".number_format($nilai_gr_per_kab_max,3,",",".")."), "
                           . "              sedangkan daerah dengan gini rasio terendah adalah ".end($namagr_k2)." (".number_format($nilai_gr_per_kab_min,3,",",".").").
                                            Selisih tingkat gini rasio tertinggi dan terendah di ". $xname ." Pada periode ".$gr_k_tahun." sebesar ".number_format($selisih_gr,3,",",".").".";
                  
//                  print_r($max_n_gr);
//                    echo '<br/>';echo '<br/>';
//                print_r($max_p_gr);
//                echo '<br/>';echo '<br/>';
//                 print_r($gr_rkpd_rkp);
//                echo '<br/>';echo '<br/>';
//                print_r($gr_perbandingan_pro);
//                echo '<br/>';echo '<br/>';
//                print_r($gr_perbandingan_2th);
//                echo '<br/>';echo '<br/>';
//                print_r($gr_perbandingan_kab);exit();                   
                    
                                    //Gini Rasio
                $graph_gr = new Graph(500,230);
                $graph_gr->SetScale("textlin");
                $theme_class_gr= new UniversalTheme;
                $graph_gr->SetTheme($theme_class_gr);
                $graph_gr->SetMargin(40,20,33,58);
                //$graph_gr->title->Set('Perkembangan Gini Rasio');
                $graph_gr->SetBox(false);
                $graph_gr->yaxis->HideZeroLabel();
                $graph_gr->yaxis->HideLine(false);
                $graph_gr->yaxis->HideTicks(false,false);
                $graph_gr->xaxis->SetTickLabels($tahun_gr);
                $graph_gr->ygrid->SetFill(false);
                $graph_gr->legend->SetFrameWeight(1);
                    $graph_gr->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_gr->legend->SetMarkAbsSize(8);
                    
                $graph_bar_gr = new Graph(600,230);
                $graph_bar_gr->img->SetMargin(40,20,20,100);
                $graph_bar_gr->SetScale("textlin");
                $graph_bar_gr->SetMarginColor("lightblue:1.1");
                $graph_bar_gr->SetShadow();
                $graph_bar_gr->title->SetMargin(8);
                $graph_bar_gr->title->SetColor("darkred");
                $graph_bar_gr->ygrid->SetFill(false);
                $graph_bar_gr->xaxis->SetTickLabels($label_data_gr);
                $graph_bar_gr->yaxis->HideLine(false);
                $graph_bar_gr->yaxis->HideTicks(false,false);                
                $graph_bar_gr->xaxis->SetLabelAngle(90);
                
                 //Gini Rasio
                                $p1_gr = new LinePlot($datay_gr);
                $graph_gr->Add($p1_gr);
                $p1_gr->SetColor("#0000FF");
                $p1_gr->SetLegend('Indonesia');
                $p1_gr->SetCenter();
                $p1_gr->mark->SetType(MARK_FILLEDCIRCLE,'',9.0);
                $p1_gr->mark->SetSize(3);
                $p1_gr->mark->SetColor('#0000FF');
                $p1_gr->mark->SetFillColor('#0000FF');
                $p1_gr->value->SetMargin(20);
                $p1_gr->value->SetFormat('%0.3f');
                $p1_gr->value->SetAlign('left','center');
                $p1_gr->value->SetColor('#0000FF','darkred');
                $p1_gr->value->Show();
//                    $p1_gr = new LinePlot($datay_gr);
//                    $graph_gr->Add($p1_gr);
//                    $p1_gr->SetColor("#0000FF");
//                    $p1_gr->SetLegend('Indonesia');
//                    $p1_gr->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
//                    $p1_gr->mark->SetColor('#0000FF');
//                    $p1_gr->mark->SetFillColor('#0000FF');
//                    $p1_gr->SetCenter();
//                    $p1_gr->value->Show();
//                    $p1_gr->value->SetColor('#0000FF');
//                    $p1_gr->value->SetFormat('%0.3f');
//                    
                $p2_gr = new LinePlot($datay_gr2);
                $graph_gr->Add($p2_gr);
                $p2_gr->SetColor("#000000");
                $p2_gr->SetLegend($xname);
                $p2_gr->SetCenter();
                $p2_gr->SetStyle("dotted");
                $p2_gr->mark->SetType(MARK_UTRIANGLE,9.0);
                $p2_gr->mark->SetSize(14);
                $p2_gr->mark->SetColor('#000000');
                $p2_gr->mark->SetFillColor('#000000');
                $p2_gr->value->SetColor('#000000');
                $p2_gr->value->SetMargin(20);
                $p2_gr->value->SetFormat('%0.3f');
                $p2_gr->value->SetAlign('right','center');
                $p2_gr->value->SetColor('#000000','darkred');
                $p2_gr->value->Show();
                    
                    
                        $p4_gr = new LinePlot($nilaiTarget_RKPD_gr);
                        $graph_gr->Add($p4_gr);
                        $p4_gr->SetColor("#006400");
                        $p4_gr->SetLegend('Target RKPD');
                        $p4_gr->SetCenter();
                        $p4_gr->mark->SetType(MARK_STAR,'',9.0);
                        $p4_gr->mark->SetSize(10);
                        $p4_gr->mark->SetColor('#006400');
                        $p4_gr->mark->SetFillColor('#006400');
                        $p4_gr->value->SetColor('#006400');
                        $p4_gr->value->SetMargin(14);
                        $p4_gr->value->SetFormat('%0.3f');
                        $p4_gr->value->SetAlign('right','center');
                        $p4_gr->value->Show();    
                    
                    $b1plot_gr_per = new BarPlot($nilai_data_gr_per);
                    $gbplot_gr_per = new GroupBarPlot(array($b1plot_gr_per));
                    $graph_bar_gr->Add($gbplot_gr_per);
                    $b1plot_gr_per->SetColor("white");
                    $b1plot_gr_per->SetFillColor("#0000FF");
                    //$b1plot_gr_per->value->Show();
                    $b1plot_gr_per->value->SetFormat('%0.3f');
                    
                    //Radar
                    // Some data to plot
                $titles_gr=$label_data_gr;
                $data_r_gr = $nilai_data_gr_per;
                $data_r_gr2 = $nilai_data_gr_r2;
                // Create the graph and the plot
                $graph_r_gr = new RadarGraph(500,380);
                //$graph_r_gr->title->Set('Radar Provinsi (%)');
                //$graph_r_pe->title->SetFont(FF_VERDANA,FS_NORMAL,12);
                $graph_r_gr->SetTitles($titles_gr);
                $graph_r_gr->SetCenter(0.5,0.55);
                $graph_r_gr->HideTickMarks();
                //$graph_r_pe->SetColor('lightgreen@0.7');
                $graph_r_gr->axis->SetColor('darkgray');
                $graph_r_gr->grid->SetColor('darkgray');
                $graph_r_gr->grid->Show();
                //$graph_r_pe->axis->title->SetFont(FF_ARIAL,FS_NORMAL,12);
                $graph_r_gr->axis->title->SetMargin(5);
                $graph_r_gr->SetGridDepth(DEPTH_BACK);
                $graph_r_gr->SetSize(0.6);
                $plot_gr = new RadarPlot($data_r_gr);
                $plot_gr->SetColor('red@0.2');
                $plot_gr->SetLineWeight(1);
                //$plot->SetFillColor('red@0.7');
                $plot_gr->mark->SetType(MARK_IMG_SBALL,'red');
                $plot_gr->SetLegend($tahun_gr[5]);
                // Add the plot and display the graph
                $plot2_gr = new RadarPlot($data_r_gr2);
                $plot2_gr->SetColor('forestgreen');
                $plot2_gr->SetLineWeight(1);
                $plot2_gr->SetFillColor('forestgreen@0.9');
                $plot2_gr->SetLegend($tahun_gr[3]);
                //$plot2->mark->SetType(MARK_IMG_SBALL,'darkred');
                // Add the plot and display the graph
                $graph_r_gr->Add($plot_gr);
                $graph_r_gr->Add($plot2_gr);
                
                //Perbandingan Kabupaten
                $graph_bar_gr_kab = new Graph(500,250);
                $graph_bar_gr_kab->SetScale("textlin");
                $graph_bar_gr_kab->SetY2Scale("lin",0,90);
                $graph_bar_gr_kab->SetY2OrderBack(false);
                $theme_class_bar_gr_kab=new UniversalTheme;
                $graph_bar_gr_kab->SetTheme($theme_class_bar_gr_kab);
                $graph_bar_gr_kab->SetMargin(40,20,20,150);
                $graph_bar_gr_kab->ygrid->SetFill(false);
                $graph_bar_gr_kab->xaxis->SetTickLabels($label_data_gr_kab);
                $graph_bar_gr_kab->xaxis->SetLabelAngle(90);
                $graph_bar_gr_kab->yaxis->HideLine(false);
                $graph_bar_gr_kab->yaxis->HideTicks(false,false);
                
                $b1plot_gr_kab = new BarPlot($nilai_data_gr_per_kab);
                $b1plot_gr_kab->SetColor("white");
                $b1plot_gr_kab->SetFillColor("#0000FF");  
                $gbplot_kab_gr = new GroupBarPlot(array($b1plot_gr_kab));
                $graph_bar_gr_kab->Add($gbplot_kab_gr);
                    
                    //Gini Rasio
               $graph_gr->Stroke($this->picture.'/'.$picture_gr.'.png'); 
               $graph_bar_gr->Stroke($this->picture.'/'.$picture_gr_bar.'.png');
               $graph_r_gr->Stroke($this->picture.'/'.$picture_gr_r.'.png');               
               $graph_bar_gr_kab->Stroke($this->picture.'/'.$picture_gr_k.'.png');
               
               
                    
                }
               
                
                

                
                
                if($provinsi == '' ){  
                     //perkembangan pertumbuhan ekonomi
                    //$p1_ppe = new LinePlot($nl);
                    $p1_ppe = new LinePlot($nilaiData1);
                    $graph_ppe->Add($p1_ppe);
                    $p1_ppe->SetColor("#0000FF");
                    $p1_ppe->SetLegend('Indonesia');
                    $p1_ppe->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
                    $p1_ppe->mark->SetColor('#0000FF');
                    $p1_ppe->mark->SetFillColor('#0000FF');
                    $p1_ppe->SetCenter();
                    $p1_ppe->value->Show();
                    //$p1_ppe->grid->SetColor('darkgrey');
                    //$p1_ppe->SetFormat('$%01.2f'); 
                    //$graph_ppe->legend->SetFormat('%d');
                    $p1_ppe->value->SetAlign('center');
                    $graph_ppe->legend->SetFrameWeight(2);
                    $graph_ppe->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_ppe->legend->SetMarkAbsSize(8);
                    
                    //adhb
                    $b1plot_adhb = new BarPlot($datay_adhb1);
                    $gbplot_adhb = new GroupBarPlot(array($b1plot_adhb));
                    $graph_adhb->Add($gbplot_adhb);
                    $b1plot_adhb->SetColor("white");
                    $b1plot_adhb->SetFillColor("#0000FF");
                    $b1plot_adhb->SetLegend("Indonesia");
                    $b1plot_adhb->SetWidth(50);          
                    
                    //adhk
                    $adhk1 = new LinePlot($nilaiData1);
                    $graph_adhk->Add($adhk1);
                    $adhk1->SetColor("#0000FF");
                    $adhk1->SetLegend('Indonesia');
                    $adhk1->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
                    $adhk1->mark->SetColor('#0000FF');
                    $adhk1->mark->SetFillColor('#0000FF');
                    $adhk1->SetCenter();
                    $adhk1->value->Show();
                    //$p1_ppe->SetFormt('$%01.2f'); 
                    $graph_ppe->legend->SetFrameWeight(2);
                    $graph_ppe->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_ppe->legend->SetMarkAbsSize(8);                    
                    
                    //jumlah pengangguran
                    $b1plot_jp = new BarPlot($datay_jp);
                    $gbplot_jp = new GroupBarPlot(array($b1plot_jp));
                    $graph_jp->Add($gbplot_jp);
                    $b1plot_jp->SetColor("white");
                    $b1plot_jp->SetFillColor("#0000FF");
                    $b1plot_jp->SetLegend("Indonesia");
                    $b1plot_jp->SetWidth(50);
                    
                    //tingkat pengangguran terbuka
//                    $p1_tpt = new LinePlot($datay_tpt);
//                    $graph_tpt->Add($p1_tpt);
//                    $p1_tpt->SetColor("#0000FF");
//                    $p1_tpt->SetLegend('Indonesia');
//                    $p1_tpt->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
//                    $p1_tpt->mark->SetColor('#0000FF');
//                    $p1_tpt->mark->SetFillColor('#0000FF');
//                    $p1_tpt->SetCenter();
//                    $p1_tpt->value->Show();
//                    $graph_tpt->legend->SetFrameWeight(1);
//                    $graph_tpt->legend->SetColor('#4E4E4E','#00A78A');
//                    $graph_tpt->legend->SetMarkAbsSize(8);
                    $b1plot_tpt  = new BarPlot($datay_tpt);
                    $gbplot_tpt  = new GroupBarPlot(array($b1plot_tpt));
                    $graph_tpt->Add($gbplot_tpt);
                    $b1plot_tpt->SetColor("white");
                    $b1plot_tpt->SetFillColor("#0000FF");
                    $b1plot_tpt->SetLegend("Indonesia");
                    $b1plot_tpt->SetWidth(20);
                   
                    //Indeks Pembangunan Manusia
                    $p1_ipm = new LinePlot($datay_ipm);
                    $graph_ipm->Add($p1_ipm);
                    $p1_ipm->SetColor("#0000FF");
                    $p1_ipm->SetLegend('Indonesia');
                    $p1_ipm->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
                    $p1_ipm->mark->SetColor('#0000FF');
                    $p1_ipm->mark->SetFillColor('#0000FF');
                    $p1_ipm->SetCenter();
                    $p1_ipm->value->Show();
                    $graph_ipm->legend->SetFrameWeight(1);
                    $graph_ipm->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_ipm->legend->SetMarkAbsSize(8);
                    //Gini Rasio
                    $p1_gr = new LinePlot($datay_gr);
                    $graph_gr->Add($p1_gr);
                    $p1_gr->SetColor("#0000FF");
                    $p1_gr->SetLegend('Indonesia');
                    $p1_gr->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
                    $p1_gr->mark->SetColor('#0000FF');
                    $p1_gr->mark->SetFillColor('#0000FF');
                    $p1_gr->SetCenter();
                    $p1_gr->value->Show();
                    $graph_gr->legend->SetFrameWeight(1);
                    $graph_gr->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_gr->legend->SetMarkAbsSize(8);
                    //Angka Harapan Hidup
                    $p1_ahh = new LinePlot($datay_ahh);
                    $graph_ahh->Add($p1_ahh);
                    $p1_ahh->SetColor("#0000FF");
                    $p1_ahh->SetLegend('Indonesia');
                    $p1_ahh->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
                    $p1_ahh->mark->SetColor('#0000FF');
                    $p1_ahh->mark->SetFillColor('#0000FF');
                    $p1_ahh->SetCenter();
                    $p1_ahh->value->Show();
                    $graph_ahh->legend->SetFrameWeight(1);
                    $graph_ahh->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_ahh->legend->SetMarkAbsSize(8);
                    //Rata-rata Lama Sekolah
                    $p1_rls = new LinePlot($datay_rls);
                    $graph_rls->Add($p1_rls);
                    $p1_rls->SetColor("#0000FF");
                    $p1_rls->SetLegend('Indonesia');
                    $p1_rls->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
                    $p1_rls->mark->SetColor('#0000FF');
                    $p1_rls->mark->SetFillColor('#0000FF');
                    $p1_rls->SetCenter();
                    $p1_rls->value->Show();
                    $graph_rls->legend->SetFrameWeight(1);
                    $graph_rls->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_rls->legend->SetMarkAbsSize(8);
                    //Rata-rata Lama Sekolah
                    $p1_hls = new LinePlot($datay_hls);
                    $graph_hls->Add($p1_hls);
                    $p1_hls->SetColor("#0000FF");
                    $p1_hls->SetLegend('Indonesia');
                    $p1_hls->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
                    $p1_hls->mark->SetColor('#0000FF');
                    $p1_hls->mark->SetFillColor('#0000FF');
                    $p1_hls->SetCenter();
                    $p1_hls->value->Show();
                    $graph_hls->legend->SetFrameWeight(1);
                    $graph_hls->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_hls->legend->SetMarkAbsSize(8);
                    //pengeluaran per kapita
                    $p1_ppk = new LinePlot($datay_ppk);
                    $graph_ppk->Add($p1_ppk);
                    $p1_ppk->SetColor("#0000FF");
                    $p1_ppk->SetLegend('Indonesia');
                    $p1_ppk->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
                    $p1_ppk->mark->SetColor('#0000FF');
                    $p1_ppk->mark->SetFillColor('#0000FF');
                    $p1_ppk->SetCenter();
                    $p1_ppk->value->Show();
                    $graph_ppk->legend->SetFrameWeight(1);
                    $graph_ppk->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_ppk->legend->SetMarkAbsSize(8);
                    //tingkat kemiskinan
                    $p1_tk = new LinePlot($datay_tk);
                    $graph_tk->Add($p1_tk);
                    $p1_tk->SetColor("#0000FF");
                    $p1_tk->SetLegend('Indonesia');
                    $p1_tk->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
                    $p1_tk->mark->SetColor('#0000FF');
                    $p1_tk->mark->SetFillColor('#0000FF');
                    $p1_tk->SetCenter();
                    $p1_tk->value->Show();
                    $graph_tk->legend->SetFrameWeight(1);
                    $graph_tk->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_tk->legend->SetMarkAbsSize(8);
                    //Indeks Kedalaman Kemiskinan
                    $p1_ikk = new LinePlot($datay_idk);
                    $graph_ikk->Add($p1_ikk);
                    $p1_ikk->SetColor("#0000FF");
                    $p1_ikk->SetLegend('Indonesia');
                    $p1_ikk->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
                    $p1_ikk->mark->SetColor('#0000FF');
                    $p1_ikk->mark->SetFillColor('#0000FF');
                    $p1_ikk->SetCenter();
                    $p1_ikk->value->Show();
                    $graph_ikk->legend->SetFrameWeight(1);
                    $graph_ikk->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_ikk->legend->SetMarkAbsSize(8);
                    //Jumlah Penduduk Miskin
                    $p1_jpk = new LinePlot($datay_jpk);
                    $graph_jpk->Add($p1_jpk);
                    $p1_jpk->SetColor("#0000FF");
                    $p1_jpk->SetLegend('Indonesia');
                    $p1_jpk->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
                    $p1_jpk->mark->SetColor('#0000FF');
                    $p1_jpk->mark->SetFillColor('#0000FF');
                    $p1_jpk->SetCenter();
                    $p1_jpk->value->Show();
                    $graph_jpk->legend->SetFrameWeight(1);
                    $graph_jpk->legend->SetColor('#4E4E4E','#00A78A');
                    $graph_jpk->legend->SetMarkAbsSize(8);
                }
                
                
                
                $logo         = "logo_bappenas.png";
                $halaman_satu = $this->view_dir."/halaman_satu";
                //$no=1;
                $data_page = array( 
                    "halaman_satu"      => $halaman_satu,
                    "logo_picture"      => base_url("assets/js/img/".$logo),
                    //"daftar_isi  "      => base_url("assets/js/img/".$daftar_isi),
                    "logo_provinsi"     => base_url("assets/images/logopropinsi/".$logopro),
                    "judul"             => $judul,
                    //"no"=> $no,
                    //pertumbuhan ekonomi
                    "halaman_PE"        => base_url("picture/laporan_ppd/".$picture_pe.".png"),
                    "capaian_n_pe"      => $max_pe,
                    "capaian_p_pe"      => $max_pe_p,
                    "capaian_k_pe"      => $max_pe_k,
                    "desc_peek"         => $peek_d,
                    "sumber_pe"             => $sumber_pe,
                    "tahun_pe_g1"         => $tahun_pe_g1,
                    "tahun_max_pe"          => $tahun_pe_max,
                    "tahun_max_pe_a"        => $tahun_pe_anatar,
                    "tahun_max_kab"         => $tahun_pe_kab,
                    "pe_rkpd_rkp"           => $pe_rkpd_rkp,
                    "pe_perbandingan_pro"   => $pe_perbandingan_pro,
                   // "pe_perbandingan_2th"   => $pe_perbandingan_2th,
                    "pe_perbandingan_kab"   => $pe_perbandingan_kabb,
                    "perbandingan_PE"     => base_url("picture/laporan_ppd/".$picture_pe_bar.".png"),
                    "perbandingan_PE_kan" => base_url("picture/laporan_ppd/".$picture_rpe.".png"),
                    "radar_PE"            => base_url("picture/laporan_ppd/".$picture_r_pe.".png"),
                    //struktur PDRB
                    "struktur_pdrb"     => base_url("picture/laporan_ppd/".$picture_s_pdrb.".png"),
                    "struktur_sektoral"     => base_url("picture/laporan_ppd/".$picture_sektoral.".png"),
                    "struktur_ctc"     => base_url("picture/laporan_ppd/".$picture_ctc.".png"),
                    "pdrb_d"    => $pdrb_d,
                    
                    //tingkat kemiskinan
                    "halaman_tk"        => base_url("picture/laporan_ppd/".$picture_tk.".png"),
                    "capaian_n_tk"      => $max_n_tk,
                    "capaian_p_tk"      => $max_p_tk,
                    "capaian_k_tk"      => $max_k_tk,
                    "desc_tk"           => $tk_d,
                    "sumber_tk"         => $sumber_tk,
                    "tahun_max_tk"      => $periode_tk_tahun,
                    "tahun_max_tk_a"    =>$periode_tk_a,
                    "tahun_max_tk_k"    => $tahun_tk_kab,
                    "tk_rkpd_rkp"           => $tk_rkpd_rkp,
                    "tk_perbandingan_pro"   => $tk_perbandingan_pro,
                    "tk_perbandingan_2th"   => $tk_perbandingan_2th,
                    "tk_perbandingan_kab"   => $tk_perbandingan_kab,
                    "perbandingan_tk"   => base_url("picture/laporan_ppd/".$picture_tk_bar.".png"),
                    "radar_tk"          => base_url("picture/laporan_ppd/".$picture_r_tk.".png"),
                    "kab_tk"          => base_url("picture/laporan_ppd/".$picture_k_tk.".png"),
                    //jumlah penduduk miskin
                    "halaman_jpk"       => base_url("picture/laporan_ppd/".$picture_jpk.".png"),
                    "capaian_n_jpk"     => $max_n_jpk,
                    "capaian_p_jpk"     => $max_p_jpk,
                    "capaian_k_jpk"     => $max_k_jpk,
                    "desc_jpm"          => $jpm_d,
                    "sumber_jpk"        => $sumber_jpk,
                    "periode_max_jpk"   => $periode_jpk_max_p,
                    "tahun_max_jpk"     => $periode_jpk_tahun,
                    "tahun_jpk_kab"     => $tahun_jpk_kab,
                    "jpk_perbandingan_pro"   =>$jpk_perbandingan_pro,
                    "jpk_perbandingan_2th"   => $jpk_perbandingan_2th,
                    "jpk_perbandingan_kab"   => $jpk_perbandingan_kab,
                    "perbandingan_jpk"  => base_url("picture/laporan_ppd/".$picture_jpk_bar.".png"),
                    "radar_jpk"         => base_url("picture/laporan_ppd/".$picture_jpk_r.".png"),
                    "kab_jpk"           => base_url("picture/laporan_ppd/".$picture_jpk_k.".png"),
                    //tingkat pengangguran terbuka
                    "halaman_tpt"       => base_url("picture/laporan_ppd/".$picture_tpt.".png"),
                    "sumber_tpt"        => $sumber_tpt,
                    "capaian_n_tpt"     => $max_tpt,
                    "capaian_p_tpt"     => $max_tpt_p,
                    "capaian_k_tpt"     => $max_tpt_k,
                    "desc_tpt"          => $tpt_d,
                    "tahun_max_tpt"     => $periode_tpt_tahun,
                    "tahun_tpt_kab"    => $tahun_tpt_kab,
                    "tpt_rkpd_rkp"      => $tpt_rkpd_rkp,
                    "tpt_perbandingan_pro"   => $tpt_perbandingan_pro,
                    "tpt_perbandingan_2th"   => $tpt_perbandingan_2th,
                    "tpt_perbandingan_kab"   => $tpt_perbandingan_kab,
                    "perbandingan_tpt"  => base_url("picture/laporan_ppd/".$picture_tpt_bar.".png"),
                    "radar_tpt"         => base_url("picture/laporan_ppd/".$picture_tpt_r.".png"),
                    "kab_tpt"           => base_url("picture/laporan_ppd/".$picture_tpt_k.".png"),
                    
                    //jumlah Pengangguran
                    "halaman_jp"        => base_url("picture/laporan_ppd/".$picture_jp.".png"),
                    "sumber_jp"         => $sumber_jp,
                    "capaian_n_jp"      => $max_jp,
                    "capaian_p_jp"      => $max_jp_p,
                    "capaian_k_jp"      => $max_jp_k,
                    "desc_jp"           =>  $jp_d,
                    "tahun_max_jp"      => $periode_jp_tahun,
                    "tahun_jp_kab"      => $tahun_jp_kab,
                    "jp_perbandingan_pro" => $jp_perbandingan_pro,
                    "jp_perbandingan_2th"   => $jp_perbandingan_2th,
                    "jp_perbandingan_kab" => $jp_perbandingan_kab,
                    "perbandingan_jp"   => base_url("picture/laporan_ppd/".$picture_jp_bar.".png"),
                    "radar_jp"          => base_url("picture/laporan_ppd/".$picture_jp_r.".png"),
                    "kab_jp"            => base_url("picture/laporan_ppd/".$picture_jp_k.".png"),
                    
                    //indek Pembangunan Manusia
                    "halaman_ipm"       => base_url("picture/laporan_ppd/".$picture_ipm.".png"),
                    "capaian_n_ipm"     => $max_ipm,
                    "capaian_p_ipm"     => $max_ipm_p,
                    "capaian_k_ipm"     => $max_ipm_k,
                    "desc_ipm"          => $ipm_d,
                    "sumber_ipm"        => $sumber_ipm,
                    "ipm_tahun"         => $ipm_tahun,
                    "tahun_max_ipm"     => $periode_ipm_tahun,
                    "tahun_ipm_kab"     => $tahun_ipm_kab,
                    "ipm_rkpd_rkp"      => $ipm_rkpd_rkp,
                    "ipm_perbandingan_pro"   => $ipm_perbandingan_pro,
                    "ipm_perbandingan_2th"   => $ipm_perbandingan_2th,
                    "ipm_perbandingan_kab"      => $ipm_perbandingan_kab,
                    "perbandingan_ipm"   => base_url("picture/laporan_ppd/".$picture_ipm_bar.".png"),
                    "radar_ipm"          => base_url("picture/laporan_ppd/".$picture_ipm_r.".png"),
                    "kab_ipm"            => base_url("picture/laporan_ppd/".$picture_ipm_k.".png"),
                    
                    //Gini Rasio
                    "halaman_gr"        => base_url("picture/laporan_ppd/".$picture_gr.".png"),
                    "capaian_n_gr"      => $max_n_gr,
                    "capaian_p_gr"      => $max_p_gr,
                    "capaian_k_gr"      => $max_k_gr,
                    "desc_gr"           => $gr_d,
                    "sumber_gr"         => $sumber_gr,
                    "tahun_max_gr"      => $periode_gr_tahun,
                    "tahun_gr_kab"      => $tahun_gr_kab,
                    "gr_rkpd_rkp"        => $gr_rkpd_rkp,
                    "gr_perbandingan_pro"      => $gr_perbandingan_pro,
                    "gr_perbandingan_2th"     => $gr_perbandingan_2th,
                    "gr_perbandingan_kab"      => $gr_perbandingan_kab,
                    "perbandingan_GR"   => base_url("picture/laporan_ppd/".$picture_gr_bar.".png"),
                    "radar_GR"          => base_url("picture/laporan_ppd/".$picture_gr_r.".png"),
                    "kab_GR"          => base_url("picture/laporan_ppd/".$picture_gr_k.".png"),
                    
                    
                );
                //$this->mpdf->SetHeader('Document Title');
                $html_1    = $this->load->view($this->view_dir."tmp",$data_page,TRUE);
                //$html_1  = $this->load->view($this->view_dir."hal_1",$data_page,TRUE);
                $html_2  = $this->load->view($this->view_dir."pertumbuhan_ekonomi",$data_page,TRUE);
                $html_2a  = $this->load->view($this->view_dir."pertumbuhan_ekonomi_1",$data_page,TRUE);
                $html_s  = $this->load->view($this->view_dir."struktur_pdrb",$data_page,TRUE);
                
                $html_3  = $this->load->view($this->view_dir."tingkat_kemiskinan",$data_page,TRUE);
                $html_4  = $this->load->view($this->view_dir."penduduk_miskin",$data_page,TRUE);
                $html_5  = $this->load->view($this->view_dir."tingkat_pengangguran_terbuka",$data_page,TRUE);
                $html_5a = $this->load->view($this->view_dir."tingkat_pengangguran_terbuka_1",$data_page,TRUE);
                $html_6  = $this->load->view($this->view_dir."jumlah_pengangguran",$data_page,TRUE);
                $html_6a  = $this->load->view($this->view_dir."jumlah_pengangguran_1",$data_page,TRUE);
                $html_7  = $this->load->view($this->view_dir."indek_pembangunan_manusia",$data_page,TRUE);
                $html_7a  = $this->load->view($this->view_dir."indek_pembangunan_manusia_1",$data_page,TRUE);
                $html_8  = $this->load->view($this->view_dir."gini_rasio",$data_page,TRUE);
                $html_8a  = $this->load->view($this->view_dir."gini_rasio_1",$data_page,TRUE);
                
                //this the the PDF filename that user will get to download
		$pdfFilePath = "Laporan_".$current_date_time."_".$judul.".pdf";
                $this->mpdf = new mPDF('utf-8', 'A4','', '', 25, 30, 20, 20, 'arial');
                //$this->mpdf = new mPDF('utf-8', 'A4', 0, '');
                //$this->mpdf->SetDisplayMode(90);
                $this->mpdf->SetDisplayMode('fullpage');
                //generate the PDF from the given html
                $this->m_pdf->pdf->AddPage('P');
                //$this->m_pdf->pdf->WriteHTML($html);
                //$this->m_pdf->pdf->setFooter('{PAGENO}');
                //$this->m_pdf->pdf->AddPage('','','','','');
                $this->m_pdf->pdf->WriteHTML($html_1);
                
//                $this->m_pdf->pdf->setFooter('{PAGENO}','','{DATE j-m-Y}');
                $this->m_pdf->pdf->SetHeader('<table width="100%"><tr><td width="100%" style="text-align: right;">Evaluasi Kinerja Pencapaian Makro Pembangunan</td></tr></table>');
                $this->m_pdf->pdf->AddPage('','','','','');
                $this->m_pdf->pdf->SetHTMLFooter('
                    <hr />
                    <table width="100%">
                        <tr>
                            <td width="33%">{DATE j-m-Y}</td>
                            <td width="33%" align="center">{PAGENO}/{nbpg}</td>
                            <td width="33%" style="text-align: right;">Direktorat PEPPD-Bappenas</td>
                        </tr>
                    </table>');
                $this->m_pdf->pdf->WriteHTML($html_2);
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_2a);
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_s);
                //$this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_3);
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_4);
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_5);
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_5a);
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_6);
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_6a);
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_7);
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_7a);
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_8);
                $this->m_pdf->pdf->AddPage();
                $this->m_pdf->pdf->WriteHTML($html_8a);

                //$this->m_pdf->pdf->AddPage('L');
		$this->m_pdf->pdf->Output($pdfFilePath, "D");
                
    }
    
    function test(){
$datay=array(2,3,-5,8,12,6,3);
$datax=array("Jan","Feb","Mar","Apr","May","Jun","Jul");
// Size of graph
$width=400; 
$height=500;
// Set the basic parameters of the graph 
$graph = new Graph($width,$height,'auto');
$graph->SetScale("textlin");
$graph->graph_theme = null;
$graph->SetFrame(false);
$top = 50;
$bottom = 80;
$left = 50;
$right = 20;
$graph->Set90AndMargin($left,$right,$top,$bottom);
$graph->xaxis->SetPos('min');
// Nice shadow
$graph->SetShadow();
// Setup title
$graph->title->Set("Horizontal bar graph ex 3");
//$rgaph->title->SetFont(FF_VERDANA,FS_BOLD,14);
$graph->subtitle->Set("(Axis at bottom)");
// Setup X-axis
$graph->xaxis->SetTickLabels($datax);
//$graph->xaxis->SetFont(FF_FONT2,FS_BOLD,12);
// Some extra margin looks nicer
$graph->xaxis->SetLabelMargin(5);
// Label align for X-axis
$graph->xaxis->SetLabelAlign('right','center');
// Add some grace to y-axis so the bars doesn't go
// all the way to the end of the plot area
$graph->yaxis->scale->SetGrace(20);
// Setup the Y-axis to be displayed in the bottom of the 
// graph. We also finetune the exact layout of the title,
// ticks and labels to make them look nice.
$graph->yaxis->SetPos('max');
// First make the labels look right
$graph->yaxis->SetLabelAlign('center','top');
$graph->yaxis->SetLabelFormat('%d');
$graph->yaxis->SetLabelSide(SIDE_RIGHT);
// The fix the tick marks
$graph->yaxis->SetTickSide(SIDE_LEFT);
// Finally setup the title
$graph->yaxis->SetTitleSide(SIDE_RIGHT);
$graph->yaxis->SetTitleMargin(35);
// To align the title to the right use :
$graph->yaxis->SetTitle('Turnaround 2002','high');
$graph->yaxis->title->Align('right');
// To center the title use :
$graph->yaxis->SetTitle('Turnaround 2002','center');
$graph->yaxis->title->Align('center');
//$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->yaxis->title->SetAngle(0);
//$graph->yaxis->SetFont(FF_FONT2,FS_NORMAL);
// If you want the labels at an angle other than 0 or 90
// you need to use TTF fonts
//$graph->yaxis->SetLabelAngle(0);
// Now create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor("#00FFFF");
$bplot->SetShadow();
//You can change the width of the bars if you like
$bplot->SetWidth(0.5);
// We want to display the value of each bar at the top
$bplot->value->Show();
//$bplot->value->SetFont(FF_ARIAL,FS_BOLD,12);
$bplot->value->SetAlign('left','center');
$bplot->value->SetColor("black","darkred");
$bplot->value->SetFormat('%.1f mkr');
// Add the bar to the graph
$graph->Add($bplot);
$graph->Stroke();
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
        function download_pdf(){
            
            
//            $datax = array(1,2,3,4); 
//            $datay = array(20,22,12,13); 
//            $datax2 = array(1,2,3,4); 
//            $datay2 = array(2,12,22,1); 
//            $graph = new Graph(300,200,"auto"); 
//            $graph->img->SetMargin(40,40,40,40);     
//            $graph->img->SetAntiAliasing(); 
//            $graph->SetScale("linlin"); 
//            $graph->SetShadow(); 
//            $graph->title->Set("Linked Scatter plot ex1"); 
//            $graph->title->SetFont(FF_FONT1,FS_BOLD); 
//
//            $sp1 = new ScatterPlot($datay,$datax); 
//            $sp1->SetLinkPoints(true,"red",2); 
//            $sp1->mark->SetType(MARK_FILLEDCIRCLE); 
//            $sp1->mark->SetFillColor("navy"); 
//            $sp1->mark->SetWidth(3); 
//            
//            $sp12 = new ScatterPlot($datay2,$datax2); 
//            $sp12->SetLinkPoints(true,"red",2); 
//            $sp12->mark->SetType(MARK_FILLEDCIRCLE); 
//            $sp12->mark->SetFillColor("navy"); 
//            $sp12->mark->SetWidth(3); 
//
//            $graph->Add($sp1); 
//            $graph->Add($sp12); 
//            $graph->Stroke(); 
            
            
            
            $datay1 = array(20,2,23,15);
            $datay2 = array(12,9,"-",8);
            $datay3 = array(5,"-",32,24);

// Setup the graph
$graph = new Graph(300,250);
$graph->SetScale("textlin");

$theme_class=new UniversalTheme;

$graph->SetTheme($theme_class);
$graph->img->SetAntiAliasing(false);
$graph->title->Set('Filled Y-grid');
$graph->SetBox(false);
$data1=array('A','B','C','D');
$graph->SetMargin(40,20,36,63);

$graph->img->SetAntiAliasing();

$graph->yaxis->HideZeroLabel();
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);

$graph->xgrid->Show($data1);
$graph->xgrid->SetLineStyle("solid");
$graph->xaxis->SetTickLabels();
$graph->xgrid->SetColor('#E3E3E3');

// Create the first line
$p1 = new LinePlot($datay1);
$graph->Add($p1);
$p1->SetColor("#6495ED");
$p1->SetLegend('Line 1');
$p1->value->Show();
// Create the second line
$p2 = new LinePlot($datay2);
$graph->Add($p2);
$p2->SetColor("#B22222");
$p2->SetLegend('Line 2');
$p2->value->Show();
// Create the third line
$p3 = new LinePlot($datay3);
$graph->Add($p3);
$p3->SetColor("#FF1493");
$p3->SetLegend('Line 3');
$graph->legend->SetFrameWeight(1);
// Output line
$graph->Stroke();
           
    }
}