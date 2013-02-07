<?php

class Mail
{
	var $mail_subject;
	var $mail_contents;
	var $to; //array
	var $cc; //array
	var $url;
	var $ticket_title;
    var $user_type;
    var $staff;
    var $tx; //quote contents
    var $attachment;
    var $training_topic;
    var $training_time;
    var $training_place;
    var $training_lang;
    var $training_open;
    var $training_tips;
    var $training_trainer;
    var $training_trainee;
    var $training_status;
	
	function Send()
	{
		$config = $this -> GetConfig();
        
        $mailconfig = array('auth' => 'login', 'username' => $config['smtp_account'], 'password' => $config['smtp_pw'], 'port' => '25');
		$transport = new Zend_Mail_Transport_Smtp($config['smtp_server'], $mailconfig);
		$mail = new Zend_Mail('utf-8');
		$mail->setSubject($this->mail_subject);
		$mail->setBodyHtml($this->mail_contents);
		$mail->setFrom($config['sender_account'], $config['sender_name']);
        
		if(!empty($this->to) || !empty($this->cc))
		{
			if(!empty($this->to))
			{
				foreach($this->to as $to_key => $to_val)
				{
					$mail->addTo($to_key, $to_val);
				}
			}
			
			if(!empty($this->cc))
			{
				foreach($this->cc as $cc_key => $cc_val)
				{
					$mail->addCc($cc_key, $cc_val);
				}
			}
			
			try {
				$mail->send($transport);
			} catch (Exception $e) {
				$maillog = new MailLog();
				$maillog -> Add($e->getMessage());
			}
		}
	}
	
	function ContentsTemplate($type)
	{
		$config = $this -> GetConfig();
        
        switch($type)
		{
			case 1: //create ticket
            {
				$contents = $_SESSION["Zend_Auth"]["storage"]->realname." created a ticket for you: <strong><a href='".$config['system_path'].$this->url."'>".$this->ticket_title."</a></strong><br /><br />".$this->tx;
				if($this->attachment)
                {
                    $contents .= "<br /><br />====== Attachment ======<br />".$this->attachment;
                }
                break;
            }
			case 2: //update ticket
            {
				$contents = "This ticket has been updated: <strong><a href='".$config['system_path'].$this->url."'>".$this->ticket_title."</a></strong> by ".$_SESSION["Zend_Auth"]["storage"]->realname."<br /><br />".$this->tx;
				if($this->attachment)
                {
                    $contents .= "<br /><br />====== Attachment ======<br />".$this->attachment;
                }
				break;
            }
			case 3: //close ticket
            {
				$contents = $_SESSION["Zend_Auth"]["storage"]->realname." closed the ticket: <strong><a href='".$config['system_path'].$this->url."'>".$this->ticket_title."</a></strong>.<br /><br />".$this->tx;
				if($this->attachment)
                {
                    $contents .= "<br /><br />====== Attachment ======<br />".$this->attachment;
                }
				break;
            }
			case 4: //create request
            {
				$contents = $_SESSION["Zend_Auth"]["storage"]->realname." created a request for you: <strong><a href='".$config['system_path'].$this->url."'>".$this->ticket_title."</a></strong><br /><br />".$this->tx;
				if($this->attachment)
                {
                    $contents .= "<br /><br />====== Attachment ======<br />".$this->attachment;
                }
				break;
            }
			case 5: //update request
            {
				$contents = "This request has been updated: <strong><a href='".$config['system_path'].$this->url."'>".$this->ticket_title."</a></strong> by ".$_SESSION["Zend_Auth"]["storage"]->realname."<br /><br />".$this->tx;
				if($this->attachment)
                {
                    $contents .= "<br /><br />====== Attachment ======<br />".$this->attachment;
                }
				break;
            }
			case 6: //close request
            {
				$contents = $_SESSION["Zend_Auth"]["storage"]->realname." closed the request: <strong><a href='".$config['system_path'].$this->url."'>".$this->ticket_title."</a></strong>.<br /><br />".$this->tx;
				if($this->attachment)
                {
                    $contents .= "<br /><br />====== Attachment ======<br />".$this->attachment;
                }
				break;
            }
			case 7: //add staff
            {
                $contents = "
Hello,<br /><br />
	
".$this->user_type." ".$this->staff." has been assigned to the ticket: <strong><a href='".$config['system_path'].$this->url."'>".$this->ticket_title."</a></strong><br /><br />

Regards,<br />
IT Tickets System"
;
				break;
            }
			case 8: //delete staff
            {
				$contents = "
Hello,<br /><br />
	
".$this->user_type." ".$this->staff." has been removed from the ticket: <strong><a href='".$config['system_path'].$this->url."'>".$this->ticket_title."</a></strong><br /><br />

Regards,<br />
IT Tickets System"
;
				break;
            }
            case 9: //create training
            {
                $contents = "<strong><i>You have been added into the following training event:</i></strong>
<br /><br />
Topic: <a href='".$config['system_path'].$this->url."'>".$this->training_topic."</a><br /><br />
Date / Time: ".$this->training_time."<br /><br />
Place: ".$this->training_place."<br /><br />
Language: ".$this->training_lang."<br /><br />
Open Class: ".$this->training_open."<br /><br />
Tips: ".$this->training_tips."<br /><br />
Trainer(s): ".$this->training_trainer."<br /><br />
Trainee(s): ".$this->training_trainee;
                break;
            }
            case 10: //update training
            {
                $contents = "<strong><i>The following training event has been updated by ".$_SESSION["Zend_Auth"]["storage"]->realname.":</i></strong>
<br /><br />
Status: ".$this->training_status."<br /><br />
Topic: <a href='".$config['system_path'].$this->url."'>".$this->training_topic."</a><br /><br />
Date / Time: ".$this->training_time."<br /><br />
Place: ".$this->training_place."<br /><br />
Language: ".$this->training_lang."<br /><br />
Open Class: ".$this->training_open."<br /><br />
Tips: ".$this->training_tips."<br /><br />
Trainer(s): ".$this->training_trainer."<br /><br />
Trainee(s): ".$this->training_trainee;
                break;
            }
		}
		
		return $contents;
	}
    
    function FormatUrl($str)
    {
        return preg_replace("/\/scripts\/kindeditor\/attached/i", $config['system_path']."scripts/kindeditor/attached", $str);
    }
    
    function GetConfig()
    {
        $params = new Params();
        
        $result = array();
        
        $result['system_path'] = $params ->GetVal("system_path");
        $result['smtp_server'] = $params ->GetVal("smtp_server");
        $result['smtp_account'] = $params ->GetVal("smtp_account");
        $result['smtp_pw'] = $params ->GetVal("smtp_pw");
        $result['sender_account'] = $params ->GetVal("sender_account");
        $result['sender_name'] = $params ->GetVal("sender_name");
        
        return $result;
    }
}
