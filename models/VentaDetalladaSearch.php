<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\VentaDetallada;

/**
 * VentaDetalladaSearch represents the model behind the search form of `app\models\VentaDetallada`.
 */
class VentaDetalladaSearch extends VentaDetallada
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_venta', 'id_producto', 'id_sucursal', 'cant', 'unidad', 'paquete', 'id_promocion'], 'integer'],
            [['precio', 'descuento'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = VentaDetallada::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id_venta' => $this->id_venta,
            'id_producto' => $this->id_producto,
            'id_sucursal' => $this->id_sucursal,
            'cant' => $this->cant,
            'precio' => $this->precio,
            'descuento' => $this->descuento,
            'unidad' => $this->unidad,
            'paquete' => $this->paquete,
            'id_promocion' => $this->id_promocion,
        ]);

        return $dataProvider;
    }
}
