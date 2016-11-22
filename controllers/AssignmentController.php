<?php

namespace yii2mod\rbac\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii2mod\rbac\models\AssignmentModel;
use yii2mod\rbac\models\search\AssignmentSearch;

/**
 * Class AssignmentController
 *
 * @package yii2mod\rbac\controllers
 */
class AssignmentController extends Controller
{
    /**
     * @var \yii\web\IdentityInterface the class name of the [[identity]] object
     */
    public $userIdentityClass;

    /**
     * @var string search class name for assignments search
     */
    public $searchClass;

    /**
     * @var string id column name
     */
    public $idField = 'id';

    /**
     * @var string username column name
     */
    public $usernameField = 'username';

    /**
     * @var array assignments GridView columns
     */
    public $gridViewColumns = [];

    /**
     * Initializes the object.
     */
    public function init()
    {
        if (empty($this->userIdentityClass)) {
            $this->userIdentityClass = Yii::$app->user->identityClass;
        }

        if (empty($this->gridViewColumns)) {
            $this->gridViewColumns = [
                $this->idField,
                $this->usernameField,
            ];
        }

        parent::init();
    }

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => 'yii\filters\VerbFilter',
                'actions' => [
                    'index' => ['get'],
                    'view' => ['get'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                ],
            ],
            'contentNegotiator' => [
                'class' => 'yii\filters\ContentNegotiator',
                'only' => ['assign', 'remove'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * List of all assignments
     *
     * @return string
     */
    public function actionIndex()
    {
        if (empty($this->searchClass)) {
            $searchModel = Yii::createObject(AssignmentSearch::className());
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $this->userIdentityClass, $this->idField, $this->usernameField);
        } else {
            $searchModel = Yii::createObject($this->searchClass);
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'gridViewColumns' => $this->gridViewColumns,
        ]);
    }

    /**
     * Displays a single Assignment model.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'usernameField' => $this->usernameField,
        ]);
    }

    /**
     * Assign items
     *
     * @param string $id
     *
     * @return array
     */
    public function actionAssign($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $assignmentModel = $this->findModel($id);
        $assignmentModel->assign($items);

        return $assignmentModel->getItems();
    }

    /**
     * Remove items
     *
     * @param string $id
     *
     * @return array
     */
    public function actionRemove($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $assignmentModel = $this->findModel($id);
        $assignmentModel->revoke($items);

        return $assignmentModel->getItems();
    }

    /**
     * Finds the Assignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return AssignmentModel the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $class = $this->userIdentityClass;

        if (($user = $class::findIdentity($id)) !== null) {
            return new AssignmentModel($user);
        } else {
            throw new NotFoundHttpException(Yii::t('yii2mod.rbac', 'The requested page does not exist.'));
        }
    }
}
