# Aliyun DirectMail for Laravel 9

使用阿里云的 DirectMail 发送邮件。

当前实现仅支持[单一发信接口](https://help.aliyun.com/document_detail/29444.html)。

## 安装

1. 使用 `composer` 安装文件

   ```bash
   composer require greenhat616/laravel-directmail
   ```

2. 在 `config/mail.php` 中的 `mailers` 字段下添加如下配置:

	```php
	'directmail' => [
    	'access_secret_id' => env('DIRECT_MAIL_ACCESS_SECRET_ID'),
    	'access_key_secret' => env('DIRECT_MAIL_ACCESS_SECRET_KEY'),
    	'region' => 'cn-hangzhou',
    	'account_name' => env('DIRECT_MAIL_ACCOUNT_NAME'),
    	'reply_to' => env('DIRECT_MAIL_REPLY_TO'),
    	'from_alias' => env('DIRECT_MAIL_ACCOUNT_ALIAS'),
	],
	```

   具体配置含义请参考[官方文档](https://help.aliyun.com/document_detail/29444.html)。

   请根据需要在`.env`中创建环境配置。

3. 修改 `default` 为 `directmail`（或者`.env` 中的 `MAIL_MAILER`）。

