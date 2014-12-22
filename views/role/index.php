<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var yii\web\View                       $this
 * @var yii\data\ActiveDataProvider        $dataProvider
 * @var yii2mod\rbac\models\AuthItemSearch $searchModel
 */
$this->title = 'Roles';
$this->params['breadcrumbs'][] = $this->title;
$this->render('/layouts/_sidebar');
?>
<div class="role-index">

    <h1><?php echo Html::encode($this->title); ?></h1>

    <p>
        <?php echo Html::a('Create Role', ['create'], ['class' => 'btn btn-success']); ?>
    </p>

    <?php Pjax::begin(['enablePushState' => false, 'timeout' => 5000]); ?>
    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'description:ntext',
            ['class' => 'yii\grid\ActionColumn',],
        ],
    ]);
    ?>
    <?php Pjax::end(); ?>
</div>
