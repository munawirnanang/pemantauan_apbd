<?php defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . "third_party/vendor/autoload.php");

use PhpOffice\PhpWord\Autoloader;
//Autoloader::register();
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\Common\XMLWriter;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TestHelperDOCX;
//use PhpOffice\PhpWord\SimpleType\TextAlignment;


class Laporan_word_e extends CI_Controller
{
    var $view_dir   = "peppd1/laporan_perkembangan/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/laporan_ppd/laporan_perkembangan.js";
    var $picture    = "picture/laporan_ppd";

    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Master", "m_ref");
        $this->load->library('M_pdf');
        require_once(APPPATH . '/third_party/jpgraph/jpgraph.php');
        require_once(APPPATH . '/third_party/jpgraph/jpgraph_bar.php');
        require_once(APPPATH . '/third_party/jpgraph/jpgraph_line.php');
        require_once(APPPATH . '/third_party/jpgraph/jpgraph_radar.php');
        require_once(APPPATH . '/third_party/jpgraph/jpgraph_scatter.php');
        require_once(APPPATH . '/third_party/jpgraph/jpgraph_ttf.inc.php');
    }

    /*
     * 
     */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            try {
                if (!$this->session->userdata(SESSION_LOGIN)) {
                    throw new Exception("Session expired, please login", 2);
                }

                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/admin/laporan_ppd/laporan_perkembangan.js?v=" . now("Asia/Jakarta");
                $now = date('Y');
                $data_page = array(
                    "now" => $now
                );
                $str = $this->load->view($this->view_dir . "index", $data_page, TRUE);
                $output = array(
                    "status"        =>  1,
                    "str"           =>  $str,
                    "js_path"       =>  base_url($this->js_path),
                    "js_initial"    =>  $this->js_init . ".init();",
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
        } else {
            exit("access denied!");
        }
    }

    function download_word()
    {
        $provinsi  = $_GET['inp_pro'];
        $kabupaten = $_GET['inp_sp'];
        $pro = $provinsi;
        $kab = $kabupaten;
        $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
        $bulan1 = array('00' => '', '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',);
        $prde = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
        $xname = "";
        $query = "";
        $gambar = 1;
        $nomor = 1;
        $daftarisi = 1;
        $daftargamabar = 1;
        $d_peek = "SELECT I.id,I.`deskripsi` FROM indikator I where 1=1";
        $list_peek = $this->db->query($d_peek);
        foreach ($list_peek->result() as $peek) {
            if ($peek->id == '1') {
                $deskripsi1   = $peek->deskripsi;
            }
            if ($peek->id == '2') {
                $deskripsi2   = $peek->deskripsi;
            }
            if ($peek->id == '3') {
                $deskripsi3   = $peek->deskripsi;
            }
            if ($peek->id == '4') {
                $deskripsi4   = $peek->deskripsi;
            }
            if ($peek->id == '6') {
                $deskripsi5   = $peek->deskripsi;
            }
            if ($peek->id == '5') {
                $deskripsi6   = $peek->deskripsi;
            }
            if ($peek->id == '7') {
                $deskripsi7   = $peek->deskripsi;
            }
            if ($peek->id == '8') {
                $deskripsi8   = $peek->deskripsi;
            }
            if ($peek->id == '9') {
                $deskripsi9   = $peek->deskripsi;
            }
            if ($peek->id == '10') {
                $deskripsi10   = $peek->deskripsi;
            }
            if ($peek->id == '11') {
                $deskripsi11   = $peek->deskripsi;
            }
            if ($peek->id == '36') {
                $deskripsi12   = $peek->deskripsi;
            }
            if ($peek->id == '39') {
                $deskripsi13   = $peek->deskripsi;
            }
            if ($peek->id == '38') {
                $deskripsi15   = $peek->deskripsi;
            }
            if ($peek->id == '40') {
                $deskripsi14   = $peek->deskripsi;
            }
        }

        $hal1 = base_url("assets/images/arcgis/ekonomi.jpg");
        $hal2 = base_url("assets/images/arcgis/adhb.png");
        $hal7 = base_url("assets/images/arcgis/adhk.png");
        $hal8 = base_url("assets/images/arcgis/jumlah_pengangguran_v.png");
        $hal9  = base_url("assets/images/arcgis/TPT1.jpg");
        $hal10 = base_url("assets/images/arcgis/ipm.png");
        $hal11 = base_url("assets/images/arcgis/gr.jpg");
        $hal12 = base_url("assets/images/arcgis/AHH.jpg");
        $hal13 = base_url("assets/images/arcgis/RLS.jpg");
        $hal14 = base_url("assets/images/arcgis/hls3.jpg");
        $hal15 = base_url("assets/images/arcgis/Pengeluaran_perkapita.jpg");
        $hal16 = base_url("assets/images/arcgis/Kedalaman_Kemiskinan.jpg");
        $hal17 = base_url("assets/images/arcgis/keparahan_kemiskinan.png");
        $hal18 = base_url("assets/images/arcgis/perkapita1.png");
        $hal19 = base_url("assets/images/arcgis/JPM.jpg");
        $iconh = base_url("assets/images/laporan/icon.png");


        //ASC periode 3 tahun
        $tahunini = date('Y') - 3;
        if ($provinsi == '' & $kabupaten == '') {
            $xname = "Indonesia";
            $query = "1000";
            $judul = "Indonesia";
        } elseif ($provinsi != '' & $kabupaten == '') {
            $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`id`='" . $pro . "' ";
            $list_data = $this->db->query($sql_pro);
            foreach ($list_data->result() as $Lis_pro) {
                $xname = $Lis_pro->nama_provinsi;
                $query = "1000";
                $id_pro = $Lis_pro->id;
                $judul = $Lis_pro->nama_provinsi;
            }
            $logopro      = $pro . ".jpg";
            //Perkembangan Pertumbuhan Ekonomi (%)
            $sql_ppe = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppe = $this->db->query($sql_ppe);
            foreach ($list_ppe->result() as $row_ppe) {
                $tahun1[]   = $row_ppe->tahun;
                $nilaiData1[] = (float)$row_ppe->nilai;
                $nilaimax[] = number_format($row_ppe->nilai, 2);
            }
            $max_pe = end($nilaiData1);
            $sql_ppe_pro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='" . $id_pro . "') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppe_pro = $this->db->query($sql_ppe_pro);

            $thn = '';
            foreach ($list_ppe_pro->result() as $row_ppe_pro) {
                $tahun1_pro[]   = $row_ppe_pro->tahun;
                $nilaiData1_pro[] = (float)$row_ppe_pro->nilai;
                $nilaimax_pro[] = number_format($row_ppe_pro->nilai, 2);
                $sumber_pe = $row_ppe_pro->sumber;
                $periode_pe[] = $row_ppe_pro->id_periode;
                $periode = $row_ppe_pro->periode;
                if ($periode == '00') {
                    $thn[] = $row_ppe_pro->tahun;
                } else {
                    $thn[] =  $prde[$row_ppe_pro->periode] . " - " . $row_ppe_pro->tahun;
                }
            }
            $thn_ex = $thn;
            $periode_pe_max = max($periode_pe);
            $data1 = substr($periode_pe_max, 0, 4);
            $data2 = substr($periode_pe_max, -2);

            if ($data2 == '00') {
                $tahun_pe_max = $data1 . " Antar Provinsi";
            } else {
                $tahun_pe_max =  $prde[$data2] . " - " . $data1 . " Antar Provinsi";
            }
            $datay1 = $nilaiData1;
            $datay2 = $nilaiData1_pro;
            if ($nilaiData1_pro[4] > $nilaiData1_pro[5]) {
                $meningkatmenurun = 'menurun';
                if ($nilaimax[5] > $nilaimax_pro[5]) {
                    $dibawahdiatas = 'di bawah';
                } else {
                    $dibawahdiatas = 'di atas';
                }
            } else {
                $meningkatmenurun = 'meningkat';
                if ($nilaimax[5] > $nilaimax_pro[5]) {
                    $dibawahdiatas = 'di bawah';
                } else {
                    $dibawahdiatas = 'di atas';
                }
            }

            $max_pe_k  = " ";
            $perbandingan_pe = "select p.label as label, e.* 
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
                $nilaiData_adhb1 = (float)$row_adhb->nilai / 1000000;
                $nilaiData_adhb11[] = number_format($nilaiData_adhb1, 2);
                $nilaiData_max[]   = (float)$row_adhb->nilai;
            }
            $datay_adhb1 = $nilaiData_adhb11;
            $tahun_adhb1 = $tahun_adhb;

            $max_pdrb = end($nilaiData_adhb11);
            $sql_adhb2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhb2 = $this->db->query($sql_adhb2);
            foreach ($list_adhb2->result() as $row_adhb2) {
                $tahun_adhb2[]   = $row_adhb2->tahun;
                $nilaiData_adhb2 = (float)$row_adhb2->nilai / 1000000;
                $nilaiData_adhb22[] = number_format($nilaiData_adhb2, 2);
                $nilaiData_max_p[] = (float)$row_adhb2->nilai;
                $sumber_adhb       = $row_adhb2->sumber;
                $periode_adhb[] = $row_adhb2->id_periode;
                $ket_adhb2[]  = $row_adhb2->keterangan;
            }
            $datay_adhb2        = $nilaiData_adhb22;
            $tahun_adhb2        = $tahun_adhb2;
            $tahunadhb          = end($tahun_adhb1);
            $periode_adhb_max   = max($periode_adhb);
            $periode_adhb_tahun = max($tahun_adhb2) . " Antar Provinsi";

            $max_adhb_k  = " ";
            if ($nilaiData_max_p[4] > $nilaiData_max_p[5]) {
                $meningkatmenurunADHB = 'menurun';
                if ($nilaiData_max[5] > $nilaiData_max_p[5]) {
                    $dibawahdiatasADHB = 'di bawah';
                } else {
                    $dibawahdiatasADHB = 'di atas';
                }
            } else {
                $meningkatmenurunADHB = 'meningkat';
                if ($nilaiData_max[5] > $nilaiData_max_p[5]) {
                    $dibawahdiatasADHB = 'di bawah';
                } else {
                    $dibawahdiatasADHB = 'di atas';
                }
            }

            $perbandingan_adhb = "select p.label as label, e.* 
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
                $nilai_adhb_per = $row_adhb_per->nilai / 1000000;
                $nilai_adhb_per1[] = number_format($nilai_adhb_per, 2);
            }
            $label_data_adhb     = $label_adhb;
            $nilai_data_adhb_per = $nilai_adhb_per1;

            //adhk (Rp)
            $sql_adhk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhk = $this->db->query($sql_adhk);
            foreach ($list_adhk->result() as $row_adhk) {
                $tahun_adhk[]   = $row_adhk->tahun;
                $nilaiData_adhk1 = (float)$row_adhk->nilai / 1000000;
                $nilaiData_adhk11[] = number_format($nilaiData_adhk1, 2);
                $adhk_nasional[] = (float)$row_adhk->nilai;
            }
            $datay_adhk1 = $nilaiData_adhk11;
            $tahun_adhk1 = $tahun_adhk;


            $sql_adhk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhk2 = $this->db->query($sql_adhk2);
            foreach ($list_adhk2->result() as $row_adhk2) {
                $tahun_adhk2[]     = $row_adhk2->tahun;
                $nilaiData_adhk22  = (float)$row_adhk2->nilai / 1000000;
                $nilaiData_adhk2[] = number_format($nilaiData_adhk22, 2);
                $adhk_p[] = (float)$row_adhk2->nilai;
                $sumber_adhk       = $row_adhk2->sumber;
                $periode_adhk[] = $row_adhk2->id_periode;
                $ket_adhk2[]  = $row_adhk2->keterangan;
            }
            $datay_adhk2 = $nilaiData_adhk2;
            $tahun_adhk2 = $tahun_adhk2;
            $periode_adhk_max = max($periode_adhk);
            $periode_adhk_tahun = max($tahun_adhk2) . " Antar Provinsi";

            if ($adhk_p[4] > $adhk_p[5]) {
                $meningkatmenurunADHK = 'menurun';
                if ($adhk_nasional[5] > $adhk_p[5]) {
                    $dibawahdiatasADHK = 'di bawah';
                } else {
                    $dibawahdiatasADHK = 'di atas';
                }
            } else {
                $meningkatmenurunADHK = 'meningkat';
                if ($adhk_nasional[5] > $adhk_p[5]) {
                    $dibawahdiatasADHK = 'di bawah';
                } else {
                    $dibawahdiatasADHK = 'di atas';
                }
            }


            $perbandingan_adhk = "select p.label as label, e.* 
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
                $nilai_adhk_per1 = $row_adhk_per->nilai / 1000000;
                $nilai_adhk_per[] = number_format($nilai_adhk_per1, 2);
            }
            $label_data_adhk     = $label_adhk;
            $nilai_data_adhk_per = $nilai_adhk_per;

            //jumlah pengangguran (Orang)
            $sql_jp = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jp = $this->db->query($sql_jp);
            foreach ($list_jp->result() as $row_jp) {
                $tahun_jp[]      = $bulan[$row_jp->periode] . "-" . $row_jp->tahun;
                $tahunJP[]      = $bulan1[$row_jp->periode] . " " . $row_jp->tahun;
                $tahun_jp1[]     = $row_jp->id_periode;
                $nilaiData_jp[]  = (float)$row_jp->nilai / 1000;
                $nilai_capaian[] = $row_jp->nilai;
                $tahun_jp11[] = $row_jp->tahun;
                $periode_jp1[] = $row_jp->periode;
            }
            $datay_jp = $nilaiData_jp;
            $tahun_jp = $tahun_jp;
            $periode_jp_max  = max($tahun_jp1);
            $dataJP1 = substr($periode_jp_max, 0, 4);
            $dataJP2 = substr($periode_jp_max, -2);
            if ($dataJP2 == '00') {
                $periode_jp_tahun = $dataJP1 . " Antar Provinsi";
            } else {
                $periode_jp_tahun =  $bulan1[$dataJP2] . " " . $dataJP1 . " Antar Provinsi";
            }

            $sql_jp2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jp2 = $this->db->query($sql_jp2);
            foreach ($list_jp2->result() as $row_jp2) {
                $tahun_jp2[]   = $row_jp2->tahun;
                $nilaiData_jp22 = (float)$row_jp2->nilai / 10000;
                $nilaiData_jp2[] = number_format($nilaiData_jp22, 2);
                $nilai_capaian2[] = $row_jp2->nilai;
                $sumber_jp = $row_jp->sumber;
            }
            $datay_jp2 = $nilaiData_jp2;
            $tahun_jp2 = $tahun_jp2;

            if ($nilai_capaian[3] > $nilai_capaian[5]) {
                $nn_jp = $nilai_capaian[3] - $nilai_capaian[5];
                $nn_jp2 = $nn_jp / $nilai_capaian[5];
                $nn_jp3 = $nn_jp2 * 100;
                $nn_jp33 = number_format($nn_jp3, 2);
                $max_jp  = "Jumlah penganggur nasional pada " . $tahun_jp[5] . " sebanyak " . number_format($nilai_capaian[5], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahun_jp[3] . " sebanyak " . number_format($nilai_capaian[3], 0) . " orang. Selama periode  " . $tahun_jp[3] . " - " . $tahun_jp[5] . " jumlah penganggur nasional berkurang " . number_format($nn_jp) . " orang atau sebesar " . $nn_jp33 . "% ";
            } else {
                $nn_jp  = $nilai_capaian[5] - $nilai_capaian[3];
                $nn_jp2 = $nn_jp / $nilai_capaian[3];
                $nn_jp3 = $nn_jp2 * 100;
                $nn_jp33 = number_format($nn_jp3, 2);
                $max_jp  = "Jumlah penganggur nasional pada " . $tahun_jp[5] . " sebanyak " . number_format($nilai_capaian[5], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahun_jp[3] . " sebanyak " . number_format($nilai_capaian2[3], 0) . " orang. Selama periode  " . $tahun_jp[3] . " - " . $tahun_jp[5] . " jumlah penganggur di " . $xname . " meningkat " . number_format($nn_jp) . " orang atau sebesar " . number_format($nn_jp33) . "%";
            }

            if ($nilai_capaian2[3] > $nilai_capaian2[5]) {
                $rt_jp = $nilai_capaian2[5] - $nilai_capaian2[3];
                $rt_jpp = abs($nilai_capaian2[5] - $nilai_capaian2[3]);
                $rt_jp2 = $rt_jp / $nilai_capaian2[3];
                $rt_jp3 = abs($rt_jp2 * 100);
                $rt_jp33 = number_format($rt_jp3, 2);
                $berkurangmeningkat = 'meningkat';
            } else {
                $rt_jp  = $nilai_capaian2[5] - $nilai_capaian2[3];
                $rt_jpp = abs($nilai_capaian2[3] - $nilai_capaian2[5]);
                $rt_jp2 = $rt_jp / $nilai_capaian2[3];
                $rt_jp3 = $rt_jp2 * 100;
                $rt_jp33 = number_format($rt_jp3, 2);
                $berkurangmeningkat = 'meningkat';
            }

            $perbandingan_jp = "select p.label as label, e.* 
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
                $nilai_jp_per[] = (float)$row_jp_per->nilai / 10000;
            }
            $label_data_jp     = $label_jp;
            $nilai_data_jp_per = $nilai_jp_per;

            //tingkat pengangguran terbuka
            $sql_tpt = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tpt = $this->db->query($sql_tpt);
            foreach ($list_tpt->result() as $row_tpt) {
                $tahun_tpt1[]    = $bulan[$row_tpt->periode] . "-" . $row_tpt->tahun;
                $tahun_tpt[]   = $row_tpt->tahun;
                $nilaiData_tpt1 = (float)$row_tpt->nilai;
                $nilaiData_tpt[] = number_format($nilaiData_tpt1, 2);
            }
            $datay_tpt = $nilaiData_tpt;
            $tahun_tpt = $tahun_tpt1;

            $sql_tpt2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tpt2 = $this->db->query($sql_tpt2);
            foreach ($list_tpt2->result() as $row_tpt2) {
                $tahun_tpt21[]    = $bulan[$row_tpt2->periode] . "-" . $row_tpt2->tahun;
                $tahunTPT2[]    = $bulan1[$row_tpt2->periode] . " " . $row_tpt2->tahun;
                $periode_tpt21[]  = $row_tpt2->periode;
                $tahun_tpt2[]     = $row_tpt2->tahun;
                $nilaiData_tpt22  = (float)$row_tpt2->nilai;
                $nilaiData_tpt2[] = number_format($nilaiData_tpt22, 2);
                $sumber_tpt       = $row_tpt2->sumber;
                $periode_tpt_id[] =   $row_tpt2->id_periode;
            }
            $datay_tpt2 = $nilaiData_tpt2;
            $tahun_tpt2 = $tahun_tpt2;
            $periode_tpt_max = max($periode_tpt_id);
            $dataTPT1 = substr($periode_tpt_max, 0, 4);
            $dataTPT2 = substr($periode_tpt_max, -2);
            if ($dataTPT2 == '00') {
                $periode_tpt_tahun = $dataTPT2 . " Antar Provinsi";
            } else {
                $periode_tpt_tahun =  $bulan1[$dataTPT2] . " " . $dataTPT1 . " Antar Provinsi";
            }

            if ($nilaiData_tpt2[3] > $nilaiData_tpt2[5]) {
                $menurunmeningkatTPT = 'menurun';
                if ($nilaiData_tpt[5] > $nilaiData_tpt2[5]) {
                    $dibawahdiatasTPT = 'di bawah';
                } else {
                    $dibawahdiatasTPT = 'di atas';
                }
            } else {
                $menurunmeningkatTPT = 'meningkat';
                if ($nilaiData_tpt[5] > $nilaiData_tpt2[5]) {
                    $dibawahdiatasTPT = 'di bawah';
                } else {
                    $dibawahdiatasTPT = 'di atas';
                }
            }

            $max_tpt_k = " ";
            $perbandingan_tpt = "select p.label as label, e.* 
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
            $sql_ipm2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ipm2 = $this->db->query($sql_ipm2);
            foreach ($list_ipm2->result() as $row_ipm2) {
                $tahun_ipm2[]   = $row_ipm2->tahun;
                $nilaiData_ipm2[] = (float)$row_ipm2->nilai;
                $sumber_ipm       = $row_ipm2->sumber;
                $periode_ipm_id[] = $row_ipm2->id_periode;
                $tahun_ipm21[]    = $bulan[$row_ipm2->periode] . "-" . $row_ipm2->tahun;
            }
            $datay_ipm2 = $nilaiData_ipm2;
            $tahun_ipm2 = $tahun_ipm2;
            $max_ipm_k = "";
            $periode_ipm_max = max($periode_ipm_id);
            $periode_ipm_tahun = max($tahun_ipm2) . " Antar Provinsi";
            //$paragraf_6_3='';
            if ($nilaiData_ipm2[4] > $nilaiData_ipm2[5]) {
                $menurunmeningkatIPM = 'menurun';
                if ($nilaiData_ipm[5] > $nilaiData_ipm2[5]) {
                    $dibawahdiatasIPM = 'dibawah';
                } else {
                    $dibawahdiatasIPM = 'diatas';
                }
            } else {
                $menurunmeningkatIPM = 'meningkat';
                $paragraf_6_2 = "Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " meningkat dibandingkan dengan tahun " . $tahun_ipm2[4] . ". Pada tahun " . $tahun_ipm2[5] . " Indeks Pembangunan Manusia " . $xname . " adalah sebesar " . number_format(end($nilaiData_ipm2), 2) . "% sedangkan pada tahun " . $tahun_ipm2[4] . "  Indeks Pembangunan Manusia tercatat sebesar " . number_format($nilaiData_ipm2[4], 2) . "%. ";
                if ($nilaiData_ipm[5] > $nilaiData_ipm2[5]) {
                    $dibawahdiatasIPM = 'dibawah';
                } else {
                    $dibawahdiatasIPM = 'diatas';
                }
            }

            $perbandingan_ipm = "select p.label as label, e.* 
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
                $nilai_ipm_per[] = number_format($nilai_ipm_per1, 2);
            }
            $label_data_ipm     = $label_ipm;
            $nilai_data_ipm_per = $nilai_ipm_per;


            //Gini Rasio.
            $sql_gr = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_gr = $this->db->query($sql_gr);
            foreach ($list_gr->result() as $row_gr) {
                $tahun_gr[]    = $bulan[$row_gr->periode] . "-" . $row_gr->tahun;
                $tahun_grR[]    = $bulan1[$row_gr->periode] . " " . $row_gr->tahun;
                $nilai_gr = number_format((float)$row_gr->nilai, 3);
                $nilaiData_gr[] = $nilai_gr;
            }
            $datay_gr = $nilaiData_gr;
            $tahun_gr = $tahun_gr;
            $sql_gr2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_gr2 = $this->db->query($sql_gr2);
            foreach ($list_gr2->result() as $row_gr2) {
                $tahun_gr2[]   = $row_gr2->tahun;
                $periode    = $row_gr2->periode;
                $tahungr2    = $row_gr2->tahun;
                $nilaiData_gr2[] = (float)$row_gr2->nilai;
                $nilai_gr22 = number_format((float)$row_gr2->nilai, 3);
                $nilaiData_gr22[] = $nilai_gr22;
                $sumber_gr       = $row_gr2->sumber;
                $periode_gr_id[]    = $row_gr2->id_periode;
                $tahun_gr21[]    = $bulan[$row_gr2->periode] . "-" . $row_gr2->tahun;
            }
            $datay_gr2 = $nilaiData_gr22;
            $tahun_gr2 = $tahun_gr2;
            $max_k_gr  =  "";
            $periode_gr_max   = max($periode_gr_id);
            if ($periode == '00') {
                $periode_gr_tahun = $tahun_gr2 . " Antar Provinsi";
            } else {
                $periode_gr_tahun =  $bulan1[$periode] . " " . $tahungr2 . " Antar Provinsi";
            }
            if ($nilaiData_gr2[3] > $nilaiData_gr2[5]) {
                $menurunmeningkatGR = 'menurun';
                if ($nilaiData_gr[5] > $nilaiData_gr2[5]) {
                    $dibawahdiatasGR = 'di bawah';
                } else {
                    $dibawahdiatasGR = 'di atas';
                }
            } elseif ($nilaiData_gr2[3] < $nilaiData_gr2[5]) {
                $menurunmeningkatGR = 'meningkat';
                if ($nilaiData_gr[5] > $nilaiData_gr2[5]) {
                    $dibawahdiatasGR = 'di bawah';
                } else {
                    $dibawahdiatasGR = 'di atas';
                }
            } else {
                $menurunmeningkatGR = 'sama';
                if ($nilaiData_gr[5] > $nilaiData_gr2[5]) {
                    $dibawahdiatasGR = 'di bawah';
                } else {
                    $dibawahdiatasGR = 'di atas';
                }
            }

            $perbandingan_gr = "select p.label as label, e.* 
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
                $nilai_gr_per[] = number_format($nilai_gr_per1, 3);
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
            $sql_ahh2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ahh2 = $this->db->query($sql_ahh2);
            foreach ($list_ahh2->result() as $row_ahh2) {
                $tahun_ahh2[]   = $row_ahh2->tahun;
                $nilaiData_ahh2[] = (float)$row_ahh2->nilai;
                $sumber_ahh       = $row_ahh2->sumber;
                $periode_ahh_id[] = $row_ahh2->id_periode;
                $tahun_ahh21[]    = $bulan[$row_ahh2->periode] . "-" . $row_ahh2->tahun;
            }
            $datay_ahh2 = $nilaiData_ahh2;
            //$tahun_ahh2 = $tahun_ahh2;
            $max_k_ahh = "";
            $periode_ahh_max = max($periode_ahh_id);
            $periode_ahh_tahun = max($tahun_ahh2) . " Antar Provinsi";
            if ($nilaiData_ahh2[4] > $nilaiData_ahh2[5]) {
                $menurunmeningkatAHH = 'menurun';
                if ($nilaiData_ahh[5] > $nilaiData_ahh2[5]) {
                    $dibawahdiatasAHH = 'dibawah';
                    //$paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                } else {
                    $dibawahdiatasAHH = 'diatas';
                    //$paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada diatas capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                }
            } else {
                $menurunmeningkatAHH = 'meningkat';
                if ($nilaiData_ahh[5] > $nilaiData_ahh2[5]) {
                    $dibawahdiatasAHH = 'dibawah';
                    // $paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                } else {
                    $dibawahdiatasAHH = 'diatas';
                }
            }

            $perbandingan_ahh = "select p.label as label, e.* 
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
            $sql_rls2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_rls2 = $this->db->query($sql_rls2);
            foreach ($list_rls2->result() as $row_rls2) {
                $tahun_rls2[]   = $row_rls2->tahun;
                $nilaiData_rls2[] = (float)$row_rls2->nilai;
                $sumber_rls = $row_rls2->sumber;
                $periode_rls_id[] = $row_rls2->id_periode;
                $tahun_rls21[]    = $bulan[$row_rls2->periode] . "-" . $row_rls2->tahun;
            }
            $datay_rls2 = $nilaiData_rls2;
            $tahun_rls2 = $tahun_rls2;
            $periode_rls_max = max($periode_rls_id);
            $periode_rls_tahun = max($tahun_rls2) . " Antar Provinsi";
            if ($nilaiData_rls2[4] > $nilaiData_rls2[5]) {
                $menurunmeningkatRLS = 'menurun';
                if ($nilaiData_rls[5] > $nilaiData_rls2[5]) {
                    $dibawahdiatasRLS = 'di bawah';
                } else {
                    $dibawahdiatasRLS = 'di atas';
                }
            } else {
                $menurunmeningkatRLS = 'meningkat';
                if ($nilaiData_rls[5] > $nilaiData_rls2[5]) {
                    $dibawahdiatasRLS = 'di bawah';
                } else {
                    $dibawahdiatasRLS = 'di atas';
                }
            }

            $max_k_rls = "";
            $perbandingan_rls = "select p.label as label, e.* 
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
            $sql_hls2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_hls2 = $this->db->query($sql_hls2);
            foreach ($list_hls2->result() as $row_hls2) {
                $tahun_hls2[]   = $row_hls2->tahun;
                $nilaiData_hls2[] = (float)$row_hls2->nilai;
                $sumber_hls = $row_hls2->sumber;
                $periode_hls_id[] = $row_hls2->id_periode;
                $tahun_hls21[]    = $bulan[$row_hls2->periode] . "-" . $row_hls2->tahun;
            }
            $datay_hls2 = $nilaiData_hls2;
            $tahun_hls2 = $tahun_hls2;
            $periode_hls_max = max($periode_hls_id);
            $periode_hls_tahun = max($tahun_hls2) . " Antar Provinsi";
            if ($nilaiData_hls2[4] > $nilaiData_hls2[5]) {
                $menurunmeningkatHLS = 'menurun';
                if ($nilaiData_hls[5] > $nilaiData_hls2[5]) {
                    $dibawahdiatasHLS = 'di bawah';
                } else {
                    $dibawahdiatasHLS = 'di atas';
                }
            } else {
                $menurunmeningkatHLS = 'meningkat';
                if ($nilaiData_hls[5] > $nilaiData_hls2[5]) {
                    $dibawahdiatasHLS = 'di bawah';
                } else {
                    $dibawahdiatasHLS = 'di atas';
                }
            }

            $max_k_hls = "";
            $perbandingan_hls = "select p.label as label, e.* 
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
                $nilai_ppk1 = number_format((float)$row_ppk->nilai / 1000000, 2);
                $nilaiData_ppk1[] = $nilai_ppk1;
            }
            $datay_ppk = $nilaiData_ppk1;
            $tahun_ppk = $tahun_ppk;
            $sql_ppk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppk2 = $this->db->query($sql_ppk2);
            foreach ($list_ppk2->result() as $row_ppk2) {
                $tahun_ppk2[]     = $row_ppk2->tahun;
                $nilaiData_ppk2[] = (float)$row_ppk2->nilai;
                $nilai_ppk22 = number_format((float)$row_ppk2->nilai / 1000000, 2);
                $nilaiData_ppk22[] = $nilai_ppk22;
                $sumber_ppk       = $row_ppk->sumber;
                $periode_ppk_id[] = $row_ppk2->id_periode;
                $tahun_ppk21[]    = $bulan[$row_ppk2->periode] . "-" . $row_ppk2->tahun;
            }
            $datay_ppk2 = $nilaiData_ppk22;
            $tahun_ppk2 = $tahun_ppk2;
            $periode_ppk_max   = max($periode_ppk_id);
            $periode_ppk_tahun = max($tahun_ppk2) . " Antar Provinsi";
            if ($nilaiData_ppk2[4] > $nilaiData_ppk2[5]) {
                $menurunmeningkatPPK = 'menurun';
                if ($nilaiData_ppk[5] > $nilaiData_ppk2[5]) {
                    $dibawahdiatasPPK = 'di bawah';
                } else {
                    $dibawahdiatasPPK = 'di atas';
                }
            } else {
                $menurunmeningkatPPK = 'meningkat';
                if ($nilaiData_ppk[5] > $nilaiData_ppk2[5]) {
                    $dibawahdiatasPPK = 'di bawah';
                } else {
                    $dibawahdiatasPPK = 'di atas';
                }
            }

            $max_k_ppk = "";
            $perbandingan_ppk = "select p.label as label, e.* 
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
                $nilai_ppk       = number_format($row_ppk_per->nilai / 1000000, 2);
                $nilai_ppk_per[] = $nilai_ppk;
            }
            $label_data_ppk     = $label_ppk;
            $nilai_data_ppk_per = $nilai_ppk_per;


            //Tingkat Kemiskinan
            $sql_tk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tk = $this->db->query($sql_tk);
            foreach ($list_tk->result() as $row_tk) {
                $tahun_tk[]    = $bulan[$row_tk->periode] . "-" . $row_tk->tahun;
                $tahun_tkk[]    = $bulan1[$row_tk->periode] . " " . $row_tk->tahun;
                $nilaiData_tk[] = (float)$row_tk->nilai;
            }
            $datay_tk = $nilaiData_tk;
            $tahun_tk = $tahun_tk;
            $sql_tk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tk2 = $this->db->query($sql_tk2);
            foreach ($list_tk2->result() as $row_tk2) {
                $tahun_tk2[]   = $row_tk2->tahun;
                $tahun_tk22   = $row_tk2->tahun;
                $nilaiData_tk2[] = (float)$row_tk2->nilai;
                $sumber_tk       = $row_tk2->sumber;
                $periode_tk_id[] = $row_tk2->id_periode;
                $periode_tk = $row_tk2->periode;
                $tahun_tk21[]    = $bulan[$row_tk2->periode] . "-" . $row_tk2->tahun;
            }
            $datay_tk2 = $nilaiData_tk2;
            $tahun_tk2 = $tahun_tk2;
            $periode_tk_max = max($periode_tk_id);
            //$periode_tk_tahun=max($tahun_tk21)." Antar Provinsi" ;
            if ($periode_tk == '00') {
                $periode_tk_tahun = $tahun_tk22 . " Antar Provinsi";
            } else {
                $periode_tk_tahun =  $bulan1[$periode_tk] . " " . $tahun_tk22 . " Antar Provinsi";
            }

            if ($nilaiData_tk2[3] > $nilaiData_tk2[5]) {
                $menurunmeningkatTK = 'menurun';
                if ($nilaiData_tk[5] > $nilaiData_tk2[5]) {
                    $dibawahdiatasTK = 'di bawah';
                } else {
                    $dibawahdiatasTK = 'di atas';
                    //$paragraf_12_3  ="Capaian Angka tingkat Kemiskinan ". $xname ." pada ".$tahun_tk[5]." berada diatas capaian nasional. Angka tingkat Kemiskinan nasional pada ".$tahun_tk[5]." adalah sebesar ".number_format(end($nilaiData_tk),2) ."% ";
                }
            } else {
                $menurunmeningkatTK = 'meningkat';
                if ($nilaiData_tk[5] > $nilaiData_tk2[5]) {
                    $dibawahdiatasTK = 'di bawah';
                } else {
                    $dibawahdiatasTK = 'di atas';
                }
            }

            $max_k_tk = "";
            $perbandingan_tk = "select p.label as label, e.* 
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
                $tahun_idk[]    = $bulan[$row_idk->periode] . "-" . $row_idk->tahun;
                $tahunIDK1[]    = $bulan1[$row_idk->periode] . " " . $row_idk->tahun;
                $nilai_idk = number_format((float)$row_idk->nilai, 2);
                $nilaiData_idk[] = $nilai_idk;
            }
            $datay_idk = $nilaiData_idk;
            $tahun_idk = $tahun_idk;
            $sql_idk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_idk2 = $this->db->query($sql_idk2);
            foreach ($list_idk2->result() as $row_idk2) {
                $tahun_idk2[]     = $row_idk2->tahun;
                $nilai_idk2       = number_format((float)$row_idk2->nilai, 2);
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
            $periode_ikk_tahun = $bulan1[$periodeidk] . " " . $tahunidk . " Antar Provinsi";
            if ($nilaiData_idk2[3] > $nilaiData_idk2[5]) {
                $menurunmeningkatIKK = 'menurun';
                if ($nilaiData_idk[5] > $nilaiData_idk2[5]) {
                    $dibawahdiatasIKK = 'di bawah';
                } else {
                    $dibawahdiatasIKK = 'di atas';
                }
            } else {
                $menurunmeningkatIKK = 'meningkat';
                if ($nilaiData_idk[5] > $nilaiData_idk2[5]) {
                    $dibawahdiatasIKK = 'di bawah';
                } else {
                    $dibawahdiatasIKK = 'di atas';
                }
            }


            $max_k_ikk = "";
            $perbandingan_ikk = "select p.label as label, e.* 
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
                $nilai_ikk       = number_format($row_ikk_per->nilai, 2);
                $nilai_ikk_per[] = $nilai_ikk;
            }
            $label_data_ikk     = $label_ikk;
            $nilai_data_ikk_per = $nilai_ikk_per;

            //indeks Keparahan Kemiskinan(P2)
            $sql_ikk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ikk = $this->db->query($sql_ikk);
            foreach ($list_ikk->result() as $row_ikk) {
                //$tahun_idk[]   = $row_idk->tahun;
                $tahun_ikk[]    = $bulan[$row_ikk->periode] . "-" . $row_ikk->tahun;
                $tahunIKK1[]    = $bulan1[$row_ikk->periode] . " " . $row_ikk->tahun;
                $nilai_ikk = number_format((float)$row_ikk->nilai, 2);
                $nilaiData_ikk[] = $nilai_ikk;
            }
            $datay_ikk = $nilaiData_ikk;
            $tahun_ikk = $tahun_ikk;
            $sql_ikk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ikk2 = $this->db->query($sql_ikk2);
            foreach ($list_ikk2->result() as $row_ikk2) {
                $tahun_ikk2[]     = $row_ikk2->tahun;
                $nilai_ikk2       = number_format((float)$row_ikk2->nilai, 2);
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
            $periode_ikkk_tahun = $bulan1[$periodeikk] . " " . $tahunikk . " Antar Provinsi";
            if ($nilaiData_ikk2[3] > $nilaiData_ikk2[5]) {
                $menurunmeningkatIKKK = 'menurun';
                if ($nilaiData_ikk[5] > $nilaiData_ikk2[5]) {
                    $dibawahdiatasIKKK = 'di bawah';
                } else {
                    $dibawahdiatasIKKK = 'di atas';
                }
            } else {
                $menurunmeningkatIKKK = 'meningkat';
                if ($nilaiData_ikk[5] > $nilaiData_ikk2[5]) {
                    $dibawahdiatasIKKK = 'di bawah';
                } else {
                    $dibawahdiatasIKKK = 'di atas';
                }
            }


            $max_k_ikkk = "";
            $perbandingan_ikkk = "select p.label as label, e.* 
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
                $nilai_ikkk       = number_format($row_ikkk_per->nilai, 2);
                $nilai_ikkk_per[] = $nilai_ikkk;
            }
            $label_data_ikkk     = $label_ikkk;
            $nilai_data_ikkk_per = $nilai_ikkk_per;



            //jumlah Penduduk Miskin
            $sql_jpk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jpk = $this->db->query($sql_jpk);
            foreach ($list_jpk->result() as $row_jpk) {
                $tahun_jpk[]    = $bulan1[$row_jpk->periode] . " " . $row_jpk->tahun;
                $nilaiData_jpk[] = (float)$row_jpk->nilai;
                $nilaiData_jpk1[] = (float)$row_jpk->nilai;
                $periode_jpk1[] = (float)$row_jpk->id_periode;
            }
            $datay_jpk = $nilaiData_jpk;
            $tahun_jpk = $tahun_jpk;

            $periode_jpk_max    = max($periode_jpk1);
            $data1_jpk          = substr($periode_jpk_max, 0, 4);
            $data2_jpk          = substr($periode_jpk_max, -2);
            $periode_jpk_tahun  =  $bulan1[$data2_jpk] . " " . $data1_jpk . " Antar Provinsi";


            $sql_jpk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jpk2 = $this->db->query($sql_jpk2);
            foreach ($list_jpk2->result() as $row_jpk2) {
                $tahun_jpk2[]   = $row_jpk2->tahun;
                $nilaiData_jpk2[] = (float)$row_jpk2->nilai;
                $nilaiData_jpk22[] = (float)$row_jpk2->nilai;
                $sumber_jpk       = $row_jpk2->sumber;
                $periode_jpk_id[]  = $row_jpk2->id_periode;
                $tahun_jpk21[]     = $bulan[$row_jpk2->periode] . "-" . $row_jpk2->tahun;
            }
            $datay_jpk2 = $nilaiData_jpk2;
            $tahun_jpk2 = $tahun_jpk2;
            $tahun_jpk21 = $tahun_jpk21;

            $periode_jpk_max = max($periode_jpk_id);


            if ($nilaiData_jpk22[3] > $nilaiData_jpk22[5]) {
                $rt_jpk = $nilaiData_jpk22[5] - $nilaiData_jpk22[3];
                $rt_jpkk = abs($nilaiData_jpk22[5] - $nilaiData_jpk22[3]);
                $rt_jpk2 = $rt_jpk / $nilaiData_jpk22[3];
                $rt_jpk3 = abs($rt_jpk2 * 100);
                $rt_jpk33 = number_format($rt_jpk3, 2);
                $berkurangbertambah = 'berkurang';
            } else {
                $rt_jpk  = $nilaiData_jpk22[5] - $nilaiData_jpk22[3];
                $rt_jpkk = abs($nilaiData_jpk22[5] - $nilaiData_jpk22[3]);
                $rt_jpk2 = $rt_jpk / $nilaiData_jpk22[3];
                $rt_jpk3 = $rt_jpk2 * 100;
                $rt_jpk33 = number_format($rt_jpk3, 2);
                $berkurangbertambah = 'bertambah';
            }

            $perbandingan_jpk = "select p.label as label, e.* 
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

            $cover = base_url("assets/images/laporan/cover/" . $pro . ".jpg");
            $src = base_url("assets/images/laporan/cover_master_v3.jpg");
            $logo = base_url("assets/images/logopropinsi/" . $pro . ".png");
            //$hal1 =base_url("assets/images/laporan/hal1.jpg");

            //        $hal2 =base_url("assets/images/laporan/hal2.jpg");

            //        $hal8 =base_url("assets/images/laporan/hal8.jpg");
            //        $hal9  =base_url("assets/images/laporan/hal9.jpg");
            //        $hal10 =base_url("assets/images/laporan/hal10.jpg");
            //        $hal18 =base_url("assets/images/laporan/hal18.jpg");


            $section = $phpWord->addSection(array(
                'headerHeight' => 50,
                'footerHeight' => 3000
            ));
            $header = $section->addHeader();
            $header->addWatermark(
                $cover,
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
            $header->addWatermark(
                base_url("assets/images/header.png"),
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
            $phpWord->addFontStyle($fontStyleName, array('name' => 'Arial Narrow', 'size' => 14, 'color' => '1B2232', 'bold' => true));
            $style0 = array(
                'width'          => Converter::cmToEmu(13), 'height'         => Converter::cmToEmu(6),
                'showAxisLabels' => true, 'showGridX'      => false, 'showGridY'      => false,
                'showLegend'     => true, 'valueLabelPosition'     => FALSE,
            );
            $style = array(
                'width'          => Converter::cmToEmu(16), 'height'         => Converter::cmToEmu(6),
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
            $fontparagraf       = array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10);
            $fontparagraf1 = array('alignment' => 'both');
            $fontmiring   = array('name' => 'Book Antiqua (Body)', 'size' => 10, 'spaceAfter' => 80, 'italic' => true, 'size' => 10);

            $fontgambar = array('name' => 'Book Antiqua (Body)', 'size' => 9, 'bold' => true);
            $fontgambar1 = array('alignment' => 'center');
            //Kata Pengantar
            $section->addText('Kata Pengantar',  array('name' => 'Arial', 'spaceAfter' => 80, 'size' => 16, 'bold' => true), array('alignment' => 'center'));

            $phpWord->addParagraphStyle('pStyler', array('alignment' => 'both'));
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
            $textrun3->addText(htmlspecialchars("Jakartaaa,      Desember 2023"), $fontparagraf);
            $textrun4 = $section->addTextRun('pStyler2');
            $textrun4->addText(htmlspecialchars("Direktur Pemantauan, Evaluasi dan"), $fontparagraf);
            $textrun5 = $section->addTextRun('pStyler2');
            $textrun5->addText(htmlspecialchars("Pengendalian Pembangunan Dareah"), $fontparagraf);
            $section->addTextBreak(2);
            $textrun6 = $section->addTextRun('pStyler2');
            $textrun6->addText(htmlspecialchars("Agustin Arry Yanna"), $fontparagraf);

            //          daftar isi
            $section = $phpWord->addSection();
            $section->addText('DAFTAR ISI',  array('name' => 'Century Gothic (Headings)', 'spaceAfter' => 80, 'size' => 20, 'bold' => true), array('alignment' => 'center'));
            $section->addText(' KATA PENGANTAR...........................................................................................................................................2',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText(' DAFTAR ISI...........................................................................................................................................................3',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText(' DAFTAR GAMBAR.............................................................................................................................................4',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Pertumbuhan Ekonomi.....................................................................................................................................5',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Perkembangan PDRB per kapita ADHB........................................................................................................6',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Perkembangan PDRB per kapita ADHK tahun dasar 2010........................................................................7',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Perkembangan Jumlah Penganggur...............................................................................................................8',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Tingkat Pengangguran Terbuka......................................................................................................................9',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Indeks Pembangunan Manusia.....................................................................................................................10',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Rasio Gini..........................................................................................................................................................11',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Angka Harapan Hidup...................................................................................................................................12',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Rata-rata Lama Sekolah..................................................................................................................................13',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Harapan Lama Sekolah................................................................................................................................14',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Pengeluaran per Kapita................................................................................................................................15',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Tingkat Kemiskinan......................................................................................................................................16',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Indeks Kedalaman Kemiskinan (P1) .........................................................................................................17',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Indeks Keparahan Kemiskinan (P2) ..........................................................................................................18',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Jumlah Penduduk Miskin............................................................................................................................19',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            //daftar gambar 
            $section = $phpWord->addSection();
            $section->addText('DAFTAR GAMBAR',  array('name' => 'Century Gothic (Headings)', 'spaceAfter' => 80, 'size' => 20, 'bold' => true), array('alignment' => 'center'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Pertumbuhan Ekonomi.........................................................................................4',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Pertumbuhan Ekonomi Antar Provinsi................................................................4',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan PDRB per Kapita ADHB........................................................................................5',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Perkembangan PDRB per kapita ADHB Antar provinsi...................................5',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan PDRB per kapita ADHK Tahun Dasar 2010.......................................................6',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan PDRB per kapita ADHK Tahun Dasar 2010 Antar provinsi..............................6',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Jumlah Penganggur................................................................................................7',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Jumlah Penganggur Antar Provinsi......................................................................7',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Tingkat Pengangguran Terbuka..........................................................................8',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Tingkat Pengangguran Terbuka Antar Provinsi...............................................8',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Indeks Pembangunan Indonesia........................................................................9',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Indeks Pembangunan Indonesia Antar Provinsi...............................................9',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Rasio Gini.............................................................................................................10',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Rasio Gini Antar Provinsi...................................................................................10',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Angka Harapan Hidup......................................................................................11',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Angka Harapan Hidup Antar Provinsi............................................................11',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Rata-rata Lama Sekolah.....................................................................................12',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Rata-rata Lama Sekolah Antar Provinsi...........................................................12',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Harapan Lama Sekolah.....................................................................................13',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Harapan Lama Sekolah Antar Provinsi............................................................13',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Pengeluaran per Kapita.....................................................................................14',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Pengeluaran per Kapita Antar Provinsi............................................................14',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Tingkat Kemiskinan...........................................................................................15',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Tingkat Kemiskinan Antar Provinsi..................................................................15',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Indeks Kedalaman Kemiskinan (P1) ..............................................................16',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Indeks Kedalaman Kemiskinan (P1) Antar Provinsi......................................16',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Indeks Keparahan Kemiskinan (P2) ...........…................................................17',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Indeks Keparahan Kemiskinan (P2) Antar Provinsi.......................................17',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Jumlah Penduduk Miskin.................................................................................18',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Jumlah Penduduk Miskin Antar Provinsi........................................................18',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));

            //halaman Baru
            $section = $phpWord->addSection();
            $section->addText('1. Pertumbuhan Ekonomi', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));

            $section->addImage(
                $hal1,
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
                )
            );

            $paragraf_1_2  = "Pertumbuhan ekonomi " . $xname . " pada tahun " . $tahun1[5] . " " . $meningkatmenurun . " dibandingkan dengan tahun " . $tahun1[4] . ". Pada tahun " . $tahun1[5] . " pertumbuhan ekonomi " . $xname . " adalah sebesar " . end($nilaimax_pro) . "%, sedangkan pada tahun " . $tahun1[4] . " pertumbuhannya tercatat sebesar " . $nilaimax_pro[4] . "%. ";
            $paragraf_1_3  = "Capaian pertumbuhan ekonomi " . $xname . " pada tahun " . $tahun1[5] . " " . $dibawahdiatas . " "
                . "Nasional. Pertumbuhan ekonomi Nasional pada tahun " . $tahun1[5] . " adalah sebesar " . end($nilaimax) . "%. ";

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
            $textbox->addText($deskripsi1,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_1_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_1_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
                $chart = $section->addChart($chartType, $categories, $series1, $style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories, $series2, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Pertumbuhan Ekonomi (%)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_1_2 = $label_data_ppe;
            $series_1_2     = $nilai_data_ppe_per;
            //$warna_1_2      = $wrnPE;
            $chart = $section->addChart('column', $categories_1_2, $series_1_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Perkembangan Pertumbuhan Ekonomi Tahun " . $tahun_pe_max . " (%)", $fontgambar, $fontgambar1);

            //$chart->getStyle()->setColors($warna_1_2);
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);


            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('2. Perkembangan PDRB per Kapita ADHB', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal2,
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
                )
            );
            //$section->addText('2. Perkembangan PDRB per Kapita ADHB',$fontStyleName);
            $paragraf_2_2  = "PDRB per kapita ADHB " . $xname . " pada tahun " . $tahun_adhb2[5] . " " . $meningkatmenurunADHB . " dibandingkan dengan tahun " . $tahun_adhb2[4] . ". "
                . "Pada tahun " . $tahun_adhb2[5] . " PDRB per kapita ADHB " . $xname . " adalah sebesar Rp" . number_format(end($nilaiData_max_p), 0) . " " . $ket_adhb2[5] . ""
                . "sedangkan pada tahun " . $tahun_adhb2[4] . " PDRB per kapita ADHB tercatat sebesar Rp" . number_format($nilaiData_max_p[4], 0) . ". ";
            $paragraf_2_3  = "Capaian PDRB per kapita " . $xname . " pada tahun " . $tahun_adhb2[5] . " berada " . $dibawahdiatasADHB . " capaian Nasional. "
                . "PDRB per kapita ADHB Nasional pada tahun " . $tahun_adhb2[5] . " adalah sebesar Rp" . number_format($nilaiData_max[5]) . ". ";
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
            $textbox->addText($deskripsi2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_2_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 223,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#539333',
                )
            );
            $textbox->addText($paragraf_2_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            $categories_2_2 = $tahun_adhb;
            $series_2_n     = $datay_adhb1;
            $series_2_2     = $datay_adhb2;
            foreach ($chartTypes2 as $chartType) {
                $chart = $section->addChart($chartType, $categories_2_2, $series_2_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_2_2, $series_2_2, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan PDRB per kapita ADHB (Juta Rupiah)', $fontgambar, $fontgambar1);
            }


            $categories_2_3 = $label_data_adhb;
            $series_2_3     = $nilai_data_adhb_per;
            $chart_2_3      = $section->addChart('column', $categories_2_3, $series_2_3, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan PDRB per kapita ADHB Tahun " . $periode_adhb_tahun . " (Juta Rupiah)", $fontgambar, $fontgambar1);
            $section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('3. Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal7,
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
                )
            );
            $paragraf_3_2  = "PDRB per kapita ADHK tahun dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " " . $meningkatmenurunADHK . " dibandingkan dengan tahun " . $tahun_adhk[4] . ". Pada tahun " . $tahun_adhk[5] . " PDRB per kapita ADHK tahun dasar 2010 " . $xname . " adalah sebesar Rp" . number_format(end($adhk_p)) . " " . $ket_adhk2[5] . "sedangkan pada tahun " . $tahun_adhk[4] . " PDRB per kapita ADHK tahun dasar 2010 tercatat sebesar Rp" . number_format($adhk_p[4]) . ". ";
            $paragraf_3_3  = "Capaian PDRB per kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " berada " . $dibawahdiatasADHK . " capaian nasional. PDRB per kapita ADHK tahun dasar 2010 nasional pada tahun " . $tahun_adhk[5] . " adalah sebesar Rp" . number_format(end($adhk_nasional)) . ".";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 380,
                    'height'      => 100,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',

                )
            );
            $textbox->addText($deskripsi3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_3_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_3_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            //$section->addTextBreak(1);
            $categories_3_1 = $tahun_adhk1;
            $series_3_n     = $datay_adhk1;
            $series_3_1     = $datay_adhk2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_3_1, $series_3_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_3_1, $series_3_1, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010 (Juta Rupiah)', $fontgambar, $fontgambar1);
            }

            $categories_3_2 = $label_data_adhk;
            $series_3_2     = $nilai_data_adhk_per;
            $chart_2_3      = $section->addChart('column', $categories_3_2, $series_3_2, $style_ADHK);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan PDRB per Kapita ADHK (2010) tahun " . $periode_adhk_tahun . " (Juta Rupiah)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik, diolah', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('4. Perkembangan Jumlah Penganggur', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal8,
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
                )
            );
            $paragraf_4_2  = "Jumlah penganggur di " . $xname . " pada " . $tahunJP[5] . " sebanyak " . number_format($nilai_capaian2[5], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahunJP[3] . " sebanyak " . number_format($nilai_capaian2[3], 0) . " orang. Selama periode " . $tahunJP[3] . " sampai " . $tahunJP[5] . " jumlah penganggur di " . $xname . " " . $berkurangmeningkat . " " . number_format($rt_jpp) . " orang atau sebesar " . $rt_jp33 . "%."
                . " Jumlah pengangur nasional pada " . $tahunJP[5] . " sebesar " . number_format($nilai_capaian[5], 0) . " orang.";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi4,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 323,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                )
            );
            $textbox->addText($paragraf_4_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            $section->addTextBreak(1);
            $categories_4_1 = $tahun_jp;
            $series_4_1     = $datay_jp2;

            $chart_4_1      = $section->addChart('column', $categories_4_1, $series_4_1, $style_2, $xname);
            $section->addText("Gambar " . $gambar++ . ". Perkembangan Jumlah Penganggur (Ribu Orang)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $categories_4_2 = $label_data_jp;
            $series_4_2     = $nilai_data_jp_per;
            $chart_4_2      = $section->addChart('column', $categories_4_2, $series_4_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Jumlah Penganggur " . $periode_jp_tahun . " (Ribu Orang)", $fontgambar, $fontgambar1);
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('5. Tingkat Pengangguran Terbuka', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal9,
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
                )
            );
            $paragraf_5_2  = "Tingkat pengangguran terbuka " . $xname . " pada " . $tahunTPT2[5] . " " . $menurunmeningkatTPT . " dibandingkan dengan " . $tahunTPT2[3] . ". Pada " . $tahunTPT2[5] . " Tingkat pengangguran terbuka " . $xname . " adalah sebesar " . number_format(end($nilaiData_tpt2), 2) . "% sedangkan pada " . $tahunTPT2[3] . " tingkat pengangguran terbuka tercatat sebesar " . number_format($nilaiData_tpt2[3], 2) . "%. ";
            $paragraf_5_3  = "Capaian tingkat pengangguran terbuka " . $xname . " pada " . $tahunTPT2[5] . " berada " . $dibawahdiatasTPT . " capaian Nasional. Tingkat pengangguran terbuka Nasional pada " . $tahunTPT2[5] . " adalah sebesar " . number_format(end($nilaiData_tpt), 2) . "%. ";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 310,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi5,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_5_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_5_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            $categories_5_1 = $tahun_tpt;
            $series_5_n     = $datay_tpt;
            $series_5_1     = $datay_tpt2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_5_1, $series_5_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_5_1, $series_5_1, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Tingkat Pengangguran Terbuka (%)', $fontgambar, $fontgambar1);
                // $section->addTextBreak();
            }
            $categories_5_2 = $label_data_tpt;
            $series_5_2     = $nilai_data_tpt_per;
            $chart_5_2      = $section->addChart('column', $categories_5_2, $series_5_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Tingkat Pengangguran Terbuka " . $periode_tpt_tahun . " (%)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('6. Indeks Pembangunan Manusia', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal10,
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
                )
            );
            $paragraf_6_2  = "Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " " . $menurunmeningkatIPM . " dibandingkan dengan tahun " . $tahun_ipm2[4] . ". Pada tahun " . $tahun_ipm2[5] . " IPM " . $xname . " adalah sebesar " . number_format(end($nilaiData_ipm2), 2) . " sedangkan pada tahun " . $tahun_ipm2[4] . " IPM tercatat sebesar " . number_format($nilaiData_ipm2[4], 2) . ".";
            $paragraf_6_3  = "Capaian IPM " . $xname . " pada tahun " . $tahun_ipm2[5] . " berada " . $dibawahdiatasIPM . " capaian Nasional. Indeks Pembangunan Manusia Nasional pada tahun " . $tahun_ipm2[5] . " adalah sebesar " . number_format(end($nilaiData_ipm), 2) . ".";

            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi6,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_6_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_6_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            $categories_6_1 = $tahun_ipm;
            $series_6_n     = $datay_ipm;
            $series_6_1     = $datay_ipm2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_6_1, $series_6_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_6_1, $series_6_1, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Indeks Pembangunan Manusia', $fontgambar, $fontgambar1);
            }
            $categories_6_2 = $label_data_ipm;
            $series_6_2     = $nilai_data_ipm_per;
            $chart_6_2      = $section->addChart('column', $categories_6_2, $series_6_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Indeks Pembangunan Manusia Tahun " . $periode_ipm_tahun . " ", $fontgambar, $fontgambar1);
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('7. Rasio Gini', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal11,
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
                )
            );
            $paragraf_7_2  = "Rasio gini " . $xname . " pada " . $tahun_grR[5] . " " . $menurunmeningkatGR . " dibandingkan dengan " . $tahun_grR[3] . ". Pada " . $tahun_grR[5] . " rasio gini " . $xname . " adalah sebesar " . number_format($nilaiData_gr2[5], 3) . " sedangkan pada " . $tahun_grR[3] . " rasio gini tercatat sebesar " . number_format($nilaiData_gr2[3], 3) . ". ";
            $paragraf_7_3  = "Capaian rasio gini " . $xname . " pada " . $tahun_grR[5] . " berada " . $dibawahdiatasGR . " capaian nasional. Rasio gini nasional pada " . $tahun_grR[5] . " adalah sebesar " . number_format($nilaiData_gr[5], 3) . ".";

            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi7,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_7_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_7_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            $categories_7_1 = $tahun_gr;
            $series_7_n     = $datay_gr;
            $series_7_1     = $datay_gr2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_7_1, $series_7_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_7_1, $series_7_1, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Rasio Gini', $fontgambar, $fontgambar1);
            }
            $categories_7_2 = $label_data_gr;
            $series_7_2     = $nilai_data_gr_per;
            $chart_7_2      = $section->addChart('column', $categories_7_2, $series_7_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Rasio Gini " . $periode_gr_tahun . " ", $fontgambar, $fontgambar1);
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('8. Angka Harapan Hidup', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal12,
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
                )
            );
            $paragraf_8_1  = "Angka harapan hidup adalah perkiraan rata-rata tambahan umur seseorang yang diharapkan dapat terus hidup. Angka Harapan Hidup juga dapat didefinisikan sebagai rata-rata jumlah tahun yang dijalani oleh seseorang setelah orang tersebut mencapai ulang tahun yang ke-x. Ukuran yang umum digunakan adalah angka harapan hidup saat lahir yang mencerminkan kondisi kesehatan pada saat itu. Sehingga pada umumnya ketika membicarakan AHH, yang dimaksud adalah rata-rata jumlah tahun yang akan dijalani oleh seseorang sejak orang tersebut lahir.";
            $paragraf_8_2  = "Angka harapan hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " " . $menurunmeningkatAHH . " dibandingkan dengan tahun " . $tahun_ahh[4] . ". Pada tahun " . $tahun_ahh[5] . " angka harapan hidup nasional " . $xname . " adalah sebesar " . number_format(end($nilaiData_ahh2), 2) . " tahun sedangkan pada tahun " . $tahun_ahh[4] . " angka harapan hidup tercatat sebesar " . number_format($nilaiData_ahh2[4], 2) . " tahun.";
            $paragraf_8_3  = "Capaian angka harapan hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " berada " . $dibawahdiatasAHH . " capaian Nasional. Angka harapan hidup nasional pada tahun " . $tahun_ahh[5] . " adalah sebesar " . number_format(end($nilaiData_ahh), 2) . " tahun.";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($paragraf_8_1,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_8_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_8_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            $categories_8_1 = $tahun_ahh;
            $series_8_n     = $datay_ahh;
            $series_8_1     = $datay_ahh2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_8_1, $series_8_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_8_1, $series_8_1, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Angka Harapan Hidup (Tahun)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_8_2 = $label_ahh;
            $series_8_2     = $nilai_ahh_per;
            $chart_8_2      = $section->addChart('column', $categories_8_2, $series_8_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Angka Harapan Hidup Tahun " . $periode_ahh_tahun . " (Tahun)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru RLS
            $section = $phpWord->addSection();
            $section->addText('9. Rata-rata Lama Sekolah', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal13,
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
                )
            );
            $paragraf_9_1  = "Rata-rata lama sekolah merupakan salah satu indikator pembentuk Indeks Pembangunan Manusia dari dimensi pendidikan. Rata-rata lama sekolah menunjukkan jumlah tahun belajar penduduk usia 25 tahun ke atas yang telah diselesaikan dalam pendidikan formal (tidak termasuk tahun yang mengulang). Indikator ini menunjukkan tingkat pendidikan formal dari penduduk di suatu wilayah. Semakin tinggi nilai rata-rata lama sekolah, semakin baik pula tingkat pendidikan di suatu wilayah.";
            $paragraf_9_2  = "Rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " " . $menurunmeningkatRLS . " dibandingkan dengan tahun " . $tahun_rls[4] . ". Pada tahun " . $tahun_rls[5] . " Rata-rata lama sekolah " . $xname . " " . number_format(end($nilaiData_rls2), 2) . " tahun, sedangkan pada tahun " . $tahun_rls[4] . " rata-rata lama sekolah tercatat sebesar " . number_format($nilaiData_rls2[4], 2) . " tahun.";
            $paragraf_9_3  = "Capaian rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " berada " . $dibawahdiatasRLS . " capaian Nasional. Rata-rata lama sekolah nasional pada tahun " . $tahun_rls[5] . " sebesar " . number_format($nilaiData_rls[5], 2) . " tahun.";

            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($paragraf_9_1,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_9_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_9_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            $categories_9_1 = $tahun_rls;
            $series_9_n     = $datay_rls;
            $series_9_1     = $datay_rls2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_9_1, $series_9_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_9_1, $series_9_1, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Rata-rata Lama Sekolah (Tahun)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_9_2 = $label_rls;
            $series_9_2     = $nilai_rls_per;
            $chart_9_2      = $section->addChart('column', $categories_9_2, $series_9_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Rata-rata Lama Sekolah Tahun " . $periode_rls_tahun . " (Tahun)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('10. Harapan Lama Sekolah', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal14,
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
                )
            );
            $paragraf_10_2  = "Harapan lama sekolah " . $xname . " pada tahun " . $tahun_hls[5] . " " . $menurunmeningkatHLS . " dibandingkan dengan tahun " . $tahun_hls[4] . ". Pada tahun " . $tahun_hls[5] . " Harapan Lama Sekolah " . $xname . " adalah sebesar " . number_format(end($nilaiData_hls2), 2) . " tahun, sedangkan pada tahun " . $tahun_hls[4] . " rata-rata lama sekolah tercatat sebesar " . number_format($nilaiData_hls2[4], 2) . " tahun.";
            $paragraf_10_3  = "Capaian harapan lama sekolah " . $xname . " pada tahun " . $tahun_hls[5] . " berada " . $dibawahdiatasHLS . " capaian Nasional. Harapan lama sekolah Nasional pada tahun " . $tahun_hls[5] . " adalah sebesar " . number_format(end($nilaiData_hls), 2) . " tahun.";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi10,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_10_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_10_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            $categories_10_1 = $tahun_hls;
            $series_10_n     = $datay_hls;
            $series_10_1     = $datay_hls2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_10_1, $series_10_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_10_1, $series_10_1, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Harapan Lama Sekolah (Tahun)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_10_2 = $label_data_hls;
            $series_10_2     = $nilai_data_hls_per;
            $chart_10_2      = $section->addChart('column', $categories_10_2, $series_10_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Harapan Lama Sekolah tahun " . $periode_hls_tahun . " (Tahun)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('11. Pengeluaran per Kapita', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal15,
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
                )
            );
            $paragraf_11_2  = "Pengeluaran per kapita " . $xname . " pada tahun " . $tahun_ppk[5] . " " . $menurunmeningkatPPK . " dibandingkan dengan tahun " . $tahun_ppk[4] . ". Pada tahun " . $tahun_ppk[5] . " pengeluaran per kapita " . $xname . " adalah sebesar Rp" . number_format(end($nilaiData_ppk2)) . " sedangkan pada tahun " . $tahun_ppk[4] . " pengeluaran per kapita tercatat sebesar Rp" . number_format($nilaiData_ppk2[4]) . ". ";
            $paragraf_11_3  = "Capaian pengeluaran per kapita " . $xname . " pada tahun " . $tahun_ppk[5] . " berada " . $dibawahdiatasPPK . " capaian nasional. Pengeluaran per kapita Nasional pada tahun " . $tahun_ppk[5] . " adalah sebesar Rp" . number_format(end($nilaiData_ppk)) . " ";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi11,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_11_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_11_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            $categories_11_1 = $tahun_ppk;
            $series_11_n     = $datay_ppk;
            $series_11_1     = $datay_ppk2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_11_1, $series_11_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_11_1, $series_11_1, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Pengeluaran per Kapita (Juta Rupiah)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_11_2 = $label_data_ppk;
            $series_11_2     = $nilai_data_ppk_per;
            $chart_11_2      = $section->addChart('column', $categories_11_2, $series_11_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Pengeluaran per Kapita Tahun " . $periode_ppk_tahun . " (Juta Rupiah)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('12. Tingkat Kemiskinan', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal16,
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
                )
            );
            $paragraf_12_2  = "Tingkat kemiskinan " . $xname . " pada " . $tahun_tkk[5] . " " . $menurunmeningkatTK . " dibandingkan dengan " . $tahun_tkk[3] . ". Pada " . $tahun_tkk[5] . " Angka tingkat kemiskinan " . $xname . " adalah sebesar " . number_format(end($nilaiData_tk2), 2) . "%, sedangkan pada " . $tahun_tkk[3] . " Angka tingkat Kemiskinan tercatat sebesar " . number_format($nilaiData_tk2[3], 2) . "%. ";
            $paragraf_12_3  = "Capaian angka tingkat Kemiskinan " . $xname . " pada " . $tahun_tkk[5] . " berada " . $dibawahdiatasTK . " capaian Nasional. Angka tingkat kemiskinan Nasional pada " . $tahun_tkk[5] . " adalah sebesar " . number_format(end($nilaiData_tk), 2) . "%.";

            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 125,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi12,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_12_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_12_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            $categories_12_1 = $tahun_tk;
            $series_12_n     = $datay_tk;
            $series_12_1     = $datay_tk2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_12_1, $series_12_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_12_1, $series_12_1, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Tingkat Kemiskinan (%)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_12_2 = $label_data_tk;
            $series_12_2     = $nilai_data_tk_per;
            $chart_12_2      = $section->addChart('column', $categories_12_2, $series_12_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Tingkat Kemiskinan " . $periode_tk_tahun . " (%)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('13. Indeks Kedalaman Kemiskinan (P1)', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal17,
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
                )
            );
            $tulisannormal       = array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10);
            $tulisanfontmiring   = array('name' => 'Book Antiqua (Body)', 'size' => 10, 'spaceAfter' => 80, 'italic' => true);


            $paragraf_13_1_2 = "), merupakan ukuran rata-rata kesenjangan pengeluaran masing-masing penduduk miskin terhadap garis kemiskinan. Semakin tinggi nilai indeks kedalaman kemiskinan, semakin jauh rata-rata pengeluaran penduduk miskin dari garis kemiskinan.";
            $paragraf_13_2 = "Indeks kedalaman kemiskinan " . $xname . " pada " . $tahunIDK1[5] . " " . $menurunmeningkatIKK . " dibandingkan dengan " . $tahunIDK1[3] . ". Pada " . $tahunIDK1[5] . " indeks kedalaman kemiskinan " . $xname . " adalah sebesar " . number_format(end($nilaiData_idk2), 2) . ", sedangkan pada " . $tahunIDK1[3] . " indeks kedalaman kemiskinan tercatat sebesar " . number_format($nilaiData_idk2[3], 2) . ". ";
            $paragraf_13_3 = "Capaian indeks kedalaman kemiskinan " . $xname . " pada " . $tahunIDK1[5] . " berada " . $dibawahdiatasIKK . " capaian Nasional. Indeks kedalaman kemiskinan Nasional pada " . $tahunIDK1[5] . " adalah sebesar " . number_format(end($nilaiData_idk), 2) . ".";
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
                'alignment' => 'both',
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
            //$textbox->addText($textbox1);
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
            $textbox->addText($paragraf_13_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_13_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            $categories_13_1 = $tahun_idk;
            $series_13_n     = $datay_idk;
            $series_13_1     = $datay_idk2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_13_1, $series_13_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_13_1, $series_13_1, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Indeks Kedalaman Kemiskinan', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }
            $categories_13_2 = $label_data_ikk;
            $series_13_2     = $nilai_data_ikk_per;
            $chart_13_2      = $section->addChart('column', $categories_13_2, $series_13_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Indeks Kedalaman Kemiskinan " . $periode_ikk_tahun . "", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('14. Indeks Keparahan Kemiskinan (P2)', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal18,
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
                )
            );
            $paragraf_15_2 = "Indeks keparahan kemiskinan " . $xname . " pada " . $tahunIKK1[5] . " " . $menurunmeningkatIKKK . " dibandingkan dengan " . $tahunIKK1[3] . ". Pada " . $tahunIKK1[5] . " indeks keparahan kemiskinan " . $xname . " adalah sebesar " . number_format(end($nilaiData_ikk2), 2) . ", sedangkan pada " . $tahunIKK1[3] . " indeks keparahan kemiskinan tercatat sebesar " . number_format($nilaiData_ikk2[3], 2) . ". ";
            $paragraf_15_3 = "Capaian indeks keparahan kemiskinan " . $xname . " pada " . $tahunIKK1[5] . " berada " . $dibawahdiatasIKKK . " capaian Nasional. Indeks keparahan kemiskinan Nasional pada " . $tahunIKK1[5] . " adalah sebesar " . number_format(end($nilaiData_ikk), 2) . ".";

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
                'alignment' => 'both',
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
            $textbox->addText($paragraf_15_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_15_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            $categories_15_1 = $tahun_ikk;
            $series_15_n     = $datay_ikk;
            $series_15_1     = $datay_ikk2;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_15_1, $series_15_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_15_1, $series_15_1, $xname);
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Indeks Keparahan Kemiskinan', $fontgambar, $fontgambar1);
                //                $section->addTextBreak();
            }
            $categories_15_2 = $label_data_ikkk;
            $series_15_2     = $nilai_data_ikkk_per;
            $chart_15_2      = $section->addChart('column', $categories_15_2, $series_15_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Indeks Keparahan Kemiskinan " . $periode_ikkk_tahun . "", $fontgambar, $fontgambar1);
            //            $section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('15. Jumlah Penduduk Miskin', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal19,
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
                )
            );
            $paragraf_14_2    = "Jumlah penduduk miskin " . $xname . " pada " . $tahun_jpk[5] . " sebanyak " . number_format($nilaiData_jpk22[5], 0) . " orang sedangkan jumlah penduduk miskin pada " . $tahun_jpk[3] . " sebanyak " . number_format($nilaiData_jpk22[3], 0) . " orang. "
                . "Selama periode " . $tahun_jpk[3] . " - " . $tahun_jpk[5] . " jumlah penduduk miskin di " . $xname . " " . $berkurangbertambah . " sebanyak " . number_format($rt_jpkk, 0) . " orang atau sebesar " . $rt_jpk33 . "%. "
                . "Jumlah Penduduk Miskin nasional pada " . $tahun_jpk[5] . " sebesar " . number_format($nilaiData_jpk[5], 0) . " orang.";

            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 55,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi14,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_14_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));


            $categories_14_1 = $tahun_jpk21;
            $series_14_1     = $datay_jpk2;
            $chart = $section->addChart('column', $categories_14_1, $series_14_1, $style_2, $xname);
            $section->addText('Gambar ' . $gambar++ . '. Perkembangan Jumlah Penduduk Miskin (Orang)', $fontgambar, $fontgambar1);
            //$section->addTextBreak();

            $categories_14_2 = $label_data_jpk;
            $series_14_2     = $nilai_data_jpk_per;
            $chart_14_2      = $section->addChart('column', $categories_14_2, $series_14_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Jumlah Penduduk Miskin " . $periode_jpk_tahun . " (Orang)", $fontgambar, $fontgambar1);
            //            $section->addTextBreak();
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            $filename = $xname . '.docx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); //mime type
            header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
            header('Cache-Control: max-age=0'); //no cache
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('php://output');
        } elseif ($provinsi != '' & $kabupaten != '') {

            $style       = array('width' => Converter::cmToEmu(16), 'height'         => Converter::cmToEmu(6), 'showAxisLabels' => true, 'showGridX'      => false, 'showGridY'      => false, 'showLegend'     => true, 'valueLabelPosition'     => FALSE,);
            $style_1     = array('width' => Converter::cmToEmu(16), 'height'         => Converter::cmToEmu(6), 'showAxisLabels' => true,);
            $style_g1       = array(
                'width' => Converter::cmToEmu(16),
                'height' => Converter::cmToEmu(6),
                'showAxisLabels' => true,
                'showGridX'      => false,
                'showGridY'      => false,
                'showLegend'     => false,
                'valueLabelPosition'     => FALSE,
                'showSerName'      => false,
            );

            $style_g2       = array(
                'width' => Converter::cmToEmu(16),
                'height' => Converter::cmToEmu(6),
                'showAxisLabels' => true,
                'showGridX'      => false,
                'showGridY'      => false,
                'showLegend'     => false,
                'valueLabelPosition'     => FALSE,
                'showSerName'      => false,
                'showCatName'      => false, // category name

            );

            $style_1_jp = array(
                'width'          => Converter::cmToEmu(16),
                'height'         => Converter::cmToEmu(2),
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
            $style_2_jp = array(
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

            $sql_pro1 = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`id`='" . $pro . "' ";
            $list_data1 = $this->db->query($sql_pro1);
            foreach ($list_data1->result() as $Lis_pro1) {
                $id_pro = $Lis_pro1->id;
                $xname  = $Lis_pro1->nama_provinsi;
                $label_pe = $Lis_pro1->label;
            }
            $sql_pro = "SELECT K.`id`, K.`nama_kabupaten`, K.`prov_id`, K.`ppd`, K.`urutan`, P.id, P.nama_provinsi 
            FROM `kabupaten` K
            LEFT JOIN provinsi P ON P.id = K.`prov_id`
            WHERE K.id = '" . $kab . "' ";
            $list_data = $this->db->query($sql_pro);
            foreach ($list_data->result() as $Lis_pro) {
                $xnameKab = $Lis_pro->nama_kabupaten;
                $query = "1000";
                $id_kab = $Lis_pro->id;
                $judul = $Lis_pro->nama_kabupaten;
                $judul1 = $xname;
            }

            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $src = base_url("assets/images/laporan/cover_master_v4.jpg");
            $logo = base_url("assets/images/logopropinsi/" . $kab . ".png");

            $section = $phpWord->addSection(array(
                'headerHeight' => 50,
                'footerHeight' => 3000
            ));
            $header = $section->addHeader();
            $header->addWatermark(
                $src,
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
            $fontStyle->setSize(26);
            $fontStyle->setColor('#bfbfbf');

            $section->addTextBreak(18);
            $section->addImage(
                $logo,
                array(
                    'width' => 119,
                    'height' => 120,
                    'posHorizontal' => 'absolute',
                    'posVertical' => 'absolute',
                )
            );
            $section->addTextBreak(5);

            $myTextElement = $section->addText($judul);
            $myTextElement->setFontStyle($fontStyle, array(
                'alignment' => 'right',
                'marginRight' => -70,
            ));

            //fontStyle
            //fontStyle Judul Indikator
            $fontStyleNameJudul = '';
            $phpWord->addFontStyle($fontStyleNameJudul, array('name' => 'Tahoma', 'size' => 12, 'color' => '1B2232', 'bold' => true));
            //fontStyle Char gambar
            $fontStyleNameGambar = '';
            $phpWord->addFontStyle($fontStyleNameGambar, array('name' => 'Arial', 'size' => 10, 'bold' => true), array('alignment' => 'center'));

            $fontparagraf       = array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10);
            $fontmiring   = array('name' => 'Book Antiqua (Body)', 'size' => 11, 'spaceAfter' => 80, 'italic' => true);

            $fontgambar = array('name' => 'Book Antiqua (Body)', 'size' => 9, 'bold' => true);
            $fontgambar1 = array('alignment' => 'center');

            // Halaman Baru
            $section = $phpWord->addSection();
            $header = $section->addHeader();
            $header->addWatermark(
                base_url("assets/images/header.png"),
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
            $phpWord->addFontStyle($fontStyleName, array('name' => 'Arial Narrow', 'size' => 14, 'color' => '1B2232', 'bold' => true));
            $style = array(
                'width'          => Converter::cmToEmu(16), 'height'         => Converter::cmToEmu(6),
                'showAxisLabels' => true, 'showGridX'      => false, 'showGridY'      => false,
                'showLegend'     => true, 'valueLabelPosition'     => FALSE,
            );

            $section = $phpWord->addSection(array('colsNum' => 1, 'breakType' => 'continuous'));
            $chartTypes = array('line');
            $chartTypes2 = array('column');
            $twoSeries = array('bar', 'column', 'line', 'area', 'scatter', 'radar', 'stacked_bar', 'percent_stacked_bar', 'stacked_column', 'percent_stacked_column');
            $threeSeries = array('bar', 'line');



            //Kata Pengantar
            $section->addText('Kata Pengantar',  array('name' => 'Arial', 'spaceAfter' => 80, 'size' => 16, 'bold' => true), array('alignment' => 'center'));
            $phpWord->addParagraphStyle('pStyler', array('alignment' => 'both'));
            $textrun = $section->addTextRun('pStyler');
            $textrun->addText(htmlspecialchars("     Pemantauan merupakan salah satu tahapan penting dalam pengendalian pelaksanaan rencana. Kegiatan Pemantauan Pembangunan dilakukan dalam rangka mengendalikan kesesuaian pelaksanaan pembangunan dengan tahapan dan target yang telah direncanakan. Dalam melakukan pemantauan pembangunan, Direktorat Pemantauan, Evaluasi, dan Pengendalian Pembangunan Daerah (PEPPD), Bappenas telah mengembangkan aplikasi "), $fontparagraf);
            $textrun->addText(htmlspecialchars('Dashboard '), $fontmiring);
            $textrun->addText(htmlspecialchars("Pemantauan Pembangunan Daerah yang berguna untuk mendukung kegiatan pemantauan pembangunan dan mendukung proses penilaian kegiatan Penghargaan Pembangunan Daerah. Capaian sasaran pokok pembangunan didokumentasikan secara digital pada aplikasi "), $fontparagraf);
            $textrun->addText(htmlspecialchars('Dashboard '), $fontmiring);
            $textrun->addText(htmlspecialchars("Pemantauan Pembangunan Daerah mulai dari tingkat nasional, provinsi, kabupaten, dan kota."), $fontparagraf);
            $textrun1 = $section->addTextRun('pStyler');
            $textrun1->addText(htmlspecialchars("     Terdapat beberapa fitur dalam "), $fontparagraf);
            $textrun1->addText(htmlspecialchars('Dashboard '), $fontmiring);
            $textrun1->addText(htmlspecialchars("Pemantauan Pembangunan Daerah. Salah satu diantaranya adalah untuk melihat Laporan Perkembangan Indikator Makro Pembangunan pada setiap provinsi dan kabupaten/kota. Laporan Perkembangan Indikator Makro Pembangunan berisi tentang pencapaian dari indikator makro beserta komposit ataupun turunan indikator tersebut di dalam bidang ekonomi, kemiskinan, pengangguran, rasio gini, dan Indeks Pembangunan Manusia. Angka pencapaian tersebut dikompilasi berdasarkan hasil publikasi dari Badan Pusat Statistik mengenai indikator makro terkait."), $fontparagraf);
            $textrun2 = $section->addTextRun('pStyler');
            $textrun2->addText(htmlspecialchars("     Kami ucapkan terima kasih dan penghargaan kepada seluruh pihak yang telah membantu dalam penyusunan laporan ini. Masukan, saran dan kritik yang membangun kami harapkan untuk perbaikan dan penyempurnaan di masa yang akan datang."), $fontparagraf);
            $section->addTextBreak(2);
            $phpWord->addParagraphStyle('pStyler2', array('align' => 'right'));
            $textrun3 = $section->addTextRun('pStyler2');
            $textrun3->addText(htmlspecialchars("Jakarta,      Maret 2023"), $fontparagraf);
            $textrun4 = $section->addTextRun('pStyler2');
            $textrun4->addText(htmlspecialchars("Direktur Pemantauan, Evaluasi dan"), $fontparagraf);
            $textrun5 = $section->addTextRun('pStyler2');
            $textrun5->addText(htmlspecialchars("Pengendalian Pembangunan Dareah"), $fontparagraf);
            $section->addTextBreak(2);
            $textrun6 = $section->addTextRun('pStyler2');
            $textrun6->addText(htmlspecialchars("Agustin Arry Yanna"), $fontparagraf);


            //daftar isi
            $section = $phpWord->addSection();
            $section->addText('DAFTAR ISI',  array('name' => 'Arial', 'spaceAfter' => 80, 'size' => 16, 'bold' => true), array('alignment' => 'center'));
            $section->addText('' . $daftarisi++ . '. Kata Pengantar...................................................................................................................................2');
            $section->addText('' . $daftarisi++ . '. Daftar isi..............................................................................................................................................3');
            $section->addText('' . $daftarisi++ . '. Daftar gambar.....................................................................................................................................4');
            $section->addText('' . $daftarisi++ . '. Pertumbuhan ekonomi........................................................................................................................5');
            $section->addText('' . $daftarisi++ . '. Perkembangan PDRB per kapita ADHB.............................................................................................7');
            $section->addText('' . $daftarisi++ . '. Perkembangan PDRB per kapita ADHK tahun dasar 2010................................................................9');
            $section->addText('' . $daftarisi++ . '. Perkembangan jumlah penganggur..................................................................................................11');
            $section->addText('' . $daftarisi++ . '. Tingkat pengangguran terbuka.........................................................................................................12');
            $section->addText('' . $daftarisi++ . '. Tingkat kemiskinan...........................................................................................................................14');
            $section->addText('' . $daftarisi++ . '. Indeks kedalaman kemiskinan (P1) ...............................................................................................16');
            $section->addText('' . $daftarisi++ . '. Indeks keparahan kemiskinan (P2) ...............................................................................................17');
            $section->addText('' . $daftarisi++ . '. Jumlah penduduk miskin................................................................................................................18');
            $section->addText('' . $daftarisi++ . '. Indeks pembangunan manusia.......................................................................................................19');
            $section->addText('' . $daftarisi++ . '. Gini rasio.........................................................................................................................................21');


            //daftar gambar 
            $section = $phpWord->addSection();
            $section->addText('DAFTAR GAMBAR',  array('name' => 'Arial', 'spaceAfter' => 80, 'size' => 16, 'bold' => true), array('alignment' => 'center'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Pertumbuhan Ekonomi................................................................................5');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Pertumbuhan Ekonomi .................................................................................6');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan PDRB per Kapita ADHB..............................................................................7');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Perkembangan PDRB per kapita ADHB ......................................................8');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan PDRB per kapita ADHK Tahun Dasar 2010................................................9');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan PDRB per kapita ADHK Tahun Dasar 2010 ...............................................10');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Jumlah Penganggur..................................................................................11');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Jumlah Penganggur ...................................................................................11');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Tingkat Pengangguran Terbuka................................................................12');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Tingkat Pengangguran Terbuka ...............................................................13');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Tingkat Kemiskinan.................................................................................14');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Tingkat Kemiskinan ..................................................................................15');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Indeks Kedalaman Kemiskinan (P1) ......................................................16');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Indeks Kedalaman Kemiskinan (P1) ........................................................16');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Indeks Keparahan Kemiskinan (P2) ...........…........................................17');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Indeks Keparahan Kemiskinan (P2) .........................................................17');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Jumlah Penduduk Miskin........................................................................18');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Jumlah Penduduk Miskin..........................................................................18');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Indeks Pembangunan Manusia ..............................................................19');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Indeks Pembangunan Manusia ................................................................20');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Gini Rasio ...............................................................................................21');
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Gini Rasio .................................................................................................22');


            //Pertumbuhan Ekonomi
            $sql_ppe = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppe = $this->db->query($sql_ppe);
            foreach ($list_ppe->result() as $row_ppe) {
                $nilaiData_ppe[] = (float)$row_ppe->nilai;
                $nilai_ppe_n[$row_ppe->tahun] = (float)$row_ppe->nilai;
                $tahun_ppe[]    = $row_ppe->tahun;
                $idperiode_ppe[] = $row_ppe->id_periode;
                $periode = $row_ppe->periode;
                if ($periode == '00') {
                    $thn[] = $row_ppe->tahun;
                } else {
                    $thn[] =  $prde[$row_ppe->periode] . " - " . $row_ppe->tahun;
                }
            }
            $datay_ppe = $nilaiData_ppe;
            $tahun_ppe = $tahun_ppe;
            $tahun_ppe_c = $thn;
            $periode_kab_ppe_max = max($idperiode_ppe);

            $sql_ppe2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
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
                                            where (id_indikator='1' AND wilayah='" . $kab . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='1' AND wilayah='" . $kab . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";

            $list_ppe3 = $this->db->query($sql_ppe3);
            foreach ($list_ppe3->result() as $row_ppe3) {
                $sumber_ppe = $row_ppe3->sumber_k;
                $n_ppe3 = $row_ppe3->nilai_kab;
                if ($n_ppe3 == 0) {
                    $nilaiData_kppe3 = '#N/A';
                } else {
                    $nilaiData_kppe3 = number_format((float)$n_ppe3, 2);
                }
                $nilaiData_ppe3[] = $nilaiData_kppe3;
                $nilaiData_ppe33[$row_ppe3->tahun] = $nilaiData_kppe3;
                $datay_ppe33[] = $row_ppe3->nilai_kab;
                $tahun_ppe3[] = $row_ppe3->tahun;
                $periode_ppe3[] = $row_ppe3->periodekab;
            }

            $datay_ppe3 = array_reverse($nilaiData_ppe3);
            $tahun_ppe3 = $tahun_ppe3;
            $tahun_kab_ppe_max = max($tahun_ppe3);
            $tahun_kab_ppe_ke2 = $tahun_kab_ppe_max - 1;
            $periode_kab_ppe_max = max($periode_ppe3);

            if ($nilaiData_ppe33[$tahun_kab_ppe_ke2] > $nilaiData_ppe33[$tahun_kab_ppe_max]) {
                $menurunmeningkatPE = 'menurun';
                if ($nilai_ppe2_pro[$tahun_kab_ppe_max] > $nilaiData_ppe33[$tahun_kab_ppe_max]) {
                    $dibawahdiatasPE = 'dibawah';
                } else {
                    $dibawahdiatasPE = 'diatas';
                }
            } else {
                $menurunmeningkatPE = 'meningkat';
                if ($nilai_ppe2_pro[$tahun_kab_ppe_max] > $nilaiData_ppe33[$tahun_kab_ppe_max]) {
                    $dibawahdiatasPE = 'dibawah';
                } else {
                    $dibawahdiatasPE = 'diatas';
                }
            }

            if ($nilai_ppe_n[$tahun_kab_ppe_max] > $nilaiData_ppe33[$tahun_kab_ppe_max]) {
                $dibawahdiatasNPE = 'dibawah';
            } else {
                $dibawahdiatasNPE = 'diatas';
            }

            $paragraf_1_2  = "Pertumbuhan Ekonomi " . $xnameKab . " pada tahun " . $tahun_kab_ppe_max . " " . $menurunmeningkatPE . " dibandingkan dengan tahun " . $tahun_kab_ppe_ke2 . ". Pada " . $tahun_kab_ppe_max . " pertumbuhan ekonomi " . $xnameKab . " adalah sebesar " . $nilaiData_ppe33[$tahun_kab_ppe_max] . "%, sedangkan pada tahun " . $tahun_kab_ppe_ke2 . " pertumbuhan tercatat sebesar " . $nilaiData_ppe33[$tahun_kab_ppe_ke2] . "%. ";
            $paragraf_1_3  = "Pertumbuhan ekonomi " . $xnameKab . " pada tahun " . $tahun_kab_ppe_max . " berada " . $dibawahdiatasPE . " capaian " . $xname . ". Pertumbuhan ekonomi " . $xname . " pada tahun " . $tahun_kab_ppe_max . " adalah sebesar " . $nilai_ppe2_pro[$tahun_kab_ppe_max] . "%. ";
            $paragraf_1_4  = "Capaian pertumbuhan ekonomi " . $xnameKab . " pada tahun " . $tahun_kab_ppe_max . " berada " . $dibawahdiatasNPE . " nasional. Pertumbuhan ekonomi nasional pada tahun " . $tahun_kab_ppe_max . " adalah sebesar " . $nilai_ppe_n[$tahun_kab_ppe_max] . "% ";

            $tahun_pe_max = $tahun_kab_ppe_max . "";
            $catdata_kab = array();
            $ppe_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='1' AND e.id_periode='" . $periode_kab_ppe_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='1' AND id_periode='" . $periode_kab_ppe_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";

            $list_kab_ppe_per = $this->db->query($ppe_kab);
            foreach ($list_kab_ppe_per->result() as $row_ppe_kab_per) {
                $label_ppe[]     = $row_ppe_kab_per->label;
                $nilai_ppe_per[] = $row_ppe_kab_per->nilai;
                $posisi_ppe = strpos($row_ppe_kab_per->label, "Kabupaten");
                if ($posisi_ppe !== FALSE) {
                    $label_ppe11 = substr($row_ppe_kab_per->label, 0, 3) . " " . substr($row_ppe_kab_per->label, 10);
                } else {
                    $label_ppe11 = $row_ppe_kab_per->label;
                }
                $label_ppe1[] = $label_ppe11;

                $label_pe1_k[$row_ppe_kab_per->label] = $row_ppe_kab_per->nilai;
                $tahun_p_k        = $bulan[$row_ppe_kab_per->periode] . "" . $row_ppe_kab_per->tahun;
                $nilai_ppe_kab[]  = (float)$row_ppe_kab_per->nilai;
                //$label_lpe_k[$label_adhb11]    = $row_ppe_kab_per->nilai;
            }
            $label_data_ppe     = $label_ppe1;
            $nilai_data_ppe_per = $nilai_ppe_per;

            $label_pe_k1          = $label_ppe1;
            $nilaiData_k['name'] = $tahun_p_k;
            $nilaiData_k['data'] = $nilai_ppe_kab;

            $ranking = $label_pe1_k;
            arsort($ranking);

            array_push($catdata_kab, $nilaiData_k);
            $nilai_ppe_per_kab_max = max($label_pe1_k);
            $nilai_ppe_per_kab_min = min($label_pe1_k);
            arsort($nilai_ppe_per);
            $nama_k1 = $label_pe1_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_pe = $nilai_ppe_per_kab_max - $nilai_ppe_per_kab_min;

            $nrk = 1;
            foreach ($nama_k1 as $xk => $xk_value) {
                if ($xk == $judul) {
                    $rengking_pro_k = $nrk++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $pe_perbandingan_kab = "Perbandingan pertumbuhan ekonomi antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada tahun " . $tahun_p_k . " daerah dengan tingkat pertumbuhan ekonomi tertinggi adalah " . array_shift($nama_k2) . " (" . $nilai_ppe_per_kab_max . "%), sedangkan daerah dengan pertumbuhan ekonomi terendah adalah " . end($nama_k2) . " (" . $nilai_ppe_per_kab_min . "%). " . $xnameKab . " berada pada urutan ke " . $rengking_pro_k . ".";

            // Halaman Baru
            $section = $phpWord->addSection();
            $header = $section->addHeader();
            $header->addWatermark(
                base_url("assets/images/header.png"),
                array(
                    'headerHeight' => 300, 'marginTop' => -36, 'marginLeft' => -70, 'footerHeight' => -50, 'width' => 590,
                    'posHorizontal' => 'absolute', 'posVertical' => 'absolute',
                )
            );
            $footer = $section->addFooter();
            $textRun = $footer->addTextRun(array('alignment' => 'center'));
            $textRun->addField('PAGE', array('format' => 'NUMBER'));
            $textRun->addText('');


            $section = $phpWord->addSection(array('colsNum' => 1, 'breakType' => 'continuous'));

            $chartTypes  = array('line');
            $chartTypes2 = array('column');
            $twoSeries   = array('bar', 'column', 'line', 'area', 'scatter', 'radar', 'stacked_bar', 'percent_stacked_bar', 'stacked_column', 'percent_stacked_column');
            $threeSeries = array('bar', 'line');

            $section->addText('' . $nomor++ . '. Pertumbuhan Ekonomi', $fontStyleNameJudul);
            $section->addText($deskripsi1, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_1_2, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_1_3, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_1_4, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            //$section->addTextBreak();

            $categories = $tahun_ppe_c;
            $series1    = $datay_ppe;
            $series2    = $datay_ppe2;
            $series3    = $datay_ppe3;
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Pertumbuhan Ekonomi (%)', array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
                $chart = $section->addChart($chartType, $categories, $series1, $style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories, $series2, $xname);
                    $chart->addSeries($categories, $series3, $judul);
                }
                //$section->addTextBreak();
            }
            $section = $phpWord->addSection();
            $section->addText($pe_perbandingan_kab, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            //$section->addTextBreak();

            $categories_1_2 = $label_data_ppe;
            $series_1_2     = $nilai_data_ppe_per;
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Pertumbuhan Ekonomi Tahun " . $tahun_pe_max . " Antar Kabupaten/Kota Di $xname (%)", array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
            $chart = $section->addChart('column', $categories_1_2, $series_1_2, $style_1);
            $section->addText('Sumber Data: Badan Pusat Statistik', array('name' => 'Tahoma', 'size' => 10));

            //Perkembangan PDRB Per Kapita ADHB
            $sql_adhb = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhb = $this->db->query($sql_adhb);
            foreach ($list_adhb->result() as $row_adhb) {
                $tahun_adhb[]                       = $row_adhb->tahun;
                $nilaiData_adhb1[]                  = number_format((float)$row_adhb->nilai / 1000000, 2);
                $nilaiData_max[]            = (float)$row_adhb->nilai;
                $nilaiData_maxx[$row_adhb->tahun]   = (float)$row_adhb->nilai;
            }
            $datay_adhb1 = $nilaiData_adhb1;
            $tahun_adhb1 = $tahun_adhb;
            $sql_adhb2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhb2 = $this->db->query($sql_adhb2);
            foreach ($list_adhb2->result() as $row_adhb2) {
                $tahun_adhb2[]   = $row_adhb2->tahun;
                $nilaiData_adhb2[] = number_format((float)$row_adhb2->nilai / 1000000, 2);
                $nilaiData_max_p[]   = (float)$row_adhb2->nilai;
                $nilaiD_adhb2[$row_adhb2->tahun] = (float)$row_adhb2->nilai;
                $ket_adhb2[]  = $row_adhb2->keterangan;
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
                                        where (id_indikator='2' AND wilayah='" . $kab . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='2' AND wilayah='" . $kab . "' group by id_periode
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
                $periode_kab_adhb[] = $row_adhb3->idperiode;
                $n_adhb3          = (float)$row_adhb3->nilai_kab;
                if ($n_adhb3 == 0) {
                    $nilaiDataadhb3 = '';
                } else {
                    $nilaiDataadhb3 = number_format((float)$row_adhb3->nilai_kab / 1000000, 2);
                }
                $nilaiData_adhb3[] = $nilaiDataadhb3;
            }
            $datay_adhb3 = array_reverse($nilaiData_adhb3);
            $dataX_adhb3 = array_reverse($tahun_adhb3);
            $tahunadhb    = end($tahun_adhb1);
            $periode_kab_adhb_max = max($periode_kab_adhb);
            $periode_adhb_tahun = max($tahun_adhb3) . "";
            $tahun_kab_adhb_max = max($tahun_adhb3);
            $tahun_kab_adhb_ke2 = $tahun_kab_adhb_max - 1;

            if ($nilaiD_adhb3[$tahun_kab_adhb_ke2] > $nilaiD_adhb3[$tahun_kab_adhb_max]) {
                $menurunmeningkatADHB = 'menurun';
                if ($nilaiD_adhb2[$tahun_kab_adhb_max] > $nilaiD_adhb3[$tahun_kab_adhb_max]) {
                    $dibawahdiatasADHB = 'dibawah';
                } else {
                    $dibawahdiatasADHB = 'diatas';
                }
            } else {
                $menurunmeningkatADHB = 'meningkat';
                if ($nilaiD_adhb2[$tahun_kab_adhb_max] > $nilaiD_adhb3[$tahun_kab_adhb_max]) {
                    $dibawahdiatasADHB = 'dibawah';
                } else {
                    $dibawahdiatasADHB = 'diatas';
                }
            }
            if ($nilaiData_maxx[$tahun_kab_adhb_max] > $nilaiD_adhb3[$tahun_kab_adhb_max]) {
                $dibawahdiatasADHBN = 'dibawah';
            } else {
                $dibawahdiatasADHBN = 'diatas';
            }
            $paragraf_2_2  = "PDRB perkapita ADHB " . $xnameKab . " pada tahun " . $tahun_kab_adhb_max . " " . $menurunmeningkatADHB . " dibandingkan dengan tahun " . $tahun_kab_adhb_ke2 . ". Pada tahun " . $tahun_kab_adhb_max . " PDRB per kapita ADHB " . $xnameKab . " adalah sebesar Rp" . number_format($nilaiD_adhb3[$tahun_kab_adhb_max], 0) . ", sedangkan pada tahun " . $tahun_kab_adhb_ke2 . " PDRB per kapita ADHB tercatat sebesar Rp " . number_format($nilaiD_adhb3[$tahun_kab_adhb_ke2], 0) . ". ";
            $paragraf_2_3  = "PDRB per kapita ADHB " . $xnameKab . " pada tahun " . $tahun_kab_adhb_max . " berada " . $dibawahdiatasADHB . " capaian " . $xname . ". PDRB perkapita ADHB " . $xname . " pada tahun " . $tahun_kab_adhb_max . " adalah sebesar Rp" . number_format($nilaiD_adhb2[$tahun_kab_adhb_max], 0) . ".";
            $paragraf_2_4  = "Capaian PDRB perkapita ADHB " . $xnameKab . " pada tahun " . $tahun_kab_adhb_max . " berada " . $dibawahdiatasADHBN . " nasional. PDRB perkapita ADHB nasional pada tahun " . $tahun_kab_adhb_max . " adalah sebesar Rp" . number_format($nilaiData_maxx[$tahun_kab_adhb_max], 0) . ".";


            $adhb2_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='2' AND e.id_periode='" . $periode_kab_adhb_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='2' AND id_periode='" . $periode_kab_adhb_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";

            $list_kab_adhb_per = $this->db->query($adhb2_kab);
            foreach ($list_kab_adhb_per->result() as $row_adhb_kab_per) {
                $label_adhb[]     = $row_adhb_kab_per->label;
                $nilai_adhb_per[] = number_format($row_adhb_kab_per->nilai / 1000000, 2);
                $posisi = strpos($row_adhb_kab_per->label, "Kabupaten");
                if ($posisi !== FALSE) {
                    $label_adhb11 = substr($row_adhb_kab_per->label, 0, 3) . " " . substr($row_adhb_kab_per->label, 10);
                } else {
                    $label_adhb11 = $row_adhb_kab_per->label;
                }
                $label_adhb1[]                  = $label_adhb11;
                $tahun_adhb_k                   = $row_adhb_kab_per->tahun;
                $nilai_adhb_kab[]               = (float)$row_adhb_kab_per->nilai / 1000000;
                $label_adhb_k[$row_adhb_kab_per->label]    = $row_adhb_kab_per->nilai;
                $nilai_adhb_per1[]              = $row_adhb_kab_per->nilai;
            }
            $label_data_adhb     = $label_adhb1;
            $nilai_data_adhb_per = $nilai_adhb_per;


            $ranking = $label_adhb_k;
            arsort($ranking);

            $nilai_adhb_per_kab_max = max($label_adhb_k);
            $nilai_adhb_per_kab_min = min($label_adhb_k);
            arsort($nilai_adhb_per1);
            $nama_k1 = $label_adhb_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_adhb = $nilai_adhb_per_kab_max - $nilai_adhb_per_kab_min;

            $nrk = 1;
            foreach ($nilai_adhb_per1 as $xk => $xk_value) {
                if ($xk == $judul) {
                    $rengking_pro_k = $nrk++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $adhb_perbandingan_kab = "Perbandingan perkembangan PDRB perkapita ADHB antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada tahun " . $tahun_adhb_k . " daerah dengan tingkat perkembangan PDRB perkapita ADHB tertinggi adalah " . array_shift($nama_k2) . " Rp " . number_format($nilai_adhb_per_kab_max, 0) . ", sedangkan daerah dengan perkembangan PDRB perkapita ADHB terendah adalah " . end($nama_k2) . " Rp " . number_format($nilai_adhb_per_kab_min, 0) . ". Selisih perkembangan PDRB perkapita ADHB tertinggi dan terendah di " . $xname . " pada tahun " . $tahun_adhb_k . " adalah sebesar Rp" . number_format($selisih_adhb, 0) . ". " . $judul . " berada pada urutan " . $rengking_pro_k . ". ";

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('' . $nomor++ . '. Perkembangan PDRB per Kapita ADHB', $fontStyleName);
            $section->addText($deskripsi2, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_2_2, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_2_3, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            //            $section->addTextBreak();
            $section->addText($paragraf_2_4, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));

            $section->addTextBreak();
            $categories_2_2 = $tahun_adhb;
            $series_2_n     = $nilaiData_adhb1;
            $series_2_2     = $datay_adhb2;
            $series_2_3     = $datay_adhb3;
            // foreach ($chartTypes2 as $chartTypes22) {
            //     $section->addText('Gambar ' . $gambar++ . ' Perkembangan PDRB per Kapita ADHB Tahun ' . $tahun_adhb_k . ' Antar Kabupaten/Kota Di ' . $xname . ' (Juta Rupiah)', array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
            //     $chart = $section->addChart($chartTypes22, $categories_2_2, $series_2_n, $style_g1, 'Nasional');
            //     //$chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));

            //     if (in_array($chartTypes22, $twoSeries)) {
            //         $chart->addSeries($categories_2_2, $series_2_2, $xname);
            //         $chart->addSeries($categories_2_2, $series_2_3, $judul);
            //     }
            //     $section->addTextBreak();
            // }
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan PDRB per Kapita ADHB Tahun (Juta Rupiah)', array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
                $chart = $section->addChart($chartType, $categories_2_2, $series_2_n, $style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_2_2, $series_2_2, $xname);
                    $chart->addSeries($categories_2_2, $series_2_3, $judul);
                }
                //$section->addTextBreak();
            }
            $section = $phpWord->addSection();
            $section->addText($adhb_perbandingan_kab, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addTextBreak();
            $categories_2_3 = $label_data_adhb;
            $series_2_3     = $nilai_data_adhb_per;
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Perkembangan PDRB per kapita ADHB Tahun " . $periode_adhb_tahun . " (Juta Rupiah)", array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
            $chart_2_3      = $section->addChart('column', $categories_2_3, $series_2_3, $style_1);
            $section->addText('Sumber Data: Badan Pusat Statistik', array('name' => 'Arial', 'size' => 10));

            //PDRB per Kapita ADHK (Rp)
            $sql_adhk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhk = $this->db->query($sql_adhk);
            foreach ($list_adhk->result() as $row_adhk) {
                $tahun_adhk[]   = $row_adhk->tahun;
                $nilaiData_adhk1[] = number_format((float)$row_adhk->nilai / 1000000, 2);
                $adhk_nasional[] = (float)$row_adhk->nilai;
                $adhk_nasionall[$row_adhk->tahun] = (float)$row_adhk->nilai;
            }
            $datay_adhk1 = $nilaiData_adhk1;
            $tahun_adhk1 = $tahun_adhk;
            $sql_adhk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhk2 = $this->db->query($sql_adhk2);
            foreach ($list_adhk2->result() as $row_adhk2) {
                $tahun_adhk2[]              = $row_adhk2->tahun;
                $nilaiData_adhk2[]          = number_format((float)$row_adhk2->nilai / 1000000, 2);
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
                                        where (id_indikator='3' AND wilayah='" . $kab . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='3' AND wilayah='" . $kab . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
            $list_adhk3 = $this->db->query($sql_adhk3);
            foreach ($list_adhk3->result() as $row_adhk3) {
                $tahun_adhk3[]   = $row_adhk3->tahun;
                $n_adhk3          = (float)$row_adhk3->nilai_kab;
                if ($n_adhk3 == 0) {
                    $nilaiDataadhk3 = '#N/A';
                } else {
                    $nilaiDataadhk3 = number_format((float)$row_adhk3->nilai_kab / 1000000, 2);
                }
                $nilaiData_adhk3[] = $nilaiDataadhk3;
                $adhk_k[]          = (float)$row_adhk3->nilai_kab;
                $adhk_kk[$row_adhk3->tahun] = (float)$row_adhk3->nilai_kab;
                $sumber_adhk       = "diolah";
                $periode_kab_adhk[] = $row_adhk3->idperiode;
            }
            $datay_adhk3 = array_reverse($nilaiData_adhk3);
            $tahun_adhk3 = $tahun_adhk3;
            $periode_kab_adhk_max = max($periode_kab_adhk);
            $periode_adhk_tahun = max($tahun_adhk3) . "";
            $tahun_adhk3_max = max($tahun_adhk3);
            $tahun_adhk3_1 = $tahun_adhk3_max - 1;
            if ($adhk_kk[$tahun_adhk3_1] > $adhk_kk[$periode_adhk_tahun]) {
                $menurunmeningkatADHK = 'menurun';
                if ($adhk_pp[$tahun_adhk3_max] > $adhk_kk[$tahun_adhk3_max]) {
                    $dibawahdiatasADHK = 'dibawah';
                } else {
                    $dibawahdiatasADHK = 'diatas';
                }
            } else {
                $menurunmeningkatADHK = 'meningkat';
                if ($adhk_pp[$tahun_adhk3_max] > $adhk_kk[$tahun_adhk3_max]) {
                    $dibawahdiatasADHK = 'dibawah';
                } else {
                    $dibawahdiatasADHK = 'diatas';
                }
            }
            if ($adhk_nasionall[$tahun_adhk3_max] > $adhk_kk[$tahun_adhk3_max]) {
                $dibawahdiatasADHKN = 'dibawah';
            } else {
                $dibawahdiatasADHKN = 'diatas';
            }
            $max_adhk    = "PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " pada tahun " . $tahun_adhk3_max . " " . $menurunmeningkatADHK . " dibandingkan dengan tahun " . $tahun_adhk3_1 . ". Pada " . $tahun_adhk3_max . " PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " adalah sebesar Rp " . number_format($adhk_kk[$tahun_adhk3_max], 0) . ", sedangkan pada tahun " . $tahun_adhk3_1 . " PDRB per Kapita ADHK Tahun Dasar 2010 tercatat sebesar Rp " . number_format($adhk_kk[$tahun_adhk3_1], 0) . ".";
            $max_adhk_p  = "PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " pada tahun " . $tahun_adhk3_max . " berada " . $dibawahdiatasADHK . " capaian " . $xname . ". PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk3_max . " adalah sebesar Rp " . number_format($adhk_pp[$tahun_adhk3_max], 0) . ".";
            $max_adhk_k  = "Capaian PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " pada tahun " . $tahun_adhk3_max . " berada " . $dibawahdiatasADHKN . " nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun " . $tahun_adhk3_max . " adalah sebesar Rp " . number_format($adhk_nasionall[$tahun_adhk3_max], 0) . ".";

            $adhk2_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='3' AND e.id_periode='" . $periode_kab_adhk_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='3' AND id_periode='" . $periode_kab_adhk_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_adhk_per = $this->db->query($adhk2_kab);
            foreach ($list_kab_adhk_per->result() as $row_adhk_kab_per) {
                $label_adhk[]     = $row_adhk_kab_per->label;
                $nilai_adhk_per[] = number_format($row_adhk_kab_per->nilai / 1000000, 2);
                $nilai_adhk_per1[] = $row_adhk_kab_per->nilai;
                $posisi_adhk = strpos($row_adhk_kab_per->label, "Kabupaten");
                if ($posisi_adhk !== FALSE) {
                    $label_adhk11 = substr($row_adhk_kab_per->label, 0, 3) . " " . substr($row_adhk_kab_per->label, 10);
                } else {
                    $label_adhk11 = $row_adhk_kab_per->label;
                }
                $label_adhk1[] = $label_adhk11;
                $tahun_adhk_k                   = $bulan[$row_adhk_kab_per->periode] . "" . $row_adhk_kab_per->tahun;
                $nilai_adhk_kab[]               = (float)$row_adhk_kab_per->nilai / 1000000;
                $label_adhk_k[$row_adhk_kab_per->label]    = $row_adhk_kab_per->nilai;
            }
            $label_data_adhk     = $label_adhk1;
            $nilai_data_adhk_per = $nilai_adhk_per;

            $ranking = $label_adhk_k;
            arsort($ranking);

            $nilai_adhk_per_kab_max = max($label_adhk_k);
            $nilai_adhk_per_kab_min = min($label_adhk_k);
            arsort($nilai_adhk_per1);
            $nama_k1 = $label_adhk_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_adhk = $nilai_adhk_per_kab_max - $nilai_adhk_per_kab_min;

            $nrk = 1;
            foreach ($ranking as $xk => $xk_value) {
                if ($xk == $judul) {
                    $rengking_pro_k = $nrk++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $adhk_perbandingan_kab = "Perbandingan perkembangan PDRB per Kapita ADHK Tahun Dasar 2010 antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada tahun " . $tahun_adhk_k . " daerah dengan tingkat perkembangan PDRB per Kapita ADHK Tahun Dasar 2010 tertinggi adalah " . array_shift($nama_k2) . " Rp " . number_format($nilai_adhk_per_kab_max, 0) . ", sedangkan daerah dengan perkembangan PDRB per Kapita ADHK Tahun Dasar 2010 terendah adalah " . end($nama_k2) . " Rp " . number_format($nilai_adhk_per_kab_min, 0) . ". " . $judul . " berada pada urutan ke " . $rengking_pro_k . ".";


            $paragraf_3_2 = $max_adhk;
            $paragraf_3_3 = $max_adhk_p;
            $paragraf_3_4 = $max_adhk_k;
            $section = $phpWord->addSection();
            $section->addText('' . $nomor++ . '. Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010', $fontStyleName);
            $section->addText($deskripsi3, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_3_2, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_3_3, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_3_4, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            //$section->addTextBreak(4);

            $categories = $tahun_adhk;
            $series1    = $datay_adhk1;
            $series2    = $datay_adhk2;
            $series3    = $datay_adhk3;
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010 (Juta Rupiah)', array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
                $chart = $section->addChart($chartType, $categories, $series1, $style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories, $series2, $xname);
                    $chart->addSeries($categories, $series3, $judul);
                }
                //                $section->addTextBreak();
            }
            $section = $phpWord->addSection();
            $section->addText($adhk_perbandingan_kab, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addTextBreak();
            $categories_1_2 = $label_adhk1;
            $series_1_2     = $nilai_data_adhk_per;
            $section->addText("Gambar " . $gambar++ . ". Perbandingan PDRB per Kapita ADHK Tahun Dasar 2010 Tahun " . $tahun_pe_max . " Antar Kabupaten/Kota Di $xname (Juta Rupiah)", array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
            $chart = $section->addChart('column', $categories_1_2, $series_1_2, $style_g2);
            $section->addText('Sumber Data: Badan Pusat Statistik', array('name' => 'Tahoma', 'size' => 10));

            //jumlah pengangguran
            $sql_jp = "SELECT REF.id_periode,IND.nilai nilai_nas
                        FROM(
                                select DISTINCT id_periode from nilai_indikator 
                                where (id_indikator='4' AND wilayah='1000') 
                                AND (id_periode, versi) in (
                                                            select id_periode, max(versi) as versi 
                                                            from nilai_indikator 
                                                            WHERE id_indikator='4' AND wilayah='1000' AND tahun >= '" . $tahunini . "' group by id_periode
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
                                                    WHERE id_indikator='4' AND wilayah='1000' AND tahun >= '" . $tahunini . "' group by id_periode
                                                    ) 
                                                    group by id_periode 
                                                    order by id_periode ASC limit 6
                                    ) IND	ON REF.id_periode=IND.id_periode";

            $list_jp = $this->db->query($sql_jp);
            foreach ($list_jp->result() as $row_jp) {
                $tahun_ip[]     = $bulan[substr($row_jp->id_periode, 4)] . "-" . substr($row_jp->id_periode, 0, -2);
                $nilaiData_jp[] = (float)$row_jp->nilai_nas / 1000;
            }
            $datay_jp = $nilaiData_jp;
            $tahun_jp = $tahun_ip;

            $sql_jp2 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_prov
                    FROM(
                    select DISTINCT id_periode from nilai_indikator 
                    where (id_indikator='4' AND wilayah='1000') 
                    AND (id_periode, versi) in (
                                                        select id_periode, max(versi) as versi 
                                                        from nilai_indikator 
                                                        WHERE id_indikator='4' AND wilayah='1000' AND tahun  >= '" . $tahunini . "' group by id_periode
                                                )
                    order by id_periode 
                    ASC limit 6 
                    ) REF
                    LEFT JOIN(
                            select id_periode,nilai 
                    from nilai_indikator 
                    where (id_indikator='4' AND wilayah='" . $id_pro . "')
                    AND (id_periode, versi) in (
                                            select id_periode, max(versi) as versi 
                            from nilai_indikator 
                            WHERE id_indikator='4' AND wilayah='" . $id_pro . "' AND tahun >= '" . $tahunini . "' group by id_periode
                            ) 
                            group by id_periode 
                            order by id_periode ASC limit 6
                    ) IND	ON REF.id_periode=IND.id_periode";

            $list_jp2 = $this->db->query($sql_jp2);
            foreach ($list_jp2->result() as $row_jp2) {
                $tahun_jp2[]   = $row_jp2->id_periode;
                $nilaiData_jp2[] = (float)$row_jp2->nilai_prov / 1000;
                $nilai_capaian[] = (float)$row_jp2->nilai_prov;
            }
            $datay_jp2 = $nilaiData_jp2;
            $tahun_jp2 = $tahun_jp2;

            $sql_jp3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k, IND.tahun, IND.periode, IND.id_periode idperiode 
                            FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='4' AND wilayah='1000') 
                                    AND (id_periode, versi) in (
                                                                        select id_periode, max(versi) as versi 
                                                                        from nilai_indikator 
                                                                        WHERE id_indikator='4' AND wilayah='1000' AND tahun >= '" . $tahunini . "' group by id_periode
                                                                )
                                    order by id_periode 
                                    ASC limit 6 
                            ) REF
                            LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun,periode 
                                    from nilai_indikator 
                                    where (id_indikator='4' AND wilayah='" . $kab . "')
                                    AND (id_periode, versi) in (
                                                            select id_periode, max(versi) as versi 
                                            from nilai_indikator 
                                            WHERE id_indikator='4' AND wilayah='" . $kab . "' AND tahun >= '" . $tahunini . "' group by id_periode
                                            ) 
                                            group by id_periode 
                                            order by id_periode ASC limit 6
                            ) IND	ON REF.id_periode=IND.id_periode";

            $list_jp3 = $this->db->query($sql_jp3);
            foreach ($list_jp3->result() as $row_jp3) {
                $tahun_jp3[]     = $row_jp3->idperiode;
                $tahun_jp33[]     = $row_jp3->tahun;
                $nilaiDatajp3_1 = $row_jp3->nilai_kab;
                if ($nilaiDatajp3_1 = '0') {
                    $nilai_k = '#N/A';
                } else {
                    $nilai_k = (float)$row_jp3->nilai_kab / 1000;
                }
                $nilaiDatajp3[] = $nilai_k;
                $nilai_capaian33[$row_jp3->id_periode] = (float)$row_jp3->nilai_kab;
                $sumber_jp                             = $row_jp3->sumber_k;
                $tahun_jp333[$row_jp3->id_periode]     = $bulan1[$row_jp3->periode] . " " . $row_jp3->tahun;
            }
            $datay_jp3 = $nilaiDatajp3;
            $periode_kab_jp_max = max($tahun_jp3);
            $periode_kab_jp_maxx = max($tahun_jp3);
            $periode_kab_jp_1   = $periode_kab_jp_maxx - 100;
            $periode_jp_tahun   = $tahun_jp333[$periode_kab_jp_maxx];
            $periode_jp_tahun_1 = $tahun_jp333[$periode_kab_jp_1];

            if ($nilai_capaian33[$periode_kab_jp_1] > $nilai_capaian33[$periode_kab_jp_maxx]) {
                $rt_jp  = $nilai_capaian33[$periode_kab_jp_maxx] - $nilai_capaian33[$periode_kab_jp_1];
                $rt_jpp = abs($nilai_capaian33[$periode_kab_jp_maxx] - $nilai_capaian33[$periode_kab_jp_1]);
                $rt_jp2 = $rt_jp / $nilai_capaian33[$periode_kab_jp_1];
                $rt_jp3 = abs($rt_jp2 * 100);
                $rt_jp33 = number_format($rt_jp3, 2);
                $berkurangbertambah = 'berkurang';
            } else {
                $rt_jp  = $nilai_capaian33[$periode_kab_jp_maxx] - $nilai_capaian33[$periode_kab_jp_1];
                $rt_jpp = abs($nilai_capaian33[$periode_kab_jp_maxx] - $nilai_capaian33[$periode_kab_jp_1]);
                $rt_jp2 = $rt_jp / $nilai_capaian33[$periode_kab_jp_1];
                $rt_jp3 = $rt_jp2 * 100;
                $rt_jp33 = number_format($rt_jp3, 2);
                $berkurangbertambah = 'meningkat';
            }
            $max_jp_p  = "Jumlah penganggur di " . $xnameKab . " pada " . $periode_jp_tahun . " sebanyak " . number_format($nilai_capaian33[$periode_kab_jp_maxx], 0) . " orang. Sedangkan jumlah penganggur pada " . $periode_jp_tahun_1 . " sebanyak " . number_format($nilai_capaian33[$periode_kab_jp_1], 0) . " orang. Selama periode " . $periode_jp_tahun_1 . " - " . $periode_jp_tahun . " jumlah penganggur di " . $xnameKab . " " . $berkurangbertambah . " " . number_format($rt_jp) . " orang atau sebesar " . $rt_jp33 . "%";

            $jp2_kab = "select p.nama_kabupaten as label, e.* 
                        from kabupaten p
                        join nilai_indikator e on p.id = e.wilayah 
                        where p.prov_id='" . $provinsi . "' and (e.id_indikator='4' AND e.id_periode='" . $periode_kab_jp_max . "') AND (wilayah, versi) in (
                        select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='4' AND id_periode='" . $periode_kab_jp_max . "' group by wilayah ) 
                        group by wilayah order by wilayah asc";
            $list_kab_jp_per = $this->db->query($jp2_kab);
            foreach ($list_kab_jp_per->result() as $row_jp_kab_per) {
                $label_jp[]     = $row_jp_kab_per->label;
                $nilai_jp_per[] = $row_jp_kab_per->nilai / 1000;
                $nilai_jp_per1[] = $row_jp_kab_per->nilai;
                $posisi_jp = strpos($row_jp_kab_per->label, "Kabupaten");
                if ($posisi_jp !== FALSE) {
                    $label_jp11 = substr($row_jp_kab_per->label, 0, 3) . " " . substr($row_jp_kab_per->label, 10);
                } else {
                    $label_jp11 = $row_jp_kab_per->label;
                }
                $label_jp1[] = $label_jp11;
                $tahun_jp_k                   = $row_jp_kab_per->tahun;
                $nilai_jp_kab[]               = (float)$row_jp_kab_per->nilai / 1000;
                $label_jp_k[$row_jp_kab_per->label]      = $row_jp_kab_per->nilai;
            }
            $label_data_jp     = $label_jp1;
            $nilai_data_jp_per = $nilai_jp_per;

            $ranking = $label_jp_k;
            arsort($ranking);

            $nilai_jp_per_kab_max = max($label_jp_k);
            $nilai_jp_per_kab_min = min($label_jp_k);
            arsort($nilai_jp_per1);
            $nama_k1 = $label_jp_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_jp = $nilai_jp_per_kab_max - $nilai_jp_per_kab_min;

            $nrk = 1;
            foreach ($ranking as $xk => $xk_value) {
                if ($xk == $judul) {
                    $rengking_pro_k = $nrk++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $jp_perbandingan_kab = "Perbandingan jumlah penganggur antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada tahun " . $tahun_jp_k . " daerah dengan jumlah penganggur tertinggi adalah " . array_shift($nama_k2) . " " . number_format($nilai_jp_per_kab_max, 0) . " orang, sedangkan daerah jumlah penganggur terendah adalah " . end($nama_k2) . " " . number_format($nilai_jp_per_kab_min, 0) . " orang. Selisih Jumlah penganggur tertinggi dan terendah di " . $xname . " pada tahun " . $tahun_jp_k . " adalah sebesar " . number_format($selisih_jp, 0) . " orang. " . $judul . " berada pada urutan ke " . $rengking_pro_k . ".";

            $section = $phpWord->addSection();
            $section->addText('' . $nomor++ . ' Perkembangan Jumlah Penganggur', $fontStyleName);
            $section->addText($deskripsi4, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $paragraf_4_2 = $max_jp_p;
            $section->addText($paragraf_4_2, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));

            $categories_4_2 = $tahun_jp;
            $series_4_n     = $datay_jp;
            $series_4_2     = $datay_jp2;
            $series_4_3     = $datay_jp3;
            foreach ($chartTypes2 as $chartTypes22) {
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Jumlah Penganggur (Ribu Orang)', array('name' => 'Arial', 'size' => 10, 'bold' => true), array('alignment' => 'center'));
                $chart = $section->addChart($chartTypes22, $categories_4_2, $series_4_3, $style_g2, $judul);
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartTypes22, $twoSeries)) {
                    // $chart->addSeries($categories_4_2, $series_4_2, $xname );
                    // $chart->addSeries($categories_4_2, $series_4_3, $judul );
                }
                // $section->addTextBreak(2);
            }
            $section->addText($jp_perbandingan_kab, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addTextBreak();
            $categories_4_3 = $label_data_jp;
            $series_1_4     = $nilai_data_jp_per;
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Jumlah Penganggur Periode " . $periode_jp_tahun_1 . " Antar Kabupaten Di $xname (Orang)", array('name' => 'Arial', 'size' => 10, 'bold' => true), array('alignment' => 'center'));
            $chart = $section->addChart('column', $categories_4_3, $series_1_4, $style_g2);
            $section->addText('Sumber Data: Badan Pusat Statistik', array('name' => 'Tahoma', 'size' => 10));

            //tingkat pengangguran terbuka                    
            $sql_tpt = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 8) y order by id_periode ASC";
            $list_tpt = $this->db->query($sql_tpt);
            foreach ($list_tpt->result() as $row_tpt) {
                $tahun_tpt1[]     = $bulan[$row_tpt->periode] . "-" . $row_tpt->tahun;
                $tahun_tpt[]      = $row_tpt->tahun;
                $nilaiData_tpt[]  = number_format((float)$row_tpt->nilai, 2);
                $data_tpt[$row_tpt->id_periode] = (float)$row_tpt->nilai;
            }
            $datay_tpt = $nilaiData_tpt;
            $tahun_tpt = $tahun_tpt;
            $sql_tpt2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 8) y order by id_periode ASC";
            $list_tpt2 = $this->db->query($sql_tpt2);
            foreach ($list_tpt2->result() as $row_tpt2) {
                $tahun_tpt2[]   = $row_tpt2->tahun;
                $nilaiData_tpt2[] = number_format((float)$row_tpt2->nilai, 2);
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
                                        where (id_indikator='6' AND wilayah='" . $kab . "')
                                            AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='6' AND wilayah='" . $kab . "' group by id_periode
                                                                    ) 
                                        group by id_periode 
                                        order by id_periode Desc limit 8
                                        ) IND	ON REF.id_periode=IND.id_periode";
            $list_tpt3 = $this->db->query($sql_tpt3);
            foreach ($list_tpt3->result() as $row_tpt3) {
                if ($row_tpt3->nilai_kab == 0) {
                    $nilaiData_ktpt3 = '#N/A';
                } else {
                    $nilaiData_ktpt3 = number_format((float)$row_tpt3->nilai_kab, 2);
                }
                $nilaiData_tpt3[] = $nilaiData_ktpt3;
                $data_tpt3[$row_tpt3->perkab]   = $nilaiData_ktpt3;
                $diperiode_tpt3[]                   = $row_tpt3->id_periode;
                $diperiode_tpt33[]                  = $row_tpt3->perkab;
                $tahun_tpt3[]                       = $row_tpt3->tahun;
                $tahunTPT_K[$row_tpt3->id_periode]    = $bulan1[$row_tpt3->periode] . " " . $row_tpt3->tahun;
                $periode_tpt3[] = $row_tpt3->perkab;
            }
            $datay_tpt3 = array_reverse($nilaiData_tpt3);
            $tahun_tpt3 = $tahun_tpt3;
            $periode_kab_tpt_max3 = max($diperiode_tpt33);

            $periode_TPT_max  = max($diperiode_tpt3);
            $dataTPT1 = substr($periode_TPT_max, 0, 4);
            $dataTPT2 = substr($periode_TPT_max, -2);

            $tahun_kab_tpt_max = max($periode_tpt3);
            $tahun_kab_tpt_1 = $tahun_kab_tpt_max - 100;

            if ($dataTPT2 == '00') {
                $periode_TPT_tahun = $dataTPT1 . " Antar Kabupaten";
            } else {
                $periode_TPT_tahun =  $bulan1[$dataTPT2] . " " . $dataTPT1 . " Antar Kabupaten";
            }
            if ($data_tpt[$tahun_kab_tpt_1] > $data_tpt[$tahun_kab_tpt_max]) {
                $meningkatmenurunTPT = 'meningkat';
                if ($data_tpt2[$tahun_kab_tpt_max] > $data_tpt[$tahun_kab_tpt_max]) {
                    $dibawahdiatasTPT = 'di atas';
                } else {
                    $dibawahdiatasTPT = 'di bawah';
                }
            } else {
                $meningkatmenurunTPT = 'menurun';
                if ($data_tpt2[$tahun_kab_tpt_max] > $data_tpt[$tahun_kab_tpt_max]) {
                    $dibawahdiatasTPT = 'di atas';
                } else {
                    $dibawahdiatasTPT = 'di bawah';
                }
            }

            if ($data_tpt[$tahun_kab_tpt_max] > $data_tpt[$tahun_kab_tpt_max]) {
                $dibawahdiatasTPTN = 'diatas';
            } else {
                $dibawahdiatasTPTN = 'dibawah';
            }
            $max_tpt    = "Tingkat pengangguran terbuka " . $xnameKab . " pada " . $tahunTPT_K[$tahun_kab_tpt_max] . " " . $meningkatmenurunTPT . " dibandingkan dengan " . $tahunTPT_K[$tahun_kab_tpt_1] . ". Pada " . $tahunTPT_K[$tahun_kab_tpt_1] . " Tingkat pengangguran terbuka " . $xnameKab . " adalah sebesar " . number_format((float)$nilaiData_tpt3[2], 2) . "%, sedangkan pada " . $tahunTPT_K[$tahun_kab_tpt_max] . " Tingkat pengangguran terbuka tercatat sebesar " . number_format((float)$nilaiData_tpt3[0], 2) . "%. ";
            $max_tpt_p  = "Tingkat pengangguran terbuka " . $xnameKab . " pada " . $tahunTPT_K[$tahun_kab_tpt_max] . " berada " . $dibawahdiatasTPT . " capaian " . $xname . ". Tingkat pengangguran terbuka " . $xname . " pada " . $tahunTPT_K[$tahun_kab_tpt_max] . " adalah sebesar " . number_format($data_tpt2[$tahun_kab_tpt_max], 2) . "%. ";
            $max_tpt_k  = "Capaian tingkat pengangguran terbuka " . $xnameKab . " pada " . $tahunTPT_K[$tahun_kab_tpt_max] . " berada " . $dibawahdiatasTPTN . " nasional. Tingkat pengangguran terbuka nasional pada " . $tahunTPT_K[$tahun_kab_tpt_max] . " adalah sebesar " . number_format($data_tpt[$tahun_kab_tpt_max], 2) . "% ";

            $tpt_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='6' AND e.id_periode='" . $periode_kab_tpt_max3 . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='6' AND id_periode='" . $periode_kab_tpt_max3 . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_tpt_per = $this->db->query($tpt_kab);
            foreach ($list_kab_tpt_per->result() as $row_tpt_kab_per) {
                $label_tpt[]                = $row_tpt_kab_per->label;
                $nilai_tpt_per[]            = $row_tpt_kab_per->nilai;
                $nilai_tpt_per1             = $row_tpt_kab_per->nilai;

                if ($nilai_tpt_per1 == '0') {
                    $nilai_kab = '#N/A';
                } else {
                    $nilai_kab = $row_tpt_kab_per->nilai;
                }

                $nilai_tpt_kab[]        = $nilai_kab;
                $posisi_tpt             = strpos($row_tpt_kab_per->label, "Kabupaten");

                if ($posisi_tpt !== FALSE) {
                    $label_tpt11            = substr($row_tpt_kab_per->label, 0, 3) . " " . substr($row_tpt_kab_per->label, 10);
                } else {
                    $label_tpt11 = $row_tpt_kab_per->label;
                }
                $label_tpt1[]               = $label_tpt11;
                $label_tpt1_k[$row_tpt_kab_per->label] = $row_tpt_kab_per->nilai;
                $tahun_tpt_k                = $bulan1[$row_tpt_kab_per->periode] . " " . $row_tpt_kab_per->tahun;
            }
            $label_data_tpt     = $label_tpt1;
            $nilai_data_tpt_per = $nilai_tpt_kab;

            $ranking            = $label_tpt1_k;
            arsort($ranking);

            $nilai_tpt_per_kab_max = max($label_tpt1_k);
            $nilai_tpt_per_kab_min = min($label_tpt1_k);
            arsort($nilai_tpt_per);
            $nama_k1 = $label_tpt1_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_tpt = $nilai_tpt_per_kab_max - $nilai_tpt_per_kab_min;
            $nrk = 1;
            foreach ($ranking as $xk => $xk_value) {
                if ($xk == $judul) {
                    $rengking_pro_k = $nrk++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $tpt_perbandingan_kab = "Perbandingan tingkat pengangguran terbuka antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada " . $tahun_tpt_k . " daerah dengan tingkat pengangguran terbuka tertinggi adalah " . array_shift($nama_k2) . " (" . number_format($nilai_tpt_per_kab_max, 2) . "%), sedangkan daerah tingkat pengangguran terbuka terendah adalah " . end($nama_k2) . " (" . number_format($nilai_tpt_per_kab_min, 2) . "%). Selisih tingkat pengangguran terbuka tertinggi dan terendah di " . $xname . " pada tahun " . $tahun_tpt_k . " adalah sebesar " . number_format($selisih_tpt, 2) . "%. " . $judul . " berada pada urutan ke " . $rengking_pro_k . ".";

            $paragraf_5_2 = $max_tpt;
            $paragraf_5_3 = $max_tpt_p;
            $paragraf_5_4 = $max_tpt_k;
            $section = $phpWord->addSection();
            $section->addText('' . $nomor++ . ' Perkembangan Tingkat Pengangguran Terbuka', $fontStyleName);
            $section->addText($deskripsi5, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_5_2, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_5_3, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_5_4, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));

            $categories_5_1 = $tahun_tpt1;
            $series1        = $datay_tpt;
            $series2        = $datay_tpt2;
            $series3        = $datay_tpt3;
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Tingkat Pengangguran Terbuka (%)', array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
                $chart = $section->addChart($chartType, $categories_5_1, $series1, $style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_5_1, $series2, $xname);
                    $chart->addSeries($categories_5_1, $series3, $judul);
                }
                $section->addTextBreak(2);
            }
            $section = $phpWord->addSection();
            $section->addText($tpt_perbandingan_kab, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addTextBreak();
            $categories_5_2 = $label_data_tpt;
            $series_5_2     = $nilai_data_tpt_per;
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Tingkat Pengangguran Terbuka Tahun " . $tahun_tpt_k . " Antar Kabupaten Di " . $xname . " (%)", array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
            $chart = $section->addChart('column', $categories_5_2, $series_5_2, $style_1);
            $section->addText('Sumber Data: Badan Pusat Statistik', array('name' => 'Tahoma', 'size' => 10));

            //Tingkat Kemiskinan
            $sql_tk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tk = $this->db->query($sql_tk);
            foreach ($list_tk->result() as $row_tk) {
                $tahun_tk[]                         = $bulan1[$row_tk->periode] . " " . $row_tk->tahun;
                $periode_kab_tk_max[]               = $row_tk->id_periode;
                $nilaiData_tk[]                     = (float)$row_tk->nilai;
                $nilaiData_tk1[$row_tk->id_periode] = (float)$row_tk->nilai;
            }
            $datay_tk = $nilaiData_tk;
            $tahun_tk = $tahun_tk;
            $sql_tk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tk2 = $this->db->query($sql_tk2);
            foreach ($list_tk2->result() as $row_tk2) {
                $tahun_tk2[]                            = $row_tk2->tahun;
                $nilaiData_tk2[]                        = (float)$row_tk2->nilai;
                $nilaiData_tk22[$row_tk2->id_periode]   = (float)$row_tk2->nilai;
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
                                            where (id_indikator='36' AND wilayah='" . $kab . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='36' AND wilayah='" . $kab . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";

            $list_tk3 = $this->db->query($sql_tk3);
            foreach ($list_tk3->result() as $row_tk3) {
                $n_tk3                          = $row_tk3->nilai_kab;
                if ($n_tk3 == 0) {
                    $nilaiData_ktk3 = '#N/A';
                } else {
                    $nilaiData_ktk3 = (float)$n_tk3;
                }
                $nilaiData_tk3[]                = $nilaiData_ktk3;
                $periode_tk3[]                  = $row_tk3->idperiode;
                $data_tk3[$row_tk3->idperiode]  = $nilaiData_ktk3;
                $per_tk3[$row_tk3->idperiode]   = $bulan1[$row_tk3->periode] . " " . $row_tk3->tahun;
            }
            $datay_tk3 = array_reverse($nilaiData_tk3);
            $tahun_kab_tk_max = max($periode_tk3);
            $tahun_kab_tk_1 = $tahun_kab_tk_max - 100;
            $periode_tk_tahun = $per_tk3[$tahun_kab_tk_max];
            if ($data_tk3[$tahun_kab_tk_1] > $data_tk3[$tahun_kab_tk_max]) {
                $meningkatmeningkatTK = 'menurun';
                if ($nilaiData_tk22[$tahun_kab_tk_max] > $data_tk3[$tahun_kab_tk_max]) {
                    $dibawahdiatasTK = 'dibawah';
                    $max_p_tk  = "Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " berada dibawah capaian " . $xname . ". Tingkat Kemiskinan " . $xname . " pada " . $per_tk3[$tahun_kab_tk_max] . " adalah sebesar " . $nilaiData_tk22[$tahun_kab_tk_max] . "%. ";
                } else {
                    $dibawahdiatasTK = 'diatas';
                }
            } else {
                $meningkatmeningkatTK = 'meningkat';
                if ($nilaiData_tk22[$tahun_kab_tk_max] > $data_tk3[$tahun_kab_tk_max]) {
                    $dibawahdiatasTK = 'dibawah';
                } else {
                    $dibawahdiatasTK = 'diatas';
                }
            }
            if ($nilaiData_tk1[$tahun_kab_tk_max] > $data_tk3[$tahun_kab_tk_max]) {
                $dibawahdiatasTKN = 'dibawah';
            } else {
                $dibawahdiatasTKN = 'diatas';
            }
            $max_n_tk  = "Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " " . $meningkatmeningkatTK . " dibandingkan dengan " . $per_tk3[$tahun_kab_tk_1] . ". Pada " . $per_tk3[$tahun_kab_tk_max] . " Tingkat Kemiskinan " . $xnameKab . " adalah sebesar " . $data_tk3[$tahun_kab_tk_max] . "%, sedangkan pada tahun " . $per_tk3[$tahun_kab_tk_1] . " Tingkat Kemiskinan tercatat sebesar " . $data_tk3[$tahun_kab_tk_1] . "%. ";
            $max_p_tk  = "Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " berada " . $dibawahdiatasTK . " capaian " . $xname . ". Tingkat Kemiskinan " . $xname . " pada " . $per_tk3[$tahun_kab_tk_max] . " adalah sebesar " . $nilaiData_tk22[$tahun_kab_tk_max] . "%. ";
            $max_k_tk  = "Capaian Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " berada " . $dibawahdiatasTKN . " nasional. Tingkat Kemiskinan nasional pada " . $per_tk3[$tahun_kab_tk_max] . " adalah sebesar " . $nilaiData_tk1[$tahun_kab_tk_max] . "%";

            $tk_kab = "select p.nama_kabupaten as label, e.*  from kabupaten p join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='36' AND e.id_periode='" . $tahun_kab_tk_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='36' AND id_periode='" . $tahun_kab_tk_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_tk_per = $this->db->query($tk_kab);
            foreach ($list_kab_tk_per->result() as $row_tk_kab_per) {
                $label_tk[]     = $row_tk_kab_per->label;
                $nilai_tk_per[] = $row_tk_kab_per->nilai;
                $posisi_tk = strpos($row_tk_kab_per->label, "Kabupaten");
                if ($posisi_tk !== FALSE) {
                    $label_tk11 = substr($row_tk_kab_per->label, 0, 3) . " " . substr($row_tk_kab_per->label, 10);
                } else {
                    $label_tk11 = $row_tk_kab_per->label;
                }
                $label_tk1[]                = $label_tk11;
                $tahun_tk_k                 = $bulan1[$row_tk_kab_per->periode] . " " . $row_tk_kab_per->tahun;
                $label_tk1_k[$row_tk_kab_per->label]   = $row_tk_kab_per->nilai;
            }
            $label_data_tk      = $label_tk1;
            $nilai_data_tk_per  = $nilai_tk_per;

            $ranking            = $label_tk1_k;
            arsort($ranking);

            $nilai_tk_per_kab_max   = max($label_tk1_k);
            $nilai_tk_per_kab_min   = min($label_tk1_k);
            arsort($nilai_tk_per);
            $nama_k1                = $label_tk1_k;
            arsort($nama_k1);
            $nama_k2                = array_keys($nama_k1);
            $selisih_ikk            = $nilai_tk_per_kab_max - $nilai_tk_per_kab_min;
            $nrk = 1;
            foreach ($ranking as $xk => $xk_value) {
                if ($xk == $judul) {
                    $rengking_pro_k = $nrk++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $tk_perbandingan_kab = "Perbandingan tingkat kemiskinan antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada " . $tahun_tk_k . " daerah dengan tingkat kemiskinan tertinggi adalah " . array_shift($nama_k2) . " (" . $nilai_tk_per_kab_max . "%), sedangkan daerah dengan tingkat kemiskinan terendah adalah " . end($nama_k2) . " (" . $nilai_tk_per_kab_min . "%). " . $judul . " berada pada urutan ke " . $rengking_pro_k . ".";


            $paragraf_12_2 = $max_n_tk;
            $paragraf_12_3 = $max_p_tk;
            $paragraf_12_4 = $max_k_tk;
            $section = $phpWord->addSection();
            $section->addText('' . $nomor++ . '. Tingkat Kemiskinan', $fontStyleName);
            $section->addText($deskripsi12, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_12_2, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_12_3, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_12_4, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));

            $categoriesTK = $tahun_tk;
            $series1TK    = $datay_tk;
            $series2TK    = $datay_tk2;
            $series3TK    = $datay_tk3;
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Tingkat Kemiskinan (%)', array('name' => 'Arial', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
                $chart = $section->addChart($chartType, $categoriesTK, $series1TK, $style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories, $series2TK, $xname);
                    $chart->addSeries($categories, $series3TK, $judul);
                }
                $section->addTextBreak();
            }
            $section = $phpWord->addSection();
            $section->addText($tk_perbandingan_kab, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addTextBreak();

            $categories_TK_2 = $label_data_tk;
            $series_TK_2     = $nilai_data_tk_per;
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Tingkat Kemiskinan (%) Periode " . $tahun_tk_k .  " Antar Kabupaten Di $xname (%)", array('name' => 'Arial', 'size' => 11, 'Bold' => true), array('alignment' => 'center'));
            $chart = $section->addChart('column', $categories_TK_2, $series_TK_2, $style_g2);
            $section->addText('Sumber Data: Badan Pusat Statistik', array('name' => 'Tahoma', 'size' => 10));


            //indeks Kedalaman Kemiskinan
            $sql_idk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_idk = $this->db->query($sql_idk);
            foreach ($list_idk->result() as $row_idk) {
                $tahun_idk[]                           = $bulan1[$row_idk->periode] . " " . $row_idk->tahun;
                $tahun_idk_k[$row_idk->id_periode]     = $bulan1[$row_idk->periode] . " " . $row_idk->tahun;
                $nilaiData_idk[]                       = (float)$row_idk->nilai;
                $nilaiData_idk_k[$row_idk->id_periode] = (float)$row_idk->nilai;
                $idperiode_idk3[]                      = $row_idk->id_periode;
            }
            $datay_idk = $nilaiData_idk;
            $tahun_idk = $tahun_idk;
            $sql_idk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_idk2 = $this->db->query($sql_idk2);
            foreach ($list_idk2->result() as $row_idk2) {
                $tahun_idk2[]   = $row_idk2->tahun;
                $tahun_idk2_k[$row_idk2->id_periode]   = $row_idk2->tahun;
                $nilaiData_idk2[] = (float)$row_idk2->nilai;
                $nilaiData_idk2_k[$row_idk2->id_periode] = (float)$row_idk2->nilai;
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
                                                    where (id_indikator='39' AND wilayah='" . $kab . "')
                                                    AND (id_periode, versi) in (
                                                                            select id_periode, max(versi) as versi 
                                                            from nilai_indikator 
                                                            WHERE id_indikator='39' AND wilayah='" . $kab . "' group by id_periode
                                                            ) 
                                                            group by id_periode 
                                                            order by id_periode Desc limit 6
                                            ) IND	ON REF.id_periode=IND.id_periode";

            $list_idk3 = $this->db->query($sql_idk3);
            foreach ($list_idk3->result() as $row_idk3) {
                $n_idk3   = $row_idk3->nilai_kab;
                if ($n_idk3 == 0) {
                    $nil_idk33 = '#N/A';
                } else {
                    $nil_idk33 = (float)$n_idk3;
                }
                $nil_idk3[] = $nil_idk33;
                $nil_idkPer[$row_idk3->idperiode] = $nil_idk33;
                $per_idk3[$row_idk3->tahun]         = $row_idk3->tahun;
                $per_idkPer[$row_idk3->idperiode]   = $bulan1[$row_idk3->periode] . " " . $row_idk3->tahun;
                $periode_idk3[] = $row_idk3->idperiode;
            }
            $datay_idk3 = array_reverse($nil_idk3);
            $periode_kab_ikk_max = max($periode_idk3);
            $periode_kab_IKK_ke2 = $periode_kab_ikk_max - 100;
            $periode_ikk_tahun = "Tahun " . $per_idkPer[$periode_kab_ikk_max];

            if ($nil_idkPer[$periode_kab_IKK_ke2] > $nil_idkPer[$periode_kab_ikk_max]) {
                $menurunmeningkatIDK = 'menurun';
                if ($nilaiData_idk2_k[$periode_kab_ikk_max] > $nil_idkPer[$periode_kab_ikk_max]) {
                    $dibawahdiatasIDK = 'dibawah';
                } else {
                    $dibawahdiatasIDK = 'diatas';
                }
            } else {
                $menurunmeningkatIDK = 'meningkat';
                if ($nilaiData_idk2_k[$periode_kab_ikk_max] > $nil_idkPer[$periode_kab_ikk_max]) {
                    $dibawahdiatasIDK = 'dibawah';
                } else {
                    $dibawahdiatasIDK = 'diatas';
                }
            }
            if ($nilaiData_idk_k[$periode_kab_ikk_max] > $nil_idkPer[$periode_kab_ikk_max]) {
                $dibawahdiatasIDKN = 'dibawah';
            } else {
                $dibawahdiatasIDKN = 'diatas';
            }

            $max_n_ikk    = "Indeks Kedalaman Kemiskinan " . $xnameKab . " pada " . $per_idkPer[$periode_kab_ikk_max] . " " . $menurunmeningkatIDK . " dibandingkan dengan " . $per_idkPer[$periode_kab_IKK_ke2] . ". Pada " . $per_idkPer[$periode_kab_ikk_max] . " Angka Indeks Kedalaman Kemiskinan " . $xnameKab . " adalah sebesar " . $nil_idkPer[$periode_kab_ikk_max] . ", sedangkan pada tahun " . $per_idkPer[$periode_kab_IKK_ke2] . " Angka Indeks Kedalaman Kemiskinan tercatat sebesar " . $nil_idkPer[$periode_kab_IKK_ke2] . ". ";
            $max_p_ikk    = "Indeks Kedalaman Kemiskinan " . $xnameKab . " pada " . $per_idkPer[$periode_kab_ikk_max] . " berada " . $dibawahdiatasIDK . " capaian " . $xname . ". Angka Indeks Kedalaman Kemiskinan " . $xname . " pada " . $tahun_idk2_k[$periode_kab_ikk_max] . " adalah sebesar " . $nilaiData_idk2_k[$periode_kab_ikk_max] . ".";
            $max_k_ikk    = "Capaian Angka Indeks Kedalaman Kemiskinan " . $xnameKab . " pada " . $per_idkPer[$periode_kab_ikk_max] . " berada " . $dibawahdiatasIDKN . " nasional. Angka Indeks Kedalaman Kemiskinan nasional pada " . $per_idkPer[$periode_kab_ikk_max] . " adalah sebesar " . $nilaiData_idk_k[$periode_kab_ikk_max] . ". ";

            $catdata_kab = array();
            $ikk_kab = "select p.nama_kabupaten as label, e.* 
                                        from kabupaten p
                                        join nilai_indikator e on p.id = e.wilayah 
                                        where p.prov_id='" . $provinsi . "' and (e.id_indikator='39' AND e.id_periode='" . $periode_kab_ikk_max . "') AND (wilayah, versi) in (
                                           select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='39' AND id_periode='" . $periode_kab_ikk_max . "' group by wilayah ) 
                                       group by wilayah order by wilayah asc";

            $list_kab_ikk_per = $this->db->query($ikk_kab);
            foreach ($list_kab_ikk_per->result() as $row_ikk_kab_per) {
                $label_ikk[]     = $row_ikk_kab_per->label;
                $nilai_ikk_per[] = $row_ikk_kab_per->nilai;
                $posisi_ikk = strpos($row_ikk_kab_per->label, "Kabupaten");
                if ($posisi_ikk !== FALSE) {
                    $label_ikk11 = substr($row_ikk_kab_per->label, 0, 3) . " " . substr($row_ikk_kab_per->label, 10);
                } else {
                    $label_ikk11 = $row_ikk_kab_per->label;
                }
                $label_ikk1[] = $label_ikk11;
                $label_ikk1_k[$row_ikk_kab_per->label] = $row_ikk_kab_per->nilai;
                $tahun_ikk_k        = $bulan1[$row_ikk_kab_per->periode] . " " . $row_ikk_kab_per->tahun;
                $nilai_ikk_kab[] = (float)$row_ikk_kab_per->nilai;
            }
            $label_data_ikk3     = $label_ikk1;
            $nilai_data_ikk_per3 = $nilai_ikk_per;

            $ranking = $label_ikk1_k;
            arsort($ranking);

            $nilai_ikk_per_kab_max = max($label_ikk1_k);
            $nilai_ikk_per_kab_min = min($label_ikk1_k);
            arsort($nilai_ikk_per);
            $nama_k1 = $label_ikk1_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_ikk = $nilai_ikk_per_kab_max - $nilai_ikk_per_kab_min;
            $nrk = 1;
            foreach ($ranking as $xk => $xk_value) {
                if ($xk == $judul) {
                    $rengking_pro_k = $nrk++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $ikk_perbandingan_kabb = "Perbandingan indeks kedalaman kemiskinan antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada " . $tahun_ikk_k . " daerah dengan tingkat indeks kedalaman kemiskinan tertinggi adalah " . array_shift($nama_k2) . " (" . $nilai_ikk_per_kab_max . "), sedangkan daerah dengan indeks kedalaman kemiskinan terendah adalah " . end($nama_k2) . " (" . $nilai_ikk_per_kab_min . "). " . $judul . " berada pada urutan ke " . $rengking_pro_k . ".";

            $paragraf_13_2 = $max_n_ikk;
            $paragraf_13_3 = $max_p_ikk;
            $paragraf_13_4 = $max_k_ikk;
            $section = $phpWord->addSection();
            $section->addText('' . $nomor++ . '. Indeks Kedalaman Kemiskinan (P1)', $fontStyleName);
            $section->addText($paragraf_13_2, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_13_3, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_13_4, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            //$section->addTextBreak();
            $categoriesIKK = $tahun_idk;
            $series1IKK    = $datay_idk;
            $series2IKK    = $datay_idk2;
            $series3IKK    = $datay_idk3;

            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Indeks Kedalaman Kemiskinan (P1)', array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
                $chart = $section->addChart($chartType, $categoriesIKK, $series1IKK, $style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories, $series2IKK, $xname);
                    $chart->addSeries($categories, $series3IKK, $judul);
                }
            }

            $section->addText($ikk_perbandingan_kabb, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));

            $categories_IKK_2 = $label_data_ikk3;
            $series_IKK_2     = $nilai_data_ikk_per3;

            $section->addText("Gambar " . $gambar++ . ". Perbandingan Indeks Kedalaman Kemiskinan (P1) Periode " . $per_idkPer[$periode_kab_ikk_max] . " Antar Kabupaten Di " . $xname . "", array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
            $chart = $section->addChart('column', $categories_IKK_2, $series_IKK_2, $style_1);
            $section->addText('Sumber Data: Badan Pusat Statistik', array('name' => 'Tahoma', 'size' => 10));

            //indeks Keparahan Kemiskinan
            $sql_kki = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_kki = $this->db->query($sql_kki);
            foreach ($list_kki->result() as $row_kki) {
                $tahun_kki[]     = $bulan[$row_kki->periode] . "-" . $row_kki->tahun;
                $tahun_kki_k[$row_kki->id_periode]     = $bulan[$row_kki->periode] . "-" . $row_kki->tahun;
                $nilaiData_kki[] = (float)$row_kki->nilai;
                $nilaiData_kki_k[$row_kki->id_periode] = (float)$row_kki->nilai;
                $idperiode_kki3[] = $row_kki->id_periode;
            }
            $datay_kki = $nilaiData_kki;
            $tahun_kki = $tahun_kki;
            $sql_kki2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_kki2 = $this->db->query($sql_kki2);
            foreach ($list_kki2->result() as $row_kki2) {
                $tahun_kki2[]   = $row_kki2->tahun;
                $tahun_kki2_k[$row_kki2->id_periode]   = $row_kki2->tahun;
                $nilaiData_kki2[] = (float)$row_kki2->nilai;
                $nilaiData_kki2_k[$row_kki2->id_periode] = (float)$row_kki2->nilai;
            }
            $datay_kki2 = $nilaiData_kki2;
            $tahun_kki2 = $tahun_kki2;
            $sql_kki3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab, IFNULL(IND.sumber,0) sumber_k,IND.id_periode idperiode,IND.tahun,IND.periode
                                            FROM(
                                                    select DISTINCT id_periode from nilai_indikator 
                                                    where (id_indikator='38' AND wilayah='1000') 
                                                    AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                WHERE id_indikator='38' AND wilayah='1000' group by id_periode
                                                        )
                                                    order by id_periode 
                                                    Desc limit 6 
                                            ) REF
                                            LEFT JOIN(
                                                select id_periode,nilai,sumber,tahun,periode 
                                                from nilai_indikator 
                                                where (id_indikator='38' AND wilayah='" . $kab . "')
                                                    AND (id_periode, versi) in (
                                                select id_periode, max(versi) as versi 
                                                    from nilai_indikator 
                            WHERE id_indikator='38' AND wilayah='" . $kab . "' group by id_periode
                                                    ) 
                                                group by id_periode 
                                            order by id_periode Desc limit 6
                                            ) IND	ON REF.id_periode=IND.id_periode";
            $list_kki3 = $this->db->query($sql_kki3);
            foreach ($list_kki3->result() as $row_kki3) {
                $n_kki3   = $row_kki3->nilai_kab;
                if ($n_kki3 == 0) {
                    $nil_kki33 = '#N/A';
                } else {
                    $nil_kki33 = (float)$n_kki3;
                }
                $nil_kki3[] = $nil_kki33;
                $nil_kkiPer[$row_kki3->idperiode] = $nil_kki33;
                $per_kki3[$row_kki3->tahun]         = $row_kki3->tahun;
                $per_kkiPer[$row_kki3->idperiode]   = $bulan1[$row_kki3->periode] . " " . $row_kki3->tahun;
                $periode_kki3[] = $row_kki3->idperiode;
            }
            $datay_kki3 = array_reverse($nil_kki3);
            $periode_kab_kki_max = max($periode_kki3);
            $periode_kab_kki_ke2 = $periode_kab_kki_max - 100;
            $periode_kki_tahun = "Tahun " . $per_kkiPer[$periode_kab_kki_max];



            if ($nil_kkiPer[$periode_kab_kki_ke2] > $nil_kkiPer[$periode_kab_kki_max]) {
                $menurunmeningkatkki = 'menurun';
                if ($nilaiData_kki2_k[$periode_kab_kki_max] > $nil_idkPer[$periode_kab_kki_max]) {
                    $dibawahdiataskki = 'dibawah';
                } else {
                    $dibawahdiataskki = 'diatas';
                }
            } else {
                $menurunmeningkatkki = 'meningkat';
                if ($nilaiData_kki2_k[$periode_kab_kki_max] > $nil_kkiPer[$periode_kab_kki_max]) {
                    $dibawahdiataskki = 'dibawah';
                } else {
                    $dibawahdiataskki = 'diatas';
                }
            }
            if ($nilaiData_kki_k[$periode_kab_kki_max] > $nil_kkiPer[$periode_kab_kki_max]) {
                $dibawahdiataskkiN = 'dibawah';
            } else {
                $dibawahdiataskkiN = 'diatas';
            }

            $max_n_kki    = "Indeks Keparahan Kemiskinan " . $xnameKab . " pada " . $per_kkiPer[$periode_kab_kki_max] . " " . $menurunmeningkatkki . " dibandingkan dengan " . $per_kkiPer[$periode_kab_kki_ke2] . ". Pada " . $per_kkiPer[$periode_kab_kki_max] . " Angka Indeks Kedalaman Kemiskinan " . $xnameKab . " adalah sebesar " . $nil_kkiPer[$periode_kab_kki_max] . ", sedangkan pada tahun " . $per_kkiPer[$periode_kab_kki_ke2] . " Angka Indeks Keparahan Kemiskinan tercatat sebesar " . $nil_kkiPer[$periode_kab_kki_ke2] . ". ";
            $max_p_kki    = "Indeks keparahan Kemiskinan " . $xnameKab . " pada " . $per_kkiPer[$periode_kab_kki_max] . " berada " . $dibawahdiataskki . " capaian " . $xname . ". Angka Indeks Keparahan Kemiskinan " . $xname . " pada " . $tahun_kki2_k[$periode_kab_kki_max] . " adalah sebesar " . $nilaiData_kki2_k[$periode_kab_kki_max] . ".";
            $max_k_kki    = "Capaian Angka Indeks keparahan Kemiskinan " . $xnameKab . " pada " . $per_kkiPer[$periode_kab_kki_max] . " berada " . $dibawahdiataskkiN . " nasional. Angka Indeks keparahan Kemiskinan nasional pada " . $per_kkiPer[$periode_kab_kki_max] . " adalah sebesar " . $nilaiData_kki_k[$periode_kab_kki_max] . ". ";

            $catdata_kab = array();
            $kki_kab = "select p.nama_kabupaten as label, e.* 
                                        from kabupaten p
                                        join nilai_indikator e on p.id = e.wilayah 
                                        where p.prov_id='" . $provinsi . "' and (e.id_indikator='38' AND e.id_periode='" . $periode_kab_kki_max . "') AND (wilayah, versi) in (
                                           select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='38' AND id_periode='" . $periode_kab_kki_max . "' group by wilayah ) 
                                       group by wilayah order by wilayah asc";

            $list_kab_kki_per = $this->db->query($kki_kab);
            foreach ($list_kab_kki_per->result() as $row_kki_kab_per) {
                $label_kki[]     = $row_kki_kab_per->label;
                $nilai_kki_per[] = $row_kki_kab_per->nilai;
                $posisi_kki = strpos($row_kki_kab_per->label, "Kabupaten");
                if ($posisi_kki !== FALSE) {
                    $label_kki11 = substr($row_kki_kab_per->label, 0, 3) . " " . substr($row_kki_kab_per->label, 10);
                } else {
                    $label_kki11 = $row_kki_kab_per->label;
                }
                $label_kki1[] = $label_kki11;
                $label_kki1_k[$row_kki_kab_per->label] = $row_kki_kab_per->nilai;
                $tahun_kki_k        = $bulan1[$row_kki_kab_per->periode] . " " . $row_kki_kab_per->tahun;
                $nilai_kki_kab[] = (float)$row_kki_kab_per->nilai;
            }
            $label_data_kki3     = $label_kki1;
            $nilai_data_kki_per3 = $nilai_kki_per;

            $ranking = $label_kki1_k;
            arsort($ranking);

            $nilai_kki_per_kab_max = max($label_kki1_k);
            $nilai_kki_per_kab_min = min($label_kki1_k);
            arsort($nilai_kki_per);
            $nama_k1 = $label_kki1_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_kki = $nilai_kki_per_kab_max - $nilai_kki_per_kab_min;
            $nrk = 1;
            foreach ($ranking as $xk => $xk_value) {
                if ($xk == $judul) {
                    $rengking_pro_k = $nrk++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $kki_perbandingan_kabb = "Perbandingan indeks keparahan kemiskinan antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada " . $tahun_kki_k . " daerah dengan tingkat indeks keparahan kemiskinan tertinggi adalah " . array_shift($nama_k2) . " (" . $nilai_kki_per_kab_max . "), sedangkan daerah dengan indeks keparahan kemiskinan terendah adalah " . end($nama_k2) . " (" . $nilai_kki_per_kab_min . "). " . $judul . " berada pada urutan ke " . $rengking_pro_k . ".";

            $paragraf_13_2 = $max_n_kki;
            $paragraf_13_3 = $max_p_kki;
            $paragraf_13_4 = $max_k_kki;
            $section = $phpWord->addSection();
            $section->addText('' . $nomor++ . '. Indeks Keparahan Kemiskinan (P2)', $fontStyleName);
            $section->addText($paragraf_13_2, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_13_3, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_13_4, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));

            //$section->addTextBreak();
            $categoriesKKI = $tahun_kki;
            $series1KKI    = $datay_kki;
            $series2KKI    = $datay_kki2;
            $series3KKI    = $datay_kki3;

            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Indeks Keparahan Kemiskinan (P2)', array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
                $chart = $section->addChart($chartType, $categoriesKKI, $series1KKI, $style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories, $series2KKI, $xname);
                    $chart->addSeries($categories, $series3KKI, $judul);
                }
                //$section->addTextBreak();
            }
            //$section = $phpWord->addSection();
            $section->addText($kki_perbandingan_kabb, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            //$section->addTextBreak();
            $categories_KKI_2 = $label_data_kki3;
            $series_KKI_2     = $nilai_data_kki_per3;

            $section->addText("Gambar " . $gambar++ . ". Perbandingan Indeks Keparahan Kemiskinan (P2) Periode " . $per_kkiPer[$periode_kab_kki_max] . " Antar Kabupaten Di " . $xname . "", array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
            $chart = $section->addChart('column', $categories_KKI_2, $series_KKI_2, $style_g2);
            $section->addText('Sumber Data: Badan Pusat Statistik', array('name' => 'Tahoma', 'size' => 10));



            //jumlah penduduk miskin
            $sql_jpm = "SELECT REF.id_periode,IND.nilai nilai_nas
            FROM(
                    select DISTINCT id_periode from nilai_indikator 
                    where (id_indikator='40' AND wilayah='1000') 
                    AND (id_periode, versi) in (
                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='40' AND wilayah='1000' AND tahun >= '" . $tahunini . "' group by id_periode
                                            )
                                order by id_periode 
                                ASC limit 6 
                        ) REF
                        LEFT JOIN(
                                        select id_periode,nilai 
                                from nilai_indikator 
                                where (id_indikator='40' AND wilayah='1000')
                                AND (id_periode, versi) in (
                                                        select id_periode, max(versi) as versi 
                                        from nilai_indikator 
                                        WHERE id_indikator='40' AND wilayah='1000' AND tahun >= '" . $tahunini . "' group by id_periode
                                        ) 
                                        group by id_periode 
                                        order by id_periode ASC limit 6
                        ) IND	ON REF.id_periode=IND.id_periode";

            $list_jpm = $this->db->query($sql_jpm);
            foreach ($list_jpm->result() as $row_jpm) {
                $tahun_jpm[]     = $bulan1[substr($row_jpm->id_periode, 4)] . " " . substr($row_jpm->id_periode, 0, -2);
                $nilaiData_jpm[] = (float)$row_jpm->nilai_nas / 1000;
            }
            $datay_jpm = $nilaiData_jpm;
            $tahun_jpm = $tahun_jpm;

            $sql_jpm2 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_prov
                    FROM(
                    select DISTINCT id_periode from nilai_indikator 
                    where (id_indikator='40' AND wilayah='1000') 
                    AND (id_periode, versi) in (
                                                        select id_periode, max(versi) as versi 
                                                        from nilai_indikator 
                                                        WHERE id_indikator='40' AND wilayah='1000' AND tahun >= '" . $tahunini . "' group by id_periode
                                                )
                    order by id_periode 
                    ASC limit 6 
                    ) REF
                    LEFT JOIN(
                            select id_periode,nilai 
                    from nilai_indikator 
                    where (id_indikator='40' AND wilayah='" . $id_pro . "')
                    AND (id_periode, versi) in (
                                            select id_periode, max(versi) as versi 
                            from nilai_indikator 
                            WHERE id_indikator='40' AND wilayah='" . $id_pro . "' AND tahun >= '" . $tahunini . "' group by id_periode
                            ) 
                            group by id_periode 
                            order by id_periode ASC limit 6
                    ) IND	ON REF.id_periode=IND.id_periode";
            $list_jpm2 = $this->db->query($sql_jpm2);
            foreach ($list_jpm2->result() as $row_jpm2) {
                $tahun_jpm2[]   = $row_jpm2->id_periode;
                $nilaiData_jpm2[] = (float)$row_jpm2->nilai_prov / 1000;
                $nilai_capaian[] = (float)$row_jpm2->nilai_prov;
            }
            $datay_jpm2 = $nilaiData_jpm2;
            $tahun_jpm2 = $tahun_jpm2;

            $sql_jpm3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k, IND.tahun, IND.periode, IND.id_periode periodeid 
                            FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='40' AND wilayah='1000') 
                                    AND (id_periode, versi) in (
                                                                        select id_periode, max(versi) as versi 
                                                                        from nilai_indikator 
                                                                        WHERE id_indikator='40' AND wilayah='1000' AND tahun >= '" . $tahunini . "' group by id_periode
                                                                )
                            order by id_periode 
                                    ASC limit 6 
                            ) REF
                            LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun,periode 
                                    from nilai_indikator 
                                    where (id_indikator='40' AND wilayah='" . $kab . "')
                                    AND (id_periode, versi) in (
                                                            select id_periode, max(versi) as versi 
                                            from nilai_indikator 
                                            WHERE id_indikator='40' AND wilayah='" . $kab . "' AND tahun >= '" . $tahunini . "' group by id_periode
                                            ) 
                                            group by id_periode 
                                            order by id_periode ASC limit 6
                            ) IND	ON REF.id_periode=IND.id_periode";

            $list_jpm3 = $this->db->query($sql_jpm3);
            foreach ($list_jpm3->result() as $row_jpm3) {
                $periode_jpm3[]     = $row_jpm3->periodeid;
                $tahun_jpm3[]     = $row_jpm3->id_periode;
                $tahun_jpm33[]     = $row_jpm3->tahun;
                $nilaiDatajpm3_1 = $row_jpm3->nilai_kab;
                if ($nilaiDatajpm3_1 = '0') {
                    $nilai_k = '#N/A';
                } else {
                    $nilai_k = (float)$row_jpm3->nilai_kab / 1000;
                }
                $nilaiDatajpm3[] = $nilai_k;
                $nilai_capaian33m[$row_jpm3->id_periode] = (float)$row_jpm3->nilai_kab;
                $sumber_jpm                             = $row_jpm3->sumber_k;
                $tahun_jpm333[$row_jpm3->id_periode]     = $bulan1[$row_jpm3->periode] . " " . $row_jpm3->tahun;
            }
            $datay_jpm3 = $nilaiDatajpm3;
            $periode_kab_jpm_maxx = max($periode_jpm3);
            $periode_kab_jpm_1 = $periode_kab_jpm_maxx - 100;
            $periode_jpm_tahun = " " . $tahun_jpm333[$periode_kab_jpm_maxx] . "";

            if ($nilai_capaian33m[$periode_kab_jpm_1] > $nilai_capaian33m[$periode_kab_jpm_maxx]) {
                $rt_jpm  = $nilai_capaian33m[$periode_kab_jpm_maxx] - $nilai_capaian33m[$periode_kab_jpm_1];
                $rt_jppm = abs($nilai_capaian33m[$periode_kab_jpm_maxx] - $nilai_capaian33m[$periode_kab_jpm_1]);
                $rt_jpm2 = $rt_jpm / $nilai_capaian33m[$periode_kab_jpm_1];
                $rt_jpm3 = abs($rt_jpm2 * 100);
                $rt_jpm33 = number_format($rt_jpm3, 2);
                $berkurangbertambah = 'berkurang';
            } else {
                $rt_jpm  = $nilai_capaian33m[$periode_kab_jpm_maxx] - $nilai_capaian33m[$periode_kab_jpm_1];
                $rt_jppm = abs($nilai_capaian33m[$periode_kab_jpm_maxx] - $nilai_capaian33m[$periode_kab_jpm_1]);
                $rt_jpm2 = $rt_jpm / $nilai_capaian33m[$periode_kab_jpm_1];
                $rt_jpm3 = $rt_jpm2 * 100;
                $rt_jpm33 = number_format($rt_jpm3, 2);
                $berkurangbertambah = 'meningkat';
            }
            $max_jpm_p  = "Jumlah penduduk miskin di " . $xnameKab . " pada " . $tahun_jpm333[$periode_kab_jpm_maxx] . " sebanyak " . number_format($nilai_capaian33m[$periode_kab_jpm_maxx], 0) . " orang. Sedangkan jumlah penduduk miskin pada " . $tahun_jpm333[$periode_kab_jpm_1] . " sebanyak " . number_format($nilai_capaian33m[$periode_kab_jpm_1], 0) . " orang. Selama periode " . $tahun_jpm333[$periode_kab_jpm_1] . " - " . $tahun_jpm333[$periode_kab_jpm_maxx] . " jumlah penduduk miskin di " . $xnameKab . " " . $berkurangbertambah . " " . number_format($rt_jpm) . " orang atau sebesar " . $rt_jpm33 . "%";

            $jpm2_kab = "select p.nama_kabupaten as label, e.* 
                        from kabupaten p
                        join nilai_indikator e on p.id = e.wilayah 
                        where p.prov_id='" . $provinsi . "' and (e.id_indikator='40' AND e.id_periode='" . $periode_kab_jpm_maxx . "') AND (wilayah, versi) in (
                        select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='40' AND id_periode='" . $periode_kab_jpm_maxx . "' group by wilayah ) 
                        group by wilayah order by wilayah asc";

            $list_kab_jpm_per = $this->db->query($jpm2_kab);
            foreach ($list_kab_jpm_per->result() as $row_jpm_kab_per) {
                $label_jpm[]      = $row_jpm_kab_per->label;
                $nilai_jpm_per[]  = $row_jpm_kab_per->nilai / 1000;
                $nilai_jpm_per1[] = $row_jpm_kab_per->nilai;
                $posisi_jpm = strpos($row_jpm_kab_per->label, "Kabupaten");
                if ($posisi_jpm !== FALSE) {
                    $label_jpm11 = substr($row_jpm_kab_per->label, 0, 3) . " " . substr($row_jpm_kab_per->label, 10);
                } else {
                    $label_jpm11 = $row_jpm_kab_per->label;
                }
                $label_jpm1[] = $label_jpm11;
                $tahun_jpm_k                   = $row_jpm_kab_per->tahun;
                $nilai_jpm_kab[]               = (float)$row_jpm_kab_per->nilai / 1000;
                $label_jpm_k[$row_jpm_kab_per->label]     = $row_jpm_kab_per->nilai;
            }
            $label_data_jpm     = $label_jpm1;
            $nilai_data_jpm_per = $nilai_jpm_per;


            $ranking = $label_jpm_k;
            arsort($ranking);

            $nilai_jpm_per_kab_max = max($label_jpm_k);
            $nilai_jpm_per_kab_min = min($label_jpm_k);
            arsort($nilai_jpm_per1);
            $nama_k1 = $label_jpm_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_jpm = $nilai_jpm_per_kab_max - $nilai_jpm_per_kab_min;

            $nrk = 1;
            foreach ($ranking as $xk => $xk_value) {
                if ($xk == $judul) {
                    $rengking_pro_k = $nrk++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $jpm_perbandingan_kab = "Perbandingan jumlah penduduk miskin antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada Periode " . $tahun_jpm333[$periode_kab_jpm_maxx] . " daerah dengan jumlah penduduk miskin tertinggi adalah " . array_shift($nama_k2) . " " . number_format($nilai_jpm_per_kab_max, 0) . " orang, sedangkan daerah jumlah penduduk miskin terendah adalah " . end($nama_k2) . " " . number_format($nilai_jpm_per_kab_min, 0) . " orang. " . $judul . " berada pada urutan ke " . $rengking_pro_k . ".";

            $section = $phpWord->addSection();
            $section->addText('' . $nomor++ . ' Perkembangan Jumlah Penduduk Miskin', $fontStyleName);
            $section->addText($deskripsi14, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($max_jpm_p, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));

            $categories_4_2 = $tahun_jpm;
            $series_4_n     = $datay_jpm;
            $series_4_3     = $datay_jpm3;

            foreach ($chartTypes2 as $chartTypes22) {
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Jumlah Penduduk Miskin (Ribu Orang)', array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
                $chart = $section->addChart($chartTypes22, $categories_4_2, $series_4_3, $style_g2, $judul);
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartTypes22, $twoSeries)) {
                }
                // $section->addTextBreak(2);
            }
            //$section = $phpWord->addSection();
            $section->addText($jpm_perbandingan_kab, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            //$section->addTextBreak();

            $categories_4_3 = $label_data_jpm;
            $series_1_4     = $nilai_data_jpm_per;
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Jumlah Penduduk Miskin Periode " . $tahun_jpm333[$periode_kab_jpm_maxx] . " Antar Kabupaten Di $xname (Ribu Orang)", array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
            $chart = $section->addChart('column', $categories_4_3, $series_1_4, $style_g2);
            $section->addText('Sumber Data: Badan Pusat Statistik', array('name' => 'Tahoma', 'size' => 10));



            //indeks pembangunan Manusia
            $sql_ipm  = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ipm = $this->db->query($sql_ipm);
            foreach ($list_ipm->result() as $row_ipm) {
                $tahun_ipm[]   = $row_ipm->tahun;
                $nilaiData_ipm[] = (float)$row_ipm->nilai;
                $nilaiData_ipm1[$row_ipm->tahun] = (float)$row_ipm->nilai;
            }
            $datay_ipm = $nilaiData_ipm;
            $tahun_ipm = $tahun_ipm;
            $sql_ipm2  = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ipm2 = $this->db->query($sql_ipm2);
            foreach ($list_ipm2->result() as $row_ipm2) {
                $tahun_ipm2[]   = $row_ipm2->tahun;
                $nilaiData_ipm2[] = (float)$row_ipm2->nilai;
                $nilaiData_ipm22[$row_ipm2->tahun] = (float)$row_ipm2->nilai;
            }
            $datay_ipm2 = $nilaiData_ipm2;
            $tahun_ipm2 = $tahun_ipm2;
            $sql_ipm3  = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='" . $kab . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='" . $kab . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
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
            $periode_kab_ipm_1 = $periode_kab_ipm_maxx - 1;
            $datay_ipm3 = $nilaiData_ipm3;
            $tahun_ipm3 = $tahun_ipm3;
            $periode_ipm_tahun = " " . max($tahun_ipm3) . "";
            if ($nilaiData_ipm33[$periode_kab_ipm_1] > $nilaiData_ipm33[$periode_kab_ipm_maxx]) {
                $max_ipm    = "Indeks pembangunan manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " menurun dibandingkan dengan tahun" . $periode_kab_ipm_1 . ". Pada tahun " . $periode_kab_ipm_maxx . " indeks pembangunan manusia " . $xnameKab . " adalah sebesar " . number_format($nilaiData_ipm33[$periode_kab_ipm_maxx], 2) . ", sedangkan pada tahun " . $periode_kab_ipm_1 . " indeks pembangunan manusia tercatat sebesar " . number_format($nilaiData_ipm33[$periode_kab_ipm_1], 2) . ". ";
                if ($nilaiData_ipm22[$periode_kab_ipm_maxx] > $nilaiData_ipm33[$periode_kab_ipm_maxx]) {
                    $max_ipm_p  = "Indeks pembangunan manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " berada dibawah capaian " . $xname . ". Indeks pembangunan manusia " . $xname . " pada tahun " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm22[$periode_kab_ipm_maxx], 2) . ".";
                } else {
                    $max_ipm_p  = "Indeks pembangunan manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " berada diatas capaian " . $xname . ". Indeks pembangunan manusia " . $xname . " pada tahun " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm22[$periode_kab_ipm_maxx], 2) . ".";
                }
            } else {
                $max_ipm    = "Indeks pembangunan manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " meningkat dibandingkan dengan tahun " . $periode_kab_ipm_1 . ". Pada " . $periode_kab_ipm_maxx . " indeks pembangunan manusia " . $xnameKab . " adalah sebesar " . number_format($nilaiData_ipm33[$periode_kab_ipm_maxx], 2) . ", sedangkan pada tahun " . $periode_kab_ipm_1 . " indeks pembangunan manusia tercatat sebesar " . number_format($nilaiData_ipm33[$periode_kab_ipm_1], 2) . ".";
                if ($nilaiData_ipm22[$periode_kab_ipm_maxx] > $nilaiData_ipm33[$periode_kab_ipm_maxx]) {
                    $max_ipm_p  = "Indeks pembangunan manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " berada dibawah capaian " . $xname . ". Indeks pembangunan manusia " . $xname . " pada tahun  " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm22[$periode_kab_ipm_maxx], 2) . ". ";
                } else {
                    $max_ipm_p  = "Indeks pembangunan manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " berada diatas capaian " . $xname . ". Indeks pembangunan manusia " . $xname . " pada tahun " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm22[$periode_kab_ipm_maxx], 2) . ". ";
                }
            }

            if ($nilaiData_ipm1[$periode_kab_ipm_maxx] > $nilaiData_ipm33[$periode_kab_ipm_maxx]) {
                $max_ipm_k    = "Capaian indeks pembangunan manusia " . $xnameKab . " pada " . $periode_kab_ipm_maxx . " berada dibawah nasional. Indeks pembangunan manusia nasional pada " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm1[$periode_kab_ipm_maxx], 2) . "% ";
            } else {
                $max_ipm_k    = "Capaian indeks pembangunan manusia " . $xnameKab . " pada " . $periode_kab_ipm_maxx . " berada diatas nasional. Indeks pembangunan manusia nasional pada " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm1[$periode_kab_ipm_maxx], 2) . "%";
            }

            $ipm_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='5' AND e.id_periode='" . $periode_kab_ipm_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='5' AND id_periode='" . $periode_kab_ipm_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_ipm_per = $this->db->query($ipm_kab);
            foreach ($list_kab_ipm_per->result() as $row_ipm_kab_per) {
                $label_ipm[]     = $row_ipm_kab_per->label;
                $nilai_ipm_per[] = $row_ipm_kab_per->nilai;
                $posisi_ipm = strpos($row_ipm_kab_per->label, "Kabupaten");
                if ($posisi_ipm !== FALSE) {
                    $label_ipm11 = substr($row_ipm_kab_per->label, 0, 3) . " " . substr($row_ipm_kab_per->label, 10);
                } else {
                    $label_ipm11 = $row_ipm_kab_per->label;
                }
                $label_ipm1[] = $label_ipm11;
                $label_ipm1_k[$row_ipm_kab_per->label] = $row_ipm_kab_per->nilai;
                $tahun_ipm_k        = $bulan1[$row_ipm_kab_per->periode] . " " . $row_ipm_kab_per->tahun;
            }
            $label_data_ipm     = $label_ipm1;
            $nilai_data_ipm_per = $nilai_ipm_per;


            $ranking = $label_ipm1_k;
            arsort($ranking);
            $nipm = 1;
            foreach ($ranking as $xk => $xk_value) {
                if ($xk == $judul) {
                    $rengking_pro_k = $nipm++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nipm++;
            }
            $nilai_ipm_per_kab_max = max($label_ipm1_k);
            $nilai_ipm_per_kab_min = min($label_ipm1_k);
            $selisih_ipm = $nilai_ipm_per_kab_max - $nilai_ipm_per_kab_min;
            $nama_k1 = $label_ipm1_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);

            $ipm_perbandingan_kab = "Perbandingan indeks pembangunan manusia antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada tahun " . $periode_kab_ipm_maxx . " daerah dengan indeks pembangunan manusia tertinggi adalah " . array_shift($nama_k2) . " (" . number_format($nilai_ipm_per_kab_max, 2) . "), sedangkan daerah dengan indeks pembangunan manusia terendah adalah " . end($nama_k2) . " (" . number_format($nilai_ipm_per_kab_min, 2) . "). " . $judul . " berada pada urutan ke " . $rengking_pro_k . ".";

            $paragraf_6_2 = $max_ipm;
            $paragraf_6_3 = $max_ipm_p;
            $paragraf_6_4 = $max_ipm_k;
            $section = $phpWord->addSection();
            $section->addText('' . $nomor++ . '. Indeks Pembangunan Manusia', $fontStyleName);
            $section->addText($deskripsi6, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));

            $section->addText($paragraf_6_2, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_6_3, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_6_4, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addTextBreak();

            $categories_6_1 = $tahun_ipm;
            $series1        = $datay_ipm;
            $series2        = $datay_ipm2;
            $series3        = $datay_ipm3;
            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Indeks Pembangunan Manusia ', array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
                $chart = $section->addChart($chartType, $categories_6_1, $series1, $style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_6_1, $series2, $xname);
                    $chart->addSeries($categories_6_1, $series3, $judul);
                }
            }
            $section = $phpWord->addSection();
            $section->addText($ipm_perbandingan_kab, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            //$section->addTextBreak();
            $categories_6_2 = $label_data_ipm;
            $series_6_2     = $nilai_data_ipm_per;
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Indeks Pembangunan Manusia Tahun " . $periode_kab_ipm_maxx . " Antar Kabupaten Di " . $xname . ".", array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
            $chart = $section->addChart('column', $categories_6_2, $series_6_2, $style_g2);
            $section->addText('Sumber Data: Badan Pusat Statistik', array('name' => 'Tahoma', 'size' => 10));


            //Gini Rasio
            $sql_gr = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_gr = $this->db->query($sql_gr);
            foreach ($list_gr->result() as $row_gr) {
                $tahun_gr[]    = $bulan[$row_gr->periode] . "-" . $row_gr->tahun;
                $nilaiData_gr[] = (float)$row_gr->nilai;
                $tahun_pr[]    = $row_gr->id_periode;
                $idperiode_gr[] = $row_gr->id_periode;
                $nilaiData_gr_n[$row_gr->id_periode] = (float)$row_gr->nilai;
            }
            $datay_gr = $nilaiData_gr;
            $tahun_gr = $tahun_gr;
            $periode_kab_gr_max = max($idperiode_gr);
            $sql_gr2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_gr2 = $this->db->query($sql_gr2);
            foreach ($list_gr2->result() as $row_gr2) {
                $tahun_gr2[]   = $row_gr2->tahun;
                $nilaiData_gr2[] = (float)$row_gr2->nilai;
                $nilaiData_gr22[] = number_format((float)$row_gr2->nilai, 2);
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
                                            where (id_indikator='7' AND wilayah='" . $kab . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='7' AND wilayah='" . $kab . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";

            $list_gr3 = $this->db->query($sql_gr3);
            foreach ($list_gr3->result() as $row_gr3) {
                $sumber_gr = $row_gr3->sumber_k;
                $n_gr3 = $row_gr3->nilai_kab;
                if ($n_gr3 == 0) {
                    $nilaiData_kgr3 = '#N/A';
                } else {
                    $nilaiData_kgr3 = (float)$n_gr3;
                }
                $nilaiData_gr3[] = $nilaiData_kgr3;
                $tahun_gr3[] = $row_gr3->tahun;
                $periode_gr3[] = $row_gr3->idperiode;
                $nilaiData_gr33[$row_gr3->id_periode] = (float)$row_gr3->nilai_kab;
                $tanggal_gr[$row_gr3->id_periode]     = $bulan1[$row_gr3->periode] . " " . $row_gr3->tahun;
            }
            $datay_gr3 = array_reverse($nilaiData_gr3);
            $tahun_gr3 = $tahun_gr3;
            $periode_kab_gr_maxx = max($periode_gr3);

            $periode_kab_gr_1 = $periode_kab_gr_maxx - 100;
            $periode_gr_tahun = "Tahun " . $tanggal_gr[$periode_kab_gr_maxx] . "";
            if ($nilaiData_gr33[$periode_kab_gr_1] > $nilaiData_gr33[$periode_kab_gr_maxx]) {
                $max_n_gr    = "Gini Rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " menurun dibandingkan dengan " . $tanggal_gr[$periode_kab_gr_1] . ". Pada " . $tanggal_gr[$periode_kab_gr_maxx] . " gini rasio " . $xnameKab . " adalah sebesar " . $nilaiData_gr33[$periode_kab_gr_maxx] . " sedangkan pada " . $tanggal_gr[$periode_kab_gr_1] . " gini rasio tercatat sebesar " . $nilaiData_gr33[$periode_kab_gr_1] . ". ";
                if ($nilaiData_gr222[$periode_kab_gr_maxx] > $nilaiData_gr33[$periode_kab_gr_maxx]) {
                    $max_p_gr  = "Capaian gini rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " berada dibawah capaian " . $xname . ". Gini rasio " . $xname . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " adalah sebesar " . $nilaiData_gr222[$periode_kab_gr_maxx] . ". ";
                } else {
                    $max_p_gr  = "Capaian gini rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " berada diatas capaian " . $xname . ". Gini rasio " . $xname . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " adalah sebesar " . $nilaiData_gr222[$periode_kab_gr_maxx] . ". ";
                }
            } else {
                $max_n_gr    = "Gini Rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " meningkat dibandingkan dengan " . $tanggal_gr[$periode_kab_gr_1] . ". Pada " . $tanggal_gr[$periode_kab_gr_maxx] . " gini rasio " . $xnameKab . " adalah sebesar " . $nilaiData_gr33[$periode_kab_gr_maxx] . " sedangkan pada " . $tanggal_gr[$periode_kab_gr_1] . " gini rasio tercatat sebesar " . $nilaiData_gr33[$periode_kab_gr_1] . ". ";
                if ($nilaiData_gr222[$periode_kab_gr_maxx] > $nilaiData_gr33[$periode_kab_gr_maxx]) {
                    $max_p_gr  = "Capaian gini rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " berada dibawah capaian " . $xname . ". Gini rasio " . $xname . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " adalah sebesar " . $nilaiData_gr222[$periode_kab_gr_maxx] . ". ";
                } else {
                    $max_p_gr  = "Capaian gini rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " berada diatas capaian " . $xname . ". Gini rasio " . $xname . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " adalah sebesar " . $nilaiData_gr222[$periode_kab_gr_maxx] . ". ";
                }
            }
            if ($nilaiData_gr_n[$periode_kab_ikk_max] > $nilaiData_gr33[$periode_kab_gr_maxx]) {
                $dibawahdiatasGRN = 'dibawah';
            } else {
                $dibawahdiatasGRN = 'diatas';
            }
            $max_gr_k    = "Capaian gini rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " berada " . $dibawahdiatasGRN . " nasional. Gini rasio nasional pada " . $tanggal_gr[$periode_kab_gr_maxx] . " adalah sebesar " . number_format($nilaiData_gr_n[$periode_kab_ikk_max], 3) . ".";
            $gr_kab = "select p.nama_kabupaten as label, e.* from kabupaten p join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='7' AND e.id_periode='" . $periode_kab_gr_maxx . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='7' AND id_periode='" . $periode_kab_gr_maxx . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";

            $list_kab_gr_per = $this->db->query($gr_kab);
            foreach ($list_kab_gr_per->result() as $row_gr_kab_per) {
                $label_gr[]     = $row_gr_kab_per->label;
                $nilai_gr_per[] = $row_gr_kab_per->nilai;
                $posisi_gr = strpos($row_gr_kab_per->label, "Kabupaten");
                if ($posisi_gr !== FALSE) {
                    $label_gr11 = substr($row_gr_kab_per->label, 0, 3) . " " . substr($row_gr_kab_per->label, 10);
                } else {
                    $label_gr11 = $row_gr_kab_per->label;
                }
                $label_gr1[] = $label_gr11;
                $label_gr1_k[$row_gr_kab_per->label] = $row_gr_kab_per->nilai;
                $tahun_gr_k        = $bulan1[$row_gr_kab_per->periode] . " " . $row_gr_kab_per->tahun;
            }
            $label_data_gr     = $label_gr1;
            $nilai_data_gr_per = $nilai_gr_per;

            $ranking = $label_gr1_k;
            arsort($ranking);

            $nilai_gr_per_kab_max = max($label_gr1_k);
            $nilai_gr_per_kab_min = min($label_gr1_k);
            arsort($nilai_gr_per);
            $nama_k1 = $label_gr1_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_gr = $nilai_gr_per_kab_max - $nilai_gr_per_kab_min;
            $nrk = 1;
            foreach ($ranking as $xk => $xk_value) {
                if ($xk == $judul) {
                    $rengking_pro_k = $nrk++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $gr_perbandingan_kab = "Perbandingan gini rasio antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada " . $tahun_gr_k . " daerah dengan gini rasio tertinggi adalah " . array_shift($nama_k2) . " (" . number_format($nilai_gr_per_kab_max, 3) . "), sedangkan daerah dengan gini rasio terendah adalah " . end($nama_k2) . " (" . number_format($nilai_gr_per_kab_min, 3) . "). " . $judul . " berada pada urutan ke " . $rengking_pro_k . ". ";

            $paragraf_7_2 = $max_n_gr;
            $paragraf_7_3 = $max_p_gr;
            $section = $phpWord->addSection();
            $section->addText('' . $nomor++ . '. Gini Rasio', $fontStyleName);
            $section->addText($deskripsi7, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));

            $section->addText($paragraf_7_2, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));
            $section->addText($paragraf_7_3, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));


            $categories_7_1 = $tahun_gr;
            $series1        = $datay_gr;
            $series2        = $datay_gr2;
            $series3        = $datay_gr3;

            foreach ($chartTypes as $chartType) {
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Gini Rasio', array('name' => 'Tahoma', 'size' => 11, 'bold' => true), array('alignment' => 'center'));
                $chart = $section->addChart($chartType, $categories_7_1, $series1, $style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_7_1, $series2, $xname);
                    $chart->addSeries($categories_7_1, $series3, $judul);
                }
            }
            $section = $phpWord->addSection();
            $section->addText($gr_perbandingan_kab, array('name' => 'Arial', 'size' => 11), array('alignment' => 'both'));

            $categories_7_2 = $label_data_gr;
            $series_7_2     = $nilai_data_gr_per;

            $section->addText("Gambar " . $gambar++ . ". Perbandingan Gini Rasio Periode " . $tahun_gr_k . " Antar Kabupaten Di " . $xname . "", array('name' => 'Arial', 'size' => 10, 'Bold' => true), array('alignment' => 'center'));
            $chart = $section->addChart('column', $categories_7_2, $series_7_2, $style_g2);
            $section->addText('Sumber Data: Badan Pusat Statistik', array('name' => 'Tahoma', 'size' => 10));



            $filename = "$judul" . '.docx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); //mime type
            header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
            header('Cache-Control: max-age=0'); //no cache
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('php://output');
        }
    }

    function download_kinerja()
    {
        $provinsi  = $_GET['inp_pro'];
        $kabupaten = $_GET['inp_sp'];
        $pro = $provinsi;
        $kab = $kabupaten;
        $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
        $bulan1 = array('00' => '', '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',);
        $prde = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
        $xname = "";
        $query = "";
        $gambar = 1;
        $nomor = 1;
        $daftarisi = 1;
        $daftargamabar = 1;
        $d_peek = "SELECT I.id,I.`deskripsi` FROM indikator I where 1=1";
        $list_peek = $this->db->query($d_peek);
        foreach ($list_peek->result() as $peek) {
            if ($peek->id == '1') {
                $deskripsi1   = $peek->deskripsi;
            }
            if ($peek->id == '2') {
                $deskripsi2   = $peek->deskripsi;
            }
            if ($peek->id == '3') {
                $deskripsi3   = $peek->deskripsi;
            }
            if ($peek->id == '4') {
                $deskripsi4   = $peek->deskripsi;
            }
            if ($peek->id == '6') {
                $deskripsi5   = $peek->deskripsi;
            }
            if ($peek->id == '5') {
                $deskripsi6   = $peek->deskripsi;
            }
            if ($peek->id == '7') {
                $deskripsi7   = $peek->deskripsi;
            }
            if ($peek->id == '8') {
                $deskripsi8   = $peek->deskripsi;
            }
            if ($peek->id == '9') {
                $deskripsi9   = $peek->deskripsi;
            }
            if ($peek->id == '10') {
                $deskripsi10   = $peek->deskripsi;
            }
            if ($peek->id == '11') {
                $deskripsi11   = $peek->deskripsi;
            }
            if ($peek->id == '36') {
                $deskripsi12   = $peek->deskripsi;
            }
            if ($peek->id == '39') {
                $deskripsi13   = $peek->deskripsi;
            }
            if ($peek->id == '38') {
                $deskripsi15   = $peek->deskripsi;
            }
            if ($peek->id == '40') {
                $deskripsi14   = $peek->deskripsi;
            }
        }

        $hal1 = base_url("assets/images/arcgis/ekonomi.jpg");
        $hal2 = base_url("assets/images/arcgis/adhb.png");
        $hal7 = base_url("assets/images/arcgis/adhk.png");
        $hal8 = base_url("assets/images/arcgis/jumlah_pengangguran_v.png");
        $hal9  = base_url("assets/images/arcgis/TPT1.jpg");
        $hal10 = base_url("assets/images/arcgis/ipm.png");
        $hal11 = base_url("assets/images/arcgis/gr.jpg");
        $hal12 = base_url("assets/images/arcgis/AHH.jpg");
        $hal13 = base_url("assets/images/arcgis/RLS.jpg");
        $hal14 = base_url("assets/images/arcgis/hls3.jpg");
        $hal15 = base_url("assets/images/arcgis/Pengeluaran_perkapita.jpg");
        $hal16 = base_url("assets/images/arcgis/Kedalaman_Kemiskinan.jpg");
        $hal17 = base_url("assets/images/arcgis/keparahan_kemiskinan.png");
        $hal18 = base_url("assets/images/arcgis/perkapita1.png");
        $hal19 = base_url("assets/images/arcgis/JPM.jpg");
        $iconh = base_url("assets/images/laporan/icon.png");


        //ASC periode 3 tahun
        $tahunini = date('Y') - 3;
        if ($provinsi == '' & $kabupaten == '') {
            $xname = "Indonesia";
            $query = "1000";
            $judul = "Indonesia";
        } elseif ($provinsi != '' & $kabupaten == '') {
            $sql_pro = "SELECT P.id, P.nama_provinsi, P.label FROM provinsi P WHERE P.`id`='" . $pro . "' ";
            $list_data = $this->db->query($sql_pro);
            foreach ($list_data->result() as $Lis_pro) {
                $xname = $Lis_pro->nama_provinsi;
                $query = "1000";
                $id_pro = $Lis_pro->id;
                $judul = $Lis_pro->nama_provinsi;
                $label_pe = $Lis_pro->label;
            }
            $logopro      = $pro . ".jpg";
            //Perkembangan Pertumbuhan Ekonomi (%)
            $sql_ppe = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppe = $this->db->query($sql_ppe);
            foreach ($list_ppe->result() as $row_ppe) {
                $tahun1[]   = $row_ppe->tahun;
                $nilaiData1[] = (float)$row_ppe->nilai;
                $nilaimax[] = number_format($row_ppe->nilai, 2);
                $periode_id[]    = $row_ppe->id_periode;
            }
            $max_pe = end($nilaiData1);
            $sql_ppe_pro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='" . $id_pro . "') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppe_pro = $this->db->query($sql_ppe_pro);

            $thn = '';
            foreach ($list_ppe_pro->result() as $row_ppe_pro) {
                $tahun1_pro[]   = $row_ppe_pro->tahun;
                $nilaiData1_pro[]   = (float)$row_ppe_pro->nilai;
                $nilaitarget[]      = (float)$row_ppe_pro->target;
                $categories_pro[]           = $row_ppe_pro->tahun;
                $nilairpjmn0 = $row_ppe_pro->t_m_rpjmn;
                if ($nilairpjmn0 == 0) {
                    $nilairpjmn1 = '#N/A';
                } else {
                    $nilairpjmn1 = (float)$nilairpjmn0;
                }
                $nilairpjmn[]       = $nilairpjmn1;
                $nilairkpd0 = $row_ppe_pro->t_rkpd;
                if ($nilairkpd0 == 0) {
                    $nilairkpd1 = '#N/A';
                } else {
                    $nilairkpd1 = (float)$nilairkpd0;
                }
                $nilairkpd[]        = $nilairkpd1;
                $nilairkp0 = $row_ppe_pro->t_k_rkp;
                if ($nilairkp0 == 0) {
                    $nilairkp1 = '#N/A';
                } else {
                    $nilairkp1 = (float)$nilairkp0;
                }
                $nilairkp[]         = $nilairkp1;
                $nilaimax_pro[] = number_format($row_ppe_pro->nilai, 2);
                $sumber_pe = $row_ppe_pro->sumber;
                $periode_pe[] = $row_ppe_pro->id_periode;
                $periode = $row_ppe_pro->periode;
                if ($periode == '00') {
                    $thn[] = $row_ppe_pro->tahun;
                } else {
                    $thn[] =  $prde[$row_ppe_pro->periode] . " - " . $row_ppe_pro->tahun;
                }
            }
            $thn_ex = $thn;
            $periode_pe_max = max($periode_pe);
            $data1 = substr($periode_pe_max, 0, 4);
            $data2 = substr($periode_pe_max, -2);
            $tahun_pro         = $categories_pro;
            if ($data2 == '00') {
                $tahun_pe_max = $data1 . " Antar Provinsi";
                $tahun_pe_max_kk = $data1 . " Antar Kab/Kota " . $xname;
            } else {
                $tahun_pe_max    =  $prde[$data2] . " - " . $data1 . " Antar Provinsi";
                $tahun_pe_max_kk =  $prde[$data2] . " - " . $data1 . " Antar Kab/Kota " . $xname;
            }
            $datay1 = $nilaiData1;
            $datay2 = $nilaiData1_pro;
            if ($nilaiData1_pro[4] > $nilaiData1_pro[5]) {
                $meningkatmenurun = 'menurun';
                if ($nilaimax[5] > $nilaimax_pro[5]) {
                    $dibawahdiatas = 'di bawah';
                } else {
                    $dibawahdiatas = 'di atas';
                }
            } else {
                $meningkatmenurun = 'meningkat';
                if ($nilaimax[5] > $nilaimax_pro[5]) {
                    $dibawahdiatas = 'di bawah';
                } else {
                    $dibawahdiatas = 'di atas';
                }
            }

            $max_pe_k  = " ";
            $perbandingan_pe = "select p.label as label,p.nama_provinsi, e.* 
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
                $nilai_p_e_r1[$row_ppe_per->label]         = $row_ppe_per->nilai;
                $nilai_p_e_r2[$row_ppe_per->nama_provinsi] = $row_ppe_per->nilai;
                $nilai_r = $row_ppe_per->nilai;
                if ($nilai_r <= 0) {
                    $nilai_r1 = 0;
                } else {
                    $nilai_r1 = $row_ppe_per->nilai;
                }
                $nilai_radar[] = (float)$nilai_r1;
            }
            $label_data_ppe     = $label_ppe;
            $nilai_data_ppe_per = $nilai_ppe_per;
            $nilai_data_tk_per = $nilai_ppe_per;

            $nilai_data_pe_r1   = $nilai_p_e_r1;
            $nilai_data_pe_r2   = $nilai_p_e_r2;
            $ranking            = $nilai_data_pe_r1;
            arsort($ranking);
            $nr = 1;
            foreach ($ranking as $x => $x_value) {
                if ($x == $label_pe) {
                    $rengking_pro = $nr++;
                }
                $urutan_pro = $x . $x_value . $x_value . $nr++;
            }
            $nilai_ppe_p_max = max($nilai_data_pe_r2);  //nila paling besar
            $nilai_ppe_p_min = min($nilai_data_pe_r2);  //nila paling rendah
            $nama_pe1 = $nilai_data_pe_r2;
            arsort($nama_pe1);
            $nama_pe2 = array_keys($nama_pe1);

            $pe_perbandingan_pro = "Perbandingan pertumbuhan ekonomi antar 34 provinsi menunjukkan bahwa pertumbuhan ekonomi " . $xname . " pada tahun " . max($categories_pro) . " berada pada urutan ke-" . $rengking_pro . ". Provinsi dengan tingkat pertumbuhan ekonomi tertinggi adalah " . array_shift($nama_pe2) . " (" . $nilai_ppe_p_max . "%), sedangkan provinsi dengan pertumbuhan ekonomi terendah adalah " . end($nama_pe2) . " (" . $nilai_ppe_p_min . "%). ";

            //radar
            $catdata_pro_r = array();
            $perbandingan_tk2 = "select p.label as label, p.nama_provinsi, e.* 
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
                if ($nilai_tk_r2 <= 0) {
                    $nilai_tk2 = 0;
                } else {
                    $nilai_tk2 = $row_tk_per2->nilai;
                }
                $nilai_pe_r22[] = (float)$nilai_tk2;
                $thn_r =  $row_tk_per2->tahun;
            }
            $label_tk2          = $label_tk2;
            $nilaiRadar_p2     = $nilai_pe_r22;
            $nilaiRadar_p1     = $nilai_radar;

            $nilaiData_p2['type'] = 'column';
            $nilaiData_p2['name'] = $thn_r;
            $nilaiData_p2['data'] = $nilai_tk_r22;
            //array_push($catdata_pro_r, $nilaiData_p2);

            $nilaiData_p1['type'] = 'line';
            $nilaiData_p1['name'] = $prov_tgl;
            $nilaiData_p1['data'] = $nilai_radar;
            //array_push($catdata_pro_r, $nilaiData_p1);

            $nilai_data_tk_r2 = $nilai_tk_r22;
            $array_tiga = array();
            for ($i = 0; $i < count($nilai_data_tk_per); $i++) {
                $array_tiga[$i] = $nilai_data_tk_per[$i] - $nilai_data_tk_r2[$i];
            }
            $kombinasi_tk = array_combine($label_tk2, $array_tiga);
            $kombinasi_tk2 = array_combine($label_tk2, $array_tiga);
            asort($kombinasi_tk2); //tinggi-rendah
            $kombinasi_tk3 = array_combine($label_tk2, $array_tiga);
            asort($kombinasi_tk3);

            $nrtkp = 1;
            foreach ($kombinasi_tk2 as $xtkp => $xtkp_value) {
                if ($xtkp == $label_pe) {
                    $rengkingtk_p = $nrtkp++;
                }
                $urutan_pro_tkp = $xtkp . $xtkp_value . $xtkp_value . $nrtkp++;
            }

            $nilai_tk_per_p_max = max($kombinasi_tk2);  //nila paling besar
            $nilai_tk_per_p_min = min($kombinasi_tk2);  //nila paling rendah

            $kombinasi_tk4 = array_combine($np_tk2, $array_tiga);
            asort($kombinasi_tk4);
            $label_p_tk = array_keys($kombinasi_tk4);
            $label_tk_per_p_max = array_shift($label_p_tk); //label paling besar
            $label_tk_per_p_min = end($label_p_tk);     //label paling kecil
            $nper_tk = abs($kombinasi_tk2[$label_pe]);

            $perbandingan_pe_2th = "Perbandingan pertumbuhan ekonomi " . $categories_pro[4] . " dengan " . $categories_pro[5] . " menunjukkan bahwa provinsi yang mengalami penurunan pertumbuhan ekonomi terbesar adalah " . $label_tk_per_p_max . " (" . number_format($nilai_tk_per_p_min, 0, ",", ".") . "%). Dari sisi perubahan pertumbuhan ekonomi tahun " . $categories_pro[4] . " hingga " . $categories_pro[5] . ", " . $xname . " berada pada urutan ke-" . $rengkingtk_p . " , dengan pertumbuhan ekonomi " . $nu_p . " sebesar " . number_format($nper_tk, 0, ",", ".") . "%";


            $th_p_kab = "select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='" . $id_pro . "' AND e.id_indikator='1' ";
            $t_list_kab_pe = $this->db->query($th_p_kab);
            foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
                $perio = $row_t_pe_kab->perio;
            }
            $ppe_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $id_pro . "' and (e.id_indikator='1' AND e.id_periode='$perio') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='1' AND id_periode='$perio' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_ppe_per = $this->db->query($ppe_kab);
            foreach ($list_kab_ppe_per->result() as $row_ppe_kab_per) {
                $nilai_ppe_per_kab[] = $row_ppe_kab_per->nilai;
                $posisi_ppe          = strpos($row_ppe_kab_per->label, "Kabupaten");
                if ($posisi_ppe !== FALSE) {
                    $label_ppe11 = substr($row_ppe_kab_per->label, 0, 3) . ". " . substr($row_ppe_kab_per->label, 10);
                } else {
                    $label_ppe11 = $row_ppe_kab_per->label;
                }
                $label_pek1[] = $label_ppe11;
                $label_pe1_k[$label_ppe11] = $row_ppe_kab_per->nilai;
                $tahun_p_k        = $bulan[$row_ppe_kab_per->periode] . "" . $row_ppe_kab_per->tahun;
                $nilai_ppe_kab[]                           = (float)$row_ppe_kab_per->nilai;
            }
            $label_pek11     = $label_pek1;
            $nilai_ppe_kab11 = $nilai_ppe_kab;

            $label_pe_k1          = $label_pek1;
            $nilaiData_k['name'] = $tahun_p_k;
            $nilaiData_k['data'] = $nilai_ppe_kab;

            $nilai_ppe_per_kab_max = max($label_pe1_k);
            $nilai_ppe_per_kab_min = min($label_pe1_k);
            arsort($nilai_ppe_per_kab);
            $nama_k1 = $label_pe1_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_pe = $nilai_ppe_per_kab_max - $nilai_ppe_per_kab_min;

            $nrk = 1;
            foreach ($nilai_ppe_per_kab as $xk => $xk_value) {
                if ($xk == $label_pe) {
                    $rengking_pro_k = $nr++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $tahun_pe_kab        = $tahun_p_k . " Antar Kabupaten/kota di " . $judul;
            $pe_perbandingan_kabb = "Perbandingan pertumbuhan ekonomi antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada tahun " . $tahun_p_k . " daerah dengan tingkat pertumbuhan ekonomi tertinggi adalah " . array_shift($nama_k2) . " (" . $nilai_ppe_per_kab_max . " %), sedangkan daerah dengan pertumbuhan ekonomi terendah adalah " . end($nama_k2) . " (" . $nilai_ppe_per_kab_min . "%). Selisih pertumbuhan ekonomi tertinggi dan terendah di " . $xname . " pada tahun " . $tahun_p_k . " adalah sebesar " . $selisih_pe . "%.";

            //jumlah pengangguran (Orang)
            $sql_jp = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jp = $this->db->query($sql_jp);
            foreach ($list_jp->result() as $row_jp) {
                $tahun_jp[]      = $bulan[$row_jp->periode] . "-" . $row_jp->tahun;
                $tahunJP[]      = $bulan1[$row_jp->periode] . " " . $row_jp->tahun;
                $tahun_jp1[]     = $row_jp->id_periode;
                $nilaiData_jp[]  = (float)$row_jp->nilai / 1000;
                $nilai_capaian[] = $row_jp->nilai;
                $tahun_jp11[] = $row_jp->tahun;
                $periode_jp1[] = $row_jp->periode;
            }
            $datay_jp = $nilaiData_jp;
            $tahun_jp = $tahun_jp;
            $periode_jp_max  = max($tahun_jp1);
            $dataJP1 = substr($periode_jp_max, 0, 4);
            $dataJP2 = substr($periode_jp_max, -2);
            if ($dataJP2 == '00') {
                $periode_jp_tahun = $dataJP1 . " Antar Provinsi";
            } else {
                $periode_jp_tahun =  $bulan1[$dataJP2] . " " . $dataJP1 . " Antar Provinsi";
            }

            $sql_jp2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jp2 = $this->db->query($sql_jp2);
            foreach ($list_jp2->result() as $row_jp2) {
                $tahun_jp2[]   = $row_jp2->tahun;
                $nilaiData_jp22 = (float)$row_jp2->nilai / 10000;
                $nilaiData_jp2[] = number_format($nilaiData_jp22, 2);
                $nilai_capaian2[] = $row_jp2->nilai;
                $sumber_jp = $row_jp->sumber;
            }
            $datay_jp2 = $nilaiData_jp2;
            $tahun_jp2 = $tahun_jp2;

            if ($nilai_capaian[3] > $nilai_capaian[5]) {
                $nn_jp = $nilai_capaian[3] - $nilai_capaian[5];
                $nn_jp2 = $nn_jp / $nilai_capaian[5];
                $nn_jp3 = $nn_jp2 * 100;
                $nn_jp33 = number_format($nn_jp3, 2);
                $max_jp  = "Jumlah penganggur nasional pada " . $tahun_jp[5] . " sebanyak " . number_format($nilai_capaian[5], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahun_jp[3] . " sebanyak " . number_format($nilai_capaian[3], 0) . " orang. Selama periode  " . $tahun_jp[3] . " - " . $tahun_jp[5] . " jumlah penganggur nasional berkurang " . number_format($nn_jp) . " orang atau sebesar " . $nn_jp33 . "% ";
            } else {
                $nn_jp  = $nilai_capaian[5] - $nilai_capaian[3];
                $nn_jp2 = $nn_jp / $nilai_capaian[3];
                $nn_jp3 = $nn_jp2 * 100;
                $nn_jp33 = number_format($nn_jp3, 2);
                $max_jp  = "Jumlah penganggur nasional pada " . $tahun_jp[5] . " sebanyak " . number_format($nilai_capaian[5], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahun_jp[3] . " sebanyak " . number_format($nilai_capaian2[3], 0) . " orang. Selama periode  " . $tahun_jp[3] . " - " . $tahun_jp[5] . " jumlah penganggur di " . $xname . " meningkat " . number_format($nn_jp) . " orang atau sebesar " . number_format($nn_jp33) . "%";
            }

            if ($nilai_capaian2[3] > $nilai_capaian2[5]) {
                $rt_jp = $nilai_capaian2[5] - $nilai_capaian2[3];
                $rt_jpp = abs($nilai_capaian2[5] - $nilai_capaian2[3]);
                $rt_jp2 = $rt_jp / $nilai_capaian2[3];
                $rt_jp3 = abs($rt_jp2 * 100);
                $rt_jp33 = number_format($rt_jp3, 2);
                $berkurangmeningkat = 'meningkat';
            } else {
                $rt_jp  = $nilai_capaian2[5] - $nilai_capaian2[3];
                $rt_jpp = abs($nilai_capaian2[3] - $nilai_capaian2[5]);
                $rt_jp2 = $rt_jp / $nilai_capaian2[3];
                $rt_jp3 = $rt_jp2 * 100;
                $rt_jp33 = number_format($rt_jp3, 2);
                $berkurangmeningkat = 'meningkat';
            }

            $perbandingan_jp = "select p.label as label, e.* 
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
                $nilai_jp_per[] = (float)$row_jp_per->nilai / 10000;
            }
            $label_data_jp     = $label_jp;
            $nilai_data_jp_per = $nilai_jp_per;

            //tingkat pengangguran terbuka
            $sql_tpt = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tpt = $this->db->query($sql_tpt);
            foreach ($list_tpt->result() as $row_tpt) {
                $tahun_tpt1[]    = $bulan[$row_tpt->periode] . "-" . $row_tpt->tahun;
                $tahun_tpt[]     = $row_tpt->tahun;
                $nilaiData_tpt1  = (float)$row_tpt->nilai;
                $nilaiData_tpt[] = number_format($nilaiData_tpt1, 2);
                $categories1[]   = $bulan[$row_tpt->periode] . " " . $row_tpt->tahun;
            }
            $datay_tpt = $nilaiData_tpt;
            $tahun_tpt = $tahun_tpt1;

            $sql_tpt2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tpt2 = $this->db->query($sql_tpt2);
            foreach ($list_tpt2->result() as $row_tpt2) {
                $tahun_tpt21[]    = $bulan[$row_tpt2->periode] . "-" . $row_tpt2->tahun;
                $tahunTPT2[]      = $bulan1[$row_tpt2->periode] . " " . $row_tpt2->tahun;
                $periode_tpt21[]  = $row_tpt2->periode;
                $tahun_tpt2[]     = $row_tpt2->tahun;
                $nilaiData_tpt22  = (float)$row_tpt2->nilai;
                $nilaiData_tpt2[] = number_format($nilaiData_tpt22, 2);
                $sumber_tpt       = $row_tpt2->sumber;
                $periode_tpt_id[] =   $row_tpt2->id_periode;

                $nilaiTarget_tpt0 = (float)$row_tpt2->target;
                if ($nilaiTarget_tpt0 == 0) {
                    $nilaiTarget_tpt1 = '#N/A';
                } else {
                    $nilaiTarget_tpt1 = number_format($nilaiTarget_tpt0, 2);
                }
                $nilaiTarget_tpt2[] = $nilaiTarget_tpt1;
                $nilaiRpjmn_tpt0    = (float)$row_tpt2->t_m_rpjmn;
                if ($nilaiRpjmn_tpt0 == 0) {
                    $nilaiRpjmn_tpt1 = '#N/A';
                } else {
                    $nilaiRpjmn_tpt1 = number_format($nilaiRpjmn_tpt0, 2);
                }
                $nilaiRpjmn_tpt2[] = $nilaiRpjmn_tpt1;
                $nilaiRKPD_tpt0    = (float)$row_tpt2->t_rkpd;
                if ($nilaiRKPD_tpt0 == 0) {
                    $nilaiRKPD_tpt1 = '#N/A';
                } else {
                    $nilaiRKPD_tpt1 = number_format($nilaiRKPD_tpt0, 2);
                }
                $nilaiRKPD_tpt2[] = $nilaiRKPD_tpt1;
                $nilaiRKP_tpt0    = (float)$row_tpt2->t_k_rkp;
                if ($nilaiRKP_tpt0 == 0) {
                    $nilaiRKP_tpt1 = '#N/A';
                } else {
                    $nilaiRKP_tpt1 = number_format($nilaiRKP_tpt0, 2);
                }
                $nilaiRKP_tpt2[] = $nilaiRKP_tpt1;
            }
            $datay_tpt2 = $nilaiData_tpt2;
            $tahun_tpt2 = $tahun_tpt2;
            $nilaiTarget_tpt = $nilaiTarget_tpt2;
            $nilaiRpjmn_tpt = $nilaiRpjmn_tpt2;
            $nilaiRKPD_tpt = $nilaiRKPD_tpt2;
            $nilaiRKP_tpt = $nilaiRKP_tpt2;


            $periode_tpt_max = max($periode_tpt_id);
            $dataTPT1 = substr($periode_tpt_max, 0, 4);
            $dataTPT2 = substr($periode_tpt_max, -2);
            if ($dataTPT2 == '00') {
                $periode_tpt_tahun = $dataTPT2 . " Antar Provinsi";
                $periode_tpt_kk = $dataTPT2 . " Antar Provinsi";
            } else {
                $periode_tpt_tahun =  $bulan1[$dataTPT2] . " " . $dataTPT1 . " Antar Provinsi";
                $periode_tpt_kk =  $bulan1[$dataTPT2] . " " . $dataTPT1 . " Antar Provinsi";
            }

            if ($nilaiData_tpt2[3] > $nilaiData_tpt2[5]) {
                $menurunmeningkatTPT = 'menurun';
                if ($nilaiData_tpt[5] > $nilaiData_tpt2[5]) {
                    $dibawahdiatasTPT = 'di bawah';
                } else {
                    $dibawahdiatasTPT = 'di atas';
                }
            } else {
                $menurunmeningkatTPT = 'meningkat';
                if ($nilaiData_tpt[5] > $nilaiData_tpt2[5]) {
                    $dibawahdiatasTPT = 'di bawah';
                } else {
                    $dibawahdiatasTPT = 'di atas';
                }
            }

            $max_tpt_k = " ";
            $perbandingan_tpt = "select p.label as label,p.nama_provinsi, e.* 
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
                $nilai_p_e_r1[$row_tpt_per->label]         = $row_tpt_per->nilai;
                $nilai_p_e_r2[$row_tpt_per->nama_provinsi] = $row_tpt_per->nilai;
            }
            $label_data_tpt     = $label_tpt;
            $nilai_data_tpt_per = $nilai_tpt_per;

            $nilai_data_pe_r1   = $nilai_p_e_r1;
            $nilai_data_pe_r2   = $nilai_p_e_r2;
            $ranking            = $nilai_data_pe_r1;
            arsort($ranking);
            $nr = 1;
            foreach ($ranking as $x => $x_value) {
                if ($x == $label_pe) {
                    $rengking_pro = $nr++;
                }
                $urutan_pro = $x . $x_value . $x_value . $nr++;
            }
            $tahun_tk_p_max = $categories1[5];
            $tahun_tk_p_2 = $categories1[3];
            $nilai_ppe_p_max = max($nilai_data_pe_r2);  //nila paling besar
            $nilai_ppe_p_min = min($nilai_data_pe_r2);  //nila paling rendah
            $nama_pe1 = $nilai_data_pe_r2;
            arsort($nama_pe1);
            $nama_pe2 = array_keys($nama_pe1);
            $tpt_perbandingan_pro = "Perbandingan tingkat pengangguran terbuka antar 34 provinsi menunjukkan bahwa tingkat pengangguran terbuka " . $xname . " pada " . $tahun_tk_p_max . " berada pada urutan ke-" . $rengking_pro . ". Provinsi dengan tingkat pengangguran terbuka tertinggi adalah " . array_shift($nama_pe2) . " (" . $nilai_ppe_p_max . "%), sedangkan provinsi dengan tingkat pengangguran terbuka terendah adalah " . end($nama_pe2) . " (" . $nilai_ppe_p_min . "%). ";

            $th_tpt_kab = "select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='" . $id_pro . "' AND e.id_indikator='6' ";
            $t_list_kab_tpt = $this->db->query($th_tpt_kab);
            foreach ($t_list_kab_tpt->result() as $row_t_tpt_kab) {
                $perio = $row_t_tpt_kab->perio;
            }
            $tpt_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $id_pro . "' and (e.id_indikator='6' AND e.id_periode='$perio') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='6' AND id_periode='$perio' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_tpt_per = $this->db->query($tpt_kab);
            foreach ($list_kab_tpt_per->result() as $row_tpt_kab_per) {
                $nilai_tpt_per_kab[] = $row_tpt_kab_per->nilai;
                $posisi_ppe          = strpos($row_tpt_kab_per->label, "Kabupaten");
                if ($posisi_ppe !== FALSE) {
                    $label_ppe11 = substr($row_tpt_kab_per->label, 0, 3) . ". " . substr($row_tpt_kab_per->label, 10);
                } else {
                    $label_ppe11 = $row_tpt_kab_per->label;
                }
                $label_tpt_k[] = $label_ppe11;
                $label_pe1_k[$label_ppe11] = $row_tpt_kab_per->nilai;
                $tahun_p_k        = $bulan[$row_tpt_kab_per->periode] . " " . $row_tpt_kab_per->tahun;
                $nilai_ppe_kab[]  = (float)$row_tpt_kab_per->nilai;
            }
            $label_tpt_k1            = $label_tpt_k;
            $nilai_data_tpt_per_kab = $nilai_tpt_per_kab;

            $nilai_ppe_per_kab_max = max($label_pe1_k);
            $nilai_ppe_per_kab_min = min($label_pe1_k);
            arsort($nilai_ppe_per_kab);
            $nama_k1 = $label_pe1_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_pe = $nilai_ppe_per_kab_max - $nilai_ppe_per_kab_min;

            $nrk = 1;
            foreach ($nilai_ppe_per_kab as $xk => $xk_value) {
                if ($xk == $label_pe) {
                    $rengking_pro_k = $nr++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $tahun_pe_kab = $tahun_p_k . " Antar Kabupaten/kota di " . $xname;
            $tpt_perbandingan_kab = "Perbandingan tingkat pengangguran terbuka antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada periode " . $tahun_p_k . " daerah dengan tingkat tingkat pengangguran terbuka tertinggi adalah " . array_shift($nama_k2) . " (" . number_format($nilai_ppe_per_kab_max, 2, ",", ".") . "%), sedangkan daerah dengan tingkat pengangguran terbuka terendah adalah " . end($nama_k2) . " (" . number_format($nilai_ppe_per_kab_min, 2, ",", ".") . "%).";


            //indeks pembangunan Manusia
            $sql_ipm = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ipm = $this->db->query($sql_ipm);
            foreach ($list_ipm->result() as $row_ipm) {
                $tahun_ipm[]   = $row_ipm->tahun;
                $nilaiData_ipm[] = (float)$row_ipm->nilai;
            }
            $datay_ipm = $nilaiData_ipm;
            $tahun_ipm = $tahun_ipm;
            $sql_ipm2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ipm2 = $this->db->query($sql_ipm2);
            foreach ($list_ipm2->result() as $row_ipm2) {
                $tahun_ipm2[]   = $row_ipm2->tahun;
                $nilaiData_ipm2[] = (float)$row_ipm2->nilai;
                $sumber_ipm       = $row_ipm2->sumber;
                $periode_ipm_id[] = $row_ipm2->id_periode;
                $tahun_ipm21[]    = $bulan[$row_ipm2->periode] . "-" . $row_ipm2->tahun;
                $nilaiTarget_ipm0 = (float)$row_ipm2->target;
                if ($nilaiTarget_ipm0 == 0) {
                    $nilaiTarget_ipm1 = '#N/A';
                } else {
                    $nilaiTarget_ipm1 = number_format($nilaiTarget_ipm0, 2);
                }
                $nilaiTarget_ipm2[] = $nilaiTarget_ipm1;

                $nilaiRpjmn_ipm0    = (float)$row_ipm2->t_m_rpjmn;
                if ($nilaiRpjmn_ipm0 == 0) {
                    $nilaiRpjmn_ipm1 = '#N/A';
                } else {
                    $nilaiRpjmn_ipm1 = number_format($nilaiRpjmn_ipm0, 2);
                }
                $nilaiRpjmn_ipm2[] = $nilaiRpjmn_ipm1;
                $nilaiRKPD_ipm0    = (float)$row_ipm2->t_rkpd;
                if ($nilaiRKPD_ipm0 == 0) {
                    $nilaiRKPD_ipm1 = '#N/A';
                } else {
                    $nilaiRKPD_ipm1 = number_format($nilaiRKPD_ipm0, 2);
                }
                $nilaiRKPD_ipm2[] = $nilaiRKPD_ipm1;
                $nilaiRKP_ipm0    = (float)$row_ipm2->t_k_rkp;
                if ($nilaiRKP_ipm0 == 0) {
                    $nilaiRKP_ipm1 = '#N/A';
                } else {
                    $nilaiRKP_ipm1 = number_format($nilaiRKP_ipm0, 2);
                }
                $nilaiRKP_ipm2[] = $nilaiRKP_ipm1;
            }

            $datay_ipm2 = $nilaiData_ipm2;
            $tahun_ipm2 = $tahun_ipm2;
            $nilaiTarget_ipm = $nilaiTarget_ipm2;
            $nilaiRpjmn_ipm = $nilaiRpjmn_ipm2;
            $nilaiRKPD_ipm = $nilaiRKPD_ipm2;
            $nilaiRKP_ipm = $nilaiRKP_ipm2;

            $max_ipm_k = "";
            $periode_ipm_max = max($periode_ipm_id);
            $periode_ipm_tahun = max($tahun_ipm2) . " Antar Provinsi";
            //$paragraf_6_3='';
            if ($nilaiData_ipm2[4] > $nilaiData_ipm2[5]) {
                $menurunmeningkatIPM = 'menurun';
                if ($nilaiData_ipm[5] > $nilaiData_ipm2[5]) {
                    $dibawahdiatasIPM = 'dibawah';
                } else {
                    $dibawahdiatasIPM = 'diatas';
                }
            } else {
                $menurunmeningkatIPM = 'meningkat';
                $paragraf_6_2 = "Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " meningkat dibandingkan dengan tahun " . $tahun_ipm2[4] . ". Pada tahun " . $tahun_ipm2[5] . " Indeks Pembangunan Manusia " . $xname . " adalah sebesar " . number_format(end($nilaiData_ipm2), 2) . "% sedangkan pada tahun " . $tahun_ipm2[4] . "  Indeks Pembangunan Manusia tercatat sebesar " . number_format($nilaiData_ipm2[4], 2) . "%. ";
                if ($nilaiData_ipm[5] > $nilaiData_ipm2[5]) {
                    $dibawahdiatasIPM = 'dibawah';
                } else {
                    $dibawahdiatasIPM = 'diatas';
                }
            }

            $perbandingan_ipm = "select p.label as label,p.nama_provinsi, e.* from provinsi p  join nilai_indikator e on p.id = e.wilayah  where (e.id_indikator='5' AND e.id_periode='$periode_ipm_max') AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='5' AND id_periode='$periode_ipm_max' group by wilayah) group by wilayah order by wilayah asc";
            $list_ipm_per = $this->db->query($perbandingan_ipm);
            foreach ($list_ipm_per->result() as $row_ipm_per) {
                $label_ipm[]     = $row_ipm_per->label;
                $nilai_ipm_per1 = $row_ipm_per->nilai;
                $nilai_ipm_per[] = number_format($nilai_ipm_per1, 2);
                $nilai_ipm_r1[$row_ipm_per->label]         = $row_ipm_per->nilai;
                $nilai_ipm_r2[$row_ipm_per->nama_provinsi] = $row_ipm_per->nilai;
            }
            $label_data_ipm     = $label_ipm;
            $nilai_data_ipm_per = $nilai_ipm_per;

            $label_ppe1          = $label_ppe;

            $nilai_data_ipm_r1   = $nilai_ipm_r1;
            $nilai_data_ipm_r2   = $nilai_ipm_r2;
            $ranking            = $nilai_data_ipm_r1;
            arsort($ranking);
            $nr = 1;
            foreach ($ranking as $x => $x_value) {
                if ($x == $label_pe) {
                    $rengking_pro = $nr++;
                }
                $urutan_pro = $x . $x_value . $x_value . $nr++;
            }
            $tahun_tk_p_max = $categories1[5];
            $nilai_ppe_ipm_max = max($nilai_data_ipm_r2);  //nila paling besar
            $nilai_ppe_ipm_min = min($nilai_data_ipm_r2);  //nila paling rendah
            $nama_ìpm1 = $nilai_data_ipm_r2;
            arsort($nama_ìpm1);
            $nama_ipm2 = array_keys($nama_ìpm1);
            $ipm_perbandingan_pro = "Perbandingan indeks pembangunan manusia antar 34 provinsi menunjukkan bahwa indeks pembangunan manusia " . $xname . " pada " . $tahun_tk_p_max . " berada pada urutan ke-" . $rengking_pro . ". Provinsi dengan indeks pembangunan manusia tertinggi adalah " . array_shift($nama_ipm2) . " (" . $nilai_ppe_ipm_max . "), sedangkan provinsi dengan indeks pembangunan manusia terendah adalah " . end($nama_ipm2) . " (" . $nilai_ppe_ipm_min . "). ";

            $th_ipm_kab = "select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='" . $id_pro . "' AND e.id_indikator='36' ";
            $t_list_kab_ipm = $this->db->query($th_ipm_kab);
            foreach ($t_list_kab_ipm->result() as $row_ipm_kab) {
                $perio = $row_ipm_kab->perio;
            }

            $ipm_kab = "select p.nama_kabupaten as label, e.* from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='" . $id_pro . "' and (e.id_indikator='36' AND e.id_periode='$perio') AND (wilayah, versi) in ( select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='36' AND id_periode='$perio' group by wilayah )  group by wilayah order by wilayah asc";
            $list_kab_ipm_per = $this->db->query($ipm_kab);
            foreach ($list_kab_ipm_per->result() as $row_ipm_kab_per) {
                if ($posisi_ipm !== FALSE) {
                    $label_ipm11 = substr($row_ipm_kab_per->label, 0, 3) . ". " . substr($row_ipm_kab_per->label, 10);
                } else {
                    $label_ipm11 = $row_ipm_kab_per->label;
                }
                $nilai_ipm_per_kab[]        = $row_ipm_kab_per->nilai;
                $posisi_ipm                 = strpos($row_ipm_kab_per->label, "Kabupaten");
                $label_pek1[]               = $label_ipm11;
                $label_ipm_k[$label_ipm11]  = $row_ipm_kab_per->nilai;
                $label_ipm_kk[$row_ipm_kab_per->label]  = $row_ipm_kab_per->nilai;
                $tahun_ipm_k                = $row_ipm_kab_per->tahun;
                $nilai_ppe_kab[]            = (float)$row_ipm_kab_per->nilai;
            }
            $label_ipm_k1       = $label_pek1;
            $nilai_data_ipm_kab = $nilai_ipm_per_kab;

            $nilai_ppe_per_kab_max = max($label_ipm_kk);
            $nilai_ppe_per_kab_min = min($label_ipm_kk);
            arsort($nilai_ppe_per_kab);
            $nama_k1 = $label_ipm_kk;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_pe = $nilai_ppe_per_kab_max - $nilai_ppe_per_kab_min;

            $nrk = 1;
            foreach ($nilai_ppe_per_kab as $xk => $xk_value) {
                if ($xk == $label_pe) {
                    $rengking_pro_k = $nr++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $tahun_pe_kab = $tahun_ipm_k . " Antar Kabupaten/kota di " . $judul;
            $ipm_perbandingan_kab = "Perbandingan indeks pembangunan manusia antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada " . $tahun_ipm_k . " daerah dengan tingkat indeks pembangunan manusia tertinggi adalah " . array_shift($nama_k2) . " (" . $nilai_ppe_per_kab_max . "), sedangkan daerah dengan indeks pembangunan manusia terendah adalah " . end($nama_k2) . " (" . $nilai_ppe_per_kab_min . ").";

            //Gini Rasio.
            $sql_gr = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_gr = $this->db->query($sql_gr);
            foreach ($list_gr->result() as $row_gr) {
                $tahun_gr[]    = $bulan[$row_gr->periode] . "-" . $row_gr->tahun;
                $tahun_grR[]    = $bulan1[$row_gr->periode] . " " . $row_gr->tahun;
                $nilai_gr = number_format((float)$row_gr->nilai, 3);
                $nilaiData_gr[] = $nilai_gr;
            }
            $datay_gr = $nilaiData_gr;
            $tahun_gr = $tahun_gr;
            $sql_gr2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_gr2 = $this->db->query($sql_gr2);
            foreach ($list_gr2->result() as $row_gr2) {
                $tahun_gr2[]   = $row_gr2->tahun;
                $periode    = $row_gr2->periode;
                $tahungr2    = $row_gr2->tahun;
                $nilaiData_gr2[] = (float)$row_gr2->nilai;
                $nilai_gr22 = number_format((float)$row_gr2->nilai, 3);
                $nilaiData_gr22[] = $nilai_gr22;

                $nilaiTarget_gr0 = (float)$row_gr2->target;
                if ($nilaiTarget_gr0 == 0) {
                    $nilaiTarget_gr1 = '#N/A';
                } else {
                    $nilaiTarget_gr1 = number_format($nilaiTarget_gr0, 3);
                }
                $nilaiTarget_gr2[] = $nilaiTarget_gr1;

                $nilaiRpjmn_gr0    = (float)$row_gr2->t_m_rpjmn;
                if ($nilaiRpjmn_gr0 == 0) {
                    $nilaiRpjmn_gr1 = '#N/A';
                } else {
                    $nilaiRpjmn_gr1 = number_format($nilaiRpjmn_gr0, 3);
                }
                $nilaiRpjmn_gr2[] = $nilaiRpjmn_gr1;

                $nilaiRKPD_gr0    = (float)$row_gr2->t_rkpd;
                if ($nilaiRKPD_gr0 == 0) {
                    $nilaiRKPD_gr1 = '#N/A';
                } else {
                    $nilaiRKPD_gr1 = number_format($nilaiRKPD_gr0, 3);
                }
                $nilaiRKPD_gr2[] = $nilaiRKPD_gr1;
                $nilaiRKP_gr0    = (float)$row_gr2->t_k_rkp;
                if ($nilaiRKP_gr0 == 0) {
                    $nilaiRKP_gr1 = '#N/A';
                } else {
                    $nilaiRKP_gr1 = number_format($nilaiRKP_gr0, 2);
                }
                $nilaiRKP_gr2[] = $nilaiRKP_gr1;

                $sumber_gr       = $row_gr2->sumber;
                $periode_gr_id[]    = $row_gr2->id_periode;
                $tahun_gr21[]    = $bulan[$row_gr2->periode] . "-" . $row_gr2->tahun;
            }
            $datay_gr2 = $nilaiData_gr22;
            $datay_gr3 = $nilaiTarget_gr2;
            $datay_gr4 = $nilaiRpjmn_gr2;
            $datay_gr5 = $nilaiRKPD_gr2;
            $datay_gr6 = $nilaiRKP_gr2;

            $tahun_gr2 = $tahun_gr2;
            $max_k_gr  =  "";
            $periode_gr_max   = max($periode_gr_id);
            if ($periode == '00') {
                $periode_gr_tahun = $tahun_gr2 . " Antar Provinsi";
            } else {
                $periode_gr_tahun =  $bulan1[$periode] . " " . $tahungr2 . " Antar Provinsi";
            }
            if ($nilaiData_gr2[3] > $nilaiData_gr2[5]) {
                $menurunmeningkatGR = 'menurun';
                if ($nilaiData_gr[5] > $nilaiData_gr2[5]) {
                    $dibawahdiatasGR = 'di bawah';
                } else {
                    $dibawahdiatasGR = 'di atas';
                }
            } elseif ($nilaiData_gr2[3] < $nilaiData_gr2[5]) {
                $menurunmeningkatGR = 'meningkat';
                if ($nilaiData_gr[5] > $nilaiData_gr2[5]) {
                    $dibawahdiatasGR = 'di bawah';
                } else {
                    $dibawahdiatasGR = 'di atas';
                }
            } else {
                $menurunmeningkatGR = 'sama';
                if ($nilaiData_gr[5] > $nilaiData_gr2[5]) {
                    $dibawahdiatasGR = 'di bawah';
                } else {
                    $dibawahdiatasGR = 'di atas';
                }
            }

            $perbandingan_gr = "select p.label as label, e.* 
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
                $nilai_gr_per[] = number_format($nilai_gr_per1, 3);
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
            $sql_ahh2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ahh2 = $this->db->query($sql_ahh2);
            foreach ($list_ahh2->result() as $row_ahh2) {
                $tahun_ahh2[]   = $row_ahh2->tahun;
                $nilaiData_ahh2[] = (float)$row_ahh2->nilai;
                $sumber_ahh       = $row_ahh2->sumber;
                $periode_ahh_id[] = $row_ahh2->id_periode;
                $tahun_ahh21[]    = $bulan[$row_ahh2->periode] . "-" . $row_ahh2->tahun;
            }
            $datay_ahh2 = $nilaiData_ahh2;
            $max_k_ahh = "";
            $periode_ahh_max = max($periode_ahh_id);
            $periode_ahh_tahun = max($tahun_ahh2) . " Antar Provinsi";
            if ($nilaiData_ahh2[4] > $nilaiData_ahh2[5]) {
                $menurunmeningkatAHH = 'menurun';
                if ($nilaiData_ahh[5] > $nilaiData_ahh2[5]) {
                    $dibawahdiatasAHH = 'dibawah';
                    //$paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                } else {
                    $dibawahdiatasAHH = 'diatas';
                    //$paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada diatas capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                }
            } else {
                $menurunmeningkatAHH = 'meningkat';
                if ($nilaiData_ahh[5] > $nilaiData_ahh2[5]) {
                    $dibawahdiatasAHH = 'dibawah';
                    // $paragraf_8_3  ="Capaian Angka Harapan Hidup ". $xname ." pada tahun ".$tahun_ahh[5]." berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun ".$tahun_ahh[5]." adalah sebesar ".number_format(end($nilaiData_ahh),2) ." tahun ";
                } else {
                    $dibawahdiatasAHH = 'diatas';
                }
            }

            $perbandingan_ahh = "select p.label as label, e.* 
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
            $sql_rls2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_rls2 = $this->db->query($sql_rls2);
            foreach ($list_rls2->result() as $row_rls2) {
                $tahun_rls2[]   = $row_rls2->tahun;
                $nilaiData_rls2[] = (float)$row_rls2->nilai;
                $sumber_rls = $row_rls2->sumber;
                $periode_rls_id[] = $row_rls2->id_periode;
                $tahun_rls21[]    = $bulan[$row_rls2->periode] . "-" . $row_rls2->tahun;
            }
            $datay_rls2 = $nilaiData_rls2;
            $tahun_rls2 = $tahun_rls2;
            $periode_rls_max = max($periode_rls_id);
            $periode_rls_tahun = max($tahun_rls2) . " Antar Provinsi";
            if ($nilaiData_rls2[4] > $nilaiData_rls2[5]) {
                $menurunmeningkatRLS = 'menurun';
                if ($nilaiData_rls[5] > $nilaiData_rls2[5]) {
                    $dibawahdiatasRLS = 'di bawah';
                } else {
                    $dibawahdiatasRLS = 'di atas';
                }
            } else {
                $menurunmeningkatRLS = 'meningkat';
                if ($nilaiData_rls[5] > $nilaiData_rls2[5]) {
                    $dibawahdiatasRLS = 'di bawah';
                } else {
                    $dibawahdiatasRLS = 'di atas';
                }
            }

            $max_k_rls = "";
            $perbandingan_rls = "select p.label as label, e.* 
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
            $sql_hls2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_hls2 = $this->db->query($sql_hls2);
            foreach ($list_hls2->result() as $row_hls2) {
                $tahun_hls2[]   = $row_hls2->tahun;
                $nilaiData_hls2[] = (float)$row_hls2->nilai;
                $sumber_hls = $row_hls2->sumber;
                $periode_hls_id[] = $row_hls2->id_periode;
                $tahun_hls21[]    = $bulan[$row_hls2->periode] . "-" . $row_hls2->tahun;
            }
            $datay_hls2 = $nilaiData_hls2;
            $tahun_hls2 = $tahun_hls2;
            $periode_hls_max = max($periode_hls_id);
            $periode_hls_tahun = max($tahun_hls2) . " Antar Provinsi";
            if ($nilaiData_hls2[4] > $nilaiData_hls2[5]) {
                $menurunmeningkatHLS = 'menurun';
                if ($nilaiData_hls[5] > $nilaiData_hls2[5]) {
                    $dibawahdiatasHLS = 'di bawah';
                } else {
                    $dibawahdiatasHLS = 'di atas';
                }
            } else {
                $menurunmeningkatHLS = 'meningkat';
                if ($nilaiData_hls[5] > $nilaiData_hls2[5]) {
                    $dibawahdiatasHLS = 'di bawah';
                } else {
                    $dibawahdiatasHLS = 'di atas';
                }
            }

            $max_k_hls = "";
            $perbandingan_hls = "select p.label as label, e.* 
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
                $nilai_ppk1 = number_format((float)$row_ppk->nilai / 1000000, 2);
                $nilaiData_ppk1[] = $nilai_ppk1;
            }
            $datay_ppk = $nilaiData_ppk1;
            $tahun_ppk = $tahun_ppk;
            $sql_ppk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppk2 = $this->db->query($sql_ppk2);
            foreach ($list_ppk2->result() as $row_ppk2) {
                $tahun_ppk2[]     = $row_ppk2->tahun;
                $nilaiData_ppk2[] = (float)$row_ppk2->nilai;
                $nilai_ppk22 = number_format((float)$row_ppk2->nilai / 1000000, 2);
                $nilaiData_ppk22[] = $nilai_ppk22;
                $sumber_ppk       = $row_ppk->sumber;
                $periode_ppk_id[] = $row_ppk2->id_periode;
                $tahun_ppk21[]    = $bulan[$row_ppk2->periode] . "-" . $row_ppk2->tahun;
            }
            $datay_ppk2 = $nilaiData_ppk22;
            $tahun_ppk2 = $tahun_ppk2;
            $periode_ppk_max   = max($periode_ppk_id);
            $periode_ppk_tahun = max($tahun_ppk2) . " Antar Provinsi";
            if ($nilaiData_ppk2[4] > $nilaiData_ppk2[5]) {
                $menurunmeningkatPPK = 'menurun';
                if ($nilaiData_ppk[5] > $nilaiData_ppk2[5]) {
                    $dibawahdiatasPPK = 'di bawah';
                } else {
                    $dibawahdiatasPPK = 'di atas';
                }
            } else {
                $menurunmeningkatPPK = 'meningkat';
                if ($nilaiData_ppk[5] > $nilaiData_ppk2[5]) {
                    $dibawahdiatasPPK = 'di bawah';
                } else {
                    $dibawahdiatasPPK = 'di atas';
                }
            }

            $max_k_ppk = "";
            $perbandingan_ppk = "select p.label as label, e.* 
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
                $nilai_ppk       = number_format($row_ppk_per->nilai / 1000000, 2);
                $nilai_ppk_per[] = $nilai_ppk;
            }
            $label_data_ppk     = $label_ppk;
            $nilai_data_ppk_per = $nilai_ppk_per;


            //Tingkat Kemiskinan
            $sql_tk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tk = $this->db->query($sql_tk);
            foreach ($list_tk->result() as $row_tk) {
                $tahun_tk[]    = $bulan[$row_tk->periode] . "-" . $row_tk->tahun;
                $tahun_tkk[]    = $bulan1[$row_tk->periode] . " " . $row_tk->tahun;
                $nilaiData_tk[] = (float)$row_tk->nilai;
            }
            $datay_tk = $nilaiData_tk;
            $tahun_tk = $tahun_tk;
            $sql_tk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tk2 = $this->db->query($sql_tk2);
            foreach ($list_tk2->result() as $row_tk2) {
                $tahun_tk2[]   = $row_tk2->tahun;
                $tahun_tk22   = $row_tk2->tahun;
                $nilaiData_tk2[] = (float)$row_tk2->nilai;

                $tknilairpjmn0 = $row_tk2->t_m_rpjmn;
                if ($tknilairpjmn0 == 0) {
                    $tknilairpjmn1 = '#N/A';
                } else {
                    $tknilairpjmn1 = (float)$tknilairpjmn0;
                }
                $tknilairpjmn[]       = $tknilairpjmn1;
                $tknilairkpd0 = $row_tk2->t_rkpd;
                if ($tknilairkpd0 == 0) {
                    $tknilairkpd1 = '#N/A';
                } else {
                    $tknilairkpd1 = (float)$tknilairkpd0;
                }
                $tknilairkpd[]        = $tknilairkpd1;
                $tknilairkp0 = $row_tk2->t_k_rkp;
                if ($tknilairkp0 == 0) {
                    $tknilairkp1 = '#N/A';
                } else {
                    $tknilairkp1 = (float)$tknilairkp0;
                }
                $tknilairkp[]         = $tknilairkp1;

                $sumber_tk       = $row_tk2->sumber;
                $periode_tk_id[] = $row_tk2->id_periode;
                $periode_tk = $row_tk2->periode;
                $tahun_tk21[]    = $bulan[$row_tk2->periode] . "-" . $row_tk2->tahun;
            }
            $datay_tk2 = $nilaiData_tk2;
            $tahun_tk2 = $tahun_tk2;
            $periode_tk_max = max($periode_tk_id);

            if ($periode_tk == '00') {
                $periode_tk_tahun = $tahun_tk22 . " Antar Provinsi";
                $periode_tk_kk = $tahun_tk22 . " Antar Antar Kab/Kota " . $xname;
            } else {
                $periode_tk_tahun =  $bulan1[$periode_tk] . " " . $tahun_tk22 . " Antar Provinsi";
                $periode_tk_kk =  $bulan1[$periode_tk] . " " . $tahun_tk22 . " Antar Kab/Kota " . $xname;
            }

            if ($nilaiData_tk2[3] > $nilaiData_tk2[5]) {
                $menurunmeningkatTK = 'menurun';
                if ($nilaiData_tk[5] > $nilaiData_tk2[5]) {
                    $dibawahdiatasTK = 'di bawah';
                } else {
                    $dibawahdiatasTK = 'di atas';
                    //$paragraf_12_3  ="Capaian Angka tingkat Kemiskinan ". $xname ." pada ".$tahun_tk[5]." berada diatas capaian nasional. Angka tingkat Kemiskinan nasional pada ".$tahun_tk[5]." adalah sebesar ".number_format(end($nilaiData_tk),2) ."% ";
                }
            } else {
                $menurunmeningkatTK = 'meningkat';
                if ($nilaiData_tk[5] > $nilaiData_tk2[5]) {
                    $dibawahdiatasTK = 'di bawah';
                } else {
                    $dibawahdiatasTK = 'di atas';
                }
            }

            $max_k_tk = "";
            $perbandingan_tk = "select p.label as label, p.nama_provinsi, e.* 
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
                $nilai_p_e_r1[$row_tk_per->label]         = $row_tk_per->nilai;
                $nilai_p_e_r2[$row_tk_per->nama_provinsi] = $row_tk_per->nilai;
            }
            $label_data_tk     = $label_tk;
            $nilai_data_tk_per = $nilai_tk_per;

            $nilai_data_pe_r1   = $nilai_p_e_r1;
            $nilai_data_pe_r2   = $nilai_p_e_r2;
            $ranking            = $nilai_data_pe_r1;
            arsort($ranking);
            $nr = 1;
            foreach ($ranking as $x => $x_value) {
                if ($x == $label_pe) {
                    $rengking_pro = $nr++;
                }
                $urutan_pro = $x . $x_value . $x_value . $nr++;
            }
            $tahun_tk_p_max = $categories1[5];
            $tahun_tk_p_2 = $categories1[3];
            $nilai_ppe_p_max = max($nilai_data_pe_r2);  //nila paling besar
            $nilai_ppe_p_min = min($nilai_data_pe_r2);  //nila paling rendah
            $nama_pe1 = $nilai_data_pe_r2;
            arsort($nama_pe1);
            $nama_pe2 = array_keys($nama_pe1);
            $tk_perbandingan_pro = "Perbandingan tingkat kemiskinan antar 34 provinsi menunjukkan bahwa tingkat kemiskinan " . $xname . " pada " . $tahun_tk_p_max . " berada pada urutan ke-" . $rengking_pro . ". Provinsi dengan tingkat kemiskinan tertinggi adalah " . array_shift($nama_pe2) . " (" . $nilai_ppe_p_max . "%), sedangkan provinsi dengan tingkat kemiskinan terendah adalah " . end($nama_pe2) . " (" . $nilai_ppe_p_min . "%). ";


            $th_p_kab = "select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='" . $query . "' AND e.id_indikator='36' ";
            $t_list_kab_pe = $this->db->query($th_p_kab);
            foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
                $perio = $row_t_pe_kab->perio;
            }
            $ppe_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $query . "' and (e.id_indikator='36' AND e.id_periode='$perio') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='36' AND id_periode='$perio' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_ppe_per = $this->db->query($ppe_kab);
            foreach ($list_kab_ppe_per->result() as $row_ppe_kab_per) {
                $nilai_ppe_per_kab[] = $row_ppe_kab_per->nilai;
                $posisi_ppe          = strpos($row_ppe_kab_per->label, "Kabupaten");
                if ($posisi_ppe !== FALSE) {
                    $label_ppe11 = substr($row_ppe_kab_per->label, 0, 3) . ". " . substr($row_ppe_kab_per->label, 10);
                } else {
                    $label_ppe11 = $row_ppe_kab_per->label;
                }
                $label_pek1[] = $label_ppe11;
                $label_pe1_k[$label_ppe11] = $row_ppe_kab_per->nilai;
                $tahun_p_k        = $bulan[$row_ppe_kab_per->periode] . " " . $row_ppe_kab_per->tahun;
                $nilai_ppe_kab[]                           = (float)$row_ppe_kab_per->nilai;
            }
            $label_pe_k1    = $label_pek1;
            $nilaiData_k_tk = $nilai_ppe_kab;
            // $nilaiData_k['name'] = $tahun_p_k;
            // $nilaiData_k['data'] = $nilai_ppe_kab;

            $nilai_data_ppe_per_kab = $nilai_ppe_per_kab;
            $nilai_ppe_per_kab_max = max($label_pe1_k);
            $nilai_ppe_per_kab_min = min($label_pe1_k);
            arsort($nilai_ppe_per_kab);
            $nama_k1 = $label_pe1_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_pe = $nilai_ppe_per_kab_max - $nilai_ppe_per_kab_min;

            $nrk = 1;
            foreach ($nilai_ppe_per_kab as $xk => $xk_value) {
                if ($xk == $label_pe) {
                    $rengking_pro_k = $nr++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $tahun_pe_kab = $tahun_p_k . " Antar Kabupaten/kota di " . $xname;
            $tk_perbandingan_kab = "Perbandingan tingkat kemiskinan antar kabupaten/kota di " . $xname . " memperlihatkan 
                                            bahwa pada tahun " . $tahun_p_k . " daerah dengan tingkat tingkat kemiskinan tertinggi adalah " . array_shift($nama_k2) . " (" . $nilai_ppe_per_kab_max . " %), sedangkan daerah dengan tingkat kemiskinanmi terendah adalah " . end($nama_k2) . " (" . $nilai_ppe_per_kab_min . "%).
                                            Selisih tingkat kemiskinan tertinggi dan terendah di " . $xname . " pada tahun " . $tahun_p_k . " adalah sebesar " . $selisih_pe . "%.";


            //indeks Kedalaman Kemiskinan
            $sql_idk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_idk = $this->db->query($sql_idk);
            foreach ($list_idk->result() as $row_idk) {
                //$tahun_idk[]   = $row_idk->tahun;
                $tahun_idk[]    = $bulan[$row_idk->periode] . "-" . $row_idk->tahun;
                $tahunIDK1[]    = $bulan1[$row_idk->periode] . " " . $row_idk->tahun;
                $nilai_idk = number_format((float)$row_idk->nilai, 2);
                $nilaiData_idk[] = $nilai_idk;
            }
            $datay_idk = $nilaiData_idk;
            $tahun_idk = $tahun_idk;
            $sql_idk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_idk2 = $this->db->query($sql_idk2);
            foreach ($list_idk2->result() as $row_idk2) {
                $tahun_idk2[]     = $row_idk2->tahun;
                $nilai_idk2       = number_format((float)$row_idk2->nilai, 2);
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
            $periode_ikk_tahun = $bulan1[$periodeidk] . " " . $tahunidk . " Antar Provinsi";
            if ($nilaiData_idk2[3] > $nilaiData_idk2[5]) {
                $menurunmeningkatIKK = 'menurun';
                if ($nilaiData_idk[5] > $nilaiData_idk2[5]) {
                    $dibawahdiatasIKK = 'di bawah';
                } else {
                    $dibawahdiatasIKK = 'di atas';
                }
            } else {
                $menurunmeningkatIKK = 'meningkat';
                if ($nilaiData_idk[5] > $nilaiData_idk2[5]) {
                    $dibawahdiatasIKK = 'di bawah';
                } else {
                    $dibawahdiatasIKK = 'di atas';
                }
            }


            $max_k_ikk = "";
            $perbandingan_ikk = "select p.label as label, e.* 
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
                $nilai_ikk       = number_format($row_ikk_per->nilai, 2);
                $nilai_ikk_per[] = $nilai_ikk;
            }
            $label_data_ikk     = $label_ikk;
            $nilai_data_ikk_per = $nilai_ikk_per;

            //indeks Keparahan Kemiskinan(P2)
            $sql_ikk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ikk = $this->db->query($sql_ikk);
            foreach ($list_ikk->result() as $row_ikk) {
                //$tahun_idk[]   = $row_idk->tahun;
                $tahun_ikk[]    = $bulan[$row_ikk->periode] . "-" . $row_ikk->tahun;
                $tahunIKK1[]    = $bulan1[$row_ikk->periode] . " " . $row_ikk->tahun;
                $nilai_ikk = number_format((float)$row_ikk->nilai, 2);
                $nilaiData_ikk[] = $nilai_ikk;
            }
            $datay_ikk = $nilaiData_ikk;
            $tahun_ikk = $tahun_ikk;
            $sql_ikk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ikk2 = $this->db->query($sql_ikk2);
            foreach ($list_ikk2->result() as $row_ikk2) {
                $tahun_ikk2[]     = $row_ikk2->tahun;
                $nilai_ikk2       = number_format((float)$row_ikk2->nilai, 2);
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
            $periode_ikkk_tahun = $bulan1[$periodeikk] . " " . $tahunikk . " Antar Provinsi";
            if ($nilaiData_ikk2[3] > $nilaiData_ikk2[5]) {
                $menurunmeningkatIKKK = 'menurun';
                if ($nilaiData_ikk[5] > $nilaiData_ikk2[5]) {
                    $dibawahdiatasIKKK = 'di bawah';
                } else {
                    $dibawahdiatasIKKK = 'di atas';
                }
            } else {
                $menurunmeningkatIKKK = 'meningkat';
                if ($nilaiData_ikk[5] > $nilaiData_ikk2[5]) {
                    $dibawahdiatasIKKK = 'di bawah';
                } else {
                    $dibawahdiatasIKKK = 'di atas';
                }
            }


            $max_k_ikkk = "";
            $perbandingan_ikkk = "select p.label as label, e.* 
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
                $nilai_ikkk       = number_format($row_ikkk_per->nilai, 2);
                $nilai_ikkk_per[] = $nilai_ikkk;
            }
            $label_data_ikkk     = $label_ikkk;
            $nilai_data_ikkk_per = $nilai_ikkk_per;



            //jumlah Penduduk Miskin
            $sql_jpk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jpk = $this->db->query($sql_jpk);
            foreach ($list_jpk->result() as $row_jpk) {
                $tahun_jpk[]    = $bulan1[$row_jpk->periode] . " " . $row_jpk->tahun;
                $nilaiData_jpk[] = (float)$row_jpk->nilai;
                $nilaiData_jpk1[] = (float)$row_jpk->nilai;
                $periode_jpk1[] = (float)$row_jpk->id_periode;
            }
            $datay_jpk = $nilaiData_jpk;
            $tahun_jpk = $tahun_jpk;

            $periode_jpk_max    = max($periode_jpk1);
            $data1_jpk          = substr($periode_jpk_max, 0, 4);
            $data2_jpk          = substr($periode_jpk_max, -2);
            $periode_jpk_tahun  =  $bulan1[$data2_jpk] . " " . $data1_jpk . " Antar Provinsi";


            $sql_jpk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jpk2 = $this->db->query($sql_jpk2);
            foreach ($list_jpk2->result() as $row_jpk2) {
                $tahun_jpk2[]      = $row_jpk2->tahun;
                $nilaiData_jpk2[]  = (float)$row_jpk2->nilai;
                $nilaiData_jpk22[] = (float)$row_jpk2->nilai;
                $sumber_jpk        = $row_jpk2->sumber;
                $periode_jpk_id[]  = $row_jpk2->id_periode;
                $tahun_jpk21[]     = $bulan[$row_jpk2->periode] . "-" . $row_jpk2->tahun;
            }
            $datay_jpk2 = $nilaiData_jpk2;
            $tahun_jpk2 = $tahun_jpk2;
            $tahun_jpk21 = $tahun_jpk21;

            $periode_jpk_max = max($periode_jpk_id);


            if ($nilaiData_jpk22[3] > $nilaiData_jpk22[5]) {
                $rt_jpk = $nilaiData_jpk22[5] - $nilaiData_jpk22[3];
                $rt_jpkk = abs($nilaiData_jpk22[5] - $nilaiData_jpk22[3]);
                $rt_jpk2 = $rt_jpk / $nilaiData_jpk22[3];
                $rt_jpk3 = abs($rt_jpk2 * 100);
                $rt_jpk33 = number_format($rt_jpk3, 2);
                $berkurangbertambah = 'berkurang';
            } else {
                $rt_jpk  = $nilaiData_jpk22[5] - $nilaiData_jpk22[3];
                $rt_jpkk = abs($nilaiData_jpk22[5] - $nilaiData_jpk22[3]);
                $rt_jpk2 = $rt_jpk / $nilaiData_jpk22[3];
                $rt_jpk3 = $rt_jpk2 * 100;
                $rt_jpk33 = number_format($rt_jpk3, 2);
                $berkurangbertambah = 'bertambah';
            }

            $perbandingan_jpk = "select p.label as label, p.nama_provinsi, e.* from provinsi p join nilai_indikator e on p.id = e.wilayah where (e.id_indikator='40' AND e.id_periode='$periode_jpk_max') AND (wilayah, versi) in (select x.wilayah, max(x.versi) as versi from nilai_indikator x where id_indikator='40' AND id_periode='$periode_jpk_max' group by wilayah) group by wilayah order by wilayah asc";
            $list_jpk_per = $this->db->query($perbandingan_jpk);
            foreach ($list_jpk_per->result() as $row_jpk_per) {
                $label_jpk[]     = $row_jpk_per->label;
                $nilai_jpk_per[] = $row_jpk_per->nilai;
                $nilai_jpk_r1[$row_jpk_per->label]         = $row_jpk_per->nilai;
                $nilai_jpk_r2[$row_jpk_per->nama_provinsi] = $row_jpk_per->nilai;
            }
            $label_data_jpk     = $label_jpk;
            $nilai_data_jpk_per = $nilai_jpk_per;

            $nilai_data_jpk_r1   = $nilai_jpk_r1;
            $nilai_data_jpk_r2   = $nilai_jpk_r2;
            $ranking             = $nilai_data_jpk_r1;
            arsort($ranking);
            $nr = 1;
            foreach ($ranking as $x => $x_value) {
                if ($x == $label_pe) {
                    $rengking_pro = $nr++;
                }
                $urutan_pro = $x . $x_value . $x_value . $nr++;
            }
            $nilai_jpk_p_max = max($nilai_data_jpk_r2);  //nila paling besar
            $nilai_jpk_p_min = min($nilai_data_jpk_r2);  //nila paling rendah
            $nama_pe1 = $nilai_data_jpk_r2;
            arsort($nama_pe1);
            $nama_pe2 = array_keys($nama_pe1);
            $jp_perbandingan_pro = "Perbandingan jumlah penduduk miskin antar 34 provinsi menunjukkan bahwa jumlah penduduk miskin " . $xname . " pada " . max($categories_pro) . " berada pada urutan ke-" . $rengking_pro . ". Provinsi dengan jumlah penduduk miskin tertinggi adalah " . array_shift($nama_pe2) . " (" . number_format($nilai_jpk_p_max, 0, ",", ".") . " orang), sedangkan provinsi dengan jumlah penduduk miskin terendah adalah " . end($nama_pe2) . " (" . number_format($nilai_jpk_p_min, 0, ",", ".") . " orang). ";

            $th_p_kab = "select max(e.id_periode) AS perio from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='" . $query . "' AND e.id_indikator='40' ";
            $t_list_kab_pe = $this->db->query($th_p_kab);
            foreach ($t_list_kab_pe->result() as $row_t_pe_kab) {
                $perio = $row_t_pe_kab->perio;
            }
            $ppe_kab = "select p.nama_kabupaten as label, e.* from kabupaten p join nilai_indikator e on p.id = e.wilayah where p.prov_id='" . $query . "' and (e.id_indikator='40' AND e.id_periode='$perio') AND (wilayah, versi) in ( select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='40' AND id_periode='$perio' group by wilayah ) group by wilayah order by wilayah asc";
            $list_kab_ppe_per = $this->db->query($ppe_kab);
            foreach ($list_kab_ppe_per->result() as $row_ppe_kab_per) {
                $nilai_ppe_per_kab[]        = $row_ppe_kab_per->nilai;
                $posisi_ppe                 = strpos($row_ppe_kab_per->label, "Kabupaten");
                if ($posisi_ppe !== FALSE) {
                    $label_ppe11 = substr($row_ppe_kab_per->label, 0, 3) . ". " . substr($row_ppe_kab_per->label, 10);
                } else {
                    $label_ppe11 = $row_ppe_kab_per->label;
                }
                $label_pek1[]               = $label_ppe11;
                $label_pe1_k[$label_ppe11]  = $row_ppe_kab_per->nilai;
                $tahun_p_k                  = $bln[$row_ppe_kab_per->periode] . " " . $row_ppe_kab_per->tahun;
                $nilai_ppe_kab[]            = (float)$row_ppe_kab_per->nilai;
            }
            $label_pe_k1          = $label_pek1;
            $label_jp_k1          = $label_pek1;
            $nilaiData_jp = $nilai_ppe_kab;

            $nilaiData_k['name'] = $tahun_p_k;
            $nilaiData_k['data'] = $nilai_ppe_kab;
            $nilai_data_ppe_per_kab = $nilai_ppe_per_kab;
            $nilai_ppe_per_kab_max = max($label_pe1_k);
            $nilai_ppe_per_kab_min = min($label_pe1_k);
            arsort($nilai_ppe_per_kab);
            $nama_k1 = $label_pe1_k;
            arsort($nama_k1);
            $nama_k2 = array_keys($nama_k1);
            $selisih_pe = $nilai_ppe_per_kab_max - $nilai_ppe_per_kab_min;

            $nrk = 1;
            foreach ($nilai_ppe_per_kab as $xk => $xk_value) {
                if ($xk == $label_pe) {
                    $rengking_pro_k = $nr++;
                }
                $urutan_pro_k = $xk . $xk_value . $xk_value . $nrk++;
            }
            $tahun_pe_kab = $tahun_p_k . " Antar Kabupaten/kota di " . $judul;
            $jp_perbandingan_kab = "Perbandingan jumlah penduduk miskin antar kabupaten/kota di " . $xname . " memperlihatkan bahwa pada " . $tahun_p_k . " daerah dengan jumlah penduduk miskin tertinggi adalah " . array_shift($nama_k2) . " (" . number_format($nilai_ppe_per_kab_max, 0, ",", ".") . " orang), sedangkan daerah dengan jumlah penduduk miskin terendah adalah " . end($nama_k2) . " (" . number_format($nilai_ppe_per_kab_min, 0, ",", ".") . " orang). Selisih jumlah penduduk miskin tertinggi dan terendah di " . $xname . " pada " . $tahun_p_k . " adalah sebesar " . number_format($selisih_pe, 0, ",", ".") . " orang.";

            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            //$lan     = new \PhpOffice\PhpWord\Language\Language();

            $cover = base_url("assets/images/laporan/cover/" . $pro . ".jpg");
            $src = base_url("assets/images/laporan/cover_master_v3.jpg");
            $logo = base_url("assets/images/logopropinsi/" . $pro . ".png");
            //$hal1 =base_url("assets/images/laporan/hal1.jpg");

            //        $hal2 =base_url("assets/images/laporan/hal2.jpg");

            //        $hal8 =base_url("assets/images/laporan/hal8.jpg");
            //        $hal9  =base_url("assets/images/laporan/hal9.jpg");
            //        $hal10 =base_url("assets/images/laporan/hal10.jpg");
            //        $hal18 =base_url("assets/images/laporan/hal18.jpg");


            $section = $phpWord->addSection(array(
                'headerHeight' => 50,
                'footerHeight' => 3000
            ));
            $header = $section->addHeader();
            // $header->addWatermark(
            //     //$cover,
            //     array(
            //         'headerHeight' => 300, 'marginTop' => -3, 'marginLeft' => -73,
            //         //'footerHeight' => -50,
            //         'width' => 595,
            //         //'height'=> 30,
            //         'posHorizontal' => 'absolute', 'posVertical' => 'absolute',
            //     )
            // );

            // Adding Text element with font customized using explicitly created font style object...
            $fontStyle = new \PhpOffice\PhpWord\Style\Font();
            $fontStyle->setBold(true);
            $fontStyle->setName('Verdana');
            $fontStyle->setSize(24);
            $fontStyle->setColor('#bfbfbf');

            $section->addTextBreak(18);
            $section->addImage(
                $logo,
                array(
                    'width' => 119,
                    'height' => 120,
                    'marginLeft' => 45,
                )
            );
            $section->addTextBreak(2);
            $myTextElement = $section->addText("Evaluasi Kinerja Pencapaian", $fontStyle, array('alignment' => 'right'));
            $myTextElement = $section->addText("Makro Pembangunan", $fontStyle, array('alignment' => 'right'));
            $myTextElement = $section->addText($xname);
            $myTextElement->setFontStyle($fontStyle, array('alignment' => 'right'));

            // Halaman Baru
            $section = $phpWord->addSection();
            $header = $section->addHeader();
            $header->addWatermark(
                base_url("assets/images/header.png"),
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
            $phpWord->addFontStyle($fontStyleName, array('name' => 'Arial Narrow', 'size' => 14, 'color' => '1B2232', 'bold' => true));
            $style0 = array(
                'width'          => Converter::cmToEmu(13), 'height'         => Converter::cmToEmu(6),
                'showAxisLabels' => true, 'showGridX'      => false, 'showGridY'      => false,
                'showLegend'     => true, 'valueLabelPosition'     => FALSE,
            );
            $style = array(
                'width'          => Converter::cmToEmu(16), 'height'         => Converter::cmToEmu(6),
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
            $style_sp = array(
                'width'          => Converter::cmToEmu(16), 'height'         => Converter::cmToEmu(10),
                'showAxisLabels' => true, 'showGridX'      => false, 'showGridY'      => false,
                'showLegend'     => true, 'valueLabelPosition'     => FALSE,
            );
            $style_radar = array(
                'width'          => Converter::cmToEmu(16),
                'height'         => Converter::cmToEmu(10),
                'showAxisLabels' => true, 'showGridX'      => false, 'showGridY'      => false,
                'showLegend'     => true, 'valueLabelPosition'     => FALSE,
            );

            $section = $phpWord->addSection(array('colsNum' => 1, 'breakType' => 'continuous'));
            $chartTypes = array('line');
            $chartTypes2 = array('column');
            $twoSeries = array('bar', 'column', 'line', 'area', 'scatter', 'radar', 'stacked_bar', 'percent_stacked_bar', 'stacked_column', 'percent_stacked_column');
            $threeSeries = array('bar', 'line');

            //            $fontparagraf = array('name' => 'Book Antiqua (Body)', 'size' => 10, 'spaceAfter'=>80);
            $fontparagraf       = array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10);
            $fontparagraf1 = array('alignment' => 'both');
            $fontmiring   = array('name' => 'Book Antiqua (Body)', 'size' => 10, 'spaceAfter' => 80, 'italic' => true, 'size' => 10);

            $fontgambar = array('name' => 'Book Antiqua (Body)', 'size' => 9, 'bold' => true);
            $fontgambar1 = array('alignment' => 'center');
            //Kata Pengantar
            $section->addText('Kata Pengantar',  array('name' => 'Arial', 'spaceAfter' => 80, 'size' => 16, 'bold' => true), array('alignment' => 'center'));

            $phpWord->addParagraphStyle('pStyler', array('alignment' => 'both'));
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
            $textrun3->addText(htmlspecialchars("Jakarta,      Desember 2022"), $fontparagraf);
            $textrun4 = $section->addTextRun('pStyler2');
            $textrun4->addText(htmlspecialchars("Direktur Pemantauan, Evaluasi dan"), $fontparagraf);
            $textrun5 = $section->addTextRun('pStyler2');
            $textrun5->addText(htmlspecialchars("Pengendalian Pembangunan Dareah"), $fontparagraf);
            $section->addTextBreak(2);
            $textrun6 = $section->addTextRun('pStyler2');
            $textrun6->addText(htmlspecialchars("Agustin Arry Yanna"), $fontparagraf);

            //          daftar isi
            $section = $phpWord->addSection();
            $section->addText('DAFTAR ISI',  array('name' => 'Century Gothic (Headings)', 'spaceAfter' => 80, 'size' => 20, 'bold' => true), array('alignment' => 'center'));
            $section->addText(' KATA PENGANTAR...........................................................................................................................................2',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText(' DAFTAR ISI...........................................................................................................................................................3',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText(' DAFTAR GAMBAR.............................................................................................................................................4',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Pertumbuhan Ekonomi.....................................................................................................................................5',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Perkembangan PDRB per kapita ADHB........................................................................................................6',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Perkembangan PDRB per kapita ADHK tahun dasar 2010........................................................................7',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Perkembangan Jumlah Penganggur...............................................................................................................8',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Tingkat Pengangguran Terbuka......................................................................................................................9',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Indeks Pembangunan Manusia.....................................................................................................................10',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Rasio Gini..........................................................................................................................................................11',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Angka Harapan Hidup...................................................................................................................................12',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Rata-rata Lama Sekolah..................................................................................................................................13',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Harapan Lama Sekolah................................................................................................................................14',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Pengeluaran per Kapita................................................................................................................................15',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Tingkat Kemiskinan......................................................................................................................................16',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Indeks Kedalaman Kemiskinan (P1) .........................................................................................................17',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Indeks Keparahan Kemiskinan (P2) ..........................................................................................................18',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('' . $daftarisi++ . '. Jumlah Penduduk Miskin............................................................................................................................19',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            //daftar gambar 
            $section = $phpWord->addSection();
            $section->addText('DAFTAR GAMBAR',  array('name' => 'Century Gothic (Headings)', 'spaceAfter' => 80, 'size' => 20, 'bold' => true), array('alignment' => 'center'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Pertumbuhan Ekonomi.........................................................................................4',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Pertumbuhan Ekonomi Antar Provinsi................................................................4',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan PDRB per Kapita ADHB........................................................................................5',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Perkembangan PDRB per kapita ADHB Antar provinsi...................................5',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan PDRB per kapita ADHK Tahun Dasar 2010.......................................................6',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan PDRB per kapita ADHK Tahun Dasar 2010 Antar provinsi..............................6',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Jumlah Penganggur................................................................................................7',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Jumlah Penganggur Antar Provinsi......................................................................7',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Tingkat Pengangguran Terbuka..........................................................................8',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Tingkat Pengangguran Terbuka Antar Provinsi...............................................8',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Indeks Pembangunan Indonesia........................................................................9',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Indeks Pembangunan Indonesia Antar Provinsi...............................................9',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Rasio Gini.............................................................................................................10',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Rasio Gini Antar Provinsi...................................................................................10',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Angka Harapan Hidup......................................................................................11',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Angka Harapan Hidup Antar Provinsi............................................................11',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Rata-rata Lama Sekolah.....................................................................................12',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Rata-rata Lama Sekolah Antar Provinsi...........................................................12',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Harapan Lama Sekolah.....................................................................................13',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Harapan Lama Sekolah Antar Provinsi............................................................13',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Pengeluaran per Kapita.....................................................................................14',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Pengeluaran per Kapita Antar Provinsi............................................................14',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Tingkat Kemiskinan...........................................................................................15',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Tingkat Kemiskinan Antar Provinsi..................................................................15',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Indeks Kedalaman Kemiskinan (P1) ..............................................................16',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Indeks Kedalaman Kemiskinan (P1) Antar Provinsi......................................16',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Indeks Keparahan Kemiskinan (P2) ...........…................................................17',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Indeks Keparahan Kemiskinan (P2) Antar Provinsi.......................................17',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perkembangan Jumlah Penduduk Miskin.................................................................................18',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));
            $section->addText('Gambar ' . $daftargamabar++ . '. Perbandingan Jumlah Penduduk Miskin Antar Provinsi........................................................18',  array('name' => 'Book Antiqua (Body)', 'size' => 10), array('alignment' => 'both'));

            //halaman Baru
            $section = $phpWord->addSection();
            $section->addText('1. Pertumbuhan Ekonomi', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));

            $section->addImage(
                $hal1,
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
                )
            );

            $paragraf_1_2  = "Pertumbuhan ekonomi " . $xname . " pada tahun " . $tahun1[5] . " " . $meningkatmenurun . " dibandingkan dengan tahun " . $tahun1[4] . ". Pada tahun " . $tahun1[5] . " pertumbuhan ekonomi " . $xname . " adalah sebesar " . end($nilaimax_pro) . "%, sedangkan pada tahun " . $tahun1[4] . " pertumbuhannya tercatat sebesar " . $nilaimax_pro[4] . "%. ";
            $paragraf_1_3  = "Capaian pertumbuhan ekonomi " . $xname . " pada tahun " . $tahun1[5] . " " . $dibawahdiatas . " "
                . "Nasional. Pertumbuhan ekonomi Nasional pada tahun " . $tahun1[5] . " adalah sebesar " . end($nilaimax) . "%. ";

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
            $textbox->addText($deskripsi1,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_1_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_1_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            //$section->addText($paragraf_1_2,array('name' => 'Arial', 'size' => 10),array('alignment'=>'both'));
            //$section->addText($paragraf_1_3,array('name' => 'Arial', 'size' => 10),array('alignment'=>'both'));


            // $section->addTextBreak(1);
            $categories = $thn_ex;
            $series1 = $nilaiData1;
            $series2 = $nilaiData1_pro;
            $series3 = $nilaitarget;
            $series4 = $nilairpjmn;
            $series5 = $nilairkpd;
            $series6 = $nilairkp;
            $showGridLines = true;
            $showAxisLabels = true;

            foreach ($chartTypes as $chartType) {
                //$section->addText('Gambar '.$gambar++.' Perkembangan Pertumbuhan Ekonomi (%)',array('name' => 'Century Gothic (Headings)', 'size' => 9),array('alignment'=>'center'));
                $chart = $section->addChart($chartType, $categories, $series1, $style, 'Nasional');
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories, $series2, $xname);
                    $chart->addSeries($categories, $series4, 'Target Makro RPJMN');
                    $chart->addSeries($categories, $series5, 'Target RKPD');
                    $chart->addSeries($categories, $series6, 'Target Kewilayahan RKP');
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Pertumbuhan Ekonomi (%)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }

            $section->addText($pe_perbandingan_pro,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));

            $categories_1_2 = $label_data_ppe;
            $series_1_2     = $nilai_data_ppe_per;
            $chart = $section->addChart('column', $categories_1_2, $series_1_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Perkembangan Pertumbuhan Ekonomi Tahun " . $tahun_pe_max . " (%)", $fontgambar, $fontgambar1);

            //$section = $phpWord->addSection();
            $section->addText($perbandingan_pe_2th,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));
            $chartTypes = array('radar');
            $twoSeries = array('radar');
            $threeSeries = array('bar', 'line');
            $categories_pe_r = $label_tk2;
            $series_pe_r1 = $nilai_pe_r22;
            $series_pe_r2 = $nilai_radar;

            foreach ($chartTypes as $chartType) {
                //$section->addTitle(ucfirst($chartType), 2);
                $chart = $section->addChart($chartType, $categories_pe_r, $series_pe_r1, $style_radar, 'Satu');
                //$chart->getStyle()->setWidth(Converter::inchToEmu(2.5))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_pe_r, $series_pe_r2, 'Satu');
                }
                // if (in_array($chartType, $threeSeries)) {
                //     $chart->addSeries($categories_pe_r, $series3);
                // }
                $section->addTextBreak();
            }
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Pertumbuhan Ekonomi Tahun " . $tahun_pe_max . " dengan Antar Provinsi (%)", $fontgambar, $fontgambar1);
            //$section = $phpWord->addSection();
            $section->addText($pe_perbandingan_kabb,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));
            $categories_1_3 = $label_pek11;
            $series_1_3     = $nilai_ppe_kab11;
            $chart = $section->addChart('column', $categories_1_3, $series_1_3, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Perkembangan Pertumbuhan Ekonomi Tahun " . $tahun_pe_max_kk . " (%) ", $fontgambar, $fontgambar1);
            //$chart->getStyle()->setColors($warna_1_2);
            $section->addText('Sumber Data: Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('2. Struktur dan Pertumbuhan PDRB Sektoral', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));

            $select_pdrb = "SELECT R.id,R.nama_indikator,IFNULL(IND.nilai,0) nilai_struktur,IFNULL(IND.target,0) nilai_pertumbuhan, IND.id_periode, IND.periode,IND.tahun
                             FROM (SELECT IK.id,IK.nama_indikator 
                                    FROM `indikator` IK 
                                    WHERE IK.group_id='6' )R 
                            LEFT JOIN ( select id_indikator, id_periode,nilai,target,periode,tahun
                                        from nilai_indikator 
                                        where (wilayah='" . $id_pro . "') 
                                             AND (id_indikator, id_periode, versi) IN( 
                                                    select id_indikator,id_periode, max(versi) as versi 
                                                    from nilai_indikator 
                                                    WHERE wilayah='" . $id_pro . "' group by id_indikator ) 
                                       )IND ON R.id=IND.id_indikator ";
            $list_pdrb  = $this->db->query($select_pdrb);
            foreach ($list_pdrb->result() as $row_pdrb) {
                $categories_p[]             = $row_pdrb->nama_indikator;
                $nilai_s[]                = (float)$row_pdrb->nilai_struktur;
                $nilai_p[]                = (float)$row_pdrb->nilai_pertumbuhan;
                $nilai_pc[]                = (float)$row_pdrb->nilai_pertumbuhan;
                $periode                  = $row_pdrb->periode;
                $tahun1                   = $row_pdrb->tahun;
                if ($periode == '00') {
                    $thn = $row_pdrb->tahun;
                } else {
                    $thn =  $prde[$row_pdrb->periode] . " - " . $row_pdrb->tahun;
                }
                $periode1[]   = $thn;
                $periode_c   = $thn;

                $label_sektor[$row_pdrb->nama_indikator] = (float)$row_pdrb->nilai_struktur;
                $label_pertumbuhan[$row_pdrb->nama_indikator] = (float)$row_pdrb->nilai_pertumbuhan;
            }
            $tahun2 = $tahun1 - 1;
            $tahun_p = $tahun2 . $periode;

            $tahun             = $categories_p;
            $nilaiData['name'] = $periode_c;
            $nilaiData['data'] = $nilai_s;
            $catdata[]            = $nilaiData;

            $nilaiData2['name'] = $periode_c;
            $nilaiData2['data'] = $nilai_p;
            $catdata2[]            = $nilaiData2;

            arsort($nilai_s);
            $nrk = 1;
            foreach ($nilai_s as $xk => $xk_value) {
                $nok = $nrk++;
                if ($nok == '1') {
                    $rengking_satu = $xk_value;
                }
                if ($nok == '2') {
                    $rengking_dua = $xk_value;
                }
                if ($nok == '3') {
                    $rengking_tiga = $xk_value;
                }
            }
            $nama_s1 = $label_sektor;
            arsort($nama_s1);
            $nama_s2 = array_keys($nama_s1);
            foreach ($nilai_s as $xk => $xk_value) {
                $nok = $nrk++;
                if ($nok == '1') {
                    $rengking_satu = $xk_value;
                }
                if ($nok == '2') {
                    $rengking_dua = $xk_value;
                }
                if ($nok == '3') {
                    $rengking_tiga = $xk_value;
                }
            }
            arsort($nilai_p);
            $nrp = 1;
            foreach ($nilai_p as $xkp => $xkp_value) {
                $nop = $nrp++;
                if ($nop == '1') {
                    $rengking_satu_p = $xkp_value;
                }
                if ($nop == '2') {
                    $rengking_dua_p = $xkp_value;
                }
                if ($nop == '3') {
                    $rengking_tiga_p = $xkp_value;
                }
            }
            $nama_p1 = $label_pertumbuhan;
            arsort($nama_p1);
            $nama_p2 = array_keys($nama_p1);
            $nama_p3 = $label_pertumbuhan;
            asort($nama_p3);
            $nrps = 1;
            foreach ($nama_p3 as $xkps => $xkps_value) {
                $nops = $nrps++;
                if ($nops == '1') {
                    $rengking_satu_ts = $xkps_value;
                }
                if ($nops == '2') {
                    $rengking_dua_ts = $xkps_value;
                }
                if ($nops == '3') {
                    $rengking_tiga_ts = $xkps_value;
                }
            }
            $nama_p4 = array_keys($nama_p3);
            $peek_pdrb = "Tiga sektor yang paling dominan dalam struktur perekonomian " . $xname . " adalah sektor "
                . "" . $nama_s2[0] . " dengan kontribusi sebesar " . $rengking_satu . " persen, diikuti oleh sektor " . $nama_s2[1] . " dengan kontribusi sebesar " . $rengking_dua . " persen, kemudian sektor " . $nama_s2[2] . " dengan kontribusi sebesar " . $rengking_tiga . " persen. "
                . "Sedangkan tiga sektor yang memiliki pertumbuhan paling tinggi adalah sektor " . $nama_p2[0] . " "
                . "dengan pertumbuhan sebesar " . $rengking_satu_p . " persen, "
                . "diikuti oleh sektor " . $nama_p2[1] . " dengan pertumbuhan sebesar " . $rengking_dua_p . " persen, "
                . "serta sektor " . $nama_p2[2] . " dengan pertumbuhan sebesar " . $rengking_tiga_p . " persen. "
                . "Di sisi lain tiga sektor yang mengalami pertumbuhan terendah adalah "
                . "sektor " . $nama_p4[0] . " dengan pertumbuhan sebesar " . $rengking_satu_ts . " persen, "
                . "diikuti oleh sektor " . $nama_p4[1] . " dengan pertumbuhan sebesar " . $rengking_dua_ts . " persen, "
                . "serta sektor " . $nama_p4[2] . " dengan pertumbuhan sebesar " . $rengking_tiga_ts . " persen.	";

            $section->addText($peek_pdrb,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));

            $categories_12_1 = $categories_p;
            $series_12_n     = $nilai_s;
            $series_13_n     = $nilai_p;

            // 3D charts


            //$chartTypes = array('pie', 'bar', 'column', 'line', 'area');
            $chartTypes_sp = array('bar');
            $multiSeries_sp = array('column');
            //$style = array('width' => Converter::cmToEmu(8), 'height' => Converter::cmToEmu(8), '3d' => false);
            foreach ($chartTypes_sp as $chartType) {
                //$section->addTitle(ucfirst($chartType), 2);
                $chart = $section->addChart($chartType, $categories_12_1, $series_12_n, $style_sp, "Struktur PDRB");
                if (in_array($chartType, $multiSeries_sp)) {
                    //$chart->addSeries($categories, $series_12_1, "Pertanian, kehutanan, dan perikanan");
                    //$chart->addSeries($categories, $series_12_2, "RKKK");
                }
                $section->addTextBreak();
            }

            foreach ($chartTypes_sp as $chartType) {
                //$section->addTitle(ucfirst($chartType), 2);
                $chart = $section->addChart($chartType, $categories_12_1, $series_13_n, $style_sp, "Laju Pertumbuhan");
                if (in_array($chartType, $multiSeries_sp)) {
                    //$chart->addSeries($categories, $series_12_1, "Pertanian, kehutanan, dan perikanan");
                    //$chart->addSeries($categories, $series_12_2, "RKKK");
                }
                $section->addTextBreak();
            }



            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('3. Tingkat Kemiskinan', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal16,
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
                )
            );
            $paragraf_12_2  = "Tingkat kemiskinan " . $xname . " pada " . $tahun_tkk[5] . " " . $menurunmeningkatTK . " dibandingkan dengan " . $tahun_tkk[3] . ". Pada " . $tahun_tkk[5] . " Angka tingkat kemiskinan " . $xname . " adalah sebesar " . number_format(end($nilaiData_tk2), 2) . "%, sedangkan pada " . $tahun_tkk[3] . " Angka tingkat Kemiskinan tercatat sebesar " . number_format($nilaiData_tk2[3], 2) . "%. ";
            $paragraf_12_3  = "Capaian angka tingkat Kemiskinan " . $xname . " pada " . $tahun_tkk[5] . " berada " . $dibawahdiatasTK . " capaian Nasional. Angka tingkat kemiskinan Nasional pada " . $tahun_tkk[5] . " adalah sebesar " . number_format(end($nilaiData_tk), 2) . "%.";

            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 125,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi12,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_12_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_12_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            $categories_12_1 = $tahun_tk;
            $series_12_n     = $datay_tk;
            $series_12_1     = $datay_tk2;
            $series_12_2     = $tknilairpjmn;
            $series_12_3     = $tknilairkpd;
            $series_12_4     = $tknilairkp;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_12_1, $series_12_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_12_1, $series_12_1, $xname);
                    $chart->addSeries($categories_12_1, $series_12_2, 'Target Makro RPJMN');
                    $chart->addSeries($categories_12_1, $series_12_3, 'Target RKPD');
                    $chart->addSeries($categories_12_1, $series_12_4, 'Target Kewilayahan RKP');
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Tingkat Kemiskinan (%)', $fontgambar, $fontgambar1);
                //$section->addTextBreak();
            }

            $section->addText($tk_perbandingan_pro,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));

            $categories_12_2 = $label_data_tk;
            $series_12_2     = $nilai_data_tk_per;
            $chart_12_2      = $section->addChart('column', $categories_12_2, $series_12_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Tingkat Kemiskinan " . $periode_tk_tahun . " (%)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText($tk_perbandingan_kab,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));
            $chart_12_2      = $section->addChart('column', $label_pek1, $nilai_ppe_kab, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Tingkat Kemiskinan " . $periode_tk_kk . " (%)", $fontgambar, $fontgambar1);

            $section->addText('Sumber Data: Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('4. Jumlah Penduduk Miskin', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal19,
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
                )
            );
            $paragraf_14_2    = "Jumlah penduduk miskin " . $xname . " pada " . $tahun_jpk[5] . " sebanyak " . number_format($nilaiData_jpk22[5], 0) . " orang sedangkan jumlah penduduk miskin pada " . $tahun_jpk[3] . " sebanyak " . number_format($nilaiData_jpk22[3], 0) . " orang. "
                . "Selama periode " . $tahun_jpk[3] . " - " . $tahun_jpk[5] . " jumlah penduduk miskin di " . $xname . " " . $berkurangbertambah . " sebanyak " . number_format($rt_jpkk, 0) . " orang atau sebesar " . $rt_jpk33 . "%. "
                . "Jumlah Penduduk Miskin nasional pada " . $tahun_jpk[5] . " sebesar " . number_format($nilaiData_jpk[5], 0) . " orang.";

            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 55,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi14,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_14_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));


            $categories_14_1 = $tahun_jpk21;
            $series_14_1     = $datay_jpk2;
            $chart = $section->addChart('column', $categories_14_1, $series_14_1, $style_2, $xname);
            $section->addText('Gambar ' . $gambar++ . '. Perkembangan Jumlah Penduduk Miskin (Orang)', $fontgambar, $fontgambar1);
            //$section->addTextBreak();

            $section->addText($jp_perbandingan_pro,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));

            $categories_14_2 = $label_data_jpk;
            $series_14_2     = $nilai_data_jpk_per;
            $chart_14_2      = $section->addChart('column', $categories_14_2, $series_14_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Jumlah Penduduk Miskin " . $periode_jpk_tahun . " (Orang)", $fontgambar, $fontgambar1);
            //            $section->addTextBreak();
            $section->addText($jp_perbandingan_kab,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));
            $chart_14_2      = $section->addChart('column', $label_jp_k1, $nilaiData_jp, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Jumlah Penduduk Miskin " . $tahun_pe_kab . " (Orang)", $fontgambar, $fontgambar1);

            $section->addText('Sumber Data: Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('5. Tingkat Pengangguran Terbuka', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal9,
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
                )
            );
            $paragraf_5_2  = "Tingkat pengangguran terbuka " . $xname . " pada " . $tahunTPT2[5] . " " . $menurunmeningkatTPT . " dibandingkan dengan " . $tahunTPT2[3] . ". Pada " . $tahunTPT2[5] . " Tingkat pengangguran terbuka " . $xname . " adalah sebesar " . number_format(end($nilaiData_tpt2), 2) . "% sedangkan pada " . $tahunTPT2[3] . " tingkat pengangguran terbuka tercatat sebesar " . number_format($nilaiData_tpt2[3], 2) . "%. ";
            $paragraf_5_3  = "Capaian tingkat pengangguran terbuka " . $xname . " pada " . $tahunTPT2[5] . " berada " . $dibawahdiatasTPT . " capaian Nasional. Tingkat pengangguran terbuka Nasional pada " . $tahunTPT2[5] . " adalah sebesar " . number_format(end($nilaiData_tpt), 2) . "%. ";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 310,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi5,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_5_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_5_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            $categories_5_1 = $tahun_tpt;
            $series_5_n     = $datay_tpt;
            $series_5_1     = $datay_tpt2;
            $series_5_2    = $nilaiTarget_tpt;
            $series_5_3 =   $nilaiRpjmn_tpt;
            $series_5_4 =   $nilaiRKPD_tpt;
            $series_5_5 =   $nilaiRKP_tpt;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_5_1, $series_5_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_5_1, $series_5_1, $xname);
                    $chart->addSeries($categories_5_1, $series_5_2, 'Target');
                    $chart->addSeries($categories_5_1, $series_5_3, 'Target Makro RPJMN');
                    $chart->addSeries($categories_5_1, $series_5_4, 'Target RPKPD');
                    $chart->addSeries($categories_5_1, $series_5_5, 'Target Kewilayahan RKP');
                }
                $section->addText('Gambar ' . $gambar++ . '. Tingkat Pengangguran Terbuka (%)', $fontgambar, $fontgambar1);
                // $section->addTextBreak();
            }

            $section->addText($tpt_perbandingan_pro,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));
            $categories_5_2 = $label_data_tpt;
            $series_5_2     = $nilai_data_tpt_per;
            $chart_5_2      = $section->addChart('column', $categories_5_2, $series_5_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Tingkat Pengangguran Terbuka " . $periode_tpt_tahun . " (%)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $section->addText($tpt_perbandingan_kab,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));
            $label_tpt_k1            = $label_pek1;
            $nilai_data_tpt_per_kab   = $nilai_data_tpt_per_kab;
            $chart_5_2      = $section->addChart('column', $label_tpt_k1, $nilai_data_tpt_per_kab, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Tingkat Pengangguran Terbuka " . $periode_tpt_kk . " (%)", $fontgambar, $fontgambar1);

            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('6. Perkembangan Jumlah Penganggur', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal8,
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
                )
            );
            $paragraf_4_2  = "Jumlah penganggur di " . $xname . " pada " . $tahunJP[5] . " sebanyak " . number_format($nilai_capaian2[5], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahunJP[3] . " sebanyak " . number_format($nilai_capaian2[3], 0) . " orang. Selama periode " . $tahunJP[3] . " sampai " . $tahunJP[5] . " jumlah penganggur di " . $xname . " " . $berkurangmeningkat . " " . number_format($rt_jpp) . " orang atau sebesar " . $rt_jp33 . "%."
                . " Jumlah pengangur nasional pada " . $tahunJP[5] . " sebesar " . number_format($nilai_capaian[5], 0) . " orang.";
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi4,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            $textbox = $section->addTextBox(
                array(
                    'align'       => 'left',
                    'width'       => 323,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#004D8B',
                )
            );
            $textbox->addText($paragraf_4_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            $section->addTextBreak(1);
            $categories_4_1 = $tahun_jp;
            $series_4_1     = $datay_jp2;

            $chart_4_1      = $section->addChart('column', $categories_4_1, $series_4_1, $style_2, $xname);
            $section->addText("Gambar " . $gambar++ . ". Perkembangan Jumlah Penganggur (Ribu Orang)", $fontgambar, $fontgambar1);
            //$section->addTextBreak();
            $categories_4_2 = $label_data_jp;
            $series_4_2     = $nilai_data_jp_per;
            $chart_4_2      = $section->addChart('column', $categories_4_2, $series_4_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Jumlah Penganggur " . $periode_jp_tahun . " (Ribu Orang)", $fontgambar, $fontgambar1);
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);


            // Halaman Baru
            // $section = $phpWord->addSection();
            // $section->addText('2. Perkembangan PDRB per Kapita ADHB', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            // $section->addImage(
            //     $hal2,
            //     array(
            //         'positioning' => 'absolute',
            //         'marginLeft' => 50,
            //         'marginTop' => 15,
            //         'width' => 100,
            //         'height' => 78,
            //         'wrappingStyle' => 'behind',
            //         'posHorizontal' => 'absolute',
            //         'posHorizontalRel' => 'page',
            //         'posVertical' => 'absolute',
            //         'posVerticalRel' => 'line',
            //     )
            // );
            // //$section->addText('2. Perkembangan PDRB per Kapita ADHB',$fontStyleName);
            // $paragraf_2_2  = "PDRB per kapita ADHB " . $xname . " pada tahun " . $tahun_adhb2[5] . " " . $meningkatmenurunADHB . " dibandingkan dengan tahun " . $tahun_adhb2[4] . ". "
            //     . "Pada tahun " . $tahun_adhb2[5] . " PDRB per kapita ADHB " . $xname . " adalah sebesar Rp" . number_format(end($nilaiData_max_p), 0) . " " . $ket_adhb2[5] . ""
            //     . "sedangkan pada tahun " . $tahun_adhb2[4] . " PDRB per kapita ADHB tercatat sebesar Rp" . number_format($nilaiData_max_p[4], 0) . ". ";
            // $paragraf_2_3  = "Capaian PDRB per kapita " . $xname . " pada tahun " . $tahun_adhb2[5] . " berada " . $dibawahdiatasADHB . " capaian Nasional. "
            //     . "PDRB per kapita ADHB Nasional pada tahun " . $tahun_adhb2[5] . " adalah sebesar Rp" . number_format($nilaiData_max[5]) . ". ";
            // //$section->addText($deskripsi2,  array('name' => 'Arial', 'size' => 10),array('alignment'=>'both'));
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 380,
            //         'height'      => 98,
            //         'borderSize'  => 1,
            //         'borderColor' => '#F2A132',
            //     )
            // );
            // $textbox->addText($deskripsi2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'left',
            //         'width'       => 223,
            //         'height'      => 100,
            //         'borderSize'  => 1,
            //         'borderColor' => '#004D8B',
            //         'positioning' => 'relative',
            //         //'marginTop' => 20,
            //     )
            // );
            // $textbox->addText($paragraf_2_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 223,
            //         'height'      => 90,
            //         'borderSize'  => 1,
            //         'borderColor' => '#539333',
            //     )
            // );
            // $textbox->addText($paragraf_2_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            // $categories_2_2 = $tahun_adhb;
            // $series_2_n     = $datay_adhb1;
            // $series_2_2     = $datay_adhb2;
            // foreach ($chartTypes2 as $chartType) {
            //     $chart = $section->addChart($chartType, $categories_2_2, $series_2_n, $style, 'Nasional');
            //     $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
            //     if (in_array($chartType, $twoSeries)) {
            //         $chart->addSeries($categories_2_2, $series_2_2, $xname);
            //     }
            //     $section->addText('Gambar ' . $gambar++ . '. Perkembangan PDRB per kapita ADHB (Juta Rupiah)', $fontgambar, $fontgambar1);
            // }


            // $categories_2_3 = $label_data_adhb;
            // $series_2_3     = $nilai_data_adhb_per;
            // $chart_2_3      = $section->addChart('column', $categories_2_3, $series_2_3, $style_2);
            // $section->addText("Gambar " . $gambar++ . ". Perbandingan PDRB per kapita ADHB Tahun " . $periode_adhb_tahun . " (Juta Rupiah)", $fontgambar, $fontgambar1);
            // $section->addTextBreak();
            // $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // // Halaman Baru
            // $section = $phpWord->addSection();
            // $section->addText('3. Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            // $section->addImage(
            //     $hal7,
            //     array(
            //         'positioning' => 'absolute',
            //         'marginLeft' => 50,
            //         'marginTop' => 15,
            //         'width' => 100,
            //         'height' => 78,
            //         'wrappingStyle' => 'behind',
            //         'posHorizontal' => 'absolute',
            //         'posHorizontalRel' => 'page',
            //         'posVertical' => 'absolute',
            //         'posVerticalRel' => 'line',
            //     )
            // );
            // $paragraf_3_2  = "PDRB per kapita ADHK tahun dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " " . $meningkatmenurunADHK . " dibandingkan dengan tahun " . $tahun_adhk[4] . ". Pada tahun " . $tahun_adhk[5] . " PDRB per kapita ADHK tahun dasar 2010 " . $xname . " adalah sebesar Rp" . number_format(end($adhk_p)) . " " . $ket_adhk2[5] . "sedangkan pada tahun " . $tahun_adhk[4] . " PDRB per kapita ADHK tahun dasar 2010 tercatat sebesar Rp" . number_format($adhk_p[4]) . ". ";
            // $paragraf_3_3  = "Capaian PDRB per kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " berada " . $dibawahdiatasADHK . " capaian nasional. PDRB per kapita ADHK tahun dasar 2010 nasional pada tahun " . $tahun_adhk[5] . " adalah sebesar Rp" . number_format(end($adhk_nasional)) . ".";
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 380,
            //         'height'      => 100,
            //         'borderSize'  => 1,
            //         'borderColor' => '#F2A132',

            //     )
            // );
            // $textbox->addText($deskripsi3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'left',
            //         'width'       => 230,
            //         'height'      => 115,
            //         'borderSize'  => 1,
            //         'borderColor' => '#004D8B',
            //         'positioning' => 'relative',
            //         //'marginTop' => 20,
            //     )
            // );
            // $textbox->addText($paragraf_3_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 220,
            //         'height'      => 90,
            //         'borderSize'  => 1,
            //         'borderColor' => '#539333',
            //         //'marginTop' => -2,
            //     )
            // );
            // $textbox->addText($paragraf_3_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            // //$section->addTextBreak(1);
            // $categories_3_1 = $tahun_adhk1;
            // $series_3_n     = $datay_adhk1;
            // $series_3_1     = $datay_adhk2;
            // foreach ($chartTypes as $chartType) {
            //     $chart = $section->addChart($chartType, $categories_3_1, $series_3_n, $style, 'Nasional');
            //     $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
            //     if (in_array($chartType, $twoSeries)) {
            //         $chart->addSeries($categories_3_1, $series_3_1, $xname);
            //     }
            //     $section->addText('Gambar ' . $gambar++ . '. Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010 (Juta Rupiah)', $fontgambar, $fontgambar1);
            // }

            // $categories_3_2 = $label_data_adhk;
            // $series_3_2     = $nilai_data_adhk_per;
            // $chart_2_3      = $section->addChart('column', $categories_3_2, $series_3_2, $style_ADHK);
            // $section->addText("Gambar " . $gambar++ . ". Perbandingan PDRB per Kapita ADHK (2010) tahun " . $periode_adhk_tahun . " (Juta Rupiah)", $fontgambar, $fontgambar1);
            // //$section->addTextBreak();
            // $section->addText('Sumber Data : Badan Pusat Statistik, diolah', $fontgambar);



            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('7. Indeks Pembangunan Manusia', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal10,
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
                )
            );
            $paragraf_6_2  = "Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " " . $menurunmeningkatIPM . " dibandingkan dengan tahun " . $tahun_ipm2[4] . ". Pada tahun " . $tahun_ipm2[5] . " IPM " . $xname . " adalah sebesar " . number_format(end($nilaiData_ipm2), 2) . " sedangkan pada tahun " . $tahun_ipm2[4] . " IPM tercatat sebesar " . number_format($nilaiData_ipm2[4], 2) . ".";
            $paragraf_6_3  = "Capaian IPM " . $xname . " pada tahun " . $tahun_ipm2[5] . " berada " . $dibawahdiatasIPM . " capaian Nasional. Indeks Pembangunan Manusia Nasional pada tahun " . $tahun_ipm2[5] . " adalah sebesar " . number_format(end($nilaiData_ipm), 2) . ".";

            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 90,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi6,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_6_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_6_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            $categories_6_1 = $tahun_ipm;
            $series_6_n     = $datay_ipm;
            $series_6_1     = $datay_ipm2;
            $series_6_2 = $nilaiTarget_ipm;
            $series_6_3 = $nilaiRpjmn_ipm;
            $series_6_4 = $nilaiRKPD_ipm;
            $series_6_5 = $nilaiRKP_ipm;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_6_1, $series_6_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_6_1, $series_6_1, $xname);
                    $chart->addSeries($categories_6_1, $series_6_2, 'Target');
                    $chart->addSeries($categories_6_1, $series_6_3, 'Target Makro RPJMN');
                    $chart->addSeries($categories_6_1, $series_6_4, 'Target RKPD');
                    $chart->addSeries($categories_6_1, $series_6_5, 'Target Kewilayahan RKP');
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Indeks Pembangunan Manusia', $fontgambar, $fontgambar1);
            }
            $section->addText($ipm_perbandingan_pro,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));

            $categories_6_2 = $label_data_ipm;
            $series_6_2     = $nilai_data_ipm_per;
            $chart_6_2      = $section->addChart('column', $categories_6_2, $series_6_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Indeks Pembangunan Manusia Tahun " . $periode_ipm_tahun . " ", $fontgambar, $fontgambar1);
            //$ipm_perbandingan_kab
            $section->addText($ipm_perbandingan_kab,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 11), array('alignment' => 'both'));
            // $label_ipm_k1       = $label_pek1;
            // $nilai_data_ipm_kab = $nilai_ipm_per_kab;
            $chart_6_2      = $section->addChart('column', $label_ipm_k1, $nilai_data_ipm_kab, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Indeks Pembangunan Manusia Tahun " . $periode_ipm_tahun . " ", $fontgambar, $fontgambar1);
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            $section = $phpWord->addSection();
            $section->addText('8. Rasio Gini', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            $section->addImage(
                $hal11,
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
                )
            );
            $paragraf_7_2  = "Rasio gini " . $xname . " pada " . $tahun_grR[5] . " " . $menurunmeningkatGR . " dibandingkan dengan " . $tahun_grR[3] . ". Pada " . $tahun_grR[5] . " rasio gini " . $xname . " adalah sebesar " . number_format($nilaiData_gr2[5], 3) . " sedangkan pada " . $tahun_grR[3] . " rasio gini tercatat sebesar " . number_format($nilaiData_gr2[3], 3) . ". ";
            $paragraf_7_3  = "Capaian rasio gini " . $xname . " pada " . $tahun_grR[5] . " berada " . $dibawahdiatasGR . " capaian nasional. Rasio gini nasional pada " . $tahun_grR[5] . " adalah sebesar " . number_format($nilaiData_gr[5], 3) . ".";

            $textbox = $section->addTextBox(
                array(
                    'align'       => 'right',
                    'width'       => 360,
                    'height'      => 115,
                    'borderSize'  => 1,
                    'borderColor' => '#F2A132',
                )
            );
            $textbox->addText($deskripsi7,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
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
            $textbox->addText($paragraf_7_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

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
            $textbox->addText($paragraf_7_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            $categories_7_1 = $tahun_gr;
            $series_7_n     = $datay_gr;
            $series_7_1     = $datay_gr2;
            $series_7_2 = $datay_gr3;
            $series_7_3 = $datay_gr4; // = $nilaiRpjmn_gr1;
            $series_7_4 = $datay_gr5; // = $nilaiRKPD_gr1;
            $series_7_5 = $datay_gr6; //= $nilaiRKP_gr1;
            foreach ($chartTypes as $chartType) {
                $chart = $section->addChart($chartType, $categories_7_1, $series_7_n, $style, 'Nasional');
                $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories_7_1, $series_7_1, $xname);
                    $chart->addSeries($categories_7_1, $series_7_2, 'Target');
                    $chart->addSeries($categories_7_1, $series_7_3, 'Target Makro RPJMN');
                    $chart->addSeries($categories_7_1, $series_7_4, 'Target RKPD');
                    $chart->addSeries($categories_7_1, $series_7_5, 'Target Kewilayahan RKP');
                }
                $section->addText('Gambar ' . $gambar++ . '. Perkembangan Rasio Gini', $fontgambar, $fontgambar1);
            }
            $categories_7_2 = $label_data_gr;
            $series_7_2     = $nilai_data_gr_per;
            $chart_7_2      = $section->addChart('column', $categories_7_2, $series_7_2, $style_2);
            $section->addText("Gambar " . $gambar++ . ". Perbandingan Rasio Gini " . $periode_gr_tahun . " ", $fontgambar, $fontgambar1);
            $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            // $section = $phpWord->addSection();
            // $section->addText('8. Angka Harapan Hidup', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            // $section->addImage(
            //     $hal12,
            //     array(
            //         'positioning' => 'absolute',
            //         'marginLeft' => 50,
            //         'marginTop' => 15,
            //         'width' => 100,
            //         'height' => 78,
            //         'wrappingStyle' => 'behind',
            //         'posHorizontal' => 'absolute',
            //         'posHorizontalRel' => 'page',
            //         'posVertical' => 'absolute',
            //         'posVerticalRel' => 'line',
            //     )
            // );
            // $paragraf_8_1  = "Angka harapan hidup adalah perkiraan rata-rata tambahan umur seseorang yang diharapkan dapat terus hidup. Angka Harapan Hidup juga dapat didefinisikan sebagai rata-rata jumlah tahun yang dijalani oleh seseorang setelah orang tersebut mencapai ulang tahun yang ke-x. Ukuran yang umum digunakan adalah angka harapan hidup saat lahir yang mencerminkan kondisi kesehatan pada saat itu. Sehingga pada umumnya ketika membicarakan AHH, yang dimaksud adalah rata-rata jumlah tahun yang akan dijalani oleh seseorang sejak orang tersebut lahir.";
            // $paragraf_8_2  = "Angka harapan hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " " . $menurunmeningkatAHH . " dibandingkan dengan tahun " . $tahun_ahh[4] . ". Pada tahun " . $tahun_ahh[5] . " angka harapan hidup nasional " . $xname . " adalah sebesar " . number_format(end($nilaiData_ahh2), 2) . " tahun sedangkan pada tahun " . $tahun_ahh[4] . " angka harapan hidup tercatat sebesar " . number_format($nilaiData_ahh2[4], 2) . " tahun.";
            // $paragraf_8_3  = "Capaian angka harapan hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " berada " . $dibawahdiatasAHH . " capaian Nasional. Angka harapan hidup nasional pada tahun " . $tahun_ahh[5] . " adalah sebesar " . number_format(end($nilaiData_ahh), 2) . " tahun.";
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 360,
            //         'height'      => 115,
            //         'borderSize'  => 1,
            //         'borderColor' => '#F2A132',
            //     )
            // );
            // $textbox->addText($paragraf_8_1,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'left',
            //         'width'       => 223,
            //         'height'      => 125,
            //         'borderSize'  => 1,
            //         'borderColor' => '#004D8B',
            //         'positioning' => 'relative',
            //     )
            // );
            // $textbox->addText($paragraf_8_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 223,
            //         'height'      => 95,
            //         'borderSize'  => 1,
            //         'borderColor' => '#539333',
            //         //'marginTop' => -2,
            //     )
            // );
            // $textbox->addText($paragraf_8_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $categories_8_1 = $tahun_ahh;
            // $series_8_n     = $datay_ahh;
            // $series_8_1     = $datay_ahh2;
            // foreach ($chartTypes as $chartType) {
            //     $chart = $section->addChart($chartType, $categories_8_1, $series_8_n, $style, 'Nasional');
            //     $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
            //     if (in_array($chartType, $twoSeries)) {
            //         $chart->addSeries($categories_8_1, $series_8_1, $xname);
            //     }
            //     $section->addText('Gambar ' . $gambar++ . '. Perkembangan Angka Harapan Hidup (Tahun)', $fontgambar, $fontgambar1);
            //     //$section->addTextBreak();
            // }
            // $categories_8_2 = $label_ahh;
            // $series_8_2     = $nilai_ahh_per;
            // $chart_8_2      = $section->addChart('column', $categories_8_2, $series_8_2, $style_2);
            // $section->addText("Gambar " . $gambar++ . ". Perbandingan Angka Harapan Hidup Tahun " . $periode_ahh_tahun . " (Tahun)", $fontgambar, $fontgambar1);
            // //$section->addTextBreak();
            // $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru RLS
            // $section = $phpWord->addSection();
            // $section->addText('9. Rata-rata Lama Sekolah', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            // $section->addImage(
            //     $hal13,
            //     array(
            //         'positioning' => 'absolute',
            //         'marginLeft' => 50,
            //         'marginTop' => 15,
            //         'width' => 100,
            //         'height' => 78,
            //         'wrappingStyle' => 'behind',
            //         'posHorizontal' => 'absolute',
            //         'posHorizontalRel' => 'page',
            //         'posVertical' => 'absolute',
            //         'posVerticalRel' => 'line',
            //     )
            // );
            // $paragraf_9_1  = "Rata-rata lama sekolah merupakan salah satu indikator pembentuk Indeks Pembangunan Manusia dari dimensi pendidikan. Rata-rata lama sekolah menunjukkan jumlah tahun belajar penduduk usia 25 tahun ke atas yang telah diselesaikan dalam pendidikan formal (tidak termasuk tahun yang mengulang). Indikator ini menunjukkan tingkat pendidikan formal dari penduduk di suatu wilayah. Semakin tinggi nilai rata-rata lama sekolah, semakin baik pula tingkat pendidikan di suatu wilayah.";
            // $paragraf_9_2  = "Rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " " . $menurunmeningkatRLS . " dibandingkan dengan tahun " . $tahun_rls[4] . ". Pada tahun " . $tahun_rls[5] . " Rata-rata lama sekolah " . $xname . " " . number_format(end($nilaiData_rls2), 2) . " tahun, sedangkan pada tahun " . $tahun_rls[4] . " rata-rata lama sekolah tercatat sebesar " . number_format($nilaiData_rls2[4], 2) . " tahun.";
            // $paragraf_9_3  = "Capaian rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " berada " . $dibawahdiatasRLS . " capaian Nasional. Rata-rata lama sekolah nasional pada tahun " . $tahun_rls[5] . " sebesar " . number_format($nilaiData_rls[5], 2) . " tahun.";

            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 360,
            //         'height'      => 115,
            //         'borderSize'  => 1,
            //         'borderColor' => '#F2A132',
            //     )
            // );
            // $textbox->addText($paragraf_9_1,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'left',
            //         'width'       => 223,
            //         'height'      => 125,
            //         'borderSize'  => 1,
            //         'borderColor' => '#004D8B',
            //         'positioning' => 'relative',
            //     )
            // );
            // $textbox->addText($paragraf_9_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 223,
            //         'height'      => 95,
            //         'borderSize'  => 1,
            //         'borderColor' => '#539333',
            //     )
            // );
            // $textbox->addText($paragraf_9_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $categories_9_1 = $tahun_rls;
            // $series_9_n     = $datay_rls;
            // $series_9_1     = $datay_rls2;
            // foreach ($chartTypes as $chartType) {
            //     $chart = $section->addChart($chartType, $categories_9_1, $series_9_n, $style, 'Nasional');
            //     $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
            //     if (in_array($chartType, $twoSeries)) {
            //         $chart->addSeries($categories_9_1, $series_9_1, $xname);
            //     }
            //     $section->addText('Gambar ' . $gambar++ . '. Perkembangan Rata-rata Lama Sekolah (Tahun)', $fontgambar, $fontgambar1);
            //     //$section->addTextBreak();
            // }
            // $categories_9_2 = $label_rls;
            // $series_9_2     = $nilai_rls_per;
            // $chart_9_2      = $section->addChart('column', $categories_9_2, $series_9_2, $style_2);
            // $section->addText("Gambar " . $gambar++ . ". Perbandingan Rata-rata Lama Sekolah Tahun " . $periode_rls_tahun . " (Tahun)", $fontgambar, $fontgambar1);
            // //$section->addTextBreak();
            // $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            // $section = $phpWord->addSection();
            // $section->addText('10. Harapan Lama Sekolah', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            // $section->addImage(
            //     $hal14,
            //     array(
            //         'positioning' => 'absolute',
            //         'marginLeft' => 50,
            //         'marginTop' => 15,
            //         'width' => 100,
            //         'height' => 78,
            //         'wrappingStyle' => 'behind',
            //         'posHorizontal' => 'absolute',
            //         'posHorizontalRel' => 'page',
            //         'posVertical' => 'absolute',
            //         'posVerticalRel' => 'line',
            //     )
            // );
            // $paragraf_10_2  = "Harapan lama sekolah " . $xname . " pada tahun " . $tahun_hls[5] . " " . $menurunmeningkatHLS . " dibandingkan dengan tahun " . $tahun_hls[4] . ". Pada tahun " . $tahun_hls[5] . " Harapan Lama Sekolah " . $xname . " adalah sebesar " . number_format(end($nilaiData_hls2), 2) . " tahun, sedangkan pada tahun " . $tahun_hls[4] . " rata-rata lama sekolah tercatat sebesar " . number_format($nilaiData_hls2[4], 2) . " tahun.";
            // $paragraf_10_3  = "Capaian harapan lama sekolah " . $xname . " pada tahun " . $tahun_hls[5] . " berada " . $dibawahdiatasHLS . " capaian Nasional. Harapan lama sekolah Nasional pada tahun " . $tahun_hls[5] . " adalah sebesar " . number_format(end($nilaiData_hls), 2) . " tahun.";
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 360,
            //         'height'      => 115,
            //         'borderSize'  => 1,
            //         'borderColor' => '#F2A132',
            //     )
            // );
            // $textbox->addText($deskripsi10,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'left',
            //         'width'       => 223,
            //         'height'      => 125,
            //         'borderSize'  => 1,
            //         'borderColor' => '#004D8B',
            //         'positioning' => 'relative',
            //     )
            // );
            // $textbox->addText($paragraf_10_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 223,
            //         'height'      => 95,
            //         'borderSize'  => 1,
            //         'borderColor' => '#539333',
            //         //'marginTop' => -2,
            //     )
            // );
            // $textbox->addText($paragraf_10_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $categories_10_1 = $tahun_hls;
            // $series_10_n     = $datay_hls;
            // $series_10_1     = $datay_hls2;
            // foreach ($chartTypes as $chartType) {
            //     $chart = $section->addChart($chartType, $categories_10_1, $series_10_n, $style, 'Nasional');
            //     $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
            //     if (in_array($chartType, $twoSeries)) {
            //         $chart->addSeries($categories_10_1, $series_10_1, $xname);
            //     }
            //     $section->addText('Gambar ' . $gambar++ . '. Perkembangan Harapan Lama Sekolah (Tahun)', $fontgambar, $fontgambar1);
            //     //$section->addTextBreak();
            // }
            // $categories_10_2 = $label_data_hls;
            // $series_10_2     = $nilai_data_hls_per;
            // $chart_10_2      = $section->addChart('column', $categories_10_2, $series_10_2, $style_2);
            // $section->addText("Gambar " . $gambar++ . ". Perbandingan Harapan Lama Sekolah tahun " . $periode_hls_tahun . " (Tahun)", $fontgambar, $fontgambar1);
            // //$section->addTextBreak();
            // $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            // $section = $phpWord->addSection();
            // $section->addText('11. Pengeluaran per Kapita', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            // $section->addImage(
            //     $hal15,
            //     array(
            //         'positioning' => 'absolute',
            //         'marginLeft' => 50,
            //         'marginTop' => 15,
            //         'width' => 100,
            //         'height' => 78,
            //         'wrappingStyle' => 'behind',
            //         'posHorizontal' => 'absolute',
            //         'posHorizontalRel' => 'page',
            //         'posVertical' => 'absolute',
            //         'posVerticalRel' => 'line',
            //     )
            // );
            // $paragraf_11_2  = "Pengeluaran per kapita " . $xname . " pada tahun " . $tahun_ppk[5] . " " . $menurunmeningkatPPK . " dibandingkan dengan tahun " . $tahun_ppk[4] . ". Pada tahun " . $tahun_ppk[5] . " pengeluaran per kapita " . $xname . " adalah sebesar Rp" . number_format(end($nilaiData_ppk2)) . " sedangkan pada tahun " . $tahun_ppk[4] . " pengeluaran per kapita tercatat sebesar Rp" . number_format($nilaiData_ppk2[4]) . ". ";
            // $paragraf_11_3  = "Capaian pengeluaran per kapita " . $xname . " pada tahun " . $tahun_ppk[5] . " berada " . $dibawahdiatasPPK . " capaian nasional. Pengeluaran per kapita Nasional pada tahun " . $tahun_ppk[5] . " adalah sebesar Rp" . number_format(end($nilaiData_ppk)) . " ";
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 360,
            //         'height'      => 115,
            //         'borderSize'  => 1,
            //         'borderColor' => '#F2A132',
            //     )
            // );
            // $textbox->addText($deskripsi11,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'left',
            //         'width'       => 223,
            //         'height'      => 125,
            //         'borderSize'  => 1,
            //         'borderColor' => '#004D8B',
            //         'positioning' => 'relative',
            //     )
            // );
            // $textbox->addText($paragraf_11_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 223,
            //         'height'      => 95,
            //         'borderSize'  => 1,
            //         'borderColor' => '#539333',
            //         //'marginTop' => -2,
            //     )
            // );
            // $textbox->addText($paragraf_11_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $categories_11_1 = $tahun_ppk;
            // $series_11_n     = $datay_ppk;
            // $series_11_1     = $datay_ppk2;
            // foreach ($chartTypes as $chartType) {
            //     $chart = $section->addChart($chartType, $categories_11_1, $series_11_n, $style, 'Nasional');
            //     $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
            //     if (in_array($chartType, $twoSeries)) {
            //         $chart->addSeries($categories_11_1, $series_11_1, $xname);
            //     }
            //     $section->addText('Gambar ' . $gambar++ . '. Perkembangan Pengeluaran per Kapita (Juta Rupiah)', $fontgambar, $fontgambar1);
            //     //$section->addTextBreak();
            // }
            // $categories_11_2 = $label_data_ppk;
            // $series_11_2     = $nilai_data_ppk_per;
            // $chart_11_2      = $section->addChart('column', $categories_11_2, $series_11_2, $style_2);
            // $section->addText("Gambar " . $gambar++ . ". Perbandingan Pengeluaran per Kapita Tahun " . $periode_ppk_tahun . " (Juta Rupiah)", $fontgambar, $fontgambar1);
            // //$section->addTextBreak();
            // $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);



            // Halaman Baru
            // $section = $phpWord->addSection();
            // $section->addText('13. Indeks Kedalaman Kemiskinan (P1)', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            // $section->addImage(
            //     $hal17,
            //     array(
            //         'positioning' => 'absolute',
            //         'marginLeft' => 50,
            //         'marginTop' => 15,
            //         'width' => 100,
            //         'height' => 78,
            //         'wrappingStyle' => 'behind',
            //         'posHorizontal' => 'absolute',
            //         'posHorizontalRel' => 'page',
            //         'posVertical' => 'absolute',
            //         'posVerticalRel' => 'line',
            //     )
            // );
            // $tulisannormal       = array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10);
            // $tulisanfontmiring   = array('name' => 'Book Antiqua (Body)', 'size' => 10, 'spaceAfter' => 80, 'italic' => true);


            // $paragraf_13_1_2 = "), merupakan ukuran rata-rata kesenjangan pengeluaran masing-masing penduduk miskin terhadap garis kemiskinan. Semakin tinggi nilai indeks kedalaman kemiskinan, semakin jauh rata-rata pengeluaran penduduk miskin dari garis kemiskinan.";
            // $paragraf_13_2 = "Indeks kedalaman kemiskinan " . $xname . " pada " . $tahunIDK1[5] . " " . $menurunmeningkatIKK . " dibandingkan dengan " . $tahunIDK1[3] . ". Pada " . $tahunIDK1[5] . " indeks kedalaman kemiskinan " . $xname . " adalah sebesar " . number_format(end($nilaiData_idk2), 2) . ", sedangkan pada " . $tahunIDK1[3] . " indeks kedalaman kemiskinan tercatat sebesar " . number_format($nilaiData_idk2[3], 2) . ". ";
            // $paragraf_13_3 = "Capaian indeks kedalaman kemiskinan " . $xname . " pada " . $tahunIDK1[5] . " berada " . $dibawahdiatasIKK . " capaian Nasional. Indeks kedalaman kemiskinan Nasional pada " . $tahunIDK1[5] . " adalah sebesar " . number_format(end($nilaiData_idk), 2) . ".";
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



            // $phpWord->addParagraphStyle('formatbox', array(
            //     'alignment' => 'both',
            //     //       'align'       => 'right',
            //     'width'       => 160,
            //     'height'      => 90,
            //     'borderSize'  => 1,
            //     'borderColor' => '#F2A132',
            //     //       'indentation' => array('left' => 2960, 'right' => 60, 'hanging' => 360),
            //     'indentation' => array('left' => 2160),
            // ));

            // $textrun = $section->addTextRun('formatbox');
            // $textrun->addText(htmlspecialchars("Indeks Kedalaman Kemiskinan ("), $fontparagraf);
            // $textrun->addText(htmlspecialchars('Poverty Gap Index-P1'), $fontmiring);
            // $textrun->addText(htmlspecialchars("), merupakan ukuran rata-rata kesenjangan pengeluaran masing-masing penduduk miskin terhadap garis kemiskinan. Semakin tinggi nilai indeks kedalaman kemiskinan, semakin jauh rata-rata pengeluaran penduduk miskin dari garis kemiskinan."), $fontparagraf);



            // //   
            // //$textbox->addText($textbox1);
            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'left',
            //         'width'       => 223,
            //         'height'      => 125,
            //         'borderSize'  => 1,
            //         'borderColor' => '#004D8B',
            //         'positioning' => 'relative',
            //     )
            // );
            // $textbox->addText($paragraf_13_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 223,
            //         'height'      => 95,
            //         'borderSize'  => 1,
            //         'borderColor' => '#539333',
            //         //'marginTop' => -2,
            //     )
            // );
            // $textbox->addText($paragraf_13_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $categories_13_1 = $tahun_idk;
            // $series_13_n     = $datay_idk;
            // $series_13_1     = $datay_idk2;
            // foreach ($chartTypes as $chartType) {
            //     $chart = $section->addChart($chartType, $categories_13_1, $series_13_n, $style, 'Nasional');
            //     $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
            //     if (in_array($chartType, $twoSeries)) {
            //         $chart->addSeries($categories_13_1, $series_13_1, $xname);
            //     }
            //     $section->addText('Gambar ' . $gambar++ . '. Perkembangan Indeks Kedalaman Kemiskinan', $fontgambar, $fontgambar1);
            //     //$section->addTextBreak();
            // }
            // $categories_13_2 = $label_data_ikk;
            // $series_13_2     = $nilai_data_ikk_per;
            // $chart_13_2      = $section->addChart('column', $categories_13_2, $series_13_2, $style_2);
            // $section->addText("Gambar " . $gambar++ . ". Perbandingan Indeks Kedalaman Kemiskinan " . $periode_ikk_tahun . "", $fontgambar, $fontgambar1);
            // //$section->addTextBreak();
            // $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            // Halaman Baru
            // $section = $phpWord->addSection();
            // $section->addText('14. Indeks Keparahan Kemiskinan (P2)', array('name' => 'Century Gothic (Headings)', 'size' => 22, 'color' => '1B2232', 'bold' => true,));
            // $section->addImage(
            //     $hal18,
            //     array(
            //         'positioning' => 'absolute',
            //         'marginLeft' => 50,
            //         'marginTop' => 15,
            //         'width' => 100,
            //         'height' => 78,
            //         'wrappingStyle' => 'behind',
            //         'posHorizontal' => 'absolute',
            //         'posHorizontalRel' => 'page',
            //         'posVertical' => 'absolute',
            //         'posVerticalRel' => 'line',
            //     )
            // );
            // $paragraf_15_2 = "Indeks keparahan kemiskinan " . $xname . " pada " . $tahunIKK1[5] . " " . $menurunmeningkatIKKK . " dibandingkan dengan " . $tahunIKK1[3] . ". Pada " . $tahunIKK1[5] . " indeks keparahan kemiskinan " . $xname . " adalah sebesar " . number_format(end($nilaiData_ikk2), 2) . ", sedangkan pada " . $tahunIKK1[3] . " indeks keparahan kemiskinan tercatat sebesar " . number_format($nilaiData_ikk2[3], 2) . ". ";
            // $paragraf_15_3 = "Capaian indeks keparahan kemiskinan " . $xname . " pada " . $tahunIKK1[5] . " berada " . $dibawahdiatasIKKK . " capaian Nasional. Indeks keparahan kemiskinan Nasional pada " . $tahunIKK1[5] . " adalah sebesar " . number_format(end($nilaiData_ikk), 2) . ".";

            // //            $textbox = $section->addTextBox(
            // //                array(
            // //                    'align'       => 'right',
            // //                    'width'       => 310,
            // //                    'height'      => 90,
            // //                    'borderSize'  => 1,
            // //                    'borderColor' => '#F2A132',
            // //                )
            // //            );
            // //            $textbox->addText($deskripsi15,  array('name' => 'Book Antiqua (Body)','spaceAfter'=>80, 'size' => 10),array('alignment'=>'both'));
            // //            Indeks Keparahan Kemiskinan (Proverty Severity Index-P2) memberikan gambaran mengenai penyebaran pengeluaran diantara penduduk miskin. Semakin tinggi nilai indeks, semakin tinggi ketimpangan pengeluaran diantara penduduk miskin.

            // $phpWord->addParagraphStyle('formatbox1', array(
            //     'alignment' => 'both',
            //     //       'align'       => 'right',
            //     'width'       => 160,
            //     'height'      => 90,
            //     'borderSize'  => 1,
            //     'borderColor' => '#F2A132',
            //     'indentation' => array('left' => 2160),
            // ));

            // $textrun = $section->addTextRun('formatbox1');
            // $textrun->addText(htmlspecialchars("Indeks Keparahan Kemiskinan ("), $fontparagraf);
            // $textrun->addText(htmlspecialchars('Proverty Severity Index-P2'), $fontmiring);
            // $textrun->addText(htmlspecialchars(") memberikan gambaran mengenai penyebaran pengeluaran diantara penduduk miskin. Semakin tinggi nilai indeks, semakin tinggi ketimpangan pengeluaran diantara penduduk miskin."), $fontparagraf);

            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'left',
            //         'width'       => 223,
            //         'height'      => 125,
            //         'borderSize'  => 1,
            //         'borderColor' => '#004D8B',
            //         'positioning' => 'relative',
            //     )
            // );
            // $textbox->addText($paragraf_15_2,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));

            // $textbox = $section->addTextBox(
            //     array(
            //         'align'       => 'right',
            //         'width'       => 223,
            //         'height'      => 95,
            //         'borderSize'  => 1,
            //         'borderColor' => '#539333',
            //         //'marginTop' => -2,
            //     )
            // );
            // $textbox->addText($paragraf_15_3,  array('name' => 'Book Antiqua (Body)', 'spaceAfter' => 80, 'size' => 10), array('alignment' => 'both'));
            // $categories_15_1 = $tahun_ikk;
            // $series_15_n     = $datay_ikk;
            // $series_15_1     = $datay_ikk2;
            // foreach ($chartTypes as $chartType) {
            //     $chart = $section->addChart($chartType, $categories_15_1, $series_15_n, $style, 'Nasional');
            //     $chart->getStyle()->setWidth(Converter::inchToEmu(6))->setHeight(Converter::inchToEmu(2));
            //     if (in_array($chartType, $twoSeries)) {
            //         $chart->addSeries($categories_15_1, $series_15_1, $xname);
            //     }
            //     $section->addText('Gambar ' . $gambar++ . '. Perkembangan Indeks Keparahan Kemiskinan', $fontgambar, $fontgambar1);
            //     //                $section->addTextBreak();
            // }
            // $categories_15_2 = $label_data_ikkk;
            // $series_15_2     = $nilai_data_ikkk_per;
            // $chart_15_2      = $section->addChart('column', $categories_15_2, $series_15_2, $style_2);
            // $section->addText("Gambar " . $gambar++ . ". Perbandingan Indeks Keparahan Kemiskinan " . $periode_ikkk_tahun . "", $fontgambar, $fontgambar1);
            // //            $section->addTextBreak();
            // $section->addText('Sumber Data : Badan Pusat Statistik', $fontgambar);

            $phpWord->addTitleStyle(1, array('size' => 14, 'bold' => true), array('keepNext' => true, 'spaceBefore' => 240));
            $phpWord->addTitleStyle(2, array('size' => 14, 'bold' => true), array('keepNext' => true, 'spaceBefore' => 240));

            // 2D charts
            //$section = $phpWord->addSection();
            //$section->addTitle(htmlspecialchars('2D charts'), 1);
            //$section = $phpWord->addSection(array('colsNum' => 2, 'breakType' => 'continuous'));





            $filename = $xname . '.docx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); //mime type
            header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
            header('Cache-Control: max-age=0'); //no cache
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('php://output');
        }
    }
}
