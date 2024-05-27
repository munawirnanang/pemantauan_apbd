<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
    var $view_dir   = "admin/home";
    var $view_dir_demo   = "demo/home";
    var $js_init    = "home";
    var $js_path    = "assets/js/userdefined/home/home.js";
    function __construct() {
        parent::__construct();
        $this->load->model("M_Master","m_ref");
        date_default_timezone_set("Asia/Jakarta");
    }
    public function index(){
        
        try {
            if(!$this->session->userdata(SESSION_LOGIN))
                throw new Exception("Session Expired",2);
            
            date_default_timezone_set("Asia/Jakarta");
            
            $sidebar_view = "admin/template/sidebar/sidebar";
            
            $main_content = $this->view_dir."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            $main_content= $this->view_dir."/home_page_".$this->session->userdata(SESSION_LOGIN)->groupid;
            
            //SIDEBAR
            $sidebar_view = "admin/template/sidebar/sidebar_".$this->session->userdata(SESSION_LOGIN)->groupid;
            $this->js_path="assets/js/admin/home/home_".$this->session->userdata(SESSION_LOGIN)->groupid.".js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                "main_content"  =>  $main_content,
                "sidebar"       =>  $sidebar_view,
                "profile"       =>  $this->session->userdata(SESSION_LOGIN)->name,
                "home_properties"       =>  $home_properties,
                "group"         =>  $this->session->userdata(SESSION_LOGIN)->groupname,
                "notif"         =>'0',
                "csrf"          =>  array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                "js_path"       =>  base_url($this->js_path),
                "js_init"       =>  $this->js_init,
            );
            $this->load->view("admin/template/template",$data_page);
            
        } catch (Exception $exc) {
            if($exc->getCode()==2)
                redirect ("Welcome?err=session_expired");
            else
                show_error($exc->getMessage(), 500, "Error");
        }
        
    }
    
    /*
     * get data detail alamat
     */
    public function detail_get()
    {
        if($this->input->is_ajax_request()){
            try {
                if(!$this->session->userdata(SESSION_LOGIN))
                    throw new Exception("Session berakhir, silahkan login ulang",2);
                
                
                
                date_default_timezone_set("Asia/Jakarta");
                $current_date_time = date("Y-m-d H:i:s");
                

                $address = "Direktorat PEPPD <br/>
                            Jalan Taman Sunda Kelapa No.9 Jakarta 10310,<br>
                            Telp. 021 3193 6207 <br/>
                            Fax 021 3145 374";
                $cond = array($this->session->userdata(SESSION_LOGIN)->id_group);
                $sql = "SELECT alamat FROM tbl_profile_kl WHERE iddept=?";
                $list_data =$this->db->query($sql,$cond);
                if($list_data->num_rows() > 0)
                    $address = $list_data->row()->alamat;
                
                //sukses
                $output = array(
                    "status"        =>  1,
                    "msg"           =>  "Success",
                    "address"       =>  $address,
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            } catch (Exception $exc) {
                $output = array(
                    "status"    =>  $exc->getCode(),
                    "msg"       =>  $exc->getMessage(),
                    "csrf_hash"     =>  $this->security->get_csrf_hash(),
                );
                exit(json_encode($output));
            }
        }
        else{exit("Access Denied");}
    }
    
    /*
     * get Demo
     */
     public function demo(){
        
      //  try {
//            if(!$this->session->userdata(SESSION_LOGIN))
//                throw new Exception("Session Expired",2);
            
            date_default_timezone_set("Asia/Jakarta");
            
            $sidebar_view = "admin/template/sidebar/sidebar";
            
            $main_content = $this->view_dir_demo."/home_page";
            
            $home_properties = array();
            //MAIN CONTENT
            $main_content= $this->view_dir_demo."/home_page_demo";            
            //SIDEBAR
            $sidebar_view = "demo/template/sidebar/sidebar_demo";
            $this->js_path="assets/demo/home/home_demo.js";
            $this->js_init="home.init();";
            $data_page = array(
                "tag_title"     =>  APP_TITLE,
                "main_content"  =>  $main_content,
                "sidebar"       =>  $sidebar_view,
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
            $this->load->view("demo/template/template",$data_page);
            
//        } catch (Exception $exc) {
//            if($exc->getCode()==2)
//                redirect ("Welcome?err=session_expired");
//            else
//                show_error($exc->getMessage(), 500, "Error");
//        }
        
    }
    
    
    
}
