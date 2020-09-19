<?php

define('MAILGUN_URL', '');
define('MAILGUN_KEY', ''); 

define('FROM_EMAIL', 'mail@xxx_daycare_sdn_bhd.com'); 
define('FROM_NAME', 'xxx Daycare Sdn. Bhd.'); 

class Mail
{
    public function sendmailbymailgun($to, $to_name, $subject, $html){
        $array_data = array(
            'from'=> FROM_NAME .'<'.FROM_EMAIL.'>',
            'to'=> $to_name.'<'.$to.'>',
            'subject'=> $subject,
            'html'=> $html,
        );


        $session = curl_init(MAILGUN_URL.'/messages');
        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_USERPWD, 'api:'.MAILGUN_KEY);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $array_data);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($session);
        curl_close($session);
        $result = json_decode($response, true);
        return $result;
    }

}



