<?php
namespace Jack;

trait Jack {

	public static $config;

	public static function email($from, $to, $subject, $message) {
		$message = \Swift_Message::newInstance()
			->setSubject($subject)
			->setFrom((array)$from)
			->setTo($to)
			->setBody($message);
		$transport = \Swift_SmtpTransport::newInstance(static::$config->get('smtp.host'), static::$config->get('smtp.port'), static::$config->get('smtp.transport'))
			->setUsername(static::$config->get('smtp.user'))
			->setPassword(static::$config->get('smtp.pass'));
		$mailer = \Swift_Mailer::newInstance($transport);
		return $mailer->send($message);
	}

}
