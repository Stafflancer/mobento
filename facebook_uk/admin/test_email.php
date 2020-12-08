<?php 
include_once '../config.php' ;
include_once '../functions.php' ;
require_once '../lib/sendMail.php';

if(SMTP) ini_set('SMTP', SMTP) ;

$lot = array('name' => 'NOM DU LOT') ;
$user = array('email' => 'test@email.com', 'name' => 'Nom', 'fname' => 'Prénom') ;

$email_sender 	= $_POST['email_sender'] ;
$email_subject 	= $_POST['email_subject'] ;
$email_message 	= $_POST['email_message'] ;

if(!$email_sender) echo 'KO' ;

//Mail
$subject = replace_email_tags($email_subject, $lot, $user) ;
$message = replace_email_tags($email_message, $lot, $user) ;

$mailDest = new sendMail();
$mailDest->destMail(array('oliv84@free.fr'));
$mailDest->headMail(NAME . '<' . $email_sender . '>', $email_sender);

try
{
    //Send the message
	$mailDest->envoi($message, $subject) ;
	
	echo 'OK' ;
    }catch(Exception $e)
    {
    var_dump($e) ;
    
    echo 'KO' ;	
}
?>