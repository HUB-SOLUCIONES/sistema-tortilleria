<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "trabajador".
 *
 * @property int $id
 * @property int $sucursal_id
 * @property string $nombre
 * @property string $apellidos
 * @property string $telefono
 * @property string $celular
 * @property string $email
 * @property string $direccion
 * @property string $ciudad
 * @property string $estado
 * @property int $cp
 * @property string $sueldo
 * @property int $nomina
 * @property string $fecha_inicio
 * @property string $fecha_fin
 * @property resource $imagen
 * @property resource $huella
 * @property int $eliminado
 * @property int $create_user
 * @property string $create_time
 * @property int $update_user
 * @property string $update_time
 * @property int $delete_user
 * @property string $delete_time
 */
class Trabajador extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trabajador';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sucursal_id', 'sueldo', 'nombre'], 'required'],
            [['sucursal_id', 'cp', 'nomina', 'eliminado', 'create_user', 'update_user', 'delete_user'], 'integer'],
            [['sueldo'], 'number'],
            [['create_time', 'update_time', 'delete_time'], 'safe'],
            [['nombre', 'apellidos', 'telefono', 'celular', 'email', 'direccion', 'ciudad', 'estado'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sucursal_id' => 'Sucursal',
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'telefono' => 'Teléfono',
            'celular' => 'Celular',
            'email' => 'Email',
            'direccion' => 'Dirección',
            'ciudad' => 'Ciudad',
            'estado' => 'Estado',
            'cp' => 'Código Postal',
            'sueldo' => 'Sueldo',
            'nomina' => 'Nómina',
            'eliminado' => 'Eliminado',
            'create_user' => 'Registró',
            'create_time' => 'Creado',
            'update_user' => 'Actualizó',
            'update_time' => 'Actualizado',
            'delete_user' => 'Delete User',
            'delete_time' => 'Delete Time',
        ];
    }

    public function obtenerNombreSucursal($id)
    {
      $model = Sucursal::find()
      ->where(['id'=>$id])
      ->one();

      return $model->nombre;
    }
}
