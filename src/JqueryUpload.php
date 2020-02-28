<?php

namespace iAvatar777\assets\JqueryUpload1;

use yii\web\AssetBundle;

class JqueryUpload extends AssetBundle
{

    public $sourcePath = '@vendor/LPology/Simple-Ajax-Uploader';

    public $js = [
        'SimpleAjaxUploader.min.js',
    ];

    public $css = [
    ];

    public $depends = [
    ];


}