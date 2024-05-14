<?php defined('BASEPATH') or exit('No direct script access allowed');

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
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");

                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $prde  = array('01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');


                $xname      = "";
                $query      = "";

                $content1 = '<table class="table mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Tahun</th>
                                                                <th>Indonesia</th>
                                                                <th>' . $pro . '</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>';
                $select_title = "SELECT * FROM indikator where id='1'";
                $list_title = $this->db->query($select_title);
                foreach ($list_title->result() as $lst) {
                    $title = $lst->nama_indikator;
                }
                if ($pro == '' & $kab == '' & $kot == '') {
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . " " . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if ($periode == '00') {
                            $thn = $row->tahun; //$thn2=$row->tahun; 
                        } else {
                            $thn =  $prde[$row->periode] . " - " . $row->tahun;
                            //$thn2=  $prde2[$row->periode]." - ".$row->tahun;      
                        }
                        $categories1[]   = $thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]         = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . " " . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if ($periode == '00') {
                            $thn = $row->tahun; //$thn2=$row->tahun; 
                        } else {
                            $thn =  $prde[$row->periode] . " - " . $row->tahun;
                        }
                        $categories1[]   = $thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (
                                            select * 
                                            from nilai_indikator 
                                            where (id_indikator='1' AND wilayah='" . $query . "') 
                                            AND (id_periode, versi) in ( select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='1' 
                                                AND wilayah='" . $query . "' AND periode !='01'
                                                group by id_periode) 
                                group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        //    $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;

                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                        //$judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;                        
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . " " . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if ($periode == '00') {
                            $thn = $row->tahun; //$thn2=$row->tahun; 
                        } else {
                            $thn =  $prde[$row->periode] . " - " . $row->tahun;
                        }
                        $categories1[]   = $thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='" . $query . "'  AND periode !='01' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='1' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='1' AND periode !='01' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='1' AND wilayah='" . $kpquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='1' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        //$id_kab                     = $row_dkab->id;
                        $categories_kab[]             = $row_dkab->tahun;
                        if ($row_dkab->nilai_kab == 0) {
                            $nilai_kab1 = '';
                        } else {
                            $nilai_kab1                = (float)$row_dkab->nilai_kab;
                        }
                        $nilai_kab[]                = $nilai_kab1;
                    }
                    //$datay_ppe3 = array_reverse($nilai_kab);
                    //$tahun_kab         = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = array_reverse($nilai_kab);
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        //    $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;

                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }

                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . " " . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if ($periode == '00') {
                            $thn = $row->tahun; //$thn2=$row->tahun; 
                        } else {
                            $thn =  $prde[$row->periode] . " - " . $row->tahun;
                            //$thn2=  $prde2[$row->periode]." - ".$row->tahun;      
                        }
                        $categories1[]   = $thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='1' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='1' AND periode !='01' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='1' AND wilayah='" . $kpquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='1' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        //$id_kab                     = $row_dkab->id;
                        $categories_kab[]             = $row_dkab->tahun;
                        if ($row_dkab->nilai_kab == 0) {
                            $nilai_kab1 = '';
                        } else {
                            $nilai_kab1                = (float)$row_dkab->nilai_kab;
                        }
                        $nilai_kab[]                = $nilai_kab1;
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = array_reverse($nilai_kab);
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='1' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='1' AND periode !='01' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='1' AND wilayah='" . $ktquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='1' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        //$categories_kab[]             = $row_dkab->tahun;
                        if ($row_dkot->nilai_kab == 0) {
                            $nilai_kot1 = '';
                        } else {
                            $nilai_kot1                = (float)$row_dkot->nilai_kab;
                        }
                        $nilai_kot[]                = $nilai_kot1;
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        //    $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;

                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }

                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='1000') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . " " . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                        $periode                = $row->periode;
                        if ($periode == '00') {
                            $thn = $row->tahun; //$thn2=$row->tahun; 
                        } else {
                            $thn =  $prde[$row->periode] . " - " . $row->tahun;
                            //$thn2=  $prde2[$row->periode]." - ".$row->tahun;      
                        }
                        $categories1[]   = $thn;
                    }
                    $tahun             = $categories1;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='1' AND wilayah='" . $query . "') AND periode !='01' AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='1' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='1' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='1' AND periode !='01' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='1' AND wilayah='" . $ktquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='1' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        //$categories_kab[]             = $row_dkab->tahun;
                        if ($row_dkot->nilai_kab == 0) {
                            $nilai_kot1 = '';
                        } else {
                            $nilai_kot1                = (float)$row_dkot->nilai_kab;
                        }
                        $nilai_kot[]                = $nilai_kot1;
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                }
                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '1' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "<div > Sumber :" . $Lis_thn->sumber . "</div>";
                }
                $title_y    = $title . " (%)";
                $json_data = array(
                    "text"       => $title,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata,
                    "content1"   => $content1
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
        } else
            die;
    }

    function pdrb()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");

                $xname = "";
                $query = "";
                $select_title = "SELECT * FROM indikator where id='2'";
                $list_title = $this->db->query($select_title);
                foreach ($list_title->result() as $lst) {
                    //$title= $lst->nama_indikator;
                    $title = $lst->nama_indikator;
                }
                if ($pro == '' & $kab == '' & $kot == '') {
                    $xname = "Indonesia";
                    $query = "1000";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data = $this->db->query($sql);
                    $nilai    = array();
                    $nilaiData['seriesname'] = 'Indonesia';
                    $i = 1;
                    // $seriesname="";
                    foreach ($list_data->result() as $row) {
                        $id            = $row->id;
                        $categories[]  = $row->tahun;
                        $nilai[]       = (float)$row->nilai;
                        //$sumber        = $row->sumber;

                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai;
                    $catdata[]          = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data = $this->db->query($sql);
                    //                    $nilai    = array();
                    //                    $nilaiData['seriesname'] ='Indonesia';
                    $i = 1;
                    // $seriesname="";
                    foreach ($list_data->result() as $row) {
                        $id            = $row->id;
                        $categories[]  = $row->tahun;
                        $nilai[]       = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);
                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $row_p->tahun;
                        $nilai_p[]       = (float)$row_p->nilai;
                        $sumber_p        = $row_p->sumber;
                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }

                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }

                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id            = $row->id;
                        $categories[]  = $row->tahun;
                        $nilai[]       = (float)$row->nilai;
                        //$sumber        = $row->sumber;
                    }
                    //$tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);

                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $row_p->tahun;
                        $nilai_p[]       = (float)$row_p->nilai;
                        $sumber_p        = $row_p->sumber;
                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
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
                                            where (id_indikator='2' AND wilayah='" . $kpquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='2' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        //$id_kab                     = $row_dkab->id;
                        //$categories_kab[]             = $row_dkab->tahun;
                        if ($row_dkab->nilai_kab == 0) {
                            $nilai_kab1 = '';
                        } else {
                            $nilai_kab1                = (float)$row_dkab->nilai_kab;
                        }
                        $nilai_kab[]                = $nilai_kab1;
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = array_reverse($nilai_kab);
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }

                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id            = $row->id;
                        $categories[]  = $row->tahun;
                        $nilai[]       = (float)$row->nilai;
                        //$sumber        = $row->sumber;
                    }
                    //$tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);

                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $row_p->tahun;
                        $nilai_p[]       = (float)$row_p->nilai;
                        $sumber_p        = $row_p->sumber;
                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);

                    //$sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
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
                                            where (id_indikator='2' AND wilayah='" . $kpquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='2' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        //$id_kab                     = $row_dkab->id;
                        //$categories_kab[]             = $row_dkab->tahun;
                        if ($row_dkab->nilai_kab == 0) {
                            $nilai_kab1 = '';
                        } else {
                            $nilai_kab1                = (float)$row_dkab->nilai_kab;
                        }
                        $nilai_kab[]                = $nilai_kab1;
                    }
                    //$tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = array_reverse($nilai_kab);
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
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
                                            where (id_indikator='2' AND wilayah='" . $ktquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='2' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        if ($row_dkot->nilai_kab == 0) {
                            $nilai_kot1 = '';
                        } else {
                            $nilai_kot1                = (float)$row_dkot->nilai_kab;
                        }
                        $nilai_kot[]                = $nilai_kot1;
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }

                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id            = $row->id;
                        $categories[]  = $row->tahun;
                        $nilai[]       = (float)$row->nilai;
                        //$sumber        = $row->sumber;
                    }
                    //$tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);

                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='2' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='2' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $row_p->tahun;
                        $nilai_p[]       = (float)$row_p->nilai;
                        $sumber_p        = $row_p->sumber;
                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    //$catdata          = $nilaiData;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
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
                                            where (id_indikator='2' AND wilayah='" . $ktquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='2' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        if ($row_dkot->nilai_kab == 0) {
                            $nilai_kot1 = '';
                        } else {
                            $nilai_kot1                = (float)$row_dkot->nilai_kab;
                        }
                        $nilai_kot[]                = $nilai_kot1;
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                }

                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '2' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }
                //$title       = "Perkembangan PDRB Per Kapita ADHK";
                $title_y     = "PDRB Per Kapita ADHB (Rp)";



                $json_data = array(
                    "text"           => $title,
                    "text1"          => $title_y,
                    "categories"     => $tahun,
                    "series"         => $catdata,
                    "sumber"        => $datasumber
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text"           => "",
                    "text1"          => "",
                    "categories"     => "",
                    "series"         => 0,
                    "sumber"        => ""
                );
                exit(json_encode($json_data));
            }
        } else
            die;
    }

    function adhk()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $xname      = "";
                $query      = "";

                $title_y    = "PDRB per Kapita ADHK Tanun Dasar 2010 (Rp)";
                $select_title = "SELECT * FROM indikator where id='3'";
                $list_title = $this->db->query($select_title);
                foreach ($list_title->result() as $lst) {
                    $title = $lst->nama_indikator;
                }
                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai1                 = number_format($row->nilai / 1000000, 2);
                        $nilai[]                = (float)$nilai1;
                        //$nilai[]                = (float)$row->nilai/1000000;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi " . $Lis_pro->nama_provinsi;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        //$nilai[]                = (float)$row->nilai/1000000;
                        $nilai1                 = number_format($row->nilai / 1000000, 2);
                        $nilai[]                = (float)$nilai1;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        //                         $nilai_pro[]                = (float)$row_dpro->nilai;
                        $nilai_pro1                 = number_format($row_dpro->nilai / 1000000, 2);
                        $nilai_pro[]                = (float)$nilai_pro1;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        //$nilai[]                = (float)$row->nilai;
                        $nilai1                 = number_format($row->nilai / 1000000, 2);
                        $nilai[]                = (float)$nilai1;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        //$nilai_pro[]                = (float)$row_dpro->nilai;   
                        $nilai_pro1                 = number_format($row_dpro->nilai / 1000000, 2);
                        $nilai_pro[]                = (float)$nilai_pro1;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
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
                                            where (id_indikator='3' AND wilayah='" . $kpquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='3' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        $categories_kab[]             = $row_dkab->tahun;
                        if ($row_dkab->nilai_kab == 0) {
                            $nilai_kab1 = '';
                        } else {
                            $nilai_kab2                 = number_format($row_dkab->nilai_kab / 1000000, 2);
                            $nilai_kab1                = (float)$nilai_kab2;
                            //$nilai_kab1                = (float)$row_dkab->nilai_kab;   
                        }
                        $nilai_kab[]                = $nilai_kab1;
                    }

                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = array_reverse($nilai_kab);
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        //$nilai[]                = (float)$row->nilai;
                        $nilai1                 = number_format($row->nilai / 1000000, 2);
                        $nilai[]                = (float)$nilai1;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        //$nilai_pro[]                = (float)$row_dpro->nilai;   
                        $nilai_pro1                 = number_format($row_dpro->nilai / 1000000, 2);
                        $nilai_pro[]                = (float)$nilai_pro1;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
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
                                            where (id_indikator='3' AND wilayah='" . $kpquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='3' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        $categories_kab[]             = $row_dkab->tahun;
                        if ($row_dkab->nilai_kab == 0) {
                            $nilai_kab1 = '';
                        } else {
                            $nilai_kab2                 = number_format($row_dkab->nilai_kab / 1000000, 2);
                            $nilai_kab1                = (float)$nilai_kab2;
                            //$nilai_kab1                = (float)$row_dkab->nilai_kab;   
                        }
                        $nilai_kab[]                = $nilai_kab1;
                    }

                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = array_reverse($nilai_kab);
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
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
                                            where (id_indikator='3' AND wilayah='" . $ktquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='3' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        if ($row_dkot->nilai_kab == 0) {
                            $nilai_kot1 = '';
                        } else {
                            $nilai_kot2                 = number_format($row_dkot->nilai_kab / 1000000, 2);
                            $nilai_kot1                = (float)$nilai_kot2;
                        }
                        $nilai_kot[]                = $nilai_kot1;
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        //$nilai[]                = (float)$row->nilai;
                        $nilai1                 = number_format($row->nilai / 1000000, 2);
                        $nilai[]                = (float)$nilai1;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='3' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='3' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        //$nilai_pro[]                = (float)$row_dpro->nilai;   
                        $nilai_pro1                 = number_format($row_dpro->nilai / 1000000, 2);
                        $nilai_pro[]                = (float)$nilai_pro1;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
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
                                            where (id_indikator='3' AND wilayah='" . $ktquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='3' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        if ($row_dkot->nilai_kab == 0) {
                            $nilai_kot1 = '';
                        } else {
                            $nilai_kot2                 = number_format($row_dkot->nilai_kab / 1000000, 2);
                            $nilai_kot1                = (float)$nilai_kot2;
                        }
                        $nilai_kot[]                = $nilai_kot1;
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                }
                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '3' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
    }

    function jumlah_penganggur()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $xname = "";
                $query = "";
                $select_title = "SELECT * FROM indikator where id='4'";
                $list_title = $this->db->query($select_title);
                foreach ($list_title->result() as $lst) {
                    $title = $lst->nama_indikator;
                }
                if ($pro == '' & $kab == '' & $kot == '') {
                    $xname = "Indonesia";
                    $query = "1000";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data = $this->db->query($sql);
                    $nilai    = array();
                    $nilaiData['seriesname'] = 'Indonesia';
                    $i = 1;
                    // $seriesname="";
                    foreach ($list_data->result() as $row) {
                        $id            = $row->id;
                        $categories[] = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]       = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai;
                    $catdata[]          = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $bulan[$row_p->periode] . "-" . $row_p->tahun;
                        $nilai_p[]       = (float)$row_p->nilai;
                        $sumber_p        = $row_p->sumber;
                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }

                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }

                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $bulan[$row_p->periode] . "-" . $row_p->tahun;
                        $nilai_p[]       = (float)$row_p->nilai;
                        //$sumber_p        = $row_p->sumber;
                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    array_push($catdata, $nilaiData);

                    //                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    //                        $list_dkab  = $this->db->query($sql_dkab);
                    //                        foreach ($list_dkab->result() as $row_dkab) {
                    //                         $id_kab                     = $row_dkab->id;
                    //                         $categories_kab[]             = $bulan[$row_dkab->periode]."-".$row_dkab->tahun;
                    //                         $nilai_kab[]                = (float)$row_dkab->nilai;   
                    //                        }
                    //                        $tahun_kab         = $categories_kab;
                    //                        $nilaiData['name'] = $kab;
                    //                        $nilaiData['data'] = $nilai_kab;
                    //                        array_push($catdata, $nilaiData);
                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='4' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='4' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='4' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='4' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        $nilai_kab[]                = (float)$row_dkab->nilai_kab;
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = array_reverse($nilai_kab);
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }

                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $bulan[$row_p->periode] . "-" . $row_p->tahun;
                        $nilai_p[]       = (float)$row_p->nilai;
                        //$sumber_p        = $row_p->sumber;
                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='4' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='4' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='4' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                from nilai_indikator 
                                                WHERE id_indikator='4' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        $nilai_kab[]                = (float)$row_dkab->nilai_kab;
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = array_reverse($nilai_kab);
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='4' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='4' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='4' AND wilayah='" . $ktquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='4' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        if ($row_dkot->nilai_kab == 0) {
                            $nilai_kot1 = '';
                        } else {
                            $nilai_kot1                = (float)$row_dkot->nilai_kab;
                        }
                        $nilai_kot[]                = $nilai_kot1;
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }

                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql_p = "SELECT * FROM (select * from nilai_indikator where (id_indikator='4' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='4' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_p = $this->db->query($sql_p);
                    foreach ($list_p->result() as $row_p) {
                        $id_p            = $row_p->id;
                        $categories_p[]  = $bulan[$row_p->periode] . "-" . $row_p->tahun;
                        $nilai_p[]       = (float)$row_p->nilai;
                        //$sumber_p        = $row_p->sumber;
                    }
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_p;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='4' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='4' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='4' AND wilayah='" . $ktquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='4' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        if ($row_dkot->nilai_kab == 0) {
                            $nilai_kot1 = '';
                        } else {
                            $nilai_kot1                = (float)$row_dkot->nilai_kab;
                        }
                        $nilai_kot[]                = $nilai_kot1;
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                }
                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '4' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn            = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                //$title       = "Perkembangan Jumlah Pengangguran";
                $title_y     = "Jumlah Penganggur (orang)";

                $json_data = array(
                    "text"           => $title,
                    "text1"          => $title_y,
                    "categories"     => $tahun,
                    "series"         => $catdata,
                    "sumber"        => $datasumber
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                $json_data = array(
                    "text"           => "",
                    "text1"          => "",
                    "categories"     => "",
                    "series"         => 0,
                    "sumber"        => ""
                );
                exit(json_encode($json_data));
            }
        } else
            die;
    }

    function tingkat_pengangguran()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);

                $title_y           = "Tingkat Pengangguran Terbuka (%)";
                $select_title = "SELECT * FROM indikator where id='6'";
                $list_title = $this->db->query($select_title);
                foreach ($list_title->result() as $lst) {
                    $title = $lst->nama_indikator;
                }
                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nli                     = number_format($row->nilai, 2);
                        $nilai[]                = (float)$nli;
                        //number_format($angka,2);
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        $judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi " . $Lis_pro->nama_provinsi;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bulan[$row->periode] . "-" . $row->tahun;
                        $nli                     = number_format($row->nilai, 2);
                        $nilai[]                = (float)$nli;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nli_pro                     = number_format($row_dpro->nilai, 2);
                        $nilai_pro[]                = (float)$nli_pro;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }

                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nli                     = number_format($row->nilai, 2);
                        $nilai[]                = (float)$nli;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nli_pro                     = number_format($row_dpro->nilai, 2);
                        $nilai_pro[]                = (float)$nli_pro;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='6' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='6' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='6' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='6' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode 
                                 order by id_periode";

                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $nli_dkab                   = [$m, (float)number_format($row_dkab->nilai_kab, 2)];
                            $nilai_kab[]                = $nli_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData[]       = $categories;
                    //$nilaiData['data'] = array_reverse($nilai_kab);
                    $nilaiData['data'] = $nilai_kab;
                    //print_r($nilaiData);exit();
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nli                     = number_format($row->nilai, 2);
                        $nilai[]                = (float)$nli;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nli_pro                     = number_format($row_dpro->nilai, 2);
                        $nilai_pro[]                = (float)$nli_pro;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='6' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='6' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='6' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='6' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode 
                                 order by id_periode";

                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $nli_dkab                   = [$m, (float)number_format($row_dkab->nilai_kab, 2)];
                            $nilai_kab[]                = $nli_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData[]       = $categories;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);

                    //                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                    //                                FROM(
                    //                                    select DISTINCT id_periode from nilai_indikator 
                    //                                    where (id_indikator='6' AND wilayah='1000') 
                    //                                        AND (id_periode, versi) in (
                    //                                                                    select id_periode, max(versi) as versi 
                    //                                                                    from nilai_indikator 
                    //                                                                    WHERE id_indikator='6' AND wilayah='1000' group by id_periode
                    //                                                                    )
                    //                                    order by id_periode 
                    //                                    Desc limit 6 
                    //                                    ) REF
                    //                                LEFT JOIN(
                    //                                            select id_periode,nilai,sumber,tahun 
                    //                                            from nilai_indikator 
                    //                                            where (id_indikator='6' AND wilayah='".$ktquery."')
                    //                                                AND (id_periode, versi) in (
                    //                                                                select id_periode, max(versi) as versi 
                    //                                                                from nilai_indikator 
                    //                                                                WHERE id_indikator='6' AND wilayah='".$ktquery."' group by id_periode
                    //                                                ) 
                    //                                            group by id_periode 
                    //                                            order by id_periode Desc limit 6
                    //                                ) IND	ON REF.id_periode=IND.id_periode"; 
                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='6' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                                                   WHERE id_indikator='6' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='6' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='6' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)number_format($row_dkot->nilai_kab, 2)];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    //$nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nli                     = number_format($row->nilai, 2);
                        $nilai[]                = (float)$nli;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='6' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='6' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nli_pro                     = number_format($row_dpro->nilai, 2);
                        $nilai_pro[]                = (float)$nli_pro;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='6' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                                                   WHERE id_indikator='6' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='6' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='6' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)number_format($row_dkot->nilai_kab, 2)];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    //$nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                }

                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '6' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
    }

    function pembangunan_manusia()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");

                $xname      = "";
                $query      = "";

                $title_y             = "Indek Pembangunan Manusia";
                $select_title = "SELECT * FROM indikator where id='5'";
                $list_title = $this->db->query($select_title);
                foreach ($list_title->result() as $lst) {
                    $title_x = $lst->nama_indikator;
                }
                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='5' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='5' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='5' AND wilayah='" . $kpquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='5' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        $id_kab                     = $row_dkab->id_periode;
                        $categories_kab[]           = $row_dkab->tahun;
                        //$nilai_kab[]                = (float)$row_dkab->nilai;
                        $nilai_kab1 = $row_dkab->nilai_kab;
                        if ($row_dkab->nilai_kab == 0) {
                            $nilai_kab1    = '';
                        } else {
                            $nilai_kab1    = (float)$row_dkab->nilai_kab;
                        }
                        $nilai_kab[]                = $nilai_kab1;
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = array_reverse($nilai_kab);
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='5' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='5' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='5' AND wilayah='" . $kpquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='5' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        $id_kab                     = $row_dkab->id_periode;
                        $categories_kab[]           = $row_dkab->tahun;
                        $nilai_kab1 = $row_dkab->nilai_kab;
                        if ($row_dkab->nilai_kab == 0) {
                            $nilai_kab1    = '';
                        } else {
                            $nilai_kab1    = (float)$row_dkab->nilai_kab;
                        }
                        $nilai_kab[]                = $nilai_kab1;
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = array_reverse($nilai_kab);
                    array_push($catdata, $nilaiData);

                    //$sql_dkot = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='".$ktquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='".$ktquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='5' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='5' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='5' AND wilayah='" . $ktquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='5' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        $id_kot                     = $row_dkot->id_periode;
                        $categories_kot[]           = $row_dkot->tahun;
                        $nilai_kot1 = $row_dkot->nilai_kab;
                        if ($row_dkot->nilai_kab == 0) {
                            $nilai_kot1    = '';
                        } else {
                            $nilai_kot1    = (float)$row_dkot->nilai_kab;
                        }
                        $nilai_kot[]                = $nilai_kot1;
                    }
                    $tahun_kot             = $categories_kot;
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='5' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='5' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab,IFNULL(IND.sumber,'') sumber_k,IND.tahun,IND.id_periode periodekab 
                                FROM(
                                    select DISTINCT id_periode from nilai_indikator 
                                    where (id_indikator='5' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                    select id_periode, max(versi) as versi 
                                                                    from nilai_indikator 
                                                                    WHERE id_indikator='5' AND wilayah='1000' group by id_periode
                                                                    )
                                    order by id_periode 
                                    Desc limit 6 
                                    ) REF
                                LEFT JOIN(
                                            select id_periode,nilai,sumber,tahun 
                                            from nilai_indikator 
                                            where (id_indikator='5' AND wilayah='" . $ktquery . "')
                                                AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi 
                                                                from nilai_indikator 
                                                                WHERE id_indikator='5' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                            group by id_periode 
                                            order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        $id_kot                     = $row_dkot->id_periode;
                        $categories_kot[]           = $row_dkot->tahun;
                        $nilai_kot1 = $row_dkot->nilai_kab;
                        if ($row_dkot->nilai_kab == 0) {
                            $nilai_kot1    = '';
                        } else {
                            $nilai_kot1    = (float)$row_dkot->nilai_kab;
                        }
                        $nilai_kot[]                = $nilai_kot1;
                    }
                    $tahun_kot             = $categories_kot;
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = array_reverse($nilai_kot);
                    array_push($catdata, $nilaiData);
                }
                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '5' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
    }

    //Gini Rasio
    function gini_rasio()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $bulan      = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $title_x           = "Gini Rasio";
                $title_y           = "Gini Rasio";

                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]           = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id_periode_huruf       = str_split($row->id_periode, 4);
                        $bulann = $id_periode_huruf[1];
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='7' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='7' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        //                        if($row_dkab->nilai!=NULL){
                        //                            $ngr_k=$ngr_k=(float)$row_dkab->nilai;
                        //                        }else{
                        //                            $ngr_k=$row_dkab->nilai;
                        //                        }
                        //$nilai_kab[]                = $ngr_k;
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]                = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    //$nilaiData['data'] = array_reverse($nilai_kab);
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id_periode_huruf       = str_split($row->id_periode, 4);
                        $bulann = $id_periode_huruf[1];
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='7' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='7' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    //print_r($sql_dkab);exit();
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        //                        if($row_dkab->nilai!=NULL){
                        //                            $ngr_k=$ngr_k=(float)$row_dkab->nilai;
                        //                        }else{
                        //                            $ngr_k=$row_dkab->nilai;
                        //                        }
                        //$nilai_kab[]                = $ngr_k;
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]                = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    //$nilaiData['data'] = array_reverse($nilai_kab);
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='7' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='7' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nk = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $k = $nk++;
                        if ($row_dkot->nilai_kab != 0) {
                            $n_dkot                   = [$k, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $n_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id_periode_huruf       = str_split($row->id_periode, 4);
                        $bulann = $id_periode_huruf[1];
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='7' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='7' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='7' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='7' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nk = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $k = $nk++;
                        if ($row_dkot->nilai_kab != 0) {
                            $n_dkot                   = [$k, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $n_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                }
                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '7' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
    }

    //Harapan Hidup
    function harapan_hidup()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $bulan      = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $title_x           = "Angka Harapan Hidup";
                $title_y           = "Angka Harapan Hidup (Tahun)";

                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='8' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='8' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]              = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='8' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='8' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]              = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='8' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                WHERE id_indikator='8' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='8' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='8' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='8' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='8' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='8' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                WHERE id_indikator='8' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='8' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='8' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                }
                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '8' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
    }

    //Rata rata Lama Sekolah
    function rata_lama_sekolah()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $bulan      = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $title_x           = "Rata-rata Lama Sekolah";
                $title_y           = "Rata-rata Lama Sekolah (Tahun)";

                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='9' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='9' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='9' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='9' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]              = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='9' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='9' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='9' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='9' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]              = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);

                    //                    $sql_dkot = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='".$ktquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='".$ktquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    //                    $list_dkot  = $this->db->query($sql_dkot);
                    //                    foreach ($list_dkot->result() as $row_dkot) {
                    //                         $id_kot                     = $row_dkot->id;
                    //                         $categories_kot[]             = $row_dkot->tahun;
                    //                         $nilai_kot[]                = (float)$row_dkot->nilai;   
                    //                    }
                    //                    $tahun_kot             = $categories_kot;
                    //                    $nilaiData['name'] = $kot;
                    //                    $nilaiData['data'] = $nilai_kot;
                    //                    array_push($catdata, $nilaiData);
                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='9' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                                                   WHERE id_indikator='9' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='9' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='9' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='9' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='9' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='9' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                                                   WHERE id_indikator='9' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='9' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='9' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                }
                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '9' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
    }

    //harapan Lama Sekolah
    function harapan_lama_sekolah()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $bulan      = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $title_x           = "Harapan Lama Sekolah";
                $title_y           = "Harapan Lama Sekolah (Tahun)";

                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='10' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='10' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='10' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='10' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]              = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='10' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='10' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='10' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='10' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]              = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='10' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                                                   WHERE id_indikator='10' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='10' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='10' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='10' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='10' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='10' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                                                   WHERE id_indikator='10' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='10' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='10' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                }

                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '10' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
    }

    //pengeluaran_perkapita
    function pengeluaran_perkapita()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $bulan      = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $title_x           = "Pengeluaran per Kapita";
                $title_y           = "Pengeluaran per Kapita (Juta Rp)";

                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)number_format($row->nilai / 1000000, 2);
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)number_format($row->nilai / 1000000, 2);
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)number_format($row_dpro->nilai / 1000000, 2);
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)number_format($row->nilai / 1000000, 2);
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)number_format($row_dpro->nilai / 1000000, 2);
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    //                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    //                    $list_dkab  = $this->db->query($sql_dkab);
                    //                    foreach ($list_dkab->result() as $row_dkab) {
                    //                         $id_kab                     = $row_dkab->id;
                    //                         $categories_kab[]             = $row_dkab->tahun;
                    //                         $nilai_kab[]                = (float)number_format($row_dkab->nilai/1000000,2);
                    //                    }
                    //                    $tahun_kab             = $categories_kab;
                    //                    $nilaiData['name'] = $kab;
                    //                    $nilaiData['data'] = $nilai_kab;
                    //                    array_push($catdata, $nilaiData);
                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='11' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='11' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='11' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='11' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)number_format($row_dkab->nilai_kab / 1000000, 2)];
                            $nilai_kab[]              = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)number_format($row->nilai / 1000000, 2);
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)number_format($row_dpro->nilai / 1000000, 2);
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        select DISTINCT id_periode from nilai_indikator 
                                        where (id_indikator='11' AND wilayah='1000') 
                                        AND (id_periode, versi) in (
                                                                                                   select id_periode, max(versi) as versi 
                                                                                                   from nilai_indikator 
                                                                                                   WHERE id_indikator='11' AND wilayah='1000' group by id_periode
                                                                                           )
                                        order by id_periode 
                                        Desc limit 6 
                                ) REF
                                LEFT JOIN(
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='11' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='11' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)number_format($row_dkab->nilai_kab / 1000000, 2)];
                            $nilai_kab[]                = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='11' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                                                   WHERE id_indikator='11' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='11' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='11' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)number_format($row_dkot->nilai_kab / 1000000, 2)];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $row->tahun;
                        $nilai[]                = (float)number_format($row->nilai / 1000000, 2);
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='11' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='11' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)number_format($row_dpro->nilai / 1000000, 2);
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='11' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                                                   WHERE id_indikator='11' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='11' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='11' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)number_format($row_dkot->nilai_kab / 1000000, 2)];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                }
                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '11' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
    }

    //Tingkat Kemiskinan
    function tinkat_kemiskinan()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $bulan      = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $title_x           = " Tingkat Kemiskinan";
                $title_y           = "Tingkat Kemiskinan (%)";

                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    //$sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='".$kpquery."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='".$kpquery."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC"; 
                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                        select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='36' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='36' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $nk = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $k = $nk++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$k, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]                = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                    //                    foreach ($list_dkab->result() as $row_dkab) {
                    //                         $id_kab                     = $row_dkab->id;
                    //                         $categories_kab[]             = $bulan[$row_dkab->periode]."-".$row_dkab->tahun;
                    //                         $nilai_kab[]                = (float)$row_dkab->nilai;   
                    //                    }
                    //                    $tahun_kab             = $categories_kab;
                    //                    $nilaiData['name'] = $kab;
                    //                    $nilaiData['data'] = $nilai_kab;
                    //                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                        select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='36' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='36' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $nk = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $k = $nk++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$k, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]                = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);


                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                        select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='36' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='36' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nkt = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kt = $nkt++;
                        if ($row_dkot->nilai_kab != 0) {
                            $n_dkot                   = [$kt, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $n_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='36' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='36' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                        select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='36' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='36' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nkt = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kt = $nkt++;
                        if ($row_dkot->nilai_kab != 0) {
                            $n_dkot                   = [$kt, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $n_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                }

                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '36' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
    }

    //Indeks Kedalaman Kemiskinan
    function kedalaman_kemiskinan()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $bulan      = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);

                $title_y           = "Indeks Kedalaman Kemiskinan";
                $select_title = "SELECT * FROM indikator where id='39'";
                $list_title = $this->db->query($select_title);
                foreach ($list_title->result() as $lst) {
                    $title_x = $lst->nama_indikator;
                }
                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='39' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='39' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]              = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                                select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='39' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='39' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $n = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $m = $n++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$m, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]              = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='39' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                WHERE id_indikator='39' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='39' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='39' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='39' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='39' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
                                FROM(
                                        SELECT DISTINCT id_periode FROM nilai_indikator 
                                        WHERE (id_indikator='39' AND wilayah='1000') 
                                        AND (id_periode, versi) IN (
                                                                                                   SELECT id_periode, MAX(versi) AS versi 
                                                                                                   FROM nilai_indikator 
                                                                WHERE id_indikator='39' AND wilayah='1000' GROUP BY id_periode
                                                                                           )
                                        ORDER BY id_periode 
                                        DESC LIMIT 6 
                                ) REF
                                LEFT JOIN(
                                                SELECT id_periode,nilai 
                                        FROM nilai_indikator 
                                        WHERE (id_indikator='39' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) IN (
                                                                SELECT id_periode, MAX(versi) AS versi  
                                                FROM nilai_indikator 
                                                WHERE id_indikator='39' AND wilayah='" . $ktquery . "' GROUP BY id_periode
                                                ) 
                                                GROUP BY id_periode 
                                                ORDER BY id_periode DESC LIMIT 6
                                ) IND	ON REF.id_periode=IND.id_periode ORDER BY id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nnn = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kk = $nnn++;
                        if ($row_dkot->nilai_kab != 0) {
                            $nli_dkot                   = [$kk, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $nli_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                }

                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '39' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
    }

    //Indeks Keparahan Kemiskinan
    function keparahan_kemiskinan()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $bulan      = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);

                $title_y           = "Indeks Keparahan Kemiskinan";
                $select_title = "SELECT * FROM indikator where id='38'";
                $list_title = $this->db->query($select_title);
                foreach ($list_title->result() as $lst) {
                    $title_x = $lst->nama_indikator;
                }
                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);
                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    // $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='" . $kpquery . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='" . $kpquery . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                        select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='38' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='38' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $nk = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $k = $nk++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$k, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]                = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                        select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='38' AND wilayah='" . $kpquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='38' AND wilayah='" . $kpquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkab  = $this->db->query($sql_dkab);
                    $nk = 0;
                    foreach ($list_dkab->result() as $row_dkab) {
                        $k = $nk++;
                        if ($row_dkab->nilai_kab != 0) {
                            $n_dkab                   = [$k, (float)$row_dkab->nilai_kab];
                            $nilai_kab[]                = $n_dkab;
                        }
                    }
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);


                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                        select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='38' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='38' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nkt = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kt = $nkt++;
                        if ($row_dkot->nilai_kab != 0) {
                            $n_dkot                   = [$kt, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $n_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='38' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='38' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT REF.id_periode,IFNULL(IND.nilai,0) nilai_kab
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
                                        select id_periode,nilai 
                                        from nilai_indikator 
                                        where (id_indikator='38' AND wilayah='" . $ktquery . "')
                                        AND (id_periode, versi) in (
                                                                select id_periode, max(versi) as versi  
                                                from nilai_indikator 
                                                WHERE id_indikator='38' AND wilayah='" . $ktquery . "' group by id_periode
                                                ) 
                                                group by id_periode 
                                                order by id_periode Desc limit 6
                                ) IND	ON REF.id_periode=IND.id_periode
                                order by id_periode";
                    $list_dkot  = $this->db->query($sql_dkot);
                    $nkt = 0;
                    foreach ($list_dkot->result() as $row_dkot) {
                        $kt = $nkt++;
                        if ($row_dkot->nilai_kab != 0) {
                            $n_dkot                   = [$kt, (float)$row_dkot->nilai_kab];
                            $nilai_kot[]                = $n_dkot;
                        }
                    }
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                }

                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '38' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
    }

    //Jumlah Penduduk Miskin
    function penduduk_miskin()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('provinsi', 'Provinsi', 'required');
                $this->form_validation->set_rules('kabupaten', 'Kabupaten', 'required');
                $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
                $pro = $this->input->post("provinsi");
                $kab = $this->input->post("kabupaten");
                $kot = $this->input->post("kota");
                $bulan      = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                $title_x           = "Jumlah Penduduk Miskin";
                $title_y           = "Jumlah Penduduk Miskin (Orang)";

                if ($pro == '' & $kab == '' & $kot == '') {
                    $judul = "Dimensi Pemerataan  Dan Kewilayahan";
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    $catdata[]            = $nilaiData;
                } else if ($pro != '' & $kab == '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $bulan[$row_dpro->periode] . "-" . $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun            = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot == '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $kpquery . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $kpquery . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        $id_kab                     = $row_dkab->id;
                        $categories_kab[]             = $row_dkab->tahun;
                        $nilai_kab[]                = (float)$row_dkab->nilai;
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab != '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkab = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $kpquery . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $kpquery . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dkab  = $this->db->query($sql_dkab);
                    foreach ($list_dkab->result() as $row_dkab) {
                        $id_kab                     = $row_dkab->id;
                        $categories_kab[]             = $row_dkab->tahun;
                        $nilai_kab[]                = (float)$row_dkab->nilai;
                    }
                    $tahun_kab             = $categories_kab;
                    $nilaiData['name'] = $kab;
                    $nilaiData['data'] = $nilai_kab;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $ktquery . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $ktquery . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        $id_kot                     = $row_dkot->id;
                        $categories_kot[]             = $row_dkot->tahun;
                        $nilai_kot[]                = (float)$row_dkot->nilai;
                    }
                    $tahun_kot             = $categories_kot;
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                } else if ($pro != '' & $kab == '' & $kot != '') {
                    $catdata = array();
                    $sql_pro = "SELECT P.id, P.nama_provinsi FROM provinsi P WHERE P.`nama_provinsi`='" . $pro . "' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro) {
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                    }
                    $sql_kp = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kab . "' ";
                    $list_kp = $this->db->query($sql_kp);
                    foreach ($list_kp->result() as $Lis_kp) {
                        $kpname = $Lis_kp->nama_kabupaten;
                        $kpquery = $Lis_kp->id;
                    }
                    $sql_kt = "SELECT K.id, K.nama_kabupaten FROM kabupaten K WHERE K.`nama_kabupaten`='" . $kot . "' ";
                    $list_kt = $this->db->query($sql_kt);
                    foreach ($list_kt->result() as $Lis_kt) {
                        $ktname = $Lis_kt->nama_kabupaten;
                        $ktquery = $Lis_kt->id;
                    }
                    $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='1000') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='1000' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_data  = $this->db->query($sql);
                    foreach ($list_data->result() as $row) {
                        $id                     = $row->id;
                        $categories[]             = $bulan[$row->periode] . "-" . $row->tahun;
                        $nilai[]                = (float)$row->nilai;
                    }
                    $tahun             = $categories;
                    $nilaiData['name'] = "Indonesia";
                    $nilaiData['data'] = $nilai;
                    array_push($catdata, $nilaiData);

                    $sql_dpro = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $query . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $query . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dpro  = $this->db->query($sql_dpro);
                    foreach ($list_dpro->result() as $row_dpro) {
                        $id_pro                     = $row_dpro->id;
                        $categories_pro[]             = $row_dpro->tahun;
                        $nilai_pro[]                = (float)$row_dpro->nilai;
                    }
                    $tahun_pro             = $categories_pro;
                    $nilaiData['name'] = $xname;
                    $nilaiData['data'] = $nilai_pro;
                    array_push($catdata, $nilaiData);

                    $sql_dkot = "SELECT * FROM (select * from nilai_indikator where (id_indikator='40' AND wilayah='" . $ktquery . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='40' AND wilayah='" . $ktquery . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                    $list_dkot  = $this->db->query($sql_dkot);
                    foreach ($list_dkot->result() as $row_dkot) {
                        $id_kot                     = $row_dkot->id;
                        $categories_kot[]             = $row_dkot->tahun;
                        $nilai_kot[]                = (float)$row_dkot->nilai;
                    }
                    $tahun_kot             = $categories_kot;
                    $nilaiData['name'] = $kot;
                    $nilaiData['data'] = $nilai_kot;
                    array_push($catdata, $nilaiData);
                }

                $thnmax = "SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '40' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn) {
                    $thn = $Lis_thn->thn;
                    $datasumber     = "Sumber : " . $Lis_thn->sumber;
                }

                $json_data = array(
                    "text"       => $title_x,
                    "text1"      => $title_y,
                    "sumber"     => $datasumber,
                    "categories" => $tahun,
                    "series"     => $catdata
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
        } else
            die;
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
