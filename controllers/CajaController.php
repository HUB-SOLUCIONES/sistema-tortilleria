<?php

namespace app\controllers;
use Yii;
use app\models\Reservacion;
use app\models\Caja;
use app\models\Costales;
use app\models\Venta;
use app\models\Banco;
use app\models\Boveda;
use app\models\EstadoCaja;
use app\models\CajaSearch;
use app\models\Privilegio;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\RegistroSistema;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf;

/**
 * CajaController implements the CRUD actions for Caja model.
 */
class CajaController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Caja models.
     * @return mixed
     */
    public function actionIndex()
    {
      $searchModel = new CajaSearch();
      $id_current_user = Yii::$app->user->identity->id;
      $id_sucursal = Yii::$app->user->identity->id_sucursal;

      $privilegio = Yii::$app->db->createCommand('SELECT * FROM privilegio WHERE id_usuario = '.$id_current_user)->queryAll();
      $totalCaja = Yii::$app->db->createCommand('SELECT Sum(efectivo) FROM caja AS Caja WHERE id_sucursal = '.$id_sucursal)->queryAll();
      $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
      $estado_caja = new EstadoCaja();
      $estado_caja = Yii::$app->db->createCommand('SELECT estado_caja FROM estado_caja WHERE id_sucursal = '.$id_sucursal)->queryAll();

      return $this->render('index', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider,
          'estado_caja' => $estado_caja,
          'totalCaja'=>$totalCaja,
          'privilegio'=>$privilegio,
      ]);
    }

    /**
     * Displays a single Caja model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Caja model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
     public function actionCreate()
     {
         $id_current_user = Yii::$app->user->identity->id;
         $privilegio = Yii::$app->db->createCommand('SELECT * FROM privilegio WHERE id_usuario = '.$id_current_user)->queryAll();

         if($privilegio[0]['movimientos_caja'] == 1){

           $model = new Caja();
           $registroSistema= new RegistroSistema();
           if ($model->load(Yii::$app->request->post()))
           {
             $totalCaja = Yii::$app->db->createCommand('SELECT Sum(efectivo) FROM caja AS Caja')->queryAll();
             $model->create_user=Yii::$app->user->identity->id;
             $model->id_sucursal=Yii::$app->user->identity->id_sucursal;
             $model->create_time=date('Y-m-d H:i:s');

             if($model->tipo_movimiento == 1){
               $model->efectivo=-($model->efectivo);
               $registroSistema->descripcion = Yii::$app->user->identity->nombre ." retiró $".-($model->efectivo). ' de la caja';
               $registroSistema->id_sucursal = Yii::$app->user->identity->id_sucursal;
             }
             else{
               $model->efectivo= $model->efectivo;
               $registroSistema->descripcion = Yii::$app->user->identity->nombre ." ingresó $".$model->efectivo. ' a la caja';
               $registroSistema->id_sucursal = Yii::$app->user->identity->id_sucursal;
             }

               if($model->save() && $registroSistema->save())
               {
                     $searchModel = new CajaSearch();
                     $id_sucursal = Yii::$app->user->identity->id_sucursal;
                     $estado_caja = Yii::$app->db->createCommand('SELECT estado_caja FROM estado_caja WHERE id_sucursal = '.$id_sucursal)->queryAll();
                     $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                     return $this->redirect(['index', [
                         'searchModel' => $searchModel,
                         'dataProvider' => $dataProvider,
                         'estado_caja' => $estado_caja,
                         'totalCaja'=>$totalCaja,
                     ]]);
               }
             }
           }
           else{
             return $this->redirect(['index']);
           }
         return $this->renderAjax('create', [
             'model' => $model,
         ]);
     }

     /**
     * Creates a new Caja model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
     public function actionApertura()
     {
         $id_current_user = Yii::$app->user->identity->id;
         $id_sucursal = Yii::$app->user->identity->id_sucursal;
         $privilegio = Yii::$app->db->createCommand('SELECT * FROM privilegio WHERE id_usuario = '.$id_current_user)->queryAll();
         $id_sucursal = Yii::$app->user->identity->id_sucursal;
         $totalCaja = Yii::$app->db->createCommand('SELECT Sum(efectivo) FROM caja AS Caja WHERE id_sucursal = '.$id_sucursal)->queryAll();

         if($privilegio[0]['apertura_caja'] == 1){
           $model = new Caja();
           $costales = new Costales();
           $costales_cuenta = Costales::find()->orderBy('id DESC')->one();
           $fin = $costales_cuenta->costales_fin;
           $registroSistema= new RegistroSistema();

           if ($model->load(Yii::$app->request->post()) && $costales->load(Yii::$app->request->post()))
           {
             $model->descripcion="Apertura de caja";
             $model->tipo_movimiento = 0;
             $model->create_user=Yii::$app->user->identity->id;
             $model->id_sucursal=Yii::$app->user->identity->id_sucursal;
             $model->create_time=date('Y-m-d H:i:s');
             $sql = EstadoCaja::findOne(['id_sucursal' => $id_sucursal]);
             $sql->estado_caja = 1;
             $registroSistema->descripcion = Yii::$app->user->identity->nombre ." ha realizado una apertura de caja con $".$model->efectivo. ' de efectivo';
             $registroSistema->id_sucursal = Yii::$app->user->identity->id_sucursal;

             if($model->save() && $sql->save() && $registroSistema->save())
             {
                 $model = Caja::find()->orderBy('id DESC')->one();
                 $last_model = $model->id;
                 $costales->id_sucursal = Yii::$app->user->identity->id_sucursal;
                 $costales->costales_fin = "0";
                 $costales->id_caja_ini = $last_model;
                 $costales->id_caja_fin = 0;
                 $costales->create_time = date('Y-m-d H:i:s');

                 if($costales->save())
                 {
                   $searchModel = new CajaSearch();
                   $estado_caja = new EstadoCaja();
                   $estado_caja = Yii::$app->db->createCommand('SELECT estado_caja FROM estado_caja WHERE id_sucursal = '.$id_sucursal)->queryAll();
                   $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                   return $this->redirect(['index', [
                       'searchModel' => $searchModel,
                       'dataProvider' => $dataProvider,
                       'estado_caja' => $estado_caja,
                       'totalCaja' => $totalCaja,
                   ]]);
                 }
             }
           }
         }
         else {
           return $this->redirect(['index']);
         }
         return $this->renderAjax('apertura', [
             'model' => $model,
             'totalCaja'=>$totalCaja,
             'costales' => $costales,
             'fin' => $fin,
         ]);
     }


    /**
     * Cierre de Caja.
     * Al cerrar caja se agrega un momivimiento en caja y se imprime el corte.
     * @return mixed
     */
    public function actionCierre()
    {
        $id_current_user = Yii::$app->user->identity->id;
        $id_sucursal = Yii::$app->user->identity->id_sucursal;
        $privilegio = Yii::$app->db->createCommand('SELECT * FROM privilegio WHERE id_usuario = '.$id_current_user)->queryAll();
        $id_sucursal = Yii::$app->user->identity->id_sucursal;
        $totalCaja = Yii::$app->db->createCommand('SELECT Sum(efectivo) FROM caja AS Caja WHERE id_sucursal = '.$id_sucursal)->queryAll();
        $totalesRetirado = Yii::$app->db->createCommand('SELECT * FROM caja WHERE id=(SELECT MAX(id) FROM caja WHERE descripcion=\'Cierre de caja\')')->queryAll();

        if($privilegio[0]['cierre_caja'] == 1){
          $model = new Caja();
          $costales = new Costales();
          $estado_caja = new EstadoCaja();
          $registroSistema = new RegistroSistema();
          $banco = new Banco();

          if ($model->load(Yii::$app->request->post()) && $costales->load(Yii::$app->request->post()))
            {
              $registroSistema->descripcion = Yii::$app->user->identity->nombre ." ha realizado un cierre de caja de $".$model->efectivo. ' de efectivo';
              $registroSistema->id_sucursal = 1;

              $sql = EstadoCaja::findOne(['id_sucursal' => $id_sucursal]);
              $sql->estado_caja = 0;

              $model->descripcion="Cierre de caja";
              $model->tipo_movimiento=1;
              $model->efectivo=-($model->efectivo);
              $model->create_user=Yii::$app->user->identity->id;
              $model->id_sucursal=Yii::$app->user->identity->id_sucursal;
              $model->create_time=date('Y-m-d H:i:s');

              if($model->save() && $sql->save() && $registroSistema->save())
              {

                $model = Caja::find()->orderBy('id DESC')->one();
                $last_model = $model->id;
                $costales_ultimo = new Costales();
                $costales_ultimo = Costales::find()->orderBy('id DESC')->one();
                $ultimo = $costales->costales_fin;
                $costales_ultimo->costales_fin = $ultimo;
                $costales_ultimo->id_caja_fin = $last_model;

                $banco->id_sucursal = Yii::$app->user->identity->id_sucursal;
                $banco->id_cuenta = 0;
                $banco->descripcion = "Cierre de caja con folio ". $last_model;
                $banco->deposito = -($model->efectivo);
                $banco->tipo_movimiento = 0;
                $banco->create_user = Yii::$app->user->identity->id;
                $banco->create_time = $model->create_time=date('Y-m-d H:i:s');

                if($costales_ultimo->save() && $banco->save()){
                  return $this->render('info');
                }
              }
            }
          }
        else {
          return $this->redirect(['index']);
        }

        return $this->renderAjax('cierre', [
            'model' => $model,
            'totalCaja'=>$totalCaja,
            'estado_caja' => $estado_caja,
            'costales' => $costales,
        ]);
    }

    public function actionInfo(){

      $sucursal = Yii::$app->user->identity->id_sucursal;

      $totalCaja = Yii::$app->db->createCommand('SELECT Sum(efectivo) FROM caja AS Caja WHERE id_sucursal ='. $sucursal)->queryAll();
      $totalesRetirado = Yii::$app->db->createCommand('SELECT * FROM caja WHERE id=(SELECT MAX(id) FROM caja WHERE descripcion = "Cierre de caja") AND id_sucursal ='. $sucursal)->queryAll();
      $costales = Yii::$app->db->createCommand('SELECT id, costales_ini, costales_fin FROM costales WHERE id=(SELECT MAX(id) FROM costales) AND id_sucursal ='. $sucursal)->queryAll();

      $apertura = Yii::$app->db->createCommand('SELECT create_time FROM caja WHERE id=(SELECT MAX(id) FROM caja WHERE descripcion = "Apertura de caja") AND id_sucursal ='. $sucursal)->queryAll();
      $hoy = date('Y-m-d H:i:s');

      $venta = Yii::$app->db->createCommand('SELECT SUM(subtotal) AS subtotal FROM venta WHERE (create_time BETWEEN :fecha_inicio AND :fecha_fin) AND (id_sucursal = :id_sucursal)')
      ->bindValue(':fecha_inicio', $apertura[0]['create_time'])
      ->bindValue(':fecha_fin', $hoy)
      ->bindValue(':id_sucursal', $sucursal)
      ->queryAll();

      $extras = Caja::find()
      ->where(['tipo_pago' => 2])
      ->sum('efectivo');

      $caja = Yii::$app->db->createCommand('SELECT SUM(efectivo) AS efectivo FROM `caja` WHERE NOT (descripcion LIKE "Apertura de caja" OR descripcion LIKE "Cierre de caja") AND (tipo_movimiento = 1)')
      ->queryAll();

      if($extras == null){
          $extras = 0;
      }

      $costal = $costales[0]['costales_ini'] - $costales[0]['costales_fin'];

      $cos = new Costales();
      $cos = Costales::find()
      ->where(['id' => $costales[0]['id']])
      ->one();

      $precioCostal = $venta[0]['subtotal'] / $costal;

      $cos->precio_dia = $precioCostal;
      $cos->usados_dia = $costal;

      $cos->save();

      $searchModel = new CajaSearch();
      $dataProvider = $searchModel->buscarMovimientosCierre(Yii::$app->request->queryParams);
      $content = $this->renderPartial('corteCaja',[
          'dataProvider' => $dataProvider,
          'totalesRetirados'=>$totalesRetirado,
          'totalCaja'=>$totalCaja,
          'costales'=>$costales,
          'precioCostal'=>$precioCostal,
      ]);

      $pdf = new Pdf([
          'mode' => Pdf::MODE_CORE,
          'format' => Pdf::FORMAT_A4,
          'orientation' => Pdf::ORIENT_PORTRAIT,
          'destination' => Pdf::DEST_BROWSER,
          'content' => $content,
          'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
          'cssInline' => '.kv-heading-1{font-size:18px}',
          'options' => ['title' => 'Reporte Cierre'],
          'methods' => [
              'SetHeader'=>['Reporte de cierre de caja '. date('d-m-Y')],
              'SetFooter'=>['Página {PAGENO}'],
          ]
      ]);
      return $pdf->render();

    }


    /**
     * Updates an existing Caja model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Caja model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Caja the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Caja::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
