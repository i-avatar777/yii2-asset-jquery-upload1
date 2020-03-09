<?php

namespace iAvatar777\assets\JqueryUpload1;

use avatar\models\Log;
use common\models\school\File;
use cs\Application;
use cs\services\Str;
use cs\services\Url;
use cs\services\VarDumper;
use Yii;
use yii\base\Model;
use cs\Widget\FileUpload2\FileUpload;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 *
 */
class Model1 extends Model
{
    /** @var  string */
    public $signature;

    /** @var  string */
    public $update;

    /** @var  array устанавливается после validateJson */
    public $updateJson = [
        [
            'function' => 'crop',
            'index'    => 'crop',
            'options'  => [
                'width'  => '300',
                'height' => '300',
                'mode'   => 'MODE_THUMBNAIL_CUT',
            ],
        ],
    ];

    public function rules()
    {
        return [
            ['signature', 'string'],

            ['update', 'string'],
            ['update', 'validateJson'],
        ];
    }


    public function validateJson($attribute, $params)
    {
        if (!$this->hasErrors()) {
            try {
                $this->updateJson = Json::decode($this->update);
            } catch (\Exception $e) {
                $this->addError($attribute, 'Не верный JSON');
            }
        }
    }

    /**
     *
     *
     * @return array
     */
    public function action()
    {
        // '@upload/cloud'
        $upload_dir = Yii::getAlias(Yii::$app->params['widgetFileUpload7']['uploadDirectory']);

        $Upload = new extras\FileUpload(Yii::$app->params['widgetFileUpload7']['inputName']);
        $Upload->sizeLimit = 100 * 1000 * 1000;

        $ext = strtolower($Upload->getExtension()); // Get the extension of the uploaded file

        // создаю папку
        $time = (string)time();
        $folderName = substr($time, 0, strlen($time) - 5);
        $fileName = substr($time, strlen($time) - 5);
        if (!file_exists($upload_dir . '/' . $folderName)) {
            FileHelper::createDirectory($upload_dir . '/' . $folderName);
        }
        $upload_dir2 = $upload_dir . '/' . $folderName . '/';

        $fileNameWithoutExt = $fileName . '_' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), 0, 10);
        $Upload->newFileName = $fileNameWithoutExt . '.' . $ext;
        $result = $Upload->handleUpload($upload_dir2);

        if (!$result) {
            return [
                'success' => false,
                'msg'     => $Upload->getErrorMsg(),
            ];
        }

        $path = $upload_dir2;
        $size = filesize($path . $Upload->newFileName);

        $fileName = $Upload->getFileName();
        $upload_dir1 = Yii::getAlias('@webroot');

        $ret = [
            'success' => true,
            'file'    => $fileName,
            'url'     => substr($path, strlen($upload_dir1)) . $fileName,
            'path'    => $path . $fileName,
            'size'    => $size,
        ];

        return $ret;
    }
}
