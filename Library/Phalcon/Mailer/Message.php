<?php
/**
 * Message.php 2014-08-31 04:50
 * ----------------------------------------------
 *
 *
 * @author      Stanislav Kiryukhin <korsar.zn@gmail.com>
 * @copyright   Copyright (c) 2014, CKGroup.ru
 *
 * @version     0.0.1
 * ----------------------------------------------
 * All Rights Reserved.
 * ----------------------------------------------
 */
namespace Phalcon\Mailer;


/**
 * Class Message
 * @package Phalcon\Mailer
 */
class Message
{

    /**
     *  content type PLAIN
     */
    const CONTENT_TYPE_PLAIN = 'text/plain';

    /**
     *  content type HTML
     */
    const CONTENT_TYPE_HTML = 'text/html';

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var \Swift_Message
     */
    protected $message;

    /**
     * @var array
     */
    protected $failedRecipients = [];

    /**
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param $email
     * @param null $name
     *
     * @return $this
     */
    public function from($email, $name = null)
    {
        $email = $this->normalizeEmail($email);

        if (is_array($email)) {
            $this->getMessage()->setFrom($email);
        } else {
            $this->getMessage()->addFrom($email, $name);
        }

        return $this;
    }

    /**
     * @param $email
     *
     * @return array|string
     */
    protected function normalizeEmail($email)
    {
        if (is_array($email)) {

            $emails = [];

            foreach ($email as $k => $v) {
                if (is_int($k)) {
                    $emails[$k] = $this->mailer->normalizeEmail($v);
                } else {
                    $k = $this->mailer->normalizeEmail($k);
                    $emails[$k] = $v;
                }
            }

            return $emails;

        } else {
            return $this->mailer->normalizeEmail($email);
        }
    }

    /**
     * @return \Swift_Message
     */
    public function getMessage()
    {
        if (!$this->message) {
            $this->message = $this->getSwift()->createMessage();
        }

        return $this->message;
    }

    /**
     * @return \Swift_Mailer
     */
    protected function getSwift()
    {
        return $this->mailer->getSwift();
    }

    /**
     * @param $email
     * @param null $name
     *
     * @return $this
     */
    public function replyTo($email, $name = null)
    {
        $email = $this->normalizeEmail($email);

        if (is_array($email)) {
            $this->getMessage()->setReplyTo($email);
        } else {
            $this->getMessage()->addReplyTo($email, $name);
        }

        return $this;
    }

    /**
     * @param $email
     * @param null $name
     *
     * @return $this
     */
    public function to($email, $name = null)
    {
        $email = $this->normalizeEmail($email);

        if (is_array($email)) {
            $this->getMessage()->setTo($email);
        } else {
            $this->getMessage()->addTo($email, $name);
        }

        return $this;
    }

    /**
     * @param $email
     * @param null $name
     *
     * @return $this
     */
    public function cc($email, $name = null)
    {
        $email = $this->normalizeEmail($email);

        if (is_array($email)) {
            $this->getMessage()->setCc($email);
        } else {
            $this->getMessage()->addCc($email, $name);
        }

        return $this;
    }

    /**
     * @param $email
     * @param null $name
     *
     * @return $this
     */
    public function bcc($email, $name = null)
    {
        $email = $this->normalizeEmail($email);

        if (is_array($email)) {
            $this->getMessage()->setBcc($email);
        } else {
            $this->getMessage()->addBcc($email, $name);
        }

        return $this;
    }

    /**
     * @param $email
     * @param null $name
     *
     * @return $this
     */
    public function sender($email, $name = null)
    {
        $email = $this->normalizeEmail($email);
        $this->getMessage()->setSender($email, $name);

        return $this;
    }

    /**
     * @param $subject
     *
     * @return $this
     */
    public function subject($subject)
    {
        $this->getMessage()->setSubject($subject);

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->getMessage()->getSubject();
    }

    /**
     * @param $content
     * @param string $contentType
     * @param null $charset
     *
     * @return $this
     */
    public function content($content, $contentType = self::CONTENT_TYPE_HTML, $charset = null)
    {
        $this->getMessage()->setBody($content, $contentType, $charset);

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->getMessage()->getBody();
    }

    /**
     * @param $contentType
     *
     * @return $this
     */
    public function contentType($contentType)
    {
        $this->getMessage()->setContentType($contentType);

        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->getMessage()->getContentType();
    }

    /**
     * @param $charset
     *
     * @return $this
     */
    public function charset($charset)
    {
        $this->getMessage()->setCharset($charset);

        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->getMessage()->getCharset();
    }

    /**
     * @param $priority
     *
     * @return $this
     */
    public function priority($priority)
    {
        $this->getMessage()->setPriority($priority);

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->getMessage()->getPriority();
    }

    /**
     * Attach a file to the message.
     *
     * @param  string $file
     * @param  array $options
     *
     * @return Message
     */
    public function attachment($file, Array $options = [])
    {
        $attachment = $this->createAttachmentViaPath($file);
        return $this->prepareAttachment($attachment, $options);
    }

    /**
     * Create a Swift Attachment instance.
     *
     * @param  string $file
     *
     * @return \Swift_Attachment
     */
    protected function createAttachmentViaPath($file)
    {
        return \Swift_Attachment::fromPath($file);
    }

    /**
     * Prepare and attach the given attachment.
     *
     * @param  \Swift_Attachment $attachment
     * @param  array $options
     *
     * @return Message
     */
    protected function prepareAttachment(\Swift_Attachment $attachment, Array $options = [])
    {
        if (isset($options['mime'])) {
            $attachment->setContentType($options['mime']);
        }

        if (isset($options['as'])) {
            $attachment->setFilename($options['as']);
        }

        $eventManager = $this->mailer->getEventsManager();

        if ($eventManager) {
            $result = $eventManager->fire('mailer:beforeAttachFile', $this, [$attachment]);
        } else {
            $result = true;
        }

        if ($result) {
            $this->getMessage()->attach($attachment);

            if ($eventManager) {
                $eventManager->fire('mailer:afterAttachFile', $this, [$attachment]);
            }
        }

        return $this;
    }

    /**
     * Attach in-memory data as an attachment.
     *
     * @param  string $data
     * @param  string $name
     * @param  array $options
     *
     * @return Message
     */
    public function attachmentData($data, $name, Array $options = [])
    {
        $attachment = $this->createAttachmentViaData($data, $name);
        return $this->prepareAttachment($attachment, $options);
    }

    /**
     * Create a Swift Attachment instance from data.
     *
     * @param  string $data
     * @param  string $name
     *
     * @return \Swift_Attachment
     */
    protected function createAttachmentViaData($data, $name)
    {
        return \Swift_Attachment::newInstance($data, $name);
    }

    /**
     * Embed a file in the message and get the CID.
     *
     * @param  string $file
     *
     * @return string
     */
    public function embed($file)
    {
        $embed = $this->createEmbedViaPath($file);
        return $this->getMessage()->embed($embed);
    }

    /**
     * @param $file
     *
     * @return \Swift_Image
     */
    protected function createEmbedViaPath($file)
    {
        return \Swift_Image::fromPath($file);
    }

    /**
     * Embed in-memory data in the message and get the CID.
     *
     * @param  string $data
     * @param  string $name
     * @param  string $contentType
     *
     * @return string
     */
    public function embedData($data, $name, $contentType = null)
    {
        $embed = $this->createEmbedViaData($data, $name, $contentType);
        return $this->getMessage()->embed($embed);
    }

    /**
     * @param $data
     * @param $name
     * @param null $contentType
     *
     * @return \Swift_Image
     */
    protected function createEmbedViaData($data, $name, $contentType = null)
    {
        return \Swift_Image::newInstance($data, $name, $contentType);
    }

    /**
     * @return bool
     */
    public function send()
    {
        $eventManager = $this->mailer->getEventsManager();

        if ($eventManager) {
            $result = $eventManager->fire('mailer:beforeSend', $this);
        } else {
            $result = true;
        }

        if ($result !== false) {
            $this->failedRecipients = [];
            $result = $this->getSwift()->send($this->getMessage(), $this->failedRecipients);

            if ($eventManager) {
                $eventManager->fire('mailer:afterSend', $this, [$this->failedRecipients]);
            }

            return (bool)$result;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getFailedRecipients()
    {
        return $this->failedRecipients;
    }

    /**
     * @return array
     */
    public function getTo()
    {
        return $this->getMessage()->getTo();
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->getMessage()->getFrom();
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->getMessage()->getSender();
    }

    /**
     * @return string
     */
    public function getReplyTo()
    {
        return $this->getMessage()->getReplyTo();
    }

    /**
     * @return array
     */
    public function getBcc()
    {
        return $this->getMessage()->getBcc();
    }

    /**
     * @return array
     */
    public function getCc()
    {
        return $this->getMessage()->getCc();
    }
}