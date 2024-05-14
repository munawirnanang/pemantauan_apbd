<?php defined('BASEPATH') or exit('No direct script access allowed');

class M_users_pemantauan extends CI_Controller
{
    var $view_dir   = "admin/users/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/management/users.js";
    var $allowed    = array("PPD1");

    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Master", "m_ref");
        $this->two_db        = $this->load->database('database_two', TRUE);
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
                $this->js_path    = "assets/js/admin/management/users_tim_pemantauan.js?v=" . now("Asia/Jakarta");

                //List Group
                $this->m_ref->setTableName("tbl_user_group");
                $select = array("id", "name");
                $cond = array(
                    //  "id" => 'G2'
                );
                $list_group = $this->m_ref->get_by_condition($select, $cond);

                //List kab/kota
                $this->m_ref->setTableName("provinsi");
                $select = array("id", "id_kode", "nama_provinsi", "ppd");
                $cond = array();
                $list_prov = $this->m_ref->get_by_condition($select, $cond);

                $data_page = array(
                    "list_group"    =>  $list_group,
                    "list_prov"    =>  $list_prov,
                );
                $str = $this->load->view($this->view_dir . "index_pemantauan", $data_page, TRUE);


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

    function get_datatable()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $userid = $this->session->userdata(SESSION_LOGIN)->id;
                $idx = 0;
                $columns = array(
                    // datatable column index  => database column name
                    $idx++   => 'A.`userid`',
                    $idx++   => 'A.`name`',
                    $idx++   => 'A.`active_flag`',
                );

                $sql = "SELECT A.*, B.name 'nmuser' 
                            FROM `peppd_registration_user`.`login_session` A
                            LEFT JOIN `peppd_pemantauan`.`tbl_user_group` B ON B.`idname`=A.`group`
                            WHERE A.active_flag!='D' AND A.group='1'";

                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if (!empty($requestData['search']['value'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " AND ( "
                        . " A.`userid` LIKE '%" . $requestData['search']['value'] . "%' "
                        . " OR A.`name` LIKE '%" . $requestData['search']['value'] . "%' "
                        . " OR A.`active_flag` LIKE '%" . $requestData['search']['value'] . "%' "
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
                    $id      = "ppd-" . $row->id;
                    $title   = $row->name;
                    $encrypted_id = base64_encode(openssl_encrypt($id, "AES-128-ECB", ENCRYPT_PASS));
                    $tmp1 = "class='text-info btn btn-sm getDetail' data-id='" . $encrypted_id . "'";
                    $tmp1 .= " data-ustpt='" . $row->userid . "'";
                    $tmp1 .= " data-nmtpt='" . $row->name . "'";
                    $tmp1 .= " data-emtpt='" . $row->nmuser . "'";
                    $tmp2 = "class='text-warning btn btn-sm btnRes ' data-id='" . $encrypted_id . "'";
                    $tmp2 .= " data-title='" . $row->name . "'";
                    $tmp3 = "class='text-danger btn btn-sm btnDel' data-id='" . $encrypted_id . "'";
                    $tmp3 .= " data-title='" . $row->name . "'";


                    $nestedData[] = $row->userid;
                    $nestedData[] = $row->name;
                    // $nestedData[] = $row->email;
                    $nestedData[] = $row->nmuser;
                    $str = "<span class='badge badge-pink'>Tidak Aktif</span>";
                    if ($row->active_flag == 'Y')
                        $str = "<span class='badge badge-success'>Aktif</span>";
                    $nestedData[] = $str;
                    // $nestedData[] = $row->last_access;
                    $nestedData[] = "<a  href='javascript:void(0)' " . $tmp1 . " title='Edit Data'>     <i class='fas fa-pencil-alt'></i>      </a>"
                        . "<a  href='javascript:void(0)' " . $tmp2 . " title='Reset Password'>     <i class='fas mdi mdi-lock-reset'></i>      </a>"
                        . "<a  href='javascript:void(0)' " . $tmp3 . " title='Hapus Data'>     <i class='text fas fa-trash-alt'></i>      </a>";
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

    function add_act()
    {
        if ($this->input->is_ajax_request()) {
            try {
                if (!$this->session->userdata(SESSION_LOGIN)) {
                    throw new Exception("Your session is ended, please relogin", 2);
                }
                $this->form_validation->set_rules('code', 'Code', 'required|xss_clean');
                $this->form_validation->set_rules('name', 'Name', 'required|xss_clean');
                $this->form_validation->set_rules('email', 'Email', 'required|xss_clean');
                //$this->form_validation->set_rules('group','group','required|xss_clean');

                if ($this->form_validation->run() == FALSE) {
                    throw new Exception(validation_errors("", ""), 0);
                }

                $userid = $this->input->post("code");
                $name = $this->input->post("name");
                $group = $this->input->post("group");
                $email = $this->input->post("email");
                //$satker = NULL;

                $this->two_db        = $this->load->database('database_two', TRUE);
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");

                //cek data 
                $this->m_ref->setTableName("tbl_user");
                $select = array();
                $cond = array(
                    "userid"  => $userid,
                );
                $list_data = $this->m_ref->get_by_condition($select, $cond);
                if (!$list_data) {
                    log_message("error", $this->db->error()["message"]);
                    throw new Exception("Invalid SQL");
                }
                if ($list_data->num_rows() > 0) {
                    throw new Exception("Data duplication", 0);
                }
                $tambah = "INSERT INTO login_session (userid,        `name`,        `email`,                                `password`,               `group`,                         `active_flag`,`cr_dt`,             `cr_by`)
                   VALUES                     ('" . $userid . "', '" . $this->input->post("name") . "', '" . $this->input->post("email") . "', '" . md5("pancasila" . "monda") . "',        '1','Y',          '$current_date_time','" . $this->session->userdata(SESSION_LOGIN)->userid . "')";

                $status_save = $this->two_db->query($tambah);
                $tambah1 = "INSERT INTO tbl_user (userid,        `name`,                                      `password`,               `groupid`, `unit_code`,                        `active_flag`,`cr_dt`,             `cr_by`)
                   VALUES                     ('" . $userid . "', '" . $this->input->post("name") . "', '" . md5("pancasila" . "monda") . "',        'PPD1','','Y',          '$current_date_time','" . $this->session->userdata(SESSION_LOGIN)->userid . "')";

                $status_save1 = $this->db->query($tambah1);

                if (!$status_save1) {
                    throw new Exception($this->db->error()["code"] . ":Failed save data", 0);
                }

                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"       =>  "New data has been saved"
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"    =>  $exc->getMessage(),
                );
                exit(json_encode($output));
            }
        } else {
            exit("Access Denied");
        }
    }
    //klik view table
    //klik view table
    function detail_view()
    {
        if ($this->input->is_ajax_request()) {
            try {
                if (!$this->session->userdata(SESSION_LOGIN)) {
                    throw new Exception("Session berakhir, silahkan login ulang", 2);
                }
                $this->form_validation->set_rules('id', 'ID Data', 'required');
                if ($this->form_validation->run() == FALSE) {
                    throw new Exception(validation_errors("", ""), 0);
                }

                $idcomb = decrypt_base64($this->input->post("id"));
                $tmp = explode('-', $idcomb);
                if (count($tmp) != 2)
                    throw new Exception("Invalid ID #1");
                $kate_grp = $tmp[0];
                $id = $tmp[1];


                $sql1 = "SELECT A.id, A.userid,A.`name`,A.`email`,A.`active_flag`,A.`group`
                    FROM login_session A
                    WHERE A.`id`=?  ";
                $bind1 = array($id);
                $list_data = $this->two_db->query($sql1, $bind1);
                if ($list_data->num_rows() == 0) {
                    throw new Exception("Data not found, please reload this page!", 0);
                }

                $groupid = $list_data->row()->group;

                $sql_g = "SELECT TU.id,TU.idname, TU.name FROM tbl_user_group TU WHERE 1=1 ";
                $list_pr = $this->db->query($sql_g);
                $str_pr = "<option value=''> - Choose - </option>";
                foreach ($list_pr->result() as $v) {
                    if ($v->idname == $groupid)
                        $str_pr .= "<option value='" . $v->id . "' selected=''>";
                    else
                        $str_pr .= "<option value='" . $v->id . "'>";
                    $str_pr .= $v->name . "</option>";
                }

                //LIST STATUS
                $_arr_stts = array("Y", "N");
                $_arr_stts_lbl = array("Y" => "Active", "N" => "Tidak Aktif");
                $str_stts = "<option value=''> - Choose - </option>";
                $statt = '';
                foreach ($_arr_stts as $v) {

                    if ($v == $list_data->row()->active_flag)
                        $str_stts .= "<option value='" . $v . "' selected=''>";
                    else
                        $str_stts .= "<option value='" . $v . "'>";
                    $str_stts .= $_arr_stts_lbl[$v] . "</option>";

                    $stat = $list_data->row()->active_flag;
                }


                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"       =>  "success get data",
                    "data"      =>  $list_data->result(),
                    //    "tbl_wilayah"       =>  $content,
                    //   "tbl_kabupaten"     =>  $content_k,
                    "str_pr"      =>  $str_pr,
                    "str_stts"      =>  $str_stts,
                    "str_status"      =>  $statt,
                    "id"      => encrypt_text($id),
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "msg"       =>  $exc->getMessage(),
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            }
        } else {
            exit("Access Denied");
        }
    }

    function kab_datatable()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $requestData = $_REQUEST;
                $this->form_validation->set_rules('id', 'ID', 'required');
                //$user = $this->input->post("id");
                $user = decrypt_text($this->input->post("id"));
                if (!is_numeric($user))
                    throw new Exception("Invalid ID!");
                //$idprov = decrypt_text($prov);
                //cari
                $idx = 0;
                $columns = array(
                    // datatable column index  => database column name
                    $idx++   => "K.id_kab",
                    $idx++   => "K.nama_kabupaten",

                );
                $sql = "SELECT K.`id`,K.id_kab,K.nama_kabupaten FROM `kabupaten` K
                        LEFT JOIN `provinsi` P ON P.id_kode = K.prov_id
                        LEFT JOIN `tbl_user_wilayah` W ON W.idwilayah = P.id WHERE 1=1 ";


                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if (!empty($requestData['search']['value'])) {
                    $sql .= " AND ( "
                        . " K.`id_kab` LIKE '%" . $requestData['search']['value'] . "%' "
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

                    $nestedData[] = $row->id_kab;
                    $nestedData[] = $row->nama_kabupaten;
                    $tmp = " data-data='" . encrypt_text($id) . "' ";
                    $nestedData[] = ""

                        . "<input type='radio' class='checkbox' name='group' $tmp  value='" . $row->nama_kabupaten . "'  /> ";

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

    function add_wil()
    {
        if ($this->input->is_ajax_request()) {
            try {
                if (!$this->session->userdata(SESSION_LOGIN)) {
                    throw new Exception("Your session is ended, please relogin", 2);
                }
                $this->form_validation->set_rules('iduser', 'Code', 'required|xss_clean');
                $this->form_validation->set_rules('prov', 'Name', 'required|xss_clean');

                if ($this->form_validation->run() == FALSE) {
                    throw new Exception(validation_errors("", ""), 0);
                }
                $userid = decrypt_text($this->input->post("iduser"));
                if (!is_numeric($userid))
                    throw new Exception("Invalid ID!");
                print_r($userid);
                exit();
                $wilayah = decrypt_text($this->input->post("prov"));



                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");

                //cek data 
                $this->m_ref->setTableName("tbl_user_wilayah");
                $select = array();
                $cond = array(
                    "iduser"  => $userid,
                    "idwilayah" => $wilayah,
                );
                $list_data = $this->m_ref->get_by_condition($select, $cond);
                if (!$list_data) {
                    log_message("error", $this->db->error()["message"]);
                    throw new Exception("Invalid SQL");
                }
                if ($list_data->num_rows() > 0) {
                    throw new Exception("Data duplication", 0);
                }

                $tambah = "INSERT INTO tbl_user_wilayah (iduser,`idwilayah`,`cr_dt`,`up_dt`,`cr_by`,`up_by`)
                   VALUES ('" . $userid . "', '" . $wilayah . "','" . $current_date_time . "', '', '" . $this->session->userdata(SESSION_LOGIN)->userid . "','')";
                //print_r($tambah);exit();
                $status_save = $this->db->query($tambah);

                if (!$status_save) {
                    throw new Exception($this->db->error()["code"] . ":Failed save data", 0);
                }
                //daerah penilaian
                $select_daerah = "SELECT 	P.`id`, P.`id_kode`, P.`nama_provinsi`, P.`label`,P. `ppd`,W.iduser
                FROM  `provinsi` P 
                LEFT JOIN `tbl_user_wilayah` W ON W.idwilayah = P.id
                WHERE W.iduser='" . $userid . "'";
                $list_wilayah = $this->db->query($select_daerah);
                $content = "";
                $no = 1;
                foreach ($list_wilayah->result() as $r_wil) {
                    $id      = $r_wil->id;
                    $content .= "<tr class='odd gradeX'>";
                    $content .= "<td style='font-size: 11px'><a class='isinilai' data-id='" . encrypt_text($id) . "' >" . $no++ . "</a></td>";
                    $content .= "<td style='font-size: 11px'><a class='isinilai' data-id='" . encrypt_text($id) . "' >" . $r_wil->id_kode . "</a></td>";
                    $content .= "<td style='font-size: 11px'><a class='isinilai' data-id='" . encrypt_text($id) . "' >" . $r_wil->nama_provinsi . "</a></td>";
                    $content .= "<td style='font-size: 11px'><a class='isinilai' data-id='" . encrypt_text($id) . "' >Hapus</a></td>";
                    $content .= "</tr>";
                }
                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"       =>  "New data has been saved"
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "tbl_wilayah"       =>  $content,
                    "msg"    =>  $exc->getMessage(),
                );
                exit(json_encode($output));
            }
        } else {
            exit("Access Denied");
        }
    }

    /*
     * edit data User TPT
     * author : FSM 
     * date : 01 jan 2021
     */
    public function detail_act()
    {
        if ($this->input->is_ajax_request()) {
            try {
                $this->two_db        = $this->load->database('database_two', TRUE);
                if (!$this->session->userdata(SESSION_LOGIN)) {
                    throw new Exception("Session berakhir, silahkan login ulang", 2);
                }
                if (!in_array($this->session->userdata(SESSION_LOGIN)->groupid, $this->allowed)) {
                    throw new Exception("You're not allowed access this page!", 3);
                }

                $this->form_validation->set_rules('iduser', 'id', 'required|xss_clean');
                $this->form_validation->set_rules('userid', 'user id', 'required|xss_clean');
                $this->form_validation->set_rules('nama', 'Name', 'required|xss_clean');
                $this->form_validation->set_rules('email', 'email', 'required|xss_clean');
                $this->form_validation->set_rules('stts', 'Status Active', 'required|xss_clean');

                if ($this->form_validation->run() == FALSE) {
                    throw new Exception(validation_errors("", ""), 0);
                }

                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");

                $idcomb = decrypt_base64($this->input->post("iduser"));
                $tmp = explode('-', $idcomb);
                if (count($tmp) != 2)
                    throw new Exception("Invalid ID");
                $kate_wlyh = $tmp[0];
                $userid = $tmp[1];

                //CHECK DATA
                $sql1 = "SELECT A.id, A.userid,A.`name`,A.`active_flag`,A.`group`,A.password
                        FROM login_session A
                        WHERE A.`id`=?";
                $bind1 = array($userid);
                $list_data_l = $this->two_db->query($sql1, $bind1);
                if (!$list_data_l)
                    throw new Exception("SQL 2 Error!");
                if ($list_data_l->num_rows() == 0)
                    throw new Exception("Data not found");

                $pass = $list_data_l->row()->password;
                $iduser = $list_data_l->row()->userid;

                $this->m_ref->setTableName("tbl_user");
                $select = array();
                $cond = array(
                    "id"  => $userid,
                );
                $list_data = $this->m_ref->get_by_condition($select, $cond);
                if ($list_data->num_rows() == 0) {
                    $tambah = "INSERT INTO tbl_user (userid,        `name`,                                `password`,               `unit_code`,`groupid`,                         `active_flag`,`cr_dt`,             `cr_by`)
                        VALUES                     ('" . $iduser . "', '" . $this->input->post("nama") . "', '" . $pass . "','',        'PPD1','" . $this->input->post("stts") . "',          '$current_date_time','" . $this->session->userdata(SESSION_LOGIN)->userid . "')";

                    $status_save = $this->db->query($tambah);

                    if (!$status_save) {
                        throw new Exception($this->db->error()["code"] . ":Failed save data", 0);
                    }
                } else {
                    //update
                    $this->m_ref->setTableName("tbl_user");
                    $data_uodate = array(
                        "name"      => $this->input->post("nama"),
                        "groupid"      => 'PPD1',
                        "active_flag"      => $this->input->post("stts"),
                        "up_dt"      => $current_date_time,
                        "up_by"      =>  $this->session->userdata(SESSION_LOGIN)->userid
                    );
                    $cond = array(
                        "id"    => $userid,
                    );
                    $status_save = $this->m_ref->update($cond, $data_uodate);
                    if (!$status_save) {
                        throw new Exception($this->db->error("code") . " : Failed Update data", 0);
                    }
                }
                $sql2 = "UPDATE `login_session`
              SET
                `group` = '1',
                `active_flag` = '" . $this->input->post("stts") . "'
              WHERE `id` = '" . $userid . "'";
                $update_data = $this->two_db->query($sql2);
                if (!$update_data)
                    throw new Exception("SQL 2 Error!");

                //sukses
                $output = array(
                    "status"    =>  1,
                    "msg"       =>  "Data Sukses diperbaharui",
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "msg"    =>  $exc->getMessage(),
                );
                exit(json_encode($output));
            }
        } else {
            exit("Access Denied");
        }
    }


    /*
     * reset data User ppd
     * author : FSM 
     * date : 01 jan 2021
     */
    function reset_password()
    {
        if ($this->input->is_ajax_request()) {
            try {
                if (!$this->session->userdata(SESSION_LOGIN)) {
                    throw new Exception("Session berakhir, silahkan login ulang", 2);
                }
                $this->form_validation->set_rules('id', 'Id', 'required');
                if ($this->form_validation->run() == FALSE) {
                    throw new Exception(validation_errors("", ""), 0);
                }
                $idcomb = decrypt_base64($this->input->post("id"));
                $tmp = explode('-', $idcomb);
                if (count($tmp) != 2)
                    throw new Exception("Invalid ID");
                $kate_wlyh = $tmp[0];
                $userid = $tmp[1];

                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");

                $npas     = "pancasila";

                //cek data 
                $this->m_ref->setTableName("tbl_user");
                $select = array();
                $cond = array(
                    "id"  => $userid,
                );
                $list_data = $this->m_ref->get_by_condition($select, $cond);
                if (!$list_data) {
                    log_message("error", $this->db->error()["message"]);
                    throw new Exception("Invalid SQL");
                }
                if ($list_data->num_rows() == 0) {
                    throw new Exception("Data Tidak ada", 0);
                }

                //update

                $this->m_ref->setTableName("tbl_user");
                $data_baru = array(
                    "password"         => md5($npas . "monda"),
                    "up_dt"         => $current_date_time,
                    "up_by" =>  $this->session->userdata(SESSION_LOGIN)->userid
                );
                $cond = array(
                    "id"  => $userid,
                );
                $status_save = $this->m_ref->update($cond, $data_baru);
                if (!$status_save) {
                    throw new Exception($this->db->error("code") . " : Failed Update data", 0);
                }

                $this->db->trans_commit();
                $output = array(
                    "status"    =>  1,
                    "msg"       =>  "Data has been updated",
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "msg"       =>  $exc->getMessage(),
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            }
        } else {
            exit("Access Denied");
        }
    }

    /*
     * hapus data
     */
    function delete()
    {
        if ($this->input->is_ajax_request()) {
            try {
                if (!$this->session->userdata(SESSION_LOGIN)) {
                    throw new Exception("Session berakhir, silahkan login ulang", 2);
                }
                $this->form_validation->set_rules('id', 'ID Data', 'required');
                if ($this->form_validation->run() == FALSE) {
                    throw new Exception(validation_errors("", ""), 0);
                }
                $this->two_db        = $this->load->database('database_two', TRUE);
                $idcomb = decrypt_base64($this->input->post("id"));
                $tmp = explode('-', $idcomb);
                if (count($tmp) != 2)
                    throw new Exception("Invalid ID");
                $kate_wlyh = $tmp[0];
                $userid = $tmp[1];

                $sql1 = "SELECT A.id, A.userid,A.`name`,A.`active_flag`,A.`group`
                        FROM login_session A
                        WHERE A.`id`=?  ";
                $bind1 = array($userid);
                $list_data_l = $this->two_db->query($sql1, $bind1);
                if ($list_data_l->num_rows() == 0) {
                    throw new Exception("Data not found, please reload this page!", 0);
                }
                $iduser = $list_data_l->row()->userid;

                $sql_update = "UPDATE `login_session`SET `active_flag` = 'D' WHERE id='$userid'";
                $list_update = $this->two_db->query($sql_update);
                if (!$list_update)
                    throw new Exception("SQL 2 Error!");

                $this->m_ref->setTableName("tbl_user");
                $select = array();
                $cond = array(
                    "userid"  => $iduser,
                );
                $list_data = $this->m_ref->get_by_condition($select, $cond);
                if ($list_data->num_rows() == 0) {
                    throw new Exception("Data not found, please reload this page!", 0);
                }

                $this->db->trans_begin();
                $status = $this->m_ref->delete($cond);
                if (!$status) {
                    if ($this->db->error()["code"] == 1451)
                        throw new Exception($this->db->error()["code"] . ":Data sedang digunakan", 0);
                    else
                        throw new Exception($this->db->error()["code"] . ":Failed delete data", 0);
                }
                $this->db->trans_commit();
                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"       =>  "Data has been deleted"
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "msg"       =>  $exc->getMessage(),
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            }
        } else {
            exit("Access Denied");
        }
    }





    function change_password()
    {
        if ($this->input->is_ajax_request()) {
            try {
                if (!$this->session->userdata(SESSION_LOGIN)) {
                    throw new Exception("Session berakhir, silahkan login ulang", 2);
                }
                $this->form_validation->set_rules('opass', 'Password Saat Ini', 'required');
                $this->form_validation->set_rules('npass', 'New Password', 'required');
                $this->form_validation->set_rules('cpass', 'Ulangi Password', 'required');
                if ($this->form_validation->run() == FALSE) {
                    throw new Exception(validation_errors("", ""), 0);
                }
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");

                $pass     = $this->input->post("opass");
                $npas     = $this->input->post("npass");
                $userid   = $this->session->userdata(SESSION_LOGIN)->userid;

                $sql = "SELECT A.id, A.userid,A.`name`,A.`active_flag`,A.`groupid`,B.`name` groupname,A.unit_code
                        FROM tbl_user A
                        INNER JOIN `tbl_user_group` B ON A.`groupid`=B.`id`
                        WHERE A.`userid`=? AND A.`password`=?;";
                $bind = array($userid, md5($pass . "monda"));
                $list_data = $this->db->query($sql, $bind);
                if (!$list_data)
                    throw new Exception("SQL Error!");
                if ($list_data->num_rows() == 0)
                    throw new Exception("Wrong Combination!");
                //update
                $this->db->trans_begin();
                $this->m_ref->setTableName("tbl_user");
                $data_baru = array(
                    "password"         => md5($npas . "monda"),
                    "up_dt"         => $current_date_time,
                    "up_by" =>  $this->session->userdata(SESSION_LOGIN)->userid
                );
                $cond = array(
                    "id"  => $list_data->row()->id,
                );
                $status_save = $this->m_ref->update($cond, $data_baru);
                if (!$status_save) {
                    throw new Exception($this->db->error("code") . " : Failed Update data", 0);
                }

                $this->db->trans_commit();
                $output = array(
                    "status"    =>  1,
                    "msg"       =>  "Data has been updated",
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $this->db->trans_rollback();
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "msg"       =>  $exc->getMessage(),
                    "csrf_hash" =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            }
        } else {
            exit("Access Denied");
        }
    }
}
