<?
	$pluginData[snviprog][type] = 'sn';
	$pluginData[snviprog][name] = 'پرداخت آنلاين';
	$pluginData[snviprog][uniq] = 'sn';
	$pluginData[snviprog][description] = 'سرويس پرداخت آنلاين savano';
	$pluginData[snviprog][author][name] = 'sn';
	$pluginData[snviprog][author][url] = 'https://www.savano.co.ir';
	$pluginData[snviprog][author][email] = 'support@savano.ir';

	$pluginData[snviprog][field][config][1][title] = 'API';
	$pluginData[snviprog][field][config][1][name] = 'MID';

	function gateway__snviprog($data)
	{
		global $config,$smarty,$db;
		$midkey = $data[MID];
		$price = $data[price]/10;//
        $callback = $data[callback];
		$order_id= $data[invoice_id];




	$data_string = json_encode(array(
					'pin'=> $midkey,
					'price'=> $price,
					'callback'=>$callback ,
					'order_id'=> $order_id,
					'ip'=> $_SERVER['REMOTE_ADDR'],
					'callback_type'=>2
					));

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





        if($json['result']==1)
		{
		echo ('<div style="display:none">'.$json['form'].'</div>Please wait ... <script language="javascript">document.payment.submit(); </script>');
			exit;	
		}
		else
		{
		//-- نمایش خطا
		$data[title] = 'خطای سیستم';
		$data[message] = '<font color="red">خطا در ارتباط با بانک</font> شماره خطا: '.$msg.'<br /><a href="index.php" class="button">بازگشت</a>';
		throw new Exception($json['msg'] );
		}
	}

	//-- تابع بررسی وضعیت پرداخت
	function callback__snviprog($data)
	{
		global $db,$post;


		$order_id = $_POST['order_id'];
		$trans_id = $_POST['trans_id'];
		$midkey = $data['MID'];


		$sql = 'SELECT * FROM `payment` WHERE `payment_rand` = ? LIMIT 1;';
		$sql = $db->prepare($sql);
		$sql->execute(array (
            $order_id
		));

		$payment 	= $sql->fetch();

		if ($payment[payment_status] == 1)
		{
			$price = $payment[payment_amount];
			///////////////////

			$bank_return = $_POST + $_GET ;

            $data_string = json_encode(array (
			'pin' => $midkey,
			'price' => $price/10,
			'order_id' => $order_id,
			'au' => $trans_id,
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




			$pay = false;


            if($json['result']==1){
				$pay = true;
			} else {
				$pay = false;
			}
			///////////////////








			if($pay)
			{
				//-- آماده کردن خروجی
				$output[status]		= 1;
				$output[res_num]	= $order_id;
				$output[ref_num]	= $trans_id;
				$output[payment_id]	= $payment[payment_id];
			}
			else
			{
				$output[status]	= 0;
				$output[message]= 'خطا در پرداخت';
			}
		}
		else
		{
			//-- سفارش قبلا پرداخت شده است.
			$output[status]	= 0;
			$output[message]= 'این سفارش قبلا پرداخت شده است.';
		}

		return $output;
	}
