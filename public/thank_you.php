<?php
/*define('DNS', 'mysql:host=127.0.0.1;dbname=astoredb;charset=utf8');
define('DBUSERNAME', 'root');
define('DBPASSWD', '');*/

define('DNS', 'mysql:host=localhost;dbname=astoredb;charset=utf8');
define('DBUSERNAME', 'astoreusr');
define('DBPASSWD', '6pzGo65^');

class Db
{

    private static $instance = NULL;
    private function __construct() {}
    private function __clone() {}

    public static function getInstance() {
        if (!isset(self::$instance)) {
            $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            self::$instance = new PDO(DNS, DBUSERNAME, DBPASSWD, $pdo_options);
        }
        return self::$instance;
    }
}

class KortiMilli
{
    private $secretkey;
    public  $key;
    public  $amount;
    public  $info;
    public  $orderid;
    public  $callbackUrl;
    public  $returnUrl;
    public  $email;
    public  $phone;
    public  $status;
    public  $transactionId;

    public function __construct($key, $pass)
    {
        $this->key = $key;
        $this->secretkey = hash_hmac('sha256', $pass, $key);
    }

    function token(){
        $this->amount = sprintf('%.2f',$this->amount);
        if($this->amount !== '' && $this->key !== '' && $this->orderid !== '' && $this->callbackUrl !== ''){
            return hash_hmac('sha256', $this->key.$this->orderid.$this->amount.$this->callbackUrl, $this->secretkey);
        }
    }

    function callback(){
        return hash_hmac('sha256', $this->orderid.$this->status.$this->transactionId, $this->secretkey);
    }

    function checkOrderToken(){
        return hash_hmac('sha256', $this->key.$this->orderid, $this->secretkey);
    }

    function tokenInfo($jsn){
        return hash_hmac('sha256', $jsn, $this->secretkey);
    }
}
    $a = new KortiMilli('155929','hXuufItldCNidQk0NotR');

    $jsn = json_decode(file_get_contents("php://input"), false);

/*
{
  "orderId": "2133213",
  "transactionId": "2231223",
  "status": "ok",
  "token": "lasjdlkasjdlajdoi1321i3921391203",
  "amount": 12.00,
  "phone": "+992921223100"
}
*/
   
    if(isset($jsn)){
	//print_r($jsn);
     // $jsn->phone
     // $jsn->amount

        $db = Db::getInstance();
        $a->orderId = $jsn->orderId;
        $a->status = $jsn->status;
        $a->amount = sprintf('%.2f',$jsn->amount);
        $a->transactionId = $jsn->transactionId;
        $token = $a->callback();


        $result = $db->prepare("select * from astore_orders where id = :id and payment_status = 0");
        $result->bindParam(':id', $a->orderId);
        $result->execute();

        if ($result->rowCount() > 0){
            $res =  $result->fetch(PDO::FETCH_ASSOC);

            $amount = sprintf('%.2f',$res['itogo']);

            if($amount == $a->amount){

                if ($token === $jsn->token){
                    if ($jsn->status === "ok"){
                        //update datebase for success payment

                        $result2 = $db->prepare("update astore_orders set payment_status = 1, transaction_id = :transaction_id where id = :id");
                        $result2->bindParam(':id', $a->orderId);
                        $result2->bindParam(':transaction_id', $a->transactionId);
                        $res2 = $result2->execute();
                        if ($res2 == true) {
                            echo "Success";
                        }
                        else
                        {
                            echo "Failed";
                        }

                    }
                    else{
                        // update database for failed payment
                        echo "Failed";
                    }
                }
                else{
                    echo "Failed";
                }

            }
            else{
                echo "Failed";
            }

        }
        else{
            echo "Failed";
        }

    }
    else{
        echo "No json";
    }
