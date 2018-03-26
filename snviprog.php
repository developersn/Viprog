<?php
$pluginData[sn][type] = 'payment';
$pluginData[sn][name] = 'پرداخت آنلاين';
$pluginData[sn][uniq] = 'sn';
$pluginData[sn][description] = 'اتصال به درگاه اختصاصي';
$pluginData[sn][author][name] = 'sn';
$pluginData[sn][author][url] = 'freer.ir';
$pluginData[sn][author][email] = 'info@domain.com';


$pluginData[sn][field][config][1][title] = 'API';
$pluginData[sn][field][config][1][name] = 'merchant';
$pluginData[sn][field][config][2][title] = 'ارسال موارد اختیاری خریدار به وب سرویس/ از گزینه ON OFF برای فعال و غیر فعال کردن این گزینه استفاده نمایید';
$pluginData[sn][field][config][2][name] = 'webservice';

//-- تابع انتقال به دروازه پرداخت
function gateway__sn($data)
{
global $config,$db,$smarty,$pay;
$merchantID = trim($data[merchant]);
$amount = ceil($data[amount]/10);
$invoice_id = $data[invoice_id];

						// Security
						@session_start();
						$sec = uniqid();
						$md = md5($sec.'vm');
						// Security
						$callBackUrl = $data[callback]."&check={$md}&sec={$sec}";


			$sql2 = "SELECT * FROM `payment` WHERE `payment_rand` = {$invoice_id} LIMIT 1;";
			$pay = $db->fetch($sql2);

						if ($data[webservice] == 'ON'){

                     $Email=$pay['payment_email'];
                     $Mobile=$pay['payment_mobile'];
                     $Description="پرداخت فاکتور به شماره : ".$invoice_id ;
						    
				    if($Email==''){$Email='0'; }
				     if($Paymenter==''){$Paymenter='0';}
				      if($Mobile==''){$Mobile='0';}
				       if($Description==''){$Description='0';}
				       
					   	$data_string = json_encode(array(
					'pin'=> $merchantID,
					'price'=> $amount,
					'callback'=>$callBackUrl ,
					'order_id'=> $invoice_id,
					'email'=> $Email,
					'description'=> $Description,
					'name'=> $Paymenter,
					'mobile'=> $Mobile,
					'ip'=> $_SERVER['REMOTE_ADDR'],
					'callback_type'=>2
					));
				    
			        }
					else
					{
					   	$data_string = json_encode(array(
					'pin'=> $merchantID,
					'price'=> $amount,
					'callback'=>$callBackUrl ,
					'order_id'=> $invoice_id,
					'email'=> '0',
					'description'=> $Description,
					'name'=> '0',
					'mobile'=> '0',
					'ip'=> $_SERVER['REMOTE_ADDR'],
					'callback_type'=>2
					));
					    
					}


			$ch = curl_init('https://developerapi.net/api/v1/request');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
			);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
			$result = curl_exec($ch);
			curl_close($ch);
			$json = json_decode($result,true);
			
													$res=$json['result'];
	                 switch ($res) {
						    case -1:
						    $msg = "پارامترهای ارسالی برای متد مورد نظر ناقص یا خالی هستند . پارمترهای اجباری باید ارسال گردد";
						    break;
						     case -2:
						    $msg = "دسترسی api برای شما مسدود است";
						    break;
						     case -6:
						    $msg = "عدم توانایی اتصال به گیت وی بانک از سمت وبسرویس";
						    break;
						     case -9:
						    $msg = "خطای ناشناخته";
						    break;
						     case -20:
						    $msg = "پین نامعتبر";
						    break;
						     case -21:
						    $msg = "ip نامعتبر";
						    break;
						     case -22:
						    $msg = "مبلغ وارد شده کمتر از حداقل مجاز میباشد";
						    break;
						    case -23:
						    $msg = "مبلغ وارد شده بیشتر از حداکثر مبلغ مجاز هست";
						    break;
						      case -24:
						    $msg = "مبلغ وارد شده نامعتبر";
						    break;
						      case -26:
						    $msg = "درگاه غیرفعال است";
						    break;
						      case -27:
						    $msg = "آی پی مسدود شده است";
						    break;
						      case -28:
						    $msg = "آدرس کال بک نامعتبر است ، احتمال مغایرت با آدرس ثبت شده";
						    break;
						      case -29:
						    $msg = "آدرس کال بک خالی یا نامعتبر است";
						    break;
						      case -30:
						    $msg = "چنین تراکنشی یافت نشد";
						    break;
						      case -31:
						    $msg = "تراکنش ناموفق است";
						    break;
						      case -32:
						    $msg = "مغایرت مبالغ اعلام شده با مبلغ تراکنش";
						    break;
						      case -35:
						    $msg = "شناسه فاکتور اعلامی order_id نامعتبر است";
						    break;
						      case -36:
						    $msg = "پارامترهای برگشتی بانک bank_return نامعتبر است";
						    break;
						        case -38:
						    $msg = "تراکنش برای چندمین بار وریفای شده است";
						    break;
						      case -39:
						    $msg = "تراکنش در حال انجام است";
						    break;
                            case 1:
						    $msg = "پرداخت با موفقیت انجام گردید.";
						    break;
						    default:
						       $msg = $josn['result'];
						}

$query = 'SELECT * FROM `config` WHERE `config_id` = "1" LIMIT 1';
$conf = $db->fetch($query);

					if(!empty($json['result']) AND $json['result'] == 1)
					{
						$_SESSION[$sec] = [
						'price'=>$amount ,
						'order_id'=>$invoice_id ,
						'au'=>$json['au'] ,
					];
 	
$update[payment_res_num] = $json['au'];
$sql = $db->queryUpdate('payment', $update, 'WHERE `payment_rand` = "'.$invoice_id.'" LIMIT 1;');
$db->execute($sql);

		echo ('<div style="display:none">'.$json['form'].'</div>Please wait ... <script language="javascript">document.payment.submit(); </script>');
			exit;	
}
else
{
$data[title] = 'خطای سیستمی';
$data[message] = '<font color="red">خطا در اتصال به ساوانو</font>'.$msg.'<a href="index.php" class="button">بازگشت</a>';
$smarty->assign('config', $conf);
$smarty->assign('data', $data);
$smarty->assign('pay', $payment);
$smarty->display('message.tpl');
exit;
}
}


function callback__sn($data)
{
	if(empty($_GET['sec']) or empty($_GET['check']))
	{
		$output[status] = 0;
        $output[message]= 'مشکل در وریفای تراکنش !!!!';
		return $output;
	}	
	
	
	// Security
$sec=$_GET['sec'];
$mdback = md5($sec.'vm');
$mdurl=$_GET['check'];
// Security
	
	if($mdback == $mdurl)
	{
		
		//get data from session
		$transData = $_SESSION[$sec];
		
		
global $db;
$order_id = preg_replace('/[^0-9]/','',$_GET['order_id']);
$sql = "SELECT * FROM `payment` WHERE `payment_rand` = {$order_id} LIMIT 1;";
$payment = $db->fetch($sql);
if ($payment[payment_status] == 1)
{
	

			$merchantID = trim($data[merchant]);
			$amount = $transData['price']; 
			$bank_return = $_POST + $_GET ;
			
			$au=$transData['au']; 
			
			$data_string = json_encode(array (
			'pin' => $merchantID,
			'price' => $amount,
			'order_id' => $order_id,
			'au' => $au,
			'bank_return' =>$bank_return,
			));

			$ch = curl_init('https://developerapi.net/api/v1/verify');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
			);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
			$result = curl_exec($ch);
			curl_close($ch);


                    //result
                    $json = json_decode($result,true);
					
                    if($json['result'] == 1)
						
                   {
                   //-- آماده کردن خروجی
                   $output['status'] = 1;
                   $output['res_num'] = $payment['payment_res_num'];
                   $output['ref_num'] = $au;
                   $output['payment_id'] = $payment['payment_id'];
                   }
                   else
                   {
                   $output[status] = 0;
                   $output[message]= 'پرداخت انجام نشده است .';
                   }
				               
				   }
				   else
				   {
				   $output[status] = 0;
				   $output[message]= 'این سفارش قبلا پرداخت شده است.';
				   }
					}else{
					 $output[status] = 0;
					 $output[message]= 'مشکل در وریفای تراکنش !!!';
					}
return $output;
}