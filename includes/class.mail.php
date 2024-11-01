<?php
class Mail{
    
    /* USAGE:
    ** $mail = New Mail($smtp_server, $smtp_username, $smtp_password, $smtp_fromemail, $smtp_fromname, $smtp_port);
    ** $mail->send($email, $subject, $message, $cc, $attachments, $bcc);
    */

    public $message;
    public $attachments = array();
    public $transport = FALSE;

    function __construct($smtp_server, $smtp_username, $smtp_password, $smtp_fromemail, $smtp_fromname, $smtp_port = 25, $smtp_encryption = null) {
        if (file_exists(plugin_dir_path( __FILE__ ).'../packages/swiftmailer/swift_required.php')) {
            require(plugin_dir_path( __FILE__ ).'../packages/swiftmailer/swift_required.php');

            if ($smtp_server <> '') {
                if ($smtp_encryption == '') $smtp_encryption = null;
                $transport = Swift_SmtpTransport::newInstance()
                  ->setHost($smtp_server)
                  ->setPort($smtp_port)
                  ->setEncryption($smtp_encryption);
                if ($smtp_username <> '' && $smtp_password <> '') {
                  $transport->setUsername($smtp_username);
                  $transport->setPassword($smtp_password);
                }
            } else $transport = Swift_MailTransport::newInstance();

            $this->swift = Swift_Mailer::newInstance($transport);

            $this->transport = TRUE;
            $this->fromemail = $smtp_fromemail;
            $this->fromname = $smtp_fromname;
        }
    }

    function send($to, $subject, $body, $cc = array(), $attachments = array(), $bcc = array()) {

        if ($this->transport == FALSE) exit('Mail is not configured.');
        $body = $this->format_body($body);
        $message = Swift_Message::newInstance($subject)
         ->setFrom(array($this->fromemail => $this->fromname))
         ->setTo($to)
         ->setBody($body, 'text/html');
        if (!empty($cc)) {
            $message->setCc($cc);
        }
        if (!empty($bcc)) {
            $message->setBcc($bcc);
        }
        if (!empty($attachments)) {
            foreach ($attachments as $attach)
                $message->attach( Swift_Attachment::fromPath($attach) );
        }
        
        return $this->swift->send($message);

    }
    
    function format_body($body) {
        $reg_exUrl = "/((http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?)/";
        
        $body = preg_replace($reg_exUrl, "<a href='$1'>$1</a>", $body);
        
        return nl2br($body);
    }

}

?>