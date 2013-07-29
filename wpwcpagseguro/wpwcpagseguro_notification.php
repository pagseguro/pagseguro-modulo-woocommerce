<?php
/*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

/**
 * Class use for PagSeguro Notification
 */
class WP_WC_Pagseguro_Notification {
     
    /**
    * $_POST['notificationType']
    * @var string 
    */
    private $notification_type;
   
    /**
     * $_POST['notificationCode']
     * @var string 
     */
    private $notification_code;

    /**
     * Reference purchase
     * @var int 
     */
    private $reference;

    /**
     * Code language active in the system
     * @var string 
     */
    private $code_language;

    /**
     * Status PagSeguro
     * @var array 
     */
    private $array_order_status;

    /**
     * PagSeguroAccountCredentials
     * @var PagSeguroAccountCredentials 
     */
    private $obj_credentials;

    /**
     * PagSeguroNotificationType
     * @var PagSeguroNotificationType 
     */
    private $obj_notification_type;

    /**
     * PagSeguroNotificationService
     * @var Transaction 
     */
    private $obj_transaction;
    
    
    /**
    * The first method to be called by the notification PagSeguro treatment.
    */
    public function index($_POST){
        $this->load();
        $this->validatePost($_POST);
        $this->createArrayOrderStatus();
        $this->createCredentials();
        $this->createNotificationType();
        
        if($this->obj_notification_type->getValue() == $this->notification_type){
            $this->createTransaction();
            $this->updateCms();
        }
    }
    
    private function load(){
        require_once 'classes/wpwcmodalpagseguro.class.php';
        require_once "PagSeguroLibrary/PagSeguroLibrary.php";
    }
    
    /**
     * validete if the post is empty
     */
    private function validatePost($_POST){
        $this->notification_type = (isset($_POST['notificationType']) && trim($_POST['notificationType']) != "") ? trim($_POST['notificationType']) : NULL;
        $this->notification_code = (isset($_POST['notificationCode']) && trim($_POST['notificationCode']) != "") ? trim($_POST['notificationCode']) : NULL;
    }
    
    /**
    * Retrieves list of status PagSeguro
    */
    private function createArrayOrderStatus(){
        $modal_pagseguro          = new WP_WC_Modal_Pagseguro();
        $this->array_order_status = $modal_pagseguro->getOrderStatus();
    }
    
    /**
    * Create Credentials
    */
    private function createCredentials(){
        $option = get_option('woocommerce_pagseguro_settings');
        
        $this->obj_credentials = new PagSeguroAccountCredentials($option['email'], $option['token'] );
    }
    
    /**
    * Create Notification type
    */
    private function createNotificationType(){
        $this->obj_notification_type = new PagSeguroNotificationType();
        $this->obj_notification_type->setByType("TRANSACTION");
    }
    
    /**
     * Create Transaction
     */
    private function createTransaction(){
        $this->obj_transaction = PagSeguroNotificationService::checkTransaction($this->obj_credentials, $this->notification_code);
        $this->reference       = str_replace('WC-','',$this->obj_transaction->getReference());
    }
    
    /**
     * Update the transaction status
     */
    private function updateCms(){
        $modal_pagseguro = new WP_WC_Modal_Pagseguro();
        $value_array = $this->array_order_status[$this->obj_transaction->getStatus()->getValue()];
        $id_order_status = $modal_pagseguro->getKeyOrderStatusByName($value_array);
        $this->updateOrder($id_order_status, $modal_pagseguro);
    }
    
    /**
     * 
     * Update table order and save historic
     * 
     * @param type String
     * @param type WP_WC_Modal_Pagseguro 
     */
    private function updateOrder($id_order_status, WP_WC_Modal_Pagseguro $modal_pagseguro){
        $update = $modal_pagseguro->updateOrder($this->reference, $id_order_status);
        $modal_pagseguro->saveHistoric($this->reference,$modal_pagseguro->getNameOrderStatusByKey($id_order_status),$update);
    }
    
}
?>