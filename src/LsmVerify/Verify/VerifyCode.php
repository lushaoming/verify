<?php
/**
 * 验证码
 * @author: Shannon
 * @DateTime: 2019/2/20 11:19
 */
namespace LsmVerify\Verify;

class VerifyCode
{
    private static $_instance = null;
    private static $sessionKey = 'verify_code';
    private static $flag = [
        '+',
        '-',
    ];

    private function __construct(){}

    /**
     * 单例模式
     * @author: Shannon
     * @DateTime: 2019/2/20 11:22
     * @return VerifyCode|null
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new VerifyCode();
        }
        return self::$_instance;
    }

    /**
     * 生成code验证码
     * @author: Shannon
     * @DateTime: 2019/2/20 15:39
     * @param array $config
     */
    public function createCodeImg($config = [])
    {
        $baseConfig = $this->getBaseConfig();
        // config为空则读取默认配置
        if ($config && is_array($config)) {
            foreach ($config as $key => $value) {
                $baseConfig[$key] = $value;
            }
        }

        $length = $baseConfig['length'];
        $onlyNumber = $baseConfig['onlyNumber'];

        // 生成验证码并存session
        $code = $this->createRandString($length, $onlyNumber);
        $this->setSession($code);
        $baseConfig['text'] = $code;

        $this->createImg($baseConfig);
    }

    /**
     * 生成简单的计算式验证码
     * @author: Shannon
     * @DateTime: 2019/2/20 15:40
     * @param array $config
     */
    public function createComputeCode($config = [])
    {
        $baseConfig = $this->getBaseConfig();
        // config为空则读取默认配置
        if ($config && is_array($config)) {
            foreach ($config as $key => $value) {
                $baseConfig[$key] = $value;
            }
        }
        // 获取计算符号
        $flag = $this->getComputeFlag();
        $num1 = mt_rand(1, $baseConfig['maxNumber']);
        $num2 = mt_rand(0, $baseConfig['maxNumber']);
        // 若第一个数比第二个数小，则只能执行加法
        if ($num1 < $num2) {
            $text = $num1 . '+' . $num2;
        } else {
            $text = $num1 . $flag . $num2;
        }

        $result = eval("return $text;");
        $this->setSession($result);
        $text = $text . '=';
        $baseConfig['text'] = $text;
        $this->createImg($baseConfig);
    }

    /**
     * 验证码验证
     * @author: Shannon
     * @DateTime: 2019/2/20 15:43
     * @param $code
     * @return bool
     */
    public function checkCode($code)
    {
        $code = strtolower($code);
        $sessionCode = $this->getSession();
        if (empty($sessionCode)) return false;
        if ($sessionCode == $code) {// 验证成功，清除session的code
            $this->setSession('');
            return true;
        }
        else return false;
    }

    /**
     * 生成图片
     * @author: Shannon
     * @DateTime: 2019/2/20 15:43
     * @param $config
     */
    private function createImg($config)
    {
        $width = $config['width'];
        $height = $config['height'];
        $fontSize = $config['fontSize'];
        $font = $config['font'];
        $backColorRGB = $config['backColor'];
        $textColorRGB = $config['textColor'];
        $addObstruct = $config['obstruction'];
        $text = $config['text'];

        $im = @imagecreate($width, $height);

        $backColor = ImageColorAllocate($im, $backColorRGB[0], $backColorRGB[1], $backColorRGB[2]); //背景颜色
        imagefill($im, 0,0, $backColor);

        $textColor = ImageColorAllocate($im, $textColorRGB[0], $textColorRGB[1], $textColorRGB[2]);  //文本颜色

        $imageWidth = imagesx($im);
        $imageHeight = imagesy($im);

        // 文字居中计算
        $arr = imagettfbbox($fontSize, 0, $font, $text);
        $textWidth = $arr[2] - $arr[0];
        $x = ($imageWidth - $textWidth) / 2;

        imagettftext($im, $fontSize, 0, $x, 28, $textColor, $font, $text);

        if ($addObstruct === true) {
            //加入干扰线
            for($i=0;$i<3;$i++) {
                $line = ImageColorAllocate($im,rand(0,255),rand(0,255),rand(0,255));
                Imageline($im, rand(0,15), rand(0,15), rand(100,150),rand(10,50), $line);
            }
            //加入干扰象素
            for($i=0;$i<200;$i++) {
                $randcolor = ImageColorallocate($im,rand(0,255),rand(0,255),rand(0,255));
                Imagesetpixel($im, rand()%100 , rand()%50 , $randcolor);
            }
        }

        ob_clean();   // ob_clean()清空输出缓存区
        header("Content-type: image/png");
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * 生成随机字符串
     * @author: Shannon
     * @DateTime: 2019/2/20 11:28
     * @param int $length 字符串长度，默认4
     * @param bool $onlyNumber 是否只包含数字，默认否
     * @return string
     */
    private function createRandString($length = 4, $onlyNumber = false)
    {
        if ($onlyNumber === true) $str = '0123456798';
        else $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz012346798';
        $returnStr = '';
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, mb_strlen($str) - 1);
            $returnStr .= mb_substr($str, $rand, 1);
        }
        return $returnStr;
    }

    /**
     * 默认配置
     * @author: Shannon
     * @DateTime: 2019/2/20 15:44
     * @return array
     */
    private function getBaseConfig()
    {
        $baseConfig = [
            'width' => 130,
            'height' => 40,
            'length' => 4,
            'obstruction' => false,// 是否生成干扰点
            'onlyNumber' => false,// 是否只包含数字
            'textColor' => [50, 50, 255],
            'backColor' => [255, 255, 255],
            'font' => __DIR__ . '/basic.TTF',
            'fontSize' => 20,
            'maxNumber' => 20,// 计算式最大的数
        ];
        return $baseConfig;
    }

    private function setSession($code)
    {
        $code = strtolower($code);
        if (!session_id()) session_start();
        $_SESSION[self::$sessionKey] = $code;
    }

    private function getSession()
    {
        if (!session_id()) session_start();
        return $_SESSION[self::$sessionKey];
    }

    /**
     * 获取计算式符号
     * @author: Shannon
     * @DateTime: 2019/2/20 15:44
     * @return mixed
     */
    private function getComputeFlag()
    {
        $rand = mt_rand(0, count(self::$flag) - 1);
        return self::$flag[$rand];
    }
}