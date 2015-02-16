<?php

namespace yii2mod\rbac\controllers;

use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii2mod\rbac\models\search\AssignmentSearch;

/**
 * Class AssignmentController
 * @package yii2mod\rbac\controllers
 */
class AssignmentController extends Controller
{
    /**
     * @var string user model class
     */
    public $userClassName;
    /**
     * @var string id column name
     */
    public $idField = 'id';
    /**
     * @var string username column name
     */
    public $usernameField = 'username';
    /**
     * @var string search class name for assignments search
     */
    public $searchClass;


    /**
     * Init function
     */
    public function init()
    {
        parent::init();
        if ($this->userClassName === null) {
            $this->userClassName = Yii::$app->getUser()->identityClass;
            $this->userClassName = $this->userClassName ?: 'app\models\UserModel';
        }
    }

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * Child classes may override this method to specify the behaviors they want to behave as.
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'assign' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Assignment models.
     * @return mixed
     */
    public function actionIndex()
    {
        if ($this->searchClass === null) {
            $searchModel = new AssignmentSearch;
        } else {
            $class = $this->searchClass;
            $searchModel = new $class;
        }

        $dataProvider = $searchModel->search(\Yii::$app->request->getQueryParams(), $this->userClassName, $this->usernameField);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'idField' => $this->idField,
            'usernameField' => $this->usernameField,
        ]);
    }

    /**
     * Displays a single Assignment model.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $authManager = Yii::$app->authManager;
        $available = [];
        $assigned = [];

        foreach ($authManager->getRolesByUser($id) as $role) {
            $assigned[$role->name] = $role->name;
        }

        foreach ($authManager->getRoles() as $role) {
            if (!array_key_exists($role->name, $assigned)) {
                $available[$role->name] = $role->name;
            }
        }

        return $this->render('view', [
            'model' => $model,
            'id' => $id,
            'available' => $available,
            'assigned' => $assigned,
            'idField' => $this->idField,
            'usernameField' => $this->usernameField,
        ]);
    }

    /**
     * Assign or Revoke user role
     * @param $id
     * @param $action
     * @return string[]
     */
    public function actionAssign($id, $action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $roles = ArrayHelper::getValue($post, 'roles', []);
        $manager = Yii::$app->authManager;
        if ($action == 'assign') {
            foreach ($roles as $role) {
                $manager->assign($manager->getRole($role), $id);
            }
        } else {
            foreach ($roles as $role) {
                $manager->revoke($manager->getRole($role), $id);
            }
        }
        return [
            $this->actionRoleSearch($id, 'available', $post['search_av']),
            $this->actionRoleSearch($id, 'assigned', $post['search_asgn']),
        ];
    }

    /**
     * Role search
     * @param $id
     * @param string $target
     * @param string $term
     *
     * @return string
     */
    public function actionRoleSearch($id, $target, $term = '')
    {
        $authManager = Yii::$app->authManager;
        $available = [];
        foreach ($authManager->getRoles() as $role) {
            $available[$role->name] = $role->name;
        }
        $assigned = [];
        foreach ($authManager->getRolesByUser($id) as $role) {
            $assigned[$role->name] = $role->name;
            unset($available[$role->name]);
        }
        $result = [];
        if (!empty($term)) {
            foreach (${$target} as $role) {
                if (strpos($role, $term) !== false) {
                    $result[$role] = $role;
                }
            }
        } else {
            $result = ${$target};
        }
        return Html::renderSelectOptions('', $result);
    }

    /**
     * Finds the Assignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Assignment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $class = $this->userClassName;
        if (($model = $class::findIdentity($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}