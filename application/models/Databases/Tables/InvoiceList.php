<?php
class Databases_Tables_InvoiceList extends Zend_Db_Table{
    protected $_name  =   'invoice_list';
    var $invoice_id;
    var $company;
    var $contact;
    var $email;
    var $phone;
    var $amount;
    var $csv;
    var $comments;
    var $create_date;
    var $prepaid;
    var $prepaid_csv;
    var $current_page;
    var $page_now;
    var $page_size;
    function addInvoiceData(){
        $data   =   array(
            'company'       =>  $this->company,
            'contact'       =>  $this->contact,
            'email'         =>  $this->email,
            'phone'         =>  $this->phone,
            'amount'        =>  $this->amount,
            'csv'           =>  $this->csv,
            'comments'      =>  $this->comments,
            'create_date'   =>  $this->create_date,
            'prepaid'       =>  $this->prepaid,
            'prepaid_csv'   =>  $this->prepaid_csv,
        );
        $invoice_id   =   $this->insert($data);
        return $invoice_id;
    }
    
    function updateComments(){
        if($this->invoice_id && $this->comments){
            $where  =   ' invoice_id = '.$this->invoice_id;
            $invoice_data   =   $this->fetchRow($where);
            if($invoice_data && $this->comments){
                $invoice_data->comments =   $this->comments;
                $invoice_data->save();
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    function getInvoiceList(){
        $this->page_size    =   10;
        $select     =   $this->select();
        $select->from($this->_name, "count(*) as invoice_count");
        $invoice_count_data =   $this->fetchRow($select);
        unset($select);
        $invoice_count      =   $invoice_count_data->toArray();
        $pages              =   intval($invoice_count['invoice_count']/$this->page_size); 
        if($invoice_count['invoice_count']%$this->page_size){
           $pages++;  
        }
        if($this->page_now > 1){ 
             $page  =   intval($this->page_now); 
        }else{ 
             $page  =   1; 
        }
        $offset     =   $this->page_size * ($page - 1);
        $select     =   $this->select();
        $select->from($this->_name,"*");
        $select->limit($this->page_size, $offset);
        $select->order('invoice_id DESC');
        $invoice_list_data =   $this->fetchAll($select);
        if($this->page_now !=1 ){
            $previous = $this->page_now - 1;
        }else{
            $previous = $this->page_now;
        }
        if($this->page_now != $pages){
            $next = $this->page_now + 1;
        }else{
            $next = $this->page_now;
        }
        $page_info = array(
              'previous'    =>  $previous,
              'next'        =>  $next,
              'page_count'  =>  $pages,
              'num'         =>  $invoice_count,
              'page_size'   =>  $this->page_size ,
              'page_now'    =>  $this->page_now
        );
        $result = array(
              'page_html'   =>  $this->Pagination($page_info),
              'records'     =>  $invoice_list_data->toArray(),
       );
        return $result;
    }
    
    function Pagination($page_info){
        $html   =   "";
        if($page_info['page_count'] > 1)
        {
            $html   .=  '<a href="/admin/invoice">First</a> | ';
            $html   .=  '<a href="/admin/invoice/page/'.$page_info['previous'].'">Previous</a> | ';
            $html   .=  '<a href="/admin/invoice/page/'.$page_info['next'].'">Next </a> | ';
            $html   .=  '<a href="/admin/invoice/page/'.$page_info['page_count'].'">Last</a>';
        }else{
            $html = "";
        }
        
        return $html;
    }
}

?>
