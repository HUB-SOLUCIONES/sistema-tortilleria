<?php

use yii\helpers\Html;
use kartik\detail\DetailView;
use app\models\User;
use yii\bootstrap\Modal;
use kartik\editable\Editable;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Venta */

$this->title = 'Venta '. $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="producto-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="col-md-4">
      <?php
      $user= new User();
          echo DetailView::widget([
              'model'=>$model,
              'condensed'=>true,
              'hover'=>true,
              'mode'=>DetailView::MODE_VIEW,
              'deleteOptions'=>[
                'params'=>['id' => $model->id],
                'url'=> ['delete', 'id' => $model->id],
                'data'=> [
                  //'confirm'=>'¿Está seguro que desea eliminar esta habitación?',
                  'method'=>'post',
                ],
              ],
              'panel'=>[
                  'heading'=>'Registrar venta </br>' . $model->id,
                  'type'=>DetailView::TYPE_INFO,
              ],
              'attributes'=>
              [
                [
                    'attribute'=>'id',
                    'format'=>'raw',
                    'displayOnly'=>true,
                ],
                'id_cliente',
                'id_sucursal',
                'id_vendedor',
                'cancelada',
                'subtotal',
                'descuento',
                'total',
                'saldo',
                'a_pagos',
                  [
                      'attribute'=>'create_user',
                      'format'=>'raw',
                      'value'=>$user->obtenerNombre($model->create_user),
                      'displayOnly'=>true,
                  ],
                  [
                      'attribute'=>'create_time',
                      'format'=>'date',
                      'value'=>$model->create_time,
                      'displayOnly'=>true,
                  ],
                  [
                      'attribute'=>'update_user',
                      'format'=>'raw',
                      'displayOnly'=>true,
                  ],
                  [
                      'attribute'=>'update_time',
                      'format'=>'raw',
                      'displayOnly'=>true,
                  ],
              ]
          ]);

      ?>
      </div>

      <p>
        <?php
        if($privilegio[0]['apertura_caja'] == 1)
          echo Html::button('Realizar pago', ['value'=>Url::to(['pago-venta', 'id' => $model->id]), 'class' => 'btn btn-warning', 'id' => 'modalButton']) ?>
      </p>

      <?php
        Modal::begin([
          'header' => '<h4 style="color:#337AB7";>Realizar pago</h4>',
          'id' => 'modal',
          'size' => 'modal-lg',
        ]);
        echo "<div id='modalContent'></div>";
        Modal::end();
      ?>

      <p>
        <?php
        if($privilegio[0]['apertura_caja'] == 1)
        echo Html::a(Yii::t('app', 'Cancelar venta'), ['', 'id' => $model->id], ['class' => 'btn btn-danger']) ?>
      </p>

</div>