### How to install?
- Use composer command : composer require lsmverify/lsmverify
- No composer? click [here](https://getcomposer.org/download/ "here") to download and install composer.
### How to use this package?
```php
<?php
/**
 * Created by PhpStorm.
 * User: Shannon
 * Date: 2019/4/3
 * Time: 15:35
 */
require_once ('../vendor/autoload.php');
use LsmVerify\Verify\VerifyCode;

// default setting
// string verify code img output
VerifyCode::getInstance()->createCodeImg();
// simple computational verification code img output
VerifyCode::getInstance()->createComputeCode();

// If you want to customize the parameters, you can pass in an array parameter, like this:
$baseConfig = [
    'width' => 130,// image width
    'height' => 40,// image height
    'length' => 4,// code length
    'obstruction' => false,// has obstruction? true or false
    'onlyNumber' => false,// only number? true or false
    'textColor' => [50, 50, 255],// RGB
    'backColor' => [255, 255, 255],// RGB
    'font' => __DIR__ . '/basic.TTF',
    'fontSize' => 20,
    'maxNumber' => 20,// max number of compute code
];
VerifyCode::getInstance()->createCodeImg($baseConfig);
VerifyCode::getInstance()->createComputeCode($baseConfig);

/** check verify code **/
$code = '1234';
$result = VerifyCode::getInstance()->checkCode($code);
// VerifyCode::getInstance()->checkCode($code);return true or false
