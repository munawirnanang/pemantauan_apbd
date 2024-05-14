<?php defined('BASEPATH') OR exit('No direct script access allowed');

include_once(APPPATH."third_party/New_folder/PhpWord/Autoloader.php");
use PhpOffice\PhpWord\Autoloader;
Autoloader::register();
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\Common\XMLWriter;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TestHelperDOCX;
use PhpOffice\PhpWord\SimpleType\TextAlignment;


class Laporan_word extends CI_Controller {
    var $view_dir   = "peppd1/laporan_perkembangan/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/laporan_ppd/laporan_perkembangan.js";
    var $picture    = "picture/laporan_ppd";
    
    function __construct() {
        parent::__construct();
        $this->load->model("M_Master","m_ref");
        // load core JpGraph as CI library
       //$this->load->library('jpgraph/jpgraph.php');
        $this->load->library('M_pdf');   
        require_once (APPPATH.'/third_party/jpgraph/jpgraph.php');
        require_once (APPPATH.'/third_party/jpgraph/jpgraph_bar.php');
        require_once (APPPATH.'/third_party/jpgraph/jpgraph_line.php');
        require_once (APPPATH.'/third_party/jpgraph/jpgraph_radar.php');
        require_once (APPPATH.'/third_party/jpgraph/jpgraph_scatter.php');
        require_once (APPPATH.'/third_party/jpgraph/jpgraph_ttf.inc.php');

        
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
                $this->js_path    = "assets/js/admin/laporan_ppd/laporan_perkembangan.js?v=".now("Asia/Jakarta");
                $now=date('Y');
                $data_page = array(
                    "now" => $now
                );
                $str = $this->load->view($this->view_dir."index",$data_page,TRUE);
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
    
    function download_word(){
        $provinsi  = $_GET['inp_pro']; $kabupaten = $_GET['inp_sp'];
        $pro = $provinsi;
        $kab = $kabupaten;
        $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
        $prde = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
        $xname="";
        $query="";  
        $gambar=1;
        $d_peek="SELECT I.id,I.`deskripsi` FROM indikator I where 1=1";
        $list_peek = $this->db->query($d_peek);
        foreach ($list_peek->result() as $peek){
            if($peek->id =='1'){ $deskripsi1   = $peek->deskripsi; } if($peek->id =='2'){ $deskripsi2   = $peek->deskripsi; } if($peek->id =='3'){ $deskripsi3   = $peek->deskripsi; }
            if($peek->id =='4'){ $deskripsi4   = $peek->deskripsi; }
            if($peek->id =='6'){ $deskripsi5   = $peek->deskripsi; }
            if($peek->id =='5'){ $deskripsi6   = $peek->deskripsi; }
            if($peek->id =='7'){ $deskripsi7   = $peek->deskripsi; }
            if($peek->id =='8'){ $deskripsi8   = $peek->deskripsi; }
            if($peek->id =='9'){ $deskripsi9   = $peek->deskripsi; }
            if($peek->id =='10'){ $deskripsi10   = $peek->deskripsi; }
            if($peek->id =='11'){ $deskripsi11   = $peek->deskripsi; }
            if($peek->id =='36'){ $deskripsi12   = $peek->deskripsi; }
            if($peek->id =='39'){ $deskripsi13   = $peek->deskripsi; }
            if($peek->id =='40'){ $deskripsi14   = $peek->deskripsi; }
            
        }
        if($provinsi == '' & $kabupaten ==''){ $xname="Indonesia"; $query="1000";$judul="Indonesia";}
        elseif($provinsi != '' & $kabupaten =='') {
            $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`id`='".$pro."' ";
            $list_data = $this->db->query($sql_pro);
            foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = "1000";
                        $id_pro = $Lis_pro->id;
                        $judul = $Lis_pro->nama_provinsi;
            }
            $logopro      = $pro.".jpg";
            //Perkembangan Pertumbuhan Ekonomi (%)
            $sql_ppe = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
            $list_ppe = $this->db->query($sql_ppe);
            foreach ($list_ppe->result() as $row_ppe) {
                $tahun1[]   = $row_ppe->tahun;
                $nilaiData1[] = (float)$row_ppe->nilai;
                $nilaimax[] = number_format($row_ppe->nilai,2); 
                
            }
            $max_pe = end($nilaiData1);
            $sql_ppe_pro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
            $list_ppe_pro = $this->db->query($sql_ppe_pro);
            $thn='';
            foreach ($list_ppe_pro->result() as $row_ppe_pro) {
                $tahun1_pro[]   = $row_ppe_pro->tahun;
                $nilaiData1_pro[] = (float)$row_ppe_pro->nilai;
                $nilaimax_pro[] = number_format($row_ppe_pro->nilai,2); 
                $sumber_pe = $row_ppe_pro->sumber;
                $periode_pe[] = $row_ppe_pro->id_periode;
                $periode = $row_ppe_pro->periode;
                if($periode == '00'){ $thn=$row_ppe_pro->tahun;  }
                else{ $thn=  $prde[$row_ppe_pro->periode]." - ".$row_ppe_pro->tahun; }
            }
            //print_r($thn);exit();
            $periode_pe_max=max($periode_pe);
            $tahun_pe_max=max($tahun1_pro)." Antar Provinsi" ;
                    
                    $datay1 = $nilaiData1;
                    $datay2 = $nilaiData1_pro;
                    if($nilaimax_pro[4] > $nilaimax_pro[5]){                        
                        $paragraf_1_2    ="Pertumbuhan Ekonomi ". $xname ." pada tahun ".$tahun1[5]." menurun dibandingkan dengan Tahun ".$tahun1[4].". Pada tahun ".$tahun1[5]." pertumbuhan ekonomi ". $xname ." adalah sebesar ". end($nilaimax_pro) ."%, sedangkan pada tahun ".$tahun1[4]." pertumbuhannya tercatat sebesar ".$nilaimax_pro[4]."%. ";
                        if($nilaimax[5]>$nilaimax_pro[5]){
                            $paragraf_1_3  =" Capaian Pertumbuhan Ekonomi ". $xname ." pada tahun ".$tahun1[5]." dibawah nasional. Pertumbuhan ekonomi nasional pada tahun ".$tahun1[5]." adalah sebesar ". end($nilaimax) ."%. ";
                        }else{
                            $paragraf_1_3  =" Capaian Pertumbuhan Ekonomi ". $xname ." pada tahun ".$tahun1[5]." diatas nasional. Pertumbuhan ekonomi nasional pada tahun ".$tahun1[5]." adalah sebesar ". end($nilaimax) ."%. ";
                        }
                    } else {
                        $paragraf_1_2    ="Pertumbuhan Ekonomi ". $xname ." pada tahun ".$tahun1[5]." meningkat dibandingkan dengan Tahun ".$tahun1[4].". Pada tahun ".$tahun1[5]." pertumbuhan ekonomi ".$xname." adalah sebesar ". end($nilaimax_pro) ."%, sedangkan pada tahun ".$tahun1[4]." pertumbuhannya tercatat sebesar ".$nilaimax_pro[4]."%. ";    
                        if($nilaimax[5]>$nilaimax_pro[5]){
                            $max_pe_p  ="Capaian Pertumbuhan Ekonomi ". $xname ." pada tahun ".$tahun1[5]." dibawah nasional. Pertumbuhan ekonomi nasional pada tahun ".$tahun1[5]." adalah sebesar ". end($nilaimax) ."%. ";
                        }else{
                            $paragraf_1_3  =" Capaian Pertumbuhan Ekonomi ". $xname ." pada tahun ".$tahun1[5]." diatas nasional. Pertumbuhan ekonomi nasional pada tahun ".$tahun1[5]." adalah sebesar ". end($nilaimax) ."%. ";
                        }
                    }
                    
                    $max_pe_k  =" ";
                    $perbandingan_pe ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='1' AND e.id_periode='$periode_pe_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='1' AND id_periode='$periode_pe_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_ppe_per = $this->db->query($perbandingan_pe);
                    foreach ($list_ppe_per->result() as $row_ppe_per) {
                        $label_ppe[]     = $row_ppe_per->label;
                        $nilai_ppe_per[] = $row_ppe_per->nilai;
                    }
                    $label_data_ppe     = $label_ppe;
                    $nilai_data_ppe_per = $nilai_ppe_per;
                    
                    
                  //Perkembangan PDRB Per Kapita ADHB (Rp)
                    $sql_adhb = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_adhb = $this->db->query($sql_adhb);
                    foreach ($list_adhb->result() as $row_adhb) {
                        $tahun_adhb[]      = $row_adhb->tahun;
                        $nilaiData_adhb1[] = (float)$row_adhb->nilai/1000000;
                        $nilaiData_max[]   = (float)$row_adhb->nilai;
                        $adhb_nasional[]   = number_format($row_adhb->nilai,1);
                    }
                    $datay_adhb1 = $nilaiData_adhb1;
                    $tahun_adhb1 = $tahun_adhb;
                    $max_pdrb = end($nilaiData_adhb1);
                    $sql_adhb2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_adhb2 = $this->db->query($sql_adhb2);
                    foreach ($list_adhb2->result() as $row_adhb2) {
                        $tahun_adhb2[]   = $row_adhb2->tahun;
                        $nilaiData_adhb2[] = (float)$row_adhb2->nilai/1000000;
                        $nilaiData_max_p[] = (float)$row_adhb2->nilai;
                        $sumber_adhb       = $row_adhb2->sumber;
                        $periode_adhb[] = $row_adhb2->id_periode;
                        $ket_adhb2[]  = $row_adhb2->keterangan;
                    }
                    $datay_adhb2 = $nilaiData_adhb2;
                    $tahun_adhb2 = $tahun_adhb2;
                    $tahunadhb  = end($tahun_adhb1);
                    $periode_adhb_max=max($periode_adhb);
                    $periode_adhb_tahun=max($tahun_adhb2)." Antar Provinsi" ;
                    
                    $max_adhb_k  =" ";                    
                    if($nilaiData_max_p[4] > $nilaiData_max_p[5]){                        
                        $paragraf_2_2    ="PDRB Per Kapita ADHB ". $xname ." pada tahun ".$tahun_adhb2[5]." menurun dibandingkan dengan tahun ".$tahun_adhb2[4].". Pada tahun ".$tahun_adhb2[5]." PDRB perkapita ADHB ". $xname ." adalah sebesar Rp ". number_format(end($nilaiData_max_p),0) ." ".$ket_adhb2[5]." sedangkan pada tahun ".$tahun_adhb2[4]."   PDRB perkapita ADHB tercatat sebesar Rp ".number_format($nilaiData_max_p[4],0).". ";
                        if($nilaiData_max[5] > $nilaiData_max_p[5]){
                            $paragraf_2_3  ="Capaian PDRB Per Kapita ". $xname ." pada tahun ".$tahun_adhb2[5]." berada dibawah capaian nasional. PDRB perkapita ADHB nasional pada tahun ".$tahun_adhb2[5]." adalah sebesar  Rp ".number_format($nilaiData_max[5]) ." ";
                        }else{
                            $paragraf_2_3  ="Capaian PDRB Per Kapita ". $xname ." pada tahun ".$tahun_adhb2[5]." berada diatas capaian nasional. PDRB perkapita ADHB nasional pada tahun ".$tahun_adhb2[5]." adalah sebesar  Rp ".number_format($nilaiData_max[5]) ." ";
                        }
                    } else {
                        $paragraf_2_2    ="PDRB Per Kapita ADHB ". $xname ." pada tahun ".$tahun_adhb2[5]." meningkat dibandingkan dengan tahun ".$tahun_adhb2[4].". Pada tahun ".$tahun_adhb2[5]." PDRB perkapita ADHB ". $xname ." adalah sebesar Rp ". number_format(end($nilaiData_max_p),0) ." ".$ket_adhb2[5]." sedangkan pada tahun ".$tahun_adhb2[4]."  PDRB perkapita ADHB tercatat sebesar Rp ".number_format($nilaiData_max_p[4],0).". ";    
                        if($nilaiData_max[5] > $nilaiData_max_p[5]){
                            $paragraf_2_3  ="Capaian PDRB Per Kapita ". $xname ." pada tahun ".$tahun_adhb2[5]." berada dibawah capaian nasional. PDRB perkapita ADHB nasional pada tahun ".$tahun_adhb2[5]." adalah sebesar  Rp ".number_format($nilaiData_max[5]) ." ";
                        }else{
                            $paragraf_2_3  ="Capaian PDRB Per Kapita ". $xname ." pada tahun ".$tahun_adhb2[5]." berada diatas capaian nasional. PDRB perkapita ADHB nasional pada tahun ".$tahun_adhb2[5]." adalah sebesar  Rp ".number_format($nilaiData_max[5]) ." ";
                        }
                    }
                    $perbandingan_adhb ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='2' AND e.id_periode='$periode_adhb_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='2' AND id_periode='$periode_adhb_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_adhb_per = $this->db->query($perbandingan_adhb);
                    foreach ($list_adhb_per->result() as $row_adhb_per) {
                        $label_adhb[]     = $row_adhb_per->label;
                        $nilai_adhb_per[] = $row_adhb_per->nilai/1000000;
                    }
                    $label_data_adhb     = $label_adhb;
                    $nilai_data_adhb_per = $nilai_adhb_per;
                    
                    //adhk (Rp)
                    $sql_adhk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_adhk = $this->db->query($sql_adhk);
                    foreach ($list_adhk->result() as $row_adhk) {
                        $tahun_adhk[]   = $row_adhk->tahun;
                        $nilaiData_adhk1[] = (float)$row_adhk->nilai/1000000;
                        $adhk_nasional[] = (float)$row_adhk->nilai;                        
                    }
                    $datay_adhk1 = $nilaiData_adhk1;
                    $tahun_adhk1 = $tahun_adhk;
                    
                    
                    $sql_adhk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_adhk2 = $this->db->query($sql_adhk2);
                    foreach ($list_adhk2->result() as $row_adhk2) {
                        $tahun_adhk2[]   = $row_adhk2->tahun;
                        $nilaiData_adhk2[] = (float)$row_adhk2->nilai/1000000;
                        $adhk_p[] = (float)$row_adhk2->nilai;
                        $sumber_adhk       = $row_adhk2->sumber;
                        $periode_adhk[] = $row_adhk2->id_periode;
                        $ket_adhk2[]  = $row_adhk2->keterangan;
                    }
                    $datay_adhk2 = $nilaiData_adhk2;
                    $tahun_adhk2 = $tahun_adhk2;
                    $periode_adhk_max=max($periode_adhk);
                    $periode_adhk_tahun=max($tahun_adhk2)." Antar Provinsi" ;
                    
                    if($adhk_p[4] > $adhk_p[5]){                        
                        $paragraf_3_2    ="PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." pada tahun ".$tahun_adhk[5]." menurun dibandingkan dengan tahun ".$tahun_adhk[4].". Pada tahun ".$tahun_adhk[5]." PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." adalah sebesar Rp ". number_format(end($adhk_p)) ." ".$ket_adhk2[5]." sedangkan pada tahun ".$tahun_adhk[4]."  PDRB per Kapita ADHK Tahun Dasar 2010 tercatat sebesar Rp ".number_format($adhk_p[4]).". ";
                        if($adhk_nasional[5]>$adhk_p[5]){
                            $paragraf_3_3  ="Capaian PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." pada tahun ".$tahun_adhk[5]." berada dibawah capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun ".$tahun_adhk[5]." adalah sebesar  Rp ".number_format(end($adhk_nasional)) ." ";
                        }else{
                            $paragraf_3_3  ="Capaian PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." pada tahun ".$tahun_adhb[5]." berada diatas capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun ".$tahun_adhk[5]." adalah sebesar  Rp ".number_format(end($adhk_nasional)) ." ";
                        }
                    } else {
                        $paragraf_3_2    ="PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." pada tahun ".$tahun_adhk[5]." meningkat dibandingkan dengan tahun ".$tahun_adhk[4].". Pada tahun ".$tahun_adhk[5]." PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." adalah sebesar Rp ". number_format(end($adhk_p)) ." ".$ket_adhk2[5]." sedangkan pada tahun ".$tahun_adhk[4]."  PDRB per Kapita ADHK Tahun Dasar 2010 tercatat sebesar Rp ".number_format($adhk_p[4]).". ";    
                        if($adhk_nasional[5]>$adhk_p[5]){
                            $paragraf_3_3  ="Capaian PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." pada tahun ".$tahun_adhk[5]." berada dibawah capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun ".$tahun_adhk[5]." adalah sebesar  Rp ".number_format(end($adhk_nasional)) ." ";
                        }else{
                            $paragraf_3_3  ="Capaian PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." pada tahun ".$tahun_adhb[5]." berada diatas capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun ".$tahun_adhk[5]." adalah sebesar  Rp ".number_format(end($adhk_nasional)) ." ";
                        }
                    }
                    $perbandingan_adhk ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='3' AND e.id_periode='$periode_adhk_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='3' AND id_periode='$periode_adhk_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                   
                    $list_adhk_per = $this->db->query($perbandingan_adhk);
                    foreach ($list_adhk_per->result() as $row_adhk_per) {
                        $label_adhk[]     = $row_adhk_per->label;
                        $nilai_adhk_per[] = $row_adhk_per->nilai/1000000;
                    }
                    $label_data_adhk     = $label_adhk;
                    $nilai_data_adhk_per = $nilai_adhk_per;
                    
                    //jumlah pengangguran (Orang)
                    $sql_jp = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jp = $this->db->query($sql_jp);
                    foreach ($list_jp->result() as $row_jp) {
                        $tahun_jp[]      = $bulan[$row_jp->periode]."-".$row_jp->tahun;
                        $tahun_jp1[]     = $row_jp->id_periode;
                        $nilaiData_jp[]  = (float)$row_jp->nilai/1000;
                        $nilai_capaian[] = $row_jp->nilai;
                        $tahun_jp11[] = $row_jp->tahun;
                        $periode_jp1[] = $row_jp->periode;
                    }
                    $datay_jp = $nilaiData_jp;
                    $tahun_jp = $tahun_jp;
                    $periode_jp_max  =max($tahun_jp1);
                    $periode_jp_tahun=$bulan[max($periode_jp1)]." ".max($tahun_jp11)." Antar Provinsi" ;
                    $sql_jp2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jp2 = $this->db->query($sql_jp2);
                    foreach ($list_jp2->result() as $row_jp2) {
                        $tahun_jp2[]   = $row_jp2->tahun;
                        $nilaiData_jp2[] = (float)$row_jp2->nilai/1000;
                        $nilai_capaian2[] = $row_jp2->nilai;
                        $sumber_jp=$row_jp->sumber;
                    }
                    $datay_jp2 = $nilaiData_jp2;
                    $tahun_jp2 = $tahun_jp2;
                      
                    if($nilai_capaian[3] > $nilai_capaian[5]){
                        $nn_jp=$nilai_capaian[3]-$nilai_capaian[5];
                        $nn_jp2=$nn_jp/$nilai_capaian[5];
                        $nn_jp3=$nn_jp2*100;
                        $nn_jp33=number_format($nn_jp3,2);
                        $max_jp  ="Jumlah penganggur nasional pada ". $tahun_jp[5] ." sebanyak ". number_format($nilai_capaian[5],0) . " orang. Sedangkan jumlah penganggur pada ". $tahun_jp[3] ." sebanyak ". number_format($nilai_capaian[3],0) . " orang. Selama periode  ". $tahun_jp[3] ." - ". $tahun_jp[5] . " jumlah penganggur nasional berkurang ".number_format($nn_jp)." orang atau sebesar ".$nn_jp33 ."% ";
                    } else {
                        $nn_jp  =$nilai_capaian[5]-$nilai_capaian[3];
                        $nn_jp2=$nn_jp/$nilai_capaian[3];
                        $nn_jp3=$nn_jp2*100;
                        $nn_jp33=number_format($nn_jp3,2);
                        $max_jp  ="Jumlah penganggur nasional pada ". $tahun_jp[5] ." sebanyak ". number_format($nilai_capaian[5],0) . " orang. Sedangkan jumlah penganggur pada ". $tahun_jp[3] ." sebanyak ". number_format($nilai_capaian2[3],0) . " orang. Selama periode  ". $tahun_jp[3] ." - ". $tahun_jp[5] . " jumlah penganggur di ". $xname ." meningkat ".number_format($nn_jp)." orang atau sebesar ".number_format($nn_jp33) ."%";
                    }
                   
                    if($nilai_capaian2[3] > $nilai_capaian2[5]){
                        $rt_jp=$nilai_capaian2[5]-$nilai_capaian2[3];
                        $rt_jpp=abs($nilai_capaian2[5]-$nilai_capaian2[3]);
                        $rt_jp2=$rt_jp/$nilai_capaian2[3];
                        $rt_jp3=abs($rt_jp2*100);
                        $rt_jp33=number_format($rt_jp3,2);
                        $paragraf_4_2  ="Jumlah penganggur di ". $xname ." pada ". $tahun_jp[5] ." sebanyak ". number_format($nilai_capaian2[5],0) . " orang. Sedangkan jumlah penganggur pada ". $tahun_jp[3] ." sebanyak ". number_format($nilai_capaian2[3],0) . " orang. Selama periode  ". $tahun_jp[3] ." - ". $tahun_jp[5] . " jumlah penganggur di ". $xname." berkurang ".number_format($rt_jpp)." orang atau sebesar ".$rt_jp33 ."% ";
                    }else{
                        $rt_jp  =$nilai_capaian2[5]-$nilai_capaian2[3];
                        $rt_jp2=$rt_jp/$nilai_capaian2[3];
                        $rt_jp3=$rt_jp2*100;
                        $rt_jp33=number_format($rt_jp3,2);
                        $paragraf_4_2  ="Jumlah penganggur di ". $xname ." pada ". $tahun_jp[5] ." sebanyak ". number_format($nilai_capaian2[5],0) . " orang. Sedangkan jumlah penganggur pada ". $tahun_jp[3] ." sebanyak ". number_format($nilai_capaian2[3],0) . " orang. Selama periode  ". $tahun_jp[3] ." - ". $tahun_jp[5] . " jumlah penganggur di ". $xname ." meningkat ".number_format($rt_jp)." orang atau sebesar ".$rt_jp33 ."%";
                    }
                    
                    $perbandingan_jp ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='4' AND e.id_periode='$periode_jp_max') 
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='4' AND id_periode='$periode_jp_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    
                    $list_jp_per = $this->db->query($perbandingan_jp);
                    foreach ($list_jp_per->result() as $row_jp_per) {
                        $label_jp[]     = $row_jp_per->label;
                        $nilai_jp_per[] = $row_jp_per->nilai/1000;
                    }
                    $label_data_jp     = $label_jp; 
                    $nilai_data_jp_per = $nilai_jp_per;   
                    
                    
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
                        $tahun_tpt21[]    = $bulan[$row_tpt2->periode]."-".$row_tpt2->tahun;
                        $periode_tpt21[]    = $row_tpt2->periode;
                        $tahun_tpt2[]     = $row_tpt2->tahun;
                        $nilaiData_tpt2[] = (float)$row_tpt2->nilai;
                        $sumber_tpt       = $row_tpt2->sumber;
                        $periode_tpt_id[]    =   $row_tpt2->id_periode;
                    }
                    $datay_tpt2 = $nilaiData_tpt2;
                    $tahun_tpt2 = $tahun_tpt2;
                    $periode_tpt_max=max($periode_tpt_id);
                    $periode_tpt_tahun=$bulan[max($periode_tpt21)]." ".max($tahun_tpt2)." Antar Provinsi" ;
                    
                    if($nilaiData_tpt2[3] > $nilaiData_tpt2[5]){                        
                        $paragraf_5_2    ="Tingkat Pengangguran Terbuka ". $xname ." pada ".$tahun_tpt21[5]." menurun dibandingkan dengan ".$tahun_tpt21[3].". Pada ".$tahun_tpt21[5]." Tingkat Pengangguran Terbuka ". $xname ." adalah sebesar ". number_format(end($nilaiData_tpt2),2) ."% sedangkan pada ".$tahun_tpt21[3]."  Tingkat Pengangguran Terbuka tercatat sebesar ".number_format($nilaiData_tpt2[3],2)."%. ";
                        if($nilaiData_tpt[5]>$nilaiData_tpt2[5]){
                            $paragraf_5_3  ="Capaian Tingkat Pengangguran Terbuka ". $xname ." pada ".$tahun_tpt21[5]." berada dibawah capaian nasional. Tingkat Pengangguran Terbuka nasional pada ".$tahun_tpt21[5]." adalah sebesar ".number_format(end($nilaiData_tpt),2) ."% ";
                        }else{
                            $paragraf_5_3  ="Capaian Tingkat Pengangguran Terbuka ". $xname ." pada ".$tahun_tpt21[5]." berada diatas capaian nasional. Tingkat Pengangguran Terbuka nasional pada ".$tahun_tpt21[5]." adalah sebesar ".number_format(end($nilaiData_tpt),2) ."% ";
                        }
                    } else {
                        $paragraf_5_2    ="Tingkat Pengangguran Terbuka ". $xname ." pada ".$tahun_tpt21[5]." meningkat dibandingkan dengan ".$tahun_tpt21[3].". Pada ".$tahun_tpt21[5]." Tingkat Pengangguran Terbuka ". $xname ." adalah sebesar ". number_format(end($nilaiData_tpt2),2) ."% sedangkan pada ".$tahun_tpt21[3]."  Tingkat Pengangguran Terbuka tercatat sebesar ".number_format($nilaiData_tpt2[3],2)."%. ";    
                        if($nilaiData_tpt[5]>$nilaiData_tpt2[5]){
                            $paragraf_5_3  ="Capaian Tingkat Pengangguran Terbuka ". $xname ." pada ".$tahun_tpt21[5]." berada dibawah capaian nasional. Tingkat Pengangguran Terbuka nasional pada ".$tahun_tpt21[5]." adalah sebesar ".number_format(end($nilaiData_tpt),2) ."% ";
                        }else{
                            $paragraf_5_3  ="Capaian Tingkat Pengangguran Terbuka ". $xname ." pada ".$tahun_tpt21[5]." berada diatas capaian nasional. Tingkat Pengangguran Terbuka nasional pada ".$tahun_tpt21[5]." adalah sebesar ".number_format(end($nilaiData_tpt),2) ."% ";
                        }
                    }
                    $max_tpt_k =" ";
                    $perbandingan_tpt ="select p.label as label, e.* 
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
                        $nilai_tpt_per[] = $row_tpt_per->nilai;
                    }
                    $label_data_tpt     = $label_tpt;
                    $nilai_data_tpt_per = $nilai_tpt_per;
                    
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
                    $list_ipm2 = $this->db->query($sql_ipm2);
                    foreach ($list_ipm2->result() as $row_ipm2) {
                        $tahun_ipm2[]   = $row_ipm2->tahun;
                        $nilaiData_ipm2[] = (float)$row_ipm2->nilai;
                        $sumber_ipm       = $row_ipm2->sumber;
                        $periode_ipm_id[] = $row_ipm2->id_periode;
                        $tahun_ipm21[]    = $bulan[$row_ipm2->periode]."-".$row_ipm2->tahun;
                    }
                    $datay_ipm2 = $nilaiData_ipm2;
                    $tahun_ipm2 = $tahun_ipm2;
                    $max_ipm_k ="";
                    $periode_ipm_max = max($periode_ipm_id);
                    $periode_ipm_tahun=max($tahun_ipm2)." Antar Provinsi" ;
                    $paragraf_6_3='';
                    if($nilaiData_ipm2[4] > $nilaiData_ipm2[5]){                        
                        $paragraf_6_2    ="Indeks Pembangunan Manusia ". $xname ." pada tahun ".$tahun_ipm2[5]." menurun dibandingkan dengan tahun ".$tahun_ipm2[4].". Pada tahun ".$tahun_ipm2[5]." Indeks Pembangunan Manusia ". $xname ." adalah sebesar ". number_format(end($nilaiData_ipm2),2) ."% sedangkan pada tahun ".$tahun_ipm2[4]."  Indeks Pembangunan Manusia tercatat sebesar ".number_format($nilaiData_ipm2[4],2)."%. ";
                        if($nilaiData_ipm[5]>$nilaiData_ipm2[5]){
                            $paragraf_6_3  ="Capaian Indeks Pembangunan Manusia ". $xname ." pada tahun ".$tahun_ipm2[5]." berada dibawah capaian nasional. Indeks Pembangunan Manusia nasional pada tahun ".$tahun_ipm2[5]." adalah sebesar ".number_format(end($nilaiData_ipm),2)."% "."<br/><br/><br/><br/>";
                        }else{
                            $paragraf_6_3  ="Capaian Indeks Pembangunan Manusia ". $xname ." pada tahun ".$tahun_ipm2[5]." berada diatas capaian nasional. Indeks Pembangunan Manusia nasional pada tahun ".$tahun_ipm2[5]." adalah sebesar ".number_format(end($nilaiData_ipm),2)."% "."<br/><br/><br/><br/>";
                        }
                    } else {
                        $paragraf_6_2 ="Indeks Pembangunan Manusia ". $xname ." pada tahun ".$tahun_ipm2[5]." meningkat dibandingkan dengan tahun ".$tahun_ipm2[4].". Pada tahun ".$tahun_ipm2[5]." Indeks Pembangunan Manusia ". $xname ." adalah sebesar ". number_format(end($nilaiData_ipm2),2) ."% sedangkan pada tahun ".$tahun_ipm2[4]."  Indeks Pembangunan Manusia tercatat sebesar ".number_format($nilaiData_ipm2[4],2)."%. ";    
                        if($nilaiData_ipm[5]>$nilaiData_ipm2[5]){
                            $paragraf_6_3  ="Capaian Indeks Pembangunan Manusia ". $xname ." pada tahun ".$tahun_ipm2[5]." berada dibawah capaian nasional. Indeks Pembangunan Manusia nasional pada tahun ".$tahun_ipm2[5]." adalah sebesar ".number_format(end($nilaiData_ipm),2)."% "."<br/><br/><br/><br/>";
                        }else{
                            $paragraf_6_3  ="Capaian Indeks Pembangunan Manusia ". $xname ." pada tahun ".$tahun_ipm2[5]." berada diatas capaian nasional. Indeks Pembangunan Manusia nasional pada tahun ".$tahun_ipm2[5]." adalah sebesar ".number_format(end($nilaiData_ipm),2)."% "."<br/><br/><br/><br/>";
                        }
                    }

                    $perbandingan_ipm ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='5' AND e.id_periode='$periode_ipm_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='5' AND id_periode='$periode_ipm_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_ipm_per = $this->db->query($perbandingan_ipm);
                    foreach ($list_ipm_per->result() as $row_ipm_per) {
                        $label_ipm[]     = $row_ipm_per->label;
                        $nilai_ipm_per[] = $row_ipm_per->nilai;
                    }
                    $label_data_ipm     = $label_ipm;
                    $nilai_data_ipm_per = $nilai_ipm_per;
                    
                    
                    //Gini Rasio.
                    $sql_gr = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_gr = $this->db->query($sql_gr);
                    foreach ($list_gr->result() as $row_gr) {
                        $tahun_gr[]    = $bulan[$row_gr->periode]."-".$row_gr->tahun;
                        $nilaiData_gr[] = (float)$row_gr->nilai;
                    }
                    $datay_gr = $nilaiData_gr;
                    $tahun_gr = $tahun_gr;
                    $sql_gr2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_gr2 = $this->db->query($sql_gr2);
                    foreach ($list_gr2->result() as $row_gr2) {
                        $tahun_gr2[]   = $row_gr2->tahun;
                        $periode    = $row_gr2->periode;                        
                        $nilaiData_gr2[] = (float)$row_gr2->nilai;
                        $nilaiData_gr22[] = number_format((float)$row_gr2->nilai,3);
                        $sumber_gr       = $row_gr2->sumber;
                        $periode_gr_id[]    = $row_gr2->id_periode;
                        $tahun_gr21[]    = $bulan[$row_gr2->periode]."-".$row_gr2->tahun;
                    }
                    $datay_gr2 = $nilaiData_gr2;
                    $tahun_gr2 = $tahun_gr2;
                    $max_k_gr  =  "";
                    $periode_gr_max   = max($periode_gr_id);
                //    $periode_gr_tahun = $bulan[max($periode)]." ".max($tahun_gr2)." Antar Provinsi" ;
                    if($nilaiData_gr2[3] > $nilaiData_gr2[5]){                        
                        $paragraf_7_2    ="1Gini Rasio ". $xname ." pada ".$tahun_gr[5]." menurun dibandingkan dengan ".$tahun_gr[3].". Pada ".$tahun_gr[5]." gini rasio ". $xname ." adalah sebesar ". number_format($nilaiData_gr2[5],3) ."% sedangkan pada ".$tahun_gr[3]."  gini rasio tercatat sebesar ".number_format($nilaiData_gr2[3],3)."%. ";
                        if($nilaiData_gr[5]>$nilaiData_gr2[5]){
                            $paragraf_7_3  ="Capaian gini rasio ". $xname ." pada ".$tahun_gr[5]." berada dibawah capaian nasional. Gini rasio nasional pada ".$tahun_gr[5]." adalah sebesar ".number_format($nilaiData_gr[5],3) ."% ";
                        }else{
                            $paragraf_7_3  ="Capaian gini rasio ". $xname ." pada ".$tahun_gr[5]." berada diatas capaian nasional. Gini rasio nasional pada ".$tahun_gr[5]." adalah sebesar ".number_format($nilaiData_gr[5],3) ."% ";
                        }
                    } else {
                        $paragraf_7_2    ="Gini Rasio ". $xname ." pada ".$tahun_gr[5]." meningkat dibandingkan dengan ".$tahun_gr[3].". Pada ".$tahun_gr[5]." gini rasio ". $xname ." adalah sebesar ". number_format($nilaiData_gr2[5],3) ."% sedangkan pada ".$tahun_gr[3]."  gini rasio tercatat sebesar ".number_format($nilaiData_gr2[3],3)."%. ";    
                        if($nilaiData_gr[5]>$nilaiData_gr2[5]){
                            $paragraf_7_3  ="Capaian gini rasio ". $xname ." pada ".$tahun_gr[5]." berada dibawah capaian nasional. Gini rasio nasional pada ".$tahun_gr[5]." adalah sebesar ".number_format($nilaiData_gr[5],3) ."% ";
                        }else{
                            $paragraf_7_3  ="Capaian gini rasio ". $xname ." pada ".$tahun_gr[5]." berada diatas capaian nasional. Gini rasio nasional pada ".$tahun_gr[5]." adalah sebesar ".number_format($nilaiData_gr[5],3) ."% ";
                        }
                    }
                    $perbandingan_gr ="select p.label as label, e.* 
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
                    }
                    $label_data_gr     = $label_gr;
                    $nilai_data_gr_per = $nilai_gr_per;
                                        
                    //angka harapan hidup (Tahun)
                    $sql_ahh = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ahh = $this->db->query($sql_ahh);
                    foreach ($list_ahh->result() as $row_ahh) {
                        $tahun_ahh[]   = $row_ahh->tahun;
                        $nilaiData_ahh[] = (float)$row_ahh->nilai;
                    }
                    $datay_ahh = $nilaiData_ahh;
                    $tahun_ahh = $tahun_ahh;
                    $sql_ahh2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ahh2 = $this->db->query($sql_ahh2);
                    foreach ($list_ahh2->result() as $row_ahh2) {
                        $tahun_ahh2[]   = $row_ahh2->tahun;
                        $nilaiData_ahh2[] = (float)$row_ahh2->nilai;
                        $sumber_ahh       = $row_ahh2->sumber;
                        $periode_ahh_id[] = $row_ahh2->id_periode;
                        $tahun_ahh21[]    = $bulan[$row_ahh2->periode]."-".$row_ahh2->tahun;
                    }
                    $datay_ahh2 = $nilaiData_ahh2;
                    //$tahun_ahh2 = $tahun_ahh2;
                    $max_k_ahh ="";
                    $periode_ahh_max=max($periode_ahh_id);
                    $periode_ahh_tahun=max($tahun_ahh2)." Antar Provinsi" ;
                    if($nilaiData_ahh2[4] > $nilaiData_ahh2[5]){
                        $paragraf_8_2    ="Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." menurun dibandingkan dengan tahun ".$tahun_ahh[4].". Pada tahun ".$tahun_ahh[5]." Angka Harapan Hidup ". $xname ." adalah sebesar ". number_format(end($nilaiData_ahh2),2) ." tahun sedangkan pada tahun ".$tahun_ahh[4]." Angka Harapan Hidup tercatat sebesar ".number_format($nilaiData_ahh2[4],2)." tahun. ";
                        if($nilaiData_ahh[5]>$nilaiData_ahh2[5]){
                            $paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                        }else{
                            $paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada diatas capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                        }
                    } else {
                        $paragraf_8_2    ="Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." meningkat dibandingkan dengan tahun ".$tahun_ahh[4].". Pada tahun ".$tahun_ahh[5]." Angka Harapan Hidup ". $xname ." adalah sebesar ". number_format(end($nilaiData_ahh2),2) ." tahun sedangkan pada tahun ".$tahun_ahh[4]." Angka Harapan Hidup tercatat sebesar ".number_format($nilaiData_ahh2[4],2)." tahun.";    
                         if($nilaiData_ahh[5]>$nilaiData_ahh2[5]){
                            $paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                        }else{
                            $paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada diatas capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                        }
                    }
                    $perbandingan_ahh ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='8' AND e.id_periode='$periode_ahh_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='8' AND id_periode='$periode_ahh_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_ahh_per = $this->db->query($perbandingan_ahh);
                    foreach ($list_ahh_per->result() as $row_ahh_per) {
                        $label_ahh[]     = $row_ahh_per->label;
                        $nilai_ahh_per[] = $row_ahh_per->nilai;
                    }
                    $label_data_ahh     = $label_ahh;
                    $nilai_data_ahh_per = $nilai_ahh_per;
                    
                    //rata-rata lama sekolah (Tahun)
                    $sql_rls = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_rls = $this->db->query($sql_rls);
                    foreach ($list_rls->result() as $row_rls) {
                        $tahun_rls[]   = $row_rls->tahun;
                        $nilaiData_rls[] = (float)$row_rls->nilai;
                    }
                    $datay_rls = $nilaiData_rls;
                    $tahun_rls = $tahun_rls;
                    $sql_rls2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_rls2 = $this->db->query($sql_rls2);
                    foreach ($list_rls2->result() as $row_rls2) {
                        $tahun_rls2[]   = $row_rls2->tahun;
                        $nilaiData_rls2[] = (float)$row_rls2->nilai;
                        $sumber_rls = $row_rls2->sumber;
                        $periode_rls_id[] = $row_rls2->id_periode;
                        $tahun_rls21[]    = $bulan[$row_rls2->periode]."-".$row_rls2->tahun;
                    }
                    $datay_rls2 = $nilaiData_rls2;
                    $tahun_rls2 = $tahun_rls2;
                    $periode_rls_max = max($periode_rls_id);
                    $periode_rls_tahun=max($tahun_rls2)." Antar Provinsi" ;
                    if($nilaiData_rls2[4] > $nilaiData_rls2[5]){
                        $paragraf_9_2    ="Rata-rata lama sekolah ". $xname ." pada tahun ".$tahun_rls[5]." menurun dibandingkan dengan tahun ".$tahun_rls[4].". Pada tahun ".$tahun_rls[5]." Rata-rata lama sekolah ". $xname ." ". number_format(end($nilaiData_rls2),2) ." Tahun, sedangkan pada tahun ".$tahun_rls[4]." rata-rata lama sekolah tercatat sebesar ".number_format($nilaiData_rls2[4],2)." tahun. ";
                        if($nilaiData_rls[5]>$nilaiData_rls2[5]){
                            $paragraf_9_3  ="Capaian rata-rata lama sekolah ". $xname ." pada tahun ".$tahun_rls[5]." berada dibawah capaian nasional. Rata-rata lama sekolah nasional pada tahun ".$tahun_rls[5]." sebesar ".number_format($nilaiData_rls[5],2) ." tahun. ";
                        }else{
                            $paragraf_9_3  ="Capaian rata-rata lama sekolah ". $xname ." pada tahun ".$tahun_rls[5]." berada diatas capaian nasional. Rata-rata lama sekolah nasional pada tahun ".$tahun_rls[5]." sebesar ".number_format($nilaiData_rls[5],2) ." tahun. ";
                        }
                    } else {
                        $paragraf_9_2    ="Rata-rata lama sekolah ". $xname ." pada tahun ".$tahun_rls[5]." meningkat dibandingkan dengan tahun ".$tahun_rls[4].". Pada tahun ".$tahun_rls[5]." Rata-rata lama sekolah ". $xname ." ". number_format(end($nilaiData_rls2),2) ." Tahun, sedangkan pada tahun ".$tahun_rls[4]." rata-rata lama sekolah tercatat sebesar ".number_format($nilaiData_rls2[4],2)." tahun. ";
                        if($nilaiData_rls[5]>$nilaiData_rls2[5]){
                            $paragraf_9_3  ="Capaian rata-rata lama sekolah ". $xname ." pada tahun ".$tahun_rls[5]." berada dibawah capaian nasional. Rata-rata lama sekolah nasional pada tahun ".$tahun_rls[5]." sebesar ".number_format($nilaiData_rls[5],2) ." tahun. ";
                        }else{
                            $paragraf_9_3  ="Capaian rata-rata lama sekolah ". $xname ." pada tahun ".$tahun_rls[5]." berada diatas capaian nasional. Rata-rata lama sekolah nasional pada tahun ".$tahun_rls[5]." sebesar ".number_format($nilaiData_rls[5],2) ." tahun. ";
                        }
                    }
                    $max_k_rls ="";
                    $perbandingan_rls ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='9' AND e.id_periode='$periode_rls_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='9' AND id_periode='$periode_rls_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_rls_per = $this->db->query($perbandingan_rls);
                    foreach ($list_rls_per->result() as $row_rls_per) {
                        $label_rls[]     = $row_rls_per->label;
                        $nilai_rls_per[] = $row_rls_per->nilai;
                    }
                    $label_data_rls     = $label_rls;
                    $nilai_data_rls_per = $nilai_rls_per;

                                        //harapan lama sekolah (Tahun)
                    $sql_hls = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_hls = $this->db->query($sql_hls);
                    foreach ($list_hls->result() as $row_hls) {
                        $tahun_hls[]   = $row_hls->tahun;
                        $nilaiData_hls[] = (float)$row_hls->nilai;
                    }
                    $datay_hls = $nilaiData_hls;
                    $tahun_hls = $tahun_hls;
                    $sql_hls2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_hls2 = $this->db->query($sql_hls2);
                    foreach ($list_hls2->result() as $row_hls2) {
                        $tahun_hls2[]   = $row_hls2->tahun;
                        $nilaiData_hls2[] = (float)$row_hls2->nilai;
                        $sumber_hls = $row_hls2->sumber;
                        $periode_hls_id[] = $row_hls2->id_periode;
                        $tahun_hls21[]    = $bulan[$row_hls2->periode]."-".$row_hls2->tahun;
                    }
                    $datay_hls2 = $nilaiData_hls2;
                    $tahun_hls2 = $tahun_hls2;
                    $periode_hls_max = max($periode_hls_id);
                    $periode_hls_tahun=max($tahun_hls2)." Antar Provinsi" ;
                    if($nilaiData_hls2[4] > $nilaiData_hls2[5]){
                        $paragraf_10_2    ="Harapan Lama Sekolah ". $xname ." pada tahun ".$tahun_hls[5]." menurun dibandingkan dengan tahun ".$tahun_hls[4].". Pada tahun ".$tahun_hls[5]." Harapan Lama Sekolah ". $xname ." adalah sebesar ". number_format(end($nilaiData_hls2),2) ." Tahun, sedangkan pada tahun ".$tahun_hls[4]." rata-rata lama sekolah tercatat sebesar ".number_format($nilaiData_hls2[4],2)." tahun. ";
                        if($nilaiData_hls[5]>$nilaiData_hls2[5]){
                            $paragraf_10_3  ="Capaian Harapan Lama Sekolah ". $xname ." pada tahun ".$tahun_hls[5]." berada dibawah capaian nasional. Harapan Lama Sekolah nasional pada tahun ".$tahun_hls[5]." adalah sebesar ".number_format(end($nilaiData_hls),2) ." tahun. ";
                        }else{
                            $paragraf_10_3  ="Capaian Harapan Lama Sekolah ". $xname ." pada tahun ".$tahun_hls[5]." berada diatas capaian nasional. Harapan Lama Sekolah nasional pada tahun ".$tahun_hls[5]." adalah sebesar ".number_format(end($nilaiData_hls),2) ." tahun. ";
                        }
                    } else {
                        $paragraf_10_2    ="Harapan Lama Sekolah ". $xname ." pada tahun ".$tahun_hls[5]." meningkat dibandingkan dengan tahun ".$tahun_hls[4].". Pada tahun ".$tahun_hls[5]." Harapan Lama Sekolah ". $xname ." adalah sebesar ". number_format(end($nilaiData_hls2),2) ." Tahun, sedangkan pada tahun ".$tahun_hls[4]." rata-rata lama sekolah tercatat sebesar ".number_format($nilaiData_hls2[4],2)." tahun. ";
                        if($nilaiData_hls[5]>$nilaiData_hls2[5]){
                            $paragraf_10_3  ="Capaian Harapan Lama Sekolah ". $xname ." pada tahun ".$tahun_hls[5]." berada dibawah capaian nasional. Harapan Lama Sekolah nasional pada tahun ".$tahun_hls[5]." adalah sebesar ".number_format(end($nilaiData_hls),2) ." tahun. ";
                        }else{
                            $paragraf_10_3  ="Capaian Harapan Lama Sekolah ". $xname ." pada tahun ".$tahun_hls[5]." berada diatas capaian nasional. Harapan Lama Sekolah nasional pada tahun ".$tahun_hls[5]." adalah sebesar ".number_format(end($nilaiData_hls),2) ." tahun. ";
                        }
                    }
                    $max_k_hls ="";
                    $perbandingan_hls ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='9' AND e.id_periode='$periode_hls_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='9' AND id_periode='$periode_hls_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_hls_per = $this->db->query($perbandingan_hls);
                    foreach ($list_hls_per->result() as $row_hls_per) {
                        $label_hls[]     = $row_hls_per->label;
                        $nilai_hls_per[] = $row_hls_per->nilai;
                    }
                    $label_data_hls     = $label_hls;
                    $nilai_data_hls_per = $nilai_hls_per;
                    
                    //pengeluaran per kapita 
                    $sql_ppk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ppk = $this->db->query($sql_ppk);
                    foreach ($list_ppk->result() as $row_ppk) {
                        $tahun_ppk[]   = $row_ppk->tahun;
                        $nilaiData_ppk[] = (float)$row_ppk->nilai;
                        $nilaiData_ppk1[] = (float)$row_ppk->nilai/1000000;
                    }
                    $datay_ppk = $nilaiData_ppk1;
                    $tahun_ppk = $tahun_ppk;
                    $sql_ppk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ppk2 = $this->db->query($sql_ppk2);
                    foreach ($list_ppk2->result() as $row_ppk2) {
                        $tahun_ppk2[]     = $row_ppk2->tahun;
                        $nilaiData_ppk2[] = (float)$row_ppk2->nilai;
                        $nilaiData_ppk22[] = (float)$row_ppk2->nilai/1000000;
                        $sumber_ppk       = $row_ppk->sumber;
                        $periode_ppk_id[] = $row_ppk2->id_periode;
                        $tahun_ppk21[]    = $bulan[$row_ppk2->periode]."-".$row_ppk2->tahun;
                    }
                    $datay_ppk2 = $nilaiData_ppk22;
                    $tahun_ppk2 = $tahun_ppk2;
                    $periode_ppk_max = max($periode_ppk_id);
                    $periode_ppk_tahun=max($tahun_ppk2)." Antar Provinsi" ;
                    if($nilaiData_ppk2[4] > $nilaiData_ppk2[5]){
                        $paragraf_11_2    ="Pengeluaran Perkapita ". $xname ." pada tahun ".$tahun_ppk[5]." menurun dibandingkan dengan tahun ".$tahun_ppk[4].". Pada tahun ".$tahun_ppk[5]." Pengeluaran Perkapita ". $xname ." adalah sebesar Rp ". number_format(end($nilaiData_ppk2)) ." sedangkan pada tahun ".$tahun_ppk[4]." Pengeluaran Perkapita tercatat sebesar Rp ".number_format($nilaiData_ppk2[4]).". ";
                        if($nilaiData_ppk[5]>$nilaiData_ppk2[5]){
                            $paragraf_11_3  ="Capaian Pengeluaran Perkapita ". $xname ." pada tahun ".$tahun_ppk[5]." berada dibawah capaian nasional. Pengeluaran Perkapita nasional pada tahun ".$tahun_ppk[5]." adalah sebesar Rp ".number_format(end($nilaiData_ppk)) ." ";
                        }else{
                            $paragraf_11_3  ="Capaian Pengeluaran Perkapita ". $xname ." pada tahun ".$tahun_ppk[5]." berada dibawah capaian nasional. Pengeluaran Perkapita nasional pada tahun ".$tahun_ppk[5]." adalah sebesar Rp ".number_format(end($nilaiData_ppk)) ." ";
                        }
                    } else {
                        $paragraf_11_2    ="Pengeluaran Perkapita ". $xname ." pada tahun ".$tahun_ppk[5]." meningkat dibandingkan dengan tahun ".$tahun_ppk[4].". Pada tahun ".$tahun_ppk[5]." Pengeluaran Perkapita ". $xname ." adalah sebesar Rp ". number_format(end($nilaiData_ppk2)) ." sedangkan pada tahun ".$tahun_ppk[4]." Pengeluaran Perkapita tercatat sebesar Rp ".number_format($nilaiData_ppk2[4]).". ";
                        if($nilaiData_ppk[5]>$nilaiData_ppk2[5]){
                            $paragraf_11_3  ="Capaian Pengeluaran Perkapita ". $xname ." pada tahun ".$tahun_ppk[5]." berada dibawah capaian nasional. Pengeluaran Perkapita nasional pada tahun ".$tahun_ppk[5]." adalah sebesar Rp ".number_format(end($nilaiData_ppk)) ." ";
                        }else{
                            $paragraf_11_3  ="Capaian Pengeluaran Perkapita ". $xname ." pada tahun ".$tahun_ppk[5]." berada dibawah capaian nasional. Pengeluaran Perkapita nasional pada tahun ".$tahun_ppk[5]." adalah sebesar Rp ".number_format(end($nilaiData_ppk)) ." ";
                        }
                    }
                    $max_k_ppk ="";
                    $perbandingan_ppk ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='11' AND e.id_periode='$periode_ppk_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='11' AND id_periode='$periode_ppk_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    //  print_r($perbandingan_ppk);exit();
                    $list_ppk_per = $this->db->query($perbandingan_ppk);
                    foreach ($list_ppk_per->result() as $row_ppk_per) {
                        $label_ppk[]     = $row_ppk_per->label;
                        $nilai_ppk_per[] = $row_ppk_per->nilai/1000000;
                    }
                    $label_data_ppk     = $label_ppk;
                    $nilai_data_ppk_per = $nilai_ppk_per;
                    
                    
                    //Tingkat Kemiskinan
                    $sql_tk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_tk = $this->db->query($sql_tk);
                    foreach ($list_tk->result() as $row_tk) {
                        $tahun_tk[]    = $bulan[$row_tk->periode]."-".$row_tk->tahun;
                        $nilaiData_tk[] = (float)$row_tk->nilai;
                    }
                    $datay_tk = $nilaiData_tk;
                    $tahun_tk = $tahun_tk;
                    $sql_tk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_tk2 = $this->db->query($sql_tk2);
                    foreach ($list_tk2->result() as $row_tk2) {
                        $tahun_tk2[]   = $row_tk2->tahun;
                        $nilaiData_tk2[] = (float)$row_tk2->nilai;
                        $sumber_tk       = $row_tk2->sumber;
                        $periode_tk_id[] = $row_tk2->id_periode;
                        $tahun_tk21[]    = $bulan[$row_tk2->periode]."-".$row_tk2->tahun;
                    }
                    $datay_tk2 = $nilaiData_tk2;
                    $tahun_tk2 = $tahun_tk2;
                    $periode_tk_max = max($periode_tk_id);
                    $periode_tk_tahun=max($tahun_tk21)." Antar Provinsi" ;
                    
                    if($nilaiData_tk2[3] > $nilaiData_tk2[5]){
                        $paragraf_12_2    ="Tingkat Kemiskinan ". $xname ." pada ".$tahun_tk[5]." menurun dibandingkan dengan ".$tahun_tk[3].". Pada ".$tahun_tk[5]." Angka tingkat Kemiskinan ". $xname ." adalah sebesar ". number_format(end($nilaiData_tk2),2) ."%, sedangkan pada ".$tahun_tk[3]." Angka tingkat Kemiskinan tercatat sebesar ".number_format($nilaiData_tk2[3],2)."%. ";
                        if($nilaiData_tk[5]>$nilaiData_tk2[5]){
                            $paragraf_12_3  ="Capaian Angka tingkat Kemiskinan ". $xname ." pada ".$tahun_tk[5]." berada dibawah capaian nasional. Angka tingkat Kemiskinan nasional pada ".$tahun_tk[5]." adalah sebesar ".number_format(end($nilaiData_tk),2) ."% ";
                        }else{
                            $paragraf_12_3  ="Capaian Angka tingkat Kemiskinan ". $xname ." pada ".$tahun_tk[5]." berada diatas capaian nasional. Angka tingkat Kemiskinan nasional pada ".$tahun_tk[5]." adalah sebesar ".number_format(end($nilaiData_tk),2) ."% ";
                        }
                    } else {
                        $paragraf_12_2    ="Tingkat Kemiskinan ". $xname ." pada ".$tahun_tk[5]." meningkat dibandingkan dengan ".$tahun_tk[3].". Pada ".$tahun_tk[5]." Angka tingkat Kemiskinan ". $xname ." adalah sebesar ". number_format(end($nilaiData_tk2),2) ."%, sedangkan pada ".$tahun_tk[3]." Angka tingkat Kemiskinan tercatat sebesar ".number_format($nilaiData_tk2[3],2)."%. ";
                        if($nilaiData_tk[5]>$nilaiData_tk2[5]){
                            $paragraf_12_3  ="Capaian Angka tingkat Kemiskinan ". $xname ." pada ".$tahun_tk[5]." berada dibawah capaian nasional. Angka tingkat Kemiskinan nasional pada ".$tahun_tk[5]." adalah sebesar ".number_format(end($nilaiData_tk),2) ."% ";
                        }else{
                            $paragraf_12_3  ="Capaian Angka tingkat Kemiskinan ". $xname ." pada ".$tahun_tk[5]." berada diatas capaian nasional. Angka tingkat Kemiskinan nasional pada ".$tahun_tk[5]." adalah sebesar ".number_format(end($nilaiData_tk),2) ."% ";
                        }
                    }
                    $max_k_tk = "";
                    $perbandingan_tk ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='36' AND e.id_periode='$periode_tk_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='36' AND id_periode='$periode_tk_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_tk_per = $this->db->query($perbandingan_tk);
                    foreach ($list_tk_per->result() as $row_tk_per) {
                        $label_tk[]     = $row_tk_per->label;
                        $nilai_tk_per[] = $row_tk_per->nilai;
                    }
                    $label_data_tk     = $label_tk;
                    $nilai_data_tk_per = $nilai_tk_per;
                    
                    //indeks Kedalaman Kemiskinan
                    $sql_idk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_idk = $this->db->query($sql_idk);
                    foreach ($list_idk->result() as $row_idk) {
                        //$tahun_idk[]   = $row_idk->tahun;
                        $tahun_idk[]    = $bulan[$row_idk->periode]."-".$row_idk->tahun;
                        $nilaiData_idk[] = (float)$row_idk->nilai;
                    }
                    $datay_idk = $nilaiData_idk;
                    $tahun_idk = $tahun_idk;
                    $sql_idk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_idk2 = $this->db->query($sql_idk2);
                    foreach ($list_idk2->result() as $row_idk2) {
                        $tahun_idk2[]     = $row_idk2->tahun;
                        $nilaiData_idk2[] = (float)$row_idk2->nilai;
                        $sumber_idk       = $row_idk2->sumber;
                        $periode_idk_id[]   = $row_idk2->id_periode;
                        $tahun_idk21[]    = $bulan[$row_idk2->periode]."-".$row_idk2->tahun;
                    }
                    $datay_idk2 = $nilaiData_idk2;
                    $tahun_idk2 = $tahun_idk2;
                    $periode_ikk_max = max($periode_idk_id);
                    $periode_ikk_tahun=max($tahun_idk21)." Antar Provinsi" ;
                    if($nilaiData_idk2[3] > $nilaiData_idk2[5]){
                        $paragraf_13_2    ="Indeks Kedalaman Kemiskinan ". $xname ." pada ".$tahun_idk[5]." menurun dibandingkan dengan ".$tahun_idk[3].". Pada ".$tahun_idk[5]." Indeks Kedalaman Kemiskinan ". $xname ." adalah sebesar ". number_format(end($nilaiData_idk2),2) ."%, sedangkan pada ".$tahun_idk[3]." Indeks Kedalaman Kemiskinan tercatat sebesar ".number_format($nilaiData_idk2[3],2)."%. ";
                        if($nilaiData_idk[5]>$nilaiData_idk2[5]){
                            $paragraf_13_3  ="Capaian Indeks Kedalaman Kemiskinan ". $xname ." pada ".$tahun_idk[5]." berada dibawah capaian nasional. Indeks Kedalaman Kemiskinan nasional pada ".$tahun_idk[5]." adalah sebesar ".number_format(end($nilaiData_idk),2) ."% ";
                        }else{
                            $paragraf_13_3  ="Capaian Indeks Kedalaman Kemiskinan ". $xname ." pada ".$tahun_idk[5]." berada diatas capaian nasional. Indeks Kedalaman Kemiskinan nasional pada ".$tahun_idk[5]." adalah sebesar ".number_format(end($nilaiData_idk),2) ."% ";
                        }
                    } else {
                        $paragraf_13_2 ="Indeks Kedalaman Kemiskinan ". $xname ." pada ".$tahun_idk[5]." meningkat dibandingkan dengan ".$tahun_idk[3].". Pada ".$tahun_idk[5]." Indeks Kedalaman Kemiskinan ". $xname ." adalah sebesar ". number_format(end($nilaiData_idk2),2) ."%, sedangkan pada ".$tahun_idk[3]." Indeks Kedalaman Kemiskinan tercatat sebesar ".number_format($nilaiData_idk2[3],2)."%. ";
                        if($nilaiData_idk[5]>$nilaiData_idk2[5]){
                            $paragraf_13_3 ="Capaian Indeks Kedalaman Kemiskinan ". $xname ." pada ".$tahun_idk[5]." berada dibawah capaian nasional. Indeks Kedalaman Kemiskinan nasional pada ".$tahun_idk[5]." adalah sebesar ".number_format(end($nilaiData_idk),2) ."% ";
                        }else{
                            $paragraf_13_3 ="Capaian Indeks Kedalaman Kemiskinan ". $xname ." pada ".$tahun_idk[5]." berada diatas capaian nasional. Indeks Kedalaman Kemiskinan nasional pada ".$tahun_idk[5]." adalah sebesar ".number_format(end($nilaiData_idk),2) ."% ";
                        }
                    }
                    $max_k_ikk = "";
                    $perbandingan_ikk ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='39' AND e.id_periode='$periode_ikk_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='39' AND id_periode='$periode_ikk_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_ikk_per = $this->db->query($perbandingan_ikk);
                    foreach ($list_ikk_per->result() as $row_ikk_per) {
                        $label_ikk[]     = $row_ikk_per->label;
                        $nilai_ikk_per[] = $row_ikk_per->nilai;
                    }
                    $label_data_ikk     = $label_ikk;
                    $nilai_data_ikk_per = $nilai_ikk_per;
                    
                    
                    
//                    print_r(number_format(end($nilaiData_ipm),2));echo '</Br>';                 
//                    print_r($paragraf_9_2); echo '</Br>';
//                    print_r($paragraf_9_3);echo '</Br>';
//                    exit();
                    
                }
        
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        $section = $phpWord->addSection();
        $phpWord->addFontStyle('rStyle', array('bold'=>true, 'italic'=>true, 'size'=>16));
        $phpWord->addParagraphStyle('pStyle', array('align'=>'center', 'spaceAfter'=>100));
        $phpWord->addParagraphStyle('rTengah', array('align'=>'justify', 'spaceAfter'=>100, 'size'=>12));
        $phpWord->addParagraphStyle('rKiriKanan', array('align'=>'both', 'spaceAfter'=>100, 'size'=>10));
        $phpWord->addParagraphStyle('jdlGr', array('align'=>'center', 'spaceAfter'=>100, 'size'=>11));
        
        $section->addText('Perkembangan Indikator Makro Pembangunan', 'rStyle', 'pStyle');
        $section->addText("$xname", 'rStyle', 'pStyle');
        $section->addTextBreak();
      
    $style1 = array( 
        'width' => Converter::inchToEmu(6),
        'height' => Converter::inchToEmu(2),    
//'3d'             => true,
                'showAxisLabels' => true,
                'showGridX'      => true,
                'showGridY'      => true,
//        'gridX' => TRUE,
//        'gridY' => TRUE,
  
 //       '3d'             => true
//        'dataLabelOptions' => array(
//          'showCatName' => true,
//          'showVal' => Converter::htmlToRgb(),
//          
//        )
//    '3d'             => true,
//    'showAxisLabels' => $showAxisLabels,
//    'showGridX'      => $showGridLines,
//    'showGridY'      => $showGridLines,
    );
 $style2_1 = array( 
        'width' => Converter::inchToEmu(6),
        'height' => Converter::inchToEmu(2),
     'showAxisLabels' => $showAxisLabels,
                'showGridX'      => $showGridLines,
                'showGridY'      => $showGridLines,
//        'valueAxisTitle' => 'Last month consumed in kW',
//        'showAxisLabels' => true,
//        'showLegend' => true,
//        'gridX' => true,
//        'gridY' => true,
//        'showVal' => true,
//        'showCatName' => true
    );
 
$stylee2 = array(
    'showAxisLabels' => $showAxisLabels,
    'showGridX'      => $showGridLines,
    'showGridY'      => $showGridLines,
);
 
$chartTypes = array('line');
$twoSeries = array('line');
        $section->addText('Pertumbuhan Ekonomi', 'rStyle');
        $section->addText("$deskripsi1", 'rKiriKanan');
        $section->addText("$paragraf_1_2", 'rKiriKanan');
        $section->addText("$paragraf_1_3", 'rKiriKanan');
        
$categories_1_1 =$tahun1_pro;
$series_1_n = $nilaiData1;
$series_1_1 = $nilaiData1_pro;
foreach ($chartTypes as $chartType) {
    $section->addTitle("Gambar ".$gambar++." Perkembangan Pertumbuhan Ekonomi (%)", 'jdlGr');
    $chart = $section->addChart($chartType, $categories_1_1, $series_1_n, $style1);
    $chart->getStyle()->setShowGridX($showGridLines);
    $chart->getStyle()->setShowGridX($showGridLines);
    $chart->getStyle()->setShowGridY($showGridLines);
    $chart->getStyle()->setShowAxisLabels($showAxisLabels);
  //  $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
    if (in_array($chartType, $twoSeries)) {
        $chart->addSeries($categories_1_1, $series_1_1, $style1);
    }
    $section->addTextBreak();
}
//$chart_1_1 = $section->addChart('line', $categories_1_1, $series_1_n, $series_1_1, $stylee);

$chartTypes_1_2 = array('column');
$twoSeries_1_2 = array('column');
$categories_1_2 = $label_data_ppe;
$series_1_2     = $nilai_data_ppe_per;
$section->addTitle("Gambar ".$gambar++." Perbandingan Pertumbuhan Ekonomi tahun 2020 Antar Provinsi (%)");

$chart_1_2      = $section->addChart('column', $categories_1_2, $series_1_2, $style1);
$section->addTextBreak();


        $section->addText('Perkembangan PDRB Per Kapita ADHB', 'rStyle');
        $section->addText("$deskripsi2", 'rTengah');
        $section->addText("$paragraf_2_2", 'rTengah');
        $section->addText("$paragraf_2_3", 'rTengah');
    
$categories_2_2 = $tahun_adhb;
$series_2_n     = $nilaiData_max;
$series_2_2     = $nilaiData_max_p;
$chartTypes_2_2 = array('column');
$twoSeries_2_2 = array('column');
foreach ($chartTypes_2_2 as $chartType2) {
    $section->addTitle("Gambar ".$gambar++." Perkembangan PDRB Pe]r Kapita ADHB (Juta Rupiah");
    $chart_2_2      = $section->addChart($chartType2, $categories_2_2, $series_2_n, $style1);        
    if (in_array($chartType2, $twoSeries_2_2)) {
         $chart_2_2->addSeries($categories_2_2, $series_2_2, $style2_1);
    }
        $section->addTextBreak();
}

$categories_2_3 = $label_data_adhb;
$series_2_3     = $nilai_data_adhb_per;
$section->addTitle("Gambar ".$gambar++." 4 Perbandingan PDRB Per Kapita ADHB tahun 2019 Antar Provinsi (Juta Rupiah)");
$chart_2_3      = $section->addChart('column', $categories_2_3, $series_2_3, $style1);
$section->addTextBreak();         

        $section->addText('Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010', 'rStyle');
        $section->addText("$deskripsi3", 'rTengah');
        $section->addText("$paragraf_3_2", 'rTengah');
        $section->addText("$paragraf_3_3", 'rTengah');
$categories_3_1 =$tahun_adhk1;
$series_3_n     = $datay_adhk1;
$series_3_1     = $datay_adhk2;
foreach ($chartTypes as $chartType) {
    $section->addTitle("Gambar ".$gambar++." Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010 (Juta Rupiah)", 'jdlGr');
    $chart_3_1 = $section->addChart($chartType, $categories_3_1, $series_3_n, $style1);
    $chart_3_1->getStyle()->setShowGridX($showGridLines);
    if (in_array($chartType, $twoSeries)) {
        $chart_3_1->addSeries($categories_3_1, $series_3_1, $style1);
    }
    $section->addTextBreak();
}        
$categories_3_2 = $label_data_adhk;
$series_3_2     = $nilai_data_adhk_per;
$section->addTitle("Gambar ".$gambar++." Perbandingan PDRB per Kapita ADHK (2010) tahun 2020 Antar Provinsi (Juta Rupiah)");
$chart_3_2      = $section->addChart('column', $categories_3_2, $series_3_2, $style1);
$section->addTextBreak();
        
        $section->addText('Perkembangan Jumlah Penganggur', 'rStyle');
        $section->addText("$deskripsi4", 'rTengah');
        $section->addText("$paragraf_4_2", 'rTengah');
$categories_4_1 = $tahun_jp;
$series_4_1     = $datay_jp;
$section->addTitle("Gambar ".$gambar++." Perkembangan Jumlah Penganggur (Ribu Orang)");
$chart_4_1      = $section->addChart('column', $categories_4_1, $series_4_1, $style1);
$section->addTextBreak();
$categories_4_2 = $label_data_jp;
$series_4_2     = $nilai_data_jp_per;
$section->addTitle("Gambar ".$gambar++." Perbandingan Jumlah Penganggur Ags 2020 Antar Provinsi (Ribu Orang)");
$chart_4_2      = $section->addChart('column', $categories_4_2, $series_4_2, $style1);
$section->addTextBreak();

        $section->addText('Tingkat Pengangguran Terbuka', 'rStyle');
        $section->addText("$deskripsi5", 'rTengah');
        $section->addText("$paragraf_5_2", 'rTengah');
        $section->addText("$paragraf_5_3", 'rTengah');
$categories_5_1 = $tahun_tpt;
$series_5_n     = $datay_tpt;
$series_5_1     = $datay_tpt2;
foreach ($chartTypes as $chartType) {
    $section->addTitle("Gambar ".$gambar++." Tingkat Pengangguran Terbuka (%)", 'jdlGr');
    $chart_5_1 = $section->addChart($chartType, $categories_5_1, $series_5_n, $style1);
    $chart_5_1->getStyle()->setShowGridX($showGridLines);
    if (in_array($chartType, $twoSeries)) {
        $chart_5_1->addSeries($categories_5_1, $series_5_1, $style1);
    }
    $section->addTextBreak();
}
                                  
$categories_5_2 = $label_data_tpt;
$series_5_2     = $nilai_data_tpt_per;
$section->addTitle("Gambar ".$gambar++." Perbandingan Tingkat Pengangguran Terbuka Ags 2020 Antar Provinsi (%)");
$chart_5_2      = $section->addChart('column', $categories_5_2, $series_5_2, $style1);
$section->addTextBreak();

        $section->addText('Indeks Pembangunan Manusia', 'rStyle');
        $section->addText("$deskripsi6", 'rTengah');
        $section->addText("$paragraf_6_2", 'rTengah');
        //$section->addText("$paragraf_6_3", 'rTengah');
$categories_6_1 = $tahun_ipm;
$series_6_n     = $datay_ipm;
$series_6_1     = $datay_ipm2;
foreach ($chartTypes as $chartType) {
    $section->addTitle("Gambar 11 Perkembangan Indeks Pembangunan Manusia(%)", 'jdlGr');
    $chart_6_1 = $section->addChart($chartType, $categories_6_1, $series_6_n, $style1);
    $chart_6_1->getStyle()->setShowGridX($showGridLines);
    if (in_array($chartType, $twoSeries)) {
        $chart_6_1->addSeries($categories_6_1, $series_6_1, $style1);
    }
    $section->addTextBreak();
}
$categories_6_2 = $label_data_ipm;
$series_6_2     = $nilai_data_ipm_per;
$section->addTitle("Gambar 12 Perbandingan Indeks Pembangunan Manusia Tahun 2019 Antar Provinsi (%)");
$chart_6_2      = $section->addChart('column', $categories_6_2, $series_6_2, $style1);
$section->addTextBreak();

        $section->addText('Gini Rasio', 'rStyle');
        $section->addText("$deskripsi7", 'rTengah');
        $section->addText("$paragraf_7_2", 'rTengah');
        $section->addText("$paragraf_7_3", 'rTengah');
$categories_7_1 = $tahun_ppk;
$series_7_n     = $datay_ppk;
$series_7_1     = $nilaiData_gr22;
foreach ($chartTypes as $chartType) {
    $section->addTitle("Gambar 13 Perkembangan Gini Rasio", 'jdlGr');
    $chart_7_1 = $section->addChart($chartType, $categories_7_1, $series_7_n, $style1);
    $chart_7_1->getStyle()->setShowGridX($showGridLines);
    if (in_array($chartType, $twoSeries)) {
        $chart_7_1->addSeries($categories_7_1, $series_7_1, $style1);
    }
    $section->addTextBreak();
}

$categories_7_2 = $label_data_gr;
$series_7_2     = $nilai_data_gr_per;
$section->addTitle("Gambar 14 Perbandingan Gini Rasio 2019 Antar Provinsi");
$chart_7_2      = $section->addChart('column', $categories_7_2, $series_7_2, $style1);
$section->addTextBreak();

        $section->addText('Angka Harapan Hidup', 'rStyle');
        $section->addText("$deskripsi8", 'rTengah');
        $section->addText("$paragraf_8_2", 'rTengah');
        $section->addText("$paragraf_8_3", 'rTengah');
$categories_8_1 = $tahun_ahh;
$series_8_n     = $datay_ahh;
$series_8_1     = $datay_ahh2;
foreach ($chartTypes as $chartType) {
    $section->addTitle("Gambar 15 Perkembangan Angka Harapan Hidup (Tahun)", 'jdlGr');
    $chart_8_1 = $section->addChart($chartType, $categories_8_1, $series_8_n, $style1);
    $chart_8_1->getStyle()->setShowGridX($showGridLines);
    if (in_array($chartType, $twoSeries)) {
        $chart_8_1->addSeries($categories_8_1, $series_8_1, $style1);
    }
    $section->addTextBreak();
}
$categories_8_2 = $label_ahh;
$series_8_2     = $nilai_ahh_per;
$section->addTitle("Gambar 16 Perbandingan Angka Harapan Hidup tahun 2018 Antar Provinsi (Tahun)");
$chart_8_2      = $section->addChart('column', $categories_8_2, $series_8_2, $style1);
$section->addTextBreak();
        
        $section->addText('Rata-rata Lama Sekolah', 'rStyle');
        $section->addText("$deskripsi9", 'rTengah');
        $section->addText("$paragraf_9_2", 'rTengah');
        $section->addText("$paragraf_9_3", 'rTengah');
$categories_9_1 = $tahun_rls;
$series_9_n     = $datay_rls;
$series_9_1     = $datay_rls2;
foreach ($chartTypes as $chartType) {
    $section->addTitle("Gambar 17 Perkembangan Rata-rata Lama Sekolah (Tahun)", 'jdlGr');
    $chart_9_1 = $section->addChart($chartType, $categories_9_1, $series_9_n, $style1);
    $chart_9_1->getStyle()->setShowGridX($showGridLines);
    if (in_array($chartType, $twoSeries)) {
        $chart_9_1->addSeries($categories_9_1, $series_9_1, $style1);
    }
    $section->addTextBreak();
}
$categories_9_2 = $label_data_rls;
$series_9_2     = $nilai_data_rls_per;
$section->addTitle("Gambar 18 Perbandingan Rata-rata Lama Sekolah tahun 2018 Antar Provinsi (Tahun)");
$chart_9_2      = $section->addChart('column', $categories_9_2, $series_9_2, $style1);
$section->addTextBreak();
        
        $section->addText('Harapan Lama Sekolah', 'rStyle');
        $section->addText("$deskripsi10", 'rTengah');
        $section->addText("$paragraf_10_2", 'rTengah');
        $section->addText("$paragraf_10_3", 'rTengah');
$categories_10_1 = $tahun_hls;
$series_10_n     = $datay_hls;
$series_10_1     = $datay_hls2;
foreach ($chartTypes as $chartType) {
    $section->addTitle("Gambar 19 Perkembangan Harapan Lama Sekolah (Tahun)", 'jdlGr');
    $chart_10_1 = $section->addChart($chartType, $categories_10_1, $series_10_n, $style1);
    $chart_10_1->getStyle()->setShowGridX($showGridLines);
    if (in_array($chartType, $twoSeries)) {
        $chart_10_1->addSeries($categories_10_1, $series_10_1, $style1);
    }
    $section->addTextBreak();
}
$categories_10_2 = $label_data_hls;
$series_10_2     = $nilai_data_hls_per;
$section->addTitle("Gambar 20 Perbandingan Harapan Lama Sekolah tahun 2018 Antar Provinsi (Tahun)");
$chart_10_2      = $section->addChart('column', $categories_10_2, $series_10_2, $style1);
$section->addTextBreak();                    
        
        $section->addText('Pengeluaran per Kapita', 'rStyle');
        $section->addText("$deskripsi11", 'rTengah');
        $section->addText("$paragraf_10_2", 'rTengah');
        $section->addText("$paragraf_10_3", 'rTengah');
$categories_10_1 = $tahun_hls;
$series_10_n     = $datay_hls;
$series_10_1     = $datay_hls2;
foreach ($chartTypes as $chartType) {
    $section->addTitle("Gambar 19 Perkembangan Harapan Lama Sekolah (Tahun)", 'jdlGr');
    $chart_10_1 = $section->addChart($chartType, $categories_10_1, $series_10_n, $style1);
    $chart_10_1->getStyle()->setShowGridX($showGridLines);
    if (in_array($chartType, $twoSeries)) {
        $chart_10_1->addSeries($categories_10_1, $series_10_1, $style1);
    }
    $section->addTextBreak();
}
$categories_10_2 = $label_data_hls;
$series_10_2     = $nilai_data_hls_per;
$section->addTitle("Gambar 20 Perbandingan Harapan Lama Sekolah tahun 2018 Antar Provinsi (Tahun)");
$chart_10_2      = $section->addChart('column', $categories_10_2, $series_10_2, $style1);
$section->addTextBreak();        
        
        $section->addText('Tingkat Kemiskinan', 'rStyle');
        $section->addText("$deskripsi12", 'rTengah');
       // $section->addText("$paragraf_6_2", 'rTengah');
        //$section->addText("$paragraf_6_3", 'rTengah');

        $section->addText('Indeks Kedalaman Kemiskinan', 'rStyle');
        $section->addText("$deskripsi13", 'rTengah');
       // $section->addText("$paragraf_6_2", 'rTengah');
        //$section->addText("$paragraf_6_3", 'rTengah');

        $section->addText('Jumlah Penduduk Miskin', 'rStyle');
        $section->addText("$deskripsi14", 'rTengah');
       // $section->addText("$paragraf_6_2", 'rTengah');
        //$section->addText("$paragraf_6_3", 'rTengah');

        
        
        $filename = $xname . '.docx';		
    	
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); //mime type
        header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
    }
            
    
    function download_template(){
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('template.docx');

$templateProcessor->setValues([
    'number' => '212/SKD/VII/2019',
    'name' => 'Alfa',
    'birthplace' => 'Bandung',
    'birthdate' => '4 Mei 1991',
    'gender' => 'Laki-Laki',
    'religion' => 'Islam',
    'address' => 'Jln. ABC no 12',
    'date' => date('Y-m-d'),
]);

$filename = 'tessss.docx';		
    	
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); //mime type
        header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');

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

$graph->SetMargin(40,20,36,63);

$graph->img->SetAntiAliasing();

$graph->yaxis->HideZeroLabel();
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);

$graph->xgrid->Show();
$graph->xgrid->SetLineStyle("solid");
$graph->xaxis->SetTickLabels(array('A','B','C','D'));
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
