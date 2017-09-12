# wilddog-sms

![](https://docs.wilddog.com/images/logo-d2df5d3b45.svg)

[Wilddog SMS](https://docs.wilddog.com/sms/index.html) SDK for PHP

### 安装
```
composer require wilddog/sms
```

### 初始化

```php
use wilddog\sms\Sms;
use wilddog\sms\Settings;
use wilddog\http\CurlClient;
use wilddog\http\Response;

$settings = new Settings('<YOUR_APP_ID>', '<YOUR_SMS_KEY>');
$sms = new Sms($settings);
```

### 发送验证码短信

`$sms.sendCode($mobile, $templateId, Settings $settings, $var = array())`

```php
$response = $sms->sendCode('<PHONE_NUMBER>', '<YOUR_TEMPLATE_ID>', $settings);
echo $response->__toString();

$response = $sms->sendCode('<PHONE_NUMBER>', '<YOUR_TEMPLATE_ID>', $settings, array('Var1', 'Var2'));
echo $response->__toString();
```

### 校验验证码

`sms.checkCode($mobile, $code)`


```php
$response = $sms->checkCode('<PHONE_NUMBER>', '<CODE>');
echo $response->__toString();
```

### 发送通知短信

`sms.sendNotify($mobiles = array(), $templateId, $params = array())`


```php
$response = $sms->sendNotify(array('<PHONE_NUMBER>'), '<YOUR_TEMPLATE_ID>', array('Var1', 'Var2'));
echo $response->__toString();
```

### 查询发送状态

`sms.checkStatus($rrid)`

```php
$response = $sms->checkStatus('<RRID>');
echo $response->__toString();
```

### 查询账户余额

`sms.getBalance()`

```php
$response = $sms->getBalance();
echo $response->__toString();
```
