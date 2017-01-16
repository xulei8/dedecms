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