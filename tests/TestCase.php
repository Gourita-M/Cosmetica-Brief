<?php

abstract Class Notification{
    private string $message;
    private $recipient;

    public function __construct($message,$recipient)
    {
        $this->message = $message;
        $this->recipient = $recipient;
    }

    abstract public function send();

    public function getMessage()
    {
        return $this->message;
    }
}

interface Loggable{
    public function log();
}

Class EmailNotification extends Notification implements Loggable{

    private string $email;

    public function __construct($email,$message,$recipient)
    {
        Parent::__construct($message,$recipient);
        $this->email = $email;
    }
    public function send()
    {
        echo "Email Sent to " . $this->email . Parent::getMessage();
    }
    public function log()
    {
        echo "Log: Email Notification Saved";
    }
}

Class SMSNotification extends Notification implements Loggable{
    private int $number;

    public function __construct($number,$message,$recipient)
    {
        Parent::__construct($message,$recipient);
        $this->number = $number;
    }
    public function send()
    {
        echo "SMS Sent to " . $this->number . Parent::getMessage();
    }
    public function log()
    {
        echo "Log: SMS Notification Saved";
    }
}