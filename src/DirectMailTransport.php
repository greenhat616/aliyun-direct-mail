<?php

namespace Greenhat616\LaravelDirectMail;

use AlibabaCloud\SDK\Dm\V20151123\Dm as DM;
use AlibabaCloud\SDK\Dm\V20151123\Models\SingleSendMailRequest;
use AlibabaCloud\Tea\Utils\Utils;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;


/**
 * @link API Reference: https://help.aliyun.com/document_detail/29444.html
 * @link PHPSDK: https://github.com/alibabacloud-sdk-php/dm-20151123
 */
class DirectMailTransport implements TransportInterface
{
    protected DM $client;
    protected string $accountName;
    protected string $replyTo;
    protected string $replyToAlias;


    public function __construct(DM $client, string $accountName, string $replyTo, string $replyToAlias)
    {
        $this->client = $client;
        $this->accountName = $accountName;
        $this->replyTo = $replyTo;
        $this->replyToAlias = $replyToAlias;
    }

    /**
     * @param RawMessage $message
     * @param Envelope|null $envelope
     * @return SentMessage|null
     */
    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        ['email' => $fromEmail, 'name' => $fromName] = $this->getFrom($message);
        ['email' => $replyToEmail, 'name' => $replyToName] = $this->getReplyTo($message);

        $text = $message->getTextBody();
        $html = $message->getHtmlBody();

        $to = $this->getToRecipients();
        // 由于阿里云邮件推送 API 不支持 CC、BCC，因此不做处理
        $subject = $message->getSubject();

        // 由于阿里云邮件推送 API 不支持附件，因此附件不做处理.
        $profile = [
            'accountName' => $this->accountName,
            'addressType' => 1, // 0 随机地址，1 发信地址
            'FromAlias' => $fromName,
            'toAddress' => implode(',', array_column($to, 'email')),
            'subject' => $subject,
            'ReplyToAddress' => $replyToEmail ? 'true' : 'false',
        ];
        if ($html) {
            $profile['HtmlBody'] = $html;
        } else if ($text) {
            $profile['TextBody'] = $text;
        } else {
            $profile['TextBody'] = '';
        }
        $singleSendMailRequest = new SingleSendMailRequest($profile);
        $this->client->singleSendMail($singleSendMailRequest);
        // return new SentMessage($message, $envelope)
    }

    protected function getFrom(RawMessage $message): array
    {
        $from = $message->getFrom();

        if (count($from) > 0) {
            return ['name' => $from[0]->getName(), 'email' => $from[0]->getAddress()];
        }

        return ['email' => '', 'name' => ''];
    }

    protected function getReplyTo(RawMessage $message): array
    {
        $from = $message->getReplyTo();

        if (count($from) > 0) {
            return ['name' => $from[0]->getName(), 'email' => $from[0]->getAddress()];
        }

        return ['email' => '', 'name' => ''];
    }

    protected function getToRecipients(RawMessage $message): array
    {
        $recipients = [];
        if ($addresses = $message->getTo()) {
            foreach ($addresses as $address) {
                $recipients[] = [
                    'address' => $address->getAddress(),
                    'name' => $address->getName()
                ];
            }
        }
    }

    public function __toString(): string
    {
        return 'directmail';
    }

    protected function getRecipients(string $type, RawMessage $message): array
    {
        $recipients = [];

        if ($addresses = $message->{'get' . ucfirst($type)}()) {
            foreach ($addresses as $address) {
                $recipients[] = [
                    'address' => $address->getAddress(),
                    'name' => $address->getName()
                ];
            }
        }

        return $recipients;
    }


    /*
    protected function sendSingle(\Swift_Mime_SimpleMessage $message)
    {
        $request = new DM\SingleSendMailRequest();

        $request->setAccountName($this->accountName);    //控制台创建的发信地址
        $request->setFromAlias($this->accountAlias);
        $request->setAddressType(1);
        $request->setReplyToAddress('true');

        $request->setToAddress($this->getToAddress($message));
        $request->setSubject($message->getSubject());
        $request->setHtmlBody($message->getBody());
        // dd($message->getBody());

        $this->createClient()->getAcsResponse($request);

        return 1;
    }

    // 多个地址使用逗号分隔
    protected function getToAddress(\Swift_Mime_SimpleMessage $message)
    {
        return implode(',', array_keys($message->getTo()));
    }
    */
}
