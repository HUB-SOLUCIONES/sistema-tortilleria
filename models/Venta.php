<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "venta".
 *
 * @property int $id
 * @property int $id_cliente
 * @property int $id_sucursal
 * @property int $id_vendedor Utilizar trabajador_id
 * @property int $cancelada
 * @property string $subtotal
 * @property string $impuesto
 * @property string $descuento
 * @property string $total
 * @property string $saldo
 * @property int $remision
 * @property int $factura
 * @property string $folio_factura
 * @property int $tipo_pago
 * @property string $terminacion_tarjeta
 * @property string $terminal_tarjeta
 * @property string $cargo_tarjeta
 * @property string $folio_deposito
 * @property int $a_pagos
 * @property string $abonado
 * @property int $create_user
 * @property string $create_time
 * @property int $update_user
 * @property string $update_time
 * @property int $cancel_user
 * @property string $cancel_time
 */
class Venta extends \yii\db\ActiveRecord
{

  public $credito=0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'venta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_cliente', 'id_sucursal'], 'required'],
            [['id_cliente', 'id_sucursal', 'id_vendedor', 'cancelada', 'a_pagos', 'create_user', 'update_user', 'cancel_user'], 'integer'],
            [['subtotal', 'descuento', 'total', 'saldo'], 'number'],
            [['create_time', 'update_time', 'cancel_time', 'notas'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Folio',
            'id_cliente' => 'Cliente',
            'id_vendedor' => 'Vendedor',
            'subtotal' => 'Subtotal',
            'descuento' => 'Gastos',
            'total' => 'Total',
            'saldo' => 'Saldo',
            'a_pagos' => 'A crédito',
            'notas' => 'Notas',
            'create_user' => 'Registró',
            'create_time' => 'Creado',
            'update_user' => 'Actualizó',
            'update_time' => 'Actualizado a las',
            'cancel_user' => 'Cancel User',
            'cancel_time' => 'Cancel Time',
        ];
    }

    public function obtenerNombreCliente($id)
    {
      $model = Cliente::find()
      ->where(['id'=>$id])
      ->one();

      return $model->nombre;
    }
    public function obtenerNombreTrabajador($id)
    {
      $model = Trabajador::find()
      ->where(['id'=>$id])
      ->one();

      return $model->nombre;
    }

    public function obtenerTotal($id_cliente)
    {
      $model = Venta::find()
      ->where(['id_cliente'=>$id_cliente])
      ->andWhere(['saldo'=>0])
      ->one();
      return $model->total;
    }

    public function obtenerSaldo($id)
    {
      $model = Venta::find()
      ->where(['id'=>$id])
      ->all();
      return $model->saldo;
    }

    public function obtenerTipoPago($key)
    {
        switch ($key) {
          case 0:
              return 'No';
              break;
          case 1:
              return 'Sí';
              break;
          default:
              return 'Sin información';
              break;
        }
	}

}
