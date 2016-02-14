<?php

class TemperatureController extends Controller
{
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/column2';

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',  // allow all users to perform 'index' and 'view' actions
                'actions' => array('index', 'view', 'getWeather'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('create', 'update'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'delete'),
                'users' => array('admin'),
            ),
            array('deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        $this->render('index');
    }

    /**
     * This is the only method that actually does something .
     * Please note that the name of the method is 'actionGetWeather'.
     * This means that the url 'temperature/get-weather' will be mapped to this specific
     * controller method
     */
    public function actionGetWeather()
    {
        // Calculate how many seconds are in five minutes
        $fiveMinutesInSeconds = 5 * 60;

        /**
         * The {CDbCriteria} object is some kind of SQL Query modifier
         * utility, provided by the yii framework
         */
        $criteria = new CDbCriteria();
        /**
         * Provide some restrictions to the SQL query, it is just a pretty
         * way to modify it
         * I took it from:
         * http://www.tutorialspoint.com/sql/sql-top-clause.htm
         * http://www.tutorialspoint.com/sql/sql-order-by.htm
         * See the 'LIMIT' and 'ORDER BY' parts
         */
        $criteria->limit = '1';
        $criteria->order = 'timestamp DESC';

        /**
         * Run a database query, basically what we are saying is
         * "Please go through all of the records in the 'temperature' table, order them
         *  by the timestamp column in descending order, and take the first row"
         * the $criteria is a way to limit and sort the results
         */
        $results = Temperature::model()->findAll($criteria);

        /**
         * If the 'temperature' table is not empty AND the timestamp on the found record
         * is less than 5 minutes ago
         */
        if (!empty($results) && time() - $results[0]->timestamp < $fiveMinutesInSeconds) {
            $record = $results[0];
            // Copy the table record to the $parsedWeather array
            $parsedWeather = [
                'location_name' => $record['location_name'],
                'temp' => $record['temp'],
                'pressure' => $record['pressure'],
                'humidity' => $record['humidity'],
                'timestamp' => $record['timestamp']
            ];
            // If the table is empty or the found record's timestamp is older than 5 minutes ago
        } else {
            // Go fetch Weather data from openweathermap API
            $weatherJsonString = file_get_contents('http://api.openweathermap.org/data/2.5/weather?q=London,uk&units=metric&APPID=18806da9eb99e25a1a5249fc06c69647');
            // Convert the received string to an array
            $weatherObject = CJSON::decode($weatherJsonString);

            /**
             * Create a temperature Model object, this object represents one row in the
             * 'temperature' table
             * Create a new empty table row
             * The Temperature class was created by the yii generator.
             * This is the only auto-generated class I kept
             */
            $temperatureModel = new Temperature;

            $parsedWeather = [
                'location_name' => $weatherObject['name'],
                'temp' => $weatherObject['main']['temp'],
                'pressure' => $weatherObject['main']['pressure'],
                'humidity' => $weatherObject['main']['humidity'],
                'timestamp' => time()
            ];

            /**
             * Take the model class and copy the results I got from Open Weather into the Model
             * object (the one that represents a table row/record
             */
            $temperatureModel->attributes = $parsedWeather;
            /**
             * Save the model, when I call the save() function, yii runs an 'INSERT' SQL
             * query behind the scenes.
             * took it from:
             * http://www.tutorialspoint.com/sql/sql-insert-query.htm
             */
            $temperatureModel->save();
        }

        /**
         * At this point, unless an error occurred, can be sure that
         * $parsedWeather contains some data (from DB or from Open Weather)
         * Here I render the _ajaxContent php file.
         * However I am not using the regular 'render()' method, I am using 'renderPartial()' method
         * that receives a view file name (without the extension) and some data.
         * 'weather' => $parsedWeather means that inside _ajaxContent.php I can access the $weather
         * variable.
         * I am using renderPartial method since this method will be invoked via Ajax call.
         * this means that _ajaxContent.php output will appear in the <div id="weather"></div> tag
         * in views/temperature/index.php file
         */

        $this->renderPartial('_ajaxContent', [
            'weather' => $parsedWeather
        ]);
    }


    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Temperature the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = Temperature::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param Temperature $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'temperature-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
