<?php

namespace iAvatar777\assets\JqueryUpload1;

use Imagine\Image\Box;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\imagine\Image;

/**
 *
 */
class Model1 extends Model
{
    /** с обрезкой, по умолчанию */
    const MODE_THUMBNAIL_CUT = 'outbound';

    /** вписать */
    const MODE_THUMBNAIL_FIELDS = 'inset';

    /** вписать с фоном */
    const MODE_THUMBNAIL_WHITE = 'white';

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
        $upload_dir = Yii::getAlias(ArrayHelper::getValue(\Yii::$app->params, 'widgetFileUpload7.uploadDirectory', '@webroot/upload/cloud'));

        $Upload = new extras\FileUpload(ArrayHelper::getValue(\Yii::$app->params, 'widgetFileUpload7.inputName', 'imgname'));

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
        $upload_dir1 = \Yii::getAlias('@webroot');

        copy($_FILES['imgfile']['tmp_name'], $path . $fileName);

        $ret = [
            'success' => true,
            'file'    => $fileName,
            'url'     => \yii\helpers\Url::to(substr($path, strlen($upload_dir1)) . $fileName, true),
//            'path'    => $path . $fileName,
            'size'    => $size,
        ];

        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            if ($this->updateJson) {
                $updateRet = [];
                foreach ($this->updateJson as $item) {
                    $function = $item['function'];
                    $index = $item['index'];
                    $options = $item['options'];
                    $file = \iAvatar777\assets\JqueryUpload1\File::path($path . $Upload->newFileName);
                    $destination = new \iAvatar777\assets\JqueryUpload1\SitePath('/upload/cloud/' . $folderName . '/'. $fileNameWithoutExt . '_' . $index . '.' . $ext);
                    if ($function == 'crop') {
                        $mode = 'outbound';
                        if ($options['mode'] == 'MODE_THUMBNAIL_CUT') $mode = 'outbound';
                        if ($options['mode'] == 'MODE_THUMBNAIL_FIELDS') $mode = 'inset';
                        if ($options['mode'] == 'MODE_THUMBNAIL_WHITE') $mode = 'white';
                        self::saveImage(
                            $file,
                            $destination,
                            [
                                $options['width'],
                                $options['height'],
                                $mode,
                                'quality' => \yii\helpers\ArrayHelper::getValue($options, 'quality', 100),
                            ]
                        );

                        // Добавляю в БД
                        $path = Yii::getAlias('@webroot' . $destination->getPath());
                        if (file_exists($path)) {
                            $size = filesize($path);
                        } else {
                            $size = 0;
                        }

                        $updateRet[$index] = [
                            'url'  => \yii\helpers\Url::to($destination->getPath(), true),
                            'size' => $size,
                        ];
                    }
                }
                $ret['update'] = $updateRet;
            }
        }

        return $ret;
    }


    /**
     * Сохраняет картинку по формату
     *
     * @param \iAvatar777\assets\JqueryUpload1\File $file
     * @param \iAvatar777\assets\JqueryUpload1\SitePath $destination
     * @param array $field
     * @param array | false $format => [
     *                              3000,
     *                              3000,
     *                              FileUpload::MODE_THUMBNAIL_OUTBOUND
     *                              'isExpandSmall' => true,
     *                              ] ,
     *
     * @return \iAvatar777\assets\JqueryUpload1\SitePath
     */
    private function saveImage($file, $destination, $format)
    {
        if ($format === false || is_null($format)) {
            $file->save($destination->getPathFull());
            return $destination;
        }

        $widthFormat = 1;
        $heightFormat = 1;
        if (is_numeric($format)) {
            // Обрезать квадрат
            $widthFormat = $format;
            $heightFormat = $format;
        } else if (is_array($format)) {
            $widthFormat = $format[0];
            $heightFormat = $format[1];
        }

        // generate a thumbnail image
        $mode = ArrayHelper::getValue($format, 2, self::MODE_THUMBNAIL_CUT);
        if ($file->isContent()) {
            $image = Image::getImagine()->load($file->content);
        } else {
            $image = Image::getImagine()->open($file->path);
        }
        if (ArrayHelper::getValue($format, 'isExpandSmall', true)) {
            $image = self::expandImage($image, $widthFormat, $heightFormat, $mode);
        }
        $quality = ArrayHelper::getValue($format, 'quality', 80);
        $options = ['quality' => $quality];
        $image->thumbnail(new Box($widthFormat, $heightFormat), $mode)->save($destination->getPathFull(), $options);

        return $destination;
    }

    /**
     * Расширяет маленькую картинку по заданной стратегии
     *
     * @param \Imagine\Image\ImageInterface $image
     * @param int $widthFormat
     * @param int $heightFormat
     * @param int $mode
     *
     * @return \Imagine\Image\ImageInterface
     */
    protected static function expandImage($image, $widthFormat, $heightFormat, $mode)
    {
        $size = $image->getSize();
        $width = $size->getWidth();
        $height = $size->getHeight();
        if ($width < $widthFormat || $height < $heightFormat) {
            // расширяю картинку
            if ($mode == self::MODE_THUMBNAIL_CUT) {
                if ($width < $widthFormat && $height >= $heightFormat) {
                    $size = $size->widen($widthFormat);
                } else if ($width >= $widthFormat && $height < $heightFormat) {
                    $size = $size->heighten($heightFormat);
                } else if ($width < $widthFormat && $height < $heightFormat) {
                    // определяю как расширять по ширине или по высоте
                    if ($width / $widthFormat < $height / $heightFormat) {
                        $size = $size->widen($widthFormat);
                    } else {
                        $size = $size->heighten($heightFormat);
                    }
                }
                $image->resize($size);
            } else {
                if ($width < $widthFormat && $height >= $heightFormat) {
                    $size = $size->heighten($heightFormat);
                } else if ($width >= $widthFormat && $height < $heightFormat) {
                    $size = $size->widen($widthFormat);
                } else if ($width < $widthFormat && $height < $heightFormat) {
                    // определяю как расширять по ширине или по высоте
                    if ($width / $widthFormat < $height / $heightFormat) {
                        $size = $size->heighten($heightFormat);
                    } else {
                        $size = $size->widen($widthFormat);
                    }
                }
                $image->resize($size);
            }
        }

        return $image;
    }

}
