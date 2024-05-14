<?php defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . "third_party/New_folder/PhpWord/Autoloader.php");

use PhpOffice\PhpWord\Autoloader;

Autoloader::register();

use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\Common\XMLWriter;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TestHelperDOCX;



class Laporan_perkembangan extends CI_Controller
{
    var $view_dir   = "peppd1/laporan_perkembangan/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/laporan_ppd/laporan_perkembangan.js";
    var $picture    = "picture/laporan_ppd";

    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Master", "m_ref");
        // load core JpGraph as CI library
        //$this->load->library('jpgraph/jpgraph.php');
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

    function daerah_list()
    {
        if ($this->input->is_ajax_request()) {
            try {
                // $list_data_pro = $this->db->query("SELECT nama_provinsi FROM provinsi")->result();
                // $list_data_kab_kota = $this->db->query("SELECT nama_kabupaten FROM kabupaten")->result();

                $list_daerah = $this->db->query("SELECT pro.id AS id, pro.nama_provinsi AS nama, null as id_provinsi FROM provinsi pro UNION SELECT kab.id AS id, kab.nama_kabupaten AS nama, kab.prov_id AS id_provinsi FROM kabupaten kab ORDER BY id")->result();

                // $data_list_daerah = array();

                // foreach ($list_data_pro as $data_pro) {
                //     array_push($data_list_daerah, $data_pro->nama_provinsi);
                // }

                // foreach ($list_data_kab_kota as $data_kab_kota) {
                //     array_push($data_list_daerah, $data_kab_kota->nama_kabupaten);
                // }

                $json_data = array(
                    "data"  => $list_daerah,   // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            die;
        }
    }

    function years_list()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $list_years = $this->db->query("SELECT DISTINCT(tahun) FROM `nilai_indikator` ORDER BY tahun ASC")->result();

                $json_data = array(
                    "data"  => $list_years,   // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            die;
        }
    }

    function pro_datatable()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;

                $idx = 0;
                $columns = array(
                    // datatable column index  => database column name
                    $idx++   => "A.id",
                    $idx++   => "A.nama_provinsi",
                    $idx++   => "A.label",
                    $idx++   => "A.`ppd`",
                );
                $sql = "SELECT A.id,A.`nama_provinsi`,A.`label`,A.`ppd`
                        FROM provinsi A
                        WHERE 1=1 ";
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;
                if (!empty($requestData['search']['value'])) {
                    $sql .= " AND ( "
                        . " A.`id` LIKE '%" . $requestData['search']['value'] . "%' "
                        . " OR A.`nama_provinsi` LIKE '%" . $requestData['search']['value'] . "%' "
                        . " OR A.`label` LIKE '%" . $requestData['search']['value'] . "%' "
                        . ")";
                }
                $list_data = $this->db->query($sql);
                $totalFiltered = $list_data->num_rows();

                $sql .= " ORDER BY "
                    . $columns[$requestData['order'][0]['column']] . "   "
                    . $requestData['order'][0]['dir'] . "  "
                    . "LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "   ";
                $list_data = $this->db->query($sql);
                $data = array();
                $i = 1;
                foreach ($list_data->result() as $row) {
                    $nestedData = array();
                    $id     = $row->id;
                    $nestedData[] = $row->id;
                    $nestedData[] = $row->nama_provinsi;
                    $nestedData[] = $row->label;
                    $tmp = " data-id='" . $id . "' ";
                    $grp = " data-gi='" . $row->nama_provinsi . "' ";
                    $nestedData[] = ""
                        . "<input type='radio' class='radio' name='group' $tmp $grp value='" . $row->group_indikator . "'  /> ";
                    $data[] = $nestedData;
                }
                $json_data = array(
                    "draw"            => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval($totalData),  // total number of records
                    "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $data   // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else
            die;
    }

    function kab_datatable()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('id', 'ID', 'required');
                $prov = $this->input->post("id");
                $idprov = decrypt_text($prov);
                $idx = 0;
                $columns = array(
                    // datatable column index  => database column name
                    $idx++   => "K.id",
                    $idx++   => "K.nama_kabupaten",

                );
                $sql = "SELECT K.`id`'kb', K.`nama_kabupaten`, K.`prov_id`, K.`ppd`, K.`urutan`, P.id, P.nama_provinsi 
                    FROM `kabupaten` K LEFT JOIN provinsi P ON P.id = K.`prov_id` WHERE 1=1 ";

                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if (!empty($requestData['search']['value'])) {
                    $sql .= " AND ( "
                        . " K.`id` LIKE '%" . $requestData['search']['value'] . "%' "
                        . " OR K.`nama_kabupaten` LIKE '%" . $requestData['search']['value'] . "%' "
                        . ")";
                }
                $list_data = $this->db->query($sql);
                $totalFiltered = $list_data->num_rows();

                $sql .= " ORDER BY "
                    . $columns[$requestData['order'][0]['column']] . "   "
                    . $requestData['order'][0]['dir'] . "  "
                    . "LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "   ";
                $list_data = $this->db->query($sql);
                $data = array();
                $i = 1;
                foreach ($list_data->result() as $row) {
                    $nestedData = array();
                    $id     = $row->kb;
                    $nestedData[] = $row->kb;
                    $nestedData[] = $row->nama_kabupaten;
                    //$tmp = " data-id='".encrypt_text($id)."' ";
                    $tmp = " data-id='" . $id . "' ";
                    $kp = " data-kp='" . $row->nama_kabupaten . "' ";
                    $tpr = " data-idp='" . encrypt_text($row->id) . "'";
                    $tpr = " data-idp='" . $row->id . "'";
                    $pr = " data-pr='" . $row->nama_provinsi . "' ";
                    $nestedData[] = ""
                        . "<input type='radio' class='radio' name='group' $tmp $kp $pr $tpr value='" . $row->nama_kabupaten . "'  /> ";
                    $data[] = $nestedData;
                }
                $json_data = array(
                    "draw"            => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval($totalData),  // total number of records
                    "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $data   // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else
            die;
    }

    function download_act()
    {
        // if(!$this->session->userdata(SESSION_LOGIN)){ throw new Exception("Your session is ended, please relogin",2); }
        $provinsi  = $_GET['inp_pro'];
        $kabupaten = $_GET['inp_sp'];
        //$tahun     = $_GET['tahun'];
        //$pro = decrypt_text($provinsi); $kab = decrypt_text($kabupaten);
        $pro = $provinsi;
        $kab = $kabupaten;
        $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
        $xname = "";
        $query = "";
        $d_peek = "SELECT I.`deskripsi` FROM indikator I where id='1'";
        $list_peek = $this->db->query($d_peek);
        foreach ($list_peek->result() as $peek) {
            $peek_d   = $peek->deskripsi;
        }
        $d_adhb = "SELECT I.`deskripsi` FROM indikator I where id='2'";
        $list_adhb = $this->db->query($d_adhb);
        foreach ($list_adhb->result() as $adhb) {
            $adhb_d   = $adhb->deskripsi;
        }
        $d_adhk = "SELECT I.`deskripsi` FROM indikator I where id='3'";
        $list_adhk = $this->db->query($d_adhk);
        foreach ($list_adhk->result() as $adhk) {
            $adhk_d   = $adhk->deskripsi;
        }
        $d_jp = "SELECT I.`deskripsi` FROM indikator I where id='4'";
        $list_jp = $this->db->query($d_jp);
        foreach ($list_jp->result() as $jp) {
            $jp_d   = $jp->deskripsi;
        }
        $d_ipm = "SELECT I.`deskripsi` FROM indikator I where id='5'";
        $list_ipm = $this->db->query($d_ipm);
        foreach ($list_ipm->result() as $ipm) {
            $ipm_d   = $ipm->deskripsi;
        }
        $d_tpt = "SELECT I.`deskripsi` FROM indikator I where id='6'";
        $list_tpt = $this->db->query($d_tpt);
        foreach ($list_tpt->result() as $tpt) {
            $tpt_d   = $tpt->deskripsi;
        }
        $d_gr = "SELECT I.`deskripsi` FROM indikator I where id='7'";
        $list_gr = $this->db->query($d_gr);
        foreach ($list_gr->result() as $gr) {
            $gr_d   = $gr->deskripsi;
        }
        $d_ahh = "SELECT I.`deskripsi` FROM indikator I where id='8'";
        $list_ahh = $this->db->query($d_ahh);
        foreach ($list_ahh->result() as $ahh) {
            $ahh_d   = $ahh->deskripsi;
        }
        $d_rls = "SELECT I.`deskripsi` FROM indikator I where id='9'";
        $list_rls = $this->db->query($d_rls);
        foreach ($list_rls->result() as $rls) {
            $rls_d   = $rls->deskripsi;
        }
        $d_hls = "SELECT I.`deskripsi` FROM indikator I where id='10'";
        $list_hls = $this->db->query($d_hls);
        foreach ($list_hls->result() as $hls) {
            $hls_d   = $hls->deskripsi;
        }
        $d_pk = "SELECT I.`deskripsi` FROM indikator I where id='11'";
        $list_pk = $this->db->query($d_pk);
        foreach ($list_pk->result() as $pk) {
            $pk_d   = $pk->deskripsi;
        }
        $d_tk = "SELECT I.`deskripsi` FROM indikator I where id='36'";
        $list_tk = $this->db->query($d_tk);
        foreach ($list_tk->result() as $tk) {
            $tk_d   = $tk->deskripsi;
        }
        $d_ikk = "SELECT I.`deskripsi` FROM indikator I where id='39'";
        $list_ikk = $this->db->query($d_ikk);
        foreach ($list_ikk->result() as $ikk) {
            $ikk_d   = $ikk->deskripsi;
        }
        $d_jpm = "SELECT I.`deskripsi` FROM indikator I where id='40'";
        $list_jpm = $this->db->query($d_jpm);
        foreach ($list_jpm->result() as $jpm) {
            $jpm_d   = $jpm->deskripsi;
        }

        date_default_timezone_set("Asia/Jakarta");
        $current_date_time = date("Y-m-d H:i:s");
        $picture_1          = "halaman_1_" . date("Y_m_d_H_i_s");
        $picture_2          = "halaman_2_" . date("Y_m_d_H_i_s");
        $picture_pe         = "pertumbuhan_ekonomi_" . date("Y_m_d_H_i_s");
        $picture_pe_bar     = "pertumbuhan_ekonomi_per" . date("Y_m_d_H_i_s");
        $picture_rpe        = "radar_pertumbuhan_ekonomi_" . date("Y_m_d_H_i_s");
        $picture_pdrb       = "PDRB_" . date("Y_m_d_H_i_s");
        $picture_adhb       = "adhb_" . date("Y_m_d_H_i_s");
        $picture_adhb_bar   = "adhb_per" . date("Y_m_d_H_i_s");
        $picture_adhk       = "adhk_" . date("Y_m_d_H_i_s");
        $picture_adhk_bar   = "adhk_per" . date("Y_m_d_H_i_s");
        $picture_jp         = "jp_" . date("Y_m_d_H_i_s");
        $picture_jp_bar     = "jp_per" . date("Y_m_d_H_i_s");
        $picture_tpt        = "tingkatpengangguranterbuka_" . date("Y_m_d_H_i_s");
        $picture_tpt_bar    = "tpt_per" . date("Y_m_d_H_i_s");
        $picture_ipm        = "indekspembangunanmanusia_" . date("Y_m_d_H_i_s");
        $picture_ipm_bar    = "ipm_per" . date("Y_m_d_H_i_s");
        $picture_gr         = "gini_rasio_" . date("Y_m_d_H_i_s");
        $picture_gr_bar     = "gr_per" . date("Y_m_d_H_i_s");
        $picture_ahh        = "angka_harapan_hidup_" . date("Y_m_d_H_i_s");
        $picture_ahh_bar    = "ahh_per" . date("Y_m_d_H_i_s");
        $picture_rls        = "rata_lama_sekolah_" . date("Y_m_d_H_i_s");
        $picture_rls_bar    = "rls_per" . date("Y_m_d_H_i_s");
        $picture_hls        = "harapan_lama_sekolah_" . date("Y_m_d_H_i_s");
        $picture_hls_bar    = "hls_per" . date("Y_m_d_H_i_s");
        $picture_ppk        = "pengeluaran_per_kapita_" . date("Y_m_d_H_i_s");
        $picture_ppk_bar    = "ppk_per" . date("Y_m_d_H_i_s");
        $picture_tk         = "tingkat_kemiskinan_" . date("Y_m_d_H_i_s");
        $picture_tk_bar     = "tk_per" . date("Y_m_d_H_i_s");
        $picture_ikk        = "indeks_kedalaman_kemiskinan_" . date("Y_m_d_H_i_s");
        $picture_ikk_bar    = "ikk_per" . date("Y_m_d_H_i_s");
        $picture_jpk        = "jumlah_penduduk_miskin_" . date("Y_m_d_H_i_s");
        $picture_jpk_bar    = "jpk_per" . date("Y_m_d_H_i_s");

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
            $sql_ppe = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppe = $this->db->query($sql_ppe);
            foreach ($list_ppe->result() as $row_ppe) {
                $tahun1[]   = $row_ppe->tahun;
                $nilaiData1[] = (float)$row_ppe->nilai;
                $nilaimax[] = number_format($row_ppe->nilai, 2);
            }
            $max_pe = end($nilaiData1);
            $sql_ppe_pro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppe_pro = $this->db->query($sql_ppe_pro);
            foreach ($list_ppe_pro->result() as $row_ppe_pro) {
                $tahun1_pro[]   = $row_ppe_pro->tahun;
                $nilaiData1_pro[] = (float)$row_ppe_pro->nilai;
                $nilaimax_pro[] = number_format($row_ppe_pro->nilai, 2);
                $sumber_pe = $row_ppe_pro->sumber;
                $periode_pe[] = $row_ppe_pro->id_periode;
            }
            $periode_pe_max = max($periode_pe);
            $tahun_pe_max = max($tahun1_pro) . " Antar Provinsi";

            $datay1 = $nilaiData1;
            $datay2 = $nilaiData1_pro;
            if ($nilaimax_pro[4] > $nilaimax_pro[5]) {
                $max_pe    = "Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " menurun dibandingkan dengan Tahun " . $tahun1[4] . ". Pada tahun " . $tahun1[5] . " pertumbuhan ekonomi " . $xname . " adalah sebesar " . end($nilaimax_pro) . "%, sedangkan pada tahun " . $tahun1[4] . " pertumbuhannya tercatat sebesar " . $nilaimax_pro[4] . "%. ";
                if ($nilaimax[5] > $nilaimax_pro[5]) {
                    $max_pe_p  = " Capaian Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " dibawah nasional. Pertumbuhan ekonomi nasional pada tahun " . $tahun1[5] . " adalah sebesar " . end($nilaimax) . "%. ";
                } else {
                    $max_pe_p  = " Capaian Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " diatas nasional. Pertumbuhan ekonomi nasional pada tahun " . $tahun1[5] . " adalah sebesar " . end($nilaimax) . "%. ";
                }
            } else {
                $max_pe    = "Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " meningkat dibandingkan dengan Tahun " . $tahun1[4] . ". Pada tahun " . $tahun1[5] . " pertumbuhan ekonomi " . $xname . " adalah sebesar " . end($nilaimax_pro) . "%, sedangkan pada tahun " . $tahun1[4] . " pertumbuhannya tercatat sebesar " . $nilaimax_pro[4] . "%. ";
                if ($nilaimax[5] > $nilaimax_pro[5]) {
                    $max_pe_p  = "Capaian Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " dibawah nasional. Pertumbuhan ekonomi nasional pada tahun " . $tahun1[5] . " adalah sebesar " . end($nilaimax) . "%. ";
                } else {
                    $max_pe_p  = " Capaian Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " diatas nasional. Pertumbuhan ekonomi nasional pada tahun " . $tahun1[5] . " adalah sebesar " . end($nilaimax) . "%. ";
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

            //perbandingan pertumbuhan ekonomi
            $graph_ppe = new Graph(500, 230);
            $graph_ppe->SetScale("textlin");
            $theme_class_ppe = new UniversalTheme;
            $graph_ppe->SetTheme($theme_class_ppe);
            $graph_ppe->SetMargin(40, 10, 33, 58);
            $graph_ppe->SetBox(false);
            $graph_ppe->yaxis->HideZeroLabel();
            $graph_ppe->yaxis->HideLine(false);
            $graph_ppe->yaxis->HideTicks(false, false);
            $graph_ppe->xaxis->SetTickLabels($tahun1);
            $graph_ppe->ygrid->SetFill(false);

            $graph_bar_ppe = new Graph(500, 250);
            $graph_bar_ppe->SetScale("textlin");
            $graph_bar_ppe->SetY2Scale("lin", 0, 90);
            $graph_bar_ppe->SetY2OrderBack(false);
            $theme_class_bar_ppe = new UniversalTheme;
            $graph_bar_ppe->SetTheme($theme_class_bar_ppe);
            $graph_bar_ppe->SetMargin(23, 0.1, 0.1, 150);
            $graph_bar_ppe->ygrid->SetFill(false);
            $graph_bar_ppe->xaxis->SetTickLabels($label_data_ppe);
            $graph_bar_ppe->xaxis->SetLabelAngle(90);
            $graph_bar_ppe->yaxis->HideLine(false);
            $graph_bar_ppe->yaxis->HideTicks(false, false);

            //perkembangan pertumbuhan ekonomi
            $p1_ppe = new LinePlot($nilaiData1);
            $graph_ppe->Add($p1_ppe);
            $p1_ppe->SetColor("#0000FF");
            $p1_ppe->SetLegend('Indonesia');
            $p1_ppe->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ppe->mark->SetColor('#0000FF');
            $p1_ppe->mark->SetFillColor('#0000FF');
            $p1_ppe->value->SetColor('#0000FF');
            $p1_ppe->SetCenter();
            $p1_ppe->value->Show();
            $p1_ppe->value->SetFormat('%0.2f');
            // $p1_ppe->grid->SetColor('darkgrey');
            $graph_ppe->legend->SetFrameWeight(2);
            $graph_ppe->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ppe->legend->SetMarkAbsSize(8);

            $p2_ppe = new LinePlot($nilaiData1_pro);
            $graph_ppe->Add($p2_ppe);
            $p2_ppe->SetColor("#000000");
            $p2_ppe->SetLegend($xname);
            $p2_ppe->mark->SetType(MARK_UTRIANGLE, 2);
            $p2_ppe->mark->SetColor('#000000');
            $p2_ppe->mark->SetFillColor('#000000');
            $p2_ppe->value->SetColor('#000000');
            $p2_ppe->value->SetMargin(14);
            $p2_ppe->SetCenter();
            $p2_ppe->value->Show();
            $p2_ppe->value->SetFormat('%0.2f');
            $p2_ppe->SetStyle("dotted");

            $b1plot_ppe_per = new BarPlot($nilai_data_ppe_per);
            $gbplot_ppe_per = new GroupBarPlot(array($b1plot_ppe_per));
            $graph_bar_ppe->Add($gbplot_ppe_per);
            $b1plot_ppe_per->SetColor("white");
            $b1plot_ppe_per->SetFillColor("#0000FF");


            //Perkembangan PDRB Per Kapita ADHB (Rp)
            $sql_adhb = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhb = $this->db->query($sql_adhb);
            foreach ($list_adhb->result() as $row_adhb) {
                $tahun_adhb[]      = $row_adhb->tahun;
                $nilaiData_adhb1[] = (float)$row_adhb->nilai / 1000000;
                $nilaiData_max[]   = (float)$row_adhb->nilai;
                $adhb_nasional[]   = number_format($row_adhb->nilai, 1);
            }
            $datay_adhb1 = $nilaiData_adhb1;
            $tahun_adhb1 = $tahun_adhb;
            $max_pdrb = end($nilaiData_adhb1);
            $sql_adhb2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhb2 = $this->db->query($sql_adhb2);
            foreach ($list_adhb2->result() as $row_adhb2) {
                $tahun_adhb2[]   = $row_adhb2->tahun;
                $nilaiData_adhb2[] = (float)$row_adhb2->nilai / 1000000;
                $nilaiData_max_p[] = (float)$row_adhb2->nilai;
                $sumber_adhb       = $row_adhb2->sumber;
                $periode_adhb[] = $row_adhb2->id_periode;
                $ket_adhb2[]  = $row_adhb2->keterangan;
            }
            $datay_adhb2 = $nilaiData_adhb2;
            $tahun_adhb2 = $tahun_adhb2;
            $tahunadhb  = end($tahun_adhb1);
            $periode_adhb_max = max($periode_adhb);
            $periode_adhb_tahun = max($tahun_adhb2) . " Antar Provinsi";

            $max_adhb_k  = " ";
            if ($nilaiData_max_p[4] > $nilaiData_max_p[5]) {
                $max_adhb    = "PDRB Per Kapita ADHB " . $xname . " pada tahun " . $tahun_adhb2[5] . " menurun dibandingkan dengan tahun " . $tahun_adhb2[4] . ". Pada tahun " . $tahun_adhb2[5] . " PDRB perkapita ADHB " . $xname . " adalah sebesar Rp " . number_format(end($nilaiData_max_p), 0) . " " . $ket_adhb2[5] . " sedangkan pada tahun " . $tahun_adhb2[4] . "   PDRB perkapita ADHB tercatat sebesar Rp " . number_format($nilaiData_max_p[4], 0) . ". ";
                if ($nilaiData_max[5] > $nilaiData_max_p[5]) {
                    $max_adhb_p  = "Capaian PDRB Per Kapita " . $xname . " pada tahun " . $tahun_adhb2[5] . " berada dibawah capaian nasional. PDRB perkapita ADHB nasional pada tahun " . $tahun_adhb2[5] . " adalah sebesar  Rp " . number_format($nilaiData_max[5]) . " ";
                } else {
                    $max_adhb_p  = "Capaian PDRB Per Kapita " . $xname . " pada tahun " . $tahun_adhb2[5] . " berada diatas capaian nasional. PDRB perkapita ADHB nasional pada tahun " . $tahun_adhb2[5] . " adalah sebesar  Rp " . number_format($nilaiData_max[5]) . " ";
                }
            } else {
                $max_adhb    = "PDRB Per Kapita ADHB " . $xname . " pada tahun " . $tahun_adhb2[5] . " meningkat dibandingkan dengan tahun " . $tahun_adhb2[4] . ". Pada tahun " . $tahun_adhb2[5] . " PDRB perkapita ADHB " . $xname . " adalah sebesar Rp " . number_format(end($nilaiData_max_p), 0) . " " . $ket_adhb2[5] . " sedangkan pada tahun " . $tahun_adhb2[4] . "  PDRB perkapita ADHB tercatat sebesar Rp " . number_format($nilaiData_max_p[4], 0) . ". ";
                if ($nilaiData_max[5] > $nilaiData_max_p[5]) {
                    $max_adhb_p  = "Capaian PDRB Per Kapita " . $xname . " pada tahun " . $tahun_adhb2[5] . " berada dibawah capaian nasional. PDRB perkapita ADHB nasional pada tahun " . $tahun_adhb2[5] . " adalah sebesar  Rp " . number_format($nilaiData_max[5]) . " ";
                } else {
                    $max_adhb_p  = "Capaian PDRB Per Kapita " . $xname . " pada tahun " . $tahun_adhb2[5] . " berada diatas capaian nasional. PDRB perkapita ADHB nasional pada tahun " . $tahun_adhb2[5] . " adalah sebesar  Rp " . number_format($nilaiData_max[5]) . " ";
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
                $nilai_adhb_per[] = $row_adhb_per->nilai / 1000000;
            }
            $label_data_adhb     = $label_adhb;
            $nilai_data_adhb_per = $nilai_adhb_per;

            //bar adhb
            $graph_adhb = new Graph(500, 230);
            $graph_adhb->SetScale("textlin");
            $graph_adhb->SetY2Scale("lin", 0, 90);
            $graph_adhb->SetY2OrderBack(false);
            $theme_class_adhb = new UniversalTheme;
            $graph_adhb->SetTheme($theme_class_adhb);
            $graph_adhb->SetMargin(40, 20, 46, 80);
            //$graph_adhb->yaxis->SetTickPositions(array(25000000,50000000,75000000));
            $graph_adhb->ygrid->SetFill(false);
            //$graph->xaxis->SetTickLabels(array(Aceh, Sumut, Sumbar, Riau, Jambi, Sumsel, Bengkulu, Lampung, Babel, Kepri, DKI, Jabar, Jateng, DIY, Jatim, Banten, Bali, NTB, NTT, Kalbar, Kalteng, Kalsel, Kaltim, Kaltara, Sulut, Sulteng, Sulsel, Sultra, Gorontalo, Sulbar, Maluku, Malut, Papbar, Papua));
            $graph_adhb->xaxis->SetTickLabels($tahun_adhb1);
            $graph_adhb->yaxis->HideLine(false);
            $graph_adhb->yaxis->HideTicks(false, false);
            $graph_adhb->title->Set(" ");

            $graph_bar_adhb = new Graph(500, 230);
            $graph_bar_adhb->SetScale("textlin");
            $graph_bar_adhb->SetY2Scale("lin", 0, 90);
            $graph_bar_adhb->SetY2OrderBack(false);
            $theme_class_bar_adhb = new UniversalTheme;
            $graph_bar_adhb->SetTheme($theme_class_bar_adhb);
            $graph_bar_adhb->SetMargin(40, 20, 20, 100);
            //$graph_adhb->yaxis->SetTickPositions(array(25000000,50000000,75000000));
            $graph_bar_adhb->ygrid->SetFill(false);
            $graph_bar_adhb->xaxis->SetTickLabels($label_data_adhb);
            $graph_bar_adhb->yaxis->HideLine(false);
            $graph_bar_adhb->yaxis->HideTicks(false, false);
            $graph_bar_adhb->title->Set(" ");
            $graph_bar_adhb->xaxis->SetLabelAngle(90);

            //adhb1     
            $b1plot_adhb = new BarPlot($datay_adhb1);
            $b1plot2_adhb = new BarPlot($datay_adhb2);
            $gbplot_adhb = new GroupBarPlot(array($b1plot_adhb, $b1plot2_adhb));
            $graph_adhb->Add($gbplot_adhb);
            $b1plot_adhb->SetColor("white");
            $b1plot_adhb->SetFillColor("#0000FF");
            $b1plot_adhb->SetLegend("Indonesia");
            $b1plot_adhb->SetWidth(20);
            $b1plot2_adhb->SetColor("white");
            $b1plot2_adhb->SetFillColor("#000000");
            $b1plot2_adhb->SetLegend($xname);
            $b1plot2_adhb->SetWidth(20);

            $b1plot_adhb_per = new BarPlot($nilai_data_adhb_per);
            $gbplot_adhb_per = new GroupBarPlot(array($b1plot_adhb_per));
            $graph_bar_adhb->Add($gbplot_adhb_per);
            $b1plot_adhb_per->SetColor("white");
            $b1plot_adhb_per->SetFillColor("#0000FF");




            //adhk (Rp)
            $sql_adhk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhk = $this->db->query($sql_adhk);
            foreach ($list_adhk->result() as $row_adhk) {
                $tahun_adhk[]   = $row_adhk->tahun;
                $nilaiData_adhk1[] = (float)$row_adhk->nilai / 1000000;
                $adhk_nasional[] = (float)$row_adhk->nilai;
            }
            $datay_adhk1 = $nilaiData_adhk1;
            $tahun_adhk1 = $tahun_adhk;


            $sql_adhk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhk2 = $this->db->query($sql_adhk2);
            foreach ($list_adhk2->result() as $row_adhk2) {
                $tahun_adhk2[]   = $row_adhk2->tahun;
                $nilaiData_adhk2[] = (float)$row_adhk2->nilai / 1000000;
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
                $max_adhk    = "PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " menurun dibandingkan dengan tahun " . $tahun_adhk[4] . ". Pada tahun " . $tahun_adhk[5] . " PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " adalah sebesar Rp " . number_format(end($adhk_p)) . " " . $ket_adhk2[5] . " sedangkan pada tahun " . $tahun_adhk[4] . "  PDRB per Kapita ADHK Tahun Dasar 2010 tercatat sebesar Rp " . number_format($adhk_p[4]) . ". ";
                if ($adhk_nasional[5] > $adhk_p[5]) {
                    $max_adhk_p  = "Capaian PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " berada dibawah capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun " . $tahun_adhk[5] . " adalah sebesar  Rp " . number_format(end($adhk_nasional)) . " ";
                } else {
                    $max_adhk_p  = "Capaian PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhb[5] . " berada diatas capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun " . $tahun_adhk[5] . " adalah sebesar  Rp " . number_format(end($adhk_nasional)) . " ";
                }
            } else {
                $max_adhk    = "PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " meningkat dibandingkan dengan tahun " . $tahun_adhk[4] . ". Pada tahun " . $tahun_adhk[5] . " PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " adalah sebesar Rp " . number_format(end($adhk_p)) . " " . $ket_adhk2[5] . " sedangkan pada tahun " . $tahun_adhk[4] . "  PDRB per Kapita ADHK Tahun Dasar 2010 tercatat sebesar Rp " . number_format($adhk_p[4]) . ". ";
                if ($adhk_nasional[5] > $adhk_p[5]) {
                    $max_adhk_p  = "Capaian PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " berada dibawah capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun " . $tahun_adhk[5] . " adalah sebesar  Rp " . number_format(end($adhk_nasional)) . " ";
                } else {
                    $max_adhk_p  = "Capaian PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhb[5] . " berada diatas capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun " . $tahun_adhk[5] . " adalah sebesar  Rp " . number_format(end($adhk_nasional)) . " ";
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
            // print_r($perbandingan_adhk);exit();
            $list_adhk_per = $this->db->query($perbandingan_adhk);
            foreach ($list_adhk_per->result() as $row_adhk_per) {
                $label_adhk[]     = $row_adhk_per->label;
                $nilai_adhk_per[] = $row_adhk_per->nilai / 1000000;
            }
            $label_data_adhk     = $label_adhk;
            $nilai_data_adhk_per = $nilai_adhk_per;

            //adhk
            $graph_adhk = new Graph(500, 230);
            $graph_adhk->SetScale("textlin");
            $theme_class_adhk = new UniversalTheme;
            $graph_adhk->SetTheme($theme_class_adhk);
            $graph_adhk->SetMargin(40, 20, 33, 58);
            //                $graph_adhk->title->Set('Perkembangan PDRB Per Kapita ADHK Tahun Dasar 2010');
            $graph_adhk->SetBox(false);
            $graph_adhk->yaxis->HideZeroLabel();
            $graph_adhk->yaxis->HideLine(false);
            $graph_adhk->yaxis->HideTicks(false, false);
            $graph_adhk->xaxis->SetTickLabels($tahun_adhk);
            $graph_adhk->ygrid->SetFill(false);

            $graph_bar_adhk = new Graph(500, 230);
            $graph_bar_adhk->SetScale("textlin");
            $graph_bar_adhk->SetY2Scale("lin", 0, 90);
            $graph_bar_adhk->SetY2OrderBack(false);
            $theme_class_bar_adhk = new UniversalTheme;
            $graph_bar_adhk->SetTheme($theme_class_bar_adhk);
            $graph_bar_adhk->SetMargin(80, 20, 5, 80);
            //$graph_adhb->yaxis->SetTickPositions(array(25000000,50000000,75000000));
            $graph_bar_adhk->ygrid->SetFill(false);
            $graph_bar_adhk->xaxis->SetTickLabels($label_data_adhk);
            $graph_bar_adhk->yaxis->HideLine(false);
            $graph_bar_adhk->yaxis->HideTicks(false, false);
            $graph_bar_adhk->title->Set(" ");
            $graph_bar_adhk->xaxis->SetLabelAngle(90);




            //jumlah pengangguran (Orang)
            $sql_jp = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jp = $this->db->query($sql_jp);
            foreach ($list_jp->result() as $row_jp) {
                $tahun_jp[]      = $bulan[$row_jp->periode] . "-" . $row_jp->tahun;
                $tahun_jp1[]     = $row_jp->id_periode;
                $nilaiData_jp[]  = (float)$row_jp->nilai / 1000;
                $nilai_capaian[] = $row_jp->nilai;
                $tahun_jp11[] = $row_jp->tahun;
                $periode_jp1[] = $row_jp->periode;
            }
            $datay_jp = $nilaiData_jp;
            $tahun_jp = $tahun_jp;
            $periode_jp_max  = max($tahun_jp1);
            //$periode_jp_tahun=max($tahun_jp)." Antar Provinsi" ;
            $periode_jp_tahun = $bulan[max($periode_jp1)] . " " . max($tahun_jp11) . " Antar Provinsi";
            $sql_jp2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jp2 = $this->db->query($sql_jp2);
            foreach ($list_jp2->result() as $row_jp2) {
                $tahun_jp2[]   = $row_jp2->tahun;
                $nilaiData_jp2[] = (float)$row_jp2->nilai / 1000;
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
                //$rt_jp=$nilai_capaian2[3]-$nilai_capaian2[5];$rt_jp2=$rt_jp/$nilai_capaian2[3];$rt_jp3=$rt_jp2*100;$rt_jp33=number_format($rt_jp3,2);
                $rt_jp = $nilai_capaian2[5] - $nilai_capaian2[3];
                $rt_jpp = abs($nilai_capaian2[5] - $nilai_capaian2[3]);
                $rt_jp2 = $rt_jp / $nilai_capaian2[3];
                $rt_jp3 = abs($rt_jp2 * 100);
                //$rt_jp3=$rt_jp2*100;
                //$t='berkurang';
                $rt_jp33 = number_format($rt_jp3, 2);
                $max_jp_p  = "Jumlah penganggur di " . $xname . " pada " . $tahun_jp[5] . " sebanyak " . number_format($nilai_capaian2[5], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahun_jp[3] . " sebanyak " . number_format($nilai_capaian2[3], 0) . " orang. Selama periode  " . $tahun_jp[3] . " - " . $tahun_jp[5] . " jumlah penganggur di " . $xname . " berkurang " . number_format($rt_jpp) . " orang atau sebesar " . $rt_jp33 . "% ";
            } else {
                $rt_jp  = $nilai_capaian2[5] - $nilai_capaian2[3];
                $rt_jp2 = $rt_jp / $nilai_capaian2[3];
                $rt_jp3 = $rt_jp2 * 100;
                //$t='meningkat';
                $rt_jp33 = number_format($rt_jp3, 2);
                $max_jp_p  = "Jumlah penganggur di " . $xname . " pada " . $tahun_jp[5] . " sebanyak " . number_format($nilai_capaian2[5], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahun_jp[3] . " sebanyak " . number_format($nilai_capaian2[3], 0) . " orang. Selama periode  " . $tahun_jp[3] . " - " . $tahun_jp[5] . " jumlah penganggur di " . $xname . " meningkat " . number_format($rt_jp) . " orang atau sebesar " . $rt_jp33 . "%";
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
                $nilai_jp_per[] = $row_jp_per->nilai / 1000;
            }
            $label_data_jp     = $label_jp;
            $nilai_data_jp_per = $nilai_jp_per;
            //jumlah pengangguran
            $graph_jp = new Graph(550, 230);
            $graph_jp->SetScale("textlin");
            $graph_jp->SetY2Scale("lin", 0, 90);
            $graph_jp->SetY2OrderBack(false);
            $theme_class_jp = new UniversalTheme;
            $graph_jp->SetTheme($theme_class_jp);
            $graph_jp->SetMargin(80, 20, 46, 80);
            $graph_jp->ygrid->SetFill(false);
            $graph_jp->xaxis->SetTickLabels($tahun_jp);
            $graph_jp->yaxis->HideLine(false);
            $graph_jp->yaxis->HideTicks(false, false);
            $graph_jp->title->Set("");
            $graph_bar_jp = new Graph(600, 230);
            $graph_bar_jp->SetScale("textlin");
            $graph_bar_jp->SetY2Scale("lin", 0, 90);
            $graph_bar_jp->SetY2OrderBack(false);
            $theme_class_bar_jp = new UniversalTheme;
            $graph_bar_jp->SetTheme($theme_class_bar_jp);
            $graph_bar_jp->SetMargin(80, 20, 20, 100);
            $graph_bar_jp->ygrid->SetFill(false);
            $graph_bar_jp->xaxis->SetTickLabels($label_data_jp);
            $graph_bar_jp->yaxis->HideLine(false);
            $graph_bar_jp->yaxis->HideTicks(false, false);
            $graph_bar_jp->title->Set(" ");
            $graph_bar_jp->xaxis->SetLabelAngle(90);

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
            $b1plot_jp_per = new BarPlot($nilai_data_jp_per);
            $gbplot_jp_per = new GroupBarPlot(array($b1plot_jp_per));
            $graph_bar_jp->Add($gbplot_jp_per);
            $b1plot_jp_per->SetColor("white");
            $b1plot_jp_per->SetFillColor("#0000FF");







            //tingkat pengangguran terbuka
            $sql_tpt = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tpt = $this->db->query($sql_tpt);
            foreach ($list_tpt->result() as $row_tpt) {
                $tahun_tpt1[]    = $bulan[$row_tpt->periode] . "-" . $row_tpt->tahun;
                $tahun_tpt[]   = $row_tpt->tahun;
                $nilaiData_tpt[] = (float)$row_tpt->nilai;
            }
            $datay_tpt = $nilaiData_tpt;
            $tahun_tpt = $tahun_tpt;
            $sql_tpt2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tpt2 = $this->db->query($sql_tpt2);
            foreach ($list_tpt2->result() as $row_tpt2) {
                $tahun_tpt21[]    = $bulan[$row_tpt2->periode] . "-" . $row_tpt2->tahun;
                $periode_tpt21[]    = $row_tpt2->periode;
                $tahun_tpt2[]     = $row_tpt2->tahun;
                $nilaiData_tpt2[] = (float)$row_tpt2->nilai;
                $sumber_tpt       = $row_tpt2->sumber;
                $periode_tpt_id[]    =   $row_tpt2->id_periode;
            }
            $datay_tpt2 = $nilaiData_tpt2;
            $tahun_tpt2 = $tahun_tpt2;
            $periode_tpt_max = max($periode_tpt_id);
            $periode_tpt_tahun = $bulan[max($periode_tpt21)] . " " . max($tahun_tpt2) . " Antar Provinsi";

            if ($nilaiData_tpt2[3] > $nilaiData_tpt2[5]) {
                $max_tpt    = "Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " menurun dibandingkan dengan " . $tahun_tpt21[3] . ". Pada " . $tahun_tpt21[5] . " Tingkat Pengangguran Terbuka " . $xname . " adalah sebesar " . number_format(end($nilaiData_tpt2), 2) . "% sedangkan pada " . $tahun_tpt21[3] . "  Tingkat Pengangguran Terbuka tercatat sebesar " . number_format($nilaiData_tpt2[3], 2) . "%. ";
                if ($nilaiData_tpt[5] > $nilaiData_tpt2[5]) {
                    $max_tpt_p  = "Capaian Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " berada dibawah capaian nasional. Tingkat Pengangguran Terbuka nasional pada " . $tahun_tpt21[5] . " adalah sebesar " . number_format(end($nilaiData_tpt), 2) . "% ";
                } else {
                    $max_tpt_p  = "Capaian Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " berada diatas capaian nasional. Tingkat Pengangguran Terbuka nasional pada " . $tahun_tpt21[5] . " adalah sebesar " . number_format(end($nilaiData_tpt), 2) . "% ";
                }
            } else {
                $max_tpt    = "Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " meningkat dibandingkan dengan " . $tahun_tpt21[3] . ". Pada " . $tahun_tpt21[5] . " Tingkat Pengangguran Terbuka " . $xname . " adalah sebesar " . number_format(end($nilaiData_tpt2), 2) . "% sedangkan pada " . $tahun_tpt21[3] . "  Tingkat Pengangguran Terbuka tercatat sebesar " . number_format($nilaiData_tpt2[3], 2) . "%. ";
                if ($nilaiData_tpt[5] > $nilaiData_tpt2[5]) {
                    $max_tpt_p  = "Capaian Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " berada dibawah capaian nasional. Tingkat Pengangguran Terbuka nasional pada " . $tahun_tpt21[5] . " adalah sebesar " . number_format(end($nilaiData_tpt), 2) . "% ";
                } else {
                    $max_tpt_p  = "Capaian Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " berada diatas capaian nasional. Tingkat Pengangguran Terbuka nasional pada " . $tahun_tpt21[5] . " adalah sebesar " . number_format(end($nilaiData_tpt), 2) . "% ";
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

            //tingkat pengangguran terbuka  
            $graph_tpt = new Graph(500, 230);
            $graph_tpt->SetScale("textlin");
            $theme_class_tpt = new UniversalTheme;
            $graph_tpt->SetTheme($theme_class_tpt);
            $graph_tpt->SetMargin(40, 20, 33, 60);
            $graph_tpt->SetBox(false);
            $graph_tpt->yaxis->HideZeroLabel();
            $graph_tpt->yaxis->HideLine(false);
            $graph_tpt->yaxis->HideTicks(false, false);
            $graph_tpt->xaxis->SetTickLabels($tahun_tpt1);
            $graph_tpt->ygrid->SetFill(false);

            $graph_bar_tpt = new Graph(600, 230);
            $graph_bar_tpt->img->SetMargin(40, 20, 20, 100);
            $graph_bar_tpt->SetScale("textlin");
            $graph_bar_tpt->SetMarginColor("lightblue:1.1");
            $graph_bar_tpt->SetShadow();
            $graph_bar_tpt->title->SetMargin(8);
            $graph_bar_tpt->title->SetColor("darkred");

            $graph_bar_tpt->ygrid->SetFill(false);
            $graph_bar_tpt->xaxis->SetTickLabels($label_data_tpt);
            $graph_bar_tpt->yaxis->HideLine(false);
            $graph_bar_tpt->yaxis->HideTicks(false, false);
            $graph_bar_tpt->xaxis->SetLabelAngle(90);






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

            if ($nilaiData_ipm2[4] > $nilaiData_ipm2[5]) {
                $max_ipm    = "Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " menurun dibandingkan dengan tahun " . $tahun_ipm2[4] . ". Pada tahun " . $tahun_ipm2[5] . " Indeks Pembangunan Manusia " . $xname . " adalah sebesar " . number_format(end($nilaiData_ipm2), 2) . "% sedangkan pada tahun " . $tahun_ipm2[4] . "  Indeks Pembangunan Manusia tercatat sebesar " . number_format($nilaiData_ipm2[4], 2) . "%. ";
                if ($nilaiData_ipm[5] > $nilaiData_ipm2[5]) {
                    $max_ipm_p  = "Capaian Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " berada dibawah capaian nasional. Indeks Pembangunan Manusia nasional pada tahun " . $tahun_ipm2[5] . " adalah sebesar " . number_format(end($nilaiData_ipm), 2) . "% " . "<br/><br/><br/><br/>";
                } else {
                    $max_ipm_p  = "Capaian Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " berada diatas capaian nasional. Indeks Pembangunan Manusia nasional pada tahun " . $tahun_ipm2[5] . " adalah sebesar " . number_format(end($nilaiData_ipm), 2) . "% " . "<br/><br/><br/><br/>";
                }
            } else {
                $max_ipm    = "Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " meningkat dibandingkan dengan tahun " . $tahun_ipm2[4] . ". Pada tahun " . $tahun_ipm2[5] . " Indeks Pembangunan Manusia " . $xname . " adalah sebesar " . number_format(end($nilaiData_ipm2), 2) . "% sedangkan pada tahun " . $tahun_ipm2[4] . "  Indeks Pembangunan Manusia tercatat sebesar " . number_format($nilaiData_ipm2[4], 2) . "%. ";
                if ($nilaiData_ipm[5] > $nilaiData_ipm2[5]) {
                    $max_ipm_p  = "Capaian Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " berada dibawah capaian nasional. Indeks Pembangunan Manusia nasional pada tahun " . $tahun_ipm2[5] . " adalah sebesar " . number_format(end($nilaiData_ipm), 2) . "% " . "<br/><br/><br/><br/>";
                } else {
                    $max_ipm_p  = "Capaian Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " berada diatas capaian nasional. Indeks Pembangunan Manusia nasional pada tahun " . $tahun_ipm2[5] . " adalah sebesar " . number_format(end($nilaiData_ipm), 2) . "% " . "<br/><br/><br/><br/>";
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
                $nilai_ipm_per[] = $row_ipm_per->nilai;
            }
            $label_data_ipm     = $label_ipm;
            $nilai_data_ipm_per = $nilai_ipm_per;

            //Gini Rasio
            $sql_gr = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_gr = $this->db->query($sql_gr);
            foreach ($list_gr->result() as $row_gr) {
                $tahun_gr[]    = $bulan[$row_gr->periode] . "-" . $row_gr->tahun;
                $nilaiData_gr[] = (float)$row_gr->nilai;
                //$nilaiData_grr[] = number_format((float)$row_gr->nilai,2);
            }
            $datay_gr = $nilaiData_gr;
            $tahun_gr = $tahun_gr;
            $sql_gr2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_gr2 = $this->db->query($sql_gr2);
            foreach ($list_gr2->result() as $row_gr2) {
                $tahun_gr2[]   = $row_gr2->tahun;
                $periode    = $row_gr2->periode;
                $nilaiData_gr2[] = (float)$row_gr2->nilai;
                $nilaiData_gr22[] = number_format((float)$row_gr2->nilai, 2);
                $sumber_gr       = $row_gr2->sumber;
                $periode_gr_id[]    = $row_gr2->id_periode;
                $tahun_gr21[]    = $bulan[$row_gr2->periode] . "-" . $row_gr2->tahun;
            }
            $datay_gr2 = $nilaiData_gr2;
            $tahun_gr2 = $tahun_gr2;
            $max_k_gr = "";
            $periode_gr_max = max($periode_gr_id);
            //$periode_gr_tahun=max($tahun_gr21)." Antar Provinsi" ;
            $periode_gr_tahun = $bulan[max($periode)] . " " . max($tahun_gr2) . " Antar Provinsi";
            if ($nilaiData_gr2[3] > $nilaiData_gr2[5]) {
                $max_n_gr    = "1Gini Rasio " . $xname . " pada " . $tahun_gr[5] . " menurun dibandingkan dengan " . $tahun_gr[3] . ". Pada " . $tahun_gr[5] . " gini rasio " . $xname . " adalah sebesar " . number_format($nilaiData_gr2[5], 3) . "% sedangkan pada " . $tahun_gr[3] . "  gini rasio tercatat sebesar " . number_format($nilaiData_gr2[3], 3) . "%. ";
                if ($nilaiData_gr[5] > $nilaiData_gr2[5]) {
                    $max_p_gr  = "Capaian gini rasio " . $xname . " pada " . $tahun_gr[5] . " berada dibawah capaian nasional. Gini rasio nasional pada " . $tahun_gr[5] . " adalah sebesar " . number_format($nilaiData_gr[5], 3) . "% ";
                } else {
                    $max_p_gr  = "Capaian gini rasio " . $xname . " pada " . $tahun_gr[5] . " berada diatas capaian nasional. Gini rasio nasional pada " . $tahun_gr[5] . " adalah sebesar " . number_format($nilaiData_gr[5], 3) . "% ";
                }
            } else {
                $max_n_gr    = "Gini Rasio " . $xname . " pada " . $tahun_gr[5] . " meningkat dibandingkan dengan " . $tahun_gr[3] . ". Pada " . $tahun_gr[5] . " gini rasio " . $xname . " adalah sebesar " . number_format($nilaiData_gr2[5], 3) . "% sedangkan pada " . $tahun_gr[3] . "  gini rasio tercatat sebesar " . number_format($nilaiData_gr2[3], 3) . "%. ";
                if ($nilaiData_gr[5] > $nilaiData_gr2[5]) {
                    $max_p_gr  = "Capaian gini rasio " . $xname . " pada " . $tahun_gr[5] . " berada dibawah capaian nasional. Gini rasio nasional pada " . $tahun_gr[5] . " adalah sebesar " . number_format($nilaiData_gr[5], 3) . "% ";
                } else {
                    $max_p_gr  = "Capaian gini rasio " . $xname . " pada " . $tahun_gr[5] . " berada diatas capaian nasional. Gini rasio nasional pada " . $tahun_gr[5] . " adalah sebesar " . number_format($nilaiData_gr[5], 3) . "% ";
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
                $max_n_ahh    = "Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " menurun dibandingkan dengan tahun " . $tahun_ahh[4] . ". Pada tahun " . $tahun_ahh[5] . " Angka Harapan Hidup " . $xname . " adalah sebesar " . number_format(end($nilaiData_ahh2), 2) . " tahun sedangkan pada tahun " . $tahun_ahh[4] . " Angka Harapan Hidup tercatat sebesar " . number_format($nilaiData_ahh2[4], 2) . " tahun. ";
                if ($nilaiData_ahh[5] > $nilaiData_ahh2[5]) {
                    $max_p_ahh  = "Capaian Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun " . $tahun_ahh[5] . " adalah sebesar " . number_format(end($nilaiData_ahh), 2) . " tahun ";
                } else {
                    $max_p_ahh  = "Capaian Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " berada diatas capaian nasional. Angka Harapan Hidup nasional pada tahun " . $tahun_ahh[5] . " adalah sebesar " . number_format(end($nilaiData_ahh), 2) . " tahun ";
                }
            } else {
                $max_n_ahh    = "Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " meningkat dibandingkan dengan tahun " . $tahun_ahh[4] . ". Pada tahun " . $tahun_ahh[5] . " Angka Harapan Hidup " . $xname . " adalah sebesar " . number_format(end($nilaiData_ahh2), 2) . " tahun sedangkan pada tahun " . $tahun_ahh[4] . " Angka Harapan Hidup tercatat sebesar " . number_format($nilaiData_ahh2[4], 2) . " tahun.";
                if ($nilaiData_ahh[5] > $nilaiData_ahh2[5]) {
                    $max_p_ahh  = "Capaian Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun " . $tahun_ahh[5] . " adalah sebesar " . number_format(end($nilaiData_ahh), 2) . " tahun ";
                } else {
                    $max_p_ahh  = "Capaian Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " berada diatas capaian nasional. Angka Harapan Hidup nasional pada tahun " . $tahun_ahh[5] . " adalah sebesar " . number_format(end($nilaiData_ahh), 2) . " tahun ";
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
                $max_n_rls    = "Rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " menurun dibandingkan dengan tahun " . $tahun_rls[4] . ". Pada tahun " . $tahun_rls[5] . " Rata-rata lama sekolah " . $xname . " " . number_format(end($nilaiData_rls2), 2) . " Tahun, sedangkan pada tahun " . $tahun_rls[4] . " rata-rata lama sekolah tercatat sebesar " . number_format($nilaiData_rls2[4], 2) . " tahun. ";
                if ($nilaiData_rls[5] > $nilaiData_rls2[5]) {
                    $max_p_rls  = "Capaian rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " berada dibawah capaian nasional. Rata-rata lama sekolah nasional pada tahun " . $tahun_rls[5] . " sebesar " . number_format($nilaiData_rls[5], 2) . " tahun. ";
                } else {
                    $max_p_rls  = "Capaian rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " berada diatas capaian nasional. Rata-rata lama sekolah nasional pada tahun " . $tahun_rls[5] . " sebesar " . number_format($nilaiData_rls[5], 2) . " tahun. ";
                }
            } else {
                $max_n_rls    = "Rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " meningkat dibandingkan dengan tahun " . $tahun_rls[4] . ". Pada tahun " . $tahun_rls[5] . " Rata-rata lama sekolah " . $xname . " " . number_format(end($nilaiData_rls2), 2) . " Tahun, sedangkan pada tahun " . $tahun_rls[4] . " rata-rata lama sekolah tercatat sebesar " . number_format($nilaiData_rls2[4], 2) . " tahun. ";
                if ($nilaiData_rls[5] > $nilaiData_rls2[5]) {
                    $max_p_rls  = "Capaian rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " berada dibawah capaian nasional. Rata-rata lama sekolah nasional pada tahun " . $tahun_rls[5] . " sebesar " . number_format($nilaiData_rls[5], 2) . " tahun. ";
                } else {
                    $max_p_rls  = "Capaian rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " berada diatas capaian nasional. Rata-rata lama sekolah nasional pada tahun " . $tahun_rls[5] . " sebesar " . number_format($nilaiData_rls[5], 2) . " tahun. ";
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
                $max_n_hls    = "Harapan Lama Sekolah " . $xname . " pada tahun " . $tahun_hls[5] . " menurun dibandingkan dengan tahun " . $tahun_hls[4] . ". Pada tahun " . $tahun_hls[5] . " Harapan Lama Sekolah " . $xname . " adalah sebesar " . number_format(end($nilaiData_hls2), 2) . " Tahun, sedangkan pada tahun " . $tahun_hls[4] . " rata-rata lama sekolah tercatat sebesar " . number_format($nilaiData_hls2[4], 2) . " tahun. ";
                if ($nilaiData_hls[5] > $nilaiData_hls2[5]) {
                    $max_p_hls  = "Capaian Harapan Lama Sekolah " . $xname . " pada tahun " . $tahun_hls[5] . " berada dibawah capaian nasional. Harapan Lama Sekolah nasional pada tahun " . $tahun_hls[5] . " adalah sebesar " . number_format(end($nilaiData_hls), 2) . " tahun. ";
                } else {
                    $max_p_hls  = "Capaian Harapan Lama Sekolah " . $xname . " pada tahun " . $tahun_hls[5] . " berada diatas capaian nasional. Harapan Lama Sekolah nasional pada tahun " . $tahun_hls[5] . " adalah sebesar " . number_format(end($nilaiData_hls), 2) . " tahun. ";
                }
            } else {
                $max_n_hls    = "Harapan Lama Sekolah " . $xname . " pada tahun " . $tahun_hls[5] . " meningkat dibandingkan dengan tahun " . $tahun_hls[4] . ". Pada tahun " . $tahun_hls[5] . " Harapan Lama Sekolah " . $xname . " adalah sebesar " . number_format(end($nilaiData_hls2), 2) . " Tahun, sedangkan pada tahun " . $tahun_hls[4] . " rata-rata lama sekolah tercatat sebesar " . number_format($nilaiData_hls2[4], 2) . " tahun. ";
                if ($nilaiData_hls[5] > $nilaiData_hls2[5]) {
                    $max_p_hls  = "Capaian Harapan Lama Sekolah " . $xname . " pada tahun " . $tahun_hls[5] . " berada dibawah capaian nasional. Harapan Lama Sekolah nasional pada tahun " . $tahun_hls[5] . " adalah sebesar " . number_format(end($nilaiData_hls), 2) . " tahun. ";
                } else {
                    $max_p_hls  = "Capaian Harapan Lama Sekolah " . $xname . " pada tahun " . $tahun_hls[5] . " berada diatas capaian nasional. Harapan Lama Sekolah nasional pada tahun " . $tahun_hls[5] . " adalah sebesar " . number_format(end($nilaiData_hls), 2) . " tahun. ";
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
                $nilaiData_ppk1[] = (float)$row_ppk->nilai / 1000000;
            }
            $datay_ppk = $nilaiData_ppk1;
            $tahun_ppk = $tahun_ppk;
            $sql_ppk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppk2 = $this->db->query($sql_ppk2);
            foreach ($list_ppk2->result() as $row_ppk2) {
                $tahun_ppk2[]     = $row_ppk2->tahun;
                $nilaiData_ppk2[] = (float)$row_ppk2->nilai;
                $nilaiData_ppk22[] = (float)$row_ppk2->nilai / 1000000;
                $sumber_ppk       = $row_ppk->sumber;
                $periode_ppk_id[] = $row_ppk2->id_periode;
                $tahun_ppk21[]    = $bulan[$row_ppk2->periode] . "-" . $row_ppk2->tahun;
            }
            $datay_ppk2 = $nilaiData_ppk22;
            $tahun_ppk2 = $tahun_ppk2;
            $periode_ppk_max = max($periode_ppk_id);
            $periode_ppk_tahun = max($tahun_ppk2) . " Antar Provinsi";
            if ($nilaiData_ppk2[4] > $nilaiData_ppk2[5]) {
                $max_n_ppk    = "Pengeluaran Perkapita " . $xname . " pada tahun " . $tahun_ppk[5] . " menurun dibandingkan dengan tahun " . $tahun_ppk[4] . ". Pada tahun " . $tahun_ppk[5] . " Pengeluaran Perkapita " . $xname . " adalah sebesar Rp " . number_format(end($nilaiData_ppk2)) . " sedangkan pada tahun " . $tahun_ppk[4] . " Pengeluaran Perkapita tercatat sebesar Rp " . number_format($nilaiData_ppk2[4]) . ". ";
                if ($nilaiData_ppk[5] > $nilaiData_ppk2[5]) {
                    $max_p_ppk  = "Capaian Pengeluaran Perkapita " . $xname . " pada tahun " . $tahun_ppk[5] . " berada dibawah capaian nasional. Pengeluaran Perkapita nasional pada tahun " . $tahun_ppk[5] . " adalah sebesar Rp " . number_format(end($nilaiData_ppk)) . " ";
                } else {
                    $max_p_ppk  = "Capaian Pengeluaran Perkapita " . $xname . " pada tahun " . $tahun_ppk[5] . " berada dibawah capaian nasional. Pengeluaran Perkapita nasional pada tahun " . $tahun_ppk[5] . " adalah sebesar Rp " . number_format(end($nilaiData_ppk)) . " ";
                }
            } else {
                $max_n_ppk    = "Pengeluaran Perkapita " . $xname . " pada tahun " . $tahun_ppk[5] . " meningkat dibandingkan dengan tahun " . $tahun_ppk[4] . ". Pada tahun " . $tahun_ppk[5] . " Pengeluaran Perkapita " . $xname . " adalah sebesar Rp " . number_format(end($nilaiData_ppk2)) . " sedangkan pada tahun " . $tahun_ppk[4] . " Pengeluaran Perkapita tercatat sebesar Rp " . number_format($nilaiData_ppk2[4]) . ". ";
                if ($nilaiData_ppk[5] > $nilaiData_ppk2[5]) {
                    $max_p_ppk  = "Capaian Pengeluaran Perkapita " . $xname . " pada tahun " . $tahun_ppk[5] . " berada dibawah capaian nasional. Pengeluaran Perkapita nasional pada tahun " . $tahun_ppk[5] . " adalah sebesar Rp " . number_format(end($nilaiData_ppk)) . " ";
                } else {
                    $max_p_ppk  = "Capaian Pengeluaran Perkapita " . $xname . " pada tahun " . $tahun_ppk[5] . " berada dibawah capaian nasional. Pengeluaran Perkapita nasional pada tahun " . $tahun_ppk[5] . " adalah sebesar Rp " . number_format(end($nilaiData_ppk)) . " ";
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
            //  print_r($perbandingan_ppk);exit();
            $list_ppk_per = $this->db->query($perbandingan_ppk);
            foreach ($list_ppk_per->result() as $row_ppk_per) {
                $label_ppk[]     = $row_ppk_per->label;
                $nilai_ppk_per[] = $row_ppk_per->nilai / 1000000;
            }
            $label_data_ppk     = $label_ppk;
            $nilai_data_ppk_per = $nilai_ppk_per;

            //Tingkat Kemiskinan
            $sql_tk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tk = $this->db->query($sql_tk);
            foreach ($list_tk->result() as $row_tk) {
                $tahun_tk[]    = $bulan[$row_tk->periode] . "-" . $row_tk->tahun;
                $nilaiData_tk[] = (float)$row_tk->nilai;
            }
            $datay_tk = $nilaiData_tk;
            $tahun_tk = $tahun_tk;
            $sql_tk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tk2 = $this->db->query($sql_tk2);
            foreach ($list_tk2->result() as $row_tk2) {
                $tahun_tk2[]   = $row_tk2->tahun;
                $nilaiData_tk2[] = (float)$row_tk2->nilai;
                $sumber_tk       = $row_tk2->sumber;
                $periode_tk_id[] = $row_tk2->id_periode;
                $tahun_tk21[]    = $bulan[$row_tk2->periode] . "-" . $row_tk2->tahun;
            }
            $datay_tk2 = $nilaiData_tk2;
            $tahun_tk2 = $tahun_tk2;
            $periode_tk_max = max($periode_tk_id);
            $periode_tk_tahun = max($tahun_tk21) . " Antar Provinsi";

            if ($nilaiData_tk2[3] > $nilaiData_tk2[5]) {
                $max_n_tk    = "Tingkat Kemiskinan " . $xname . " pada " . $tahun_tk[5] . " menurun dibandingkan dengan " . $tahun_tk[3] . ". Pada " . $tahun_tk[5] . " Angka tingkat Kemiskinan " . $xname . " adalah sebesar " . number_format(end($nilaiData_tk2), 2) . "%, sedangkan pada " . $tahun_tk[3] . " Angka tingkat Kemiskinan tercatat sebesar " . number_format($nilaiData_tk2[3], 2) . "%. ";
                if ($nilaiData_tk[5] > $nilaiData_tk2[5]) {
                    $max_p_tk  = "Capaian Angka tingkat Kemiskinan " . $xname . " pada " . $tahun_tk[5] . " berada dibawah capaian nasional. Angka tingkat Kemiskinan nasional pada " . $tahun_tk[5] . " adalah sebesar " . number_format(end($nilaiData_tk), 2) . "% ";
                } else {
                    $max_p_tk  = "Capaian Angka tingkat Kemiskinan " . $xname . " pada " . $tahun_tk[5] . " berada diatas capaian nasional. Angka tingkat Kemiskinan nasional pada " . $tahun_tk[5] . " adalah sebesar " . number_format(end($nilaiData_tk), 2) . "% ";
                }
            } else {
                $max_n_tk    = "Tingkat Kemiskinan " . $xname . " pada " . $tahun_tk[5] . " meningkat dibandingkan dengan " . $tahun_tk[3] . ". Pada " . $tahun_tk[5] . " Angka tingkat Kemiskinan " . $xname . " adalah sebesar " . number_format(end($nilaiData_tk2), 2) . "%, sedangkan pada " . $tahun_tk[3] . " Angka tingkat Kemiskinan tercatat sebesar " . number_format($nilaiData_tk2[3], 2) . "%. ";
                if ($nilaiData_tk[5] > $nilaiData_tk2[5]) {
                    $max_p_tk  = "Capaian Angka tingkat Kemiskinan " . $xname . " pada " . $tahun_tk[5] . " berada dibawah capaian nasional. Angka tingkat Kemiskinan nasional pada " . $tahun_tk[5] . " adalah sebesar " . number_format(end($nilaiData_tk), 2) . "% ";
                } else {
                    $max_p_tk  = "Capaian Angka tingkat Kemiskinan " . $xname . " pada " . $tahun_tk[5] . " berada diatas capaian nasional. Angka tingkat Kemiskinan nasional pada " . $tahun_tk[5] . " adalah sebesar " . number_format(end($nilaiData_tk), 2) . "% ";
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
                $nilaiData_idk[] = (float)$row_idk->nilai;
            }
            $datay_idk = $nilaiData_idk;
            $tahun_idk = $tahun_idk;
            $sql_idk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_idk2 = $this->db->query($sql_idk2);
            foreach ($list_idk2->result() as $row_idk2) {
                $tahun_idk2[]     = $row_idk2->tahun;
                $nilaiData_idk2[] = (float)$row_idk2->nilai;
                $sumber_idk       = $row_idk2->sumber;
                $periode_idk_id[]   = $row_idk2->id_periode;
                $tahun_idk21[]    = $bulan[$row_idk2->periode] . "-" . $row_idk2->tahun;
            }
            $datay_idk2 = $nilaiData_idk2;
            $tahun_idk2 = $tahun_idk2;
            $periode_ikk_max = max($periode_idk_id);
            $periode_ikk_tahun = max($tahun_idk21) . " Antar Provinsi";
            if ($nilaiData_idk2[3] > $nilaiData_idk2[5]) {
                $max_n_ikk    = "Indeks Kedalaman Kemiskinan " . $xname . " pada " . $tahun_idk[5] . " menurun dibandingkan dengan " . $tahun_idk[3] . ". Pada " . $tahun_idk[5] . " Indeks Kedalaman Kemiskinan " . $xname . " adalah sebesar " . number_format(end($nilaiData_idk2), 2) . "%, sedangkan pada " . $tahun_idk[3] . " Indeks Kedalaman Kemiskinan tercatat sebesar " . number_format($nilaiData_idk2[3], 2) . "%. ";
                if ($nilaiData_idk[5] > $nilaiData_idk2[5]) {
                    $max_p_ikk  = "Capaian Indeks Kedalaman Kemiskinan " . $xname . " pada " . $tahun_idk[5] . " berada dibawah capaian nasional. Indeks Kedalaman Kemiskinan nasional pada " . $tahun_idk[5] . " adalah sebesar " . number_format(end($nilaiData_idk), 2) . "% ";
                } else {
                    $max_p_ikk  = "Capaian Indeks Kedalaman Kemiskinan " . $xname . " pada " . $tahun_idk[5] . " berada diatas capaian nasional. Indeks Kedalaman Kemiskinan nasional pada " . $tahun_idk[5] . " adalah sebesar " . number_format(end($nilaiData_idk), 2) . "% ";
                }
            } else {
                $max_n_ikk    = "Indeks Kedalaman Kemiskinan " . $xname . " pada " . $tahun_idk[5] . " meningkat dibandingkan dengan " . $tahun_idk[3] . ". Pada " . $tahun_idk[5] . " Indeks Kedalaman Kemiskinan " . $xname . " adalah sebesar " . number_format(end($nilaiData_idk2), 2) . "%, sedangkan pada " . $tahun_idk[3] . " Indeks Kedalaman Kemiskinan tercatat sebesar " . number_format($nilaiData_idk2[3], 2) . "%. ";
                if ($nilaiData_idk[5] > $nilaiData_idk2[5]) {
                    $max_p_ikk  = "Capaian Indeks Kedalaman Kemiskinan " . $xname . " pada " . $tahun_idk[5] . " berada dibawah capaian nasional. Indeks Kedalaman Kemiskinan nasional pada " . $tahun_idk[5] . " adalah sebesar " . number_format(end($nilaiData_idk), 2) . "% ";
                } else {
                    $max_p_ikk  = "Capaian Indeks Kedalaman Kemiskinan " . $xname . " pada " . $tahun_idk[5] . " berada diatas capaian nasional. Indeks Kedalaman Kemiskinan nasional pada " . $tahun_idk[5] . " adalah sebesar " . number_format(end($nilaiData_idk), 2) . "% ";
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
                $nilai_ikk_per[] = $row_ikk_per->nilai;
            }
            $label_data_ikk     = $label_ikk;
            $nilai_data_ikk_per = $nilai_ikk_per;

            //jumlah Penduduk Miskin
            $sql_jpk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jpk = $this->db->query($sql_jpk);
            foreach ($list_jpk->result() as $row_jpk) {
                //$tahun_jpk[]   = $row_jpk->tahun;
                $tahun_jpk[]    = $bulan[$row_jpk->periode] . "-" . $row_jpk->tahun;
                $nilaiData_jpk[] = (float)$row_jpk->nilai;
                $nilaiData_jpk1[] = (float)$row_jpk->nilai;
            }
            $datay_jpk = $nilaiData_jpk;
            $tahun_jpk = $tahun_jpk;

            $sql_jpk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jpk2 = $this->db->query($sql_jpk2);
            foreach ($list_jpk2->result() as $row_jpk2) {
                $tahun_jpk2[]   = $row_jpk2->tahun;
                $nilaiData_jpk2[] = (float)$row_jpk2->nilai;
                $nilaiData_jpk22[] = (float)$row_jpk2->nilai;
                $sumber_jpk       = $row_jpk2->sumber;
                $periode_jpk_id[]   = $row_jpk2->id_periode;
                $tahun_jpk21[]    = $bulan[$row_jpk2->periode] . "-" . $row_jpk2->tahun;
            }
            $datay_jpk2 = $nilaiData_jpk2;
            $tahun_jpk2 = $tahun_jpk2;
            $periode_jpk_max = max($periode_jpk_id);
            $periode_jpk_tahun = max($tahun_jpk21) . " Antar Provinsi";
            if ($nilaiData_jpk22[3] > $nilaiData_jpk22[5]) {
                //                        $rt_jpk=$nilaiData_jpk22[3]-$nilaiData_jpk22[5]; $rt_jpk2=$rt_jpk/$nilaiData_jpk22[5]; $rt_jpk3=$rt_jpk2*100;$rt_jpk33=number_format($rt_jpk3,2);
                $rt_jpk = $nilaiData_jpk22[5] - $nilaiData_jpk22[3];
                $rt_jpkk = abs($nilaiData_jpk22[5] - $nilaiData_jpk22[3]);
                $rt_jpk2 = $rt_jpk / $nilaiData_jpk22[3];
                $rt_jpk3 = abs($rt_jpk2 * 100);
                //$t='berkurang';
                $rt_jpk33 = number_format($rt_jpk3, 2);
                $max_n_jpk    = "Jumlah Penduduk Miskin  " . $xname . " pada  " . $tahun_jpk[5] . " sebanyak " . number_format($nilaiData_jpk22[5], 0) . " orang sedangkan jumlah penduduk miskin pada " . $tahun_jpk[3] . " sebanyak " . number_format($nilaiData_jpk22[3], 2) . " orang. Selama periode " . $tahun_jpk[3] . " - " . $tahun_jpk[5] . " jumlah penduduk miskin di provinsi " . $xname . " berkurang sebanyak " . number_format($rt_jpkk, 0) . " orang atau sebesar " . $rt_jpk33 . "%.";
            } else {
                $rt_jpk  = $nilaiData_jpk22[5] - $nilaiData_jpk22[3];
                $rt_jpk2 = $rt_jpk / $nilaiData_jpk22[3];
                $rt_jpk3 = $rt_jpk2 * 100;
                $rt_jpk33 = number_format($rt_jpk3, 2);
                //$t='naik';
                $max_n_jpk    = "Jumlah Penduduk Miskin  " . $xname . " pada " . $tahun_jpk[5] . " sebanyak " . number_format($nilaiData_jpk22[5], 0) . " orang sedangka jumlah penduduk miskin pada " . $tahun_jpk[3] . " sebanyak " . number_format($nilaiData_jpk22[3], 2) . " orang. Selama periode " . $tahun_jpk[3] . " - " . $tahun_jpk[5] . " jumlah penduduk miskin di provinsi " . $xname . " bertambah sebanyak " . number_format($rt_jpk, 0) . " orang atau sebesar " . $rt_jpk33 . "%.";
            }
            //                   
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





            //Indeks Pembangunan Manusia
            $graph_ipm = new Graph(500, 230);
            $graph_ipm->SetScale("textlin");
            $theme_class_ipm = new UniversalTheme;
            $graph_ipm->SetTheme($theme_class_ipm);
            $graph_ipm->SetMargin(40, 20, 33, 60);
            //$graph_ipm->title->Set('Perkembangan Indeks Pembangunan Manusia');
            $graph_ipm->SetBox(false);
            $graph_ipm->yaxis->HideZeroLabel();
            $graph_ipm->yaxis->HideLine(false);
            $graph_ipm->yaxis->HideTicks(false, false);
            $graph_ipm->xaxis->SetTickLabels($tahun_ipm);
            $graph_ipm->ygrid->SetFill(false);
            //$graph_ipm->SetFormat('%.3f');

            $graph_bar_ipm = new Graph(600, 230);
            $graph_bar_ipm->img->SetMargin(80, 20, 20, 100);
            $graph_bar_ipm->SetScale("textlin");
            $graph_bar_ipm->SetMarginColor("lightblue:1.1");
            $graph_bar_ipm->SetShadow();
            $graph_bar_ipm->title->SetMargin(8);
            $graph_bar_ipm->title->SetColor("darkred");
            $graph_bar_ipm->ygrid->SetFill(false);
            $graph_bar_ipm->xaxis->SetTickLabels($label_data_ipm);
            $graph_bar_ipm->yaxis->HideLine(false);
            $graph_bar_ipm->yaxis->HideTicks(false, false);
            $graph_bar_ipm->xaxis->SetLabelAngle(90);


            //Gini Rasio
            $graph_gr = new Graph(500, 230);
            $graph_gr->SetScale("textlin");
            $theme_class_gr = new UniversalTheme;
            $graph_gr->SetTheme($theme_class_gr);
            $graph_gr->SetMargin(40, 20, 33, 58);
            //$graph_gr->title->Set('Perkembangan Gini Rasio');
            $graph_gr->SetBox(false);
            $graph_gr->yaxis->HideZeroLabel();
            $graph_gr->yaxis->HideLine(false);
            $graph_gr->yaxis->HideTicks(false, false);
            $graph_gr->xaxis->SetTickLabels($tahun_gr);
            $graph_gr->ygrid->SetFill(false);

            $graph_bar_gr = new Graph(600, 230);
            $graph_bar_gr->img->SetMargin(40, 20, 20, 100);
            $graph_bar_gr->SetScale("textlin");
            $graph_bar_gr->SetMarginColor("lightblue:1.1");
            $graph_bar_gr->SetShadow();
            $graph_bar_gr->title->SetMargin(8);
            $graph_bar_gr->title->SetColor("darkred");
            $graph_bar_gr->ygrid->SetFill(false);
            $graph_bar_gr->xaxis->SetTickLabels($label_data_gr);
            $graph_bar_gr->yaxis->HideLine(false);
            $graph_bar_gr->yaxis->HideTicks(false, false);
            $graph_bar_gr->xaxis->SetLabelAngle(90);

            //Angka Harapan Hidup
            $graph_ahh = new Graph(500, 230);
            $graph_ahh->SetScale("textlin");
            $theme_class_ahh = new UniversalTheme;
            $graph_ahh->SetTheme($theme_class_ahh);
            $graph_ahh->SetMargin(40, 20, 33, 58);
            //$graph_ahh->title->Set('Perkembangan Angka Harapan Hidup');
            $graph_ahh->SetBox(false);
            $graph_ahh->yaxis->HideZeroLabel();
            $graph_ahh->yaxis->HideLine(false);
            $graph_ahh->yaxis->HideTicks(false, false);
            $graph_ahh->xaxis->SetTickLabels($tahun_ahh);
            $graph_ahh->ygrid->SetFill(false);

            $graph_bar_ahh = new Graph(600, 230);
            $graph_bar_ahh->img->SetMargin(40, 20, 20, 100);
            $graph_bar_ahh->SetScale("textlin");
            $graph_bar_ahh->SetMarginColor("lightblue:1.1");
            $graph_bar_ahh->SetShadow();
            $graph_bar_ahh->title->SetMargin(8);
            $graph_bar_ahh->title->SetColor("darkred");
            $graph_bar_ahh->ygrid->SetFill(false);
            $graph_bar_ahh->xaxis->SetTickLabels($label_data_ahh);
            $graph_bar_ahh->yaxis->HideLine(false);
            $graph_bar_ahh->yaxis->HideTicks(false, false);
            $graph_bar_ahh->xaxis->SetLabelAngle(90);

            //Rata-rata Lama Sekolah
            $graph_rls = new Graph(500, 230);
            $graph_rls->SetScale("textlin");
            $theme_class_rls = new UniversalTheme;
            $graph_rls->SetTheme($theme_class_rls);
            $graph_rls->SetMargin(40, 20, 33, 58);
            //$graph_rls->title->Set('Perkembangan Rata-rata Lama Sekolah');
            $graph_rls->SetBox(false);
            $graph_rls->yaxis->HideZeroLabel();
            $graph_rls->yaxis->HideLine(false);
            $graph_rls->yaxis->HideTicks(false, false);
            $graph_rls->xaxis->SetTickLabels($tahun_rls);
            $graph_rls->ygrid->SetFill(false);

            $graph_bar_rls = new Graph(600, 230);
            $graph_bar_rls->img->SetMargin(40, 20, 20, 100);
            $graph_bar_rls->SetScale("textlin");
            $graph_bar_rls->SetMarginColor("lightblue:1.1");
            $graph_bar_rls->SetShadow();
            $graph_bar_rls->title->SetMargin(8);
            $graph_bar_rls->title->SetColor("darkred");
            $graph_bar_rls->ygrid->SetFill(false);
            $graph_bar_rls->xaxis->SetTickLabels($label_data_rls);
            $graph_bar_rls->yaxis->HideLine(false);
            $graph_bar_rls->yaxis->HideTicks(false, false);
            $graph_bar_rls->xaxis->SetLabelAngle(90);

            //harapan Lama Sekolah
            $graph_hls = new Graph(500, 230);
            $graph_hls->SetScale("textlin");
            $theme_class_hls = new UniversalTheme;
            $graph_hls->SetTheme($theme_class_hls);
            $graph_hls->SetMargin(40, 20, 33, 58);
            //$graph_hls->title->Set('Perkembangan harapan Lama Sekolah');
            $graph_hls->SetBox(false);
            $graph_hls->yaxis->HideZeroLabel();
            $graph_hls->yaxis->HideLine(false);
            $graph_hls->yaxis->HideTicks(false, false);
            $graph_hls->xaxis->SetTickLabels($tahun_hls);
            $graph_hls->ygrid->SetFill(false);

            $graph_bar_hls = new Graph(600, 230);
            $graph_bar_hls->img->SetMargin(80, 20, 20, 100);
            $graph_bar_hls->SetScale("textlin");
            $graph_bar_hls->SetMarginColor("lightblue:1.1");
            $graph_bar_hls->SetShadow();
            $graph_bar_hls->title->SetMargin(8);
            $graph_bar_hls->title->SetColor("darkred");
            $graph_bar_hls->ygrid->SetFill(false);
            $graph_bar_hls->xaxis->SetTickLabels($label_data_hls);
            $graph_bar_hls->yaxis->HideLine(false);
            $graph_bar_hls->yaxis->HideTicks(false, false);
            $graph_bar_hls->xaxis->SetLabelAngle(90);

            //pengeluaran per kapita
            $graph_ppk = new Graph(500, 230);
            $graph_ppk->SetScale("textlin");
            $theme_class_ppk = new UniversalTheme;
            $graph_ppk->SetTheme($theme_class_ppk);
            $graph_ppk->SetMargin(40, 20, 33, 58);
            //$graph_ppk->title->Set('Perkembangan Pengeluaran Per Kapita');
            $graph_ppk->SetBox(false);
            $graph_ppk->yaxis->HideZeroLabel();
            $graph_ppk->yaxis->HideLine(false);
            $graph_ppk->yaxis->HideTicks(false, false);
            $graph_ppk->xaxis->SetTickLabels($tahun_ppk);
            $graph_ppk->ygrid->SetFill(false);

            $graph_bar_ppk = new Graph(600, 230);
            $graph_bar_ppk->img->SetMargin(40, 20, 20, 100);
            $graph_bar_ppk->SetScale("textlin");
            $graph_bar_ppk->SetMarginColor("lightblue:1.1");
            $graph_bar_ppk->SetShadow();
            $graph_bar_ppk->title->SetMargin(8);
            $graph_bar_ppk->title->SetColor("darkred");
            $graph_bar_ppk->ygrid->SetFill(false);
            $graph_bar_ppk->xaxis->SetTickLabels($label_data_ppk);
            $graph_bar_ppk->yaxis->HideLine(false);
            $graph_bar_ppk->yaxis->HideTicks(false, false);
            $graph_bar_ppk->xaxis->SetLabelAngle(90);

            //tingkat kemiskinan
            $graph_tk = new Graph(500, 230);
            $graph_tk->SetScale("textlin");
            $theme_class_tk = new UniversalTheme;
            $graph_tk->SetTheme($theme_class_tk);
            $graph_tk->SetMargin(40, 20, 33, 58);
            //$graph_tk->title->Set('Perkembangan Tingkat Kemiskinan');
            $graph_tk->SetBox(false);
            $graph_tk->yaxis->HideZeroLabel();
            $graph_tk->yaxis->HideLine(false);
            $graph_tk->yaxis->HideTicks(false, false);
            $graph_tk->xaxis->SetTickLabels($tahun_tk);
            $graph_tk->ygrid->SetFill(false);

            $graph_bar_tk = new Graph(600, 230);
            $graph_bar_tk->img->SetMargin(40, 20, 20, 100);
            $graph_bar_tk->SetScale("textlin");
            $graph_bar_tk->SetMarginColor("lightblue:1.1");
            $graph_bar_tk->SetShadow();
            $graph_bar_tk->title->SetMargin(8);
            $graph_bar_tk->title->SetColor("darkred");
            $graph_bar_tk->ygrid->SetFill(false);
            $graph_bar_tk->xaxis->SetTickLabels($label_data_tk);
            $graph_bar_tk->yaxis->HideLine(false);
            $graph_bar_tk->yaxis->HideTicks(false, false);
            $graph_bar_tk->xaxis->SetLabelAngle(90);

            //Indeks Kedalaman Kemiskinan
            $graph_ikk = new Graph(500, 230);
            $graph_ikk->SetScale("textlin");
            $theme_class_ikk = new UniversalTheme;
            $graph_ikk->SetTheme($theme_class_ikk);
            $graph_ikk->SetMargin(40, 20, 33, 58);
            //$graph_ikk->title->Set('Perkembangan Indeks Kedalaman Kemiskinan');
            $graph_ikk->SetBox(false);
            $graph_ikk->yaxis->HideZeroLabel();
            $graph_ikk->yaxis->HideLine(false);
            $graph_ikk->yaxis->HideTicks(false, false);
            $graph_ikk->xaxis->SetTickLabels($tahun_idk);
            $graph_ikk->ygrid->SetFill(false);

            $graph_bar_ikk = new Graph(600, 230);
            $graph_bar_ikk->img->SetMargin(40, 20, 20, 100);
            $graph_bar_ikk->SetScale("textlin");
            $graph_bar_ikk->SetMarginColor("lightblue:1.1");
            $graph_bar_ikk->SetShadow();
            $graph_bar_ikk->title->SetMargin(8);
            $graph_bar_ikk->title->SetColor("darkred");
            $graph_bar_ikk->ygrid->SetFill(false);
            $graph_bar_ikk->xaxis->SetTickLabels($label_data_ikk);
            $graph_bar_ikk->yaxis->HideLine(false);
            $graph_bar_ikk->yaxis->HideTicks(false, false);
            $graph_bar_ikk->xaxis->SetLabelAngle(90);

            //Jumlah Penduduk Miskin                
            $graph_jpk = new Graph(650, 250);
            $graph_jpk->SetScale("textlin");
            $graph_jpk->SetY2Scale("lin", 0, 90);
            $graph_jpk->SetY2OrderBack(false);
            $theme_class_jpk = new UniversalTheme;
            $graph_jpk->SetTheme($theme_class_jpk);
            $graph_jpk->SetMargin(120, 60, 33, 60);
            $graph_jpk->ygrid->SetFill(false);
            $graph_jpk->xaxis->SetTickLabels($tahun_jpk);
            $graph_jpk->yaxis->HideLine(false);
            $graph_jpk->yaxis->HideTicks(false, false);
            $graph_jpk->title->Set("");

            $graph_bar_jpk = new Graph(600, 230);
            $graph_bar_jpk->img->SetMargin(80, 20, 20, 100);
            $graph_bar_jpk->SetScale("textlin");
            $graph_bar_jpk->SetMarginColor("lightblue:1.1");
            $graph_bar_jpk->SetShadow();
            $graph_bar_jpk->title->SetMargin(8);
            $graph_bar_jpk->title->SetColor("darkred");
            $graph_bar_jpk->ygrid->SetFill(false);
            $graph_bar_jpk->xaxis->SetTickLabels($label_data_jpk);
            $graph_bar_jpk->yaxis->HideLine(false);
            $graph_bar_jpk->yaxis->HideTicks(false, false);
            $graph_bar_jpk->xaxis->SetLabelAngle(90);
        } elseif ($provinsi != '' & $kabupaten != '') {
            $sql_pro1 = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`id`='" . $pro . "' ";
            $list_data1 = $this->db->query($sql_pro1);
            foreach ($list_data1->result() as $Lis_pro1) {
                $id_pro = $Lis_pro1->id;
                $xname  = $Lis_pro1->nama_provinsi;
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
                $judul = $Lis_pro->nama_kabupaten . "<br/>" . $xname;
            }
            $logopro      = "KABUPATEN_" . $kab . ".png";

            //Pertumbuhan Ekonomi
            $sql_ppe = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppe = $this->db->query($sql_ppe);
            foreach ($list_ppe->result() as $row_ppe) {
                //$tahun_ppe[]    = $bulan[$row_ppe->periode]."-".$row_ppe->tahun;
                $nilaiData_ppe[] = (float)$row_ppe->nilai;
                $nilai_ppe_n[$row_ppe->tahun] = (float)$row_ppe->nilai;
                $tahun_ppe[]    = $row_ppe->tahun;
                $idperiode_ppe[] = $row_ppe->id_periode;
            }
            $datay_ppe = $nilaiData_ppe;
            $tahun_ppe = $tahun_ppe;
            $periode_kab_ppe_max = max($idperiode_ppe);

            $sql_ppe2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppe2 = $this->db->query($sql_ppe2);
            foreach ($list_ppe2->result() as $row_ppe2) {
                $tahun_ppe2[]   = $row_ppe2->tahun;
                $nilaiData_ppe2[] = (float)$row_ppe2->nilai;
                //$nilaiData_ppe22[] = number_format((float)$row_ppe2->nilai,2);
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
                                                                    WHERE id_indikator='1' AND wilayah='1000' group by id_periode
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
                    $nilaiData_kppe3 = '-';
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
                $max_pe    = "Pertumbuhan Ekonomi " . $xnameKab . " pada tahun " . $tahun_kab_ppe_max . " menurun dibandingkan dengan tahun " . $tahun_kab_ppe_ke2 . ". Pada " . $tahun_kab_ppe_max . " pertumbuhan ekonomi " . $xnameKab . " adalah sebesar " . $nilaiData_ppe33[$tahun_kab_ppe_max] . "%, sedangkan pada tahun " . $tahun_kab_ppe_ke2 . " pertumbuhan tercatat sebesar " . $nilaiData_ppe33[$tahun_kab_ppe_ke2] . "%. ";
                if ($nilai_ppe2_pro[$tahun_kab_ppe_max] > $nilaiData_ppe33[$tahun_kab_ppe_max]) {
                    $max_pe_p  = " Pertumbuhan ekonomi " . $xnameKab . " pada tahun " . $tahun_kab_ppe_max . " berada dibawah capaian " . $xname . ". Pertumbuhan ekonomi " . $xname . " pada tahun " . $tahun_kab_ppe_max . " adalah sebesar " . $nilai_ppe2_pro[$tahun_kab_ppe_max] . "%. ";
                } else {
                    $max_pe_p  = " Pertumbuhan ekonomi " . $xnameKab . " pada tahun " . $tahun_kab_ppe_max . " berada diatas capaian " . $xname . ". Pertumbuhan ekonomi " . $xname . " pada tahun " . $tahun_kab_ppe_max . " adalah sebesar " . $nilai_ppe2_pro[$tahun_kab_ppe_max] . "%. ";
                }
            } else {
                $max_pe    = "Pertumbuhan Ekonomi " . $xnameKab . " pada tahun " . $tahun_kab_ppe_max . " meningkat dibandingkan dengan tahun " . $tahun_kab_ppe_ke2 . ". Pada " . $tahun_kab_ppe_max . " pertumbuhan ekonomi " . $xnameKab . " adalah sebesar " . $nilaiData_ppe33[$tahun_kab_ppe_max] . "%, sedangkan pada tahun " . $tahun_kab_ppe_ke2 . " pertumbuhan tercatat sebesar " . $nilaiData_ppe33[$tahun_kab_ppe_ke2] . "%. ";
                if ($nilai_ppe2_pro[$tahun_kab_ppe_max] > $nilaiData_ppe33[$tahun_kab_ppe_max]) {
                    $max_pe_p  = " Pertumbuhan ekonomi " . $xnameKab . " pada tahun " . $tahun_kab_ppe_max . " berada dibawah capaian " . $xname . ". Pertumbuhan ekonomi " . $xname . " pada tahun " . $tahun_kab_ppe_max . " adalah sebesar " . $nilai_ppe2_pro[$tahun_kab_ppe_max] . "%. ";
                } else {
                    $max_pe_p  = " Pertumbuhan ekonomi " . $xnameKab . " pada tahun " . $tahun_kab_ppe_max . " berada diatas capaian " . $xname . ". Pertumbuhan ekonomi " . $xname . " pada tahun " . $tahun_kab_ppe_max . " adalah sebesar " . $nilai_ppe2_pro[$tahun_kab_ppe_max] . "%. ";
                }
            }
            if ($nilai_ppe_n[$tahun_kab_ppe_max] > $nilaiData_ppe33[$tahun_kab_ppe_max]) {
                $max_pe_k    = " Capaian pertumbuhan ekonomi " . $xnameKab . " pada tahun " . $tahun_kab_ppe_max . " berada dibawah nasional. Pertumbuhan ekonomi nasional pada tahun " . $tahun_kab_ppe_max . " adalah sebesar " . $nilai_ppe_n[$tahun_kab_ppe_max] . "% ";
            } else {
                $max_pe_k    = " Capaian pertumbuhan ekonomi " . $xnameKab . " pada tahun " . $tahun_kab_ppe_max . " berada diatas nasional. Pertumbuhan ekonomi nasional pada tahun " . $tahun_kab_ppe_max . " adalah sebesar " . $nilai_ppe_n[$tahun_kab_ppe_max] . "%  ";
            }
            $tahun_pe_max = $tahun_kab_ppe_max . "";
            $ppe_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='1' AND e.id_periode='" . $periode_kab_ppe_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='1' AND id_periode='" . $periode_kab_ppe_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            //print_r($ppe_kab);exit();
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
            }
            $label_data_ppe     = $label_ppe1;
            $nilai_data_ppe_per = $nilai_ppe_per;

            //Pertumbuhan Ekonomi
            $graph_ppe = new Graph(500, 230);
            $graph_ppe->SetScale("textlin");
            $theme_class_ppe = new UniversalTheme;
            $graph_ppe->SetTheme($theme_class_ppe);
            $graph_ppe->SetMargin(40, 20, 33, 58);
            $graph_ppe->SetBox(false);
            $graph_ppe->yaxis->HideZeroLabel();
            $graph_ppe->yaxis->HideLine(false);
            $graph_ppe->yaxis->HideTicks(false, false);
            $graph_ppe->xaxis->SetTickLabels($tahun_ppe);
            $graph_ppe->ygrid->SetFill(false);
            $graph_bar_ppe = new Graph(600, 230);
            $graph_bar_ppe->img->SetMargin(40, 20, 20, 150);
            $graph_bar_ppe->SetScale("textlin");
            $graph_bar_ppe->SetMarginColor("lightblue:1.1");
            $graph_bar_ppe->SetShadow();
            $graph_bar_ppe->title->SetMargin(8);
            $graph_bar_ppe->title->SetColor("darkred");
            $graph_bar_ppe->ygrid->SetFill(false);
            $graph_bar_ppe->xaxis->SetTickLabels($label_data_ppe);
            $graph_bar_ppe->yaxis->HideLine(false);
            $graph_bar_ppe->yaxis->HideTicks(false, false);
            $graph_bar_ppe->xaxis->SetLabelAngle(90);

            $p1_ppe = new LinePlot($datay_ppe);
            $graph_ppe->Add($p1_ppe);
            $p1_ppe->SetColor("#0000FF");
            $p1_ppe->SetLegend('Indonesia');
            $p1_ppe->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ppe->mark->SetColor('#0000FF');
            $p1_ppe->mark->SetFillColor('#0000FF');
            $p1_ppe->SetCenter();
            $p1_ppe->value->Show();
            $p1_ppe->value->SetFormat('%0.3f');
            $p1_ppe->value->SetColor('#0000FF');

            $graph_ppe->legend->SetFrameWeight(1);
            $graph_ppe->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ppe->legend->SetMarkAbsSize(8);

            $p2_ppe = new LinePlot($datay_ppe2);
            $graph_ppe->Add($p2_ppe);
            $p2_ppe->SetColor("#000000");
            $p2_ppe->SetLegend($xname);
            $p2_ppe->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_ppe->mark->SetColor('#000000');
            $p2_ppe->mark->SetFillColor('#000000');
            $p2_ppe->value->SetMargin(14);
            $p2_ppe->SetCenter();
            $p2_ppe->value->Show();
            $p2_ppe->value->SetFormat('%0.3f');
            $p2_ppe->value->SetColor('#000000');

            $p3_ppe = new LinePlot($datay_ppe3);
            $graph_ppe->Add($p3_ppe);
            $p3_ppe->SetColor("#FF0000");
            $p3_ppe->SetLegend($xnameKab);
            $p3_ppe->mark->SetType(MARK_X, '', 1.0);
            $p3_ppe->mark->SetColor('#FF0000');
            $p3_ppe->mark->SetFillColor('#FF0000');
            $p3_ppe->value->SetMargin(14);
            $p3_ppe->SetCenter();
            $p3_ppe->value->Show();
            $p3_ppe->value->SetFormat('%0.3f');
            $p3_ppe->value->SetColor('#FF0000');
            $p3_ppe->SetStyle("dotted");

            $b1plot_ppe_per = new BarPlot($nilai_data_ppe_per);
            $gbplot_ppe_per = new GroupBarPlot(array($b1plot_ppe_per));
            $graph_bar_ppe->Add($gbplot_ppe_per);
            $b1plot_ppe_per->SetColor("white");
            $b1plot_ppe_per->SetFillColor("#0000FF");
            //$b1plot_ppe_per->value->Show();
            $b1plot_ppe_per->value->SetFormat('%0.3f');
            //PERKEMBANGAN PERTUMBUHAN EKONOMI
            $graph_ppe->Stroke($this->picture . '/' . $picture_pe . '.png');
            $graph_bar_ppe->Stroke($this->picture . '/' . $picture_pe_bar . '.png');

            //Perkembangan PDRB Per Kapita ADHB
            $sql_adhb = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";

            $list_adhb = $this->db->query($sql_adhb);
            foreach ($list_adhb->result() as $row_adhb) {
                $tahun_adhb[]   = $row_adhb->tahun;
                $nilaiData_adhb1[] = (float)$row_adhb->nilai / 1000000;
                $nilaiData_max[]   = (float)$row_adhb->nilai;
                $nilaiData_maxx[$row_adhb->tahun]   = (float)$row_adhb->nilai;
            }
            $datay_adhb1 = $nilaiData_adhb1;
            $tahun_adhb1 = $tahun_adhb;
            $sql_adhb2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhb2 = $this->db->query($sql_adhb2);
            foreach ($list_adhb2->result() as $row_adhb2) {
                $tahun_adhb2[]   = $row_adhb2->tahun;
                $nilaiData_adhb2[] = (float)$row_adhb2->nilai / 1000000;
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
                $nilaiData_adhb3[] = (float)$row_adhb3->nilai_kab / 1000000;
                $nilaiD_adhb3[$row_adhb3->tahun] = (float)$row_adhb3->nilai_kab;
                $nilaiData_max_k[] = (float)$row_adhb3->nilai_kab;
                $sumber_adhb       = "BPS"; //$row_adhb3->sumber;
                $periode_kab_adhb[] = $row_adhb3->idperiode;
            }
            $datay_adhb3 = array_reverse($nilaiData_adhb3);
            $tahunadhb    = end($tahun_adhb1);
            $periode_kab_adhb_max = max($periode_kab_adhb);
            $periode_adhb_tahun = max($tahun_adhb3) . "";
            $tahun_kab_adhb_max = max($tahun_adhb3);
            $tahun_kab_adhb_ke2 = $tahun_kab_adhb_max - 1;
            //                    $Kmax =$nilaiD_adhb3[$tahun_kab_adhb_max];

            if ($nilaiD_adhb3[$tahun_kab_adhb_ke2] > $nilaiD_adhb3[$tahun_kab_adhb_max]) {
                $max_adhb    = "PDRB perkapita ADHB " . $xnameKab . " pada tahun " . $tahun_kab_adhb_max . " menurun dibandingkan dengan tahun " . $tahun_kab_adhb_ke2 . ". Pada tahun " . $tahun_kab_adhb_max . " PDRB per kapita ADHB " . $xnameKab . " adalah sebesar Rp" . number_format($nilaiD_adhb3[$tahun_kab_adhb_max], 0) . ", sedangkan pada tahun " . $tahun_kab_adhb_ke2 . " PDRB per kapita ADHB tercatat sebesar Rp " . number_format($nilaiD_adhb3[$tahun_kab_adhb_ke2], 0) . ". ";
                if ($nilaiD_adhb2[$tahun_kab_adhb_max] > $nilaiD_adhb3[$tahun_kab_adhb_max]) {
                    $max_adhb_p  = " PDRB per kapita ADHB " . $xnameKab . " pada tahun " . $tahun_kab_adhb_max . " berada dibawah capaian " . $xname . ". PDRB perkapita ADHB " . $xname . " pada tahun " . $tahun_kab_adhb_max . " adalah sebesar Rp" . number_format($nilaiD_adhb2[$tahun_kab_adhb_max], 0) . " " . $ket_adhk2[5] . ". ";
                } else {
                    $max_adhb_p  = " PDRB per kapita ADHB " . $xnameKab . " pada tahun " . $tahun_kab_adhb_max . " berada diatas capaian " . $xname . ". PDRB perkapita ADHB " . $xname . " pada tahun " . $tahun_kab_adhb_max . " adalah sebesar Rp" . number_format($nilaiD_adhb2[$tahun_kab_adhb_max], 0) . " " . $ket_adhk2[5] . ". ";
                }
            } else {
                $max_adhb    = "PDRB per kapita ADHB " . $xnameKab . " pada tahun " . $tahun_kab_adhb_max . " meningkat dibandingkan dengan tahun " . $tahun_kab_adhb_ke2 . ". Pada tahun " . $tahun_kab_adhb_max . " PDRB per kapita ADHB " . $xnameKab . " adalah sebesar Rp" . number_format($nilaiD_adhb3[$tahun_kab_adhb_max], 0) . ", sedangkan pada tahun " . $tahun_kab_adhb_ke2 . " PDRB per kapita ADHB tercatat sebesar Rp " . number_format($nilaiD_adhb3[$tahun_kab_adhb_ke2], 0) . ". ";
                if ($nilaiD_adhb2[$tahun_kab_adhb_max] > $nilaiD_adhb3[$tahun_kab_adhb_max]) {
                    $max_adhb_p  = " PDRB per kapita ADHB " . $xnameKab . " pada tahun " . $tahun_kab_adhb_max . " berada dibawah capaian " . $xname . ". PDRB perkapita ADHB " . $xname . " pada tahun " . $tahun_kab_adhb_max . " adalah sebesar Rp" . number_format($nilaiD_adhb2[$tahun_kab_adhb_max], 0) . " " . $ket_adhk2[5] . ". ";
                } else {
                    $max_adhb_p  = " PDRB per kapita ADHB " . $xnameKab . " pada tahun " . $tahun_kab_adhb_max . " berada diatas capaian " . $xname . ". PDRB perkapita ADHB " . $xname . " pada tahun " . $tahun_kab_adhb_max . " adalah sebesar Rp" . number_format($nilaiD_adhb2[$tahun_kab_adhb_max], 0) . " " . $ket_adhk2[5] . ". ";
                }
            }
            if ($nilaiData_maxx[$tahun_kab_adhb_max] > $nilaiD_adhb3[$tahun_kab_adhb_max]) {
                $max_adhb_k    = " Capaian PDRB perkapita ADHB " . $xnameKab . " pada tahun " . $tahun_kab_adhb_max . " berada dibawah nasional. PDRB perkapita ADHB nasional pada tahun " . $tahun_kab_adhb_max . " adalah sebesar Rp " . number_format($nilaiData_maxx[$tahun_kab_adhb_max], 0) . " ";
            } else {
                $max_adhb_k    = " Capaian PDRB perkapita ADHB " . $xnameKab . " pada tahun " . $tahun_kab_adhb_max . " berada diatas nasional. PDRB perkapita ADHB nasional pada tahun " . $tahun_kab_adhb_max . " adalah sebesar Rp " . number_format($nilaiData_maxx[$tahun_kab_adhb_max], 0) . "  ";
            }

            $adhb2_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='2' AND e.id_periode='" . $periode_kab_adhb_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='2' AND id_periode='" . $periode_kab_adhb_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";

            $list_kab_adhb_per = $this->db->query($adhb2_kab);
            foreach ($list_kab_adhb_per->result() as $row_adhb_kab_per) {
                // $kab = substr($row_adhb_kab_per->label,9);
                $label_adhb[]     = $row_adhb_kab_per->label;
                $nilai_adhb_per[] = $row_adhb_kab_per->nilai / 1000000;
                $posisi = strpos($row_adhb_kab_per->label, "Kabupaten");
                if ($posisi !== FALSE) {
                    $label_adhb11 = substr($row_adhb_kab_per->label, 0, 3) . " " . substr($row_adhb_kab_per->label, 10);
                } else {
                    $label_adhb11 = $row_adhb_kab_per->label;
                }
                $label_adhb1[]     = $label_adhb11;
            }
            $label_data_adhb     = $label_adhb1;
            $nilai_data_adhb_per = $nilai_adhb_per;

            //bar adhb
            $graph_adhb = new Graph(500, 230);
            $graph_adhb->SetScale("textlin");
            $graph_adhb->SetY2Scale("lin", 0, 90);
            $graph_adhb->SetY2OrderBack(false);
            $theme_class_adhb = new UniversalTheme;
            $graph_adhb->SetTheme($theme_class_adhb);
            $graph_adhb->SetMargin(40, 20, 46, 80);
            $graph_adhb->ygrid->SetFill(false);
            $graph_adhb->xaxis->SetTickLabels($tahun_adhb1);
            $graph_adhb->yaxis->HideLine(false);
            $graph_adhb->yaxis->HideTicks(false, false);
            $graph_adhb->title->Set(" ");

            $graph_bar_adhb = new Graph(500, 230);
            $graph_bar_adhb->SetScale("textlin");
            $graph_bar_adhb->SetY2Scale("lin", 0, 90);
            $graph_bar_adhb->SetY2OrderBack(false);
            $theme_class_bar_adhb = new UniversalTheme;
            $graph_bar_adhb->SetTheme($theme_class_bar_adhb);
            $graph_bar_adhb->SetMargin(40, 0.1, 0.9, 150);
            $graph_bar_adhb->ygrid->SetFill(false);
            $graph_bar_adhb->xaxis->SetTickLabels($label_data_adhb);
            $graph_bar_adhb->yaxis->HideLine(false);
            $graph_bar_adhb->yaxis->HideTicks(false, false);
            $graph_bar_adhb->xaxis->SetLabelAngle(90);
            //adhb1     
            $b1plot_adhb = new BarPlot($datay_adhb1);
            $b1plot2_adhb = new BarPlot($datay_adhb2);
            $b1plot3_adhb = new BarPlot($datay_adhb3);
            $gbplot_adhb = new GroupBarPlot(array($b1plot_adhb, $b1plot2_adhb, $b1plot3_adhb));
            $graph_adhb->Add($gbplot_adhb);
            $b1plot_adhb->SetColor("white");
            $b1plot_adhb->SetFillColor("#0000FF");
            $b1plot_adhb->SetLegend("Indonesia");
            $b1plot_adhb->SetWidth(20);
            //2\
            $b1plot2_adhb->SetColor("white");
            $b1plot2_adhb->SetFillColor("#000000");
            $b1plot2_adhb->SetLegend($xname);
            $b1plot2_adhb->SetWidth(20);
            //3
            $b1plot3_adhb->SetColor("white");
            $b1plot3_adhb->SetFillColor("#00FF00");
            $b1plot3_adhb->SetLegend($xnameKab);
            $b1plot3_adhb->SetWidth(20);
            //$b1plot3_adhb->value->Show();
            $b1plot_adhb_per = new BarPlot($nilai_data_adhb_per);
            $gbplot_adhb_per = new GroupBarPlot(array($b1plot_adhb_per));
            $graph_bar_adhb->Add($gbplot_adhb_per);
            $b1plot_adhb_per->SetColor("white");
            $b1plot_adhb_per->SetFillColor("#0000FF");
            //perkembangan PDRB per kapita ADHK
            $graph_adhb->Stroke($this->picture . '/' . $picture_adhb . '.png');
            $graph_bar_adhb->Stroke($this->picture . '/' . $picture_adhb_bar . '.png');

            //adhk
            $sql_adhk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhk = $this->db->query($sql_adhk);
            foreach ($list_adhk->result() as $row_adhk) {
                $tahun_adhk[]   = $row_adhk->tahun;
                $nilaiData_adhk1[] = (float)$row_adhk->nilai / 1000000;
                $adhk_nasional[] = (float)$row_adhk->nilai;
                $adhk_nasionall[$row_adhk->tahun] = (float)$row_adhk->nilai;
            }
            $datay_adhk1 = $nilaiData_adhk1;
            $tahun_adhk1 = $tahun_adhk;
            $sql_adhk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhk2 = $this->db->query($sql_adhk2);
            foreach ($list_adhk2->result() as $row_adhk2) {
                $tahun_adhk2[]              = $row_adhk2->tahun;
                $nilaiData_adhk2[]          = (float)$row_adhk2->nilai / 1000000;
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
                    $nilaiDataadhk3 = '-';
                } else {
                    $nilaiDataadhk3 = (float)$row_adhk3->nilai_kab / 1000000;
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
                $max_adhk    = "PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " pada tahun " . $tahun_adhk3_max . " menurun dibandingkan dengan tahun " . $tahun_adhk3_1 . ". Pada " . $tahun_adhk3_max . " PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " adalah sebesar Rp " . number_format($adhk_kk[$tahun_adhk3_max], 0) . ", sedangkan pada tahun " . $tahun_adhk3_1 . " PDRB per Kapita ADHK Tahun Dasar 2010 tercatat sebesar Rp " . number_format($adhk_kk[$tahun_adhk3_1], 0) . ". ";
                if ($adhk_pp[$tahun_adhk3_max] > $adhk_kk[$tahun_adhk3_max]) {
                    $max_adhk_p  = " PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " pada tahun " . $tahun_adhk3_max . " berada dibawah capaian " . $xname . ". PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk3_max . " adalah sebesar Rp " . number_format($adhk_pp[$tahun_adhk3_max], 0) . " " . $ket_adhk2[$tahun_adhk3_max] . ". ";
                } else {
                    $max_adhk_p  = "  PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " pada tahun " . $tahun_adhk3_max . " berada diatas capaian " . $xname . ". PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk3_max . " adalah sebesar Rp " . number_format($adhk_pp[$tahun_adhk3_max], 0) . " " . $ket_adhk2[$tahun_adhk3_max] . ". ";
                }
            } else {
                $max_adhk    = "PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " pada tahun " . $tahun_adhk3_max . " meningkat dibandingkan dengan tahun " . $tahun_adhk3_1 . ". Pada " . $tahun_adhk3_max . " PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " adalah sebesar Rp " . number_format($adhk_kk[$tahun_adhk3_max], 0) . ", sedangkan pada tahun " . $tahun_adhk3_1 . " PDRB per Kapita ADHK Tahun Dasar 2010 tercatat sebesar Rp " . number_format($adhk_kk[$tahun_adhk3_1], 0) . ". ";
                if ($adhk_pp[$tahun_adhk3_max] > $adhk_kk[$tahun_adhk3_max]) {
                    $max_adhk_p  = " PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " pada tahun " . $tahun_adhk3_max . " berada dibawah capaian " . $xname . ". PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk3_max . " adalah sebesar Rp " . number_format($adhk_pp[$tahun_adhk3_max], 0) . " " . $ket_adhk2[$tahun_adhk3_max] . " . ";
                } else {
                    $max_adhk_p  = "  PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " pada tahun " . $tahun_adhk3_max . " berada diatas capaian " . $xname . ". PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk3_max . " adalah sebesar Rp " . number_format($adhk_pp[$tahun_adhk3_max], 0) . " " . $ket_adhk2[$tahun_adhk3_max] . ". ";
                }
            }
            if ($adhk_nasionall[$tahun_adhk3_max] > $adhk_kk[$tahun_adhk3_max]) {
                $max_adhk_k    = " Capaian PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " pada tahun " . $tahun_adhk3_max . " berada dibawah nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun " . $tahun_adhk3_max . " adalah sebesar Rp " . number_format($adhk_nasionall[$tahun_adhk3_max], 0) . " ";
            } else {
                $max_adhk_k    = " Capaian PDRB per Kapita ADHK Tahun Dasar 2010 " . $xnameKab . " pada tahun " . $tahun_adhk3_max . " berada diatas nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun " . $tahun_adhk3_max . " adalah sebesar Rp " . number_format($adhk_nasionall[$tahun_adhk3_max], 0) . "  ";
            }
            $adhk2_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='3' AND e.id_periode='" . $periode_kab_adhk_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='3' AND id_periode='" . $periode_kab_adhk_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_adhk_per = $this->db->query($adhk2_kab);
            foreach ($list_kab_adhk_per->result() as $row_adhk_kab_per) {
                $label_adhk[]     = $row_adhk_kab_per->label;
                $nilai_adhk_per[] = $row_adhk_kab_per->nilai / 1000000;
                $posisi_adhk = strpos($row_adhk_kab_per->label, "Kabupaten");
                if ($posisi_adhk !== FALSE) {
                    $label_adhk11 = substr($row_adhk_kab_per->label, 0, 3) . " " . substr($row_adhk_kab_per->label, 10);
                } else {
                    $label_adhk11 = $row_adhk_kab_per->label;
                }
                $label_adhk1[] = $label_adhk11;
            }
            $label_data_adhk     = $label_adhk1;
            $nilai_data_adhk_per = $nilai_adhk_per;

            //adhk
            $graph_adhk = new Graph(500, 230);
            $graph_adhk->SetScale("textlin");
            $theme_class_adhk = new UniversalTheme;
            $graph_adhk->SetTheme($theme_class_adhk);
            $graph_adhk->SetMargin(40, 20, 33, 58);
            $graph_adhk->SetBox(false);
            $graph_adhk->yaxis->HideZeroLabel();
            $graph_adhk->yaxis->HideLine(false);
            $graph_adhk->yaxis->HideTicks(false, false);
            $graph_adhk->xaxis->SetTickLabels($tahun_adhk);
            $graph_adhk->ygrid->SetFill(false);

            $graph_bar_adhk = new Graph(500, 230);
            $graph_bar_adhk->SetScale("textlin");
            $graph_bar_adhk->SetY2Scale("lin", 0, 90);
            $graph_bar_adhk->SetY2OrderBack(false);
            $theme_class_bar_adhk = new UniversalTheme;
            $graph_bar_adhk->SetTheme($theme_class_bar_adhk);
            $graph_bar_adhk->SetMargin(80, 20, 5, 150);
            $graph_bar_adhk->ygrid->SetFill(false);
            $graph_bar_adhk->xaxis->SetTickLabels($label_data_adhk);
            $graph_bar_adhk->yaxis->HideLine(false);
            $graph_bar_adhk->yaxis->HideTicks(false, false);
            $graph_bar_adhk->title->Set(" ");
            $graph_bar_adhk->xaxis->SetLabelAngle(90);

            $adhk1 = new LinePlot($datay_adhk1);
            $graph_adhk->Add($adhk1);
            $adhk1->SetColor("#0000FF");
            $adhk1->value->Show();
            $adhk1->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $adhk1->mark->SetColor('#0000FF');
            $adhk1->mark->SetFillColor('#0000FF');
            $adhk1->SetCenter();
            $adhk1->SetLegend("Indonesia");
            $adhk1->value->SetFormat('%0.2f. Jt');
            $graph_adhk->legend->SetFrameWeight(1);
            $graph_adhk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_adhk->legend->SetMarkAbsSize(8);
            //2    
            $adhk2 = new LinePlot($datay_adhk2);
            $graph_adhk->Add($adhk2);
            $adhk2->SetColor("#000000");
            $adhk2->SetLegend($xname);
            $adhk2->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $adhk2->mark->SetColor('#000000');
            $adhk2->mark->SetFillColor('#000000');
            $adhk2->value->SetMargin(14);
            $adhk2->SetCenter();
            $adhk2->value->Show();
            $adhk2->value->SetFormat('%0.2f. Jt');
            //3
            $adhk3 = new LinePlot($datay_adhk3);
            $graph_adhk->Add($adhk3);
            $adhk3->SetColor("#FF0000");
            $adhk3->SetLegend($xnameKab);
            $adhk3->mark->SetType(MARK_X, '', 1.0);
            $adhk3->mark->SetColor('#FF0000');
            $adhk3->mark->SetFillColor('#FF0000');
            $adhk3->value->SetMargin(18);
            $adhk3->SetCenter();
            $adhk3->value->Show();
            $adhk3->value->SetColor('#FF0000');
            $adhk3->value->SetFormat('%0.2f. Jt');
            $adhk3->SetStyle("dotted");

            $b1plot_adhk_per = new BarPlot($nilai_data_adhk_per);
            $gbplot_adhk_per = new GroupBarPlot(array($b1plot_adhk_per));
            $graph_bar_adhk->Add($gbplot_adhk_per);
            $b1plot_adhk_per->SetColor("white");
            $b1plot_adhk_per->SetFillColor("#0000FF");
            //adhk
            $graph_adhk->legend->SetFrameWeight(1);
            $graph_adhk->Stroke($this->picture . '/' . $picture_adhk . '.png');
            $graph_bar_adhk->Stroke($this->picture . '/' . $picture_adhk_bar . '.png');

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
                //$tahun_ip[]     = $row_jp->id_periode;
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
                                                                                           WHERE id_indikator='4' AND wilayah='1000' group by id_periode
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
                                        WHERE id_indikator='4' AND wilayah='" . $id_pro . "' group by id_periode
                                        ) 
                                        group by id_periode 
                                        order by id_periode ASC limit 6
                        ) IND	ON REF.id_periode=IND.id_periode";
            $list_jp2 = $this->db->query($sql_jp2);
            foreach ($list_jp2->result() as $row_jp2) {
                $tahun_jp2[]   = $row_jp2->id_periode;
                $nilaiData_jp2[] = (float)$row_jp2->nilai_prov / 1000;
                //$nilai_capaian2[] = $row_jp2->nilai_prov;
                $nilai_capaian[] = (float)$row_jp2->nilai_prov;
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
                                        where (id_indikator='4' AND wilayah='" . $kab . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='4' AND wilayah='" . $kab . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode ASC limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
            $list_jp3 = $this->db->query($sql_jp3);
            foreach ($list_jp3->result() as $row_jp3) {
                $tahun_jp3[]     = $row_jp3->id_periode;
                $tahun_jp33[]     = $row_jp3->tahun;
                $nilaiDatajp3[] = (float)$row_jp3->nilai_kab / 1000;
                $nilai_capaian3[]                      = (float)$row_jp3->nilai_kab;
                $nilai_capaian33[$row_jp3->id_periode] = (float)$row_jp3->nilai_kab;
                $sumber_jp                             = $row_jp3->sumber_k;
                $tahun_jp333[$row_jp3->id_periode]     = $bulan[$row_jp3->periode] . "-" . $row_jp3->tahun;
            }
            $datay_jp3 = $nilaiDatajp3;
            $periode_kab_jp_max = max($tahun_jp3);
            $periode_kab_jp_maxx = max($tahun_jp3);
            $periode_kab_jp_1 = $periode_kab_jp_maxx - 100;
            $max_jp = "";
            $periode_jp_tahun = " " . $tahun_jp333[$periode_kab_jp_maxx] . "";


            if ($nilai_capaian33[$periode_kab_jp_1] > $nilai_capaian33[$periode_kab_jp_maxx]) {
                $rt_jp  = $nilai_capaian33[$periode_kab_jp_maxx] - $nilai_capaian33[$periode_kab_jp_1];
                $rt_jpp = abs($nilai_capaian33[$periode_kab_jp_maxx] - $nilai_capaian33[$periode_kab_jp_1]);
                $rt_jp2 = $rt_jp / $nilai_capaian33[$periode_kab_jp_1];
                $rt_jp3 = abs($rt_jp2 * 100);
                $rt_jp33 = number_format($rt_jp3, 2);
                $max_jp_p  = "Jumlah penganggur di " . $xnameKab . " pada " . $tahun_jp333[$periode_kab_jp_maxx] . " sebanyak " . number_format($nilai_capaian33[$periode_kab_jp_maxx], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahun_jp333[$periode_kab_jp_1] . " sebanyak " . number_format($nilai_capaian33[$periode_kab_jp_1], 0) . " orang. Selama periode  " . $tahun_jp333[$periode_kab_jp_1] . " - " . $tahun_jp333[$periode_kab_jp_maxx] . " jumlah penganggur di " . $xnameKab . " berkurang " . number_format($rt_jpp) . " orang atau sebesar " . $rt_jp33 . "% ";
            } else {
                $rt_jp  = $nilai_capaian3[5] - $nilai_capaian3[3];
                $rt_jp2 = $rt_jp / $nilai_capaian3[3];
                $rt_jp3 = $rt_jp2 * 100;
                $rt_jp33 = number_format($rt_jp3, 2);
                $max_jp_p  = "Jumlah penganggur di " . $xnameKab . " pada " . $tahun_jp333[$periode_kab_jp_maxx] . " sebanyak " . number_format($nilai_capaian33[$periode_kab_jp_maxx], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahun_jp333[$periode_kab_jp_1] . " sebanyak " . number_format($nilai_capaian33[$periode_kab_jp_1], 0) . " orang. Selama periode  " . $tahun_jp333[$periode_kab_jp_1] . " - " . $tahun_jp333[$periode_kab_jp_maxx] . " jumlah penganggur di " . $xnameKab . " meningkat " . number_format($rt_jp) . " orang atau sebesar " . $rt_jp33 . "%";
            }
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
                $posisi_jp = strpos($row_jp_kab_per->label, "Kabupaten");
                if ($posisi_jp !== FALSE) {
                    $label_jp11 = substr($row_jp_kab_per->label, 0, 3) . " " . substr($row_jp_kab_per->label, 10);
                } else {
                    $label_jp11 = $row_jp_kab_per->label;
                }
                $label_jp1[] = $label_jp11;
            }
            $label_data_jp     = $label_jp1;
            $nilai_data_jp_per = $nilai_jp_per;

            //jumlah pengangguran
            $graph_jp = new Graph(550, 230);
            $graph_jp->SetScale("textlin");
            $graph_jp->SetY2Scale("lin", 0, 90);
            $graph_jp->SetY2OrderBack(false);
            $theme_class_jp = new UniversalTheme;
            $graph_jp->SetTheme($theme_class_jp);
            $graph_jp->SetMargin(80, 20, 46, 80);
            $graph_jp->ygrid->SetFill(false);
            $graph_jp->xaxis->SetTickLabels($tahun_jp);
            $graph_jp->yaxis->HideLine(false);
            $graph_jp->yaxis->HideTicks(false, false);
            $graph_jp->title->Set("");
            //$graph_jp->yaxis->title->Set("Jumlah Pengangguran (orang)"); 
            $graph_bar_jp = new Graph(600, 230);
            $graph_bar_jp->SetScale("textlin");
            $graph_bar_jp->SetY2Scale("lin", 0, 90);
            $graph_bar_jp->SetY2OrderBack(false);
            $theme_class_bar_jp = new UniversalTheme;
            $graph_bar_jp->SetTheme($theme_class_bar_jp);
            $graph_bar_jp->SetMargin(20, 20, 20, 150);
            //$graph_adhb->yaxis->SetTickPositions(array(25000000,50000000,75000000));
            $graph_bar_jp->ygrid->SetFill(false);
            $graph_bar_jp->xaxis->SetTickLabels($label_data_jp);
            $graph_bar_jp->yaxis->HideLine(false);
            $graph_bar_jp->yaxis->HideTicks(false, false);
            $graph_bar_jp->xaxis->SetLabelAngle(90);
            //jumlah pengangguran
            $b1plot_jp  = new BarPlot($datay_jp);
            $b1plot2_jp = new BarPlot($datay_jp2);
            $b1plot3_jp = new BarPlot($datay_jp3);
            $gbplot_jp  = new GroupBarPlot(array($b1plot3_jp));
            $graph_jp->Add($gbplot_jp);
            $b1plot_jp->SetColor("white");
            $b1plot_jp->SetFillColor("#0000FF");
            $b1plot_jp->SetLegend("Indonesia");
            $b1plot_jp->SetWidth(20);

            $b1plot2_jp->SetColor("white");
            $b1plot2_jp->SetFillColor("#000000");
            $b1plot2_jp->SetLegend($xname);
            $b1plot2_jp->SetWidth(20);

            $b1plot3_jp->SetColor("white");
            $b1plot3_jp->SetFillColor("#00FF00");
            $b1plot3_jp->SetLegend($xnameKab);
            $b1plot3_jp->SetWidth(20);

            $b1plot_jp_per = new BarPlot($nilai_data_jp_per);
            $gbplot_jp_per = new GroupBarPlot(array($b1plot_jp_per));
            $graph_bar_jp->Add($gbplot_jp_per);
            $b1plot_jp_per->SetColor("white");
            $b1plot_jp_per->SetFillColor("#0000FF");
            //jumlah pengangguran
            $graph_jp->Stroke($this->picture . '/' . $picture_jp . '.png');
            $graph_bar_jp->Stroke($this->picture . '/' . $picture_jp_bar . '.png');



            //tingkat pengangguran terbuka                    
            $sql_tpt = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 8) y order by id_periode ASC";
            $list_tpt = $this->db->query($sql_tpt);
            foreach ($list_tpt->result() as $row_tpt) {
                $tahun_tpt1[]     = $bulan[$row_tpt->periode] . "-" . $row_tpt->tahun;
                $tahun_tpt[]      = $row_tpt->tahun;
                $nilaiData_tpt[]  = (float)$row_tpt->nilai;
                $data_tpt[$row_tpt->id_periode] = (float)$row_tpt->nilai;
            }
            $datay_tpt = $nilaiData_tpt;
            $tahun_tpt = $tahun_tpt;
            $sql_tpt2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 8) y order by id_periode ASC";
            $list_tpt2 = $this->db->query($sql_tpt2);
            foreach ($list_tpt2->result() as $row_tpt2) {
                $tahun_tpt2[]   = $row_tpt2->tahun;
                $nilaiData_tpt2[] = (float)$row_tpt2->nilai;
                $data_tpt2[$row_tpt2->id_periode] = (float)$row_tpt2->nilai;
            }
            $datay_tpt2 = $nilaiData_tpt2;
            $tahun_tpt2 = $tahun_tpt2;

            $sql_tpt3 = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k, IND.tahun, IND.periode 
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
                    $nilaiData_ktpt3 = '-';
                } else {
                    $nilaiData_ktpt3 = (float)$row_tpt3->nilai_kab;
                }
                $nilaiData_tpt3[] = $nilaiData_ktpt3;
                $data_tpt3[$row_tpt3->id_periode]   = $nilaiData_ktpt3;
                $sumber_tpt                         = "Badan Pusat Statistik";
                $diperiode_tpt3[]                   = $row_tpt3->id_periode;
                $tahun_tpt3[]                       = $row_tpt3->tahun;
                $datay_tpt33[$row_tpt3->id_periode] = $bulan[$row_tpt3->periode] . " " . $row_tpt3->tahun;
            }
            $datay_tpt3 = array_reverse($nilaiData_tpt3);
            $tahun_tpt3 = $tahun_tpt3;
            $periode_kab_tpt_max = max($diperiode_tpt3);
            $periode_kab_tpt_ke2 = $periode_kab_tpt_max - 100;
            $periode_tpt_tahun = "" . $datay_tpt33[$periode_kab_tpt_max] . "";

            if ($data_tpt3[$periode_kab_tpt_ke2] > $data_tpt3[$periode_kab_tpt_max]) {
                $max_tpt    = "Tingkat Pengangguran Terbuka " . $xnameKab . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " menurun dibandingkan dengan " . $datay_tpt33[$periode_kab_tpt_ke2] . ". Pada " . $datay_tpt33[$periode_kab_tpt_max] . " Tingkat Pengangguran Terbuka " . $xnameKab . " adalah sebesar " . number_format((float)$data_tpt3[$periode_kab_tpt_max], 2) . "%, sedangkan pada " . $datay_tpt33[$periode_kab_tpt_ke2] . " Tingkat Pengangguran Terbuka tercatat sebesar " . number_format((float)$data_tpt3[$periode_kab_tpt_ke2], 2) . "%. ";
                if ($data_tpt2[$periode_kab_tpt_ke2] > $data_tpt3[$periode_kab_tpt_max]) {
                    $max_tpt_p  = "Tingkat Pengangguran Terbuka " . $xnameKab . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " berada dibawah capaian " . $xname . ". Tingkat Pengangguran Terbuka " . $xname . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " adalah sebesar " . number_format($data_tpt2[$periode_kab_tpt_ke2], 2) . "%. ";
                } else {
                    $max_tpt_p  = "Tingkat Pengangguran Terbuka " . $xnameKab . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " berada diatas capaian " . $xname . ". Tingkat Pengangguran Terbuka " . $xname . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " adalah sebesar " . number_format($data_tpt2[$periode_kab_tpt_ke2], 2) . "%. ";
                }
            } else {
                $max_tpt    = "Tingkat Pengangguran Terbuka " . $xnameKab . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " meningkat dibandingkan dengan " . $datay_tpt33[$periode_kab_tpt_ke2] . ". Pada " . $datay_tpt33[$periode_kab_tpt_max] . " Tingkat Pengangguran Terbuka " . $xnameKab . " adalah sebesar " . number_format((float)$data_tpt3[$periode_kab_tpt_max], 2) . "%, sedangkan pada " . $datay_tpt33[$periode_kab_tpt_ke2] . " Tingkat Pengangguran Terbuka tercatat sebesar " . number_format((float)$data_tpt3[$periode_kab_tpt_ke2], 2) . "%. ";
                if ($data_tpt2[$periode_kab_tpt_ke2] > $data_tpt3[$periode_kab_tpt_max]) {
                    $max_tpt_p  = "Tingkat Pengangguran Terbuka " . $xnameKab . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " berada dibawah capaian " . $xname . ". Tingkat Pengangguran Terbuka " . $xname . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " adalah sebesar " . number_format($data_tpt2[$periode_kab_tpt_ke2], 2) . "%. ";
                } else {
                    $max_tpt_p  = "Tingkat Pengangguran Terbuka " . $xnameKab . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " berada diatas capaian " . $xname . ". Tingkat Pengangguran Terbuka " . $xname . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " adalah sebesar " . number_format($data_tpt2[$periode_kab_tpt_ke2], 2) . "%. ";
                }
            }
            if ($data_tpt[$periode_kab_tpt_max] > $data_tpt3[$periode_kab_tpt_max]) {
                $max_tpt_k    = " Capaian Tingkat Pengangguran Terbuka " . $xnameKab . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " berada dibawah nasional. Tingkat Pengangguran Terbuka nasional pada " . $datay_tpt33[$periode_kab_tpt_max] . " adalah sebesar " . number_format($data_tpt[$periode_kab_tpt_max], 2) . "% ";
            } else {
                $max_tpt_k    = " Capaian Tingkat Pengangguran Terbuka " . $xnameKab . " pada " . $datay_tpt33[$periode_kab_tpt_max] . " berada diatas nasional. Tingkat Pengangguran Terbuka nasional pada " . $datay_tpt33[$periode_kab_tpt_max] . " adalah sebesar " . number_format($datay_tpt[$periode_kab_tpt_max], 2) . "%  ";
            }

            $tpt_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='6' AND e.id_periode='" . $periode_kab_tpt_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='6' AND id_periode='" . $periode_kab_tpt_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_tpt_per = $this->db->query($tpt_kab);
            foreach ($list_kab_tpt_per->result() as $row_tpt_kab_per) {
                $label_tpt[]     = $row_tpt_kab_per->label;
                $nilai_tpt_per[] = $row_tpt_kab_per->nilai;
                $posisi_tpt      = strpos($row_tpt_kab_per->label, "Kabupaten");
                if ($posisi_tpt !== FALSE) {
                    $label_tpt11 = substr($row_tpt_kab_per->label, 0, 3) . " " . substr($row_tpt_kab_per->label, 10);
                } else {
                    $label_tpt11 = $row_tpt_kab_per->label;
                }
                $label_tpt1[] = $label_tpt11;
            }
            $label_data_tpt     = $label_tpt1;
            $nilai_data_tpt_per = $nilai_tpt_per;

            //tingkat pengangguran terbuka  
            $graph_tpt = new Graph(500, 230);
            $graph_tpt->SetScale("textlin");
            $theme_class_tpt = new UniversalTheme;
            $graph_tpt->SetTheme($theme_class_tpt);
            $graph_tpt->SetMargin(40, 20, 33, 60);
            $graph_tpt->SetBox(false);
            $graph_tpt->yaxis->HideZeroLabel();
            $graph_tpt->yaxis->HideLine(false);
            $graph_tpt->yaxis->HideTicks(false, false);
            $graph_tpt->xaxis->SetTickLabels($tahun_tpt1);
            $graph_tpt->ygrid->SetFill(false);

            $graph_bar_tpt = new Graph(600, 230);
            $graph_bar_tpt->img->SetMargin(40, 20, 20, 150);
            $graph_bar_tpt->SetScale("textlin");
            $graph_bar_tpt->SetMarginColor("lightblue:1.1");
            $graph_bar_tpt->SetShadow();
            $graph_bar_tpt->title->SetMargin(8);
            $graph_bar_tpt->title->SetColor("darkred");
            $graph_bar_tpt->ygrid->SetFill(false);
            $graph_bar_tpt->xaxis->SetTickLabels($label_data_tpt);
            $graph_bar_tpt->yaxis->HideLine(false);
            $graph_bar_tpt->yaxis->HideTicks(false, false);
            $graph_bar_tpt->xaxis->SetLabelAngle(90);

            //tingkat pengangguran terbuka
            $p1_tpt = new LinePlot($datay_tpt);
            $graph_tpt->Add($p1_tpt);
            $p1_tpt->SetLegend('Indonesia');
            $p1_tpt->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_tpt->mark->SetColor('#0000FF');
            $p1_tpt->mark->SetFillColor('#0000FF');
            $p1_tpt->SetCenter();
            $p1_tpt->value->Show();
            $p1_tpt->value->SetColor('#0000FF');
            $p1_tpt->SetColor("#0000FF");
            $p1_tpt->value->SetFormat('%0.2f');
            $graph_tpt->legend->SetFrameWeight(1);
            $graph_tpt->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_tpt->legend->SetMarkAbsSize(5);

            $p2_tpt = new LinePlot($datay_tpt2);
            $graph_tpt->Add($p2_tpt);
            $p2_tpt->SetLegend($xname);
            $p2_tpt->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_tpt->mark->SetColor('#000000');
            $p2_tpt->mark->SetFillColor('#000000');
            $p2_tpt->value->SetMargin(14);
            $p2_tpt->SetCenter();
            $p2_tpt->value->Show();
            $p2_tpt->SetColor("#000000");
            $p2_tpt->value->SetColor('#000000');
            $p2_tpt->value->SetFormat('%0.2f');
            $p3_tpt = new LinePlot($datay_tpt3);
            $graph_tpt->Add($p3_tpt);
            $p3_tpt->SetLegend($xnameKab);
            $p3_tpt->mark->SetType(MARK_X, '', 1.0);
            $p3_tpt->mark->SetFillColor('#FF0000');
            $p3_tpt->value->SetMargin(14);
            $p3_tpt->SetCenter();
            $p3_tpt->value->Show();
            $p3_tpt->SetColor("#FF0000");
            $p3_tpt->value->SetColor('#FF0000');
            $p3_tpt->value->SetFormat('%0.2f');
            $p3_tpt->SetStyle("dotted");
            $b1plot_tpt_per = new BarPlot($nilai_data_tpt_per);
            $gbplot_tpt_per = new GroupBarPlot(array($b1plot_tpt_per));
            $graph_bar_tpt->Add($gbplot_tpt_per);
            $b1plot_tpt_per->SetColor("white");
            $b1plot_tpt_per->SetFillColor("#0000FF");
            //tingkat pengangguran terbuka
            $graph_tpt->Stroke($this->picture . '/' . $picture_tpt . '.png');
            $graph_bar_tpt->Stroke($this->picture . '/' . $picture_tpt_bar . '.png');

            //indeks pembangunan Manusia
            $sql_ipm = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ipm = $this->db->query($sql_ipm);
            foreach ($list_ipm->result() as $row_ipm) {
                $tahun_ipm[]   = $row_ipm->tahun;
                $nilaiData_ipm[] = (float)$row_ipm->nilai;
                $nilaiData_ipm1[$row_ipm->tahun] = (float)$row_ipm->nilai;
            }
            $datay_ipm = $nilaiData_ipm;
            $tahun_ipm = $tahun_ipm;
            $sql_ipm2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ipm2 = $this->db->query($sql_ipm2);
            foreach ($list_ipm2->result() as $row_ipm2) {
                $tahun_ipm2[]   = $row_ipm2->tahun;
                $nilaiData_ipm2[] = (float)$row_ipm2->nilai;
                $nilaiData_ipm22[$row_ipm2->tahun] = (float)$row_ipm2->nilai;
            }
            $datay_ipm2 = $nilaiData_ipm2;
            $tahun_ipm2 = $tahun_ipm2;
            $sql_ipm3 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='" . $kab . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='" . $kab . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
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
                $max_ipm    = "Indeks Pembangunan Manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " menurun dibandingkan dengan tahun" . $periode_kab_ipm_1 . ". Pada tahun " . $periode_kab_ipm_maxx . " Indeks Pembangunan Manusia " . $xnameKab . " adalah sebesar " . number_format($nilaiData_ipm33[$periode_kab_ipm_maxx], 2) . "%, sedangkan pada tahun " . $periode_kab_ipm_1 . " Indeks Pembangunan Manusia tercatat sebesar " . number_format($nilaiData_ipm33[$periode_kab_ipm_1], 2) . "%. ";
                if ($nilaiData_ipm22[$periode_kab_ipm_maxx] > $nilaiData_ipm33[$periode_kab_ipm_maxx]) {
                    $max_ipm_p  = "Indeks Pembangunan Manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " berada dibawah capaian " . $xname . ". Indeks Pembangunan Manusia " . $xname . " pada tahun " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm22[$periode_kab_ipm_maxx], 2) . "%. ";
                } else {
                    $max_ipm_p  = "Indeks Pembangunan Manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " berada diatas capaian " . $xname . ". Indeks Pembangunan Manusia " . $xname . " pada tahun " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm22[$periode_kab_ipm_maxx], 2) . "%. ";
                }
            } else {
                $max_ipm    = "Indeks Pembangunan Manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " meningkat dibandingkan dengan " . $periode_kab_ipm_1 . ". Pada " . $periode_kab_ipm_maxx . " Indeks Pembangunan Manusia " . $xnameKab . " adalah sebesar " . number_format($nilaiData_ipm33[$periode_kab_ipm_maxx], 2) . "%, sedangkan pada tahun " . $periode_kab_ipm_1 . " Indeks Pembangunan Manusia tercatat sebesar " . number_format($nilaiData_ipm33[$periode_kab_ipm_1], 2) . "%. ";
                if ($nilaiData_ipm22[$periode_kab_ipm_maxx] > $nilaiData_ipm33[$periode_kab_ipm_maxx]) {
                    $max_ipm_p  = "Indeks Pembangunan Manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " berada dibawah capaian " . $xname . ". Indeks Pembangunan Manusia " . $xname . " pada tahun  " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm22[$periode_kab_ipm_maxx], 2) . "%. ";
                } else {
                    $max_ipm_p  = "Indeks Pembangunan Manusia " . $xnameKab . " pada tahun " . $periode_kab_ipm_maxx . " berada diatas capaian " . $xname . ". Indeks Pembangunan Manusia " . $xname . " pada tahun " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm22[$periode_kab_ipm_maxx], 2) . "%. ";
                }
            }
            if ($nilaiData_ipm1[$periode_kab_ipm_maxx] > $nilaiData_ipm33[$periode_kab_ipm_maxx]) {
                $max_ipm_k    = " Capaian Indeks Pembangunan Manusia " . $xnameKab . " pada " . $periode_kab_ipm_maxx . " berada dibawah nasional. Indeks Pembangunan Manusia nasional pada " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm1[$periode_kab_ipm_maxx], 2) . "% ";
            } else {
                $max_ipm_k    = " Capaian Indeks Pembangunan Manusia " . $xnameKab . " pada " . $periode_kab_ipm_maxx . " berada diatas nasional. Indeks Pembangunan Manusia nasional pada " . $periode_kab_ipm_maxx . " adalah sebesar " . number_format($nilaiData_ipm1[$periode_kab_ipm_maxx], 2) . "%";
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
            }
            $label_data_ipm     = $label_ipm1;
            $nilai_data_ipm_per = $nilai_ipm_per;
            //Indeks Pembangunan Manusia
            $graph_ipm = new Graph(500, 230);
            $graph_ipm->SetScale("textlin");
            $theme_class_ipm = new UniversalTheme;
            $graph_ipm->SetTheme($theme_class_ipm);
            $graph_ipm->SetMargin(40, 20, 33, 60);
            //$graph_ipm->title->Set('Perkembangan Indeks Pembangunan Manusia');
            $graph_ipm->SetBox(false);
            $graph_ipm->yaxis->HideZeroLabel();
            $graph_ipm->yaxis->HideLine(false);
            $graph_ipm->yaxis->HideTicks(false, false);
            $graph_ipm->xaxis->SetTickLabels($tahun_ipm);
            $graph_ipm->ygrid->SetFill(false);
            //$graph_ipm->SetFormat('%.3f');

            $graph_bar_ipm = new Graph(600, 230);
            $graph_bar_ipm->img->SetMargin(40, 20, 20, 150);
            $graph_bar_ipm->SetScale("textlin");
            $graph_bar_ipm->SetMarginColor("lightblue:1.1");
            $graph_bar_ipm->SetShadow();
            $graph_bar_ipm->title->SetMargin(8);
            $graph_bar_ipm->title->SetColor("darkred");
            $graph_bar_ipm->ygrid->SetFill(false);
            $graph_bar_ipm->xaxis->SetTickLabels($label_data_ipm);
            $graph_bar_ipm->yaxis->HideLine(false);
            $graph_bar_ipm->yaxis->HideTicks(false, false);
            $graph_bar_ipm->xaxis->SetLabelAngle(90);

            //Indeks Pembangunan Manusia
            $p1_ipm = new LinePlot($datay_ipm);
            $graph_ipm->Add($p1_ipm);
            $p1_ipm->SetColor("#0000FF");
            $p1_ipm->SetLegend('Indonesia');
            $p1_ipm->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ipm->mark->SetColor('#0000FF');
            $p1_ipm->mark->SetFillColor('#0000FF');
            $p1_ipm->SetCenter();
            $p1_ipm->value->Show();
            $p1_ipm->value->SetColor('#0000FF');
            $p1_ipm->value->SetFormat('%0.2f');
            $graph_ipm->legend->SetFrameWeight(1);
            $graph_ipm->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ipm->legend->SetMarkAbsSize(8);

            $p2_ipm = new LinePlot($datay_ipm2);
            $graph_ipm->Add($p2_ipm);
            $p2_ipm->SetColor("#000000");
            $p2_ipm->SetLegend($xname);
            $p2_ipm->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_ipm->mark->SetColor('#000000');
            $p2_ipm->mark->SetFillColor('#000000');
            $p2_ipm->value->SetMargin(14);
            $p2_ipm->SetCenter();
            $p2_ipm->value->Show();
            $p2_ipm->value->SetColor('#000000');
            $p2_ipm->value->SetFormat('%0.2f');

            $p3_ipm = new LinePlot($datay_ipm3);
            $graph_ipm->Add($p3_ipm);
            $p3_ipm->SetColor("#FF0000");
            $p3_ipm->SetLegend($xnameKab);
            $p3_ipm->mark->SetType(MARK_X, '', 1.0);
            $p3_ipm->mark->SetColor('#FF0000');
            $p3_ipm->mark->SetFillColor('#FF0000');
            $p3_ipm->value->SetMargin(14);
            $p3_ipm->SetCenter();
            $p3_ipm->value->Show();
            $p3_ipm->value->SetColor('#FF0000');
            $p3_ipm->value->SetFormat('%0.2f');
            $p3_ipm->SetStyle("dotted");

            $b1plot_ipm_per = new BarPlot($nilai_data_ipm_per);
            $gbplot_ipm_per = new GroupBarPlot(array($b1plot_ipm_per));
            $graph_bar_ipm->Add($gbplot_ipm_per);
            $b1plot_ipm_per->SetColor("white");
            $b1plot_ipm_per->SetFillColor("#0000FF");
            //Indeks Pembangunan Manusia
            $graph_ipm->Stroke($this->picture . '/' . $picture_ipm . '.png');
            $graph_bar_ipm->Stroke($this->picture . '/' . $picture_ipm_bar . '.png');

            //Gini Rasio
            $sql_gr = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_gr = $this->db->query($sql_gr);
            foreach ($list_gr->result() as $row_gr) {
                $tahun_gr[]    = $bulan[$row_gr->periode] . "-" . $row_gr->tahun;
                $nilaiData_gr[] = (float)$row_gr->nilai;
                $tahun_pr[]    = $row_gr->id_periode;
                $idperiode_gr[] = $row_gr->id_periode;
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
                    $nilaiData_kgr3 = '-';
                } else {
                    $nilaiData_kgr3 = (float)$n_gr3;
                }
                $nilaiData_gr3[] = $nilaiData_kgr3;
                $tahun_gr3[] = $row_gr3->tahun;
                $periode_gr3[] = $row_gr3->idperiode;
                $nilaiData_gr33[$row_gr3->id_periode] = (float)$row_gr3->nilai_kab;
                $tanggal_gr[$row_gr3->id_periode]     = $bulan[$row_gr3->periode] . "-" . $row_gr3->tahun;
            }
            $datay_gr3 = array_reverse($nilaiData_gr3);
            $tahun_gr3 = $tahun_gr3;
            $periode_kab_gr_maxx = max($periode_gr3);
            $periode_kab_gr_1 = $periode_kab_gr_maxx - 100;
            $periode_gr_tahun = "Tahun " . $tanggal_gr[$periode_kab_gr_maxx] . "";
            if ($nilaiData_gr33[$periode_kab_gr_1] > $nilaiData_gr33[$periode_kab_gr_maxx]) {
                $max_n_gr    = "Gini Rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " menurun dibandingkan dengan " . $tanggal_gr[$periode_kab_gr_1] . ". Pada " . $tanggal_gr[$periode_kab_gr_maxx] . " gini rasio " . $xnameKab . " adalah sebesar " . $nilaiData_gr33[$periode_kab_gr_maxx] . "% sedangkan pada " . $tanggal_gr[$periode_kab_gr_1] . "  gini rasio tercatat sebesar " . $nilaiData_gr33[$periode_kab_gr_1] . "%. ";
                if ($nilaiData_gr222[$periode_kab_gr_maxx] > $nilaiData_gr33[$periode_kab_gr_maxx]) {
                    $max_p_gr  = "Capaian gini rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " berada dibawah capaian " . $xname . ". Gini rasio " . $xname . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " adalah sebesar " . $nilaiData_gr222[$periode_kab_gr_maxx] . "% ";
                } else {
                    $max_p_gr  = "Capaian gini rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " berada diatas capaian " . $xname . ". Gini rasio " . $xname . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " adalah sebesar " . $nilaiData_gr222[$periode_kab_gr_maxx] . "% ";
                }
            } else {
                $max_n_gr    = "Gini Rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " meningkat dibandingkan dengan " . $tanggal_gr[$periode_kab_gr_1] . ". Pada " . $tanggal_gr[$periode_kab_gr_maxx] . " gini rasio " . $xnameKab . " adalah sebesar " . $nilaiData_gr33[$periode_kab_gr_maxx] . "% sedangkan pada " . $tanggal_gr[$periode_kab_gr_1] . "  gini rasio tercatat sebesar " . $nilaiData_gr33[$periode_kab_gr_1] . "%. ";
                if ($nilaiData_gr222[$periode_kab_gr_maxx] > $nilaiData_gr33[$periode_kab_gr_maxx]) {
                    $max_p_gr  = "Capaian gini rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " berada dibawah capaian " . $xname . ". Gini rasio " . $xname . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " adalah sebesar " . $nilaiData_gr222[$periode_kab_gr_maxx] . "% ";
                } else {
                    $max_p_gr  = "Capaian gini rasio " . $xnameKab . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " berada diatas capaian " . $xname . ". Gini rasio " . $xname . " pada " . $tanggal_gr[$periode_kab_gr_maxx] . " adalah sebesar " . $nilaiData_gr222[$periode_kab_gr_maxx] . "% ";
                }
            }
            $gr_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='7' AND e.id_periode='" . $idperiode_gr[4] . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='7' AND id_periode='" . $idperiode_gr[4] . "' group by wilayah ) 
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
            }
            $label_data_gr     = $label_gr1;
            $nilai_data_gr_per = $nilai_gr_per;
            //Gini Rasio
            $graph_gr = new Graph(500, 230);
            $graph_gr->SetScale("textlin");
            $theme_class_gr = new UniversalTheme;
            $graph_gr->SetTheme($theme_class_gr);
            $graph_gr->SetMargin(40, 20, 33, 58);
            //$graph_gr->title->Set('Perkembangan Gini Rasio');
            $graph_gr->SetBox(false);
            $graph_gr->yaxis->HideZeroLabel();
            $graph_gr->yaxis->HideLine(false);
            $graph_gr->yaxis->HideTicks(false, false);
            $graph_gr->xaxis->SetTickLabels($tahun_gr);
            $graph_gr->ygrid->SetFill(false);

            $graph_bar_gr = new Graph(600, 230);
            $graph_bar_gr->img->SetMargin(40, 20, 20, 150);
            $graph_bar_gr->SetScale("textlin");
            $graph_bar_gr->SetMarginColor("lightblue:1.1");
            $graph_bar_gr->SetShadow();
            $graph_bar_gr->title->SetMargin(8);
            $graph_bar_gr->title->SetColor("darkred");
            $graph_bar_gr->ygrid->SetFill(false);
            $graph_bar_gr->xaxis->SetTickLabels($label_data_gr);
            $graph_bar_gr->yaxis->HideLine(false);
            $graph_bar_gr->yaxis->HideTicks(false, false);
            $graph_bar_gr->xaxis->SetLabelAngle(90);

            //Gini Rasio
            $p1_gr = new LinePlot($datay_gr);
            $graph_gr->Add($p1_gr);
            $p1_gr->SetColor("#0000FF");
            $p1_gr->SetLegend('Indonesia');
            $p1_gr->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_gr->mark->SetColor('#0000FF');
            $p1_gr->mark->SetFillColor('#0000FF');
            $p1_gr->SetCenter();
            $p1_gr->value->Show();
            $p1_gr->value->SetFormat('%0.3f');
            $p1_gr->value->SetColor('#0000FF');

            $graph_gr->legend->SetFrameWeight(1);
            $graph_gr->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_gr->legend->SetMarkAbsSize(8);

            $p2_gr = new LinePlot($datay_gr2);
            $graph_gr->Add($p2_gr);
            $p2_gr->SetColor("#000000");
            $p2_gr->SetLegend($xname);
            $p2_gr->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_gr->mark->SetColor('#000000');
            $p2_gr->mark->SetFillColor('#000000');
            $p2_gr->value->SetMargin(14);
            $p2_gr->SetCenter();
            $p2_gr->value->Show();
            $p2_gr->value->SetFormat('%0.3f');
            $p2_gr->value->SetColor('#000000');

            $p3_gr = new LinePlot($datay_gr3);
            $graph_gr->Add($p3_gr);
            $p3_gr->SetColor("#FF0000");
            $p3_gr->SetLegend($xnameKab);
            $p3_gr->mark->SetType(MARK_X, '', 1.0);
            $p3_gr->mark->SetColor('#FF0000');
            $p3_gr->mark->SetFillColor('#FF0000');
            $p3_gr->value->SetMargin(14);
            $p3_gr->SetCenter();
            $p3_gr->value->Show();
            $p3_gr->value->SetFormat('%0.3f');
            $p3_gr->value->SetColor('#FF0000');
            $p3_gr->SetStyle("dotted");
            $b1plot_gr_per = new BarPlot($nilai_data_gr_per);
            $gbplot_gr_per = new GroupBarPlot(array($b1plot_gr_per));
            $graph_bar_gr->Add($gbplot_gr_per);
            $b1plot_gr_per->SetColor("white");
            $b1plot_gr_per->SetFillColor("#0000FF");
            //$b1plot_gr_per->value->Show();
            $b1plot_gr_per->value->SetFormat('%0.3f');
            //Gini Rasio
            $graph_gr->Stroke($this->picture . '/' . $picture_gr . '.png');
            $graph_bar_gr->Stroke($this->picture . '/' . $picture_gr_bar . '.png');



            //angka harapan hidup
            $sql_ahh = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ahh = $this->db->query($sql_ahh);
            foreach ($list_ahh->result() as $row_ahh) {
                $tahun_ahh[]   = $row_ahh->tahun;
                $nilaiData_ahh[] = (float)$row_ahh->nilai;
                $nilaiData_ahh1[$row_ahh->tahun] = (float)$row_ahh->nilai;
            }
            $datay_ahh = $nilaiData_ahh;
            $tahun_ahh = $tahun_ahh;
            $sql_ahh2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ahh2 = $this->db->query($sql_ahh2);
            foreach ($list_ahh2->result() as $row_ahh2) {
                $tahun_ahh2[]   = $row_ahh2->tahun;
                $nilaiData_ahh2[] = (float)$row_ahh2->nilai;
                $nilaiData_ahh22[$row_ahh2->tahun] = (float)$row_ahh2->nilai;
            }
            $datay_ahh2 = $nilaiData_ahh2;
            $tahun_ahh2 = $tahun_ahh2;
            $sql_ahh3 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='" . $kab . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='" . $kab . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";

            $list_ahh3 = $this->db->query($sql_ahh3);
            foreach ($list_ahh3->result() as $row_ahh3) {
                $tahun_ahh3[]   = $row_ahh3->tahun;
                $nilaiData_ahh3[] = (float)$row_ahh3->nilai;
                $nilaiData_ahh33[$row_ahh3->tahun] = (float)$row_ahh3->nilai;
                $sumber_ahh = $row_ahh3->sumber;
                $idperiode_ahh_max[] = $row_ahh3->id_periode;
            }
            $periode_kab_ahh_max = max($idperiode_ahh_max);
            $periode_kab_ahh_maxx = max($tahun_ahh3);
            $periode_kab_ahh_1 = $periode_kab_ahh_maxx - 1;
            $datay_ahh3 = $nilaiData_ahh3;
            $tahun_ahh3 = $tahun_ahh3;
            $periode_ahh_tahun = "" . $periode_kab_ahh_maxx . "";

            if ($nilaiData_ahh33[$periode_kab_ahh_1] > $nilaiData_ahh33[$periode_kab_ahh_maxx]) {
                $max_n_ahh    = "Angka Harapan Hidup " . $xnameKab . " pada tahun " . $periode_kab_ahh_maxx . " menurun dibandingkan dengan tahun " . $periode_kab_ahh_1 . ". Pada tahun " . $periode_kab_ahh_maxx . " Angka Harapan Hidup " . $xnameKab . " adalah sebesar " . number_format($nilaiData_ahh33[$periode_kab_ahh_maxx], 2) . " tahun, sedangkan pada tahun " . $periode_kab_ahh_1 . " Angka Harapan Hidup tercatat sebesar " . number_format($nilaiData_ahh33[$periode_kab_ahh_1], 2) . " tahun. ";
                if ($nilaiData_ahh22[$periode_kab_ahh_maxx] > $nilaiData_ahh33[$periode_kab_ahh_maxx]) {
                    $max_p_gr  = "Angka Harapan Hidup " . $xnameKab . " pada tahun " . $periode_kab_ahh_maxx . " berada dibawah capaian " . $xname . ". Angka Harapan Hidup " . $xname . " pada tahun " . $periode_kab_ahh_maxx . " adalah sebesar " . number_format($nilaiData_ahh22[$periode_kab_ahh_maxx], 2) . " tahun. ";
                } else {
                    $max_p_gr  = "Angka Harapan Hidup " . $xnameKab . " pada tahun " . $periode_kab_ahh_maxx . " berada diatas capaian " . $xname . ". Angka Harapan Hidup " . $xname . " pada tahun " . $periode_kab_ahh_maxx . " adalah sebesar " . number_format($nilaiData_ahh22[$periode_kab_ahh_maxx], 2) . " tahun. ";
                }
            } else {
                $max_n_ahh    = "Angka Harapan Hidup " . $xnameKab . " pada tahun " . $periode_kab_ahh_maxx . " meningkat dibandingkan dengan tahun " . $periode_kab_ahh_1 . ". Pada " . $periode_kab_ahh_maxx . " Angka Harapan Hidup " . $xnameKab . " adalah sebesar " . number_format($nilaiData_ahh33[$periode_kab_ahh_maxx], 2) . " tahun, sedangkan pada tahun " . $periode_kab_ipm_1 . " Angka Harapan Hidup tercatat sebesar " . number_format($nilaiData_ahh33[$periode_kab_ipm_1], 2) . " tahun. ";
                if ($nilaiData_ahh22[$periode_kab_ahh_maxx] > $nilaiData_ahh33[$periode_kab_ahh_maxx]) {
                    $max_p_ahh  = "Angka Harapan Hidup " . $xnameKab . " pada tahun " . $periode_kab_ahh_maxx . " berada dibawah capaian " . $xname . ". Angka Harapan Hidup " . $xname . " pada tahun " . $periode_kab_ahh_maxx . " adalah sebesar " . number_format($nilaiData_ahh22[$periode_kab_ahh_maxx], 2) . " tahun. ";
                } else {
                    $max_p_ahh  = "Angka Harapan Hidup " . $xnameKab . " pada tahun " . $periode_kab_ahh_maxx . " berada diatas capaian " . $xname . ". Angka Harapan Hidup " . $xname . " pada tahun " . $periode_kab_ahh_maxx . " adalah sebesar " . number_format($nilaiData_ahh22[$periode_kab_ahh_maxx], 2) . " tahun. ";
                }
            }
            if ($nilaiData_ahh1[$periode_kab_ahh_maxx] > $nilaiData_ahh33[$periode_kab_ahh_maxx]) {
                $max_k_ahh    = " Capaian Angka Harapan Hidup " . $xnameKab . " pada tahun " . $periode_kab_ahh_maxx . " berada dibawah nasional. Angka Harapan Hidup nasional pada tahun " . $periode_kab_ahh_maxx . " adalah sebesar " . number_format($nilaiData_ahh1[$periode_kab_ahh_maxx], 2) . " tahun ";
            } else {
                $max_k_ahh    = " Capaian Angka Harapan Hidup " . $xnameKab . " pada tahun " . $periode_kab_ahh_maxx . " berada diatas nasional. Angka Harapan Hidup nasional pada tahun " . $periode_kab_ahh_maxx . " adalah sebesar " . number_format($nilaiData_ahh1[$periode_kab_ahh_maxx], 2) . " tahun";
            }
            $ahh_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='8' AND e.id_periode='" . $periode_kab_ipm_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='8' AND id_periode='" . $periode_kab_ipm_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_ahh_per = $this->db->query($ahh_kab);
            foreach ($list_kab_ahh_per->result() as $row_ahh_kab_per) {
                $label_ahh[]     = $row_ahh_kab_per->label;
                $nilai_ahh_per[] = $row_ahh_kab_per->nilai;
                $posisi_ahh = strpos($row_ahh_kab_per->label, "Kabupaten");
                if ($posisi_ahh !== FALSE) {
                    $label_ahh11 = substr($row_ahh_kab_per->label, 0, 3) . " " . substr($row_ahh_kab_per->label, 10);
                } else {
                    $label_ahh11 = $row_ahh_kab_per->label;
                }
                $label_ahh1[] = $label_ahh11;
            }
            $label_data_ahh     = $label_ahh1;
            $nilai_data_ahh_per = $nilai_ahh_per;
            //Angka Harapan Hidup
            $graph_ahh = new Graph(500, 230);
            $graph_ahh->SetScale("textlin");
            $theme_class_ahh = new UniversalTheme;
            $graph_ahh->SetTheme($theme_class_ahh);
            $graph_ahh->SetMargin(40, 20, 33, 58);
            //$graph_ahh->title->Set('Perkembangan Angka Harapan Hidup');
            $graph_ahh->SetBox(false);
            $graph_ahh->yaxis->HideZeroLabel();
            $graph_ahh->yaxis->HideLine(false);
            $graph_ahh->yaxis->HideTicks(false, false);
            $graph_ahh->xaxis->SetTickLabels($tahun_ahh);
            $graph_ahh->ygrid->SetFill(false);

            $graph_bar_ahh = new Graph(600, 230);
            $graph_bar_ahh->img->SetMargin(40, 20, 20, 150);
            $graph_bar_ahh->SetScale("textlin");
            $graph_bar_ahh->SetMarginColor("lightblue:1.1");
            $graph_bar_ahh->SetShadow();
            $graph_bar_ahh->title->SetMargin(8);
            $graph_bar_ahh->title->SetColor("darkred");
            $graph_bar_ahh->ygrid->SetFill(false);
            $graph_bar_ahh->xaxis->SetTickLabels($label_data_ahh);
            $graph_bar_ahh->yaxis->HideLine(false);
            $graph_bar_ahh->yaxis->HideTicks(false, false);
            $graph_bar_ahh->xaxis->SetLabelAngle(90);

            //Angka Harapan Hidup
            $p1_ahh = new LinePlot($datay_ahh);
            $graph_ahh->Add($p1_ahh);
            $p1_ahh->SetColor("#0000FF");
            $p1_ahh->SetLegend('Indonesia');
            $p1_ahh->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ahh->mark->SetColor('#0000FF');
            $p1_ahh->mark->SetFillColor('#0000FF');
            $p1_ahh->SetCenter();
            $p1_ahh->value->Show();
            $p1_ahh->value->SetColor('#0000FF');
            $p1_ahh->value->SetFormat('%0.2f');
            $graph_ahh->legend->SetFrameWeight(1);
            $graph_ahh->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ahh->legend->SetMarkAbsSize(8);

            $p2_ahh = new LinePlot($datay_ahh2);
            $graph_ahh->Add($p2_ahh);
            $p2_ahh->SetColor("#000000");
            $p2_ahh->SetLegend($xname);
            $p2_ahh->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_ahh->mark->SetColor('#000000');
            $p2_ahh->mark->SetFillColor('#000000');
            $p2_ahh->value->SetMargin(14);
            $p2_ahh->SetCenter();
            $p2_ahh->value->Show();
            $p2_ahh->value->SetColor('#000000');
            $p2_ahh->value->SetFormat('%0.2f');
            $p3_ahh = new LinePlot($datay_ahh3);
            $graph_ahh->Add($p3_ahh);
            $p3_ahh->SetColor("#FF0000");
            $p3_ahh->SetLegend($xnameKab);
            $p3_ahh->mark->SetType(MARK_X, '', 1.0);
            $p3_ahh->mark->SetColor('#FF0000');
            $p3_ahh->mark->SetFillColor('#FF0000');
            $p3_ahh->value->SetMargin(14);
            $p3_ahh->SetCenter();
            $p3_ahh->value->Show();
            $p3_ahh->value->SetColor('#FF0000');
            $p3_ahh->value->SetFormat('%0.2f');
            $p3_ahh->SetStyle("dotted");

            $b1plot_ahh_per = new BarPlot($nilai_data_ahh_per);
            $gbplot_ahh_per = new GroupBarPlot(array($b1plot_ahh_per));
            $graph_bar_ahh->Add($gbplot_ahh_per);
            $b1plot_ahh_per->SetColor("white");
            $b1plot_ahh_per->SetFillColor("#0000FF");

            //                //angka Harapan Hidup
            $graph_ahh->Stroke($this->picture . '/' . $picture_ahh . '.png');
            $graph_bar_ahh->Stroke($this->picture . '/' . $picture_ahh_bar . '.png');


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
            $sql_rls2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_rls2 = $this->db->query($sql_rls2);
            foreach ($list_rls2->result() as $row_rls2) {
                $tahun_rls2[]   = $row_rls2->tahun;
                $nilaiData_rls2[] = (float)$row_rls2->nilai;
                $nilaiData_rls22[$row_rls2->tahun] = (float)$row_rls2->nilai;
            }
            $datay_rls2 = $nilaiData_rls2;
            $tahun_rls2 = $tahun_rls2;
            $sql_rls3 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='" . $kab . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='" . $kab . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_rls3 = $this->db->query($sql_rls3);
            foreach ($list_rls3->result() as $row_rls3) {
                $tahun_rls3[]   = $row_rls3->tahun;
                $nilaiData_rls3[] = (float)$row_rls3->nilai;
                $nilaiData_rls33[$row_rls3->tahun] = (float)$row_rls3->nilai;
                $sumber_rls = $row_rls3->sumber;
                $idperiode_rls[] = $row_rls3->id_periode;
            }
            $datay_rls3 = $nilaiData_rls3;
            $tahun_rls3 = $tahun_rls3;
            $periode_kab_rls_max = max($idperiode_rls);
            $tahun_kab_rls_max = max($tahun_rls3);
            $tahun_kab_rls_1 = $tahun_kab_rls_max - 1;
            $periode_rls_tahun = $tahun_kab_rls_max;

            if ($nilaiData_rls33[$tahun_kab_rls_1] > $nilaiData_rls33[$tahun_kab_rls_max]) {
                $max_n_rls    = "Rata-rata lama sekolah " . $xnameKab . " pada tahun " . $tahun_kab_rls_max . " menurun dibandingkan dengan tahun " . $tahun_kab_rls_1 . ". Pada tahun " . $tahun_kab_rls_max . " Rata-rata Lama Sekolah " . $xnameKab . " adalah sebesar " . number_format($nilaiData_rls33[$tahun_kab_rls_max], 2) . " tahun, sedangkan pada tahun " . $tahun_kab_rls_1 . " Rata-rata Lama Sekolah tercatat sebesar " . number_format($nilaiData_rls33[$tahun_kab_rls_1], 2) . " tahun. ";
                if ($nilaiData_ahh22[$tahun_kab_rls_max] > $nilaiData_ahh33[$tahun_kab_rls_max]) {
                    $max_p_rls  = "Rata-rata Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_rls_max . " berada dibawah capaian " . $xname . ". Rata-rata Lama Sekolah " . $xname . " pada tahun " . $tahun_kab_rls_max . " adalah sebesar " . number_format($nilaiData_rls22[$tahun_kab_rls_max], 2) . " tahun. ";
                } else {
                    $max_p_rls  = "Rata-rata Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_rls_max . " berada diatas capaian " . $xname . ". Rata-rata Lama Sekolah " . $xname . " pada tahun " . $tahun_kab_rls_max . " adalah sebesar " . number_format($nilaiData_rls22[$tahun_kab_rls_max], 2) . " tahun. ";
                }
            } else {
                $max_n_rls    = "Rata-rata Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_rls_max . " meningkat dibandingkan dengan " . $tahun_kab_rls_1 . ". Pada tahun " . $tahun_kab_rls_max . " Rata-rata Lama Sekolah " . $xnameKab . " adalah sebesar " . number_format($nilaiData_rls33[$tahun_kab_rls_max], 2) . " tahun, sedangkan pada tahun " . $tahun_kab_rls_1 . " Rata-rata Lama Sekolah tercatat sebesar " . number_format($nilaiData_rls33[$tahun_kab_rls_1], 2) . " tahun. ";
                if ($nilaiData_ahh22[$tahun_kab_rls_max] > $nilaiData_ahh33[$tahun_kab_rls_max]) {
                    $max_p_rls  = "Rata-rata Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_rls_max . " berada dibawah capaian " . $xname . ". Rata-rata Lama Sekolah " . $xname . " pada tahun " . $tahun_kab_rls_max . " adalah sebesar " . number_format($nilaiData_rls22[$tahun_kab_rls_max], 2) . " tahun. ";
                } else {
                    $max_p_rls  = "Rata-rata Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_rls_max . " berada diatas capaian " . $xname . ". Rata-rata Lama Sekolah " . $xname . " pada tahun " . $tahun_kab_rls_max . " adalah sebesar " . number_format($nilaiData_rls22[$tahun_kab_rls_max], 2) . " tahun. ";
                }
            }
            if ($nilaiData_rls1[$tahun_kab_rls_max] > $nilaiData_rls33[$tahun_kab_rls_max]) {
                $max_k_rls    = " Capaian Rata-rata Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_rls_max . " berada dibawah nasional. Rata-rata Lama Sekolah nasional pada tahun " . $tahun_kab_rls_max . " adalah sebesar " . number_format($nilaiData_rls1[$tahun_kab_rls_max], 2) . " tahun ";
            } else {
                $max_k_rls    = " Capaian Rata-rata Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_rls_max . " berada diatas nasional. Rata-rata Lama Sekolah nasional pada tahun " . $tahun_kab_rls_max . " adalah sebesar " . number_format($nilaiData_rls1[$tahun_kab_rls_max], 2) . " tahun";
            }
            $rls_kab = "select p.nama_kabupaten as label, e.*  from kabupaten p join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='9' AND e.id_periode='" . $periode_kab_rls_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='9' AND id_periode='" . $periode_kab_rls_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_rls_per = $this->db->query($rls_kab);
            foreach ($list_kab_rls_per->result() as $row_rls_kab_per) {
                $label_rls[]     = $row_rls_kab_per->label;
                $nilai_rls_per[] = $row_rls_kab_per->nilai;
                $posisi_rls = strpos($row_rls_kab_per->label, "Kabupaten");
                if ($posisi_rls !== FALSE) {
                    $label_rls11 = substr($row_rls_kab_per->label, 0, 3) . " " . substr($row_rls_kab_per->label, 10);
                } else {
                    $label_rls11 = $row_rls_kab_per->label;
                }
                $label_rls1[] = $label_rls11;
            }
            $label_data_rls     = $label_rls1;
            $nilai_data_rls_per = $nilai_rls_per;
            //Rata-rata Lama Sekolah
            $graph_rls = new Graph(500, 230);
            $graph_rls->SetScale("textlin");
            $theme_class_rls = new UniversalTheme;
            $graph_rls->SetTheme($theme_class_rls);
            $graph_rls->SetMargin(40, 20, 33, 58);
            //$graph_rls->title->Set('Perkembangan Rata-rata Lama Sekolah');
            $graph_rls->SetBox(false);
            $graph_rls->yaxis->HideZeroLabel();
            $graph_rls->yaxis->HideLine(false);
            $graph_rls->yaxis->HideTicks(false, false);
            $graph_rls->xaxis->SetTickLabels($tahun_rls);
            $graph_rls->ygrid->SetFill(false);

            $graph_bar_rls = new Graph(600, 230);
            $graph_bar_rls->img->SetMargin(40, 20, 20, 150);
            $graph_bar_rls->SetScale("textlin");
            $graph_bar_rls->SetMarginColor("lightblue:1.1");
            $graph_bar_rls->SetShadow();
            $graph_bar_rls->title->SetMargin(8);
            $graph_bar_rls->title->SetColor("darkred");
            $graph_bar_rls->ygrid->SetFill(false);
            $graph_bar_rls->xaxis->SetTickLabels($label_data_rls);
            $graph_bar_rls->yaxis->HideLine(false);
            $graph_bar_rls->yaxis->HideTicks(false, false);
            $graph_bar_rls->xaxis->SetLabelAngle(90);

            //Rata-rata Lama Sekolah
            $p1_rls = new LinePlot($datay_rls);
            $graph_rls->Add($p1_rls);
            $p1_rls->SetColor("#0000FF");
            $p1_rls->SetLegend('Indonesia');
            $p1_rls->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_rls->mark->SetColor('#0000FF');
            $p1_rls->mark->SetFillColor('#0000FF');
            $p1_rls->SetCenter();
            $p1_rls->value->Show();
            $p1_rls->value->SetColor('#0000FF');
            $p1_rls->value->SetFormat('%0.2f');
            $graph_rls->legend->SetFrameWeight(1);
            $graph_rls->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_rls->legend->SetMarkAbsSize(8);

            $p2_rls = new LinePlot($datay_rls2);
            $graph_rls->Add($p2_rls);
            $p2_rls->SetColor("#000000");
            $p2_rls->SetLegend($xname);
            $p2_rls->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_rls->mark->SetColor('#000000');
            $p2_rls->mark->SetFillColor('#000000');
            $p2_rls->value->SetMargin(14);
            $p2_rls->SetCenter();
            $p2_rls->value->Show();
            $p2_rls->value->SetColor('#000000');
            $p2_rls->value->SetFormat('%0.2f');
            $p3_rls = new LinePlot($datay_rls3);
            $graph_rls->Add($p3_rls);
            $p3_rls->SetColor("#FF0000");
            $p3_rls->SetLegend($xnameKab);
            $p3_rls->mark->SetType(MARK_X, '', 1.0);
            $p3_rls->mark->SetColor('#FF0000');
            $p3_rls->mark->SetFillColor('#FF0000');
            $p3_rls->value->SetMargin(14);
            $p3_rls->SetCenter();
            $p3_rls->value->Show();
            $p3_rls->value->SetColor('#FF0000');
            $p3_rls->value->SetFormat('%0.2f');
            $p3_rls->SetStyle("dotted");
            $b1plot_rls_per = new BarPlot($nilai_data_rls_per);
            $gbplot_rls_per = new GroupBarPlot(array($b1plot_rls_per));
            $graph_bar_rls->Add($gbplot_rls_per);
            $b1plot_rls_per->SetColor("white");
            $b1plot_rls_per->SetFillColor("#0000FF");
            //Rata-rata Lama Sekolah
            $graph_rls->Stroke($this->picture . '/' . $picture_rls . '.png');
            $graph_bar_rls->Stroke($this->picture . '/' . $picture_rls_bar . '.png');

            //harapan lama sekolah
            $sql_hls = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_hls = $this->db->query($sql_hls);
            foreach ($list_hls->result() as $row_hls) {
                $tahun_hls[]   = $row_hls->tahun;
                $nilaiData_hls[] = (float)$row_hls->nilai;
                $nilaiData_hls1[$row_hls->tahun] = (float)$row_hls->nilai;
            }
            $datay_hls = $nilaiData_hls;
            $tahun_hls = $tahun_hls;
            $sql_hls2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_hls2 = $this->db->query($sql_hls2);
            foreach ($list_hls2->result() as $row_hls2) {
                $tahun_hls2[]   = $row_hls2->tahun;
                $nilaiData_hls2[] = (float)$row_hls2->nilai;
                $nilaiData_hls22[$row_hls2->tahun] = (float)$row_hls2->nilai;
            }
            $datay_hls2 = $nilaiData_hls2;
            $tahun_hls2 = $tahun_hls2;
            $sql_hls3 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='" . $kab . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='" . $kab . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";

            $list_hls3 = $this->db->query($sql_hls3);
            foreach ($list_hls3->result() as $row_hls3) {
                $tahun_hls3[]   = $row_hls3->tahun;
                $nilaiData_hls3[] = (float)$row_hls3->nilai;
                $nilaiData_hls33[$row_hls3->tahun] = (float)$row_hls3->nilai;
                $sumber_hls = $row_hls3->sumber;
                $idperiode_hls[] = $row_hls3->id_periode;
            }
            $periode_kab_hls_max = max($idperiode_hls);
            $tahun_kab_hls_max = max($tahun_hls3);
            $tahun_kab_hls_1 = $tahun_kab_hls_max - 1;
            $datay_hls3 = $nilaiData_hls3;
            $tahun_hls3 = $tahun_hls3;
            $periode_hls_tahun = $tahun_kab_hls_max;

            if ($nilaiData_hls33[$tahun_kab_hls_1] > $nilaiData_hls33[$tahun_kab_hls_max]) {
                $max_n_hls    = "Harapan Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_hls_max . " menurun dibandingkan dengan tahun " . $tahun_kab_hls_1 . ". Pada tahun  " . $tahun_kab_hls_max . " Harapan Lama Sekolah " . $xnameKab . " adalah sebesar " . number_format($nilaiData_hls33[$tahun_kab_hls_max], 2) . " tahun, sedangkan pada tahun " . $tahun_hls[4] . " Harapan Lama Sekolah tercatat sebesar " . number_format($nilaiData_hls3[4], 2) . " tahun. ";
                if ($nilaiData_hls22[$tahun_kab_hls_max] > $nilaiData_hls32[$tahun_kab_hls_max]) {
                    $max_p_hls  = "Harapan Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_hls_max . " berada dibawah capaian " . $xname . ". Harapan Lama Sekolah " . $xname . " pada tahun " . $tahun_kab_hls_max . " adalah sebesar " . number_format($nilaiData_hls22[$tahun_kab_hls_max], 2) . " tahun. ";
                } else {
                    $max_p_hls  = "Harapan Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_hls_max . " berada diatas capaian " . $xname . ". Harapan Lama Sekolah " . $xname . " pada tahun " . $tahun_kab_hls_max . " adalah sebesar " . number_format($nilaiData_hls22[$tahun_kab_hls_max], 2) . " tahun. ";
                }
            } else {
                $max_n_hls    = "Harapan Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_hls_max . " meningkat dibandingkan dengan tahun " . $tahun_kab_hls_1 . ". Pada tahun " . $tahun_kab_hls_max . " Harapan Lama Sekolah " . $xnameKab . " adalah sebesar " . number_format($nilaiData_hls33[$tahun_kab_hls_max], 2) . " tahun, sedangkan pada tahun " . $tahun_hls[4] . " Harapan Lama Sekolah tercatat sebesar " . number_format($nilaiData_hls3[4], 2) . " tahun. ";
                if ($nilaiData_hls22[$tahun_kab_hls_max] > $nilaiData_hls32[$tahun_kab_hls_max]) {
                    $max_p_hls  = "Harapan Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_hls_max . " berada dibawah capaian " . $xname . ". Harapan Lama Sekolah " . $xname . " pada tahun " . $tahun_kab_hls_max . " adalah sebesar " . number_format($nilaiData_hls22[$tahun_kab_hls_max], 2) . " tahun. ";
                } else {
                    $max_p_hls  = "Harapan Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_hls_max . " berada diatas capaian " . $xname . ". Harapan Lama Sekolah " . $xname . " pada tahun " . $tahun_kab_hls_max . " adalah sebesar " . number_format($nilaiData_hls22[$tahun_kab_hls_max], 2) . " tahun. ";
                }
            }
            if ($nilaiData_hls1[$tahun_kab_hls_max] > $nilaiData_hls33[$tahun_kab_hls_max]) {
                $max_k_hls    = " Capaian Harapan Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_hls_max . " berada dibawah nasional. Harapan Lama Sekolah nasional pada tahun " . $tahun_kab_hls_max . " adalah sebesar " . number_format($nilaiData_hls1[$tahun_kab_hls_max], 2) . " tahun ";
            } else {
                $max_k_hls    = " Capaian Harapan Lama Sekolah " . $xnameKab . " pada tahun " . $tahun_kab_hls_max . " berada diatas nasional. Harapan Lama Sekolah nasional pada tahun " . $tahun_kab_hls_max . " adalah sebesar " . number_format($nilaiData_hls1[$tahun_kab_hls_max], 2) . " tahun";
            }
            $hls_kab = "select p.nama_kabupaten as label, e.*  from kabupaten p join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='10' AND e.id_periode='" . $periode_kab_hls_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='10' AND id_periode='" . $periode_kab_hls_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_hls_per = $this->db->query($hls_kab);
            foreach ($list_kab_hls_per->result() as $row_hls_kab_per) {
                $label_hls[]     = $row_hls_kab_per->label;
                $nilai_hls_per[] = $row_hls_kab_per->nilai;
                $posisi_hls = strpos($row_hls_kab_per->label, "Kabupaten");
                if ($posisi_hls !== FALSE) {
                    $label_hls11 = substr($row_hls_kab_per->label, 0, 3) . " " . substr($row_hls_kab_per->label, 10);
                } else {
                    $label_hls11 = $row_hls_kab_per->label;
                }
                $label_hls1[] = $label_hls11;
            }
            $label_data_hls     = $label_hls1;
            $nilai_data_hls_per = $nilai_hls_per;

            $graph_hls = new Graph(500, 230);
            $graph_hls->SetScale("textlin");
            $theme_class_hls = new UniversalTheme;
            $graph_hls->SetTheme($theme_class_hls);
            $graph_hls->SetMargin(40, 20, 33, 58);
            //$graph_hls->title->Set('Perkembangan harapan Lama Sekolah');
            $graph_hls->SetBox(false);
            $graph_hls->yaxis->HideZeroLabel();
            $graph_hls->yaxis->HideLine(false);
            $graph_hls->yaxis->HideTicks(false, false);
            $graph_hls->xaxis->SetTickLabels($tahun_hls);
            $graph_hls->ygrid->SetFill(false);

            $graph_bar_hls = new Graph(600, 230);
            $graph_bar_hls->img->SetMargin(40, 20, 20, 150);
            $graph_bar_hls->SetScale("textlin");
            $graph_bar_hls->SetMarginColor("lightblue:1.1");
            $graph_bar_hls->SetShadow();
            $graph_bar_hls->title->SetMargin(8);
            $graph_bar_hls->title->SetColor("darkred");
            $graph_bar_hls->ygrid->SetFill(false);
            $graph_bar_hls->xaxis->SetTickLabels($label_data_hls);
            $graph_bar_hls->yaxis->HideLine(false);
            $graph_bar_hls->yaxis->HideTicks(false, false);
            $graph_bar_hls->xaxis->SetLabelAngle(90);

            //harapan Lama Sekolah
            $p1_hls = new LinePlot($datay_hls);
            $graph_hls->Add($p1_hls);
            $p1_hls->SetColor("#0000FF");
            $p1_hls->SetLegend('Indonesia');
            $p1_hls->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_hls->mark->SetColor('#0000FF');
            $p1_hls->mark->SetFillColor('#0000FF');
            $p1_hls->SetCenter();
            $p1_hls->value->Show();
            $p1_hls->value->SetColor('#0000FF');
            $p1_hls->value->SetFormat('%0.2f');
            $graph_hls->legend->SetFrameWeight(1);
            $graph_hls->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_hls->legend->SetMarkAbsSize(8);
            $p2_hls = new LinePlot($datay_hls2);
            $graph_hls->Add($p2_hls);
            $p2_hls->SetColor("#000000");
            $p2_hls->SetLegend($xname);
            $p2_hls->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_hls->mark->SetColor('#000000');
            $p2_hls->mark->SetFillColor('#000000');
            $p2_hls->value->SetMargin(14);
            $p2_hls->SetCenter();
            $p2_hls->value->Show();
            $p2_hls->value->SetColor('#000000');
            $p2_hls->value->SetFormat('%0.2f');

            $p3_hls = new LinePlot($datay_hls3);
            $graph_hls->Add($p3_hls);
            $p3_hls->SetColor("#FF0000");
            $p3_hls->SetLegend($xnameKab);
            $p3_hls->mark->SetType(MARK_X, '', 1.0);
            $p3_hls->mark->SetColor('#FF0000');
            $p3_hls->mark->SetFillColor('#FF0000');
            $p3_hls->value->SetMargin(14);
            $p3_hls->SetCenter();
            $p3_hls->value->Show();
            $p3_hls->value->SetColor('#FF0000');
            $p3_hls->value->SetFormat('%0.2f');
            $p3_hls->SetStyle("dotted");

            $b1plot_hls_per = new BarPlot($nilai_data_hls_per);
            $gbplot_hls_per = new GroupBarPlot(array($b1plot_hls_per));
            $graph_bar_hls->Add($gbplot_hls_per);
            $b1plot_hls_per->SetColor("white");
            $b1plot_hls_per->SetFillColor("#0000FF");

            //Harapan Lama Sekolah
            $graph_hls->Stroke($this->picture . '/' . $picture_hls . '.png');
            $graph_bar_hls->Stroke($this->picture . '/' . $picture_hls_bar . '.png');


            //pengeluaran per kapita 
            $sql_ppk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppk = $this->db->query($sql_ppk);
            foreach ($list_ppk->result() as $row_ppk) {
                $tahun_ppk[]   = $row_ppk->tahun;
                $nilaiData_ppk[] = (float)$row_ppk->nilai;
                $nilaiData_ppk1[$row_ppk->tahun] = (float)$row_ppk->nilai;
                $nilaiData_ppk11[] = (float)$row_ppk->nilai / 1000000;
            }
            $datay_ppk = $nilaiData_ppk11;
            $tahun_ppk = $tahun_ppk;
            $sql_ppk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppk2 = $this->db->query($sql_ppk2);
            foreach ($list_ppk2->result() as $row_ppk2) {
                $tahun_ppk2[]                      = $row_ppk2->tahun;
                $nilaiData_ppk2[]                  = (float)$row_ppk2->nilai;
                $nilaiData_ppk22[$row_ppk2->tahun] = (float)$row_ppk2->nilai;
                $nilaiData_ppk222[]                 = (float)$row_ppk2->nilai / 1000000;
            }
            $datay_ppk2 = $nilaiData_ppk222;
            $tahun_ppk2 = $tahun_ppk2;
            $sql_ppk3 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='" . $kab . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='" . $kab . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppk3 = $this->db->query($sql_ppk3);
            foreach ($list_ppk3->result() as $row_ppk3) {
                $tahun_ppk3[]   = $row_ppk3->tahun;
                $nilaiData_ppk3[] = (float)$row_ppk3->nilai;
                $nilaiData_ppk33[$row_ppk3->tahun] = (float)$row_ppk3->nilai;
                $nilaiData_ppk333[] = (float)$row_ppk3->nilai / 1000000;
                $sumber_ppk = $row_ppk3->sumber;
                $idperiode_ppk[] = $row_ppk3->id_periode;
            }
            $periode_kab_ppk_max = max($idperiode_ppk);
            $tahun_kab_ppk_max = max($tahun_ppk3);
            $tahun_kab_ppk_1 = $tahun_kab_ppk_max - 1;
            $datay_ppk3 = $nilaiData_ppk33;
            $tahun_ppk3 = $tahun_ppk3;
            $periode_ppk_tahun = $tahun_kab_ppk_max;

            if ($nilaiData_ppk33[$tahun_kab_ppk_1] > $nilaiData_ppk33[$tahun_kab_ppk_max]) {
                $max_n_ppk    = "Pengeluaran Perkapita " . $xnameKab . " pada tahun " . $tahun_kab_ppk_max . " menurun dibandingkan dengan tahun " . $tahun_kab_ppk_1 . ". Pada tahun " . $tahun_kab_ppk_max . " Pengeluaran Perkapita " . $xnameKab . " adalah sebesar Rp " . number_format($nilaiData_ppk33[$tahun_kab_ppk_max], 0) . ", sedangkan pada tahun " . $tahun_kab_ppk_1 . " Pengeluaran Perkapita tercatat sebesar Rp " . number_format($nilaiData_ppk33[$tahun_kab_ppk_1], 0) . ". ";
                if ($nilaiData_ppk22[$tahun_kab_ppk_max] > $nilaiData_ppk33[$tahun_kab_ppk_max]) {
                    $max_p_ppk  = "Pengeluaran Perkapita " . $xnameKab . " pada tahun " . $tahun_kab_ppk_max . " berada dibawah capaian " . $xname . ". Pengeluaran Perkapita " . $xname . " pada tahun " . $tahun_kab_ppk_max . " adalah sebesar Rp " . number_format($nilaiData_ppk22[$tahun_kab_ppk_max], 0) . ". ";
                } else {
                    $max_p_ppk  = "Pengeluaran Perkapita " . $xnameKab . " pada tahun " . $tahun_kab_ppk_max . " berada diatas capaian " . $xname . ". Pengeluaran Perkapita " . $xname . " pada tahun " . $tahun_kab_ppk_max . " adalah sebesar Rp " . number_format($nilaiData_ppk22[$tahun_kab_ppk_max], 0) . ". ";
                }
            } else {
                $max_n_ppk    = "Pengeluaran Perkapita " . $xnameKab . " pada tahun " . $tahun_kab_ppk_max . " meningkat dibandingkan dengan tahun " . $tahun_kab_ppk_1 . ". Pada tahun " . $tahun_kab_ppk_max . " Pengeluaran Perkapita " . $xnameKab . " adalah sebesar Rp " . number_format($nilaiData_ppk33[$tahun_kab_ppk_max], 0) . ", sedangkan pada tahun " . $tahun_kab_ppk_1 . " Pengeluaran Perkapita tercatat sebesar Rp " . number_format($nilaiData_ppk33[$tahun_kab_ppk_1], 0) . ". ";
                if ($nilaiData_ppk22[$tahun_kab_ppk_max] > $nilaiData_ppk33[$tahun_kab_ppk_max]) {
                    $max_p_ppk  = "Pengeluaran Perkapita " . $xnameKab . " pada tahun " . $tahun_kab_ppk_max . " berada dibawah capaian " . $xname . ". Pengeluaran Perkapita " . $xname . " pada tahun " . $tahun_kab_ppk_max . " adalah sebesar Rp " . number_format($nilaiData_ppk22[$tahun_kab_ppk_max], 0) . ". ";
                } else {
                    $max_p_ppk  = "Pengeluaran Perkapita " . $xnameKab . " pada tahun " . $tahun_kab_ppk_max . " berada diatas capaian " . $xname . ". Pengeluaran Perkapita " . $xname . " pada tahun " . $tahun_kab_ppk_max . " adalah sebesar Rp " . number_format($nilaiData_ppk22[$tahun_kab_ppk_max], 0) . ". ";
                }
            }
            if ($nilaiData_ppk1[$tahun_kab_ppk_max] > $nilaiData_ppk33[$tahun_kab_ppk_max]) {
                $max_k_ppk    = " Capaian Pengeluaran Perkapita " . $xnameKab . " pada tahun " . $tahun_kab_ppk_max . " berada dibawah nasional. Pengeluaran Perkapita nasional pada tahun " . $tahun_kab_ppk_max . " adalah sebesar Rp " . number_format($nilaiData_ppk1[$tahun_kab_ppk_max], 0) . ". ";
            } else {
                $max_k_ppk    = " Capaian Pengeluaran Perkapita " . $xnameKab . " pada tahun " . $tahun_kab_ppk_max . " berada diatas nasional. Pengeluaran Perkapita nasional pada tahun " . $tahun_kab_ppk_max . " adalah sebesar Rp " . number_format($nilaiData_ppk1[$tahun_kab_ppk_max], 0) . ". ";
            }
            $ppk_kab = "select p.nama_kabupaten as label, e.*  from kabupaten p join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='11' AND e.id_periode='" . $periode_kab_ppk_max . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='11' AND id_periode='" . $periode_kab_ppk_max . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_ppk_per = $this->db->query($ppk_kab);
            foreach ($list_kab_ppk_per->result() as $row_ppk_kab_per) {
                $label_ppk[]     = $row_ppk_kab_per->label;
                $nilai_ppk_per[] = $row_ppk_kab_per->nilai;
                $posisi_ppk = strpos($row_ppk_kab_per->label, "Kabupaten");
                if ($posisi_ppk !== FALSE) {
                    $label_ppk11 = substr($row_ppk_kab_per->label, 0, 3) . " " . substr($row_ppk_kab_per->label, 10);
                } else {
                    $label_ppk11 = $row_ppk_kab_per->label;
                }
                $label_ppk1[] = $label_ppk11;
            }
            $label_data_ppk     = $label_ppk1;
            $nilai_data_ppk_per = $nilai_ppk_per;

            //pengeluaran per kapita
            $graph_ppk = new Graph(500, 230);
            $graph_ppk->SetScale("textlin");
            $theme_class_ppk = new UniversalTheme;
            $graph_ppk->SetTheme($theme_class_ppk);
            $graph_ppk->SetMargin(40, 20, 33, 58);
            //$graph_ppk->title->Set('Perkembangan Pengeluaran Per Kapita');
            $graph_ppk->SetBox(false);
            $graph_ppk->yaxis->HideZeroLabel();
            $graph_ppk->yaxis->HideLine(false);
            $graph_ppk->yaxis->HideTicks(false, false);
            $graph_ppk->xaxis->SetTickLabels($tahun_ppk);
            $graph_ppk->ygrid->SetFill(false);

            $graph_bar_ppk = new Graph(600, 230);
            $graph_bar_ppk->img->SetMargin(40, 20, 20, 150);
            $graph_bar_ppk->SetScale("textlin");
            $graph_bar_ppk->SetMarginColor("lightblue:1.1");
            $graph_bar_ppk->SetShadow();
            $graph_bar_ppk->title->SetMargin(8);
            $graph_bar_ppk->title->SetColor("darkred");
            $graph_bar_ppk->ygrid->SetFill(false);
            $graph_bar_ppk->xaxis->SetTickLabels($label_data_ppk);
            $graph_bar_ppk->yaxis->HideLine(false);
            $graph_bar_ppk->yaxis->HideTicks(false, false);
            $graph_bar_ppk->xaxis->SetLabelAngle(90);

            //pengeluaran per kapita
            $p1_ppk = new LinePlot($datay_ppk);
            $graph_ppk->Add($p1_ppk);
            $p1_ppk->SetColor("#0000FF");
            $p1_ppk->SetLegend('Indonesia');
            $p1_ppk->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ppk->mark->SetColor('#0000FF');
            $p1_ppk->mark->SetFillColor('#0000FF');
            $p1_ppk->SetCenter();
            $p1_ppk->value->Show();
            $p1_ppk->value->SetColor('#0000FF');
            $p1_ppk->value->SetFormat('%0.2f. Jt');
            $graph_ppk->legend->SetFrameWeight(1);
            $graph_ppk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ppk->legend->SetMarkAbsSize(8);
            $p2_ppk = new LinePlot($datay_ppk2);
            $graph_ppk->Add($p2_ppk);
            $p2_ppk->SetColor("#000000");
            $p2_ppk->SetLegend($xname);
            $p2_ppk->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_ppk->mark->SetColor('#000000');
            $p2_ppk->mark->SetFillColor('#000000');
            $p2_ppk->value->SetMargin(14);
            $p2_ppk->SetCenter();
            $p2_ppk->value->Show();
            $p2_ppk->value->SetColor('#000000');
            $p2_ppk->value->SetFormat('%0.2f. Jt');


            $p3_ppk = new LinePlot($datay_ppk3);
            $graph_ppk->Add($p3_ppk);
            $p3_ppk->SetColor("#FF0000");
            $p3_ppk->SetLegend($xnameKab);
            $p3_ppk->mark->SetType(MARK_X, '', 1.0);
            $p3_ppk->mark->SetColor('#FF0000');
            $p3_ppk->mark->SetFillColor('#FF0000');
            $p3_ppk->value->SetMargin(14);
            $p3_ppk->SetCenter();
            $p3_ppk->value->Show();
            $p3_ppk->value->SetColor('#FF0000');
            $p3_ppk->value->SetFormat('%0.2f. Jt');
            $p3_ppk->SetStyle("dotted");

            $b1plot_ppk_per = new BarPlot($nilai_data_ppk_per);
            $gbplot_ppk_per = new GroupBarPlot(array($b1plot_ppk_per));
            $graph_bar_ppk->Add($gbplot_ppk_per);
            $b1plot_ppk_per->SetColor("white");
            $b1plot_ppk_per->SetFillColor("#0000FF");

            //pengeluaran Per kapita
            $graph_ppk->Stroke($this->picture . '/' . $picture_ppk . '.png');
            $graph_bar_ppk->Stroke($this->picture . '/' . $picture_ppk_bar . '.png');

            //Tingkat Kemiskinan
            $sql_tk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tk = $this->db->query($sql_tk);
            foreach ($list_tk->result() as $row_tk) {
                $tahun_tk[]    = $bulan[$row_tk->periode] . "-" . $row_tk->tahun;
                $periode_kab_tk_max[]   = $row_tk->id_periode;
                $nilaiData_tk[] = (float)$row_tk->nilai;
                $nilaiData_tk1[$row_tk->id_periode] = (float)$row_tk->nilai;
            }
            $datay_tk = $nilaiData_tk;
            $tahun_tk = $tahun_tk;
            $sql_tk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
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
            $nilaiData_tk3[] = '';
            foreach ($list_tk3->result() as $row_tk3) {
                $n_tk3 = $row_tk3->nilai_kab;
                if ($n_tk3 == 0) {
                    $nilaiData_ktk3 = '-';
                } else {
                    $nilaiData_ktk3 = (float)$n_tk3;
                }
                $nilaiData_tk3[] = $nilaiData_ktk3;
                $sumber_tk       = $row_tk3->sumber_k;
                $periode_tk3[]   = $row_tk3->idperiode;
                $data_tk3[$row_tk3->idperiode]   = $nilaiData_ktk3;
                $per_tk3[$row_tk3->idperiode]    = $bulan[$row_tk3->periode] . " " . $row_tk3->tahun;
            }
            $datay_tk3 = array_reverse($nilaiData_tk3);
            $tahun_kab_tk_max = max($periode_tk3);
            $tahun_kab_tk_1 = $tahun_kab_tk_max - 100;
            $tahun_tk3 = $tahun_tk3;
            $periode_tk_tahun = $per_tk3[$tahun_kab_tk_max];
            if ($data_tk3[$tahun_kab_tk_1] > $data_tk3[$tahun_kab_tk_max]) {
                $max_n_tk    = "Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " menurun dibandingkan dengan " . $per_tk3[$tahun_kab_tk_1] . ". Pada " . $per_tk3[$tahun_kab_tk_max] . " Tingkat Kemiskinan " . $xnameKab . " adalah sebesar " . $data_tk3[$tahun_kab_tk_max] . "%, sedangkan pada tahun " . $per_tk3[$tahun_kab_tk_1] . " Tingkat Kemiskinan tercatat sebesar " . $data_tk3[$tahun_kab_tk_1] . "%. ";
                if ($nilaiData_tk22[$tahun_kab_tk_max] > $data_tk3[$tahun_kab_tk_max]) {
                    $max_p_tk  = "Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " berada dibawah capaian " . $xname . ". Tingkat Kemiskinan " . $xname . " pada " . $per_tk3[$tahun_kab_tk_max] . " adalah sebesar " . $nilaiData_tk22[$tahun_kab_tk_max] . "%. ";
                } else {
                    $max_p_tk  = "Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " berada diatas capaian " . $xname . ". Tingkat Kemiskinan " . $xname . " pada " . $per_tk3[$tahun_kab_tk_max] . " adalah sebesar " . $nilaiData_tk22[$tahun_kab_tk_max] . "%. ";
                }
            } else {
                $max_n_tk    = "Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " meningkat dibandingkan dengan " . $per_tk3[$tahun_kab_tk_1] . ". Pada " . $per_tk3[$tahun_kab_tk_max] . " Tingkat Kemiskinan " . $xnameKab . " adalah sebesar " . $data_tk3[$tahun_kab_tk_max] . "%, sedangkan pada tahun " . $per_tk3[$tahun_kab_tk_1] . " Tingkat Kemiskinan tercatat sebesar " . $data_tk3[$tahun_kab_tk_1] . "%. ";
                if ($nilaiData_tk22[$tahun_kab_tk_max] > $data_tk3[$tahun_kab_tk_max]) {
                    $max_p_tk  = "Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " berada dibawah capaian " . $xname . ". Tingkat Kemiskinan " . $xname . " pada " . $per_tk3[$tahun_kab_tk_max] . " adalah sebesar " . $nilaiData_tk22[$tahun_kab_tk_max] . "%. ";
                } else {
                    $max_p_tk  = "Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " berada diatas capaian " . $xname . ". Tingkat Kemiskinan " . $xname . " pada " . $per_tk3[$tahun_kab_tk_max] . " adalah sebesar " . $nilaiData_tk22[$tahun_kab_tk_max] . "%. ";
                }
            }
            if ($nilaiData_tk1[$tahun_kab_tk_max] > $data_tk3[$tahun_kab_tk_max]) {
                $max_k_tk    = "Capaian Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " berada dibawah nasional. Tingkat Kemiskinan nasional pada " . $per_tk3[$tahun_kab_tk_max] . " adalah sebesar " . $nilaiData_tk1[$tahun_kab_tk_max] . "%. ";
            } else {
                $max_k_tk    = " Capaian Tingkat Kemiskinan " . $xnameKab . " pada " . $per_tk3[$tahun_kab_tk_max] . " berada diatas nasional. Tingkat Kemiskinan nasional pada " . $per_tk3[$tahun_kab_tk_max] . " adalah sebesar " . $nilaiData_tk1[$tahun_kab_tk_max] . ".% ";
            }
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
                $label_tk1[] = $label_tk11;
            }
            $label_data_tk     = $label_tk1;
            $nilai_data_tk_per = $nilai_tk_per;
            //tingkat kemiskinan
            $graph_tk = new Graph(500, 230);
            $graph_tk->SetScale("textlin");
            $theme_class_tk = new UniversalTheme;
            $graph_tk->SetTheme($theme_class_tk);
            $graph_tk->SetMargin(40, 20, 33, 58);
            //$graph_tk->title->Set('Perkembangan Tingkat Kemiskinan');
            $graph_tk->SetBox(false);
            $graph_tk->yaxis->HideZeroLabel();
            $graph_tk->yaxis->HideLine(false);
            $graph_tk->yaxis->HideTicks(false, false);
            $graph_tk->xaxis->SetTickLabels($tahun_tk);
            $graph_tk->ygrid->SetFill(false);

            $graph_bar_tk = new Graph(600, 230);
            $graph_bar_tk->img->SetMargin(40, 20, 20, 150);
            $graph_bar_tk->SetScale("textlin");
            $graph_bar_tk->SetMarginColor("lightblue:1.1");
            $graph_bar_tk->SetShadow();
            $graph_bar_tk->title->SetMargin(8);
            $graph_bar_tk->title->SetColor("darkred");
            $graph_bar_tk->ygrid->SetFill(false);
            $graph_bar_tk->xaxis->SetTickLabels($label_data_tk);
            $graph_bar_tk->yaxis->HideLine(false);
            $graph_bar_tk->yaxis->HideTicks(false, false);
            $graph_bar_tk->xaxis->SetLabelAngle(90);

            //tingkat kemiskinan
            $p1_tk = new LinePlot($datay_tk);
            $graph_tk->Add($p1_tk);
            $p1_tk->SetColor("#0000FF");
            $p1_tk->SetLegend('Indonesia');
            $p1_tk->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_tk->mark->SetColor('#0000FF');
            $p1_tk->mark->SetFillColor('#0000FF');
            $p1_tk->SetCenter();
            $p1_tk->value->Show();
            $p1_tk->value->SetColor('#0000FF');
            $p1_tk->value->SetFormat('%0.2f');

            $graph_tk->legend->SetFrameWeight(1);
            $graph_tk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_tk->legend->SetMarkAbsSize(8);
            $p2_tk = new LinePlot($datay_tk2);
            $graph_tk->Add($p2_tk);
            $p2_tk->SetColor("#000000");
            $p2_tk->SetLegend($xname);
            $p2_tk->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_tk->mark->SetColor('#000000');
            $p2_tk->mark->SetFillColor('#000000');
            $p2_tk->value->SetMargin(14);
            $p2_tk->SetCenter();
            $p2_tk->value->Show();
            $p2_tk->value->SetColor('#000000');
            $p2_tk->value->SetFormat('%0.2f');
            $p3_tk = new LinePlot($datay_tk3);
            $graph_tk->Add($p3_tk);
            $p3_tk->SetColor("#FF0000");
            $p3_tk->SetLegend($xnameKab);
            $p3_tk->mark->SetType(MARK_X, '', 1.0);
            $p3_tk->mark->SetColor('#FF0000');
            $p3_tk->mark->SetFillColor('#FF0000');
            $p3_tk->value->SetMargin(14);
            $p3_tk->SetCenter();
            $p3_tk->value->Show();
            $p3_tk->value->SetColor('#FF0000');
            $p3_tk->value->SetFormat('%0.2f');
            $p3_tk->SetStyle("dotted");

            $b1plot_tk_per = new BarPlot($nilai_data_tk_per);
            $gbplot_tk_per = new GroupBarPlot(array($b1plot_tk_per));
            $graph_bar_tk->Add($gbplot_tk_per);
            $b1plot_tk_per->SetColor("white");
            $b1plot_tk_per->SetFillColor("#0000FF");


            //Tingkat Kemiskinan
            $graph_tk->Stroke($this->picture . '/' . $picture_tk . '.png');
            $graph_bar_tk->Stroke($this->picture . '/' . $picture_tk_bar . '.png');

            //indeks Kedalaman Kemiskinan
            $sql_idk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_idk = $this->db->query($sql_idk);
            foreach ($list_idk->result() as $row_idk) {
                $tahun_idk[]     = $bulan[$row_idk->periode] . "-" . $row_idk->tahun;
                $idperiode_idk   = $row_idk->id_periode;
                $nilaiData_idk[] = (float)$row_idk->nilai;
                $idperiode_idk3[] = $row_idk->id_periode;
            }
            $datay_idk = $nilaiData_idk;
            $tahun_idk = $tahun_idk;
            $sql_idk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
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
                    $nil_idk33 = '-';
                } else {
                    $nil_idk33 = (float)$n_idk3;
                }

                $nil_idk3[] = $nil_idk33;
                $sumber_idk = $row_idk3->sumber_k;
                $per_idk3[$row_idk3->tahun]    = $row_idk3->tahun;
                $per_idk33[$row_idk3->tahun]    = $bulan[$row_idk3->periode] . " " . $row_idk3->tahun;
            }
            $datay_idk3 = array_reverse($nil_idk3);
            $tahun_idk3 = $tahun_idk3;
            $tahun_kab_idk_max = max($per_idk3);
            $periode_ikk_tahun = "Tahun " . $per_idk33[$tahun_kab_idk_max];

            if ($datay_idk3[2] > $datay_idk3[4]) {
                $max_n_ikk    = "Indeks Kedalaman Kemiskinan " . $xnameKab . " pada " . $tahun_idk[4] . " menurun dibandingkan dengan " . $tahun_idk[2] . ". Pada " . $tahun_idk[4] . " Angka Indeks Kedalaman Kemiskinan " . $xnameKab . " adalah sebesar " . $datay_idk3[4] . "%, sedangkan pada tahun " . $tahun_idk[2] . " Angka Indeks Kedalaman Kemiskinan tercatat sebesar " . $datay_idk3[2] . "%. ";
                if ($nilaiData_idk2[4] > $datay_idk3[4]) {
                    $max_p_ikk  = "Indeks Kedalaman Kemiskinan " . $xnameKab . " pada " . $tahun_idk[4] . " berada dibawah capaian " . $xname . ". Angka Indeks Kedalaman Kemiskinan " . $xname . " pada " . $tahun_idk[5] . " adalah sebesar " . $nilaiData_idk2[4] . "%. ";
                } else {
                    $max_p_ikk  = "Indeks Kedalaman Kemiskinan " . $xnameKab . " pada " . $tahun_idk[4] . " berada diatas capaian " . $xname . ". Angka Indeks Kedalaman Kemiskinan " . $xname . " pada " . $tahun_idk[5] . " adalah sebesar " . $nilaiData_idk2[4] . "%. ";
                }
            } else {
                $max_n_ikk    = "Angka Indeks Kedalaman Kemiskinan " . $xnameKab . " pada " . $tahun_idk[4] . " meningkat dibandingkan dengan " . $tahun_idk[2] . ". Pada " . $tahun_idk[4] . " Angka Indeks Kedalaman Kemiskinan " . $xnameKab . " adalah sebesar " . $datay_idk3[4] . "%, sedangkan pada tahun " . $tahun_idk[2] . " Angka Indeks Kedalaman Kemiskinan tercatat sebesar " . $datay_idk3[2] . "%. ";
                if ($nilaiData_idk2[4] > $datay_idk3[4]) {
                    $max_p_ikk  = "Angka Indeks Kedalaman Kemiskinan " . $xnameKab . " pada " . $tahun_idk[4] . " berada dibawah capaian " . $xname . ". Angka Indeks Kedalaman Kemiskinan " . $xname . " pada " . $tahun_idk[5] . " adalah sebesar " . $nilaiData_idk2[4] . "%. ";
                } else {
                    $max_p_ikk  = "Angka Indeks Kedalaman Kemiskinan " . $xnameKab . " pada " . $tahun_idk[5] . " berada diatas capaian " . $xname . ". Angka Indeks Kedalaman Kemiskinan " . $xname . " pada " . $tahun_idk[5] . " adalah sebesar " . $nilaiData_idk2[4] . "%. ";
                }
            }
            if ($datay_idk[4] > $datay_idk3[4]) {
                $max_k_ikk    = "Capaian Angka Indeks Kedalaman Kemiskinan " . $xnameKab . " pada " . $tahun_idk[4] . " berada dibawah nasional. Angka Indeks Kedalaman Kemiskinan nasional pada " . $tahun_idk[4] . " adalah sebesar " . $datay_idk[4] . "%. ";
            } else {
                $max_k_ikk    = " Capaian Angka Indeks Kedalaman Kemiskinan " . $xnameKab . " pada " . $tahun_idk[4] . " berada diatas nasional. Angka Indeks Kedalaman Kemiskinan nasional pada " . $tahun_idk[4] . " adalah sebesar " . $datay_idk[4] . "%. ";
            }
            $ikk_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='39' AND e.id_periode='" . $idperiode_idk3[4] . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='39' AND id_periode='" . $idperiode_idk3[4] . "' group by wilayah ) 
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
            }
            $label_data_ikk     = $label_ikk1;
            $nilai_data_ikk_per = $nilai_ikk_per;
            //Indeks Kedalaman Kemiskinan
            $graph_ikk = new Graph(500, 230);
            $graph_ikk->SetScale("textlin");
            $theme_class_ikk = new UniversalTheme;
            $graph_ikk->SetTheme($theme_class_ikk);
            $graph_ikk->SetMargin(40, 20, 33, 58);
            //$graph_ikk->title->Set('Perkembangan Indeks Kedalaman Kemiskinan');
            $graph_ikk->SetBox(false);
            $graph_ikk->yaxis->HideZeroLabel();
            $graph_ikk->yaxis->HideLine(false);
            $graph_ikk->yaxis->HideTicks(false, false);
            $graph_ikk->xaxis->SetTickLabels($tahun_idk);
            $graph_ikk->ygrid->SetFill(false);

            $graph_bar_ikk = new Graph(600, 230);
            $graph_bar_ikk->img->SetMargin(40, 20, 20, 150);
            $graph_bar_ikk->SetScale("textlin");
            $graph_bar_ikk->SetMarginColor("lightblue:1.1");
            $graph_bar_ikk->SetShadow();
            $graph_bar_ikk->title->SetMargin(8);
            $graph_bar_ikk->title->SetColor("darkred");
            $graph_bar_ikk->ygrid->SetFill(false);
            $graph_bar_ikk->xaxis->SetTickLabels($label_data_ikk);
            $graph_bar_ikk->yaxis->HideLine(false);
            $graph_bar_ikk->yaxis->HideTicks(false, false);
            $graph_bar_ikk->xaxis->SetLabelAngle(90);

            //Indeks Kedalaman Kemiskinan
            $p1_ikk = new LinePlot($datay_idk);
            $graph_ikk->Add($p1_ikk);
            $p1_ikk->SetColor("#0000FF");
            $p1_ikk->SetLegend('Indonesia');
            $p1_ikk->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ikk->mark->SetColor('#0000FF');
            $p1_ikk->mark->SetFillColor('#0000FF');
            $p1_ikk->SetCenter();
            $p1_ikk->value->Show();
            $p1_ikk->value->SetColor('#0000FF');
            $p1_ikk->value->SetFormat('%0.2f');
            $graph_ikk->legend->SetFrameWeight(1);
            $graph_ikk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ikk->legend->SetMarkAbsSize(8);
            $p2_ikk = new LinePlot($datay_idk2);
            $graph_ikk->Add($p2_ikk);
            $p2_ikk->SetColor("#000000");
            $p2_ikk->SetLegend($xname);
            $p2_ikk->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_ikk->mark->SetColor('#000000');
            $p2_ikk->mark->SetFillColor('#000000');
            $p2_ikk->value->SetMargin(14);
            $p2_ikk->SetCenter();
            $p2_ikk->value->Show();
            $p2_ikk->value->SetColor('#000000');
            $p2_ikk->value->SetFormat('%0.2f');
            $p3_ikk = new LinePlot($datay_idk3);
            $graph_ikk->Add($p3_ikk);
            $p3_ikk->SetColor("#FF0000");
            $p3_ikk->SetLegend($xnameKab);
            $p3_ikk->mark->SetType(MARK_X, '', 1.0);
            $p3_ikk->mark->SetColor('#FF0000');
            $p3_ikk->mark->SetFillColor('#FF0000');
            $p3_ikk->value->SetMargin(14);
            $p3_ikk->SetCenter();
            $p3_ikk->value->Show();
            $p3_ikk->value->SetColor('#FF0000');
            $p3_ikk->value->SetFormat('%0.2f');
            $p3_ikk->SetStyle("dotted");

            $b1plot_ikk_per = new BarPlot($nilai_data_ikk_per);
            $gbplot_ikk_per = new GroupBarPlot(array($b1plot_ikk_per));
            $graph_bar_ikk->Add($gbplot_ikk_per);
            $b1plot_ikk_per->SetColor("white");
            $b1plot_ikk_per->SetFillColor("#0000FF");

            //Indeks Kedalaman Kemiskinan
            $graph_ikk->Stroke($this->picture . '/' . $picture_ikk . '.png');
            $graph_bar_ikk->Stroke($this->picture . '/' . $picture_ikk_bar . '.png');



            //jumlah Penduduk Miskin
            $sql_jpk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jpk = $this->db->query($sql_jpk);
            foreach ($list_jpk->result() as $row_jpk) {
                $tahun_jpk[]    = $bulan[$row_jpk->periode] . "-" . $row_jpk->tahun;
                $idperiode_jpk3[]   = $row_jpk->id_periode;
                $nilaiData_jpk[] = (float)$row_jpk->nilai;
            }
            $datay_jpk = $nilaiData_jpk;
            $tahun_jpk = $tahun_jpk;
            $periode_kab_jpk_max = max($idperiode_jpk3);
            $sql_jpk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
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
                                            where (id_indikator='40' AND wilayah='" . $kab . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='40' AND wilayah='" . $kab . "' group by id_periode
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
                $per_jpk3[$row_jpk3->idperiode]    = $bulan[$row_jpk3->periode] . " " . $row_jpk3->tahun;
                $periode_jpm[$row_jpk3->idperiode] = $row_jpk3->idperiode;
            }
            $datay_jpk3 = array_reverse($nil_jpk3);
            $periode_kab_jpk_max = max($periode_jpk3);
            $periode_kab_jpk_1 = $periode_kab_jpk_max - 100;
            $periode_jpk_tahun = "Tahun " . $per_jpk3[$periode_kab_jpk_max];
            //                    print_r($nil_jpk33[$periode_kab_jpk_max]);
            //                    echo '</br>';
            //                    print_r($nil_jpk33[$periode_kab_jpk_1]);
            //                    echo '</br>';
            //                    print_r($per_idk3[$periode_kab_jpk_max]);
            //                    exit();                  
            //$periode_jpk_tahun=max($tahun_jpk21)." Antar Provinsi" ;



            if ($nil_jpk33[$periode_kab_jpk_1] > $nil_jpk33[$periode_kab_jpk_max]) {
                $rt_jpk = abs($nil_jpk33[$periode_kab_jpk_max] - $nil_jpk33[$periode_kab_jpk_1]);
                $rt_jpkk = abs($nil_jpk33[$periode_kab_jpk_max] - $nil_jpk33[$periode_kab_jpk_1]);
                $rt_jpk2 = $rt_jpk / $nil_jpk33[$periode_kab_jpk_1];
                $rt_jpk3 = $rt_jpk2 * 100;
                $rt_jpk33 = number_format($rt_jpk3, 2);
                $max_n_jpk    = "Jumlah Penduduk Miskin di " . $xnameKab . " pada " . $per_jpk3[$periode_kab_jpk_max] . " sebanyak " . number_format($nil_jpk33[$periode_kab_jpk_max], 0) . " orang. Sedangkan jumlah Penduduk Miskin pada " . $per_jpk3[$periode_kab_jpk_1] . " sebanyak " . number_format($nil_jpk33[$periode_kab_jpk_1], 0) . " orang. Selama periode  " . $per_jpk3[$periode_kab_jpk_1] . " - " . $per_jpk3[$periode_kab_jpk_max] . " jumlah Penduduk Miskin di " . $xnameKab . " berkurang " . number_format($rt_jpkk, 0) . " atau sebesar " . $rt_jpk33 . "% ";
            } else {
                $rt_jpk  = $nil_jpk33[$periode_kab_jpk_max] - $nil_jpk33[$periode_kab_jpk_1];
                $rt_jpk2 = $rt_jpk / $datay_jpk33[$periode_kab_jpk_1];
                $rt_jpk3 = $rt_jpk2 * 100;
                $rt_jpk33 = number_format($rt_jpk3, 2);
                $max_n_jpk    = "Jumlah Penduduk Miskin di " . $xnameKab . " pada " . $per_jpk3[$periode_kab_jpk_max] . " sebanyak " . number_format($nil_jpk33[$periode_kab_jpk_max], 0) . " orang. Sedangkan jumlah Penduduk Miskin pada " . $per_jpk3[$periode_kab_jpk_1] . " sebanyak " . number_format($nil_jpk33[$periode_kab_jpk_1], 0) . " orang. Selama periode  " . $per_jpk3[$periode_kab_jpk_1] . " - " . $per_jpk3[$periode_kab_jpk_max] . " jumlah Penduduk Miskin di " . $xnameKab . " meningkat " . number_format($rt_jpk, 0) . " atau sebesar " . $rt_jpk33 . "%";
            }
            $jpk2_kab = "select p.nama_kabupaten as label, e.* 
                            from kabupaten p
                            join nilai_indikator e on p.id = e.wilayah 
                            where p.prov_id='" . $provinsi . "' and (e.id_indikator='40' AND e.id_periode='" . $periode_jpm[$periode_kab_jpk_max] . "') AND (wilayah, versi) in (
                               select x.wilayah, max(x.versi) as versi from nilai_indikator x  where id_indikator='40' AND id_periode='" . $periode_jpm[$periode_kab_jpk_max] . "' group by wilayah ) 
                           group by wilayah order by wilayah asc";
            $list_kab_jpk_per = $this->db->query($jpk2_kab);
            foreach ($list_kab_jpk_per->result() as $row_jpk_kab_per) {
                $label_jpk[]     = $row_jpk_kab_per->label;
                $nilai_jpk_per[] = $row_jpk_kab_per->nilai;
                $posisi_jpk = strpos($row_jpk_kab_per->label, "Kabupaten");
                if ($posisi_jpk !== FALSE) {
                    $label_jpk11 = substr($row_jpk_kab_per->label, 0, 3) . " " . substr($row_jpk_kab_per->label, 10);
                } else {
                    $label_jpk11 = $row_jpk_kab_per->label;
                }
                $label_jpk1[] = $label_jpk11;
            }
            $label_data_jpk     = $label_jpk1;
            $nilai_data_jpk_per = $nilai_jpk_per;

            //Jumlah Penduduk Miskin                
            $graph_jpk = new Graph(650, 250);
            $graph_jpk->SetScale("textlin");
            $graph_jpk->SetY2Scale("lin", 0, 90);
            $graph_jpk->SetY2OrderBack(false);
            $theme_class_jpk = new UniversalTheme;
            $graph_jpk->SetTheme($theme_class_jpk);
            $graph_jpk->SetMargin(120, 60, 33, 60);
            $graph_jpk->ygrid->SetFill(false);
            $graph_jpk->xaxis->SetTickLabels($tahun_jpk);
            $graph_jpk->yaxis->HideLine(false);
            $graph_jpk->yaxis->HideTicks(false, false);
            $graph_jpk->title->Set("");

            $graph_bar_jpk = new Graph(600, 230);
            $graph_bar_jpk->img->SetMargin(120, 20, 20, 150);
            $graph_bar_jpk->SetScale("textlin");
            $graph_bar_jpk->SetMarginColor("lightblue:1.1");
            $graph_bar_jpk->SetShadow();
            $graph_bar_jpk->title->SetMargin(8);
            $graph_bar_jpk->title->SetColor("darkred");
            $graph_bar_jpk->ygrid->SetFill(false);
            $graph_bar_jpk->xaxis->SetTickLabels($label_data_jpk);
            $graph_bar_jpk->yaxis->HideLine(false);
            $graph_bar_jpk->yaxis->HideTicks(false, false);
            $graph_bar_jpk->xaxis->SetLabelAngle(90);
            //Jumlah Penduduk Miskin
            $b1plot_jpk = new BarPlot($datay_jpk);
            $b1plot2_jpk = new BarPlot($datay_jpk2);
            $b1plot3_jpk = new BarPlot($datay_jpk3);
            $gbplot_jpk = new GroupBarPlot(array($b1plot3_jpk));
            $graph_jpk->Add($gbplot_jpk);
            $b1plot_jpk->SetColor("white");
            $b1plot_jpk->SetFillColor("#0000FF");
            $b1plot_jpk->SetLegend("Indonesia");
            $b1plot_jpk->SetWidth(20);
            $b1plot2_jpk->SetColor("white");
            $b1plot2_jpk->SetFillColor("#000000");
            $b1plot2_jpk->SetLegend($xname);
            $b1plot2_jpk->SetWidth(20);

            $b1plot3_jpk->SetColor("white");
            $b1plot3_jpk->SetFillColor("#00FF00");
            $b1plot3_jpk->SetLegend($xnameKab);
            $b1plot3_jpk->SetWidth(20);

            $b1plot_jpk_per = new BarPlot($nilai_data_jpk_per);
            $gbplot_jpk_per = new GroupBarPlot(array($b1plot_jpk_per));
            $graph_bar_jpk->Add($gbplot_jpk_per);
            $b1plot_jpk_per->SetColor("white");
            $b1plot_jpk_per->SetFillColor("#0000FF");


            //Jumlah Penduduk Miskin
            $graph_jpk->Stroke($this->picture . '/' . $picture_jpk . '.png');
            $graph_bar_jpk->Stroke($this->picture . '/' . $picture_jpk_bar . '.png');
        }





        if ($provinsi == '' & $kabupaten == '') {
            //perkembangan pertumbuhan ekonomi
            //$p1_ppe = new LinePlot($nl);
            $p1_ppe = new LinePlot($nilaiData1);
            $graph_ppe->Add($p1_ppe);
            $p1_ppe->SetColor("#0000FF");
            $p1_ppe->SetLegend('Indonesia');
            $p1_ppe->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ppe->mark->SetColor('#0000FF');
            $p1_ppe->mark->SetFillColor('#0000FF');
            $p1_ppe->SetCenter();
            $p1_ppe->value->Show();
            //$p1_ppe->grid->SetColor('darkgrey');
            //$p1_ppe->SetFormat('$%01.2f'); 
            //$graph_ppe->legend->SetFormat('%d');
            $p1_ppe->value->SetAlign('center');
            $graph_ppe->legend->SetFrameWeight(2);
            $graph_ppe->legend->SetColor('#4E4E4E', '#00A78A');
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
            $adhk1->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $adhk1->mark->SetColor('#0000FF');
            $adhk1->mark->SetFillColor('#0000FF');
            $adhk1->SetCenter();
            $adhk1->value->Show();
            //$p1_ppe->SetFormt('$%01.2f'); 
            $graph_ppe->legend->SetFrameWeight(2);
            $graph_ppe->legend->SetColor('#4E4E4E', '#00A78A');
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
            $p1_ipm->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ipm->mark->SetColor('#0000FF');
            $p1_ipm->mark->SetFillColor('#0000FF');
            $p1_ipm->SetCenter();
            $p1_ipm->value->Show();
            $graph_ipm->legend->SetFrameWeight(1);
            $graph_ipm->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ipm->legend->SetMarkAbsSize(8);
            //Gini Rasio
            $p1_gr = new LinePlot($datay_gr);
            $graph_gr->Add($p1_gr);
            $p1_gr->SetColor("#0000FF");
            $p1_gr->SetLegend('Indonesia');
            $p1_gr->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_gr->mark->SetColor('#0000FF');
            $p1_gr->mark->SetFillColor('#0000FF');
            $p1_gr->SetCenter();
            $p1_gr->value->Show();
            $graph_gr->legend->SetFrameWeight(1);
            $graph_gr->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_gr->legend->SetMarkAbsSize(8);
            //Angka Harapan Hidup
            $p1_ahh = new LinePlot($datay_ahh);
            $graph_ahh->Add($p1_ahh);
            $p1_ahh->SetColor("#0000FF");
            $p1_ahh->SetLegend('Indonesia');
            $p1_ahh->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ahh->mark->SetColor('#0000FF');
            $p1_ahh->mark->SetFillColor('#0000FF');
            $p1_ahh->SetCenter();
            $p1_ahh->value->Show();
            $graph_ahh->legend->SetFrameWeight(1);
            $graph_ahh->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ahh->legend->SetMarkAbsSize(8);
            //Rata-rata Lama Sekolah
            $p1_rls = new LinePlot($datay_rls);
            $graph_rls->Add($p1_rls);
            $p1_rls->SetColor("#0000FF");
            $p1_rls->SetLegend('Indonesia');
            $p1_rls->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_rls->mark->SetColor('#0000FF');
            $p1_rls->mark->SetFillColor('#0000FF');
            $p1_rls->SetCenter();
            $p1_rls->value->Show();
            $graph_rls->legend->SetFrameWeight(1);
            $graph_rls->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_rls->legend->SetMarkAbsSize(8);
            //Rata-rata Lama Sekolah
            $p1_hls = new LinePlot($datay_hls);
            $graph_hls->Add($p1_hls);
            $p1_hls->SetColor("#0000FF");
            $p1_hls->SetLegend('Indonesia');
            $p1_hls->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_hls->mark->SetColor('#0000FF');
            $p1_hls->mark->SetFillColor('#0000FF');
            $p1_hls->SetCenter();
            $p1_hls->value->Show();
            $graph_hls->legend->SetFrameWeight(1);
            $graph_hls->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_hls->legend->SetMarkAbsSize(8);
            //pengeluaran per kapita
            $p1_ppk = new LinePlot($datay_ppk);
            $graph_ppk->Add($p1_ppk);
            $p1_ppk->SetColor("#0000FF");
            $p1_ppk->SetLegend('Indonesia');
            $p1_ppk->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ppk->mark->SetColor('#0000FF');
            $p1_ppk->mark->SetFillColor('#0000FF');
            $p1_ppk->SetCenter();
            $p1_ppk->value->Show();
            $graph_ppk->legend->SetFrameWeight(1);
            $graph_ppk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ppk->legend->SetMarkAbsSize(8);
            //tingkat kemiskinan
            $p1_tk = new LinePlot($datay_tk);
            $graph_tk->Add($p1_tk);
            $p1_tk->SetColor("#0000FF");
            $p1_tk->SetLegend('Indonesia');
            $p1_tk->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_tk->mark->SetColor('#0000FF');
            $p1_tk->mark->SetFillColor('#0000FF');
            $p1_tk->SetCenter();
            $p1_tk->value->Show();
            $graph_tk->legend->SetFrameWeight(1);
            $graph_tk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_tk->legend->SetMarkAbsSize(8);
            //Indeks Kedalaman Kemiskinan
            $p1_ikk = new LinePlot($datay_idk);
            $graph_ikk->Add($p1_ikk);
            $p1_ikk->SetColor("#0000FF");
            $p1_ikk->SetLegend('Indonesia');
            $p1_ikk->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ikk->mark->SetColor('#0000FF');
            $p1_ikk->mark->SetFillColor('#0000FF');
            $p1_ikk->SetCenter();
            $p1_ikk->value->Show();
            $graph_ikk->legend->SetFrameWeight(1);
            $graph_ikk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ikk->legend->SetMarkAbsSize(8);
            //Jumlah Penduduk Miskin
            $p1_jpk = new LinePlot($datay_jpk);
            $graph_jpk->Add($p1_jpk);
            $p1_jpk->SetColor("#0000FF");
            $p1_jpk->SetLegend('Indonesia');
            $p1_jpk->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_jpk->mark->SetColor('#0000FF');
            $p1_jpk->mark->SetFillColor('#0000FF');
            $p1_jpk->SetCenter();
            $p1_jpk->value->Show();
            $graph_jpk->legend->SetFrameWeight(1);
            $graph_jpk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_jpk->legend->SetMarkAbsSize(8);
        } elseif ($provinsi != '' & $kabupaten == '') {


            //                   
            //                    //adhk
            $adhk1 = new LinePlot($datay_adhk1);
            $graph_adhk->Add($adhk1);
            $adhk1->SetColor("#0000FF");
            $adhk1->value->Show('Jt');
            $adhk1->value->SetColor('#0000FF');
            $adhk1->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $adhk1->mark->SetColor('#0000FF');
            $adhk1->mark->SetFillColor('#55bbdd');
            $adhk1->SetCenter();
            $adhk1->SetLegend("Indonesia");
            $adhk1->value->SetFormat('%0.2f. Jt');
            $graph_adhk->legend->SetFrameWeight(1);
            $graph_adhk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_adhk->legend->SetMarkAbsSize(8);

            $adhk2 = new LinePlot($datay_adhk2);
            $graph_adhk->Add($adhk2);
            $adhk2->SetColor("#000000");
            $adhk2->SetLegend($xname);
            $adhk2->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $adhk2->mark->SetColor('#000000');
            $adhk2->mark->SetFillColor('#000000');
            $adhk2->value->SetMargin(14);
            $adhk2->SetCenter();
            $adhk2->value->Show();
            $adhk2->value->SetColor('#000000');
            $adhk2->value->SetFormat('%0.2f. Jt');
            $adhk2->SetStyle("dotted");

            $b1plot_adhk_per = new BarPlot($nilai_data_adhk_per);
            $gbplot_adhk_per = new GroupBarPlot(array($b1plot_adhk_per));
            $graph_bar_adhk->Add($gbplot_adhk_per);
            $b1plot_adhk_per->SetColor("white");
            $b1plot_adhk_per->SetFillColor("#0000FF");
            //                    
            //                    

            //tingkat pengangguran terbuka                    
            $p1_tpt = new LinePlot($datay_tpt);
            $graph_tpt->Add($p1_tpt);
            $p1_tpt->SetColor("#0000FF");
            $p1_tpt->SetLegend('Indonesia');
            $p1_tpt->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_tpt->mark->SetColor('#0000FF');
            $p1_tpt->mark->SetFillColor('#0000FF');
            $p1_tpt->SetCenter();
            $p1_tpt->value->Show();
            $p1_tpt->value->SetColor('#0000FF');
            $p1_tpt->value->SetFormat('%0.2f');
            $graph_tpt->legend->SetFrameWeight(1);
            $graph_tpt->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_tpt->legend->SetMarkAbsSize(8);
            $p2_tpt = new LinePlot($datay_tpt2);
            $graph_tpt->Add($p2_tpt);
            $p2_tpt->SetColor("#000000");
            $p2_tpt->SetLegend($xname);
            $p2_tpt->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_tpt->mark->SetColor('#000000');
            $p2_tpt->mark->SetFillColor('#000000');
            $p2_tpt->value->SetMargin(14);
            $p2_tpt->SetCenter();
            $p2_tpt->value->Show();
            $p2_tpt->value->SetFormat('%0.2f');
            $p2_tpt->SetStyle("dotted");

            $b1plot_tpt_per = new BarPlot($nilai_data_tpt_per);
            $gbplot_tpt_per = new GroupBarPlot(array($b1plot_tpt_per));
            $graph_bar_tpt->Add($gbplot_tpt_per);
            $b1plot_tpt_per->SetColor("white");
            $b1plot_tpt_per->SetFillColor("#0000FF");
            $b1plot_tpt_per->value->SetFormat('%0.2f');

            //Indeks Pembangunan Manusia
            $p1_ipm = new LinePlot($datay_ipm);
            $graph_ipm->Add($p1_ipm);
            $p1_ipm->SetColor("#0000FF");
            $p1_ipm->SetLegend('Indonesia');
            $p1_ipm->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ipm->mark->SetColor('#0000FF');
            $p1_ipm->mark->SetFillColor('#0000FF');
            $p1_ipm->SetCenter();
            $p1_ipm->value->Show();
            $p1_ipm->value->SetColor('#0000FF');
            $p1_ipm->value->SetFormat('%0.2f');
            $graph_ipm->legend->SetFrameWeight(1);
            $graph_ipm->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ipm->legend->SetMarkAbsSize(8);
            $p2_ipm = new LinePlot($datay_ipm2);
            $graph_ipm->Add($p2_ipm);
            $p2_ipm->SetColor("#000000");
            $p2_ipm->SetLegend($xname);
            $p2_ipm->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_ipm->mark->SetColor('#000000');
            $p2_ipm->mark->SetFillColor('#000000');
            $p2_ipm->value->SetMargin(14);
            $p2_ipm->SetCenter();
            $p2_ipm->value->Show();
            $p2_ipm->value->SetFormat('%0.2f');
            $p2_ipm->SetStyle("dotted");

            $b1plot_ipm_per = new BarPlot($nilai_data_ipm_per);
            $gbplot_ipm_per = new GroupBarPlot(array($b1plot_ipm_per));
            $graph_bar_ipm->Add($gbplot_ipm_per);
            $b1plot_ipm_per->SetColor("white");
            $b1plot_ipm_per->SetFillColor("#0000FF");

            //                    //Gini Rasio
            $p1_gr = new LinePlot($datay_gr);
            $graph_gr->Add($p1_gr);
            $p1_gr->SetColor("#0000FF");
            $p1_gr->SetLegend('Indonesia');
            $p1_gr->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_gr->mark->SetColor('#0000FF');
            $p1_gr->mark->SetFillColor('#0000FF');
            $p1_gr->SetCenter();
            $p1_gr->value->Show();
            $p1_gr->value->SetColor('#0000FF');
            $p1_gr->value->SetFormat('%0.3f');
            $graph_gr->legend->SetFrameWeight(1);
            $graph_gr->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_gr->legend->SetMarkAbsSize(8);
            $p2_gr = new LinePlot($datay_gr2);
            $graph_gr->Add($p2_gr);
            $p2_gr->SetColor("#000000");
            $p2_gr->SetLegend($xname);
            $p2_gr->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_gr->mark->SetColor('#000000');
            $p2_gr->mark->SetFillColor('#000000');
            $p2_gr->value->SetMargin(14);
            $p2_gr->SetCenter();
            $p2_gr->value->Show();
            $p2_gr->value->SetColor('#000000');
            $p2_gr->value->SetFormat('%0.3f');
            $p2_gr->SetStyle("dotted");

            $b1plot_gr_per = new BarPlot($nilai_data_gr_per);
            $gbplot_gr_per = new GroupBarPlot(array($b1plot_gr_per));
            $graph_bar_gr->Add($gbplot_gr_per);
            $b1plot_gr_per->SetColor("white");
            $b1plot_gr_per->SetFillColor("#0000FF");
            //$b1plot_gr_per->value->Show();
            $b1plot_gr_per->value->SetFormat('%0.3f');

            //                    //Angka Harapan Hidup
            $p1_ahh = new LinePlot($datay_ahh);
            $graph_ahh->Add($p1_ahh);
            $p1_ahh->SetColor("#0000FF");
            $p1_ahh->SetLegend('Indonesia');
            $p1_ahh->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ahh->mark->SetColor('#0000FF');
            $p1_ahh->mark->SetFillColor('#0000FF');
            $p1_ahh->SetCenter();
            $p1_ahh->value->Show();
            $p1_ahh->value->SetColor('#0000FF');
            $p1_ahh->value->SetFormat('%0.2f');
            $graph_ahh->legend->SetFrameWeight(1);
            $graph_ahh->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ahh->legend->SetMarkAbsSize(8);
            $p2_ahh = new LinePlot($datay_ahh2);
            $graph_ahh->Add($p2_ahh);
            $p2_ahh->SetColor("#000000");
            $p2_ahh->SetLegend($xname);
            $p2_ahh->mark->SetType(MARK_UTRIANGLE, '', 2.0);
            $p2_ahh->mark->SetColor('#000000');
            $p2_ahh->mark->SetFillColor('#000000');
            $p2_ahh->value->SetMargin(14);
            $p2_ahh->SetCenter();
            $p2_ahh->value->Show();
            $p2_ahh->value->SetColor('#000000');
            $p2_ahh->value->SetFormat('%0.2f');
            $p2_ahh->SetStyle("dotted");

            $b1plot_ahh_per = new BarPlot($nilai_data_ahh_per);
            $gbplot_ahh_per = new GroupBarPlot(array($b1plot_ahh_per));
            $graph_bar_ahh->Add($gbplot_ahh_per);
            $b1plot_ahh_per->SetColor("white");
            $b1plot_ahh_per->SetFillColor("#0000FF");

            //                    //Rata-rata Lama Sekolah
            $p1_rls = new LinePlot($datay_rls);
            $graph_rls->Add($p1_rls);
            $p1_rls->SetColor("#0000FF");
            $p1_rls->SetLegend('Indonesia');
            $p1_rls->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_rls->mark->SetColor('#0000FF');
            $p1_rls->mark->SetFillColor('#0000FF');
            $p1_rls->SetCenter();
            $p1_rls->value->Show();
            $p1_rls->value->SetColor('#0000FF');
            $p1_rls->value->SetFormat('%0.2f');
            $graph_rls->legend->SetFrameWeight(1);
            $graph_rls->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_rls->legend->SetMarkAbsSize(8);
            $p2_rls = new LinePlot($datay_rls2);
            $graph_rls->Add($p2_rls);
            $p2_rls->SetColor("#000000");
            $p2_rls->SetLegend($xname);
            $p2_rls->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_rls->mark->SetColor('#000000');
            $p2_rls->mark->SetFillColor('#000000');
            $p2_rls->value->SetMargin(14);
            $p2_rls->SetCenter();
            $p2_rls->value->Show();
            $p2_rls->value->SetColor('#000000');
            $p2_rls->value->SetFormat('%0.2f');
            $p2_rls->SetStyle("dotted");

            $b1plot_rls_per = new BarPlot($nilai_data_rls_per);
            $gbplot_rls_per = new GroupBarPlot(array($b1plot_rls_per));
            $graph_bar_rls->Add($gbplot_rls_per);
            $b1plot_rls_per->SetColor("white");
            $b1plot_rls_per->SetFillColor("#0000FF");

            //                    //harapan Lama Sekolah
            $p1_hls = new LinePlot($datay_hls);
            $graph_hls->Add($p1_hls);
            $p1_hls->SetColor("#0000FF");
            $p1_hls->SetLegend('Indonesia');
            $p1_hls->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_hls->mark->SetColor('#0000FF');
            $p1_hls->mark->SetFillColor('#0000FF');
            $p1_hls->SetCenter();
            $p1_hls->value->Show();
            $p1_hls->value->SetColor('#0000FF');
            $p1_hls->value->SetFormat('%0.2f');
            $graph_hls->legend->SetFrameWeight(1);
            $graph_hls->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_hls->legend->SetMarkAbsSize(8);
            $p2_hls = new LinePlot($datay_hls2);
            $graph_hls->Add($p2_hls);
            $p2_hls->SetColor("#000000");
            $p2_hls->SetLegend($xname);
            $p2_hls->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_hls->mark->SetColor('#000000');
            $p2_hls->mark->SetFillColor('#000000');
            $p2_hls->value->SetMargin(14);
            $p2_hls->SetCenter();
            $p2_hls->value->Show();
            $p2_hls->value->SetColor('#000000');
            $p2_hls->value->SetFormat('%0.2f');
            $p2_hls->SetStyle("dotted");

            $b1plot_hls_per = new BarPlot($nilai_data_hls_per);
            $gbplot_hls_per = new GroupBarPlot(array($b1plot_hls_per));
            $graph_bar_hls->Add($gbplot_hls_per);
            $b1plot_hls_per->SetColor("white");
            $b1plot_hls_per->SetFillColor("#0000FF");

            //                    //pengeluaran per kapita
            $p1_ppk = new LinePlot($datay_ppk);
            $graph_ppk->Add($p1_ppk);
            $p1_ppk->SetColor("#0000FF");
            $p1_ppk->SetLegend('Indonesia');
            $p1_ppk->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ppk->mark->SetColor('#0000FF');
            $p1_ppk->mark->SetFillColor('#0000FF');
            $p1_ppk->SetCenter();
            $p1_ppk->value->Show();
            $p1_ppk->value->SetColor('#0000FF');
            $p1_ppk->value->SetFormat('%0.2f. Jt');
            $graph_ppk->legend->SetFrameWeight(1);
            $graph_ppk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ppk->legend->SetMarkAbsSize(8);
            $p2_ppk = new LinePlot($datay_ppk2);
            $graph_ppk->Add($p2_ppk);
            $p2_ppk->SetColor("#000000");
            $p2_ppk->SetLegend($xname);
            $p2_ppk->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_ppk->mark->SetColor('#000000');
            $p2_ppk->mark->SetFillColor('#000000');
            $p2_ppk->value->SetMargin(14);
            $p2_ppk->SetCenter();
            $p2_ppk->value->Show();
            $p2_ppk->value->SetColor('#000000');
            $p2_ppk->value->SetFormat('%0.2f. Jt');
            $p2_ppk->SetStyle("dotted");

            $b1plot_ppk_per = new BarPlot($nilai_data_ppk_per);
            $gbplot_ppk_per = new GroupBarPlot(array($b1plot_ppk_per));
            $graph_bar_ppk->Add($gbplot_ppk_per);
            $b1plot_ppk_per->SetColor("white");
            $b1plot_ppk_per->SetFillColor("#0000FF");

            //                    //tingkat kemiskinan
            $p1_tk = new LinePlot($datay_tk);
            $graph_tk->Add($p1_tk);
            $p1_tk->SetColor("#0000FF");
            $p1_tk->SetLegend('Indonesia');
            $p1_tk->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_tk->mark->SetColor('#0000FF');
            $p1_tk->mark->SetFillColor('#0000FF');
            $p1_tk->SetCenter();
            $p1_tk->value->Show();
            $p1_tk->value->SetColor('#0000FF');
            $p1_tk->value->SetFormat('%0.2f');
            $graph_tk->legend->SetFrameWeight(1);
            $graph_tk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_tk->legend->SetMarkAbsSize(8);
            $p2_tk = new LinePlot($datay_tk2);
            $graph_tk->Add($p2_tk);
            $p2_tk->SetColor("#000000");
            $p2_tk->SetLegend($xname);
            $p2_tk->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_tk->mark->SetColor('#000000');
            $p2_tk->mark->SetFillColor('#000000');
            $p2_tk->value->SetMargin(14);
            $p2_tk->SetCenter();
            $p2_tk->value->Show();
            $p2_tk->value->SetColor('#000000');
            $p2_tk->value->SetFormat('%0.2f');
            $p2_tk->SetStyle("dotted");

            $b1plot_tk_per = new BarPlot($nilai_data_tk_per);
            $gbplot_tk_per = new GroupBarPlot(array($b1plot_tk_per));
            $graph_bar_tk->Add($gbplot_tk_per);
            $b1plot_tk_per->SetColor("white");
            $b1plot_tk_per->SetFillColor("#0000FF");

            //                    //Indeks Kedalaman Kemiskinan
            $p1_ikk = new LinePlot($datay_idk);
            $graph_ikk->Add($p1_ikk);
            $p1_ikk->SetColor("#0000FF");
            $p1_ikk->SetLegend('Indonesia');
            $p1_ikk->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
            $p1_ikk->mark->SetColor('#0000FF');
            $p1_ikk->mark->SetFillColor('#0000FF');
            $p1_ikk->SetCenter();
            $p1_ikk->value->Show();
            $p1_ikk->value->SetColor('#0000FF');
            $p1_ikk->value->SetFormat('%0.3f');
            $graph_ikk->legend->SetFrameWeight(1);
            $graph_ikk->legend->SetColor('#4E4E4E', '#00A78A');
            $graph_ikk->legend->SetMarkAbsSize(8);
            $p2_ikk = new LinePlot($datay_idk2);
            $graph_ikk->Add($p2_ikk);
            $p2_ikk->SetColor("#000000");
            $p2_ikk->SetLegend($xname);
            $p2_ikk->mark->SetType(MARK_UTRIANGLE, '', 1.0);
            $p2_ikk->mark->SetColor('#000000');
            $p2_ikk->mark->SetFillColor('#000000');
            $p2_ikk->value->SetMargin(14);
            $p2_ikk->SetCenter();
            $p2_ikk->value->Show();
            $p2_ikk->value->SetColor('#000000');
            $p2_ikk->value->SetFormat('%0.3f');
            $p2_ikk->SetStyle("dotted");

            $b1plot_ikk_per = new BarPlot($nilai_data_ikk_per);
            $gbplot_ikk_per = new GroupBarPlot(array($b1plot_ikk_per));
            $graph_bar_ikk->Add($gbplot_ikk_per);
            $b1plot_ikk_per->SetColor("white");
            $b1plot_ikk_per->SetFillColor("#0000FF");

            //                    //Jumlah Penduduk Miskin

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

            //PERKEMBANGAN PERTUMBUHAN EKONOMI
            $graph_ppe->Stroke($this->picture . '/' . $picture_pe . '.png');
            $graph_bar_ppe->Stroke($this->picture . '/' . $picture_pe_bar . '.png');
            //perkembangan PDRB per kapita ADHK
            $graph_adhb->Stroke($this->picture . '/' . $picture_adhb . '.png');
            $graph_bar_adhb->Stroke($this->picture . '/' . $picture_adhb_bar . '.png');
            //                //adhk
            //                $graph_adhk->legend->SetFrameWeight(1);
            $graph_adhk->Stroke($this->picture . '/' . $picture_adhk . '.png');
            $graph_bar_adhk->Stroke($this->picture . '/' . $picture_adhk_bar . '.png');
            //                //jumlah pengangguran
            $graph_jp->Stroke($this->picture . '/' . $picture_jp . '.png');
            $graph_bar_jp->Stroke($this->picture . '/' . $picture_jp_bar . '.png');
            //tingkat pengangguran terbuka
            $graph_tpt->Stroke($this->picture . '/' . $picture_tpt . '.png');
            $graph_bar_tpt->Stroke($this->picture . '/' . $picture_tpt_bar . '.png');
            //Indeks Pembangunan Manusia
            $graph_ipm->Stroke($this->picture . '/' . $picture_ipm . '.png');
            $graph_bar_ipm->Stroke($this->picture . '/' . $picture_ipm_bar . '.png');
            //Gini Rasio
            $graph_gr->Stroke($this->picture . '/' . $picture_gr . '.png');
            $graph_bar_gr->Stroke($this->picture . '/' . $picture_gr_bar . '.png');
            //angka Harapan Hidup
            $graph_ahh->Stroke($this->picture . '/' . $picture_ahh . '.png');
            $graph_bar_ahh->Stroke($this->picture . '/' . $picture_ahh_bar . '.png');
            //Rata-rata Lama Sekolah
            $graph_rls->Stroke($this->picture . '/' . $picture_rls . '.png');
            $graph_bar_rls->Stroke($this->picture . '/' . $picture_rls_bar . '.png');
            //Harapan Lama Sekolah
            $graph_hls->Stroke($this->picture . '/' . $picture_hls . '.png');
            $graph_bar_hls->Stroke($this->picture . '/' . $picture_hls_bar . '.png');
            //pengeluaran Per kapita
            $graph_ppk->Stroke($this->picture . '/' . $picture_ppk . '.png');
            $graph_bar_ppk->Stroke($this->picture . '/' . $picture_ppk_bar . '.png');
            //Tingkat Kemiskinan
            $graph_tk->Stroke($this->picture . '/' . $picture_tk . '.png');
            $graph_bar_tk->Stroke($this->picture . '/' . $picture_tk_bar . '.png');
            //Indeks Kedalaman Kemiskinan
            $graph_ikk->Stroke($this->picture . '/' . $picture_ikk . '.png');
            $graph_bar_ikk->Stroke($this->picture . '/' . $picture_ikk_bar . '.png');
            //Jumlah Penduduk Miskin
            $graph_jpk->Stroke($this->picture . '/' . $picture_jpk . '.png');
            $graph_bar_jpk->Stroke($this->picture . '/' . $picture_jpk_bar . '.png');
        } elseif ($provinsi != '' & $kabupaten != '') {
        }

        $logo         = "logo_bappenas.png";
        $halaman_satu = $this->view_dir . "/halaman_satu";

        $data_page = array(
            "halaman_satu"      => $halaman_satu,
            "logo_picture"      => base_url("assets/js/img/" . $logo),
            "daftar_isi  "      => base_url("assets/js/img/" . $daftar_isi),
            "logo_provinsi"     => base_url("assets/images/logopropinsi/" . $logopro),
            "judul"             => $judul,
            "halaman_PE"        => base_url("picture/laporan_ppd/" . $picture_pe . ".png"),
            "capaian_n_pe"      => $max_pe,
            "capaian_p_pe"      => $max_pe_p,
            "capaian_k_pe"      => $max_pe_k,
            "desc_peek"         => $peek_d,
            "sumber_pe"         => $sumber_pe,
            "tahun_max_pe"      => $tahun_pe_max,
            "perbandingan_PE"   => base_url("picture/laporan_ppd/" . $picture_pe_bar . ".png"),
            "halaman_ADHB"      => base_url("picture/laporan_ppd/" . $picture_adhb . ".png"),
            "capaian_n_adhb"    => $max_adhb,
            "capaian_p_adhb"    => $max_adhb_p,
            "capaian_k_adhb"    => $max_adhb_k,
            "tahun_adhb"        => $tahunadhb,
            "desc_adhb"         => $adhb_d,
            "sumber_adhb"       => $sumber_adhb,
            "tahun_max_adhb"    => $periode_adhb_tahun,
            "perbandingan_adhb" => base_url("picture/laporan_ppd/" . $picture_adhb_bar . ".png"),
            "capaian_n_pdrb"    => $max_pdrb,
            "halaman_ADHk"      => base_url("picture/laporan_ppd/" . $picture_adhk . ".png"),
            "capaian_n_adhk"    => $max_adhk,
            "capaian_p_adhk"    => $max_adhk_p,
            "capaian_k_adhk"    => $max_adhk_k,
            "sumber_adhk"       => $sumber_adhk,
            "desc_adhk"         => $adhk_d,
            "tahun_max_adhk"    => $periode_adhk_tahun,
            "perbandingan_adhk" => base_url("picture/laporan_ppd/" . $picture_adhk_bar . ".png"),
            "halaman_jp"        => base_url("picture/laporan_ppd/" . $picture_jp . ".png"),
            "sumber_jp"         => $sumber_jp,
            "capaian_n_jp"      => $max_jp,
            "capaian_p_jp"      => $max_jp_p,
            "capaian_k_jp"      => $max_jp_k,
            "desc_jp"          =>  $jp_d,
            "tahun_max_jp"      => $periode_jp_tahun,
            "perbandingan_jp"   => base_url("picture/laporan_ppd/" . $picture_jp_bar . ".png"),
            "halaman_tpt"       => base_url("picture/laporan_ppd/" . $picture_tpt . ".png"),
            "sumber_tpt"        => $sumber_tpt,
            "capaian_n_tpt"     => $max_tpt,
            "capaian_p_tpt"     => $max_tpt_p,
            "capaian_k_tpt"     => $max_tpt_k,
            "desc_tpt"          => $tpt_d,
            "tahun_max_tpt"      => $periode_tpt_tahun,
            "perbandingan_tpt"   => base_url("picture/laporan_ppd/" . $picture_tpt_bar . ".png"),
            "halaman_ipm"       => base_url("picture/laporan_ppd/" . $picture_ipm . ".png"),
            "capaian_n_ipm"     => $max_ipm,
            "capaian_p_ipm"     => $max_ipm_p,
            "capaian_k_ipm"     => $max_ipm_k,
            "desc_ipm"          => $ipm_d,
            "sumber_ipm"         => $sumber_ipm,
            "tahun_max_ipm"      => $periode_ipm_tahun,
            "perbandingan_ipm"   => base_url("picture/laporan_ppd/" . $picture_ipm_bar . ".png"),
            "halaman_gr"        => base_url("picture/laporan_ppd/" . $picture_gr . ".png"),
            "capaian_n_gr"      => $max_n_gr,
            "capaian_p_gr"      => $max_p_gr,
            "capaian_k_gr"      => $max_k_gr,
            "desc_gr"           => $gr_d,
            "sumber_gr"         => $sumber_gr,
            "tahun_max_gr"      => $periode_gr_tahun,
            "perbandingan_GR"   => base_url("picture/laporan_ppd/" . $picture_gr_bar . ".png"),
            "halaman_ahh"       => base_url("picture/laporan_ppd/" . $picture_ahh . ".png"),
            "capaian_n_ahh"     => $max_n_ahh,
            "capaian_p_ahh"     => $max_p_ahh,
            "capaian_k_ahh"     => $max_k_ahh,
            "desc_ahh"          => $ahh_d,
            "sumber_ahh"         => $sumber_ahh,
            "tahun_max_ahh"      => $periode_ahh_tahun,
            "perbandingan_ahh"   => base_url("picture/laporan_ppd/" . $picture_ahh_bar . ".png"),
            "halaman_rls"       => base_url("picture/laporan_ppd/" . $picture_rls . ".png"),
            "capaian_n_rls"     => $max_n_rls,
            "capaian_p_rls"     => $max_p_rls,
            "capaian_k_rls"     => $max_k_rls,
            "desc_rls"          => $rls_d,
            "sumber_rls"        => $sumber_rls,
            "tahun_max_rls"      => $periode_rls_tahun,
            "perbandingan_rls"   => base_url("picture/laporan_ppd/" . $picture_rls_bar . ".png"),
            "halaman_hls"       => base_url("picture/laporan_ppd/" . $picture_hls . ".png"),
            "capaian_n_hls"     => $max_n_hls,
            "capaian_p_hls"     => $max_p_hls,
            "capaian_k_hls"     => $max_k_hls,
            "desc_hls"          => $hls_d,
            "sumber_hls"        => $sumber_hls,
            "tahun_max_hls"      => $periode_hls_tahun,
            "perbandingan_hls"   => base_url("picture/laporan_ppd/" . $picture_hls_bar . ".png"),
            "halaman_ppk"       => base_url("picture/laporan_ppd/" . $picture_ppk . ".png"),
            "capaian_n_ppk"     => $max_n_ppk,
            "capaian_p_ppk"     => $max_p_ppk,
            "capaian_k_ppk"     => $max_k_ppk,
            "desc_pk"           => $pk_d,
            "sumber_ppk"        => $sumber_ppk,
            "tahun_max_ppk"      => $periode_ppk_tahun,
            "perbandingan_ppk"   => base_url("picture/laporan_ppd/" . $picture_ppk_bar . ".png"),
            "halaman_tk"        => base_url("picture/laporan_ppd/" . $picture_tk . ".png"),
            "capaian_n_tk"      => $max_n_tk,
            "capaian_p_tk"      => $max_p_tk,
            "capaian_k_tk"      => $max_k_tk,
            "desc_tk"           => $tk_d,
            "sumber_tk"         => $sumber_tk,
            "tahun_max_tk"      => $periode_tk_tahun,
            "perbandingan_tk"   => base_url("picture/laporan_ppd/" . $picture_tk_bar . ".png"),
            "halaman_ikk"       => base_url("picture/laporan_ppd/" . $picture_ikk . ".png"),
            "capaian_n_ikk"     => $max_n_ikk,
            "capaian_p_ikk"     => $max_p_ikk,
            "capaian_k_ikk"     => $max_k_ikk,
            "desc_ikk"          => $ikk_d,
            "sumber_ikk"        => $sumber_idk,
            "tahun_max_ikk"      => $periode_ikk_tahun,
            "perbandingan_ikk"   => base_url("picture/laporan_ppd/" . $picture_ikk_bar . ".png"),
            "halaman_jpk"       => base_url("picture/laporan_ppd/" . $picture_jpk . ".png"),
            "capaian_n_jpk"     => $max_n_jpk,
            "capaian_p_jpk"     => $max_p_jpk,
            "capaian_k_jpk"     => $max_k_jpk,
            "desc_jpm"          => $jpm_d,
            "sumber_jpk"         => $sumber_jpk,
            "tahun_max_jpk"      => $periode_jpk_tahun,
            "perbandingan_jpk"   => base_url("picture/laporan_ppd/" . $picture_jpk_bar . ".png"),
            "halaman_pertama"    =>  'Pencapaian Pembangunan di 34 Provinsi',
            "halaman_1"          =>  base_url("picture/laporan_ppd/" . $picture_1 . ".png"),
            "radar_pe"           =>  base_url("picture/laporan_ppd/" . $picture_rpe . ".png"),
            "halaman_2"          =>  base_url("picture/laporan_ppd/" . $picture_2 . ".png"),
            "halaman_picture"    =>  base_url("picture/laporan_ppd/" . $picture_time . ".png"),
            "picture_pdrb"       =>  base_url("picture/laporan_ppd/" . $picture_pdrb . ".png"),
        );
        //$this->mpdf->SetHeader('Document Title');
        $html    = $this->load->view($this->view_dir . "tmp", $data_page, TRUE);
        //$html_1  = $this->load->view($this->view_dir."hal_1",$data_page,TRUE);
        $html_2  = $this->load->view($this->view_dir . "pertumbuhan_ekonomi", $data_page, TRUE);
        $html_3  = $this->load->view($this->view_dir . "PDRB_per_kapita_ADHB", $data_page, TRUE);
        $html_4  = $this->load->view($this->view_dir . "PDRB_per_kapita_ADHK", $data_page, TRUE);
        $html_5  = $this->load->view($this->view_dir . "jumlah_pengangguran", $data_page, TRUE);
        $html_6  = $this->load->view($this->view_dir . "pengangguran_terbuka", $data_page, TRUE);
        $html_7  = $this->load->view($this->view_dir . "gini_rasio", $data_page, TRUE);
        $html_8  = $this->load->view($this->view_dir . "angka_harapan_hidup", $data_page, TRUE);
        //                $html_9  = $this->load->view($this->view_dir."hal_9",$data_page,TRUE);
        //                $html_10 = $this->load->view($this->view_dir."hal_10",$data_page,TRUE);
        //                $html_11 = $this->load->view($this->view_dir."hal_11",$data_page,TRUE);
        //                $html_12 = $this->load->view($this->view_dir."hal_12",$data_page,TRUE);
        //                $html_13 = $this->load->view($this->view_dir."hal_13",$data_page,TRUE);
        //                $html_14 = $this->load->view($this->view_dir."hal_14",$data_page,TRUE);
        //                $html_15 = $this->load->view($this->view_dir."hal_15",$data_page,TRUE);

        //this the the PDF filename that user will get to download
        $pdfFilePath = "Laporan_" . $current_date_time . "_" . $judul . ".pdf";
        $this->mpdf = new mPDF('utf-8', 'A4', '', '', 25, 30, 20, 20, 'arial');
        //$this->mpdf = new mPDF('utf-8', 'A4', 0, '');
        //$this->mpdf->SetDisplayMode(90);
        $this->mpdf->SetDisplayMode('fullpage');
        //generate the PDF from the given html
        $this->m_pdf->pdf->AddPage('P');
        $this->m_pdf->pdf->WriteHTML($html);
        //$this->m_pdf->pdf->setFooter('{PAGENO}');
        //$this->m_pdf->pdf->AddPage('','','','','');
        //$this->m_pdf->pdf->WriteHTML($html_1);

        //                $this->m_pdf->pdf->setFooter('{PAGENO}','','{DATE j-m-Y}');
        $this->m_pdf->pdf->SetHeader('<table width="100%"><tr><td width="100%" style="text-align: right;">Perkembangan Indikator Makro Pembangunan</td></tr></table>');
        $this->m_pdf->pdf->AddPage('', '', '', '', '');
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


        //<td width="33%" style="text-align: right;">Perkembangan Indikator Makro Pembangunan</td>                
        $this->m_pdf->pdf->AddPage();
        $this->m_pdf->pdf->WriteHTML($html_3);
        //                $this->m_pdf->pdf->AddPage();
        $this->m_pdf->pdf->WriteHTML($html_4);
        //                $this->m_pdf->pdf->AddPage();
        $this->m_pdf->pdf->WriteHTML($html_5);
        //                $this->m_pdf->pdf->AddPage();
        $this->m_pdf->pdf->WriteHTML($html_6);
        //                $this->m_pdf->pdf->AddPage();
        $this->m_pdf->pdf->WriteHTML($html_7);
        //                $this->m_pdf->pdf->AddPage();
        $this->m_pdf->pdf->WriteHTML($html_8);
        //                $this->m_pdf->pdf->AddPage();
        //                $this->m_pdf->pdf->WriteHTML($html_9);
        //                $this->m_pdf->pdf->AddPage();
        //                $this->m_pdf->pdf->WriteHTML($html_10);
        //                $this->m_pdf->pdf->AddPage();
        //                $this->m_pdf->pdf->WriteHTML($html_11);
        //                $this->m_pdf->pdf->AddPage();
        //                $this->m_pdf->pdf->WriteHTML($html_12);
        //                $this->m_pdf->pdf->AddPage();
        //                $this->m_pdf->pdf->WriteHTML($html_13);
        //                $this->m_pdf->pdf->AddPage();
        //                $this->m_pdf->pdf->WriteHTML($html_14);
        //                $this->m_pdf->pdf->AddPage();
        //                $this->m_pdf->pdf->WriteHTML($html_15);
        //$this->m_pdf->pdf->AddPage('');
        $this->m_pdf->pdf->AddPage('L');
        //$this->m_pdf->pdf->WriteHTML($html);
        //$this->m_pdf->pdf->WriteHTML($html_2);
        //$this->m_pdf->pdf->WriteHTML($html_3);
        //download it.
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    function download_word()
    {
        $provinsi  = $_GET['inp_pro'];
        $kabupaten = $_GET['inp_sp'];
        $pro = $provinsi;
        $kab = $kabupaten;
        $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
        $xname = "";
        $query = "";
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
            if ($peek->id == '40') {
                $deskripsi14   = $peek->deskripsi;
            }
        }
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
            $sql_ppe = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppe = $this->db->query($sql_ppe);
            foreach ($list_ppe->result() as $row_ppe) {
                $tahun1[]   = $row_ppe->tahun;
                $nilaiData1[] = (float)$row_ppe->nilai;
                $nilaimax[] = number_format($row_ppe->nilai, 2);
            }
            $max_pe = end($nilaiData1);
            $sql_ppe_pro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_ppe_pro = $this->db->query($sql_ppe_pro);
            foreach ($list_ppe_pro->result() as $row_ppe_pro) {
                $tahun1_pro[]   = $row_ppe_pro->tahun;
                $nilaiData1_pro[] = (float)$row_ppe_pro->nilai;
                $nilaimax_pro[] = number_format($row_ppe_pro->nilai, 2);
                $sumber_pe = $row_ppe_pro->sumber;
                $periode_pe[] = $row_ppe_pro->id_periode;
            }
            $periode_pe_max = max($periode_pe);
            $tahun_pe_max = max($tahun1_pro) . " Antar Provinsi";

            $datay1 = $nilaiData1;
            $datay2 = $nilaiData1_pro;
            if ($nilaimax_pro[4] > $nilaimax_pro[5]) {
                $paragraf_1_2    = "Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " menurun dibandingkan dengan Tahun " . $tahun1[4] . ". Pada tahun " . $tahun1[5] . " pertumbuhan ekonomi " . $xname . " adalah sebesar " . end($nilaimax_pro) . "%, sedangkan pada tahun " . $tahun1[4] . " pertumbuhannya tercatat sebesar " . $nilaimax_pro[4] . "%. ";
                if ($nilaimax[5] > $nilaimax_pro[5]) {
                    $paragraf_1_3  = " Capaian Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " dibawah nasional. Pertumbuhan ekonomi nasional pada tahun " . $tahun1[5] . " adalah sebesar " . end($nilaimax) . "%. ";
                } else {
                    $paragraf_1_3  = " Capaian Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " diatas nasional. Pertumbuhan ekonomi nasional pada tahun " . $tahun1[5] . " adalah sebesar " . end($nilaimax) . "%. ";
                }
            } else {
                $paragraf_1_2    = "Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " meningkat dibandingkan dengan Tahun " . $tahun1[4] . ". Pada tahun " . $tahun1[5] . " pertumbuhan ekonomi " . $xname . " adalah sebesar " . end($nilaimax_pro) . "%, sedangkan pada tahun " . $tahun1[4] . " pertumbuhannya tercatat sebesar " . $nilaimax_pro[4] . "%. ";
                if ($nilaimax[5] > $nilaimax_pro[5]) {
                    $max_pe_p  = "Capaian Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " dibawah nasional. Pertumbuhan ekonomi nasional pada tahun " . $tahun1[5] . " adalah sebesar " . end($nilaimax) . "%. ";
                } else {
                    $paragraf_1_3  = " Capaian Pertumbuhan Ekonomi " . $xname . " pada tahun " . $tahun1[5] . " diatas nasional. Pertumbuhan ekonomi nasional pada tahun " . $tahun1[5] . " adalah sebesar " . end($nilaimax) . "%. ";
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


            //Perkembangan PDRB Per Kapita ADHB (Rp)
            $sql_adhb = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhb = $this->db->query($sql_adhb);
            foreach ($list_adhb->result() as $row_adhb) {
                $tahun_adhb[]      = $row_adhb->tahun;
                $nilaiData_adhb1[] = (float)$row_adhb->nilai / 1000000;
                $nilaiData_max[]   = (float)$row_adhb->nilai;
                $adhb_nasional[]   = number_format($row_adhb->nilai, 1);
            }
            $datay_adhb1 = $nilaiData_adhb1;
            $tahun_adhb1 = $tahun_adhb;
            $max_pdrb = end($nilaiData_adhb1);
            $sql_adhb2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhb2 = $this->db->query($sql_adhb2);
            foreach ($list_adhb2->result() as $row_adhb2) {
                $tahun_adhb2[]   = $row_adhb2->tahun;
                $nilaiData_adhb2[] = (float)$row_adhb2->nilai / 1000000;
                $nilaiData_max_p[] = (float)$row_adhb2->nilai;
                $sumber_adhb       = $row_adhb2->sumber;
                $periode_adhb[] = $row_adhb2->id_periode;
                $ket_adhb2[]  = $row_adhb2->keterangan;
            }
            $datay_adhb2 = $nilaiData_adhb2;
            $tahun_adhb2 = $tahun_adhb2;
            $tahunadhb  = end($tahun_adhb1);
            $periode_adhb_max = max($periode_adhb);
            $periode_adhb_tahun = max($tahun_adhb2) . " Antar Provinsi";

            $max_adhb_k  = " ";
            if ($nilaiData_max_p[4] > $nilaiData_max_p[5]) {
                $paragraf_2_2    = "PDRB Per Kapita ADHB " . $xname . " pada tahun " . $tahun_adhb2[5] . " menurun dibandingkan dengan tahun " . $tahun_adhb2[4] . ". Pada tahun " . $tahun_adhb2[5] . " PDRB perkapita ADHB " . $xname . " adalah sebesar Rp " . number_format(end($nilaiData_max_p), 0) . " " . $ket_adhb2[5] . " sedangkan pada tahun " . $tahun_adhb2[4] . "   PDRB perkapita ADHB tercatat sebesar Rp " . number_format($nilaiData_max_p[4], 0) . ". ";
                if ($nilaiData_max[5] > $nilaiData_max_p[5]) {
                    $paragraf_2_3  = "Capaian PDRB Per Kapita " . $xname . " pada tahun " . $tahun_adhb2[5] . " berada dibawah capaian nasional. PDRB perkapita ADHB nasional pada tahun " . $tahun_adhb2[5] . " adalah sebesar  Rp " . number_format($nilaiData_max[5]) . " ";
                } else {
                    $paragraf_2_3  = "Capaian PDRB Per Kapita " . $xname . " pada tahun " . $tahun_adhb2[5] . " berada diatas capaian nasional. PDRB perkapita ADHB nasional pada tahun " . $tahun_adhb2[5] . " adalah sebesar  Rp " . number_format($nilaiData_max[5]) . " ";
                }
            } else {
                $paragraf_2_2    = "PDRB Per Kapita ADHB " . $xname . " pada tahun " . $tahun_adhb2[5] . " meningkat dibandingkan dengan tahun " . $tahun_adhb2[4] . ". Pada tahun " . $tahun_adhb2[5] . " PDRB perkapita ADHB " . $xname . " adalah sebesar Rp " . number_format(end($nilaiData_max_p), 0) . " " . $ket_adhb2[5] . " sedangkan pada tahun " . $tahun_adhb2[4] . "  PDRB perkapita ADHB tercatat sebesar Rp " . number_format($nilaiData_max_p[4], 0) . ". ";
                if ($nilaiData_max[5] > $nilaiData_max_p[5]) {
                    $paragraf_2_3  = "Capaian PDRB Per Kapita " . $xname . " pada tahun " . $tahun_adhb2[5] . " berada dibawah capaian nasional. PDRB perkapita ADHB nasional pada tahun " . $tahun_adhb2[5] . " adalah sebesar  Rp " . number_format($nilaiData_max[5]) . " ";
                } else {
                    $paragraf_2_3  = "Capaian PDRB Per Kapita " . $xname . " pada tahun " . $tahun_adhb2[5] . " berada diatas capaian nasional. PDRB perkapita ADHB nasional pada tahun " . $tahun_adhb2[5] . " adalah sebesar  Rp " . number_format($nilaiData_max[5]) . " ";
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
                $nilai_adhb_per[] = $row_adhb_per->nilai / 1000000;
            }
            $label_data_adhb     = $label_adhb;
            $nilai_data_adhb_per = $nilai_adhb_per;

            //adhk (Rp)
            $sql_adhk = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhk = $this->db->query($sql_adhk);
            foreach ($list_adhk->result() as $row_adhk) {
                $tahun_adhk[]   = $row_adhk->tahun;
                $nilaiData_adhk1[] = (float)$row_adhk->nilai / 1000000;
                $adhk_nasional[] = (float)$row_adhk->nilai;
            }
            $datay_adhk1 = $nilaiData_adhk1;
            $tahun_adhk1 = $tahun_adhk;


            $sql_adhk2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_adhk2 = $this->db->query($sql_adhk2);
            foreach ($list_adhk2->result() as $row_adhk2) {
                $tahun_adhk2[]   = $row_adhk2->tahun;
                $nilaiData_adhk2[] = (float)$row_adhk2->nilai / 1000000;
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
                $paragraf_3_2    = "PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " menurun dibandingkan dengan tahun " . $tahun_adhk[4] . ". Pada tahun " . $tahun_adhk[5] . " PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " adalah sebesar Rp " . number_format(end($adhk_p)) . " " . $ket_adhk2[5] . " sedangkan pada tahun " . $tahun_adhk[4] . "  PDRB per Kapita ADHK Tahun Dasar 2010 tercatat sebesar Rp " . number_format($adhk_p[4]) . ". ";
                if ($adhk_nasional[5] > $adhk_p[5]) {
                    $paragraf_3_3  = "Capaian PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " berada dibawah capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun " . $tahun_adhk[5] . " adalah sebesar  Rp " . number_format(end($adhk_nasional)) . " ";
                } else {
                    $paragraf_3_3  = "Capaian PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhb[5] . " berada diatas capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun " . $tahun_adhk[5] . " adalah sebesar  Rp " . number_format(end($adhk_nasional)) . " ";
                }
            } else {
                $paragraf_3_2    = "PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " meningkat dibandingkan dengan tahun " . $tahun_adhk[4] . ". Pada tahun " . $tahun_adhk[5] . " PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " adalah sebesar Rp " . number_format(end($adhk_p)) . " " . $ket_adhk2[5] . " sedangkan pada tahun " . $tahun_adhk[4] . "  PDRB per Kapita ADHK Tahun Dasar 2010 tercatat sebesar Rp " . number_format($adhk_p[4]) . ". ";
                if ($adhk_nasional[5] > $adhk_p[5]) {
                    $paragraf_3_3  = "Capaian PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhk[5] . " berada dibawah capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun " . $tahun_adhk[5] . " adalah sebesar  Rp " . number_format(end($adhk_nasional)) . " ";
                } else {
                    $paragraf_3_3  = "Capaian PDRB per Kapita ADHK Tahun Dasar 2010 " . $xname . " pada tahun " . $tahun_adhb[5] . " berada diatas capaian nasional. PDRB per Kapita ADHK Tahun Dasar 2010 nasional pada tahun " . $tahun_adhk[5] . " adalah sebesar  Rp " . number_format(end($adhk_nasional)) . " ";
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
                $nilai_adhk_per[] = $row_adhk_per->nilai / 1000000;
            }
            $label_data_adhk     = $label_adhk;
            $nilai_data_adhk_per = $nilai_adhk_per;

            //jumlah pengangguran (Orang)
            $sql_jp = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jp = $this->db->query($sql_jp);
            foreach ($list_jp->result() as $row_jp) {
                $tahun_jp[]      = $bulan[$row_jp->periode] . "-" . $row_jp->tahun;
                $tahun_jp1[]     = $row_jp->id_periode;
                $nilaiData_jp[]  = (float)$row_jp->nilai / 1000;
                $nilai_capaian[] = $row_jp->nilai;
                $tahun_jp11[] = $row_jp->tahun;
                $periode_jp1[] = $row_jp->periode;
            }
            $datay_jp = $nilaiData_jp;
            $tahun_jp = $tahun_jp;
            $periode_jp_max  = max($tahun_jp1);
            $periode_jp_tahun = $bulan[max($periode_jp1)] . " " . max($tahun_jp11) . " Antar Provinsi";
            $sql_jp2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_jp2 = $this->db->query($sql_jp2);
            foreach ($list_jp2->result() as $row_jp2) {
                $tahun_jp2[]   = $row_jp2->tahun;
                $nilaiData_jp2[] = (float)$row_jp2->nilai / 1000;
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
                $paragraf_4_2  = "Jumlah penganggur di " . $xname . " pada " . $tahun_jp[5] . " sebanyak " . number_format($nilai_capaian2[5], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahun_jp[3] . " sebanyak " . number_format($nilai_capaian2[3], 0) . " orang. Selama periode  " . $tahun_jp[3] . " - " . $tahun_jp[5] . " jumlah penganggur di " . $xname . " berkurang " . number_format($rt_jpp) . " orang atau sebesar " . $rt_jp33 . "% ";
            } else {
                $rt_jp  = $nilai_capaian2[5] - $nilai_capaian2[3];
                $rt_jp2 = $rt_jp / $nilai_capaian2[3];
                $rt_jp3 = $rt_jp2 * 100;
                $rt_jp33 = number_format($rt_jp3, 2);
                $paragraf_4_2  = "Jumlah penganggur di " . $xname . " pada " . $tahun_jp[5] . " sebanyak " . number_format($nilai_capaian2[5], 0) . " orang. Sedangkan jumlah penganggur pada " . $tahun_jp[3] . " sebanyak " . number_format($nilai_capaian2[3], 0) . " orang. Selama periode  " . $tahun_jp[3] . " - " . $tahun_jp[5] . " jumlah penganggur di " . $xname . " meningkat " . number_format($rt_jp) . " orang atau sebesar " . $rt_jp33 . "%";
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
                $nilai_jp_per[] = $row_jp_per->nilai / 1000;
            }
            $label_data_jp     = $label_jp;
            $nilai_data_jp_per = $nilai_jp_per;


            //tingkat pengangguran terbuka
            $sql_tpt = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tpt = $this->db->query($sql_tpt);
            foreach ($list_tpt->result() as $row_tpt) {
                $tahun_tpt1[]    = $bulan[$row_tpt->periode] . "-" . $row_tpt->tahun;
                $tahun_tpt[]   = $row_tpt->tahun;
                $nilaiData_tpt[] = (float)$row_tpt->nilai;
            }
            $datay_tpt = $nilaiData_tpt;
            $tahun_tpt = $tahun_tpt;
            $sql_tpt2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_tpt2 = $this->db->query($sql_tpt2);
            foreach ($list_tpt2->result() as $row_tpt2) {
                $tahun_tpt21[]    = $bulan[$row_tpt2->periode] . "-" . $row_tpt2->tahun;
                $periode_tpt21[]    = $row_tpt2->periode;
                $tahun_tpt2[]     = $row_tpt2->tahun;
                $nilaiData_tpt2[] = (float)$row_tpt2->nilai;
                $sumber_tpt       = $row_tpt2->sumber;
                $periode_tpt_id[]    =   $row_tpt2->id_periode;
            }
            $datay_tpt2 = $nilaiData_tpt2;
            $tahun_tpt2 = $tahun_tpt2;
            $periode_tpt_max = max($periode_tpt_id);
            $periode_tpt_tahun = $bulan[max($periode_tpt21)] . " " . max($tahun_tpt2) . " Antar Provinsi";

            if ($nilaiData_tpt2[3] > $nilaiData_tpt2[5]) {
                $paragraf_5_2    = "Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " menurun dibandingkan dengan " . $tahun_tpt21[3] . ". Pada " . $tahun_tpt21[5] . " Tingkat Pengangguran Terbuka " . $xname . " adalah sebesar " . number_format(end($nilaiData_tpt2), 2) . "% sedangkan pada " . $tahun_tpt21[3] . "  Tingkat Pengangguran Terbuka tercatat sebesar " . number_format($nilaiData_tpt2[3], 2) . "%. ";
                if ($nilaiData_tpt[5] > $nilaiData_tpt2[5]) {
                    $paragraf_5_3  = "Capaian Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " berada dibawah capaian nasional. Tingkat Pengangguran Terbuka nasional pada " . $tahun_tpt21[5] . " adalah sebesar " . number_format(end($nilaiData_tpt), 2) . "% ";
                } else {
                    $paragraf_5_3  = "Capaian Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " berada diatas capaian nasional. Tingkat Pengangguran Terbuka nasional pada " . $tahun_tpt21[5] . " adalah sebesar " . number_format(end($nilaiData_tpt), 2) . "% ";
                }
            } else {
                $paragraf_5_2    = "Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " meningkat dibandingkan dengan " . $tahun_tpt21[3] . ". Pada " . $tahun_tpt21[5] . " Tingkat Pengangguran Terbuka " . $xname . " adalah sebesar " . number_format(end($nilaiData_tpt2), 2) . "% sedangkan pada " . $tahun_tpt21[3] . "  Tingkat Pengangguran Terbuka tercatat sebesar " . number_format($nilaiData_tpt2[3], 2) . "%. ";
                if ($nilaiData_tpt[5] > $nilaiData_tpt2[5]) {
                    $paragraf_5_3  = "Capaian Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " berada dibawah capaian nasional. Tingkat Pengangguran Terbuka nasional pada " . $tahun_tpt21[5] . " adalah sebesar " . number_format(end($nilaiData_tpt), 2) . "% ";
                } else {
                    $paragraf_5_3  = "Capaian Tingkat Pengangguran Terbuka " . $xname . " pada " . $tahun_tpt21[5] . " berada diatas capaian nasional. Tingkat Pengangguran Terbuka nasional pada " . $tahun_tpt21[5] . " adalah sebesar " . number_format(end($nilaiData_tpt), 2) . "% ";
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
            $paragraf_6_3 = '';
            if ($nilaiData_ipm2[4] > $nilaiData_ipm2[5]) {
                $paragraf_6_2    = "Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " menurun dibandingkan dengan tahun " . $tahun_ipm2[4] . ". Pada tahun " . $tahun_ipm2[5] . " Indeks Pembangunan Manusia " . $xname . " adalah sebesar " . number_format(end($nilaiData_ipm2), 2) . "% sedangkan pada tahun " . $tahun_ipm2[4] . "  Indeks Pembangunan Manusia tercatat sebesar " . number_format($nilaiData_ipm2[4], 2) . "%. ";
                if ($nilaiData_ipm[5] > $nilaiData_ipm2[5]) {
                    $paragraf_6_3  = "Capaian Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " berada dibawah capaian nasional. Indeks Pembangunan Manusia nasional pada tahun " . $tahun_ipm2[5] . " adalah sebesar " . number_format(end($nilaiData_ipm), 2) . "% " . "<br/><br/><br/><br/>";
                } else {
                    $paragraf_6_3  = "Capaian Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " berada diatas capaian nasional. Indeks Pembangunan Manusia nasional pada tahun " . $tahun_ipm2[5] . " adalah sebesar " . number_format(end($nilaiData_ipm), 2) . "% " . "<br/><br/><br/><br/>";
                }
            } else {
                $paragraf_6_2 = "Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " meningkat dibandingkan dengan tahun " . $tahun_ipm2[4] . ". Pada tahun " . $tahun_ipm2[5] . " Indeks Pembangunan Manusia " . $xname . " adalah sebesar " . number_format(end($nilaiData_ipm2), 2) . "% sedangkan pada tahun " . $tahun_ipm2[4] . "  Indeks Pembangunan Manusia tercatat sebesar " . number_format($nilaiData_ipm2[4], 2) . "%. ";
                if ($nilaiData_ipm[5] > $nilaiData_ipm2[5]) {
                    $paragraf_6_3  = "Capaian Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " berada dibawah capaian nasional. Indeks Pembangunan Manusia nasional pada tahun " . $tahun_ipm2[5] . " adalah sebesar " . number_format(end($nilaiData_ipm), 2) . "% " . "<br/><br/><br/><br/>";
                } else {
                    $paragraf_6_3  = "Capaian Indeks Pembangunan Manusia " . $xname . " pada tahun " . $tahun_ipm2[5] . " berada diatas capaian nasional. Indeks Pembangunan Manusia nasional pada tahun " . $tahun_ipm2[5] . " adalah sebesar " . number_format(end($nilaiData_ipm), 2) . "% " . "<br/><br/><br/><br/>";
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
                $nilai_ipm_per[] = $row_ipm_per->nilai;
            }
            $label_data_ipm     = $label_ipm;
            $nilai_data_ipm_per = $nilai_ipm_per;


            //Gini Rasio.
            $sql_gr = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_gr = $this->db->query($sql_gr);
            foreach ($list_gr->result() as $row_gr) {
                $tahun_gr[]    = $bulan[$row_gr->periode] . "-" . $row_gr->tahun;
                $nilaiData_gr[] = (float)$row_gr->nilai;
            }
            $datay_gr = $nilaiData_gr;
            $tahun_gr = $tahun_gr;
            $sql_gr2 = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='" . $id_pro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='" . $id_pro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
            $list_gr2 = $this->db->query($sql_gr2);
            foreach ($list_gr2->result() as $row_gr2) {
                $tahun_gr2[]   = $row_gr2->tahun;
                $periode    = $row_gr2->periode;
                $nilaiData_gr2[] = (float)$row_gr2->nilai;
                $nilaiData_gr22[] = number_format((float)$row_gr2->nilai, 3);
                $sumber_gr       = $row_gr2->sumber;
                $periode_gr_id[]    = $row_gr2->id_periode;
                $tahun_gr21[]    = $bulan[$row_gr2->periode] . "-" . $row_gr2->tahun;
            }
            $datay_gr2 = $nilaiData_gr2;
            $tahun_gr2 = $tahun_gr2;
            $max_k_gr  =  "";
            $periode_gr_max   = max($periode_gr_id);
            //    $periode_gr_tahun = $bulan[max($periode)]." ".max($tahun_gr2)." Antar Provinsi" ;
            if ($nilaiData_gr2[3] > $nilaiData_gr2[5]) {
                $paragraf_7_2    = "1Gini Rasio " . $xname . " pada " . $tahun_gr[5] . " menurun dibandingkan dengan " . $tahun_gr[3] . ". Pada " . $tahun_gr[5] . " gini rasio " . $xname . " adalah sebesar " . number_format($nilaiData_gr2[5], 3) . "% sedangkan pada " . $tahun_gr[3] . "  gini rasio tercatat sebesar " . number_format($nilaiData_gr2[3], 3) . "%. ";
                if ($nilaiData_gr[5] > $nilaiData_gr2[5]) {
                    $paragraf_7_3  = "Capaian gini rasio " . $xname . " pada " . $tahun_gr[5] . " berada dibawah capaian nasional. Gini rasio nasional pada " . $tahun_gr[5] . " adalah sebesar " . number_format($nilaiData_gr[5], 3) . "% ";
                } else {
                    $paragraf_7_3  = "Capaian gini rasio " . $xname . " pada " . $tahun_gr[5] . " berada diatas capaian nasional. Gini rasio nasional pada " . $tahun_gr[5] . " adalah sebesar " . number_format($nilaiData_gr[5], 3) . "% ";
                }
            } else {
                $paragraf_7_2    = "Gini Rasio " . $xname . " pada " . $tahun_gr[5] . " meningkat dibandingkan dengan " . $tahun_gr[3] . ". Pada " . $tahun_gr[5] . " gini rasio " . $xname . " adalah sebesar " . number_format($nilaiData_gr2[5], 3) . "% sedangkan pada " . $tahun_gr[3] . "  gini rasio tercatat sebesar " . number_format($nilaiData_gr2[3], 3) . "%. ";
                if ($nilaiData_gr[5] > $nilaiData_gr2[5]) {
                    $paragraf_7_3  = "Capaian gini rasio " . $xname . " pada " . $tahun_gr[5] . " berada dibawah capaian nasional. Gini rasio nasional pada " . $tahun_gr[5] . " adalah sebesar " . number_format($nilaiData_gr[5], 3) . "% ";
                } else {
                    $paragraf_7_3  = "Capaian gini rasio " . $xname . " pada " . $tahun_gr[5] . " berada diatas capaian nasional. Gini rasio nasional pada " . $tahun_gr[5] . " adalah sebesar " . number_format($nilaiData_gr[5], 3) . "% ";
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
                $paragraf_8_2    = "Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " menurun dibandingkan dengan tahun " . $tahun_ahh[4] . ". Pada tahun " . $tahun_ahh[5] . " Angka Harapan Hidup " . $xname . " adalah sebesar " . number_format(end($nilaiData_ahh2), 2) . " tahun sedangkan pada tahun " . $tahun_ahh[4] . " Angka Harapan Hidup tercatat sebesar " . number_format($nilaiData_ahh2[4], 2) . " tahun. ";
                if ($nilaiData_ahh[5] > $nilaiData_ahh2[5]) {
                    $paragraf_8_3  = "Capaian Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun " . $tahun_ahh[5] . " adalah sebesar " . number_format(end($nilaiData_ahh), 2) . " tahun ";
                } else {
                    $paragraf_8_3  = "Capaian Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " berada diatas capaian nasional. Angka Harapan Hidup nasional pada tahun " . $tahun_ahh[5] . " adalah sebesar " . number_format(end($nilaiData_ahh), 2) . " tahun ";
                }
            } else {
                $paragraf_8_2    = "Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " meningkat dibandingkan dengan tahun " . $tahun_ahh[4] . ". Pada tahun " . $tahun_ahh[5] . " Angka Harapan Hidup " . $xname . " adalah sebesar " . number_format(end($nilaiData_ahh2), 2) . " tahun sedangkan pada tahun " . $tahun_ahh[4] . " Angka Harapan Hidup tercatat sebesar " . number_format($nilaiData_ahh2[4], 2) . " tahun.";
                if ($nilaiData_ahh[5] > $nilaiData_ahh2[5]) {
                    $paragraf_8_3  = "Capaian Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " berada dibawah capaian nasional. Angka Harapan Hidup nasional pada tahun " . $tahun_ahh[5] . " adalah sebesar " . number_format(end($nilaiData_ahh), 2) . " tahun ";
                } else {
                    $paragraf_8_3  = "Capaian Angka Harapan Hidup " . $xname . " pada tahun " . $tahun_ahh[5] . " berada diatas capaian nasional. Angka Harapan Hidup nasional pada tahun " . $tahun_ahh[5] . " adalah sebesar " . number_format(end($nilaiData_ahh), 2) . " tahun ";
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
                $paragraf_9_2    = "Rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " menurun dibandingkan dengan tahun " . $tahun_rls[4] . ". Pada tahun " . $tahun_rls[5] . " Rata-rata lama sekolah " . $xname . " " . number_format(end($nilaiData_rls2), 2) . " Tahun, sedangkan pada tahun " . $tahun_rls[4] . " rata-rata lama sekolah tercatat sebesar " . number_format($nilaiData_rls2[4], 2) . " tahun. ";
                if ($nilaiData_rls[5] > $nilaiData_rls2[5]) {
                    $paragraf_9_3  = "Capaian rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " berada dibawah capaian nasional. Rata-rata lama sekolah nasional pada tahun " . $tahun_rls[5] . " sebesar " . number_format($nilaiData_rls[5], 2) . " tahun. ";
                } else {
                    $paragraf_9_3  = "Capaian rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " berada diatas capaian nasional. Rata-rata lama sekolah nasional pada tahun " . $tahun_rls[5] . " sebesar " . number_format($nilaiData_rls[5], 2) . " tahun. ";
                }
            } else {
                $paragraf_9_2    = "Rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " meningkat dibandingkan dengan tahun " . $tahun_rls[4] . ". Pada tahun " . $tahun_rls[5] . " Rata-rata lama sekolah " . $xname . " " . number_format(end($nilaiData_rls2), 2) . " Tahun, sedangkan pada tahun " . $tahun_rls[4] . " rata-rata lama sekolah tercatat sebesar " . number_format($nilaiData_rls2[4], 2) . " tahun. ";
                if ($nilaiData_rls[5] > $nilaiData_rls2[5]) {
                    $paragraf_9_3  = "Capaian rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " berada dibawah capaian nasional. Rata-rata lama sekolah nasional pada tahun " . $tahun_rls[5] . " sebesar " . number_format($nilaiData_rls[5], 2) . " tahun. ";
                } else {
                    $paragraf_9_3  = "Capaian rata-rata lama sekolah " . $xname . " pada tahun " . $tahun_rls[5] . " berada diatas capaian nasional. Rata-rata lama sekolah nasional pada tahun " . $tahun_rls[5] . " sebesar " . number_format($nilaiData_rls[5], 2) . " tahun. ";
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

            //                    print_r(number_format(end($nilaiData_ipm),2));echo '</Br>';                 
            //                    print_r($paragraf_9_2); echo '</Br>';
            //                    print_r($paragraf_9_3);echo '</Br>';
            //                    exit();

        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        $section = $phpWord->addSection();
        $phpWord->addFontStyle('rStyle', array('bold' => true, 'italic' => true, 'size' => 16));
        $phpWord->addParagraphStyle('pStyle', array('align' => 'center', 'spaceAfter' => 100));
        $phpWord->addParagraphStyle('rTengah', array('align' => 'justify', 'spaceAfter' => 100, 'size' => 12));
        $phpWord->addParagraphStyle('jdlGr', array('align' => 'center', 'spaceAfter' => 100, 'size' => 11));

        $section->addText('Perkembangan Indikator Makro Pembangunan', 'rStyle', 'pStyle');
        $section->addText("$xname", 'rStyle', 'pStyle');
        $section->addTextBreak();

        $style1 = array(
            'width' => Converter::inchToEmu(6),
            'height' => Converter::inchToEmu(2),
            //        'categoryAxisTitle' => TRUE,
            //        'valueAxisTitle' => TRUE,
            //        'majorTickMarkPos' => TRUE,
            'showAxisLabels' => true,
            'valueLabelPosition' => low,
            'title' => 'testtttt',
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
            'valueAxisTitle' => 'Last month consumed in kW',
            'showAxisLabels' => true,
            'showLegend' => true,
            'gridX' => true,
            'gridY' => true,
            'showVal' => true,
            'showCatName' => true
        );

        $stylee2 = array(
            'showAxisLabels' => $showAxisLabels,
            'showGridX'      => $showGridLines,
            'showGridY'      => $showGridLines,
        );

        $chartTypes = array('line');
        $twoSeries = array('line');
        $section->addText('Pertumbuhan Ekonomi', 'rStyle');
        $section->addText("$deskripsi1", 'rTengah');
        $section->addText("$paragraf_1_2", 'rTengah');
        $section->addText("$paragraf_1_3", 'rTengah');

        $categories_1_1 = $periode_pe;
        $series_1_n = $nilaiData1;
        $series_1_1 = $nilaiData1_pro;
        foreach ($chartTypes as $chartType) {
            $section->addTitle("Gambar 1 Perkembangan Pertumbuhan Ekonomi (%)", 'jdlGr');
            $chart = $section->addChart($chartType, $categories_1_1, $series_1_n, $style1);
            $chart->getStyle()->setShowGridX($showGridLines);

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
        $section->addTitle("Gambar 2 Perbandingan Pertumbuhan Ekonomi tahun 2020 Antar Provinsi (%)");

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
            $section->addTitle("Gambar 3 Perkembangan PDRB Pe]r Kapita ADHB (Juta Rupiah");
            $chart_2_2      = $section->addChart($chartType2, $categories_2_2, $series_2_n, $style1);
            if (in_array($chartType2, $twoSeries_2_2)) {
                $chart_2_2->addSeries($categories_2_2, $series_2_2, $style2_1);
            }
            $section->addTextBreak();
        }

        $categories_2_3 = $label_data_adhb;
        $series_2_3     = $nilai_data_adhb_per;
        $section->addTitle("Gambar 4 Perbandingan PDRB Per Kapita ADHB tahun 2019 Antar Provinsi (Juta Rupiah)");
        $chart_2_3      = $section->addChart('column', $categories_2_3, $series_2_3, $style1);
        $section->addTextBreak();

        $section->addText('Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010', 'rStyle');
        $section->addText("$deskripsi3", 'rTengah');
        $section->addText("$paragraf_3_2", 'rTengah');
        $section->addText("$paragraf_3_3", 'rTengah');
        $categories_3_1 = $tahun_adhk1;
        $series_3_n     = $datay_adhk1;
        $series_3_1     = $datay_adhk2;
        foreach ($chartTypes as $chartType) {
            $section->addTitle("Gambar 5 Perkembangan PDRB per Kapita ADHK Tahun Dasar 2010 (Juta Rupiah)", 'jdlGr');
            $chart_3_1 = $section->addChart($chartType, $categories_3_1, $series_3_n, $style1);
            $chart_3_1->getStyle()->setShowGridX($showGridLines);
            if (in_array($chartType, $twoSeries)) {
                $chart_3_1->addSeries($categories_3_1, $series_3_1, $style1);
            }
            $section->addTextBreak();
        }
        $categories_3_2 = $label_data_adhk;
        $series_3_2     = $nilai_data_adhk_per;
        $section->addTitle("Grafik 6 Perbandingan PDRB per Kapita ADHK (2010) tahun 2020 Antar Provinsi (Juta Rupiah)");
        $chart_3_2      = $section->addChart('column', $categories_3_2, $series_3_2, $style1);
        $section->addTextBreak();

        $section->addText('Perkembangan Jumlah Penganggur', 'rStyle');
        $section->addText("$deskripsi4", 'rTengah');
        $section->addText("$paragraf_4_2", 'rTengah');
        $categories_4_1 = $tahun_jp;
        $series_4_1     = $datay_jp;
        $section->addTitle("Gambar 7 Perkembangan Jumlah Penganggur (Ribu Orang)");
        $chart_4_1      = $section->addChart('column', $categories_4_1, $series_4_1, $style1);
        $section->addTextBreak();
        $categories_4_2 = $label_data_jp;
        $series_4_2     = $nilai_data_jp_per;
        $section->addTitle("Gambar 8 Perbandingan Jumlah Penganggur Ags 2020 Antar Provinsi (Ribu Orang)");
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
            $section->addTitle("Gambar 9 Tingkat Pengangguran Terbuka (%)", 'jdlGr');
            $chart_5_1 = $section->addChart($chartType, $categories_5_1, $series_5_n, $style1);
            $chart_5_1->getStyle()->setShowGridX($showGridLines);
            if (in_array($chartType, $twoSeries)) {
                $chart_5_1->addSeries($categories_5_1, $series_5_1, $style1);
            }
            $section->addTextBreak();
        }

        $categories_5_2 = $label_data_tpt;
        $series_5_2     = $nilai_data_tpt_per;
        $section->addTitle("Gambar 10 Perbandingan Tingkat Pengangguran Terbuka Ags 2020 Antar Provinsi (%)");
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
        $categories_7_1 = $tahun_gr;
        $series_7_n     = $datay_gr;
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
        // $section->addText("$paragraf_6_2", 'rTengah');
        //$section->addText("$paragraf_6_3", 'rTengah');
        $categories_9_1 = $tahun_ahh;
        $series_9_n     = $datay_ahh;
        $series_9_1     = $datay_ahh2;
        foreach ($chartTypes as $chartType) {
            $section->addTitle("Gambar 17 Perkembangan Rata-rata Lama Sekolah (Tahun)", 'jdlGr');
            $chart_9_1 = $section->addChart($chartType, $categories_8_1, $series_8_n, $style1);
            $chart_9_1->getStyle()->setShowGridX($showGridLines);
            if (in_array($chartType, $twoSeries)) {
                $chart_9_1->addSeries($categories_9_1, $series_9_1, $style1);
            }
            $section->addTextBreak();
        }
        $categories_9_2 = $label_ahh;
        $series_9_2     = $nilai_ahh_per;
        $section->addTitle("Gambar 18 Perbandingan Rata-rata Lama Sekolah tahun 2018 Antar Provinsi (Tahun)");
        $chart_9_2      = $section->addChart('column', $categories_9_2, $series_9_2, $style1);
        $section->addTextBreak();

        $section->addText('Harapan Lama Sekolah', 'rStyle');
        $section->addText("$deskripsi10", 'rTengah');
        // $section->addText("$paragraf_6_2", 'rTengah');
        //$section->addText("$paragraf_6_3", 'rTengah');


        $section->addText('Pengeluaran per Kapita', 'rStyle');
        $section->addText("$deskripsi11", 'rTengah');
        // $section->addText("$paragraf_6_2", 'rTengah');
        //$section->addText("$paragraf_6_3", 'rTengah');


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
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
    }




































    function download_pdf()
    {


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



        $datay1 = array(20, 2, 23, 15);
        $datay2 = array(12, 9, "-", 8);
        $datay3 = array(5, "-", 32, 24);

        // Setup the graph
        $graph = new Graph(300, 250);
        $graph->SetScale("textlin");

        $theme_class = new UniversalTheme;

        $graph->SetTheme($theme_class);
        $graph->img->SetAntiAliasing(false);
        $graph->title->Set('Filled Y-grid');
        $graph->SetBox(false);

        $graph->SetMargin(40, 20, 36, 63);

        $graph->img->SetAntiAliasing();

        $graph->yaxis->HideZeroLabel();
        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false, false);

        $graph->xgrid->Show();
        $graph->xgrid->SetLineStyle("solid");
        $graph->xaxis->SetTickLabels(array('A', 'B', 'C', 'D'));
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
