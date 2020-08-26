<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'resource/PHPMailer/src/Exception.php';
require 'resource/PHPMailer/src/PHPMailer.php';
require 'resource/PHPMailer/src/SMTP.php';

class Mail
{
	public function __construct()
	{
		$this->mail = new PHPMailer();
		$this->exception = new Exception();
		$this->setupServer();
	}

	private function setupServer()
	{
		$this->mail->SMTPDebug = 0;
		$this->mail->isSMTP();
		$this->mail->Host = 'smtp.gmail.com';
		$this->mail->SMTPAuth = true;
		$this->mail->Username = '';
		$this->mail->Password = '';
		$this->mail->SMTPSecure = 'tls';
		$this->mail->Port = 587;
	}

	public function errorMessage()
	{
		return $this->exception->errorMessage();
	}

	public function setupRecipients($toaddressarr = array(), $ccaddressarr = array(), $bccaddressarr = array())
	{
		$this->mail->setFrom($this->mail->Username, 'Botakpedia');
		
		foreach($toaddressarr AS $emailAddress => $emailName)
		{
			$this->mail->addAddress($emailAddress, $emailName);
		}

		foreach($ccaddressarr AS $emailAddress)
		{
			$this->mail->addCC($emailAddress);
		}

		foreach($bccaddressarr AS $emailAddress)
		{
			$this->mail->addBCC($emailAddress);
		}
	}

	public function sendEmail($subject = '', $body = '', $altbody = '', $attachmentArr = array(), $isHtml = true)
	{
		foreach($attachmentArr AS $attachment => $attachmentname)
		{
			$this->mail->addAttachment($attachment, $attachmentname);
		}

		$this->mail->isHTML($isHtml);
		$this->mail->Subject = $subject;
		$this->mail->Body = $body;
		$this->mail->AltBody = $altbody;

		$result = array();

		if(!$this->mail->send())
		{
			$result['msg'] = $this->mail->ErrorInfo;
			$result['status'] = 0;
			return $result;
		}
		else
		{
			$result['status'] = 1;
			return $result;
		}
	}
}