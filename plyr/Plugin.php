<?php
/**
 * plyr 播放器
 * 
 * @package plyr  播放器
 * @author 杨永全
 * @version 1.0.0
 * @dependence 14.10.10-*
 * @link http://www.qt06.com
 */
class plyr_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
    */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->filter = array('plyr_Plugin','filter');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('plyr_Plugin', 'parse');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('plyr_Plugin', 'parse');
        Typecho_Plugin::factory('Widget_Archive')->header = array('plyr_Plugin', 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('plyr_Plugin', 'footer');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
    */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
    */
    public static function config(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
    */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 输出头部css
     * 
     * @access public
     * @param unknown $header
     * @return unknown
    */
    public static function header() {
        $cssUrl = Helper::options()->pluginUrl . '/plyr/plyr.css';
        echo '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '" />';
    }
    
    /**
     * 输出尾部js
     * 
     * @access public
     * @param unknown $header
     * @return unknown
    */
    public static function footer($widget) {
        $jsUrl = Helper::options()->pluginUrl . '/plyr/plyr.js';
        echo '
<script src="'. $jsUrl .'"></script>
<script>
plyr.setup({';
        if($widget->is('post') || $widget->is('page')) {
            echo '
  autoplay: true,';
        }
        echo '
  i18n: {
    restart:            "重新播放",
    rewind:             "后退 {seektime} 秒",
    play:               "播放",
    pause:              "暂停",
    forward:            "快进 {seektime} 秒",
    buffered:           "缓冲",
    currentTime:        "当前时长",
    duration:           "持续时间",
    volume:             "音量",
    toggleMute:         "切换静音",
    toggleCaptions:     "切换字幕",
    toggleFullscreen:   "切换全屏"
  }
});
</script>';
    }
    
    /**
     * MD兼容性过滤
     * 
     * @param array $value
     * @return array
    */
    public static function filter($value)
    {
        //避免自动添加 P 标签
        if ($value['isMarkdown']) {
            $value['text'] = preg_replace('/(?!<div>)\[(mp3)](.*?)\[\/\\1](?!<\/div>)/is','<div>[mp3]\\2[/mp3]</div>',$value['text']);
            $value['text'] = preg_replace('/(?!<div>)<(audio)(.*?)<\/\\1>(?!<\/div>)/is','<div><audio\\2</audio></div>',$value['text']);
        }
        return $value;
    }
    
    /**
     * 解析
     * 
     * @access public
     * @param array $matches 解析值
     * @return string
    */
    public static function parseCallback($matches)
    {
        $atts = explode('|',$matches[2]);
        //获取播放地址
        $files = array_shift($atts);
        return '<audio src="' . $files . '"></audio>';
    }
    
    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
    */
    public static function parse($text, $widget, $lastResult)
    {
        $text = empty($lastResult) ? $text : $lastResult;
        if ($widget instanceof Widget_Archive) {
            //兼容 audioPlayer
            return preg_replace_callback('/\[(mp3)](.*?)\[\/\\1]/is',array('plyr_Plugin','parseCallback'),$text);
        } else {
            return $text;
        }
    }
}