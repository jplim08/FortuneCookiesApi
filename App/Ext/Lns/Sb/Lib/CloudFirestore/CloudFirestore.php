<?php
namespace Lns\Sb\Lib\CloudFirestore;

/* IMPORTANT DATA TYPES
stringValue
doubleValue
integerValue
booleanValue
arrayValue
bytesValue
geoPointValue
mapValue
nullValue
referenceValue
timestampValue */
    
class CloudFirestore {
    
    protected $_password;
    protected $_siteConfig;

    public function __construct(
        \Lns\Sb\Lib\Password\Password $Password,
        \Lns\Sb\Lib\Siteconfig $Siteconfig
    ) {
        $this->_password = $Password;
        $this->_siteConfig = $Siteconfig;
    }
    public function setData($data, $collection, $documentId = null) {
        $apiKey = $this->_siteConfig->getData('firebase_api_key');
        $projectId = $this->_siteConfig->getData('firebase_project_id');
        if (!$documentId) {
            $documentId = $this->_password->generate(100);
        }
        $data["documentId"] = ["stringValue" => $documentId];
        $json = json_encode(["fields" => (object)$data]);
        $url = "https://firestore.googleapis.com/v1beta1/projects/".$projectId."/databases/(default)/documents/".$collection."/".$documentId;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                'Content-Length: ' . strlen($json),
                'X-HTTP-Method-Override: PATCH'),
            CURLOPT_URL => $url . '?key='.$apiKey,
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POSTFIELDS => $json
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
}
?>