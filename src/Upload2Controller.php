<?php

namespace iAvatar777\assets\JqueryUpload1;

use Yii;

class Upload2Controller extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    public function init()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

//    // https://learn.javascript.ru/xhr-crossdomain
//    public function behaviors()
//    {
//        return [
//            'corsFilter' => [
//                'class' => '\yii\filters\Cors',
//                'cors'  => [
//                    'Origin'                           => [ArrayHelper::getValue(\Yii::$app->params, 'widgetFileUpload7.Origin', '*')],
//                    'Access-Control-Allow-Origin'      => [ArrayHelper::getValue(\Yii::$app->params, 'widgetFileUpload7.Access-Control-Allow-Origin', '*')],
//                    'Access-Control-Request-Method'    => ['POST', 'OPTIONS'],
//                    'Access-Control-Request-Headers'   => ['*'],
//                    'Access-Control-Allow-Credentials' => true,
//                    'Access-Control-Max-Age'           => 86400,
//                    'Access-Control-Expose-Headers'    => [],
//                ],
//            ],
//        ];
//    }

    /**
     */
    public function actionFileUpload7()
    {
        if (Yii::$app->request->method == 'OPTIONS') return '';

        $model = new Model1();
        $model->load(Yii::$app->request->post(), '');
        if (!$model->validate()) {
            return self::jsonErrorId(102, $model->errors);
        }

        return $model->action();
    }

    /**
     */
    public function actionFileUpload8()
    {
        if (Yii::$app->request->method == 'OPTIONS') return '';

        $model = new Model1();
        $model->load(Yii::$app->request->post(), '');
        if (!$model->validate()) {
            return self::jsonErrorId(102, $model->errors);
        }

        return $model->action();
    }

    public function actionSessionProgress()
    {
        Yii::$app->session->open();

        if (!isset($_POST[ini_get('session.upload_progress.name')])) {
            return ['success' => false];
        }

        $key = ini_get('session.upload_progress.prefix') . $_POST[ini_get('session.upload_progress.name')];

        if (!isset($_SESSION[$key])) {
            return ['success' => false];
        }

        $progress = $_SESSION[$key];
        $pct = 0;
        $size = 0;

        if (is_array($progress)) {

            if (array_key_exists('bytes_processed', $progress) && array_key_exists('content_length', $progress)) {

                if ($progress['content_length'] > 0) {
                    $pct = round(($progress['bytes_processed'] / $progress['content_length']) * 100);
                    $size = round($progress['content_length'] / 1024);
                }
            }
        }

        return [
            'success' => true,
            'pct'     => $pct,
            'size'    => $size,
        ];
    }


    /**
     * Возвращает стандартный ответ JSON при отрицательном срабатывании
     * https://redmine.suffra.com/projects/suffra/wiki/Стандартный_ответ_JSON
     *
     * @param mixed $data [optional] возвращаемые данные
     *
     * @return \yii\web\Response json
     */
    public static function jsonError($data = null)
    {
        if (is_null($data)) $data = '';

        return self::json([
            'success' => false,
            'data'    => $data,
        ]);
    }

    /**
     * Возвращает стандартный ответ JSON при отрицательном срабатывании
     * https://redmine.suffra.com/projects/suffra/wiki/Стандартный_ответ_JSON
     *
     * @param integer $id   идентификатор ошибки
     * @param mixed   $data [optional] возвращаемые данные
     *
     * @return \yii\web\Response json
     */
    public static function jsonErrorId($id, $data = null)
    {
        $return = [
            'id' => $id,
        ];
        if (!is_null($data)) $return['data'] = $data;

        return self::jsonError($return);
    }

    /**
     * Закодировать в JSON
     *
     * @return \yii\web\Response json
     * */
    public static function json($array)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $array;

        return Yii::$app->response;
    }

}
