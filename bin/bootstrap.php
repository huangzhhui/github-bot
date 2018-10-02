<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/define.php';

\Swoft\Bean\BeanFactory::init();
\Swoft\App::getBean(\Swoft\Bootstrap\Bootstrap::class)->bootstrap();
