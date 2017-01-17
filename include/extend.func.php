<?php
function litimgurls($imgid=0)
{
    global $lit_imglist,$dsql;
    //获取附加表
    $row = $dsql->GetOne("SELECT c.addtable FROM #@__archives AS a LEFT JOIN #@__channeltype AS c 
                                                            ON a.channel=c.id where a.id='$imgid'");
    $addtable = trim($row['addtable']);
    
    //获取图片附加表imgurls字段内容进行处理
    $row = $dsql->GetOne("Select imgurls From `$addtable` where aid='$imgid'");
    
    //调用inc_channel_unit.php中ChannelUnit类
    $ChannelUnit = new ChannelUnit(2,$imgid);
    
    //调用ChannelUnit类中GetlitImgLinks方法处理缩略图
    $lit_imglist = $ChannelUnit->GetlitImgLinks($row['imgurls']);
    
    //返回结果
    return $lit_imglist;
}


//send email use phpmail
function sendemail($emails = array(),$title = '' ,$body = '',$attachments = array(),$ishtml  =true)
{
	global $cfg_smtp_server,$cfg_smtp_usermail,$cfg_smtp_user,$cfg_smtp_password;
	require DEDEINC.'/phpmail/PHPMailerAutoload.php';
	$mail = new PHPMailer;
	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = $cfg_smtp_server;  // Specify main and backup SMTP servers
	$mail->SMTPAuth = TRUE;                               // Enable SMTP authentication
	$mail->Username = $cfg_smtp_user;                 // SMTP username
	$mail->Password = $cfg_smtp_password;                           // SMTP password
	$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
	$mail->Port = 587;                                    // TCP port to connect to
	
	$mail->setFrom($cfg_smtp_usermail, $cfg_smtp_user);
	if( is_array($emails) ){
		foreach( $emails as $email ){
			$mail->addAddress($email);     // Add a recipient
		}
	}else{
		$mail->addAddress($emails);     // Add a recipient
	}
	$mail->Subject = $title;
	$mail->IsHTML($ishtml);
	$mail->Body    = $body;
	if( is_array($attachments) ){
		foreach( $attachments as $attach ){
			$mail->addAttachment($attach);         // Add attachments
		}
	}else{
		$mail->addAttachment($attachments);     // Add a recipient
	}
	
	if(!$mail->send()) {
	    return 'Mailer Error: ' . $mail->ErrorInfo;
	}else{
	    return true;
	}
}

/*
 * export data to .xls
 * @params $data = array(
	0=>array('product name','price','sale num'),
	1=>array('Cupshe 1','$21.99',30),
	2=>array('Cupshe 2','$19.99',20),
	);
 */
function export_xls($data)
{
	/** Include PHPExcel */
	require_once dirname(__FILE__) . '/PHPExcel/Classes/PHPExcel.php';
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
								 ->setLastModifiedBy("Maarten Balliauw")
								 ->setTitle("Office 2007 XLSX Test Document")
								 ->setSubject("Office 2007 XLSX Test Document")
								 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Test result file");

    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);

	// Add some data
	$objPHPExcel->setActiveSheetIndex(0)->fromArray(
                $data, // 赋值的数组
                NULL, // 忽略的值,不会在excel中显示
                'A1');

	// Rename worksheet
	//$objPHPExcel->getActiveSheet()->setTitle('Simple');

	// Redirect output to a client’s web browser (Excel5)
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="01simple.xls"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');

	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;
}

/*
 * read data from files type of .xls
 * @params $file string eg:./products.xls
 */
function read_xls($file)
{
	/** Include PHPExcel_IOFactory */
	require_once dirname(__FILE__) . '/PHPExcel/Classes/PHPExcel/IOFactory.php';

	if (!file_exists($file)) {
		exit("File {$file} is not exist.\n");
	}

	$objPHPExcel = PHPExcel_IOFactory::load($file);
	$excelData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
    return $excelData;
}