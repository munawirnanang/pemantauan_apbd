<?php defined('BASEPATH') or exit('No direct script access allowed');

ini_set('max_execution_time', 0);
// ini_set('memory_limit', '2048M');

class Pencapaian_indikator extends CI_Controller
{
    var $view_dir   = "peppd1/pencapaian_indikator/";
    var $view_dir_demo   = "demo/pencapaian_indikator/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/pencapaian_indikator/pencapaian_indikator.js";

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
                $this->js_init    = "main";
                $this->js_path    = "assets/js/admin/pencapaian_indikator/pencapaian_indikator_" . $this->session->userdata(SESSION_LOGIN)->groupid . ".js?v=" . now("Asia/Jakarta");

                $data_page = array();
                $str = $this->load->view($this->view_dir . "content", $data_page, TRUE);

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

    function pro_list()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $sql = "SELECT A.id,A.`nama_provinsi`,A.`label`,A.`ppd`
                        FROM provinsi A
                        WHERE 1=1";
                $list_data = $this->db->query($sql);
                $totalData = $this->db->query($sql)->num_rows();
                $data = array();
                foreach ($list_data->result() as $row) {
                    $nestedData = array();

                    $nestedData[] = encrypt_text($row->id);
                    $nestedData[] = $row->nama_provinsi;
                    $nestedData[] = $row->label;

                    $data[] = $nestedData;
                }
                $json_data = array(
                    "data"  => $data   // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else
            die;
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
                    $tmp = " data-id='" . encrypt_text($id) . "' ";
                    $nama = " data-nama='" . $row->nama_provinsi . "' ";
                    $nestedData[] = ""
                        . "<input type='radio' class='checkbox' name='group' $tmp  value='" . $row->nama_provinsi . "'  /> ";
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

    function kab_list()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('prov_id', 'ID Provinsi', 'required');
                $prov_id = $this->input->post("prov_id");

                $prov_id_decrypt = decrypt_text($prov_id);

                $sql = "SELECT K.`id` 'idkab', K.`nama_kabupaten`, K.`prov_id`, K.`ppd`, K.`urutan`, P.id, P.nama_provinsi 
                    FROM `kabupaten` K
                    LEFT JOIN provinsi P ON P.id = K.`prov_id`
                    WHERE P.id='$prov_id_decrypt' AND K.`nama_kabupaten` LIKE '%kabupaten%'";
                $list_data = $this->db->query($sql);
                $totalData = $this->db->query($sql)->num_rows();
                $data = array();
                foreach ($list_data->result() as $row) {
                    $nestedData = array();

                    $nestedData[] = encrypt_text($row->id);
                    $nestedData[] = $row->nama_kabupaten;

                    $data[] = $nestedData;
                }
                $json_data = array(
                    "data"  => $data   // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else
            die;
    }

    function kota_list()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('prov_id', 'ID Provinsi', 'required');
                $prov_id = $this->input->post("prov_id");

                $prov_id_decrypt = decrypt_text($prov_id);

                $sql = "SELECT K.`id` 'idkab', K.`nama_kabupaten`, K.`prov_id`, K.`ppd`, K.`urutan`, P.id, P.nama_provinsi 
                    FROM `kabupaten` K
                    LEFT JOIN provinsi P ON P.id = K.`prov_id`
                    WHERE P.id='$prov_id_decrypt' AND K.`nama_kabupaten` LIKE '%kota%'";
                $list_data = $this->db->query($sql);
                $totalData = $this->db->query($sql)->num_rows();
                $data = array();
                foreach ($list_data->result() as $row) {
                    $nestedData = array();

                    $nestedData[] = encrypt_text($row->id);
                    $nestedData[] = $row->nama_kabupaten;

                    $data[] = $nestedData;
                }
                $json_data = array(
                    "data"  => $data   // total data array
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
                //cari
                $idx = 0;
                $columns = array(
                    // datatable column index  => database column name
                    $idx++   => "K.id",
                    $idx++   => "K.nama_kabupaten",

                );
                $sql = "SELECT K.`id` 'idkab', K.`nama_kabupaten`, K.`prov_id`, K.`ppd`, K.`urutan`, P.id, P.nama_provinsi 
                    FROM `kabupaten` K
                    LEFT JOIN provinsi P ON P.id = K.`prov_id`
                    WHERE P.id='$idprov' AND K.`nama_kabupaten` LIKE '%kabupaten%'";

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
                    $id     = $row->id;

                    $nestedData[] = $row->idkab;
                    $nestedData[] = $row->nama_kabupaten;
                    $tmp = " data-id='" . encrypt_text($id) . "' ";
                    $nestedData[] = ""
                        //                            . "<input type='radio' class='checkboxx' name='noso' value='".$row->nama_kabupaten."' data-id='$row->nama_kabupaten' data-pv='$row->nama_provinsi' /> ";
                        . "<input type='radio' class='radio' name='group' value='" . $row->nama_kabupaten . "' data-id='$row->nama_kabupaten' data-pv='$row->nama_provinsi' /> ";
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

    function kot_datatable()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('id', 'ID', 'required');
                $prov = $this->input->post("id");
                $idprov = decrypt_text($prov);
                //cari
                $idx = 0;
                $columns = array(
                    // datatable column index  => database column name
                    $idx++   => "K.id",
                    $idx++   => "K.nama_kabupaten",

                );
                $sql = "SELECT K.`id` 'idkab', K.`nama_kabupaten`, K.`prov_id`, K.`ppd`, K.`urutan`, P.id, P.nama_provinsi 
                    FROM `kabupaten` K
                    LEFT JOIN provinsi P ON P.id = K.`prov_id`
                    WHERE P.id='$idprov' AND K.`nama_kabupaten` LIKE '%kota%'";

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
                    $id     = $row->id;

                    $nestedData[] = $row->idkab;
                    $nestedData[] = $row->nama_kabupaten;
                    $tmp = " data-id='" . encrypt_text($id) . "' ";
                    $nestedData[] = ""
                        //                            . "<input type='radio' class='checkboxx' name='noso' value='".$row->nama_kabupaten."' data-id='$row->nama_kabupaten' data-pv='$row->nama_provinsi' /> ";
                        . "<input type='radio' class='radio' name='group' value='" . $row->nama_kabupaten . "' data-id='$row->nama_kabupaten' data-pv='$row->nama_provinsi' /> ";
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

    //Pertumbuhan Ekonomi
    function pertumbuhan_ekomomi()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='1'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='1' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='1' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='1' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='1' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $val_cap_nas->tahun);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kabupaten
                        foreach ($indikator_capaian_kabupaten as $val_cap_kab) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kab->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='1' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kota
                        foreach ($indikator_capaian_kota as $val_cap_kota) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kota->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    function pdrb()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='2'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='2' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='2' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='2' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='2' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();
                //tahun untuk sorting data
                $sort_tahun = "";

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $val_cap_nas->tahun);
                    //masukan tahun untuk sorting data
                    $sort_tahun .= "'" . $val_cap_nas->tahun . "',";
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }
                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kabupaten
                        foreach ($indikator_capaian_kabupaten as $val_cap_kab) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kab->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='2' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kota
                        foreach ($indikator_capaian_kota as $val_cap_kota) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kota->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    function adhk()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='3'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='3' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='3' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='3' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='3' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $val_cap_nas->tahun);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kabupaten
                        foreach ($indikator_capaian_kabupaten as $val_cap_kab) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kab->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='3' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kota
                        foreach ($indikator_capaian_kota as $val_cap_kota) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kota->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    function jumlah_penganggur()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='4'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='4' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='4' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='4' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='4' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();
                $tahun2 = array();

                //array untuk menampung periode
                $periode = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $bulan[$val_cap_nas->periode] . "-" . $val_cap_nas->tahun);
                    array_push($tahun2, $val_cap_nas->tahun);
                    //memasukan data id_periode ke array $periode
                    array_push($periode, $val_cap_nas->id_periode);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);


                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kabupaten
                            foreach ($indikator_capaian_kabupaten as $val_cap_kab) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kab->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kab->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='4' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kabupaten
                            foreach ($indikator_capaian_kota as $val_cap_kota) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kota->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kota->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    function tingkat_pengangguran()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='6'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='6' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='6' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='6' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='6' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung tahun2
                $tahun2 = array();

                //array untuk menampung periode
                $periode = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $bulan[$val_cap_nas->periode] . "-" . $val_cap_nas->tahun);
                    //masukan data tahun ke array $tahun2
                    array_push($tahun2, $val_cap_nas->tahun);
                    //memasukan data id_periode ke array $periode
                    array_push($periode, $val_cap_nas->id_periode);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)number_format($val_cap_nas->nilai, 2, '.', ''));
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)number_format($val_cap_pro->nilai, 2, '.', ''));
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kabupaten
                            foreach ($indikator_capaian_kabupaten as $val_cap_kab) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kab->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)number_format($val_cap_kab->nilai, 2, '.', '');
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='6' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kota
                            foreach ($indikator_capaian_kota as $val_cap_kota) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kota->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)number_format($val_cap_kota->nilai, 2, '.', '');
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    function pembangunan_manusia()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='5'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='5' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='5' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='5' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='5' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $val_cap_nas->tahun);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kabupaten
                        foreach ($indikator_capaian_kabupaten as $val_cap_kab) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kab->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='5' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kota
                        foreach ($indikator_capaian_kota as $val_cap_kota) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kota->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    //Gini Rasio
    function gini_rasio()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='7'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='7' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='7' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='7' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='7' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung tahun2
                $tahun2 = array();

                //array untuk menampung periode
                $periode = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $bulan[$val_cap_nas->periode] . "-" . $val_cap_nas->tahun);
                    //masukan data tahun ke array $tahun2
                    array_push($tahun2, $val_cap_nas->tahun);
                    //memasukan data id_periode ke array $periode
                    array_push($periode, $val_cap_nas->id_periode);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kabupaten
                            foreach ($indikator_capaian_kabupaten as $val_cap_kab) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kab->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kab->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='7' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kota
                            foreach ($indikator_capaian_kota as $val_cap_kota) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kota->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kota->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    //Harapan Hidup
    function harapan_hidup()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='8'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='8' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='8' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='8' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='8' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $val_cap_nas->tahun);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kabupaten
                        foreach ($indikator_capaian_kabupaten as $val_cap_kab) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kab->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='8' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kota
                        foreach ($indikator_capaian_kota as $val_cap_kota) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kota->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    //Rata rata Lama Sekolah
    function rata_lama_sekolah()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='9'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='9' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='9' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='9' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='9' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $val_cap_nas->tahun);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kabupaten
                        foreach ($indikator_capaian_kabupaten as $val_cap_kab) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kab->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='9' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kota
                        foreach ($indikator_capaian_kota as $val_cap_kota) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kota->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    //harapan Lama Sekolah
    function harapan_lama_sekolah()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='10'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='10' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='10' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='10' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='10' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $val_cap_nas->tahun);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kabupaten
                        foreach ($indikator_capaian_kabupaten as $val_cap_kab) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kab->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='10' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kota
                        foreach ($indikator_capaian_kota as $val_cap_kota) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kota->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    //pengeluaran_perkapita
    function pengeluaran_perkapita()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='11'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='11' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='11' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='11' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='11' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $val_cap_nas->tahun);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kabupaten
                        foreach ($indikator_capaian_kabupaten as $val_cap_kab) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kab->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun[0] . "', '" . $tahun[1] . "', '" . $tahun[2] . "', '" . $tahun[3] . "', '" . $tahun[4] . "', '" . $tahun[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='11' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator kota
                        foreach ($indikator_capaian_kota as $val_cap_kota) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_kota->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    //Tingkat Kemiskinan
    function tinkat_kemiskinan()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='36'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                //get nilai indikator nasional
                // $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='36' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='36' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='36' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='36' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='36' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='36' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();
                //array untuk menampung tahun2
                $tahun2 = array();

                //array untuk menampung periode
                $periode = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $bulan[$val_cap_nas->periode] . "-" . $val_cap_nas->tahun);
                    //masukan data tahun ke array $tahun2
                    array_push($tahun2, $val_cap_nas->tahun);
                    //memasukan data id_periode ke array $periode
                    array_push($periode, $val_cap_nas->id_periode);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                        // $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kabupaten
                            foreach ($indikator_capaian_kabupaten as $val_cap_kab) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kab->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kab->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        //cari 6 data nilai indikator terbaru dari kota yang di pilih
                        // $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='36' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kabupaten
                            foreach ($indikator_capaian_kota as $val_cap_kota) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kota->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kota->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    //Indeks Kedalaman Kemiskinan
    function kedalaman_kemiskinan()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='39'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                //get nilai indikator nasional
                // $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='39' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='39' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='39' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='39' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='39' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='39' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung tahun2
                $tahun2 = array();

                //array untuk menampung periode
                $periode = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $bulan[$val_cap_nas->periode] . "-" . $val_cap_nas->tahun);
                    //masukan data tahun ke array $tahun2
                    array_push($tahun2, $val_cap_nas->tahun);
                    //memasukan data id_periode ke array $periode
                    array_push($periode, $val_cap_nas->id_periode);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kabupaten
                            foreach ($indikator_capaian_kabupaten as $val_cap_kab) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kab->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kab->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        //cari 6 data nilai indikator terbaru dari kota yang di pilih
                        // $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='39' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kota
                            foreach ($indikator_capaian_kota as $val_cap_kota) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kota->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kota->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    //Indeks Keparahan Kemiskinan
    function keparahan_kemiskinan()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='38'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                //get nilai indikator nasional
                // $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung tahun2
                $tahun2 = array();

                //array untuk menampung periode
                $periode = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $bulan[$val_cap_nas->periode] . "-" . $val_cap_nas->tahun);
                    //masukan data tahun ke array $tahun2
                    array_push($tahun2, $val_cap_nas->tahun);
                    //memasukan data id_periode ke array $periode
                    array_push($periode, $val_cap_nas->id_periode);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                        // $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                        // $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kabupaten
                            foreach ($indikator_capaian_kabupaten as $val_cap_kab) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kab->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kab->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        //cari 6 data nilai indikator terbaru dari kota yang di pilih
                        // $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kota
                            foreach ($indikator_capaian_kota as $val_cap_kota) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kota->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kota->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    //Jumlah Penduduk Miskin
    function penduduk_miskin()
    {
        if ($this->input->is_ajax_request()) {
            try {
                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //get indikator from database
                $indikator = $this->db->query("SELECT * FROM indikator where id='40'")->result();

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                //get nilai indikator nasional
                // $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='40' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                if ($selected_year) {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='40' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();
                } else {
                    //get nilai indikator nasional
                    $indikator_capaian_nasional = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='40' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                }

                //sorting dari nilai terkecil
                krsort($indikator_capaian_nasional);

                array_push($data_mentah, $indikator_capaian_nasional);

                //array untuk menampung tahun
                $tahun = array();

                //array untuk menampung tahun2
                $tahun2 = array();

                //array untuk menampung periode
                $periode = array();

                //array untuk menampung nilai indikator
                $data_nilai_indikator = array();
                //looping nilai indikator nasional
                foreach ($indikator_capaian_nasional as $val_cap_nas) {
                    //masukan data tahun ke array $tahun
                    array_push($tahun, $bulan[$val_cap_nas->periode] . "-" . $val_cap_nas->tahun);
                    //masukan data tahun ke array $tahun2
                    array_push($tahun2, $val_cap_nas->tahun);
                    //memasukan data id_periode ke array $periode
                    array_push($periode, $val_cap_nas->id_periode);
                    //masukan data nilai indikatoe ke $data_nilai_indikator
                    array_push($data_nilai_indikator, (float)$val_cap_nas->nilai);
                }

                //masukan data ke array $data_indikator
                $data_indikator['name'] = 'Nasional';
                $data_indikator['data'] = $data_nilai_indikator;

                //masukan ke array $catdata
                array_push($catdata, $data_indikator);

                //jika provinsi[0] tidak bernilai ''
                if ($pro[0] != '') {
                    //lakukan looping sebanyak jumlah data $pro
                    for ($i = 0; $i < count($pro); $i++) {
                        //get provinsi from database berdasarkan nama provinsi yang di masukan
                        $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                        //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                        // $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari provinsi yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_provinsi);

                        array_push($data_mentah, $indikator_capaian_provinsi);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();
                        //looping nilai indikator provinsi
                        foreach ($indikator_capaian_provinsi as $val_cap_pro) {
                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, (float)$val_cap_pro->nilai);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_provinsi[0]->nama_provinsi;
                        $data_indikator['data'] = $data_nilai_indikator;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kabupaten[0] tidak bernilai ''
                if ($kab[0] != '') {
                    //lakukan looping sebanyak jumlah data $kab
                    for ($j = 0; $j < count($kab); $j++) {
                        //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                        $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$j] . "' ")->result();

                        //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                        // $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kabupaten);

                        array_push($data_mentah, $indikator_capaian_kabupaten);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kabupaten
                            foreach ($indikator_capaian_kabupaten as $val_cap_kab) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kab->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kab->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kabupaten[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //jika kota[0] tidak bernilai ''
                if ($kot[0] != '') {
                    //lakukan looping sebanyak jumlah data $kot
                    for ($j = 0; $j < count($kot); $j++) {
                        //get kota from database berdasarkan nama kota yang di masukan
                        $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$j] . "' ")->result();

                        //cari 6 data nilai indikator terbaru dari kota yang di pilih
                        // $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='38' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();

                        if ($selected_year) {
                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();
                        } else {
                            //cari 6 data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT * FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN ('" . $tahun2[0] . "', '" . $tahun2[1] . "', '" . $tahun2[2] . "', '" . $tahun2[3] . "', '" . $tahun2[4] . "', '" . $tahun2[5] . "') AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='40' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 6")->result();
                        }

                        //urutkan dari terkecil
                        krsort($indikator_capaian_kota);

                        array_push($data_mentah, $indikator_capaian_kota);

                        //array untuk menampung nilai indikator
                        $data_nilai_indikator = array();

                        //looping berdasarkan jumlah periode
                        for ($i = 0; $i < count($periode); $i++) {

                            //variabel penampung nilai berdasarkan periode. default null
                            $nilai_per_periode = null;

                            //looping nilai indikator kota
                            foreach ($indikator_capaian_kota as $val_cap_kota) {

                                //jika nilai periode sama dengan nilai pada foerach id_periode
                                if ($periode[$i] == $val_cap_kota->id_periode) {
                                    //masukan nilai ke $nilai_per_periode
                                    $nilai_per_periode = (float)$val_cap_kota->nilai;
                                }
                            }

                            //masukan data nilai indikator ke $data_nilai_indikator
                            array_push($data_nilai_indikator, $nilai_per_periode);
                        }

                        //masukan data ke array $data_indikator
                        $data_indikator['name'] = $list_kota[0]->nama_kabupaten;
                        $data_indikator['data'] = $data_nilai_indikator;
                        $data_indikator['connectNulls'] = true;

                        //masukan ke array $catdata
                        array_push($catdata, $data_indikator);
                    }
                }

                //masukan data ke $json_data
                $json_data = array(
                    "indikator"      => $indikator,
                    "tahun"          => $tahun,
                    "data_indikator" => $catdata,
                    "data_mentah"       => $data_mentah,
                    "sumber"         => "Sumber : Badan Pusat Statistik"
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

    //tabel responsif indikator makro
    function indikator_makro_tabel()
    {
        if ($this->input->is_ajax_request()) {
            try {

                //validasi
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $this->form_validation->set_rules('indikator', 'Indikator', 'required');
                $this->form_validation->set_rules('tahun', 'Tahun', 'required');

                //inputan
                $pro = explode(',', $this->input->post("provinsi"));
                $kab = explode(',', $this->input->post("kabupaten"));
                $kot = explode(',', $this->input->post("kota"));
                $selected_indicator = explode(',', $this->input->post("indikator"));
                $selected_year = $this->input->post("tahun");

                //tanggal
                $bulan = array('0' => '', '00' => '', '000' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des');
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

                //array untuk menampung data_indikator
                $catdata = array();

                $data_mentah = array();

                $data_all_all_nilai_indikator = array();

                //get nilai indikator nasional
                // $indikator_capaian_nasional = $this->db->query("SELECT ni.*, i.nama_indikator FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id WHERE id_indikator='6' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='6' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 10")->result();

                if (($selected_indicator) && ($selected_year)) {

                    for ($k = 0; $k < count($selected_indicator); $k++) {

                        $data_all_nilai_indikator[$k]['nama_indikator'] = array();
                        $data_all_nilai_indikator[$k]['tanggal'] = array();
                        $data_all_nilai_indikator[$k]['satuan'] = array();
                        // $data_all_nilai_indikator[$k]['tanggal'] = array();
                        $data_all_nilai_indikator[$k]['data'] = array();

                        //array untuk menampung tahun
                        $tanggal['tahun'] = array();

                        //array untuk menampung periode
                        $tanggal['periode'] = array();

                        $indikator_capaian_nasional = $this->db->query("SELECT ni.*, i.nama_indikator FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();

                        if ($indikator_capaian_nasional != null) {
                            //sorting dari nilai terkecil
                            krsort($indikator_capaian_nasional);

                            array_push($data_mentah, $indikator_capaian_nasional);

                            $data_nilai_indikator[$k]['nama_indikator'] = $indikator_capaian_nasional[0]->nama_indikator;
                            $data_nilai_indikator[$k]['satuan'] = $indikator_capaian_nasional[0]->satuan;

                            //array untuk menampung nilai indikator
                            $data_nilai_indikator[$k]['wilayah'] = array();
                            $data_nilai_indikator[$k]['nilai'] = array();

                            //looping nilai indikator nasional
                            foreach ($indikator_capaian_nasional as $val_cap_nas) {
                                //masukan data tahun ke array $tahun
                                array_push($tanggal['tahun'], $val_cap_nas->tahun);
                                //memasukan data id_periode ke array $periode
                                array_push($tanggal['periode'], $bulan[$val_cap_nas->periode]);

                                if ($val_cap_nas->wilayah == '1000') {
                                    array_push($data_nilai_indikator[$k]['wilayah'], 'Nasional');
                                } else {
                                    array_push($data_nilai_indikator[$k]['wilayah'], $val_cap_nas->wilayah);
                                }

                                if (($val_cap_nas->id_indikator == '2') || ($val_cap_nas->id_indikator == '3') || ($val_cap_nas->id_indikator == '4') || ($val_cap_nas->id_indikator == '11') || ($val_cap_nas->id_indikator == '37') || ($val_cap_nas->id_indikator == '40')) {
                                    array_push($data_nilai_indikator[$k]['nilai'], number_format((float)$val_cap_nas->nilai));
                                } else {
                                    array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_nas->nilai);
                                }
                            }

                            array_push($data_all_nilai_indikator[$k]['nama_indikator'], $data_nilai_indikator[$k]['nama_indikator']);
                            array_push($data_all_nilai_indikator[$k]['tanggal'], $tanggal);
                            array_push($data_all_nilai_indikator[$k]['satuan'], $data_nilai_indikator[$k]['satuan']);
                            array_push($data_all_nilai_indikator[$k]['data'], $data_nilai_indikator[$k]);


                            //jika provinsi[0] tidak bernilai ''
                            if ($pro[0] != '') {

                                //lakukan looping sebanyak jumlah data $pro
                                for ($i = 0; $i < count($pro); $i++) {

                                    //array untuk menampung tahun
                                    $tanggal['tahun'] = array();

                                    //array untuk menampung periode
                                    $tanggal['periode'] = array();

                                    //get provinsi from database berdasarkan nama provinsi yang di masukan
                                    $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                                    //cari data nilai indikator terbaru dari kabupaten yang di pilih
                                    $indikator_capaian_provinsi = $this->db->query("SELECT ni.*, i.nama_indikator, wil.nama_daerah AS nama_daerah FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON ni.wilayah = wil.id WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();

                                    //urutkan dari terkecil
                                    krsort($indikator_capaian_provinsi);

                                    array_push($data_mentah, $indikator_capaian_provinsi);

                                    //array untuk menampung nilai indikator
                                    $data_nilai_indikator[$k]['wilayah'] = array();
                                    $data_nilai_indikator[$k]['nilai'] = array();

                                    //looping nilai indikator nasional
                                    foreach ($indikator_capaian_provinsi as $val_cap_pro) {

                                        //masukan data tahun ke array $tahun
                                        array_push($tanggal['tahun'], $val_cap_pro->tahun);
                                        //memasukan data id_periode ke array $periode
                                        array_push($tanggal['periode'], $bulan[$val_cap_pro->periode]);

                                        array_push($data_nilai_indikator[$k]['wilayah'], $val_cap_pro->nama_daerah);

                                        if (($val_cap_pro->id_indikator == '2') || ($val_cap_pro->id_indikator == '3') || ($val_cap_pro->id_indikator == '4') || ($val_cap_pro->id_indikator == '11') || ($val_cap_pro->id_indikator == '37') || ($val_cap_pro->id_indikator == '40')) {
                                            array_push($data_nilai_indikator[$k]['nilai'], number_format((float)$val_cap_pro->nilai));
                                        } else {
                                            array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_pro->nilai);
                                        }
                                    }

                                    array_push($data_all_nilai_indikator[$k]['nama_indikator'], $data_nilai_indikator[$k]['nama_indikator']);
                                    array_push($data_all_nilai_indikator[$k]['tanggal'], $tanggal);
                                    array_push($data_all_nilai_indikator[$k]['satuan'], $data_nilai_indikator[$k]['satuan']);
                                    array_push($data_all_nilai_indikator[$k]['data'], $data_nilai_indikator[$k]);
                                }
                            }

                            //jika kabupaten[0] tidak bernilai ''
                            if ($kab[0] != '') {

                                //lakukan looping sebanyak jumlah data $pro
                                for ($i = 0; $i < count($kab); $i++) {

                                    //array untuk menampung tahun
                                    $tanggal['tahun'] = array();

                                    //array untuk menampung periode
                                    $tanggal['periode'] = array();

                                    //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                                    $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$i] . "' ")->result();

                                    //cari data nilai indikator terbaru dari kabupaten yang di pilih
                                    $indikator_capaian_kabupaten = $this->db->query("SELECT ni.*, i.nama_indikator, wil.nama_daerah AS nama_daerah FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON ni.wilayah = wil.id WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();

                                    //urutkan dari terkecil
                                    krsort($indikator_capaian_kabupaten);

                                    array_push($data_mentah, $indikator_capaian_kabupaten);

                                    //array untuk menampung nilai indikator
                                    $data_nilai_indikator[$k]['wilayah'] = array();
                                    $data_nilai_indikator[$k]['nilai'] = array();

                                    //looping nilai indikator nasional
                                    foreach ($indikator_capaian_kabupaten as $val_cap_kab) {

                                        //masukan data tahun ke array $tahun
                                        array_push($tanggal['tahun'], $val_cap_kab->tahun);
                                        //memasukan data id_periode ke array $periode
                                        array_push($tanggal['periode'], $bulan[$val_cap_kab->periode]);

                                        array_push($data_nilai_indikator[$k]['wilayah'], $val_cap_kab->nama_daerah);

                                        if (($val_cap_kab->id_indikator == '2') || ($val_cap_kab->id_indikator == '3') || ($val_cap_kab->id_indikator == '4') || ($val_cap_kab->id_indikator == '11') || ($val_cap_kab->id_indikator == '37') || ($val_cap_kab->id_indikator == '40')) {
                                            array_push($data_nilai_indikator[$k]['nilai'], number_format((float)$val_cap_kab->nilai));
                                        } else {
                                            array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_kab->nilai);
                                        }
                                    }

                                    array_push($data_all_nilai_indikator[$k]['nama_indikator'], $data_nilai_indikator[$k]['nama_indikator']);
                                    array_push($data_all_nilai_indikator[$k]['tanggal'], $tanggal);
                                    array_push($data_all_nilai_indikator[$k]['satuan'], $data_nilai_indikator[$k]['satuan']);
                                    array_push($data_all_nilai_indikator[$k]['data'], $data_nilai_indikator[$k]);
                                }
                            }

                            //jika kota[0] tidak bernilai ''
                            if ($kot[0] != '') {

                                //lakukan looping sebanyak jumlah data $pro
                                for ($i = 0; $i < count($kot); $i++) {

                                    //array untuk menampung tahun
                                    $tanggal['tahun'] = array();

                                    //array untuk menampung periode
                                    $tanggal['periode'] = array();

                                    //get kota from database berdasarkan nama kota yang di masukan
                                    $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$i] . "' ")->result();

                                    //cari data nilai indikator terbaru dari kota yang di pilih
                                    $indikator_capaian_kota = $this->db->query("SELECT ni.*, i.nama_indikator, wil.nama_daerah AS nama_daerah FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON ni.wilayah = wil.id WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();

                                    //urutkan dari terkecil
                                    krsort($indikator_capaian_kota);

                                    array_push($data_mentah, $indikator_capaian_kota);

                                    //array untuk menampung nilai indikator
                                    $data_nilai_indikator[$k]['wilayah'] = array();
                                    $data_nilai_indikator[$k]['nilai'] = array();

                                    //looping nilai indikator nasional
                                    foreach ($indikator_capaian_kota as $val_cap_kota) {

                                        //masukan data tahun ke array $tahun
                                        array_push($tanggal['tahun'], $val_cap_kota->tahun);
                                        //memasukan data id_periode ke array $periode
                                        array_push($tanggal['periode'], $bulan[$val_cap_kota->periode]);

                                        array_push($data_nilai_indikator[$k]['wilayah'], $val_cap_kota->nama_daerah);

                                        if (($val_cap_kota->id_indikator == '2') || ($val_cap_kota->id_indikator == '3') || ($val_cap_kota->id_indikator == '4') || ($val_cap_kota->id_indikator == '11') || ($val_cap_kota->id_indikator == '37') || ($val_cap_kota->id_indikator == '40')) {
                                            array_push($data_nilai_indikator[$k]['nilai'], number_format((float)$val_cap_kota->nilai));
                                        } else {
                                            array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_kota->nilai);
                                        }
                                    }

                                    array_push($data_all_nilai_indikator[$k]['nama_indikator'], $data_nilai_indikator[$k]['nama_indikator']);
                                    array_push($data_all_nilai_indikator[$k]['tanggal'], $tanggal);
                                    array_push($data_all_nilai_indikator[$k]['satuan'], $data_nilai_indikator[$k]['satuan']);
                                    array_push($data_all_nilai_indikator[$k]['data'], $data_nilai_indikator[$k]);
                                }
                            }
                            array_push($data_all_all_nilai_indikator, $data_all_nilai_indikator[$k]);
                        } else {
                            $data_all_all_nilai_indikator = "";
                        }
                    }
                } else {
                    $data_all_all_nilai_indikator = "";
                }


                if ($data_all_all_nilai_indikator != '') {
                    $html = '';
                    // $html .= '<div class="btn-group mb-2" style="float: left;">';
                    // $html .= '<button type="button" class="btn btn-secondary waves-effect button-csv-tabel-indikator-makro">CSV</button>';
                    // $html .= '<button type="button" class="btn btn-secondary waves-effect button-excel-tabel-indikator-makro">Excel</button>';
                    // $html .= '<button type="button" class="btn btn-secondary waves-effect button-pdf-tabel-indikator-makro">PDF</button>';
                    // $html .= '</div>';
                    $html .= '<table id="datatables-indikator-makro" class="table table-bordered table-striped" style="font-size: 10px; cursor: grab; 1px solid #e3e0e0;">';

                    $html .= '<thead>';

                    $html .= '<tr>';
                    $html .= '<th rowspan="3" style="background-color: #645A82; color: white; z-index: 93;">';
                    $html .= '<b><center>Provinsi/Kabupaten/Kota</center></b>';
                    $html .= '</th>';

                    //nama indikator
                    for ($i = 0; $i < count($data_all_all_nilai_indikator); $i++) {
                        if ($data_all_all_nilai_indikator[$i]['tanggal'][0]['periode'][0] == '') {
                            $html .= '<th colspan="' . count($data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun']) . '" style="background-color: #645A82; color: white;">';
                            $html .= '<b><center>' . ($data_all_all_nilai_indikator[$i]['satuan'][0] != '' ? $data_all_all_nilai_indikator[$i]['nama_indikator'][0] . ' (' . $data_all_all_nilai_indikator[$i]['satuan'][0] . ')' : $data_all_all_nilai_indikator[$i]['nama_indikator'][0]) . '</center></b>';
                            $html .= '</th>';
                        } else {
                            $html .= '<th colspan="' . count($data_all_all_nilai_indikator[$i]['tanggal'][0]['periode']) . '" style="background-color: #645A82; color: white;">';
                            $html .= '<b><center>' . ($data_all_all_nilai_indikator[$i]['satuan'][0] != '' ? $data_all_all_nilai_indikator[$i]['nama_indikator'][0] . ' (' . $data_all_all_nilai_indikator[$i]['satuan'][0] . ')' : $data_all_all_nilai_indikator[$i]['nama_indikator'][0]) . '</center></b>';
                            $html .= '</th>';
                        }
                    }
                    //end nama indikator
                    $html .= '</tr>';

                    $html .= '<tr>';
                    //tahun
                    for ($i = 0; $i < count($data_all_all_nilai_indikator); $i++) {
                        $duplicate_years = '';
                        $a_count_duplicate_years = array_count_values($data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun']);
                        for ($j = 0; $j < count($data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun']); $j++) {
                            if ($data_all_all_nilai_indikator[$i]['tanggal'][0]['periode'][0] == '') {
                                $html .= '<th rowspan="2" style="background-color: #645A82; color: white;">';
                                $html .= '<b><center>' . $data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun'][$j] . '</center></b>';
                                $html .= '</th>';
                            } else {
                                if ($duplicate_years != $data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun'][$j]) {
                                    $duplicate_years = $data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun'][$j];
                                    $html .= '<th colspan="' . $a_count_duplicate_years[$data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun'][$j]] . '" style="background-color: #645A82; color: white;">';
                                    $html .= '<b><center>' . $duplicate_years . '</center></b>';
                                    $html .= '</th>';
                                }
                            }
                        }
                    }
                    //end tahun
                    $html .= '</tr>';

                    $html .= '<tr>';
                    //periode
                    for ($i = 0; $i < count($data_all_all_nilai_indikator); $i++) {
                        if ($data_all_all_nilai_indikator[$i]['tanggal'][0]['periode'][0] != '') {
                            for ($j = 0; $j < count($data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun']); $j++) {
                                $html .= '<th style="background-color: #645A82; color: white;">';
                                $html .= '<b><center>' . $data_all_all_nilai_indikator[$i]['tanggal'][0]['periode'][$j] . '</center></b>';
                                $html .= '</th>';
                            }
                        }
                    }
                    //end periode
                    $html .= '</tr>';

                    $html .= '<tr>';
                    $html .= '<th style="background-color: #74737a; color: white;">Nasional</th>';

                    for ($i = 0; $i < count($data_all_all_nilai_indikator); $i++) {
                        for ($j = 0; $j < count($data_all_all_nilai_indikator[$i]['data'][0]['nilai']); $j++) {
                            $html .= '<th style="background-color: #74737a; color: white;">' . $data_all_all_nilai_indikator[$i]['data'][0]['nilai'][$j] . '</th>';
                        }
                    }

                    $html .= '</tr>';

                    $html .= '</thead>';

                    $html .= '<tbody>';

                    //wilayah
                    for ($j = 1; $j < count($data_all_all_nilai_indikator[0]['data']); $j++) {
                        $html .= '<tr>';
                        $duplicate_region = '';
                        //indikator
                        for ($i = 0; $i < count($data_all_all_nilai_indikator); $i++) {
                            //nilai
                            $l = 0;
                            while ($l < count($data_all_all_nilai_indikator[$i]['data'][$j]['wilayah'])) {
                                for ($k = 0; $k < count($data_all_all_nilai_indikator[$i]['data'][0]['wilayah']); $k++) {
                                    if ($l != count($data_all_all_nilai_indikator[$i]['data'][$j]['wilayah'])) {
                                        if ($duplicate_region != $data_all_all_nilai_indikator[$i]['data'][$j]['wilayah'][$l]) {
                                            $duplicate_region = $data_all_all_nilai_indikator[$i]['data'][$j]['wilayah'][$l];
                                            $html .= '<td style="background-color: #74737a; color: white;">' . $duplicate_region . '</td>';
                                        }
                                        if ($data_all_all_nilai_indikator[$i]['tanggal'][0]['periode'][$k] == $data_all_all_nilai_indikator[$i]['tanggal'][$j]['periode'][$l]) {
                                            $html .= '<td style="' . ($i % 2 == 0 ? "background-color: #ffffff;" : "background-color: #f5f4f5;") . '">' . $data_all_all_nilai_indikator[$i]['data'][$j]['nilai'][$l] . '</td>';
                                            $l++;
                                        } else {
                                            $html .= '<td style="' . ($i % 2 == 0 ? "background-color: #ffffff;" : "background-color: #f5f4f5;") . '"><center>-</center></td>';
                                        }
                                    } else {
                                        $html .= '<td style="' . ($i % 2 == 0 ? "background-color: #ffffff;" : "background-color: #f5f4f5;") . '"><center>-</center></td>';
                                    }
                                }
                            }
                        }
                        $html .= '</tr>';
                    }

                    $html .= '</tbody>';
                    $html .= '</table>';
                } else {
                    $html = "";
                }

                //masukan data ke $json_data
                $json_data = array(
                    // "nama_indikator"    => $selected_indicator,
                    // "jumlah_data"       => count(($indikator_capaian_nasional != null ? $indikator_capaian_nasional : '')),
                    // "tanggal"           => array(
                    //     "tahun"     => ($tahun != null ? $tahun : ''),
                    //     "periode"   => ($periode != null ? $periode : ''),
                    // ),
                    "html"              => $html,
                    "data"              => ($data_all_all_nilai_indikator != null ? $data_all_all_nilai_indikator : ''),
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

    function export_indikator_makro_tabel()
    {

        $pro = explode(',', $_GET['provinsi']);
        $kab = explode(',', $_GET['kabupaten']);
        $kot = explode(',', $_GET['kota']);
        $selected_indicator = explode(',', $_GET['indikator']);
        $selected_year = $_GET['tahun'];

        //tanggal
        $bulan = array('0' => '', '00' => '', '000' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des');
        $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');

        //array untuk menampung data_indikator
        $catdata = array();

        $data_mentah = array();

        $data_all_all_nilai_indikator = array();

        //get nilai indikator nasional
        // $indikator_capaian_nasional = $this->db->query("SELECT ni.*, i.nama_indikator FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id WHERE id_indikator='6' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='6' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC LIMIT 10")->result();

        if (($selected_indicator) && ($selected_year)) {

            for ($k = 0; $k < count($selected_indicator); $k++) {

                $data_all_nilai_indikator[$k]['nama_indikator'] = array();
                $data_all_nilai_indikator[$k]['tanggal'] = array();
                $data_all_nilai_indikator[$k]['satuan'] = array();
                // $data_all_nilai_indikator[$k]['tanggal'] = array();
                $data_all_nilai_indikator[$k]['data'] = array();

                //array untuk menampung tahun
                $tanggal['tahun'] = array();

                //array untuk menampung periode
                $tanggal['periode'] = array();

                $indikator_capaian_nasional = $this->db->query("SELECT ni.*, i.nama_indikator FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='1000' AND periode !='01' AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='1000' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) AND tahun IN(" . $selected_year . ") GROUP BY id_periode ORDER BY id_periode DESC")->result();

                if ($indikator_capaian_nasional != null) {

                    //sorting dari nilai terkecil
                    krsort($indikator_capaian_nasional);

                    array_push($data_mentah, $indikator_capaian_nasional);

                    $data_nilai_indikator[$k]['nama_indikator'] = $indikator_capaian_nasional[0]->nama_indikator;
                    $data_nilai_indikator[$k]['satuan'] = $indikator_capaian_nasional[0]->satuan;

                    //array untuk menampung nilai indikator
                    $data_nilai_indikator[$k]['wilayah'] = array();
                    $data_nilai_indikator[$k]['nilai'] = array();

                    //looping nilai indikator nasional
                    foreach ($indikator_capaian_nasional as $val_cap_nas) {
                        //masukan data tahun ke array $tahun
                        array_push($tanggal['tahun'], $val_cap_nas->tahun);
                        //memasukan data id_periode ke array $periode
                        array_push($tanggal['periode'], $bulan[$val_cap_nas->periode]);

                        if ($val_cap_nas->wilayah == '1000') {
                            array_push($data_nilai_indikator[$k]['wilayah'], 'Nasional');
                        } else {
                            array_push($data_nilai_indikator[$k]['wilayah'], $val_cap_nas->wilayah);
                        }

                        if (($val_cap_nas->id_indikator == '2') || ($val_cap_nas->id_indikator == '3') || ($val_cap_nas->id_indikator == '4') || ($val_cap_nas->id_indikator == '11') || ($val_cap_nas->id_indikator == '37') || ($val_cap_nas->id_indikator == '40')) {
                            array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_nas->nilai);
                        } else {
                            array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_nas->nilai);
                        }
                    }

                    array_push($data_all_nilai_indikator[$k]['nama_indikator'], $data_nilai_indikator[$k]['nama_indikator']);
                    array_push($data_all_nilai_indikator[$k]['tanggal'], $tanggal);
                    array_push($data_all_nilai_indikator[$k]['satuan'], $data_nilai_indikator[$k]['satuan']);
                    array_push($data_all_nilai_indikator[$k]['data'], $data_nilai_indikator[$k]);

                    //jika provinsi[0] tidak bernilai ''
                    if ($pro[0] != '') {

                        //lakukan looping sebanyak jumlah data $pro
                        for ($i = 0; $i < count($pro); $i++) {

                            //array untuk menampung tahun
                            $tanggal['tahun'] = array();

                            //array untuk menampung periode
                            $tanggal['periode'] = array();

                            //get provinsi from database berdasarkan nama provinsi yang di masukan
                            $list_provinsi = $this->db->query("SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro[$i] . "' ")->result();

                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_provinsi = $this->db->query("SELECT ni.*, i.nama_indikator, wil.nama_daerah AS nama_daerah FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON ni.wilayah = wil.id WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_provinsi[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_provinsi[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();

                            //urutkan dari terkecil
                            krsort($indikator_capaian_provinsi);

                            array_push($data_mentah, $indikator_capaian_provinsi);

                            //array untuk menampung nilai indikator
                            $data_nilai_indikator[$k]['wilayah'] = array();
                            $data_nilai_indikator[$k]['nilai'] = array();

                            //looping nilai indikator nasional
                            foreach ($indikator_capaian_provinsi as $val_cap_pro) {

                                //masukan data tahun ke array $tahun
                                array_push($tanggal['tahun'], $val_cap_pro->tahun);
                                //memasukan data id_periode ke array $periode
                                array_push($tanggal['periode'], $bulan[$val_cap_pro->periode]);

                                array_push($data_nilai_indikator[$k]['wilayah'], $val_cap_pro->nama_daerah);

                                if (($val_cap_pro->id_indikator == '2') || ($val_cap_pro->id_indikator == '3') || ($val_cap_pro->id_indikator == '4') || ($val_cap_pro->id_indikator == '11') || ($val_cap_pro->id_indikator == '37') || ($val_cap_pro->id_indikator == '40')) {
                                    array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_pro->nilai);
                                } else {
                                    array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_pro->nilai);
                                }
                            }

                            array_push($data_all_nilai_indikator[$k]['nama_indikator'], $data_nilai_indikator[$k]['nama_indikator']);
                            array_push($data_all_nilai_indikator[$k]['tanggal'], $tanggal);
                            array_push($data_all_nilai_indikator[$k]['satuan'], $data_nilai_indikator[$k]['satuan']);
                            array_push($data_all_nilai_indikator[$k]['data'], $data_nilai_indikator[$k]);
                        }
                    }

                    //jika kabupaten[0] tidak bernilai ''
                    if ($kab[0] != '') {

                        //lakukan looping sebanyak jumlah data $pro
                        for ($i = 0; $i < count($kab); $i++) {

                            //array untuk menampung tahun
                            $tanggal['tahun'] = array();

                            //array untuk menampung periode
                            $tanggal['periode'] = array();

                            //get kabupaten from database berdasarkan nama kabupaten yang di masukan
                            $list_kabupaten = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab[$i] . "' ")->result();

                            //cari data nilai indikator terbaru dari kabupaten yang di pilih
                            $indikator_capaian_kabupaten = $this->db->query("SELECT ni.*, i.nama_indikator, wil.nama_daerah AS nama_daerah FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON ni.wilayah = wil.id WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_kabupaten[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();

                            //urutkan dari terkecil
                            krsort($indikator_capaian_kabupaten);

                            array_push($data_mentah, $indikator_capaian_kabupaten);

                            //array untuk menampung nilai indikator
                            $data_nilai_indikator[$k]['wilayah'] = array();
                            $data_nilai_indikator[$k]['nilai'] = array();

                            //looping nilai indikator nasional
                            foreach ($indikator_capaian_kabupaten as $val_cap_kab) {

                                //masukan data tahun ke array $tahun
                                array_push($tanggal['tahun'], $val_cap_kab->tahun);
                                //memasukan data id_periode ke array $periode
                                array_push($tanggal['periode'], $bulan[$val_cap_kab->periode]);

                                array_push($data_nilai_indikator[$k]['wilayah'], $val_cap_kab->nama_daerah);

                                if (($val_cap_kab->id_indikator == '2') || ($val_cap_kab->id_indikator == '3') || ($val_cap_kab->id_indikator == '4') || ($val_cap_kab->id_indikator == '11') || ($val_cap_kab->id_indikator == '37') || ($val_cap_kab->id_indikator == '40')) {
                                    array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_kab->nilai);
                                } else {
                                    array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_kab->nilai);
                                }
                            }

                            array_push($data_all_nilai_indikator[$k]['nama_indikator'], $data_nilai_indikator[$k]['nama_indikator']);
                            array_push($data_all_nilai_indikator[$k]['tanggal'], $tanggal);
                            array_push($data_all_nilai_indikator[$k]['satuan'], $data_nilai_indikator[$k]['satuan']);
                            array_push($data_all_nilai_indikator[$k]['data'], $data_nilai_indikator[$k]);
                        }
                    }

                    //jika kota[0] tidak bernilai ''
                    if ($kot[0] != '') {

                        //lakukan looping sebanyak jumlah data $pro
                        for ($i = 0; $i < count($kot); $i++) {

                            //array untuk menampung tahun
                            $tanggal['tahun'] = array();

                            //array untuk menampung periode
                            $tanggal['periode'] = array();

                            //get kota from database berdasarkan nama kota yang di masukan
                            $list_kota = $this->db->query("SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot[$i] . "' ")->result();

                            //cari data nilai indikator terbaru dari kota yang di pilih
                            $indikator_capaian_kota = $this->db->query("SELECT ni.*, i.nama_indikator, wil.nama_daerah AS nama_daerah FROM nilai_indikator ni JOIN indikator i ON ni.id_indikator = i.id LEFT JOIN (SELECT prov.id AS id, prov.nama_provinsi AS nama_daerah FROM provinsi prov UNION SELECT kabkot.id AS id, kabkot.nama_kabupaten AS nama_daerah FROM kabupaten kabkot) wil ON ni.wilayah = wil.id WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_kota[0]->id . "' AND periode !='01' AND tahun IN (" . $selected_year . ") AND (id_periode, versi) IN (SELECT id_periode, MAX(versi) AS versi FROM nilai_indikator WHERE id_indikator='" . $selected_indicator[$k] . "' AND wilayah='" . $list_kota[0]->id . "' AND periode != '01' GROUP BY id_periode ORDER BY id_periode DESC) GROUP BY id_periode ORDER BY id_periode DESC")->result();

                            //urutkan dari terkecil
                            krsort($indikator_capaian_kota);

                            array_push($data_mentah, $indikator_capaian_kota);

                            //array untuk menampung nilai indikator
                            $data_nilai_indikator[$k]['wilayah'] = array();
                            $data_nilai_indikator[$k]['nilai'] = array();

                            //looping nilai indikator nasional
                            foreach ($indikator_capaian_kota as $val_cap_kota) {

                                //masukan data tahun ke array $tahun
                                array_push($tanggal['tahun'], $val_cap_kota->tahun);
                                //memasukan data id_periode ke array $periode
                                array_push($tanggal['periode'], $bulan[$val_cap_kota->periode]);

                                array_push($data_nilai_indikator[$k]['wilayah'], $val_cap_kota->nama_daerah);

                                if (($val_cap_kota->id_indikator == '2') || ($val_cap_kota->id_indikator == '3') || ($val_cap_kota->id_indikator == '4') || ($val_cap_kota->id_indikator == '11') || ($val_cap_kota->id_indikator == '37') || ($val_cap_kota->id_indikator == '40')) {
                                    array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_kota->nilai);
                                } else {
                                    array_push($data_nilai_indikator[$k]['nilai'], (float)$val_cap_kota->nilai);
                                }
                            }

                            array_push($data_all_nilai_indikator[$k]['nama_indikator'], $data_nilai_indikator[$k]['nama_indikator']);
                            array_push($data_all_nilai_indikator[$k]['tanggal'], $tanggal);
                            array_push($data_all_nilai_indikator[$k]['satuan'], $data_nilai_indikator[$k]['satuan']);
                            array_push($data_all_nilai_indikator[$k]['data'], $data_nilai_indikator[$k]);
                        }
                    }
                    array_push($data_all_all_nilai_indikator, $data_all_nilai_indikator[$k]);
                } else {
                    $data_all_all_nilai_indikator = "";
                }
            }
        } else {
            $data_all_all_nilai_indikator = "";
        }


        if ($data_all_all_nilai_indikator != '') {
            $this->load->library("Excel");
            $sharedStyleTitles = new PHPExcel_Style();

            $this->excel->getActiveSheet()->getSheetView()->setZoomScale(50);
            $this->excel->getSheet(0)->setTitle('Pencapaian Indikator Makro');

            // garis
            $sharedStyleTitles->applyFromArray(
                array(
                    'borders' =>
                    array(
                        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                        'top'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                        'left'   => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                        'right'  => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                    )
                )
            );

            $this->excel->getActiveSheet()->setCellValue('A1', 'PENCAPAIAN INDIKATOR MAKRO');
            $this->excel->getActiveSheet()->setCellValue('A2', 'DIUNDUH TANGGAL : ' . date('d-m-Y'));
            $this->excel->getActiveSheet()->mergeCells('A1:P1');
            $this->excel->getActiveSheet()->mergeCells('A2:B2');
            $this->excel->getActiveSheet()->getStyle('A1:IV255')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, 3, 'Provinsi/Kabupaten/Kota');
            $this->excel->getActiveSheet()->mergeCells('A3:A5');
            //style border
            $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2(0, 0, 3, 6))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('DDDDDD');
            $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2(0, 0, 3, 5))->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'D9E1F2')
                    )
                )
            );

            $start = 1;
            $colborder1 = 1;
            $endcol = 0;
            // nama indikator
            for ($i = 0; $i < count($data_all_all_nilai_indikator); $i++) {
                $text = ($data_all_all_nilai_indikator[$i]['satuan'][0] != '' ? $data_all_all_nilai_indikator[$i]['nama_indikator'][0] . ' (' . $data_all_all_nilai_indikator[$i]['satuan'][0] . ')' : $data_all_all_nilai_indikator[$i]['nama_indikator'][0]);
                if ($data_all_all_nilai_indikator[$i]['tanggal'][0]['periode'][0] == '') {
                    $stop = count($data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun']);
                    $colborder2 = count($data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun']);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($start, 3, $text);
                    $this->excel->getActiveSheet()->mergeCells($this->cellsToMergeByColsRow($start, (($start + $stop) - 1), 3));
                } else {
                    $stop = count($data_all_all_nilai_indikator[$i]['tanggal'][0]['periode']);
                    $colborder2 = count($data_all_all_nilai_indikator[$i]['tanggal'][0]['periode']);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($start, 3, $text);
                    $this->excel->getActiveSheet()->mergeCells($this->cellsToMergeByColsRow($start, (($start + $stop) - 1), 3));
                }
                $start = $start + $stop;
                $endcol = $endcol + $colborder2;
            }
            //style border
            $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2($colborder1, ($endcol + 1), 3, 3))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('DDDDDD');
            $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2($colborder1, ($endcol), 3, 3))->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'D9E1F2')
                    )
                )
            );

            //end nama indikator

            $column = 1;
            $num_of_periode = 1;
            $colborderyear1 = 1;
            $endcolyear = 0;
            //tahun
            for ($i = 0; $i < count($data_all_all_nilai_indikator); $i++) {
                $duplicate_years = '';
                $a_count_duplicate_years = array_count_values($data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun']);
                for ($j = 0; $j < count($data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun']); $j++) {
                    if ($data_all_all_nilai_indikator[$i]['tanggal'][0]['periode'][0] == '') {
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow($column, 4, $data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun'][$j]);
                        $this->excel->getActiveSheet()->mergeCells($this->cellsToMergeByOnlyRow(4, 5, $column));
                        //style border
                        $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2($column, $column, 4, 7))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('DDDDDD');
                        $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2($column, $column, 4, 5))->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'D9E1F2')
                                )
                            )
                        );
                        $column = $column + 1;
                    } else {
                        if ($duplicate_years != $data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun'][$j]) {
                            $duplicate_years = $data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun'][$j];

                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow($column, 4, $duplicate_years);
                            $this->excel->getActiveSheet()->mergeCells($this->cellsToMergeByColsRow($column, ($column + 1), 4));
                            $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2($column, ($column + 1), 4, 5))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('DDDDDD');
                            $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2($column, ($column + 1), 4, 5))->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'D9E1F2')
                                    )
                                )
                            );
                            $column = $column + $a_count_duplicate_years[$data_all_all_nilai_indikator[$i]['tanggal'][0]['tahun'][$j]];
                        }
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow($num_of_periode, 5, $data_all_all_nilai_indikator[$i]['tanggal'][0]['periode'][$j]);
                    }
                    $num_of_periode = $num_of_periode + 1;
                }
            }
            //end tahun

            //nasional
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, 6, 'Nasional');
            $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2(0, 0, 6, 6))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('DDDDDD');
            $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2(0, 0, 6, 6))->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'D0CECE')
                    )
                )
            );

            $num_of_value = 1;
            for ($i = 0; $i < count($data_all_all_nilai_indikator); $i++) {
                for ($j = 0; $j < count($data_all_all_nilai_indikator[$i]['data'][0]['nilai']); $j++) {
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($num_of_value, 6, $data_all_all_nilai_indikator[$i]['data'][0]['nilai'][$j]);
                    $num_of_value = $num_of_value + 1;
                }
            }
            $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2(1, ($num_of_value - 1), 4, 6))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('DDDDDD');
            $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2(1, ($num_of_value - 1), 6, 6))->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'D0CECE')
                    )
                )
            );
            //end nasional

            $num_of_region = 6;
            //wilayah
            for ($j = 1; $j < count($data_all_all_nilai_indikator[0]['data']); $j++) {
                $duplicate_region = '';
                //indikator
                $num_of_value_region = 1;
                for ($i = 0; $i < count($data_all_all_nilai_indikator); $i++) {
                    //nilai
                    $l = 0;
                    while ($l < count($data_all_all_nilai_indikator[$i]['data'][$j]['wilayah'])) {
                        for ($k = 0; $k < count($data_all_all_nilai_indikator[$i]['data'][0]['wilayah']); $k++) {
                            if ($l != count($data_all_all_nilai_indikator[$i]['data'][$j]['wilayah'])) {
                                if ($duplicate_region != $data_all_all_nilai_indikator[$i]['data'][$j]['wilayah'][$l]) {
                                    $duplicate_region = $data_all_all_nilai_indikator[$i]['data'][$j]['wilayah'][$l];
                                    $num_of_region = $num_of_region + 1;
                                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $num_of_region, $duplicate_region);
                                }
                                if ($data_all_all_nilai_indikator[$i]['tanggal'][0]['periode'][$k] == $data_all_all_nilai_indikator[$i]['tanggal'][$j]['periode'][$l]) {
                                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($num_of_value_region, $num_of_region, $data_all_all_nilai_indikator[$i]['data'][$j]['nilai'][$l]);
                                    $num_of_value_region = $num_of_value_region + 1;
                                    $l++;
                                } else {
                                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($num_of_value_region, $num_of_region, '-');
                                    $num_of_value_region = $num_of_value_region + 1;
                                }
                            } else {
                                $this->excel->getActiveSheet()->setCellValueByColumnAndRow($num_of_value_region, $num_of_region, '-');
                                $num_of_value_region = $num_of_value_region + 1;
                            }
                        }
                    }
                }
            }

            $this->excel->getActiveSheet()->getStyle($this->cellsCol1Col2(1, ($num_of_value_region), 6, 6))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('DDDDDD');

            //font
            $this->excel->getActiveSheet()->getStyle('A1:A2')->getFont()->setName('CMIIW');

            //bolt
            // $this->excel->getActiveSheet()->getStyle('A2:C3')->getFont()->setBold(true);
            // $this->excel->getActiveSheet()->getStyle('A10:L11')->getFont()->setBold(true);

            //size    
            $this->excel->getActiveSheet()->getStyle('A1:IV255')->getFont()->setSize(20);

            //lebar kolom
            $this->excel->getActiveSheet()->getColumnDimension("A")->setWidth("50");
            $this->excel->getActiveSheet()->getColumnDimension("B")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("C")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("D")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("E")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("F")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("G")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("H")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("I")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("J")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("K")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("L")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("M")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("N")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("O")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("P")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("Q")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("R")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("S")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("T")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("U")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("V")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("W")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("X")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("Y")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("Z")->setWidth("20");

            $this->excel->getActiveSheet()->getColumnDimension("AA")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AB")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AC")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AD")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AE")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AF")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AG")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AH")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AI")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AJ")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AK")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AL")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AM")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AN")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AO")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AP")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AQ")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AR")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AS")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AT")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AU")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AV")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AW")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AX")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AY")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("AZ")->setWidth("20");

            $this->excel->getActiveSheet()->getColumnDimension("BA")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BB")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BC")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BD")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BE")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BF")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BG")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BH")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BI")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BJ")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BK")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BL")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BM")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BN")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BO")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BP")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BQ")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BR")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BS")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BT")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BU")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BV")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BW")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BX")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BY")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("BZ")->setWidth("20");

            $this->excel->getActiveSheet()->getColumnDimension("CA")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CB")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CC")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CD")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CE")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CF")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CG")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CH")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CI")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CJ")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CK")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CL")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CM")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CN")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CO")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CP")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CQ")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CR")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CS")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CT")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CU")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CV")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CW")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CX")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CY")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("CZ")->setWidth("20");

            $this->excel->getActiveSheet()->getColumnDimension("DA")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DB")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DC")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DD")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DE")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DF")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DG")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DH")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DI")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DJ")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DK")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DL")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DM")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DN")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DO")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DP")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DQ")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DR")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DS")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DT")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DU")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DV")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DW")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DX")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DY")->setWidth("20");
            $this->excel->getActiveSheet()->getColumnDimension("DZ")->setWidth("20");

            $this->excel->getActiveSheet()->setShowGridlines(true);
            // $this->excel->getActiveSheet()->getStyle('D12:I202')->getAlignment()->setWrapText(true);
            $this->excel->getActiveSheet()->getProtection()->setSheet(true);
            $this->excel->getActiveSheet()->getProtection()->setSort(true);
            $this->excel->getActiveSheet()->getProtection()->setInsertRows(true);
            $this->excel->getActiveSheet()->getProtection()->setFormatCells(true);

            header("Content-Type:application/vnd.ms-excel");
            header("Content-Disposition:attachment;filename = pencapaian_indikator_makro.xls");
            header("Cache-Control:max-age=0");
            $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
            $objWriter->save("php://output");
        } else {
            $data_all_all_nilai_indikator = "something wrong";
        }
    }

    public function demo()
    {
        if ($this->input->is_ajax_request()) {
            try {
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");

                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/demo/pencapaian_indikator/pencapaian_indikator_demo.js?v=" . now("Asia/Jakarta");

                $data_page = array();
                $str = $this->load->view($this->view_dir_demo . "content_demo", $data_page, TRUE);

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

    public function indikator()
    {
        date_default_timezone_set("Asia/Jakarta");

        //$sidebar_view = "admin/template/sidebar/sidebar";

        //$main_content = $this->view_dir_arcgis."/home_page";

        $home_properties = array();
        //MAIN CONTENT
        //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
        //SIDEBAR
        //$sidebar_view = "demo/template/sidebar/sidebar_demo";
        $this->js_path = "assets/demo/home/home_demo.js";
        $this->js_init = "home.init();";
        $data_page = array(
            "tag_title"     =>  APP_TITLE,
            //"main_content"  =>  $main_content,
            //"sidebar"       =>  $sidebar_view,
            "profile"       =>  'Demo',
            "home_properties"       =>  $home_properties,
            "group"         =>  'Demo',
            "notif"         => '0',
            "csrf"          =>  array(
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash()
            ),
            "js_path"       =>  base_url($this->js_path),
            "js_init"       =>  $this->js_init,
        );
        $this->load->view("peppd1/arcgis/ahh/home_page_pro", $data_page);
    }
}
