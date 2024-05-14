<?php defined('BASEPATH') OR exit('No direct script access allowed');

include_once(APPPATH."third_party/vendor/autoload.php");
use PhpOffice\PhpWord\Autoloader;
//Autoloader::register();
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\Common\XMLWriter;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TestHelperDOCX;
//use PhpOffice\PhpWord\SimpleType\TextAlignment;


class Laporan_word_e extends CI_Controller {
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
        $bulan1 = array( '00' => '','01' => 'Januari','02' => 'Februari','03' => 'Maret','04' => 'April','05' => 'Mei','06' => 'Juni','07' => 'Juli','08' => 'Agustus','09' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember',);
        $prde = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
        $xname="";
        $query="";  
        $gambar=1;
        $nomor=1;
        $daftarisi=1;
        $daftargamabar=1;
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
            if($peek->id =='38'){ $deskripsi15   = $peek->deskripsi; }
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
            $sql_ppe = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
            $list_ppe = $this->db->query($sql_ppe);
            foreach ($list_ppe->result() as $row_ppe) {
                $tahun1[]   = $row_ppe->tahun;
                $nilaiData1[] = (float)$row_ppe->nilai;
                $nilaimax[] = number_format($row_ppe->nilai,2); 
                
            }
            $max_pe = end($nilaiData1);
            $sql_ppe_pro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='".$id_pro."') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
            $list_ppe_pro = $this->db->query($sql_ppe_pro);
            
            $thn='';
            foreach ($list_ppe_pro->result() as $row_ppe_pro) {
                $tahun1_pro[]   = $row_ppe_pro->tahun;
                $nilaiData1_pro[] = (float)$row_ppe_pro->nilai;
                $nilaimax_pro[] = number_format($row_ppe_pro->nilai,2); 
                $sumber_pe = $row_ppe_pro->sumber;
                $periode_pe[] = $row_ppe_pro->id_periode;
                $periode = $row_ppe_pro->periode;
                if($periode == '00'){ $thn[]=$row_ppe_pro->tahun;  }
                else{ $thn[]=  $prde[$row_ppe_pro->periode]." - ".$row_ppe_pro->tahun; }
            }
            $thn_ex=$thn;
            $periode_pe_max=max($periode_pe);
            $data1 = substr($periode_pe_max,0, 4);
            $data2 = substr($periode_pe_max, -2);
           
            if($data2 == '00'){ $tahun_pe_max=$data1." Antar Provinsi" ; }
            else{ $tahun_pe_max=  $prde[$data2]." - ".$data1." Antar Provinsi"; }
            $datay1 = $nilaiData1;
            $datay2 = $nilaiData1_pro;
            if($nilaiData1_pro[4] > $nilaiData1_pro[5]){
                $meningkatmenurun = 'menurun';
                if($nilaimax[5]>$nilaimax_pro[5]){ 
                    $dibawahdiatas ='di bawah';
                }
                else{ 
                    $dibawahdiatas ='di atas';
                }
            } 
            else {
                $meningkatmenurun = 'meningkat';
                if($nilaimax[5]>$nilaimax_pro[5]){ $dibawahdiatas ='di bawah'; }
                else{ $dibawahdiatas ='di atas'; }
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
                    //$wrnPE              = $wrnPE;
                    
                  //Perkembangan PDRB Per Kapita ADHB (Rp)
                    $sql_adhb = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_adhb = $this->db->query($sql_adhb);
                    foreach ($list_adhb->result() as $row_adhb) {
                        $tahun_adhb[]      = $row_adhb->tahun;
                        $nilaiData_adhb1 = (float)$row_adhb->nilai/1000000;
                        $nilaiData_adhb11[] = number_format($nilaiData_adhb1,2);
                        $nilaiData_max[]   = (float)$row_adhb->nilai;
                    }
                    $datay_adhb1 = $nilaiData_adhb11;
                    $tahun_adhb1 = $tahun_adhb;
 
                    $max_pdrb = end($nilaiData_adhb11);
                    $sql_adhb2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_adhb2 = $this->db->query($sql_adhb2);
                    foreach ($list_adhb2->result() as $row_adhb2) {
                        $tahun_adhb2[]   = $row_adhb2->tahun;
                        $nilaiData_adhb2 = (float)$row_adhb2->nilai/1000000;
                        $nilaiData_adhb22[] = number_format($nilaiData_adhb2,2);
                        $nilaiData_max_p[] = (float)$row_adhb2->nilai;
                        $sumber_adhb       = $row_adhb2->sumber;
                        $periode_adhb[] = $row_adhb2->id_periode;
                        $ket_adhb2[]  = $row_adhb2->keterangan;
                    }
                    $datay_adhb2        = $nilaiData_adhb22;
                    $tahun_adhb2        = $tahun_adhb2;
                    $tahunadhb          = end($tahun_adhb1);
                    $periode_adhb_max   = max($periode_adhb);
                    $periode_adhb_tahun = max($tahun_adhb2)." Antar Provinsi" ;
                   
                    $max_adhb_k  =" ";                    
                    if($nilaiData_max_p[4] > $nilaiData_max_p[5]){
                        $meningkatmenurunADHB='menurun';
                        if($nilaiData_max[5] > $nilaiData_max_p[5]){
                            $dibawahdiatasADHB='di bawah';
                            }else{
                            $dibawahdiatasADHB='di atas';
                            }
                    } else {
                        $meningkatmenurunADHB=='meningkat';
                        if($nilaiData_max[5] > $nilaiData_max_p[5]){
                            $dibawahdiatasADHB='di bawah';
                            }else{
                            $dibawahdiatasADHB='di atas';
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
                        $nilai_adhb_per = $row_adhb_per->nilai/1000000;
                        $nilai_adhb_per1[] = number_format($nilai_adhb_per,2);
                    }
                    $label_data_adhb     = $label_adhb;
                    $nilai_data_adhb_per = $nilai_adhb_per1;
                    
                    //adhk (Rp)
                    $sql_adhk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_adhk = $this->db->query($sql_adhk);
                    foreach ($list_adhk->result() as $row_adhk) {
                        $tahun_adhk[]   = $row_adhk->tahun;
                        $nilaiData_adhk1 = (float)$row_adhk->nilai/1000000;
                        $nilaiData_adhk11[] = number_format($nilaiData_adhk1,2);
                        $adhk_nasional[] = (float)$row_adhk->nilai;                        
                    }
                    $datay_adhk1 = $nilaiData_adhk11;
                    $tahun_adhk1 = $tahun_adhk;
                    
                    
                    $sql_adhk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_adhk2 = $this->db->query($sql_adhk2);
                    foreach ($list_adhk2->result() as $row_adhk2) {
                        $tahun_adhk2[]     = $row_adhk2->tahun;
                        $nilaiData_adhk22  = (float)$row_adhk2->nilai/1000000;
                        $nilaiData_adhk2[] = number_format($nilaiData_adhk22,2);
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
                        $meningkatmenurunADHK ='menurun';                        
                        if($adhk_nasional[5] > $adhk_p[5]){
                            $dibawahdiatasADHK='di bawah';
                        }else{
                            $dibawahdiatasADHK='di atas';
                        }
                    } else { 
                        $meningkatmenurunADHK='meningkat';                        
                        if($adhk_nasional[5] > $adhk_p[5]){
                            $dibawahdiatasADHK='di bawah';
                        }else{
                            $dibawahdiatasADHK='di atas';
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
                        $nilai_adhk_per1 = $row_adhk_per->nilai/1000000;
                        $nilai_adhk_per[] = number_format($nilai_adhk_per1,2);
                    }
                    $label_data_adhk     = $label_adhk;
                    $nilai_data_adhk_per = $nilai_adhk_per;
                    
                    //jumlah pengangguran (Orang)
                    $sql_jp = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jp = $this->db->query($sql_jp);
                    foreach ($list_jp->result() as $row_jp) {
                        $tahun_jp[]      = $bulan[$row_jp->periode]."-".$row_jp->tahun;
                        $tahunJP[]      = $bulan1[$row_jp->periode]." ".$row_jp->tahun;
                        $tahun_jp1[]     = $row_jp->id_periode;
                        $nilaiData_jp[]  = (float)$row_jp->nilai/1000;
                        $nilai_capaian[] = $row_jp->nilai;
                        $tahun_jp11[] = $row_jp->tahun;
                        $periode_jp1[] = $row_jp->periode;
                    }
                    $datay_jp = $nilaiData_jp;
                    $tahun_jp = $tahun_jp;
                    $periode_jp_max  =max($tahun_jp1);
                    $dataJP1 = substr($periode_jp_max,0, 4);
                    $dataJP2 = substr($periode_jp_max, -2);           
                    if($dataJP2 == '00'){ $periode_jp_tahun =$dataJP1." Antar Provinsi" ; }
                    else{ $periode_jp_tahun =  $bulan1[$dataJP2]." ".$dataJP1." Antar Provinsi"; }
                    
                    $sql_jp2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jp2 = $this->db->query($sql_jp2);
                    foreach ($list_jp2->result() as $row_jp2) {
                        $tahun_jp2[]   = $row_jp2->tahun;
                        $nilaiData_jp22 = (float)$row_jp2->nilai/10000;
                        $nilaiData_jp2[] = number_format($nilaiData_jp22,2);
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
                        $berkurangmeningkat='meningkat';
                        }
                        else{
                        $rt_jp  =$nilai_capaian2[5]-$nilai_capaian2[3];
                        $rt_jpp=abs($nilai_capaian2[3]-$nilai_capaian2[5]);
                        $rt_jp2=$rt_jp/$nilai_capaian2[3];
                        $rt_jp3=$rt_jp2*100;
                        $rt_jp33=number_format($rt_jp3,2);
                        $berkurangmeningkat='meningkat';
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
                        $nilai_jp_per[] = (float)$row_jp_per->nilai/10000;
                    }
                    $label_data_jp     = $label_jp; 
                    $nilai_data_jp_per = $nilai_jp_per;   
                    
                    //tingkat pengangguran terbuka
                    $sql_tpt = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_tpt = $this->db->query($sql_tpt);
                    foreach ($list_tpt->result() as $row_tpt) {
                        $tahun_tpt1[]    = $bulan[$row_tpt->periode]."-".$row_tpt->tahun;
                        $tahun_tpt[]   = $row_tpt->tahun;
                        $nilaiData_tpt1 = (float)$row_tpt->nilai;
                        $nilaiData_tpt[] = number_format($nilaiData_tpt1,2);
                    }
                    $datay_tpt = $nilaiData_tpt;
                    $tahun_tpt = $tahun_tpt1;
                    
                    $sql_tpt2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_tpt2 = $this->db->query($sql_tpt2);
                    foreach ($list_tpt2->result() as $row_tpt2) {
                        $tahun_tpt21[]    = $bulan[$row_tpt2->periode]."-".$row_tpt2->tahun;
                        $tahunTPT2[]    = $bulan1[$row_tpt2->periode]." ".$row_tpt2->tahun;
                        $periode_tpt21[]  = $row_tpt2->periode;
                        $tahun_tpt2[]     = $row_tpt2->tahun;
                        $nilaiData_tpt22  = (float)$row_tpt2->nilai;
                        $nilaiData_tpt2[] = number_format($nilaiData_tpt22,2);
                        $sumber_tpt       = $row_tpt2->sumber;
                        $periode_tpt_id[] =   $row_tpt2->id_periode;
                    }
                    $datay_tpt2 = $nilaiData_tpt2;
                    $tahun_tpt2 = $tahun_tpt2;
                    $periode_tpt_max=max($periode_tpt_id);
                    $dataTPT1 = substr($periode_tpt_max,0, 4);
                    $dataTPT2 = substr($periode_tpt_max, -2);           
                    if($dataTPT2 == '00'){ $periode_tpt_tahun =$dataTPT2." Antar Provinsi" ; }
                    else{ $periode_tpt_tahun =  $bulan1[$dataTPT2]." ".$dataTPT1." Antar Provinsi"; }
                    
                    if($nilaiData_tpt2[3] > $nilaiData_tpt2[5]){         
                        $menurunmeningkatTPT='menurun';
                        if($nilaiData_tpt[5]>$nilaiData_tpt2[5]){
                            $dibawahdiatasTPT='di bawah';}else{
                            $dibawahdiatasTPT='di atas';}
                    } else {
                        $menurunmeningkatTPT='meningkat';
                        if($nilaiData_tpt[5]>$nilaiData_tpt2[5]){
                            $dibawahdiatasTPT='di bawah';}else{
                            $dibawahdiatasTPT='di atas';}
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
                    //$paragraf_6_3='';
                    if($nilaiData_ipm2[4] > $nilaiData_ipm2[5]){
                        $menurunmeningkatIPM='menurun';
                        if($nilaiData_ipm[5]>$nilaiData_ipm2[5]){
                            $dibawahdiatasIPM='dibawah';
                       }else{
                            $dibawahdiatasIPM='diatas';
                        }
                    } else {
                        $menurunmeningkatIPM='meningkat';$paragraf_6_2 ="Indeks Pembangunan Manusia ". $xname ." pada tahun ".$tahun_ipm2[5]." meningkat dibandingkan dengan tahun ".$tahun_ipm2[4].". Pada tahun ".$tahun_ipm2[5]." Indeks Pembangunan Manusia ". $xname ." adalah sebesar ". number_format(end($nilaiData_ipm2),2) ."% sedangkan pada tahun ".$tahun_ipm2[4]."  Indeks Pembangunan Manusia tercatat sebesar ".number_format($nilaiData_ipm2[4],2)."%. ";    
                        if($nilaiData_ipm[5]>$nilaiData_ipm2[5]){
                             $dibawahdiatasIPM='dibawah';
                        }else{
                            $dibawahdiatasIPM='diatas';
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
                        $nilai_ipm_per1 = $row_ipm_per->nilai;
                        $nilai_ipm_per[] = number_format($nilai_ipm_per1,2);
                    }
                    $label_data_ipm     = $label_ipm;
                    $nilai_data_ipm_per = $nilai_ipm_per;
                    
                    
                    //Gini Rasio.
                    $sql_gr = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_gr = $this->db->query($sql_gr);
                    foreach ($list_gr->result() as $row_gr) {
                        $tahun_gr[]    = $bulan[$row_gr->periode]."-".$row_gr->tahun;
                        $tahun_grR[]    = $bulan1[$row_gr->periode]." ".$row_gr->tahun;
                        $nilai_gr = number_format((float)$row_gr->nilai,3);
                        $nilaiData_gr[] = $nilai_gr;
                    }
                    $datay_gr = $nilaiData_gr;
                    $tahun_gr = $tahun_gr;
                    $sql_gr2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_gr2 = $this->db->query($sql_gr2);
                    foreach ($list_gr2->result() as $row_gr2) {
                        $tahun_gr2[]   = $row_gr2->tahun;
                        $periode    = $row_gr2->periode;                        
                        $tahungr2    = $row_gr2->tahun;                        
                        $nilaiData_gr2[] = (float)$row_gr2->nilai;
                        $nilai_gr22 = number_format((float)$row_gr2->nilai,3);
                        $nilaiData_gr22[] = $nilai_gr22;
                        $sumber_gr       = $row_gr2->sumber;
                        $periode_gr_id[]    = $row_gr2->id_periode;
                        $tahun_gr21[]    = $bulan[$row_gr2->periode]."-".$row_gr2->tahun;
                    }
                    $datay_gr2 = $nilaiData_gr22;
                    $tahun_gr2 = $tahun_gr2;
                    $max_k_gr  =  "";
                    $periode_gr_max   = max($periode_gr_id);
                    if($periode == '00'){ $periode_gr_tahun =$tahun_gr2." Antar Provinsi" ; }
                    else{ $periode_gr_tahun =  $bulan1[$periode]." ".$tahungr2." Antar Provinsi"; }
                    if($nilaiData_gr2[3] > $nilaiData_gr2[5]){      
                        $menurunmeningkatGR='menurun';
                        if($nilaiData_gr[5]>$nilaiData_gr2[5]){
                            $dibawahdiatasGR='di bawah';
                            }else{
                            $dibawahdiatasGR='di atas';
                            }
                    } elseif($nilaiData_gr2[3] < $nilaiData_gr2[5]) {
                        $menurunmeningkatGR='meningkat';
                        if($nilaiData_gr[5]>$nilaiData_gr2[5]){
                            $dibawahdiatasGR='di bawah';
                            }else{
                            $dibawahdiatasGR='di atas';
                            }
                    }else{
                        $menurunmeningkatGR='sama';
                        if($nilaiData_gr[5]>$nilaiData_gr2[5]){
                            $dibawahdiatasGR='di bawah';
                            }else{
                            $dibawahdiatasGR='di atas';
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
                        $nilai_gr_per1 = $row_gr_per->nilai;
                        $nilai_gr_per[] = number_format($nilai_gr_per1,3);
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
                        $menurunmeningkatAHH='menurun';
                        if($nilaiData_ahh[5]>$nilaiData_ahh2[5]){
                            $dibawahdiatasAHH='dibawah';
                            //$paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                        }else{
                            $dibawahdiatasAHH='diatas';
                            //$paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada diatas capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                        }
                    } else {
                        $menurunmeningkatAHH='meningkat';
                        if($nilaiData_ahh[5]>$nilaiData_ahh2[5]){
                            $dibawahdiatasAHH='dibawah';
                           // $paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                        }else{
                            $dibawahdiatasAHH='diatas';

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
                        $menurunmeningkatRLS='menurun';
                        if($nilaiData_rls[5]>$nilaiData_rls2[5]){
                            $dibawahdiatasRLS='di bawah';
                        }else{
                            $dibawahdiatasRLS='di atas';
                        }
                    } else {
                        $menurunmeningkatRLS='meningkat';
                        if($nilaiData_rls[5]>$nilaiData_rls2[5]){
                            $dibawahdiatasRLS='di bawah';
                        }else{
                            $dibawahdiatasRLS='di atas';
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
                        $menurunmeningkatHLS='menurun';
                        if($nilaiData_hls[5]>$nilaiData_hls2[5]){ $dibawahdiatasHLS='di bawah'; }
                        else{ $dibawahdiatasHLS='di atas'; }
                    } 
                    else {
                        $menurunmeningkatHLS='meningkat';
                        if($nilaiData_hls[5]>$nilaiData_hls2[5]){ $dibawahdiatasHLS='di bawah'; }
                        else{ $dibawahdiatasHLS='di atas'; }
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
                        $nilai_ppk1 = number_format((float)$row_ppk->nilai/1000000,2);
                        $nilaiData_ppk1[] = $nilai_ppk1;
                    }
                    $datay_ppk = $nilaiData_ppk1;
                    $tahun_ppk = $tahun_ppk;
                    $sql_ppk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ppk2 = $this->db->query($sql_ppk2);
                    foreach ($list_ppk2->result() as $row_ppk2) {
                        $tahun_ppk2[]     = $row_ppk2->tahun;
                        $nilaiData_ppk2[] = (float)$row_ppk2->nilai;
                        $nilai_ppk22 = number_format((float)$row_ppk2->nilai/1000000,2);
                        $nilaiData_ppk22[] = $nilai_ppk22;
                        $sumber_ppk       = $row_ppk->sumber;
                        $periode_ppk_id[] = $row_ppk2->id_periode;
                        $tahun_ppk21[]    = $bulan[$row_ppk2->periode]."-".$row_ppk2->tahun;
                    }
                    $datay_ppk2 = $nilaiData_ppk22;
                    $tahun_ppk2 = $tahun_ppk2;
                    $periode_ppk_max   = max($periode_ppk_id);
                    $periode_ppk_tahun = max($tahun_ppk2)." Antar Provinsi" ;
                    if($nilaiData_ppk2[4] > $nilaiData_ppk2[5]){
                        $menurunmeningkatPPK='menurun';
                        if($nilaiData_ppk[5]>$nilaiData_ppk2[5]){
                            $dibawahdiatasPPK='di bawah';
                            }else{
                            $dibawahdiatasPPK='di atas';
                            }
                    } else {
                        $menurunmeningkatPPK='meningkat';
                        if($nilaiData_ppk[5]>$nilaiData_ppk2[5]){
                            $dibawahdiatasPPK='di bawah';
                            }else{
                            $dibawahdiatasPPK='di atas';
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
                    $list_ppk_per = $this->db->query($perbandingan_ppk);
                    foreach ($list_ppk_per->result() as $row_ppk_per) {
                        $label_ppk[]     = $row_ppk_per->label;
                        $nilai_ppk       = number_format($row_ppk_per->nilai/1000000,2);
                        $nilai_ppk_per[] = $nilai_ppk;
                    }
                    $label_data_ppk     = $label_ppk;
                    $nilai_data_ppk_per = $nilai_ppk_per;
                    
                    
                    //Tingkat Kemiskinan
                    $sql_tk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_tk = $this->db->query($sql_tk);
                    foreach ($list_tk->result() as $row_tk) {
                        $tahun_tk[]    = $bulan[$row_tk->periode]."-".$row_tk->tahun;
                        $tahun_tkk[]    = $bulan1[$row_tk->periode]." ".$row_tk->tahun;
                        $nilaiData_tk[] = (float)$row_tk->nilai;
                    }
                    $datay_tk = $nilaiData_tk;
                    $tahun_tk = $tahun_tk;
                    $sql_tk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_tk2 = $this->db->query($sql_tk2);
                    foreach ($list_tk2->result() as $row_tk2) {
                        $tahun_tk2[]   = $row_tk2->tahun;
                        $tahun_tk22   = $row_tk2->tahun;
                        $nilaiData_tk2[] = (float)$row_tk2->nilai;
                        $sumber_tk       = $row_tk2->sumber;
                        $periode_tk_id[] = $row_tk2->id_periode;
                        $periode_tk = $row_tk2->periode;
                        $tahun_tk21[]    = $bulan[$row_tk2->periode]."-".$row_tk2->tahun;
                    }
                    $datay_tk2 = $nilaiData_tk2;
                    $tahun_tk2 = $tahun_tk2;
                    $periode_tk_max = max($periode_tk_id);
                    //$periode_tk_tahun=max($tahun_tk21)." Antar Provinsi" ;
                    if($periode_tk == '00'){ $periode_tk_tahun =$tahun_tk22." Antar Provinsi" ; }
                    else{ $periode_tk_tahun =  $bulan1[$periode_tk]." ".$tahun_tk22." Antar Provinsi"; }
                    
                    if($nilaiData_tk2[3] > $nilaiData_tk2[5]){
                        $menurunmeningkatTK='menurun';
                        if($nilaiData_tk[5]>$nilaiData_tk2[5]){
                            $dibawahdiatasTK='di bawah';
                            }else{
                            $dibawahdiatasTK='di atas';
                            //$paragraf_12_3  ="Capaian Angka tingkat Kemiskinan ". $xname ." pada ".$tahun_tk[5]." berada diatas capaian nasional. Angka tingkat Kemiskinan nasional pada ".$tahun_tk[5]." adalah sebesar ".number_format(end($nilaiData_tk),2) ."% ";
                        }
                    } else {
                        $menurunmeningkatTK='meningkat';
                        if($nilaiData_tk[5]>$nilaiData_tk2[5]){
                            $dibawahdiatasTK='di bawah';
                        }else{
                            $dibawahdiatasTK='di atas';
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
                        $tahunIDK1[]    = $bulan1[$row_idk->periode]." ".$row_idk->tahun;
                        $nilai_idk = number_format((float)$row_idk->nilai,2);
                        $nilaiData_idk[] = $nilai_idk;
                    }
                    $datay_idk = $nilaiData_idk;
                    $tahun_idk = $tahun_idk;
                    $sql_idk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_idk2 = $this->db->query($sql_idk2);
                    foreach ($list_idk2->result() as $row_idk2) {
                        $tahun_idk2[]     = $row_idk2->tahun;
                        $nilai_idk2       = number_format((float)$row_idk2->nilai,2);
                        $nilaiData_idk2[] = $nilai_idk2;
                        $sumber_idk       = $row_idk2->sumber;
                        $periode_idk_id[] = $row_idk2->id_periode;
                        //$tahun_idk21[]    = $bulan1[$row_idk2->periode]." ".$row_idk2->tahun;
                        $periodeidk       = $row_idk2->periode;
                        $tahunidk         = $row_idk2->tahun;
                        $periode_ikk_max  = $row_idk2->id_periode;
                    }
                    $datay_idk2 = $nilaiData_idk2;
                    $tahun_idk2 = $tahun_idk2;
                    $periode_ikk_tahun=$bulan1[$periodeidk]." ".$tahunidk." Antar Provinsi";
                    if($nilaiData_idk2[3] > $nilaiData_idk2[5]){
                        $menurunmeningkatIKK='menurun';
                        if($nilaiData_idk[5]>$nilaiData_idk2[5]){
                            $dibawahdiatasIKK='di bawah';
                            }else{
                            $dibawahdiatasIKK='di atas';
                            }
                    } else {
                        $menurunmeningkatIKK='meningkat';
                        if($nilaiData_idk[5]>$nilaiData_idk2[5]){
                            $dibawahdiatasIKK='di bawah';
                            }else{
                            $dibawahdiatasIKK='di atas';
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
                        $nilai_ikk       = number_format($row_ikk_per->nilai,2);
                        $nilai_ikk_per[] = $nilai_ikk;
                    }
                    $label_data_ikk     = $label_ikk;
                    $nilai_data_ikk_per = $nilai_ikk_per;
                    
                    //indeks Keparahan Kemiskinan(P2)
                    $sql_ikk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ikk = $this->db->query($sql_ikk);
                    foreach ($list_ikk->result() as $row_ikk) {
                        //$tahun_idk[]   = $row_idk->tahun;
                        $tahun_ikk[]    = $bulan[$row_ikk->periode]."-".$row_ikk->tahun;
                        $tahunIKK1[]    = $bulan1[$row_ikk->periode]." ".$row_ikk->tahun;
                        $nilai_ikk = number_format((float)$row_ikk->nilai,2);
                        $nilaiData_ikk[] = $nilai_ikk;
                    }
                    $datay_ikk = $nilaiData_ikk;
                    $tahun_ikk = $tahun_ikk;
                    $sql_ikk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ikk2 = $this->db->query($sql_ikk2);
                    foreach ($list_ikk2->result() as $row_ikk2) {
                        $tahun_ikk2[]     = $row_ikk2->tahun;
                        $nilai_ikk2       = number_format((float)$row_ikk2->nilai,2);
                        $nilaiData_ikk2[] = $nilai_ikk2;
                        $sumber_ikk       = $row_ikk2->sumber;
                        $periode_ikk_id[] = $row_ikk2->id_periode;
                        //$tahun_idk21[]    = $bulan1[$row_idk2->periode]." ".$row_idk2->tahun;
                        $periodeikk       = $row_ikk2->periode;
                        $tahunikk         = $row_ikk2->tahun;
                        $periode_ikkk_max  = $row_ikk2->id_periode;
                    }
                    $datay_ikk2 = $nilaiData_ikk2;
                    $tahun_ikk2 = $tahun_ikk2;
                    $periode_ikkk_tahun=$bulan1[$periodeikk]." ".$tahunikk." Antar Provinsi";
                    if($nilaiData_ikk2[3] > $nilaiData_ikk2[5]){
                        $menurunmeningkatIKKK='menurun';
                        if($nilaiData_ikk[5]>$nilaiData_ikk2[5]){
                            $dibawahdiatasIKKK='di bawah';
                            }else{
                            $dibawahdiatasIKKK='di atas';
                            }
                    } else {
                        $menurunmeningkatIKKK='meningkat';
                        if($nilaiData_ikk[5]>$nilaiData_ikk2[5]){
                            $dibawahdiatasIKKK='di bawah';
                            }else{
                            $dibawahdiatasIKKK='di atas';
                            }
                    }
                                                

                    $max_k_ikkk = "";
                    $perbandingan_ikkk ="select p.label as label, e.* 
                                    from provinsi p 
                                    join nilai_indikator e on p.id = e.wilayah 
                                    where (e.id_indikator='38' AND e.id_periode='$periode_ikkk_max')
                                    AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi 
							from nilai_indikator x  
							where id_indikator='38' AND id_periode='$periode_ikkk_max' 
                            group by wilayah) group by wilayah order by wilayah asc                    ";
                    $list_ikkk_per = $this->db->query($perbandingan_ikkk);
                    foreach ($list_ikkk_per->result() as $row_ikkk_per) {
                        $label_ikkk[]     = $row_ikkk_per->label;
                        $nilai_ikkk       = number_format($row_ikkk_per->nilai,2);
                        $nilai_ikkk_per[] = $nilai_ikkk;
                    }
                    $label_data_ikkk     = $label_ikkk;
                    $nilai_data_ikkk_per = $nilai_ikkk_per;
                    
                    
                    
                    //jumlah Penduduk Miskin
                     $sql_jpk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jpk = $this->db->query($sql_jpk);
                    foreach ($list_jpk->result() as $row_jpk) {
                        $tahun_jpk[]    = $bulan1[$row_jpk->periode]." ".$row_jpk->tahun;
                        $nilaiData_jpk[] = (float)$row_jpk->nilai;
                        $nilaiData_jpk1[] = (float)$row_jpk->nilai;
                        $periode_jpk1[] = (float)$row_jpk->id_periode;
                    }
                    $datay_jpk = $nilaiData_jpk;
                    $tahun_jpk = $tahun_jpk;
      
                    $periode_jpk_max    = max($periode_jpk1);
                    $data1_jpk          = substr($periode_jpk_max,0, 4);
                    $data2_jpk          = substr($periode_jpk_max, -2);
                    $periode_jpk_tahun  =  $bulan1[$data2_jpk]." ".$data1_jpk." Antar Provinsi";
                    
                    
                    $sql_jpk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jpk2 = $this->db->query($sql_jpk2);
                    foreach ($list_jpk2->result() as $row_jpk2) {
                        $tahun_jpk2[]   = $row_jpk2->tahun;
                        $nilaiData_jpk2[] = (float)$row_jpk2->nilai;
                        $nilaiData_jpk22[] = (float)$row_jpk2->nilai;
                        $sumber_jpk       = $row_jpk2->sumber;
                        $periode_jpk_id[]  = $row_jpk2->id_periode;
                        $tahun_jpk21[]     = $bulan[$row_jpk2->periode]."-".$row_jpk2->tahun;
                    }
                    $datay_jpk2 = $nilaiData_jpk2;
                    $tahun_jpk2 = $tahun_jpk2;
                    $tahun_jpk21 = $tahun_jpk21;
                    
                    $periode_jpk_max = max($periode_jpk_id);
                    
                    
                     if($nilaiData_jpk22[3] > $nilaiData_jpk22[5]){
                         $rt_jpk=$nilaiData_jpk22[5]-$nilaiData_jpk22[3];
                        $rt_jpkk=abs($nilaiData_jpk22[5]-$nilaiData_jpk22[3]);
                        $rt_jpk2=$rt_jpk/$nilaiData_jpk22[3];
                        $rt_jpk3=abs($rt_jpk2*100);
                        $rt_jpk33=number_format($rt_jpk3,2);
                        $berkurangbertambah='berkurang';
                    }else{
                        $rt_jpk  =$nilaiData_jpk22[5]-$nilaiData_jpk22[3];
                        $rt_jpkk =abs($nilaiData_jpk22[5]-$nilaiData_jpk22[3]);
                        $rt_jpk2 =$rt_jpk/$nilaiData_jpk22[3];
                        $rt_jpk3 =$rt_jpk2*100;
                        $rt_jpk33=number_format($rt_jpk3,2);
                        $berkurangbertambah='bertambah';
                    }

                    $perbandingan_jpk ="select p.label as label, e.* 
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
                    }
                    $label_data_jpk     = $label_jpk;
                    $nilai_data_jpk_per = $nilai_jpk_per;
                 
                    
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        //$lan     = new \PhpOffice\PhpWord\Language\Language();
       
        $cover =base_url("assets/images/laporan/cover/".$pro.".jpg");
        $src= base_url("assets/images/laporan/cover_master_v3.jpg");
        $logo =base_url("assets/images/logopropinsi/".$pro.".png");
        //$hal1 =base_url("assets/images/laporan/hal1.jpg");
        
//        $hal2 =base_url("assets/images/laporan/hal2.jpg");
        
//        $hal8 =base_url("assets/images/laporan/hal8.jpg");
//        $hal9  =base_url("assets/images/laporan/hal9.jpg");
//        $hal10 =base_url("assets/images/laporan/hal10.jpg");
//        $hal18 =base_url("assets/images/laporan/hal18.jpg");
        $hal1 =base_url("assets/images/arcgis/ekonomi.jpg");
        $hal2 =base_url("assets/images/arcgis/adhb.png");
        $hal7 =base_url("assets/images/arcgis/adhk.png");
        $hal8 =base_url("assets/images/arcgis/jumlah_pengangguran_v.png");
        $hal9  =base_url("assets/images/arcgis/TPT1.jpg");
        $hal10 =base_url("assets/images/arcgis/ipm.png");
        $hal11 =base_url("assets/images/arcgis/gr.jpg");
        $hal12 =base_url("assets/images/arcgis/AHH.jpg");
        $hal13 =base_url("assets/images/arcgis/RLS.jpg");
        $hal14 =base_url("assets/images/arcgis/hls3.jpg");
        $hal15 =base_url("assets/images/arcgis/Pengeluaran_perkapita.jpg");
        $hal16 =base_url("assets/images/arcgis/Kedalaman_Kemiskinan.jpg");
        $hal17 =base_url("assets/images/arcgis/keparahan_kemiskinan.png");
        $hal18 =base_url("assets/images/arcgis/perkapita1.png");
        $hal19 =base_url("assets/images/arcgis/JPM.jpg");
        $iconh =base_url("assets/images/laporan/icon.png");
        
        $section = $phpWord->addSection(array(
                'headerHeight' => 50,
                'footerHeight' => 3000
            ));
        $header = $section->addHeader();
        $header->addWatermark($cover,
                array(
                    'headerHeight' => 300, 'marginTop' => -3, 'marginLeft' => -73,
                    //'footerHeight' => -50,
                    'width' => 595,
                    //'height'=> 30,
                    'posHorizontal' => 'absolute', 'posVertical' => 'absolute',
                    )
                );

            // Adding Text element with font customized using explicitly created font style object...
            $fontStyle = new \PhpOffice\PhpWord\Style\Font();            
            $fontStyle->setBold(true);
            $fontStyle->setName('Verdana');
            $fontStyle->setSize(24);
            $fontStyle->setColor('#bfbfbf');
            
//            $section->addTextBreak(18);
//            $section->addImage($logo,
//                    array(
//                        'width' => 119,
//                        'height'=> 120,
//                        'marginLeft' => 45,
//                        ));
//            $section->addTextBreak(2);
//            $myTextElement = $section->addText("Perkembangan Indikator",$fontStyle,array('alignment'=>'right'));
//            $myTextElement = $section->addText("Makro Pembangunan",$fontStyle,array('alignment'=>'right'));
//            $myTextElement = $section->addText($xname);
//            $myTextElement->setFontStyle($fontStyle,array('alignment'=>'right'));
            
            // Halaman Baru
            $section = $phpWord->addSection();
            $header = $section->addHeader();
            $header->addWatermark(base_url("assets/images/header.png"),
                    array(
                        'headerHeight' => 300,
                        'marginTop' => -36,
                        'marginLeft' => -70,
            'footerHeight' => -50,
            'width' => 590,
            //'height'=> 30,
            'posHorizontal' => 'absolute',
             'posVertical' => 'absolute',
            )
        );

            $footer = $section->addFooter();
            $textRun = $footer->addTextRun(array('alignment' => 'center'));
            $textRun->addField('PAGE', array('format' => 'NUMBER'));
            $textRun->addText('');
            $fontStyleName = '';
            $phpWord->addFontStyle( $fontStyleName,array('name' => 'Arial Narrow', 'size' => 14, 'color' => '1B2232', 'bold' => true) );
            $style0 = array( 'width'          => Converter::cmToEmu(13), 'height'         => Converter::cmToEmu(6),
                'showAxisLabels' => true, 'showGridX'      => false, 'showGridY'      => false,
                'showLegend'     => true, 'valueLabelPosition'     => FALSE,
            );
            $style = array( 'width'          => Converter::cmToEmu(16), 'height'         => Converter::cmToEmu(6),
                'showAxisLabels' => true, 'showGridX'      => false, 'showGridY'      => false,
                'showLegend'     => true, 'valueLabelPosition'     => FALSE,  
            );
            
            $style_1 = array(
                'width'          => Converter::cmToEmu(16),
                'height'         => Converter::cmToEmu(6),
               // '3d'             => true,
                'showAxisLabels' => true,
                //'showGridX'      => false,
                'showGridY'      => TRUE,
                 'dataLabelOptions' => array(
                     'showVal'      => false, 'showCatName'      => false, 'showLegendKey'      => false, 'showSerName'      => false,
                        'showPercent'      => false, 'showLeaderLines'      => false, 'showBubbleSize'      => true,
                     )
            );
             $style_2 = array(
                'width'          => Converter::cmToEmu(16),
                'height'         => Converter::cmToEmu(6),
                'showAxisLabels' => true,
                'showGridX'      => false,
                'showGridY'      => false,
                //'showLegend'     => true,
                'valueLabelPosition'     => FALSE,
                  'dataLabelOptions' => array(
                     'showVal'      => false,
                        'showCatName'      => false,
                        'showLegendKey'      => false,
                        'showSerName'      => false,
                        'showPercent'      => false,
                        'showLeaderLines'      => false,
                        'showBubbleSize'      => true,
                      'breakType' => 'continuous'
                     )

            );
             $style_ADHK = array(
                'width'          => Converter::cmToEmu(16),
                'height'         => Converter::cmToEmu(5),
                'showAxisLabels' => true,
                'showGridX'      => false,
                'showGridY'      => false,
                //'showLegend'     => true,
                'valueLabelPosition'     => FALSE,
                  'dataLabelOptions' => array(
                     'showVal'      => false,
                        'showCatName'      => false,
                        'showLegendKey'      => false,
                        'showSerName'      => false,
                        'showPercent'      => false,
                        'showLeaderLines'      => false,
                        'showBubbleSize'      => true,
                      'breakType' => 'continuous'
                     )

            );
             $section = $phpWord->addSection(array('colsNum' => 1, 'breakType' => 'continuous'));
             $chartTypes = array('line');
             $chartTypes2 = array('column');
             $twoSeries = array('bar', 'column', 'line', 'area', 'scatter', 'radar', 'stacked_bar', 'percent_stacked_bar', 'stacked_column', 'percent_stacked_column');
             $threeSeries = array('bar', 'line');
            
//            $fontparagraf = array('name' => 'Book Antiqua (Body)', 'size' => 10, 'spaceAfter'=>80);
             $fontparagraf       = array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10);
            $fontparagraf1 = array('alignment'=>'both');
            $fontmiring   = array('name' => 'Book Antiqua (Body)', 'size' => 10, 'spaceAfter'=>80,'italic' => true, 'size' => 10);
                       
            $fontgambar = array('name' => 'Book Antiqua (Body)', 'size' => 9,'bold' => true);
            $fontgambar1 = array('alignment'=>'center');
//Kata Pengantar
            $section->addText('Kata Pengantar',  array('name' => 'Arial','spaceAfter'=>80, 'size' => 16,'bold' => true),array('alignment'=>'center'));

            $phpWord->addParagraphStyle('pStyler', array('alignment'=>'both'));
   $textrun = $section->addTextRun('pStyler');
   $textrun->addText(htmlspecialchars("     Pemantauan merupakan salah satu tahapan penting dalam pengendalian pelaksanaan rencana. Kegiatan Pemantauan Pembangunan dilakukan dalam rangka mengendalikan kesesuaian pelaksanaan pembangunan dengan tahapan dan target yang telah direncanakan. Dalam melakukan pemantauan pembangunan, Direktorat Pemantauan, Evaluasi, dan Pengendalian Pembangunan Daerah (PEPPD), Bappenas telah mengembangkan aplikasi "), $fontparagraf);
   $textrun->addText(htmlspecialchars('Dashboard '), $fontmiring);         
   $textrun->addText(htmlspecialchars("Pemantauan Pembangunan Daerah yang berguna untuk mendukung kegiatan pemantauan pembangunan dan mendukung proses penilaian kegiatan Penghargaan Pembangunan Daerah. Capaian sasaran pokok pembangunan didokumentasikan secara digital pada aplikasi "), $fontparagraf);
   $textrun->addText(htmlspecialchars('Dashboard '), $fontmiring);
   $textrun->addText(htmlspecialchars("Pemantauan Pembangunan Daerah mulai dari tingkat nasional, provinsi, kabupaten, dan kota."), $fontparagraf);
   $textrun1 = $section->addTextRun('pStyler');
   $textrun1->addText(htmlspecialchars("     Terdapat beberapa fitur dalam "), $fontparagraf);
   $textrun1->addText(htmlspecialchars('Dashboard '), $fontmiring);
   $textrun1->addText(htmlspecialchars("Pemantauan Pembangunan Daerah. Salah satu diantaranya adalah untuk melihat Laporan Perkembangan Indikator Makro Pembangunan pada setiap provinsi. Laporan Perkembangan Indikator Makro Pembangunan berisi tentang pencapaian dari indikator makro beserta komposit ataupun turunan indikator tersebut di dalam bidang ekonomi, kemiskinan, pengangguran, rasio gini, dan Indeks Pembangunan Manusia. Angka pencapaian tersebut dikompilasi berdasarkan hasil publikasi dari Badan Pusat Statistik mengenai indikator makro terkait."), $fontparagraf);
   $textrun2 = $section->addTextRun('pStyler');
   $textrun2->addText(htmlspecialchars("     Kami ucapkan terima kasih dan penghargaan kepada seluruh pihak yang telah membantu dalam penyusunan laporan ini. Masukan, saran dan kritik yang membangun kami harapkan untuk perbaikan dan penyempurnaan di masa yang akan datang."), $fontparagraf);
   $section->addTextBreak(2);
   $phpWord->addParagraphStyle('pStyler2', array('align' => 'right'));
   $textrun3 = $section->addTextRun('pStyler2');
   $textrun3->addText(htmlspecialchars("Jakarta,      Desember 2021"), $fontparagraf);
   $textrun4 = $section->addTextRun('pStyler2');
   $textrun4->addText(htmlspecialchars("Direktur Pemantauan, Evaluasi dan"), $fontparagraf);
   $textrun5 = $section->addTextRun('pStyler2');
   $textrun5->addText(htmlspecialchars("Pengendalian Pembangunan Dareah"), $fontparagraf);
   $section->addTextBreak(2);
   $textrun6 = $section->addTextRun('pStyler2');
   $textrun6->addText(htmlspecialchars("Agustin Arry Yanna"), $fontparagraf);
   
//          daftar isi
           $section = $phpWord->addSection();
           $section->addText('DAFTAR ISI',  array('name' => 'Century Gothic (Headings)','spaceAfter'=>80, 'size' => 20,'bold' => true),array('alignment'=>'center'));
           $section->addText( ' KATA PENGANTAR...........................................................................................................................................2',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ' DAFTAR ISI...........................................................................................................................................................3',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ' DAFTAR GAMBAR.............................................................................................................................................4',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Pertumbuhan Ekonomi.....................................................................................................................................5',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Perkembangan PDRB per kapita ADHB........................................................................................................6',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Perkembangan PDRB per kapita ADHK tahun dasar 2010........................................................................7',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Perkembangan Jumlah Penganggur...............................................................................................................8',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Tingkat Pengangguran Terbuka......................................................................................................................9',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Indeks Pembangunan Manusia.....................................................................................................................10',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Rasio Gini..........................................................................................................................................................11',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both') );
           $section->addText( ''.$daftarisi++.'. Angka Harapan Hidup...................................................................................................................................12',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Rata-rata Lama Sekolah..................................................................................................................................13',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Harapan Lama Sekolah................................................................................................................................14',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Pengeluaran per Kapita................................................................................................................................15',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Tingkat Kemiskinan......................................................................................................................................16',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Indeks Kedalaman Kemiskinan (P1) .........................................................................................................17',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Indeks Keparahan Kemiskinan (P2) ..........................................................................................................18',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
           $section->addText( ''.$daftarisi++.'. Jumlah Penduduk Miskin............................................................................................................................19',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            //daftar gambar 
            $section = $phpWord->addSection();
            $section->addText('DAFTAR GAMBAR',  array('name' => 'Century Gothic (Headings)','spaceAfter'=>80, 'size' => 20,'bold' => true),array('alignment'=>'center'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Pertumbuhan Ekonomi.........................................................................................4',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Pertumbuhan Ekonomi Antar Provinsi................................................................4',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan PDRB per Kapita ADHB........................................................................................5',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Perkembangan PDRB per kapita ADHB Antar provinsi...................................5',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan PDRB per kapita ADHK Tahun Dasar 2010.......................................................6',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan PDRB per kapita ADHK Tahun Dasar 2010 Antar provinsi..............................6',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Jumlah Penganggur................................................................................................7',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Jumlah Penganggur Antar Provinsi......................................................................7',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Tingkat Pengangguran Terbuka..........................................................................8',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Tingkat Pengangguran Terbuka Antar Provinsi...............................................8',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Indeks Pembangunan Indonesia........................................................................9',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Indeks Pembangunan Indonesia Antar Provinsi...............................................9',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Rasio Gini.............................................................................................................10',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Rasio Gini Antar Provinsi...................................................................................10',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Angka Harapan Hidup......................................................................................11',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Angka Harapan Hidup Antar Provinsi............................................................11',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Rata-rata Lama Sekolah.....................................................................................12',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Rata-rata Lama Sekolah Antar Provinsi...........................................................12',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Harapan Lama Sekolah.....................................................................................13',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Harapan Lama Sekolah Antar Provinsi............................................................13',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Pengeluaran per Kapita.....................................................................................14',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Pengeluaran per Kapita Antar Provinsi............................................................14',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Tingkat Kemiskinan...........................................................................................15',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Tingkat Kemiskinan Antar Provinsi..................................................................15',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Indeks Kedalaman Kemiskinan (P1) ..............................................................16',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Indeks Kedalaman Kemiskinan (P1) Antar Provinsi......................................16',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Indeks Keparahan Kemiskinan (P2) ...........…................................................17',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Indeks Keparahan Kemiskinan (P2) Antar Provinsi.......................................17',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perkembangan Jumlah Penduduk Miskin.................................................................................18',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));
            $section->addText( 'Gambar '.$daftargamabar++.'. Perbandingan Jumlah Penduduk Miskin Antar Provinsi........................................................18',  array('name' => 'Book Antiqua (Body)', 'size' => 10),array('alignment'=>'both'));

            //halaman Baru
            $section = $phpWord->addSection();
            $section->addText( '1. Pertumbuhan Ekonomi',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            
                $section->addImage($hal1,
                    array(
                       'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                ));
                
                $paragraf_1_2  ="Pertumbuhan ekonomi ". $xname ." pada tahun ".$tahun1[5]." ".$meningkatmenurun." dibandingkan dengan tahun ".$tahun1[4].". Pada tahun ".$tahun1[5]." pertumbuhan ekonomi ". $xname ." adalah sebesar ". end($nilaimax_pro) ."%, sedangkan pada tahun ".$tahun1[4]." pertumbuhannya tercatat sebesar ".$nilaimax_pro[4]."%. ";
                $paragraf_1_3  ="Capaian pertumbuhan ekonomi ". $xname ." pada tahun ".$tahun1[5]." ".$dibawahdiatas." "
                    . "Nasional. Pertumbuhan ekonomi Nasional pada tahun ".$tahun1[5]." adalah sebesar ". end($nilaimax) ."%. ";
            
            // In section
            $textbox = $section->addTextBox(
                array(
//                    'positioning' => 'absolute',
                    'marginLeft' => 1,
                                     // 'marginTop' => 200,
                    'align'       => 'right',
                    'width'       => 380,
                    'height'      => 78,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi1,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));

            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 100,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                    //'marginTop' => 20,
                )
            );
            $textbox->addText($paragraf_1_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 95,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_1_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            //$section->addText($paragraf_1_2,array('name' => 'Arial', 'size' => 10),array('alignment'=>'both'));
            //$section->addText($paragraf_1_3,array('name' => 'Arial', 'size' => 10),array('alignment'=>'both'));
            
            
           // $section->addTextBreak(1);
            $categories = $thn_ex;
            $series1 = $nilaiData1;
            $series2 = $nilaiData1_pro;
            $showGridLines = true;
            $showAxisLabels = true;
            
            foreach ($chartTypes as $chartType) {
                //$section->addText('Gambar '.$gambar++.' Perkembangan Pertumbuhan Ekonomi (%)',array('name' => 'Century Gothic (Headings)', 'size' => 9),array('alignment'=>'center'));
                $chart = $section->addChart($chartType, $categories, $series1,$style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories, $series2, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan Pertumbuhan Ekonomi (%)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }            
            $categories_1_2 = $label_data_ppe;
            $series_1_2     = $nilai_data_ppe_per;
            //$warna_1_2      = $wrnPE;
            $chart = $section->addChart('column', $categories_1_2, $series_1_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Perkembangan Pertumbuhan Ekonomi Tahun ".$tahun_pe_max." (%)", $fontgambar, $fontgambar1);
            
            //$chart->getStyle()->setColors($warna_1_2);
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);
            

// Halaman Baru
            $section = $phpWord->addSection();
            $section->addText( '2. Perkembangan PDRB per Kapita ADHB',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal2,
                    array(
                       'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            //$section->addText('2. Perkembangan PDRB per Kapita ADHB',$fontStyleName);
            $paragraf_2_2  ="PDRB per kapita ADHB ". $xname ." pada tahun ".$tahun_adhb2[5]." ".$meningkatmenurunADHB." dibandingkan dengan tahun ".$tahun_adhb2[4].". "
                    . "Pada tahun ".$tahun_adhb2[5]." PDRB per kapita ADHB ". $xname ." adalah sebesar Rp".number_format(end($nilaiData_max_p),0)." ".$ket_adhb2[5].""
                    . "sedangkan pada tahun ".$tahun_adhb2[4]." PDRB per kapita ADHB tercatat sebesar Rp".number_format($nilaiData_max_p[4],0).". ";
            $paragraf_2_3  ="Capaian PDRB per kapita ". $xname ." pada tahun ".$tahun_adhb2[5]." berada ".$dibawahdiatasADHB." capaian Nasional. "
                    . "PDRB per kapita ADHB Nasional pada tahun ".$tahun_adhb2[5]." adalah sebesar Rp".number_format($nilaiData_max[5]).". ";
            //$section->addText($deskripsi2,  array('name' => 'Arial', 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 380,
                    'height'      => 98,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 100,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                    //'marginTop' => 20,
                )
            );
            $textbox->addText($paragraf_2_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                )
            );
            $textbox->addText($paragraf_2_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $categories_2_2 = $tahun_adhb;
            $series_2_n     = $datay_adhb1;
            $series_2_2     = $datay_adhb2;
            foreach ($chartTypes2 as $chartType) {
                $chart = $section->addChart($chartType, $categories_2_2, $series_2_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_2_2, $series_2_2, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan PDRB per kapita ADHB (Juta Rupiah)', $fontgambar, $fontgambar1);
            }  

            
            $categories_2_3 = $label_data_adhb;
            $series_2_3     = $nilai_data_adhb_per;
            $chart_2_3      = $section->addChart('column', $categories_2_3, $series_2_3, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan PDRB per kapita ADHB Tahun ".$periode_adhb_tahun." (Juta Rupiah)", $fontgambar, $fontgambar1);
            $section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);
            
// Halaman Baru
            $section = $phpWord->addSection();
            $section->addText( '3. Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal7,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_3_2  ="PDRB per kapita ADHK tahun dasar 2010 ". $xname ." pada tahun ".$tahun_adhk[5]." ".$meningkatmenurunADHK." dibandingkan dengan tahun ".$tahun_adhk[4].". Pada tahun ".$tahun_adhk[5]." PDRB per kapita ADHK tahun dasar 2010 ". $xname ." adalah sebesar Rp". number_format(end($adhk_p)) ." ".$ket_adhk2[5]."sedangkan pada tahun ".$tahun_adhk[4]." PDRB per kapita ADHK tahun dasar 2010 tercatat sebesar Rp".number_format($adhk_p[4]).". ";
            $paragraf_3_3  ="Capaian PDRB per kapita ADHK Tahun Dasar 2010 ". $xname ." pada tahun ".$tahun_adhk[5]." berada ".$dibawahdiatasADHK." capaian nasional. PDRB per kapita ADHK tahun dasar 2010 nasional pada tahun ".$tahun_adhk[5]." adalah sebesar Rp".number_format(end($adhk_nasional)) .".";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 380,
                    'height'      => 100,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                    
                )
            );
            $textbox->addText($deskripsi3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 230,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                    //'marginTop' => 20,
                )
            );
            $textbox->addText($paragraf_3_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 220,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_3_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            //$section->addTextBreak(1);
            $categories_3_1 = $tahun_adhk1;
            $series_3_n     = $datay_adhk1;
            $series_3_1     = $datay_adhk2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_3_1, $series_3_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_3_1, $series_3_1, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010 (Juta Rupiah)', $fontgambar, $fontgambar1);
            }  
            
            $categories_3_2 = $label_data_adhk;
            $series_3_2     = $nilai_data_adhk_per;
            $chart_2_3      = $section->addChart('column', $categories_3_2, $series_3_2, $style_ADHK);
            $section->addText("Gambar ".$gambar++.". Perbandingan PDRB per Kapita ADHK (2010) tahun ".$periode_adhk_tahun." (Juta Rupiah)", $fontgambar, $fontgambar1);            
//$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik, diolah', $fontgambar);
            
// Halaman Baru
            $section = $phpWord->addSection();
            $section->addText( '4. Perkembangan Jumlah Penganggur',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal8,
                    array(
                       'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_4_2  ="Jumlah penganggur di ". $xname ." pada ". $tahunJP[5] ." sebanyak ". number_format($nilai_capaian2[5],0) . " orang. Sedangkan jumlah penganggur pada ". $tahunJP[3] ." sebanyak ". number_format($nilai_capaian2[3],0) . " orang. Selama periode ". $tahunJP[3] ." sampai ". $tahunJP[5] . " jumlah penganggur di ". $xname." ".$berkurangmeningkat." ".number_format($rt_jpp)." orang atau sebesar ".$rt_jp33 ."%."
                    . " Jumlah pengangur nasional pada ".$tahunJP[5]." sebesar ". number_format($nilai_capaian[5],0) . " orang.";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi4,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 323,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                )
            );
            $textbox->addText($paragraf_4_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $section->addTextBreak(1);
            $categories_4_1 = $tahun_jp;
            $series_4_1     = $datay_jp2;
            
            $chart_4_1      = $section->addChart('column', $categories_4_1, $series_4_1, $style_2, $xname);
            $section->addText("Gambar ".$gambar++.". Perkembangan Jumlah Penganggur (Ribu Orang)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $categories_4_2 = $label_data_jp;
            $series_4_2     = $nilai_data_jp_per;
            $chart_4_2      = $section->addChart('column', $categories_4_2, $series_4_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Jumlah Penganggur ".$periode_jp_tahun." (Ribu Orang)", $fontgambar, $fontgambar1);
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);            
        
// Halaman Baru
            $section = $phpWord->addSection();
            $section->addText( '5. Tingkat Pengangguran Terbuka',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal9,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_5_2  ="Tingkat pengangguran terbuka ".$xname." pada ".$tahunTPT2[5]." ".$menurunmeningkatTPT." dibandingkan dengan ".$tahunTPT2[3].". Pada ".$tahunTPT2[5]." Tingkat pengangguran terbuka ". $xname ." adalah sebesar ". number_format(end($nilaiData_tpt2),2) ."% sedangkan pada ".$tahunTPT2[3]." tingkat pengangguran terbuka tercatat sebesar ".number_format($nilaiData_tpt2[3],2)."%. ";
            $paragraf_5_3  ="Capaian tingkat pengangguran terbuka ".$xname." pada ".$tahunTPT2[5]." berada ".$dibawahdiatasTPT." capaian Nasional. Tingkat pengangguran terbuka Nasional pada ".$tahunTPT2[5]." adalah sebesar ".number_format(end($nilaiData_tpt),2) ."%. ";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 310,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi5,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 100,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                    //'marginTop' => 20,
                )
            );
            $textbox->addText($paragraf_5_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 95,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_3_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $categories_5_1 = $tahun_tpt;
            $series_5_n     = $datay_tpt;
            $series_5_1     = $datay_tpt2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_5_1, $series_5_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_5_1, $series_5_1, $xname );
                }
               $section->addText('Gambar '.$gambar++.'. Tingkat Pengangguran Terbuka (%)', $fontgambar, $fontgambar1);
                // $section->addTextBreak();
            }
            $categories_5_2 = $label_data_tpt;
            $series_5_2     = $nilai_data_tpt_per;
            $chart_5_2      = $section->addChart('column', $categories_5_2, $series_5_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Tingkat Pengangguran Terbuka ".$periode_tpt_tahun." (%)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);
            
             // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('6. Indeks Pembangunan Manusia',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal10,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_6_2  ="Indeks Pembangunan Manusia ". $xname ." pada tahun ".$tahun_ipm2[5]." ".$menurunmeningkatIPM." dibandingkan dengan tahun ".$tahun_ipm2[4].". Pada tahun ".$tahun_ipm2[5]." IPM ". $xname ." adalah sebesar ". number_format(end($nilaiData_ipm2),2) ." sedangkan pada tahun ".$tahun_ipm2[4]." IPM tercatat sebesar ".number_format($nilaiData_ipm2[4],2).".";
            $paragraf_6_3  ="Capaian IPM ".$xname." pada tahun ".$tahun_ipm2[5]." berada ".$dibawahdiatasIPM." capaian Nasional. Indeks Pembangunan Manusia Nasional pada tahun ".$tahun_ipm2[5]." adalah sebesar ".number_format(end($nilaiData_ipm),2).".";
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi6,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 125,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                )
            );
            $textbox->addText($paragraf_6_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 95,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_6_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $categories_6_1 = $tahun_ipm;
            $series_6_n     = $datay_ipm;
            $series_6_1     = $datay_ipm2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_6_1, $series_6_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_6_1, $series_6_1, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan Indeks Pembangunan Manusia', $fontgambar, $fontgambar1);
            }
            $categories_6_2 = $label_data_ipm;
            $series_6_2     = $nilai_data_ipm_per;
            $chart_6_2      = $section->addChart('column', $categories_6_2, $series_6_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Indeks Pembangunan Manusia Tahun ".$periode_ipm_tahun." ", $fontgambar, $fontgambar1);
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);            
             
// Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('7. Rasio Gini',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal11,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_7_2  ="Rasio gini ". $xname ." pada ".$tahun_grR[5]." ".$menurunmeningkatGR." dibandingkan dengan ".$tahun_grR[3].". Pada ".$tahun_grR[5]." rasio gini ". $xname ." adalah sebesar ". number_format($nilaiData_gr2[5],3) ." sedangkan pada ".$tahun_grR[3]." rasio gini tercatat sebesar ".number_format($nilaiData_gr2[3],3).". ";
            $paragraf_7_3  ="Capaian rasio gini ". $xname ." pada ".$tahun_grR[5]." berada ".$dibawahdiatasGR." capaian nasional. Rasio gini nasional pada ".$tahun_grR[5]." adalah sebesar ".number_format($nilaiData_gr[5],3).".";
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi7,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 125,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                )
            );
            $textbox->addText($paragraf_7_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 95,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_7_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $categories_7_1 = $tahun_gr;
            $series_7_n     = $datay_gr;
            $series_7_1     = $datay_gr2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_7_1, $series_7_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_7_1, $series_7_1, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan Rasio Gini', $fontgambar, $fontgambar1);
            }
            $categories_7_2 = $label_data_gr;
            $series_7_2     = $nilai_data_gr_per;
            $chart_7_2      = $section->addChart('column', $categories_7_2, $series_7_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Rasio Gini ".$periode_gr_tahun." ", $fontgambar, $fontgambar1);
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);
            
// Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('8. Angka Harapan Hidup',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal12,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_8_1  ="Angka harapan hidup adalah perkiraan rata-rata tambahan umur seseorang yang diharapkan dapat terus hidup. Angka Harapan Hidup juga dapat didefinisikan sebagai rata-rata jumlah tahun yang dijalani oleh seseorang setelah orang tersebut mencapai ulang tahun yang ke-x. Ukuran yang umum digunakan adalah angka harapan hidup saat lahir yang mencerminkan kondisi kesehatan pada saat itu. Sehingga pada umumnya ketika membicarakan AHH, yang dimaksud adalah rata-rata jumlah tahun yang akan dijalani oleh seseorang sejak orang tersebut lahir.";
            $paragraf_8_2  ="Angka harapan hidup ". $xname ." pada tahun ".$tahun_ahh[5]." ".$menurunmeningkatAHH." dibandingkan dengan tahun ".$tahun_ahh[4].". Pada tahun ".$tahun_ahh[5]." angka harapan hidup nasional ". $xname ." adalah sebesar ". number_format(end($nilaiData_ahh2),2) ." tahun sedangkan pada tahun ".$tahun_ahh[4]." angka harapan hidup tercatat sebesar ".number_format($nilaiData_ahh2[4],2)." tahun.";
            $paragraf_8_3  ="Capaian angka harapan hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada ".$dibawahdiatasAHH." capaian Nasional. Angka harapan hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun.";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($paragraf_8_1,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 125,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                )
            );
            $textbox->addText($paragraf_8_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 95,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_8_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $categories_8_1 = $tahun_ahh;
            $series_8_n     = $datay_ahh;
            $series_8_1     = $datay_ahh2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_8_1, $series_8_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_8_1, $series_8_1, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan Angka Harapan Hidup (Tahun)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_8_2 = $label_ahh;
            $series_8_2     = $nilai_ahh_per;
            $chart_8_2      = $section->addChart('column', $categories_8_2, $series_8_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Angka Harapan Hidup Tahun ".$periode_ahh_tahun." (Tahun)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);
            
// Halaman Baru RLS
            $section = $phpWord->addSection();
            $section->addText('9. Rata-rata Lama Sekolah',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal13,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_9_1  ="Rata-rata lama sekolah merupakan salah satu indikator pembentuk Indeks Pembangunan Manusia dari dimensi pendidikan. Rata-rata lama sekolah menunjukkan jumlah tahun belajar penduduk usia 25 tahun ke atas yang telah diselesaikan dalam pendidikan formal (tidak termasuk tahun yang mengulang). Indikator ini menunjukkan tingkat pendidikan formal dari penduduk di suatu wilayah. Semakin tinggi nilai rata-rata lama sekolah, semakin baik pula tingkat pendidikan di suatu wilayah.";
            $paragraf_9_2  ="Rata-rata lama sekolah ". $xname ." pada tahun ".$tahun_rls[5]." ".$menurunmeningkatRLS." dibandingkan dengan tahun ".$tahun_rls[4].". Pada tahun ".$tahun_rls[5]." Rata-rata lama sekolah ". $xname ." ". number_format(end($nilaiData_rls2),2) ." tahun, sedangkan pada tahun ".$tahun_rls[4]." rata-rata lama sekolah tercatat sebesar ".number_format($nilaiData_rls2[4],2)." tahun.";
            $paragraf_9_3  ="Capaian rata-rata lama sekolah ". $xname ." pada tahun ".$tahun_rls[5]." berada ".$dibawahdiatasRLS." capaian Nasional. Rata-rata lama sekolah nasional pada tahun ".$tahun_rls[5]." sebesar ".number_format($nilaiData_rls[5],2) ." tahun.";
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($paragraf_9_1,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 125,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                )
            );
            $textbox->addText($paragraf_9_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 95,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_9_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $categories_9_1 = $tahun_rls;
            $series_9_n     = $datay_rls;
            $series_9_1     = $datay_rls2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_9_1, $series_9_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_9_1, $series_9_1, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan Rata-rata Lama Sekolah (Tahun)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_9_2 = $label_rls;
            $series_9_2     = $nilai_rls_per;
            $chart_9_2      = $section->addChart('column', $categories_9_2, $series_9_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Rata-rata Lama Sekolah Tahun ".$periode_rls_tahun." (Tahun)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);
            
// Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('10. Harapan Lama Sekolah',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal14,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_10_2  ="Harapan lama sekolah ". $xname ." pada tahun ".$tahun_hls[5]." ".$menurunmeningkatHLS." dibandingkan dengan tahun ".$tahun_hls[4].". Pada tahun ".$tahun_hls[5]." Harapan Lama Sekolah ". $xname ." adalah sebesar ". number_format(end($nilaiData_hls2),2) ." tahun, sedangkan pada tahun ".$tahun_hls[4]." rata-rata lama sekolah tercatat sebesar ".number_format($nilaiData_hls2[4],2)." tahun.";
            $paragraf_10_3  ="Capaian harapan lama sekolah ". $xname ." pada tahun ".$tahun_hls[5]." berada ".$dibawahdiatasHLS." capaian Nasional. Harapan lama sekolah Nasional pada tahun ".$tahun_hls[5]." adalah sebesar ".number_format(end($nilaiData_hls),2) ." tahun.";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi10,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 125,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                )
            );
            $textbox->addText($paragraf_10_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 95,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_10_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $categories_10_1 = $tahun_hls;
            $series_10_n     = $datay_hls;
            $series_10_1     = $datay_hls2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_10_1, $series_10_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_10_1, $series_10_1, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan Harapan Lama Sekolah (Tahun)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_10_2 = $label_data_hls;
            $series_10_2     = $nilai_data_hls_per;
            $chart_10_2      = $section->addChart('column', $categories_10_2, $series_10_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Harapan Lama Sekolah tahun ".$periode_hls_tahun." (Tahun)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);
            
// Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('11. Pengeluaran per Kapita',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal15,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_11_2  ="Pengeluaran per kapita ". $xname ." pada tahun ".$tahun_ppk[5]." ".$menurunmeningkatPPK." dibandingkan dengan tahun ".$tahun_ppk[4].". Pada tahun ".$tahun_ppk[5]." pengeluaran per kapita ". $xname ." adalah sebesar Rp".number_format(end($nilaiData_ppk2))." sedangkan pada tahun ".$tahun_ppk[4]." pengeluaran per kapita tercatat sebesar Rp".number_format($nilaiData_ppk2[4]).". ";
            $paragraf_11_3  ="Capaian pengeluaran per kapita ". $xname ." pada tahun ".$tahun_ppk[5]." berada ".$dibawahdiatasPPK." capaian nasional. Pengeluaran per kapita Nasional pada tahun ".$tahun_ppk[5]." adalah sebesar Rp".number_format(end($nilaiData_ppk))." ";        
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi11,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 125,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                )
            );
            $textbox->addText($paragraf_11_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 95,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_11_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $categories_11_1 = $tahun_ppk;
            $series_11_n     = $datay_ppk;
            $series_11_1     = $datay_ppk2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_11_1, $series_11_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_11_1, $series_11_1, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan Pengeluaran per Kapita (Juta Rupiah)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_11_2 = $label_data_ppk;
            $series_11_2     = $nilai_data_ppk_per;
            $chart_11_2      = $section->addChart('column', $categories_11_2, $series_11_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Pengeluaran per Kapita Tahun ".$periode_ppk_tahun." (Juta Rupiah)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);
            
            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('12. Tingkat Kemiskinan',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal16,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_12_2  ="Tingkat kemiskinan ". $xname ." pada ".$tahun_tkk[5]." ".$menurunmeningkatTK." dibandingkan dengan ".$tahun_tkk[3].". Pada ".$tahun_tkk[5]." Angka tingkat kemiskinan ". $xname ." adalah sebesar ". number_format(end($nilaiData_tk2),2) ."%, sedangkan pada ".$tahun_tkk[3]." Angka tingkat Kemiskinan tercatat sebesar ".number_format($nilaiData_tk2[3],2)."%. ";
            $paragraf_12_3  ="Capaian angka tingkat Kemiskinan ". $xname ." pada ".$tahun_tkk[5]." berada ".$dibawahdiatasTK." capaian Nasional. Angka tingkat kemiskinan Nasional pada ".$tahun_tkk[5]." adalah sebesar ".number_format(end($nilaiData_tk),2) ."%.";
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 125,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi12,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                )
            );
            $textbox->addText($paragraf_12_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 85,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_12_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $categories_12_1 = $tahun_tk;
            $series_12_n     = $datay_tk;
            $series_12_1     = $datay_tk2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_12_1, $series_12_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_12_1, $series_12_1, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan Tingkat Kemiskinan (%)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_12_2 = $label_data_tk;
            $series_12_2     = $nilai_data_tk_per;
            $chart_12_2      = $section->addChart('column', $categories_12_2, $series_12_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Tingkat Kemiskinan ".$periode_tk_tahun." (%)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar); 
             
// Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('13. Indeks Kedalaman Kemiskinan (P1)',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal17,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $tulisannormal       = array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10);
            $tulisanfontmiring   = array('name' => 'Book Antiqua (Body)', 'size' => 10, 'spaceAfter'=>80,'italic' => true);
            
            
            $paragraf_13_1_2 ="), merupakan ukuran rata-rata kesenjangan pengeluaran masing-masing penduduk miskin terhadap garis kemiskinan. Semakin tinggi nilai indeks kedalaman kemiskinan, semakin jauh rata-rata pengeluaran penduduk miskin dari garis kemiskinan.";
            $paragraf_13_2 ="Indeks kedalaman kemiskinan ". $xname ." pada ".$tahunIDK1[5]." ".$menurunmeningkatIKK." dibandingkan dengan ".$tahunIDK1[3].". Pada ".$tahunIDK1[5]." indeks kedalaman kemiskinan ". $xname ." adalah sebesar ". number_format(end($nilaiData_idk2),2) .", sedangkan pada ".$tahunIDK1[3]." indeks kedalaman kemiskinan tercatat sebesar ".number_format($nilaiData_idk2[3],2).". ";
            $paragraf_13_3 ="Capaian indeks kedalaman kemiskinan ". $xname ." pada ".$tahunIDK1[5]." berada ".$dibawahdiatasIKK." capaian Nasional. Indeks kedalaman kemiskinan Nasional pada ".$tahunIDK1[5]." adalah sebesar ".number_format(end($nilaiData_idk),2) .".";
           // $textrun = $section->addTextBox(
//                array(
//                    'align'       => 'right',
//                    'width'       => 360,
//                    'height'      => 90,
//                    'borderSize'  => 1,
//                    'borderColor' => '#F2A132',
//                )
//            );

            //            Indeks Kedalaman Kemiskinan (Poverty Gap Index-P1), merupakan ukuran rata-rata kesenjangan pengeluaran masing-masing penduduk miskin terhadap garis kemiskinan. Semakin tinggi nilai indeks kedalaman kemiskinan, semakin jauh rata-rata pengeluaran penduduk miskin dari garis kemiskinan.
            //$phpWord->addParagraphStyle('pStyler', array('alignment'=>'both'));
            //$textbox = $section->addTextBox('pStyler');           
            
            //$textbox->addText(htmlspecialchars('Poverty Gap Index-P1'), $tulisanfontmiring);
          //  $textbox->addText(htmlspecialchars('), merupakan ukuran rata-rata kesenjangan pengeluaran masing-masing penduduk miskin terhadap garis kemiskinan. Semakin tinggi nilai indeks kedalaman kemiskinan, semakin jauh rata-rata pengeluaran penduduk miskin dari garis kemiskinan.'), $fontparagraf);



   $phpWord->addParagraphStyle('formatbox', array(
       'alignment'=>'both',
//       'align'       => 'right',
                    'width'       => 160,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
//       'indentation' => array('left' => 2960, 'right' => 60, 'hanging' => 360),
       'indentation' => array('left' => 2160),
       ));

   $textrun = $section->addTextRun('formatbox');
   $textrun->addText(htmlspecialchars("Indeks Kedalaman Kemiskinan ("), $fontparagraf);
   $textrun->addText(htmlspecialchars('Poverty Gap Index-P1'), $fontmiring);         
   $textrun->addText(htmlspecialchars("), merupakan ukuran rata-rata kesenjangan pengeluaran masing-masing penduduk miskin terhadap garis kemiskinan. Semakin tinggi nilai indeks kedalaman kemiskinan, semakin jauh rata-rata pengeluaran penduduk miskin dari garis kemiskinan."), $fontparagraf);
   
 
 
//   
            $textbox->addText($textbox1);
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 125,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                )
            );
            $textbox->addText($paragraf_13_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 95,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_13_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $categories_13_1 = $tahun_idk;
            $series_13_n     = $datay_idk;
            $series_13_1     = $datay_idk2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_13_1, $series_13_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_13_1, $series_13_1, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan Indeks Kedalaman Kemiskinan', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_13_2 = $label_data_ikk;
            $series_13_2     = $nilai_data_ikk_per;
            $chart_13_2      = $section->addChart('column', $categories_13_2, $series_13_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Indeks Kedalaman Kemiskinan ".$periode_ikk_tahun."", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('14. Indeks Keparahan Kemiskinan (P2)',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal18,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_15_2 ="Indeks keparahan kemiskinan ". $xname ." pada ".$tahunIKK1[5]." ".$menurunmeningkatIKKK." dibandingkan dengan ".$tahunIKK1[3].". Pada ".$tahunIKK1[5]." indeks keparahan kemiskinan ". $xname ." adalah sebesar ". number_format(end($nilaiData_ikk2),2) .", sedangkan pada ".$tahunIKK1[3]." indeks keparahan kemiskinan tercatat sebesar ".number_format($nilaiData_ikk2[3],2).". ";
            $paragraf_15_3 ="Capaian indeks keparahan kemiskinan ". $xname ." pada ".$tahunIKK1[5]." berada ".$dibawahdiatasIKKK." capaian Nasional. Indeks keparahan kemiskinan Nasional pada ".$tahunIKK1[5]." adalah sebesar ".number_format(end($nilaiData_ikk),2) .".";
            
//            $textbox = $section->addTextBox(
//                array(
//                    'align'       => 'right',
//                    'width'       => 310,
//                    'height'      => 90,
//                    'borderSize'  => 1,
//                    'borderColor' => '#F2A132',
//                )
//            );
//            $textbox->addText($deskripsi15,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
//            Indeks Keparahan Kemiskinan (Proverty Severity Index-P2) memberikan gambaran mengenai penyebaran pengeluaran diantara penduduk miskin. Semakin tinggi nilai indeks, semakin tinggi ketimpangan pengeluaran diantara penduduk miskin.

            $phpWord->addParagraphStyle('formatbox1', array(
       'alignment'=>'both',
//       'align'       => 'right',
                    'width'       => 160,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
       'indentation' => array('left' => 2160),
       ));

   $textrun = $section->addTextRun('formatbox1');
   $textrun->addText(htmlspecialchars("Indeks Keparahan Kemiskinan ("), $fontparagraf);
   $textrun->addText(htmlspecialchars('Proverty Severity Index-P2'), $fontmiring);         
   $textrun->addText(htmlspecialchars(") memberikan gambaran mengenai penyebaran pengeluaran diantara penduduk miskin. Semakin tinggi nilai indeks, semakin tinggi ketimpangan pengeluaran diantara penduduk miskin."), $fontparagraf);  
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 223,
                    'height'      => 125,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                    'positioning' => 'relative',
                )
            );
            $textbox->addText($paragraf_15_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 95,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                    //'marginTop' => -2,
                )
            );
            $textbox->addText($paragraf_15_3,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $categories_15_1 = $tahun_ikk;
            $series_15_n     = $datay_ikk;
            $series_15_1     = $datay_ikk2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_15_1, $series_15_n,$style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_15_1, $series_15_1, $xname );
                }
                $section->addText('Gambar '.$gambar++.'. Perkembangan Indeks Keparahan Kemiskinan', $fontgambar, $fontgambar1);
                //                $section->addTextBreak();
            }
            $categories_15_2 = $label_data_ikkk;
            $series_15_2     = $nilai_data_ikkk_per;
            $chart_15_2      = $section->addChart('column', $categories_15_2, $series_15_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Indeks Keparahan Kemiskinan ".$periode_ikkk_tahun."", $fontgambar, $fontgambar1);    
//            $section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);
            
            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('15. Jumlah Penduduk Miskin',array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,) );
            $section->addImage($hal19,
                    array(
                        'positioning' => 'absolute',
                        'marginLeft' => 50,
                        'marginTop' => 15,
                        'width' => 100,
                        'height' => 78,
                        'wrappingStyle' => 'behind',
                        'posHorizontal' => 'absolute',
                        'posHorizontalRel' => 'page',
                        'posVertical' => 'absolute',
                        'posVerticalRel' => 'line',
                        ));
            $paragraf_14_2    ="Jumlah penduduk miskin ". $xname ." pada ".$tahun_jpk[5]." sebanyak ".number_format($nilaiData_jpk22[5],0) ." orang sedangkan jumlah penduduk miskin pada ".$tahun_jpk[3]." sebanyak ".number_format($nilaiData_jpk22[3],0)." orang. "
                    . "Selama periode ".$tahun_jpk[3]." - ".$tahun_jpk[5]." jumlah penduduk miskin di ". $xname ." ".$berkurangbertambah." sebanyak ".number_format($rt_jpkk,0)." orang atau sebesar ".$rt_jpk33."%. "
                    . "Jumlah Penduduk Miskin nasional pada ".$tahun_jpk[5]." sebesar ". number_format($nilaiData_jpk[5],0) ." orang.";
            
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 55,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi14,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 323,
                    'height'      => 100,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B ',
                    //'positioning' => 'relative',
                )
            );
            $textbox->addText($paragraf_14_2,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            
            
            $categories_14_1 = $tahun_jpk21;
            $series_14_1     = $datay_jpk2;
            $chart = $section->addChart('column', $categories_14_1, $series_14_1,$style_2, $xname);
            $section->addText('Gambar '.$gambar++.'. Perkembangan Jumlah Penduduk Miskin (Orang)', $fontgambar, $fontgambar1);
            //$section->addTextBreak();
                
            $categories_14_2 = $label_data_jpk;
            $series_14_2     = $nilai_data_jpk_per;
            $chart_14_2      = $section->addChart('column', $categories_14_2, $series_14_2, $style_2);
            $section->addText("Gambar ".$gambar++.". Perbandingan Jumlah Penduduk Miskin ".$periode_jpk_tahun." (Orang)", $fontgambar, $fontgambar1);
            //            $section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);
            
            $filename = $xname.'.docx';		
    	
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); //mime type
        header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
                    
                }
                
        elseif($provinsi != '' & $kabupaten !='' ){
            
            $sql_pro1 = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`id`='".$pro."' ";
            $list_data1 = $this->db->query($sql_pro1);
            foreach ($list_data1->result() as $Lis_pro1){
                $id_pro = $Lis_pro1->id;
                $xname  = $Lis_pro1->nama_provinsi;
            }
            $sql_pro = "SELECT K.`id`, K.`nama_kabupaten`, K.`prov_id`, K.`ppd`, K.`urutan`, P.id, P.nama_provinsi 
            FROM `kabupaten` K
            LEFT JOIN provinsi P ON P.id = K.`prov_id`
            WHERE K.id = '".$kab."' ";$list_data = $this->db->query($sql_pro);
            foreach ($list_data->result() as $Lis_pro){
                $xnameKab = $Lis_pro->nama_kabupaten;
                $query = "1000";
                $id_kab = $Lis_pro->id;
                $judul = $Lis_pro->nama_kabupaten;
                $judul1 = $xname;
            }

            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $src= base_url("assets/images/laporan/cover_master_v2.jpg");
            $logo =base_url("assets/images/logopropinsi/".$pro.".png");
            $section = $phpWord->addSection(array(
                'headerHeight' => 50,
                'footerHeight' => 3000
            ));
            $header = $section->addHeader();
            $header->addWatermark($src,
                    array(
                        'headerHeight' => 300, 'marginTop' => -0, 'marginLeft' => -70,
                        //'footerHeight' => -50,
                        'width' => 590,
                        //'height'=> 30,
                        'posHorizontal' => 'absolute', 'posVertical' => 'absolute',
                        )
                    );

            // Adding Text element with font customized using explicitly created font style object...
            $fontStyle = new \PhpOffice\PhpWord\Style\Font();            
            $fontStyle->setBold(true);
            $fontStyle->setName('Product Sans');
            $fontStyle->setSize(28);
            $fontStyle->setColor('#bfbfbf');
            
            $section->addTextBreak(18);
            $section->addImage($logo,
                    array(
                        'width' => 119,
                        'height'=> 120,
                        'posHorizontal' => 'absolute',
                        'posVertical' => 'absolute',
                        ));
            $section->addTextBreak(5);
            
            $myTextElement = $section->addText($judul);
            $myTextElement->setFontStyle($fontStyle,array('alignment'=>'right'));
            
            // Halaman Baru
            $section = $phpWord->addSection();
            $header = $section->addHeader();
            $header->addWatermark(base_url("assets/images/header.png"),
                    array(
                        'headerHeight' => 300,
                        'marginTop' => -36,
                        'marginLeft' => -70,
            'footerHeight' => -50,
            'width' => 590,
            //'height'=> 30,
            'posHorizontal' => 'absolute',
             'posVertical' => 'absolute',
            )
        );

            $footer = $section->addFooter();
            $textRun = $footer->addTextRun(array('alignment' => 'center'));
            $textRun->addField('PAGE', array('format' => 'NUMBER'));
            $textRun->addText('');
            $fontStyleName = '';
            $phpWord->addFontStyle( $fontStyleName,array('name' => 'Arial Narrow', 'size' => 14, 'color' => '1B2232', 'bold' => true) );
            $style = array( 'width'          => Converter::cmToEmu(16), 'height'         => Converter::cmToEmu(6),
                'showAxisLabels' => true, 'showGridX'      => false, 'showGridY'      => false,
                'showLegend'     => true, 'valueLabelPosition'     => FALSE,
            );
            $style_1 = array(
                'width'          => Converter::cmToEmu(16),
                'height'         => Converter::cmToEmu(6),
               // '3d'             => true,
                'showAxisLabels' => true,
                //'showGridX'      => false,
                'showGridY'      => TRUE,
                 'dataLabelOptions' => array(
                     'showVal'      => false, 'showCatName'      => false, 'showLegendKey'      => false, 'showSerName'      => false,
                        'showPercent'      => false, 'showLeaderLines'      => false, 'showBubbleSize'      => true,
                     )
            );
             $style_2 = array(
                'width'          => Converter::cmToEmu(16),
                'height'         => Converter::cmToEmu(6),
                'showAxisLabels' => true,
                'showGridX'      => false,
                'showGridY'      => false,
                //'showLegend'     => true,
                'valueLabelPosition'     => FALSE,
                  'dataLabelOptions' => array(
                     'showVal'      => false,
                        'showCatName'      => false,
                        'showLegendKey'      => false,
                        'showSerName'      => false,
                        'showPercent'      => false,
                        'showLeaderLines'      => false,
                        'showBubbleSize'      => true,
                     )

            );
             $section = $phpWord->addSection(array('colsNum' => 1, 'breakType' => 'continuous'));
            $chartTypes = array('line');
            $chartTypes2 = array('column');
            $twoSeries = array('bar', 'column', 'line', 'area', 'scatter', 'radar', 'stacked_bar', 'percent_stacked_bar', 'stacked_column', 'percent_stacked_column');
            $threeSeries = array('bar', 'line');
            
            //daftar isi
            $section->addText('DAFTAR ISI',  array('name' => 'Arial','spaceAfter'=>80, 'size' => 16,'bold' => true),array('alignment'=>'center'));
           $section->addText( ''.$daftarisi++.'. Daftar isi..............................................................................................................................................2');
           $section->addText( ''.$daftarisi++.'. Daftar gambar.....................................................................................................................................3');
           $section->addText( ''.$daftarisi++.'. Pertumbuhan ekonomi........................................................................................................................4');
           $section->addText( ''.$daftarisi++.'. Perkembangan PDRB per kapita ADHB.............................................................................................5');
           $section->addText( ''.$daftarisi++.'. Perkembangan PDRB per kapita ADHK tahun dasar 2010................................................................6');
           $section->addText( ''.$daftarisi++.'. Perkembangan jumlah penganggur....................................................................................................7');
           $section->addText( ''.$daftarisi++.'. Tingkat pengangguran terbuka...........................................................................................................8');
           $section->addText( ''.$daftarisi++.'. Indeks pembangunan manusia...........................................................................................................9');
           $section->addText( ''.$daftarisi++.'. Gini rasio...........................................................................................................................................10' );
           $section->addText( ''.$daftarisi++.'. Angka harapan hidup......................................................................................................................11');
           $section->addText( ''.$daftarisi++.'. Rata-rata lama sekolah...................................................................................................................12');
           $section->addText( ''.$daftarisi++.'. Harapan lama sekolah....................................................................................................................13');
           $section->addText( ''.$daftarisi++.'. Pengeluaran per kapita...................................................................................................................14');
           $section->addText( ''.$daftarisi++.'. Tingkat kemiskinan.........................................................................................................................15');
           $section->addText( ''.$daftarisi++.'. Indeks kedalaman kemiskinan (P1) ...............................................................................................16');
           $section->addText( ''.$daftarisi++.'. Indeks keparahan kemiskinan (P2) ...............................................................................................17');
           $section->addText( ''.$daftarisi++.'. Jumlah penduduk miskin................................................................................................................18');
           //daftar gambar 
            $section = $phpWord->addSection();
            $section->addText('DAFTAR GAMBAR',  array('name' => 'Arial','spaceAfter'=>80, 'size' => 16,'bold' => true),array('alignment'=>'center'));
            
            
            
             //Pertumbuhan Ekonomi
            $sql_ppe = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";        
            $list_ppe = $this->db->query($sql_ppe);
            foreach ($list_ppe->result() as $row_ppe) {
                        $nilaiData_ppe[] = (float)$row_ppe->nilai;
                        $nilai_ppe_n[$row_ppe->tahun] = (float)$row_ppe->nilai;
                        $tahun_ppe[]    = $row_ppe->tahun;
                        $idperiode_ppe[] = $row_ppe->id_periode;
                        $periode = $row_ppe->periode;
                        if($periode == '00'){ $thn[]=$row_ppe->tahun;  }
                        else{ $thn[]=  $prde[$row_ppe->periode]." - ".$row_ppe->tahun; }
            }
                    $datay_ppe = $nilaiData_ppe;
                    $tahun_ppe = $tahun_ppe;
                    $tahun_ppe_c = $thn;
                    $periode_kab_ppe_max = max($idperiode_ppe);
                    
            $sql_ppe2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
            $list_ppe2 = $this->db->query($sql_ppe2);
            foreach ($list_ppe2->result() as $row_ppe2) {
                        $tahun_ppe2[]   = $row_ppe2->tahun;
                        $nilaiData_ppe2[] = (float)$row_ppe2->nilai;
                        $nilai_ppe2_pro[$row_ppe2->tahun] = (float)$row_ppe2->nilai;
            }
                    $datay_ppe2 = $nilaiData_ppe2;
                    $tahun_ppe2 = $tahun_ppe2;
                    
            $sql_ppe3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='1' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='1' AND periode !='01' AND wilayah='1000' AND periode !='01' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='1' AND wilayah='".$kab."')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='1' AND wilayah='".$kab."' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode"; 
                   
            $list_ppe3 = $this->db->query($sql_ppe3);
            foreach ($list_ppe3->result() as $row_ppe3) {
                        $sumber_ppe = $row_ppe3->sumber_k;
                        $n_ppe3 = $row_ppe3->nilai_kab;
                        if($n_ppe3==0){
                            $nilaiData_kppe3 = '#N/A';
                        }else{
                            $nilaiData_kppe3 = number_format((float)$n_ppe3,2);
                        }
                        $nilaiData_ppe3[]=$nilaiData_kppe3;
                        $nilaiData_ppe33[$row_ppe3->tahun]=$nilaiData_kppe3;
                        $datay_ppe33[]=$row_ppe3->nilai_kab;
                        $tahun_ppe3[]=$row_ppe3->tahun;
                        $periode_ppe3[]=$row_ppe3->periodekab;
            }
                    
            $datay_ppe3 = array_reverse($nilaiData_ppe3);
            $tahun_ppe3 = $tahun_ppe3;
            $tahun_kab_ppe_max=max($tahun_ppe3);
            $tahun_kab_ppe_ke2=$tahun_kab_ppe_max-1;
            $periode_kab_ppe_max=max($periode_ppe3);
                    
                     if($nilaiData_ppe33[$tahun_kab_ppe_ke2] > $nilaiData_ppe33[$tahun_kab_ppe_max]){                        
                        $paragraf_1_2    ="Pertumbuhan Ekonomi ". $xnameKab ." pada tahun ".$tahun_kab_ppe_max." menurun dibandingkan dengan tahun ".$tahun_kab_ppe_ke2.". Pada ".$tahun_kab_ppe_max." pertumbuhan ekonomi ". $xnameKab ." adalah sebesar ". $nilaiData_ppe33[$tahun_kab_ppe_max] ."%, sedangkan pada tahun ".$tahun_kab_ppe_ke2." pertumbuhan tercatat sebesar ".$nilaiData_ppe33[$tahun_kab_ppe_ke2]."%. ";
                        if($nilai_ppe2_pro[$tahun_kab_ppe_max]>$nilaiData_ppe33[$tahun_kab_ppe_max]){
                            $paragraf_1_3  ="Pertumbuhan ekonomi ". $xnameKab ." pada tahun ".$tahun_kab_ppe_max." berada dibawah capaian ". $xname .". Pertumbuhan ekonomi ". $xname ." pada tahun ".$tahun_kab_ppe_max." adalah sebesar ". $nilai_ppe2_pro[$tahun_kab_ppe_max] ."%. ";
                        }else{
                            $paragraf_1_3  ="Pertumbuhan ekonomi ". $xnameKab ." pada tahun ".$tahun_kab_ppe_max." berada diatas capaian ". $xname .". Pertumbuhan ekonomi ". $xname ." pada tahun ".$tahun_kab_ppe_max." adalah sebesar ". $nilai_ppe2_pro[$tahun_kab_ppe_max] ."%. ";
                        }
                    } else {
                        $paragraf_1_2    ="Pertumbuhan Ekonomi ". $xnameKab ." pada tahun ".$tahun_kab_ppe_max." meningkat dibandingkan dengan tahun ".$tahun_kab_ppe_ke2.". Pada ".$tahun_kab_ppe_max." pertumbuhan ekonomi ". $xnameKab ." adalah sebesar ". $nilaiData_ppe33[$tahun_kab_ppe_max] ."%, sedangkan pada tahun ".$tahun_kab_ppe_ke2." pertumbuhan tercatat sebesar ".$nilaiData_ppe33[$tahun_kab_ppe_ke2]."%. ";    
                        if($nilai_ppe2_pro[$tahun_kab_ppe_max]>$nilaiData_ppe33[$tahun_kab_ppe_max]){
                            $paragraf_1_3  ="Pertumbuhan ekonomi ". $xnameKab ." pada tahun ".$tahun_kab_ppe_max." berada dibawah capaian ". $xname .". Pertumbuhan ekonomi ". $xname ." pada tahun ".$tahun_kab_ppe_max." adalah sebesar ". $nilai_ppe2_pro[$tahun_kab_ppe_max] ."%. ";
                        }else{
                            $paragraf_1_3  ="Pertumbuhan ekonomi ". $xnameKab ." pada tahun ".$tahun_kab_ppe_max." berada diatas capaian ". $xname .". Pertumbuhan ekonomi ". $xname ." pada tahun ".$tahun_kab_ppe_max." adalah sebesar ". $nilai_ppe2_pro[$tahun_kab_ppe_max] ."%. ";
                        }
                    }
                    if($nilai_ppe_n[$tahun_kab_ppe_max] > $nilaiData_ppe33[$tahun_kab_ppe_max]){
                        $paragraf_1_4    ="Capaian pertumbuhan ekonomi ". $xnameKab ." pada tahun ".$tahun_kab_ppe_max." berada dibawah nasional. Pertumbuhan ekonomi nasional pada tahun ".$tahun_kab_ppe_max." adalah sebesar ". $nilai_ppe_n[$tahun_kab_ppe_max]."% ";
                    } else {
                        $paragraf_1_4    ="Capaian pertumbuhan ekonomi ". $xnameKab ." pada tahun ".$tahun_kab_ppe_max." berada diatas nasional. Pertumbuhan ekonomi nasional pada tahun ".$tahun_kab_ppe_max." adalah sebesar ".$nilai_ppe_n[$tahun_kab_ppe_max]."%  ";
                    }
                    $tahun_pe_max= $tahun_kab_ppe_max."" ;
                    $ppe_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='1' AND e.id_periode='".$periode_kab_ppe_max."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='1' AND id_periode='".$periode_kab_ppe_max."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    
                    $list_kab_ppe_per = $this->db->query($ppe_kab);
                    foreach ($list_kab_ppe_per->result() as $row_ppe_kab_per) {
                        $label_ppe[]     = $row_ppe_kab_per->label;
                        $nilai_ppe_per[] = $row_ppe_kab_per->nilai;
                        $posisi_ppe=strpos($row_ppe_kab_per->label, "Kabupaten");
                        if ($posisi_ppe !== FALSE){
                            $label_ppe11=substr( $row_ppe_kab_per->label,0,3)." ".substr( $row_ppe_kab_per->label,10);
                        }else{
                            $label_ppe11=$row_ppe_kab_per->label;
                        }
                        $label_ppe1[]=$label_ppe11;
                    }
                    $label_data_ppe     = $label_ppe1;
                    $nilai_data_ppe_per = $nilai_ppe_per;
                    
            // Halaman Baru
            $section = $phpWord->addSection();
            $header = $section->addHeader();
            $header->addWatermark(base_url("assets/images/header.png"),
                    array( 'headerHeight' => 300, 'marginTop' => -36, 'marginLeft' => -70, 'footerHeight' => -50, 'width' => 590,
                            //'height'=> 30,
                            'posHorizontal' => 'absolute', 'posVertical' => 'absolute',
                )
            );
            $footer = $section->addFooter();
            $textRun = $footer->addTextRun(array('alignment' => 'center'));
            $textRun->addField('PAGE', array('format' => 'NUMBER'));
            $textRun->addText('');
            $fontStyleName = '';
            $phpWord->addFontStyle( $fontStyleName,array('name' => 'Tahoma', 'size' => 12, 'color' => '1B2232', 'bold' => true) );
            
            $section = $phpWord->addSection(array('colsNum' => 1, 'breakType' => 'continuous'));
            $style       = array( 'width' => Converter::cmToEmu(16), 'height'         => Converter::cmToEmu(6), 'showAxisLabels' => true, 'showGridX'      => false, 'showGridY'      => false, 'showLegend'     => true, 'valueLabelPosition'     => FALSE, );
            $style_1     = array( 'width'          => Converter::cmToEmu(16), 'height'         => Converter::cmToEmu(6), 'showAxisLabels' => true, );
            $chartTypes  = array('line');
            $chartTypes2 = array('column');
            $twoSeries   = array('bar', 'column', 'line', 'area', 'scatter', 'radar', 'stacked_bar', 'percent_stacked_bar', 'stacked_column', 'percent_stacked_column');
            $threeSeries = array('bar', 'line');

            $section->addText(''.$nomor++.'. Pertumbuhan Ekonomi',$fontStyleName );
            $section->addText($deskripsi1,array('name' => 'Arial','spaceAfter'=>100, 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_1_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_1_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_1_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            //$section->addTextBreak(1);
            
            $categories = $tahun_ppe_c;
            $series1    = $datay_ppe;
            $series2    = $datay_ppe2;
            $series3    = $datay_ppe3;
            $showGridLines = true;
            $showAxisLabels = true;  
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar '.$gambar++.' Perkembangan Pertumbuhan Ekonomi (%)',array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
                $chart = $section->addChart($chartType, $categories, $series1,$style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories, $series2, $xname );
                    $chart->addSeries($categories, $series3, $judul );
                }
                $section->addTextBreak();
            }            
            $categories_1_2 = $label_data_ppe;
            $series_1_2     = $nilai_data_ppe_per;
            $section->addText("Gambar ".$gambar++." Perbandingan Perkembangan Pertumbuhan Ekonomi Tahun ".$tahun_pe_max." Antar Kabupaten/Kota Di $xname (%)",array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
            $chart = $section->addChart('column', $categories_1_2, $series_1_2, $style_2);
            $section->addText('Sumber Data : Badan Pusat Statistik',array('name' => 'Tahoma', 'size' => 10));
            
            //Perkembangan PDRB Per Kapita ADHB
            $sql_adhb = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhb = $this->db->query($sql_adhb);
            foreach ($list_adhb->result() as $row_adhb) {
                        $tahun_adhb[]   = $row_adhb->tahun;
                        $nilaiData_adhb1[] = number_format((float)$row_adhb->nilai/1000000,2);
                        $nilaiData_max[]   = (float)$row_adhb->nilai;
                        $nilaiData_maxx[$row_adhb->tahun]   = (float)$row_adhb->nilai;
            }
            $datay_adhb1 = $nilaiData_adhb1;
            $tahun_adhb1 = $tahun_adhb;
            $sql_adhb2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
            $list_adhb2 = $this->db->query($sql_adhb2);
            foreach ($list_adhb2->result() as $row_adhb2) {
                $tahun_adhb2[]   = $row_adhb2->tahun;
                $nilaiData_adhb2[] = number_format((float)$row_adhb2->nilai/1000000,2);
                $nilaiData_max_p[]   = (float)$row_adhb2->nilai;
                $nilaiD_adhb2[$row_adhb2->tahun] = (float)$row_adhb2->nilai;
                $ket_adhb2[]  = $row_adhk2->keterangan;
            }
            $datay_adhb2 = $nilaiData_adhb2;
            $tahun_adhb2 = $tahun_adhb2;
                    
                    $sql_adhb3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k, IFNULL(IND.id_periode,'') idperiode, IFNULL(IND.tahun,'')tahun 
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='2' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='2' AND wilayah='1000' group by id_periode
                                                                )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai,sumber,tahun 
                                        from nilai_indikator 
                                        where (id_indikator='2' AND wilayah='".$kab."')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='2' AND wilayah='".$kab."' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode"; 
                    
                    $list_adhb3 = $this->db->query($sql_adhb3);
                    foreach ($list_adhb3->result() as $row_adhb3) {
                        $tahun_adhb3[]     = $row_adhb3->tahun;
                        $nilaiD_adhb3[$row_adhb3->tahun] = (float)$row_adhb3->nilai_kab;
                        $nilaiData_max_k[] = (float)$row_adhb3->nilai_kab;
                        $sumber_adhb       = "BPS";
                        $periode_kab_adhb[]= $row_adhb3->idperiode;
                        $n_adhb3          = (float)$row_adhb3->nilai_kab;
                        if($n_adhb3==0){
                            $nilaiDataadhb3 = '';}
                        else{
                            $nilaiDataadhb3 = number_format((float)$row_adhb3->nilai_kab/1000000,2);
                        }
                        $nilaiData_adhb3[] = $nilaiDataadhb3;
                    }
                    $datay_adhb3 = array_reverse($nilaiData_adhb3);
                    $dataX_adhb3 = array_reverse($tahun_adhb3);
                    $tahunadhb    = end($tahun_adhb1);
                    $periode_kab_adhb_max=max($periode_kab_adhb);
                    $periode_adhb_tahun=max($tahun_adhb3)."" ;
                    $tahun_kab_adhb_max=max($tahun_adhb3);
                    $tahun_kab_adhb_ke2=$tahun_kab_adhb_max-1;
                    
                    if($nilaiD_adhb3[$tahun_kab_adhb_ke2] > $nilaiD_adhb3[$tahun_kab_adhb_max]){
                        $paragraf_2_2    ="PDRB perkapita ADHB ". $xnameKab ." pada tahun ".$tahun_kab_adhb_max." menurun dibandingkan dengan tahun ".$tahun_kab_adhb_ke2.". Pada tahun ".$tahun_kab_adhb_max." PDRB per kapita ADHB ". $xnameKab ." adalah sebesar Rp". number_format($nilaiD_adhb3[$tahun_kab_adhb_max],0) .", sedangkan pada tahun ".$tahun_kab_adhb_ke2." PDRB per kapita ADHB tercatat sebesar Rp ".number_format($nilaiD_adhb3[$tahun_kab_adhb_ke2],0).". ";
                        if($nilaiD_adhb2[$tahun_kab_adhb_max]>$nilaiD_adhb3[$tahun_kab_adhb_max]){
                            $paragraf_2_3  ="PDRB per kapita ADHB ". $xnameKab ." pada tahun ".$tahun_kab_adhb_max." berada dibawah capaian ". $xname .". PDRB perkapita ADHB ". $xname ." pada tahun ".$tahun_kab_adhb_max." adalah sebesar Rp". number_format($nilaiD_adhb2[$tahun_kab_adhb_max],0)." ".$ket_adhk2[5].". ";
                        }else{
                            $paragraf_2_3  ="PDRB per kapita ADHB ". $xnameKab ." pada tahun ".$tahun_kab_adhb_max." berada diatas capaian ". $xname .". PDRB perkapita ADHB ". $xname ." pada tahun ".$tahun_kab_adhb_max." adalah sebesar Rp". number_format($nilaiD_adhb2[$tahun_kab_adhb_max],0)." ".$ket_adhk2[5].". ";
                        }
                    } else{
                        $paragraf_2_2    ="PDRB per kapita ADHB ". $xnameKab ." pada tahun ".$tahun_kab_adhb_max." meningkat dibandingkan dengan tahun ".$tahun_kab_adhb_ke2.". Pada tahun ".$tahun_kab_adhb_max." PDRB per kapita ADHB ". $xnameKab ." adalah sebesar Rp". number_format($nilaiD_adhb3[$tahun_kab_adhb_max],0) .", sedangkan pada tahun ".$tahun_kab_adhb_ke2." PDRB per kapita ADHB tercatat sebesar Rp ".number_format($nilaiD_adhb3[$tahun_kab_adhb_ke2],0).". ";    
                        if($nilaiD_adhb2[$tahun_kab_adhb_max]>$nilaiD_adhb3[$tahun_kab_adhb_max]){
                            $paragraf_2_3  ="PDRB per kapita ADHB ". $xnameKab ." pada tahun ".$tahun_kab_adhb_max." berada dibawah capaian ". $xname .". PDRB perkapita ADHB ". $xname ." pada tahun ".$tahun_kab_adhb_max." adalah sebesar Rp". number_format($nilaiD_adhb2[$tahun_kab_adhb_max],0) ." ".$ket_adhk2[5].". ";
                        }else{
                            $paragraf_2_3  ="PDRB per kapita ADHB ". $xnameKab ." pada tahun ".$tahun_kab_adhb_max." berada diatas capaian ". $xname .". PDRB perkapita ADHB ". $xname ." pada tahun ".$tahun_kab_adhb_max." adalah sebesar Rp". number_format($nilaiD_adhb2[$tahun_kab_adhb_max],0) ." ".$ket_adhk2[5].". ";
                        }
                    }
                    if($nilaiData_maxx[$tahun_kab_adhb_max] > $nilaiD_adhb3[$tahun_kab_adhb_max]){
                        $paragraf_2_4    ="Capaian PDRB perkapita ADHB ". $xnameKab ." pada tahun ".$tahun_kab_adhb_max." berada dibawah nasional. PDRB perkapita ADHB nasional pada tahun ".$tahun_kab_adhb_max." adalah sebesar Rp". number_format($nilaiData_maxx[$tahun_kab_adhb_max],0)." ";
                    }else{
                        $paragraf_2_4    ="Capaian PDRB perkapita ADHB ". $xnameKab ." pada tahun ".$tahun_kab_adhb_max." berada diatas nasional. PDRB perkapita ADHB nasional pada tahun ".$tahun_kab_adhb_max." adalah sebesar Rp". number_format($nilaiData_maxx[$tahun_kab_adhb_max],0)."  ";
                    }
                    
                    $adhb2_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='2' AND e.id_periode='".$periode_kab_adhb_max."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='2' AND id_periode='".$periode_kab_adhb_max."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    
                    $list_kab_adhb_per = $this->db->query($adhb2_kab);
                    foreach ($list_kab_adhb_per->result() as $row_adhb_kab_per) {
                        $label_adhb[]     = $row_adhb_kab_per->label;
                        $nilai_adhb_per[] = number_format($row_adhb_kab_per->nilai/1000000,2);
                        $posisi=strpos($row_adhb_kab_per->label, "Kabupaten");
                        if ($posisi !== FALSE){ $label_adhb11=substr( $row_adhb_kab_per->label,0,3)." ".substr( $row_adhb_kab_per->label,10); }
                        else{ $label_adhb11=$row_adhb_kab_per->label; }
                        $label_adhb1[]     = $label_adhb11;
                    }
                    $label_data_adhb     = $label_adhb1;
                    $nilai_data_adhb_per = $nilai_adhb_per;   
            
                     // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText(''.$nomor++.'. Perkembangan PDRB per Kapita ADHB',$fontStyleName);
            $section->addText($deskripsi2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_2_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_2_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_2_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addTextBreak(1);
            $categories_2_2 = $tahun_adhb;
            $series_2_n     = $nilaiData_adhb1;
            $series_2_2     = $datay_adhb2;
            $series_2_3     = $datay_adhb3;
            foreach ($chartTypes2 as $chartTypes22) {
                $section->addText('Gambar '.$gambar++.' Perkembangan PDRB per Kapita ADHB (Juta Rupiah)',array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
                $chart = $section->addChart($chartTypes22, $categories_2_2, $series_2_n,$style_1, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartTypes22, $twoSeries)) {
                    $chart->addSeries($categories_2_2, $series_2_2, $xname );
                    $chart->addSeries($categories_2_2, $series_2_3, $judul );
                }
                $section->addTextBreak();
            }  
            
            $categories_2_3 = $label_data_adhb;
            $series_2_3     = $nilai_data_adhb_per;
            $section->addText("Gambar ".$gambar++." Perbandingan PDRB per Kapita ADHB tahun ".$periode_adhb_tahun." (Juta Rupiah)",array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
            $chart_2_3      = $section->addChart('column', $categories_2_3, $series_2_3, $style_2);
//            $section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik',array('name' => 'Arial', 'size' => 10));
            
             //PDRB per Kapita ADHK (Rp)
            $sql_adhk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
            $list_adhk = $this->db->query($sql_adhk);
                foreach ($list_adhk->result() as $row_adhk) {
                        $tahun_adhk[]   = $row_adhk->tahun;
                        $nilaiData_adhk1[] = (float)$row_adhk->nilai/1000000;
                        $adhk_nasional[] = (float)$row_adhk->nilai;
                        $adhk_nasionall[$row_adhk->tahun] = (float)$row_adhk->nilai;
                }
                $datay_adhk1 = $nilaiData_adhk1;
                $tahun_adhk1 = $tahun_adhk;
            $sql_adhk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                $list_adhk2 = $this->db->query($sql_adhk2);
                foreach ($list_adhk2->result() as $row_adhk2) {
                        $tahun_adhk2[]              = $row_adhk2->tahun;
                        $nilaiData_adhk2[]          = (float)$row_adhk2->nilai/1000000;
                        $adhk_p[]                   = (float)$row_adhk2->nilai;
                        $adhk_pp[$row_adhk2->tahun] = (float)$row_adhk2->nilai;
                        $ket_adhk2[]                = $row_adhk2->keterangan;
                }
                
                $datay_adhk2 = $nilaiData_adhk2;
                $tahun_adhk2 = $tahun_adhk2;
                $sql_adhk3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k, IFNULL(IND.id_periode,'') idperiode, IFNULL(IND.tahun,'')tahun 
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
                                        where (id_indikator='3' AND wilayah='".$kab."')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='3' AND wilayah='".$kab."' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode"; 
                    $list_adhk3 = $this->db->query($sql_adhk3);
                    foreach ($list_adhk3->result() as $row_adhk3) {
                        $tahun_adhk3[]   = $row_adhk3->tahun;
                        $n_adhk3          = (float)$row_adhk3->nilai_kab;
                        if($n_adhk3==0){
                            $nilaiDataadhk3 = '#N/A';}
                        else{
                            $nilaiDataadhk3 = (float)$row_adhk3->nilai_kab/1000000;
                        }
                        $nilaiData_adhk3[] = $nilaiDataadhk3;
                        $adhk_k[]          = (float)$row_adhk3->nilai_kab;
                        $adhk_kk[$row_adhk3->tahun] = (float)$row_adhk3->nilai_kab;
                        $sumber_adhk       = "diolah";
                        $periode_kab_adhk[] = $row_adhk3->idperiode;
                    }
                    $datay_adhk3 = array_reverse($nilaiData_adhk3);
                    $tahun_adhk3 = $tahun_adhk3;
                    $periode_kab_adhk_max=max($periode_kab_adhk);
                    $periode_adhk_tahun=max($tahun_adhk3)."" ;
                    $tahun_adhk3_max=max($tahun_adhk3);
                    $tahun_adhk3_1=$tahun_adhk3_max-1;
                    if($adhk_kk[$tahun_adhk3_1] > $adhk_kk[$periode_adhk_tahun]){                        
                        $max_adhk    ="PDRB per Kapita ADHK Tahun Dasar 2010 ". $xnameKab ." pada tahun ".$tahun_adhk3_max." menurun dibandingkan dengan tahun ".$tahun_adhk3_1.". Pada ".$tahun_adhk3_max." PDRB per Kapita ADHK Tahun Dasar 2010 ". $xnameKab ." adalah sebesar Rp ".number_format($adhk_kk[$tahun_adhk3_max],0) .", sedangkan pada tahun ".$tahun_adhk3_1." PDRB per Kapita ADHK Tahun Dasar 2010 tercatat sebesar Rp ".number_format($adhk_kk[$tahun_adhk3_1],0).". ";
                        if($adhk_pp[$tahun_adhk3_max]>$adhk_kk[$tahun_adhk3_max]){
                            $max_adhk_p  =" PDRB per Kapita ADHK Tahun Dasar 2010 ". $xnameKab ." pada tahun ".$tahun_adhk3_max." berada dibawah capaian ". $xname .". PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." pada tahun ".$tahun_adhk3_max." adalah sebesar Rp ".number_format($adhk_pp[$tahun_adhk3_max],0)." ".$ket_adhk2[$tahun_adhk3_max].". ";
                        }else{
                            $max_adhk_p  ="  PDRB per Kapita ADHK Tahun Dasar 2010 ". $xnameKab ." pada tahun ".$tahun_adhk3_max." berada diatas capaian ". $xname .". PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." pada tahun ".$tahun_adhk3_max." adalah sebesar Rp ".number_format($adhk_pp[$tahun_adhk3_max],0)." ".$ket_adhk2[$tahun_adhk3_max].". ";
                        }
                    } else {
                        $max_adhk    ="PDRB per Kapita ADHK Tahun Dasar 2010 ". $xnameKab ." pada tahun ".$tahun_adhk3_max." meningkat dibandingkan dengan tahun ".$tahun_adhk3_1.". Pada ".$tahun_adhk3_max." PDRB per Kapita ADHK Tahun Dasar 2010 ". $xnameKab ." adalah sebesar Rp ".number_format($adhk_kk[$tahun_adhk3_max],0) .", sedangkan pada tahun ".$tahun_adhk3_1." PDRB per Kapita ADHK Tahun Dasar 2010 tercatat sebesar Rp ".number_format($adhk_kk[$tahun_adhk3_1],0).". ";    
                        if($adhk_pp[$tahun_adhk3_max]>$adhk_kk[$tahun_adhk3_max]){
                            $max_adhk_p  =" PDRB per Kapita ADHK Tahun Dasar 2010 ". $xnameKab ." pada tahun ".$tahun_adhk3_max." berada dibawah capaian ". $xname .". PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." pada tahun ".$tahun_adhk3_max." adalah sebesar Rp ".number_format($adhk_pp[$tahun_adhk3_max],0)." ".$ket_adhk2[$tahun_adhk3_max]." . ";
                        }else{
                            $max_adhk_p  ="  PDRB per Kapita ADHK Tahun Dasar 2010 ". $xnameKab ." pada tahun ".$tahun_adhk3_max." berada diatas capaian ". $xname .". PDRB per Kapita ADHK Tahun Dasar 2010 ". $xname ." pada tahun ".$tahun_adhk3_max." adalah sebesar Rp ".number_format($adhk_pp[$tahun_adhk3_max],0)." ".$ket_adhk2[$tahun_adhk3_max].". ";
                        }
                    }
                    if($adhk_nasionall[$tahun_adhk3_max] > $adhk_kk[$tahun_adhk3_max]){
                        $max_adhk_k    =" Capaian PDRB per Kapita ADHK Tahun Dasar 2010 ". $xnameKab ." pada tahun ".$tahun_adhk3_max." berada dibawah nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun ".$tahun_adhk3_max." adalah sebesar Rp ". number_format($adhk_nasionall[$tahun_adhk3_max],0)." ";
                    } else {
                        $max_adhk_k    =" Capaian PDRB per Kapita ADHK Tahun Dasar 2010 ". $xnameKab ." pada tahun ".$tahun_adhk3_max." berada diatas nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun ".$tahun_adhk3_max." adalah sebesar Rp ". number_format($adhk_nasionall[$tahun_adhk3_max],0)."  ";
                    }
                    $adhk2_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='3' AND e.id_periode='".$periode_kab_adhk_max."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='3' AND id_periode='".$periode_kab_adhk_max."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_adhk_per = $this->db->query($adhk2_kab);
                    foreach ($list_kab_adhk_per->result() as $row_adhk_kab_per) {
                        $label_adhk[]     = $row_adhk_kab_per->label;
                        $nilai_adhk_per[] = $row_adhk_kab_per->nilai/1000000;
                        $posisi_adhk=strpos($row_adhk_kab_per->label, "Kabupaten");
                        if ($posisi_adhk !== FALSE){
                            $label_adhk11=substr( $row_adhk_kab_per->label,0,3)." ".substr( $row_adhk_kab_per->label,10);
                        }else{
                            $label_adhk11=$row_adhk_kab_per->label;
                        }
                        $label_adhk1[]=$label_adhk11;
                    }
                    $label_data_adhk     = $label_adhk1;
                    $nilai_data_adhk_per = $nilai_adhk_per;
                    
                    $paragraf_3_2=$max_adhk;
                    $paragraf_3_3=$max_adhk_k;
            $section = $phpWord->addSection();
            $section->addText(''.$nomor++.'. Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010', $fontStyleName );
            $section->addText($deskripsi3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_3_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_3_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addTextBreak(1);
            $categories = $tahun_adhk;
            $series1    = $datay_adhk1;
            $series2    = $datay_adhk2;
            $series3    = $datay_adhk3;
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar '.$gambar++.' Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010 (Rupiah)',array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
                $chart = $section->addChart($chartType, $categories, $series1,$style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories, $series2, $xname );
                    $chart->addSeries($categories, $series3, $judul );
                }
//                $section->addTextBreak();
            }
            $categories_1_2 = $label_adhk1;
            $series_1_2     = $nilai_data_adhk_per;
            $section->addText("Gambar ".$gambar++." Perbandingan Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010 Tahun ".$tahun_pe_max." Antar Kabupaten/Kota Di $xname (%)",array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
            $chart = $section->addChart('column', $categories_1_2, $series_1_2, $style_2);
            $section->addText('Sumber Data : Badan Pusat Statistik',array('name' => 'Tahoma', 'size' => 10));
            
            //jumlah pengangguran
                    $sql_jp = "SELECT REF.id_periode,IND.nilai nilai_nas
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='4' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='4' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        ASC limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='4' AND wilayah='1000')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='4' AND wilayah='1000' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode ASC limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                  
                    $list_jp = $this->db->query($sql_jp);
                    foreach ($list_jp->result() as $row_jp) {
                        $tahun_ip[]     = $bulan[substr($row_jp->id_periode,4)]."-".substr($row_jp->id_periode,0,-2);
                        $nilaiData_jp[] = (float)$row_jp->nilai_nas/1000;
                        
                    }
                    $datay_jp =$nilaiData_jp;
                    $tahun_jp = $tahun_ip;

                    $sql_jp2 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_prov
                        FROM(
                                select DISTINCT id_periode from nilai_indikator 
                                where (id_indikator='4' AND wilayah='1000') 
                                AND (id_periode, versi) in (
                                                                                           select id_periode, max(versi) as versi 
                                                                                           from nilai_indikator 
                                                                                           WHERE id_indikator='4' AND wilayah='1000' group by id_periode
                                                                                   )
                                order by id_periode 
                                ASC limit 6 
                        ) REF
                        LEFT JOIN(
                                        select id_periode,nilai 
                                from nilai_indikator 
                                where (id_indikator='4' AND wilayah='".$id_pro."')
                                AND (id_periode, versi) in (
                                                        select id_periode, max(versi) as versi 
                                        from nilai_indikator 
                                        WHERE id_indikator='4' AND wilayah='".$id_pro."' group by id_periode
                                        ) 
                                        group by id_periode 
                                        order by id_periode ASC limit 6
                        ) IND	ON REF.id_periode=IND.id_periode"; 
                    $list_jp2 = $this->db->query($sql_jp2);
                    foreach ($list_jp2->result() as $row_jp2) {
                        $tahun_jp2[]   = $row_jp2->id_periode;
                        $nilaiData_jp2[] = (float)$row_jp2->nilai_prov/1000;
                        //$nilai_capaian2[] = $row_jp2->nilai_prov;
                        $nilai_capaian[]= (float)$row_jp2->nilai_prov;
                    }
                    $datay_jp2 = $nilaiData_jp2;
                    $tahun_jp2 = $tahun_jp2;
                    
                    $sql_jp3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k, IND.tahun, IND.periode 
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='4' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='4' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        ASC limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai,sumber,tahun,periode 
                                        from nilai_indikator 
                                        where (id_indikator='4' AND wilayah='".$kab."')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='4' AND wilayah='".$kab."' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode ASC limit 6
                                ) IND	ON REF.id_periode=IND.id_periode"; 
                    $list_jp3 = $this->db->query($sql_jp3);
                    foreach ($list_jp3->result() as $row_jp3) {
                        $tahun_jp3[]     = $row_jp3->id_periode;
                        $tahun_jp33[]     = $row_jp3->tahun;
                        $nilaiDatajp3_1 = $row_jp3->nilai_kab;
                        if($nilaiDatajp3_1='0'){
                            $nilai_k='#N/A';
                        }else{
                            $nilai_k=(float)$row_jp3->nilai_kab/1000;
                        }                            
                        $nilaiDatajp3[] = $nilai_k; 
                        //$nilai_capaian3[]                      = (float)$row_jp3->nilai_kab;
                        $nilai_capaian33[$row_jp3->id_periode] = (float)$row_jp3->nilai_kab;
                        $sumber_jp                             =$row_jp3->sumber_k;
                        $tahun_jp333[$row_jp3->id_periode]     = $bulan[$row_jp3->periode]."-".$row_jp3->tahun;
                    }                                            
                    $datay_jp3 = $nilaiDatajp3;
                    $periode_kab_jp_max = max($tahun_jp3);
                    $periode_kab_jp_maxx = max($tahun_jp3);
                    $periode_kab_jp_1 = $periode_kab_jp_maxx-100;
                    $max_jp="";
                    $periode_jp_tahun=" ".$tahun_jp333[$periode_kab_jp_maxx]."" ;
  
                    
                    if($nilai_capaian33[$periode_kab_jp_1] > $nilai_capaian33[$periode_kab_jp_maxx]){
                        $rt_jp  = $nilai_capaian33[$periode_kab_jp_maxx]-$nilai_capaian33[$periode_kab_jp_1];
                        $rt_jpp = abs($nilai_capaian33[$periode_kab_jp_maxx]-$nilai_capaian33[$periode_kab_jp_1]);
                        $rt_jp2=$rt_jp/$nilai_capaian33[$periode_kab_jp_1];
                        $rt_jp3=abs($rt_jp2*100);
                        $rt_jp33=number_format($rt_jp3,2);
                        $max_jp_p  ="Jumlah penganggur di ". $xnameKab ." pada ". $tahun_jp333[$periode_kab_jp_maxx]." sebanyak ". number_format($nilai_capaian33[$periode_kab_jp_maxx],0) . " orang. Sedangkan jumlah penganggur pada ".$tahun_jp333[$periode_kab_jp_1]." sebanyak ". number_format($nilai_capaian33[$periode_kab_jp_1],0) . " orang. Selama periode  ".$tahun_jp333[$periode_kab_jp_1]." - ".$tahun_jp333[$periode_kab_jp_maxx]. " jumlah penganggur di ". $xnameKab." berkurang ".number_format($rt_jpp)." orang atau sebesar ".$rt_jp33 ."% ";
                    }else{
                        $rt_jp  =$nilai_capaian3[5]-$nilai_capaian3[3];
                        $rt_jp2=$rt_jp/$nilai_capaian3[3];
                        $rt_jp3=$rt_jp2*100;
                        $rt_jp33=number_format($rt_jp3,2);
                        $max_jp_p  ="Jumlah penganggur di ". $xnameKab ." pada ".$tahun_jp333[$periode_kab_jp_maxx]." sebanyak ". number_format($nilai_capaian33[$periode_kab_jp_maxx],0) . " orang. Sedangkan jumlah penganggur pada ".$tahun_jp333[$periode_kab_jp_1]." sebanyak ". number_format($nilai_capaian33[$periode_kab_jp_1],0) . " orang. Selama periode  ".$tahun_jp333[$periode_kab_jp_1]." - ".$tahun_jp333[$periode_kab_jp_maxx]. " jumlah penganggur di ". $xnameKab ." meningkat ".number_format($rt_jp)." orang atau sebesar ".$rt_jp33 ."%";
                    }
                    $jp2_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='4' AND e.id_periode='".$periode_kab_jp_max."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='4' AND id_periode='".$periode_kab_jp_max."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_jp_per = $this->db->query($jp2_kab);
                    foreach ($list_kab_jp_per->result() as $row_jp_kab_per) {
                        $label_jp[]     = $row_jp_kab_per->label;
                        $nilai_jp_per[] = $row_jp_kab_per->nilai/1000;
                        $posisi_jp=strpos($row_jp_kab_per->label, "Kabupaten");
                        if ($posisi_jp !== FALSE){
                            $label_jp11=substr( $row_jp_kab_per->label,0,3)." ".substr( $row_jp_kab_per->label,10);
                        }else{
                            $label_jp11=$row_jp_kab_per->label;
                        }
                        $label_jp1[]=$label_jp11;
                    }
                    $label_data_jp     = $label_jp1;
                    $nilai_data_jp_per = $nilai_jp_per;
            $section->addText(''.$nomor++.' Perkembangan Jumlah Penganggur', $fontStyleName );
            $paragraf_4_2=$max_jp_p;
            $section->addText($deskripsi4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_4_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $categories_4_2 = $tahun_jp;
            $series_4_n     = $datay_jp;
            $series_4_2     = $datay_jp2;
            $series_4_3     = $datay_jp3;
            foreach ($chartTypes2 as $chartTypes22) {
                $section->addText('Gambar '.$gambar++.' Perkembangan Jumlah Penganggur (Ribu Orang)',array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
//                $chart = $section->addChart($chartTypes22, $categories_4_2, $series_4_n,$style_1, 'Nasional');
                $chart = $section->addChart($chartTypes22, $categories_4_2, $series_4_3,$style_1, $judul);
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartTypes22, $twoSeries)) {
                   // $chart->addSeries($categories_4_2, $series_4_2, $xname );
                   // $chart->addSeries($categories_4_2, $series_4_3, $judul );
                }
                $section->addTextBreak();
            }
            $categories_4_3 = $label_data_jp;
            $series_1_4     = $nilai_data_jp_per;
            $section->addText("Gambar ".$gambar++." Perkembangan Jumlah Penganggur ".$tahun_pe_max." Antar Kabupaten Di $xname (%)",array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
            $chart = $section->addChart('column', $categories_4_3, $series_1_4, $style_2);
            $section->addText('Sumber Data : Badan Pusat Statistik',array('name' => 'Tahoma', 'size' => 10));
            
            //tingkat pengangguran terbuka                    
                    $sql_tpt = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 8) y order by id_periode ASC"; 
                    $list_tpt = $this->db->query($sql_tpt);
                    foreach ($list_tpt->result() as $row_tpt) {
                        $tahun_tpt1[]     = $bulan[$row_tpt->periode]."-".$row_tpt->tahun;
                        $tahun_tpt[]      = $row_tpt->tahun;
                        $nilaiData_tpt[]  = (float)$row_tpt->nilai;
                        $data_tpt[$row_tpt->id_periode] = (float)$row_tpt->nilai;
                    }
                    $datay_tpt = $nilaiData_tpt;
                    $tahun_tpt = $tahun_tpt;
                    $sql_tpt2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 8) y order by id_periode ASC"; 
                    $list_tpt2 = $this->db->query($sql_tpt2);
                    foreach ($list_tpt2->result() as $row_tpt2) {
                        $tahun_tpt2[]   = $row_tpt2->tahun;
                        $nilaiData_tpt2[] = (float)$row_tpt2->nilai;
                        $data_tpt2[$row_tpt2->id_periode] = (float)$row_tpt2->nilai;
                    }
                    $datay_tpt2 = $nilaiData_tpt2;
                    $tahun_tpt2 = $tahun_tpt2;
                    
                    $sql_tpt3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k, IND.tahun, IND.periode, IND.id_periode 'perkab' 
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='6' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='6' AND wilayah='1000' group by id_periode
                                                                    )
                                        order by id_periode 
                                        Desc limit 8
                                ) REF
                                LEFT JOIN(
                                        select id_periode,nilai,sumber,tahun,periode 
                                        from nilai_indikator 
                                        where (id_indikator='6' AND wilayah='".$kab."')
                                            AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='6' AND wilayah='".$kab."' group by id_periode
                                                                    ) 
                                        group by id_periode 
                                        order by id_periode Desc limit 8
                                        ) IND	ON REF.id_periode=IND.id_periode"; 
                    //print_r($sql_tpt3);exit();
                    $list_tpt3 = $this->db->query($sql_tpt3);
                    foreach ($list_tpt3->result() as $row_tpt3) {
                        if($row_tpt3->nilai_kab==0){
                            $nilaiData_ktpt3 = '#N/A';
                        }else{
                            $nilaiData_ktpt3 = (float)$row_tpt3->nilai_kab;
                        }
                        $nilaiData_tpt3[]=$nilaiData_ktpt3;
                        $data_tpt3[$row_tpt3->id_periode]   = $nilaiData_ktpt3;
                        $sumber_tpt                         = "Badan Pusat Statistik";
                        $diperiode_tpt3[]                   = $row_tpt3->id_periode;
                        $diperiode_tpt33[]                   = $row_tpt3->perkab;
                        $tahun_tpt3[]                       = $row_tpt3->tahun;
                        $datay_tpt33[$row_tpt3->id_periode] = $bulan[$row_tpt3->periode]." ".$row_tpt3->tahun;
                        
                    }
//                    print_r($sql_tpt3);exit();
                    $datay_tpt3 = array_reverse($nilaiData_tpt3);
                    $tahun_tpt3 = $tahun_tpt3;
                    //$periode_kab_tpt_max = max($diperiode_tpt3);
                    //$periode_kab_tpt_ke2 = $periode_kab_tpt_max-100;
                    $periode_kab_tpt_ke2 = max($diperiode_tpt33);
                    $periode_tpt_tahun="".$datay_tpt33[$periode_kab_tpt_max]."" ;
                    $periode_kab_tpt_max3 = max($diperiode_tpt33);
                    
                    if($data_tpt3[$periode_kab_tpt_ke2] > $data_tpt3[$periode_kab_tpt_max]){
                        $max_tpt    ="Tingkat pengangguran terbuka ". $xnameKab ." pada ".$datay_tpt33[$periode_kab_tpt_max]." menurun dibandingkan dengan ".$datay_tpt33[$periode_kab_tpt_ke2].". Pada ".$datay_tpt33[$periode_kab_tpt_max]." Tingkat pengangguran terbuka ". $xnameKab ." adalah sebesar ".number_format((float)$data_tpt3[$periode_kab_tpt_max],2) ."%, sedangkan pada ".$datay_tpt33[$periode_kab_tpt_ke2]." Tingkat pengangguran terbuka tercatat sebesar ".number_format((float)$data_tpt3[$periode_kab_tpt_ke2],2)."%. ";
                        if($data_tpt2[$periode_kab_tpt_ke2]>$data_tpt3[$periode_kab_tpt_max]){
                            $max_tpt_p  ="Tingkat pengangguran terbuka ". $xnameKab ." pada ".$datay_tpt33[$periode_kab_tpt_max]." berada dibawah capaian ". $xname .". Tingkat pengangguran terbuka ". $xname ." pada ".$datay_tpt33[$periode_kab_tpt_max]." adalah sebesar ".number_format($data_tpt2[$periode_kab_tpt_ke2],2)."%. ";
                        }else{
                            $max_tpt_p  ="Tingkat pengangguran terbuka ". $xnameKab ." pada ".$datay_tpt33[$periode_kab_tpt_max]." berada diatas capaian ". $xname .". Tingkat pengangguran terbuka ". $xname ." pada ".$datay_tpt33[$periode_kab_tpt_max]." adalah sebesar ".number_format($data_tpt2[$periode_kab_tpt_ke2],2)."%. ";
                        }
                    } else {
                        $max_tpt    ="Tingkat pengangguran terbuka ". $xnameKab ." pada ".$datay_tpt33[$periode_kab_tpt_max]." meningkat dibandingkan dengan ".$datay_tpt33[$periode_kab_tpt_ke2].". Pada ".$datay_tpt33[$periode_kab_tpt_max]." Tingkat pengangguran terbuka ". $xnameKab ." adalah sebesar ".number_format((float)$data_tpt3[$periode_kab_tpt_max],2) ."%, sedangkan pada ".$datay_tpt33[$periode_kab_tpt_ke2]." Tingkat pengangguran terbuka tercatat sebesar ".number_format((float)$data_tpt3[$periode_kab_tpt_ke2],2)."%. ";
                        if($data_tpt2[$periode_kab_tpt_ke2]>$data_tpt3[$periode_kab_tpt_max]){
                            $max_tpt_p  ="Tingkat pengangguran terbuka ". $xnameKab ." pada ".$datay_tpt33[$periode_kab_tpt_max]." berada dibawah capaian ". $xname .". Tingkat pengangguran terbuka ". $xname ." pada ".$datay_tpt33[$periode_kab_tpt_max]." adalah sebesar ".number_format($data_tpt2[$periode_kab_tpt_ke2],2)."%. ";
                        }else{
                            $max_tpt_p  ="Tingkat Pengangguran Terbuka ". $xnameKab ." pada ".$datay_tpt33[$periode_kab_tpt_max]." berada diatas capaian ". $xname .". Tingkat pengangguran terbuka ". $xname ." pada ".$datay_tpt33[$periode_kab_tpt_max]." adalah sebesar ".number_format($data_tpt2[$periode_kab_tpt_ke2],2)."%. ";
                        }
                    }
                    if($data_tpt[$periode_kab_tpt_max] > $data_tpt3[$periode_kab_tpt_max]){
                        $max_tpt_k    =" Capaian tingkat pengangguran terbuka ". $xnameKab ." pada ".$datay_tpt33[$periode_kab_tpt_max]." berada dibawah nasional. Tingkat pengangguran terbuka nasional pada ".$datay_tpt33[$periode_kab_tpt_max]." adalah sebesar ". number_format($data_tpt[$periode_kab_tpt_max],2)."% ";
                    } else {
                        $max_tpt_k    =" Capaian tingkat pengangguran terbuka ". $xnameKab ." pada ".$datay_tpt33[$periode_kab_tpt_max]." berada diatas nasional. Tingkat pengangguran terbuka nasional pada ".$datay_tpt33[$periode_kab_tpt_max]." adalah sebesar ". number_format($datay_tpt[$periode_kab_tpt_max],2)."%  ";
                    }
                  
                  $tpt_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='6' AND e.id_periode='".$periode_kab_tpt_max3."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='6' AND id_periode='".$periode_kab_tpt_max3."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
               //   print_r($tpt_kab);exit();
                    $list_kab_tpt_per = $this->db->query($tpt_kab);
                    foreach ($list_kab_tpt_per->result() as $row_tpt_kab_per) {
                        $label_tpt[]     = $row_tpt_kab_per->label;
                        $nilai_tpt_per1 = $row_tpt_kab_per->nilai;
                        if($nilai_tpt_per1=='0'){
                            $nilai_kab='#N/A';
                        }else{
                            $nilai_kab=$row_tpt_kab_per->nilai;
                        }
                        $nilai_tpt_per[] = $nilai_kab;
                        //$nilai_tpt_per[] = $row_tpt_kab_per->nilai;
                        $posisi_tpt      = strpos($row_tpt_kab_per->label, "Kabupaten");
                        if ($posisi_tpt !== FALSE){
                            $label_tpt11=substr( $row_tpt_kab_per->label,0,3)." ".substr( $row_tpt_kab_per->label,10);
                        }else{
                            $label_tpt11=$row_tpt_kab_per->label;
                        }
                        $label_tpt1[]=$label_tpt11;
                    }
                    $label_data_tpt     = $label_tpt1;
                    $nilai_data_tpt_per = $nilai_tpt_per;
                    
                    $paragraf_5_2=$max_tpt;
                    $paragraf_5_3=$max_tpt_p;
                    $paragraf_5_4=$max_tpt_k;
            $section->addText(''.$nomor++.' Perkembangan Tingkat Pengangguran Terbuka', $fontStyleName );
            $section->addText($deskripsi5,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_5_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_5_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_5_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $categories_5_1 = $tahun_tpt1;
            $series1        = $datay_tpt;
            $series2        = $datay_tpt2;
            $series3        = $datay_tpt3;
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar '.$gambar++.' Perkembangan Tingkat Pengangguran Terbuka (%)',array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
                $chart = $section->addChart($chartType, $categories_5_1, $series1,$style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_5_1, $series2, $xname );
                    $chart->addSeries($categories_5_1, $series3, $judul );
                }
                $section->addTextBreak();
            }
            $categories_5_2 = $label_data_tpt;
            $series_5_2     = $nilai_data_tpt_per;
            $section->addText("Gambar ".$gambar++." Perbandingan Tingkat Pengangguran Terbuka Tahun ".$tahun_pe_max." Antar Kabupaten Di .$xname. (%)",array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
            $chart = $section->addChart('column', $categories_5_2, $series_1_2, $style_2);
            $section->addText('Sumber Data : Badan Pusat Statistik',array('name' => 'Tahoma', 'size' => 10));    
            
            //indeks pembangunan Manusia
            $sql_ipm  ="SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
            $list_ipm = $this->db->query($sql_ipm);
            foreach ($list_ipm->result() as $row_ipm) {
                        $tahun_ipm[]   = $row_ipm->tahun;
                        $nilaiData_ipm[] = (float)$row_ipm->nilai;
                        $nilaiData_ipm1[$row_ipm->tahun] = (float)$row_ipm->nilai;
            }
                    $datay_ipm = $nilaiData_ipm;
                    $tahun_ipm = $tahun_ipm;
            $sql_ipm2  ="SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
            $list_ipm2 = $this->db->query($sql_ipm2);
                    foreach ($list_ipm2->result() as $row_ipm2) {
                        $tahun_ipm2[]   = $row_ipm2->tahun;
                        $nilaiData_ipm2[] = (float)$row_ipm2->nilai;
                        $nilaiData_ipm22[$row_ipm2->tahun] = (float)$row_ipm2->nilai;
                    }
                    $datay_ipm2 = $nilaiData_ipm2;
                    $tahun_ipm2 = $tahun_ipm2;
            $sql_ipm3  ="SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='".$kab."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='".$kab."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
            $list_ipm3 = $this->db->query($sql_ipm3);
            foreach ($list_ipm3->result() as $row_ipm3) {
                        $tahun_ipm3[]   = $row_ipm3->tahun;
                        $nilaiData_ipm3[] = (float)$row_ipm3->nilai;
                        $nilaiData_ipm33[$row_ipm3->tahun] = (float)$row_ipm3->nilai;
                        $sumber_ipm = $row_ipm3->sumber;
                        $idperiode_ipm3[]   = $row_ipm3->id_periode;
            }
                    $periode_kab_ipm_max = max($idperiode_ipm3);
                    $periode_kab_ipm_maxx = max($tahun_ipm3);
                    $periode_kab_ipm_1 = $periode_kab_ipm_maxx-1;
                    $datay_ipm3 = $nilaiData_ipm3;
                    $tahun_ipm3 = $tahun_ipm3;
                    $periode_ipm_tahun=" ".max($tahun_ipm3)."" ;
                    if($nilaiData_ipm33[$periode_kab_ipm_1] > $nilaiData_ipm33[$periode_kab_ipm_maxx]){
                        $max_ipm    ="Indeks pembangunan manusia ". $xnameKab ." pada tahun ".$periode_kab_ipm_maxx." menurun dibandingkan dengan tahun".$periode_kab_ipm_1.". Pada tahun ".$periode_kab_ipm_maxx." indeks pembangunan manusia ". $xnameKab ." adalah sebesar ".number_format($nilaiData_ipm33[$periode_kab_ipm_maxx],2) ."%, sedangkan pada tahun ".$periode_kab_ipm_1." indeks pembangunan manusia tercatat sebesar ".number_format($nilaiData_ipm33[$periode_kab_ipm_1],2)."%. ";
                        if($nilaiData_ipm22[$periode_kab_ipm_maxx]>$nilaiData_ipm33[$periode_kab_ipm_maxx]){ 
                            $max_ipm_p  ="Indeks pembangunan manusia ". $xnameKab ." pada tahun ".$periode_kab_ipm_maxx." berada dibawah capaian ". $xname .". Indeks pembangunan manusia ". $xname ." pada tahun ".$periode_kab_ipm_maxx." adalah sebesar ".number_format($nilaiData_ipm22[$periode_kab_ipm_maxx],2)."%. ";
                        }else{ 
                            $max_ipm_p  ="Indeks pembangunan manusia ". $xnameKab ." pada tahun ".$periode_kab_ipm_maxx." berada diatas capaian ". $xname .". Indeks pembangunan manusia ". $xname ." pada tahun ".$periode_kab_ipm_maxx." adalah sebesar ".number_format($nilaiData_ipm22[$periode_kab_ipm_maxx],2)."%. ";
                        }
                    } else {
                        $max_ipm    ="Indeks pembangunan manusia ". $xnameKab ." pada tahun ".$periode_kab_ipm_maxx." meningkat dibandingkan dengan tahun ".$periode_kab_ipm_1.". Pada ".$periode_kab_ipm_maxx." indeks pembangunan manusia ". $xnameKab ." adalah sebesar ".number_format($nilaiData_ipm33[$periode_kab_ipm_maxx],2) ."%, sedangkan pada tahun ".$periode_kab_ipm_1." indeks pembangunan manusia tercatat sebesar ".number_format($nilaiData_ipm33[$periode_kab_ipm_1],2)."%. ";
                        if($nilaiData_ipm22[$periode_kab_ipm_maxx]>$nilaiData_ipm33[$periode_kab_ipm_maxx]){ 
                            $max_ipm_p  ="Indeks pembangunan manusia ". $xnameKab ." pada tahun ".$periode_kab_ipm_maxx." berada dibawah capaian ".$xname .". Indeks pembangunan manusia ". $xname ." pada tahun  ".$periode_kab_ipm_maxx." adalah sebesar ".number_format($nilaiData_ipm22[$periode_kab_ipm_maxx],2)."%. "; }
                        else{ 
                            $max_ipm_p  ="Indeks pembangunan manusia ". $xnameKab ." pada tahun ".$periode_kab_ipm_maxx." berada diatas capaian ".$xname .". Indeks pembangunan manusia ". $xname ." pada tahun ".$periode_kab_ipm_maxx." adalah sebesar ".number_format($nilaiData_ipm22[$periode_kab_ipm_maxx],2)."%. "; }
                    }
                    
                    if($nilaiData_ipm1[$periode_kab_ipm_maxx] > $nilaiData_ipm33[$periode_kab_ipm_maxx]){ 
                           $max_ipm_k    ="Capaian indeks pembangunan manusia ". $xnameKab ." pada ".$periode_kab_ipm_maxx." berada dibawah nasional. Indeks pembangunan manusia nasional pada ".$periode_kab_ipm_maxx." adalah sebesar ". number_format($nilaiData_ipm1[$periode_kab_ipm_maxx],2)."% "; } 
                    else { $max_ipm_k    ="Capaian indeks pembangunan manusia ". $xnameKab ." pada ".$periode_kab_ipm_maxx." berada diatas nasional. Indeks pembangunan manusia nasional pada ".$periode_kab_ipm_maxx." adalah sebesar ". number_format($nilaiData_ipm1[$periode_kab_ipm_maxx],2)."%"; }
                    
                    $ipm_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='5' AND e.id_periode='".$periode_kab_ipm_max."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='5' AND id_periode='".$periode_kab_ipm_max."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_ipm_per = $this->db->query($ipm_kab);
                    foreach ($list_kab_ipm_per->result() as $row_ipm_kab_per) {
                        $label_ipm[]     = $row_ipm_kab_per->label;
                        $nilai_ipm_per[] = $row_ipm_kab_per->nilai;
                        $posisi_ipm=strpos($row_ipm_kab_per->label, "Kabupaten");
                        if ($posisi_ipm !== FALSE){
                            $label_ipm11=substr( $row_ipm_kab_per->label,0,3)." ".substr( $row_ipm_kab_per->label,10);
                        }else{
                            $label_ipm11=$row_ipm_kab_per->label;
                        }
                        $label_ipm1[]=$label_ipm11;
                    }
                    $label_data_ipm     = $label_ipm1;
                    $nilai_data_ipm_per = $nilai_ipm_per;
            $paragraf_6_2=$max_ipm;        
            $paragraf_6_3=$max_ipm_p;        
            $paragraf_6_4=$max_ipm_k;        
            $section->addText(''.$nomor++.'. Indeks Pembangunan Manusia', $fontStyleName );
            $section->addText($deskripsi6,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_6_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_6_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_6_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));        
            
            $categories_6_1 = $tahun_ipm;
            $series1        = $datay_ipm;
            $series2        = $datay_ipm2;
            $series3        = $datay_ipm3;
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar '.$gambar++.' Perkembangan Indeks Pembangunan Manusia (%)',array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
                $chart = $section->addChart($chartType, $categories_6_1, $series1,$style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_6_1, $series2, $xname );
                    $chart->addSeries($categories_6_1, $series3, $judul );
                }
                $section->addTextBreak();
            }
            $categories_6_2 = $label_data_ipm;
            $series_6_2     = $nilai_data_ipm_per;
            $section->addText("Gambar ".$gambar++." Perbandingan Perkembangan Indeks Pembangunan Manusia Tahun ".$periode_ipm_tahun." Antar Kabupaten Di .$xname. (%)",array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
            $chart = $section->addChart('column', $categories_6_2, $series_6_2, $style_2);
            $section->addText('Sumber Data : Badan Pusat Statistik',array('name' => 'Tahoma', 'size' => 10));  
            
             //Gini Rasio
                    $sql_gr = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_gr = $this->db->query($sql_gr);
                    foreach ($list_gr->result() as $row_gr) {
                        $tahun_gr[]    = $bulan[$row_gr->periode]."-".$row_gr->tahun;
                        $nilaiData_gr[] = (float)$row_gr->nilai;
                        $tahun_pr[]    = $row_gr->id_periode;
                        $idperiode_gr[] = $row_gr->id_periode;
                        
                    }
                    $datay_gr = $nilaiData_gr;
                    $tahun_gr = $tahun_gr;
                    $periode_kab_gr_max = max($idperiode_gr);
                    $sql_gr2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_gr2 = $this->db->query($sql_gr2);
                    foreach ($list_gr2->result() as $row_gr2) {
                        $tahun_gr2[]   = $row_gr2->tahun;
                        $nilaiData_gr2[] = (float)$row_gr2->nilai;
                        $nilaiData_gr22[] = number_format((float)$row_gr2->nilai,2);
                        $nilaiData_gr222[$row_gr2->id_periode] = (float)$row_gr2->nilai;
                    }
                    $datay_gr2 = $nilaiData_gr2;
                    $tahun_gr2 = $tahun_gr2;
                    $sql_gr3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode idperiode,IND.periode
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
                                            select id_periode,nilai,sumber,tahun,periode 
                                            from nilai_indikator 
                                            where (id_indikator='7' AND wilayah='".$kab."')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='7' AND wilayah='".$kab."' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode"; 
                    $list_gr3 = $this->db->query($sql_gr3);
                    foreach ($list_gr3->result() as $row_gr3) {                        
                        $sumber_gr = $row_gr3->sumber_k;
                        $n_gr3 = $row_gr3->nilai_kab;
                        if($n_gr3==0){
                            $nilaiData_kgr3 = '#N/A';
                        }else{
                            $nilaiData_kgr3 = (float)$n_gr3;
                        }
                        $nilaiData_gr3[]=$nilaiData_kgr3;
                        $tahun_gr3[]=$row_gr3->tahun;
                        $periode_gr3[]=$row_gr3->idperiode;
                        $nilaiData_gr33[$row_gr3->id_periode] = (float)$row_gr3->nilai_kab;
                        $tanggal_gr[$row_gr3->id_periode]     = $bulan[$row_gr3->periode]."-".$row_gr3->tahun;
                    }
                    $datay_gr3 = array_reverse($nilaiData_gr3);
                    $tahun_gr3 = $tahun_gr3;
                    $periode_kab_gr_maxx = max($periode_gr3);
                    $periode_kab_gr_1 = $periode_kab_gr_maxx-100;
                    $periode_gr_tahun="Tahun ".$tanggal_gr[$periode_kab_gr_maxx]."" ;
                    if($nilaiData_gr33[$periode_kab_gr_1] > $nilaiData_gr33[$periode_kab_gr_maxx]){                        
                        $max_n_gr    ="Gini Rasio ". $xnameKab ." pada ".$tanggal_gr[$periode_kab_gr_maxx]." menurun dibandingkan dengan ".$tanggal_gr[$periode_kab_gr_1].". Pada ".$tanggal_gr[$periode_kab_gr_maxx]." gini rasio ". $xnameKab ." adalah sebesar ".$nilaiData_gr33[$periode_kab_gr_maxx]."% sedangkan pada ".$tanggal_gr[$periode_kab_gr_1]."  gini rasio tercatat sebesar ".$nilaiData_gr33[$periode_kab_gr_1]."%. ";
                        if($nilaiData_gr222[$periode_kab_gr_maxx]>$nilaiData_gr33[$periode_kab_gr_maxx]){
                            $max_p_gr  ="Capaian gini rasio ". $xnameKab ." pada ".$tanggal_gr[$periode_kab_gr_maxx]." berada dibawah capaian ".$xname.". Gini rasio ".$xname." pada ".$tanggal_gr[$periode_kab_gr_maxx]." adalah sebesar ".$nilaiData_gr222[$periode_kab_gr_maxx]."% ";
                        }else{
                            $max_p_gr  ="Capaian gini rasio ". $xnameKab ." pada ".$tanggal_gr[$periode_kab_gr_maxx]." berada diatas capaian ".$xname.". Gini rasio ".$xname." pada ".$tanggal_gr[$periode_kab_gr_maxx]." adalah sebesar ".$nilaiData_gr222[$periode_kab_gr_maxx]."% ";
                        }
                    } else {
                        $max_n_gr    ="Gini Rasio ". $xnameKab ." pada ".$tanggal_gr[$periode_kab_gr_maxx]." meningkat dibandingkan dengan ".$tanggal_gr[$periode_kab_gr_1].". Pada ".$tanggal_gr[$periode_kab_gr_maxx]." gini rasio ". $xnameKab ." adalah sebesar ".$nilaiData_gr33[$periode_kab_gr_maxx]."% sedangkan pada ".$tanggal_gr[$periode_kab_gr_1]."  gini rasio tercatat sebesar ".$nilaiData_gr33[$periode_kab_gr_1]."%. ";    
                        if($nilaiData_gr222[$periode_kab_gr_maxx]>$nilaiData_gr33[$periode_kab_gr_maxx]){
                            $max_p_gr  ="Capaian gini rasio ". $xnameKab ." pada ".$tanggal_gr[$periode_kab_gr_maxx]." berada dibawah capaian ".$xname.". Gini rasio ".$xname." pada ".$tanggal_gr[$periode_kab_gr_maxx]." adalah sebesar ".$nilaiData_gr222[$periode_kab_gr_maxx]."% ";
                        }else{
                            $max_p_gr  ="Capaian gini rasio ". $xnameKab ." pada ".$tanggal_gr[$periode_kab_gr_maxx]." berada diatas capaian ".$xname.". Gini rasio ".$xname." pada ".$tanggal_gr[$periode_kab_gr_maxx]." adalah sebesar ".$nilaiData_gr222[$periode_kab_gr_maxx]."% ";
                        }
                    }
                    $gr_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='7' AND e.id_periode='".$idperiode_gr[4]."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='7' AND id_periode='".$idperiode_gr[4]."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_gr_per = $this->db->query($gr_kab);
                    foreach ($list_kab_gr_per->result() as $row_gr_kab_per) {
                        $label_gr[]     = $row_gr_kab_per->label;
                        $nilai_gr_per[] = $row_gr_kab_per->nilai;
                        $posisi_gr=strpos($row_gr_kab_per->label, "Kabupaten");
                        if ($posisi_gr !== FALSE){
                            $label_gr11=substr( $row_gr_kab_per->label,0,3)." ".substr( $row_gr_kab_per->label,10);
                        }else{
                            $label_gr11=$row_gr_kab_per->label;
                        }
                        $label_gr1[]=$label_gr11;
                    }
                    $label_data_gr     = $label_gr1;
                    $nilai_data_gr_per = $nilai_gr_per;
            
                    $paragraf_7_2=$max_n_gr;
                    $paragraf_7_3=$max_p_gr;
                    $section->addText(''.$nomor++.'. Gini Rasio', $fontStyleName );
            $section->addText($deskripsi7,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_7_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_7_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            //$section->addText($paragraf_7_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));        
            $categories_7_1 = $tahun_gr;
            $series1        = $datay_gr;
            $series2        = $datay_gr2;
            $series3        = $datay_gr3;
            
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar '.$gambar++.' Perkembangan Gini Rasio',array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
                $chart = $section->addChart($chartType, $categories_7_1, $series1,$style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_7_1, $series2, $xname );
                    $chart->addSeries($categories_7_1, $series3, $judul );
                }
                $section->addTextBreak();
            }
            $categories_7_2 = $label_data_gr;
            $series_7_2     = $nilai_data_gr_per;
            $section->addText("Gambar ".$gambar++." Perbandingan Perkembangan Gini Rasio Tahun ".$idperiode_gr[4]." Antar Kabupaten Di .$xname. (%)",array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
            $chart = $section->addChart('column', $categories_7_2, $series_7_2, $style_2);
            $section->addText('Sumber Data : Badan Pusat Statistik',array('name' => 'Tahoma', 'size' => 10));  
           
            //Angka Harapan Hidup
            $sql_ahh = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ahh = $this->db->query($sql_ahh);
                    foreach ($list_ahh->result() as $row_ahh) {
                        $tahun_ahh[]   = $row_ahh->tahun;
                        $nilaiData_ahh[] = (float)$row_ahh->nilai;
                        $nilaiData_ahh1[$row_ahh->tahun] = (float)$row_ahh->nilai;
                    }
                    $datay_ahh = $nilaiData_ahh;
                    $tahun_ahh = $tahun_ahh;
                    $sql_ahh2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ahh2 = $this->db->query($sql_ahh2);
                    foreach ($list_ahh2->result() as $row_ahh2) {
                        $tahun_ahh2[]   = $row_ahh2->tahun;
                        $nilaiData_ahh2[] = (float)$row_ahh2->nilai;
                        $nilaiData_ahh22[$row_ahh2->tahun] = (float)$row_ahh2->nilai;
                    }
                    $datay_ahh2 = $nilaiData_ahh2;
                    $tahun_ahh2 = $tahun_ahh2;
                    
                    $sql_ahh33 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode idperiode,IND.periode
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='8' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='8' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun,periode 
                                            from nilai_indikator 
                                            where (id_indikator='8' AND wilayah='".$kab."')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='8' AND wilayah='".$kab."' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode"; 
                    
                    $sql_ahh3 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='".$kab."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='".$kab."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    //print_r($sql_ahh3);exit();
                    $list_ahh3 = $this->db->query($sql_ahh3);
                    foreach ($list_ahh3->result() as $row_ahh3) {
                        $tahun_ahh3[]   = $row_ahh3->tahun;
                        $nilaiData_ahh3[] = (float)$row_ahh3->nilai;
                        $nilaiData_ahh33[$row_ahh3->tahun] = (float)$row_ahh3->nilai;
                        $sumber_ahh = $row_ahh3->sumber;
                        $idperiode_ahh_max[] = $row_ahh3->id_periode;
                    }
                    $periode_kab_ahh_max=max($idperiode_ahh_max);
                    $periode_kab_ahh_maxx=max($tahun_ahh3);
                    $periode_kab_ahh_1=$periode_kab_ahh_maxx-1;
                    $datay_ahh3 = $nilaiData_ahh3;
                    $tahun_ahh3 = $tahun_ahh3;
                    $periode_ahh_tahun="".$periode_kab_ahh_maxx."" ;
                    
                    if($nilaiData_ahh33[$periode_kab_ahh_1] > $nilaiData_ahh33[$periode_kab_ahh_maxx]){
                        $max_n_ahh    ="Angka Harapan Hidup ". $xnameKab ." pada tahun ".$periode_kab_ahh_maxx." menurun dibandingkan dengan tahun ".$periode_kab_ahh_1.". Pada tahun ".$periode_kab_ahh_maxx." Angka Harapan Hidup ". $xnameKab ." adalah sebesar ".number_format($nilaiData_ahh33[$periode_kab_ahh_maxx],2) ." tahun, sedangkan pada tahun ".$periode_kab_ahh_1." Angka Harapan Hidup tercatat sebesar ".number_format($nilaiData_ahh33[$periode_kab_ahh_1],2)." tahun. ";
                        if($nilaiData_ahh22[$periode_kab_ahh_maxx]>$nilaiData_ahh33[$periode_kab_ahh_maxx]){
                            $max_p_gr  ="Angka Harapan Hidup ". $xnameKab ." pada tahun ".$periode_kab_ahh_maxx." berada dibawah capaian ". $xname .". Angka Harapan Hidup ". $xname ." pada tahun ".$periode_kab_ahh_maxx." adalah sebesar ".number_format($nilaiData_ahh22[$periode_kab_ahh_maxx],2)." tahun. ";
                        }else{
                            $max_p_gr  ="Angka Harapan Hidup ". $xnameKab ." pada tahun ".$periode_kab_ahh_maxx." berada diatas capaian ". $xname .". Angka Harapan Hidup ". $xname ." pada tahun ".$periode_kab_ahh_maxx." adalah sebesar ".number_format($nilaiData_ahh22[$periode_kab_ahh_maxx],2)." tahun. ";
                        }
                    } else {
                        $max_n_ahh    ="Angka Harapan Hidup ". $xnameKab ." pada tahun ".$periode_kab_ahh_maxx." meningkat dibandingkan dengan tahun ".$periode_kab_ahh_1.". Pada ".$periode_kab_ahh_maxx." Angka Harapan Hidup ". $xnameKab ." adalah sebesar ".number_format($nilaiData_ahh33[$periode_kab_ahh_maxx],2) ." tahun, sedangkan pada tahun ".$periode_kab_ipm_1." Angka Harapan Hidup tercatat sebesar ".number_format($nilaiData_ahh33[$periode_kab_ipm_1],2)." tahun. ";
                        if($nilaiData_ahh22[$periode_kab_ahh_maxx]>$nilaiData_ahh33[$periode_kab_ahh_maxx]){
                            $max_p_ahh  ="Angka Harapan Hidup ". $xnameKab ." pada tahun ".$periode_kab_ahh_maxx." berada dibawah capaian ". $xname .". Angka Harapan Hidup ". $xname ." pada tahun ".$periode_kab_ahh_maxx." adalah sebesar ".number_format($nilaiData_ahh22[$periode_kab_ahh_maxx],2)." tahun. ";
                        }else{
                            $max_p_ahh  ="Angka Harapan Hidup ". $xnameKab ." pada tahun ".$periode_kab_ahh_maxx." berada diatas capaian ". $xname .". Angka Harapan Hidup ". $xname ." pada tahun ".$periode_kab_ahh_maxx." adalah sebesar ".number_format($nilaiData_ahh22[$periode_kab_ahh_maxx],2)." tahun. ";
                        }
                    }
                    if($nilaiData_ahh1[$periode_kab_ahh_maxx] > $nilaiData_ahh33[$periode_kab_ahh_maxx]){
                        $max_k_ahh    =" Capaian Angka Harapan Hidup ". $xnameKab ." pada tahun ".$periode_kab_ahh_maxx." berada dibawah nasional. Angka Harapan Hidup nasional pada tahun ".$periode_kab_ahh_maxx." adalah sebesar ". number_format($nilaiData_ahh1[$periode_kab_ahh_maxx],2)." tahun ";
                    } else {
                        $max_k_ahh    =" Capaian Angka Harapan Hidup ". $xnameKab ." pada tahun ".$periode_kab_ahh_maxx." berada diatas nasional. Angka Harapan Hidup nasional pada tahun ".$periode_kab_ahh_maxx." adalah sebesar ". number_format($nilaiData_ahh1[$periode_kab_ahh_maxx],2)." tahun";
                    }
                    $ahh_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='8' AND e.id_periode='".$periode_kab_ahh_1."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='8' AND id_periode='".$periode_kab_ahh_1."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_ahh_per = $this->db->query($ahh_kab);
                    foreach ($list_kab_ahh_per->result() as $row_ahh_kab_per) {
                        $label_ahh[]     = $row_ahh_kab_per->label;
                        $nilai_ahh_per[] = $row_ahh_kab_per->nilai;
                        $posisi_ahh=strpos($row_ahh_kab_per->label, "Kabupaten");
                        if ($posisi_ahh !== FALSE){
                            $label_ahh11=substr( $row_ahh_kab_per->label,0,3)." ".substr( $row_ahh_kab_per->label,10);
                        }else{
                            $label_ahh11=$row_ahh_kab_per->label;
                        }
                        $label_ahh1[]=$label_ahh11;
                    }
                    $label_data_ahh     = $label_ahh1;
                    $nilai_data_ahh_per = $nilai_ahh_per;
                    
                    
            $paragraf_8_2=$max_n_ahh;
            $paragraf_8_3=$max_p_ahh;
            $paragraf_8_4=$max_k_ahh;
                    
            $section->addText(''.$nomor++.'. Angka Harapan Hidup', $fontStyleName );
            $section->addText($deskripsi8,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_8_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_8_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_8_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));        
            $categories_8_1     = $tahun_ahh;
            $series_8_1        = $datay_ahh;
            $series_8_2        = $datay_ahh2;
            $series_8_3        = $datay_ahh3;
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar '.$gambar++.' Perkembangan Angka Harapan Hidup',array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
                $chart = $section->addChart($chartType, $categories_8_1, $series_8_1,$style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_8_1, $series_8_2, $xname );
                    $chart->addSeries($categories_8_1, $series_8_3, $judul );
                }
                $section->addTextBreak();
            }
            $categories_8_2 = $label_data_ahh;
            $series_8_2     = $nilai_data_ahh_per;
            $section->addText("Gambar ".$gambar++." Perbandingan Perkembangan Angka Harapan Hidup Tahun ".$periode_ahh_tahun." Antar Kabupaten/Kota Di $xname (%)",array('name' => 'Arial', 'size' => 10),array('alignment'=>'center'));
            $chart = $section->addChart('column', $categories_8_2, $series_8_2, $style_2);
            $section->addText('Sumber Data : Badan Pusat Statistik',array('name' => 'Tahoma', 'size' => 10));  
            
            //rata-rata lama sekolah
                    $sql_rls = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_rls = $this->db->query($sql_rls);
                    foreach ($list_rls->result() as $row_rls) {
                        $tahun_rls[]   = $row_rls->tahun;
                        $nilaiData_rls[] = (float)$row_rls->nilai;
                        $nilaiData_rls1[$row_rls->tahun] = (float)$row_rls->nilai;
                    }
                    $datay_rls = $nilaiData_rls;
                    $tahun_rls = $tahun_rls;
                    $sql_rls2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_rls2 = $this->db->query($sql_rls2);
                    foreach ($list_rls2->result() as $row_rls2) {
                        $tahun_rls2[]   = $row_rls2->tahun;
                        $nilaiData_rls2[] = (float)$row_rls2->nilai;
                        $nilaiData_rls22[$row_rls2->tahun] = (float)$row_rls2->nilai;
                    }
                    $datay_rls2 = $nilaiData_rls2;
                    $tahun_rls2 = $tahun_rls2;
                    $sql_rls3 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='".$kab."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='".$kab."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_rls3 = $this->db->query($sql_rls3);
                    foreach ($list_rls3->result() as $row_rls3) {
                        $tahun_rls3[]   = $row_rls3->tahun;
                        $nilaiData_rls3[] = (float)$row_rls3->nilai;
                        $nilaiData_rls33[$row_rls3->tahun] = (float)$row_rls3->nilai;
                        $sumber_rls=$row_rls3->sumber;
                        $idperiode_rls[]=$row_rls3->id_periode;
                    }                    
                    $datay_rls3 = $nilaiData_rls3;
                    $tahun_rls3 = $tahun_rls3;                    
                    $periode_kab_rls_max=max($idperiode_rls);
                    $tahun_kab_rls_max=max($tahun_rls3);
                    $tahun_kab_rls_1=$tahun_kab_rls_max-1;
                    $periode_rls_tahun=$tahun_kab_rls_max;
                    
                    if($nilaiData_rls33[$tahun_kab_rls_1] > $nilaiData_rls33[$tahun_kab_rls_max]){
                        $max_n_rls    ="Rata-rata lama sekolah ". $xnameKab ." pada tahun ".$tahun_kab_rls_max." menurun dibandingkan dengan tahun ".$tahun_kab_rls_1.". Pada tahun ".$tahun_kab_rls_max." Rata-rata Lama Sekolah ". $xnameKab ." adalah sebesar ".number_format($nilaiData_rls33[$tahun_kab_rls_max],2) ." tahun, sedangkan pada tahun ".$tahun_kab_rls_1." Rata-rata Lama Sekolah tercatat sebesar ".number_format($nilaiData_rls33[$tahun_kab_rls_1],2)." tahun. ";
                        if($nilaiData_ahh22[$tahun_kab_rls_max]>$nilaiData_ahh33[$tahun_kab_rls_max]){
                            $max_p_rls  ="Rata-rata Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_rls_max." berada dibawah capaian ". $xname .". Rata-rata Lama Sekolah ". $xname ." pada tahun ".$tahun_kab_rls_max." adalah sebesar ".number_format($nilaiData_rls22[$tahun_kab_rls_max],2)." tahun. ";
                        }else{
                            $max_p_rls  ="Rata-rata Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_rls_max." berada diatas capaian ". $xname .". Rata-rata Lama Sekolah ". $xname ." pada tahun ".$tahun_kab_rls_max." adalah sebesar ".number_format($nilaiData_rls22[$tahun_kab_rls_max],2)." tahun. ";
                        }
                    } else {
                        $max_n_rls    ="Rata-rata Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_rls_max." meningkat dibandingkan dengan ".$tahun_kab_rls_1.". Pada tahun ".$tahun_kab_rls_max." Rata-rata Lama Sekolah ". $xnameKab ." adalah sebesar ".number_format($nilaiData_rls33[$tahun_kab_rls_max],2) ." tahun, sedangkan pada tahun ".$tahun_kab_rls_1." Rata-rata Lama Sekolah tercatat sebesar ".number_format($nilaiData_rls33[$tahun_kab_rls_1],2)." tahun. ";
                        if($nilaiData_ahh22[$tahun_kab_rls_max]>$nilaiData_ahh33[$tahun_kab_rls_max]){
                            $max_p_rls  ="Rata-rata Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_rls_max." berada dibawah capaian ". $xname .". Rata-rata Lama Sekolah ". $xname ." pada tahun ".$tahun_kab_rls_max." adalah sebesar ".number_format($nilaiData_rls22[$tahun_kab_rls_max],2)." tahun. ";
                        }else{
                            $max_p_rls  ="Rata-rata Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_rls_max." berada diatas capaian ". $xname .". Rata-rata Lama Sekolah ". $xname ." pada tahun ".$tahun_kab_rls_max." adalah sebesar ".number_format($nilaiData_rls22[$tahun_kab_rls_max],2)." tahun. ";
                        }
                    }
                    if($nilaiData_rls1[$tahun_kab_rls_max] > $nilaiData_rls33[$tahun_kab_rls_max]){
                        $max_k_rls    =" Capaian Rata-rata Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_rls_max." berada dibawah nasional. Rata-rata Lama Sekolah nasional pada tahun ".$tahun_kab_rls_max." adalah sebesar ". number_format($nilaiData_rls1[$tahun_kab_rls_max],2)." tahun ";
                    } else {
                        $max_k_rls    =" Capaian Rata-rata Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_rls_max." berada diatas nasional. Rata-rata Lama Sekolah nasional pada tahun ".$tahun_kab_rls_max." adalah sebesar ". number_format($nilaiData_rls1[$tahun_kab_rls_max],2)." tahun";
                    }
                    $rls_kab="select p.nama_kabupaten as label, e.*  from kabupaten p join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='9' AND e.id_periode='".$periode_kab_rls_max."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='9' AND id_periode='".$periode_kab_rls_max."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_rls_per = $this->db->query($rls_kab);
                    foreach ($list_kab_rls_per->result() as $row_rls_kab_per) {
                        $label_rls[]     = $row_rls_kab_per->label;
                        $nilai_rls_per[] = $row_rls_kab_per->nilai;
                        $posisi_rls=strpos($row_rls_kab_per->label, "Kabupaten");
                        if ($posisi_rls !== FALSE){
                            $label_rls11=substr( $row_rls_kab_per->label,0,3)." ".substr( $row_rls_kab_per->label,10);
                        }else{
                            $label_rls11=$row_rls_kab_per->label;
                        }
                        $label_rls1[]=$label_rls11;
                    }
                    $label_data_rls     = $label_rls1;
                    $nilai_data_rls_per = $nilai_rls_per;
                    
                    $paragraf_9_2=$max_n_rls;
                    $paragraf_9_3=$max_p_rls;
                    $paragraf_9_4=$max_k_rls;
                    
                    
            $section->addText(''.$nomor++.'. Rata-rata Lama Sekolah', $fontStyleName );
            $section->addText($deskripsi9,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_9_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_9_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_9_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            
            
            
            
            //                    //harapan lama sekolah
                    $sql_hls = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_hls = $this->db->query($sql_hls);
                    foreach ($list_hls->result() as $row_hls) {
                        $tahun_hls[]   = $row_hls->tahun;
                        $nilaiData_hls[] = (float)$row_hls->nilai;
                        $nilaiData_hls1[$row_hls->tahun] = (float)$row_hls->nilai;
                    }
                    $datay_hls = $nilaiData_hls;
                    $tahun_hls = $tahun_hls;
                    $sql_hls2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_hls2 = $this->db->query($sql_hls2);
                    foreach ($list_hls2->result() as $row_hls2) {
                        $tahun_hls2[]   = $row_hls2->tahun;
                        $nilaiData_hls2[] = (float)$row_hls2->nilai;
                        $nilaiData_hls22[$row_hls2->tahun] = (float)$row_hls2->nilai;
                    }
                    $datay_hls2 = $nilaiData_hls2;
                    $tahun_hls2 = $tahun_hls2;
                    $sql_hls3 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='".$kab."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='".$kab."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 

                    $list_hls3 = $this->db->query($sql_hls3);
                    foreach ($list_hls3->result() as $row_hls3) {
                        $tahun_hls3[]   = $row_hls3->tahun;
                        $nilaiData_hls3[] = (float)$row_hls3->nilai;
                        $nilaiData_hls33[$row_hls3->tahun] = (float)$row_hls3->nilai;
                        $sumber_hls=$row_hls3->sumber;
                        $idperiode_hls[]=$row_hls3->id_periode;
                    }
                    $periode_kab_hls_max=max($idperiode_hls);
                    $tahun_kab_hls_max=max($tahun_hls3);
                    $tahun_kab_hls_1=$tahun_kab_hls_max-1;
                    $datay_hls3 = $nilaiData_hls3;
                    $tahun_hls3 = $tahun_hls3;
                    $periode_hls_tahun=$tahun_kab_hls_max;
                    
                    if($nilaiData_hls33[$tahun_kab_hls_1] > $nilaiData_hls33[$tahun_kab_hls_max]){
                        $max_n_hls    ="Harapan Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_hls_max." menurun dibandingkan dengan tahun ".$tahun_kab_hls_1.". Pada tahun  ".$tahun_kab_hls_max." Harapan Lama Sekolah ". $xnameKab ." adalah sebesar ".number_format($nilaiData_hls33[$tahun_kab_hls_max],2) ." tahun, sedangkan pada tahun ".$tahun_hls[4]." Harapan Lama Sekolah tercatat sebesar ".number_format($nilaiData_hls3[4],2)." tahun. ";
                        if($nilaiData_hls22[$tahun_kab_hls_max]>$nilaiData_hls32[$tahun_kab_hls_max]){
                            $max_p_hls  ="Harapan Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_hls_max." berada dibawah capaian ". $xname .". Harapan Lama Sekolah ". $xname ." pada tahun ".$tahun_kab_hls_max." adalah sebesar ".number_format($nilaiData_hls22[$tahun_kab_hls_max],2)." tahun. ";
                        }else{
                            $max_p_hls  ="Harapan Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_hls_max." berada diatas capaian ". $xname .". Harapan Lama Sekolah ". $xname ." pada tahun ".$tahun_kab_hls_max." adalah sebesar ".number_format($nilaiData_hls22[$tahun_kab_hls_max],2)." tahun. ";
                        }
                    } else {
                        $max_n_hls    ="Harapan Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_hls_max." meningkat dibandingkan dengan tahun ".$tahun_kab_hls_1.". Pada tahun ".$tahun_kab_hls_max." Harapan Lama Sekolah ". $xnameKab ." adalah sebesar ".number_format($nilaiData_hls33[$tahun_kab_hls_max],2) ." tahun, sedangkan pada tahun ".$tahun_hls[4]." Harapan Lama Sekolah tercatat sebesar ".number_format($nilaiData_hls3[4],2)." tahun. ";
                        if($nilaiData_hls22[$tahun_kab_hls_max]>$nilaiData_hls32[$tahun_kab_hls_max]){
                            $max_p_hls  ="Harapan Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_hls_max." berada dibawah capaian ". $xname .". Harapan Lama Sekolah ". $xname ." pada tahun ".$tahun_kab_hls_max." adalah sebesar ".number_format($nilaiData_hls22[$tahun_kab_hls_max],2)." tahun. ";
                        }else{
                            $max_p_hls  ="Harapan Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_hls_max." berada diatas capaian ". $xname .". Harapan Lama Sekolah ". $xname ." pada tahun ".$tahun_kab_hls_max." adalah sebesar ".number_format($nilaiData_hls22[$tahun_kab_hls_max],2)." tahun. ";
                        }
                    }
                    if($nilaiData_hls1[$tahun_kab_hls_max] > $nilaiData_hls33[$tahun_kab_hls_max]){
                        $max_k_hls    =" Capaian Harapan Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_hls_max." berada dibawah nasional. Harapan Lama Sekolah nasional pada tahun ".$tahun_kab_hls_max." adalah sebesar ". number_format($nilaiData_hls1[$tahun_kab_hls_max],2)." tahun ";
                    } else {
                        $max_k_hls    =" Capaian Harapan Lama Sekolah ". $xnameKab ." pada tahun ".$tahun_kab_hls_max." berada diatas nasional. Harapan Lama Sekolah nasional pada tahun ".$tahun_kab_hls_max." adalah sebesar ". number_format($nilaiData_hls1[$tahun_kab_hls_max],2)." tahun";
                    }
                    $hls_kab="select p.nama_kabupaten as label, e.*  from kabupaten p join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='10' AND e.id_periode='".$periode_kab_hls_max."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='10' AND id_periode='".$periode_kab_hls_max."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_hls_per = $this->db->query($hls_kab);
                    foreach ($list_kab_hls_per->result() as $row_hls_kab_per) {
                        $label_hls[]     = $row_hls_kab_per->label;
                        $nilai_hls_per[] = $row_hls_kab_per->nilai;
                        $posisi_hls=strpos($row_hls_kab_per->label, "Kabupaten");
                        if ($posisi_hls !== FALSE){
                            $label_hls11=substr( $row_hls_kab_per->label,0,3)." ".substr( $row_hls_kab_per->label,10);
                        }else{
                            $label_hls11=$row_hls_kab_per->label;
                        }
                        $label_hls1[]=$label_hls11;
                    }
                    $label_data_hls     = $label_hls1;
                    $nilai_data_hls_per = $nilai_hls_per;
            
            $paragraf_10_2=$max_n_hls;
            $paragraf_10_3=$max_p_hls;
            $paragraf_10_4=$max_k_hls;
            $section->addText(''.$nomor++.'. Harapan Lama Sekolah', $fontStyleName );
            $section->addText($deskripsi10,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_10_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_10_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_10_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            
            
            
            
                                //pengeluaran per kapita 
                    $sql_ppk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ppk = $this->db->query($sql_ppk);
                    foreach ($list_ppk->result() as $row_ppk) {
                        $tahun_ppk[]   = $row_ppk->tahun;
                        $nilaiData_ppk[] = (float)$row_ppk->nilai;
                        $nilaiData_ppk1[$row_ppk->tahun] = (float)$row_ppk->nilai;
                        $nilaiData_ppk11[] = (float)$row_ppk->nilai/1000000;
                    }
                    $datay_ppk = $nilaiData_ppk11;
                    $tahun_ppk = $tahun_ppk;
                    $sql_ppk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ppk2 = $this->db->query($sql_ppk2);
                    foreach ($list_ppk2->result() as $row_ppk2) {
                        $tahun_ppk2[]                      = $row_ppk2->tahun;
                        $nilaiData_ppk2[]                  = (float)$row_ppk2->nilai;
                        $nilaiData_ppk22[$row_ppk2->tahun] = (float)$row_ppk2->nilai;
                        $nilaiData_ppk222[]                 = (float)$row_ppk2->nilai/1000000;
                    }
                    $datay_ppk2 = $nilaiData_ppk222;
                    $tahun_ppk2 = $tahun_ppk2;
                    $sql_ppk3 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='".$kab."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='".$kab."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_ppk3 = $this->db->query($sql_ppk3);
                    foreach ($list_ppk3->result() as $row_ppk3) {
                        $tahun_ppk3[]   = $row_ppk3->tahun;
                        $nilaiData_ppk3[] = (float)$row_ppk3->nilai;
                        $nilaiData_ppk33[$row_ppk3->tahun] = (float)$row_ppk3->nilai;
                        $nilaiData_ppk333[] = (float)$row_ppk3->nilai/1000000;
                        $sumber_ppk=$row_ppk3->sumber;
                        $idperiode_ppk[]=$row_ppk3->id_periode;
                    }
                    $periode_kab_ppk_max=max($idperiode_ppk);
                    $tahun_kab_ppk_max=max($tahun_ppk3);
                    $tahun_kab_ppk_1=$tahun_kab_ppk_max-1;
                    $datay_ppk3 = $nilaiData_ppk33;
                    $tahun_ppk3 = $tahun_ppk3;
                    $periode_ppk_tahun=$tahun_kab_ppk_max;
                    
                    if($nilaiData_ppk33[$tahun_kab_ppk_1] > $nilaiData_ppk33[$tahun_kab_ppk_max]){
                        $max_n_ppk    ="Pengeluaran Perkapita ". $xnameKab ." pada tahun ".$tahun_kab_ppk_max." menurun dibandingkan dengan tahun ".$tahun_kab_ppk_1.". Pada tahun ".$tahun_kab_ppk_max." Pengeluaran Perkapita ". $xnameKab ." adalah sebesar Rp ".number_format($nilaiData_ppk33[$tahun_kab_ppk_max],0) .", sedangkan pada tahun ".$tahun_kab_ppk_1." Pengeluaran Perkapita tercatat sebesar Rp ".number_format($nilaiData_ppk33[$tahun_kab_ppk_1],0).". ";
                        if($nilaiData_ppk22[$tahun_kab_ppk_max]>$nilaiData_ppk33[$tahun_kab_ppk_max]){
                            $max_p_ppk  ="Pengeluaran Perkapita ". $xnameKab ." pada tahun ".$tahun_kab_ppk_max." berada dibawah capaian ". $xname .". Pengeluaran Perkapita ". $xname ." pada tahun ".$tahun_kab_ppk_max." adalah sebesar Rp ".number_format($nilaiData_ppk22[$tahun_kab_ppk_max],0).". ";
                        }else{
                            $max_p_ppk  ="Pengeluaran Perkapita ". $xnameKab ." pada tahun ".$tahun_kab_ppk_max." berada diatas capaian ". $xname .". Pengeluaran Perkapita ". $xname ." pada tahun ".$tahun_kab_ppk_max." adalah sebesar Rp ".number_format($nilaiData_ppk22[$tahun_kab_ppk_max],0).". ";
                        }
                    } else {
                        $max_n_ppk    ="Pengeluaran Perkapita ". $xnameKab ." pada tahun ".$tahun_kab_ppk_max." meningkat dibandingkan dengan tahun ".$tahun_kab_ppk_1.". Pada tahun ".$tahun_kab_ppk_max." Pengeluaran Perkapita ". $xnameKab ." adalah sebesar Rp ".number_format($nilaiData_ppk33[$tahun_kab_ppk_max],0) .", sedangkan pada tahun ".$tahun_kab_ppk_1." Pengeluaran Perkapita tercatat sebesar Rp ".number_format($nilaiData_ppk33[$tahun_kab_ppk_1],0).". ";
                        if($nilaiData_ppk22[$tahun_kab_ppk_max]>$nilaiData_ppk33[$tahun_kab_ppk_max]){
                            $max_p_ppk  ="Pengeluaran Perkapita ". $xnameKab ." pada tahun ".$tahun_kab_ppk_max." berada dibawah capaian ". $xname .". Pengeluaran Perkapita ". $xname ." pada tahun ".$tahun_kab_ppk_max." adalah sebesar Rp ".number_format($nilaiData_ppk22[$tahun_kab_ppk_max],0).". ";
                        }else{
                            $max_p_ppk  ="Pengeluaran Perkapita ". $xnameKab ." pada tahun ".$tahun_kab_ppk_max." berada diatas capaian ". $xname .". Pengeluaran Perkapita ". $xname ." pada tahun ".$tahun_kab_ppk_max." adalah sebesar Rp ".number_format($nilaiData_ppk22[$tahun_kab_ppk_max],0).". ";
                        }
                    }
                    if($nilaiData_ppk1[$tahun_kab_ppk_max] > $nilaiData_ppk33[$tahun_kab_ppk_max]){
                        $max_k_ppk    =" Capaian Pengeluaran Perkapita ". $xnameKab ." pada tahun ".$tahun_kab_ppk_max." berada dibawah nasional. Pengeluaran Perkapita nasional pada tahun ".$tahun_kab_ppk_max." adalah sebesar Rp ". number_format($nilaiData_ppk1[$tahun_kab_ppk_max],0).". ";
                    } else {
                        $max_k_ppk    =" Capaian Pengeluaran Perkapita ". $xnameKab ." pada tahun ".$tahun_kab_ppk_max." berada diatas nasional. Pengeluaran Perkapita nasional pada tahun ".$tahun_kab_ppk_max." adalah sebesar Rp ". number_format($nilaiData_ppk1[$tahun_kab_ppk_max],0).". ";
                    }
                    $ppk_kab="select p.nama_kabupaten as label, e.*  from kabupaten p join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='11' AND e.id_periode='".$periode_kab_ppk_max."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='11' AND id_periode='".$periode_kab_ppk_max."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_ppk_per = $this->db->query($ppk_kab);
                    foreach ($list_kab_ppk_per->result() as $row_ppk_kab_per) {
                        $label_ppk[]     = $row_ppk_kab_per->label;
                        $nilai_ppk_per[] = $row_ppk_kab_per->nilai;
                        $posisi_ppk=strpos($row_ppk_kab_per->label, "Kabupaten");
                        if ($posisi_ppk !== FALSE){
                            $label_ppk11=substr( $row_ppk_kab_per->label,0,3)." ".substr( $row_ppk_kab_per->label,10);
                        }else{
                            $label_ppk11=$row_ppk_kab_per->label;
                        }
                        $label_ppk1[]=$label_ppk11;
                    }
                    $label_data_ppk     = $label_ppk1;
                    $nilai_data_ppk_per = $nilai_ppk_per;
                    
                    $paragraf_11_2=$max_n_ppk;
            $paragraf_11_3=$max_p_ppk;
            $paragraf_11_4=$max_k_ppk;
            $section->addText(''.$nomor++.'. Pengeluaran per Kapita', $fontStyleName );
            $section->addText($deskripsi11,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_11_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_11_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_11_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            
                    //                    //Tingkat Kemiskinan
                    $sql_tk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_tk = $this->db->query($sql_tk);
                    foreach ($list_tk->result() as $row_tk) {
                        $tahun_tk[]    = $bulan[$row_tk->periode]."-".$row_tk->tahun;
                        $periode_kab_tk_max[]   = $row_tk->id_periode;
                        $nilaiData_tk[] = (float)$row_tk->nilai;
                        $nilaiData_tk1[$row_tk->id_periode] = (float)$row_tk->nilai;
                    }
                    $datay_tk = $nilaiData_tk;
                    $tahun_tk = $tahun_tk;
                    $sql_tk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_tk2 = $this->db->query($sql_tk2);
                    foreach ($list_tk2->result() as $row_tk2) {
                        $tahun_tk2[]   = $row_tk2->tahun;
                        $nilaiData_tk2[] = (float)$row_tk2->nilai;
                        $nilaiData_tk22[$row_tk2->id_periode] = (float)$row_tk2->nilai;
                    }
                    $datay_tk2 = $nilaiData_tk2;
                    $tahun_tk2 = $tahun_tk2;
                    
                    $sql_tk3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab, IFNULL(IND.sumber,0) sumber_k,IND.id_periode idperiode,IND.tahun,IND.periode
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='36' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='36' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun,periode 
                                            from nilai_indikator 
                                            where (id_indikator='36' AND wilayah='".$kab."')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='36' AND wilayah='".$kab."' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode"; 
                    $list_tk3 = $this->db->query($sql_tk3);
                    $nilaiData_tk3[] = '';
                    foreach ($list_tk3->result() as $row_tk3) {
                        $n_tk3 = $row_tk3->nilai_kab;
                        if($n_tk3==0){
                            $nilaiData_ktk3 = '-';
                        }else{
                            $nilaiData_ktk3 = (float)$n_tk3;
                        }
                        $nilaiData_tk3[] =$nilaiData_ktk3;
                        $sumber_tk       = $row_tk3->sumber_k;   
                        $periode_tk3[]   =$row_tk3->idperiode;
                        $data_tk3[$row_tk3->idperiode]   = $nilaiData_ktk3;
                        $per_tk3[$row_tk3->idperiode]    = $bulan[$row_tk3->periode]." ".$row_tk3->tahun;
                    }
                    $datay_tk3 = array_reverse($nilaiData_tk3);
                    $tahun_kab_tk_max=max($periode_tk3);
                    $tahun_kab_tk_1=$tahun_kab_tk_max-100;
                    $tahun_tk3 = $tahun_tk3;
                    $periode_tk_tahun =$per_tk3[$tahun_kab_tk_max];
                    if($data_tk3[$tahun_kab_tk_1] > $data_tk3[$tahun_kab_tk_max]){
                        $max_n_tk    ="Tingkat Kemiskinan ". $xnameKab ." pada ".$per_tk3[$tahun_kab_tk_max]." menurun dibandingkan dengan ".$per_tk3[$tahun_kab_tk_1].". Pada ".$per_tk3[$tahun_kab_tk_max]." Tingkat Kemiskinan ". $xnameKab ." adalah sebesar ".$data_tk3[$tahun_kab_tk_max]."%, sedangkan pada tahun ".$per_tk3[$tahun_kab_tk_1]." Tingkat Kemiskinan tercatat sebesar ".$data_tk3[$tahun_kab_tk_1]."%. ";
                        if($nilaiData_tk22[$tahun_kab_tk_max]>$data_tk3[$tahun_kab_tk_max]){
                            $max_p_tk  ="Tingkat Kemiskinan ". $xnameKab ." pada ".$per_tk3[$tahun_kab_tk_max]." berada dibawah capaian ". $xname .". Tingkat Kemiskinan ". $xname ." pada ".$per_tk3[$tahun_kab_tk_max]." adalah sebesar ".$nilaiData_tk22[$tahun_kab_tk_max]."%. ";
                        }else{
                            $max_p_tk  ="Tingkat Kemiskinan ". $xnameKab ." pada ".$per_tk3[$tahun_kab_tk_max]." berada diatas capaian ". $xname .". Tingkat Kemiskinan ". $xname ." pada ".$per_tk3[$tahun_kab_tk_max]." adalah sebesar ".$nilaiData_tk22[$tahun_kab_tk_max]."%. ";
                        }
                    } else {
                        $max_n_tk    ="Tingkat Kemiskinan ". $xnameKab ." pada ".$per_tk3[$tahun_kab_tk_max]." meningkat dibandingkan dengan ".$per_tk3[$tahun_kab_tk_1].". Pada ".$per_tk3[$tahun_kab_tk_max]." Tingkat Kemiskinan ". $xnameKab ." adalah sebesar ".$data_tk3[$tahun_kab_tk_max]."%, sedangkan pada tahun ".$per_tk3[$tahun_kab_tk_1]." Tingkat Kemiskinan tercatat sebesar ".$data_tk3[$tahun_kab_tk_1]."%. ";
                        if($nilaiData_tk22[$tahun_kab_tk_max]>$data_tk3[$tahun_kab_tk_max]){
                            $max_p_tk  ="Tingkat Kemiskinan ". $xnameKab ." pada ".$per_tk3[$tahun_kab_tk_max]." berada dibawah capaian ". $xname .". Tingkat Kemiskinan ". $xname ." pada ".$per_tk3[$tahun_kab_tk_max]." adalah sebesar ".$nilaiData_tk22[$tahun_kab_tk_max]."%. ";
                        }else{
                            $max_p_tk  ="Tingkat Kemiskinan ". $xnameKab ." pada ".$per_tk3[$tahun_kab_tk_max]." berada diatas capaian ". $xname .". Tingkat Kemiskinan ". $xname ." pada ".$per_tk3[$tahun_kab_tk_max]." adalah sebesar ".$nilaiData_tk22[$tahun_kab_tk_max]."%. ";
                        }
                    }
                    if($nilaiData_tk1[$tahun_kab_tk_max] > $data_tk3[$tahun_kab_tk_max]){
                        $max_k_tk    ="Capaian Tingkat Kemiskinan ". $xnameKab ." pada ".$per_tk3[$tahun_kab_tk_max]." berada dibawah nasional. Tingkat Kemiskinan nasional pada ".$per_tk3[$tahun_kab_tk_max]." adalah sebesar ".$nilaiData_tk1[$tahun_kab_tk_max]."%. ";
                    } else {
                        $max_k_tk    =" Capaian Tingkat Kemiskinan ". $xnameKab ." pada ".$per_tk3[$tahun_kab_tk_max]." berada diatas nasional. Tingkat Kemiskinan nasional pada ".$per_tk3[$tahun_kab_tk_max]." adalah sebesar ".$nilaiData_tk1[$tahun_kab_tk_max].".% ";
                    }
                    $tk_kab="select p.nama_kabupaten as label, e.*  from kabupaten p join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='36' AND e.id_periode='".$tahun_kab_tk_max."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='36' AND id_periode='".$tahun_kab_tk_max."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_tk_per = $this->db->query($tk_kab);
                    foreach ($list_kab_tk_per->result() as $row_tk_kab_per) {
                        $label_tk[]     = $row_tk_kab_per->label;
                        $nilai_tk_per[] = $row_tk_kab_per->nilai;
                        $posisi_tk=strpos($row_tk_kab_per->label, "Kabupaten");
                        if ($posisi_tk !== FALSE){
                            $label_tk11=substr( $row_tk_kab_per->label,0,3)." ".substr( $row_tk_kab_per->label,10);
                        }else{
                            $label_tk11=$row_tk_kab_per->label;
                        }
                        $label_tk1[]=$label_tk11;
                    }
                    $label_data_tk     = $label_tk1;
                    $nilai_data_tk_per = $nilai_tk_per;
                    
            $paragraf_12_2=$max_n_tk;
            $paragraf_12_3=$max_p_tk;
            $paragraf_12_4=$max_k_tk;
            $section->addText(''.$nomor++.'. Tingkat Kemiskinan', $fontStyleName );
            $section->addText($deskripsi12,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_12_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_12_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_12_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
                    
                    
                                        //indeks Kedalaman Kemiskinan
                    $sql_idk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_idk = $this->db->query($sql_idk);
                    foreach ($list_idk->result() as $row_idk) {
                        $tahun_idk[]     = $bulan[$row_idk->periode]."-".$row_idk->tahun;
                        $idperiode_idk   = $row_idk->id_periode;
                        $nilaiData_idk[] = (float)$row_idk->nilai;
                        $idperiode_idk3[]=$row_idk->id_periode;
                    }
                    $datay_idk = $nilaiData_idk;
                    $tahun_idk = $tahun_idk;
                    $sql_idk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_idk2 = $this->db->query($sql_idk2);
                    foreach ($list_idk2->result() as $row_idk2) {
                        $tahun_idk2[]   = $row_idk2->tahun;
                        $nilaiData_idk2[] = (float)$row_idk2->nilai;
                    }
                    $datay_idk2 = $nilaiData_idk2;
                    $tahun_idk2 = $tahun_idk2;
                    $sql_idk3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab, IFNULL(IND.sumber,0) sumber_k,IND.id_periode idperiode,IND.tahun,IND.periode
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='39' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='39' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai,sumber,tahun,periode 
                                        from nilai_indikator 
                                        where (id_indikator='39' AND wilayah='".$kab."')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='39' AND wilayah='".$kab."' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode"; 
                    $list_idk3 = $this->db->query($sql_idk3);
                    foreach ($list_idk3->result() as $row_idk3) {
                        $n_idk3   = $row_idk3->nilai_kab; 
                        if($n_idk3==0){
                            $nil_idk33 = '-';
                        } else {
                            $nil_idk33 = (float)$n_idk3;
                        }
                        
                        $nil_idk3[] = $nil_idk33;
                        $sumber_idk =$row_idk3->sumber_k;
                        $per_idk3[$row_idk3->tahun]    = $row_idk3->tahun;
                        $per_idk33[$row_idk3->tahun]    = $bulan[$row_idk3->periode]." ".$row_idk3->tahun;
                    }
                    $datay_idk3 = array_reverse($nil_idk3);
                    $tahun_idk3 = $tahun_idk3;
                    $tahun_kab_idk_max=max($per_idk3);
                    $periode_ikk_tahun ="Tahun ".$per_idk33[$tahun_kab_idk_max];
                    
                    if($datay_idk3[2] > $datay_idk3[4]){
                        $max_n_ikk    ="Indeks Kedalaman Kemiskinan ". $xnameKab ." pada ".$tahun_idk[4]." menurun dibandingkan dengan ".$tahun_idk[2].". Pada ".$tahun_idk[4]." Angka Indeks Kedalaman Kemiskinan ". $xnameKab ." adalah sebesar ".$datay_idk3[4]."%, sedangkan pada tahun ".$tahun_idk[2]." Angka Indeks Kedalaman Kemiskinan tercatat sebesar ".$datay_idk3[2]."%. ";
                        if($nilaiData_idk2[4]>$datay_idk3[4]){
                            $max_p_ikk  ="Indeks Kedalaman Kemiskinan ". $xnameKab ." pada ".$tahun_idk[4]." berada dibawah capaian ". $xname .". Angka Indeks Kedalaman Kemiskinan ". $xname ." pada ".$tahun_idk[5]." adalah sebesar ".$nilaiData_idk2[4]."%. ";
                        }else{
                            $max_p_ikk  ="Indeks Kedalaman Kemiskinan ". $xnameKab ." pada ".$tahun_idk[4]." berada diatas capaian ". $xname .". Angka Indeks Kedalaman Kemiskinan ". $xname ." pada ".$tahun_idk[5]." adalah sebesar ".$nilaiData_idk2[4]."%. ";
                        }
                    } else {
                        $max_n_ikk    ="Angka Indeks Kedalaman Kemiskinan ". $xnameKab ." pada ".$tahun_idk[4]." meningkat dibandingkan dengan ".$tahun_idk[2].". Pada ".$tahun_idk[4]." Angka Indeks Kedalaman Kemiskinan ". $xnameKab ." adalah sebesar ".$datay_idk3[4] ."%, sedangkan pada tahun ".$tahun_idk[2]." Angka Indeks Kedalaman Kemiskinan tercatat sebesar ".$datay_idk3[2]."%. ";
                        if($nilaiData_idk2[4]>$datay_idk3[4]){
                            $max_p_ikk  ="Angka Indeks Kedalaman Kemiskinan ". $xnameKab ." pada ".$tahun_idk[4]." berada dibawah capaian ". $xname .". Angka Indeks Kedalaman Kemiskinan ". $xname ." pada ".$tahun_idk[5]." adalah sebesar ".$nilaiData_idk2[4]."%. ";
                        }else{
                            $max_p_ikk  ="Angka Indeks Kedalaman Kemiskinan ". $xnameKab ." pada ".$tahun_idk[5]." berada diatas capaian ". $xname .". Angka Indeks Kedalaman Kemiskinan ". $xname ." pada ".$tahun_idk[5]." adalah sebesar ".$nilaiData_idk2[4]."%. ";
                        }
                    }
                    if($datay_idk[4] > $datay_idk3[4]){
                        $max_k_ikk    ="Capaian Angka Indeks Kedalaman Kemiskinan ". $xnameKab ." pada ".$tahun_idk[4]." berada dibawah nasional. Angka Indeks Kedalaman Kemiskinan nasional pada ".$tahun_idk[4]." adalah sebesar ". $datay_idk[4]."%. ";
                    } else {
                        $max_k_ikk    =" Capaian Angka Indeks Kedalaman Kemiskinan ". $xnameKab ." pada ".$tahun_idk[4]." berada diatas nasional. Angka Indeks Kedalaman Kemiskinan nasional pada ".$tahun_idk[4]." adalah sebesar ". $datay_idk[4]."%. ";
                    }
                    $ikk_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='39' AND e.id_periode='".$idperiode_idk3[4]."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='39' AND id_periode='".$idperiode_idk3[4]."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_ikk_per = $this->db->query($ikk_kab);
                    foreach ($list_kab_ikk_per->result() as $row_ikk_kab_per) {
                        $label_ikk[]     = $row_ikk_kab_per->label;
                        $nilai_ikk_per[] = $row_ikk_kab_per->nilai;
                        $posisi_ikk=strpos($row_ikk_kab_per->label, "Kabupaten");
                        if ($posisi_ikk !== FALSE){
                            $label_ikk11=substr( $row_ikk_kab_per->label,0,3)." ".substr( $row_ikk_kab_per->label,10);
                        }else{
                            $label_ikk11=$row_ikk_kab_per->label;
                        }
                        $label_ikk1[]=$label_ikk11;
                    }
                    $label_data_ikk     = $label_ikk1;
                    $nilai_data_ikk_per = $nilai_ikk_per;
                    
            $paragraf_13_2=$max_n_ikk;
            $paragraf_13_3=$max_p_ikk;
            $paragraf_13_4=$max_k_ikk;
            $section->addText(''.$nomor++.'. Indeks Kedalaman Kemiskinan (P1)', $fontStyleName );
            $section->addText($deskripsi13,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_13_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_13_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_13_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
                    
                    
            
            
                                //jumlah Penduduk Miskin
                     $sql_jpk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jpk = $this->db->query($sql_jpk);
                    foreach ($list_jpk->result() as $row_jpk) {
                        $tahun_jpk[]    = $bulan[$row_jpk->periode]."-".$row_jpk->tahun;
                        $idperiode_jpk3[]   = $row_jpk->id_periode;
                        $nilaiData_jpk[] = (float)$row_jpk->nilai;
                    }
                    $datay_jpk = $nilaiData_jpk;
                    $tahun_jpk = $tahun_jpk;
                    $periode_kab_jpk_max=max($idperiode_jpk3);
                    $sql_jpk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='".$id_pro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='".$id_pro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $list_jpk2 = $this->db->query($sql_jpk2);
                    foreach ($list_jpk2->result() as $row_jpk2) {
                        $tahun_jpk2[]   = $row_jpk2->tahun;
                        $nilaiData_jpk2[] = (float)$row_jpk2->nilai;
                        $nilaiData_jpk22[] = (float)$row_jpk2->nilai;
                    }
                    $datay_jpk2 = $nilaiData_jpk2;
                    $tahun_jpk2 = $tahun_jpk2;
                    
                    $sql_jpkkk3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.id_periode idperiode,IND.tahun,IND.periode 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='40' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='40' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun,periode 
                                            from nilai_indikator 
                                            where (id_indikator='40' AND wilayah='".$kab."')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='40' AND wilayah='".$kab."' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode"; 
                    
                    $list_jpk3 = $this->db->query($sql_jpkkk3);
                    foreach ($list_jpk3->result() as $row_jpk3) {
                        $nil_jpk3[] = $row_jpk3->nilai_kab; 
                        $sumber_jpk = $row_jpk3->sumber_k; 
                        $nil_jpk33[$row_jpk3->idperiode] = $row_jpk3->nilai_kab; 
                        $periode_jpk3[] = $row_jpk3->idperiode;
                        $per_jpk3[$row_jpk3->idperiode]    = $bulan[$row_jpk3->periode]." ".$row_jpk3->tahun;
                        $periode_jpm[$row_jpk3->idperiode] = $row_jpk3->idperiode;
                        
                    }
                    $datay_jpk3 = array_reverse($nil_jpk3);
                    $periode_kab_jpk_max = max($periode_jpk3);
                    $periode_kab_jpk_1 = $periode_kab_jpk_max-100;
                    $periode_jpk_tahun ="Tahun ".$per_jpk3[$periode_kab_jpk_max];
                    
                    
                    if($nil_jpk33[$periode_kab_jpk_1] > $nil_jpk33[$periode_kab_jpk_max]){
                        $rt_jpk=abs($nil_jpk33[$periode_kab_jpk_max]-$nil_jpk33[$periode_kab_jpk_1]);
                        $rt_jpkk=abs($nil_jpk33[$periode_kab_jpk_max]-$nil_jpk33[$periode_kab_jpk_1]);
                        $rt_jpk2=$rt_jpk/$nil_jpk33[$periode_kab_jpk_1];
                        $rt_jpk3=$rt_jpk2*100;
                        $rt_jpk33=number_format($rt_jpk3,2);
                        $max_n_jpk    ="Jumlah Penduduk Miskin di ". $xnameKab ." pada ". $per_jpk3[$periode_kab_jpk_max] ." sebanyak ". number_format($nil_jpk33[$periode_kab_jpk_max],0) . " orang. Sedangkan jumlah Penduduk Miskin pada ". $per_jpk3[$periode_kab_jpk_1] ." sebanyak ". number_format($nil_jpk33[$periode_kab_jpk_1],0) . " orang. Selama periode  ". $per_jpk3[$periode_kab_jpk_1] ." - ". $per_jpk3[$periode_kab_jpk_max] . " jumlah Penduduk Miskin di ". $xnameKab ." berkurang ".number_format($rt_jpkk,0)." atau sebesar ".$rt_jpk33 ."% ";
                    }else{
                        $rt_jpk  =$nil_jpk33[$periode_kab_jpk_max]-$nil_jpk33[$periode_kab_jpk_1];
                        $rt_jpk2=$rt_jpk/$datay_jpk33[$periode_kab_jpk_1];
                        $rt_jpk3=$rt_jpk2*100;
                        $rt_jpk33=number_format($rt_jpk3,2);
                        $max_n_jpk    ="Jumlah Penduduk Miskin di ". $xnameKab ." pada ". $per_jpk3[$periode_kab_jpk_max] ." sebanyak ". number_format($nil_jpk33[$periode_kab_jpk_max],0) . " orang. Sedangkan jumlah Penduduk Miskin pada ". $per_jpk3[$periode_kab_jpk_1] ." sebanyak ". number_format($nil_jpk33[$periode_kab_jpk_1],0) . " orang. Selama periode  ". $per_jpk3[$periode_kab_jpk_1] ." - ". $per_jpk3[$periode_kab_jpk_max] . " jumlah Penduduk Miskin di ".$xnameKab." meningkat ".number_format($rt_jpk,0)." atau sebesar ".$rt_jpk33 ."%";
                    }
                    $jpk2_kab="select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='".$provinsi."' and (e.id_indikator='40' AND e.id_periode='".$periode_jpm[$periode_kab_jpk_max]."') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='40' AND id_periode='".$periode_jpm[$periode_kab_jpk_max]."' group by wilayah ) 
                           group by wilayah order by wilayah asc";
                    $list_kab_jpk_per = $this->db->query($jpk2_kab);
                    foreach ($list_kab_jpk_per->result() as $row_jpk_kab_per) {
                        $label_jpk[]     = $row_jpk_kab_per->label;
                        $nilai_jpk_per[] = $row_jpk_kab_per->nilai;
                        $posisi_jpk=strpos($row_jpk_kab_per->label, "Kabupaten");
                        if ($posisi_jpk !== FALSE){
                            $label_jpk11=substr( $row_jpk_kab_per->label,0,3)." ".substr( $row_jpk_kab_per->label,10);
                        }else{
                            $label_jpk11=$row_jpk_kab_per->label;
                        }
                        $label_jpk1[]=$label_jpk11;
                    }
                    $label_data_jpk     = $label_jpk1;
                    $nilai_data_jpk_per = $nilai_jpk_per;
            
                    
                    $paragraf_14_2=$max_n_jpk;
            //$paragraf_14_3=$max_n_jpk;
    //        $paragraf_14_4=$max_k_ikk;
            $section->addText(''.$nomor++.'. Jumlah Penduduk Miskin', $fontStyleName );
            $section->addText($deskripsi14,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            $section->addText($paragraf_14_2,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
//            $section->addText($paragraf_14_3,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
//            $section->addText($paragraf_14_4,array('name' => 'Arial', 'size' => 11),array('alignment'=>'both'));
            
            
        $filename = "$judul".'.docx';			
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); //mime type
        header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
            
        }
        
    }
            
   
    
}
