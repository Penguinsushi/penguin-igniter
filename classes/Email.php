<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $mail = new Email();
// $mail->to = 'recipient@address.com';
// $mail->cc = 'recipient2@address.com';
// $mail->from = 'sender@address.com';
// $mail->subject = 'An Email!';
// $mail->message = '<b>Hey!</b>  How's it going?';
// $mail->sendMail();
// =============================================================================================================

class Email
{
    
    // PROPERTIES
    
    public $to = '';
    public $replyto = '';
    public $from = '';
    public $from_name = '';
    public $subject = '';
    public $message = '';
    public $cc = '';
    public $bcc = '';
    public $format = 'html';
    public $attachments = array();  
    
    // STATIC METHODS
    
    // shortcut method for simple execution
    public static function send($to,$subject,$message,$from)
    {
        $mail = new self();
        $mail->to = $to;
        $mail->subject = $subject;
        $mail->message = $message;
        $mail->from = $from;      
        $mail->sendMail();  
    }
    
    // METHODS
    
    public function sendMail()
    {
        $headers='';
        if (!empty($this->from))
        {
            $headers.='From: ';
            if (!empty($this->from_name))
            {
                $headers.=$this-from_name.' <'.$this->from.'>';
            }
            else
            {
                $headers.=$this->from;
            }
            $headers.="\n";
        } 
        if (!empty($this->replyto))
        {
            $headers.='Reply-To: '.$this->replyto."\n";
        }
        if (!empty($this->cc))
        {
            $headers.='Cc: ';
            if (is_array($this->cc))
            {
                foreach($this->cc AS $cc_key => $cc_value)
                {
                    $ccaddr.= ",$cc_value";
                }
                $ccaddr = substr($ccaddr,1);
                $headers.= $ccaddr."\n";
            }
            else
            {
                $headers.=$this->cc."\n";
            }
        }
        if (!empty($this->bcc))
        {
            $headers.='Bcc: ';
            if (is_array($this->bcc))
            {
                foreach($this->bcc AS $bcc_key => $bcc_value)
                {
                    $bccaddr.= ",$bcc_value";
                }
                $bccaddr = substr($bccaddr,1);
                $headers.= $bccaddr."\n";
            }
            else
            {
                $headers.=$this->bcc."\n";
            }
        }
        if (!empty($this->attachments))
        {
            $headers.='MIME-Version: 1.0'."\n";
            $random_hash = md5(date('r', time())); 
            $boundary = "=Multipart_Boundary_x".$random_hash."x";
            $headers.="Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
            $mess = "\r\n";
            $mess.= "--$boundary\n";
            if ($this->format == 'html')
            {
                $mess.= "Content-Type: text/html; charset=\"UTF-8\"\n";
            }
            else
            {
                $mess.= "Content-Type: text/plain; charset=\"UTF-8\"\n";
            }
            $mess.= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $mess.= $this->message."\r\n\r\n";
            
            foreach($this->attachments AS $attachment)
            {
                $attach = chunk_split(base64_encode(file_get_contents($attachment))); 
                $mess.= "--$boundary\r\n";
                $mess.= "Content-Type: ".  mime_content_type($attachment)."; name=\"$attachment\"\r\n";  
                $mess.= "Content-Transfer-Encoding: base64\r\n";  
                $mess.= "Content-Disposition: attachment\r\n\r\n";  
                $mess.= $attach."\r\n";
            }
            $mess.= "--$boundary--";
            $this->message = $mess;
        }
        elseif (strtolower($this->format) == 'html')
        {
            $headers.='MIME-Version: 1.0'."\n";
            $headers.='Content-type: text/html; charset=iso-8859-1'."\r\n";
        }
        if (is_array($this->to))
        {
            $to='';
            foreach($this->to AS $to_key => $to_value)
            {
                $to.=",$to_value";             
            }
            $to = substr($to,1);
        }
        else
        {
            $to = $this->to;
        }
        mail($to,$this->subject,$this->message,$headers);
    }
    
}

?>