<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pdrb_sektoral extends CI_Controller {
    var $view_dir   = "peppd1/evaluasi/pdrb_sektoral/";
    var $view_dir_demo   = "demo/evaluasi/pdrb_sektoral/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/pdrb_sektoral/pdrb_sektoral.js";
    
    function __construct() {
        parent::__construct();
        $this->load->model("M_Master","m_ref");       
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
                $this->js_path    = "assets/js/peppd1/evaluasi/pdrb_sektoral/pdrb_sektoral_".$this->session->userdata(SESSION_LOGIN)->groupid.".js?v=".now("Asia/Jakarta");
                
                $data_page = array( );
                $str = $this->load->view($this->view_dir."content",$data_page,TRUE);

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
                        $idx++   =>"A.id", 
                        $idx++   =>"A.nama_provinsi",
                        $idx++   =>"A.label", 
                        $idx++   =>"A.`ppd`", 
                );
                $sql = "SELECT A.id,A.`nama_provinsi`,A.`label`,A.`ppd`
                        FROM provinsi A
                        WHERE 1=1 ";
                
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {  
                        $sql.=" AND ( "
                                . " A.`id` LIKE '%".$requestData['search']['value']."%' "
                                . " OR A.`nama_provinsi` LIKE '%".$requestData['search']['value']."%' "
                                . " OR A.`label` LIKE '%".$requestData['search']['value']."%' "
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
                    $nestedData[] = $row->nama_provinsi;
                    $nestedData[] = $row->label;
                    $tmp = " data-id='".encrypt_text($id)."' ";
                    $nama = " data-prov='".$row->nama_provinsi."' ";
                    $nestedData[] = ""
                    . "<input type='radio' class='checkbox' name='group' $tmp  value='".$row->nama_provinsi."'  /> ";
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
                $this->form_validation->set_rules('id','ID','required');
                $prov = $this->input->post("id");
                
                $idx = 0;
                $columns = array( 
                // datatable column index  => database column name
                        $idx++   =>"K.id", 
                        $idx++   =>"K.nama_kabupaten",

                );
                $sql = "SELECT K.`id` 'idkab', K.`nama_kabupaten`, K.`prov_id`, K.`ppd`, K.`urutan`, P.id, P.nama_provinsi 
                    FROM `kabupaten` K
                    LEFT JOIN provinsi P ON P.id = K.`prov_id`
                    WHERE K.ppd = '1'";
                
                $totalData = $this->db->query($sql)->num_rows();
                $totalFiltered = $totalData;

                if( !empty($requestData['search']['value']) ) {  
                        $sql.=" AND ( "
                                . " K.`id` LIKE '%".$requestData['search']['value']."%' "
                                . " OR K.`nama_kabupaten` LIKE '%".$requestData['search']['value']."%' "
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
                    
                    $nestedData[] = $row->idkab;
                    $nestedData[] = $row->nama_kabupaten ;
                    $tmp = " data-id='".encrypt_text($id)."' ";
                    $nestedData[] = ""
                            . "<input type='radio' class='radio' name='group' value='".$row->nama_kabupaten."' data-id='$row->nama_kabupaten' data-pv='$row->nama_provinsi' /> ";
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

    //sruktur
    function struktur_pdrb(){
        if($this->input->is_ajax_request()){
            try {
                $requestData= $_REQUEST;
                $this->form_validation->set_rules('provinsi','Provinsi','required');
                $pro = $this->input->post("provinsi");
               
                $bulan      = array( '00' => '','01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Ags','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',);
                $prde  = array('01' => 'Tw I', '02' => 'Tw II', '03' => 'Tw III' , '04' => 'Tw IV');
                $d_peek="SELECT I.`deskripsi` FROM indikator I where id='36'";
                $list_peek = $this->db->query($d_peek);
                foreach ($list_peek->result() as $peek){$peek_tk   = $peek->deskripsi;}
                $peek_pdrb  ="";
                $thn_k2     ='';
                $periode_c  ='';
                $periode_c2 ='';
                $catdata_2 = array();
                if($pro == ''){
                    $judul="";
                    //Struktur PDRB
                    $select_pdrb="SELECT R.id,R.nama_indikator,IFNULL(IND.nilai,0) nilai_struktur,IFNULL(IND.target,0) nilai_pertumbuhan, IND.id_periode, IND.periode,IND.tahun
                             FROM (SELECT IK.id,IK.nama_indikator 
                                    FROM `indikator` IK 
                                    WHERE IK.group_id='6' )R 
                            LEFT JOIN ( select id_indikator, id_periode,nilai,target,periode,tahun
                                        from nilai_indikator 
                                        where (wilayah='1000') 
                                             AND (id_indikator, id_periode, versi) IN( 
                                                    select id_indikator,id_periode, max(versi) as versi 
                                                    from nilai_indikator 
                                                    WHERE wilayah='1000' group by id_indikator ) 
                                       )IND ON R.id=IND.id_indikator ";
                    $list_pdrb  = $this->db->query($select_pdrb);
                    foreach ($list_pdrb->result() as $row_pdrb) {
                        $categories_p[]           = $row_pdrb->nama_indikator;
                        $nilai_s[]                = (float)$row_pdrb->nilai_struktur;
                        $nilai_p[]                = (float)$row_pdrb->nilai_pertumbuhan;
                        $nilai_pc[]               = (float)$row_pdrb->nilai_pertumbuhan;
                        $periode                  = $row_pdrb->periode;
                        $tahun1                   = $row_pdrb->tahun;
                        if($periode == '00'){ $thn=$row_pdrb->tahun; }
                        else{ $thn=  $prde[$row_pdrb->periode]."-".$row_pdrb->tahun; }
                        $periode1[]   = $thn;
                        $periode_c   = $thn;
                    }
                    $tahun2=$tahun1-1;
                    $tahun_p=$tahun2.$periode;

                    $tahun             = $categories_p;
                    $nilaiData['name'] = $periode_c;
                    $nilaiData['data'] = $nilai_s;
                    $catdata[]         = $nilaiData;
                    
                    $nilaiData2['name'] = $periode_c;
                    $nilaiData2['data'] = $nilai_p;
                    $catdata2[]         = $nilaiData2;
                    
                    $nilaiData_3['name'] = "TW 1 2020";
                    $nilaiData_3['data'] = $nilai_s;
                    $catdata_3[]         = $nilaiData_3;
                    

                    $select_pdrb_2 ="SELECT R.id,R.nama_indikator,IFNULL(IND.nilai,0) nilai_struktur,IFNULL(IND.target,0) nilai_pertumbuhan, IND.id_periode, IND.periode,IND.tahun
                                FROM (SELECT IK.id,IK.nama_indikator 
                                        FROM `indikator` IK 
                                        WHERE IK.group_id='6' )R 
                                LEFT JOIN ( select id_indikator, id_periode,nilai,target,periode,tahun
                                            from nilai_indikator 
                                            where (id_periode='".$tahun_p."' AND wilayah='1000') 
                                                AND (id_indikator, id_periode, versi) IN( 
                                                        select id_indikator,id_periode, max(versi) as versi 
                                                        from nilai_indikator 
                                                        WHERE wilayah='1000' group by id_indikator ) 
                                        )IND ON R.id=IND.id_indikator ";
                    $list_pdrb_2 = $this->db->query($select_pdrb_2);
                    foreach ($list_pdrb_2->result() as $row_pdrb_2) {
                        $categories_p_2[]             = $row_pdrb_2->nama_indikator;
                        $nilai_s_2[]                = (float)$row_pdrb_2->nilai_struktur;
                        $nilai_p_2[]                = (float)$row_pdrb_2->nilai_pertumbuhan;
                        $periode_2                  = $row_pdrb_2->periode;
                        $tahun1_2                   = $row_pdrb_2->tahun;
                        if($periode_2 == ''){ $thn_2 = ''; }
                        elseif($periode_2 == '00'){ $thn_2 = $row_pdrb_2->tahun; }
                        else{ $thn_2=  $prde[$row_pdrb_2->periode]."-".$row_pdrb_2->tahun; }
                        $periode1_2[]   = $thn_2;
                        $thn_k2   = $thn_2;
                        
                        $label_sektor_2[$row_pdrb_2->nama_indikator]=(float)$row_pdrb_2->nilai_struktur;
                        $label_pertumbuhan_2[$row_pdrb->nama_indikator]=(float)$row_pdrb_2->nilai_pertumbuhan;
                            
                    }
                    if($thn_k2==''){ $periode_c2=  $prde[$periode]."-".$tahun2; } 
                    else { $periode_c2=   $thn_k2; }

                    $nilaiData_2['name'] = $periode_c;
                    $nilaiData_2['data'] = $nilai_pc;
                    array_push($catdata_2, $nilaiData_2);
                    
                    $nilaiData_2['name'] = $periode_c2; //ke-2
                    $nilaiData_2['data'] = $nilai_p_2;
                    array_push($catdata_2, $nilaiData_2);
                }
                
                else if($pro != ''){
                    $sql_pro = "SELECT P.* FROM provinsi P WHERE P.`nama_provinsi`='".$pro."' ";
                    $list_data = $this->db->query($sql_pro);
                    foreach ($list_data->result() as $Lis_pro){
                        $xname = $Lis_pro->nama_provinsi;
                        $query = $Lis_pro->id;
                        //$judul = "Dimensi Pemerataan  Dan Kewilayahan  Provinsi ".$Lis_pro->nama_provinsi;
                        $label_pe = $Lis_pro->label;
                    }
                    $select_pdrb="SELECT R.id,R.nama_indikator,IFNULL(IND.nilai,0) nilai_struktur,IFNULL(IND.target,0) nilai_pertumbuhan, IND.id_periode, IND.periode,IND.tahun
                             FROM (SELECT IK.id,IK.nama_indikator 
                                    FROM `indikator` IK 
                                    WHERE IK.group_id='6' )R 
                            LEFT JOIN ( select id_indikator, id_periode,nilai,target,periode,tahun
                                        from nilai_indikator 
                                        where (wilayah='".$query."') 
                                             AND (id_indikator, id_periode, versi) IN( 
                                                    select id_indikator,id_periode, max(versi) as versi 
                                                    from nilai_indikator 
                                                    WHERE wilayah='".$query."' group by id_indikator ) 
                                       )IND ON R.id=IND.id_indikator ";
                    $list_pdrb  = $this->db->query($select_pdrb);
                    foreach ($list_pdrb->result() as $row_pdrb) {
                        $categories_p[] = $row_pdrb->nama_indikator;
                        $nilai_s[]          = (float)$row_pdrb->nilai_struktur;
                        $nilai_p[]                = (float)$row_pdrb->nilai_pertumbuhan;
                        $nilai_pc[]                = (float)$row_pdrb->nilai_pertumbuhan;
                        $periode                  = $row_pdrb->periode;
                        $tahun1                   = $row_pdrb->tahun;
                        if($periode == '00'){ $thn=$row_pdrb->tahun; }
                        else{ $thn=  $prde[$row_pdrb->periode]."-".$row_pdrb->tahun; }
                        $periode1[]   = $thn;
                        $periode_c   = $thn;

                        $label_sektor[$row_pdrb->nama_indikator]=(float)$row_pdrb->nilai_struktur;
                        $label_pertumbuhan[$row_pdrb->nama_indikator]=(float)$row_pdrb->nilai_pertumbuhan;
                    }
                    $tahun2=$tahun1-1;
                    $tahun_p=$tahun2.$periode;

                    if($thn_k2==''){
                        $periode_c2=  $prde[$periode]." - ".$tahun2;
                    } else {
                    $periode_c2=   $thn_k2;
                    }
                    
                    $tahun             = $categories_p;
                    $nilaiData['name'] = $periode_c;
                    $nilaiData['data'] = $nilai_s;
                    $catdata[]            = $nilaiData;
                    
                    $nilaiData2['name'] = $periode_c;
                    $nilaiData2['data'] = $nilai_p;
                    $catdata2[]            = $nilaiData2;
                    
                    arsort($nilai_s);
                    $nrk=1;
                    foreach($nilai_s as $xk=>$xk_value){
                        $nok=$nrk++;
                        if($nok=='1'){$rengking_satu=$xk_value;}
                        if($nok=='2'){$rengking_dua =$xk_value;}
                        if($nok=='3'){$rengking_tiga=$xk_value;}
                    }
                    $nama_s1=$label_sektor;
                    arsort($nama_s1);
                    $nama_s2=array_keys($nama_s1);
                    foreach($nilai_s as $xk=>$xk_value){
                        $nok=$nrk++;
                        if($nok=='1'){$rengking_satu=$xk_value;}
                        if($nok=='2'){$rengking_dua =$xk_value;}
                        if($nok=='3'){$rengking_tiga=$xk_value;}
                    }
                    arsort($nilai_p);
                    $nrp=1;
                    foreach($nilai_p as $xkp=>$xkp_value){
                        $nop=$nrp++;
                        if($nop=='1'){$rengking_satu_p=$xkp_value;}
                        if($nop=='2'){$rengking_dua_p =$xkp_value;}
                        if($nop=='3'){$rengking_tiga_p=$xkp_value;}
                     }
                     $nama_p1=$label_pertumbuhan;
                    arsort($nama_p1);
                    $nama_p2=array_keys($nama_p1);
                    $nama_p3=$label_pertumbuhan;
                    asort($nama_p3);
                    $nrps=1;
                    foreach($nama_p3 as $xkps=>$xkps_value){
                        $nops=$nrps++;
                        if($nops=='1'){$rengking_satu_ts=$xkps_value;}
                        if($nops=='2'){$rengking_dua_ts =$xkps_value;}
                        if($nops=='3'){$rengking_tiga_ts=$xkps_value;}
                    }
                     $nama_p4=array_keys($nama_p3);
                    $peek_pdrb="Tiga sektor yang paling dominan dalam struktur perekonomian ". $xname ." adalah sektor "
                            . "".$nama_s2[0]." dengan kontribusi sebesar ".$rengking_satu." persen, diikuti oleh sektor ".$nama_s2[1]." dengan kontribusi sebesar ".$rengking_dua." persen, kemudian sektor ".$nama_s2[2]." dengan kontribusi sebesar ".$rengking_tiga." persen. "
                            . "Sedangkan tiga sektor yang memiliki pertumbuhan paling tinggi adalah sektor ".$nama_p2[0]." "
                            . "dengan pertumbuhan sebesar ".$rengking_satu_p." persen, "
                            . "diikuti oleh sektor ".$nama_p2[1]." dengan pertumbuhan sebesar ".$rengking_dua_p." persen, "
                            . "serta sektor ".$nama_p2[2]." dengan pertumbuhan sebesar ".$rengking_tiga_p." persen. "
                            . "Di sisi lain tiga sektor yang mengalami pertumbuhan terendah adalah "
                            . "sektor ".$nama_p4[0]." dengan pertumbuhan sebesar ".$rengking_satu_ts." persen, "
                            . "diikuti oleh sektor ".$nama_p4[1]." dengan pertumbuhan sebesar ".$rengking_dua_ts." persen, "
                            . "serta sektor ".$nama_p4[2]." dengan pertumbuhan sebesar ".$rengking_tiga_ts." persen.	";
                    
                    $select_pdrb_2 ="SELECT R.id,R.nama_indikator,IFNULL(IND.nilai,0) nilai_struktur,IFNULL(IND.target,0) nilai_pertumbuhan, IND.id_periode, IND.periode,IND.tahun
                                FROM (SELECT IK.id,IK.nama_indikator 
                                        FROM `indikator` IK 
                                        WHERE IK.group_id='6' )R 
                                LEFT JOIN ( select id_indikator, id_periode,nilai,target,periode,tahun
                                            from nilai_indikator 
                                            where (id_periode='".$tahun_p."' AND wilayah='".$query."') 
                                                AND (id_indikator, id_periode, versi) IN( 
                                                        select id_indikator,id_periode, max(versi) as versi 
                                                        from nilai_indikator 
                                                        WHERE wilayah='".$query."' group by id_indikator ) 
                                        )IND ON R.id=IND.id_indikator ";
                    $list_pdrb_2 = $this->db->query($select_pdrb_2);
                    foreach ($list_pdrb_2->result() as $row_pdrb_2) {
                        $categories_p_2[]             = $row_pdrb_2->nama_indikator;
                        $nilai_s_2[]                = (float)$row_pdrb_2->nilai_struktur;
                        $nilai_p_2[]                = (float)$row_pdrb_2->nilai_pertumbuhan;
                        $periode_2                  = $row_pdrb_2->periode;
                        $tahun1_2                   = $row_pdrb_2->tahun;
                        if($periode_2 == ''){ $thn_2 = ''; }
                        elseif($periode_2 == '00'){ $thn_2 = $row_pdrb_2->tahun; }
                        else{ $thn_2=  $prde[$row_pdrb_2->periode]."-".$row_pdrb_2->tahun; }
                        $periode1_2[]   = $thn_2;
                        $thn_k2   = $thn_2;
                        
                        $label_sektor_2[$row_pdrb_2->nama_indikator]=(float)$row_pdrb_2->nilai_struktur;
                        $label_pertumbuhan_2[$row_pdrb->nama_indikator]=(float)$row_pdrb_2->nilai_pertumbuhan;
                            
                    }
                    $tahun2_2=$tahun1_2-1;
                    $tahun_p_2=$tahun2_2.$periode_2;
                    $tahun_2             = $categories_p_2;
                
                    if($thn_k2==''){
                            $periode_c2=  $prde[$periode]."-".$tahun2;
                        } else {
                        $periode_c2=   $thn_k2;
                        }
                   
                    $nilaiData_2['name'] = $periode_c;
                    $nilaiData_2['data'] = $nilai_pc;
                    array_push($catdata_2, $nilaiData_2);
                    
                    $nilaiData_2['name'] = $periode_c2; //ke-2
                    $nilaiData_2['data'] = $nilai_s_2;
                    array_push($catdata_2, $nilaiData_2);
                
                }
                
                $thnmax ="SELECT MAX(tahun) AS thn,sumber FROM nilai_indikator WHERE `wilayah`='1000' AND id_indikator = '36' ";
                $list_thn = $this->db->query($thnmax);
                foreach ($list_thn->result() as $Lis_thn){
                    $thn=$Lis_thn->thn;
                    $datasumber     = "Sumber : ".$Lis_thn->sumber;
                }
                
                $title_x           = "Perbandingan Pertumbuhan Ekonomi Sektoral Antara ".$periode_c2." dengan ".$periode_c." (c to c)";
                $title_y           = "";
                
                $json_data = array(
                    "ket_tk"     => $peek_pdrb,
                    "text"       => $title_x,
                    "text2"      => $title_y,
                    "sumber"     => $datasumber,                    
                    "categories" => $tahun, 
                    "series"     => $catdata,
                    "series2"    => $catdata2,
                    "series_2"   => $catdata_2,
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
        }
        else
            die;
    }
      
    
public function demo(){
        if($this->input->is_ajax_request()){
            try 
            {                
                
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/demo/evaluasi/pdrb_sektoral/pdrb_sektoral_demo.js?v=".now("Asia/Jakarta");
                
                $data_page = array( );
                $str = $this->load->view($this->view_dir_demo."content_demo",$data_page,TRUE);

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
