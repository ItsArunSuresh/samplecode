<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct() {
        parent::__construct();
       
        $this->load->model('language_model');
        $this->load->model('homeSlider_model');
        $this->load->model('homeSliderLang_model');
        $this->load->model('globalSettings_model');
        $this->load->model('category_model');
        $this->load->model('banner_model');
        $this->load->model('homeSlider_model');
        $this->load->model('product_model');
    }

	public function index()
	{
       

		$data['title'] = 'Home';
        $data['languages'] = $this->language_model->listActiveLanguagesByOrder();
        $data['setting'] = $this->globalSettings_model->listSettings();
        $data['categories'] = $this->category_model->listCategories(0,$this->session->userdata('langid'));

        $bannername= 'homebanner';
        $slidername= 'homeslider';

        $data['bannerDetails'] = $this->banner_model->getBannerByName($bannername,$this->session->userdata('langid')); 
        $data['sliderDetails'] = $this->homeSlider_model->getSliderByName($slidername,$this->session->userdata('langid'));

        $data['productDetails'] = $this->product_model->listFeaturedProductsHome($this->session->userdata('langid')); 
        
        $data['menu']=$this->buildMenu($data['categories']);
       
        $data["search"] = $this->lang->line("search");
        $data["cart"] = $this->lang->line("cart");
        $data["login"] = $this->lang->line("login");
        $data["products"] = $this->lang->line("products");
        $data["wishlist"] = $this->lang->line("wishlist");
        $data["home"] = $this->lang->line("home");

		$this->load->view('common/header', $data);
		$this->load->view('common/topbar', $data);

        $this->load->view('frontsite/home', $data);
        $this->load->view('common/footer');
	}


    
    public function buildMenu($data){
       
        $divData="";
        foreach($data as $menu){

            $data['subcategories'] = $this->category_model->listCategories($menu['category_id'],$this->session->userdata('langid'));

            $divData.='<li class="dropdown mega-dropdown">
                    <a href="#" class="dropdown-toggle col_wh" data-toggle="dropdown">'.$menu['name'].'';

            if(count($data['subcategories'])>0){
               $divData.='<span class="caret ml10p"></span>';
            }   
            
            $divData.='</a>';

            if(count($data['subcategories'])>0){

               
                $divData.='<ul class="dropdown-menu mega-dropdown-menu bor_rad0p">';

                for($i=0; $i < count($data['subcategories']); $i++){

                    $divData.='<li class="col-sm-3">';
                    $divData.='<ul>';

                    $divData.='<li class="dropdown-header">'.$data['subcategories'][$i]['name'].'</li>'; /* Header */
                    
                    $data['subcategories1'] = $this->category_model->listCategories($data['subcategories'][$i]['category_id'],   $this->session->userdata('langid'));

                    for($j=0; $j < count($data['subcategories1']); $j++){
                        $divData.='<li><a href="#">'.$data['subcategories1'][$i]['name'].'</a></li>'; /* sub under header */
                    }

                   

                    $divData.='</ul></li>';

                }


                $divData.='</ul>';

            }        
                
 
            $divData.='</li>';        
            
        } 

        return $divData;   
    }
}
