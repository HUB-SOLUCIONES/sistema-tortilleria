<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Producto;

/**
 * ProductoSearch represents the model behind the search form of `app\models\Producto`.
 */
class ProductoSearch extends Producto
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'eliminado', 'create_user', 'update_user', 'delete_user'], 'integer'],
            [['nombre', 'marca', 'codigo', 'descripcion1','create_time', 'update_time', 'delete_time'], 'safe'],
            [['costo', 'precio'], 'number'],
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
        $query = Producto::find();

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
            'id' => $this->id,
            'id_sucursal' => $this->id_sucursal,
            'costo' => $this->costo,
            'precio' => $this->precio,
            'eliminado' => $this->eliminado,
            'create_user' => $this->create_user,
            'create_time' => $this->create_time,
            'update_user' => $this->update_user,
            'update_time' => $this->update_time,
            'delete_user' => $this->delete_user,
            'delete_time' => $this->delete_time,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'marca', $this->marca])
            ->andFilterWhere(['like', 'codigo', $this->codigo])
            ->andFilterWhere(['like', 'descripcion1', $this->descripcion1]);


        $id = Yii::$app->user->identity->id_sucursal;
        $sucursal = Yii::$app->db->createCommand('SELECT id_sucursal FROM producto WHERE id_sucursal = '.$id)->queryAll();

        if($sucursal != NULL){
            $query->andFilterWhere(['eliminado' => 0 ])
            ->andFilterWhere(['id_sucursal' => $sucursal[0]['id_sucursal']]);

            return $dataProvider;
        }
        else{

            $sucursal = 0;

            $query->andFilterWhere(['eliminado' => 0 ])
            ->andFilterWhere(['id_sucursal' => $sucursal]);

            return $dataProvider;
        }


    }
}
