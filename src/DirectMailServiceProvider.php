<?php

namespace Greenhat616\LaravelDirectMail;

use AlibabaCloud\SDK\Dm\V20151123\Dm as DM;
use Darabonba\OpenApi\Models\Config;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class DirectMailServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->make(MailManager::class)->extend('directmail', function () {
            $config = $this->app['config']->get('directmail', []);
            $profile = new Config([
                "accessKeyId" => Arr::get($config, 'access_key_id'),
                "accessKeySecret" => Arr::get($config, 'access_key_secret')
            ]);
            $profile->endpoint = 'dm.aliyuncs.com';
            $client = new DM($profile); // 初始化 DirectMail 实例

            return new DirectMailTransport(
                $client,
                Arr::get($config, 'account_name'),
            );
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/directmail.php', 'directmail');
    }
}
