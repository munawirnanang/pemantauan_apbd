<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ArcGis extends CI_Controller {
    var $view_dir   = "peppd1/arcgis/gis/";
    var $view_dir_demo   = "demo/evaluasi/gis/";
    var $view_dir_arcgis   = "peppd1/arcgis/ahh/";
    var $js_init    = "main";
    var $js_path    = "assets/js/admin/gis/gis.js";
    
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
                //if(!$this->session->userdata(SESSION_LOGIN)){throw new Exception("Session expired, please login",2);}
                
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
               // print_r($current_date_time);exit();
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/peppd1/arcgis/gis/arcgis.js?v=".now("Asia/Jakarta");
                
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
    
    public function demo(){
        if($this->input->is_ajax_request()){
            try 
            {                
                //if(!$this->session->userdata(SESSION_LOGIN)){throw new Exception("Session expired, please login",2);}
                
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                //common properties
                $this->js_init    = "main";
                $this->js_path    = "assets/js/demo/evaluasi/gis/gis_demo.js?v=".now("Asia/Jakarta");
                
                $data_page = array( );
                $str = $this->load->view($this->view_dir_demo."content",$data_page,TRUE);

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
    /*
     * Jumlah Penganggur
     */
    public function jppro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/jp/home_page_pro",$data_page);
            
    }
    public function jpkab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/jp/home_page_kab",$data_page);
            
    }
    /*
     * Tingkat Pengangguran Terbuka
     */
    public function tptpro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/tpt/home_page_pro",$data_page);
            
    }
    public function tptkab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/tpt/home_page_kab",$data_page);
            
    }
    public function ahhpro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/ahh/home_page_pro",$data_page);
            
    }
    
    public function ahhkab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/ahh/home_page_kab",$data_page);
            
    }
    /*
     * harapan Lama Sekolah
     */
    public function hlspro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/hls/home_page_pro",$data_page);
            
    }
    public function hlskab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/hls/home_page_kab",$data_page);
            
    }
    /*
     * Indeks Pembangunan Manusia
     */
    public function ipmpro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/ipm/home_page_pro",$data_page);
            
    }
    public function ipmkab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/ipm/home_page_kab",$data_page);
            
    }
    
    /*
     * pertumbuhan Ekonomi
     */
    public function pepro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/pe/home_page_pro",$data_page);
            
    }
    public function pekab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/pe/home_page_kab",$data_page);
            
    }
    /*
     * Pengeluaran per kapita
     */
    public function ppkpro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/ppk/home_page_pro",$data_page);
            
    }
    public function ppkkab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/ppk/home_page_kab",$data_page);
            
    }
    
    /*
     * Gini Rasio
     */
    public function grpro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/gr/home_page_pro",$data_page);
            
    }
    public function grkab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/gr/home_page_kab",$data_page);
            
    }
    /*
     * Rata-rata Lama Sekolah
     */
    public function rlspro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/rls/home_page_pro",$data_page);
            
    }
    public function rlskab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/rls/home_page_kab",$data_page);
            
    }
    
    /*
     * ADHB
     */
    public function adhbpro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/adhb/home_page_pro",$data_page);
            
    }
    public function adhbkab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/adhb/home_page_kab",$data_page);
            
    }
    /*
     * ADHK
     */
    public function adhkpro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/adhk/home_page_pro",$data_page);
            
    }
    public function adhkkab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/adhk/home_page_kab",$data_page);
            
    }
    /*
     * Kedalaman Kemiskinan
     */
    public function kkpro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/kk/home_page_pro",$data_page);
            
    }
    public function kkkab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/kk/home_page_kab",$data_page);
            
    }
    /*
     * keparahan Kemiskinan
     */
    public function p2pro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/p2/home_page_pro",$data_page);
            
    }
    public function p2kab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/p2/home_page_kab",$data_page);
            
    }
    /*
     * persentase penduduk miskin
     */
    public function ppmpro(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/ppm/home_page_pro",$data_page);
            
    }
    public function ppmkab(){
        date_default_timezone_set("Asia/Jakarta");
            
            //$sidebar_view = "admin/template/sidebar/sidebar";
            
            //$main_content = $this->view_dir_arcgis."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/ppm/home_page_kab",$data_page);
            
    }
    /*
     * Junlah penduduk miskin
     */
    public function jpmpro(){
        date_default_timezone_set("Asia/Jakarta");
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/jpm/home_page_pro",$data_page);
            
    }
    public function jpmkab(){
        date_default_timezone_set("Asia/Jakarta");
            
            $home_properties = array();
            //MAIN CONTENT
            //$main_content= $this->view_dir_arcgis."/home_page_home_page_pro";            
            //SIDEBAR
            //$sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                //"main_content"  =>  $main_content,
                //"sidebar"       =>  $sidebar_view,
                "profile"       =>  'Demo',
                "home_properties"       =>  $home_properties,
                "group"         =>  'Demo',
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("peppd1/arcgis/jpm/home_page_kab",$data_page);
            
    }
}
