<?php

namespace Greenhat616\LaravelDirectMail;

use AlibabaCloud\SDK\Dm\V20151123\Dm as DM;
use Darabonba\OpenApi\Models\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class DirectMailServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Mail::extend('directmail', function (array $config = []) {
            $profile = new Config([
                "accessKeyId" => Arr::get($config, 'access_key_id'),
                "accessKeySecret" => Arr::get($config, 'access_key_secret'),
            ]);
            $profile->endpoint = 'dm.aliyuncs.com';
            $client = new DM($profile); // 初始化 DirectMail 实例
            $replyTo = Arr::get($config, 'reply_to');
            return new DirectMailTransport(
                $client,
                Arr::get($config, 'account_name'),
                is_null($replyTo) ? 
                    false : (
                        is_bool($replyTo) ? 
                            $replyTo : 
                            filter_var((string)$replyTo, FILTER_VALIDATE_BOOL
                        )
                    ),
                Arr::get($config, 'from_alias')
            );
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/directmail.php', 'mail.mailer.directmail');
    }
}
