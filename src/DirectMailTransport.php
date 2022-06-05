<?php

namespace Greenhat616\LaravelDirectMail;

use AlibabaCloud\SDK\Dm\V20151123\Dm as DM;
use AlibabaCloud\SDK\Dm\V20151123\Models\SingleSendMailRequest;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

/**
 * @link API Reference: https://help.aliyun.com/document_detail/29444.html
 * @link PHPSDK: https://github.com/alibabacloud-sdk-php/dm-20151123
 */
class DirectMailTransport extends AbstractTransport
{
    /**
     * 阿里云 SDK 客户端
     * @var AlibabaCloud\SDK\Dm\V20151123\Dm
     */
    protected DM $client;
    /**
     * 邮箱账户
     * @var string
     */
    protected string $accountName;
    /**
     * 是否启用 ReplyTo 指定邮箱，需要在 阿里云控制台配置
     * @var bool
     */
    protected bool $replyTo;
    /**
     * 邮箱别名（昵称）
     * @var string
     */
    protected string $accountAlias;

    public function __construct(DM $client, string $accountName, bool $replyTo, string $accountAlias)
    {
        $this->client = $client;
        $this->accountName = $accountName;
        $this->accountAlias = $accountAlias;
        $this->replyTo = $replyTo;
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        // 获得发信文本
        $text = $email->getTextBody();
        $html = $email->getHtmlBody();

        // 发信配置
        $profile = [
            'accountName' => $this->accountName,
            'addressType' => 1, // 0 随机地址，1 发信地址
            'FromAlias' => $this->accountAlias,
            'toAddress' => collect($email->getTo())->map(function ($email) {
                return  $email->getAddress();
            })->implode(','),
            'subject' => $email->getSubject(),
            'ReplyToAddress' => $this->replyTo ? 'true' : 'false',
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

    public function __toString(): string
    {
        return 'directmail';
    }
}
