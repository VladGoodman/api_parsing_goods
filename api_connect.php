<?php

namespace App;

class Connect
{
    public $url;
    private $path;
    private $method;
    public $result_url;
    public $token;
    public $start;
    public $end;

    public function __construct($url, $method, $start, $end)
    {
        $this->url = $url;
        $this->method = $method;
        $this->start = $start;
        $this->end = $end;
        $this->token = file_get_contents('token.txt');
        $this->result_url = "https://mpstats.io/api/wb/" . $this->url;

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
            CURLOPT_POSTFIELDS => "{\"startRow\":$this->start,\"endRow\":$this->end,\"filterModel\":{},\"sortModel\":[{\"colId\":\"id\",\"sort\":\"asc\"}]}",
            CURLOPT_HTTPHEADER => array(
                "X-Mpstats-TOKEN: " . $this->token,
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
