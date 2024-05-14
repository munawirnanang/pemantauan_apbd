<?php defined('BASEPATH') or exit('No direct script access allowed');

class Upload_data_apbd extends CI_Controller
{
    var $view_dir   = "peppd1/upload_data_apbd/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/upload_data_apbd/upload_data_apbd.js";

    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Master", "m_ref");
        $this->load->library("Excel");
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
                $this->js_path    = "assets/js/peppd1/upload_data_apbd/upload_data_apbd_" . $this->session->userdata(SESSION_LOGIN)->groupid . ".js?v=" . now("Asia/Jakarta");

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

    function add_act()
    {
        if ($this->input->is_ajax_request()) {
            try {
                if (!$this->session->userdata(SESSION_LOGIN)) {
                    throw new Exception("Your session is ended, please relogin", 2);
                }
                if ($_FILES['attch']['tmp_name'] == '') {
                    throw new Exception(validation_errors("File Tidak Ada", ""), 0);
                }

                if (file_exists($_FILES['attch']['tmp_name']) && is_uploaded_file($_FILES['attch']['tmp_name'])) {
                    //UPLOAD documents
                    $config['upload_path'] = './attachments_apbd/';
                    $config['allowed_types'] = "csv";
                    $config['max_size']    = '3000'; //3 Mb
                    $config['encrypt_name']    = TRUE;
                    $this->load->library('upload');
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload("attch")) {
                        throw new Exception($this->upload->display_errors("", ""), 0);
                    }
                    //uploaded data
                    $upload_file = $this->upload->data();
                    $inp_urldoc = base_url("attachments_apbd/") . $upload_file['file_name'];
                }

                $sql_n = 'SELECT MAX(id) AS idnomor FROM `nilaianggaran_apbd`';
                $list_no = $this->db->query($sql_n);
                foreach ($list_no->result() as $row_n) {
                    $urut = $row_n->idnomor;
                }
                // Open uploaded CSV file with read-only mode
                $handle = fopen("$inp_urldoc", "r");
                $handle1 = fopen("$inp_urldoc", "r");
                $csvFile = fopen($_FILES['attch']['tmp_name'], 'r');
                //print_r($handle);exit();
                $skip = 1;
                $skip1 = 1;
                $noo = 1;
                while (($data1 = fgetcsv($handle)) !== FALSE) {
                    if ($skip >= 2) {
                        if (strlen($data1[0]) !== 4) {
                            throw new Exception("Penulisan Id Provinsi/Kab/Kota Salah Pada Baris ke " . $skip . "");
                        }
                        if (strlen($data1[1]) !== 5) {
                            throw new Exception("Penulisan Kodepemda Salah Pada Baris ke " . $skip . "");
                        }
                        if (strlen($data1[2]) !== 4) {
                            throw new Exception("Penulisan Tahun Salah Pada Baris ke " . $skip . "");
                        }
                        if (strlen($data1[3]) !== 4) {
                            throw new Exception("Penulisan Standarjenis_APBD_id Periode Salah Pada Baris ke " . $skip . "");
                        }

                        //                       if(strlen($data1[11]) !== 10){
                        //                           throw new Exception("Format Tanggal Salah Pada Baris ke ".$skip."");
                        //                       }
                        if (strlen($data1[6]) == 10) {
                            $pecah = explode("-", $data1[6]);
                            if (strlen($pecah[0]) != 4 || strlen($pecah[1]) != 2 || strlen($pecah[2]) != 2) {
                                throw new Exception("Format Tanggal Versi Salah Pada Baris ke " . $skip . "");
                            }
                        }
                    }
                    $skip++;
                }
                while (($data = fgetcsv($handle1, 10000, ",")) !== FALSE) {
                    if ($skip1 >= 2) {
                        $nm = $urut + $noo++;
                        $string = "SELECT * FROM `nilaianggaran_apbd` "
                            . "WHERE `wilayah`  ='" . $data[0] . "' "
                            . "AND kodepemda   ='" . $data[1] . "' "
                            . "AND tahun   ='" . $data[2] . "' "
                            . "AND standarjenis_APBD_id   ='" . $data[3] . "' "
                            . "AND versi ='" . $data[5] . "' ";

                        $q = $this->db->query($string);
                        if ($q->num_rows() == 0) {
                            $this->m_ref->setTableName("nilaianggaran_apbd");
                            $data_baru = array(
                                // "id"                    => $nm,
                                "wilayah"               => $data[0],
                                "kodepemda"             => $data[1],
                                "tahun"                 => $data[2],
                                "standarjenis_APBD_id"  => $data[3],
                                // "nilai"                 => (empty($data[4]) ? NULL : $data[4]),
                                "nilai"                 => $data[4],
                                "versi"                 => $data[5],
                                "satuan"                => $data[6],
                                "sumber"                => $data[7],
                                "keterangan"            => $data[8],
                            );
                            $status_save = $this->m_ref->save($data_baru);
                            if (!$status_save) {
                                log_message("error", $this->db->error()["message"]);
                                throw new Exception($this->db->error()["code"] . ":Failed save data11", 0);
                            }
                        } else {
                            $string_d = "DELETE FROM `nilaianggaran_apbd` "
                                . "WHERE `wilayah`  ='" . $data[0] . "' "
                                . "AND kodepemda   ='" . $data[1] . "' "
                                . "AND tahun   ='" . $data[2] . "' "
                                . "AND standarjenis_APBD_id   ='" . $data[3] . "' "
                                . "AND versi ='" . $data[5] . "' ";
                            $d = $this->db->query($string_d);
                            $this->m_ref->setTableName("nilaianggaran_apbd");
                            $data_baru = array(
                                // "id"                    => $nm,
                                "wilayah"               => $data[0],
                                "kodepemda"             => $data[1],
                                "tahun"                 => $data[2],
                                "standarjenis_APBD_id"  => $data[3],
                                // "nilai"                 => (empty($data[4]) ? NULL : $data[4]),
                                "nilai"                 => $data[4],
                                "versi"                 => $data[5],
                                "satuan"                => $data[6],
                                "sumber"                => $data[7],
                                "keterangan"            => $data[8],
                            );
                            $status_save = $this->m_ref->save($data_baru);
                            if (!$status_save) {
                                log_message("error", $this->db->error()["message"]);
                                throw new Exception($this->db->error()["code"] . ":Failed save data1", 0);
                            }
                        }
                    }
                    $skip1++;
                }

                //sukses
                $output = array(
                    "status"        =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    // "content"       =>  $content,
                    "msg"           =>  ""
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $output = array(
                    "status"        =>  $exc->getCode(),
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "msg"           =>  $exc->getMessage(),
                );
                exit(json_encode($output));
            }
        } else {
            exit("Access Denied");
        }
    }

    function Download_excel()
    {
        if ($this->input->is_ajax_request()) {
            try {
                if (!$this->session->userdata(SESSION_LOGIN)) {
                    throw new Exception("Your session is ended, please relogin", 2);
                }
                $this->form_validation->set_rules('inp_wl', 'Id Prov', 'required|xss_clean');
                $this->form_validation->set_rules('inp_in', 'Nama Provinsi', 'required|xss_clean');

                if ($this->form_validation->run() == FALSE) {
                    throw new Exception(validation_errors("", ""), 0);
                }
                $idpro = decrypt_text($this->input->post("inp_proid"));
                $idind = decrypt_text($this->input->post("inp_idind"));

                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                if ($idind == 1) {
                    $bulan  = array('00' => '', '01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
                } else {
                    $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
                }



                //cari data 
                //                $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='".$idind."' AND wilayah='".$idpro."') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='".$idind."' AND wilayah='".$idpro."' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
                //                
                //                $list_data  = $this->db->query($sql);
                //                $content="";
                //                foreach ($list_data->result() as $row) {
                //                    $periode                = $row->periode;
                //                    if($periode == '00'){ $thn=$row->tahun; //$thn2=$row->tahun; 
                //                    }
                //                    else{ $thn=  $bulan[$row->periode]." - ".$row->tahun; }
                //                    $content.="<tr class='odd gradeX'>";
                //                    $content.="<td>".$row->tahun."</td>";
                //                    $content.="<td>".$thn."</td>";
                //                    $content.="<td>".$row->nilai."</td>";
                //                    $content.="<td>".$row->nasional."</td>";
                //                    $content.="<td>".$row->target."</td>";
                //                    $content.="<td>".$row->satuan."</td>";
                //                    $content.="<td>".$row->versi."</td>";
                //                    $content.="</tr>";
                //                }

                $this->load->library("Excel");
                $sharedStyleTitles = new PHPExcel_Style();

                //garis
                $sharedStyleTitles->applyFromArray(
                    array(
                        'borders' =>
                        array(
                            'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                            'top'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                            'left'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                            'right'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                        )
                    )
                );

                $this->excel->getActiveSheet()->getRowDimension('3')->setRowHeight(50);

                $this->excel->getActiveSheet()->setSharedStyle($sharedStyleTitles, 'B4:Q35');

                $this->excel->getActiveSheet()->setCellValue('B2', "DATA KEPENDUDUKAN LOKASI DAMPINGAN ");
                $this->excel->getActiveSheet()->setCellValue("B4", "Kode :");
                $this->excel->getActiveSheet()->mergeCells('B4:C4');
                $this->excel->getActiveSheet()->getStyle('B4')->getFont()->setSize(12);

                $this->excel->getActiveSheet()->setCellValue("D4", "JJJ ");
                $this->excel->getActiveSheet()->mergeCells('D4:Q4');
                $this->excel->getActiveSheet()->setCellValue("B5", "Mitra :");
                $this->excel->getActiveSheet()->mergeCells('B5:C5');
                $this->excel->getActiveSheet()->setCellValue("D5", "");
                $this->excel->getActiveSheet()->mergeCells('D5:Q5');
                $this->excel->getActiveSheet()->setCellValue("B6", "Provinsi :");
                $this->excel->getActiveSheet()->mergeCells('B6:C6');
                $this->excel->getActiveSheet()->setCellValue("D6", "");
                $this->excel->getActiveSheet()->mergeCells('D6:Q6');
                $this->excel->getActiveSheet()->setCellValue("B7", "Kabupaten/Kota :");
                $this->excel->getActiveSheet()->mergeCells('B7:C7');
                $this->excel->getActiveSheet()->setCellValue("D7", "");
                $this->excel->getActiveSheet()->mergeCells('D7:Q7');
                $this->excel->getActiveSheet()->setCellValue("B8", "Kecamatan :");
                $this->excel->getActiveSheet()->mergeCells('B8:C8');
                $this->excel->getActiveSheet()->setCellValue("D8", "");
                $this->excel->getActiveSheet()->mergeCells('D8:Q8');

                header("Content-Type:application/vnd.ms-excel");
                header("Content-Disposition:attachment;filename = LokasiDampingan_.xls");
                header("Cache-Control:max-age=0");
                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save("php://output");

                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    // "content"       =>  $content,
                    "msg"       =>  ""
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

    function Download_excel1()
    {
        if (!$this->session->userdata(SESSION_LOGIN)) {
            throw new Exception("Session expired, please login", 2);
        }
        $id = $_GET['wl'];
        $idpro = decrypt_text($_GET['wl']);
        $idind = decrypt_text($_GET['in']);


        if ($idind == 1) {
            $bulan  = array('00' => '', '01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');
        } else {
            $bulan = array('00' => '', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',);
        }
        //cari nama indikator
        $d_peek = "SELECT I.* FROM indikator I where id='$idind'";
        $list_peek = $this->db->query($d_peek);
        foreach ($list_peek->result() as $peek) {
            $nm_indikator   = $peek->nama_indikator;
        }
        //cari Provinsi
        $d_pro = "SELECT I.* FROM `wilayah` I WHERE id='$idpro'";
        $list_pro = $this->db->query($d_pro);
        foreach ($list_pro->result() as $pro) {
            $nm_wilayah   = $pro->nama_wilayah;
        }

        //cari data 
        $sql = "SELECT * FROM (select * from nilai_indikator where (id_indikator='" . $idind . "' AND wilayah='" . $idpro . "') AND (id_periode, versi) in (select id_periode, max(versi) as versi from nilai_indikator WHERE id_indikator='" . $idind . "' AND wilayah='" . $idpro . "' group by id_periode) group by id_periode order by id_periode desc limit 6) y order by id_periode ASC";
        $list_data  = $this->db->query($sql);
        //$content="";
        $excelColumn = range('A', 'ZZ');
        $index_excelColumn = 1;
        $row = $rowstart = 5;

        foreach ($list_data->result() as $value) {
            $periode                = $value->periode;
            if ($periode == '00') {
                $thn = $value->tahun; //$thn2=$row->tahun; 
            } else {
                $thn =  $bulan[$value->periode] . " - " . $value->tahun;
            }
            $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++] . $row, $value->tahun);
            $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++] . $row, $thn);
            $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++] . $row, $value->nilai);
            $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++] . $row, $value->nasional);
            $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++] . $row, $value->target);
            $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++] . $row, $value->satuan);
            $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++] . $row, $value->versi);

            $index_excelColumn = 1;
            $row++;
        }


        $this->load->library("Excel");
        $sharedStyleTitles = new PHPExcel_Style();

        //garis
        $sharedStyleTitles->applyFromArray(
            array(
                'borders' =>
                array(
                    'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                    'top'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                    'left'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                    'right'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                )
            )
        );

        //                        $this->excel->getActiveSheet()->getRowDimension('3')->setRowHeight(50);

        $this->excel->getActiveSheet()->setSharedStyle($sharedStyleTitles, 'B4:H10');

        $this->excel->getActiveSheet()->setCellValue('B2', "$nm_wilayah");
        $this->excel->getActiveSheet()->setCellValue('B3', "Indikator :");
        $this->excel->getActiveSheet()->setCellValue("C3", "$nm_indikator");
        //                $this->excel->getActiveSheet()->setCellValue('C3', '$nm_indikator');

        $this->excel->getActiveSheet()->setCellValue("B4", "Tahun:");
        $this->excel->getActiveSheet()->setCellValue("C4", "Periode");
        $this->excel->getActiveSheet()->setCellValue("D4", "Nilai");
        $this->excel->getActiveSheet()->setCellValue("E4", "Nasional");
        $this->excel->getActiveSheet()->setCellValue("F4", "Target");
        $this->excel->getActiveSheet()->setCellValue("G4", "Satuan");
        $this->excel->getActiveSheet()->setCellValue("H4", "Versi");
        //                $this->excel->getActiveSheet()->mergeCells('B4:C4');
        $this->excel->getActiveSheet()->getStyle('B4')->getFont()->setSize(12);


        //$this->excel->getActiveSheet()->mergeCells('D4:Q4');

        header("Content-Type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename = LokasiDampingan_.xls");
        header("Cache-Control:max-age=0");
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $objWriter->save("php://output");
    }
}
