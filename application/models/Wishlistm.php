<?php 
class Wishlistm extends CI_Model {
    function __construct() {
    parent::__construct();
       $this->load->database();
    }
    public function add($cid,$pid){
        $data = array('customer_id'=>$cid,'product_id'=>$pid);
        $this->db->insert('wishlist_table', $data);
    }
   
    public function remove($cid,$pid){
        $data = array('customer_id'=>$cid,'product_id'=>$pid);
        $this->db->delete('wishlist_table', $data);
    }
    public function getIds($cid){
        $data = array('customer_id'=>$cid);
                $this->db->select('product_id');
        return $this->db->get_where('wishlist_table', $data)->result_array();
    }
}
?>