<?php

namespace App;

class Connect
{
    public $url;
    private $date;
    private $path;
    private $method;
    public $result_url;


    public function __construct(string $url, string $date, string $path, string $method)
    {
        $this->url = $url;
        $this->date = $date;
        $this->path = $path;
        $this->method = $method;
        if (!$this->date && !$this->path){
            $this->result_url = "https://mpstats.io/api/wb/".$this->url;

        }
        $this->result_url = "https://mpstats.io/api/wb/".$this->url."?d1=".trim($this->date)."&path=".$this->path;

    }

    public function getInfoForApi()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->result_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_POSTFIELDS => "{
        \"startRow\":0,\"filterModel\":{},\"sortModel\":[{\"colId\":\"revenue\",\"sort\":\"desc\"}]}",
            CURLOPT_HTTPHEADER => array(
                "X-Mpstats-TOKEN: 60f991cb23a8a4.55242720a2cbcb3f06383121730e03c23e2c8a79",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

}
