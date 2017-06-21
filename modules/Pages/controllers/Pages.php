<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends MX_Controller
{

    public $module_assets;

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('ion_auth','functions'));
        $this->load->model('Pages/page_model');

        //Check login
        if (!$this->ion_auth->logged_in()) {
            redirect('gc-admin/login', 'refresh');
        }
        elseif (!$this->ion_auth->is_admin()) {
            redirect('/', 'refresh');
        }

        //Define locale of the site
        $this->layout->locale = 'admin';

        //Load Module CSS and JS from config to Layout library
        foreach ($this->config->load('module_assets', true) as $key => $asset){
            $this->layout->$key = $asset;
        }
    }

    public function index($msg = "", $type = "") {
        $this->load->library('pagination');
        $offset = $this->input->get("per_page");
        $jsinc = array('plugins.js');

        if ($offset <= 0) {
            $offset = 0;
        }

        $data = array(
            'pages' => $this->page_model->get_pages(),
            'jsinc' => $jsinc,
            'msg' => $this->functions->show_msg($msg, $type)
        );

        $config['base_url'] = base_url() . 'gc-admin/pages/list/';
        $config['total_rows'] = 3;
        $config['per_page'] = 3;
        $config['page_query_string'] = TRUE;
        $data['page_title'] = 'Pages List';
        $data['meta_description'] = 'GreatCMS Pages List';

        $this->pagination->initialize($config);
        $this->layout->_render("Pages/page_list", $data);
    }

    public function add_page(){
        if(!$this->session->userdata('login')) {
            redirect('login');
        }else {
            $this->load->view('add_page');
        }
    }

    public function edit_page($id){

        $this->layout->css[] = '/assets/dist/keditor/css/keditor-1.1.4.min.css';
        $this->layout->css[] = '/assets/dist/keditor/keditor-components-1.1.4.min.css';
        $this->layout->css[] = '/assets/libs/fileuploader/jquery.fileuploader.css';
        $this->layout->css[] = '/assets/global/plugins/ckeditor/skins/moono/editor.css';

        $this->layout->bottom_js[] = '/assets/vendor/jquery.nicescroll-3.6.6/jquery.nicescroll.min.js';

        $this->layout->bottom_js[] = '/assets/vendor/ckeditor-4.5.6/ckeditor.js';
        $this->layout->bottom_js[] = '/assets/vendor/ckeditor-4.5.6/adapters/jquery.js';

//                $this->layout->bottom_js[] = '/assets/global/plugins/ckeditor/config.js';
//        $this->layout->bottom_js[] = '/assets/global/plugins/ckeditor/lang/en.js';
//        $this->layout->bottom_js[] = '/assets/global/plugins/ckeditor/styles.js';
//        $this->layout->bottom_js[] = '/assets/global/plugins/ckeditor/ckeditor.js';

        //$this->layout->bottom_js[] = '/assets/dist/keditor/js/keditor-1.1.4.js';
        //$this->layout->bottom_js[] = '/assets/dist/keditor/js/keditor-components-1.1.4.min.js';

        $data['page_title'] = 'Pages List';
        $data['meta_description'] = 'GreatCMS Pages List';
        $data['page'] = $this->page_model->get_page($id);
        $data['csrf'] = array(
            'name' => $this->security->get_csrf_token_name(),
            'hash' => $this->security->get_csrf_hash()
        );
        //$this->load->view('add_page',$data);
        $this->layout->_render("Pages/add_page", $data);
    }

    public function delete_page($id){
        if(!$this->session->userdata('login')) {
            redirect('login');
        }else {
            $data['page'] = $this->page_model->delete_page($id);
            redirect("page");
        }
    }

    public function save_page(){
        $text = $_REQUEST['page'];
        $title = (strlen($_REQUEST['title']) > 0) ? $_REQUEST['title'] : "No Title";
        $safi = $this->safisha($text);
        $data['title'] = $title;
        $data['content'] = $safi;
        $data['original'] = "<db>".$text."</db>";

        $this->page_model->add_page($data);
        //$data['safi'] = $safi;
        //$this->load->view('page_list',$data);
    }
    public function update_page($id){
        $text = $_REQUEST['page'];
        $title = (strlen($_REQUEST['title']) > 0) ? $_REQUEST['title'] : "No Title";
        $safi = $this->safisha($text);
        $data['title'] = $title;
        $data['content'] = $safi;
        $data['original'] = "<db>".$text."</db>";

        $this->page_model->update_page($id,$data);
        //$data['safi'] = $safi;
        //$this->load->view('page_list',$data);
    }

    function  show_text($id){
        $data['page'] = $this->page_model->get_page($id);
        $this->load->view('preview', $data);
    }

    public function safisha($text){
        $text_safi = $this->functions->strip_tags_content($text, '<info>', TRUE);
        $text_safi = preg_replace ('/id="keditor-(.+?)"/', '', $text_safi);
        $text_safi = preg_replace ('/class="keditor-(.+?)"/', '', $text_safi);
        $text_safi = preg_replace ('/<div >   <a(.+?)<.div./', '', $text_safi);
        return $text_safi;
    }

}