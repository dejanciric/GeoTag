<?php

/**
 * @author Marko Hudomal 0112/15
 * @author Jakov Jezdic 0043/2015
 * Request_model - handles all database manipulation regarding requests
 */
class Request_model extends CI_Model{
    public function __construct() {
        parent::__construct();
        $this->load->model("destination_model");
        $this->load->model("review_model");
        $this->load->model("user_model");
    }
    
    // insert new request
    // @param string $type Request type, int $id request-type-specific id, string $username Username of user connected with request
    public function insert($type, $id, $username=NULL) {   
        
        $new_request['idRev'] = NULL;
        $new_request['idDest'] = NULL;
        $new_request['username'] = $username;
        $new_request['type'] = $type;
        switch($type){
            case "destination added":
                $new_request['idDest'] = $id;
                break;
            case "negative review":
                $new_request['idRev'] = $id;
                break;
            case "user promotion":
                break;
            case "destination confirm":
                $new_request['idDest'] = $id;
        }
        
        $this->db->insert('request', $new_request); 
    }
    
    
    
    // delete request
    // @param int $request_id
    // @return void
    public function delete($request_id) {
        $this->db->where('idReq', $request_id);
        $this->db->delete('request');
    }
    
    // get review by it's id
    // @param int $id 
    // @return request as array
    public function get_request($id){
        $query = $this->db->query("select * from request where idReq=".$id);
        return $query->result()[0];
    }
    
    // get all requests
    // @return string $ret HTML code for all requests
    public function get_html_all_requests()
    {
        $ret = "";  
        $query = $this->db->query("SELECT * from request ORDER BY idReq DESC");
        
        foreach ($query->result() as $row)
        {
            $dest_name="";
            if ($row->type=="destination confirm") continue;
            
            switch ($row->type) {
            case "destination added":
                $dest=$this->destination_model->get_destination($row->idDest);
                
                $req_content="<strong>Destination: </strong>".$dest->name.", ".$dest->country."<hr>"."(Long: ".$dest->longitude.", Lat: ".$dest->latitude.")";
                $button_func="<i>Add destination?</i>";
                break;
            case "negative review":
                $rev = $this->review_model->get_review($row->idRev);
                $dest= $this->destination_model->get_destination($rev->idDest);
                
                $req_content="<strong>Destination:</strong><a href='".base_url()."index.php/".$this->session->userdata('user')->status."/load_dest/".$rev->idDest."'>".$dest->name."</a>, ".$dest->country."<hr>"."<Strong>Up/Down vote: </strong>".$rev->upCount."/".$rev->downCount."<hr><strong>Text: </strong><br>".$rev->content;
                $button_func="<i>Delete review?</i>";
                break;
            case "user promotion":
                $status=$this->user_model->get_status($row->username);
                $req_content="<strong>Username: </strong>"."<a href='".base_url()."index.php/".$this->session->userdata('user')->status."/preview_other_user/".$row->username."'>".$row->username."</a>"."<hr> <strong>Status: </strong>".$status; 
                $button_func="<i>Promote to SuperUser?</i>";
                break;
            default:
                $req_content="Request type unknown..";
                $button_func="";
                break;
            }
            //User validation
            if (($this->session->userdata('user')) != NULL) {
            $user1 = $this->session->userdata('user')->status;
            }
            else
                $user1 = "guest";
            //if ($req_content=="Request type unknown..") continue;
            
            $ret=$ret."<div class=\"card\" style=\"margin-top:20px\">
                                <div class=\"card-header\" style=\"overflow: auto;\">
                                    <table style=\"width:100%;\">
                                        <tr>
                                            <td rowspan=\"2\"><strong><a href='".base_url()."index.php/".$this->session->userdata('user')->status."/preview_other_user/".$row->username."'>".$row->username."</a></strong></td>
                                            <td rowspan=\"2\" style=\"text-align:right\">$button_func</td>
                                            <form action=\"".base_url()."index.php/".$user1."/approve_request/".$row->idReq."\" method=\"PUT\" >
                                                <td style=\"text-align: right;\"><button type=\"submit\" class=\"btn btn-success btn-sm\">Approve</button> </td>
                                            </form>
                                         </tr>
                                         <tr>
                                            <form action=\"".base_url()."index.php/".$user1."/dismiss_request/".$row->idReq."\" method=\"PUT\" >
                                                <td style=\"text-align: right;\"><button type=\"submit\" class=\"btn btn-danger btn-sm\">Dismiss</button> </td>
                                            </form>
                                        </tr>
                                    </table>
                                </div>
                                <div class=\"card-body\">
                                    <p><strong>Type: </strong>".$row->type."</p><hr>
                                    <p>".$req_content."</p>
                                </div>
                       </div>";        
        }
        
        return $ret;
    }
    
}
