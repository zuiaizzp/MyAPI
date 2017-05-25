<?php
class MongodbYii{
    protected $manager;
    protected $collection;
    protected $writeconcern;
    protected $dbname;
    protected $readpreference;
    public function __construct(){
        $this->manager = new MongoDB\Driver\Manager('mongodb://'.MONGO_LAN_IP);
        $this->writeconcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        $this->readpreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);
        $this->dbname = "95xiu";
        $this->collection = "";
    }

    public function selectCollection($collection){
        $this->collection = $collection;
    }

    public function insert($insertary){
        if(empty($this->collection)){
            return false;
        }
        try {
            $bulkwrite = new MongoDB\Driver\BulkWrite();
            $bulkwrite->insert($insertary);
            $result = $this->manager->executeBulkWrite($this->dbname.".".$this->collection,$bulkwrite,$this->writeconcern);
            return $result;
        }catch (Exception $e) {
            printf("MongoDB error: %s\n", $e->getMessage());
            exit(0);
        }
    }

    public function batchInsert($insertary){
        if(empty($this->collection) || empty($insertary)){
            return false;
        }
        foreach ($insertary as $k => $v) {
            $result = $this->insert($v);
        }
        return $result;
    }

    public function update($filter=array(),$newObj=array(),$updateOptions=array()){
        if(empty($this->collection)){
            return false;
        }
        try{
            $bulkwrite = new MongoDB\Driver\BulkWrite();
            $bulkwrite->update($filter,$newObj,$updateOptions);
            $result = $this->manager->executeBulkWrite($this->dbname.".".$this->collection,$bulkwrite,$this->writeconcern);
            return $result;
        }catch (Exception $e){
            printf("MongoDB error: %s\n", $e->getMessage());
            exit(0);
        }
    }


    public function find($filter=array(),$options=array(),$returnAry=true){
        if(empty($this->collection)){
            return false;
        }
        $query = new MongoDB\Driver\Query($filter, $options);
        $cursor = $this->manager->executeQuery($this->dbname.".".$this->collection,$query,$this->readpreference);
        $result = $cursor->toArray();
        // $returnAry 判断是否返回数组
        if( $returnAry == true ){
            $result = $this->object2array($result);
        }
        return $result;
    }

    public function findOne($filter=array(),$options=array()){
        if(empty($this->collection)){
            return false;
        }
        $options['limit'] = 1;
        $query = new MongoDB\Driver\Query($filter, $options); 
        $cursor = $this->manager->executeQuery($this->dbname.".".$this->collection,$query,$this->readpreference);
        $output = array();
        foreach ($cursor as $v){
            $output = (array)$v;
            break;
        }
        return $output;
    }

    public function count($filter=array(),$options=array()){
        $returnAry = false;
        $result = $this->find($filter,$options,$returnAry);
        return count($result);
    }

    public function delete($filter=array(),$options=array()){
        if(empty($this->collection)){
            return false;
        }
        try{
            $bulkwrite = new MongoDB\Driver\BulkWrite();
            $bulkwrite->delete($filter,$options);
            $result = $this->manager->executeBulkWrite($this->dbname.".".$this->collection,$bulkwrite,$this->writeconcern);
            return $result;
        }catch (Exception $e){
            printf("MongoDB error: %s\n", $e->getMessage());
            exit(0);
        }
    }

    public static function MongoDate($time = ''){
        if(empty($time)){
            $time = date('Y-m-d H:i:s',time());
        }else{
            $time = date('Y-m-d H:i:s',$time);
        }
        $result = new MongoDB\BSON\UTCDateTime(new DateTime($time));
        return $result;
    }

    public static function MongoID($id){
        $result = new MongoDB\BSON\ObjectID($id);
        return $result;
    }

    public function aggregate($pipeline=array(),$returnAry=true){
        $command = new MongoDB\Driver\Command([
            'aggregate' => $this->collection,
            'pipeline' => $pipeline,
            'cursor' => new stdClass,
        ]);

        $cursor = $this->manager->executeCommand($this->dbname, $command);
        if( $cursor ){
            $result['result'] = $cursor->toArray();
            $result['ok'] = 1;
        }else{
            $result = array();
            $result['ok'] = 0;
        }
        // $returnAry 判断是否返回数组
        if( $returnAry == true ){
            $result['result'] = $this->object2array($result['result']);
        }
        return $result;
    }
    public function object2array(&$object) {
        if (is_object($object)) {
            $arr = (array)($object);
        } else {
            $arr = &$object;
        }
        if (is_array($arr)) {
            foreach($arr as $varName => $varValue){
                $arr[$varName] = $this->object2array($varValue);
            }
        }
        return $arr;
    }
}