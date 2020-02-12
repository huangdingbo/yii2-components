<?php


namespace dsj\components\controllers;

use dsj\components\server\DemoDataServer;
use ReflectionClass;
use Yii;
use yii\base\Exception;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class ApiController extends Controller
{
    /**
     * @var bool
     * 关闭csrf自动验证
     */
    public $enableCsrfValidation = false;


    /**
     * @var array  必须传递的参数
     * 定义：
     * [
     *     '方法id' => ['参数名1'，'参数名2'],
     * ]
     *
     */
    protected $mustParams = [];

    /**
     * @var bool
     * 是否开启debug
     */
    protected $is_debug = false;

    /**
     * @param $action
     * @return bool
     * @throws ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     * 添加公有属性自动赋值
     * 检查是否开启演示模式
     */
    public function beforeAction($action)
    {
        //给访问该类的公有属性赋值
        $this->load(ArrayHelper::merge(\Yii::$app->request->post(),\Yii::$app->request->get()));

        //检查子类中定义的必传参数
        $this->checkMustParams();

        //检查是否有配置演示数据
        $data = $this->checkIsDemo();
        if($data){
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->data = $data;
            return false;
        }

        return parent::beforeAction($action);
    }


    /**
     * @return array
     * 配置允许跨域
     */
    public function behaviors()
    {
        return ArrayHelper::merge([
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => true
                ],
            ],
        ], parent::behaviors());
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return array|mixed
     * @throws ForbiddenHttpException
     * 格式化输出数据
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action,$result);

        //是否开启debug
        if ($this->is_debug){
            $this->layout = '@vendor/dsj/yii2-components/layouts/main_debug.php';
           return $this->render('@vendor/dsj/yii2-components/views/debug.php',['data' => $result]);
        }

        if (!is_array($result)){
            throw new ForbiddenHttpException('返回参数必须是数组');
        }
        $response = \Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $result['code'] = $response->getStatusCode();
        $result['message'] = $response->statusText;
        return $result;
    }

    protected function load($requestData){
        unset($requestData['r']);
        //本类的所有公有属性
        $selfRef = new ReflectionClass(get_class());
        $selfProperty = $selfRef->getProperties();

        //访问该类的所有公有属性
        $callRef = new ReflectionClass(get_called_class());
        $callProperty = $callRef->getProperties();

        //得到访问该类定义的公有属性，防止更改本类的属性
        $propertys = array_diff($callProperty,$selfProperty);

        foreach ($propertys as $property) {

            $name = $property->getName();

            if ($property->isPublic() && isset($requestData[$name]) && $requestData[$name] !== null) {
                $this->$name = $requestData[$name];
            }
        }
    }

    protected function checkMustParams(){
        if (empty($this->mustParams[$this->action->id])){
            return true;
        }

        foreach ($this->mustParams[$this->action->id] as $item){
            if (!isset($this->$item)){
                throw new Exception('路由:'.$this->route.'的'.$item.'参数为必传参数，请参照文档!!!');
            }
        }
        return true;
    }

    protected function checkIsDemo(){
        $urlArr = explode('?',Yii::$app->request->getAbsoluteUrl());
        $url = $urlArr[0];
        $params = array_merge(Yii::$app->request->get(),Yii::$app->request->post());
        if (isset($params['r'])){
            $url .= '?r=' . $params['r'];
        }
        $data = (new DemoDataServer())->setUrl($url)->setParams($params)->getData();

        return $data;
    }

    protected function setStatusCode($code = 501 , $msg = 'Error'){
        Yii::$app->response->setStatusCode($code ,$msg);
    }
}