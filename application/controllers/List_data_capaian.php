<?php defined('BASEPATH') OR exit('No direct script access allowed');

class List_data_capaian extends CI_Controller {
    var $view_dir   = "peppd1/list_data_capaian/";
    var $view_dir_demo   = "demo/list_data/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/list_data/list_data.js";
    
    function __construct() {
        parent::__construct();
        $this->load->model("M_Master","m_ref");
        $this->load->library("Excel");
        //require_once (APPPATH.'/libraries/Excel.php');
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
                $this->js_path    = "assets/js/peppd1/list_data_capaian/G1_list_data_capaian_".$this->session->userdata(SESSION_LOGIN)->groupid.".js?v=".now("Asia/Jakarta");
                
                $data_page = array( );
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
        else{
            exit("access denied!");
        }
    }
    
    function pro_datatable(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $idx = 0;
                $columns = array( 
                // datatable column index  => database column name
                        $idx++   =>"ID", 
                        $idx++   =>"A.nama_wilayah",
//                        $idx++   =>"A.label", 
//                        $idx++   =>"A.`ppd`", 
                );
                $sql = "SELECT A.id 'ID',A.`nama_wilayah` 'nama_wilayah'
                        FROM `wilayah` A
                        WHERE A.id='1000'
                        UNION ALL
                    SELECT A.id 'ID',A.`nama_provinsi` 'nama_wilayah'
                        FROM `provinsi` A
                        WHERE 1=1 ";
                
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {  
                        $sql.=" AND ( "
                                . " 'ID' LIKE '%".$requestData['search']['value']."%' "
                                . " OR A.`nama_wilayah` LIKE '%".$requestData['search']['value']."%' "
                                . ")";    
                }
                $list_data = $this->db->query($sql);
                $totalFiltered = $list_data->num_rows();

                $sql.=" ORDER BY "
                        .$columns[$requestData['order'][0]['column']]."   "
                        .$requestData['order'][0]['dir']."  "
                        . "LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
                $list_data = $this->db->query($sql);
                $data = array();
                $i=1;
                foreach ($list_data->result() as $row) {
                    $nestedData=array(); 
                    $id     = $row->ID;
                    $nestedData[] = $row->ID;
                    $nestedData[] = $row->nama_wilayah;
                    $nestedData[] = "";
                    $tmp = " data-id='".encrypt_text($id)."' ";
                    $wil = " data-wil='".$row->nama_wilayah."' ";
                    $nama = " data-nama='".$row->nama_wilayah."' ";
                    $nestedData[] = ""
//                            . "<a class='btn btn-xs btn-info btnSelect' ".$tmp." title='Pilih Data'><i class='fa fa-hand-o-up'></i> Pilih</a>";
//                            . "<input type='checkbox' class='checkbox' $tmp  value='".$row->nama_provinsi."'  /> ";
                    . "<input type='radio' class='checkbox' name='group' $tmp $wil  value='".$row->nama_wilayah."'  /> ";
                    $data[] = $nestedData;
                }
                $json_data = array(
                    "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval( $totalData ),  // total number of records
                    "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $data   // total data array
                );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
                }
        else
            die;
    }
    
    function kab_datatable(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
               // $this->form_validation->set_rules('id','ID','required');
                //$prov = $this->input->post("id");
                
                //$idprov = decrypt_text($prov);
                //cari
                $idx = 0;
                $columns = array( 
                        $idx++   =>"K.id", 
                        $idx++   =>"K.nama_indikator",

                );
                $sql = "SELECT K.`id`, K.`nama_indikator` FROM `indikator` K WHERE 1=1";
                
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {  
                        $sql.=" AND ( "
                                . " K.`id` LIKE '%".$requestData['search']['value']."%' "
                                . " OR K.`nama_indikator` LIKE '%".$requestData['search']['value']."%' "
                                . ")";    
                }
                $list_data = $this->db->query($sql);
                $totalFiltered = $list_data->num_rows();

                $sql.=" ORDER BY "
                        .$columns[$requestData['order'][0]['column']]."   "
                        .$requestData['order'][0]['dir']."  "
                        . "LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
                $list_data = $this->db->query($sql);
                $data = array();
                $i=1;
                foreach ($list_data->result() as $row) {
                    $nestedData=array(); 
                    $id     = $row->id;
                    
                    $nestedData[] = $row->id;
                    $nestedData[] = $row->nama_indikator ;
                    $tmp = " data-id='".encrypt_text($id)."' ";
                    $tmp_nm = " data-in='".$row->nama_indikator."' ";
                    $nestedData[] = ""
                            . "<input type='radio' class='checkbox' name='group' $tmp $tmp_nm /> ";
                    $data[] = $nestedData;
                }
                $json_data = array(
                    "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval( $totalData ),  // total number of records
                    "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $data   // total data array
                    );
                exit(json_encode($json_data));
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
                }
        else
            die;
    }
    
    
    function list_act(){
        if($this->input->is_ajax_request()){
            try {
//                if(!$this->session->userdata(SESSION_LOGIN)){
//                    throw new Exception("Your session is ended, please relogin",2);
//                }
                $this->form_validation->set_rules('inp_pro','Id Prov','required|xss_clean');
                $this->form_validation->set_rules('inp_proid','Nama Provinsi','required|xss_clean');
                $this->form_validation->set_rules('inp_idind','Id Indikator','required|xss_clean');
                $this->form_validation->set_rules('inp_kab','Nama Indikator','required|xss_clean');

                if($this->form_validation->run() == FALSE){
                    throw new Exception(validation_errors("", ""),0);
                }
                $idpro = decrypt_text($this->input->post("inp_proid"));
                $idind = decrypt_text($this->input->post("inp_idind"));
              
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                if($idind==1){
                $bulan  = array('00' => '', '01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');    
                } else {
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);    
                }
                if($idpro=='1000'){
                    $s_prov="SELECT I.* FROM provinsi I where 1=1";
                    $list_prov = $this->db->query($s_prov);                    
                    foreach ($list_prov->result() as $row_p) {
                        $id_p[]=$row_p->id;
                    }
                    $nilaiData=$id_p;
                    $sql = "
                        SELECT I.nama_indikator 'nama',P.nama_wilayah 'namawil',Y.* 
                    FROM (
                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah = '$idpro' AND tahun >= '2015') 
                    AND (id_periode, versi) IN (
                            SELECT id_periode, MAX(versi) AS versi 
                            FROM nilai_indikator 
                            WHERE id_indikator='".$idind."'  AND tahun >= '2015' AND wilayah = '$idpro' GROUP BY id_periode
                    ) 
                     ORDER BY id_periode ) Y
                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
                     LEFT JOIN `wilayah` P ON Y.wilayah=P.id
                        UNION ALL
                        SELECT I.nama_indikator 'nama',P.nama_provinsi 'namawil',Y.* 
                    FROM (
                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah IN  (" . implode(',', $nilaiData) . ") AND tahun >= '2015') 
                    AND (id_periode, versi) IN (
                            SELECT id_periode, MAX(versi) AS versi 
                            FROM nilai_indikator 
                            WHERE id_indikator='".$idind."'  AND tahun >= '2015' AND wilayah IN  (" . implode(',', $nilaiData) . ") GROUP BY id_periode
                    ) 
                     ORDER BY id_periode ) Y
                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
                     LEFT JOIN `provinsi` P ON Y.wilayah=P.id
                     ORDER BY wilayah, tahun ASC";                    
                    
                }else
                {
                    $s_prov="SELECT I.* FROM kabupaten I where I.prov_id='$idpro' ";
                    $list_prov = $this->db->query($s_prov);                    
                    foreach ($list_prov->result() as $row_p) {
                        $id_k[]=$row_p->id;
                    }
                    $nilaiData=$id_k;
//                    $sql = "SELECT I.nama_indikator 'nama',P.nama_wilayah 'namawil',Y.* 
//                    FROM (
//                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah = '$idpro' AND tahun >= '2014') 
//                    AND (id_periode, versi) IN (
//                            SELECT id_periode, MAX(versi) AS versi 
//                            FROM nilai_indikator 
//                            WHERE id_indikator='".$idind."'  AND tahun >= '2014' GROUP BY id_periode
//                    ) 
//                     ORDER BY id_periode ) Y
//                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
//                     LEFT JOIN `wilayah` P ON Y.wilayah=P.id
//                        UNION ALL
//                        SELECT I.nama_indikator 'nama',P.nama_kabupaten 'namawil',Y.* 
//                            FROM (
//                                   SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah IN  (" . implode(',', $nilaiData) . ") AND tahun >= '2014') 
//                            AND (id_periode, versi) IN (
//                                    SELECT id_periode, MAX(versi) AS versi 
//                                    FROM nilai_indikator 
//                                    WHERE id_indikator='".$idind."'  AND tahun >= '2014' GROUP BY id_periode
//                            ) 
//                             ORDER BY id_periode ) Y
//                             LEFT JOIN `indikator` I ON I.id=Y.id_indikator
//                             LEFT JOIN `kabupaten` P ON Y.wilayah=P.id
//                             ORDER BY wilayah, tahun ASC";
                     $sql = "
                         SELECT I.nama_indikator 'nama',P.nama_wilayah 'namawil',Y.* 
                    FROM (
                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah = '$idpro' AND tahun >= '2015') 
                    AND (id_periode, versi) IN (
                            SELECT id_periode, MAX(versi) AS versi 
                            FROM nilai_indikator 
                            WHERE id_indikator='".$idind."'  AND tahun >= '2015' AND wilayah = '$idpro' GROUP BY id_periode
                    ) 
                     ORDER BY id_periode ) Y
                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
                     LEFT JOIN `wilayah` P ON Y.wilayah=P.id
                        UNION ALL
                        SELECT I.nama_indikator 'nama',P.nama_kabupaten 'namawil',Y.* 
                    FROM (
                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah IN  (" . implode(',', $nilaiData) . ") AND tahun >= '2015') 
                    AND (id_periode, versi) IN (
                            SELECT id_periode, MAX(versi) AS versi 
                            FROM nilai_indikator 
                            WHERE id_indikator='".$idind."'  AND tahun >= '2015' AND wilayah IN  (" . implode(',', $nilaiData) . ") GROUP BY id_periode
                    ) 
                     ORDER BY id_periode ) Y
                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
                     LEFT JOIN `kabupaten` P ON Y.wilayah=P.id
                     ORDER BY wilayah, tahun ASC";   
                }
                
                $list_data  = $this->db->query($sql);
                $content="";
                if($list_data->num_rows()==0)
                    $content = "<tr><td colspan='2'>Data tidak ditemukan</td></tr>";
                
                foreach ($list_data->result() as $row) {
                    
                    $periode                = $row->periode;
                    if($periode == '00'){ $thn=$row->tahun; //$thn2=$row->tahun; 
                    }
                    else{ $thn=  $bulan[$row->periode]." - ".$row->tahun; }
                    $nilai                = $row->nilai;
                    if($nilai > '0'){ $warna= "<td style='font-size: 11px'>".$row->nilai."</td>"; 
                    }
                    else{ $warna= "<td style='font-size: 11px; color:#ef3312;'>".$row->nilai."</td>"; }
                    
                    $content.="<tr class='odd gradeX'>";
                    $content.="<td style='font-size: 11px;'>".$row->namawil."</td>";
                    $content.="<td style='font-size: 11px;'>".$row->nama."</td>";
                    $content.="<td style='font-size: 11px;'>".$row->tahun."</td>";
                    $content.="<td style='font-size: 11px'>".$thn."</td>";
                    $content.=$warna;
                    $content.="<td style='font-size: 11px'>".$row->nasional."</td>";
                    $content.="<td style='font-size: 11px'>".$row->target."</td>";
                    $content.="<td style='font-size: 11px'>".$row->t_m_rpjmn."</td>";
                    $content.="<td style='font-size: 11px'>".$row->t_rkpd."</td>";
                    $content.="<td style='font-size: 11px'>".$row->t_k_rkp."</td>";
                    $content.="<td style='font-size: 11px'>".$row->satuan."</td>";
                    $content.="<td style='font-size: 11px'>".$row->versi."</td>";
                    $content.="</tr>";
                }
                
                //sukses
                $output = array(
                    "status"    =>  1,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                    "content"       =>  $content,
                    "msg"       =>  "Data ditemukan"
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
        }
        else{exit("Access Denied");}
    }
   
  
    function Download_excel1(){
        if(!$this->session->userdata(SESSION_LOGIN)){throw new Exception("Session expired, please login",2);}
        $idpro = decrypt_text($_GET['wl']);
        $nmwil = $_GET['wy'];
        $idind = decrypt_text($_GET['in']);
        
        if($idind==1){
                $bulan  = array('00' => '', '01' => 'TWL I', '02' => 'TWL II', '03' => 'TWL III');    
        } else {
                $bulan = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);    
        }
        $this->load->library("Excel");
        $sharedStyleTitles = new PHPExcel_Style();
                
                //garis
                $sharedStyleTitles->applyFromArray(
                        array('borders' => 
                            array(
                                'bottom'=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'top'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'left'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'right'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                            )
                        ));
        
        //cari nama indikator
        $d_peek="SELECT I.* FROM indikator I where id='$idind'";
        $list_peek = $this->db->query($d_peek);
        foreach ($list_peek->result() as $peek){
                    $nm_indikator   = $peek->nama_indikator;
        }
        
        if($idpro=='1000'){
                    $s_prov="SELECT I.* FROM provinsi I where 1=1";
                    $list_prov = $this->db->query($s_prov);                    
                    foreach ($list_prov->result() as $row_p) {
                        $id_p[]=$row_p->id;
                    }
                    $nilaiData=$id_p;
//                    $sql = "
//                        SELECT I.nama_indikator 'nama',P.nama_wilayah 'namawil',Y.* 
//                    FROM (
//                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah = '$idpro' AND tahun >= '2015') 
//                    AND (id_periode, versi) IN (
//                            SELECT id_periode, MAX(versi) AS versi 
//                            FROM nilai_indikator 
//                            WHERE id_indikator='".$idind."'  AND tahun >= '2015' GROUP BY id_periode
//                    ) 
//                     ORDER BY id_periode ) Y
//                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
//                     LEFT JOIN `wilayah` P ON Y.wilayah=P.id
//                        UNION ALL
//                        SELECT I.nama_indikator 'nama',P.nama_provinsi 'namawil',Y.* 
//                    FROM (
//                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah IN  (" . implode(',', $nilaiData) . ") AND tahun >= '2015') 
//                    AND (id_periode, versi) IN (
//                            SELECT id_periode, MAX(versi) AS versi 
//                            FROM nilai_indikator 
//                            WHERE id_indikator='".$idind."'  AND tahun >= '2015' GROUP BY id_periode
//                    ) 
//                     ORDER BY id_periode ) Y
//                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
//                     LEFT JOIN `provinsi` P ON Y.wilayah=P.id
//                     ORDER BY wilayah, tahun ASC";   
                    $sql = "
                        SELECT I.nama_indikator 'nama',P.nama_wilayah 'namawil',Y.* 
                    FROM (
                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah = '$idpro' AND tahun >= '2015') 
                    AND (id_periode, versi) IN (
                            SELECT id_periode, MAX(versi) AS versi 
                            FROM nilai_indikator 
                            WHERE id_indikator='".$idind."'  AND tahun >= '2015' AND wilayah = '$idpro' GROUP BY id_periode
                    ) 
                     ORDER BY id_periode ) Y
                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
                     LEFT JOIN `wilayah` P ON Y.wilayah=P.id
                        UNION ALL
                        SELECT I.nama_indikator 'nama',P.nama_provinsi 'namawil',Y.* 
                    FROM (
                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah IN  (" . implode(',', $nilaiData) . ") AND tahun >= '2015') 
                    AND (id_periode, versi) IN (
                            SELECT id_periode, MAX(versi) AS versi 
                            FROM nilai_indikator 
                            WHERE id_indikator='".$idind."'  AND tahun >= '2015' AND wilayah IN  (" . implode(',', $nilaiData) . ") GROUP BY id_periode
                    ) 
                     ORDER BY id_periode ) Y
                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
                     LEFT JOIN `provinsi` P ON Y.wilayah=P.id
                     ORDER BY wilayah, tahun ASC";  
                    
                }
        else
                {
                    $s_prov="SELECT I.* FROM kabupaten I where I.prov_id='$idpro' ";
                    $list_prov = $this->db->query($s_prov);                    
                    foreach ($list_prov->result() as $row_p) {
                        $id_k[]=$row_p->id;
                    }
                    $nilaiData=$id_k;
//                    $sql = "SELECT I.nama_indikator 'nama',P.nama_wilayah 'namawil',Y.* 
//                    FROM (
//                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah = '$idpro' AND tahun >= '2015') 
//                    AND (id_periode, versi) IN (
//                            SELECT id_periode, MAX(versi) AS versi 
//                            FROM nilai_indikator 
//                            WHERE id_indikator='".$idind."'  AND tahun >= '2015' GROUP BY id_periode
//                    ) 
//                     ORDER BY id_periode ) Y
//                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
//                     LEFT JOIN `wilayah` P ON Y.wilayah=P.id
//                        UNION ALL
//                        SELECT I.nama_indikator 'nama',P.nama_kabupaten 'namawil',Y.* 
//                            FROM (
//                                   SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah IN  (" . implode(',', $nilaiData) . ") AND tahun >= '2015') 
//                            AND (id_periode, versi) IN (
//                                    SELECT id_periode, MAX(versi) AS versi 
//                                    FROM nilai_indikator 
//                                    WHERE id_indikator='".$idind."'  AND tahun >= '2015' GROUP BY id_periode
//                            ) 
//                             ORDER BY id_periode ) Y
//                             LEFT JOIN `indikator` I ON I.id=Y.id_indikator
//                             LEFT JOIN `kabupaten` P ON Y.wilayah=P.id
//                             ORDER BY wilayah, tahun ASC";
                     $sql = "
                         SELECT I.nama_indikator 'nama',P.nama_wilayah 'namawil',Y.* 
                    FROM (
                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah = '$idpro' AND tahun >= '2015') 
                    AND (id_periode, versi) IN (
                            SELECT id_periode, MAX(versi) AS versi 
                            FROM nilai_indikator 
                            WHERE id_indikator='".$idind."'  AND tahun >= '2015' AND wilayah = '$idpro' GROUP BY id_periode
                    ) 
                     ORDER BY id_periode ) Y
                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
                     LEFT JOIN `wilayah` P ON Y.wilayah=P.id
                        UNION ALL
                        SELECT I.nama_indikator 'nama',P.nama_kabupaten 'namawil',Y.* 
                    FROM (
                           SELECT * FROM nilai_indikator WHERE (id_indikator='".$idind."' AND wilayah IN  (" . implode(',', $nilaiData) . ") AND tahun >= '2015') 
                    AND (id_periode, versi) IN (
                            SELECT id_periode, MAX(versi) AS versi 
                            FROM nilai_indikator 
                            WHERE id_indikator='".$idind."'  AND tahun >= '2015' AND wilayah IN  (" . implode(',', $nilaiData) . ") GROUP BY id_periode
                    ) 
                     ORDER BY id_periode ) Y
                     LEFT JOIN `indikator` I ON I.id=Y.id_indikator
                     LEFT JOIN `kabupaten` P ON Y.wilayah=P.id
                     ORDER BY wilayah, tahun ASC";   
                }        
        $list_data  = $this->db->query($sql);
        $excelColumn = range('A', 'ZZ');
        $index_excelColumn = 1;
        $index_excelColumn1 = 1;
        $row = $rowstart = 4;
        $row1 = $rowstart = 4;
                
        foreach ($list_data->result() as $value) {
                    $periode                = $value->periode;
                    if($periode == '00'){ $thn=$value->tahun; //$thn2=$row->tahun; 
                    }
                    else{ $thn=  $bulan[$value->periode]." - ".$value->tahun; }
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->namawil);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->nama);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->tahun);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $thn);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->nilai);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->nasional);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->target);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->t_m_rpjmn);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->t_rkpd);
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->t_k_rkp);
                    
                    $this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->satuan);
                    //$this->excel->getActiveSheet()->setCellValue($excelColumn[$index_excelColumn++].$row, $value->versi);
                    
                    $index_excelColumn=1;$row++;
                    
                }
       
                $this->excel->getActiveSheet()->setSharedStyle($sharedStyleTitles, 'B3:L3');
                
                //$this->excel->getActiveSheet()->setCellValue('B2', "$nmwil");
                //$this->excel->getActiveSheet()->setCellValue('B3', "Indikator :");
                $this->excel->getActiveSheet()->setCellValue("C3", "$nm_indikator");
                
                $this->excel->getActiveSheet()->setCellValue("B3", "Wilayah ");
                $this->excel->getActiveSheet()->setCellValue("C3", "Indikator ");
                $this->excel->getActiveSheet()->setCellValue("D3", "Tahun ");
                $this->excel->getActiveSheet()->setCellValue("E3", "Periode");
                $this->excel->getActiveSheet()->setCellValue("F3", "Nilai");
                $this->excel->getActiveSheet()->setCellValue("G3", "Nasional");
                $this->excel->getActiveSheet()->setCellValue("H3", "Target");
                $this->excel->getActiveSheet()->setCellValue("I3", "Target Makro RPJMN");
                $this->excel->getActiveSheet()->setCellValue("J3", "Target RKPD");
                $this->excel->getActiveSheet()->setCellValue("K3", "Target Kewilayahan RKP");
                $this->excel->getActiveSheet()->setCellValue("L3", "Satuan");
//                $this->excel->getActiveSheet()->setCellValue("M4", "Versi");
//                $this->excel->getActiveSheet()->mergeCells('B4:C4');
                $this->excel->getActiveSheet()->getStyle('B3')->getFont()->setSize(12);
                
//                $this->excel->getActiveSheet()->mergeCells('B2:D2');
                
                $this->excel->getActiveSheet()->getColumnDimension("B")->setWidth("22");
                $this->excel->getActiveSheet()->getColumnDimension("C")->setWidth("22");
                $this->excel->getActiveSheet()->getColumnDimension("I")->setWidth("18");
                $this->excel->getActiveSheet()->getColumnDimension("J")->setWidth("18");
                $this->excel->getActiveSheet()->getColumnDimension("K")->setWidth("18");
                
                $this->excel->getActiveSheet()->setAutoFilter('B3:L3');
                
                header("Content-Type:application/vnd.ms-excel");
                header("Content-Disposition:attachment;filename = Data_indikator__".$nm_indikator.".xls");
                header("Cache-Control:max-age=0");
                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save("php://output");   
    }
    
    
    
    /*
     * Demo
     */
    public function demo(){
        if($this->input->is_ajax_request()){
            try 
            {                
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/demo/list_data/list_data_demo.js?v=".now("Asia/Jakarta");
                
                $data_page = array( );
                $str = $this->load->view($this->view_dir_demo."index_demo",$data_page,TRUE);

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
        else{
            exit("access denied!");
        }
    }
    
}
