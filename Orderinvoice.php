
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orderinvoice extends CI_Controller {


	public function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('order_model');
        $this->load->model('orderItems_model');
        $this->load->model('orderInvoice_model');
        $this->load->model('globalSettings_model');
        $this->load->model('orderInvoice_model');
        $this->adminauth->restrict();
    }


	public function orderInvoicePdf($orderid){

		$langid = $this->adminauth->BackofficeLang;

		$data['orderData']=  $this->order_model->getOrderData($orderid);
        $data['productData']=  $this->order_model->getOrderProductData($orderid,$langid);
        $data['customerData']=  $this->order_model->getCustomerData($orderid);
        $data['shippingData']=  $this->order_model->getShippingData($orderid,$langid);

        $data['settings']=$this->globalSettings_model->listSettings();

        $data['invoicenumber'] = $this->orderInvoice_model->getInvoiceNumber($orderid);
        $invoicenumber = 'INV00000'.$data['invoicenumber'][0]['number'].'.pdf';

        /* Logo and site name */
        $logo =  log_path.$data['settings'][0]['logo'];
      	$sitename =  $data['settings'][0]['name'];

		$this->load->library("Pdf");

    	$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
    	$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);
    	$pdf->SetCreator(PDF_CREATOR);
      
        $pdf->AddPage();
        
        ob_clean();

     
		$html = '

		<table cellpadding="1" cellspacing="1" style="text-align:center;">
		<tr><td></td></tr>
		<tr style="text-align:left;"><td><img src="'.$logo.'" border="0" height="100" width="250" align="top" /></td></tr>
		<tr style="text-align:left;"><td><h2>'.$sitename.'</h2></td></tr>
		</table>

		<table><tr><td></td><td></td></tr><tr><td></td><td></td></tr></table>
		
		<table style="width:100%">
		  <tr style="text-align:left;">
		    <th colspan="2">
		    	<h3>'.$data['customerData'][0]['firstname'].' '.$data['customerData'][0]['lastname'].'</h3><br>
		    	<small style="font-size:10px; text-align:left;">'.$data['customerData'][0]['email'].'</small> <br/>
				<small style="font-size:10px; text-align:left;">'.$data['customerData'][0]['homephone'].'</small> <br/>
				<small style="font-size:10px; text-align:left;">'.$data['customerData'][0]['mobilephone'].'</small>
		    </th>
		    <th>

		    </th> 
		    <th></th> 
		    <th align="right">
		    	<h3>ORDER</h3>
		    	<small style="font-size:10px;">Order Date:</small>
		    </th> 
		    <th width="100" align="left">
		    	<h3>#'.$data['orderData'][0]['reference'].'</h3><br/>
		    	<small style="font-size:10px;">'.date('d-m-Y',strtotime($data['orderData'][0]['created']) ).'</small> <br/>
		    </th> 
		  </tr>

		  <tr style="text-align:center;">
		  	<td></td> 
		    <td></td>
		    <td></td>	
		  </tr>
		</table>

		<table border="0" cellpadding="3" cellspacing="3" style="text-align:center;border-color:grey;">
		  
		  <tr style="text-align:center;" >

		  	<td></td> 
		    <td></td>
		    <td></td>	

		    <td></td>
		    <td></td>
		   
		  </tr>
		
		  <tr style="text-align:center;">
		    <th><b><small style="font-size:10px;">Name</small></b></th>
		    <th><b><small style="font-size:10px;">Unit Price</small></b></th> 
		    <th><b><small style="font-size:10px;">Tax</small></b></th> 
		    <th><b><small style="font-size:10px;">Discount</small></b></th> 
		    <th><b><small style="font-size:10px;">Total Price</small></b></th> 
		  </tr> ';


		foreach ($data['productData'] as $pd) {  
			if($pd['percentage']!= null){
				$taxname=$pd['taxname'];
				$taxpercentage= $pd['percentage'].'%';
			}else{
				$taxname="No tax";
				$taxpercentage="";
			}

			if($pd['discount']!= 0){  
                $temtotal =  $pd['price']*$pd['discount']/100;
            }else{
                $temtotal =0;
            }

			if($pd['taxincprice']== 0){  
                $grandtotal =  $pd['price'];
                $grandtotal =  $grandtotal - $temtotal;
            }else{
                $grandtotal =  $pd['taxincprice'];
                $grandtotal =  $grandtotal - $temtotal;
            }	

		$html .='  

		  <tr style="text-align:center;">

		  	<td><small style="font-size:9px;">'.$pd['name'].'</small></td> 
		    <td><small style="font-size:9px;">$'.$pd['price'].'</small></td> 
		    <td><small style="font-size:9px;">'.$taxpercentage.' ('.$taxname.')</small> </td>	
		    <td><small style="font-size:9px;">'.$pd['discount'].' %</small></td>
		    <td><small style="font-size:9px;">$'.$grandtotal.'</small> <br/></td>

		</tr>';

		}


		$html .='<tr style="text-align:center;">

		  	<td></td> 
		    <td></td>
		    <td></td>	

		    <td>
		    	<small style="font-size:10px;">Shipping:</small>
		    </td>

		    <td>
		    	<small style="font-size:10px;"><b>$'.$data['orderData'][0]['shippingamount'].'</b></small> 
		    </td>
		   

		  </tr>

		  <tr style="text-align:center;">

		  	<td></td> 
		    <td></td>
		    <td></td>	

		    <td>
		    	<small style="font-size:10px;">Total Price:</small>
		    </td>

		    <td>
		    	<small style="font-size:10px;"><b>$'.$data['orderData'][0]['grandtotal'].'</b></small> 
		    </td>
		   

		  </tr>
		</table>

		<table><tr><td></td><td></td></tr><tr><td></td><td></td></tr></table>

		<table style="width:100%; border-color:grey;">
		  <tr style="text-align:center;">
		   
		    <th colspan="2">
		    	<h3 style="font-size:10px;">Billing Address</h3><br/>
		    	<small style="font-size:10px;">'.$data['customerData'][0]['billingaddress1'].', </small> <br/>
		    	<small style="font-size:10px;">'.$data['customerData'][0]['billingaddress2'].'</small> <br/>
		    	<small style="font-size:10px;">'.$data['customerData'][0]['billingcity'].','.$data['customerData'][0]['billingstatename'].'</small> <br/>
		    	<small style="font-size:10px;">'.$data['customerData'][0]['billingcountryname'].','.$data['customerData'][0]['billingzip'].'</small> <br/>
		    </th>

		    
		    <th colspan="2">	
		    	<h3 style="font-size:10px;">Shipping Address</h3><br/>
		    	<small style="font-size:10px;">'.$data['customerData'][0]['shippingaddress1'].',</small> <br/>
		    	<small style="font-size:10px;">'.$data['customerData'][0]['shippingaddress2'].'</small> <br/>
		    	<small style="font-size:10px;">'.$data['customerData'][0]['shippingcity'].','.$data['customerData'][0]['shippingstatename'].'</small> <br/>
		    	<small style="font-size:10px;">'.$data['customerData'][0]['shippingcountryname'].','.$data['customerData'][0]['shippingzip'].'</small> <br/>
		    </th> 

		   
		    <th></th> 
		  </tr>

		  <tr style="text-align:center;">

		    <td></td>
		    <td></td>	
		    <td></td> 
		  </tr>

		</table>


		<table style="width:100%">
		  <tr style="text-align:left;">
		    <td colspan="5">
		    	<small style="font-size:10px;">Thanks for the purchase with us.</small> <br/>
		    </td>
		  </tr>

		</table>

		';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($invoicenumber,'D');
    }

}    