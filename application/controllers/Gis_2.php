<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gis_2 extends CI_Controller
{
    var $view_dir   = "peppd1/evaluasi/gis/";
    var $view_dir_demo   = "demo/evaluasi/gis/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/gis/gis_2.js";

    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Master", "m_ref");
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
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            try {
                if (!$this->session->userdata(SESSION_LOGIN)) {
                    throw new Exception("Session expired, please login", 2);
                }

                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                // print_r($current_date_time);exit();
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/peppd1/evaluasi/gis/gis_2_" . $this->session->userdata(SESSION_LOGIN)->groupid . ".js?v=" . now("Asia/Jakarta");

                $data_page = array();
                $str = $this->load->view($this->view_dir . "content_2", $data_page, TRUE);

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

    function indikator_list()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $list_indikator = $this->db->query("SELECT * FROM indikator WHERE group_id = 1 ORDER BY id ASC")->result();

                $json_data = array(
                    "data"  => $list_indikator   // total data array
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
                $list_years = $this->db->query("SELECT DISTINCT(tahun) FROM `nilai_indikator` ORDER BY tahun DESC")->result();

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
                    "data"  => $data_list_daerah   // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            die;
        }
    }

    function evaluasi_kinerja()
    {
        if ($this->input->is_ajax_request()) {
            try {

                //validasi
                $requestData = $_REQUEST;
                // $this->form_validation->set_rules('region', 'Daerah', 'required');
                // $this->form_validation->set_rules('indicator', 'Indikator', 'required');
                // $this->form_validation->set_rules('year', 'Tahun', 'required');
                // $this->form_validation->set_rules('data', 'Data', 'required');

                //inputan
                $data_all_evaluasi_kinerja = $this->input->post("data");

                $group_indikator = array();
                for ($i = 0; $i < count($data_all_evaluasi_kinerja[0]['indicator']); $i++) {
                    $indikator = array();
                    for ($j = 0; $j < count($data_all_evaluasi_kinerja[0]['indicator'][$i]); $j++) {
                        $data_indikator = $this->db->query("SELECT * FROM indikator WHERE id=" . $data_all_evaluasi_kinerja[0]['indicator'][$i][$j])->result_array();
                        array_push($indikator, $data_indikator);
                    }
                    array_push($group_indikator, $indikator);
                }

                $region = array();
                for ($k = 0; $k < count($data_all_evaluasi_kinerja[0]['region']); $k++) {
                    $data_region = $this->db->query("SELECT prov.id as id, prov.nama_provinsi as nama_daerah, NULL as prov_id FROM provinsi prov WHERE prov.nama_provinsi ='" . $data_all_evaluasi_kinerja[0]['region'][$k][0] . "' UNION SELECT kab.id as id, kab.nama_kabupaten as nama_daerah, kab.prov_id AS prov_id FROM kabupaten kab WHERE kab.nama_kabupaten = '" . $data_all_evaluasi_kinerja[0]['region'][$k][0] . "' ORDER BY id ASC")->result_array();
                    array_push($region, $data_region);
                }

                $group_data_nasional = array();
                $group_data_evaluasi = array();
                $group_data_regional = array();
                $group_data_regional_min_1 = array();
                $group_data_capital = array();
                for ($m = 0; $m < count($data_all_evaluasi_kinerja[0]['indicator']); $m++) {
                    $nasional = array();
                    $evaluasi = array();
                    $regional_by_indi = array();
                    $regional_by_indi_min_1 = array();
                    $regional_min_1 = array();
                    $capital = array();
                    for ($n = 0; $n < count($data_all_evaluasi_kinerja[0]['indicator'][$m]); $n++) {
                        $regional = array();
                        if (substr($region[$m][0]['nama_daerah'], 0, 8) == 'Provinsi') {
                            $data_provinsi = $this->db->query("SELECT * FROM `provinsi` ORDER BY `id` ASC")->result_array();
                            foreach ($data_provinsi as $prov) {
                                $data_regional = $this->db->query("SELECT ni.*, prov.nama_provinsi AS nama_daerah FROM nilai_indikator ni JOIN provinsi prov ON ni.wilayah = prov.id WHERE ni.id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND ni.wilayah='" . $prov['id'] . "' AND ni.periode !='01' AND ni.tahun IN ('" . $data_all_evaluasi_kinerja[0]['year'][$m][0] . "') AND (ni.id_periode, ni.versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND wilayah='" . $prov['id'] . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY ni.id_periode ORDER BY ni.id_periode DESC")->result();
                                array_push($regional, $data_regional);

                                $data_regional_min_1 = $this->db->query("SELECT ni.*, prov.nama_provinsi AS nama_daerah FROM nilai_indikator ni JOIN provinsi prov ON ni.wilayah = prov.id WHERE ni.id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND ni.wilayah='" . $prov['id'] . "' AND ni.periode !='01' AND ni.tahun IN ('" . ($data_all_evaluasi_kinerja[0]['year'][$m][0] - 1) . "') AND (ni.id_periode, ni.versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND wilayah='" . $prov['id'] . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY ni.id_periode ORDER BY ni.id_periode DESC")->result();
                                array_push($regional_min_1, $data_regional_min_1);
                            }
                            $data_capital = $this->db->query("SELECT ni.*, prov.nama_provinsi AS nama_daerah FROM nilai_indikator ni JOIN provinsi prov ON ni.wilayah = prov.id WHERE ni.id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND ni.wilayah='" . $region[$m][0]['id'] . "' AND ni.periode !='01' AND ni.tahun IN ('" . $data_all_evaluasi_kinerja[0]['year'][$m][0] . "') AND (ni.id_periode, ni.versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND wilayah='" . $region[$m][0]['id'] . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY ni.id_periode ORDER BY ni.id_periode DESC")->result();
                            array_push($capital, $data_capital);
                        } else {
                            $data_kabkot = $this->db->query("SELECT * FROM `kabupaten` WHERE prov_id = '" . $region[$m][0]['prov_id'] . "' ORDER BY `id` ASC")->result_array();
                            foreach ($data_kabkot as $kabkot) {
                                $data_regional = $this->db->query("SELECT ni.*, kabkot.nama_kabupaten AS nama_daerah FROM nilai_indikator ni JOIN kabupaten kabkot ON ni.wilayah = kabkot.id WHERE ni.id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND ni.wilayah='" . $kabkot['id'] . "' AND ni.periode !='01' AND ni.tahun IN ('" . $data_all_evaluasi_kinerja[0]['year'][$m][0] . "') AND (ni.id_periode, ni.versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND wilayah='" . $kabkot['id'] . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY ni.id_periode ORDER BY ni.id_periode DESC")->result();
                                array_push($regional, $data_regional);

                                $data_regional_min_1 = $this->db->query("SELECT ni.*, kabkot.nama_kabupaten AS nama_daerah FROM nilai_indikator ni JOIN kabupaten kabkot ON ni.wilayah = kabkot.id WHERE ni.id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND ni.wilayah='" . $kabkot['id'] . "' AND ni.periode !='01' AND ni.tahun IN ('" . ($data_all_evaluasi_kinerja[0]['year'][$m][0] - 1) . "') AND (ni.id_periode, ni.versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND wilayah='" . $kabkot['id'] . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY ni.id_periode ORDER BY ni.id_periode DESC")->result();
                                array_push($regional_min_1, $data_regional_min_1);
                            }
                            $data_capital = $this->db->query("SELECT ni.*, prov.nama_provinsi AS nama_daerah FROM nilai_indikator ni JOIN provinsi prov ON ni.wilayah = prov.id WHERE ni.id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND ni.wilayah='" . $region[$m][0]['prov_id'] . "' AND ni.periode !='01' AND ni.tahun IN ('" . $data_all_evaluasi_kinerja[0]['year'][$m][0] . "') AND (ni.id_periode, ni.versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND wilayah='" . $region[$m][0]['prov_id'] . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY ni.id_periode ORDER BY ni.id_periode DESC")->result();
                            array_push($capital, $data_capital);
                        }
                        array_push($regional_by_indi, $regional);
                        array_push($regional_by_indi_min_1, $regional_min_1);

                        $data_nasional = $this->db->query("SELECT ni.*, i.nama_indikator FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id WHERE id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode ASC")->result();
                        array_push($nasional, $data_nasional);
                        $data_evaluasi = $this->db->query("SELECT ni.*, i.nama_indikator, wil.nama_daerah AS nama_daerah FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON ni.wilayah = wil.id WHERE id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND wilayah='" . $region[$m][0]['id'] . "' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $data_all_evaluasi_kinerja[0]['indicator'][$m][$n] . "' AND wilayah='" . $region[$m][0]['id'] . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode ASC")->result();
                        array_push($evaluasi, $data_evaluasi);
                    }
                    array_push($group_data_nasional, $nasional);
                    array_push($group_data_evaluasi, $evaluasi);
                    array_push($group_data_regional, $regional_by_indi);
                    array_push($group_data_regional_min_1, $regional_by_indi_min_1);
                    array_push($group_data_capital, $capital);
                }

                //masukan data ke $json_data
                $json_data = array(
                    "data"          => $data_all_evaluasi_kinerja,
                    "data_evaluasi" => $data_all_evaluasi_kinerja[0]['data'],
                    "indicator"     => $group_indikator,
                    "region"        => $region,
                    "year"          => $data_all_evaluasi_kinerja[0]['year'],
                    "data_evaluasi_db" => $group_data_evaluasi,
                    "data_nasional_db" => $group_data_nasional,
                    "data_regional_db" => $group_data_regional,
                    "data_regional_min_1_db" => $group_data_regional_min_1,
                    "capital"       => $group_data_capital,
                );
                //encode data $json_data
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text"           => "",
                    "categories"     => "",
                    "series"         => 0
                );
                exit(json_encode($json_data));
            }
        } else {
            die;
        }
    }

    public function demo()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //if(!$this->session->userdata(SESSION_LOGIN)){throw new Exception("Session expired, please login",2);}

                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/demo/evaluasi/gis/gis_demo_2.js?v=" . now("Asia/Jakarta");

                $data_page = array();
                $str = $this->load->view($this->view_dir_demo . "content_2", $data_page, TRUE);

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
}
