<?php defined('BASEPATH') or exit('No direct script access allowed');

ini_set('max_execution_time', 0);
ini_set('memory_limit', '2048M');

class Apbd_2 extends CI_Controller
{
    var $view_dir = "peppd1/apbd/";
    // var $view_dir_demo   = "demo/apbd/";
    var $js_init = "main";
    var $js_path = "assets/js/admin/apbd/apbd_2.js";

    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Master", "m_ref");
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
                $this->js_init = "main";
                $this->js_path = "assets/js/admin/apbd/apbd_2.js";

                $data_page = array();
                $str = $this->load->view($this->view_dir . "index", $data_page, TRUE);

                $output = array(
                    "status" => 1,
                    "str" => $str,
                    "js_path" => base_url($this->js_path),
                    "js_initial" => $this->js_init . ".init();",
                    "csrf_hash" => $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status" => $exc->getCode(),
                    "msg" => $exc->getMessage(),
                    "csrf_hash" => $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            }
        } else {
            exit("access denied!");
        }
    }

    // function years_list()
    // {
    //     if ($this->input->is_ajax_request()) {
    //         try {
    //             $list_years = $this->db->query("SELECT DISTINCT(tahun) FROM `nilai_indikator` ORDER BY tahun ASC")->result();

    //             $json_data = array(
    //                 "data"  => $list_years,   // total data array
    //             );
    //             exit(json_encode($json_data));
    //         } catch (Exception $exc) {
    //             echo $exc->getTraceAsString();
    //         }
    //     } else {
    //         die;
    //     }
    // }

    function daerah_list()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $list_data_pro = $this->db->query("SELECT nama_provinsi FROM provinsi")->result();
                $list_data_kab_kota = $this->db->query("SELECT nama_kabupaten FROM kabupaten")->result();

                $data_list_daerah = array();

                foreach ($list_data_pro as $data_pro) {
                    array_push($data_list_daerah, $data_pro->nama_provinsi);
                }

                foreach ($list_data_kab_kota as $data_kab_kota) {
                    array_push($data_list_daerah, $data_kab_kota->nama_kabupaten);
                }

                $json_data = array(
                    "data" => $data_list_daerah // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            die;
        }
    }

    function item_list()
    {
        if ($this->input->is_ajax_request()) {
            try {
                // $list_data_pro = $this->db->query("SELECT nama_provinsi FROM provinsi")->result();
                // $list_data_kab_kota = $this->db->query("SELECT nama_kabupaten FROM kabupaten")->result();

                // $data_list_daerah = array();

                // foreach ($list_data_pro as $data_pro) {
                //     array_push($data_list_daerah, $data_pro->nama_provinsi);
                // }

                // foreach ($list_data_kab_kota as $data_kab_kota) {
                //     array_push($data_list_daerah, $data_kab_kota->nama_kabupaten);
                // }

                $list_standar_utama = $this->db->query("SELECT * FROM standarutama_apbd")->result();

                $list_apbd = array();

                for ($i = 0; $i < count($list_standar_utama); $i++) {
                    $list_data_apbd['kode'] = $list_standar_utama[$i]->kode;
                    $list_data_apbd['nama'] = $list_standar_utama[$i]->nama;
                    array_push($list_apbd, $list_data_apbd);
                    $list_standar_kelompok = $this->db->query("SELECT * FROM standarkelompok_apbd WHERE standarutama_APBD_id = " . $list_standar_utama[$i]->kode)->result();
                    for ($j = 0; $j < count($list_standar_kelompok); $j++) {
                        $list_data_apbd['kode'] = $list_standar_kelompok[$j]->kode;
                        $list_data_apbd['nama'] = $list_standar_kelompok[$j]->nama;
                        array_push($list_apbd, $list_data_apbd);
                        $list_standar_jenis = $this->db->query("SELECT * FROM standarjenis_apbd WHERE standarkelompok_APBD_id = " . $list_standar_kelompok[$j]->kode)->result();
                        for ($k = 0; $k < count($list_standar_jenis); $k++) {
                            $list_data_apbd['kode'] = $list_standar_jenis[$k]->kode;
                            $list_data_apbd['nama'] = $list_standar_jenis[$k]->nama;
                            array_push($list_apbd, $list_data_apbd);
                        }
                    }
                }

                $json_data = array(
                    "data" => $list_apbd, // total data array
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
                $list_years = $this->db->query("SELECT DISTINCT(tahun) FROM `nilaianggaran_apbd` ORDER BY tahun DESC")->result();

                $json_data = array(
                    "data" => $list_years // total data array
                );

                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            die;
        }
    }

    function itemdrilldown()
    {

        $selected_item = explode(',', $this->input->post("item"));
        $itemdrilldown = [];

        foreach ($selected_item as $item) {
            if (strlen($item) == 1) {
                $idsub = $this->db->query("SELECT kode FROM standarkelompok_apbd WHERE standarutama_APBD_id='$item'")->result_array();
                array_push($itemdrilldown, $idsub);
                foreach ($idsub as $s) {
                    $sub = $s['kode'];
                    if (strlen($sub) == 2) {
                        $idrilldown = $this->db->query("SELECT kode FROM standarjenis_apbd WHERE standarkelompok_APBD_id='$sub'")->result_array();
                        array_push($itemdrilldown, $idrilldown);
                    }
                }
            }
            if (strlen($item) == 2) {
                $idrilldown = $this->db->query("SELECT kode FROM standarjenis_apbd WHERE standarkelompok_APBD_id='$item'")->result_array();
                array_push($itemdrilldown, $idrilldown);
            }
        }

        $itemdrilldown = json_encode($itemdrilldown);
        print_r($itemdrilldown);
    }

    //grafik responsif apbd
    function apbd_grafik()
    {
        if ($this->input->is_ajax_request()) {
            try {

                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('daerah', 'Daerah', 'required');
                $this->form_validation->set_rules('item', 'Item', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $selected_daerah = explode(',', $this->input->post("daerah"));
                $selected_item = explode(',', $this->input->post("item"));
                $selected_year = explode(',', $this->input->post("tahun"));

                sort($selected_year);

                if (($selected_item) && ($selected_year)) {

                    $json_data_grafik_apbd_level_0 = array();

                    $data_all_all_nilai_apbd = array();

                    $list_selected_item = array();

                    for ($k = 0; $k < count($selected_item); $k++) {

                        $item = $this->db->query("SELECT * FROM (SELECT su.nama AS nama, su.kode AS kode FROM `standarutama_apbd` su UNION SELECT sk.nama AS nama, sk.kode AS kode FROM standarkelompok_apbd sk UNION SELECT sj.nama AS nama, sj.kode AS kode FROM standarjenis_apbd sj) a WHERE a.kode = '" . $selected_item[$k] . "'")->result();

                        $item_apbd['kode'] = $item[0]->kode;
                        $item_apbd['nama'] = $item[0]->nama;

                        array_push($list_selected_item, $item_apbd);


                        for ($l = 0; $l < count($selected_daerah); $l++) {
                            $daerah = $this->db->query("SELECT id, nama_provinsi AS nama FROM `provinsi` WHERE nama_provinsi = '" . $selected_daerah[$l] . "' UNION SELECT id, nama_kabupaten AS nama FROM kabupaten WHERE nama_kabupaten = '" . $selected_daerah[$l] . "' ORDER BY id ASC")->result();

                            for ($m = 0; $m < count($selected_year); $m++) {
                                $nilai_apbd = $this->db->query("SELECT na.wilayah, wil.nama_daerah AS nama_daerah, na.tahun, SUM(nilai) AS jumlah 
                                                                FROM nilaianggaran_apbd na 
                                                                JOIN standarjenis_apbd sj ON na.standarjenis_APBD_id = sj.kode 
                                                                LEFT JOIN standarkelompok_apbd sk ON sj.standarkelompok_APBD_id = sk.kode 
                                                                LEFT JOIN standarutama_apbd su ON sk.standarutama_APBD_id = su.kode 
                                                                LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON na.wilayah = wil.id 
                                                                WHERE standarjenis_APBD_id LIKE '" . $selected_item[$k] . "%' 
                                                                AND wilayah='" . $daerah[0]->id . "' 
                                                                AND tahun = '" . $selected_year[$m] . "'
                                                                AND versi IN (SELECT MAX(versi) AS versi FROM nilaianggaran_apbd WHERE standarjenis_APBD_id LIKE '" . $selected_item[$k] . "%' AND wilayah='" . $daerah[0]->id . "' AND tahun = '" . $selected_year[$m] . "' GROUP BY standarjenis_APBD_id ORDER BY standarjenis_APBD_id ASC) 
                                                                ORDER BY standarjenis_APBD_id ASC")->result();
                                $all_nilai_apbd['wilayah'] = $nilai_apbd[0]->wilayah;
                                $all_nilai_apbd['nama_daerah'] = $nilai_apbd[0]->nama_daerah;
                                $all_nilai_apbd['tahun'] = $nilai_apbd[0]->tahun;
                                $all_nilai_apbd['jumlah'] = $nilai_apbd[0]->jumlah;
                                $all_nilai_apbd['kode_item'] = $item[0]->kode;
                                $all_nilai_apbd['nama_item'] = $item[0]->nama;

                                array_push($data_all_all_nilai_apbd, $all_nilai_apbd);
                            }
                        }
                    }

                    // =========================================================================================================
                    for ($a = 0; $a < count($selected_daerah); $a++) {


                        $json_data_grafik_apbd_level_1 = array();

                        for ($b = 0; $b < count($selected_year); $b++) {

                            $json_data_grafik_apbd_level_2 = array();

                            $json_data_grafik_apbd = array();

                            $json_data_grafik_apbd['id'] = '0';
                            $json_data_grafik_apbd['parent'] = '';
                            $json_data_grafik_apbd['name'] = 'APBD';

                            array_push($json_data_grafik_apbd_level_2, $json_data_grafik_apbd);

                            for ($c = 0; $c < count($selected_item); $c++) {

                                $json_data_grafik_apbd = array();

                                if (strlen($selected_item[$c]) == '1') {
                                    $item = $this->db->query("SELECT * FROM `standarutama_apbd` WHERE kode = '" . $selected_item[$c] . "'")->result();

                                    $json_data_grafik_apbd['id'] = $item[0]->kode;
                                    // $json_data_grafik_apbd['parent'] = '';
                                    $json_data_grafik_apbd['parent'] = '0';
                                    $json_data_grafik_apbd['name'] = $item[0]->nama;
                                } elseif (strlen($selected_item[$c]) == '2') {
                                    $item = $this->db->query("SELECT * FROM `standarkelompok_apbd` WHERE kode = '" . $selected_item[$c] . "'")->result();

                                    $json_data_grafik_apbd['id'] = $item[0]->kode;
                                    $json_data_grafik_apbd['parent'] = $item[0]->standarutama_APBD_id;
                                    $json_data_grafik_apbd['name'] = $item[0]->nama;
                                } elseif (strlen($selected_item[$c]) == '4') {
                                    $wilayah = $this->db->query("SELECT id, nama_provinsi AS nama_daerah FROM `provinsi` WHERE nama_provinsi = '" . $selected_daerah[$a] . "' UNION SELECT id, nama_kabupaten AS nama_daerah FROM `kabupaten` WHERE nama_kabupaten = '" . $selected_daerah[$a] . "'")->result();


                                    $item = $this->db->query("SELECT na.wilayah AS wilayah, wil.nama_daerah AS nama_daerah, na.tahun AS tahun, sk.standarutama_APBD_id AS standarutama_APBD_id, sj.standarkelompok_APBD_id AS standarkelompok_APBD_id, sj.kode AS kode, sj.nama AS nama, na.nilai AS nilai 
                                                            FROM nilaianggaran_apbd na 
                                                            JOIN standarjenis_apbd sj ON na.standarjenis_APBD_id = sj.kode 
                                                            LEFT JOIN standarkelompok_apbd sk ON sj.standarkelompok_APBD_id = sk.kode 
                                                            LEFT JOIN standarutama_apbd su ON sk.standarutama_APBD_id = su.kode 
                                                            LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON na.wilayah = wil.id 
                                                            WHERE standarjenis_APBD_id= '" . $selected_item[$c] . "'
                                                            AND wilayah='" . $wilayah[0]->id . "' 
                                                            AND tahun = '" . $selected_year[$b] . "'
                                                            AND versi IN (SELECT MAX(versi) AS versi FROM nilaianggaran_apbd WHERE standarjenis_APBD_id= '" . $selected_item[$c] . "' AND wilayah='" . $wilayah[0]->id . "' AND tahun = '" . $selected_year[$b] . "' GROUP BY standarjenis_APBD_id ORDER BY standarjenis_APBD_id ASC) 
                                                            ORDER BY standarjenis_APBD_id ASC")->result();

                                    if (!empty($item)) {
                                        $json_data_grafik_apbd['id'] = $item[0]->kode;
                                        $json_data_grafik_apbd['parent'] = $item[0]->standarkelompok_APBD_id;
                                        $json_data_grafik_apbd['name'] = $item[0]->nama;
                                        $json_data_grafik_apbd['value'] = (float)$item[0]->nilai;
                                    } else {
                                        // Handle the case where no data is found
                                        $json_data_grafik_apbd['id'] = null;
                                        $json_data_grafik_apbd['parent'] = null;
                                        $json_data_grafik_apbd['name'] = 'Data not found';
                                        $json_data_grafik_apbd['value'] = 0;
                                    }
                                } else {
                                    $json_data_grafik_apbd['id'] = null;
                                    $json_data_grafik_apbd['parent'] = null;
                                    $json_data_grafik_apbd['name'] = null;
                                }
                                array_push($json_data_grafik_apbd_level_2, $json_data_grafik_apbd);
                            }

                            $tahun_apbd['tahun'] = $selected_year[$b];
                            $tahun_apbd['data_level_2'] = $json_data_grafik_apbd_level_2;

                            array_push($json_data_grafik_apbd_level_1, $tahun_apbd);
                        }

                        $daerah_apbd['daerah'] = $selected_daerah[$a];
                        $daerah_apbd['data_level_1'] = $json_data_grafik_apbd_level_1;

                        array_push($json_data_grafik_apbd_level_0, $daerah_apbd);
                    }
                    // =========================================================================================================

                } else {
                    $data_all_all_nilai_apbd = "";
                    $list_selected_item = "";
                }

                //masukan data ke $json_data
                $json_data = array(
                    "selected_daerah"   => $selected_daerah,
                    "selected_item"     => $selected_item,
                    "selected_year"     => $selected_year,
                    "data"              => $json_data_grafik_apbd_level_0,
                );

                //encode data $json_data
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text" => "",
                    "categories" => "",
                    "series" => 0
                );
                exit(json_encode($json_data));
            }
        } else {
            die;
        }
    }

    //tabel responsif apbd
    function apbd_tabel()
    {
        if ($this->input->is_ajax_request()) {
            try {

                //validasi
                $requestData = $_REQUEST;
                // $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                // $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                // $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('daerah', 'Daerah', 'required');
                $this->form_validation->set_rules('item', 'Item', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                // $pro = explode(',', $this->input->post("provinsi"));
                // $kab = explode(',', $this->input->post("kabupaten"));
                // $kot = explode(',', $this->input->post("kota"));
                $selected_daerah = explode(',', $this->input->post("daerah"));
                $selected_item = explode(',', $this->input->post("item"));
                $selected_year = explode(',', $this->input->post("tahun"));

                sort($selected_year);

                if (($selected_item) && ($selected_year)) {

                    $data_all_all_nilai_apbd = array();

                    $data_all_all_nilai_apbd_2 = array();

                    $list_selected_item = array();

                    for ($k = 0; $k < count($selected_item); $k++) {

                        $item = $this->db->query("SELECT * FROM (SELECT su.nama AS nama, su.kode AS kode FROM `standarutama_apbd` su UNION SELECT sk.nama AS nama, sk.kode AS kode FROM standarkelompok_apbd sk UNION SELECT sj.nama AS nama, sj.kode AS kode FROM standarjenis_apbd sj) a WHERE a.kode = '" . $selected_item[$k] . "'")->result();

                        $item_apbd['kode'] = $item[0]->kode;
                        $item_apbd['nama'] = $item[0]->nama;

                        array_push($list_selected_item, $item_apbd);


                        for ($l = 0; $l < count($selected_daerah); $l++) {
                            $daerah = $this->db->query("SELECT id, nama_provinsi AS nama FROM `provinsi` WHERE nama_provinsi = '" . $selected_daerah[$l] . "' UNION SELECT id, nama_kabupaten AS nama FROM kabupaten WHERE nama_kabupaten = '" . $selected_daerah[$l] . "' ORDER BY id ASC")->result();

                            for ($m = 0; $m < count($selected_year); $m++) {
                                $nilai_apbd = $this->db->query("SELECT na.wilayah, wil.nama_daerah AS nama_daerah, na.tahun, SUM(nilai) AS jumlah 
                                                                FROM nilaianggaran_apbd na 
                                                                JOIN standarjenis_apbd sj ON na.standarjenis_APBD_id = sj.kode 
                                                                LEFT JOIN standarkelompok_apbd sk ON sj.standarkelompok_APBD_id = sk.kode 
                                                                LEFT JOIN standarutama_apbd su ON sk.standarutama_APBD_id = su.kode 
                                                                LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON na.wilayah = wil.id 
                                                                WHERE standarjenis_APBD_id LIKE '" . $selected_item[$k] . "%' 
                                                                AND wilayah='" . $daerah[0]->id . "' 
                                                                AND tahun = '" . $selected_year[$m] . "'
                                                                AND versi IN (SELECT MAX(versi) AS versi FROM nilaianggaran_apbd WHERE standarjenis_APBD_id LIKE '" . $selected_item[$k] . "%' AND wilayah='" . $daerah[0]->id . "' AND tahun = '" . $selected_year[$m] . "' GROUP BY standarjenis_APBD_id ORDER BY standarjenis_APBD_id ASC) 
                                                                ORDER BY standarjenis_APBD_id ASC")->result();
                                $all_nilai_apbd['wilayah'] = $nilai_apbd[0]->wilayah;
                                $all_nilai_apbd['nama_daerah'] = $nilai_apbd[0]->nama_daerah;
                                $all_nilai_apbd['tahun'] = $nilai_apbd[0]->tahun;
                                $all_nilai_apbd['jumlah'] = $nilai_apbd[0]->jumlah;
                                $all_nilai_apbd['kode_item'] = $item[0]->kode;
                                $all_nilai_apbd['nama_item'] = $item[0]->nama;

                                array_push($data_all_all_nilai_apbd, $all_nilai_apbd);
                            }
                        }
                    }

                    // =========================================================================================================
                    for ($a = 0; $a < count($selected_item); $a++) {

                        $item = $this->db->query("SELECT * FROM (SELECT su.nama AS nama, su.kode AS kode FROM `standarutama_apbd` su UNION SELECT sk.nama AS nama, sk.kode AS kode FROM standarkelompok_apbd sk UNION SELECT sj.nama AS nama, sj.kode AS kode FROM standarjenis_apbd sj) a WHERE a.kode = '" . $selected_item[$a] . "'")->result();

                        $item_apbd_2['kode'] = $item[0]->kode;
                        $item_apbd_2['nama'] = $item[0]->nama;

                        $wilayah_all_nilai_apbd_2 = array();

                        for ($b = 0; $b < count($selected_daerah); $b++) {
                            $daerah = $this->db->query("SELECT id, nama_provinsi AS nama FROM `provinsi` WHERE nama_provinsi = '" . $selected_daerah[$b] . "' UNION SELECT id, nama_kabupaten AS nama FROM kabupaten WHERE nama_kabupaten = '" . $selected_daerah[$b] . "' ORDER BY id ASC")->result();

                            $value_all_nilai_apbd_2 = array();

                            for ($c = 0; $c < count($selected_year); $c++) {
                                $nilai_apbd = $this->db->query("SELECT na.wilayah, wil.nama_daerah AS nama_daerah, na.tahun, SUM(nilai) AS jumlah 
                                                                FROM nilaianggaran_apbd na 
                                                                JOIN standarjenis_apbd sj ON na.standarjenis_APBD_id = sj.kode 
                                                                LEFT JOIN standarkelompok_apbd sk ON sj.standarkelompok_APBD_id = sk.kode 
                                                                LEFT JOIN standarutama_apbd su ON sk.standarutama_APBD_id = su.kode 
                                                                LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON na.wilayah = wil.id 
                                                                WHERE standarjenis_APBD_id LIKE '" . $selected_item[$a] . "%' 
                                                                AND wilayah='" . $daerah[0]->id . "' 
                                                                AND tahun = '" . $selected_year[$c] . "'
                                                                AND versi IN (SELECT MAX(versi) AS versi FROM nilaianggaran_apbd WHERE standarjenis_APBD_id LIKE '" . $selected_item[$a] . "%' AND wilayah='" . $daerah[0]->id . "' AND tahun = '" . $selected_year[$c] . "' GROUP BY standarjenis_APBD_id ORDER BY standarjenis_APBD_id ASC) 
                                                                ORDER BY standarjenis_APBD_id ASC")->result();

                                $all_nilai_apbd_2['tahun'] = $nilai_apbd[0]->tahun;
                                $all_nilai_apbd_2['jumlah'] = $nilai_apbd[0]->jumlah;

                                array_push($value_all_nilai_apbd_2, $all_nilai_apbd_2);
                            }

                            $all_wilayah_apbd_2['wilayah'] = $daerah[0]->id;
                            $all_wilayah_apbd_2['nama_daerah'] = $daerah[0]->nama;
                            $all_wilayah_apbd_2['value'] = $value_all_nilai_apbd_2;

                            array_push($wilayah_all_nilai_apbd_2, $all_wilayah_apbd_2);
                        }

                        $item_apbd_2['data'] = $wilayah_all_nilai_apbd_2;

                        array_push($data_all_all_nilai_apbd_2, $item_apbd_2);
                    }
                    // =========================================================================================================

                } else {
                    $data_all_all_nilai_apbd = "";
                    $list_selected_item = "";
                }


                //masukan data ke $json_data
                $json_data = array(
                    // "nama_indikator"    => $selected_indicator,
                    // "jumlah_data"       => count(($indikator_capaian_nasional != null ? $indikator_capaian_nasional : '')),
                    // "tanggal"           => array(
                    //     "tahun"     => ($tahun != null ? $tahun : ''),
                    //     "periode"   => ($periode != null ? $periode : ''),
                    // ),
                    // "provinsi"          => $pro,
                    // "kabupaten"         => $kab,
                    // "kota"              => $kot,
                    "selected_daerah" => $selected_daerah,
                    "selected_item" => $selected_item,
                    "selected_year" => $selected_year,
                    "list_selected_item" => $list_selected_item,
                    "data_all_all_nilai_apbd" => $data_all_all_nilai_apbd,
                    "data_all_all_nilai_apbd_2" => $data_all_all_nilai_apbd_2,
                    // "html"              => $html,
                    // "data"              => ($data_all_all_nilai_indikator != null ? $data_all_all_nilai_indikator : ''),
                );

                //encode data $json_data
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text" => "",
                    "categories" => "",
                    "series" => 0
                );
                exit(json_encode($json_data));
            }
        } else {
            die;
        }
    }

    function cellsCol1Col2($col1 = -1, $col2 = -1, $numcol1 = -1, $numcol2 = -1)
    {
        $cell = 'A1:A1';
        if ($col1 >= 0 && $col2 >= 0 && $numcol1 >= 0 && $numcol2 >= 0) {
            $col1 = PHPExcel_Cell::stringFromColumnIndex($col1);
            $col2 = PHPExcel_Cell::stringFromColumnIndex($col2);
            $cell = "$col1{$numcol1}:$col2{$numcol2}";
        }
        return $cell;
    }

    function cellsToMergeByColsRow($start = -1, $end = -1, $row = -1)
    {
        $merge = 'A1:A1';
        if ($start >= 0 && $end >= 0 && $row >= 0) {
            $start = PHPExcel_Cell::stringFromColumnIndex($start);
            $end = PHPExcel_Cell::stringFromColumnIndex($end);
            $merge = "$start{$row}:$end{$row}";
        }
        return $merge;
    }

    function cellsToMergeByOnlyRow($start = -1, $end = -1, $column = -1)
    {
        $merge = 'A1:A4';
        if ($start >= 0 && $end >= 0 && $column >= 0) {
            $column = PHPExcel_Cell::stringFromColumnIndex($column);
            $merge = "$column{$start}:$column{$end}";
        }
        return $merge;
    }

    function export_apbd_tabel()
    {
        //inputan
        $selected_daerah = explode(',', $_GET['daerah']);
        $selected_item = explode(',', $_GET['item']);
        $selected_year = explode(',', $_GET['tahun']);

        sort($selected_year);

        if (($selected_item) && ($selected_year)) {

            $data_all_all_nilai_apbd = array();

            $list_selected_item = array();

            for ($k = 0; $k < count($selected_item); $k++) {

                $item = $this->db->query("SELECT * FROM (SELECT su.nama AS nama, su.kode AS kode FROM `standarutama_apbd` su UNION SELECT sk.nama AS nama, sk.kode AS kode FROM standarkelompok_apbd sk UNION SELECT sj.nama AS nama, sj.kode AS kode FROM standarjenis_apbd sj) a WHERE a.kode = '" . $selected_item[$k] . "'")->result();

                $item_apbd['kode'] = $item[0]->kode;
                $item_apbd['nama'] = $item[0]->nama;

                array_push($list_selected_item, $item_apbd);

                for ($l = 0; $l < count($selected_daerah); $l++) {
                    $daerah = $this->db->query("SELECT id, nama_provinsi AS nama FROM `provinsi` WHERE nama_provinsi = '" . $selected_daerah[$l] . "' UNION SELECT id, nama_kabupaten AS nama FROM kabupaten WHERE nama_kabupaten = '" . $selected_daerah[$l] . "' ORDER BY id ASC")->result();

                    for ($m = 0; $m < count($selected_year); $m++) {
                        $nilai_apbd = $this->db->query("SELECT na.wilayah, wil.nama_daerah AS nama_daerah, na.tahun, SUM(nilai) AS jumlah 
                                                        FROM nilaianggaran_apbd na 
                                                        JOIN standarjenis_apbd sj ON na.standarjenis_APBD_id = sj.kode 
                                                        LEFT JOIN standarkelompok_apbd sk ON sj.standarkelompok_APBD_id = sk.kode 
                                                        LEFT JOIN standarutama_apbd su ON sk.standarutama_APBD_id = su.kode 
                                                        LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON na.wilayah = wil.id 
                                                        WHERE standarjenis_APBD_id LIKE '" . $selected_item[$k] . "%' 
                                                        AND wilayah='" . $daerah[0]->id . "' 
                                                        AND tahun = '" . $selected_year[$m] . "'
                                                        AND versi IN (SELECT MAX(versi) AS versi FROM nilaianggaran_apbd WHERE standarjenis_APBD_id LIKE '" . $selected_item[$k] . "%' AND wilayah='" . $daerah[0]->id . "' AND tahun = '" . $selected_year[$m] . "' GROUP BY standarjenis_APBD_id ORDER BY standarjenis_APBD_id ASC) 
                                                        ORDER BY standarjenis_APBD_id ASC")->result();
                        $all_nilai_apbd['wilayah'] = $nilai_apbd[0]->wilayah;
                        $all_nilai_apbd['nama_daerah'] = $nilai_apbd[0]->nama_daerah;
                        $all_nilai_apbd['tahun'] = $nilai_apbd[0]->tahun;
                        $all_nilai_apbd['jumlah'] = $nilai_apbd[0]->jumlah;
                        $all_nilai_apbd['kode_item'] = $item[0]->kode;
                        $all_nilai_apbd['nama_item'] = $item[0]->nama;

                        array_push($data_all_all_nilai_apbd, $all_nilai_apbd);
                    }
                }
            }
        } else {
            $data_all_all_nilai_apbd = "";
            $list_selected_item = "";
        }

        if (($data_all_all_nilai_apbd != '') && ($list_selected_item != '')) {
            $this->load->library("Excel");
            $sharedStyleTitles = new PHPExcel_Style();

            $this->excel->getActiveSheet()->getSheetView()->setZoomScale(50);
            $this->excel->getSheet(0)->setTitle('APBD');

            // garis
            $sharedStyleTitles->applyFromArray(
                array(
                    'borders' =>
                    array(
                        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                        'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                    )
                )
            );

            $this->excel->getActiveSheet()->setCellValue('A1', 'Anggaran Pendapatan dan Belanja Daerah (APBD)');
            $this->excel->getActiveSheet()->setCellValue('A2', 'DIUNDUH TANGGAL : ' . date('d-m-Y'));
            $this->excel->getActiveSheet()->mergeCells('A1:P1');
            $this->excel->getActiveSheet()->mergeCells('A2:B2');
            $this->excel->getActiveSheet()->getStyle('A1:IV255')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, 3, 'KODE');
            $this->excel->getActiveSheet()->mergeCells('A3:A5');

            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, 3, 'URAIAN');
            $this->excel->getActiveSheet()->mergeCells('B3:B5');

            $kode_wilayah = '';

            $start = 2;

            for ($j = 0; $j < count($data_all_all_nilai_apbd); $j++) {

                if (($list_selected_item[0]['kode'] == $data_all_all_nilai_apbd[$j]['kode_item']) && ($kode_wilayah != $data_all_all_nilai_apbd[$j]['wilayah'])) {

                    $kode_wilayah = $data_all_all_nilai_apbd[$j]['wilayah'];

                    $stop = count($selected_year);

                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($start, 3, $data_all_all_nilai_apbd[$j]['wilayah']);
                    $this->excel->getActiveSheet()->mergeCells($this->cellsToMergeByColsRow($start, (($start + $stop) - 1), 3));

                    $start = $start + $stop;
                }
            }

            $nama_daerah = '';

            $start_daerah = 2;

            for ($j = 0; $j < count($data_all_all_nilai_apbd); $j++) {

                if (($list_selected_item[0]['kode'] == $data_all_all_nilai_apbd[$j]['kode_item']) && ($nama_daerah != $data_all_all_nilai_apbd[$j]['nama_daerah'])) {

                    $nama_daerah = $data_all_all_nilai_apbd[$j]['nama_daerah'];

                    $stop_daerah = count($selected_year);

                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($start_daerah, 4, $data_all_all_nilai_apbd[$j]['nama_daerah']);
                    $this->excel->getActiveSheet()->mergeCells($this->cellsToMergeByColsRow($start_daerah, (($start_daerah + $stop_daerah) - 1), 4));

                    $start_daerah = $start_daerah + $stop_daerah;
                }
            }

            $nama_daerah_2 = '';

            $start_daerah_2 = 2;

            for ($j = 0; $j < count($data_all_all_nilai_apbd); $j++) {

                if (($list_selected_item[0]['kode'] == $data_all_all_nilai_apbd[$j]['kode_item']) && ($nama_daerah_2 != $data_all_all_nilai_apbd[$j]['nama_daerah'])) {

                    $nama_daerah_2 = $data_all_all_nilai_apbd[$j]['nama_daerah'];

                    for ($k = 0; $k < count($selected_year); $k++) {

                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow($start_daerah_2, 5, $selected_year[$k]);

                        $start_daerah_2 = $start_daerah_2 + 1;
                    }
                }
            }


            $num = 0;
            for ($i = 0; $i < count($list_selected_item); $i++) {
                $num = $i + 6;
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $num, $list_selected_item[$i]['kode']);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $num, $list_selected_item[$i]['nama']);
                $num_value_apbd = 0;
                $k = 0;
                for ($j = 0; $j < count($data_all_all_nilai_apbd); $j++) {
                    if ($data_all_all_nilai_apbd[$j]['kode_item'] == $list_selected_item[$i]['kode']) {
                        $num_value_apbd = $k + 2;
                        if ($data_all_all_nilai_apbd[$j]['jumlah'] != null) {
                            $value_apbd = $data_all_all_nilai_apbd[$j]['jumlah'];
                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow($num_value_apbd, $num, $value_apbd);
                        } else {
                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow($num_value_apbd, $num, '-');
                        }
                        $k++;
                    } else {
                        $k = 0;
                    }
                }
            }

            //font
            $this->excel->getActiveSheet()->getStyle('A1:A2')->getFont()->setName('CMIIW');

            //bolt
            // $this->excel->getActiveSheet()->getStyle('A2:C3')->getFont()->setBold(true);
            // $this->excel->getActiveSheet()->getStyle('A10:L11')->getFont()->setBold(true);

            //size    
            $this->excel->getActiveSheet()->getStyle('A1:IV255')->getFont()->setSize(20);

            $this->excel->getActiveSheet()->getStyle('C6:IV255')->getNumberFormat()->setFormatCode('#,##0.00');

            //lebar kolom
            $this->excel->getActiveSheet()->getColumnDimension("A")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("B")->setWidth("150");
            $this->excel->getActiveSheet()->getColumnDimension("C")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("D")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("E")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("F")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("G")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("H")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("I")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("J")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("K")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("L")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("M")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("N")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("O")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("P")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("Q")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("R")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("S")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("T")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("U")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("V")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("W")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("X")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("Y")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("Z")->setWidth("40");

            $this->excel->getActiveSheet()->getColumnDimension("AA")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AB")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AC")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AD")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AE")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AF")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AG")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AH")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AI")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AJ")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AK")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AL")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AM")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AN")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AO")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AP")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AQ")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AR")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AS")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AT")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AU")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AV")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AW")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AX")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AY")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("AZ")->setWidth("40");

            $this->excel->getActiveSheet()->getColumnDimension("BA")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BB")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BC")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BD")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BE")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BF")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BG")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BH")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BI")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BJ")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BK")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BL")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BM")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BN")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BO")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BP")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BQ")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BR")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BS")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BT")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BU")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BV")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BW")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BX")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BY")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("BZ")->setWidth("40");

            $this->excel->getActiveSheet()->getColumnDimension("CA")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CB")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CC")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CD")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CE")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CF")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CG")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CH")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CI")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CJ")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CK")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CL")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CM")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CN")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CO")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CP")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CQ")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CR")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CS")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CT")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CU")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CV")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CW")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CX")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CY")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("CZ")->setWidth("40");

            $this->excel->getActiveSheet()->getColumnDimension("DA")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DB")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DC")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DD")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DE")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DF")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DG")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DH")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DI")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DJ")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DK")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DL")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DM")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DN")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DO")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DP")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DQ")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DR")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DS")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DT")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DU")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DV")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DW")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DX")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DY")->setWidth("40");
            $this->excel->getActiveSheet()->getColumnDimension("DZ")->setWidth("40");

            $this->excel->getActiveSheet()->setShowGridlines(true);
            // $this->excel->getActiveSheet()->getStyle('D12:I202')->getAlignment()->setWrapText(true);
            $this->excel->getActiveSheet()->getProtection()->setSheet(true);
            $this->excel->getActiveSheet()->getProtection()->setSort(true);
            $this->excel->getActiveSheet()->getProtection()->setInsertRows(true);
            $this->excel->getActiveSheet()->getProtection()->setFormatCells(true);

            header("Content-Type:application/vnd.ms-excel");
            header("Content-Disposition:attachment;filename = APBD.xls");
            header("Cache-Control:max-age=0");
            $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
            $objWriter->save("php://output");
        } else {
            $data_all_all_nilai_indikator = "something wrong";
        }
    }
}
