<?php

/**
 * @author Dejan Ciric 570/15
 * @author Jakov Jezdic 0043/15
 * 
 * Admin - class that handle all requests for admin
 */
class Admin extends CI_Controller {

    // Creating new instance
    // @return void 
    function __construct() {
        parent::__construct();
        $this->load->model("User_model");
        $this->load->model("destination_model");
        $this->load->model("statistic_model");
        $this->load->model("review_model");
        $this->load->model("request_model");
        // check if user is already logged in, or if unauthorized access through the link
        if (($this->session->userdata('user')) != NULL) {
            switch ($this->session->userdata('user')->status) {
                 case "user":
                    redirect("user");
                    break;
                case "super_user":
                    redirect("super_user");
                    break;
                
                
            }
        }
        $phpArray = $this->get_all_destinations();
        ?>
<script type="text/javascript">var jArray =<?php echo json_encode($phpArray); ?>;</script>
<?php
    }

    // default function, load default views
    // and add all information reading from database on load here
    // @return void
    function index() {
        $data['profile_pic'] = $this->get_img_name();
        $data['last_pendings_html'] =$this->request_model->get_html_all_requests();
        
        $this->load->view("templates/admin_header.php", $data);
        $this->load->view("admin_home.php");
        $this->load->view("templates/footer.php");
    }

    // load different views for user
    // and add all information reading from database on load here
    // @param string $page
    // @return void
    public function load($page, $data=null) {
        $info['profile_pic'] = $this->get_img_name();       
        $data['last_pendings_html'] =$this->request_model->get_html_all_requests();
        
        $this->load->view("templates/admin_header.php", $info);
        $this->load->view($page . ".php", $data);
        $this->load->view("templates/footer.php");
    }

    
    
    public function approve_request($request_id){
        //Proveri se tim requesta i u zavinosti od toga izvrsi funkcija, i obrise request
        $this->load("admin_home");
    }
    public function dismiss_request($request_id){
        //Brisanje requesta
        $this->request_model->delete($request_id);
        $this->load("admin_home");
    }
    public function delete_review($destination_id, $review_id){
        //Brisanje reviewa
        $this->review_model->delete($review_id);
        $this->load_dest($destination_id);
    }
    
    
    
    // get img name by its id if null return default avatar
    // @param string $id
    // @return string
    public function get_img_name() {

        $path = $this->User_model->get_img_name($this->session->userdata('user')->idImg);
        
        if ($path == "avatar.png"){
           
            return base_url() . "img/avatar.png";
        }
            
        else {
            return base_url() . "uploads/" . $path;
        }
    }
public function get_all_destinations(){
        return $this->destination_model->get_all_destinations();
    }
    
      // logout function, breaks session
    // @return void
    public function logout() {
        $this->session->unset_userdata("user");
        $this->session->sess_destroy();
        redirect("Guest");
    }
        public function getStatistics(){
        
        
        $statistics['userCount']=0;
        $statistics['reviewCount']=0;
        $statistics['destinationCount']=0;
        $statistics['positiveVoteCount']=0;
        $statistics['negativeVoteCount']=0;
        
        $statistics=$this->statistic_model->getStatistics();
        
        
        $data['date']=$statistics->date;;
        $data['userCount']=$statistics->userCount;;
        $data['reviewCount']=$statistics->reviewCount;
        $data['destinationCount']=$statistics->destinationCount;
        $data['positiveVoteCount']=$statistics->positiveVoteCount;
        $data['posReviews']=$statistics->posReviews;
        $this->load("guest_statistics",$data);
}
    public function search(){
       $output = '';
		$query = '';
		
		if($this->input->post('query'))
		{
			$query = $this->input->post('query');
		}
		$data = $this->destination_model->search_data($query);
		$output .= '
		<div class="table-responsive">
		<table class="table bg-light">

		';
		if($data->num_rows() > 0)
		{
			foreach($data->result() as $row)
			{
				$output .= '
						<tr>
							<td><a href="'.base_url().'index.php/admin/load_dest/'.$row->idDest.'">'.$row->name.'</a></td>
                                                        <td>'.$row->country.'</td>    
						</tr>
				';
			}
		}
		else
		{
			$output .= '<tr>
							
						</tr>';
		}
		$output .= '</table>';
		echo $output;
    }
    
     public function load_dest($id,$message=null){
       $data['dest_name'] = $this->destination_model->get_name($id);
       $data['dest_country'] = $this->destination_model->get_country($id);
       $data['all_reviews_current_destination_html'] = $this->review_model->get_html_all_reviews_admin($id);
       $data['dest_id']=$id;
       $data['message']=$message;
       
       $this->load("destination",$data);
    }

         // add new pending destination if it is requested from superuser
        // or just enter new destination if it is requested from admin
        // @return void
        public function add_destination(){

                $this->form_validation->set_rules("destination", "Username", "trim|required|min_length[2]|max_length[40]");
                $this->form_validation->set_rules("country", "Password", "trim|required|min_length[2]|max_length[40]");
                if ($this->form_validation->run()) {
                    
                $data['name'] = $this->input->post('destination');
                $data['longitude'] = explode(":",$this->input->post('longitudeH'))[1];
                $data['latitude'] = explode(":",$this->input->post('latitudeH'))[1];
                $data['pending'] = 0;
                $data['country'] = $this->input->post('country');
                
                $this->destination_model->insert_destination($data);

                $this->load("super_user_add_destination", Array("message"=>"Successfully changed username"));
                } else {
                    $this->load("super_user_add_destination");
                }
            
        }
        
            // form check and forward request to coresponding model
    // @return void
    public function change_username() {
        $this->form_validation->set_rules("usernameChange", "Username", "trim|required|min_length[4]|max_length[20]|is_unique[user.username]");
        if ($this->form_validation->run()) {
            $new_username = $this->input->post('usernameChange');

            $this->User_model->change_username($new_username);
            $this->load("profile", Array("message"=>"Successfully changed username"));
        } else {
            $this->load("profile");
        }
    }

    // form check and forward request to coresponding model
    // @return void
    public function change_password() {
        $this->form_validation->set_rules("oldPass", "Old password", "trim|required|min_length[4]|max_length[20]");
        $this->form_validation->set_rules("newPass1", "New password", "trim|required|min_length[4]|max_length[20]");
        $this->form_validation->set_rules("newPass2", "Confirm new password", "trim|required|min_length[4]|max_length[20]|matches[newPass1]");
        if ($this->form_validation->run()) {
            $new_password = $this->input->post('newPass1');

            if (!$this->User_model->check_password($this->input->post('oldPass'),$this->session->userdata('user')->username)) {
                $this->load("profile", Array("message"=>"Wrong old password"));
            } else {
                $this->User_model->change_password($new_password);
                $this->load("profile", Array("message"=>"Successfully changed username"));
            }
        } else {
            $this->load("profile");
        }
    }

    // upload new photo to uploads folder in GeoTag, persist it's name to table
    // and later use path for display
    // @return void
    public function do_upload() {
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = 100;
        $config['max_width'] = 1024;
        $config['max_height'] = 768;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('pic')) {
            $message = $this->upload->display_errors();
            $this->load("profile", Array("message"=>$message));
        } else {
            $this->User_model->change_photo($this->upload->data()['file_name']);
            $this->load("profile", Array("message"=>"Successfully changed username"));
        }
    }
    
    
    public function add_review($id){
        $this->form_validation->set_rules("comment", "Comment", "trim|required|min_length[2]|max_length[255]");
        
        $data['content']="";
        if ($this->form_validation->run()) {
            $data['content'] = $this->input->post('comment');  
            
            $data['upCount']=0;
            $data['downCount']=0;

            date_default_timezone_set("Europe/Belgrade");
            $now = new DateTime();
            $date=$now->format('Y-m-d');

            $data['date']=$date;
            $data['username']=$this->session->userdata('user')->username;
            $data['idImg']=null;
            $data['idDest']=$id;
            
            
            
            if (isset($_FILES['pic']) && $_FILES['pic']['error'] != UPLOAD_ERR_NO_FILE) {
                
            
                $config['upload_path'] = './uploads/';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size'] = 1000;
                $config['max_width'] = 2048;
                $config['max_height'] = 1024;

                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('pic')) {
                    $message = (string)$this->upload->display_errors();
                   
                    $this->load_dest($id,$message);
                    return;
                } else {
                    $data['idImg']=$this->review_model->add_photo($this->upload->data()['file_name']);
                }
            
            }
            
        

            $this->review_model->insert_review($data);
             
            $this->load_dest($id);
        } else {
            $this->load_dest($id);
        }
        
    }
    
    // loads profile view with needed data
    public function preview_profile() {
        
        $data['review_count'] = $this->User_model->get_user_review_count($this->session->userdata('user')->username);
        $data['places_count'] = $this->User_model->get_user_added_places_count($this->session->userdata('user')->username);
        
        
        $up_down_count = $this->User_model->up_down_count($this->session->userdata('user')->username);
        $data['up_count'] = $up_down_count['upCount'];
        $data['down_count'] = $up_down_count['downCount'];
        
        $this->load('profile', $data);
    }
    
    // previews profile_other if user is trying to view someone elses profile
    public function preview_other_user($other) {
        
        if ($other != $this->session->userdata('user')->username) {
            $data['review_count'] = $this->User_model->get_user_review_count($other);
            $data['places_count'] = $this->User_model->get_user_added_places_count($other);


            $up_down_count = $this->User_model->up_down_count($other);
            $data['up_count'] = $up_down_count['upCount'];
            $data['down_count'] = $up_down_count['downCount'];
            
            $full_name = $this->User_model->get_full_name($other);
            $data['firstname'] = $full_name['firstname'];
            $data['lastname'] = $full_name['lastname'];
            $data['username'] = $other;
            
            
            $this->load("profile_other", $data);
        }
        else
            $this->preview_profile();
    }
}
